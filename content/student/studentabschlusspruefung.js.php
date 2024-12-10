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

require_once('../../config/vilesci.config.inc.php');
if(false):?> <script><?php endif;

?>
// *********** Globale Variablen *****************//
var StudentAbschlusspruefungSelectID=null; //Id der Abschlusspruefung die nach dem Rebuild markiert werden soll
var StudentAbschlusspruefungTreeDatasource=null; //Datasource des Abschlusspruefung Trees
var StudentAbschlusspruefungAkadgradDDDatasource=null; //Datasource des Akadgrad DropDowns
// ********** Observer und Listener ************* //

// ****
// * Observer fuer Abschlusspruefung Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentAbschlusspruefungTreeSinkObserver =
{
	onBeginLoad : function(pSink)
	{
		//Eventlistener waehrend des Ladevorganges deaktivieren da es sonst
		//zu Problemen kommt
		tree = document.getElementById('student-abschlusspruefung-tree');
		tree.removeEventListener('select', StudentAbschlusspruefungAuswahl, false);
	},
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
  		tree = document.getElementById('student-abschlusspruefung-tree');
		tree.addEventListener('select', StudentAbschlusspruefungAuswahl, false);
		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		window.setTimeout(StudentAbschlusspruefungTreeSelectID,10);
	}
};

// ****
// * Observer fuer Akadgrad DropDown
// ****
var StudentAbschlusspruefungAkadgradDDSinkObserver =
{
	onBeginLoad : function(pSink)
	{},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-abschlusspruefung-menulist-akadgrad').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Akadgrad markiert
// ****
var StudentAbschlusspruefungAkadgradDDListener =
{
	willRebuild : function(builder) {  },
	didRebuild : function(builder)
	{
  		dd = document.getElementById('student-abschlusspruefung-menulist-akadgrad');
		//ersten Eintrag im DD markieren
		dd.selectedIndex=0;
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

	//Alte Observer entfernen
	try
	{
		StudentAbschlusspruefungTreeDatasource.removeXMLSinkObserver(StudentAbschlusspruefungTreeSinkObserver);
		tree.builder.removeListener(StudentAbschlusspruefungTreeListener);
	}
	catch(e)
	{}

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
	document.getElementById('student-abschlusspruefung-menulist-pruefungsantritt').disabled=val;
	document.getElementById('student-abschlusspruefung-menulist-notekommpruef').disabled=val;
	document.getElementById('student-abschlusspruefung-menulist-akadgrad').disabled=val;
	document.getElementById('student-abschlusspruefung-menulist-typ').disabled=val;
	document.getElementById('student-abschlusspruefung-datum-datum').disabled=val;
	document.getElementById('student-abschlusspruefung-datum-uhrzeit').disabled=val;
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
	document.getElementById('student-abschlusspruefung-menulist-pruefungsantritt').value='';
	document.getElementById('student-abschlusspruefung-menulist-notekommpruef').value='';
	//document.getElementById('student-abschlusspruefung-menulist-akadgrad').value='';
	//document.getElementById('student-abschlusspruefung-menulist-typ').value='Bachelor';
	document.getElementById('student-abschlusspruefung-datum-datum').value='';
	document.getElementById('student-abschlusspruefung-datum-uhrzeit').value='00:00';
	document.getElementById('student-abschlusspruefung-datum-sponsion').value='';
	document.getElementById('student-abschlusspruefung-textbox-anmerkung').value='';
	document.getElementById('student-abschlusspruefung-link-value').value='';
	document.getElementById('student-abschlusspruefung-link').value='';
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
		var url = '<?php echo APP_ROOT; ?>rdf/person.rdf.php?filter='+encodeURIComponent(v)+'&'+gettimestamp();

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
		var url = '<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php?filter='+encodeURIComponent(v)+'&'+gettimestamp();
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
	   		var id = getTreeCellText(tree, "student-abschlusspruefung-treecol-abschlusspruefung_id", i);

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
	var abschlusspruefung_id = getTreeCellText(tree, "student-abschlusspruefung-treecol-abschlusspruefung_id", tree.currentIndex)

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
	pruefungsantritt_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#pruefungsantritt_kurzbz" ));
	notekommpruef = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#notekommpruef" ));
	akadgrad_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#akadgrad_id" ));
	datum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#datum" ));
	uhrzeit = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#uhrzeit" ));
	sponsion = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#sponsion" ));
	pruefungstyp_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#pruefungstyp_kurzbz" ));
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	protokoll = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#protokoll" ));
	link_abschlusspruefung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#link_abschlusspruefung" ));

	stg_kz = studiengang_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;

	//Akadgrad DropDown laden
	var AkadgradDropDown = document.getElementById('student-abschlusspruefung-menulist-akadgrad');
	url='<?php echo APP_ROOT;?>rdf/akadgrad.rdf.php?studiengang_kz='+stg_kz+"&"+gettimestamp();

	//Alte Observer entfernen
	try
	{
		StudentAbschlusspruefungAkadgradDDDatasource.removeXMLSinkObserver(StudentAbschlusspruefungAkadgradDDSinkObserver);
		AkadgradDropDown.builder.removeListener(StudentAbschlusspruefungAkadgradDDListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = AkadgradDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		AkadgradDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	AkadgradDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentAbschlusspruefungAkadgradDDDatasource = rdfService.GetDataSourceBlocking(url);
	StudentAbschlusspruefungAkadgradDDDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentAbschlusspruefungAkadgradDDDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	AkadgradDropDown.database.AddDataSource(StudentAbschlusspruefungAkadgradDDDatasource);

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
	document.getElementById('student-abschlusspruefung-menulist-pruefungsantritt').value=pruefungsantritt_kurzbz;
	document.getElementById('student-abschlusspruefung-menulist-notekommpruef').value=notekommpruef;
	document.getElementById('student-abschlusspruefung-menulist-akadgrad').value=akadgrad_id;
	document.getElementById('student-abschlusspruefung-datum-datum').value=datum;
	if(uhrzeit=='')
		uhrzeit='00:00';
	document.getElementById('student-abschlusspruefung-datum-uhrzeit').value=uhrzeit;
	document.getElementById('student-abschlusspruefung-datum-sponsion').value=sponsion;
	document.getElementById('student-abschlusspruefung-menulist-typ').value=pruefungstyp_kurzbz;
	document.getElementById('student-abschlusspruefung-textbox-anmerkung').value=anmerkung;
	document.getElementById('student-abschlusspruefung-textbox-abschlusspruefung_id').value=abschlusspruefung_id;
	document.getElementById('student-abschlusspruefung-checkbox-neu').checked=false;
	document.getElementById('student-abschlusspruefung-textbox-protokoll').value=protokoll;
	document.getElementById('student-abschlusspruefung-link-value').value=link_abschlusspruefung;
	document.getElementById('student-abschlusspruefung-link').value='Prüfungsprotokoll';

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
	var vorsitz = MenulistGetSelectedValue('student-abschlusspruefung-menulist-vorsitz');
	var pruefer1 = MenulistGetSelectedValue('student-abschlusspruefung-menulist-pruefer1');
	var pruefer2 = MenulistGetSelectedValue('student-abschlusspruefung-menulist-pruefer2');
	var pruefer3 = MenulistGetSelectedValue('student-abschlusspruefung-menulist-pruefer3');
	var abschlussbeurteilung_kurzbz = document.getElementById('student-abschlusspruefung-menulist-abschlussbeurteilung').value;
	var pruefungsantritt_kurzbz = document.getElementById('student-abschlusspruefung-menulist-pruefungsantritt').value;
	var notekommpruef = document.getElementById('student-abschlusspruefung-menulist-notekommpruef').value;
	var akadgrad_id = document.getElementById('student-abschlusspruefung-menulist-akadgrad').value;
	var datum = document.getElementById('student-abschlusspruefung-datum-datum').value;
	var uhrzeit = document.getElementById('student-abschlusspruefung-datum-uhrzeit').value;
	var sponsion = document.getElementById('student-abschlusspruefung-datum-sponsion').value;
	var pruefungstyp_kurzbz = document.getElementById('student-abschlusspruefung-menulist-typ').value;
	var anmerkung = document.getElementById('student-abschlusspruefung-textbox-anmerkung').value;
	var abschlusspruefung_id = document.getElementById('student-abschlusspruefung-textbox-abschlusspruefung_id').value;
	var neu = document.getElementById('student-abschlusspruefung-checkbox-neu').checked;

	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;

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

	if(akadgrad_id=='')
	{
		alert('Akademischer Grad muss angegeben werden');
		return false;
	}

	var student_uid = getTreeCellText(tree, "student-treecol-uid", tree.currentIndex);

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
	req.add('pruefungsantritt_kurzbz', pruefungsantritt_kurzbz);
	req.add('notekommpruef', notekommpruef);
	req.add('akadgrad_id', akadgrad_id);
	req.add('datum', ConvertDateToISO(datum));
	req.add('uhrzeit', uhrzeit);
	req.add('sponsion', ConvertDateToISO(sponsion));
	req.add('pruefungstyp_kurzbz', pruefungstyp_kurzbz);
	req.add('anmerkung', anmerkung);
	req.add('abschlusspruefung_id', abschlusspruefung_id);
	req.add('neu', neu);
	req.add('studiengang_kz', studiengang_kz);

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

	var stg_kz = studiengang_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;

	//Akadgrad DropDown laden
	var AkadgradDropDown = document.getElementById('student-abschlusspruefung-menulist-akadgrad');
	url='<?php echo APP_ROOT;?>rdf/akadgrad.rdf.php?studiengang_kz='+stg_kz+"&"+gettimestamp();

	//Alte Observer entfernen
	try
	{
		StudentAbschlusspruefungAkadgradDDDatasource.removeXMLSinkObserver(StudentAbschlusspruefungAkadgradDDSinkObserver);
		AkadgradDropDown.builder.removeListener(StudentAbschlusspruefungAkadgradDDListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = AkadgradDropDown.database.GetDataSources();

	while(oldDatasources.hasMoreElements())
	{
		AkadgradDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	AkadgradDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentAbschlusspruefungAkadgradDDDatasource = rdfService.GetDataSourceBlocking(url);
	StudentAbschlusspruefungAkadgradDDDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentAbschlusspruefungAkadgradDDDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	StudentAbschlusspruefungAkadgradDDDatasource.addXMLSinkObserver(StudentAbschlusspruefungAkadgradDDSinkObserver);
	AkadgradDropDown.builder.addListener(StudentAbschlusspruefungAkadgradDDListener);

	AkadgradDropDown.database.AddDataSource(StudentAbschlusspruefungAkadgradDDDatasource);


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

	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;

	//Ausgewaehlte ID holen
	var abschlusspruefung_id = getTreeCellText(tree, "student-abschlusspruefung-treecol-abschlusspruefung_id", tree.currentIndex);

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';

	var req = new phpRequest(url,'','');

	req.add('type', 'deleteabschlusspruefung');

	req.add('abschlusspruefung_id', abschlusspruefung_id);
	req.add('studiengang_kz', studiengang_kz);

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
// ****
// * Druckt das Pruefungsprotokoll fuer mehrere Studenten auf einmal aus.
// * wenn mehrere Abschlusspruefungen angelegt sind, dann wird fuer jede Abschlusspruefung
// * ein Protokoll gedruckt.
// * Den Typ (Bakk/Dipl) des Protokolls bestimmt der zuletzt markierte.
// ****
function StudentAbschlusspruefungPrintPruefungsprotokollMultiple(event, lang)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	//Typ der ersten Abschlusspruefung des zuletzt markierten Studenten (der von dem die Daten geladen wurden) holen
	//ToDo: Wenn zuerst alle Studenten markiert werden und dann diejenigen abmarkiert werden die nicht gedruckt werden sollen,
	//dann funktioniert diese Methode nicht da die Daten desjenigen geladen sind der abmarkiert wurde und dieser moeglicherweise
	//keine Abschlusspruefung eingetragen hat
	try
	{
		var pruefungstyp_kurzbz = getTreeCellText(tree, "student-abschlusspruefung-treecol-pruefungstyp_kurzbz", 0);
	}
	catch(e)
	{
		alert('Der zuletzt markierte Student hat keine Abschlusspruefungen');
		return false;
	}

	if(pruefungstyp_kurzbz=='')
	{
		alert('Der zuletzt markierte Student hat keine Abschlusspruefungen');
		return false;
	}

	if(pruefungstyp_kurzbz=='Bachelor')
	{
		if(lang=='de')
			xsl='PrProtokollBakk';
		if(lang=='en')
			xsl='PrProtBakkEng';
		if(lang=='de2')
			xsl='PrProtBA';
		if(lang=='en2')
			xsl='PrProtBAEng';
	}
	else
	{
		if(lang=='de')
			xsl='PrProtokollDipl';
		if(lang=='en')
			xsl='PrProtDiplEng';
		if(lang=='de2')
			xsl='PrProtMA';
		if(lang=='en2')
			xsl='PrProtMAEng';
	}

	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1)
		return;

	//Uids aller markierten Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;
	var uids='';
	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			uid = ';'+getTreeCellText(tree,"student-treecol-uid", v);
			uids = uids + uid;
			anzahl++;
		}
	}
	var stg_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;

	if (event.shiftKey)
	{
	    var output='odt';
	}
	else if (event.ctrlKey)
	{
		var output='doc';
	}
	else
	{
		var output='pdf';
	}

	window.open('<?php echo APP_ROOT; ?>/content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl='+xsl+'&uid='+uids+'&xsl_stg_kz='+stg_kz+'&output='+output,'Pruefungsprotokoll', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ****
// * Druckt das Pruefungsprotokoll fuer eine bestimmte Abschlusspruefung
// ****
function StudentAbschlusspruefungPrintPruefungsprotokoll(event, lang)
{
	if(lang=='')
		lang='de';
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	if (tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return false;
	}

	//Ausgewaehlte Nr holen
	var abschlusspruefung_id = getTreeCellText(tree,"student-abschlusspruefung-treecol-abschlusspruefung_id", tree.currentIndex);
	var pruefungstyp_kurzbz = getTreeCellText(tree,"student-abschlusspruefung-treecol-pruefungstyp_kurzbz", tree.currentIndex);

	var stg_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;


	if(pruefungstyp_kurzbz=='Bachelor')
	{
		if(lang=='de')
			xsl='PrProtokollBakk';
		if(lang=='en')
			xsl='PrProtBakkEng';
		if(lang=='de2')
			xsl='PrProtBA';
		if(lang=='en2')
			xsl='PrProtBAEng';
	}
	else
	{
		if(lang=='de')
			xsl='PrProtokollDipl';
		if(lang=='en')
			xsl='PrProtDiplEng';
		if(lang=='de2')
			xsl='PrProtMA';
		if(lang=='en2')
			xsl='PrProtMAEng';
	}

	if (event.shiftKey)
	{
	    var output='odt';
	}
	else if (event.ctrlKey)
	{
		var output='doc';
	}
	else
	{
		var output='pdf';
	}

	window.open('<?php echo APP_ROOT; ?>/content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl='+xsl+'&abschlusspruefung_id='+abschlusspruefung_id+'&xsl_stg_kz='+stg_kz+'&output='+output,'Pruefungsprotokoll', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ****
// * Druckt das Pruefungszeugnis fuer mehrere Studenten auf einmal aus.
// * wenn mehrere Abschlusspruefungen angelegt sind, dann wird fuer jede Abschlusspruefung
// * ein Zeugnis gedruckt.
// * Den Typ (Bakk/Dipl) des Zeugnisses bestimmt der zuletzt markierte.
// ****
function StudentAbschlusspruefungPrintPruefungszeugnisMultiple(event, sprache)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	//Typ der ersten Abschlusspruefung des zuletzt markierten Studenten (der von dem die Daten geladen wurden) holen
	try
	{
		var pruefungstyp_kurzbz = getTreeCellText(tree,"student-abschlusspruefung-treecol-pruefungstyp_kurzbz", 0);
	}
	catch(e)
	{
		alert('Der zuletzt markierte Student hat keine Abschlusspruefungen');
		return false;
	}

	if(pruefungstyp_kurzbz=='')
	{
		alert('Der zuletzt markierte Student hat keine Abschlusspruefungen');
		return false;
	}

	if(pruefungstyp_kurzbz=='Bachelor')
	{
		if(sprache=="deutsch")
			xsl='Bakkzeugnis';
		else
			xsl='BakkzeugnisEng';
	}
	else
	{
		if(sprache=="deutsch")
			xsl='Diplomzeugnis';
		else
			xsl='DiplomzeugnisEng';
	}
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1)
		return;

	//Uids aller markierten Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;
	var uids='';
	var stg_kz='';
	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			uid = ';'+getTreeCellText(tree,"student-treecol-uid", v);
			stg_kz = getTreeCellText(tree,"student-treecol-studiengang_kz", v);
			uids = uids + uid;
			anzahl++;
		}
	}

	if (event.shiftKey)
	{
	    var output='odt';
	}
	else if (event.ctrlKey)
	{
		var output='doc';
	}
	else
	{
		var output='pdf';
	}

	window.open('<?php echo APP_ROOT; ?>/content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl='+xsl+'&uid='+uids+'&xsl_stg_kz='+stg_kz+'&output='+output,'Pruefungsprotokoll', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ****
// * Pruefungszeugnis fuer eine bestimmte Abschlusspruefung drucken
// ****
function StudentAbschlusspruefungPrintPruefungszeugnis(event)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	if (tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return false;
	}

	//Ausgewaehlte Nr holen
	var abschlusspruefung_id = getTreeCellText(tree,"student-abschlusspruefung-treecol-abschlusspruefung_id", tree.currentIndex);
	var pruefungstyp_kurzbz = getTreeCellText(tree,"student-abschlusspruefung-treecol-pruefungstyp_kurzbz", tree.currentIndex);

	if(pruefungstyp_kurzbz=='Bachelor')
		xsl='Bakkzeugnis';
	else
		xsl='Diplomzeugnis';

	if (event.shiftKey)
	{
	    var output='odt';
	}
	else if (event.ctrlKey)
	{
		var output='doc';
	}
	else
	{
		var output='pdf';
	}

	window.open('<?php echo APP_ROOT; ?>/content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl='+xsl+'&abschlusspruefung_id='+abschlusspruefung_id+'&output='+output,'PruefungsZeugnis', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ****
// * Druckt den Bescheid fuer eine Abschlusspruefung fuer mehrere Studenten auf einmal aus.
// * wenn mehrere Abschlusspruefungen angelegt sind, dann wird fuer jede Abschlusspruefung
// * ein Bescheid gedruckt.
// * Den Typ (Bakk/Dipl) der Urkunde bestimmt der zuletzt markierte Student.
// ****
function StudentAbschlusspruefungPrintBescheidMultiple(event, sprache)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	//Typ der ersten Abschlusspruefung des zuletzt markierten Studenten (der von dem die Daten geladen wurden) holen
	try
	{
		var pruefungstyp_kurzbz = getTreeCellText(tree,"student-abschlusspruefung-treecol-pruefungstyp_kurzbz", 0);
	}
	catch(e)
	{
		alert('Der zuletzt markierte Student hat keine Abschlusspruefungen');
		return false;
	}

	if(pruefungstyp_kurzbz=='')
	{
		alert('Der zuletzt markierte Student hat keine Abschlusspruefungen');
		return false;
	}

	if(pruefungstyp_kurzbz=='Bachelor' && sprache=='deutsch')
		xsl='Bescheid';
	else if(pruefungstyp_kurzbz=='Bachelor' && sprache=='englisch')
		xsl='BescheidEng';
	else if(pruefungstyp_kurzbz=='Diplom' && sprache=='deutsch')
		xsl='Bescheid';
	else if(pruefungstyp_kurzbz=='Diplom' && sprache=='englisch')
		xsl='BescheidEng';

	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1)
		return;

	//Uids aller markierten Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;
	var uids='';
	var stg_kz=0;
	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			uid = ';'+getTreeCellText(tree,"student-treecol-uid", v);
			uids = uids + uid;
			stg_kz=getTreeCellText(tree,"student-treecol-studiengang_kz", v);
			anzahl++;
		}
	}

	if (event.shiftKey)
	{
	    var output='odt';
	}
	else if (event.ctrlKey)
	{
		var output='doc';
	}
	else
	{
		var output='pdf';
	}

	window.open('<?php echo APP_ROOT; ?>/content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl_stg_kz='+stg_kz+'&xsl='+xsl+'&uid='+uids+'&output='+output,'Pruefungsprotokoll', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}
// ****
// * Druckt die Urkunde fuer eine Abschlusspruefung fuer mehrere Studenten auf einmal aus.
// * wenn mehrere Abschlusspruefungen angelegt sind, dann wird fuer jede Abschlusspruefung
// * eine Urkunde gedruckt.
// * Den Typ (Bakk/Dipl) der Urkunde bestimmt der zuletzt markierte Student.
// ****
function StudentAbschlusspruefungPrintUrkundeMultiple(event, sprache)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	//Typ der ersten Abschlusspruefung des zuletzt markierten Studenten (der von dem die Daten geladen wurden) holen
	try
	{
		var pruefungstyp_kurzbz = getTreeCellText(tree,"student-abschlusspruefung-treecol-pruefungstyp_kurzbz", 0);
	}
	catch(e)
	{
		alert('Der zuletzt markierte Student hat keine Abschlusspruefungen');
		return false;
	}

	if(pruefungstyp_kurzbz=='')
	{
		alert('Der zuletzt markierte Student hat keine Abschlusspruefungen');
		return false;
	}

	if((pruefungstyp_kurzbz=='Bachelor' || pruefungstyp_kurzbz=='lgabschluss') && sprache=='deutsch')
		xsl='Bakkurkunde';
	else if((pruefungstyp_kurzbz=='Bachelor' || pruefungstyp_kurzbz=='lgabschluss') && sprache=='englisch')
		xsl='BakkurkundeEng';
	else if(pruefungstyp_kurzbz=='Diplom' && sprache=='deutsch')
		xsl='Diplomurkunde';
	else if(pruefungstyp_kurzbz=='Diplom' && sprache=='englisch')
		xsl='DiplomurkundeEng';

	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1)
		return;

	//Uids aller markierten Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;
	var uids='';
	var stg_kz=0;
	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			uid = ';'+getTreeCellText(tree,"student-treecol-uid", v);
			uids = uids + uid;
			stg_kz=getTreeCellText(tree,"student-treecol-studiengang_kz", v);
			anzahl++;
		}
	}

	if (event.shiftKey)
	{
	    var output='odt';
	}
	else if (event.ctrlKey)
	{
		var output='doc';
	}
	else
	{
		var output='pdf';
	}

	window.open('<?php echo APP_ROOT; ?>/content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl_stg_kz='+stg_kz+'&xsl='+xsl+'&uid='+uids+'&output='+output,'Pruefungsprotokoll', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ****
// * Druckt eine Urkunde zu der ausgewaehlten Abschlusspruefung
// * die Sprache der Urkunde wird als Parameter uebergeben
// ****
function StudentAbschlusspruefungPrintUrkunde(event, sprache)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-abschlusspruefung-tree');

	if (tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return false;
	}

	//Ausgewaehlte Nr holen
	var abschlusspruefung_id = getTreeCellText(tree,"student-abschlusspruefung-treecol-abschlusspruefung_id", tree.currentIndex);
	var pruefungstyp_kurzbz = getTreeCellText(tree,"student-abschlusspruefung-treecol-pruefungstyp_kurzbz", tree.currentIndex);
	var uid = getTreeCellText(tree,"student-abschlusspruefung-treecol-student_uid", tree.currentIndex);

	if(pruefungstyp_kurzbz=='Bachelor' && sprache=='deutsch')
		xsl='Bakkurkunde';
	else if(pruefungstyp_kurzbz=='Bachelor' && sprache=='englisch')
		xsl='BakkurkundeEng';
	else if(pruefungstyp_kurzbz=='Diplom' && sprache=='deutsch')
		xsl='Diplomurkunde';
	else if(pruefungstyp_kurzbz=='Diplom' && sprache=='englisch')
		xsl='DiplomurkundeEng';
	else if(pruefungstyp_kurzbz=='Defensio' && sprache=='deutsch')
		xsl='Defensiourkunde';
	else if(pruefungstyp_kurzbz=='Master' && sprache=='deutsch')
		xsl='Masterurkunde';
	else if(pruefungstyp_kurzbz=='Abschluss' && sprache=='deutsch')
	    xsl='Magisterurkunde';

	if (event.shiftKey)
	{
	    var output='odt';
	}
	else if (event.ctrlKey)
	{
		var output='doc';
	}
	else
	{
		var output='pdf';
	}

	window.open('<?php echo APP_ROOT; ?>/content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl='+xsl+'&uid=;'+uid+'&abschlusspruefung_id='+abschlusspruefung_id+'&output='+output,'Pruefungsprotokoll', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
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
		document.getElementById('student-abschlusspruefung-label-pruefer1').value='Pruefer 1 (Diplomarbeit)';
		document.getElementById('student-abschlusspruefung-label-pruefer2').value='Pruefer 2';
		document.getElementById('student-abschlusspruefung-menulist-pruefer3').hidden=true;
		document.getElementById('student-abschlusspruefung-label-pruefer3').hidden=true;
	}
}
