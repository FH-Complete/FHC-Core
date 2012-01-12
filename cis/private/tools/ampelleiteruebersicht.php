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
	<link rel="stylesheet" href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../../../skin/jquery.css" type="text/css"/>
	<script type="text/javascript" src="../../../include/js/jquery.js"></script> 
	<title>',$p->t('tools/ampelsystem'),'</title>
	
	<script type="text/javascript">
	$(document).ready(function() 
	{ 
	    $("#myTable").tablesorter(
		{
			sortList: [[5,0]],
			widgets: [\'zebra\']
		}); 
	});
	</script>
</head>
<body>
<h1>',$p->t('tools/ampelsystem'),'</h1>
';


$datum_obj = new datum();
$benutzerfunktion = new benutzerfunktion();
$benutzerfunktion->getBenutzerFunktionen('Leitung', '', '', $user);

$organisationseinheit = new organisationseinheit();

$oes=array();
foreach ($benutzerfunktion->result as $row)
{
	$oe = $organisationseinheit->getChilds($row->oe_kurzbz);
	$oes = array_merge($oe, $oes);
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


$oe_arr = $oe_kurzbz!=''?array($oe_kurzbz):$oes;	
//echo 'OE: '.$oe_kurzbz.' Ampel:'.$ampel_id;
$ampel = new ampel();
if(!$ampel->loadAmpelMitarbeiter($oe_arr, $ampel_id))
	die('Fehler:'.$ampel->errormsg);


echo '
<table id="myTable" class="tablesorter">
	<thead>
		<tr>
			<th>'.$p->t('tools/ampelStatus').'</th>
			<th>'.$p->t('tools/ampelBeschreibung').'</th>
			<th>'.$p->t('global/institut').'</th>
			<th>'.$p->t('tools/ampelMitarbeiter').'</th>
			<th>'.$p->t('tools/ampelBestaetigtAm').'</th>
			<th>'.$p->t('tools/ampelRestdauer').'</th>
			<th>'.$p->t('tools/ampelDeadline').'</th>
		</tr>
	</thead>
	<tbody>
';

foreach($ampel->result as $row)
{
	$ts_deadline = $datum_obj->mktime_fromdate($row->deadline);
	$vlz = "-".$row->vorlaufzeit." day";
	$ts_vorlaufzeit = strtotime($vlz, $ts_deadline);
	$ts_now = $datum_obj->mktime_fromdate(date('Y-m-d'));
	
	if($ts_vorlaufzeit<=$ts_now && $ts_now<=$ts_deadline)
		$ampelstatus='gelb';
	elseif($ts_now>$ts_deadline)
		$ampelstatus='rot';
	elseif($ts_now<$ts_deadline && $ts_vorlaufzeit>=$ts_now)
		$ampelstatus='gruen';
	
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
			$status= '<img src="../../../skin/images/ampel_rot.png">';
			break;
		case 'gelb':
			$status= '<img src="../../../skin/images/ampel_gelb.png">';
			break;
		case 'gruen':
			$status= '<img src="../../../skin/images/ampel_gruen.png">';
			break;
		default:
			$status= '<img src="../../../skin/images/true.png" height="15px">';
			break;
	}
	echo $status;
	
	echo '</td>';
	$beschreibung = $row->beschreibung[$sprache];
	if($beschreibung=='' && isset($row->beschreibung[DEFAULT_LANGUAGE]))
		$beschreibung = $row->beschreibung[DEFAULT_LANGUAGE];
	echo '<td>'.$beschreibung.'</td>';
	$institut = $row->oe_kurzbz;
	echo '<td>'.$institut.'</td>';
	$name = $row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost;
	echo '<td>'.$name.'</td>';
	echo '<td>'.$datum_obj->formatDatum($row->insertamum_best,'d.m.Y').'</td>';
	echo '<td>'.(($ts_deadline-$ts_now)/86400).'</td>';
	echo '<td>'.$datum_obj->formatDatum($row->deadline,'d.m.Y').'</td>';
	echo '</tr>';
}
echo '</tbody></table>';

echo '</body>
</html>';
?>