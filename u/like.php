<?php
require_once('config.php');

session_start();


function h($s){
	return htmlspecialchars($s,ENT_QUOTES,"UTF-8");
}

// login check
if (empty($_SESSION['user'])){
	header('Location: '.SITE_URL.'u/login.php');
	exit;
}
	//全ユーザー情報更新
	$url = "https://api.instagram.com/v1/users/self/media/liked?access_token=".$_SESSION['user']['instagram_access_token'];
	$json = file_get_contents($url);
	$json = json_decode($json);

	
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Like page - Stacho</title>
<link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="../css/base.css" rel="stylesheet" type="text/css">
<link href="../css/common.css" rel="stylesheet" type="text/css">
<link href="../css/like.css" rel="stylesheet" type="text/css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
		$(function(){
			var agent = navigator.userAgent;
			if(agent.search(/iPhone/) != -1 || agent.search(/iPad/) != -1 || agent.search(/iPod/) != -1 || agent.search(/Android/) != -1)
			{
				$('#back-to-top a i').attr("class","fa fa-arrow-circle-up fa-5x");
			}
			
			$('#back-to-top').hide();
			
			$(window).scroll(function() {
				if( $(this).scrollTop() > 60 ){
					$('#back-to-top').fadeIn();
				}else{
					$('#back-to-top').fadeOut();
				}
				$('#pos').text($(this).scrollTop());
			});
			$('#back-to-top a').click(function(){
				$('html,body').animate({
					scrollTop:0
				}, 500);
				return false;
			});
			var next_id;
			$('#more').click(function(){
				$('#loading').show();
				next_id = $('#next_id').val();
				$.get('more.php',{ 
					next_id:next_id
				},function(rs){
					$('#loading').hide();
					$('#next_id').remove();
					for(var i = 0; i < rs.data.length ; i++){
						$('#tweets').append('<li><a href="' + rs.data[i].link +'" target="_blank"><img src="' + rs.data[i].images.standard_resolution.url + '" width="280" height="280" alt="stacho image" /></a></li>');
					}
					if( rs.pagination.next_max_like_id ){
						$('#tweets').append('<input type="hidden" id="next_id" value="' + rs.pagination.next_max_like_id + '" />');
					}else{
						$('#more').remove();
					}
				});
				
			});
		});
</script>
</head>
<body>
<?php include_once("analyticstracking.php") ?>
<div id="container">
  <div id="header" class="clearfix">
    <div class="header-inner">
      <h1><a href="<?php echo h(SITE_URL); ?>"><img src="../images/stacho_logo.png" width="140" height="" alt="Stacho"></a></h1>
      <p><a href="logout.php">logout</a></p>
    </div>
    <!-- /#header --></div>
  <div class="container-inner">
    <div id="main" class="clearfix">
      <div id="mainContents">
        <p class="return"><a href="<?php echo h(SITE_URL."u/?id=".$_SESSION['user']['instagram_user_name']); ?>"> &gt;&gt;<?php echo h($_SESSION['user']['instagram_user_name']); ?>のページへ戻る</a></p>
        <ul id="tweets" class="clearfix">
          <?php foreach($json->data as $data): ?>
			<li><a href="<?php echo $data->link; ?>" target="_blank"><img src="<?php echo $data->images->standard_resolution->url; ?>" width="280" height="280" alt="stacho image"/></a></li>
		  <?php endforeach;?>
		  <?php if(isset($json->pagination->next_max_like_id)) :?>
			<input type="hidden" id="next_id" value="<?php echo h($json->pagination->next_max_like_id); ?>" />
		  <?php endif; ?>
        </ul>
        <!-- #mainContents--></div>
      <div id="more-container">
      	<p id="loading" style="display:none;">loading...</p>
      	<?php if(isset($json->pagination->next_max_like_id)) :?>
        	<input type="button" id="more" value="もっと読む" />
        <?php endif; ?>
      </div>
      <div id="back-to-top"><a href="#"><i class="fa fa-arrow-circle-up fa-4x"></i></a></div>
      <!--#main--></div>
    <!-- #container-inner --></div>
</div>
</div>
</body>
</html>
