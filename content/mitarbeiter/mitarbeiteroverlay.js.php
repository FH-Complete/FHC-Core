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
var MitarbeiterSelectUid=null; //UID des zu selektierenden Mitarbeiters
var MitarbeiterTreeLoadDataOnSelect=true; // Gibt an ob die Details beim markieren eines Mitarbeiters geladen werden sollen
var MitarbeiterVerwendungTreeDatasource=null; // Datasource des Verwendungstrees
var MitarbeiterVerwendungSelectID=null; // ID der Verwendung die nach dem rebuild markiert werden soll
var MitarbeiterFunktionTreeDatasource=null; // Datasource des Verwendungstrees
var MitarbeiterFunktionSelectVerwendungID=null; // ID der Verwendung der Funktion die nach dem rebuild markiert werden soll
var MitarbeiterFunktionSelectStudiengangID=null; // ID des Studiengangs der Funktion die nach dem rebuild markiert werden soll
var MitarbeiterEntwicklungsteamTreeDatasource=null; // Datasource des Entwicklungsteamtrees
var MitarbeiterEntwicklungsteamSelectMitarbeiterUID=null; // UID des Mitarbeiters des Entwicklugnsteams das nach dem rebuild markiert werden soll
var MitarbeiterEntwicklungsteamSelectStudiengangID=null; // ID des Stg des Entwicklungsteams das nach dem rebuild markiert werden soll
// ********** Observer und Listener ************* //


// ****
// * Observer fuer Mitarbeiter Tree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var MitarbeiterTreeSinkObserver =
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
		document.getElementById('mitarbeiter-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Mitarbeiter wieder
// * markiert
// ****
var MitarbeiterTreeListener =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
 		//timeout nur bei Mozilla notwendig da sonst die rows
 		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		window.setTimeout(MitarbeiterTreeSelectMitarbeiter,10);
	}
};

// ****
// * Observer fuer Mitarbeiter VerwendungTree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var MitarbeiterVerwendungTreeSinkObserver =
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
		document.getElementById('mitarbeiter-tree-verwendung').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Mitarbeiter wieder
// * markiert
// ****
var MitarbeiterVerwendungTreeListener =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
 		//timeout nur bei Mozilla notwendig da sonst die rows
 		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		window.setTimeout(MitarbeiterVerwendungTreeSelect,10);
	}
};

// ****
// * Observer fuer Mitarbeiter FunktionTree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var MitarbeiterFunktionTreeSinkObserver =
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
		document.getElementById('mitarbeiter-tree-funktion').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Funktion wieder
// * markiert
// ****
var MitarbeiterFunktionTreeListener =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
 		//timeout nur bei Mozilla notwendig da sonst die rows
 		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		window.setTimeout(MitarbeiterFunktionTreeSelect,10);
	}
};

// ****
// * Observer fuer Mitarbeiter EntwicklungsteamTree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var MitarbeiterEntwicklungsteamTreeSinkObserver =
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
		document.getElementById('mitarbeiter-tree-entwicklungsteam').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die Funktion wieder
// * markiert
// ****
var MitarbeiterEntwicklungsteamTreeListener =
{
	willRebuild : function(builder)
	{
	},
	didRebuild : function(builder)
  	{
 		//timeout nur bei Mozilla notwendig da sonst die rows
 		//noch keine values haben. Ab Seamonkey funktionierts auch
		//ohne dem setTimeout
		window.setTimeout(MitarbeiterEntwicklungsteamTreeSelect,10);
	}
};

// ****************** FUNKTIONEN ************************** //

// ****
// * Wenn der Mitarbeiter Tab markiert wird, dann wird
// * automatisch der Mitarbeiter Reiter im linken Tree 
// * markiert.
// ****
function MitarbeiterTabSelect()
{
	//Index des Mitarbeiter Tabs ermitteln

	//Tab markieren
}

// ****
// * Beim Auswaehlen eines Mitarbeiter-Filters werden die Mitarbeiter
// * die diesem Filter entsprechen geladen
// ****
function onMitarbeiterSelect()
{	
	var tree=document.getElementById('tree-menu-mitarbeiter');
	var col = tree.columns ? tree.columns["tree-menu-mitarbeiter-col-filter"] : "tree-menu-mitarbeiter-col-filter";
	var filter=tree.view.getCellText(tree.currentIndex,col);
	var url = "<?php echo APP_ROOT; ?>rdf/personal.rdf.php";
	var attributes="?type=unknown";

	if(filter=="")
		filter="Studiengangsleiter";

	if (filter=="Studiengangsleiter")
	{
		attributes+="&stgl=true";
		document.getElementById('mitarbeiter-toolbar-neu').disabled=true;
	}
	if (filter=="Fachbereichsleiter")
	{
		attributes+="&fbl=true";
		document.getElementById('mitarbeiter-toolbar-neu').disabled=true;
	}
	if (filter=="Alle")
	{
		attributes+="&alle=true";
		document.getElementById('mitarbeiter-toolbar-neu').disabled=false;
	}
	if (filter=="Aktive")
	{
		attributes+="&aktiv=true";
		document.getElementById('mitarbeiter-toolbar-neu').disabled=false;
	}
	if (filter=="FixAngestellte")
	{
		attributes+="&fix=true&aktiv=true";
		document.getElementById('mitarbeiter-toolbar-neu').disabled=false;
	}
	if (filter=="FixAngestellteAlle")
	{
		attributes+="&fix=true";
		document.getElementById('mitarbeiter-toolbar-neu').disabled=false;
	}
	if (filter=="Inaktive")
	{
		attributes+="&aktiv=false";
		document.getElementById('mitarbeiter-toolbar-neu').disabled=true;
	}
	if (filter=="Karenziert")
	{
		attributes+="&karenziert=true";
		document.getElementById('mitarbeiter-toolbar-neu').disabled=true;
	}
	if (filter=="Ausgeschieden")
	{
		attributes+="&ausgeschieden=true";
		document.getElementById('mitarbeiter-toolbar-neu').disabled=true;
	}
	if (filter=="FreiAngestellte")
	{
		attributes+="&fix=false&aktiv=true";
		document.getElementById('mitarbeiter-toolbar-neu').disabled=false;
	}
	if (filter=="FreiAngestellteAlle")
	{
		attributes+="&fix=false";
		document.getElementById('mitarbeiter-toolbar-neu').disabled=false;
	}
	
	//Timestamp anhaengen da beim Laden von Zwischengespeicherten Dateien kein
	//Observer Event ausgeloest wird.
	url+=attributes+'&'+gettimestamp();

	//Mitarbeiter Detail Felder deaktivieren
	MitarbeiterDetailDisableFields(true);

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	var tree=document.getElementById('mitarbeiter-tree');
	
	///Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}

	try
	{
		MitarbeiterTreeDatasource.removeXMLSinkObserver(MitarbeiterTreeSinkObserver);
		tree.builder.removeListener(MitarbeiterTreeListener);
	}
	catch(e)
	{}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	MitarbeiterTreeDatasource = rdfService.GetDataSource(url);
	MitarbeiterTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	MitarbeiterTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	tree.database.AddDataSource(MitarbeiterTreeDatasource);
	MitarbeiterTreeDatasource.addXMLSinkObserver(MitarbeiterTreeSinkObserver);
	tree.builder.addListener(MitarbeiterTreeListener);
}

// ****
// * Selectiert den Mitarbeiter nachdem der Tree
// * rebuildet wurde.
// ****
function MitarbeiterTreeSelectMitarbeiter()
{
	var tree=document.getElementById('mitarbeiter-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist der zu selektierende Mitarbeiter gespeichert
	if(MitarbeiterSelectUid!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//Uid der row holen
			col = tree.columns ? tree.columns["mitarbeiter-treecol-uid"] : "mitarbeiter-treecol-uid";
			uid=tree.view.getCellText(i,col);

			if(uid == MitarbeiterSelectUid)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	}
	document.getElementById('mitarbeiter-toolbar-label-anzahl').value='Anzahl: '+items;
}

// ****
// * De-/Aktiviert die Mitarbeiter Detail Felder
// ****
function MitarbeiterDetailDisableFields(val)
{
	//document.getElementById('mitarbeiter-detail-textbox-uid').disabled=val;
	document.getElementById('mitarbeiter-detail-checkbox-aktiv').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-anrede').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-titelpre').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-titelpost').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-nachname').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-vorname').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-vornamen').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-geburtsdatum').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-geburtsort').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-geburtszeit').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-svnr').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-ersatzkennzeichen').disabled=val;
	document.getElementById('mitarbeiter-detail-menulist-staatsbuergerschaft').disabled=val;
	document.getElementById('mitarbeiter-detail-menulist-geburtsnation').disabled=val;
	document.getElementById('mitarbeiter-detail-menulist-sprache').disabled=val;
	document.getElementById('mitarbeiter-detail-menulist-geschlecht').disabled=val;
	document.getElementById('mitarbeiter-detail-menulist-familienstand').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-anzahlderkinder').disabled=val;
	document.getElementById('mitarbeiter-detail-button-image-upload').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-anmerkung').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-homepage').disabled=val;
	
	//document.getElementById('mitarbeiter-detail-textbox-personalnummer').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-kurzbezeichnung').disabled=val;
	document.getElementById('mitarbeiter-detail-checkbox-lektor').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-stundensatz').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-telefonklappe').disabled=val;
	document.getElementById('mitarbeiter-detail-checkbox-fixangestellt').disabled=val;
	document.getElementById('mitarbeiter-detail-menulist-ort_kurzbz').disabled=val;
	document.getElementById('mitarbeiter-detail-menulist-standort').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-mitarbeiteranmerkung').disabled=val;
	document.getElementById('mitarbeiter-detail-menulist-ausbildung').disabled=val;
	document.getElementById('mitarbeiter-detail-button-speichern').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-alias').disabled=val;
}

function MitarbeiterAuswahl()
{
	if(!MitarbeiterTreeLoadDataOnSelect)
	{
		MitarbeiterTreeLoadDataOnSelect=true;
		return true;
	}

	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('mitarbeiter-tree');

	if (tree.currentIndex==-1) return;

	try
	{
		//Ausgewaehlte UID holen
        var col = tree.columns ? tree.columns["mitarbeiter-treecol-uid"] : "mitarbeiter-treecol-uid";
		var uid=tree.view.getCellText(tree.currentIndex,col);
		if(uid!='')
		{
			//Aktivieren der Felder
			MitarbeiterDetailDisableFields(false);
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

	var url = '<?php echo APP_ROOT ?>rdf/personal.rdf.php?uid='+uid+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/mitarbeiter/" + uid);

	var predicateNS = "http://www.technikum-wien.at/mitarbeiter/rdf";

	//Daten holen

	anrede = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anrede" ));
	titelpre=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titelpre" ));
	titelpost=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titelpost" ));
	vorname=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vorname" ));
	vornamen=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vornamen" ));
	nachname=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nachname" ));
	geburtsdatum=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geburtsdatum" ));
	geburtsort=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geburtsort" ));
	geburtszeit=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geburtszeit" ));
	anmerkungen=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkungen" ));
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
		
	personalnummer=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#personalnummer" ));
	kurzbezeichnung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kurzbz" ));
	stundensatz=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#stundensatz" ));
	telefonklappe=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#telefonklappe" ));
	lektor=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lektor" ));
	fixangestellt=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#fixangestellt" ));
	ausbildung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ausbildung" ));
	anmerkung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	ort_kurzbz=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ort_kurzbz" ));
	standort_kurzbz=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#standort_kurzbz" ));
	alias=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#alias" ));
	
	//Daten den Feldern zuweisen

	//Personendaten
	document.getElementById('mitarbeiter-detail-textbox-uid').value=uid;
	document.getElementById('mitarbeiter-detail-textbox-anrede').value=anrede;
	document.getElementById('mitarbeiter-detail-textbox-titelpre').value=titelpre;
	document.getElementById('mitarbeiter-detail-textbox-titelpost').value=titelpost;
	document.getElementById('mitarbeiter-detail-textbox-vorname').value=vorname;
	document.getElementById('mitarbeiter-detail-textbox-vornamen').value=vornamen;
	document.getElementById('mitarbeiter-detail-textbox-nachname').value=nachname;
	document.getElementById('mitarbeiter-detail-textbox-geburtsdatum').value=geburtsdatum;
	document.getElementById('mitarbeiter-detail-textbox-geburtsort').value=geburtsort;
	document.getElementById('mitarbeiter-detail-textbox-geburtszeit').value=geburtszeit;
	document.getElementById('mitarbeiter-detail-textbox-anmerkung').value=anmerkungen;
	document.getElementById('mitarbeiter-detail-textbox-homepage').value=homepage;
	document.getElementById('mitarbeiter-detail-textbox-svnr').value=svnr;
	document.getElementById('mitarbeiter-detail-textbox-ersatzkennzeichen').value=ersatzkennzeichen;
	document.getElementById('mitarbeiter-detail-menulist-familienstand').value=familienstand;
	document.getElementById('mitarbeiter-detail-menulist-geschlecht').value=geschlecht;
	if(aktiv=='Ja')
		document.getElementById('mitarbeiter-detail-checkbox-aktiv').checked=true;
	else
		document.getElementById('mitarbeiter-detail-checkbox-aktiv').checked=false;
	document.getElementById('mitarbeiter-detail-textbox-anzahlderkinder').value=anzahlderkinder;
	document.getElementById('mitarbeiter-detail-menulist-staatsbuergerschaft').value=staatsbuergerschaft;
	document.getElementById('mitarbeiter-detail-menulist-geburtsnation').value=geburtsnation;
	document.getElementById('mitarbeiter-detail-menulist-sprache').value=sprache;
	document.getElementById('mitarbeiter-detail-image').src='<?php echo APP_ROOT?>content/bild.php?src=person&person_id='+person_id+'&'+gettimestamp();
	document.getElementById('mitarbeiter-detail-textbox-person_id').value=person_id;
	
	//Mitarbeiterdaten
	document.getElementById('mitarbeiter-detail-textbox-personalnummer').value=personalnummer;
	document.getElementById('mitarbeiter-detail-textbox-kurzbezeichnung').value=kurzbezeichnung;
	document.getElementById('mitarbeiter-detail-textbox-stundensatz').value=stundensatz;
	document.getElementById('mitarbeiter-detail-textbox-telefonklappe').value=telefonklappe;
	if(lektor=='Ja')
		document.getElementById('mitarbeiter-detail-checkbox-lektor').checked=true;
	else
		document.getElementById('mitarbeiter-detail-checkbox-lektor').checked=false;
		
	if(fixangestellt=='Ja')
		document.getElementById('mitarbeiter-detail-checkbox-fixangestellt').checked=true;
	else
		document.getElementById('mitarbeiter-detail-checkbox-fixangestellt').checked=false;
	document.getElementById('mitarbeiter-detail-menulist-ausbildung').value=ausbildung;
	document.getElementById('mitarbeiter-detail-textbox-mitarbeiteranmerkung').value=anmerkung;
	document.getElementById('mitarbeiter-detail-menulist-ort_kurzbz').value=ort_kurzbz;
	document.getElementById('mitarbeiter-detail-menulist-standort').value=standort_kurzbz;
	document.getElementById('mitarbeiter-detail-textbox-alias').value=alias;
	
	// ***** KONTAKTE *****
	document.getElementById('mitarbeiter-kontakt').setAttribute('src','kontakt.xul.php?person_id='+person_id);
	
	// **** VERWENDUNG ****
	verwendungtree = document.getElementById('mitarbeiter-tree-verwendung');
	url='<?php echo APP_ROOT;?>rdf/bisverwendung.rdf.php?uid='+uid+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = verwendungtree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		verwendungtree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	verwendungtree.builder.rebuild();

	try
	{
		MitarbeiterVerwendungTreeDatasource.removeXMLSinkObserver(MitarbeiterVerwendungTreeSinkObserver);
		verwendungtree.builder.removeListener(MitarbeiterVerwendungTreeListener);
	}
	catch(e)
	{}

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	MitarbeiterVerwendungTreeDatasource = rdfService.GetDataSource(url);
	MitarbeiterVerwendungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	MitarbeiterVerwendungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	verwendungtree.database.AddDataSource(MitarbeiterVerwendungTreeDatasource);
	MitarbeiterVerwendungTreeDatasource.addXMLSinkObserver(MitarbeiterVerwendungTreeSinkObserver);
	verwendungtree.builder.addListener(MitarbeiterVerwendungTreeListener);
	
	MitarbeiterVerwendungDisableFields(false);
	
	// **** ENTWICKLUNGSTEAM ****
	entwicklungsteamtree = document.getElementById('mitarbeiter-tree-entwicklungsteam');
	url='<?php echo APP_ROOT;?>rdf/entwicklungsteam.rdf.php?mitarbeiter_uid='+uid+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = entwicklungsteamtree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		entwicklungsteamtree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	entwicklungsteamtree.builder.rebuild();

	try
	{
		MitarbeiterEntwicklungsteamTreeDatasource.removeXMLSinkObserver(MitarbeiterEntwicklungsteamTreeSinkObserver);
		entwicklungsteamtree.builder.removeListener(MitarbeiterEntwicklungsteamTreeListener);
	}
	catch(e)
	{}

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	MitarbeiterEntwicklungsteamTreeDatasource = rdfService.GetDataSource(url);
	MitarbeiterEntwicklungsteamTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	MitarbeiterEntwicklungsteamTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	entwicklungsteamtree.database.AddDataSource(MitarbeiterEntwicklungsteamTreeDatasource);
	MitarbeiterEntwicklungsteamTreeDatasource.addXMLSinkObserver(MitarbeiterEntwicklungsteamTreeSinkObserver);
	entwicklungsteamtree.builder.addListener(MitarbeiterEntwicklungsteamTreeListener);
	
	MitarbeiterEntwicklungsteamDisableFields(false);
	
	// Funktionen Tree Leeren
	funktiontree = document.getElementById('mitarbeiter-tree-funktion');
	
	//Alte DS entfernen
	var oldDatasources = funktiontree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		funktiontree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	funktiontree.builder.rebuild();
	
	MitarbeiterFunktionDisableFields(true);
}

// ****
// * Laedt ein Bild zu einer Person in die Datenbank
// ****
function MitarbeiterImageUpload()
{
	person_id = document.getElementById('mitarbeiter-detail-textbox-person_id').value;
	if(person_id!='')
	{
		window.open('<?php echo APP_ROOT; ?>content/bildupload.php?person_id='+person_id,'Bild Upload', 'height=10,width=350,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
	}
	else
		alert('Es wurde keine Person ausgewaehlt');
}

// ****
// * Speichert die Mitarbeiterdaten
// ****
function MitarbeiterSave()
{
	//Daten holen
	uid = document.getElementById('mitarbeiter-detail-textbox-uid').value;
	anrede = document.getElementById('mitarbeiter-detail-textbox-anrede').value;
	titelpre = document.getElementById('mitarbeiter-detail-textbox-titelpre').value;
	titelpost = document.getElementById('mitarbeiter-detail-textbox-titelpost').value;
	vorname = document.getElementById('mitarbeiter-detail-textbox-vorname').value;
	vornamen = document.getElementById('mitarbeiter-detail-textbox-vornamen').value;
	nachname = document.getElementById('mitarbeiter-detail-textbox-nachname').value;
	geburtsdatum = document.getElementById('mitarbeiter-detail-textbox-geburtsdatum').value;
	geburtsort = document.getElementById('mitarbeiter-detail-textbox-geburtsort').value;
	geburtszeit = document.getElementById('mitarbeiter-detail-textbox-geburtszeit').value;
	anmerkungen = document.getElementById('mitarbeiter-detail-textbox-anmerkung').value;
	homepage = document.getElementById('mitarbeiter-detail-textbox-homepage').value;
	svnr = document.getElementById('mitarbeiter-detail-textbox-svnr').value;
	ersatzkennzeichen = document.getElementById('mitarbeiter-detail-textbox-ersatzkennzeichen').value;
	familienstand = document.getElementById('mitarbeiter-detail-menulist-familienstand').value;
	geschlecht = document.getElementById('mitarbeiter-detail-menulist-geschlecht').value;
	aktiv = document.getElementById('mitarbeiter-detail-checkbox-aktiv').checked;
	anzahlderkinder = document.getElementById('mitarbeiter-detail-textbox-anzahlderkinder').value;
	staatsbuergerschaft = document.getElementById('mitarbeiter-detail-menulist-staatsbuergerschaft').value;
	geburtsnation = document.getElementById('mitarbeiter-detail-menulist-geburtsnation').value;
	sprache = document.getElementById('mitarbeiter-detail-menulist-sprache').value;
	person_id = document.getElementById('mitarbeiter-detail-textbox-person_id').value;
	
	//Mitarbeiterdaten
	kurzbezeichnung = document.getElementById('mitarbeiter-detail-textbox-kurzbezeichnung').value;
	stundensatz = document.getElementById('mitarbeiter-detail-textbox-stundensatz').value;
	telefonklappe = document.getElementById('mitarbeiter-detail-textbox-telefonklappe').value;
	lektor = document.getElementById('mitarbeiter-detail-checkbox-lektor').checked;
	fixangestellt = document.getElementById('mitarbeiter-detail-checkbox-fixangestellt').checked;
	ausbildung = document.getElementById('mitarbeiter-detail-menulist-ausbildung').value;
	anmerkung = document.getElementById('mitarbeiter-detail-textbox-mitarbeiteranmerkung').value;
	ort_kurzbz = document.getElementById('mitarbeiter-detail-menulist-ort_kurzbz').value;
	standort_kurzbz = document.getElementById('mitarbeiter-detail-menulist-standort').value;
	alias = document.getElementById('mitarbeiter-detail-textbox-alias').value;
	
	if(geburtsdatum!='' && !CheckDatum(geburtsdatum))
	{
		alert('Geburtsdatum ist ungueltig');
		return false;
	}
		
	var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'mitarbeitersave');

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
	req.add('anmerkungen', anmerkungen);
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
	req.add('kurzbezeichnung', kurzbezeichnung);
	req.add('stundensatz', stundensatz);
	req.add('telefonklappe', telefonklappe);
	req.add('lektor', lektor);
	req.add('fixangestellt', fixangestellt);
	req.add('ausbildung', ausbildung);
	req.add('anmerkung', anmerkung);
	req.add('ort_kurzbz', ort_kurzbz);
	req.add('standort_kurzbz', standort_kurzbz);
	req.add('alias', alias);
	
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

		MitarbeiterSelectUid=uid;
		MitarbeiterTreeDatasource.Refresh(false); //non blocking
		SetStatusBarText('Daten wurden gespeichert');
	}
}


// ***************** VERWENDUNG ********************** //

// ****
// * Selectiert die Verwendung nachdem der Tree
// * rebuildet wurde.
// ****
function MitarbeiterVerwendungTreeSelect()
{
	var tree=document.getElementById('mitarbeiter-tree-verwendung');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist der zu selektierende Verwendung gespeichert
	if(MitarbeiterVerwendungSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//Uid der row holen
			col = tree.columns ? tree.columns["mitarbeiter-verwendung-treecol-bisverwendung_id"] : "mitarbeiter-verwendung-treecol-bisverwendung_id";
			id=tree.view.getCellText(i,col);

			if(id == MitarbeiterVerwendungSelectID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	}
}

// ****
// * Wenn ein Eintrag im Verwendungstree Selektiert wird,
// * dann werden die dazugehoerigen Funktionen geladen
// ****
function MitarbeiterVerwendungSelect()
{
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	var tree = document.getElementById('mitarbeiter-tree-verwendung');

	if (tree.currentIndex==-1) return;

	//Ausgewaehlte ID holen
    var col = tree.columns ? tree.columns["mitarbeiter-verwendung-treecol-bisverwendung_id"] : "mitarbeiter-verwendung-treecol-bisverwendung_id";
	var bisverwendung_id=tree.view.getCellText(tree.currentIndex,col);

	
	// Laden der Funktionen
	funktiontree = document.getElementById('mitarbeiter-tree-funktion');
	url='<?php echo APP_ROOT;?>rdf/bisfunktion.rdf.php?bisverwendung_id='+bisverwendung_id+"&"+gettimestamp();

	//Alte DS entfernen
	var oldDatasources = funktiontree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		funktiontree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	funktiontree.builder.rebuild();

	try
	{
		MitarbeiterFunktionTreeDatasource.removeXMLSinkObserver(MitarbeiterFunktionTreeSinkObserver);
		funktiontree.builder.removeListener(MitarbeiterFunktionTreeListener);
	}
	catch(e)
	{}

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	MitarbeiterFunktionTreeDatasource = rdfService.GetDataSource(url);
	MitarbeiterFunktionTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	MitarbeiterFunktionTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	funktiontree.database.AddDataSource(MitarbeiterFunktionTreeDatasource);
	MitarbeiterFunktionTreeDatasource.addXMLSinkObserver(MitarbeiterFunktionTreeSinkObserver);
	funktiontree.builder.addListener(MitarbeiterFunktionTreeListener);
	
	MitarbeiterFunktionDisableFields(false);
}

// ****
// * De-/Aktiviert die Buttons
// ****
function MitarbeiterVerwendungDisableFields(val)
{
	document.getElementById('mitarbeiter-verwendung-button-neu').disabled=val;
	document.getElementById('mitarbeiter-verwendung-button-bearbeiten').disabled=val;
	document.getElementById('mitarbeiter-verwendung-button-loeschen').disabled=val;
}

// *********** FUNKTION *************** //

// ****
// * Selectiert die Funktion nachdem der Tree
// * rebuildet wurde.
// ****
function MitarbeiterFunktionTreeSelect()
{
	var tree=document.getElementById('mitarbeiter-tree-funktion');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist der zu selektierende Verwendung gespeichert
	if(MitarbeiterFunktionSelectStudiengangID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
			col = tree.columns ? tree.columns["mitarbeiter-funktion-treecol-bisverwendung_id"] : "mitarbeiter-funktion-treecol-bisverwendung_id";
			verwendung_id=tree.view.getCellText(i,col);
			col = tree.columns ? tree.columns["mitarbeiter-funktion-treecol-studiengang_kz"] : "mitarbeiter-funktion-treecol-studiengang_kz";
			studiengang_kz=tree.view.getCellText(i,col);

			if(verwendung_id == MitarbeiterFunktionSelectVerwendungID && studiengang_kz==MitarbeiterFunktionSelectStudiengangID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	}
}

// ****
// * De-/Aktiviert die Buttons
// ****
function MitarbeiterFunktionDisableFields(val)
{
	document.getElementById('mitarbeiter-funktion-button-neu').disabled=val;
	document.getElementById('mitarbeiter-funktion-button-bearbeiten').disabled=val;
	document.getElementById('mitarbeiter-funktion-button-loeschen').disabled=val;
}

// *********** ENTWICKLUNGSTEAM *************** //

// ****
// * Selectiert die Funktion nachdem der Tree
// * rebuildet wurde.
// ****
function MitarbeiterEntwicklungsteamTreeSelect()
{
	var tree=document.getElementById('mitarbeiter-tree-entwicklungsteam');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist der zu selektierende Verwendung gespeichert
	if(MitarbeiterEntwicklungsteamSelectStudiengangID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
			col = tree.columns ? tree.columns["mitarbeiter-entwicklungsteam-treecol-mitarbeiter_uid"] : "mitarbeiter-entwicklungsteam-treecol-mitarbeiter_uid";
			mitarbeiter_uid=tree.view.getCellText(i,col);
			col = tree.columns ? tree.columns["mitarbeiter-entwicklungsteam-treecol-studiengang_kz"] : "mitarbeiter-entwicklungsteam-treecol-studiengang_kz";
			studiengang_kz=tree.view.getCellText(i,col);

			if(mitarbeiter_uid == MitarbeiterEntwicklungsteamSelectMitarbeiterUID && studiengang_kz==MitarbeiterEntwicklungsteamSelectStudiengangID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	}
}

// ****
// * De-/Aktiviert die Buttons
// ****
function MitarbeiterEntwicklungsteamDisableFields(val)
{
	document.getElementById('mitarbeiter-entwicklungsteam-button-neu').disabled=val;
	document.getElementById('mitarbeiter-entwicklungsteam-button-bearbeiten').disabled=val;
	document.getElementById('mitarbeiter-entwicklungsteam-button-loeschen').disabled=val;
}
