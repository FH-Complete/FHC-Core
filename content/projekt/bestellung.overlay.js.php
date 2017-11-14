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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 * 			Karl Burkhart <burkhart@technikum-wien.at>
 */

require_once('../../config/vilesci.config.inc.php');

?>
// *********** Globale Variablen *****************//

var BestellungSelectID=null; //ID des Dokuments das nach dem Refresh markiert werden soll
// ********** Observer und Listener ************* //

// ****
// * Observer fuer Dokument Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var BestellungTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) { debug('onerror:'+pError); },
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('tree-bestellung').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird das Dokument wieder
// * markiert
// ****
var BestellungTreeListener =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
  		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
	    window.setTimeout(BestellungTreeSelectBestellung,10);
		// Progressmeter stoppen
		//document.getElementById('statusbar-progressmeter').setAttribute('mode','determined');
	}
};

// ****************** FUNKTIONEN ************************** //

// ****
// * Asynchroner (Nicht blockierender) Refresh des Bestellung Trees
// ****
function BestellungTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//markierte Bestellung global speichern damit es nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('tree-bestellung');
		
	try
	{
		BestellungSelectID = getTreeCellText(tree, "treecol-bestellung-bestell_nr", tree.currentIndex);
	}
	catch(e)
	{
		BestellungSelectID=null;
	}

	datasourceTreeBestellung.Refresh(false); //non blocking
}

// ****
// * Selectiert die Bestellung nachdem der Tree
// * rebuildet wurde.
// ****
function BestellungTreeSelectBestellung()
{
	var tree=document.getElementById('tree-bestellung');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende ID gespeichert
	if(BestellungSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//id der row holen
	   		id = getTreeCellText(tree, "treecol-bestellung-bestell_nr", i);
			
			//wenn dies die zu selektierende Zeile
			if(BestellungSelectID==id)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	}
}
