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
	datasourceTreeTask.Refresh(false); //non blocking
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
		var soapBody = new SOAPObject("deleteProjekttask");
		soapBody.appendChild(new SOAPObject("projekttask_id")).val(id);
		var sr = new SOAPRequest("deleteProjekttask",soapBody);
	
		SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projekttask.soap.php?"+gettimestamp();
		SOAPClient.SendRequest(sr, clb_deleteProjekttask);
	}
}
// ****
// * Delete Callback Funktion
// ****
function clb_deleteProjekttask(respObj)
{
	try
	{
		var msg = respObj.Body[0].deleteProjekttaskResponse[0].message[0].Text;
	}
	catch(e)
	{
		var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
		alert('Fehler: '+fehler);
		return;
	}
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			
	TaskSelectID='';
	datasourceTreeTask.Refresh(false); //non blocking
	SetStatusBarText('Eintrag wurde entfernt');
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
function saveProjekttaskDetail()
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
	SOAPClient.SendRequest(sr, clb_saveProjekttask);
}

// ****
// * Callback Funktion nach Speichern eines Task
// ****
function clb_saveProjekttask(respObj)
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
function onselectProjekttask()
{
	
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('projekttask-tree');

	if (tree.currentIndex==-1) return;
	try
	{
	    //Ausgewaehlte Lehreinheit holen
            id = getTreeCellText(tree, "projekttask-treecol-projekttask_id", tree.currentIndex);
            mantis_id = getTreeCellText(tree, "projekttask-treecol-mantis_id", tree.currentIndex);

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
	
	//Mantis Tab reset
	document.getElementById('textbox-projekttask-mantis-mantis_id').value='';
	document.getElementById('textbox-projekttask-mantis-issue_summary').value=bezeichnung;
	document.getElementById('textbox-projekttask-mantis-issue_description').value=beschreibung;
	document.getElementById('textbox-projekttask-mantis-issue_view_state_id').value='';
	document.getElementById('textbox-projekttask-mantis-issue_view_state_name').value='';
	document.getElementById('textbox-projekttask-mantis-issue_last_updated').value='';
	document.getElementById('textbox-projekttask-mantis-issue_project_id').value='';
    document.getElementById('textbox-projekttask-mantis-issue_project_name').value='';
    document.getElementById('textbox-projekttask-mantis-issue_category').value='';
    document.getElementById('textbox-projekttask-mantis-issue_priority_id').value='';
    document.getElementById('textbox-projekttask-mantis-issue_priority_name').value='';
	document.getElementById('textbox-projekttask-mantis-issue_severity_id').value='';
    document.getElementById('textbox-projekttask-mantis-issue_severity_name').value='';
    document.getElementById('textbox-projekttask-mantis-issue_status_id').value='';
    document.getElementById('textbox-projekttask-mantis-issue_status_name').value='';
    document.getElementById('textbox-projekttask-mantis-issue_reporter_id').value='';
    document.getElementById('textbox-projekttask-mantis-issue_reporter_name').value='';
	document.getElementById('textbox-projekttask-mantis-issue_reporter_real_name').value='';
    document.getElementById('textbox-projekttask-mantis-issue_reporter_email').value='';
    document.getElementById('textbox-projekttask-mantis-issue_reproducibility_id').value='';
    document.getElementById('textbox-projekttask-mantis-issue_reproducibility_name').value='';
	document.getElementById('textbox-projekttask-mantis-issue_date_submitted').value='';
    document.getElementById('textbox-projekttask-mantis-issue_sponsorship_total').value='';
    document.getElementById('textbox-projekttask-mantis-issue_projection_id').value='';
    document.getElementById('textbox-projekttask-mantis-issue_projection_name').value='';
    document.getElementById('textbox-projekttask-mantis-issue_eta_id').value='';
    document.getElementById('textbox-projekttask-mantis-issue_eta_name').value='';
    document.getElementById('textbox-projekttask-mantis-issue_resolution_id').value='';
    document.getElementById('textbox-projekttask-mantis-issue_resolution_name').value='';
    document.getElementById('textbox-projekttask-mantis-issue_due_date').value='';
	document.getElementById('textbox-projekttask-mantis-issue_steps_to_reproduce').value='';
	document.getElementById('textbox-projekttask-mantis-issue_additional_information').value='';
        
    //Mantis
	if (mantis_id!='')
    {
    	var req = new phpRequest('../rdf/mantis.rdf.php','','');
        req.add('issue_id',mantis_id);
        response = req.execute();
        //Datasource holen
        dsource=parseRDFString(response, 'http://www.technikum-wien.at/mantis/alle-issues');
        dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);
        rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
		           getService(Components.interfaces.nsIRDFService);
        subject = rdfService.GetResource("http://www.technikum-wien.at/mantis/" + mantis_id);
        predicateNS = "http://www.technikum-wien.at/mantis/rdf";
    
        //Daten holen
        var issue_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_id" ));
        var issue_summary=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_summary" ));
        var issue_description=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_description" ));
        var issue_view_state_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_view_state_id" ));
        var issue_view_state_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_view_state_name" ));
        var issue_last_updated=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_last_updated" ));
        var issue_project_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_project_id" ));
        var issue_project_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_project_name" ));
        var issue_category=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_category" ));
		var issue_priority_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_priority_id" ));
		var issue_priority_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_priority_name" ));
		var issue_severity_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_severity_id" ));
		var issue_severity_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_severity_name" ));
		var issue_status_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_status_id" ));
		var issue_status_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_status_name" ));
		var issue_reporter_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_reporter_id" ));
		var issue_reporter_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_reporter_name" ));
		var issue_reporter_real_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_reporter_real_name" ));
		var issue_reporter_email=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_reporter_email" ));
		var issue_reproducibility_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_reproducibility_id" ));
		var issue_reproducibility_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_reproducibility_name" ));
		var issue_date_submitted=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_date_submitted" ));
		var issue_sponsorship_total=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_sponsorship_total" ));
		var issue_projection_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_projection_id" ));
		var issue_projection_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_projection_name" ));
		var issue_eta_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_eta_id" ));
		var issue_eta_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_eta_name" ));
		var issue_resolution_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_resolution_id" ));
		var issue_resolution_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_resolution_name" ));
		var issue_due_date=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_due_date" ));			 		 
		var issue_steps_to_reproduce=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_steps_to_reproduce" ));
		var issue_additional_information=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_additional_information" ));
		
		//Daten den Feldern zuweisen
		document.getElementById('textbox-projekttask-mantis-mantis_id').value=mantis_id;
		document.getElementById('textbox-projekttask-mantis-issue_summary').value=issue_summary;
		document.getElementById('textbox-projekttask-mantis-issue_description').value=issue_description;
		document.getElementById('textbox-projekttask-mantis-issue_view_state_id').value=issue_view_state_id;
		document.getElementById('textbox-projekttask-mantis-issue_view_state_name').value=issue_view_state_name;
		document.getElementById('textbox-projekttask-mantis-issue_last_updated').value=issue_last_updated;
		document.getElementById('textbox-projekttask-mantis-issue_project_id').value=issue_project_id;
		document.getElementById('textbox-projekttask-mantis-issue_project_name').value=issue_project_name;
		document.getElementById('textbox-projekttask-mantis-issue_category').value=issue_category;
		document.getElementById('textbox-projekttask-mantis-issue_priority_id').value=issue_priority_id;
		document.getElementById('textbox-projekttask-mantis-issue_priority_name').value=issue_priority_name;
		document.getElementById('textbox-projekttask-mantis-issue_severity_id').value=issue_severity_id;
		document.getElementById('textbox-projekttask-mantis-issue_severity_name').value=issue_severity_name;
		document.getElementById('textbox-projekttask-mantis-issue_status_id').value=issue_status_id;
		document.getElementById('textbox-projekttask-mantis-issue_status_name').value=issue_status_name;
		document.getElementById('textbox-projekttask-mantis-issue_reporter_id').value=issue_reporter_id;
		document.getElementById('textbox-projekttask-mantis-issue_reporter_name').value=issue_reporter_name;
		document.getElementById('textbox-projekttask-mantis-issue_reporter_real_name').value=issue_reporter_real_name;
		document.getElementById('textbox-projekttask-mantis-issue_reporter_email').value=issue_reporter_email;
		document.getElementById('textbox-projekttask-mantis-issue_reproducibility_id').value=issue_reproducibility_id;
		document.getElementById('textbox-projekttask-mantis-issue_reproducibility_name').value=issue_reproducibility_name;
		document.getElementById('textbox-projekttask-mantis-issue_date_submitted').value=issue_date_submitted;
		document.getElementById('textbox-projekttask-mantis-issue_sponsorship_total').value=issue_sponsorship_total;
		document.getElementById('textbox-projekttask-mantis-issue_projection_id').value=issue_projection_id;
		document.getElementById('textbox-projekttask-mantis-issue_projection_name').value=issue_projection_name;
		document.getElementById('textbox-projekttask-mantis-issue_eta_id').value=issue_eta_id;
		document.getElementById('textbox-projekttask-mantis-issue_eta_name').value=issue_eta_name;
		document.getElementById('textbox-projekttask-mantis-issue_resolution_id').value=issue_resolution_id;
		document.getElementById('textbox-projekttask-mantis-issue_resolution_name').value=issue_resolution_name;
		document.getElementById('textbox-projekttask-mantis-issue_due_date').value=issue_due_date;
		document.getElementById('textbox-projekttask-mantis-issue_steps_to_reproduce').value=issue_steps_to_reproduce;
		document.getElementById('textbox-projekttask-mantis-issue_additional_information').value=issue_additional_information;
	}
}

// ****
// * Speichert die Mantis-Details
// ****
function saveProjekttaskMantis()
{
	//Werte holen
	mantis_id = document.getElementById('textbox-projekttask-mantis-mantis_id').value;
	issue_summary=document.getElementById('textbox-projekttask-mantis-issue_summary').value;
	issue_description=document.getElementById('textbox-projekttask-mantis-issue_description').value;
	issue_view_state_id=document.getElementById('textbox-projekttask-mantis-issue_view_state_id').value;
	issue_view_state_name=document.getElementById('textbox-projekttask-mantis-issue_view_state_name').value;
	issue_last_updated=document.getElementById('textbox-projekttask-mantis-issue_last_updated').value;
	issue_project_id=document.getElementById('textbox-projekttask-mantis-issue_project_id').value;
	issue_project_name=document.getElementById('textbox-projekttask-mantis-issue_project_name').value;
	issue_category=document.getElementById('textbox-projekttask-mantis-issue_category').value;
	issue_priority_id=document.getElementById('textbox-projekttask-mantis-issue_priority_id').value;
	issue_priority_name=document.getElementById('textbox-projekttask-mantis-issue_priority_name').value;
	issue_severity_id=document.getElementById('textbox-projekttask-mantis-issue_severity_id').value;
	issue_severity_name=document.getElementById('textbox-projekttask-mantis-issue_severity_name').value;
	issue_status_id=document.getElementById('textbox-projekttask-mantis-issue_status_id').value;
	issue_status_name=document.getElementById('textbox-projekttask-mantis-issue_status_name').value;
	issue_reporter_id=document.getElementById('textbox-projekttask-mantis-issue_reporter_id').value;
	issue_reporter_name=document.getElementById('textbox-projekttask-mantis-issue_reporter_name').value;
	issue_reporter_real_name=document.getElementById('textbox-projekttask-mantis-issue_reporter_real_name').value;
	issue_reporter_email=document.getElementById('textbox-projekttask-mantis-issue_reporter_email').value;
	issue_reproducibility_id=document.getElementById('textbox-projekttask-mantis-issue_reproducibility_id').value;
	issue_reproducibility_name=document.getElementById('textbox-projekttask-mantis-issue_reproducibility_name').value;
	issue_date_submitted=document.getElementById('textbox-projekttask-mantis-issue_date_submitted').value;
	issue_sponsorship_total=document.getElementById('textbox-projekttask-mantis-issue_sponsorship_total').value;
	issue_projection_id=document.getElementById('textbox-projekttask-mantis-issue_projection_id').value;
	issue_projection_name=document.getElementById('textbox-projekttask-mantis-issue_projection_name').value;
	issue_eta_id=document.getElementById('textbox-projekttask-mantis-issue_eta_id').value;
	issue_eta_name=document.getElementById('textbox-projekttask-mantis-issue_eta_name').value;
	issue_resolution_id=document.getElementById('textbox-projekttask-mantis-issue_resolution_id').value;
	issue_resolution_name=document.getElementById('textbox-projekttask-mantis-issue_resolution_name').value;
	issue_due_date=document.getElementById('textbox-projekttask-mantis-issue_due_date').value;
	issue_steps_to_reproduce=document.getElementById('textbox-projekttask-mantis-issue_steps_to_reproduce').value;
	issue_additional_information=document.getElementById('textbox-projekttask-mantis-issue_additional_information').value;

	var soapBody = new SOAPObject("saveMantis");
	soapBody.appendChild(new SOAPObject("mantis_id")).val(mantis_id);
	soapBody.appendChild(new SOAPObject("issue_summary")).val(issue_summary);
	soapBody.appendChild(new SOAPObject("issue_description")).val(issue_description);
	soapBody.appendChild(new SOAPObject("issue_view_state_id")).val(issue_view_state_id);
	soapBody.appendChild(new SOAPObject("issue_view_state_name")).val(issue_view_state_name);
	soapBody.appendChild(new SOAPObject("issue_last_updated")).val(issue_last_updated);
	soapBody.appendChild(new SOAPObject("issue_project_id")).val(issue_project_id);
	soapBody.appendChild(new SOAPObject("issue_project_name")).val(issue_project_name);
	soapBody.appendChild(new SOAPObject("issue_category")).val(issue_category);
	soapBody.appendChild(new SOAPObject("issue_priority_id")).val(issue_priority_id);
	soapBody.appendChild(new SOAPObject("issue_priority_name")).val(issue_priority_name);
	soapBody.appendChild(new SOAPObject("issue_severity_id")).val(issue_severity_id);
	soapBody.appendChild(new SOAPObject("issue_severity_name")).val(issue_severity_name);
	soapBody.appendChild(new SOAPObject("issue_status_id")).val(issue_status_id);
	soapBody.appendChild(new SOAPObject("issue_status_name")).val(issue_status_name);
	soapBody.appendChild(new SOAPObject("issue_reporter_id")).val(issue_reporter_id);
	soapBody.appendChild(new SOAPObject("issue_reporter_name")).val(issue_reporter_name);
	soapBody.appendChild(new SOAPObject("issue_reporter_real_name")).val(issue_reporter_real_name);
	soapBody.appendChild(new SOAPObject("issue_reporter_email")).val(issue_reporter_email);
	soapBody.appendChild(new SOAPObject("issue_reproducibility_id")).val(issue_reproducibility_id);
	soapBody.appendChild(new SOAPObject("issue_reproducibility_name")).val(issue_reproducibility_name);
	soapBody.appendChild(new SOAPObject("issue_date_submitted")).val(issue_date_submitted);
	soapBody.appendChild(new SOAPObject("issue_sponsorship_total")).val(issue_sponsorship_total);
	soapBody.appendChild(new SOAPObject("issue_projection_id")).val(issue_projection_id);
	soapBody.appendChild(new SOAPObject("issue_projection_name")).val(issue_projection_name);
	soapBody.appendChild(new SOAPObject("issue_eta_id")).val(issue_eta_id);
	soapBody.appendChild(new SOAPObject("issue_eta_name")).val(issue_eta_name);
	soapBody.appendChild(new SOAPObject("issue_resolution_id")).val(issue_resolution_id);
	soapBody.appendChild(new SOAPObject("issue_resolution_name")).val(issue_resolution_name);
	soapBody.appendChild(new SOAPObject("issue_due_date")).val(issue_due_date);
	soapBody.appendChild(new SOAPObject("issue_steps_to_reproduce")).val(issue_steps_to_reproduce);
	soapBody.appendChild(new SOAPObject("issue_additional_information")).val(issue_additional_information);
		
	var sr = new SOAPRequest("saveMantis",soapBody);

	SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projekttask.soap.php?"+gettimestamp();
	SOAPClient.SendRequest(sr, clb_saveProjekttaskMantis);
}

// ****
// * Callback Funktion nach Speichern eines Mantis Eitnrags
// ****
function clb_saveProjekttaskMantis(respObj)
{
	try
	{
		var id = respObj.Body[0].saveMantisResponse[0].message[0].Text;
		alert('OK:'+id);
	}
	catch(e)
	{
		var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
		alert('Fehler: '+fehler);
		return;
	}
}
                                