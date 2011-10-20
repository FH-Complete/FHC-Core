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
function initProjektphase(projektphase_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
}

// ****
// * Speichern der Daten
// ****
function saveProjektphase()
{
    var projektphase_id=document.getElementById('textbox-projektphase-projektphase_id').value;
    var projekt_kurzbz=document.getElementById('textbox-projektphase-projekt_kurzbz').value;
    var projektphase_fk=document.getElementById('textbox-projektphase-projektphase_fk').value;
     var bezeichnung=document.getElementById('textbox-projektphase-bezeichnung').value;
    var beschreibung=document.getElementById('textbox-projektphase-beschreibung').value;
    var start=document.getElementById('textbox-projektphase-start').value;
    var ende=document.getElementById('textbox-projektphase-ende').value;
    var budget=document.getElementById('textbox-projektphase-budget').value;
    var personentage=document.getElementById('textbox-projektphase-personentage').value;
    
   // Variablen checken
    
    // SOAP-Action
   var soapBody = new SOAPObject("saveProjektphase");
	//soapBody.appendChild(new SOAPObject("username")).val('joe');
	//soapBody.appendChild(new SOAPObject("passwort")).val('waschl');
				
	var phase = new SOAPObject("phase");
	phase.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
	phase.appendChild(new SOAPObject("projektphase_fk")).val(projektphase_fk);
	phase.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
	phase.appendChild(new SOAPObject("bezeichnung")).val(bezeichnung);
	phase.appendChild(new SOAPObject("beschreibung")).val(beschreibung);
	phase.appendChild(new SOAPObject("start")).val(start);
	phase.appendChild(new SOAPObject("ende")).val(ende);
	phase.appendChild(new SOAPObject("budget")).val(budget);
	phase.appendChild(new SOAPObject("personentage")).val(personentage);
    phase.appendChild(new SOAPObject("neu")).val("true");
    
    soapBody.appendChild(phase);
    
    var sr = new SOAPRequest("saveProjektPhase",soapBody);
    SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projektphase.soap.php?"+gettimestamp();
    SOAPClient.SendRequest(sr, clb_saveProjektphase);
}

function clb_saveProjektphase(respObj)
{
    try
    {
    	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        var msg = respObj.Body[0].saveProjektphaseResponse[0].message[0].Text;
		window.opener.ProjektphaseTreeRefresh();
		window.close();
    }
    catch(e)
    {
		var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
		alert('Fehler: '+fehler);
    }
}