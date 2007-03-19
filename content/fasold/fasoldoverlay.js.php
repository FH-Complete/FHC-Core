<?php
	include('../vilesci/config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/fas/functions.inc.php');
	require_once('../../include/fas/benutzer.class.php');

	// Datenbank Verbindung
	if (!$conn = @pg_pconnect(CONN_STRING_FAS))
	   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

	if (!$conn_vilesci = @pg_pconnect(CONN_STRING))
	   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

	$user = get_uid();

	//Studiensemester_id holen
	$benutzer = new benutzer($conn_vilesci);
	$benutzer->loadVariables($user);
	$stsem = $benutzer->variable->semester_aktuell;
	$stsem_id = getStudiensemesterIdFromName($conn, $stsem);

	echo "var studiensemester_id=".$stsem_id.";";
?>
var sink;
var MenuMitarbeiterAuswahl=0;
var treemenuobservedata='';
/**
 * Observer der aufgerufen wird wenn Refresh des Funktionen-Trees
 * fertig ausgefuehrt wurde.
 */
var treefunktionenobserve = {
	     onBeginLoad: function(aSink)
	     {
	     	/* Statusbar setzen */
	     	setStatusBarText('Loading ...');
	     	/* Progressmeter anzeigen */
	     	document.getElementById('statusbarpanel-progressmeter').hidden=false;
	     },
	     onInterrupt: function(aSink) { },
	     onResume:    function(aSink) { },
	     onEndLoad:   function(aSink)
	     {
	     	 //debug('onEndLoad Funktionen');
	     	 /* Statusbar leeren */
		     setStatusBarText('');

		     /* Progressmeter verstecken */
		     document.getElementById('statusbarpanel-progressmeter').hidden=true;

		     /* Mitarbeiter Value Changed zuruecksetzen und Speichern Button deaktivieren */
		    // MitarbeiterDetailRestore();
			//debug('Starting Rebuild');
			document.getElementById('tree-liste-funktionen').builder.rebuild();

	     },
	     onError: function(aSink, aStatus, aErrorMsg)
	     {
	     	alert('Bei der Datenuebertragung ist ein Fehler Aufgetreten. Bitte Versuchen Sie es erneut.');
	     }
	  };

/**
 * Observer der aufgerufen wird wenn Refresh des Mitarbeiter-Trees
 * fertig ausgefuehrt wurde.
 */
var treemenuobserve = {
	     onBeginLoad: function(aSink)
	     {
	     	/* Statusbar setzen */
	     	setStatusBarText('Loading ...');
	     	/* Progressmeter anzeigen */
	     	document.getElementById('statusbarpanel-progressmeter').hidden=false;
	     },
	     onInterrupt: function(aSink) { },
	     onResume:    function(aSink) { },
	     onEndLoad:   function(aSink)
	     {
	     	 //debug('onEndLoad');
	     	 /* Statusbar leeren */
		     setStatusBarText('');

		     /* Progressmeter verstecken */
		     document.getElementById('statusbarpanel-progressmeter').hidden=true;

		     /* Mitarbeiter Value Changed zuruecksetzen und Speichern Button deaktivieren */
		     MitarbeiterDetailRestore();

			document.getElementById('tree-liste-mitarbeiter').builder.rebuild();
	     },
	     onError: function(aSink, aStatus, aErrorMsg)
	     {
	     	alert('Bei der Datenuebertragung ist ein Fehler Aufgetreten. Bitte Versuchen Sie es erneut.');
	     }
	  };

/**
 * Observer der aufgerufen wird wenn Rebuild fertig abgearbeitet wurde
 * (Funktioniert mit Moz 1.7.13 nicht richtig -> Seamonkey 1.0.2 funktioniert)
 */
var treemenurebuildobserve = {
	willRebuild: function(aSink) { },
	didRebuild: function(aSink)
	{
		//debug('didRebuild:'+treemenuobservedata);
		//wenn treemenuobservedata = refresheintragmerken dann wird der Mitarbeiter wieder markiert
		if(treemenuobservedata=='refresheintragmerken')
			MitarbeiterSelectMitarbeiter();
		treemenuobservedata='';
   		treeMitarbeiterReload=true;
	}
}

/**
 * Gibt eine Message auf die Javascript Console aus
 */
function debug(msg)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	 var consoleService = Components.classes["@mozilla.org/consoleservice;1"]
                                 .getService(Components.interfaces.nsIConsoleService);
    consoleService.logStringMessage(msg);
}

/**
 * Beim Auswaehlen eines Mitarbeiter-Filters werden die Mitarbeiter
 * die diesem Filter entsprechen geladen
 */
function onMenuMitarbeiterSelect(id)
{
	//Falls Daten geaendert wurden aber noch nicht gespeichert
	if(treeMitarbeiterDetailChanged)
	{
		if(confirm("Wollen Sie die geänderten Daten speichern?"))
			saveMitarbeiter();
		else
			treeMitarbeiterDetailChanged=false;
	}

	var tree=document.getElementById(id);
	var content=document.getElementById('tree-liste-mitarbeiter');
	var col = tree.columns ? tree.columns["tree-menu-mitarbeiter-col-filter"] : "tree-menu-mitarbeiter-col-filter";
	var filter=tree.view.getCellText(tree.currentIndex,col);
	var url = "<?php echo APP_ROOT; ?>rdf/fas/mitarbeiter.rdf.php";
	var attributes="?type=unknown&leerzeichencodierung=true";

	if(filter=="")
		filter="Studiengangsleiter";

	if (filter=="Studiengangsleiter")
	{
		attributes+="&stgl=true";
		MenuMitarbeiterAuswahl=0;
		document.getElementById('toolbar-MitarbeiterEditor-neu').disabled=true;
	}
	if (filter=="Fachbereichsleiter")
	{
		attributes+="&fbl=true";
		MenuMitarbeiterAuswahl=1;
		document.getElementById('toolbar-MitarbeiterEditor-neu').disabled=true;
	}
	if (filter=="Alle")
	{
		attributes+="&alle=true";
		MenuMitarbeiterAuswahl=2;
		document.getElementById('toolbar-MitarbeiterEditor-neu').disabled=false;
	}
	if (filter=="Aktive")
	{
		attributes+="&aktiv=true";
		MenuMitarbeiterAuswahl=3;
		document.getElementById('toolbar-MitarbeiterEditor-neu').disabled=false;
	}
	if (filter=="FixAngestellte")
	{
		attributes+="&fix=true&aktiv=true";
		MenuMitarbeiterAuswahl=4;
		document.getElementById('toolbar-MitarbeiterEditor-neu').disabled=false;
	}
	if (filter=="FixAngestellteAlle")
	{
		attributes+="&fix=true";
		MenuMitarbeiterAuswahl=5;
		document.getElementById('toolbar-MitarbeiterEditor-neu').disabled=false;
	}
	if (filter=="Inaktive")
	{
		attributes+="&aktiv=false";
		MenuMitarbeiterAuswahl=6;
		document.getElementById('toolbar-MitarbeiterEditor-neu').disabled=true;
	}
	if (filter=="Karenziert")
	{
		attributes+="&karenziert=true";
		MenuMitarbeiterAuswahl=7;
		document.getElementById('toolbar-MitarbeiterEditor-neu').disabled=true;
	}
	if (filter=="Ausgeschieden")
	{
		attributes+="&ausgeschieden=true";
		MenuMitarbeiterAuswahl=8;
		document.getElementById('toolbar-MitarbeiterEditor-neu').disabled=true;
	}
	if (filter=="FreiAngestellte")
	{
		attributes+="&fix=false&aktiv=true";
		MenuMitarbeiterAuswahl=9;
		document.getElementById('toolbar-MitarbeiterEditor-neu').disabled=false;
	}
	if (filter=="FreiAngestellteAlle")
	{
		attributes+="&fix=false";
		MenuMitarbeiterAuswahl=10;
		document.getElementById('toolbar-MitarbeiterEditor-neu').disabled=false;
	}
	//Timestamp anhaengen da beim Laden von Zwischengespeicherten Dateien kein
	//Observer Event ausgeloest wird.
	url+=attributes+'&'+gettimestamp();

	treeMitarbeiterReload=false;
	//Mitarbeiter Detail Felder deaktivieren
	SetMitarbeiterDetailAktiv(false);

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//Alte Datasources loeschen
	var oldDatasources = content.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
		content.database.RemoveDataSource(oldDatasources.getNext());

	//Neue Datasource setzen
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var newDs  = rdfService.GetDataSource(url);
	content.database.AddDataSource(newDs);

	//Sink Observer anhaengen
	sink = newDs.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	sink.addXMLSinkObserver(treemenuobserve);
}

/**
 * Bei Auswahl des Verbandes wird lehreinheiten.rdf
 * fuer die zugehoerige Gruppe/Studiengang geladen
 */
function onMenuVerbandSelect()
{
	tree = document.getElementById('tree-verband');

	//Studiengang, Gruppe und Ausbildungssemester holen
	var col = tree.columns ? tree.columns["tree-verband-studiengang_id"] : "tree-verband-studiengang_id";
	var studiengang_id=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["tree-verband-gruppe_id"] : "tree-verband-gruppe_id";
	var gruppe_id=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["tree-verband-ausbildungssemester_id"] : "tree-verband-ausbildungssemester_id";
	var ausbildungssemester_id=tree.view.getCellText(tree.currentIndex,col);

	//Tree fuellen
	lehreinheitentree = document.getElementById('tree-liste-lehreinheiten');
	if(gruppe_id==0)
		url = '<?php echo APP_ROOT; ?>rdf/fas/lehreinheiten.rdf.php?studiengang_id='+studiengang_id;
	else
		url = '<?php echo APP_ROOT; ?>rdf/fas/lehreinheiten.rdf.php?gruppe_id='+gruppe_id;

	lehreinheitentree.setAttribute('datasources',url);
	document.getElementById('textbox-lehreinheiten-detail-studiengang').value=studiengang_id;
	document.getElementById('textbox-lehreinheiten-ausbildungssemester_id').value=ausbildungssemester_id;
}

/**
 * Parst einen RDF String
 */
function parseRDFString(str, url)
{
  netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

  var memoryDS = Components.classes["@mozilla.org/rdf/datasource;1?name=in-memory-datasource"].createInstance(Components.interfaces.nsIRDFDataSource);

  var ios=Components.classes["@mozilla.org/network/io-service;1"].getService(Components.interfaces.nsIIOService);
  baseUri=ios.newURI(url,null,null);

  var parser=Components.classes["@mozilla.org/rdf/xml-parser;1"].createInstance(Components.interfaces.nsIRDFXMLParser);
  parser.parseString(memoryDS,baseUri,str);

  return memoryDS;
}

/**
 * Liefert Wert aus Datasource
 */
function getTargetHelper(dsource,subj,predi)
{
	if (dsource.hasArcOut(subj, predi))
	{
		var target = dsource.GetTarget(subj, predi, true);
		if (target instanceof Components.interfaces.nsIRDFLiteral)
			return target.Value;
		if (target instanceof Components.interfaces.nsIRDFInt) //Fuer Integer Werte
			return target.Value;
	}
	return "";
}