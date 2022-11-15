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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/ort.class.php');
require_once('../../../include/raumtyp.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/phrasen.class.php');

$datum = (isset($_POST['datum'])?$_POST['datum']:date('d.m.Y'));
$vonzeit = (isset($_POST['vonzeit'])?$_POST['vonzeit']:date('H:i'));
$biszeit = (isset($_POST['biszeit'])?$_POST['biszeit']:date('H:i', mktime(date('H')+1,date('i'))));
$raumtyp = (isset($_POST['raumtyp'])?$_POST['raumtyp']:'');
$anzahlpersonen = (isset($_POST['anzahlpersonen'])?$_POST['anzahlpersonen']:'0');
$sent = true; //isset($_POST['sent']);
$datum_obj = new datum();

$sprache = getSprache();
$p = new phrasen($sprache);

echo '
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>'.$p->t('lvplan/reservierungsliste').'</title>
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../../../skin/jquery.css" type="text/css"/>
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css"/>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<link rel="stylesheet" href="../../../skin/flexcrollstyles.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
	<script language="Javascript">

	$(document).ready(function()
	{
	    $("#myTable").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});

		$( "#datum" ).datepicker($.datepicker.regional["de"]);
	});

	function checkdata()
	{
		if(document.getElementById("datum").value=="")
		{
			alert("'.$p->t('lvplan/datumAngeben').'");
			return false;
		}
		datum = document.getElementById("datum").value
		if(datum.length!=10)
		{
			alert("'.$p->t('lvplan/datumUngueltig').'");
			return false;
		}

		if(document.getElementById("vonzeit").value=="")
		{
			alert("'.$p->t('lvplan/vonZeitEingeben').'");
			return false;
		}

		if(document.getElementById("biszeit").value=="")
		{
			alert("'.$p->t('lvplan/bisZeitEingeben').'");
			return false;
		}
		return true;
	}
	</script>
</head>
<body>
	<h1>'.$p->t('lvplan/raumsuche').'</h1>
	<p><a href="index.php">'.$p->t('lvplan/lehrveranstaltungsplan').'</a></p>
';

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" onsubmit="return checkdata()">
		'.$p->t('global/datum').'* <input type="text" name="datum" id="datum" size="10" value="'.$datum.'">
		'.$p->t('global/von').'* <input type="text" name="vonzeit" id="vonzeit" size="5" value="'.$vonzeit.'">
		'.$p->t('global/bis').'* <input type="text" name="biszeit" id="biszeit" size="5" value="'.$biszeit.'">
		'.$p->t('lvplan/raumtyp').' <SELECT name="raumtyp" style="width: 100px;">
		<OPTION value="">'.$p->t('global/alle').'</OPTION>';
$raumtyp_obj = new raumtyp();
$raumtyp_obj->getAll();

foreach ($raumtyp_obj->result as $row)
{
	if($raumtyp==$row->raumtyp_kurzbz)
		$selected='selected';
	else
		$selected='';

	echo '<OPTION value="'.$row->raumtyp_kurzbz.'" '.$selected.'>'.$row->beschreibung.'</OPTION>';
}
echo '	</SELECT>
		'.$p->t('lvplan/anzahlPersonen').' <input type="text" size="3" name="anzahlpersonen" value="'.$anzahlpersonen.'">
		<input type="submit" name="sent" value="'.$p->t('global/suchen').'" />
	  </form>';
if($sent)
{
	$error=false;
	if($datum=='')
	{
		echo "<br>".$p->t('lvplan/datumAngeben');
		$error = true;
	}
	if($vonzeit=='')
	{
		echo "<br>".$p->t('lvplan/vonZeitEingeben');
		$error = true;
	}
	if($biszeit=='')
	{
		echo "<br>".$p->t('lvplan/bisZeitEingeben');
		$error = true;
	}

	if(!$error)
	{
		//Von Zeit pruefen
		if(!preg_match('/^[0-9]{2}:[0-9]{2}$/', $vonzeit))
		{
			echo "<br>".$p->t('lvplan/vonZeitFormat');
			$error = true;
		}
		//Bis Zeit pruefen
		if(!preg_match('/^[0-9]{2}:[0-9]{2}$/', $biszeit))
		{
			echo "<br>".$p->t('lvplan/bisZeitFormat');
			$error = true;
		}

		//Datum pruefen
		if(!$datum_obj->checkDatum($datum))
		{
			echo "<br>".$p->t('lvplan/datumUngueltig');
			$error = true;
		}
	}
	if(!$error)
	{
		$ort = new ort();
		if(!$ort->search($datum_obj->formatDatum($datum), $vonzeit, $biszeit, $raumtyp, $anzahlpersonen, true))
		{
			echo $ort->errormsg;
		}
		else
		{
			echo '<br><table class="tablesorter" id="myTable" style="width: auto">
			<thead>
				<tr>
					<th>'.$p->t('lvplan/raum').'</th>
					<th>'.$p->t('global/bezeichnung').'</th>
					<th>'.$p->t('global/nummer').'</th>
					<th>'.$p->t('global/personen').'</th>
					<th>'.$p->t('global/aktion').'</th>
				</tr>
			</thead>
			<tbody>';
			$i=0;
			$datum_sec = $datum_obj->mktime_datum($datum)-1;
			foreach ($ort->result as $row)
			{
				$i++;
				echo '<tr>';
				echo '<td>'.($row->content_id!=''?'<a href="../../../cms/content.php?content_id='.$row->content_id.'" title="'.$p->t('lvplan/rauminfoAnzeigen').'" target="_blank" onClick="window.resizeTo(1200,880)">'.$row->ort_kurzbz.'</a>':$row->ort_kurzbz).'</td>';
				echo "<td>$row->bezeichnung</td>";
				echo "<td>$row->planbezeichnung</td>";
				echo "<td>$row->max_person</td>";
				echo "<td><a href='stpl_week.php?type=ort&ort_kurzbz=$row->ort_kurzbz&datum=".$datum_sec."' class='Item'>".$p->t('lvplan/zurReservierung')."</a></td>";
				echo '</tr>';
				flush();
			}
			echo '</tbody></table>';
		}
	}
}
echo '</body></html>';
?>
