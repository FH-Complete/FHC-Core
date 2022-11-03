<?php
/* Copyright (C) 2011 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once('../config/wawi.config.inc.php');
require_once('auth.php');
require_once('../include/wawi_kostenstelle.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/benutzer.class.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WaWi Kostenstellen - Berechtigungen</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css"/>

	<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../vendor/jquery/sizzle/sizzle.js"></script>

</head>
<body>
<?php

$kostenstelle = new wawi_kostenstelle();
$uid=get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(isset($_GET['kostenstelle_id']))
{
	$kostenstelle_id = $_GET['kostenstelle_id'];

	if($rechte->isBerechtigt('wawi/rechnung',null, null, $kostenstelle_id)
	|| $rechte->isBerechtigt('wawi/bestellung',null, null, $kostenstelle_id)
	|| $rechte->isBerechtigt('wawi/freigabe',null, null, $kostenstelle_id))
	{
		$kst = new wawi_kostenstelle();
		if(!$kst->load($kostenstelle_id))
			die('Fehler beim Laden der Kostenstelle');
		$rechte = new benutzerberechtigung();
		echo '<h1>Berechtigungen - Kostenstelle '.$kst->bezeichnung.'</h1>';
		echo '<a href="berechtigung.php">Zurück</a>';
		$rechte->getKostenstelleUser($kostenstelle_id);

		$rights = array();

		function getArt($art)
		{
			$value=array();
			if(mb_strstr($art,'s'))
				$value['read']=true;
			if(mb_strstr($art,'u'))
				$value['write']=true;
			if(mb_strstr($art,'i'))
				$value['write']=true;
			if(mb_strstr($art,'d'))
				$value['delete']=true;
			return $value;
		}

		foreach($rechte->berechtigungen as $row)
		{

			if(!isset($rights[$row->uid]))
			{
				$benutzer = new benutzer();
				$benutzer->load($row->uid);

				if($benutzer->bnaktiv==true && in_array($row->berechtigung_kurzbz, array('wawi/rechnung','wawi/bestellung','wawi/freigabe')))
				{
					$rights[$row->uid]['vorname']=$benutzer->vorname;
					$rights[$row->uid]['nachname']=$benutzer->nachname;

				}
				else
					continue;
			}
			switch($row->berechtigung_kurzbz)
			{
				case 'wawi/rechnung': $rights[$row->uid]['rechnung']=getArt($row->art); break;
				case 'wawi/bestellung': $rights[$row->uid]['bestellung']=getArt($row->art); break;
				case 'wawi/freigabe': $rights[$row->uid]['freigabe']=true; break;
				default: break;
			}

		}
		echo '
		<script type="text/javascript">
			$(document).ready(function()
				{
				    $("#myTable").tablesorter(
					{
						sortList: [[0,0]],
						widgets: ["zebra"]
					});
				}
			);
		</script>';
		echo '<table class="tablesorter" id="myTable" style="width:auto">
			<thead>
			<tr>
				<th>Nachname</th>
				<th>Vorname</th>
				<th colspan="3">Bestellung</th>
				<th colspan="3">Rechnung</th>
				<th>Freigabe</th>
			</tr>
			<tr>
				<th></th>
				<th></th>
				<th>Lesen</th>
				<th>Schreiben</th>
				<th>Löschen</th>
				<th>Lesen</th>
				<th>Schreiben</th>
				<th>Löschen</th>
				<th></th>
			</tr>
			</thead>
			<tbody>';
		foreach($rights as $user1)
		{
			echo '<tr>';
			echo '<td>'.$user1['nachname'].'</td>';
			echo '<td>'.$user1['vorname'].'</td>';
			echo '<td>'.(isset($user1['bestellung']['read'])?'X':'').'</td>';
			echo '<td>'.(isset($user1['bestellung']['write'])?'X':'').'</td>';
			echo '<td>'.(isset($user1['bestellung']['delete'])?'X':'').'</td>';
			echo '<td>'.(isset($user1['rechnung']['read'])?'X':'').'</td>';
			echo '<td>'.(isset($user1['rechnung']['write'])?'X':'').'</td>';
			echo '<td>'.(isset($user1['rechnung']['delete'])?'X':'').'</td>';
			echo '<td>'.(isset($user1['freigabe'])?'X':'').'</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}
	else
		die('Sie haben keine Berechtigung!');
}
else
{
	$kst_array = $rechte->getKostenstelle('wawi/bestellung');
	$kst_array = array_merge($kst_array, $rechte->getKostenstelle('wawi/rechnung'));
	$kst_array = array_merge($kst_array, $rechte->getKostenstelle('wawi/bestellung'));
	$kst_array = array_merge($kst_array, $rechte->getKostenstelle('wawi/freigabe'));

	$kst_array = array_unique($kst_array);

	echo '<h1>Kostenstellen - Berechtigungen</h1>';

	if(count($kst_array)==0)
		die('Sie benoetigen eine Kostenstellenberechtigung um diese Seite anzuzeigen');

	$kst = new wawi_kostenstelle();
	$kst->loadArray($kst_array);
	echo '
	<script type="text/javascript">
		$(document).ready(function()
			{
			    $("#myTable").tablesorter(
				{
					sortList: [[1,0]],
					widgets: ["zebra"]
				});
			}
		);
	</script>';
	echo '<table id="myTable" class="tablesorter" style="width:auto">
		<thead>
			<tr>
				<th>ID</th>
				<th>Bezeichnung</th>
				<th>Berechtigung</th>
			</tr>
		</thead>
		<tbody>';
	foreach($kst->result as $row)
	{
		echo '<tr>';
		echo '<td>',$row->kostenstelle_id,'</td>';
		echo '<td>',$row->bezeichnung,'</td>';
		echo '<td><a href="'.$_SERVER['PHP_SELF'].'?kostenstelle_id='.$row->kostenstelle_id.'">User anzeigen</a></td>';
		echo '</tr>';
	}
	echo '</tbody>
		</table>';
}

echo '<br><br><br><br><br><br>';

?>