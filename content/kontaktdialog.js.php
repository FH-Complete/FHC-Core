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
function KontaktInit(kontakt_id, person_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	if(kontakt_id!='')
	{
		//Daten holen
		var url = '<?php echo APP_ROOT ?>rdf/kontakt.rdf.php?kontakt_id='+kontakt_id+'&'+gettimestamp();
			
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);
	    
	    var dsource = rdfService.GetDataSourceBlocking(url);
	    
		var subject = rdfService.GetResource("http://www.technikum-wien.at/kontakt/" + kontakt_id);
	
		var predicateNS = "http://www.technikum-wien.at/kontakt/rdf";
	
		//RDF parsen
	
		person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
		anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
		kontakt = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kontakt" ));
		zustellung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zustellung" ));
		standort_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#standort_id" ));
		typ = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kontakttyp" ));
		neu = false;
	}
	else
	{
		//Defaultwerte bei Neuem Datensatz
		neu = true;
		anmerkung='';
		kontakt='';
		zustellung='Ja';
		standort_id='';
		typ='email';
	}		
	
	document.getElementById('kontakt-checkbox-neu').checked=neu;
	document.getElementById('kontakt-textbox-person_id').value=person_id;
	document.getElementById('kontakt-textbox-kontakt_id').value=kontakt_id;
	document.getElementById('kontakt-textbox-anmerkung').value=anmerkung;
	document.getElementById('kontakt-textbox-kontakt').value=kontakt;
	document.getElementById('kontakt-menulist-typ').value=typ;
	document.getElementById('kontakt-menulist-firma').value=standort_id;
	
	if(zustellung=='Ja')
		document.getElementById('kontakt-checkbox-zustellung').checked=true;
	else
		document.getElementById('kontakt-checkbox-zustellung').checked=false;
}

// ****
// * Speichern der Daten
// ****
function KontaktSpeichern()
{
	if(window.opener.KontaktKontaktSpeichern(document))
		window.close();
}