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
    var dsource=parseRDFString(response, 'http://www.technikum-wien.at/projektphase/alle-projektphasen');

    dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);

    var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
               getService(Components.interfaces.nsIRDFService);
    var subject = rdfService.GetResource("http://www.technikum-wien.at/projektphase/" + projektphase_id);

    var predicateNS = "http://www.technikum-wien.at/projektphase/rdf";

    //Daten holen
    var projekt_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#projekt_kurzbz" ));
    var projektphase_fk=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#projektphase_fk" ));
    var bezeichnung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bezeichnung" ));
    var beschreibung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beschreibung" ));
    var start=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#start" ));
    var ende=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ende" ));
    var budget=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#budget" ));
    var personentage=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#personentage" ));
    
    //Daten den Feldern zuweisen

    document.getElementById('textbox-projektphase-detail-projekt_kurzbz').value=projekt_kurzbz;
    document.getElementById('textbox-projektphase-detail-projektphase_id').value=projektphase_id;
    document.getElementById('textbox-projektphase-detail-projektphase_fk').value=projektphase_fk;
    document.getElementById('textbox-projektphase-detail-beschreibung').value=beschreibung;
    document.getElementById('textbox-projektphase-detail-bezeichnung').value=bezeichnung;
    document.getElementById('textbox-projektphase-detail-start').value=start;
    document.getElementById('textbox-projektphase-detail-ende').value=ende;
    document.getElementById('textbox-projektphase-detail-budget').value=budget;
    document.getElementById('textbox-projektphase-detail-personentage').value=personentage;
    document.getElementById('checkbox-projektphase-detail-neu').checked=false;
    
    //Notizen zu einer Phase Laden
	notiz = document.getElementById('box-projektphase-notizen');
	notiz.LoadNotizTree('',projektphase_id,'','','','','', '');
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
	projektphase_id = document.getElementById('textbox-projektphase-detail-projektphase_id').value;
	projektphase_fk = document.getElementById('textbox-projektphase-detail-projektphase_fk').value;
	projekt_kurzbz = document.getElementById('textbox-projektphase-detail-projekt_kurzbz').value;
	bezeichnung = document.getElementById('textbox-projektphase-detail-bezeichnung').value;
	beschreibung = document.getElementById('textbox-projektphase-detail-beschreibung').value;
	start = document.getElementById('textbox-projektphase-detail-start').value;
	ende = document.getElementById('textbox-projektphase-detail-ende').value;
	budget = document.getElementById('textbox-projektphase-detail-budget').value;
	personentage = document.getElementById('textbox-projektphase-detail-personentage').value;
	neu = document.getElementById('checkbox-projektphase-detail-neu').checked;

	var soapBody = new SOAPObject("saveProjektphase");
	soapBody.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
	soapBody.appendChild(new SOAPObject("projektphase_fk")).val(projektphase_fk);
	soapBody.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
	soapBody.appendChild(new SOAPObject("bezeichnung")).val(bezeichnung);
	soapBody.appendChild(new SOAPObject("beschreibung")).val(beschreibung);
	soapBody.appendChild(new SOAPObject("start")).val(start);
	soapBody.appendChild(new SOAPObject("ende")).val(ende);
	soapBody.appendChild(new SOAPObject("budget")).val(budget);
	soapBody.appendChild(new SOAPObject("personentage")).val(personentage);
	if(neu)
		soapBody.appendChild(new SOAPObject("neu")).val('true');
	else
		soapBody.appendChild(new SOAPObject("neu")).val('false');
	soapBody.appendChild(new SOAPObject("user")).val(getUsername());
	
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
		var id = respObj.Body[0].SaveProjektphaseResponse[0].message[0].Text;
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

// Dialog fuer neues Projekt starten
function ProjektphaseNeu()
{
    // Trick 17	(sonst gibt's ein Permission denied)
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

    var tree=document.getElementById('tree-projektmenue');
    
    var projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
    window.open('<?php echo APP_ROOT; ?>content/projekt/projektphase.window.xul.php?projekt_kurzbz='+projekt_kurzbz,'Projektphase anlegen', 'height=384,width=512,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no');
    //alert (oe);
}
