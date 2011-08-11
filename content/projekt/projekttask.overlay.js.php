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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */

require_once('../../config/vilesci.config.inc.php');

?>
// *********** Globale Variablen *****************//

var TaskSelectID=null; //ID des Task Eintrages der nach dem Refresh markiert werden soll
// ********** Observer und Listener ************* //

// ****
// * Observer fuer LV Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var TaskTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) { debug('onerror:'+pError); },
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('projekttask-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Lehreinheit wieder
// * markiert
// ****
var TaskTreeListener =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
  		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
	    window.setTimeout(TaskTreeSelectTask,10);
		// Progressmeter stoppen
		//document.getElementById('statusbar-progressmeter').setAttribute('mode','determined');
	}
};

// ****************** FUNKTIONEN ************************** //

// ****
// * Asynchroner (Nicht blockierender) Refresh des LV Trees
// ****
function TaskTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//markierte Lehreinheit global speichern damit diese LE nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('projekttask-tree');
		
	try
	{
		TaskSelectID = getTreeCellText(tree, "projekttask-treecol-projekttask_id", tree.currentIndex);
	}
	catch(e)
	{
		TaskSelectID=null;
	}
	TaskTreeDatasource.Refresh(false); //non blocking
}

// ****
// * neuen Task anlegen
// ****
function TaskNeu()
{
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	alert('Neuer Task - noch nicht implementiert');
	/*
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
	if(lehrform_kurzbz=='')
		lehrform_kurzbz='UE';
	document.getElementById('lehrveranstaltung-detail-menulist-lehrform').value=lehrform_kurzbz;

	var stsem = getStudiensemester();
	document.getElementById('lehrveranstaltung-detail-menulist-studiensemester').value=stsem;
	
	//Defaultwert fuer Anmerkung
	document.getElementById('lehrveranstaltung-detail-textbox-anmerkung').value='<?php echo str_replace("'","\'",LEHREINHEIT_ANMERKUNG_DEFAULT);?>';
	*/
}
// ****
// * Selectiert die Lektorzuordnung nachdem der Tree
// * rebuildet wurde.
// ****
function TaskTreeSelectTask()
{
	var tree=document.getElementById('projekttask-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende ID gespeichert
	if(TaskSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//id der row holen
	   		id = getTreeCellText(tree, "projekttask-treecol-projekttask_id", i);
			
			//wenn dies die zu selektierende Zeile
			if(TaskSelectID==id)
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
// * Task loeschen
// ****
function TaskDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('projekttask-tree');

	if (tree.currentIndex==-1)
		return;

	try
	{
		//Ausgewaehlten Task holen
		id = getTreeCellText(tree, "projekttask-treecol-projekttask_id", tree.currentIndex);
   	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Abfrage ob wirklich geloescht werden soll
	if (confirm('Wollen Sie den Task mit der ID '+id+' wirklich loeschen?'))
	{
		//Script zum loeschen der Lehreinheit aufrufen
		alert('Ist noch nicht implementiert!');
		/*
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
		*/
	}
}

// ****
// * Leert alle Eingabe- und Auswahlfelder
// ****
function TaskDetailReset()
{
	document.getElementById('textbox-projekttaskdetail-projekttask_id').value='';
	document.getElementById('textbox-projekttaskdetail-projektphase_id').value='';
	document.getElementById('textbox-projekttask-detail-bezeichnung').value='';
	document.getElementById('textbox-projekttask-detail-beschreibung').checked=true;
	document.getElementById('textbox-projekttask-detail-aufwand').value='';
	document.getElementById('textbox-projekttask-detail-mantis_id').value='';
}

// ****
// * Deaktiviert alle Eingabe- und Auswahlfelder
// ****
function TaskDisableFields(val)
{
	document.getElementById('textbox-projekttaskdetail-projekttask_id').disabled=val;
	document.getElementById('textbox-projekttaskdetail-projektphase_id').disabled=val;
	document.getElementById('textbox-projekttask-detail-bezeichnung').disabled=val;
	document.getElementById('textbox-projekttask-detail-beschreibung').disabled=val;
	document.getElementById('textbox-projekttask-detail-aufwand').disabled=val;
	document.getElementById('textbox-projekttask-detail-mantis_id').disabled=val;
}

// ****
// * Speichert die Details
// ****
function TaskDetailSave()
{

	//Werte holen
	projekttask_id = document.getElementById('textbox-projekttaskdetail-projekttask_id').value;
	projektphase_id = document.getElementById('textbox-projekttaskdetail-projektphase_id').value;
	bezeichnung = document.getElementById('textbox-projekttask-detail-bezeichnung').value;
	beschreibung = document.getElementById('textbox-projekttask-detail-beschreibung').value;
	aufwand = document.getElementById('textbox-projekttask-detail-aufwand').value;
	mantis_id = document.getElementById('textbox-projekttask-detail-mantis_id').value;

	var soapBody = new SOAPObject("saveProjekttask");
	soapBody.appendChild(new SOAPObject("projekttask_id")).val(projekttask_id);
	soapBody.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
	soapBody.appendChild(new SOAPObject("bezeichnung")).val(bezeichnung);
	soapBody.appendChild(new SOAPObject("beschreibung")).val(beschreibung);
	soapBody.appendChild(new SOAPObject("aufwand")).val(aufwand);
	soapBody.appendChild(new SOAPObject("mantis_id")).val(mantis_id);
	soapBody.appendChild(new SOAPObject("user")).val(getUsername());
	
	var sr = new SOAPRequest("saveProjekttask",soapBody);

	SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projekttask.soap.php?"+gettimestamp();
	SOAPClient.SendRequest(sr, clb_TaskDetailSave);
}

// ****
// * Callback Funktion nach Speichern eines Task
// ****
function clb_TaskDetailSave(respObj)
{
	try
	{
		var id = respObj.Body[0].saveProjekttaskResponse[0].message[0].Text;
	}
	catch(e)
	{
		var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
		alert('Fehler: '+fehler);
		return;
	}
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('textbox-projekttaskdetail-projekttask_id').value=id;
		
	TaskSelectID=id;
	datasourceTreeTask.Refresh(false); //non blocking
	SetStatusBarText('Daten wurden gespeichert');
}

// ****
// * Auswahl eines Tasks
// * bei Auswahl eines Tasks wird diese geladen
// * und die Daten unten angezeigt
// ****
function TaskAuswahl()
{
	
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('projekttask-tree');

	if (tree.currentIndex==-1) return;
	try
	{
	    //Ausgewaehlte Lehreinheit holen
            id = getTreeCellText(tree, "projekttask-treecol-projekttask_id", tree.currentIndex);

            if(id!='')
            {
                //Task wurde markiert
                //Loeschen Button aktivieren
                document.getElementById('projekttask-toolbar-del').disabled=false;
            }
            else
            {
                    return false;
            }
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	alert("Details Laden von Task "+id);
	/*
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
	var url = '<?php echo APP_ROOT;?>rdf/lehrfach.rdf.php?lehrveranstaltung_id='+lehrveranstaltung+'&lehrfach_id='+lehrfach+'&'+gettimestamp();

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
	*/
}