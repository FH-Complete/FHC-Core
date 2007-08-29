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
include('../vilesci/config.inc.php');
?>

var currentAuswahl=new auswahlValues();
var LvTreeDatasource;
var LektorTreeDatasource;
var LektorTreeOpenStudiengang;
var StudentTreeDatasource;
var InteressentTreeDatasource;

// ****
// * initialisiert den Lektor Tree
// ****
function initLektorTree()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	try
	{
		url = '<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php?user=true&lektor=true&'+gettimestamp();
		var LektorTree=document.getElementById('tree-lektor');

		//Alte DS entfernen
		var oldDatasources = LektorTree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			LektorTree.database.RemoveDataSource(oldDatasources.getNext());
		}

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		LektorTreeDatasource = rdfService.GetDataSource(url);
		LektorTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		LektorTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		LektorTree.database.AddDataSource(LektorTreeDatasource);
		//LektorTreeDatasource.addXMLSinkObserver(LektorTreeSinkObserver);
		LektorTree.builder.addListener(LektorTreeListener);
	}
	catch(e)
	{
		debug(e);
	}
}

// ****
// * Nach dem Rebuild wird die Lehreinheit wieder
// * markiert
// ****
var LektorTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(LektorTreeSelectMitarbeiter,10);
  }
};

function LektorTreeSelectMitarbeiter()
{
	var tree=document.getElementById('tree-lektor');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	if(LektorTreeOpenStudiengang!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//Lehreinheit_id der row holen
			col = tree.columns ? tree.columns["studiengang_kz"] : "studiengang_kz";
			var studiengang_kz=tree.view.getCellText(i,col);
			if(studiengang_kz == LektorTreeOpenStudiengang)
			{
				tree.view.toggleOpenState(i);
				break;
			}
	   	}
	}
}

// ****
// * Refresht den Lektor Tree
// ****
function RefreshLektorTree()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	LektorTreeDatasource.Refresh(true);
	document.getElementById('tree-lektor').builder.rebuild();
}

// ****
// * Loescht die Lkt Funktion eines Lektors
// ****
function LektorFunktionDel()
{
	tree = document.getElementById('tree-lektor');

	//Nachsehen ob Mitarbeiter markiert wurde
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
	{
		alert('Bitte zuerst einen Mitarbeiter markieren');
		return false;
	}

	try
	{
		//UID holen
		var col = tree.columns ? tree.columns["uid"] : "uid";
		var uid=tree.view.getCellText(idx,col);
		//Stg_kz holen
		var stg_idx = tree.view.getParentIndex(idx);
		var col = tree.columns ? tree.columns["studiengang_kz"] : "studiengang_kz";
		var studiengang_kz=tree.view.getCellText(stg_idx,col);
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Request absetzen
	var req = new phpRequest('tempusDBDML.php','','');

	req.add('type', 'delFunktionFromMitarbeiter');
	req.add('studiengang_kz', studiengang_kz);
	req.add('uid', uid);

	var response = req.executePOST();
	//Returnwert auswerten
	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		alert(val.dbdml_errormsg)
	}
	else
	{
		//Refresh des Trees
		LektorTreeOpenStudiengang = studiengang_kz;
		RefreshLektorTree();
	}
}

function auswahlValues()
{
	this.stg_kz=null;
	this.sem=null;
	this.ver=null;
	this.grp=null;
	this.gruppe=null;
	this.lektor_uid=null;
}

// ---------------------------------------------------------
// -------------- onVerbandSelect --------------------------

function onVerbandSelect(event)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var tree=document.getElementById('tree-verband');

	//Wenn nichts markiert wurde -> beenden
	if(tree.currentIndex==-1)
		return;

	var row = { };
    var col = { };
    var child = { };

    tree.treeBoxObject.getCellAt(event.pageX, event.pageY, row, col, child)

    //Wenn es keine Row ist sondern ein Header oder Scrollbar dann abbrechen
    if (!col.value)
       	return false;

    //Wenn eine andere row markiert ist als angeklickt wurde -> beenden.
	//Dies kommt vor wenn ein Subtree geoeffnet wird
	if(row.value!=tree.currentIndex)
		return;

	//Export aktivieren
	document.getElementById('student-toolbar-export').disabled=false;
	
    // Progressmeter starten. Ab jetzt keine 'return's mehr.
    document.getElementById('statusbar-progressmeter').setAttribute('mode','undetermined');
    //globalProgressmeter.StartPM();

	var col;
	col = tree.columns ? tree.columns["stg_kz"] : "stg_kz";
	var stg_kz=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["sem"] : "sem";
	var sem=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["ver"] : "ver";
	var ver=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["grp"] : "grp";
	var grp=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["gruppe"] : "gruppe";
	var gruppe=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["typ"] : "typ";
	var typ=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["stsem"] : "stsem";
	var stsem=tree.view.getCellText(tree.currentIndex,col);

	currentAuswahl.stg_kz=stg_kz;
	currentAuswahl.sem=sem;
	currentAuswahl.ver=ver;
	currentAuswahl.grp=grp;
	currentAuswahl.gruppe=gruppe;

	if(typ=='')
	{
		//Bei Ansicht von Ab-/Unterbrecher den Button "->Student" anzeigen
		if(sem=='0')
			document.getElementById('student-toolbar-student').hidden=false;
		else
			document.getElementById('student-toolbar-student').hidden=true;

		//Wenn der Interessenten Tab markiert ist, dann den Studenten Tab markieren
		if(document.getElementById('main-content-tabs').selectedItem==document.getElementById('tab-interessenten'))
			document.getElementById('main-content-tabs').selectedItem=document.getElementById('tab-studenten');

		// -------------- Studenten --------------------------
		try
		{
			stsem = getStudiensemester();
			url = "<?php echo APP_ROOT; ?>rdf/student.rdf.php?studiengang_kz="+stg_kz+"&semester="+sem+"&verband="+ver+"&gruppe="+grp+"&gruppe_kurzbz="+gruppe+"&studiensemester_kurzbz="+stsem+"&typ=student&"+gettimestamp();
			var treeStudent=document.getElementById('student-tree');

			//Alte DS entfernen
			var oldDatasources = treeStudent.database.GetDataSources();
			while(oldDatasources.hasMoreElements())
			{
				treeStudent.database.RemoveDataSource(oldDatasources.getNext());
			}

			try
			{
				StudentTreeDatasource.removeXMLSinkObserver(StudentTreeSinkObserver);
				treeStudent.builder.removeListener(StudentTreeListener);
			}
			catch(e)
			{}
			var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
			StudentTreeDatasource = rdfService.GetDataSource(url);
			StudentTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
			StudentTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
			treeStudent.database.AddDataSource(StudentTreeDatasource);
			StudentTreeDatasource.addXMLSinkObserver(StudentTreeSinkObserver);
			treeStudent.builder.addListener(StudentTreeListener);

			//Detailfelder Deaktivieren
			StudentDetailReset();
			StudentDetailDisableFields(true);
			StudentPrestudentDisableFields(true);
			StudentKontoDisableFields(true);
			StudentAkteDisableFields(true);
			StudentIODisableFields(true);
			StudentNoteDisableFields(true);
			document.getElementById('student-kontakt').setAttribute('src','');
			document.getElementById('student-betriebsmittel').setAttribute('src','');
			StudentAbschlusspruefungDisableFields(true);
		}
		catch(e)
		{
			debug(e);
		}

		// -------------- Lehrveranstaltung --------------------------
		try
		{
			url = '<?php echo APP_ROOT; ?>rdf/lehrveranstaltung_einheiten.rdf.php?stg_kz='+stg_kz+'&sem='+sem+'&ver='+ver+'&grp='+grp+'&gruppe='+gruppe+'&'+gettimestamp();
			var treeLV=document.getElementById('lehrveranstaltung-tree');

			//Alte DS entfernen
			var oldDatasources = treeLV.database.GetDataSources();
			while(oldDatasources.hasMoreElements())
			{
				treeLV.database.RemoveDataSource(oldDatasources.getNext());
			}

			try
			{
				LvTreeDatasource.removeXMLSinkObserver(LvTreeSinkObserver);
				treeLV.builder.removeListener(LvTreeListener);
			}
			catch(e)
			{}
			var rdfService1 = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);

			LvTreeDatasource = rdfService1.GetDataSource(url);
			LvTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
			LvTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
			treeLV.database.AddDataSource(LvTreeDatasource);
			LvTreeDatasource.addXMLSinkObserver(LvTreeSinkObserver);
			treeLV.builder.addListener(LvTreeListener);
			document.getElementById('lehrveranstaltung-toolbar-lehrauftrag').hidden=true;
		}
		catch(e)
		{
			debug(e);
		}
	}

	// Interessenten / Bewerber
	if(typ!='')
	{
		// Interessenten Tab markieren
		//document.getElementById('main-content-tabs').selectedItem=document.getElementById('tab-interessenten');

		// -------------- Interessenten / Bewerber --------------------------
		try
		{
			if(stsem=='' && typ=='')
				stsem='aktuelles';
			url = "<?php echo APP_ROOT; ?>rdf/student.rdf.php?"+"studiengang_kz="+stg_kz+"&semester="+sem+"&typ="+typ+"&studiensemester_kurzbz="+stsem+"&"+gettimestamp();
			var treeInt=document.getElementById('student-tree');

			//Alte DS entfernen
			var oldDatasources = treeInt.database.GetDataSources();
			while(oldDatasources.hasMoreElements())
			{
				treeInt.database.RemoveDataSource(oldDatasources.getNext());
			}

			try
			{
				StudentTreeDatasource.removeXMLSinkObserver(StudentTreeSinkObserver);
				treeInt.builder.removeListener(StudentTreeListener);
			}
			catch(e)
			{}
			
			var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
			StudentTreeDatasource = rdfService.GetDataSource(url);
			StudentTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
			StudentTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
			treeInt.database.AddDataSource(StudentTreeDatasource);
			StudentTreeDatasource.addXMLSinkObserver(StudentTreeSinkObserver);
			treeInt.builder.addListener(StudentTreeListener);
			
			//Detailfelder Deaktivieren
			StudentDetailReset();
			StudentDetailDisableFields(true);
			StudentPrestudentDisableFields(true);
			StudentKontoDisableFields(true);
			StudentAkteDisableFields(true);
			StudentIODisableFields(true);
			StudentNoteDisableFields(true);
			document.getElementById('student-kontakt').setAttribute('src','');
			document.getElementById('student-betriebsmittel').setAttribute('src','');
			StudentAbschlusspruefungDisableFields(true);
		}
		catch(e)
		{
			debug(e);
		}
	}
}

function onFachbereichSelect(event)
{
	var tree=document.getElementById('tree-fachbereich');
	//Wenn nichts markiert wurde -> beenden
	if(tree.currentIndex==-1)
		return;

	var row = { };
    var col = { };
    var child = { };

    tree.treeBoxObject.getCellAt(event.pageX, event.pageY, row, col, child)

    //Wenn es keine Row ist sondern ein Header oder Scrollbar dann abbrechen
    if (!col.value)
       	return false;

    //Wenn eine andere row markiert ist als angeklickt wurde -> beenden.
	//Dies kommt vor wenn ein Subtree geoeffnet wird
	if(row.value!=tree.currentIndex)
		return;

	col = tree.columns ? tree.columns["kurzbz"] : "kurzbz";
	var kurzbz=tree.view.getCellText(tree.currentIndex,col);

	// Lehrveranstaltung
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	try
	{
		url = '<?php echo APP_ROOT; ?>rdf/lehrveranstaltung_einheiten.rdf.php?fachbereich_kurzbz='+kurzbz+'&'+gettimestamp();
		var treeLV=document.getElementById('lehrveranstaltung-tree');

		//Alte DS entfernen
		var oldDatasources = treeLV.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			treeLV.database.RemoveDataSource(oldDatasources.getNext());
		}

		try
		{
			LvTreeDatasource.removeXMLSinkObserver(LvTreeSinkObserver);
			treeLV.builder.removeListener(LvTreeListener);
		}
		catch(e)
		{}
			
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		LvTreeDatasource = rdfService.GetDataSource(url);
		LvTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		LvTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		treeLV.database.AddDataSource(LvTreeDatasource);
		LvTreeDatasource.addXMLSinkObserver(LvTreeSinkObserver);
		treeLV.builder.addListener(LvTreeListener);
		document.getElementById('lehrveranstaltung-toolbar-lehrauftrag').hidden=true;
	}
	catch(e)
	{
		debug(e);
	}
}

function onOrtSelect()
{
	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var treeOrt=document.getElementById('tree-ort');
	var ort=treeOrt.view.getCellText(treeOrt.currentIndex,"ort_kurzbz");
	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));

	var attributes="?type=ort&ort="+ort+"&datum="+datum;
	var url = "<?php echo APP_ROOT; ?>content/timetable-week.xul.php";
	url+=attributes;
	if (url)
		contentFrame.setAttribute('src', url);
}

function onLektorSelect(event)
{
	var tree=document.getElementById('tree-lektor');
	//Wenn nichts markiert wurde -> beenden
	if(tree.currentIndex==-1)
		return;

	var row = { };
    var col = { };
    var child = { };

    tree.treeBoxObject.getCellAt(event.pageX, event.pageY, row, col, child)

    //Wenn es keine Row ist sondern ein Header oder Scrollbar dann abbrechen
    if (!col.value)
       	return false;

    //Wenn eine andere row markiert ist als angeklickt wurde -> beenden.
	//Dies kommt vor wenn ein Subtree geoeffnet wird
	if(row.value!=tree.currentIndex)
		return;

	col = tree.columns ? tree.columns["uid"] : "uid";
	var uid=tree.view.getCellText(tree.currentIndex,col);

	var stg_idx = tree.view.getParentIndex(tree.currentIndex);
	var col = tree.columns ? tree.columns["studiengang_kz"] : "studiengang_kz";
	var stg_kz=tree.view.getCellText(stg_idx,col);

	document.getElementById('LehrveranstaltungEditor').setAttribute('stg_kz',stg_kz);
	document.getElementById('LehrveranstaltungEditor').setAttribute('uid',uid);

	// Lehrveranstaltung des Lektors laden
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	try
	{
		url = '<?php echo APP_ROOT; ?>rdf/lehrveranstaltung_einheiten.rdf.php?stg_kz='+stg_kz+'&uid='+uid+'&'+gettimestamp();
		var treeLV=document.getElementById('lehrveranstaltung-tree');

		//Alte DS entfernen
		var oldDatasources = treeLV.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			treeLV.database.RemoveDataSource(oldDatasources.getNext());
		}
		
		try
		{
			LvTreeDatasource.removeXMLSinkObserver(LvTreeSinkObserver);
			treeLV.builder.removeListener(LvTreeListener);
		}
		catch(e)
		{}
		
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		LvTreeDatasource = rdfService.GetDataSource(url);
		LvTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		LvTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		treeLV.database.AddDataSource(LvTreeDatasource);
		LvTreeDatasource.addXMLSinkObserver(LvTreeSinkObserver);
		treeLV.builder.addListener(LvTreeListener);
		document.getElementById('lehrveranstaltung-toolbar-lehrauftrag').hidden=false;
	}
	catch(e)
	{
		debug(e);
	}
}

function loadURL(event)
{
        var contentFrame = document.getElementById('contentFrame');
        var url = event.target.getAttribute('value');

        if (url) contentFrame.setAttribute('src', url);
};

function parseRDFString(str, url)
{

	try {
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	} catch(e) {
		alert(e);
		return;
	}

  var memoryDS = Components.classes["@mozilla.org/rdf/datasource;1?name=in-memory-datasource"].createInstance(Components.interfaces.nsIRDFDataSource);

  var ios=Components.classes["@mozilla.org/network/io-service;1"].getService(Components.interfaces.nsIIOService);
  baseUri=ios.newURI(url,null,null);

  var parser=Components.classes["@mozilla.org/rdf/xml-parser;1"].createInstance(Components.interfaces.nsIRDFXMLParser);
  parser.parseString(memoryDS,baseUri,str);

  return memoryDS;
}

// ****
// * Liefert eine HTML Liste mit den Koordinatorstunden
// * fuer einen Fachbereich
// ****
function StatistikPrintKoordinatorstunden()
{
	tree = document.getElementById('tree-fachbereich');

	if(tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Fachbereich auswaehlen');
		return;
	}

	//Fachbereich holen
	var col;
	col = tree.columns ? tree.columns["kurzbz"] : "kurzbz";
	var fachbereich_kurzbz=tree.view.getCellText(tree.currentIndex,col);

	window.open('<?php echo APP_ROOT ?>content/statistik/koordinatorstunden.php?fachbereich_kurzbz='+fachbereich_kurzbz,'Koordinatorstunden');
}

// ****
// * Erstellt das PDF File fuer die Lehrauftraege
// * Studiengang muss ausgewaehlt sein
// ****
function StatistikPrintLehrauftraege()
{
	tree = document.getElementById('tree-verband');

	if(tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Studiengang auswaehlen');
		return;
	}

	//Studiengang holen
	var col;
	col = tree.columns ? tree.columns["stg_kz"] : "stg_kz";
	var studiengang_kz=tree.view.getCellText(tree.currentIndex,col);

	var ss = document.getElementById('statusbarpanel-semester').label;
	window.open('<?php echo APP_ROOT ?>content/pdfExport.php?xml=lehrauftrag.xml.php&xsl=Lehrauftrag&stg_kz='+studiengang_kz+'&ss='+ss,'Lehrauftrag');
}

// ****
// * Liefert eine HTML Liste mit Uebersicht der Lehrauftraege.
// * Studiengang und optional Semester muss gewaehlt sein.
// ****
function StatistikPrintLVPlanung()
{
	tree = document.getElementById('tree-verband');

	if(tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Studiengang auswaehlen');
		return;
	}

	//Studiengang und Semester holen
	var col;
	col = tree.columns ? tree.columns["stg_kz"] : "stg_kz";
	var studiengang_kz=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["sem"] : "sem";
	var semester=tree.view.getCellText(tree.currentIndex,col);

	window.open('<?php echo APP_ROOT ?>content/statistik/lvplanung.php?studiengang_kz='+studiengang_kz+'&semester='+semester,'LV-Planung');
}

// ****
// * Erstellt ein Excel File mit der Uebersicht
// * ueber alle Lektoren und deren Kosten eines Studienganges
// ****
function StatistikPrintLehrauftragsliste()
{
	tree = document.getElementById('tree-verband');

	if(tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Studiengang auswaehlen');
		return;
	}

	//Studiengang und Semester holen
	var col;
	col = tree.columns ? tree.columns["stg_kz"] : "stg_kz";
	var studiengang_kz=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["sem"] : "sem";
	var semester=tree.view.getCellText(tree.currentIndex,col);

	window.open('<?php echo APP_ROOT ?>content/statistik/lehrauftragsliste_gst.xls.php?studiengang_kz='+studiengang_kz+'&semester='+semester,'Lehrauftragsliste');
}

// ****
// * Erstellt ein Excelfile mit den Daten der Abschlusspruefung
// ****
function StatistikPrintProjektarbeit()
{
	tree = document.getElementById('tree-verband');

	if(tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Studiengang auswaehlen');
		return;
	}

	//Studiengang und Semester holen
	var col;
	col = tree.columns ? tree.columns["stg_kz"] : "stg_kz";
	var studiengang_kz=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["sem"] : "sem";
	var semester=tree.view.getCellText(tree.currentIndex,col);

	if(studiengang_kz!='')
		window.open('<?php echo APP_ROOT ?>content/statistik/projektarbeitexport.xls.php?studiengang_kz='+studiengang_kz+'&semester='+semester+'&studiensemester_kurzbz='+getStudiensemester(),'Excel');
	else
		alert('Bitte zuerst Studiengang / Semester auswaehlen');
}

// ****
// * Liefert eine HTML Liste mit Uebersicht der Lehrauftraege.
// * Studiengang und optional Semester muss gewaehlt sein.
// ****
function StatistikPrintAbschlusspruefung()
{
	tree = document.getElementById('tree-verband');

	if(tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Studiengang auswaehlen');
		return;
	}

	//Studiengang und Semester holen
	var col;
	col = tree.columns ? tree.columns["stg_kz"] : "stg_kz";
	var studiengang_kz=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["sem"] : "sem";
	var semester=tree.view.getCellText(tree.currentIndex,col);

	if(studiengang_kz!='')
		window.open('<?php echo APP_ROOT ?>content/statistik/abschlusspruefungexport.xls.php?studiengang_kz='+studiengang_kz+'&semester='+semester+'&studiensemester_kurzbz='+getStudiensemester(),'Excel');
	else
		alert('Bitte zuerst Studiengang / Semester auswaehlen');
}

// ****
// * Liefert eine HTML Liste mit Uebersicht ueber die eingetragenen Noten
// * Studiengang und optional Semester muss gewaehlt sein.
// ****
function StatistikPrintNotenspiegel()
{
	tree = document.getElementById('tree-verband');

	if(tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Studiengang auswaehlen');
		return;
	}

	//Studiengang und Semester holen
	var col;
	col = tree.columns ? tree.columns["stg_kz"] : "stg_kz";
	var studiengang_kz=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["sem"] : "sem";
	var semester=tree.view.getCellText(tree.currentIndex,col);

	window.open('<?php echo APP_ROOT ?>content/statistik/notenspiegel.php?studiengang_kz='+studiengang_kz+'&semester='+semester,'Notenspiegel');
}

// ****
// * Zeigt HTML Seite zur Bearbeitung der Reihungstests an
// ****
function ExtrasShowReihungstest()
{
	window.open('<?php echo APP_ROOT ?>vilesci/stammdaten/reihungstestverwaltung.php','Reihungstest','');
}

// ****
// * Zeigt HTML Seite zur bearbeitung der Firmen an
// ****
function ExtrasShowFirmenverwaltung()
{
	window.open('<?php echo APP_ROOT ?>vilesci/stammdaten/firma_frameset.html','Firma','');
}

// ****
// * Oeffnet den About Dialog
// ****
function OpenAboutDialog()
{
	window.open('<?php echo APP_ROOT ?>content/about.xul.php','About','height=520,width=500,left=350,top=350,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ****
// * Wenn der Tab Mitarbeiter aktiviert ist und der Prestudent-, Student- oder Lehrveranstaltungstab
// * markiert wird, dann wird im Menue auf den Verband Tag gewechselt
// ****
function ChangeTabsToVerband()
{
	if(document.getElementById('menu-content-tabs').selectedItem==document.getElementById('tab-menu-mitarbeiter'))
		document.getElementById('menu-content-tabs').selectedItem=document.getElementById('tab-verband');
}

// ****
// * Wenn der Tab Fachbereich oder Lektor gewaehlt wird, dann wird auf den Tab Lehrveranstaltung gewechselt
// ****
function ChangeTabsToLehrveranstaltung()
{
	document.getElementById('main-content-tabs').selectedItem=document.getElementById('tab-lfvt');
}

// ****
// * Wenn der Tab Mitarbeiter Markiert ist, und auf den Tab Verband geklickt wird, 
// * dann wird der StudententTab markiert
// ****
function ChangeTabVerband()
{
	if(document.getElementById('main-content-tabs').selectedItem==document.getElementById('tab-mitarbeiter'))
		document.getElementById('main-content-tabs').selectedItem=document.getElementById('tab-studenten');
}