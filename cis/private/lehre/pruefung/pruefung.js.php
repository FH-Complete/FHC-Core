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
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/globals.inc.php');
require_once('../../../../include/sprache.class.php');

$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

if (defined('CIS_PRUEFUNGSANMELDUNG_FRIST'))
{
	$anmeldefrist = CIS_PRUEFUNGSANMELDUNG_FRIST;
}
else
{
	$anmeldefrist = 3;
}

if (defined('CIS_PRUEFUNGSTERMIN_FRIST'))
{
	$terminfrist = CIS_PRUEFUNGSTERMIN_FRIST;
}
else
{
	$terminfrist = 14;
}

?>

/* Copyright (C) 2013 fhcomplete.org
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
 * Authors:		Stefan Puraner	<puraner@technikum-wien.at>
 */

/**
 * Liest GET Variablen einer URL aus
 * @returns {String|value|Element.value|document@arr;all.value}
 */
function getUrlVars()
{
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		vars[key] = value;
	});
	return vars;
}

/**
 * Initialisiert den Datepicker
 * @param {type} ele
 * @returns {undefined}
 */
function setDatePicker(ele)
{
	var start = $("#pruefungsfenster option:selected").attr("start");
	start = start.split('-');
	var ende = $("#pruefungsfenster option:selected").attr("ende");
	ende = ende.split('-');
	$("#prfTermin input[type=text]").each(function(i,v){
		$("#"+v.id).datepicker("destroy");
		$("#"+v.id).datepicker({
			minDate: new Date(start[0],start[1]-1,start[2]),
			maxDate: new Date(ende[0],ende[1]-1,ende[2])
		});
	});
	$("#pruefungsfenster").attr("onchange", "setDatePicker()");
}

/**
 * Fügt ein neunes Formularfeld für einen zusätzlichen Termin hinzu
 * @param {type} htmlTag
 * @returns {undefined}
 */
function terminHinzufuegen(htmlTag)
{
	htmlTag = (htmlTag===undefined ? "input" : htmlTag);
	switch(htmlTag)
	{
		case 'input':
			var count = $("#prfTermin tr").length+1;
			$("#prfTermin").append('<tr id="row'+count+'"><td><input type="text" id="termin'+count+'" name="termin[]"></td><td><input type="time" id="termin'+count+'Beginn" placeholder="00:00" name="termin'+count+'Beginn[]"></td><td><input type="time" id="termin'+count+'Ende" placeholder="00:00" name="termin'+count+'Ende[]"></td><td><input type="number" id="termin'+count+'min" placeholder="0" min="0" name="termin'+count+'minTeilnehmer[]"></td><td><input type="number" id="termin'+count+'max" placeholder="10" min="0" name="termin'+count+'maxTeilnehmer[]"></td><td><input id="termin'+count+'sammelklausur" type="checkbox" name="sammelklausur"></td></tr>');
			setDatePicker();
			break;
		case 'span':
			var count = $("#prfTermin tr").length+1;
			$("#prfTermin").append('<tr id="row'+count+'"><td><span style="visibility: hidden;" id="termin'+count+'Id"></span><span id="termin'+count+'" name="termin[]"></span></td><td><span id="termin'+count+'Beginn" name="termin'+count+'Beginn[]"></span></td><td><span id="termin'+count+'Ende" name="termin'+count+'Ende[]"></span></td><td><span id="termin'+count+'min" name="termin'+count+'minTeilnehmer[]"></span></td><td><span id="termin'+count+'max" name="termin'+count+'maxTeilnehmer[]"></span></td><td><span id="termin'+count+'sammelklausur" name="termin'+count+'sammelklausur"></td></tr>');
			break;
	}

}

/**
 * Fügt ein neues Dropdownfeld zur Auswahl der Lehrveranstaltung hinzu
 * @param {type} element
 * @param {type} isChanged
 * @returns {undefined}
 */
function lehrveranstaltungDropdownhinzufuegen(element, isChanged)
{
	if(!isChanged)
	{
		var newSelect = $("#lvDropdown1").clone();
		newSelect.attr("onchange", "lehrveranstaltungDropdownhinzufuegen(this, false);");
		var id = $("#lvDropdowns select").length+1;
		newSelect.attr("id", "lvDropdown"+id);
		element.setAttribute("onchange", "lehrveranstaltungDropdownhinzufuegen(this, true);");
		newSelect.appendTo("#lvDropdowns");
		$("#lvDropdowns").append("<br/>");
	}

}

/**
 * Error-Behandlung bei Ajax Requests
 */
function loadError(xhr, textStatus, errorThrown)
{
	if(xhr.status==200)
		alert('Fehler:'+xhr.responseText);
	else
		alert('Fehler beim Laden der Daten. ErrorNr:'+xhr.status);
}

/**
 * Lädt die Prüfungstenster eines Studiensemesters
 * @returns {undefined}
 */
function loadPruefungsfenster()
{
	var studiensemester_kurzbz = $("#studiensemester option:selected").val();
	$.ajax({
		dataType: 'json',
		url: "./pruefungstermin.json.php",
		type: "POST",
		data: {
			method: "getPruefungsfensterByStudiensemester",
			studiensemester_kurzbz: studiensemester_kurzbz
		},
		error: loadError,
		success: function(data){
			if(data.result.length === 0)
			{
				messageBox("message", "<?php echo $p->t('pruefung/keinFensterVorhanden'); ?>", "red", "highlight", 10000);
				$("#pruefungsfenster").html("<option value='null'></option>");
			}
			else
			{
				writePruefungsfenster(data);
				setDatePicker();
			}
		}
	});
}

/**
 * Schreibt die Daten des geladenen Prüfungsfensters
 * @param {type} data
 * @returns {undefined}
 */
function writePruefungsfenster(data)
{
	var prfFensterId = getUrlVars()["prfFensterId"];
	$("#pruefungsfenster").empty();
	$.each(data.result, function(i, v)
	{
		if(v.oe_kurzbz !== null)
		{
			var start = v.start;
			var ende = v.ende;
			start = start.split('-');
			ende = ende.split('-');
			start = new Date(start[0], start[1]-1,start[2]);
			ende = new Date(ende[0], ende[1]-1,ende[2]);
			start = start.getDate()+"."+(start.getMonth()+1)+"."+start.getFullYear();
			ende = ende.getDate()+"."+(ende.getMonth()+1)+"."+ende.getFullYear();
			if(v.pruefungsfenster_id === prfFensterId)
			{
				$("#pruefungsfenster").append("<option selected start='"+v.start+"' ende='"+v.ende+"' value='"+v.pruefungsfenster_id+"'>" + v.oe_kurzbz +" ("+start+" - "+ende+")</option>");
			}
			else
			{
				$("#pruefungsfenster").append("<option start='"+v.start+"' ende='"+v.ende+"' value='"+v.pruefungsfenster_id+"'>" + v.oe_kurzbz +" ("+start+" - "+ende+")</option>");
			}
		}
		else
		{
			$("#pruefungsfenster").html("<option><?php echo $p->t('pruefung/keinFensterVorhanden'); ?></option>");
		}

	});
}

/**
 * Lädt alle Prüfungen eines Studenten zu deren LVs er angemeldet ist
 * @returns {undefined}
 */
function loadPruefungen()
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getPruefungByLv",
			studiensemester: $("#filter_studiensemester").val()
		},
		error: loadError,
		success: function(data){
			$("#pruefungen").empty();
			if(data.error === 'false')
			{
				data.result.pruefungen.forEach(function(e){
					if(e.pruefung.storniert === false)
					{
						var table = writePruefungsTable(e, data, true);
						$("#pruefungen").append(table);
					}
				});
			}
			else
			{
				$("#pruefungen").append("<td align='center' colspan='6'><?php echo $p->t('pruefung/keineDatenVorhanden'); ?></td>");
			}
			setTablesorter("table1");
		}
	});
}

/**
 * Lädt alle Prüfungen eines Studienganges
 * @returns {undefined}
 */
function loadPruefungenOfStudiengang()
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getPruefungByLvFromStudiengang",
			studiensemester: $("#filter_studiensemester").val()
		},
		error: loadError,
		success: function(data){
			if(data.error === 'false')
			{
				data.result.pruefungen.forEach(function(e){
					if(e.pruefung.storniert === false)
					{
						var table = writePruefungsTable(e, data, true);
						$("#pruefungenStudiengang").append(table);
					}
				});
			}
			else
			{
				$("#pruefungenStudiengang").append("<td align='center' colspan='6'><?php echo $p->t('pruefung/keineDatenVorhanden'); ?></td>");
			}

			setTablesorter("table2");
		}
	});
}

/**
 * Lädt alle Prüfungen
 * @returns {undefined}
 */
function loadPruefungenGesamt()
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getAllPruefungen",
		},
		error: loadError,
		success: function(data){
			data.result.pruefungen.forEach(function(e){
				if(e.pruefung.storniert === false)
				{
					var table = writePruefungsTable(e, data, false);
					$("#pruefungenGesamt").append(table);
				}
			});
			setTablesorter("table3");
		}
	});
}

/**
 * Schreibt die Daten der Prüfungen in eine Tabelle
 * @param {type} e Daten der Prüfungen
 * @param {type} data Daten der Anmeldungen
 * @returns {String}
 */
function writePruefungsTable(e, data, anmeldung)
{
	var row = "";
	var teilnehmer = "";
	row += "<tr><td>"+e.organisationseinheit+"</td><td style='cursor: pointer; text-decoration: underline;' onclick='showPruefungsDetails(\""+e.pruefung.pruefung_id+"\",\""+e.lehrveranstaltung[0].lehrveranstaltung_id+"\");'>"+e.lehrveranstaltung[0].bezeichnung+" <br>("+e.lehrveranstaltung[0].lehrform_kurzbz+", "+e.lehrveranstaltung[0].ects+" ECTS, "+e.pruefung.mitarbeiter_uid+")</td><td>";
	e.pruefung.termine.forEach(function(d){
        var button = "";
	    var storno = false;
		var anmeldung_id = null;
		data.result.anmeldungen.forEach(function(anmeldung){
			if((anmeldung.pruefungstermin_id === d.pruefungstermin_id) && (anmeldung.lehrveranstaltung_id === e.lehrveranstaltung[0].lehrveranstaltung_id))
			{
				storno = true;
				anmeldung_id= anmeldung.pruefungsanmeldung_id;
			}
		});
		var termin = d.von.split(" ");
		var time = termin[1].substring(0,5);
		termin = termin[0].split("-");

		// Wie viele Monate vor Prüfungen dürfen sich Studierende anmelden?
        // Sperre "deaktiviert" indem man sich 24 Monate vorher anmelden darf
		var minimumFrist = new Date(termin[0], termin[1]-1,termin[2]);
        minimumFrist.setMonth(minimumFrist.getMonth() - 24);

        termin = new Date(termin[0], termin[1]-1,termin[2]);
		var frist = termin;
		termin = termin.getDate()+"."+(termin.getMonth()+1)+"."+termin.getFullYear();
		frist = frist.getTime();
		frist = frist - (<?php echo $anmeldefrist ?>*24*60*60*1000);
		var fristDate = new Date(frist);
		frist = fristDate.getDate()+"."+(fristDate.getMonth()+1)+"."+fristDate.getFullYear();

		if(fristDate < new Date())
		{
			if(!storno)
				button = "<p><span style='display: inline-block; width: 155px;'><?php echo $p->t('pruefung/anmeldefristAbgelaufen'); ?></span><br />";
			else
				button = "<p><span style='display: inline-block; width: 155px;'><?php echo $p->t('pruefung/stornoNichtMehrMoeglich'); ?></span><br />";
		}
		else if(anmeldung || e.lehrveranstaltung[0].angemeldet)
		{
			if(storno)
			{
				button = "<p><a href='#' title='<?php echo $p->t('pruefung/stornierenMoeglichBis'); ?> "+frist+"'><input style='width: 140px;' type='button' value='"+termin+" "+time+"' onclick='stornoAnmeldung(\""+anmeldung_id+"\");'></a></p>";

			}
			else if(new Date() > minimumFrist)
			{
				button = "<p><a href='#' title='<?php echo $p->t('pruefung/anmeldenMoeglichBis'); ?> "+frist+"'><input style='width: 140px; background-color: green;' type='button' value='"+termin+" "+time+"' onclick='openDialog(\""+e.lehrveranstaltung[0].lehrveranstaltung_id+"\", \""+d.pruefungstermin_id+"\", \""+e.lehrveranstaltung[0].bezeichnung.replace("'", "&apos;")+"\", \""+d.von+"\", \""+d.bis+"\");'></a></p>";
			}
		}
		else
        {
			button = "<p><input style='width: 180px;' type='button' value='<?php echo $p->t('pruefung/zurLvAnmeldung'); ?>' onclick='openAnmeldung(\""+e.lehrveranstaltung[0].lehrveranstaltung_id+"\", \""+e.pruefung.studiensemester_kurzbz+"\");'></p>";
		}

		row += button;

		if(new Date() > minimumFrist)
        {
            if(d.max === null)
            {
                teilnehmer += "<?php echo $p->t('pruefung/unbegrenzt'); ?><br />";
            }
            else
            {
                teilnehmer += "<p><span style='line-height: 24px'>"+(d.max - d.teilnehmer)+"/"+d.max+"</span></p>";
            }
        }
	});
	row += "<td>"+teilnehmer+"</td>";
	return row;
}

/**
 * Lädt die Details zu einer Prüfung
 * @param {type} prfId ID einer Prüfung
 * @param {type} lvId ID einer Lehrveranstaltung
 * @returns {undefined}
 */
function showPruefungsDetails(prfId, lvId)
{
	var prfId = prfId;
	if(prfId!=="null")
	{
		$.ajax({
			dataType: 'json',
			url: "./pruefungsanmeldung.json.php",
			type: "POST",
			data: {
				method: "loadPruefung",
				pruefung_id: prfId
			},
			error: loadError,
			success: function(data){
				data.result.forEach(function(e){
					if(e.lehrveranstaltung.lehrveranstaltung_id === lvId)
					{
						var p = e.pruefung;
						var l = e.lehrveranstaltung

						$("#prfMethode").html(p.methode);
						$("#prfBeschreibung").html(p.beschreibung);
						if(p.einzeln === true)
						{
							$("#prfEinzeln").html("<b>Einzelprüfung!</b>");
							$("#prfIntervall").html(p.pruefungsintervall+" min");
							$("#prfIntervall").prev().css("visibility", "visible");
						}
						else
						{
							$("#prfEinzeln").html("");
							$("#prfIntervall").html("");
							$("#prfIntervall").prev().css("visibility", "hidden");
						}
						$("#lvBez").html(l.bezeichnung);
						if(l.ects !== null)
						{
							$("#lvEcts").html(l.ects);
						}
						else
						{
							$("#lvEcts").html("0");
						}
					}
				});
				$("#details").dialog("open");
			}
		});
	}
	else
	{
		$("#prfTermine").attr("disabled", true);
		$("#prfTermine").html("<option><?php echo $p->t('pruefung/zuerstPruefungAuswaehlen'); ?></option>");
		clearPrfDetails();
	}
}

/**
 * Öffnet einen Dialog zur Anmeldung zu einer Prüfung
 * @param {type} lehrveranstaltung_id ID einer Lehrveransaltung
 * @param {type} termin_id ID des Prüfungstermines
 * @param {type} lvBezeichnung Bezeichnung der Lehrveranstaltung
 * @param {type} terminVon Beginn der Prüfung
 * @param {type} terminBis Ende der Prüfung
 * @returns {undefined}
 */
function openDialog(lehrveranstaltung_id, termin_id, lvBezeichnung, terminVon, terminBis)
{
	$("#lehrveranstaltungHidden").val(lehrveranstaltung_id);
	$("#terminHidden").val(termin_id);
	$("#lehrveranstaltung").html(lvBezeichnung);

	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getLvKompatibel",
			lehrveranstaltung_id: lehrveranstaltung_id
		},
		error: loadError,
		success: function(data){
			var html = "";
			html += '<option id="'+lehrveranstaltung_id+'" value="'+lehrveranstaltung_id+'">';
			html += lvBezeichnung;
			html += "</option>";
			data.result.forEach(function(v, i){
				html += '<option id="'+v.lehrveranstaltung_id+'" value="'+v.lehrveranstaltung_id+'">';
				html += v.bezeichnung;
				html += "</option>";
			});
			$("#studienverpflichtung").html(html);
		}
	});

	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getPrestudenten"
		},
		error: loadError,
		success: function(data)
		{
			if(data.error !== false)
			{
				//show if more than 1 active prestudent exists
				if(data.result.length > 1)
				{
					var html = "<select id='prestudent_studiengang' name='studiengang'>";
					data.result.forEach(function(v,i){
						html += "<option value='"+v.studiengang_kz+"'>"+v.kuerzel+"</option>";
					});
					html += "</select>";

					$("#studiengang").html("<td style='vertical-align: top; font-weight: bold;'><?php echo $p->t('pruefung/AnrechnungInStudiengang'); ?>:</td><td>"+html+"</td>");
				}
			}
		}
	});

	var start = terminVon;
	var ende = terminBis;
	start = start.split(' ');
	ende = ende.split(' ');
	var startTime = start[1];
	var endeTime = ende[1];
	start = start[0].split('-');
	ende = ende[0].split('-');
	start = new Date(start[0], start[1]-1,start[2]);
	ende = new Date(ende[0], ende[1]-1,ende[2]);
	start = start.getDate()+"."+(start.getMonth()+1)+"."+start.getFullYear();
	ende = ende.getDate()+"."+(ende.getMonth()+1)+"."+ende.getFullYear();

	start += " "+startTime.substr(0,5)+" Uhr";
	ende += " "+endeTime.substr(0,5)+" Uhr";
	$("#terminVon").html(start);
	$("#terminBis").html(ende);
	$("#saveDialog").dialog("open");
}

/**
 * speichert eine Prüfungsanmeldung
 * @param {type} lehrveranstaltung_id ID der Lehrveranstaltung
 * @param {type} termin_id ID des Prüfungstermines
 * @returns {undefined}
 */
function saveAnmeldung(lehrveranstaltung_id, termin_id)
{
	var uid = $("#anmeldung_hinzufuegen_uid").val();
	if(lehrveranstaltung_id === undefined)
		lehrveranstaltung_id = $("#lehrveranstaltungHidden").val();
	if(termin_id === undefined)
		termin_id = $("#terminHidden").val();
	var bemerkungen = $("#anmeldungBemerkung").val();
	if(bemerkungen === undefined)
		bemerkungen = "<?php echo $p->t('pruefung/bemerkungVonLektorHinzugefuegt'); ?>";

	var studienverpflichtung_id = null;
	if($("#studienverpflichtung").length)
		studienverpflichtung_id = $("#studienverpflichtung option:selected").val();

	var studiengang_kz = null;
	if($('#prestudent_studiengang').length)
		studiengang_kz =   $('#prestudent_studiengang option:selected').val();

	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "saveAnmeldung",
			termin_id: termin_id,
			lehrveranstaltung_id: lehrveranstaltung_id,
			bemerkung: bemerkungen,
			uid: uid,
			studienverpflichtung_id: studienverpflichtung_id,
			studiengang_kz: studiengang_kz
		},
		error: loadError,
		success: function(data){
			if(data.error === 'false')
			{
				messageBox("message", data.result, "green", "highlight", 10000);
			}
			else
			{
				messageBox("message", data.errormsg, "red", "highlight", 10000);
			}
			resetForm();

			$("#saveDialog").dialog("close");

			if(uid === undefined)
			{
				//Wenn Anmeldung durch Student
				refresh();
			}
			else
			{
				//Wenn Anmeldung durch Lektor
				showAnmeldungen(termin_id, lehrveranstaltung_id, false, false);
			}
		}
	});
}

/**
 * storniert eine Prüfungsanmeldung
 * @param {type} pruefungsanmeldung_id ID einer Prüfungsanmeldung
 * @returns {undefined}
 */
function stornoAnmeldung(pruefungsanmeldung_id)
{
	if(confirm("Anmeldung wirklich stornieren?"))
	{
		$.ajax({
			dataType: 'json',
			url: "./pruefungsanmeldung.json.php",
			type: "POST",
			data: {
				method: "stornoAnmeldung",
				pruefungsanmeldung_id: pruefungsanmeldung_id
			},
			error: loadError,
			success: function(data){
				if(data.error === 'false')
				{
					messageBox("message", data.result, "green", "highlight", 10000);
				}
				else
				{
					messageBox("message", data.errormsg, "red", "highlight", 10000);
				}

				refresh();
			}
		});
	}
}

/**
 * Leer das DIV-Element der Prüfungsdetails
 * @returns {undefined}
 */
function clearPrfDetails()
{
	$("#prfTyp").empty();
	$("#prfMethode").empty();
	$("#prfBeschreibung").empty();
	$("#prfEinzeln").empty();
}

/**
 * Setzt ein Formular auf den Urzustand zurück
 * @returns {undefined}
 */
function resetForm()
{
	$("form").find("input[type=text], textarea").val("");
	clearPrfDetails();
}

/**
 * Initialisiert den tablesorter für eine Tabelle
 * @param {type} tableId ID eines "table"-Elements
 * @returns {undefined}
 */
function setTablesorter(tableId)
{
	if($("#"+tableId).length != 0)
	{
		if($("#"+tableId)[0].hasInitialized !== true)
		{
			$("#"+tableId).tablesorter({
				widgets: ["zebra"],
				sortList: [[1,0]]
			});
		}
		else
		{
			$("#"+tableId).trigger("updateAll");
			var sorting = [[1,0],[0,0]];
			$("#"+tableId).trigger("sorton",[sorting]);
		}
	}
}

/**
 * Leert die Daten eines table-bodys
 * @returns {undefined}
 */
function clearAccordion()
{
	$("#accordion tbody").each(function(i, v){
		$("#"+v.id).empty();
	});
}

/**
 * Lädt die Seite der Prüfungsanmeldung neu
 * @returns {undefined}
 */
function refresh()
{
	clearAccordion();
	loadPruefungen();
	loadPruefungenOfStudiengang();
	loadPruefungenGesamt();

	if ($("#filter_studiensemester").val() == "0")
	    $("#additional-exams").hide();
	else
        $("#additional-exams").show();
}

/**
 * Konvertiert einen String im Fromat JJJJ-MM-TT hh:mm:ss zu DD.MM.JJJJ oder HH:MM
 * @param {String} string Format: JJJJ-MM-TT hh:mm:ss
 * @param {String} type spezifiziet die Ausgabe ("time"|"date")
 * @returns {String} Format: date: DD.MM.JJJJ; time: HH:MM
 */
function convertDateTime(string, type)
{
	switch (type)
	{
		case 'date':
			string = string.split(' ');
			string = string[0].split('-');
			string = new Date(string[0], string[1]-1,string[2]);
			string = string.getDate()+"."+(string.getMonth()+1)+"."+string.getFullYear();
			break;
		case 'time':
			string = string.split(' ');
			string = string[1].split(':');
			string = string[0]+":"+string[1];
			break;
		default:
			string = string.split(' ');
			string = string[0].split('-');
			string = new Date(string[0], string[1]-1,string[2]);
			string = string.getDate()+"."+(string.getMonth()+1)+"."+string.getFullYear();
			break;
	}
	return string;
}

/**
 * Lädt die Anmeldungen zu einer Prüfung
 * @param {type} pruefungstermin_id ID des Prüfungstermins
 * @param {type} lehrveranstaltung_id ID der Lehrveranstaltung
 * @param saveReihungAfterShow speichert Reihung neu wenn true
 * @param showMessage steuert ob Meldung angzeigt werden soll
 * @returns {undefined}
 */
function showAnmeldungen(pruefungstermin_id, lehrveranstaltung_id, saveReihungAfterShow = false, showMessage = true)
{
	$("#kommentar").empty();
	$("#kommentarSpeichernButton").empty();
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getAnmeldungenTermin",
			pruefungstermin_id: pruefungstermin_id,
			lehrveranstaltung_id: lehrveranstaltung_id
		},
		error: loadError,
		success: function(data){
			writeAnmeldungen(data, showMessage);
			$("#sortable").sortable();
			$("#sortable").disableSelection();

			if(saveReihungAfterShow)
                saveReihung(pruefungstermin_id, lehrveranstaltung_id);
		}
	});
}

function writeAnmeldungen(data, showMessage = true)
{
	if(data.error === 'false')
	{
		var terminId = data.result.anmeldungen[0].pruefungstermin_id;
		var pruefung_id = data.result.anmeldungen[0].pruefung_id;
		var lehrveranstaltung_id = data.result.anmeldungen[0].lehrveranstaltung_id;
		var ort_kurzbz = data.result.ort_kurzbz;
		var lv_bezeichnung = data.result.lv_bezeichnung;
		var lv_lehrtyp = data.result.lv_lehrtyp;
		var prf_termin = data.result.datum;
		var liste = "<ul id='sortable'>";
		var count = 0;
		var studiensemester = $("#filter_studiensemester option:selected").val();
		var listenLinks = "<a href='./pruefungsanmeldungen_liste.pdf.php?termin_id="+terminId+"&lehrveranstaltung_id="+lehrveranstaltung_id+"&studiensemester="+studiensemester+"' target='_blank'><?php echo $p->t('pruefung/listeDrucken'); ?></a><br>"
                        + "<a href='./pruefungsanmeldungen_liste_ohne_namen.php?termin_id="+terminId+"&lehrveranstaltung_id="+lehrveranstaltung_id+"&studiensemester="+studiensemester+"' target='_blank'><?php echo $p->t('pruefung/listeOhneNamenDrucken'); ?></a>";
		data.result.anmeldungen.forEach(function(d){
			count++;
			var vorname = d.student.vorname !== "null" ? d.student.vorname : "";
			var nachname = d.student.nachname !== "null" ? d.student.nachname : "";
			switch(d.status_kurzbz)
			{
				case 'angemeldet':
					liste += "<li class='ui-state-default' id='"+d.student.uid+"'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span><a href='#' onclick='showKommentar(\""+vorname+"\",\""+nachname+"\", \""+d.pruefungsanmeldung_id+"\", \""+d.kommentar+"\", \""+terminId+"\", \""+lehrveranstaltung_id+"\");'>"+vorname+" "+nachname+"</a>";
					liste += "<div style='width: 3%; text-align: right;'>"+count+"</div><div style='text-align: center; width: 34%;'><input style='vertical-align: top; height: 24px;' type='button' value='<?php echo $p->t('pruefung/bestaetigen'); ?>' onclick='anmeldungBestaetigen(\""+d.pruefungsanmeldung_id+"\", \""+terminId+"\", \""+lehrveranstaltung_id+"\");'>";
					liste += "<input style='vertical-align: top; height: 24px; background-color: #dd514c;' type='button' value='X' onclick='anmeldungLoeschen(\""+d.pruefungsanmeldung_id+"\", \""+terminId+"\", \""+lehrveranstaltung_id+"\");'></div>";
					if(d.wuensche !== null)
					{
						liste += "<div class='anmerkungInfo'><a href='#' title='<?php echo $p->t('pruefung/anmerkungDesStudenten'); ?>"+d.wuensche+"'><img style='width: 20px;' src='../../../../skin/images/button_lvinfo.png'></a></div>";
					}
					liste += "</li>";
					break;
				case 'bestaetigt':
					liste += "<li class='ui-state-default' id='"+d.student.uid+"'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span><a href='#' onclick='showKommentar(\""+vorname+"\",\""+nachname+"\", \""+d.pruefungsanmeldung_id+"\", \""+d.kommentar+"\", \""+terminId+"\", \""+lehrveranstaltung_id+"\");'>"+vorname+" "+nachname+"</a>";
					liste += "<div style='width: 2%; text-align: right;'>"+count+"</div><div style='text-align: center; width: 20%;'><a href='#' title='<?php echo $p->t('pruefung/statusAenderungVon'); ?>: "+d.statusupdatevon+"'><?php echo $p->t('pruefung/bestaetigt'); ?></a></div>";
					if(d.wuensche !== null)
					{
						liste += "<div class='anmerkungInfo'><a href='#' title='<?php echo $p->t('pruefung/anmerkungDesStudenten'); ?>"+d.wuensche+"'><img style='width: 20px;' src='../../../../skin/images/button_lvinfo.png'></a></div>";
					}

					break;
				default:
					break;
			}

		});
		liste += "</ul>";
		$("#anmeldung_hinzufuegen").html("<input id='anmeldung_hinzufuegen_uid' type='text' placeholder='StudentIn-UID' /><input type='button' value='<?php echo $p->t('global/hinzufuegen'); ?>' onclick='saveAnmeldung(\""+lehrveranstaltung_id+"\",\""+terminId+"\");'/>");
		$("#reihungSpeichernButton").html("<input type='button' value='<?php echo $p->t('pruefung/reihungSpeichern'); ?>' onclick='saveReihung(\""+terminId+"\", \""+lehrveranstaltung_id+"\");'><input type='button' value='<?php echo $p->t('pruefung/alleBestaetigen'); ?>' onclick='alleBestaetigen(\""+terminId+"\", \""+lehrveranstaltung_id+"\");'>");
		$("#lvdaten").html(lv_bezeichnung+" ("+prf_termin+")");
		$("#anmeldeDaten").html(liste);
		$("#listeDrucken").html(listenLinks);
		if(ort_kurzbz !== null)
		{
			$("#raumLink").html("<span><?php echo $p->t('pruefung/pruefungsraum'); ?></span>"+ort_kurzbz);
		}
		else
		{
			$("#raumLink").html("<a href='#' onclick='openRaumDialog(\""+terminId+"\", \""+lehrveranstaltung_id+"\");'><?php echo $p->t('pruefung/pruefungsort'); ?></a>");
		}

	}
	else
	{
		$("#anmeldung_hinzufuegen").empty();
		$("#lvdaten").empty();
		$("#anmeldeDaten").empty();
		$("#reihungSpeichernButton").empty();
		$("#kommentar").empty();
		$("#kommentarSpeichernButton").empty();
		$("#raumLink").empty();
		$("#listeDrucken").empty();

		if (showMessage)
		    messageBox("message", data.errormsg, "red", "highlight", 10000);

		if (data.lv_bezeichnung)
		{
			$("#lvdaten").html(data.lv_bezeichnung+" ("+data.termin_datum+")");
			$("#anmeldung_hinzufuegen").html("<input id='anmeldung_hinzufuegen_uid' type='text' placeholder='StudentIn-UID' /><input type='button' value='<?php echo $p->t('global/hinzufuegen'); ?>' onclick='saveAnmeldung(\""+data.lv_id+"\",\""+data.termin_id+"\");'/>");
		}
	}
}

function openRaumDialog(terminId, lehrveranstaltung_id)
{
	getRaeume(terminId);
	$("#raum").html('<h2><?php echo $p->t('pruefung/pruefungsraum'); ?></h2><input onChange="changeStateOfRaumDropdown();" type="checkbox" /><span><?php echo $p->t('pruefung/imBuero'); ?></span><br /><span style="font-weight: bold;"><?php echo $p->t('pruefung/raum'); ?>: </span><select id="raeumeDropdown"></select>');
	$("#raumSpeichernButton").html("<input type='button' value='<?php echo $p->t('pruefung/raumSpeichern'); ?>' onclick='saveRaum(\""+terminId+"\", \""+lehrveranstaltung_id+"\");'/>");
	$("#raumDialog").dialog("open");
}

/**
 * speichert die Reihung der Studenten einer Prüfungsanmeldung
 * @param {type} terminId ID des Prüfungstermines
 * @param {type} lehrveranstaltung_id ID der Lehrveranstaltung
 * @returns {undefined}
 */
function saveReihung(terminId, lehrveranstaltung_id)
{
	var reihung = [];
	$("#anmeldeDaten ul").children().each(function(i, v){
		var anmeldung = new Object();
		anmeldung.terminId = terminId;
		anmeldung.lehrveranstaltung_id = lehrveranstaltung_id;
		anmeldung.reihung = (i+1);
		anmeldung.uid = v.id;
		reihung.push(anmeldung);
	});

	if (reihung.length > 0) {
        $.ajax({
            dataType: 'json',
            url: "./pruefungsanmeldung.json.php",
            type: "POST",
            data: {
                method: "saveReihung",
                reihung: reihung
            },
            error: loadError,
            success: function(data){
                if(data.error === 'false' && data.result === true)
                {
                    messageBox("message", "<?php echo $p->t('pruefung/reihunghErfolgreichGeaendert'); ?>", "green", "highlight", 10000);
                }
                else
                {
                    messageBox("message", data.errormsg, "red", "highlight", 10000);
                }

                showAnmeldungen(terminId, lehrveranstaltung_id);
            }
        });
    }
}

/**
 * Ändert den Status einer Anmeldung auf "bestätigt"
 * @param {type} pruefungsanmeldung_id ID der Prüfungsanmeldung
 * @param {type} termin_id ID des Prüfungstermines
 * @param {type} lehrveranstaltung_id ID der Lehrveranstaltung
 * @returns {undefined}
 */
function anmeldungBestaetigen(pruefungsanmeldung_id, termin_id, lehrveranstaltung_id)
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "anmeldungBestaetigen",
			pruefungsanmeldung_id: pruefungsanmeldung_id
		},
		error: loadError,
		success: function(data){
			if(data.error === 'false' && data.result === true)
			{
				if(termin_id !== 'undefined' && lehrveranstaltung_id !== 'undefined')
				{
					showAnmeldungen(termin_id, lehrveranstaltung_id);
				}
			}
			else
			{
				messageBox("message", data.errormsg, "red", "highlight", 10000);
			}
		}
	});
}

/**
 * Löscht eine Prüfungsanmeldung
 * @param {type} pruefungsanmeldung_id ID der Prüfungsanmeldung
 * @param {type} termin_id ID des Prüfungstermines
 * @param {type} lehrveranstaltung_id ID der Lehrveranstaltung
 * @returns {undefined}
 */
function anmeldungLoeschen(pruefungsanmeldung_id, termin_id, lehrveranstaltung_id)
{
    if (!confirm("Möchten Sie die Anmeldung wirklich löschen?"))
        return undefined;

    $.ajax({
        dataType: 'json',
        url: "./pruefungsanmeldung.json.php",
        type: "POST",
        data: {
            method: "anmeldungLoeschen",
            pruefungsanmeldung_id: pruefungsanmeldung_id
        },
        error: loadError,
        success: function(data){
            if(data.error === 'false' && data.result === true)
            {
                if(termin_id !== 'undefined' && lehrveranstaltung_id !== 'undefined')
                {
                    showAnmeldungen(termin_id, lehrveranstaltung_id, true);
                }
            }
            else
            {
                messageBox("message", data.errormsg, "red", "highlight", 10000);
            }
        }
    });
}

/**
 * Ändert den Status aller Anmeldungen eines Termins auf "bestätigt"
 * @param {type} termin_id ID des Prüfungstermines
 * @param {type} lehrveranstaltung_id ID der Lehrveranstaltung
 * @returns {undefined}
 */
function alleBestaetigen(termin_id, lehrveranstaltung_id)
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "alleBestaetigen",
			termin_id: termin_id,
			lehrveranstaltung_id: lehrveranstaltung_id
		},
		error: loadError,
		success: function(data){
			if(data.error === 'false' && data.result === true)
			{
				if(termin_id !== 'undefined' && lehrveranstaltung_id !== 'undefined')
				{
					showAnmeldungen(termin_id, lehrveranstaltung_id);
				}
			}
			else
			{
				messageBox("message", data.errormsg, "red", "highlight", 10000);
			}
		}
	});
}

/**
 * Anzeige eines DIVs zur Darstellung von Fehlermeldungen, etc.
 * @param {type} divId ID des DIVs in dem die Meldung dargestellt werden soll
 * @param {type} data Daten die angezeigt werden sollen
 * @param {type} color Hintergrundfarbe des DIVs
 * @param {type} effect Anzeigeeffekt (siehe jQuery UI)
 * @param {type} duration Dauer des Effektes
 * @returns {undefined}
 */
function messageBox(divId, data, color, effect, duration)
{
	$("#"+divId).html(data);
	$("#"+divId).effect(effect, {
		duration: duration,
		color: color
	}).delay(200).hide("clip", "fast");
}

/**
 * Lädt alle Studiengänge
 * @returns {undefined}
 */
function loadStudiengaenge()
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getStudiengaenge"
		},
		error: loadError,
		success: function(data){
			$("#stgListe").empty();
			if(data.error === 'false')
			{
				var liste = "<select id='select_studiengang' onchange='loadPruefungStudiengang();'><option><?php echo $p->t('pruefung/studiengangAuswaehlen'); ?></option>";
				data.result.forEach(function(e){
					var kuerzel = e.typ+e.kurzbz
					liste += "<option id='stg"+e.studiengang_kz+"' value='"+e.studiengang_kz+"'>"+e.bezeichnung+" ("+kuerzel.toUpperCase()+")</option>";
				});
				liste += "</select>";
				$("#stgListe").append(liste);
			}
			else
			{
				messageBox("message", data.errormsg, "red", "highlight", 10000);
			}
		}
	});
}

/**
 * Lädt alle Prüfungen zu einem Studiengang
 * @param {type} studiengang_kz Studiengangskennzahl
 * @returns {undefined}
 */
function loadPruefungStudiengang(studiengang_kz, studiensemester)
{
	if(studiengang_kz === undefined)
		studiengang_kz = $("#select_studiengang option:selected").val();

	if(studiensemester === undefined)
		studiensemester = $("#filter_studiensemester option:selected").val();

	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getPruefungenStudiengang",
			studiengang_kz: studiengang_kz,
			studiensemester: studiensemester
		},
		error: loadError,
		success: function(data){
			if(data.error === 'false')
			{
				$("#pruefungenListe").empty();
				if(data.result.length > 0)
				{
					var liste = "";
					data.result.forEach(function(e){
						liste += "<ul><li>"+e.bezeichnung+"<ul>";
						try
						{
							e.pruefung[0].termine.forEach(function(d){
								liste += "<li> <a onclick='showAnmeldungen(\""+d.pruefungstermin_id+"\", \""+e.lehrveranstaltung_id+"\");'>"+convertDateTime(d.von)+" "+convertDateTime(d.von, "time")+" - "+convertDateTime(d.bis, "time")+"</a></li>";
							});
						}
						catch(err)
						{
							var errmsg = err.message;
						}
						liste += "</ul></li></ul>";
					});
					$("#pruefungenListe").append(liste);
				}
				else
				{
					$("#pruefungenListe").html("<?php echo $p->t('pruefung/keinePruefungenVorhanden'); ?>");
				}
			}
			else
			{
				messageBox("message", data.errormsg, "red", "highlight", 10000);
			}
		}
	});
}

/**
 * Zeigt das Formularfeld zur Eingabe eines Kommentars in der Anmeldungsverwaltung an.
 * @param {String} vorname Vorname des Studenten
 * @param {String} nachname Nachname des Studenten
 * @param {int} pruefungsanmeldung_id ID der Anmeldung
 * @param {String} kommentar Kommentar zur Anmeldung (wenn bereits vorhanden)
 * @param {int} termin_id ID des Prüfungstermins
 * @param {int} lehrveranstaltung_id ID der Lehrveranstaltung
 * @returns {void}
 */
function showKommentar(vorname, nachname, pruefungsanmeldung_id, kommentar, termin_id, lehrveranstaltung_id)
{
	if(kommentar === "null")
		kommentar = "";
	$("#kommentar").html("<h2><?php echo $p->t('pruefung/kommentarZu'); ?>"+vorname+" "+nachname+"</h2><textarea id='kommentarText' rows='5' cols='20'>"+kommentar+"</textarea>");
	$("#kommentarSpeichernButton").html("<input type='button' value='<?php echo $p->t('pruefung/kommentarSpeichern'); ?>' onclick='saveKommentar(\""+pruefungsanmeldung_id+"\", \""+termin_id+"\", \""+lehrveranstaltung_id+"\");'>");
}

/**
 * Speichert ein Kommentar zu einer Anmeldung
 * @param {int} pruefungsanmeldung_id ID der Anmeldung
 * @param {int} termin_id ID des Termins
 * @param {int} lehrveranstaltung_id ID der Lehrveranstaltung
 * @returns {void}
 */
function saveKommentar(pruefungsanmeldung_id, termin_id, lehrveranstaltung_id)
{
	var kommentar = $("#kommentarText").val();
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "saveKommentar",
			pruefungsanmeldung_id: pruefungsanmeldung_id,
			kommentar: kommentar
		},
		error: loadError,
		success: function(data){
			messageBox("message", "<?php echo $p->t('pruefung/kommentarErfolgreichGespeichert'); ?>", "green", "highlight", 10000);
			showAnmeldungen(termin_id, lehrveranstaltung_id);
		}
	});
}

/**
 * lädt alle Prüfungstypen per AJAX aus der Datenbank
 * @param {boolean} abschluss
 * @returns {void}
 */
function loadPruefungstypen(abschluss)
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungstermin.json.php",
		type: "POST",
		data: {
			method: "loadPruefungstypen",
			abschluss: abschluss
		},
		error: loadError,
		success: function(data){
			var selectData = "";
			data.result.forEach(function(d){
				if(d.beschreibung === null)
					d.beschreibung = "";
				selectData += "<option value='"+d.pruefungstyp_kurzbz+"'>"+d.beschreibung+"</option>";
			});
			$('#pruefungsTyp').html(selectData);
		}
	});
}

/**
 * Lädt alle Studiensemester per AJAX aus der Datenbank
 * @returns {void}
 */
function loadStudiensemester()
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungstermin.json.php",
		type: "POST",
		data: {
			method: "loadStudiensemester",
            prevSemester: 5
		},
		error: loadError,
		success: function(data){
			var selectData = "";
			data.result.forEach(function(d){
				selectData += "<option "+((d.studiensemester_kurzbz === data.aktSem) ? "selected" : "")+" value='"+d.studiensemester_kurzbz+"'>"+d.studiensemester_kurzbz+"</option>";
			});
			$('#studiensemester').html(selectData);
			loadPruefungsfenster();
			loadLehrveranstaltungen();
		}
	});
}

/**
 * Prüft eine Variable ob diese NULL ist
 * @param {type} variable
 * @returns {boolean} TRUE, wenn die Variable NULL ist, ansonsten FALSE
 */
function is_null(variable)
{
	if(variable === null)
	{
		return true;
	}
	return false;
}

/**
 * Prüft eine Variable ob diese undefined ist
 * @param {type} variable
 * @returns {boolean} TRUE, wenn die Variable undefined ist, ansonsten FALSE
 */
function is_undefined(variable)
{
	if(variable === undefined)
	{
		return true;
	}
	return false;
}

/**
 * Prüft einen String ob dieser die Länge 0 hat
 * @param {string} string
 * @returns {boolean} TRUE, wenn die Länge des Strings 0 ist, ansonsten FALSE
 */
function is_empty_String(string)
{
	if(string.length === 0)
	{
		return true;
	}
	return false;
}

/**
 * Speichert einen Prüfungstermin
 * @returns {void}
 */
function savePruefungstermin()
{
	unmarkMissingFormEntry();
	var studiensemester_kurzbz = $("#studiensemester").val();
	var pruefungsfenster_id = $("#pruefungsfenster").val();
	var titel = $("#titel").val();
	var beschreibung = $("#beschreibung").val();
	var methode = $("#methode").val();
	var einzeln = $("#einzeln").prop("checked");
	var termine = [];
	var lehrveranstaltungen = [];
	var error = false;
	var mitarbeiter_uid = $("#mitarbeiter_uid").val();
	var pruefungsintervall = $("#pruefungsintervall").val();
	$("#prfTermin tr").each(function(i,v){
		var termin = {};
		$(v).find("input").each(function(j, w){
			switch (j) {
				case 0:
					termin.datum = $(w).val();
					break;
				case 1:
					termin.beginn = $(w).val();
					break;
				case 2:
					termin.ende = $(w).val();
					break;
				case 3:
					termin.min = $(w).val();
					break;
				case 4:
					termin.max = $(w).val();
					break;
				case 5:
					termin.sammelklausur = $(w).prop("checked");
					break;
			}
		});
		if(!checkTermin(termin))
		{
			error = true;
			markMissingFormEntry($(v).attr("id"));
		}
		if(!checkMinMaxTeilnehmer(termin.min, termin.max))
		{
			error = true;
			markMissingFormEntry("termin"+(i+1)+"min");
			markMissingFormEntry("termin"+(i+1)+"max");
		}
		if(einzeln)
		{
			if(!checkPruefungsintervall(pruefungsintervall, termin))
			{
				error = true;
				markMissingFormEntry("pruefungsintervall");
				markMissingFormEntry($(v).attr("id"));
			}
		}
		else
		{
			pruefungsintervall = false;
		}
		termine.push(termin);
	});


	$("#lvDropdowns select").each(function(i,v){
		if($(v).val() !== "null")
			lehrveranstaltungen.push($(v).val());
	});

	if(is_null(studiensemester_kurzbz) || is_undefined(studiensemester_kurzbz) || is_empty_String(studiensemester_kurzbz))
	{
		error = true;
		markMissingFormEntry("studiensemester");
	}
	if(is_null(pruefungsfenster_id) || is_undefined(pruefungsfenster_id) || is_empty_String(pruefungsfenster_id) || (pruefungsfenster_id === "null"))
	{
		error = true;
		markMissingFormEntry("pruefungsfenster");
	}
	if(is_null(titel) || is_undefined(titel) || is_empty_String(titel))
	{
		error = true;
		markMissingFormEntry("titel");
	}
	if(is_null(beschreibung) || is_undefined(beschreibung) || is_empty_String(beschreibung))
	{
		error = true;
		markMissingFormEntry("beschreibung");
	}
	if(is_null(methode) || is_undefined(methode) || is_empty_String(methode))
	{
		error = true;
		markMissingFormEntry("methode");
	}

	if(lehrveranstaltungen.length === 0)
	{
		error = true;
		markMissingFormEntry("lvDropdowns");
	}

	if(is_null(termine) || is_undefined(termine) || is_empty_String(termine))
	{
		error = true;
		markMissingFormEntry("prfTermin");
	}

	if(error)
	{
		messageBox("message", "<?php echo $p->t('pruefung/formulardatenNichtKorrekt'); ?>", "red", "highlight", 10000);
	}
	else
	{
		$.ajax({
			dataType: 'json',
			url: "./pruefungstermin.json.php",
			type: "POST",
			data: {
				method: "savePruefungstermin",
				studiensemester_kurzbz: studiensemester_kurzbz,
				pruefungsfenster_id: pruefungsfenster_id,
				titel: titel,
				beschreibung: beschreibung,
				methode: methode,
				einzeln: einzeln,
				termine: termine,
				lehrveranstaltungen: lehrveranstaltungen,
				mitarbeiter_uid: mitarbeiter_uid,
				pruefungsintervall: pruefungsintervall
			},
			error: loadError,
			success: function(data){
				unmarkMissingFormEntry();
				if(data.error === "false")
				{
					messageBox("message", "<?php echo $p->t('pruefung/pruefungErfolgreichGespeichert'); ?>", "green", "highlight", 10000);
					resetPruefungsverwaltung();
				}
				else
				{
					messageBox("message", data.errormsg, "red", "highlight", 10000);
				}
			}
		});
	}
}

/**
 * Fügt einem Element die Klasse "missingFormData" hinzu
 * CSS-Klasse "missingFormData" wird benötigt
 * @param {string} eleId ID des zu markierenden Formularfeldes
 * @returns {void}
 */
function markMissingFormEntry(eleId)
{
	$("#"+eleId).addClass("missingFormData");
	$(".modalOverlay").each(function(i,v){
		$(v).removeClass("modalOverlay");
	});
}

/**
 * Entfernt die Klasse "missingFormData" von einem Element.
 * @param {string} eleId ID des Formularfeldes
 * @returns {void}
 */
function unmarkMissingFormEntry()
{
	$(".missingFormData").each(function(i, v){
		$(v).removeClass("missingFormData");
	});
}

/**
 * Lädt alle Lehrveranstaltungen eine Mitarbeiters
 * @returns {void}
 */
function loadLehrveranstaltungen()
{
	var studiensemester_kurzbz = $("#studiensemester").val();
	//alert(studiensemester_kurzbz);
	var mitarbeiter_uid = $("#mitarbeiter_uid").val();
	$.ajax({
		dataType: 'json',
		url: "./pruefungstermin.json.php",
		type: "POST",
		data: {
			method: "getLehrveranstaltungenByMitarbeiter",
			mitarbeiter_uid: mitarbeiter_uid,
			studiensemester_kurzbz: studiensemester_kurzbz
		},
		error: loadError,
		success: function(data){
			var selectData = '<option value="null"><?php echo $p->t('pruefung/lehrveranstaltungAuswaehlen'); ?></option>';
			data.result.forEach(function(d){
				selectData += '<option value="'+d.lehrveranstaltung_id+'">'+d.studiengang_bezeichnung+' | <b>'+d.bezeichnung+'</b> ('+d.lehrform_kurzbz+')</option>';
			});
			$("#lvDropdown1").html(selectData);
		}
	});
}

/**
 * Lädt die Details zu eine Prüfung
 * @param {int} prfId ID der Prüfung
 * @returns {void}
 */
function loadPruefungsDetails(prfId)
{
	unmarkMissingFormEntry();
	$("#modalOverlay").addClass("modalOverlay");
	resetLehrveranstaltungen();
	var row = $("#row1").clone();
	resetTermine();
	$("#prfTermine").append(row);
	$.ajax({
		dataType: 'json',
		url: "./pruefungstermin.json.php",
		type: "POST",
		data: {
			method: "loadStudiensemester"
		},
		error: loadError,
	}).done(function(data){
		var selectData = "";
		data.result.forEach(function(d){
			selectData += "<option "+((d.studiensemester_kurzbz === data.aktSem) ? "selected" : "")+" value='"+d.studiensemester_kurzbz+"'>"+d.studiensemester_kurzbz+"</option>";
		});
		$('#studiensemester').html(selectData);
	}).always(function(){
		var studiensemester_kurzbz = $("#studiensemester option:selected").val();
		$.ajax({
			dataType: 'json',
			url: "./pruefungstermin.json.php",
			type: "POST",
			data: {
				method: "getPruefungsfensterByStudiensemester",
				studiensemester_kurzbz: studiensemester_kurzbz
			},
			error: loadError
		}).done(function(data){
			if(data.result.length === 0)
			{
				messageBox("message", "<?php echo $p->t('pruefung/keinePruefungsfensterGespeichert'); ?>", "red", "highlight", 10000);
				$("#pruefungsfenster").html("<option value='null'></option>");
			}
			else
			{
				writePruefungsfenster(data);
				setDatePicker();
			}
		}).always(function(){
			var studiensemester_kurzbz = $("#studiensemester").val();
			var mitarbeiter_uid = $("#mitarbeiter_uid").val();
			$.ajax({
				dataType: 'json',
				url: "./pruefungstermin.json.php",
				type: "POST",
				data: {
					method: "getLehrveranstaltungenByMitarbeiter",
					mitarbeiter_uid: mitarbeiter_uid,
					studiensemester_kurzbz: studiensemester_kurzbz
				},
				error: loadError
			}).done(function(data){
				var selectData = '<option value="null"><?php echo $p->t('pruefung/lehrveranstaltungAuswaehlen'); ?></option>';
				data.result.forEach(function(d){
					selectData += '<option value="'+d.lehrveranstaltung_id+'">'+d.studiengang_bezeichnung+' | <b>'+d.bezeichnung+'</b> ('+d.lehrform_kurzbz+')</option>';
				});
				$("#lvDropdown1").html(selectData);
			}).always(function(){
				$.ajax({
					dataType: 'json',
					url: "./pruefungsanmeldung.json.php",
					type: "POST",
					data: {
						method: "loadPruefung",
						pruefung_id: prfId
					},
					error: loadError
				}).done(function(data){
					var copy = $("#lvDropdown1").clone();
					$("#lvDropdowns").empty();
					$("#lvDropdowns").html(copy);
					$("#lvDropdowns").append("<br/>");
					var result = data.result[0];
					$("#titel").val(result.pruefung.titel);
					$("#beschreibung").val(result.pruefung.beschreibung);
					$("#studiensemester").val(result.pruefung.studiensemester_kurzbz);
					$("#pruefungsfenster").val(result.pruefung.pruefungsfenster_id);
					$("#methode").val(result.pruefung.methode);
					var i = 0;
					$("#termin1").closest("tr").remove();
					terminHinzufuegen("span");
					data.result.forEach(function(d){
						i++;
						var lv = "";
						if(d.lehrveranstaltung !== undefined)
						{
							lv = "<span value='"+d.lehrveranstaltung.lehrveranstaltung_id+"'>"+d.lehrveranstaltung.studiengang.kurzbzlang+" | <b>"+d.lehrveranstaltung.bezeichnung+"</b> ("+d.lehrveranstaltung.lehrform_kurzbz+")</span><a href='#' onclick='deleteLehrveranstaltungFromPruefung(\""+d.lehrveranstaltung.lehrveranstaltung_id+"\", \""+d.pruefung.pruefung_id+"\");'> <?php echo $p->t('global/löschen'); ?></a><br />";
						}
						else
						{
							lv = "<span><?php echo $p->t('pruefung/keineLehrveranstaltungenVorhanden'); ?></span><br />";
						}
						$("#lvDropdowns").children().first().before(lv);
						var j = 0;
						d.pruefung.termine.forEach(function(t){
							j++;
							if(i===1)
							{
								var date = convertDateTime(t.von, "date");
								var von = convertDateTime(t.von, "time");
								var bis = convertDateTime(t.bis, "time");
								var min = (t.min === null) ? "" : t.min;
								var max = (t.max === null) ? "" : t.max;
								$("#termin"+j+"Id").text(t.pruefungstermin_id);
								$("#termin"+j).text(date);
								$("#termin"+j+"Beginn").text(von);
								$("#termin"+j+"Ende").text(bis);
								$("#termin"+j+"min").text(min);
								$("#termin"+j+"max").text(max);
								if(t.sammelklausur)
								{
									$("#termin"+j+"sammelklausur").text("true");
								}
								else
								{
									$("#termin"+j+"sammelklausur").text("false");
								}
								//$("#termin"+j+"Id").closest("tr").append("<td><a href='#' onclick='terminLoeschen(\""+d.pruefung.pruefung_id+"\", \""+t.pruefungstermin_id+"\");'><?php //echo $p->t('global/löschen'); ?></a></td>");
                                $("#termin"+j+"Id").closest("tr").append("<td><a href='#' onclick='window.open(\"pruefungstermin_loeschen.php?pruefung_id="+d.pruefung.pruefung_id+"&termin_id="+t.pruefungstermin_id+"\",\"delete\",\"height=600,width=500,toolbar=no,titlebar=no,status=no,menubar=no\");'><?php echo $p->t('global/löschen'); ?></a></td>");
                                $("#termin"+j+"Id").closest("tr").append("<td><a href='#' onclick='window.open(\"pruefungstermin_aendern.php?termin_id="+t.pruefungstermin_id+"\",\"edit\",\"height=600,width=500,toolbar=no,titlebar=no,status=no,menubar=no\");'><?php echo $p->t('global/editieren'); ?></a></td>");
								terminHinzufuegen("span");
							}
						});
					});
					var ele = document.getElementById("lvDropdowns");
					$("#prfTermin tr").last().remove();
					if(result.pruefung.einzeln)
					{
						$("#einzeln").prop("checked", "checked");
						$("#pruefungsintervall").parent().parent().css("visibility", "visible");
						$("#pruefungsintervall").val(result.pruefung.pruefungsintervall);
					}
					else
					{
						$("#einzeln").removeAttr("checked");
					}
				}).always(function(){
					$("#buttonSave").attr("onclick", "updatePruefung(\""+prfId+"\");");
					if($("#buttonStorno").length === 0)
					{
						$("#buttonSave").after("<input type='button' id='buttonStorno' onclick='stornoPruefung(\""+prfId+"\");' value='<?php echo $p->t('pruefung/pruefungStornieren'); ?>'></input>");
					}
					else
					{
						$("#buttonStorno").attr("onclick", "stornoPruefung(\""+prfId+"\");");
					}
					$("#modalOverlay").removeClass("modalOverlay");
				});
			});
		});
	});
}

/**
 * Setzt den Bereich Lehrveranstaltungen in der Anmeldungsverwaltung auf den Ausgangszustand
 * @returns {void}
 */
function resetLehrveranstaltungen()
{
	$("#lvDropdown1").attr("onchange", "lehrveranstaltungDropdownhinzufuegen(this, false);")
	$("#lvDropdowns").children().each(function(i,v){
		$("#lvDropdown"+(i+2)).next().remove();
		$("#lvDropdown"+(i+2)).remove();
	});
	$("#lvDropdown1").prevAll().each(function(i,v){
		$(v).remove();
	});
}

/**
 * Setzt den Bereich Termine in der Ammeldungsverwaltung auf den Ausgangszustand
 * @returns {void}
 */
function resetTermine()
{
	$("#prfTermin tr").each(function(i,v){
	$(v).remove();
	});
}

/**
 * Aktualisiert die Daten einer Prüfung in der Datenbank
 * @param {int} prfId ID der Prüfung
 * @returns {void}
 */
function updatePruefung(prfId)
{
	$("#modalOverlay").addClass("modalOverlay");
	unmarkMissingFormEntry();
	var studiensemester_kurzbz = $("#studiensemester").val();
	var pruefungsfenster_id = $("#pruefungsfenster").val();
	var titel = $("#titel").val();
	var beschreibung = $("#beschreibung").val();
	var methode = $("#methode").val();
	var einzeln = $("#einzeln").prop("checked");
	var termine = [];
	var termineNeu = [];
	var lehrveranstaltungen = [];
	var mitarbeiter_uid = $("#mitarbeiter_uid").val();
	var pruefungsintervall = $("#pruefungsintervall").val();
	var error = false;
	$('#prfTermin tr').has("span").each(function(i,v){
		var termin = {};
		$(v).find("span").each(function(j, w){
			switch (j) {
				case 0:
					termin.pruefungstermin_id = $(w).text();
					break;
				case 1:
					termin.datum = $(w).text();
					break;
				case 2:
					termin.beginn = $(w).text();
					break;
				case 3:
					termin.ende = $(w).text();
					break;
				case 4:
					termin.min = $(w).text();
					break;
				case 5:
					termin.max = $(w).text();
					break;
				case 6:
					termin.sammelklausur = $(w).prop("checked");
					break;
			}
		});
		if(!checkTermin(termin))
		{
			error = true;
			markMissingFormEntry($(v).attr("id"));
		}
		if(einzeln)
		{
			if(!checkPruefungsintervall(pruefungsintervall, termin))
			{
				error = true;
				markMissingFormEntry("pruefungsintervall");
				markMissingFormEntry($(v).attr("id"));
			}
		}
		termine.push(termin);
	});

	$('#prfTermin tr').has("input").each(function(i,v){
		var termin = {};
		$(v).find("input").each(function(j, w){
			switch (j) {
				case 0:
					termin.datum = $(w).val();
					break;
				case 1:
					termin.beginn = $(w).val();
					break;
				case 2:
					termin.ende = $(w).val();
					break;
				case 3:
					termin.min = $(w).val();
					break;
				case 4:
					termin.max = $(w).val();
					break;
				case 5:
					termin.sammelklausur = $(w).prop("checked");
					break;
			}
		});
		if(!checkTermin(termin))
		{
			error = true;
			markMissingFormEntry($(v).attr("id"));
		}
		if(einzeln)
		{
			if(!checkPruefungsintervall(pruefungsintervall, termin))
			{
				error = true;
				markMissingFormEntry("pruefungsintervall");
				markMissingFormEntry($(v).attr("id"));
			}
		}
		termineNeu.push(termin);
	});

	$("#lvDropdowns select").each(function(i,v){
		if($(v).val() !== "null")
			lehrveranstaltungen.push($(v).val());
	});

	if(is_null(studiensemester_kurzbz) || is_undefined(studiensemester_kurzbz) || is_empty_String(studiensemester_kurzbz))
	{
		error = true;
		markMissingFormEntry("studiensemester");
	}
	if(is_null(pruefungsfenster_id) || is_undefined(pruefungsfenster_id) || is_empty_String(pruefungsfenster_id) || (pruefungsfenster_id === "null"))
	{
		error = true;
		markMissingFormEntry("pruefungsfenster");
	}
	if(is_null(titel) || is_undefined(titel) || is_empty_String(titel))
	{
		error = true;
		markMissingFormEntry("titel");
	}
	if(is_null(beschreibung) || is_undefined(beschreibung) || is_empty_String(beschreibung))
	{
		error = true;
		markMissingFormEntry("beschreibung");
	}
	if(is_null(methode) || is_undefined(methode) || is_empty_String(methode))
	{
		error = true;
		markMissingFormEntry("methode");
	}
	if(lehrveranstaltungen[0] === "null")
	{
		error = true;
		markMissingFormEntry("lvDropdowns");
	}

	if(error)
	{
		messageBox("message", "<?php echo $p->t('pruefung/formulardatenNichtKorrekt'); ?>", "red", "highlight", 10000);
	}
	else
	{
		$.ajax({
			dataType: 'json',
			url: "./pruefungstermin.json.php",
			type: "POST",
			data: {
				method: "updatePruefungstermin",
				pruefung_id: prfId,
				studiensemester_kurzbz: studiensemester_kurzbz,
				pruefungsfenster_id: pruefungsfenster_id,
				titel: titel,
				beschreibung: beschreibung,
				methode: methode,
				einzeln: einzeln,
				termine: termine,
				termineNeu: termineNeu,
				lehrveranstaltungen: lehrveranstaltungen,
				mitarbeiter_uid: mitarbeiter_uid,
				pruefungsintervall: pruefungsintervall
			},
			error: loadError
		}).done(function(data){
			unmarkMissingFormEntry();
			if(data.error === "false")
			{
				messageBox("message", "<?php echo $p->t('pruefung/pruefungErfolgreichGespeichert'); ?>", "green", "highlight", 10000);
				resetPruefungsverwaltung();
			}
			else
			{
				messageBox("message", data.errormsg, "red", "highlight", 10000);
			}
		}).always(function(){
			loadAllPruefungen();
			$("#modalOverlay").removeClass("modalOverlay");
		});
	}
}

/**
 * Löscht eine Lehrveranstaltung von einer Prüfung
 * @param {int} lvId ID der Lehrveranstaltung
 * @param {int} pruefung_id ID der Prüfung
 * @returns {void}
 */
function deleteLehrveranstaltungFromPruefung(lvId, pruefung_id)
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungstermin.json.php",
		type: "POST",
		data: {
			method: "deleteLehrveranstaltungFromPruefung",
			pruefung_id: pruefung_id,
			lehrveranstaltung_id: lvId
		},
		error: loadError
	}).done(function(data){
		if(data.error === "false")
		{
			messageBox("message", "<?php echo $p->t('pruefung/lvErfolgreichEntfernt'); ?>", "green", "highlight", 10000);
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 10000);
		}
	}).always(function(){
		loadPruefungsDetails(pruefung_id);
	});
}

/**
 * Storniert eine Prüfung
 * @param {int} pruefung_id ID der Prüfung
 * @returns {void}
 */
function stornoPruefung(pruefung_id)
{
	$("#modalOverlay").addClass("modalOverlay");
	$.ajax({
		dataType: 'json',
		url: "./pruefungstermin.json.php",
		type: "POST",
		data: {
			method: "stornoPruefung",
			pruefung_id: pruefung_id
		},
		error: loadError
	}).done(function(data){
		if(data.error === "false")
		{
			messageBox("message", "<?php echo $p->t('pruefung/pruefungStorniert'); ?>", "green", "highlight", 10000);
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 10000);
		}
	}).always(function(){
		loadAllPruefungen();
		resetPruefungsverwaltung();
	});
}

/**
 * Löscht einen Termin
 * @param {int} pruefung_id ID der Prüfung
 * @param {int} pruefungstermin_id ID des Prüfungstermines
 * @returns {undefined}
 */
function terminLoeschen(pruefung_id, pruefungstermin_id)
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungstermin.json.php",
		type: "POST",
		data: {
			method: "deleteTermin",
			pruefung_id: pruefung_id,
			pruefungstermin_id: pruefungstermin_id
		},
		error: loadError
	}).done(function(data){
		if(data.error === "false")
		{
			messageBox("message", "<?php echo $p->t('pruefung/terminGeloescht'); ?>", "green", "highlight", 10000);
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 10000);
		}
	}).always(function(){
		loadPruefungsDetails(pruefung_id);
		loadAllPruefungen();
	});
}

/**
 * Löscht einen Termin ohne im Anschluss die Prüfungen neu zu laden
 * @param {int} pruefung_id ID der Prüfung
 * @param {int} pruefungstermin_id ID des Prüfungstermines
 * @returns {undefined}
 */
function terminLoeschenOhneLaden(pruefung_id, pruefungstermin_id)
{
    $.ajax({
        dataType: 'json',
        url: "./pruefungstermin.json.php",
        type: "POST",
        data: {
            method: "deleteTermin",
            pruefung_id: pruefung_id,
            pruefungstermin_id: pruefungstermin_id
        },
        error: loadError
    }).done(function(data){
        if(data.error === "false")
        {
            messageBox("message", "<?php echo $p->t('pruefung/terminGeloescht'); ?>", "green", "highlight", 10000);
        }
        else
        {
            messageBox("message", data.errormsg, "red", "highlight", 10000);
        }
    });
}

/**
 * Lädt alle Prüfungen
 * @returns {void}
 */
function loadAllPruefungen()
{
	var uid = $("#mitarbeiter_uid").val();
	$.ajax({
		dataType: 'json',
		url: "./pruefungstermin.json.php",
		type: "POST",
		data: {
			method: "getAllPruefungen",
			uid: uid
		},
		error: loadError
	}).done(function(data){
		if(data.error != 'true')
		{
			$("#prfTable tbody").first().html("");
			var tableRow = "";
			data.result.forEach(function(e){
				if(e.storniert === false)
				{
					tableRow = "<tr><td><a href='#' onclick='loadPruefungsDetails(\""+e.pruefung_id+"\")'>"+e.titel+"</a></td>";
					tableRow += "<td>"+e.studiensemester_kurzbz+"</td>";
					tableRow += "<td>";
					e.lehrveranstaltungen.forEach(function(f){
						tableRow += f.bezeichnung+"<br/>";
					});
					tableRow+="</td>";
					tableRow+="<td>";
					e.termine.forEach(function(f){
						tableRow += convertDateTime(f.von, "date")+" von "+convertDateTime(f.von, "time")+" bis "+convertDateTime(f.bis, "time")+"<br/>";
					});
					tableRow+="</td>";
					tableRow += "<td>"+e.methode+"</td>";
					tableRow += "<td>"+e.einzeln+"</td>";
					tableRow += "<td>"+e.mitarbeiter_uid+"</td>";
					tableRow += "<td>"+e.storniert+"</td>";
					tableRow += "</tr>";
					$("#prfTable tbody").first().append(tableRow);
				}
			});
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 10000);
		}
	}).always(function(event, xhr, settings){
		if($("#prfTable")[0].hasInitialized !== true)
		{
			$("#prfTable").tablesorter({
				widgets: ["zebra"],
				sortList: [[1,0]]
			});
		}
		else
		{
			$("#prfTable").trigger("updateAll");
			var sorting = [[1,0],[0,0]];
			$("#prfTable").trigger("sorton",[sorting]);
		}
	});
}

/**
 * Prüft die Daten eines Termins auf deren Richtigkeit
 * Prüft ob die Beginnzeit vor der Endzeit liegt und ob beide Daten mindestens 14 Tage in der Zukunft liegen.
 * @param {Object} termin Object mit den Attributen datum (DD.MM.YYYY), beginn (HH:mm) und ende (HH:mm)
 * @returns {Boolean} TRUE, wenn die Daten korrekt sind, ansonsten FALSE
 */
function checkTermin(termin)
{
	var heute = new Date();
	var aktTime = new Date(heute.getTime() + (<?php echo $terminfrist ?>*24*60*60*1000));
	var vonTime = stringToDate(termin.datum, termin.beginn);
	var bisTime = stringToDate(termin.datum, termin.ende);

	if(!(aktTime < vonTime))
	{
		return false;
	}
	else if(!(vonTime < bisTime))
	{
		return false;
	}
	return true;
}

function checkPruefungsintervall(intervall, termin)
{
	var beginn = stringToDate(termin.datum, termin.beginn);
	var ende = stringToDate(termin.datum, termin.ende);
	var maxTeilnehmer = ((ende - beginn) / 1000 / 60 / intervall);
	if(maxTeilnehmer < termin.max)
	{
		return false;
	}
	return true;
}

/**
 * Formatiert eine Datum von String in eine Date-Objekt
 * @param {string} datum Ein Datum als String im Format "DD.MM.YYYY"
 * @param {string} time eine Uhrzeit als String im Format " HH:mm:ss"
 * @returns {Date} Dateobjekt
 */
function stringToDate(datum, time)
{
	datum = datum.split(".");
	time = time.split(":");
	time = new Date(datum[2], (datum[1]-1), datum[0], time[0], time[1]);
	return time;
}

/**
 * Setzt die Oberfläche der Prüfungsverwaltung auf den Ausgangszustand zurück
 * @returns {void}
 */
function resetPruefungsverwaltung()
{
	loadAllPruefungen();
	$("#titel").val("");
	$("#beschreibung").val("");
	loadStudiensemester();
	$("#methode").val("");
	resetLehrveranstaltungen();
	resetTermine();
	$("#pruefungsintervall").val("15");
	$("#einzeln").removeAttr("checked");
	$("#pruefungsintervall").closest("tr").css("visibility", "hidden");
	$("#modalOverlay").removeClass("modalOverlay");
	$("#buttonSave").attr("onclick", "savePruefungstermin();");
}

/**
 * Prüft ob eine Zahl "min" kleiner als eine Zahl "max" ist.
 * @param {String} min
 * @param {String} max
 * @returns {Boolean} TRUE, wenn min kleiner als max ist und beide einen positiven Wert haben
 */
function checkMinMaxTeilnehmer(min, max)
{
	if(((min !== null) && (max !== null)) && ((min !== "") && (max !== "")))
	{
		min = parseInt(min);
		max = parseInt(max);
		if(max < min)
		{
			return false;
		}
		if(max < 0)
		{
			return false;
		}
		if(min < 0)
		{
			return false;
		}
		if(isNaN(min))
		{
			return false;
		}
		if(isNaN(max))
		{
			return false;
		}
	}
	return true;
}

function changeStateOfRaumDropdown()
{
	if($("#raum input[type=checkbox]").prop("checked") === true)
	{
		$("#raeumeDropdown").css("visibility", "hidden");
		$("#raeumeDropdown").prev().css("visibility", "hidden");
	}
	else
	{
		$("#raeumeDropdown").css("visibility", "visible");
		$("#raeumeDropdown").prev().css("visibility", "visible");
	}
}

function saveRaum(terminId, lehrveranstaltung_id)
{
	var ort_kurzbz;
	if($("#raum input[type=checkbox]").prop("checked") === true)
	{
		ort_kurzbz = "buero";
	}
	else
	{
		ort_kurzbz = $("#raeumeDropdown").val();
	}
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "saveRaum",
			ort_kurzbz: ort_kurzbz,
			terminId: terminId
		},
		error: loadError
	}).done(function(data){
		$("#raumDialog").dialog("close");
		showAnmeldungen(terminId, lehrveranstaltung_id);
	});
}

function getRaeume(terminId)
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getAllFreieRaeume",
			terminId: terminId
		},
		error: loadError
	}).done(function(data){
		var liste = "";
		data.result.forEach(function(d){
			liste += "<option value="+d.ort_kurzbz+">"+d.ort_kurzbz+"</option>"
		});
		$("#raeumeDropdown").html(liste);
	});
}
