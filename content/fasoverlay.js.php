<?php
include('../vilesci/config.inc.php');
?>

var currentAuswahl=new auswahlValues();
var LvTreeDatasource;
var LektorTreeDatasource;
var LektorTreeOpenStudiengang;
var StudentTreeDatasource;

// ****
// * initialisiert den Lektor Tree
// ****
function initLektorTree()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	try
	{
		url = '<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php?user=true&'+gettimestamp();
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
	
	currentAuswahl.stg_kz=stg_kz;
	currentAuswahl.sem=sem;
	currentAuswahl.ver=ver;
	currentAuswahl.grp=grp;
	currentAuswahl.gruppe=gruppe;

	// Studenten
	try
	{
		url = "<?php echo APP_ROOT; ?>rdf/student.rdf.php?"+"stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe+"&"+gettimestamp();
		var treeStudent=document.getElementById('student-tree');
		
		//Alte DS entfernen
		var oldDatasources = treeStudent.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			treeStudent.database.RemoveDataSource(oldDatasources.getNext());
		}

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		StudentTreeDatasource = rdfService.GetDataSource(url);
		StudentTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		StudentTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		treeStudent.database.AddDataSource(StudentTreeDatasource);
		StudentTreeDatasource.addXMLSinkObserver(StudentTreeSinkObserver);
		treeStudent.builder.addListener(StudentTreeListener);
		StudentDetailReset();
		StudentDetailDisableFields(true);
	}
	catch(e)
	{
		debug(e);
	}

	// Lehrveranstaltung
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
