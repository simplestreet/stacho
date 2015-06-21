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

$sql = "select instagram_user_name,instagram_user_id,created from cache_users where created < now() - interval 1 day";
$stmt = $dbh->query($sql);

if(!$stmt){
	$dbh = null;
	echo "db query errors.";
	exit;
}
if( $stmt->rowCount() === 0 ){
	$dbh = null;
	echo "No items need to be deleted.";
	exit;
}
foreach($stmt as $row){
	print $row['instagram_user_name']."\n";
	var_dump($row);
	$sql = "delete from cache_users where instagram_user_id = :instagram_user_id";
	$stmt = $dbh->prepare($sql);
	$result = $stmt->execute(array(":instagram_user_id" => $row['instagram_user_id']));
	if(!$result){
		print $row['instagram_user_name']." user info error\n";
		continue;
	}
	$sql = "delete from cache_user_data where user_id = :user_id";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":user_id" => $row['instagram_user_id']));
	
	print $row['instagram_user_name']." data is deleted\n";
}

$dbh = null;
