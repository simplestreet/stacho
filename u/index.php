<?php
require_once('config.php');
require_once('function.php');
session_start();



// login check
/*
if (empty($_SESSION['user'])){
	header('Location: '.SITE_URL.'u/login.php');
	exit;
}
*/
define('MEDIAS_PER_PAGE' , 20 );

if(preg_match('/^[1-9][0-9]*$/',$_GET['page'])){
	$page = (int)$_GET['page'];
}else{
	$page = 1;
}

//user情報の格納
try{
	$dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASSWORD);
}catch(PDOEXception $e){
	echo $e->getMessage();
	exit;
}

//
// page offset count
// 1    0      20
// 2    20     20
// 3    40     20
// 4    60     20

//user情報の取得 ?id対応時に変更する。
if(isset($_GET['id'])){
	if(!empty($_SESSION['user_detail'][0])){
		if(strcmp($_GET['id'],$_SESSION['user_detail'][0]['instagram_user_name']) !== 0){
			get_userinfo($dbh,$_GET['id']);
		}
	}else{
		get_userinfo($dbh,$_GET['id']);
	}
}else{
	header('Location: '.SITE_URL);
}
if(isset($_SESSION['user_detail'])){
	//userの詳細なデータの受け渡し。
	$userdetail = $_SESSION['user_detail'][0];

	//cacheによるテーブルの指定
	if($_SESSION['cache']){
		$user_data = " cache_user_data";
	} else {
		$user_data = " user_data";
	}
	//表示するdataの検索
	$offset = MEDIAS_PER_PAGE * ($page -1);
	$sql_postscript = " limit ".$offset.",".MEDIAS_PER_PAGE;
	$order_by = " order by created desc";
	//$select = "select * from user_data";
	$select = "select * from".$user_data;
	if( !empty($_GET['tag']) ){
		$where = "";
		if(!empty($_GET['media']) ){
			if(strcmp($_GET['media'],'m') === 0){
				$where = " where user_id=:user_id and tags like :tags and video=1".$order_by;
			} else {
				$where = " where user_id=:user_id and tags like :tags and video=0".$order_by;
			}
		} else {
			$where = " where user_id=:user_id and tags like :tags".$order_by;
		}
		$stmt = $dbh->prepare($select.$where.$sql_postscript);
		$tag = '%,'.$_GET['tag'].',%';
		$params = array(":user_id" => /*$userdetail->id*/(int)$userdetail['instagram_user_id'],":tags" => $tag );
		$stmt->execute($params);
		
		//レコード数を取得
		//$stmt_cnt = $dbh->prepare("select count(*) from user_data".$where);
		$stmt_cnt = $dbh->prepare("select count(*) from".$user_data.$where);
		$stmt_cnt->execute($params);
		$total = $stmt_cnt->fetchColumn();
	} else if( !empty($_GET['day']) ){
		$day = $_GET['day']." 00:00:00";
		$nextday = date("Y-m-d H:i:s",strtotime($day." +1 day"));
		$where = "";
		if(!empty($_GET['media']) ){
			if(strcmp($_GET['media'],'m') === 0){
				$where = " where user_id=:user_id and created >= :day and created < :nextday and video=1".$order_by;
			} else {
				$where = " where user_id=:user_id and created >= :day and created < :nextday and video=0".$order_by;
			}
		} else {
			$where = " where user_id=:user_id and created >= :day and created < :nextday".$order_by;
		}
		$stmt = $dbh->prepare($select.$where.$sql_postscript);
		$params = array(":user_id" => /*$userdetail->id*/(int)$userdetail['instagram_user_id'],":day" => $day,":nextday" => $nextday);
		$stmt->execute($params);
		
		//レコード数を取得
		//$stmt_cnt = $dbh->prepare("select count(*) from user_data".$where);
		$stmt_cnt = $dbh->prepare("select count(*) from".$user_data.$where);
		$stmt_cnt->execute($params);
		$total = $stmt_cnt->fetchColumn();
	} else if( !empty($_GET['mon'])) {
		$mon = $_GET['mon']."-01 00:00:00";
		$nextmon = date("Y-m-d H:i:s",strtotime($mon." +1 month"));
		$where = "";
		if(!empty($_GET['media']) ){
			if(strcmp($_GET['media'],'m') === 0){
				$where = " where user_id=:user_id and created >= :mon and created < :nextmon and video=1".$order_by;
			} else {
				$where = " where user_id=:user_id and created >= :mon and created < :nextmon and video=0".$order_by;
			}
		} else {
			$where = " where user_id=:user_id and created >= :mon and created < :nextmon".$order_by;
		}
		$stmt = $dbh->prepare($select.$where.$sql_postscript);
		$params = array(":user_id" => (int)$userdetail['instagram_user_id'],":mon" => $mon,":nextmon" => $nextmon );
		$stmt->execute($params);
		
		//レコード数を取得
		//$stmt_cnt = $dbh->prepare("select count(*) from user_data".$where);
		$stmt_cnt = $dbh->prepare("select count(*) from".$user_data.$where);
		$stmt_cnt->execute($params);
		$total = $stmt_cnt->fetchColumn();

	} else if ( !empty($_GET['word']) ) {
		$words = explode(" ",str_replace("　"," ",$_GET['word']));
		$words_cnt = count($words);
		if( $words_cnt > 3 /*and検索最大数*/) {
			$words_cnt = 3;
		}
		$where = "";
		for($i = 0 ; $i < $words_cnt; $i++ ) {
			if( $i != 0 ) {
				$where .= " and";
			}
			$where = $where." caption like :wword$i";
		}
		$sql = $select." where user_id=:user_id and".$where.$order_by;
		$stmt = $dbh->prepare($sql.$sql_postscript);
		$params = array();
		$params[":user_id"] = /*$userdetail->id*/(int)$userdetail['instagram_user_id'];
		for($i = 0 ; $i < $words_cnt; $i++ ) {
			$params[":wword$i"] = '%'.$words[$i].'%';
		}
		$stmt->execute($params);
		
		//レコード数を取得
		//$sql = "select count(*) from user_data where user_id=:user_id and".$where.$order_by;
		$sql = "select count(*) from".$user_data." where user_id=:user_id and".$where.$order_by;
		$stmt_cnt = $dbh->prepare($sql);
		$stmt_cnt->execute($params);
		$total = $stmt_cnt->fetchColumn();
		
	} else {
		$where = "";
		if(!empty($_GET['media']) ){
			if(strcmp($_GET['media'],'m') === 0){
				$where = " where user_id=:user_id and video=1".$order_by;
			}else{
				$where = " where user_id=:user_id and video=0".$order_by;
			}
		}else{
			$where = " where user_id=:user_id".$order_by;
		}
		$stmt = $dbh->prepare($select.$where.$sql_postscript);
		$params = array(":user_id" => /*$userdetail->id*/(int)$userdetail['instagram_user_id']);
		$stmt->execute($params);
		
		//レコード数を取得
		//$stmt_cnt = $dbh->prepare("select count(*) from user_data".$where);
		$stmt_cnt = $dbh->prepare("select count(*) from".$user_data.$where);
		$stmt_cnt->execute($params);
		$total = $stmt_cnt->fetchColumn();
	}
	//検索データの取得
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$totalPages = ceil($total / MEDIAS_PER_PAGE);
	$from = $offset + 1;
	$to =($offset + MEDIAS_PER_PAGE) < $total ? ($offset + MEDIAS_PER_PAGE) : $total;
	
	$dbh = null;
	
	//月別、日別、tagsの統計情報をSESSIONより取得
	$daily = $_SESSION['daily'];
	$monthly = $_SESSION['monthly'];
	$tags = $_SESSION['tags'];
	
	//counter
	$cnt_daily = 0;
	$cnt_monthly = 0;
	$cnt_tags = 0;
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Stacho</title>
<link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="../css/base.css" rel="stylesheet" type="text/css">
<link href="../css/common.css" rel="stylesheet" type="text/css">
<link href="../css/index.css" rel="stylesheet" type="text/css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script type="text/javascript">
		function validForm() {
			var formElem = document.getElementsByName("word");
			var idElem = document.getElementsByName("id");
			if( formElem[0].value == "" ) {
				return false;
			} else {
				//ascii 一文字は検索対象としない。
				if( unescape(encodeURIComponent(formElem[0].value)).length < 2) {
				return false;
				}
				location.href = "./?id=" + idElem[0].value + "&word=" + formElem[0].value;
				return true;
			}
		}
		function validUserForm() {
			var formElem = document.getElementsByName("searchId");
			if( formElem[0].value == "" ) {
				return false;
			} else {
				//ascii 一文字は検索対象としない。
				if( unescape(encodeURIComponent(formElem[0].value)).length < 2) {
				return false;
				}
				location.href = "./?id=" + formElem[0].value;
				$('#loading').show();
				return true;
			}
		}
		$(function(){
			$('img').error(function(){
				$(this).parent().parent().remove();
			});
			$('#delete-logdata').click(function(){
				if(!confirm('ログされたデータを削除しますか？')){
					/* キャンセルの時の処理 */
					return false;
				}
			});
		});
	</script>
</head>

<body>
<?php include_once("analyticstracking.php") ?>
<div id="container">
  <div id="header" class="clearfix">
    <div class="header-inner">
      <h1><a href="<?php echo h(SITE_URL);?>"><img src="../images/stacho_logo.png" width="140" height="" alt="Stacho"></a></h1>
		<form id="usearch" onsubmit="validUserForm();return false;" method="get">
			<input type="text" value="" name="searchId" maxlength="50" placeholder="Instagram ID">
			</input>
			<input type="submit" value="&#xf007;">
			</input>
		</form>
		<?php if(!empty($_SESSION['user'])) :?>
			<p><a href="logout.php">logout</a></p>
		<?php else: ?>
			<p><a href="<?php echo h(SITE_URL."u/login.php"); ?>">login</a></p>
		<?php endif; ?>
    </div>
    <!--		<p><?php echo h($_SERVER["REQUEST_URI"]); ?></p>
		<p><?php echo h($_SERVER["PHP_SELF"]); ?></p>
		<p style="font:250%;color=blue;"><?php echo mb_internal_encoding(); ?></p>-->
    <!-- /#header --></div>
  <div class="container-inner">
  	<p id="loading"><i class="fa fa-refresh fa-spin fa-3x"></i> Now logging...it may take about 2 minutes</p>
  	<?php if(isset($_SESSION['user_detail'])): ?>
    <div id="userprofile" class="clearfix">
      <div id="userprofile-left">
        <p><img src="<?php echo h($userdetail['instagram_profile_picture']); ?>" width="140" height="140" alt="profile image"/></p>
        <p class="id"><a href="https://instagram.com/<?php echo h($userdetail['instagram_user_name']); ?>/" target="_blank"><?php echo h($userdetail['instagram_user_name']); ?></a></p>
      </div>
      <div id="userprofile-right">
        <dl>
          <dt>fullname:</dt>
          <?php if( !empty($userdetail['full_name']) ) : ?>
          	<dd><?php echo h($userdetail['full_name']); ?></dd>
          <?php else: ?>
          	<dd> ... </dd>
          <?php endif; ?>
          <dt>biography:</dt>
          <?php if( !empty($userdetail['bio']) ) : ?>
          	<dd><?php echo h($userdetail['bio']); ?></dd>
          <?php else: ?>
          	<dd> ... </dd>
          <?php endif; ?>
          <dt>website:</dt>
          <dd><a href="<?php echo h($userdetail['website']); ?>" target="_blank"><?php echo h($userdetail['website']); ?></a></dd>
        </dl>
        <ul>
          <li class="posts"><span class="number"><?php echo h($userdetail['media']); ?></span> posts</li>
          <?php if( !empty($_SESSION['user']) ) :?>
          	<li class="followers"><a href="<?php echo h(SITE_URL."u/followed.php"); ?>"><span class="number"><?php echo h($userdetail['followed_by']); ?></span> followers</a></li>
          	<li class="following"><a href="<?php echo h(SITE_URL."u/follows.php"); ?>"><span class="number"><?php echo h($userdetail['follows']); ?></span> following</a></li>
          <?php else : ?>
          	<li class="followers"><a href="" class="nosession"><span class="number"><?php echo h($userdetail['followed_by']); ?></span> followers</a></li>
          	<li class="following"><a href="" class="nosession"><span class="number"><?php echo h($userdetail['follows']); ?></span> following</a></li>
          <?php endif; ?>
          <li class="like"><a href="./like.php" class="<?php if(!isMyPage()){echo h("nonactive");}?>"><i class="fa fa-heart fa-3x"></i></a></li>
        </ul>
      </div>
      <!-- /#userprofile --></div>
    <div id="main" class="clearfix">
    	<ul id="current_page" class="clearfix">
    		<li><a href="<?php echo h(SITE_URL); ?>"><i class="fa fa-home fa-2x"></i></a></li>
    		<li> <i class="fa fa-angle-right fa-2x"></i> </li>
    		<li><a href="<?php echo h(SITE_URL."u/?id=".$userdetail['instagram_user_name']); ?>"><?php echo h($userdetail['instagram_user_name']);?></a></li>
    		<?php $chars_currentpage = get_chars_currentpage(); if(!empty($chars_currentpage)): ?>
    			<li> <i class="fa fa-angle-right fa-2x"></i> </li>
    			<li><?php echo h($chars_currentpage);?></li>
    		<?php endif; ?>
    	</ul>
      <div id="mainContents">
      	<?php if( empty($_GET['word']) ): ?>
      		<dl class="clearfix">
      	<?php else:?>
      		<dl class="nonactive clearfix">
      	<?php endif;?>
          <dt class="mediacaption">media type：</dt>
          <dd><a href="<?php echo media_url_generator(0); ?>" class="<?php echo media_active_check(0); ?>">both</a></dd>
          <dd><a href="<?php echo media_url_generator(1); ?>" class="<?php echo media_active_check(1); ?>">only image</a></dd>
          <dd><a href="<?php echo media_url_generator(2); ?>" class="<?php echo media_active_check(2); ?>">only movie</a></dd>
        </dl>
        <div class="ad1"> 
          <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script> 
          <!-- stacho_main_ad1 --> 
          <ins class="adsbygoogle"
     style="display:inline-block;width:468px;height:60px"
     data-ad-client="ca-pub-7393795767652133"
     data-ad-slot="8404886408"></ins> 
          <script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script> 
          <!-- /.ads--></div>
        <?php if($_SESSION['cache']) : ?>
				<div id="cacheMsg">
					<p><i class="fa fa-info-circle"></i> このユーザーはStachoに登録していません。</p>
					<p>現在100件のデータのみをキャッシュしております。このデータは24時間後に削除されます。</p>
			</div>
        <?php endif; ?>
		<ul class="pager clearfix">
			<?php if($page > 1) :?>
				<li><a href="<?php echo prev_url_generator($page); ?>">&lt; prev</a></li>
			<?php else: ?>
				<li class="nonactive"><a href="./">&lt; prev</a></li>
			<?php endif; ?>
			<li><a href="./<?php echo h("?id=".$userdetail['instagram_user_name']);?>"><?php echo h($userdetail['instagram_user_name']); ?></a></li>
			<?php if($page < $totalPages): ?>
				<li><a href="<?php echo next_url_generator($page); ?>">next &gt;</a></li>
			<?php else: ?>
				<li class="nonactive"><a href="./">next &gt;</a></li>
			<?php endif; ?>
		</ul>
		<div id="medias">
			<?php foreach ($result as $data) : ?>
				<?php if(($ymddate = headline_display($data['created'])) != $GLOBALS['cnt_date'] ): ?>
					<h2><?php echo h($ymddate); ?></h2>
					<?php $GLOBALS['cnt_date'] = $ymddate ?>
				<?php endif; ?>
				<div class="item-box clearfix">
					<?php if($data['video']): ?>
						<div class="media">
							<video controls width="180" height="180">
								<source src="<?php echo h($data['image_url']); ?>" />
								<p>動画を再生するには、videoタグをサポートしたブラウザが必要です。</p>
							</video>
						<!-- /.media --></div>
					<?php else : ?>
						<div class="media">
							<a href="<?php echo h($data['link']); ?>" target="_blank" ><img src="<?php echo h($data['image_url']); ?>" width="180" height="180" alt="media"/></a>
						<!-- /.media--></div>
					<?php endif; ?>
					<div class="description">
						<h3><a href="<?php echo h($data['link']); ?>" target="_blank">posted at <?php echo h($data['created']); ?> <i class="fa fa-external-link"></i></a></h3>
						<p><?php echo nl2br(getCaptionWithLink($data['caption'],$userdetail['instagram_user_name'])); ?></p>
					<!-- /.description--></div>
				<!-- /.item-box --></div>
			<?php endforeach; ?>
		<!-- /#medias --></div>
		<ul class="pager clearfix">
			<?php if($page > 1) :?>
				<li><a href="<?php echo prev_url_generator($page); ?>">&lt; prev</a></li>
			<?php else: ?>
				<li class="nonactive"><a href="./">&lt; prev</a></li>
			<?php endif; ?>
			<li><a href="./<?php echo h("?id=".$userdetail['instagram_user_name']);?>"><?php echo h($userdetail['instagram_user_name']); ?></a></li>
			<?php if($page < $totalPages): ?>
				<li><a href="<?php echo next_url_generator($page); ?>">next &gt;</a></li>
			<?php else: ?>
				<li class="nonactive"><a href="./">next &gt;</a></li>
			<?php endif; ?>
		</ul>
        <div class="ad2"> 
          <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script> 
          <!-- stacho_main_ad2 --> 
          <ins class="adsbygoogle"
     style="display:inline-block;width:336px;height:280px"
     data-ad-client="ca-pub-7393795767652133"
     data-ad-slot="5172218404"></ins> 
          <script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script> 
          <!-- /.ad2--></div>
        <!-- /#mainContents --></div>
      <div id="sidemenu">
		<div class="item-box"> 
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<!-- stacho_main_ad3 -->
			<ins class="adsbygoogle"
			     style="display:inline-block;width:300px;height:250px"
			     data-ad-client="ca-pub-7393795767652133"
			     data-ad-slot="8052816002"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
        <!-- /#sidemenu--></div>
		<div class="item-box">
          <h3>Search</h3>
          <!--<p>searchbox<i class="fa fa-search"></i></p>-->
          <form id="wsearch" onsubmit="validForm();return false;" method="get">
          	<input type="hidden" value="<?php echo h($_GET['id']);?>" name="id"></input>
            <input type="text" value="" name="word" maxlength="50">
            </input>
            <input type="submit" value="&#xf002;">
            </input>
          </form>
          <!-- /.item-box --></div>
      	<div class="item-box">
      		<h3>Popular</h3>
      		<p><a style="diplay:block;" href="./?id=fumio0728"><img src="https://instagramimages-a.akamaihd.net/profiles/profile_2823122_75sq_1374591049.jpg" width="50" height="50" /> No.1: * fumio *</a></p>
      		<p><a style="diplay:block;" href="./?id=kikichiaki"><img src="https://igcdn-photos-f-a.akamaihd.net/hphotos-ak-xpf1/t51.2885-19/1660811_1562667210638157_346481196_a.jpg" width="50" height="50" /> No.2: 伊藤 千晃 （AAA）</a></p>
      		<p><a style="diplay:block;" href="./?id=yuri_ebihara"><img src="https://instagramimages-a.akamaihd.net/profiles/profile_14204819_75sq_1374907016.jpg" width="50" height="50" /> No.3: YURI_EBIHARA</a></p>
      		<p><a style="diplay:block;" href="./?id=nyanchan22"><img src="https://igcdn-photos-e-a.akamaihd.net/hphotos-ak-xap1/t51.2885-19/10684131_705357866178940_1655275906_a.jpg" width="50" height="50" /> No.4: Haruna Kojima</a></p>
      		<p><a style="diplay:block;" href="./?id=yuk00shima"><img src="https://igcdn-photos-g-a.akamaihd.net/hphotos-ak-xfp1/t51.2885-19/10448839_502902489838630_130059946_a.jpg" width="50" height="50" /> No.5: yuk00shima</a></p>
      	<!-- /.item-box --></div>
        <div class="item-box">
          <h3>Recent</h3>
			<?php foreach($daily as $data) : ?>
				<?php if($cnt_daily >= 10): ?>
					<?php break; ?>
				<?php endif; ?>
				<?php if( !empty($_GET['day']) && strcmp($data['created'],$_GET['day']) === 0 ) : ?>
					<p><a href="" class="active" ><?php echo h($data['created']); ?> <span class="item-num"><?php echo h($data['count']); ?></span></a></p>
				<?php else: ?>
					<p><a href="<?php echo '?id='.$_GET['id'].'&day='.h($data['created']).(!empty($_GET['media']) ? "&media=".$_GET['media'] : "" ); ?>"><?php echo h($data['created']); ?> <span class="item-num"><?php echo h($data['count']); ?></span></a></p>
				<?php endif; ?>
			<?php $cnt_daily++; endforeach; ?>
          <!-- /.item-box --></div>
        <div class="item-box">
          <h3>Monthly</h3>
			<?php foreach($monthly as $data) : ?>
				<?php if( !empty($_GET['mon']) && strcmp($data['created'],$_GET['mon']) === 0 ) : ?>
					<p><a href="" class="active"><?php echo h($data['created']); ?> <span class="item-num"><?php echo h($data['count']); ?></span></a></p>
				<?php else: ?>
					<p><a href="<?php echo '?id='.$_GET['id'].'&mon='.h($data['created']).(!empty($_GET['media']) ? "&media=".$_GET['media'] : "" ); ?>"><?php echo h($data['created']); ?> <span class="item-num"><?php echo h($data['count']); ?></span></a></p>
				<?php endif; ?>
			<?php $cnt_monthly++; endforeach; ?>
          <!-- /.item-box --></div>
        <div class="item-box">
          <h3>Tags</h3>
			<?php foreach($tags as $tag) : ?>
				<?php if($cnt_tags >= 15): ?>
					<?php break; ?>
				<?php endif; ?>
				<?php if( !empty($_GET['tag']) && strcmp($tag,$_GET['tag']) === 0 ) : ?>
					<p><a href="" class="active" ><?php echo h("#".$tag); ?></a></p>
				<?php else: ?>
					<p><a href="?id=<?php echo h($_GET['id']); ?>&tag=<?php echo h($tag).(!empty($_GET['media']) ? "&media=".$_GET['media'] : "" ); ?>"><?php echo h("#".$tag); ?></a></p>
				<?php endif; ?>
			<?php $cnt_tags++; endforeach; ?>
          <!-- /.item-box --></div>
        <!-- /#sidemenu --></div>
      <!-- /#menu --></div>
      <?php else : ?>
      	<div id="nouser" style="min-height:200px;">
      		<div id="noUser">
      			<div id="boxAttention">
					<p><i class="fa fa-instagram"></i> ユーザーが見つかりませんでした。</p>
				</div>
			</div>
		</div>
      <?php endif; ?>
  <!-- /#container-inner --></div>
  <div id="footer">
    <div class="footer-inner">
      <ul class="clearfix">
        <li><a>このサイトについて</a></li>
        /
        <li><a>ヘルプ</a></li>
        /
        <li><a>利用規約</a></li>
        /
        <?php if(isMyPage()) : ?><li><a id="delete-logdata" href="delete.php">全ログデータの削除</a></li><?php endif; ?>
      </ul>
      <address>
      Copyright(C) 2015 simplestreet All rights Reserved.
      </address>
    </div>
  </div>
</div>
</body>
</html>
