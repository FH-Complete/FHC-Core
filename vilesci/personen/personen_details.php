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
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/nation.class.php');
require_once('../../include/ort.class.php');
require_once('../../include/akte.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/geschlecht.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

echo '
<html>
<head>
	<title>Details</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<script language="javascript">
		// ****
		// * Liefert einen Timestamp in Sekunden
		// * zum anhaengen an eine URL um Caching zu verhindern
		// ****
		function gettimestamp()
		{
			var now = new Date();
			var ret = now.getHours()*60*60*60;
			ret = ret + now.getMinutes()*60*60;
			ret = ret + now.getSeconds()*60;
			ret = ret + now.getMilliseconds();
			return ret;
		}

		function RefreshImage()
		{
			path=document.getElementById("personimage").src;
			document.getElementById("personimage").src="";
			document.getElementById("personimage").src=path+"&"+gettimestamp();
		}
	</script>
</head>

<body class="background_main">
';

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('student/stammdaten', null, 's') && !$rechte->isBerechtigt('mitarbeiter/stammdaten', null, 's'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$error_person_save = false;
$error_benutzer_save = false;
$error_mitarbeiter_save = false;
$error_student_save = false;

$msg = '';

$uid = (isset($_GET['uid'])?$_GET['uid']:'');
$person_id = (isset($_GET['person_id'])?$_GET['person_id']:'');

$anrede = (isset($_POST['anrede'])?$_POST['anrede']:'');
$titelpre = (isset($_POST['titelpre'])?$_POST['titelpre']:'');
$titelpost = (isset($_POST['titelpost'])?$_POST['titelpost']:'');
$nachname = (isset($_POST['nachname'])?$_POST['nachname']:'');
$vorname = (isset($_POST['vorname'])?$_POST['vorname']:'');
$vornamen = (isset($_POST['vornamen'])?$_POST['vornamen']:'');
$geburtsdatum = (isset($_POST['geburtsdatum'])?$_POST['geburtsdatum']:'');
$geburtsort = (isset($_POST['geburtsort'])?$_POST['geburtsort']:'');
$geburtsnation = (isset($_POST['geburtsnation'])?$_POST['geburtsnation']:'');
$svnr = (isset($_POST['svnr'])?$_POST['svnr']:'');
$ersatzkennzeichen = (isset($_POST['ersatzkennzeichen'])?$_POST['ersatzkennzeichen']:'');
$geburtszeit = (isset($_POST['geburtszeit'])?$_POST['geburtszeit']:'');
$staatsbuergerschaft = (isset($_POST['staatsbuergerschaft'])?$_POST['staatsbuergerschaft']:'');
$sprache = (isset($_POST['sprache'])?$_POST['sprache']:'');
$geschlecht = (isset($_POST['geschlecht'])?$_POST['geschlecht']:'');
$familienstand = (isset($_POST['familienstand'])?$_POST['familienstand']:'');
$anzahlderkinder = (isset($_POST['anzahlderkinder'])?$_POST['anzahlderkinder']:'');
$anmerkungen = (isset($_POST['anmerkungen'])?$_POST['anmerkungen']:'');
$homepage = (isset($_POST['homepage'])?$_POST['homepage']:'');
$aktiv = (isset($_POST['aktiv'])?$_POST['aktiv']:'');
$alias = (isset($_POST['alias'])?$_POST['alias']:'');
$personalnummer = (isset($_POST['personalnummer'])?$_POST['personalnummer']:'');
$telefonklappe = (isset($_POST['telefonklappe'])?$_POST['telefonklappe']:'');
$kurzbz = (isset($_POST['kurzbz'])?$_POST['kurzbz']:'');
$lektor = (isset($_POST['lektor'])?$_POST['lektor']:'');
$fixangestellt = (isset($_POST['fixangestellt'])?$_POST['fixangestellt']:'');
$stundensatz = (isset($_POST['stundensatz'])?$_POST['stundensatz']:'');
$ausbildungcode = (isset($_POST['ausbildungcode'])?$_POST['ausbildungcode']:'');
$ort_kurzbz = (isset($_POST['ort_kurzbz'])?$_POST['ort_kurzbz']:'');
$standort_id = (isset($_POST['standort_id'])?$_POST['standort_id']:'');
$anmerkung = (isset($_POST['anmerkung'])?$_POST['anmerkung']:'');
$bismelden = (isset($_POST['bismelden'])?$_POST['bismelden']:'');
$matrikelnummer = (isset($_POST['matrikelnummer'])?$_POST['matrikelnummer']:'');
$semester = (isset($_POST['semester'])?$_POST['semester']:'');
$verband = (isset($_POST['verband'])?$_POST['verband']:'');
$gruppe = (isset($_POST['gruppe'])?$_POST['gruppe']:'');
$dms_id_lichtbild = '';

if($uid!='')
{
	$qry = "SELECT person_id, true as mitarbeiter FROM campus.vw_mitarbeiter WHERE uid=".$db->db_add_param($uid)."
			UNION
			SELECT person_id, false as mitarbeiter FROM campus.vw_student WHERE uid=".$db->db_add_param($uid);

	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			$is_mitarbeiter = ($row->mitarbeiter=='t'?true:false);
			$person_id = $row->person_id;
		}
		else
			die('UID wurde nicht gefunden');
	}
	else
		die('Fehler beim Ermitteln der UID');
}

if(isset($_POST['saveperson']))
{
	if(!$rechte->isBerechtigt('student/stammdaten', null, 'su') && !$rechte->isBerechtigt('mitarbeiter/stammdaten', null, 'su'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	$person = new person();
	if(!$person->load($person_id))
		die('Person konnte nicht geladen werden');

	$person->anrede = $anrede;
	$person->titelpre = $titelpre;
	$person->titelpost = $titelpost;
	$person->nachname = $nachname;
	$person->vorname = $vorname;
	$person->vornamen = $vornamen;
	$person->gebdatum = $geburtsdatum;
	$person->gebort = $geburtsort;
	$person->geburtsnation = $geburtsnation;
	$person->svnr = $svnr;
	$person->ersatzkennzeichen = $ersatzkennzeichen;
	$person->gebzeit = $geburtszeit;
	$person->staatsbuergerschaft = $staatsbuergerschaft;
	$person->sprache = $sprache;
	$person->geschlecht = $geschlecht;
	$person->familienstand = $familienstand;
	$person->anzahlkinder = $anzahlderkinder;
	$person->anmerkungen = $anmerkungen;
	$person->homepage = $homepage;
	$person->updateamum = date('Y-m-d H:i:s');
	$person->updatevon = $user;
	$person->new = false;

	if($person->save())
	{
		$msg = '<h3>Personendaten wurden erfolgreich gespeichert</h3>';
	}
	else
	{
		$msg = "<h3>Fehler beim Speichern der Personendaten: $person->errormsg</h3>";
		$error_person_save=true;
	}

}

if(isset($_GET['deleteimage']))
{
	if(!$rechte->isBerechtigt('student/stammdaten', null, 'su') && !$rechte->isBerechtigt('mitarbeiter/stammdaten', null, 'su'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	$person = new person();
	if(!$person->load($person_id))
		die('Person konnte nicht geladen werden');

	$person->foto='';
	if(!$person->save())
		die('Fehler beim Speichern:'.$person->errormsg);

	$akte = new akte();
	if($akte->getAkten($person_id, 'Lichtbil'))
	{
		foreach ($akte->result as $row)
		{
			$hlp = new akte();
			if(!$hlp->delete($row->akte_id))
				echo 'Fehler beim Löschen des Bildes: '.$hlp->errormsg;
		}
	}
	else
	{
		die('Fehler beim Laden der Akten:'.$akte->errormsg);
	}

	$msg = '<h3>Bild wurde erfolgreich entfernt</h3>';
}
if(isset($_POST['savebenutzer']))
{
	if(!$rechte->isBerechtigt('student/stammdaten', null, 'su') && !$rechte->isBerechtigt('mitarbeiter/stammdaten', null, 'su'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	$benutzer = new benutzer();
	$benutzer->load($uid);

	if(checkalias($alias) || $alias=='')
	{
		$benutzer->alias = $alias;
		$benutzer->bnaktiv = ($aktiv!=''?true:false);
		$benutzer->new = false;
		$benutzer->updateamum = date('Y-m-d H:i:s');
		$benutzer->updatevon = $user;

		if($benutzer->save())
		{
			$msg = '<h3>Daten wurden erfolgreich gespeichert</h3>';
		}
		else
		{
			$msg = "<h3>Fehler beim Speichern: $benutzer->errormsg";
		}
	}
	else
	{
		$msg = "<h3>Alias ist ungueltig $alias</h3>";
		$error_benutzer_save=true;
	}
}

if(isset($_POST['savemitarbeiter']))
{
	if(!$rechte->isBerechtigt('mitarbeiter/stammdaten', null, 'su'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	$mitarbeiter = new mitarbeiter();
	if(!$mitarbeiter->load($uid))
		die('Mitarbeiter konnte nicht geladen werden');

	$mitarbeiter->personalnummer = $personalnummer;
	$mitarbeiter->telefonklappe = $telefonklappe;
	$mitarbeiter->kurzbz = $kurzbz;
	$mitarbeiter->lektor = ($lektor!=''?true:false);
	$mitarbeiter->fixangestellt = ($fixangestellt!=''?true:false);
	$mitarbeiter->stundensatz = $stundensatz;
	$mitarbeiter->ausbildungcode = $ausbildungcode;
	$mitarbeiter->ort_kurzbz = $ort_kurzbz;
	$mitarbeiter->standort_id = $standort_id;
	$mitarbeiter->anmerkung = $anmerkung;
	$mitarbeiter->bismelden = ($bismelden!=''?true:false);
	$mitarbeiter->new = false;
	$mitarbeiter->updateamum = date('Y-m-d H:i:s');
	$mitarbeiter->updatevon = $user;

	if($mitarbeiter->save())
		$msg = '<h3>Daten wurden erfolgreich gespeichert</h3>';
	else
	{
		$msg = "<h3>Fehler beim Speichern der Daten: $mitarbeiter->errormsg</h3>";
		$error_mitarbeiter_save = true;
	}
}

if(isset($_POST['savestudent']))
{
	$student = new student();
	if(!$student->load($uid))
		die('Student konnte nicht geladen werden');

	$studiengang = new studiengang();
	if(!$studiengang->load($student->studiengang_kz))
		die('Fehler beim Laden des Studienganges');

	if(!$rechte->isBerechtigt('student/stammdaten', $studiengang->oe_kurzbz, 'su'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	$student->matrikelnr = $matrikelnummer;
	$student->semester = $semester;
	$student->verband = $verband;
	$student->gruppe = $gruppe;
	$student->updateamum = date('Y-m-d H:i:s');
	$student->updatevon = $user;
	$student->new = false;

	if($student->save(null, false))
		$msg = '<h3>Daten wurden erfolgreich gespeichert</h3>';
	else
	{
		$msg = "<h3>Fehler beim Speichern der Daten: $student->errormsg</h3>";
		$error_student_save = true;
	}
}

$person = new person();
if(!$person->load($person_id))
	die('Person wurde nicht gefunden');

echo "<h2>Details von $person->vorname $person->nachname</h2>";
echo $msg;

if(!$error_person_save)
{
	$anrede = $person->anrede;
	$titelpre = $person->titelpre;
	$titelpost = $person->titelpost;
	$nachname = $person->nachname;
	$vorname = $person->vorname;
	$vornamen = $person->vornamen;
	$geburtsdatum = $person->gebdatum;
	$geburtsort = $person->gebort;
	$geburtsnation = $person->geburtsnation;
	$svnr = $person->svnr;
	$ersatzkennzeichen = $person->ersatzkennzeichen;
	$geburtszeit = $person->gebzeit;
	$staatsbuergerschaft = $person->staatsbuergerschaft;
	$sprache = $person->sprache;
	$geschlecht = $person->geschlecht;
	$familienstand = $person->familienstand;
	$anzahlderkinder = $person->anzahlkinder;
	$anmerkungen = $person->anmerkungen;
	$homepage = $person->homepage;
}

$akte = new akte();
if($akte->getAkten($person_id, 'Lichtbil'))
{
	if(isset($akte->result[0]))
	{
		$dms_id_lichtbild = $akte->result[0]->dms_id;
	}
}

// PERSON
echo "<table><tr><td>
<fieldset>
<legend>Person</legend>
<form accept-charset='UTF-8' accept-charset='UTF-8' action='".$_SERVER['PHP_SELF']."?uid=$uid&person_id=$person_id' method='POST'>
<table>
<tr>
	<td>Anrede</td>
	<td><input type='text' name='anrede' value='".$anrede."'/></td>
	<td>Titelpre</td>
	<td><input type='text' name='titelpre' value='".$titelpre."'/></td>
	<td>Titelpost</td>
	<td><input type='text' name='titelpost' value='".$titelpost."'/></td>
</tr>
<tr>
	<td>Nachname</td>
	<td><input type='text' name='nachname' value='".$nachname."'/></td>
	<td>Vorname</td>
	<td><input type='text' name='vorname' value='".$vorname."'/></td>
	<td>Vornamen</td>
	<td><input type='text' name='vornamen' value='".$vornamen."'/></td>
</tr>
<tr>
	<td>Geburtsdatum</td>
	<td><input type='text' name='geburtsdatum' value='".$geburtsdatum."'/></td>
	<td>Geburtsort</td>
	<td><input type='text' name='geburtsort' value='".$geburtsort."'/></td>
	<td>Geburtsnation</td>
	<td><SELECT name='geburtsnation'>
			<option value=''>-- keine Auswahl --</option>";
$nation = new nation();
$nation->getAll();

foreach ($nation->nation as $row_nation)
{
	if($row_nation->code == $geburtsnation)
		$selected = 'selected';
	else
		$selected = '';

	echo "<option value='$row_nation->code' $selected>$row_nation->kurztext</option>";
}
echo "</SELECT>
	</td>
</tr>
<tr>
	<td>SVNR</td>
	<td><input type='text' name='svnr' value='".$svnr."'/></td>
	<td>Ersatzkennzeichen</td>
	<td><input type='text' name='ersatzkennzeichen' value='".$ersatzkennzeichen."'/></td>
	<td>Geburtszeit</td>
	<td><input type='text' name='geburtszeit' value='".$geburtszeit."'/></td>
</tr>
<tr>
	<td>Staatsbuergerschaft</td>
	<td><SELECT name='staatsbuergerschaft'><option value=''>-- keine Auswahl --</option>";
$nation = new nation();
$nation->getAll();

foreach ($nation->nation as $row_nation)
{
	if($row_nation->code == $staatsbuergerschaft)
		$selected = 'selected';
	else
		$selected = '';

	echo "<option value='$row_nation->code' $selected>$row_nation->kurztext</option>";
}
echo "
	</SELECT>
	</td>
	<td>Sprache</td>
	<td><SELECT name='sprache'><option value=''>-- keine Auswahl --</option>";

$qry = "SELECT * FROM public.tbl_sprache ORDER BY sprache";

if($result_sprache = $db->db_query($qry))
{
	while($row_sprache = $db->db_fetch_object($result_sprache))
	{
		if($row_sprache->sprache == $sprache)
			$selected = 'selected';
		else
			$selected = '';

		echo "<option value='$row_sprache->sprache' $selected>$row_sprache->sprache</option>";
	}
}
echo "
	</SELECT>
	</td>
	<td valign='top'>Homepage</td>
	<td valign='top'><input type='text' name='homepage' value='".$homepage."'/></td>
</tr>
<tr>
	<td>Geschlecht</td>
	<td><SELECT name='geschlecht'>";
$geschlecht_obj = new geschlecht();
$geschlecht_obj->getAll();
foreach ($geschlecht_obj->result as $row_geschlecht)
{
	if($geschlecht == $row_geschlecht->geschlecht)
		$selected = 'selected';
	else
		$selected = '';

	echo "<option value='".$row_geschlecht->geschlecht."' ".$selected.">".$row_geschlecht->bezeichnung_mehrsprachig_arr[DEFAULT_LANGUAGE]."</option>";
}
echo "		</SELECT>
	</td>
	<td>Familienstand</td>
	<td><SELECT name='familienstand'>
			<option value='' >-- keine Auswahl --</option>
			<option value='g' ".($familienstand=='g'?'selected':'').">geschieden</option>
			<option value='l' ".($familienstand=='l'?'selected':'').">ledig</option>
			<option value='v' ".($familienstand=='v'?'selected':'').">verheiratet</option>
			<option value='w' ".($familienstand=='w'?'selected':'').">verwittwet</option>
		</SELECT>
	</td>
	<td>Anzahl der Kinder</td>
	<td><input type='text' name='anzahlderkinder' value='".$anzahlderkinder."' /></td>
</tr>
<tr>
	<td valign='top'>Anmerkungen</td>
	<td valign='top'><textarea name='anmerkungen'>".$anmerkungen."</textarea></td>
	<td></td>
	<td><a href='".APP_ROOT."cms/dms.php?id=".$dms_id_lichtbild."' target='_blank'><img id='personimage' src='../../content/bild.php?src=person&person_id=$person_id' height='100'></a></td>
	<td colspan='2'>
		<a href='#foo' onclick='window.open(\"../../content/bildupload.php?person_id=$person_id\",\"BildUpload\", \"height=50,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes\"); return false;'>Bild hochladen</a>
		<br><br>
		<a href='#foo' onclick='RefreshImage(); return false;'>Bild aktualisieren</a>
		<br><br>
		<a href='".$_SERVER['PHP_SELF']."?person_id=$person_id&uid=$uid&deleteimage=true'>Bild entfernen</a>
		<br><br>
		<a href=\"../fhausweis/search.php?person_id=".$person_id."\">FH-Ausweis Status</a>
	</td>
</tr>
<tr>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td valign='bottom' align='right'><input type='submit' name='saveperson' value='Speichern'></td>
</tr>
</table>
</form>
</fieldset>
</td>
</tr>
";

if(isset($uid) && $uid!='')
{
	//Benutzerdaten
	echo "<tr><td>
	<fieldset>
	<legend>Benutzerdaten</legend>
	";

	$qry = "SELECT * FROM public.tbl_benutzer WHERE uid=".$db->db_add_param($uid);
	if(!$result_benutzer = $db->db_query($qry))
		die('Fehler beim Auslesen der Benutzerdaten');

	if(!$row_benutzer = $db->db_fetch_object($result_benutzer))
		die('Fehler beim Auslesen der Benutzerdaten');

	echo "
	<form action='".$_SERVER['PHP_SELF']."?person_id=$person_id&uid=$uid' method='POST'>
	<table>
	<tr>
		<td style='padding-right: 15px'>Aktiv</td>
		<td style='padding-right: 15px'><input type='checkbox' name='aktiv' ".($row_benutzer->aktiv=='t'?'checked':'')."></td>
		<td style='padding-right: 15px'>Alias</td>
		<td style='padding-right: 15px'><input type='text' name='alias' value='".$row_benutzer->alias."'></td>
		<td style='padding-right: 15px'><input type='submit' name='savebenutzer' value='Speichern'></td>
	</tr>
	</table>
	</form>";


	echo '<br><a href="../../content/pdfExport.php?xsl=AccountInfo&xml=accountinfoblatt.xml.php&uid='.$uid.'" >AccountInfoBlatt erstellen</a>';
	echo '<br><a href="../stammdaten/betriebsmittel_frameset.php?searchstr='.$uid.'" >Betriebsmittel verwalten</a>';

	echo "</fieldset></td></tr>";

	if($is_mitarbeiter)
	{
		$mitarbeiter = new mitarbeiter();
		if(!$mitarbeiter->load($uid))
			die('Mitarbeiter konnte nicht geladen werden');

		if(!$error_mitarbeiter_save)
		{
			$personalnummer = $mitarbeiter->personalnummer;
			$telefonklappe = $mitarbeiter->telefonklappe;
			$kurzbz = $mitarbeiter->kurzbz;
			$lektor = $mitarbeiter->lektor;
			$fixangestellt = $mitarbeiter->fixangestellt;
			$stundensatz = $mitarbeiter->stundensatz;
			$ausbildungcode = $mitarbeiter->ausbildungcode;
			$ort_kurzbz = $mitarbeiter->ort_kurzbz;
			$standort_id = $mitarbeiter->standort_id;
			$anmerkung = $mitarbeiter->anmerkung;
			$bismelden = $mitarbeiter->bismelden;
		}

		//MITARBEITER
		echo "<tr><td>
			<fieldset>
			<legend>Mitarbeiterdaten</legend>
			<form method='POST'>
			<table>
			<tr>
				<td>Personalnummer</td>
				<td><input type='text' name='personalnummer' value='".$personalnummer."'></td>
				<td>Kurzbezeichnung</td>
				<td><input type='text' name='kurzbz' value='".$kurzbz."'></td>
				<td>Lektor</td>
				<td><input type='checkbox' name='lektor' ".(($lektor || $lektor!='')?'checked':'')."></td>
			</tr>
			<tr>
				<td>Stundensatz</td>
				<td><input type='text' name='stundensatz' value='".$stundensatz."'></td>
				<td>Telefonklappe</td>
				<td><input type='text' name='telefonklappe' value='".$telefonklappe."'></td>
				<td>Fixangestellt</td>
				<td><input type='checkbox' name='fixangestellt' ".(($fixangestellt || $fixangestellt!='')?'checked':'')."></td>
			</tr>
			<tr>
				<td>Buero</td>
				<td><SELECT name='ort_kurzbz'><option value=''>-- keine Auswahl --</option>";

		$ort = new ort();
		$ort->getAll();
		foreach ($ort->result as $row_ort)
		{
			if($row_ort->ort_kurzbz==$ort_kurzbz)
				$selected = 'selected';
			else
				$selected = '';

			echo "<option value='$row_ort->ort_kurzbz' $selected>$row_ort->ort_kurzbz</option>";
		}

		echo "</SELECT></td>
				<td>Standort</td>
				<td><SELECT name='standort_id'><option value=''>-- keine Auswahl --</option>";
		$qry = "SELECT * FROM public.tbl_standort JOIN public.tbl_firma USING(firma_id) WHERE tbl_firma.firmentyp_kurzbz='Intern' ORDER BY tbl_standort.kurzbz";
		if($result_standort = $db->db_query($qry))
		{
			while($row_standort = $db->db_fetch_object($result_standort))
			{
				if($row_standort->standort_id == $standort_id)
					$selected = 'selected';
				else
					$selected = '';

				echo "<option value='$row_standort->standort_id' $selected>$row_standort->kurzbz</option>";
			}
		}

		echo "
				</SELECT></td>
				<td>Bismelden</td>
				<td><input type='checkbox' name='bismelden' ".(($bismelden || $bismelden!='')?'checked':'')."></td>
			</tr>
			<tr>
				<td valign='top'>Anmerkungen</td>
				<td><textarea name='anmerkung'>".$anmerkung."</textarea></td>
				<td valign='top'>Ausbildung</td>
				<td valign='top'><SELECT name='ausbildungcode'><option value=''>-- keine Auswahl --</option>";
		$qry = "SELECT * FROM bis.tbl_ausbildung ORDER BY ausbildungcode";
		if($result_ausbildung = $db->db_query($qry))
		{
			while($row_ausbildung = $db->db_fetch_object($result_ausbildung))
			{
				if($row_ausbildung->ausbildungcode == $ausbildungcode)
					$selected = 'selected';
				else
					$selected = '';

				echo "<option value='$row_ausbildung->ausbildungcode' $selected>$row_ausbildung->ausbildungbez</option>";
			}
		}
		echo "</SELECT></td>
				<td></td>
				<td valign='bottom'><input type='submit' name='savemitarbeiter' value='Speichern'></td>
			</tr>
			</table>
			</form>
			</fieldset>
			</td></tr>
			";
	}
	else
	{
		$student = new student();
		if(!$student->load($uid))
			die('Fehler beim Laden des Studenten');

		if(!$error_student_save)
		{
			$semester = $student->semester;
			$verband = $student->verband;
			$gruppe = $student->gruppe;
			$matrikelnummer = $student->matrikelnr;
		}

		//STUDENT
		echo "<tr><td>
			<fieldset>
			<legend>Studentendaten</legend>
			<form method='POST'>
			<table>
			<tr>
				<td>Semester</td>
				<td><input type='text' size='3' name='semester' value='".$semester."'></td>
				<td>Verband</td>
				<td><input type='text' size='3' name='verband' value='".$verband."'></td>
				<td>Gruppe</td>
				<td><input type='text' size='3' name='gruppe' value='".$gruppe."'></td>
			</tr>
			<tr>
				<td>Matrikelnummer</td>
				<td colspan='3'><input type='text' name='matrikelnummer' value='".$matrikelnummer."'></td>
				<td></td>
				<td></td>
				<td><input type='submit' value='Speichern' name='savestudent'></td>
			</tr>

			</table>
			</form>
			</fieldset>
			</td></tr>
			";
	}
}
echo "</table>";
?>

</body>
</html>
