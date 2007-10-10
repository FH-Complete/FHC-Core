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
var StudentAbschlusspruefungSelectID=null; //Id der Abschlusspruefung die nach dem Rebuild markiert werden soll
var StudentAbschlusspruefungTreeDatasource=null; //Datasource des Abschlusspruefung Trees

// ********** Observer und Listener ************* //

// ****
// * Observer fuer Abschlusspruefung Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentAbschlusspruefungTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-abschlusspruefung-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Abschlusspruefung wieder
// * markiert
// ****
var StudentAbschlusspruefungTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(StudentAbschlusspruefungTreeSelectID,10);
  }
};

// ****************** FUNKTIONEN ************************** //

// ****
// * Laedt die Daten fuer den Abschlusspruefungen Tree
// ****
function StudentAbschlusspruefungTreeLoad(uid)
{
	tree = document.getElementById('student-abschlusspruefung-tree');
	url='<?php echo APP_ROOT;?>rdf/abschlusspruefung.rdf.php?student_uid='+uid+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	tree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentAbschlusspruefungTreeDatasource = rdfService.GetDataSource(url);
	StudentAbschlusspruefungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentAbschlusspruefungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	tree.database.AddDataSource(StudentAbschlusspruefungTreeDatasource);
	StudentAbschlusspruefungTreeDatasource.addXMLSinkObserver(StudentAbschlusspruefungTreeSinkObserver);
	tree.builder.addListener(StudentAbschlusspruefungTreeListener);
	
	StudentAbschlusspruefungDisableFields(false);
}

// ****
// * De-/Aktiviert die AbschlusspruefungFelder
// ****
function StudentAbschlusspruefungDisableFields(val)
{
	document.getElementById('student-abschlusspruefung-button-neu').disabled=val;
	document.getElementById('student-abschlusspruefung-button-loeschen').disabled=val;
	
	if(val)
		StudentAbschlusspruefungDetailDisableFields(val);
}

// ****
// * De-/Aktiviert die AbschlusspruefungDetailFelder
// ****
function StudentAbschlusspruefungDetailDisableFields(val)
{
	document.getElementById('student-abschlusspruefung-menulist-vorsitz').disabled=val;
	document.getElementById('student-abschlusspruefung-menulist-pruefer1').disabled=val;
	document.getElementById('student-abschlusspruefung-menulist-pruefer2').disabled=val;
	document.getElementById('student-abschlusspruefung-menulist-pruefer3').disabled=val;
	document.getElementById('student-abschlusspruefung-menulist-abschlussbeurteilung').disabled=val;
	document.getElementById('student-abschlusspruefung-menulist-akadgrad').disabled=val;
	document.getElementById('student-abschlusspruefung-menulist-typ').disabled=val;
	document.getElementById('student-abschlusspruefung-datum-datum').disabled=val;
	document.getElementById('student-abschlusspruefung-datum-sponsion').disabled=val;
	document.getElementById('student-abschlusspruefung-textbox-anmerkung').disabled=val;
	document.getElementById('student-abschlusspruefung-button-speichern').disabled=val;
	
	if(val)
		StudentAbschlusspruefungResetFields();
}

// ****
// * Resetet die AbschlusspruefungDetailFelder
// ****
function StudentAbschlusspruefungResetFields()
{
	document.getElementById('student-abschlusspruefung-menulist-vorsitz').value='';
	document.getElementById('student-abschlusspruefung-menulist-pruefer1').value='';
	document.getElementById('student-abschlusspruefung-menulist-pruefer2').value='';
	document.getElementById('student-abschlusspruefung-menulist-pruefer3').value='';
	document.getElementById('student-abschlusspruefung-menulist-abschlussbeurteilung').value='';
	//document.getElementById('student-abschlusspruefung-menulist-akadgrad').value='';
	//document.getElementById('student-abschlusspruefung-menulist-typ').value='Bachelor';
	document.getElementById('student-abschlusspruefung-datum-datum').value='';
	document.getElementById('student-abschlusspruefung-datum-sponsion').value='';
	document.getElementById('student-abschlusspruefung-textbox-anmerkung').value='';
}

// ****
// * Laedt dynamisch die Personen fuer das DropDown Menue
// * Es muessen mindestens 3 Zeichen in das DropDown Menue eingegeben werden
// ****
function StudentAbschlusspruefungMenulistPersonLoad(menulist, filter)
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
// * Laedt dynamisch die Mitarbeiter fuer das DropDown Menue
// * Es muessen mindestens 3 Zeichen in das DropDown Menue eingegeben werden
// ****
function StudentAbschlusspruefungMenulistMitarbeiterLoad(menulist, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	if(typeof(filter)=='undefined')
		v = menulist.value;
	else
		v = filter;
		
	if(v.length>2)
	{
		if(filter=='')
			filter1 = menulist.value;
		else
			filter1 = filter;
		var url = '<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php?filter='+v+'&'+gettimestamp();
		
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

// *****
// * Markiert einen Datensatz im Tree
// *****
function StudentAbschlusspruefungTreeSelectID()
{
	var tree=document.getElementById('student-abschlusspruefung-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentAbschlusspruefungSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["student-abschlusspruefung-treecol-abschlusspruefung_id"] : "student-abschlusspruefung-treecol-abschlusspruefung_id";
			var id=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(id == StudentAbschlusspruefungSelectID)
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
// * Laedt die Daten der Abschlusspruefung zum Bearbeiten
// ****
function StudentAbschlusspruefungAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	if (tree.currentIndex==-1) return;

	StudentAbschlusspruefungDetailDisableFields(false);
	
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-abschlusspruefung-treecol-abschlusspruefung_id"] : "student-abschlusspruefung-treecol-abschlusspruefung_id";
	var abschlusspruefung_id=tree.view.getCellText(tree.currentIndex,col);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/abschlusspruefung.rdf.php?abschlusspruefung_id='+abschlusspruefung_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/abschlusspruefung/" + abschlusspruefung_id);

	var predicateNS = "http://www.technikum-wien.at/abschlusspruefung/rdf";

	//Daten holen

	student_uid = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#student_uid" ));
	vorsitz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vorsitz" ));
	vorsitz_nachname = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vorsitz_nachname" ));
	pruefer1 = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#pruefer1" ));
	pruefer1_nachname = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#pruefer1_nachname" ));
	pruefer2 = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#pruefer2" ));
	pruefer2_nachname = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#pruefer2_nachname" ));
	pruefer3 = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#pruefer3" ));
	pruefer3_nachname = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#pruefer3_nachname" ));
	abschlussbeurteilung_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#abschlussbeurteilung_kurzbz" ));
	akadgrad_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#akadgrad_id" ));
	datum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#datum" ));
	sponsion = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#sponsion" ));
	pruefungstyp_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#pruefungstyp_kurzbz" ));
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	
	//var verband_tree=document.getElementById('tree-verband');
	//var col = verband_tree.columns ? verband_tree.columns["stg_kz"] : "stg_kz";
	//var stg_kz=verband_tree.view.getCellText(verband_tree.currentIndex,col);
	stg_kz = studiengang_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;
	
	//Akadgrad DropDown laden
	var AkadgradDropDown = document.getElementById('student-abschlusspruefung-menulist-akadgrad');
	url='<?php echo APP_ROOT;?>rdf/akadgrad.rdf.php?studiengang_kz='+stg_kz+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = AkadgradDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		AkadgradDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	AkadgradDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	AkadgradDropDown.database.AddDataSource(datasource);
	
	AkadgradDropDown.builder.rebuild();

	// Vorsitz Drop Down laden
	StudentAbschlusspruefungMenulistMitarbeiterLoad(document.getElementById('student-abschlusspruefung-menulist-vorsitz'), vorsitz_nachname);
	
	// Pruefer Drop Down laden
	StudentAbschlusspruefungMenulistPersonLoad(document.getElementById('student-abschlusspruefung-menulist-pruefer1'), pruefer1_nachname);
	StudentAbschlusspruefungMenulistPersonLoad(document.getElementById('student-abschlusspruefung-menulist-pruefer2'), pruefer2_nachname);
	StudentAbschlusspruefungMenulistPersonLoad(document.getElementById('student-abschlusspruefung-menulist-pruefer3'), pruefer3_nachname);
			
	MenulistSelectItemOnValue('student-abschlusspruefung-menulist-vorsitz', vorsitz)
	MenulistSelectItemOnValue('student-abschlusspruefung-menulist-pruefer1', pruefer1);
	MenulistSelectItemOnValue('student-abschlusspruefung-menulist-pruefer2', pruefer2);
	MenulistSelectItemOnValue('student-abschlusspruefung-menulist-pruefer3', pruefer3);
	document.getElementById('student-abschlusspruefung-menulist-abschlussbeurteilung').value=abschlussbeurteilung_kurzbz;
	document.getElementById('student-abschlusspruefung-menulist-akadgrad').value=akadgrad_id;
	document.getElementById('student-abschlusspruefung-datum-datum').value=datum;
	document.getElementById('student-abschlusspruefung-datum-sponsion').value=sponsion;
	document.getElementById('student-abschlusspruefung-menulist-typ').value=pruefungstyp_kurzbz;
	document.getElementById('student-abschlusspruefung-textbox-anmerkung').value=anmerkung;
	document.getElementById('student-abschlusspruefung-textbox-abschlusspruefung_id').value=abschlusspruefung_id;
	document.getElementById('student-abschlusspruefung-checkbox-neu').checked=false;
	
	StudentAbschlusspruefungTypChange();
}

// ****
// * Speichert die Abschlusspruefung Daten
// ****
function StudentAbschlusspruefungSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//der Value einer Editable Menulist muss mittels dieser Funktion geholt werden
	//da sonst der Text der Menulist geliefert wird und nicht der dahinterliegene Wert
	vorsitz = MenulistGetSelectedValue('student-abschlusspruefung-menulist-vorsitz');	
	pruefer1 = MenulistGetSelectedValue('student-abschlusspruefung-menulist-pruefer1');
	pruefer2 = MenulistGetSelectedValue('student-abschlusspruefung-menulist-pruefer2');
	pruefer3 = MenulistGetSelectedValue('student-abschlusspruefung-menulist-pruefer3');
	abschlussbeurteilung_kurzbz = document.getElementById('student-abschlusspruefung-menulist-abschlussbeurteilung').value;
	akadgrad_id = document.getElementById('student-abschlusspruefung-menulist-akadgrad').value;
	datum = document.getElementById('student-abschlusspruefung-datum-datum').value;
	sponsion = document.getElementById('student-abschlusspruefung-datum-sponsion').value;
	pruefungstyp_kurzbz = document.getElementById('student-abschlusspruefung-menulist-typ').value;
	anmerkung = document.getElementById('student-abschlusspruefung-textbox-anmerkung').value;
	abschlusspruefung_id = document.getElementById('student-abschlusspruefung-textbox-abschlusspruefung_id').value;
	neu = document.getElementById('student-abschlusspruefung-checkbox-neu').checked;

	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1)
	{
		alert('Student muss ausgewaehlt sein');
		return;
	}
	
	if(pruefungstyp_kurzbz=='')
	{
		alert('Bitte den Pruefungstyp auswaehlen');
		return false;
	}
	
    var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
	var student_uid=tree.view.getCellText(tree.currentIndex,col);

	//Datum pruefen
	if(datum!='' && !CheckDatum(datum))
	{
		alert('Datum ist ungueltig');
		return false;
	}

	if(sponsion!='' && !CheckDatum(sponsion))
	{
		alert('Sponsionsdatum ist ungueltig');
		return false;
	}

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'saveabschlusspruefung');
	
	req.add('student_uid', student_uid);
	req.add('vorsitz', vorsitz);
	req.add('pruefer1', pruefer1);
	req.add('pruefer2', pruefer2);
	req.add('pruefer3', pruefer3);
	req.add('abschlussbeurteilung_kurzbz', abschlussbeurteilung_kurzbz);
	req.add('akadgrad_id', akadgrad_id);
	req.add('datum', datum);
	req.add('sponsion', sponsion);
	req.add('pruefungstyp_kurzbz', pruefungstyp_kurzbz);
	req.add('anmerkung', anmerkung);
	req.add('abschlusspruefung_id', abschlusspruefung_id);
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
		StudentAbschlusspruefungSelectID=val.dbdml_data;
		StudentAbschlusspruefungTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
		StudentAbschlusspruefungDetailDisableFields(true);
	}
}

// ****
// * Aktiviert die Detailfelder zum Neuanlegen einer Abschlusspruefung
// ****
function StudentAbschlusspruefungNeu()
{

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	document.getElementById('student-abschlusspruefung-checkbox-neu').checked=true;
	document.getElementById('student-abschlusspruefung-textbox-abschlusspruefung_id').value='';
	StudentAbschlusspruefungResetFields();
	StudentAbschlusspruefungDetailDisableFields(false);
	
	//var verband_tree=document.getElementById('tree-verband');
	//var col = verband_tree.columns ? verband_tree.columns["stg_kz"] : "stg_kz";
	//var stg_kz=verband_tree.view.getCellText(verband_tree.currentIndex,col);
	
	var stg_kz = studiengang_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;
	
	//Akadgrad DropDown laden
	var AkadgradDropDown = document.getElementById('student-abschlusspruefung-menulist-akadgrad');
	url='<?php echo APP_ROOT;?>rdf/akadgrad.rdf.php?studiengang_kz='+stg_kz+"&"+gettimestamp();
	//Alte DS entfernen
	var oldDatasources = AkadgradDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		AkadgradDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	AkadgradDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	AkadgradDropDown.database.AddDataSource(datasource);
	
	AkadgradDropDown.builder.rebuild();
}

// ****
// * Loescht den markierten Eintrag
// ****
function StudentAbschlusspruefungLoeschen()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	if (tree.currentIndex==-1) 
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return false;
	}

	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-abschlusspruefung-treecol-abschlusspruefung_id"] : "student-abschlusspruefung-treecol-abschlusspruefung_id";
	var abschlusspruefung_id=tree.view.getCellText(tree.currentIndex,col);
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	
	var req = new phpRequest(url,'','');

	req.add('type', 'deleteabschlusspruefung');
		
	req.add('abschlusspruefung_id', abschlusspruefung_id);
	
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
		StudentAbschlusspruefungSelectID=null;
		StudentAbschlusspruefungTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden geloescht');
		StudentAbschlusspruefungDetailDisableFields(true);
	}
}

// ***** AUSDRUCKE ***** //

function StudentAbschlusspruefungPrintPruefungsprotokoll()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	if (tree.currentIndex==-1) 
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return false;
	}

	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-abschlusspruefung-treecol-abschlusspruefung_id"] : "student-abschlusspruefung-treecol-abschlusspruefung_id";
	var abschlusspruefung_id=tree.view.getCellText(tree.currentIndex,col);
	
	var col = tree.columns ? tree.columns["student-abschlusspruefung-treecol-pruefungstyp_kurzbz"] : "student-abschlusspruefung-treecol-pruefungstyp_kurzbz";
	var pruefungstyp_kurzbz=tree.view.getCellText(tree.currentIndex,col);
	
	if(pruefungstyp_kurzbz=='Bachelor')
		xsl='PrProtokollBakk';
	else
		xsl='PrProtokollDipl';
			
	window.open('<?php echo APP_ROOT; ?>/content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl='+xsl+'&abschlusspruefung_id='+abschlusspruefung_id,'Pruefungsprotokoll', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

function StudentAbschlusspruefungPrintPruefungszeugnis()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	if (tree.currentIndex==-1) 
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return false;
	}

	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-abschlusspruefung-treecol-abschlusspruefung_id"] : "student-abschlusspruefung-treecol-abschlusspruefung_id";
	var abschlusspruefung_id=tree.view.getCellText(tree.currentIndex,col);
	
	var col = tree.columns ? tree.columns["student-abschlusspruefung-treecol-pruefungstyp_kurzbz"] : "student-abschlusspruefung-treecol-pruefungstyp_kurzbz";
	var pruefungstyp_kurzbz=tree.view.getCellText(tree.currentIndex,col);
	
	if(pruefungstyp_kurzbz=='Bachelor')
		xsl='Bakkzeugnis';
	else
		xsl='Diplomzeugnis';
			
	window.open('<?php echo APP_ROOT; ?>/content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl='+xsl+'&abschlusspruefung_id='+abschlusspruefung_id,'PruefungsZeugnis', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

function StudentAbschlusspruefungPrintUrkunde(sprache)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	if (tree.currentIndex==-1) 
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return false;
	}

	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-abschlusspruefung-treecol-abschlusspruefung_id"] : "student-abschlusspruefung-treecol-abschlusspruefung_id";
	var abschlusspruefung_id=tree.view.getCellText(tree.currentIndex,col);
	
	var col = tree.columns ? tree.columns["student-abschlusspruefung-treecol-pruefungstyp_kurzbz"] : "student-abschlusspruefung-treecol-pruefungstyp_kurzbz";
	var pruefungstyp_kurzbz=tree.view.getCellText(tree.currentIndex,col);
	
	if(pruefungstyp_kurzbz=='Bachelor' && sprache=='deutsch')
		xsl='Bakkurkunde';
	else if(pruefungstyp_kurzbz=='Bachelor' && sprache=='englisch')
		xsl='BakkurkundeEng';
	else if(pruefungstyp_kurzbz=='Diplom' && sprache=='deutsch')
		xsl='Diplomurkunde';
	else if(pruefungstyp_kurzbz=='Diplom' && sprache=='englisch')
		xsl='DiplomurkundeEng';
			
	window.open('<?php echo APP_ROOT; ?>/content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl='+xsl+'&abschlusspruefung_id='+abschlusspruefung_id,'Pruefungsprotokoll', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

function StudentAbschlusspruefungTypChange()
{
	if(document.getElementById('student-abschlusspruefung-menulist-typ').value=='Bachelor')
	{
		document.getElementById('student-abschlusspruefung-label-pruefer1').value='Pruefer 1';
		document.getElementById('student-abschlusspruefung-label-pruefer2').value='Pruefer 2';
		document.getElementById('student-abschlusspruefung-menulist-pruefer3').hidden=false;
		document.getElementById('student-abschlusspruefung-label-pruefer3').hidden=false;
	}
	else
	{
		document.getElementById('student-abschlusspruefung-label-pruefer1').value='Pruefer 1 (nicht technisch)';
		document.getElementById('student-abschlusspruefung-label-pruefer2').value='Pruefer 2 (technisch)';
		document.getElementById('student-abschlusspruefung-menulist-pruefer3').hidden=true;
		document.getElementById('student-abschlusspruefung-label-pruefer3').hidden=true;
	}
}