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
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/ressource.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/projektphase.class.php');
require_once('../../include/projekttask.class.php');

$showweeks=52;
$timestamp = time();
$ressource = new ressource();
$ressource_arr = array();

$datum_obj = new datum();
$datum = date('Y-m-d',$timestamp);
$endetimestamp = jump_week($timestamp,$showweeks);
$endedatum = date('Y-m-d',$endetimestamp);

if(isset($_GET['empty']))
{
	echo '<br><br><br>';
	exit;
}
elseif(isset($_GET['projekt_kurzbz']) && $_GET['projekt_kurzbz']!='')
{
	$projekt_kurzbz=$_GET['projekt_kurzbz'];
}
else
	$projekt_kurzbz=null;

if(isset($_GET['typ']) && $_GET['typ']=='projekt')
{
	$ressource->getProjektRessourceDatum($datum, $endedatum);
	$typ = 'projekt';
	$anzahl_warnung = 6;
}
elseif(isset($_GET['typ']) && $_GET['typ']=='task')
{
	$ressource->getTaskRessoureDatum($datum, $endedatum, $projekt_kurzbz);
	$typ = 'task';
	$anzahl_warnung = 6;
}
else
{
	$ressource->getProjektphaseRessourceDatum($datum, $endedatum, $projekt_kurzbz);
	$typ = 'phase';
	$anzahl_warnung = 6;
}

foreach($ressource->result as $row)
{
	$ressource_arr[]=$row->bezeichnung;
}

$ressource_arr = array_unique($ressource_arr);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Auslastung</title>
	<style>
	body
	{
		font-size: small;
	}
	table
	{
		border: 1px solid black;
	}
	.warning
	{
		color: red;
		font-weight: bold;
	}
	</style>
</head>
<body>
<table>
<tr>
	<th>Ressource ('.$typ.(!is_null($projekt_kurzbz)?'/'.$projekt_kurzbz:'').')</th>';
for($i=0;$i<$showweeks;$i++)
{
	$timestamp_kw = jump_week($timestamp,$i);
	echo '<th>KW '.kalenderwoche($timestamp_kw).'</th>';
}

echo '</tr>';

foreach($ressource_arr as $bezeichnung)
{
	$showrow=false;
	$htmlrow='<tr>';
	$htmlrow.='<td>'.$bezeichnung.'</td>';
	for($i=0;$i<$showweeks;$i++)
	{
		$timestamp_kw = jump_week($timestamp,$i);
		$anzahl=0;
		$title='';
		reset($ressource->result);
		$aufwandssumme=0;
		foreach($ressource->result as $row)
		{
			$beistrich=false;
			$start = $datum_obj->mktime_fromdate($row->start);
			$ende = $datum_obj->mktime_fromdate($row->ende);
			if($row->bezeichnung == $bezeichnung
				&& ($row->start=='' || $start<=$timestamp_kw)
				&& ($row->ende=='' || $ende>=$timestamp_kw)
				)
			{
				if($typ=='projekt' && $row->projekt_kurzbz!='')
				{
					$anzahl++;
					$title .= $row->projekt_kurzbz;
					$beistrich=true;
					$showrow=true;
				}
				elseif($typ=='phase' && $row->projektphase_id!='')
				{
					$anzahl++;
					$showrow=true;
					$phase = new projektphase();
					$phase->load($row->projektphase_id);
					$title .= $phase->bezeichnung.'('.$phase->projekt_kurzbz.')';
					$beistrich=true;
				}
				elseif($typ=='task' && $row->projekttask_id!='')
				{
					$anzahl++;
					$showrow=true;
					$task = new projekttask();
					$task->load($row->projekttask_id);
					$title.=$task->bezeichnung;
					$beistrich=true;
				}
				if($typ!='projekt' && $row->aufwand!='' && $row->aufwand!=0)
				{
					$title.='['.$row->aufwand.']';
					$beistrich=true;
					$aufwandssumme +=$row->aufwand;
				}
				if($beistrich)
					$title.=', ';
			}

		}
		$title = mb_substr($title,0,-1);

		$htmlrow.='<td title="'.$title.'" align="center">';
		if($anzahl>=$anzahl_warnung)
			$htmlrow.='<span class="warning">'.$anzahl.($typ!='projekt'?'/'.$aufwandssumme:'').'</span>';
		else
			$htmlrow.= $anzahl.($typ!='projekt'?'/'.$aufwandssumme:'');
		$htmlrow.= '</td>';
	}
	$htmlrow.='</tr>';
	if ($showrow)
		echo $htmlrow;
	ob_flush();
}

echo ' </table>';
echo '
	</body>
</html>';
?>
