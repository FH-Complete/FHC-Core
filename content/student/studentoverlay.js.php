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

require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');

$conn = pg_pconnect(CONN_STRING);

$user = get_uid();
loadVariables($conn, $user);
?>
// *********** Globale Variablen *****************//
var StudentSelectUid=null; //Student der nach dem Refresh markiert werden soll

// ********** Observer und Listener ************* //

// ****
// * Observer fuer Studenten Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Student wieder
// * markiert
// ****
var StudentTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(StudentTreeSelectStudent,10);
  }
};

// ***************** KEY Events ************************* //

// ****
// * Wird ausgefuehrt wenn eine Taste gedrueckt wird und der Focus
// * im Lehrveranstaltungs-tree ist
// * Beim Druecken von ENTF wird der markierte Student geloescht
// * Beim Druecken von F5 wird der Studententree aktualisiert
// ****
function StudentTreeKeyPress(event)
{
	if(event.keyCode==46) // Entf
		StudentDelete();
	else if(event.keyCode==116) // F5
		StudentTreeRefresh();
}

// ****************** FUNKTIONEN ************************** //

// ****
// * Asynchroner (Nicht blockierender) Refresh des StudentenTrees
// ****
function StudentTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//markierten Studenten global speichern damit dieser Student nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('student-tree');
	var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
	try
	{
		StudentSelectUid=tree.view.getCellText(tree.currentIndex,col);
		StudentTreeDatasource.Refresh(false); //non blocking
	}
	catch(e)
	{}
}

// ****
// * neuen Studenten anlegen
// ****
function StudentNeu()
{
	
}

// ****
// * Selectiert den Studenten nachdem der Tree
// * rebuildet wurde.
// ****
function StudentTreeSelectStudent()
{
	var tree=document.getElementById('student-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist der zu selektierende Student gespeichert
	if(StudentSelectUid!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//Uid der row holen
			col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
			uid=tree.view.getCellText(i,col);
						
			if(uid == StudentSelectUid)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	}
	document.getElementById('student-toolbar-label-anzahl').value='Anzahl: '+items;
}

// ****
// * Student loeschen
// ****
function StudentDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1)
		return;

	try
	{
		//Ausgewaehlte Lehreinheit holen
        var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
		var uid=tree.view.getCellText(tree.currentIndex,col);
		if(uid=='')
			return false
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Abfrage ob wirklich geloescht werden soll
	if (confirm('Wollen Sie diesen Studenten wirklich löschen?'))
	{
		//Script zum loeschen der Lehreinheit aufrufen
		var req = new phpRequest('student/studentDBDML.php','','');

		req.add('type','student');
		req.add('do','delete');
		req.add('uid',uid);
		var response = req.executePOST();

		var val =  new ParseReturnValue(response)
		if(!val.dbdml_return)
			alert(val.dbdml_errormsg)

		StudentTreeRefresh();
		StudentDetailReset();
	}
}

// ****
// * Leert alle Eingabe- und Auswahlfelder
// ****
function StudentDetailReset()
{
	
}

// ****
// * Deaktiviert alle Eingabe- und Auswahlfelder
// ****
function StudentDetailDisableFields(val)
{
	
}

// ****
// * Speichert die Details
// ****
function StudentDetailSave()
{
	//Werte holen
	//lvnr = document.getElementById('lehrveranstaltung-detail-textbox-lvnr').value;
	//...
	
	var req = new phpRequest('student/studentDBDML.php','','');
	neu = document.getElementById('student-detail-checkbox-new').checked;

	if (neu)
	{
		req.add('do','create');
	}
	else
	{
		req.add('do','update');
	}
	
	req.add('type', 'student');
	//...
	
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		alert(val.dbdml_errormsg)
	}
	else
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-detail-checkbox-new').checked=false;
		
		StudentSelectUid=val.dbdml_data;
		StudentTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Auswahl eines Studenten
// * bei Auswahl eines Studenten wird dieser geladen
// * und die Daten unten angezeigt
// ****
function StudentAuswahl()
{
/*
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('lehrveranstaltung-tree');

	//Felder bei Lektorenzuordnung deaktivieren
	LeMitarbeiterDisableFields(true);

	document.getElementById('lehrveranstaltung-detail-tree-lehreinheitgruppe').hidden=false;
	document.getElementById('lehrveranstaltung-detail-label-lehreinheitgruppe').hidden=false;
	document.getElementById('lehrveranstaltung-tab-lektor').collapsed=false;	

	if (tree.currentIndex==-1) return;
	try
	{
		//Ausgewaehlte Lehreinheit holen
        var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehreinheit_id"] : "lehrveranstaltung-treecol-lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(tree.currentIndex,col);
		if(lehreinheit_id=='')
		{
			//Lehrveranstaltung wurde markiert
			//Neu Button aktivieren
			document.getElementById('lehrveranstaltung-toolbar-neu').disabled=false;
			document.getElementById('lehrveranstaltung-toolbar-del').disabled=true;

			LeDetailDisableFields(true);
			//Details zuruecksetzen
			LeDetailReset();
			return false;
		}
		else
		{
			LeDetailDisableFields(false);
			document.getElementById('lehrveranstaltung-toolbar-neu').disabled=true;
			document.getElementById('lehrveranstaltung-toolbar-del').disabled=false;
		}

		var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehrveranstaltung_id"] : "lehrveranstaltung-treecol-lehrveranstaltung_id";
		var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);

		if(lehrveranstaltung_id=='')
			return false;
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	var req = new phpRequest('../rdf/lehreinheit.rdf.php','','');
	req.add('lehreinheit_id',lehreinheit_id);

	var response = req.execute();
	// Datasource holen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/lehreinheit/liste');

	dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/lehreinheit/" + lehreinheit_id);

	var predicateNS = "http://www.technikum-wien.at/lehreinheit/rdf";

	//Daten holen

	unr = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#unr" ));
	lvnr=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lvnr" ));
	sprache=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#sprache" ));
	lehrveranstaltung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehrveranstaltung_id" ));
	lehrfach=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehrfach_id" ));
	raumtyp=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#raumtyp" ));
	raumtyp_alt=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#raumtypalternativ" ));
	lehre=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehre" ));
	stundenblockung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#stundenblockung" ));
	wochenrythmus=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#wochenrythmus" ));
	start_kw=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#start_kw" ));
	anmerkung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	studiensemester=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiensemester_kurzbz" ));
	lehrform=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehrform_kurzbz" ));

	//Lehrfach drop down setzen

	//ID in globale Variable speichern
	LeDetailLehrfach_id=lehrfach;
	
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
	var url = '<?php echo APP_ROOT;?>rdf/lehrfach.rdf.php?lehrveranstaltung_id='+lehrveranstaltung+'&'+gettimestamp();

	//RDF holen
	var newDs  = rdfService.GetDataSource(url);
	lehrfachmenulist.database.AddDataSource(newDs);
	
	//SinkObserver hinzufuegen
	var sink = newDs.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	sink.addXMLSinkObserver(LeDetailLehrfachSinkObserver);
	
	//Daten den Feldern zuweisen

	document.getElementById('lehrveranstaltung-detail-textbox-unr').value=unr;
	document.getElementById('lehrveranstaltung-detail-textbox-lvnr').value=lvnr;
	document.getElementById('lehrveranstaltung-detail-textbox-lehrveranstaltung').value=lehrveranstaltung;
	if(lehre=='Ja')
		document.getElementById('lehrveranstaltung-detail-checkbox-lehre').checked=true;
	else
		document.getElementById('lehrveranstaltung-detail-checkbox-lehre').checked=false;
	document.getElementById('lehrveranstaltung-detail-textbox-stundenblockung').value=stundenblockung;
	document.getElementById('lehrveranstaltung-detail-textbox-wochenrythmus').value=wochenrythmus;
	document.getElementById('lehrveranstaltung-detail-textbox-startkw').value=start_kw;
	document.getElementById('lehrveranstaltung-detail-textbox-anmerkung').value=anmerkung;
	document.getElementById('lehrveranstaltung-detail-menulist-sprache').value=sprache;
	document.getElementById('lehrveranstaltung-detail-menulist-lehrfach').value=lehrfach;
	document.getElementById('lehrveranstaltung-detail-menulist-raumtyp').value=raumtyp;
	document.getElementById('lehrveranstaltung-detail-menulist-raumtypalternativ').value=raumtyp_alt;
	document.getElementById('lehrveranstaltung-detail-menulist-studiensemester').value=studiensemester;
	document.getElementById('lehrveranstaltung-detail-menulist-lehrform').value=lehrform;
	document.getElementById('lehrveranstaltung-detail-checkbox-new').checked=false;
	document.getElementById('lehrveranstaltung-detail-textbox-lehreinheit_id').value=lehreinheit_id;

	//Lehreinheitmitarbeiter tree setzen
	url='<?php echo APP_ROOT;?>rdf/lehreinheitmitarbeiter.rdf.php?lehreinheit_id='+lehreinheit_id+"&"+gettimestamp();
	try
	{
		lektortree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitmitarbeiter');

		//Alte DS entfernen
		var oldDatasources = lektortree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			lektortree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		lektortree.builder.rebuild();
		
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		LeDetailLektorDatasource = rdfService.GetDataSource(url);
		LeDetailLektorDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		LeDetailLektorDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		lektortree.database.AddDataSource(LeDetailLektorDatasource);
		lektortree.builder.addListener(LvLektorTreeListener);
	}
	catch(e)
	{
		debug(e);
	}

	//Lehreinheitgruppe tree setzen
	url='<?php echo APP_ROOT; ?>rdf/lehreinheitgruppe.rdf.php?lehreinheit_id='+lehreinheit_id+"&"+gettimestamp();

	try
	{
		gruppentree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitgruppe');

		//Alte DS entfernen
		var oldDatasources = gruppentree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			gruppentree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		gruppentree.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		LeDetailGruppeDatasource = rdfService.GetDataSource(url);
		LeDetailGruppeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		LeDetailGruppeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		gruppentree.database.AddDataSource(LeDetailGruppeDatasource);
	}
	catch(e)
	{
		debug(e);
	}
*/
}