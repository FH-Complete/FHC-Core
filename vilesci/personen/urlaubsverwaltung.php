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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

		require_once('../../config/vilesci.config.inc.php');

/**
 *
 * Seite zur Verwaltung der Urlaubs- und Zeitausgleichstage der Mitarbeiter
 */

require_once('../../include/functions.inc.php');
require_once('../../include/zeitsperre.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
$user = get_uid();
$datum = new datum();

$uid = (isset($_GET['uid'])?$_GET['uid']:'');
$zeitsperre_id=(isset($_REQUEST['zeitsperre_id'])?$_REQUEST['zeitsperre_id']:'');
$action = (isset($_GET['action'])?$_GET['action']:'');

$zeitsperretyp_kurzbz=(isset($_POST['zeitsperretyp_kurzbz'])?$_POST['zeitsperretyp_kurzbz']:'');
$bezeichnung=(isset($_POST['bezeichnung'])?$_POST['bezeichnung']:'');
$vondatum=(isset($_POST['von'])?$_POST['von']:'');
$vonstunde=(isset($_POST['vonstunde'])?$_POST['vonstunde']:'');
$bisdatum=(isset($_POST['bis'])?$_POST['bis']:'');
$bisstunde=(isset($_POST['bisstunde'])?$_POST['bisstunde']:'');
$vertretung_uid=(isset($_POST['vertretung_uid'])?$_POST['vertretung_uid']:'');
$erreichbarkeit_kurzbz=(isset($_POST['erreichbarkeit_kurzbz'])?$_POST['erreichbarkeit_kurzbz']:'');

$errormsg='';
$message='';
$error=false;

//Kopfzeile
echo '<html>
	<head>
		<title>Urlaubsverwaltung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script language="Javascript">
		function confdel(val)
		{
			return confirm("Wollen Sie diesen Eintrag wirklich loeschen: "+val);
		}
		</script>
	</head>
	<body class="Background_main">
	<h2>Urlaubsverwaltung</h2>
	';

//Rechte Pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin'))
	die('Sie haben keine Berechtigung für diese Seite');

//Formular zur Eingabe der UID
echo '<form  accept-charset="UTF-8" action="'.$_SERVER['PHP_SELF'].'" mehtod="GET">';
echo 'Zeitsperren des Mitarbeiters mit der UID <input type="text" name="uid" value="'.$uid.'">';
echo '<input type="submit" name="submit" value="Anzeigen">';
echo '</form>';

//Loeschen von Zeitsperren
if($action=='delete')
{
	if($zeitsperre_id!='' && is_numeric($zeitsperre_id))
	{
		$zeitsperre = new zeitsperre();
		if($zeitsperre->delete($zeitsperre_id))
			$message='Zeitsperre wurde geloescht';
		else 
			$errormsg='Fehler beim Loeschen der Zeitsperre';
	}
	else 
		$errormsg='Zeitsperre_id ist ungueltig';
}

if(isset($_POST['save']))
{
	//Speichern der Daten
	$zeitsperre = new zeitsperre();
	
	if($zeitsperre_id!='')
	{
		if(!$zeitsperre->load($zeitsperre_id))
		{
			$errormsg='Zeitsperre konnte nicht geladen werden';
			$error=true;
		}
		$zeitsperre->new = false;
	}
	else 
	{
		$zeitsperre->insertamum=date('Y-m-d H:i:s');
		$zeitsperre->insertvon = $user;
		$zeitsperre->new=true;
		$zeitsperre->mitarbeiter_uid=$uid;
	}
	
	if(!$error)
	{
		$zeitsperre->zeitsperretyp_kurzbz=$zeitsperretyp_kurzbz;
		$zeitsperre->bezeichnung = $bezeichnung;
		$zeitsperre->vondatum = $vondatum;
		$zeitsperre->vonstunde = $vonstunde;
		$zeitsperre->bisdatum = $bisdatum;
		$zeitsperre->bisstunde = $bisstunde;
		$zeitsperre->vertretung_uid = $vertretung_uid;
		$zeitsperre->erreichbarkeit_kurzbz = $erreichbarkeit_kurzbz;
		$zeitsperre->updateamum = date('Y-m-d H:i:s');
		$zeitsperre->updatevon = $user;
		
		if($zeitsperre->save())
		{
			$message = 'Daten wurden erfolgreich gespeichert';
		}
		else 
		{
			$errormsg = "Fehler beim Speichern der Daten: $zeitsperre->errormsg";
		}
	}
}
//Statusmeldungen ausgeben
if($errormsg!='')
	echo "<br><div class='inserterror'>$errormsg</div><br>";
if($message!='')
	echo "<br><div class='insertok'>$message</div><br>";

//Zeitsperren des Mitarbeiters anzeigen
if($uid!='')
{
	$mitarbeiter = new mitarbeiter();
	if(!$mitarbeiter->load($uid))
		die('Mitarbeiter wurde nicht gefunden');

	$zeitsperre = new zeitsperre();
	
	$zeitsperre->getzeitsperren($uid);
	echo '<h3>Zeitsperren von <b>'.$mitarbeiter->titelpre.' '.$mitarbeiter->vorname.' '.$mitarbeiter->nachname.' '.$mitarbeiter->titelpost.'</b></h3>';
	echo "<table class='liste table-autosort: table-stripeclass:alternate table-autostripe'>";
	echo '
	<thead>
		<tr class="liste">
			<th>ID</th>
			<th>Typ</th>
			<th>Bezeichnung</th>
			<th>Von</th>
			<th>Bis</th>
			<th>edit</th>
			<th>delete</th>
		</tr>
	</thead>
	<tbody>';
	foreach ($zeitsperre->result as $row) 
	{
		echo '<tr>';
		echo "<td>$row->zeitsperre_id</td>";
		echo "<td>$row->zeitsperretyp_kurzbz</td>";
		echo "<td>$row->bezeichnung</td>";
		echo "<td>".$datum->formatDatum($row->vondatum,'d.m.Y')." ".($row->vonstunde!=''?'(Stunde '.$row->vonstunde.')':'')."</td>";
		echo "<td>".$datum->formatDatum($row->bisdatum,'d.m.Y')." ".($row->bisstunde!=''?'(Stunde '.$row->bisstunde.')':'')."</td>";
		echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?action=edit&uid=$uid&zeitsperre_id=$row->zeitsperre_id'><img src='../../skin/images/application_form_edit.png' alt='bearbeiten' title='bearbeiten' /></a></td>";
		echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?action=delete&uid=$uid&zeitsperre_id=$row->zeitsperre_id' onclick='return confdel(\"$row->zeitsperretyp_kurzbz von ".$datum->formatDatum($row->vondatum,'d.m.Y')." bis ".$datum->formatDatum($row->bisdatum,'d.m.Y')."\")'><img src='../../skin/images/application_form_delete.png' alt='loeschen' title='loeschen'/></a></td>";
		echo '</tr>';
	}
	echo '</tbody></table>';


	//Editieren und Neu anlegen von Zeitsperren
	$zeitsperre = new zeitsperre();
	echo '<br><br>';
	if($action=='edit')
	{	
		if(!$zeitsperre->load($zeitsperre_id))
			die('Zeitsperre wurde nicht gefunden');
		if($zeitsperre->mitarbeiter_uid!=$uid)
			die('Zeitsperre und Mitarbeiter passen nicht zusammen');
		echo "<h3>Bearbeiten der Zeitsperre $zeitsperre->zeitsperre_id:</h3>";
	}
	else 
		echo "<h3>Neue Zeitsperre:</h3>";
	
	echo '<form accept-charset="UTF-8" action="'.$_SERVER['PHP_SELF'].'?uid='.$uid.'" method="POST">';
	echo '<input type="hidden" name="zeitsperre_id" value="'.$zeitsperre->zeitsperre_id.'">';
	echo '<table>';
	echo '<tr>';
	echo '<td>Typ</td><td><SELECT name="zeitsperretyp_kurzbz">';
	$qry = "SELECT * FROM campus.tbl_zeitsperretyp ORDER BY beschreibung";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			echo '<OPTION value="'.$row->zeitsperretyp_kurzbz.'" '.($row->zeitsperretyp_kurzbz==$zeitsperre->zeitsperretyp_kurzbz?'selected':'').'>'.$row->beschreibung.'</OPTION>';
		}
	}
	echo '</SELECT></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>Bezeichnung:</td><td><input type="text" name="bezeichnung" value="'.$zeitsperre->bezeichnung.'"></td>';
	
	echo '</tr><tr>';
	$qry = "SELECT * FROM lehre.tbl_stunde ORDER BY stunde";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$std_arr[$row->stunde]="$row->stunde (".date('H:i',strtotime($row->beginn)).' - '.date('H:i',strtotime($row->ende))." Uhr)";
		}
	}
	
	echo '<td>Von</td><td><input type="text" name="von" value="'.$datum->formatDatum($zeitsperre->vondatum,'d.m.Y').'">';
	echo 'Stunde (inklusive)';
	echo '<SELECT name="vonstunde">';
	if($zeitsperre->vonstunde=='')
		echo "<OPTION value='' selectd>*</OPTION>\n";
	else
		echo "<OPTION value=''>*</OPTION>\n";
		
	foreach ($std_arr as $std=>$val)
	{
		if($std==$zeitsperre->vonstunde)
			$selected='selected';
		else 
			$selected='';
		echo "<OPTION value='$std' $selected>$val</OPTION>";
	}
	echo '</SELECT></td>';
	echo '</tr><tr>';
	
	echo '<td>Bis</td><td><input type="text" name="bis" value="'.$datum->formatDatum($zeitsperre->bisdatum,'d.m.Y').'">';
	echo 'Stunde (inklusive)';
	echo '<SELECT name="bisstunde">';
	if($zeitsperre->bisstunde=='')
		echo "<OPTION value='' selectd>*</OPTION>\n";
	else
		echo "<OPTION value=''>*</OPTION>\n";
		
	foreach ($std_arr as $std=>$val)
	{
		if($std==$zeitsperre->bisstunde)
			$selected='selected';
		else 
			$selected='';
		echo "<OPTION value='$std' $selected>$val</OPTION>";
	}
	echo '</SELECT></td>';
	echo '</tr><tr>';
	
	echo '<td>Erreichbarkeit</td>';
	$qry = "SELECT * FROM campus.tbl_erreichbarkeit";
	$erreichbarkeit_arr=array();
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$erreichbarkeit_arr[$row->erreichbarkeit_kurzbz]=$row->beschreibung;
		}
	}
	echo "<td><SELECT name='erreichbarkeit'>";
	foreach ($erreichbarkeit_arr as $erreichbarkeit_key=>$erreichbarkeit_beschreibung)
	{
		if($zeitsperre->erreichbarkeit_kurzbz == $erreichbarkeit_key)+
			$selected='selected';
		else 
			$selected='';
		
		echo "<OPTION value='$erreichbarkeit_key' ".$selected.">$erreichbarkeit_beschreibung</OPTION>\n";
	}
	
	echo '</SELECT></td>';
	echo '</tr><tr>';
	
	echo '<td>Vertretung</td>';
	echo "<td><SELECT name='vertretung_uid' id='vertretung_uid'>";
	//dropdown fuer vertretung
	$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE uid not LIKE '\\\_%' ORDER BY nachname, vorname";
	
	echo "<OPTION value=''>-- Auswahl --</OPTION>\n";
	
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if($zeitsperre->vertretung_uid == $row->uid)
				$selected='selected';
			else 
				$selected='';
			echo "<OPTION value='$row->uid' ".$selected.">$row->nachname $row->vorname ($row->uid)</OPTION>\n";
		}
	}
	echo '</SELECT></td>';
	echo '</tr><tr><td></td><td>';
	echo '<input type="submit" value="Speichern" name="save">';
	echo '</td></tr></table>';
	echo '</form>';
}
?>