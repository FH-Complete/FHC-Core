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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');

$conn = pg_pconnect(CONN_STRING);

$user = get_uid();
loadVariables($conn, $user);
?>
var lfvt_detail_lehrfach_id;
var lfvt_detail_gruppe_datasource;
var lfvt_detail_lektor_datasource;

// ****
// * Observer fuer LFVT Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var lfvt_tree_observer = 
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink) 
	{	
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('treeLFVT').builder.rebuild();
	}
}	

// ****
// * Asynchroner (Nicht blockierender) Refresh des LFVT Trees
// ****
function lfvt_tree_refresh()
{
	
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	lfvt_tree_datasource.Refresh(false); //non blocking
}

// ****
// * neue Lehreinheit anlegen
// ****
function lvaNeu() 
{
	lfvtDetailDisableFields(false);
	
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	var tree = document.getElementById('treeLFVT');
	var lvaDetail=document.getElementById('lvaDetail');
	//Details zuruecksetzen
	lfvtDetailReset();
	//Lehrveranstaltungs_id holen
	var col = tree.columns ? tree.columns["lva_lehrveranstaltung_id"] : "lva_lehrveranstaltung_id";
	var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);
	
	//Lehrfach drop down setzen

	//ID in globale Variable speichern
	lfvt_detail_lehrfach_id='';
		
	lehrfachmenulist = document.getElementById('lfvt_detail_menulist_lehrfach');
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	
	//Entfernen der alten Datasources
	var oldDatasources = lehrfachmenulist.database.GetDataSources();	
	while(oldDatasources.hasMoreElements())
	{
		lehrfachmenulist.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	lehrfachmenulist.builder.refresh();
	
	//Url zusammenbauen
	var url = '<?php echo APP_ROOT;?>rdf/lehrfach.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+'&'+gettimestamp();

	//RDF holen
	var newDs  = rdfService.GetDataSource(url);
	lehrfachmenulist.database.AddDataSource(newDs);
	
	//SinkObserver hinzufuegen
	var sink = newDs.QueryInterface(Components.interfaces.nsIRDFXMLSink);  
	sink.addXMLSinkObserver(lfvt_detail_lehrfach_observer);
	
	document.getElementById('lfvt_detail_textbox_lehrveranstaltung').value=lehrveranstaltung_id;
	document.getElementById('lfvt_detail_checkbox_new').checked=true;
}

// ****
// * Lehreinheit loeschen
// ****
function lvaDelete() 
{

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('treeLFVT');
	
	if (tree.currentIndex==-1) return;
	try 
	{
		//Ausgewaehlte Lehreinheit holen
        var col = tree.columns ? tree.columns["lva_lehreinheit_id"] : "lva_lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(tree.currentIndex,col);
		if(lehreinheit_id=='')
			return false
	}
	catch(e)
	{
		alert(e);
		return false;
	}	
	
	//Abfrage ob wirklich geloescht werden soll
	if (confirm('Wollen Sie diese Lehreinheit wirklich löschen?')) 
	{		
		//Script zum loeschen der Lehreinheit aufrufen
		var req = new phpRequest('lfvtCUD.php','','');
		req.add('type','lehreinheit');
		req.add('do','delete');
		req.add('lehreinheit_id',lehreinheit_id);
		var response = req.executePOST();
		if (response!='ok') 
			alert(response);
			
		lfvt_tree_refresh();
		lfvtDetailReset();
	}
}

// ****
// * Observer fuer lehrfachdropdown
// ****
var lfvt_detail_lehrfach_observer = {
	     onBeginLoad: function(aSink) { },
	     onInterrupt: function(aSink) { },
	     onResume:    function(aSink) { },
	     onEndLoad:   function(aSink) { 	    
	     	//Das richtige Lehrfach markieren	
	     	if(lfvt_detail_lehrfach_id!='')
		 		document.getElementById('lfvt_detail_menulist_lehrfach').value=lfvt_detail_lehrfach_id;
		 	else
		 	{
		 		document.getElementById('lfvt_detail_menulist_lehrfach').value='';
		 		document.getElementById('lfvt_detail_menulist_lehrfach').label='';
		 	}
	     },
	     onError: function(aSink, aStatus, aErrorMsg) { 
	     	alert('Bei der Datenuebertragung ist ein Fehler Aufgetreten. Bitte Versuchen Sie es erneut.'); 
	     }
	  };

// ****
// * Leert alle Eingabe- und Auswahlfelder
// ****
function lfvtDetailReset()
{
	document.getElementById('lfvt_detail_textbox_lvnr').value='';
	document.getElementById('lfvt_detail_textbox_unr').value='';
	document.getElementById('lfvt_detail_textbox_lehrveranstaltung').value='';
	document.getElementById('lfvt_detail_checkbox_lehre').checked=false;
	document.getElementById('lfvt_detail_textbox_stundenblockung').value='';
	document.getElementById('lfvt_detail_textbox_wochenrythmus').value='';
	document.getElementById('lfvt_detail_textbox_startkw').value='';
	document.getElementById('lfvt_detail_textbox_anmerkung').value='';
	document.getElementById('lfvt_detail_menulist_sprache').value='German';
	document.getElementById('lfvt_detail_menulist_raumtyp').value='Dummy';
	document.getElementById('lfvt_detail_menulist_raumtypalternativ').value='Dummy';
	document.getElementById('lfvt_detail_menulist_studiensemester').value='<?php echo $semester_aktuell; ?>';
	document.getElementById('lfvt_detail_menulist_lehrform').value='UE';
	
	//mitarbeiterlehreinheit tree leeren
	lektortree = document.getElementById('lfvt_detail_tree_lehreinheitmitarbeiter');
	
	//Alte DS entfernen
	var oldDatasources = lektortree.database.GetDataSources();	
	while(oldDatasources.hasMoreElements())
	{
		lektortree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	lektortree.builder.refresh();
		
	//Gruppentree leeren
	gruppentree = document.getElementById('lfvt_detail_tree_lehreinheitgruppe');
	
	//Alte DS entfernen
	var oldDatasources = gruppentree.database.GetDataSources();	
	while(oldDatasources.hasMoreElements())
	{
		gruppentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	gruppentree.builder.refresh();
}

// ****
// * Deaktiviert alle Eingabe- und Auswahlfelder
// ****
function lfvtDetailDisableFields(val)
{
	document.getElementById('lfvt_detail_textbox_lvnr').disabled=val;
	document.getElementById('lfvt_detail_textbox_unr').disabled=val;
	document.getElementById('lfvt_detail_textbox_lehrveranstaltung').disabled=val;
	document.getElementById('lfvt_detail_checkbox_lehre').disabled=val;
	document.getElementById('lfvt_detail_textbox_stundenblockung').disabled=val;
	document.getElementById('lfvt_detail_textbox_wochenrythmus').disabled=val;
	document.getElementById('lfvt_detail_textbox_startkw').disabled=val;
	document.getElementById('lfvt_detail_textbox_anmerkung').disabled=val;
	document.getElementById('lfvt_detail_menulist_sprache').disabled=val;
	document.getElementById('lfvt_detail_menulist_lehrfach').disabled=val;
	document.getElementById('lfvt_detail_menulist_raumtyp').disabled=val;
	document.getElementById('lfvt_detail_menulist_raumtypalternativ').disabled=val;
	document.getElementById('lfvt_detail_menulist_studiensemester').disabled=val;
	document.getElementById('lfvt_detail_menulist_lehrform').disabled=val;
	document.getElementById('lfvt_detail_tree_lehreinheitgruppe').disabled=val;
	document.getElementById('lfvt_detail_button_save').disabled=val;
}

// ****
// * Speichert die Details
// ****
function lfvtDetailSave()
{
	//Werte holen
	lvnr = document.getElementById('lfvt_detail_textbox_lvnr').value;
	unr = document.getElementById('lfvt_detail_textbox_unr').value;
	lehrveranstaltung = document.getElementById('lfvt_detail_textbox_lehrveranstaltung').value;
	lehre = document.getElementById('lfvt_detail_checkbox_lehre').checked;
	stundenblockung = document.getElementById('lfvt_detail_textbox_stundenblockung').value;
	wochenrythmus = document.getElementById('lfvt_detail_textbox_wochenrythmus').value;
	start_kw = document.getElementById('lfvt_detail_textbox_startkw').value;
	anmerkung = document.getElementById('lfvt_detail_textbox_anmerkung').value;
	sprache = document.getElementById('lfvt_detail_menulist_sprache').value;
	lehrfach = document.getElementById('lfvt_detail_menulist_lehrfach').value;
	raumtyp = document.getElementById('lfvt_detail_menulist_raumtyp').value;
	raumtypalternativ = document.getElementById('lfvt_detail_menulist_raumtypalternativ').value;
	studiensemester = document.getElementById('lfvt_detail_menulist_studiensemester').value;
	lehrform = document.getElementById('lfvt_detail_menulist_lehrform').value;
	
	if(lehrveranstaltung=='')
		return false;
	
	var req = new phpRequest('lfvtCUD.php','','');
	neu = document.getElementById('lfvt_detail_checkbox_new').checked;
	
	if (neu) 
	{
		req.add('do','create');
	} 
	else  
	{
		req.add('do','update');
		lehreinheit_id = document.getElementById('lfvt_detail_textbox_lehreinheit_id').value;
		req.add('lehreinheit_id',lehreinheit_id);
	}
	//alert(lehreinheit_id);
	req.add('type', 'lehreinheit');
	req.add('unr', unr);
	req.add('lvnr', lvnr);
	req.add('sprache', sprache);
	req.add('lehrveranstaltung', lehrveranstaltung);
	req.add('lehrfach_id', lehrfach);
	req.add('raumtyp', raumtyp);
	req.add('raumtypalternativ', raumtypalternativ);
	req.add('lehre', lehre);
	req.add('stundenblockung', stundenblockung);
	req.add('wochenrythmus', wochenrythmus);
	req.add('start_kw', start_kw);
	req.add('studiensemester_kurzbz', studiensemester);
	req.add('lehrform', lehrform);
	req.add('anmerkung', anmerkung);
	
	var response = req.executePOST();
	if (response!='ok') 
	{
		alert(response);
	} 
	else 
	{
		document.getElementById('lfvt_detail_checkbox_new').checked=false;
		lfvt_tree_refresh();
		alert('Daten wurden gespeichert');
	}
}

// ****
// * Auswahl einer Lehreinheit
// * bei Auswahl einer Lehreinheit wird diese Lehreinheit geladen
// * und die Daten unten angezeigt
// ****
function lvaAuswahl() 
{

	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('treeLFVT');
	
	//Felder bei Lektorenzuordnung deaktivieren
	lfvt_LehreinheitMitarbeiterDisableFields(true);
	
	if (tree.currentIndex==-1) return;
	try 
	{
		//Ausgewaehlte Lehreinheit holen
        var col = tree.columns ? tree.columns["lva_lehreinheit_id"] : "lva_lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(tree.currentIndex,col);
		if(lehreinheit_id=='')
		{
			//Lehrveranstaltung wurde markiert
			//Neu Button aktivieren
			document.getElementById('lfvt_toolbar_neu').disabled=false;
			document.getElementById('lfvt_toolbar_del').disabled=true;
									
			lfvtDetailDisableFields(true);
			//Details zuruecksetzen
			lfvtDetailReset();
			return false;
		}
		else
		{	
			lfvtDetailDisableFields(false);
			document.getElementById('lfvt_toolbar_neu').disabled=true;
			document.getElementById('lfvt_toolbar_del').disabled=false;
		}
			
		var col = tree.columns ? tree.columns["lva_lehrveranstaltung_id"] : "lva_lehrveranstaltung_id";
		var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);

		if(lehrveranstaltung_id=='')
			return false;
	}
	catch(e) 
	{
		alert(e);
		return false;
	}
	
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
	
	//Lehrfach drop down setzen
	//document.getElementById('gridLFVTLehrfach').setAttribute('datasources',"<?php echo APP_ROOT;?>rdf/lehrfach.rdf.php");
	//debug("datasource="+document.getElementById('gridLFVTLehrfach').datasources);

	//ID in globale Variable speichern
	lfvt_detail_lehrfach_id=lehrfach;
		
	lehrfachmenulist = document.getElementById('lfvt_detail_menulist_lehrfach');
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	
	//Entfernen der alten Datasources
	var oldDatasources = lehrfachmenulist.database.GetDataSources();	
	while(oldDatasources.hasMoreElements())
	{
		lehrfachmenulist.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	lehrfachmenulist.builder.refresh();
	
	//Url zusammenbauen
	var url = '<?php echo APP_ROOT;?>rdf/lehrfach.rdf.php?lehrveranstaltung_id='+lehrveranstaltung+'&'+gettimestamp();

	//RDF holen
	var newDs  = rdfService.GetDataSource(url);
	lehrfachmenulist.database.AddDataSource(newDs);
	
	//SinkObserver hinzufuegen
	var sink = newDs.QueryInterface(Components.interfaces.nsIRDFXMLSink);  
	sink.addXMLSinkObserver(lfvt_detail_lehrfach_observer);
	
	//Daten den Feldern zuweisen
	
	document.getElementById('lfvt_detail_textbox_unr').value=unr;
	document.getElementById('lfvt_detail_textbox_lvnr').value=lvnr;	
	document.getElementById('lfvt_detail_textbox_lehrveranstaltung').value=lehrveranstaltung;
	if(lehre='Ja')
		document.getElementById('lfvt_detail_checkbox_lehre').checked=true;
	else
		document.getElementById('lfvt_detail_checkbox_lehre').checked=false;
	document.getElementById('lfvt_detail_textbox_stundenblockung').value=stundenblockung;
	document.getElementById('lfvt_detail_textbox_wochenrythmus').value=wochenrythmus;
	document.getElementById('lfvt_detail_textbox_startkw').value=start_kw;
	document.getElementById('lfvt_detail_textbox_anmerkung').value=anmerkung;
	document.getElementById('lfvt_detail_menulist_sprache').value=sprache;
	document.getElementById('lfvt_detail_menulist_lehrfach').value=lehrfach;
	document.getElementById('lfvt_detail_menulist_raumtyp').value=raumtyp;
	document.getElementById('lfvt_detail_menulist_raumtypalternativ').value=raumtyp_alt;
	document.getElementById('lfvt_detail_menulist_studiensemester').value=studiensemester;
	document.getElementById('lfvt_detail_menulist_lehrform').value=lehrform;
	document.getElementById('lfvt_detail_checkbox_new').checked=false;
	document.getElementById('lfvt_detail_textbox_lehreinheit_id').value=lehreinheit_id;
	
	//Lehreinheitmitarbeiter tree setzen
	url='<?php echo APP_ROOT;?>rdf/lehreinheitmitarbeiter.rdf.php?lehreinheit_id='+lehreinheit_id+"&"+gettimestamp();
	try
	{	
		lektortree = document.getElementById('lfvt_detail_tree_lehreinheitmitarbeiter');
		
		//Alte DS entfernen
		var oldDatasources = lektortree.database.GetDataSources();	
		while(oldDatasources.hasMoreElements())
		{
			lektortree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		lektortree.builder.refresh();
				
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		lfvt_detail_lektor_datasource = rdfService.GetDataSource(url);
		lfvt_detail_lektor_datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		lfvt_detail_lektor_datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		lektortree.database.AddDataSource(lfvt_detail_lektor_datasource);
	}
	catch(e)
	{
		debug(e);
	}
	
	//Lehreinheitgruppe tree setzen
	url='<?php echo APP_ROOT; ?>rdf/lehreinheitgruppe.rdf.php?lehreinheit_id='+lehreinheit_id+"&"+gettimestamp();
			
	try
	{	
		gruppentree = document.getElementById('lfvt_detail_tree_lehreinheitgruppe');
		
		//Alte DS entfernen
		var oldDatasources = gruppentree.database.GetDataSources();	
		while(oldDatasources.hasMoreElements())
		{
			gruppentree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		gruppentree.builder.refresh();
				
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		lfvt_detail_gruppe_datasource = rdfService.GetDataSource(url);
		lfvt_detail_gruppe_datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		lfvt_detail_gruppe_datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		gruppentree.database.AddDataSource(lfvt_detail_gruppe_datasource);
	}
	catch(e)
	{
		debug(e);
	}
}

//******** LehreinheitMitarbeiter **********//

// ****
// * Speichert die Zuteilung von Lektoren
// * zu einer Lehrveranstaltung
// ****
function lfvt_LehreinheitMitarbeiterSave()
{
	//Daten holen
	lehrfunktion = document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lehrfunktion_kurzbz').value;
	lektor = document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lektor').value;
	semesterstunden = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_semesterstunden').value;
	planstunden = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_planstunden').value;	
	stundensatz = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_stundensatz').value;
	faktor = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_faktor').value;
	anmerkung = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_anmerkung').value;
	bismelden = document.getElementById('lfvt_lehreinheitmitarbeiter_checkbox_bismelden').checked;
	
	//Request absetzen
	var req = new phpRequest('lfvtCUD.php','','');
	
	req.add('type','lehreinheit_mitarbeiter_add');
	req.add('do','update');
	lehreinheit_id = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_lehreinheit_id').value;
	mitarbeiter_uid = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_mitarbeiter_uid').value;
	req.add('lehreinheit_id',lehreinheit_id);
		
	req.add('lehrfunktion_kurzbz', lehrfunktion);
	req.add('mitarbeiter_uid', lektor);
	req.add('mitarbeiter_uid_old', mitarbeiter_uid);
	req.add('semesterstunden', semesterstunden);
	req.add('planstunden', planstunden);
	req.add('stundensatz', stundensatz);
	req.add('faktor', faktor);
	req.add('anmerkung', anmerkung);
	req.add('bismelden', bismelden);
	req.add('lehreinheit_id', lehreinheit_id);
		
	var response = req.executePOST();
	if (response!='ok') 
	{
		alert(response);
	} 
	else 
	{
		lfvt_lektor_treerefresh()
	}
}

// ****
// * Loescht die Zuteilung eines Lektoren zu einer Lehreinheit
// ****
function lfvt_LehreinheitMitarbeiterDel()
{
	tree = document.getElementById('lfvt_detail_tree_lehreinheitmitarbeiter');

	//Nachsehen ob Mitarbeiter markiert wurde
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
	{
		alert('Bitte zuerst einen Mitarbeiter markieren');
		return false;
	}

	try
	{
		//UID holen
		var col = tree.columns ? tree.columns["lfvt_detail_tree_lehreinheitmitarbeiter-col-mitarbeiter_uid"] : "lfvt_detail_tree_lehreinheitmitarbeiter-col-mitarbeiter_uid";
		var uid=tree.view.getCellText(idx,col);
		//Lehreinheit_id holen
		var col = tree.columns ? tree.columns["lfvt_detail_tree_lehreinheitmitarbeiter-col-lehreinheit_id"] : "lfvt_detail_tree_lehreinheitmitarbeiter-col-lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(idx,col);
	}
	catch(e)
	{
		alert(e);
		return false;
	}
	
	var req = new phpRequest('lfvtCUD.php','','');

	req.add('type', 'lehreinheit_mitarbeiter_del');
	req.add('lehreinheit_id', lehreinheit_id);
	req.add('mitarbeiter_uid', uid);
	
	var response = req.executePOST();
	if (response!='ok') 
	{
		alert(response);
	} 
	else 
	{
		//refresh des Trees
		lfvt_lektor_treerefresh();
	}
}

// ****
// * Wenn bei den Lektorenzuordnungen Felder bearbeitet werden, 
// * dann wird der Speichern Button aktiviert
// ****
function lfvt_LehreinheitMitarbeiterValueChanged()
{
	document.getElementById('lfvt_lehreinheitmitarbeiter_save').disabled=false;
}

// ****
// * deaktiviert/aktiviert die Lektorendetails und
// * loescht den Inhalt der Felder
// * wenn val=false dann werden die Felder deaktiviert
// * wenn val=true dann werden die Felder aktiviert
// ****
function lfvt_LehreinheitMitarbeiterDisableFields(val)
{
	//Felder Leeren
	document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lehrfunktion_kurzbz').value='lektor';
	//document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lektor').value='';
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_semesterstunden').value='';
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_planstunden').value='';
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_stundensatz').value='';
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_faktor').value='';
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_anmerkung').value='';
	document.getElementById('lfvt_lehreinheitmitarbeiter_checkbox_bismelden').checked=false;
	
	//Felder aktivieren/deaktivieren
	document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lehrfunktion_kurzbz').disabled=val;
	document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lektor').disabled=val;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_semesterstunden').disabled=val;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_planstunden').disabled=val;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_stundensatz').disabled=val;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_faktor').disabled=val;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_anmerkung').disabled=val;
	document.getElementById('lfvt_lehreinheitmitarbeiter_checkbox_bismelden').disabled=val;
}

// ****
// * Bei Auswaehlen eines Mitarbeiters werden zu zugehoerigen
// * Details geladen und angezeigt
// ****
function lfvt_LehreinheitMitarbeiterAuswahl()
{
	tree = document.getElementById('lfvt_detail_tree_lehreinheitmitarbeiter');
	
	//Falls kein Eintrag gewaehlt wurde, den ersten auswaehlen
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
		idx = 0;

	try
	{
		//Lehreinheit_id holen
		var col = tree.columns ? tree.columns["lfvt_detail_tree_lehreinheitmitarbeiter-col-lehreinheit_id"] : "lfvt_detail_tree_lehreinheitmitarbeiter-col-lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(idx,col);
		
		//Mitarbeiter_uid holen
		var col = tree.columns ? tree.columns["lfvt_detail_tree_lehreinheitmitarbeiter-col-mitarbeiter_uid"] : "lfvt_detail_tree_lehreinheitmitarbeiter-col-mitarbeiter_uid";
		var mitarbeiter_uid=tree.view.getCellText(idx,col);
	} 
	catch(e)
	{		
		return false;
	}
		
	// Url zum RDF
	var url="<?php echo APP_ROOT; ?>rdf/lehreinheitmitarbeiter.rdf.php?"+gettimestamp();
	
	//RDF laden
	var req = new phpRequest(url,'','');
	req.add('lehreinheit_id',lehreinheit_id);
	req.add('mitarbeiter_uid',mitarbeiter_uid);
	
	var response = req.execute();
	
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	// XML in Datasource parsen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/lehreinheitmitarbeiter/liste');

	// Daten aus RDF auslesen
	dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/lehreinheitmitarbeiter/" + lehreinheit_id + "/"+ mitarbeiter_uid);
   	var predicateNS = "http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf";

	//Daten in Variablen speichern
	lehrfunktion_kurzbz = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#lehrfunktion_kurzbz" ));
	semesterstunden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#semesterstunden" ));
	planstunden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#planstunden" ));
	stundensatz = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#stundensatz" ));
	faktor = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#faktor" ));
	anmerkung = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#anmerkung" ));
	bismelden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#bismelden" ));
	
	//Felder aktivieren
	lfvt_LehreinheitMitarbeiterDisableFields(false);
	
	//Felder befuellen
	document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lehrfunktion_kurzbz').value=lehrfunktion_kurzbz;
	document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lektor').value=mitarbeiter_uid;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_semesterstunden').value=semesterstunden;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_planstunden').value=planstunden;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_stundensatz').value=stundensatz;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_faktor').value=faktor;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_anmerkung').value=anmerkung;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_lehreinheit_id').value=lehreinheit_id;
	document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_mitarbeiter_uid').value=mitarbeiter_uid;
	
	if(bismelden='Ja')
		document.getElementById('lfvt_lehreinheitmitarbeiter_checkbox_bismelden').checked=true;
	else
		document.getElementById('lfvt_lehreinheitmitarbeiter_checkbox_bismelden').checked=false;
}

// ****
// * Refresht den Lehreinheitmitarbeiter Tree
// ****
function lfvt_lektor_treerefresh()
{	
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    try
    {
    	lfvt_detail_lektor_datasource.Refresh(true); //Blocking
    	lektortree = document.getElementById('lfvt_detail_tree_lehreinheitmitarbeiter');
    	lektortree.builder.rebuild();
    }
    catch(e)
    {
    	debug(e);
    }
}

// ************* GRUPPEN ******************** //
  
// ****
// * Loescht die Zuordnung einer Gruppe zu einer
// * Lehreinheit
// ****
function lfvt_LehreinheitGruppeDel()
{
	tree = document.getElementById('lfvt_detail_tree_lehreinheitgruppe');

	//Nachsehen ob Gruppe markiert wurde
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
	{
		alert('Bitte zuerst eine Gruppe markieren');
		return false;
	}

	try
	{
		//Lehreinheit_id holen
		var col = tree.columns ? tree.columns["lfvt_detail_tree_lehreinheitgruppe-col-lehreinheitgruppe_id"] : "lfvt_detail_tree_lehreinheitgruppe-col-lehreinheitgruppe_id";
		var lehreinheitgruppe_id=tree.view.getCellText(idx,col);
	}
	catch(e)
	{
		alert(e);
		return false;
	}
	
	var req = new phpRequest('lfvtCUD.php','','');
	neu = document.getElementById('lfvt_detail_checkbox_new').checked;

	req.add('type', 'lehreinheit_gruppe_del');
	req.add('lehreinheitgruppe_id', lehreinheitgruppe_id);
	
	var response = req.executePOST();
	if (response!='ok') 
	{
		alert(response);
	} 
	else 
	{
		//refresh des Trees
		lfvt_detail_gruppe_treerefresh();
	}		
}

// ****
// * Gruppen Tree Refreshen
// ****
function lfvt_detail_gruppe_treerefresh()
{
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    try
    {
    	lfvt_detail_gruppe_datasource.Refresh(true); //Blocking
    	gruppentree = document.getElementById('lfvt_detail_tree_lehreinheitgruppe');
    	gruppentree.builder.rebuild();
    }
    catch(e)
    {
    	debug(e);
    }
}
