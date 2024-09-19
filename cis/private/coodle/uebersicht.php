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
 * 			Andreas Österreicher <oesi@technikum-wien.at>
 */
/**
 * Uebersicht ueber die Coodle Umfragen
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/coodle.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

$lang = getSprache();

$p = new phrasen($lang);

$uid = get_uid();
$message = '';

// Administratoren duerfen die UID als Parameter uebergeben um die Umfragen von anderen Personen anzuzeigen
if(isset($_GET['uid']))
{
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);
	if($rechte->isBerechtigt('admin'))
	{
		$uid = $_GET['uid'];
		$getParam = '&uid='.$uid;
	}
	else
		$getParam = '';
}
else
	$getParam = '';

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
		"http://www.w3.org/TR/html4/strict.dtd">
   <html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet"  href="../../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
		<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
		<link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css">
		<link href="../../../skin/jquery.css" rel="stylesheet"  type="text/css"/>
		<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
		<script type="text/javascript">
		$(document).ready(function()
		{
			$("#myTableFiles").tablesorter(
			{
				sortList: [[3,0]],
				widgets: ["zebra"]
			});

		});

		$(document).ready(function()
		{
			$("#myTableFiles2").tablesorter(
			{
				sortList: [[3,1]],
				widgets: ["zebra"]
			});

		});

		</script>
		<style>
			.wrapper h4
			{
				font-size: 17px;
				margin-top: 0;
				padding-top: 10px;
				padding-bottom: 10px;
				text-decoration: none;
			}
			a:hover
			{
				text-decoration: none;
			}
		</style>
';

echo'   <title>'.$p->t('coodle/uebersicht').'</title>
	</head>
	<body>';

$method = isset($_GET['method'])?$_GET['method']:'';

// coodle umfrage löschen
if($method=='delete')
{
	$coodle= new coodle();
	$coodle_id = isset($_GET['coodle_id'])?$_GET['coodle_id']:'';

	if($coodle->load($coodle_id))
	{
		// löschen nur von eigenen Umfragen möglich
		if($coodle->ersteller_uid!=$uid)
			$message = '<span class="error">'.$p->t('global/keineBerechtigung').'</span>';
		else
		{
			if($coodle->delete($coodle_id))
				$message ='<span class="ok">Erfolgreich storniert!</span>';
			else
				$message ='<span class="error">'.$p->t('coodle/umfrageKonnteNichtGeloeschtWerden').'</span>';
		}
	}
	else
		$message = '<span class ="error">'.$p->t('coodle/umfrageNichtGeladen').'</span>';
}

echo'<h1>'.$p->t('coodle/uebersicht').'</h1>';

echo $p->t('coodle/einfuehrungstext').'<br /><br />';

if(check_lektor($uid))
{
	echo '
		<div style="display:block; text-align:left; float:left;">
	<input type="button" onclick="window.location.href=\'stammdaten.php\'" value="'.$p->t('coodle/neueUmfrage').'">
	</div><br>';
}

echo '
	<div style="display:block; text-align:right; margin-right:16px; ">'.$message.'</div>
	<br>
	<div class="wrapper">
	<h4>'.$p->t('coodle/laufendeUmfragen').'</h4>
	<table id="myTableFiles" class="tablesorter">
	<thead>
		<tr>
			<th>'.$p->t('coodle/titel').'</th>
			<th>'.$p->t('coodle/letzterStatus').'</th>
			<th>'.$p->t('coodle/ersteller').'</th>
			<th>'.$p->t('coodle/endedatum').'</th>
			<th>'.$p->t('coodle/aktion').'</th>
		</tr>
	</thead><tbody>';

$beendeteUmfragen='';
$datum = new datum();
$coodle = new coodle();
$coodle->loadStatus();
$coodle->getCoodleFromUser($uid);
foreach($coodle->result as $c)
{
	$benutzer = new benutzer();
	$benutzer->load($c->ersteller_uid);
	$ersteller = $benutzer->nachname.' '.$benutzer->vorname;
	$row =  '<tr>
			<td>'.$coodle->convert_html_chars($c->titel).'</td>
			<td>'.$coodle->convert_html_chars($coodle->status_arr[$c->coodle_status_kurzbz]).'</td>
			<td>'.$coodle->convert_html_chars($ersteller).'</td>
			<td>'.$coodle->convert_html_chars($datum->formatDatum($c->endedatum, 'd.m.Y')).'</td>
			<td nowrap>
			';

	// Bearbeiten Button
	if((($c->coodle_status_kurzbz=='neu')||($c->coodle_status_kurzbz=='laufend')) && $uid==$c->ersteller_uid)
	{
		if($c->coodle_status_kurzbz=='laufend')
			$title=$p->t('coodle/umfrageWurdeBereitsGestartet');
		else
			$title=$p->t('coodle/bearbeiten');

		$row.= '&nbsp;<a href="stammdaten.php?coodle_id='.$c->coodle_id.'&'.$getParam.'">
					<img src="../../../skin/images/edit.png" title="'.$title.'">
				</a>';
	}
	else
	{
		$title=$p->t('global/keineBerechtigung');

		$row.= '&nbsp;<img src="../../../skin/images/edit_grau.png" title="'.$title.'">';
	}

	// Storno Button
	if($uid==$c->ersteller_uid && $c->coodle_status_kurzbz!='storniert' && $c->coodle_status_kurzbz!='abgeschlossen')
	{
		$row.= '&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?method=delete&coodle_id='.$c->coodle_id.'">
					<img src="../../../skin/images/delete_x.png" title="'.$p->t('coodle/loeschen').'">
				</a>';
	}
	else
	{
		$row.='&nbsp; <img src="../../../skin/images/delete_x_grau.png" title="'.$p->t('global/keineBerechtigung').'">';
	}

	// Umfrage Button
	if($c->coodle_status_kurzbz=='laufend' || $c->coodle_status_kurzbz=='abgeschlossen')
	{
		$row.= '&nbsp; <a href="../../public/coodle.php?coodle_id='.$c->coodle_id.'">
					<img src="../../../skin/images/date_go.png" title="'.$p->t('coodle/zurUmfrage').'">
				</a>';
	}
	else
	{
		if($c->coodle_status_kurzbz=='neu')
			$title=$p->t('coodle/umfrageNochNichtGestartet');
		else
			$title=$p->t('global/keineBerechtigung');

		$row.=' &nbsp; <img src="../../../skin/images/date_go_grau.png" title="'.$title.'">';
	}

	$row.='
			</td>
		</tr>';

	if($c->coodle_status_kurzbz=='laufend' || $c->coodle_status_kurzbz=='neu')
		echo $row;
	else
		$beendeteUmfragen.=$row;
}
echo '</tbody></table></div>';

if($beendeteUmfragen!='')
{
	echo '<br>

	<div class="wrapper">
	<h4>'.$p->t('coodle/beendeteUmfragen').'</h4>

	<table id="myTableFiles2" class="tablesorter">
		<thead>
			<tr>
				<th>'.$p->t('coodle/titel').'</th>
				<th>'.$p->t('coodle/letzterStatus').'</th>
				<th>'.$p->t('coodle/ersteller').'</th>
				<th>'.$p->t('coodle/endedatum').'</th>
				<th>'.$p->t('coodle/aktion').'</th>
			</tr>
		</thead>
		<tbody>
			'.$beendeteUmfragen.'
		</tbody>
	</table>
	</div>';
}
echo '</body>
</html>';
?>
