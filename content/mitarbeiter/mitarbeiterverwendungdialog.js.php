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
var MitarbeiterVerwendungDetailBisverwendungId=''; // enthaelt die bisverwendung_id
var MitarbeiterVerwendungDetailMitarbeiterUid=''; // enthaelt die mitarbeiterUID
var MitarbeiterVerwendungDetailNeu=false; // true wenn eine neuer Datensatz angelegt wird false beim Bearbeiten

// ****
// * Initialisiert den Verwendungsdialog
// ****
function MitarbeiterVerwendungInit(mitarbeiter_uid, bisverwendung_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	MitarbeiterVerwendungDetailMitarbeiterUid=mitarbeiter_uid;

	if(bisverwendung_id!='')
	{
		//Wenn eine BisverwendungID uebergeben wird, dann wird dieser Datensatz geladen
		MitarbeiterVerwendungDetailNeu='false';
		MitarbeiterVerwendungDetailBisverwendungId=bisverwendung_id;

		//Laden der Daten
		//Daten holen
		var url = '<?php echo APP_ROOT ?>rdf/bisverwendung.rdf.php?bisverwendung_id='+bisverwendung_id+'&'+gettimestamp();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);

	    var dsource = rdfService.GetDataSourceBlocking(url);

		var subject = rdfService.GetResource("http://www.technikum-wien.at/bisverwendung/" + bisverwendung_id);

		var predicateNS = "http://www.technikum-wien.at/bisverwendung/rdf";

		//RDF parsen

		ba1code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ba1code" ));
		ba2code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ba2code" ));
		beschausmasscode = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beschausmasscode" ));
		verwendung_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#verwendung_code" ));
		mitarbeiter_uid = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#mitarbeiter_uid" ));
		hauptberufcode = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#hauptberufcode" ));
		hauptberuflich = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#hauptberuflich" ));
		habilitation = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#habilitation" ));
		beginn = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beginn" ));
		ende = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ende" ));
		vertragsstunden = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vertragsstunden" ));
		dv_art = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#dv_art" ));
		inkludierte_lehre = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#inkludierte_lehre" ));
		zeitaufzeichnungspflichtig = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zeitaufzeichnungspflichtig" ));
		azgrelevant = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#azgrelevant" ));
		homeoffice = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#homeoffice" ));
	}
	else
	{
		//Laden der Daten
		//Daten holen
		var url = '<?php echo APP_ROOT ?>rdf/personal.rdf.php?uid='+MitarbeiterVerwendungDetailMitarbeiterUid+'&'+gettimestamp();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);

	    var dsource = rdfService.GetDataSourceBlocking(url);

		var subject = rdfService.GetResource("http://www.technikum-wien.at/mitarbeiter/" + MitarbeiterVerwendungDetailMitarbeiterUid);

		var predicateNS = "http://www.technikum-wien.at/mitarbeiter/rdf";

		fixangestellt = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#fixangestellt" ));
		azgrelevant = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#azgrelevant" ));
        homeoffice = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#homeoffice" ));
		//neuer Datensatz wird angelegt
		MitarbeiterVerwendungDetailNeu='true';

		//Defaultwerte
		ba1code=103; //fixer Dienstvertrag
		ba2code=1; //unbefristet
		beschausmasscode=1; //Vollzeit
		verwendung_code=1; //Lehr- und Forschungspersonal
		hauptberufcode='';
		hauptberuflich='Ja';
		habilitation='Nein';
		beginn = '<?php echo date('d.m.Y') ?>';
		ende='';
		vertragsstunden='38.5';
		dv_art='';
		inkludierte_lehre='';
		if (fixangestellt=='Ja')
		{
			zeitaufzeichnungspflichtig='Ja';
		}
		else
		{
			zeitaufzeichnungspflichtig='Nein';
		}
		if (!azgrelevant)
		{
			azgrelevant='Nein';
		}
		else
		{
			azgrelevant='Ja';
		}
		
		if (!homeoffice)
		{
            homeoffice='Nein';
		}
		else
		{
            homeoffice='Ja';
		}
	}

	document.getElementById('mitarbeiter-verwendung-detail-menulist-beschart1').value=ba1code;
	document.getElementById('mitarbeiter-verwendung-detail-menulist-beschart2').value=ba2code;
	document.getElementById('mitarbeiter-verwendung-detail-menulist-ausmass').value=beschausmasscode;
	document.getElementById('mitarbeiter-verwendung-detail-menulist-verwendung').value=verwendung_code;
	document.getElementById('mitarbeiter-verwendung-detail-menulist-hauptberuf').value=hauptberufcode;
	if(hauptberuflich=='Ja')
		document.getElementById('mitarbeiter-verwendung-detail-checkbox-hauptberuflich').checked=true;
	else
		document.getElementById('mitarbeiter-verwendung-detail-checkbox-hauptberuflich').checked=false;

	if(habilitation=='Ja')
		document.getElementById('mitarbeiter-verwendung-detail-checkbox-habilitation').checked=true;
	else
		document.getElementById('mitarbeiter-verwendung-detail-checkbox-habilitation').checked=false;

	document.getElementById('mitarbeiter-verwendung-detail-datum-beginn').value=beginn;
	document.getElementById('mitarbeiter-verwendung-detail-datum-ende').value=ende;
	document.getElementById('mitarbeiter-verwendung-detail-textbox-vertragsstunden').value=vertragsstunden;
	document.getElementById('mitarbeiter-verwendung-detail-textbox-dv_art').value=dv_art;
	document.getElementById('mitarbeiter-verwendung-detail-textbox-inkludierte_lehre').value=inkludierte_lehre;
	if(zeitaufzeichnungspflichtig=='Ja')
		document.getElementById('mitarbeiter-verwendung-detail-checkbox-zeitaufzeichnungspflichtig').checked=true;
	else
		document.getElementById('mitarbeiter-verwendung-detail-checkbox-zeitaufzeichnungspflichtig').checked=false;
	if(azgrelevant=='Nein')
		document.getElementById('mitarbeiter-verwendung-detail-checkbox-azgrelevant').checked=false;
	else
		document.getElementById('mitarbeiter-verwendung-detail-checkbox-azgrelevant').checked=true;
	
    if(homeoffice=='Nein')
        document.getElementById('mitarbeiter-verwendung-detail-checkbox-homeoffice').checked=false;
    else
        document.getElementById('mitarbeiter-verwendung-detail-checkbox-homeoffice').checked=true;
	MitarbeiterVerwendungDetailToggleHauptberuf();
	MitarbeiterVerwendungVerwendungChange();
}

// ****
// * Wenn die Checkbox Hauptberuflich angeklickt ist, dann wird die Menulist Hauptberuf deaktiviert
// ****
function MitarbeiterVerwendungDetailToggleHauptberuf()
{
	var checked = document.getElementById('mitarbeiter-verwendung-detail-checkbox-hauptberuflich').checked;

	if(checked)
	{
		document.getElementById('mitarbeiter-verwendung-detail-menulist-hauptberuf').disabled=true;
	}
	else
	{
		document.getElementById('mitarbeiter-verwendung-detail-menulist-hauptberuf').disabled=false;
	}
}

// ****
// * Speichern der Verwendung
// ****
function MitarbeiterVerwendungDetailSpeichern()
{
	if(window.opener.MitarbeiterVerwendungSpeichern(document, MitarbeiterVerwendungDetailBisverwendungId, MitarbeiterVerwendungDetailMitarbeiterUid, MitarbeiterVerwendungDetailNeu))
		window.close();
	else
		this.focus();
}

function MitarbeiterVerwendungVerwendungChange()
{

}
