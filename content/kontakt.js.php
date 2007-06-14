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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');

$conn = pg_pconnect(CONN_STRING);

$user = get_uid();
loadVariables($conn, $user);
?>
// ********** GLOBALE VARIABLEN ********** //
var AdressenTreeDatasource=''; // Datasource des Adressen Trees
var KontaktAdresseSelectID=null; // ID der Adresse die nach dem Rebuild markiert werden soll
var KontaktTreeDatasource=''; // Datasource des Kontakt Trees
var KontaktKontaktSelectID=null; // ID des Kontaktes der nach dem Rebuild markiert werden soll
// ********** LISTENER UND OBSERVER ********** //

// ****
// * Observer fuer Adressen Tree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var KontaktAdressenTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('kontakt-adressen-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var KontaktAdressenTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(KontaktAdressenTreeSelectID,10);
  }
};

// ****
// * Observer fuer Kontakt Tree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var KontaktKontaktTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('kontakt-kontakt-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var KontaktKontaktTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(KontaktKontaktTreeSelectID,10);
  }
};
// ********** FUNKTIONEN ********** //
function loadKontakte(person_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	//Adressen laden
	url = "<?php echo APP_ROOT; ?>rdf/adresse.rdf.php?person_id="+person_id+"&"+gettimestamp();	
	var treeAdressen=document.getElementById('kontakt-adressen-tree');
	
	//Alte DS entfernen
	var oldDatasources = treeAdressen.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		treeAdressen.database.RemoveDataSource(oldDatasources.getNext());
	}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	AdressenTreeDatasource = rdfService.GetDataSource(url);
	AdressenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	AdressenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	treeAdressen.database.AddDataSource(AdressenTreeDatasource);
	AdressenTreeDatasource.addXMLSinkObserver(KontaktAdressenTreeSinkObserver);
	treeAdressen.builder.addListener(KontaktAdressenTreeListener);
		
	//Kontakte laden
	url = "<?php echo APP_ROOT; ?>rdf/kontakt.rdf.php?person_id="+person_id+"&"+gettimestamp();	
	var treeKontakt=document.getElementById('kontakt-kontakt-tree');
	
	//Alte DS entfernen
	var oldDatasources = treeKontakt.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		treeKontakt.database.RemoveDataSource(oldDatasources.getNext());
	}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	KontaktTreeDatasource = rdfService.GetDataSource(url);
	KontaktTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	KontaktTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	treeKontakt.database.AddDataSource(KontaktTreeDatasource);
	KontaktTreeDatasource.addXMLSinkObserver(KontaktKontaktTreeSinkObserver);
	treeKontakt.builder.addListener(KontaktKontaktTreeListener);
}

// ********** ADRESSEN ********** //

// ****
// * Selectiert eine Adresse nachdem der Tree
// * rebuildet wurde.
// ****
function KontaktAdressenTreeSelectID()
{
	var tree=document.getElementById('kontakt-adressen-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende Adresse gespeichert
	if(KontaktAdresseSelectID!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["kontakt-adresse-treecol-adresse_id"] : "kontakt_adresse-treecol-adresse_id";
			id=tree.view.getCellText(i,col);
						
			if(id == KontaktAdresseSelectID)
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

// ********** KONTAKTE ********** //

// ****
// * Selectiert eine Adresse nachdem der Tree
// * rebuildet wurde.
// ****
function KontaktKontaktTreeSelectID()
{
	var tree=document.getElementById('kontakt-kontakt-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende Adresse gespeichert
	if(KontaktKontaktSelectID!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["kontakt-kontakt-treecol-kontakt_id"] : "kontakt_kontakt-treecol-kontakt_id";
			id=tree.view.getCellText(i,col);
						
			if(id == KontaktKontaktSelectID)
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