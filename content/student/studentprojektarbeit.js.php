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
var StudentProjektarbeitSelectID=null; //Id der Projektarbeit die nach dem Rebuild markiert werden soll
var StudentProjektarbeitTreeDatasource=null; //Datasource des Projektarbeit Trees
var StudentProjektbetreuerTreeDatasource=null; //Datasource des Projektbetreuer Trees
var StudentProjektbetreuerSelectPersonID=null;
var StudentProjektbetreuerSelectProjektarbeitID=null;
// ********** Observer und Listener ************* //

// ****
// * Observer fuer Projektarbeit Tree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var StudentProjektarbeitTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-projektarbeit-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Projektarbeit wieder
// * markiert
// ****
var StudentProjektarbeitTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(StudentProjektarbeitTreeSelectID,10);
  }
};

// ****
// * Observer fuer Projektbetreuer Tree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var StudentProjektbetreuerTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-projektbetreuer-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Projektbetreuer wieder
// * markiert
// ****
var StudentProjektbetreuerTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(StudentProjektbetreuerTreeSelectID,10);
  }
};
// ****************** FUNKTIONEN ************************** //

// ****
// * Laedt die Daten fuer den Projektarbeiten Tree
// ****
function StudentProjektarbeitTreeLoad(uid)
{
	var tree = document.getElementById('student-projektarbeit-tree');
	var url='<?php echo APP_ROOT;?>rdf/projektarbeit.rdf.php?student_uid='+uid+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	tree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentProjektarbeitTreeDatasource = rdfService.GetDataSource(url);
	StudentProjektarbeitTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentProjektarbeitTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	tree.database.AddDataSource(StudentProjektarbeitTreeDatasource);
	StudentProjektarbeitTreeDatasource.addXMLSinkObserver(StudentProjektarbeitTreeSinkObserver);
	tree.builder.addListener(StudentProjektarbeitTreeListener);
	
	StudentProjektarbeitDisableFields(false);
}

// ****
// * De-/Aktiviert die ProjektarbeitFelder
// ****
function StudentProjektarbeitDisableFields(val)
{
	document.getElementById('student-projektarbeit-button-neu').disabled=val;
	document.getElementById('student-projektarbeit-button-loeschen').disabled=val;
	
	if(val)
		StudentProjektarbeitDetailDisableFields(val);
}

// ****
// * De-/Aktiviert die ProjektarbeitDetailFelder
// ****
function StudentProjektarbeitDetailDisableFields(val)
{
	document.getElementById('student-projektarbeit-button-speichern').disabled=val;
	document.getElementById('student-projektarbeit-menulist-projekttyp').disabled=val;
	document.getElementById('student-projektarbeit-menulist-lehrveranstaltung').disabled=val;
	document.getElementById('student-projektarbeit-menulist-lehreinheit').disabled=val;
	document.getElementById('student-projektarbeit-menulist-firma').disabled=val;
	document.getElementById('student-projektarbeit-menulist-note').disabled=val;
	document.getElementById('student-projektarbeit-textbox-titel').disabled=val;
	document.getElementById('student-projektarbeit-textbox-punkte').disabled=val;
	document.getElementById('student-projektarbeit-datum-beginn').disabled=val;
	document.getElementById('student-projektarbeit-datum-ende').disabled=val;
	document.getElementById('student-projektarbeit-textbox-faktor').disabled=val;
	document.getElementById('student-projektarbeit-checkbox-freigegeben').disabled=val;
	document.getElementById('student-projektarbeit-datum-gesperrtbis').disabled=val;
	document.getElementById('student-projektarbeit-textbox-stundensatz').disabled=val;
	document.getElementById('student-projektarbeit-textbox-gesamtstunden').disabled=val;
	document.getElementById('student-projektarbeit-textbox-themenbereich').disabled=val;
	document.getElementById('student-projektarbeit-textbox-anmerkung').disabled=val;
	
	if(val)
		StudentProjektarbeitResetFields();
}

// ****
// * Resetet die ProjektarbeitDetailFelder
// ****
function StudentProjektarbeitResetFields()
{
	document.getElementById('student-projektarbeit-textbox-titel').value='';
	document.getElementById('student-projektarbeit-textbox-punkte').value='0.0';
	document.getElementById('student-projektarbeit-datum-beginn').value='';
	document.getElementById('student-projektarbeit-datum-ende').value='';
	document.getElementById('student-projektarbeit-textbox-faktor').value='1.0';
	document.getElementById('student-projektarbeit-checkbox-freigegeben').checked=false;
	document.getElementById('student-projektarbeit-datum-gesperrtbis').value='';
	document.getElementById('student-projektarbeit-textbox-stundensatz').value='80.0';
	document.getElementById('student-projektarbeit-textbox-gesamtstunden').value='3.0';
	document.getElementById('student-projektarbeit-textbox-themenbereich').value='';
	document.getElementById('student-projektarbeit-textbox-anmerkung').value='';
}

// *****
// * Markiert einen Datensatz im Tree
// *****
function StudentProjektarbeitTreeSelectID()
{
	var tree=document.getElementById('student-projektarbeit-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentProjektarbeitSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["student-projektarbeit-tree-projektarbeit_id"] : "student-projektarbeit-tree-projektarbeit_id";
			var id=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(id == StudentProjektarbeitSelectID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentIOSelectID=null;
				return true;
			}
	   	}
	}
}

// ****
// * Laedt die Daten der Projektarbeit zum Bearbeiten
// ****
function StudentProjektarbeitAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-projektarbeit-tree');

	if (tree.currentIndex==-1) return;

	StudentProjektarbeitDetailDisableFields(false);
	
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-projektarbeit-tree-projektarbeit_id"] : "student-projektarbeit-treecol-projektarbeit_id";
	var projektarbeit_id=tree.view.getCellText(tree.currentIndex,col);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/projektarbeit.rdf.php?projektarbeit_id='+projektarbeit_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/projektarbeit/" + projektarbeit_id);

	var predicateNS = "http://www.technikum-wien.at/projektarbeit/rdf";

	//Daten holen

	projekttyp_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#projekttyp_kurzbz" ));
	titel = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titel" ));
	lehreinheit_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehreinheit_id" ));
	lehrveranstaltung_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehrveranstaltung_id" ));
	lehreinheit_stsem = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehreinheit_stsem" ));
	student_uid = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#student_uid" ));
	firma_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#firma_id" ));
	note = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#note" ));
	punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));
	beginn = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beginn" ));
	ende = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ende" ));
	faktor = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#faktor" ));
	freigegeben = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#freigegeben" ));
	gesperrtbis = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gesperrtbis" ));
	stundensatz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#stundensatz" ));
	themenbereich = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#themenbereich" ));
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	gesamtstunden = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gesamtstunden" ));
		
	//var verband_tree=document.getElementById('tree-verband');
	//var col = verband_tree.columns ? verband_tree.columns["stg_kz"] : "stg_kz";
	//var stg_kz=verband_tree.view.getCellText(verband_tree.currentIndex,col);
	var stg_kz = studiengang_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;
	
	//Lehrveranstaltung DropDown laden
	var LvDropDown = document.getElementById('student-projektarbeit-menulist-lehrveranstaltung');
	url='<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?stg_kz='+stg_kz+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = LvDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LvDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LvDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	LvDropDown.database.AddDataSource(datasource);
	
	LvDropDown.builder.rebuild();

	// Lehreinheit Drop Down laden
	var LeDropDown = document.getElementById('student-projektarbeit-menulist-lehreinheit');
	url='<?php echo APP_ROOT;?>rdf/lehreinheit.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+"&studiensemester_kurzbz="+lehreinheit_stsem+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = LeDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LeDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LeDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	LeDropDown.database.AddDataSource(datasource);
	
	LeDropDown.builder.rebuild();
	
	//Werte setzen
	document.getElementById('student-projektarbeit-textbox-projektarbeit_id').value=projektarbeit_id;
	document.getElementById('student-projektarbeit-menulist-projekttyp').value=projekttyp_kurzbz;
	document.getElementById('student-projektarbeit-menulist-lehrveranstaltung').value=lehrveranstaltung_id;
	document.getElementById('student-projektarbeit-menulist-lehreinheit').value=lehreinheit_id;
	document.getElementById('student-projektarbeit-textbox-titel').value=titel;
	document.getElementById('student-projektarbeit-menulist-firma').value=firma_id;
	document.getElementById('student-projektarbeit-menulist-note').value=note;
	document.getElementById('student-projektarbeit-textbox-punkte').value=punkte;
	document.getElementById('student-projektarbeit-datum-beginn').value=beginn;
	document.getElementById('student-projektarbeit-datum-ende').value=ende;
	document.getElementById('student-projektarbeit-textbox-faktor').value=faktor;
	if(freigegeben=='Ja')
		document.getElementById('student-projektarbeit-checkbox-freigegeben').checked=true;
	else
		document.getElementById('student-projektarbeit-checkbox-freigegeben').checked=false;
	document.getElementById('student-projektarbeit-datum-gesperrtbis').value=gesperrtbis;
	document.getElementById('student-projektarbeit-textbox-stundensatz').value=stundensatz;
	document.getElementById('student-projektarbeit-textbox-gesamtstunden').value=gesamtstunden;
	document.getElementById('student-projektarbeit-textbox-themenbereich').value=themenbereich;
	document.getElementById('student-projektarbeit-textbox-anmerkung').value=anmerkung;
	document.getElementById('student-projektarbeit-checkbox-neu').checked=false;
	
	// **** BETREUER **** //
	var tree = document.getElementById('student-projektbetreuer-tree');
	var url='<?php echo APP_ROOT;?>rdf/projektbetreuer.rdf.php?projektarbeit_id='+projektarbeit_id+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	tree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentProjektbetreuerTreeDatasource = rdfService.GetDataSource(url);
	StudentProjektbetreuerTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentProjektbetreuerTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	tree.database.AddDataSource(StudentProjektbetreuerTreeDatasource);
	StudentProjektbetreuerTreeDatasource.addXMLSinkObserver(StudentProjektbetreuerTreeSinkObserver);
	tree.builder.addListener(StudentProjektbetreuerTreeListener);
	StudentProjektbetreuerDisableFields(false);
		
}

// ****
// * Speichert die Projektarbeit Daten
// ****
function StudentProjektarbeitSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	projektarbeit_id = document.getElementById('student-projektarbeit-textbox-projektarbeit_id').value;
	projekttyp_kurzbz = document.getElementById('student-projektarbeit-menulist-projekttyp').value;
	lehrveranstaltung_id = document.getElementById('student-projektarbeit-menulist-lehrveranstaltung').value;
	lehreinheit_id = document.getElementById('student-projektarbeit-menulist-lehreinheit').value;
	titel = document.getElementById('student-projektarbeit-textbox-titel').value;
	firma_id = document.getElementById('student-projektarbeit-menulist-firma').value;
	note = document.getElementById('student-projektarbeit-menulist-note').value;
	punkte = document.getElementById('student-projektarbeit-textbox-punkte').value;
	beginn = document.getElementById('student-projektarbeit-datum-beginn').value;
	ende = document.getElementById('student-projektarbeit-datum-ende').value;
	faktor = document.getElementById('student-projektarbeit-textbox-faktor').value;
	freigegeben = document.getElementById('student-projektarbeit-checkbox-freigegeben').checked;
	gesperrtbis = document.getElementById('student-projektarbeit-datum-gesperrtbis').value;
	stundensatz = document.getElementById('student-projektarbeit-textbox-stundensatz').value;
	gesamtstunden = document.getElementById('student-projektarbeit-textbox-gesamtstunden').value;
	themenbereich = document.getElementById('student-projektarbeit-textbox-themenbereich').value;
	anmerkung = document.getElementById('student-projektarbeit-textbox-anmerkung').value;
	neu = document.getElementById('student-projektarbeit-checkbox-neu').checked;

	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1)
	{
		alert('Student muss ausgewaehlt sein');
		return;
	}
    var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
	var student_uid=tree.view.getCellText(tree.currentIndex,col);

	//Datum pruefen
	if(beginn!='' && !CheckDatum(beginn))
	{
		alert('Beginn ist ungueltig');
		return false;
	}

	if(ende!='' && !CheckDatum(ende))
	{
		alert('Ende ist ungueltig');
		return false;
	}
	
	if(gesperrtbis!='' && !CheckDatum(gesperrtbis))
	{
		alert('gesperrtbis ist ungueltig');
		return false;
	}

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'saveprojektarbeit');
	
	req.add('projektarbeit_id', projektarbeit_id);
	req.add('projekttyp_kurzbz', projekttyp_kurzbz );
	req.add('titel', titel);
	req.add('lehreinheit_id', lehreinheit_id);
	req.add('student_uid', student_uid);
	req.add('firma_id', firma_id);
	req.add('note', note);
	req.add('punkte', punkte);
	req.add('beginn', beginn);
	req.add('ende', ende);
	req.add('faktor', faktor);
	req.add('freigegeben', freigegeben);
	req.add('gesperrtbis', gesperrtbis);
	req.add('stundensatz', stundensatz);
	req.add('gesamtstunden', gesamtstunden);
	req.add('themenbereich', themenbereich);
	req.add('anmerkung', anmerkung);
	req.add('neu', neu);	

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
		StudentProjektarbeitSelectID=val.dbdml_data;
		StudentProjektarbeitTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
		StudentProjektarbeitDetailDisableFields(true);
	}
}

// ****
// * Aktiviert die Detailfelder zum Neu Anlegen einer Projektarbeit
// ****
function StudentProjektarbeitNeu()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	document.getElementById('student-projektarbeit-checkbox-neu').checked=true;
	document.getElementById('student-projektarbeit-textbox-projektarbeit_id').value='';
	StudentProjektarbeitResetFields();
	StudentProjektarbeitDetailDisableFields(false);
	//var verband_tree=document.getElementById('tree-verband');
	//var col = verband_tree.columns ? verband_tree.columns["stg_kz"] : "stg_kz";
	//var stg_kz=verband_tree.view.getCellText(verband_tree.currentIndex,col);
	var stg_kz = studiengang_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;
	
	//Lehrveranstaltung DropDown laden
	var LvDropDown = document.getElementById('student-projektarbeit-menulist-lehrveranstaltung');
	url='<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?stg_kz='+stg_kz+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = LvDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LvDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LvDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	LvDropDown.database.AddDataSource(datasource);
	
	LvDropDown.builder.rebuild();
}

// ****
// * Loescht den markierten Eintrag
// ****
function StudentProjektarbeitLoeschen()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-projektarbeit-tree');

	if (tree.currentIndex==-1) 
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return false;
	}

	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-projektarbeit-tree-projektarbeit_id"] : "student-projektarbeit-tree-projektarbeit_id";
	var projektarbeit_id=tree.view.getCellText(tree.currentIndex,col);
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	
	var req = new phpRequest(url,'','');

	req.add('type', 'deleteprojektarbeit');
		
	req.add('projektarbeit_id', projektarbeit_id);
	
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
		StudentProjektarbeitSelectID=null;
		StudentProjektarbeitTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden geloescht');
		StudentProjektarbeitDetailDisableFields(true);
	}
}

// ****
// * Wenn die Lehrveranstaltung geaendert wird, dann 
// * muss das LehreinheitenDropDown neu geladen werden
// ****
function StudentProjektarbeitLVAChange()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	lehrveranstaltung_id = document.getElementById('student-projektarbeit-menulist-lehrveranstaltung').value;
	studiensemester_kurzbz = getStudiensemester();
	
	// Lehreinheit Drop Down laden
	var LeDropDown = document.getElementById('student-projektarbeit-menulist-lehreinheit');
	url='<?php echo APP_ROOT;?>rdf/lehreinheit.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+"&studiensemester_kurzbz="+studiensemester_kurzbz+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = LeDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LeDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LeDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	LeDropDown.database.AddDataSource(datasource);
	
	LeDropDown.builder.rebuild();
}

// ******************* PROJEKTBETREUER ************************* //

// *****
// * Markiert eine Zeile im Projektbetreuer Tree
// *****
function StudentProjektbetreuerTreeSelectID()
{
	var tree=document.getElementById('student-projektbetreuer-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	
	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentProjektbetreuerSelectPersonID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["student-projektbetreuer-tree-projektarbeit_id"] : "student-projektbetreuer-tree-projektarbeit_id";
			var projektarbeit_id=tree.view.getCellText(i,col);
			col = tree.columns ? tree.columns["student-projektbetreuer-tree-person_id"] : "student-projektbetreuer-tree-person_id";
			var person_id=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(person_id == StudentProjektbetreuerSelectPersonID && projektarbeit_id == StudentProjektbetreuerSelectProjektarbeitID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentIOSelectID=null;
				return true;
			}
	   	}
	}
}

// *****
// * Bei Auswahl eines Betreuers, wird dieser zum Bearbeiten geladen
// *****
function StudentProjektbetreuerAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-projektbetreuer-tree');

	if (tree.currentIndex==-1) return;

	StudentProjektbetreuerDetailDisableFields(false);
	
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-projektbetreuer-tree-projektarbeit_id"] : "student-projektbetreuer-treecol-projektarbeit_id";
	var projektarbeit_id=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["student-projektbetreuer-tree-person_id"] : "student-projektbetreuer-treecol-person_id";
	var person_id=tree.view.getCellText(tree.currentIndex,col);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/projektbetreuer.rdf.php?projektarbeit_id='+projektarbeit_id+'&person_id='+person_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/projektbetreuer/" + person_id+'/'+projektarbeit_id);

	var predicateNS = "http://www.technikum-wien.at/projektbetreuer/rdf";

	//Daten holen

	note = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#note" ));
	faktor = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#faktor" ));
	name = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#name" ));
	punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));
	stunden = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#stunden" ));
	stundensatz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#stundensatz" ));
	betreuerart_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#betreuerart_kurzbz" ));
	person_nachname = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_nachname" ));
			
	StudentProjektbetreuerMenulistPersonLoad(document.getElementById('student-projektbetreuer-menulist-person'), person_nachname);
	
	//Werte setzen
	MenulistSelectItemOnValue('student-projektbetreuer-menulist-person', person_id);
	document.getElementById('student-projektbetreuer-menulist-note').value=note;
	document.getElementById('student-projektbetreuer-textbox-faktor').value=faktor;
	document.getElementById('student-projektbetreuer-textbox-name').value=name;
	document.getElementById('student-projektbetreuer-textbox-punkte').value=punkte;
	document.getElementById('student-projektbetreuer-textbox-stunden').value=stunden;
	document.getElementById('student-projektbetreuer-textbox-stundensatz').value=stundensatz;
	document.getElementById('student-projektbetreuer-menulist-betreuerart').value=betreuerart_kurzbz;
	document.getElementById('student-projektbetreuer-textbox-person_id').value=person_id;
	document.getElementById('student-projektbetreuer-checkbox-neu').checked=false;	
}

// *****
// * De-/Aktiviert die Buttons
// *****
function StudentProjektbetreuerDisableFields(val)
{
	document.getElementById('student-projektbetreuer-button-neu').disabled=val;
	document.getElementById('student-projektbetreuer-button-loeschen').disabled=val;
	
	if(val)
	{
		tree = document.getElementById('student-projektbetreuer-tree');
		var oldDatasources = tree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			tree.database.RemoveDataSource(oldDatasources.getNext());
		}
		tree.builder.rebuild();
		
		StudentProjektbetreuerDetailDisableFields(val);
	}
}

// ****
// * De-/Aktiviert die Detailfelder
// ****
function StudentProjektbetreuerDetailDisableFields(val)
{
	document.getElementById('student-projektbetreuer-menulist-person').disabled=val;
	document.getElementById('student-projektbetreuer-menulist-note').disabled=val;
	document.getElementById('student-projektbetreuer-menulist-betreuerart').disabled=val;
	document.getElementById('student-projektbetreuer-textbox-faktor').disabled=val;
	document.getElementById('student-projektbetreuer-textbox-name').disabled=val;
	document.getElementById('student-projektbetreuer-textbox-punkte').disabled=val;
	document.getElementById('student-projektbetreuer-textbox-stunden').disabled=val;
	document.getElementById('student-projektbetreuer-textbox-stundensatz').disabled=val;
	document.getElementById('student-projektbetreuer-button-speichern').disabled=val;
}

// ****
// * Resettet die Detailfelder
// ****
function StudentProjektbetreuerDetailReset()
{
	document.getElementById('student-projektbetreuer-textbox-faktor').value=document.getElementById('student-projektarbeit-textbox-faktor').value;
	document.getElementById('student-projektbetreuer-textbox-name').value='';
	document.getElementById('student-projektbetreuer-textbox-punkte').value='0.0';
	document.getElementById('student-projektbetreuer-textbox-stunden').value=document.getElementById('student-projektarbeit-textbox-gesamtstunden').value;
	document.getElementById('student-projektbetreuer-textbox-stundensatz').value=document.getElementById('student-projektarbeit-textbox-stundensatz').value;
}

// ****
// * Laedt dynamisch die Personen fuer das DropDown Menue
// * Es muessen mindestens 3 Zeichen in das DropDown Menue eingegeben werden
// ****
function StudentProjektbetreuerMenulistPersonLoad(menulist, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	if(typeof(filter)=='undefined')
		v = menulist.value;
	else
		v = filter;

	if(v.length>2)
	{		
		var url = '<?php echo APP_ROOT; ?>rdf/person.rdf.php?filter='+v+'&'+gettimestamp();

		var oldDatasources = menulist.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			menulist.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		menulist.builder.rebuild();
	
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		if(typeof(filter)=='undefined')
			var datasource = rdfService.GetDataSource(url);
		else
			var datasource = rdfService.GetDataSourceBlocking(url);
		datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		menulist.database.AddDataSource(datasource);
		if(typeof(filter)!='undefined')
			menulist.builder.rebuild();
	}
}

// ****
// * Speichert die Projektbetreuer Daten
// ****
function StudentProjektbetreuerSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	person_id = MenulistGetSelectedValue('student-projektbetreuer-menulist-person');
	note = document.getElementById('student-projektbetreuer-menulist-note').value;
	faktor = document.getElementById('student-projektbetreuer-textbox-faktor').value;
	name = document.getElementById('student-projektbetreuer-textbox-name').value;
	punkte = document.getElementById('student-projektbetreuer-textbox-punkte').value;
	stunden = document.getElementById('student-projektbetreuer-textbox-stunden').value;
	stundensatz = document.getElementById('student-projektbetreuer-textbox-stundensatz').value;
	betreuerart_kurzbz = document.getElementById('student-projektbetreuer-menulist-betreuerart').value;
	person_id_old = document.getElementById('student-projektbetreuer-textbox-person_id').value;
	neu = document.getElementById('student-projektbetreuer-checkbox-neu').checked;
	
	var tree = document.getElementById('student-projektarbeit-tree');

	if (tree.currentIndex==-1)
	{
		alert('Projektarbeit muss ausgewaehlt sein');
		return;
	}
    var col = tree.columns ? tree.columns["student-projektarbeit-tree-projektarbeit_id"] : "student-projektarbeit-tree-projektarbeit_id";
	var projektarbeit_id=tree.view.getCellText(tree.currentIndex,col);
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'saveprojektbetreuer');
	
	req.add('person_id', person_id);
	req.add('note', note);
	req.add('faktor', faktor);
	req.add('name', name);
	req.add('punkte', punkte);
	req.add('stunden', stunden);
	req.add('stundensatz', stundensatz);
	req.add('betreuerart_kurzbz', betreuerart_kurzbz);
	req.add('projektarbeit_id', projektarbeit_id);
	req.add('person_id_old', person_id_old);
	req.add('neu', neu);

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
		StudentProjektbetreuerSelectPersonID=person_id;
		StudentProjektbetreuerSelectProjektarbeitID=projektarbeit_id;
		StudentProjektbetreuerTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
		StudentProjektbetreuerDetailDisableFields(true);
	}
}

// ****
// * Deaktiviert die Detailfelder um einen Neuen Datensatz zu erstellen
// ****
function StudentProjektbetreuerNeu()
{
	StudentProjektbetreuerDetailReset();
	document.getElementById('student-projektbetreuer-checkbox-neu').checked=true;
	StudentProjektbetreuerDetailDisableFields(false);
}

// ****
// * Loescht einen Projektbetreuer
// ****
function StudentProjektbetreuerLoeschen()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		
	var tree = document.getElementById('student-projektbetreuer-tree');

	if (tree.currentIndex==-1)
	{
		alert('Projektbetreuer muss ausgewaehlt sein');
		return;
	}
    var col = tree.columns ? tree.columns["student-projektbetreuer-tree-projektarbeit_id"] : "student-projektbetreuer-tree-projektarbeit_id";
	var projektarbeit_id=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["student-projektbetreuer-tree-person_id"] : "student-projektbetreuer-tree-person_id";
	var person_id=tree.view.getCellText(tree.currentIndex,col);
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'deleteprojektbetreuer');
	
	req.add('person_id', person_id);
	req.add('projektarbeit_id', projektarbeit_id);

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
		StudentProjektbetreuerSelectPersonID=null;
		StudentProjektbetreuerSelectProjektarbeitID=null;
		StudentProjektbetreuerTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden geloescht');
		StudentProjektbetreuerDetailDisableFields(true);
	}
}