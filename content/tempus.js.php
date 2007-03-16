<?php
include('../vilesci/config.inc.php');
?>

function closeWindow()
{
  window.close();
}

function onLoad()
{
  //window.close();
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
   		document.getElementById("statusbarpanel-text").label = "Studiensemester erfolgreich geändert";
   		document.getElementById("statusbarpanel-semester").label = stsem;   		
   		//MitarbeiterDetailStudiensemester_id = dbdml_errormsg;
   	}
   	else
   	{
		alert("Fehler beim Speichern der Daten: "+dbdml_errormsg);
   	}
	return true;
}

function loadUndoList()
{
	menu = document.getElementById('menu-edit-undo');
	
	var url = '<?php echo APP_ROOT; ?>rdf/undo.rdf.php?'+gettimestamp();	
	menu.setAttribute('datasources', url);
	debug('load:'+url);

	return true;
}

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