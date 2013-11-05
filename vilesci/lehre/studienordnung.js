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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 */
var studiengang_kz='';
var studiengang_bezeichnung='';
var studienordnung_id='';
var studienordnung_bezeichnung='';
var studienplan_id='';
var lehrveranstaltungen='';

function loadError(xhr, textStatus, errorThrown)
{
	if(xhr.status==200)
	{
		alert('Fehler:'+xhr.responseText);
	}
	else
		alert('Fehler beim Laden der Daten. ErrorNr:'+xhr.status);
}

function drawHeader(text)
{
	if(text===undefined)
	{
		text = '<h2>';
		if(studiengang_bezeichnung!='')
			text=text+studiengang_bezeichnung;
		if(studienordnung_bezeichnung!='')
			text=text+' <b>&gt;</b> '+studienordnung_bezeichnung;
		if(studienplan_bezeichnung!='')
			text=text+' <b>&gt;</b> '+studienplan_bezeichnung;


		text=text+'</h2>';
	}
	else
		text='<h2>'+text+'</h2>';
	$('#header').html(text);
}
function loadStudienordnung()
{
	studiengang_kz = $('#studiengang').val();
	studiengang_bezeichnung = $( "#studiengang option:selected" ).text();
	studienordnung_id='';
	studienordnung_bezeichnung='';
	studienplan_id='';
	studienplan_bezeichnung='';

	drawHeader();
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		data: {
				"typ": "json",
				"class": "studienordnung",
				"method":	"loadStudienordnungSTG",
				"parameter_0": studiengang_kz
			},
		success: StudienordnungLoaded,
		error: loadError
	});
}

function StudienordnungLoaded(data)
{
	if(data.error=='true')
	{
		alert('Fehler:'+data.errormsg);
	}
	else
	{
		drawStudienordnungen(data.result);
		$( "#menueLinks" ).accordion("option","active",1);
	}
}

function drawStudienordnungen(data)
{
	var obj='<a href="#Neu" onclick="neueStudienordnung();return false;">Neue Studienordnung</a><ul>';

	for(i in data)
	{
		obj=obj+'<li><a href="#Load" onclick="loadStudienplanSTO('+data[i].studienordnung_id+',\''+data[i].bezeichnung+'\');return false;">'+data[i].bezeichnung+'</a></li>';		
	}
	obj=obj+'</ul>';
	$('#studienordnung').html(obj);
}

function loadStudienplanSTO(studienordnung_id,bezeichnung)
{
	studienordnung_bezeichnung=bezeichnung;
	studienordnung_id=studienordnung_id;
	drawHeader();
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		data: {
				"typ": "json",
				"class": "studienplan",
				"method":	"loadStudienplanSTO",
				"parameter_0": studienordnung_id
			},
		success: StudienplanSTOLoaded,
		error: loadError
	});
}

function StudienplanSTOLoaded(data)
{
	if(data.error=='true')
	{
		alert('Fehler:'+data.errormsg);
	}
	else
	{
		drawStudienplan(data.result);
		$( "#menueLinks" ).accordion("option","active",2);
	}
}

function drawStudienplan(data)
{
	var obj='<a href="#Neu" onclick="neuerStudienplan();return false;">Neuer Studienplan</a><ul>';

	for(i in data)
	{
		obj=obj+'<li><a href="#Load'+data[i].studienplan_id+'" onclick="loadLehrveranstaltungSTPL('+data[i].studienplan_id+',\''+data[i].bezeichnung+' '+data[i].orgform_kurzbz+'\');return false;">'+data[i].bezeichnung+' '+data[i].orgform_kurzbz+'</a></li>';
	}
	obj=obj+'</ul>';
	$('#studienplan').html(obj);
}

function loadLehrveranstaltungSTPL(studienplan_id, bezeichnung)
{
	studienplan_id=studienplan_id;
	studienplan_bezeichnung=bezeichnung;
	drawHeader();
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/lehrveranstaltung.json.php",
		data: {
				"method":	"loadLehrveranstaltungStudienplan",
				"studienplan_id": studienplan_id
			},
		success: LehrveranstaltungSTPLLoaded,
		error: loadError
	});
}

function LehrveranstaltungSTPLLoaded(data)
{
	if(data.error=='true')
	{
		alert('Fehler:'+data.errormsg);
	}
	else
	{
		lehrveranstaltungen = data.result;
		drawLehrveranstaltung(data.result);
		$( "#menueRechts" ).accordion("option","active",0);
		//drawLehrveranstaltungGrid();
		$("#lehrveranstaltung").html("<h2>Organisationseinheit</h2><h2>Lehrtyp</h2>");
	}
}

function drawLehrveranstaltung(data)
{
	var obj='';
	obj=getLehrveranstaltungSub(data);
	$('#data').html(obj);
}

function getLehrveranstaltungSub(data)
{
	var obj='<ul>';

	for(i in data)
	{
		obj=obj+'<li><a href="#Load'+data[i].lehrveranstaltung_id+'" onclick="alert(\'comming soon\');return false;">'+data[i].bezeichnung+'</a></li>';
		if(data[i].childs)
		{
			obj=obj+getLehrveranstaltungSub(data[i].childs);
		}
	}
	obj=obj+'</ul>';
	return obj;
}

function neueStudienordnung()
{
	drawHeader('Neue Studienordnung');
	$("#data").load('studienordnung.inc.php?method=neueStudienordnung&studiengang_kz='+studiengang_kz);
}

function neuerStudienplan()
{
	drawHeader('Neuer Studienplan');
	$("#data").load('studienordnung.inc.php?method=neuerStudienplan&studiengang_kz='+studiengang_kz);
}

function saveStudienordnung()
{
	bezeichnung = $("#bezeichnung").val();
	version = $("#version").val();
	gueltigvon = $("#gueltigvon option:selected").val();
	gueltigbis = $("#gueltigbis option:selected").val();
	ects = $("#ects").val();
	studiengangbezeichnung = $("#studiengangbezeichnung").val();
	studiengangbezeichnungenglisch = $("#studiengangbezeichnungenglisch").val();
	studiengangkurzbzlang = $("#studiengangkurzbzlang").val();
	studienordnung_id = $("#studienordnung_id").val();
	akadgrad_id = $("#akadgrad_id").val();

	if(studienordnung_id!='')
	{
		loaddata = {
			"method": "loadStudienordnung",
			"parameter_0": studienordnung_id
		};
	}
	else
		loaddata={};

	savedata = {
	"bezeichnung": bezeichnung,
	"version":version,
	"gueltigvon":gueltigvon,
	"gueltigbis":gueltigbis,
	"ects":ects,
	"studiengangbezeichnung":studiengangbezeichnung,
	"studiengangbezeichnungenglisch":studiengangbezeichnungenglisch,
	"studiengangkurzbzlang":studiengangkurzbzlang,
	"akadgrad_id":akadgrad_id,
	"studiengang_kz":studiengang_kz
	};

	
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		type: "POST",
		data: {
				"typ": "json",
				"class": "studienordnung",
				"method": "save",
				"loaddata": JSON.stringify(loaddata),
				"savedata": JSON.stringify(savedata),
			},
		success: StudienordnungSaved,
		error: loadError
	});
}
function StudienordnungSaved()
{
	if(data.error=='true')
	{
		alert('Fehler:'+data.errormsg);
	}
	else
	{
		loadStudienordnung();
	}
}

