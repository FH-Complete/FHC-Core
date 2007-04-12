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
var StudentSelectUid=null; //Student der nach dem Refresh markiert werden soll

// ********** Observer und Listener ************* //

// ****
// * Observer fuer Studenten Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Student wieder
// * markiert
// ****
var StudentTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(StudentTreeSelectStudent,10);
  }
};

// ***************** KEY Events ************************* //

// ****
// * Wird ausgefuehrt wenn eine Taste gedrueckt wird und der Focus
// * im Lehrveranstaltungs-tree ist
// * Beim Druecken von ENTF wird der markierte Student geloescht
// * Beim Druecken von F5 wird der Studententree aktualisiert
// ****
function StudentTreeKeyPress(event)
{
	if(event.keyCode==46) // Entf
		StudentDelete();
	else if(event.keyCode==116) // F5
		StudentTreeRefresh();
}

// ****************** FUNKTIONEN ************************** //

// ****
// * Asynchroner (Nicht blockierender) Refresh des StudentenTrees
// ****
function StudentTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//markierten Studenten global speichern damit dieser Student nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('student-tree');
	var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
	try
	{
		StudentSelectUid=tree.view.getCellText(tree.currentIndex,col);
		StudentTreeDatasource.Refresh(false); //non blocking
	}
	catch(e)
	{}
}

// ****
// * neuen Studenten anlegen
// ****
function StudentNeu()
{
	
}

// ****
// * Selectiert den Studenten nachdem der Tree
// * rebuildet wurde.
// ****
function StudentTreeSelectStudent()
{
	var tree=document.getElementById('student-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist der zu selektierende Student gespeichert
	if(StudentSelectUid!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//Uid der row holen
			col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
			uid=tree.view.getCellText(i,col);
						
			if(uid == StudentSelectUid)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	}
	document.getElementById('student-toolbar-label-anzahl').value='Anzahl: '+items;
}

// ****
// * Student loeschen
// ****
function StudentDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1)
		return;

	try
	{
		//Ausgewaehlte Lehreinheit holen
        var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
		var uid=tree.view.getCellText(tree.currentIndex,col);
		if(uid=='')
			return false
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Abfrage ob wirklich geloescht werden soll
	if (confirm('Wollen Sie diesen Studenten wirklich löschen?'))
	{
		//Script zum loeschen der Lehreinheit aufrufen
		var req = new phpRequest('student/studentDBDML.php','','');

		req.add('type','student');
		req.add('do','delete');
		req.add('uid',uid);
		var response = req.executePOST();

		var val =  new ParseReturnValue(response)
		if(!val.dbdml_return)
			alert(val.dbdml_errormsg)

		StudentTreeRefresh();
		StudentDetailReset();
	}
}

// ****
// * Leert alle Eingabe- und Auswahlfelder
// ****
function StudentDetailReset()
{	
	document.getElementById('student-detail-textbox-uid').value='';
	document.getElementById('student-detail-textbox-anrede').value='';
	document.getElementById('student-detail-textbox-titelpre').value='';
	document.getElementById('student-detail-textbox-titelpost').value='';
	document.getElementById('student-detail-textbox-vorname').value='';
	document.getElementById('student-detail-textbox-vornamen').value='';
	document.getElementById('student-detail-textbox-nachname').value='';
	document.getElementById('student-detail-textbox-geburtsdatum').value='';
	document.getElementById('student-detail-textbox-geburtsort').value='';
	document.getElementById('student-detail-textbox-geburtszeit').value='';
	document.getElementById('student-detail-textbox-anmerkung').value='';
	document.getElementById('student-detail-textbox-homepage').value='';
	document.getElementById('student-detail-textbox-svnr').value='';
	document.getElementById('student-detail-textbox-ersatzkennzeichen').value='';
	document.getElementById('student-detail-menulist-familienstand').value='l';
	document.getElementById('student-detail-menulist-geschlecht').value='m';
	document.getElementById('student-detail-checkbox-aktiv').checked=true;
	document.getElementById('student-detail-textbox-anzahlderkinder').value='';
	document.getElementById('student-detail-menulist-staatsbuergerschaft').value='A';
	document.getElementById('student-detail-menulist-geburtsnation').value='A';
	document.getElementById('student-detail-menulist-sprache').value='German';
	document.getElementById('student-detail-textbox-matrikelnummer').value='';
	document.getElementById('student-detail-image').src='';
}

// ****
// * Deaktiviert alle Eingabe- und Auswahlfelder
// ****
function StudentDetailDisableFields(val)
{
	//document.getElementById('student-detail-textbox-uid').disabled=val;
	document.getElementById('student-detail-textbox-anrede').disabled=val;
	document.getElementById('student-detail-textbox-titelpre').disabled=val;
	document.getElementById('student-detail-textbox-titelpost').disabled=val;
	document.getElementById('student-detail-textbox-vorname').disabled=val;
	document.getElementById('student-detail-textbox-vornamen').disabled=val;
	document.getElementById('student-detail-textbox-nachname').disabled=val;
	document.getElementById('student-detail-textbox-geburtsdatum').disabled=val;
	document.getElementById('student-detail-textbox-geburtsort').disabled=val;
	document.getElementById('student-detail-textbox-geburtszeit').disabled=val;
	document.getElementById('student-detail-textbox-anmerkung').disabled=val;
	document.getElementById('student-detail-textbox-homepage').disabled=val;
	document.getElementById('student-detail-textbox-svnr').disabled=val;
	document.getElementById('student-detail-textbox-ersatzkennzeichen').disabled=val;
	document.getElementById('student-detail-menulist-familienstand').disabled=val;
	document.getElementById('student-detail-menulist-geschlecht').disabled=val;
	document.getElementById('student-detail-checkbox-aktiv').disabled=val;
	document.getElementById('student-detail-textbox-anzahlderkinder').disabled=val;
	document.getElementById('student-detail-menulist-staatsbuergerschaft').disabled=val;
	document.getElementById('student-detail-menulist-geburtsnation').disabled=val;
	document.getElementById('student-detail-menulist-sprache').disabled=val;
	document.getElementById('student-detail-textbox-matrikelnummer').disabled=val;
	document.getElementById('student-detail-button-image-upload').disabled=val;
	document.getElementById('student-detail-textbox-studiengang_kz').disabled=val;
	document.getElementById('student-detail-textbox-semester').disabled=val;
	document.getElementById('student-detail-textbox-verband').disabled=val;
	document.getElementById('student-detail-textbox-gruppe').disabled=val;
}

// ****
// * Speichert die Details
// ****
function StudentDetailSave()
{
	//Werte holen
	uid = document.getElementById('student-detail-textbox-uid').value;
	anrede = document.getElementById('student-detail-textbox-anrede').value;
	titelpre = document.getElementById('student-detail-textbox-titelpre').value;
	titelpost = document.getElementById('student-detail-textbox-titelpost').value;
	vorname = document.getElementById('student-detail-textbox-vorname').value;
	vornamen = document.getElementById('student-detail-textbox-vornamen').value;
	nachname = document.getElementById('student-detail-textbox-nachname').value;
	geburtsdatum = document.getElementById('student-detail-textbox-geburtsdatum').value;
	geburtsort = document.getElementById('student-detail-textbox-geburtsort').value;
	geburtszeit = document.getElementById('student-detail-textbox-geburtszeit').value;
	anmerkung = document.getElementById('student-detail-textbox-anmerkung').value;
	homepage = document.getElementById('student-detail-textbox-homepage').value;
	svnr = document.getElementById('student-detail-textbox-svnr').value;
	ersatzkennzeichen = document.getElementById('student-detail-textbox-ersatzkennzeichen').value;
	familienstand = document.getElementById('student-detail-menulist-familienstand').value;
	geschlecht = document.getElementById('student-detail-menulist-geschlecht').value;
	aktiv = document.getElementById('student-detail-checkbox-aktiv').checked;
	anzahlderkinder = document.getElementById('student-detail-textbox-anzahlderkinder').value;
	staatsbuergerschaft = document.getElementById('student-detail-menulist-staatsbuergerschaft').value;
	geburtsnation = document.getElementById('student-detail-menulist-geburtsnation').value;
	sprache = document.getElementById('student-detail-menulist-sprache').value;
	matrikelnummer = document.getElementById('student-detail-textbox-matrikelnummer').value;
	studiengang_kz = document.getElementById('student-detail-textbox-studiengang_kz').value;
	semester = document.getElementById('student-detail-textbox-semester').value;
	verband = document.getElementById('student-detail-textbox-verband').value;
	gruppe = document.getElementById('student-detail-textbox-gruppe').value;
		
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');
	neu = document.getElementById('student-detail-checkbox-new').checked;

	if (neu)
	{
		req.add('do','create');
	}
	else
	{
		req.add('do','update');
	}
	
	req.add('type', 'savestudent');
	
	req.add('uid', uid);
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
	req.add('matrikelnummer', matrikelnummer);
	req.add('studiengang_kz', studiengang_kz);
	req.add('semester', semester);
	req.add('verband', verband);
	req.add('gruppe', gruppe);
	
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)
	
	if (!val.dbdml_return)
	{
		alert(val.dbdml_errormsg)
	}
	else
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-detail-checkbox-new').checked=false;
		
		StudentSelectUid=val.dbdml_data;
		StudentTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

function StudentImageUpload()
{
	person_id = document.getElementById('student-detail-textbox-person_id').value;
	if(person_id!='')
	{
		window.open('<?php echo APP_ROOT; ?>content/bildupload.php?person_id='+person_id,'Bild Upload', 'height=10,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
	}
	else
		alert('Es wurde keine Person ausgewaehlt');
}

// ****
// * Auswahl eines Studenten
// * bei Auswahl eines Studenten wird dieser geladen
// * und die Daten unten angezeigt
// ****
function StudentAuswahl()
{
	
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1) return;
	
	try
	{
		//Ausgewaehlte UID holen
        var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
		var uid=tree.view.getCellText(tree.currentIndex,col);
		if(uid!='')
		{
			//Student wurde markiert
			//loeschen button aktivieren
			StudentDetailDisableFields(false);
			document.getElementById('student-detail-button-save').disabled=false;
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

	var url = '<?php echo APP_ROOT ?>rdf/student.rdf.php?uid='+uid+'&'+gettimestamp();
	
	//hier wird GetDataSourceBlocking verwendet da sich
	//bei der Methode mit phpRequest der Mozilla aufhaengt
	//var req = new phpRequest(url,'','');
	//req.add('uid',uid);

	//var response = req.execute();
	
	// Datasource holen
	//var dsource=parseRDFString(response, 'http://www.technikum-wien.at/student/alle');

	//dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
    
    var dsource = rdfService.GetDataSourceBlocking(url);
    
	var subject = rdfService.GetResource("http://www.technikum-wien.at/student/" + uid);

	var predicateNS = "http://www.technikum-wien.at/student/rdf";

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
	anmerkung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	homepage=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#homepage" ));
	svnr=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#svnr" ));
	ersatzkennzeichen=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ersatzkennzeichen" ));
	familienstand=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#familienstand" ));
	geschlecht=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geschlecht" ));
	aktiv=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aktiv" ));	
	anzahlderkinder=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anzahlderkinder" ));
	staatsbuergerschaft=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#staatsbuergerschaft" ));
	geburtsnation=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geburtsnation" ));
	sprache=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#sprache" ));
	matrikelnummer=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#matrikelnummer" ));
	person_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	studiengang_kz=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiengang_kz" ));
	semester=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#semester" ));
	verband=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#verband" ));
	gruppe=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gruppe" ));
	
	//Daten den Feldern zuweisen

	document.getElementById('student-detail-textbox-uid').value=uid;
	document.getElementById('student-detail-textbox-anrede').value=anrede;
	document.getElementById('student-detail-textbox-titelpre').value=titelpre;
	document.getElementById('student-detail-textbox-titelpost').value=titelpost;
	document.getElementById('student-detail-textbox-vorname').value=vorname;
	document.getElementById('student-detail-textbox-vornamen').value=vornamen;
	document.getElementById('student-detail-textbox-nachname').value=nachname;
	document.getElementById('student-detail-textbox-geburtsdatum').value=geburtsdatum;
	document.getElementById('student-detail-textbox-geburtsort').value=geburtsort;
	document.getElementById('student-detail-textbox-geburtszeit').value=geburtszeit;
	document.getElementById('student-detail-textbox-anmerkung').value=anmerkung;
	document.getElementById('student-detail-textbox-homepage').value=homepage;
	document.getElementById('student-detail-textbox-svnr').value=svnr;
	document.getElementById('student-detail-textbox-ersatzkennzeichen').value=ersatzkennzeichen;
	document.getElementById('student-detail-menulist-familienstand').value=familienstand;
	document.getElementById('student-detail-menulist-geschlecht').value=geschlecht;
	if(aktiv=='true')
		document.getElementById('student-detail-checkbox-aktiv').checked=true;
	else
		document.getElementById('student-detail-checkbox-aktiv').checked=false;
	document.getElementById('student-detail-textbox-anzahlderkinder').value=anzahlderkinder;
	document.getElementById('student-detail-menulist-staatsbuergerschaft').value=staatsbuergerschaft;
	document.getElementById('student-detail-menulist-geburtsnation').value=geburtsnation;
	document.getElementById('student-detail-menulist-sprache').value=sprache;
	document.getElementById('student-detail-textbox-matrikelnummer').value=matrikelnummer;
	document.getElementById('student-detail-image').src='<?php echo APP_ROOT?>content/bild.php?src=person&person_id='+person_id+'&'+gettimestamp();
	document.getElementById('student-detail-textbox-person_id').value=person_id;
	document.getElementById('student-detail-textbox-studiengang_kz').value=studiengang_kz;
	document.getElementById('student-detail-textbox-semester').value=semester;
	document.getElementById('student-detail-textbox-verband').value=verband;
	document.getElementById('student-detail-textbox-gruppe').value=gruppe;
}