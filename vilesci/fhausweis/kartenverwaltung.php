<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>,
 *			Andreas Österreicher <oesi@technikum-wien.at>
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/student.class.php');
require_once('../../include/fotostatus.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/variable.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

define("anzahlSemester","10");
$buchstabenArray = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','Ä','Ö','Ü');

$studiengang = new studiengang();
$studiengang->getAll('typ, bezeichnung', true);
$studiengang_array = array();

$fotostatus = new fotostatus();
$fotostatus->getAllStatusKurzbz();

$mails = array();
$variable = new variable();
$variable->loadVariables($uid);

$statusStudent=(isset($_REQUEST['select_statusStudent'])?$_REQUEST['select_statusStudent']:null);
$statusMitarbeiter=(isset($_REQUEST['select_statusMitarbeiter'])?$_REQUEST['select_statusMitarbeiter']:null);
$typMitarbeiter =(isset($_REQUEST['select_typ_mitarbeiter'])?$_REQUEST['select_typ_mitarbeiter']:null);
$studiengang_kz=(isset($_REQUEST['select_studiengang'])?$_REQUEST['select_studiengang']:null);
$semester=(isset($_REQUEST['select_semester'])?$_REQUEST['select_semester']:null);
$buchstabe=(isset($_REQUEST['select_buchstabe'])?$_REQUEST['select_buchstabe']:null);
$studSemArray=(isset($_REQUEST['select_studiensemester'])?$_REQUEST['select_studiensemester']:array());

if (empty($studSemArray))
{
	$studiensemester = new studiensemester();

	$studSemArray[]=$studiensemester->getakt();
	$studSemArray[]=$studiensemester->getPrevious();
	$studSemArray[]=$studiensemester->getBeforePrevious();
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
		"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet"  href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css">
	<link href="../../skin/jquery.css" rel="stylesheet"  type="text/css"/>

	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$("#myTableFiles").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra"]
			});
		});

		function showStudiensemester()
		{
			document.getElementById("studiensemester_dropdown").style.display="inline";
		}
		function hideStudiensemester()
		{
			document.getElementById("studiensemester_dropdown").style.display="none";
		}

		</script>
	<title>FH-Ausweis Kartenverwaltung</title>
</head>
<?php
if(!$rechte->isBerechtigt('basis/fhausweis', 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

echo '<body>
<h2>FH-Ausweis Kartenverwaltung</h2>
<fieldset style="display: inline; vertical-align: top">
	<legend>Studentensuche</legend>
	<form method="POST" name="form_filterStudent">
		<table border="0">
			<tr>
				<td>Studiengang:</td>
				<td><select name="select_studiengang">
				<option value="">Alle (keine Incoming)</option>
				<option value="incoming" '.($studiengang_kz=="incoming"?'selected':'').'>Incoming</option>
 				<option value="bama" '.($studiengang_kz=="bama"?'selected':'').'>Bachelor und Master</option>
				<option value="special" '.($studiengang_kz=="special"?'selected':'').'>Spezialfälle</option>
				';
				$typ = '';
				foreach($studiengang->result as $stud)
				{
					// 10007 -> EVU Studiengang
					//if($stud->studiengang_kz < '10000' || $stud->studiengang_kz == '10007' || $stud->studiengang_kz=='10004')
					$studiengang_array[$stud->studiengang_kz] = mb_strtoupper($stud->typ.$stud->kurzbz);
					if ($typ != $stud->typ || $typ=='')
					{
						if ($typ!='')
							echo '</optgroup>';
						echo '<optgroup label="'.$stud->typ.'">';
					}
					echo '<option value='.$stud->studiengang_kz.' '.($studiengang_kz==$stud->studiengang_kz?'selected':'').'>'.mb_strtoupper($stud->typ.$stud->kurzbz).' - '.$stud->bezeichnung.'</option>';
					$typ = $stud->typ;
				}
echo'			</select></td>
				<td>Semester:</td>
				<td><select name="select_semester">';
				echo '<option>alle</option>';
				for($i = 1;$i<=anzahlSemester;$i++)
					echo '<option '.($semester==$i?'selected':'').'>'.$i.'</option>';

echo'			</select>
				</td>
				<td>letzter Status:</td>
				<td><select name="select_statusStudent">';
				foreach($fotostatus->result as $foto)
				{
					echo '<option value="'.$foto->fotostatus_kurzbz.'" '.($statusStudent==$foto->fotostatus_kurzbz?'selected':'').'>'.ucfirst($foto->fotostatus_kurzbz).'</option>';
				}
echo'			<option value="nichtGedrucktAkzept" '.($statusStudent=='nichtGedrucktAkzept'?'selected':'').'>Akzeptiert und nicht gedruckt</option>
				<option value="nichtGedruckt" '.($statusStudent=='nichtGedruckt'?'selected':'').'>nicht gedruckt</option>
				<option value="gedrucktNichtAusgegeben" '.($statusStudent=='gedrucktNichtAusgegeben'?'selected':'').'>Gedruckt nicht ausgegeben</option>
				</select></td>
				<td><input name="btn_submitStudent" type="submit" value="Anzeigen"></td>
			</tr>
		</table>
	</form>
</fieldset>

<fieldset style="display:inline;">
	<legend>Mitarbeitersuche</legend>
	<form method="POST" name="form_filterMitarbeiter">
		<div style="float: right;">
			<table style="vertical-align: top" border="0" >
				<tr style="vertical-align: top">
					<td>Typ:</td>
					<td><select name="select_typ_mitarbeiter" onClick="this.options[this.selectedIndex].onclick()">
					<option value="intern" '.($typMitarbeiter=='intern'?'selected':'').' onClick="hideStudiensemester();">Fixangestellte</option>
					<option value="extern" '.($typMitarbeiter=='extern'?'selected':'').' onClick="showStudiensemester();">Externe mit Lehrauftrag</option>
					<option value="extern_ohne" '.($typMitarbeiter=='extern_ohne'?'selected':'').' onClick="hideStudiensemester();">Externe ohne Lehrauftrag</option>
					</select></td>
					<td id="studiensemester_dropdown" style="display: '.($typMitarbeiter=='extern'?'inline':'none').'; vertical-align: top;"><span style="vertical-align: top">im</span>
					<select name="select_studiensemester[]" multiple="multiple" size="7">';
					$studsem = new studiensemester();
					$studsem->getPlusMinus(2,5);
					foreach ($studsem->studiensemester as $s)
					{
						$selected=false;
						if(in_array($s->studiensemester_kurzbz, $studSemArray))
							$selected=true;
						echo '<option value="'.$s->studiensemester_kurzbz.'" '.($selected==true?'selected':'').'>'.$s->studiensemester_kurzbz.'</option>';
					}
echo'				</select>
					<td>Anfangsbuchstabe:</td>
					<td><select name="select_buchstabe">
					<option value="">*</option>';
					foreach($buchstabenArray as $b)
					{
							echo '<option value="'.$b.'" '.($b==$buchstabe?'selected':'').'>'.$b.'</option>';
					}
echo'				</select>
					<td>letzter Status:</td>
					<td><select name="select_statusMitarbeiter">';
					foreach($fotostatus->result as $foto)
					{
						echo '<option value="'.$foto->fotostatus_kurzbz.'" '.($statusMitarbeiter==$foto->fotostatus_kurzbz?'selected':'').'>'.ucfirst($foto->fotostatus_kurzbz).'</option>';
					}
echo'			   <option value="nichtGedruckt" '.($statusMitarbeiter=='nichtGedruckt'?'selected':'').'>Akzeptiert und nicht gedruckt</option>
					<option value="gedrucktNichtAusgegeben" '.($statusMitarbeiter=='gedrucktNichtAusgegeben'?'selected':'').'>Gedruckt nicht ausgegeben</option>
					</select></td>
					<td><input name="btn_submitMitarbeiter" type="submit" value="Anzeigen"></td>
				</tr>
			</table>
		</div>
	</form>
</fieldset>';

// zeige alle Studenten an
if(isset($_REQUEST['btn_submitStudent']))
{
	$uids = '';
	if($semester == 'alle')
		$semester = null;

	$studenten = new student();
	$studentenArray = array();

	if($studiengang_kz=='incoming')
	{
		$studenten->getIncoming();
	}
	elseif ($studiengang_kz=='special')
	{
		foreach($studiengang->result as $stud)
		{
			if($stud->studiengang_kz >= '10000' || $stud->studiengang_kz < '0' || $stud->studiengang_kz == '9005')
				$studenten->getStudentsStudiengang($stud->studiengang_kz, $semester);
		}
	}
	elseif ($studiengang_kz=='bama')
	{
		foreach($studiengang->result as $stud)
		{
			if($stud->typ == 'b' || $stud->typ == 'm')
				$studenten->getStudentsStudiengang($stud->studiengang_kz, $semester);
		}
	}
	else
	{
		$studenten->getStudentsStudiengang($studiengang_kz, $semester);
	}
	$studentenArray = $studenten->result;

	echo '
		<form method="POST" name="form_studentenkarten" action="kartezuweisen.php">
		<table id="myTableFiles" class="tablesorter">
		<thead>
			<tr>
				<th>Name</th>
				<th>Geburtsdatum</th>
				<th>Matrikelnummer</th>
				<th>UID</th>
				<th>Studiengang</th>
				<th>person_id</th>
			</tr>
		</thead>
		<tbody>';

	if (count($studentenArray) > 0)
	{
		foreach($studentenArray as $stud)
		{
			//if($stud->studiengang_kz>10000  && $stud->studiengang_kz !='10007'  && $stud->studiengang_kz!='10004')
				//continue;

			// Wenn letzter Status nicht Student ist -> nicht anzeigen
			$prestudent = new prestudent();
			$prestudent->getLastStatus($stud->prestudent_id);
			if(($prestudent->status_kurzbz == 'Student' || ($studiengang_kz=='incoming' && $prestudent->status_kurzbz='Incoming')) && array_key_exists($stud->studiengang_kz, $studiengang_array))
			{
				if($statusStudent=='gedrucktNichtAusgegeben')
				{
					// gedruckt aber noch nicht ausgegeben
					$fotostatus = new fotostatus();
					$fotostatus->getLastFotoStatus($stud->person_id);
					$betriebsmittel = new betriebsmittel();

					// status akzeptiert und noch nicht gedruckt
					if($fotostatus->fotostatus_kurzbz == 'akzeptiert' && $betriebsmittel->zutrittskartePrinted($stud->uid) == true && $betriebsmittel->zutrittskarteAusgegeben($stud->uid) == false)
					{
						echo '<tr><td>'.$stud->nachname.' '.$stud->vorname.'</td><td>'.$stud->gebdatum.'</td><td>'.$stud->matrikelnr.'</td><td>'.$stud->uid.'</td><td>'.$studiengang_array[$stud->studiengang_kz].'</td><td>'.$stud->person_id.'<input type="hidden" name="users[]" value="'.$stud->uid.'"></td></tr>';
						$uids.=';'.$stud->uid;
						$mails[]=$stud->uid.'@'.DOMAIN;
					}
				}
				else if($statusStudent == 'nichtGedrucktAkzept')
				{
					// akzeptiert und nicht gedruckt
					$fotostatus = new fotostatus();
					$fotostatus->getLastFotoStatus($stud->person_id);
					$betriebsmittel = new betriebsmittel();

					// status akzeptiert und noch nicht gedruckt
					if($fotostatus->fotostatus_kurzbz == 'akzeptiert' && $betriebsmittel->zutrittskartePrinted($stud->uid) == false)
					{
						echo '<tr><td>'.$stud->nachname.' '.$stud->vorname.'</td><td>'.$stud->gebdatum.'</td><td>'.$stud->matrikelnr.'</td><td>'.$stud->uid.'</td><td>'.$studiengang_array[$stud->studiengang_kz].'</td><td>'.$stud->person_id.'<input type="hidden" name="users[]" value="'.$stud->uid.'"></td></tr>';
						$uids.=';'.$stud->uid;
						$mails[]=$stud->uid.'@'.DOMAIN;
					}
				}
				else if($statusStudent == 'nichtGedruckt')
				{
					// akzeptiert und nicht gedruckt
					$fotostatus = new fotostatus();
					$fotostatus->getLastFotoStatus($stud->person_id);
					$betriebsmittel = new betriebsmittel();

					// noch nicht gedruckt
					if($betriebsmittel->zutrittskartePrinted($stud->uid) == false)
					{
						echo '<tr><td>'.$stud->nachname.' '.$stud->vorname.' ('.$fotostatus->fotostatus_kurzbz.')</td><td>'.$stud->gebdatum.'</td><td>'.$stud->matrikelnr.'</td><td>'.$stud->uid.'</td><td>'.$studiengang_array[$stud->studiengang_kz].'</td><td>'.$stud->person_id.'<input type="hidden" name="users[]" value="'.$stud->uid.'"></td></tr>';
						$uids.=';'.$stud->uid;
						$mails[]=$stud->uid.'@'.DOMAIN;
					}
				}
				else
				{
					// letzten Status anzeigen
					$fotostatus = new fotostatus();
					$fotostatus->getLastFotoStatus($stud->person_id);

					// überprüfen ob letzer Status der gesuchte ist
					if($fotostatus->fotostatus_kurzbz == $statusStudent)
					{
						echo '<tr><td>'.$stud->nachname.' '.$stud->vorname.'</td><td>'.$stud->gebdatum.'</td><td>'.$stud->matrikelnr.'</td><td>'.$stud->uid.'</td><td>'.$studiengang_array[$stud->studiengang_kz].'</td><td>'.$stud->person_id.'<input type="hidden" name="users[]" value="'.$stud->uid.'"></td></tr>';
						$uids.=';'.$stud->uid;
						$mails[]=$stud->uid.'@'.DOMAIN;
					}
				}
			}
		}
	}
	//Mail Zusammenfassen
	$mails = array_unique($mails);
	echo "
		<script type=\"text/Javascript\">
		var mails = '".implode($variable->variable->emailadressentrennzeichen,$mails)."';

		// ****
		// * Teilt die Mailto Links auf kleinere Brocken auf, da der
		// * Link nicht funktioniert wenn er zu lange ist
		// * art = to | cc | bcc
		// ****
		function splitmailto(mails, art)
		{
			var splititem = '".$variable->variable->emailadressentrennzeichen."';
			var splitposition=0;
			var mailto='';
			var loop=true;
			if(mails.length>2048)
				alert('Aufgrund der großen Anzahl an Empfängern, muss die Nachricht auf mehrere E-Mails aufgeteilt werden!');

			while(loop)
			{
				if(mails.length>2048)
				{
					splitposition=mails.indexOf(splititem,1900);
					mailto = mails.substring(0,splitposition);
					mails = mails.substring(splitposition);
				}
				else
				{
					loop=false;
					mailto=mails;
				}

				if(art=='to')
					window.location.href='mailto:'+mailto;
				else
					window.location.href='mailto:?'+art+'='+mailto;
			}
		}
		</script>";
	echo '
		</tbody>
		</table>
		<table>
			<tr>
				<td>Anzahl: '.count($mails).'</td>
			</tr>
			<tr>
				<td>
					<input type="button" value="Mail Senden" name="MailSenden" onclick="splitmailto(mails, \'bcc\'); return false;">&nbsp;
					<input type="submit" value="Karten zuteilen" name="btn_kartezuteilenStudent" onclick="document.form_studentenkarten.action=\'kartezuweisen.php\'">&nbsp;
					<input type="button" value="Karten drucken" onclick="document.form_studentenkarten.action=\'../../content/zutrittskarte.php\';document.form_studentenkarten.submit();"/>
				</td>
			</tr>
		</table>
		</form>';
	if(count($mails)>500)
	{
		printWarning();
	}

	//<input type="button" value="Karten drucken" onclick=\'window.open("../../content/zutrittskarte.php?data='.$uids.'");\'>

}
// Zeige alle Mitarbeiter an
if(isset($_REQUEST['btn_submitMitarbeiter']))
{
	$fixangestellt = true;
	if($_REQUEST['select_typ_mitarbeiter'] == 'extern')
		$fixangestellt = false;

	if($_REQUEST['select_typ_mitarbeiter'] == 'extern_ohne')
	{
		$fixangestellt = false;
		$studSemArray = null;
	}

	$mitarbeiter = new mitarbeiter();
	$mitarbeiter->getMitarbeiterForZutrittskarte($buchstabe, $fixangestellt, $studSemArray);

	$uids = '';

	echo '
		<form method="POST" name="form_mitarbeiterkarten" action="kartezuweisen.php">
		<table id="myTableFiles" class="tablesorter">
		<thead>
			<tr>
				<th>Name</th>
				<th>Geburtsdatum</th>
				<th>Personalnummer</th>
				<th>UID</th>
				<th>person_id</th>
			</tr>
		</thead>
		<tbody>';

		foreach($mitarbeiter->result as $mit)
		{
			if($statusMitarbeiter=='gedrucktNichtAusgegeben')
			{
				$fotostatus = new fotostatus();
				$fotostatus->getLastFotoStatus($mit->person_id);
				$betriebsmittel = new betriebsmittel();

				// status akzeptiert, gedruckt aber noch nicht ausgegeben
				if($fotostatus->fotostatus_kurzbz == 'akzeptiert' && $betriebsmittel->zutrittskartePrinted($mit->uid) == true && $betriebsmittel->zutrittskarteAusgegeben($mit->uid) == false)
				{
					$uids.=';'.$mit->uid;
					$mails[]=$mit->uid.'@'.DOMAIN;
					echo '<tr><td>'.$mit->nachname.' '.$mit->vorname.'</td><td>'.$mit->gebdatum.'</td><td>'.$mit->personalnummer.'</td><td>'.$mit->uid.'</td><td>'.$mit->person_id.'<input type="hidden" name="users[]" value="'.$mit->uid.'"></td></tr>';
				}
			}
			else if($statusMitarbeiter == 'nichtGedruckt')
			{
				$fotostatus = new fotostatus();
				$fotostatus->getLastFotoStatus($mit->person_id);
				$betriebsmittel = new betriebsmittel();

				// status akzeptiert und noch nicht gedruckt
				if($fotostatus->fotostatus_kurzbz == 'akzeptiert' && $betriebsmittel->zutrittskartePrinted($mit->uid) == false)
				{
					$uids.=';'.$mit->uid;
					$mails[]=$mit->uid.'@'.DOMAIN;
					echo '<tr><td>'.$mit->nachname.' '.$mit->vorname.'</td><td>'.$mit->gebdatum.'</td><td>'.$mit->personalnummer.'</td><td>'.$mit->uid.'</td><td>'.$mit->person_id.'<input type="hidden" name="users[]" value="'.$mit->uid.'"></td></tr>';
				}
			}
			else
			{
				$fotostatus = new fotostatus();
				$fotostatus->getLastFotoStatus($mit->person_id);

				// überprüfen ob letzer Status der gesuchte ist
				if($fotostatus->fotostatus_kurzbz == $statusMitarbeiter)
				{
					$uids.=';'.$mit->uid;
					$mails[]=$mit->uid.'@'.DOMAIN;
					echo '<tr><td>'.$mit->nachname.' '.$mit->vorname.'</td><td>'.$mit->gebdatum.'</td><td>'.$mit->personalnummer.'</td><td>'.$mit->uid.'</td><td>'.$mit->person_id.'<input type="hidden" name="users[]" value="'.$mit->uid.'"></td></tr>';
				}
			}
		}

	//Mail Zusammenfassen
	$mails = array_unique($mails);
	echo "
		<script type=\"text/Javascript\">
		var mails = '".implode($variable->variable->emailadressentrennzeichen,$mails)."';

		// ****
		// * Teilt die Mailto Links auf kleinere Brocken auf, da der
		// * Link nicht funktioniert wenn er zu lange ist
		// * art = to | cc | bcc
		// ****
		function splitmailto(mails, art)
		{
			var splititem = '".$variable->variable->emailadressentrennzeichen."';
			var splitposition=0;
			var mailto='';
			var loop=true;
			if(mails.length>2048)
				alert('Aufgrund der großen Anzahl an Empfängern, muss die Nachricht auf mehrere E-Mails aufgeteilt werden!');

			while(loop)
			{
				if(mails.length>2048)
				{
					splitposition=mails.indexOf(splititem,1900);
					mailto = mails.substring(0,splitposition);
					mails = mails.substring(splitposition);
				}
				else
				{
					loop=false;
					mailto=mails;
				}

				if(art=='to')
					window.location.href='mailto:'+mailto;
				else
					window.location.href='mailto:?'+art+'='+mailto;
			}
		}
		</script>";
	echo '
		</tbody>
		</table>
		<table>
			<tr>
				<td>Anzahl: '.count($mails).'</td>
			</tr>
			<tr>
				<td>
					<input type="button" value="Mail Senden" name="MailSenden" onclick="splitmailto(mails, \'bcc\'); return false;">&nbsp;
					<input type="submit" value="Karten zuteilen" name="btn_kartezuteilenMitarbeiter" onclick="document.form_mitarbeiterkarten.action=\'kartezuweisen.php\'">&nbsp;
					<input type="button" value="Karten drucken" onclick="document.form_mitarbeiterkarten.action=\'../../content/zutrittskarte.php\';document.form_mitarbeiterkarten.submit();"/>
				</td>
			</tr>
		</table>
		</form>';
	if(count($mails)>500)
	{
		printWarning();
	}
	echo '
		</body></html>';
	//<input type="button" value="Karten drucken" onclick=\'window.open("../../content/zutrittskarte.php?data='.$uids.'");\'>
}
function printWarning()
{
	echo '<div style="color:red; font-style:bold">
		Achtung - Es sind sehr viele Einträge vorhanden. <br>
		Dies kann Probleme bei der Kartenzuordnung verursachen.<br>
		Verwende zur Kartenzuordnung bitte einen Studiengangsfilter
		</div>';
}
?>
