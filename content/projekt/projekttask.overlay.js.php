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
	tree = document.getElementById('tree-projektmenue');
	
	//Projektphase_id holen
	var projektphase_id = getTreeCellText(tree, "treecol-projektmenue-projekt_phase_id", tree.currentIndex);

	if(projektphase_id=='')
	{
		alert('Bitte markieren sie im Projektmenue zuerst eine Projektphase');
		return false;
	}
	//Details zuruecksetzen
	TaskDetailReset();
			
	document.getElementById('textbox-projekttaskdetail-projektphase_id').value=projektphase_id;
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
	document.getElementById('textbox-projekttask-detail-beschreibung').value='';
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
	
	var req = new phpRequest('../rdf/projekttask.rdf.php','','');
	req.add('projekttask_id',id);

	var response = req.execute();
	
	// Datasource holen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/projekttask/alle-projekttasks');

	dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/projekttask/" + id);

	var predicateNS = "http://www.technikum-wien.at/projekttask/rdf";

	//Daten holen
	var projekttask_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#projekttask_id" ));
	var projektphase_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#projektphase_id" ));
	var bezeichnung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bezeichnung" ));
	var beschreibung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beschreibung" ));
	var aufwand=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aufwand" ));
	var mantis_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#mantis_id" ));
	
	//Daten den Feldern zuweisen

	document.getElementById('textbox-projekttaskdetail-projekttask_id').value=projekttask_id;
	document.getElementById('textbox-projekttaskdetail-projektphase_id').value=projektphase_id;
	document.getElementById('textbox-projekttask-detail-bezeichnung').value=bezeichnung;
	document.getElementById('textbox-projekttask-detail-beschreibung').value=beschreibung;
	document.getElementById('textbox-projekttask-detail-aufwand').value=aufwand;
	document.getElementById('textbox-projekttask-detail-mantis_id').value=mantis_id;
}