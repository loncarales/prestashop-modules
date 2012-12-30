<?php
require(dirname(dirname(dirname(__FILE__))).'/config/config.inc.php');

global $cookie;

// Init Cookie
$cookie = new Cookie('ps');

if (isset($cookie->fb_user_id))
	unset($cookie->fb_user_id);

/* Server Params */
$server_host_ssl = Tools::getHttpHost(false, true);
$server_host     = str_replace(':'._PS_SSL_PORT_, '',$server_host_ssl);
$protocol = 'http://';
 
$url = $protocol.$server_host . '/index.php?mylogout';
header("Location: $url");
exit;