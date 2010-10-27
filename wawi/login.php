<?php
require_once(dirname(__FILE__).'/../config/wawi.config.inc.php');
require_once(dirname(__FILE__).'/../include/wawi_konto.class.php');
require_once(dirname(__FILE__).'/../include/functions.inc.php');

$errormsg='';

if (isset($_POST['username'])) 
{
	session_start();

	$username = $_POST['username'];
	$passwort = $_POST['passwort'];

	$hostname = $_SERVER['HTTP_HOST'];
	
	//Benutzername und Passwort werden überprüft
	if (checkldapuser($username,$passwort)) 
	{
		$_SESSION['user'] = $username;
		if(isset($_SESSION['request_uri']))
			$path = $_SESSION['request_uri'];
		else
			$path = dirname($_SERVER['PHP_SELF']).'/index.php';
		
		//echo 'REDIRECT TO '.SERVER_ROOT.$path;
		//echo "user: ".$_SESSION['user'];
		header('Location: '.SERVER_ROOT.$path);
		exit;
	}
	else
	{
		$errormsg .= 'Passwort oder Benutzername ung&uuml;ltig';
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
  <title>Login - Bestellwesen Technikum Wien V 2.0.0</title>
  <style type="text/css">
  <!--
    body { font-family: Arial, Helvetica, sans-serif }
    td   { font-family: Arial, Helvetica, sans-serif }
  -->
  </style>

</head>
<body  bgcolor="#f5f5f5">
	<form name="login" action="login.php" method=post>
	<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=4>
	<th colspan=2 bgcolor="#666666"><font color="white">Login</font></th>
	<tr valign=top align=left>
		<td>Username:</td>
		<td><input type="text" name="username" size=32 maxlength=32></td>
	</tr>
	<tr valign=top align=left>
		<td>Passwort:</td>

		<td><input type="password" name="passwort" size=32 maxlength=32></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align=right><input type="submit" name="submitbtn" value="  OK  "></td>
	</tr>
	</table>

</form>

</body>

</html>

