<?php 
require_once('../../config/vilesci.config.inc.php');
?>

 
 
var datasourceTreeRessource; 
var selectIDRessource;

function treeRessourcemenueSelect()
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
            try
            {
                var datasource="<?php echo APP_ROOT; ?>rdf/projekt.rdf.php?oe="+oe+"&"+gettimestamp();
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


// ****
// * Speichern der Daten
// ****
function saveRessource()
{
    var bezeichnung=document.getElementById('textbox-ressource-bezeichnung').value;
    var beschreibung=document.getElementById('textbox-ressource-beschreibung').value;
    var mitarbeiter_uid = MenulistGetSelectedValue('ressource-menulist-mitarbeiter'); 
    var student_uid = MenulistGetSelectedValue('ressource-menulist-student');
    var betriebsmittel_id = MenulistGetSelectedValue('ressource-menulist-betriebsmittel');
    var firma_id = MenulistGetSelectedValue('ressource-menulist-firma');
    // Variablen checken
    
    // SOAP-Action
    var soapBody = new SOAPObject("saveProjekt");
    soapBody.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
    soapBody.appendChild(new SOAPObject("oe_kurzbz")).val(oe_kurzbz);
    soapBody.appendChild(new SOAPObject("titel")).val(titel);
    soapBody.appendChild(new SOAPObject("nummer")).val(nummer);
    soapBody.appendChild(new SOAPObject("beschreibung")).val(beschreibung);
    soapBody.appendChild(new SOAPObject("beginn")).val(beginn);
    soapBody.appendChild(new SOAPObject("ende")).val(ende);
    soapBody.appendChild(new SOAPObject("neu")).val('true');
    
    var sr = new SOAPRequest("saveProjekt",soapBody);
    SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projekt.soap.php?"+gettimestamp();
    SOAPClient.SendRequest(sr, clb_saveProjekt);
}

// ****
// * Callback Funktion nach Speichern eines Projekts
// ****
function clb_saveProjekt(respObj)
{
	try
	{
		var projekt_kurzbz = respObj.Body[0].saveProjektResponse[0].message[0].Text;
		ProjektSelectKurzbz = projekt_kurzbz;
	}
	catch(e)
	{
		var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
		alert('Fehler: '+fehler);
		return;
	}
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	datasourceTreeProjekt.Refresh(false); //non blocking
	SetStatusBarText('Daten wurden gespeichert');
}


// ****
// * Laedt dynamisch die Personen fuer das DropDown Menue
// * Es muessen mindestens 3 Zeichen in das DropDown Menue eingegeben werden
// ****
function RessourceMenulistMitarbeiterLoad(menulist, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	document.getElementById('ressource-menulist-student').disabled=true; 
	document.getElementById('ressource-menulist-betriebsmittel').disabled=true; 
	document.getElementById('ressource-menulist-firma').disabled=true; 

	if(typeof(filter)=='undefined')
		v = menulist.value;
	else
		v = filter;

	if(v.length>2)
	{		
		var url = '<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php?filter='+encodeURIComponent(v)+'&'+gettimestamp();
		//nurmittitel=&
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

function RessourceLoadMitarbeiterDaten()
{
	person_id = MenulistGetSelectedValue('student-projektbetreuer-menulist-person');
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'getstundensatz');
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
		stundensatz = val.dbdml_data
	}
	
	document.getElementById('student-projektbetreuer-textbox-stundensatz').value=stundensatz;
}

function RessourceMenulistStudentLoad(menulist, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	document.getElementById('ressource-menulist-mitarbeiter').disabled=true; 
	document.getElementById('ressource-menulist-betriebsmittel').disabled=true; 
	document.getElementById('ressource-menulist-firma').disabled=true; 
	
	if(typeof(filter)=='undefined')
		v = menulist.value;
	else
		v = filter;

	if(v.length>2)
	{		
		var url = '<?php echo APP_ROOT; ?>rdf/student.rdf.php?filter='+v+'&'+gettimestamp();
		//nurmittitel=&
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

function RessourceMenulistFirmaLoad(menulist, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	document.getElementById('ressource-menulist-mitarbeiter').disabled=true; 
	document.getElementById('ressource-menulist-betriebsmittel').disabled=true; 
	document.getElementById('ressource-menulist-student').disabled=true; 
	
	if(typeof(filter)=='undefined')
		v = menulist.value;
	else
		v = filter;

	if(v.length>2)
	{		
		var url = '<?php echo APP_ROOT; ?>rdf/firma.rdf.php?filter='+v+'&'+gettimestamp();
		//nurmittitel=&
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

function RessourceMenulistBetriebsmittelLoad(menulist, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	document.getElementById('ressource-menulist-mitarbeiter').disabled=true; 
	document.getElementById('ressource-menulist-student').disabled=true; 
	document.getElementById('ressource-menulist-firma').disabled=true; 
	
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
// * Liefert den value eines Editierbaren DropDowns
// * @param id = ID der Menulist
// ****
function MenulistGetSelectedValue(id)
{
	menulist = document.getElementById(id);
	
	//Es kann sein, dass im Eingabefeld nichts steht und
	//trotzdem ein Eintrag auf selected gesetzt ist.
	//In diesem Fall soll aber kein Wert zurueckgegeben werden
	if(menulist.value=='')
		return '';
	
	//Wenn es Selektierte Eintraege gibt, dann den value zurueckliefern
	var children = menulist.getElementsByAttribute('selected','true');
	if(children.length>0)
		return children[0].value;
	else
		return '';
}


function ressourceTreeLoad()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	try
	{
		var datasources="<?php echo APP_ROOT; ?>rdf/ressource.rdf.php?"+gettimestamp();
		var ref="http://www.technikum-wien.at/ressource/liste";
		var treeRessource=document.getElementById('tree-ressourcemenue');

		//Alte DS entfernen
		var oldDatasources = treeRessource.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			treeRessource.database.RemoveDataSource(oldDatasources.getNext());
		}

		try
		{
			datasourceTreeRessource.removeXMLSinkObserver(observerTreeRessource);
			treeRessource.builder.removeListener(RessourceTreeListener);
		}
		catch(e)
		{}
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		datasourceTreeRessource = rdfService.GetDataSource(datasources);
		datasourceTreeRessource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasourceTreeRessource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		treeRessource.database.AddDataSource(datasourceTreeRessource);
		datasourceTreeRessource.addXMLSinkObserver(observerTreeRessource);
		treeRessource.builder.addListener(RessourceTreeListener);
		treeRessource.ref=ref;
	}
	catch(e)
	{
		debug("whoops Ressource load failed with exception: "+e);
	}
}

var observerTreeRessource =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) { debug('onerror:'+pError); },
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('tree-ressourcemenue').builder.rebuild();
		
	}
};

// ****
// * Nach dem Rebuild wird die Lehreinheit wieder
// * markiert
// ****
var RessourceTreeListener =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
  		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
	    window.setTimeout(RessourceTreeSelectRessource,10);
		// Progressmeter stoppen
		//document.getElementById('statusbar-progressmeter').setAttribute('mode','determined');
	}
};

// ****
// * Asynchroner (Nicht blockierender) Refresh des Trees
// ****
function RessourceRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//markierte Lehreinheit global speichern damit diese LE nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('tree-ressourcemenue');
		
	try
	{
		selectIDRessource = getTreeCellText(tree, "treecol-ressourcemenue-ressource_id", tree.currentIndex);
	}
	catch(e)
	{
		selectIDRessource=null;
	}
	datasourceTreeRessource.Refresh(false); //non blocking
}


// ****
// * Selectiert die Ressource nachdem der Tree
// * rebuildet wurde.
// ****
function RessourceTreeSelectRessource()
{

	var tree=document.getElementById('tree-ressourcemenue');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende ID gespeichert
	if(selectIDRessource!=null)
	{
		//Alle aufklappen
	   	for(var i=items-1;i>=0;i--)
	   	{
	   		if(tree.view.isContainer(i) && !tree.view.isContainerOpen(i))
	   			tree.view.toggleOpenState(i);
	   	}
	   	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	   	for(var i=0;i<items;i++)
	   	{
	   		//id der row holen
	   		id = getTreeCellText(tree, "treecol-ressourcemenue-ressource_id", i);
			
			//wenn dies die zu selektierende Zeile
			if(selectIDRessource==id)
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
// * Observer fuer die Ressourcen
// ****
var ressourceDDObserver=
{
	getSupportedFlavours : function ()
	{
  	  	var flavours = new FlavourSet();
  	  	flavours.appendFlavour("application/fhc-ressource");
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
	    debug('Ressource onDrop'+dropdata);
  	},
  	onDragStart: function (evt,transferData,action)
	{
		debug('Ressource DragStart');
		
		paramList='1';
		transferData.data=new TransferData();
		transferData.data.addDataForFlavour("application/fhc-ressource",paramList);
  	}
};