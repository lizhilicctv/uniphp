<?php $code=$this->gets['code']; $msg=$this->gets['msg']; $url=$this->gets['url']; $wait=$this->gets['wait'];?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <title>跳转提示</title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #fff; font-family: "Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif; color: #333; font-size: 16px;  background:url("timg.jpg");}
        .system-message{background:#f1f1f1;padding: 24px 48px; margin: 20px auto;width: 340px; text-align: center;}
        .system-message h2 img {width: 50%;	padding: 20px;}
		.system-message .tip {font-size: 26px;line-height: 38px;}
		.system-message .jump {padding-top: 10px;line-height: 32px;}
		.system-message .jump .red {color: red;}
		.system-message .jump a {color: #333;}
		.system-message .success,.system-message .error {line-height: 1.8em;font-size: 36px;}
		.system-message .detail {font-size: 12px;line-height: 20px;	margin-top: 12px;display: none;}
    </style>
</head>
<body>
    <div class="system-message">
		<?php switch ($code){ 		
			case 1:	?>
			<p class="tip">温馨提示</p>
			<h2><img src="/static/success.png"></h2>
			<p class="success"><?php echo(strip_tags($msg));?></p>
			<?php break;?>
			<?php case 0:?>
		 <p class="tip">温馨提示</p>
		 <h2><img src="/static/error.png"></h2>
		 <p class="error"><?php echo(strip_tags($msg));?></p>
		 <?php break;?>
		<?php } ?>
        <p class="detail"></p>
        <p class="jump">
            <span class="red">请您注意：</span>页面将在 <b id="wait"><?php echo($wait);?></b>秒内自动跳转<br>
            <a id="href" href="<?php
				if($code==1){
					if($url==2){
						echo('javascript:parent.location.reload()');
					}else{
						$url=$url ?? $_SERVER["HTTP_REFERER"];
						echo($url);
					}
				}else if($code==0){
					if($url==2){
						echo('javascript:parent.location.reload()');
					}else{
						$url=$url ?? 'javascript:history.back(-1);';
						echo($url);
					}
				}
			?>">直接跳转</a>
        </p>
    </div>
    <script type="text/javascript">
        (function(){
            var wait = document.getElementById('wait'),
                href = document.getElementById('href').href;
            
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
		            location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        })();
    </script>
</body>
</html>
<?php exit(); ?>