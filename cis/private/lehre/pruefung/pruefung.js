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

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

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

function terminHinzufuegen(inputTag)
{
	inputTag = (inputTag===undefined ? "" : inputTag);
	var count = $("#prfTermin tr").length+1;
	$("#prfTermin").append('<tr><td><input type="text" id="termin'+count+'" name="termin'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="time" placeholder="00:00" name="terminBeginn'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="time" placeholder="00:00" name="terminEnde'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="number" placeholder="0" min="0" name="minTeilnehmer'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="number" placeholder="10" min="0" name="maxTeilnehmer'+(inputTag !== "" ? inputTag : "")+'[]"></td></tr>');
	setDatePicker();
}

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

function writePrfFensterDetails(){
	var id = $("#pruefungsfenster option:selected").val();
	if(id !== null)
	{
		var start = $("#pruefungsfenster option:selected").attr("start");
		var ende = $("#pruefungsfenster option:selected").attr("ende");
		start = start.split('-');
		ende = ende.split('-');
		start = new Date(start[0], start[1]-1,start[2]);
		ende = new Date(ende[0], ende[1]-1,ende[2]);
		start = start.getDate()+"."+(start.getMonth()+1)+"."+start.getFullYear();
		ende = ende.getDate()+"."+(ende.getMonth()+1)+"."+ende.getFullYear();
		$("#prfFensterDetails").html("Beginn: "+start+"</br>Ende: "+ende);
	}
}

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
			$("#message").html(data.result);
			$("#message").effect("highlight", {
				duration: 4000,
				color: "green"
			});
		}
		else
		{
			$("#message").html(data.errormsg);
			$("#message").effect("highlight", {
				duration: 4000,
				color: "red"
			});
		}
		resetForm();
	}).complete(function(event, xhr, settings){
		$("#saveDialog").dialog("close");
		refresh();
	});
}

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
				$("#message").html(data.result);
				$("#message").effect("highlight", {
					duration: 4000,
					color: "green"
				});
			}
			else
			{
				$("#message").html(data.errormsg);
				$("#message").effect("highlight", {
					duration: 4000,
					color: "red"
				});
			}
		}).complete(function(event, xhr, settings){
			refresh();
		});
	}
}

function clearPrfDetails()
{
	$("#prfTyp").empty();
	$("#prfMethode").empty();
	$("#prfBeschreibung").empty();
	$("#prfEinzeln").empty();
}

function resetForm()
{
	$("form").find("input[type=text], textarea").val("");
	clearPrfDetails();
}

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

function clearAccordion()
{
	$("#accordion tbody").each(function(i, v){
		$("#"+v.id).empty();
	});
}

function refresh()
{
	clearAccordion();
	loadPruefungen();
	loadPruefungenOfStudiengang();
	loadPruefungenGesamt();
}