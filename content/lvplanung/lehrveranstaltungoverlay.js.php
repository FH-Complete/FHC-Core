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

require_once('../../config/global.config.inc.php');
require_once('../../config/vilesci.config.inc.php');

?>
// *********** Globale Variablen *****************//

var LeDetailLehrfach_id; //Lehrfach_id die nach dem Laden markiert werden soll
var LeDetailLehrfach_label; //Bezeichnung des Lehrfachs das markiert werden soll
var LeDetailGruppeDatasource; //Datasource fuer Gruppen DropDown
var LeDetailGruppeDirektDatasource; //Datasource fuer Direkt-Gruppen DropDown
var LeDetailLektorDatasource; //Datasource fuer Lektren DropDown
var LvAngebotGruppenDatasource; //Datasource fuer LV-Angebot Gruppen
var LvSelectLehreinheit_id; //Lehreinheit_id die nach dem Rebuild des Trees markiert werden soll
var LvOpenLehrveranstaltung_id; //Lehrveranstaltung_id der Lehreinheit die gerade gespeichert wurde. Diese LV muss vor dem Select im Tree geoeffnet werden
var leDetailLektorUid; // UID der Lektorzuordnung die nach dem Rebuild markiert werden soll
var leDetailLektorLehreinheit_id; // Lehreinheit_id der Lektorzuordnung die nach dem Rebuild markiert werden soll
var lehrveranstaltungNotenTreeDatasource; //Datasource des Noten Trees
var lehrveranstaltungNotenSelectUID=null; //UID des Noten Eintrages der nach dem Refresh markiert werden soll
var lehrveranstaltungLvGesamtNotenTreeDatasource; //Datasource des Noten Trees
var lehrveranstaltungLvGesamtNotenSelectUID=null; //LehreinheitID des Noten Eintrages der nach dem Refresh markiert werden soll
var lehrveranstaltungNotenTreeloaded=false;
var lehrveranstaltungGesamtNotenTreeloaded=false;
var LehrveranstaltungAusbildungssemesterFilter='';

// Config-Eintrag, ob Vertragsdetails angezeigt werden sollen
var lehrveranstaltung_vertragsdetails_anzeigen = Boolean(<?php echo (defined('FAS_LV_LEKTORINNENZUTEILUNG_VERTRAGSDETAILS_ANZEIGEN') && FAS_LV_LEKTORINNENZUTEILUNG_VERTRAGSDETAILS_ANZEIGEN) ? true : false ?>);

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
	onError : function(pSink, pStatus, pError) { debug('onerror:'+pError); },
	onEndLoad : function(pSink)
	{
		//debug('startrebuild');
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
  		//debug('didrebuild');
		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
	    window.setTimeout(LvTreeSelectLehreinheit,10);
		// Progressmeter stoppen
		document.getElementById('statusbar-progressmeter').setAttribute('mode','determined');
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
// * Nach dem Rebuild wird die LV-Angebot Gruppe
// * wieder markiert
// ****
var LvAngebotTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
      window.setTimeout(LvAngebotTreeSelectGruppe,10);
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
					//Vom Label des DropDowns den Fachbereich abschneiden
					//der dahinter in Klammer steht
					lflabel = items[i].label.substr(0, items[i].label.lastIndexOf('(')).trim();

					//Richtigen Eintrag suchen
					if(lflabel==LeDetailLehrfach_label)
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
		lehrveranstaltungNotenTreeloaded=false;
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
  	  lehrveranstaltungNotenTreeloaded=true;
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
		lehrveranstaltungGesamtNotenTreeloaded=false;
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
  	  lehrveranstaltungGesamtNotenTreeloaded=true;
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
// * Wird ausgefuehrt wenn eine Taste gedrueckt wird und der Focus
// * im LV-Angebot-tree ist
// * Beim Druecken von ENTF wird die markierte Gruppe geloescht
// ****
function LvAngebotTreeKeyPress(event)
{
	if(event.keyCode==46) //Entf
		LvAngebotGruppeDel();
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
	document.getElementById('lehrveranstaltung-detail-gruppen-box').hidden=true;
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
	var url = '<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?lehrveranstaltung_kompatibel_id='+lehrveranstaltung_id+'&'+gettimestamp();

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
// * Selectiert die LV-Angebot Gruppe nachdem der Tree
// * rebuildet wurde.
// ****
function LvAngebotTreeSelectGruppe()
{

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
		//Den Subtree der Lehrveranstaltung oeffnen zu der zuletzt die Lehreinheit gespeichert/angelegt wurde
	   	//da diese sonst nicht markiert werden kann
	   	for(var i=items-1;i>=0;i--)
	   	{
	   		col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehrveranstaltung_id"] : "lehrveranstaltung-treecol-lehrveranstaltung_id";
			lehrveranstaltung_id=tree.view.getCellText(i,col);
	   		if(lehrveranstaltung_id == LvOpenLehrveranstaltung_id)
	   		{
	   			if(!tree.view.isContainerOpen(i))
	   				tree.view.toggleOpenState(i);
	   			break;
	   		}
	   	}
	   	LvOpenLehrveranstaltung_id='';

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
		{
			alert('Lehrveranstaltungen koennen nur von Administratoren geloescht werden');
			return false;
		}
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Abfrage ob wirklich geloescht werden soll
	if (confirm('Wollen Sie diesen LV-Teil wirklich loeschen?'))
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
		LeDetailDisableFields(true);
	}
}

// ****
// * LV-Teile kopieren
// ****
function LeCopy(art)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('lehrveranstaltung-tree');

	if (tree.currentIndex==-1)
		return;

	try
	{
		//Ausgewaehlte LV-Teile holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehreinheit_id"] : "lehrveranstaltung-treecol-lehreinheit_id";
		var lehreinheit_id = tree.view.getCellText(tree.currentIndex,col);
		if(lehreinheit_id == '')
		{
			alert('Lehreinheit_id konnte nicht ermittelt werden');
			return false;
		}
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Script zum kopieren des LV-Teils aufrufen
	var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');

	req.add('type','lehreinheit');
	req.add('do','copy');
	req.add('art',art);
	req.add('lehreinheit_id',lehreinheit_id);
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)
	if(!val.dbdml_return)
		alert(val.dbdml_errormsg)

	LvTreeRefresh();
	LeDetailReset();
	LeDetailDisableFields(true);
}

// ****
// * Leert alle Eingabe- und Auswahlfelder
// ****
function LeDetailReset()
{
	//Sprache der LVA holen
    var tree = document.getElementById('lehrveranstaltung-tree');
    var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-sprache"] : "lehrveranstaltung-treecol-sprache";
    var sprache = tree.view.getCellText(tree.currentIndex,col);

    document.getElementById('lehrveranstaltung-detail-textbox-lvnr').value='';
	document.getElementById('lehrveranstaltung-detail-textbox-unr').value='';
	document.getElementById('lehrveranstaltung-detail-textbox-lehrveranstaltung').value='';
	document.getElementById('lehrveranstaltung-detail-checkbox-lehre').checked=true;
	document.getElementById('lehrveranstaltung-detail-textbox-stundenblockung').value='';
	document.getElementById('lehrveranstaltung-detail-textbox-wochenrythmus').value='';
	document.getElementById('lehrveranstaltung-detail-textbox-startkw').value='';
	document.getElementById('lehrveranstaltung-detail-textbox-anmerkung').value='';
	document.getElementById('lehrveranstaltung-detail-menulist-sprache').value=sprache;
	document.getElementById('lehrveranstaltung-detail-menulist-raumtyp').value='<?php echo DEFAULT_LEHREINHEIT_RAUMTYP; ?>';
	document.getElementById('lehrveranstaltung-detail-menulist-raumtypalternativ').value='<?php echo DEFAULT_LEHREINHEIT_RAUMTYP_ALTERNATIV; ?>';
	document.getElementById('lehrveranstaltung-detail-menulist-studiensemester').value=getStudiensemester();
	document.getElementById('lehrveranstaltung-detail-menulist-lehrform').value='<?php echo DEFAULT_LEHREINHEIT_LEHRFORM; ?>';
	document.getElementById('lehrveranstaltung-detail-textbox-lehreinheit_id').value='';
	document.getElementById('lehrveranstaltung-detail-textbox-gewicht').value='1';

	//mitarbeiterlehreinheit tree leeren
	lektortree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitmitarbeiter');

	//Alte DS entfernen
	var oldDatasources = lektortree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		lektortree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	lektortree.builder.rebuild();

	//Gruppentree leeren
	gruppentree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitgruppe');

	//Alte DS entfernen
	var oldDatasources = gruppentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		gruppentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	gruppentree.builder.rebuild();
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

	document.getElementById('lehrveranstaltung-detail-textbox-unr').disabled=val;
	document.getElementById('lehrveranstaltung-detail-textbox-lehrveranstaltung').disabled=val;
	document.getElementById('lehrveranstaltung-detail-textbox-gewicht').disabled=val;
	document.getElementById('lehrveranstaltung-lehreinheitgruppedirekt-textbox-user').disabled=val;
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
	gewicht = document.getElementById('lehrveranstaltung-detail-textbox-gewicht').value;

	if(lehrveranstaltung=='')
		return false;

	if(raumtyp=='')
	{
		alert('Raumtyp muss ausgewaehlt werden');
		return false;
	}

	if(raumtypalternativ=='')
	{
		alert('RaumtypAlternativ muss ausgewaehlt werden');
		return false;
	}

	if(sprache=='')
	{
		alert('Sprache muss ausgewaehlt werden');
		return false;
	}


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
	req.add('gewicht', gewicht);

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
		LeDetailDisableFields(true);
		//LvTreeRefresh();
		LvSelectLehreinheit_id=val.dbdml_data;
		LvOpenLehrveranstaltung_id=lehrveranstaltung;
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

	document.getElementById('lehrveranstaltung-detail-gruppen-box').hidden=false;
	document.getElementById('lehrveranstaltung-tab-lektor').collapsed=false;

	lehrveranstaltungNotenTreeloaded=false;
	lehrveranstaltungGesamtNotenTreeloaded=false;

	if (tree.currentIndex==-1) return;

	// Wenn Honorareingabe aktiviert ist dann das Vertragsdatum auf heutigen Tag setzen
	var pruefung_vertragsdatum = document.getElementById('lehrveranstaltung-noten-pruefung-box-datum')
	if(pruefung_vertragsdatum)
	{
		var heute = new Date();
		var datum = '';
		datum = heute.getDate()+'.'+(heute.getMonth()+1)+'.'+heute.getFullYear();
		pruefung_vertragsdatum.value=datum;
	}

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

			//Noten Tab einblenden
			//document.getElementById('lehrveranstaltung-tab-noten').collapsed=false;

			//Noten Laden
			LehrveranstaltungNotenLoad(lehrveranstaltung_id);

			//Notizen Tab ausblenden
			document.getElementById('lehrveranstaltung-tab-notizen').collapsed=true;

			if(document.getElementById('lehrveranstaltung-tabs').selectedItem === document.getElementById('lehrveranstaltung-tab-notizen'))
			{
				document.getElementById('lehrveranstaltung-tabs').selectedItem = document.getElementById('lehrveranstaltung-tab-detail');
			}

			//LV-Angebot Tab einblenden und Gruppen laden
			document.getElementById('lehrveranstaltung-tab-lvangebot').collapsed=false;
			LvAngebotLoad(lehrveranstaltung_id);

			if(document.getElementById('lehrveranstaltung-tabs').selectedItem==document.getElementById('lehrveranstaltung-tab-termine'))
			{
				// Termine Laden
				document.getElementById('lehrveranstaltung-termine').setAttribute('src','termine.xul.php?lehrveranstaltung_id='+lehrveranstaltung_id);
			}

			if(document.getElementById('lehrveranstaltung-tabs').selectedItem==document.getElementById('lehrveranstaltung-tab-anwesenheit'))
			{
				// Anwesenheit Laden
				document.getElementById('lehrveranstaltung-anwesenheit').setAttribute('src','anwesenheit.xul.php?lehrveranstaltung_id='+lehrveranstaltung_id);
			}

			LeDetailDisableFields(true);
			//Details zuruecksetzen
			LeDetailReset();
			return false;
		}
		else
		{
			LeDetailDisableFields(false);
			LehrveranstaltungNotenDisableFields(true);
			LehrveranstaltungNotenTreeUnload();

			//Noten Tab ausblenden
			//document.getElementById('lehrveranstaltung-tab-noten').collapsed=true;

			//Notizen Tab einblenden
			document.getElementById('lehrveranstaltung-tab-notizen').collapsed=false;

			//LV-Angebot Tab ausblenden
			document.getElementById('lehrveranstaltung-tab-lvangebot').collapsed=true;

			if(document.getElementById('lehrveranstaltung-tabs').selectedItem==document.getElementById('lehrveranstaltung-tab-termine'))
			{
				// Termine Laden
				document.getElementById('lehrveranstaltung-termine').setAttribute('src','termine.xul.php?lehreinheit_id='+lehreinheit_id);
			}

			document.getElementById('lehrveranstaltung-toolbar-neu').disabled=true;
			document.getElementById('lehrveranstaltung-toolbar-del').disabled=false;

			//Wenn ein Tab markiert ist der nun ausgeblendet wurde,
			//dann wird der Detail Tab markiert
			if(document.getElementById('lehrveranstaltung-tabs').selectedItem.collapsed)
			{
				document.getElementById('lehrveranstaltung-tabs').selectedItem=document.getElementById('lehrveranstaltung-tab-detail');
			}
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
	anzahl_studenten=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anzahl_studenten" ));
	gewicht=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gewicht" ));

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
	var url = '<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?lehrveranstaltung_kompatibel_id='+lehrveranstaltung+'&lehrfach_id='+lehrfach+'&'+gettimestamp();

	//RDF holen
	var newDs  = rdfService.GetDataSourceBlocking(url);
	newDs.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	newDs.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	lehrfachmenulist.database.AddDataSource(newDs);

	lehrfachmenulist.builder.rebuild();
	//SinkObserver hinzufuegen
	//var sink = newDs.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	//sink.addXMLSinkObserver(LeDetailLehrfachSinkObserver);

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
	document.getElementById('lehrveranstaltung-detail-groupbox-caption').label='Details - Anzahl TeilnehmerInnen: '+anzahl_studenten;

	document.getElementById('lehrveranstaltung-detail-textbox-gewicht').value=gewicht;
	//Lehreinheitmitarbeiter tree setzen
	url='<?php echo APP_ROOT;?>rdf/lehreinheitmitarbeiter.rdf.php?lehreinheit_id='+lehreinheit_id+"&"+gettimestamp();
	try
	{
		lektortree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitmitarbeiter');

		try
		{
			lektortree.builder.removeListener(LvLektorTreeListener);
		}
		catch(e)
		{}

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

	//Lehreinheitgruppe Direktzuordnung tree setzen
	url='<?php echo APP_ROOT; ?>rdf/lehreinheit_direkt.rdf.php?lehreinheit_id='+lehreinheit_id+"&"+gettimestamp();

	try
	{
		direkttree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitgruppe-direkt');

		//Alte DS entfernen
		var oldDatasources = direkttree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			direkttree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		direkttree.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		LeDetailGruppeDirektDatasource = rdfService.GetDataSource(url);
		LeDetailGruppeDirektDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		LeDetailGruppeDirektDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		direkttree.database.AddDataSource(LeDetailGruppeDirektDatasource);
	}
	catch(e)
	{
		debug(e);
	}

	// Notizen Laden
	var lehreinheitnotiz = document.getElementById('lehrveranstaltung-box-notizen');
	lehreinheitnotiz.LoadNotizTree('','','','','','','','',lehreinheit_id);
}

function LvDetailsGetCurrentLehreinheitId()
{
	var tree = document.getElementById('lehrveranstaltung-tree');

	if (tree.currentIndex==-1)
		return;
	try
	{
		//Ausgewaehlte Lehreinheit holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehreinheit_id"] : "lehrveranstaltung-treecol-lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(tree.currentIndex,col);

		if(lehreinheit_id!='')
			return lehreinheit_id;
	}
	catch(e)
	{}

	return false;
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
		if(val.dbdml_errormsg=='')
			alert(response);
		else
			alert(val.dbdml_errormsg)
	}
	//else
	//{
		leDetailLektorUid = lektor;
		leDetailLektorLehreinheit_id = lehreinheit_id;
		LeLektorTreeRefresh();
	//}
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
		alert('Bitte zuerst eine/n MitarbeiterIn markieren');
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
	req.add('vertrag_id', vertrag_id);

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
// * Wenn der Lektor geaendert wird, dann den Stundensatz aus der Tabelle Mitarbeiter holen
// ****
function LeMitarbeiterLektorChange()
{
	mitarbeiter_uid = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor').value;

	var url = '<?php echo APP_ROOT ?>content/lvplanung/lehrveranstaltungDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'getstundensatz');
	req.add('mitarbeiter_uid', mitarbeiter_uid);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response);

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response);
		else
			alert(val.dbdml_errormsg);
	}
	else
	{
		stundensatz = val.dbdml_data;
	}
	if (stundensatz != '')
		default_stundensatz = 'Stundensatz (Default '+stundensatz+'):';
	else
		default_stundensatz = 'Stundensatz:';
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz').value=stundensatz;
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-stundensatz').value=default_stundensatz;
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
// * Details sowie ggf. die Vertragsdetails geladen und angezeigt
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

	//Standardstundensatz fuer die Anzeige laden
	var url_stundensatz = '<?php echo APP_ROOT ?>content/lvplanung/lehrveranstaltungDBDML.php';
	var req_stundensatz = new phpRequest(url_stundensatz,'','');

	req_stundensatz.add('type', 'getstundensatz');
	req_stundensatz.add('mitarbeiter_uid', mitarbeiter_uid);

	var response_stundensatz = req_stundensatz.executePOST();

	var val_stundensatz =  new ParseReturnValue(response_stundensatz);

	if (!val_stundensatz.dbdml_return)
	{
		if(val_stundensatz.dbdml_errormsg=='')
			alert(response_stundensatz);
		else
			alert(val_stundensatz.dbdml_errormsg);
	}
	else
	{
		default_stundensatz = val_stundensatz.dbdml_data;
	}
	if (default_stundensatz != '')
		default_stundensatz_text = 'Stundensatz (Default '+default_stundensatz+'):';
	else
		default_stundensatz_text = 'Stundensatz:';

	//Daten in Variablen speichern
	lehrfunktion_kurzbz = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#lehrfunktion_kurzbz" ));
	semesterstunden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#semesterstunden" ));
	planstunden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#planstunden" ));
	stundensatz = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#stundensatz" ));
	faktor = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#faktor" ));
	anmerkung = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#anmerkung" ));
	bismelden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#bismelden" ));
    vertrag_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#vertrag_id" ));

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
	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-stundensatz').value=default_stundensatz_text;

	if(bismelden=='Ja')
		document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-checkbox-bismelden').checked=true;
	else
		document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-checkbox-bismelden').checked=false;

	var gesamtkosten = LeMitarbeiterGesamtkosten();

    // Wenn Vertragsdetails angezeigt werden
    if (lehrveranstaltung_vertragsdetails_anzeigen) {

        // Reset attributes
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-vertragsstatus').setAttribute("style", "font-weight: normal");
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-button-vertrag-stornieren').setAttribute("tooltiptext",
            "Stornieren erst nötig, sobald Lektor einen Vertrag hat. (Ab Status 'Bestellt')");
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-button-vertrag-stornieren').disabled = true;
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor').disabled = false;
        document.getElementById('lehrveranstaltung-lektor-tree-popup-label').disabled = false;
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden').disabled= false;
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz').disabled= false;
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden').setAttribute("tooltiptext", "");
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz').setAttribute("tooltiptext", "");
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor').setAttribute("tooltiptext", "");
        document.getElementById('lehrveranstaltung-lektor-tree-popup-label').setAttribute("tooltiptext", "");

        // Wenn es einen Vertrag zum Lehrauftrag gibt
        if (vertrag_id != null && vertrag_id != '')
        {
            // Änderung und Entfernen des Lektors disablen
            document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor').disabled = true;
            document.getElementById('lehrveranstaltung-lektor-tree-popup-label').disabled = true;

            // Tooltip für Blockierung von Aenderung und Entfernen des Lektors
            document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor').setAttribute("tooltiptext",
                "Änderung nur nach Stornierung des Vertrags möglich.");
            document.getElementById('lehrveranstaltung-lektor-tree-popup-label').setAttribute("tooltiptext",
                "Änderung nur nach Stornierung des Vertrags möglich.");

            // Stornieren ermoeglichen und tooltip entfernen
            document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-button-vertrag-stornieren').disabled = false;
            document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-button-vertrag-stornieren').setAttribute("tooltiptext", "");

            // Url zum RDF
            var url = "<?php echo APP_ROOT; ?>rdf/vertrag.rdf.php?"+gettimestamp();
            //
            ////RDF laden
            var req = new phpRequest(url, '', '');
            req.add('vertrag_id', vertrag_id);
            var response = req.execute();

            // Trick 17	(sonst gibt's ein Permission denied)
            netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

            // XML in Datasource parsen
            var dsource = parseRDFString(response, 'http://www.technikum-wien.at/vertrag/liste');
            var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
            getService(Components.interfaces.nsIRDFService);
            var subject = rdfService.GetResource("http://www.technikum-wien.at/vertrag/" + vertrag_id);
            var predicateNS = "http://www.technikum-wien.at/vertrag/rdf";

            //Daten holen
            betrag = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#betrag" ));
            vertragsdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vertragsdatum" ));
            vertragsstunden = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vertragsstunden" ));
            vertragsstunden_studiensemester_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vertragsstunden_studiensemester_kurzbz" ));


            // Letzten (aktuellsten) Vertragsstatus laden
            var url_letzterStatus = '<?php echo APP_ROOT ?>content/lvplanung/lehrveranstaltungDBDML.php';
            var req_letzterStatus = new phpRequest(url_letzterStatus,'','');

            req_letzterStatus.add('type', 'getLastVertragsstatus');
            req_letzterStatus.add('vertrag_id', vertrag_id);


            var response_letzterStatus = req_letzterStatus.executePOST();

            var val_letzterStatus =  new ParseReturnValue(response_letzterStatus);

            if (!val_letzterStatus.dbdml_return)
            {
                if(val_letzterStatus.dbdml_errormsg=='')
                    alert(response_letzterStatus);
                else
                    alert(val_letzterStatus.dbdml_errormsg);
            }
            else
            {
                var letzterStatus = val_letzterStatus.dbdml_data;
            }

            // Vertragsstatus setzen
            // * wenn Gesamtkosten im Lehrauftrag nicht gleich Betrag im Vertrag ist ODER
            //   wenn Semesterstunden im Lehrauftrag nicht gleich Stunden im Vertrag:
            //   dann: Status 'geändert' hardcoden
            if(gesamtkosten != parseFloat(betrag) || semesterstunden != vertragsstunden)
            {
                vertragsstatus = 'Geändert';
                document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-vertragsstatus').setAttribute("style", "font-weight: bold")
            }
            // * ansonsten Vertragsstatus zeigen
            else
            {
                vertragsstatus = letzterStatus;
            }

            // Uppercase status
            vertragsstatus = vertragsstatus.charAt(0).toUpperCase() + vertragsstatus.slice(1);

            /**
             * Stornierung
             * Nur wenn Vertragsstatus 'akzeptiert' ist:
             * - button Stornierung aktivieren
             * - tooltip ausblenden
             * - Felder zur Bearbeitung von LektorInnendaten deaktivieren
             */
            if(letzterStatus == 'akzeptiert')
            {
                // Semesterstunden und Stundensatz disablen
                document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden').disabled= true;
                document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz').disabled= true;

                // Tooltip für Semesterstunden und Stundensatz
                document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden').setAttribute("tooltiptext",
                    "Änderung nur nach Stornierung des Vertrags möglich.");
                document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz').setAttribute("tooltiptext",
                    "Änderung nur nach Stornierung des Vertrags möglich.");
            }
        }
        // Wenn kein Vertrag vorhanden
        else {
            vertragsstatus = 'Noch kein Vertrag';
            vertragsstunden = '-';
            vertragsstunden_studiensemester_kurzbz = '-';
        }

        // Felder befüllen
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-vertragsstatus').value = vertragsstatus;
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-vertragsstunden').value = vertragsstunden;
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-vertragsstunden_studiensemester_kurzbz').value = vertragsstunden_studiensemester_kurzbz;
        document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-vertrag_id').value = vertrag_id;
    }
}

// ****
// * Storniert einen Vertrag
// ****
function VertragStornieren(){

    var result = confirm("Möchten Sie den Vertrag wirklich stornieren?");

    if (result == true) {

        var vertrag_id = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-vertrag_id').value;
        var mitarbeiter_uid = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor').value;

        // Vertrag stornieren
        var url_storniert = '<?php echo APP_ROOT ?>content/lvplanung/lehrveranstaltungDBDML.php';
        var req_storniert = new phpRequest(url_storniert,'','');

        req_storniert.add('type', 'cancelVertrag');
        req_storniert.add('vertrag_id', vertrag_id);
        req_storniert.add('mitarbeiter_uid', mitarbeiter_uid);

        var response_storniert = req_storniert.executePOST();

        var val_storniert =  new ParseReturnValue(response_storniert);

        if (!val_storniert.dbdml_return)
        {
            if(val_storniert.dbdml_errormsg=='')
                alert(response_storniert);
            else
                alert(val_storniert.dbdml_errormsg);
        }
        else
        {
            // Reiter wieder aufbauen
            LeMitarbeiterAuswahl();
        }
    }


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
// * Laedt das Dropdown fuer die Auswahl der Benutzer
// *  bei direkter Gruppenzuordnung
// ****
function LeGruppeDirektLoad(menulist, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	if(typeof(filter) == 'undefined')
		v = menulist.value;
	else
		v = filter;

	if(v.length > 2)
	{
		var url = '<?php echo APP_ROOT; ?>rdf/benutzer.rdf.php?filter=' + encodeURIComponent(v) + '&' + gettimestamp();

		var oldDatasources = menulist.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			menulist.database.RemoveDataSource(oldDatasources.getNext());
		}

		//Refresh damit die entfernten DS auch wirklich entfernt werden
		menulist.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		if(typeof(filter) == 'undefined')
			var datasource = rdfService.GetDataSource(url);
		else
			var datasource = rdfService.GetDataSourceBlocking(url);

		datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		menulist.database.AddDataSource(datasource);
		if(typeof(filter) != 'undefined')
			menulist.builder.rebuild();
	}
}
// ****
// * Loescht eine Person die direkt zu einer Lehreinheit zugeordnet ist
// *****
function LeGruppeDirektDel()
{
	tree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitgruppe-direkt');

	//Nachsehen ob Gruppe markiert wurde
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return false;
	}

	try
	{
		var col = tree.columns ? tree.columns["lehrveranstaltung-lehreinheitgruppedirekt-treecol-gruppe_kurzbz"] : "lehrveranstaltung-lehreinheitgruppedirekt-treecol-gruppe_kurzbz";
		var gruppe_kurzbz=tree.view.getCellText(idx,col);
		var col = tree.columns ? tree.columns["lehrveranstaltung-lehreinheitgruppedirekt-treecol-uid"] : "lehrveranstaltung-lehreinheitgruppedirekt-treecol-uid";
		var uid=tree.view.getCellText(idx,col);
		var lehreinheit_id = LvDetailsGetCurrentLehreinheitId();
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');
	neu = document.getElementById('lehrveranstaltung-detail-checkbox-new').checked;

	req.add('type', 'lehreinheit_gruppe_direkt_del');
	req.add('gruppe_kurzbz', gruppe_kurzbz);
	req.add('uid', uid);
	req.add('lehreinheit_id', lehreinheit_id);

	var response = req.executePOST();
	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		alert(val.dbdml_errormsg)
	}
	else
	{
		//Refresh des Trees
		LeDetailGruppeDirektTreeRefresh();
	}
}

// ****
// * Fuegt eine Person zu einer Lehreinheit hinzu
// * Zuordnung erfolgt ueber eine direkte Gruppenzuordnung
// ****
function LeGruppeDirektAdd()
{
	var user = MenulistGetSelectedValue('lehrveranstaltung-lehreinheitgruppedirekt-textbox-user');
	var lehreinheit_id = LvDetailsGetCurrentLehreinheitId();

	if(lehreinheit_id=='')
	{
		alert("Lehreinheit wurde nicht gefunden. Daten wurden nicht gespeichert!");
		return;
	}
	if(user == '')
	{
		alert("Bitte wählen sie zuerst einen Eintrag aus");
		return;
	}

	var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');

	req.add('type', 'lehreinheit_direkt_user_add');
	req.add('lehreinheit_id', lehreinheit_id);
	req.add('uid', user);

	var response = req.executePOST();
	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		alert(val.dbdml_errormsg)
	}
	else
	{
		//Refresh des Trees
		LeDetailGruppeDirektTreeRefresh();
	}
}

// ****
// * Refresht den Tree mit den direkt Zugeordneten Personen
// ****
function LeDetailGruppeDirektTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	try
	{
		LeDetailGruppeDirektDatasource.Refresh(true); //Blocking
		gruppentree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitgruppe-direkt');
		gruppentree.builder.rebuild();
	}
	catch(e)
	{
		debug(e);
	}
}

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
// * Loescht den LVPlan einer Gruppe zu einer Lehreinheit aus dem LVPlan
// ****
function LeGruppeDelLVPlan()
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

	if(!confirm("Sind Sie sicher dass Sie diese Gruppe aus dem LVPlan entfernen wollen?"))
		return false;

	var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');
	neu = document.getElementById('lehrveranstaltung-detail-checkbox-new').checked;

	req.add('type', 'lehreinheit_gruppe_del_lvplan');
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
// * Loescht den LVPlan eines Lektors zu einer Lehreinheit aus dem LVPlan
// ****
function LeLektorDelLVPlan()
{
	tree = document.getElementById('lehrveranstaltung-detail-tree-lehreinheitmitarbeiter');

	//Nachsehen ob Lektor markiert wurde
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
	{
		alert('Bitte zuerst eine/n LektorIn markieren');
		return false;
	}

	try
	{
		//Lehreinheit_id holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-lehreinheitmitarbeiter-treecol-lehreinheit_id"] : "lehrveranstaltung-lehreinheitmitarbeiter-treecol-lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(idx,col);
		var col = tree.columns ? tree.columns["lehrveranstaltung-lehreinheitmitarbeiter-treecol-mitarbeiter_uid"] : "lehrveranstaltung-lehreinheitmitarbeiter-treecol-mitarbeiter_uid";
		var mitarbeiter_uid=tree.view.getCellText(idx,col);
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	if(!confirm("Sind Sie sicher dass Sie diese/n MitarbeiterIn aus dem LVPlan entfernen wollen?"))
		return false;

	var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');

	req.add('type', 'lehreinheit_lektor_del_lvplan');
	req.add('lehreinheit_id', lehreinheit_id);
	req.add('mitarbeiter_uid', mitarbeiter_uid);

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
	document.getElementById('lehrveranstaltung-noten-textbox-punkte').disabled=val;
}

// ****
// * Noten Trees Loeschen
// ****
function LehrveranstaltungNotenTreeUnload()
{
 	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	notentree = document.getElementById('lehrveranstaltung-noten-tree');
	var oldDatasources = notentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		notentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	notentree.builder.rebuild();

	var lvgesamtnotentree = document.getElementById('lehrveranstaltung-lvgesamtnoten-tree');
	var oldDatasources = lvgesamtnotentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		lvgesamtnotentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	lvgesamtnotentree.builder.rebuild();
}

// ****
// * Laedt die Notentrees
// ****
function LehrveranstaltungNotenLoad(lehrveranstaltung_id)
{
	// *** ZeugnisNoten ***
	notentree = document.getElementById('lehrveranstaltung-noten-tree');

	url='<?php echo APP_ROOT;?>rdf/zeugnisnote.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+"&"+gettimestamp();

	try
	{
		LehrveranstaltungNotenTreeDatasource.removeXMLSinkObserver(LehrveranstaltungNotenTreeSinkObserver);
		notentree.builder.removeListener(LehrveranstaltungNotenTreeListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = notentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		notentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	notentree.builder.rebuild();
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

	try
	{
		LehrveranstaltungLvGesamtNotenTreeDatasource.removeXMLSinkObserver(LehrveranstaltungLvGesamtNotenTreeSinkObserver);
		lvgesamtnotentree.builder.removeListener(LehrveranstaltungLvGesamtNotenTreeListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = lvgesamtnotentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		lvgesamtnotentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	lvgesamtnotentree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	LehrveranstaltungLvGesamtNotenTreeDatasource = rdfService.GetDataSource(url);
	LehrveranstaltungLvGesamtNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	LehrveranstaltungLvGesamtNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	lvgesamtnotentree.database.AddDataSource(LehrveranstaltungLvGesamtNotenTreeDatasource);
	LehrveranstaltungLvGesamtNotenTreeDatasource.addXMLSinkObserver(LehrveranstaltungLvGesamtNotenTreeSinkObserver);
	lvgesamtnotentree.builder.addListener(LehrveranstaltungLvGesamtNotenTreeListener);
}

// ****
// * Selectiert die Noten im LVGesamtNoteTree welche nicht gleich denen
// * im ZeugnisNoteTree sind
// ****
function LehrveranstaltungGesamtNotenTreeSelectDifferent()
{
	var zeugnistree = document.getElementById("lehrveranstaltung-noten-tree");
	var lvgesamttree = document.getElementById("lehrveranstaltung-lvgesamtnoten-tree");
	lvgesamttree.view.selection.clearSelection();

	if(lehrveranstaltungNotenTreeloaded && lehrveranstaltungGesamtNotenTreeloaded)
	{
		lvgesamttree.view.selection.clearSelection();
		if(lvgesamttree.view)
			var lvgesamtitems = lvgesamttree.view.rowCount; //Anzahl der Zeilen ermitteln
		else
			return false;

		if(zeugnistree.view)
			var zeugnisitems = zeugnistree.view.rowCount; //Anzahl der Zeilen ermitteln
		else
			return false;

		for(var i=0;i<lvgesamtitems;i++)
	   	{
	   		//Daten aus LVGesamtNotenTree holen
			col = lvgesamttree.columns ? lvgesamttree.columns["lehrveranstaltung-lvgesamtnoten-tree-student_uid"] : "lehrveranstaltung-lvgesamtnoten-tree-student_uid";
			var lvgesamtuid=lvgesamttree.view.getCellText(i,col);
			col = lvgesamttree.columns ? lvgesamttree.columns["lehrveranstaltung-lvgesamtnoten-tree-note"] : "lehrveranstaltung-lvgesamtnoten-tree-note";
			var lvgesamtnote=lvgesamttree.view.getCellText(i,col);
			col = lvgesamttree.columns ? lvgesamttree.columns["lehrveranstaltung-lvgesamtnoten-tree-benotungsdatum-iso"] : "lehrveranstaltung-lvgesamtnoten-tree-benotungsdatum-iso";
			var lvgesamtbenotungsdatum=lvgesamttree.view.getCellText(i,col);

			found=false;
			//Schauen ob die gleiche Zeile im Zeugnisnoten Tree vorkommt
			for(var j=0;j<zeugnisitems;j++)
			{
				col = zeugnistree.columns ? zeugnistree.columns["lehrveranstaltung-noten-tree-student_uid"] : "lehrveranstaltung-noten-tree-student_uid";
				var zeugnisuid=zeugnistree.view.getCellText(j,col);
				col = zeugnistree.columns ? zeugnistree.columns["lehrveranstaltung-noten-tree-note"] : "lehrveranstaltung-noten-tree-note";
				var zeugnisnote=zeugnistree.view.getCellText(j,col);
				col = zeugnistree.columns ? zeugnistree.columns["lehrveranstaltung-noten-tree-benotungsdatum-iso"] : "lehrveranstaltung-noten-tree-benotungsdatum-iso";
				var zeugnisbenotungsdatum=zeugnistree.view.getCellText(j,col);

				//debug(zeugnisuid+'=='+lvgesamtuid+' && '+zeugnisnote+'=='+lvgesamtnote);
				if(zeugnisuid==lvgesamtuid && zeugnisnote==lvgesamtnote && zeugnisbenotungsdatum==lvgesamtbenotungsdatum)
				{
					found=true;
					break;
				}

				//Wenn das benotungsdatum im Zeugnis
				//nach dem benotungsdatum des lektors liegt, dann wird die zeile auch nicht markiert.
				//damit wird verhindert, dass pruefungsnoten die nur von der assistenz eingetragen wurden,
				//durch den alten eintrag des lektors wieder ueberschrieben werden
				if(zeugnisuid==lvgesamtuid
				   && zeugnisbenotungsdatum>lvgesamtbenotungsdatum)
				{
					found=true;
					break;
				}
			}

			if(!found)
			{
				//Zeile markieren
				lvgesamttree.view.selection.rangedSelect(i,i,true);
			}
	   	}
	}
}

// ****
// * Markiert einen Eintrag im LVGesamtNotenTree
// ****
function LehrveranstaltungLvGesamtNotenTreeSelectID()
{
	LehrveranstaltungGesamtNotenTreeSelectDifferent();
/*
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
	}*/
}

// ****
// * Markiert einen Eintrag im ZeugnisnotenTree
// ****
function LehrveranstaltungNotenTreeSelectID()
{
	LehrveranstaltungGesamtNotenTreeSelectDifferent();
	/*
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
	}*/
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
			alert(response);
		else
			alert(val.dbdml_errormsg);

		LehrveranstaltungNotenTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
		LehrveranstaltungNotenDetailDisableFields(true);
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
	punkte = document.getElementById('lehrveranstaltung-noten-textbox-punkte').value;


	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savenote');

	req.add('lehrveranstaltung_id', lehrveranstaltung_id);
	req.add('student_uid', student_uid);
	req.add('studiensemester_kurzbz', studiensemester_kurzbz);
	req.add('note', note);
	req.add('punkte', punkte);

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

    var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
    getService(Components.interfaces.nsIRDFService);

	var url = '<?php echo APP_ROOT ?>rdf/zeugnisnote.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+'&uid='+student_uid+'&studiensemester_kurzbz='+studiensemester_kurzbz+'&'+gettimestamp();

    var dsource = rdfService.GetDataSourceBlocking(url);

    var subject = rdfService.GetResource("http://www.technikum-wien.at/zeugnisnote/" + lehrveranstaltung_id+'/'+student_uid+'/'+studiensemester_kurzbz);

    var predicateNS = "http://www.technikum-wien.at/zeugnisnote/rdf";

    //Daten holen

    note = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#note" ));
    punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));

	if(note=='')
		note='9';

	document.getElementById('lehrveranstaltung-noten-menulist-note').value=note;
	document.getElementById('lehrveranstaltung-noten-textbox-punkte').value=punkte;
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

		if(zeile[0]!='' && zeile[1]!='')
		{
			req.add('matrikelnummer_'+i, zeile[0]);
			<?php
			if(CIS_GESAMTNOTE_PUNKTE)
				echo "req.add('punkte_'+i, zeile[1]);";
			else
				echo "req.add('note_'+i, zeile[1]);";
			?>

			i++;
		}
	}

	req.add('anzahl', i);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response);
		else
			alert(val.dbdml_errormsg);

		LehrveranstaltungNotenTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
	else
	{
		LehrveranstaltungNotenTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

/**
 * Wird aufgerufen wenn Punkte zu einer Note eingetragen werden
 * Laedt die Note anhand des Notenschluessels
 */
function LehrveranstaltungNotenPunkteChange()
{
	var punkte = document.getElementById('lehrveranstaltung-noten-textbox-punkte').value;
	punkte = punkte.replace(',','.');
	if(punkte!='')
	{
		var tree=document.getElementById('lehrveranstaltung-noten-tree');
		//Ausgewaehlte LV holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-lehrveranstaltung_id"] : "lehrveranstaltung-noten-tree-lehrveranstaltung_id";
		var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);

		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'getnotenotenschluessel');

		req.add('lehrveranstaltung_id', lehrveranstaltung_id);
		req.add('punkte', punkte);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response)

		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response);
			else
				alert(val.dbdml_errormsg);
		}
		else
		{
			document.getElementById('lehrveranstaltung-noten-menulist-note').value=val.dbdml_data;
		}
	}
}

// ****
// * Erstellt das Zertifikat fuer die Freifaecher
// ****
function LehrveranstaltungFFZertifikatPrint(event, signieren)
{
	tree = document.getElementById('lehrveranstaltung-noten-tree');
	//Alle markierten Noten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;
	var lvid='';

	var ss = getStudiensemester();
	col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-studiengang_kz_lv"] : "lehrveranstaltung-noten-tree-studiengang_kz_lv";
	stg_kz = tree.view.getCellText(tree.currentIndex,col);

	url =  '<?php echo APP_ROOT; ?>content/pdfExport.php?xml=zertifikat.rdf.php&xsl=Zertifikat&stg_kz='+stg_kz+'&ss='+ss+'&'+gettimestamp();

	if (signieren)
	{
		// Wenn die Dokumente signiert werden, dann einzeln erstellen

		var errors='';
		var anz_erfolgreich=0;

		for (var t = 0; t < numRanges; t++)
		{
			tree.view.selection.getRangeAt(t,start,end);
			for (var v = start.value; v <= end.value; v++)
			{
				col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-student_uid"] : "lehrveranstaltung-noten-tree-student_uid";
				uid = tree.view.getCellText(v,col);

				col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-lehrveranstaltung_id"] : "lehrveranstaltung-noten-tree-lehrveranstaltung_id";
				lvid = tree.view.getCellText(v,col);

				// Dokument fuer alle markierten Personen einzeln erstellen und signieren/archivieren

				var req = new phpRequest(url,'','');
				req.add('output', 'pdf');
				req.add('sign', '1');
				req.add('archive', '1');
				req.add('uid', uid);
				req.add('lvid', lvid);

				var response = req.execute();

				if (response != '')
					errors = errors + response;
				else
				{
					anz_erfolgreich = anz_erfolgreich + 1;
				}
			}
		}

		if (errors != '')
		{
			alert(anz_erfolgreich + ' Dokumente wurden erfolgreich erstellt und signiert. Folgende Fehler sind aufgetreten:' + errors);
		}
		else
		{
			alert(anz_erfolgreich + ' Dokumente wurden erfolgreich erstellt und signiert');
		}
	}
	else
	{
		// wenn die Dokumente unsigniert erstellt werden, dann alle in ein Dokument generieren

		for (var t = 0; t < numRanges; t++)
		{
	  		tree.view.selection.getRangeAt(t,start,end);
			for (var v = start.value; v <= end.value; v++)
			{
				col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-student_uid"] : "lehrveranstaltung-noten-tree-student_uid";
				uid = tree.view.getCellText(v,col);
				paramList += ';'+uid;
				anzahl = anzahl+1;
				col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-lehrveranstaltung_id"] : "lehrveranstaltung-noten-tree-lehrveranstaltung_id";
				lvid = tree.view.getCellText(v,col);
			}
		}

		if (event.shiftKey)
		    var output='odt';
		else if (event.ctrlKey)
			var output='doc';
		else
			var output='pdf';

		url = url +'&uid='+paramList+'&lvid='+lvid+'&output='+output;
		window.location.href = url;
	}
}

// ****
// * Erstellt ein Lehrveranstaltungszeugnis fuer die LV
// ****
function LehrveranstaltungLVZeugnisPrint(event, sprache, signieren)
{
	tree = document.getElementById('lehrveranstaltung-noten-tree');
	//Alle markierten Noten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;
	var lvid='';

	var ss = getStudiensemester();
	col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-studiengang_kz"] : "lehrveranstaltung-noten-tree-studiengang_kz";
	stg_kz = tree.view.getCellText(tree.currentIndex,col);

	var xsl = 'LVZeugnis';

	if (sprache == 'English')
		xsl = 'LVZeugnisEng';

	url =  '<?php echo APP_ROOT; ?>content/pdfExport.php?xml=lehrveranstaltungszeugnis.rdf.php&xsl='+xsl+'&stg_kz='+stg_kz+'&ss='+ss+'&'+gettimestamp();

	if(signieren)
	{
		// Wenn die Dokumente signiert werden, dann einzeln erstellen

		var errors='';
		var anz_erfolgreich=0;

		for (var t = 0; t < numRanges; t++)
		{
	  		tree.view.selection.getRangeAt(t,start,end);
			for (var v = start.value; v <= end.value; v++)
			{
				col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-student_uid"] : "lehrveranstaltung-noten-tree-student_uid";
				uid = tree.view.getCellText(v,col);

				col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-lehrveranstaltung_id"] : "lehrveranstaltung-noten-tree-lehrveranstaltung_id";
				lvid = tree.view.getCellText(v,col);

				// Dokument fuer alle markierten Personen einzeln erstellen und signieren/archivieren

				var req = new phpRequest(url,'','');
				req.add('output', 'pdf');
				req.add('sign', '1');
				req.add('archive', '1');
				req.add('uid', uid);
				req.add('lvid', lvid);

				var response = req.execute();

				if (response != '')
					errors = errors + response;
				else
				{
					anz_erfolgreich = anz_erfolgreich + 1;
				}
			}
		}
		if (errors != '')
		{
			alert(anz_erfolgreich + ' Dokumente wurden erfolgreich erstellt und signiert. Folgende Fehler sind aufgetreten:' + errors);
		}
		else
		{
			alert(anz_erfolgreich + ' Dokumente wurden erfolgreich erstellt und signiert');
		}
	}
	else
	{
		// wenn die Dokumente unsigniert erstellt werden, dann alle in ein Dokument generieren

		for (var t = 0; t < numRanges; t++)
		{
	  		tree.view.selection.getRangeAt(t,start,end);
			for (var v = start.value; v <= end.value; v++)
			{
				col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-student_uid"] : "lehrveranstaltung-noten-tree-student_uid";
				uid = tree.view.getCellText(v,col);
				paramList += ';'+uid;
				anzahl = anzahl+1;
				col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-lehrveranstaltung_id"] : "lehrveranstaltung-noten-tree-lehrveranstaltung_id";
				lvid = tree.view.getCellText(v,col);
			}
		}

		if (event.shiftKey)
		    var output='odt';
		else if (event.ctrlKey)
			var output='doc';
		else
			var output='pdf';


		url = url+'&lvid='+lvid+'&uid='+paramList+'&output='+output;
		window.location.href = url;
	}
}

// ****
// * Loescht die markierte Note
// ****
function LehrveranstaltungNotenDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	tree = document.getElementById('lehrveranstaltung-noten-tree');

	col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-student_uid"] : "lehrveranstaltung-noten-tree-student_uid";
	uid = tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-lehrveranstaltung_id"] : "lehrveranstaltung-noten-tree-lehrveranstaltung_id";
	lvid = tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["lehrveranstaltung-noten-tree-studiensemester_kurzbz"] : "lehrveranstaltung-noten-tree-studiensemester_kurzbz";
	stsem = tree.view.getCellText(tree.currentIndex,col);

	if(confirm('Wollen Sie diese Note wirklich l�schen'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'deletenote');

		req.add('lehrveranstaltung_id', lvid);
		req.add('student_uid', uid);
		req.add('studiensemester_kurzbz', stsem);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response)

		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response);
			else
				alert(val.dbdml_errormsg);

			LehrveranstaltungNotenTreeDatasource.Refresh(false); //non blocking
		}
		else
		{
			LehrveranstaltungNotenTreeDatasource.Refresh(false); //non blocking
			LehrveranstaltungNotenDetailDisableFields(true);
			SetStatusBarText('Eintrag wurde geloescht');
		}
	}
}

function LeMitarbeiterGesamtkosten()
{
	semesterstunden = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden').value
	faktor = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-faktor').value
	stundensatz = document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz').value

	if(!isNaN(semesterstunden) && !isNaN(stundensatz))
		gesamtkosten = semesterstunden*stundensatz;
	else
		gesamtkosten = 0;

	document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-gesamtkosten').value=gesamtkosten.toFixed(2)+' €';

	if(gesamtkosten<=0)
		document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-gesamtkosten').setAttribute("style",'color: red');
	else
		document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-label-gesamtkosten').setAttribute("style",'color: black');

	return gesamtkosten;
}

/*
 * Oeffnet alle Subtrees
 */
function LvTreeOpenAllSubtrees(art)
{
	var tree=document.getElementById('lehrveranstaltung-tree');

	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	if (art == 'aus')
	{
		for(var i=items-1;i>=0;i--)
		{
			if(!tree.view.isContainerOpen(i))
				tree.view.toggleOpenState(i);
		}
	}
	else if (art == 'ein')
	{
		for(var i=items-1;i>=0;i--)
		{
			if(tree.view.isContainerOpen(i))
				tree.view.toggleOpenState(i);
		}
	}
}

/**
 * Filtert die Liste der Lehrveranstaltungen auf ein Ausbildungssemester
 * in der OE Ansicht
 */
function FilterLehrveranstaltungAusbsem(semester)
{
	// Auswahl speichern
	LehrveranstaltungAusbildungssemesterFilter=semester;

	// Label aktualisieren
	var label='Filter:';
	if(semester=='')
		label='Filter: Alle Semester';
	else
		label='Filter: '+semester+'.Semester';
	document.getElementById('lehrveranstaltung-toolbar-filter-ausbildungssemester').label=label;

	// Daten neu laden
	onOrganisationseinheitSelect();
}

// ****************** LV-ANGEBOT ****************** //

// ****
// * Aktiviert bzw. deaktiviert das Eingabfeld für die Gruppenbezeichnung
// ****
function ToggleGruppe()
{
	if(document.getElementById('lehrveranstaltung-lvangebot-checkbox-gruppe').checked == false)
		document.getElementById('lehrveranstaltung-lvangebot-textbox-gruppe').disabled = true;
	else
		document.getElementById('lehrveranstaltung-lvangebot-textbox-gruppe').disabled = false;
}

// ****
// * Laedt dynamisch die Gruppen fuer das DropDown Menue
// * Es muessen mindestens 3 Zeichen in das DropDown Menue eingegeben werden
// ****
function LvAngebotGruppenLoad(menulist, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	if(typeof(filter) == 'undefined')
		v = menulist.value;
	else
		v = filter;

	if(v.length > 2)
	{
		var url = '<?php echo APP_ROOT; ?>rdf/gruppen.rdf.php?filter=' + encodeURIComponent(v) + '&' + gettimestamp();

		var oldDatasources = menulist.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			menulist.database.RemoveDataSource(oldDatasources.getNext());
		}

		//Refresh damit die entfernten DS auch wirklich entfernt werden
		menulist.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		if(typeof(filter) == 'undefined')
			var datasource = rdfService.GetDataSource(url);
		else
			var datasource = rdfService.GetDataSourceBlocking(url);

		datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		menulist.database.AddDataSource(datasource);
		if(typeof(filter) != 'undefined')
			menulist.builder.rebuild();
	}
}

// ****
// * Speichert eine neue Gruppe
// ****
function LvAngebotGruppeSave()
{
	var tree = document.getElementById('lehrveranstaltung-tree');
	var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehrveranstaltung_id"] : "lehrveranstaltung-treecol-lehrveranstaltung_id";

	if(tree.currentIndex == -1)
	{
		alert('Bitte zuerst eine Lehrveranstaltung auswaehlen');
		return false;
	}

	//Werte holen
	var lehrveranstaltung_id = tree.view.getCellText(tree.currentIndex,col);
	var neue_gruppe = document.getElementById('lehrveranstaltung-lvangebot-checkbox-gruppe').checked;
	var gruppe = document.getElementById('lehrveranstaltung-lvangebot-textbox-gruppe').value;
	var incomingplaetze = document.getElementById('lehrveranstaltung-lvangebot-textbox-incoming').value;
	var gesamtplaetze = document.getElementById('lehrveranstaltung-lvangebot-textbox-gesamt').value;
	var anmeldefenster_start = document.getElementById('lehrveranstaltung-lvangebot-textbox-start').value;
	var anmeldefenster_ende = document.getElementById('lehrveranstaltung-lvangebot-textbox-ende').value;

	//Eingaben validieren
	if(neue_gruppe == false && gruppe == "")
	{
		alert('Es muss eine Gruppe ausgewaehlt werden');
		return false;
	}

	var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');

	//Wenn ein Angebot gewaehlt wurde dann ID fuer Update ermitteln
	tree = document.getElementById('lehrveranstaltung-lvangebot-tree-gruppen');
	var idx;
	if(tree.currentIndex >= 0)
	{
		idx = tree.currentIndex;
		var col = tree.columns ? tree.columns["lehrveranstaltung-lvangebot-treecol-lvangebot_id"] : "lehrveranstaltung-lvangebot-treecol-lvangebot_id";
		var lvangebot_id = tree.view.getCellText(idx,col);
		req.add('lvangebot_id', lvangebot_id);
	}

	req.add('type', 'lvangebot-gruppe-save');
	req.add('neue_gruppe', neue_gruppe);
	req.add('gruppe', gruppe);
	req.add('incomingplaetze', incomingplaetze);
	req.add('gesamtplaetze', gesamtplaetze);
	req.add('anmeldefenster_start', anmeldefenster_start);
	req.add('anmeldefenster_ende', anmeldefenster_ende);
	req.add('lehrveranstaltung_id', lehrveranstaltung_id);
	req.add('studiensemester_kurzbz', getStudiensemester());

	var response = req.executePOST();

	var val = new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if (val.dbdml_errormsg == "")
			alert('Es ist ein unbekannter Fehler aufgetreten');
		else
			alert(val.dbdml_errormsg);
	}
	else
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		SetStatusBarText('Daten wurden gespeichert');
		LvAngebotGruppeTreeRefresh();
		LvAngebotReset();
	}
}

// ****
// * Laedt alle Gruppen fuer die LV
// ****
function LvAngebotLoad(lehrveranstaltung_id)
{
	url='<?php echo APP_ROOT;?>rdf/lvangebot.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+"&"+gettimestamp();
	try
	{
		lvangebottree = document.getElementById('lehrveranstaltung-lvangebot-tree-gruppen');

		try
		{
			lvangebottree.builder.removeListener(LvAngebotTreeListener);
		}
		catch(e)
		{}

		//Alte DS entfernen
		var oldDatasources = lvangebottree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			lvangebottree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		lvangebottree.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		LvAngebotGruppenDatasource = rdfService.GetDataSource(url);
		LvAngebotGruppenDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		LvAngebotGruppenDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		lvangebottree.database.AddDataSource(LvAngebotGruppenDatasource);
		lvangebottree.builder.addListener(LvAngebotTreeListener);

		//Eingefelder leeren
		LvAngebotReset();
	}
	catch(e)
	{
		debug(e);
	}
}

// ****
// * Loescht die Gruppe
// ****
function LvAngebotGruppeDel()
{
	tree = document.getElementById('lehrveranstaltung-lvangebot-tree-gruppen');

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
		//ID holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-lvangebot-treecol-lvangebot_id"] : "lehrveranstaltung-lvangebot-treecol-lvangebot_id";
		var lvangebot_id = tree.view.getCellText(idx,col);
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');

	req.add('type', 'lvangebot_gruppe_del');
	req.add('lvangebot_id', lvangebot_id);

	var response = req.executePOST();
	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		alert(val.dbdml_errormsg)
	}
	else
	{
		//Refresh des Trees
		LvAngebotGruppeTreeRefresh();
		LvAngebotReset();
	}
}

// ****
// * Refresht den LV-Angebot Gruppen Tree
// ****
function LvAngebotGruppeTreeRefresh()
{
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    try
    {
    	LvAngebotGruppenDatasource.Refresh(true); //Blocking
    	lvangebottree = document.getElementById('lehrveranstaltung-lvangebot-tree-gruppen');
    	lvangebottree.builder.rebuild();
    }
    catch(e)
    {
    	debug(e);
    }
}

// ****
// * Bei Auswaehlen einer Gruppe werden Eingabefelder
// * zum Bearbeiten befuellt
// ****
function LvAngebotGruppeAuswahl()
{
	tree = document.getElementById('lehrveranstaltung-lvangebot-tree-gruppen');
	//Falls kein Eintrag gewaehlt wurde, den ersten auswaehlen
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
		idx = 0;

	try
	{
		//Daten holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-lvangebot-treecol-gruppe"] : "lehrveranstaltung-lvangebot-treecol-gruppe";
		var gruppe = tree.view.getCellText(idx,col);
		var col = tree.columns ? tree.columns["lehrveranstaltung-lvangebot-treecol-plaetze_inc"] : "lehrveranstaltung-lvangebot-treecol-plaetze_inc";
		var plaetze_inc = tree.view.getCellText(idx,col);
		var col = tree.columns ? tree.columns["lehrveranstaltung-lvangebot-treecol-plaetze_gesamt"] : "lehrveranstaltung-lvangebot-treecol-plaetze_gesamt";
		var plaetze_gesamt = tree.view.getCellText(idx,col);
		var col = tree.columns ? tree.columns["lehrveranstaltung-lvangebot-treecol-anmeldefenster_start"] : "lehrveranstaltung-lvangebot-treecol-anmeldefenster_start";
		var anmeldefenster_start = tree.view.getCellText(idx,col);
		var col = tree.columns ? tree.columns["lehrveranstaltung-lvangebot-treecol-anmeldefenster_ende"] : "lehrveranstaltung-lvangebot-treecol-anmeldefenster_ende";
		var anmeldefenster_ende = tree.view.getCellText(idx,col);
	}
	catch(e)
	{
		return false;
	}

	//Felder befuellen
	document.getElementById('lehrveranstaltung-lvangebot-textbox-gruppe').value = gruppe;
	document.getElementById('lehrveranstaltung-lvangebot-textbox-incoming').value = plaetze_inc;
	document.getElementById('lehrveranstaltung-lvangebot-textbox-gesamt').value = plaetze_gesamt;
	document.getElementById('lehrveranstaltung-lvangebot-textbox-start').value = anmeldefenster_start;
	document.getElementById('lehrveranstaltung-lvangebot-textbox-ende').value = anmeldefenster_ende;
}

// ****
// * Setzt alle Eingebfelder zurueck
// ****
function LvAngebotReset()
{
	document.getElementById('lehrveranstaltung-lvangebot-textbox-gruppe').value = '';
	document.getElementById('lehrveranstaltung-lvangebot-textbox-gruppe').disabled = false;
	document.getElementById('lehrveranstaltung-lvangebot-checkbox-gruppe').checked = false;
	document.getElementById('lehrveranstaltung-lvangebot-textbox-incoming').value = '';
	document.getElementById('lehrveranstaltung-lvangebot-textbox-gesamt').value = '';
	document.getElementById('lehrveranstaltung-lvangebot-textbox-start').value = '';
	document.getElementById('lehrveranstaltung-lvangebot-textbox-ende').value = '';
}

// ****
// * Setzt alle Eingebfelder zurueck und entfernt
// * Markierung der Gruppe
// ****
function LvAngebotNew()
{
	LvAngebotReset();
	LvAngebotGruppeTreeRefresh();
}

/**
 * Laedt die Termine einer Lehrveranstaltung/Lehreinheit wenn auf den Tab gewechselt wird
 */
function LehrveranstaltungTermineIFrameLoad()
{
	var tree = document.getElementById('lehrveranstaltung-tree');

	if (tree.currentIndex==-1)
		return;
	try
	{
		//Ausgewaehlte Lehreinheit holen
        var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehreinheit_id"] : "lehrveranstaltung-treecol-lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(tree.currentIndex,col);
		var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehrveranstaltung_id"] : "lehrveranstaltung-treecol-lehrveranstaltung_id";
		var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);

		if(lehreinheit_id!='')
			document.getElementById('lehrveranstaltung-termine').setAttribute('src','termine.xul.php?lehreinheit_id='+lehreinheit_id);
		else
			document.getElementById('lehrveranstaltung-termine').setAttribute('src','termine.xul.php?lehrveranstaltung_id='+lehrveranstaltung_id);
	}
	catch(e)
	{}
}

/**
 * Laedt die Anwesenheiten einer Lehrveranstaltung wenn auf den Tab gewechselt wird
 */
function LehrveranstaltungAnwesenheitIFrameLoad()
{
	var tree = document.getElementById('lehrveranstaltung-tree');

	if (tree.currentIndex==-1)
		return;
	try
	{
		//Ausgewaehlte LV holen
		var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehrveranstaltung_id"] : "lehrveranstaltung-treecol-lehrveranstaltung_id";
		var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);

		if(lehrveranstaltung_id!='')
			document.getElementById('lehrveranstaltung-anwesenheit').setAttribute('src','anwesenheit.xul.php?lehrveranstaltung_id='+lehrveranstaltung_id);
	}
	catch(e)
	{}
}

/**
 * Berechnet das Pruefungshonorar
 */
function LehrveranstaltungNotenPruefungCalculate()
{
	var satz = document.getElementById('lehrveranstaltung-noten-pruefung-textbox-satz').value;
	var anzahl = document.getElementById('lehrveranstaltung-noten-pruefung-textbox-anzahl').value;
	satz = satz.replace(',','.');

	var gesamt = satz*anzahl;
	document.getElementById('lehrveranstaltung-noten-pruefung-label-gesamt').value=gesamt;
	document.getElementById('lehrveranstaltung-noten-pruefung-textbox-anmerkung').value=satz+'€ * '+anzahl;
}

function LehrveranstaltungNotenPruefungSave()
{
	var mitarbeiter_uid = document.getElementById('lehrveranstaltung-noten-pruefung-menulist-mitarbeiter').value;
	var satz = document.getElementById('lehrveranstaltung-noten-pruefung-textbox-satz').value;
	var anzahl = document.getElementById('lehrveranstaltung-noten-pruefung-textbox-anzahl').value;
	var vertragstyp_kurzbz = document.getElementById('lehrveranstaltung-noten-pruefung-menulist-vertragstyp').value;
	var anmerkung = document.getElementById('lehrveranstaltung-noten-pruefung-textbox-anmerkung').value;
	var vertragsdatum = document.getElementById('lehrveranstaltung-noten-pruefung-box-datum').iso;

	satz = satz.replace(',','.');

    if(mitarbeiter_uid == '' || satz == '' || anzahl == '' || vertragstyp_kurzbz=='')
    {
        alert('Bitte wählen Sie eine/n MitarbeiterIn aus und geben Sie den Satz pro Prüfung sowie die Anzahl der Prüfungen an!');
        return false;
    }

	var gesamt = satz*anzahl;

	var tree = document.getElementById('lehrveranstaltung-tree');
	if (tree.currentIndex==-1)
    {
		alert('Bitte wählen Sie die gewünschte Lehrveranstaltung aus!');
        return;
    }

	//Ausgewaehlte LV holen
    var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-lehrveranstaltung_id"] : "lehrveranstaltung-treecol-lehrveranstaltung-id";
	var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-bezeichnung"] : "lehrveranstaltung-treecol-bezeichnung";
	var lv_bezeichnung=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-studiengang"] : "lehrveranstaltung-treecol-studiengang";
	var lv_studiengang=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["lehrveranstaltung-treecol-semester"] : "lehrveranstaltung-treecol-semester";
	var lv_semester=tree.view.getCellText(tree.currentIndex,col);

	var req = new phpRequest('mitarbeiter/mitarbeiterDBDML.php','','');

	req.add('type', 'vertraggenerate');
	req.add('mitarbeiter_uid', mitarbeiter_uid);
	req.add('vertragstyp_kurzbz', vertragstyp_kurzbz);
	req.add('betrag', gesamt);
	req.add('bezeichnung', 'Pruefungshonorar '+lv_studiengang+' '+lv_semester+' '+lv_bezeichnung+' '+lehrveranstaltung_id);
	req.add('anmerkung', anmerkung);
	req.add('vertragsdatum', vertragsdatum);
	req.add('lehrveranstaltung_id',lehrveranstaltung_id);

	var response = req.executePOST();

	var val = new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if (val.dbdml_errormsg == "")
			alert('Es ist ein unbekannter Fehler aufgetreten');
		else
			alert(val.dbdml_errormsg);
	}
	else
	{
		SetStatusBarText('Daten wurden gespeichert');
		document.getElementById('lehrveranstaltung-noten-pruefung-textbox-satz').value='';
		document.getElementById('lehrveranstaltung-noten-pruefung-textbox-anzahl').value='';
		document.getElementById('lehrveranstaltung-noten-pruefung-label-gesamt').value='';
		document.getElementById('lehrveranstaltung-noten-pruefung-textbox-anmerkung').value='';
	}
}

function LehrveranstaltungLVGesamtNotenTreeSort()
{
	// Nach dem Sortieren der Noten die Unterschiede erneut markieren
	// da sonst nach dem sortieren falsche Eintraege markiert sind
	window.setTimeout(LehrveranstaltungGesamtNotenTreeSelectDifferent,20);
}

function LehrveranstaltungNotenTreeSort()
{
}
