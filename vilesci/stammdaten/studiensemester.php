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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *          Manfred Kindl	<manfred.kindl@technikum-wien.at>
 */
/**
 * Studiensemesterverwaltung
 *
 */
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/datum.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/studiensemester.class.php');

	if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$user = get_uid();
	$datum_obj = new datum();
	$action = (isset($_GET['action'])?$_GET['action']:'');
	$studiensemester_kurzbz=(isset($_REQUEST['studiensemester_kurzbz'])?$_REQUEST['studiensemester_kurzbz']:'');
	$von = (isset($_POST['vondatum'])?$_POST['vondatum']:date('d.m.Y'));
	$bis = (isset($_POST['bisdatum'])?$_POST['bisdatum']:date('d.m.Y'));

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('admin'))
		die($rechte->errormsg);

	$studiensemester = new studiensemester();
	$studiensemester->getAll();

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//DE" "http://www.w3.org/TR/html4/strict.dtd">
				<html>
				<head>
					<title>Studiensemesterverwaltung</title>
					<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
					<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
					<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
					<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>
					<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
					<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
					<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
					<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
					<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
					<script type="text/javascript">
					$(document).ready(function()
					{
						$( ".datepicker" ).datepicker({
							dateFormat: "yy-mm-dd",
							changeMonth: true,
							changeYear: true,
							 });
						 $("#t1").tablesorter({
							sortList: [[1,1]],
							widgets: ["zebra"],
							headers: {7:{sorter:false}}
							});
								});
					</script>
				</head>
				<body class="Background_main">
				<h2>Studiensemesterverwaltung</h2>';

	// Speichern eines Studiensemesters
	if(isset($_GET['speichern']))
	{

		if(!$rechte->isBerechtigt('admin'))
		{
			die($rechte->errormsg);
		}

		$studiensemester = new studiensemester();

		if(isset($_POST['studiensemester_kurzbz']) && $_POST['studiensemester_kurzbz']!='' && $_GET['speichern']!='neu')
		{
			//Studiensemester laden
			if(!$studiensemester->load($_POST['studiensemester_kurzbz']))
			{
				die($studiensemester->errormsg);
			}

			$studiensemester->new=false;
			$studiensemester->studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
		}
		else
		{
			//Neues Studiensemester anlegen
			$studiensemester->new=true;
		}

		$studiensemester->studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
		$studiensemester->start = $_POST['start'];
		$studiensemester->ende = $_POST['ende'];
		$startS = substr($studiensemester->start, 8, 9).'-'.substr($studiensemester->start, 5, 2).'-'.substr($studiensemester->start, 0, 4);
		$endeS = substr($studiensemester->ende, 8, 9).'-'.substr($studiensemester->ende, 5, 2).'-'.substr($studiensemester->ende, 0, 4);
		$timestampStart = strtotime($startS);
		$timestampEnde = strtotime($endeS);
		if ($timestampEnde - $timestampStart <= 0) {
			echo '<span class="input_error">Das Enddatum darf nicht vor dem Startdatum sein</span><br/>';
		} else {
			$studiensemester->bezeichnung = $_POST['bezeichnung'];
			$studiensemester->studienjahr_kurzbz = $_POST['studienjahr_kurzbz'];
			$studiensemester->beschreibung = $_POST['beschreibung'];
			if (isset($_POST['onlinebewerbung'])) {
				$studiensemester->onlinebewerbung = true;
			} else {
				$studiensemester->onlinebewerbung = false;
			}
			//$studiensemester->onlinebewerbung = $_POST['onlinebewerbung'];

			if($studiensemester->save())
			{
				echo '<b>Daten wurden erfolgreich gespeichert</b>';
			}
			else
			{
				echo '<span class="input_error">'.$db->convert_html_chars($studiensemester->errormsg).'</span>';
			}
			echo "<br/>";
		}
	}

	/*
	//Dropdown Auswahl Studiengang
	$studiensemester = new Studiensemester();
	$studiensemester->getAll('DESC');

	echo "<SELECT name='studiensemester_kurzbz' id='studiensemester' onchange='window.location.href=this.value'>";
	if($studiensemester_kurzbz=='')
		$selected='selected';
	else
		$selected='';
	echo "<OPTION value='".$_SERVER['PHP_SELF']."' $selected>-- Bearbeiten --</OPTION>";
	foreach ($studiensemester->studiensemester as $row)
	{
		if($row->studiensemester_kurzbz==$studiensemester_kurzbz)
			$selected='selected';
		else
			$selected='';

		echo '<OPTION value="'.$_SERVER['PHP_SELF'].'?studiensemester_kurzbz='.$row->studiensemester_kurzbz.'" '.$selected.'>'.$row->studiensemester_kurzbz.'</OPTION>';
		echo "\n";
	}
	echo '</SELECT>';
	*/

	echo "<table style='width:100%;'><tr><td><h1>Neu</h1>";

	//Studiensemester bearbeiten
	if ($studiensemester_kurzbz != '' && !isset($_GET['speichern'])) {
		$studiensemester = new Studiensemester();
		$studiensemester->load($studiensemester_kurzbz);
		$checked = $studiensemester->onlinebewerbung=='t'?"checked":"";

		//Neues Studiensemester eintragen disabled
		echo '<form action="'.$_SERVER['PHP_SELF'].'?speichern=neu" method="POST">
				<table style="width:50%;">
					<tr>
						<td>Studiensemester_kurzbz</td><td><input type="text" disabled maxlength="16"/></td>
					</tr>
					<tr>
						<td>Start</td><td><input type="text" name="start" disabled/></td>
					</tr>
					<tr>
						<td>Ende</td><td><input type="text" name="ende" disabled/></td>
					</tr>
					<tr>
						<td>Bezeichnung</td><td><input type="text" name="bezeichnung" disabled/></td>
					</tr>
					<tr>
						<td>Studienjahr_kurzbz</td><td><input type="text" name="studienjahr_kurzbz" disabled/></td>
					</tr>
					<tr>
						<td>Beschreibung</td><td><textarea name="beschreibung" disabled cols="22"></textarea></td>
					</tr>
					<tr>
						<td>Onlinebewerbung</td><td><input type="checkbox" name="onlinebewerbung" disabled/></td>
					</tr>
			  </table>
			  <input type="hidden" name="studiensemester_kurzbz" disabled/>
			  <input type="submit" value="neu anlegen" disabled/>
			</form>';
		echo "</td><td style='margin-left:30%;'><h1>Bearbeiten</h1>";
		//Studiensemester bearbeiten enabled
		echo '<form action="'.$_SERVER['PHP_SELF'].'?speichern" method="POST">
				<table style="width:50%;">
					<tr>
						<td>Studiensemester_kurzbz</td><td><input type="text" disabled value="'.$studiensemester_kurzbz.'" maxlength="16"/></td>
					</tr>
					<tr>
						<td>Start</td><td><input type="text" name="start" value="'.$studiensemester->start.'" class="datepicker"/></td>
					</tr>
					<tr>
						<td>Ende</td><td><input type="text" name="ende" value="'.$studiensemester->ende.'" class="datepicker"/></td>
					</tr>
					<tr>
						<td>Bezeichnung</td><td><input type="text" name="bezeichnung" value="'.$studiensemester->bezeichnung.'" /></td>
					</tr>
					<tr>
						<td>Studienjahr_kurzbz</td><td><input type="text" name="studienjahr_kurzbz" value="'.$studiensemester->studienjahr_kurzbz.'" /></td>
					</tr>
					<tr>
						<td>Beschreibung</td><td><textarea name="beschreibung" cols="22">'.$studiensemester->beschreibung.'</textarea></td>
					</tr>
					<tr>
						<td>Onlinebewerbung</td><td><input type="checkbox" name="onlinebewerbung" '.$checked.' value="check"/></td>
					</tr>
			  </table>
			  <input type="hidden" name="studiensemester_kurzbz" value="'.$studiensemester_kurzbz.'"/>
			  <input type="submit" value="speichern"/>
			  <a href="'.$_SERVER['PHP_SELF'].'"><input type="button" value="abbrechen"/></a>
			</form>';
	} else {
		//Neues Studiensemester eintragen enabled
		echo '<form action="'.$_SERVER['PHP_SELF'].'?speichern=neu" method="POST">
				<table style="width:50%;">
					<tr>
						<td>Studiensemester_kurzbz</td><td><input type="text" name="studiensemester_kurzbz" maxlength="16"/></td>
					</tr>
					<tr>
						<td>Start</td><td><input type="text" name="start" class="datepicker"/></td>
					</tr>
					<tr>
						<td>Ende</td><td><input type="text" name="ende" class="datepicker"/></td>
					</tr>
					<tr>
						<td>Bezeichnung</td><td><input type="text" name="bezeichnung"/></td>
					</tr>
					<tr>
						<td>Studienjahr_kurzbz</td><td><input type="text" name="studienjahr_kurzbz"/></td>
					</tr>
					<tr>
						<td>Beschreibung</td><td><textarea name="beschreibung" cols="22"></textarea></td>
					</tr>
					<tr>
						<td>Onlinebewerbung</td><td><input type="checkbox" name="onlinebewerbung" value="check"/></td>
					</tr>
			  </table>
			  <input type="submit" value="neu anlegen"/>
			</form>';
		echo "</td><td style='margin-left:30%;'><h1>Bearbeiten</h1>";
		//Studiensemester bearbeiten disabled
		echo '<form action="'.$_SERVER['PHP_SELF'].'?speichern" method="POST">
				<table style="width:50%;">
					<tr>
						<td>Studiensemester_kurzbz</td><td><input type="text" disabled maxlength="16"/></td>
					</tr>
					<tr>
						<td>Start</td><td><input type="text" name="start" disabled/></td>
					</tr>
					<tr>
						<td>Ende</td><td><input type="text" name="ende" disabled/></td>
					</tr>
					<tr>
						<td>Bezeichnung</td><td><input type="text" name="bezeichnung" disabled /></td>
					</tr>
					<tr>
						<td>Studienjahr_kurzbz</td><td><input type="text" name="studienjahr_kurzbz" disabled /></td>
					</tr>
					<tr>
						<td>Beschreibung</td><td><textarea name="beschreibung" disabled cols="22"></textarea></td>
					</tr>
					<tr>
						<td>Onlinebewerbung</td><td><input type="checkbox" name="onlinebewerbung" disabled/></td>
					</tr>
			  </table>
			  <input type="hidden" name="studiensemester_kurzbz" value="'.$studiensemester_kurzbz.'"/>
			  <input type="submit" value="speichern" disabled/>
			  <input type="button" value="abbrechen" disabled/>
			</form>';
	}
	echo "</td></tr></table>";
	echo '<HR/>';


	//Liste der eingetragenen Studiensemester
	$studiensemester = new Studiensemester();
	$studiensemester->getAll('DESC');

	echo "<table id='t1' class='tablesorter'>
			<thead>
			<tr>
				<th>Studiensemester_kurzbz</th>
				<th>Start</th>
				<th>Ende</th>
				<th>Bezeichnung</th>
				<th>Studienjahr_kurzbz</th>
				<th>Beschreibung</th>
				<th>Onlinebewerbung</th>
				<th></th>
			</tr>
			</thead>
			<tbody>";
	foreach ($studiensemester->studiensemester as $row)
	{
		echo "<tr>
				<td>".$row->studiensemester_kurzbz."</td>
				<td>".$row->start."</td>
				<td>".$row->ende."</td>
				<td>".$row->bezeichnung."</td>
				<td>".$row->studienjahr_kurzbz."</td>
				<td>".$row->beschreibung."</td>
				<td>";if ($row->onlinebewerbung=='t') { echo '<img src="../../skin/images/true.png"/>'; } else { echo '<img src="../../skin/images/false.png"/>'; } echo "</td>
			  	<td><a href=\"".$_SERVER['PHP_SELF'].'?studiensemester_kurzbz='.$row->studiensemester_kurzbz."\">edit</a></td>
			  </tr>";
	}
	echo "</tbody></table>";



	echo "</td></tr></table><br>";
	echo '
			</body>
			</html>';
?>
