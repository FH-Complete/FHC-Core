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
require_once('../../include/functions.inc.php');

$user = get_uid();

if(false): ?> <script type="text/javascript"><?php endif; ?>
    
var MitarbeiterVertragNeuBetragOld=0;
var addon = Array();

function MitarbeiterVertragNeuInit(person_id, vertrag_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	// *** Nicht zugeordnete Vertragselemente laden
	vertragoffentree = document.getElementById('mitarbeiter-vertrag-tree-nichtzugeordnet');
	url='<?php echo APP_ROOT;?>rdf/vertragdetails.rdf.php?person_id='+person_id+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = vertragoffentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		vertragoffentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	vertragoffentree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	vertragoffentree.database.AddDataSource(datasource);

	if(typeof(vertrag_id)=='undefined' || vertrag_id=='')
	{
		// Neuer Eintrag

		// Aktuelles Datum holen
		var now = new Date();
		var jahr = now.getFullYear();
		var monat = now.getMonth()+1;
		var tag = now.getDate();

		// Zweistellige Monats und Tagangaben
		monat = ((monat<10)?"0":"")+monat;
		tag = ((tag<10)?"0":"")+tag;

		document.getElementById('mitarbeiter-vertrag-neu-textbox-bezeichnung').value=jahr+monat+tag+'';
		document.getElementById('mitarbeiter-vertrag-neu-box-vertragsdatum').value=tag+'.'+monat+'.'+jahr;
	}
	else
	{
		// Bearbeiten eines bestehenden Eintrages

		var url = '<?php echo APP_ROOT ?>rdf/vertrag.rdf.php?vertrag_id='+vertrag_id+'&'+gettimestamp();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
			           getService(Components.interfaces.nsIRDFService);
		var dsource = rdfService.GetDataSourceBlocking(url);
		var subject = rdfService.GetResource("http://www.technikum-wien.at/vertrag/" + vertrag_id);

		var predicateNS = "http://www.technikum-wien.at/vertrag/rdf";

		//Daten holen
		vertragstyp_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vertragstyp_kurzbz" ));
		betrag = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#betrag" ));
		bezeichnung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bezeichnung" ));
		anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
		vertragsdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vertragsdatum" ));
        vertragsstunden = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vertragsstunden" ));
        vertragsstunden_studiensemester_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vertragsstunden_studiensemester_kurzbz" ));

		MitarbeiterVertragNeuBetragOld = betrag;

		document.getElementById('mitarbeiter-vertrag-neu-textbox-bezeichnung').value=bezeichnung;
		document.getElementById('mitarbeiter-vertrag-neu-textbox-betrag').value=betrag;
		MenulistSelectItemOnValue('mitarbeiter-vertrag-neu-menulist-vertragstyp', vertragstyp_kurzbz)
		document.getElementById('mitarbeiter-vertrag-neu-textbox-vertrag_id').value=vertrag_id;
		document.getElementById('mitarbeiter-vertrag-neu-textbox-anmerkung').value=anmerkung;
		document.getElementById('mitarbeiter-vertrag-neu-box-vertragsdatum').value=vertragsdatum;
        document.getElementById('mitarbeiter-vertrag-neu-textbox-vertragsstunden').value = vertragsstunden;
        document.getElementById('mitarbeiter-vertrag-neu-textbox-vertragsstunden_studiensemester_kurzbz').value = vertragsstunden_studiensemester_kurzbz;

	}
	for(i in addon)
	{
	    if(typeof addon[i].AddonKtuaddEventlistenerVertrag == 'function')
	    {
		addon[i].AddonKtuaddEventlistenerVertrag();
	    }
	}
}

/**
 * Generiert einen Vertrag aus den markierten Elementen
 */
function MitarbeiterVertragNeuGenerateVertrag()
{
	if(window.opener.MitarbeiterVertragGenerateVertrag(document))
		window.close();
}

/**
 * Wenn im Baum Eintraege markiert werden, wird die Summe der markierten Eintraege berechnet
 * und in das Betrag Feld geschrieben
 */
function MitarbeiterVertragNeuSelectEntry()
{
	var tree = document.getElementById('mitarbeiter-vertrag-tree-nichtzugeordnet');

	if (tree.currentIndex==-1)
		return;

	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();

	var betragssumme = MitarbeiterVertragNeuBetragOld;
	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			betrag = getTreeCellText(tree, "mitarbeiter-vertrag-tree-nichtzugeordnet-betrag", v);
			betragssumme = (parseFloat(betragssumme)+parseFloat(betrag));
		}
	}
	
	document.getElementById('mitarbeiter-vertrag-neu-textbox-betrag').value=betragssumme;
}
