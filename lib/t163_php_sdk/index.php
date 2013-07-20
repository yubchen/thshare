<?php
session_start();
include_once('config.php');
include_once('api/tblog.class.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>登录网易微博授权页面</title>
</head>
<body>

<?php
$oauth = new OAuth(CONSUMER_KEY, CONSUMER_SECRET);
$request_token = $oauth->getRequestToken();

echo "Request Token: ".$request_token['oauth_token'];
echo "<br>";
echo "Request Token Secret: ".$request_token['oauth_token_secret'];

$aurl = $oauth->getAuthorizeURL( $request_token['oauth_token'], "http://".$_SERVER['HTTP_HOST'].'/callback.php');
$_SESSION['request_token'] = $request_token;
?>

<br/>
<br/>
<a href="<?php echo $aurl ?>">点此登录网易微博授权页面</a>
</body>
</html>