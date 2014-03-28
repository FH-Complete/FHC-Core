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

var progressBarCount = 0;

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
 * @param {type} inputTag
 * @returns {undefined}
 */
function terminHinzufuegen(inputTag)
{
	inputTag = (inputTag===undefined ? "" : inputTag);
	var count = $("#prfTermin tr").length+1;
	$("#prfTermin").append('<tr><td><input type="text" id="termin'+count+'" name="termin'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="time" placeholder="00:00" name="terminBeginn'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="time" placeholder="00:00" name="terminEnde'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="number" placeholder="0" min="0" name="minTeilnehmer'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="number" placeholder="10" min="0" name="maxTeilnehmer'+(inputTag !== "" ? inputTag : "")+'[]"></td></tr>');
	setDatePicker();
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
		url: "../../../../soap/fhcomplete.php",
		data: {
			typ: "json",
			class: "pruefungsfenster",
			method: "getByStudiensemester",
			parameter_0: studiensemester_kurzbz
		},
		error: loadError
	}).success(function(data){
		writePruefungsfenster(data);
		setDatePicker();
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
//	$("#pruefungsfenster").append("<option value='null'>Prüfungsfenster auswählen...</option>");
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
 * 
 * @returns {undefined}
 */
//function writePrfFensterDetails(){
//	var id = $("#pruefungsfenster option:selected").val();
//	if(id !== null)
//	{
//		var start = $("#pruefungsfenster option:selected").attr("start");
//		var ende = $("#pruefungsfenster option:selected").attr("ende");
//		start = start.split('-');
//		ende = ende.split('-');
//		start = new Date(start[0], start[1]-1,start[2]);
//		ende = new Date(ende[0], ende[1]-1,ende[2]);
//		start = start.getDate()+"."+(start.getMonth()+1)+"."+start.getFullYear();
//		ende = ende.getDate()+"."+(ende.getMonth()+1)+"."+ende.getFullYear();
//		$("#prfFensterDetails").html("Beginn: "+start+"</br>Ende: "+ende);
//	}
//}

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
				var table = writePruefungsTable(e, data);
				$("#pruefungen").append(table);
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
				var table = writePruefungsTable(e, data);
				$("#pruefungenStudiengang").append(table);
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
			var table = writePruefungsTable(e, data);
			$("#pruefungenGesamt").append(table);
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
function writePruefungsTable(e, data)
{
	var row = "";
	var teilnehmer = "";
	var button = "";
	row += "<tr><td>"+e.organisationseinheit+"</td><td style='cursor: pointer;' onclick='showPruefungsDetails(\""+e.pruefung.pruefung_id+"\",\""+e.lehrveranstaltung[0].lehrveranstaltung_id+"\");'>"+e.lehrveranstaltung[0].bezeichnung+"</td><td>";
	e.pruefung.termine.forEach(function(d){
		var termin = d.von.split(" ");
		termin = termin[0].split("-");
		termin = new Date(termin[0], termin[1]-1,termin[2]);
		termin = termin.getDate()+"."+(termin.getMonth()+1)+"."+termin.getFullYear();
		row += termin+"</br>";

		if(d.max === null)
		{
			teilnehmer += "unbegrenzt</br>";
		}
		else
		{
			teilnehmer += (d.max - d.teilnehmer)+"/"+d.max+"</br>";
		}					
		var storno = false;
		var anmeldung_id = null;
		data.result.anmeldungen.forEach(function(anmeldung){
			if((anmeldung.pruefungstermin_id === d.pruefungstermin_id) && (anmeldung.lehrveranstaltung_id === e.lehrveranstaltung[0].lehrveranstaltung_id))
			{
				storno = true;
				anmeldung_id= anmeldung.pruefungsanmeldung_id;
			}
		});
		if(storno)
		{
			button += "<input type='button' value='Stornieren' onclick='stornoAnmeldung(\""+anmeldung_id+"\");'></br>";
		}
		else
		{
//			button += "<input type='button' value='zur Anmeldung' onclick='saveAnmeldung(\""+e.lehrveranstaltung[0].lehrveranstaltung_id+"\", \""+d.pruefungstermin_id+"\");'></br>";
			button += "<input type='button' value='zur Anmeldung' onclick='openDialog(\""+e.lehrveranstaltung[0].lehrveranstaltung_id+"\", \""+d.pruefungstermin_id+"\", \""+e.lehrveranstaltung[0].bezeichnung+"\", \""+d.von+"\", \""+d.bis+"\");'></br>";

		}
	});
	row += "<td>"+teilnehmer+"</td>";
	row += "</td><td></td><td>"+button+"</td></tr>";
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
					}
					else
					{
						$("#prfEinzeln").html("");
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
//	var studiensemester_kurzbz = $("#studiensemesterHidden").val();
	
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "saveAnmeldung",
			termin_id: termin_id,
			lehrveranstaltung_id: lehrveranstaltung_id,
			bemerkung: bemerkungen
//			studiensemester_kurzbz: studiensemester_kurzbz
		},
		error: loadError
	}).success(function(data){
		if(data.error === 'false')
		{
			messageBox("message", data.result, "green", "highlight", 4000);
//			$("#message").html(data.result);
//			$("#message").effect("highlight", {
//				duration: 4000,
//				color: "green"
//			});
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 4000);
//			$("#message").html(data.errormsg);
//			$("#message").effect("highlight", {
//				duration: 4000,
//				color: "red"
//			});
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
				messageBox("message", data.result, "green", "highlight", 4000);
//				$("#message").html(data.result);
//				$("#message").effect("highlight", {
//					duration: 4000,
//					color: "green"
//				});
			}
			else
			{
				messageBox("message", data.errormsg, "red", "highlight", 4000);
//				$("#message").html(data.errormsg);
//				$("#message").effect("highlight", {
//					duration: 4000,
//					color: "red"
//				});
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
 * Lädt alle Prüfungen eines Mitarbeiters
 * @returns {undefined}
 */
function loadPruefungenMitarbeiter()
{
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getPruefungMitarbeiter"
		},
		error: loadError
	}).success(function(data){
		if(data.error === 'false')
		{
			var liste = "";
			data.result.forEach(function(e){
				liste += "<li>"+e.bezeichnung+"<ul>";
				e.pruefung.termine.forEach(function(d){
					liste += "<li> <a onclick='showAnmeldungen(\""+d.pruefungstermin_id+"\", \""+e.lehrveranstaltung_id+"\");'>"+convertDateTime(d.von)+"</a></li>";
				})
				liste += "</li></ul>";
			});
			$("#pruefungenListe").append(liste);
		}
		else
		{
			$("#pruefungenListe").html(data.errormsg);
		}
	});
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
		if(data.error === 'false')
		{
			var terminId = data.result[0].pruefungstermin_id;
			var lehrveranstaltung_id = data.result[0].lehrveranstaltung_id;
			var liste = "<ul id='sortable'>";
			var count = 0;
			data.result.forEach(function(d){
				count++;
				switch(d.status_kurzbz)
				{
					case 'angemeldet':
						liste += "<li class='ui-state-default' id='"+d.student.uid+"'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span><a>"+d.student.vorname+" "+d.student.nachname+"</a>";
						liste += "<div style='width: 5%; text-align: right;'>"+count+"</div><div style='text-align: center; width: 20%;'><input type='button' value='Bestätigen' onclick='anmeldungBestaetigen(\""+d.pruefungsanmeldung_id+"\", \""+terminId+"\", \""+lehrveranstaltung_id+"\");'></div>";
						if(d.wuensche !== null)
						{
							liste += "<div class='anmerkungInfo'><a href='#' title='Anmerkung des Studenten:</br>"+d.wuensche+"'><img style='width: 20px;' src='../../../../skin/images/button_lvinfo.png'></a></div>";
						}
						liste += "</li>";
						break;
					case 'bestaetigt':
						liste += "<li class='ui-state-default' id='"+d.student.uid+"'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span><a>"+d.student.vorname+" "+d.student.nachname+"</a>";
						liste += "<div style='width: 5%; text-align: right;'>"+count+"</div><div style='text-align: center; width: 20%;'>bestätigt</div>";
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
			$(document).tooltip({
				position: {
					at: "right center",
					my: "left+15 center"
				}
			});
		}
		else
		{
//			$("#message").html(data.errormsg);
			messageBox("message", data.errormsg, "red", "highlight", 4000);
		}
	}).complete(function(event, xhr, settings){
		$("#sortable").sortable();
		$("#sortable").disableSelection();
	});
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
			messageBox("message", "Reihung erfolgreich geändert.", "green", "highlight", 4000);
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 4000);
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
			messageBox("message", data.errormsg, "red", "highlight", 4000);
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
	});
	$("#"+divId).hide("blind",{ 
		duration: 4000,
		queue: false
	});
}

/**
 * Lädt alle Studiengänge
 * @returns {undefined}
 */
function loadStudiengaenge()
{
//	$("body").append("<div class='modalOverlay'></div>");
	$.ajax({
		dataType: 'json',
		url: "./pruefungsanmeldung.json.php",
		type: "POST",
		data: {
			method: "getStudiengaenge"
		},
		error: loadError
	}).success(function(data){
		$("#stgListe").empty();
		if(data.error === 'false')
		{
			var liste = "";
			data.result.forEach(function(e){
				progressBarCount++;
			});
//			$("#progressbar").progressbar({
//				value: 0,
//				max: progressBarCount
//			}).bind('progressbarchange', function(event, ui) {
//				var selector = "#" + this.id + " > div";
//				var value = this.getAttribute( "aria-valuenow" );
//				
//				if (value < (progressBarCount / 6)){
//					$(selector).css({ 'background': 'Red' });
//				} else if (value < (progressBarCount / 3)){
//					$(selector).css({ 'background': 'Orange' });
//				} else if (value < (progressBarCount / 1.5)){
//					$(selector).css({ 'background': 'Yellow' });
//				} else{
//					$(selector).css({ 'background': 'LightGreen' });
//				}
//			});
			
			data.result.forEach(function(e){
				var kuerzel = e.typ+e.kurzbz
				liste += "<li id='stg"+e.studiengang_kz+"'><span class='studiengang'><a href='#' onclick='loadPruefungStudiengang(\""+e.studiengang_kz+"\");'>"+e.bezeichnung+" ("+kuerzel.toUpperCase()+")</a></span></li>";
//				loadPruefungStudiengang(e.studiengang_kz);
			});
			$("#stgListe").append(liste);
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 4000);
		}
	}).complete(function(event, xhr, settings){
		
	});
}

/**
 * Lädt alle Prüfungen zu einem Studiengang
 * @param {type} studiengang_kz Studiengangskennzahl
 * @returns {undefined}
 */
function loadPruefungStudiengang(studiengang_kz)
{
	var progressBarStep = 100/progressBarCount;
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
					e.pruefung.termine.forEach(function(d){
						liste += "<li> <a onclick='showAnmeldungen(\""+d.pruefungstermin_id+"\", \""+e.lehrveranstaltung_id+"\");'>"+convertDateTime(d.von)+" "+convertDateTime(d.von, "time")+" - "+convertDateTime(d.bis, "time")+"</a></li>";
					})
					liste += "</li></ul></ul>";
				});
//				$("#stg"+studiengang_kz).append(liste);
				$("#pruefungenListe").append(liste);
			}
			else
			{
				$("#pruefungenListe").html("Keine Prüfungen vorhanden.");
			}
		}
		else
		{
			messageBox("message", data.errormsg, "red", "highlight", 4000);
		}
	}).complete(function(event, xhr, settings){
//		var value = $("#progressbar").progressbar("option", "value");
//		$("#progressbar").progressbar({
//			value: (value += 1)
//		});
//		if(value === progressBarCount)
//		{
//			$(".modalOverlay").remove();
//			$("#progressbar").hide();
////			
////			var width = $("#prfWrapper").width();
////			$("#prfWrapper").width(width+40);
////			$("#anmWrapper").css("left", width+80+"px");
//		}
	});
}
