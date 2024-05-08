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

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');

$user = get_uid();
loadVariables($user);
?>
// *********** Globale Variablen *****************//
var BetriebsmittelTreeDatasource; //Datasource des BetriebsmittelTrees
var BetriebsmittelSelectBetriebsmittelperson_id=null; //Betriebsmittelzurodnung die nach dem Refresh markiert werden soll
var Betriebsmittel_Person_id;
var Betriebsmittel_Person_UID;
// ********** Observer und Listener ************* //

// ****
// * Observer fuer Betriebsmittel Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var BetriebsmittelTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('betriebsmittel-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Betriebsmittelzuordnung wieder
// * markiert
// ****
var BetriebsmittelTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
  	  
      window.setTimeout(BetriebsmittelTreeSelectZuordnung,10);
  }
};


// ***************** KEY Events ************************* //


// ****************** FUNKTIONEN ************************** //


// ****
// * Laedt den Betriebsmitteltree
// ****
function loadBetriebsmittel(person_id, uid)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	Betriebsmittel_Person_id = person_id;
	Betriebsmittel_Person_UID = uid;
	
	// *** Betriebsmittel ***
	betriebsmitteltree = document.getElementById('betriebsmittel-tree');
	url='<?php echo APP_ROOT;?>rdf/betriebsmittelperson.rdf.php?person_id='+person_id+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = betriebsmitteltree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		betriebsmitteltree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	betriebsmitteltree.builder.rebuild();
	
	try
	{
		BetriebsmittelTreeDatasource.removeXMLSinkObserver(BetriebsmittelTreeSinkObserver);
		betriebsmitteltree.builder.removeListener(BetriebsmittelTreeListener);
	}
	catch(e)
	{}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	BetriebsmittelTreeDatasource = rdfService.GetDataSource(url);
	BetriebsmittelTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	BetriebsmittelTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	betriebsmitteltree.database.AddDataSource(BetriebsmittelTreeDatasource);
	BetriebsmittelTreeDatasource.addXMLSinkObserver(BetriebsmittelTreeSinkObserver);
	betriebsmitteltree.builder.addListener(BetriebsmittelTreeListener);
}

// ********** Betriebsmittel ******************

// ****
// * Selectiert die Betriebsmittelzuordnung nachdem der Tree
// * rebuildet wurde.
// ****
function BetriebsmittelTreeSelectZuordnung()
{
	var tree=document.getElementById('betriebsmittel-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Buchung gespeichert
	if(BetriebsmittelSelectBetriebsmittelperson_id!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//id der row holen
			betriebsmittelperson_id=getTreeCellText(tree, "betriebsmittel-tree-betriebsmittelperson_id", i);
			
			//wenn dies die zu selektierende Zeile ist
			if(betriebsmittelperson_id == BetriebsmittelSelectBetriebsmittelperson_id)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				BetriebsmittelSelectBetriebsmittelperson_id=null;
				return true;
			}
	   	}
	}
}

// ****
// * Wenn ein Betriebsmittel ausgewaehlt wird, dann
// * werden die zugehoerigen Details geladen
// ****
function BetriebsmittelAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('betriebsmittel-tree');

	if (tree.currentIndex==-1) 
		return;
		
	BetriebsmittelDetailDisableFields(false);

	document.getElementById('betriebsmittel-checkbox-neu').checked=false;

	//Ausgewaehlte Nr holen
	betriebsmittelperson_id=getTreeCellText(tree, "betriebsmittel-tree-betriebsmittelperson_id", tree.currentIndex);

	if(betriebsmittelperson_id=='')
		return;
	
	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/betriebsmittelperson.rdf.php?betriebsmittelperson_id='+betriebsmittelperson_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/betriebsmittel/"+betriebsmittelperson_id);

	var predicateNS = "http://www.technikum-wien.at/betriebsmittel/rdf";

	//Daten holen
	person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	betriebsmittel_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#betriebsmittel_id" ));
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	kaution = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kaution" ));
	ausgegebenam = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ausgegebenam" ));
	retouram = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#retouram" ));
	betriebsmitteltyp = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#betriebsmitteltyp" ));
	inventarnummer = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#inventarnummer" ));
	nummer = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nummer" ));
	nummer2 = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nummer2" ));
	beschreibung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beschreibung" ));

	document.getElementById('betriebsmittel-textbox-person_id').value=person_id;
	document.getElementById('betriebsmittel-textbox-betriebsmittel_id').value=betriebsmittel_id;
	document.getElementById('betriebsmittel-textbox-betriebsmittelperson_id').value=betriebsmittelperson_id;
	document.getElementById('betriebsmittel-textbox-anmerkung').value=anmerkung;
	document.getElementById('betriebsmittel-textbox-kaution').value=kaution;
	document.getElementById('betriebsmittel-textbox-ausgegebenam').value=ausgegebenam;
	document.getElementById('betriebsmittel-textbox-retouram').value=retouram;
	if(inventarnummer=='')
		document.getElementById('betriebsmittel-menulist-betriebsmitteltyp').value=betriebsmitteltyp;
	else
		document.getElementById('betriebsmittel-menulist-betriebsmitteltyp').value='Inventar';
	document.getElementById('betriebsmittel-textbox-nummer').value=nummer;
	document.getElementById('betriebsmittel-textbox-nummerold').value=nummer;
	document.getElementById('betriebsmittel-textbox-nummer2').value=nummer2;
	document.getElementById('betriebsmittel-textbox-beschreibung').value=beschreibung;
	BetriebsmittelTypChange();
	menulist = document.getElementById('betriebsmittel-menulist-inventarnummer');
	BetriebsmittelMenulistInventarLoad(menulist, inventarnummer);
	MenulistSelectItemOnValue('betriebsmittel-menulist-inventarnummer',betriebsmittel_id);
	
	//Typ darf nach nach Anlegen nicht mehr geändert werden, da sonst normales Inventar
	//angelegt werden koennte, das soll aber nur der Zentraleinkauf machen
	document.getElementById('betriebsmittel-menulist-betriebsmitteltyp').disabled=true;
}

// ****
// * Aktiviert / Deaktiviert die Betriebsmittel Felder
// ****
function BetriebsmittelDisableFields(val)
{
	document.getElementById('betriebsmittel-button-neu').disabled=val;
	document.getElementById('betriebsmittel-button-loeschen').disabled=val;
	BetriebsmittelDetailDisableFields(true);
}

// ****
// * Aktiviert / Deaktiviert die Betriebsmitteldetail Felder
// ****
function BetriebsmittelDetailDisableFields(val)
{
	document.getElementById('betriebsmittel-menulist-betriebsmitteltyp').disabled=val;
	document.getElementById('betriebsmittel-textbox-nummer').disabled=val;
	document.getElementById('betriebsmittel-textbox-nummer2').disabled=val;
	document.getElementById('betriebsmittel-textbox-beschreibung').disabled=val;
	document.getElementById('betriebsmittel-textbox-kaution').disabled=val;
	document.getElementById('betriebsmittel-textbox-anmerkung').disabled=val;
	document.getElementById('betriebsmittel-textbox-ausgegebenam').disabled=val;
	document.getElementById('betriebsmittel-textbox-retouram').disabled=val;
	document.getElementById('betriebsmittel-button-speichern').disabled=val;
	document.getElementById('betriebsmittel-menulist-inventarnummer').disabled=val;

	if(val)
		BetriebsmittelDetailResetFields();
}

// ****
// * Resetet die Betriebsmitteldetail Felder
// ****
function BetriebsmittelDetailResetFields()
{
	document.getElementById('betriebsmittel-menulist-betriebsmitteltyp').value='Zutrittskarte';
	document.getElementById('betriebsmittel-textbox-nummer').value='';
	document.getElementById('betriebsmittel-textbox-nummer2').value='';
	document.getElementById('betriebsmittel-textbox-beschreibung').value='';
	document.getElementById('betriebsmittel-textbox-kaution').value='';
	document.getElementById('betriebsmittel-textbox-anmerkung').value='';
	document.getElementById('betriebsmittel-textbox-ausgegebenam').value='';
	document.getElementById('betriebsmittel-textbox-retouram').value='';
	document.getElementById('betriebsmittel-textbox-nummerold').value='';
	document.getElementById('betriebsmittel-menulist-inventarnummer').value='';
}

// ****
// * Loescht eine Betriebsmittelzuordnung
// ****
function BetriebsmittelDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('betriebsmittel-tree');

	if (tree.currentIndex==-1) return;

	BetriebsmittelDetailDisableFields(false);

	//Ausgewaehlte Nr holen
	betriebsmittelperson_id=getTreeCellText(tree, "betriebsmittel-tree-betriebsmittelperson_id", tree.currentIndex);

	if(window.parent.document.getElementById('main-content-tabs').selectedItem==window.parent.document.getElementById('tab-studenten'))
		studiengang_kz=window.parent.document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	else
		studiengang_kz='';
		
	if(confirm('Diesen Eintrag wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'deletebetriebsmittel');

		req.add('betriebsmittelperson_id', betriebsmittelperson_id);
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
			BetriebsmittelDetailDisableFields(true);
			BetriebsmittelTreeDatasource.Refresh(false);
		}
	}
}

// ****
// * Speichert die Betriebsmittelzuordnung
// ****
function BetriebsmittelDetailSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	person_id = document.getElementById('betriebsmittel-textbox-person_id').value;
	betriebsmittel_id = document.getElementById('betriebsmittel-textbox-betriebsmittel_id').value;
	betriebsmittelperson_id = document.getElementById('betriebsmittel-textbox-betriebsmittelperson_id').value;
	anmerkung = document.getElementById('betriebsmittel-textbox-anmerkung').value;
	kaution = document.getElementById('betriebsmittel-textbox-kaution').value;
	ausgegebenam = document.getElementById('betriebsmittel-textbox-ausgegebenam').value;
	retouram = document.getElementById('betriebsmittel-textbox-retouram').value;
	betriebsmitteltyp = document.getElementById('betriebsmittel-menulist-betriebsmitteltyp').value;
	nummer = document.getElementById('betriebsmittel-textbox-nummer').value;
	nummerold = document.getElementById('betriebsmittel-textbox-nummerold').value;
	nummer2 = document.getElementById('betriebsmittel-textbox-nummer2').value;
	beschreibung = document.getElementById('betriebsmittel-textbox-beschreibung').value;
	neu = document.getElementById('betriebsmittel-checkbox-neu').checked;
	inventarnummer = MenulistGetSelectedValue('betriebsmittel-menulist-inventarnummer');
	if(inventarnummer!='')
		betriebsmittel_id=inventarnummer;

	if(ausgegebenam!='' && !CheckDatum(ausgegebenam))
	{
		alert('AusgegebenAm Datum ist ungueltig');
		return false;
	}
	if(retouram!='' && !CheckDatum(retouram))
	{
		alert('RetourAm Datum ist ungueltig');
		return false;
	}
	
	if(betriebsmitteltyp=='Inventar' && betriebsmittel_id=='')
	{
		/* Wenn nur die Inventarnummer ins DropDown eingetragen wird, 
		 * und der Eintrag nicht ausgewaehlt wird, wird die BetriebsmittelID nicht
		 * korrekt ausgewaehlt.
		 * 
		 * In diesem Fall wird hier geprueft ob im Dropdown nur 1 Eintrag vorhanden ist, 
		 * dann wird dieser ausgewaehlt und gespeichert.
		 */
		menulist = document.getElementById('betriebsmittel-menulist-inventarnummer');
		childs = menulist.getElementsByTagName('menuitem');

		/* Hier wird auf Laenge 2 geprueft, da das 1. Child immer die RDF URL enthaelt
		 * der 2. Eintrag ist dann das 1. Element im DropDown
		 */
		if(childs.length==2)
		{
			betriebsmittel_id = childs[1].value;
		}
		else
		{
			alert('Bitte waehlen Sie das entsprechende Inventar aus dem Drop Down Menue aus!');
			return false;
		}
	}
	if(window.parent.document.getElementById('main-content-tabs').selectedItem==window.parent.document.getElementById('tab-studenten'))
		studiengang_kz=window.parent.document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	else
		studiengang_kz='';
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savebetriebsmittel');

	req.add('neu', neu);
	req.add('person_id', person_id);
	req.add('betriebsmittel_id', betriebsmittel_id);
	req.add('betriebsmittelperson_id', betriebsmittelperson_id);
	req.add('anmerkung', anmerkung);
	req.add('kaution', kaution);
	req.add('ausgegebenam', ConvertDateToISO(ausgegebenam));
	req.add('retouram', ConvertDateToISO(retouram));
	req.add('betriebsmitteltyp', betriebsmitteltyp);
	req.add('nummer', nummer);
	req.add('nummerold', nummerold);
	req.add('nummer2', nummer2);
	req.add('beschreibung', beschreibung);
	req.add('studiengang_kz', studiengang_kz);
	req.add('uid', Betriebsmittel_Person_UID);

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
		BetriebsmittelSelectBetriebsmittelperson_id=val.dbdml_data;
		loadBetriebsmittel(Betriebsmittel_Person_id, Betriebsmittel_Person_UID);
	}
}

// ****
// * Neues Betriebsmittel anlegen
// ****
function BetriebsmittelNeu()
{
	var now = new Date();
	var jahr = now.getFullYear();

	var monat = now.getMonth()+1;

	if(monat<10)
		monat='0'+monat;
	var tag = now.getDate();
	if(tag<10)
		tag='0'+tag;

	document.getElementById('betriebsmittel-checkbox-neu').checked=true;
	BetriebsmittelDetailDisableFields(false);
	BetriebsmittelDetailResetFields();
	document.getElementById('betriebsmittel-textbox-person_id').value = Betriebsmittel_Person_id;
	document.getElementById('betriebsmittel-textbox-betriebsmittelperson_id').value = '';
	document.getElementById('betriebsmittel-textbox-betriebsmittel_id').value = '';
	document.getElementById('betriebsmittel-textbox-ausgegebenam').value=tag+'.'+monat+'.'+jahr;
	document.getElementById('betriebsmittel-textbox-kaution').value = '0.0';
	document.getElementById('betriebsmittel-textbox-nummerold').value='';
	document.getElementById('betriebsmittel-menulist-inventarnummer').value='';
	BetriebsmittelTypChange();
}

// ****
// * Wird aufgerufen, wenn der Betriebsmitteltyp geändert wird
// * Wenn als Betriebsmitteltyp Inventar ausgewaehlt wird, dann wird ein DropDown fuer die 
// * Inventarnummer angezeigt, sonst ein Feld fuer die Nummer und Beschreibung
// ****
function BetriebsmittelTypChange()
{
	betriebsmitteltyp = document.getElementById('betriebsmittel-menulist-betriebsmitteltyp').value;
	if(betriebsmitteltyp=='Inventar')
	{
		document.getElementById('betriebsmittel-row-nummer').hidden=true;
		document.getElementById('betriebsmittel-row-nummer2').hidden=true;
		document.getElementById('betriebsmittel-row-beschreibung').hidden=true;
		document.getElementById('betriebsmittel-row-inventarnummer').hidden=false;
	}
	else
	{
		document.getElementById('betriebsmittel-row-nummer').hidden=false;
		document.getElementById('betriebsmittel-row-nummer2').hidden=false;
		document.getElementById('betriebsmittel-row-beschreibung').hidden=false;
		document.getElementById('betriebsmittel-row-inventarnummer').hidden=true;
	}
}

// ****
// * Laedt dynamisch das Inventarnummern DropDown
// * Es muessen mindestens 3 Zeichen in das DropDown Menue eingegeben werden
// ****
function BetriebsmittelMenulistInventarLoad(menulist, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	if(typeof(filter)=='undefined')
		v = menulist.value;
	else
		v = filter;

	if(v.length>2)
	{		
		var url = '<?php echo APP_ROOT; ?>rdf/betriebsmittel.rdf.php?filter='+encodeURIComponent(v)+'&'+gettimestamp();

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
// * Erstellt eine Uebernahmebestaetigung
// ****
function BetriebsmittelPrintUebernahmebestaetigung()
{
	id = document.getElementById('betriebsmittel-textbox-betriebsmittelperson_id').value;
	
	if(id=='')
	{
		alert('Sie muessen zuerst einen Eintrag auswaehlen');
		return false;
	}
	var url = '<?php echo APP_ROOT; ?>content/pdfExport.php?xsl=Uebernahme&xml=betriebsmittelperson.rdf.php&id='+id;
	window.open(url);
}


