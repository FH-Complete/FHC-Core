/* Copyright (C) 2014 fhcomplete.org
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
 * Lädt alle Prüfungen eines Mitarbeiters
 * @returns {undefined}
 */
function loadPruefungenMitarbeiter()
{
	var uid = $("#mitarbeiter_uid").val();
	$.ajax({
		dataType: 'json',
		url: "./pruefungsbewertung.json.php",
		type: "POST",
		data: {
			method: "getPruefungMitarbeiter",
			mitarbeiter_uid: uid
		},
		error: loadError
	}).success(function(data){
		$("#pruefungen").find("h2").first().text("Prüfungen ("+uid+")");
		if(data.error === 'false')
		{
			var liste = "";
			data.result.forEach(function(e){
				liste += "<li>"+e.bezeichnung+"<ul>";
				e.pruefung.lehrveranstaltungen.forEach(function(d)
				{
					d.pruefung.termine.forEach(function(f){
						liste += "<li> <a onclick='showTeilnehmer(\""+f.pruefungstermin_id+"\", \""+e.lehrveranstaltung_id+"\", \""+e.bezeichnung+"\", \""+convertDateTime(f.von)+"\");'>"+convertDateTime(f.von)+"</a></li>";
					});
				})
				liste += "</li></ul>";
			});
			$("#pruefungenListe").html(liste);
		}
		else
		{
			$("#pruefungenListe").html(data.errormsg);
		}
	});
}

/**
 * Lädt die Anmeldungen zu einer Prüfung
 * @param {type} pruefungstermin_id ID des Prüfungstermins
 * @param {type} lehrveranstaltung_id ID der Lehrveranstaltung
 * @returns {undefined}
 */
function showTeilnehmer(pruefungstermin_id, lehrveranstaltung_id, lehrveranstaltung, datum)
{
	$("#modalOverlay").addClass("modalOverlay");
	$("#anmeldeDaten").empty();
	$("#anmeldungen").children("h2").text("Bewertungen zu "+lehrveranstaltung+" ("+datum+")");
	var noten = "<select onchange='markAsUnsaved(this);'>";
	$.ajax({
		dataType: 'json',
		url: "./pruefungsbewertung.json.php",
		type: "POST",
		data: {
			method: "getNoten"
		},
		error: loadError
	}).success(function(data){
		data.result.forEach(function(d)
		{
			noten += "<option value="+d.note+">"+d.bezeichnung+"</option>";
		});
		noten += "</select>";
	}).complete(function(event, xhr, settings){
		var notenSelect = noten;
		$.ajax({
			dataType: 'json',
			url: "./pruefungsbewertung.json.php",
			type: "POST",
			data: {
				method: "getAnmeldungenTermin",
				pruefungstermin_id: pruefungstermin_id,
				lehrveranstaltung_id: lehrveranstaltung_id
			},
			error: loadError
		}).success(function(data){
			var entry = "";
			if(data.error === "false")
			{
				data.result.forEach(function(d)
				{
					if(d.status_kurzbz === "bestaetigt")
					{	
						var datum = d.von.split(" ");	
						if(d.pruefung.note===null)
						{
							entry = "<div class='anmeldung' id="+d.student.uid+"><div>"+d.student.vorname+" "+d.student.nachname+"</div>"+notenSelect+"<input type='button' onclick='saveBeurteilung(this,\""+datum[0]+"\",\""+d.pruefungsanmeldung_id+"\",\""+d.pruefung_id+"\",\""+d.lehrveranstaltung_id+"\");' value='speichern'/></div>";
						}
						else
						{
							entry = "<div class='anmeldung' id="+d.student.uid+"><div>"+d.student.vorname+" "+d.student.nachname+"</div>"+notenSelect+"<input type='button' onclick='updateBeurteilung(this,\""+d.pruefung.pruefung_id+"\");' value='speichern'/></div>";
						}
						$("#anmeldeDaten").append(entry);
						if(d.pruefung.note!==null)
						{
							markAsSaved(document.getElementById(d.student.uid).firstChild);
							$("#"+d.student.uid).find("select").val(d.pruefung.note);
						}
						else
						{
							markAsUnsaved(document.getElementById(d.student.uid).firstChild);
						}
					}
				});
				if(entry === "")
				{
					entry = "<div><div>Keine Anmeldungen vorhanden.</div></div>";
					$("#anmeldeDaten").html(entry);
				}
			}
			else
			{
				entry = "<div><div>"+data.errormsg+"</div></div>";
				$("#anmeldeDaten").html(entry);
			}
		}).complete(function(event, xhr, settings){
			$("#modalOverlay").removeClass("modalOverlay");
		});
	});
}

/**
 * Speichert eine Beurteilung
 * @param {type} ele Element das die Funtkion aufruft
 * @param {type} datum
 * @param {type} pruefungsanmeldung_id ID der Anmeldung
 * @param {type} pruefung_id ID der Prüfung
 * @param {type} lehrveranstaltung_id ID der Lehrveranstaltung
 * @returns {void}
 */
function saveBeurteilung(ele, datum, pruefungsanmeldung_id, pruefung_id, lehrveranstaltung_id)
{
	var student_uid = $(ele).parent().attr("id");
	var mitarbeiter_uid = $("#mitarbeiter_uid").val();
	var note = $(ele).parent().find("select").val();
	var anmerkung = "";
	
	$.ajax({
		dataType: 'json',
		url: "./pruefungsbewertung.json.php",
		type: "POST",
		data: {
			method: "saveBeurteilung",
			pruefung_id: pruefung_id,
			lehrveranstaltung_id: lehrveranstaltung_id,
			student_uid: student_uid,
			mitarbeiter_uid: mitarbeiter_uid,
			note: note,
			//TODO Datum der Prüfung oder der Beurteilung?????
			datum: datum,
			anmerkung: anmerkung,
			pruefungsanmeldung_id: pruefungsanmeldung_id
		},
		error: loadError
	}).success(function(data){
		markAsSaved(ele);
		$(ele).attr("onclick", "updateBeurteilung(this,\""+data.result+"\")");
	}).complete(function(event, xhr, settings){
		
	});
}

/**
 * Aktualisiert eine Beurteilung
 * @param {type} ele Element das die Funtkion aufruft
 * @param {type} pruefung_id ID der Prüfung
 * @returns {undefined}
 */
function updateBeurteilung(ele, pruefung_id)
{
	var student_uid = $(ele).parent().attr("id");
	var mitarbeiter_uid = $("#mitarbeiter_uid").val();
	var note = $(ele).parent().find("select").val();
	var anmerkung = "";
	
	$.ajax({
		dataType: 'json',
		url: "./pruefungsbewertung.json.php",
		type: "POST",
		data: {
			method: "updateBeurteilung",
			pruefung_id: pruefung_id,
			note: note,
			anmerkung: anmerkung
		},
		error: loadError
	}).success(function(data){
		markAsSaved(ele);
	}).complete(function(event, xhr, settings){

	});
}

/**
 * Markiert einen Datensatz als gespeichert
 * @param {type} ele Element das die Funtkion aufruft
 * @returns {undefined}
 */
function markAsSaved(ele)
{
	$(ele).parent().removeClass("unsaved");
	$(ele).parent().addClass("saved");
}

/**
 * Markiert einen Datensatz als ungespeichert
 * @param {type} ele Element das die Funtkion aufruft
 * @returns {undefined}
 */
function markAsUnsaved(ele)
{
	$(ele).parent().removeClass("saved");
	$(ele).parent().addClass("unsaved");
}
