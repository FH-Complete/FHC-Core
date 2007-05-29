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

require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');

$conn = pg_pconnect(CONN_STRING);

$user = get_uid();
loadVariables($conn, $user);
?>
var studiengang_kz=''; // enthaelt die Studiengangskennzahl
var person_ids=''; // enthaelt eine durch ';' getrennte Liste an Personen_ids

// ****
// * Ermittelt die markierten Personen und den aktuellen Studiengang
// ****
function InteressentKontoNeuInit()
{
	var tree = window.opener.document.getElementById('interessent-tree')
    
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;
	
	//alle markierten personen holen
	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
			for (var v = start.value; v <= end.value; v++)
			{
			col = tree.columns ? tree.columns["interessent-treecol-person_id"] : "interessent-treecol-person_id";
			id = tree.view.getCellText(v,col);
			paramList += ';'+id;
			anzahl +=1;
			}
	}

	//Studiengang holen
	var tree=window.opener.document.getElementById('tree-verband');
	
	col = tree.columns ? tree.columns["stg_kz"] : "stg_kz";
	studiengang_kz=tree.view.getCellText(tree.currentIndex,col);
	
	person_ids = paramList;
	
	if(anzahl>1)
		document.getElementById('interessent-konto-neu-label').value='Anzahl Personen: '+anzahl;
}

// ****
// * Speichern der Buchung
// * Hierzu wird eine Funktion vom Aufrufenden Fenster gestartet weil
// * es dann nicht zu Problemen mit den Zugriffen auf die anderen Fkt
// * kommt.
// ****
function InteressentKontoNeuSpeichern()
{
	if(window.opener.InteressentKontoNeuSpeichern(document, person_ids, studiengang_kz))
		window.close();
}