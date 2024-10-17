<?php
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
require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/globals.inc.php');
require_once('../../../../include/sprache.class.php');

$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

?>

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
	}).done(function(data){
		$("#pruefungen").find("h2").first().text("<?php echo $p->t('pruefung/pruefungPruefungenTitle'); ?> ("+uid+")");
		if(data.error === 'false')
		{
			var liste = "";
			data.result.forEach(function(e){
				liste += "<li>"+e.bezeichnung+"<ul>";
				e.pruefung.lehrveranstaltungen.forEach(function(d)
				{
					d.pruefung.termine.forEach(function(f){
						liste += "<li> <a onclick='showTeilnehmer(\""+f.pruefungstermin_id+"\", \""+e.lehrveranstaltung_id+"\", \""+e.bezeichnung.replace("'", "&apos;")+"\", \""+convertDateTime(f.von)+"\");'>"+convertDateTime(f.von)+"</a></li>";
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
	$("#anmeldungen").children("h2").text("<?php echo $p->t('pruefung/bewertungenZu'); ?> "+lehrveranstaltung+" ("+datum+")");
	var noten = "<select onchange='markAsUnsaved(this);'><option value='null'><?php echo $p->t('pruefung/keineAuswahl'); ?></option>";
	$.ajax({
		dataType: 'json',
		url: "./pruefungsbewertung.json.php",
		type: "POST",
		data: {
			method: "getNoten"
		},
		error: loadError
	}).done(function(data){
		if(data.error != 'true')
		{
			data.result.forEach(function(d)
			{
				noten += "<option value="+d.note+">"+d.bezeichnung+"</option>";
			});
			noten += "</select>";
		}
		else
		{
			messageBox("message",data.errormsg, "red", "highlight", 1000);
		}
	}).always(function(event, xhr, settings){
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
		}).done(function(data){
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
							entry = "<div class='anmeldung' id="+d.student.uid+">";
							entry = entry+"<div>"+d.student.vorname+" "+d.student.nachname+"</div>"
							entry = entry+notenSelect;
							entry = entry+"<input id='note_anmerkung_"+d.student.uid+"' placeholder='<?php echo $p->t('global/anmerkung'); ?>' />";
							entry = entry+"<input type='button' onclick='saveBeurteilung(this,\""+datum[0]+"\",\""+d.pruefungsanmeldung_id+"\",\""+d.pruefung_id+"\",\""+d.lehrveranstaltung_id+"\");' value='<?php echo $p->t('global/speichern'); ?>'/>";
							entry = entry+"</div>";
						}
						else
						{
							entry = "<div class='anmeldung' id="+d.student.uid+">";
							entry = entry+"<div>"+d.student.vorname+" "+d.student.nachname+"</div>";
							entry = entry+notenSelect;
							entry = entry+"<input id='note_anmerkung_"+d.student.uid+"' placeholder='<?php echo $p->t('global/anmerkung'); ?>' value='"+d.pruefung.anmerkung+"' />";
							entry = entry+"<input type='button' onclick='updateBeurteilung(this,\""+d.pruefung.pruefung_id+"\");' value='<?php echo $p->t('global/speichern'); ?>'/>";
							entry = entry+"</div>";
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
						var t = $("#note_anmerkung_"+d.student.uid).parent().find('select').first().width();
						$("#note_anmerkung_"+d.student.uid).width(t);
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
		}).always(function(event, xhr, settings){
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
	if((note === "null") || (note===null))
	{
		messageBox("message", "Keine Note ausgewählt.", "red", "highlight", 1000);
		return false;
	}
	var anmerkung = $("#note_anmerkung_"+student_uid).val();

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
	}).done(function(data){
		if(data.error != 'true')
		{
			markAsSaved(ele);
			$(ele).attr("onclick", "updateBeurteilung(this,\""+data.result+"\")");
		}
		else
		{
			messageBox("message",data.errormsg, "red", "highlight", 1000);
			$(ele).parent().find("select").val(null);
		}
	}).always(function(event, xhr, settings){

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
	var anmerkung = $("#note_anmerkung_"+student_uid).val();
	if((note === "null") || (note===null))
	{
		messageBox("message", "Keine Note ausgewählt.", "red", "highlight", 1000);
		return false;
	}
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
	}).done(function(data){
		if(data.error != 'true')
		{
			markAsSaved(ele);
		}
		else
		{
			messageBox("message",data.errormsg, "red", "highlight", 1000);
		}


	}).always(function(event, xhr, settings){

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
