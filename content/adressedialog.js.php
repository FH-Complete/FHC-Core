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

require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');

$conn = pg_pconnect(CONN_STRING);

$user = get_uid();
loadVariables($conn, $user);
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
	
		//Daten holen
	
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
		firma_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#firma_id" ));
		neu = false;
	}
	else
	{
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
		firma_id='';
	}		
	
	document.getElementById('adresse-checkbox-neu').checked=neu;
	document.getElementById('adresse-textbox-person_id').value=person_id;
	document.getElementById('adresse-textbox-adresse_id').value=adresse_id;
	document.getElementById('adresse-textbox-name').value=name;
	document.getElementById('adresse-textbox-strasse').value=strasse;
	document.getElementById('adresse-textbox-plz').value=plz;
	document.getElementById('adresse-textbox-ort').value=ort;
	document.getElementById('adresse-textbox-gemeinde').value=gemeinde;
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
	document.getElementById('adresse-menulist-firma').value=firma_id;
}

function AdresseSpeichern()
{
	if(window.opener.KontaktAdresseSpeichern(document))
		window.close();
}