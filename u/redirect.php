<?php

require_once('config.php');

session_start();
set_time_limit(150);
if (empty($_GET['code'])){
	//認証前の準備
	$params = array(
		'client_id' => CLIENT_ID,
		'redirect_uri' => SITE_URL.'u/redirect.php',
		'scope' => 'basic',
		'response_type' => 'code'
		);
	$url = 'https://api.instagram.com/oauth/authorize/?'.http_build_query($params);
	
	//instagramへ飛ばす
	header('Location: '.$url);
	exit;
	
}else{
	//認証後の処理
	//user情報の取得
	$params = array(
		'client_id' => CLIENT_ID,
		'client_secret' => CLIENT_SECRET,
		'code' => $_GET['code'],
		'redirect_uri' => SITE_URL.'u/redirect.php',
		'grant_type' => 'authorization_code'
	);
	$url = "https://api.instagram.com/oauth/access_token";
	$curl = curl_init();
	curl_setopt($curl,CURLOPT_URL,$url);
	curl_setopt($curl,CURLOPT_POST,1);
	curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($params));
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
	
	$res = curl_exec($curl);
	curl_close($curl);
	$json = json_decode($res);
	
	//user情報の格納
	try{
		$dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASSWORD);
	}catch(PDOEXception $e){
		echo $e->getMessage();
		exit;
	}
	$stmt = $dbh->prepare("select * from users where instagram_user_id=:user_id limit 1");
	$stmt->execute(array(":user_id" => $json->user->id));
	//$stmt->execute(array(":user_id" => 173645757));
	$user = $stmt->fetch();

	if( empty($user)) {
		$stmt = $dbh->prepare("insert into users(instagram_user_id,
		instagram_user_name,instagram_profile_picture,instagram_access_token,
		created,modified) values
		(:user_id,:user_name,:profile_picture,:access_token,now(),now());");
		
		$params = array(
			":user_id" => $json->user->id,
			//":user_id" => 173645757,
			":user_name" => $json->user->username,
			":profile_picture" => $json->user->profile_picture,
			":access_token" => $json->access_token
		);
		$result= $stmt->execute($params);
		if(!$result){
			echo "hello";
			exit;
		}
		//挿入したデータをひぱってくる
		$stmt = $dbh->prepare("select * from users where
		id=:last_insert_id limit 1");
		$stmt->execute(array(":last_insert_id" => $dbh->lastInsertId()));
		$user = $stmt->fetch();
	} else {
		$stmt = $dbh->prepare("update users set instagram_access_token = :access_token where instagram_user_name = :instagram_user_name");
		$result = $stmt->execute(
			array(":access_token" => $json->access_token,
					":instagram_user_name" => $user['instagram_user_name']));
		if(!$result){
			echo "update error";
			exit;
		}
		//挿入したデータをひぱってくる
		$stmt = $dbh->prepare("select * from users where instagram_user_id= :instagram_user_id limit 1");
		$result = $stmt->execute(array(":instagram_user_id" => $user['instagram_user_id']));
		if(!$result){
			echo "error:can not get the data";
			exit;
		}
		$user = $stmt->fetch();
	}
	
	//ログイン処理
	if(!empty($user)){
		session_regenerate_id(true);
		$_SESSION['user'] = $user;
	}
	$user_id = $_SESSION['user']['instagram_user_id'];
	//$user_id = "173645757";
	
	////全ユーザー情報更新////////////////////////////////////////////////////////////////////////////
	$url = "https://api.instagram.com/v1/users/".$user_id."/?access_token=".$_SESSION['user']['instagram_access_token'];
	$json = file_get_contents($url);
	$json = json_decode($json);
	//$_SESSION['user_detail']
	$data = $json->data;

	$sql = "update users set instagram_user_name = :instagram_user_name,full_name = :full_name,instagram_profile_picture = :instagram_profile_picture,
			bio = :bio,website = :website,media = :media,follows = :follows,followed_by = :followed_by,modified = now() where instagram_user_id = :instagram_user_id";
	$stmt = $dbh->prepare($sql);
	$params = array(
				":instagram_user_name" => $data->username,
				":full_name" => $data->full_name,
				":instagram_profile_picture" => $data->profile_picture,
				":bio" => $data->bio,
				":website" => $data->website,
				":media" => $data->counts->media,
				":follows" => $data->counts->follows,
				":followed_by" => $data->counts->followed_by,
				":instagram_user_id" => $data->id );
	$stmt->execute($params);

	/////////////////////////////////////////////////////////////////////////////////////////////////
	
	// 1430652834 自分
	// 173645757  ローラ
	// 474885524  みやたさとこ
	
	$sql = "select user_id,image_id,created from user_data where user_id = :user_id order by created desc limit 1";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":user_id" => $user_id));
	//var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));
	if( $stmt->rowCount() > 0 ){
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$url = "https://api.instagram.com/v1/users/".$user_id."/media/recent/?access_token=".$_SESSION['user']['instagram_access_token']."&min_id=".$result[0]["image_id"];
	} else {
		$url = "https://api.instagram.com/v1/users/".$user_id."/media/recent/?access_token=".$_SESSION['user']['instagram_access_token'];
	}

	//$url = "https://api.instagram.com/v1/users/".$user_id."/media/recent/?access_token=".$_SESSION['user']['instagram_access_token'];
	$json = file_get_contents($url);
	$json = json_decode($json);
	
	// 300枚までログをする。
	for($count = 0; $count < 15; $count++){
		foreach($json->data as $data){
			$sql = "insert into user_data(user_id,image_id,image_url,link,caption,tags,video,created) values(:user_id,
				:image_id,:image_url,:link,:caption,:tags,:video,:created)";
			$stmt = $dbh->prepare($sql);
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
				":image_url" => $image_url/*$data->images->standard_resolution->url*/,
				":link" => $data->link,
				":caption" => /*$data->caption->text*/$textwithout4byte,
				":tags" => /*implode(",", $data->tags)*/$tags,
				":video" => !empty($data->videos), 
				":created" => date('Y-m-d H:i:s',$data->created_time)
				);
			$stmt->execute($params);
		}
		if(!isset($json->pagination->next_url) ){
			break;
		}else{
			time_nanosleep(0,500000); // 0.5秒
			$url = $json->pagination->next_url;
			$json = file_get_contents($url);
			$json = json_decode($json);
		}
	}
	
	$dbh = null;

	// index.php
	header('Location: '.SITE_URL.'u/?id='.$_SESSION['user']['instagram_user_name']);
	//header('Location: '.SITE_URL.'u/?id='.'rolaofficial');
}
?>
