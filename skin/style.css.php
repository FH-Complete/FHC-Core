<?php
	setcookie('stylesheet', 'tw');
	if (isset($_COOKIE['stylesheet']))
		$stylesheet=$_COOKIE['stylesheet'];
	else
		$stylesheet='tw';
	header("Content-Type: text/css");
	readfile ('styles/'.$stylesheet.'.css');
?>

