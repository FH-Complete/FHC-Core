<?php
	include('../vilesci/config.inc.php');
	require_once('../include/functions.inc.php');
	require_once('../include/fas/functions.inc.php');
	require_once('../include/fas/benutzer.class.php');

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

	echo "var MitarbeiterDetailStudiensemester_id = $stsem_id;\n";
	echo "var sleep_time = ".$benutzer->variable->sleep_time.";\n"; // Zeit zwischen Refresh und Rebuild
?>
var treeMitarbeiterReload=true; // Wenn treeMitarbeiterReload=false dann werden beim Reload die Defaultwerte nicht gesetzt
var treeMitarbeiterDetailChanged=false; // Gibt an ob die Mitarbeiter Daten veraendert wurden
var MitarbeiterSelectMitarbeiter_id; // Id des Mitarbeiters der Selektiert werden soll


/**
 * Wird aufgerufen wenn ein Eintrag aus dem Tree ausgewaehlt wird und
 * fuellt die Textfelder mit den Werten
 * wenn treeMitarbeiterReload false ist wird nichts durchgefuehrt
 */
function TreeListeMitarbeiterAuswahl()
{
	if(treeMitarbeiterReload)
	{
		//Falls Daten veraendert wurden fragen ob gespeichert werden soll
		if(treeMitarbeiterDetailChanged)
		{
			if(confirm("Wollen Sie die geaenderten Daten speichern?"))
				if(!saveMitarbeiter())
					return false;
			else
				treeMitarbeiterDetailChanged=false;
		}
		//Eingabefelder aktivieren
		SetMitarbeiterDetailAktiv(true);

		var tree=document.getElementById('tree-liste-mitarbeiter');
		setStatusBarText("");

		//Falls kein Eintrag gewaehlt wurde, den ersten auswaehlen
		var idx;
		if(tree.currentIndex>=0)
			idx = tree.currentIndex;
		else
			idx = 0;

		try
		{
			//Mitarbeiter_id holen
			var col = tree.columns ? tree.columns["tree-liste-mitarbeiter-col-mitarbeiter_id"] : "tree-liste-mitarbeiter-col-mitarbeiter_id";
			var mitarbeiter_id=tree.view.getCellText(idx,col);
		}
		catch(e)
		{
			return false;
		}
		// RDF mit den Mitarbeiterdaten vom Server holen
		// Url zum RDF
		var url="<?php echo APP_ROOT; ?>rdf/fas/mitarbeiter.rdf.php?mitarbeiter_id="+mitarbeiter_id;

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

		// XML in Datasource parsen
		var dsource=parseRDFString(response, 'http://www.technikum-wien.at/mitarbeiter/alle');

		// Trick 17	(sonst gibt's ein Permission denied)
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

		// Daten aus RDF auslesen
		dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);
		var subject = rdfService.GetResource("http://www.technikum-wien.at/mitarbeiter/" + mitarbeiter_id);
	   	var predicateNS = "http://www.technikum-wien.at/mitarbeiter/rdf";

		//Felder befuellen

		//Personen Daten
		person_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#person_id" ));
		mitarbeiter_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#mitarbeiter_id" ));
	   	anrede    = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#anrede" ));
	   	aktstatus = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#aktstatus" ));
	   	titelpre  = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#titelpre" ));
	   	titelpost = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#titelpost" ));
		nachname  = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#nachname" ));
		vorname   = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#vorname" ));
		vornamen  = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#vornamen" ));
		uid       = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#uid" ));
		svnr      = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#svnr" ));
		ersatzkennzeichen = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#ersatzkennzeichen" ));
		geburtsort = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#geburtsort" ));
		geburtsdatum = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#geburtsdatum" ));
		bemerkung = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#bemerkung" ));
		anzahlderkinder = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#anzahlderkinder" ));
		geschlecht = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#geschlecht" ));
		bismelden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#bismelden" ));
		familienstand = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#familienstand" ));
		staatsbuergerschaft = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#staatsbuergerschaft" ));
		geburtsnation = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#geburtsnation" ));
		deluser = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#deluser" ));

		//Wenn deluser = aktueller User dann darf der Datensatz geloescht werden
		if(deluser==document.getElementById('statusbarpanel-user').label)
			document.getElementById('toolbar-MitarbeiterEditor-loeschen').disabled=false;
		else
			document.getElementById('toolbar-MitarbeiterEditor-loeschen').disabled=true;

		document.getElementById('textbox-mitarbeiter-detail-person_id').value=person_id;
		document.getElementById('textbox-mitarbeiter-detail-mitarbeiter_id').value=mitarbeiter_id;
		document.getElementById('textbox-mitarbeiter-detail-aktstatus').value=aktstatus;
		document.getElementById('textbox-mitarbeiter-detail-anrede').value=anrede;
		document.getElementById('textbox-mitarbeiter-detail-titelpre').value = titelpre;
		document.getElementById('textbox-mitarbeiter-detail-titelpost').value = titelpost;
		document.getElementById('textbox-mitarbeiter-detail-nachname').value = nachname;
		document.getElementById('textbox-mitarbeiter-detail-vorname').value = vorname;
		document.getElementById('textbox-mitarbeiter-detail-vornamen').value = vornamen;
		document.getElementById('textbox-mitarbeiter-detail-uid').value = uid;
		document.getElementById('textbox-mitarbeiter-detail-svnr').value = svnr;
		document.getElementById('textbox-mitarbeiter-detail-ersatzkennzeichen').value = ersatzkennzeichen;
		document.getElementById('textbox-mitarbeiter-detail-geburtsort').value = geburtsort;
		document.getElementById('textbox-mitarbeiter-detail-geburtsdatum').value = geburtsdatum;
		document.getElementById('textbox-mitarbeiter-detail-bemerkung').value = bemerkung;
		document.getElementById('textbox-mitarbeiter-detail-anzahlderkinder').value = anzahlderkinder;
		document.getElementById('button-mitarbeiter-detail-geschlecht').label = (geschlecht=='M'?'maennlich':'weiblich');
		document.getElementById('checkbox-mitarbeiter-detail-bismelden').checked = (bismelden=='Ja'?true:false);
		document.getElementById('menulist-mitarbeiter-detail-familienstand').value = familienstand;
		document.getElementById('menulist-mitarbeiter-detail-staatsbuergerschaft').value = staatsbuergerschaft;
		document.getElementById('menulist-mitarbeiter-detail-geburtsnation').value = geburtsnation;

		//Mitarbeiter Daten
		personal_nr = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#personal_nr" ));
		kurzbezeichnung = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#kurzbezeichnung" ));
		beginndatum = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#beginndatum" ));
		stundensatz = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#stundensatz" ));
		habilitation = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#habilitation" ));
		ausgeschieden = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#ausgeschieden" ));
		beendigungsdatum = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#beendigungsdatum" ));
		ausbildung = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#ausbildung" ));
		aktiv = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#aktiv" ));

		document.getElementById('textbox-mitarbeiter-detail-personal_nr').value=personal_nr;
		document.getElementById('textbox-mitarbeiter-detail-kurzbezeichnung').value=kurzbezeichnung;
		document.getElementById('textbox-mitarbeiter-detail-beginndatum').value=beginndatum;
		document.getElementById('textbox-mitarbeiter-detail-stundensatz').value=stundensatz;
		document.getElementById('checkbox-mitarbeiter-detail-habilitation').checked = (habilitation=='Ja'?true:false);
		document.getElementById('checkbox-mitarbeiter-detail-ausgeschieden').checked = (ausgeschieden=='Ja'?true:false);
		if(ausgeschieden=='Ja')
			document.getElementById('textbox-mitarbeiter-detail-beendigungsdatum').disabled=false;
		else
			document.getElementById('textbox-mitarbeiter-detail-beendigungsdatum').disabled=true;
		document.getElementById('textbox-mitarbeiter-detail-beendigungsdatum').value=beendigungsdatum;
		document.getElementById('menulist-mitarbeiter-detail-ausbildung').value=ausbildung;
		document.getElementById('checkbox-mitarbeiter-detail-aktiv').checked = (aktiv=='Ja'?true:false);

		//Funktionen Tree fuellen
		var treeFunktionen=document.getElementById('tree-liste-funktionen');
		var treeFunktionenURL = "<?php echo APP_ROOT; ?>rdf/fas/funktionen.rdf.php?mitarbeiter_id="+mitarbeiter_id+"&leerzeichencodierung=true&"+gettimestamp();

		//treeFunktionen.setAttribute('datasources',treeFunktionenURL);

		//Alte Datasources loeschen
		var oldDatasources = treeFunktionen.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
			treeFunktionen.database.RemoveDataSource(oldDatasources.getNext());

		//Neue Datasource setzen
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		var newDs  = rdfService.GetDataSource(treeFunktionenURL);
		treeFunktionen.database.AddDataSource(newDs);

		//Sink Observer anhaengen
		sinkfunktion = newDs.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		sinkfunktion.addXMLSinkObserver(treefunktionenobserve);

		document.getElementById('button-mitarbeiter-detail-funktionen-neu').disabled=false;
		document.getElementById('button-mitarbeiter-detail-funktionen-bearbeiten').disabled=false;
		document.getElementById('button-mitarbeiter-detail-funktionen-loeschen').disabled=false;
		document.getElementById('button-mitarbeiter-detail-funktionen-alleanzeigen').disabled=false;
		document.getElementById('button-mitarbeiter-detail-funktionen-alleanzeigen').label='Alle Anzeigen';

		//Adressen Tree fuellen
		var treeAdressen=document.getElementById('tree-liste-adressen');
		var treeAdressenURL = "<?php echo APP_ROOT; ?>rdf/fas/adressen.rdf.php?pers_id="+person_id;
		treeAdressen.setAttribute('datasources',treeAdressenURL);
		document.getElementById('button-mitarbeiter-detail-adressen-neu').disabled=false;
		document.getElementById('button-mitarbeiter-detail-adressen-bearbeiten').disabled=false;
		document.getElementById('button-mitarbeiter-detail-adressen-loeschen').disabled=false;

		//Email Tree fuellen
		var treeEmail=document.getElementById('tree-liste-email');
		var treeEmailURL = "<?php echo APP_ROOT; ?>rdf/fas/email.rdf.php?pers_id="+person_id;
		treeEmail.setAttribute('datasources',treeEmailURL);
		document.getElementById('button-mitarbeiter-detail-email-neu').disabled=false;
		document.getElementById('button-mitarbeiter-detail-email-bearbeiten').disabled=false;
		document.getElementById('button-mitarbeiter-detail-email-loeschen').disabled=false;

		//Telefonnummern Tree fuellen
		var treeTelefonnummern=document.getElementById('tree-liste-telefonnummern');
		var treeTelefonnummernURL = "<?php echo APP_ROOT; ?>rdf/fas/telefonnummern.rdf.php?pers_id="+person_id;
		treeTelefonnummern.setAttribute('datasources',treeTelefonnummernURL);
		document.getElementById('button-mitarbeiter-detail-telefonnummern-neu').disabled=false;
		document.getElementById('button-mitarbeiter-detail-telefonnummern-bearbeiten').disabled=false;
		document.getElementById('button-mitarbeiter-detail-telefonnummern-loeschen').disabled=false;

		//Bankverbindungen Tree fuellen
		var treeBankverbindungen=document.getElementById('tree-liste-bankverbindungen');
		var treeBankverbindungenURL = "<?php echo APP_ROOT; ?>rdf/fas/bankverbindungen.rdf.php?pers_id="+person_id;
		treeBankverbindungen.setAttribute('datasources',treeBankverbindungenURL);
		document.getElementById('button-mitarbeiter-detail-bankverbindungen-neu').disabled=false;
		document.getElementById('button-mitarbeiter-detail-bankverbindungen-bearbeiten').disabled=false;
		document.getElementById('button-mitarbeiter-detail-bankverbindungen-loeschen').disabled=false;

		treeMitarbeiterValueNoChange();
	}

	return true;
}
/**
 * Wird aufgerufen wenn der Tree sortiert wird.
 * Merkt sich den Markierten Datensatz und setzt ihn spaeter (10ms) wieder neu
 * damit nach dem sortieren der gleiche Mitarbeiter markiert ist wie vorher
 */
function TreeMitarbeiterSort()
{
	var i;
	var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');
	if(treeMitarbeiter.currentIndex>=0)
		i = treeMitarbeiter.currentIndex;
	else
		i = 0;
	col = treeMitarbeiter.columns ? treeMitarbeiter.columns["tree-liste-mitarbeiter-col-mitarbeiter_id"] : "tree-liste-mitarbeiter-col-mitarbeiter_id";
	MitarbeiterSelectMitarbeiter_id = treeMitarbeiter.view.getCellText(i,col);
	window.setTimeout("MitarbeiterSelectMitarbeiter()",10);
}

/**
 * Refresht den MitarbeiterTree
 * @param eintrag_merken Wenn true dann wird der aktuell markierte Eintrag nach dem
 * refresh wieder markiert
 */
function refreshtree(eintrag_merken)
{
	var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');

	if(eintrag_merken)
	{
		var i;
		if(treeMitarbeiter.currentIndex>=0)
			i = treeMitarbeiter.currentIndex;
		else
			i = 0;

		col = treeMitarbeiter.columns ? treeMitarbeiter.columns["tree-liste-mitarbeiter-col-mitarbeiter_id"] : "tree-liste-mitarbeiter-col-mitarbeiter_id";
		MitarbeiterSelectMitarbeiter_id = treeMitarbeiter.view.getCellText(i,col);
	}

	treeMitarbeiterReload=false;
   	treeMitarbeiter.builder.refresh();

    //for (var i=0;i<30;i++)
	//	treeMitarbeiter.builder.rebuild();

   	//window.setTimeout("document.getElementById('tree-liste-mitarbeiter').builder.rebuild()",sleep_time);
   	treemenuobservedata='refresh';
   	if(eintrag_merken)
   	{
   		treemenuobservedata='refresheintragmerken';
   	}
}

/**
 * Excel Export
 */
function MitarbeiterExcelExport()
{
	var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');
	var treeMitarbeiterMenu=document.getElementById('tree-menu-mitarbeiter1');
	var col = treeMitarbeiterMenu.columns ? treeMitarbeiterMenu.columns["tree-menu-mitarbeiter-col-filter"] : "tree-menu-mitarbeiter-col-filter";
	var filter=treeMitarbeiterMenu.view.getCellText(treeMitarbeiterMenu.currentIndex,col);
	cols = treeMitarbeiter.getElementsByTagName('treecol');

	var url = "excel.php";
	var attributes="?type=mitarbeiter";
	if (filter=="Studiengangsleiter")
			attributes+="&stgl=true";
		if (filter=="Fachbereichsleiter")
			attributes+="&fbl=true";
		if (filter=="Alle")
			attributes+="&alle=true";
		if (filter=="Aktive")
			attributes+="&aktiv=true";
		if (filter=="FixAngestellte")
			attributes+="&fix=true&aktiv=true";
		if (filter=="FixAngestellteAlle")
			attributes+="&fix=true";
		if (filter=="Inaktive")
			attributes+="&aktiv=false";
		if (filter=="Karenziert")
			attributes+="&karenziert=true";
		if (filter=="Ausgeschieden")
			attributes+="&ausgeschieden=true";
		if (filter=="FreiAngestellte")
			attributes+="&fix=false&aktiv=true";
		if (filter=="FreiAngestellteAlle")
			attributes+="&fix=false";

	url+=attributes;
	spalte=0;
	for(i in cols)
	{
		if(cols[i].hidden==false)
		{
			url += "&spalte"+spalte+"="+MitarbeiterDetailgetSpaltenname(cols[i].id);
			spalte=spalte+1;
		}
	}
	//url+='&spalte0=titelpre&spalte1=vorname&spalte2=vornamen&spalte3=familienname&spalte4=uid';

	//alert(url);
	//window.open(url,"","chrome,status=no, modal, width=400, height=250, centerscreen, resizable");
	window.location.href=url;
}

/**
 * Liefert anhand der ID den Namen der Klassenvariable
 */
function MitarbeiterDetailgetSpaltenname(id)
{
	if(id=='tree-liste-mitarbeiter-col-anrede') return 'anrede';
	if(id=='tree-liste-mitarbeiter-col-titelpre') return 'titelpre';
	if(id=='tree-liste-mitarbeiter-col-vorname') return 'vorname';
	if(id=='tree-liste-mitarbeiter-col-vornamen') return 'vornamen';
	if(id=='tree-liste-mitarbeiter-col-nachname') return 'familienname';
	if(id=='tree-liste-mitarbeiter-col-titelpost') return 'titelpost';
	if(id=='tree-liste-mitarbeiter-col-personal_nr') return 'persnr';
	if(id=='tree-liste-mitarbeiter-col-geburtsdatum') return 'gebdat';
	if(id=='tree-liste-mitarbeiter-col-svnr') return 'svnr';
	if(id=='tree-liste-mitarbeiter-col-ersatzkennzeichen') return 'ersatzkennzeichen';
	if(id=='tree-liste-mitarbeiter-col-uid') return 'uid';
	if(id=='tree-liste-mitarbeiter-col-kurzbezeichnung') return 'kurzbez';
	if(id=='tree-liste-mitarbeiter-col-geschlecht') return 'geschlecht';
	if(id=='tree-liste-mitarbeiter-col-staatsbuergerschaft') return 'staatsbuergerschaft';
	if(id=='tree-liste-mitarbeiter-col-aktstatus') return 'aktstatus_bezeichnung';
	if(id=='tree-liste-mitarbeiter-col-akademischergrad') return 'akadgrad_bezeichnung';
	if(id=='tree-liste-mitarbeiter-col-familienstand') return 'familienstand_bezeichnung';
	if(id=='tree-liste-mitarbeiter-col-anzahlderkinder') return 'anzahlderkinder';
	if(id=='tree-liste-mitarbeiter-col-geburtsnation') return 'gebnation';
	if(id=='tree-liste-mitarbeiter-col-habilitation') return 'habilitation_bezeichnung';
	if(id=='tree-liste-mitarbeiter-col-beginndatum') return 'beginndatum';
	if(id=='tree-liste-mitarbeiter-col-bemerkung') return 'bemerkung';
	if(id=='tree-liste-mitarbeiter-col-ausgeschieden') return 'ausgeschieden';
	if(id=='tree-liste-mitarbeiter-col-beendigungsdatum') return 'beendigungsdatum';
	if(id=='tree-liste-mitarbeiter-col-ausbildung') return 'ausbildung_bezeichnung';
	if(id=='tree-liste-mitarbeiter-col-stundensatz') return 'stundensatz';
	if(id=='tree-liste-mitarbeiter-col-bismelden') return 'bismelden_bezeichnung';
	if(id=='tree-liste-mitarbeiter-col-aktiv') return 'aktiv_bezeichnung';
	if(id=='tree-liste-mitarbeiter-col-mitarbeiter_id') return 'mitarbeiter_id';
	if(id=='tree-liste-mitarbeiter-col-person_id')	return 'person_id';
}
/**
 * Erstellt das Geburtsdatum aus der SVNR
 */
function MitarbeiterSVNRValueChange()
{
	svnr=document.getElementById('textbox-mitarbeiter-detail-svnr').value;

	if(svnr!='' && svnr.length==10)
		document.getElementById('textbox-mitarbeiter-detail-geburtsdatum').value = svnr.charAt(4) + svnr.charAt(5) + "." + svnr.charAt(6) + svnr.charAt(7) + ".19" + svnr.charAt(8) + svnr.charAt(9);

}

/**
 * Wenn bei der Anrede "Frau" eingegeben wird, soll das Geschlecht auf weiblich geaendert werden
 */
function MitarbeiterAnredeValueChange()
{
	var geschlecht=document.getElementById('textbox-mitarbeiter-detail-anrede').value;
	var button = document.getElementById('button-mitarbeiter-detail-geschlecht');

	if(geschlecht=='Frau')
    	button.label='weiblich';
    else
    	button.label='maennlich';
}

/**
 * Generiert die Kurzbezeichnung und die UID
 */
function MitarbeiterDetailKurzbzGenerate()
{
	vorname=document.getElementById('textbox-mitarbeiter-detail-vorname').value;
	nachname=document.getElementById('textbox-mitarbeiter-detail-nachname').value;
	//Wenn vorname und nachname eingegeben wurden
	if(nachname!='' && vorname!='')
	{
		if(document.getElementById('textbox-mitarbeiter-detail-kurzbezeichnung').value=='')
		{
			// Kuerzel generieren
			var url="<?php echo APP_ROOT; ?>rdf/fas/generate_kuerzel.rdf.php?type=kurzbz&nachname="+nachname+"&vorname="+vorname;

			// Request absetzen
			var httpRequest = new XMLHttpRequest();
			httpRequest.open("GET", url, false, '','');
			httpRequest.send('');
			// Bei status 4 ist sendung Ok
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
				default: alert('Timing error');
						 break;
			}

			// XML in Datasource parsen
			var dsource=parseRDFString(response, 'http://www.technikum-wien.at/generate_kurzbz/msg');

			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

			// Daten aus RDF auslesen
			dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);

			var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
		                   getService(Components.interfaces.nsIRDFService);
			var subject = rdfService.GetResource("http://www.technikum-wien.at/generate_kurzbz/0");

		   	var predicateNS = "http://www.technikum-wien.at/generate_kurzbz/rdf";

	        var gen_return = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
			var gen_msg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#msg" ));

			if(gen_return=='true')
			{
			   	document.getElementById('textbox-mitarbeiter-detail-kurzbezeichnung').value=gen_msg;
			}
			else
				alert("Fehler beim Generieren der Daten: "+gen_msg);
		}


		// UID generieren
		if(document.getElementById('textbox-mitarbeiter-detail-uid').value=='')
		{
			var url="<?php echo APP_ROOT; ?>rdf/fas/generate_kuerzel.rdf.php?type=uid&nachname="+nachname+"&vorname="+vorname;

			// Request absetzen
			var httpRequest = new XMLHttpRequest();
			httpRequest.open("GET", url, false, '','');
			httpRequest.send('');
			// Bei status 4 ist sendung Ok
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

			// XML in Datasource parsen
			var dsource=parseRDFString(response, 'http://www.technikum-wien.at/generate_kurzbz/msg');

			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

			// Daten aus RDF auslesen
			dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);

			var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
		                   getService(Components.interfaces.nsIRDFService);
			var subject = rdfService.GetResource("http://www.technikum-wien.at/generate_kurzbz/0");

		   	var predicateNS = "http://www.technikum-wien.at/generate_kurzbz/rdf";

	        var gen_return = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
			var gen_msg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#msg" ));

			if(gen_return=='true')
			{
			   	document.getElementById('textbox-mitarbeiter-detail-uid').value=gen_msg;
			}
			else
				alert("Fehler beim Generieren der Daten: "+gen_msg);
		}
	}
	else
		alert('Bitte zuerst Vorname und Nachname eingeben');
	treeMitarbeiterValueChange();
}

/**
 * Legt eine neue leere DummyZeile an
 */
function MitarbeiterNeu()
{
    var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";
	// Request absetzen
	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	param = "type=newmitarbeiter&studiensemester_id="+MitarbeiterDetailStudiensemester_id;

	switch(MenuMitarbeiterAuswahl)
   	{
   		case 2: //Alle
   				break;
   		case 3: //Aktive
   				break;
   		case 4: //Fixangestellte
   				param = param + "&art=fix";
   				break;
   		case 5: //FixangestellteAlle
   				param = param + "&art=fix";
   				break;
   		case 9: //FreiAngestellte
   		case 10: //FreiAngestellteAlle
   				param = param + "&art=frei";
   				break;
   		default: break;
   	}


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
   		//In dbdml_errormsg steht die person_id des neuen Datensatzes

   		//Statusbar sezten
   		setStatusBarText("Neuer Datensatz wurde angelegt");

   		//Tree Refreshen
   		refreshtree(false);

   		//Eintrag im Tree Selektieren
   		MitarbeiterSelectMitarbeiter_id = dbdml_errormsg;
		treemenuobservedata='refresheintragmerken';
		//Wird automatisch selektiert sobald das rebuild fertig ist
   	}
   	else
		alert("Fehler beim anlegen des Datensatzes: "+dbdml_errormsg);


}

/**
 * Selektiert den Eintrag mit der Mitarbeiter_id MitarbeiterSelectMitarbeiter_id
 */
function MitarbeiterSelectMitarbeiter()
{
	debug('mitarbeiterSelectMitarbeiter');
	var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');
	var items = treeMitarbeiter.view.rowCount; //Anzahl der Zeilen

   	for(var i=0;i<items;i++)
   	{
		col = treeMitarbeiter.columns ? treeMitarbeiter.columns["tree-liste-mitarbeiter-col-mitarbeiter_id"] : "tree-liste-mitarbeiter-col-mitarbeiter_id";
		mitarbeiter_id=treeMitarbeiter.view.getCellText(i,col);

		if(mitarbeiter_id == MitarbeiterSelectMitarbeiter_id)
		{
			treeMitarbeiter.view.selection.select(i);
			treeMitarbeiter.treeBoxObject.ensureRowIsVisible(i);
			return true;
		}
   	}
}
/**
 * Loescht die Markierten Mitarbeiter
 */
function MitarbeiterDelete()
{
	if(document.getElementById('toolbar-MitarbeiterEditor-loeschen').disabled)
	{
		alert('Das loeschen dieses Datensatzes ist nicht moeglich');
		return false;
	}
	var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');
	var start = new Object();
	var end = new Object();
	var numRanges = treeMitarbeiter.view.selection.getRangeCount();
	var msg="";
	var anz=0;
	var ids=new Array();

	//Markierte Datensaetze holen
	for (var t=0; t<numRanges; t++)
	{
  		treeMitarbeiter.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			var col = treeMitarbeiter.columns ? treeMitarbeiter.columns["tree-liste-mitarbeiter-col-person_id"] : "tree-liste-mitarbeiter-col-person_id";
			ids[anz]=treeMitarbeiter.view.getCellText(v,col);
  			anz++;
  		}
	}
	if(anz>0)
	{
		if(anz>1)
			msg="Wollen Sie wirklich alle "+anz+" Datensaetze löschen?";
		else
			msg="Wollen Sie wirklich diesen Datensatz löschen?";

		if(confirm(msg))
		{
		    //DS Loeschen
		    //Parameter zusammenbauen
		    var param="type=delmitarbeiter&anz="+anz;
			for(var i=0;i<anz;i++)
				param = param + "&x"+i+"="+ids[i];

			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			// Request absetzen
			var httpRequest = new XMLHttpRequest();
			var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

			httpRequest.open("POST", url, false, '','');
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
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
		   		//Statusbar sezten
		   		setStatusBarText("Datensatz wurde erfolgreich geloescht");
		   		treeMitarbeiterValueNoChange()
		   		refreshtree(false);
		   		window.setTimeout("SelectIndex(0)",sleep_time+10);
		   	}
		   	else
				alert("Fehler beim Löschen der Daten: "+dbdml_errormsg);
		}
  	}
  	else
  		alert("Bitte zuerst einen Datensatz markieren");
}

/**
 * Selektiert den index idx im MitarbeiterTree
 */
function SelectIndex(idx)
{
	var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');
	treeMitarbeiter.view.selection.select(idx);
	treeMitarbeiter.treeBoxObject.ensureRowIsVisible(idx);
}

/**
 * Speichert die Mitarbeiterdaten
 */
function saveMitarbeiter()
{
	var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');

	//Daten aus den Felder holen
	person_id = document.getElementById('textbox-mitarbeiter-detail-person_id').value;
	mitarbeiter_id = document.getElementById('textbox-mitarbeiter-detail-mitarbeiter_id').value;
	aktstatus = document.getElementById('textbox-mitarbeiter-detail-aktstatus').value;
	anrede = document.getElementById('textbox-mitarbeiter-detail-anrede').value;
	titelpre = document.getElementById('textbox-mitarbeiter-detail-titelpre').value;
	titelpost = document.getElementById('textbox-mitarbeiter-detail-titelpost').value;
	nachname = document.getElementById('textbox-mitarbeiter-detail-nachname').value;
	vorname = document.getElementById('textbox-mitarbeiter-detail-vorname').value;
	vornamen = document.getElementById('textbox-mitarbeiter-detail-vornamen').value;
	uid = document.getElementById('textbox-mitarbeiter-detail-uid').value;
	svnr = document.getElementById('textbox-mitarbeiter-detail-svnr').value;
	ersatzkennzeichen = document.getElementById('textbox-mitarbeiter-detail-ersatzkennzeichen').value;
	geburtsort = document.getElementById('textbox-mitarbeiter-detail-geburtsort').value;
	geburtsdatum = document.getElementById('textbox-mitarbeiter-detail-geburtsdatum').value;
	bemerkung = document.getElementById('textbox-mitarbeiter-detail-bemerkung').value;
	anzahlderkinder = document.getElementById('textbox-mitarbeiter-detail-anzahlderkinder').value;
	geschlecht = document.getElementById('button-mitarbeiter-detail-geschlecht').label;
	bismelden = document.getElementById('checkbox-mitarbeiter-detail-bismelden').checked;
	familienstand = document.getElementById('menulist-mitarbeiter-detail-familienstand').value;
	staatsbuergerschaft = document.getElementById('menulist-mitarbeiter-detail-staatsbuergerschaft').value;
	geburtsnation = document.getElementById('menulist-mitarbeiter-detail-geburtsnation').value;
	personal_nr = document.getElementById('textbox-mitarbeiter-detail-personal_nr').value;
	kurzbezeichnung = document.getElementById('textbox-mitarbeiter-detail-kurzbezeichnung').value;
	beginndatum = document.getElementById('textbox-mitarbeiter-detail-beginndatum').value;
	stundensatz = document.getElementById('textbox-mitarbeiter-detail-stundensatz').value;
	habilitation = document.getElementById('checkbox-mitarbeiter-detail-habilitation').checked;
	ausgeschieden = document.getElementById('checkbox-mitarbeiter-detail-ausgeschieden').checked;
	beendigungsdatum = document.getElementById('textbox-mitarbeiter-detail-beendigungsdatum').value;
	ausbildung = document.getElementById('menulist-mitarbeiter-detail-ausbildung').value;
	aktiv = document.getElementById('checkbox-mitarbeiter-detail-aktiv').checked;

	//Pflichtfelder pruefen
	if(vorname!='' && nachname!='' && kurzbezeichnung!='')
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

		// Request absetzen
		var httpRequest = new XMLHttpRequest();
		var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

		httpRequest.open("POST", url, false, '','');
		httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

		//fuer die Einzelnen Felder wird encodeURIComponent aufgerufen,
		//damit Sonderzeichen wie + oder & richtig codiert werden.
		//Parameterlist zusammenbauen
		var param = "type=mitarbeiter";
		param = param + "&new=false";
		param = param + "&person_id=" + encodeURIComponent(person_id);
		param = param + "&mitarbeiter_id=" + encodeURIComponent(mitarbeiter_id);
		param = param + "&aktstatus=" + encodeURIComponent(aktstatus);
		param = param + "&anrede=" + encodeURIComponent(anrede);
		param = param + "&titelpre=" + encodeURIComponent(titelpre);
		param = param + "&titelpost=" + encodeURIComponent(titelpost);
		param = param + "&nachname=" + encodeURIComponent(nachname);
		param = param + "&vorname=" + encodeURIComponent(vorname);
		param = param + "&vornamen=" + encodeURIComponent(vornamen);
		param = param + "&uid=" + encodeURIComponent(uid);
		param = param + "&svnr=" + encodeURIComponent(svnr);
		param = param + "&ersatzkennzeichen=" + encodeURIComponent(ersatzkennzeichen);
		param = param + "&geburtsort=" + encodeURIComponent(geburtsort);
		param = param + "&geburtsdatum=" + encodeURIComponent(geburtsdatum);
		param = param + "&bemerkung=" + encodeURIComponent(bemerkung);
		param = param + "&anzahlderkinder=" + encodeURIComponent(anzahlderkinder);
		if(geschlecht=='weiblich')
			param = param + "&geschlecht=W";
		else
			param = param + "&geschlecht=M";

		param = param + "&bismelden="+encodeURIComponent(bismelden);
		param = param + "&familienstand=" + encodeURIComponent(familienstand);
		param = param + "&staatsbuergerschaft=" + encodeURIComponent(staatsbuergerschaft);
		param = param + "&geburtsnation=" + encodeURIComponent(geburtsnation);
		param = param + "&personal_nr=" + encodeURIComponent(personal_nr);
		param = param + "&kurzbezeichnung=" + encodeURIComponent(kurzbezeichnung);
		param = param + "&beginndatum=" + encodeURIComponent(beginndatum);
		param = param + "&stundensatz=" + encodeURIComponent(stundensatz);
		param = param + "&habilitation="+encodeURIComponent(habilitation);
		param = param + "&ausgeschieden="+encodeURIComponent(ausgeschieden);
		param = param + "&beendigungsdatum=" + encodeURIComponent(beendigungsdatum);
		param = param + "&ausbildung=" + encodeURIComponent(ausbildung);
		param = param + "&aktiv=" + encodeURIComponent(aktiv);

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
				treeMitarbeiterValueNoChange();
				refreshtree(true);

				//Statusbar Text setzen
				window.setTimeout("setStatusBarText('Datensatz wurde erfolgreich gespeichert')",sleep_time);
			}
		   	else
		   	{
				alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
				return false;
		   	}
	}
	else
	{
		alert("Bitte zuerst die Pflichtfelder ausfuellen");
		return false;
	}
	return true;
}

/**
 * Setzt den Text in der Statusbar
 */
function setStatusBarText($text)
{
	document.getElementById('statusbarpanel-text').label= $text;
}

/**
 * Beim aendern eines Wertes in einer Textbox
 * - Changed Flag setzen
 * - Speichern Button aktivieren
 */
function treeMitarbeiterValueChange()
{
   treeMitarbeiterDetailChanged=true;
   document.getElementById('button-mitarbeiter-detail-speichern').disabled=false;
   document.getElementById('button-mitarbeiter-detail-zurueck').disabled=false;
}

/**
 * Setzt den Changed Status wieder zurueck
 * - Changed Flag setzen
 * - Speichern Button deaktivieren
 */
function treeMitarbeiterValueNoChange()
{
   treeMitarbeiterDetailChanged=false;
   document.getElementById('button-mitarbeiter-detail-speichern').disabled=true;
   document.getElementById('button-mitarbeiter-detail-zurueck').disabled=true;
}

/**
 * Beim druecken auf den zurueck Button
 * - Die Daten werden neu geladen
 * - Das Changed Flag zurueck gesetzt
 * - Der Speichern Button Deaktiviert
 */
function MitarbeiterDetailZurueck()
{
	treeMitarbeiterValueNoChange();
	TreeListeMitarbeiterAuswahl();
}

/**
 * Deaktiviert/Aktiviert das Detailfenster
 */
function SetMitarbeiterDetailAktiv(enabled)
{
	document.getElementById('textbox-mitarbeiter-detail-person_id').value='';
	document.getElementById('textbox-mitarbeiter-detail-person_id').disabled=!enabled;
	document.getElementById('textbox-mitarbeiter-detail-mitarbeiter_id').value='';
	document.getElementById('textbox-mitarbeiter-detail-mitarbeiter_id').disabled=!enabled;
	document.getElementById('textbox-mitarbeiter-detail-anrede').value='';
	document.getElementById('textbox-mitarbeiter-detail-anrede').disabled=!enabled;
	document.getElementById('textbox-mitarbeiter-detail-titelpre').value = '';
	document.getElementById('textbox-mitarbeiter-detail-titelpre').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-titelpost').value = '';
	document.getElementById('textbox-mitarbeiter-detail-titelpost').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-nachname').value = '';
	document.getElementById('textbox-mitarbeiter-detail-nachname').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-vorname').value = '';
	document.getElementById('textbox-mitarbeiter-detail-vorname').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-vornamen').value = '';
	document.getElementById('textbox-mitarbeiter-detail-vornamen').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-uid').value = '';
	document.getElementById('textbox-mitarbeiter-detail-uid').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-svnr').value = '';
	document.getElementById('textbox-mitarbeiter-detail-svnr').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-ersatzkennzeichen').value = '';
	document.getElementById('textbox-mitarbeiter-detail-ersatzkennzeichen').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-geburtsort').value = '';
	document.getElementById('textbox-mitarbeiter-detail-geburtsort').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-geburtsdatum').value = '';
	document.getElementById('textbox-mitarbeiter-detail-geburtsdatum').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-bemerkung').value = '';
	document.getElementById('textbox-mitarbeiter-detail-bemerkung').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-anzahlderkinder').value = '';
	document.getElementById('textbox-mitarbeiter-detail-anzahlderkinder').disabled = !enabled;
	document.getElementById('button-mitarbeiter-detail-geschlecht').disabled = !enabled;
	document.getElementById('checkbox-mitarbeiter-detail-bismelden').disabled = !enabled;
	document.getElementById('menulist-mitarbeiter-detail-familienstand').disabled = !enabled;
	document.getElementById('menulist-mitarbeiter-detail-staatsbuergerschaft').disabled = !enabled;
	document.getElementById('menulist-mitarbeiter-detail-geburtsnation').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-personal_nr').value='';
	//document.getElementById('textbox-mitarbeiter-detail-personal_nr').disabled=!enabled;
	document.getElementById('textbox-mitarbeiter-detail-kurzbezeichnung').value='';
	document.getElementById('textbox-mitarbeiter-detail-kurzbezeichnung').disabled=!enabled;
	document.getElementById('textbox-mitarbeiter-detail-beginndatum').value='';
	document.getElementById('textbox-mitarbeiter-detail-beginndatum').disabled=!enabled;
	document.getElementById('textbox-mitarbeiter-detail-stundensatz').value='';
	document.getElementById('textbox-mitarbeiter-detail-stundensatz').disabled=!enabled;
	document.getElementById('checkbox-mitarbeiter-detail-habilitation').disabled = !enabled;
	document.getElementById('checkbox-mitarbeiter-detail-ausgeschieden').disabled = !enabled;
	document.getElementById('textbox-mitarbeiter-detail-beendigungsdatum').value='';
	document.getElementById('textbox-mitarbeiter-detail-beendigungsdatum').disabled=!enabled;
	document.getElementById('textbox-mitarbeiter-detail-beendigungsdatum').value='';
	document.getElementById('textbox-mitarbeiter-detail-beendigungsdatum').disabled=!enabled;
	document.getElementById('menulist-mitarbeiter-detail-ausbildung').disabled=!enabled;
	document.getElementById('checkbox-mitarbeiter-detail-aktiv').disabled = !enabled;
	document.getElementById('button-mitarbeiter-detail-funktionen-neu').disabled= !enabled;
	document.getElementById('button-mitarbeiter-detail-funktionen-bearbeiten').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-funktionen-loeschen').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-funktionen-alleanzeigen').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-adressen-neu').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-adressen-bearbeiten').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-adressen-loeschen').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-email-neu').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-email-bearbeiten').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-email-loeschen').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-telefonnummern-neu').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-telefonnummern-bearbeiten').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-telefonnummern-loeschen').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-bankverbindungen-neu').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-bankverbindungen-bearbeiten').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-bankverbindungen-loeschen').disabled=!enabled;
	document.getElementById('button-mitarbeiter-detail-gen_kurzbez').disabled=!enabled;

	document.getElementById('tree-liste-funktionen').setAttribute('datasources',"rdf:null");
    document.getElementById('tree-liste-adressen').setAttribute('datasources',"rdf:null");
	document.getElementById('tree-liste-email').setAttribute('datasources',"rdf:null");
	document.getElementById('tree-liste-telefonnummern').setAttribute('datasources',"rdf:null");
	document.getElementById('tree-liste-bankverbindungen').setAttribute('datasources',"rdf:null");
}

/**
 * Detailfenster Defaults setzen
 * Beim auswaehlen eines Mitarbeiters
 */
function MitarbeiterDetailRestore()
{
	treeMitarbeiterDetailChanged=false;
    document.getElementById('button-mitarbeiter-detail-speichern').disabled=true;
    document.getElementById('button-mitarbeiter-detail-zurueck').disabled=true;
    document.getElementById('textbox-mitarbeiter-detail-personal_nr').disabled=true;
}

/**
 * wechselt die Beschriftung des Geschlecht Buttons
 */
function MitarbeiterDetailGeschlechtChange()
{
    var button = document.getElementById('button-mitarbeiter-detail-geschlecht');
    treeMitarbeiterValueChange();

    if(button.label=='maennlich')
    	button.label='weiblich';
    else
    	button.label='maennlich';
}

/**
 * Schaltet das Beendigungsdatumfeld ein/aus
 */
function MitarbeiterDetailAusgeschiedenChange()
{
	var textbox = document.getElementById('textbox-mitarbeiter-detail-beendigungsdatum');
	var checkbox = document.getElementById('checkbox-mitarbeiter-detail-ausgeschieden');

	if(checkbox.checked)
	{
		textbox.disabled=false;
		var d = new Date();
		textbox.value=d.getDate()+"."+(d.getMonth()+1)+"."+d.getFullYear();
		document.getElementById('checkbox-mitarbeiter-detail-aktiv').checked=false;
	}
	else
	{
		textbox.disabled=true;
		textbox.value='';
		document.getElementById('checkbox-mitarbeiter-detail-aktiv').checked=true;
	}
}
/*****************************FUNKTIONEN*******************************/
/**
 * Ruft einen Dialog zum Eingeben der Funktionen auf
 */
function MitarbeiterDetailFunktionenNeu()
{
    var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');
	mitarbeiter_id = document.getElementById('textbox-mitarbeiter-detail-mitarbeiter_id').value;
	window.open("mitarbeiterfunktiondialog.xul.php?mitarbeiter_id="+mitarbeiter_id+"&MitarbeiterDetailStudiensemester_id="+MitarbeiterDetailStudiensemester_id,"","chrome, status=no, modal, width=500, height=350, centerscreen, resizable");
}

/**
 * Ruft einen Dialog zum Bearbeiten der Funktionen auf
 */
function MitarbeiterDetailFunktionenBearbeiten()
{
	var treeFunktionen=document.getElementById('tree-liste-funktionen');

	if(!(treeFunktionen.currentIndex>=0))
	{
		alert("Bitte zuerst einen Datensatz markieren");
		return false;
	}
	var col = treeFunktionen.columns ? treeFunktionen.columns["tree-liste-funktionen-col-funktion_id"] : "tree-liste-funktionen-col-funktion_id";
	var funktion_id=treeFunktionen.view.getCellText(treeFunktionen.currentIndex,col);
	window.open("mitarbeiterfunktiondialog.xul.php?funktion_id="+funktion_id,"","chrome,status=no, modal, width=500, height=350, centerscreen, resizable");
}

/**
 * Loescht die Markierte(n) Funktion(en)
 */
function MitarbeiterDetailFunktionenLoeschen()
{

	var treeFunktionen=document.getElementById('tree-liste-funktionen');
	var start = new Object();
	var end = new Object();
	var numRanges = treeFunktionen.view.selection.getRangeCount();
	var msg="";
	var anz=0;
	var ids=new Array();

	//Markierte Datensaetze holen
	for (var t=0; t<numRanges; t++)
	{
  		treeFunktionen.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			var col = treeFunktionen.columns ? treeFunktionen.columns["tree-liste-funktionen-col-funktion_id"] : "tree-liste-funktionen-col-funktion_id";
			ids[anz]=treeFunktionen.view.getCellText(v,col);
  			anz++;
  		}
	}
	if(anz>0)
	{
		if(anz>1)
			msg="Wollen Sie wirklich alle "+anz+" Datensaetze löschen?";
		else
			msg="Wollen Sie wirklich diesen Datensatz löschen?";

		if(confirm(msg))
		{
		    //DS Loeschen

		    //Parameter zusammenbauen
		    var param="type=delfunktion&anz="+anz;
			for(var i=0;i<anz;i++)
				param = param + "&x"+i+"="+ids[i];

			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			// Request absetzen
			var httpRequest = new XMLHttpRequest();
			var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

			httpRequest.open("POST", url, false, '','');
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
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
		   		//Statusbar sezten
		   		setStatusBarText("Datensatz wurde erfolgreich geloescht");

		   		treeFunktionen.builder.refresh();
		  		window.setTimeout("document.getElementById('tree-liste-funktionen').builder.rebuild()",sleep_time);
		  		//refreshtree(true);
		  		//Aktstatus setzten damit beim speichern nicht wieder der alte status gespeichert wird
		  		document.getElementById('textbox-mitarbeiter-detail-aktstatus').value=dbdml_errormsg;
		   	}
		   	else
				alert("Fehler beim Löschen der Daten: "+dbdml_errormsg);
		}
  	}
  	else
  		alert("Bitte zuerst einen Datensatz markieren");
}

/**
 * Zeigt alle Funktionen dieses Mitarbeiters an die er jemals hatte
 */
function MitarbeiterDetailFunktionenAlleAnzeigen()
{

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	var tree=document.getElementById('tree-liste-mitarbeiter');
	setStatusBarText("");

	//Falls kein Eintrag gewaehlt wurde, den ersten auswaehlen
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
		idx = 0;

	try
	{
		//Mitarbeiter_id holen
		var col = tree.columns ? tree.columns["tree-liste-mitarbeiter-col-mitarbeiter_id"] : "tree-liste-mitarbeiter-col-mitarbeiter_id";
		var mitarbeiter_id=tree.view.getCellText(idx,col);
	}
	catch(e)
	{
		return false;
	}

	//Funktionen Tree fuellen
	var treeFunktionen=document.getElementById('tree-liste-funktionen');
	button = document.getElementById('button-mitarbeiter-detail-funktionen-alleanzeigen');
	if(button.label=='Alle Anzeigen')
	{
		var treeFunktionenURL = "<?php echo APP_ROOT; ?>rdf/fas/funktionen.rdf.php?mitarbeiter_id="+mitarbeiter_id+"&allesemester=true&leerzeichencodierung=true&"+gettimestamp();
		button.label='Aktuelle Anzeigen';
	}
	else
	{
		var treeFunktionenURL = "<?php echo APP_ROOT; ?>rdf/fas/funktionen.rdf.php?mitarbeiter_id="+mitarbeiter_id+"&leerzeichencodierung=true&"+gettimestamp();
		button.label='Alle Anzeigen';
	}

	//Alte Datasources loeschen
	var oldDatasources = treeFunktionen.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
		treeFunktionen.database.RemoveDataSource(oldDatasources.getNext());

	//Neue Datasource setzen
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var newDs  = rdfService.GetDataSource(treeFunktionenURL);
	treeFunktionen.database.AddDataSource(newDs);

	//Sink Observer anhaengen
	sinkfunktion = newDs.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	sinkfunktion.addXMLSinkObserver(treefunktionenobserve);

}

/*****************************ADRESSEN*******************************/
/**
 * Ruft einen Dialog zum Eingeben der Adresse auf
 */
function MitarbeiterDetailAdressenNeu()
{
    var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');
	person_id = document.getElementById('textbox-mitarbeiter-detail-person_id').value;
	window.open("mitarbeiteradressendialog.xul.php?person_id="+person_id,"","chrome,status=no, modal, width=400, height=250, centerscreen, resizable");
}

/**
 * Ruft einen Dialog zum Bearbeiten der Adresse auf
 */
function MitarbeiterDetailAdressenBearbeiten()
{
	var treeAdressen=document.getElementById('tree-liste-adressen');

	if(!(treeAdressen.currentIndex>=0))
	{
		alert("Bitte zuerst einen Datensatz markieren");
		return false;
	}
	var col = treeAdressen.columns ? treeAdressen.columns["tree-liste-adressen-col-adresse_id"] : "tree-liste-adressen-col-adresse_id";
	var adress_id=treeAdressen.view.getCellText(treeAdressen.currentIndex,col);
	window.open("mitarbeiteradressendialog.xul.php?adress_id="+adress_id,"","chrome,status=no, modal, width=400, height=250, centerscreen, resizable");
}

/**
 * Loescht die Markierte Adresse
 */
function MitarbeiterDetailAdressenLoeschen()
{
	var treeAdressen=document.getElementById('tree-liste-adressen');
	var start = new Object();
	var end = new Object();
	var numRanges = treeAdressen.view.selection.getRangeCount();
	var msg="";
	var anz=0;
	var ids=new Array();

	for (var t=0; t<numRanges; t++)
	{
  		treeAdressen.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			var col = treeAdressen.columns ? treeAdressen.columns["tree-liste-adressen-col-adresse_id"] : "tree-liste-adressen-col-adresse_id";
			ids[anz]=treeAdressen.view.getCellText(v,col);
  			anz++;
  		}
	}
	if(anz>0)
	{
		if(anz>1)
			msg="Wollen Sie wirklich alle "+anz+" Datensaetze löschen?";
		else
			msg="Wollen Sie wirklich diesen Datensatz loeschen?";

		if(confirm(msg))
		{
		    //DS Loeschen

		    //Parameter zusammenbauen
		    var param="type=deladresse&anz="+anz;
			for(var i=0;i<anz;i++)
				param = param + "&x"+i+"="+ids[i];

			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			// Request absetzen
			var httpRequest = new XMLHttpRequest();
			var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

			httpRequest.open("POST", url, false, '','');
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
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
		   		//Statusbar sezten
		   		setStatusBarText("Datensatz wurde erfolgreich geloescht");

		   		treeAdressen.builder.refresh();
		  		window.setTimeout("document.getElementById('tree-liste-adressen').builder.rebuild()",sleep_time);
		   	}
		   	else
				alert("Fehler beim Löschen der Daten: "+dbdml_errormsg);
		}
  	}
  	else
  		alert("Bitte zuerst einen Datensatz markieren");
}

/*****************************EMAIL*******************************/
/**
 * Ruft einen Dialog zum Eingeben der Email auf
 */
function MitarbeiterDetailEmailNeu()
{
    var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');
	person_id = document.getElementById('textbox-mitarbeiter-detail-person_id').value;
	window.open("mitarbeiteremaildialog.xul.php?person_id="+person_id,"","chrome,status=no, modal, width=400, height=150, centerscreen, resizable");
}

/**
 * Ruft einen Dialog zum Bearbeiten der Email auf
 */
function MitarbeiterDetailEmailBearbeiten()
{
	var treeEmail=document.getElementById('tree-liste-email');

	if(!(treeEmail.currentIndex>=0))
	{
		alert("Bitte zuerst einen Datensatz markieren");
		return false;
	}
	var col = treeEmail.columns ? treeEmail.columns["tree-liste-email-col-email_id"] : "tree-liste-email-col-email_id";
	var email_id=treeEmail.view.getCellText(treeEmail.currentIndex,col);
	window.open("mitarbeiteremaildialog.xul.php?email_id="+email_id,"","chrome,status=no, modal, width=400, height=150, centerscreen, resizable");
}

/**
 * Loescht die Markierte(n) Email(s)
 */
function MitarbeiterDetailEmailLoeschen()
{
	var treeEmail=document.getElementById('tree-liste-email');
	var start = new Object();
	var end = new Object();
	var numRanges = treeEmail.view.selection.getRangeCount();
	var msg="";
	var anz=0;
	var ids=new Array();

	//Ermitteln der markierten Zeilen
	for (var t=0; t<numRanges; t++)
	{
  		treeEmail.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			var col = treeEmail.columns ? treeEmail.columns["tree-liste-email-col-email_id"] : "tree-liste-email-col-email_id";
			ids[anz]=treeEmail.view.getCellText(v,col);
  			anz++;
  		}
	}
	if(anz>0)
	{
		if(anz>1)
			msg="Wollen Sie wirklich alle "+anz+" Datensaetze loeschen?";
		else
			msg="Wollen Sie wirklich diesen Datensatz loeschen?";

		if(confirm(msg))
		{
		    //DS Loeschen

		    //Parameter zusammenbauen
		    var param="type=delemail&anz="+anz;
			for(var i=0;i<anz;i++)
				param = param + "&x"+i+"="+ids[i];

			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			// Request absetzen
			var httpRequest = new XMLHttpRequest();
			var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

			httpRequest.open("POST", url, false, '','');
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
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
		   		//Statusbar sezten
		   		setStatusBarText("Datensatz wurde erfolgreich geloescht");

		   		treeEmail.builder.refresh();
		  		window.setTimeout("document.getElementById('tree-liste-email').builder.rebuild()",sleep_time);
		   	}
		   	else
				alert("Fehler beim Löschen der Daten: "+dbdml_errormsg);
		}
  	}
  	else
  		alert("Bitte zuerst einen Datensatz markieren");
}

/*****************************TELEFONNUMMERN*******************************/
/**
 * Ruft einen Dialog zum Eingeben der Telefonnummer auf
 */
function MitarbeiterDetailTelefonnummernNeu()
{
    var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');
	person_id = document.getElementById('textbox-mitarbeiter-detail-person_id').value;
	window.open("mitarbeitertelefonnummerdialog.xul.php?person_id="+person_id,"","chrome,status=no, modal, width=400, height=150, centerscreen, resizable");
}

/**
 * Ruft einen Dialog zum Bearbeiten der Telefonnummern auf
 */
function MitarbeiterDetailTelefonnummernBearbeiten()
{
	var treeTelefonnummer=document.getElementById('tree-liste-telefonnummern');

	if(!(treeTelefonnummer.currentIndex>=0))
	{
		alert("Bitte zuerst einen Datensatz markieren");
		return false;
	}
	var col = treeTelefonnummer.columns ? treeTelefonnummer.columns["tree-liste-telefonnummern-col-telefonnummer_id"] : "tree-liste-telefonnummern-col-telefonnummer_id";
	var telefonnummer_id=treeTelefonnummer.view.getCellText(treeTelefonnummer.currentIndex,col);
	window.open("mitarbeitertelefonnummerdialog.xul.php?telefonnummer_id="+telefonnummer_id,"","chrome,status=no, modal, width=400, height=150, centerscreen, resizable");
}

/**
 * Loescht die Markierte(n) Telefonnummern(s)
 */
function MitarbeiterDetailTelefonnummernLoeschen()
{
	var treeTelefonnummer=document.getElementById('tree-liste-telefonnummern');
	var start = new Object();
	var end = new Object();
	var numRanges = treeTelefonnummer.view.selection.getRangeCount();
	var msg="";
	var anz=0;
	var ids=new Array();

	//Ermitteln der markierten Zeilen
	for (var t=0; t<numRanges; t++)
	{
  		treeTelefonnummer.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			var col = treeTelefonnummer.columns ? treeTelefonnummer.columns["tree-liste-telefonnummern-col-telefonnummer_id"] : "tree-liste-telefonnummern-col-telefonnummer_id";
			ids[anz]=treeTelefonnummer.view.getCellText(v,col);
  			anz++;
  		}
	}
	if(anz>0)
	{
		if(anz>1)
			msg="Wollen Sie wirklich alle "+anz+" Datensaetze loeschen?";
		else
			msg="Wollen Sie wirklich diesen Datensatz loeschen?";

		if(confirm(msg))
		{
		    //DS Loeschen

		    //Parameter zusammenbauen
		    var param="type=deltelefonnummer&anz="+anz;
			for(var i=0;i<anz;i++)
				param = param + "&x"+i+"="+ids[i];

			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			// Request absetzen
			var httpRequest = new XMLHttpRequest();
			var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

			httpRequest.open("POST", url, false, '','');
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
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
		   		//Statusbar sezten
		   		setStatusBarText("Datensatz wurde erfolgreich geloescht");

		   		treeTelefonnummer.builder.refresh();
		  		window.setTimeout("document.getElementById('tree-liste-telefonnummern').builder.rebuild()",sleep_time);
		   	}
		   	else
				alert("Fehler beim Löschen der Daten: "+dbdml_errormsg);
		}
  	}
  	else
  		alert("Bitte zuerst einen Datensatz markieren");
}

/*****************************Bankverbindung*******************************/
/**
 * Ruft einen Dialog zum Eingeben der Bankverbindung auf
 */
function MitarbeiterDetailBankverbindungenNeu()
{
    var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');
	person_id = document.getElementById('textbox-mitarbeiter-detail-person_id').value;
	window.open("mitarbeiterbankverbindungdialog.xul.php?person_id="+person_id,"","chrome,status=no, modal, width=400, height=250, centerscreen, resizable");
}

/**
 * Ruft einen Dialog zum Bearbeiten der Bankverbindungen auf
 */
function MitarbeiterDetailBankverbindungenBearbeiten()
{
	var treeBankverbindung=document.getElementById('tree-liste-bankverbindungen');

	if(!(treeBankverbindung.currentIndex>=0))
	{
		alert("Bitte zuerst einen Datensatz markieren");
		return false;
	}
	var col = treeBankverbindung.columns ? treeBankverbindung.columns["tree-liste-bankverbindungen-col-bankverbindung_id"] : "tree-liste-bankverbindungen-col-bankverbindung_id";
	var bankverbindung_id=treeBankverbindung.view.getCellText(treeBankverbindung.currentIndex,col);
	window.open("mitarbeiterbankverbindungdialog.xul.php?bankverbindung_id="+bankverbindung_id,"","chrome,status=no, modal, width=400, height=250, centerscreen, resizable");
}

/**
 * Loescht die Markierte(n) Bankverbindungen(s)
 */
function MitarbeiterDetailBankverbindungenLoeschen()
{

	var treeBankverbindung=document.getElementById('tree-liste-bankverbindungen');
	var start = new Object();
	var end = new Object();
	var numRanges = treeBankverbindung.view.selection.getRangeCount();
	var msg="";
	var anz=0;
	var ids=new Array();

	//Ermitteln der markierten Zeilen
	for (var t=0; t<numRanges; t++)
	{
  		treeBankverbindung.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			var col = treeBankverbindung.columns ? treeBankverbindung.columns["tree-liste-bankverbindungen-col-bankverbindung_id"] : "tree-liste-bankverbindungen-col-bankverbindung_id";
			ids[anz]=treeBankverbindung.view.getCellText(v,col);
  			anz++;
  		}
	}
	if(anz>0)
	{
		if(anz>1)
			msg="Wollen Sie wirklich alle "+anz+" Datensaetze loeschen?";
		else
			msg="Wollen Sie wirklich diesen Datensatz loeschen?";

		if(confirm(msg))
		{
		    //DS Loeschen

		    //Parameter zusammenbauen
		    var param="type=delbankverbindung&anz="+anz;
			for(var i=0;i<anz;i++)
				param = param + "&x"+i+"="+ids[i];

			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			// Request absetzen
			var httpRequest = new XMLHttpRequest();
			var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

			httpRequest.open("POST", url, false, '','');
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
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
		   		//Statusbar sezten
		   		setStatusBarText("Datensatz wurde erfolgreich geloescht");

		   		treeBankverbindung.builder.refresh();
		  		window.setTimeout("document.getElementById('tree-liste-bankverbindungen').builder.rebuild()",sleep_time);
		   	}
		   	else
				alert("Fehler beim Löschen der Daten: "+dbdml_errormsg);
		}
  	}
  	else
  		alert("Bitte zuerst einen Datensatz markieren");
}

/**
 * Es wird ein Mailfenster geoeffnet und alle markierten
 * Mitarbeiter werden als Empfaenger eingetragen.
 */
function TreeListeMitarbeiter_sendmail()
{
	mailempfaenger='';
	var treeMitarbeiter=document.getElementById('tree-liste-mitarbeiter');
	var numRanges = treeMitarbeiter.view.selection.getRangeCount();
	var start = new Object();
	var end = new Object();
	var anzfault=0;
	//Markierte Datensaetze holen
	for (var t=0; t<numRanges; t++)
	{
  		treeMitarbeiter.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			var col = treeMitarbeiter.columns ? treeMitarbeiter.columns["tree-liste-mitarbeiter-col-uid"] : "tree-liste-mitarbeiter-col-uid";
  			if(treeMitarbeiter.view.getCellText(v,col).length>1)
  			{
  				if(mailempfaenger!='')
					mailempfaenger=mailempfaenger+','+treeMitarbeiter.view.getCellText(v,col)+'@technikum-wien.at';
				else
					mailempfaenger='mailto:'+treeMitarbeiter.view.getCellText(v,col)+'@technikum-wien.at';
  			}
  			else
  			{
  				anzfault=anzfault+1;
  			}
  		}
	}
	if(anzfault!=0)
		alert(anzfault+' Mitarbeiter konnten nicht hinzugefuegt werden weil keine UID eingetragen ist!');
	window.location.href=mailempfaenger;
}