<?php
session_start();
include_once('config.php');
include_once('api/tblog.class.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>home_timeline</title>
</head>
<body>

<?php

$tblog = new TBlog(CONSUMER_KEY, CONSUMER_SECRET,ACCESS_TOKEN,ACCESS_TOKEN_SECRET);
$me = $tblog->verify_credentials();
$ms = $tblog->home_timeline();

?>

<h2><?php echo $me['name'];?> <img src="<?php echo $me['profile_image_url'];?>" width="50" height="50" border="0" alt=""></h2>

<?php

foreach($ms as $item)
{
    echo '<div style="padding:10px;margin:5px;border:1px solid #ccc">';
    echo $item['text'];
    echo '</div>';
}

?>

</body>
</html>