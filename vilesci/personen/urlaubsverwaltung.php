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
/**
 *
 * Seite zur Verwaltung der Urlaubs- und Zeitausgleichstage der Mitarbeiter
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/zeitsperre.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/addon.class.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/phrasen.class.php');

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
$freigabeamum=(isset($_POST['freigabe_amum'])?$_POST['freigabe_amum']:'');
$freigabevon=(isset($_POST['freigabe_von'])?$_POST['freigabe_von']:'');

$alle = (isset($_GET['alle'])?true:false);

$errormsg='';
$message='';
$error=false;
$mlAbgeschickt = '';

//Phrasen
$sprache = getSprache();
$p = new phrasen($sprache);

//Default-Wert für Max-Intervall in Tagen für Zeitsperre, über Config veränderbar
$maxDauerZS = 730;

if (defined('CIS_ZEITSPERREN_MAX_DAUER') && CIS_ZEITSPERREN_MAX_DAUER != '') {
	$maxDauerZS = CIS_ZEITSPERREN_MAX_DAUER;
}
//prüfen, ob addon casetime aktiviert ist
$addon_obj = new addon();
$addoncasetime = $addon_obj->checkActiveAddon("casetime");
if ($addoncasetime)
{
	require_once('../../addons/casetime/include/functions.inc.php');
}

//Kopfzeile
echo '<html>
	<head>
		<title>Zeitsperren (Urlaube) der MitarbeiterInnen</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">';

		include('../../include/meta/jquery.php');
		include('../../include/meta/jquery-tablesorter.php');

echo '	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script language="Javascript">
		function confdel(val)
		{
			return confirm("Wollen Sie diesen Eintrag wirklich loeschen: "+val);
		}
		function rejdel(val)
		{
			return confirm("ACHTUNG! Die Zeitsperre ("+val + ") wurde bereits in einer abgeschickten Monatsliste verarbeitet und kann nicht gelöscht werden.");
		}
		$(document).ready(function()
		{
			$("#ma_name").autocomplete({
			source: "../../cis/private/tools/zeitaufzeichnung_autocomplete.php?autocomplete=kunde",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].uid;
					ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#ma_name").val(ui.item.uid);
			}
			});
			$("#t1").tablesorter(
			{
				sortList: [[3,1]],
				widgets: [\'zebra\', \'filter\'],
				headers : { 3 : { sorter: "shortDate", dateFormat: "ddmmyyyy" },4 : { sorter: "shortDate", dateFormat: "ddmmyyyy" } }
			});
			$( ".datepicker_datum" ).datepicker({
					 changeMonth: true,
					 changeYear: true,
					 dateFormat: "dd.mm.yy",
			});
		})
		</script>
	</head>
	<body class="Background_main">
	<h2>Zeitsperren (Urlaube) der MitarbeiterInnen</h2>
	';


$redirect = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);

//Rechte Pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$berechtigt = false;

$bf = new benutzerfunktion();
$bf->getBenutzerFunktionByUid($uid);
foreach ($bf->result as $oe)
{
	if($oe->funktion_kurzbz == "oezuordnung")
		if($rechte->isBerechtigt('mitarbeiter/zeitsperre:begrenzt', $oe->oe_kurzbz, 'suid'))
			$berechtigt = true;
}

//Formular zur Eingabe der UID
echo '<form  accept-charset="UTF-8" action="'.$_SERVER['PHP_SELF'].'" method="GET">';
echo 'Zeitsperren der UID <INPUT type="hidden" id="uid" name="uid" value="uid">
		<input type="text" id="ma_name" name="uid" value="'.$uid.'">';
echo '<input type="submit" name="submit" value="Anzeigen">';
echo '</form>';

if(!$berechtigt)
	die("Sie haben keine Berechtigung um Mitarbeiter*in " . $uid . " zu bearbeiten!<br><br> <a href='$redirect'>Zurück</a>");

//Loeschen von Zeitsperren
if($action=='delete')
{
	if(!$berechtigt)
		die('Sie haben keine Berechtigung für diese Aktion');

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

//Kopieren einer Zeitsperre
if($action=='copy')
{
	if(!$berechtigt)
		die('Sie haben keine Berechtigung für diese Aktion');

	if($zeitsperre_id!='' && is_numeric($zeitsperre_id))
	{
		$zeitsperre = new zeitsperre($zeitsperre_id);
		$zeitsperre->new = true;

		if($zeitsperre->save())
		{
			$message = 'Eintrag wurde erfolgreich kopiert';
		}
		else
		{
			$errormsg = "Fehler beim Kopieren der Zeitsperre: $zeitsperre->errormsg";
		}
	}
	else
		$errormsg='Zeitsperre_id ist ungueltig';
}

if(isset($_POST['save']))
{
	if(!$berechtigt)
		die('Sie haben keine Berechtigung für diese Aktion');

	//Validierungen Felder Bis-Datum und Von-Datum
	if($vondatum > $bisdatum)
	{
		$errormsg = $p->t('zeitsperre/vonDatumGroesserAlsBisDatum').'! ';
		$error=true;
	}

	//Check if Bisdatum zu weit in der Zukunft
	$von = new DateTime($vondatum);
	$bis = new DateTime($bisdatum);

	$intervall = $bis->diff($von);
	if ($intervall->days > $maxDauerZS)
	{
		$error=true;
		$errormsg = $p->t('zeitsperre/bisDatumGroesserMax');
	}

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
		$zeitsperre->vondatum = $datum->formatDatum($vondatum);
		$zeitsperre->vonstunde = $vonstunde;
		$zeitsperre->bisdatum = $datum->formatDatum($bisdatum);
		$zeitsperre->bisstunde = $bisstunde;
		$zeitsperre->vertretung_uid = $vertretung_uid;
		$zeitsperre->erreichbarkeit_kurzbz = $erreichbarkeit_kurzbz;
		$zeitsperre->updateamum = date('Y-m-d H:i:s');
		$zeitsperre->updatevon = $user;
		$zeitsperre->freigabeamum = $datum->formatDatum($freigabeamum);
		$zeitsperre->freigabevon = $freigabevon;

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

	echo "<a href='".$_SERVER['PHP_SELF']."?uid=$uid&alle=true'>Alle Einträge anzeigen</a>";
	$zeitsperre->getzeitsperren($uid, !$alle);
	echo '<h3>Zeitsperren von <b>'.$mitarbeiter->titelpre.' '.$mitarbeiter->vorname.' '.$mitarbeiter->nachname.' '.$mitarbeiter->titelpost.'</b></h3>';
	echo "<table id='t1' class='tablesorter'>";
	echo '
	<thead>
		<tr class="liste">
			<th>ID</th>
			<th>Typ</th>
			<th>Bezeichnung</th>
			<th>Von</th>
			<th>Bis</th>
			<th>Vertretung</th>';

	if($addoncasetime)
	{
		echo '<th>Status Monatsliste</th>';
	}

	echo'
	<th>Freigegeben von, am</th>
	<th>Aktualisiert am</th>
	<th>Aktualisiert von</th>
	<th>Edit</th>
	<th>Copy</th>
	<th>Delete</th>
	</tr>
	</thead>
	<tbody>';
	foreach ($zeitsperre->result as $row)
	{
		echo '<tr>';
		echo "<td>$row->zeitsperre_id</td>";
		echo "<td>$row->zeitsperretyp_kurzbz</td>";
		echo "<td>$row->bezeichnung</td>";
		echo "<td data-sorter='shortDate' data-date-format='dd.mm.yyyy'>".$datum->formatDatum($row->vondatum,'d.m.Y')." ".($row->vonstunde!=''?'(Stunde '.$row->vonstunde.')':'')."</td>";
		echo "<td data-sorter='shortDate' data-date-format='dd.mm.yyyy'>".$datum->formatDatum($row->bisdatum,'d.m.Y')." ".($row->bisstunde!=''?'(Stunde '.$row->bisstunde.')':'')."</td>";
		echo "<td>$row->vertretung_uid</td>";

		if($addoncasetime)
		{
			echo "<td align='center'>";
			checkStatusMonatsliste($uid,$row->vondatum, $row->bisdatum) == '' ? $mlAbgeschickt = false : $mlAbgeschickt = true;

			if($mlAbgeschickt)
			echo "abgeschickt";
			else
			echo "nicht abgeschickt";

			echo '</td>';
		}
		echo "<td>$row->freigabevon ".$datum->formatDatum($row->freigabeamum,'d.m.Y')."</td>";
		echo "<td>".$datum->formatDatum($row->updateamum,'d.m.Y H:i:s')."</td>";
		echo "<td>$row->updatevon</td>";

		//nur Zeitsperren von noch nicht abgeschickten Monatlisten dürfen gelöscht werden
		if ( ($addoncasetime) && ($mlAbgeschickt && in_array($row->zeitsperretyp_kurzbz, zeitsperre::getBlockierendeZeitsperren())) )
		{
			echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?action=edit&uid=$uid&zeitsperre_id=$row->zeitsperre_id".($alle?'&alle=true':'')."'><img src='../../skin/images/application_form_edit.png' alt='bearbeiten' title='bearbeiten' /></a></td>";
			echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?action=copy&uid=$uid&zeitsperre_id=$row->zeitsperre_id".($alle?'&alle=true':'')."'><img src='../../skin/images/copy.png' alt='bearbeiten' title='kopieren' /></a></td>";
			echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?uid=$uid&zeitsperre_id=$row->zeitsperre_id".($alle?'&alle=true':'')."'' onclick='return rejdel(\"$row->zeitsperretyp_kurzbz von ".$datum->formatDatum($row->vondatum,'d.m.Y')." bis ".$datum->formatDatum($row->bisdatum,'d.m.Y'). "" . "\") '><img src='../../skin/images/application_form_delete.png' alt='loeschen' title='loeschen'/></a></td>";
		}
		else
		{
			echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?action=edit&uid=$uid&zeitsperre_id=$row->zeitsperre_id".($alle?'&alle=true':'')."'><img src='../../skin/images/application_form_edit.png' alt='bearbeiten' title='bearbeiten' /></a></td>";
			echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?action=copy&uid=$uid&zeitsperre_id=$row->zeitsperre_id".($alle?'&alle=true':'')."'><img src='../../skin/images/copy.png' alt='bearbeiten' title='kopieren' /></a></td>";
			echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?action=delete&uid=$uid&zeitsperre_id=$row->zeitsperre_id".($alle?'&alle=true':'')."'' onclick='return confdel(\"$row->zeitsperretyp_kurzbz von ".$datum->formatDatum($row->vondatum,'d.m.Y')." bis ".$datum->formatDatum($row->bisdatum,'d.m.Y')."\")'><img src='../../skin/images/application_form_delete.png' alt='loeschen' title='loeschen'/></a></td>";
		}


		echo '</tr>';

	}
	echo '</tbody></table>';


	//Editieren und Neu anlegen von Zeitsperren
	$zeitsperre = new zeitsperre();
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

	echo '<form accept-charset="UTF-8" action="'.$_SERVER['PHP_SELF'].'?uid='.$uid.($alle?'&alle=true':'').'" method="POST">';
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

	echo '<td>Von</td><td><input class="datepicker_datum" type="text" name="von" value="'.$datum->formatDatum($zeitsperre->vondatum,'d.m.Y').'">';
	echo ' Stunde (inklusive)';
	echo '<SELECT name="vonstunde">';
	if($zeitsperre->vonstunde=='')
		echo "<OPTION value='' selected>*</OPTION>\n";
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

	echo '<td>Bis</td><td><input class="datepicker_datum" type="text" name="bis" value="'.$datum->formatDatum($zeitsperre->bisdatum,'d.m.Y').'">';
	echo ' Stunde (inklusive)';
	echo '<SELECT name="bisstunde">';
	if($zeitsperre->bisstunde=='')
		echo "<OPTION value='' selected>*</OPTION>\n";
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
	$qry = "SELECT DISTINCT uid,vorname,nachname FROM campus.vw_mitarbeiter WHERE uid not LIKE '\\\_%' AND aktiv=true
			UNION SELECT uid,vorname,nachname FROM campus.vw_mitarbeiter WHERE uid=".$db->db_add_param($zeitsperre->vertretung_uid)."
			ORDER BY nachname, vorname";

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
	echo '</SELECT></td></tr>';
	echo '<tr><td>&nbsp;</td><td></td></tr><tr>';
	echo '<td>Freigegeben von</td>';
	echo "<td><SELECT name='freigabe_von' id='freigabe_von'>";
	//dropdown fuer freigabe
	$qry = "SELECT DISTINCT uid,vorname,nachname FROM campus.vw_mitarbeiter WHERE uid not LIKE '\\\_%' AND aktiv=true
			UNION SELECT uid,vorname,nachname FROM campus.vw_mitarbeiter WHERE uid=".$db->db_add_param($zeitsperre->vertretung_uid)."
			ORDER BY nachname, vorname";

	echo "<OPTION value=''>-- Keine Auswahl --</OPTION>\n";

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if($zeitsperre->freigabevon == $row->uid)
				$selected='selected';
			else
				$selected='';
			echo "<OPTION value='$row->uid' ".$selected.">$row->nachname $row->vorname ($row->uid)</OPTION>\n";
		}
	}
	echo '</SELECT></td><tr>';

	//input fuer freigabeamum
	echo '<td>Freigegeben am</td>';
	echo '<td><input class="datepicker_datum" type="text" name="freigabe_amum" value="'.$datum->formatDatum($zeitsperre->freigabeamum,'d.m.Y').'">';
	echo '</tr><tr><td></td><td>';
	echo '<input type="submit" value="Speichern" name="save">';
	echo '</td></tr></table>';
	echo '</form>';
}

echo '</body></html>';
?>
