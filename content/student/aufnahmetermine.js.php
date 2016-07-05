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
var AufnahmeTerminePrestudentID='';
var AufnahmeTerminStudiengang='';

// ****
// * Laedt die Trees
// ****
function loadAufnahmeTermine(prestudent_id, studiengang_kz)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	AufnahmeTerminePrestudentID = prestudent_id;
	AufnahmeTerminStudiengang = studiengang_kz
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
	document.getElementById('aufnahmetermine-textbox-gesamtpunkte').value=punkte;

	// ReihungstestDropDown laden
	var reihungstestmenulist = document.getElementById('aufnahmetermine-menulist-reihungstest');
	var url="<?php echo APP_ROOT ?>rdf/reihungstest.rdf.php?optional=true&studiengang_kz="+AufnahmeTerminStudiengang;

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

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var AufnahmeTerminTreeDatasource = rdfService.GetDataSource(url);
	AufnahmeTerminTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	treeAufnahmeTermine.database.AddDataSource(AufnahmeTerminTreeDatasource);

}

function AufnahmeTermineSaveGesamtpunkte()
{
	// TODO
	alert('Dieses Feature ist noch nicht implementiert');
}

function AufnahmeTermineCalculateTotal()
{
	// TODO
	alert('Dieses Feature ist noch nicht implementiert');
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
	var rt_id = getTreeCellText(tree, 'aufnahmetermine-tree-rt_id', tree.currentIndex);
	var person_id = getTreeCellText(tree, 'aufnahmetermine-tree-person_id', tree.currentIndex);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/aufnahmetermine.rdf.php?person_id='+person_id+'&rt_id='+rt_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/aufnahmetermine/"+rt_id+'/'+person_id);

	var predicateNS = "http://www.technikum-wien.at/aufnahmetermine/rdf";

	//Daten holen

	person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	rt_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#rt_id" ));
	anmeldedatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmeldedatum" ));
	teilgenommen = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#teilgenommen" ));
	punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));

	document.getElementById('aufnahmetermine-textbox-rt_id').value=rt_id;
	document.getElementById('aufnahmetermine-textbox-person_id').value=person_id;
	document.getElementById('aufnahmetermine-checkbox-neu').checked=false;
	document.getElementById('aufnahmetermine-textbox-anmeldungreihungstest').value=anmeldedatum;
	MenulistSelectItemOnValue('aufnahmetermine-menulist-reihungstest', rt_id);
	if(teilgenommen=='Ja')
		document.getElementById('aufnahmetermine-checkbox-teilgenommen').checked=true;
	else
		document.getElementById('aufnahmetermine-checkbox-teilgenommen').checked=false;
	document.getElementById('aufnahmetermine-textbox-punkte').value=punkte;
}

function AufnahmeTermineNeu()
{
	AufnahmeTermineDisableFields(false);
	AufnahmeTermineResetFields();
}

function AufnahmeTermineDelete()
{
	// TODO
}

function AufnahemTermineReihungstestPunkteTransmit()
{
	// TODO
}

function AufnahmeTermineSpeichern()
{
	// TODO
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
	document.getElementById('aufnahmetermine-textbox-rt_id').value='';
	document.getElementById('aufnahmetermine-textbox-person_id').value='';
	document.getElementById('aufnahmetermine-checkbox-neu').checked=true;
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

function AufnahmeTermineReihungstestDropDownRefresh()
{
	// TODO
	alert("Dieses Feature ist noch nicht implementiert");
}