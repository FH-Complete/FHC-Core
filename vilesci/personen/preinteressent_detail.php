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
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/preinteressent.class.php');
require_once('../../include/person.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/aufmerksamdurch.class.php');
require_once('../../include/firma.class.php');
require_once('../../include/nation.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$user = get_uid();

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

$datum_obj = new datum();
$stsem = new studiensemester($conn);
$stsem_aktuell = $stsem->getaktorNext();

$selection = (isset($_GET['selection'])?$_GET['selection']:'preinteressent');

echo '<html>
	<head>
		<title>PreInteressenten</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script language="Javascript">
		<!--
		var selection = "'.htmlentities($selection).'";
		
		function changeTo(id)
		{
			selection=id;
			document.getElementById(id).style.display="block";
			document.getElementById(id+"_label").style.textDecoration="underline";
			
			if(id=="personendaten")
			{
				document.getElementById("preinteressent").style.display="none";
				document.getElementById("preinteressent_label").style.textDecoration="none";
				document.getElementById("studiengangszuordnung").style.display="none";
				document.getElementById("studiengangszuordnung_label").style.textDecoration="none";
			}
			else if(id=="preinteressent")
			{
				document.getElementById("personendaten").style.display="none";
				document.getElementById("personendaten_label").style.textDecoration="none";
				document.getElementById("studiengangszuordnung").style.display="none";
				document.getElementById("studiengangszuordnung_label").style.textDecoration="none";
			}
			else if(id=="studiengangszuordnung")
			{
				document.getElementById("personendaten").style.display="none";
				document.getElementById("personendaten_label").style.textDecoration="none";
				document.getElementById("preinteressent").style.display="none";
				document.getElementById("preinteressent_label").style.textDecoration="none";
			}
		}
		
		function confdel()
		{
			return confirm("Wollen Sie diesen Eintrag wirklich loeschen?");
		}
		-->
		</script>
	</head>
	<body class="Background_main">
	';

if(!$rechte->isBerechtigt('admin', null, 'suid') && 
   !$rechte->isBerechtigt('preinteressent', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

if(isset($_GET['id']) && is_numeric($_GET['id']))
	$id = $_GET['id'];
else 
	die('<h2>Details</h2>');
	
	
$preinteressent = new preinteressent($conn);

if(!$preinteressent->load($id))
	die('Datensatz konnte nicht geladen werden');
	
$person = new person($conn);
if(!$person->load($preinteressent->person_id))
	die('Personen Datensatz konnte nicht geladen werden');

echo "<h2>Details - $person->nachname $person->vorname</h2>";

if(isset($_POST['save_preinteressent']))
{
	//Speichern der Preinteressentdaten
	
	$preinteressent->studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
	$preinteressent->aufmerksamdurch_kurzbz = $_POST['aufmerksamdurch_kurzbz'];
	$preinteressent->firma_id = $_POST['firma'];
	$preinteressent->erfassungsdatum = $_POST['erfassungsdatum'];
	$preinteressent->einverstaendnis = isset($_POST['einverstaendnis']);
	if(isset($_POST['absagedatum']) && $preinteressent->absagedatum=='')
		$preinteressent->absagedatum = date('Y-m-d H:i:s');
	if(!isset($_POST['absagedatum']))
		$preinteressent->absagedatum = '';
	$preinteressent->anmerkung = $_POST['anmerkung'];
	$preinteressent->updateamum = date('Y-m-d H:i:s');
	$preinteressent->updatevon = $user;
	$preinteressent->maturajahr = $_POST['maturajahr'];
	$preinteressent->infozusendung = $_POST['infozusendung'];

	if(!$preinteressent->save(false))
		echo "<b>Fehler beim Speichern der Daten: $preinteressent->errormsg</b>";
	else 
		echo "<b>Daten wurden gespeichert</b>";
}

if(isset($_POST['saveperson']))
{
	//Speichern der Personendaten
	
	$person->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
	$person->geburtsnation = $_POST['geburtsnation'];
	$person->sprache = $_POST['sprache'];
	$person->anrede = $_POST['anrede'];
	$person->titelpost = $_POST['titelpost'];
	$person->titelpre = $_POST['titelpre'];
	$person->nachname = $_POST['nachname'];
	$person->vorname = $_POST['vorname'];
	$person->vornamen = $_POST['vornamen'];
	$person->gebdatum = $_POST['gebdatum'];
	$person->gebort = $_POST['gebort'];
	$person->gebzeit = $_POST['gebzeit'];
	$person->anmerkungen = $_POST['anmerkungen'];
	$person->homepage = $_POST['homepage'];
	$person->svnr = $_POST['svnr'];
	$person->ersatzkennzeichen = $_POST['ersatzkennzeichen'];
	$person->familienstand = $_POST['familienstand'];
	$person->geschlecht = $_POST['geschlecht'];
	$person->anzahlkinder = $_POST['anzahlkinder'];
	$person->aktiv = isset($_POST['aktiv']);
	$person->updateamum = date('Y-m-d H:i:s');
	$person->updatevon = $user;
	
	if($person->save(false))
	{
		echo '<b>Daten wurden erfolgreich gespeichert</b>';
	}
	else 
	{
		echo "<b>Fehler beim Speichern der Daten: $person->errormsg</b>";
	}
	
}
if(isset($_GET['action']) && $_GET['action']=='neuezuordnung')
{
	//speichern einer neue Studiengangszuordnung
	$zuordnung = new preinteressent($conn);

	if(!$zuordnung->loadZuordnung($preinteressent->preinteressent_id, $_POST['studiengang_kz']))
	{
		$zuordnung->preinteressent_id = $preinteressent->preinteressent_id;
		$zuordnung->studiengang_kz = $_POST['studiengang_kz'];
		$zuordnung->prioritaet = $_POST['prioritaet'];
		$zuordnung->insertamum = date('Y-m-d H:i:s');
		$zuordnung->insertvon = $user;
		
		if(!$zuordnung->saveZuordnung(true))
			echo "<b>Fehler beim Speichern: $zuordnung->errormsg</b>";
	}
	else 
		echo "<b>Es besteht bereits eine Zuordnung zu diesem Studiengang</b>";
}

if(isset($_GET['savezuordnung']))
{
	//bestehende Zuordnung speichern
	$zuordnung = new preinteressent($conn);	
	
	if($zuordnung->loadZuordnung($preinteressent->preinteressent_id, $_GET['studiengang_kz']))
	{
		$zuordnung->prioritaet = $_POST['prioritaet'];
		$zuordnung->updateamum = date('Y-m-d H:i:s');
		$zuordnung->updatevon = $user;
		
		if(!$zuordnung->saveZuordnung(false))
			echo "<b>Fehler beim Speichern der Daten: $zuordnung->errormsg</b>";
	}
	else 
		echo '<b>Fehler beim Speichern der Daten: Datensatz wurde nicht gefunden</b>';
}

if(isset($_POST['freigabe']))
{
	//freigabe einer zuordnung
	$zuordnung = new preinteressent($conn);
	if($zuordnung->loadZuordnung($preinteressent->preinteressent_id, $_GET['studiengang_kz']))
	{
		if($zuordnung->freigabedatum=='')
		{
			$zuordnung->freigabedatum = date('Y-m-d H:i:s');
			$zuordnung->updateamum = date('Y-m-d H:i:s');
			$zuordnung->updatevon = $user;
		
			if(!$zuordnung->saveZuordnung(false))
				echo "<b>Fehler beim Speichern der Daten: $zuordnung->errormsg</b>";
			else 
			{
				//MAIL an Assistenz verschicken
				$qry_person = "SELECT vorname, nachname 
								FROM public.tbl_person JOIN public.tbl_preinteressent USING(person_id) 
								WHERE preinteressent_id='$preinteressent->preinteressent_id'";
				$name='';
				if($result_person = pg_query($conn, $qry_person))
					if($row_person = pg_fetch_object($result_person))
						$name = $row_person->nachname.' '.$row_person->vorname;
				$stg_obj = new studiengang($conn);
				$stg_obj->load($zuordnung->studiengang_kz);
				$to = $stg_obj->email;
				$to = 'oesi@technikum-wien.at';
				$message = "Dies ist eine automatische Mail! $stg_obj->email\n\n".
							"Der Preinteressent $name wurde zur Übernahme freigegeben. \nSie können diesen ".
							"im FAS unter 'Extras->Preinteressenten übernehmen' oder unter folgendem Link\n\n".
							APP_ROOT."vilesci/personen/preinteressent_uebernahme.php?studiengang_kz=$zuordnung->studiengang_kz \n".
							"ins FAS übertragen";
				mail($to, 'Preinteressent Freigabe', $message, 'FROM: vilesci@'.DOMAIN);
			}
		}
		else 
		{
			echo '<b>Diese Zuteilung ist bereits freigegeben</b>';
		}
	}
	else 
		echo '<b>Fehler beim Speichern der Daten: Datensatz wurde nicht gefunden</b>';	
}
if(isset($_POST['freigabe_rueckgaengig']))
{
	//studiengangsfreigabe zurueckziehen
	$zuordnung = new preinteressent($conn);
	if($zuordnung->loadZuordnung($preinteressent->preinteressent_id, $_GET['studiengang_kz']))
	{
		if($zuordnung->freigabedatum!='')
		{
			if($zuordnung->uebernahmedatum=='')
			{
				$zuordnung->freigabedatum = '';
				$zuordnung->updateamum = date('Y-m-d H:i:s');
				$zuordnung->updatevon = $user;
		
				if(!$zuordnung->saveZuordnung(false))
					echo "<b>Fehler beim Speichern der Daten: $zuordnung->errormsg</b>";	
			}
			else 
			{
				echo '<b>Freigabe kann nicht R&uuml;ckg&auml;ngig gemacht werden da der Datensatz bereits &uuml;bernommen wurde</b>';
			}
		}
		else 
		{
			echo '<b>Diese Zuteilung ist bereits freigegeben</b>';
		}
	}
	else 
		echo '<b>Fehler beim Speichern der Daten: Datensatz wurde nicht gefunden</b>';	
}
if(isset($_POST['zuordnungloeschen']))
{
	//zuordnung zu einem studiengang loeschen
	$zuordnung = new preinteressent($conn);
	if($zuordnung->loadZuordnung($preinteressent->preinteressent_id, $_GET['studiengang_kz']))
	{
		if($zuordnung->uebernahmedatum=='')
		{
			if(!$zuordnung->deleteZuordnung($preinteressent->preinteressent_id, $_GET['studiengang_kz']))
				echo "<b>Fehler beim L&ouml;schen der Zuteilung: $zuordnung->errormsg</b>";
		}
		else 
		{
			echo '<b>Diese Zuteilung wurde bereits uebernommen und kann daher nicht geloescht werden</b>';
		}
	}
	else 
		echo '<b>Fehler beim Speichern der Daten: Datensatz wurde nicht gefunden</b>';	
}

// ----- TABS ------
echo '<h3><a id="preinteressent_label" href="javascript: changeTo(\'preinteressent\');" '.($selection=='preinteressent'?'style="text-decoration:underline"':'').'>PreInteressent</a> - ';
echo '<a id="studiengangszuordnung_label" href="javascript: changeTo(\'studiengangszuordnung\');"'.($selection=='studiengangszuordnung'?'style="text-decoration:underline"':'').'>Studiengangszuordnung</a> - ';
echo '<a id="personendaten_label" href="javascript: changeTo(\'personendaten\');"'.($selection=='personendaten'?'style="text-decoration:underline"':'').'>Personendaten</a></h3>';

// ----- PERSON -----
echo "<div id='personendaten' style='display: ".($selection=='personendaten'?'block':'none')."'>";

$disabled=true;
$qry = "SELECT count(*) as anzahl FROM (
		SELECT 1 FROM public.tbl_prestudent WHERE person_id='$person->person_id' UNION 
		SELECT 1 FROM public.tbl_benutzer WHERE person_id='$person->person_id') as foo";
if($result = pg_query($conn, $qry))
{
	if($row = pg_fetch_object($result))
	{
		if($row->anzahl==0)
			$disabled=false;
	}
}

echo "<form action='".$_SERVER['PHP_SELF']."?id=$preinteressent->preinteressent_id&selection=personendaten' method='POST'>";
echo "<table><tr>";

//Anrede
echo "<td>Anrede:</td><td><input type='text' name='anrede' ".($disabled?'disabled':'')." value='".htmlentities($person->anrede, ENT_QUOTES)."'></td>";
//Titelpre
echo "<td>Titelpre:</td><td><input type='text' name='titelpre' ".($disabled?'disabled':'')." value='".htmlentities($person->titelpre, ENT_QUOTES)."'></td>";
//Titelpost
echo "<td>Titelpost:</td><td><input type='text' name='titelpost' ".($disabled?'disabled':'')." value='".htmlentities($person->titelpost, ENT_QUOTES)."'></td>";
echo '<td width="100%" align="right"><a href="personendetails.php?id='.$person->person_id.'" target="_blank">Gesamtübersicht über diese Person</a></td>';
echo '</tr><tr>';
//Nachname
echo "<td>Nachname*:</td><td><input type='text' name='nachname' ".($disabled?'disabled':'')." value='".htmlentities($person->nachname, ENT_QUOTES)."'></td>";
//Vorname
echo "<td>Vorname:</td><td><input type='text' name='vorname' ".($disabled?'disabled':'')." value='".htmlentities($person->vorname, ENT_QUOTES)."'></td>";
//Vornamen
echo "<td>2. Vorname:</td><td><input type='text' name='vornamen' ".($disabled?'disabled':'')." value='".htmlentities($person->vornamen, ENT_QUOTES)."'></td>";
echo '<td width="100%" align="right">';
if(!$disabled)
	echo "<a href='kontaktdaten_edit.php?person_id=$person->person_id' target='_blank'>Kontaktdaten bearbeiten</a>";
else 
	echo "Kontaktdaten bearbeiten";
echo '</td>';
echo '</tr><tr>';
//Geburtsdatum
echo "<td>Geburtsdatum:</td><td><input type='text' name='gebdatum' ".($disabled?'disabled':'')." value='".htmlentities($datum_obj->formatDatum($person->gebdatum, 'd.m.Y'), ENT_QUOTES)."'></td>";
//Geburtsort
echo "<td>Geburtsort:</td><td><input type='text' name='gebort' ".($disabled?'disabled':'')." value='".htmlentities($person->gebort, ENT_QUOTES)."'></td>";
//Geburtszeit
echo "<td>Geburtszeit:</td><td><input type='text' name='gebzeit' size='5' ".($disabled?'disabled':'')." value='".htmlentities($person->gebzeit, ENT_QUOTES)."'></td>";
echo '</tr><tr>';
//Staatsbuergerschaft
echo "<td>Staatsb&uuml;rgerschaft:</td><td><SELECT ".($disabled?'disabled':'')." name='staatsbuergerschaft'>";
echo "<option value=''>-- Keine Auswahl --</option>";
$nation = new nation($conn);
$nation->getAll();

foreach ($nation->nation as $row)
{
	if($row->code==$person->staatsbuergerschaft)
		$selected='selected';
	else 
		$selected='';
	
	echo "<option value='$row->code' $selected>$row->kurztext</option>";
}
echo "</SELECTED>";
echo "</td>";
//Geburtsnation
echo "<td>Geburtsnation:</td><td><SELECT ".($disabled?'disabled':'')." name='geburtsnation'>";
echo "<option value=''>-- Keine Auswahl --</option>";
$nation = new nation($conn);
$nation->getAll();

foreach ($nation->nation as $row)
{
	if($row->code==$person->geburtsnation)
		$selected='selected';
	else 
		$selected='';
	
	echo "<option value='$row->code' $selected>$row->kurztext</option>";
}
echo "</SELECTED>";
echo "</td>";
//Sprache
echo "<td>Sprache:</td><td><SELECT ".($disabled?'disabled':'')." name='sprache'>";
echo "<option value=''>-- keine Auswahl --</option>";
$qry = "SELECT * FROM public.tbl_sprache ORDER BY sprache";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($row->sprache==$person->sprache)
			$selected='selected';
		else 
			$selected='';
		
		echo "<option value='$row->sprache' $selected>$row->sprache</option>";
	}
}
echo '</SELECT></td>';
echo '</tr><tr>';
//SVNR
echo "<td>SVNR:</td><td><input type='text' name='svnr' ".($disabled?'disabled':'')." value='".htmlentities($person->svnr, ENT_QUOTES)."'></td>";
//Ersatzkennzeichen
echo "<td>Ersatzkennzeichen</td><td><input type='text' name='ersatzkennzeichen' ".($disabled?'disabled':'')." value='".htmlentities($person->ersatzkennzeichen, ENT_QUOTES)."'></td>";
//Geschlecht
echo "<td>Geschlecht*</td><td><SELECT ".($disabled?'disabled':'')." name='geschlecht'>";
echo '<option value="m" '.($person->geschlecht=='m'?'selected':'').'>männlich</option>';
echo '<option value="w" '.($person->geschlecht=='w'?'selected':'').'>weiblich</option>';
echo '</SELECT></td>';
echo '</tr><tr>';

//Anzahlkinder
echo "<td>Anzahl der Kinder</td><td><input type='text' name='anzahlkinder' ".($disabled?'disabled':'')." value='".htmlentities($person->anzahlkinder, ENT_QUOTES)."'></td>";
//Aktiv
echo "<td>Aktiv:</td><td><input type='checkbox' ".($disabled?'disabled':'')." name='aktiv' ".($person->aktiv==false?'':'checked')."></td>";
echo '</tr><tr valign="top">';
//Anmerkung
echo "<td>Anmerkung:</td><td><textarea ".($disabled?'disabled':'')." name='anmerkungen'>".htmlentities($person->anmerkungen)."</textarea></td>";
//Homepage
echo "<td>Homepage:</td><td><input type='text' name='homepage' ".($disabled?'disabled':'')." value='".htmlentities($person->homepage, ENT_QUOTES)."'></td>";
//Familienstand
echo "<td>Familienstand:</td><td><SELECT ".($disabled?'disabled':'')." name='familienstand'>";
echo '<option value="l" '.($person->familienstand=='l'?'selected':'').'>ledig</option>';
echo '<option value="v" '.($person->familienstand=='v'?'selected':'').'>verheiratet</option>';
echo '<option value="g" '.($person->familienstand=='g'?'selected':'').'>geschieden</option>';
echo '<option value="w" '.($person->familienstand=='w'?'selected':'').'>verwitwert</option>';
echo '</SELECT></td>';
echo "</tr><tr><td></td><td></td><td></td><td></td><td></td><td><input ".($disabled?'disabled':'')." type='submit' value='Speichern' name='saveperson'></td>";
echo "</tr></table></form>";
echo "</div>";

// ----- PREINTERESSENT -----
echo "<div id='preinteressent' style='display: ".($selection=='preinteressent'?'block':'none')."'>";
echo "<form action='".$_SERVER['PHP_SELF']."?id=$preinteressent->preinteressent_id&selection=preinteressent' method='POST'>";

echo '<table width="100%"><tr>';

//STUDIENSEMESTER
echo "<td>Studiensemester:</td><td><SELECT name='studiensemester_kurzbz'>";
$stsem = new studiensemester($conn);
$stsem->getAll();
foreach ($stsem->studiensemester as $row)
{
	if($row->studiensemester_kurzbz==$preinteressent->studiensemester_kurzbz)
		$selected='selected';
	else
		$selected='';
		
	echo "<option value='$row->studiensemester_kurzbz' $selected>$row->studiensemester_kurzbz</option>";
}
echo "</SELECT>";

echo '</td>';

//AUFMERKSAMDURCH
echo "<td>Aufmerksam durch:</td><td> <SELECT name='aufmerksamdurch_kurzbz'>";
$aufmerksam = new aufmerksamdurch($conn);
$aufmerksam->getAll();
foreach ($aufmerksam->result as $row)
{
	if($row->aufmerksamdurch_kurzbz==$preinteressent->aufmerksamdurch_kurzbz)
		$selected='selected';
	else
		$selected='';
		
	echo "<option value='$row->aufmerksamdurch_kurzbz' $selected>$row->aufmerksamdurch_kurzbz</option>";
}
echo "</SELECT>";
echo '</td>';

//SCHULE
echo "<td>Schule:</td><td> <SELECT name='firma'>";
$firma = new firma($conn);
$firma->getAll();
foreach ($firma->result as $row)
{
	if($row->firma_id==$preinteressent->firma_id)
		$selected='selected';
	else
		$selected='';
		
	echo "<option value='$row->firma_id' $selected>$row->name</option>";
}
echo "</SELECT> <a href='../stammdaten/firma_frameset.html' target='_blank'>Schulverwaltung</a></td>";

echo '</tr><tr>';

//Erfassungsdatum
echo "<td>Erfassungsdatum:</td><td> <input type='text' size='10' maxlength='10' name='erfassungsdatum' value='".$datum_obj->formatDatum($preinteressent->erfassungsdatum,'d.m.Y')."'></td>";

//Einverstaendnis
echo "<td>Einverst&auml;ndnis:</td><td><input type='checkbox' ".($preinteressent->einverstaendnis?'checked':'')." name='einverstaendnis'></td>";

//Absagedatum
echo "<td>Absage</td><td><input type='checkbox' ".($preinteressent->absagedatum!=''?'checked':'')." name='absagedatum'></td>";

echo '</tr><tr>';

//Infozusendung
echo "<td>Infozusendung am</td><td><input type='text' name='infozusendung' size='10' maxlength='10' value='".$datum_obj->formatDatum($preinteressent->infozusendung,'d.m.Y')."'></td>";


//Maturajahr
echo "<td>Maturajahr</td><td><input type='text' name='maturajahr' size='4' maxlength='4' value='$preinteressent->maturajahr'></td>";


echo '</tr><tr>';

//Anmerkung
echo '<td>Anmerkungen:</td>';
echo '<td colspan="5">';
echo "<textarea rows='4' style='width: 100%' name='anmerkung'>".htmlentities($preinteressent->anmerkung)."</textarea>";
echo '</td>';

echo '</tr><tr>';
echo '<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td align="right"><input type="submit" name="save_preinteressent" value="Speichern"></td>
		';

echo '</tr></table>';
echo '</form>';
echo "</div>";

// ----- Studiengangszuordnung -----
echo "<div id='studiengangszuordnung' style='display: ".($selection=='studiengangszuordnung'?'block':'none')."'>";

echo '<table class="liste table-stripeclass:alternate table-autostripe"><tr><th>Studiengang</th><th>Priorit&auml;t</th><th>Freigabe</th><th>&Uuml;bernahme</th><th colspan="2">Aktion</th></tr>';
$zuordnung = new preinteressent($conn);
$zuordnung->loadZuordnungen($preinteressent->preinteressent_id);

foreach ($zuordnung->result as $row)
{
	echo "<form action='".$_SERVER['PHP_SELF']."?id=$preinteressent->preinteressent_id&studiengang_kz=$row->studiengang_kz&selection=studiengangszuordnung' method='POST'>";
	echo '<tr>';
	echo '<td>';
	$studiengang = new studiengang($conn);
	$studiengang->load($row->studiengang_kz);
	echo "$studiengang->kuerzel - $studiengang->bezeichnung";
	echo '</td>';
	echo '<td>';
	echo '<SELECT name="prioritaet" onchange="this.form.action=this.form.action+\'&savezuordnung\';this.form.submit();">';
	echo '<option value="1" '.($row->prioritaet==1?'selected':'').'>niedrig (1)</option>';
	echo '<option value="2" '.($row->prioritaet==2?'selected':'').'>mittel (2)</option>';
	echo '<option value="3" '.($row->prioritaet==3?'selected':'').'>hoch (3)</option>';
	echo '</SELECT>';
	echo '</td>';
	echo '<td>';
	//Wenn noch nicht freigegeben - Freigabe Button anzeigen
	if($row->freigabedatum=='')
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_prestudent WHERE person_id='$person->person_id' AND studiengang_kz='$row->studiengang_kz'";
		if($result_check = pg_query($conn, $qry))
		{
			if($row_check = pg_fetch_object($result_check))
			{
				if($row_check->anzahl==0)
				{
					echo '<input type="submit" name="freigabe" value="Freigeben">';			
				}
				else 
				{
					echo 'ist bereits im Studiengang erfasst';
				}
			}
		}
		
	}
	else
	{
		if($row->uebernahmedatum=='')
		{
			//Wenn freigegeben aber noch nicht uebernommen -> zurueckziehen button anzeigen
			echo '<input type="submit" name="freigabe_rueckgaengig" value="Freigabe zur&uuml;ckziehen">';
		}
		else 
		{
			//Wenn freigegeben und uebernommen -> Freigabedatum anzeigen
			echo $datum_obj->formatDatum($row->freigabedatum, 'd.m.Y H:i:s');
		}
	}
	echo '</td>';
	
	echo '<td>';
	echo $datum_obj->formatDatum($row->uebernahmedatum, 'd.m.Y H:i:s');
	echo '</td>';
	echo '<td>';
	//echo '<input type="submit" value="Speichern" name="savezuordnung">';
	echo '</td>';
	echo '<td>';
	if($row->uebernahmedatum=='')
		echo '<input type="submit" value="L&ouml;schen" name="zuordnungloeschen" onclick="return confdel();">';
	echo '</td>';
	echo '</tr></form>';
}

//Neuer Eintrag
echo "<form action='".$_SERVER['PHP_SELF']."?id=$preinteressent->preinteressent_id&selection=studiengangszuordnung&action=neuezuordnung' method='POST'>";	
echo '<tr>';
echo '<td>';
echo '<SELECT name="studiengang_kz">';
$studiengang = new studiengang($conn);
$studiengang->getAll('typ, kurzbz', false);

foreach ($studiengang->result as $rowstg)
{
	echo "<option value='$rowstg->studiengang_kz' $selected>$rowstg->kuerzel - $rowstg->bezeichnung</option>";
}
echo '</SELECT>';
echo '</td>';
echo '<td>';
echo '<SELECT name="prioritaet">';
echo '<option value="1" selected>niedrig (1)</option>';
echo '<option value="2">mittel (2)</option>';
echo '<option value="3">hoch (3)</option>';
echo '</SELECT>';
echo '</td>';
echo '<td>';
//Freigabedatum
echo '</td>';

echo '<td>';
//Uebernahmedatum
echo '</td>';
echo '<td>';
echo '<input type="submit" value="Neu" name="speichern">';
echo '</td>';
echo '<td>';

echo '</td>';
echo '</tr></form>';

echo '</table>';
echo '</div>';

echo '</body>';
echo '</html>';
?>