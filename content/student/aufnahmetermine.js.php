<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
?>
// ********** FUNKTIONEN ********** //
var AufnahmeterminePrestudentID='';
var AufnahmeTermineStudienplanID='';
var AufnahmeTermineStudiengang='';

// ****
// * Laedt die Trees
// ****
function loadAufnahmeTermine(prestudent_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	AufnahmeTerminePrestudentID = prestudent_id;
	AufnahmeTermineLoadTree();

	document.getElementById('aufnahmetermine-textbox-gesamtpunkte').disabled=false;
	document.getElementById('aufnahmetermine-button-savegesamtpunkte').disabled=false;
	document.getElementById('aufnahmetermine-button-calculatetotal').disabled=false;

	// Gruppen DropDown laden
	var aufnahmegruppemenulist = document.getElementById('aufnahmetermine-menulist-aufnahmegruppe');
	if(aufnahmegruppemenulist)
	{
		var url="<?php echo APP_ROOT ?>rdf/gruppen.rdf.php?aufnahmegruppe=true&optional=true";

		//Alte DS entfernen
		var oldDatasources = aufnahmegruppemenulist.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			aufnahmegruppemenulist.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		aufnahmegruppemenulist.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		var myDatasource = rdfService.GetDataSourceBlocking(url);
		aufnahmegruppemenulist.database.AddDataSource(myDatasource);
		aufnahmegruppemenulist.builder.rebuild();
	}
	// Gesamtpunkte laden und anzeigen

	var url = '<?php echo APP_ROOT ?>rdf/student.rdf.php?prestudent_id='+prestudent_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
		getService(Components.interfaces.nsIRDFService);

	var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/student/" + prestudent_id);

	var predicateNS = "http://www.technikum-wien.at/student/rdf";

	punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));
	var person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	AufnahmeTermineStudiengang = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiengang_kz" ));
	reihungstestangetreten = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#reihungstestangetreten" ));
	var aufnahmegruppe_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aufnahmegruppe_kurzbz" ));
	document.getElementById('aufnahmetermine-textbox-gesamtpunkte').value=punkte;
	if(reihungstestangetreten=='true')
		document.getElementById('aufnahmetermine-checkbox-reihungstestangetreten').checked=true;
	else
		document.getElementById('aufnahmetermine-checkbox-reihungstestangetreten').checked=false;
	document.getElementById('aufnahmetermine-menulist-aufnahmegruppe').value = aufnahmegruppe_kurzbz;
	AufnahmeTermineStudienplanID = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studienplan_id" ));

	// ReihungstestDropDown laden
	var reihungstestmenulist = document.getElementById('aufnahmetermine-menulist-reihungstest');
	var url="<?php echo APP_ROOT ?>rdf/reihungstest.rdf.php?optional=true&prestudent_id="+AufnahmeTerminePrestudentID;

	//Alte DS entfernen
	var oldDatasources = reihungstestmenulist.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		reihungstestmenulist.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	reihungstestmenulist.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var myDatasource = rdfService.GetDataSourceBlocking(url);
	reihungstestmenulist.database.AddDataSource(myDatasource);
	reihungstestmenulist.builder.rebuild();

	// Studienplan DropDown laden
	var studienplanmenulist = document.getElementById('aufnahmetermine-menulist-studienplan');
	var url="<?php echo APP_ROOT ?>rdf/studienplan.rdf.php?person_id="+person_id;

	//Alte DS entfernen
	var oldDatasources = studienplanmenulist.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		studienplanmenulist.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	studienplanmenulist.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var myDatasource = rdfService.GetDataSourceBlocking(url);
	studienplanmenulist.database.AddDataSource(myDatasource);
	studienplanmenulist.builder.rebuild();
}

/**
 * Laedt den Tree mit den Terminen
 */
function AufnahmeTermineLoadTree()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	//Termine laden
	url = "<?php echo APP_ROOT; ?>rdf/aufnahmetermine.rdf.php?prestudent_id="+AufnahmeTerminePrestudentID+"&ts="+gettimestamp();

	var treeAufnahmeTermine=document.getElementById('aufnahmetermine-tree');

	//Alte DS entfernen
	var oldDatasources = treeAufnahmeTermine.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		treeAufnahmeTermine.database.RemoveDataSource(oldDatasources.getNext());
	}
	treeAufnahmeTermine.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var AufnahmeTerminTreeDatasource = rdfService.GetDataSource(url);
	AufnahmeTerminTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	treeAufnahmeTermine.database.AddDataSource(AufnahmeTerminTreeDatasource);
}

/**
 * Speichert die Gesamtpunkte
 */
function AufnahmeTermineSaveGesamtpunkte()
{
	var punkte = document.getElementById('aufnahmetermine-textbox-gesamtpunkte').value;
	var aufnahmegruppe_kurzbz = document.getElementById('aufnahmetermine-menulist-aufnahmegruppe').value;
	var reihungstestangetreten = document.getElementById('aufnahmetermine-checkbox-reihungstestangetreten').checked;
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'AufnahmeTermineSaveGesamtpunkte');

	req.add('prestudent_id', AufnahmeTerminePrestudentID);
	req.add('punkte', punkte);
	req.add('reihungstestangetreten',reihungstestangetreten);
	req.add('aufnahmegruppe_kurzbz',aufnahmegruppe_kurzbz);

	var response = req.executePOST();
	var val = new ParseReturnValue(response);

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
		return false;
	}
	else
	{
		return true;
	}
}

function AufnahmeTermineCalculateTotal()
{
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'AufnahmeTermineBerechneGesamtpunkte');
	req.add('prestudent_id', AufnahmeTerminePrestudentID);

	var response = req.executePOST();
	var val = new ParseReturnValue(response);

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
		return false;
	}
	else
	{
		document.getElementById('aufnahmetermine-textbox-gesamtpunkte').value=val.dbdml_data;
		return true;
	}
}

/**
 * Laedt die Details bei Auswahl eines Eintrages aus dem Tree
 */
function AufnahmeTermineAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('aufnahmetermine-tree');

	if (tree.currentIndex==-1) return;

	AufnahmeTermineDisableFields(false);
	AufnahmeTermineReihungstestDropDownRefresh(true);

	//Ausgewaehlten Eintrag holen
	var rt_person_id = getTreeCellText(tree, 'aufnahmetermine-tree-rt_person_id', tree.currentIndex);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/aufnahmetermine.rdf.php?rt_person_id='+rt_person_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
			getService(Components.interfaces.nsIRDFService);

	var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/aufnahmetermine/"+rt_person_id);

	var predicateNS = "http://www.technikum-wien.at/aufnahmetermine/rdf";

	//Daten holen

	var person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	var rt_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#rt_id" ));
	var anmeldedatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmeldedatum" ));
	var teilgenommen = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#teilgenommen" ));
	var punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));
	var studienplan_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studienplan_id" ));
	var studienplan_studiengang_kz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studienplan_studiengang_kz" ));
    var endpunkte_inkl_gebiete = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#endpunkte_inkl_gebiete" ));
    var endpunkte_exkl_gebiete = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#endpunkte_exkl_gebiete" ));
    var typ = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#typ" ));

	document.getElementById('aufnahmetermine-textbox-rt_person_id').value=rt_person_id;
	document.getElementById('aufnahmetermine-textbox-person_id').value=person_id;
	document.getElementById('aufnahmetermine-textbox-studienplan_studiengang_kz').value=studienplan_studiengang_kz;
	document.getElementById('aufnahmetermine-checkbox-neu').checked=false;
	document.getElementById('aufnahmetermine-textbox-anmeldungreihungstest').value=anmeldedatum;
	MenulistSelectItemOnValue('aufnahmetermine-menulist-reihungstest', rt_id);

	if(teilgenommen=='Ja')
		document.getElementById('aufnahmetermine-checkbox-teilgenommen').checked=true;
	else
		document.getElementById('aufnahmetermine-checkbox-teilgenommen').checked=false;
	document.getElementById('aufnahmetermine-textbox-punkte').value=punkte;
	document.getElementById('aufnahmetermine-menulist-studienplan').value=studienplan_id;
    document.getElementById('aufnahmetermine-textbox-endpunkte-inkl-gebiete').value = endpunkte_inkl_gebiete;
    document.getElementById('aufnahmetermine-textbox-endpunkte-exkl-gebiete').value = endpunkte_exkl_gebiete;

    // Sichtbarkeit der RT-Vergleichsergebnispunkte (ohne Quereinsteiger)
    var basisgebiet_punkte_anzeigen = <?php echo (defined('FAS_REIHUNGSTEST_PUNKTE_BASISGEBIET_ANZEIGEN') && FAS_REIHUNGSTEST_PUNKTE_BASISGEBIET_ANZEIGEN) ? 'true' : 'false' ?>;

    //  * Generell Anzeige nur wenn über config-Datei gesetzt ist
    if (basisgebiet_punkte_anzeigen)
    {
        //  * für Bachelor-Studiengänge anzeigen
        if (typ == 'b')
        {
            document.getElementById('aufnahmetermine-groupbox-vergleich-endpunkte').hidden = false;
        }
        //  * für Master-Studiengänge verstecken
        else if (typ == 'm')
        {
            document.getElementById('aufnahmetermine-groupbox-vergleich-endpunkte').hidden = true;
        }
    }
}

function AufnahmeTermineNeu()
{
	AufnahmeTermineDisableFields(false);
	AufnahmeTermineResetFields();
	AufnahmeTermineReihungstestDropDownRefresh(true);
}

/**
 * Loescht einen Aufnahmetermin
 */
function AufnahmeTermineDelete()
{
	var rt_person_id = document.getElementById('aufnahmetermine-textbox-rt_person_id').value;

	if(!confirm("Wollen Sie diesen Eintrag wirklich löschen?"))
		return;

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'AufnahmeTermineDelete');
	req.add('rt_person_id', rt_person_id);

	var response = req.executePOST();
	var val = new ParseReturnValue(response);

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
		return false;
	}
	else
	{
		AufnahmeTermineLoadTree();
		return true;
	}
}

function AufnahemTermineReihungstestPunkteTransmit()
{
	var reihungstest_id = document.getElementById('aufnahmetermine-menulist-reihungstest').value;
	var person_id = document.getElementById('aufnahmetermine-textbox-person_id').value;
	var studienplan_studiengang_kz = document.getElementById('aufnahmetermine-textbox-studienplan_studiengang_kz').value;

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'getReihungstestPunkte');

	req.add('person_id', person_id);
	req.add('studienplan_studiengang_kz', studienplan_studiengang_kz);
	req.add('reihungstest_id', reihungstest_id);
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		document.getElementById('aufnahmetermine-textbox-punkte').value = val.dbdml_data;
	}
}

function setEndpunkteAsPunkte(id)
{
	var punkte = document.getElementById(id).value;
	document.getElementById('aufnahmetermine-textbox-punkte').value = punkte;
}

/**
 * Speichert einen AufnahmeTermin
 */
function AufnahmeTermineSpeichern()
{
	var rt_person_id = document.getElementById('aufnahmetermine-textbox-rt_person_id').value;
	var rt_id = document.getElementById('aufnahmetermine-menulist-reihungstest').value;
	var person_id = document.getElementById('aufnahmetermine-textbox-person_id').value;
	var neu = document.getElementById('aufnahmetermine-checkbox-neu').checked;
	var anmeldedatum = document.getElementById('aufnahmetermine-textbox-anmeldungreihungstest').iso;
	var rt_id_new = document.getElementById('aufnahmetermine-menulist-reihungstest').value;
	var teilgenommen = document.getElementById('aufnahmetermine-checkbox-teilgenommen').checked;
	var punkte = document.getElementById('aufnahmetermine-textbox-punkte').value;
	var studienplan_id = document.getElementById('aufnahmetermine-menulist-studienplan').value;

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'AufnahmeTermineSave');

	req.add('rt_id', rt_id);
	req.add('rt_person_id', rt_person_id);
	req.add('person_id', person_id);
	req.add('prestudent_id', AufnahmeTerminePrestudentID);
	req.add('neu', neu);
	req.add('anmeldedatum', anmeldedatum);
	req.add('teilgenommen', teilgenommen);
	req.add('punkte', punkte);
	req.add('studienplan_id', studienplan_id);

	var response = req.executePOST();
	var val = new ParseReturnValue(response);

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)

		document.getElementById('aufnahmetermine-textbox-rt_person_id').value=val.dbdml_data;
		document.getElementById('aufnahmetermine-checkbox-neu').checked=false;
		AufnahmeTermineLoadTree();

		return false;
	}
	else
	{
		document.getElementById('aufnahmetermine-textbox-rt_person_id').value=val.dbdml_data;
		document.getElementById('aufnahmetermine-checkbox-neu').checked=false;
		AufnahmeTermineLoadTree();
		return true;
	}
}

/**
 * Aktiviert oder Deaktiviert die Eingabefelder
 * @param val boolean true | false
 */
function AufnahmeTermineDisableFields(val)
{
	document.getElementById('aufnahmetermine-button-speichern').disabled=val;
	document.getElementById('aufnahmetermine-textbox-punkte').disabled=val;
	document.getElementById('aufnahmetermine-checkbox-teilgenommen').disabled=val;
	document.getElementById('aufnahmetermine-textbox-anmeldungreihungstest').disabled=val;
	document.getElementById('aufnahmetermine-menulist-reihungstest').disabled=val;
	document.getElementById('aufnahmetermine-button-anmeldungreihungstest-heute').disabled=val;
	document.getElementById('aufnahmetermine-menulist-studienplan').disabled=val;
}

/**
 * Leert die Eingabefelder
 */
function AufnahmeTermineResetFields()
{
	document.getElementById('aufnahmetermine-textbox-punkte').value='';
	document.getElementById('aufnahmetermine-checkbox-teilgenommen').checked=false;
	document.getElementById('aufnahmetermine-textbox-anmeldungreihungstest').value='';
	document.getElementById('aufnahmetermine-menulist-reihungstest').value='';
	document.getElementById('aufnahmetermine-textbox-person_id').value='';
	document.getElementById('aufnahmetermine-textbox-studienplan_studiengang_kz').value='';
	document.getElementById('aufnahmetermine-checkbox-neu').checked=true;
	document.getElementById('aufnahmetermine-textbox-rt_person_id').value='';
	document.getElementById('aufnahmetermine-menulist-studienplan').value=AufnahmeTermineStudienplanID;
}


/**
 * Setzt das aktuelle Datum als Anmeldedatum
 */
function AufnahmeTermineAnmeldungreihungstestHeute()
{
	var now = new Date();
	var jahr = now.getFullYear();

	monat = now.getMonth()+1;
	if(monat<10) monat='0'+monat;
	tag = now.getDate();
	if(tag<10) tag='0'+tag;

	document.getElementById('aufnahmetermine-textbox-anmeldungreihungstest').value=tag+'.'+monat+'.'+jahr;
}

/**
 * Refresht das DropDown mit den Reihungstestterminen
 */
function AufnahmeTermineReihungstestDropDownRefresh(prestudent)
{

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var menulist = document.getElementById('aufnahmetermine-menulist-reihungstest');
	if(typeof(prestudent)=='undefined')
		var url="<?php echo APP_ROOT ?>rdf/reihungstest.rdf.php?include_id=&studiengang_kz="+AufnahmeTermineStudiengang+"&"+gettimestamp();
	else
		var url="<?php echo APP_ROOT ?>rdf/reihungstest.rdf.php?optional=true&prestudent_id="+AufnahmeTerminePrestudentID+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = menulist.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		menulist.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	menulist.builder.rebuild();
	btn = document.getElementById('aufnahmetermine-button-reihungstest-refresh');
	btn.setAttribute('image','../../skin/images/spinner.gif');
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var myDatasource = rdfService.GetDataSourceBlocking(url);
	menulist.database.AddDataSource(myDatasource);
	menulist.builder.rebuild();
	btn.setAttribute('image','../../skin/images/refresh.png');
}

function AufnahmeTermineReihungstestEdit()
{
	var rt_id = document.getElementById('aufnahmetermine-menulist-reihungstest').value;
	var url="<?php echo APP_ROOT ?>vilesci/stammdaten/reihungstestverwaltung.php?reihungstest_id="+rt_id;
	window.open(url);
}