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

?>
var projekt_kurzbz='';
var projektphase_id='';
// ****
// * Laedt die zu bearbeitenden Daten
// ****
function initProjektdokument(kurzbz, id)
{
	projekt_kurzbz = kurzbz;
	projektphase_id=id;
}

// ****
// * Speichern der Daten
// ****
function saveZuordnung()
{
   var dms_id = MenulistGetSelectedValue('projektdokument-menulist-dokument');
    // Variablen checken
    
    // SOAP-Action
    var soapBody = new SOAPObject("saveProjektdokumentZuordnung");
    //soapBody.appendChild(new SOAPObject("username")).val('joe');
	//soapBody.appendChild(new SOAPObject("passwort")).val('waschl');
    
    soapBody.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
    soapBody.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
    soapBody.appendChild(new SOAPObject("dms_id")).val(dms_id);
    
    var sr = new SOAPRequest("saveProjektdokumentZuordnung",soapBody);
    SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projekt.soap.php?"+gettimestamp();
    SOAPClient.SendRequest(sr, clb_saveZuordnung);
}

function clb_saveZuordnung(respObj)
{
    try
    {
        var msg = respObj.Body[0].saveProjektdokumentZuordnungResponse[0].message[0].Text;
		window.opener.ProjektDokumentTreeRefresh();
		window.close();
    }
    catch(e)
    {
		var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
		alert('Fehler: '+fehler);
    }
}

// ****
// * Laedt dynamisch die Personen fuer das DropDown Menue
// * Es muessen mindestens 3 Zeichen in das DropDown Menue eingegeben werden
// ****
function ProjektdokumentMenulistDokumentLoad(menulist, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	if(typeof(filter)=='undefined')
		v = menulist.value;
	else
		v = filter;

	if(v.length>2)
	{		
		var url = '<?php echo APP_ROOT; ?>rdf/dms.rdf.php?filter='+encodeURIComponent(v)+'&'+gettimestamp();
		var oldDatasources = menulist.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			menulist.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		menulist.builder.rebuild();
	
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		if(typeof(filter)=='undefined')
			var datasource = rdfService.GetDataSource(url);
		else
			var datasource = rdfService.GetDataSourceBlocking(url);
		datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		menulist.database.AddDataSource(datasource);
		if(typeof(filter)!='undefined')
			menulist.builder.rebuild();
	}
}
