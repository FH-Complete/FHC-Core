<?php
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');

$conn = pg_pconnect(CONN_STRING);

$user = get_uid();
loadVariables($conn, $user);
?>
var lfvt_detail_lehrfach_id;

function getDropDownValue(obj) 
{
	//var list = document.getElementById(obj.name);
	//var selectedText = list.selectedItem.label;
	//alert(selectedText);
	return obj.name;
}

function listElementHandlers(aObj) 
{
	if(!aObj)
       return null;
    for(var list in aObj)
       if(list.match(/^on/))
         dump(list+'\n');
}

function lfvt_tree_refresh()
{
	var tree = document.getElementById('treeLFVT');
	tree.builder.refresh();
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
	     	//Die richtige Gruppe markieren	
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
	//document.getElementById('lfvt_detail_menulist_lehrfach').value='';
	document.getElementById('lfvt_detail_menulist_raumtyp').value='Dummy';
	document.getElementById('lfvt_detail_menulist_raumtypalternativ').value='Dummy';
	document.getElementById('lfvt_detail_menulist_studiensemester').value='<?php echo $semester_aktuell; ?>';
	document.getElementById('lfvt_detail_menulist_lehrform').value='UE';
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

function lfvtDetailSave()
{
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
			
			//Lehreinheitmitarbeiter tree deaktivieren
			document.getElementById('lfvt_detail_tree_lehreinheitmitarbeiter').datasources='';
			document.getElementById('lfvt_detail_tree_lehreinheitgruppe').datasources='';
			document.getElementById('lfvt_lehreinheitmitarbeiter_button_add').disabled=true;
			document.getElementById('lfvt_lehreinheitmitarbeiter_button_del').disabled=true;
			
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
			document.getElementById('lfvt_lehreinheitmitarbeiter_button_add').disabled=false;
			document.getElementById('lfvt_lehreinheitmitarbeiter_button_del').disabled=false;
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

	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
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
	url='../rdf/lehreinheitmitarbeiter.rdf.php?lehreinheit_id='+lehreinheit_id+"&"+gettimestamp();
	document.getElementById('lfvt_detail_tree_lehreinheitmitarbeiter').setAttribute('datasources',url);
	
	//Lehreinheitgruppe tree setzen
	url='../rdf/lehreinheitgruppe.rdf.php?lehreinheit_id='+lehreinheit_id+"&"+gettimestamp();
	document.getElementById('lfvt_detail_tree_lehreinheitgruppe').setAttribute('datasources',url);
}

/**
 * Daten aus Formular holen und evt. speichern

Lehrveranstaltung.prototype.updateData = function() {
	if (document.getElementById('gridStudentenUID').value!=this.uid) {
		this.uid = document.getElementById('gridStudentenUID').value;
		this.dataChanged = true;
	};
	if (document.getElementById('gridStudentenTitel').value!=this.titel) {
		this.titel = document.getElementById('gridStudentenTitel').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenVornamen').value!=this.vornamen) {
		this.vornamen = document.getElementById('gridStudentenVornamen').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenNachname').value!=this.nachname) {
		this.nachname = document.getElementById('gridStudentenNachname').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenMatrikelnummer').value!=this.matrikelnummer)  {
		this.matrikelnummer = document.getElementById('gridStudentenMatrikelnummer').value;
		this.dataChanged = true;
	}
	//alert(document.getElementById('gridStudentenMatrikelnummer').value);
	if (document.getElementById('gridStudentenGeburtsdatum').value!=this.geburtsdatum) {
		// todo validation
		this.geburtsdatum = document.getElementById('gridStudentenGeburtsdatum').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenGeburtsort').value!=this.geburtsort) {
		this.geburtsort = document.getElementById('gridStudentenGeburtsort').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenGeburtszeit').value!=this.geburtszeit) {
		// todo validation
		this.geburtszeit = document.getElementById('gridStudentenGeburtszeit').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenHomepage').value!=this.homepage) {
		this.homepage = document.getElementById('gridStudentenHomepage').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenEmail').value!=this.email) {
		this.email = document.getElementById('gridStudentenEmail').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenSemester').value!=this.semester) {
		this.semester = document.getElementById('gridStudentenSemester').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenVerband').value!=this.verband) {
		this.verband = document.getElementById('gridStudentenVerband').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenGruppe').value!=this.gruppe) {
		this.gruppe = document.getElementById('gridStudentenGruppe').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridLehrform').value!=this.lehrform) {
		this.lehrform = document.getElementById('gridLehrform').value;
		this.dataChanged = true;
	}
	if (!((document.getElementById('gridStudentenAktiv').checked && this.aktiv=='True') ||
		(!document.getElementById('gridStudentenAktiv').checked && this.aktiv=='False'))) {
		this.aktiv = document.getElementById('gridStudentenAktiv').checked?'True':'False';
		this.dataChanged = true;
	}
	alert(this.dataChanged?'dataChanged':'nix changed');
} */

/**
 * Student anzeigen

Lehrveranstaltung.prototype.show = function() {
	document.getElementById('gridStudentenUID').value = this.uid;
	document.getElementById('gridStudentenTitel').value = this.titel;
	document.getElementById('gridStudentenVornamen').value = this.vornamen;
	document.getElementById('gridStudentenNachname').value = this.nachname;
	document.getElementById('gridStudentenMatrikelnummer').value = this.matrikelnummer;
	document.getElementById('gridStudentenGeburtsdatum').value = this.geburtsdatum;
	document.getElementById('gridStudentenGeburtsort').value = this.geburtsort;
	document.getElementById('gridStudentenGeburtszeit').value = this.geburtszeit;
	document.getElementById('gridStudentenHomepage').value = this.homepage;
	document.getElementById('gridStudentenEmail').value = this.email;
	document.getElementById('gridStudentenSemester').value = this.semester;
	document.getElementById('gridStudentenVerband').value = this.verband;
	document.getElementById('gridStudentenGruppe').value = this.gruppe;
	document.getElementById('gridStudentenStgBezeichnung').value = this.stg_bezeichnung;
	document.getElementById('gridStudentenAktiv').checked = (this.aktiv=='True'?true:false);
} */

//******** LehreinheitMitarbeiter **********//

// ****
// * Speichert die Zuteilung von Lektoren
// * zu einer Lehrveranstaltung
// ****
function lfvt_LehreinheitMitarbeiterSave()
{
	lehrfunktion = document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lehrfunktion_kurzbz').value;
	lektor = document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lektor').value;
	semesterstunden = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_semesterstunden').value;
	planstunden = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_planstunden').value;	
	stundensatz = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_stundensatz').value;
	faktor = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_faktor').value;
	anmerkung = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_anmerkung').value;
	bismelden = document.getElementById('lfvt_lehreinheitmitarbeiter_checkbox_bismelden').checked;
	lehreinheit_id = document.getElementById('lfvt_lehreinheitmitarbeiter_textbox_lehreinheit_id').value;
	neu = document.getElementById('lfvt_lehreinheitmitarbeiter_checkbox_new').checked;
	
	var req = new phpRequest('lfvtCUD.php','','');
	neu = document.getElementById('lfvt_detail_checkbox_new').checked;
	
	req.add('type','lehreinheit_mitarbeiter_add');
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
		
	req.add('lehrfunktion_kurzbz', lehrfunktion);
	req.add('mitarbeiter_uid', lektor);
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
		document.getElementById('lfvt_lehreinheitmitarbeiter_checkbox_new').checked=false;
		alert('Daten wurden gespeichert');
	}
}

// ****
// * Legt eine neue Zuordnung von Lektor zu 
// * einer Lehreinheit an.
// ****
function lfvt_LehreinheitMitarbeiterAdd()
{
	alert('geht noch nicht');
}

// ****
// * Loescht die Zuteilung eines Lektoren zu einer Lehreinheit
// ****
function lfvt_LehreinheitMitarbeiterDel()
{
	alert('geht noch nicht');
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
	document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lehrfunktion_kurzbz').value='';
	document.getElementById('lfvt_lehreinheitmitarbeiter_menulist_lektor').value='';
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
	var url="<?php echo APP_ROOT; ?>rdf/lehreinheitmitarbeiter.rdf.php";
	
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
	if(bismelden='Ja')
		document.getElementById('lfvt_lehreinheitmitarbeiter_checkbox_bismelden').checked=true;
	else
		document.getElementById('lfvt_lehreinheitmitarbeiter_checkbox_bismelden').checked=false;
}

// ************* GRUPPEN ******************** //

// ****
// * Observer fuer GruppenTree (testing)
// ****
var lfvt_detail_gruppe_observer = {
	     onBeginLoad: function(aSink) { },
	     onInterrupt: function(aSink) { },
	     onResume:    function(aSink) { },
	     onEndLoad:   function(aSink) { 	    
	     	tree = document.getElementById('lfvt_detail_tree_lehreinheitgruppe');
	     	tree.builder.rebuild();
	     },
	     onError: function(aSink, aStatus, aErrorMsg) { }
	  };
	  
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
	}		
}

// ****
// * Fuegt eine Gruppe zu einer
// * Lehreinheit hinzu
// ****
function lfvt_LehreinheitGruppeAdd()
{

}


/**
 * (Wenn gedroppt wird)
 * Speichert die Zuteilung einer Gruppe zu einer Lehreinheit
 */
function lfvt_detail_gruppe_dragdrop(event)
{   
    event.stopPropagation();
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect")   
    try {
        dragservice_ds = Components.classes["@mozilla.org/widget/dragservice;1"].getService(Components.interfaces.nsIDragService);
    }
    catch (e)
    {
    	debug('treeDragDrop: e');
    }
    
    var ds = dragservice_ds;
    var ses = ds.getCurrentSession()
    var sourceNode = ses.sourceNode
    var lehreinheit_id = document.getElementById('lfvt_detail_textbox_lehreinheit_id').value;
    var row = { }
    var col = { }
    var child = { }
   
    if(lehreinheit_id=='')
    	return false;
    	
	//Quelle holen (Gruppe)
	var flavourset = new FlavourSet();
    flavourset.appendFlavour("gruppe");

    var transferData = nsTransferable.get(flavourset, getDragData, true);

    quell_gruppe=transferData.first.first.data;
    var arr = quell_gruppe.split("&");
    var stg_kz = arr[0];
    var sem = arr[1];
    var ver = arr[2];
    var grp = arr[3];
    var gruppe = arr[4];
    //alert("stg: "+stg_kz+" sem: "+sem+" ver: "+ver+" grp: "+grp+" gruppe: "+gruppe+" TO Lehreinheit:"+lehreinheit_id);
    
    var req = new phpRequest('lfvtCUD.php','','');
	neu = document.getElementById('lfvt_detail_checkbox_new').checked;
	
	req.add('type','lehreinheit_gruppe_add');
			
	req.add('lehreinheit_id', lehreinheit_id);
	req.add('studiengang_kz', stg_kz);
	req.add('semester', sem);
	req.add('verband', ver);
	req.add('gruppe', grp);
	req.add('gruppe_kurzbz', gruppe);
			
	var response = req.executePOST();
	if (response!='ok') 
	{
		alert(response);
	} 
	else 
	{
		//GruppenTree Refreshen
		tree = document.getElementById('lfvt_detail_tree_lehreinheitgruppe');
		tree.builder.addListener(lfvt_detail_gruppe_observer);
		tree.builder.refresh();
	}
}

var dragservice_ds;
/**
 * Holt die Daten aus der DragSession
 */
function getDragData(aFlavourSet)
{
	var ds = dragservice_ds;
	var ses = ds.getCurrentSession()
	
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	var supportsArray = Components.classes["@mozilla.org/supports-array;1"]
    	.createInstance(Components.interfaces.nsISupportsArray);

  	for (var i = 0; i < ses.numDropItems; ++i)
    {
      	var trans = nsTransferable.createTransferable();
      	for (var j = 0; j < aFlavourSet.flavours.length; ++j)
        	trans.addDataFlavor(aFlavourSet.flavours[j].contentType);
      	ses.getData(trans, i);
      	supportsArray.AppendElement(trans);
    }
  	return supportsArray;
}

/**
 * Drag ueber den Tree
 */
function lfvt_detail_gruppe_dragover( event )
{
  var validFlavor = false;
  var dragSession = null;

  var targetNode = event.target 
  	  	
  netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
  var dragService = Components.classes["@mozilla.org/widget/dragservice;1"].
                    getService().QueryInterface(Components.interfaces.nsIDragService);
  
  if( dragService ) 
  {
    dragSession = dragService.getCurrentSession();
    
    if( dragSession ) 
    {
		if( dragSession.isDataFlavorSupported("gruppe") )
    		validFlavor = true;
    	else if ( dragSession.isDataFlavorSupported("gruppe") )
        	validFlavor = true;
      
		if ( validFlavor ) 
		{
	        //Style action	        
			//targetNode.style.backgroundColor = "red";
		 	//targetNode.style.color = "red";
		  	//event.originalTarget.style.color = "red";
	        dragSession.canDrop = true;
	        event.stopPropagation();
      	}
    }
  }
}