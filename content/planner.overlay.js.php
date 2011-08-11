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

function treeProjektSelect()
{
	//document.getElementById('tempus-lva-filter').value='';
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	//var contentFrame=document.getElementById('iframeTimeTableWeek');
	var tree=document.getElementById('tree-projektmenue');
	
	// Wenn auf die Ueberschrift geklickt wird, soll nix passieren
        if(tree.currentIndex==-1)
            return;
	
	var bezeichnung = getTreeCellText(tree, "treecol-projektmenue-bezeichnung", tree.currentIndex);
	var oe=getTreeCellText(tree, "treecol-projektmenue-oe", tree.currentIndex);
	var projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
	var projekt_phase=getTreeCellText(tree, "treecol-projektmenue-projekt_phase", tree.currentIndex);
	var projekt_phase_id=getTreeCellText(tree, "treecol-projektmenue-projekt_phase_id", tree.currentIndex);
	    
	//alert("Projekt Phase ID "+projekt_phase_id);
        
        // Neu und Delete Button fuer Projekte und Phasen aktivieren/deaktivieren
        if (projekt_kurzbz=='')
        {
            document.getElementById('toolbarbutton-projektmenue-neu').disabled=false;
            document.getElementById('toolbarbutton-projektphase-neu').disabled=true;
        }
        else
        {
            document.getElementById('toolbarbutton-projektmenue-neu').disabled=true;
            document.getElementById('toolbarbutton-projektphase-neu').disabled=false;
        }
        
        // Projekte neu laden
	if(projekt_phase_id=='' && projekt_kurzbz=='')
        {
            try
            {
                var datasource="<?php echo APP_ROOT; ?>rdf/projekt.rdf.php?oe="+oe+"&foo=<?php echo time(); ?>";
                //alert("OE "+oe+" | Projekt KurzBZ "+projekt_kurzbz+" | Datasource "+datasource);
                var treeProjekt=document.getElementById('tree-projekt');
                //treeProjekt.datasources=datasource;
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
        }
        
        // Projektphasen neu laden
	if(projekt_phase_id=='' && projekt_kurzbz!='')
	{
            //alert("OE "+oe+" | Projekt KurzBZ "+projekt_kurzbz);
	    try
            {
                var datasources="<?php echo APP_ROOT; ?>rdf/projektphase.rdf.php?"+gettimestamp();
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
                    treePhase.builder.removeListener(TaskTreeListener);
                }
                catch(e)
                {}
                
                var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
                datasourceTreeTask = rdfService.GetDataSource(datasources);
                datasourceTreeTask.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
                datasourceTreeTask.QueryInterface(Components.interfaces.nsIRDFXMLSink);
                treePhase.database.AddDataSource(datasourceTreeTask);
                datasourceTreeTask.addXMLSinkObserver(observerTreeProjektphase);
                treePhase.builder.addListener(TaskTreeListener);
                treePhase.ref=ref;
            }
            catch(e)
            {
                    debug("whoops Projekttask load failed with exception: "+e);
            }
	}
        
        // Projekttasks neu laden
	if(projekt_phase_id!='')
	{
	    try
            {
                url = "<?php echo APP_ROOT; ?>rdf/projekttask.rdf.php?projektphase_id="+projekt_phase_id+"&"+gettimestamp();
                
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
	
	document.getElementById('projekttask-toolbar-del').disabled=true;
	
	
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

// Dialog fuer neues Projekt starten
function ProjektNeu()
{
    var tree=document.getElementById('tree-projekt');
    var oe=getTreeCellText(tree, "tree-projekt-oe", tree.currentIndex);
    window.open('<?php echo APP_ROOT; ?>content/projekt/projekt.window.xul.php?oe='+oe,'Projekt anlegen', 'height=384,width=512,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no');
    //alert (oe);
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
