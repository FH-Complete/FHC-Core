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

var LeDetailLehrfach_id; //Lehrfach_id die nach dem Laden markiert werden soll
var LeDetailLehrfach_label; //Bezeichnung des Lehrfachs das markiert werden soll
var LeDetailGruppeDatasource; //Datasource fuer Gruppen DropDown
var LeDetailLektorDatasource; //Datasource fuer Lektren DropDown
var LvSelectLehreinheit_id; //Lehreinheit_id die nach dem Rebuild des Trees markiert werden soll
var leDetailLektorUid; // UID der Lektorzuordnung die nach dem Rebuild markiert werden soll
var leDetailLektorLehreinheit_id; // Lehreinheit_id der Lektorzuordnung die nach dem Rebuild markiert werden soll
var lehrveranstaltungNotenTreeDatasource; //Datasource des Noten Trees
var lehrveranstaltungNotenSelectUID=null; //UID des Noten Eintrages der nach dem Refresh markiert werden soll
var lehrveranstaltungLvGesamtNotenTreeDatasource; //Datasource des Noten Trees
var lehrveranstaltungLvGesamtNotenSelectUID=null; //LehreinheitID des Noten Eintrages der nach dem Refresh markiert werden soll

// ********** Observer und Listener ************* //

// ****
// * Observer fuer LV Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var LvTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('lehrveranstaltung-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Lehreinheit wieder
// * markiert
// ****
var LvTreeListener =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
	    window.setTimeout(LvTreeSelectLehreinheit,10);
		// Progressmeter stoppen
		//document.getElementById('statusbar-progressmeter').setAttribute('mode','determined');
	}
};

// ****
// * Nach dem Rebuild wird die Lektorzuordnung
// * wieder markiert
// ****
var LvLektorTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
      window.setTimeout(LeLektorTreeSelectLektor,10);
  }
};


// ****
// * Observer fuer Lehrfachdropdown
// ****
var LeDetailLehrfachSinkObserver =
{
	onBeginLoad: function(aSink) { },
	onInterrupt: function(aSink) { },
	onResume:    function(aSink) { },
	onEndLoad:   function(aSink) {
		//Das richtige Lehrfach markieren
		if(LeDetailLehrfach_id!='') //Wenn die Lehrfach_id bekannt ist, dann einfach markieren
			document.getElementById('lehrveranstaltung-detail-menulist-lehrfach').value=LeDetailLehrfach_id;
		else
		{
			if(LeDetailLehrfach_label!='') //Wenn Name bekannt ist
			{

				menulist = document.getElementById('lehrveranstaltung-detail-menulist-lehrfach');

				//Alle eintraege aus menulist holen
				var items = menulist.childNodes[1].childNodes //Anzahl der Zeilen ermitteln
				found=false;
			   	for(i in items)
				{
					//Richtigen eintrag suchen
					if(items[i].label==LeDetailLehrfach_label)
					{
						//Eintrag markieren
						menulist.selectedIndex=i;
						found=true;
						break;
					}
		   		}
		   		//Wenn nichts gefunden wurde, wird der erste Eintrag markiert
		   		if(!found)
		   			menulist.selectedIndex=0;
			}
		}
	},
	onError: function(aSink, aStatus, aErrorMsg) {
		alert('Bei der Datenuebertragung ist ein Fehler Aufgetreten. Bitte Versuchen Sie es erneut.');
	}
};

// ****
// * Observer fuer Noten Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var LehrveranstaltungNotenTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('lehrveranstaltung-noten-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var LehrveranstaltungNotenTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(LehrveranstaltungNotenTreeSelectID,10);
  }
};

// ****
// * Observer fuer LvGesamtNoten Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var LehrveranstaltungLvGesamtNotenTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('lehrveranstaltung-lvgesamtnoten-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var LehrveranstaltungLvGesamtNotenTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(LehrveranstaltungLvGesamtNotenTreeSelectID,10);
  }
};

// ***************** KEY Events ************************* //

// ****
// * Wird ausgefuehrt wenn eine Taste gedrueckt wird und der Focus
// * im Lehrveranstaltungs-tree ist
// * Beim Druecken von ENTF wird die markierte Lehreinheit geloescht
// * Beim Druecken von F5 wird der Lehrveranstaltungstree aktualisiert
// ****
function LvTreeKeyPress(event)
{
	if(event.keyCode==46) // Entf
		LeDelete();
	else if(event.keyCode==116) // F5
		LvTreeRefresh();
}

// ****
// * Wird ausgefuehrt wenn eine Taste gedrueckt wird und der Focus
// * im Gruppen-tree ist
// * Beim Druecken von ENTF wird die markierte Gruppenzuordnung geloescht
// ****
function LvDetailGruppenTreeKeyPress(event)
{
	if(event.keyCode==46) //Entf
		LeGruppeDel();
}

// ****
// * Wird ausgefuehrt wenn eine Taste gedrueckt wird und der Focus
// * im Mitarbeiter-tree ist
// * Beim Druecken von ENTF wird die markierte Mitarbeiterzuordnung geloescht
// ****
function LvDetailMitarbeiterTreeKeyPress(event)
{
	if(event.keyCode==46) //Entf
		LeMitarbeiterDel();
}

// ****
// * Erstellt den Lehrauftrag fuer
// * einen Mitarbeiter
// ****
function LvCreateLehrauftrag()
{
	stg = document.getElementById('LehrveranstaltungEditor').getAttribute('stg_kz');
	uid = document.getElementById('LehrveranstaltungEditor').getAttribute('uid');
	var ss = document.getElementById('statusbarpanel-semester').label;
	//window.location.href = '<?php echo APP_ROOT; ?>content/lvplanung/lehrauftrag.php?stg_kz='+stg+'&uid='+uid+'&'+gettimestamp();
	window.location.href = '<?php echo APP_ROOT; ?>content/pdfExport.php?xml=lehrauftrag.xml.php&xsl=Lehrauftrag&stg_kz='+stg+'&uid='+uid+'&ss='+ss+'&'+gettimestamp();
}

// ****************** FUNKTIONEN ************************** //

// ****
// * Asynchroner (Nicht blockierender) Refresh des LV Trees
// ****
function LvTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//markierte Lehreinheit global speichern damit diese LE nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('lehrveranstaltung-tree');
	var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehreinheit_id"] : "lehrveranstaltung-treecol-lehreinheit_id";
	try
	{
		LvSelectLehreinheit_id=tree.view.getCellText(tree.currentIndex,col);
	}
	catch(e)
	{
		LvSelectLehreinheit_id=null;
	}
	LvTreeDatasource.Refresh(false); //non blocking
}

// ****
// * neue Lehreinheit anlegen
// ****
function LeNeu()
{
	LeDetailDisableFields(false);

	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

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
	lehrfachmenulist.builder.refresh();

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
}
// ****
// * Selectiert die Lektorzuordnung nachdem der Tree
// * rebuildet wurde.
// ****
function LeLektorTreeSelectLektor()
{
	var tree=document.getElementById('lehrveranstaltung-detail-tree-lehreinheitmitarbeiter');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende Lehreinheit gespeichert
	if(leDetailLektorUid!=null && leDetailLektorLehreinheit_id!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//Lehreinheit_id der row holen
			col = tree.columns ? tree.columns["lehrveranstaltung-lehreinheitmitarbeiter-treecol-lehreinheit_id"] : "lehrveranstaltung-lehreinheitmitarbeiter-treecol-lehreinheit_id";
			lehreinheit_id=tree.view.getCellText(i,col);
			//Uid der row holen
			col = tree.columns ? tree.columns["lehrveranstaltung-lehreinheitmitarbeiter-treecol-mitarbeiter_uid"] : "lehrveranstaltung-lehreinheitmitarbeiter-treecol-mitarbeiter_uid";
			uid=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(leDetailLektorUid==uid && leDetailLektorLehreinheit_id==lehreinheit_id)
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

// ****
// * Selectiert die Lehreinheit nachdem der Tree
// * rebuildet wurde.
// ****
function LvTreeSelectLehreinheit()
{
	var tree=document.getElementById('lehrveranstaltung-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Lehreinheit gespeichert
	if(LvSelectLehreinheit_id!=null)
	{
		//Alle subtrees oeffnen weil rowCount nur die Anzahl der sichtbaren
		//Zeilen zurueckliefert
	   	for(var i=items-1;i>=0;i--)
	   	{
	   		if(!tree.view.isContainerOpen(i))
	   			tree.view.toggleOpenState(i);
	   	}

	   	//Jetzt die wirkliche Anzahl (aller) Zeilen holen
	   	items = tree.view.rowCount;
	   	for(var i=0;i<items;i++)
	   	{
	   		//Lehreinheit_id der row holen
			col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehreinheit_id"] : "lehrveranstaltung-treecol-lehreinheit_id";
			lehreinheit_id=tree.view.getCellText(i,col);
			//Wenn Lehreinheit_id leer ist, dann kann es sein, dass der Tree noch nicht fertig geladen ist
			//dann muss beim Listener das Timeout erhoeht werden

			//wenn dies die zu selektierende Zeile
			if(lehreinheit_id == LvSelectLehreinheit_id)
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

// ****
// * Lehreinheit loeschen
// ****
function LeDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('lehrveranstaltung-tree');

	if (tree.currentIndex==-1)
		return;

	try
	{
		//Ausgewaehlte Lehreinheit holen
        var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehreinheit_id"] : "lehrveranstaltung-treecol-lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(tree.currentIndex,col);
		if(lehreinheit_id=='')
			return false
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Abfrage ob wirklich geloescht werden soll
	if (confirm('Wollen Sie diese Lehreinheit wirklich löschen?'))
	{
		//Script zum loeschen der Lehreinheit aufrufen
		var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');

		req.add('type','lehreinheit');
		req.add('do','delete');
		req.add('lehreinheit_id',lehreinheit_id);
		var response = req.executePOST();

		var val =  new ParseReturnValue(response)
		if(!val.dbdml_return)
			alert(val.dbdml_errormsg)

		LvTreeRefresh();
		LeDetailReset();
	}
}

// ****
// * Leert alle Eingabe- und Auswahlfelder
// ****
function LeDetailReset()
{
	document.getElementById('lehrveranstaltung-detail-textbox-lvnr').value='';
	document.getElementById('lehrveranstaltung-detail-textbox-unr').value='';
	document.getElementById('lehrveranstaltung-detail-textbox-lehrveranstaltung').value='';
	document.getElementById('lehrveranstaltung-detail-checkbox-lehre').checked=true;
	document.getElementById('lehrveranstaltung-detail-textbox-stundenblockung').value='';
	document.getElementById('lehrveranstaltung-detail-textbox-wochenrythmus').value='';
	document.getElementById('lehrveranstaltung-detail-textbox-startkw').value='';
	document.getElementById('lehrveranstaltung-detail-textbox-anmerkung').value='';
	document.getElementById('lehrveranstaltung-detail-menulist-sprache').value='German';
	document.getElementById('lehrveranstaltung-detail-menulist-raumtyp').value='Dummy';
	document.getElementById('lehrveranstaltung-detail-menulist-raumtypalternativ').value='Dummy';
	document.getElementById('lehrveranstaltung-detail-menulist-studiensemester').value='<?php echo $semester_aktuell; ?>';
	document.getElementById('lehrveranstaltung-detail-menulist-lehrform').value='UE';

	//mitarbeiterlehreinheit tree leeren
	lektortree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitmitarbeiter');

	//Alte DS entfernen
	var oldDatasources = lektortree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		lektortree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	lektortree.builder.refresh();

	//Gruppentree leeren
	gruppentree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitgruppe');

	//Alte DS entfernen
	var oldDatasources = gruppentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		gruppentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	gruppentree.builder.refresh();
}

// ****
// * Deaktiviert alle Eingabe- und Auswahlfelder
// ****
function LeDetailDisableFields(val)
{
	//document.getElementById('lehrveranstaltung-detail-textbox-lvnr').disabled=val;
	//document.getElementById('lehrveranstaltung-detail-textbox-unr').disabled=val;
	//document.getElementById('lehrveranstaltung-detail-textbox-lehrveranstaltung').disabled=val;
	document.getElementById('lehrveranstaltung-detail-checkbox-lehre').disabled=val;
	document.getElementById('lehrveranstaltung-detail-textbox-stundenblockung').disabled=val;
	document.getElementById('lehrveranstaltung-detail-textbox-wochenrythmus').disabled=val;
	document.getElementById('lehrveranstaltung-detail-textbox-startkw').disabled=val;
	document.getElementById('lehrveranstaltung-detail-textbox-anmerkung').disabled=val;
	document.getElementById('lehrveranstaltung-detail-menulist-sprache').disabled=val;
	document.getElementById('lehrveranstaltung-detail-menulist-lehrfach').disabled=val;
	document.getElementById('lehrveranstaltung-detail-menulist-raumtyp').disabled=val;
	document.getElementById('lehrveranstaltung-detail-menulist-raumtypalternativ').disabled=val;
	document.getElementById('lehrveranstaltung-detail-menulist-studiensemester').disabled=val;
	document.getElementById('lehrveranstaltung-detail-menulist-lehrform').disabled=val;
	document.getElementById('lehrveranstaltung-detail-tree-lehreinheitgruppe').disabled=val;
	document.getElementById('lehrveranstaltung-detail-button-save').disabled=val;
}

// ****
// * Speichert die Details
// ****
function LeDetailSave()
{
	//Werte holen
	lvnr = document.getElementById('lehrveranstaltung-detail-textbox-lvnr').value;
	unr = document.getElementById('lehrveranstaltung-detail-textbox-unr').value;
	lehrveranstaltung = document.getElementById('lehrveranstaltung-detail-textbox-lehrveranstaltung').value;
	lehre = document.getElementById('lehrveranstaltung-detail-checkbox-lehre').checked;
	stundenblockung = document.getElementById('lehrveranstaltung-detail-textbox-stundenblockung').value;
	wochenrythmus = document.getElementById('lehrveranstaltung-detail-textbox-wochenrythmus').value;
	start_kw = document.getElementById('lehrveranstaltung-detail-textbox-startkw').value;
	anmerkung = document.getElementById('lehrveranstaltung-detail-textbox-anmerkung').value;
	sprache = document.getElementById('lehrveranstaltung-detail-menulist-sprache').value;
	lehrfach = document.getElementById('lehrveranstaltung-detail-menulist-lehrfach').value;
	raumtyp = document.getElementById('lehrveranstaltung-detail-menulist-raumtyp').value;
	raumtypalternativ = document.getElementById('lehrveranstaltung-detail-menulist-raumtypalternativ').value;
	studiensemester = document.getElementById('lehrveranstaltung-detail-menulist-studiensemester').value;
	lehrform = document.getElementById('lehrveranstaltung-detail-menulist-lehrform').value;

	if(lehrveranstaltung=='')
		return false;

	var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');
	neu = document.getElementById('lehrveranstaltung-detail-checkbox-new').checked;

	if (neu)
	{
		req.add('do','create');
	}
	else
	{
		req.add('do','update');
		lehreinheit_id = document.getElementById('lehrveranstaltung-detail-textbox-lehreinheit_id').value;
		req.add('lehreinheit_id',lehreinheit_id);
	}
	//alert(lehreinheit_id);
	req.add('type', 'lehreinheit');
	req.add('unr', unr);
	req.add('lvnr', lvnr);
	req.add('sprache', sprache);
	req.add('lehrveranstaltung', lehrveranstaltung);
	req.add('lehrfach_id', lehrfach);
	req.add('raumtyp', raumtyp);
	req.add('raumtypalternativ', raumtypalternativ);
	req.add('lehre', lehre);
	req.add('stundenblockung', stundenblockung);
	req.add('wochenrythmus', wochenrythmus);
	req.add('start_kw', start_kw);
	req.add('studiensemester_kurzbz', studiensemester);
	req.add('lehrform', lehrform);
	req.add('anmerkung', anmerkung);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		alert(val.dbdml_errormsg)
	}
	else
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('lehrveranstaltung-detail-checkbox-new').checked=false;
		//LvTreeRefresh();
		LvSelectLehreinheit_id=val.dbdml_data;
		LvTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Auswahl einer Lehreinheit
// * bei Auswahl einer Lehreinheit wird diese geladen
// * und die Daten unten angezeigt
// ****
function LeAuswahl()
{

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
		var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehrveranstaltung_id"] : "lehrveranstaltung-treecol-lehrveranstaltung_id";
		var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);

		if(lehreinheit_id=='')
		{
			//Lehrveranstaltung wurde markiert
			//Neu Button aktivieren
			document.getElementById('lehrveranstaltung-toolbar-neu').disabled=false;
			document.getElementById('lehrveranstaltung-toolbar-del').disabled=true;

			//Noten Tab aktivieren
			LehrveranstaltungNotenDisableFields(false);

			//Noten Laden
			LehrveranstaltungNotenLoad(lehrveranstaltung_id);

			LeDetailDisableFields(true);
			//Details zuruecksetzen
			LeDetailReset();
			return false;
		}
		else
		{
			LeDetailDisableFields(false);
			LehrveranstaltungNotenDisableFields(true);

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

		try
		{
			lektortree.builder.removeListener(LvLektorTreeListener);
		}
		catch(e)
		{}

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
}

//******** LehreinheitMitarbeiter **********//

// ****
// * Speichert die Zuteilung von Lektoren
// * zu einer Lehrveranstaltung
// ****
function LeMitarbeiterSave()
{
	//Daten holen
	lehrfunktion = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lehrfunktion_kurzbz').value;
	lektor = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor').value;
	semesterstunden = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden').value;
	planstunden = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-planstunden').value;
	stundensatz = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz').value;
	faktor = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-faktor').value;
	anmerkung = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-anmerkung').value;
	bismelden = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-checkbox-bismelden').checked;

	//Request absetzen
	var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');

	req.add('type','lehreinheit_mitarbeiter_save');
	lehreinheit_id = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-lehreinheit_id').value;
	mitarbeiter_uid = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-mitarbeiter_uid').value;
	req.add('lehreinheit_id',lehreinheit_id);

	req.add('lehrfunktion_kurzbz', lehrfunktion);
	req.add('mitarbeiter_uid', lektor);
	req.add('mitarbeiter_uid_old', mitarbeiter_uid);
	req.add('semesterstunden', semesterstunden);
	req.add('planstunden', planstunden);
	req.add('stundensatz', stundensatz);
	req.add('faktor', faktor);
	req.add('anmerkung', anmerkung);
	req.add('bismelden', bismelden);
	req.add('lehreinheit_id', lehreinheit_id);

	var response = req.executePOST();
	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		alert(val.dbdml_errormsg)
	}
	else
	{
		leDetailLektorUid = lektor;
		leDetailLektorLehreinheit_id = lehreinheit_id;
		LeLektorTreeRefresh();
	}
}

// ****
// * Loescht die Zuteilung eines Lektoren zu einer Lehreinheit
// ****
function LeMitarbeiterDel()
{
	tree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitmitarbeiter');

	//Nachsehen ob Mitarbeiter markiert wurde
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
	{
		alert('Bitte zuerst einen Mitarbeiter markieren');
		return false;
	}

	try
	{
		//UID holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-lehreinheitmitarbeiter-treecol-mitarbeiter_uid"] : "lehrveranstaltung-lehreinheitmitarbeiter-treecol-mitarbeiter_uid";
		var uid=tree.view.getCellText(idx,col);
		//Lehreinheit_id holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-lehreinheitmitarbeiter-treecol-lehreinheit_id"] : "lehrveranstaltung-lehreinheitmitarbeiter-treecol-lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(idx,col);
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');

	req.add('type', 'lehreinheit_mitarbeiter_del');
	req.add('lehreinheit_id', lehreinheit_id);
	req.add('mitarbeiter_uid', uid);

	var response = req.executePOST();
	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		alert(val.dbdml_errormsg)
	}
	else
	{
		//Refresh des Trees
		LeLektorTreeRefresh();
	}
}

// ****
// * Wenn bei den Lektorenzuordnungen Felder bearbeitet werden,
// * dann wird der Speichern Button aktiviert
// ****
function LeMitarbeiterValueChanged()
{
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-button-save').disabled=false;
}

// ****
// * deaktiviert/aktiviert die Lektorendetails und
// * loescht den Inhalt der Felder
// * wenn val=false dann werden die Felder deaktiviert
// * wenn val=true dann werden die Felder aktiviert
// ****
function LeMitarbeiterDisableFields(val)
{
	//Felder Leeren
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lehrfunktion_kurzbz').value='Lektor';
	//document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor').value='';
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden').value='';
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-planstunden').value='';
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz').value='';
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-faktor').value='';
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-anmerkung').value='';
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-checkbox-bismelden').checked=false;

	//Felder aktivieren/deaktivieren
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lehrfunktion_kurzbz').disabled=val;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor').disabled=val;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden').disabled=val;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-planstunden').disabled=val;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz').disabled=val;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-faktor').disabled=val;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-anmerkung').disabled=val;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-checkbox-bismelden').disabled=val;
}

// ****
// * Bei Auswaehlen eines Mitarbeiters werden zu zugehoerigen
// * Details geladen und angezeigt
// ****
function LeMitarbeiterAuswahl()
{
	tree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitmitarbeiter');
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-button-save').disabled=true;
	//Falls kein Eintrag gewaehlt wurde, den ersten auswaehlen
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
		idx = 0;

	try
	{
		//Lehreinheit_id holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-lehreinheitmitarbeiter-treecol-lehreinheit_id"] : "lehrveranstaltung-lehreinheitmitarbeiter-treecol-lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(idx,col);

		//Mitarbeiter_uid holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-lehreinheitmitarbeiter-treecol-mitarbeiter_uid"] : "lehrveranstaltung-lehreinheitmitarbeiter-treecol-mitarbeiter_uid";
		var mitarbeiter_uid=tree.view.getCellText(idx,col);
	}
	catch(e)
	{
		return false;
	}

	// Url zum RDF
	var url="<?php echo APP_ROOT; ?>rdf/lehreinheitmitarbeiter.rdf.php?"+gettimestamp();

	//RDF laden
	var req = new phpRequest(url,'','');
	req.add('lehreinheit_id',lehreinheit_id);
	req.add('mitarbeiter_uid',mitarbeiter_uid);

	var response = req.execute();

	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	// XML in Datasource parsen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/lehreinheitmitarbeiter/liste');

	// Daten aus RDF auslesen
	dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/lehreinheitmitarbeiter/" + lehreinheit_id + "/"+ mitarbeiter_uid);
   	var predicateNS = "http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf";

	//Daten in Variablen speichern
	lehrfunktion_kurzbz = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#lehrfunktion_kurzbz" ));
	semesterstunden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#semesterstunden" ));
	planstunden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#planstunden" ));
	stundensatz = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#stundensatz" ));
	faktor = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#faktor" ));
	anmerkung = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#anmerkung" ));
	bismelden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#bismelden" ));

	//Felder aktivieren
	LeMitarbeiterDisableFields(false);
	//Felder befuellen
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lehrfunktion_kurzbz').value=lehrfunktion_kurzbz;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor').value=mitarbeiter_uid;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden').value=semesterstunden;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-planstunden').value=planstunden;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz').value=stundensatz;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-faktor').value=faktor;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-anmerkung').value=anmerkung;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-lehreinheit_id').value=lehreinheit_id;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-mitarbeiter_uid').value=mitarbeiter_uid;

	if(bismelden=='Ja')
		document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-checkbox-bismelden').checked=true;
	else
		document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-checkbox-bismelden').checked=false;
}

// ****
// * Refresht den Lehreinheitmitarbeiter Tree
// ****
function LeLektorTreeRefresh()
{
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    try
    {
    	LeDetailLektorDatasource.Refresh(true); //Blocking
    	lektortree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitmitarbeiter');
    	lektortree.builder.rebuild();
    }
    catch(e)
    {
    	debug(e);
    }
}

// ************* GRUPPEN ******************** //

// ****
// * Loescht die Zuordnung einer Gruppe zu einer
// * Lehreinheit
// ****
function LeGruppeDel()
{
	tree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitgruppe');

	//Nachsehen ob Gruppe markiert wurde
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
	{
		alert('Bitte zuerst eine Gruppe markieren');
		return false;
	}

	try
	{
		//Lehreinheit_id holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-lehreinheitgruppe-treecol-lehreinheitgruppe_id"] : "lehrveranstaltung-lehreinheitgruppe-treecol-lehreinheitgruppe_id";
		var lehreinheitgruppe_id=tree.view.getCellText(idx,col);
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');
	neu = document.getElementById('lehrveranstaltung-detail-checkbox-new').checked;

	req.add('type', 'lehreinheit_gruppe_del');
	req.add('lehreinheitgruppe_id', lehreinheitgruppe_id);

	var response = req.executePOST();
	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		alert(val.dbdml_errormsg)
	}
	else
	{
		//Refresh des Trees
		LeDetailGruppeTreeRefresh();
		LvTreeRefresh();
	}
}

// ****
// * Gruppen Tree Refreshen
// ****
function LeDetailGruppeTreeRefresh()
{
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    try
    {
    	LeDetailGruppeDatasource.Refresh(true); //Blocking
    	gruppentree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitgruppe');
    	gruppentree.builder.rebuild();
    }
    catch(e)
    {
    	debug(e);
    }
}

// ****************** NOTEN ****************** //

// ****
// * De-/Aktiviert die Noten Felder
// ****
function LehrveranstaltungNotenDisableFields(val)
{
	document.getElementById('lehrveranstaltung-note-copy').disabled=val;
	document.getElementById('lehrveranstaltung-noten-button-import').disabled=val;

	if(val)
		LehrveranstaltungNotenDetailDisableFields(val);
}

// ****
// * De-/Aktiviert die Noten Detail Felder
// ****
function LehrveranstaltungNotenDetailDisableFields(val)
{
	document.getElementById('lehrveranstaltung-noten-button-speichern').disabled=val;
	document.getElementById('lehrveranstaltung-noten-menulist-note').disabled=val;
}

// ****
// * Laedt die Notentrees
// ****
function LehrveranstaltungNotenLoad(lehrveranstaltung_id)
{
	// *** ZeugnisNoten ***
	notentree = document.getElementById('lehrveranstaltung-noten-tree');

	url='<?php echo APP_ROOT;?>rdf/zeugnisnote.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = notentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		notentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	notentree.builder.rebuild();

	try
	{
		LehrveranstaltungNotenTreeDatasource.removeXMLSinkObserver(LehrveranstaltungNotenTreeSinkObserver);
		notentree.builder.removeListener(LehrveranstaltungNotenTreeListener);
	}
	catch(e)
	{}

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	LehrveranstaltungNotenTreeDatasource = rdfService.GetDataSource(url);
	LehrveranstaltungNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	LehrveranstaltungNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	notentree.database.AddDataSource(LehrveranstaltungNotenTreeDatasource);
	LehrveranstaltungNotenTreeDatasource.addXMLSinkObserver(LehrveranstaltungNotenTreeSinkObserver);
	notentree.builder.addListener(LehrveranstaltungNotenTreeListener);

	// *** LvGesamtNoten ***
	var lvgesamtnotentree = document.getElementById('lehrveranstaltung-lvgesamtnoten-tree');

	url='<?php echo APP_ROOT;?>rdf/lvgesamtnote.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = lvgesamtnotentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		lvgesamtnotentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	lvgesamtnotentree.builder.rebuild();

	try
	{
		LehrveranstaltungLvGesamtNotenTreeDatasource.removeXMLSinkObserver(LehrveranstaltungLvGesamtNotenTreeSinkObserver);
		lvgesamtnotentree.builder.removeListener(LehrveranstaltungLvGesamtNotenTreeListener);
	}
	catch(e)
	{}

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	LehrveranstaltungLvGesamtNotenTreeDatasource = rdfService.GetDataSource(url);
	LehrveranstaltungLvGesamtNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	LehrveranstaltungLvGesamtNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	lvgesamtnotentree.database.AddDataSource(LehrveranstaltungLvGesamtNotenTreeDatasource);
	LehrveranstaltungLvGesamtNotenTreeDatasource.addXMLSinkObserver(LehrveranstaltungLvGesamtNotenTreeSinkObserver);
	lvgesamtnotentree.builder.addListener(LehrveranstaltungLvGesamtNotenTreeListener);
}

// ****
// * Markiert einen Eintrag im LVGesamtNotenTree
// ****
function LehrveranstaltungLvGesamtNotenTreeSelectID()
{
	var tree=document.getElementById('lehrveranstaltung-lvgesamtnoten-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(lehrveranstaltungLvGesamtNotenSelectUID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["lehrveranstaltung-lvgesamtnoten-tree-student_uid"] : "lehrveranstaltung-lvgesamtnoten-tree-student_uid";
			var uid=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile ist
			if(uid == lehrveranstaltungLvGesamtNotenSelectUID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				LehrveranstaltungNotenSelectUID=null;
				return true;
			}
	   	}
	}
}

// ****
// * Markiert einen Eintrag im ZeugnisnotenTree
// ****
function LehrveranstaltungNotenTreeSelectID()
{
	var tree=document.getElementById('lehrveranstaltung-noten-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(lehrveranstaltungNotenSelectUID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-student_uid"] : "lehrveranstaltung-noten-tree-student_uid";
			var uid=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(uid == lehrveranstaltungNotenSelectUID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				LehrveranstaltungNotenSelectUID=null;
				return true;
			}
	   	}
	}
}

// ****
// * Uebernimmt die Noten der Lektoren fuer die Zeugnisnote
// ****
function LehrveranstaltungNotenMove()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('lehrveranstaltung-lvgesamtnoten-tree');

	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var i = 0;

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'movenote');

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			col = tree.columns ? tree.columns["lehrveranstaltung-lvgesamtnoten-tree-lehrveranstaltung_id"] : "lehrveranstaltung-lvgesamtnoten-tree-lehrveranstaltung_id";
			lehrveranstaltung_id = tree.view.getCellText(v,col);
			col = tree.columns ? tree.columns["lehrveranstaltung-lvgesamtnoten-tree-student_uid"] : "lehrveranstaltung-lvgesamtnoten-tree-student_uid";
			student_uid = tree.view.getCellText(v,col);
			col = tree.columns ? tree.columns["lehrveranstaltung-lvgesamtnoten-tree-studiensemester_kurzbz"] : "lehrveranstaltung-lvgesamtnoten-tree-studiensemester_kurzbz";
			studiensemester_kurzbz = tree.view.getCellText(v,col);

			req.add('lehrveranstaltung_id_'+i, lehrveranstaltung_id);
			req.add('student_uid_'+i, student_uid);
			req.add('studiensemester_kurzbz_'+i, studiensemester_kurzbz);
			i++;
		}
	}
	req.add('anzahl', i);

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
		LehrveranstaltungNotenTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
		LehrveranstaltungNotenDetailDisableFields(true);
	}
}

// ****
// * Speichert die Noten
// ****
function LehrveranstaltungNoteSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('lehrveranstaltung-noten-tree');

	if (tree.currentIndex==-1)
	{
		alert('Speichern nicht moeglich! Es muss eine Note im Tree ausgewaehlt sein');
		return;
	}

	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-lehrveranstaltung_id"] : "lehrveranstaltung-noten-tree-lehrveranstaltung_id";
	var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-student_uid"] : "lehrveranstaltung-noten-tree-student_uid";
	var student_uid=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-studiensemester_kurzbz"] : "lehrveranstaltung-noten-tree-studiensemester_kurzbz";
	var studiensemester_kurzbz=tree.view.getCellText(tree.currentIndex,col);

	note = document.getElementById('lehrveranstaltung-noten-menulist-note').value;


	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savenote');

	req.add('lehrveranstaltung_id', lehrveranstaltung_id);
	req.add('student_uid', student_uid);
	req.add('studiensemester_kurzbz', studiensemester_kurzbz);
	req.add('note', note);

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
		LehrveranstaltungLvGesamtNotenSelectUID=student_uid;
		LehrveranstaltungNotenTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
		LehrveranstaltungNotenDetailDisableFields(true);
	}
}

// ***
// * Nach dem Auswaehlen einer Note kann diese veraendert werden
// ***
function LehrveranstaltungNotenAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('lehrveranstaltung-noten-tree');

	if (tree.currentIndex==-1) return;

	LehrveranstaltungNotenDetailDisableFields(false);

	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-lehrveranstaltung_id"] : "lehrveranstaltung-noten-tree-lehrveranstaltung_id";
	var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-student_uid"] : "lehrveranstaltung-noten-tree-student_uid";
	var student_uid=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-studiensemester_kurzbz"] : "lehrveranstaltung-noten-tree-studiensemester_kurzbz";
	var studiensemester_kurzbz=tree.view.getCellText(tree.currentIndex,col);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/zeugnisnote.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+'&uid='+student_uid+'&studiensemester_kurzbz='+studiensemester_kurzbz+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/zeugnisnote/" + lehrveranstaltung_id+'/'+student_uid+'/'+studiensemester_kurzbz);

	var predicateNS = "http://www.technikum-wien.at/zeugnisnote/rdf";

	//Daten holen

	note = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#note" ));

	if(note=='')
		note='9';

	document.getElementById('lehrveranstaltung-noten-menulist-note').value=note;
}

// ****
// * Importiert Note aus der Zwischenablage
// * Die Daten in der Zwischenablage sind im Format
// * Matrikelnummer[Tabulator]Note
// ****
function LehrveranstaltungNotenImport()
{

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var data = getDataFromClipboard();
	var tree=document.getElementById('lehrveranstaltung-tree');
	if (tree.currentIndex==-1)
	{
		alert("Bitte zuerst eine Lehrveranstaltung auswaehlen");
		return false;
	}

	var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehrveranstaltung_id"] : "lehrveranstaltung-treecol-lehrveranstaltung_id";
	var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);

	if(lehrveranstaltung_id=='')
	{
		alert("Bitte zuerst eine Lehrveranstaltung auswaehlen");
		return false;
	}

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'importnoten');

	req.add('lehrveranstaltung_id', lehrveranstaltung_id);

	//Reihen ermitteln
	var rows = data.split("\n");
	var i=0;
	for(row in rows)
	{
		zeile = rows[row].split("	");

		req.add('matrikelnummer_'+i, zeile[0]);
		req.add('note_'+i, zeile[1]);
		i++;
	}

	req.add('anzahl', i);

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
		LehrveranstaltungNotenTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}