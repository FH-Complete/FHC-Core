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
var anwesenheitStudentUID='';

// ****
// * Laedt die Trees
// ****
function loadanwesenheit(student_uid,lehrveranstaltung_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	anwesenheitStudentUID=student_uid;

	//anwesenheit laden
	url = "<?php echo APP_ROOT; ?>rdf/anwesenheit.rdf.php?ts="+gettimestamp();

	if(student_uid!='')
		url=url+"&student_uid="+student_uid;
	if(lehrveranstaltung_id!='')
		url=url+"&lehrveranstaltung_id="+lehrveranstaltung_id;

	var treeanwesenheit=document.getElementById('anwesenheit-tree');
	
	//Alte DS entfernen
	var oldDatasources = treeanwesenheit.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		treeanwesenheit.database.RemoveDataSource(oldDatasources.getNext());
	}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var TerminTreeDatasource = rdfService.GetDataSource(url);
	TerminTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	treeanwesenheit.database.AddDataSource(TerminTreeDatasource);
		
}
