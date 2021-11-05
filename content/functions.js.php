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
/*
 * functions.js.php
 *
 * enthaelt globale JS Funktionen
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');

if(false): ?><script><?php endif; ?>

// ****
// * Liefert den Value aus einer XML-Datasource
// ****
function getTargetHelper(dsource,subj,predi)
{
	if (dsource.hasArcOut(subj, predi))
	{
		var target = dsource.GetTarget(subj, predi, true);
		if (target instanceof Components.interfaces.nsIRDFLiteral ||
			target instanceof Components.interfaces.nsIRDFInt)
		{
			return target.Value;
		}
	}
	return "";
}

// ****
// * Gibt eine Message auf die Javascript Console aus
// ****
function debug(msg)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	 var consoleService = Components.classes["@mozilla.org/consoleservice;1"]
                                 .getService(Components.interfaces.nsIConsoleService);
    consoleService.logStringMessage(msg);
}

// ****
// * Liefert einen Timestamp in Sekunden
// * zum anhaengen an eine URL um Caching zu verhindern
// ****
function gettimestamp()
{
	var now = new Date();
	var ret = now.getHours()*60*60*60;
	ret = ret + now.getMinutes()*60*60;
	ret = ret + now.getSeconds()*60;
	ret = ret + now.getMilliseconds();
	return ret;
}

// ****
// * Parst die Returnwerte der DBDML Scripte
// * @param response ... RDF Response des DBDML Scripts
// *
// * obj.dbdml_return ... Returnwert des Scripts
// * obj.dbdml_errormsg ... Errormessage
// * obj.dbdml_warning ... Warnhinweis
// * obj.dbdml_data ... zusaetzliche Daten vom Script. zB ID des angelegten Datansatzes
// ****
function ParseReturnValue(response)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	// Returnwerte aus RDF abfragen
	var dsource=parseRDFString(response, 'http://www.technikum-wien.at/dbdml');

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
	               getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/dbdml/0");

	var predicateNS = "http://www.technikum-wien.at/dbdml/rdf";

	retval = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
	if(retval=='true')
		this.dbdml_return = true;
	else
		this.dbdml_return = false;
	this.dbdml_errormsg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#errormsg" ));
	this.dbdml_data = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#data" ));
	this.dbdml_warning = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#warning" ));
	//debug('data:'+this.dbdml_data+' errormsg:'+this.dbdml_errormsg+' return:'+this.dbdml_return );
}

function parseRDFString(str, url)
{
	try {
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	} catch(e) {
		alert(e);
		return;
	}

  var memoryDS = Components.classes["@mozilla.org/rdf/datasource;1?name=in-memory-datasource"].createInstance(Components.interfaces.nsIRDFDataSource);

  var ios=Components.classes["@mozilla.org/network/io-service;1"].getService(Components.interfaces.nsIIOService);
  baseUri=ios.newURI(url,null,null);

  var parser=Components.classes["@mozilla.org/rdf/xml-parser;1"].createInstance(Components.interfaces.nsIRDFXMLParser);
  parser.parseString(memoryDS,baseUri,str);

  return memoryDS;
}


// ****
// * Zeigt einen Text in der Statusbar an
// ****
function SetStatusBarText(text)
{
	document.getElementById('statusbarpanel-text').label=text;
}


// ****
// * Prueft ein Datum auf Gueltigkeit
// * Erlaubte Formate: 1.1.2007, 31.12.2007
// ****
function CheckDatum(datum)
{
	var pattern = /^(0[1-9]|[1-9]|[12][0-9]|3[01])[.](0[1-9]|[1-9]|1[012])[.](19|20)\d\d$/

	if(pattern.exec(datum))
		return true;
	else
		return false;
}

// ****
// * Wandelt ein Datum ins ISO Format um
// * aus 31.2.2007 wird 2007-02-31
// ****
function ConvertDateToISO(datum)
{
	if(datum!='')
	{
		arr = datum.split('.');

		if(arr[0].length==1)
			arr[0]='0'+arr[0];

		if(arr[1].length==1)
			arr[1]='0'+arr[1];

		return arr[2]+'-'+arr[1]+'-'+arr[0];
	}
	else
		return '';
}

// ****
// * Wandelt ein Datum ins ISO Format um
// * aus 2007-02-31 wird 31.2.2007
// ****
function ConvertDateToGerman(datum)
{
	if(datum!='')
	{
		arr = datum.split('-');

		if(arr[1].length==1)
			arr[1]='0'+arr[1];

		if(arr[2].length==1)
			arr[2]='0'+arr[2];

		return arr[2]+'.'+arr[1]+'.'+arr[0];
	}
	else
		return '';
}

// ****
// * Liefert die Daten aus der Zwischenablage
// ****
function getDataFromClipboard()
{
	netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
	var clip = Components.classes["@mozilla.org/widget/clipboard;1"].getService(Components.interfaces.nsIClipboard);
	if (!clip)
		return false;
	var trans = Components.classes["@mozilla.org/widget/transferable;1"].createInstance(Components.interfaces.nsITransferable);
	if (!trans)
		return false;

	trans.addDataFlavor("text/unicode");

	clip.getData(trans,clip.kGlobalClipboard);
	var str = new Object();
	var strLength = new Object();
	trans.getTransferData("text/unicode",str,strLength);

	if (str) str = str.value.QueryInterface(Components.interfaces.nsISupportsString);
	if (str) pastetext = str.data.substring(0,strLength.value / 2);

	return pastetext;
}

// ****
// * Oeffnet ein neues Fenster welches dann die Datei 'action' mit dem POST Parameter 'data' aufruft
// ****
function OpenWindowPost(action, data)
{
	newwindow= window.open ("","FAS","width=350, height=350");
	newwindow.document.getElementsByTagName('body')[0].innerHTML = "<form id='postform-form' name='postfrm' action='' method='POST'><input type='hidden' id='postform-textbox-data' name='data' /></form>";
	newwindow.document.getElementById('postform-textbox-data').value=data;
	newwindow.document.getElementById('postform-form').action=action;
	newwindow.document.postfrm.submit();
}

// ****
// * Liefert das Aktuelle Studiensemester
// ****
function getStudiensemester()
{
	return document.getElementById('statusbarpanel-semester').label;
}

// ****
// * Markiert in einem Editierbaren DropDown Menu
// * einen Eintrag.
// * @param id = ID der Menulist
// *        data = value des Eintrages der markiert werden soll
// ****
function MenulistSelectItemOnValue(id, data)
{
	var children = document.getElementById(id).getElementsByAttribute('value',data);
	document.getElementById(id).selectedItem=children[0];
}

// ****
// * Liefert den value eines Editierbaren DropDowns
// * @param id = ID der Menulist
// ****
function MenulistGetSelectedValue(id)
{
	menulist = document.getElementById(id);

	//Es kann sein, dass im Eingabefeld nichts steht und
	//trotzdem ein Eintrag auf selected gesetzt ist.
	//In diesem Fall soll aber kein Wert zurueckgegeben werden
	if(menulist.value=='')
		return '';

	//Wenn es Selektierte Eintraege gibt, dann den value zurueckliefern
	var children = menulist.getElementsByAttribute('selected','true');
	if(children.length>0)
		return children[0].value;
	else
		return '';
}

// *****
// * Liefert den Text aus einem Tree
// * Parameter: tree ... Referenz auf den Tree
// *            col  ... id der Spalte
// *            idx  ... Zeilenindex im Tree
// *****
function getTreeCellText(tree, col, idx)
{
	col = tree.columns ? tree.columns[col] : col;
	return tree.view.getCellText(idx, col);
}

/**
 * Like getTreeCellText, but returns all the selected values (= cooler!)
 * @param: tree
 * @param: col
 */
function getMultipleTreeCellText(tree, col)
{
	var start = new Object();
	var end = new Object();
	var numRanges = tree.view.selection.getRangeCount();
	var returnArray = new Array();

	for (var t = 0; t < numRanges; t++)
	{
		tree.view.selection.getRangeAt(t, start, end);
		for (var v = start.value; v <= end.value; v++)
		{
			returnArray.push(getTreeCellText(tree, col, v));
		}
	}

	return returnArray;
}

/**
 * Create a window with a form inside and a hidden input for each element of the given array
 * @param: action
 * @param: dataName
 * @param: data
 */
function openWindowPostArray(action, dataName, data)
{
	var FORM_HEAD = "<form id='postform-form' name='postfrm' action='' method='POST'>\n";
	var FORM_FOOT = "</form>";

	var inputsHidden = "";

	for (var i = 0; i < data.length; i++)
	{
		inputsHidden += "	<input type='hidden' name='" + dataName + "[]' value='" + data[i] + "' />\n";
	}

	var newwindow = window.open("", "FAS");
	newwindow.document.getElementsByTagName('body')[0].innerHTML = FORM_HEAD + inputsHidden + FORM_FOOT;
	newwindow.document.getElementById('postform-form').action = action;

	newwindow.document.postfrm.submit();
}

// ****
// * Trim Member Function fuer Strings
// ****
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
}

// ****
// * StartsWidth Member Function fuer Strings
// ****
String.prototype.startsWith = function(str)
{return (this.match("^"+str)==str)}

// ****
// * Liefert den Usernamne des eingeloggten Users
// ****
function getUsername()
{
	return '<?php echo get_uid(); ?>';
}

// ****
// * Laedt den Wert einer Variable aus der DB
// ****
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

/**
 * Setzte eine Variable
 */
function setVariable(variable, wert)
{
	// Request absetzen

	var url = '<?php echo APP_ROOT ?>content/fasDBDML.php';

	var req = new phpRequest(url,'','');

	req.add('type', 'variablechange');
	req.add('name', variable);
	req.add('wert', wert);

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
		return true;
	}
}

// ****
// * Validierung Alias auf Sonderzeichen (analog zu checkWunschUid erlaubt)
// ****
function checkAlias(alias)
{

	if (/^[a-z0-9 . _ -]*$/i.test(alias) === false)
	{
		return false;
	}
	else
		return true;
}
