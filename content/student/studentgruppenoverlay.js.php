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

require_once('../../config/vilesci.config.inc.php');

?>
// *********** Globale Variablen *****************//
var StudentGruppenTreeDatasource=null; //Datasource fuer den GruppenTree
// ********** Observer und Listener ************* //


// ****
// * Observer fuer den GruppenTree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentGruppenSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-gruppen-tree').builder.rebuild();
	}
};


// ****
// * Nach dem Rebuild des GruppenTrees
// ****
var StudentGruppenListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
      	//Bei Bedarf Datensatz markieren
  }
};


// ****************** FUNKTIONEN ************************** //

function StudentGruppenLoadData()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	tree = document.getElementById('student-gruppen-tree');
	
	//Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	
	if(oldDatasources.hasMoreElements())
	{
		//Wenn Datasource bereits gesetzt ist, dann muss nicht neu geladen werden
		return true;
	}
	
	//Alte Datasource entfernen
	StudentGruppenRemoveDatasource();
	
	var stsem = getStudiensemester();

	uid = document.getElementById('student-detail-textbox-uid').value;
	url="<?php echo APP_ROOT;?>rdf/gruppen.rdf.php?uid="+uid+"&studiensemester_kurzbz="+stsem+"&"+gettimestamp();
	
	try
	{
		StudentGruppenTreeDatasource.removeXMLSinkObserver(StudentGruppenSinkObserver);
		tree.builder.removeListener(StudentGruppenListener);
	}
	catch(e)
	{}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentGruppenTreeDatasource = rdfService.GetDataSource(url);
	StudentGruppenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentGruppenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	tree.database.AddDataSource(StudentGruppenTreeDatasource);
	StudentGruppenTreeDatasource.addXMLSinkObserver(StudentGruppenSinkObserver);
	tree.builder.addListener(StudentGruppenListener);
}

// ****
// * Datasource aus GruppenTree entfernen
// ****
function StudentGruppenRemoveDatasource()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	tree = document.getElementById('student-gruppen-tree');
	var oldDatasources = tree.database.GetDataSources();
	
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	tree.builder.rebuild();
}


function StudentGruppeDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('student-gruppen-tree');
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
	{
		alert('Bitte zuerst eine Gruppe markieren');
		return;
	}
	
	col = tree.columns ? tree.columns["student-gruppen-gruppe_kurzbz"] : "student-gruppen-gruppe_kurzbz";
	gruppe_kurzbz = tree.view.getCellText(i,col);
	col = tree.columns ? tree.columns["student-gruppen-uid"] : "student-gruppen-uid";
	uid = tree.view.getCellText(i,col);
	col = tree.columns ? tree.columns["student-gruppen-generiert"] : "student-gruppen-generiert";
	generiert = tree.view.getCellText(i,col);
	
	if(generiert=='Ja')
	{
		alert('Automatisch generierte Gruppezuordnungen koennen nicht geloescht werden');
		return false;
	}
	//Abfrage ob wirklich geloescht werden soll
	if (confirm(' Student wirklich aus Gruppe '+gruppe_kurzbz+' entfernen?'))
	{
		//Script zum loeschen aufrufen
		var req = new phpRequest('student/studentDBDML.php','','');

		req.add('type','deleteGruppenzuteilung');
		req.add('uid',uid);
		req.add('gruppe_kurzbz', gruppe_kurzbz);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response)

		if(!val.dbdml_return)
			alert(val.dbdml_errormsg)

		StudentGruppenTreeDatasource.Refresh(false);
	}
}