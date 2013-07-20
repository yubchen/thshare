<?php
session_start();
include_once('config.php');
include_once('api/tblog.class.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>授权完成</title>
</head>
<body>
<h1>授权完成</h1>
<?php
$oauth = new OAuth( CONSUMER_KEY, CONSUMER_SECRET , $_SESSION['request_token']['oauth_token'] , $_SESSION['request_token']['oauth_token_secret']  );

if ($access_token = $oauth->getAccessToken(  $_REQUEST['oauth_token'] ) )
{
	echo "access_token: ".$access_token['oauth_token'];
	echo "<br/>";
	echo "access_token_secret: ".$access_token['oauth_token_secret'];
?>
	<p>请将access token和secret更新到config.php</p>
    <br/>
    <div><a href="home_timeline.php">微博列表</a></div>
    <br/>
    <div><a href="update.php">发送微博</a></div>
<?php
}
else
{
    echo "授权失败";
}
?>
</body>
</html>