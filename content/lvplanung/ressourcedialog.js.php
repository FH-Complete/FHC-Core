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
 * Authors:  Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
?>

// *********** Globale Variablen *****************//
var RessourceZugeteiltTreeDatasource; //Datasource des RessourceZugeteiltTrees
var RessourceVerplanbarTreeDatasource; //Datasource des RessourceVerplanbarTrees
var RessourceStunden;
var RessourceDatum;
var RessourceStplIDs;
var	RessourceZugeteiltTreeDoubleRefresh=false;
var	RessourceVerplanbarTreeDoubleRefresh=false;

// ********** Observer und Listener ************* //

// ****
// * Observer fuer RessourceZugeteilt Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var RessourceZugeteiltTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('ressource-zugeteilt-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Betriebsmittelzuordnung wieder
// * markiert
// ****
var RessourceZugeteiltTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {  	  
		if(RessourceZugeteiltTreeDoubleRefresh)
	 	{
			// Doppeltes Rebuild damit nach leeren Trees korrekt geladen wird
			RessourceZugeteiltTreeDoubleRefresh=false;
			window.setTimeout("RessourceZugeteiltTreeRefresh()",10);
		}
		else
		{
			window.setTimeout(RessourceZugeteiltTreeSelectZuordnung,10);
		}
  }
};

// ****
// * Observer fuer RessourceVerplanbar Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var RessourceVerplanbarTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('ressource-verplanbar-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Betriebsmittelzuordnung wieder
// * markiert
// ****
var RessourceVerplanbarTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
		if(RessourceVerplanbarTreeDoubleRefresh)
		{
			// Doppeltes Rebuild damit nach leeren Trees korrekt geladen wird
			RessourceVerplanbarTreeDoubleRefresh=false;
			window.setTimeout("RessourceVerplanbarTreeRefresh()",10);
		}
		else
		{
    		window.setTimeout(RessourceVerplanbarTreeSelectZuordnung,10);
		}
  }
};

// ****
// * Laedt die Trees
// ****
function RessourceInit(datum, stunden, stplids)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	RessourceStunden = stunden;
	RessourceDatum = datum;
	RessourceStplIDs = stplids;

	// *** Zugeteilte Betriebsmittel ***
	var ressourcezugeteilttree = document.getElementById('ressource-zugeteilt-tree');
	// TODO Parameter
	url='<?php echo APP_ROOT;?>rdf/stundenplan_betriebsmittel.rdf.php?ts='+gettimestamp();

	for(i in stplids)
		url = url+'&stundenplan_ids[]='+stplids[i];
	
	//Alte DS entfernen
	var oldDatasources = ressourcezugeteilttree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		ressourcezugeteilttree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	ressourcezugeteilttree.builder.rebuild();
	
	try
	{
		RessourceZugeteiltTreeDatasource.removeXMLSinkObserver(RessourceZugeteiltTreeSinkObserver);
		ressourcezugeteilttree.builder.removeListener(RessourceZugeteiltTreeListener);
	}
	catch(e)
	{}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	RessourceZugeteiltTreeDatasource = rdfService.GetDataSource(url);
	RessourceZugeteiltTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	RessourceZugeteiltTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	ressourcezugeteilttree.database.AddDataSource(RessourceZugeteiltTreeDatasource);
	RessourceZugeteiltTreeDatasource.addXMLSinkObserver(RessourceZugeteiltTreeSinkObserver);
	ressourcezugeteilttree.builder.addListener(RessourceZugeteiltTreeListener);


	// *** Verplanbare Betriebsmittel ***
	var ressourceverplanbartree = document.getElementById('ressource-verplanbar-tree');
	// TODO Parameter
	url='<?php echo APP_ROOT;?>rdf/betriebsmittel.rdf.php?datum='+datum+'&'+gettimestamp();

	for(i in stunden)
		url = url+'&stunde[]='+stunden[i];
	
	//Alte DS entfernen
	var oldDatasources = ressourceverplanbartree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		ressourceverplanbartree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	ressourceverplanbartree.builder.rebuild();
	
	try
	{
		RessourceVerplanbarTreeDatasource.removeXMLSinkObserver(RessourceVerplanbarTreeSinkObserver);
		ressourceverplanbartree.builder.removeListener(RessourceVerplanbarTreeDatasource);
	}
	catch(e)
	{}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	RessourceVerplanbarTreeDatasource = rdfService.GetDataSource(url);
	RessourceVerplanbarTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	RessourceVerplanbarTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	ressourceverplanbartree.database.AddDataSource(RessourceVerplanbarTreeDatasource);
	RessourceVerplanbarTreeDatasource.addXMLSinkObserver(RessourceVerplanbarTreeSinkObserver);
	ressourceverplanbartree.builder.addListener(RessourceVerplanbarTreeListener);
}

// Hinzufuegen der Ressourcenzuordnung
function RessourceAdd()
{
	var tree = document.getElementById('ressource-verplanbar-tree');

	if (tree.currentIndex==-1) 
		return;

	//Ausgewaehlte Nr holen
	betriebsmittel_id=getTreeCellText(tree, "ressource-verplanbar-tree-betriebsmittel_id", tree.currentIndex);

	var url = '<?php echo APP_ROOT ?>content/tempusDBDML.php';
	var req = new phpRequest(url,'','');
		
	req.add('type', 'addressource');
	req.add('betriebsmittel_id', betriebsmittel_id);

	for(i in RessourceStplIDs)
		req.add('stpl_id[]', RessourceStplIDs[i]);

	for(i in RessourceStunden)
		req.add('stunden[]', RessourceStunden[i]);
		
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
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		RessourcenReloadTrees();
	}

}

// Entfernen der Ressourcenzuordnung
function RessourceRemove()
{
	var tree = document.getElementById('ressource-zugeteilt-tree');

	if (tree.currentIndex==-1) 
		return;

	var url = '<?php echo APP_ROOT ?>content/tempusDBDML.php';
	var req = new phpRequest(url,'','');
		
	req.add('type', 'deleteressource');

	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			stundenplan_betriebsmittel_id = getTreeCellText(tree, 'ressource-zugeteilt-tree-stundenplan_betriebsmittel_id', v);
			req.add('stundenplan_betriebsmittel_id[]', stundenplan_betriebsmittel_id);
		}
	}
		
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
		RessourcenReloadTrees();
		RessourcenDisableDetails()
	}
}

function RessourceZugeteiltTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	RessourceZugeteiltTreeDatasource.Refresh(false);
}

function RessourceVerplanbarTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	RessourceVerplanbarTreeDatasource.Refresh(false);
}

function RessourcenReloadTrees()
{

	RessourceZugeteiltTreeDoubleRefresh=true;
	RessourceVerplanbarTreeDoubleRefresh=true;
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	RessourceZugeteiltTreeDatasource.Refresh(false);
	RessourceVerplanbarTreeDatasource.Refresh(false);
}

// Speichert die Anmerkung
function RessourceSave()
{
	var stundenplan_betriebsmittel_id=document.getElementById('ressource-detail-stundenplan_betriebsmittel_id').value;
	var anmerkung = document.getElementById('ressource-detail-anmerkung').value;

	var url = '<?php echo APP_ROOT ?>content/tempusDBDML.php';
	var req = new phpRequest(url,'','');
		
	req.add('type', 'saveressource');
	req.add('stundenplan_betriebsmittel_id', stundenplan_betriebsmittel_id);
	req.add('anmerkung', anmerkung);
		
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
		RessourcenReloadTrees();
		RessourcenDisableDetails();
	}
}

function RessourcenDisableDetails()
{
	document.getElementById('ressource-detail-anmerkung').value='';
	document.getElementById('ressource-detail-anmerkung').disabled=true;
	document.getElementById('ressource-detail-stundenplan_betriebsmittel_id').value='';
	document.getElementById('ressource-detail-speichern').disabled=true;
}

/**
 * Bei der Auswahl der zugeteilten Ressource wird diese geladen und die Anmerkung angezeigt
 */
function RessourceZugeteiltAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('ressource-zugeteilt-tree');

	if (tree.currentIndex==-1) 
		return;

	//Ausgewaehlte Nr holen
	var stundenplan_betriebsmittel_id=getTreeCellText(tree, "ressource-zugeteilt-tree-stundenplan_betriebsmittel_id", tree.currentIndex);

	var req = new phpRequest('<?php echo APP_ROOT; ?>rdf/stundenplan_betriebsmittel.rdf.php','','');
	req.add('stundenplan_betriebsmittel_id',stundenplan_betriebsmittel_id);

	var response = req.execute();
	// Datasource holen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/stundenplanbetriebsmittel');

	dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/stundenplanbetriebsmittel/" + stundenplan_betriebsmittel_id);

	var predicateNS = "http://www.technikum-wien.at/stundenplanbetriebsmittel/rdf";

	//Daten holen
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	beschreibung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beschreibung" ));

	document.getElementById('ressource-detail-anmerkung').disabled=false;
	document.getElementById('ressource-detail-speichern').disabled=false;

	document.getElementById('ressource-detail-caption').label='Anmerkungen zu '+beschreibung;
	document.getElementById('ressource-detail-anmerkung').value=anmerkung;

	document.getElementById('ressource-detail-stundenplan_betriebsmittel_id').value=stundenplan_betriebsmittel_id;
}

function RessourceZugeteiltTreeSelectZuordnung()
{
}

function RessourceVerplanbarTreeSelectZuordnung()
{
}
