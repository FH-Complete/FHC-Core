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

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/studiengang.class.php');

$user = get_uid();
loadVariables($user);

?>
// ********** GLOBALE VARIABLEN ********** //
var FunktionenTreeDatasource=''; // Datasource des Adressen Trees
var FunktionenSelectID='';
var FunktionenUID=null;
var FunktionBezeichnungChanged=false;
<?php

// JS-Variable anlegen mit einer Uebersetzungstabellle
// Studiengang zu Organisationseinheit
$stg_obj = new studiengang();
$stg_obj->getAll();

echo 'var organisationseinheit = {';
$content = '';
foreach ($stg_obj->result as $stg)
	$content .= '"'.$stg->studiengang_kz.'":"'.$stg->oe_kurzbz.'",';
echo substr($content, 0, mb_strlen($content)-1).'};';

?>
// ********** LISTENER UND OBSERVER ********** //

// ****
// * Observer fuer Adressen Tree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var FunktionenTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('funktion-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var FunktionenTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(FunktionenTreeSelectID,10);
  }
};

// ********** FUNKTIONEN ********** //
function FunktionFilter()
{
	var filter = document.getElementById('funktionen-button-filter')
	var state = document.getElementById('funktionen-filter-state')
	if(state.value == 'alle')
	{
		state.value = 'offene';
		loadFunktionen(FunktionenUID);
		filter.label = 'Alle anzeigen';
		setVariable('fasfunktionfilter', 'offene');
	}
	else
	{
		state.value = 'alle';
		loadFunktionen(FunktionenUID);
		filter.label = 'Nur aktuelle anzeigen';
		setVariable('fasfunktionfilter', 'alle')
	}
}

// ****
// * Laedt die Trees
// ****
function loadFunktionen(uid)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	FunktionenUID = uid;
	var filter = document.getElementById('funktionen-filter-state');

	//Adressen laden
	url = "<?php echo APP_ROOT; ?>rdf/benutzerfunktion.rdf.php?uid="+uid+"&filter="+filter.value+"&"+gettimestamp();

	var tree=document.getElementById('funktion-tree');

	try
	{
		FunktionenTreeDatasource.removeXMLSinkObserver(FunktionenTreeSinkObserver);
		tree.builder.removeListener(FunktionenTreeListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	FunktionenTreeDatasource = rdfService.GetDataSource(url);
	FunktionenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	FunktionenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	tree.database.AddDataSource(FunktionenTreeDatasource);
	FunktionenTreeDatasource.addXMLSinkObserver(FunktionenTreeSinkObserver);
	tree.builder.addListener(FunktionenTreeListener);
	FunktionDisableFields(false);

}

// ****
// * Selectiert eine Funktion nachdem der Tree
// * rebuildet wurde.
// ****
function FunktionenTreeSelectID()
{
	var tree=document.getElementById('funktion-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende Adresse gespeichert
	if(FunktionenSelectID!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["funktion-treecol-benutzerfunktion_id"] : "funktion-treecol-benutzerfunktion_id";
			id=tree.view.getCellText(i,col);

			if(id == FunktionenSelectID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	   	FunktionenSelectID=null;
	}
}

// ****
// * Deaktiviert die Felder und setzt den Neu status
// ****
function FunktionNeu()
{
	FunktionDetailResetFields();
	FunktionDetailDisableFields(false);
	document.getElementById('funktion-checkbox-neu').checked=true;
	document.getElementById('funktion-textbox-benutzerfunktion_id').value='';

	//Wenn die aktuelle Person ein Student ist,
	//dann wird Studiengang und 'Studentenvertreter' vorausgewaehlt
	if(window.parent.document.getElementById('main-content-tabs').selectedItem==window.parent.document.getElementById('tab-mitarbeiter'))
		oe_kurzbz='';
	else
		oe_kurzbz = organisationseinheit[ window.parent.document.getElementById('student-prestudent-menulist-studiengang_kz').value ];

	if(oe_kurzbz!='')
	{
		document.getElementById('funktion-menulist-oe_kurzbz').value=oe_kurzbz;
		document.getElementById('funktion-menulist-funktion').value='stdv';
	}

	var Datum = new Date()
	var Jahr = Datum.getFullYear()
	var Tag = Datum.getDate()
	var Monat = Datum.getMonth()+1

	document.getElementById('funktion-box-datum_von').value=Tag+'.'+Monat+'.'+Jahr;
	document.getElementById('funktion-box-datum_bis').value='';
	document.getElementById('funktion-textbox-bezeichnung').value='';
	document.getElementById('funktion-textbox-wochenstunden').value='';
	FunktionToggleFachbereich();
}

// ****
// * Loescht eine Funktion
// ****
function FunktionDelete()
{
	tree = document.getElementById('funktion-tree');

	if (tree.currentIndex==-1)
	{
		alert('Bitte zuerst eine Funktion auswaehlen');
		return;
	}

	//Ausgewaehlte ID holen
    var col = tree.columns ? tree.columns["funktion-treecol-benutzerfunktion_id"] : "funktion-treecol-benutzerfunktion_id";
	var benutzerfunktion_id=tree.view.getCellText(tree.currentIndex,col);

	//Bei Mitarbeitern wird kein Studiengang mitgeschickt
	if(window.parent.document.getElementById('main-content-tabs').selectedItem==window.parent.document.getElementById('tab-mitarbeiter'))
		studiengang_kz='';
	else
		studiengang_kz = window.parent.document.getElementById('student-prestudent-menulist-studiengang_kz').value;

	if(confirm('Soll diese Funktion wirklich geloescht werden?'))
	{
		var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'funktiondelete');

		req.add('benutzerfunktion_id', benutzerfunktion_id);
		req.add('studiengang_kz', studiengang_kz);

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
			FunktionenTreeDatasource.Refresh(false);
			FunktionDetailResetFields();
			FunktionDetailDisableFields(true);
			return true;
		}
	}
}

// ****
// * Speichert die Daten
// ****
function FunktionDetailSpeichern(kopie)
{
	var funktion_kurzbz = document.getElementById('funktion-menulist-funktion').value;
	var oe_kurzbz = document.getElementById('funktion-menulist-oe_kurzbz').value;
	var semester = document.getElementById('funktion-menulist-semester').value;
	var fachbereich_kurzbz = document.getElementById('funktion-menulist-fachbereich').value;
	var neu = document.getElementById('funktion-checkbox-neu').checked;
	var benutzerfunktion_id = document.getElementById('funktion-textbox-benutzerfunktion_id').value;
	var datum_von = document.getElementById('funktion-box-datum_von').value;
	var datum_bis = document.getElementById('funktion-box-datum_bis').value;
	var bezeichnung = document.getElementById('funktion-textbox-bezeichnung').value;
	var wochenstunden = document.getElementById('funktion-textbox-wochenstunden').value;

	if(kopie == true)
		var neu = true;

	//Bei Mitarbeitern wird kein Studiengang mitgeschickt
	if(window.parent.document.getElementById('main-content-tabs').selectedItem==window.parent.document.getElementById('tab-mitarbeiter'))
		studiengang_kz_berecht='';
	else
		studiengang_kz_berecht = window.parent.document.getElementById('student-prestudent-menulist-studiengang_kz').value;

	var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
	var req = new phpRequest(url,'','');

	//Wenn Fachbereich ausgeblendet ist, dann sicherheitshalber auf '' setzen
	if(document.getElementById('funktion-menulist-fachbereich').hidden==true)
		fachbereich_kurzbz='';

	req.add('type', 'funktionsave');

	req.add('funktion_kurzbz', funktion_kurzbz);
	req.add('oe_kurzbz', oe_kurzbz);
	req.add('semester', semester);
	req.add('studiengang_kz_berecht', studiengang_kz_berecht);
	req.add('fachbereich_kurzbz', fachbereich_kurzbz);
	req.add('uid', FunktionenUID);
	req.add('neu', neu);
	req.add('benutzerfunktion_id', benutzerfunktion_id);
	req.add('datum_von', ConvertDateToISO(datum_von));
	req.add('datum_bis', ConvertDateToISO(datum_bis));
	req.add('bezeichnung', bezeichnung);
	req.add('wochenstunden', wochenstunden);

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
		if(val.dbdml_errormsg!='' && val.dbdml_errormsg!='unknown')
			alert(val.dbdml_errormsg);
		FunktionBezeichnungChanged=false;
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		FunktionenSelectID=val.dbdml_data;
		FunktionenTreeDatasource.Refresh(false);
		document.getElementById('funktion-checkbox-neu').checked=false;
		document.getElementById('funktion-textbox-benutzerfunktion_id').value=FunktionenSelectID;
		return true;
	}
}

// ****
// * Daten zum Bearbeiten laden
// ****
function FunktionBearbeiten()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	tree = document.getElementById('funktion-tree');

	if (tree.currentIndex==-1)
		return;

	//Ausgewaehlte Nr holen
    var col = tree.columns ? tree.columns["funktion-treecol-benutzerfunktion_id"] : "funktion-treecol-benutzerfunktion_id";
	var benutzerfunktion_id=tree.view.getCellText(tree.currentIndex,col);

	//Daten holen
	var url = '<?php echo APP_ROOT ?>rdf/benutzerfunktion.rdf.php?benutzerfunktion_id='+benutzerfunktion_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/bnfunktion/"+benutzerfunktion_id);

	var predicateNS = "http://www.technikum-wien.at/bnfunktion/rdf";

	//Daten holen
	var fachbereich_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#fachbereich_kurzbz" ));
	var uid = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#uid" ));
	var oe_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#oe_kurzbz" ));
	var semester = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#semester" ));
	var funktion_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#funktion_kurzbz" ));
	var datum_von = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#datum_von" ));
	var datum_bis = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#datum_bis" ));
	var bezeichnung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bezeichnung" ));
	var wochenstunden = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#wochenstunden" ));

	document.getElementById('funktion-menulist-fachbereich').value=fachbereich_kurzbz;
	document.getElementById('funktion-menulist-oe_kurzbz').value=oe_kurzbz;
	document.getElementById('funktion-menulist-semester').value=semester;
	document.getElementById('funktion-menulist-funktion').value=funktion_kurzbz;
	document.getElementById('funktion-textbox-benutzerfunktion_id').value=benutzerfunktion_id;
	document.getElementById('funktion-checkbox-neu').checked = false;
	document.getElementById('funktion-box-datum_von').value=datum_von;
	document.getElementById('funktion-box-datum_bis').value=datum_bis;
	document.getElementById('funktion-textbox-bezeichnung').value=bezeichnung;
	document.getElementById('funktion-textbox-wochenstunden').value=wochenstunden;

	FunktionBezeichnungChanged=true;
	FunktionDetailDisableFields(false);
	FunktionToggleFachbereich();
}

// ****
// * De-/Aktiviert die Buttons
// ****
function FunktionDisableFields(val)
{
	document.getElementById('funktion-button-neu').disabled=val;
	document.getElementById('funktion-button-loeschen').disabled=val;

	if(val)
		FunktionDetailDisableFields(val);
}

// ****
// * De-/Aktiviert die DetailFelder
// ****
function FunktionDetailDisableFields(val)
{
	document.getElementById('funktion-menulist-fachbereich').disabled=val;
	document.getElementById('funktion-menulist-oe_kurzbz').disabled=val;
	document.getElementById('funktion-menulist-semester').disabled=val;
	document.getElementById('funktion-menulist-funktion').disabled=val;
	document.getElementById('funktion-button-speichern').disabled=val;
	document.getElementById('funktion-button-kopiespeichern').disabled=val;
	document.getElementById('funktion-box-datum_von').disabled=val;
	document.getElementById('funktion-box-datum_bis').disabled=val;
	document.getElementById('funktion-textbox-bezeichnung').disabled=val;
	document.getElementById('funktion-textbox-wochenstunden').disabled=val;
}

// ****
// * Setzt Defaultwerte fuer die Felder
// ****
function FunktionDetailResetFields()
{
	document.getElementById('funktion-menulist-fachbereich').value='';
	document.getElementById('funktion-menulist-oe_kurzbz').selectedIndex=0;
	document.getElementById('funktion-menulist-semester').value='';
	document.getElementById('funktion-menulist-funktion').value='ass';

	var Datum = new Date();
	var Jahr = Datum.getFullYear();
	var Tag = Datum.getDate();
	var Monat = Datum.getMonth()+1;

	document.getElementById('funktion-box-datum_von').value=Tag+'.'+Monat+'.'+Jahr;
	document.getElementById('funktion-box-datum_bis').value='';
	document.getElementById('funktion-textbox-bezeichnung').value='';
	document.getElementById('funktion-textbox-wochenstunden').value='';
	FunktionBezeichnungChanged=false;
}

// ****
// * Fachbereichs und Semester DropDown nur Anzeigen, wenn die entsprechenden Attribute
// * der Funktion auf true gesetzt sind
// ****
function FunktionToggleFachbereich()
{
	var menulist = document.getElementById('funktion-menulist-funktion');

	//ersten selektierten Eintrag holen
	var children = menulist.getElementsByAttribute('selected','true');
	children = children[0];

	//Attribute semester und fachbereich auslesen
	var semester = children.getAttribute('semester');
	var fachbereich = children.getAttribute('fachbereich');
	var bezeichnung = children.label;

	//wenn in der Bezeichung noch nichts drinnen steht, dann die Funktion in die Bezeichnung schreiben
	var tbbezeichnung = document.getElementById('funktion-textbox-bezeichnung')

	if(!FunktionBezeichnungChanged)
	{
		tbbezeichnung.value=bezeichnung;
	}

	//Felder sichtbar/unsichtbar setzen
	var semesterhidden=false;
	var fachbereichhidden=false;

	if(semester=='true')
		semesterhidden=false;
	else
		semesterhidden=true;

	if(fachbereich=='true')
		fachbereichhidden=false;
	else
		fachbereichhidden=true;

	document.getElementById('funktion-menulist-fachbereich').hidden=fachbereichhidden;
	document.getElementById('funktion-label-fachbereich').hidden=fachbereichhidden;

	document.getElementById('funktion-menulist-semester').hidden=semesterhidden;
	document.getElementById('funktion-label-semester').hidden=semesterhidden;

}

function FunktionBezeichnungChange()
{
	FunktionBezeichnungChanged=true;
}
