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
function getUrlVars() {
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
			$("#prfTermin").append('<tr id="row'+count+'"><td><input type="text" id="termin'+count+'" name="termin[]"></td><td><input type="time" id="termin'+count+'Beginn" placeholder="00:00" name="termin'+count+'Beginn[]"></td><td><input type="time" id="termin'+count+'Ende" placeholder="00:00" name="termin'+count+'Ende[]"></td><td><input type="number" id="termin'+count+'min" placeholder="0" min="0" name="termin'+count+'minTeilnehmer[]"></td><td><input type="number" id="termin'+count+'max" placeholder="10" min="0" name="termin'+count+'maxTeilnehmer[]"></td></tr>');
			setDatePicker();
			break;
		case 'span':
			var count = $("#prfTermin tr").length+1;
			$("#prfTermin").append('<tr id="row'+count+'"><td><span style="visibility: hidden;" id="termin'+count+'Id"></span><span id="termin'+count+'" name="termin[]"></span></td><td><span id="termin'+count+'Beginn" name="termin'+count+'Beginn[]"></span></td><td><span id="termin'+count+'Ende" name="termin'+count+'Ende[]"></span></td><td><span id="termin'+count+'min" name="termin'+count+'minTeilnehmer[]"></span></td><td><span id="termin'+count+'max" name="termin'+count+'maxTeilnehmer[]"></span></td></tr>');
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
		$("#lvDropdowns").append("</br>");
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
		error: loadError
	}).success(function(data){
		if(data.result.length === 0)
		{
			messageBox("message", "Keine Prüfungsfenster vorhanden", "red", "highlight", 1000);
			$("#pruefungsfenster").html("<option value='null'></option>");
		}
		else
		{
			writePruefungsfenster(data);
			setDatePicker();
		}
	}).complete(function(){

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
			$("#pruefungsfenster").html("<option>Kein Prüfungsfenster vorhanden.</option>");
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
			method: "getPruefungByLv"
		},
		error: loadError
	}).success(function(data){
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
			$("#pruefungen").append("<td align='center' colspan='6'>Keine Daten vorhanden.</td>");
		}
	}).complete(function(event, xhr, settings){
		setTablesorter("table1");
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
			method: "getPruefungByLvFromStudiengang"
		},
		error: loadError
	}).success(function(data){
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
			$("#pruefungenStudiengang").append("<td align='center' colspan='6'>Keine Daten vorhanden.</td>");
		}
	}).complete(function(event, xhr, settings){
		setTablesorter("table2");
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
		error: loadError
	}).success(function(data){
		data.result.pruefungen.forEach(function(e){
			if(e.pruefung.storniert === false)
			{
				var table = writePruefungsTable(e, data, false);
				$("#pruefungenGesamt").append(table);
			}
		});
	}).complete(function(event, xhr, settings){
		setTablesorter("table3");
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
	var button = "";
	row += "<tr><td>"+e.organisationseinheit+"</td><td style='cursor: pointer; text-decoration: underline;' onclick='showPruefungsDetails(\""+e.pruefung.pruefung_id+"\",\""+e.lehrveranstaltung[0].lehrveranstaltung_id+"\");'>"+e.lehrveranstaltung[0].bezeichnung+"</td><td>";
	e.pruefung.termine.forEach(function(d){
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
		termin = termin[0].split("-");
		termin = new Date(termin[0], termin[1]-1,termin[2]);
		var frist = termin;
		termin = termin.getDate()+"."+(termin.getMonth()+1)+"."+termin.getFullYear();
		frist = frist.getTime();
		frist = frist - (3*24*60*60*1000);
		var fristDate = new Date(frist);
		frist = fristDate.getDate()+"."+(fristDate.getMonth()+1)+"."+fristDate.getFullYear();

		if(fristDate < new Date())
		{
			if(!storno)
				button = "<p><span style='display: inline-block; width: 155px;'>Frist abgelaufen</span></br>";
		}
		else if(anmeldung || e.lehrveranstaltung[0].angemeldet)
		{
			if(storno)
			{
				button = "<p><span style='display: inline-block; width: 155px;'>Stornieren (bis "+frist+"): </span><input style='width: 90px;' type='button' value='"+termin+"' onclick='stornoAnmeldung(\""+anmeldung_id+"\");'></br>";
			}
			else
			{
				button = "<p><span style='display: inline-block; width: 155px;'>Anmelden (bis "+frist+"): </span><input style='width: 90px;' type='button' value='"+termin+"' onclick='openDialog(\""+e.lehrveranstaltung[0].lehrveranstaltung_id+"\", \""+d.pruefungstermin_id+"\", \""+e.lehrveranstaltung[0].bezeichnung+"\", \""+d.von+"\", \""+d.bis+"\");'></p>";

			}
		}
		else
		{
			button = "<p><span style='display: inline-block; width: 155px;'>Anmelden (bis "+frist+"): </span><input style='width: 90px;' type='button' value='"+termin+"' onclick='openAnmeldung(\""+e.lehrveranstaltung[0].lehrveranstaltung_id+"\", \""+e.pruefung.studiensemester_kurzbz+"\");'></p>";
		}
		
		row += button;

		if(d.max === null)
		{
			teilnehmer += "unbegrenzt</br>";
		}
		else
		{
			teilnehmer += "<p><span style='line-height: 24px'>"+(d.max - d.teilnehmer)+"/"+d.max+"</span></p>";
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
			error: loadError
		}).success(function(data){
			data.result.forEach(function(e){
				if(e.lehrveranstaltung.lehrveranstaltung_id === lvId)
				{
					var p = e.pruefung;
					var l = e.lehrveranstaltung
					$("#prfTyp").html(p.pruefungstyp_kurzbz);
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
		});
	}
	else
	{
		$("#prfTermine").attr("disabled", true);
		$("#prfTermine").html("<option>Zuerst Prüfung auswählen.</option>");
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
	var lehrveranstaltung_id = $("#lehrveranstaltungHidden").val();
	var termin_id = $("#terminHidden").val();
	var bemerkungen = $("#anmeldungBemerkung").val();
	
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "saveAnmeldung",
			termin_id: termin_id,
			lehrveranstaltung_id: lehrveranstaltung_id,
			bemerkung: bemerkungen
		},
		error: loadError
	}).success(function(data){
		if(data.error === 'false')
		{
			messageBox("message", data.result, "green", "highlight", 1000);
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 1000);
		}
		resetForm();
	}).complete(function(event, xhr, settings){
		$("#saveDialog").dialog("close");
		refresh();
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
			error: loadError
		}).success(function(data){
			if(data.error === 'false')
			{
				messageBox("message", data.result, "green", "highlight", 1000);
			}
			else
			{
				messageBox("message", data.errormsg, "red", "highlight", 1000);
			}
		}).complete(function(event, xhr, settings){
			refresh();
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
 * @returns {undefined}
 */
function showAnmeldungen(pruefungstermin_id, lehrveranstaltung_id)
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
		error: loadError
	}).success(function(data){
		writeAnmeldungen(data);
	}).complete(function(event, xhr, settings){
		$("#sortable").sortable();
		$("#sortable").disableSelection();
	});
}

function writeAnmeldungen(data)
{
	console.log(data);
	if(data.error === 'false')
	{
		var terminId = data.result.anmeldungen[0].pruefungstermin_id;
		var pruefung_id = data.result.anmeldungen[0].pruefung_id;
		var lehrveranstaltung_id = data.result.anmeldungen[0].lehrveranstaltung_id;
		var ort_kurzbz = data.result.ort_kurzbz;
		var liste = "<ul id='sortable'>";
		var count = 0;
		data.result.anmeldungen.forEach(function(d){
			count++;
			var vorname = d.student.vorname !== "null" ? d.student.vorname : "";
			var nachname = d.student.nachname !== "null" ? d.student.nachname : "";
			switch(d.status_kurzbz)
			{
				case 'angemeldet':
					liste += "<li class='ui-state-default' id='"+d.student.uid+"'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span><a href='#' onclick='showKommentar(\""+vorname+"\",\""+nachname+"\", \""+d.pruefungsanmeldung_id+"\", \""+d.kommentar+"\", \""+terminId+"\", \""+lehrveranstaltung_id+"\");'>"+vorname+" "+nachname+"</a>";
					liste += "<div style='width: 3%; text-align: right;'>"+count+"</div><div style='text-align: center; width: 25%;'><input style='vertical-align: top; height: 24px;' type='button' value='Bestätigen' onclick='anmeldungBestaetigen(\""+d.pruefungsanmeldung_id+"\", \""+terminId+"\", \""+lehrveranstaltung_id+"\");'></div>";
					if(d.wuensche !== null)
					{
						liste += "<div class='anmerkungInfo'><a href='#' title='Anmerkung des Studenten:</br>"+d.wuensche+"'><img style='width: 20px;' src='../../../../skin/images/button_lvinfo.png'></a></div>";
					}
					liste += "</li>";
					break;
				case 'bestaetigt':
					liste += "<li class='ui-state-default' id='"+d.student.uid+"'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span><a href='#' onclick='showKommentar(\""+vorname+"\",\""+nachname+"\", \""+d.pruefungsanmeldung_id+"\", \""+d.kommentar+"\", \""+terminId+"\", \""+lehrveranstaltung_id+"\");'>"+vorname+" "+nachname+"</a>";
					liste += "<div style='width: 2%; text-align: right;'>"+count+"</div><div style='text-align: center; width: 20%;'><a href='#' title='Satusänderung von: "+d.statusupdatevon+"'>bestätigt</a></div>";
					if(d.wuensche !== null)
					{
						liste += "<div class='anmerkungInfo'><a href='#' title='Anmerkung des Studenten:</br>"+d.wuensche+"'><img style='width: 20px;' src='../../../../skin/images/button_lvinfo.png'></a></div>";
					}

					break;
				default:
					break;
			}

		});
		liste += "</ul>";
		$("#reihungSpeichernButton").html("<input type='button' value='Reihung speichern' onclick='saveReihung(\""+terminId+"\", \""+lehrveranstaltung_id+"\");'>");
		$("#anmeldeDaten").html(liste);
		if(ort_kurzbz !== null)
		{
			$("#raumLink").html("<span>Prüfungsraum: </span>"+ort_kurzbz);
		} 
		else
		{
			$("#raumLink").html("<a href='#' onclick='openRaumDialog(\""+terminId+"\", \""+lehrveranstaltung_id+"\");'>Prüfungsort</a>");
		}
		
		$(document).tooltip({
			position: {
				at: "right center",
				my: "left+15 center"
			}
		});
	}
	else
	{
		$("#anmeldeDaten").empty();
		$("#reihungSpeichernButton").empty();
		$("#kommentar").empty();
		$("#kommentarSpeichernButton").empty();
		$("#raumLink").empty();
		messageBox("message", data.errormsg, "red", "highlight", 1000);
	}
}

function openRaumDialog(terminId, lehrveranstaltung_id)
{
	getRaeume(terminId);
	$("#raum").html('<h2>Prüfungsraum</h2><input onChange="changeStateOfRaumDropdown();" type="checkbox" /><span> im Büro</span></br><span style="font-weight: bold;">Raum: </span><select id="raeumeDropdown"></select>');
	$("#raumSpeichernButton").html("<input type='button' value='Raum speichern' onclick='saveRaum(\""+terminId+"\", \""+lehrveranstaltung_id+"\");'/>");
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
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "saveReihung",
			reihung: reihung
		},
		error: loadError
	}).success(function(data){
		if(data.error === 'false' && data.result === true)
		{
			messageBox("message", "Reihung erfolgreich geändert.", "green", "highlight", 1000);
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 1000);
		}
	}).complete(function(){
		showAnmeldungen(terminId, lehrveranstaltung_id);
	});
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
		error: loadError
	}).success(function(data){
		if(data.error === 'false' && data.result === true)
		{
			if(termin_id !== 'undefined' && lehrveranstaltung_id !== 'undefined')
			{
				showAnmeldungen(termin_id, lehrveranstaltung_id);
			}
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 1000);
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
		error: loadError
	}).success(function(data){
		console.log(data);
		$("#stgListe").empty();
		if(data.error === 'false')
		{
			var liste = "";	
			data.result.forEach(function(e){
				var kuerzel = e.typ+e.kurzbz
				liste += "<li id='stg"+e.studiengang_kz+"'><span class='studiengang'><a href='#' onclick='loadPruefungStudiengang(\""+e.studiengang_kz+"\");'>"+e.bezeichnung+" ("+kuerzel.toUpperCase()+")</a></span></li>";
			});
			$("#stgListe").append(liste);
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 1000);
		}
	});
}

/**
 * Lädt alle Prüfungen zu einem Studiengang
 * @param {type} studiengang_kz Studiengangskennzahl
 * @returns {undefined}
 */
function loadPruefungStudiengang(studiengang_kz)
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getPruefungenStudiengang",
			studiengang_kz: studiengang_kz
		},
		error: loadError
	}).success(function(data){
		console.log(data);
		if(data.error === 'false')
		{
			$("#pruefungenListe").empty();
			if(data.result.length > 0)
			{
				var liste = "";
				data.result.forEach(function(e){
					liste += "<ul><li>"+e.bezeichnung+"<ul>";
					e.pruefung[0].termine.forEach(function(d){
						liste += "<li> <a onclick='showAnmeldungen(\""+d.pruefungstermin_id+"\", \""+e.lehrveranstaltung_id+"\");'>"+convertDateTime(d.von)+" "+convertDateTime(d.von, "time")+" - "+convertDateTime(d.bis, "time")+"</a></li>";
					});
					liste += "</li></ul></ul>";
				});
				$("#pruefungenListe").append(liste);
			}
			else
			{
				$("#pruefungenListe").html("Keine Prüfungen vorhanden.");
			}
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 1000);
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
	$("#kommentar").html("<h2>Kommentar zu "+vorname+" "+nachname+"</h2><textarea id='kommentarText' rows='5' cols='20'>"+kommentar+"</textarea>");
	$("#kommentarSpeichernButton").html("<input type='button' value='Kommentar speichern' onclick='saveKommentar(\""+pruefungsanmeldung_id+"\", \""+termin_id+"\", \""+lehrveranstaltung_id+"\");'>");
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
		error: loadError
	}).success(function(data){
		messageBox("message", "Kommentar erfolgreich gespeichert.", "green", "highlight", 1000);
	}).complete(function(){
		showAnmeldungen(termin_id, lehrveranstaltung_id);
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
		error: loadError
	}).success(function(data){
		var selectData = "";
		data.result.forEach(function(d){
			if(d.beschreibung === null)
				d.beschreibung = "";
			selectData += "<option value='"+d.pruefungstyp_kurzbz+"'>"+d.beschreibung+"</option>";
		});
		$('#pruefungsTyp').html(selectData);
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
			method: "loadStudiensemester"
		},
		error: loadError
	}).success(function(data){
		var selectData = "";
		data.result.forEach(function(d){
			selectData += "<option "+((d.studiensemester_kurzbz === data.aktSem) ? "selected" : "")+" value='"+d.studiensemester_kurzbz+"'>"+d.studiensemester_kurzbz+"</option>";
		});
		$('#studiensemester').html(selectData);
	}).complete(function(){
		loadPruefungsfenster();
		loadLehrveranstaltungen();
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
	var pruefungstyp_kurzbz = $("#pruefungsTyp").val();
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
	if(is_null(pruefungstyp_kurzbz) || is_undefined(pruefungstyp_kurzbz) || is_empty_String(pruefungstyp_kurzbz) || (pruefungstyp_kurzbz === "undefiniert"))
	{
		error = true;
		markMissingFormEntry("pruefungsTyp");
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

	if(error)
	{
		messageBox("message", "Formulardaten sind nicht korrekt.", "red", "highlight", 3000);
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
				pruefungstyp_kurzbz: pruefungstyp_kurzbz,
				titel: titel,
				beschreibung: beschreibung,
				methode: methode,
				einzeln: einzeln,
				termine: termine,
				lehrveranstaltungen: lehrveranstaltungen,
				mitarbeiter_uid: mitarbeiter_uid,
				pruefungsintervall: pruefungsintervall
			},
			error: loadError
		}).success(function(data){
			unmarkMissingFormEntry();
			if(data.error === "false")
			{
				messageBox("message", "Prüfung erfolgreich gespeichert.", "green", "highlight", 1000);
				resetPruefungsverwaltung();
			} 
			else
			{
				messageBox("message", data.errormsg, "red", "highlight", 1000);
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
	}).success(function(data){
		var selectData = '<option value="null">Lehrveranstaltung auswählen...</option>';
		data.result.forEach(function(d){
			selectData += '<option value="'+d.lehrveranstaltung_id+'">'+d.studiengang_bezeichnung+' | <b>'+d.bezeichnung+'</b> ('+d.lehrform_kurzbz+')</option>';
		});
		$("#lvDropdown1").html(selectData);
	}).complete(function(){

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
		error: loadError
	}).success(function(data){
		var selectData = "";
		data.result.forEach(function(d){
			selectData += "<option "+((d.studiensemester_kurzbz === data.aktSem) ? "selected" : "")+" value='"+d.studiensemester_kurzbz+"'>"+d.studiensemester_kurzbz+"</option>";
		});
		$('#studiensemester').html(selectData);
	}).complete(function(){
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
		}).success(function(data){
			if(data.result.length === 0)
			{
				messageBox("message", "Keine Prüfungsfenster vorhanden", "red", "highlight", 1000);
				$("#pruefungsfenster").html("<option value='null'></option>");
			}
			else
			{
				writePruefungsfenster(data);
				setDatePicker();
			}
		}).complete(function(){
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
			}).success(function(data){
				var selectData = '<option value="null">Lehrveranstaltung auswählen...</option>';
				data.result.forEach(function(d){
					selectData += '<option value="'+d.lehrveranstaltung_id+'">'+d.studiengang_bezeichnung+' | <b>'+d.bezeichnung+'</b> ('+d.lehrform_kurzbz+')</option>';
				});
				$("#lvDropdown1").html(selectData);
			}).complete(function(){
				$.ajax({
					dataType: 'json',
					url: "./pruefungsanmeldung.json.php",
					type: "POST",
					data: {
						method: "loadPruefung",
						pruefung_id: prfId
					},
					error: loadError
				}).success(function(data){
					var copy = $("#lvDropdown1").clone();
					$("#lvDropdowns").empty();
					$("#lvDropdowns").html(copy);
					$("#lvDropdowns").append("<br/>");
					var result = data.result[0];
					$("#titel").val(result.pruefung.titel);
					$("#beschreibung").val(result.pruefung.beschreibung);
					$("#studiensemester").val(result.pruefung.studiensemester_kurzbz);
					$("#pruefungsfenster").val(result.pruefung.pruefungsfenster_id);
					$("#pruefungsTyp").val(result.pruefung.pruefungstyp_kurzbz);
					$("#methode").val(result.pruefung.methode);
					var i = 0;
					$("#termin1").closest("tr").remove();
					terminHinzufuegen("span");
					data.result.forEach(function(d){
						i++;
						var lv = "";
						if(d.lehrveranstaltung !== undefined)
						{
							lv = "<span value='"+d.lehrveranstaltung.lehrveranstaltung_id+"'>"+d.lehrveranstaltung.studiengang.kurzbzlang+" | <b>"+d.lehrveranstaltung.bezeichnung+"</b> ("+d.lehrveranstaltung.lehrform_kurzbz+")</span><a href='#' onclick='deleteLehrveranstaltungFromPruefung(\""+d.lehrveranstaltung.lehrveranstaltung_id+"\", \""+d.pruefung.pruefung_id+"\");'> löschen</a></br>";
						}
						else
						{
							lv = "<span>Keine Lehrveranstaltungen vorhanden.</span></br>";
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
								$("#termin"+j+"Id").closest("tr").append("<td><a href='#' onclick='terminLoeschen(\""+d.pruefung.pruefung_id+"\", \""+t.pruefungstermin_id+"\");'>löschen</a></td>");
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
				}).complete(function(){
					$("#buttonSave").attr("onclick", "updatePruefung(\""+prfId+"\");");
					if($("#buttonStorno").length === 0)
					{
						$("#buttonSave").after("<input type='button' id='buttonStorno' onclick='stornoPruefung(\""+prfId+"\");' value='Prüfung stornieren'></input>");
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
	var pruefungstyp_kurzbz = $("#pruefungsTyp").val();
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
	if(is_null(pruefungstyp_kurzbz) || is_undefined(pruefungstyp_kurzbz) || is_empty_String(pruefungstyp_kurzbz) || (pruefungstyp_kurzbz === "undefiniert"))
	{
		error = true;
		markMissingFormEntry("pruefungsTyp");
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
		messageBox("message", "Formulardaten sind nicht korrekt.", "red", "highlight", 3000);
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
				pruefungstyp_kurzbz: pruefungstyp_kurzbz,
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
		}).success(function(data){
			unmarkMissingFormEntry();
			if(data.error === "false")
			{
				messageBox("message", "Prüfung erfolgreich gespeichert.", "green", "highlight", 1000);
				resetPruefungsverwaltung();
			}
			else
			{
				messageBox("message", data.errormsg, "red", "highlight", 1000);
			}
		}).complete(function(){
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
	}).success(function(data){
		if(data.error === "false")
		{
			messageBox("message", "Lehrveranstaltung erfolgreich entfernt", "green", "highlight", 1000);
		}
	}).complete(function(){
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
	}).success(function(data){
		if(data.error === "false")
		{
			messageBox("message", "Prüfung storniert", "green", "highlight", 1000);
		}
	}).complete(function(){
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
	}).success(function(data){
		if(data.error === "false")
		{
			messageBox("message", "Termin gelöscht", "green", "highlight", 1000);
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 1000);
		}
	}).complete(function(){
		loadPruefungsDetails(pruefung_id);
		loadAllPruefungen();
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
	}).success(function(data){
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
				tableRow += "<td>"+e.pruefungstyp_kurzbz+"</td>";
				tableRow += "<td>"+e.einzeln+"</td>";
				tableRow += "<td>"+e.mitarbeiter_uid+"</td>";
				tableRow += "<td>"+e.storniert+"</td>";
				tableRow += "</tr>";
				$("#prfTable tbody").first().append(tableRow);
			}
			
		});
	}).complete(function(event, xhr, settings){
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
 * Prüft ob die Beginnzeit vor der Endzeit liegt und ob beide Daten in der Zukunft liegen.
 * @param {Object} termin Object mit den Attributen datum (DD.MM.YYYY), beginn (HH:mm) und ende (HH:mm)
 * @returns {Boolean} TRUE, wenn die Daten korrekt sind, ansonsten FALSE
 */
function checkTermin(termin)
{
	var aktTime = new Date();
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
	console.log(maxTeilnehmer);
	console.log(termin.max);
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
	loadPruefungstypen("false");
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
	console.log($("#raum input[type=checkbox]").prop("checked"));
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
	}).success(function(data){
		console.log(data);
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
	}).success(function(data){
		console.log(data);
		var liste = "";
		data.result.forEach(function(d){
			liste += "<option value="+d.ort_kurzbz+">"+d.ort_kurzbz+"</option>"
		});
		$("#raeumeDropdown").html(liste);
	});
}