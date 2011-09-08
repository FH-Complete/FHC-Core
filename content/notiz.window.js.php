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
		
	}
	else
	{
		//Defaultwerte bei Neuem Datensatz
		titel='';
	}		
	
	document.getElementById('notiz-textbox-titel').value=titel;
}

// ****
// * Speichern der Daten
// ****
function NotizSpeichern()
{
	alert('Noch nicht implementiert');
}