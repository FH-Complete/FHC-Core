<?php
	session_start();

	if (!isset($_SESSION['incoming/user']) || $_SESSION['incoming/user']=='') 
	{
		$_SESSION['request_uri']=$_SERVER['REQUEST_URI'];
		
		header('Location: index.php');
		exit;
    }
?>