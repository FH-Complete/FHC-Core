<?php
	include('../vilesci/config.inc.php');
	if(isset($_GET['email_id']) && $_GET['email_id']!='')
		echo "var MitarbeiterDetailEmail_id=".$_GET['email_id'];
	else 
		echo "var MitarbeiterDetailEmail_id=null";
?>

if(MitarbeiterDetailEmail_id!=null)
{
	window.setTimeout("MitarbeiterEmailBearbeiten()",window.opener.sleep_time/2);
}

/**
 * Laedt den zu bearbeitenden Datensatz
 */
function MitarbeiterEmailBearbeiten()
{
		// RDF vom Server holen

		// Url zum RDF
		var url="<?php echo APP_ROOT; ?>rdf/fas/email.rdf.php?email_id="+MitarbeiterDetailEmail_id;

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
		var dsource=parseRDFString(response, 'http://www.technikum-wien.at/email/alle');
		
		// Trick 17	(sonst gibt's ein Permission denied)
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			
		// Daten aus RDF auslesen
		dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);
	
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);
		var subject = rdfService.GetResource("http://www.technikum-wien.at/email/" + MitarbeiterDetailEmail_id);

	   	var predicateNS = "http://www.technikum-wien.at/email/rdf";
	   	
		//Felder befuellen
				
		person_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#person_id" ));
		typ      = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#typ" ));
		email     = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#email" ));
		zustelladresse = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#zustelladresse" ));
						
		document.getElementById('textbox-mitarbeiter-email-person_id').value = person_id;
		document.getElementById('menulist-mitarbeiter-email-name').value = typ;
		document.getElementById('textbox-mitarbeiter-email-email').value = email;
		if(zustelladresse == 'Ja')
			document.getElementById('checkbox-mitarbeiter-email-zustelladresse').checked = true;
		else
			document.getElementById('checkbox-mitarbeiter-email-zustelladresse').checked = false;		
}

function MitarbeiterEmailValueChange()
{   
}

/**
 * Speichert die eingegebene Email
 */
function MitarbeiterEmailSave()
{
	//Daten aus den Felder holen
	person_id = document.getElementById('textbox-mitarbeiter-email-person_id').value;
	name = document.getElementById('menulist-mitarbeiter-email-name').label;
	typ = document.getElementById('menulist-mitarbeiter-email-name').value;
	email = document.getElementById('textbox-mitarbeiter-email-email').value;
	zustelladresse = document.getElementById('checkbox-mitarbeiter-email-zustelladresse').checked;
		
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	var param = "type=email";
	if(MitarbeiterDetailEmail_id!=null)
		param = param + "&new=false";
	else
		param = param + "&new=true";
	
	param = param + "&email_id="+encodeURIComponent(MitarbeiterDetailEmail_id);
	param = param + "&person_id="+encodeURIComponent(person_id);
	param = param + "&name="+encodeURIComponent(name);
	param = param + "&typ="+encodeURIComponent(typ);
	param = param + "&email="+encodeURIComponent(email);
	param = param + "&zustelladresse="+encodeURIComponent(zustelladresse);
		
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
   		//AdressenTree aktualisieren
   		
   		//Statusbar setzen
   		window.opener.setStatusBarText("Email erfolgreich gespeichert");
   		window.opener.document.getElementById("tree-liste-email").builder.refresh();
   		window.setTimeout('window.opener.document.getElementById("tree-liste-email").builder.rebuild()',window.opener.sleep_time);
   		
   		//Fenster schliessen
		window.setTimeout('window.close()',window.opener.sleep_time);
   	}
   	else
   	{
		alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
   	}

	return true;

}