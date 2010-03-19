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
	$firma_id=trim(isset($_REQUEST['firma_id'])?$_REQUEST['firma_id']:(isset($_REQUEST['firma_id'])?$_REQUEST['firma_id']:''));
	$firma_search=trim((isset($_REQUEST['firma_search']) ? $_REQUEST['firma_search']:''));
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
		<title>WAWI Firmen - Suche</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="<?php echo $path;?>skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $path;?>include/js/jquery.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="<?php echo $path;?>include/js/tablesort/table.css" type="text/css">
		<script src="<?php echo $path;?>include/js/tablesort/table.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery.autocomplete.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery.autocomplete.min.js" type="text/javascript"></script>		
	</head>
	<body>

	<h1 title="Anwender:<?php echo $uid ?>">&nbsp;WAWI Firmen - Suche&nbsp;</h1>
	  <form name="sendform" action="<?php echo $_SERVER["PHP_SELF"];  ?>" method="post" enctype="application/x-www-form-urlencoded">
		<table class="navbar" style="border:0;width:100%;">
			<tr>

				<!-- firma_id -->
					<td><label for="firma_id">ID</label>&nbsp;
						<input onchange="document.sendform.firma_search.value='';" id="firma_id" name="firma_id" size="5" maxlength="10" value="<?php echo $firma_id; ?>" />&nbsp;
						<script type="text/javascript">
							function selectItem(li) {
							   return false;
							}
							function formatItem(row) {
							    return row[0] + " <i>" + row[1] + "</i> ";
							}
							$(document).ready(function() {
								  $('#firma_id').autocomplete('inventar_autocomplete.php', {
									minChars:3,
									matchSubset:1,matchContains:1,
									width:500,
									cacheLength:100,
									onItemSelect:selectItem,
									formatItem:formatItem,
									extraParams:{'work':'wawi_firma_id'}
								  });
						  });
						</script>
					</td>
					
				<!-- firma_search -->
				<td><label for="firma_search">Bezeichnung</label>&nbsp;
					<input onchange="document.sendform.firma_id.value='';" id="firma_search" name="firma_search" size="40" maxlength="80" value="<?php echo $firma_search; ?>" />&nbsp;
						<script type="text/javascript">
							function selectItem(li) {
							   return false;
							}
							function formatItem(row) {
							    return row[0] + " <i>" + row[1] + "</i> ";
							}
							$(document).ready(function() {
								  $('#firma_search').autocomplete('inventar_autocomplete.php', {
									minChars:4,
									matchSubset:1,matchContains:1,
									width:500,
									cacheLength:100,
									onItemSelect:selectItem,
									formatItem:formatItem,
									extraParams:{'work':'wawi_firma_search'}
								  });
						  });
						</script>
				</td>
				<td style="width:10%;background-color: #FFFFDD;"><a href="javascript:document.sendform.submit();"><img src="../../skin/images/application_go.png" alt="suchen" />&nbsp;suchen</a>&nbsp;<input style="display:none;" name="debug" value="<?php echo $debug;?>"></td>
				
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
	$check=$firma_id.$firma_search;
	if ($check!='' && !$oWAWI->firma($firma_id,$firma_search))
		$errormsg[]=$oWAWI->errormsg;
		
	// check Datenlesen erfolgreich
	if (is_array($oWAWI->result) && count($oWAWI->result)==1)
		echo output_firmainformation($oWAWI->result,$debug);
	else if (is_array($oWAWI->result) && count($oWAWI->result)>1)
		echo output_firma($oWAWI->result,$debug);
	else
	{
		if ($check!='' )
			$errormsg[]='keine Daten gefunden';
		else
			$errormsg[]='Auswahl fehlt';
	}
	// Meldungen ausgeben
	if (is_array($errormsg) && count($errormsg)>0)
		  echo '<font class="error">'. implode("<br />",$errormsg).'</font>';
	else if (!is_array($errormsg))
		  echo '<font class="error"><br />'.$errormsg.'</font>';
 ?>
</body>
</html>

<?php
function output_firma($resultFirma=null,$debug=false)
{
	$htmlstring='';
	if (is_null($resultFirma) || !is_array($resultFirma) || count($resultFirma)<1)
		return $htmlstring;

	$htmlstring.='<table  id="t1" class="liste table-autosort:2 table-stripeclass:alternate table-autostripe">
			<thead>';
	if (is_array($resultFirma) && count($resultFirma)>1)
		$htmlstring.='<tr><th colspan="10">Bitte eine Firmennummer aus den '.count($resultFirma).' gefundenen ausw&auml;hlen</th></tr>';
	$htmlstring.='<tr class="liste">
				<th class="table-sortable:default">ID</th>
				<th class="table-sortable:default">Firmenname</th>
				<th class="table-sortable:default">Plz.Ort</th>
				<th class="table-sortable:default">Strasse</th>
				<th class="table-sortable:default" style="font-size:x-small;">Bestellung</th>
			</tr>
			</thead>
			';
	for ($pos=0;$pos<count($resultFirma);$pos++)
	{
			if ($pos%2)
				$classe='liste1';
			else
				$classe='liste0';
			$htmlstring.='<!-- firma_id Auflistung -->
		    	<tr class="'.$classe.'">
					<td><a title="Detail firma_id '.$resultFirma[$pos]->firma_id.'" href="'.$_SERVER["PHP_SELF"].'?firma_id='.$resultFirma[$pos]->firma_id.'">'.$resultFirma[$pos]->firma_id.'</a></td>
					<td>'.StringCut($resultFirma[$pos]->firmenname,30).'</td>
					<td>'.$resultFirma[$pos]->plz.'&nbsp;'.StringCut($resultFirma[$pos]->ort,15).'</td>
					<td>'.StringCut($resultFirma[$pos]->strasse,20).'</td>
					<td><a title="Bestellungen zum firma_id '.$resultFirma[$pos]->firma_id.' '.$resultFirma[$pos]->firmenname.'" href="bestellung.php?firma_id='.$resultFirma[$pos]->firma_id.'&amp;jahr_monat='.date("Y").'">anzeigen<img src="../../skin/images/application_go.png" alt="Bestellungen anzeigen" /></a>&nbsp;</td>
				</tr>
				';
	}
	$htmlstring.='</table>';
	return $htmlstring;
}

function output_firmainformation($resultFirma=null,$debug=false)
{
	$htmlstring='';
	if (is_null($resultFirma) || !is_array($resultFirma) || count($resultFirma)<1)
		return $htmlstring;

	for ($pos=0;$pos<count($resultFirma);$pos++)
	{
		$htmlstring.='<fieldset><legend>Firma&nbsp;'.$resultFirma[$pos]->firma_id.'&nbsp;'.$resultFirma[$pos]->firmenname.'</legend>';
			$htmlstring.='<!-- Firma -->
				<table class="liste"> ';

			if ($pos%2)
				$classe='liste1';
			else
				$classe='liste0';

			$htmlstring.='<!-- firma_id Auflistung -->
			    	<tr class="'.$classe.'">
						<th align="right">ID&nbsp;</th>
						<td width="80%">'.$resultFirma[$pos]->firma_id.'&nbsp;</td>
					</tr>

			    	<tr class="'.$classe.'">
						<th align="right">Kurzbez.&nbsp;</th>
						<td width="80%">'.$resultFirma[$pos]->kurzbezeichnung.'&nbsp;</td>
					</tr>

			    	<tr class="'.$classe.'">
						<th align="right">Firmenname&nbsp;</th>
						<td>'.$resultFirma[$pos]->firmenname.($resultFirma[$pos]->anmerkung?'<br>'.$resultFirma[$pos]->anmerkung:'').'&nbsp;</td>
					</tr>

			    	<tr class="'.$classe.'">
						<th align="right">Ansprechpartner&nbsp;</th>
						<td width="80%">'.($resultFirma[$pos]->email?'<a href="mailto:'.$resultFirma[$pos]->email.'?subject=Anfrage von '.$resultFirma[$pos]->anrede.'&nbsp;'.$resultFirma[$pos]->vname.'&nbsp;'.$resultFirma[$pos]->nname.'&body='.$resultFirma[$pos]->ansprechpartner.'"><img src="../../skin/images/email.png" alt="email" >&nbsp;</a>':'').$resultFirma[$pos]->ansprechpartner.'&nbsp;</td>
					</tr>

					<tr class="'.$classe.'">
						<th align="right">Kundennummer&nbsp;</th>
						<td width="80%">'.$resultFirma[$pos]->kundennr.'&nbsp;</td>
					</tr>

			    	<tr class="'.$classe.'">
						<th align="right">Tel&nbsp;</th>
						<td>'.$resultFirma[$pos]->telefon.'&nbsp;</td>
					</tr>
			    	<tr class="'.$classe.'">
						<th align="right">Fax&nbsp;</th>
						<td>'.$resultFirma[$pos]->telefax.'&nbsp;</td>
					</tr>

			    	<tr class="'.$classe.'">
						<th align="right">WEB&nbsp;</th>
						<td>'.$resultFirma[$pos]->homepage.'&nbsp;</td>
					</tr>

			    	<tr class="'.$classe.'">
						<th align="right">Adresse&nbsp;</th>
						<td>'.$resultFirma[$pos]->strasse.'<br>'.$resultFirma[$pos]->plz.'&nbsp;'.$resultFirma[$pos]->ort.'&nbsp;</td>
					</tr>
					';
			$htmlstring.='</table>';

			$htmlstring.='<fieldset><legend>Verantwortlich&nbsp;</legend>';
				$htmlstring.='<table class="liste"> ';
				$htmlstring.='
			    	<tr class="'.$classe.'">
						<th align="right">Anwender&nbsp;</th>
						<td width="80%">'.($resultFirma[$pos]->mail?'<a href="mailto:'.$resultFirma[$pos]->mail.'?subject=Firma '.$resultFirma[$pos]->firma_id.' '.$resultFirma[$pos]->firmenname.' Ansprechpartner '.$resultFirma[$pos]->ansprechpartner.'&body='.$resultFirma[$pos]->anrede.'&nbsp;'.$resultFirma[$pos]->vname.'&nbsp;'.$resultFirma[$pos]->nname.'"><img src="../../skin/images/email.png" alt="email" >&nbsp;</a>':'').$resultFirma[$pos]->anrede.'&nbsp;'.$resultFirma[$pos]->vname.'&nbsp;'.$resultFirma[$pos]->nname.'&nbsp;</td>
					</tr>
			    	<tr class="'.$classe.'">
						<th align="right">Tel&nbsp;</th>
						<td>'.$resultFirma[$pos]->tel.'&nbsp;</td>
					</tr>
			    	<tr class="'.$classe.'">
						<th align="right">Fax&nbsp;</th>
						<td>'.$resultFirma[$pos]->fax.'&nbsp;</td>
					</tr>


				';
				$htmlstring.='</table>';
			$htmlstring.='</fieldset>';
			$htmlstring.='<table class="liste">
					<tr><td>&nbsp;</td></tr>
			    	<tr class="'.$classe.'">
						<th align="right">Bestellungen&nbsp;</th>
						<td width="80%"><a title="Bestellungen zum Firma '.$resultFirma[$pos]->firma_id.' '.$resultFirma[$pos]->firmenname.'" href="bestellung.php?firma_id='.$resultFirma[$pos]->firma_id.'&amp;jahr_monat='.date("Y").'">anzeigen<img src="../../skin/images/application_go.png" alt="Bestellungen anzeigen" /></a>&nbsp;</td>
					</tr>
			</table>';
		$htmlstring.='</fieldset>';
	}
	$htmlstring.='<div style="width:100%;text-align:right;"><a href="javascript:history.back();"><img src="../../skin/images/cross.png" alt="schliessen" title="schliessen/close" />&nbsp;zur&uuml;ck</a></div />';
	return $htmlstring;
}
?>
