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
// *********** Globale Variablen *****************//
var InteressentSelectId=null; //Interessent der nach dem Refresh markiert werden soll
var InteressentTreeLoadDataOnSelect=true; //Gibt an ob beim naechsten Select des Interessenten Trees die Daten geladen werden sollen
var InteressentKontoSelectBuchung=null; //Buchung die nach dem Refresh markiert werden soll
var InteressentKontoTreeDatasource; //Datasource des KontoTrees

// ********** Observer und Listener ************* //

// ****
// * Observer fuer Interessenten Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var InteressentTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('interessent-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Interessent wieder
// * markiert
// ****
var InteressentTreeListener =
{
  	willRebuild : function(builder)
  	{
  	},
  	didRebuild : function(builder)
  	{
  	  	//timeout nur bei Mozilla notwendig da sonst die rows
  	  	//noch keine values haben. Ab Seamonkey funktionierts auch
  	  	//ohne dem setTimeout
      	window.setTimeout(InteressentTreeSelectInteressent,10);
		// Progressmeter stoppen
		document.getElementById('statusbar-progressmeter').setAttribute('mode','determined');
  	}
};

// ****
// * Observer fuer Konto Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var InteressentKontoTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('interessent-konto-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Buchung wieder
// * markiert
// ****
var InteressentKontoTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(InteressentKontoTreeSelectBuchung,10);
  }
};
// ***************** KEY Events ************************* //

// ****
// * Wird ausgefuehrt wenn eine Taste gedrueckt wird und der Focus
// * im Interessent-tree ist
// * Beim Druecken von F5 wird der Studententree aktualisiert
// ****
function InteressentTreeKeyPress(event)
{
	if(event.keyCode==116) // F5
		InteressentTreeRefresh();
}

// ****************** FUNKTIONEN ************************** //

// ****
// * Asynchroner (Nicht blockierender) Refresh des InteressentenTrees
// ****
function InteressentTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//markierten Interessenten global speichern damit dieser Interessent nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('interessent-tree');
	var col = tree.columns ? tree.columns["interessent-treecol-prestudent_id"] : "interessent-treecol-prestudent_id";
	try
	{
		InteressentSelectId=tree.view.getCellText(tree.currentIndex,col);
	}
	catch(e)
	{
		InteressentSelectId=null;
	}
	InteressentTreeDatasource.Refresh(false); //non blocking
}

// ****
// * neuen Interessenten anlegen
// ****
function InteressentNeu()
{
	try
	{
		var verband_tree=document.getElementById('tree-verband');
		var col = verband_tree.columns ? verband_tree.columns["stg_kz"] : "stg_kz";
		var stg_kz=verband_tree.view.getCellText(verband_tree.currentIndex,col);
	}
	catch(e)
	{}
	
	window.open('<?php echo APP_ROOT; ?>vilesci/personen/import/interessentenimport.php?studiengang_kz='+stg_kz,'Interessent anlegen', 'height=768,width=1024,resizable=yes,status=yes,scrollbars=yes,toolbar=yes,location=yes,menubar=yes');
}

// ****
// * Macht aus einem Interessenten einen Bewerber
// * Voraussetzungen:
// * 	- Datum fuer Anmeldung zum RT muss eingetragen sein
// *	- Hakerl "zum Reihungstest angetreten" muss angekreuzt sein
// * Wenn die Voraussetzungen erfuellt sind wird die Rolle Bewerber hinzugefuegt
// ****
function InteressentzuBewerber()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	//Wenn kein Interessent ausgewaehlt ist, dann abbrechen
	if (tree.currentIndex==-1) return;

	//Alle markierten Personen holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', v);
			paramList += ';'+prestudent_id;
			anzahl = anzahl+1;
		}
	}

	//Rolle Bewerber hinzufuegen

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'addrolle');

	req.add('prestudent_id', paramList);
	req.add('rolle_kurzbz', 'Bewerber');

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
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

		StudentTreeRefresh();
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * macht aus einem Bewerber einen Studenten
// * Voraussetzungen:
// *	- ZGV muss ausgefuellt sein (bei Master beide)
// *	- Kaution muss bezahlt sein
// *	- Rolle Bewerber muss existieren
// * Wenn die Voraussetzungen erfuellt sind, dann wird die Matrikelnr
// * und UID generiert und der Studentendatensatz angelegt.
// ****
function InteressentzuStudent()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	//Wenn kein Interessent ausgewaehlt ist, dann abbrechen
	if (tree.currentIndex==-1) return;

	//Alle markierten Personen holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', v);
			paramList += ';'+prestudent_id;
			anzahl = anzahl+1;
		}
	}
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'BewerberZuStudent');

	req.add('prestudent_id', paramList);

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
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

		StudentTreeRefresh();
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Fuegt eine Rolle zu einem Interessenten hinzu
// ****
function InteressentAddRolle(rolle)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1) return;
	
	//Alle markierten Personen holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', v);
			paramList += ';'+prestudent_id;
			anzahl = anzahl+1;
		}
	}

	if(anzahl>1)
		conf = 'Diese '+anzahl+' Studenten';
	else
		conf = 'Diesen Studenten';
	
	if(confirm(conf+' zum '+rolle+' machen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'addrolle');

		req.add('prestudent_id', paramList);
		req.add('rolle_kurzbz', rolle);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response);

		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response)
			else
				alert(val.dbdml_errormsg)
			StudentTreeRefresh();
		}
		else
		{			
			StudentTreeRefresh();
			SetStatusBarText('Rolle hinzugefuegt');
		}
	}
}