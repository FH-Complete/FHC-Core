<?php
include("../vilesci/config.inc.php");
?>

/**
 * Wenn einer der Tabs angeklickt wird wird der zugehoerige Tab
 * im anderen Overlay auch geaendert
 */
function tabchange(select)
{
	if(select=='lehreinheiten') //Auswahl des Lehreinheiten Tabs
	{
		document.getElementById('tab-mitarbeiter').selected=false;
		document.getElementById('tab-mitarbeiter1').selected=false;
		document.getElementById('tab-lehreinheiten').selected=true;
		document.getElementById('tab-verband').selected=true;
		document.getElementById('tabpanels-main').selectedIndex=1;
		document.getElementById('tabpanels-left').selectedIndex=1;
		
	}
	else if(select=='mitarbeiter') //Auswahl des Mitarbeiter Tabs
	{
		document.getElementById('tab-lehreinheiten').selected=false;
		document.getElementById('tab-verband').selected=false;
		document.getElementById('tab-mitarbeiter').selected=true;
		document.getElementById('tab-mitarbeiter1').selected=true;
		document.getElementById('tabpanels-main').selectedIndex=0;
		document.getElementById('tabpanels-left').selectedIndex=0;
	}
}

/**
 * Beim schliessen des Fensters wird ueberprueft ob Daten geaendert und noch
 * nicht gespeichert wurden.
 */
function closeWindow()
{
  //Wenn Daten geaendert wurden  
  if(treeMitarbeiterDetailChanged)
  {
     if(confirm("Wollen Sie die geänderten Daten speichern?"))
		if(!saveMitarbeiter())
		{
			//Falls beim Speichern ein Fehler auftritt bleibt das Fenster offen!
			return false;
		}
		else
			window.close();
	 else
	 	window.close();
  }
  else
     window.close();
}

/**
 * Wenn das Fenster Fertig geladen ist
 */
function onLoad()
{
	//rebuild Listener setzen
	document.getElementById('tree-liste-mitarbeiter').builder.addListener(treemenurebuildobserve);
	
	//Eingabefelder deaktivieren
	SetMitarbeiterDetailAktiv(false);

	//studiengangsleiter als Default setzen
	document.getElementById('tree-menu-mitarbeiter1').view.selection.select(6);
}

/**
 * Aenderung des Studiensemesters
 */
function studiensemesterChange()
{
	var items = document.getElementsByTagName('menuitem');
	var stsem='';
	//Markiertes Studiensemester holen
	for(i in items)
	{
		if(items[i].id=='menu-properies-studiensemester-name' && items[i].getAttribute("checked")=='true')
			stsem = items[i].label;
	}
	
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	var param = "type=variablechange";
	param = param + "&stsem="+stsem;
	
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
   		//Statusbar setzen
   		setStatusBarText("Studiensemester erfolgreich geändert");
   		document.getElementById("tree-liste-funktionen").builder.refresh();
   		document.getElementById("statusbarpanel-semester").label = stsem;   		
   		MitarbeiterDetailStudiensemester_id = dbdml_errormsg;
   	}
   	else
   	{
		alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
   	}
	return true;
}
