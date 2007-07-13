<?php
	header("Cache-Control: no-cache");
	header("Cache-Control: post-check=0, pre-check=0",false);
	header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
	if (isset($_GET['path']))
		$path=$_GET['path'];
	else
		$path='../';
	require ($path.'cis/config.inc.php');
	//setcookie('stylesheet', DEFAULT_STYLE);
	if (isset($_COOKIE['stylesheet']))
		$stylesheet=$_COOKIE['stylesheet'];
	else
		$stylesheet=DEFAULT_STYLE;
	//setcookie('stylesheet', DEFAULT_STYLE);
	header("Content-Type: text/css");
	//echo $_COOKIE['stylesheet'];
	readfile ($path.'skin/styles/'.$stylesheet.'.css');
?>

