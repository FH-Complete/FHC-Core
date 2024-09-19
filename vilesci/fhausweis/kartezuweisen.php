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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
/**
 * GUI zum Zuweisen einer Zutrittskarte zu einer Person
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<title>Karte zuweisen</title>
	<script type="text/javascript">
	$(document).ready(function()
	{
		$("#myTable").tablesorter(
		{
			sortList: [[2,0]],
			widgets: ["zebra"]
		});
	});
</script>

</head>
<body>
<h2>Zutrittskarte - Zuweisen der Karte</h2>';

function printWarning()
{
	echo '<div style="color:red; font-style:bold">
		ACHTUNG - Es wurde eine große Datenmenge geschickt.<br>
		Daten wurden eventuell nicht vollständig gespeichert.<br>
		Bitte wähle einzelne Studiengänge aus um die Daten einzutragen.<br>
		</div>';

}
if(!$rechte->isBerechtigt('basis/fhausweis', 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

$db = new basis_db();

$users = (isset($_REQUEST['users'])?$_REQUEST['users']:'');
if(isset($_GET['data']))
{
	$users = explode(';',$_GET['data']);
}
if(count($_POST)>700)
{
	printWarning();
}
if(isset($_POST['save']) && $users!='')
{
	//var_dump($users);
	foreach($users as $user)
	{
		$benutzer = new benutzer();
		if($benutzer->load($user))
		{
			$nummer1 = $_POST['nummer1_'.$user];
			$nummer2 = $_POST['nummer2_'.$user];

			if($nummer1=='' && $nummer2=='')
			{
				echo '<span class="error">Ueberspringe '.$db->convert_html_chars($user).' - keine Nummer eingetragen</span>';
				continue;
			}
			//Karte anlegen
			$bm = new betriebsmittel();
			$bm->betriebsmitteltyp = 'Zutrittskarte';
			if ($nummer1 != '')
				$bm->nummer = $nummer1;
			$bm->nummer2 = $nummer2;
			$bm->insertamum = date('Y-m-d H:i:s');
			$bm->insertvon = $uid;
			$bm->updateamum = date('Y-m-d H:i:s');
			$bm->updatevon = $uid;
			$bm->reservieren=false;

			if($bm->save(true))
			{

				//Zuordnung zu Benutzer anlegen
				$bmp = new betriebsmittelperson();
				$bmp->betriebsmittel_id = $bm->betriebsmittel_id;
				$bmp->person_id = $benutzer->person_id;
				$bmp->insertamum = date('Y-m-d H:i:s');
				$bmp->insertvon = $uid;
				if(isset($_POST['ausgegeben']))
					$bmp->ausgegebenam = date('Y-m-d');
				$bmp->uid = $user;
				if($bmp->save(true))
				{
					echo '<span class="ok">+</span>';
				}
				else
				{
					echo '<br><span class="error">'.$user.' - '.$bmp->errormsg.'</span>';
				}
			}
			else
			{
				echo '<br><span class="error">'.$user.' - '.$bm->errormsg.'</span>';
			}
		}
		else
		{
			echo '<br><span class="error">'.$user.' - '.$benutzer->errormsg.'</span>';
		}
	}
}
if($users!='')
{
	if(count($users)>500)
	{
		printWarning();
	}
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
	Karte gleich als Ausgegeben eintragen: <input type="checkbox" name="ausgegeben"/>
	<table id="myTable" class="tablesorter">
	<thead>
	<tr>
		<th>UID</th>
		<th>Vorname</th>
		<th>Nachname</th>
		<th>Nummer 1</th>
		<th>Nummer 2</th>
	</tr>
	</thead>
	<tbody>';
	foreach($users as $user)
	{
		$benutzer = new benutzer();
		if($benutzer->load($user))
		{
			echo '<tr>';
			echo '<td><input type="hidden" name="users[]" value="'.$db->convert_html_chars($user).'">'.$db->convert_html_chars($user).'</td>';
			echo '<td>'.$db->convert_html_chars($benutzer->vorname).'</td>';
			echo '<td>'.$db->convert_html_chars($benutzer->nachname).'</td>';
			echo '<td><input type="text" name="nummer1_'.$db->convert_html_chars($user).'"></td>';
			echo '<td><input type="text" name="nummer2_'.$db->convert_html_chars($user).'"></td>';
			echo '</tr>';
		}
		else
		{
			echo '<tr><td colspan="5">'.$db->convert_html_chars($user).' - Unbekannte UID</td></tr>';
		}
	}
	echo '</tbody></table>';
	echo '<input type="submit" name="save" value="Speichern">
	</form>';
}
else
{
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
	UID: <input type="text" name="users[]" value="">';
	echo '<input type="submit" name="submit" value="Zuweisung">
	</form>';
}
echo '</body>
</html>';
?>
