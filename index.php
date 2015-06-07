<?php
require_once('u/config.php');

session_start();
function get_login_url(){
	$ret = "";
	if(empty($_SESSION['user'])){
		$ret = SITE_URL.'u/login.php';
	}else{
		$ret = SITE_URL.'u/?id='.$_SESSION['user']['instagram_user_name'];
	}
	return $ret;
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Stacho - Instagramをもっと便利に。。。</title>
<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="css/lightbox.css" rel="stylesheet" type="text/css">
<link href="css/base.css" rel="stylesheet" type="text/css">
<link href="css/home.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="js/lightbox.min.js"></script>
	<script type="text/javascript">
		function validForm() {
			var formElem = document.getElementsByName("searchId");
			if( formElem[0].value == "" ) {
				return false;
			} else {
				//ascii 一文字は検索対象としない。
				if( unescape(encodeURIComponent(formElem[0].value)).length < 2) {
				return false;
				}
				location.href = "./u/?id=" + formElem[0].value;
				$('#loading').show();
				return true;
			}
		}
	</script>
</head>
<body>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-62866190-1', 'auto');
  ga('send', 'pageview');

</script>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/ja_JP/sdk.js#xfbml=1&version=v2.3";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
 <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
<div id="container">
  <div id="container-inner">
    <div id="main" class=" clearfix">
      <div id="home-left">
        <!-- <p class="image-box"><a href="images/sample.png" data-lightbox="image-1" data-title="Stacho sample"><img src="images/sample.png" width="300" height="" alt="sample image"/></a></p> -->
		<p class="image-box"><a href="./u/?id=baffaro66"><img src="images/sample.png" width="300" height="" alt="sample image"/></a></p>
        <h1>StachoはInstagramをブログ形式に表示するサービスです。</h1>
        <div id="user-search">
        	<p>ログする前に、とりあえず使ってみたい方はこちらから。↓↓↓</p>
			<form id="usearch" onsubmit="validForm();return false;" method="get">
				<input type="text" value="" name="searchId" maxlength="50" placeholder="Instagram ID">
				</input>
				<input type="submit" value="&#xf007;">
				</input>
			</form>
		</div>
		<p id="loading"><i class="fa fa-refresh fa-spin fa-3x"></i> Now logging...it may take about 2 minutes</p>
      </div>
      <div id="home-right">
        <p><img src="images/stacho_logo.png" width="150" height="" alt="Stacho"/></p>
        <p class="caption">Instagramをもっと便利に。。。</p>
        <p class="button"><a href="<?php echo get_login_url(); ?>">Login or 新規登録</a></p>
        <ul>
          <li><a href="https://twitter.com/share" class="twitter-share-button" data-url="http://stcho.xyz">Tweet</a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script></li>
          <li><a href="http://b.hatena.ne.jp/entry/stacho.xyz" class="hatena-bookmark-button" data-hatena-bookmark-title="Stacho - Instagramをもっと便利に。。。" data-hatena-bookmark-layout="standard-balloon" data-hatena-bookmark-lang="ja" title="このエントリーをはてなブックマークに追加"><img src="https://b.st-hatena.com/images/entry-button/button-only@2x.png" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a><script type="text/javascript" src="https://b.st-hatena.com/js/bookmark_button.js" charset="utf-8" async="async"></script></li>
          <li>
          	<div class="fb-like" data-href="http://stacho.xyz" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
          </li>
          <!--<li><a href="https://twitter.com/Stachosupport" class="twitter-follow-button" data-show-count="false">Follow @Stachosupport</a></li>-->
          <li>  <a class="twitter-timeline" href="https://twitter.com/Stachosupport" data-widget-id="597206517125750785">@Stachosupportさんのツイート</a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script> </li>
        </ul>
        
      <!-- /#home-right --></div>
    <!-- /#main --></div>
  <!-- /#container-inner --></div>
<!-- /#container --></div>
</body>
</html>
