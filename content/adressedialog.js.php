<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');

$user = get_uid();
loadVariables($user);
?>

// ****
// * Laedt die zu bearbeitenden Daten
// ****
function AdresseInit(adresse_id, person_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	if(adresse_id!='')
	{
		//Daten holen
		var url = '<?php echo APP_ROOT ?>rdf/adresse.rdf.php?adresse_id='+adresse_id+'&'+gettimestamp();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);

	    var dsource = rdfService.GetDataSourceBlocking(url);

		var subject = rdfService.GetResource("http://www.technikum-wien.at/adresse/" + adresse_id);

		var predicateNS = "http://www.technikum-wien.at/adresse/rdf";

		//RDF parsen

		person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
		name = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#name" ));
		strasse = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#strasse" ));
		plz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#plz" ));
		ort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ort" ));
		gemeinde = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gemeinde" ));
		nation = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nation" ));
		typ = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#typ" ));
		heimatadresse = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#heimatadresse" ));
		zustelladresse = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zustelladresse" ));
        co_name = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#co_name" ));
		firma_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#firma_id" ));
		rechnungsadresse = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#rechnungsadresse" ));
		anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
		neu = false;
	}
	else
	{
		//Defaultwerte bei Neuem Datensatz
		neu = true;
		name='';
		strasse='';
		plz='';
		ort='';
		gemeinde=''
		nation='A';
		typ='h';
		heimatadresse='Ja';
		zustelladresse='Ja';
        co_name = '';
		firma_id='';
		rechnungsadresse='Nein';
		anmerkung='';
	}

	document.getElementById('adresse-checkbox-neu').checked=neu;
	document.getElementById('adresse-textbox-person_id').value=person_id;
	document.getElementById('adresse-textbox-adresse_id').value=adresse_id;
	document.getElementById('adresse-textbox-name').value=name;
	document.getElementById('adresse-textbox-strasse').value=strasse;
	document.getElementById('adresse-textbox-plz').value=plz;
	AdresseLoadGemeinde(true);
	document.getElementById('adresse-textbox-gemeinde').value=gemeinde;
	AdresseLoadOrtschaft(true);
	document.getElementById('adresse-textbox-ort').value=ort;
	document.getElementById('adresse-menulist-nation').value=nation;
	document.getElementById('adresse-menulist-typ').value=typ;
	if(heimatadresse=='Ja')
		document.getElementById('adresse-checkbox-heimatadresse').checked=true;
	else
		document.getElementById('adresse-checkbox-heimatadresse').checked=false;

	if(zustelladresse=='Ja')
		document.getElementById('adresse-checkbox-zustelladresse').checked=true;
	else
		document.getElementById('adresse-checkbox-zustelladresse').checked=false;
    document.getElementById('adresse-textbox-co_name').value = co_name;
	document.getElementById('adresse-menulist-firma').value=firma_id;
	document.getElementById('adresse-textbox-anmerkung').value=anmerkung;
	if(rechnungsadresse=='Ja')
		document.getElementById('adresse-checkbox-rechnungsadresse').checked=true;
	else
		document.getElementById('adresse-checkbox-rechnungsadresse').checked=false;
}

// ****
// * Speichern der Daten
// ****
function AdresseSpeichern()
{
	if(window.opener.KontaktAdresseSpeichern(document))
		window.close();
	else
		this.focus();
}

// ****
// * Laedt die Gemeinden zur eingegebenen Postleitzahl
// ****
function AdresseLoadGemeinde(blocking)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	menulist_gemeinde = document.getElementById('adresse-textbox-gemeinde');
	if(document.getElementById('adresse-menulist-nation').value=='A')
	{
		menulist_gemeinde.value='';
		document.getElementById('adresse-textbox-ort').value='';
	}
	plz = document.getElementById('adresse-textbox-plz').value;

	if(plz.length>3)
	{
		var url = '<?php echo APP_ROOT; ?>rdf/gemeinde.rdf.php?plz='+plz+'&'+gettimestamp();

		var oldDatasources = menulist_gemeinde.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			menulist_gemeinde.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		menulist_gemeinde.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		if(blocking)
			var datasource = rdfService.GetDataSourceBlocking(url);
		else
			var datasource = rdfService.GetDataSource(url);
		datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		menulist_gemeinde.database.AddDataSource(datasource);

		menulist_gemeinde.builder.rebuild();
	}
}

// ****
// * Laedt die Ortschaften zu Plz und Gemeinde
// ****
function AdresseLoadOrtschaft(blocking)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	gemeinde = document.getElementById('adresse-textbox-gemeinde').value;
	menulist_ort = document.getElementById('adresse-textbox-ort');
	if(document.getElementById('adresse-menulist-nation').value=='A')
	{
		menulist_ort.value='';
	}
	plz = document.getElementById('adresse-textbox-plz').value;

	if(plz.length>3 && gemeinde!='')
	{
		var url = '<?php echo APP_ROOT; ?>rdf/gemeinde.rdf.php?plz='+plz+'&gemeinde='+encodeURIComponent(gemeinde)+'&'+gettimestamp();

		var oldDatasources = menulist_ort.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			menulist_ort.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		menulist_ort.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		if(blocking)
			var datasource1 = rdfService.GetDataSourceBlocking(url);
		else
			var datasource1 = rdfService.GetDataSource(url);
		datasource1.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasource1.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		menulist_ort.database.AddDataSource(datasource1);

		menulist_ort.builder.rebuild();
	}
}