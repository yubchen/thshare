<?php
header('Content-Type: text/html; charset=UTF-8');

define( "WB_AKEY" , sfConfig::get('app_sina_app_key') );
define( "WB_SKEY" , sfConfig::get('app_sina_app_secret') );
define( "WB_CALLBACK_URL" , 'http://www.d4cheng.com/callback.php' );
