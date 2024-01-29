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

$user = get_uid();
loadVariables($user);
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

// ****
// * Laedt die Trees
// ****
function loadKontakte(person_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	KontaktPerson_id=person_id;

	//Adressen laden
	url = "<?php echo APP_ROOT; ?>rdf/adresse.rdf.php?person_id="+person_id+"&"+gettimestamp();
	var treeAdressen=document.getElementById('kontakt-adressen-tree');

	try
	{
		AdressenTreeDatasource.removeXMLSinkObserver(KontaktAdressenTreeSinkObserver);
		treeAdressen.builder.removeListener(KontaktAdressenTreeListener);
	}
	catch(e)
	{}

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

	try
	{
		KontaktTreeDatasource.removeXMLSinkObserver(KontaktKontaktTreeSinkObserver);
		treeKontakt.builder.removeListener(KontaktKontaktTreeListener);
	}
	catch(e)
	{}

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
	if(treeBankverbindung != null)
    {
        try
        {
            BankverbindungTreeDatasource.removeXMLSinkObserver(KontaktBankverbindungTreeSinkObserver);
            treeBankverbindung.builder.removeListener(KontaktBankverbindungTreeListener);
        }
        catch(e)
        {}

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
    co_name = dialog.getElementById('adresse-textbox-co_name').value;
	firma_id = dialog.getElementById('adresse-menulist-firma').value;
	rechnungsadresse = dialog.getElementById('adresse-checkbox-rechnungsadresse').checked;
	anmerkung = dialog.getElementById('adresse-textbox-anmerkung').value;

	//Bei Mitarbeitern wird kein Studiengang mitgeschickt
	if(window.parent.document.getElementById('main-content-tabs').selectedItem==window.parent.document.getElementById('tab-mitarbeiter'))
		studiengang_kz='';
	else
		studiengang_kz = window.parent.document.getElementById('student-prestudent-menulist-studiengang_kz').value;

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
	req.add('studiengang_kz', studiengang_kz);
	req.add('rechnungsadresse', rechnungsadresse);
	req.add('anmerkung', anmerkung);
	req.add('co_name', co_name);

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

// ****
// * Neu Dialog oeffnen
// ****
function KontaktAdresseNeu()
{
	window.open("<?php echo APP_ROOT; ?>content/adressedialog.xul.php?person_id="+KontaktPerson_id,"","chrome, status=no, width=500, height=500, centerscreen, resizable");
}

// ****
// * Bearbeiten Dialog oeffnen
// ****
function KontaktAdresseBearbeiten()
{
	tree = document.getElementById('kontakt-adressen-tree');

	if (tree.currentIndex==-1) return;

	//Ausgewaehlte ID holen
    var col = tree.columns ? tree.columns["kontakt-adressen-treecol-adresse_id"] : "kontakt-adressen-treecol-adresse_id";
	var adresse_id=tree.view.getCellText(tree.currentIndex,col);

	window.open("<?php echo APP_ROOT; ?>content/adressedialog.xul.php?adresse_id="+adresse_id,"","chrome, status=no, width=500, height=500, centerscreen, resizable");
}

// ****
// * markierten Datensatz loeschen
// ****
function KontaktAdresseDelete()
{
	tree = document.getElementById('kontakt-adressen-tree');

	if (tree.currentIndex==-1) return;

	//Ausgewaehlte ID holen
    var col = tree.columns ? tree.columns["kontakt-adressen-treecol-adresse_id"] : "kontakt-adressen-treecol-adresse_id";
	var adresse_id=tree.view.getCellText(tree.currentIndex,col);

	//Bei Mitarbeitern wird kein Studiengang mitgeschickt
	if(window.parent.document.getElementById('main-content-tabs').selectedItem==window.parent.document.getElementById('tab-mitarbeiter'))
		studiengang_kz='';
	else
		studiengang_kz = window.parent.document.getElementById('student-prestudent-menulist-studiengang_kz').value;

	if(confirm('Diese Adresse wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'adressedelete');

		req.add('adresse_id', adresse_id);
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
			AdressenTreeDatasource.Refresh(false);
			return true;
		}
	}
}

// ****
// * Beim Sortieren des Trees wird der markierte Eintrag gespeichert und nach dem sortieren
// * wieder markiert.
// ****
function KontaktAdresseTreeSort()
{
	var i;
	var tree=document.getElementById('kontakt-adressen-tree');
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
		i = 0;
	col = tree.columns ? tree.columns["kontakt-adressen-treecol-adresse_id"] : "kontakt-adressen-treecol-adresse_id";
	KontaktAdresseSelectID = tree.view.getCellText(i,col);
	window.setTimeout("KontaktAdressenTreeSelectID()",10);
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

// ****
// * Speichert die Kontaktdaten
// ****
function KontaktKontaktSpeichern(dialog)
{
	neu = dialog.getElementById('kontakt-checkbox-neu').checked;
	person_id = dialog.getElementById('kontakt-textbox-person_id').value;
	kontakt_id = dialog.getElementById('kontakt-textbox-kontakt_id').value;
	anmerkung = dialog.getElementById('kontakt-textbox-anmerkung').value;
	kontakt = dialog.getElementById('kontakt-textbox-kontakt').value;
	zustellung = dialog.getElementById('kontakt-checkbox-zustellung').checked;
	typ = dialog.getElementById('kontakt-menulist-typ').value;
	standort_id = dialog.getElementById('kontakt-menulist-firma').value;

	//Bei Mitarbeitern wird kein Studiengang mitgeschickt
	if(window.parent.document.getElementById('main-content-tabs').selectedItem==window.parent.document.getElementById('tab-mitarbeiter'))
		studiengang_kz='';
	else
		studiengang_kz = window.parent.document.getElementById('student-prestudent-menulist-studiengang_kz').value;


	var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'kontaktsave');

	req.add('neu', neu);
	req.add('person_id', person_id);
	req.add('kontakt_id', kontakt_id);
	req.add('anmerkung', anmerkung);
	req.add('kontakt', kontakt);
	req.add('typ', typ);
	req.add('zustellung', zustellung);
	req.add('standort_id', standort_id);
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
		KontaktKontaktSelectID = val.dbdml_data;
		KontaktTreeDatasource.Refresh(false);
		return true;
	}
}

// ****
// * Neu Dialog anzeigen
// ****
function KontaktKontaktNeu()
{
	window.open("<?php echo APP_ROOT; ?>content/kontaktdialog.xul.php?person_id="+KontaktPerson_id,"","chrome, status=no, width=500, height=350, centerscreen, resizable");
}

// ****
// * Bearbeiten Dialog anzeigen
// ****
function KontaktKontaktBearbeiten()
{
	tree = document.getElementById('kontakt-kontakt-tree');

	if (tree.currentIndex==-1) return;

	//Ausgewaehlte ID holen
    var col = tree.columns ? tree.columns["kontakt-kontakt-treecol-kontakt_id"] : "kontakt-kontakt-treecol-kontakt_id";
	var kontakt_id=tree.view.getCellText(tree.currentIndex,col);

	window.open("<?php echo APP_ROOT; ?>content/kontaktdialog.xul.php?kontakt_id="+kontakt_id,"","chrome, status=no, width=500, height=350, centerscreen, resizable");
}

// ****
// * markierten Datensatz loeschen
// ****
function KontaktKontaktDelete()
{
	tree = document.getElementById('kontakt-kontakt-tree');

	if (tree.currentIndex==-1) return;

	//Ausgewaehlte ID holen
    var col = tree.columns ? tree.columns["kontakt-kontakt-treecol-kontakt_id"] : "kontakt-kontakt-treecol-kontakt_id";
	var kontakt_id=tree.view.getCellText(tree.currentIndex,col);

	//Bei Mitarbeitern wird kein Studiengang mitgeschickt
	if(window.parent.document.getElementById('main-content-tabs').selectedItem==window.parent.document.getElementById('tab-mitarbeiter'))
		studiengang_kz='';
	else
		studiengang_kz = window.parent.document.getElementById('student-prestudent-menulist-studiengang_kz').value;

	if(confirm('Diesen Kontakt wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'kontaktdelete');

		req.add('kontakt_id', kontakt_id);
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
			KontaktTreeDatasource.Refresh(false);
			return true;
		}
	}
}

// ****
// * Beim Sortieren des Trees wird der markierte Eintrag gespeichert und nach dem sortieren
// * wieder markiert.
// ****
function KontaktKontaktTreeSort()
{
	var i;
	var tree=document.getElementById('kontakt-kontakt-tree');
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
		i = 0;
	col = tree.columns ? tree.columns["kontakt-kontakt-treecol-kontakt_id"] : "kontakt-kontakt-treecol-kontakt_id";
	KontaktKontaktSelectID = tree.view.getCellText(i,col);
	window.setTimeout("KontaktKontaktTreeSelectID()",10);
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


// ****
// * Speichert die Bankdaten
// ****
function KontaktBankverbindungSpeichern(dialog)
{
	neu = dialog.getElementById('bankverbindung-checkbox-neu').checked;
	person_id = dialog.getElementById('bankverbindung-textbox-person_id').value;
	bankverbindung_id = dialog.getElementById('bankverbindung-textbox-bankverbindung_id').value;
	name = dialog.getElementById('bankverbindung-textbox-name').value;
	anschrift = dialog.getElementById('bankverbindung-textbox-anschrift').value;
	bic = dialog.getElementById('bankverbindung-textbox-bic').value;
	blz = dialog.getElementById('bankverbindung-textbox-blz').value;
	iban = dialog.getElementById('bankverbindung-textbox-iban').value;
	kontonr = dialog.getElementById('bankverbindung-textbox-kontonr').value;
	typ = dialog.getElementById('bankverbindung-menulist-typ').value;
	verrechnung = dialog.getElementById('bankverbindung-checkbox-verrechnung').checked;

	//Bei Mitarbeitern wird kein Studiengang mitgeschickt
	if(window.parent.document.getElementById('main-content-tabs').selectedItem==window.parent.document.getElementById('tab-mitarbeiter'))
		studiengang_kz='';
	else
		studiengang_kz = window.parent.document.getElementById('student-prestudent-menulist-studiengang_kz').value;

	var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'bankverbindungsave');

	req.add('neu', neu);
	req.add('person_id', person_id);
	req.add('bankverbindung_id', bankverbindung_id);
	req.add('name', name);
	req.add('anschrift', anschrift);
	req.add('bic', bic);
	req.add('blz', blz);
	req.add('iban', iban);
	req.add('kontonr', kontonr);
	req.add('typ', typ);
	req.add('verrechnung', verrechnung);
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
		KontaktBankverbindungSelectID = val.dbdml_data;
		BankverbindungTreeDatasource.Refresh(false);
		return true;
	}
}

// ****
// * Neu Dialog anzeigen
// ****
function KontaktBankverbindungNeu()
{
	window.open("<?php echo APP_ROOT; ?>content/bankverbindungdialog.xul.php?person_id="+KontaktPerson_id,"","chrome, status=no, width=500, height=350, centerscreen, resizable");
}

// ****
// * Bearbeiten Dialog anzeigen
// ****
function KontaktBankverbindungBearbeiten()
{
	tree = document.getElementById('kontakt-bankverbindung-tree');

	if (tree.currentIndex==-1) return;

	//Ausgewaehlte ID holen
    var col = tree.columns ? tree.columns["kontakt-bankverbindung-treecol-bankverbindung_id"] : "kontakt-bankverbindung-treecol-bankverbindung_id";
	var bankverbindung_id=tree.view.getCellText(tree.currentIndex,col);

	window.open("<?php echo APP_ROOT; ?>content/bankverbindungdialog.xul.php?bankverbindung_id="+bankverbindung_id,"","chrome, status=no, width=500, height=350, centerscreen, resizable");
}

// ****
// * markierten Datensatz loeschen
// ****
function KontaktBankverbindungDelete()
{
	tree = document.getElementById('kontakt-bankverbindung-tree');

	if (tree.currentIndex==-1) return;

	//Ausgewaehlte ID holen
    var col = tree.columns ? tree.columns["kontakt-bankverbindung-treecol-bankverbindung_id"] : "kontakt-bankverbindung-treecol-bankverbindung_id";
	var bankverbindung_id=tree.view.getCellText(tree.currentIndex,col);

	//Bei Mitarbeitern wird kein Studiengang mitgeschickt
	if(window.parent.document.getElementById('main-content-tabs').selectedItem==window.parent.document.getElementById('tab-mitarbeiter'))
		studiengang_kz='';
	else
		studiengang_kz = window.parent.document.getElementById('student-prestudent-menulist-studiengang_kz').value;

	if(confirm('Diese Bankverbindung wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'bankverbindungdelete');

		req.add('bankverbindung_id', bankverbindung_id);
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
			BankverbindungTreeDatasource.Refresh(false);
			return true;
		}
	}
}


// ****
// * Beim Sortieren des Trees wird der markierte Eintrag gespeichert und nach dem sortieren
// * wieder markiert.
// ****
function KontaktBankverbindungTreeSort()
{
	var i;
	var tree=document.getElementById('kontakt-bankverbindung-tree');
	if(tree.currentIndex>=0)
		i = tree.currentIndex;
	else
		i = 0;
	col = tree.columns ? tree.columns["kontakt-bankverbindung-treecol-bankverbindung_id"] : "kontakt-bankverbindung-treecol-bankverbindung_id";
	KontaktBankverbindungSelectID = tree.view.getCellText(i,col);
	window.setTimeout("KontaktBankverbindungTreeSelectID()",10);
}
