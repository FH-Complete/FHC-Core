<?php
/* Copyright (C) 2015 fhcomplete.org
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
// ********** FUNKTIONEN ********** //
var TermineLehreinheitID='';
var TermineLehrveranstaltungID='';
var TermineMitarbeiterUID='';
var TermineStudentUID='';
var TermineStundenplanTable='stundenplan';

// ****
// * Laedt die Trees
// ****
function loadTermine(lehreinheit_id, lehrveranstaltung_id, mitarbeiter_uid, student_uid)
{
	TermineLehreinheitID=lehreinheit_id;
	TermineLehrveranstaltungID=lehrveranstaltung_id;
	TermineMitarbeiterUID=mitarbeiter_uid;
	TermineStudentUID=student_uid;

	// Stundenplan Tabelle aus Variablen holen
	TermineStundenplanTable = getvariable('termin_export_db_stpl_table');
	TermineSetSTPLTable(TermineStundenplanTable);
	TermineLoadTree();
}

function TermineLoadTree()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	//Termine laden
	url = "<?php echo APP_ROOT; ?>rdf/termine.rdf.php?ts="+gettimestamp();

	if(TermineLehreinheitID!='')
		url=url+"&lehreinheit_id="+TermineLehreinheitID;
	if(TermineLehrveranstaltungID!='')
		url=url+"&lehrveranstaltung_id="+TermineLehrveranstaltungID;
	if(TermineMitarbeiterUID!='')
		url=url+"&mitarbeiter_uid="+TermineMitarbeiterUID;
	if(TermineStudentUID!='')
		url=url+"&student_uid="+TermineStudentUID;

	url=url+"&db_stpl_table="+TermineStundenplanTable;

	var treeTermine=document.getElementById('termine-tree');

	//Alte DS entfernen
	var oldDatasources = treeTermine.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		treeTermine.database.RemoveDataSource(oldDatasources.getNext());
	}

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var TerminTreeDatasource = rdfService.GetDataSource(url);
	TerminTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	treeTermine.database.AddDataSource(TerminTreeDatasource);

}

function TermineChangeSTPLTable()
{
	var checkState = document.getElementById('termine-button-stpltable').checked;
	if(checkState==true)
	{
		TermineSetSTPLTable('stundenplandev');
		setVariable('termin_export_db_stpl_table','stundenplandev');
	}
	else
	{
		TermineSetSTPLTable('stundenplan');
		setVariable('termin_export_db_stpl_table','stundenplan');
	}
	TermineLoadTree();
}

/**
 * Aendert die Stundenplantabelle fuer den TermineExport
 */
function TermineSetSTPLTable(db_stpl_table)
{
	var button = document.getElementById('termine-button-stpltable');
	if(db_stpl_table=='stundenplandev')
	{
		button.label='StundenplanDEV';
		button.checked=true;
		TermineStundenplanTable='stundenplandev';
	}
	else
	{
		button.label='Stundenplan';
		button.checked=false;
		TermineStundenplanTable='stundenplan';
	}
}
/**
 * Exportiert die Termine
 */
function TermineExport()
{
	var url = 'statistik/termine.xls.php?lehreinheit_id='+TermineLehreinheitID+'&lehrveranstaltung_id='+TermineLehrveranstaltungID+'&mitarbeiter_uid='+TermineMitarbeiterUID+'&student_uid='+TermineStudentUID+'&db_stpl_table='+TermineStundenplanTable;
	window.open(url);
}
