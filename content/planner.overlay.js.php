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
include('../config/vilesci.config.inc.php');
?>

var datasourceTreeProjekt;
var datasourceTreeProjektphase;
var datasourceTreeProjekttask;
var datasourceTreeDokument;
var datasourceTreeBestellung;
var global_filter = '';

function treeProjektmenueSelect()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('tree-projektmenue');

	// Wenn auf die Ueberschrift geklickt wird, soll nix passieren
	if(tree.currentIndex==-1)
		return;

	var bezeichnung = getTreeCellText(tree, "treecol-projektmenue-bezeichnung", tree.currentIndex);
	var oe=getTreeCellText(tree, "treecol-projektmenue-oe", tree.currentIndex);
	var projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
	var projekt_phase=getTreeCellText(tree, "treecol-projektmenue-projekt_phase", tree.currentIndex);
	var projekt_phase_id=getTreeCellText(tree, "treecol-projektmenue-projekt_phase_id", tree.currentIndex);


	//Neu und Delete Button fuer Projekte und Phasen aktivieren/deaktivieren
	if (projekt_kurzbz=='')
	{
    	document.getElementById('toolbarbutton-projekt-neu').disabled=false;
		document.getElementById('toolbarbutton-projektphase-neu').disabled=true;
	}
	else
	{
		document.getElementById('toolbarbutton-projekt-neu').disabled=true;
		document.getElementById('toolbarbutton-projektphase-neu').disabled=false;
	}

    //Projekte neu laden
	try
	{
		// Wenn eine OE angeklickt wird, den Tab Projekte anzeigen
		if(oe!='' && projekt_kurzbz=='' && projekt_phase_id=='')
		{
			// Wenn der Ressourceauslastung Tab geoeffnet ist
			if(document.getElementById('tabs-planner-main').selectedItem==document.getElementById('tab-ressourceauslastung'))
			{
				// und dort der Projekttask Tab geoffnet ist, dann die Anzeige dort neu laden
				if(document.getElementById('ressource-tabs').selectedItem==document.getElementById('tab-ressource-projekttask'))
				{
					reloadRessourceTasks();
				}
				if(document.getElementById('ressource-tabs').selectedItem==document.getElementById('tab-ressource-projektphase'))
				{
					// wenn der Phasen Karteireiter offen ist werden die Phasen dort neu geladen
					reloadRessourcePhasen();
				}
			}
			else
			{
				// Wenn der Tab Phase oder Tasks ausgewaehlt ist auf die Projekte wechseln
				if(document.getElementById('tabs-planner-main').selectedItem==document.getElementById('tab-projektphase')
				|| document.getElementById('tabs-planner-main').selectedItem==document.getElementById('tab-projekttask'))
				{
					document.getElementById('tabs-planner-main').selectedItem=document.getElementById('tab-projekte');
				}
			}
		}

		var datasource="<?php echo APP_ROOT; ?>rdf/projekt.rdf.php?oe="+oe+"&filter="+global_filter+"&"+gettimestamp();
		var treeProjekt=document.getElementById('tree-projekt');
		//Alte DS entfernen
		var oldDatasources = treeProjekt.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			treeProjekt.database.RemoveDataSource(oldDatasources.getNext());
		}

        try
        {
        	datasourceTreeProjekt.removeXMLSinkObserver(observerTreeProjekt);
			treeProjekt.builder.removeListener(listenerTreeProjekt);
		}
        catch(e)
        {}

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		datasourceTreeProjekt = rdfService.GetDataSource(datasource);
		datasourceTreeProjekt.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasourceTreeProjekt.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		treeProjekt.database.AddDataSource(datasourceTreeProjekt);
		datasourceTreeProjekt.addXMLSinkObserver(observerTreeProjekt);
		treeProjekt.builder.addListener(listenerTreeProjekt);
	}
    catch(e)
    {
		debug("whoops Projekt load failed with exception: "+e);
	}

    // Projektphasen neu laden
	if(projekt_kurzbz!='')
	{
		// Wenn ein Projekt angeklickt wird, ggf Karteireiter wechseln

		// Wenn der Ressourceauslastung Tab geoeffnet ist
		if(document.getElementById('tabs-planner-main').selectedItem==document.getElementById('tab-ressourceauslastung'))
		{
			// und dort der Projekttask Tab geoffnet ist, dann die Anzeige dort neu laden
			if(document.getElementById('ressource-tabs').selectedItem==document.getElementById('tab-ressource-projekttask'))
			{
				reloadRessourceTasks();
			}
			if(document.getElementById('ressource-tabs').selectedItem==document.getElementById('tab-ressource-projektphase'))
			{
				// wenn der Phasen Karteireiter offen ist werden die Phasen dort neu geladen
				reloadRessourcePhasen();
			}
		}
		else
		{
			// Wenn der Tab Projekte oder Tasks ausgewaehlt ist auf die Phasen wechseln
			if(document.getElementById('tabs-planner-main').selectedItem==document.getElementById('tab-projekte')
			|| document.getElementById('tabs-planner-main').selectedItem==document.getElementById('tab-projekttask'))
			{
				document.getElementById('tabs-planner-main').selectedItem=document.getElementById('tab-projektphase');
			}
		}

	    try
		{
			var datasources="<?php echo APP_ROOT; ?>rdf/projektphase.rdf.php?"+gettimestamp();
			datasources = datasources + "&filterprj=" + encodeURIComponent(projekt_kurzbz);
			var ref="http://www.technikum-wien.at/projektphase/"+oe+"/"+projekt_kurzbz;
			var treePhase=document.getElementById('tree-projektphase');

			//Alte DS entfernen
			var oldDatasources = treePhase.database.GetDataSources();
			while(oldDatasources.hasMoreElements())
			{
			    treePhase.database.RemoveDataSource(oldDatasources.getNext());
			}

			try
			{
			    datasourceTreeProjektphase.removeXMLSinkObserver(observerTreeProjektphase);
			    treePhase.builder.removeListener(ProjektphaseTreeListener);
			}
			catch(e)
			{}

			var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
			datasourceTreeProjektphase = rdfService.GetDataSource(datasources);
			datasourceTreeProjektphase.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
			datasourceTreeProjektphase.QueryInterface(Components.interfaces.nsIRDFXMLSink);
			treePhase.database.AddDataSource(datasourceTreeProjektphase);
			datasourceTreeProjektphase.addXMLSinkObserver(observerTreeProjektphase);
			treePhase.builder.addListener(ProjektphaseTreeListener);
			treePhase.ref=ref;
		}
		catch(e)
		{
			debug("whoops Projektphase load failed with exception: "+e);
		}
	}

	// Projekttasks neu laden
	if(projekt_phase_id!='')
	{
		// Wenn eine Phase angeklickt wird, den Task Karteireiter anzeigen wenn projekt oder phasen tab geoeffnet ist
		if(document.getElementById('tabs-planner-main').selectedItem==document.getElementById('tab-projekte')
		|| document.getElementById('tabs-planner-main').selectedItem==document.getElementById('tab-projektphase'))
		{
			document.getElementById('tabs-planner-main').selectedItem=document.getElementById('tab-projekttask');
		}
	    LoadTasks(projekt_phase_id);
	}

	document.getElementById('projekttask-toolbar-del').disabled=true;


	// Dokumente laden
	if(projekt_phase_id!='' || projekt_kurzbz!='')
	{
		document.getElementById('toolbarbutton-projektdokument-neu').disabled=false;
		document.getElementById('toolbarbutton-projektdokument-zuweisung').disabled=false;
		try
		{

			if(projekt_phase_id!='')
				url = "<?php echo APP_ROOT; ?>rdf/dms.rdf.php?projektphase_id="+projekt_phase_id+"&"+gettimestamp();
	        else if(projekt_kurzbz!='')
	        	url = "<?php echo APP_ROOT; ?>rdf/dms.rdf.php?projekt_kurzbz="+projekt_kurzbz+"&"+gettimestamp();

	        var treeDokument=document.getElementById('tree-projektdokument');

	        //Alte DS entfernen
			var oldDatasources = treeDokument.database.GetDataSources();
			while(oldDatasources.hasMoreElements())
	        {
	        	treeDokument.database.RemoveDataSource(oldDatasources.getNext());
			}

			try
			{
				datasourceTreeDokument.removeXMLSinkObserver(DokumentTreeSinkObserver);
				treeDokument.builder.removeListener(DokumentTreeListener);
			}
	        catch(e)
	        {}
	        treeDokument.builder.rebuild();

	        var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	        datasourceTreeDokument = rdfService.GetDataSource(url);
	        datasourceTreeDokument.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	        datasourceTreeDokument.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	        treeDokument.database.AddDataSource(datasourceTreeDokument);
	        datasourceTreeDokument.addXMLSinkObserver(DokumentTreeSinkObserver);
	        treeDokument.builder.addListener(DokumentTreeListener);
		}
		catch(e)
		{
			debug("whoops Documents load failed with exception: "+e);
		}
		// Gantt Chart laden
		drawGantt();
	}
	else
	{
		document.getElementById('toolbarbutton-projektdokument-neu').disabled=true;
		document.getElementById('toolbarbutton-projektdokument-zuweisung').disabled=true;
		drawGantt();
	}




	// Bestellung laden
	if(projekt_phase_id=='' && projekt_kurzbz!='')
	{
		try
		{
	        url = "<?php echo APP_ROOT; ?>rdf/bestellung.rdf.php?projektKurzbz="+projekt_kurzbz+"&"+gettimestamp();
	        var treeBestellung=document.getElementById('tree-bestellung');

	        //Alte DS entfernen
			var oldDatasources = treeBestellung.database.GetDataSources();
			while(oldDatasources.hasMoreElements())
	        {
	        	treeBestellung.database.RemoveDataSource(oldDatasources.getNext());
			}

			try
			{
				datasourceTreeBestellung.removeXMLSinkObserver(BestellungTreeSinkObserver);
				treeBestellung.builder.removeListener(BestellungTreeListener);
			}
	        catch(e)
	        {}

	        var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	        datasourceTreeBestellung = rdfService.GetDataSource(url);
	        datasourceTreeBestellung.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	        datasourceTreeBestellung.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	        treeBestellung.database.AddDataSource(datasourceTreeBestellung);
	        datasourceTreeBestellung.addXMLSinkObserver(BestellungTreeSinkObserver);
	        treeBestellung.builder.addListener(BestellungTreeListener);
		}
		catch(e)
		{
			debug("whoops bestellung load failed with exception: "+e);
		}
	}


	if(projekt_kurzbz!='')
	{
		//Neu Button bei Tasks aktivieren
		document.getElementById('projekttask-toolbar-neu').disabled=false;
	}
	else
	{
		document.getElementById('projekttask-toolbar-neu').disabled=true;
		document.getElementById('projekttask-toolbar-del').disabled=true;
	}
}

// ****
// * Dialog fuer neue Ressource starten
// ****
function RessourceNeu()
{
    // netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect"); // Trick 17
    var tree=document.getElementById('tree-ressourcemenue');
   // var oe=getTreeCellText(tree, "treecol-ressourcemenue-oe", tree.currentIndex);
    window.open('<?php echo APP_ROOT; ?>content/projekt/ressource.window.xul.php','Neue Ressource anlegen', 'height=384,width=512,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no');
    //alert (oe);
}

// *****
// * Refresht den Projektmenue Tree
// *****
function ProjektmenueRefresh(filter)
{
	if(global_filter==undefined || global_filter=='')
		global_filter='alle';
	if(filter==undefined)
		filter=global_filter;

	global_filter=filter;
	try
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		if(filter=='alle')
			url = "<?php echo APP_ROOT; ?>rdf/projektphase.rdf.php?"+gettimestamp();
		else
			url = "<?php echo APP_ROOT; ?>rdf/projektphase.rdf.php?filter="+global_filter+"&"+gettimestamp();

		var treeProjektmenue=document.getElementById('tree-projektmenue');

		//Alte DS entfernen
		var oldDatasources = treeProjektmenue.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			treeProjektmenue.database.RemoveDataSource(oldDatasources.getNext());
		}
		treeProjektmenue.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		datasourceTreeProjektmenue = rdfService.GetDataSource(url);
		datasourceTreeProjektmenue.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		treeProjektmenue.database.AddDataSource(datasourceTreeProjektmenue);
		if(global_filter=='aktuell')
          SetStatusBarText('Filter: Aktuelle Projekte');
		else if(global_filter=='kommende')
          SetStatusBarText('Filter: Aktuelle und Kommende Projekte');
		else
          SetStatusBarText('Filter: Alle Projekte');
	}
	catch(e)
	{
		debug("whoops Projektmenue load failed with exception: "+e);
	}
	ProjektTreeRefresh();
}

function loadURL(event)
{
        var contentFrame = document.getElementById('contentFrame');
        var url = event.target.getAttribute('value');

        if (url) contentFrame.setAttribute('src', url);
};

function parseRDFString(str, url)
{

	try
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	}
	catch(e)
	{
		alert(e);
		return;
	}

  var memoryDS = Components.classes["@mozilla.org/rdf/datasource;1?name=in-memory-datasource"].createInstance(Components.interfaces.nsIRDFDataSource);

  var ios=Components.classes["@mozilla.org/network/io-service;1"].getService(Components.interfaces.nsIIOService);
  baseUri=ios.newURI(url,null,null);

  var parser=Components.classes["@mozilla.org/rdf/xml-parser;1"].createInstance(Components.interfaces.nsIRDFXMLParser);
  parser.parseString(memoryDS,baseUri,str);

  return memoryDS;
}


/***** Drag Observer fuer Tasks verschieben *****/
var projektTaskDDObserver=
{
	getSupportedFlavours : function ()
	{
  	  	var flavours = new FlavourSet();
  	  	flavours.appendFlavour("application/taskID");
  	  	return flavours;
  	},
  	onDragEnter: function (evt,flavour,session)
	{
	},
	onDragExit: function (evt,flavour,session)
	{
  	},
  	onDragOver: function(evt,flavour,session)
  	{
  	},
  	onDrop: function (evt,dropdata,session)
  	{
	    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	    try
	    {
	        dragservice_ds = Components.classes["@mozilla.org/widget/dragservice;1"].getService(Components.interfaces.nsIDragService);
	    }
	    catch (e)
	    {
	    	debug('treeDragDrop: e');
	    }

	    var ds = dragservice_ds;

		var tree = document.getElementById('tree-projektmenue')
	    var row = { }
	    var col = { }
	    var child = { }

	    tree.treeBoxObject.getCellAt(evt.pageX, evt.pageY, row, col, child)

	    	col = tree.columns ? tree.columns["treecol-projektmenue-projekt_phase_id"] : "treecol-projektmenue-projekt_phase_id";
			projektphaseID=tree.view.getCellText(row.value,col);

		if(projektphaseID == '')
		{
			alert('keine phase ausgewählt!');
			return false;
		}
		var projekttask_id = dropdata.data;

		var soapBody = new SOAPObject("changeProjektPhase");
		//soapBody.appendChild(new SOAPObject("username")).val('joe');
		//soapBody.appendChild(new SOAPObject("passwort")).val('waschl');
		soapBody.appendChild(new SOAPObject("projekttask_id")).val(projekttask_id);
		soapBody.appendChild(new SOAPObject("projektphase_id")).val(projektphaseID);

		var sr = new SOAPRequest("changeProjektPhase",soapBody);

		SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projekttask.soap.php?"+gettimestamp();
		SOAPClient.SendRequest(sr, clb_changePhaseTask);
  	}
};

// ****
// * Callback Funktion nach ändern einer Phase
// ****
function clb_changePhaseTask(respObj)
{
	try
	{
		var id = respObj.Body[0].changeProjektPhaseResponse[0].message[0].Text;
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
	TaskTreeRefresh()
	SetStatusBarText('Daten wurden gespeichert');
}
