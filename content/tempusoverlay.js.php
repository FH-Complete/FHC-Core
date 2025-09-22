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
include('../config/vilesci.config.inc.php');
?>

var currentAuswahl=new auswahlValues();
var LvTreeDatasource;
var StudentTreeDatasource;

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
	document.getElementById('tempus-lva-filter').value='';
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var tree=document.getElementById('tree-verband');
	if(tree.currentIndex==-1)
		return;

	var col=tree.columns ? tree.columns["stg_kz"] : "stg_kz";
	var stg_kz=tree.view.getCellText(tree.currentIndex,col);
	col=tree.columns ? tree.columns["sem"] : "sem";
	var sem=tree.view.getCellText(tree.currentIndex,col);
	col=tree.columns ? tree.columns["ver"] : "ver";
	var ver=tree.view.getCellText(tree.currentIndex,col);
	col=tree.columns ? tree.columns["grp"] : "grp";
	var grp=tree.view.getCellText(tree.currentIndex,col);
	col=tree.columns ? tree.columns["gruppe"] : "gruppe";
	var gruppe=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["typ"] : "typ";
	var typ=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["stsem"] : "stsem";
	var stsem=tree.view.getCellText(tree.currentIndex,col);
	col = tree.columns ? tree.columns["orgform"] : "orgform";
	var orgform=tree.view.getCellText(tree.currentIndex,col);

	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));
	var attributes="&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe;
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
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
		//alert (url);
		var contentFrame=document.getElementById('iframeTimeTableSemester');
		var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
		if (gruppe!=null && gruppe!=0 &gruppe!='')
			var type="?type=gruppe";
		else
			var type="?type=verband";
		url+=type+attributes+"&semesterplan=true&"+gettimestamp();
		if (url)
			contentFrame.setAttribute('src', url);
	}

	var order = LehrstundeGetSortOrder();
	LVAFilterReset();
	// LVAs
	var vboxLehrveranstalungPlanung=document.getElementById('vboxLehrveranstalungPlanung');
	var attribute='../rdf/lehreinheit-lvplan.rdf.php'+type+"&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe+"&order="+order+"&orgform="+orgform;

	vboxLehrveranstalungPlanung.setAttribute('datasources',attribute);

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
	catch(e){}


	// Lehrveranstaltung
	try
	{
		url = '<?php echo APP_ROOT; ?>rdf/lehrveranstaltung_einheiten.rdf.php?stg_kz='+encodeURIComponent(stg_kz)+'&sem='+encodeURIComponent(sem)+'&ver='+encodeURIComponent(ver)+'&grp='+encodeURIComponent(grp)+'&gruppe='+encodeURIComponent(gruppe)+'&orgform='+encodeURIComponent(orgform)+'&'+gettimestamp();
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
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var treeOrt=document.getElementById('tree-ort');
	var col=treeOrt.columns ? treeOrt.columns["ort_kurzbz"] : "ort_kurzbz";
	var ort=treeOrt.view.getCellText(treeOrt.currentIndex,col);
	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));

	if(ort=='')
		return false;

	var attributes="?type=ort&ort="+ort+"&datum="+datum;
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
	url+=attributes+'&'+gettimestamp();
	if (url)
		contentFrame.setAttribute('src', url);

	// Semesterplan
	var semesterplan=document.getElementById('tabpanels-main');
	var panelIndex=semesterplan.getAttribute("selectedIndex");
	if (panelIndex==1)
	{
		//alert (url);
		var contentFrame=document.getElementById('iframeTimeTableSemester');
		var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
		url+=attributes+"&semesterplan=true&"+gettimestamp();
		if (url)
			contentFrame.setAttribute('src', url);
	}
}

function onLektorSelect(event)
{
	document.getElementById('tempus-lva-filter').value='';
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var treeLektor=document.getElementById('tree-lektor');
	var col=treeLektor.columns ? treeLektor.columns["uid"] : "uid";
	//debug(col+'-'+treeLektor.currentIndex);
	try
	{
		var uid=treeLektor.view.getCellText(treeLektor.currentIndex,col);
	}
	catch(e)
	{
	}
	if(uid=='')
		return;
	//var treeVerband=document.getElementById('tree-verband');
	//var stg_kz=treeVerband.view.getCellText(treeVerband.currentIndex,"stg_kz");
	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));

	var attributes="?type=lektor&pers_uid="+uid+"&datum="+datum;
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
	url+=attributes+'&'+gettimestamp();
	if (url)
		contentFrame.setAttribute('src', url);

	// Semesterplan
	var semesterplan=document.getElementById('tabpanels-main');
	var panelIndex=semesterplan.getAttribute("selectedIndex");
	if (panelIndex==1)
	{
		//alert (url);
		var contentFrame=document.getElementById('iframeTimeTableSemester');
		var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
		url+=attributes+"&semesterplan=true&"+gettimestamp();
		if (url)
			contentFrame.setAttribute('src', url);
	}

	var order = LehrstundeGetSortOrder();
	LVAFilterReset();
	// LVAs
	var vboxLehrveranstalungPlanung=document.getElementById('vboxLehrveranstalungPlanung');
	vboxLehrveranstalungPlanung.setAttribute('datasources','../rdf/lehreinheit-lvplan.rdf.php?'+"type=lektor&lektor="+uid+"&order="+order+"&"+gettimestamp());

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
	//Wenn der Filter angewendet wurde, gibt es keinen Parent. Daher wird hier der stg_idx auf 0 gesetzt.
	if(stg_idx == -1 && uid != '')
		stg_idx = 0;

	if (stg_idx != -1)
		var stg_kz=tree.view.getCellText(stg_idx,col);
	else
		var stg_kz = 0;

	document.getElementById('LehrveranstaltungEditor').setAttribute('stg_kz',stg_kz);
	document.getElementById('LehrveranstaltungEditor').setAttribute('uid',uid);

	// Lehrveranstaltung des Lektors laden
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	try
	{
		//alert(stg_kz);
		url = '<?php echo APP_ROOT; ?>rdf/lehrveranstaltung_einheiten.rdf.php?stg_kz='+stg_kz+'&uid='+uid+'&'+gettimestamp();
		var treeLV=document.getElementById('lehrveranstaltung-tree');

		//Alte DS entfernen
		var oldDatasources = treeLV.database.GetDataSources();
		try
		{
			LvTreeDatasource.removeXMLSinkObserver(LvTreeSinkObserver);
			treeLV.builder.removeListener(LvTreeListener);
		}
		catch(e)
		{}

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

function LektorFunktionLoadZeitwunschAdminUrl(){

    var treeLektor = document.getElementById('tree-lektor');
    var col = treeLektor.columns ? treeLektor.columns["uid"] : "uid";
    try
    {
        var uid = treeLektor.view.getCellText(treeLektor.currentIndex,col);
    }
    catch(e)
    {
    }

    if (uid == '' || uid == undefined)
    {
        alert('LektorIn auswählen, um Zeitwünsche einsehen zu können.');
        return;
    }
    else
    {
        window.open('<?php echo APP_ROOT ?>vilesci/personen/zeitwunsch.php?uid=' + uid);
    }
}

function loadURL(event)
{
        var contentFrame = document.getElementById('contentFrame');
        var url = event.target.getAttribute('value');

        if (url) contentFrame.setAttribute('src', url);
};

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

	col = tree.columns ? tree.columns["fachbereich-treecol-kurzbz"] : "fachbereich-treecol-kurzbz";
	var kurzbz=tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["fachbereich-treecol-uid"] : "fachbereich-treecol-uid";
	var uid=tree.view.getCellText(tree.currentIndex,col);

	//Wenn auf einen Mitarbeiter geklickt wird, dann die kurzbz vom uebergeordneten
	//Fachbereich holen
	if(uid!='')
	{
		idx = tree.view.getParentIndex(tree.currentIndex);
		col = tree.columns ? tree.columns["fachbereich-treecol-kurzbz"] : "fachbereich-treecol-kurzbz";
		var kurzbz=tree.view.getCellText(idx,col);
	}


	//----
	document.getElementById('tempus-lva-filter').value='';
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var contentFrame=document.getElementById('iframeTimeTableWeek');

	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));

	var attributes="?type=fachbereich&fachbereich_kurzbz="+kurzbz+"&datum="+datum;
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
	url+=attributes+'&'+gettimestamp();
	if (url)
		contentFrame.setAttribute('src', url);

	// Semesterplan
	var semesterplan=document.getElementById('tabpanels-main');
	var panelIndex=semesterplan.getAttribute("selectedIndex");
	if (panelIndex==1)
	{
		//alert (url);
		var contentFrame=document.getElementById('iframeTimeTableSemester');
		var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
		url+=attributes+"&semesterplan=true&"+gettimestamp();
		if (url)
			contentFrame.setAttribute('src', url);
	}

	var order = LehrstundeGetSortOrder();
	LVAFilterReset();
	// LVAs
	var vboxLehrveranstalungPlanung=document.getElementById('vboxLehrveranstalungPlanung');
	vboxLehrveranstalungPlanung.setAttribute('datasources','../rdf/lehreinheit-lvplan.rdf.php?'+"type=fachbereich&fachbereich_kurzbz="+kurzbz+"&order="+order+"&"+gettimestamp());


	// Lehrveranstaltung des Fachbereichs
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	try
	{
		//alert(stg_kz);
		url = '<?php echo APP_ROOT; ?>rdf/lehrveranstaltung_einheiten.rdf.php?fachbereich_kurzbz='+kurzbz+'&'+gettimestamp();
		var treeLV=document.getElementById('lehrveranstaltung-tree');

		//Alte DS entfernen
		var oldDatasources = treeLV.database.GetDataSources();
		try
		{
			LvTreeDatasource.removeXMLSinkObserver(LvTreeSinkObserver);
			treeLV.builder.removeListener(LvTreeListener);
		}
		catch(e)
		{}

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
