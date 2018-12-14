<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/ampel.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/benutzerfunktion.class.php');
require_once('../../../include/organisationseinheit.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

$user = get_uid();
$sprache = getSprache();
$p = new phrasen($sprache);
$basis = new basis();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css"/>
	<link rel="stylesheet" href="../../../skin/style.css.php" rel="stylesheet" type="text/css">';

	include('../../../include/meta/jquery.php');
	include('../../../include/meta/jquery-tablesorter.php');

	echo '
	<title>',$p->t('tools/ampelsystem'),'</title>

	<script type="text/javascript">
	$(document).ready(function() 
	{ 
		$("#myTable").tablesorter(
		{
			imgAttr: "alt",
			headers: {0 : { sorter: "image" }},
			sortList: [[0,1],[1,0],[2,0]],
			widgets: [\'zebra\']
		}); 
	});
	</script>
</head>
<body>
<h1>',$p->t('tools/ampelsystem'),'</h1>
';

$datum_obj = new datum();

//Leiter OEs holen
$benutzerfunktion = new benutzerfunktion();
$benutzerfunktion->getBenutzerFunktionen('Leitung', '', '', $user);

$organisationseinheit = new organisationseinheit();

$oes=array();
foreach ($benutzerfunktion->result as $row)
{
	$oe = $organisationseinheit->getChilds($row->oe_kurzbz);
	$oes = array_merge($oe, $oes);
}

//Berechtigungs OEs holen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if($rechte->isBerechtigt('basis/ampeluebersicht'))
{
	$oes_berechtigung = $rechte->getOEkurzbz('basis/ampeluebersicht');
	$oes = array_merge($oes_berechtigung, $oes);
}

array_unique($oes);
if(count($oes)==0)
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

if(!$organisationseinheit->loadArray($oes,'organisationseinheittyp_kurzbz, bezeichnung'))
	echo 'Fehler:'.$organisationseinheit->errormsg;

if(isset($_POST['oe_kurzbz']))
	$oe_kurzbz=$_POST['oe_kurzbz'];
else
	$oe_kurzbz='';
	
if(isset($_POST['ampel_id']))
	$ampel_id = $_POST['ampel_id'];
else
	$ampel_id = '';

if (isset($_GET['ampel_benutzer_bestaetigt_id']) && isset($_GET['delete']))
{
	if ($rechte->isBerechtigt('admin', null, 'suid'))
	{
		$delete_bestaetigung = new ampel();
		if($delete_bestaetigung->deleteAmpelBenutzer($_GET['ampel_benutzer_bestaetigt_id']))
		{
			echo '<span class="ok">Ampelbestaetigung erfolgreich geloescht</span>';
		}
		else
		{
			$action='new';
			echo '<span class="error">'.$delete_bestaetigung->errormsg.'</span>';
		}
	}
}


echo '<p><a href="ampelverwaltung.php">'.($p->t('tools/ampelsystem')).'</a></p>';
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
echo $p->t('global/organisationseinheit').': <SELECT name="oe_kurzbz">';
echo '<OPTION value="">'.$p->t('global/alle').'</OPTION>';
foreach($organisationseinheit->result as $row)
{
	if($oe_kurzbz==$row->oe_kurzbz)
		$selected='selected="selected"';
	else
		$selected='';
	
	echo '<OPTION value="'.$basis->convert_html_chars($row->oe_kurzbz).'" '.$selected.'>';
	echo $basis->convert_html_chars($row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung);
	echo '</OPTION>';
}
echo '</SELECT>';

$ampel = new ampel();
$ampel->getAll();
echo ' '.$p->t('tools/ampel').': <SELECT name="ampel_id">';
echo '<OPTION value="">'.$p->t('global/alle').'</OPTION>';
foreach($ampel->result as $row)
{
	if($ampel_id==$row->ampel_id)
		$selected='selected="selected"';
	else
		$selected='';
	
	echo '<OPTION value="'.$basis->convert_html_chars($row->ampel_id).'" '.$selected.'>';
	echo $basis->convert_html_chars($row->kurzbz);
	echo '</OPTION>';
}
echo '</SELECT>';
echo '<input type="submit" value="OK" />';
echo '</form><br>';

if(!isset($_POST['ampel_id']))
{
	echo $p->t('tools/waehlenSieEineOEoderAmpel');
	exit;
}
$oe_arr = $oe_kurzbz!=''?array($oe_kurzbz):$oes;	
//echo 'OE: '.$oe_kurzbz.' Ampel:'.$ampel_id;
$ampel = new ampel();
if(!$ampel->loadAmpelMitarbeiter($oe_arr, $ampel_id))
	die('Fehler:'.$ampel->errormsg);

if($ampel_id != '')
{
	$ampel_aktuell = new ampel($ampel_id);
	echo '<div style="padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: #efefef"><h3>Ampeltext:</h3>'.$ampel_aktuell->beschreibung[$sprache].'</div>';
}

echo '
<table id="myTable" class="tablesorter">
	<thead>
		<tr>
			<th>'.$p->t('tools/ampelStatus').'</th>
			<th>'.$p->t('tools/ampelKurzbz').'</th>
			<th>'.$p->t('tools/ampelMitarbeiter').'</th>
			<th>'.$p->t('global/organisationseinheit').'</th>
			<th>'.$p->t('tools/ampelBestaetigtAm').'</th>
			<th>'.$p->t('tools/ampelDeadline').'</th>
			<th>'.$p->t('tools/ampelRestdauer').'</th>';
			if ($rechte->isBerechtigt('admin', null, 'suid'))
				echo '<th>'.$p->t('global/loeschen').'</th>';
echo '	</tr>
	</thead>
	<tbody>
';
$anzahlRot = 0;
$anzahlGelb = 0;
$anzahlGruen = 0;
foreach($ampel->result as $row)
{
	$ts_deadline = $datum_obj->mktime_fromdate($row->deadline);
	$vlz = "-".$row->vorlaufzeit." day";
	$ts_vorlaufzeit = strtotime($vlz, $ts_deadline);
	$ts_now = $datum_obj->mktime_fromdate(date('Y-m-d'));
	
	if($ts_vorlaufzeit<=$ts_now && $ts_now<=$ts_deadline)
	{
		$ampelstatus='gelb';
	}
	elseif($ts_now>$ts_deadline)
	{
		$ampelstatus='rot';
	}
	elseif($ts_now<$ts_deadline && $ts_vorlaufzeit>=$ts_now)
	{
		$ampelstatus='gruen';
	}
	
	//if($bestaetigt = $ampel->isBestaetigt($user,$row->ampel_id))
	//	$ampelstatus='';
	if($row->ampel_benutzer_bestaetigt_id!='')
	{
		$ampelstatus='';
		$bestaetigt=true;
	}
	else
		$bestaetigt=false;
	
	echo '<tr>';
	echo '<td align="center">';
	switch($ampelstatus)
	{
		case 'rot':
			$status= '<img alt="C" src="../../../skin/images/ampel_rot.png">';
			$anzahlRot++;
			break;
		case 'gelb':
			$status= '<img alt="B" src="../../../skin/images/ampel_gelb.png">';
			$anzahlGelb++;
			break;
		case 'gruen':
			$status= '<img alt="B" src="../../../skin/images/ampel_gruen.png">';
			$anzahlGruen++;
			break;
		default:
			$status= '<img alt="A" src="../../../skin/images/ampel_gruen.png">';
			$anzahlGruen++;
			break;
	}
	echo $status;
	
	echo '</td>';
	$beschreibung = $row->kurzbz;
	//if($beschreibung=='' && isset($row->beschreibung[DEFAULT_LANGUAGE]))
		//$beschreibung = $row->beschreibung[DEFAULT_LANGUAGE];
	echo '<td>'.$beschreibung.'</td>';
	
	//$name = $row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost;
	$name = $row->nachname.' '.$row->vorname.($row->titelpre != '' || $row->titelpost !='' ? ' ('.$row->titelpre.' '.$row->titelpost.')' : '');
	echo '<td>'.$name.'</td>';
	$institut = $row->oe_kurzbz;
	echo '<td>'.$institut.'</td>';
	echo '<td>'.$datum_obj->formatDatum($row->insertamum_best,'d.m.Y').'</td>';
	echo '<td>'.$datum_obj->formatDatum($row->deadline,'d.m.Y').'</td>';
	
	//Restdauer wird nur angezeigt, wenn noch nicht bestaetigt
	if($bestaetigt)
		echo '<td></td>';
	else
		echo '<td>'.(($ts_deadline-$ts_now)/86400).'</td>';
	if ($rechte->isBerechtigt('admin', null, 'suid'))
	{
		if($bestaetigt)
			echo '<form action="'.$_SERVER['PHP_SELF'].'?ampel_benutzer_bestaetigt_id='.$row->ampel_benutzer_bestaetigt_id.'&delete" method="POST"><td>
					<input type="hidden" name="oe_kurzbz" value="'.$_POST['oe_kurzbz'].'">
					<input type="hidden" name="ampel_id" value="'.$_POST['ampel_id'].'">
					<button type="submit">'.$p->t('global/loeschen').'</button></td></form>';
		else	
			echo '<td></td>';
			
	}
	echo '</tr>';
}
echo '</tbody></table>';
echo '<br>';
echo 'Anzahl rot: '.$anzahlRot.'<br>';
echo 'Anzahl gelb: '.$anzahlGelb.'<br>';
echo 'Anzahl grün: '.$anzahlGruen.'<br>';
echo '</body>
</html>';
?>