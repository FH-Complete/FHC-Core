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
 *		  Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *		  Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *		  Andreas Moik <moik@technikum-wien.at>.
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../../../include/'.EXT_FKT_PATH.'/generateuid.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/adresse.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/student.class.php');
require_once('../../../include/lehrverband.class.php');
require_once('../../../include/nation.class.php');
require_once('../../../include/studienplan.class.php');
require_once('../../../include/geschlecht.class.php');

$db = new basis_db();
$user = get_uid();
$datum_obj = new datum();
loadVariables($user);

function getGemeindeDropDown($postleitzahl)
{
	global $_REQUEST, $gemeinde;
	$db = new basis_db();

	$found = false;
	$firstentry = '';
	$gemeinde_x = (isset($_REQUEST['gemeinde'])?$_REQUEST['gemeinde']:'');

	echo '<SELECT id="gemeinde" name="gemeinde" onchange="loadOrtData()">';
	if (is_numeric($postleitzahl) && $postleitzahl < 10000)
	{
		$qry = "SELECT distinct name FROM bis.tbl_gemeinde WHERE plz = ".$db->db_add_param($postleitzahl);

		if ($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				if ($firstentry == '')
					$firstentry = $row->name;
				if ($gemeinde_x == '')
					$gemeinde_x = $row->name;

				if ($row->name == $gemeinde_x)
				{
					$selected = 'selected';
					$found = true;
				}
				else
					$selected = '';
				echo "<option value='$row->name' $selected>$row->name</option>";
			}
		}
	}

	echo '</SELECT>';
	if (!$found && (isset($importort) && $importort != ''))
	{
		echo $importort;
	}
	$gemeinde = $gemeinde_x;
}

if (isset($_GET['type']) && $_GET['type'] == 'getgemeindecontent' && isset($_GET['plz']))
{
	header('Content-Type: text/html; charset=UTF-8');

	echo getGemeindeDropDown($_GET['plz']);
	exit;
}

function getOrtDropDown($postleitzahl, $gemeindename)
{
	global $_REQUEST;
	$db = new basis_db();

	echo '<SELECT id="ort" name="ort">';

	if (is_numeric($postleitzahl) && $postleitzahl < 10000)
	{
		$ort = (isset($_REQUEST['ort'])?$_REQUEST['ort']:'');
		$qry = "
		SELECT
			distinct ortschaftsname
		FROM
			bis.tbl_gemeinde
		WHERE
			plz = ".$db->db_add_param($postleitzahl)."
			AND name = ".$db->db_add_param($gemeindename);

		if ($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				if ($row->ortschaftsname == $ort)
					$selected = 'selected';
				else
					$selected = '';
				echo "<option value='$row->ortschaftsname' $selected>$row->ortschaftsname</option>";
			}
		}
	}
	echo '</SELECT>';
}
if (isset($_GET['type']) && $_GET['type'] == 'getortcontent' && isset($_GET['plz']) && isset($_GET['gemeinde']))
{
	header('Content-Type: text/html; charset=UTF-8');

	echo getOrtDropDown($_GET['plz'], $_GET['gemeinde']);
	exit;
}

function getStudienplanDropDown($studiengang_kz, $orgform_kurzbz = '', $studienplan_id = '', $studiensemester_kurzbz = '', $ausbildungssemester = '')
{
	$db = new basis_db();

	$content = '<SELECT id="studienplan_id" name="studienplan_id">
	<OPTION value="">-- keine Auswahl --</OPTION>';
	$studienplan = new studienplan();
	//$studienplan->getStudienplaene($studiengang_kz);
	$studienplan->getStudienplaeneFromSem($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester, $orgform_kurzbz);

	foreach($studienplan->result as $row)
	{
		if ($studienplan_id == '')
			$studienplan_id = $row->studienplan_id;

		if ($studienplan_id == $row->studienplan_id)
			$selected = 'selected';
		else
			$selected = '';

		if ($row->aktiv)
		{
			if ($orgform_kurzbz == '' || $row->orgform_kurzbz == '' || $row->orgform_kurzbz == $orgform_kurzbz)
				$content .= "<option value='$row->studienplan_id' $selected>$row->bezeichnung_studienplan</option>";
		}
	}

	$content .= '</SELECT>';
	return $content;
}

if (isset($_GET['type']) && $_GET['type'] == 'getstudienplancontent' && isset($_GET['studiengang_kz']) && isset($_GET['orgform_kurzbz']))
{
	header('Content-Type: text/html; charset=UTF-8');

	echo getStudienplanDropDown($_GET['studiengang_kz'], $_GET['orgform_kurzbz'], '', $_GET['studiensemester'],$_GET['ausbildungssemester']);
	exit;
}
?><!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/styles/tw.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
	<script type="text/javascript" src="../../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css">
	<script type="text/Javascript">

	$(document).ready(function()
	{
		$('#t1').tablesorter(
		{
			sortList: [[1,0],[2,0],[4,0]],
			widgets: ['zebra'],
			headers: {0: {sorter: false},8: {sorter: false},9: {sorter: false}}
		});
	});

	function disablefields(obj)
	{
		if (obj.value == 0)
			val = false;
		else
			val = true;

		document.getElementById('anrede').disabled = val;
		document.getElementById('titel').disabled = val;
		document.getElementById('titelpost').disabled = val;
		document.getElementById('nachname').disabled = val;
		document.getElementById('vorname').disabled = val;
		document.getElementById('vornamen').disabled = val;
		document.getElementById('wahlname').disabled = val;
		document.getElementById('geschlecht').disabled = val;
		document.getElementById('geburtsdatum').disabled = val;

		if (val)
		{
			document.getElementById('ueb1').style.display = 'block';
			document.getElementById('ueb2').style.display = 'block';
			document.getElementById('ueb3').style.display = 'block';
			document.getElementById('ueberschreiben3').checked = true;
		}
		else
		{
			document.getElementById('ueb1').style.display = 'none';
			document.getElementById('ueb2').style.display = 'none';
			document.getElementById('ueb3').style.display = 'none';
			document.getElementById('ueberschreiben1').checked = true;
		}
	}

	function disablefields2(val)
	{
		document.getElementById('adresse').disabled = val;
		document.getElementById('plz').disabled = val;
		document.getElementById('ort').disabled = val;
	}

	function AnredeChange()
	{
		anrede = document.getElementById('anrede').value;

		if (anrede == 'Herr')
			document.getElementById('geschlecht').value = 'm';
		if (anrede == 'Frau')
			document.getElementById('geschlecht').value = 'w';
	}

	function cmdIncoming()
	{
		document.getElementById('ausbildungssemester').disabled = document.getElementById('incoming').checked;
	}

	function checkVorschlag()
	{
		var elems = document.getElementsByName('person_id');

		for(i = 0;i <= elems.length;i++)
		{
			try
			{
				if (elems[i].checked)
					return true;
			}
			catch(e)
			{}
		}

		alert('Bitte wählen Sie einen der Vorschläge aus');
		return false;
	}

	function checkInput1()
	{
		if (document.getElementById('nachname').value == '')
		{
			alert('Nachname muss eingetragen werden');
			return false;
		}
		return true;
	}

	//Gemeinde DropDown holen wenn Nation Oesterreich
	function loadGemeindeData()
	{
		if (document.getElementById('adresse_nation').value == 'A')
		{
			var plz = document.getElementById('plz').value;
			var url= '<?php echo $_SERVER['PHP_SELF']."?type=getgemeindecontent"?>';
			url += '&plz='+plz;

			$('#adresse-gemeinde-textfeld').attr('type','hidden');
			$('#adresse-ort-textfeld').attr('type','hidden');

			$.ajax({
				url: url,
				cache: false
			}).done(function( html ) {
				$( "#gemeindediv" ).html( html );
				loadOrtData();
			});
		}
		else
		{
			$('#adresse-gemeinde-textfeld').attr('type','text');
			$('#adresse-ort-textfeld').attr('type','text');
			$('#gemeindediv').html('');
			$('#ortdiv').html('');
		}
	}

	function loadOrtData()
	{
		if (document.getElementById('gemeinde'))
		{
			var plz = document.getElementById('plz').value;
			var gemeinde = document.getElementById('gemeinde').value;
			var url= '<?php echo $_SERVER['PHP_SELF']."?type=getortcontent"?>';
			url += '&plz='+plz+"&gemeinde="+encodeURIComponent(gemeinde);

			$.ajax({
				url: url,
				cache: false
			}).done(function( html ) {
				$( "#ortdiv" ).html(html);
			});
		}
	}

	function loadStudienplanData()
	{
		var studiengang_kz = document.getElementById('studiengang_kz').value;
		var orgform_kurzbz = document.getElementById('orgform_kurzbz').value;
		var ausbildungssemester = document.getElementById('ausbildungssemester').value;
		var studiensemester = document.getElementById('studiensemester_kurzbz').value;
		var url= '<?php echo $_SERVER['PHP_SELF']."?type=getstudienplancontent"?>';
		url += '&studiengang_kz='+encodeURIComponent(studiengang_kz)+"&orgform_kurzbz="+encodeURIComponent(orgform_kurzbz);
		url += '&ausbildungssemester='+encodeURIComponent(ausbildungssemester)+"&studiensemester="+encodeURIComponent(studiensemester);

		$.ajax({
			url: url,
			cache: false
		}).done(function( html ) {
			$( "#studienplandiv" ).html(html);
		});
	}

	function changeGebnation()
	{
		var nation = document.getElementById('adresse_nation').value;
		document.getElementById('geburtsnation').value = nation;
		document.getElementById('staatsbuergerschaft').value = nation;
	}
	</script>
</head>
<body>
<h1>InteressentIn anlegen</h1>
<?php
//Berechtigung pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if (!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$where = '';
$error = false;
$importort='';
//Parameter
$titel = (isset($_REQUEST['titel'])?$_REQUEST['titel']:'');
$titelpost = (isset($_REQUEST['titelpost'])?$_REQUEST['titelpost']:'');
$anrede = (isset($_REQUEST['anrede'])?$_REQUEST['anrede']:'');
$nachname = (isset($_REQUEST['nachname'])?$_REQUEST['nachname']:'');
$vorname = (isset($_REQUEST['vorname'])?$_REQUEST['vorname']:'');
$vornamen = (isset($_REQUEST['vornamen'])?$_REQUEST['vornamen']:'');
$wahlname = (isset($_REQUEST['wahlname'])?$_REQUEST['wahlname']:'');
$geschlecht = (isset($_REQUEST['geschlecht'])?$_REQUEST['geschlecht']:'');
$geburtsdatum = (isset($_REQUEST['geburtsdatum'])?$_REQUEST['geburtsdatum']:'');
$adresse = (isset($_REQUEST['adresse'])?$_REQUEST['adresse']:'');
$adresse_nation = (isset($_REQUEST['adresse_nation'])?$_REQUEST['adresse_nation']:'A');
$plz = (isset($_REQUEST['plz'])?$_REQUEST['plz']:'');

//Wenn die Daten aus dem Mail Importiert werden, sind diese LATIN9 konvertiert
//und muessen zuerst nach UTF8 konvertiert werden
function utf8($string)
{
	if (!check_utf8($string))
		return utf8_encode($string);
	else
		return $string;
}

$titel = utf8($titel);
$titelpost = utf8($titelpost);
$anrede = utf8($anrede);
$nachname = utf8($nachname);
$vorname = utf8($vorname);
$vornamen = utf8($vornamen);
$wahlname = utf8($wahlname);
$geschlecht = utf8($geschlecht);
$geburtsdatum = utf8($geburtsdatum);
$adresse = utf8($adresse);
$adresse_nation = utf8($adresse_nation);
$plz = utf8($plz);

if ($adresse_nation=='A')
{
	$ort = (isset($_REQUEST['ort'])?$_REQUEST['ort']:'');
	$gemeinde = (isset($_REQUEST['gemeinde'])?$_REQUEST['gemeinde']:'');
}
else
{
	$ort = (isset($_REQUEST['ort_txt'])?$_REQUEST['ort_txt']:'');
	$gemeinde = (isset($_REQUEST['gemeinde_txt'])?$_REQUEST['gemeinde_txt']:'');
}

$gemeinde = utf8($gemeinde);
$ort = utf8($ort);
//wenn die Gemeinde leer ist und im Ort etwas steht
//dann umdrehen (Das passiert wenn die Daten aus dem Mail von der www importiert werden)
if ($gemeinde == '' && $ort != '')
{
	$importort = $ort;
	$gemeinde = $ort;
	$ort = '';
}
$email = (isset($_REQUEST['email'])?$_REQUEST['email']:'');
$geburtsnation = (isset($_REQUEST['geburtsnation'])?$_REQUEST['geburtsnation']:'A');
$staatsbuergerschaft = (isset($_REQUEST['staatsbuergerschaft'])?$_REQUEST['staatsbuergerschaft']:'A');
$telefon = (isset($_REQUEST['telefon'])?$_REQUEST['telefon']:'');
$mobil = (isset($_REQUEST['mobil'])?$_REQUEST['mobil']:'');
$letzteausbildung = (isset($_REQUEST['letzteausbildung'])?$_REQUEST['letzteausbildung']:'');
$ausbildungsart = (isset($_REQUEST['ausbildungsart'])?$_REQUEST['ausbildungsart']:'');
$anmerkungen = (isset($_REQUEST['anmerkungen'])?$_REQUEST['anmerkungen']:'');
$studiengang_kz = (isset($_REQUEST['studiengang_kz'])?$_REQUEST['studiengang_kz']:'');
if ($studiengang_kz == '' && isset($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
if ($studiengang_kz == 'undefined')
	$studiengang_kz = '';

$person_id = (isset($_REQUEST['person_id'])?$_REQUEST['person_id']:'');
$ueberschreiben = (isset($_REQUEST['ueberschreiben'])?$_REQUEST['ueberschreiben']:'');
$studiensemester_kurzbz = (isset($_REQUEST['studiensemester_kurzbz'])?$_REQUEST['studiensemester_kurzbz']:'');
$ausbildungssemester = (isset($_REQUEST['ausbildungssemester'])?$_REQUEST['ausbildungssemester']:'0');
$incoming = (isset($_REQUEST['incoming'])?true:false);
$orgform_kurzbz = (isset($_REQUEST['orgform_kurzbz'])?$_REQUEST['orgform_kurzbz']:'');
$studienplan_id = (isset($_REQUEST['studienplan_id'])?$_REQUEST['studienplan_id']:'');
//end Parameter
$geburtsdatum_error = false;

$ausbildungsart = utf8($ausbildungsart);
$anmerkungen = utf8($anmerkungen);

// ****
// * Generiert die Matrikelnummer
// * FORMAT: 0710254001
// * 07 = Jahr
// * 1/2/0  = WS/SS/incoming
// * 0254 = Studiengangskennzahl vierstellig
// * 001 = Laufende Nummer
// ****
function generateMatrikelnummer($studiengang_kz, $studiensemester_kurzbz)
{
	$db = new basis_db();

	$jahr = mb_substr($studiensemester_kurzbz, 4);
	$sem = mb_substr($studiensemester_kurzbz, 0, 2);
	if ($sem == 'SS')
		$jahr = $jahr-1;
	$art = 0;

	$matrikelnummer = sprintf("%02d",$jahr).$art.sprintf("%04d",$studiengang_kz);

	$qry = "SELECT matrikelnr FROM public.tbl_student WHERE matrikelnr LIKE '$matrikelnummer%' ORDER BY matrikelnr DESC LIMIT 1";

	if ($db->db_query($qry))
	{
		if ($row = $db->db_fetch_object())
		{
			$max = mb_substr($row->matrikelnr,7);
		}
		else
			$max = 0;

		$max += 1;
		return $matrikelnummer.sprintf("%03d",$max);
	}
	else
	{
		return false;
	}
}


if ($studiensemester_kurzbz == '')
{
	//Im September wird das Aktuelle Studiensemester vorgeschlagen sonst immer das naechste WS
	/*$stsem = new studiensemester();
	if (date('m')=='9')
		$studiensemester_kurzbz = $stsem->getaktorNext();
	else
	{
		$stsem->getNextStudiensemester('WS');
		$studiensemester_kurzbz = $stsem->studiensemester_kurzbz;
	}*/

	$stsem = new studiensemester();
	if (defined('VILESCI_PERSON_NEU_STUDIENSEMESTER_UEBERGANGSFRIST') && VILESCI_PERSON_NEU_STUDIENSEMESTER_UEBERGANGSFRIST>0)
	{
		$studiensemester_kurzbz = $stsem->getNextOrAktSemester(VILESCI_PERSON_NEU_STUDIENSEMESTER_UEBERGANGSFRIST);

		if (defined('VILESCI_PERSON_NEU_STUDIENSEMESTER_WINTERONLY')
		   && VILESCI_PERSON_NEU_STUDIENSEMESTER_WINTERONLY
		   && mb_substr($studiensemester_kurzbz,0,2) == 'SS')
		{
			$studiensemester_kurzbz = $stsem->getNextFrom($studiensemester_kurzbz);
		}
	}
	else
	{
		$studiensemester_kurzbz = $stsem->getaktorNext();
	}

}

// *** Speichern der Daten ***
if (isset($_POST['save']))
{
	$person = new person();
	$prestudent = new prestudent();

	$db->db_query('BEGIN');
	//Wenn die person_id=0 dann wird eine neue Person angelegt
	//Ansosnsten wird es an die Person mit $person_id angehaengt
	if ($person_id != '0')
	{
		if (!$person->load($person_id))
		{
			$error = true;
			$errormsg = 'Person konnte nicht geladen werden';
		}
		else
		{
			$geburtsdatum = $person->gebdatum;
			$vorname = $person->vorname;
			$vornamen = $person->vornamen;
			$wahlname = $person->wahlname;
			$nachname = $person->nachname;
			$titel = $person->titelpre;
			$titelpost = $person->titelpost;
			$geschlecht = $person->geschlecht;
			$anrede = $person->anrede;
		}
	}
	else
	{
		$person->new = true;
		$person->anrede = $anrede;
		$person->titelpre = $titel;
		$person->titelpost = $titelpost;
		$person->nachname = $nachname;
		$person->vorname = $vorname;
		$person->vornamen = $vornamen;
		$person->wahlname = $wahlname;
		$person->geschlecht = $geschlecht;
		$person->gebdatum = $datum_obj->formatDatum($geburtsdatum,'Y-m-d');
		$person->geburtsnation = $geburtsnation;
		$person->staatsbuergerschaft = $staatsbuergerschaft;
		$person->aktiv = true;
		$person->insertamum = date('Y-m-d H:i:s');
		$person->insertvon = $user;
		$person->zugangscode= uniqid();
		if ($person->save())
		{
			$error = false;
		}
		else
		{
			$error = true;
			$errormsg = "Person konnte nicht gespeichert werden: $person->errormsg";
		}
	}

	//Adresse anlegen
	if ($ueberschreiben != '' && !($plz == '' && $adresse == '' && $ort == ''))
	{
		if ($person_id == '0')
			$ueberschreiben = 'Nein';

		$adr = new adresse();
		//Adresse neu anlegen
		if ($ueberschreiben == 'Nein')
		{
			$adr->new = true;
			$adr->insertamum = date('Y-m-d H:i:s');
			$adr->insertvon = $user;
			$adr->nation = $adresse_nation;
			//Wenn die Person neu angelegt wird, dann ist die neue Adresse die Heimatadresse
			//sonst nicht
			if ($person_id == '0')
				$adr->heimatadresse = true;
			else
				$adr->heimatadresse = false;
		}
		else
		{
			//Bestehende Adresse Ueberschreiben

			//Adressen der Person laden
			$adr->load_pers($person->person_id);
			if (isset($adr->result[0]))
			{
				//Erste Adresse laden
				if ($adr->load($adr->result[0]->adresse_id))
				{
					$adr->new = false;
					$adr->updateamum = date('Y-m-d H:i:s');
					$adr->updatevon = $user;
				}
				else
				{
					$error = true;
					$errormsg = 'Fehler beim Laden der Adresse';
				}
			}
			else
			{
				//Wenn keine Adrese vorhanden ist dann eine neue Anlegen
				$adr->new = true;
				$adr->insertamum = date('Y-m-d H:i:s');
				$adr->insertvon = $user;
				$adr->nation = $adresse_nation;
				$adr->heimatadresse = true;
			}
		}

		if (!$error)
		{
			//Adressdaten zuweisen und speichern
			$adr->person_id = $person->person_id;
			$adr->strasse = $adresse;
			$adr->plz = $plz;
			$adr->ort = $ort;
			$adr->gemeinde = $gemeinde;
			$adr->typ = 'h';
			$adr->zustelladresse = true;
			if (!$adr->save())
			{
				$error = true;
				$errormsg = $adr->errormsg;
			}
		}
	}

	//Kontaktdaten anlegen
	if (!$error)
	{
		//EMail Adresse speichern
		if ($email != '')
		{
			$kontakt = new kontakt();
			$kontakt->person_id = $person->person_id;
			$kontakt->kontakttyp = 'email';
			$kontakt->kontakt = $email;
			$kontakt->zustellung = true;
			$kontakt->insertamum = date('Y-m-d H:i:s');
			$kontakt->insertvon = $user;
			$kontakt->new = true;

			if (!$kontakt->save())
			{
				$error = true;
				$errormsg = 'Fehler beim Speichern der Email Adresse';
			}
		}
		//Telefonnummer speichern
		if ($telefon != '')
		{
			$kontakt = new kontakt();
			$kontakt->person_id = $person->person_id;
			$kontakt->kontakttyp = 'telefon';
			$kontakt->kontakt = $telefon;
			$kontakt->zustellung = true;
			$kontakt->insertamum = date('Y-m-d H:i:s');
			$kontakt->insertvon = $user;
			$kontakt->new = true;

			if (!$kontakt->save())
			{
				$error = true;
				$errormsg = 'Fehler beim Speichern der Telefonnummer';
			}
		}
		//Mobiltelefonnummer speichern
		if ($mobil != '')
		{
			$kontakt = new kontakt();
			$kontakt->person_id = $person->person_id;
			$kontakt->kontakttyp = 'mobil';
			$kontakt->kontakt = $mobil;
			$kontakt->zustellung = true;
			$kontakt->insertamum = date('Y-m-d H:i:s');
			$kontakt->insertvon = $user;
			$kontakt->new = true;

			if (!$kontakt->save())
			{
				$error = true;
				$errormsg = 'Fehler beim Speichern der Mobiltelefonnummer';
			}
		}
	}

	//Prestudent Anlegen
	if (!$error)
	{
		$prestudent->new = true;
		$prestudent->aufmerksamdurch_kurzbz = 'k.A.';
		$prestudent->person_id = $person->person_id;
		$prestudent->studiengang_kz = $studiengang_kz;
		$prestudent->ausbildungcode = $letzteausbildung;
		$prestudent->anmerkung = $anmerkungen .($ausbildungsart != ''?' Ausbildungsart:'.$ausbildungsart:'');
		$prestudent->reihungstestangetreten = false;
		$prestudent->bismelden = true;

		// Incomings und ausserordentliche sind bei Meldung nicht förderrelevant
		if ($incoming === true || substr($studiengang_kz, 0, 1) == '9')
			$prestudent->foerderrelevant = false;

		//Wenn die Person schon im System erfasst ist, dann die ZGV des Datensatzes uebernehmen
		$qry_zgv = "
			SELECT
				*
			FROM
				public.tbl_prestudent
			WHERE
				person_id = ".$db->db_add_param($person->person_id, FHC_INTEGER)."
				AND zgv_code is not null
			ORDER BY
				zgvmas_code, zgv_code DESC
			LIMIT 1";

		if ($result_zgv = $db->db_query($qry_zgv))
		{
			if ($row_zgv = $db->db_fetch_object($result_zgv))
			{
				if ($row_zgv->zgv_code != '')
				{
					$prestudent->zgv_code = $row_zgv->zgv_code;
					$prestudent->zgvort = $row_zgv->zgvort;
					$prestudent->zgvdatum = $row_zgv->zgvdatum;

					$prestudent->zgvmas_code = $row_zgv->zgvmas_code;
					$prestudent->zgvmaort = $row_zgv->zgvmaort;
					$prestudent->zgvmadatum = $row_zgv->zgvmadatum;
				}
			}
		}

		if (!$prestudent->save())
		{
			$error = true;
			$errormsg = $prestudent->errormsg;
		}
	}

	if (!$error)
	{
		//Prestudent Rolle Anlegen
		$rolle = new prestudent();

		$rolle->prestudent_id = $prestudent->prestudent_id;
		if (!$incoming)
			$rolle->status_kurzbz = 'Interessent';
		else
			$rolle->status_kurzbz = 'Incoming';
		$rolle->studiensemester_kurzbz = $studiensemester_kurzbz;
		$rolle->ausbildungssemester = $ausbildungssemester;
		$rolle->orgform_kurzbz = $orgform_kurzbz;
		$rolle->studienplan_id = $studienplan_id;
		$rolle->datum = date('Y-m-d');
		$rolle->insertamum = date('Y-m-d H:i:s');
		$rolle->insertvon = $user;

		$rolle->new = true;

		if (!$rolle->save_rolle())
		{
			$error = true;
			$errormsg = $rolle->errormsg;
		}
		else
			$error = false;
	}

	if (!$error && $incoming)
	{
		//Matrikelnummer und UID generieren
		$matrikelnr = generateMatrikelnummer($studiengang_kz, $studiensemester_kurzbz);

		$jahr = mb_substr($matrikelnr,0, 2);
		$stg = mb_substr($matrikelnr, 3, 4);

		$stg_obj = new studiengang();
		$stg_obj->load(ltrim($stg,'0'));

		$uid = generateUID($stg_obj->kurzbz,$jahr, $stg_obj->typ, $matrikelnr);

		//Benutzerdatensatz anlegen
		$benutzer = new benutzer();
		$benutzer->uid = $uid;
		$benutzer->person_id = $person->person_id;
		$benutzer->aktiv = true;
		$benutzer->aktivierungscode = generateActivationKey();

		$nachname_clean = mb_strtolower(convertProblemChars($person->nachname));
		$vorname_clean = mb_strtolower(convertProblemChars($person->vorname));
		$nachname_clean = str_replace(' ','_', $nachname_clean);
		$vorname_clean = str_replace(' ','_', $vorname_clean);

		if (!defined('GENERATE_ALIAS_STUDENT') || GENERATE_ALIAS_STUDENT === true)
		{
			$qry_alias = "SELECT * FROM public.tbl_benutzer WHERE alias = LOWER(".$db->db_add_param(".$vorname_clean.".".$nachname_clean.").")";
			$result_alias = $db->db_query($qry_alias);
			if ($db->db_num_rows($result_alias) == 0)
				$benutzer->alias = $vorname_clean.'.'.$nachname_clean;
			else
				$benutzer->alias = '';
		}
		else
			$benutzer->alias = '';

		$benutzer->insertamum = date('Y-m-d H:i:s');
		$benutzer->insertvon = $user;

		if ($benutzer->save(true, false))
		{
			//Studentendatensatz anlegen
			$student = new student();
			$student->uid = $uid;
			$student->matrikelnr = $matrikelnr;
			$student->prestudent_id = $prestudent->prestudent_id;
			$student->studiengang_kz = $studiengang_kz;
			$student->semester = '0';
			$student->verband = 'I';
			$student->gruppe = ' ';
			$student->insertamum = date('Y-m-d H:i:s');
			$student->insertvon = $user;

			$lvb = new lehrverband();
			if (!$lvb->exists($student->studiengang_kz, $student->semester, $student->verband, $student->gruppe))
			{
				$lvb->studiengang_kz = $student->studiengang_kz;
				$lvb->semester = $student->semester;
				$lvb->verband = $student->verband;
				$lvb->gruppe = $student->gruppe;
				$lvb->bezeichnung = 'Incoming';
				$lvb->aktiv = true;

				$lvb->save(true);
			}

			if ($student->save(true, false))
			{
				//StudentLehrverband anlegen
				$studentlehrverband = new student();
				$studentlehrverband->uid = $uid;
				$studentlehrverband->studiensemester_kurzbz = $studiensemester_kurzbz;
				$studentlehrverband->studiengang_kz = $studiengang_kz;
				$studentlehrverband->semester = '0';
				$studentlehrverband->verband = 'I';
				$studentlehrverband->gruppe = ' ';
				$studentlehrverband->insertamum = date('Y-m-d H:i:s');
				$studentlehrverband->insertvon = $user;

				if (!$studentlehrverband->save_studentlehrverband(true))
				{
					$error = true;
					$errormsg = 'StudentLehrverband konnte nicht angelegt werden';
				}
			}
			else
			{
				$error = true;
				$errormsg = 'Student konnte nicht angelegt werden: '.$student->errormsg;
			}
		}
		else
		{
			$error = true;
			$errormsg = 'Benutzer konnte nicht angelegt werden:'.$benutzer->errormsg;
		}
	}

	if (!$error)
	{
		$db->db_query('COMMIT');
		die("<b>".($incoming?'Incoming':'InteressentIn')." $vorname $vornamen $nachname wurde erfolgreich angelegt</b><br><br><a href='interessentenimport.php?studiengang_kz=$studiengang_kz'>Neue Person anlegen</a>");
	}
	else
	{
		$db->db_query('ROLLBACK');
		echo '<span class="error">'.$errormsg.'</span>';
	}
}
// *** SAVE ENDE ***

$geburtsdatum_orig = $geburtsdatum;
if ($geburtsdatum != '')
{
	//Wenn das Datum im Format d.m.Y ist dann in Y-m-d umwandeln
	if (mb_strpos($geburtsdatum,'.'))
	{
		if ($datum_obj->mktime_datum($geburtsdatum))
		{
			$geburtsdatum = date('Y-m-d',$datum_obj->mktime_datum($geburtsdatum));
		}
		else
		{
			$geburtsdatum_error = true;
		}
	}
	else
	{
		if (!mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})", $geburtsdatum))
			$geburtsdatum_error = true;
	}

	if ($geburtsdatum_error)
		echo "Format des Geburtsdatums ist ungueltig!";
}
if (($geburtsdatum == '' && $vorname == '' && $nachname == '') || $geburtsdatum_error)
	echo "<form method='POST' onsubmit='return checkInput1();'>";
else
	echo "<form method='POST'>";
?>
<table width="100%">

<tr>
<td valign="top">
<!--Formularfelder-->
<table>
<?php
echo '<tr><td>Anrede</td><td><input type="text" id="anrede" name="anrede" maxlength="64" value="'.$anrede.'"  onblur="AnredeChange()"/></td></tr>';
echo '<tr><td>Titel(Pre)</td><td><input type="text" id="titel" name="titel" maxlength="64" value="'.$titel.'" /></td></tr>';
echo '<tr><td>Vorname </td><td><input type="text" id="vorname" maxlength="32" name="vorname" value="'.$vorname.'" /></td></tr>';
echo '<tr><td>Weitere Vornamen </td><td><input type="text" id="vornamen" maxlength="32" name="vornamen" value="'.$vornamen.'" /></td></tr>';
echo '<tr><td>Wahlname </td><td><input type="text" id="wahlname" maxlength="32" name="wahlname" value="'.$wahlname.'" /></td></tr>';
echo '<tr><td>Nachname *</td><td><input type="text" maxlength="64" id="nachname" name="nachname" value="'.$nachname.'" required="required" autofocus/></td></tr>';
echo '<tr><td>Titel(Post)</td><td><input type="text" id="titelpost" name="titelpost" maxlength="64" value="'.$titelpost.'" /></td></tr>';
echo '<tr><td>Geschlecht *</td><td><SELECT id="geschlecht" name="geschlecht">';
$geschlecht_obj = new geschlecht();
$geschlecht_obj->getAll();
foreach ($geschlecht_obj->result as $row_geschlecht)
{
	if ($row_geschlecht->geschlecht == $geschlecht)
		$selected = 'selected';
	else
		$selected = '';

	echo '<OPTION value="'.$row_geschlecht->geschlecht.'" '.$selected.'>'.$row_geschlecht->bezeichnung_mehrsprachig_arr[DEFAULT_LANGUAGE].'</OPTION>';
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Geburtsdatum </td><td><input type="text" id="geburtsdatum" size="10" maxlength="10" name="geburtsdatum" value="'.$geburtsdatum_orig.'" /> (Format: dd.mm.JJJJ)</td></tr>';
echo '<tr><td colspan="2"><fieldset><legend>Adresse</legend><table>';

if (isset($adresse_nation) && $adresse_nation == 'A' && isset($plz) && $plz > 10000)
	$nationstyle = 'style="border: 1px solid red"';
else
	$nationstyle = '';
echo '<tr><td>Land</td><td><SELECT name="adresse_nation" id="adresse_nation" onchange="loadGemeindeData();changeGebnation()" '.$nationstyle.'>';
$nation =  new nation();
$nation->getAll();
foreach ($nation->nation as $row)
{
	if ($row->code == $adresse_nation)
		$selected = 'selected';
	else
		$selected = '';
	echo "<option value='$row->code' $selected>$row->langtext</option>";
}
echo '</SELECT></td></tr>';
echo '<tr><td>Postleitzahl</td><td><input type="text" size="5" maxlength="16" id="plz" name="plz" value="'.$plz.'" onblur="loadGemeindeData()" /></td></tr>';
echo '<tr><td>Adresse</td><td><input type="text" id="adresse" maxlength="256"  size="40" name="adresse" value="'.$adresse.'" /></td></tr>';
echo '<tr><td>Gemeinde</td><td><div id="gemeindediv">';
//wenn die Nation Oesterreich ist, dann wird ein DropDown fuer Gemeinde und Ort angezeigt.
//wenn die Nation nicht Oesterreich ist, werden nur textfelder angezeigt
if ($adresse_nation == 'A' && $plz != '')
{
	echo getGemeindeDropDown($plz);
}
else
{
	echo '<font color="gray">Bitte zuerst eine Postleitzahl eintragen</font>';
}

//wenn der Ort per EMail-Import von der www kommt und der Ort in der Gemeindetabelle
//nicht gefunden wird, dann wird der Ort in Klammer neben dem DropDown angezeigt
if ($importort != '' && $gemeinde != $importort)
	echo ' ( '.$importort.' )';

echo '</div><input type="'.($adresse_nation=='A'?'hidden':'text').'" id="adresse-gemeinde-textfeld" maxlength="256" name="gemeinde_txt" value="'.$gemeinde.'" />';

echo '</td></tr>';
echo '<tr><td>Ort</td><td><div id="ortdiv">';
if ($adresse_nation == 'A' && $plz != '')
{
	echo getOrtDropDown($plz, $gemeinde);
}
echo '</div><input type="'.($adresse_nation=='A'?'hidden':'text').'" id="adresse-ort-textfeld" maxlength="256" name="ort_txt" value="'.$ort.'"/></td></tr>';

echo '</table>';
echo '<div style="display: none;" id="ueb1"><input type="radio" id="ueberschreiben1" name="ueberschreiben" value="Ja" onclick="disablefields2(false)">Bestehende Adresse überschreiben</div>';
echo '<div style="display: none;" id="ueb2"><input type="radio" id="ueberschreiben2" name="ueberschreiben" value="Nein" onclick="disablefields2(false)" checked>Adresse hinzufügen</div>';
echo '<div style="display: none;" id="ueb3"><input type="radio" id="ueberschreiben3" name="ueberschreiben" value="" onclick="disablefields2(true)">Adresse nicht anlegen</div>';
echo '</fieldset></td></tr>';
echo '<tr><td>Geburtsnation</td><td><SELECT name="geburtsnation" id="geburtsnation">';
$nation =  new nation();
$nation->getAll();
foreach ($nation->nation as $row)
{
	if ($row->code == $geburtsnation)
		$selected = 'selected';
	else
		$selected = '';
	echo "<option value='$row->code' $selected>$row->langtext</option>";
}
echo '</SELECT></td></tr>';
echo '<tr><td>Staatsbürgerschaft</td><td><SELECT name="staatsbuergerschaft" id="staatsbuergerschaft">';
$nation =  new nation();
$nation->getAll();
foreach ($nation->nation as $row)
{
	if ($row->code == $staatsbuergerschaft)
		$selected = 'selected';
	else
		$selected = '';
	echo "<option value='$row->code' $selected>$row->langtext</option>";
}
echo '</SELECT></td></tr>';
echo '<tr><td>EMail</td><td><input type="text" id="email" maxlength="128" name="email" value="'.$email.'" /></td></tr>';
echo '<tr><td>Telefon</td><td><input type="text" id="telefon" maxlength="128" name="telefon" value="'.$telefon.'" /></td></tr>';
echo '<tr><td>Mobil</td><td><input type="text" id="mobil" maxlength="128" name="mobil" value="'.$mobil.'" /></td></tr>';
echo '<tr><td>Letzte Ausbildung</td><td><SELECT id="letzteausbildung" name="letzteausbildung">';
echo '<OPTION value="" '.($letzteausbildung==''?'selected':'').'>-- keine Auswahl --</OPTION>';
$qry = "SELECT * FROM bis.tbl_ausbildung ORDER BY ausbildungcode";
if ($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		echo '<OPTION value="'.$row->ausbildungcode.'" '.($letzteausbildung==$row->ausbildungcode?'selected':'').'>'.$row->ausbildungbez.'</OPTION>';
	}
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Ausbildungsart</td><td><input type="text" id="ausbildungsart" name="ausbildungsart" value="'.$ausbildungsart.'" /></td></tr>';
echo '<tr><td>Anmerkungen</td><td><textarea id="anmerkung" name="anmerkungen">'.$anmerkungen.'</textarea></td></tr>';
echo '<tr><td>Studiengang *</td><td><SELECT id="studiengang_kz" name="studiengang_kz" onchange="loadStudienplanData()">';
$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz');
foreach ($stg_obj->result as $row)
{
	if ($rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid') || $rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid'))
		echo '<OPTION value="'.$row->studiengang_kz.'" '.($row->studiengang_kz==$studiengang_kz?'selected':'').'>'.$row->kuerzel.'</OPTION>';
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Studiensemester *</td><td><SELECT id="studiensemester_kurzbz" name="studiensemester_kurzbz" onchange="loadStudienplanData()">';
$stsem = new studiensemester();
$stsem->getAll();
foreach ($stsem->studiensemester as $row)
	echo '<OPTION value="'.$row->studiensemester_kurzbz.'" '.($row->studiensemester_kurzbz==$studiensemester_kurzbz?'selected':'').'>'.$row->studiensemester_kurzbz.'</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Ausbildungssemester *</td><td><SELECT id="ausbildungssemester" name="ausbildungssemester" onchange="loadStudienplanData()" '.($incoming?'disabled':'').'>';
for ($i=1;$i<9;$i++)
	echo '<OPTION value="'.$i.'" '.($i==$ausbildungssemester?'selected':'').'>'.$i.'. Semester</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>OrgForm</td><td><SELECT id="orgform_kurzbz" name="orgform_kurzbz" onchange="loadStudienplanData()">';
echo '<OPTION value="">-- keine Auswahl --</OPTION>';
$qry = "SELECT orgform_kurzbz, bezeichnung FROM bis.tbl_orgform WHERE rolle ORDER BY bezeichnung";
if ($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		echo '<OPTION value="'.$row->orgform_kurzbz.'" '.($orgform_kurzbz==$row->orgform_kurzbz?'selected':'').'>'.$row->bezeichnung.'</OPTION>';
	}
}
echo '</SELECT>';
echo '</td></tr>';
echo "\n";
echo '<tr><td>Studienplan</td><td><div id="studienplandiv">';
if ($studiengang_kz!='')
	echo getStudienplanDropDown($studiengang_kz, $orgform_kurzbz, $studienplan_id, $studiensemester_kurzbz, $ausbildungssemester);
else
	echo '<font color="gray">Bitte zuerst einen Studiengang waehlen</font>';
echo '</div></td>
</tr>';

echo '<tr><td>Incoming:</td><td><input type="checkbox" id="incoming" name="incoming" '.($incoming?'checked':'').' onclick="cmdIncoming()" /></td></tr>';
echo '<tr><tr><td></td><td>';

if (($geburtsdatum=='' && $vorname=='' && $nachname=='') || $geburtsdatum_error)
	echo '<input type="submit" name="showagain" value="Vorschlag laden"></td></tr>';
else
{
	echo '<input type="submit" name="showagain" value="Vorschlag laden">';
	echo '<input type="submit" name="save" value="Speichern" onclick="return checkVorschlag()"></td></tr>';
}
?>
</table>
<br><br>
Felder die mit einem * gekennzeichnet sind müssen ausgefüllt werden!

</td>
<td valign="top">
<!--Vorschlaege-->
<?php
//Vorschlaege laden
if ($geburtsdatum != '')
{
	if (mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$geburtsdatum))
	{
		$where = " gebdatum=".$db->db_add_param($geburtsdatum);
	}
}

if ($vorname != '' && $nachname != '')
{
	if ($where != '')
		$where .= ' OR';
	$where .= " (LOWER(vorname)=LOWER(".$db->db_add_param($vorname).") AND LOWER(nachname)=LOWER(".$db->db_add_param($nachname)."))";
}
elseif ($nachname != '')
{
	if ($where != '')
		$where .= ' OR';
	$where .= " LOWER(nachname)=LOWER(".$db->db_add_param($nachname).")";
}

if ($where != '')
{
	$qry = "SELECT * FROM public.tbl_person WHERE $where ORDER BY nachname, vorname, gebdatum";

	if ($result = $db->db_query($qry))
	{
		echo '<table style="margin-top: 0px" class="tablesorter" id="t1"><thead><tr><th></th><th>Nachname</th><th>Vorname</th><th>Wahlname</th><th>Weitere<br/>Vornamen</th><th>GebDatum</th><th>SVNR</th><th>Geschlecht</th><th>Adresse</th><th>Status</th><th>Details</th></tr></thead>';
		echo '<tfoot><tr><td style="padding: 4px"><input type="radio" name="person_id" value="0" onclick="disablefields(this)"></td><td style="padding: 4px" colspan="3">Neue Person anlegen</td></tr></tfoot><tbody>';
		while($row = $db->db_fetch_object($result))
		{
			$status = '';
			$qry_stati = "SELECT 'Mitarbeiter' as rolle FROM campus.vw_mitarbeiter WHERE person_id='$row->person_id'
							UNION
							SELECT (get_rolle_prestudent(prestudent_id, null) || ' ' || UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz)) as rolle FROM public.tbl_prestudent JOIN public.tbl_studiengang USING(studiengang_kz) WHERE person_id='$row->person_id'
							UNION
							SELECT 'PreInteressent' as rolle FROM public.tbl_preinteressent WHERE person_id='$row->person_id'";
			if ($result_stati = $db->db_query($qry_stati))
			{
				while($row_stati = $db->db_fetch_object($result_stati))
				{
					$status .= $row_stati->rolle.', ';
				}
			}
			$status = mb_substr($status, 0, mb_strlen($status)-2);

			echo '<tr valign="top"><td><input type="radio" name="person_id" value="'.$row->person_id.'" onclick="disablefields(this)"></td><td>'."$row->nachname</td><td>$row->vorname</td><td>$row->wahlname</td><td>$row->vornamen</td><td>$row->gebdatum</td><td>$row->svnr</td><td>".($row->geschlecht=='m'?'männlich':'weiblich')."</td><td>";
			$qry_adr = "SELECT * FROM public.tbl_adresse WHERE person_id=".$db->db_add_param($row->person_id, FHC_INTEGER);
			if ($result_adr = $db->db_query($qry_adr))
				while ($row_adr = $db->db_fetch_object($result_adr))
					echo "$row_adr->plz $row_adr->ort, $row_adr->strasse<br>";
			echo '</td>';
			echo '<td>'.$status.'</td>';
			echo '<td><a href="../personendetails.php?id='.$row->person_id.'" target="_blank">Details</a></td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}
}

?>
</td>
</tr>
</table>
</form>
</body>
</html>
