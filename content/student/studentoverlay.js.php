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
require_once('../../include/benutzerberechtigung.class.php');

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
var StudentIOAufenthaltFoerderungTreeDatasource; //Datasource des Outgoing Foerderung Trees
var StudentIOZweckTreeDatasource; //Datasource des Outgoing Zweck Trees
var StudentIOSelectID=null; //BISIO Eintrag der nach dem Refresh markiert werden soll
var StudentNotenTreeDatasource; //Datasource des Noten Trees
var StudentNotenSelectLehrveranstaltungID=null; //LehreinheitID des Noten Eintrages der nach dem Refresh markiert werden soll
var StudentLvGesamtNotenTreeDatasource; //Datasource des Noten Trees
var StudentLvGesamtNotenSelectLehrveranstaltungID=null; //LehreinheitID des Noten Eintrages der nach dem Refresh markiert werden soll
var StudentPruefungTreeDatasource; //Datasource des Pruefung Trees
var StudentPruefungSelectID=null; //ID der Pruefung die nach dem Refresh markiert werden soll
var StudentAnrechnungTreeDatasource; //Datasource des Anrechnung Trees
var StudentAnrechnungSelectID=null; //ID der Anrechnung die nach dem Refresh markiert werden soll
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
// * Observer fuer BISIO Aufenthaltfoerderung Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentIOAufenthaltFoerderungTreeSinkObserver =
{
	onBeginLoad : function(pSink)
	{
	},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-io-tree-aufenthaltfoerderung').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var StudentIOAufenthaltFoerderungTreeListener =
{
	willRebuild : function(builder) {  },
	didRebuild : function(builder)
	{

	}
};

// ****
// * Observer fuer BISIO Zweck Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentIOZweckTreeSinkObserver =
{
	onBeginLoad : function(pSink)
	{
	},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-io-tree-aufenthaltfoerderung').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var StudentIOZweckTreeListener =
{
	willRebuild : function(builder) {  },
	didRebuild : function(builder)
	{

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
// * Observer fuer Anrechnung Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var StudentAnrechnungTreeSinkObserver =
{
	onBeginLoad : function(pSink)
	{
		tree = document.getElementById('student-anrechnungen-tree');
		tree.removeEventListener('select', StudentAnrechnungAuswahl, false);
	},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('student-anrechnungen-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var StudentAnrechnungTreeListener =
{
	willRebuild : function(builder) {  },
	didRebuild : function(builder)
	{
  		tree = document.getElementById('student-anrechnungen-tree');
		tree.addEventListener('select', StudentAnrechnungAuswahl, false);

		//timeout nur bei Mozilla notwendig da sonst die rows
		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		window.setTimeout(StudentAnrechnungenTreeSelectID,10);
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
function StudentFFZertifikatPrint(event)
{
//	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-noten-tree');

	col = tree.columns ? tree.columns["student-noten-tree-student_uid"] : "student-noten-tree-student_uid";
	uid = tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["student-noten-tree-lehrveranstaltung_id"] : "student-noten-tree-lehrveranstaltung_id";
	lvid = tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["student-noten-tree-studiensemester_kurzbz"] : "student-noten-tree-studiensemester_kurzbz";
	stsem = tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["student-noten-tree-studiengang_kz_lv"] : "student-noten-tree-studiengang_kz_lv";
	stg_kz = tree.view.getCellText(tree.currentIndex,col);

	if (event.shiftKey)
	    var output='odt';
	else if (event.ctrlKey)
		var output='doc';
	else
		var output='pdf';

	url =  '<?php echo APP_ROOT; ?>content/pdfExport.php?xml=zertifikat.rdf.php&xsl=Zertifikat&stg_kz='+stg_kz+'&uid=;'+uid+'&output='+output+'&ss='+stsem+'&lvid='+lvid+'&'+gettimestamp();

//	alert('url: '+url);
	window.location.href = url;
}

//****
//* Erstellt ein Lehrveranstaltungszeugnis fuer die LV
//****
function StudentLVZeugnisPrint(event, sprache)
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

	if (event.shiftKey)
	    var output='odt';
	else if (event.ctrlKey)
		var output='doc';
	else
		var output='pdf';

	var xsl = 'LVZeugnis';
	if (sprache == 'English')
		xsl = 'LVZeugnisEng';

	url =  '<?php echo APP_ROOT; ?>content/pdfExport.php?xml=lehrveranstaltungszeugnis.rdf.php&xsl='+xsl+'&stg_kz='+stg_kz+'&uid=;'+uid+'&output='+output+'&ss='+stsem+'&lvid='+lvid+'&'+gettimestamp();

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
	document.getElementById('student-detail-button-image-upload').disabled=val;
	document.getElementById('student-detail-button-image-delete').disabled=val;
	document.getElementById('student-detail-button-image-infomail').disabled=val;
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

function StudentCount()
{
	var tree = document.getElementById('student-tree');

	//Alle markierten Personen holen
	var start = {};
	var end = {};
	var numRanges = tree.view.selection.getRangeCount();
	var anzahl = 0;

	for (var t = 0; t < numRanges; t++)
	{
  		tree.view.selection.getRangeAt(t, start, end);
		for (var v = start.value; v <= end.value; v++)
		{
			anzahl = anzahl + 1;
		}
	}
	return anzahl;
}

// ****
// * Auswahl eines Studenten
// * bei Auswahl eines Studenten wird dieser geladen
// * und die Daten unten angezeigt
// ****
function StudentAuswahl()
{
	document.getElementById('student-toolbar-label-anzahl').value = 'Anzahl: ' + StudentCount();

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
			StudentMobilitaetDisableFields(false);
			StudentNoteDisableFields(false);
			document.getElementById('student-detail-button-save').disabled=false;
			StudentPruefungDisableFileds(false);
			if(document.getElementById('student-tab-anrechnungen'))
				StudentAnrechnungenDisableFields(false);
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
	zugangscode=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zugangscode" ));
	link_bewerbungstool=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#link_bewerbungstool" ));

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
	document.getElementById('label-student-detail-zugangscode').value=zugangscode;
	document.getElementById('label-student-detail-link_bewerbungstool').value=link_bewerbungstool;

	//PreStudent Daten holen

	aufmerksamdurch_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aufmerksamdurch_kurzbz" ));
	studiengang_kz_prestudent = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiengang_kz_prestudent" ));
	berufstaetigkeit_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#berufstaetigkeit_code" ));
	ausbildungcode = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ausbildungcode" ));
	zgv_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgv_code" ));
	zgvort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvort" ));
	zgvnation = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvnation" ));
	zgvdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvdatum" ));
	zgvmaster_code = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmas_code" ));
	zgvmasterort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmaort" ));
	zgvmasternation = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmanation" ));
	zgvmasterdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#zgvmadatum" ));
	aufnahmeschluessel = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aufnahmeschluessel" ));
	facheinschlberuf = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#facheinschlberuf" ));
	bismelden = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bismelden" ));
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkungpre" ));
	mentor = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#mentor" ));
	dual = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#dual" ));
	gsstudientyp_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gsstudientyp_kurzbz" ));
	priorisierung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#priorisierung" ));

	document.getElementById('student-prestudent-menulist-aufmerksamdurch').value=aufmerksamdurch_kurzbz;
	document.getElementById('student-prestudent-menulist-berufstaetigkeit').value=berufstaetigkeit_code;
	document.getElementById('student-prestudent-menulist-ausbildung').value=ausbildungcode;
	document.getElementById('student-prestudent-menulist-zgvcode').value=zgv_code;
	document.getElementById('student-prestudent-textbox-zgvort').value=zgvort;
    MenulistSelectItemOnValue('student-prestudent-menulist-zgvnation', zgvnation);
	document.getElementById('student-prestudent-textbox-zgvdatum').value=zgvdatum;
	document.getElementById('student-prestudent-menulist-zgvmastercode').value=zgvmaster_code;
	document.getElementById('student-prestudent-textbox-zgvmasterort').value=zgvmasterort;
    MenulistSelectItemOnValue('student-prestudent-menulist-zgvmasternation', zgvmasternation);
	document.getElementById('student-prestudent-textbox-zgvmasterdatum').value=zgvmasterdatum;
	document.getElementById('student-prestudent-menulist-aufnahmeschluessel').value=aufnahmeschluessel;
	if(facheinschlberuf=='true')
		document.getElementById('student-prestudent-checkbox-facheinschlberuf').checked=true;
	else
		document.getElementById('student-prestudent-checkbox-facheinschlberuf').checked=false;

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
	document.getElementById('student-prestudent-textbox-priorisierung').value=priorisierung;
	document.getElementById('student-prestudent-textbox-mentor').value=mentor;
	document.getElementById('student-detail-menulist-gsstudientyp').value=gsstudientyp_kurzbz;

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

	var historietree = document.getElementById('historie-tree');
	url_historie='<?php echo APP_ROOT;?>rdf/prestudenthistorie.rdf.php?prestudent_id='+prestudent_id+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasourcesHistorie = historietree.database.GetDataSources();
	while(oldDatasourcesHistorie.hasMoreElements())
	{
		historietree.database.RemoveDataSource(oldDatasourcesHistorie.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	historietree.builder.rebuild();

	var HistorieTreeDatasource = rdfService.GetDataSource(url_historie);
	HistorieTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	historietree.database.AddDataSource(HistorieTreeDatasource);

	if(uid=='')
	{
		//PRESTUDENT

		//Wenn keine UID gesetzt ist, dann ist er noch kein Student.
		//Hierbei werden einige der Tabs nicht angezeigt und auch nicht geladen!

		document.getElementById('student-tab-zeugnis').collapsed=false;
		document.getElementById('student-tab-betriebsmittel').collapsed=true;
		document.getElementById('student-tab-io').collapsed=true;
		document.getElementById('student-tab-mobilitaet').hidden=true;
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
		document.getElementById('student-tab-mobilitaet').hidden=false;
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

	/*if(uid!='')*/
	{
		// *** Zeugnis ***
		zeugnistree = document.getElementById('student-zeugnis-tree');
		url='<?php echo APP_ROOT;?>rdf/akte.rdf.php?person_id='+person_id+"&"+gettimestamp();

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
		// *** Gemeinsame Studien / Mobilitaet ***
		StudentMobilitaetLoad(prestudent_id);
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

		var pruefungstsemall='';
		if(document.getElementById('student-pruefung-button-filterstsem').checked)
			pruefungstsemall='&all_stsem';
		url='<?php echo APP_ROOT;?>rdf/pruefung.rdf.php?student_uid='+uid+pruefungstsemall+"&"+gettimestamp();

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

	if(document.getElementById('student-tab-anrechnungen'))
	{
		// ****** Anrechnungen ****** //
		StudentAnrechnungDetailDisableFields(true);
		StudentAnrechnungResetNotizLabel();

		anrechnungtree = document.getElementById('student-anrechnungen-tree');

		url='<?php echo APP_ROOT;?>rdf/anrechnung.rdf.php?prestudent_id='+prestudent_id+"&"+gettimestamp();

		try
		{
			StudentAnrechnungTreeDatasource.removeXMLSinkObserver(StudentAnrechnungTreeSinkObserver);
			anrechnungtree.builder.removeListener(StudentAnrechnungTreeListener);
		}
		catch(e)
		{}

		//Alte DS entfernen
		var oldDatasources = anrechnungtree.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			anrechnungtree.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		anrechnungtree.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		StudentAnrechnungTreeDatasource = rdfService.GetDataSource(url);
		StudentAnrechnungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		StudentAnrechnungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		anrechnungtree.database.AddDataSource(StudentAnrechnungTreeDatasource);
		StudentAnrechnungTreeDatasource.addXMLSinkObserver(StudentAnrechnungTreeSinkObserver);
		anrechnungtree.builder.addListener(StudentAnrechnungTreeListener);
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

		// ***** Termine *****
		if(document.getElementById('student-content-tabs').selectedItem==document.getElementById('student-tab-termine'))
		{
			document.getElementById('student-termine').setAttribute('src','termine.xul.php?student_uid='+uid);
		}

		// ***** Anwesenheit *****
		if(document.getElementById('student-content-tabs').selectedItem==document.getElementById('student-tab-anwesenheit'))
		{
			document.getElementById('student-anwesenheit').setAttribute('src','anwesenheit.xul.php?student_uid='+uid);
		}
	}

	// ***** Aufnahme-Termine *****
	if(document.getElementById('student-content-tabs').selectedItem==document.getElementById('student-tab-aufnahmetermine'))
	{
		document.getElementById('student-aufnahmetermine').setAttribute('src','student/aufnahmetermine.xul.php?prestudent_id='+prestudent_id);
	}

	// ***** Messages *****
	if(document.getElementById('student-content-tabs').selectedItem==document.getElementById('student-tab-messages'))
	{
		document.getElementById('student-messages').setAttribute('src','messages.xul.php?person_id='+person_id);
	}

	// ***** UDF *****
	if (document.getElementById('student-content-tabs').selectedItem == document.getElementById('student-tab-udf'))
	{
		document.getElementById('student-udf').setAttribute('src', 'udf.xul.php?person_id='+person_id+'&prestudent_id='+prestudent_id);
	}

	// Notizen laden
	anzahl_notizen = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anzahl_notizen" ));

	if(anzahl_notizen == "0")
		document.getElementById('student-tab-notizen').label = "Notizen";
	else
		document.getElementById('student-tab-notizen').label = " Notizen (" + anzahl_notizen + ")";

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
	document.getElementById('student-prestudent-textbox-zgvort').disabled=val;
	document.getElementById('student-prestudent-menulist-zgvnation').disabled=val;
	document.getElementById('student-prestudent-textbox-zgvdatum').disabled=val;
	document.getElementById('student-prestudent-menulist-zgvmastercode').disabled=val;
	document.getElementById('student-prestudent-textbox-zgvmasterort').disabled=val;
	document.getElementById('student-prestudent-menulist-zgvmasternation').disabled=val;
	document.getElementById('student-prestudent-textbox-zgvmasterdatum').disabled=val;
	document.getElementById('student-prestudent-menulist-aufnahmeschluessel').disabled=val;
	document.getElementById('student-prestudent-checkbox-facheinschlberuf').disabled=val;
	document.getElementById('student-prestudent-checkbox-bismelden').disabled=val;
	document.getElementById('student-prestudent-checkbox-dual').disabled=val;
	document.getElementById('student-prestudent-button-save').disabled=val;
	document.getElementById('student-prestudent-textbox-anmerkung').disabled=val;
	document.getElementById('student-prestudent-textbox-priorisierung').disabled=val;
	document.getElementById('student-prestudent-textbox-mentor').disabled=val;
	document.getElementById('student-detail-menulist-gsstudientyp').disabled=val;

	// Studiengang des angeklickten Prestudenten ermitteln
	var tree = document.getElementById('student-tree');
	var col = tree.columns ? tree.columns["student-treecol-studiengang_kz"] : "student-treecol-studiengang_kz";
	var studiengang_kz = parseInt(tree.view.getCellText(tree.currentIndex,col));

	<?php
	// Die Bachelor-ZGV darf nur mit einem eigenen Recht geändert werden
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	$studiengaenge = $rechte->getStgKz('student/editBakkZgv');
	// Anlegen eines Arrays mit allen berechtigten Stg-Kz
	echo ' var berechtigte_studiengaenge = ['.implode(',',$studiengaenge).'];';
	?>

	if (berechtigte_studiengaenge.indexOf(studiengang_kz) >= 0)
	{
		document.getElementById('student-prestudent-menulist-zgvcode').disabled=val;
	}
	else
	{
		document.getElementById('student-prestudent-menulist-zgvcode').disabled=true;
	}

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
	zgvnation = document.getElementById('student-prestudent-menulist-zgvnation').value;
	zgvdatum = document.getElementById('student-prestudent-textbox-zgvdatum').value;
	zgvmaster_code = document.getElementById('student-prestudent-menulist-zgvmastercode').value;
	zgvmasterort = document.getElementById('student-prestudent-textbox-zgvmasterort').value;
	zgvmasternation = document.getElementById('student-prestudent-menulist-zgvmasternation').value;
	zgvmasterdatum = document.getElementById('student-prestudent-textbox-zgvmasterdatum').value;
	aufnahmeschluessel = document.getElementById('student-prestudent-menulist-aufnahmeschluessel').value;
	facheinschlberuf = document.getElementById('student-prestudent-checkbox-facheinschlberuf').checked;
	bismelden = document.getElementById('student-prestudent-checkbox-bismelden').checked;
	dual = document.getElementById('student-prestudent-checkbox-dual').checked;
	person_id = document.getElementById('student-prestudent-textbox-person_id').value;
	prestudent_id = document.getElementById('student-prestudent-textbox-prestudent_id').value;
	neu = document.getElementById('student-prestudent-checkbox-new').checked;
	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	anmerkung = document.getElementById('student-prestudent-textbox-anmerkung').value;
	priorisierung = document.getElementById('student-prestudent-textbox-priorisierung').value;
	mentor = document.getElementById('student-prestudent-textbox-mentor').value;
	gsstudientyp = document.getElementById('student-detail-menulist-gsstudientyp').value;

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
	req.add('zgvnation', zgvnation);
	req.add('zgvdatum', ConvertDateToISO(zgvdatum));
	req.add('zgvmas_code', zgvmaster_code);
	req.add('zgvmaort', zgvmasterort);
	req.add('zgvmanation', zgvmasternation);
	req.add('zgvmadatum', ConvertDateToISO(zgvmasterdatum));
	req.add('aufnahmeschluessel', aufnahmeschluessel);
	req.add('facheinschlberuf', facheinschlberuf);
	req.add('bismelden', bismelden);
	req.add('dual', dual);
	req.add('person_id', person_id);
	req.add('prestudent_id', prestudent_id);
	req.add('studiengang_kz', studiengang_kz);
	req.add('anmerkung', anmerkung);
	req.add('priorisierung', priorisierung);
	req.add('mentor', mentor);
	req.add('gsstudientyp_kurzbz', gsstudientyp);

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

		req.add('type', 'returnDeletePrestudent');
		req.add('prestudent_id', prestudent_id);

		var response = req.executePOST();
		var val =  new ParseReturnValue(response)

		if (val.dbdml_data == 1)
		{
			if(confirm('Das Loeschen der letzten Rolle loescht auch den gesamten Prestudent-Datensatz!\nMoechten Sie Fortfahren?'))
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
		else
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
	var bewerbung_abgeschicktamum = getTreeCellText(tree, 'student-prestudent-tree-rolle-bewerbung_abgeschicktamum', tree.currentIndex);

	studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	if(confirm('Diesen Status bestaetigen?'))
	{
		// Status darf nur bestaetig werden, wenn Bewerbung schon abgeschickt wurde
		if (bewerbung_abgeschicktamum=='')
		{
			alert('Die Bewerbung wurde noch nicht abgeschickt und kann deshalb nicht bestätigt werden');
			return false;
		}
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

	window.open('<?php echo APP_ROOT?>content/student/studentrolledialog.xul.php?prestudent_id='+prestudent_id+'&status_kurzbz='+status_kurzbz+'&studiensemester_kurzbz='+studiensemester_kurzbz+'&ausbildungssemester='+ausbildungssemester,"Status","status=no, width=500, height=450, centerscreen, resizable");
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
	bestaetigt_datum = dialog.getElementById('student-rolle-datum-bestaetigt_datum').value;
	orgform_kurzbz = dialog.getElementById('student-rolle-menulist-orgform_kurzbz').value;
	studienplan_id = dialog.getElementById('student-rolle-menulist-studienplan').value;
	anmerkung = dialog.getElementById('student-rolle-textbox-anmerkung').value;
	rt_stufe = dialog.getElementById('student-rolle-menulist-stufe').value;
	statusgrund_id = dialog.getElementById('student-rolle-menulist-statusgrund').value;
	bewerbung_abgeschicktamum = dialog.getElementById('student-rolle-datum-bewerbung_abgeschicktamum').value;

	if(!CheckDatum(datum))
	{
		alert('Datum ist ungueltig');
		return false;
	}
	if(bestaetigt_datum!='' && !CheckDatum(bestaetigt_datum))
	{
		alert('Bestaetigungsdatum ist ungueltig');
		return false;
	}

	// Convert bewerbung_abgeschicktamum to ISO-Date
	if(bewerbung_abgeschicktamum != '')
	{
		if(bewerbung_abgeschicktamum.length != 19)
		{
			bewerbung_abgeschicktamum = '';
		}
		else
		{
			datepart = bewerbung_abgeschicktamum.substring(0, 10);
			timepart = bewerbung_abgeschicktamum.substring(11);
			arr = datepart.split('.');

			if(arr[0].length==1)
				arr[0]='0'+arr[0];

			if(arr[1].length==1)
				arr[1]='0'+arr[1];

			bewerbung_abgeschicktamum = arr[2]+'-'+arr[1]+'-'+arr[0]+' '+timepart;
		}
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
	req.add('bestaetigtam', ConvertDateToISO(bestaetigt_datum));
	req.add('orgform_kurzbz', orgform_kurzbz);
	req.add('studienplan_id', studienplan_id);
	req.add('anmerkung', anmerkung);
	req.add('rt_stufe', rt_stufe);
	req.add('statusgrund_id', statusgrund_id);
	req.add('bewerbung_abgeschicktamum', bewerbung_abgeschicktamum);

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
// * Einen Ab-/Unterbrecher wieder zum Studenten machen
// ****
function StudentUnterbrecherZuStudent(statusgrund_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1) return;

	if(sem = prompt('In welches Semester soll dieser Student verschoben werden?'))
	{
		if(!isNaN(sem))
		{
			StudentAddRolle('Student', sem, undefined, statusgrund_id);
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
	window.open('<?php echo APP_ROOT?>content/student/studentrolledialog.xul.php?prestudent_id='+prestudent_id,"Status","chrome, status=no, width=500, height=450, centerscreen, resizable");
}

// ****
// * Fuegt eine Rolle zu einem Studenten hinzu
// ****
function StudentAddRolle(rolle, semester, studiensemester, statusgrund_id)
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
		if(typeof(studiensemester)!='undefined')
			req.add('studiensemester_kurzbz', studiensemester);
		if(typeof(statusgrund_id)!='undefined')
			req.add('statusgrund_id', statusgrund_id);

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
function StudentPrintInskriptionsbestaetigung(event)
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

	if (event.shiftKey)
	    var output='odt';
	else if (event.ctrlKey)
		var output='doc';
	else
		var output='pdf';

	if(anzahl>0)
		window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=student.rdf.php&xsl=Inskription&stg_kz='+stg_kz+'&uid='+paramList+'&ss='+stsem+'&output='+output,'Inskriptionsbestaetigung', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
	else
		alert('Bitte einen Studenten auswaehlen');
}

// ****
// * Druckt den Bewerberakt
// ****
function StudentPrintBewerberakt(event)
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
			var prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', v);
			if(paramList!='')
				paramList = paramList+";";
			paramList += prestudent_id;
			anzahl = anzahl+1;
		}
	}

	if (event.shiftKey)
	    var output='odt';
	else if (event.ctrlKey)
		var output='doc';
	else
		var output='pdf';

	if(anzahl>0)
		window.open('<?php echo APP_ROOT; ?>content/dokumentenakt.pdf.php?prestudent_ids='+paramList+'&output='+output+'&vorlage_kurzbz=Bewerberakt','Bewerberakt', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
	else
		alert('Bitte markieren Sie zuerst eine oder mehrere Personen');
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
	action = '<?php echo APP_ROOT; ?>content/statistik/studentenexportextended.xls.php?studiensemester_kurzbz='+stsem;
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
	anmerkung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));

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
	document.getElementById('student-konto-textbox-anmerkung').value=anmerkung;
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
		filter.label='Offene Anzeigen';
		document.getElementById('student-konto-label-filter').value='alle Buchungen:';
	}
	else
	{
		filter.value='offene';
		filter.label='Alle Anzeigen';
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
	document.getElementById('student-konto-textbox-gegenbuchungsdatum').disabled=val;
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
	document.getElementById('student-konto-menulist-buchungstyp').disabled=val;
	document.getElementById('student-konto-menulist-studiensemester').disabled=val;
	document.getElementById('student-konto-menulist-studiengang_kz').disabled=val;
	document.getElementById('student-konto-textbox-anmerkung').disabled=val;
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
	anmerkung = document.getElementById('student-konto-textbox-anmerkung').value;

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
	var gegenbuchungsdatum = document.getElementById("student-konto-textbox-gegenbuchungsdatum").value;

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
	req.add('gegenbuchungsdatum', gegenbuchungsdatum);

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
	anmerkung = dialog.getElementById('student-konto-neu-textbox-anmerkung').value;

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
	req.add('anmerkung', anmerkung);

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
	//Wenn es kein Student ist, Studiengangs_kz vom PreStudenten ermitteln
	if (studiengang_kz == '')
	{
		studiengang_kz = document.getElementById('student-prestudent-menulist-studiengang_kz').value;
	}
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
	document.getElementById('student-zeugnis-button-archive').disabled=val;
	// Zeugnis als Default markieren
	document.getElementById('student-zeugnis-menulist-dokument').value='Zeugnis';
}

// ****
// * Offent eine Seite zum Upload einer neuen Datei zu einer archivierten Akte
// ****
function StudentAkteUpload()
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

	window.open('../vilesci/personen/akteupdate.php?akte_id='+akte_id);
}

function StudentZeugnisDokumentArchivieren()
{
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

	var vorlage = document.getElementById('student-zeugnis-menulist-dokument').value;
	var url = '<?php echo APP_ROOT; ?>content/pdfExport.php';
	var xml = '';

	switch(vorlage)
	{
		case 'Zeugnis':
		case 'ZeugnisEng':
			xml = 'zeugnis.rdf.php'
			break;

		case 'PrProtokollBakk':
		case 'PrProtBakkEng':
		case 'PrProtBA':
		case 'PrProtBAEng':
		case 'PrProtokollDipl':
		case 'PrProtDiplEng':
		case 'PrProtMA':
		case 'PrProtMAEng':
		case 'Bescheid':
		case 'BescheidEng':
		case 'Bakkurkunde':
		case 'BakkurkundeEng':
		case 'Diplomurkunde':
		case 'DiplomurkundeEng':
			xml = 'abschlusspruefung.rdf.php';
			break;

		case 'DiplSupplement':
			xml = 'diplomasupplement.xml.php';
			break;

		case 'Studienblatt':
		case 'StudienblattEng':
			xml = 'studienblatt.xml.php';
			break;

		case 'Ausbildungsver':
		case 'AusbVerEng':
			xml = 'ausbildungsvertrag.xml.php';
			break;

		default:
			alert('Das Archivieren fuer diesen Dokumenttyp wird derzeit nicht unterstuetzt');
			return
			break;
	}

	var labelalt = document.getElementById('student-zeugnis-button-archive').label;
	document.getElementById('student-zeugnis-button-archive').label='Loading...';

	//Dokument fuer alle markierten Studenten archivieren
	for (var t=0; t<numRanges; t++)
	{
		tree.view.selection.getRangeAt(t,start,end);
		for (v=start.value; v<=end.value; v++)
		{
			uid = getTreeCellText(tree, 'student-treecol-uid', v);
			prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', v);

			//Wenn keine UID vorhanden, kann nur der Ausbildungsvertrag generiert werden
			if(vorlage != 'Ausbildungsver' && vorlage != 'AusbVerEng' && uid == '')
			{
				alert('Dieses Dokument kann nur für Studierende erstellt werden. Mindestens eine ausgewählte Person hat keine UID');
				continue;
			}
			if(vorlage == 'Ausbildungsver' || vorlage == 'AusbVerEng')
			{
				// Ausbildungsvertrag nimmt nur PrestudentID
				uid = '';
			}

			var req = new phpRequest(url,'','');
			req.add('xsl', vorlage);
			req.add('xml', xml);
			req.add('ss', stsem);
			req.add('archive', '1');
			req.add('uid', uid);
			req.add('prestudent_id', prestudent_id);

			var response = req.execute();
			if(response!='')
				errormsg = errormsg + response;
		}
	}

	if(errormsg!='')
		alert(errormsg);
	document.getElementById('student-zeugnis-button-archive').label=labelalt;
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

	if (tree.currentIndex == -1)
		return;

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
	ects_angerechnet = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ects_angerechnet" ));
	ects_erworben = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ects_erworben" ));

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
	document.getElementById('student-io-detail-textbox-uid').value=student_uid;
	document.getElementById('student-io-detail-checkbox-neu').checked=false;
	document.getElementById('student-io-detail-textbox-bisio_id').value=bisio_id;
	document.getElementById('student-io-textbox-ort').value=ort;
	document.getElementById('student-io-textbox-universitaet').value=universitaet;
	document.getElementById('student-io-menulist-lehreinheit').value=lehreinheit_id;
	document.getElementById('student-io-menulist-lehrveranstaltung').value=lehrveranstaltung_id;
	document.getElementById('student-io-textbox-ects_erworben').value=ects_erworben;
	document.getElementById('student-io-textbox-ects_angerechnet').value=ects_angerechnet;

	StudentIOAufenthaltFoerderungTreeLoad(bisio_id);
	StudentIOZweckTreeLoad(bisio_id);
	StudentIOZweckMenulistLoad();
}

/**
 * Laedt das Dropdown fuer den Zweck
 * Abhaengig vom Status werden unterschiedliche Eintraege geladen
 */
function StudentIOZweckMenulistLoad()
{
	var student_tree = document.getElementById('student-tree');
	var status = getTreeCellText(student_tree, 'student-treecol-status', student_tree.currentIndex);

	var type = 'outgoing';
	if (status == 'Incoming')
		type = 'incoming';

	//Lehreinheiten Drop Down laden
	var zweckDropDown = document.getElementById('student-io-menulist-zweck');
	url = '<?php echo APP_ROOT;?>rdf/zweck.rdf.php?type=' + type + '&'+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = zweckDropDown.database.GetDataSources();
	while (oldDatasources.hasMoreElements())
	{
		zweckDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	zweckDropDown.builder.rebuild();

	zweckDropDown.selectedItem = '';
	zweckDropDown.value = '';
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	zweckDropDown.database.AddDataSource(datasource);
	zweckDropDown.builder.rebuild();
}

/**
 * Fuegt einen Zweck zu einem Auslandssemester hinzu
 * Bei Incoming darf nur ein Eintrag gesetzt werden
 */
function StudentIOZweckAdd()
{
	var student_tree = document.getElementById('student-tree');
	var status = getTreeCellText(student_tree, 'student-treecol-status', student_tree.currentIndex);

	if (status == 'Incoming')
	{
		// Incoming duerfen nur einen Zweck eingetragen haben.
		tree = document.getElementById('student-io-tree-zweck');
		if (tree.view && tree.view.rowCount > 0)
		{
			alert("Bei Incoming darf nur ein Zweck angegeben werden");
			return false;
		}
	}

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var bisio_id = document.getElementById('student-io-detail-textbox-bisio_id').value;
	var zweck_code = document.getElementById('student-io-menulist-zweck').value;

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savebisiozweck');

	req.add('bisio_id', bisio_id);
	req.add('zweck_code', zweck_code);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if (val.dbdml_errormsg == '')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		StudentIOZweckTreeLoad(bisio_id);
		SetStatusBarText('Daten wurden gespeichert');
	}
}

/**
 * Loescht die Zuordnung eines Zwecks zu einem Auslandssemester
 */
function StudentIOZweckDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-io-tree-zweck');

	if (tree.currentIndex == -1)
		return;

	//Ausgewaehlte Nr holen
	var zweck_code = getTreeCellText(tree, 'student-io-tree-zweck-code', tree.currentIndex);
	var bisio_id = document.getElementById('student-io-detail-textbox-bisio_id').value

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'deletebisiozweck');
	req.add('bisio_id', bisio_id);
	req.add('zweck_code', zweck_code);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if (val.dbdml_errormsg == '')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		StudentIOZweckTreeLoad(bisio_id);
		SetStatusBarText('Eintrag wurde gelöscht');
	}
}

/**
 * Fuegt eine Foerderung zu einem Auslandssemester hinzu
 */
function StudentIOAufenthaltfoerderungAdd()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var bisio_id = document.getElementById('student-io-detail-textbox-bisio_id').value;
	var aufenthaltfoerderung_code = document.getElementById('student-io-menulist-aufenthaltfoerderung').value;
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savebisioaufenthaltfoerderung');

	req.add('bisio_id', bisio_id);
	req.add('aufenthaltfoerderung_code', aufenthaltfoerderung_code);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if (val.dbdml_errormsg == '')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		StudentIOAufenthaltFoerderungTreeLoad(bisio_id);
		SetStatusBarText('Daten wurden gespeichert');
	}
}

/**
 * Entfernt eine Foerderung von einem Auslandssemester
 */
function StudentIOAufenthaltfoerderungDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-io-tree-aufenthaltfoerderung');

	if (tree.currentIndex == -1)
		return;

	//Ausgewaehlte Nr holen
	var aufenthaltfoerderung_code = getTreeCellText(tree, 'student-io-tree-aufenthaltfoerderung-code', tree.currentIndex);
	var bisio_id = document.getElementById('student-io-detail-textbox-bisio_id').value

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'deletebisioaufenthaltfoerderung');
	req.add('bisio_id', bisio_id);
	req.add('aufenthaltfoerderung_code', aufenthaltfoerderung_code);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if (val.dbdml_errormsg == '')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		StudentIOAufenthaltFoerderungTreeLoad(bisio_id);
		SetStatusBarText('Eintrag wurde gelöscht');
	}
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
	document.getElementById('student-io-textbox-ects_angerechnet').disabled=val;
	document.getElementById('student-io-textbox-ects_erworben').disabled=val;
	document.getElementById('student-io-menulist-aufenthaltfoerderung').disabled=val;
	document.getElementById('student-io-button-aufenthaltfoerderung-hinzufuegen').disabled=val;
	document.getElementById('student-io-button-zweck-hinzufuegen').disabled=val;
	document.getElementById('student-io-tree-aufenthaltfoerderung').disabled=val;
	document.getElementById('student-io-tree-zweck').disabled=val;
}

// *****
// * Resettet die Werte in den Detailfeldern des Incomming/Outgoing Moduls
// *****
function StudentIOResetFileds()
{
	document.getElementById('student-io-textbox-von').value='';
	document.getElementById('student-io-textbox-bis').value='';
	document.getElementById('student-io-menulist-mobilitaetsprogramm').value='7';
	document.getElementById('student-io-menulist-zweck').value='2';
	document.getElementById('student-io-menulist-nation').value='A';
	document.getElementById('student-io-textbox-ort').value='';
	document.getElementById('student-io-textbox-universitaet').value='';
	document.getElementById('student-io-textbox-ects_angerechnet').value='';
	document.getElementById('student-io-textbox-ects_erworben').value='';
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
	ects_erworben = document.getElementById('student-io-textbox-ects_erworben').value;
	ects_angerechnet = document.getElementById('student-io-textbox-ects_angerechnet').value;

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
	req.add('ects_angerechnet', ects_angerechnet);
	req.add('ects_erworben', ects_erworben);

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
// * Erstellt einen neuen IO Eintrag mit Defaultwerten und wechselt in den Editiermodus
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

	var uid = document.getElementById('student-detail-textbox-uid').value;
	var defaultdatum = tag+'.'+monat+'.'+jahr;
	var mobilitaetsprogramm = 7; // ERASMUS

	//UID ins Textfeld schreiben
	document.getElementById('student-io-detail-textbox-uid').value = uid;
	document.getElementById('student-io-detail-checkbox-neu').checked = true;
	document.getElementById('student-io-textbox-von').value = defaultdatum;
	document.getElementById('student-io-textbox-bis').value = defaultdatum;
	document.getElementById('student-io-menulist-mobilitaetsprogramm').value = mobilitaetsprogramm;
	try
	{
		//Wenn nach dem Personen gesucht wurde, ist es moeglich, dass kein Studiengang gewaehlt ist.
		//Dann wird der Studiengang/Semester des Studenten genommen
		var verband_tree = document.getElementById('tree-verband');

		var stg_kz = getTreeCellText(verband_tree, 'stg_kz', verband_tree.currentIndex);
		var sem = getTreeCellText(verband_tree, 'sem', verband_tree.currentIndex);
	}
	catch(e)
	{
		var stg_kz = document.getElementById('student-detail-menulist-studiengang_kz').value;
		var sem = document.getElementById('student-detail-textbox-semester').value;
	}

	// Neuen IO Datensatz erstellen und in Editiermodus wechseln.
	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savebisio');

	req.add('neu', true);
	req.add('von', ConvertDateToISO(defaultdatum));
	req.add('bis', ConvertDateToISO(defaultdatum));
	req.add('mobilitaetsprogramm_code', mobilitaetsprogramm);
	req.add('nation_code', 'A');
	req.add('student_uid', uid);
	req.add('studiengang_kz', stg_kz);
	req.add('lehreinheit_id', '');
	req.add('ort', '');
	req.add('universitaet', '');
	req.add('ects_angerechnet', '');
	req.add('ects_erworben', '');

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg == '')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		StudentIOSelectID = val.dbdml_data;
		StudentIOTreeDatasource.Refresh(false); //non blocking
		document.getElementById('student-io-detail-checkbox-neu').checked = false;
		document.getElementById('student-io-detail-textbox-bisio_id').value = StudentIOSelectID;
	}

	//Lehrveranstaltung Drop Down laden
	var LVDropDown = document.getElementById('student-io-menulist-lehrveranstaltung');
	url = '<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?stg_kz='+stg_kz+"&sem="+sem+"&optional=true&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = LVDropDown.database.GetDataSources();
	while (oldDatasources.hasMoreElements())
	{
		LVDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LVDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	LVDropDown.database.AddDataSource(datasource);
	LVDropDown.value = '';

	var LEDropDown = document.getElementById('student-io-menulist-lehreinheit');

	//Alte DS entfernen
	var oldDatasources = LEDropDown.database.GetDataSources();
	while (oldDatasources.hasMoreElements())
	{
		LEDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LEDropDown.builder.rebuild();

	LEDropDown.value = '';
	LEDropDown.selectedItem = '';
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

/**
 * Laedt den Aufenthalt Foerderung Tree im In/Out Karteireiter
 */
function StudentIOAufenthaltFoerderungTreeLoad(bisio_id)
{
	tree = document.getElementById('student-io-tree-aufenthaltfoerderung');
	url='<?php echo APP_ROOT;?>rdf/aufenthaltfoerderung.rdf.php?bisio_id='+bisio_id+"&"+gettimestamp();

	//Alte Observer entfernen
	try
	{
		StudentIOAufenthaltFoerderungTreeDatasource.removeXMLSinkObserver(StudentIOAufenthaltFoerderungTreeSinkObserver);
		tree.builder.removeListener(StudentIOAufenthaltFoerderungTreeListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	tree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentIOAufenthaltFoerderungTreeDatasource = rdfService.GetDataSource(url);
	StudentIOAufenthaltFoerderungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentIOAufenthaltFoerderungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	tree.database.AddDataSource(StudentIOAufenthaltFoerderungTreeDatasource);
	StudentIOAufenthaltFoerderungTreeDatasource.addXMLSinkObserver(StudentIOAufenthaltFoerderungTreeSinkObserver);
	tree.builder.addListener(StudentIOAufenthaltFoerderungTreeListener);
}

/**
 * Laedt den Zweck Tree im In/Out Karteireiter
 */
function StudentIOZweckTreeLoad(bisio_id)
{
	tree = document.getElementById('student-io-tree-zweck');
	url='<?php echo APP_ROOT;?>rdf/zweck.rdf.php?bisio_id='+bisio_id+"&"+gettimestamp();

	//Alte Observer entfernen
	try
	{
		StudentIOZweckTreeDatasource.removeXMLSinkObserver(StudentIOZweckTreeSinkObserver);
		tree.builder.removeListener(StudentIOZweckTreeListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	tree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StudentIOZweckTreeDatasource = rdfService.GetDataSource(url);
	StudentIOZweckTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StudentIOZweckTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	tree.database.AddDataSource(StudentIOZweckTreeDatasource);
	StudentIOZweckTreeDatasource.addXMLSinkObserver(StudentIOZweckTreeSinkObserver);
	tree.builder.addListener(StudentIOZweckTreeListener);
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

				//Wenn das benotungsdatum im Zeugnis
				//nach dem benotungsdatum des lektors liegt, dann wird die zeile auch nicht markiert.
				//damit wird verhindert, dass pruefungsnoten die nur von der assistenz eingetragen wurden,
				//durch den alten eintrag des lektors wieder ueberschrieben werden
				if(zeugnislehrveranstaltung_id==lvgesamtlehrveranstaltung_id
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
	document.getElementById('student-noten-textbox-punkte').disabled=val;
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
	punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));

	if(note=='')
		note='9';

	document.getElementById('student-noten-menulist-note').value=note;
	document.getElementById('student-noten-textbox-punkte').value=punkte;
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
	punkte = document.getElementById('student-noten-textbox-punkte').value;


	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savenote');

	req.add('lehrveranstaltung_id', lehrveranstaltung_id);
	req.add('student_uid', student_uid);
	req.add('studiensemester_kurzbz', studiensemester_kurzbz);
	req.add('note', note);
	req.add('punkte', punkte);

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

/**
 * Wird aufgerufen wenn Punkte zu einer Note eingetragen werden
 * Laedt die Note anhand des Notenschluessels
 */
function StudentNotenPunkteChange()
{
	var punkte = document.getElementById('student-noten-textbox-punkte').value;
	punkte = punkte.replace(',','.');
	if(punkte!='')
	{
		var tree=document.getElementById('student-noten-tree');
		//Ausgewaehlte LV holen
		var col = tree.columns ? tree.columns["student-noten-tree-lehrveranstaltung_id"] : "student-noten-tree-lehrveranstaltung_id";
		var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);

		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'getnotenotenschluessel');

		req.add('lehrveranstaltung_id', lehrveranstaltung_id);
		req.add('punkte', punkte);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response)

		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response);
			else
				alert(val.dbdml_errormsg);
		}
		else
		{
			document.getElementById('student-noten-menulist-note').value=val.dbdml_data;
		}
	}
}

// **************** PRUEFUNG ************** //

function pruefungTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var uid = document.getElementById('student-detail-textbox-uid').value;
	var pruefungtree = document.getElementById('student-pruefung-tree');

	var pruefungstsemall='';
	if(document.getElementById('student-pruefung-button-filterstsem').checked)
		pruefungstsemall='&all_stsem';
	url='<?php echo APP_ROOT;?>rdf/pruefung.rdf.php?student_uid='+uid+pruefungstsemall+"&"+gettimestamp();

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
	document.getElementById('student-pruefung-button-filterstsem').disabled=val;

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
	document.getElementById('student-pruefung-textbox-punkte').disabled=val;
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
	punkte = document.getElementById('student-pruefung-textbox-punkte').value;

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
	req.add('punkte', punkte);

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
	punkte = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#punkte" ));

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
	document.getElementById('student-pruefung-textbox-punkte').value=punkte;
}

function StudentPruefungFilterStsem()
{
	var buttonstsem = document.getElementById('student-pruefung-button-filterstsem');
	if(buttonstsem.checked)
		buttonstsem.label="Aktuelles Studiensemester anzeigen";
	else
		buttonstsem.label="Alle Studiensemester anzeigen";


	pruefungTreeRefresh();
}

// **************** ANRECHNUNGEN ************** //

// ****
// * Selektiert den Anrechnung Eintrag nachdem der Tree
// * rebuildet wurde.
// ****
function StudentAnrechnungenTreeSelectID()
{
	var tree=document.getElementById('student-anrechnungen-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Eintrag gespeichert
	if(StudentAnrechnungSelectID!=null)
	{
		for(var i=0;i<items;i++)
		{
			//ID der row holen
			col = tree.columns ? tree.columns["student-anrechnungen-tree-anrechnung_id"] : "student-anrechnungen-tree-anrechnung_id";
			var anrechnung_id=tree.view.getCellText(i,col);

			//wenn dies die zu selektierende Zeile
			if(anrechnung_id == StudentAnrechnungSelectID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				StudentAnrechnungSelectID=null;
				return true;
			}
		}
	}
}

// ****
// * Notiz-Dialog oeffnen
// ****
function StudentNotizNeu()
{
	var tree = document.getElementById('student-anrechnungen-tree');

	if (tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return;
	}

	//Ausgewaehlte ID holen
	var col = tree.columns ? tree.columns["student-anrechnungen-tree-anrechnung_id"] : "student-anrechnungen-tree-anrechnung_id";
	var anrechnung_id = tree.view.getCellText(tree.currentIndex,col);

	window.open("<?php echo APP_ROOT; ?>content/notizdialog.xul.php?anrechnung_id="+anrechnung_id,"","chrome, status=no, width=500, height=500, centerscreen, resizable");
}

// ****
// * De-/Aktiviert die Anrechnungsfelder
// ****
function StudentAnrechnungenDisableFields(val)
{
	document.getElementById('student-anrechnungen-button-neu').disabled = val;
	document.getElementById('student-anrechnungen-button-loeschen').disabled = val;
	document.getElementById('student-anrechnungen-button-notiz').disabled = val;

	if(val)
		StudentAnrechnungDetailDisableFields(val);
}

// ****
// * De-/Aktiviert die Anrechnungs-Detailfelder
// ****
function StudentAnrechnungDetailDisableFields(val)
{
	document.getElementById('student-anrechnungen-menulist-lehrveranstaltung').disabled=val;
	document.getElementById('student-anrechnungen-menulist-begruendung').disabled=val;
	document.getElementById('student-anrechnungen-menulist-kompatible_lehrveranstaltung').disabled=val;
	document.getElementById('student-anrechnungen-menulist-genehmigt_von').disabled=val;
	document.getElementById('student-anrechnungen-button-speichern').disabled=val;
}

// ****
// * Aktiviert die Felder um eine neue Anrechnung anzulegen
// ****
function StudentAnrechnungNeu()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	// ausgewählter Student
	var tree = document.getElementById('student-tree');
	if (tree.currentIndex==-1)
	{
		alert('StudentIn muss ausgewaehlt sein');
		return false;
	}

	document.getElementById("student-anrechnungen-menulist-kompatible_lehrveranstaltung-row").hidden = true;
	StudentAnrechnungDetailDisableFields(false);
	StudentAnrechnungResetNotizLabel();

	// Prestudent-ID in hidden field speichern
	var col = tree.columns ? tree.columns["student-treecol-prestudent_id"] : "student-treecol-prestudent_id";
	document.getElementById("student-anrechnungen-prestudent_id").value = tree.view.getCellText(tree.currentIndex,col);

	// Studiengang ermitteln
	var col = tree.columns ? tree.columns["student-treecol-studiengang_kz"] : "student-treecol-studiengang_kz";
	var stg_kz = tree.view.getCellText(tree.currentIndex,col);

    // Prestudent-ID ermitteln
    var col = tree.columns ? tree.columns["student-treecol-prestudent_id"] : "student-treecol-prestudent_id";
    var prestudentId = tree.view.getCellText(tree.currentIndex,col);

	//Lehrveranstaltung Drop Down laden
	var LVDropDown = document.getElementById('student-anrechnungen-menulist-lehrveranstaltung');
    url="<?php echo APP_ROOT;?>rdf/lehrveranstaltung_studienplan.rdf.php?&prestudent="+prestudentId+"&"+gettimestamp();

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

	//Begründung Drop Down laden
	var BegruendungDropDown = document.getElementById('student-anrechnungen-menulist-begruendung');
	url="<?php echo APP_ROOT;?>rdf/anrechnungbegruendung.rdf.php?"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = BegruendungDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		BegruendungDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	BegruendungDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	BegruendungDropDown.database.AddDataSource(datasource);
	BegruendungDropDown.value='';
	BegruendungDropDown.selectedItem='';

	//genehmigt von Drop Down laden
	var GenehmigtVonDropDown = document.getElementById('student-anrechnungen-menulist-genehmigt_von');
	url="<?php echo APP_ROOT;?>rdf/mitarbeiter.rdf.php?lektor=true&stg_kz=" + stg_kz+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = GenehmigtVonDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		GenehmigtVonDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	GenehmigtVonDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	GenehmigtVonDropDown.database.AddDataSource(datasource);
	GenehmigtVonDropDown.value='';
	GenehmigtVonDropDown.selectedItem='';

	document.getElementById('student-anrechnungen-neu').value = 1;
}

// ****
// * Laedt die kompatiblen Lehrveranstaltungen
// ****
function StudentLoadKompatibleLvaDropDown()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	//kompatible Lehrveranstaltung Drop Down laden
	var lehrveranstaltung = document.getElementById('student-anrechnungen-menulist-lehrveranstaltung').value;
	var LVKompDropDown = document.getElementById('student-anrechnungen-menulist-kompatible_lehrveranstaltung');
	url="<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?lehrveranstaltung_kompatibel_id="+lehrveranstaltung+"&self=0"+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = LVKompDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LVKompDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LVKompDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	LVKompDropDown.database.AddDataSource(datasource);
	LVKompDropDown.value='';
	LVKompDropDown.selectedItem='';
}

// ****
// * Zeigt bzw. versteckt das Dropdown fuer die kompatiblen Lehrveranstaltungen
// ****
function StudentAnrechnungShowKompatibleLvaDropDown()
{
	if(document.getElementById("student-anrechnungen-menulist-begruendung").value == 2)
		document.getElementById("student-anrechnungen-menulist-kompatible_lehrveranstaltung-row").hidden = false;
	else
		document.getElementById("student-anrechnungen-menulist-kompatible_lehrveranstaltung-row").hidden = true;
}

// ****
// * Speichert eine Anrechnung
// ****
function StudentAnrechnungDetailSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	lehrveranstaltung_id = document.getElementById('student-anrechnungen-menulist-lehrveranstaltung').value;
	begruendung_id = document.getElementById('student-anrechnungen-menulist-begruendung').value;
	lehrveranstaltung_id_kompatibel = document.getElementById('student-anrechnungen-menulist-kompatible_lehrveranstaltung').value;
	genehmigt_von = document.getElementById('student-anrechnungen-menulist-genehmigt_von').value;
	neu = document.getElementById('student-anrechnungen-neu').value;

	if (document.getElementById("student-anrechnungen-prestudent_id").value == '')
	{
		alert('StudentIn muss ausgewaehlt sein');
		return;
	}

	if (neu == '0')
	{
		tree = document.getElementById('student-anrechnungen-tree');
		col = tree.columns ? tree.columns["student-anrechnungen-tree-anrechnung_id"] : "student-anrechnungen-tree-anrechnung_id";
		anrechnung_id = tree.view.getCellText(tree.currentIndex,col);
	}
	else
		anrechnung_id = null;

	var prestudent_id = document.getElementById("student-anrechnungen-prestudent_id").value;

	var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'saveanrechnung');

	req.add('anrechnung_id', anrechnung_id);
	req.add('lehrveranstaltung_id', lehrveranstaltung_id);
	req.add('begruendung_id', begruendung_id);
	req.add('lehrveranstaltung_id_kompatibel', lehrveranstaltung_id_kompatibel);
	req.add('genehmigt_von', genehmigt_von);
	req.add('prestudent_id', prestudent_id);
	req.add('neu', neu);

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
		StudentAnrechnungTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
		StudentAnrechnungDetailDisableFields(true);
	}
}

// ****
// * Loescht eine Anrechnung
// ****
function StudentAnrechnungDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-anrechnungen-tree');

	if (tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Eintrag markieren');
		return;
	}

	//Ausgewaehlte ID holen
	var col = tree.columns ? tree.columns["student-anrechnungen-tree-anrechnung_id"] : "student-anrechnungen-tree-anrechnung_id";
	var anrechnung_id = tree.view.getCellText(tree.currentIndex,col);

	// Studiengang ermitteln
	var tree = document.getElementById('student-tree');
	var col = tree.columns ? tree.columns["student-treecol-studiengang_kz"] : "student-treecol-studiengang_kz";
	var stg_kz = tree.view.getCellText(tree.currentIndex,col);

	if(confirm('Diesen Eintrag wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/student/studentDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'deleteanrechnung');

		req.add('anrechnung_id', anrechnung_id);
		req.add('studiengang_kz', stg_kz);

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
			StudentAnrechnungTreeDatasource.Refresh(false); //non blocking
			SetStatusBarText('Daten wurden geloescht');
			StudentAnrechnungDetailDisableFields(true);
		}
	}
}

// ****
// * Setzt das Label des Notizen-Buttons zurueck
// ****
function StudentAnrechnungResetNotizLabel()
{
	document.getElementById('student-anrechnungen-button-notiz').label = "Notiz hinzufügen";
}

// ****
// * Laedt eine Anrechnung zum Bearbeiten
// ****
function StudentAnrechnungAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('student-anrechnungen-tree');

	if (tree.currentIndex==-1) return;

	StudentAnrechnungDetailDisableFields(false);

	//Ausgewaehlte ID holen
	var col = tree.columns ? tree.columns["student-anrechnungen-tree-anrechnung_id"] : "student-anrechnungen-tree-anrechnung_id";
	var anrechnung_id = tree.view.getCellText(tree.currentIndex,col);

	// Prestudent-ID in hidden field speichern
	var tree = document.getElementById('student-tree');
	var col = tree.columns ? tree.columns["student-treecol-prestudent_id"] : "student-treecol-prestudent_id";
	document.getElementById("student-anrechnungen-prestudent_id").value = tree.view.getCellText(tree.currentIndex,col);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/anrechnung.rdf.php?anrechnung_id='+anrechnung_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var dsource = rdfService.GetDataSourceBlocking(url);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/anrechnung/" + anrechnung_id);
	var predicateNS = "http://www.technikum-wien.at/anrechnung/rdf";

	//Daten holen
	anrechnung_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anrechnung_id" ));
	lehrveranstaltung_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehrveranstaltung_id" ));
	begruendung_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#begruendung_id" ));
	lehrveranstaltung_id_kompatibel = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehrveranstaltung_id_kompatibel" ));
	genehmigt_von = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#genehmigt_von" ));
	anzahl_notizen = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anzahl_notizen" ));

	// Studiengang ermitteln
	var tree = document.getElementById('student-tree');
	var col = tree.columns ? tree.columns["student-treecol-studiengang_kz"] : "student-treecol-studiengang_kz";
	var stg_kz = tree.view.getCellText(tree.currentIndex,col);

	//Lehrveranstaltung Drop Down laden
	var LVDropDown = document.getElementById('student-anrechnungen-menulist-lehrveranstaltung');
	url="<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?stg_kz="+stg_kz+"&"+gettimestamp();

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

	//Begründung Drop Down laden
	var BegruendungDropDown = document.getElementById('student-anrechnungen-menulist-begruendung');
	url="<?php echo APP_ROOT;?>rdf/anrechnungbegruendung.rdf.php?"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = BegruendungDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		BegruendungDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	BegruendungDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	BegruendungDropDown.database.AddDataSource(datasource);
	BegruendungDropDown.builder.rebuild();

	//kompatible Lehrveranstaltung Drop Down laden
	var LVKompDropDown = document.getElementById('student-anrechnungen-menulist-kompatible_lehrveranstaltung');
	url="<?php echo APP_ROOT;?>rdf/lehrveranstaltung.rdf.php?lehrveranstaltung_kompatibel_id="+lehrveranstaltung_id+"&self=0"+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = LVKompDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		LVKompDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	LVKompDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	LVKompDropDown.database.AddDataSource(datasource);
	LVKompDropDown.builder.rebuild();

	//genehmigt von Drop Down laden
	var GenehmigtVonDropDown = document.getElementById('student-anrechnungen-menulist-genehmigt_von');
	url="<?php echo APP_ROOT;?>rdf/mitarbeiter.rdf.php?lektor=true&stg_kz=" + stg_kz+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = GenehmigtVonDropDown.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		GenehmigtVonDropDown.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	GenehmigtVonDropDown.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSourceBlocking(url);
	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	GenehmigtVonDropDown.database.AddDataSource(datasource);
	GenehmigtVonDropDown.builder.rebuild();

	document.getElementById('student-anrechnungen-menulist-lehrveranstaltung').value = lehrveranstaltung_id;
	document.getElementById('student-anrechnungen-menulist-begruendung').value = begruendung_id;
	document.getElementById('student-anrechnungen-menulist-kompatible_lehrveranstaltung').value = lehrveranstaltung_id_kompatibel;
	document.getElementById('student-anrechnungen-menulist-genehmigt_von').value = genehmigt_von;
	document.getElementById('student-anrechnungen-neu').value = 0;
	StudentAnrechnungShowKompatibleLvaDropDown();

	if(anzahl_notizen == "0")
		document.getElementById('student-anrechnungen-button-notiz').label = "Notiz hinzufügen";
	else if(anzahl_notizen == "1")
		document.getElementById('student-anrechnungen-button-notiz').label = "1 Notiz vorhanden";
	else
		document.getElementById('student-anrechnungen-button-notiz').label = anzahl_notizen + " Notizen vorhanden";
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
function StudentSendMail(event)
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
		alert(anzfault+' StudentIn konnte nicht hinzugefuegt werden weil keine UID eingetragen ist!');
	if(mailempfaenger!='')
	{
		if (event.ctrlKey)
			splitmailto(mailempfaenger,'bcc');
		else
			splitmailto(mailempfaenger,'to');
	}
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
		alert('Aufgrund der großen Anzahl an EmpfängerInnen, muss die Nachricht auf mehrere E-Mails aufgeteilt werden!');

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
		alert('Bitte eine/n Studierende/n auswaehlen');
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
// * Erstellt den Ausbildungsvertrag fuer einen oder mehrere Studenten
// ****
function StudentPrintAusbildungsvertrag(event)
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
			var col = tree.columns ? tree.columns["student-treecol-prestudent_id"] : "student-treecol-prestudent_id";
			var prestudentId=tree.view.getCellText(v,col);
			paramList += ';'+prestudentId;
			stg_kz=getTreeCellText(tree,"student-treecol-studiengang_kz", v);
		}
	}

	if(paramList.replace(";",'')=='')
	{
		alert('Bitte mindestens eine Person auswaehlen');
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
	window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=ausbildungsvertrag.xml.php&xsl=Ausbildungsver&output='+output+'&prestudent_id='+paramList,'Ausbildungsvertrag', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

//****
//* Erstellt den englischen Ausbildungsvertrag fuer einen oder mehrere Studenten
//****
function StudentPrintAusbildungsvertragEnglisch(event)
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
			var col = tree.columns ? tree.columns["student-treecol-prestudent_id"] : "student-treecol-prestudent_id";
			var prestudentId=tree.view.getCellText(v,col);
			paramList += ';'+prestudentId;
			stg_kz=getTreeCellText(tree,"student-treecol-studiengang_kz", v);
		}
	}

	if(paramList.replace(";",'')=='')
	{
		alert('Bitte mindestens eine Person auswaehlen');
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
	window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=ausbildungsvertrag.xml.php&xsl=AusbVerEng&output='+output+'&prestudent_id='+paramList,'AusbildungsvertragEng', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ****
// * Erstellt die Studienerfolgsbestaetigung fuer einen oder mehrere Studenten
// ****
function StudentCreateStudienerfolg(event, xsl, finanzamt, studiensemester, all)
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
		alert('Bitte eine/n Studierende/n auswaehlen');
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
	window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?xml=studienerfolg.rdf.php&xsl='+xsl+'&uid='+paramList+'&ss='+studiensemester+'&typ='+finanzamt+all+'&output='+output,'DiplomaSupplement', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
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
// * Laedt den Termine IFrame
// ****
function StudentTermineIFrameLoad()
{
	uid = document.getElementById('student-detail-textbox-uid').value;
	if(uid!='')
	{
		url = 'termine.xul.php?student_uid='+uid+'&ts='+gettimestamp();
		document.getElementById('student-termine').setAttribute('src',url);
	}
}

// ****
// * Laedt den AufnahmeTermine IFrame
// ****
function StudentAufnahmeTermineIFrameLoad()
{
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1) return;

	try
	{
		//Ausgewaehlte prestudent_id holen
		var prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', tree.currentIndex);

		url = 'student/aufnahmetermine.xul.php?prestudent_id='+prestudent_id+'&ts='+gettimestamp();
		document.getElementById('student-aufnahmetermine').setAttribute('src',url);
	}
	catch(e)
	{
	}
}

// ****
// * Laedt den Messages IFrame
// ****
function StudentMessagesIFrameLoad()
{
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex==-1) return;

	try
	{
		//Ausgewaehlte prestudent_id holen
		var person_id = getTreeCellText(tree, 'student-treecol-person_id', tree.currentIndex);

		url = 'messages.xul.php?person_id='+person_id+'&ts='+gettimestamp();
		document.getElementById('student-messages').setAttribute('src',url);
	}
	catch(e)
	{
	}
}

// ****
// * Load UDF IFrame
// ****
function StudentUDFIFrameLoad()
{
	var tree = document.getElementById('student-tree');

	if (tree.currentIndex == -1) return;

	try
	{
		//Ausgewaehlte person_id holen
		var person_id = getTreeCellText(tree, 'student-treecol-person_id', tree.currentIndex);
		var prestudent_id = getTreeCellText(tree, 'student-treecol-prestudent_id', tree.currentIndex);

		url = 'udf.xul.php?person_id='+person_id+'&prestudent_id='+prestudent_id;
		document.getElementById('student-udf').setAttribute('src', url);
	}
	catch(e) {}
}

// ****
// * Laedt den Anwesenheit IFrame
// ****
function StudentAnwesenheitIFrameLoad()
{
	uid = document.getElementById('student-detail-textbox-uid').value;
	if(uid!='')
	{
		url = 'anwesenheit.xul.php?student_uid='+uid+'&ts='+gettimestamp();
		document.getElementById('student-anwesenheit').setAttribute('src',url);
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

// ****
// * Öffnet den Studienplan des Studenten im CIS
// ****
function StudentCisStudienplan(event)
{
	var tree = document.getElementById('student-tree');
	if (tree.currentIndex == -1)
	{
		alert("Bitte wählen Sie einen Studierenden aus.");
		return false;
	}

	var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
	var uid = tree.view.getCellText(tree.currentIndex,col);

	window.open('<?php echo CIS_ROOT; ?>cis/private/profile/studienplan.php?uid='+uid);
}

// ****
// * Öffnet die Notenliste des Studenten im CIS
// ****
function StudentCisNotenliste(event)
{
	var tree = document.getElementById('student-tree');
	if (tree.currentIndex == -1)
	{
		alert("Bitte wählen Sie eine/n Studierende/n aus.");
		return false;
	}

	var col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
	var uid = tree.view.getCellText(tree.currentIndex,col);

	window.open('<?php echo CIS_ROOT; ?>cis/private/lehre/notenliste.php?stsem=alle&uid='+uid);
}

//****
//* Fuegt Suchkriterien in das Textfeld der Personensuche ein
//****
function StudentSuchkriterien(suchkriterium)
{
	filter = document.getElementById('student-toolbar-textbox-suche').value;
	if (filter.substr(0, 1) == '#')
	{
		position = filter.search(" ") + 1;
		filter = filter.substr(position);
	}

	newval = suchkriterium+' '+filter;

	document.getElementById('student-toolbar-textbox-suche').value = newval;
	document.getElementById('student-toolbar-textbox-suche').focus();
	//document.getElementById('student-toolbar-textbox-suche').select();
}

function StudentLVGesamtNotenTreeSort()
{
	// Nach dem Sortieren der Noten die Unterschiede erneut markieren
	// da sonst nach dem sortieren falsche Eintraege markiert sind
	window.setTimeout(StudentNotenTreeSelectDifferent,20);
}

//****
//* Exportiert den Bescheid fuer alle markierten Studierenden
//****
function StudentExportBescheid()
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
			anzahl = anzahl+1;
		}
	}

	if(paramList.replace(";",'') == '')
	{
		alert('Bitte eine/n Studierende/n auswaehlen');
		return false;
	}

	if(anzahl>0)
		window.open('<?php echo APP_ROOT; ?>content/pdfExport.php?archivdokument=Bescheid&uid='+paramList,'Bescheide', 'height=200,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
	else
		alert('Bitte eine/n Studierende/n auswaehlen');
}

//****
//* Übergibt die PersonIDs an das Skript personen_wartung.php um Personen zusammenzulegen
//****
function StudentPersonenZusammenlegen()
{
	tree = document.getElementById('student-tree');
	//Alle markierten Studenten holen
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var anzahl = 0;
	var person1 = '';
	var person2 = '';

	for (var t = 0; t < numRanges; t++)
	{
		tree.view.selection.getRangeAt(t,start,end);
		for (var v = start.value; v <= end.value; v++)
		{
			if (person1 == '')
			{
				person1 = getTreeCellText(tree, 'student-treecol-person_id', v);
				anzahl = anzahl+1;
				continue;
			}

			person2 = getTreeCellText(tree, 'student-treecol-person_id', v);
			anzahl = anzahl+1;
		}
	}

	if(anzahl > 2)
	{
		alert('Sie können maximal 2 Personen zum Zusammenlegen auswählen');
		return false;
	}

	if(anzahl > 0)
		window.open('<?php echo APP_ROOT ?>vilesci/stammdaten/personen_wartung.php?person_id_1='+person1+'&person_id_2='+person2,'Personen-Zusammenlegen','');
	else
		alert('Bitte eine oder 2 Personen zum Zusammenlegen auswählen');
}
