<?php
/**
 * FAS-Online
 * Dialog zum eingeben und bearbeiten der Adressen
 */
	include('../vilesci/config.inc.php');
	if(isset($_GET['adress_id']) && $_GET['adress_id']!='')
		echo "var MitarbeiterDetailAdress_id=".$_GET['adress_id'];
	else 
		echo "var MitarbeiterDetailAdress_id=null";
?>

if(MitarbeiterDetailAdress_id!=null)
{
	window.setTimeout("MitarbeiterAdressenBearbeiten()",window.opener.sleep_time/2);
}

/**
 * Laedt den zu bearbeitenden Datensatz
 */
function MitarbeiterAdressenBearbeiten()
{
		// RDF vom Server holen

		// Url zum RDF
		var url="<?php echo APP_ROOT; ?>rdf/fas/adressen.rdf.php?adress_id="+MitarbeiterDetailAdress_id;

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
		var dsource=parseRDFString(response, 'http://www.technikum-wien.at/adressen/alle');
		
		// Trick 17	(sonst gibt's ein Permission denied)
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			
		// Daten aus RDF auslesen
		dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);
	
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);
		var subject = rdfService.GetResource("http://www.technikum-wien.at/adressen/" + MitarbeiterDetailAdress_id);

	   	var predicateNS = "http://www.technikum-wien.at/adressen/rdf";
	   	
		//Felder befuellen
				
		person_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#person_id" ));
		adresstyp = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#typ" ));
		nation    = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#nation" ));
		strasse   = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#strasse" ));
		plz       = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#plz" ));
		gemeinde  = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#gemeinde" ));
		ort       = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#ort" ));
		zustelladresse = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#zustelladresse" ));
		bismeldeadresse = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#bismeldeadresse" ));
				
		document.getElementById('textbox-mitarbeiter-adressen-person_id').value = person_id;
		document.getElementById('menulist-mitarbeiter-adressen-adresstyp').value = adresstyp;
		document.getElementById('menulist-mitarbeiter-adressen-nation').value = nation;
		document.getElementById('textbox-mitarbeiter-adressen-strasse').value = strasse;
		document.getElementById('textbox-mitarbeiter-adressen-plz').value = plz;
		document.getElementById('textbox-mitarbeiter-adressen-gemeinde').value = gemeinde;
		document.getElementById('textbox-mitarbeiter-adressen-ort').value = ort;
		if(zustelladresse == 'Ja')
			document.getElementById('checkbox-mitarbeiter-adressen-zustelladresse').checked = true;
		else
			document.getElementById('checkbox-mitarbeiter-adressen-zustelladresse').checked = false;
		if(bismeldeadresse == 'Ja')
			document.getElementById('checkbox-mitarbeiter-adressen-bismeldeadresse').checked = true;
		else
			document.getElementById('checkbox-mitarbeiter-adressen-bismeldeadresse').checked = false;
}

function MitarbeiterAdressenValueChange()
{   
}

/**
 * Speichert die eingegebene Adresse
 */
function MitarbeiterAdressenSave()
{
	//Daten aus den Felder holen
	person_id = document.getElementById('textbox-mitarbeiter-adressen-person_id').value;
	adresstyp = document.getElementById('menulist-mitarbeiter-adressen-adresstyp').value;
	name = document.getElementById('menulist-mitarbeiter-adressen-adresstyp').label;
	nation = document.getElementById('menulist-mitarbeiter-adressen-nation').value;
	strasse = document.getElementById('textbox-mitarbeiter-adressen-strasse').value;
	plz = document.getElementById('textbox-mitarbeiter-adressen-plz').value;
	gemeinde = document.getElementById('textbox-mitarbeiter-adressen-gemeinde').value;
	ort = document.getElementById('textbox-mitarbeiter-adressen-ort').value;
	zustelladresse = document.getElementById('checkbox-mitarbeiter-adressen-zustelladresse').checked;
	bismeldeadresse = document.getElementById('checkbox-mitarbeiter-adressen-bismeldeadresse').checked;
	
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	//Parameterliste zusammenbauen
	var param = "type=adresse";
	if(MitarbeiterDetailAdress_id!=null)
		param = param + "&new=false";
	else
		param = param + "&new=true";
	
	param = param + "&adress_id="+encodeURIComponent(MitarbeiterDetailAdress_id);
	param = param + "&person_id="+encodeURIComponent(person_id);
	param = param + "&adresstyp="+encodeURIComponent(adresstyp);
	param = param + "&name="+encodeURIComponent(name);
	param = param + "&nation="+encodeURIComponent(nation);
	param = param + "&strasse="+encodeURIComponent(strasse);
	param = param + "&plz="+encodeURIComponent(plz);
	param = param + "&gemeinde="+encodeURIComponent(gemeinde);
	param = param + "&ort="+encodeURIComponent(ort);
	param = param + "&zustelladresse="+encodeURIComponent(zustelladresse);
	param = param + "&bismeldeadresse="+encodeURIComponent(bismeldeadresse);
	
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
   		window.opener.setStatusBarText("Adresse erfolgreich gespeichert");
   		window.opener.document.getElementById("tree-liste-adressen").builder.refresh();
   		//Fenster schliessen
		window.close();
   	}
   	else
   	{
		alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
   	}

	return true;

	
}