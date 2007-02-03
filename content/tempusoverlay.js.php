<?php
include('../vilesci/config.inc.php');
?>

var currentAuswahl=new auswahlValues();

function auswahlValues()
{
	this.stg_kz=null;
	this.sem=null;
	this.ver=null;
	this.grp=null;
	this.einheit=null;
	this.lektor_uid=null;
}

function onVerbandSelect()
{
	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var tree=document.getElementById('tree-verband');
	var stg_kz=tree.view.getCellText(tree.currentIndex,"stg_kz");
	var sem=tree.view.getCellText(tree.currentIndex,"sem");
	var ver=tree.view.getCellText(tree.currentIndex,"ver");
	var grp=tree.view.getCellText(tree.currentIndex,"grp");
	var einheit=tree.view.getCellText(tree.currentIndex,"einheit");
	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));
	var attributes="&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&einheit="+einheit;
	var url = "<?php echo APP_ROOT; ?>content/timetable-week.xul.php";
	if (einheit!=null && einheit!=0 &einheit!='')
		var type="?type=einheit";
	else
		var type="?type=verband";
	url+=type+attributes+"&datum="+datum;
	if (url)
		contentFrame.setAttribute('src', url);

	currentAuswahl.stg_kz=stg_kz;
	currentAuswahl.sem=sem;
	currentAuswahl.ver=ver;
	currentAuswahl.grp=grp;
	currentAuswahl.einheit=einheit;

	// Semesterplan
	var semesterplan=document.getElementById('tabpanels-main');
	var panelIndex=semesterplan.getAttribute("selectedIndex");
	if (panelIndex==1)
	{
		alert (url);
		var contentFrame=document.getElementById('iframeTimeTableSemester');
		var url = "<?php echo APP_ROOT; ?>content/timetable-week.xul.php";
		if (einheit!=null && einheit!=0 &einheit!='')
			var type="?type=einheit";
		else
			var type="?type=verband";
		url+=type+attributes+"&semesterplan=true";
		if (url)
			contentFrame.setAttribute('src', url);
	}


	// LVAs
	var vboxLehrveranstalungPlanung=document.getElementById('vboxLehrveranstalungPlanung');
	var attribute='../rdf/lehreinheit-lvplan.rdf.php'+type+"&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&einheit="+einheit;
	vboxLehrveranstalungPlanung.setAttribute('datasources',attribute);

	/*
	// Studenten
	var treeStudenten=document.getElementById('treeStudenten');
	attribute="<?php echo APP_ROOT; ?>rdf/student.rdf.php?"+"stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&einheit="+einheit;
	treeStudenten.setAttribute('datasources',attribute);

	// LFVT
	var req = new phpRequest('lfvt.rdf.php','pam','pam');
	req.add('stg_kz',stg_kz);
	req.add('sem',sem);
	req.add('ver',ver);
	req.add('grp',grp);
	req.add('einheit',einheit);

	var response = req.execute();

	// http error handling ist in phpRequest
	// SQL-Error werden derzeit noch nicht behandelt!
	//if (response!='ok') alert(response);

	// XML in Datasource parsen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/tempus/lva/liste');

	var treeLFVT=document.getElementById('treeLFVT');

	// Trick 17	(sonst gibt's ein Permission denied)
	try {
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	} catch(e) {
		alert(e);
		return;
	}

	// alte datenquellen entfernen
	var sources=treeLFVT.database.GetDataSources();
	while (sources.hasMoreElements()){
		treeLFVT.database.RemoveDataSource(sources.getNext());
	}

	// neue Datenquelle setzen
	treeLFVT.database.AddDataSource(dsource);
	treeLFVT.builder.rebuild();

	//treeLFVT.setAttribute('datasources','lfvt.rdf.php?'+"stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&einheit="+einheit);
	//alert('lfvt.rdf.php?'+"stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&einheit="+einheit);
	*/
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

/*
	// LFVT
	var req = new phpRequest('lfvt.rdf.php','pam','pam');
	req.add('lektor',uid);

	var response = req.execute();

	// http error handling ist in phpRequest
	// SQL-Error werden derzeit noch nicht behandelt!
	//if (response!='ok') alert(response);

	// XML in Datasource parsen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/tempus/lva/liste');

	var treeLFVT=document.getElementById('treeLFVT');

	// Trick 17	(sonst gibt's ein Permission denied)
	try {
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	} catch(e) {
		alert(e);
		return;
	}

	// alte datenquellen entfernen
	var sources=treeLFVT.database.GetDataSources();
	while (sources.hasMoreElements()){
		treeLFVT.database.RemoveDataSource(sources.getNext());
	}

	// neue Datenquelle setzen
	treeLFVT.database.AddDataSource(dsource);
	treeLFVT.builder.rebuild();
*/
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