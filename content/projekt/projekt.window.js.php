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

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');

$user = get_uid();
loadVariables($user);
?>

// ****
// * Laedt die zu bearbeitenden Daten
// ****
function initProjekt(projekt_kurzbz)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	/*if(projekt_kurzbz!='')
	{
		//Daten holen
		var url = '<?php echo APP_ROOT ?>rdf/bankverbindung.rdf.php?bankverbindung_id='+bankverbindung_id+'&'+gettimestamp();
			
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);
	    
	    var dsource = rdfService.GetDataSourceBlocking(url);
	    
		var subject = rdfService.GetResource("http://www.technikum-wien.at/bankverbindung/" + bankverbindung_id);
	
		var predicateNS = "http://www.technikum-wien.at/bankverbindung/rdf";
	
		//RDF parsen
	
		person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
		name = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#name" ));
		anschrift = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anschrift" ));
		bic = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bic" ));
		blz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#blz" ));
		iban = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#iban" ));
		kontonr = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kontonr" ));
		typ = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#typ" ));
		verrechnung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#verrechnung" ));
		neu = false;
	}
	else
	{
		//Defaultwerte bei Neuem Datensatz
		neu = true;
		name='';
		anschrift='';
		bic='';
		blz='';
		iban='';
		kontonr='';
		typ='p';
		verrechnung='Ja';
	}		
	
	document.getElementById('bankverbindung-checkbox-neu').checked=neu;
	document.getElementById('bankverbindung-textbox-person_id').value=person_id;
	document.getElementById('bankverbindung-textbox-bankverbindung_id').value=bankverbindung_id;
	document.getElementById('bankverbindung-textbox-name').value=name;
	document.getElementById('bankverbindung-textbox-anschrift').value=anschrift;
	document.getElementById('bankverbindung-textbox-bic').value=bic;
	document.getElementById('bankverbindung-textbox-blz').value=blz;
	document.getElementById('bankverbindung-textbox-iban').value=iban;
	document.getElementById('bankverbindung-textbox-kontonr').value=kontonr;
	document.getElementById('bankverbindung-menulist-typ').value=typ;
	if(verrechnung=='Ja')
		document.getElementById('bankverbindung-checkbox-verrechnung').checked=true;
	else
		document.getElementById('bankverbindung-checkbox-verrechnung').checked=false;	*/
}

// ****
// * Speichern der Daten
// ****
function saveProjekt()
{
    var oe_kurzbz=document.getElementById('textbox-projekt-oe').value;
    var projekt_kurzbz=document.getElementById('textbox-projekt-projekt_kurzbz').value;
    //alert (projekt_kurzbz);
    var titel=document.getElementById('textbox-projekt-titel').value;
    var nummer=document.getElementById('textbox-projekt-nummer').value;
    var beschreibung=document.getElementById('textbox-projekt-beschreibung').value;
    var beginn=document.getElementById('textbox-projekt-beginn').value;
    var ende=document.getElementById('textbox-projekt-ende').value;
    // Variablen checken
    
    // SOAP-Action
    var soapBody = new SOAPObject("saveProjekt");
    soapBody.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
    soapBody.appendChild(new SOAPObject("oe_kurzbz")).val(oe_kurzbz);
    soapBody.appendChild(new SOAPObject("titel")).val(titel);
    soapBody.appendChild(new SOAPObject("nummer")).val(nummer);
    soapBody.appendChild(new SOAPObject("beschreibung")).val(beschreibung);
    soapBody.appendChild(new SOAPObject("beginn")).val(beginn);
    soapBody.appendChild(new SOAPObject("ende")).val(ende);
    soapBody.appendChild(new SOAPObject("neu")).val('true');
    
    var sr = new SOAPRequest("saveProjekt",soapBody);
    SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projekt.soap.php?"+gettimestamp();
    SOAPClient.SendRequest(sr, clb_saveProjekt);
}

function clb_saveProjekt(respObj)
{
    try
    {
        var msg = respObj.Body[0].saveProjektResponse[0].message[0].Text;
		window.opener.ProjektmenueRefresh();
		window.opener.ProjektTreeRefresh();
		window.close();
    }
    catch(e)
    {
		var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
		alert('Fehler: '+fehler);
    }
}