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

	// Gesamtpunkte laden und anzeigen

	var url = '<?php echo APP_ROOT ?>rdf/student.rdf.php?prestudent_id='+prestudent_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
		getService(Components.interfaces.nsIRDFService);

	var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/student/" + prestudent_id);

	var predicateNS = "http://www.technikum-wien.at/student/rdf";

	punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));
	reihungstestangetreten = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#reihungstestangetreten" ));
	document.getElementById('aufnahmetermine-textbox-gesamtpunkte').value=punkte;
	if(reihungstestangetreten=='true')
		document.getElementById('aufnahmetermine-checkbox-reihungstestangetreten').checked=true;
	else
		document.getElementById('aufnahmetermine-checkbox-reihungstestangetreten').checked=false;
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

	// OrtDropDown laden
	var ortmenulist = document.getElementById('aufnahmetermine-menulist-ort');
	var url="<?php echo APP_ROOT ?>rdf/orte.rdf.php?optional=true";

	//Alte DS entfernen
	var oldDatasources = ortmenulist.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		ort.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	ortmenulist.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var myDatasource = rdfService.GetDataSourceBlocking(url);
	ortmenulist.database.AddDataSource(myDatasource);
	ortmenulist.builder.rebuild();
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
	var reihungstestangetreten = document.getElementById('aufnahmetermine-checkbox-reihungstestangetreten').checked;
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'AufnahmeTermineSaveGesamtpunkte');

	req.add('prestudent_id', AufnahmeTerminePrestudentID);
	req.add('punkte', punkte);
	req.add('reihungstestangetreten',reihungstestangetreten);

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
	var ort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ort_kurzbz" ));
	var studienplan_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studienplan_id" ));

	document.getElementById('aufnahmetermine-textbox-rt_person_id').value=rt_person_id;
	document.getElementById('aufnahmetermine-textbox-person_id').value=person_id;
	document.getElementById('aufnahmetermine-checkbox-neu').checked=false;
	document.getElementById('aufnahmetermine-textbox-anmeldungreihungstest').value=anmeldedatum;
	MenulistSelectItemOnValue('aufnahmetermine-menulist-reihungstest', rt_id);
	MenulistSelectItemOnValue('aufnahmetermine-menulist-ort', ort);
	if(teilgenommen=='Ja')
		document.getElementById('aufnahmetermine-checkbox-teilgenommen').checked=true;
	else
		document.getElementById('aufnahmetermine-checkbox-teilgenommen').checked=false;
	document.getElementById('aufnahmetermine-textbox-punkte').value=punkte;
	document.getElementById('aufnahmetermine-textbox-studienplan_id').value=studienplan_id;
}

function AufnahmeTermineNeu()
{
	AufnahmeTermineDisableFields(false);
	AufnahmeTermineResetFields();
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
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'getReihungstestPunkte');

	req.add('prestudent_id', AufnahmeTerminePrestudentID);

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
	var ort = document.getElementById('aufnahmetermine-menulist-ort').value;
	var studienplan_id = document.getElementById('aufnahmetermine-textbox-studienplan_id').value;

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
	req.add('ort_kurzbz', ort);
	req.add('studienplan_id', studienplan_id);

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
	document.getElementById('aufnahmetermine-menulist-ort').disabled=val;
	document.getElementById('aufnahmetermine-button-anmeldungreihungstest-heute').disabled=val;
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
	document.getElementById('aufnahmetermine-menulist-ort').value='';
	document.getElementById('aufnahmetermine-textbox-person_id').value='';
	document.getElementById('aufnahmetermine-checkbox-neu').checked=true;
	document.getElementById('aufnahmetermine-textbox-rt_person_id').value='';
	document.getElementById('aufnahmetermine-textbox-studienplan_id').value=AufnahmeTermineStudienplanID;
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
function AufnahmeTermineReihungstestDropDownRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('aufnahmetermine-menulist-reihungstest');
	var url="<?php echo APP_ROOT ?>rdf/reihungstest.rdf.php?optional=true&prestudent_id="+AufnahmeTerminePrestudentID+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	tree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var myDatasource = rdfService.GetDataSource(url);
	tree.database.AddDataSource(myDatasource);
}