<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../config.inc.php');
//require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
//require_once('../../include/studiengang.class.php');
//require_once('../../include/lehrveranstaltung.class.php');

session_start();
if (isset($_POST['logout']))
	session_destroy();
	
//Connection Herstellen
if(!$db_conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

if (isset($_POST['prestudent']) && isset($_POST['gebdatum']))
{
	$ps=new prestudent($db_conn,$_POST['prestudent']);
	if ($_POST['gebdatum']==$ps->gebdatum)
	{
		$_SESSION['prestudent_id']=$_POST['prestudent'];
		$_SESSION['studiengang_kz']=$ps->studiengang_kz;
		$_SESSION['nachname']=$ps->nachname;
		$_SESSION['vorname']=$ps->vorname;
		$_SESSION['gebdatum']=$ps->gebdatum;
	}
}
	
if (isset($_SESSION['prestudent_id']))
	$prestudent_id=$_SESSION['prestudent_id'];
else
{
	//$prestudent_id=null;
	$ps=new prestudent($db_conn);
	$datum=date('Y-m-d');
	$ps->getPrestudentRT($datum,true);
	if ($ps->num_rows==0)
		$ps->getPrestudentRT($datum);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../../skin/cis.css" rel="stylesheet" type="text/css">
</head>

<body>
<h1>Login</h1>
<?php
	if (isset($prestudent_id))
	{
		echo '<BR>Sie sind eingelogt als '.$_SESSION['vorname'].' '.$_SESSION['nachname'];
		echo ' ('.$_SESSION['gebdatum'].') ID: '.$_SESSION['prestudent_id'];
		echo '<form method="post">';
		echo '&nbsp; <INPUT type="submit" value="Logout" name="logout" />';
		echo '</form>';
	}
	else
	{
		echo '<form method="post">
				<SELECT name="prestudent">';
		foreach($ps->result as $prestd)
			echo '<OPTION value="'.$prestd->prestudent_id.'">'.$prestd->nachname.' '.$prestd->vorname."</OPTION>\n";
		echo '</SELECT>';
		echo '&nbsp; Geburtsdatum: <INPUT type="text" maxlength="10" size="10" name="gebdatum" />(YYYY-MM-TT)';
		echo '&nbsp; <INPUT type="submit" value="Login" />';
		echo '</form>';
	}
?>

</body>
</html>
