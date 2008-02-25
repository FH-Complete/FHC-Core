<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Raab <gerald.raab@technikum-wien.at>.
 */
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/lehrverband.class.php');
	require_once('../../include/gruppe.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
?>
<html>
<head>
<title>Lehrverbandsgruppen Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script language="JavaScript">
function replicateKurzbz()
{
	var kurzbz = document.copygroup.gruppe_kurzbz_copy.value;
	document.copygroup.bezeichnung_copy.value = kurzbz;
	document.copygroup.beschreibung_copy.value = kurzbz;
}
</script>

</head>

<body class="background_main">
<h2>Lehrverbandsgruppen - Verwaltung</h2>

<?php

if(isset($_GET['studiengang_kz']) && is_numeric($_GET['studiengang_kz']))
	$studiengang_kz=$_GET['studiengang_kz'];
else 
	$studiengang_kz='';

$user = get_uid();
$aktiv = (isset($_GET['aktiv'])?$_GET['aktiv']:'');
$type = (isset($_GET['type'])?$_GET['type']:'');
$semester = (isset($_GET['semester'])?$_GET['semester']:'');
$verband = (isset($_GET['verband'])?$_GET['verband']:'');
$gruppe = (isset($_GET['gruppe'])?$_GET['gruppe']:'');
$gruppe_kurzbz = (isset($_GET['gruppe_kurzbz'])?$_GET['gruppe_kurzbz']:'');

//Connection zur Datenbank herstellen
if(!$conn = pg_pconnect(CONN_STRING))
	die('Es konnte keine Verbindung zur Datenbank hergestellt werden');

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

//Studiengang Drop Down anzeigen
$stud = new studiengang($conn);
if(!$stud->getAll('typ, kurzbzlang'))
	echo 'Fehler beim Laden der Studiengaenge:'.$stud->errormsg;

echo '<form name="frm_studiengang" action="'.$_SERVER['PHP_SELF'].'" method="GET">';
echo 'Studiengang: <SELECT name="studiengang_kz"  onchange="document.frm_studiengang.submit()">';

foreach($stud->result as $row)
{
	if($rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid') || 
	   $rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid'))
	{
		if($studiengang_kz=='')
			$studiengang_kz=$row->studiengang_kz;
		
		echo '<OPTION value="'.$row->studiengang_kz.'"'.($studiengang_kz==$row->studiengang_kz?'selected':'').'>'.$row->kuerzel.'</OPTION>';
	}
}
echo '</SELECT>';
echo '</form>';

if($rechte->isBerechtigt('admin', $studiengang_kz, 'suid'))
	$admin=true;
else 
	$admin=false;

if($rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid'))
	$assistenz=true;
else 
	$assistenz=false;

if(!$admin && !$assistenz)
	die('Sie haben keine Berechtigung für diesen Studiengang');

$studiengang = new studiengang($conn);
$studiengang->load($studiengang_kz);

//Anlegen einer neuen Gruppe
if($type=='neu')
{
	if(!$admin)
		die('Sie haben keine Berechtigung zum Speichern');
	
	if(isset($_POST['spzgruppe_neu']))
	{
		//neue Spezialgruppe anlegen
		$gruppe_kurzbz=$studiengang->kuerzel.'-'.$semester.strtoupper($_POST['spzgruppe_neu']);
		
		$gruppe = new gruppe($conn);
		
		if(!$gruppe->exists($gruppe_kurzbz))
		{
			$gruppe->gruppe_kurzbz = $gruppe_kurzbz;
			$gruppe->studiengang_kz = $studiengang_kz;
			$gruppe->semester = $semester;
			$gruppe->bezeichnung = $gruppe_kurzbz;
			$gruppe->beschreibung = $gruppe_kurzbz;
			$gruppe->aktiv = true;
			$gruppe->sichtbar = true;
			$gruppe->lehre = true;
			$gruppe->sort = '';
			$gruppe->mailgrp = true;
			$gruppe->generiert = false;
			$gruppe->insertamum = date('Y-m-d H:i:s');
			$gruppe->insertvon = $user;
			
			if($gruppe->save(true))
			{
				echo "Gruppe wurde angelegt";
			}
			else 
			{
				echo "<span class='error'>Fehler beim anlegen der Gruppe:$gruppe->errormsg</span>";
			}
		}
		else 
		{
			echo "<span class='error'>Diese Gruppe Existiert bereits: $gruppe_kurzbz</span>";
		}			
	}
	else 
	{
		$lvb = new lehrverband($conn);
		
		if(isset($_POST['semester_neu']))
		{
			//Neues Semester anlegen
			$semester = $_POST['semester_neu'];
			$verband = ' ';
			$gruppe = ' ';
		}
		elseif(isset($_POST['verband_neu']))
		{
			//neuen Verband anlegen
			$verband = $_POST['verband_neu'];
			$gruppe = ' ';
		}
		elseif(isset($_POST['gruppe_neu']))
		{
			//neue Gruppe anlegen
			$gruppe = $_POST['gruppe_neu'];
		}
				
		if(!$lvb->exists($studiengang_kz, $semester, $verband, $gruppe))
		{
			$lvb->studiengang_kz = $studiengang_kz;
			$lvb->semester = $semester;
			$lvb->verband = $verband;
			$lvb->gruppe = $gruppe;
			$lvb->aktiv=true;
			$lvb->bezeichnung = '';
			
			if($lvb->save(true))
			{
				echo 'Gruppe wurde erfolgreich angelegt';
			}
			else 
			{
				echo "<span class='error'>Fehler beim Anlegen der Gruppe: $lvb->errormsg</span>";
			}
		}
		else 
		{
			echo "<span class='error'>Diese Gruppe Existiert bereits</span>";
		}
	}
}

// kopieren einer Spez-Gruppe
if($type=='copy')
{
	if(!$admin)
		die('Sie haben keine Berechtigung zum Speichern');
	
	if(isset($_POST['copynew']))
	{
		//neue Spezialgruppe anlegen

		$bezeichnung = $_POST['bezeichnung_copy'];

		$gruppe_kurzbz = $_POST["gruppe_kurzbz_copy"];		
		$beschreibung = $_POST['beschreibung_copy'];
		$sichtbar = isset($_POST['sichtbar_copy']);
		$lehre = isset($_POST['sichtbar_copy']);
		$aktiv = isset($_POST['aktiv_copy']);
		$sort = $_POST['sort_copy'];
		$mailgrp = isset($_POST['mailgrp_copy']);
		$generiert = isset($_POST['generiert_copy']);
		
		$gruppe = new gruppe($conn);
		
		if(!$gruppe->exists($gruppe_kurzbz))
		{
			$gruppe->gruppe_kurzbz = $gruppe_kurzbz;
			$gruppe->studiengang_kz = $studiengang_kz;
			$gruppe->semester = $semester;
			$gruppe->bezeichnung = $gruppe_kurzbz;
			$gruppe->beschreibung = $gruppe_kurzbz;
			$gruppe->aktiv = true;
			$gruppe->sichtbar = true;
			$gruppe->lehre = true;
			$gruppe->sort = '';
			$gruppe->mailgrp = true;
			$gruppe->generiert = false;
			$gruppe->insertamum = date('Y-m-d H:i:s');
			$gruppe->insertvon = $user;
			
			if($gruppe->save(true,false))
			{
				echo "Gruppe wurde angelegt";
			}
			else 
			{
				echo "<span class='error'>Fehler beim anlegen der Gruppe:$gruppe->errormsg</span>";
			}
		}
		else 
		{
			echo "<span class='error'>Diese Gruppe Existiert bereits: $gruppe_kurzbz</span>";
		}			
	}
}




//Aenderung des Aktiv Status
if($aktiv!='')
{
	if(!$admin)
		die('Sie haben keine Berechtigung zum Speichern');
	
	if($gruppe_kurzbz!='')
	{
		$gruppe = new gruppe($conn);
		if($gruppe->load($gruppe_kurzbz))
		{
			$gruppe->aktiv=!$gruppe->aktiv;
			if($gruppe->save(false))
			{
				echo "Aktiv Status wurde erfolgreich geaendert";
			}
			else 
			{
				echo "Fehler beim Aendern des Aktiv-Feldes: $gruppe->errormsg";
			}
		}
		else 
		{
			echo "Spezialgruppe wurde nicht gefunden";
		}
	}
	else 
	{
		$lvb = new lehrverband($conn);
		
		if($lvb->load($studiengang_kz, $semester, $verband, $gruppe))
		{
			$lvb->aktiv = !$lvb->aktiv;
			if($lvb->save(false))
			{
				echo "Aktiv Status wurde erfolgreich geaendert";
			}
			else 
			{
				echo "Fehler beim Aendern des Aktiv-Feldes: $lvb->errormsg";
			}
		}
		else 
		{
			echo "<span class='error'>Lehrverband wurde nicht gefunden</span>";
		}
	}
}

//Speichern der geaenderten Gruppendaten
if($type=='save')
{
	//Spezialgruppe speichern
	if($gruppe_kurzbz!='')
	{
		$gruppe = new gruppe($conn);
		if($gruppe->load($gruppe_kurzbz))
		{
			$gruppe->bezeichnung = $_POST['bezeichnung'];
			if($admin)
			{
				$gruppe->beschreibung = $_POST['beschreibung'];
				$gruppe->sichtbar = isset($_POST['sichtbar']);
				$gruppe->lehre = isset($_POST['sichtbar']);
				$gruppe->aktiv = isset($_POST['aktiv']);
				$gruppe->sort = $_POST['sort'];
				$gruppe->mailgrp = isset($_POST['mailgrp']);
				$gruppe->generiert = isset($_POST['generiert']);
			}
			$gruppe->updateamum = date('Y-m-d H:i:s');
			$gruppe->updatevon = $user;
			
			if($gruppe->save(false))
			{
				echo 'Daten wurden erfolgreich geaendert';
			}
			else 
			{
				echo "Fehler beim Speichern der Daten: $gruppe->errormsg";
			}
			$semester = $gruppe->semester;
		}
		else 
			echo "Gruppe konnte nicht geladen werden";
	}
	else 
	{
		//Lehrverbandsgruppe speichern
		$lvb = new lehrverband($conn);
		if($lvb->load($studiengang_kz, $semester, $verband, $gruppe))
		{
			$lvb->bezeichnung = $_POST['bezeichnung'];
			if($admin)
				$lvb->aktiv = isset($_POST['aktiv']);
			
			if($lvb->save(false))
			{
				echo 'Daten wurden erfolgreich geaendert';
			}
			else 
			{
				echo "Fehler beim Speichern der Daten: $lvb->errormsg";
			}
		}
		else 
		{
			echo "Gruppe konnte nicht geladen werden";
		}
	}
}


//Tree der Gruppen
echo '<table style="font-size:large" width="100%"><tr><td>';
if(!$admin)
	$where = ' AND aktiv=true';
else 
	$where = '';

$qry = "SELECT * FROM public.tbl_lehrverband WHERE studiengang_kz='$studiengang_kz' $where ORDER BY studiengang_kz, semester, verband, gruppe";

if($result = pg_query($conn, $qry))
{
	$lastsemester='';
	$lastverband='';
	
	while($row = pg_fetch_object($result))
	{
		if(trim($row->verband)=='')
		{
			if($row->semester!=$lastsemester)
			{
				if($lastsemester!='')
				{
					if($lastverband!='')
					{
						if($admin)
						{
							//Formular zum Anlegen einer neuen Gruppe
							echo "<form action='".$_SERVER['PHP_SELF']."?type=neu&studiengang_kz=$row->studiengang_kz&semester=$lastsemester&verband=$lastverband' method='POST'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-<input type='text' name='gruppe_neu' size='1' maxlength='1' /> <input type='submit' value='Neu' /></form>";
						}
					}
					if($admin)
					{
						//Formular zum Anlegen eines neuen Verbandes
						echo "<form action='".$_SERVER['PHP_SELF']."?type=neu&studiengang_kz=$row->studiengang_kz&semester=$lastsemester' method='POST'>&nbsp;&nbsp;&nbsp;&nbsp;|-<input type='text' name='verband_neu' size='1' maxlength='1' /> <input type='submit' value='Neuer Verband' /></form>";
					}
					
					if(!$admin)
						$where=' AND aktiv AND lehre';
					else 
						$where='';
					//Spezialgruppen des vorherigen Semesters
					$qry_gruppe = "SELECT * FROM public.tbl_gruppe WHERE studiengang_kz='$studiengang_kz' AND semester='$lastsemester' $where ORDER BY sort, gruppe_kurzbz";
					if($result_gruppe = pg_query($conn, $qry_gruppe))
					{
						while($row_gruppe = pg_fetch_object($result_gruppe))
						{
							echo "&nbsp;&nbsp;&nbsp;&nbsp;|-";
							if($admin)
								echo " <a href='".$_SERVER['PHP_SELF']."?studiengang_kz=$row->studiengang_kz&gruppe_kurzbz=$row_gruppe->gruppe_kurzbz&aktiv=".($row_gruppe->aktiv=='t'?'false':'true')."' class='Item'><img src='../../skin/images/".($row_gruppe->aktiv=='t'?'true.gif':'false.gif')."'></a>";
							echo " <a href='".$_SERVER['PHP_SELF']."?studiengang_kz=$row->studiengang_kz&semester=$lastsemester&gruppe_kurzbz=$row_gruppe->gruppe_kurzbz&type=edit' class='Item'>$row_gruppe->gruppe_kurzbz ($row_gruppe->bezeichnung)</a><br>";
						}
					}

					if($admin)
					{
						//Formular zum Anlegen einer neuen Spezialgruppe
						echo "<form action='".$_SERVER['PHP_SELF']."?type=neu&studiengang_kz=$row->studiengang_kz&semester=$lastsemester' method='POST'>&nbsp;&nbsp;&nbsp;&nbsp;|- $studiengang->kuerzel-$lastsemester<input type='text' name='spzgruppe_neu' size='11' maxlength='11' /> <input type='submit' value='Neue SpzGrp' /></form>";
					}
	
				}
				$lastverband='';
				$lastsemester=$row->semester;
			}
			
			//Semester ausgeben
			echo '<a name="'.$row->semester.'" />';
			if($admin)
				echo "<a href='".$_SERVER['PHP_SELF']."?studiengang_kz=$row->studiengang_kz&semester=$row->semester&verband=$row->verband&gruppe=$row->gruppe&aktiv=".($row->aktiv=='t'?'false':'true')."' class='Item'><img src='../../skin/images/".($row->aktiv=='t'?'true.gif':'false.gif')."'></a>";
			echo "<b><a href='".$_SERVER['PHP_SELF']."?studiengang_kz=$row->studiengang_kz&semester=$row->semester&verband=$row->verband&gruppe=$row->gruppe&type=edit' class='Item'>$row->semester ($row->bezeichnung)</a></b>";
		}
		elseif(trim($row->gruppe)=='')
		{
			if($row->verband!=$lastverband)
			{
				if($lastverband!='')
				{
					if($admin)
					{
						//Formular zum Anlegen einer neuen Gruppe
						echo "<form action='".$_SERVER['PHP_SELF']."?type=neu&studiengang_kz=$row->studiengang_kz&semester=$row->semester&verband=$lastverband' method='POST'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-<input type='text' name='gruppe_neu' size='1' maxlength='1' /> <input type='submit' value='Neu' /></form>";
					}
				}	
				$lastverband=$row->verband;
			}
			//Verband
			echo "&nbsp;&nbsp;&nbsp;&nbsp;|- ";
			if($admin)
				echo "<a href='".$_SERVER['PHP_SELF']."?studiengang_kz=$row->studiengang_kz&semester=$row->semester&verband=$row->verband&gruppe=$row->gruppe&aktiv=".($row->aktiv=='t'?'false':'true')."' class='Item'><img src='../../skin/images/".($row->aktiv=='t'?'true.gif':'false.gif')."'></a>";
			echo "<b><a href='".$_SERVER['PHP_SELF']."?studiengang_kz=$row->studiengang_kz&semester=$row->semester&verband=$row->verband&gruppe=$row->gruppe&type=edit' class='Item'>$row->verband ($row->bezeichnung)</a></b> ";
		}
		else
		{
			//Gruppe
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|- ";
			if($admin)
				echo "<a href='".$_SERVER['PHP_SELF']."?studiengang_kz=$row->studiengang_kz&semester=$row->semester&verband=$row->verband&gruppe=$row->gruppe&aktiv=".($row->aktiv=='t'?'false':'true')."' class='Item'><img src='../../skin/images/".($row->aktiv=='t'?'true.gif':'false.gif')."'></a>";
			echo "<b><a href='".$_SERVER['PHP_SELF']."?studiengang_kz=$row->studiengang_kz&semester=$row->semester&verband=$row->verband&gruppe=$row->gruppe&type=edit' class='Item'>$row->gruppe ($row->bezeichnung)</a></b>";
		}
		
		
		echo "<br>";
	}
	if($lastsemester!='')
	{
		if($lastverband!='')
		{
			if($admin)
			{
				//Formular zum Anlegen einer neuen Gruppe
				echo "<form action='".$_SERVER['PHP_SELF']."?type=neu&studiengang_kz=$studiengang_kz&semester=$lastsemester&verband=$lastverband' method='POST'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-<input type='text' name='gruppe_neu' size='1' maxlength='1' /> <input type='submit' value='Neu' /></form>";
			}
		}
		if($admin)
		{
			//Formular zum Anlegen eines neuen Verbandes
			echo "<form action='".$_SERVER['PHP_SELF']."?type=neu&studiengang_kz=$studiengang_kz&semester=$lastsemester' method='POST'>&nbsp;&nbsp;&nbsp;&nbsp;|-<input type='text' name='verband_neu' size='1' maxlength='1' /> <input type='submit' value='Neuer Verband' /></form>";
		}
		
		if(!$admin)
			$where=' AND aktiv AND lehre';
		else 
			$where='';
		//Spezialgruppen des vorherigen Semesters
		$qry_gruppe = "SELECT * FROM public.tbl_gruppe WHERE studiengang_kz='$studiengang_kz' AND semester='$lastsemester' $where ORDER BY sort, gruppe_kurzbz";
		if($result_gruppe = pg_query($conn, $qry_gruppe))
		{
			while($row_gruppe = pg_fetch_object($result_gruppe))
			{
				echo "&nbsp;&nbsp;&nbsp;&nbsp;|- <a href='".$_SERVER['PHP_SELF']."?studiengang_kz=$studiengang_kz&gruppe_kurzbz=$row_gruppe->gruppe_kurzbz&aktiv=".($row_gruppe->aktiv=='t'?'false':'true')."' class='Item'><img src='../../skin/images/".($row_gruppe->aktiv=='t'?'true.gif':'false.gif')."'></a><b><a href='".$_SERVER['PHP_SELF']."?studiengang_kz=$row->studiengang_kz&semester=$lastsemester&gruppe_kurzbz=$row_gruppe->gruppe_kurzbz&type=edit' class='Item'>$row_gruppe->gruppe_kurzbz</a></b><br>";
			}
		}
		
		if($admin)
		{
			//Formular zum Anlegen einer neuen Spezialgruppe
			echo "<form action='".$_SERVER['PHP_SELF']."?type=neu&studiengang_kz=$studiengang_kz&semester=$lastsemester' method='POST'>&nbsp;&nbsp;&nbsp;&nbsp;|- $studiengang->kuerzel-$lastsemester<input type='text' name='spzgruppe_neu' size='11' maxlength='11' /> <input type='submit' value='Neue SpzGrp' /></form>";
			
			//Formular zum Anlegen eines neuen Semesters
			echo "<form action='".$_SERVER['PHP_SELF']."?type=neu&studiengang_kz=$studiengang_kz' method='POST'><input type='text' name='semester_neu' size='2' maxlength='2' /> <input type='submit' value='Neu' /></form>";
		}
	}
}

echo '</td><td valign="top" align="center">';
//Formular zum bearbeiten der Daten
if($type=='edit')
{
	if($gruppe_kurzbz!='')
	{
		$gruppe = new gruppe($conn);
		if($gruppe->load($gruppe_kurzbz))
		{
			echo '<div style="position:fixed;
 							  left:450px; top:50px;">';
			
			echo "Details von $gruppe_kurzbz<br><br>";
			echo "<form action='".$_SERVER['PHP_SELF']."?type=save&studiengang_kz=$studiengang_kz&gruppe_kurzbz=$gruppe_kurzbz' method='POST'>
				  <table>
				  	<tr>
				  		<td>Bezeichnung:</td>
				  		<td><input type='text' name='bezeichnung' size='30' maxlength='32' value='$gruppe->bezeichnung'/></td>
				  	</tr>";
			if($admin)
			{
			echo "
				  	<tr>
				  		<td>Beschreibung:</td>
				  		<td><input type='text' name='beschreibung' size='30' maxlength='128' value='$gruppe->beschreibung'/></td>
				  	</tr>
				  	<tr>
				  		<td>Sichtbar:</td>
				  		<td><input type='checkbox' name='sichtbar' ".($gruppe->sichtbar?'checked':'')." /></td>
				  	</tr>
				  	<tr>
				  		<td>Lehre:</td>
				  		<td><input type='checkbox' name='lehre' ".($gruppe->lehre?'checked':'')." /></td>
				  	</tr>
				  	<tr>
				  		<td>Aktiv:</td>
				  		<td><input type='checkbox' name='aktiv' ".($gruppe->aktiv?'checked':'')." /></td>
				  	</tr>				  	
				  	<tr>
				  		<td>Sort:</td>
				  		<td><input type='text' name='sort' size='2' maxlength='2' value='$gruppe->sort' /></td>
				  	</tr>
					<tr>
				  		<td>Mailgrp:</td>
				  		<td><input type='checkbox' name='mailgrp' ".($gruppe->mailgrp?'checked':'')." /></td>
				  	</tr>
				  	<tr>
				  		<td>Generiert:</td>
				  		<td><input type='checkbox' name='generiert' ".($gruppe->generiert?'checked':'')." /></td>
				  	</tr>";
			}
			echo "
				  	<tr>
				  		<td>&nbsp;</td>
				  		<td>&nbsp;</td>
				  	</tr>
				  	<tr>
				  		<td></td>
				  		<td><input type='submit' value='Speichern' /></td>
				  	</tr>
				  </table>			
				  </form>";
				  
			if($admin)
			{
			echo "Als neue Gruppe speichern<br><br>";
			echo "<form name='copygroup' action='".$_SERVER['PHP_SELF']."?type=copy&studiengang_kz=$studiengang_kz&semester=$semester' method='POST'>
				  <table>
				  <tr>
				  		<td>Kurzbz:</td>
				  		<td><input type='text' name='gruppe_kurzbz_copy' size='30' maxlength='128' value='$gruppe->gruppe_kurzbz' onkeyup='replicateKurzbz();'/></td>
				  	</tr>
				  	<tr>
				  		<td>Bezeichnung:</td>
				  		<td><input type='text' name='bezeichnung_copy' size='30' maxlength='32' value='$gruppe->bezeichnung'/></td>
				  	</tr>";

			echo "
				  	<tr>
				  		<td>Beschreibung:</td>
				  		<td><input type='text' name='beschreibung_copy' size='30' maxlength='128' value='$gruppe->beschreibung'/></td>
				  	</tr>
				  	<tr>
				  		<td>Sichtbar:</td>
				  		<td><input type='checkbox' name='sichtbar_copy' ".($gruppe->sichtbar?'checked':'')." /></td>
				  	</tr>
				  	<tr>
				  		<td>Lehre:</td>
				  		<td><input type='checkbox' name='lehre_copy' ".($gruppe->lehre?'checked':'')." /></td>
				  	</tr>
				  	<tr>
				  		<td>Aktiv:</td>
				  		<td><input type='checkbox' name='aktiv_copy' ".($gruppe->aktiv?'checked':'')." /></td>
				  	</tr>				  	
				  	<tr>
				  		<td>Sort:</td>
				  		<td><input type='text' name='sort_copy' size='2' maxlength='2' value='$gruppe->sort' /></td>
				  	</tr>
					<tr>
				  		<td>Mailgrp:</td>
				  		<td><input type='checkbox' name='mailgrp_copy' ".($gruppe->mailgrp?'checked':'')." /></td>
				  	</tr>
				  	<tr>
				  		<td>Generiert:</td>
				  		<td><input type='checkbox' name='generiert_copy' ".($gruppe->generiert?'checked':'')." /></td>
				  	</tr>";
			
			echo "
				  	<tr>
				  		<td>&nbsp;</td>
				  		<td>&nbsp;</td>
				  	</tr>
				  	<tr>
				  		<td></td>
				  		<td><input type='submit' name='copynew' value='Speichern' /></td>
				  	</tr>
				  </table>			
				  </form>";				  
			  
			}  
			
			echo '</div>';
		}
	}
	else 
	{
		$lvb = new lehrverband($conn);
		if($lvb->load($studiengang_kz, $semester, $verband, $gruppe))
		{
			echo '<div style="position:fixed;
 							  left:450px; top:50px;">';
			echo "Details von $studiengang->kuerzel - $semester$verband$gruppe<br><br>";
			echo "<form action='".$_SERVER['PHP_SELF']."?type=save&studiengang_kz=$studiengang_kz&semester=$semester&verband=$verband&gruppe=$gruppe' method='POST'>
				  <table>
				  	<tr>
				  		<td>Bezeichnung:</td>
				  		<td><input type='text' name='bezeichnung' size='16' maxlength='16' value='$lvb->bezeichnung'/></td>
				  	</tr>";
			if($admin)
			{
				echo "
				  	<tr>
				  		<td>Aktiv:</td>
				  		<td><input type='checkbox' name='aktiv' ".($lvb->aktiv?'checked':'')." /></td>
				  	</tr>";
			}
			echo "
				  	<tr>
				  		<td>&nbsp;</td>
				  		<td>&nbsp;</td>
				  	</tr>
				  	<tr>
				  		<td></td>
				  		<td><input type='submit' value='Speichern' /></td>
				  	</tr>
				  </table>			
				  </form>";
			echo '</div>';
		}
		else 
			echo "Gruppe wurde nicht gefunden";
	}
}
echo '</td></tr></table>';
if(isset($semester) || is_numeric($semester)) //Zum Anker hüpfen
{
	echo "<script language='JavaScript'>this.location.hash='#".$semester."'</script>";
}
?>

</body>
</html>
