<?php
	header("Cache-Control: no-cache");
	header("Cache-Control: post-check=0, pre-check=0",false);
	header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
	include("../vilesci/config.inc.php");
		
	if(isset($_GET['funktion_id']) && $_GET['funktion_id']!='' && is_numeric($_GET['funktion_id']))
		echo "var MitarbeiterDetailFunktion_id=".$_GET['funktion_id'].";";
	else 
		echo "var MitarbeiterDetailFunktion_id=null;";
?>

function Check()
{
	if(MitarbeiterDetailFunktion_id!=null)
		window.setTimeout("MitarbeiterFunktionBearbeiten()",window.opener.sleep_time);
	else
		window.setTimeout("MitarbeiterFunktionDefault()",window.opener.sleep_time);
}

/**
 * Setzt die Defaultwerte
 */
function MitarbeiterFunktionDefault()
{	
	MitarbeiterDetailStudiensemester_id = document.getElementById('textbox-mitarbeiter-funktion-stsem_id').value;
	document.getElementById('menulist-mitarbeiter-funktion-fachbereich').value = 2;
	document.getElementById('textbox-mitarbeiter-funktion-beschreibung').value = '';
	document.getElementById('menulist-mitarbeiter-funktion-funktion').value = 0;
	document.getElementById('menulist-mitarbeiter-funktion-beschart1').value = 3;
	document.getElementById('menulist-mitarbeiter-funktion-beschart2').value = 2;
	document.getElementById('menulist-mitarbeiter-funktion-verwendung').value = 0;
	document.getElementById('menulist-mitarbeiter-funktion-hauptberuf').value = 0;
	document.getElementById('checkbox-mitarbeiter-funktion-hauptberuflich').checked = false;
	document.getElementById('checkbox-mitarbeiter-funktion-entwicklungsteam').checked = false;
	document.getElementById('menulist-mitarbeiter-funktion-qualifikation').disabled = true;
	document.getElementById('menulist-mitarbeiter-funktion-qualifikation').value = 0;
	document.getElementById('menulist-mitarbeiter-funktion-ausmass').value = 1;
	document.getElementById('menulist-mitarbeiter-funktion-studiensemester').value = MitarbeiterDetailStudiensemester_id;	
}

/**
 * Laedt den zu bearbeitenden Datensatz
 */
function MitarbeiterFunktionBearbeiten()
{
		// RDF vom Server holen

		// Url zum RDF
		var url="<?php echo APP_ROOT; ?>rdf/fas/funktionen.rdf.php?funktion_id="+MitarbeiterDetailFunktion_id+'&'+window.opener.gettimestamp();
	
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
		var dsource=parseRDFString(response, 'http://www.technikum-wien.at/funktionen/alle');
		
		// Trick 17	(sonst gibt's ein Permission denied)
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			
		// Daten aus RDF auslesen
		dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);
	
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);
		var subject = rdfService.GetResource("http://www.technikum-wien.at/funktionen/" + MitarbeiterDetailFunktion_id);

	   	var predicateNS = "http://www.technikum-wien.at/funktionen/rdf";
	   	
		//Felder befuellen		
		mitarbeiter_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#mitarbeiter_id" ));
		studiensemester_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#studiensemester_id" ));
		erhalter_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#erhalter_id" ));
		studiengang_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#studiengang_id" ));
		fachbereich_id = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#fachbereich_id" ));
		name = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#name" ));
		funktion = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#funktion" ));
		beschart1 = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#beschart1" ));
		beschart2 = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#beschart2" ));
		verwendung = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#verwendung" ));
		hauptberuf = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#hauptberuf" ));
		hauptberuflich = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#hauptberuflich" ));
		entwicklungsteam = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#entwicklungsteam" ));
		qualifikation = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#besonderequalifikation" ));
		ausmass = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#ausmass" ));
		
		document.getElementById('textbox-mitarbeiter-funktion-mitarbeiter_id').value = mitarbeiter_id;
		document.getElementById('menulist-mitarbeiter-funktion-studiensemester').value = studiensemester_id;
		document.getElementById('menulist-mitarbeiter-funktion-erhalter').value = erhalter_id;
		document.getElementById('menulist-mitarbeiter-funktion-studiengang').value = studiengang_id;
		document.getElementById('menulist-mitarbeiter-funktion-fachbereich').value = fachbereich_id;
		document.getElementById('textbox-mitarbeiter-funktion-beschreibung').value = name;
		document.getElementById('menulist-mitarbeiter-funktion-funktion').value = funktion;
		document.getElementById('menulist-mitarbeiter-funktion-beschart1').value = beschart1;
		document.getElementById('menulist-mitarbeiter-funktion-beschart2').value = beschart2;
		document.getElementById('menulist-mitarbeiter-funktion-verwendung').value = verwendung;
		document.getElementById('menulist-mitarbeiter-funktion-hauptberuf').value = hauptberuf;
		if(hauptberuflich=='Ja')
		{
			document.getElementById('checkbox-mitarbeiter-funktion-hauptberuflich').checked = true;
			document.getElementById('menulist-mitarbeiter-funktion-hauptberuf').disabled = true;
		}
		else
		{
			document.getElementById('checkbox-mitarbeiter-funktion-hauptberuflich').checked = false;
			document.getElementById('menulist-mitarbeiter-funktion-hauptberuf').disabled = false;
		}
		if(entwicklungsteam=='Ja')
		{
			document.getElementById('checkbox-mitarbeiter-funktion-entwicklungsteam').checked = true;
			document.getElementById('menulist-mitarbeiter-funktion-qualifikation').disabled = false;
		}
		else
		{
			document.getElementById('checkbox-mitarbeiter-funktion-entwicklungsteam').checked = false;
				document.getElementById('menulist-mitarbeiter-funktion-qualifikation').disabled = true;
		}
		document.getElementById('menulist-mitarbeiter-funktion-qualifikation').value = qualifikation;
		document.getElementById('menulist-mitarbeiter-funktion-ausmass').value = ausmass;
}

function MitarbeiterFunktionValueChange()
{   
}

/**
 * Speichert die eingegebene Funktion
 */
function MitarbeiterFunktionSave()
{		
	//Daten aus den Feldern holen
	mitarbeiter_id = document.getElementById('textbox-mitarbeiter-funktion-mitarbeiter_id').value;
	studiensemester_id = document.getElementById('menulist-mitarbeiter-funktion-studiensemester').value;
	erhalter_id = document.getElementById('menulist-mitarbeiter-funktion-erhalter').value;
	studiengang_id = document.getElementById('menulist-mitarbeiter-funktion-studiengang').value;
	fachbereich_id = document.getElementById('menulist-mitarbeiter-funktion-fachbereich').value;
	name = document.getElementById('textbox-mitarbeiter-funktion-beschreibung').value;
	funktion = document.getElementById('menulist-mitarbeiter-funktion-funktion').value;
	beschart1 = document.getElementById('menulist-mitarbeiter-funktion-beschart1').value;
	beschart2 = document.getElementById('menulist-mitarbeiter-funktion-beschart2').value;
	verwendung  = document.getElementById('menulist-mitarbeiter-funktion-verwendung').value;
	hauptberuf = document.getElementById('menulist-mitarbeiter-funktion-hauptberuf').value;
	hauptberuflich = document.getElementById('checkbox-mitarbeiter-funktion-hauptberuflich').checked;
	entwicklungsteam = 	document.getElementById('checkbox-mitarbeiter-funktion-entwicklungsteam').checked;
	qualifikation = document.getElementById('menulist-mitarbeiter-funktion-qualifikation').value;
	ausmass = document.getElementById('menulist-mitarbeiter-funktion-ausmass').value;
			
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	// Request absetzen
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	var param = "type=funktion";
	if(MitarbeiterDetailFunktion_id!=null)
		param = param + "&new=false";
	else
		param = param + "&new=true";
	
	param = param + "&funktion_id="+encodeURIComponent(MitarbeiterDetailFunktion_id);
	param = param + "&mitarbeiter_id="+encodeURIComponent(mitarbeiter_id);
	param = param + "&studiensemester_id="+encodeURIComponent(studiensemester_id);
	param = param + "&erhalter_id="+encodeURIComponent(erhalter_id);
	param = param + "&studiengang_id="+encodeURIComponent(studiengang_id);
	param = param + "&fachbereich_id="+encodeURIComponent(fachbereich_id);
	param = param + "&name="+encodeURIComponent(name);
	param = param + "&funktion="+encodeURIComponent(funktion);
	param = param + "&beschart1="+encodeURIComponent(beschart1);
	param = param + "&beschart2="+encodeURIComponent(beschart2);
	param = param + "&verwendung="+encodeURIComponent(verwendung);
	param = param + "&hauptberuf="+encodeURIComponent(hauptberuf);
	param = param + "&hauptberuflich="+encodeURIComponent(hauptberuflich);
	param = param + "&entwicklungsteam="+encodeURIComponent(entwicklungsteam);
	param = param + "&qualifikation="+encodeURIComponent(qualifikation);
	param = param + "&ausmass="+encodeURIComponent(ausmass);
	
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
   		window.opener.setStatusBarText("Funktion erfolgreich gespeichert");
   		window.opener.document.getElementById("tree-liste-funktionen").builder.refresh();
   		window.opener.document.getElementById("textbox-mitarbeiter-detail-aktstatus").value = dbdml_errormsg;
   		//window.opener.refreshtree(true);
   		//Fenster schliessen
		window.close();
   	}
   	else
   	{
		alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
   	}

	return true;

}

function MitarbeiterFunktionHauptberuflichChange()
{
	hauptberuflich = document.getElementById("checkbox-mitarbeiter-funktion-hauptberuflich").checked;
	if(hauptberuflich)
	{
		document.getElementById("menulist-mitarbeiter-funktion-hauptberuf").disabled=false;
		document.getElementById("menulist-mitarbeiter-funktion-hauptberuf").value=0;
	}
	else
	{
		document.getElementById("menulist-mitarbeiter-funktion-hauptberuf").value='';
		document.getElementById("menulist-mitarbeiter-funktion-hauptberuf").disabled=true;
	}
	
}

function MitarbeiterFunktionEntwicklungsteamChange()
{
	entwicklungsteam = document.getElementById("checkbox-mitarbeiter-funktion-entwicklungsteam").checked;
	if(entwicklungsteam)
	{
		document.getElementById("menulist-mitarbeiter-funktion-qualifikation").disabled=true;
		document.getElementById('menulist-mitarbeiter-funktion-qualifikation').value = 0;
	}
	else
	{
		document.getElementById("menulist-mitarbeiter-funktion-qualifikation").value='';
		document.getElementById("menulist-mitarbeiter-funktion-qualifikation").disabled=false;
	}
	
	
}