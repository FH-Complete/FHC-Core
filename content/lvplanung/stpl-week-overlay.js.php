<?php
include('../../vilesci/config.inc.php');
?>

// LVA-Panel aktualisieren
function onLVARefresh()
{
	// LVAs
	var vboxLehrveranstalungPlanung=document.getElementById('vboxLehrveranstalungPlanung');
	var datasources=vboxLehrveranstalungPlanung.getAttribute('datasources');
	//alert (datasources);
	vboxLehrveranstalungPlanung.setAttribute('datasources',datasources+"&bla=");
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
	var pers_uid=daten.getAttribute("pers_uid");

	var d = new Date();
    var datum=0;
    //Sekunden seit 1.1.1970
    datum = d.getTime()/1000;
	//alert(datum);
	var attributes="?type="+type+"&datum="+datum+"&ort="+ort+"&pers_uid="+pers_uid+"&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe;
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

	// neues Datum berechnen. Eine Woche sind 604800 Sekunden.
	datum+=(604800*wochen)+1;

	var attributes="?type="+type+"&datum="+datum+"&ort="+ort+"&pers_uid="+pers_uid+"&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe;
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
	var kw=daten.getAttribute("kw");
	var KWZiel=evt.target.getAttribute("kw");
	var wochen=KWZiel-kw;

	// neues Datum berechnen. Eine Woche sind 604800 Sekunden.
	datum+=(604800*wochen)+1;

	var attributes="?type="+type+"&datum="+datum+"&ort="+ort+"&pers_uid="+pers_uid+"&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe;
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
	url+=attributes;
	if (url)
		contentFrame.setAttribute('src', url);
}

function onLVAdoStpl(evt)
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
	var aktion=evt.target.getAttribute("aktion");
	var doIt=true;
	var oneDate=new Date();
	if (aktion=='lva_stpl_del_single')
		doIt=confirm('Es werden alle Lehrveranstaltungen aus dem Stundenplan dieser Woche geloescht!\nSind Sie sicher?')
	else
		if (aktion=='lva_stpl_del_multi')
			doIt=confirm('Es werden alle Lehrveranstaltungen aus dem Stundenplan ab dieser Woche geloescht!\nSind Sie sicher?')
		else
			aktion+="_search";
	var idList=evt.target.getAttribute("idList");

	var attributes="?type="+type+"&datum="+datum+"&ort="+ort+"&pers_uid="+pers_uid+"&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe;
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
		var aktion=event.target.getAttribute("aktion");
		aktion+="_single_search";
		var idList=event.target.getAttribute("idList");

		var attributes="\n?type="+type+"&datum="+datum+"&ort="+ort+"&pers_uid="+pers_uid+"\n&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"\n&gruppe="+gruppe;
		attributes+=idList+"&aktion="+aktion;
		var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
		url+=attributes;
		//alert(url);
		if (url)
			location.href=url;
	}
}

function onStplDelete(aktion)
{
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
	var idList=document.popupNode.getAttribute("idList");
	var doIt=true;
	doIt=confirm('Es werden die gewaehlten Eintraege aus dem Stundenplan geloescht!\nSind Sie sicher?')

	var attributes="\n?type="+type+"&datum="+datum+"&ort="+ort+"&pers_uid="+pers_uid+"\n&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"\n&gruppe="+gruppe;
	attributes+=idList+"&aktion="+aktion;
	var url = "<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
	url+=attributes;
	//alert(url);
	if (url && doIt)
		location.href=url;
}

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

	var attributes="?type="+type+"&datum="+datum+"&stunde="+stunde+"&ort_kurzbz="+ort_kurzbz+"&pers_uid="+pers_uid+"&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&gruppe="+gruppe+"&ort_kurzbz="+ort_kurzbz;
	//alert(attributes);
	attributes+=idList;
	var url = "<?php echo APP_ROOT; ?>rdf/lehrstunde.rdf.php";
	url+=attributes;
	//alert('first:'+window.parent.STPLlastDetailUrl);
	window.parent.STPLlastDetailUrl = url;
	//alert(url+' - '+window.parent.STPLlastDetailUrl);
	var treeStplDetails=parent.document.getElementById('treeStplDetails');
	treeStplDetails.setAttribute('datasources',url);
}


// ****
// * oeffnet einen Dialog zum Bearbeiten der StundenplanDetails
// ****
function STPLDetailEdit()
{
	tree = document.getElementById('treeStplDetails');
	var col = tree.columns ? tree.columns["stundenplan_id"] : "stundenplan_id";
	if(tree.currentIndex!=-1)
	{
		var id = tree.view.getCellText(tree.currentIndex,col);
	}
	else
	{
		alert('Bitte zuerst einen Eintrag markieren!');
		return false;
	}
	
	window.open('<?php echo APP_ROOT; ?>content/lvplanung/stpl-details-dialog.xul.php?id='+id,'Details', 'height=350,width=350,left=100,top=100,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');
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
	
	var url = '<?php echo APP_ROOT ?>content/tempusDBDML.php';
	var req = new phpRequest(url,'','');
	
	req.add('type', 'savestundenplaneintrag');

	req.add('stundenplan_id', id);
	req.add('unr', unr);
	req.add('verband', verband);
	req.add('gruppe', gruppe);
	req.add('gruppe_kurzbz', gruppe_kurzbz);
	req.add('ort_kurzbz', ort_kurzbz);
	req.add('datum', datum);
	req.add('stunde', stunde);
	req.add('titel', titel);
	req.add('anmerkung', anmerkung);
	req.add('stundenplan_id', id);
	req.add('fix', fix);
	
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
		treeStplDetails.setAttribute('datasources', STPLlastDetailUrl);
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
	var col = tree.columns ? tree.columns["stundenplan_id"] : "stundenplan_id";
	
	if(tree.currentIndex!=-1)
	{
		var stundenplanid = tree.view.getCellText(tree.currentIndex,col);
	}
	else
	{
		alert('Bitte zuerst einen Eintrag markieren!');
		return false;
	}
	
	if(confirm('Wollen Sie diesen Datensatz wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>content/tempusDBDML.php';
		var req = new phpRequest(url,'','');
		
		req.add('type', 'deletestundenplaneintrag');
	
		req.add('stundenplan_id', stundenplanid);
		
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
			treeStplDetails.setAttribute('datasources', STPLlastDetailUrl);
		}
	}
}