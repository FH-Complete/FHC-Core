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
var ProjektSelectKurzbz=null; //Kurzbz des Projekt Eintrages der nach dem Refresh markiert werden soll
// ********** Observer und Listener ************* //

// ****
// * Observer fuer LV Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var observerTreeProjekt =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) { debug('onerror:'+pError); },
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('tree-projekt').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Lehreinheit wieder
// * markiert
// ****
var listenerTreeProjekt =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
  		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
	    window.setTimeout(ProjektTreeSelectProjekt,10);
		// Progressmeter stoppen
		//document.getElementById('statusbar-progressmeter').setAttribute('mode','determined');
	}
};

// ****************** FUNKTIONEN ************************** //

// ****
// * Asynchroner (Nicht blockierender) Refresh des LV Trees
// ****
function ProjektTreeRefresh()
{
	if(datasourceTreeProjekt!=undefined)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

		//markierte Lehreinheit global speichern damit diese LE nach dem
		//refresh wieder markiert werden kann.
		var tree = document.getElementById('tree-projekt');

		try
		{
			ProjektSelectKurzbz = getTreeCellText(tree, "treecol-projekt-projekt_kurzbz", tree.currentIndex);
		}
		catch(e)
		{
			ProjektSelectKurzbz=null;
		}
		datasourceTreeProjekt.Refresh(false); //non blocking
	}
}

// ****
// * Selectiert die Lektorzuordnung nachdem der Tree
// * rebuildet wurde.
// ****
function onselectProjekt()
{
    // Trick 17	(sonst gibt's ein Permission denied)
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    var tree = document.getElementById('tree-projekt');
	
    if (tree.currentIndex==-1) return;
    try
    {
        //Ausgewaehltes Projekt holen
        var projekt_kurzbz = getTreeCellText(tree, "treecol-projekt-projekt_kurzbz", tree.currentIndex);
		
        if(projekt_kurzbz!='')
        {
            //Projekt wurde markiert
            //Loeschen Button aktivieren
            document.getElementById('toolbarbutton-projekt-del').disabled=false;
            document.getElementById('textbox-projekt-detail-projekt_kurzbz').disabled=true;
            ProjektDisableFields(false);
            document.getElementById('caption-projekt-detail').label='Bearbeiten';
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
    var req = new phpRequest('<?php echo APP_ROOT; ?>rdf/projekt.rdf.php','','');
    req.add('projekt_kurzbz',projekt_kurzbz);
    var response = req.execute();
    
    // Datasource holen
    var dsource=parseRDFString(response, 'http://www.technikum-wien.at/projekt/alle-projekte');

    dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);

    var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
               getService(Components.interfaces.nsIRDFService);
    var subject = rdfService.GetResource("http://www.technikum-wien.at/projekt/" + projekt_kurzbz);

    var predicateNS = "http://www.technikum-wien.at/projekt/rdf";

    //Daten holen
    var projekt_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#projekt_kurzbz" ));
    var oe_kurzbz=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#oe_kurzbz" ));
    var titel=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titel" ));
    var beschreibung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beschreibung" ));
    var nummer=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nummer" ));
    var beginn=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beginn" ));
    var ende=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ende" ));
    var budget=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#budget" ));
    var farbe=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#farbe" ));
    var aufwandstyp_kurzbz=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aufwandstyp_kurzbz" ));
    
    //Daten den Feldern zuweisen

    document.getElementById('textbox-projekt-detail-projekt_kurzbz').value=projekt_kurzbz;
    //document.getElementById('menulist-projekt-detail-oe_kurzbz').value=oe_kurzbz;
    MenulistSelectItemOnValue('menulist-projekt-detail-oe_kurzbz', oe_kurzbz);
    document.getElementById('textbox-projekt-detail-titel').value=titel;
    document.getElementById('textbox-projekt-detail-beschreibung').value=beschreibung;
    document.getElementById('textbox-projekt-detail-nummer').value=nummer;
    document.getElementById('textbox-projekt-detail-beginn').value=beginn;
    document.getElementById('textbox-projekt-detail-ende').value=ende;
    document.getElementById('textbox-projekt-detail-budget').value=budget;
    document.getElementById('textbox-projekt-detail-farbe').value=farbe;
    document.getElementById('checkbox-projekt-detail-neu').checked=false;
    MenulistSelectItemOnValue('menulist-projekt-detail-aufwandstyp', aufwandstyp_kurzbz);
    
    
    //Notizen zu einem Projekt Laden
	notiz = document.getElementById('box-projekt-notizen');
	notiz.LoadNotizTree(projekt_kurzbz,'','','','','','', '','');
	
	ressource = document.getElementById('box-projekt-ressourcen');
	ressource.LoadRessourceTree(projekt_kurzbz,'');
	

}
// ****
// * Speichert die Details
// ****
function saveProjektDetail()
{

	//Werte holen
	projekt_kurzbz = document.getElementById('textbox-projekt-detail-projekt_kurzbz').value;
	//oe_kurzbz = document.getElementById('menulist-projekt-detail-oe_kurzbz').value;
	oe_kurzbz = MenulistGetSelectedValue('menulist-projekt-detail-oe_kurzbz'); 
	titel = document.getElementById('textbox-projekt-detail-titel').value;
	beschreibung = document.getElementById('textbox-projekt-detail-beschreibung').value;
	nummer = document.getElementById('textbox-projekt-detail-nummer').value;
	beginn = document.getElementById('textbox-projekt-detail-beginn').iso;
	ende = document.getElementById('textbox-projekt-detail-ende').iso;
	budget = document.getElementById('textbox-projekt-detail-budget').value;
    farbe = document.getElementById('textbox-projekt-detail-farbe').value;
	neu = document.getElementById('checkbox-projekt-detail-neu').checked;
	aufwandstyp_kurzbz = MenulistGetSelectedValue('menulist-projekt-detail-aufwandstyp');
	
	var soapBody = new SOAPObject("saveProjekt");
	//soapBody.appendChild(new SOAPObject("username")).val('joe');
	//soapBody.appendChild(new SOAPObject("passwort")).val('waschl');
				
	var projekt = new SOAPObject("projekt");
    projekt.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
    projekt.appendChild(new SOAPObject("oe_kurzbz")).val(oe_kurzbz);
    projekt.appendChild(new SOAPObject("titel")).cdataval(titel);
    projekt.appendChild(new SOAPObject("nummer")).cdataval(nummer);
    projekt.appendChild(new SOAPObject("beschreibung")).cdataval(beschreibung);
    projekt.appendChild(new SOAPObject("beginn")).val(beginn);
    projekt.appendChild(new SOAPObject("ende")).val(ende);
    projekt.appendChild(new SOAPObject("budget")).val(budget);
    projekt.appendChild(new SOAPObject("farbe")).val(farbe);
    projekt.appendChild(new SOAPObject("aufwandstyp_kurzbz")).val(aufwandstyp_kurzbz);
    
	if(neu)
		projekt.appendChild(new SOAPObject("neu")).val('true');
	else	
		projekt.appendChild(new SOAPObject("neu")).val('false');
	soapBody.appendChild(projekt);
	
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
        drawGantt();
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
// * Selectiert ein Projekt nachdem der Tree
// * rebuildet wurde.
// ****
function ProjektTreeSelectProjekt()
{

	var tree=document.getElementById('tree-projekt');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende ID gespeichert
	if(ProjektSelectKurzbz!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//id der row holen
	   		id = getTreeCellText(tree, "treecol-projekt-projekt_kurzbz", i);
			
			//wenn dies die zu selektierende Zeile
			if(ProjektSelectKurzbz==id)
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
// * Projekt loeschen
// ****
function ProjektDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('tree-projekt');

	if (tree.currentIndex==-1)
		return;

	try
	{
		//Ausgewaehltes Projekt holen
		id = getTreeCellText(tree, "treecol-projekt-projekt_kurzbz", tree.currentIndex);
   	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Abfrage ob wirklich geloescht werden soll
	if (confirm('Wollen Sie das Projekt mit der Kurzbz '+id+' wirklich loeschen?'))
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
function ProjektDetailReset()
{
	document.getElementById('textbox-projekt-detail-projekt_kurzbz').value='';
	//document.getElementById('menulist-projekt-detail-oe_kurzbz').value='';
	document.getElementById('textbox-projekt-detail-titel').value='';
	document.getElementById('textbox-projekt-detail-nummer').value='';
	document.getElementById('textbox-projekt-detail-beschreibung').value='';
	document.getElementById('textbox-projekt-detail-beginn').value='';
	document.getElementById('textbox-projekt-detail-ende').value='';
	document.getElementById('textbox-projekt-detail-budget').value='';
}

// ****
// * Deaktiviert alle Eingabe- und Auswahlfelder
// ****
function ProjektDisableFields(val)
{
	document.getElementById('menulist-projekt-detail-oe_kurzbz').disabled=val;
	document.getElementById('textbox-projekt-detail-titel').disabled=val;
	document.getElementById('textbox-projekt-detail-nummer').disabled=val;
	document.getElementById('textbox-projekt-detail-beschreibung').disabled=val;
	document.getElementById('textbox-projekt-detail-beginn').disabled=val;
	document.getElementById('textbox-projekt-detail-ende').disabled=val;
	document.getElementById('textbox-projekt-detail-budget').disabled=val;
    document.getElementById('textbox-projekt-detail-farbe').disabled=val;
	document.getElementById('button-projekt-detail-speichern').disabled=val;
	document.getElementById('menulist-projekt-detail-aufwandstyp').disabled=val;
}


// ****
// * Neues Projekt anlegen
// ****
function ProjektNeu()
{
	//Markierung im Tree entfernen
	var tree = document.getElementById('tree-projekt');
	tree.view.selection.clearSelection();
	
	//Detailfelder resetten und aktivieren
	ProjektDetailReset();
	ProjektDisableFields(false);
	
	//Label setzen und status auf neu setzen
	document.getElementById('textbox-projekt-detail-projekt_kurzbz').disabled=false;
	document.getElementById('checkbox-projekt-detail-neu').checked=true;
	document.getElementById('caption-projekt-detail').label='Neues Projekt';
	document.getElementById('textbox-projekt-detail-farbe').value='#FF0000';
    
	//Detail Tab auswaehlen
	document.getElementById('tabs-projekt-main').selectedItem=document.getElementById('tab-projekt-detail');	
}

function ProjektPrintStatusbericht()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('tree-projekt');

	if (tree.currentIndex==-1)
	{
		alert('Kein Projekt ausgewählt!');
		return;
	}
	try
	{
		//Ausgewaehltes Projekt holen
		id = getTreeCellText(tree, "treecol-projekt-projekt_kurzbz", tree.currentIndex);
   	}
	catch(e)
	{
		alert(e);
		return false;
	}
	window.open('<?php echo APP_ROOT ?>content/pdfExport.php?xsl=statusbericht&xml=statusbericht.rdf.php&projekt_kurzbz='+id);
}

function ProjektPrintProjektbeschreibung()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('tree-projekt');

	if (tree.currentIndex==-1)
	{
		alert('Kein Projekt ausgewählt!');
		return;
	}
	try
	{
		//Ausgewaehltes Projekt holen
		id = getTreeCellText(tree, "treecol-projekt-projekt_kurzbz", tree.currentIndex);
   	}
	catch(e)
	{
		alert(e);
		return false;
	}
	window.open('<?php echo APP_ROOT ?>content/projektbeschreibung.php?xsl=Projektbeschr&xml=projektbeschreibung.rdf.php&projekt_kurzbz='+id);
}
