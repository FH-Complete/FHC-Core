<?php
include('../vilesci/config.inc.php');
?>
var menuUndoDatasource=0;

function closeWindow()
{
  window.close();
}

function onLoad()
{
	try
	{
  		initLektorTree();
  		//Studenten Tab beim starten markieren
  		document.getElementById('tabbox-main').selectedIndex="1";
  		
  		//Funktion ueberschreiben damit sie nicht nochmal aufgerufen wird
  		//wenn zb ein IFrame geladen wird
  		onLoad=function() {return false};
	}
	catch(e)
	{
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
//alert(db_stpl_table);
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
	var httpRequest = new XMLHttpRequest();
	var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";

	httpRequest.open("POST", url, false, '','');
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	var param = "type=variablechange";
	param = param + "&stsem="+stsem;

	//Parameter schicken
	httpRequest.send(param);

	// Bei status 4 ist sendung Ok
	switch(httpRequest.readyState)
	{
		case 1,2,3: alert('Bad Ready State: '+httpRequest.status);
			        return false;
		            break;

		case 4:		if(httpRequest.status !=200)
			        {
				        alert('The server respond with a bad status code: '+httpRequest.status);
				        return false;
			        }
			        else
			        {
				        var response = httpRequest.responseText;
			        }
		            break;
	}

	// Returnwerte aus RDF abfragen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/dbdml');

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/dbdml/0");

   	var predicateNS = "http://www.technikum-wien.at/dbdml/rdf";

   	var dbdml_return = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
   	var dbdml_errormsg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#errormsg" ));

   	if(dbdml_return=='true')
   	{
   		//Statusbar setzen
   		document.getElementById("statusbarpanel-text").label = "Studiensemester erfolgreich geaendert";
   		document.getElementById("statusbarpanel-semester").label = stsem;
   		//MitarbeiterDetailStudiensemester_id = dbdml_errormsg;
   		//Ansichten Refreshen
   		try
   		{
   			StudentTreeRefresh();
   			InteressentTreeRefresh();
   			LvTreeRefresh();
   		}
   		catch(e)
   		{
   			debug('catch: '+e);
   		}
   	}
   	else
   	{
		alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
   	}
	return true;
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