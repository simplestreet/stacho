<?php

require_once('config.php');

session_start();

function h($s){
	return htmlspecialchars($s,ENT_QUOTES,"UTF-8");
}

	//全ユーザー情報更新
	$url = "https://api.instagram.com/v1/users/".$_SESSION['user']['instagram_user_id']."/followed-by?access_token=".$_SESSION['user']['instagram_access_token']."&cursor=".$_GET['next_id'];
	//$url = "https://api.instagram.com/v1/users/"."2823122"."/followed-by?access_token=".$_SESSION['user']['instagram_access_token']."&cursor=".$_GET['next_id'];
	$json = file_get_contents($url);
	header('Content-Type: application/json; charset=utf-8');
	echo $json;

