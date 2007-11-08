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
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/datum.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$user = get_uid();

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

$datum_obj = new datum();
   
echo '<html>
	<head>
		<title>Betriebsmittel Details</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	</head>
	<body class="Background_main">
	<h2>Details</h2>
	';


if(!$rechte->isBerechtigt('admin', 0, 'suid') && !$rechte->isBerechtigt('support', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$betriebsmittel_id = (isset($_GET['betriebsmittel_id'])?$_GET['betriebsmittel_id']:'');
$person_id = (isset($_REQUEST['person_id'])?$_REQUEST['person_id']:'');
$error = false;

//Speichern der Daten
if(isset($_POST['save']))
{
	$bm = new betriebsmittel($conn);
			
	//Nachschauen ob dieses Betriebsmittel schon existiert
	if($bm->getBetriebsmittel($_POST['betriebsmitteltyp'],$_POST['nummerold']))
	{
		if(count($bm->result)>0)
		{
			//Wenn ein Eintrag gefunden wurde, dann wird die Beschreibung aktualisiert
			if($bm->load($bm->result[0]->betriebsmittel_id))
			{
				$bm->beschreibung = $_POST['beschreibung'];
				$bm->nummer = $_POST['nummer'];
				$bm->updatevon = $user;
				$bm->updateamum = date('Y-m-d H:i:s');
				
				if(!$bm->save(false))
				{
					$return = false;
					$error = true;
					echo 'Fehler beim Speichern des Betriebsmittels';
				}
				else 
				{
					$betriebsmittel_id = $bm->betriebsmittel_id;
				}
			}
			else 
			{
				$return = false;
				$error = true;
				echo 'Gefundener Eintrag konnte nicht geladen werden!?!?';
			}
		}
		else
		{
			//Wenn kein Eintrag gefunden wurde, dann wird ein neuer Eintrag angelegt
			$bm->betriebsmitteltyp = $_POST['betriebsmitteltyp'];
			$bm->nummer = $_POST['nummer'];
			$bm->beschreibung = $_POST['beschreibung'];
			$bm->reservieren = false;
			$bm->ort_kurzbz = null;
			$bm->insertamum = date('Y-m-d H:i:s');
			$bm->insertvon = $user;
		
			if($bm->save(true))
			{
				$betriebsmittel_id = $bm->betriebsmittel_id;
			}
			else 
			{
				$error = true;
				$return = false;
				echo 'Fehler beim Anlegen des Betriebsmittels';
			}
		}
					
		//Zuordnung Betriebsmittel-Person anlegen
		$bmp = new betriebsmittelperson($conn);
		if($_POST['new']=='true')
		{
			if($bmp->load($betriebsmittel_id, $_POST['person_id']))
			{
				$bmp->updateamum = date('Y-m-d H:i:s');
				$bmp->updatevon = $user;
				$bmp->new = false;
			}
			else 
			{
				$error = true;
				$return = false;
				echo 'Fehler beim Laden der Betriebmittelperson Zuordnung';
			}
		}
		else 
		{
			$bmp->insertamum = date('Y-m-d H:i:s');
			$bmp->insertvon = $user;
			$bmp->new = true;
		}

		if(!$error)
		{
			$bmp->person_id = $_POST['person_id'];
			$bmp->betriebsmittel_id=$betriebsmittel_id;
			$bmp->anmerkung = $_POST['anmerkung'];
			$bmp->kaution = trim(str_replace(',','.',$_POST['kaution']));
			$bmp->ausgegebenam = $_POST['ausgegebenam'];
			$bmp->retouram = $_POST['retouram'];
			
			if($bmp->save())
			{
				echo 'Daten wurden erfolgreich gespeichert';
			}
			else 
			{
				echo $bmp->errormsg;
			}
		}
	}
	else 
	{
		echo 'Fehler:'.$bm->errormsg;
	}
}

$bm = new betriebsmittelperson($conn);

//Laden der Daten
$new = 'false';
if($betriebsmittel_id!='' && $person_id!='')
{
	if(!$bm->load($betriebsmittel_id, $person_id))
		die('betriebsmittel konnte nicht geladen werden');
	else 
		$new ='true';
}
else 
{
	$bm->kaution = '0.0';
	$bm->ausgegebenam = date('d.m.Y');
	$bm->betriebsmitteltyp = 'Zutrittskarte';
}

$nummer = $bm->nummer;
$beschreibung = $bm->beschreibung;
$betriebsmitteltyp =$bm->betriebsmitteltyp;
$kaution = $bm->kaution;
$anmerkung = $bm->anmerkung;
$ausgegebenam = ($bm->ausgegebenam!=''?date('d.m.Y', $datum_obj->mktime_fromdate($bm->ausgegebenam)):'');
$retouram = ($bm->retouram!=''?date('d.m.Y', $datum_obj->mktime_fromdate($bm->retouram)):'');


//Formular
echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?betriebsmittel_id='.$betriebsmittel_id.'&person_id='.$person_id.'">';
echo '<input type="hidden" name="new" value="'.$new.'">';
echo '<table><tr><td valign="top">';
echo '<table>';
//Person
echo '<tr><td>Person</td><td><SELECT name="person_id">';
$qry = "SELECT distinct tbl_person.* FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) ORDER BY nachname, vorname";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($person_id == $row->person_id)
			$selected = 'selected';
		else 
			$selected = '';
			
		echo '<option value="'.$row->person_id.'" '.$selected.'>'.$row->nachname.' '.$row->vorname.' '.$row->titelpre.' '.$row->titelpost.'</option>';
	}
}
echo '</SELECT></td></tr>';
//TYP
echo '<tr><td>Typ</td><td><SELECT name="betriebsmitteltyp">';
$qry = "SELECT * FROM public.tbl_betriebsmitteltyp ORDER BY betriebsmitteltyp";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($row->betriebsmitteltyp==$betriebsmitteltyp)
			$selected = 'selected';
		else 
			$selected = '';
		
		echo "<option value='$row->betriebsmitteltyp' $selected>$row->betriebsmitteltyp</option>";
	}
}
echo '</SELECT></td></tr></table></td><td valign="top"><table>';
//Nummer
echo '<tr><td>Nummer</td><td><input type="text" name="nummer" value="'.htmlentities($nummer).'"><input type="hidden" name="nummerold" value="'.htmlentities($nummer).'"></td></tr>';
//Beschreibung
echo '<tr><td>Beschreibung</td><td><textarea name="beschreibung">'.htmlentities($beschreibung).'</textarea></td></tr>';
echo '</table>';
echo '</td><td valign="top">';
//Kaution
echo '<table><tr><td>Kaution</td><td><input type="text" name="kaution" value="'.htmlentities($kaution).'"></td></tr>';
//Anmerkung
echo '<tr><td>Anmerkung</td><td><textarea name="anmerkung">'.htmlentities($anmerkung).'</textarea></td></tr>';
echo '</table></td><td valign="top"><table>';
//Ausgegeben am
echo '<tr><td>Ausgegeben am</td><td><input type="text" name="ausgegebenam" value="'.htmlentities($ausgegebenam).'"></td></tr>';
//Retour am
echo '<tr><td>Retour am</td><td><input type="text" name="retouram" value="'.htmlentities($retouram).'"></td></tr>';
echo '</table>';
echo '</td></tr>';
echo '<tr><td></td><td></td><td></td><td align="right"><input type="submit" name="save" value="Speichern"></td></tr>';
echo '</table>';
echo '</form>';
?>
</body>
</html>