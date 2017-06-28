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
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

if (isset($_GET['studiengang_kz']))
	$studiengang_kz=$_GET['studiengang_kz'];
else if(isset($_POST['studiengang_kz']))
	$studiengang_kz = $_POST['studiengang_kz'];
else
	$studiengang_kz='';

if (isset($_GET['sem']))

	$sem=$_GET['sem'];
else
	$sem=null;

if (isset($_GET['ss']))

	$ss=$_GET['ss'];
else
	$ss=null;

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('lehre/gruppe'))
	die('Sie haben keine Berechtigung fuer diese Seite');

?>
<html>
	<head>
		<title>Gruppe-Verwaltung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		
		<?php 
		include('../../include/meta/jquery.php');
		include('../../include/meta/jquery-tablesorter.php');
		?>
		
		<script language="JavaScript" type="text/javascript">
		function conf_del()
		{
			return confirm('Diese Gruppe wirklich löschen?');
		}
		$(document).ready(function()
		{
			$("#t1").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra", "filter", "stickyHeaders"],
				headers: { 12: { filter: false,  sorter: false }}
			});
		});
		</script>
		<style>
		.tablesorter-default input.tablesorter-filter
		{
			padding: 0 4px;
		}
		</style>
	</head>
<body>
	<H2>Gruppen - Verwaltung</H2>
<?php

if (isset($_POST['newFrm']) || isset($_GET['newFrm']))
{
	doEdit(null,true);
}
else if (isset($_GET['edit']))
{
	doEdit(addslashes($_GET['kurzbz']),false);
}
else if (isset($_POST['type']) && $_POST['type']=='save')
{
	printDropDown();
	doSave();
	getUebersicht();
}
else if (isset($_GET['type']) && $_GET['type']=='delete')
{
	printDropDown();
	$e=new gruppe();
	if(!$e->delete($_GET['einheit_id']))
		echo $e->errormsg;
	getUebersicht();
}
else
{
	printDropDown();
	getUebersicht();
}

function printDropDown()
{
	global $rechte, $studiengang_kz;
	//Studiengang Drop Down anzeigen
	$stud = new studiengang();
	if(!$stud->getAll('typ, kurzbz'))
		echo 'Fehler beim Laden der Studiengaenge:'.$stud->errormsg;

	// Studiengang AuswahlFilter
	echo '<form accept-charset="UTF-8" name="frm_studiengang" action="'.$_SERVER['PHP_SELF'].'" method="GET">';
	echo 'Studiengang: <SELECT name="studiengang_kz" onchange="document.frm_studiengang.submit()">';

	foreach($stud->result as $row)
	{
		if($rechte->isBerechtigt('lehre/gruppe', $row->oe_kurzbz, 'suid'))
		{
			if($studiengang_kz=='')
				$studiengang_kz=$row->studiengang_kz;

			echo '<OPTION value="'.$row->studiengang_kz.'"'.($studiengang_kz==$row->studiengang_kz?'selected':'').'>'.$row->kuerzel.' - '.$row->bezeichnung.'</OPTION>';
		}
	}

	echo '</SELECT><input type="submit" value="Anzeigen" />';
	echo '</form>';
}
function doSave()
{
	$e = new gruppe();

	if ($_POST['new']=='true')
	{
		$e->new = true;
		$e->gruppe_kurzbz=$_POST['kurzbz'];
		$e->insertamum = date('Y-m-d H:i:s');
		$e->insertvon = get_uid();
	}
	else
	{
		$e->load($_POST['kurzbz']);
		$e->new=false;
	}

	$e->updateamum = date('Y-m-d H:i:s');
	$e->updatevon = get_uid();
	$e->bezeichnung=$_POST['bezeichnung'];
	$e->beschreibung=$_POST['beschreibung'];
	$e->studiengang_kz=$_POST['studiengang_kz'];
	$e->semester=$_POST['semester'];
	$e->mailgrp=isset($_POST['mailgrp']);
	$e->sichtbar=isset($_POST['sichtbar']);
	$e->generiert=isset($_POST['generiert']);
	$e->aktiv=isset($_POST['aktiv']);
	$e->gesperrt = isset($_POST['gesperrt']);
	$e->zutrittssystem = isset($_POST['zutrittssystem']);
	$e->aufnahmegruppe = isset($_POST['aufnahmegruppe']);
	$e->sort=$_POST['sort'];
	$e->content_visible=isset($_POST['content_visible']);

	if(!$e->save())
		echo $e->errormsg;
}



function doEdit($kurzbz,$new=false)
{
	if (!$new)
		$e=new gruppe($kurzbz);
	else
		$e = new gruppe();
	?>
	<form name="gruppe" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
			<p><b>Gruppe <?php echo ($new?'hinzufügen':'bearbeiten'); ?></b>:
				<table border="0">
				<tr>
					<td><i>Kurzbezeichnung</i></td>
					<td>
						<input type="text" name="kurzbz" size="16" maxlength="32" value="<?php echo $e->gruppe_kurzbz; ?>">
				</td>
			</tr>
				<tr>
					<td><i>Bezeichnung</i></td>
					<td>
					<input type="text" name="bezeichnung" size="20" maxlength="32" value="<?php echo $e->bezeichnung; ?>">
				</td>
			</tr>
			<tr>
					<td><i>Beschreibung</i></td>
					<td>
					<input type="text" name="beschreibung" size="20" maxlength="128" value="<?php echo $e->beschreibung; ?>">
				</td>
			</tr>
			<tr>
				<td><i>Studiengang</i></td>
				<td>
					<SELECT name="studiengang_kz">
							<option value="-1">- auswählen -</option>
						<?php
							// Auswahl des Studiengangs
							$stg=new studiengang();
							$stg->getAll();
							foreach($stg->result as $studiengang)
							{
								echo "<option value=\"$studiengang->studiengang_kz\" ";
								if ($studiengang->studiengang_kz==$e->studiengang_kz)
								echo "selected";
								echo " >$studiengang->kuerzel ($studiengang->bezeichnung)</option>\n";
							}
						?>
					</SELECT>
				</td>
			</tr>
			<tr><td><i>Semester</i></td><td><input type="text" name="semester" size="2" maxlength="1" value="<?php echo $e->semester ?>"></td></tr>
			<tr><td><i>Mailgrp</i></td><td><input type='checkbox' name='mailgrp' <?php echo ($e->mailgrp?'checked':'');?>>
			<tr><td><i>Sichtbar</i></td><td><input type='checkbox' name='sichtbar' <?php echo ($e->sichtbar?'checked':'');?>>
			<tr><td><i>Generiert</i></td><td><input type='checkbox' name='generiert' <?php echo ($e->generiert?'checked':'');?>>
			<tr><td><i>Aktiv</i></td><td><input type='checkbox' name='aktiv' <?php echo ($e->aktiv?'checked':'');?>>
			<tr><td><i>ContentVisible</i></td><td><input type='checkbox' name='content_visible' <?php echo ($e->content_visible?'checked':'');?>>
			<tr><td><i>Gesperrt</i></td><td><input type='checkbox' name='gesperrt' <?php echo ($e->gesperrt?'checked':'');?>>
			<tr><td><i>Zutrittssystem</i></td><td><input type='checkbox' name='zutrittssystem' <?php echo ($e->zutrittssystem?'checked':'');?>>
			<tr><td><i>Aufnahmegruppe</i></td><td><input type='checkbox' name='aufnahmegruppe' <?php echo ($e->aufnahmegruppe?'checked':'');?>>
			<tr>
				<td><i>Sort</i></td><td><input type='text' name='sort' maxlength="4" value="<?php echo $e->sort;?>">
				</td>
			</tr>
		</table>
		<input type="hidden" name="pk" value="<?php echo $e->gruppe_kurzbz ?>" />
		<input type="hidden" name="new" value="<?php echo ($new?'true':'false') ?>" />
		<input type="hidden" name="type" value="save">
		<input type="submit" name="save" value="Speichern">
		</p>
		<hr>
</form>
<?php
}

function getUebersicht()
{
	global $studiengang_kz,$semester;
	if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$gruppe=new gruppe();
	// Array mit allen Einheiten holen
	$gruppe->getgruppe($studiengang_kz,$semester);

	echo '<h3>&Uuml;bersicht</h3>';

	echo "<table id='t1' class='tablesorter'>";
	echo "<thead>
			<tr class='liste'>
				<th>Kurzbz.</th>
				<th>Bezeichnung</th>
				<th>Beschreibung</th>
				<!--<th>Stg.</th>-->
				<th>Sem.</th>
				<th data-placeholder='t or f'>Mailgrp</th>
				<th data-placeholder='t or f'>Sichtbar</th>
				<th data-placeholder='t or f'>Generiert</th>
				<th data-placeholder='t or f'>Aktiv</th>
				<th data-placeholder='t or f'>ContentVisible</th>
				<th data-placeholder='t or f'>Gesperrt</th>
				<th data-placeholder='t or f'>Zutrittssystem</th>
				<th data-placeholder='t or f'>Aufnahmegruppe</th>
				<th colspan=\"3\">Aktion</th>
			</tr>
			</thead><tbody>";

	$i=0;
	$stg = new studiengang();
	$stg->getAll(null, false);

	foreach ($gruppe->result as $e)
	{
		echo '<tr>';

		echo "<td>$e->gruppe_kurzbz </td>";
		echo "<td>$e->bezeichnung </td>";
		echo "<td>$e->beschreibung </td>";
		//echo "<td>".$stg->kuerzel_arr[$e->studiengang_kz]."</td>";
		echo "<td>$e->semester </td>";
		echo "<td><img title='Mailgrp' height='16px' src='../../skin/images/".($e->mailgrp?"true.png":"false.png")."' alt='".($e->mailgrp?"true.png":"false.png")."'></td>";
		echo "<td><img title='Sichtbar' height='16px' src='../../skin/images/".($e->sichtbar?"true.png":"false.png")."' alt='".($e->sichtbar?"true.png":"false.png")."'></td>";
		echo "<td><img title='Generiert' height='16px' src='../../skin/images/".($e->generiert?"true.png":"false.png")."' alt='".($e->generiert?"true.png":"false.png")."'></td>";
		echo "<td><img title='Aktiv' height='16px' src='../../skin/images/".($e->aktiv?"true.png":"false.png")."' alt='".($e->aktiv?"true.png":"false.png")."'></td>";
		echo "<td><img title='ContentVisible' height='16px' src='../../skin/images/".($e->content_visible?"true.png":"false.png")."' alt='".($e->content_visible?"true.png":"false.png")."'></td>";
		echo "<td><img title='Gesperrt' height='16px' src='../../skin/images/".($e->gesperrt?"true.png":"false.png")."' alt='".($e->gesperrt?"true.png":"false.png")."'></td>";
		echo "<td><img title='Zutrittssystem' height='16px' src='../../skin/images/".($e->zutrittssystem?"true.png":"false.png")."' alt='".($e->zutrittssystem?"true.png":"false.png")."'></td>";
		echo "<td><img title='Aufnahmegruppe' height='16px' src='../../skin/images/".($e->aufnahmegruppe?"true.png":"false.png")."' alt='".($e->aufnahmegruppe?"true.png":"false.png")."'></td>";
		// src="../../skin/images/'.($row->projektarbeit=='t'?'true.png':'false.png').'"
		//echo "<td>".$gruppe->countStudenten($e->gruppe_kurzbz)."</td>"; Auskommentiert, da sonst die Ladezeit der Seite zu lange ist
		echo "<td style='padding-right: 5px'><a href='einheit_det.php?kurzbz=$e->gruppe_kurzbz'>Details</a></td>";
		echo "<td style='padding-right: 5px'><a href=\"einheit_menu.php?edit=1&kurzbz=$e->gruppe_kurzbz\">Edit</a></td>";
		echo "<td><a href=\"einheit_menu.php?einheit_id=$e->gruppe_kurzbz&studiengang_kz=$e->studiengang_kz&type=delete\" onclick='return conf_del()'>Delete</a></td>";
		echo "</tr>\n";
	}

	echo '</tbody></table>';
}

?>
</body>
</html>