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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/konto.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/student.class.php');
require_once('../../../include/akte.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/webservicelog.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';

$uid=get_uid();

if(isset($_GET['uid']))
{
	// Administratoren duerfen die UID als Parameter uebergeben um die Dokumente
	// von anderen Personen anzuzeigen

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);
	if($rechte->isBerechtigt('admin'))
	{
		$uid = $_GET['uid'];
		$getParam = "&uid=" . $uid;
	}
	else
		$getParam = "";
}
else
	$getParam='';

$student_studiengang = new student();
$student_studiengang->load($uid);
$xsl_stg_kz = $student_studiengang->studiengang_kz;

$stg = '';

if(isset($_GET['action']) && $_GET['action']=='download')
{
	if(isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = $_GET['id'];
		$akte = new akte();
		$akte->load($id);
		if ($akte->person_id == $student_studiengang->person_id
			&& $akte->stud_selfservice)
		{
			if($akte->inhalt!='')
			{
				//Header fuer Datei schicken
				header("Content-type: $akte->mimetype");
				header('Content-Disposition: attachment; filename="'.$akte->titel.'"');
				echo base64_decode($akte->inhalt);

				//Log bei einem Download vom Becheid
				if ((isset($akte->dokument_kurzbz) && !empty($akte->dokument_kurzbz)) && ($akte->dokument_kurzbz === 'Bescheid' || $akte->dokument_kurzbz === 'BescheidEng'))
				{
					$log = new Webservicelog();
					$log->webservicetyp_kurzbz = 'content';
					$log->request_id = (isset($akte->akte_id) && !empty($akte->akte_id)) ? $akte->akte_id : NULL;
					$log->beschreibung = 'Bescheidbestaetigungsdownload';
					$log->request_data = $_SERVER['QUERY_STRING'];
					$log->execute_user = get_uid();
					$log->save(true);
				}

				exit;
			}
			else
			{
				die('Akte hat keinen Inhalt.');
			}
		}
		else
		{
			die('Nicht zum selbständigen Download bestimmt oder falsche PersonID.');
		}
	}
	else
	{
		die('Id ist ungueltig');
	}
}
echo '<!DOCTYPE HTML>
<html>
<head>
	<title>'.$p->t('tools/bestaetigungenZeugnisse').'</title>
	<meta charset="UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">';
	include('../../../include/meta/jquery.php');
	include('../../../include/meta/jquery-tablesorter.php');
echo '
	<script language="JavaScript" type="text/javascript">

	$(document).ready(function()
	{
		$(".tablesorter").tablesorter(
		{
			sortList: [[1,0]],
			headers: { 0: { sorter: false }},
			widgets: ["zebra"]
		});
		$(".tablesorter2").tablesorter(
		{
			headers: { 0: { sorter: false }},
			widgets: ["zebra"]
		});
	});

	function changeSemester(obj)
	{
		self.location = obj.options[obj.selectedIndex].value + "'.$getParam.'";
	};

	function createStudienerfolg(stsem, language, finanzamtCheckboxId)
	{
		var finanzamt = document.getElementById(finanzamtCheckboxId).checked;
		var xsl = "";
		
		if (language == "en")
			xsl = "StudienerfolgEng";
		else
			xsl = "Studienerfolg";
		
		if(finanzamt)
			finanzamt = "&typ=finanzamt";
		else
			finanzamt = "";
		
		if(stsem == "alle")
			alle = "&all=1";
		else
			alle = "";
		
		window.location.href= "../pdfExport.php?xml=studienerfolg.rdf.php&xsl="+xsl+"&ss="+stsem+"&uid='.$uid.'"+finanzamt+alle;
	};
	</script>
	<style>
	table.tablesorter tbody td
	{
		padding: 4px;
	{
	</style>
</head>
<body>
<h1>'.$p->t('tools/bestaetigungenZeugnisse').'</h1>';

$prestudent = new prestudent();
$prestudent->getPrestudentRolle($student_studiengang->prestudent_id);

$stsem_arr = array();
$laststsem = '';
foreach($prestudent->result as $row)
{
	$stsem_arr[] = $row->studiensemester_kurzbz;
	$laststsem = $row->studiensemester_kurzbz;
}
$stsem_arr = array_unique($stsem_arr);
if($stsem == '')
	$stsem = $laststsem;

/*//Aktuelles Studiensemester oder gewaehltes Studiensemester
$stsem_obj = new studiensemester();
if($stsem == '')
	$stsem = $stsem_obj->getaktorNext();

$stsem_obj->getAll();
*/
echo $p->t('global/studiensemester');
echo ' <SELECT name="stsem" id="stsem" onChange="changeSemester(this)">';
if (!in_array($stsem, $stsem_arr))
{
	echo '<OPTION value="dokumente.php?stsem='.$stsem.'" selected>';
	echo $stsem;
	echo '</OPTION>';
}
foreach ($stsem_arr as $semrow)
{
	if ($stsem == $semrow)
	{
		echo '<OPTION value="dokumente.php?stsem='.$semrow.'" selected>';
		echo $semrow;
		echo '</OPTION>';
	}
	else
	{
		echo '<OPTION value="dokumente.php?stsem='.$semrow.'">';
		echo $semrow;
		echo '</OPTION>';
	}
}
echo '</SELECT><br /><br />';

// Wenn es für das übergebene Studiensemester keinen PreStudentStatus gibt, werden nur Abschlussdokumente angezeigt 
if (in_array($stsem, $stsem_arr))
{
	$konto = new konto();
	
	$buchungstypen = array();
	if (defined("CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN"))
	{
		$buchungstypen = unserialize (CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN);
	}
	echo '<h2>' . $p->t('tools/inskriptionsbestaetigung') . '</h2>';
	$stsem_zahlung = $konto->getLastStSemBuchungstypen($uid, $buchungstypen, $stsem);
	echo '<table class="tablesorter" style="width:auto;">
			<thead>
			<tr>
				<th></th>
				<th>'.$p->t('global/name').'</th>
			</tr>
			</thead>
			<tbody><tr>';
	if ($stsem_zahlung != FALSE && $stsem == $stsem_zahlung)
	{
		$path = "../pdfExport.php?xsl=Inskription&xml=student.rdf.php&ss=".$stsem."&uid=".$uid."&xsl_stg_kz=".$xsl_stg_kz;
		echo '<td><img src="../../../skin/images/pdfpic.gif" /></td>';
		echo '<td><a href="'.$path.'">'.$p->t('tools/inskriptionsbestaetigung').' '.$stsem.'</a></td>';
	}
	else
	{
		echo '<td colspan="2">'.$p->t('tools/studienbeitragFuerSSNochNichtBezahlt',array($stsem)).'</td>';
	}
	echo '</tr></tbody></table>';
	
	if (defined('CIS_DOKUMENTE_STUDIENBUCHLBATT_DRUCKEN') && CIS_DOKUMENTE_STUDIENBUCHLBATT_DRUCKEN)
	{
		echo '<h2>' . $p->t('tools/studienbuchblatt') . '</h2>';
		echo '<table class="tablesorter" style="width:auto;">
			<thead>
			<tr>
				<th></th>
				<th>'.$p->t('global/name').'</th>
			</tr>
			</thead>
			<tbody><tr>';
		if ($stsem_zahlung != FALSE && $stsem == $stsem_zahlung)
		{
			$pfad = "../pdfExport.php?xsl=Studienblatt&xml=studienblatt.xml.php&ss=".$stsem."&uid=".$uid;
			echo '<td><img src="../../../skin/images/pdfpic.gif" /></td>';
			echo '<td><a href="'.$pfad.'">'.$p->t('tools/studienbuchblatt').' '.$stsem.'</a></td>';
		}
		else
		{
			echo '<td colspan="2">'.$p->t('tools/studienbeitragFuerSSNochNichtBezahlt',array($stsem)).'</td>';
		}
		echo '</tr></tbody></table>';
	}
	
	if (defined('CIS_DOKUMENTE_STUDIENERFOLGSBESTAETIGUNG_DRUCKEN') && CIS_DOKUMENTE_STUDIENERFOLGSBESTAETIGUNG_DRUCKEN)
	{
		echo '<h2>' . $p->t('tools/studienerfolgsbestaetigung') . '</h2>';
		echo '<table class="tablesorter" style="width:auto;">
			<thead>
			<tr>
				<th></th>
				<th>'.$p->t('global/name').'</th>
				<th></th>
			</tr>
			</thead>
			<tbody>';
		echo '<tr><td><img src="../../../skin/images/pdfpic.gif" /></td>';
		echo '<td><a href="#" onclick="createStudienerfolg(\''.$stsem.'\', \'de\', \'finanzamtDeutschStudiensemester\')">'.$p->t('tools/studienerfolgsbestaetigung').' '.$stsem.' '.$p->t('global/deutsch').'</a></td>';
		echo '<td><input type="checkbox" id="finanzamtDeutschStudiensemester"> '.$p->t('tools/vorlageWohnsitzfinanzamt').'</td></tr>';
		
		echo '<tr><td><img src="../../../skin/images/pdfpic.gif" /></td>';
		echo '<td><a href="#" onclick="createStudienerfolg(\'alle\', \'de\', \'finanzamtDeutschAlle\')">'.$p->t('tools/studienerfolgsbestaetigung').' '.$p->t('tools/alleStudiensemester').' '.$p->t('global/deutsch').'</a></td>';
		echo '<td><input type="checkbox" id="finanzamtDeutschAlle"> '.$p->t('tools/vorlageWohnsitzfinanzamt').'</td></tr>';
		
		echo '<tr><td><img src="../../../skin/images/pdfpic.gif" /></td>';
		echo '<td><a href="#" onclick="createStudienerfolg(\''.$stsem.'\', \'en\', \'finanzamtEnglishStudiensemester\')">'.$p->t('tools/studienerfolgsbestaetigung').' '.$stsem.' '.$p->t('global/englisch').'</a></td>';
		echo '<td><input type="checkbox" id="finanzamtEnglishStudiensemester"> '.$p->t('tools/vorlageWohnsitzfinanzamt').'</td></tr>';
		
		echo '<tr><td><img src="../../../skin/images/pdfpic.gif" /></td>';
		echo '<td><a href="#" onclick="createStudienerfolg(\'alle\', \'en\', \'finanzamtEnglishAlle\')">'.$p->t('tools/studienerfolgsbestaetigung').' '.$p->t('tools/alleStudiensemester').' '.$p->t('global/englisch').'</a></td>';
		echo '<td><input type="checkbox" id="finanzamtEnglishAlle"> '.$p->t('tools/vorlageWohnsitzfinanzamt').'</td></tr>';
		echo '</tbody></table>';
	}
}
else 
	echo '<p class="error">'.$p->t('tools/keinStatusImStudiensemester',array($stsem)).'</p>';

if (!defined('CIS_DOKUMENTE_SELFSERVICE') || CIS_DOKUMENTE_SELFSERVICE)
{
	$akte = new akte();
	echo '<h2>' . $p->t('tools/abschlussdokumente') . '</h2>';
	echo '<table><tr>';
	echo '<td style="	background-color: #fcf8e3; 
						color: #8a6d3b; 
						padding: .75rem 1.25rem; 
						margin-bottom: 1rem; 
						border: 1px solid #faf2cc; 
						border-radius: .25rem;">'.$p->t('tools/warnungDruckDigitaleSignatur').'</td>';
	echo '</tr></table>';

	if($akte->getArchiv($student_studiengang->person_id, null, true) && count($akte->result)>0)
	{
		echo '
		<table class="tablesorter2" style="width:auto;">
			<thead>
			<tr>
				<th></th>
				<th>'.$p->t('tools/erstelldatum').'</th>
				<th>'.$p->t('tools/dokument').'</th>
			</tr>
			</thead>
			<tbody>
		';

		$datum_obj = new datum();

		foreach($akte->result as $row)
		{
			$pfad = 'dokumente.php?action=download&id='.$row->akte_id.'&uid='.$uid;
			echo '<tr>';
			echo '<td><img src="../../../skin/images/pdfpic.gif" /></td>';
			echo '<td>'.$datum_obj->formatDatum($row->erstelltam,'d.m.Y').'</td>';
			echo '<td><a href="'.$pfad.'">'.$row->bezeichnung.'</a></td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}
	else 
	{
		echo '
		<table class="tablesorter2" style="width:auto;">
			<thead>
			<tr>
				<th></th>
				<th>'.$p->t('tools/erstelldatum').'</th>
				<th>'.$p->t('tools/dokument').'</th>
			</tr>
			</thead>
			<tbody>
		';
		echo '<td colspan="3">'.$p->t('tools/nochKeineAbschlussdokumenteVorhanden').'</td>';
		echo '</tbody></table>';
	}
}
echo '</body>
</html>
';
?>
