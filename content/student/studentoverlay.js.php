<?php
/* Copyright (C) 2006 fhcomplete.org
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
require_once('../../include/functions.inc.php');
require_once('../../include/variable.class.php');

$user = get_uid();

$variable = new variable();
$variable->loadVariables($user);

// Syntaxhighlighting in IDE
if(false): ?> <script type="text/javascript"><?php endif; ?>

// *********** Globale Variablen *****************//
var StudentSelectID=null; //Student der nach dem Refresh markiert werden soll
var StudentKontoSelectBuchung=null; //Buchung die nach dem Refresh markiert werden soll
var StudentKontoTreeDatasource; //Datasource des KontoTrees
var StudentTreeLoadDataOnSelect=true; //Gib an ob beim Selectieren im Tree die Daten geladen werden sollen
var StudentTreeLoadDataOnSelect2=true; //Gib an ob beim Selectieren im Tree die Daten geladen werden sollen
var StudentIOTreeDatasource; //Datasource des Incomming/Outgoing Trees
var StudentIOSelectID=null; //BISIO Eintrag der nach dem Refresh markiert werden soll
var StudentNotenTreeDatasource; //Datasource des Noten Trees
var StudentNotenSelectLehrveranstaltungID=null; //LehreinheitID des Noten Eintrages der nach dem Refresh markiert werden soll
var StudentLvGesamtNotenTreeDatasource; //Datasource des Noten Trees
var StudentLvGesamtNotenSelectLehrveranstaltungID=null; //LehreinheitID des Noten Eintrages der nach dem Refresh markiert werden soll
var StudentPruefungTreeDatasource; //Datasource des Pruefung Trees
var StudentPruefungSelectID=null; //ID der Pruefung die nach dem Refresh markiert werden soll
var StudentDetailRolleTreeDatasource=null; //Datasource fuer denn PrestudentRolleTree
var StudentAkteTreeDatasource=null;
var doublerebuildkonto='false';
var StudentNotenTreeloaded=false;
var StudentGesamtNotenTreeloaded=false;
// ********** Observer und Listener ************* //

// ****
// * Observer fuer Studenten Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentTreeSinkObserver =
{
	onBeginLoad : function(pSink)
	{
		StudentTreeLoadDataOnSelect2=false;
	},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) { debug('Error StudentTreeSinkObserver:'+pError+':'+pStatus); },
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
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
 		//timeout nur bei Mozilla notwendig da sonst die rows
 		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		StudentTreeLoadDataOnSelect2=true;
		window.setTimeout(StudentTreeSelectStudent,10);
		// Progressmeter stoppen
		document.getElementById('statusbar-progressmeter').setAttribute('mode','determined');
	}
};

// ****
// * Observer fuer PrestudentRolleTree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentDetailRolleTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) { debug('Error StudentDetailRolleTreeSinkObserver:'+pError+':'+pStatus); },
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-prestudent-tree-rolle').builder.rebuild();
	}
};

// ****
// * Observer fuer Konto Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentKontoTreeSinkObserver =
{
	onBeginLoad : function(pSink) 
	{
		tree = document.getElementById('student-konto-tree');
		tree.removeEventListener('select', StudentKontoAuswahl, false);
	},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) { debug('Error StudentKontoTreeSinkObserver:'+pError+':'+pStatus); },
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-konto-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Buchung wieder
// * markiert
// ****
var StudentKontoTreeListener =
{
	willRebuild : function(builder) {  },
	didRebuild : function(builder)
	{
  		tree = document.getElementById('student-konto-tree');
		tree.addEventListener('select', StudentKontoAuswahl, false);
		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		window.setTimeout(StudentKontoTreeSelectBuchung,10);
	}
};

// ****
// * Observer fuer BISIO Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentIOTreeSinkObserver =
{
	onBeginLoad : function(pSink) 
	{
		tree = document.getElementById('student-io-tree');
		tree.removeEventListener('select', StudentIOAuswahl, false);
	},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-io-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var StudentIOTreeListener =
{
	willRebuild : function(builder) {  },
	didRebuild : function(builder)
	{
  		tree = document.getElementById('student-io-tree');
		tree.addEventListener('select', StudentIOAuswahl, false);
		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		window.setTimeout(StudentIOTreeSelectID,10);
	}
};


// ****
// * Observer fuer Noten Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentNotenTreeSinkObserver =
{
	onBeginLoad : function(pSink) 
	{
		StudentNotenTreeloaded=false;
		tree = document.getElementById('student-noten-tree');
		tree.removeEventListener('select', StudentNotenAuswahl, false);
	},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-noten-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var StudentNotenTreeListener =
{
	willRebuild : function(builder) {  },
	didRebuild : function(builder)
	{
		tree = document.getElementById('student-noten-tree');
		tree.addEventListener('select', StudentNotenAuswahl, false);
		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		StudentNotenTreeloaded=true;
		window.setTimeout(StudentNotenTreeSelectID,10);
	}
};

// ****
// * Observer fuer LvGesamtNoten Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentLvGesamtNotenTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		StudentGesamtNotenTreeloaded=false;
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-lvgesamtnoten-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var StudentLvGesamtNotenTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
  	  StudentGesamtNotenTreeloaded=true;
      window.setTimeout(StudentLvGesamtNotenTreeSelectID,10);
  }
};

// ****
// * Observer fuer Pruefung Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentPruefungTreeSinkObserver =
{
	onBeginLoad : function(pSink) 
	{
		tree = document.getElementById('student-pruefung-tree');
		tree.removeEventListener('select', StudentPruefungAuswahl, false);
	},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-pruefung-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var StudentPruefungTreeListener =
{
	willRebuild : function(builder) {  },
	didRebuild : function(builder)
	{
  		tree = document.getElementById('student-pruefung-tree');
		tree.addEventListener('select', StudentPruefungAuswahl, false);
		
		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		window.setTimeout(StudentPruefungTreeSelectID,10);
	}
};

// ****
// * Observer fuer Akte Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentAkteTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-zeugnis-tree').builder.rebuild();
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
// * Erstellt das Zertifikat fuer die Freifaecher
// ****
function StudentFFZertifikatPrint()
{
//	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-noten-tree');
			
	col = tree.columns ? tree.columns["student-noten-tree-student_uid"] : "student-noten-tree-student_uid";
	uid = tree.view.getCellText(tree.currentIndex,col);
	
	col = tree.columns ? tree.columns["student-noten-tree-lehrveranstaltung_id"] : "student-noten-tree-lehrveranstaltung_id";
	lvid = tree.view.getCellText(tree.currentIndex,col);
	
	col = tree.columns ? tree.columns["student-noten-tree-studiensemester_kurzbz"] : "student-noten-tree-studiensemester_kurzbz";
	stsem = tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["student-noten-tree-studiengang_kz"] : "student-noten-tree-studiengang_kz";
	stg_kz = tree.view.getCellText(tree.currentIndex,col);

	url =  '<?php echo APP_ROOT; ?>content/pdfExport.php?xml=zertifikat.rdf.php&xsl=Zertifikat&stg_kz='+stg_kz+'&uid=;'+uid+'&ss='+stsem+'&lvid='+lvid+'&'+gettimestamp();
	
//	alert('url: '+url);
	window.location.href = url;
}

//****
//* Erstellt ein Lehrveranstaltungszeugnis fuer die LV
//****
function StudentLVZeugnisPrint()
{
//	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-noten-tree');
			
	col = tree.columns ? tree.columns["student-noten-tree-student_uid"] : "student-noten-tree-student_uid";
	uid = tree.view.getCellText(tree.currentIndex,col);
	
	col = tree.columns ? tree.columns["student-noten-tree-lehrveranstaltung_id"] : "student-noten-tree-lehrveranstaltung_id";
	lvid = tree.view.getCellText(tree.currentIndex,col);
	
	col = tree.columns ? tree.columns["student-noten-tree-studiensemester_kurzbz"] : "student-noten-tree-studiensemester_kurzbz";
	stsem = tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["student-noten-tree-studiengang_kz"] : "student-noten-tree-studiengang_kz";
	stg_kz = tree.view.getCellText(tree.currentIndex,col);

	url =  '<?php echo APP_ROOT; ?>content/pdfExport.php?xml=lehrveranstaltungszeugnis.rdf.php&xsl=LVZeugnis&stg_kz='+stg_kz+'&uid=;'+uid+'&ss='+stsem+'&lvid='+lvid+'&'+gettimestamp();
	
	window.location.href = url;
}

// ****
// * Asynchroner (Nicht blockierender) Refresh des StudentenTrees
// ****
function StudentTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	//markierten Studenten global speichern damit dieser Student nach dem
	//refresh wieder markiert werden kann.
	var tree = document.getElementById('student-tree');
	var col = tree.columns ? tree.columns["student-treecol-prestudent_id"] : "student-treecol-prestudent_id";
	try
	{
		StudentSelectID=tree.view.getCellText(tree.currentIndex,col);
	}
	catch(e)
	{
		StudentSelectID=null;
	}
	StudentTreeDatasource.Refresh(false); //non blocking
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
	if(StudentSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//Uid der row holen
			col = tree.columns ? tree.columns["student-treecol-prestudent_id"] : "student-treecol-prestudent_id";
			prestudent_id=tree.view.getCellText(i,col);

			if(prestudent_id == StudentSelectID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				document.getElementById('student-toolbar-label-anzahl').value='Anzahl: '+items;
				return true;
			}
	   	}
	}

	document.getElementById('student-toolbar-label-anzahl').value='Anzahl: '+items;
}

// ****
// * Beim Sortieren des Trees wird der markierte Eintrag gespeichert und nach dem sortieren
// * wieder markiert.
// ****
function StudentTreeSort()
{
	var i;
	var tree=document.getElementById('student-tree');
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
		i = 0;
	col = tree.columns ? tree.columns["student-treecol-prestudent_id"] : "student-treecol-prestudent_id";
	StudentSelectID = tree.view.getCellText(i,col);
	StudentTreeLoadDataOnSelect=false;
	window.setTimeout("StudentTreeSelectStudent()",10);
}

// ****
// * Loescht einen Studenten aus einer Spezialgruppe
// ****
function StudentGruppeDel()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');
	var tree_vb = document.getElementById('tree-verband');

	if (tree.currentIndex==-1)
		return;

	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;
	var uids='';
	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
			uid = ';'+tree.view.getCellText(v,col);
			uids = uids + uid;
			anzahl++;
		}
	}

	try
	{
		//Ausgewaehlte Gruppe holen
		var gruppe_kurzbz = '';
		try
		{
        	var col = tree_vb.columns ? tree_vb.columns["gruppe"] : "gruppe";
			var gruppe_kurzbz=tree_vb.view.getCellText(tree_vb.currentIndex,col);
		}
		catch(e)
		{}
		
		if(gruppe_kurzbz=='')
		{
			alert('Studenten koennen nur aus Spezialgruppen entfernt werden');
			return false
		}
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	//Abfrage ob wirklich geloescht werden soll
	if (confirm(anzahl+' Student(en) wirklich aus Gruppe '+gruppe_kurzbz+' entfernen?'))
	{
		//Script zum loeschen aufrufen
		var req = new phpRequest('student/studentDBDML.php','','');

		req.add('type','deleteGruppenzuteilung');
		req.add('uid',uids);
		req.add('gruppe_kurzbz', gruppe_kurzbz);

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
	document.getElementById('student-detail-textbox-matr_nr').value=''; 
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
	//document.getElementById('student-detail-textbox-matrikelnummer').disabled=val;
	document.getElementById('student-detail-button-image-upload').disabled=val;
	document.getElementById('student-detail-button-image-delete').disabled=val;
	document.getElementById('student-detail-button-image-infomail').disabled=val;
	//document.getElementById('student-detail-menulist-studiengang_kz').disabled=val;
	document.getElementById('student-detail-textbox-semester').disabled=val;
	document.getElementById('student-detail-textbox-verband').disabled=val;
	document.getElementById('student-detail-textbox-gruppe').disabled=val;
	document.getElementById('student-detail-textbox-alias').disabled=val;
	document.getElementById('student-detail-button-save').disabled=val;
	document.getElementById('student-detail-textbox-matr_nr').disabled=val; 
}

// ****
// * Speichert die Details
// ****
function StudentDetailSave()
{
	//Werte holen
	person_id = document.getElementById('student-detail-textbox-person_id').value;
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
	studiengang_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;
	semester = document.getElementById('student-detail-textbox-semester').value;
	verband = document.getElementById('student-detail-textbox-verband').value;
	gruppe = document.getElementById('student-detail-textbox-gruppe').value;
	alias = document.getElementById('student-detail-textbox-alias').value;
	matr_nr = document.getElementById('student-detail-textbox-matr_nr').value; 

	//Wenn es noch kein Student ist, dann wird die Studiengang_kz vom Prestudent genommen
	if(studiengang_kz=='')
		studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
		
	if(geburtsdatum!='' && !CheckDatum(geburtsdatum))
	{
		alert('Geburtsdatum ist ungueltig');
		return false;
	}
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

	if(uid=='')
		req.add('type', 'saveperson');
	else
		req.add('type', 'savestudent');

	req.add('person_id', person_id);
	req.add('uid', uid);
	req.add('anrede', anrede);
	req.add('titelpre', titelpre);
	req.add('titelpost', titelpost);
	req.add('vorname', vorname);
	req.add('vornamen', vornamen);
	req.add('nachname', nachname);
	req.add('geburtsdatum', ConvertDateToISO(geburtsdatum));
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
	req.add('alias', alias);
	req.add('matr_nr',matr_nr); 

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
		document.getElementById('student-detail-checkbox-new').checked=false;

		StudentSelectID=document.getElementById('student-prestudent-textbox-prestudent_id').value;
		StudentTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Ladt ein Script zum Upload des Bildes
// ****
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
// * Loescht das Bild aus der DB
// ****
function StudentImageDelete()
{
	person_id = document.getElementById('student-detail-textbox-person_id').value;
	if(person_id!='')
	{
		var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
		var req = new phpRequest(url,'','');
		var studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
		
		req.add('type', 'imagedelete');
		req.add('person_id', person_id);
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
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			StudentSelectID=document.getElementById('student-prestudent-textbox-prestudent_id').value;
			StudentTreeDatasource.Refresh(false); //non blocking
			SetStatusBarText('Bild wurde geloescht');
		}
	}
	else
		alert('Es wurde keine Person ausgewaehlt');
}
function StudentImageInfomail()
{
	var uid = document.getElementById('student-detail-textbox-uid').value;
	var nachname = document.getElementById('student-detail-textbox-nachname').value;
	var anrede = document.getElementById('student-detail-textbox-anrede').value;
	var sg='';
	if(anrede=='Frau')
		sg = 'Sehr geehrte';
	else
		sg = 'Sehr geehrter';
	
	if(uid=='')
	{	
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');
	
		var person_id = document.getElementById('student-detail-textbox-person_id').value;	
		req.add('type', 'getprivatemailadress');
		req.add('person_ids', person_id);
		
		var response = req.executePOST();
	
		var val =  new ParseReturnValue(response)
		email = val.dbdml_data;
	}
	else
	{
		email = uid+"@<?php echo DOMAIN;?>";
	}
	
	if(email!='')
	{
		body=sg+" "+anrede+" "+nachname+"!%0A%0AIhr Profilbild wurde entfernt, da es nicht den aktuellen Bildrichtlinen entspricht.%0ABitte laden Sie unter CIS->Profil ein neues Profilbild hoch.";
		window.location.href="mailto:"+email+"?subject=Profilbild&body="+body;
	}
	else
	{
		alert('E-Mail konnte nicht ermittelt werden');
	}
}

// ****
// * Auswahl eines Studenten
// * bei Auswahl eines Studenten wird dieser geladen
// * und die Daten unten angezeigt
// ****
function StudentAuswahl()
{
	if(!StudentTreeLoadDataOnSelect)
	{
		StudentTreeLoadDataOnSelect=true;
		return true;
	}
	
	if(!StudentTreeLoadDataOnSelect2)
		return true;

	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1) return;

	try
	{
		//Ausgewaehlte prestudent_id holen
		var prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', tree.currentIndex);
		
		if(prestudent_id!='')
		{
			//Student wurde markiert
			//loeschen button aktivieren
			StudentDetailDisableFields(false);
			StudentPrestudentDisableFields(false);
			StudentKontoDisableFields(false);
			StudentAkteDisableFields(false);
			StudentIODisableFields(false);
			StudentNoteDisableFields(false);
			document.getElementById('student-detail-button-save').disabled=false;
			StudentPruefungDisableFileds(false);
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
	StudentFunktionIFrameUnLoad();
	
	StudentNotenTreeloaded=false;
	StudentGesamtNotenTreeloaded=false;
	
	stsem = getStudiensemester();
	var url = '<?php echo APP_ROOT ?>rdf/student.rdf.php?prestudent_id='+prestudent_id+'&studiensemester_kurzbz='+stsem+'&'+gettimestamp();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/student/" + prestudent_id);

	var predicateNS = "http://www.technikum-wien.at/student/rdf";

	//Daten holen

	uid = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#uid" ));
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
	matrikelnummer=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#matrikelnummer" ));
	person_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	studiengang_kz_student=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiengang_kz_student" ));
	semester=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#semester" ));
	verband=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#verband" ));
	gruppe=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gruppe" ));
	prestudent_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#prestudent_id" ));
	status=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#status" ));
	alias=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#alias" ));
	matr_nr=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#matr_nr" ));
	
	//Bei Incoming wird das Menue zur Statusaenderung deaktiviert
	if(status=='Incoming')
		document.getElementById('student-toolbar-status').disabled=true;
	else
		document.getElementById('student-toolbar-status').disabled=false;

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
	document.getElementById('student-detail-menulist-studiengang_kz').value=studiengang_kz_student;
	document.getElementById('student-detail-textbox-semester').value=semester;
	document.getElementById('student-detail-textbox-verband').value=verband;
	document.getElementById('student-detail-textbox-gruppe').value=gruppe;
	document.getElementById('student-detail-textbox-person_id').value = person_id;
	document.getElementById('student-detail-textbox-alias').value=alias;
	document.getElementById('student-detail-textbox-matr_nr').value=matr_nr; 

	//PreStudent Daten holen

	aufmerksamdurch_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aufmerksamdurch_kurzbz" ));
	studiengang_kz_prestudent = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiengang_kz_prestudent" ));
	berufstaetigkeit_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#berufstaetigkeit_code" ));
	ausbildungcode = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ausbildungcode" ));
	zgv_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgv_code" ));
	zgvort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvort" ));
	zgvdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvdatum" ));
	zgvmaster_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmas_code" ));
	zgvmasterort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmaort" ));
	zgvmasterdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmadatum" ));
	ausstellungsstaat = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ausstellungsstaat" ));
	aufnahmeschluessel = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aufnahmeschluessel" ));
	facheinschlberuf = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#facheinschlberuf" ));
	reihungstest_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#reihungstest_id" ));
	anmeldungreihungstest = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmeldungreihungstest" ));
	reihungstestangetreten = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#reihungstestangetreten" ));
	punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));
	punkte1 = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte1" ));
	punkte2 = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte2" ));
	punkte3 = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte3" ));
	bismelden = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bismelden" ));
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkungpre" ));
	mentor = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#mentor" ));
	dual = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#dual" ));

	document.getElementById('student-prestudent-menulist-aufmerksamdurch').value=aufmerksamdurch_kurzbz;
	document.getElementById('student-prestudent-menulist-berufstaetigkeit').value=berufstaetigkeit_code;
	document.getElementById('student-prestudent-menulist-ausbildung').value=ausbildungcode;
	document.getElementById('student-prestudent-menulist-zgvcode').value=zgv_code;
	document.getElementById('student-prestudent-textbox-zgvort').value=zgvort;
	document.getElementById('student-prestudent-textbox-zgvdatum').value=zgvdatum;
	document.getElementById('student-prestudent-menulist-zgvmastercode').value=zgvmaster_code;
	document.getElementById('student-prestudent-textbox-zgvmasterort').value=zgvmasterort;
	document.getElementById('student-prestudent-textbox-zgvmasterdatum').value=zgvmasterdatum;
	document.getElementById('student-prestudent-menulist-ausstellungsstaat').value=ausstellungsstaat;
	document.getElementById('student-prestudent-menulist-aufnahmeschluessel').value=aufnahmeschluessel;
	if(facheinschlberuf=='true')
		document.getElementById('student-prestudent-checkbox-facheinschlberuf').checked=true;
	else
		document.getElementById('student-prestudent-checkbox-facheinschlberuf').checked=false;

	document.getElementById('student-prestudent-textbox-anmeldungreihungstest').value=anmeldungreihungstest;
	if(reihungstestangetreten=='true')
		document.getElementById('student-prestudent-checkbox-reihungstestangetreten').checked=true;
	else
		document.getElementById('student-prestudent-checkbox-reihungstestangetreten').checked=false;
	document.getElementById('student-prestudent-textbox-punkte').value=punkte;
	document.getElementById('student-prestudent-textbox-punkte1').value=punkte1;
	document.getElementById('student-prestudent-textbox-punkte2').value=punkte2;
	document.getElementById('student-prestudent-textbox-punkte3').value=punkte3;

	if(bismelden=='true')
		document.getElementById('student-prestudent-checkbox-bismelden').checked=true;
	else
		document.getElementById('student-prestudent-checkbox-bismelden').checked=false;
		
	if(dual=='true')
		document.getElementById('student-prestudent-checkbox-dual').checked=true;
	else
		document.getElementById('student-prestudent-checkbox-dual').checked=false;

	document.getElementById('student-prestudent-textbox-person_id').value=person_id;
	document.getElementById('student-prestudent-textbox-prestudent_id').value=prestudent_id;
	document.getElementById('student-prestudent-checkbox-new').checked=false;
	document.getElementById('student-prestudent-menulist-studiengang_kz').value=studiengang_kz_prestudent;
	
	document.getElementById('student-prestudent-textbox-anmerkung').value=anmerkung;
	document.getElementById('student-prestudent-textbox-mentor').value=mentor;

	document.getElementById('student-detail-groupbox-caption').label='Zugangsvoraussetzung für '+nachname+' '+vorname;
	rollentree = document.getElementById('student-prestudent-tree-rolle');
	url='<?php echo APP_ROOT;?>rdf/prestudentrolle.rdf.php?prestudent_id='+prestudent_id+"&"+gettimestamp();

	try
	{
		StudentDetailRolleTreeDatasource.removeXMLSinkObserver(StudentDetailRolleTreeSinkObserver);
	}
	catch(e)
	{}
	
	//Alte DS entfernen
	var oldDatasources = rollentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		rollentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	rollentree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentDetailRolleTreeDatasource = rdfService.GetDataSource(url);
	StudentDetailRolleTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentDetailRolleTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	rollentree.database.AddDataSource(StudentDetailRolleTreeDatasource);
	StudentDetailRolleTreeDatasource.addXMLSinkObserver(StudentDetailRolleTreeSinkObserver);

	// Reihungstest DropDown
	var reihungstestmenulist = document.getElementById('student-prestudent-menulist-reihungstest');
	var url="<?php echo APP_ROOT ?>rdf/reihungstest.rdf.php?optional=true&include_id="+reihungstest_id+"&studiengang_kz="+studiengang_kz_prestudent;
	
	//Alte DS entfernen
	var oldDatasources = reihungstestmenulist.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		reihungstestmenulist.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	reihungstestmenulist.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var myDatasource = rdfService.GetDataSourceBlocking(url);
	reihungstestmenulist.database.AddDataSource(myDatasource);
	reihungstestmenulist.builder.rebuild();
	document.getElementById('student-prestudent-menulist-reihungstest').value=reihungstest_id;
	
	if(uid=='')
	{
		//PRESTUDENT
		
		//Wenn keine UID gesetzt ist, dann ist er noch kein Student.
		//Hierbei werden einige der Tabs nicht angezeigt und auch nicht geladen!
		
		document.getElementById('student-tab-zeugnis').collapsed=true;
		document.getElementById('student-tab-betriebsmittel').collapsed=true;
		document.getElementById('student-tab-io').collapsed=true;
		document.getElementById('student-tab-noten').collapsed=true;
		document.getElementById('student-tab-pruefung').collapsed=true;
		document.getElementById('student-tab-abschlusspruefung').collapsed=true;
		document.getElementById('student-tab-projektarbeit').collapsed=true;
		document.getElementById('student-tab-gruppen').collapsed=true;
		document.getElementById('student-tab-funktionen').collapsed=true;
		document.getElementById('student-detail-groupbox-student').hidden=true;		
		
		document.getElementById('student-toolbar-abbrecher').hidden=true;
		document.getElementById('student-toolbar-unterbrecher').hidden=true;
		document.getElementById('student-toolbar-student').hidden=true;
		document.getElementById('student-toolbar-diplomand').hidden=true;
		document.getElementById('student-toolbar-absolvent').hidden=true;
		
		document.getElementById('interessent-toolbar-zubewerber').hidden=false;
		document.getElementById('interessent-toolbar-zustudent').hidden=false;
		document.getElementById('interessent-toolbar-aufgenommener').hidden=false;
		document.getElementById('interessent-toolbar-warteliste').hidden=false;
		document.getElementById('interessent-toolbar-absage').hidden=false;
		
		//Wenn ein Tab markiert ist der nun ausgeblendet wurde, 
		//dann wird der Detail Tab markiert
		if(document.getElementById('student-content-tabs').selectedItem.collapsed)
		{
			document.getElementById('student-content-tabs').selectedItem=document.getElementById('student-tab-detail');
		}
	}
	else
	{
		//STUDENT
		document.getElementById('student-tab-zeugnis').collapsed=false;
		document.getElementById('student-tab-betriebsmittel').collapsed=false;
		document.getElementById('student-tab-io').collapsed=false;
		document.getElementById('student-tab-noten').collapsed=false;
		document.getElementById('student-tab-pruefung').collapsed=false;
		document.getElementById('student-tab-abschlusspruefung').collapsed=false;
		document.getElementById('student-tab-projektarbeit').collapsed=false;
		document.getElementById('student-tab-gruppen').collapsed=false;
		document.getElementById('student-tab-funktionen').collapsed=false;
		document.getElementById('student-detail-groupbox-student').hidden=false;
		
		document.getElementById('student-toolbar-abbrecher').hidden=false;
		document.getElementById('student-toolbar-unterbrecher').hidden=false;
		document.getElementById('student-toolbar-student').hidden=false;
		document.getElementById('student-toolbar-diplomand').hidden=false;
		document.getElementById('student-toolbar-absolvent').hidden=false;
		
		document.getElementById('interessent-toolbar-zubewerber').hidden=true;
		document.getElementById('interessent-toolbar-zustudent').hidden=true;
		document.getElementById('interessent-toolbar-aufgenommener').hidden=true;
		document.getElementById('interessent-toolbar-warteliste').hidden=true;
		document.getElementById('interessent-toolbar-absage').hidden=true;
	}
	
	// *** Dokumente *** //
	//Dokumente
	//linker Tree
	doctree = document.getElementById('interessent-dokumente-tree-nichtabgegeben');
	url='<?php echo APP_ROOT;?>rdf/dokument.rdf.php?studiengang_kz='+studiengang_kz_prestudent+'&prestudent_id='+prestudent_id+"&"+gettimestamp();

	try
	{
		InteressentDokumentTreeNichtabgegebenDatasource.removeXMLSinkObserver(InteressentDokumentTreeNichtabgegebenSinkObserver);
		doctree.builder.removeListener(InteressentDokumentTreeNichtabgegebenListener);
	}
	catch(e)
	{}
	
	//Alte DS entfernen
	var oldDatasources = doctree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		doctree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	doctree.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	InteressentDokumentTreeNichtabgegebenDatasource = rdfService.GetDataSource(url);
	InteressentDokumentTreeNichtabgegebenDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	InteressentDokumentTreeNichtabgegebenDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	doctree.database.AddDataSource(InteressentDokumentTreeNichtabgegebenDatasource);
	InteressentDokumentTreeNichtabgegebenDatasource.addXMLSinkObserver(InteressentDokumentTreeNichtabgegebenSinkObserver);
	doctree.builder.addListener(InteressentDokumentTreeNichtabgegebenListener);

	//rechter Tree
	doctree = document.getElementById('interessent-dokumente-tree-abgegeben');
	url='<?php echo APP_ROOT;?>rdf/dokumentprestudent.rdf.php?prestudent_id='+prestudent_id+"&"+gettimestamp();

	try
	{
		InteressentDokumentTreeAbgegebenDatasource.removeXMLSinkObserver(InteressentDokumentTreeAbgegebenSinkObserver);
		doctree.builder.removeListener(InteressentDokumentTreeAbgegebenListener);
	}
	catch(e)
	{}
	
	//Alte DS entfernen
	var oldDatasources = doctree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		doctree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	doctree.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	InteressentDokumentTreeAbgegebenDatasource = rdfService.GetDataSource(url);
	InteressentDokumentTreeAbgegebenDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	InteressentDokumentTreeAbgegebenDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	doctree.database.AddDataSource(InteressentDokumentTreeAbgegebenDatasource);
	InteressentDokumentTreeAbgegebenDatasource.addXMLSinkObserver(InteressentDokumentTreeAbgegebenSinkObserver);
	doctree.builder.addListener(InteressentDokumentTreeAbgegebenListener);
	
	// *** Konto ***
	kontotree = document.getElementById('student-konto-tree');
	filter = document.getElementById('student-konto-button-filter').value;
	url='<?php echo APP_ROOT;?>rdf/konto.rdf.php?person_id='+person_id+"&filter="+filter+"&studiengang_kz="+studiengang_kz_prestudent+"&"+gettimestamp();

	try
	{
		StudentKontoTreeDatasource.removeXMLSinkObserver(StudentKontoTreeSinkObserver);
		kontotree.builder.removeListener(StudentKontoTreeListener);
	}
	catch(e)
	{}
	
	//Alte DS entfernen
	var oldDatasources = kontotree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		kontotree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	kontotree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentKontoTreeDatasource = rdfService.GetDataSource(url);
	StudentKontoTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentKontoTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	kontotree.database.AddDataSource(StudentKontoTreeDatasource);
	StudentKontoTreeDatasource.addXMLSinkObserver(StudentKontoTreeSinkObserver);
	kontotree.builder.addListener(StudentKontoTreeListener);

	if(uid!='')
	{
		// *** Zeugnis ***
		zeugnistree = document.getElementById('student-zeugnis-tree');
		url='<?php echo APP_ROOT;?>rdf/akte.rdf.php?person_id='+person_id+"&dokument_kurzbz=Zeugnis&"+gettimestamp();
	
		try
		{
			StudentAkteTreeDatasource.removeXMLSinkObserver(StudentAkteTreeSinkObserver);
		}
		catch(e)
		{}
		
		//Alte DS entfernen
		var oldDatasources = zeugnistree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			zeugnistree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		zeugnistree.builder.rebuild();
	
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		StudentAkteTreeDatasource = rdfService.GetDataSource(url);
		StudentAkteTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		StudentAkteTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		zeugnistree.database.AddDataSource(StudentAkteTreeDatasource);
		StudentAkteTreeDatasource.addXMLSinkObserver(StudentAkteTreeSinkObserver);
	}
	
	if(uid!='')
	{
		// *** Incomming/Outgoing ***
		bisiotree = document.getElementById('student-io-tree');
	
		url='<?php echo APP_ROOT;?>rdf/bisio.rdf.php?uid='+uid+"&"+gettimestamp();
	
		try
		{
			StudentIOTreeDatasource.removeXMLSinkObserver(StudentIOTreeSinkObserver);
			bisiotree.builder.removeListener(StudentIOTreeListener);
		}
		catch(e)
		{}
		
		//Alte DS entfernen
		var oldDatasources = bisiotree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			bisiotree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		bisiotree.builder.rebuild();		
	
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		StudentIOTreeDatasource = rdfService.GetDataSource(url);
		StudentIOTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		StudentIOTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		bisiotree.database.AddDataSource(StudentIOTreeDatasource);
		StudentIOTreeDatasource.addXMLSinkObserver(StudentIOTreeSinkObserver);
		bisiotree.builder.addListener(StudentIOTreeListener);
	}
	
	if(uid!='')
	{
		// *** ZeugnisNoten ***
		notentree = document.getElementById('student-noten-tree');
	
		url='<?php echo APP_ROOT;?>rdf/zeugnisnote.rdf.php?uid='+uid+"&"+gettimestamp();
	
		try
		{
			StudentNotenTreeDatasource.removeXMLSinkObserver(StudentNotenTreeSinkObserver);
			notentree.builder.removeListener(StudentNotenTreeListener);
		}
		catch(e)
		{}
		
		//Alte DS entfernen
		var oldDatasources = notentree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			notentree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		notentree.builder.rebuild();
				
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		StudentNotenTreeDatasource = rdfService.GetDataSource(url);
		StudentNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		StudentNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		notentree.database.AddDataSource(StudentNotenTreeDatasource);
		StudentNotenTreeDatasource.addXMLSinkObserver(StudentNotenTreeSinkObserver);
		notentree.builder.addListener(StudentNotenTreeListener);

		// *** LvGesamtNoten ***
		lvgesamtnotentree = document.getElementById('student-lvgesamtnoten-tree');
	
		url='<?php echo APP_ROOT;?>rdf/lvgesamtnote.rdf.php?uid='+uid+"&"+gettimestamp();
	
		try
		{
			StudentLvGesamtNotenTreeDatasource.removeXMLSinkObserver(StudentLvGesamtNotenTreeSinkObserver);
			lvgesamtnotentree.builder.removeListener(StudentLvGesamtNotenTreeListener);
		}
		catch(e)
		{}

		//Alte DS entfernen
		var oldDatasources = lvgesamtnotentree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			lvgesamtnotentree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		lvgesamtnotentree.builder.rebuild();
			
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		StudentLvGesamtNotenTreeDatasource = rdfService.GetDataSource(url);
		StudentLvGesamtNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		StudentLvGesamtNotenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		lvgesamtnotentree.database.AddDataSource(StudentLvGesamtNotenTreeDatasource);
		StudentLvGesamtNotenTreeDatasource.addXMLSinkObserver(StudentLvGesamtNotenTreeSinkObserver);
		lvgesamtnotentree.builder.addListener(StudentLvGesamtNotenTreeListener);
	}

	// ***** KONTAKTE *****
	document.getElementById('student-kontakt').setAttribute('src','kontakt.xul.php?person_id='+person_id);
	
	// ***** Betriebsmittel *****
	document.getElementById('student-betriebsmittel').setAttribute('src','betriebsmitteloverlay.xul.php?person_id='+person_id+'&uid='+uid);

	if(uid!='')
	{
		// ***** Pruefungen *****
		pruefungtree = document.getElementById('student-pruefung-tree');
	
		url='<?php echo APP_ROOT;?>rdf/pruefung.rdf.php?student_uid='+uid+"&"+gettimestamp();
	
		try
		{
			StudentPruefungTreeDatasource.removeXMLSinkObserver(StudentPruefungTreeSinkObserver);
			pruefungtree.builder.removeListener(StudentPruefungTreeListener);
		}
		catch(e)
		{}
		
		//Alte DS entfernen
		var oldDatasources = pruefungtree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			pruefungtree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		pruefungtree.builder.rebuild();
		
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		StudentPruefungTreeDatasource = rdfService.GetDataSource(url);
		StudentPruefungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		StudentPruefungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		pruefungtree.database.AddDataSource(StudentPruefungTreeDatasource);
		StudentPruefungTreeDatasource.addXMLSinkObserver(StudentPruefungTreeSinkObserver);
		pruefungtree.builder.addListener(StudentPruefungTreeListener);
		
		StudentPruefungDetailDisableFields(true);
	}
	
	if(uid!='')
	{
		// ****** Abschlusspruefung ******** //
		StudentAbschlusspruefungDetailDisableFields(true);
		StudentAbschlusspruefungTreeLoad(uid);	
	}
	
	if(uid!='')
	{
		// ****** Projektarbeit ********* //
		StudentProjektarbeitDetailDisableFields(true);
		StudentProjektbetreuerDisableFields(true);
		StudentProjektarbeitTreeLoad(uid);
	}
	
	
	if(uid!='')
	{
		// ******* GRUPPEN ************ //
		StudentGruppenRemoveDatasource();
		if(document.getElementById('student-content-tabs').selectedItem==document.getElementById('student-tab-gruppen'))
		{
			StudentGruppenLoadData();
		}
	}
	
	if(uid!='')
	{
		// ******* FUNKTIONEN ********* //
		if(document.getElementById('student-content-tabs').selectedItem==document.getElementById('student-tab-funktionen'))
		{
			url = '<?php echo APP_ROOT; ?>content/funktionen.xul.php?uid='+uid;
			document.getElementById('student-funktionen').setAttribute('src',url);
		}
	}

	// Notizen laden
	var studentnotiz = document.getElementById('student-box-notizen');
	studentnotiz.LoadNotizTree('','','','',person_id,'','','','');

	// Selektierungsfunktion der Addons aufrufen
	for(i in addon)
	{
		if(typeof addon[i].selectStudent == 'function')
			addon[i].selectStudent(person_id, prestudent_id, uid);
	}
}

// ****
// * De-/Aktiviert die Prestudent Felder
// ****
function StudentPrestudentDisableFields(val)
{
	document.getElementById('student-prestudent-menulist-aufmerksamdurch').disabled=val;
	document.getElementById('student-prestudent-menulist-berufstaetigkeit').disabled=val;
	document.getElementById('student-prestudent-menulist-ausbildung').disabled=val;
	document.getElementById('student-prestudent-menulist-zgvcode').disabled=val;
	document.getElementById('student-prestudent-textbox-zgvort').disabled=val;
	document.getElementById('student-prestudent-textbox-zgvdatum').disabled=val;
	document.getElementById('student-prestudent-menulist-zgvmastercode').disabled=val;
	document.getElementById('student-prestudent-textbox-zgvmasterort').disabled=val;
	document.getElementById('student-prestudent-textbox-zgvmasterdatum').disabled=val;
	document.getElementById('student-prestudent-menulist-ausstellungsstaat').disabled=val;
	document.getElementById('student-prestudent-menulist-aufnahmeschluessel').disabled=val;
	document.getElementById('student-prestudent-checkbox-facheinschlberuf').disabled=val;
	document.getElementById('student-prestudent-menulist-reihungstest').disabled=val;
	document.getElementById('student-prestudent-textbox-anmeldungreihungstest').disabled=val;
	document.getElementById('student-prestudent-checkbox-reihungstestangetreten').disabled=val;
	document.getElementById('student-prestudent-textbox-punkte').disabled=val;
	document.getElementById('student-prestudent-textbox-punkte1').disabled=val;
	document.getElementById('student-prestudent-textbox-punkte2').disabled=val;
	document.getElementById('student-prestudent-textbox-punkte3').disabled=val;
	document.getElementById('student-prestudent-checkbox-bismelden').disabled=val;
	document.getElementById('student-prestudent-checkbox-dual').disabled=val;
	document.getElementById('student-prestudent-button-anmeldungreihungstest-heute').disabled=val;
	document.getElementById('student-prestudent-button-save').disabled=val;
	//document.getElementById('student-prestudent-menulist-studiengang_kz').disabled=val;
	document.getElementById('student-prestudent-textbox-anmerkung').disabled=val;
	document.getElementById('student-prestudent-textbox-mentor').disabled=val;
	
	//Status Tree leeren
	rollentree = document.getElementById('student-prestudent-tree-rolle');

	try
	{
		StudentDetailRolleTreeDatasource.removeXMLSinkObserver(StudentDetailRolleTreeSinkObserver);
	}
	catch(e)
	{}
	
	//Alte DS entfernen
	var oldDatasources = rollentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		rollentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	rollentree.builder.rebuild();
}

// ****
// * Speichert die Prestudent Daten
// ****
function StudentPrestudentSave()
{
	aufmerksamdurch_kurzbz = document.getElementById('student-prestudent-menulist-aufmerksamdurch').value;
	berufstaetigkeit_code = document.getElementById('student-prestudent-menulist-berufstaetigkeit').value;
	ausbildungcode = document.getElementById('student-prestudent-menulist-ausbildung').value;
	zgv_code = document.getElementById('student-prestudent-menulist-zgvcode').value;
	zgvort = document.getElementById('student-prestudent-textbox-zgvort').value;
	zgvdatum = document.getElementById('student-prestudent-textbox-zgvdatum').value;
	zgvmaster_code = document.getElementById('student-prestudent-menulist-zgvmastercode').value;
	zgvmasterort = document.getElementById('student-prestudent-textbox-zgvmasterort').value;
	zgvmasterdatum = document.getElementById('student-prestudent-textbox-zgvmasterdatum').value;
	ausstellungsstaat = document.getElementById('student-prestudent-menulist-ausstellungsstaat').value;
	aufnahmeschluessel = document.getElementById('student-prestudent-menulist-aufnahmeschluessel').value;
	facheinschlberuf = document.getElementById('student-prestudent-checkbox-facheinschlberuf').checked;
	reihungstest_id = document.getElementById('student-prestudent-menulist-reihungstest').value;
	anmeldungreihungstest = document.getElementById('student-prestudent-textbox-anmeldungreihungstest').value;
	reihungstestangetreten = document.getElementById('student-prestudent-checkbox-reihungstestangetreten').checked;
	punkte = document.getElementById('student-prestudent-textbox-punkte').value;
	punkte1 = document.getElementById('student-prestudent-textbox-punkte1').value;
	punkte2 = document.getElementById('student-prestudent-textbox-punkte2').value;
	punkte3 = document.getElementById('student-prestudent-textbox-punkte3').value;
	bismelden = document.getElementById('student-prestudent-checkbox-bismelden').checked;
	dual = document.getElementById('student-prestudent-checkbox-dual').checked;
	person_id = document.getElementById('student-prestudent-textbox-person_id').value;
	prestudent_id = document.getElementById('student-prestudent-textbox-prestudent_id').value;
	neu = document.getElementById('student-prestudent-checkbox-new').checked;
	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	anmerkung = document.getElementById('student-prestudent-textbox-anmerkung').value;
	mentor = document.getElementById('student-prestudent-textbox-mentor').value;

	if(zgvdatum!='' && !CheckDatum(zgvdatum))
	{
		alert('ZGV Datum ist ungueltig');
		return false;
	}
	if(zgvmasterdatum!='' && !CheckDatum(zgvmasterdatum))
	{
		alert('ZGVMaster Datum ist ungueltig');
		return false;
	}
	if(anmeldungreihungstest!='' && !CheckDatum(anmeldungreihungstest))
	{
		alert('ReihungstestDatum ist ungueltig');
		return false;
	}

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
	req.add('zgvdatum', ConvertDateToISO(zgvdatum));
	req.add('zgvmas_code', zgvmaster_code);
	req.add('zgvmaort', zgvmasterort);
	req.add('zgvmadatum', ConvertDateToISO(zgvmasterdatum));
	req.add('ausstellungsstaat', ausstellungsstaat);
	req.add('aufnahmeschluessel', aufnahmeschluessel);
	req.add('facheinschlberuf', facheinschlberuf);
	req.add('reihungstest_id', reihungstest_id);
	req.add('anmeldungreihungstest', ConvertDateToISO(anmeldungreihungstest));
	req.add('reihungstestangetreten', reihungstestangetreten);
	req.add('punkte', punkte);
	req.add('punkte1', punkte1);
	req.add('punkte2', punkte2);
	req.add('punkte3', punkte3);
	req.add('bismelden', bismelden);
	req.add('dual', dual);
	req.add('person_id', person_id);
	req.add('prestudent_id', prestudent_id);
	req.add('studiengang_kz', studiengang_kz);
	req.add('anmerkung', anmerkung);
	req.add('mentor', mentor);

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
		if(val.dbdml_warning!='')
			alert(val.dbdml_warning+"\n\nDaten wurden gespeichert");
		
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

		StudentSelectID=document.getElementById('student-prestudent-textbox-prestudent_id').value;
		StudentTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Loescht eine Prestudentrolle
// ****
function StudentPrestudentRolleDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-prestudent-tree-rolle');

	if (tree.currentIndex==-1) return;

	//markierte Rolle holen
	var status_kurzbz = getTreeCellText(tree, 'student-prestudent-tree-rolle-status_kurzbz', tree.currentIndex);
	var studiensemester_kurzbz = getTreeCellText(tree, 'student-prestudent-tree-rolle-studiensemester_kurzbz', tree.currentIndex);
	var prestudent_id = getTreeCellText(tree, 'student-prestudent-tree-rolle-prestudent_id', tree.currentIndex);	
	var ausbildungssemester = getTreeCellText(tree, 'student-prestudent-tree-rolle-ausbildungssemester', tree.currentIndex);

	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	if(confirm('Diese Rolle wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'deleterolle');

		req.add('status_kurzbz', status_kurzbz);
		req.add('prestudent_id', prestudent_id);
		req.add('studiensemester_kurzbz', studiensemester_kurzbz);
		req.add('ausbildungssemester', ausbildungssemester);
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
			StudentDetailRolleTreeDatasource.Refresh(false);
			SetStatusBarText('Daten wurden geloescht');
		}
	}
}

// ****
// * Bestaetigt einen Prestudentstatus
// ****
function StudentPrestudentRolleBestaetigen()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-prestudent-tree-rolle');

	if (tree.currentIndex==-1) return;

	//markierte Rolle holen
	var status_kurzbz = getTreeCellText(tree, 'student-prestudent-tree-rolle-status_kurzbz', tree.currentIndex);
	var studiensemester_kurzbz = getTreeCellText(tree, 'student-prestudent-tree-rolle-studiensemester_kurzbz', tree.currentIndex);
	var prestudent_id = getTreeCellText(tree, 'student-prestudent-tree-rolle-prestudent_id', tree.currentIndex);	
	var ausbildungssemester = getTreeCellText(tree, 'student-prestudent-tree-rolle-ausbildungssemester', tree.currentIndex);

	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	if(confirm('Diesen Status bestaetigen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'bestaetigerolle');

		req.add('status_kurzbz', status_kurzbz);
		req.add('prestudent_id', prestudent_id);
		req.add('studiensemester_kurzbz', studiensemester_kurzbz);
		req.add('ausbildungssemester', ausbildungssemester);
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
			StudentDetailRolleTreeDatasource.Refresh(false);
			SetStatusBarText('Daten wurden geloescht');
		}
	}
}

// ****
// * oeffnet den BearbeitenDialog fuer die Prestudentrollen
// ****
function StudentRolleBearbeiten()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-prestudent-tree-rolle');

	if (tree.currentIndex==-1) return;

	//Ausgewaehlte Nr holen
	var status_kurzbz = getTreeCellText(tree, 'student-prestudent-tree-rolle-status_kurzbz', tree.currentIndex);
	var studiensemester_kurzbz = getTreeCellText(tree, 'student-prestudent-tree-rolle-studiensemester_kurzbz', tree.currentIndex);
	var prestudent_id = getTreeCellText(tree, 'student-prestudent-tree-rolle-prestudent_id', tree.currentIndex);	
	var ausbildungssemester = getTreeCellText(tree, 'student-prestudent-tree-rolle-ausbildungssemester', tree.currentIndex);

	window.open('<?php echo APP_ROOT?>content/student/studentrolledialog.xul.php?prestudent_id='+prestudent_id+'&status_kurzbz='+status_kurzbz+'&studiensemester_kurzbz='+studiensemester_kurzbz+'&ausbildungssemester='+ausbildungssemester,"Status","status=no, width=500, height=300, centerscreen, resizable");
}

// ****
// * Speichert die Daten aus dem BearbeitenDialog
// ****
function StudentRolleSpeichern(dialog, studiensemester_old, ausbildungssemester_old)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	prestudent_id = dialog.getElementById('student-rolle-textbox-prestudent_id').value;
	if(studiensemester_old=='')
		status_kurzbz = dialog.getElementById('student-rolle-menulist-status_kurzbz').value;
	else
		status_kurzbz = dialog.getElementById('student-rolle-textbox-status_kurzbz').value;
	studiensemester_kurzbz = dialog.getElementById('student-rolle-menulist-studiensemester').value;
	ausbildungssemester = dialog.getElementById('student-rolle-menulist-ausbildungssemester').value;
	datum = dialog.getElementById('student-rolle-datum-datum').value;
	orgform_kurzbz = dialog.getElementById('student-rolle-menulist-orgform_kurzbz').value;
	studienplan_id = dialog.getElementById('student-rolle-menulist-studienplan').value;
	
	if(!CheckDatum(datum))
	{
		alert('Datum ist ungueltig');
		return false;
	}
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'saverolle');

	req.add('status_kurzbz', status_kurzbz);
	req.add('prestudent_id', prestudent_id);
	req.add('studiensemester_kurzbz', studiensemester_kurzbz);
	req.add('studiensemester_old', studiensemester_old);
	req.add('ausbildungssemester_old', ausbildungssemester_old);
	req.add('ausbildungssemester', ausbildungssemester);
	req.add('datum', ConvertDateToISO(datum));
	req.add('orgform_kurzbz', orgform_kurzbz);
	req.add('studienplan_id', studienplan_id);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
		return false;
	}
	else
	{
		StudentDetailRolleTreeDatasource.Refresh(false);
		SetStatusBarText('Daten wurden gespeichert');
		return true;
	}	
}

// ****
// * Anmeldungsdatum fuer den RT wird auf das Aktuelle Datum gesetzt
// ****
function StudentAnmeldungreihungstestHeute()
{
	var now = new Date();
	var jahr = now.getFullYear();

	monat = now.getMonth()+1;
	if(monat<10) monat='0'+monat;
	tag = now.getDate();
	if(tag<10) tag='0'+tag;

	document.getElementById('student-prestudent-textbox-anmeldungreihungstest').value=tag+'.'+monat+'.'+jahr;
}

// ****
// * Wenn Reihungstestpunkte eingetragen werden automatisch das
// * Hackerl 'zum Reihungstest angetreten' setzen
// ****
function StudentReihungstestPunkteChange()
{
	if(document.getElementById('student-prestudent-textbox-punkte1').value!='' 
	&& document.getElementById('student-prestudent-checkbox-reihungstestangetreten').checked==false)
		document.getElementById('student-prestudent-checkbox-reihungstestangetreten').checked=true;
}

// ****
// * Summiert die beiden Reihungstestpunkte
// ****
function StudentReihungstestPunkteSum()
{
	punkte1 = document.getElementById('student-prestudent-textbox-punkte1').value;
	punkte2 = document.getElementById('student-prestudent-textbox-punkte2').value;
	punkte3 = document.getElementById('student-prestudent-textbox-punkte3').value;

	if(punkte1=='')
	{
		punkte1=0;
		document.getElementById('student-prestudent-textbox-punkte1').value=0;
	}
	if(punkte2=='')
	{
		punkte2=0;
		document.getElementById('student-prestudent-textbox-punkte2').value=0;
	}
	if(punkte3=='')
	{
		punkte3=0;
		document.getElementById('student-prestudent-textbox-punkte3').value=0;

	}
	
	document.getElementById('student-prestudent-textbox-punkte').value=parseFloat(punkte1)+parseFloat(punkte2)+parseFloat(punkte3);
}

// ****
// * Holt die Reihungstestpunkte des Prestudenten
// ****
function StudentReihungstestPunkteTransmit()
{
	var prestudent_id = document.getElementById('student-prestudent-textbox-prestudent_id').value;
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'getReihungstestPunkte');

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
		document.getElementById('student-prestudent-textbox-punkte1').value = val.dbdml_data;
		StudentReihungstestPunkteSum();
	}
}

// ****
// * Einen Ab-/Unterbrecher wieder zum Studenten machen
// ****
function StudentUnterbrecherZuStudent()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1) return;

	if(sem = prompt('In welches Semester soll dieser Student verschoben werden?'))
	{
		if(!isNaN(sem))
		{
			StudentAddRolle('Student', sem)
		}
		else
		{
			alert('Semester ist ungueltig');
		}
	}
}

// ****
// * Fuegt eine neue Rolle zu einer Person hinzu
// ****
function StudentRolleAdd()
{
	var prestudent_id = document.getElementById('student-prestudent-textbox-prestudent_id').value;
	window.open('<?php echo APP_ROOT?>content/student/studentrolledialog.xul.php?prestudent_id='+prestudent_id,"Status","chrome, status=no, width=500, height=300, centerscreen, resizable");
}

// ****
// * Fuegt eine Rolle zu einem Studenten hinzu
// ****
function StudentAddRolle(rolle, semester, studiensemester)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1) return;

	//Alle markierten Personen holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', v);
			paramList += ';'+prestudent_id;
			anzahl = anzahl+1;
		}
	}
	
	//Ausgewaehlte ID holen
	//var prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', tree.currentIndex);

	if(anzahl>1)
		conf = 'Diese '+anzahl+' Studenten';
	else
		conf = 'Diesen Studenten';		
	
	if(semester!='0' || confirm(conf+' zum '+rolle+' machen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'addrolle');

		req.add('prestudent_id', paramList);
		req.add('status_kurzbz', rolle);
		req.add('semester', semester);
		if(typeof(studiensemester)!='unknown')
			req.add('studiensemester_kurzbz', studiensemester);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response);

		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response)
			else
				alert(val.dbdml_errormsg)
			StudentTreeRefresh();
		}
		else
		{
			StudentTreeRefresh();
			SetStatusBarText('Rolle hinzugefuegt');
		}
	}
}

// ****
// * Druckt die Instkriptionsbestaetigung
// ****
function StudentPrintInskriptionsbestaetigung()
{
	tree = document.getElementById('student-tree');
	//Alle markierten Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var anzahl=0;

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			uid = getTreeCellText(tree, 'student-treecol-uid', v);
			paramList += ';'+uid;
			stg_kz = getTreeCellText(tree, 'student-treecol-studiengang_kz', v);
			anzahl = anzahl+1;
		}
	}

	var stsem = getStudiensemester();
	if(paramList.replace(";",'')=='')
	{
		alert('Bitte einen Studenten auswaehlen');
		return false;
	}
	
	if(anzahl>0)
		window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=student.rdf.php&xsl=Inskription&stg_kz='+stg_kz+'&uid='+paramList+'&ss='+stsem,'Inskriptionsbestaetigung', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
	else
		alert('Bitte einen Studenten auswaehlen');
}

// ****
// * Excel Export der Studentendaten
// ****
function StudentExport()
{
	var tree = document.getElementById('student-tree');
	var data='';
	//Wenn nichts markiert wurde -> alle exportieren
	if(tree.currentIndex==-1)
	{
		if(tree.view)
			var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
		else
			return false;
			
		for (var v=0; v < items; v++)
		{
			prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', v);
			data = data+';'+prestudent_id;
		}
	}
	else
	{	
		var start = new Object();
		var end = new Object();
		var numRanges = tree.view.selection.getRangeCount();
		var paramList= '';
		var anzahl=0;
	
		//alle markierten personen holen
		for (var t = 0; t < numRanges; t++)
		{
	  		tree.view.selection.getRangeAt(t,start,end);
			for (var v = start.value; v <= end.value; v++)
			{
				prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', v);
				data = data+';'+prestudent_id;
			}
		}
	}
	
	stsem = getStudiensemester();
	action = '<?php echo APP_ROOT; ?>content/statistik/studentenexport.xls.php?studiensemester_kurzbz='+stsem;
	OpenWindowPost(action, data);
}

// **************** KONTO ******************

// ****
// * Selectiert die Buchung nachdem der Tree
// * rebuildet wurde.
// ****
function StudentKontoTreeSelectBuchung()
{
	var tree=document.getElementById('student-konto-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Buchung gespeichert
	if(StudentKontoSelectBuchung!=null)
	{
		//Alle subtrees oeffnen weil rowCount nur die Anzahl der sichtbaren
		//Zeilen zurueckliefert
	   	//for(var i=items-1;i>=0;i--)
	   	//{
	   	//	if(!tree.view.isContainerOpen(i))
	   	//		tree.view.toggleOpenState(i);
	   	//}

	   	//Jetzt die wirkliche Anzahl (aller) Zeilen holen
	   	items = tree.view.rowCount;
	   	for(var i=0;i<items;i++)
	   	{
	   		//buchungsnr der row holen
			buchungsnr = getTreeCellText(tree, 'student-konto-tree-buchungsnr', i);

			//wenn dies die zu selektierende Zeile
			if(buchungsnr == StudentKontoSelectBuchung)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentKontoSelectBuchung=null;
				return true;
			}
	   	}
	}
}

// ****
// * Laedt die Buchungen der markierten Person
// ****
function StudentKontoLoad()
{
	person_id = document.getElementById('student-detail-textbox-person_id').value;
	kontotree = document.getElementById('student-konto-tree');
	filter = document.getElementById('student-konto-button-filter').value;
	studienang_kz_prestudent = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	url='<?php echo APP_ROOT;?>rdf/konto.rdf.php?person_id='+person_id+"&filter="+filter+"&studiengang_kz="+studiengang_kz_prestudent+"&"+gettimestamp();

	try
	{
		StudentKontoTreeDatasource.removeXMLSinkObserver(StudentKontoTreeSinkObserver);
		kontotree.builder.removeListener(StudentKontoTreeListener);
	}
	catch(e)
	{}
	
	//Alte DS entfernen
	var oldDatasources = kontotree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		kontotree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	kontotree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentKontoTreeDatasource = rdfService.GetDataSource(url);
	StudentKontoTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentKontoTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	kontotree.database.AddDataSource(StudentKontoTreeDatasource);
	StudentKontoTreeDatasource.addXMLSinkObserver(StudentKontoTreeSinkObserver);
	kontotree.builder.addListener(StudentKontoTreeListener);
}

// ****
// * Wenn eine buchung Ausgewaehlt wird, dann werden
// * die Details geladen und angezeigt
// ****
function StudentKontoAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-konto-tree');

	if (tree.currentIndex==-1) return;

	StudentKontoDetailDisableFields(false);

	//Ausgewaehlte Nr holen
	var buchungsnr = getTreeCellText(tree, 'student-konto-tree-buchungsnr', tree.currentIndex);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/konto.rdf.php?buchungsnr='+buchungsnr+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/konto/" + buchungsnr);

	var predicateNS = "http://www.technikum-wien.at/konto/rdf";

	//Daten holen

	person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	studiengang_kz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiengang_kz" ));
	studiensemester_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiensemester_kurzbz" ));
	buchungsnr_verweis = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#buchungsnr_verweis" ));
	betrag = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#betrag" ));
	buchungsdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#buchungsdatum" ));
	buchungstext = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#buchungstext" ));
	mahnspanne = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#mahnspanne" ));
	buchungstyp_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#buchungstyp_kurzbz" ));
	credit_points = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#credit_points" ));
	zahlungsreferenz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zahlungsreferenz" ));

	document.getElementById('student-konto-textbox-betrag').value=betrag;
	document.getElementById('student-konto-textbox-buchungsdatum').value=buchungsdatum;
	document.getElementById('student-konto-textbox-buchungstext').value=buchungstext;
	document.getElementById('student-konto-textbox-mahnspanne').value=mahnspanne;
	document.getElementById('student-konto-menulist-buchungstyp').value=buchungstyp_kurzbz;
	document.getElementById('student-konto-textbox-buchungsnr').value=buchungsnr;
	document.getElementById('student-konto-menulist-studiensemester').value=studiensemester_kurzbz;
	document.getElementById('student-konto-menulist-studiengang_kz').value=studiengang_kz;
	document.getElementById('student-konto-textbox-credit_points').value=credit_points;
	document.getElementById('student-konto-textbox-zahlungsreferenz').value=zahlungsreferenz;
}

// ****
// * Aendert den Filter fuer den Konto Tree und Refresht ihn dann
// ****
function StudentKontoFilter()
{

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	filter = document.getElementById('student-konto-button-filter');

	if(filter.value=='offene')
	{
		filter.value='alle';
		filter.label='offene Anzeigen';
		document.getElementById('student-konto-label-filter').value='alle Buchungen:';
	}
	else
	{
		filter.value='offene';
		filter.label='alle Anzeigen';
		document.getElementById('student-konto-label-filter').value='offene Buchungen:';
	}

	//Konto Tree mit neuem Filter laden
	kontotree = document.getElementById('student-konto-tree');
	person_id = document.getElementById('student-prestudent-textbox-person_id').value
	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value
	url='<?php echo APP_ROOT;?>rdf/konto.rdf.php?person_id='+person_id+"&filter="+filter.value+"&studiengang_kz="+studiengang_kz+"&"+gettimestamp();

	var buchungsnr=null;
	try
	{
		if(kontotree.currentIndex!='-1')
		{
			//Ausgewaehlte Nr holen
			buchungsnr = getTreeCellText(kontotree, 'student-konto-tree-buchungsnr', kontotree.currentIndex);
		}
	}
	catch(e)
	{}

	try
	{
		StudentKontoTreeDatasource.removeXMLSinkObserver(StudentKontoTreeSinkObserver);
		kontotree.builder.removeListener(StudentKontoTreeListener);
	}
	catch(e)
	{}
	
	//Alte DS entfernen
	var oldDatasources = kontotree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		kontotree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	kontotree.builder.rebuild();

	StudentKontoSelectBuchung = buchungsnr;

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentKontoTreeDatasource = rdfService.GetDataSource(url);
	StudentKontoTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentKontoTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	kontotree.database.AddDataSource(StudentKontoTreeDatasource);
	StudentKontoTreeDatasource.addXMLSinkObserver(StudentKontoTreeSinkObserver);
	kontotree.builder.addListener(StudentKontoTreeListener);
}

// ****
// * Setzt im Studententree einen vordefinierten Filter
// ****
function StudentKontoFilterStudenten(filter)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('tree-verband');

	//Wenn nichts markiert wurde -> beenden
	if(tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Studiengang/Semester waehlen');
		return;
	}
	
	buchungstyp = document.getElementById('student-konto-menulist-filter-buchungstyp-offen').value;
	
    // Progressmeter starten. Ab jetzt keine 'return's mehr.
    document.getElementById('statusbar-progressmeter').setAttribute('mode','undetermined');
    //globalProgressmeter.StartPM();

	var stg_kz = getTreeCellText(tree, 'stg_kz', tree.currentIndex);
	var sem = getTreeCellText(tree, 'sem', tree.currentIndex);
	var ver = getTreeCellText(tree, 'ver', tree.currentIndex);
	var grp = getTreeCellText(tree, 'grp', tree.currentIndex);
	var gruppe = getTreeCellText(tree, 'gruppe', tree.currentIndex);
	var typ = getTreeCellText(tree, 'typ', tree.currentIndex);
	var stsem = getTreeCellText(tree, 'stsem', tree.currentIndex);
		
	if(stsem=='')
		stsem = getStudiensemester();
	if(typ=='')
		typ='student';
	url = "<?php echo APP_ROOT; ?>rdf/student.rdf.php?studiengang_kz="+stg_kz+"&semester="+sem+"&verband="+ver+"&gruppe="+grp+"&gruppe_kurzbz="+gruppe+"&studiensemester_kurzbz="+stsem+"&typ="+typ+"&filter2="+filter+"&buchungstyp="+buchungstyp+"&"+gettimestamp();
	var treeStudent=document.getElementById('student-tree');

	try
	{
		StudentTreeDatasource.removeXMLSinkObserver(StudentTreeSinkObserver);
		treeStudent.builder.removeListener(StudentTreeListener);
	}
	catch(e)
	{}
	
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
}

// ****
// * Setzt im Studententree einen Filter auf die Buchungstypen
// ****
function StudentKontoFilterBuchungstyp()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('tree-verband');

	//Wenn nichts markiert wurde -> beenden
	if(tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Studiengang/Semester waehlen');
		return;
	}

	filter = document.getElementById('student-konto-menulist-filter-buchungstyp').value;
	
    // Progressmeter starten. Ab jetzt keine 'return's mehr.
    document.getElementById('statusbar-progressmeter').setAttribute('mode','undetermined');
    //globalProgressmeter.StartPM();

	var stg_kz = getTreeCellText(tree, 'stg_kz', tree.currentIndex);
	var sem = getTreeCellText(tree, 'sem', tree.currentIndex);
	var ver = getTreeCellText(tree, 'ver', tree.currentIndex);
	var grp = getTreeCellText(tree, 'grp', tree.currentIndex);
	var gruppe = getTreeCellText(tree, 'gruppe', tree.currentIndex);
	var typ = getTreeCellText(tree, 'typ', tree.currentIndex);
	var stsem = getTreeCellText(tree, 'stsem', tree.currentIndex);
		
	if(stsem=='')
		stsem = getStudiensemester();
	if(typ=='')
		typ='student';
	url = "<?php echo APP_ROOT; ?>rdf/student.rdf.php?studiengang_kz="+stg_kz+"&semester="+sem+"&verband="+ver+"&gruppe="+grp+"&gruppe_kurzbz="+gruppe+"&studiensemester_kurzbz="+stsem+"&typ="+typ+"&filter2=buchungstyp;"+filter+"&"+gettimestamp();
	var treeStudent=document.getElementById('student-tree');

	try
	{
		StudentTreeDatasource.removeXMLSinkObserver(StudentTreeSinkObserver);
		treeStudent.builder.removeListener(StudentTreeListener);
	}
	catch(e)
	{}
	
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
}

// ****
// * Aktiviert / Deaktiviert die Konto Felder
// ****
function StudentKontoDisableFields(val)
{
	document.getElementById('student-konto-button-filter').disabled=val;
	document.getElementById('student-konto-button-neu').disabled=val;
	document.getElementById('student-konto-button-gegenbuchung').disabled=val;
	document.getElementById('student-konto-button-loeschen').disabled=val;
	document.getElementById('student-konto-button-zahlungsbestaetigung').disabled=val;
	StudentKontoDetailDisableFields(true);
}

// ****
// * Aktiviert / Deaktiviert die Kontodetail Felder
// ****
function StudentKontoDetailDisableFields(val)
{
	document.getElementById('student-konto-textbox-betrag').disabled=val;
	document.getElementById('student-konto-textbox-buchungsdatum').disabled=val;
	document.getElementById('student-konto-textbox-buchungstext').disabled=val;
	document.getElementById('student-konto-textbox-mahnspanne').disabled=val;
	document.getElementById('student-konto-textbox-credit_points').disabled=val;
<!--	document.getElementById('student-konto-textbox-zahlungsreferenz').disabled=val;-->
	document.getElementById('student-konto-menulist-buchungstyp').disabled=val;
	document.getElementById('student-konto-menulist-studiensemester').disabled=val;
	document.getElementById('student-konto-menulist-studiengang_kz').disabled=val;
	document.getElementById('student-konto-button-speichern').disabled=val;
}

// ****
// * Speichert die Buchung
// ****
function StudentKontoDetailSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	betrag = document.getElementById('student-konto-textbox-betrag').value;
	buchungsdatum = document.getElementById('student-konto-textbox-buchungsdatum').value;
	buchungstext = document.getElementById('student-konto-textbox-buchungstext').value;
	mahnspanne = document.getElementById('student-konto-textbox-mahnspanne').value;
	buchungstyp_kurzbz = document.getElementById('student-konto-menulist-buchungstyp').value;
	buchungsnr = document.getElementById('student-konto-textbox-buchungsnr').value;
	studiensemester_kurzbz = document.getElementById('student-konto-menulist-studiensemester').value;
	studiengang_kz = document.getElementById('student-konto-menulist-studiengang_kz').value;
	credit_points = document.getElementById('student-konto-textbox-credit_points').value;
	
	if(buchungsdatum!='' && !CheckDatum(buchungsdatum))
	{
		alert('Buchungsdatum ist ungueltig');
		return false;
	}
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savebuchung');

	req.add('betrag', betrag);
	req.add('buchungsdatum', ConvertDateToISO(buchungsdatum));
	req.add('buchungstext', buchungstext);
	req.add('mahnspanne', mahnspanne);
	req.add('buchungstyp_kurzbz', buchungstyp_kurzbz);
	req.add('buchungsnr', buchungsnr);
	req.add('studiensemester_kurzbz', studiensemester_kurzbz);
	req.add('studiengang_kz', studiengang_kz);
	req.add('credit_points', credit_points);

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
		StudentKontoSelectBuchung=buchungsnr;
		StudentKontoTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Legt eine Gegenbuchung zu einer Buchung an
// ****
function StudentKontoGegenbuchung()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-konto-tree');

	if (tree.currentIndex==-1) return;

	StudentKontoDetailDisableFields(false);

	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
			for (var v = start.value; v <= end.value; v++)
			{
				var buchungsnr = getTreeCellText(tree, 'student-konto-tree-buchungsnr', v);
				paramList += ';'+buchungsnr;
			}
	}
		
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savegegenbuchung');

	req.add('buchungsnr', paramList);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response);
		else
			alert(val.dbdml_errormsg);
		StudentKontoTreeDatasource.Refresh(false); //non blocking
	}
	else
	{
		//StudentKontoSelectBuchung=val.dbdml_data;
		StudentKontoSelectBuchung=null;
		//StudentKontoTreeDatasource.Refresh(false); //non blocking
		//Hier wird der ganze Konto Tree Neu geladen da bei ein
		//normales Refresh hier nicht immer funktioniert
		StudentKontoLoad();
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Loescht eine Buchung
// ****
function StudentKontoDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-konto-tree');

	if (tree.currentIndex==-1) return;

	StudentKontoDetailDisableFields(false);

	//Ausgewaehlte Nr holen
	var buchungsnr = getTreeCellText(tree, 'student-konto-tree-buchungsnr', tree.currentIndex);

	if(confirm('Diese Buchung wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'deletebuchung');

		req.add('buchungsnr', buchungsnr);

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
			StudentSelectID=document.getElementById('student-prestudent-textbox-prestudent_id').value;
			StudentTreeDatasource.Refresh(false); //non blocking
			SetStatusBarText('Daten wurden gespeichert');
		}
	}
}

// ****
// * Ruft einen Dialog zum Anlegen von Buchungen auf
// ****
function StudentKontoNeu()
{
	window.open("<?php echo APP_ROOT; ?>content/student/studentkontoneudialog.xul.php","","chrome, status=no, width=500, height=350, centerscreen, resizable");
}

// ****
// * Speichert die Daten aus dem BuchungenDialog
// ****
function StudentKontoNeuSpeichern(dialog, person_ids, studiengang_kz)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	//Daten aus dem Dialog holen
	betrag = dialog.getElementById('student-konto-neu-textbox-betrag').value;
	buchungsdatum = dialog.getElementById('student-konto-neu-textbox-buchungsdatum').value;
	buchungstext = dialog.getElementById('student-konto-neu-textbox-buchungstext').value;
	mahnspanne = dialog.getElementById('student-konto-neu-textbox-mahnspanne').value;
	buchungstyp_kurzbz = dialog.getElementById('student-konto-neu-menulist-buchungstyp').value;
	studiensemester_kurzbz = dialog.getElementById('student-konto-neu-menulist-studiensemester').value;
	credit_points = dialog.getElementById('student-konto-neu-textbox-credit_points').value;
	
	if(buchungsdatum!='' && !CheckDatum(buchungsdatum))
	{
		alert('Buchungsdatum ist ungueltig');
		return false;
	}

	req.add('type', 'neuebuchung');

	req.add('person_ids', person_ids);
	req.add('studiengang_kz', studiengang_kz);
	req.add('betrag', betrag);
	req.add('buchungsdatum', ConvertDateToISO(buchungsdatum));
	req.add('buchungstext', buchungstext);
	req.add('mahnspanne', mahnspanne);
	req.add('buchungstyp_kurzbz', buchungstyp_kurzbz);
	req.add('studiensemester_kurzbz', studiensemester_kurzbz);
	req.add('credit_points', credit_points);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
		return false;
	}
	else
	{
		StudentKontoTreeDatasource.Refresh(false);
		return true;
	}
}

// *****
// * Druckt eine Zahlungsbestaetigung aus
// *****
function StudentKontoZahlungsbestaetigung()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-konto-tree');

	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
			for (var v = start.value; v <= end.value; v++)
			{
				var buchungsnr = getTreeCellText(tree, 'student-konto-tree-buchungsnr', v);
				paramList += ';'+buchungsnr;
			}
	}

	//Ausgewaehlte Nr holen
	var uid = document.getElementById('student-detail-textbox-uid').value;

	window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=konto.rdf.php&xsl=Zahlung&uid='+uid+'&buchungsnummern='+paramList,'Zahlungsbestaetigung', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}


// *********** Zeugnis *****************

// ****
// * Erstellt das Zeugnis fuer einen oder mehrere Studenten
// ****
function StudentCreateZeugnis(xsl,event)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	tree = document.getElementById('student-tree');

	//Markierte Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{			
			var uid = getTreeCellText(tree, 'student-treecol-uid', v);
			paramList += ';'+uid;
		}
	}
	//Studiensemester holen
	var ss = getStudiensemester();
	var xsl_stg_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value
	
	if(paramList.replace(";",'')=='')
	{
		alert('Bitte einen Studenten auswaehlen');
		return false;
	}
	var output = 'pdf';
	if(typeof(event)!=='undefined')
	{
		if (event.shiftKey) 
		{
		    var output = 'odt';
		} 
		else if (event.ctrlKey)
		{
			var output = 'doc';
		}
		else
		{
			var output = 'pdf';
		}
	}
	//PDF erzeugen
	window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=zeugnis.rdf.php&output='+output+'&xsl='+xsl+'&uid='+paramList+'&ss='+ss+'&xsl_stg_kz='+xsl_stg_kz,'Zeugnis', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ****
// * Erstellt das Sammelzeugnis fuer einen Studenten
// ****
function StudentCreateSammelzeugnis(xsl)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	tree = document.getElementById('student-tree');

	//Markierte Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{			
			var uid = getTreeCellText(tree, 'student-treecol-uid', v);
			paramList += ';'+uid;
		}
	}
	var xsl_stg_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value
	
	if(paramList.replace(";",'')=='')
	{
		alert('Bitte einen Studenten auswaehlen');
		return false;
	}
	
	//PDF erzeugen
	window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=sammelzeugnis.rdf.php&xsl='+xsl+'&uid='+paramList+'&xsl_stg_kz='+xsl_stg_kz,'Sammelzeugnis', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}
// ****
// * Laedt ein Zeugnis dass in der DB gespeichert ist
// ****
function StudentZeugnisAnzeigen()
{
	var tree = document.getElementById('student-zeugnis-tree');

	if (tree.currentIndex==-1) return;

	try
	{
		//Ausgewaehlte ID holen
		var akte_id = getTreeCellText(tree, 'student-zeugnis-tree-akte_id', tree.currentIndex);
		
		if(akte_id!='')
		{
			window.open('<?php echo APP_ROOT;?>content/akte.php?id='+akte_id,'File');
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
}

// ****
// * Loescht ein Dokument aus dem Archiv
// ****
function StudentAkteDel()
{

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-zeugnis-tree');

	if (tree.currentIndex==-1)
		return;

	try
	{
		//Ausgewaehlte Akte holen
		var akte_id = getTreeCellText(tree, 'student-zeugnis-tree-akte_id', tree.currentIndex);
	}
	catch(e)
	{
		alert(e);
		return false;
	}

	studiengang_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;
	//Abfrage ob wirklich geloescht werden soll
	if (confirm('Dokument wirklich entfernen?'))
	{
		//Script zum loeschen aufrufen
		var req = new phpRequest('student/studentDBDML.php','','');

		req.add('type','deleteAkte');
		req.add('akte_id',akte_id);
		req.add('studiengang_kz', studiengang_kz);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response)

		if(!val.dbdml_return)
			alert(val.dbdml_errormsg)

		StudentTreeRefresh();
	}
}

// ****
// * Deaktiviert die Felder
// ****
function StudentAkteDisableFields(val)
{
	document.getElementById('student-zeugnis-button-archivieren').disabled=val;
}

// ****
// * Startet das Script zum Archivieren des Zeugnisses und
// * Refresht dann den Tree
// ****
function StudentZeugnisArchivieren(lang)
{
	lang = lang || 'ger';
	
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1)
	{
		alert('Student muss ausgewaehlt sein');
		return;
	}
    	
	var tree=document.getElementById('student-tree');
	var numRanges = tree.view.selection.getRangeCount();
	var start = new Object();
	var end = new Object();
	var anzfault=0;
	var uid='';
	var errormsg = '';
	var stsem = getStudiensemester();
	
	//Zeugnis fuer alle markierten Studenten archivieren
	for (var t=0; t<numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			uid = getTreeCellText(tree, 'student-treecol-uid', v);
  			
			var xsl_vorlage;
			if(lang=='eng')
				xsl_vorlage = 'ZeugnisEng';
			else
				xsl_vorlage = 'Zeugnis';
  			url = '<?php echo APP_ROOT; ?>content/pdfExport.php?xsl='+xsl_vorlage+'&xml=zeugnis.rdf.php&uid='+uid+'&ss='+stsem+'&archive=1';

			var req = new phpRequest(url,'','');
		
			var response = req.execute();
			if(response!='')
				errormsg = errormsg + response;
  		}
	}
	
	if(errormsg!='')
		alert(errormsg);
			
	StudentAkteTreeDatasource.Refresh(false);    
}

// **************** Incomming/Outgoing ******************

// ****
// * Wenn ein IO Eintrag Ausgewaehlt wird, dann werden
// * die Details geladen und angezeigt
// ****
function StudentIOAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-io-tree');

	if (tree.currentIndex==-1) return;

	StudentIODetailDisableFields(false);

	//Ausgewaehlte ID holen
	var bisio_id = getTreeCellText(tree, 'student-io-tree-bisio_id', tree.currentIndex);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/bisio.rdf.php?bisio_id='+bisio_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/bisio/" + bisio_id);

	var predicateNS = "http://www.technikum-wien.at/bisio/rdf";

	//Daten holen

	mobilitaetsprogramm_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#mobilitaetsprogramm_code" ));
	nation_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nation_code" ));
	von = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#von" ));
	bis = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bis" ));
	zweck_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zweck_code" ));
	student_uid = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#student_uid" ));
	lehreinheit_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehreinheit_id" ));
	lehrveranstaltung_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehrveranstaltung_id" ));
	studiensemester_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiensemester_kurzbz" ));
	ort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ort" ));
	universitaet = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#universitaet" ));

	try
	{
		//Wenn nach dem Personen gesucht wurde, ist es moeglich, dass kein Studiengang gewaehlt ist.
		//Dann wird der Studiengang/Semester des Studenten genommen
		var verband_tree=document.getElementById('tree-verband');
		var stg_kz = getTreeCellText(verband_tree, 'stg_kz', verband_tree.currentIndex);
		var sem = getTreeCellText(verband_tree, 'sem', verband_tree.currentIndex);
	}
	catch(e)
	{	
		var stg_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;	
		var sem = document.getElementById('student-detail-textbox-semester').value;
	}

	//Lehrveranstaltung Drop Down laden
	var LVDropDown = document.getElementById('student-io-menulist-lehrveranstaltung');
	url='<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?stg_kz='+stg_kz+"&optional=true&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = LVDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LVDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LVDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	LVDropDown.database.AddDataSource(datasource);
	
	LVDropDown.builder.rebuild();


	//Lehreinheiten Drop Down laden
	var LEDropDown = document.getElementById('student-io-menulist-lehreinheit');
	url='<?php echo APP_ROOT;?>rdf/lehreinheit.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+"&studiensemester_kurzbz="+studiensemester_kurzbz+"&optional=true&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = LEDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LEDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LEDropDown.builder.rebuild();
	LEDropDown.selectedItem='';
	LEDropDown.value='';
	if(lehreinheit_id!='')
	{
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		var datasource = rdfService.GetDataSourceBlocking(url);
		datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		LEDropDown.database.AddDataSource(datasource);
	
		LEDropDown.builder.rebuild();
	}
		
	document.getElementById('student-io-menulist-mobilitaetsprogramm').value=mobilitaetsprogramm_code;
	document.getElementById('student-io-menulist-nation').value=nation_code;
	document.getElementById('student-io-textbox-von').value=von;
	document.getElementById('student-io-textbox-bis').value=bis;
	document.getElementById('student-io-menulist-zweck').value=zweck_code;
	document.getElementById('student-io-detail-textbox-uid').value=student_uid;
	document.getElementById('student-io-detail-checkbox-neu').checked=false;
	document.getElementById('student-io-detail-textbox-bisio_id').value=bisio_id;
	document.getElementById('student-io-textbox-ort').value=ort;
	document.getElementById('student-io-textbox-universitaet').value=universitaet;
	document.getElementById('student-io-menulist-lehreinheit').value=lehreinheit_id;
	document.getElementById('student-io-menulist-lehrveranstaltung').value=lehrveranstaltung_id;
}

// ****
// * Aktiviert / Deaktiviert die IO Felder
// ****
function StudentIODisableFields(val)
{
	document.getElementById('student-io-button-neu').disabled=val;
	document.getElementById('student-io-button-loeschen').disabled=val;
	StudentIODetailDisableFields(true);
}

// ****
// * Aktiviert / Deaktiviert die IO-Detail Felder
// ****
function StudentIODetailDisableFields(val)
{
	document.getElementById('student-io-textbox-von').disabled=val;
	document.getElementById('student-io-textbox-bis').disabled=val;
	document.getElementById('student-io-menulist-mobilitaetsprogramm').disabled=val;
	document.getElementById('student-io-menulist-nation').disabled=val;
	document.getElementById('student-io-menulist-zweck').disabled=val;
	document.getElementById('student-io-button-speichern').disabled=val;
	document.getElementById('student-io-menulist-lehrveranstaltung').disabled=val;
	document.getElementById('student-io-menulist-lehreinheit').disabled=val;
	document.getElementById('student-io-textbox-ort').disabled=val;
	document.getElementById('student-io-textbox-universitaet').disabled=val;
}

// *****
// * Resettet die Werte in den Detailfeldern des Incomming/Outgoing Moduls
// *****
function StudentIOResetFileds()
{
	document.getElementById('student-io-textbox-von').value='';
	document.getElementById('student-io-textbox-bis').value='';
	document.getElementById('student-io-menulist-mobilitaetsprogramm').value='6';
	document.getElementById('student-io-menulist-zweck').value='2';
	document.getElementById('student-io-menulist-nation').value='A';
	document.getElementById('student-io-textbox-ort').value='';
	document.getElementById('student-io-textbox-universitaet').value='';
}

// ****
// * Speichert den IO Datensatz
// ****
function StudentIODetailSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	von = document.getElementById('student-io-textbox-von').value;
	bis = document.getElementById('student-io-textbox-bis').value;
	mobilitaetsprogramm = document.getElementById('student-io-menulist-mobilitaetsprogramm').value;
	nation_code = document.getElementById('student-io-menulist-nation').value;
	zweck_code = document.getElementById('student-io-menulist-zweck').value;
	uid = document.getElementById('student-io-detail-textbox-uid').value;
	neu = document.getElementById('student-io-detail-checkbox-neu').checked;
	bisio_id = document.getElementById('student-io-detail-textbox-bisio_id').value;
	lehreinheit_id = document.getElementById('student-io-menulist-lehreinheit').value;
	ort = document.getElementById('student-io-textbox-ort').value;
	universitaet = document.getElementById('student-io-textbox-universitaet').value;

	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	
	if(von!='' && !CheckDatum(von))
	{
		alert('VON Datum ist ungueltig');
		return false;
	}

	if(bis!='' && !CheckDatum(bis))
	{
		alert('BIS Datum ist ungueltig');
		return false;
	}

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savebisio');

	if(neu==false)
		req.add('bisio_id', bisio_id);

	req.add('neu', neu);
	req.add('von', ConvertDateToISO(von));
	req.add('bis', ConvertDateToISO(bis));
	req.add('mobilitaetsprogramm_code', mobilitaetsprogramm);
	req.add('nation_code', nation_code);
	req.add('zweck_code', zweck_code);
	req.add('student_uid', uid);
	req.add('studiengang_kz', studiengang_kz);
	req.add('lehreinheit_id', lehreinheit_id);
	req.add('ort', ort);
	req.add('universitaet', universitaet);

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
		if(neu)
			StudentIOSelectID=val.dbdml_data;
		else
			StudentIOSelectID=bisio_id;
		StudentIOTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}

// ****
// * Loescht eines IO Eintrages
// ****
function StudentIODelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-io-tree');

	if (tree.currentIndex==-1) return;

	StudentIODetailDisableFields(false);

	//Ausgewaehlte Nr holen
	var bisio_id = getTreeCellText(tree, 'student-io-tree-bisio_id', tree.currentIndex);
	
	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	
	if(confirm('Diesen Eintrag wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'deletebisio');
		req.add('bisio_id', bisio_id);
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
			StudentIOSelectID=bisio_id;
			StudentIOTreeDatasource.Refresh(false); //non blocking
			SetStatusBarText('Daten wurden geloescht');
			StudentIOResetFileds();
			StudentIODetailDisableFields(true);
		}
	}
}

// ****
// * Aktiviert die Felder zum Anlegen eines neuen Eintrages
// ****
function StudentIONeu()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	//Felder Resetten und Aktivieren
	StudentIOResetFileds();
	StudentIODetailDisableFields(false);

	var now = new Date();
	var jahr = now.getFullYear();

	var monat = now.getMonth()+1;

	if(monat<10)
		monat='0'+monat;
	var tag = now.getDate();
	if(tag<10)
		tag='0'+tag;

	//UID ins Textfeld schreiben
	document.getElementById('student-io-detail-textbox-uid').value=document.getElementById('student-detail-textbox-uid').value;
	document.getElementById('student-io-detail-checkbox-neu').checked=true;
	document.getElementById('student-io-textbox-von').value=tag+'.'+monat+'.'+jahr;
	document.getElementById('student-io-textbox-bis').value=tag+'.'+monat+'.'+jahr;
	
	try
	{
		//Wenn nach dem Personen gesucht wurde, ist es moeglich, dass kein Studiengang gewaehlt ist.
		//Dann wird der Studiengang/Semester des Studenten genommen
		var verband_tree=document.getElementById('tree-verband');
		
		var stg_kz = getTreeCellText(verband_tree, 'stg_kz', verband_tree.currentIndex);
		var sem = getTreeCellText(verband_tree, 'sem', verband_tree.currentIndex);
	}
	catch(e)
	{	
		var stg_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;	
		var sem = document.getElementById('student-detail-textbox-semester').value;
	}
	
	//Lehrveranstaltung Drop Down laden
	var LVDropDown = document.getElementById('student-io-menulist-lehrveranstaltung');
	url='<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?stg_kz='+stg_kz+"&sem="+sem+"&optional=true&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = LVDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LVDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LVDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	LVDropDown.database.AddDataSource(datasource);
	LVDropDown.value='';
	//LVDropDown.selectedItem='';
	
	var LEDropDown = document.getElementById('student-io-menulist-lehreinheit');
	
	//Alte DS entfernen
	var oldDatasources = LEDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LEDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LEDropDown.builder.rebuild();
	
	LEDropDown.value='';
	LEDropDown.selectedItem='';
}

// ****
// * Selectiert den Incoming/Outgoing Eintrag nachdem der Tree
// * rebuildet wurde.
// ****
function StudentIOTreeSelectID()
{
	var tree=document.getElementById('student-io-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentIOSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			var bisio_id = getTreeCellText(tree, 'student-io-tree-bisio_id', i);

			//wenn dies die zu selektierende Zeile
			if(bisio_id == StudentIOSelectID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentIOSelectID=null;
				return true;
			}
	   	}
	}
}


// ****
// * Wenn die Lehrvernastaltung des IO Eintrages geaendert wird, dann wird die Liste der Lehreinheiten neu geladen
// ****
function StudentIOLVAChange()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	var lvid = document.getElementById('student-io-menulist-lehrveranstaltung').value;
	var stsem = getStudiensemester();


	//Lehreinheiten Drop Down laden
	var LEDropDown = document.getElementById('student-io-menulist-lehreinheit');
	url='<?php echo APP_ROOT;?>rdf/lehreinheit.rdf.php?lehrveranstaltung_id='+lvid+"&studiensemester_kurzbz="+stsem+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = LEDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LEDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LEDropDown.builder.rebuild();
	
	if(lvid!='')
	{
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		var datasource = rdfService.GetDataSource(url);
		LEDropDown.database.AddDataSource(datasource);
	}	
	
	//Lehreinheiten DropDown Auswahl leeren
	LEDropDown.selectedIndex=-1;
	
}

// **************** NOTEN ************** //

// ****
// * Selectiert den Noten Eintrag nachdem der Tree
// * rebuildet wurde.
// ****
function StudentNotenTreeSelectID()
{
	StudentNotenTreeSelectDifferent();
	var tree=document.getElementById('student-noten-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentNotenSelectLehrveranstaltungID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["student-noten-tree-lehrveranstaltung_id"] : "student-noten-tree-lehrveranstaltung_id";
			var lehrveranstaltung_id=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(lehrveranstaltung_id == StudentNotenSelectLehrveranstaltungID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentNotenSelectLehrveranstaltungID=null;
				StudentNotenSelectStudentUID=null;
				return true;
			}
	   	}
	}
}

// ****
// * Selectiert die Noten im LVGesamtNoteTree welche nicht gleich denen 
// * im ZeugnisNoteTree sind
// ****
function StudentNotenTreeSelectDifferent()
{
	var zeugnistree = document.getElementById("student-noten-tree");
	var lvgesamttree = document.getElementById("student-lvgesamtnoten-tree");
	//bestehende markierung entfernen
	lvgesamttree.view.selection.clearSelection();

	if(StudentNotenTreeloaded && StudentGesamtNotenTreeloaded)
	{
		if(lvgesamttree.view)
			var lvgesamtitems = lvgesamttree.view.rowCount; //Anzahl der Zeilen ermitteln
		else
			return false;
			
		if(zeugnistree.view)
			var zeugnisitems = zeugnistree.view.rowCount; //Anzahl der Zeilen ermitteln
		else
			return false;
			
		for(var i=0;i<lvgesamtitems;i++)
	   	{
	   		//Daten aus LVGesamtNotenTree holen
			col = lvgesamttree.columns ? lvgesamttree.columns["student-lvgesamtnoten-tree-lehrveranstaltung_id"] : "student-lvgesamtnoten-tree-lehrveranstaltung_id";
			var lvgesamtlehrveranstaltung_id=lvgesamttree.view.getCellText(i,col);
			col = lvgesamttree.columns ? lvgesamttree.columns["student-lvgesamtnoten-tree-note"] : "student-lvgesamtnoten-tree-note";
			var lvgesamtnote=lvgesamttree.view.getCellText(i,col);
			col = lvgesamttree.columns ? lvgesamttree.columns["student-lvgesamtnoten-tree-benotungsdatum-iso"] : "student-lvgesamtnoten-tree-benotungsdatum-iso";
			var lvgesamtbenotungsdatum=lvgesamttree.view.getCellText(i,col);

			found=false;
			//Schauen ob die gleiche Zeile im Zeugnisnoten Tree vorkommt
			for(var j=0;j<zeugnisitems;j++)
			{
				col = zeugnistree.columns ? zeugnistree.columns["student-noten-tree-lehrveranstaltung_id"] : "student-noten-tree-lehrveranstaltung_id";
				var zeugnislehrveranstaltung_id=zeugnistree.view.getCellText(j,col);
				col = zeugnistree.columns ? zeugnistree.columns["student-noten-tree-note"] : "student-noten-tree-note";
				var zeugnisnote=zeugnistree.view.getCellText(j,col);
				col = zeugnistree.columns ? zeugnistree.columns["student-noten-tree-benotungsdatum-iso"] : "student-noten-tree-benotungsdatum-iso";
				var zeugnisbenotungsdatum=zeugnistree.view.getCellText(j,col);
				
				if(zeugnislehrveranstaltung_id==lvgesamtlehrveranstaltung_id && zeugnisnote==lvgesamtnote && zeugnisbenotungsdatum==lvgesamtbenotungsdatum)
				{
					found=true;
					break;
				}
				
				//Wenn die Noten unterschiedlich sind, aber das benotungsdatum im Zeugnis
				//nach dem benotungsdatum des lektors liegt, dann wird die zeile auch nicht markiert.
				//damit wird verhindert, dass pruefungsnoten die nur von der assistenz eingetragen wurden,
				//durch den alten eintrag des lektors wieder ueberschrieben werden
				if(zeugnislehrveranstaltung_id==lvgesamtlehrveranstaltung_id 
					&& zeugnisnote!=lvgesamtnote
					&& zeugnisbenotungsdatum>lvgesamtbenotungsdatum)
				{
					found=true;
					break;
				}
			}
			
			if(!found)
			{
				//Zeile markieren
				lvgesamttree.view.selection.rangedSelect(i,i,true);
			}
	   	}
	}
}

// ****
// * Selectiert den Noten Eintrag nachdem der Tree
// * rebuildet wurde.
// ****
function StudentLvGesamtNotenTreeSelectID()
{
	StudentNotenTreeSelectDifferent();
	
	/*
	var tree=document.getElementById('student-lvgesamtnoten-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentLvGesamtNotenSelectLehrveranstaltungID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["student-lvgesamtnoten-tree-lehrveranstaltung_id"] : "student-lvgesamtnoten-tree-lehrveranstaltung_id";
			var lehrveranstaltung_id=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(lehrveranstaltung_id == StudentLvGesamtNotenSelectLehrveranstaltungID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentNotenSelectLehrveranstaltungID=null;
				StudentNotenSelectStudentUID=null;
				return true;
			}
	   	}
	}*/
}

// ***
// * Disabled/Enabled die Nodenfelder
// ***
function StudentNoteDisableFields(val)
{
	document.getElementById('student-note-copy').disabled=val;
}

// ***
// * Disabled/Enabled die Detailfelder
// ***
function StudentNoteDetailDisableFields(val)
{
	document.getElementById('student-noten-menulist-note').disabled=val;
	document.getElementById('student-noten-button-speichern').disabled=val;
}

// ***
// * Nach dem Auswaehlen einer Note kann diese veraendert werden
// ***
function StudentNotenAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-noten-tree');

	if (tree.currentIndex==-1) return;

	StudentNoteDetailDisableFields(false);

	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-noten-tree-lehrveranstaltung_id"] : "student-noten-tree-lehrveranstaltung_id";
	var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["student-noten-tree-student_uid"] : "student-noten-tree-student_uid";
	var student_uid=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["student-noten-tree-studiensemester_kurzbz"] : "student-noten-tree-studiensemester_kurzbz";
	var studiensemester_kurzbz=tree.view.getCellText(tree.currentIndex,col);

	//Falls einer der Parameter leer ist wird abgebrochen da sonst ein sehr grosses rdf geladen wird
	//Sollte eigentlich nie eintreffen, tut es aber trotzdem
	if(lehrveranstaltung_id=='' || student_uid=='' || studiensemester_kurzbz=='')
	{
		debug('unerwarteter Fehler in StudentNotenAuswahl() in studentoverlay.js.php');
		return false;
	}
	
	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/zeugnisnote.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+'&uid='+student_uid+'&studiensemester_kurzbz='+studiensemester_kurzbz+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/zeugnisnote/" + lehrveranstaltung_id+'/'+student_uid+'/'+studiensemester_kurzbz);

	var predicateNS = "http://www.technikum-wien.at/zeugnisnote/rdf";

	//Daten holen

	note = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#note" ));

	if(note=='')
		note='9';

	document.getElementById('student-noten-menulist-note').value=note;
}

// ****
// * Speichert eine Note
// ****
function StudentNoteSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-noten-tree');

	if (tree.currentIndex==-1)
	{
		alert('Speichern nicht moeglich! Es muss eine Note im Tree ausgewaehlt sein');
		return;
	}

	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-noten-tree-lehrveranstaltung_id"] : "student-noten-tree-lehrveranstaltung_id";
	var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["student-noten-tree-student_uid"] : "student-noten-tree-student_uid";
	var student_uid=tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["student-noten-tree-studiensemester_kurzbz"] : "student-noten-tree-studiensemester_kurzbz";
	var studiensemester_kurzbz=tree.view.getCellText(tree.currentIndex,col);

	note = document.getElementById('student-noten-menulist-note').value;


	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savenote');

	req.add('lehrveranstaltung_id', lehrveranstaltung_id);
	req.add('student_uid', student_uid);
	req.add('studiensemester_kurzbz', studiensemester_kurzbz);
	req.add('note', note);

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
		StudentLvGesamtNotenSelectLehrveranstaltungID=lehrveranstaltung_id;
		StudentNotenTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
		StudentNoteDetailDisableFields(true);
	}
}

// ****
// * Uebernimmt die Noten der Lektoren fuer die Zeugnisnote
// ****
function StudentNotenMove()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-lvgesamtnoten-tree');

	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var i = 0;

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'movenote');

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			col = tree.columns ? tree.columns["student-lvgesamtnoten-tree-lehrveranstaltung_id"] : "student-lvgesamtnoten-tree-lehrveranstaltung_id";
			lehrveranstaltung_id = tree.view.getCellText(v,col);
			col = tree.columns ? tree.columns["student-lvgesamtnoten-tree-student_uid"] : "student-lvgesamtnoten-tree-student_uid";
			student_uid = tree.view.getCellText(v,col);
			col = tree.columns ? tree.columns["student-lvgesamtnoten-tree-studiensemester_kurzbz"] : "student-lvgesamtnoten-tree-studiensemester_kurzbz";
			studiensemester_kurzbz = tree.view.getCellText(v,col);

			req.add('lehrveranstaltung_id_'+i, lehrveranstaltung_id);
			req.add('student_uid_'+i, student_uid);
			req.add('studiensemester_kurzbz_'+i, studiensemester_kurzbz);
			i++;
		}
	}
	req.add('anzahl', i);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response);
		else
			alert(val.dbdml_errormsg);
			
		StudentNotenTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
		StudentNoteDetailDisableFields(true);
	}
	else
	{
		StudentNotenTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
		StudentNoteDetailDisableFields(true);
	}
}

// ****
// * Loescht die markierte Note
// ****
function StudentNotenDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-noten-tree');
			
	col = tree.columns ? tree.columns["student-noten-tree-student_uid"] : "student-noten-tree-student_uid";
	uid = tree.view.getCellText(tree.currentIndex,col);
	
	col = tree.columns ? tree.columns["student-noten-tree-lehrveranstaltung_id"] : "student-noten-tree-lehrveranstaltung_id";
	lvid = tree.view.getCellText(tree.currentIndex,col);
	
	col = tree.columns ? tree.columns["student-noten-tree-studiensemester_kurzbz"] : "student-noten-tree-studiensemester_kurzbz";
	stsem = tree.view.getCellText(tree.currentIndex,col);
	
	if(confirm('Wollen Sie diese Note wirklich löschen'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');
	
		req.add('type', 'deletenote');
	
		req.add('lehrveranstaltung_id', lvid);
		req.add('student_uid', uid);
		req.add('studiensemester_kurzbz', stsem);
		
		var response = req.executePOST();
	
		var val =  new ParseReturnValue(response)
	
		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response);
			else
				alert(val.dbdml_errormsg);
				
			StudentNotenTreeDatasource.Refresh(false); //non blocking
			StudentNoteDetailDisableFields(true);
		}
		else
		{
			StudentNotenTreeDatasource.Refresh(false); //non blocking
			SetStatusBarText('Eintrag wurde geloescht');
			StudentNoteDetailDisableFields(true);
		}
	}
}


// **************** PRUEFUNG ************** //

// ****
// * Selectiert den Pruefung Eintrag nachdem der Tree
// * rebuildet wurde.
// ****
function StudentPruefungTreeSelectID()
{
	var tree=document.getElementById('student-pruefung-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentPruefungSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["student-pruefung-tree-pruefung_id"] : "student-pruefung-tree-pruefung_id";
			var pruefung_id=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(pruefung_id == StudentPruefungSelectID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentPruefungSelectID=null;
				return true;
			}
	   	}
	}
}

// ****
// * De-/Aktiviert die Pruefungsfelder
// ****
function StudentPruefungDisableFileds(val)
{
	document.getElementById('student-pruefung-button-neu').disabled = val;
	document.getElementById('student-pruefung-button-loeschen').disabled= val;

	if(val)
		StudentPruefungDetailDisableFields(val);
}

// ****
// * De-/Aktiviert die PruefungsDetailFelder
// ****
function StudentPruefungDetailDisableFields(val)
{
	document.getElementById('student-pruefung-menulist-lehrveranstaltung').disabled=val;
	document.getElementById('student-pruefung-menulist-lehreinheit').disabled=val;
	document.getElementById('student-pruefung-menulist-mitarbeiter').disabled=val;
	document.getElementById('student-pruefung-menulist-typ').disabled=val;
	document.getElementById('student-pruefung-menulist-note').disabled=val;
	document.getElementById('student-pruefung-textbox-datum').disabled=val;
	document.getElementById('student-pruefung-textbox-anmerkung').disabled=val;
	document.getElementById('student-pruefung-button-speichern').disabled=val;
}

// ****
// * Loescht eine Pruefung
// ****
function StudentPruefungDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-pruefung-tree');

	if (tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return;
	}

	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-pruefung-tree-pruefung_id"] : "student-pruefung-tree-pruefung_id";
	var pruefung_id=tree.view.getCellText(tree.currentIndex,col);
	studiengang_kz=document.getElementById('student-prestudent-menulist-studiengang_kz').value;

	if(confirm('Diesen Eintrag wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'deletepruefung');

		req.add('pruefung_id', pruefung_id);
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
			StudentPruefungTreeDatasource.Refresh(false); //non blocking
			SetStatusBarText('Daten wurden geloescht');
			StudentPruefungDetailDisableFields(true);
		}
	}
}

// ****
// * Aktiviert die Felder um eine Neue Pruefung anzulegen
// ****
function StudentPruefungNeu()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	StudentPruefungDetailDisableFields(false);
	
	document.getElementById('student-pruefung-checkbox-neu').checked=true;

	//wenn im Tree eine pruefung markiert ist, und auf neu gedrueckt wird,
	//dann wird die LV/LE und der Mitarbeiter dieser Pruefung schon vorausgewaehlt
	//ist keiner markiert werden alle eingabefelder geloescht/neu geladen
	var tree = document.getElementById('student-pruefung-tree');
	if (tree.currentIndex!=-1)
		vorlage=true;
	else
		vorlage=false;

	if(!vorlage)
	{
		//Lehrveranstaltung Drop Down laden
		var LVDropDown = document.getElementById('student-pruefung-menulist-lehrveranstaltung');
		var uid = document.getElementById('student-detail-textbox-uid').value;
		url="<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?uid="+uid+"&"+gettimestamp();
	
		//Alte DS entfernen
		var oldDatasources = LVDropDown.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			LVDropDown.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		LVDropDown.builder.rebuild();
	
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		var datasource = rdfService.GetDataSource(url);
		LVDropDown.database.AddDataSource(datasource);
		LVDropDown.value='';
		LVDropDown.selectedItem='';
	}
	
	var LEDropDown = document.getElementById('student-pruefung-menulist-lehreinheit');
	
	if(!vorlage)
	{
		//Alte DS entfernen
		var oldDatasources = LEDropDown.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			LEDropDown.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		LEDropDown.builder.rebuild();
		
		LEDropDown.value='';
		LEDropDown.selectedItem='';
	}

	var MADropDown = document.getElementById('student-pruefung-menulist-mitarbeiter');

	if(!vorlage)
	{
		//Alte DS entfernen
		var oldDatasources = MADropDown.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			MADropDown.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		MADropDown.builder.rebuild();
		
		MADropDown.value='';
		MADropDown.selectedItem='';
	}
	
	document.getElementById('student-pruefung-menulist-typ').value='';
	document.getElementById('student-pruefung-menulist-typ').selectedItem='';
	document.getElementById('student-pruefung-menulist-note').value='9';
	document.getElementById('student-pruefung-textbox-datum').value='<?php echo date('d.m.Y');?>';
	document.getElementById('student-pruefung-textbox-anmerkung').value='';
}

// ****
// * Wenn die Lehrveranstaltung der Pruefung geaendert wird, dann wird die Liste der Lehreinheiten neu geladen
// ****
function StudentPruefungLVAChange()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	var lvid = document.getElementById('student-pruefung-menulist-lehrveranstaltung').value;
	var stsem = getStudiensemester();
	
	//Lehreinheiten Drop Down laden
	var LEDropDown = document.getElementById('student-pruefung-menulist-lehreinheit');
	url='<?php echo APP_ROOT;?>rdf/lehreinheit.rdf.php?lehrveranstaltung_id='+lvid+"&studiensemester_kurzbz="+stsem+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = LEDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LEDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LEDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	LEDropDown.database.AddDataSource(datasource);

	//Mitarbeiter Drop Down laden
	var MADropDown = document.getElementById('student-pruefung-menulist-mitarbeiter');
	url='<?php echo APP_ROOT;?>rdf/mitarbeiter.rdf.php?lehrveranstaltung_id='+lvid+"&optional=true&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = MADropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		MADropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	MADropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	MADropDown.database.AddDataSource(datasource);
	
	//Lehreinheiten und Mitarbeiter DropDown Auswahl leeren
	MADropDown.selectedIndex=-1;
	LEDropDown.selectedIndex=-1;
}

// ****
// * Speichert die Pruefung
// ****
function StudentPruefungDetailSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	lehreinheit_id = document.getElementById('student-pruefung-menulist-lehreinheit').value;
	mitarbeiter_uid = document.getElementById('student-pruefung-menulist-mitarbeiter').value;
	pruefungstyp_kurzbz = document.getElementById('student-pruefung-menulist-typ').value;
	note = document.getElementById('student-pruefung-menulist-note').value;
	datum = document.getElementById('student-pruefung-textbox-datum').value;
	anmerkung = document.getElementById('student-pruefung-textbox-anmerkung').value;
	neu = document.getElementById('student-pruefung-checkbox-neu').checked;
	pruefung_id = document.getElementById('student-pruefung-textbox-pruefung_id').value;
	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;

	if(lehreinheit_id=='')
	{
		alert('Es muss eine Lehreinheit ausgewaehlt werden');
		return false;
	}
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1)
	{
		alert('Student muss ausgewaehlt sein');
		return;
	}
    var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
	var student_uid=tree.view.getCellText(tree.currentIndex,col);

	if(datum!='' && !CheckDatum(datum))
	{
		alert('Datum ist ungueltig');
		return false;
	}
	
	if(pruefungstyp_kurzbz=='')
	{
		alert('Pruefungstyp muss eingetragen werden');
		return false;
	}

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savepruefung');

	req.add('lehreinheit_id', lehreinheit_id);
	req.add('mitarbeiter_uid', mitarbeiter_uid);
	req.add('pruefungstyp_kurzbz', pruefungstyp_kurzbz);
	req.add('note', note);
	req.add('datum', ConvertDateToISO(datum));
	req.add('anmerkung', anmerkung);
	req.add('neu', neu);
	req.add('pruefung_id', pruefung_id);
	req.add('student_uid', student_uid);
	req.add('studiengang_kz', studiengang_kz);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
		
		StudentPruefungSelectID=val.dbdml_data;
		StudentPruefungTreeDatasource.Refresh(false); //non blocking
	}
	else
	{
		StudentPruefungSelectID=val.dbdml_data;
		StudentPruefungTreeDatasource.Refresh(false); //non blocking
		//Notentree Refreshen
		StudentNotenTreeDatasource.Refresh(false); //non blocking
		
		SetStatusBarText('Daten wurden gespeichert');
		StudentPruefungDetailDisableFields(true);
		
	}
}

// ****
// * Laedt eine Pruefung zum Bearbeiten
// ****
function StudentPruefungAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-pruefung-tree');

	if (tree.currentIndex==-1) return;

	StudentPruefungDetailDisableFields(false);
	
	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["student-pruefung-tree-pruefung_id"] : "student-pruefung-tree-pruefung_id";
	var pruefung_id=tree.view.getCellText(tree.currentIndex,col);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/pruefung.rdf.php?pruefung_id='+pruefung_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/pruefung/" + pruefung_id);

	var predicateNS = "http://www.technikum-wien.at/pruefung/rdf";

	//Daten holen

	lehreinheit_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehreinheit_id" ));
	lehrveranstaltung_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehrveranstaltung_id" ));
	mitarbeiter_uid = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#mitarbeiter_uid" ));
	note = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#note" ));
	pruefungstyp_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#pruefungstyp_kurzbz" ));
	datum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#datum" ));
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	studiensemester_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiensemester_kurzbz" ));

	try
	{
		//Wenn nach dem Personen gesucht wurde, ist es moeglich, dass kein Studiengang gewaehlt ist.
		//Dann wird der Studiengang/Semester des Studenten genommen
		var verband_tree=document.getElementById('tree-verband');
		var col = verband_tree.columns ? verband_tree.columns["stg_kz"] : "stg_kz";
		var stg_kz=verband_tree.view.getCellText(verband_tree.currentIndex,col);
		
		col = verband_tree.columns ? verband_tree.columns["sem"] : "sem";
		var sem=verband_tree.view.getCellText(verband_tree.currentIndex,col);
	}
	catch(e)
	{	
		var stg_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;	
		var sem = document.getElementById('student-detail-textbox-semester').value;
	}

	//Lehrveranstaltung Drop Down laden
	var LVDropDown = document.getElementById('student-pruefung-menulist-lehrveranstaltung');
	//url='<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?stg_kz='+stg_kz+"&"+gettimestamp();
	var uid = document.getElementById('student-detail-textbox-uid').value;
	url="<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?uid="+uid+"&"+gettimestamp();


	//Alte DS entfernen
	var oldDatasources = LVDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LVDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LVDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	LVDropDown.database.AddDataSource(datasource);
	
	LVDropDown.builder.rebuild();

	//Lehreinheiten Drop Down laden
	var LEDropDown = document.getElementById('student-pruefung-menulist-lehreinheit');
	url='<?php echo APP_ROOT;?>rdf/lehreinheit.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+"&studiensemester_kurzbz="+studiensemester_kurzbz+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = LEDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LEDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LEDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	LEDropDown.database.AddDataSource(datasource);

	LEDropDown.builder.rebuild();

	//Mitarbeiter Drop Down laden
	var MADropDown = document.getElementById('student-pruefung-menulist-mitarbeiter');
	url='<?php echo APP_ROOT;?>rdf/mitarbeiter.rdf.php?lehrveranstaltung_id='+lehrveranstaltung_id+"&optional=true&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = MADropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		MADropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	MADropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	MADropDown.database.AddDataSource(datasource);
	
	MADropDown.builder.rebuild();
	
	// Pruefen ob der zugeteilte Mitarbeiter in dem Dropdown vorhanden ist, ansonsten wird er zusaetzlich geladen
	// das kann der Fall sein, wenn jemand uebers CIS eine Pruefung Eintraegt der nicht Lektor der LV ist (z.B. Admin)
	var children = document.getElementById('student-pruefung-menulist-mitarbeiter').getElementsByAttribute('value',mitarbeiter_uid);
	if(children.length == 0)
	{
		url='<?php echo APP_ROOT;?>rdf/mitarbeiter.rdf.php?mitarbeiter_uid='+mitarbeiter_uid+"&"+gettimestamp();
		var datasource = rdfService.GetDataSourceBlocking(url);
		datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		MADropDown.database.AddDataSource(datasource);
		MADropDown.builder.rebuild();
	}
		
	document.getElementById('student-pruefung-menulist-lehrveranstaltung').value=lehrveranstaltung_id;
	document.getElementById('student-pruefung-menulist-lehreinheit').value=lehreinheit_id;
	document.getElementById('student-pruefung-menulist-mitarbeiter').value=mitarbeiter_uid;
	document.getElementById('student-pruefung-menulist-typ').value=pruefungstyp_kurzbz;
	document.getElementById('student-pruefung-menulist-note').value=note;
	document.getElementById('student-pruefung-textbox-datum').value=datum;
	document.getElementById('student-pruefung-textbox-anmerkung').value=anmerkung;
	document.getElementById('student-pruefung-checkbox-neu').checked=false;
	document.getElementById('student-pruefung-textbox-pruefung_id').value=pruefung_id;
}

// ****
// * Startet die Personensuche
// ****
function StudentSuche()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	filter = document.getElementById('student-toolbar-textbox-suche').value;
	
	//Wenn mehr als 2 Zeichen eingegeben wurden, die Personensuche starten
	if(filter.length>2)
	{
		//Bei der Suche wird die Markierung vom Verband Tree entfernt da
		//es sonst zu Problemen kommen kann
		document.getElementById('tree-verband').currentIndex=-1;
		document.getElementById('tree-verband').view.selection.clearSelection();
		//Export deaktivieren
		//document.getElementById('student-toolbar-export').disabled=true;

		//Datasource setzten und Felder deaktivieren
		url = "<?php echo APP_ROOT; ?>rdf/student.rdf.php?filter="+encodeURIComponent(filter)+"&"+gettimestamp();
		
		var treeStudent=document.getElementById('student-tree');
	
		try
		{
			StudentTreeDatasource.removeXMLSinkObserver(StudentTreeSinkObserver);
			treeStudent.builder.removeListener(StudentTreeListener);
		}
		catch(e)
		{}
		
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
	
		//Detailfelder Deaktivieren
		try
		{
			StudentDisableDetails();
		}
		catch(e){}
	}
	else
		alert('Es muessen mindestens 3 Zeichen eingegeben werden');		
}

function StudentDisableDetails()
{
	StudentDetailReset();
	StudentDetailDisableFields(true);
	StudentPrestudentDisableFields(true);
	StudentKontoDisableFields(true);
	StudentAkteDisableFields(true);
	document.getElementById('student-betriebsmittel').setAttribute('src','');
	StudentIODisableFields(true);
	StudentNoteDisableFields(true);
	document.getElementById('student-kontakt').setAttribute('src','');
	StudentAbschlusspruefungDisableFields(true);
	StudentProjektarbeitDisableAll();
}

// ****
// * Wenn im Suchfeld Enter gedrueckt wird, dann die Suchfunktion starten
// ****
function StudentSearchFieldKeyPress(event)
{
	if(event.keyCode==13) //Enter
		StudentSuche();
}

// ****
// * Email an die markierten Studenten versenden
// ****
function StudentSendMail()
{
	mailempfaenger='';
	var tree=document.getElementById('student-tree');
	var numRanges = tree.view.selection.getRangeCount();
	var start = new Object();
	var end = new Object();
	var anzfault=0;
	//Markierte Datensaetze holen
	for (var t=0; t<numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
  			if(tree.view.getCellText(v,col).length>1)
  			{
  				if(mailempfaenger=='')
  					mailempfaenger=tree.view.getCellText(v,col)+'@<?php echo DOMAIN; ?>';
  				else
					mailempfaenger=mailempfaenger+'<?php echo $variable->variable->emailadressentrennzeichen; ?>'+tree.view.getCellText(v,col)+'@<?php echo DOMAIN; ?>';
  			}
  			else
  			{
  				anzfault=anzfault+1;
  			}
  		}
	}
	if(anzfault!=0)
		alert(anzfault+' Student(en) konnten nicht hinzugefuegt werden weil keine UID eingetragen ist!');
	if(mailempfaenger!='')
		splitmailto(mailempfaenger,'to');
}

// ****
// * Email an die Privatadresse markierten Studenten versenden
// ****
function StudentSendMailPrivat()
{
	var tree=document.getElementById('student-tree');
	var numRanges = tree.view.selection.getRangeCount();
	var start = new Object();
	var end = new Object();	
	var person_ids='';
	
	//Markierte Datensaetze holen
	for (var t=0; t<numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			var col = tree.columns ? tree.columns["student-treecol-person_id"] : "student-treecol-person_id";
  			person_ids=person_ids+';'+tree.view.getCellText(v,col);
  		}
	}
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'getprivatemailadress');
	req.add('person_ids', person_ids);
	
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
		{
			alert(val.dbdml_errormsg)
			if(val.dbdml_data!='')
				splitmailto(val.dbdml_data,'bcc');
		}
	}
	else
	{
		if(val.dbdml_data!='')
			splitmailto(val.dbdml_data,'bcc');
	}	
}

// ****
// * Teilt die Mailto Links auf kleinere Brocken auf, da der
// * Link nicht funktioniert wenn er zu lange ist
// * art = to | cc | bcc
// ****
function splitmailto(mails, art)
{
	var splititem = '<?php echo $variable->variable->emailadressentrennzeichen; ?>';
	var splitposition=0;
	var mailto='';
	var loop=true;
	if(mails.length>2048)
		alert('Aufgrund der großen Anzahl an Empfängern, muss die Nachricht auf mehrere E-Mails aufgeteilt werden!');
	
	while(loop)
	{
		if(mails.length>2048)
		{
			splitposition=mails.indexOf(splititem,1900);
			mailto = mails.substring(0,splitposition);
			mails = mails.substring(splitposition);
		}
		else
		{
			loop=false;
			mailto=mails;
		}
		
		if(art=='to')
			window.location.href='mailto:'+mailto;
		else
			window.location.href='mailto:?'+art+'='+mailto;
		
		
	}
}

// ****
// * Oeffnet ein Fenster mit den Details der gesamten Person
// ****
function StudentShowPersonendetails()
{
	person_id = document.getElementById('student-detail-textbox-person_id').value;
	window.open('<?php echo APP_ROOT ?>vilesci/personen/personendetails.php?id='+person_id,'Personendetails','');
}

// ****
// * Erstellt das Diploma Supplement fuer einen oder mehrere Studenten
// ****
function StudentCreateDiplSupplement(event)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	tree = document.getElementById('student-tree');

	//Markierte Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
			var uid=tree.view.getCellText(v,col);
			paramList += ';'+uid;
			stg_kz=getTreeCellText(tree,"student-treecol-studiengang_kz", v);
		}
	}
	
	if(paramList.replace(";",'')=='')
	{
		alert('Bitte einen Studenten auswaehlen');
		return false;
	}
	if (event.shiftKey) 
	{
	    var output='odt';
	} 
	else if (event.ctrlKey)
	{
		var output='doc';
	}
	else
	{
		var output='pdf';
	}
	//PDF erzeugen 
	window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=diplomasupplement.xml.php&output='+output+'&xsl=DiplSupplement&xsl_stg_kz='+stg_kz+'&uid='+paramList,'DiplomaSupplement', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ****
// * Archiviert das Diplomasupplement einer Person
// ****
function StudentDiplomasupplementArchivieren()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	tree = document.getElementById('student-tree');

	//Markierte Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';
	var errormsg='';

	var labelalt = document.getElementById('student-zeugnis-button-archivieren-diplomasupplement').label;
	document.getElementById('student-zeugnis-button-archivieren-diplomasupplement').label='Loading...';
	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
			var uid=tree.view.getCellText(v,col);
			stg_kz=getTreeCellText(tree,"student-treecol-studiengang_kz", v);

			url = '<?php echo APP_ROOT; ?>content/pdfExport.php?xml=diplomasupplement.xml.php&output=pdf&xsl=DiplSupplement&xsl_stg_kz='+stg_kz+'&uid='+uid+'&archive=true';
			var req = new phpRequest(url,'','');
		
			var response = req.execute();
			if(response!='')
				errormsg = errormsg + response;
		}
	}

	document.getElementById('student-zeugnis-button-archivieren-diplomasupplement').label=labelalt;
	StudentAkteTreeDatasource.Refresh(false);   
}

// ****
// * Erstellt den Ausbildungsvertrag fuer einen oder mehrere Studenten
// ****
function StudentPrintAusbildungsvertrag()
{
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	tree = document.getElementById('student-tree');

	//Markierte Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
			var uid=tree.view.getCellText(v,col);
			paramList += ';'+uid;
			stg_kz=getTreeCellText(tree,"student-treecol-studiengang_kz", v);
		}
	}
	
	if(paramList.replace(";",'')=='')
	{
		alert('Bitte einen Studenten auswaehlen');
		return false;
	}
	
	//PDF erzeugen 
	window.open('<?php echo APP_ROOT; ?>content/createAusbildungsvertrag.php?xml=ausbildungsvertrag.xml.php&xsl=Ausbildungsver&output=pdf&uid='+paramList,'Ausbildungsvertrag', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

//****
//* Erstellt den englischen Ausbildungsvertrag fuer einen oder mehrere Studenten
//****
function StudentPrintAusbildungsvertragEnglisch()
{
 netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	tree = document.getElementById('student-tree');

	//Markierte Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';

	for (var t = 0; t < numRanges; t++)
	{
		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
			var uid=tree.view.getCellText(v,col);
			paramList += ';'+uid;
			stg_kz=getTreeCellText(tree,"student-treecol-studiengang_kz", v);
		}
	}
	
	if(paramList.replace(";",'')=='')
	{
		alert('Bitte mindestens einen Studenten auswaehlen');
		return false;
	}
	
	//PDF erzeugen 
	window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=ausbildungsvertrag.xml.php&xsl=AusbVerEng&style_xsl=AusbVerEngHead&output=pdf&uid='+paramList,'AusbildungsvertragEng', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ****
// * Erstellt die Studienerfolgsbestaetigung fuer einen oder mehrere Studenten
// ****
function StudentCreateStudienerfolg(xsl, finanzamt, studiensemester, all)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	tree = document.getElementById('student-tree');

	//Markierte Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var paramList= '';

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
			var uid=tree.view.getCellText(v,col);
			paramList += ';'+uid;
		}
	}
	
	if(paramList.replace(";",'')=='')
	{
		alert('Bitte einen Studenten auswaehlen');
		return false;
	}
	if(!studiensemester)
		studiensemester=getStudiensemester();
	if(!xsl)
	{
		xsl='Studienerfolg';
	}
	
	if(all=='true')
		all='&all=true';
	else
		all='';
	//PDF erzeugen
	window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=studienerfolg.rdf.php&xsl='+xsl+'&uid='+paramList+'&ss='+studiensemester+'&typ='+finanzamt+all,'DiplomaSupplement', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ************* FUNKTIONEN ***************** //

// ****
// * Laedt den Funktionen IFrame
// ****
function StudentFunktionIFrameLoad()
{
	if(document.getElementById('student-funktionen').getAttribute('src')=='')
	{
		uid = document.getElementById('student-detail-textbox-uid').value;
		if(uid!='')
		{
			url = '<?php echo APP_ROOT; ?>content/funktionen.xul.php?uid='+uid;
			document.getElementById('student-funktionen').setAttribute('src',url);
		}
	}
}

// ****
// * Funktionen IFrame ins leere zeigen lassen
// ****
function StudentFunktionIFrameUnLoad()
{
	document.getElementById('student-funktionen').setAttribute('src','');
}

// ****
// * Laedt das Reihungstest DropDown neu
// ****
function StudentReihungstestDropDownRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-prestudent-menulist-reihungstest');
	var url="<?php echo APP_ROOT ?>rdf/reihungstest.rdf.php?optional=true&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	tree.builder.rebuild();
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var myDatasource = rdfService.GetDataSource(url);
	tree.database.AddDataSource(myDatasource);
	SetStatusBarText('Reihungstest Liste wurde aktualisiert')
}

// *****
// * Wenn ein Reihungstest ausgewaehlt wurde und noch kein Anmeldedatum fuer den Reihungstest
// * eingetragen ist, dann das aktuelle Datum einfuegen
// *****
function StudentReihungstestDropDownSelect()
{
	if(document.getElementById('student-prestudent-textbox-anmeldungreihungstest').value=='')
		StudentAnmeldungreihungstestHeute();
}


// ****
// * Funktion um Status vorzurücken
// ****
function StudentPrestudentRolleVorruecken()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-prestudent-tree-rolle');

	if (tree.currentIndex==-1)
	{
	    return;
	}

	//markierte Rolle holen
	var status_kurzbz = getTreeCellText(tree, 'student-prestudent-tree-rolle-status_kurzbz', tree.currentIndex);
	var studiensemester_kurzbz = getTreeCellText(tree, 'student-prestudent-tree-rolle-studiensemester_kurzbz', tree.currentIndex);
	var prestudent_id = getTreeCellText(tree, 'student-prestudent-tree-rolle-prestudent_id', tree.currentIndex);	
	var ausbildungssemester = getTreeCellText(tree, 'student-prestudent-tree-rolle-ausbildungssemester', tree.currentIndex);
	var orgform_kurzbz = getTreeCellText(tree, 'student-prestudent-tree-rolle-orgform_kurzbz', tree.currentIndex);
	var studienplan_id = getTreeCellText(tree, 'student-prestudent-tree-rolle-studienplan_id', tree.currentIndex);
	
	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'rolleVorruecken');

	req.add('status_kurzbz', status_kurzbz);
	req.add('prestudent_id', prestudent_id);
	req.add('studiensemester_kurzbz', studiensemester_kurzbz);
	req.add('ausbildungssemester', ausbildungssemester);
	req.add('orgform_kurzbz', orgform_kurzbz);
	req.add('studienplan_id', studienplan_id);

	var response = req.executePOST();

	var val = new ParseReturnValue(response);
	debug("Return: "+val.dbdml_return);
	debug("Msg: "+val.dbdml_errormsg);
	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
		return false;
	}
	else
	{
		StudentDetailRolleTreeDatasource.Refresh(false);
		SetStatusBarText('Daten wurden gespeichert');
		return true;
	}
}
