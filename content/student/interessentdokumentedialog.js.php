<?php
/* Copyright (C) 2014 fhcomplete.org
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

require_once('../../config/vilesci.config.inc.php');

?>

var glob_prestudent_id;
var glob_akte_id;

// ****
// * Laedt das Dokumente / Akte
// ****
function InteressentDokumenteDialogInit(prestudent_id, akte_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	if(akte_id!='')
	{
		glob_prestudent_id = prestudent_id;
		glob_akte_id = akte_id;

		//Daten holen
		var url = '<?php echo APP_ROOT ?>rdf/akte.rdf.php?akte_id='+akte_id+'&'+gettimestamp();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);

	    var dsource = rdfService.GetDataSourceBlocking(url);

		var subject = rdfService.GetResource("http://www.technikum-wien.at/akte/" + akte_id);

		var predicateNS = "http://www.technikum-wien.at/akte/rdf";

		//RDF parsen
		var titel_intern = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titel_intern" ));
		var anmerkung_intern = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung_intern" ));
		var anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
		var nachgereicht = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nachgereicht" ));
		var nachgereicht_am = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nachgereicht_am" ));
		var dokument_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#dokument_kurzbz" ));
		var dokument_bezeichnung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#dokument_bezeichnung" ));
	}
	//Wenn eine Akte geladen wird, deren Dokumenttyp nicht im Dropdown der Dokumenttypen aufscheint, wird der Typ hinzugefuegt
	var vorhanden = document.getElementById('interessent-dokumente-dialog-menulist-dokument_kurzbz').getElementsByAttribute('value',dokument_kurzbz);
	if(typeof(vorhanden[0])=='undefined')
	{
		dokumentemenue = document.getElementById("interessent-dokumente-dialog-menulist-dokument_kurzbz").children[1];
		var menuentry = document.createElement("menuitem");
		menuentry.setAttribute("value",dokument_kurzbz);
		menuentry.setAttribute("label",dokument_bezeichnung);
		dokumentemenue.appendChild(menuentry);
	}

	document.getElementById('interessent-dokumente-dialog-textbox-titel').value=titel_intern;
	document.getElementById('interessent-dokumente-dialog-textbox-anmerkung').value=anmerkung_intern;

	MenulistSelectItemOnValue('interessent-dokumente-dialog-menulist-dokument_kurzbz',dokument_kurzbz);
	document.getElementById('interessent-dokumente-dialog-label-anmerkung').value=anmerkung;

	if(nachgereicht=='Ja')
		document.getElementById('interessent-dokumente-dialog-label-nachgereicht').value='Dokument wird nachgereicht';
	else
		document.getElementById('interessent-dokumente-dialog-label-nachgereicht').value='';

	document.getElementById('interessent-dokumente-dialog-textbox-nachgereicht_am').value=ConvertDateToGerman(nachgereicht_am);
}

// ****
// * Speichert die Dokumentenaenderung
// ****
function InteressentDokumenteDialogSpeichern()
{
	if(window.opener.InteressentDokumenteDialogSpeichern(document, glob_prestudent_id, glob_akte_id))
		window.close();
}
