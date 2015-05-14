<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>stacho mypage</title>
<link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="../css/base.css" rel="stylesheet" type="text/css">
<link href="../css/common.css" rel="stylesheet" type="text/css">
<link href="../css/login.css" rel="stylesheet" type="text/css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
	$(function(){
		$('#login').click(function(){
			$('#loading').show();
			$('.button').hide();
		});
	});
</script>
</head>

<body>
<?php include_once("analyticstracking.php") ?>
<div id="container">
  <div id="header" class="clearfix">
    <div class="header-inner">
      <h1><a href="/"><img src="../images/stacho_logo.png" width="140" height="" alt="Stacho"></a></h1>
    </div>
    <!-- /#header --></div>
  <div class="container-inner">
    <div id="main" class="clearfix">
      <div id="mainContents">
  <h2>ログイン前のご注意</h2>
        <ul id="cautionlist">
          <li>- 初回ログイン時は新規登録として、最大300件のメディア(画像、動画)情報を登録します。</li>
          <li>- 登録した情報は第三者からも閲覧できるかたちで、公開されます。<br />
            (2015/05/02時点ではログインした本人のメディア情報しか閲覧できません。)</li>
          <li>- 非公開アカウントにしている場合、本サービスをご利用いただけません。</li>
          <li>- 本サービスは、事前の予告なくサービス内容を変更、停止する場合があります。</li>
        </ul>
        <h2>Instagramの認証</h2>
        <div id="description">
  <p> Stachoにログインするには、Instagramによる認証が必要です。</p>
          <p> 下記の「Login with Instagram」を押すと、Instagram情報へのアクセス許可を求める画面があらわれますので、Authorize(承認する)を選択してください。</p>
          <p> なお、Authorizeした場合でもメディア(画像、動画)情報への参照しか許可されず、書き込み操作(いいねをする、フォローをする、メディアを投稿する)は本サービスStachoからは行いません。(*)</p>
          <p></p>
          <p id="loading" style="display:none;color:#00f;font-size:120%;"><i class="fa fa-spinner fa-spin fa-3x"></i> Now logging...it may take about 2 minutes</p>
          <p class="button"><a id="login" href="./redirect.php">Login with Instagram <i class="fa fa-instagram"></i></a></p>
          <p></p>
          <p>*) Authentication - Scope (Permissions)</p>
          <p><a href="https://instagram.com/developer/authentication/" target="_blank">https://instagram.com/developer/authentication/</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
