<?php
/*
 * Copyright 2016 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 *
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/gsprogramm.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
$db = new basis_db();

if(!$rechte->isBerechtigt('inout/uebersicht', null, 'suid'))
	die($rechte->errormsg);
if(isset($_GET['action']))
	$action = $_GET['action'];
else
	$action = '';

if($action=='save')
{
	if(isset($_POST['gsprogramm_id']))
	{
		$gsprogramm = new gsprogramm();
		if(isset($_POST['gsprogramm_id']) && $_POST['gsprogramm_id']!='')
		{
			if(!$gsprogramm->load($_POST['gsprogramm_id']))
				die($gsprogramm->errormsg);
		}

		$gsprogramm->bezeichnung = $_POST['bezeichnung'];
		$gsprogramm->gsprogrammtyp_kurzbz = $_POST['gsprogrammtyp_kurzbz'];
		$gsprogramm->programm_code = $_POST['programm_code'];
		$gsprogramm->studienkennung_uni = $_POST['studienkennung_uni'];
		if($gsprogramm->save())
			echo '<span class="ok">Daten erfolgreich gespeichert</span>';
		else
			echo '<span class="error">'.$gsprogramm->errormsg.'</span>';
	}
}

if($action=='delete')
{
	$gsprogramm = new gsprogramm();
	if($gsprogramm->delete($_GET['gsprogramm_id']))
		echo '<span class="ok">Erfolgreich gelöscht</span>';
	else
		echo '<span class="error">'.$gsprogramm->errormsg.'</span>';
}

$gsprogramm = new gsprogramm();
$gsprogramm->getTypen();
$typ_arr = array();
foreach($gsprogramm->result as $row)
	$typ_arr[$row->gsprogrammtyp_kurzbz]=$row->bezeichnung;

echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/fhcomplete.css" rel="stylesheet" type="text/css">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>

	<script type="text/javascript">
		$(document).ready(function()
		{
			$("#t1").tablesorter(
		{
				sortList: [[2,0]],
				widgets: ["zebra"],
				headers: {0:{sorter:false},1:{sorter:false}}
			});
		});

		function confdel()
		{
			return confirm("Wollen Sie diesen Eintrag wirklich löschen?");
		}
	</script>
	<title>Gemeinsames Studienprogramm</title>
</head>
<body>';

echo '<h1>Gemeinsames Studienprogramm</h1>';

$gsprogramm = new gsprogramm();
$gsprogramm->getAll();

echo '
	<form action="'.$_SERVER['PHP_SELF'].'">
	<input type="submit" value="Neuen Eintrag hinzufügen" />
	</form>
	<table id="t1" class="tablesorter" style="width:auto">
	<thead>
		<th></th>
		<th></th>
		<th>Bezeichnung</th>
		<th>Programmcode</th>
		<th>Typ</th>
		<th>Studienkennung Uni</th>
	</thead>
	<tbody>
		';
	foreach($gsprogramm->result as $row)
	{
		echo '<tr>
				<td>
					<a href="'.$_SERVER['PHP_SELF'].'?action=edit&gsprogramm_id='.$row->gsprogramm_id.'">
						<img src="../../skin/images/edit.png" title="Bearbeiten" />
					</a>
				</td>
				<td>
					<a href="'.$_SERVER['PHP_SELF'].'?action=delete&gsprogramm_id='.$row->gsprogramm_id.'" onclick="return confdel()">
						<img src="../../skin/images/cross.png" title="Löschen" />
					</a>
				</td>
				<td>'.$row->bezeichnung.'</td>
				<td>'.$row->programm_code.'</td>
				<td>'.(isset($typ_arr[$row->gsprogrammtyp_kurzbz])?$typ_arr[$row->gsprogrammtyp_kurzbz]:$row->gsprogrammtyp_kurzbz).'</td>
				<td>'.$row->studienkennung_uni.'</td>
			</tr>';
	}

	echo '
	</tbody>
	</table>';


	if($action=='edit')
	{
		$gsprogramm = new gsprogramm();
		if(!$gsprogramm->load($_GET['gsprogramm_id']))
			die($gsprogramm->errormsg);
	}
	else
	{
		$gsprogramm = new gsprogramm();
	}

	echo '
	<form action="'.$_SERVER['PHP_SELF'].'?action=save" method="post">
	<table>
		<tr>
			<td>ProgrammCode</td>
			<td>
				<input typ="text" id="programm_code" name="programm_code" maxlength="8" size="8" value="'.$gsprogramm->programm_code.'"/>
				<input type="hidden" id="gsprogramm_id" name="gsprogramm_id" value="'.$gsprogramm->gsprogramm_id.'" />
			</td>
		</tr>
		<tr>
			<td>Bezeichnung</td>
			<td>
				<input type="text" id="bezeichnung" name="bezeichnung" size="50" maxlength="128" value="'.$db->convert_html_chars($gsprogramm->bezeichnung).'">
			</td>
		</tr>
		<tr>
			<td>Typ</td>
			<td>
				<select name="gsprogrammtyp_kurzbz">';

	foreach($typ_arr as $key=>$val)
	{
		if($gsprogramm->gsprogrammtyp_kurzbz == $key)
			$selected = 'selected';
		else
			$selected = '';
		echo '<option value="'.$db->convert_html_chars($key).'" '.$selected.'>'.$db->convert_html_chars($val).'</option>';
	}
	echo '
				</select>
			</td>
		</tr>
		<tr>
			<td>StudienkennungUni</td>
			<td>
				<input type="text" id="studienkennung_uni" name="studienkennung_uni" size="50" minlength="6" maxlength="14"
				value="'.$db->convert_html_chars($gsprogramm->studienkennung_uni).'">
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="save" value="Speichern"></td>
		</tr>
	</table>
	</form><br><br><br><br><br><br><br><br><br><br><br><br>';
echo '
</body>
</html>';

?>
