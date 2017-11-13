/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Stefan Puraner		< puraner@technikum-wien.at >
 */
var loadTreeJumpAnchor='';
$(document).ready(function() {
	var stdkz = $('select[name=studiengang_kz]').val();

	$.ajax({
		type: "POST",
		url: "lvbgruppenverwaltungTree.php",
		data: {studiengang_kz: stdkz}
	}).done(function(data) {
		if (data === "No Data available!")
		{
			$("#newDataForm").css("visibility", "visible");
		}
		else
		{
			$.cookie("jstree_load", null);
			$.cookie("jstree_open", null);
			$("#newDataForm").css("visibility", "hidden");
			$("#treeContainer").html(data);
			$("#treeContainer").jstree({
				plugins: ["themes", "html_data", "cookies"]
			});
		}
	});

});

function loadTree() {
	var stdkz = $('select[name=studiengang_kz]').val();
	$.ajax({
		type: "POST",
		url: "lvbgruppenverwaltungTree.php",
		data: {studiengang_kz: stdkz, where: ""}
	}).done(function(data) {
		$("#treeContainer").html(data);
		$("#treeContainer").jstree({
			plugins: ["themes", "html_data", "cookies"]
		});
		if(loadTreeJumpAnchor!='')
		{
			window.setTimeout(function(){ window.location.hash=loadTreeJumpAnchor}, 200);
		}
	});
}
;

function getGruppenDetails(type, kurzBz, studkz, semester, verband, gruppe, aktiv) {
	$.ajax({
		type: "POST",
		url: "lvbgruppenverwaltungDetail.php",
		data: {type: type, gruppe_kurzbz: kurzBz, studiengang_kz: studkz, semester: semester, verband: verband, gruppe: gruppe}
	}).done(function(data) {
		$("#ajaxData").html("<div class='detailsDiv'>"+data+"</div>");
	});
}
;

function changeState(id, studkz, semester, verband, gruppe, aktiv, kurzBz) {
	aktiv = $("#" + id).attr('aktiv');
	$.ajax({
		type: "POST",
		url: "lvbgruppenverwaltungDetail.php",
		data: {studiengang_kz: studkz, semester: semester, verband: verband, gruppe: gruppe, changeState: aktiv, gruppe_kurzbz: kurzBz}
	}).done(function(data) {
		if (data === "erfolgreich") {
			if (aktiv === "t") {
				$("#" + id).attr('src', '../../skin/images/false.png');
				$("#" + id).attr('aktiv', 'f');
			} else {
				$("#" + id).attr('src', '../../skin/images/true.png');
				$("#" + id).attr('aktiv', 't');
			}
			$("#ajaxData").html("<div class='detailsDiv'>Status erfolgreich geändert!</div>");
		} else {
			$("#ajaxData").html("<div class='detailsDiv'>"+data+"</div>");
		}
	});
}
;

function newGroup(id) {
	var studiengang_kz = $("#newDataForm" + id).children("input[name*='studiengang_kz']").val();
	var semester = $("#newDataForm" + id).children("input[name*='semester']").val();
	var verband = $("#newDataForm" + id).children("input[name*='verband']").val();
	var type = $("#newDataForm" + id).children("input[name*='type']").val();
	var gruppe_neu = $("#newDataForm" + id).children("input[name*='gruppe_neu']").val();
	$.ajax({
		type: "POST",
		url: "lvbgruppenverwaltungDetail.php",
		data: {studiengang_kz: studiengang_kz, semester: semester, verband: verband, type: type, gruppe_neu: gruppe_neu}
	}).done(function(data) {
		obj = jQuery.parseJSON(data);
		if(obj.status=='ok')
		{
			// Sprung zur neuen Gruppe
			loadTreeJumpAnchor = obj.gruppe;
		}
		loadTree();
		$("#ajaxData").html("<div class='detailsDiv'>"+obj.message+"</div>");
	});
}
;

function newVerband(id) {
	var studiengang_kz = $("#newDataForm" + id).children("input[name*='studiengang_kz']").val();
	var semester = $("#newDataForm" + id).children("input[name*='semester']").val();
	var verband_neu = $("#newDataForm" + id).children("input[name*='verband_neu']").val();
	var type = $("#newDataForm" + id).children("input[name*='type']").val();
	$.ajax({
		type: "POST",
		url: "lvbgruppenverwaltungDetail.php",
		data: {studiengang_kz: studiengang_kz, semester: semester, type: type, verband_neu: verband_neu}
	}).done(function(data) {
		obj = jQuery.parseJSON(data);
		if(obj.status=='ok')
		{
			// Sprung zur neuen Gruppe
			loadTreeJumpAnchor = obj.gruppe;
		}
		loadTree();
		$("#ajaxData").html("<div class='detailsDiv'>"+obj.message+"</div>");
	});
}
;

function newSemester(id) {
	var studiengang_kz = $("#newDataForm" + id).children("input[name*='studiengang_kz']").val();
	var semester_neu = $("#newDataForm" + id).children("input[name*='semester_neu']").val();
	var type = $("#newDataForm" + id).children("input[name*='type']").val();
	$.ajax({
		type: "POST",
		url: "lvbgruppenverwaltungDetail.php",
		data: {studiengang_kz: studiengang_kz, type: type, semester_neu: semester_neu}
	}).done(function(data) {
		obj = jQuery.parseJSON(data);
		if(obj.status=='ok')
		{
			// Sprung zur neuen Gruppe
			loadTreeJumpAnchor = obj.gruppe;
		}
		loadTree();
		$("#ajaxData").html("<div class='detailsDiv'>"+obj.message+"</div>");
	});
}
;

function newSemesterForNewStudiengang(stdkz) {
	var studiengang_kz = stdkz;
	var semester_neu = $("#newDataForm").children("input[name*='semester_neu']").val();
	var type = $("#newDataForm").children("input[name*='type']").val();
	$.ajax({
		type: "POST",
		url: "lvbgruppenverwaltungDetail.php",
		data: {studiengang_kz: studiengang_kz, type: type, semester_neu: semester_neu}
	}).done(function(data) {
		obj = jQuery.parseJSON(data);
		if(obj.status=='ok')
		{
			// Sprung zur neuen Gruppe
			loadTreeJumpAnchor = obj.gruppe;
		}
		loadTree();
		$("#ajaxData").html("<div class='detailsDiv'>"+obj.message+"</div>");
	});
}
;

function newSpezGroup(id) {
	var studiengang_kz = $("#newSpzDataForm" + id).children("input[name*='studiengang_kz']").val();
	var semester = $("#newSpzDataForm" + id).children("input[name*='semester']").val();
	var spzgruppe_neu = $("#newSpzDataForm" + id).children("input[name*='spzgruppe_neu']").val();
	var type = $("#newSpzDataForm" + id).children("input[name*='type']").val();
	$.ajax({
		type: "POST",
		url: "lvbgruppenverwaltungDetail.php",
		data: {studiengang_kz: studiengang_kz, type: type, semester: semester, spzgruppe_neu: spzgruppe_neu}
	}).done(function(data) {
		obj = jQuery.parseJSON(data);
		if(obj.status=='ok')
		{
			// Sprung zur neuen Gruppe
			loadTreeJumpAnchor = obj.gruppe_kurzbz;
		}

		loadTree();
		$("#ajaxData").html("<div class='detailsDiv'>"+obj.message+"</div>");
	});
}
;
function saveGroup(studiengang_kz, semester, verband, gruppe, type) {
	var bezeichnung = $("#newBez").val();
	var orgform = $("#orgform_kurzbz").val();
	if(admin)
	{
		if (document.getElementById("aktiv").checked)
		{
			var aktiv = document.getElementById("aktiv").checked;
		}
	}
	$.ajax({
		type: "POST",
		url: "lvbgruppenverwaltungDetail.php",
		data: {studiengang_kz: studiengang_kz, semester: semester, verband: verband, gruppe: gruppe, type: type, bezeichnung: bezeichnung, aktiv: aktiv, orgform_kurzbz: orgform}
	}).done(function(data) {
		loadTree();
		$("#ajaxData").html("<div class='detailsDiv'>"+data+"</div>");
	});
}
function saveSpzGroup(studiengang_kz, kurzBz, type) {
	if(admin)
	{
		var kurzBzNeu = $("#spzKurzBz").val();
		var beschreibung = $("#spzBeschreibung").val();
		if (document.getElementById("spzSichtbar").checked)
		{
			var sichtbar = document.getElementById("spzSichtbar").checked;
		}
		if (document.getElementById("spzLehre").checked) {
			var lehre = document.getElementById("spzLehre").checked;
		}
		if (document.getElementById("spzAktiv").checked)
		{
			var aktiv = document.getElementById("spzAktiv").checked;
		}
		if (document.getElementById("spzMailgrp").checked)
		{
			var mailgrp = document.getElementById("spzMailgrp").checked;
		}
		if (document.getElementById("spzGeneriert").checked)
		{
			var generiert = document.getElementById("spzGeneriert").checked;
		}
		var sort = $("#spzSort").val();
		var orgForm = $("#spzOrgform").val();
	}
	var bezeichnung = $("#spzBezeichnung").val();
	
	$.ajax({
		type: "POST",
		url: "lvbgruppenverwaltungDetail.php",
		data: {studiengang_kz: studiengang_kz, gruppe_kurzbz: kurzBz, bezeichnung: bezeichnung, kurzBzNeu: kurzBzNeu,
			beschreibung: beschreibung, sichtbar: sichtbar, lehre: lehre, aktiv: aktiv, sort: sort, mailgrp: mailgrp,
			generiert: generiert, orgform_kurzbz: orgForm, type: type}
	}).done(function(data) {
		loadTree();
		$("#ajaxData").html("<div class='detailsDiv'>"+data+"</div>");
	});
	
	$("a").click(function(){
		// stuff
		return false;
	 });
}
