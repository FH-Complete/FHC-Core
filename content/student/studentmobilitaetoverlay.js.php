<?php
/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */

require_once('../../config/vilesci.config.inc.php');

?>
var StudentMobilitaetTreeDatasource;
var StudentMobilitaetTreeSelectID='';

// ****
// * Observer fuer BISIO Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentMobilitaetTreeSinkObserver =
{
	onBeginLoad : function(pSink)
	{
		tree = document.getElementById('student-mobilitaet-tree');
		tree.removeEventListener('select', StudentMobilitaetAuswahl, false);
	},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-mobilitaet-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var StudentMobilitaetTreeListener =
{
	willRebuild : function(builder) {  },
	didRebuild : function(builder)
	{
		tree = document.getElementById('student-mobilitaet-tree');
		tree.addEventListener('select', StudentMobilitaetAuswahl, false);
		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		window.setTimeout(StudentMobilitaetTreeSelectID,10);
	}
};
function StudentMobilitaetAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var gstree = document.getElementById('student-mobilitaet-tree');

	if (gstree.currentIndex==-1)
		return;

	StudentMobilitaetDisableFields(false);

	//Ausgewaehlte ID holen
	var mobilitaet_id = getTreeCellText(gstree, 'student-mobilitaet-tree-mobilitaet_id', gstree.currentIndex);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/mobilitaet.rdf.php?mobilitaet_id='+mobilitaet_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
			getService(Components.interfaces.nsIRDFService);

	var dsource = rdfService.GetDataSourceBlocking(url);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/mobilitaet/" + mobilitaet_id);
	var predicateNS = "http://www.technikum-wien.at/mobilitaet/rdf";
	//Daten holen
	var mobilitaetsprogramm_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#mobilitaetsprogramm_code" ));
	var studiensemester_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiensemester_kurzbz" ));
	var gsprogramm_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gsprogramm_id" ));
	var mobilitaetstyp_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#mobilitaetstyp_kurzbz" ));
	var firma_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#firma_id" ));
	var status_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#status_kurzbz" ));
	var ausbildungssemester = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ausbildungssemester" ));

	document.getElementById('student-mobilitaet-detail-checkbox-neu').checked=false;
	document.getElementById('student-mobilitaet-detail-textbox-mobilitaet_id').value=mobilitaet_id;
	document.getElementById('student-mobilitaet-menulist-studiensemester').value=studiensemester_kurzbz;
	document.getElementById('student-mobilitaet-menulist-mobilitaetsprogramm').value=mobilitaetsprogramm_code;
	document.getElementById('student-mobilitaet-menulist-gsprogramm').value=gsprogramm_id;
	document.getElementById('student-mobilitaet-menulist-mobilitaetstyp').value=mobilitaetstyp_kurzbz;
	document.getElementById('student-mobilitaet-menulist-firma').value=firma_id;
	document.getElementById('student-mobilitaet-menulist-status').value=status_kurzbz;
	document.getElementById('student-mobilitaet-textbox-ausbildungssemester').value=ausbildungssemester;
}

function StudentMobilitaetLoad(prestudent_id)
{
	StudentMobilitaetResetFileds();
	StudentMobilitaetDisableFields(true);
	document.getElementById('student-mobilitaet-detail-textbox-prestudent_id').value=prestudent_id;
	document.getElementById('student-mobilitaet-button-neu').disabled=false;
	document.getElementById('student-mobilitaet-button-loeschen').disabled=false;

	var gstree = document.getElementById('student-mobilitaet-tree');

	url='<?php echo APP_ROOT;?>rdf/mobilitaet.rdf.php?prestudent_id='+prestudent_id+"&"+gettimestamp();

	try
	{
		StudentMobilitaetTreeDatasource.removeXMLSinkObserver(StudentMobilitaetTreeSinkObserver);
		gstree.builder.removeListener(StudentMobilitaetTreeListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = gstree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		gstree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	gstree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentMobilitaetTreeDatasource = rdfService.GetDataSource(url);
	StudentMobilitaetTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentMobilitaetTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	gstree.database.AddDataSource(StudentMobilitaetTreeDatasource);
	StudentMobilitaetTreeDatasource.addXMLSinkObserver(StudentMobilitaetTreeSinkObserver);
	gstree.builder.addListener(StudentMobilitaetTreeListener);
}

// ****
// * Aktiviert die Felder zum Anlegen eines neuen Eintrages
// ****
function StudentMobilitaetNeu()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//Felder Resetten und Aktivieren
	StudentMobilitaetResetFileds();
	StudentMobilitaetDisableFields(false);

	// Defaultwerte fuer neuen Eintrag setzen
	var stsem = getStudiensemester();
	document.getElementById('student-mobilitaet-menulist-studiensemester').value=stsem;
	document.getElementById('student-mobilitaet-menulist-status').value='Student';
	document.getElementById('student-mobilitaet-menulist-mobilitaetstyp').value='GS';
}

function StudentMobilitaetResetFileds()
{
	var stsem = getStudiensemester();
	document.getElementById('student-mobilitaet-detail-checkbox-neu').checked=true;
	document.getElementById('student-mobilitaet-detail-textbox-mobilitaet_id').value='';
	document.getElementById('student-mobilitaet-menulist-studiensemester').value=stsem;
	document.getElementById('student-mobilitaet-menulist-mobilitaetsprogramm').value='';
	document.getElementById('student-mobilitaet-menulist-gsprogramm').value='';
	document.getElementById('student-mobilitaet-menulist-mobilitaetstyp').value='GS';
	document.getElementById('student-mobilitaet-menulist-firma').value='';
	document.getElementById('student-mobilitaet-menulist-status').value='';
	document.getElementById('student-mobilitaet-textbox-ausbildungssemester').value='';
}
function StudentMobilitaetDisableFields(val)
{
	document.getElementById('student-mobilitaet-menulist-studiensemester').disabled=val;
	document.getElementById('student-mobilitaet-menulist-mobilitaetsprogramm').disabled=val;
	document.getElementById('student-mobilitaet-menulist-gsprogramm').disabled=val;
	document.getElementById('student-mobilitaet-menulist-mobilitaetstyp').disabled=val;
	document.getElementById('student-mobilitaet-menulist-firma').disabled=val;
	document.getElementById('student-mobilitaet-menulist-status').disabled=val;
	document.getElementById('student-mobilitaet-textbox-ausbildungssemester').disabled=val;
	document.getElementById('student-mobilitaet-button-speichern').disabled=val;
	document.getElementById('student-mobilitaet-button-kopie-speichern').disabled=val;
}

// ****
// * Speichert den Mobilitaet Datensatz
// ****
function StudentMobilitaetSpeichern(newval)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	var ausbildungssemester = document.getElementById('student-mobilitaet-textbox-ausbildungssemester').value;
	var status = document.getElementById('student-mobilitaet-menulist-status').value;
	var firma = document.getElementById('student-mobilitaet-menulist-firma').value;
	var mobilitaetstyp = document.getElementById('student-mobilitaet-menulist-mobilitaetstyp').value;
	var gsprogramm = document.getElementById('student-mobilitaet-menulist-gsprogramm').value;
	var mobilitaetsprogramm = document.getElementById('student-mobilitaet-menulist-mobilitaetsprogramm').value;
	var studiensemester = document.getElementById('student-mobilitaet-menulist-studiensemester').value;
	if (newval == true)
	{
		var neu = true;
	}
	else
	{
		var neu = document.getElementById('student-mobilitaet-detail-checkbox-neu').checked;
	}
	var prestudent_id = document.getElementById('student-mobilitaet-detail-textbox-prestudent_id').value;
	var mobilitaet_id = document.getElementById('student-mobilitaet-detail-textbox-mobilitaet_id').value;

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savemobilitaet');

	if(neu==false)
		req.add('mobilitaet_id', mobilitaet_id);

	req.add('neu', neu);
	req.add('mobilitaetsprogramm_code', mobilitaetsprogramm);
	req.add('ausbildungssemester', ausbildungssemester);
	req.add('status_kurzbz', status);
	req.add('firma_id', firma);
	req.add('mobilitaetstyp_kurzbz', mobilitaetstyp);
	req.add('gsprogramm_id', gsprogramm);
	req.add('studiensemester_kurzbz', studiensemester);
	req.add('prestudent_id', prestudent_id);

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
		document.getElementById('student-mobilitaet-detail-textbox-mobilitaet_id').value = val.dbdml_data;
		document.getElementById('student-mobilitaet-detail-checkbox-neu').checked=false;
		StudentMobilitaetTreeSelectID = val.dbdml_data;
		StudentMobilitaetTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Selectiert den Mobilitaet Eintrag nachdem der Tree
// * rebuildet wurde.
// ****
function StudentMobilitaetTreeSelectID()
{
	var tree=document.getElementById('student-mobilitaet-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentMobilitaetSelectID!=null)
	{
		for(var i=0;i < items;i++)
		{
			//ID der row holen
			var mobilitaet_id = getTreeCellText(tree, 'student-mobilitaet-tree-mobilitaet_id', i);

			//wenn dies die zu selektierende Zeile
			if(mobilitaet_id == StudentMobilitaetSelectID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentMobilitaetSelectID=null;
				return true;
			}
		}
	}
}

function StudentMobilitaetDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-mobilitaet-tree');

	if (tree.currentIndex==-1) return;

	StudentMobilitaetDisableFields(false);

	//Ausgewaehlte Nr holen
	var mobilitaet_id = getTreeCellText(tree, 'student-mobilitaet-tree-mobilitaet_id', tree.currentIndex);

	if(confirm('Diesen Eintrag wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'deletemobilitaet');
		req.add('mobilitaet_id', mobilitaet_id);

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
			StudentMobilitaetSelectID=mobilitaet_id;
			StudentMobilitaetTreeDatasource.Refresh(false); //non blocking
			SetStatusBarText('Daten wurden geloescht');
			StudentMobilitaetResetFileds();
			StudentMobilitaetDisableFields(true);
		}
	}
}
