<?php
session_start();

function h($s){
	return htmlspecialchars($s,ENT_QUOTES,"UTF-8");
}

function isMyPage(){
	if(empty($_SESSION['user'])){
		return false;
	}else{
		if(strcmp($_SESSION['user']['instagram_user_name'],$_SESSION['user_detail'][0]['instagram_user_name']) !== 0){
			return false;
		}else{
			return true;
		}
	}
}
function existUserInfo($dbh,$user_name){
	$sql = "select instagram_user_id from users where instagram_user_name = :instagram_user_name";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":instagram_user_name" => $user_name));
	if ( $stmt-> rowCount() > 0 ) {
		$ret = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $ret[0]['instagram_user_id'];
	}else{
		return false;
	}
	
}
function get_userinfo($dbh,$user_name){
	
	//登録ユーザーが本データベースに存在するかチェック
	if($user_id = existUserInfo($dbh,$user_name)){
		/* Sessionがあり、ログイン本人と表示するuserが一緒の場合は、最新のデータを取ってくる。
		if (!empty($_SESSION['user'])){
			if( strcmp($_SESSION['user']['instagram_user_name'],$user_name) === 0){
			}
		} */
		////全ユーザー情報更新////////////////////////////////////////////////////////////////////////////
		$sql = "select instagram_user_id,instagram_user_name,full_name,instagram_profile_picture,bio,website,media,follows,followed_by from users where instagram_user_id = :instagram_user_id";
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array(":instagram_user_id" => $user_id));
		if ( $stmt-> rowCount() > 0 ) {
			$_SESSION['user_detail'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		//////////////////////////////////////////////////////////////////////////////////////////////////
		
		/////月別のデータ統計/////////////////////////////////////////////////////////////////////////////
		$sql = "select date_format(created,'%Y-%m') as created,count(*) as count from user_data where user_id = :instagram_user_id group by date_format(created,'%Y%m') order by created desc";
		$stmt = $dbh->prepare($sql);
		//$stmt->execute();
		$stmt->execute(array(":instagram_user_id" => $user_id));
		if ( $stmt-> rowCount() > 0 ) {
			$_SESSION['monthly'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		//////////////////////////////////////////////////////////////////////////////////////////////////
		
		/////日別のデータ統計/////////////////////////////////////////////////////////////////////////////
		$sql = "select date_format(created,'%Y-%m-%d') as created,count(*) as count from user_data where user_id = :instagram_user_id group by date_format(created,'%Y%m%d') order by created desc";
		$stmt = $dbh->prepare($sql);
		//$stmt->execute();
		$stmt->execute(array(":instagram_user_id" => $user_id));
		if ( $stmt-> rowCount() > 0 ) {
			$_SESSION['daily'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		//////////////////////////////////////////////////////////////////////////////////////////////////
		
		/////タグの情報を取得/////////////////////////////////////////////////////////////////////////////
		$sql = "select tags from user_data where user_id = :instagram_user_id order by created desc limit 20";
		$stmt = $dbh->prepare($sql);
		//$stmt->execute();
		$stmt->execute(array(":instagram_user_id" => $user_id));
		if($stmt->rowCount() > 0 ){
			$alldata = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$array_all = array();
			foreach($alldata as $data) {
				if( !empty($data['tags']) ){
					$array = explode( "," , $data['tags'] );
					$array_all =array_merge($array_all,$array);
				}
			}
			if( !empty($array_all) ) {
				$tmp_array = array_unique($array_all);
				unset($tmp_array[0]);
				$_SESSION['tags'] = array_merge($tmp_array);
				//$_SESSION['tags'] = array_merge(array_unique($array_all));
			}
		}
		///////////////////////////////////////////////////////////////////////////////////////////////////
	}else{ //本データベースにユーザー情報がない場合はキャッシュデータベースの確認
		
	}
}

//generate pager url
function pager_url_generator($_page){
	//$ret = SITE_URL;
	$ret = "?id=".$_GET['id'];
	if( !empty($_GET['day']) ){
		$ret .= "&day=".h($_GET["day"])."&page=".h($_page);
	}elseif(  !empty($_GET['mon']) ){
		$ret .= "&mon=".h($_GET["mon"])."&page=".h($_page);
	}elseif(  !empty($_GET['tag']) ){
		$ret .= "&tag=".h($_GET["tag"])."&page=".h($_page);
	}elseif(  !empty($_GET['word']) ){
		$ret .= "&word=".h($_GET["word"])."&page=".h($_page);
	}else{
		$ret .= "&page=".h($_page);
	}
	if( !empty($_GET['media']) ){
		$ret .= "&media=".$_GET['media'];
	}
	return $ret;
}
function prev_url_generator($arg_page){
	return pager_url_generator($arg_page-1);
}
function next_url_generator($arg_page){
	return pager_url_generator($arg_page+1);
}

//generate media url
function media_url_generator($_media_mode){
	$ret = "?id=".$_GET['id'];
	if( !empty($_GET['day']) ){
		$ret .= "&day=".h($_GET["day"]);
	}elseif(  !empty($_GET['mon']) ){
		$ret .= "&mon=".h($_GET["mon"]);
	}elseif(  !empty($_GET['tag']) ){
		$ret .= "&tag=".h($_GET["tag"]);
	}elseif(  !empty($_GET['word']) ){
		$ret .= "&word=".h($_GET["word"]);
	}else{
		switch($_media_mode) {
			case 1:  // only image
				$ret .= h("&media=i");
				break;
			case 2:  // only movie
				$ret .= h("&media=m");
				break;
			case 0:  // both
			default:
				break;
		}
		return $ret;
	}
	
	switch($_media_mode) {
		case 1:  // only image
			$ret .= h("&media=i");
			break;
		case 2:  // only movie
			$ret .= h("&media=m");
			break;
		case 0:  // both
		default:
			break;
	}
	return $ret;
}

//media active check
function media_active_check($media){
	$ret = "";
	switch($media) {
		case 0: /*both*/
			if( empty($_GET['media']) ) {
				$ret = "active";
			} 
			break;
		case 1: /* only image */
			if ( strcmp($_GET['media'],'i') === 0){
				$ret = "active";
			}
			break;
		case 2:
			if ( strcmp($_GET['media'],'m') === 0){
				$ret = "active";
			}
			break;
		default:
			break;
	}
	return $ret;
}

//日付をY-m-dにして返す。
function headline_display($str) {
	return date("Y-m-d",strtotime($str));
}

//$_GETの文字を返す関数。
function get_chars_currentpage()
{
	$ret = "";
	if( !empty($_GET['day']) ){
		$ret = $_GET['day'];
	}elseif(  !empty($_GET['mon']) ){
		$ret = $_GET['mon'];
	}elseif(  !empty($_GET['tag']) ){
		$ret = "#".$_GET['tag'];
	}elseif(  !empty($_GET['word']) ){
		$ret = "'".$_GET['word']."'";
	}else{
		;
	}
	return $ret;
}
