<?php
	require_once('../../config/vilesci.config.inc.php');
?>

var StundensaetzeTreeDatasource = ''; // Datasource des Stundensaetze Trees
var StundensatzSelectID = null;
var MitarbeiterUID = '';
var StundensatzChanged = false;
var DoubleRefresh = false

var StundensaetzeSinkObserver =
	{
		onBeginLoad : function(pSink) {},
		onInterrupt : function(pSink) {},
		onResume : function(pSink) {},
		onError : function(pSink, pStatus, pError) {},
		onEndLoad : function(pSink)
		{
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			document.getElementById('stundensaetze-tree').builder.rebuild();
		}
	};


var StundensaetzeTreeListener =
	{
		willRebuild : function(builder) {  },
		didRebuild : function(builder)
		{
			if(DoubleRefresh === true)
			{
				window.setTimeout('StundensatzTreeDatasourceRefresh()',10);
				DoubleRefresh = false;
			}
			else
			{
				//timeout nur bei Mozilla notwendig da sonst die rows
				//noch keine values haben. Ab Seamonkey funktionierts auch
				//ohne dem setTimeout
				window.setTimeout(StundensaetzeTreeSelectID, 10);
			}
			
		}
	};

function StundensatzTreeDatasourceRefresh(StundensatzSelect)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	StundensaetzeTreeDatasource.Refresh(false);
}

function StundensatzDisableFields(val)
{
	
	document.getElementById('mitarbeiter-stundensatz-button-neu').disabled = val;
	document.getElementById('mitarbeiter-stundensatz-button-loeschen').disabled = val;
}

function StundensatzNeu()
{
	StundensatzDetailResetFields();
	StundensatzDetailsDisableFields(false);
	StundensatzChanged = false;
}

function StundensatzDetailsDisableFields(val)
{
	document.getElementById('mitarbeiter-stundensatz-textbox-stundensatz').disabled = val;
	document.getElementById('mitarbeiter-stundensatz-textbox-unternehmen').disabled = val;
	document.getElementById('mitarbeiter-stundensatz-textbox-typ').disabled = val;
	document.getElementById('mitarbeiter-stundensatz-textbox-gueltig-von').disabled = val;
	document.getElementById('mitarbeiter-stundensatz-textbox-gueltig-bis').disabled = val;
	document.getElementById('mitarbeiter-stundensatz-button-speichern').disabled = val;
}


// ****
// * Setzt Defaultwerte fuer die Felder
// ****
function StundensatzDetailResetFields()
{
	document.getElementById('mitarbeiter-stundensatz-textbox-stundensatz').value = '';
	document.getElementById('mitarbeiter-stundensatz-textbox-gueltig-von').value = '';
	document.getElementById('mitarbeiter-stundensatz-textbox-gueltig-bis').value = '';
	document.getElementById('mitarbeiter-stundensatz-textbox-unternehmen').value = '';
	document.getElementById('mitarbeiter-stundensatz-textbox-typ').value = '';

	var Datum = new Date();
	var Jahr = Datum.getFullYear();
	var Tag = Datum.getDate();
	var Monat = Datum.getMonth()+1;

	document.getElementById('mitarbeiter-stundensatz-textbox-gueltig-von').value = Tag+'.'+Monat+'.'+Jahr;
}

// ****
// * Laedt die Studensätze
// ****
function loadStundensaetze(mitarbeiter_uid)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	MitarbeiterUID = mitarbeiter_uid;
	var url = "<?php echo APP_ROOT; ?>rdf/stundensatz.rdf.php?mitarbeiter_uid="+mitarbeiter_uid+'&'+gettimestamp();
	var tree = document.getElementById('stundensaetze-tree');
	
	//Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}
	tree.builder.rebuild();
	
	try
	{
		StundensaetzeTreeDatasource.removeXMLSinkObserver(StundensaetzeSinkObserver);
		tree.builder.removeListener(StundensaetzeTreeListener);
	}
	catch(e)
	{}

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	StundensaetzeTreeDatasource = rdfService.GetDataSource(url);

	StundensaetzeTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	StundensaetzeTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	tree.database.AddDataSource(StundensaetzeTreeDatasource);
	StundensaetzeTreeDatasource.addXMLSinkObserver(StundensaetzeSinkObserver);
	tree.builder.addListener(StundensaetzeTreeListener);

	StundensatzDisableFields(false);
}


// ****
// * Selectiert einen Stundensatz nachdem der Tree
// * rebuildet wurde.
// ****
function StundensaetzeTreeSelectID()
{
	var tree = document.getElementById('stundensaetze-tree');
	var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln

	//In der globalen Variable ist der zu selektierender Stundensatz gespeichert
	if(StundensatzSelectID != null)
	{
		for(var i = 0; i < items; i++)
		{
			//ID der row holen
			col = tree.columns ? tree.columns["stundensatz-treecol-stundensatz_id"] : "stundensatz-treecol-stundensatz_id";
			id = tree.view.getCellText(i,col);

			if(id === StundensatzSelectID)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				return true;
			}
		}
		StundensatzSelectID = null;
	}
}


function StundensatzDetailSpeichern()
{
	var stundensatz = document.getElementById('mitarbeiter-stundensatz-textbox-stundensatz').value;
	var datum_von = document.getElementById('mitarbeiter-stundensatz-textbox-gueltig-von').value;
	var datum_bis = document.getElementById('mitarbeiter-stundensatz-textbox-gueltig-bis').value;
	var unternehmen = document.getElementById('mitarbeiter-stundensatz-textbox-unternehmen').value;
	var typ = document.getElementById('mitarbeiter-stundensatz-textbox-typ').value;

	var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
	var req = new phpRequest(url,'','');

	if (StundensatzChanged !== false)
		req.add('stundensatz_id', StundensatzChanged);

	req.add('type', 'updateStundensatz');
	req.add('mitarbeiter_uid', MitarbeiterUID);
	req.add('stundensatz', stundensatz);
	req.add('datum_von', ConvertDateToISO(datum_von));
	req.add('datum_bis', ConvertDateToISO(datum_bis));
	req.add('unternehmen', unternehmen);
	req.add('typ', typ);

	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if (val.dbdml_errormsg === '')
			alert(response)
		else
			alert(val.dbdml_errormsg)
		return false;
	}
	else
	{
		if(val.dbdml_errormsg !== '' && val.dbdml_errormsg !== 'unknown')
			alert(val.dbdml_errormsg);

		if(document.getElementById('stundensaetze-tree').view.rowCount === 0)
		{
			DoubleRefresh = true;
		}
		StundensatzSelectID = val.dbdml_data;

		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		StundensaetzeTreeDatasource.Refresh(false);
		StundensatzChanged = false;
		StundensatzDetailResetFields();
		StundensatzDetailsDisableFields(true);
		return true;
	}
}

function StundensatzBearbeiten()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	var tree = document.getElementById('stundensaetze-tree');

	if (tree.currentIndex === -1)
		return;

	//Ausgewaehlten Stundensatz holen
	var col = tree.columns ? tree.columns["stundensatz-treecol-stundensatz_id"] : "stundensatz-treecol-stundensatz_id";
	var stundensatz_id = tree.view.getCellText(tree.currentIndex,col);

	//Daten holen
	var url = "<?php echo APP_ROOT; ?>rdf/stundensatz.rdf.php?stundensatz_id="+stundensatz_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);

	var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/stundensatz/"+stundensatz_id);

	var predicateNS = "http://www.technikum-wien.at/stundensatz/rdf";

	//Daten holen
	var stundensatz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#stundensatz"));
	var datum_von = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gueltig_von" ));
	var datum_bis = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gueltig_bis" ));
	var unternehmen = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#oe_kurzbz" ));
	var typ = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#stundensatztyp" ));
	
	StundensatzChanged = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#stundensatz_id" ));

	document.getElementById('mitarbeiter-stundensatz-textbox-stundensatz').value = stundensatz;
	document.getElementById('mitarbeiter-stundensatz-textbox-gueltig-von').value = datum_von;
	document.getElementById('mitarbeiter-stundensatz-textbox-gueltig-bis').value = datum_bis;
	document.getElementById('mitarbeiter-stundensatz-textbox-unternehmen').value = unternehmen;
	document.getElementById('mitarbeiter-stundensatz-textbox-typ').value = typ;

	StundensatzDetailsDisableFields(false);
}

function StundensatzDelete()
{

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('stundensaetze-tree');

	if (tree.currentIndex === -1)
	{
		alert('Bitte zuerst einen Stundensatz auswaehlen');
		return;
	}

	StundensatzDetailsDisableFields(false);
	
	//Ausgewaehlten Stundensatz holen
	var col = tree.columns ? tree.columns["stundensatz-treecol-stundensatz_id"] : "stundensatz-treecol-stundensatz_id";
	var stundensatz_id = tree.view.getCellText(tree.currentIndex,col);

	if (confirm('Diesen Stundensatz wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/mitarbeiter/mitarbeiterDBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'deleteStundensatz');
		req.add('stundensatz_id', stundensatz_id);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response)
		
		if (!val.dbdml_return)
		{
			if (val.dbdml_errormsg === '')
				alert(response)
			else
				alert(val.dbdml_errormsg)
			return false;
		}
		else
		{
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			StundensaetzeTreeDatasource.Refresh(false);
			StundensatzDetailResetFields();
			StundensatzDetailsDisableFields(true);
			return true;
		}
	}
}