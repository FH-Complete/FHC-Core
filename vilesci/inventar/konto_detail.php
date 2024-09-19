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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

// ---------------- Vilesci Include Dateien einbinden
	$path='../../';

	require_once($path.'config/vilesci.config.inc.php');
  	require_once($path.'include/functions.inc.php');
	require_once($path.'include/benutzerberechtigung.class.php');
	require_once($path.'include/mitarbeiter.class.php');
  	require_once($path.'include/wawi.class.php');

	if (!$uid = get_uid())
		die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

// ------------------------------------------------------------------------------------------
// Variable Initialisieren
// ------------------------------------------------------------------------------------------
	$errormsg=array();
	$berechtigung_kurzbz='wawi/inventar:begrenzt';
	$recht=false;

// ------------------------------------------------------------------------------------------
// Parameter Aufruf uebernehmen
// ------------------------------------------------------------------------------------------
	$konto=trim(isset($_REQUEST['konto_id'])?$_REQUEST['konto_id']:(isset($_REQUEST['konto'])?$_REQUEST['konto']:''));
	$kontonr=trim(isset($_REQUEST['kontonr'])?$_REQUEST['kontonr']:'');
	$konto_search=trim((isset($_REQUEST['konto_search']) ? $_REQUEST['konto_search']:''));
  	$debug=trim((isset($_REQUEST['debug']) ? $_REQUEST['debug']:false));

// ------------------------------------------------------------------------------------------
// Berechtigung
// ------------------------------------------------------------------------------------------
	$oBenutzerberechtigung = new benutzerberechtigung();

	// read Berechtigung
	if (!$oBenutzerberechtigung->getBerechtigungen($uid))
		die('Sie haben keine Berechtigung !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	$recht=false;
	if($oBenutzerberechtigung->isBerechtigt($berechtigung_kurzbz,($oe_kurzbz?$oe_kurzbz:null),'s'))
		$recht=true;
	if (!$recht)
		die('Sie haben keine Berechtigung f&uuml;r diese Seite !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

// ------------------------------------------------------------------------------------------
//	Datenbankanbindung
// ------------------------------------------------------------------------------------------
	if (!$oWAWI = new wawi())
	   	die('Fehler beim Verbinden mit der Datenbank '.($debug?$oWAWI->errormsg.' *** File:='.__FILE__.' Line:='.__LINE__:''));
	$oWAWI->debug=$debug;
	$oWAWI->result=array();
	$oWAWI->errormsg='';

// ------------------------------------------------------------------------------------------
// HTML Output
// ------------------------------------------------------------------------------------------
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>WAWI Konto - Suche</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="<?php echo $path;?>skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $path;?>include/js/jquery.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="<?php echo $path;?>include/js/tablesort/table.css" type="text/css">
		<script src="<?php echo $path;?>include/js/tablesort/table.js" type="text/javascript"></script>

		<link rel="stylesheet" href="../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">
        <script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>

	</head>
	<body>

		<h1 title="Anwender:<?php echo $uid ?>">&nbsp;WAWI Konto - Suche&nbsp;</h1>
	    <form name="sendform" action="<?php echo $_SERVER["PHP_SELF"];  ?>" method="post" enctype="application/x-www-form-urlencoded">
		<table class="navbar" style="border:0;width:100%;">
			<tr>
				<!-- konto -->
					<td><label for="konto">Konto</label>&nbsp;
						<input onchange="document.sendform.kontonr.value='';document.sendform.konto_search.value='';setTimeout('sendform.submit()',1500);" id="konto" name="konto" size="5" maxlength="10" value="<?php echo $konto; ?>" />&nbsp;
						<script type="text/javascript">
							function selectItem(li)
							{
							   return false;
							}
							function formatItem(row)
							{
							    return row[0] + " <i>" + row[1] + "</i> ";
							}

							$(document).ready(function() {

								// $('#konto').autocomplete({
								// 	source: "inventar_autocomplete.php",
								// 	minLength:1,
								// 	response: function(event, ui)
								// 	{
								// 		//Value und Label fuer die Anzeige setzen
								// 		for(i in ui.content)
								// 		{
								// 			ui.content[i].value = ui.content[i].nachname + " " + ui.content[i].vorname;
								// 			ui.content[i].label = ui.content[i].nachname + " " + ui.content[i].vorname;
								// 		}
								// 	},
								// 	select: function(event, ui)
								// 	{
								// 		$('#ansprechperson_uid').val(ui.item.uid);
								// 	}
								// });

								  $('#konto').autocomplete('inventar_autocomplete.php', {
									minChars:1,
									matchSubset:1,matchContains:1,
									width:500,
									cacheLength:0,
									onItemSelect:selectItem,
									formatItem:formatItem,
									extraParams:{'work':'wawi_konto_id'}
								  });
						  });
						</script>
				</td>
				<!-- kontonr -->
				<td><label for="kontonr">Kontonr.</label>&nbsp;
					<input onchange="document.sendform.konto.value='';document.sendform.konto_search.value='';" id="kontonr" name="kontonr" size="5" maxlength="10" value="<?php echo $kontonr; ?>" />&nbsp;</td>
				</td>
				<td><label for="konto_search">Bezeichnung</label>&nbsp;
					<input onchange="document.sendform.kontonr.value='';document.sendform.konto.value='';" id="konto_search" name="konto_search" size="40" maxlength="80" value="<?php echo $konto_search; ?>" />&nbsp;</td>
				</td>
				<td class="ac_submit"><a href="javascript:document.sendform.submit();"><img src="../../skin/images/application_go.png" alt="suchen" />&nbsp;suchen</a>&nbsp;<input style="display:none;" name="debug" value="<?php echo $debug;?>"></td>
			</tr>
		</table>
		</form>
		<hr />
<?php

// ------------------------------------------------------------------------------------------
//	Datenlesen
// ------------------------------------------------------------------------------------------
	$oWAWI->errormsg='';
	$oWAWI->result=array();
	$check=$konto.$kontonr.$konto_search;
	if ($check!='' && !$oWAWI->konto($konto,$kontonr,$konto_search))
		$errormsg[]=$oWAWI->errormsg;
	// Check ob Daten gefunden
	if (is_array($oWAWI->result) && count($oWAWI->result)==1)
		echo output_konteninformation($oWAWI->result,$debug);
	else if (is_array($oWAWI->result) && count($oWAWI->result)>1)
		echo output_konten($oWAWI->result,$debug);
	else
	{
		if ($check!='' )
			$errormsg[]='keine Daten gefunden';
		else
			$errormsg[]='Auswahl fehlt';
	}

	// Error - Meldungen ausgeben
	if (is_array($errormsg) && count($errormsg)>0)
		echo implode("<br />",$errormsg);
	elseif (!is_array($errormsg))
		echo "<br />",$errormsg;
?>
</body>
</html>

<?php
function output_konten($resultKonto=null,$debug=false)
{
	$htmlstring='';
	if (is_null($resultKonto) || !is_array($resultKonto) || count($resultKonto)<1)
		return $htmlstring;

	$htmlstring.='<table  id="t1" class="liste table-autosort:2 table-stripeclass:alternate table-autostripe">
			<thead>';
	if (is_array($resultKonto) && count($resultKonto)>1)
		$htmlstring.='<tr><th colspan="10">Bitte eine Bestellnummer aus den '.count($resultKonto).' gefundenen ausw&auml;hlen</th></tr>';
	$htmlstring.='<tr class="liste">
				<th class="table-sortable:default">Konto</th>
				<th class="table-sortable:default">Kontonnr.</th>
				<th class="table-sortable:default">Bezeichnung</th>
				<th class="table-sortable:default" style="font-size:x-small;">Bestellung</th>
			</tr>
			</thead>
			';
	for ($pos=0;$pos<count($resultKonto);$pos++)
	{
			if ($pos%2)
				$classe='liste1';
			else
				$classe='liste0';
			$htmlstring.='<!-- Konto Auflistung -->
		    	<tr class="'.$classe.'">
					<td><a title="Detail Konto '.$resultKonto[$pos]->konto.'" href="konto_detail.php?konto='.$resultKonto[$pos]->konto.'">'.$resultKonto[$pos]->konto.'</a></td>
					<td><a title="Detail Kontonr '.$resultKonto[$pos]->konto.'" href="konto_detail.php?konto='.$resultKonto[$pos]->konto.'&amp;kontonr='.$resultKonto[$pos]->kontonr.'">'.$resultKonto[$pos]->kontonr.'</a></td>
					<td>'.$resultKonto[$pos]->beschreibung.'</td>
					<td><a title="Bestellungen zum Konto '.$resultKonto[$pos]->konto.' Kontonr '.$resultKonto[$pos]->kontonr.'" href="bestellung.php?konto='.$resultKonto[$pos]->konto.'&amp;jahr_monat='.date("Y").'">anzeigen<img src="../../skin/images/application_go.png" alt="Bestellungen anzeigen" /></a>&nbsp;</td>
				</tr>
				';
	}
	$htmlstring.='</table>';
	return $htmlstring;
}

function output_konteninformation($resultKonto=null,$debug=false)
{
	$htmlstring='';
	if (is_null($resultKonto) || !is_array($resultKonto) || count($resultKonto)<1)
  		return $htmlstring;

	for ($pos=0;$pos<count($resultKonto);$pos++)
	{
			$htmlstring.='<!-- Konto Kurzdetail -->
				<table class="liste"> ';
				if ($pos%2)
					$classe='liste1';
				else
					$classe='liste0';
			$htmlstring.='<!-- Konto Auflistung -->
		    	<tr class="'.$classe.'">
						<th align="right">Konto&nbsp;</th>
						<td width="80%">'.$resultKonto[$pos]->konto.'&nbsp;</td>
					</tr>
		    	<tr class="'.$classe.'">
						<th align="right">Kontonnr.&nbsp;</th>
						<td>'.$resultKonto[$pos]->kontonr.'&nbsp;</td>
					</tr>
		    	<tr class="'.$classe.'">
						<th align="right">Bezeichnung&nbsp;</th>
						<td>'.$resultKonto[$pos]->beschreibung.'&nbsp;</td>
					</tr>
			    	<tr class="'.$classe.'">
						<th align="right">Bestellungen&nbsp;</th>
						<td><a title="Bestellungen zum Konto '.$resultKonto[$pos]->konto.' Kontonr '.$resultKonto[$pos]->kontonr.'" href="bestellung.php?konto='.$resultKonto[$pos]->konto.'&amp;kontonr='.$resultKonto[$pos]->kontonr.'&amp;jahr_monat='.date("Y").'">anzeigen<img src="../../skin/images/application_go.png" alt="Bestellungen anzeigen" /></a>&nbsp;</td>
					</tr>
					';
			if ($resultKonto[$pos]->cuser || $resultKonto[$pos]->luser  || $resultKonto[$pos]->duser)
				$htmlstring.='
					<tr><th align="right">Anlage am&nbsp;<img src="../../skin/images/date_edit.png" alt="cupdate" /></th><td align="left">'.($resultKonto[$pos]->cuser?$resultKonto[$pos]->cdate:'').'&nbsp;</td></tr>
					<tr><th align="right">Anlage von&nbsp;<img src="../../skin/images/edit.png" alt="cuser" /></th><td align="left">'.($resultKonto[$pos]->cuser?'<a href="mailto:'.$resultKonto[$pos]->c_email.'?subject=Anlage Konto '.$resultKonto[$pos]->konto.' - '.$resultKonto[$pos]->kontonr.'  '.$resultKonto[$pos]->beschreibung.'">&nbsp;<img src="../../skin/images/email.png" alt="email" />&nbsp;</a>':'').'&nbsp;'.($resultKonto[$pos]->c_nname?$resultKonto[$pos]->c_anrede:'').'&nbsp;'.$resultKonto[$pos]->c_vname.'&nbsp;'.$resultKonto[$pos]->c_nname.'</td></tr>
					<tr><th align="right">letzte &Auml;nderung am&nbsp;<img src="../../skin/images/date_edit.png" alt="lupdate" /></th><td align="left">'.($resultKonto[$pos]->luser?$resultKonto[$pos]->lupdate:'').'&nbsp;</td></tr>
					<tr><th align="right">letzte &Auml;nderung von&nbsp;<img src="../../skin/images/edit.png" alt="luser" /></th><td align="left">'.($resultKonto[$pos]->luser?'<a href="mailto:'.$resultKonto[$pos]->l_email.'?subject=Aendern Konto '.$resultKonto[$pos]->konto.' - '.$resultKonto[$pos]->kontonr.'  '.$resultKonto[$pos]->beschreibung.'">&nbsp;<img src="../../skin/images/email.png" alt="email" />&nbsp;</a>':'').'&nbsp;'.($resultKonto[$pos]->l_nname?$resultKonto[$pos]->l_anrede:'').'&nbsp;'.$resultKonto[$pos]->l_vname.'&nbsp;'.$resultKonto[$pos]->l_nname.'</td></tr>
					<tr><th align="right">gel&ouml;scht am&nbsp;<img src="../../skin/images/date_edit.png" alt="dupdate" /></th><td align="left">'.($resultKonto[$pos]->duser?$resultKonto[$pos]->ddate:'').'&nbsp;</td></tr>
					<tr><th align="right">gel&ouml;scht von&nbsp;<img src="../../skin/images/edit.png" alt="duser" /></th><td align="left">'.($resultKonto[$pos]->duser?'<a href="mailto:'.$resultKonto[$pos]->d_email.'?subject=Loeschen Konto '.$resultKonto[$pos]->konto.' - '.$resultKonto[$pos]->kontonr.'  '.$resultKonto[$pos]->beschreibung.'">&nbsp;<img src="../../skin/images/email.png" alt="email" />&nbsp;'.($resultKonto[$pos]->d_nname?$resultKonto[$pos]->d_anrede:'').'&nbsp;'.$resultKonto[$pos]->d_vname.'&nbsp;'.$resultKonto[$pos]->d_nname.'&nbsp;</a>':'').'</td></tr>
				</table>
				';
			$htmlstring.='</table>';
	}
	$htmlstring.='<div style="width:100%;text-align:right;"><a href="javascript:history.back();"><img src="../../skin/images/cross.png" alt="schliessen" title="schliessen/close" />&nbsp;zur&uuml;ck</a></div />';
	return $htmlstring;
}
?>
