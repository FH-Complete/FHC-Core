<!DOCTYPE html>
<?php
/*
 * Copyright 2014 fhcomplete.org
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
 * Authors: Stefan Puraner	<puraner@technikum-wien.at>
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/pruefungsfenster.class.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/pruefungstermin.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/pruefungCis.class.php');
require_once('../../../../include/mitarbeiter.class.php');

$uid = get_uid();
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$studiensemester = new studiensemester();
$lehrveranstaltung = new lehrveranstaltung();
$lehrveranstaltung->loadLVAfromMitarbeiter(0, $uid, $studiensemester->getaktorNext());
if(empty($lehrveranstaltung->lehrveranstaltungen) && !$rechte->isBerechtigt('lehre/pruefungsterminAdmin'))
    die('Sie haben keine Berechtigung für diese Seite');

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Prüfungstermin festlegen</title>
        <script src="../../../../include/js/datecheck.js"></script>
        <script src="../../../../include/js/jquery1.9.min.js"></script>
	<script src="../../../../include/js/jquery.tablesorter.min.js"></script>
        <script src="./pruefung.js"></script>
        <link rel="stylesheet" href="../../../../skin/jquery-ui-1.9.2.custom.min.css">
        <link rel="stylesheet" href="../../../../skin/fhcomplete.css">
        <link rel="stylesheet" href="../../../../skin/style.css.php">
        <link rel="stylesheet" href="../../../../skin/tablesort.css">
	<style type="text/css">
	    #message {
		position: fixed;
		top: 0px;
		right: 0px;
		width: 50%;
		height: 2em;
		font-size: 1.5em;
		font-weight: bold;
	    }
	    
	    .missingFormData {
		border: 2px solid red;
		outline: 2px solid red;
	    }
	    
	    .modalOverlay {
		position: fixed;
		width: 100%;
		height: 100%;
		top: 0px;
		left: 0px;
		background-color: rgba(0,0,0,0.3); /* black semi-transparent */
	    }
	    
	    #prfDetails, #prfVerwaltung {
		margin: 1em;
	    }
	</style>
    </head>
    <body>
        <script>
            $(document).ready(function() {
		$("#einzeln").bind("change", function(){
		    if($("#einzeln").prop("checked") === true)
		    {
			$("#pruefungsintervall").closest("tr").css("visibility", "visible");
		    }
		    else
		    {
			$("#pruefungsintervall").closest("tr").css("visibility", "hidden");
		    }
		});
		var isFormHidden = true;
		$("#lektor").autocomplete({
		    source: "lektor_autocomplete.php?autocomplete=lektor",
		    minLength:2,
		    response: function(event, ui)
		    {
			//Value und Label fuer die Anzeige setzen
			for(i in ui.content)
			{
			    ui.content[i].value=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
			    ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
			}
		    },
		    select: function(event, ui)
		    {
			//Ausgewaehlte Ressource zuweisen und Textfeld wieder leeren
			if (ui.item.mitarbeiter_uid=='')
			{
			    $("#mitarbeiter_uid").val(ui.item.uid);
			    $("#uid").val("student");
			}
			else
			{
			    $("#mitarbeiter_uid").val(ui.item.uid);
			    $("#uid").val("lektor");
			}
			if(isFormHidden)
			{
			    isFormHidden = false;
			    $("#prfVerwaltung form").slideToggle("slow");
			}
//			loadAllPruefungen();
			resetPruefungsverwaltung();
//			loadPruefungstypen("false");
//			loadStudiensemester();
//			loadAllPruefungen();
		    }
		});
           });
        </script>
        <div id="prfVerwaltung">
	    <h1>Prüfungen verwalten</h1>
	    <table>
		<tr>
		    <?php
			if(!$rechte->isBerechtigt('lehre/pruefungsterminAdmin'))
			{
			    echo '<input id="mitarbeiter_uid" type="hidden" value="'.$uid.'"/>
			    <script>
				$(document).ready(function() {
				    $("#prfVerwaltung form").attr("style", "display: block");
				    loadPruefungstypen("false");
				    loadStudiensemester();
				    loadAllPruefungen();
				});
			    </script>';
			}
			else
			{
			    echo '<td width="116px">Lektor:</td>';
			    echo '<td width="250px"><input placeholder="UID" type="text" id="lektor" value="" size="30"/></td>';
			    echo '<input type="hidden" id="uid" value="" />';
			    echo '<input type="hidden" id="mitarbeiter_uid" value="" />';
			}
		    ?>
		</tr>
	    </table>
            <form method="post" action="pruefungstermin_festlegen.php" style="display: none;">
		
                <input type="hidden" name="method" value="save">
                <table>
                    <tr>
                        <td>Titel:</td>
                        <td>
                            <input id='titel' type="text" name="titel" size="30">
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Beschreibung:</td>
                        <td>
                            <textarea id='beschreibung' name="beschreibung" rows="5" cols="20"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>Studiensemester:</td>
                        <td>
                            <select id="studiensemester" name="studiensemester" onchange="loadPruefungsfenster();" onload="loadPruefungsfenster();">
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Prüfungsfenster:</td>
                        <td>
                            <select id="pruefungsfenster" name="pruefungsfenster" onchange="setDatePicker(this);">
                                <!--Daten werden durch JavaScript geladen-->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Prüfungstyp:</td>
                        <td>
                            <select id='pruefungsTyp' name="pruefungsTyp">
<!--				Daten werden per JavaScript geladen-->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Methode:</td>
                        <td><textarea id='methode' placeholder="Multiple Choice, etc." rows="5" cols="20" name="methode"></textarea></td>
                    </tr>
                    <tr>
                        <td>Einzelprüfung:</td>
                        <td><input id='einzeln' type="checkbox" name="einzelpruefung"></td>
                    </tr>
		    <tr  style="visibility:hidden;">
			<td>Prüfungsintervall:</td>
			<td>
			    <select id="pruefungsintervall">
				<option value="15">15</option>
				<option value="20">20</option>
				<option value="30">30</option>
			    </select>
			</td>
		    </tr>
                    <tr>
                        <td style="vertical-align: top;">Lehrveranstaltungen:</td>
                        <td>
                            <div id="lvDropdowns">
                                <select id="lvDropdown1" onchange="lehrveranstaltungDropdownhinzufuegen(this, false);" name="lv[]">
				    <!--Daten werden per JavaScript geladen-->
                                </select></br>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><a name="termin">Termin:</a></td>
                        <td>
                            <div>
                                <table width="500px" style="text-align: right;">
                                    <thead>
                                        <tr>
                                            <th>Datum</th>
                                            <th>Von</th>
                                            <th>bis</th>
                                            <th>min. Teilnehmer</th>
                                            <th>max. Teilnehmer</th>
					    <th>Sammelklausur</th>
					    <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="prfTermin">
                                        <tr id="row1">
                                            <td>
                                                <input type="text" id="termin1" name="termin[]">
                                            </td>
                                            <td>
                                                <input type="time" id="termin1Beginn" placeholder="00:00" name="termin1Beginn[]">
                                            </td>
                                            <td>
                                                <input type="time" id="termin1Ende" placeholder="00:00" name="termin1Ende[]">
                                            </td>
                                            <td>
                                                <input type="number" id="termin1min" placeholder="0" min="0" name="termin1minTeilnehmer[]">
                                            </td>
                                            <td>
                                                <input type="number" id="termin1max" placeholder="10" min="0" name="termin1maxTeilnehmer[]">
                                            </td>
					    <td>
                                                <input type="number" id="termin1max" placeholder="10" min="0" name="termin1maxTeilnehmer[]">
                                            </td>
					    <td>
						<input id="termin1sammelklausur" type="checkbox" name="sammelklausur">
					    </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <a href="#termin" onclick="terminHinzufuegen();">Termin hinzufügen</a>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td><td><input id="buttonSave" type="button" value="Speichern" onclick='savePruefungstermin();'></td>
                    </tr>
                </table>
            </form>
        </div>
        <div id="prfDetails">
            <h2>Prüfungen</h2>
            <div style="width: 75%;">
                <table class="tablesorter" id="prfTable">
                    <thead>
                        <tr>
                            <th>Titel</th>
                            <th>Studiensemester</th>
                            <th>Lehrveranstaltungen</th>
                            <th>Termine</th>
                            <th>Methode</th>
                            <th>Prüfungstyp</th>
                            <th>Einzelprüfung</th>
                            <th>Mitarbeiter</th>
                            <th>storniert</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
	<div id='message'></div>
	<div id="modalOverlay"></div>
    </body>
</html>
