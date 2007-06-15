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

require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');

$conn = pg_pconnect(CONN_STRING);

$user = get_uid();
loadVariables($conn, $user);
?>
// ********** GLOBALE VARIABLEN ********** //
var AdressenTreeDatasource=''; // Datasource des Adressen Trees
var KontaktAdresseSelectID=null; // ID der Adresse die nach dem Rebuild markiert werden soll
var KontaktTreeDatasource=''; // Datasource des Kontakt Trees
var KontaktKontaktSelectID=null; // ID des Kontaktes der nach dem Rebuild markiert werden soll
var BankverbindungTreeDatasource=''; // Datasource des Bankverbindung Trees
var KontaktBankverbindungSelectID=null; // ID der Bankverbindung die nach dem Rebuild markiert werden soll
var KontaktPerson_id=null;
// ********** LISTENER UND OBSERVER ********** //

// ****
// * Observer fuer Adressen Tree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var KontaktAdressenTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('kontakt-adressen-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var KontaktAdressenTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(KontaktAdressenTreeSelectID,10);
  }
};

// ****
// * Observer fuer Kontakt Tree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var KontaktKontaktTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('kontakt-kontakt-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var KontaktKontaktTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(KontaktKontaktTreeSelectID,10);
  }
};

// ****
// * Observer fuer Bankverbindung Tree
// * startet Rebuild nachdem das Refresh
// * der Datasource fertig ist
// ****
var KontaktBankverbindungTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('kontakt-bankverbindung-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird der Eintrag wieder
// * markiert
// ****
var KontaktBankverbindungTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      window.setTimeout(KontaktBankverbindungTreeSelectID,10);
  }
};
// ********** FUNKTIONEN ********** //
function loadKontakte(person_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	KontaktPerson_id=person_id;
	
	//Adressen laden
	url = "<?php echo APP_ROOT; ?>rdf/adresse.rdf.php?person_id="+person_id+"&"+gettimestamp();	
	var treeAdressen=document.getElementById('kontakt-adressen-tree');
	
	//Alte DS entfernen
	var oldDatasources = treeAdressen.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		treeAdressen.database.RemoveDataSource(oldDatasources.getNext());
	}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	AdressenTreeDatasource = rdfService.GetDataSource(url);
	AdressenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	AdressenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	treeAdressen.database.AddDataSource(AdressenTreeDatasource);
	AdressenTreeDatasource.addXMLSinkObserver(KontaktAdressenTreeSinkObserver);
	treeAdressen.builder.addListener(KontaktAdressenTreeListener);
		
	//Kontakte laden
	url = "<?php echo APP_ROOT; ?>rdf/kontakt.rdf.php?person_id="+person_id+"&"+gettimestamp();	
	var treeKontakt=document.getElementById('kontakt-kontakt-tree');
	
	//Alte DS entfernen
	var oldDatasources = treeKontakt.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		treeKontakt.database.RemoveDataSource(oldDatasources.getNext());
	}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	KontaktTreeDatasource = rdfService.GetDataSource(url);
	KontaktTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	KontaktTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	treeKontakt.database.AddDataSource(KontaktTreeDatasource);
	KontaktTreeDatasource.addXMLSinkObserver(KontaktKontaktTreeSinkObserver);
	treeKontakt.builder.addListener(KontaktKontaktTreeListener);
	
	//Bankverbindungen laden
	url = "<?php echo APP_ROOT; ?>rdf/bankverbindung.rdf.php?person_id="+person_id+"&"+gettimestamp();	
	var treeBankverbindung=document.getElementById('kontakt-bankverbindung-tree');
	
	//Alte DS entfernen
	var oldDatasources = treeBankverbindung.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		treeBankverbindung.database.RemoveDataSource(oldDatasources.getNext());
	}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	BankverbindungTreeDatasource = rdfService.GetDataSource(url);
	BankverbindungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	BankverbindungTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	treeBankverbindung.database.AddDataSource(BankverbindungTreeDatasource);
	BankverbindungTreeDatasource.addXMLSinkObserver(KontaktBankverbindungTreeSinkObserver);
	treeBankverbindung.builder.addListener(KontaktBankverbindungTreeListener);
}

// ********** ADRESSEN ********** //

// ****
// * Selectiert eine Adresse nachdem der Tree
// * rebuildet wurde.
// ****
function KontaktAdressenTreeSelectID()
{
	var tree=document.getElementById('kontakt-adressen-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende Adresse gespeichert
	if(KontaktAdresseSelectID!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["kontakt-adressen-treecol-adresse_id"] : "kontakt_adressen-treecol-adresse_id";
			id=tree.view.getCellText(i,col);
						
			if(id == KontaktAdresseSelectID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
	   	}
	   	KontaktAdresseSelectID=null;
	}	
}

// ****
// * Speichert die Adressdaten
// ****
function KontaktAdresseSpeichern(dialog)
{
	neu = dialog.getElementById('adresse-checkbox-neu').checked;
	person_id = dialog.getElementById('adresse-textbox-person_id').value;
	adresse_id = dialog.getElementById('adresse-textbox-adresse_id').value;
	name = dialog.getElementById('adresse-textbox-name').value;
	strasse = dialog.getElementById('adresse-textbox-strasse').value;
	plz = dialog.getElementById('adresse-textbox-plz').value;
	ort = dialog.getElementById('adresse-textbox-ort').value;
	gemeinde = dialog.getElementById('adresse-textbox-gemeinde').value;
	nation = dialog.getElementById('adresse-menulist-nation').value;
	typ = dialog.getElementById('adresse-menulist-typ').value;
	heimatadresse = dialog.getElementById('adresse-checkbox-heimatadresse').checked;
	zustelladresse = dialog.getElementById('adresse-checkbox-zustelladresse').checked;
	firma_id = dialog.getElementById('adresse-menulist-firma').value;
		
	var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
	var req = new phpRequest(url,'','');
	
	req.add('type', 'adressesave');

	req.add('neu', neu);
	req.add('person_id', person_id);
	req.add('adresse_id', adresse_id);
	req.add('name', name);
	req.add('strasse', strasse);
	req.add('plz', plz);
	req.add('ort', ort);
	req.add('gemeinde', gemeinde);
	req.add('nation', nation);
	req.add('typ', typ);
	req.add('heimatadresse', heimatadresse);
	req.add('zustelladresse', zustelladresse);
	req.add('firma_id', firma_id);

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
		KontaktAdresseSelectID = val.dbdml_data;
		AdressenTreeDatasource.Refresh(false);
		return true;
	}
}

function KontaktAdresseNeu()
{
	window.open("<?php echo APP_ROOT; ?>content/adressedialog.xul.php?person_id="+KontaktPerson_id,"","chrome, status=no, width=500, height=350, centerscreen, resizable");
}

function KontaktAdresseBearbeiten()
{
	tree = document.getElementById('kontakt-adressen-tree');
	
	if (tree.currentIndex==-1) return;
	
	//Ausgewaehlte ID holen
    var col = tree.columns ? tree.columns["kontakt-adressen-treecol-adresse_id"] : "kontakt-adressen-treecol-adresse_id";
	var adresse_id=tree.view.getCellText(tree.currentIndex,col);
	
	window.open("<?php echo APP_ROOT; ?>content/adressedialog.xul.php?adresse_id="+adresse_id,"","chrome, status=no, width=500, height=350, centerscreen, resizable");
}

function KontaktAdresseDelete()
{
	tree = document.getElementById('kontakt-adressen-tree');
	
	if (tree.currentIndex==-1) return;
	
	//Ausgewaehlte ID holen
    var col = tree.columns ? tree.columns["kontakt-adressen-treecol-adresse_id"] : "kontakt-adressen-treecol-adresse_id";
	var adresse_id=tree.view.getCellText(tree.currentIndex,col);
	
	if(confirm('Diese Adresse wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
		var req = new phpRequest(url,'','');
		
		req.add('type', 'adressedelete');
		
		req.add('adresse_id', adresse_id);
	
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
			AdressenTreeDatasource.Refresh(false);
			return true;
		}
	}
}

// ********** KONTAKTE ********** //

// ****
// * Selectiert einen Kontakt nachdem der Tree
// * rebuildet wurde.
// ****
function KontaktKontaktTreeSelectID()
{
	var tree=document.getElementById('kontakt-kontakt-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende Bankverbindung gespeichert
	if(KontaktKontaktSelectID!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["kontakt-kontakt-treecol-kontakt_id"] : "kontakt_kontakt-treecol-kontakt_id";
			id=tree.view.getCellText(i,col);
						
			if(id == KontaktKontaktSelectID)
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

// ********** BANKVERBINDUNG ********** //

// ****
// * Selectiert eine Bankverbindung nachdem der Tree
// * rebuildet wurde.
// ****
function KontaktBankverbindungTreeSelectID()
{
	var tree=document.getElementById('kontakt-bankverbindung-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist die zu selektierende Bankverbindung gespeichert
	if(KontaktBankverbindungSelectID!=null)
	{		
	   	for(var i=0;i<items;i++)
	   	{
	   		//ID der row holen
			col = tree.columns ? tree.columns["kontakt-bankverbindung-treecol-bankverbindung_id"] : "kontakt_bankverbindung-treecol-bankverbindung_id";
			id=tree.view.getCellText(i,col);
						
			if(id == KontaktBankverbindungSelectID)
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