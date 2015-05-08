<?php

require_once('config.php');

session_start();

function h($s){
	return htmlspecialchars($s,ENT_QUOTES,"UTF-8");
}

	//全ユーザー情報更新
	$url = "https://api.instagram.com/v1/users/self/media/liked?access_token=".$_SESSION['user']['instagram_access_token']."&max_like_id=".$_GET['next_id'];
	$json = file_get_contents($url);
	header('Content-Type: application/json; charset=utf-8');
	echo $json;
/*	$json = json_decode($json);

	if(isset($json->pagination->next_max_like_id)){
		echo "<input type='hidden' id='next_id' value='".h($json->pagination->next_max_like_id);"' />";
	}
	foreach($json->data as $data){
		echo "<li><img src='".$data->images->standard_resolution->url."' /></li>";
	}
*/
