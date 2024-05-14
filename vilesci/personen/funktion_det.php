<?php

//var_dump($_POST);

/*
 * Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger < christian.paminger@technikum-wien.at >
 * Andreas Oesterreicher < andreas.oesterreicher@technikum-wien.at >
 * Rudolf Hangl < rudolf.hangl@technikum-wien.at >
 * Gerald Simane-Sequens < gerald.simane-sequens@technikum-wien.at >
 */

/**
 * *****************************************************************************
 * File: funktion_det.php
 * Descr: Hier werden Personen aufgelistet, die zur in funktion.php ausgewählten
 * Gruppe gehören.
 * Es können Datensätze hinzugefügt und gelöscht werden.
 * Dazu wird dieses File rekursiv aufgerufen.
 * Erstellt am: 25.05.2003 von Christian Paminger, Werner Masik
 * ******************************************************************************
 */
require_once ('../../config/vilesci.config.inc.php');
require_once ('../../include/functions.inc.php');
require_once ('../../include/person.class.php');
require_once ('../../include/funktion.class.php');
require_once ('../../include/benutzerfunktion.class.php');
require_once ('../../include/fachbereich.class.php');
require_once ('../../include/benutzerberechtigung.class.php');

// Datenbankverbindung herstellen
if (! $db = new basis_db ())
	die ( 'Es konnte keine Verbindung zum Server aufgebaut werden.' );

$user = get_uid ();

$rechte = new benutzerberechtigung ();
$rechte->getBerechtigungen ( $user );

if (! $rechte->isBerechtigt ( 'mitarbeiter', null, 'suid' ))
	die ( $rechte->errormsg );

$type = '';
if (isset ( $_POST ['type'] ))
	$type = $_POST ['type'];

if (isset ( $_GET ['type'] ))
	$type = $_GET ['type'];

if (isset ( $_GET ['kurzbz'] ))
	$funktion_kurzbz = $_GET ['kurzbz'];

if (isset ( $_GET ['bezeichnung'] ))
	$bezeichnung = $_GET ['bezeichnung'];
else
	$bezeichnung = '';

if (isset ( $_GET ['wochenstunden'] ))
	$wochenstunden = $_GET ['wochenstunden'];
else
	$wochenstunden = '';

if (isset ( $_GET ['datumvon'] ))
	$datumvon = $_GET ['datumvon'];
else
	$datumvon = '';

if (isset ( $_GET ['datumbis'] ))
	$datumbis = $_GET ['datumbis'];
else
	$datumbis = '';

if (isset ( $_POST ['oe_kurzbz_filter'] ))
	$oe_kurzbz_filter = $_POST ['oe_kurzbz_filter'];
else
	$oe_kurzbz_filter = '-1';

if (isset ( $_POST ['semester_filter'] ))
	$semester_filter = $_POST ['semester_filter'];
else
	$semester_filter = $_POST['semester_filter'] = '-1' ;

$bn_funktion_id = isset($_GET ['bn_funktion_id'])?$_GET ['bn_funktion_id']:'-1';
//var_dump($_POST);



	// Neue Funktionszuweisung speichern
if ($type == 'new' || $type == 'editsave') {
	// Einfügen in die Datenbank

	$funktion = new benutzerfunktion ();
	$funktion->uid = $_POST ['uid'];
	$funktion->funktion_kurzbz = $_POST ['kurzbz'];
	if (isset ( $_POST ['oe_kurzbz'] ) && $_POST ['oe_kurzbz'] != - 1) {
		$funktion->oe_kurzbz = $_POST ['oe_kurzbz'];

		if (isset ( $_POST ['fb_kurzbz'] ) && $_POST ['fb_kurzbz'] != - 1) {
			$funktion->fachbereich_kurzbz = $_POST ['fb_kurzbz'];
		} else {
			$funktion->fachbereich_kurzbz = null;
		}

		$funktion->semester = (isset ( $_POST ['semester'] ) ? $_POST ['semester'] : '');
		$funktion->datum_von = $_POST ['datumvon'];
		$funktion->datum_bis = $_POST ['datumbis'];

		$funktion->bezeichnung = $_POST ['bezeichnung'];
		$funktion->wochenstunden = $_POST ['wochenstunden'];


		if ($type == 'editsave') {
			$funktion->new = false;
			$funktion->benutzerfunktion_id = $_POST ['bn_funktion_id'];
			$funktion->updateamum = date ( 'Y-m-d H:i:s' );
			$funktion->updatevon = $user;
		} else {
			$funktion->new = true;
			$funktion->updateamum = date ( 'Y-m-d H:i:s' );
			$funktion->updatevon = $user;
			$funktion->insertamum = date ( 'Y-m-d H:i:s' );
			$funktion->insertvon = $user;
		}

		if (! $funktion->save ()) {
			echo "Fehler: " . $funktion->errormsg;
		}
	} else
		echo '<font color="#FC5454"> <b> Organisationseinheit muss angegeben werden </b> </font>';
}

// Mehrere Eintraege updaten
if ($type == 'editsavemultiple')
{
	$uids = explode(", ", $_POST['uids']);
	$bn_funktion_ids = explode(", ", $_POST['bn_funktion_ids']);

	for ($i=0; $i<count($uids); $i++) {
		$funktion = new benutzerfunktion ();
		$funktion->uid = $uids[$i];
		$funktion->funktion_kurzbz = $_POST ['kurzbz'];
		if (isset ( $_POST ['oe_kurzbz'] ) && $_POST ['oe_kurzbz'] != - 1) {
			$funktion->oe_kurzbz = $_POST ['oe_kurzbz'];

			if (isset ( $_POST ['fb_kurzbz'] ) && $_POST ['fb_kurzbz'] != - 1) {
				$funktion->fachbereich_kurzbz = $_POST ['fb_kurzbz'];
			} else {
				$funktion->fachbereich_kurzbz = null;
			}

			$funktion->semester = (isset ( $_POST ['semester'] ) ? $_POST ['semester'] : '');
			$funktion->datum_von = $_POST ['datumvon'];
			$funktion->datum_bis = $_POST ['datumbis'];

			$funktion->bezeichnung = $_POST ['bezeichnung'];
			$funktion->wochenstunden = $_POST ['wochenstunden'];

			$funktion->new = false;
			$funktion->benutzerfunktion_id = $bn_funktion_ids[$i];
			$funktion->updateamum = date ( 'Y-m-d H:i:s' );
			$funktion->updatevon = $user;

			//var_dump($funktion);

			if (! $funktion->save ()) {
				echo "Fehler: " . $funktion->errormsg;
			}
		} else
			echo '<font color="#FC5454"> <b> Organisationseinheit muss angegeben werden </b> </font>';
	}
}

// Eine Funktionszuweisung loeschen
if ($type == 'delete')
{
	$funktion = new benutzerfunktion ();
	$bn_funktion_id = $_GET ['bn_funktion_id'];
	if (! is_numeric ( $bn_funktion_id ))
	{
		echo "Benutzer_funktion_id ist keine Zahl";
	}
	else
	{
		if (! $funktion->delete ( $bn_funktion_id ))
		{
			echo "Fehler: " . $funktion->errormsg;
		}
	}
}

// Daten für Personenauswahl
$sql_query = "SELECT nachname, vorname, uid FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) ORDER BY upper(nachname), vorname, uid";
$result_person = $db->db_query ( $sql_query );
if (! $result_person)
	die ( $db->db_last_error () );
// Daten für Organisationseinheiten
$sql_query = "SELECT oe_kurzbz, organisationseinheittyp_kurzbz as kurzbz, bezeichnung FROM public.tbl_organisationseinheit /*WHERE organisationseinheittyp_kurzbz= 'Institut'*/ ORDER BY kurzbz, bezeichnung";
$result_oe = $db->db_query ( $sql_query );
if (! $result_oe)
	die ( $db->db_last_error () );

	// Instanz von Funktion-Klasse erzeugen
$funktion = new funktion ();
$kurzbz = (isset ( $_POST ['kurzbz'] ) ? $_POST ['kurzbz'] : $_GET ['kurzbz']);
//$kurzbz2 = $kurzbz;
if (! $funktion->load ( $kurzbz )) {
	echo "Fehler: " . $funktion->errormsg;
	exit ();
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Funktion Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">

<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<?php
	include("../../include/meta/jquery-tablesorter.php");
	?>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>

<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script src="../../vendor/rmariuzzo/jquery-checkboxes/dist/jquery.checkboxes-1.0.7.min.js" type="text/javascript"></script>

<script language="Javascript">
	$(document).ready(function() {
		$("#ma_name").autocomplete({
			source: "../../cis/private/tools/zeitaufzeichnung_autocomplete.php?autocomplete=kunde",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].uid;
					ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#ma_name").val(ui.item.uid);
			}
		});
		$("#t1").tablesorter({
			sortList: [[0,0]],
			widgets: ["zebra", "filter", "stickyHeaders"],
			headers: {9:{sorter:false, filter: false},10:{sorter:false, filter: false}}
		});
		$('#t1').checkboxes('range', true);
		$( ".datepicker_datumvon" ).datepicker({
			 changeMonth: true,
			 changeYear: true,
			 //dateFormat: "dd.mm.yy", modified 20160708
			 dateFormat: "yy-mm-dd",
			 //minDate: 0,
		});
		$( ".datepicker_datumbis" ).datepicker({
			 changeMonth: true,
			 changeYear: true,
			 //dateFormat: "dd.mm.yy", modified 20160708
			 dateFormat: "yy-mm-dd",
		});
	});
</script>
</head>
<body>
	<form id="tableform" action="<?php echo "".$_SERVER['PHP_SELF']."?kurzbz=$kurzbz&bn_funktion_id=$bn_funktion_id&type=edit&multiple=true"; ?>" method="post"><input type="hidden" name="editordelete" class="editordelete" value="" /></form>
	<H2>Funktion: <?php echo $funktion->beschreibung?></H2>
	<br />

	<?php
	// variable fuer Eingabe Bezeichnung
	$beschreibung = $funktion->beschreibung;

// Filter Felder
echo '<font color="#0086CC" style="line-height:300%"> Filter </font>';
	echo '<br>';
	echo '
	<table>
 	';
//;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

//echo ' <form enctype="multipart/form-data" action="funktion_det.php" method="post">';
//echo ' <form enctype="multipart/form-data" method="post">';
echo ' <form method="post" action="'.$_SERVER['PHP_SELF'].'?kurzbz='.$kurzbz.'&bn_funktion_id='.$bn_funktion_id.'" id="filter_form">';
//echo ' <form action="funktion_det.php" > ';
echo '
	<tr>
	<td>Organisationseinheit: </td>
	<td>
	';
//echo '<input type="text" name="organisationseinheit" value="">';
echo '
<SELECT name="oe_kurzbz_filter" id="oe_kurzbz_filter?kurzbz=hsv" form="filter_form">
	<option value="-1">- auswählen -</option>';
// Auswahl der Organisationseinheit

while ( $row = $db->db_fetch_object ( $result_oe ) )
{
	$rows[] = $row;
	echo "<option value=\"$row->oe_kurzbz\" ";
	if (isset ( $_POST ['oe_kurzbz_filter'] ) && $row->oe_kurzbz == $_POST ['oe_kurzbz_filter'])
		echo 'selected ';
	echo ">$row->kurzbz $row->bezeichnung</option>";
}
echo '</SELECT>';

//;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
	echo '
		<tr>
		<td>Semester: </td>
		<td>
		';
	//var_dump($_POST);
?>
	<select id="semester_filter" name="semester_filter">
		<option selected="selected" value="-1">- auswählen -</option>
		if (isset($_POST['semester_filter'])) {
    <option value="1" <?= $_POST['semester_filter'] == '1' ? 'selected' : '' ?>>1</option>
		<option value="2" <?= $_POST['semester_filter'] == '2' ? 'selected' : '' ?>>2</option>
		<option value="3" <?= $_POST['semester_filter'] == '3' ? 'selected' : '' ?>>3</option>
		<option value="4" <?= $_POST['semester_filter'] == '4' ? 'selected' : '' ?>>4</option>
		<option value="5" <?= $_POST['semester_filter'] == '5' ? 'selected' : '' ?>>5</option>
		<option value="6" <?= $_POST['semester_filter'] == '6' ? 'selected' : '' ?>>6</option>
		<option value="7" <?= $_POST['semester_filter'] == '7' ? 'selected' : '' ?>>7</option>
		<option value="8" <?= $_POST['semester_filter'] == '8' ? 'selected' : '' ?>>8</option>
		<option value="9" <?= $_POST['semester_filter'] == '9' ? 'selected' : '' ?>>9</option>
		<option value="10" <?= $_POST['semester_filter'] == '10' ? 'selected' : '' ?>>10</option>
	}
</select>

<?php

?>

</td></tr>

	<script type="text/javascript">


	function doFilter() {
		var oe = document.getElementById("oe_kurzbz_filter");
		var oe_d = oe.options[oe.selectedIndex].value;

		var s = document.getElementById("semester_filter");
		var s_d = s.options[s.selectedIndex].value;

		if (oe_d==-1 && s_d==-1) {
			//reload the same page
			// (do nothing)
			alert("nothing");
		}

		//alert(oe_d);
		//alert(s_d);

	}
	</script>

	<?php
	echo '</table> ';
	//echo '<form onsubmit="doFilter();" >';
	//echo '<form action="funktion_det.php" method="post" name="persfunk_neu" id="persfunk_neu">';

	//echo '<form onsubmit="setTimeout(function () { window.location.reload(); }, 10)" >';

//echo '<script type="text/javascript"> alert(' . $kurzbz2 . '); </script>';
//	echo '<script type="text/javascript"> alert(' . $p_oe_kurzbz_filter . '); </script>';
//	echo '<script type="text/javascript"> alert(' . $p_semester_filter . '); </script>';

	echo '<input type="submit" name="SubmitFilter" form="filter_form" value="' . ('Anzeigen') . '">';
	echo '</form>';
?>


<?php


	//-------------------------------------------------------------------------------------------------------------------------------->
	//Hinzufuegen und Edit Felder
	echo '<hr>';
	echo '<br>';
	echo '<font color="#0086CC"> Hinzufügen / Bearbeiten </font>';
	echo '<form action="funktion_det.php" method="post" name="persfunk_neu" id="persfunk_neu">
	<p>';

	if ($type == 'edit') {
		if (isset($_GET['multiple']) && $_GET['multiple']=='true')
			echo '<INPUT type="hidden" name="type" value="editsavemultiple">';
		else
			echo '<INPUT type="hidden" name="type" value="editsave">';
		echo '<INPUT type="hidden" name="bn_funktion_id" value="' . $_GET ['bn_funktion_id'] . '">';
	} else
		echo '<INPUT type="hidden" name="type" value="new">';

	echo '
    <INPUT type="hidden" name="kurzbz" value="' . $kurzbz . '">
    <table>
	<tr>
    	<td>Lektor: </td>
    	<td> ';

	// wenn Aktion "Edit" dann kann UID nicht veraendert werden
	// -> wird nur angezeigt
	if ($type == 'edit') {
		if (isset($_GET['multiple']) && $_GET['multiple']=='true') {
			if (isset($_POST['editordelete']) && $_POST['editordelete']=='edit') {
				if (isset($_POST['checkAktion'])) {
					$checkAktion = $_POST['checkAktion'];
					$names = array();
					$uids = array();
					$bn_funktion_ids = array();
					for ($i=0; $i<count($checkAktion); $i++) {
						$checkAktion[$i] = explode("-", $checkAktion[$i]);
						array_push($names, $checkAktion[$i][0]);
						array_push($uids, $checkAktion[$i][1]);
						array_push($bn_funktion_ids, $checkAktion[$i][2]);
					}
					$names = array_unique($names);

					echo '<form  accept-charset=\"UTF-8\" action=\"\'.$_SERVER[\'PHP_SELF\'].\'\" mehtod=\"GET\">';
					echo '<INPUT type="hidden" id="uids" name="uids" value="'.implode(", ", $uids).'">';
					echo '<INPUT type="hidden" id="bn_funktion_ids" name="bn_funktion_ids" value="'.implode(", ", $bn_funktion_ids).'">';
					echo '<textare disabled>'.implode("<br/>", $names).'</textarea>';
					echo '</form>';

					//var_dump($checkAktion);
					//var_dump($names);
				}
			} elseif (isset($_POST['editordelete']) && $_POST['editordelete']=='delete') {
				// mehrere Funktionen aufeinmal loeschen
				$checkAktion = $_POST['checkAktion'];
				$bn_funktion_ids = array();
				for ($i=0; $i<count($checkAktion); $i++) {
					$checkAktion[$i] = explode("-", $checkAktion[$i]);
					array_push($bn_funktion_ids, $checkAktion[$i][2]);
				}

				foreach ($bn_funktion_ids as $bn_funktion_idd) {
					$funktion = new benutzerfunktion ();
					$bn_funktion_id = $bn_funktion_idd;
					if (! is_numeric ( $bn_funktion_idd )) {
						echo "Benutzer_funktion_id ist keine Zahl";
					} else {
						if (! $funktion->delete ( $bn_funktion_id )) {
							echo "Fehler: " . $funktion->errormsg;
						}
					}
				}
			}
		} else {
			$dis_uid = $_GET ["uid"];
			echo '<form  accept-charset=\"UTF-8\" action=\"\'.$_SERVER[\'PHP_SELF\'].\'\" mehtod=\"GET\">';
			echo '<INPUT type="hidden" id="uid" name="uid" value="uid">';
			echo '<input type="text" STYLE="background-color: #D3D3D3;" id="ma_name" name="uid" value="';
			echo $dis_uid;
			echo '" readonly>';
			echo '</form>';
		}
	} else {
		echo '<form  accept-charset=\"UTF-8\" action=\"\'.$_SERVER[\'PHP_SELF\'].\'\" mehtod=\"GET\">';
		echo '<INPUT type="hidden" id="uid" name="uid" value="uid">';
		echo '<input type="text" id="ma_name" name="uid" value="">';
		echo '</form>';
	}

	echo '<tr>
			<td>Organisationseinheit: </td>
			<td>
		    	<SELECT name="oe_kurzbz">
		      	<option value="-1">- auswählen -</option>';

	// Auswahl der Organisationseinheit
	$num_rows = $db->db_num_rows ( $result_oe );
		foreach($rows as $row){
		echo "<option value=\"$row->oe_kurzbz\" ";
		if (isset ( $_GET ['oe_kurzbz'] ) && ($type == 'edit') && ($row->oe_kurzbz == $_GET ['oe_kurzbz']))
			echo 'selected ';
		echo ">$row->kurzbz $row->bezeichnung</option>";
	}
	echo '</SELECT></td></tr>';

	$funktion = new funktion ();
	// $funktion->load ( $funktion_kurzbz ); //  Notice: Undefined variable: funktion_kurzbz in /var/www/kindlm/fhcomplete/vilesci/personen/funktion_det.php

	$funktion = new funktion();
	if (isset($funktion_kurzbz)) // Prevents notice "Undefined variable: funktion_kurzbz"
	{
		$funktion->load($funktion_kurzbz);
	}

	if($funktion->fachbereich)
	{
		echo '
	    <tr>
	    	<td>Fachbereich:</td>
	    	<td>
			    <SELECT name="fb_kurzbz">
			     <option value="-1">- auswählen -</option>';

		// Auswahl Fachbereich
		$fachbereich = new fachbereich ();
		if ($fachbereich->getAll ()) {
			foreach ( $fachbereich->result as $fb ) {
				echo "<option value=\"$fb->fachbereich_kurzbz\" ";
				if (($type == 'edit') && ($fb->fachbereich_kurzbz == $_GET ['fb_kurzbz']) && isset ( $_GET ['fb_kurzbz'] ))
					echo 'selected ';
				echo ">$fb->fachbereich_kurzbz</option>";
			}
		} else {
			echo "Fehler: " . $fb->errormsg;
		}

		echo '</SELECT></td></tr>';
	}

	if ($funktion->semester) {
		echo '
	    <tr>
	    	<td>Semester:</td>
	    	<td>
			    <SELECT name="semester">
			     <option value="">- auswählen -</option>';

		for($i = 1; $i <= 8; $i ++) {
			echo "<option value=\"$i\" ";
			if ($type == 'edit' && isset ( $_GET ['semester'] ) && ($i == $_GET ['semester']))
				echo 'selected ';
			echo ">$i</option>";
		}

		echo '</SELECT></td></tr>';
	}

	echo '
	<tr>
	<td>Bezeichnung: </td>
	<td>
		';
	if ($type == 'edit') {
	echo '
		<input type="text" name="bezeichnung" value="' . $bezeichnung . '" size="40">
	';
	} else {
	echo '
		<input type="text" name="bezeichnung" value="' .  $beschreibung . $bezeichnung . '" size="40">
	';
	}
	echo '
	</td></tr>
	';

	echo '
	<tr>
	<td>Wochenstunden: </td>
	<td>

	<input type="number" min="0" step="any" name="wochenstunden" value="' . $wochenstunden . '">
	';

	echo '
	</td></tr>
	';

	// datepicker
	echo '<tr><td>Datum Von:</td><td>
	<input class="datepicker_datumvon" type="text" pattern="[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])" name="datumvon" id="datumvon"  onKeyPress="checkDate()" onKeyUp="checkDate()" onpaste="checkDate()" onchange="checkDate()" value="' . $datumvon . '">
	</td></tr>';
	echo '<tr><td>Datum Bis:</td><td>
	<input class="datepicker_datumbis" type="text" pattern="[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])" name="datumbis" id="datumbis" onKeyPress="checkDate()" onKeyUp="checkDate()" onpaste="checkDate()" onchange="checkDate()" value="' . $datumbis . '">
	</td></tr>';

	echo '<tr><td></td> <td>  <font color="#FC5454"> <b> <span id="errorDate"> &nbsp; </span> </b> </font>  </td></tr>';
	echo '</table>';
	echo '<input id="submitButton" type="submit" name="Submit" value="' . (($type == 'edit' && !(isset($_POST['editordelete']) && $_POST['editordelete']=='delete'))? 'Speichern' : 'Hinzuf&uuml;gen') . '" >';
	echo '</p></form>';

	 ?>

	<script type="text/javascript">
	function checkDate()
	{
			var datumvon = document.getElementById("datumvon");
			var datumvonValue = datumvon.value;
			var datumvonDate = new Date(datumvonValue);

			var datumbis = document.getElementById("datumbis");
			var datumbisValue = datumbis.value;
			var datumbisDate = new Date(datumbisValue);

			var errorDateValue = document.getElementById("errorDate");
			if ((datumvonValue > datumbisValue) && (datumvonDate.isValid() && datumbisDate.isValid())) {
				errorDateValue.innerText = "Das Enddatum darf nicht kleiner sein als das Startdatum!";
				document.getElementById('submitButton').disabled = true;
			} else {
				// '&nbsp' -> ist das selbe -> '\xa0';
				errorDateValue.innerText = '\xa0';
				document.getElementById('submitButton').disabled = false;
			}
	}

	Date.prototype.isValid = function () {
    // An invalid date object returns NaN for getTime() and NaN is the only
    // object not strictly equal to itself.
    return this.getTime() === this.getTime();
};

	function toggle(source) {
		  checkboxes = document.getElementsByName('checkAktion[]');
		  for(var i=0, n=checkboxes.length;i<n;i++) {
		    checkboxes[i].checked = source.checked;
		  }
		}

	function setEditOrDelete(s)
	{
		var x = document.getElementsByClassName("editordelete");
		for (var i = 0; i < x.length; i++) {
		    x[i].value = s;
		}
	}

	function checkCheckbox() {
	var arr = $('input:checkbox.file-selection-id').map(function () {
			return this.id;
	}).get();
}
	</script>
	<!--<p align="right"> <b>
	<span style="font-size:14px;padding-right:6px;padding-left:3px;background-color: #D3D3D3;">
	<a onclick="setEditOrDelete('edit'); document.getElementById('tableform').submit();" form="tableform">&nbsp; Edit </a> Auskommentiert, da das mehrfacheditieren noch nicht wie gewuenscht funktioniert. Ziel waere, das zB ein gemeinsames Enddatum gesetzt werden kann, ohne dass sich die anderen Daten aendern -->
	<!--<a onclick="if (confirm ('Alle markierten Eintraege loeschen?')) setEditOrDelete('delete'); document.getElementById('tableform').submit();" form="tableformdelete">Delete</a> Auskommentiert, da unklar ist, ob Funktionen ueberhaupt so einfach geloescht werden sollen und es nicht besser waere, sie aus historischen Gruenden zu behalten
	<input type=checkbox onClick="toggle(this)" />
	</span>
	</b> </p>-->

	<?php

//-------------------------------------------------------------------------------------------------------------------------------->

	echo '<table id="t1" class="tablesorter">';

//echo '<script type="text/javascript"> alert(' . $kurzbz2 . '); </script>';
	//echo '<script type="text/javascript"> alert(' . $p_oe_kurzbz_filter . '); </script>';
	//echo '<script type="text/javascript"> alert(' . $p_semester_filter . '); </script>';

	// Liste der Personen darstellen
	// Personen holen
	if (1<2) {
		$qry = "SELECT
						tbl_organisationseinheit.bezeichnung as oebezeichnung,
						tbl_organisationseinheit.organisationseinheittyp_kurzbz as oetyp,
						tbl_benutzer.uid as uid, tbl_benutzerfunktion.*, tbl_person.vorname, tbl_person.nachname
					FROM
						public.tbl_benutzerfunktion,
						public.tbl_person,
						public.tbl_benutzer,
						public.tbl_organisationseinheit
					WHERE
						funktion_kurzbz=" . $db->db_add_param ( $kurzbz ) . " AND
						tbl_benutzerfunktion.uid=tbl_benutzer.uid AND
						tbl_benutzer.person_id=tbl_person.person_id AND
						tbl_benutzerfunktion.oe_kurzbz=tbl_organisationseinheit.oe_kurzbz";
		if ($oe_kurzbz_filter!='-1')
			$qry .=  " AND tbl_organisationseinheit.oe_kurzbz=".$db->db_add_param($oe_kurzbz_filter);
		if ($semester_filter!='-1')
			$qry .= " AND semester=".$db->db_add_param($semester_filter);
	}

	if (1<0) {
	$qry = "SELECT
					tbl_organisationseinheit.bezeichnung as oebezeichnung,
					tbl_organisationseinheit.organisationseinheittyp_kurzbz as oetyp,
					tbl_benutzer.uid as uid, tbl_benutzerfunktion.*, tbl_person.vorname, tbl_person.nachname
				FROM
					public.tbl_benutzerfunktion,
					public.tbl_person,
					public.tbl_benutzer,
					public.tbl_organisationseinheit

				WHERE
				  funktion_kurzbz=" . $db->db_add_param ( $kurzbz ) . " AND
					tbl_benutzerfunktion.uid=tbl_benutzer.uid AND
					tbl_benutzer.person_id=tbl_person.person_id AND
					tbl_benutzerfunktion.oe_kurzbz=tbl_organisationseinheit.oe_kurzbz
					AND tbl_organisationseinheit.bezeichnung='Haustechnik'";

	}

	if ($result = $db->db_query ( $qry )) {

		echo "<thead>
					<tr class='liste'>
						<th>Name</th>
						<th class='table-sortable:default'>UID</th>
						<th class='table-sortable:default'>Organisationseinheit</th>
						<th class='table-sortable:default'>Institut</th>
						<th class='table-sortable:default'>Semester</th>
 						<th class='table-sortable:default'>Bezeichnung</th>
 						<th class='table-sortable:default'>Wochenstunden</th>
						<th class='table-sortable:default'>DatumVon</th>
						<th class='table-sortable:default'>DatumBis</th>
						<th colspan=\"2\">Aktion</th>
						<th class='table-sortable:off'></th>
 					</tr>
				  </thead>";
		$j = 0;
		echo '<tbody>';
		while ( $row = $db->db_fetch_object ( $result ) ) {
			$j ++;
			echo "<tr>";
			echo "<td>" . $row->nachname . ", " . $row->vorname . "</td>";
			echo "<td>" . $row->uid . "</td>";
			echo "<td>" . $row->oetyp . ' ' . $row->oebezeichnung . "</td>";
			echo "<td>" . $row->fachbereich_kurzbz . "</td>";
			echo "<td>" . $row->semester . "</td>";
			echo "<td>" . $row->bezeichnung . "</td>";
			echo "<td>" . $row->wochenstunden . "</td>";
			echo "<td>" . $row->datum_von . "</td>";
			echo "<td>" . $row->datum_bis . "</td>";

			echo "<td><a href=\"funktion_det.php?type=edit&kurzbz=$kurzbz&uid=" . $row->uid . "&bn_funktion_id=$row->benutzerfunktion_id&fb_kurzbz=$row->fachbereich_kurzbz&oe_kurzbz=$row->oe_kurzbz&semester=$row->semester&bezeichnung=$row->bezeichnung&wochenstunden=$row->wochenstunden&datumvon=$row->datum_von&datumbis=$row->datum_bis\">Edit</a></td>";
			echo "<td><a href=\"funktion_det.php?type=delete&kurzbz=$kurzbz&uid=" . $row->uid . "&bn_funktion_id=$row->benutzerfunktion_id&fb_kurzbz=$row->fachbereich_kurzbz&oe_kurzbz=$row->oe_kurzbz&semester=$row->semester&bezeichnung=$row->bezeichnung&wochenstunden=$row->wochenstunden&datumvon=$row->datum_von&datumbis=$row->datum_bis\" onclick='return confirm(\"Diesen Datensatz loeschen?\")'>Delete</a></td>";
			echo "<td width=\"10\">" . "<input type=\"checkbox\" name=\"checkAktion[]\" value='$row->nachname, $row->vorname-$row->uid-$row->benutzerfunktion_id' form='tableform' id=' c. $j . '>" . "</td>";
			echo "</tr>\n";
		}
		echo '</tbody>';
	} else {
		echo "Fehler: " . $db->db_last_error ();
	}

	// Summe aller Reihen
	echo '
	</table>
	<b>
	<p align="right">
	<span style="font-size:14px;padding-left:6px;background-color: #D3D3D3;">
	<script language="JavaScript">
	var oRows = document.getElementById(\'t1\').getElementsByTagName(\'tr\');
	var iRowCount = oRows.length;
	iRowCount--;
	document.write("Summe: ".concat(iRowCount));
	</script>
			&nbsp;
	</span>
	</p>
	<b>
	';
?>

</body>
</html>
