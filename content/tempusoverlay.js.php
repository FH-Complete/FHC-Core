<?php
include('../vilesci/config.inc.php');
?>

var currentAuswahl=new auswahlValues();
var LvTreeDatasource;

function auswahlValues()
{
	this.stg_kz=null;
	this.sem=null;
	this.ver=null;
	this.grp=null;
	this.gruppe=null;
	this.lektor_uid=null;
}

function onVerbandSelect()
{	
	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var tree=document.getElementById('tree-verband');
	if(tree.currentIndex==-1)
		return;
	var stg_kz=tree.view.getCellText(tree.currentIndex,"stg_kz");
	var sem=tree.view.getCellText(tree.currentIndex,"sem");
	var ver=tree.view.getCellText(tree.currentIndex,"ver");
	var grp=tree.view.getCellText(tree.currentIndex,"grp");
	var gruppe=tree.view.getCellText(tree.currentIndex,"gruppe");
	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));
	var attributes="&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe;
	var url = "<?php echo APP_ROOT; ?>content/timetable-week.xul.php";
	if (gruppe!=null && gruppe!=0 &gruppe!='')
		var type="?type=gruppe";
	else
		var type="?type=verband";
	url+=type+attributes+"&datum="+datum;
	if (url)
	{
		//alert(url);
		contentFrame.setAttribute('src', url);
	}

	currentAuswahl.stg_kz=stg_kz;
	currentAuswahl.sem=sem;
	currentAuswahl.ver=ver;
	currentAuswahl.grp=grp;
	currentAuswahl.gruppe=gruppe;

	// Semesterplan
	var semesterplan=document.getElementById('tabpanels-main');
	var panelIndex=semesterplan.getAttribute("selectedIndex");
	if (panelIndex==1)
	{
		alert (url);
		var contentFrame=document.getElementById('iframeTimeTableSemester');
		var url = "<?php echo APP_ROOT; ?>content/timetable-week.xul.php";
		if (gruppe!=null && gruppe!=0 &gruppe!='')
			var type="?type=gruppe";
		else
			var type="?type=verband";
		url+=type+attributes+"&semesterplan=true";
		if (url)
			contentFrame.setAttribute('src', url);
	}


	// LVAs
	var vboxLehrveranstalungPlanung=document.getElementById('vboxLehrveranstalungPlanung');
	var attribute='../rdf/lehreinheit-lvplan.rdf.php'+type+"&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe;
	vboxLehrveranstalungPlanung.setAttribute('datasources',attribute);

	// Studenten
	var treeStudenten=document.getElementById('treeStudenten');
	attribute="<?php echo APP_ROOT; ?>rdf/student.rdf.php?"+"stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe;
	treeStudenten.setAttribute('datasources',attribute);

	
	// Lehrveranstaltung
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
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
				
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		LvTreeDatasource = rdfService.GetDataSource(url);
		LvTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		LvTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		treeLV.database.AddDataSource(LvTreeDatasource);
		LvTreeDatasource.addXMLSinkObserver(LvTreeSinkObserver);
		treeLV.builder.addListener(LvTreeListener);
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

function onLektorSelect()
{

	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var treeLektor=document.getElementById('tree-lektor');
	var uid=treeLektor.view.getCellText(treeLektor.currentIndex,"uid");
	if(uid=='')
		return;
	//var treeVerband=document.getElementById('tree-verband');
	//var stg_kz=treeVerband.view.getCellText(treeVerband.currentIndex,"stg_kz");
	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));

	var attributes="?type=lektor&pers_uid="+uid+"&datum="+datum;
	var url = "<?php echo APP_ROOT; ?>content/timetable-week.xul.php";
	url+=attributes;
	if (url)
		contentFrame.setAttribute('src', url);
	// LVAs
	var vboxLehrveranstalungPlanung=document.getElementById('vboxLehrveranstalungPlanung');
	vboxLehrveranstalungPlanung.setAttribute('datasources','../rdf/lehreinheit-lvplan.rdf.php?'+"type=lektor&lektor="+uid);
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
