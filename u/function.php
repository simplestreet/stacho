<?php
session_start();
set_time_limit(120);

function h($s){
	return htmlspecialchars($s,ENT_QUOTES | ENT_HTML5 | ENT_DISALLOWED,"UTF-8");
}

function getCaptionWithLink($str,$user_name){
	$str = preg_replace("/(?:^|[^ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9&_\/]+)[#＃]([ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z]+[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*)/u", " <a href=\"".SITE_URL."u/?id=".$user_name."&tag=\\1\">#\\1</a>", $str);
	return $str;
}
//自分のページか確認
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
//登録ユーザーがキャッシュデータベースに存在するかチェックする関数
function existCacheUserInfo($dbh,$user_name){
	$sql = "select instagram_user_id from cache_users where instagram_user_name = :instagram_user_name";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":instagram_user_name" => $user_name));
	if ( $stmt-> rowCount() > 0 ) {
		$ret = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $ret[0]['instagram_user_id'];
	}else{
		return false;
	}
	
}

//登録ユーザーが本データベースに存在するかチェックする関数
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
function get_userinfo_contents($dbh,$user_id,$cache_flag){
	if($cache_flag){
		$users = " cache_users";
		$user_data = " cache_user_data";
		$_SESSION['cache'] = true;
	}else{
		$users = " users";
		$user_data = " user_data";
		$_SESSION['cache'] = false;
	}

	////全ユーザー情報更新////////////////////////////////////////////////////////////////////////////
	//$sql = "select instagram_user_id,instagram_user_name,full_name,instagram_profile_picture,bio,website,media,follows,followed_by from users where instagram_user_id = :instagram_user_id";
	$sql = "select instagram_user_id,instagram_user_name,full_name,instagram_profile_picture,bio,website,media,follows,followed_by from".$users." where instagram_user_id = :instagram_user_id";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":instagram_user_id" => $user_id));
	if ( $stmt-> rowCount() > 0 ) {
		$_SESSION['user_detail'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////
	
	/////月別のデータ統計/////////////////////////////////////////////////////////////////////////////
	//$sql = "select date_format(created,'%Y-%m') as created,count(*) as count from user_data where user_id = :instagram_user_id group by date_format(created,'%Y%m') order by created desc";
	$sql = "select date_format(created,'%Y-%m') as created,count(*) as count from".$user_data." where user_id = :instagram_user_id group by date_format(created,'%Y%m') order by created desc";
	$stmt = $dbh->prepare($sql);
	//$stmt->execute();
	$stmt->execute(array(":instagram_user_id" => $user_id));
	if ( $stmt-> rowCount() > 0 ) {
		$_SESSION['monthly'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////
	
	/////日別のデータ統計/////////////////////////////////////////////////////////////////////////////
	//$sql = "select date_format(created,'%Y-%m-%d') as created,count(*) as count from user_data where user_id = :instagram_user_id group by date_format(created,'%Y%m%d') order by created desc";
	$sql = "select date_format(created,'%Y-%m-%d') as created,count(*) as count from".$user_data." where user_id = :instagram_user_id group by date_format(created,'%Y%m%d') order by created desc";
	$stmt = $dbh->prepare($sql);
	//$stmt->execute();
	$stmt->execute(array(":instagram_user_id" => $user_id));
	if ( $stmt-> rowCount() > 0 ) {
		$_SESSION['daily'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////
	
	/////タグの情報を取得/////////////////////////////////////////////////////////////////////////////
	//$sql = "select tags from user_data where user_id = :instagram_user_id order by created desc limit 20";
	$sql = "select tags from".$user_data." where user_id = :instagram_user_id order by created desc limit 20";
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
	
	/////Poplularの情報を取得/////////////////////////////////////////////////////////////////////////////
	/*$popular_list = array();
	$popular_names = array("fumio0728","kikichiaki","yuri_ebihara","nyanchan22","yuk00shima");
	foreach($popular_names as $name){
		$sql = "select from".$user_data." where ";
		$popular_list[] = "";
	}
	exit;*/
	/*$_SESSION['popular'] = ;*/
	
	//////////////////////////////////////////////////////////////////////////////////////////////////
}
function get_access_token($dbh){
	$sql = "select count(*) from users";
	$stmt = $dbh->query($sql);
	
	//乱数生成
	$rand = $stmt->fetchColumn() - 1;
	$rand = mt_rand(0,$rand);
	
	$sql = "select id,instagram_access_token from users limit ".$rand.",1";
	$stmt = $dbh->query($sql);
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $result[0]['instagram_access_token'];
}
function get_cachemedia_from_instagram( $dbh , $user_id , $access_token ){
	$stmt_cnt = $dbh->prepare("select count(*) from cache_user_data where user_id = :user_id");
	$stmt_cnt->execute(array(":user_id"=>$user_id));
	if($stmt_cnt->fetchColumn() < 100 ){
		$sql = "select user_id,image_id,created from cache_user_data where user_id = :user_id order by created desc limit 1";
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array(":user_id" => $user_id));

		if( $stmt->rowCount() > 0 ){
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$url = "https://api.instagram.com/v1/users/".$user_id."/media/recent/?access_token=".$_SESSION['user']['instagram_access_token']."&min_id=".$result[0]["image_id"];
		} else {
			$url = "https://api.instagram.com/v1/users/".$user_id."/media/recent/?access_token=".$_SESSION['user']['instagram_access_token'];
		}
		
		//100件分のメディア情報の取得////////////////////////////////////////////////////////////////////
		$url = "https://api.instagram.com/v1/users/".$user_id."/media/recent/?access_token=".$access_token;
		$json = file_get_contents($url);
		if( !$json ){
			return false;
		}
		$json = json_decode($json);

		for($count = 0; $count < 5; $count++){
			foreach($json->data as $data){
				$sql = "insert into cache_user_data(user_id,image_id,image_url,link,caption,tags,video,created) values(:user_id,
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
					return false;
				}
				$json = json_decode($json);
			}
		}
	}
	return true;
}

function get_userinfo($dbh,$user_name){
	
	//登録ユーザーが本データベースに存在するかチェック
	if($user_id = existUserInfo($dbh,$user_name)){
		/* Sessionがあり、ログイン本人と表示するuserが一緒の場合は、最新のデータを取ってくる。
		if (!empty($_SESSION['user'])){
			if( strcmp($_SESSION['user']['instagram_user_name'],$user_name) === 0){
			}
		} */
		
		/* user情報の取得 */
		get_userinfo_contents($dbh,$user_id,false);
	}else{ //本データベースにユーザー情報がない場合はキャッシュデータベースの確認
		
		$access_token = get_access_token($dbh);
		
		//キャッシュデータベースに存在するか確認
		if($user_id = existCacheUserInfo($dbh,$user_name)){
			/* 100件未満の場合データベースに情報を登録  */
			if(!get_cachemedia_from_instagram($dbh,$user_id,$access_token)){
				//エラーの場合の処理
				if(isset($_SESSION['user_detail'])){
					unset($_SESSION['user_detail']);
				}
				return;
			}
			get_userinfo_contents($dbh,$user_id,true);
		}else{
			//キャッシュデータベースに存在しない場合
			//存在するユーザーかを確認
			$url = "https://api.instagram.com/v1/users/search?q=".$user_name."&count=1&access_token=".$access_token;
			$json = file_get_contents($url);
			$json = json_decode($json);
			if(!empty($json->data)){
				//ユーザーが存在した場合の処理
				//全ユーザー情報更新////////////////////////////////////////////////////////////////////////////
				$url = "https://api.instagram.com/v1/users/".$json->data[0]->id."/?access_token=".$access_token;
				$json = file_get_contents($url);
				if(!$json){
					//エラーの場合の処理
					if(isset($_SESSION['user_detail'])){
						unset($_SESSION['user_detail']);
					}
					return;
				}
				$json = json_decode($json);
				$data = $json->data;
				
				//キャッシュデータベースに保存
				$sql = "insert into cache_users(instagram_user_id,instagram_user_name,full_name,instagram_profile_picture,bio,website,media,follows,followed_by,created,modified) values
						(:instagram_user_id,:instagram_user_name,:full_name,:instagram_profile_picture,:bio,:website,:media,:follows,:followed_by,now(),now())";
				$stmt = $dbh->prepare($sql);
				$params = array(
							":instagram_user_id" => $data->id,
							":instagram_user_name" => $data->username,
							":full_name" => $data->full_name,
							":instagram_profile_picture" => $data->profile_picture,
							":bio" => $data->bio,
							":website" => $data->website,
							":media" => $data->counts->media,
							":follows" => $data->counts->follows,
							":followed_by" => $data->counts->followed_by);
				$stmt->execute($params);
				/////////////////////////////////////////////////////////////////////////////////////////////////
				
				$user_id = $data->id;
				
				//100件分のメディア情報の取得////////////////////////////////////////////////////////////////////
				if(!get_cachemedia_from_instagram($dbh,$user_id,$access_token)){
					//エラーの場合の処理
					if(isset($_SESSION['user_detail'])){
						unset($_SESSION['user_detail']);
					}
					return;
				}
				/*
				$url = "https://api.instagram.com/v1/users/".$user_id."/media/recent/?access_token=".$access_token;
				$json = file_get_contents($url);
				$json = json_decode($json);
	
				for($count = 0; $count < 5; $count++){
					foreach($json->data as $data){
						$sql = "insert into cache_user_data(user_id,image_id,image_url,link,caption,tags,video,created) values(:user_id,
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
						$json = json_decode($json);
					}
				}
				*/
				/////////////////////////////////////////////////////////////////////////////////////////////////
				/* user情報の取得 */
				get_userinfo_contents($dbh,$user_id,true);
			}else{
				//ユーザーが存在しない場合の処理
				if(isset($_SESSION['user_detail'])){
					unset($_SESSION['user_detail']);
				}
			}
		}
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
