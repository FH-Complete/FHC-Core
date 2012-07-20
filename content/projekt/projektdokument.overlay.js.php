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
 */

require_once('../../config/vilesci.config.inc.php');

?>
// *********** Globale Variablen *****************//

var DokumentSelectID=null; //ID des Dokuments das nach dem Refresh markiert werden soll
// ********** Observer und Listener ************* //

// ****
// * Observer fuer Dokument Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var DokumentTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) { debug('onerror:'+pError); },
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('tree-projektdokument').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird das Dokument wieder
// * markiert
// ****
var DokumentTreeListener =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
  		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
	    window.setTimeout(DokumentTreeSelectDokument,10);
		// Progressmeter stoppen
		//document.getElementById('statusbar-progressmeter').setAttribute('mode','determined');
	}
};

// ****************** FUNKTIONEN ************************** //

// ****
// * Asynchroner (Nicht blockierender) Refresh des Dokument Trees
// ****
function ProjektDokumentTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//markiertes Dokument global speichern damit es nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('tree-projektdokument');
		
	try
	{
		DokumentSelectID = getTreeCellText(tree, "treecol-projektdokument-dms_id", tree.currentIndex);
	}
	catch(e)
	{
		DokumentSelectID=null;
	}
	datasourceTreeDokument.Refresh(false); //non blocking
}

// ****
// * Selectiert das Dokument nachdem der Tree
// * rebuildet wurde.
// ****
function DokumentTreeSelectDokument()
{
	var tree=document.getElementById('tree-projektdokument');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende ID gespeichert
	if(DokumentSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//id der row holen
	   		id = getTreeCellText(tree, "treecol-projektdokument-dms_id", i);
			
			//wenn dies die zu selektierende Zeile
			if(DokumentSelectID==id)
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

function ProjektDokumentDoubleClick()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	var tree = document.getElementById('tree-projektdokument');

	if (tree.currentIndex==-1) return;
	
	try
	{
	    //Ausgewaehlten Eintrag holen
        id = getTreeCellText(tree, "treecol-projektdokument-dms_id", tree.currentIndex);

		if(id!='')
		{
			window.open('<?php echo APP_ROOT; ?>cms/dms.php?id='+id);
        }
	}
	catch(e)
	{
		alert(e);
		return false;
	}
}

function ProjektDokumentNeu()
{
	var tree=document.getElementById('tree-projektmenue');
	
	if(tree.currentIndex==-1)
	{
		alert('Bitte markieren Sie zuerst ein Projekt oder eine Phase');
		return false;
	}
	
	var projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
	var projekt_phase_id=getTreeCellText(tree, "treecol-projektmenue-projekt_phase_id", tree.currentIndex);

	if(projekt_phase_id!='')
		var url = '<?php echo APP_ROOT;?>cms/tinymce_dms.php?kategorie_kurzbz=Projekt&openupload=true&projektphase_id='+projekt_phase_id;
	else if(projekt_kurzbz!='')
		var url = '<?php echo APP_ROOT;?>cms/tinymce_dms.php?kategorie_kurzbz=Projekt&openupload=true&projekt_kurzbz='+projekt_kurzbz;
	else
	{
		alert('Bitte zuerst eine Phase oder ein Projekt markieren');
		return false;
	}
		
	window.open(url);
}

function ProjektDokumentNeueVersion()
{
	var tree=document.getElementById('tree-projektdokument');
	
	if(tree.currentIndex==-1)
	{
		alert('Bitte markieren Sie zuerst einen Eintrag');
		return false;
	}
	
	var dms_id=getTreeCellText(tree, "treecol-projektdokument-dms_id", tree.currentIndex);
	var projekt_phase_id=getTreeCellText(tree, "treecol-projektdokument-projektphase_id", tree.currentIndex);
	var projekt_kurzbz=getTreeCellText(tree, "treecol-projektdokument-projekt_kurzbz", tree.currentIndex);
	

	if(projekt_phase_id!='')
		var url = '<?php echo APP_ROOT;?>cms/tinymce_dms.php?kategorie_kurzbz=Projekt&openupload=true&newVersionID='+dms_id+'&projekt_phase_id='+projekt_phase_id;
	else if(projekt_kurzbz!='')
		var url = '<?php echo APP_ROOT;?>cms/tinymce_dms.php?kategorie_kurzbz=Projekt&openupload=true&&newVersionID='+dms_id+'&projekt_kurzbz='+projekt_kurzbz;
	else
	{
		alert('Bitte zuerst eine Phase oder ein Projekt markieren');
		return false;
	}
		
	window.open(url);
}

function ProjektDokumentZuweisen()
{
	var tree=document.getElementById('tree-projektmenue');
	
	if(tree.currentIndex==-1)
	{
		alert('Bitte markieren Sie zuerst ein Projekt oder eine Phase');
		return false;
	}
	
	var projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
	var projekt_phase_id=getTreeCellText(tree, "treecol-projektmenue-projekt_phase_id", tree.currentIndex);

	if(projekt_phase_id!='')
		var url = '<?php echo APP_ROOT;?>content/projekt/projektdokument.window.xul.php?projektphase_id='+projekt_phase_id;
	else if(projekt_kurzbz!='')
		var url = '<?php echo APP_ROOT;?>content/projekt/projektdokument.window.xul.php?projekt_kurzbz='+projekt_kurzbz;
	else
	{
		alert('Bitte zuerst eine Phase oder ein Projekt markieren');
		return false;
	}
		
	window.open(url,'Zuteilung', 'height=384,width=512,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no');
}
