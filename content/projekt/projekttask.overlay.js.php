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
var filterErledigt; //Tasks filtern
var currentProjektPhaseID;
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
// * Laedt dynamisch die Tasks
// ****
function LoadTasks(projekt_phase_id, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	try
		{
			// wenn phase übergeben wurde -> setzte globale variable
			if(projekt_phase_id != null && projekt_phase_id != '' && typeof optional && "undefined")
				currentProjektPhaseID = projekt_phase_id;

			// wenn filter übergeben wurde -> setze globale variable
			if(filter != null && filter != '' && typeof filter != "undefined")
				filterErledigt = filter;

			url = "<?php echo APP_ROOT; ?>rdf/projekttask.rdf.php?projektphase_id="+currentProjektPhaseID+"&"+gettimestamp();

			// überprüfe ob filter gesetzt ist
			if(filterErledigt != null)
				url = "<?php echo APP_ROOT; ?>rdf/projekttask.rdf.php?projektphase_id="+currentProjektPhaseID+"&filter="+filterErledigt+"&"+gettimestamp();

			var treeTask=document.getElementById('projekttask-tree');
			//Alte DS entfernen
			var oldDatasources = treeTask.database.GetDataSources();
			while(oldDatasources.hasMoreElements())
			{
			    treeTask.database.RemoveDataSource(oldDatasources.getNext());
			}

			try
			{
			    datasourceTreeTask.removeXMLSinkObserver(TaskTreeSinkObserver);
			    treeTask.builder.removeListener(TaskTreeListener);
			}
			catch(e)
			{}

			var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
			datasourceTreeTask = rdfService.GetDataSource(url);
			datasourceTreeTask.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
			datasourceTreeTask.QueryInterface(Components.interfaces.nsIRDFXMLSink);
			treeTask.database.AddDataSource(datasourceTreeTask);
			datasourceTreeTask.addXMLSinkObserver(TaskTreeSinkObserver);
			treeTask.builder.addListener(TaskTreeListener);

		}
		catch(e)
		{
		    debug("whoops Projekttask load failed with exception: "+e);
		}
}


// ****
// * Laedt dynamisch die Personen fuer das DropDown Menue
// ****
function RessourceTaskLoad(menulist, id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	var url = '<?php echo APP_ROOT; ?>rdf/ressource.rdf.php?projekt_phase='+id+'&optional&'+gettimestamp();
	//nurmittitel=&
	var oldDatasources = menulist.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		menulist.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	menulist.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	//if(typeof(filter)=='undefined')
	//	var datasource = rdfService.GetDataSource(url);
	//else

	var datasource = rdfService.GetDataSourceBlocking(url);

	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	menulist.database.AddDataSource(datasource);
	menulist.builder.rebuild();

}


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
	var tasktree=document.getElementById('projekttask-tree');
	tasktree.view.selection.clearSelection();
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
	TaskDisableFields(false);

	document.getElementById('textbox-projekttaskdetail-projektphase_id').value=projektphase_id;
	document.getElementById('caption-projekttask-detail').label='Neuer Task';

	//Detail Tab auswaehlen
	document.getElementById('projekttask-tabs').selectedItem=document.getElementById('projekttask-tab-detail');
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
	document.getElementById('textbox-projekttask-detail-scrumsprint_id').value='';
	MenulistSelectItemOnValue('textbox-projekttask-detail-ressource', '');
	document.getElementById('textbox-projekttask-detail-ende').value='';
}

// ****
// * Deaktiviert alle Eingabe- und Auswahlfelder
// ****
function TaskDisableFields(val)
{
	//document.getElementById('textbox-projekttaskdetail-projekttask_id').disabled=val;
	//document.getElementById('textbox-projekttaskdetail-projektphase_id').disabled=val;
	document.getElementById('textbox-projekttask-detail-bezeichnung').disabled=val;
	document.getElementById('textbox-projekttask-detail-beschreibung').disabled=val;
	document.getElementById('textbox-projekttask-detail-aufwand').disabled=val;
	document.getElementById('textbox-projekttask-detail-mantis_id').disabled=val;
	document.getElementById('textbox-projekttask-detail-scrumsprint_id').disabled=val;
	document.getElementById('textbox-projekttask-detail-ressource').disabled=val;
	document.getElementById('textbox-projekttask-detail-ende').disabled=val;
	document.getElementById('button-projekttask-detail-speichern').disabled=val;
}

// ****
// * Zeigt Vorschau der Details
// ****
function showProjekttaskParsedown()
{

	//Werte holen
	projekttask_id = document.getElementById('textbox-projekttaskdetail-projekttask_id').value;

	if(!isNaN(projekttask_id) && projekttask_id != '')
	{
		window.open("projekt/parsedown.php?projekttask_id="+projekttask_id,"Projekttask"+projekttask_id);
	}
	else
		alert('keine gueltige ProjekttaskID eingetragen');
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
	scrumsprint_id = document.getElementById('textbox-projekttask-detail-scrumsprint_id').value;
	ressource_id = MenulistGetSelectedValue('textbox-projekttask-detail-ressource');
	ende = document.getElementById('textbox-projekttask-detail-ende').iso;

	if(!isNaN(projektphase_id) && projektphase_id != '')
	{
		var soapBody = new SOAPObject("saveProjekttask");
		//soapBody.appendChild(new SOAPObject("username")).val('joe');
		//soapBody.appendChild(new SOAPObject("passwort")).val('waschl');

		var task = new SOAPObject("task");
		task.appendChild(new SOAPObject("projekttask_id")).val(projekttask_id);
		task.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
		task.appendChild(new SOAPObject("bezeichnung")).cdataval(bezeichnung);
		task.appendChild(new SOAPObject("beschreibung")).cdataval(beschreibung);
		task.appendChild(new SOAPObject("aufwand")).val(aufwand);
		task.appendChild(new SOAPObject("mantis_id")).val(mantis_id);
		task.appendChild(new SOAPObject("scrumsprint_id")).val(scrumsprint_id);
		task.appendChild(new SOAPObject("user")).val(getUsername());
		task.appendChild(new SOAPObject("ressource_id")).val(ressource_id);
		task.appendChild(new SOAPObject("ende")).val(ende);
		soapBody.appendChild(task);

		var sr = new SOAPRequest("saveProjekttask",soapBody);

		SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projekttask.soap.php?"+gettimestamp();
		SOAPClient.SendRequest(sr, clb_saveProjekttask);
	}else
	alert('keine gueltige Projektphase_ID eingetragen');
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
                document.getElementById('caption-projekttask-detail').label='Bearbeiten';
                TaskDisableFields(false);
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
	var scrumsprint_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#scrumsprint_id" ));
	var ressource_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ressource_id" ));
	var ende=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ende" ));

	//Daten den Feldern zuweisen
	var menulist = document.getElementById('textbox-projekttask-detail-ressource');
	RessourceTaskLoad(menulist, projektphase_id);
	document.getElementById('textbox-projekttaskdetail-projekttask_id').value=projekttask_id;
	document.getElementById('textbox-projekttask-detail-ende').value=ende;
	document.getElementById('textbox-projekttaskdetail-projektphase_id').value=projektphase_id;
	document.getElementById('textbox-projekttask-detail-bezeichnung').value=bezeichnung;
	document.getElementById('textbox-projekttask-detail-beschreibung').value=beschreibung;
	document.getElementById('textbox-projekttask-detail-aufwand').value=aufwand;
	document.getElementById('textbox-projekttask-detail-mantis_id').value=mantis_id;
	document.getElementById('textbox-projekttask-detail-scrumsprint_id').value=scrumsprint_id;
	MenulistSelectItemOnValue('textbox-projekttask-detail-ressource', ressource_id);
	//document.getElementById('textbox-projekttask-detail-ressource').value=ressource_id;

	 //Notizen zu eines Tasks Laden
	notiz = document.getElementById('box-projekttask-notizen');
	notiz.LoadNotizTree('','',projekttask_id,'','','','', '','');

	//Mantis Tab reset
	document.getElementById('textbox-projekttask-mantis-issue_summary').value=bezeichnung;
	document.getElementById('textbox-projekttask-mantis-issue_description').value=beschreibung;
	//document.getElementById('textbox-projekttask-mantis-issue_project_id').value='1';
    //document.getElementById('textbox-projekttask-mantis-issue_category').value='General';

	document.getElementById('textbox-projekttask-mantis-mantis_id').value='';
	document.getElementById('textbox-projekttask-mantis-issue_view_state_id').value='';
	document.getElementById('textbox-projekttask-mantis-issue_view_state_name').value='';
	document.getElementById('textbox-projekttask-mantis-issue_last_updated').value='';
    document.getElementById('textbox-projekttask-mantis-issue_project_name').value='';
    //document.getElementById('menulist-projekttask-mantis-issue_priority_id').value='';
    document.getElementById('textbox-projekttask-mantis-issue_priority_name').value='';
	document.getElementById('textbox-projekttask-mantis-issue_severity_id').value='';
    document.getElementById('textbox-projekttask-mantis-issue_severity_name').value='';
    //document.getElementById('menulist-projekttask-mantis-issue_status_id').value='';
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
	if (false && mantis_id!='')
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
		var issue_tags_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_tags_name" ));
		var issue_resolution_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_resolution_id" ));
		var issue_resolution_name=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_resolution_name" ));
		var issue_due_date=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_due_date" ));
		var issue_steps_to_reproduce=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_steps_to_reproduce" ));
		var issue_additional_information=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#issue_additional_information" ));

		ProjekttaskLoadCategories(issue_project_id);

		//Daten den Feldern zuweisen
		document.getElementById('textbox-projekttask-mantis-mantis_id').value=mantis_id;
		document.getElementById('textbox-projekttask-mantis-issue_summary').value=issue_summary;
		document.getElementById('textbox-projekttask-mantis-issue_description').value=issue_description;
		document.getElementById('textbox-projekttask-mantis-issue_view_state_id').value=issue_view_state_id;
		document.getElementById('textbox-projekttask-mantis-issue_view_state_name').value=issue_view_state_name;
		document.getElementById('textbox-projekttask-mantis-issue_last_updated').value=issue_last_updated;
		document.getElementById('menulist-projekttask-mantis-issue_project_id').value=issue_project_id;
		document.getElementById('textbox-projekttask-mantis-issue_project_name').value=issue_project_name;
		document.getElementById('menulist-projekttask-mantis-issue_category').value=issue_category;
		document.getElementById('menulist-projekttask-mantis-issue_priority_id').value=issue_priority_id;
		document.getElementById('textbox-projekttask-mantis-issue_priority_name').value=issue_priority_name;
		document.getElementById('textbox-projekttask-mantis-issue_severity_id').value=issue_severity_id;
		document.getElementById('textbox-projekttask-mantis-issue_severity_name').value=issue_severity_name;
		document.getElementById('menulist-projekttask-mantis-issue_status_id').value=issue_status_id;
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
		document.getElementById('textbox-projekttask-mantis-issue_tags').value=issue_tags_name;
	}
}

// ****
// * Beim Aendern des Mantis Projekts werden die zugehoerigen
// * Kategorien geladen
// ****
function ProjekttaskMantisProjektChange()
{
	project_id=document.getElementById('menulist-projekttask-mantis-issue_project_id').value;

	if(project_id!='')
		ProjekttaskLoadCategories(project_id);
}

// ****
// * Laedt die Mantis Kategorien (Blocking)
// * @param project_id Mantis Projekt ID
// ****
function ProjekttaskLoadCategories(project_id)
{
	if(project_id!='')
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		//Kategorien zu diesem Projekt laden
		menulist = document.getElementById('menulist-projekttask-mantis-issue_category');
		var url = '<?php echo APP_ROOT; ?>rdf/mantis_categories.rdf.php?project_id='+project_id+'&'+gettimestamp();
		var oldDatasources = menulist.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			menulist.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		menulist.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		var datasource = rdfService.GetDataSourceBlocking(url);

		datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		menulist.database.AddDataSource(datasource);
		menulist.builder.rebuild();
	}
}

// ****
// * Speichert die Mantis-Details
// ****
function saveProjekttaskMantis()
{
	//Werte holen
	var projekttask_id = document.getElementById('textbox-projekttaskdetail-projekttask_id').value;
	var mantis_id = document.getElementById('textbox-projekttask-mantis-mantis_id').value;
	var issue_summary=document.getElementById('textbox-projekttask-mantis-issue_summary').value;
	var issue_description=document.getElementById('textbox-projekttask-mantis-issue_description').value;
	var issue_view_state_id=document.getElementById('textbox-projekttask-mantis-issue_view_state_id').value;
	var issue_view_state_name=document.getElementById('textbox-projekttask-mantis-issue_view_state_name').value;
	var issue_last_updated=document.getElementById('textbox-projekttask-mantis-issue_last_updated').value;
	var issue_project_id=document.getElementById('menulist-projekttask-mantis-issue_project_id').value;
	var issue_project_name=document.getElementById('textbox-projekttask-mantis-issue_project_name').value;
	var issue_category=document.getElementById('menulist-projekttask-mantis-issue_category').value;
	var issue_priority_id=document.getElementById('menulist-projekttask-mantis-issue_priority_id').value;
	var issue_priority_name=document.getElementById('textbox-projekttask-mantis-issue_priority_name').value;
	var issue_severity_id=document.getElementById('textbox-projekttask-mantis-issue_severity_id').value;
	var issue_severity_name=document.getElementById('textbox-projekttask-mantis-issue_severity_name').value;
	var issue_status_id=document.getElementById('menulist-projekttask-mantis-issue_status_id').value;
	var issue_status_name=document.getElementById('textbox-projekttask-mantis-issue_status_name').value;
	var issue_reporter_id=document.getElementById('textbox-projekttask-mantis-issue_reporter_id').value;
	var issue_reporter_name=document.getElementById('textbox-projekttask-mantis-issue_reporter_name').value;
	var issue_reporter_real_name=document.getElementById('textbox-projekttask-mantis-issue_reporter_real_name').value;
	var issue_reporter_email=document.getElementById('textbox-projekttask-mantis-issue_reporter_email').value;
	var issue_reproducibility_id=document.getElementById('textbox-projekttask-mantis-issue_reproducibility_id').value;
	var issue_reproducibility_name=document.getElementById('textbox-projekttask-mantis-issue_reproducibility_name').value;
	var issue_date_submitted=document.getElementById('textbox-projekttask-mantis-issue_date_submitted').value;
	var issue_sponsorship_total=document.getElementById('textbox-projekttask-mantis-issue_sponsorship_total').value;
	var issue_projection_id=document.getElementById('textbox-projekttask-mantis-issue_projection_id').value;
	var issue_projection_name=document.getElementById('textbox-projekttask-mantis-issue_projection_name').value;
	var issue_eta_id=document.getElementById('textbox-projekttask-mantis-issue_eta_id').value;
	var issue_eta_name=document.getElementById('textbox-projekttask-mantis-issue_eta_name').value;
	var issue_resolution_id=document.getElementById('textbox-projekttask-mantis-issue_resolution_id').value;
	var issue_resolution_name=document.getElementById('textbox-projekttask-mantis-issue_resolution_name').value;
	var issue_due_date=document.getElementById('textbox-projekttask-mantis-issue_due_date').value;
	var issue_steps_to_reproduce=document.getElementById('textbox-projekttask-mantis-issue_steps_to_reproduce').value;
	var issue_additional_information=document.getElementById('textbox-projekttask-mantis-issue_additional_information').value;
	var issue_tags = document.getElementById('textbox-projekttask-mantis-issue_tags').value;

	var soapBody = new SOAPObject("saveMantis");
	soapBody.appendChild(new SOAPObject("projekttask_id")).val(projekttask_id);
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

	// Tags speichern
	if(mantis_id != '')
	{
		var soapBody = new SOAPObject("saveTagsForIssue");
		soapBody.appendChild(new SOAPObject("mantis_id")).val(mantis_id);
		soapBody.appendChild(new SOAPObject("issue_tags")).val(issue_tags);

		var sr = new SOAPRequest("saveTagsForIssue",soapBody);

		SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projekttask.soap.php?"+gettimestamp();
		SOAPClient.SendRequest(sr, clb_saveProjekttaskMantis);
	}

}

// ****
// * Callback Funktion nach Speichern eines Mantis Eitnrags
// ****
function clb_saveProjekttaskMantis(respObj)
{
	try
	{
		var id = respObj.Body[0].saveMantisResponse[0].message[0].Text;
		TaskTreeRefresh()
	}
	catch(e)
	{
		var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
		alert('Fehler: '+fehler);
		return;
	}
}

// ****
// * Aktualisiert den Erledigt Status eines Projekttasks
// ****
function ProjekttaskUpdateErledigt(event)
{
	var row = new Object();
    var col = new Object();
    var childElt = new Object();
    //Tree holen
    var tree = event.currentTarget;
    //Treecol ermitteln in die geklickt wurde
    tree.treeBoxObject.getCellAt(event.clientX, event.clientY, row, col, childElt);
    //abbrechen wenn auf Header oder Scrollbar geklickt wurde
    if(!col.value)
    	return 0;

	var val = tree.view.getCellValue(row.value, col.value);
	var text = tree.view.getCellText(row.value, col.value);

	col = tree.columns ? tree.columns['projekttask-treecol-projekttask_id'] : 'projekttask-treecol-projekttask_id';
	var id = tree.view.getCellText(row.value, col);

	if(text=='erledigt')
	{
		if(val=='true')
			newval='false';
		else
			newval='true';

		var soapBody = new SOAPObject("setErledigt");
		//soapBody.appendChild(new SOAPObject("username")).val('joe');
		//soapBody.appendChild(new SOAPObject("passwort")).val('waschl');
	    soapBody.appendChild(new SOAPObject("projekttask_id")).val(id);
	    soapBody.appendChild(new SOAPObject("erledigt")).val(newval);

	    var sr = new SOAPRequest("setErledigt",soapBody);
	    SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projekttask.soap.php?"+gettimestamp();

    	SOAPClient.SendRequest(sr,function (respObj)
    	{
	    	try
			{
				var id = respObj.Body[0].setErledigtResponse[0].message[0].Text;
				TaskTreeRefresh();
			}
			catch(e)
			{
				var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
				alert('Fehler: '+fehler);
				return;
			}
		});
	}
}

/***** Drag Observer Tasks verschieben *****/
var taskDDObserver=
{
	onDragStart: function (evt,transferData,action)
	{
		var tree = document.getElementById('projekttask-tree')
	    var row = { }
	    var col = { }
	    var child = { }

	    //Index der Quell-Row ermitteln
	    tree.treeBoxObject.getCellAt(evt.pageX, evt.pageY, row, col, child)

	    //Beim Scrollen soll kein DnD gemacht werden
	    if(col.value==null)
	    	return false;

	    //Daten ermitteln
	    col = tree.columns ? tree.columns["projekttask-treecol-projekttask_id"] : "projekttask-treecol-projekttask_id";
		projekttaskID=tree.view.getCellText(row.value,col);

		var paramList= projekttaskID
		//debug('param:'+paramList);
		transferData.data=new TransferData();
		transferData.data.addDataForFlavour("application/taskID",paramList);


  	}
};
