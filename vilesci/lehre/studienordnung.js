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
 *			Stefan Puraner	<puraner@technikum-wien.at>
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

/**
 * Laedt die Studienordnungen und zeigt diese linken Menue an
 */
function loadStudienordnung()
{
	// Ausgewaehlten Studiengang holen
	studiengang_kz = $('#studiengang').val();
	studiengang_bezeichnung = $( "#studiengang option:selected" ).text();

	// Globale Variablen resetten
	studienordnung_id='';
	studienordnung_bezeichnung='';
	studienplan_id='';
	studienplan_bezeichnung='';

	drawHeader();

	// Laden der Studienordnungen
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
		console.log(data);
		if(data.error=='true')
		{
			alert('Fehler:'+data.errormsg);
		}
		else
		{
			$("#studienplan").html("Bitte wählen Sie zuerst eine Studienordnung aus!");
			drawStudienordnungen(data.result);
			//jqUi( "#menueLinks" ).accordion("option","active",1);
		}
	});
}

/**
 * Erstellt die Links fuer die Studienordnungen
 * @param data Objekt mit den Studienordnungsdaten
 */
function drawStudienordnungen(data)
{
	var obj='<a href="#Neu" onclick="neueStudienordnung();return false;">Neue Studienordnung</a><ul>';

	for(i in data)
	{
		if(data[i].studienordnung_id !== null)
		{
			obj=obj+'<li><a href="#Load'+data[i].studienordnung_id+'" onclick="loadStudienplanSTO('+data[i].studienordnung_id+',\''+data[i].bezeichnung+'\');return false;">'+data[i].bezeichnung+'</a>'
				+' <a href="#Edit'+data[i].studienordnung_id+'" onclick="editStudienordnung('+data[i].studienordnung_id+');return false;"><img title="edit" src="../../skin/images/edit.png"></a></li>';
		}
	}
	obj=obj+'</ul>';
	$("#data").empty();
	$("#tabs").hide();
	$('#studienordnung').html(obj);
}

/**
 * Laedt die Studienplaene zu einer Studienordnung
 */
function loadStudienplanSTO(neue_studienordnung_id,bezeichnung)
{
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
		console.log(data);
		if(data.error=='true')
		{
			alert('Fehler:'+data.errormsg);
		}
		else
		{
			drawStudienplan(data.result);
			//jqUi( "#menueLinks" ).accordion("option","active",2);
		}
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
			html += data.result[0];
		}
	});
}

/**
 * Erstellt die Links zu den Studienplaenen
 */
function drawStudienplan(data)
{
	var obj='<a href="#Neu" onclick="neuerStudienplan();return false;">Neuer Studienplan</a><ul>';

	for(i in data)
	{
		if(data[i].studienplan_id !== null)
		{
			obj=obj+'<li><a href="#Load'+data[i].studienplan_id+'" onclick="loadLehrveranstaltungSTPL('+data[i].studienplan_id+',\''+data[i].bezeichnung+'\',\''+data[i].regelstudiendauer+'\');return false;">'+data[i].bezeichnung+'</a>'
			+' <a href="#Edit'+data[i].studienplan_id+'" onclick="editStudienplan('+data[i].studienplan_id+');return false;"><img title="edit" src="../../skin/images/edit.png"></a></li>';
		}
	}
	obj=obj+'</ul>';
	$("#tabs").hide();
	$('#studienplan').html(obj);
}

/**
 * Laedt die Lehrveranstaltungen eines Studienplanes
 */
function loadLehrveranstaltungSTPL(studienplan_id, bezeichnung, max_semester)
{
	//studienplan_id = studienplan_id;
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
				"studienplan_id": studienplan_id,
				"semester": max_semester
			},
		error: loadError
	}).success(function(data)
	{
/*
		var treeData=new Array();
		for(var i in data.result)
		{
			var attribute = new Array();
			attribute["id"]=data.result[i][0].lehrveranstaltung_id;
			attribute["rel"]=data.result[i][0].lehrtyp_kurzbz;
			attribute["studienplan_lehrveranstaltung_id"]=data.result[i][0].studienplan_lehrveranstaltung_id;

			var object = new Array();
			object["metadata"]=data.result[i][0];
			object["attr"]=attribute;
			treeData.push(object);
		}
*/
		$("#data").html("<div id='treeData'></div>");
	//	if(data.result[0].lehrveranstaltung_id !== null)
	//	{
			// Anzeigen des Trees mit den Lehrveranstaltungen
			$("#treeData").jstree({
				ui: {
					"select_limit": 1,
					"select_multiple_modifier": "ctrl"
				},
				json_data: { 
					data: data.result
				},
				crrm: {
					move: {
						"always_copy": "multitree",
						"check_move": function(m) {
							if(m.r.attr("rel")==="semester" && (m.p === "inside" || m.p === "before"))
							{
								console.log("test");
								return true;
							}

							if(m.p === "inside" || m.p === "last")
							{
								return true;
							}

							return false;
						}
					}
				},
				dnd: {
					"drag_check": function(data){
						return {
							after: false,
							before: false,
							inside: true
						};
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
						"lv" : {
							icon : {
								image : "../../include/js/jstree/icons/lehrveranstaltung.png"
							}
						},
						"semester" : {

						},
						"modul" : {
							icon : {
								image : "../../include/js/jstree/icons/modul.png"
							}
						},
						"lf" : {
						}
					}
				},
				sort : function(a, b){
					return this._get_node(a).attr("rel") > this._get_node(b).attr("rel");
				},
				contextmenu: {
					"items" : function(node) {
						if(node.attr("rel") !== "semester")
						{
							return {
								"Delete" : {
									"label" : "delete",
									"action": function(obj){
										if(obj.children().find("li").length === 0)
										{
											var conf = confirm("Wollen Sie \""+this.get_text(obj)+"\" wirklich aus diesem Studienplan löschen?");
											if(conf)
											{
												this.remove(obj);
												deleteLehrveranstaltungFromStudienplan(obj.attr("studienplan_lehrveranstaltung_id"));
											}
										}
										else
										{
											alert("Das zu löschende Element darf keine Elemente beinhalten!");
										}
									}
								}
							};
						}
						
					}
				},
				plugins: ["themes", "ui", "dnd", "grid", "json_data", "crrm", "types", "sort", "contextmenu"]
			}).bind("move_node.jstree", function(event, data)
			{	
				var studienplan_lehrveranstaltung_id='';
				if(data.rslt.o[0].attributes.studienplan_lehrveranstaltung_id)
					studienplan_lehrveranstaltung_id=data.rslt.o[0].attributes.studienplan_lehrveranstaltung_id.value;
				saveJsondataFromTree(data.rslt.o[0].id, studienplan_id, studienplan_lehrveranstaltung_id);
				var root = data.inst.get_container_ul();
				var nodes = root[0].childNodes;
				for(var i=0; i<nodes.length; i++)
				{
					if(nodes[i].getAttribute("rel") === "semester"){
						writeEctsSum(nodes[i]);
					}
					
				}
				hideAllTreeColumns();
				writeOverallSum(nodes);
			}).bind("loaded.jstree", function(event, data)
			{
				var root = data.inst.get_container_ul();
				var nodes = root[0].childNodes;
//				console.log(nodes);
				for(var i=0; i<nodes.length; i++)
				{
					if(nodes[i].getAttribute("rel") === "semester"){
						writeEctsSum(nodes[i]);
					}
					
				}
				writeOverallSum(nodes);
			}).bind("open_node.jstree", function(event, data)
			{
				if(data.args[0].attr)
				{
					var root = data.inst.get_container_ul()[0].childNodes;
					var nodes = $("#"+data.args[0].attr("id"));

					for(var i=0; i<nodes.length; i++)
					{
						if(nodes[i].getAttribute("rel") === "semester"){
							writeEctsSum(nodes[i]);
						}
					}
					writeOverallSum(root);
				}
			}).bind("select_node.jstree", function(event, data)
			{
				// Bei einem Klick auf eine LV werden die Details geladen
				stpllvid = data.rslt.obj.attr("studienplan_lehrveranstaltung_id");
				lvid = data.rslt.obj.attr("id");
				if(lvid.substring(0,5)==="copy_")
				{
					lvid = lvid.substring(5);
				}

				// Lehrveranstaltungsdetails laden
				if(data.rslt.obj.attr("rel") !== "semester")
				{
					LoadLVDetails(lvid, stpllvid);
				}
				else
				{
					$("#tab-lehrveranstaltungdetail").html("<p>Klicken Sie auf eine Lehrveranstaltung um die Details anzuzeigen</p>");
				}
				
				// Regeln laden
				if(stpllvid!==undefined)
					LVRegelnloadRegeln(stpllvid);

				// Kompatibilitaet laden
				if(lvid!==undefined)
					loadLVKompatibilitaet(lvid);
			});
	/*	}
		else
		{
			$('#treeData').addClass("jstree-drop");
			$('#treeData').css("border", "1px solid black");
			
			$('#treeData').jstree({
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
                "plugins": ["themes", "json_data", "ui", "crrm", "dnd", "grid", "sort"]
            }).bind("move_node.jstree", function (e, data) {
				saveJsondataFromTree("copy_"+data.rslt.o[0].id, studienplan_id);
				writeEctsSum(data.rslt.np);
				hideAllTreeColumns();
            });
		}
		*/
		$("#lehrveranstaltung").html("<h3>Organisationseinheit</h3><div id='oeDiv'></div>");
		$.ajax(
		{
			dataType: "json",
			url: "../../soap/fhcomplete.php",
			data: {
					"typ": "json",
					"class": "organisationseinheit",
					"method": "getAll",
					"parameter_0":true,
					"parameter_1":true
				},
			error: loadError
		}).success(function(data)
		{
			var html = "<div><select id='oeDropdown' style='max-width: 200px' onchange='loadFilteredLehrveranstaltungen();'><option value=''>-- Keine --</option>";
			for(i in data.result)
			{
				if(data.result[i].aktiv===true)
				{
					html+='<option value="'+data.result[i].oe_kurzbz+'">'+data.result[i].organisationseinheittyp_kurzbz+' '+data.result[i].bezeichnung+'</option>';
				}
			}
			html+="</select></div>";
			$("#oeDiv").html(html);
			loadLehrtypen();
		});
	});
	$( "#tabs" ).show();
}

/**
 * Laedt Details zur ausgewaehlten Lehrveranstaltung
 */
function LoadLVDetails(lvid, stpllvid)
{
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		data: {
				"typ": "json",
				"class": "lehrveranstaltung",
				"method": "load",
				"parameter_0":lvid
			},
		error: loadError
	}).success(function(data)
	{
		lvdata = data.result[0]
		var html = "Bezeichnung: "+lvdata.bezeichnung;
		html+="<br>Kurzbezeichnung: "+lvdata.kurzbz;
		html+="<br>ID: "+lvdata.lehrveranstaltung_id;
		html+="<br>ECTS: "+lvdata.ects;
		html+="<br>Semesterstunden: "+lvdata.semesterstunden;
		$("#tab-lehrveranstaltungdetail").html(html);
	});	
}

/**
 * Laedt kompatible LVs zur ausgewaehlten Lehrveranstaltung
 */
function loadLVKompatibilitaet(lvid)
{
	$.ajax(
	{
		dataType: "html",
		url: "lehrveranstaltung_kompatibel.php",
		type: "GET",
		data: {
				"lehrveranstaltung_id":lvid
			},
		error: loadError
	}).success(function(data)
	{
		//console.log(data);
//		lvdata = data.result[0]
//		var html = "Bezeichnung: "+lvdata.bezeichnung;
//		html+="<br>Kurzbezeichnung: "+lvdata.kurzbz;
//		html+="<br>ID: "+lvdata.lehrveranstaltung_id;
//		html+="<br>ECTS: "+lvdata.ects;
//		html+="<br>Semesterstunden: "+lvdata.semesterstunden;
		$("#tab-kompatibel").html(data);
	});	
}


/**
 * Laedt die Daten um eine neue Studienordnung zu erstellen
 */
function neueStudienordnung()
{
	$("#tabs").hide();
	drawHeader('Neue Studienordnung');
	$("#data").load('studienordnung.inc.php?method=neueStudienordnung&studiengang_kz='+studiengang_kz);
}

/**
 * Laedt die Daten um einen neuen Studienplan zu erstellen
 */
function neuerStudienplan()
{
	$("#tabs").hide();
	drawHeader('Neuer Studienplan');
	$("#data").load('studienordnung.inc.php?method=neuerStudienplan&studiengang_kz='+studiengang_kz);
}

/**
 * Laedt die Daten um eine Studienordnung zu editieren
 */
function editStudienordnung(studienordnung_id)
{
	$("#tabs").hide();
	drawHeader('Studienordnung bearbeiten');
	$("#data").load('studienordnung.inc.php?method=neueStudienordnung&studiengang_kz='+studiengang_kz+'&studienordnung_id='+studienordnung_id);
}

/**
 * Laedt die Daten um einen Studienplan zu editieren
 */
function editStudienplan(studienplan_id)
{
	$("#tabs").hide();
	drawHeader('Studienplan bearbeiten');
	$("#data").load('studienordnung.inc.php?method=neuerStudienplan&studiengang_kz='+studiengang_kz+'&studienplan_id='+studienplan_id);
}

/*
* Funktion zum Laden des Baumes der 
* gefilterten LVs
* */
function loadFilteredLehrveranstaltungen()
{
	if($("#oeDropdown option:selected").val() === "")
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
					"parameter_4": "true",								//Aktiv // sollte TRUE sein
					"parameter_5": "bezeichnung",						//Sortierung
					"parameter_6": "null",								//$("#oeDropdown option:selected").val(),//Organisationseinheit KurzBz
					"parameter_7": $("#lehrtypDropdown option:selected").val()			//Lehrtyp KurzBz
				},
			error: loadError
		}).success(function(data)
		{
			showLVTree(data);
		});
	}
	else
	{
		//TODO get LVs by OE
		$.ajax(
		{
			dataType: "json",
			url: "../../soap/fhcomplete.php",
			data: {
					"typ": "json",
					"class": "lehrveranstaltung",
					"method":	"load_lva_oe",
					"parameter_0": $("#oeDropdown option:selected").val(),			//Organisationseinheit KurzBz
					"parameter_1": "true",											//Aktiv // sollte TRUE sein
					"parameter_2": $("#lehrtypDropdown option:selected").val()		//Lehrtyp KurzBz
				},
			error: loadError
		}).success(function(data)
		{
			showLVTree(data);
		/*
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
			}*/
		});
	}
}

function showLVTree(data)
{
			if(data.result[0].lehrveranstaltung_id!==null)
			{
				if($("#lvListe").length === 0)
				{
					$("#filteredLVs").html("<h3>Lehrveranstaltungen</h3><div id='lvListe'></div>");
				}
				$("#lvListe").jstree({
					ui: {
						"select_limit": 1,
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
					types: {
						"types" :  {
	//						"valid_children" : ["semester", "lv", "default"],
							"lv" : {
								icon : {
									image : "../../include/js/jstree/icons/lehrveranstaltung.png"
								}
	//							max_children: 0
							},
							"modul" : {
								icon : {
									image : "../../include/js/jstree/icons/modul.png"
								}
							},
							"lf" : {
								icon : {
									//image : "../../include/js/jstree/icons/lehrveranstaltung.png"
								}
							}
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
		var html = "<select id='lehrtypDropdown' onchange='loadFilteredLehrveranstaltungen();'><option value='null'>-- Alle --</option>";
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
		html += "<option value='null'>-- Alle --</option>";
		for(i in data.result)
		{
			html+="<option value='"+data.result[i]+"'>"+data.result[i]+". Semester</option>";
		}
		html+="</select>";
		$("#semesterListe").html(html);
		loadFilteredLehrveranstaltungen();
	});
}

/**
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

/**
 * Speichert einen Datensatz der in den LV Tree gezogen wurde
 * @param nodeId ID des Eintrages der gedroppt wurde
 * @param studienplan_id ID des Studienplanes
 * @param studienplan_lehrveranstaltung_id wird nur bei verschiebungen uebergeben
 */
function saveJsondataFromTree(nodeId, studienplan_id, studienplan_lehrveranstaltung_id)
{
	var jsonData = $("#treeData").jstree("get_json", $("#treeData").find("li[id="+nodeId+"]"));
	var copy = false;

	if(jsonData.length !== 1)
	{
		jsonData = $("#treeData").jstree("get_json", $("#copy_"+nodeId));
		copy = true;
	}

	loaddata = {
		"method" : "loadLehrveranstaltungStudienplanByLvId",
		"parameter_0" : studienplan_id,
		"parameter_1" : jsonData[0]["metadata"]["lehrveranstaltung_id"]
	};

	var node;
	if(copy)
	{
		node = $("#copy_"+jsonData[0]["metadata"]["lehrveranstaltung_id"]);
	}
	else
	{
		node = $("#"+jsonData[0]["metadata"]["lehrveranstaltung_id"]);
	}

	var lehrveranstaltung_id = jsonData[0]["metadata"]["lehrveranstaltung_id"];
	var semester = node.closest("li[rel=semester]").attr("id");
	if(semester === undefined)
	{
		semester = 0;
	}

	var parent_id ='';
	if(node.parent().parent().attr("studienplan_lehrveranstaltung_id"))
		parent_id = node.parent().parent().attr("studienplan_lehrveranstaltung_id");

	var neu ='';

	// Pruefen ob diese Zuordnung bereits vorhanden ist
	$.ajax(
	{	
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		type: "POST",
		async: false,
		data: {
			"typ": "json",
			"class": "studienplan",
			"method": "containsLehrveranstaltung",
			"parameter_0": studienplan_id,
			"parameter_1": lehrveranstaltung_id
		}
	}).success(function(data)
	{
		if(data.return==false)
			neu = true;
		else
			neu = false;
	});

	// Bei neuen Eintraegen kein Load noetig
	if(neu)
		loaddata='';

	// Wenn der Eintrag keine Verschiebung im Tree ist, und die Lehrveranstaltung bereits im
	// Studienplan vorhanden ist -> Abbruch
	if(studienplan_lehrveranstaltung_id=='' && neu==false)
	{
		alert("Die Lehrveranstaltung ist bereits in diesem Studienplan vorhanden!");
		$("#treeData").jstree("remove", $("#copy_"+nodeId));
		return;
	}
	
	savedata = {
		"studienplan_id": studienplan_id,
		"lehrveranstaltung_id" : lehrveranstaltung_id,
		"semester": semester,
		"studienplan_lehrveranstaltung_id_parent": parent_id,
		"pflicht": true
	};
	
	$.ajax(
	{	
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		type: "POST",
		data: {
			"typ": "json",
			"class": "studienplan",
			"method": "saveStudienplanLehrveranstaltung",
			"loaddata": JSON.stringify(loaddata),
			"savedata": JSON.stringify(savedata)
		}
	}).success(function(d)
	{
		if(d.error=='false')
			$("#jsonData").html(d);
		else
			alert('Fehler:'+d.errormsg);
	});
}

function deleteLehrveranstaltungFromStudienplan(lehrveranstaltung_studienplan_id)
{
	$.ajax({
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		type: "POST",
		data: {
			"typ": "json",
			"class": "studienplan",
			"method": "deleteStudienplanLehrveranstaltung",
			"parameter_0" : lehrveranstaltung_studienplan_id
		}
	}).success(function(data)
	{
		console.log(data);
	});
}

/**
 * Speichert die Studienordnung
 */
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
		"studiengangbezeichnung_englisch":studiengangbezeichnungenglisch,
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
		success: function(data){
			if(data.error=='true')
				alert('Fehler:'+data.errormsg);
			else
			{
				$("#submsg").css("visibility", "visible");
				window.setTimeout(function(){$("#submsg").css("visibility", "hidden");}, 1500);
				loadStudienordnung();
			}
		},
		error: loadError
	});
}

/**
 * Speichert den Studienplan
 */
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
				"savedata": JSON.stringify(savedata)
			},
		success: function(data){
			if(data.error=='true')
				alert('Fehler:'+data.errormsg);
			else
			{
				$("#submsg").css("visibility", "visible");
				window.setTimeout(function(){$("#submsg").css("visibility", "hidden");}, 1500);
				loadStudienplanSTO(studienordnung_id,studienordnung_bezeichnung);	
			}
		},
		error: loadError
	});
}

/**
 * Berechnet dei ECTS Summe fuer ein Semester / Modul
 */
function writeEctsSum(parent)
{
//	console.log($(parent).children("li").length);
//	console.log($(parent).children("ul").children());
	for(var i=0; i<$(parent).children("ul").children().length; i++)
	{
//		console.log($(parent).children("ul").children().length);
		if($(parent).children("ul").children().length > 0)
		{
			writeEctsSum($(parent).children("ul").children()[i]);
		}	
	}
//	console.log($(parent).attr("rel"));
	if($(parent).attr("rel") === "semester")
	{
		var cells = $(parent).find(".jstree-grid-col-1");
	//	console.log(cells);
		var sum = 0;
		for(var j=1; j<cells.length; j++)
		{
			if(!isNaN(parseFloat(cells[j].childNodes[0].innerHTML)))
			{
				sum+=parseFloat(cells[j].childNodes[0].innerHTML);
//				console.log(sum);
			}
		}
		cells[0].childNodes[0].innerHTML = "<b>"+sum+"</b>";
	//	console.log(cells[0].childNodes[0]);
	}
}

/**
 * Berechnet die ECTS Gesamt summe
 */
function writeOverallSum(root)
{
	$("#treeData").append('<div id="stplDetails" style="padding-top: 1.0em"></div>');
	var cells = $(root).find(".jstree-grid-col-1");
	var sum = 0;

	for(var i=1; i<cells.length; i++)
	{
		if(!isNaN(parseFloat(cells[i].childNodes[0].innerHTML)))
		{
			sum+=parseFloat(cells[i].childNodes[0].innerHTML);
		}
	}
	$("#stplDetails").html("ECTS-Summe: <b>"+sum+"</b>");
	$("#stplDetails").show();
}

/**
 * Laedt die Daten zum Eintragen der Studienordnung/Semester zuordnung
 */
function semesterStoZuordnung()
{
	drawHeader('Neue Semester Zuordnung');
	$("#data").load('studienordnung.inc.php?method=semesterStoZuordnung&studienordnung_id='+studienordnung_id);
}

/**
 * Speichert die Studienordnung/Semester zuordnung
 */
function saveSemesterStoZuordnung(studiensemester, ausbildungssemester)
{
	if(studiensemester == undefined &&  ausbildungssemester == undefined)
	{
		var sem = $("#studiensemester").val();
		var cells = $("#studiensemester").parents().closest("tr").find("input[type=checkbox]");
		var semester = new Array();
		var semesterKurzbz = "";

		for(var i = 0; i < cells.length; i++)
		{
			//semester[cells[i].getAttribute("semester")] = cells[i].checked;
			semester.push(cells[i].checked);
		}

		var studiensemester = $("#studiensemester").val();
		for(var j=0; j<semester.length; j++)
		{
			if(semester[j] === true)
			{
				$.ajax({
					dataType: "json",
					url: "../../soap/studienordnung.json.php",
					type: "POST",
					data: {
						"method": "saveSemesterZuordnung",
						"studienordnung_id": studienordnung_id,
						"studiensemester_kurzbz" : studiensemester,
						"ausbildungssemester": j+1
					}
				}).success(function(data)
				{
					if(data.error === "true")
					{
						alert(data.errormsg);
					}
					semesterStoZuordnung();
				});
			}
		}
	}
	else
	{
		$.ajax({
			dataType: "json",
			url: "../../soap/studienordnung.json.php",
			type: "POST",
			data: {
				"method": "saveSemesterZuordnung",
				"studienordnung_id": studienordnung_id,
				"studiensemester_kurzbz" : studiensemester,
				"ausbildungssemester": ausbildungssemester
			}
		}).success(function(data)
		{
			if(data.error === "true")
			{
				alert(data.errormsg);
			}
			semesterStoZuordnung();
		});
	}
	
}

function deleteSemesterZuordnung(ausbildungssemester_kurzbz, studiensemester)
{
	if(studiensemester == undefined)
	{
		var row = $("#row_"+ausbildungssemester_kurzbz);
		$.ajax({
			dataType: "json",
			url: "../../soap/fhcomplete.php",
			type: "POST",
			data: {
				"typ":"json",
				"class" : "studienordnung",
				"method": "deleteSemesterZuordnung",
				"parameter_0": studienordnung_id,
				"parameter_1" : ausbildungssemester_kurzbz
			}
		}).success(function(data)
		{
			semesterStoZuordnung();
		});
	}
	else
	{
		$.ajax({
			dataType: "json",
			url: "../../soap/fhcomplete.php",
			type: "POST",
			data: {
				"typ":"json",
				"class" : "studienordnung",
				"method": "deleteSemesterZuordnung",
				"parameter_0": studienordnung_id,
				"parameter_1" : ausbildungssemester_kurzbz,
				"parameter_2" : studiensemester
			}
		}).success(function(data)
		{
			semesterStoZuordnung();
		});
	}
	
}