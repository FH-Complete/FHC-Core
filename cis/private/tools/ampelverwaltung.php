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

$user = get_uid();
$sprache = getSprache();
$p = new phrasen($sprache);

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
			sortList: [[2,0]],
			widgets: [\'zebra\']
		}); 
	});
	</script>
</head>
<body>
<h1>',$p->t('tools/ampelsystem'),'</h1>
';


$datum_obj = new datum();

$type = isset($_GET['type'])?$_GET['type']:'';
$ampel_id = isset($_GET['ampel_id'])?$_GET['ampel_id']:'';
$message='';

if($type=='bestaetigen' && is_numeric($ampel_id))
{
	$ampel = new ampel();
	if($ampel->load($ampel_id))
	{
		if($ampel->isZugeteilt($user, $ampel->benutzer_select))
		{
			if($ampel->bestaetigen($user, $ampel_id))
			{
				//$message = '<span class="ok">OK</span>';
				//Ampel Ansicht im Seiten-Header aktualisieren
				$message='<script type="text/javascript">window.parent.loadampel()</script>';
			}
			else
				$message = '<span class="error">'.$ampel->errormsg.'</span>';
		}
		else
			$message = '<span class="error">'.$p->t('tools/nichtZugeteilt').'</span>';
	}
	else
		$message = '<span class="error">'.$p->t('tools/ampelNichtGefunden').'</span>';
}

echo $message;

$ampel = new ampel();
$ampel->loadUserAmpel($user, true, true);

echo '
<table id="myTable" class="tablesorter">
	<thead>
		<tr>
			<th>'.$p->t('tools/ampelStatus').'</th>
			<th>'.$p->t('tools/ampelBeschreibung').'</th>
			<th>'.$p->t('tools/ampelDeadline').'</th>
			<th>'.$p->t('tools/ampelAktion').'</th>
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
	
	if($bestaetigt = $ampel->isBestaetigt($user,$row->ampel_id))
		$ampelstatus='';
	
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
	echo '<td>'.$datum_obj->formatDatum($row->deadline,'d.m.Y').'</td>';
	
	echo '<td>';
	if(!$bestaetigt)
		echo '<a href="'.$_SERVER['PHP_SELF'].'?ampel_id='.$row->ampel_id.'&type=bestaetigen">'.$p->t('tools/ampelBestaetigen').'</a>';
	else
		echo $p->t('tools/ampelBestaetigt');
	echo '</td>';
	
//	echo "<td>".date('d.m.Y',$ts_now)."</td>";
//	echo "<td align=\"center\">".date('d.m.Y',$ts_vorlaufzeit)."</td>";
//	echo "<td>".date('d.m.Y',$ts_deadline)."</td>";
	echo '</tr>';
}
echo '</tbody></table>';

echo '</body>
</html>';
?>