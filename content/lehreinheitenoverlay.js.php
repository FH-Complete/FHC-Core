<?php
include("../vilesci/config.inc.php"); 	

?>
var MitarbeiterLehreinheitenSelect_id=0;
var currentMitarbeiterLehreinheitenAuswahlURL = '';
var sleep_time=350;
var lehreinheiten_gruppe_id=0;
var lehreinheiten_ausbildungssemester_id=0;
var LehreinheitenSelectLehreinheit_id;
//Eingabefelder deaktivieren
window.setTimeout('LehreinheitenDetailDisable(true)',sleep_time);


/**
 * Observer fuer das Laden von gruppe.rdf.php
 */
var LehreinheitenGruppeMenulistObserver = {
	     onBeginLoad: function(aSink) { },
	     onInterrupt: function(aSink) { },
	     onResume:    function(aSink) { },
	     onEndLoad:   function(aSink) { 	    
	     	//Die richtige Gruppe markieren	
	     	if(lehreinheiten_gruppe_id!='')
		 		document.getElementById('menulist-lehreinheiten-detail-gruppe').value=lehreinheiten_gruppe_id;
		 	else
		 	{
		 		document.getElementById('menulist-lehreinheiten-detail-gruppe').value='';
		 		document.getElementById('menulist-lehreinheiten-detail-gruppe').label='';
		 	}
	     },
	     onError: function(aSink, aStatus, aErrorMsg) { 
	     	alert('Bei der Datenuebertragung ist ein Fehler Aufgetreten. Bitte Versuchen Sie es erneut.'); 
	     }
	  };
/**
 * Observer fuer das Laden von ausbildungssemester.rdf.php
 */
var LehreinheitenAusbildungssemesterMenulistObserver = {
	     onBeginLoad: function(aSink) { },
	     onInterrupt: function(aSink) { },
	     onResume:    function(aSink) { },
	     onEndLoad:   function(aSink) {  
	     	//Das richtige Ausbildungssemester markieren
		 	document.getElementById('menulist-lehreinheiten-detail-ausbildungssemester').value=lehreinheiten_ausbildungssemester_id;	
	     },
	     onError: function(aSink, aStatus, aErrorMsg) { 
	     	alert('Bei der Datenuebertragung ist ein Fehler Aufgetreten. Bitte Versuchen Sie es erneut.'); 
	     }
	  };

/**
 * Laedt die Daten der ausgewählten Lehreinheit
 * und schreibt diese in die Formularfelder
 */
function LehreinheitenTreeAuswahl()
{
	var tree=document.getElementById('tree-liste-lehreinheiten');
		
	//Falls kein Eintrag gewaehlt wurde, den ersten auswaehlen
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
		idx = 0;
		
	try
	{
	//Lehreinheit_id holen
	var col = tree.columns ? tree.columns["tree-liste-lehreinheiten-col-lehreinheit_id"] : "tree-liste-lehreinheiten-col-lehreinheit_id";
	var lehreinheit_id=tree.view.getCellText(idx,col);
	}
	catch(e)
	{
	   return false;
	}
	LehreinheitenDetailDisable(false);
	
	// RDF mit den Lehreinheiten vom Server holen
	// Url zum RDF
	var url="<?php echo APP_ROOT; ?>rdf/fas/lehreinheiten.rdf.php?lehreinheit_id="+lehreinheit_id;

	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	httpRequest.open("GET", url, false, '','');
	httpRequest.send('');
	
	switch(httpRequest.readyState)
	{
		case 1,2,3: alert('Bad Ready State: '+httpRequest.status); //404 ErrorCodes etc
			        return false;
		            break;

		case 4:		if(httpRequest.status !=200)
			        {
				        alert('The server respond with a bad status code: '+httpRequest.status);
				        return false;
			        }
			        else
			        {
				        var response = httpRequest.responseText;
			        }
		            break;
	}

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	// XML in Datasource parsen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/lehreinheiten/alle');

	// Daten aus RDF auslesen
	dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/lehreinheiten/" + lehreinheit_id);
   	var predicateNS = "http://www.technikum-wien.at/lehreinheiten/rdf";

	studiengang_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#studiengang_id" ));
	studiensemester_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#studiensemester_id" ));
	lehrveranstaltung_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#lehrveranstaltung_id" ));
	fachbereich_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#fachbereich_id" ));
	ausbildungssemester_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#ausbildungssemester_id" ));
	lehreinheit_fk = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#lehreinheit_fk" ));
	lehrform_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#lehrform_id" ));
	gruppe_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#gruppe_id" ));
	nummer = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#nummer" ));
	bezeichnung = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#bezeichnung" ));
	kurzbezeichnung = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#kurzbezeichnung" ));
	semesterwochenstunden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#semesterwochenstunden" ));
	gesamtstunden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#gesamtstunden" ));
	plankostenprolektor = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#plankostenprolektor" ));
	planfaktor = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#planfaktor" ));
	planlektoren = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#planlektoren" ));
	raumtyp_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#raumtyp_id" ));
	raumtypalternativ_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#raumtypalternativ_id" ));
	bemerkungen = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#bemerkungen" ));
	wochenrythmus = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#wochenrythmus" ));
	kalenderwoche = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#kalenderwoche" ));
	stundenblockung = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#stundenblockung" ));
	koordinator_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#koordinator_id" ));
	
	
	//*** Gruppen Menulist Fuellen
	
	//ID in globale Variable speichern
	lehreinheiten_gruppe_id=gruppe_id;
		
	grpmenulist = document.getElementById('menulist-lehreinheiten-detail-gruppe');
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	
	//Entfernen der alten Datasources
	var oldDatasources = grpmenulist.database.GetDataSources();	
	while(oldDatasources.hasMoreElements())
	{
		grpmenulist.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	grpmenulist.builder.refresh();
	
	//Url zusammenbauen
	var url = '<?php echo APP_ROOT; ?>rdf/fas/gruppen.rdf.php?stg='+studiengang_id+'&ausbsem='+ausbildungssemester_id+'&'+gettimestamp();

	//RDF holen
	var newDs  = rdfService.GetDataSource(url);
	grpmenulist.database.AddDataSource(newDs);
	
	//SinkObserver hinzufuegen
	var sink = newDs.QueryInterface(Components.interfaces.nsIRDFXMLSink);  
	sink.addXMLSinkObserver(LehreinheitenGruppeMenulistObserver);	
	
	//*** Ausbildungssemester Menulist setzen
	
	//ID in globale Variable speichern
	lehreinheiten_ausbildungssemester_id=ausbildungssemester_id;
	
	ausbsemmenulist = document.getElementById('menulist-lehreinheiten-detail-ausbildungssemester');
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	
	//Entfernen der alten Datasources
	var oldDatasources = ausbsemmenulist.database.GetDataSources();	
	while(oldDatasources.hasMoreElements())
	{
		grpmenulist.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	ausbsemmenulist.builder.refresh();
	
	//Url zusammenbauen
	var url = '<?php echo APP_ROOT; ?>rdf/fas/ausbildungssemester.rdf.php?stg='+studiengang_id+'&'+gettimestamp();

	//RDF holen
	var newDs  = rdfService.GetDataSource(url);
	ausbsemmenulist.database.AddDataSource(newDs);
	
	//SinkObserver hinzufuegen
	var sink = newDs.QueryInterface(Components.interfaces.nsIRDFXMLSink);  
	sink.addXMLSinkObserver(LehreinheitenAusbildungssemesterMenulistObserver);	
	
	
	//***MitarbeiterAuswahl Menulist setzen
	var url='<?php echo APP_ROOT; ?>rdf/fas/mitarbeiterlehreinheitenauswahl.rdf.php?stg='+studiengang_id+'&fb='+fachbereich_id;
	currentMitarbeiterLehreinheitenAuswahlURL = url;
	document.getElementById('menulist-lehreinheiten-detail-mitarbeiterauswahl').setAttribute('datasources',url+'&'+gettimestamp());
	
	//***MitarbeiterLehreinheiten tree setzen
	url='<?php echo APP_ROOT; ?>rdf/fas/mitarbeiterlehreinheiten.rdf.php?lehreinheit_id='+lehreinheit_id;
	document.getElementById('tree-liste-mitarbeiterlehreinheiten').setAttribute('datasources',url);
	document.getElementById('textbox-lehreinheiten-detail-lektoren-lehreinheit_id').value=lehreinheit_id;
	
	document.getElementById('textbox-lehreinheiten-detail-studiengang').value=studiengang_id;
	document.getElementById('textbox-lehreinheiten-detail-studiensemester').value=studiensemester_id;
	document.getElementById('textbox-lehreinheiten-detail-lehrveranstaltung').value=lehrveranstaltung_id;
	document.getElementById('textbox-lehreinheiten-detail-lehreinheit_fk').value=lehreinheit_fk;
	document.getElementById('textbox-lehreinheiten-detail-nummer').value=nummer;
	document.getElementById('textbox-lehreinheiten-detail-bezeichnung').value=bezeichnung;
	document.getElementById('textbox-lehreinheiten-detail-kurzbezeichnung').value=kurzbezeichnung;
	document.getElementById('textbox-lehreinheiten-detail-sws').value=semesterwochenstunden;
	document.getElementById('textbox-lehreinheiten-detail-gesamtstunden').value=gesamtstunden;
	document.getElementById('textbox-lehreinheiten-detail-plankostenprolektor').value=plankostenprolektor;
	document.getElementById('textbox-lehreinheiten-detail-planfaktor').value=planfaktor;
	document.getElementById('textbox-lehreinheiten-detail-planlektoren').value=planlektoren;	
	document.getElementById('textbox-lehreinheiten-detail-bemerkungen').value=bemerkungen;
	document.getElementById('textbox-lehreinheiten-detail-kalenderwoche').value=kalenderwoche;
	document.getElementById('textbox-lehreinheiten-detail-stundenblockung').value=stundenblockung;
		
	document.getElementById('menulist-lehreinheiten-detail-koordinator').value=koordinator_id;
	document.getElementById('menulist-lehreinheiten-detail-fachbereich').value=fachbereich_id;	
	document.getElementById('menulist-lehreinheiten-detail-lehrform').value=lehrform_id;
	document.getElementById('menulist-lehreinheiten-detail-wochenrythmus').value=wochenrythmus;
	//Wenn ein raumtyp vorhanden ist
	if(raumtyp_id!='')
	{
		//Value setzen und die Menulist refreshen weil es manchmal vorkommen kann das der markierte Datensatz
		//nicht angezeigt wird.
		document.getElementById('menulist-lehreinheiten-detail-raumtyp').value=raumtyp_id;
		document.getElementById('menulist-lehreinheiten-detail-raumtyp').builder.refresh();
	}
	else
	{
		//Wenn kein raumtyp vorhanden ist wird value und label auf '' gesetzt
		document.getElementById('menulist-lehreinheiten-detail-raumtyp').value='';
		document.getElementById('menulist-lehreinheiten-detail-raumtyp').label='';
	}
	if(raumtypalternativ_id!='')
	{
		document.getElementById('menulist-lehreinheiten-detail-raumtypalternativ').value=raumtypalternativ_id;
		document.getElementById('menulist-lehreinheiten-detail-raumtypalternativ').builder.refresh();
	}	
	else
	{
		document.getElementById('menulist-lehreinheiten-detail-raumtypalternativ').value='';
		document.getElementById('menulist-lehreinheiten-detail-raumtypalternativ').label='';
	}
	
	//im Lektor Tab die Felder Deaktivieren
	EmptyAndDisableLektorFields();
}

/**
 * Sorgt dafuer, dass nach dem Sortieren der gleiche Datensatz markiert ist wie vor dem Sortieren
 */
function LehreinheitenTreeSort()
{
	//Aktuell markierte ID holen
	tree = document.getElementById('tree-liste-lehreinheiten');
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
		idx = 0;
	
	col = tree.columns ? tree.columns["tree-liste-lehreinheiten-col-lehreinheit_id"] : "tree-liste-lehreinheiten-col-lehreinheit_id";
	LehreinheitenSelectLehreinheit_id = tree.view.getCellText(idx,col);
	//warten und ID wieder setzen
	window.setTimeout("LehreinheitenSelectLehreinheit()",10);
}

//Beim Aendern eines Eingabefeldes
function LehreinheitenDetailValueChange()
{
	//Speichern Button aktivieren
}

/**
 * Disabled/Enabled alle Formularfelder
 */
function LehreinheitenDetailDisable(value)
{
	document.getElementById('menulist-lehreinheiten-detail-fachbereich').disabled=value;
	document.getElementById('menulist-lehreinheiten-detail-ausbildungssemester').disabled=value;
	document.getElementById('textbox-lehreinheiten-detail-lehreinheit_fk').disabled=value;
	document.getElementById('menulist-lehreinheiten-detail-lehrform').disabled=value;	
	document.getElementById('textbox-lehreinheiten-detail-nummer').disabled=value;
	document.getElementById('textbox-lehreinheiten-detail-bezeichnung').disabled=value;
	document.getElementById('textbox-lehreinheiten-detail-kurzbezeichnung').disabled=value;
	document.getElementById('textbox-lehreinheiten-detail-sws').disabled=value;
	document.getElementById('textbox-lehreinheiten-detail-gesamtstunden').disabled=value;
	document.getElementById('textbox-lehreinheiten-detail-plankostenprolektor').disabled=value;
	document.getElementById('textbox-lehreinheiten-detail-planfaktor').disabled=value;
	document.getElementById('textbox-lehreinheiten-detail-planlektoren').disabled=value;
	document.getElementById('menulist-lehreinheiten-detail-raumtyp').disabled=value;
	document.getElementById('menulist-lehreinheiten-detail-raumtypalternativ').disabled=value;
	document.getElementById('textbox-lehreinheiten-detail-bemerkungen').disabled=value;
	document.getElementById('menulist-lehreinheiten-detail-wochenrythmus').disabled=value;
	document.getElementById('textbox-lehreinheiten-detail-kalenderwoche').disabled=value;
	document.getElementById('textbox-lehreinheiten-detail-stundenblockung').disabled=value;
	document.getElementById('menulist-lehreinheiten-detail-koordinator').disabled=value;
	document.getElementById('menulist-lehreinheiten-detail-gruppe').disabled=value;
}

/**
 * Speichert den aktuellen Datensatz
 */
function LehreinheitenDetailSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";
		
	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	//Daten holen
	lehreinheit_id = document.getElementById('textbox-lehreinheiten-detail-lektoren-lehreinheit_id').value;
	studiengang_id = document.getElementById('textbox-lehreinheiten-detail-studiengang').value;
	studiensemester_id = document.getElementById('textbox-lehreinheiten-detail-studiensemester').value;
	lehrveranstaltung_id = document.getElementById('textbox-lehreinheiten-detail-lehrveranstaltung').value;
	fachbereich_id = document.getElementById('menulist-lehreinheiten-detail-fachbereich').value;
	ausbildungssemester_id = document.getElementById('menulist-lehreinheiten-detail-ausbildungssemester').value;
	lehreinheit_fk = document.getElementById('textbox-lehreinheiten-detail-lehreinheit_fk').value;
	lehrform_id = document.getElementById('menulist-lehreinheiten-detail-lehrform').value;
	nummer = document.getElementById('textbox-lehreinheiten-detail-nummer').value;
	bezeichnung = document.getElementById('textbox-lehreinheiten-detail-bezeichnung').value;
	kurzbezeichnung = document.getElementById('textbox-lehreinheiten-detail-kurzbezeichnung').value;
	semesterwochenstunden = document.getElementById('textbox-lehreinheiten-detail-sws').value;
	gesamtstunden = document.getElementById('textbox-lehreinheiten-detail-gesamtstunden').value;
	plankostenprolektor = document.getElementById('textbox-lehreinheiten-detail-plankostenprolektor').value;
	planfaktor = document.getElementById('textbox-lehreinheiten-detail-planfaktor').value;
	planlektoren = document.getElementById('textbox-lehreinheiten-detail-planlektoren').value;
	raumtyp_id = document.getElementById('menulist-lehreinheiten-detail-raumtyp').value;
	raumtypalternativ_id = document.getElementById('menulist-lehreinheiten-detail-raumtypalternativ').value;
	bemerkungen = document.getElementById('textbox-lehreinheiten-detail-bemerkungen').value;
	wochenrythmus = document.getElementById('menulist-lehreinheiten-detail-wochenrythmus').value;
	kalenderwoche = document.getElementById('textbox-lehreinheiten-detail-kalenderwoche').value;
	stundenblockung = document.getElementById('textbox-lehreinheiten-detail-stundenblockung').value;
	koordinator_id = document.getElementById('menulist-lehreinheiten-detail-koordinator').value;
	gruppe_id = document.getElementById('menulist-lehreinheiten-detail-gruppe').value;
		
	//Parameter String zusammenbauen
	var param = 'type=lva_save';
	param = param + '&lehreinheit_id=' + encodeURI(lehreinheit_id);
	param = param + '&studiengang_id=' + encodeURI(studiengang_id);
	param = param + '&studiensemester_id=' + encodeURI(studiensemester_id);
	param = param + '&lehrveranstaltung_id=' + encodeURI(lehrveranstaltung_id);
	param = param + '&fachbereich_id=' + encodeURI(fachbereich_id);
	param = param + '&ausbildungssemester_id=' + encodeURI(ausbildungssemester_id);
	param = param + '&lehreinheit_fk=' + encodeURI(lehreinheit_fk);
	param = param + '&lehrform_id=' + encodeURI(lehrform_id);
	param = param + '&nummer=' + encodeURI(nummer);
	param = param + '&bezeichnung=' + encodeURI(bezeichnung);
	param = param + '&kurzbezeichnung=' + encodeURI(kurzbezeichnung);
	param = param + '&semesterwochenstunden=' + encodeURI(semesterwochenstunden);
	param = param + '&gesamtstunden=' + encodeURI(gesamtstunden);
	param = param + '&plankostenprolektor=' + encodeURI(plankostenprolektor);
	param = param + '&planfaktor=' + encodeURI(planfaktor);
	param = param + '&planlektoren=' + encodeURI(planlektoren);
	param = param + '&raumtyp_id=' + encodeURI(raumtyp_id);
	param = param + '&raumtypalternativ_id=' + encodeURI(raumtypalternativ_id);
	param = param + '&bemerkungen=' + encodeURI(bemerkungen);
	param = param + '&wochenrythmus=' + encodeURI(wochenrythmus);
	param = param + '&kalenderwoche=' + encodeURI(kalenderwoche);
	param = param + '&stundenblockung=' + encodeURI(stundenblockung);
	param = param + '&koordinator_id=' + encodeURI(koordinator_id);
	param = param + '&gruppe_id=' + encodeURI(gruppe_id);
	
	//Parameter schicken
	httpRequest.send(param);
	
	// Bei status 4 ist sendung Ok
	switch(httpRequest.readyState)
	{
		case 1,2,3: alert('Bad Ready State: '+httpRequest.status);
			        return false;
		            break;
	
		case 4:		if(httpRequest.status !=200)
			        {
				        alert('The server respond with a bad status code: '+httpRequest.status);
				        return false;
			        }
			        else
			        {
				        var response = httpRequest.responseText;
			        }
		            break;
	}
		
	// Returnwerte aus RDF abfragen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/dbdml');
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	               getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/dbdml/0");
	
	var predicateNS = "http://www.technikum-wien.at/dbdml/rdf";
	
	var dbdml_return = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
	var dbdml_errormsg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#errormsg" ));
		
	if(dbdml_return=='true')
	{	
		//Tree Refreshen
		RefreshLehreinheitenTree();
		//Statusbar Text setzen
		//window.setTimeout("setStatusBarText('Datensatz wurde erfolgreich gespeichert')",sleep_time);
	}
	else
	{
		alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
	}
	
}

/**
 * Laedt die Lektoren-Daten zu einer Lehreinheit
 */
function MitarbeiterLehreinheitenTreeAuswahl()
{
	tree = document.getElementById('tree-liste-mitarbeiterlehreinheiten');
	
	//Falls kein Eintrag gewaehlt wurde, den ersten auswaehlen
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
		idx = 0;

	try
	{
		//MitarbeiterLehreinheit_id holen
		var col = tree.columns ? tree.columns["tree-liste-mitarbeiterlehreinheiten-col-mitarbeiter_lehreinheit_id"] : "tree-liste-mitarbeiterlehreinheiten-col-mitarbeiter_lehreinheit_id";
		var mitarbeiter_lehreinheit_id=tree.view.getCellText(idx,col);
	} 
	catch(e)
	{		
		return false;
	}
	// RDF mit den Lehreinheiten vom Server holen
	// Url zum RDF
	var url="<?php echo APP_ROOT; ?>rdf/fas/mitarbeiterlehreinheiten.rdf.php?mitarbeiter_lehreinheit_id="+mitarbeiter_lehreinheit_id;

	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	httpRequest.open("GET", url, false, '','');
	httpRequest.send('');
	
	switch(httpRequest.readyState)
	{
		case 1,2,3: alert('Bad Ready State: '+httpRequest.status); //404 ErrorCodes etc
			        return false;
		            break;

		case 4:		if(httpRequest.status !=200)
			        {
				        alert('The server respond with a bad status code: '+httpRequest.status);
				        return false;
			        }
			        else
			        {
				        var response = httpRequest.responseText;
			        }
		            break;
	}

	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	// XML in Datasource parsen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/mitarbeiterlehreinheiten/alle');

	// Daten aus RDF auslesen
	dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/mitarbeiterlehreinheiten/" + mitarbeiter_lehreinheit_id);
   	var predicateNS = "http://www.technikum-wien.at/mitarbeiterlehreinheiten/rdf";

	//Daten in Variablen speichern
	mitarbeiter_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#mitarbeiter_id" ));
	lehrfunktion_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#lehrfunktion_id" ));
	kosten = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#kosten" ));
	faktor = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#faktor" ));
	gesamtstunden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#gesamtstunden" ));
	lehreinheit_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#lehreinheit_id" ));
	
	//Felder aktivieren
	document.getElementById('menulist-lehreinheiten-detail-mitarbeiterauswahl').disabled=false;
	document.getElementById('menulist-lehreinheiten-detail-funktion').disabled=false;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-kosten').disabled=false;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-faktor').disabled=false;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-gesamtstunden').disabled=false;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-lehreinheit_id').disabled=false;
	document.getElementById('button-lehreinheiten-detail-lektoren-save').disabled=true;
	document.getElementById('button-lehreinheiten-detail-lektoren-auswahladd').disabled=false;
	
	//Felder befuellen
	document.getElementById('menulist-lehreinheiten-detail-mitarbeiterauswahl').value=mitarbeiter_id;
	document.getElementById('menulist-lehreinheiten-detail-funktion').value=lehrfunktion_id;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-kosten').value=kosten;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-faktor').value=faktor;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-gesamtstunden').value=gesamtstunden;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-lehreinheit_id').value=lehreinheit_id;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-mitarbeiter_lehreinheit_id').value=mitarbeiter_lehreinheit_id;
		
	//Gesamtstunden aus Haupttree holen
	tree1 = document.getElementById('tree-liste-lehreinheiten');

	if(tree1.currentIndex>=0)
		idx = tree1.currentIndex;
	else
		idx = 0;
	
	//Gesamtstunden der Lehreinheit holen
	var col = tree1.columns ? tree1.columns["tree-liste-lehreinheiten-col-gesamtstunden"] : "tree-liste-lehreinheiten-col-gesamtstunden";
	var gesamt_lehreinheit=tree1.view.getCellText(idx,col);
	
	document.getElementById('textbox-lehreinheiten-detail-lektoren-differenz').value = (gesamt_lehreinheit-gesamtstunden);

}

/**
 * Leert die LektorFelder und setzt diese auf disabled
 */
function EmptyAndDisableLektorFields()
{
	document.getElementById('menulist-lehreinheiten-detail-funktion').value='';
	document.getElementById('menulist-lehreinheiten-detail-mitarbeiterauswahl').value='';
	document.getElementById('textbox-lehreinheiten-detail-lektoren-kosten').value='';
	document.getElementById('textbox-lehreinheiten-detail-lektoren-faktor').value='';
	document.getElementById('textbox-lehreinheiten-detail-lektoren-gesamtstunden').value='';
	document.getElementById('textbox-lehreinheiten-detail-lektoren-mitarbeiter_lehreinheit_id').value='';
	
	document.getElementById('menulist-lehreinheiten-detail-mitarbeiterauswahl').disabled=true;
	document.getElementById('menulist-lehreinheiten-detail-funktion').disabled=true;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-kosten').disabled=true;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-faktor').disabled=true;
	document.getElementById('textbox-lehreinheiten-detail-lektoren-gesamtstunden').disabled=true;
	document.getElementById('button-lehreinheiten-detail-lektoren-save').disabled=true;
	document.getElementById('button-lehreinheiten-detail-lektoren-auswahladd').disabled=true;	
}

/**
 * Speichert die Zuteilung eines Lektors zu einer Lehreinheit
 */
function MitarbeiterLehreinheitenZuteilungSave()
{
		
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";
		
	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	//Daten holen
	mitarbeiter_id = document.getElementById('menulist-lehreinheiten-detail-mitarbeiterauswahl').value;
	lehrfunktion_id = document.getElementById('menulist-lehreinheiten-detail-funktion').value;
	kosten = document.getElementById('textbox-lehreinheiten-detail-lektoren-kosten').value;
	faktor = document.getElementById('textbox-lehreinheiten-detail-lektoren-faktor').value;
	gesamtstunden = document.getElementById('textbox-lehreinheiten-detail-lektoren-gesamtstunden').value;
	lehreinheit_id = document.getElementById('textbox-lehreinheiten-detail-lektoren-lehreinheit_id').value;
	mitarbeiter_lehreinheit_id = document.getElementById('textbox-lehreinheiten-detail-lektoren-mitarbeiter_lehreinheit_id').value;
		
	var param = 'type=lva_mitarbeiter_lehreinheit_zuteilung&mitarbeiter_id='+mitarbeiter_id+'&lehrfunktion_id='+lehrfunktion_id+'&kosten='+kosten+'&faktor='+faktor+'&gesamtstunden='+gesamtstunden+'&lehreinheit_id='+lehreinheit_id+'&mitarbeiter_lehreinheit_id='+mitarbeiter_lehreinheit_id;
	
	//Parameter schicken
	httpRequest.send(param);
	
	// Bei status 4 ist sendung Ok
	switch(httpRequest.readyState)
	{
		case 1,2,3: alert('Bad Ready State: '+httpRequest.status);
			        return false;
		            break;
	
		case 4:		if(httpRequest.status !=200)
			        {
				        alert('The server respond with a bad status code: '+httpRequest.status);
				        return false;
			        }
			        else
			        {
				        var response = httpRequest.responseText;
			        }
		            break;
	}
		
	// Returnwerte aus RDF abfragen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/dbdml');
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	               getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/dbdml/0");
	
	var predicateNS = "http://www.technikum-wien.at/dbdml/rdf";
		
	var dbdml_return = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
	var dbdml_errormsg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#errormsg" ));
		
	if(dbdml_return=='true')
	{
		//Tree Refreshen
		RefreshMitarbeiterLehreinheitenTree();
		//Statusbar Text setzen
		window.setTimeout("setStatusBarText('Datensatz wurde erfolgreich gespeichert')",sleep_time);
	}
	else
	{
		alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
	}
}

/**
 * Refresh den MitarbeiterLehreinheiten Tree
 */
function RefreshMitarbeiterLehreinheitenTree()
{
	document.getElementById('tree-liste-mitarbeiterlehreinheiten').builder.refresh();
	window.setTimeout("document.getElementById('tree-liste-mitarbeiterlehreinheiten').builder.rebuild()",sleep_time);
}

/**
 * Refresh den Lehreinheiten Tree
 */
function RefreshLehreinheitenTree()
{
	tree = document.getElementById('tree-liste-lehreinheiten');
	
	//Aktuell Markierten Eintrag holen
	var i;
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
		i = 0;		

	try
	{
		col = tree.columns ? tree.columns["tree-liste-lehreinheiten-col-lehreinheit_id"] : "tree-liste-lehreinheiten-col-lehreinheit_id";
		LehreinheitenSelectLehreinheit_id = tree.view.getCellText(i,col);
	}
	catch(e)
	{}
	
	tree.builder.refresh();
	window.setTimeout("document.getElementById('tree-liste-lehreinheiten').builder.rebuild()",sleep_time*2);
	
	//Eintrag wieder markieren
	window.setTimeout("LehreinheitenSelectLehreinheit()",sleep_time*2+10);
}

/**
 * Selektiert den Eintrag mit der Lehreinheit_id LehreinheitenSelectLehreinheit_id
 */
function LehreinheitenSelectLehreinheit()
{
	var tree=document.getElementById('tree-liste-lehreinheiten');
	var items = tree.view.rowCount; //Anzahl der Zeilen

   	for(var i=0;i<items;i++)
   	{
		col = tree.columns ? tree.columns["tree-liste-lehreinheiten-col-lehreinheit_id"] : "tree-liste-lehreinheiten-col-lehreinheit_id";
		lehreinheit_id=tree.view.getCellText(i,col);

		if(lehreinheit_id == LehreinheitenSelectLehreinheit_id)
		{
			tree.view.selection.select(i);
			tree.treeBoxObject.ensureRowIsVisible(i);
			return true;
		}
   	}
}

/**
 * Fuegt einen Lektor zu einer Lehreinheit hinzu
 */
function MitarbeiterLehreinheitenAdd()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";
		
	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	lehreinheit_id = document.getElementById('textbox-lehreinheiten-detail-lektoren-lehreinheit_id').value;
	var param = 'type=lva_mitarbeiter_lehreinheit_add&lehreinheit_id='+lehreinheit_id;
	
	//Parameter schicken
	httpRequest.send(param);
	
	// Bei status 4 ist sendung Ok
	switch(httpRequest.readyState)
	{
		case 1,2,3: alert('Bad Ready State: '+httpRequest.status);
			        return false;
		            break;
	
		case 4:		if(httpRequest.status !=200)
			        {
				        alert('The server respond with a bad status code: '+httpRequest.status);
				        return false;
			        }
			        else
			        {
				        var response = httpRequest.responseText;
			        }
		            break;
	}
		
	// Returnwerte aus RDF abfragen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/dbdml');
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	               getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/dbdml/0");
	
	var predicateNS = "http://www.technikum-wien.at/dbdml/rdf";
		
	var dbdml_return = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
	var dbdml_errormsg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#errormsg" ));
		
	if(dbdml_return=='true')
	{
		//Tree Refreshen
		RefreshMitarbeiterLehreinheitenTree();
		MitarbeiterLehreinheitenSelect_id = dbdml_errormsg;		
		window.setTimeout("MitarbeiterLehreinheitenSelect()",sleep_time+10);
		//Statusbar Text setzen
		//window.setTimeout("setStatusBarText('Datensatz wurde angelegt')",sleep_time);
	}
	else
	{
		alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
	}
}

/**
 * Selectiert einen Eintrag im tree MitarbeiterLehreinheit
 * Markiert wird der DS mit der ID in MitarbeiterLehreinheitenSelect_id
 */
function MitarbeiterLehreinheitenSelect()
{
	var tree=document.getElementById('tree-liste-mitarbeiterlehreinheiten');
	var items = tree.view.rowCount; //Anzahl der Zeilen
	
   	for(var i=0;i<items;i++)
   	{   		
		col = tree.columns ? tree.columns["tree-liste-mitarbeiterlehreinheiten-col-mitarbeiter_lehreinheit_id"] : "tree-liste-mitarbeiterlehreinheiten-col-mitarbeiter_lehreinheit_id";
		mitarbeiterlehreinheiten_id=tree.view.getCellText(i,col);
		if(mitarbeiterlehreinheiten_id == MitarbeiterLehreinheitenSelect_id)
		{
			tree.view.selection.select(i);
			tree.treeBoxObject.ensureRowIsVisible(i);
			return true;
		}
   	}
}

/**
 * Loescht die Zuteilung eines Mitarbeiters zu einer Lehreinheit
 */
function MitarbeiterLehreinheitenDel()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";
		
	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
	tree = document.getElementById('tree-liste-mitarbeiterlehreinheiten');
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
	{
		alert('Bitte zuerst einen Lektor auswaehlen');
		return false;
	}
		
	//MitarbeiterLehreinheit_id holen
	var col = tree.columns ? tree.columns["tree-liste-mitarbeiterlehreinheiten-col-mitarbeiter_lehreinheit_id"] : "tree-liste-mitarbeiterlehreinheiten-col-mitarbeiter_lehreinheit_id";
	var mitarbeiter_lehreinheit_id=tree.view.getCellText(idx,col);
	
	var param = 'type=lva_mitarbeiter_lehreinheit_del&mitarbeiter_lehreinheit_id='+mitarbeiter_lehreinheit_id;
	
	//Parameter schicken
	httpRequest.send(param);
	
	// Bei status 4 ist sendung Ok
	switch(httpRequest.readyState)
	{
		case 1,2,3: alert('Bad Ready State: '+httpRequest.status);
			        return false;
		            break;
	
		case 4:		if(httpRequest.status !=200)
			        {
				        alert('The server respond with a bad status code: '+httpRequest.status);
				        return false;
			        }
			        else
			        {
				        var response = httpRequest.responseText;
			        }
		            break;
	}
		
	// Returnwerte aus RDF abfragen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/dbdml');
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	               getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/dbdml/0");
	
	var predicateNS = "http://www.technikum-wien.at/dbdml/rdf";
		
	var dbdml_return = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
	var dbdml_errormsg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#errormsg" ));
		
	if(dbdml_return=='true')
	{
		//Tree Refreshen
		RefreshMitarbeiterLehreinheitenTree();
		//Statusbar Text setzen
		//window.setTimeout("setStatusBarText('Datensatz wurde angelegt')",sleep_time);
	}
	else
	{
		alert(dbdml_errormsg);
	}
}

/**
 * Oeffnet einen Dialog zum Auswaehlen eines Mitarbeiters/Lektors
 */
function OpenMitarbeiterAuswahlDialog()
{
	window.open("lehreinheitenmitarbeiterdialog.xul.php","","chrome, status=no, modal, width=500, height=350, centerscreen, resizable");
}

/**
 * Fuegt eine Funktion zu einem Mitarbeiter fuer einen Fachbereich hinzu
 * damit dieser in der Liste aufscheint.
 */
function MitarbeiterLehreinheitAuswahlAdd(mitarbeiter_id)
{	
	
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";
		
	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	//Lehreinheit ID holen	
	lehreinheit_id = document.getElementById('textbox-lehreinheiten-detail-lektoren-lehreinheit_id').value
	
	var param = 'type=lva_mitarbeiter_lehreinheit_auswahladd&lehreinheit_id='+lehreinheit_id+'&mitarbeiter_id='+mitarbeiter_id;

	//Parameter schicken
	httpRequest.send(param);
	
	// Bei status 4 ist sendung Ok
	switch(httpRequest.readyState)
	{
		case 1,2,3: alert('Bad Ready State: '+httpRequest.status);
			        return false;
		            break;
	
		case 4:		if(httpRequest.status !=200)
			        {
				        alert('The server respond with a bad status code: '+httpRequest.status);
				        return false;
			        }
			        else
			        {
				        var response = httpRequest.responseText;
			        }
		            break;
	}
		
	// Returnwerte aus RDF abfragen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/dbdml');
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	               getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/dbdml/0");
	
	var predicateNS = "http://www.technikum-wien.at/dbdml/rdf";
		
	var dbdml_return = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
	var dbdml_errormsg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#errormsg" ));
		
	if(dbdml_return=='true')
	{		
		//Menulist Refreshen
		document.getElementById('menulist-lehreinheiten-detail-mitarbeiterauswahl').setAttribute('datasources',currentMitarbeiterLehreinheitenAuswahlURL+'&'+gettimestamp());
		MitarbeiterLehreinheitenSelect_id = mitarbeiter_id;
		window.setTimeout("document.getElementById('menulist-lehreinheiten-detail-mitarbeiterauswahl').value=MitarbeiterLehreinheitenSelect_id",sleep_time);
		//Statusbar Text setzen
		//window.setTimeout("setStatusBarText('Datensatz wurde angelegt')",sleep_time);
		LehreinheitenDetailLektorValueChanged()
	}
	else
	{
		alert(dbdml_errormsg);
	}
}

/**
 * Liefert einen Timestamp in Sekunden
 * zum anhaengen an eine URL um Caching zu verhindern
 */
function gettimestamp()
{
	var now = new Date();
	var ret = now.getHours()*60*60*60;
	ret = ret + now.getMinutes()*60*60;
	ret = ret + now.getSeconds()*60;
	ret = ret + now.getMilliseconds();
	return ret;
}

/**
 * Setzt die Datasource für die Gruppen Menulist
 */
function LehreinheitenDetailsetGruppenMenulistDatasource()
{
	grpmenulist = document.getElementById('menulist-lehreinheiten-detail-gruppe');
	studiengang = document.getElementById('textbox-lehreinheiten-detail-studiengang').value;
	ausbildungssemester = document.getElementById('menulist-lehreinheiten-detail-ausbildungssemester').value;
	//Url zusammenbauen
	var url = '<?php echo APP_ROOT; ?>rdf/fas/gruppen.rdf.php?stg='+studiengang+'&ausbsem='+ausbildungssemester;
	grpmenulist.setAttribute('datasources',url);
	grpmenulist.value='';
	grpmenulist.label='';
}

/**
 * Legt eine Neue Lehreinheit and
 * Dazu wird ein Dialog zur Auswahl der Lehrveranstaltung geoeffnet
 */
function LehreinheitenNeu()
{
	studiengang_id = document.getElementById('textbox-lehreinheiten-detail-studiengang').value;
	ausbildungssemester_id = document.getElementById('textbox-lehreinheiten-ausbildungssemester_id').value;
	//Dialog oeffnen
	window.open("lehreinheitenneudialog.xul.php?stg_id="+studiengang_id+"&ausbildungssemester_id="+ausbildungssemester_id,"","chrome, status=no, modal, width=500, height=350, centerscreen, resizable");
}

/**
 * Legt eine neue Lehreinheit an
 */
function LehreinheitenNeu1(lehrveranstaltung_id)
{
	//Anlegen des neuen Datensatzes	
	tree = document.getElementById('tree-liste-lehrveranstaltung');
		
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";
		
	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		
	var param = 'type=lva_neu&lehrveranstaltung_id='+lehrveranstaltung_id;

	//Parameter schicken
	httpRequest.send(param);
	
	// Bei status 4 ist sendung Ok
	switch(httpRequest.readyState)
	{
		case 1,2,3: alert('Bad Ready State: '+httpRequest.status);
			        return false;
		            break;
	
		case 4:		if(httpRequest.status !=200)
			        {
				        alert('The server respond with a bad status code: '+httpRequest.status);
				        return false;
			        }
			        else
			        {
				        var response = httpRequest.responseText;
			        }
		            break;
		default:	alert('Unknown Error on httprequest');
					break;
	}

	// Returnwerte aus RDF abfragen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/dbdml');
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	               getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/dbdml/0");
	
	var predicateNS = "http://www.technikum-wien.at/dbdml/rdf";
		
	var dbdml_return = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
	var dbdml_errormsg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#errormsg" ));
		
	if(dbdml_return=='true')
	{
		//Refreshen des Trees
		RefreshLehreinheitenTree();
		
		//Markieren des neuen Datensatzes
		LehreinheitenSelectLehreinheit_id=dbdml_errormsg;
		window.setTimeout("LehreinheitenSelectLehreinheit()",sleep_time);
		return true;
	}
	else
	{
		alert(dbdml_errormsg);
		return false;
	}
}
/**
 * Loescht eine Lehreinheit
 */
function LehreinheitenDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	tree = document.getElementById('tree-liste-lehreinheiten');
	
	//Aktuell Markierten Eintrag holen
	var i;
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
	{
		alert('Bitte zuerst einen Datensatz markieren');
		return false;
	}
	
	col = tree.columns ? tree.columns["tree-liste-lehreinheiten-col-lehreinheit_id"] : "tree-liste-lehreinheiten-col-lehreinheit_id";
	lehreinheit_id = tree.view.getCellText(i,col);
	
	if(confirm('Wollen Sie diese Lehreinheit wirklich löschen?'))
	{		
		// Request absetzen
		var httpRequest = new XMLHttpRequest();
		var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";
			
		httpRequest.open("POST", url, false, '','');
		httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			
		var param = 'type=lva_delete&lehreinheit_id='+lehreinheit_id;
	
		//Parameter schicken
		httpRequest.send(param);
		
		// Bei status 4 ist sendung Ok
		switch(httpRequest.readyState)
		{
			case 1,2,3: alert('Bad Ready State: '+httpRequest.status);
				        return false;
			            break;
		
			case 4:		if(httpRequest.status !=200)
				        {
					        alert('The server respond with a bad status code: '+httpRequest.status);
					        return false;
				        }
				        else
				        {
					        var response = httpRequest.responseText;
				        }
			            break;
			default:	alert('Unknown Error on httprequest');
						break;
		}

		// Returnwerte aus RDF abfragen
		var dsource=parseRDFString(response, 'http://www.technikum-wien.at/dbdml');
		
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
		               getService(Components.interfaces.nsIRDFService);
		var subject = rdfService.GetResource("http://www.technikum-wien.at/dbdml/0");
		
		var predicateNS = "http://www.technikum-wien.at/dbdml/rdf";
			
		var dbdml_return = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
		var dbdml_errormsg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#errormsg" ));
			
		if(dbdml_return=='true')
		{		
			//Tree Refreshen
			RefreshLehreinheitenTree();
			//Statusbar Text setzen
			//window.setTimeout("setStatusBarText('Datensatz wurde gelöscht')",sleep_time);
			//Felder Leeren und Disablen
			LehreinheitenDetailDisable(true);
		}
		else
			alert(dbdml_errormsg);
	}
}

/**
 * Wenn in der Lektorenauswahl ein Wert geaendert wird, dann wird der Speichern Button aktiviert
 */
function LehreinheitenDetailLektorValueChanged()
{
	document.getElementById('button-lehreinheiten-detail-lektoren-save').disabled=false;
}