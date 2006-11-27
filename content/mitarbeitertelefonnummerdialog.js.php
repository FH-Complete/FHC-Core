<?php
	include('../vilesci/config.inc.php');
	if(isset($_GET['telefonnummer_id']) && $_GET['telefonnummer_id']!='')
		echo "var MitarbeiterDetailTelefonnummer_id=".$_GET['telefonnummer_id'];
	else 
		echo "var MitarbeiterDetailTelefonnummer_id=null";
?>

function Check()
{
	if(MitarbeiterDetailTelefonnummer_id!=null)
		window.setTimeout("MitarbeiterTelefonnummerBearbeiten()",window.opener.sleep_time);
}

/**
 * Laedt den zu bearbeitenden Datensatz
 */
function MitarbeiterTelefonnummerBearbeiten()
{
		// RDF vom Server holen

		// Url zum RDF
		var url="<?php echo APP_ROOT; ?>rdf/fas/telefonnummern.rdf.php?telefonnummer_id="+MitarbeiterDetailTelefonnummer_id;

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
		var dsource=parseRDFString(response, 'http://www.technikum-wien.at/telefonnummern/alle');
		
		// Trick 17	(sonst gibt's ein Permission denied)
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			
		// Daten aus RDF auslesen
		dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);
	
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);
		var subject = rdfService.GetResource("http://www.technikum-wien.at/telefonnummern/" + MitarbeiterDetailTelefonnummer_id);

	   	var predicateNS = "http://www.technikum-wien.at/telefonnummern/rdf";
	   	
		//Felder befuellen
				
		person_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#person_id" ));
		typ      = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#typ" ));
		nummer     = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#nummer" ));
		
		document.getElementById('textbox-mitarbeiter-telefonnummer-person_id').value = person_id;
		document.getElementById('menulist-mitarbeiter-telefonnummer-typ').value = typ;
		document.getElementById('textbox-mitarbeiter-telefonnummer-nummer').value = nummer;
}

function MitarbeiterTelefonnummerValueChange()
{   
}

/**
 * Speichert die eingegebene Telefonnummer
 */
function MitarbeiterTelefonnummerSave()
{
	//Daten aus den Felder holen
	person_id = document.getElementById('textbox-mitarbeiter-telefonnummer-person_id').value;
	typ = document.getElementById('menulist-mitarbeiter-telefonnummer-typ').value;
	name = document.getElementById('menulist-mitarbeiter-telefonnummer-typ').label;
	nummer = document.getElementById('textbox-mitarbeiter-telefonnummer-nummer').value;
			
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	var param = "type=telefonnummer";
	if(MitarbeiterDetailTelefonnummer_id!=null)
		param = param + "&new=false";
	else
		param = param + "&new=true";
	
	param = param + "&telefonnummer_id="+encodeURIComponent(MitarbeiterDetailTelefonnummer_id);
	param = param + "&person_id="+encodeURIComponent(person_id);
	param = param + "&name="+encodeURIComponent(name);
	param = param + "&typ="+encodeURIComponent(typ);
	param = param + "&nummer="+encodeURIComponent(nummer);
	
	 //Parameter schicken
	httpRequest.send(param);
	debug('telefon save param:'+param);
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
   		window.opener.setStatusBarText("Telefonnummer erfolgreich gespeichert");
   		window.opener.document.getElementById("tree-liste-telefonnummern").builder.refresh();
   		//Fenster schliessen
		window.close();
   	}
   	else
   	{
		alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
   	}

	return true;

}