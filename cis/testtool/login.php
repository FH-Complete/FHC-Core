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
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/pruefling.class.php');
require_once('../../include/studiengang.class.php');

session_start();
$reload=false;
$reload_parent=false;

if (isset($_GET['logout']))
{
	if(isset($_SESSION['prestudent_id']))
	{
		$reload = true;
		session_destroy();
	}
}

//Connection Herstellen
if(!$db_conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Oeffnen der Datenbankverbindung');

if(isset($_POST['tag']) && isset($_POST['monat']) && isset($_POST['jahr']))
{
	if($_POST['tag']!='' && $_POST['monat']!='' && $_POST['jahr']!='')
		$gebdatum = $_POST['jahr'].'-'.$_POST['monat'].'-'.$_POST['tag'];
	else
		$gebdatum='';
}

if (isset($_POST['prestudent']) && isset($gebdatum))
{
	$ps=new prestudent($db_conn,$_POST['prestudent']);
	if ($gebdatum==$ps->gebdatum)
	{
		$pruefling = new pruefling($db_conn);
		if($pruefling->getPruefling($ps->prestudent_id))
		{
			$studiengang = $pruefling->studiengang_kz;
			$semester = $pruefling->semester;
		}
		else 
		{
			$studiengang = $ps->studiengang_kz;
			$ps->getLastStatus($ps->prestudent_id);
			$semester = $ps->ausbildungssemester;
		}
		if($semester=='')
			$semester=1;
		
		$_SESSION['prestudent_id']=$_POST['prestudent'];
		$_SESSION['studiengang_kz']=$studiengang;
		$_SESSION['nachname']=$ps->nachname;
		$_SESSION['vorname']=$ps->vorname;
		$_SESSION['gebdatum']=$ps->gebdatum;
		$stg_obj = new studiengang($db_conn, $studiengang);
		$_SESSION['sprache']=$stg_obj->sprache;
				
		$_SESSION['semester']=$semester;
	}
	else 
	{
		echo '<span class="error">Ihr Geburtsdatum stimmt nicht mit unseren Daten überein</span>';
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

if(isset($_GET['type']) && $_GET['type']=='sprachechange' && isset($_GET['sprache']))
{
	$_SESSION['sprache']=$_GET['sprache'];
}

if(isset($_SESSION['prestudent_id']) && !isset($_SESSION['pruefling_id']))
{
	$pruefling = new pruefling($db_conn);
	
	if(!$pruefling->getPruefling($_SESSION['prestudent_id']))
	{
		$pruefling->new = true;
					
		$pruefling->studiengang_kz = $_SESSION['studiengang_kz'];
		$pruefling->semester = $_SESSION['semester'];
		
		$pruefling->idnachweis = '';
		$pruefling->registriert = date('Y-m-d H:i:s');
		$pruefling->prestudent_id = $_SESSION['prestudent_id'];
		if($pruefling->save())
		{
			$_SESSION['pruefling_id']=$pruefling->pruefling_id;
			$reload_parent=true;
		}
	}
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
	$pruefling->semester = $_POST['semester'];
	if($pruefling->save())
	{
		$_SESSION['pruefling_id']=$pruefling->pruefling_id;
		$_SESSION['semester']=$pruefling->semester;
		$reload_parent=true;
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
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
		echo '<form method="GET">';	
		echo '<br>Sie sind angemeldet als '.$_SESSION['vorname'].' '.$_SESSION['nachname'];
		echo ' ('.$_SESSION['gebdatum'].') ID: '.$_SESSION['prestudent_id'];		
		echo '&nbsp; <INPUT type="submit" value="Logout" name="logout" />';
		echo '</form>';
		echo '<br><br>';
				
		$prestudent = new prestudent($db_conn, $prestudent_id);
		$stg_obj = new studiengang($db_conn, $prestudent->studiengang_kz);
		
		$pruefling = new pruefling($db_conn);
		if($pruefling->getPruefling($prestudent_id))
		{
		
			echo '<FORM METHOD="POST">';
			echo '<input type="hidden" name="pruefling_id" value="'.$pruefling->pruefling_id.'">';
			echo '<table>';
			echo '<tr><td>Semester:</td><td><input type="text" name="semester" size="1" maxlength="1" value="'.htmlentities($pruefling->semester).'"></td></tr>';
			echo '<tr><td>ID Nachweis:</td><td><INPUT type="text" maxsize="50" name="idnachweis" value="'.htmlentities($pruefling->idnachweis).'"></td></tr>';
			echo '<tr><td></td><td><input type="submit" name="save" value="OK"></td>';
			echo '</table>';
			echo '</FORM><br><br>';
			
			//Wenn die Sprachwahl fuer diesen Studiengang aktiviert ist, dann die Sprachen anzeigen
			if($stg_obj->testtool_sprachwahl)
			{
				//Liste der Sprachen
				$qry = "SELECT distinct sprache 
						FROM 
							testtool.tbl_pruefling 
							JOIN testtool.tbl_ablauf USING(studiengang_kz)
							JOIN testtool.tbl_frage USING(gebiet_id)
							JOIN testtool.tbl_frage_sprache USING(frage_id)						
						WHERE
							tbl_pruefling.pruefling_id='".addslashes($pruefling->pruefling_id)."'
						ORDER BY sprache DESC";
				echo 'Sprache:';
				if($result = pg_query($db_conn, $qry))
				{
					while($row = pg_fetch_object($result))
					{
						if($_SESSION['sprache']==$row->sprache)
							$selected='style="border:1px solid black;"';
						else 
							$selected='';
						echo " <a href='".$_SERVER['PHP_SELF']."?type=sprachechange&sprache=$row->sprache' class='Item' $selected><img src='bild.php?src=flag&amp;sprache=$row->sprache' alt='$row->sprache' title='$row->sprache'/></a>";
					}
				}
			}
			
			if($pruefling->pruefling_id!='')
			{
				$_SESSION['pruefling_id']=$pruefling->pruefling_id;
				echo '<script language="Javascript">parent.menu.location.reload()</script>';
			}
		}
		else 
		{
			echo 'Kein Pueflingseintrag vorhanden';
		}
	}
	else
	{
		echo '<form method="post">
				<SELECT name="prestudent">';
		echo '<OPTION value="13478">Dummy Dieter</OPTION>\n';
		foreach($ps->result as $prestd)
			echo '<OPTION value="'.$prestd->prestudent_id.'">'.$prestd->nachname.' '.$prestd->vorname."</OPTION>\n";
		echo '</SELECT>';
		echo '&nbsp; Geburtsdatum: ';
		//<INPUT type="text" maxlength="10" size="10" name="gebdatum" />(YYYY-MM-TT)';
		echo ' <SELECT name="tag">';
		echo '<OPTION value="">Tag</OPTION>';
		for($i=1;$i<=31;$i++)
			echo '<OPTION value="'.($i<10?'0':'').$i.'">'.$i.'</OPTION>';
		echo '</SELECT>';
		echo ' <SELECT name="monat">';
		echo '<OPTION value="">Monat</OPTION>';
		for($i=1;$i<=12;$i++)
			echo '<OPTION value="'.($i<10?'0':'').$i.'">'.date('F',mktime(0,0,0,$i,1,date('Y'))).'</OPTION>';
		echo '</SELECT>';
		echo ' <SELECT name="jahr">';
		echo '<OPTION value="">Jahr</OPTION>';
		for($i=date('Y');$i>date('Y')-99;$i--)
			echo '<OPTION value="'.$i.'">'.$i.'</OPTION>';
		echo '</SELECT>';
		echo '&nbsp; <INPUT type="submit" value="Login" />';
		echo '</form>';
		
		echo '<br /><br /><br />
		<center>
		<span style="font-size: 1.2em; font-style: italic;">
			Herzlich Willkommen zum Reihungstest der Fachhochschule Technikum-Wien<br /><br />
			Bitte warten Sie mit dem Login auf die Anweisung der Aufsichtsperson<br /><br />
			Wir wünschen Ihnen einen erfolgreichen Start ins Studium
		</span>
		</center>';
	}
?>

</body>
</html>
