<?php
require_once('config.php');
require_once('function.php');
session_start();
/*set_time_limit(180);*/

try{
	$dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASSWORD);
}catch(PDOEXception $e){
	echo $e->getMessage();
	exit;
}

$sql = "select instagram_user_name,instagram_user_id,instagram_access_token,created from users where modified < now() - interval 1 day";
$stmt = $dbh->query($sql);

foreach($stmt as $row){
	$sql = "select user_id,image_id,created from user_data where user_id = :user_id order by created desc limit 1";
	$stmt_in = $dbh->prepare($sql);
	$result = $stmt_in->execute(array(":user_id" => $row['instagram_user_id']));

	if( $stmt_in->rowCount() > 0 ){
		$result = $stmt_in->fetchAll(PDO::FETCH_ASSOC);
		$url = "https://api.instagram.com/v1/users/".$row['instagram_user_id']."/media/recent/?access_token=".$row['instagram_access_token']."&min_id=".$result[0]["image_id"];
	} else {
		$url = "https://api.instagram.com/v1/users/".$row['instagram_user_id']."/media/recent/?access_token=".$row['instagram_access_token'];
	}

	$json = file_get_contents($url);
	if(!$json){
		//エラーの場合の処理
		continue;
	}
	$json = json_decode($json);
	/* max 40件分のデータを取得する */
	for($count = 0; $count < 2; $count++ ){
		foreach($json->data as $data){
			$sql = "insert into user_data(user_id,image_id,image_url,link,caption,tags,video,created) values(:user_id,
				:image_id,:image_url,:link,:caption,:tags,:video,:created)";
			$stmt_in = $dbh->prepare($sql);
			$image_url = "";
			if( !empty($data->videos) ) {
				$image_url = $data->videos->standard_resolution->url;
			} else {
				$image_url = $data->images->standard_resolution->url;
			}
			$tags = ",".implode(",", $data->tags).",";
			$textwithout4byte = preg_replace('/[\xF0-\xF7][\x80-\xBF][\x80-\xBF][\x80-\xBF]/', ' ', $data->caption->text);
			$params = array(
				":user_id" => $row['instagram_user_id'], 
				":image_id" => $data->id,
				":image_url" => $image_url,
				":link" => $data->link,
				":caption" => $textwithout4byte,
				":tags" => $tags,
				":video" => !empty($data->videos), 
				":created" => date('Y-m-d H:i:s',$data->created_time)
				);
			$stmt_in->execute($params);
		}
		if(!isset($json->pagination->next_url) ){
			break;
		}else{
			time_nanosleep(0,300000); // 0.5秒
			$url = $json->pagination->next_url;
			$json = file_get_contents($url);
			if(!$json){
				break;
			}
			$json = json_decode($json);
		}
	}
	
	$sql = "update users set modified = :modified where instagram_user_id = :instagram_user_id";
	$stmt_in = $dbh->prepare($sql);
	$params = array(
				":modified" => date("Y-m-d H:i:s"),
				":instagram_user_id" => $row['instagram_user_id'] );
	$stmt_in->execute($params);

}

$dbh = null;
