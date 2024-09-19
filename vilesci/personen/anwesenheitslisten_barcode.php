<?php
/* Copyright (C) 2014 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/stunde.class.php');
require_once('../../include/standort.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('basis/person'))
	die($rechte->errormsg);

$studiengang = new studiengang;
$studiengang->getAll("typ, kurzbz");
$standort = new standort;
$standort->getAllStandorteWithOrt();

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//DE" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Anwesenheitslisten mit Barcodes</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>

	<script type="text/javascript">
	$(document).ready(function()
	{
	    $(".datepicker").datepicker($.datepicker.regional['de']).datepicker("setDate", new Date());

        // Dropdown der Lehrveranstaltungen befüllen
        $("#stg_kz, #sem").change(function()
        {
            // alte Optionen entfernen
            $("#lvid")
                .empty()
                .append($('<option>', {value : ''})
                .text('-- Alle --'));

            var stg_kz = $("#stg_kz").val();
            var sem = $("#sem").val();

            if(stg_kz != '' && sem != '')
            {
                // LVs ergänzen
                $.getJSON("lehrveranstaltungen_json.php", {stg_kz: stg_kz, sem: sem}, function(data) {
                    $.each(data, function(key, value) {
                        $('#lvid')
                            .append($('<option>', {value : key})
                            .text(value));
                    });
                });
            }
        })
	});

	function checkDates()
	{
		var result = true;

		if($("#von").val() == '' || $("#bis").val() == '')
		{
			result = false;
		}
		else
		{
			var von = $("#von").val().split('.');
			var bis = $("#bis").val().split('.');

			var vonObj = new Date(von[2] + "-" + von[1] + "-" + von[0]);
			var bisObj = new Date(bis[2] + "-" + bis[1] + "-" + bis[0]);

			if(Math.round((bisObj - vonObj) / 1000 / 60 / 60 / 24) > 14)
			{
				$("#error").show();
				result = false;
			}
			else
			{
				$("#error").hide();
				result = true;
			}
		}

		return result;
	}
	</script>
</head>
<body class="Background_main">
<h2>Anwesenheitslisten mit Barcodes</h2>
	<p id="error" style="display: none; font-weight: bold; color: red;">Die gewählte Zeitspanne darf nicht größer als 14 Tage sein!</p>
	<a href="anwesenheit.php">Listen erfassen</a>
	<form method="get" action="../../content/pdfExport.php" onsubmit="return checkDates();">
		<input type="hidden" name="xsl" value="AnwListBarcode" />
		<input type="hidden" name="output" value="pdf" />
		<input type="hidden" name="xml" value="anwesenheitsliste.xml.php" />

		<table>
		<tbody>
			<tr>
				<td>Von</td>
				<td><input type="text" name="von" class="datepicker" id="von" size="10" autocomplete="off" />
				<select name="stundevon">
				<?php
					$stunde = new stunde();
					$stunde->loadAll();
					foreach($stunde->stunden as $row)
					{
						echo '<option value="'.$row->stunde.'">'.$row->stunde.'. Stunde '.$row->beginn->format('H:i').' - '.$row->ende->format('H:i').'</option>';
					}
				?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Bis</td>
				<td><input type="text" name="bis" class="datepicker" id="bis" size="10" autocomplete="off" />
				<select id="stundebis" name="stundebis">
					<?php
					foreach($stunde->stunden as $row)
					{
						echo '<option value="'.$row->stunde.'">'.$row->stunde.'. Stunde '.$row->beginn->format('H:i').' - '.$row->ende->format('H:i').'</option>';
					}
				?>
				</select>
				<script>
				$(document).ready(function() {
					$('#stundebis option:last-child').attr('selected', 'selected');
				});
				</script>
			</td>
			</tr>
			<tr>
				<td>Studiengang</td>
				<td>
                    <select name="stg_kz" id="stg_kz">
						<option value=''>-- Alle --</option>
						<?php foreach($studiengang->result as $value) echo "<option value='$value->studiengang_kz'>$value->kuerzel ($value->bezeichnung)</option>\n"; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Ausbildungssemester</td>
				<td>
                    <select name="sem" id="sem">
						<option value=''>-- Alle --</option>
						<?php for($x = 1; $x <= 10; $x++) echo "<option value='$x'>$x</option>\n"; ?>
					</select>
				</td>
			</tr>
            <tr>
				<td>Lehrveranstaltung</td>
				<td>
					<select name="lvid" id="lvid">
						<option value=''>-- Alle --</option>

					</select>
				</td>
			</tr>
            <tr>
				<td>Anstellung der Vortragenden</td>
				<td>
					<select name="fixangestellt">
						<option value=''>-- Alle --</option>
						<option value='ja'>fix angestellt</option>
                        <option value='nein'>nebenberuflich</option>
					</select>
				</td>
			</tr>
            <tr>
				<td>Standort</td>
				<td>
					<select name="standort">
						<option value=''>-- Alle --</option>
						<?php foreach($standort->result as $value) echo "<option value='$value->standort_id'>$value->bezeichnung</option>\n"; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" value="Liste erstellen" /></td>
			</tr>
		</tbody>
		</table>
	</form>
</body>
</html>
