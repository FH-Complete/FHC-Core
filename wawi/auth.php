<?php
	session_start();

	$path = dirname($_SERVER['PHP_SELF']);
	
	if (!isset($_SESSION['user']) || $_SESSION['user']=='') 
	{
		$_SESSION['request_uri']=$_SERVER['REQUEST_URI'];
		
		header('Location: '.SERVER_ROOT.($path == '/' ? '' : $path).'/login.php');
		exit;
    }
?>