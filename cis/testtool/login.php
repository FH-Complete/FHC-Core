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
require_once('../../include/pruefling.class.php');
//require_once('../../include/lehrveranstaltung.class.php');

session_start();
$reload=false;
$reload_parent=false;

if (isset($_POST['logout']))
{
	$reload = true;
	session_destroy();
}

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

if(isset($_POST['save']) && isset($_SESSION['prestudent_id']))
{
	$pruefling = new pruefling($db_conn);
	if($_POST['pruefling_id']!='')
		if(!$pruefling->load($_POST['pruefling_id']))
			die('Pruefling wurde nicht gefunden');
		else 
			$pruefling->new=false;
	else 
		$pruefling->new=true;
			
	$pruefling->studiengang_kz = $_SESSION['studiengang_kz'];
	$pruefling->idnachweis = $_POST['idnachweis'];
	$pruefling->registriert = date('Y-m-d H:i:s');
	$pruefling->prestudent_id = $_SESSION['prestudent_id'];
	$pruefling->gruppe_kurzbz = $_POST['gruppe'];
	if($pruefling->save())
	{
		$_SESSION['pruefling_id']=$pruefling->pruefling_id;
		$reload_parent=true;
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../../skin/cis.css" rel="stylesheet" type="text/css">
<?php 
	if($reload_parent)
		echo '<script language="Javascript">parent.menu.location.reload()</script>';
	if($reload)
		echo "<script language=\"Javascript\">parent.location.reload();</script>";
?>		
</head>

<body>
<h1>Login</h1>
<?php
	if (isset($prestudent_id))
	{	
		echo '<form method="post">';	
		echo '<br>Sie sind eingelogt als '.$_SESSION['vorname'].' '.$_SESSION['nachname'];
		echo ' ('.$_SESSION['gebdatum'].') ID: '.$_SESSION['prestudent_id'];		
		echo '&nbsp; <INPUT type="submit" value="Logout" name="logout" />';
		echo '</form>';
		echo '<br><br>';
		
		$pruefling = new pruefling($db_conn);
		$pruefling->getPruefling($prestudent_id);
		if($pruefling->pruefling_id!='')
		{
			$_SESSION['pruefling_id']=$pruefling->pruefling_id;
			echo '<script language="Javascript">parent.menu.location.reload()</script>';
		}
		echo '<FORM METHOD="POST">';
		echo '<input type="hidden" name="pruefling_id" value="'.$pruefling->pruefling_id.'">';
		echo '<table>';
		echo '<tr><td>Gruppe:</td><td><SELECT name="gruppe">';
		$qry = "SELECT * FROM testtool.tbl_gruppe ORDER BY gruppe_kurzbz";
		if($result = pg_query($db_conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				if($row->gruppe_kurzbz==$pruefling->gruppe_kurzbz)
					echo '<OPTION value="'.$row->gruppe_kurzbz.'" selected>'.$row->gruppe_kurzbz.'</OPTION>';
				else 
					echo '<OPTION value="'.$row->gruppe_kurzbz.'" >'.$row->gruppe_kurzbz.'</OPTION>';
			}
		}
		echo '</SELECT></td></tr>';
		echo '<tr><td>ID Nachweis:</td><td><INPUT type="text" maxsize="50" name="idnachweis" value="'.htmlentities($pruefling->idnachweis).'"></td></tr>';
		echo '<tr><td></td><td><input type="submit" name="save" value="OK"></td>';
		echo '</table>';
		echo '</FORM>';
		
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
