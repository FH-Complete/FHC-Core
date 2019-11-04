<?php
/* Copyright (C) 2014 fhcomplete.org
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
require_once('../../include/functions.inc.php');

$user = get_uid();

?>
// *********** Globale Variablen *****************//
var MitarbeiterBuchungSelectBuchung=null; //Buchung die nach dem Refresh markiert werden soll
var MitarbeiterBuchungTreeDatasource; //Datasource des BuchungTrees
// ********** Observer und Listener ************* //

// ****
// * Observer fuer Buchung Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var MitarbeiterBuchungTreeSinkObserver =
{
	onBeginLoad : function(pSink) 
	{
		tree = document.getElementById('mitarbeiter-buchung-tree');
		tree.removeEventListener('select', MitarbeiterBuchungAuswahl, false);
	},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) { debug('Error MitarbeiterBuchungTreeSinkObserver:'+pError+':'+pStatus); },
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('mitarbeiter-buchung-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Buchung wieder
// * markiert
// ****
var MitarbeiterBuchungTreeListener =
{
	willRebuild : function(builder) {  },
	didRebuild : function(builder)
	{
  		tree = document.getElementById('mitarbeiter-buchung-tree');
		tree.addEventListener('select', MitarbeiterBuchungAuswahl, false);
		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		window.setTimeout(MitarbeiterBuchungTreeSelectBuchung,10);
	}
};

// ****************** FUNKTIONEN ************************** //

function MitarbeiterBuchungLoad(person_id)
{
	if(person_id=='')
		return;
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	// *** Konto ***	
	var menulistkonto = document.getElementById('mitarbeiter-buchung-menulist-konto');
	url='<?php echo APP_ROOT;?>rdf/wawi_konto.rdf.php?person_id='+person_id+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = menulistkonto.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		menulistkonto.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	menulistkonto.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var myDatasource = rdfService.GetDataSourceBlocking(url);
	menulistkonto.database.AddDataSource(myDatasource);
	menulistkonto.builder.rebuild();

	// *** Buchung ***
	buchungtree = document.getElementById('mitarbeiter-buchung-tree');
	url='<?php echo APP_ROOT;?>rdf/wawi_buchung.rdf.php?person_id='+person_id+"&"+gettimestamp();

	try
	{
		MitarbeiterBuchungTreeDatasource.removeXMLSinkObserver(MitarbeiterBuchungTreeSinkObserver);
		buchungtree.builder.removeListener(MitarbeiterBuchungTreeListener);
	}
	catch(e)
	{}
	
	//Alte DS entfernen
	var oldDatasources = buchungtree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		buchungtree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	buchungtree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	MitarbeiterBuchungTreeDatasource = rdfService.GetDataSource(url);
	MitarbeiterBuchungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	MitarbeiterBuchungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	buchungtree.database.AddDataSource(MitarbeiterBuchungTreeDatasource);
	MitarbeiterBuchungTreeDatasource.addXMLSinkObserver(MitarbeiterBuchungTreeSinkObserver);
	buchungtree.builder.addListener(MitarbeiterBuchungTreeListener);

	MitarbeiterBuchungDisableFields(false);
}

// ****
// * Selectiert die Buchung nachdem der Tree
// * rebuildet wurde.
// ****
function MitarbeiterBuchungTreeSelectBuchung()
{
	var tree=document.getElementById('mitarbeiter-buchung-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Buchung gespeichert
	if(MitarbeiterBuchungSelectBuchung!=null)
	{
	   	//Jetzt die wirkliche Anzahl (aller) Zeilen holen
	   	items = tree.view.rowCount;
	   	for(var i=0;i<items;i++)
	   	{
	   		//buchungsnr der row holen
			buchung_id = getTreeCellText(tree, 'mitarbeiter-buchung-tree-buchung_id', i);

			//wenn dies die zu selektierende Zeile
			if(buchung_id == MitarbeiterBuchungSelectBuchung)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				MitarbeiterBuchungSelectBuchung=null;
				return true;
			}
	   	}
	}
}

// ****
// * Wenn eine buchung Ausgewaehlt wird, dann werden
// * die Details geladen und angezeigt
// ****
function MitarbeiterBuchungAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('mitarbeiter-buchung-tree');

	if (tree.currentIndex==-1) return;

	MitarbeiterBuchungDetailDisableFields(false);

	//Ausgewaehlte Nr holen
	var buchung_id = getTreeCellText(tree, 'mitarbeiter-buchung-tree-buchung_id', tree.currentIndex);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/wawi_buchung.rdf.php?buchung_id='+buchung_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/wawi_buchung/" + buchung_id);

	var predicateNS = "http://www.technikum-wien.at/wawi_buchung/rdf";

	//Daten holen

	person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	betrag = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#betrag" ));
	buchungsdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#buchungsdatum" ));
	buchungstext = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#buchungstext" ));
	buchungstyp_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#buchungstyp_kurzbz" ));
	konto_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#konto_id" ));
	kostenstelle_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kostenstelle_id" ));

	document.getElementById('mitarbeiter-buchung-textbox-buchung_id').value=buchung_id;
	document.getElementById('mitarbeiter-buchung-textbox-betrag').value=betrag;
	document.getElementById('mitarbeiter-buchung-textbox-buchungsdatum').value=buchungsdatum;
	document.getElementById('mitarbeiter-buchung-textbox-buchungstext').value=buchungstext;

	MenulistSelectItemOnValue('mitarbeiter-buchung-menulist-konto', konto_id)
	MenulistSelectItemOnValue('mitarbeiter-buchung-menulist-kostenstelle', kostenstelle_id)
	MenulistSelectItemOnValue('mitarbeiter-buchung-menulist-buchungstyp', buchungstyp_kurzbz)
}


// ****
// * Aktiviert / Deaktiviert die Buchung Felder
// ****
function MitarbeiterBuchungDisableFields(val)
{
	document.getElementById('mitarbeiter-buchung-button-neu').disabled=val;
	document.getElementById('mitarbeiter-buchung-button-loeschen').disabled=val;
	MitarbeiterBuchungDetailDisableFields(true);
}

// ****
// * Aktiviert / Deaktiviert die Buchungdetail Felder
// ****
function MitarbeiterBuchungDetailDisableFields(val)
{
	document.getElementById('mitarbeiter-buchung-textbox-betrag').disabled=val;
	document.getElementById('mitarbeiter-buchung-textbox-buchungsdatum').disabled=val;
	document.getElementById('mitarbeiter-buchung-textbox-buchungstext').disabled=val;
	document.getElementById('mitarbeiter-buchung-menulist-buchungstyp').disabled=val;
	document.getElementById('mitarbeiter-buchung-menulist-konto').disabled=val;
	document.getElementById('mitarbeiter-buchung-menulist-kostenstelle').disabled=val;
	document.getElementById('mitarbeiter-buchung-button-speichern').disabled=val;
			
	var menulistkonto = document.getElementById('mitarbeiter-buchung-menulist-konto');
	if((val == false && menulistkonto.itemCount < 1) || val == true)
	{
		document.getElementById('mitarbeiter-buchung-button-konto').hidden=val;	
		document.getElementById('mitarbeiter-buchung-button-konto').disabled=val;
	}
}

// ****
// * Speichert die Buchung
// ****
function MitarbeiterBuchungDetailSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	betrag = document.getElementById('mitarbeiter-buchung-textbox-betrag').value;
	buchungsdatum = document.getElementById('mitarbeiter-buchung-textbox-buchungsdatum').value;
	buchungstext = document.getElementById('mitarbeiter-buchung-textbox-buchungstext').value;
	buchungstyp_kurzbz = document.getElementById('mitarbeiter-buchung-menulist-buchungstyp').value;
	buchung_id = document.getElementById('mitarbeiter-buchung-textbox-buchung_id').value;
	konto_id = document.getElementById('mitarbeiter-buchung-menulist-konto').value;
	kostenstelle_id = document.getElementById('mitarbeiter-buchung-menulist-kostenstelle').value;
	
	if(buchungsdatum!='' && !CheckDatum(buchungsdatum))
	{
		alert('Buchungsdatum ist ungueltig');
		return false;
	}
	var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'buchungsave');

	req.add('betrag', betrag);
	req.add('buchungsdatum', ConvertDateToISO(buchungsdatum));
	req.add('buchungstext', buchungstext);
	req.add('buchungstyp_kurzbz', buchungstyp_kurzbz);
	req.add('buchung_id', buchung_id);
	req.add('konto_id', konto_id);
	req.add('kostenstelle_id', kostenstelle_id);

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
		MitarbeiterBuchungSelectBuchung=buchung_id;
		MitarbeiterBuchungTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Loescht eine Buchung
// ****
function MitarbeiterBuchungDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('mitarbeiter-buchung-tree');

	if (tree.currentIndex==-1) return;

	MitarbeiterBuchungDetailDisableFields(false);

	//Ausgewaehlte Nr holen
	var buchung_id = getTreeCellText(tree, 'mitarbeiter-buchung-tree-buchung_id', tree.currentIndex);

	if(confirm('Diese Buchung wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'buchungdelete');

		req.add('buchung_id', buchung_id);

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
			MitarbeiterBuchungTreeDatasource.Refresh(false);
			return true;
		}
	}
}

// ****
// * Ruft einen Dialog zum Anlegen von Buchungen auf
// ****
function MitarbeiterBuchungNeu()
{
	MitarbeiterBuchungDetailDisableFields(false);
	MitarbeiterBuchungDetailReset();
	MitarbeiterBuchungKontoRefresh();
}

// ****
// * Leert die Eingabe-Felder der Buchung
// ****
function MitarbeiterBuchungDetailReset()
{
	document.getElementById('mitarbeiter-buchung-textbox-betrag').value='';	
	document.getElementById('mitarbeiter-buchung-textbox-buchungstext').value='';
	document.getElementById('mitarbeiter-buchung-textbox-buchung_id').value='';

	var now = new Date();
	var jahr = now.getFullYear();

	monat = now.getMonth()+1;
	if(monat<10) 
		monat='0'+monat;
	tag = now.getDate();
	if(tag<10) 
		tag='0'+tag;

	document.getElementById('mitarbeiter-buchung-textbox-buchungsdatum').value=tag+'.'+monat+'.'+jahr;
}

// ****
// * Legt ein neues Konto für den Mitarbeiter an
// ****
function MitarbeiterBuchungKontoAnlegen()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	var tree = document.getElementById('mitarbeiter-tree');
	if (tree.currentIndex == -1)
	{
		alert('Bitte waehlen Sie eine/n MitarbeiterIn aus');
		return false;
	}

	// Daten holen
	vorname = getTreeCellText(tree, 'mitarbeiter-treecol-vorname', tree.currentIndex);
	nachname = getTreeCellText(tree, 'mitarbeiter-treecol-nachname', tree.currentIndex);
	uid = getTreeCellText(tree, 'mitarbeiter-treecol-uid', tree.currentIndex);
	kurzbz = getTreeCellText(tree, 'mitarbeiter-treecol-kurzbz', tree.currentIndex);
	person_id = getTreeCellText(tree, 'mitarbeiter-treecol-person_id', tree.currentIndex);
	
	var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'kontosave');
	req.add('beschreibung', vorname + ' ' + nachname + ' ' + uid);
	req.add('kurzbz', kurzbz);
	req.add('person_id', person_id);
	
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
		document.getElementById('mitarbeiter-buchung-button-konto').disabled = true;
		SetStatusBarText('Daten wurden gespeichert');
		MitarbeiterBuchungKontoRefresh();
	}
}

// ****
// * Aktualisiert die Konto Dropdown-Liste
// ****
function MitarbeiterBuchungKontoRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");	
	var menulistkonto = document.getElementById('mitarbeiter-buchung-menulist-konto');
	url='<?php echo APP_ROOT;?>rdf/wawi_konto.rdf.php?person_id='+person_id+"&"+gettimestamp();
	
	//alle Eintraege entfernen
	menulistkonto.removeAllItems();

	//Alte DS entfernen
	var oldDatasources = menulistkonto.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		menulistkonto.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	menulistkonto.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var myDatasource = rdfService.GetDataSourceBlocking(url);
	menulistkonto.database.AddDataSource(myDatasource);
	menulistkonto.builder.rebuild();
}
