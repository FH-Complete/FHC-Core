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
var studiensemester_old;
var ausbildungssemester_old;

// ****
// * Laedt die Rolle
// ****
function StudentRolleInit(prestudent_id, status_kurzbz, studiensemester_kurzbz, ausbildungssemester)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	if(status_kurzbz!='')
	{
		document.getElementById('student-rolle-grid-row-textbox').hidden=false;
		document.getElementById('student-rolle-grid-row-menulist').hidden=true;
		
		studiensemester_old=studiensemester_kurzbz;
		ausbildungssemester_old=ausbildungssemester;
		
		//Daten holen
		var url = '<?php echo APP_ROOT ?>rdf/prestudentrolle.rdf.php?prestudent_id='+prestudent_id+'&status_kurzbz='+status_kurzbz+'&studiensemester_kurzbz='+studiensemester_kurzbz+'&ausbildungssemester='+ausbildungssemester+'&'+gettimestamp();
			
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	                   getService(Components.interfaces.nsIRDFService);
	    
	    var dsource = rdfService.GetDataSourceBlocking(url);
	    
		var subject = rdfService.GetResource("http://www.technikum-wien.at/prestudentrolle/" + prestudent_id+"/"+status_kurzbz+"/"+studiensemester_kurzbz+"/"+ausbildungssemester);
	
		var predicateNS = "http://www.technikum-wien.at/prestudentrolle/rdf";
	
		//RDF parsen	
		datum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#datum" ));
		bestaetigt_datum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bestaetigt_am" ));
		orgform_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#orgform_kurzbz" ));
		studienplan_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studienplan_id" ));
		anmerkung= getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
		neu = false;
	}
	else
	{
		studiensemester_old='';
		ausbildungssemester_old='';
		
		document.getElementById('student-rolle-grid-row-textbox').hidden=true;
		document.getElementById('student-rolle-grid-row-menulist').hidden=false;

		//Defaultwerte bei Neuem Datensatz
		status_kurzbz = 'Interessent';
		studiensemester_kurzbz=window.opener.getStudiensemester();
		ausbildungssemester='1';
		datum = '<?php echo date('d.m.Y') ?>';
		bestaetigt_datum = '<?php echo date('d.m.Y') ?>';
		orgform_kurzbz='';
		studienplan_id='';
		anmerkung='';
	}		
	
	document.getElementById('student-rolle-textbox-prestudent_id').value=prestudent_id;
	document.getElementById('student-rolle-textbox-status_kurzbz').value=status_kurzbz;
	document.getElementById('student-rolle-menulist-studiensemester').value=studiensemester_kurzbz;
	document.getElementById('student-rolle-menulist-ausbildungssemester').value=ausbildungssemester;
	document.getElementById('student-rolle-datum-datum').value=datum;
	document.getElementById('student-rolle-datum-bestaetigt_datum').value=bestaetigt_datum;
	document.getElementById('student-rolle-menulist-orgform_kurzbz').value=orgform_kurzbz;
	MenulistSelectItemOnValue('student-rolle-menulist-studienplan', studienplan_id);
	document.getElementById('student-rolle-textbox-anmerkung').value=anmerkung;
}

// ****
// * Speichern der Rolle
// * Hierzu wird eine Funktion vom Aufrufenden Fenster gestartet weil
// * es dann nicht zu Problemen mit den Zugriffen auf die anderen Fkt
// * kommt.
// ****
function StudentRolleSpeichern()
{
	if(window.opener.StudentRolleSpeichern(document, studiensemester_old, ausbildungssemester_old))
		window.close();
}
