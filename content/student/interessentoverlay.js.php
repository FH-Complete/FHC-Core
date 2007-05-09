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
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(InteressentTreeSelectInteressent,10);
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
		InteressentTreeDatasource.Refresh(false); //non blocking
	}
	catch(e)
	{}
}

// ****
// * neuen Interessenten anlegen
// ****
function InteressentNeu()
{
	window.open('<?php echo APP_ROOT; ?>vilesci/personen/import/interessentenimport.php','Interessent anlegen', 'height=768,width=1024,resizable=yes,status=yes,scrollbars=yes,toolbar=yes,location=yes,menubar=yes');
}

// ****
// * Selectiert den Interessenten nachdem der Tree
// * rebuildet wurde.
// ****
function InteressentTreeSelectInteressent()
{
	var tree=document.getElementById('interessent-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist der zu selektierende Interessent gespeichert
	if(InteressentSelectId!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//Id der row holen
			col = tree.columns ? tree.columns["interessent-treecol-prestudent_id"] : "interessent-treecol-prestudent_id";
			prestudent_id=tree.view.getCellText(i,col);
						
			if(prestudent_id == InteressentSelectId)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	}
	document.getElementById('interessent-toolbar-label-anzahl').value='Anzahl: '+items;
}

// ****
// * Interessent loeschen
// ****
function InteressentDelete()
{
}

// ****
// * Leert alle Eingabe- und Auswahlfelder
// ****
function InteressentDetailReset()
{
	document.getElementById('interessent-detail-textbox-anrede').value='';
	document.getElementById('interessent-detail-textbox-titelpre').value='';
	document.getElementById('interessent-detail-textbox-titelpost').value='';
	document.getElementById('interessent-detail-textbox-vorname').value='';
	document.getElementById('interessent-detail-textbox-vornamen').value='';
	document.getElementById('interessent-detail-textbox-nachname').value='';
	document.getElementById('interessent-detail-textbox-geburtsdatum').value='';
	document.getElementById('interessent-detail-textbox-geburtsort').value='';
	document.getElementById('interessent-detail-textbox-geburtszeit').value='';
	document.getElementById('interessent-detail-textbox-anmerkung').value='';
	document.getElementById('interessent-detail-textbox-homepage').value='';
	document.getElementById('interessent-detail-textbox-svnr').value='';
	document.getElementById('interessent-detail-textbox-ersatzkennzeichen').value='';
	document.getElementById('interessent-detail-menulist-familienstand').value='l';
	document.getElementById('interessent-detail-menulist-geschlecht').value='m';
	document.getElementById('interessent-detail-checkbox-aktiv').checked=true;
	document.getElementById('interessent-detail-textbox-anzahlderkinder').value='';
	document.getElementById('interessent-detail-menulist-staatsbuergerschaft').value='A';
	document.getElementById('interessent-detail-menulist-geburtsnation').value='A';
	document.getElementById('interessent-detail-menulist-sprache').value='German';
	document.getElementById('interessent-detail-image').src='';
}

// ****
// * Deaktiviert alle Eingabe- und Auswahlfelder
// ****
function InteressentDetailDisableFields(val)
{
	document.getElementById('interessent-detail-textbox-anrede').disabled=val;
	document.getElementById('interessent-detail-textbox-titelpre').disabled=val;
	document.getElementById('interessent-detail-textbox-titelpost').disabled=val;
	document.getElementById('interessent-detail-textbox-vorname').disabled=val;
	document.getElementById('interessent-detail-textbox-vornamen').disabled=val;
	document.getElementById('interessent-detail-textbox-nachname').disabled=val;
	document.getElementById('interessent-detail-textbox-geburtsdatum').disabled=val;
	document.getElementById('interessent-detail-textbox-geburtsort').disabled=val;
	document.getElementById('interessent-detail-textbox-geburtszeit').disabled=val;
	document.getElementById('interessent-detail-textbox-anmerkung').disabled=val;
	document.getElementById('interessent-detail-textbox-homepage').disabled=val;
	document.getElementById('interessent-detail-textbox-svnr').disabled=val;
	document.getElementById('interessent-detail-textbox-ersatzkennzeichen').disabled=val;
	document.getElementById('interessent-detail-menulist-familienstand').disabled=val;
	document.getElementById('interessent-detail-menulist-geschlecht').disabled=val;
	document.getElementById('interessent-detail-checkbox-aktiv').disabled=val;
	document.getElementById('interessent-detail-textbox-anzahlderkinder').disabled=val;
	document.getElementById('interessent-detail-menulist-staatsbuergerschaft').disabled=val;
	document.getElementById('interessent-detail-menulist-geburtsnation').disabled=val;
	document.getElementById('interessent-detail-menulist-sprache').disabled=val;
	document.getElementById('interessent-detail-button-image-upload').disabled=val;
}

// ****
// * Speichert die Details
// ****
function InteressentDetailSave()
{
	//Werte holen
	anrede = document.getElementById('interessent-detail-textbox-anrede').value;
	titelpre = document.getElementById('interessent-detail-textbox-titelpre').value;
	titelpost = document.getElementById('interessent-detail-textbox-titelpost').value;
	vorname = document.getElementById('interessent-detail-textbox-vorname').value;
	vornamen = document.getElementById('interessent-detail-textbox-vornamen').value;
	nachname = document.getElementById('interessent-detail-textbox-nachname').value;
	geburtsdatum = document.getElementById('interessent-detail-textbox-geburtsdatum').value;
	geburtsort = document.getElementById('interessent-detail-textbox-geburtsort').value;
	geburtszeit = document.getElementById('interessent-detail-textbox-geburtszeit').value;
	anmerkung = document.getElementById('interessent-detail-textbox-anmerkung').value;
	homepage = document.getElementById('interessent-detail-textbox-homepage').value;
	svnr = document.getElementById('interessent-detail-textbox-svnr').value;
	ersatzkennzeichen = document.getElementById('interessent-detail-textbox-ersatzkennzeichen').value;
	familienstand = document.getElementById('interessent-detail-menulist-familienstand').value;
	geschlecht = document.getElementById('interessent-detail-menulist-geschlecht').value;
	aktiv = document.getElementById('interessent-detail-checkbox-aktiv').checked;
	anzahlderkinder = document.getElementById('interessent-detail-textbox-anzahlderkinder').value;
	staatsbuergerschaft = document.getElementById('interessent-detail-menulist-staatsbuergerschaft').value;
	geburtsnation = document.getElementById('interessent-detail-menulist-geburtsnation').value;
	sprache = document.getElementById('interessent-detail-menulist-sprache').value;
	person_id = document.getElementById('interessent-detail-textbox-person_id').value;
	prestudent_id = document.getElementById('interessent-detail-textbox-prestudent_id').value
			
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'saveperson');
		
	req.add('person_id', person_id);
	req.add('anrede', anrede);
	req.add('titelpre', titelpre);
	req.add('titelpost', titelpost);
	req.add('vorname', vorname);
	req.add('vornamen', vornamen);
	req.add('nachname', nachname);
	req.add('geburtsdatum', geburtsdatum);
	req.add('geburtsort', geburtsort);
	req.add('geburtszeit', geburtszeit);
	req.add('anmerkung', anmerkung);
	req.add('homepage', homepage);
	req.add('svnr', svnr);
	req.add('ersatzkennzeichen', ersatzkennzeichen);
	req.add('familienstand', familienstand);
	req.add('geschlecht', geschlecht);
	req.add('aktiv', aktiv);
	req.add('anzahlderkinder', anzahlderkinder);
	req.add('staatsbuergerschaft', staatsbuergerschaft);
	req.add('geburtsnation', geburtsnation);
	req.add('sprache', sprache);
		
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
				
		InteressentSelectId=prestudent_id;
		InteressentTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Ruft ein Script fuer den Upload des Bildes auf
// ****
function InteressentImageUpload(evt)
{
	person_id = document.getElementById('interessent-detail-textbox-person_id').value;
	if(person_id!='')
	{
		window.open('<?php echo APP_ROOT; ?>content/bildupload.php?person_id='+person_id,'Bild Upload', 'height=10,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
	}
	else
		alert('Es wurde keine Person ausgewaehlt');
}

// ****
// * Auswahl eines Interessenten
// * bei Auswahl eines Interessenten wird dieser geladen
// * und die Daten unten angezeigt
// ****
function InteressentAuswahl()
{
	
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('interessent-tree');

	if (tree.currentIndex==-1) return;
	
	try
	{
		//Ausgewaehlte ID holen
        var col = tree.columns ? tree.columns["interessent-treecol-prestudent_id"] : "interessent-treecol-prestudent_id";
		var prestudent_id=tree.view.getCellText(tree.currentIndex,col);
		if(prestudent_id!='')
		{
			//Interessent wurde markiert
			//loeschen button aktivieren
			InteressentDetailDisableFields(false);
			InteressentPrestudentDisableFields(false);
			document.getElementById('interessent-detail-button-save').disabled=false;
		}
		else
		{
			return false;
		}	
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	var url = '<?php echo APP_ROOT ?>rdf/interessentenbewerber.rdf.php?prestudent_id='+prestudent_id+'&'+gettimestamp();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
    
    var dsource = rdfService.GetDataSourceBlocking(url);
    
	var subject = rdfService.GetResource("http://www.technikum-wien.at/interessent/" + prestudent_id);

	var predicateNS = "http://www.technikum-wien.at/interessent/rdf";

	//Daten holen

	anrede = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anrede" ));
	titelpre=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titelpre" ));
	titelpost=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titelpost" ));
	vorname=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vorname" ));
	vornamen=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vornamen" ));
	nachname=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nachname" ));
	geburtsdatum=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geburtsdatum" ));
	geburtsort=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gebort" ));
	geburtszeit=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gebzeit" ));
	anmerkung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkungen" ));
	homepage=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#homepage" ));
	svnr=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#svnr" ));
	ersatzkennzeichen=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ersatzkennzeichen" ));
	familienstand=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#familienstand" ));
	geschlecht=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geschlecht" ));
	aktiv=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aktiv" ));	
	anzahlderkinder=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anzahlkinder" ));
	staatsbuergerschaft=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#staatsbuergerschaft" ));
	geburtsnation=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geburtsnation" ));
	sprache=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#sprache" ));
	person_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
		
	//Daten den Feldern zuweisen

	document.getElementById('interessent-detail-textbox-prestudent_id').value=prestudent_id;
	document.getElementById('interessent-detail-textbox-anrede').value=anrede;
	document.getElementById('interessent-detail-textbox-titelpre').value=titelpre;
	document.getElementById('interessent-detail-textbox-titelpost').value=titelpost;
	document.getElementById('interessent-detail-textbox-vorname').value=vorname;
	document.getElementById('interessent-detail-textbox-vornamen').value=vornamen;
	document.getElementById('interessent-detail-textbox-nachname').value=nachname;
	document.getElementById('interessent-detail-textbox-geburtsdatum').value=geburtsdatum;
	document.getElementById('interessent-detail-textbox-geburtsort').value=geburtsort;
	document.getElementById('interessent-detail-textbox-geburtszeit').value=geburtszeit;
	document.getElementById('interessent-detail-textbox-anmerkung').value=anmerkung;
	document.getElementById('interessent-detail-textbox-homepage').value=homepage;
	document.getElementById('interessent-detail-textbox-svnr').value=svnr;
	document.getElementById('interessent-detail-textbox-ersatzkennzeichen').value=ersatzkennzeichen;
	document.getElementById('interessent-detail-menulist-familienstand').value=familienstand;
	document.getElementById('interessent-detail-menulist-geschlecht').value=geschlecht;
	if(aktiv=='true')
		document.getElementById('interessent-detail-checkbox-aktiv').checked=true;
	else
		document.getElementById('interessent-detail-checkbox-aktiv').checked=false;
	document.getElementById('interessent-detail-textbox-anzahlderkinder').value=anzahlderkinder;
	document.getElementById('interessent-detail-menulist-staatsbuergerschaft').value=staatsbuergerschaft;
	document.getElementById('interessent-detail-menulist-geburtsnation').value=geburtsnation;
	document.getElementById('interessent-detail-menulist-sprache').value=sprache;
	document.getElementById('interessent-detail-image').src='<?php echo APP_ROOT?>content/bild.php?src=person&person_id='+person_id+'&'+gettimestamp();
	document.getElementById('interessent-detail-textbox-person_id').value=person_id;
		
	//Prestudent Daten holen

	aufmerksamdurch_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aufmerksamdurch_kurzbz" ));
	studiengang_kz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiengang_kz" ));
	berufstaetigkeit_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#berufstaetigkeit_code" ));
	ausbildungcode = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ausbildungcode" ));
	zgv_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgv_code" ));
	zgvort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvort" ));
	zgvdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvdatum" ));
	zgvmaster_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmas_code" ));
	zgvmasterort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmaort" ));
	zgvmasterdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmadatum" ));
	aufnahmeschluessel = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aufnahmeschluessel" ));
	facheinschlberuf = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#facheinschlberuf" ));
	reihungstest_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#reihungstest_id" ));
	anmeldungreihungstest = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmeldungreihungstest" ));
	reihungstestangetreten = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#reihungstestangetreten" ));
	punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));
	bismelden = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bismelden" ));
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	
	document.getElementById('interessent-prestudent-menulist-aufmerksamdurch').value=aufmerksamdurch_kurzbz;
	document.getElementById('interessent-prestudent-menulist-berufstaetigkeit').value=berufstaetigkeit_code;
	document.getElementById('interessent-prestudent-menulist-ausbildung').value=ausbildungcode;
	document.getElementById('interessent-prestudent-menulist-zgvcode').value=zgv_code;
	document.getElementById('interessent-prestudent-textbox-zgvort').value=zgvort;
	document.getElementById('interessent-prestudent-textbox-zgvdatum').value=zgvdatum;
	document.getElementById('interessent-prestudent-menulist-zgvmastercode').value=zgvmaster_code;
	document.getElementById('interessent-prestudent-textbox-zgvmasterort').value=zgvmasterort;
	document.getElementById('interessent-prestudent-textbox-zgvmasterdatum').value=zgvmasterdatum;
	document.getElementById('interessent-prestudent-menulist-aufnahmeschluessel').value=aufnahmeschluessel;
	if(facheinschlberuf=='true')
		document.getElementById('interessent-prestudent-checkbox-facheinschlberuf').checked=true;
	else
		document.getElementById('interessent-prestudent-checkbox-facheinschlberuf').checked=false;
	document.getElementById('interessent-prestudent-menulist-reihungstest').value=reihungstest_id;
	document.getElementById('interessent-prestudent-textbox-anmeldungreihungstest').value=anmeldungreihungstest;
	if(reihungstestangetreten=='true')
		document.getElementById('interessent-prestudent-checkbox-reihungstestangetreten').checked=true;
	else
		document.getElementById('interessent-prestudent-checkbox-reihungstestangetreten').checked=false;
	document.getElementById('interessent-prestudent-textbox-punkte').value=punkte;
	
	if(bismelden=='true')
		document.getElementById('interessent-prestudent-checkbox-bismelden').checked=true;
	else
		document.getElementById('interessent-prestudent-checkbox-bismelden').checked=false;
		
	document.getElementById('interessent-prestudent-textbox-person_id').value=person_id;
	document.getElementById('interessent-prestudent-textbox-prestudent_id').value=prestudent_id;
	document.getElementById('interessent-prestudent-checkbox-new').checked=false;
	document.getElementById('interessent-prestudent-menulist-studiengang_kz').value=studiengang_kz;
	document.getElementById('interessent-prestudent-textbox-anmerkung').value=anmerkung;
		
	rollentree = document.getElementById('interessent-prestudent-tree-rolle');
	url='<?php echo APP_ROOT;?>rdf/prestudentrolle.rdf.php?prestudent_id='+prestudent_id+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = rollentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		rollentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	rollentree.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	rollentree.database.AddDataSource(datasource);
}

// ****
// * De-/Aktiviert die Prestudent Felder
// ****
function InteressentPrestudentDisableFields(val)
{
	document.getElementById('interessent-prestudent-menulist-aufmerksamdurch').disabled=val;
	document.getElementById('interessent-prestudent-menulist-berufstaetigkeit').disabled=val;
	document.getElementById('interessent-prestudent-menulist-ausbildung').disabled=val;
	document.getElementById('interessent-prestudent-menulist-studiengang_kz').disabled=val;
	document.getElementById('interessent-prestudent-menulist-zgvcode').disabled=val;
	document.getElementById('interessent-prestudent-textbox-zgvort').disabled=val;
	document.getElementById('interessent-prestudent-textbox-zgvdatum').disabled=val;
	document.getElementById('interessent-prestudent-menulist-zgvmastercode').disabled=val;
	document.getElementById('interessent-prestudent-textbox-zgvmasterort').disabled=val;
	document.getElementById('interessent-prestudent-textbox-zgvmasterdatum').disabled=val;
	document.getElementById('interessent-prestudent-menulist-aufnahmeschluessel').disabled=val;
	document.getElementById('interessent-prestudent-checkbox-facheinschlberuf').disabled=val;
	document.getElementById('interessent-prestudent-menulist-reihungstest').disabled=val;
	document.getElementById('interessent-prestudent-textbox-anmeldungreihungstest').disabled=val;
	document.getElementById('interessent-prestudent-checkbox-reihungstestangetreten').disabled=val;
	document.getElementById('interessent-prestudent-textbox-punkte').disabled=val;
	document.getElementById('interessent-prestudent-checkbox-bismelden').disabled=val;
	document.getElementById('interessent-prestudent-button-anmeldungreihungstest-heute').disabled=val;
	document.getElementById('interessent-prestudent-button-save').disabled=val;
	document.getElementById('interessent-prestudent-textbox-anmerkung').disabled=val;
}

// ****
// * Speichert die Prestudent Daten
// ****
function InteressentPrestudentSave()
{
	aufmerksamdurch_kurzbz = document.getElementById('interessent-prestudent-menulist-aufmerksamdurch').value;
	berufstaetigkeit_code = document.getElementById('interessent-prestudent-menulist-berufstaetigkeit').value;
	ausbildungcode = document.getElementById('interessent-prestudent-menulist-ausbildung').value;
	zgv_code = document.getElementById('interessent-prestudent-menulist-zgvcode').value;
	zgvort = document.getElementById('interessent-prestudent-textbox-zgvort').value;
	zgvdatum = document.getElementById('interessent-prestudent-textbox-zgvdatum').value;
	zgvmaster_code = document.getElementById('interessent-prestudent-menulist-zgvmastercode').value;
	zgvmasterort = document.getElementById('interessent-prestudent-textbox-zgvmasterort').value;
	zgvmasterdatum = document.getElementById('interessent-prestudent-textbox-zgvmasterdatum').value;
	aufnahmeschluessel = document.getElementById('interessent-prestudent-menulist-aufnahmeschluessel').value;
	facheinschlberuf = document.getElementById('interessent-prestudent-checkbox-facheinschlberuf').checked;
	reihungstest_id = document.getElementById('interessent-prestudent-menulist-reihungstest').value;
	anmeldungreihungstest = document.getElementById('interessent-prestudent-textbox-anmeldungreihungstest').value;
	reihungstestangetreten = document.getElementById('interessent-prestudent-checkbox-reihungstestangetreten').checked;
	punkte = document.getElementById('interessent-prestudent-textbox-punkte').value;
	bismelden = document.getElementById('interessent-prestudent-checkbox-bismelden').checked;
	person_id = document.getElementById('interessent-prestudent-textbox-person_id').value;
	prestudent_id = document.getElementById('interessent-prestudent-textbox-prestudent_id').value;
	neu = document.getElementById('interessent-prestudent-checkbox-new').checked;
	studiengang_kz = document.getElementById('interessent-prestudent-menulist-studiengang_kz').value;
	anmerkung = document.getElementById('interessent-prestudent-textbox-anmerkung').value;
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');
	
	if (neu)
	{
		alert('Fehler! Es wurde versucht einen neuen Prestudenten anzulegen, dies ist aber hier nicht moeglich');
		return false;
	}
	
	req.add('type', 'saveprestudent');
		
	req.add('aufmerksamdurch_kurzbz', aufmerksamdurch_kurzbz);
	req.add('berufstaetigkeit_code', berufstaetigkeit_code);
	req.add('ausbildungcode', ausbildungcode);
	req.add('zgv_code', zgv_code);
	req.add('zgvort', zgvort);
	req.add('zgvdatum', zgvdatum);
	req.add('zgvmas_code', zgvmaster_code);
	req.add('zgvmaort', zgvmasterort);
	req.add('zgvmadatum', zgvmasterdatum);
	req.add('aufnahmeschluessel', aufnahmeschluessel);
	req.add('facheinschlberuf', facheinschlberuf);
	req.add('reihungstest_id', reihungstest_id);
	req.add('anmeldungreihungstest', anmeldungreihungstest);
	req.add('reihungstestangetreten', reihungstestangetreten);
	req.add('punkte', punkte);
	req.add('bismelden', bismelden);
	req.add('person_id', person_id);
	req.add('prestudent_id', prestudent_id);
	req.add('studiengang_kz', studiengang_kz);
	req.add('anmerkung', anmerkung);
		
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
				
		InteressentSelectId=val.dbdml_data;
		InteressentTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Aktuelles Datum in das Textfeld anmeldungreihungstest einfuegen
// ****
function InteressentAnmeldungreihungstestHeute()
{
	var now = new Date();
	var jahr = now.getFullYear();
	
	monat = now.getMonth();
	if(monat<10) monat='0'+monat;
	tag = now.getDate();
	if(tag<10) tag='0'+tag;
	
	document.getElementById('interessent-prestudent-textbox-anmeldungreihungstest').value=jahr+'-'+monat+'-'+tag;
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
	var tree = document.getElementById('interessent-tree');

	//Wenn kein Interessent ausgewaehlt ist, dann abbrechen
	if (tree.currentIndex==-1) return;
	
	//Voraussetzungen pruefen
	anmeldungreihungstest = document.getElementById('interessent-prestudent-textbox-anmeldungreihungstest').value;
	reihungstestangetreten = document.getElementById('interessent-prestudent-checkbox-reihungstestangetreten').checked;
	
	if(anmeldungreihungstest=='')
	{
		alert('Um einen Interessenten zum Bewerber zu machen, muss das Reihungstestdatum gesetzt sein');
		return false;
	}
	
	if(reihungstestangetreten==false)
	{
		alert('Um einen Interessenten zum Bewerber zu machen, muss das Feld "Zum Reihungstest angetreten" gesetzt sein');
		return false;
	}
	
	prestudent_id = document.getElementById('interessent-prestudent-textbox-prestudent_id').value;
	
	//Rolle Bewerber hinzufuegen
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');
		
	req.add('type', 'addrolle');
	
	req.add('prestudent_id', prestudent_id);
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
		
		InteressentSelectId=prestudent_id;
		InteressentTreeDatasource.Refresh(false); //non blocking
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
	var tree = document.getElementById('interessent-tree');

	//Wenn kein Interessent ausgewaehlt ist, dann abbrechen
	if (tree.currentIndex==-1) return;
		
	prestudent_id = document.getElementById('interessent-prestudent-textbox-prestudent_id').value;

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');
		
	req.add('type', 'BewerberZuStudent');
	
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
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		
		InteressentSelectId=prestudent_id;
		InteressentTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}