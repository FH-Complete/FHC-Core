<?php
	session_start();

	$path = dirname(__FILE__);
	$path = mb_substr($path, mb_strlen($_SERVER['DOCUMENT_ROOT'])+1);
	
	if (!isset($_SESSION['user']) || $_SESSION['user']=='') 
	{
		$_SESSION['request_uri']=$_SERVER['REQUEST_URI'];
		
		header('Location: '.SERVER_ROOT.($path == '/' ? '' : $path).'/login.php');
		exit;
    }
?>