<?php
	include('../vilesci/config.inc.php');
	
	if(isset($_GET['bankverbindung_id']) && $_GET['bankverbindung_id']!='')
		echo "var MitarbeiterDetailBankverbindung_id=".$_GET['bankverbindung_id'];
	else
		echo "var MitarbeiterDetailBankverbindung_id=null";
		
?>

if(MitarbeiterDetailBankverbindung_id!=null)
{
	window.setTimeout("MitarbeiterBankverbindungBearbeiten()",window.opener.sleep_time/2);
}

/**
 * Laedt den zu bearbeitenden Datensatz
 */
function MitarbeiterBankverbindungBearbeiten()
{
		// RDF vom Server holen

		// Url zum RDF
		var url="<?php echo APP_ROOT; ?>rdf/fas/bankverbindungen.rdf.php?bankverbindung_id="+MitarbeiterDetailBankverbindung_id;

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
		var dsource=parseRDFString(response, 'http://www.technikum-wien.at/bankverbindungen/alle');
		
		// Trick 17	(sonst gibt's ein Permission denied)
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			
		// Daten aus RDF auslesen
		dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);
	
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);
		var subject = rdfService.GetResource("http://www.technikum-wien.at/bankverbindungen/" + MitarbeiterDetailBankverbindung_id);

	   	var predicateNS = "http://www.technikum-wien.at/bankverbindungen/rdf";
	   	
		//Felder befuellen
				
		person_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#person_id" ));
		name      = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#name" ));
		anschrift     = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#anschrift" ));
		blz = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#blz" ));
		bic = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#bic" ));
		kontonr = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#kontonummer" ));
		iban = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#iban" ));
		typ = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#typ" ));
		verrechnungskonto = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#verrechnungskonto" ));
								
		document.getElementById('textbox-mitarbeiter-bankverbindung-person_id').value = person_id;
		document.getElementById('textbox-mitarbeiter-bankverbindung-name').value = name;
		document.getElementById('textbox-mitarbeiter-bankverbindung-anschrift').value = anschrift;
		document.getElementById('textbox-mitarbeiter-bankverbindung-blz').value = blz;
		document.getElementById('textbox-mitarbeiter-bankverbindung-bic').value = bic;
		document.getElementById('textbox-mitarbeiter-bankverbindung-kontonr').value = kontonr;
		document.getElementById('textbox-mitarbeiter-bankverbindung-iban').value = iban;
		document.getElementById('menulist-mitarbeiter-bankverbindung-typ').value = typ;
		if(verrechnungskonto=='Ja')
			document.getElementById('checkbox-mitarbeiter-bankverbindung-verrechnungskonto').checked=true;
		else
			document.getElementById('checkbox-mitarbeiter-bankverbindung-verrechnungskonto').checked=false;
		
}

function MitarbeiterBankverbindungValueChange()
{

}

/**
 * Speichert die eingegebene Email
 */
function MitarbeiterBankverbindungSave()
{
	//Daten aus den Felder holen
	person_id = document.getElementById('textbox-mitarbeiter-bankverbindung-person_id').value;
	name = document.getElementById('textbox-mitarbeiter-bankverbindung-name').value;
	typ = document.getElementById('menulist-mitarbeiter-bankverbindung-typ').value;
	anschrift = document.getElementById('textbox-mitarbeiter-bankverbindung-anschrift').value;
	blz = document.getElementById('textbox-mitarbeiter-bankverbindung-blz').value;
	bic = document.getElementById('textbox-mitarbeiter-bankverbindung-bic').value;
	kontonr = document.getElementById('textbox-mitarbeiter-bankverbindung-kontonr').value;
	iban = document.getElementById('textbox-mitarbeiter-bankverbindung-iban').value;
	verrechnungskonto = document.getElementById('checkbox-mitarbeiter-bankverbindung-verrechnungskonto').checked;
	
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	var param = "type=bankverbindung";
	if(MitarbeiterDetailBankverbindung_id!=null)
		param = param + "&new=false";
	else
		param = param + "&new=true";
	
	param = param + "&bankverbindung_id="+encodeURIComponent(MitarbeiterDetailBankverbindung_id);
	param = param + "&person_id="+encodeURIComponent(person_id);
	param = param + "&name="+encodeURIComponent(name);
	param = param + "&typ="+encodeURIComponent(typ);
	param = param + "&anschrift="+encodeURIComponent(anschrift);
	param = param + "&blz="+encodeURIComponent(blz);
	param = param + "&bic="+encodeURIComponent(bic);
	param = param + "&kontonr="+encodeURIComponent(kontonr);
	param = param + "&iban="+encodeURIComponent(iban);
	if(verrechnungskonto)
		param = param + "&verrechnungskonto=true";
	else
		param = param + "&verrechnungskonto=false";
	
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
   		//BankverbindungenTree aktualisieren
   		
   		//Statusbar setzen
   		window.opener.setStatusBarText(dbdml_errormsg);
   		window.opener.document.getElementById("tree-liste-bankverbindungen").builder.refresh();
   		window.setTimeout('window.opener.document.getElementById("tree-liste-bankverbindungen").builder.rebuild()',window.opener.sleep_time*2);
   		
   		//Fenster schliessen
		window.close();
   	}
   	else
   	{
		alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
   	}

	return true;

}