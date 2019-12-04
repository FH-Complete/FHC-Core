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

var MitarbeiterVertragLoadedPerson=null
// ****************** FUNKTIONEN ************************** //

function MitarbeiterVertragLoad(person_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	if(typeof(person_id)=='undefined' && MitarbeiterVertragLoadedPerson!='')
	{
		person_id = MitarbeiterVertragLoadedPerson;
	}
	if(person_id=='')
		return;

	MitarbeiterVertragLoadedPerson=person_id;

	// Aktiven Filter Eintrag holen
	var filterpopup = document.getElementById('mitarbeiter-vertrag-menupopup-filter');
	var e = filterpopup.getElementsByTagName("menuitem");
	var filter;
	for (var i = 0; i < e.length; i++)
	{
	   if (e[i].getAttribute("checked"))
			filter=e[i].value
	}

	// *** Vertrag ***
	var treevertrag = document.getElementById('mitarbeiter-vertrag-tree');
	url='<?php echo APP_ROOT;?>rdf/vertrag.rdf.php?person_id='+person_id+"&filter="+filter+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = treevertrag.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		treevertrag.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	treevertrag.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var myDatasource = rdfService.GetDataSourceBlocking(url);
	treevertrag.database.AddDataSource(myDatasource);
	treevertrag.builder.rebuild();

	// Detail Tree leeren
	vertragzugeordnettree = document.getElementById('mitarbeiter-vertrag-tree-zugeordnet');

	//Alte DS entfernen
	var oldDatasources = vertragzugeordnettree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		vertragzugeordnettree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	vertragzugeordnettree.builder.rebuild();


	// Status Tree leeren
	vertragsstatustree = document.getElementById('mitarbeiter-vertrag-tree-vertragsstatus');
	//Alte DS entfernen
	var oldDatasources = vertragsstatustree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		vertragsstatustree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden

	vertragsstatustree.builder.rebuild();

	MitarbeiterVertragDisableFields(false);
}

function MitarbeiterVertragDisableFields(val)
{
}

function MitarbeiterVertragSelectVertrag()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('mitarbeiter-vertrag-tree');
	var col = tree.columns ? tree.columns["mitarbeiter-vertrag-tree-vertrag_id"] : "mitarbeiter-vertrag-tree-vertrag_id";

	if(tree.currentIndex==-1)
		return false;

	var vertrag_id=tree.view.getCellText(tree.currentIndex,col);

	// *** Zugeordnete Vertragselemente laden

	vertragzugeordnettree = document.getElementById('mitarbeiter-vertrag-tree-zugeordnet');
	url='<?php echo APP_ROOT;?>rdf/vertragdetails.rdf.php?vertrag_id='+vertrag_id+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = vertragzugeordnettree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		vertragzugeordnettree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	vertragzugeordnettree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	vertragzugeordnettree.database.AddDataSource(datasource);

	// *** Status laden

	vertragsstatustree = document.getElementById('mitarbeiter-vertrag-tree-vertragsstatus');
	url='<?php echo APP_ROOT;?>rdf/vertragsstatus.rdf.php?vertrag_id='+vertrag_id+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = vertragsstatustree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		vertragsstatustree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	vertragsstatustree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	vertragsstatustree.database.AddDataSource(datasource);

}

/**
 * Generiert einen Vertrag aus den markierten Elementen
 */
function MitarbeiterVertragGenerateVertrag(windowdocument)
{
	var tree = windowdocument.getElementById('mitarbeiter-vertrag-tree-nichtzugeordnet');

	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();

	var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'vertraggenerate');
	req.add('person_id',MitarbeiterVertragLoadedPerson);

	var betrag = windowdocument.getElementById('mitarbeiter-vertrag-neu-textbox-betrag').value;
	var vertragstyp_kurzbz = windowdocument.getElementById('mitarbeiter-vertrag-neu-menulist-vertragstyp').value;
	var bezeichnung = windowdocument.getElementById('mitarbeiter-vertrag-neu-textbox-bezeichnung').value;
	var vertrag_id = windowdocument.getElementById('mitarbeiter-vertrag-neu-textbox-vertrag_id').value;
	var anmerkung = windowdocument.getElementById('mitarbeiter-vertrag-neu-textbox-anmerkung').value;
	var vertragsdatum = windowdocument.getElementById('mitarbeiter-vertrag-neu-box-vertragsdatum').iso;

	if(windowdocument.getElementById("mitarbeiter-vertrag-neu-textbox-fahrten") != null)
	    var fahrten = windowdocument.getElementById("mitarbeiter-vertrag-neu-textbox-fahrten").value;
	else
		var fahrten = 1;

	if(betrag=='')
	{
		alert('Bitte geben Sie einen Betrag ein');
		return false;
	}

	if(vertragstyp_kurzbz == 'fahrtkosten')
	    betrag = betrag * fahrten;

	req.add('betrag',betrag);
	req.add('vertragstyp_kurzbz',vertragstyp_kurzbz);
	req.add('bezeichnung',bezeichnung);
	req.add('vertrag_id',vertrag_id);
	req.add('anmerkung',anmerkung);
	req.add('vertragsdatum',vertragsdatum);

	var anzahl=0;
	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			type = getTreeCellText(tree, "mitarbeiter-vertrag-tree-nichtzugeordnet-type", v);
			stsem = getTreeCellText(tree, "mitarbeiter-vertrag-tree-nichtzugeordnet-studiensemester_kurzbz",v);
			pruefung_id = getTreeCellText(tree, "mitarbeiter-vertrag-tree-nichtzugeordnet-pruefung_id",v);
			projektarbeit_id = getTreeCellText(tree, "mitarbeiter-vertrag-tree-nichtzugeordnet-projektarbeit_id",v);
			mitarbeiter_uid = getTreeCellText(tree, "mitarbeiter-vertrag-tree-nichtzugeordnet-mitarbeiter_uid",v);
			lehreinheit_id = getTreeCellText(tree, "mitarbeiter-vertrag-tree-nichtzugeordnet-lehreinheit_id",v);
			betreuerart_kurzbz = getTreeCellText(tree, "mitarbeiter-vertrag-tree-nichtzugeordnet-betreuerart_kurzbz",v);
            vertragsstunden = getTreeCellText(tree, "mitarbeiter-vertrag-tree-nichtzugeordnet-vertragsstunden",v);
            vertragsstunden_studiensemester_kurzbz = getTreeCellText(tree, "mitarbeiter-vertrag-tree-nichtzugeordnet-vertragsstunden_studiensemester_kurzbz",v);

			req.add('type_'+anzahl, type);
			req.add('stsem_'+anzahl, stsem);
			req.add('pruefung_id_'+anzahl, pruefung_id);
			req.add('projektarbeit_id_'+anzahl, projektarbeit_id);
			req.add('mitarbeiter_uid_'+anzahl, mitarbeiter_uid);
			req.add('lehreinheit_id_'+anzahl, lehreinheit_id);
			req.add('betreuerart_kurzbz_'+anzahl, betreuerart_kurzbz);
            req.add('vertragsstunden'+anzahl, vertragsstunden);
            req.add('vertragsstunden_studiensemester_kurzbz'+anzahl, vertragsstunden_studiensemester_kurzbz);

			anzahl++;
		}
	}

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		MitarbeiterVertragLoad(MitarbeiterVertragLoadedPerson);
		return true;
	}
}

/**
 * Oeffnet den Dialog zum Erstellen eines neuen Vertrags
 */
function MitarbeiterVertragAddVertrag()
{
		window.open('<?php echo APP_ROOT?>content/mitarbeiter/mitarbeitervertragneudialog.xul.php?person_id='+MitarbeiterVertragLoadedPerson,"Vertrag","status=no, width=500, height=400, centerscreen, resizable");
}

/**
 * Fuegt einen neuen Status zu einem Vertrag hinzu
 */
function MitarbeiterVertragStatusAdd(status)
{
	var tree=document.getElementById('mitarbeiter-vertrag-tree');
	var col = tree.columns ? tree.columns["mitarbeiter-vertrag-tree-vertrag_id"] : "mitarbeiter-vertrag-tree-vertrag_id";

	if(tree.currentIndex==-1)
		return false;

	var vertrag_id=tree.view.getCellText(tree.currentIndex,col);

	var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'vertragsstatusadd');
	req.add('vertrag_id',vertrag_id);
	req.add('status',status);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		MitarbeiterVertragSelectVertrag();
		return true;
	}
}

/**
 * Editieren eines Vertrags
 */
function MitarbeiterVertragEdit()
{
	var tree=document.getElementById('mitarbeiter-vertrag-tree');
	var col = tree.columns ? tree.columns["mitarbeiter-vertrag-tree-vertrag_id"] : "mitarbeiter-vertrag-tree-vertrag_id";

	if(tree.currentIndex==-1)

		return false;

	var vertrag_id=tree.view.getCellText(tree.currentIndex,col);

	window.open('<?php echo APP_ROOT?>content/mitarbeiter/mitarbeitervertragneudialog.xul.php?person_id='+MitarbeiterVertragLoadedPerson+'&vertrag_id='+vertrag_id,"Vertrag","status=no, width=500, height=400, centerscreen, resizable");
}

function MitarbeiterVertragDetailDelete()
{
	var tree=document.getElementById('mitarbeiter-vertrag-tree');
	var col = tree.columns ? tree.columns["mitarbeiter-vertrag-tree-vertrag_id"] : "mitarbeiter-vertrag-tree-vertrag_id";

	if(tree.currentIndex==-1)
		return false;

	var vertrag_id=tree.view.getCellText(tree.currentIndex,col);

	var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('vertrag_id',vertrag_id);

	var tree=document.getElementById('mitarbeiter-vertrag-tree-zugeordnet');

	type = getTreeCellText(tree, "mitarbeiter-vertrag-tree-zugeordnet-type", tree.currentIndex);
	stsem = getTreeCellText(tree, "mitarbeiter-vertrag-tree-zugeordnet-studiensemester_kurzbz",tree.currentIndex);
	pruefung_id = getTreeCellText(tree, "mitarbeiter-vertrag-tree-zugeordnet-pruefung_id",tree.currentIndex);
	projektarbeit_id = getTreeCellText(tree, "mitarbeiter-vertrag-tree-zugeordnet-projektarbeit_id",tree.currentIndex);
	mitarbeiter_uid = getTreeCellText(tree, "mitarbeiter-vertrag-tree-zugeordnet-mitarbeiter_uid",tree.currentIndex);
	lehreinheit_id = getTreeCellText(tree, "mitarbeiter-vertrag-tree-zugeordnet-lehreinheit_id",tree.currentIndex);
	betreuerart_kurzbz = getTreeCellText(tree, "mitarbeiter-vertrag-tree-zugeordnet-betreuerart_kurzbz",tree.currentIndex);
	betrag = getTreeCellText(tree, "mitarbeiter-vertrag-tree-zugeordnet-betrag",tree.currentIndex);

	req.add('type', 'vertragsdetaildelete');
	req.add('vertragstype', type);
	req.add('stsem', stsem);
	req.add('pruefung_id', pruefung_id);
	req.add('projektarbeit_id', projektarbeit_id);
	req.add('mitarbeiter_uid', mitarbeiter_uid);
	req.add('lehreinheit_id', lehreinheit_id);
	req.add('betreuerart_kurzbz', betreuerart_kurzbz);
	req.add('betrag', betrag);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		MitarbeiterVertragLoad(MitarbeiterVertragLoadedPerson);
		return true;
	}
}

function MitarbeiterVertragSelectVertragsstatus()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('mitarbeiter-vertrag-tree-vertragsstatus');
	var col = tree.columns ? tree.columns["mitarbeiter-vertrag-tree-vertragsstatus-vertrag_id"] : "mitarbeiter-vertrag-tree-vertragsstatus-vertrag_id";
	var col_status = tree.columns ? tree.columns["mitarbeiter-vertrag-tree-vertragsstatus-vertragsstatus_kurzbz"] : "mitarbeiter-vertrag-tree-vertragsstatus-vertragsstatus_kurzbz";

	if(tree.currentIndex==-1)
		return false;

	var vertrag_id=tree.view.getCellText(tree.currentIndex,col);
	var vertrag_status=tree.view.getCellText(tree.currentIndex,col_status);

	vertragstatustree = document.getElementById('mitarbeiter-vertrag-tree-vertragsstatus');
	url='<?php echo APP_ROOT;?>rdf/vertragsstatus.rdf.php?vertrag_id='+vertrag_id+'&vertragsstatus_kurzbz='+vertrag_status+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);

	var dsource = rdfService.GetDataSourceBlocking(url);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/vertragsstatus/"+vertrag_status +"/"+ vertrag_id);

	var predicateNS = "http://www.technikum-wien.at/vertragsstatus/rdf";

	//Daten holen
	vertragsdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#datum" ));

	// Uhrzeit wegschneiden
	vertragsdatum = vertragsdatum.substring(0,10);

	document.getElementById('mitarbeiter-vertrag-vertragsstatus-textbox-vertragsdatum').value=vertragsdatum;
	document.getElementById('mitarbeiter-vertrag-vertragsstatus-textbox-vertragsdatum').disabled=false;
}

function MitarbeiterVertragVertragsstatusUpdate()
{
    var tree=document.getElementById('mitarbeiter-vertrag-tree-vertragsstatus');
    var col = tree.columns ? tree.columns["mitarbeiter-vertrag-tree-vertragsstatus-vertrag_id"] : "mitarbeiter-vertrag-tree-vertragsstatus-vertrag_id";
    var col_status = tree.columns ? tree.columns["mitarbeiter-vertrag-tree-vertragsstatus-vertragsstatus_kurzbz"] : "mitarbeiter-vertrag-tree-vertragsstatus-vertragsstatus_kurzbz";

    if(tree.currentIndex==-1)
	    return false;

    var vertrag_id=tree.view.getCellText(tree.currentIndex,col);
    var vertrag_status=tree.view.getCellText(tree.currentIndex,col_status);

    var vertrag_datum = document.getElementById("mitarbeiter-vertrag-vertragsstatus-textbox-vertragsdatum").iso;

    var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
    var req = new phpRequest(url,'','');

    req.add('type', 'vertragsstatusupdate');
    req.add('vertrag_id',vertrag_id);
    req.add('status',vertrag_status);
    req.add('datum',vertrag_datum);

    var response = req.executePOST();

    var val =  new ParseReturnValue(response)

    if (!val.dbdml_return)
    {
	    if(val.dbdml_errormsg=='')
		    alert(response)
	    else
		    alert(val.dbdml_errormsg)
    }
    else
    {
		MitarbeiterVertragVertragsstatusReload(vertrag_id);
		return true;
    }
}

function MitarbeiterVertragStatusDelete()
{
	var tree=document.getElementById('mitarbeiter-vertrag-tree-vertragsstatus');
    var col = tree.columns ? tree.columns["mitarbeiter-vertrag-tree-vertragsstatus-vertrag_id"] : "mitarbeiter-vertrag-tree-vertragsstatus-vertrag_id";
    var col_status = tree.columns ? tree.columns["mitarbeiter-vertrag-tree-vertragsstatus-vertragsstatus_kurzbz"] : "mitarbeiter-vertrag-tree-vertragsstatus-vertragsstatus_kurzbz";

    if(tree.currentIndex==-1)
	    return false;

    var vertrag_id=tree.view.getCellText(tree.currentIndex,col);
    var vertrag_status=tree.view.getCellText(tree.currentIndex,col_status);

    var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
    var req = new phpRequest(url,'','');

    req.add('type', 'vertragsstatusdelete');
    req.add('vertrag_id',vertrag_id);
    req.add('status',vertrag_status);

    var response = req.executePOST();

    var val =  new ParseReturnValue(response)

    if (!val.dbdml_return)
    {
	    if(val.dbdml_errormsg=='')
		    alert(response)
	    else
		    alert(val.dbdml_errormsg)
    }
    else
    {
		MitarbeiterVertragVertragsstatusReload(vertrag_id);
		return true;
    }
}

function MitarbeiterVertragVertragsstatusReload(vertrag_id)
{
	// *** Status laden
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var vertragsstatustree = document.getElementById('mitarbeiter-vertrag-tree-vertragsstatus');
	url='<?php echo APP_ROOT;?>rdf/vertragsstatus.rdf.php?vertrag_id='+vertrag_id+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = vertragsstatustree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		vertragsstatustree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	vertragsstatustree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	vertragsstatustree.database.AddDataSource(datasource);

	return true;

}


function MitarbeiterVertragDelete()
{
    var tree=document.getElementById('mitarbeiter-vertrag-tree');
    var col = tree.columns ? tree.columns["mitarbeiter-vertrag-tree-vertrag_id"] : "mitarbeiter-vertrag-tree-vertrag_id";

    if(tree.currentIndex==-1)
        return false;

    var vertrag_id=tree.view.getCellText(tree.currentIndex,col);

    if(confirm('Wollen Sie diesen Eintrag wirklich löschen'))
    {
        var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
        var req = new phpRequest(url,'','');

        req.add('type', 'vertragdelete');
        req.add('vertrag_id',vertrag_id);

        var response = req.executePOST();

        var val =  new ParseReturnValue(response)

        if (!val.dbdml_return)
        {
            if(val.dbdml_errormsg=='')
                alert(response)
            else
                alert(val.dbdml_errormsg)
        }
        else
        {
            MitarbeiterVertragLoad(MitarbeiterVertragLoadedPerson);
            return true;
        }
    }
}
