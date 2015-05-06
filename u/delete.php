<?php
require_once('config.php');
session_start();

//var_dump($_SESSION['user']);
//exit;

try{
	$dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASSWORD);
}catch(PDOEXception $e){
	echo $e->getMessage();
	exit;
}
$sql = "delete from user_data where user_id = :user_id";
$stmt = $dbh->prepare($sql);
$stmt->execute( array(":user_id" => $_SESSION['user']['instagram_user_id']) );
$dbh = null;

$_SESSION = array();

if(isset($_COOKIE[session_name()])){
	setcookie(session_name(),time()-64000,'/instagram_api_php/');
}

session_destroy();

header('Location: '.SITE_URL);

