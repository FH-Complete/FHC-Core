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
require_once('../../include/functions.inc.php');

$user = get_uid();
loadVariables($user);
?>
var studiengang_kz=''; // enthaelt die Studiengangskennzahl
var person_ids=''; // enthaelt eine durch ';' getrennte Liste an Personen_ids

// ****
// * Ermittelt die markierten Personen und den aktuellen Studiengang
// ****
function StudentKontoNeuInit()
{
	var tree = window.opener.document.getElementById('student-tree')

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
			col = tree.columns ? tree.columns["student-treecol-person_id"] : "student-treecol-person_id";
			person_id = tree.view.getCellText(v,col);
			paramList += ';'+person_id;
			anzahl = anzahl +1;
			}
	}

	//Wenn nur ein Student markiert wurde, den Studiengang des Studenten nehmen
	//Wenn mehrere Studenten markiert wurden, dann den Studiengang nehmen der im Verbandstree markiert ist
	if(anzahl>1)
	{
		tree_vb=window.opener.document.getElementById('tree-verband');
	
		//Wenn nichts markiert wurde -> beenden
		if(tree_vb.currentIndex==-1)
		{
			alert('Es muss ein Studiengang markiert sein!');
			window.close();
			return;
		}
	
		col = tree_vb.columns ? tree_vb.columns["stg_kz"] : "stg_kz";
		//var stg_kz=tree_vb.view.getCellText(tree_vb.currentIndex,col);
		studiengang_kz=tree_vb.view.getCellText(tree_vb.currentIndex,col);
		//debug('kz:'+studiengang_kz);
	}
	else
	{
		studiengang_kz = window.opener.document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	}
	person_ids = paramList;

	if(anzahl>1)
		document.getElementById('student-konto-neu-label').value='Anzahl Studenten: '+anzahl;
	
	document.getElementById('student-konto-neu-menulist-studiensemester').value='<?php echo $semester_aktuell;?>';
	document.getElementById('student-konto-neu-textbox-buchungsdatum').value='<?php echo date("d.m.Y"); ?>';
}

// ****
// * Speichern der Buchung
// * Hierzu wird eine Funktion vom Aufrufenden Fenster gestartet weil
// * es dann nicht zu Problemen mit den Zugriffen auf die anderen Fkt
// * kommt.
// ****
function StudentKontoNeuSpeichern()
{
	if(window.opener.StudentKontoNeuSpeichern(document, person_ids, studiengang_kz))
		window.close();
}

// ****
// * Setzt den Standardbetrag fuer den ausgewaehlten Buchungstyp
// * oder -0.0 wenn kein Standardbetrag vorhanden ist
// ****
function StudentKontoNeuDefaultBetrag()
{
	var typmenulist = document.getElementById('student-konto-neu-menulist-buchungstyp');
	var betrag = typmenulist.selectedItem.getAttribute('standardbetrag');
	var text = typmenulist.selectedItem.getAttribute('standardtext');
	var credit_points = typmenulist.selectedItem.getAttribute('credit_points');
	if(betrag=='')
		betrag='-0.00';
		
	if(document.getElementById('student-konto-neu-textbox-betrag').value=='' ||
	   document.getElementById('student-konto-neu-textbox-betrag').value=='-0.00')
		document.getElementById('student-konto-neu-textbox-betrag').value=betrag;
		
	if(document.getElementById('student-konto-neu-textbox-buchungstext').value=='')
		document.getElementById('student-konto-neu-textbox-buchungstext').value=text;
		
	if(document.getElementById('student-konto-neu-textbox-credit_points').value=='0.00')
		document.getElementById('student-konto-neu-textbox-credit_points').value=credit_points;
}