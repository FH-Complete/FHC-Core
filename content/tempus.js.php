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
include('../vilesci/config.inc.php');
?>
var menuUndoDatasource=0;
var STPLlastDetailUrl='leer';

// ----------------------------------------------------------
// ------- CLASS Progressmeter ------------------------------
function Progressmeter(progress_id)
{
	var id=progress_id;
	var runningprogress=0;
    this.StopPM=StopPM;
    this.StartPM=StartPM;
    
    function StartPM()
    {
        // Progressmeter starten.
		document.getElementById(id).setAttribute('mode','undetermined');
		runningprogress++;
    }

    function StopPM()
    {
    	runningprogress--;
    	if(runningprogress<0)
    		runningprogress=0;
    	
        // Progressmeter stoppen wenn alle fertig sind
        if(runningprogress==0)
			document.getElementById(id).setAttribute('mode','determined');
    }
}
// ------ EndOf CLASS Progressmeter ------------------------------

var globalProgressmeter=new Progressmeter('statusbar-progressmeter');
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
  		//document.getElementById('main-content-tabs').selectedItem=document.getElementById('tab-studenten');

  		//Funktion ueberschreiben damit sie nicht nochmal aufgerufen wird
  		//wenn zb ein IFrame geladen wird
  		onLoad=function() {return false};
	}
	catch(e)
	{
		debug('catched'+e);
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

// ****
// * Wechselt von Studiensemester 'stsem' um 'wert' Studiensemester vor bzw zurueck
// ****
function studiensemesterChange(stsem, wert)
{
	if(typeof(wert)=='undefined')
	{
		wert=0;
		if(typeof(stsem)=='undefined')
		{
			var items = document.getElementsByTagName('menuitem');
			
			for(i in items)
			{
				if(items[i].id=='menu-properies-studiensemester-name' && items[i].getAttribute("checked")=='true')
					stsem = items[i].label;
			}
		}
	}
	else
		stsem = getStudiensemester();

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	// Request absetzen
	
	var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';

	var req = new phpRequest(url,'','');

	req.add('type', 'variablechange');
	req.add('stsem', stsem);
	req.add('wert', wert);
	
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
   		document.getElementById("statusbarpanel-semester").label = val.dbdml_data;
   		//Menue setzen
   		var items = document.getElementsByTagName('menuitem');
			
		for(i in items)
		{
			if(items[i].label==val.dbdml_data && items[i].id=='menu-properies-studiensemester-name')
			{
				items[i].setAttribute('checked',true);
				break;
			}
		}
   		//MitarbeiterDetailStudiensemester_id = dbdml_errormsg;
   		//Ansichten Refreshen
   		try
   		{
   			StudentTreeRefresh();
   		}
   		catch(e)
   		{}
   		
   		try
   		{
   			LvTreeRefresh();
   		}
   		catch(e)
   		{}
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

function getvariable(variable)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	// Request absetzen
	
	var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';

	var req = new phpRequest(url,'','');

	req.add('type', 'getvariable');
	req.add('name', variable);
	
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
		return val.dbdml_data;
	}
}

function getStudiensemesterVariable()
{
	if(stsem = getvariable('semester_aktuell'))
	{
		//Statusbar setzen
		document.getElementById("statusbarpanel-text").label = "Studiensemester erfolgreich geaendert";
		document.getElementById("statusbarpanel-semester").label = stsem;
		//Menue setzen
		var items = document.getElementsByTagName('menuitem');
		
		for(i in items)
		{
			if(items[i].label==stsem && items[i].id=='menu-properies-studiensemester-name')
			{
				items[i].setAttribute('checked',true);
				break;
			}
		}
		
		//Ansichten Refreshen
		try
		{
			StudentTreeRefresh();
		}
		catch(e)
		{}
		
		try
		{
			LvTreeRefresh();
		}
		catch(e)
		{}
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

// ****
// * Oeffnet die ToDoListe fuers Tempus
// ****
function HelpOpenToDo()
{
	window.open('ToDo_Tempus.html','ToDo');
}

// ****
// * Zeigt das Fenster zur Kollisionspruefung auf Studentenebene an
// ****
function KollisionStudentShow()
{
	window.open('<?php echo APP_ROOT;?>vilesci/lehre/stpl_benutzer_kollision_frameset.html', 'Kollision Student');
}