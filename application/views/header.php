<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<!--  <link rel="shortcut icon" href="favicon.ico" />-->
  <title><?php if(isset($tagdesc)){echo (!empty($tagdesc['title']) ? $tagdesc['title']." — ":'').$tagdesc['name']." — ";}?><?php echo $setting['sitetitle'];?></title>
  <meta name="keywords" content="<?php echo $setting['sitekeywords'];?>" />
  <meta name="description" content="<?php echo $setting['sitedescription'];?>" />
  <script src="static/js/jquery.js"></script>
  <link href="static/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script src="static/bootstrap/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="static/css/main.css">
  <script src="static/layer/layer.js"></script>
  <script src="static/js/main.js"></script>
</head>
<body>
<header class="topbar">
	<div class="container">
	  <div class="row">
	  	<div class="col-md-12">
<!--	    	<a href="./"><img src="static/images/logo.png" border="0"></a>-->

	    	<a href="./" style="font-size: 20px;">淮北选煤厂</a>
            <div class="pull-right">
                <?php
                if(empty($_SESSION['zibbs_user'])){	?>
                    <a class="btn btn-warning loginbtn" href="javascript:;">立即登录</a>
<!--                    <a class="btn btn-success registerbtn" href="javascript:;">立即注册</a>-->
                <?php	}else{	?>
                    <!--		  		<a href="./index.php?route=index/post" class="btn btn-success" style="margin-right:20px;">发表主题</a>-->
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
<!--                        <img src="--><?php //echo $avatar;?><!--" border="0" style="border-radius:50%;width:20px;">-->
                        <span style="font-size:16px;position:relative;left:4px;top:4px;">当前用户：<?php echo $_SESSION['zibbs_user']; ?></span> </a>
                    <?php	if(!empty($msgmark) && $msgmark==1 ){	?>
                        &nbsp;<a href="./index.php?route=msg/showmsg"><span class="badge badge-info">新消息</span></a>
                    <?php	}	?>
                    <ul class="dropdown-menu">
                        <? if($_SESSION['type']){	?>
                            <li><a href="./index.php?route=admin/login">后台管理</a></li>
                        <?}?>
                        <li><a href="./index.php?route=index/mypub">个人中心</a></li>
                        <li><a href="./index.php?route=common/logout">安全退出</a></li>
                    </ul>
                <?php	}	?>
            </div>
		  </div>
		  <div class="col-md-4"></div>

	  </div>
	  <?php
	  if(empty($_GET['route']) || $_GET['route'] != 'index/post'){	?>
	  <hr>
	  <div class="row alltags" style="padding-left: 15px;padding-right: 15px">

	  	<a class="label label-warning" <?php if(empty($tagdesc)){	?>style="color:#ad3a37"<?php	}	?> href="<?php echo $setting['siteurl'];?>">全部</a>
	  	<?php
	  	if(!empty($showtags)){
	  		foreach($showtags as $tag){	?>
	  			<a class="label label-info" <?php if(isset($tagdesc) && $tagdesc['id']==$tag['id']){	?>style="color:#ad3a37"<?php	}	?> href="./index.php?route=index/tag&t=<?php echo $tag['id'];?>"><?php echo $tag['name'];?></a>
	  	<?php	}}	?>
          <?php if($_SESSION['status'] == 2){?>
          <a href="javascript:void(0)" onclick="test();" class="label label-success" style="float:right;" >发表主题</a>
              <?php } else {?>
          <a href="./index.php?route=index/post" class="label label-success" style="float:right;">发表主题</a>
          <?php }?>
	  </div>
		<?php	}	?>
	</div>
</header>
<script>
    function test(){
        alert("您已被禁言");
    }
</script>
