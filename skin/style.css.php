<?php
	if (isset($_GET['path']))
		$path=$_GET['path'];
	else
		$path='../';
	require ($path.'cis/config.inc.php');
	setcookie('stylesheet', DEFAULT_STYLE);
	if (isset($_COOKIE['stylesheet']))
		$stylesheet=$_COOKIE['stylesheet'];
	else
		$stylesheet=DEFAULT_STYLE;
	header("Content-Type: text/css");
	readfile ($path.'skin/styles/'.$stylesheet.'.css');
?>

