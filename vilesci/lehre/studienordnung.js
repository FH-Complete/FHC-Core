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
			text=text+' <b>&gt;</b> '+studienplan_bezeichnung + " Beispieldaten";


		text=text+'</h2>';
	}
	else
		text='<h2>'+text+'</h2>';
	$('#header').html(text);
}
function loadStudienordnung()
{
	$(".jstree-grid-header").hide();
	$(".jstree-grid-wrapper").hide();
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
		error: loadError
	}).success(function(data)
	{
		StudienordnungLoaded(data);
		hideStplDetails();
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
		jqUi( "#menueLinks" ).accordion("option","active",1);
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

function loadStudienplanSTO(neue_studienordnung_id,bezeichnung)
{
	$(".jstree-grid-header").hide();
	$(".jstree-grid-wrapper").hide();
	studienordnung_bezeichnung=bezeichnung;
	studienordnung_id=neue_studienordnung_id;
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
		error: loadError
	}).success(function(data)
	{
		StudienplanSTOLoaded(data)
		semesterStoZuordnung();
	});
	$.ajax({
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		data: {
			"typ" : "json",
			"class": "studienordnung",
			"method": "loadStudienordnung",
			"parameter_0": studienordnung_id
		},
		error: loadError
	}).success(function(data){
		if(data.result.length === 1)
		{
			var html = "";
			html += data.result[0]
		}
//		console.log(data);
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
		jqUi( "#menueLinks" ).accordion("option","active",2);
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
				"typ": "json",
				"class": "lehrveranstaltung",
				"method":	"getLvTree",
//				"parameter_0": 100, //for debugging
				"studienplan_id": studienplan_id
			},
		error: loadError
	}).success(function(data)
	{
		if(data.result[0].lehrveranstaltung_id !== null)
		{
			$("#data").jstree({
				ui: {
					"select_limit": -1,
					"select_multiple_modifier": "ctrl"
				},
				json_data: { 
					data: data.result
				},
				crrm: {
					move: {
						"always_copy": "multitree",
						"check_move": function(m) {
							//alert(m.o[0].id);
//							alert($("#data").jstree._get_parent(m.o));
//							if(jQuery.jstree._reference("#data")._get_children(m.np).find("li[id="+m.o[0].id+"]"))
//							{
//								//alert(jQuery.jstree._reference("#data")._get_children(m.np).find("li[id="+m.o[0].id+"]"));
//								return true;
//							}
//							else
//							{
//								return false;
//							}
//							return (m.ot === m.rt) || !m.rt.get_container().find("li[id="+m.o[0].id+"]").length;
							//console.log(m.rt._get_children(m.np).find("li[id="+m.o[0].id+"]").length+" "+m.np[0].id);
//							console.log(m.np.children().find("ul > li[id="+m.o[0].id+"]").length);
//							if(m.np.children("ul").find("ul > li[id="+m.o[0].id+"]").length !== 0)
//							console.log(m.np.attr("id"));
//							console.log("Länge: "+m.np.children().find("li[id="+m.o[0].id+"]").length);

							if(m.np.children().find("li[id="+m.o[0].id+"]").length !== 0 && m.np.attr("id") !== "data")
							{
								return false;
							}
//							console.log(m.r.siblings().length);
//							var s = m.r.siblings().length;
//							var err = false;
//							for(var i=0; i<s; i++)
//							{
//								//console.log(m.r.siblings().get(i).id);
////								console.log(m.r.siblings().get(i).id);
////								console.log("copy_"+m.o[0].id);
//								if(m.r.siblings().get(i).id === "copy_"+m.o[0].id)
//								{
//									//console.log(m.r.siblings().find("li[id="+m.o[0].id+"]").length);
//									err = true;
//								}
//							}
////							console.log(err);
//							if(err) return false;
////							console.log(m.r.siblings().get("#copy_"+m.o[0].id));
							return true;
						 }
					}
				},
				dnd: {
					"drag_check": function(data){
						return {
							after: true,
							before: true,
							inside: true
						}
					}
				},
				grid: {
					columns: [
						{width: 300, header: "Lehrveranstaltung", value: "bezeichnung", source: "metadata"},
						{width: 50, header: "ECTS", value: "ects", source: "metadata"},
						{width: 120, header: "Semesterstunden", value: "semesterstunden", source: "metadata"}
					],
					resizable: true
				},
				types: {
					"types" :  {
//						"valid_children" : ["semester", "lv", "default"],
						"lv" : {
							icon : {
								//image : "test.jpg"
							},
							max_children: 0
						},
						"semester" : {
//							"valid_children" : ["lv"]
						},
						"default" : {
//							"valid_children" : [ "default", "lv"]
						}
					}
				},
				sort : function(a, b){
					return this._get_node(a).attr("rel") > this._get_node(b).attr("rel");
				},
				contextmenu: {
					"items" : function($node) {
						return {
							"Delete" : {
								"label" : "delete",
								"action": function(obj){
									var conf = confirm("Wollen Sie das Objekt wirklich löschen?");
									if(conf)
									{
										this.remove(obj);
									}
								}
							}
						}
					}
				},
				plugins: ["themes", "ui", "dnd", "grid", "json_data", "crrm", "types", "sort", "contextmenu"]
			}).bind("move_node.jstree", function(event, data)
			{
				saveJsondataFromTree("copy_"+data.rslt.o[0].id, studienplan_id);
				var root = data.inst.get_container_ul();
				var nodes = root[0].childNodes;
				for(var i=0; i<nodes.length; i++)
				{
					if(nodes[i].getAttribute("rel") !== "lv"){
						writeEctsSum(nodes[i]);
					}
					
				}
				hideAllTreeColumns();
				writeOverallSum(nodes);
			}).bind("loaded.jstree", function(event, data)
			{
				var root = data.inst.get_container_ul();
				var nodes = root[0].childNodes;
				for(var i=0; i<nodes.length; i++)
				{
					if(nodes[i].getAttribute("rel") !== "lv"){
						writeEctsSum(nodes[i]);
					}
					
				}
				writeOverallSum(nodes);
			}).bind("open_node.jstree", function(event, data)
			{
				var root = data.inst.get_container_ul();
				var nodes = root[0].childNodes;
				for(var i=0; i<nodes.length; i++)
				{
					if(nodes[i].getAttribute("rel") !== "lv"){
						writeEctsSum(nodes[i]);
					}
				}
				writeOverallSum(nodes);
			});
		}
		else
		{
			$('#data').addClass("jstree-drop");
			$('#data').css("border", "1px solid black");
			
			$('#data').jstree({
                "json_data": {
                    "data" : [ ]
                },
                crrm: {
					move: {
						"always_copy": "multitree"
					}
				},
				dnd: {
					"drag_check": function(data){
						return {
							after: true,
							before: true,
							inside: true
						};
					}
				},
				grid: {
					columns: [
						{width: 300, header: "Lehrveranstaltung", value: "bezeichnung", source: "metadata"},
						{width: 80, header: "Semester", value: "semester", source: "metadata"},
						{width: 50, header: "ECTS", value: "ects", source: "metadata"},
						{width: 120, header: "Semesterstunden", value: "semesterstunden", source: "metadata"}
					],
					resizable: true
				},
                "plugins": ["themes", "json_data", "ui", "crrm", "dnd", "grid"]
            }).bind("move_node.jstree", function (e, data) {
				saveJsondataFromTree("copy_"+data.rslt.o[0].id, studienplan_id);
				writeEctsSum(data.rslt.np);
				hideAllTreeColumns();
            });
		}
		
		$("#lehrveranstaltung").html("<h3>Organisationseinheit</h3><div id='oeDiv'></div>");
		$.ajax(
		{
			dataType: "json",
			url: "../../soap/fhcomplete.php",
			data: {
					"typ": "json",
					"class": "organisationseinheit",
					"method": "getAll",
				},
			error: loadError
		}).success(function(data)
		{
			var html = "<div><select id='oeDropdown' onchange='loadFilteredLehrveranstaltungen();'>";
			for(i in data.result)
			{
				if(data.result[i].aktiv===true)
				{
					html+='<option value="'+data.result[i].oe_kurzbz+'">'+data.result[i].bezeichnung+'</option>';
				}
			}
			html+="</select></div>";
			$("#oeDiv").html(html);
			loadLehrtypen();
		});
	})
}


//LehrveranstaltungSTPLLoaded nicht in Verwendung
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
		jqUi( "#menueRechts" ).accordion("option","active",1);
		//drawLehrveranstaltungGrid();
		$("#lehrveranstaltung").html("<h3>Organisationseinheit</h3><div id='oeDiv'></div><h3>Lehrtyp</h3><div id='lehrtypenDiv'></div>");
		$.ajax(
		{
			dataType: "json",
			url: "../../soap/fhcomplete.php",
			data: {
					"typ": "json",
					"class": "organisationseinheit",
					"method": "getAll",
				},
			error: loadError
		}).success(function(data)
		{
			var html = "<select id='oeDropdown'>";
			for(i in data.result)
			{
				if(data.result[i].aktiv===true)
				{
					html+='<option value="'+data.result[i].oe_kurzbz+'">'+data.result[i].bezeichnung+' - '+data.result[i].oe_kurzbz+'</option>';
				}
				
			}
			html+="</select>";
			$("#oeDiv").html(html);
		});
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
	hideStplDetails();
	drawHeader('Neue Studienordnung');
	$("#data").load('studienordnung.inc.php?method=neueStudienordnung&studiengang_kz='+studiengang_kz);
}

function neuerStudienplan()
{
	hideStplDetails();
	drawHeader('Neuer Studienplan');
	$("#data").load('studienordnung.inc.php?method=neuerStudienplan&studiengang_kz='+studiengang_kz);
}

/*
* Funktion zum Laden des Baumes der 
* gefilterten LVs
* */
function loadFilteredLehrveranstaltungen()
{
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		data: {
				"typ": "json",
				"class": "lehrveranstaltung",
				"method":	"load_lva",
				"parameter_0": studiengang_kz,						//Studiengangskennzahl
				"parameter_1": $("#semesterDropdown").val(),		//Semester
				"parameter_2": "null",								//Lehrverzeichnis
				"parameter_3": "null",								//Lehre // sollte TRUE sein
				"parameter_4": "null",								//Aktiv // sollte TRUE sein
				"parameter_5": "bezeichnung",						//Sortierung
				"parameter_6": $("#oeDropdown option:selected").val(),				//Organisationseinheit KurzBz
				"parameter_7": $("#lehrtypDropdown").val()			//Lehrtyp KurzBz
			},
		error: loadError
	}).success(function(data)
	{
		if(data.result[0].lehrveranstaltung_id!==null)
		{
			
			if($("#lvListe").length === 0)
			{
				$("#filteredLVs").html("<h3>Lehrveranstaltungen</h3><div id='lvListe'></div>");
			}
			$("#lvListe").jstree({
				ui: {
					"select_limit": -1,
					"select_multiple_modifier": "ctrl"
				},
				json_data: { 
					data: data.result
				},
				crrm: {
					move: {
						"check_move" : function(m)
						{
							return false;
						},
						"always_copy": "multitree"
					}
				},
				grid: {
					columns: [
						{width: 325, header: "Lehrveranstaltung", value: "bezeichnung", source: "metadata"},
						{width: 50, header: "ECTS", value: "ects", source: "metadata"},
						{width: 80, header: "Semester", value: "semester", source: "metadata"},
						{width: 120, header: "Semesterstunden", value: "semesterstunden", source: "metadata"}
					],
					resizable: true
				},
				plugins: ["themes", "ui", "dnd", "grid", "json_data", "crrm", "types"]
			}).bind("loaded.jstree", function(event, data) 
			{
				hideAllTreeColumns();
			});
		} else {
			$("#filteredLVs .jstree-grid-wrapper").remove();
			if($("#lvListe").length !== 0)
			{
				$("#lvListe").remove();
			}
			$("h3:contains('Lehrveranstaltungen')").remove();
			$("#filteredLVs").append("<div id='lvListe'>Keine Einträge gefunden!</div>");
		}
	});
}

/*
* Funktion zum Laden der Daten für
* das Dropdownfeld zum Filtern nach Lehrtyp
*/
function loadLehrtypen()
{
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		data: {
				"typ": "json",
				"class": "lehrtyp",
				"method": "getAll"
			},
		error: loadError
	}).success(function(data)
	{
		if($("#lehrtypenDiv").length === 0)
		{
			$("#lehrveranstaltung").append("<h3>Lehrtyp</h3><div id='lehrtypenDiv'></div>");
		}
		var html = "<select id='lehrtypDropdown' onchange='loadFilteredLehrveranstaltungen();'>";
		for(i in data.result)
		{
			html+='<option value="'+data.result[i].lehrtyp_kurzbz+'">'+data.result[i].bezeichnung+'</option>';
		}
		html+="</select>";
		$("#lehrtypenDiv").html(html);
		loadSemester();
	});
}

/*
* Funktion zum Laden der Daten für
* das Dropdownfeld zum Filtern nach Semester
*/
function loadSemester()
{
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/studienplan.json.php",
		data: {
				"method": "getSemesterFromStudiengang",
				"studiengang_kz": studiengang_kz
			},
		error: loadError
	}).success(function(data)
	{
		if($("#semesterListe").length === 0)
		{
			$("#lehrveranstaltung").append("<h3>Semester</h3><div id='semesterListe'></div>");
		}
		var html = "<select id='semesterDropdown' onchange='loadFilteredLehrveranstaltungen();'>";
		html += "<option value='null'>Alle Semester</option>";
		for(i in data.result)
		{
			html+="<option value='"+data.result[i]+"'>"+data.result[i]+". Semester</option>";
		}
		html+="</select>";
		$("#semesterListe").html(html);
		loadFilteredLehrveranstaltungen();
	});
}

/*
* Funktion zum Verstecken der Spalten im Baum der
* gefilterten LV-Liste
 */
function hideAllTreeColumns()
{
	var headers = $("#filteredLVs .jstree-grid-header-cell");
	var separators = $("#filteredLVs .jstree-grid-separator");
	//separators[0].style.display = "none";
	for(var j=2; j<headers.length; j++)
	{
		headers[j].style.display = "none";
		separators[j].style.display = "none";
		var divs = $("#filteredLVs .jstree-grid-col-" + j);
		for (var i = 0; i < divs.length; i++)
		{
			divs[i].style.display = "none";	
		}
	}
}

function saveJsondataFromTree(data, studienplan_id)
{
	var jsonData = $("#data").jstree("get_json", $("#"+data));
	var jsonString = JSON.stringify(jsonData);
	//alert(JSON.stringify(jsonString));
	loaddata = {
		"method" : "loadLehrveranstaltungStudienplanByLvId",
		"parameter_0" : studienplan_id,
		"parameter_1" : jsonData[0]["metadata"]["lehrveranstaltung_id"]
	};
	//alert(JSON.stringify(loaddata));
	
	savedata = {
		"studienplan_id": studienplan_id,
		"lehrveranstaltung_id" : jsonData[0]["metadata"]["lehrveranstaltung_id"],
		"stpllv_semester": jsonData[0]["metadata"]["semester"],
		"studienplan_lehrveranstaltung_id_parent": "",
		"stpllv_pflicht": true
	};
	
//	$.ajax(
//	{	
//		dataType: "json",
//		url: "../../soap/fhcomplete.php",
//		type: "POST",
//		data: {
//			"typ": "json",
//			"class": "studienplan",
//			"method": "saveStudienplanLehrveranstaltung",
//			"loaddata": JSON.stringify(loaddata),
//			"savedata": JSON.stringify(savedata),
//		}
//	}).success(function(d)
//	{
//		alert(d);
////		if(d.error !== "bereits vorhanden")
////		{
////			$("#jsonData").html(d);
////		} 
////		else 
////		{
////			alert("Lehrveranstaltung ist bereits im Studiengang enthalten!");
////			$("#data").jstree("remove", $("#"+data));
////		}
//	});
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
	mystudienordnung_id = $("#studienordnung_id").val();
	akadgrad_id = $("#akadgrad_id").val();

	if(mystudienordnung_id!='')
	{
		loaddata = {
			"method": "loadStudienordnung",
			"parameter_0": mystudienordnung_id
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

function saveStudienplan()
{
	bezeichnung = $("#bezeichnung").val();
	version = $("#version").val();
	orgform_kurzbz = $("#orgform_kurzbz option:selected").val();
	sprache = $("#sprache option:selected").val();
	regelstudiendauer = $("#regelstudiendauer").val();
	semesterwochen = $("#semesterwochen").val();
	testtool_sprachwahl = $("#testtool_sprachwahl").prop("checked");
	aktiv = $("#aktiv").prop("checked");
	mystudienplan_id = $("#studienplan_id").val();

	if(mystudienplan_id!='')
	{
		loaddata = {
			"method": "loadStudienplan",
			"parameter_0": mystudienplan_id
		};
	}
	else
		loaddata={};

	savedata = {
	"bezeichnung": bezeichnung,
	"version":version,
	"orgform_kurzbz":orgform_kurzbz,
	"sprache":sprache,
	"regelstudiendauer":regelstudiendauer,
	"semesterwochen":semesterwochen,
	"testtool_sprachwahl":testtool_sprachwahl,
	"aktiv":aktiv,
	"studienordnung_id":studienordnung_id
	};

	
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		type: "POST",
		data: {
				"typ": "json",
				"class": "studienplan",
				"method": "save",
				"loaddata": JSON.stringify(loaddata),
				"savedata": JSON.stringify(savedata),
			},
		success: StudienplanSaved,
		error: loadError
	});
}
function StudienplanSaved(data)
{
	if(data.error=='true')
	{
		alert('Fehler:'+data.errormsg);
	}
	else
	{
		loadStudienplanSTO(studienordnung_id,studienordnung_bezeichnung);
	}
}

function writeEctsSum(parent){
	//alert(parent);
	//console.log($("#data").jstree("get_children", parent));
	var cells = $(parent).find(".jstree-grid-col-1");
	var sum = 0;
	//console.log(cells);
	for(var i=1; i<cells.length; i++)
	{
		if(!isNaN(parseFloat(cells[i].childNodes[0].innerHTML)))
		{
			sum+=parseFloat(cells[i].childNodes[0].innerHTML);
		}
		//console.log(sum);
	}
	cells[0].childNodes[0].innerHTML = "<b>"+sum+"</b>";
}

function writeOverallSum(root){
//	console.log(root);
	var cells = $(root).find(".jstree-grid-col-1");
	var sum = 0;
	//console.log(cells);
	for(var i=1; i<cells.length; i++)
	{
		if(!isNaN(parseFloat(cells[i].childNodes[0].innerHTML)))
		{
			sum+=parseFloat(cells[i].childNodes[0].innerHTML);
		}
		//console.log(sum);
	}
	$("#stplDetails").html("ECTS-Summe: <b>"+sum+"</b>");
	$("#stplDetails").show();
}

function hideStplDetails()
{
	$("#stplDetails").hide();
}

function semesterStoZuordnung()
{
	hideStplDetails();
	drawHeader('Neue Semester Zuordnung');
	$("#data").load('studienordnung.inc.php?method=semesterStoZuordnung&studienordnung_id='+studienordnung_id);
}

function saveSemesterStoZuordnung(sem)
{
	var cells = $("#"+sem).find("input[type=checkbox]");
	
	var semester = new Array();
	var semesterKurzbz = "";
	for(var i = 0; i < cells.length; i++)
	{
//		semesterChecked.push(cells[i].checked);
		console.log(cells);
		semester[cells[i].getAttribute("semester")] = cells[i].checked;
	}
	console.log(semester);
//	$.ajax({
//		
//	})
}
