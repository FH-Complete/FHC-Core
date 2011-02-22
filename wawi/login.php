<?php
/* Copyright (C) 2010 FH Technikum Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/../config/wawi.config.inc.php');
require_once(dirname(__FILE__).'/../include/wawi_konto.class.php');
require_once(dirname(__FILE__).'/../include/functions.inc.php');

$errormsg='';

if (isset($_POST['username'])) 
{
	session_start();

	$username = trim($_POST['username']);
	$passwort = $_POST['passwort'];

	$hostname = $_SERVER['HTTP_HOST'];
	
	//Benutzername und Passwort werden überprüft
	if (checkldapuser($username,$passwort)) 
	{
		$_SESSION['user'] = $username;
		$_SESSION['user_original'] = $username;
		if(isset($_SESSION['request_uri']))
			$path = $_SESSION['request_uri'];
		else
			$path = dirname($_SERVER['PHP_SELF']).'/index.php';
		
		if(substr($path,0,1)=='/')
			$path=substr($path,1);
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
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Login - Bestellwesen Technikum Wien V 2.0.0</title>
	<link rel="stylesheet" href="../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css">
</head>
<body onload="document.getElementById('username').focus();">
	<br />
	<br />
	<?php
		if($errormsg!='')
			echo '<center><span class="error">'.$errormsg.'</span></center><br /><br />';
	?>
	<form name="login" action="login.php" method=post>
	<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=4>
	<th colspan=2 bgcolor="#666666"><font color="white">Login</font></th>
	<tr valign=top align=left>
		<td>Username:</td>
		<td><input type="text" name="username" id="username" size=32 maxlength=32></td>
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
<br />
<br />
<br />
<br />
<br />
<p style="font-size:small; text-align: center;">Auf diesem Server befinden sich Daten, die nicht für Aussenstehende bestimmt sind. <br>Es werden hier keine Zeugnisnoten oder dergleichen aufbewahrt, sondern lediglich Daten &uuml;ber die interne Struktur und Verwaltung der FH Technikum-Wien, die für Aussenstehende nicht von Interesse sind.<br>
Jede Hack-Attacke oder sonstige mutwillige Versuche, die eine Manipulation oder ein Löschen dieser Daten zur Folge haben, werden strafrechtlich (nach §§126a, 126b, 126c StGB) verfolgt.</p>
</body>

</html>

