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
 * 			Karl Burkhart <burkhart@technikum-wien.>
 */

require_once('../../config/vilesci.config.inc.php');

?>
// *********** Globale Variablen *****************//
var selectIDProjektphase=null; //ID des Task Eintrages der nach dem Refresh markiert werden soll
// ********** Observer und Listener ************* //

// ****
// * Observer fuer LV Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var observerTreeProjektphase =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) { debug('onerror:'+pError); },
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('tree-projektphase').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Lehreinheit wieder
// * markiert
// ****
var ProjektphaseTreeListener =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
  		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
	    window.setTimeout(ProjektphaseTreeSelectPhase,10);
		// Progressmeter stoppen
		//document.getElementById('statusbar-progressmeter').setAttribute('mode','determined');
	}
};

// ****************** FUNKTIONEN ************************** //

// ****
// * Laedt dynamisch die Personen fuer das DropDown Menue
// ****
function ProjektphaseFkLoad(menulist, kurzbz, id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	var url = '<?php echo APP_ROOT; ?>rdf/projektphase.rdf.php?projekt_kurzbz='+kurzbz+'&optional&'+gettimestamp();
	if(typeof id!='undefined' && id!='')
		url = url +'&phase_id='+id;

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
// * Auswahl einer Phase
// ****
function onselectTreeProjektphase()
{
    // Trick 17	(sonst gibt's ein Permission denied)
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    var tree = document.getElementById('tree-projektphase');

    if (tree.currentIndex==-1) return;
    try
    {
        //Ausgewaehltes Projekt holen
        var projektphase_id = getTreeCellText(tree, "treecol-projektphase-projekt_phase_id", tree.currentIndex);

        if(projektphase_id!='')
        {
            //Projektphase wurde markiert
            //Loeschen Button aktivieren
            document.getElementById('toolbarbutton-projektphase-del').disabled=false;
			ProjektphaseDetailDisable(false);
			document.getElementById('caption-projektphase-detail').label='Bearbeiten';
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
    
    var req = new phpRequest('<?php echo APP_ROOT; ?>rdf/projektphase.rdf.php','','');
    req.add('projektphase_id',projektphase_id);
    var response = req.execute();
    
    // Datasource holen
    var dsource=parseRDFString(response, 'http://www.technikum-wien.at/projektphase');

    dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);

    var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
               getService(Components.interfaces.nsIRDFService);
    var subject = rdfService.GetResource("http://www.technikum-wien.at/projektphase/" + projektphase_id);
	//console.log(subject);
    var predicateNS = "http://www.technikum-wien.at/projektphase/rdf";

    //Daten holen
    var projekt_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#projekt_kurzbz" ));
    var projektphase_fk=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#projektphase_fk" ));
    var ressource_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ressource_id" ));
    var bezeichnung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bezeichnung" ));
    var typ=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#typ" ));
    var beschreibung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beschreibung" ));
    var start=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#start" ));
    var ende=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ende" ));
    var budget=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#budget" ));
    var personentage=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#personentage" ));
    var farbe=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#farbe" ));
	var zeitaufzeichnung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zeitaufzeichnung" ));

	if (!zeitaufzeichnung)
	{
		zeitaufzeichnung='Nein';
	}
	else
	{
		zeitaufzeichnung='Ja';
	}
    //alert(typ);
    
    //Daten den Feldern zuweisen
	var menulist = document.getElementById('menulist-projektphase-detail-projektphase_fk');
	ProjektphaseFkLoad(menulist, projekt_kurzbz, projektphase_id);
    menulist = document.getElementById('menulist-projektphase-detail-ressource');
	RessourceTaskLoad(menulist, projektphase_id);
    document.getElementById('textbox-projektphase-detail-projekt_kurzbz').value=projekt_kurzbz;
    document.getElementById('textbox-projektphase-detail-projektphase_id').value=projektphase_id;
    document.getElementById('textbox-projektphase-detail-beschreibung').value=beschreibung;
    document.getElementById('textbox-projektphase-detail-bezeichnung').value=bezeichnung;
    document.getElementById('textbox-projektphase-detail-typ').value=typ;
    document.getElementById('textbox-projektphase-detail-start').value=start;
    document.getElementById('textbox-projektphase-detail-ende').value=ende;
    document.getElementById('textbox-projektphase-detail-budget').value=budget;
    document.getElementById('textbox-projektphase-detail-personentage').value=personentage;
    document.getElementById('textbox-projektphase-detail-farbe').value=farbe;
    document.getElementById('checkbox-projektphase-detail-neu').checked=false;
	if(zeitaufzeichnung=='Nein')
		document.getElementById('checkbox-projektphase-detail-zeitaufzeichnung').checked=false;
	else
		document.getElementById('checkbox-projektphase-detail-zeitaufzeichnung').checked=true;

    MenulistSelectItemOnValue('menulist-projektphase-detail-projektphase_fk', projektphase_fk);
	MenulistSelectItemOnValue('menulist-projektphase-detail-ressource', ressource_id);
    
    //Notizen zu einer Phase Laden
	notiz = document.getElementById('box-projektphase-notizen');
	notiz.LoadNotizTree('',projektphase_id,'','','','','', '','');
	
	ressource = document.getElementById('box-projekt-ressource-phase');
	ressource.LoadRessourceTree('',projektphase_id);
}
// ****
// * Asynchroner (Nicht blockierender) Refresh des Trees
// ****
function ProjektphaseTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//markierte Lehreinheit global speichern damit diese LE nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('tree-projektphase');
		
	try
	{
		selectIDProjektphase = getTreeCellText(tree, "treecol-projektphase-projekt_phase_id", tree.currentIndex);
	}
	catch(e)
	{
		selectIDProjektphase=null;
	}
	
	datasourceTreeProjektphase.Refresh(false); //non blocking
}

// ****
// * Speichert die Details
// ****
function saveProjektphaseDetail()
{

	//Werte holen
	var projektphase_id = document.getElementById('textbox-projektphase-detail-projektphase_id').value;
	var projektphase_fk = document.getElementById('menulist-projektphase-detail-projektphase_fk').value;
	var ressource_id = document.getElementById('menulist-projektphase-detail-ressource').value;
	var projekt_kurzbz = document.getElementById('textbox-projektphase-detail-projekt_kurzbz').value;
	var bezeichnung = document.getElementById('textbox-projektphase-detail-bezeichnung').value;
	var typ = document.getElementById('textbox-projektphase-detail-typ').value;
	var beschreibung = document.getElementById('textbox-projektphase-detail-beschreibung').value;
	var start = document.getElementById('textbox-projektphase-detail-start').iso;
	var ende = document.getElementById('textbox-projektphase-detail-ende').iso;
	var budget = document.getElementById('textbox-projektphase-detail-budget').value;
	var personentage = document.getElementById('textbox-projektphase-detail-personentage').value;
    var farbe = document.getElementById('textbox-projektphase-detail-farbe').value;
	var neu = document.getElementById('checkbox-projektphase-detail-neu').checked;
	var zeitaufzeichnung = document.getElementById('checkbox-projektphase-detail-zeitaufzeichnung').checked;

	var soapBody = new SOAPObject("saveProjektphase");
	//soapBody.appendChild(new SOAPObject("username")).val('joe');
	//soapBody.appendChild(new SOAPObject("passwort")).val('waschl');
				
	var phase = new SOAPObject("phase");
	phase.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
	phase.appendChild(new SOAPObject("projektphase_fk")).val(projektphase_fk);
	phase.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
	phase.appendChild(new SOAPObject("bezeichnung")).cdataval(bezeichnung);
	phase.appendChild(new SOAPObject("typ")).cdataval(typ);
	phase.appendChild(new SOAPObject("ressource_id")).val(ressource_id);
	phase.appendChild(new SOAPObject("beschreibung")).cdataval(beschreibung);
	phase.appendChild(new SOAPObject("start")).val(start);
	phase.appendChild(new SOAPObject("ende")).val(ende);
	phase.appendChild(new SOAPObject("budget")).val(budget);
	phase.appendChild(new SOAPObject("personentage")).val(personentage);
    phase.appendChild(new SOAPObject("farbe")).val(farbe);
	if(zeitaufzeichnung)
	{
		phase.appendChild(new SOAPObject("zeitaufzeichnung")).val('true');
	}
	else
	{
		phase.appendChild(new SOAPObject("zeitaufzeichnung")).val('false');
	}
	if(neu)
	{
		phase.appendChild(new SOAPObject("neu")).val('true');
	}
	else
	{
		phase.appendChild(new SOAPObject("neu")).val('false');
	}
	phase.appendChild(new SOAPObject("user")).val(getUsername());
	soapBody.appendChild(phase);
		
	var sr = new SOAPRequest("saveProjektphase",soapBody);

	SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projektphase.soap.php?"+gettimestamp();
	SOAPClient.SendRequest(sr, clb_saveProjektphase);
}

// ****
// * Callback Funktion nach Speichern eines Task
// ****
function clb_saveProjektphase(respObj)
{
	try
	{
		var id = respObj.Body[0].saveProjektphaseResponse[0].message[0].Text;
        drawGantt();
	}
	catch(e)
	{
		var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
		alert('Fehler: '+fehler);
		return;
	}
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	document.getElementById('textbox-projektphase-detail-projektphase_id').value=id;
		
	selectIDProjektphase=id;
	datasourceTreeProjektphase.Refresh(false); //non blocking
	SetStatusBarText('Daten wurden gespeichert');
}

// ****
// * Selectiert die Lektorzuordnung nachdem der Tree
// * rebuildet wurde.
// ****
function ProjektphaseTreeSelectPhase()
{
	var tree=document.getElementById('tree-projektphase');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende ID gespeichert
	if(selectIDProjektphase!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//id der row holen
	   		id = getTreeCellText(tree, "treecol-projektphase-projekt_phase_id", i);
			
			//wenn dies die zu selektierende Zeile
			if(selectIDProjektphase==id)
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
// * Setzt die Detailfelder auf die Standardwerte zurueck
// ****
function ProjektphaseDetailReset()
{
	document.getElementById('textbox-projektphase-detail-projektphase_id').value='';
	document.getElementById('textbox-projektphase-detail-projekt_kurzbz').value='';
	document.getElementById('textbox-projektphase-detail-bezeichnung').value='';
	document.getElementById('textbox-projektphase-detail-typ').value='Projektphase';
	document.getElementById('textbox-projektphase-detail-beschreibung').value='';
	document.getElementById('textbox-projektphase-detail-start').value='';
	document.getElementById('textbox-projektphase-detail-ende').value='';
	document.getElementById('textbox-projektphase-detail-budget').value='';
	document.getElementById('textbox-projektphase-detail-personentage').value='';	
}

// ****
// * Deaktiviert die Detailfelder
// ****
function ProjektphaseDetailDisable(val)
{
	document.getElementById('menulist-projektphase-detail-projektphase_fk').disabled=val;
	document.getElementById('menulist-projektphase-detail-ressource').disabled=val;
	document.getElementById('textbox-projektphase-detail-bezeichnung').disabled=val;
	document.getElementById('textbox-projektphase-detail-typ').disabled=val;
	document.getElementById('textbox-projektphase-detail-beschreibung').disabled=val;
	document.getElementById('textbox-projektphase-detail-start').disabled=val;
	document.getElementById('textbox-projektphase-detail-ende').disabled=val;
	document.getElementById('textbox-projektphase-detail-budget').disabled=val;
	document.getElementById('textbox-projektphase-detail-personentage').disabled=val;
    document.getElementById('textbox-projektphase-detail-farbe').disabled=val;
	document.getElementById('button-projektphase-detail-speichern').disabled=val;
}

// ****
// * Setzt die Felder fuer eine Neuanlage
// ****
function ProjektphaseNeu()
{
	var tree = document.getElementById('tree-projektphase');
	tree.view.selection.clearSelection();
	
	ProjektphaseDetailReset();
	ProjektphaseDetailDisable(false);
	
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//Projekt Kurzbz ermitteln
    var tree=document.getElementById('tree-projektmenue');
    var projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
    
    //Menulist fuer Parents laden und optionalen Eintrag markieren
    var menulist = document.getElementById('menulist-projektphase-detail-projektphase_fk');
    ProjektphaseFkLoad(menulist, projekt_kurzbz);
    MenulistSelectItemOnValue('menulist-projektphase-detail-projektphase_fk', '');
    
    document.getElementById('textbox-projektphase-detail-projekt_kurzbz').value=projekt_kurzbz;
    
    //Neu Status setzen
    document.getElementById('caption-projektphase-detail').label='Neue Phase';
    document.getElementById('checkbox-projektphase-detail-neu').checked=true;
    document.getElementById('textbox-projektphase-detail-farbe').value='#0000FF';
    document.getElementById('textbox-projektphase-detail-typ').value='Projektphase';
    
    //Detail Tab auswaehlen
	document.getElementById('projektphase-tabs').selectedItem=document.getElementById('projektphase-tab-detail');	

/*	    
	//Menulist fuer Ressourcen laden und optionalen Eintrag markieren
    menulist = document.getElementById('menulist-projektphase-detail-ressource');
    RessourceTaskLoad(menulist, projektphase_id);
    MenulistSelectItemOnValue('menulist-projektphase-detail-ressource', '');
  */  
}

function ProjektphaseDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('tree-projektphase');

	if (tree.currentIndex==-1)
		return;

	try
	{
		//Ausgewaehlten Task holen
		id = getTreeCellText(tree, "treecol-projektphase-projekt_phase_id", tree.currentIndex);
   	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Abfrage ob wirklich geloescht werden soll
	if (confirm('Wollen Sie den Phase mit der ID '+id+' wirklich loeschen?'))
	{
		var soapBody = new SOAPObject("deleteProjektphase");
		soapBody.appendChild(new SOAPObject("projektphase_id")).val(id);
		var sr = new SOAPRequest("deleteProjektphase",soapBody);
	
		SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projektphase.soap.php?"+gettimestamp();
		SOAPClient.SendRequest(sr, clb_deleteProjektphase);
	}
}

// ****
// * Delete Callback Funktion
// ****
function clb_deleteProjektphase(respObj)
{
	try
	{
		var msg = respObj.Body[0].deleteProjektphaseResponse[0].message[0].Text;
	}
	catch(e)
	{
		var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
		alert('Fehler: '+fehler);
		return;
	}
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			
	TaskSelectID='';
	datasourceTreeProjektphase.Refresh(false); //non blocking
	ProjektmenueRefresh()
	SetStatusBarText('Eintrag wurde entfernt');
}
