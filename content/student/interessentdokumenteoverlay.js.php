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
var InteressentDokumentTreeNichtabgegebenDatasource=null; //Datasource fuer Dokumenten tree
var InteressentDokumentTreeNichtabgegebenSelectID=null; //dokument_kurzbz des zu markierenden Datensatzes
var InteressentDokumentTreeAbgegebenDatasource=null; //Datasource fuer Dokumenten tree
var InteressentDokumentTreeAbgegebenSelectID=null; //dokument_kurzbz des zu markierenden Datensatzes
var InteressentDokumentTreeAbgegebenDoubleRefresh=false; // Wenn true, dann wird der rechte Dokumententree das naechste mal 2 mal hintereinander Refresht
var InteressentDokumentTreeNichtabgegebenDoubleRefresh=false; // Wenn true, dann wird der linke Dokumententree das naechste mal 2 mal hintereinander Refresht
// ********** Observer und Listener ************* //


// ****
// * Observer fuer linken Dokumententree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var InteressentDokumentTreeNichtabgegebenSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('interessent-dokumente-tree-nichtabgegeben').builder.rebuild();
	}
};


// ****
// * Nach dem Rebuild des Linken Dokumenten Trees
// * Wenn die Variable InteressentDokumentTreeNichtabgegebenDoubleRefresh auf 'true' gesetzt wird, dann
// * wird der Tree ein zweites mal Refresht. Dies wird benoetigt falls zuvor im Tree eine Datasource geladen
// * wurde die keine Daten enthaelt. Die Daten werden erst angezeigt wenn der Tree ein zweites mal refresht wird.
// ****
var InteressentDokumentTreeNichtabgegebenListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
      if(InteressentDokumentTreeNichtabgegebenDoubleRefresh==true)
      {
      	window.setTimeout('InteressentDokumentTreeNichtabgegebenDatasourceRefresh()',10);
      }
      InteressentDokumentTreeNichtabgegebenDoubleRefresh=false;
  }
};

// ****
// * Observer fuer rechten Dokumententree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var InteressentDokumentTreeAbgegebenSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('interessent-dokumente-tree-abgegeben').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild des rechten Dokumenten Trees
// * Wenn die Variable InteressentDokumentTreeAbgegebenDoubleRefresh auf 'true' gesetzt wird, dann
// * wird der Tree ein zweites mal Refresht. Dies wird benoetigt falls zuvor im Tree eine Datasource geladen
// * wurde die keine Daten enthaelt. Die Daten werden erst angezeigt wenn der Tree ein zweites mal refresht wird.
// ****
var InteressentDokumentTreeAbgegebenListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
      if(InteressentDokumentTreeAbgegebenDoubleRefresh==true)
      {
      	window.setTimeout('InteressentDokumentTreeAbgegebenDatasourceRefresh()',10);
      }
      InteressentDokumentTreeAbgegebenDoubleRefresh=false;
  }
};

// ****************** FUNKTIONEN ************************** //

// ****
// * Weisst dem Prestudenten Dokumente zu die er bereits abgegeben hat
// ****
function InteressentDokumenteAdd()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	//Alle markierten Dokumente holen
	tree = document.getElementById('interessent-dokumente-tree-nichtabgegeben');
	paramList='';
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			col = tree.columns ? tree.columns["interessent-dokumente-tree-nichtabgegeben-dokument_kurzbz"] : "interessent-dokumente-tree-nichtabgegeben-dokument_kurzbz";
			dok = tree.view.getCellText(v,col);
			paramList += ';'+dok;
		}
	}

	//Prestudent_id holen
	prestudent_id = document.getElementById('student-prestudent-textbox-prestudent_id').value

	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value
	
	if(paramList!='')
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'dokumentprestudentadd');

		req.add('prestudent_id', prestudent_id);
		req.add('dokumente', paramList);
		req.add('studiengang_kz', studiengang_kz);

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
			// Wenn im Tree mit den abgegebenen Dokumenten kein Eintrag vorhanden ist,
			// dann muss der Tree zwei mal hintereinander Refresht werden weil sonst der neue Eintrag
			// nicht angezeigt wird.
			if(document.getElementById('interessent-dokumente-tree-abgegeben').view.rowCount==0)
			{
				InteressentDokumentTreeAbgegebenDoubleRefresh=true;
			}
			InteressentDokumentTreeNichtabgegebenDatasource.Refresh(false);
			InteressentDokumentTreeAbgegebenDatasource.Refresh(false);
			SetStatusBarText('Dokumente wurden hinzugefuegt');
		}
	}
	else
	{
		alert('Bitte zuerst ein Dokument markieren');
	}
}

// *****
// * Loescht die Zuordnung Dokument-Prestudent
// *****
function InteressentDokumenteRemove()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	//Alle markierten Dokumente holen
	tree = document.getElementById('interessent-dokumente-tree-abgegeben');
	paramList='';
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			col = tree.columns ? tree.columns["interessent-dokumente-tree-abgegeben-dokument_kurzbz"] : "interessent-dokumente-tree-abgegeben-dokument_kurzbz";
			dok = tree.view.getCellText(v,col);
			paramList += ';'+dok;
		}
	}

	//Prestudent_id holen
	prestudent_id = document.getElementById('student-prestudent-textbox-prestudent_id').value;
	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	
	if(paramList!='')
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'dokumentprestudentdel');

		req.add('prestudent_id', prestudent_id);
		req.add('dokumente', paramList);
		req.add('studiengang_kz', studiengang_kz);

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
			// Wenn im Tree mit den noch nicht abgegebenen Dokumenten kein Eintrag vorhanden ist,
			// dann muss der Tree zwei mal hintereinander Refresht werden weil sonst der neue Eintrag
			// nicht angezeigt wird.
			if(document.getElementById('interessent-dokumente-tree-nichtabgegeben').view.rowCount==0)
			{
				InteressentDokumentTreeNichtabgegebenDoubleRefresh=true;
			}
			InteressentDokumentTreeNichtabgegebenDatasource.Refresh(false);
			InteressentDokumentTreeAbgegebenDatasource.Refresh(false);
			SetStatusBarText('Dokumente wurden entfernt');
		}
	}
	else
	{
		alert('Bitte zuerst ein Dokument markieren');
	}
}

// ****
// * Refresht den Tree mit den Abgegeben Dokumenten
// ****
function InteressentDokumentTreeAbgegebenDatasourceRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	InteressentDokumentTreeAbgegebenDatasource.Refresh(false);
}

// ****
// * Refresht den Tree mit den noch nicht Abgegebenen Dokumenten
// ****
function InteressentDokumentTreeNichtabgegebenDatasourceRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	InteressentDokumentTreeNichtabgegebenDatasource.Refresh(false);
}

function InteressentDokumentAbgegebenTreeSelect()
{
	var tree=document.getElementById('interessent-dokumente-tree-abgegeben');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist der zu selektierende DS gespeichert
	if(InteressentDokumentTreeAbgegebenSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//Uid der row holen
			col = tree.columns ? tree.columns["interessent-dokumente-tree-abgegeben-dokument_kurzbz"] : "interessent-dokumente-tree-abgegeben-dokument_kurzbz";
			kurzbz=tree.view.getCellText(i,col);

			if(kurzbz == InteressentDokumentTreeAbgegebenSelectID)
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

function InteressentDokumentNichtAbgegebenTreeSelect()
{
	var tree=document.getElementById('interessent-dokumente-tree-nichtabgegeben');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist der zu selektierende DS gespeichert
	if(InteressentDokumentTreeNichtAbgegebenSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//Uid der row holen
			col = tree.columns ? tree.columns["interessent-dokumente-tree-nichtabgegeben-dokument_kurzbz"] : "interessent-dokumente-tree-nichtabgegeben-dokument_kurzbz";
			kurzbz=tree.view.getCellText(i,col);

			if(kurzbz == InteressentDokumentTreeNichtAbgegebenSelectID)
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

function InteressentDokumenteAbgegebenTreeSort()
{
	var i;
	var tree=document.getElementById('interessent-dokumente-tree-abgegeben');
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
		i = 0;
	col = tree.columns ? tree.columns["interessent-dokumente-tree-abgegeben-dokument_kurzbz"] : "interessent-dokumente-tree-abgegeben-dokument_kurzbz";
	InteressentDokumentTreeAbgegebenSelectID = tree.view.getCellText(i,col);
	window.setTimeout("InteressentDokumentAbgegebenTreeSelect()",10);
}

function InteressentDokumenteNichtAbgegebenTreeSort()
{
	var i;
	var tree=document.getElementById('interessent-dokumente-tree-nichtabgegeben');
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
		i = 0;
	col = tree.columns ? tree.columns["interessent-dokumente-tree-nichtabgegeben-dokument_kurzbz"] : "interessent-dokumente-tree-abgegeben-nichtdokument_kurzbz";
	InteressentDokumentTreeNichtAbgegebenSelectID = tree.view.getCellText(i,col);
	window.setTimeout("InteressentDokumentNichtAbgegebenTreeSelect()",10);
}

function InteressentDokumenteFilter()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('tree-verband');

	//Wenn nichts markiert wurde -> beenden
	if(tree.currentIndex==-1)
	{
		alert('Bitte einen Studiengang/Semester waehlen');
		return;
	}
	
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
	if(typ=='')
		typ='student';
	if(stsem=='')
		stsem = getStudiensemester();
	url = "<?php echo APP_ROOT; ?>rdf/student.rdf.php?studiengang_kz="+stg_kz+"&semester="+sem+"&verband="+ver+"&gruppe="+grp+"&gruppe_kurzbz="+gruppe+"&studiensemester_kurzbz="+stsem+"&typ="+typ+"&filter2=dokumente&"+gettimestamp();
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
}