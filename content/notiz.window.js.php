<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */

require_once('../config/vilesci.config.inc.php');

?>

// ****
// * Laedt die zu bearbeitenden Daten
// ****
function NotizInit(id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	if(id!='')
	{
		//Daten holen
		var url = '<?php echo APP_ROOT ?>rdf/notiz.rdf.php?notiz_id='+id+'&'+gettimestamp();
			
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);
	    
	    var dsource = rdfService.GetDataSourceBlocking(url);
	    
		var subject = rdfService.GetResource("http://www.technikum-wien.at/notiz/" + id);
	
		var predicateNS = "http://www.technikum-wien.at/notiz/rdf";
	
		//RDF parsen
	
		titel = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titel" ));
		text = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#text" ));
		start = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#start" ));
		ende = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ende" ));
		verfasser = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#verfasser_uid" ));
		bearbeiter = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bearbeiter_uid" ));
		erledigt = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#erledigt" ));
		if(erledigt=='Ja')
			erledigt=true;
		else
			erledigt=false;
	}
	else
	{
		//Defaultwerte bei Neuem Datensatz
		titel='';
		text='';
		start='';
		ende='';
		id='';
		verfasser=getUsername();
		bearbeiter='';
		erledigt=false;
	}		
	
	document.getElementById('notiz-textbox-notiz_id').value=id;
	document.getElementById('notiz-textbox-titel').value=titel;
	document.getElementById('notiz-textbox-text').value=text;
	document.getElementById('notiz-box-start').value=start;
	document.getElementById('notiz-box-ende').value=ende;
	document.getElementById('notiz-textbox-verfasser').value=verfasser;
	document.getElementById('notiz-checkbox-erledigt').checked=erledigt;
	if(bearbeiter!='')
	{
		menulist = document.getElementById('notiz-menulist-bearbeiter');
		NotizMenulistMitarbeiterLoad(menulist, bearbeiter);
		MenulistSelectItemOnValue('notiz-menulist-bearbeiter', bearbeiter);
	}

}

// ****
// * Speichern der Daten
// ****
function NotizSpeichern()
{
	//Werte holen
	//projekttask_id = document.getElementById('textbox-projekttaskdetail-projekttask_id').value;
	
	var notiz_id = document.getElementById('notiz-textbox-notiz_id').value;
	var titel = document.getElementById('notiz-textbox-titel').value;
	var text = document.getElementById('notiz-textbox-text').value;
	var start = document.getElementById('notiz-box-start').iso;
	var ende = document.getElementById('notiz-box-ende').iso;
	var verfasser_uid = document.getElementById('notiz-textbox-verfasser').value;
	var bearbeiter_uid = MenulistGetSelectedValue('notiz-menulist-bearbeiter');
	var erledigt = document.getElementById('notiz-checkbox-erledigt').checked;
	
	var soapBody = new SOAPObject("saveNotiz");
	soapBody.appendChild(new SOAPObject("notiz_id")).val(notiz_id);
	soapBody.appendChild(new SOAPObject("titel")).val(titel);
	soapBody.appendChild(new SOAPObject("text")).val(text);
	soapBody.appendChild(new SOAPObject("verfasser_uid")).val(verfasser_uid);
	soapBody.appendChild(new SOAPObject("bearbeiter_uid")).val(bearbeiter_uid);
	soapBody.appendChild(new SOAPObject("start")).val(start);
	soapBody.appendChild(new SOAPObject("ende")).val(ende);
	soapBody.appendChild(new SOAPObject("erledigt")).val(erledigt);

	soapBody.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
	soapBody.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
	soapBody.appendChild(new SOAPObject("projekttask_id")).val(projekttask_id);
	soapBody.appendChild(new SOAPObject("uid")).val(uid);
	soapBody.appendChild(new SOAPObject("person_id")).val(person_id);
	soapBody.appendChild(new SOAPObject("prestudent_id")).val(prestudent_id);
	soapBody.appendChild(new SOAPObject("bestellung_id")).val(bestellung_id);
		
	var sr = new SOAPRequest("saveNotiz",soapBody);

	SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/notiz.soap.php?"+gettimestamp();
	SOAPClient.SendRequest(sr, clb_saveNotiz);
}

function clb_saveNotiz(respObj)
{
	try
	{
		var id = respObj.Body[0].saveNotizResponse[0].message[0].Text;
		window.opener.document.getElementById(opener_id).RefreshNotiz();
		window.close();
	}
	catch(e)
	{
		var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
		alert('Fehler: '+fehler);
		return;
	}	
}


// ****
// * Laedt dynamisch die Personen fuer das DropDown Menue
// * Es muessen mindestens 3 Zeichen in das DropDown Menue eingegeben werden
// ****
function NotizMenulistMitarbeiterLoad(menulist, filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	if(typeof(filter)=='undefined')
		v = menulist.value;
	else
		v = filter;

	if(v.length>2)
	{		
		var url = '<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php?filter='+encodeURIComponent(v)+'&'+gettimestamp();
		//nurmittitel=&
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
