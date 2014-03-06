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
//	console.log(start);
	start = start.split('-');
//	console.log(start);
//	console.log(new Date(start[0],start[1]-1,start[2]));
	var ende = $("#pruefungsfenster option:selected").attr("ende");
//	console.log(ende);
	ende = ende.split('-');
//	console.log(ende);
//	console.log(new Date(ende[0],ende[1]-1,ende[2]));
	$("#prfTermin input[type=text]").each(function(i,v){
		$("#"+v.id).datepicker("destroy");
		$("#"+v.id).datepicker({
			minDate: new Date(start[0],start[1]-1,start[2]),
			maxDate: new Date(ende[0],ende[1]-1,ende[2])
		});
	});
	$("#pruefungsfenster").attr("onchange", "setDatePicker()");
	writePrfFensterDetails();
}

function terminHinzufuegen(inputTag)
{
	inputTag = (inputTag===undefined ? "" : inputTag);
//	var start = $("#pruefungsfenster option:selected").attr("start");
//	start = start.split('-');
//	var ende = $("#pruefungsfenster option:selected").attr("ende");
//	ende = ende.split('-');
	var count = $("#prfTermin tr").length+1;
	$("#prfTermin").append('<tr><td><input type="text" id="termin'+count+'" name="termin'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="time" placeholder="00:00" name="terminBeginn'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="time" placeholder="00:00" name="terminEnde'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="number" placeholder="0" min="0" name="minTeilnehmer'+(inputTag !== "" ? inputTag : "")+'[]"></td><td><input type="number" placeholder="10" min="0" name="maxTeilnehmer'+(inputTag !== "" ? inputTag : "")+'[]"></td></tr>');
//	$("#termin"+count).datepicker({
//		minDate: new Date(start[0],start[1]-1,start[2]),
//		maxDate: new Date(ende[0],ende[1]-1,ende[2])
//	});
	setDatePicker();
}

function lehrveranstaltungDropdownhinzufuegen(element, isChanged)
{
	if(!isChanged)
	{
		var newSelect = $("#lvDropdown1").clone();
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
			if(v.pruefungsfenster_id === prfFensterId)
			{
				$("#pruefungsfenster").append("<option selected start='"+v.start+"' ende='"+v.ende+"' value='"+v.pruefungsfenster_id+"'>" + v.oe_kurzbz + "</option>");
			}
			else
			{
				$("#pruefungsfenster").append("<option start='"+v.start+"' ende='"+v.ende+"' value='"+v.pruefungsfenster_id+"'>" + v.oe_kurzbz + "</option>");	
			}
		}
		else
		{
			$("#pruefungsfenster").html("<option>Kein Prüfungsfenster vorhanden.</option>");
		}
		
	});
	writePrfFensterDetails(data);
}

function writePrfFensterDetails(){
	var id = $("#pruefungsfenster option:selected").val();
//	console.log(data);
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






