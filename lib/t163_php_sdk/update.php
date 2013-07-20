<?php
include_once('config.php');
include_once('api/tblog.class.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>发送微博</title>
</head>
<body>

<h2>发送微博</h2>
<form action="update.php" method="post"  enctype="multipart/form-data">
文字：<input type="text" name="text" style="width:300px" />
<br/>
<br/>
<br/>
图片：<input type="file" name="pic" style="width:300px" />
<br/>
<br/>
<br/>
<input type="submit" value="发送微博"/>
</form>

<?php

$tblog = new TBlog(CONSUMER_KEY, CONSUMER_SECRET,ACCESS_TOKEN,ACCESS_TOKEN_SECRET);

if(isset($_FILES['pic']))
{
	$target_path = 'images/temp_'.$_FILES['pic']['name'];  
	move_uploaded_file($_FILES['pic']['tmp_name'], $target_path);  
	echo "Upload result:";  
	if(file_exists($target_path)) {  
		if($_SERVER["OS"]!="Windows_NT"){ 
			@chmod($target_path,0604); 
		} 
	echo '<font color="green">Succeed!</font><br /><a href="http://' .$_SERVER["SERVER_NAME"] . "/" .$target_path .'"><img src=' .$target_path .' border="0">';  
}	else {  
		echo '<font color="red">Failed!</font>';  
	}  
	
    $url = $tblog ->upload($_REQUEST['text'],$target_path);    // 发送带图片微博
	echo "<br>";
    echo '<h3>发送成功！</h3>';
}
elseif(isset($_REQUEST['text']))
{
    $tblog->update($_REQUEST['text']);    // 发送纯文本微博
    echo '<h3>发送成功！</h3>';
}

?>

</body>
</html>