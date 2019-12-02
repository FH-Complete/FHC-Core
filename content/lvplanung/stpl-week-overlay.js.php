<?php
/* Copyright (C) 2008 Technikum-Wien
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
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */
require_once('../../config/vilesci.config.inc.php');
?>
// Stunde die zuletzt markiert wurde
var TimeTableWeekLastMarkedItem='';

// LVA-Panel aktualisieren
function onLVARefresh()
{
	// LVAs
	var vboxLehrveranstalungPlanung=document.getElementById('vboxLehrveranstalungPlanung');
	var datasources=vboxLehrveranstalungPlanung.getAttribute('datasources');
	//alert (datasources);
	vboxLehrveranstalungPlanung.setAttribute('datasources',datasources+"&bla=");
}

// LVA-Panel filtern
function onLVAFilter()
{
	var filter=document.getElementById('tempus-lva-filter').value;
	var vorher='';
	var nachher='';

	// LVAs
	var vboxLehrveranstalungPlanung=document.getElementById('vboxLehrveranstalungPlanung');
	var datasources=vboxLehrveranstalungPlanung.getAttribute('datasources');

	var orig=datasources.substring(0);
	var idx = datasources.indexOf("&filter=")
	if(idx!=-1)
	{
		idx2=datasources.indexOf("&",idx+9);
		vorher=datasources.slice(0,idx);
		if(idx2!=-1)
			nachher=datasources.slice(idx2);
		datasources=vorher+nachher;
	}

	datasources=datasources+"&filter="+encodeURIComponent(filter);

	//alert('Orig:'+orig+' Source: '+datasources+' Vorher:'+vorher+' Nachher:'+nachher);
	vboxLehrveranstalungPlanung.setAttribute('datasources',datasources);
}


// LVA-Panel aktualisieren
function onLektorRefresh()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var treeLektorenTree=document.getElementById('tree-lektor');
	// Input-Feld leeren
	document.getElementById('tempus-lektor-filter').value = '';
	//var datasources=vboxLehrveranstalungPlanung.getAttribute('datasources');
	var url = '<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php?user=user&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	var datasource = rdfService.GetDataSource(url);
	var oldDatasources = treeLektorenTree.database.GetDataSources();

	datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);

	treeLektorenTree.database.RemoveDataSource(oldDatasources.getNext());
	treeLektorenTree.database.AddDataSource(datasource);
	treeLektorenTree.builder.rebuild();

}

// Lektorenliste filtern
function onLektorFilter()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var filter=document.getElementById('tempus-lektor-filter').value;

	var treeLektorenTree=document.getElementById('tree-lektor');

	if(filter.length>2)
	{
		var url = '<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php?filter='+encodeURIComponent(filter)+'&'+gettimestamp();
		var oldDatasources = treeLektorenTree.database.GetDataSources();

		//Refresh damit die entfernten DS auch wirklich entfernt werden
		treeLektorenTree.builder.rebuild();

		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		if(typeof(filter)=='undefined')
			var datasource = rdfService.GetDataSource(url);
		else
			var datasource = rdfService.GetDataSourceBlocking(url);
		datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		treeLektorenTree.database.RemoveDataSource(oldDatasources.getNext());
		treeLektorenTree.database.AddDataSource(datasource);
		if(typeof(filter)!='undefined')
			treeLektorenTree.builder.rebuild();
	}
}

// Liefert die Sortierreihenfolge der Lehrstunden
function LehrstundeGetSortOrder()
{
	var toolbar = document.getElementById('toolbarTimeTableSort');
	var tbbuttons = toolbar.getElementsByTagName('toolbarbutton');

	for each(var button in tbbuttons)
	{
		if(button.getAttribute('checked')=='true')
		{
			return button.getAttribute('value');
		}
	}
	return 'stundenDESC';
}

// Setzt den Vertragsfilter zurueck
function LVAFilterReset()
{
	//Filtermarkierung von allen entfernen
	var toolbar = document.getElementById('toolbarTimeTableFilterVertrag');
	var tbbuttons = toolbar.getElementsByTagName('toolbarbutton');

	for each(var button in tbbuttons)
	{
		if(button.id)
			button.setAttribute('checked','false');
	}
}

// LVA-Panel auf Vertragsstatus filtern
function onLVAFilterVertrag(item)
{
	var vorher='';
	var nachher='';
	var vertragsstatus=item.getAttribute('value');

	LVAFilterReset();
	//Element als gedrueckt markieren
	item.setAttribute('checked','true');

	// LVAs
	var vboxLehrveranstalungPlanung=document.getElementById('vboxLehrveranstalungPlanung');
	var datasources=vboxLehrveranstalungPlanung.getAttribute('datasources');

	var orig=datasources.substring(0);
	var idx = datasources.indexOf("&vertrag=")
	if(idx!=-1)
	{
		idx2=datasources.indexOf("&",idx+10);
		vorher=datasources.slice(0,idx);
		if(idx2!=-1)
			nachher=datasources.slice(idx2);
		datasources=vorher+nachher;
	}

	datasources=datasources+"&vertrag="+encodeURIComponent(vertragsstatus);
	vboxLehrveranstalungPlanung.setAttribute('datasources',datasources);
}

// LVA-Panel filtern
function onLVASort(item)
{
	var vorher='';
	var nachher='';
	var order=item.getAttribute('value');

	//Sortiermarkierung von allen entfernen
	var toolbar = document.getElementById('toolbarTimeTableSort');
	var tbbuttons = toolbar.getElementsByTagName('toolbarbutton');

	for each(var button in tbbuttons)
	{
		if(button.id)
			button.setAttribute('checked','false');
	}
	//Element als gedrueckt markieren
	item.setAttribute('checked','true');

	// LVAs
	var vboxLehrveranstalungPlanung=document.getElementById('vboxLehrveranstalungPlanung');
	var datasources=vboxLehrveranstalungPlanung.getAttribute('datasources');

	var orig=datasources.substring(0);
	var idx = datasources.indexOf("&order=")
	if(idx!=-1)
	{
		idx2=datasources.indexOf("&",idx+8);
		vorher=datasources.slice(0,idx);
		if(idx2!=-1)
			nachher=datasources.slice(idx2);
		datasources=vorher+nachher;
	}

	datasources=datasources+"&order="+encodeURIComponent(order);

	//alert('Orig:'+orig+' Source: '+datasources+' Vorher:'+vorher+' Nachher:'+nachher);
	vboxLehrveranstalungPlanung.setAttribute('datasources',datasources);
}

function onJumpNow()
{
	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));
	var type=daten.getAttribute("stpl_type");
	var stg_kz=daten.getAttribute("stg_kz");
	var sem=daten.getAttribute("sem");
	var ver=daten.getAttribute("ver");
	var grp=daten.getAttribute("grp");
	var gruppe=daten.getAttribute("gruppe");
	var ort=daten.getAttribute("ort");
	var fachbereich_kurzbz=daten.getAttribute("fachbereich_kurzbz");
	var pers_uid=daten.getAttribute("pers_uid");

	var d = new Date();
    var datum=0;
    //Sekunden seit 1.1.1970
    datum = d.getTime()/1000;
	//alert(datum);
	var attributes="?type="+type+"&datum="+datum+"&ort="+encodeURIComponent(ort)+"&pers_uid="+encodeURIComponent(pers_uid)+"&stg_kz="+encodeURIComponent(stg_kz)+"&sem="+encodeURIComponent(sem)+"&ver="+encodeURIComponent(ver)+"&grp="+encodeURIComponent(grp)+"&gruppe="+encodeURIComponent(gruppe)+"&fachbereich_kurzbz="+encodeURIComponent(fachbereich_kurzbz);
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
	url+=attributes;
	if (url)
		contentFrame.setAttribute('src', url);
}

function onJumpDate(wochen)
{
	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));
	var type=daten.getAttribute("stpl_type");
	var stg_kz=daten.getAttribute("stg_kz");
	var sem=daten.getAttribute("sem");
	var ver=daten.getAttribute("ver");
	var grp=daten.getAttribute("grp");
	var gruppe=daten.getAttribute("gruppe");
	var ort=daten.getAttribute("ort");
	var pers_uid=daten.getAttribute("pers_uid");
	var fachbereich_kurzbz=daten.getAttribute("fachbereich_kurzbz");

	// neues Datum berechnen. Eine Woche sind 604800 Sekunden.
	datum+=(604800*wochen)+1;

	var attributes="?type="+type+"&datum="+datum+"&ort="+encodeURIComponent(ort)+"&pers_uid="+encodeURIComponent(pers_uid)+"&stg_kz="+encodeURIComponent(stg_kz)+"&sem="+encodeURIComponent(sem)+"&ver="+encodeURIComponent(ver)+"&grp="+encodeURIComponent(grp)+"&gruppe="+encodeURIComponent(gruppe)+"&fachbereich_kurzbz="+encodeURIComponent(fachbereich_kurzbz);
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
	url+=attributes;
	if (url)
		contentFrame.setAttribute('src', url);
}

function onJumpDateRel(evt)
{
	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));
	var type=daten.getAttribute("stpl_type");
	var stg_kz=daten.getAttribute("stg_kz");
	var sem=daten.getAttribute("sem");
	var ver=daten.getAttribute("ver");
	var grp=daten.getAttribute("grp");
	var gruppe=daten.getAttribute("gruppe");
	var ort=daten.getAttribute("ort");
	var pers_uid=daten.getAttribute("pers_uid");
	var fachbereich_kurzbz=daten.getAttribute("fachbereich_kurzbz");
	var kw=daten.getAttribute("kw");
	var KWZiel=evt.target.getAttribute("kw");
	var wochen=KWZiel-kw;

	// neues Datum berechnen. Eine Woche sind 604800 Sekunden.
	datum+=(604800*wochen)+1;

	var attributes="?type="+type+"&datum="+datum+"&ort="+encodeURIComponent(ort)+"&pers_uid="+encodeURIComponent(pers_uid)+"&stg_kz="+encodeURIComponent(stg_kz)+"&sem="+encodeURIComponent(sem)+"&ver="+encodeURIComponent(ver)+"&grp="+encodeURIComponent(grp)+"&gruppe="+encodeURIComponent(gruppe)+"&fachbereich_kurzbz="+encodeURIComponent(fachbereich_kurzbz);
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
	url+=attributes;
	if (url)
		contentFrame.setAttribute('src', url);
}

function onLVAdoStpl(evt)
{
	saveScrollPositionTimeTableWeek();
	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));
	var type=daten.getAttribute("stpl_type");
	var stg_kz=daten.getAttribute("stg_kz");
	var sem=daten.getAttribute("sem");
	var ver=daten.getAttribute("ver");
	var grp=daten.getAttribute("grp");
	var gruppe=daten.getAttribute("gruppe");
	var ort=daten.getAttribute("ort");
	var pers_uid=daten.getAttribute("pers_uid");
	var aktion=evt.target.getAttribute("aktion");
	var doIt=true;
	var oneDate=new Date();
	if (aktion=='lva_stpl_del_single')
		doIt=confirm('Es werden alle Lehrveranstaltungen aus dem LV-Plan dieser Woche geloescht!\nSind Sie sicher?')
	else
		if (aktion=='lva_stpl_del_multi')
			doIt=confirm('Es werden alle Lehrveranstaltungen aus dem LV-Plan ab dieser Woche geloescht!\nSind Sie sicher?')
		else
			aktion+="_search";
	var idList=evt.target.getAttribute("idList");

	var attributes="?type="+type+"&datum="+datum+"&ort="+encodeURIComponent(ort)+"&pers_uid="+pers_uid+"&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe;
	attributes+=idList+"&aktion="+aktion+"&time="+oneDate.getTime();
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
	url+=attributes+"&bla=";
	//dump(url);
	if (url && doIt)
		contentFrame.setAttribute('src', url);
}

function onStplSearchRoom(event)
{
	//alert ("clickCount="+event.clickCount+" button="+event.button);
	if (event.button == 1)
	{
		StplSearchRoom(event.target);
	}
}

function StplSearchRoom(target)
{
	saveScrollPositionTimeTableWeek();
	if(typeof(target)==='undefined')
		target = document.popupNode;

	var contentFrame=document.getElementById('iframeTimeTableWeek');
	var daten=document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));
	var type=daten.getAttribute("stpl_type");
	var	stg_kz=daten.getAttribute("stg_kz");
	var sem=daten.getAttribute("sem");
	var ver=daten.getAttribute("ver");
	var grp=daten.getAttribute("grp");
	var gruppe=daten.getAttribute("gruppe");
	var ort=daten.getAttribute("ort");
	var pers_uid=daten.getAttribute("pers_uid");
	var aktion=target.getAttribute("aktion");
	aktion+="_single_search";
	var idList=target.getAttribute("idList");

	var attributes="\n?type="+type+"&datum="+datum+"&ort="+ort+"&pers_uid="+pers_uid+"\n&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"\n&gruppe="+gruppe;
	attributes+=idList+"&aktion="+aktion;
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
	url+=attributes;
	//alert(url);
	if (url)
		location.href=url;
}

// ****
// * Markiert einen LV-Plan Eintrag
// * item ... Element das markiert werden soll
// * Wenn kein Item uebergeben wird, dann werden alle markierungen geloescht
// ****
function TimeTableWeekMarkiere(item)
{
	if(!item)
	{
		items = document.getElementsByTagName('button');

		for each(var button in items)
		{
			if(button.id && button.id.startsWith('buttonSTPL'))
			{
				button.setAttribute('marked','false');
				button.style.color='black';
				//button.style.fontStyle='normal';
				//button.style.fontWeight='normal';
				button.style.border = "1px solid transparent";

				button.style.MozBorderTopColors='transparent';
				button.style.MozBorderLeftColors='transparent';
				button.style.MozBorderBottomColors='transparent';
				button.style.MozBorderRightColors='transparent';
			}
		}
		TimeTableWeekLastMarkedItem='';
	}
	else
	{
		item.setAttribute('marked','true');

		item.style.color='darkred';
		//item.style.fontStyle='italic';
		//item.style.fontWeight='bold';

		item.style.border = "1px solid darkred";

		item.style.MozBorderTopColors='darkred';
		item.style.MozBorderLeftColors='darkred';
		item.style.MozBorderBottomColors='darkred';
		item.style.MozBorderRightColors='darkred';

		TimeTableWeekLastMarkedItem=item;
	}
}

// ****
// * Liefert die IdList der Markierten Stunden
// ****
function TimeTableWeekGetMarkedIdList()
{
	var items = document.getElementsByTagName('button');
	var myidlist = '';
	var i=0;
	for each(var button in items)
	{
		if(button.id && button.id.startsWith('buttonSTPL'))
		{
			marked = button.getAttribute('marked');
			if(marked=='true')
			{
				myidlist = myidlist+button.getAttribute('idList').replace(/&/g,"&x"+i);
				i++;
			}
		}
	}
	return myidlist;
}

// ****
// * Liefert die StundenplanIDs der markierten Stunden als array
// ****
function TimeTableWeekGetMarkedIdArray()
{
	var items = document.getElementsByTagName('button');
	var ids = Array();
	for each(var button in items)
	{
		if(button.id && button.id.startsWith('buttonSTPL'))
		{
			marked = button.getAttribute('marked');
			if(marked=='true')
			{
				idlist = button.getAttribute('idList');

				idarr = idlist.split(/&stundenplan_id[0-9]=/);

				for(i in idarr)
				{
					if(idarr[i]=='')
						continue;
					ids.push(idarr[i]);
				}
			}
		}
	}
	return ids;
}

// ****
// * Liefert die IdList der Markierten Stunden
// ****
function TimeTableWeekGetMarkedHoursArray()
{
	var items = document.getElementsByTagName('button');
	var stunden = Array();
	var i=0;
	for each(var button in items)
	{
		if(button.id && button.id.startsWith('buttonSTPL'))
		{
			marked = button.getAttribute('marked');
			if(marked=='true')
			{
				stunden.push(button.getAttribute('stunde'));
				i++;
			}
		}
	}
	return stunden;
}

// ****
// * Klick auf eine Stunde im LV-Plan
// ****
function TimeTableWeekClick(event)
{

	if(event.ctrlKey)
	{
		//Wenn mit Strg auf die Stunde geklickt wird, dann wird diese Stunde zur Markierung hinzugefuegt
		TimeTableWeekMarkiere(event.target);
	}
	else if(event.shiftKey)
	{
		//Wenn mit Shift auf eine Stunde geklickt wird, dann werden alle Stunden markiert,
		//die zwischen der zuletzt markierten und dieser Stunde liegen
		start = parseInt(TimeTableWeekLastMarkedItem.id.substring('buttonSTPL'.length));
		ende = parseInt(event.target.id.substring('buttonSTPL'.length));
		if(start>ende)
		{
			hlp = ende;
			ende = start;
			start = hlp;
		}

		for(var i=start;i<=ende;i++)
		{
			item = document.getElementById('buttonSTPL'+i);
			TimeTableWeekMarkiere(item);
		}
	}
	else
	{

		//alle markierungen entfernen
		TimeTableWeekMarkiere();

		//aktuellen Eintrag markieren
		TimeTableWeekMarkiere(event.target);

		//Details anzeigen
		onStplDetail(event);
	}
}

// ****
// * Doppelklick auf eine Stunde im LV-Plan
// * Markiert alle Stunden mit der selben UNR an diesem Tag
// ****
function TimeTableWeekDblClick(event)
{
	var items = document.getElementsByTagName('button');
	var unr = event.target.getAttribute('unr');
	var wochentag = event.target.getAttribute('wochentag');

	for each(var button in items)
	{
		if(button.id && button.id.startsWith('buttonSTPL'))
		{
			buttonunr=button.getAttribute('unr');
			buttonwochentag=button.getAttribute('wochentag');

			if(buttonunr==unr && buttonwochentag==wochentag)
			{
				TimeTableWeekMarkiere(button);
			}
		}
	}
}

// ****
// * Laedt die Details zu einer Stunde
// ****
function onStplDetail(event)
{
	var idList=event.target.getAttribute("idList");
	var type=event.target.getAttribute("stpltype");
	var stg_kz=event.target.getAttribute("stg_kz");
	var sem=event.target.getAttribute("sem");
	var ver=event.target.getAttribute("ver");
	var grp=event.target.getAttribute("grp");
	var gruppe=event.target.getAttribute("gruppe");
	var datum=event.target.getAttribute("datum");
	var stunde=event.target.getAttribute("stunde");
	var pers_uid=event.target.getAttribute("pers_uid");
	var ort_kurzbz=event.target.getAttribute("ort_kurzbz");
	var fachbereich_kurzbz=event.target.getAttribute("fachbereich_kurzbz");

	var attributes="?type="+type+"&datum="+datum+"&stunde="+stunde+"&ort_kurzbz="+encodeURIComponent(ort_kurzbz)+"&pers_uid="+pers_uid+"&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe+"&ort_kurzbz="+encodeURIComponent(ort_kurzbz)+"&fachbereich_kurzbz="+encodeURIComponent(fachbereich_kurzbz);
	//alert(attributes);
	//debug('stpl-week-overlay onStplDetail Attribute:'+attributes);
	attributes+=idList;
	var url = "<?php echo APP_ROOT; ?>rdf/lehrstunde.rdf.php";
	url+=attributes;
	//alert('first:'+window.parent.STPLlastDetailUrl);
	window.parent.STPLlastDetailUrl = url;
	//alert(url+' - '+window.parent.STPLlastDetailUrl);
	var treeStplDetails=parent.document.getElementById('treeStplDetails');
	treeStplDetails.setAttribute('datasources',url+"&ts="+gettimestamp());
}


// ****
// * oeffnet einen Dialog zum Bearbeiten der StundenplanDetails
// ****
function STPLDetailEdit()
{
	tree = document.getElementById('treeStplDetails');
	if(tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Eintrag markieren!');
		return false;
	}
	var col = tree.columns ? tree.columns["stundenplan_id"] : "stundenplan_id";
	var id = tree.view.getCellText(tree.currentIndex,col);
	var col = tree.columns ? tree.columns["stpl-details-overlay-lehrstunde-reservierung"] : "stpl-details-overlay-lehrstunde-reservierung";
	var reservierung = tree.view.getCellText(tree.currentIndex,col);
	if(reservierung=='true')
		alert('Reservierungen koennen hier nicht editiert werden');
	else
		window.open('<?php echo APP_ROOT; ?>content/lvplanung/stpl-details-dialog.xul.php?id='+id,'Details', 'height=500,width=600,left=100,top=100,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

// ****
// * Speichert die Stundenplan-Detail-Daten
// ****
function STPLDetailSave(dialog)
{
	var id = dialog.getElementById('stpl-details-dialog-textbox-id').value;
	var unr = dialog.getElementById('stpl-details-dialog-textbox-unr').value;
	var verband = dialog.getElementById('stpl-details-dialog-textbox-verband').value;
	var gruppe = dialog.getElementById('stpl-details-dialog-textbox-gruppe').value;
	var gruppe_kurzbz = dialog.getElementById('stpl-details-dialog-menulist-gruppe_kurzbz').value;
	var ort_kurzbz = dialog.getElementById('stpl-details-dialog-menulist-ort_kurzbz').value;
	var datum = dialog.getElementById('stpl-details-dialog-box-datum').value;
	var stunde = dialog.getElementById('stpl-details-dialog-menulist-stunde').value;
	var titel = dialog.getElementById('stpl-details-dialog-textbox-titel').value;
	var anmerkung = dialog.getElementById('stpl-details-dialog-textbox-anmerkung').value;
	var fix = dialog.getElementById('stpl-details-dialog-checkbox-fix').checked;
	var mitarbeiter_uid = dialog.getElementById('stpl-details-dialog-menulist-lektor').value;
	var semester = dialog.getElementById('stpl-details-dialog-textbox-semester').value;
	if(semester=='')
	{
		alert('Semester darf nicht leer sein');
		return false;
	}
	var url = '<?php echo APP_ROOT ?>content/tempusDBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'savestundenplaneintrag');

	req.add('stundenplan_id', id);
	req.add('unr', unr);
	req.add('verband', verband);
	req.add('gruppe', gruppe);
	req.add('gruppe_kurzbz', gruppe_kurzbz);
	req.add('ort_kurzbz', ort_kurzbz);
	req.add('datum', ConvertDateToISO(datum));
	req.add('stunde', stunde);
	req.add('titel', titel);
	req.add('anmerkung', anmerkung);
	req.add('stundenplan_id', id);
	req.add('fix', fix);
	req.add('mitarbeiter_uid', mitarbeiter_uid);
	req.add('semester',semester);

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

		var treeStplDetails=parent.document.getElementById('treeStplDetails');
		//alert('url'+STPLlastDetailUrl);
		treeStplDetails.setAttribute('datasources', '');
		treeStplDetails.setAttribute('datasources', STPLlastDetailUrl+"&ts="+gettimestamp());
		return true;
	}

}

// ****
// * Loescht den Eintrag der im Detailfenster markiert ist aus der Stundenplantabelle
// ****
function STPLDetailDelete()
{
	//alert('url'+STPLlastDetailUrl);
	//return false;

	tree = document.getElementById('treeStplDetails');
	if(tree.currentIndex==-1)
	{
		alert('Bitte zuerst einen Eintrag markieren!');
		return false;
	}

	var col = tree.columns ? tree.columns["stundenplan_id"] : "stundenplan_id";
	var stundenplanid = tree.view.getCellText(tree.currentIndex,col);

	var col = tree.columns ? tree.columns["stpl-details-overlay-lehrstunde-reservierung"] : "stpl-details-overlay-lehrstunde-reservierung";
	var reservierung = tree.view.getCellText(tree.currentIndex,col);

	if(confirm('Wollen Sie diesen Datensatz wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/tempusDBDML.php';
		var req = new phpRequest(url,'','');

		if(reservierung=='true')
		{
			req.add('type', 'deletereservierung');
			req.add('reservierung_id', stundenplanid);
		}
		else
		{
			req.add('type', 'deletestundenplaneintrag');
			req.add('stundenplan_id', stundenplanid);
		}

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

			var treeStplDetails=parent.document.getElementById('treeStplDetails');
			//alert('url'+STPLlastDetailUrl);
			treeStplDetails.setAttribute('datasources', '');
			treeStplDetails.setAttribute('datasources', STPLlastDetailUrl+"&ts="+gettimestamp());
		}
	}
}

// ****
// * Speichert die aktuelle Scrollposition der Wochenuebersicht.
// * Nach dem neuladen der Uebersicht, kann die Scrollposition mit setScrollpositionTimeTableWeek wieder gesetzt werden
// ****
function saveScrollPositionTimeTableWeek()
{
	if(window.TimeTableWeek)
		var sbox = window.TimeTableWeek.document.getElementById('timetable-week-scrollbox');
	else
		var sbox = document.getElementById('timetable-week-scrollbox');
	if(sbox)
	{
		var xpcomInterface = sbox.boxObject.QueryInterface(Components.interfaces.nsIScrollBoxObject);
		var x={};
		var y={};
		xpcomInterface.getPosition(x, y);
		TimeTableWeekPositionX=x.value;
		TimeTableWeekPositionY=y.value;
		window.parent.TimeTableWeekPositionX=x.value;
		window.parent.TimeTableWeekPositionY=y.value;
	}
}

// ****
// * Setzt die Scrollposition wieder auf den Stand zurueck der zuvor mittels saveScrollPositionTimeTableWeek gespeichert wurde
// ****
function setScrollpositionTimeTableWeek()
{
	var sbox = document.getElementById('timetable-week-scrollbox');
	if(sbox)
	{
		var xpcomInterface = sbox.boxObject.QueryInterface(Components.interfaces.nsIScrollBoxObject);
		xpcomInterface.scrollTo(window.parent.TimeTableWeekPositionX, window.parent.TimeTableWeekPositionY);
	}
}

// ****
// * Loescht alle markierten Stunden
// ****
function TimetableDeleteEntries()
{
	saveScrollPositionTimeTableWeek();
	var contentFrame=document.getElementById('iframeTimeTableWeek');
	if(window.TimeTableWeek)
		var daten=window.TimeTableWeek.document.getElementById('TimeTableWeekData');
	else
		var daten=document.getElementById('TimeTableWeekData');
	var datum=parseInt(daten.getAttribute("datum"));
	var type=daten.getAttribute("stpl_type");
	var	stg_kz=daten.getAttribute("stg_kz");
	var sem=daten.getAttribute("sem");
	var ver=daten.getAttribute("ver");
	var grp=daten.getAttribute("grp");
	var gruppe=daten.getAttribute("gruppe");
	var ort=daten.getAttribute("ort");
	var pers_uid=daten.getAttribute("pers_uid");
	var doIt=true;
	var aktion='stpl_delete_single';

	doIt=confirm('Es werden die gewaehlten Eintraege aus dem LV-Plan geloescht!\nSind Sie sicher?')

	var attributes="\n?type="+type+"&datum="+datum+"&ort="+encodeURIComponent(ort)+"&pers_uid="+pers_uid+"\n&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"\n&gruppe="+gruppe;
	attributes+="&aktion="+aktion;
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
	url+=attributes;

	//IDs der Stunden dazuhaengen
    idList = TimeTableWeekGetMarkedIdList();
    url+=idList

	if (url && doIt)
		location.href=url;
}
function StplWeekOpenNotiz(item)
{
	var lehreinheit_id=item.getAttribute('lehreinheit_id');
	window.open('<?php echo APP_ROOT; ?>content/notizdialog.xul.php?lehreinheit_id='+lehreinheit_id,'Details', 'height=500,width=600,left=100,top=100,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}

/**
 * Oeffnet einen Dialog zur Zuordnung von Ressourcen zu Stundenplaneintraegen
 */
function BetriebsmittelZuordnen(item)
{
	url = '<?php echo APP_ROOT; ?>content/lvplanung/ressourcedialog.xul.php?';

	var datum = item.getAttribute('datum');
	url=url+'datum='+datum;

	// Es werden die Stunden von allen markierten eintraegen geholt
	var stunden = TimeTableWeekGetMarkedHoursArray(item);
	if(stunden.length>0)
	{
		for(i in stunden)
			url = url+'&stunde[]='+stunden[i];

		// Alle StundenplanIDs holen von den Eintraegen die markiert sind
		var ids = TimeTableWeekGetMarkedIdArray();
		for(i in ids)
			url = url+'&stplid[]='+ids[i];
	}
	else
	{
		// Wenn kein eintrag markiert ist, wird der genommen auf den geklickt wurde
		url = url+'&stunde[]'+item.getAttribute('stunde');

		idlist = item.getAttribute('idList');
		idarr = idlist.split(/&stundenplan_id[0-9]=/);
		for(i in idarr)
		{
			if(idarr[i]=='')
				continue;
			url = url+'&stplid[]'+idarr[i];
		}

	}
	window.open(url,'Details', 'height=350,width=800,left=100,top=100,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
}
