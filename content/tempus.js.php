<?php
include('../vilesci/config.inc.php');
?>
var menuUndoDatasource=0;

// ----------------------------------------------------------
// ------- CLASS Progressmeter ------------------------------
function Progressmeter()
{
	var id;
    this.StartPM=StartPM;
    //var m_data = 5;
    //var m_text = "Hello World";
    //this.SetText = SetText;
    //this.ShowData = DisplayData;
    //this.ShowText = DisplayText;

    Progressmeter.prototype.construct = function(id)
    {
    	this.id=id;
    };

    function StartPM()
    {
        // Progressmeter starten.
		document.getElementById(this.id).setAttribute('mode','undetermined');
    }

    //function SetData( myVal )
    //{
    //    m_data = myVal;
    //}
}
// ------ EndOf CLASS Progressmeter ------------------------------

//var globalProgressmeter=new Progressmeter('statusbar-progressmeter');
//globalProgressmeter.StartPM();

function closeWindow()
{
	//Warnung wenn Daten veraendert aber noch nicht gespeichert wurden
	if(MitarbeiterDetailValueChanged)
	{
		if(!confirm('Achtung! Mitarbeiterdaten wurden veraendert aber noch nicht gespeichert. Programm wirklich beenden? \n(Die geaenderten Daten gehen dabei verloren)'))
			return false;
	}

	MitarbeiterDetailValueChanged=false;
	
	window.close();
}

function onLoad()
{
	try
	{
  		initLektorTree();
  		//Studenten Tab beim starten markieren
  		document.getElementById('main-content-tabs').selectedItem=document.getElementById('tab-studenten');

  		//Funktion ueberschreiben damit sie nicht nochmal aufgerufen wird
  		//wenn zb ein IFrame geladen wird
  		onLoad=function() {return false};
	}
	catch(e)
	{
		onLoad=function() {return false};
	}
}

function loadRightFrame()
{

}

function loadURL(event)
{
        var contentFrame = document.getElementById('contentFrame');
        var url = event.target.getAttribute('value');

        if (url) contentFrame.setAttribute('src', url);
}
function stpltableChange(db_stpl_table)
{
	// Request absetzen
	
	var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';

	var req = new phpRequest(url,'','');

	req.add('type', 'variablechange');
	req.add('name', 'db_stpl_table');
	req.add('wert', db_stpl_table);
		
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
		//Statusbar setzen
   		document.getElementById("statusbarpanel-text").label = "Tabelle erfolgreich geaendert";
   		document.getElementById("statusbarpanel-db_table").label = db_stpl_table;
	}
   
	return true;
}

function studiensemesterChange()
{
	var items = document.getElementsByTagName('menuitem');
	var stsem='';
	for(i in items)
	{
		if(items[i].id=='menu-properies-studiensemester-name' && items[i].getAttribute("checked")=='true')
			stsem = items[i].label;
	}

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	// Request absetzen
	
	var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';

	var req = new phpRequest(url,'','');

	req.add('type', 'variablechange');
	req.add('stsem', stsem);
	
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
		//Statusbar setzen
   		document.getElementById("statusbarpanel-text").label = "Studiensemester erfolgreich geaendert";
   		document.getElementById("statusbarpanel-semester").label = stsem;
   		//MitarbeiterDetailStudiensemester_id = dbdml_errormsg;
   		//Ansichten Refreshen
   		try
   		{
   			StudentTreeRefresh();
   			LvTreeRefresh();
   		}
   		catch(e)
   		{
   			debug('catch: '+e);
   		}
	}
   
	return true;
}

function variableChange(variable, id)
{
	item = document.getElementById(id);
	
	if(item.getAttribute('checked')=='true')
		checked='true';
	else
		checked='false';
	
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	// Request absetzen
	
	var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';

	var req = new phpRequest(url,'','');

	req.add('type', 'variablechange');
	req.add('name', variable);
	req.add('wert', checked);
	
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
		//Statusbar setzen
   		document.getElementById("statusbarpanel-text").label = "Variable erfolgreich geaendert";
	}
}

// ****
// * Laedt das Undo Menue Neu
// ****
function loadUndoList()
{
	menu = document.getElementById('menu-edit-undo');

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	if(menuUndoDatasource==0)
	{
		//Wenn noch keine Datasource angegeben ist, dann wird eine neue hinzugefuegt
		var url = '<?php echo APP_ROOT; ?>rdf/undo.rdf.php?'+gettimestamp();

		//Alte DS entfernen
		var oldDatasources = menu.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			menu.database.RemoveDataSource(oldDatasources.getNext());
		}

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		menuUndoDatasource = rdfService.GetDataSource(url);
		menuUndoDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		menu.database.AddDataSource(menuUndoDatasource);
	}
	else
	{
		//Wenn die Datasource bereits geladen wurde dann nur neu laden
		menuUndoDatasource.Refresh(true); //blocking
		menu.builder.rebuild();
	}

	return true;
}

// ****
// * Fuehrt den Undo Befehl aus
// ****
function UnDo(log_id, bezeichnung)
{
	if(confirm('Wollen Sie folgenden Befehl wirklich Rueckgaengig machen: '+bezeichnung))
	{
		//Request absetzen
		var req = new phpRequest('tempusDBDML.php','','');

		req.add('type','undo');
		req.add('log_id',log_id);

		var response = req.executePOST();
		var val =  new ParseReturnValue(response)

		if (!val.dbdml_return)
		{
			alert(val.dbdml_errormsg)
		}
		else
		{
			LvTreeRefresh();
		}
	}
}