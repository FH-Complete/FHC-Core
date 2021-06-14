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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas moik <moik@technikum-wien.at>.
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/variable.class.php');

$user = get_uid();
$variable = new variable();
$variable->loadVariables($user);
?>
// *********** Globale Variablen *****************//
var MitarbeiterSelectUid=null; //UID des zu selektierenden Mitarbeiters
var MitarbeiterTreeDatasource=null; // Datasource des Mitarbeiter Trees
var MitarbeiterTreeLoadDataOnSelect=true; // Gibt an ob die Details beim markieren eines Mitarbeiters geladen werden sollen
var MitarbeiterVerwendungTreeDatasource=null; // Datasource des Verwendungstrees
var MitarbeiterVerwendungSelectID=null; // ID der Verwendung die nach dem rebuild markiert werden soll
var MitarbeiterFunktionTreeDatasource=null; // Datasource des Verwendungstrees
var MitarbeiterFunktionSelectVerwendungID=null; // ID der Verwendung der Funktion die nach dem rebuild markiert werden soll
var MitarbeiterFunktionSelectStudiengangID=null; // ID des Studiengangs der Funktion die nach dem rebuild markiert werden soll
var MitarbeiterEntwicklungsteamTreeDatasource=null; // Datasource des Entwicklungsteamtrees
var MitarbeiterEntwicklungsteamSelectMitarbeiterUID=null; // UID des Mitarbeiters des Entwicklugnsteams das nach dem rebuild markiert werden soll
var MitarbeiterEntwicklungsteamSelectStudiengangID=null; // ID des Stg des Entwicklungsteams das nach dem rebuild markiert werden soll
var MitarbeiterEntwicklungsteamDoubleRefresh=false; // Wenn auf einen Tree der eine leere Datasource enthaelt eine neue Datasource angehaengt wird, dann muss doppelt refresht werden
var MitarbeiterTreeLoadDataOnSelect2=true; // Gibt an ob die Details beim markieren eines Mitarbeiters geladen werden sollen
var MitarbeiterDetailValueChanged=false;
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
		MitarbeiterTreeLoadDataOnSelect2=false;
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
		MitarbeiterTreeLoadDataOnSelect2=true;
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
		if(MitarbeiterEntwicklungsteamDoubleRefresh)
		{
			MitarbeiterEntwicklungsteamDoubleRefresh=false;
			window.setTimeout("MitarbeiterEntwicklungsteamTreeRefresh()",10);
		}
		else
			window.setTimeout(MitarbeiterEntwicklungsteamTreeSelect,10);
	}
};

// ****************** FUNKTIONEN ************************** //

// ****
// * Beim Sortieren des Trees wird der markierte Eintrag gespeichert und nach dem sortieren
// * wieder markiert.
// ****
function MitarbeiterTreeSort()
{
	var i;
	var tree=document.getElementById('mitarbeiter-tree');
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
		i = 0;
	col = tree.columns ? tree.columns["mitarbeiter-treecol-uid"] : "mitarbeiter-treecol-uid";
	MitarbeiterSelectUid = tree.view.getCellText(i,col);
	MitarbeiterTreeLoadDataOnSelect=false;
	window.setTimeout("MitarbeiterTreeSelectMitarbeiter()",10);
}

// ****
// * Beim Auswaehlen eines Mitarbeiter-Filters werden die Mitarbeiter
// * die diesem Filter entsprechen geladen
// ****
function onMitarbeiterSelect()
{
	//Warnung falls Daten veraendert wurden aber noch nicht gespeichert
	if(MitarbeiterDetailValueChanged)
	{
		if(!confirm('Achtung! Die Daten wurden veraendert aber noch nicht gespeichert. Neuen Datensatz trotzdem laden? (Die geaenderten Daten gehen dabei verloren)'))
			return false;
	}

	MitarbeiterDetailValueChanged=false;

	var tree=document.getElementById('tree-menu-mitarbeiter');
	var col = tree.columns ? tree.columns["tree-menu-mitarbeiter-col-filter"] : "tree-menu-mitarbeiter-col-filter";

	if(tree.currentIndex==-1)
		return false;

	var filter=tree.view.getCellText(tree.currentIndex,col);
	var url = "<?php echo APP_ROOT; ?>rdf/personal.rdf.php";
	var attributes="?type=unknown";

	if(filter=="")
		filter="Studiengangsleiter";

	if (filter=="Studiengangsleiter")
	{
		attributes+="&stgl=true";
	}
	if (filter=="Fachbereichsleiter")
	{
		attributes+="&fbl=true";
	}
	if (filter=="Alle")
	{
		attributes+="&alle=true";
	}
	if (filter=="Aktive")
	{
		attributes+="&aktiv=true";
	}
	if (filter=="FixAngestellte")
	{
		attributes+="&fix=true&aktiv=true";
	}
	if (filter=="FixAngestellteAlle")
	{
		attributes+="&fix=true";
	}
	if (filter=="Inaktive")
	{
		attributes+="&aktiv=false";
	}
	if (filter=="Karenziert")
	{
		attributes+="&karenziert=true&aktiv=true";
	}
	if (filter=="ohneVerwendung")
	{
		attributes+="&verwendung=false&aktiv=true";
	}
	if (filter=="mitVerwendung")
	{
		attributes+="&verwendung=true&aktiv=false";
	}
	if (filter=="FreiAngestellte")
	{
		attributes+="&fix=false&aktiv=true";
	}
	if (filter=="FreiAngestellteAlle")
	{
		attributes+="&fix=false";
	}
	if(filter=="VertragNochNichtRetour")
	{
		attributes+="&VertragNochNichtRetour";
	}
	if(filter=="VertragHabilitiert")
	{
		attributes+="&VertragHabilitiert";
	}
	if(filter=="VertragNichtHabilitiert")
	{
		attributes+="&VertragNichtHabilitiert";
	}
	if(filter=="VertragNichtGedruckt")
	{
		attributes+="&VertragNichtGedruckt";
	}
	document.getElementById('mitarbeiter-toolbar-neu').disabled=false;
	//Timestamp anhaengen da beim Laden von Zwischengespeicherten Dateien kein
	//Observer Event ausgeloest wird.
	url+=attributes+'&'+gettimestamp();

	//Mitarbeiter Detail Felder deaktivieren
	MitarbeiterDetailDisableFields(true);

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	var tree=document.getElementById('mitarbeiter-tree');

	try
	{
		MitarbeiterTreeDatasource.removeXMLSinkObserver(MitarbeiterTreeSinkObserver);
		tree.builder.removeListener(MitarbeiterTreeListener);
	}
	catch(e)
	{}

	///Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}

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
// * Aktualisiert den MitarbeiterTree
// ****
function MitarbeiterTreeRefresh()
{
	var tree=document.getElementById('mitarbeiter-tree');
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
		i = 0;
	col = tree.columns ? tree.columns["mitarbeiter-treecol-uid"] : "mitarbeiter-treecol-uid";
	MitarbeiterSelectUid = tree.view.getCellText(i,col);
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	MitarbeiterTreeDatasource.Refresh(false);
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
	document.getElementById('mitarbeiter-detail-button-image-delete').disabled=val;
	document.getElementById('mitarbeiter-detail-button-image-infomail').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-anmerkung').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-homepage').disabled=val;

	//document.getElementById('mitarbeiter-detail-textbox-personalnummer').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-kurzbezeichnung').disabled=val;
	document.getElementById('mitarbeiter-detail-checkbox-lektor').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-stundensatz').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-telefonklappe').disabled=val;
	document.getElementById('mitarbeiter-detail-checkbox-fixangestellt').disabled=val;
	document.getElementById('mitarbeiter-detail-checkbox-bismelden').disabled=val;
	document.getElementById('mitarbeiter-detail-menulist-ort_kurzbz').disabled=val;
	document.getElementById('mitarbeiter-detail-menulist-standort').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-mitarbeiteranmerkung').disabled=val;
	document.getElementById('mitarbeiter-detail-menulist-ausbildung').disabled=val;
	document.getElementById('mitarbeiter-detail-button-speichern').disabled=val;
	document.getElementById('mitarbeiter-detail-textbox-alias').disabled=val;
	document.getElementById('mitarbeiter-detail-checkbox-kleriker').disabled=val;
}

function MitarbeiterCount()
{
    var tree = document.getElementById('mitarbeiter-tree');

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

function MitarbeiterAuswahl()
{
    document.getElementById('mitarbeiter-toolbar-label-anzahl').value = 'Anzahl: ' + MitarbeiterCount();

    if(!MitarbeiterTreeLoadDataOnSelect)
	{
		MitarbeiterTreeLoadDataOnSelect=true;
		return true;
	}
	if(!MitarbeiterTreeLoadDataOnSelect2)
		return true;

	//Warnung falls Daten veraendert aber noch nicht gespeichert wurden
	if(MitarbeiterDetailValueChanged)
	{
		if(!confirm('Achtung! Die Daten wurden veraendert aber noch nicht gespeichert. Neuen Datensatz trotzdem laden? (Die geaenderten Daten gehen dabei verloren)'))
			return false;
	}

	MitarbeiterDetailValueChanged=false;

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
			MitarbeiterFunktionIFrameUnLoad();
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
	bismelden=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bismelden" ));
	ausbildung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ausbildung" ));
	anmerkung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	ort_kurzbz=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ort_kurzbz" ));
	standort_id=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#standort_id" ));
	alias=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#alias" ));
	urlaubstageprojahr=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#urlaubstageprojahr" ));
	kleriker=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kleriker" ));

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

	if(bismelden=='Ja')
		document.getElementById('mitarbeiter-detail-checkbox-bismelden').checked=true;
	else
		document.getElementById('mitarbeiter-detail-checkbox-bismelden').checked=false;

	if(kleriker=='Ja')
		document.getElementById('mitarbeiter-detail-checkbox-kleriker').checked=true;
	else
		document.getElementById('mitarbeiter-detail-checkbox-kleriker').checked=false;

	document.getElementById('mitarbeiter-detail-menulist-ausbildung').value=ausbildung;
	document.getElementById('mitarbeiter-detail-textbox-mitarbeiteranmerkung').value=anmerkung;
	document.getElementById('mitarbeiter-detail-menulist-ort_kurzbz').value=ort_kurzbz;
	document.getElementById('mitarbeiter-detail-menulist-standort').value=standort_id;
	document.getElementById('mitarbeiter-detail-textbox-alias').value=alias;

	// ***** KONTAKTE *****
	document.getElementById('mitarbeiter-kontakt').setAttribute('src','kontakt.xul.php?person_id='+person_id);

	// ***** BETRIEBSMITTEL *****
	document.getElementById('mitarbeiter-betriebsmittel').setAttribute('src','betriebsmitteloverlay.xul.php?person_id='+person_id+'&uid='+uid);


	if(document.getElementById('mitarbeiter-tabs').selectedItem==document.getElementById('mitarbeiter-tab-termine'))
	{
		// ***** Termine *****
		document.getElementById('mitarbeiter-termine').setAttribute('src','termine.xul.php?mitarbeiter_uid='+uid);
	}

	// ***** UDF *****
	if (document.getElementById('mitarbeiter-tabs').selectedItem == document.getElementById('mitarbeiter-tab-udf'))
	{
		document.getElementById('mitarbeiter-udf').setAttribute('src', 'udf.xul.php?person_id='+person_id);
	}

	// **** VERWENDUNG ****
	verwendungtree = document.getElementById('mitarbeiter-tree-verwendung');
	url='<?php echo APP_ROOT;?>rdf/bisverwendung.rdf.php?uid='+uid+"&"+gettimestamp();

	try
	{
		MitarbeiterVerwendungTreeDatasource.removeXMLSinkObserver(MitarbeiterVerwendungTreeSinkObserver);
		verwendungtree.builder.removeListener(MitarbeiterVerwendungTreeListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = verwendungtree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		verwendungtree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	verwendungtree.builder.rebuild();


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

	try
	{
		MitarbeiterEntwicklungsteamTreeDatasource.removeXMLSinkObserver(MitarbeiterEntwicklungsteamTreeSinkObserver);
		entwicklungsteamtree.builder.removeListener(MitarbeiterEntwicklungsteamTreeListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = entwicklungsteamtree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		entwicklungsteamtree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	entwicklungsteamtree.builder.rebuild();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	MitarbeiterEntwicklungsteamTreeDatasource = rdfService.GetDataSource(url);
	MitarbeiterEntwicklungsteamTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	MitarbeiterEntwicklungsteamTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	entwicklungsteamtree.database.AddDataSource(MitarbeiterEntwicklungsteamTreeDatasource);
	MitarbeiterEntwicklungsteamTreeDatasource.addXMLSinkObserver(MitarbeiterEntwicklungsteamTreeSinkObserver);
	entwicklungsteamtree.builder.addListener(MitarbeiterEntwicklungsteamTreeListener);

	MitarbeiterEntwicklungsteamDetailDisableFields(true);
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

	//Funktionen Laden
	if(document.getElementById('mitarbeiter-tabs').selectedItem==document.getElementById('mitarbeiter-tab-funktionen'))
	{
		url = '<?php echo APP_ROOT; ?>content/funktionen.xul.php?uid='+uid;
		document.getElementById('mitarbeiter-funktionen').setAttribute('src',url);
	}

	// Buchungen laden falls vorhanden
	if(document.getElementById('mitarbeiter-tab-buchung'))
		MitarbeiterBuchungLoad(person_id)

	// Vertraege laden falls vorhanden
	if(document.getElementById('mitarbeiter-tab-vertrag'))
		MitarbeiterVertragLoad(person_id)

	// Notizen laden
	var mitarbeiternotiz = document.getElementById('mitarbeiter-box-notizen');
	mitarbeiternotiz.LoadNotizTree('','','','',person_id,'','','','');

	// Selektierungsfunktion der Addons aufrufen
	for(i in addon)
	{
		if(typeof addon[i].selectMitarbeiter=='function')
			addon[i].selectMitarbeiter(person_id, uid);
	}
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
// * Loescht das Bild aus der DB
// ****
function MitarbeiterImageDelete()
{
	person_id = document.getElementById('mitarbeiter-detail-textbox-person_id').value;
	if(person_id!='')
	{
		var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'imagedelete');
		req.add('person_id', person_id);
		req.add('studiengang_kz', '');

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
			uid = document.getElementById('mitarbeiter-detail-textbox-uid').value;
			MitarbeiterSelectUid=uid;
			MitarbeiterTreeDatasource.Refresh(false); //non blocking
			SetStatusBarText('Bild wurde geloescht');
		}
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
	personalnummer = document.getElementById('mitarbeiter-detail-textbox-personalnummer').value;
	kurzbezeichnung = document.getElementById('mitarbeiter-detail-textbox-kurzbezeichnung').value;
	stundensatz = document.getElementById('mitarbeiter-detail-textbox-stundensatz').value;
	telefonklappe = document.getElementById('mitarbeiter-detail-textbox-telefonklappe').value;
	lektor = document.getElementById('mitarbeiter-detail-checkbox-lektor').checked;
	fixangestellt = document.getElementById('mitarbeiter-detail-checkbox-fixangestellt').checked;
	bismelden = document.getElementById('mitarbeiter-detail-checkbox-bismelden').checked;
	ausbildung = document.getElementById('mitarbeiter-detail-menulist-ausbildung').value;
	anmerkung = document.getElementById('mitarbeiter-detail-textbox-mitarbeiteranmerkung').value;
	ort_kurzbz = document.getElementById('mitarbeiter-detail-menulist-ort_kurzbz').value;
	standort_id = document.getElementById('mitarbeiter-detail-menulist-standort').value;
	alias = document.getElementById('mitarbeiter-detail-textbox-alias').value;
	kleriker = document.getElementById('mitarbeiter-detail-checkbox-kleriker').checked;

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
	req.add('geburtsdatum', ConvertDateToISO(geburtsdatum));
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
	req.add('bismelden', bismelden);
	req.add('ausbildung', ausbildung);
	req.add('anmerkung', anmerkung);
	req.add('ort_kurzbz', ort_kurzbz);
	req.add('standort_id', standort_id);
	req.add('alias', alias);
	req.add('kleriker', kleriker);
	req.add('personalnummer', personalnummer);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	MitarbeiterDetailValueChanged=false;

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

// ****
// * Ruft das Formular zum Eintragen des neuen Mitarbeiters auf
// ****
function MitarbeiterNeu()
{
	window.open('<?php echo APP_ROOT; ?>vilesci/personen/import/mitarbeiterimport.php','Mitarbeiter anlegen', 'height=768,width=1024,resizable=yes,status=yes,scrollbars=yes,toolbar=yes,location=yes,menubar=yes');
}

// ****
// * Excel Export der Mitarbeiter
// ****
function MitarbeiterExport()
{
	var tree = document.getElementById('mitarbeiter-tree');
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
			var mitarbeiter_uid = getTreeCellText(tree, 'mitarbeiter-treecol-uid', v);
			data = data+';'+mitarbeiter_uid;
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
				mitarbeiter_uid = getTreeCellText(tree, 'mitarbeiter-treecol-uid', v);
				data = data+';'+mitarbeiter_uid;
			}
		}
	}

	action = '<?php echo APP_ROOT; ?>content/statistik/mitarbeiterexport.xls.php';
	OpenWindowPost(action, data);
}

// ****
// * Liefert anhand der ID den Namen der Klassenvariable
// ****
function MitarbeiterDetailgetSpaltenname(id)
{
	if(id=='mitarbeiter-treecol-anrede') return 'anrede';
	if(id=='mitarbeiter-treecol-titelpre') return 'titelpre';
	if(id=='mitarbeiter-treecol-vorname') return 'vorname';
	if(id=='mitarbeiter-treecol-vornamen') return 'vornamen';
	if(id=='mitarbeiter-treecol-nachname') return 'nachname';
	if(id=='mitarbeiter-treecol-titelpost') return 'titelpost';
	if(id=='mitarbeiter-treecol-personalnummer') return 'personalnummer';
	if(id=='mitarbeiter-treecol-geburtsdatum') return 'gebdatum';
	if(id=='mitarbeiter-treecol-geburtsdatum_iso') return 'gebdatum';
	if(id=='mitarbeiter-treecol-svnr') return 'svnr';
	if(id=='mitarbeiter-treecol-ersatzkennzeichen') return 'ersatzkennzeichen';
	if(id=='mitarbeiter-treecol-uid') return 'uid';
	if(id=='mitarbeiter-treecol-kurzbz') return 'kurzbz';
	if(id=='mitarbeiter-treecol-geschlecht') return 'geschlecht';
	if(id=='mitarbeiter-treecol-ort_kurzbz') return 'ort_kurzbz';
	if(id=='mitarbeiter-treecol-telefonklappe') return 'telefonklappe';
	if(id=='mitarbeiter-treecol-aktiv') return 'bnaktiv';
	if(id=='mitarbeiter-treecol-person_id') return 'person_id';
	if(id=='mitarbeiter-treecol-fixangestellt') return 'fixangestellt';
	if(id=='mitarbeiter-treecol-lektor') return 'lektor';
	if(id=='mitarbeiter-treecol-insertamum') return 'insertamum';
	if(id=='mitarbeiter-treecol-insertamum_iso') return 'insertamum';
	if(id=='mitarbeiter-treecol-insertvon') return 'insertvon';
	if(id=='mitarbeiter-treecol-updateamum') return 'updateamum';
	if(id=='mitarbeiter-treecol-updateamum_iso') return 'updateamum';
	if(id=='mitarbeiter-treecol-updatevon') return 'updatevon';
}

function MitarbeiterSendMail()
{
	mailempfaenger='';
	var treeMitarbeiter=document.getElementById('mitarbeiter-tree');
	var numRanges = treeMitarbeiter.view.selection.getRangeCount();
	var start = new Object();
	var end = new Object();
	var anzfault=0;
	//Markierte Datensaetze holen
	for (var t=0; t<numRanges; t++)
	{
  		treeMitarbeiter.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			var col = treeMitarbeiter.columns ? treeMitarbeiter.columns["mitarbeiter-treecol-uid"] : "mitarbeiter-treecol-uid";
  			if(treeMitarbeiter.view.getCellText(v,col).length>1)
  			{
  				if(mailempfaenger!='')
					mailempfaenger=mailempfaenger+'<?php echo $variable->variable->emailadressentrennzeichen; ?>'+treeMitarbeiter.view.getCellText(v,col)+'@<?php echo DOMAIN; ?>';
				else
					mailempfaenger='mailto:'+treeMitarbeiter.view.getCellText(v,col)+'@<?php echo DOMAIN; ?>';
  			}
  			else
  			{
  				anzfault=anzfault+1;
  			}
  		}
	}
	if(anzfault!=0)
		alert(anzfault+' MitarbeiterIn konnten nicht hinzugefuegt werden weil keine UID eingetragen ist!');
	window.location.href=mailempfaenger;
}

/**
 * Mail an Private EMailadresse senden
 */
function MitarbeiterSendMailPrivat()
{
	var treeMitarbeiter=document.getElementById('mitarbeiter-tree');
	var numRanges = treeMitarbeiter.view.selection.getRangeCount();
	var start = new Object();
	var end = new Object();
	var uids='';
	//Markierte Datensaetze holen
	for (var t=0; t<numRanges; t++)
	{
  		treeMitarbeiter.view.selection.getRangeAt(t,start,end);
  		for (v=start.value; v<=end.value; v++)
  		{
  			var col = treeMitarbeiter.columns ? treeMitarbeiter.columns["mitarbeiter-treecol-uid"] : "mitarbeiter-treecol-uid";
			uids=uids+';'+treeMitarbeiter.view.getCellText(v,col);
  		}
	}
	var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'getprivatemailadressUID');
	req.add('uids', uids);

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
				splitmailto(val.dbdml_data,'to');
		}
	}
	else
	{
		if(val.dbdml_data!='')
			splitmailto(val.dbdml_data,'bcc');
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

	try
	{
		MitarbeiterFunktionTreeDatasource.removeXMLSinkObserver(MitarbeiterFunktionTreeSinkObserver);
		funktiontree.builder.removeListener(MitarbeiterFunktionTreeListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = funktiontree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		funktiontree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	funktiontree.builder.rebuild();

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

// ****
// * Ruft den Dialog zum Bearbeiten der Verwendung auf
// ****
function MitarbeiterVerwendungBearbeiten()
{
	var tree=document.getElementById('mitarbeiter-tree-verwendung');

	if (tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Eintrag auswaehlen');
		return false;
	}

	//Uid der row holen
	col = tree.columns ? tree.columns["mitarbeiter-verwendung-treecol-mitarbeiter_uid"] : "mitarbeiter-verwendung-treecol-mitarbeiter_uid";
	mitarbeiter_uid=tree.view.getCellText(tree.currentIndex,col);

	//Bisverwendung_id holen
	col = tree.columns ? tree.columns["mitarbeiter-verwendung-treecol-bisverwendung_id"] : "mitarbeiter-verwendung-treecol-bisverwendung_id";
	bisverwendung_id=tree.view.getCellText(tree.currentIndex,col);

	//Dialog laden
	window.open("<?php echo APP_ROOT; ?>content/mitarbeiter/mitarbeiterverwendungdialog.xul.php?mitarbeiter_uid="+mitarbeiter_uid+"&bisverwendung_id="+bisverwendung_id,"popup","chrome, status=no, width=500, height=500, centerscreen, resizable");
}

// ****
// * Ruft den Dialog zum Eintragen der Verwendungen auf
// ****
function MitarbeiterVerwendungNeu()
{
	var tree=document.getElementById('mitarbeiter-tree');

	//Uid der row holen
	col = tree.columns ? tree.columns["mitarbeiter-treecol-uid"] : "mitarbeiter-treecol-uid";
	mitarbeiter_uid=tree.view.getCellText(tree.currentIndex,col);

	window.open("<?php echo APP_ROOT; ?>content/mitarbeiter/mitarbeiterverwendungdialog.xul.php?mitarbeiter_uid="+mitarbeiter_uid,"popup","chrome, status=no, width=500, height=500, centerscreen, resizable");
}

function MitarbeiterVerwendungSpeichern(dialog, bisverwendung_id, mitarbeiter_uid, neu)
{
	ba1code = dialog.getElementById('mitarbeiter-verwendung-detail-menulist-beschart1').value;
	ba2code = dialog.getElementById('mitarbeiter-verwendung-detail-menulist-beschart2').value;
	beschausmasscode = dialog.getElementById('mitarbeiter-verwendung-detail-menulist-ausmass').value;
	verwendung_code = dialog.getElementById('mitarbeiter-verwendung-detail-menulist-verwendung').value;
	hauptberufcode = dialog.getElementById('mitarbeiter-verwendung-detail-menulist-hauptberuf').value;
	hauptberuflich = dialog.getElementById('mitarbeiter-verwendung-detail-checkbox-hauptberuflich').checked;
	habilitation = dialog.getElementById('mitarbeiter-verwendung-detail-checkbox-habilitation').checked;
	beginn = dialog.getElementById('mitarbeiter-verwendung-detail-datum-beginn').value;
	ende = dialog.getElementById('mitarbeiter-verwendung-detail-datum-ende').value;
	vertragsstunden = dialog.getElementById('mitarbeiter-verwendung-detail-textbox-vertragsstunden').value;
	dv_art = dialog.getElementById('mitarbeiter-verwendung-detail-textbox-dv_art').value;
	inkludierte_lehre = dialog.getElementById('mitarbeiter-verwendung-detail-textbox-inkludierte_lehre').value;
	zeitaufzeichnungspflichtig = dialog.getElementById('mitarbeiter-verwendung-detail-checkbox-zeitaufzeichnungspflichtig').checked;

	if(verwendung_code=='1')
	{
		if(hauptberuflich==true)
		{
			hauptberufcode='';
		}
		else
		{
			if(hauptberufcode=='')
			{
				alert('Hauptberuf muss ausgewaehlt werden');
				return false;
			}
		}
	}
	else
	{
		if(hauptberuflich==true)
		{
			alert('Hauptberuflich darf bei dieser Verwendung nicht gesetzt sein');
			return false;
		}
	}

	if(beginn!='' && !CheckDatum(beginn))
	{
		alert('Beginn Datum ist ungueltig');
		return false;
	}

	if(ende!='' && !CheckDatum(ende))
	{
		alert('Ende Datum ist ungueltig');
		return false;
	}

	var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'verwendungsave');

	req.add('neu', neu);
	req.add('mitarbeiter_uid', mitarbeiter_uid);
	req.add('bisverwendung_id', bisverwendung_id);
	req.add('ba1code', ba1code);
	req.add('ba2code', ba2code);
	req.add('beschausmasscode', beschausmasscode);
	req.add('verwendung_code', verwendung_code);
	req.add('hauptberufcode', hauptberufcode);
	req.add('hauptberuflich', hauptberuflich);
	req.add('habilitation', habilitation);
	req.add('beginn', ConvertDateToISO(beginn));
	req.add('ende', ConvertDateToISO(ende));
	req.add('vertragsstunden', vertragsstunden);
	req.add('dv_art', dv_art);
	req.add('inkludierte_lehre', inkludierte_lehre);
	req.add('zeitaufzeichnungspflichtig', zeitaufzeichnungspflichtig);

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
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		MitarbeiterVerwendungSelectID = val.dbdml_data;
		MitarbeiterVerwendungTreeDatasource.Refresh(false);
		return true;
	}
}

// ****
// * Loescht eine Bisverwendung
// ****
function MitarbeiterVerwendungLoeschen()
{
	var tree=document.getElementById('mitarbeiter-tree-verwendung');

	if (tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Eintrag auswaehlen');
		return false;
	}
	//Bisverwendung_id holen
	col = tree.columns ? tree.columns["mitarbeiter-verwendung-treecol-bisverwendung_id"] : "mitarbeiter-verwendung-treecol-bisverwendung_id";
	bisverwendung_id=tree.view.getCellText(tree.currentIndex,col);

	if(confirm('Diese Verwendung wirklich loeschen?'))
	{

		var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'verwendungdelete');

		req.add('bisverwendung_id', bisverwendung_id);

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
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			MitarbeiterVerwendungTreeDatasource.Refresh(false);
			return true;
		}
	}
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
	document.getElementById('mitarbeiter-funktion-button-loeschen').disabled=val;
	if(val)
		MitarbeiterFunktionDetailDisableFields(true);
}

// ****
// * De-/Aktiviert die Detailfelder
// ****
function MitarbeiterFunktionDetailDisableFields(val)
{
	document.getElementById('mitarbeiter-funktion-detail-button-speichern').disabled=val;
	document.getElementById('mitarbeiter-funktion-detail-textbox-sws').disabled=val,
	document.getElementById('mitarbeiter-funktion-detail-menulist-studiengang').disabled=val;
}

// ****
// * Legt eine neue Funktion an
// ****
function MitarbeiterFunktionNeu()
{
	document.getElementById('mitarbeiter-funktion-detail-checkbox-neu').checked=true;
	document.getElementById('mitarbeiter-funktion-detail-textbox-studiengang').value='';
	document.getElementById('mitarbeiter-funktion-detail-textbox-sws').value='';
	MitarbeiterFunktionDetailDisableFields(false);
}

// ****
// * Speichert einen Funktionseintrag
// ****
function MitarbeiterFunktionSpeichern()
{
	studiengang_kz = document.getElementById('mitarbeiter-funktion-detail-menulist-studiengang').value;
	sws = document.getElementById('mitarbeiter-funktion-detail-textbox-sws').value;
	neu = document.getElementById('mitarbeiter-funktion-detail-checkbox-neu').checked;
	studiengang_kz_old = document.getElementById('mitarbeiter-funktion-detail-textbox-studiengang').value;

	if(studiengang_kz=='')
	{
		alert('Bitte einen Studiengang auswaehlen');
		return false;
	}

	//Bisverwendung_id holen
	var tree=document.getElementById('mitarbeiter-tree-verwendung');

	if (tree.currentIndex==-1)
	{
		alert('Es wurde keine Verwendung ausgewaehlt');
		return false;
	}

	col = tree.columns ? tree.columns["mitarbeiter-verwendung-treecol-bisverwendung_id"] : "mitarbeiter-verwendung-treecol-bisverwendung_id";
	bisverwendung_id=tree.view.getCellText(tree.currentIndex,col);

	var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'funktionsave');

	req.add('neu', neu);
	req.add('studiengang_kz', studiengang_kz);
	req.add('studiengang_kz_old', studiengang_kz_old);
	req.add('sws', sws);
	req.add('bisverwendung_id', bisverwendung_id);

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
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		MitarbeiterFunktionSelectVerwendungID = bisverwendung_id;
		MitarbeiterFunktionSelectStudiengangID = studiengang_kz;
		MitarbeiterFunktionTreeDatasource.Refresh(false);
		MitarbeiterFunktionDetailDisableFields(true);
		return true;
	}
}

// ****
// * bei der Auswahl einer Funktion wird diese zum Bearbeiten geladen
// ****
function MitarbeiterFunktionSelect()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	//Daten laden
	tree = document.getElementById('mitarbeiter-tree-funktion');

	if (tree.currentIndex==-1)
		return false;

	col = tree.columns ? tree.columns["mitarbeiter-funktion-treecol-bisverwendung_id"] : "mitarbeiter-funktion-treecol-bisverwendung_id";
	bisverwendung_id=tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["mitarbeiter-funktion-treecol-studiengang_kz"] : "mitarbeiter-funktion-treecol-studiengang_kz";
	studiengang_kz=tree.view.getCellText(tree.currentIndex,col);

	var url = '<?php echo APP_ROOT ?>rdf/bisfunktion.rdf.php?bisverwendung_id='+bisverwendung_id+'&studiengang_kz='+studiengang_kz+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/bisfunktion/" + bisverwendung_id+'/'+studiengang_kz);

	var predicateNS = "http://www.technikum-wien.at/bisfunktion/rdf";

	//RDF parsen

	var sws = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#sws" ));

	document.getElementById('mitarbeiter-funktion-detail-menulist-studiengang').value=studiengang_kz;
	document.getElementById('mitarbeiter-funktion-detail-textbox-sws').value=sws;
	document.getElementById('mitarbeiter-funktion-detail-checkbox-neu').checked=false;
	document.getElementById('mitarbeiter-funktion-detail-textbox-studiengang').value=studiengang_kz;
	MitarbeiterFunktionDetailDisableFields(false);
}

// ****
// * Loescht eine BISFunktion
// ****
function MitarbeiterFunktionLoeschen()
{
	//Daten laden
	tree = document.getElementById('mitarbeiter-tree-funktion');

	if (tree.currentIndex==-1)
	{
		alert('Es wurde keine Verwendung ausgewaehlt');
		return false;
	}

	col = tree.columns ? tree.columns["mitarbeiter-funktion-treecol-bisverwendung_id"] : "mitarbeiter-funktion-treecol-bisverwendung_id";
	bisverwendung_id=tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["mitarbeiter-funktion-treecol-studiengang_kz"] : "mitarbeiter-funktion-treecol-studiengang_kz";
	studiengang_kz=tree.view.getCellText(tree.currentIndex,col);

	if(confirm("Wollen Sie diese Funktion wirklich loeschen?"))
	{
		var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'funktiondelete');

		req.add('studiengang_kz', studiengang_kz);
		req.add('bisverwendung_id', bisverwendung_id);

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
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			MitarbeiterFunktionTreeDatasource.Refresh(false);
			MitarbeiterFunktionDetailDisableFields(true);
			return true;
		}
	}
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
	document.getElementById('mitarbeiter-entwicklungsteam-button-loeschen').disabled=val;

	if(val)
		MitarbeiterEntwicklungsteamDetailDisableFields(val)
}

// ****
// * De-/Aktiviert die Detailfelder
// ****
function MitarbeiterEntwicklungsteamDetailDisableFields(val)
{
	document.getElementById('mitarbeiter-entwicklungsteam-detail-menulist-studiengang').disabled=val;
	document.getElementById('mitarbeiter-entwicklungsteam-detail-menulist-besqual').disabled=val;
	document.getElementById('mitarbeiter-entwicklungsteam-detail-datum-beginn').disabled=val;
	document.getElementById('mitarbeiter-entwicklungsteam-detail-datum-ende').disabled=val;
	document.getElementById('mitarbeiter-entwicklungsteam-detail-button-speichern').disabled=val;
}

// ****
// * Neuen Datensatz anlegen
// ****
function MitarbeiterEntwicklungsteamNeu()
{
	document.getElementById('mitarbeiter-entwicklungsteam-detail-checkbox-neu').checked=true;
	document.getElementById('mitarbeiter-entwicklungsteam-detail-menulist-besqual').value='0';
	document.getElementById('mitarbeiter-entwicklungsteam-detail-datum-beginn').value='';
	document.getElementById('mitarbeiter-entwicklungsteam-detail-datum-ende').value='';
	MitarbeiterEntwicklungsteamDetailDisableFields(false);
}

// ****
// * Beim Markieren eines Datensatzes wird dieser geladen
// ****
function MitarbeiterEntwicklungsteamSelect()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	//Daten laden
	tree = document.getElementById('mitarbeiter-tree-entwicklungsteam');

	if (tree.currentIndex==-1)
		return false;

	col = tree.columns ? tree.columns["mitarbeiter-entwicklungsteam-treecol-studiengang_kz"] : "mitarbeiter-entwicklungsteam-treecol-studiengang_kz";
	studiengang_kz=tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["mitarbeiter-entwicklungsteam-treecol-mitarbeiter_uid"] : "mitarbeiter-entwicklungsteam-treecol-mitarbeiter_uid";
	mitarbeiter_uid=tree.view.getCellText(tree.currentIndex,col);

	var url = '<?php echo APP_ROOT ?>rdf/entwicklungsteam.rdf.php?studiengang_kz='+studiengang_kz+'&mitarbeiter_uid='+mitarbeiter_uid+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/entwicklungsteam/" + mitarbeiter_uid+'/'+studiengang_kz);

	var predicateNS = "http://www.technikum-wien.at/entwicklungsteam/rdf";

	//RDF parsen

	var besqualcode = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#besqualcode" ));
	var beginn = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beginn" ));
	var ende = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ende" ));

	document.getElementById('mitarbeiter-entwicklungsteam-detail-menulist-studiengang').value=studiengang_kz;
	document.getElementById('mitarbeiter-entwicklungsteam-detail-menulist-besqual').value=besqualcode;
	document.getElementById('mitarbeiter-entwicklungsteam-detail-checkbox-neu').checked=false;
	document.getElementById('mitarbeiter-entwicklungsteam-detail-textbox-studiengang').value=studiengang_kz;
	document.getElementById('mitarbeiter-entwicklungsteam-detail-datum-beginn').value=beginn;
	document.getElementById('mitarbeiter-entwicklungsteam-detail-datum-ende').value=ende;
	MitarbeiterEntwicklungsteamDetailDisableFields(false);
}

// ****
// * Speichert die Entwicklungsteam Daten
// ****
function MitarbeiterEntwicklungsteamSpeichern()
{
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	studiengang_kz = document.getElementById('mitarbeiter-entwicklungsteam-detail-menulist-studiengang').value;
	besqualcode = document.getElementById('mitarbeiter-entwicklungsteam-detail-menulist-besqual').value;
	neu = document.getElementById('mitarbeiter-entwicklungsteam-detail-checkbox-neu').checked;
	studiengang_kz_old = document.getElementById('mitarbeiter-entwicklungsteam-detail-textbox-studiengang').value;
	beginn = document.getElementById('mitarbeiter-entwicklungsteam-detail-datum-beginn').value;
	ende = document.getElementById('mitarbeiter-entwicklungsteam-detail-datum-ende').value;

	if(studiengang_kz=='')
	{
		alert('Bitte einen Studiengang auswaehlen');
		return false;
	}
	var tree = document.getElementById('mitarbeiter-tree');

	if (tree.currentIndex==-1)
	{
		alert('Es ist kein/e MitarbeiterIn ausgewaehlt');
		return;
	}

	//Ausgewaehlte UID holen
    var col = tree.columns ? tree.columns["mitarbeiter-treecol-uid"] : "mitarbeiter-treecol-uid";
	var mitarbeiter_uid=tree.view.getCellText(tree.currentIndex,col);

	var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'entwicklungsteamsave');

	req.add('neu', neu);
	req.add('studiengang_kz', studiengang_kz);
	req.add('studiengang_kz_old', studiengang_kz_old);
	req.add('besqualcode', besqualcode);
	req.add('mitarbeiter_uid', mitarbeiter_uid);
	req.add('beginn', ConvertDateToISO(beginn));
	req.add('ende', ConvertDateToISO(ende));

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
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		MitarbeiterEntwicklungsteamSelectMitarbeiterUID = mitarbeiter_uid;
		MitarbeiterEntwicklungsteamSelectStudiengangID = studiengang_kz;
		MitarbeiterEntwicklungsteamDoubleRefresh=true;
		MitarbeiterEntwicklungsteamTreeDatasource.Refresh(false);
		MitarbeiterEntwicklungsteamDetailDisableFields(true);
		return true;
	}
}

// ****
// * Loescht einen Entwicklungsteameintrag
// ****
function MitarbeiterEntwicklungsteamLoeschen()
{
	//Daten laden
	tree = document.getElementById('mitarbeiter-tree-entwicklungsteam');

	if (tree.currentIndex==-1)
	{
		alert('Es wurde keine Eintrag ausgewaehlt');
		return false;
	}

	col = tree.columns ? tree.columns["mitarbeiter-entwicklungsteam-treecol-studiengang_kz"] : "mitarbeiter-entwicklungsteam-treecol-studiengang_kz";
	studiengang_kz=tree.view.getCellText(tree.currentIndex,col);

	col = tree.columns ? tree.columns["mitarbeiter-entwicklungsteam-treecol-mitarbeiter_uid"] : "mitarbeiter-entwicklungsteam-treecol-mitarbeiter_uid";
	mitarbeiter_uid=tree.view.getCellText(tree.currentIndex,col);

	if(confirm("Wollen Sie diesen Eintrag wirklich loeschen?"))
	{
		var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'entwicklungsteamdelete');

		req.add('studiengang_kz', studiengang_kz);
		req.add('mitarbeiter_uid', mitarbeiter_uid);

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
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			MitarbeiterEntwicklungsteamTreeDatasource.Refresh(false);
			MitarbeiterEntwicklungsteamDetailDisableFields(true);
			return true;
		}
	}
}

// ****
// * Refresh des Entwicklungsteam Trees
// ****
function MitarbeiterEntwicklungsteamTreeRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	MitarbeiterEntwicklungsteamTreeDatasource.Refresh(false);
}

// ****
// * Laedt den Funktionen IFrame
// ****
function MitarbeiterFunktionIFrameLoad()
{
	if(document.getElementById('mitarbeiter-funktionen').getAttribute('src')=='')
	{
		uid = document.getElementById('mitarbeiter-detail-textbox-uid').value;
		if(uid!='')
		{
			url = '<?php echo APP_ROOT; ?>content/funktionen.xul.php?uid='+uid;
			document.getElementById('mitarbeiter-funktionen').setAttribute('src',url);
		}
	}
}

// ****
// * IFrame ins leere Zeigen lassen
// ****
function MitarbeiterFunktionIFrameUnLoad()
{
	document.getElementById('mitarbeiter-funktionen').setAttribute('src','');
}

// ****
// * Wenn die Sozialversicherungsnummer eingegeben wird, dann wird automatisch das
// * Geburtsdatum-Feld befuellt
// ****
function MitarbeiterGenerateGebDatFromSVNR()
{
	var svnr = document.getElementById('mitarbeiter-detail-textbox-svnr').value;

	if(svnr!='' && svnr.length==10)
		document.getElementById('mitarbeiter-detail-textbox-geburtsdatum').value = svnr.charAt(4) + svnr.charAt(5) + "." + svnr.charAt(6) + svnr.charAt(7) + ".19" + svnr.charAt(8) + svnr.charAt(9);
}

// ****
// * Startet die Personensuche
// ****
function MitarbeiterSuche()
{
	//Warnung wenn Daten veraendert aber noch nicht gespeichert wurden
	if(MitarbeiterDetailValueChanged)
	{
		if(!confirm('Achtung! Die Daten wurden veraendert aber noch nicht gespeichert. Neuen Datensatz trotzdem laden? (Die geaenderten Daten gehen dabei verloren)'))
			return false;
	}

	MitarbeiterDetailValueChanged=false;

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	filter = document.getElementById('mitarbeiter-toolbar-textbox-suche').value;
	var treeMitarbeiterMenu=document.getElementById('tree-menu-mitarbeiter');
	treeMitarbeiterMenu.currentIndex=-1;
	treeMitarbeiterMenu.view.selection.clearSelection();

	//Wenn mehr als 2 Zeichen eingegeben wurden, die Personensuche starten
	if(filter.length>2)
	{
		//Datasource setzen und Felder deaktivieren
		url = "<?php echo APP_ROOT; ?>rdf/personal.rdf.php?filter="+encodeURIComponent(filter)+"&"+gettimestamp();

		var treeMitarbeiter=document.getElementById('mitarbeiter-tree');

		try
		{
			MitarbeiterTreeDatasource.removeXMLSinkObserver(MitarbeiterTreeSinkObserver);
			treeMitarbeiter.builder.removeListener(MitarbeiterTreeListener);
		}
		catch(e)
		{}

		//Alte DS entfernen
		var oldDatasources = treeMitarbeiter.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			treeMitarbeiter.database.RemoveDataSource(oldDatasources.getNext());
		}
		treeMitarbeiter.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		MitarbeiterTreeDatasource = rdfService.GetDataSource(url);
		MitarbeiterTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		MitarbeiterTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		treeMitarbeiter.database.AddDataSource(MitarbeiterTreeDatasource);
		MitarbeiterTreeDatasource.addXMLSinkObserver(MitarbeiterTreeSinkObserver);
		treeMitarbeiter.builder.addListener(MitarbeiterTreeListener);

		//Detailfelder Deaktivieren
		MitarbeiterDetailDisableFields(true);
	}
	else
		alert('Es muessen mindestens 3 Zeichen eingegeben werden');
}

function MitarbeiterImageInfomail()
{
	var uid = document.getElementById('mitarbeiter-detail-textbox-uid').value;
	var anrede = document.getElementById('mitarbeiter-detail-textbox-anrede').value;
	var nachname = document.getElementById('mitarbeiter-detail-textbox-nachname').value;
	var sg='';
	if(anrede=='Frau')
		sg = 'Sehr geehrte';
	else
		sg = 'Sehr geehrter';
	if(uid!='')
	{
		body=sg+" "+anrede+" "+nachname+"!%0A%0AIhr Profilbild wurde entfernt, da es nicht den aktuellen Bildrichtlinen entspricht.%0ABitte laden Sie unter CIS->Profil ein neues Profilbild hoch.";
		window.location.href="mailto:"+uid+"@<?php echo DOMAIN;?>?subject=Profilbild&body="+body;
	}
	else
	{
		alert('Nur bei Studenten verfügbar');
	}
}

// ****
// * Wenn im Suchfeld Enter gedrueckt wird, dann die Suchfunktion starten
// ****
function MitarbeiterSearchFieldKeyPress(event)
{
	if(event.keyCode==13) //Enter
		MitarbeiterSuche();
}

function MitarbeiterDetailValueChange()
{
	MitarbeiterDetailValueChanged=true;
}

function MitarbeiterTreeFunktionSort()
{
	debug('ToDo: MitarbeiterTreeFunktionSort');
}

function MitarbeiterTreeEntwicklungsteamSort()
{
	debug('ToDo: MitarbeiterTreeEntwicklungsteamSort');
}

function MitarbeiterTreeVerwendungSort()
{
	debug('ToDo: MitarbeiterTreeVerwendungSort');
}


// ****
// * Oeffnet ein Fenster mit den Details der gesamten Person
// ****
function MitarbeiterShowPersonendetails()
{
	person_id = document.getElementById('mitarbeiter-detail-textbox-person_id').value;
	window.open('<?php echo APP_ROOT ?>vilesci/personen/personendetails.php?id='+person_id,'Personendetails','');
}

function MitarbeiterTermineIFrameLoad()
{
	uid = document.getElementById('mitarbeiter-detail-textbox-uid').value;
	if(uid!='')
	{
		url = 'termine.xul.php?mitarbeiter_uid='+uid+'&ts='+gettimestamp();
		document.getElementById('mitarbeiter-termine').setAttribute('src',url);
	}
}

// ****
// * Load UDF IFrame
// ****
function MitarbeiterUDFIFrameLoad()
{
	var tree = document.getElementById('mitarbeiter-tree');

	if (tree.currentIndex == -1) return;

	try
	{
		//Ausgewaehlte person_id holen
		var person_id = getTreeCellText(tree, 'mitarbeiter-treecol-person_id', tree.currentIndex);

		url = 'udf.xul.php?person_id='+person_id;
		document.getElementById('mitarbeiter-udf').setAttribute('src', url);
	}
	catch(e) {}
}
