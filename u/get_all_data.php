<?php
require_once('config.php');
require_once('function.php');
session_start();
/*set_time_limit(180);*/

echo "number of all datas : ".$argc."\n";

if($argc < 2){
	return;
}
try{
	$dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASSWORD);
}catch(PDOEXception $e){
	echo $e->getMessage();
	exit;
}

for( $i = 1  ; $i < $argc; $i++ ) {
	/*if(!($user_id=existCacheUserInfo($dbh,$argv[$i]))) {
		continue;
	}*/
	if(!($user_id=existUserInfo($dbh,$argv[$i]))) {
		continue;
	}
	echo $argv[$i]." : ".$user_id."\n";
	$access_token = get_access_token($dbh);
	$sql = "select user_id,image_id,created from user_data where user_id = :user_id order by created asc limit 1";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":user_id" => $user_id));
	
	if( $stmt->rowCount() > 0 ){
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$url = "https://api.instagram.com/v1/users/".$user_id."/media/recent/?access_token=".$access_token."&max_id=".$result[0]["image_id"];
	} else {
		$url = "https://api.instagram.com/v1/users/".$user_id."/media/recent/?access_token=".$access_token;
	}
	
	$json = file_get_contents($url);
	if( !$json ){
		continue;
	}
	$json = json_decode($json);
	
	for($count = 0; $count < 120; $count++){
		foreach($json->data as $data){
			$sql = "insert into user_data(user_id,image_id,image_url,link,caption,tags,video,created) values(:user_id,
				:image_id,:image_url,:link,:caption,:tags,:video,:created)";
			$stmt = $dbh->prepare($sql);
			$image_url = "";
			if( !empty($data->videos) ) {
				$image_url = $data->videos->standard_resolution->url;
			} else {
				$image_url = $data->images->standard_resolution->url;
			}
			$tags = ",".implode(",", $data->tags).",";
			$textwithout4byte = preg_replace('/[\xF0-\xF7][\x80-\xBF][\x80-\xBF][\x80-\xBF]/', ' ', $data->caption->text);
			$params = array(
				":user_id" => $user_id, 
				":image_id" => $data->id,
				":image_url" => $image_url,
				":link" => $data->link,
				":caption" => $textwithout4byte,
				":tags" => $tags,
				":video" => !empty($data->videos), 
				":created" => date('Y-m-d H:i:s',$data->created_time)
				);
			$stmt->execute($params);
		}
		if(!isset($json->pagination->next_url) ){
			break;
		}else{
			time_nanosleep(0,300000); // 0.5秒
			$url = $json->pagination->next_url;
			$json = file_get_contents($url);
			if(!$json){
				continue;
			}
			$json = json_decode($json);
		}
		time_nanosleep(0,500000);
	}
	echo $argv[$i]." : all data is installed\n";
}

$dbh = null;
