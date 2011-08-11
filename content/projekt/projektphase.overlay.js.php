<?php
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */

require_once('../../config/vilesci.config.inc.php');

?>
// *********** Globale Variablen *****************//

var datasourceTreeProjektphase; //Datasource des Tree Projektphase
var selectIDProjektphase=null; //ID des Task Eintrages der nach dem Refresh markiert werden soll
// ********** Observer und Listener ************* //

// ****
// * Observer fuer LV Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var observerTreeProjektphase =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) { debug('onerror:'+pError); },
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('projekttask-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Lehreinheit wieder
// * markiert
// ****
var TaskTreeListener =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
  		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
	    window.setTimeout(TaskTreeSelectTask,10);
		// Progressmeter stoppen
		//document.getElementById('statusbar-progressmeter').setAttribute('mode','determined');
	}
};

// ****************** FUNKTIONEN ************************** //

// ****
// * Asynchroner (Nicht blockierender) Refresh des LV Trees
// ****
function TaskTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//markierte Lehreinheit global speichern damit diese LE nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('projekttask-tree');
		
	try
	{
		selectIDProjektphase = getTreeCellText(tree, "projekttask-treecol-projekttask_id", tree.currentIndex);
	}
	catch(e)
	{
		selectIDProjektphase=null;
	}
	datasourceTreeProjektphase.Refresh(false); //non blocking
}

// ****
// * neuen Task anlegen
// ****
function TaskNeu()
{
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	alert('Neuer Task - noch nicht implementiert');
	/*
	var tree = document.getElementById('lehrveranstaltung-tree');

	//Details zuruecksetzen
	LeDetailReset();

	//Detail Tab als aktiv setzen
	document.getElementById('lehrveranstaltung-tabbox').selectedIndex=0;

	//Lektor-Tab und GruppenTree ausblenden
	document.getElementById('lehrveranstaltung-detail-tree-lehreinheitgruppe').hidden=true;
	document.getElementById('lehrveranstaltung-detail-label-lehreinheitgruppe').hidden=true;
	document.getElementById('lehrveranstaltung-tab-lektor').collapsed=true;

	//Lehrveranstaltungs_id holen
	var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehrveranstaltung_id"] : "lehrveranstaltung-treecol-lehrveranstaltung_id";
	var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);

	//Lehrform setzen
	var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehrform"] : "lehrveranstaltung-treecol-lehrform";
	var lehrform_kurzbz=tree.view.getCellText(tree.currentIndex,col);

	//Lehrfach drop down setzen

	//ID in globale Variable speichern
	LeDetailLehrfach_id='';
	var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-bezeichnung"] : "lehrveranstaltung-treecol-bezeichnung";
	LeDetailLehrfach_label=tree.view.getCellText(tree.currentIndex,col);

	lehrfachmenulist = document.getElementById('lehrveranstaltung-detail-menulist-lehrfach');
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);

	//Entfernen der alten Datasources
	var oldDatasources = lehrfachmenulist.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		lehrfachmenulist.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	lehrfachmenulist.builder.rebuild();

	//Url zusammenbauen
	var url = '<?php echo APP_ROOT;?>rdf/lehrfach.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+'&'+gettimestamp();

	//RDF holen
	var newDs  = rdfService.GetDataSource(url);
	lehrfachmenulist.database.AddDataSource(newDs);

	//SinkObserver hinzufuegen
	var sink = newDs.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	sink.addXMLSinkObserver(LeDetailLehrfachSinkObserver);

	document.getElementById('lehrveranstaltung-detail-textbox-lehrveranstaltung').value=lehrveranstaltung_id;
	document.getElementById('lehrveranstaltung-detail-checkbox-new').checked=true;
	document.getElementById('lehrveranstaltung-detail-textbox-stundenblockung').value='2';
	document.getElementById('lehrveranstaltung-detail-textbox-wochenrythmus').value='1';
	if(lehrform_kurzbz=='')
		lehrform_kurzbz='UE';
	document.getElementById('lehrveranstaltung-detail-menulist-lehrform').value=lehrform_kurzbz;

	var stsem = getStudiensemester();
	document.getElementById('lehrveranstaltung-detail-menulist-studiensemester').value=stsem;
	
	//Defaultwert fuer Anmerkung
	document.getElementById('lehrveranstaltung-detail-textbox-anmerkung').value='<?php echo str_replace("'","\'",LEHREINHEIT_ANMERKUNG_DEFAULT);?>';
	*/
}
// ****
// * Selectiert die Lektorzuordnung nachdem der Tree
// * rebuildet wurde.
// ****
function TaskTreeSelectTask()
{
	var tree=document.getElementById('projekttask-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende ID gespeichert
	if(selectIDProjektphase!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//id der row holen
	   		id = getTreeCellText(tree, "projekttask-treecol-projekttask_id", i);
			
			//wenn dies die zu selektierende Zeile
			if(selectIDProjektphase==id)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	}
}

// Dialog fuer neues Projekt starten
function ProjektphaseNeu()
{
    // Trick 17	(sonst gibt's ein Permission denied)
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

    var tree=document.getElementById('tree-projektmenue');
    alert(tree);
    var projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
    window.open('<?php echo APP_ROOT; ?>content/projekt/projektphase.window.xul.php?projekt_kurzbz='+projekt_kurzbz,'Projektphase anlegen', 'height=384,width=512,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no');
    //alert (oe);
}
