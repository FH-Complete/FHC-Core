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
// Parameter Aufruf uebernehmen
// ------------------------------------------------------------------------------------------
	$kostenstelle_id=trim(isset($_REQUEST['kostenstelle_id'])?$_REQUEST['kostenstelle_id']:'');
	$kostenstelle_nr=trim(isset($_REQUEST['kostenstelle_nr'])?$_REQUEST['kostenstelle_nr']:'');
	$kostenstelle_search=trim((isset($_REQUEST['kostenstelle_search']) ? $_REQUEST['kostenstelle_search']:''));
	$user_id=trim((isset($_REQUEST['user_id']) ? $_REQUEST['user_id']:''));
	$studiengang_id=trim((isset($_REQUEST['studiengang_id']) ? $_REQUEST['studiengang_id']:''));
 	$debug=trim((isset($_REQUEST['debug']) ? $_REQUEST['debug']:false));

// ------------------------------------------------------------------------------------------
// Variable Initialisieren
// ------------------------------------------------------------------------------------------
	$errormsg=array();
	$berechtigung_kurzbz='wawi/inventar:begrenzt';
	$recht=false;

// ------------------------------------------------------------------------------------------
// Berechtigung
// ------------------------------------------------------------------------------------------
	$oBenutzerberechtigung = new benutzerberechtigung();
	$oBenutzerberechtigung->errormsg='';
	$oBenutzerberechtigung->berechtigungen=array();
	// read Berechtigung
	if (!$oBenutzerberechtigung->getBerechtigungen($uid))
		die('Sie haben keine Berechtigung !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	$recht=false;
	if($oBenutzerberechtigung->isBerechtigt($berechtigung_kurzbz,($oe_kurzbz?$oe_kurzbz:null),'s'))
		$recht=true;
	if (!$recht)
		die('Sie haben keine Berechtigung f&uuml;r diese Seite !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

// ------------------------------------------------------------------------------------------
//	Datenbankanbindung - Classe
// ------------------------------------------------------------------------------------------
	if (!$oWAWI = new wawi())
	   	die($oWAWI->errormsg . ($debug?' *** File:='.__FILE__.' Line:='.__LINE__:''));
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
		<title>WAWI Kostenstellen - Suche</title>
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
		<h1 title="Anwender:<?php echo $uid ?>">&nbsp;WAWI Kostenstellen - Suche&nbsp;</h1>
	    <form name="sendform" action="<?php echo $_SERVER["PHP_SELF"];  ?>" method="post" enctype="application/x-www-form-urlencoded">
		<table class="navbar" style="border:0;width:100%;">
			<tr>
				<!-- Kostenstelle ID -->
				<td>
					<label for="kostenstelle_id">Kostenstelle ID</label>&nbsp;
					<input id="kostenstelle_id" name="kostenstelle_id" size="5" maxlength="10" value="<?php echo $kostenstelle_id; ?>" >&nbsp;
						<script type="text/javascript">
							function selectItem(li)
							{
							   return false;
							}
							function formatItem(row)
							{
							    return row[0] + " <i>" + row[1] + "</i> ";
							}

							// $('#kostenstelle_id').autocomplete({
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

							$(document).ready(function() {
								  $('#kostenstelle_id').autocomplete('inventar_autocomplete.php', {
									minChars:1,
									matchSubset:1,matchContains:1,
									width:500,
									cacheLength:30,
									onItemSelect:selectItem,
									formatItem:formatItem,
									extraParams:{'work':'wawi_kostenstelle_id'}
								  });
						  });
						</script>
				</td>

				<!-- Kostenstelle NR -->
				<td>
					<label for="kostenstelle_nr">Nummer</label>&nbsp;
					<input id="kostenstelle_nr" name="kostenstelle_nr" size="5" maxlength="10" value="<?php echo $kostenstelle_nr; ?>" >&nbsp;
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
								  $('#kostenstelle_nr').autocomplete('inventar_autocomplete.php', {
									minChars:1,
									matchSubset:1,matchContains:1,
									width:500,
									cacheLength:50,
									onItemSelect:selectItem,
									formatItem:formatItem,
									extraParams:{'work':'wawi_kostenstelle_nr'
											,'kostenstelle_id':$("#kostenstelle_id").val()
									}
								  });
						  });
						</script>
				</td>
				<!-- Kostenstelle suche-->
				<td><label for="kostenstelle_search">Bezeichnung</label>&nbsp;
					<input onchange="document.sendform.kostenstelle_nr.value='';document.sendform.kostenstelle_id.value='';" id="kostenstelle_search" name="kostenstelle_search" size="50" maxlength="80" value="<?php echo $kostenstelle_search; ?>" >&nbsp;
				</td>
				<td class="ac_submit"><a href="javascript:document.sendform.submit();"><img src="../../skin/images/application_go.png" alt="suchen" />&nbsp;suchen</a>&nbsp;<input style="display:none;" name="debug" value="<?php echo $debug;?>"></td>
			</tr>
		</table>
		</form>
	<hr>
<?php
// ------------------------------------------------------------------------------------------
//	Datenlesen
// ------------------------------------------------------------------------------------------
	// Kostenstellen
	$oWAWI->errormsg='';
	$oWAWI->result=array();
	$check=$kostenstelle_id.$user_id.$studiengang_id.$kostenstelle_search.$kostenstelle_nr;
	if ($check!='' && !$oWAWI->kostenstelle($kostenstelle_id,$kostenstelle_search,$user_id,$studiengang_id,$kostenstelle_nr))
		$errormsg[]=$oWAWI->errormsg;

	if (is_array($oWAWI->result) && count($oWAWI->result)==1)
		echo output_konstenstelleinformation($oWAWI->result,$debug);
	else if (is_array($oWAWI->result) && count($oWAWI->result) >1)
		echo output_konstenstelle($oWAWI->result,$debug);
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
function output_konstenstelle($resultKonstenstelle=null,$debug=false)
{
	$htmlstring='';
	if (is_null($resultKonstenstelle) || !is_array($resultKonstenstelle) || count($resultKonstenstelle)<1)
		return $htmlstring;

	$htmlstring.='<table  id="t1" class="liste table-autosort:2 table-stripeclass:alternate table-autostripe">
			<thead>';
	if (is_array($resultKonstenstelle) && count($resultKonstenstelle)>1)
		$htmlstring.='<tr><th colspan="10">Bitte eine Kostenstelle aus den '.count($resultKonstenstelle).' gefundenen ausw&auml;hlen</th></tr>';
	$htmlstring.='<tr class="liste">
				<th class="table-sortable:default">ID</th>
				<th class="table-sortable:default">Nr.</th>
				<th class="table-sortable:default">Kurzz.</th>
				<th class="table-sortable:default">Bezeichnung</th>
				<th class="table-sortable:default">StgKz</th>
				<th class="table-sortable:default">Kurzz.</th>
				<th class="table-sortable:default">Studiengang</th>
				<th class="table-sortable:default" style="font-size:x-small;">Bestellung</th>
			</tr>
			</thead>
			';

	for ($pos=0;$pos<count($resultKonstenstelle);$pos++)
	{
		if ($pos%2)
			$classe='liste1';
		else
			$classe='liste0';
		$htmlstring.='<!-- Kostenstelle Auflisten -->
	    	<tr class="'.$classe.'" style="font-size:smaller;">
				<td><a title="Detail Kostenstelle ID '.$resultKonstenstelle[$pos]->kostenstelle_id.'" href="'.$_SERVER["PHP_SELF"].'?kostenstelle_id='.$resultKonstenstelle[$pos]->kostenstelle_id.'">'.$resultKonstenstelle[$pos]->kostenstelle_id.'</a></td>
				<td><a title="Detail Kostenstelle Nr '.$resultKonstenstelle[$pos]->kostenstelle_nr.'" href="'.$_SERVER["PHP_SELF"].'?kostenstelle_id='.$resultKonstenstelle[$pos]->kostenstelle_id.'">'.$resultKonstenstelle[$pos]->kostenstelle_nr.'</a></td>
				<td>'.$resultKonstenstelle[$pos]->kurzzeichen.'</td>
				<td>'.$resultKonstenstelle[$pos]->bezeichnung.'</td>
				<td><a title="Detail Studiengang '.$resultKonstenstelle[$pos]->studiengang_id.'" href="studiengang_detail.php?studiengang_id='.$resultKonstenstelle[$pos]->studiengang_id.'">'.$resultKonstenstelle[$pos]->studiengang_id.'</a></td>
				<td><a title="Detail Studiengang '.$resultKonstenstelle[$pos]->studiengang_id.'" href="studiengang_detail.php?studiengang_id='.$resultKonstenstelle[$pos]->studiengang_id.'">'.$resultKonstenstelle[$pos]->stg_kurzzeichen.'</a></td>
				<td><a title="Detail Studiengang '.$resultKonstenstelle[$pos]->studiengang_id.'" href="studiengang_detail.php?studiengang_id='.$resultKonstenstelle[$pos]->studiengang_id.'">'.$resultKonstenstelle[$pos]->stg_bez.'</a></td>
				<td align="right">&nbsp;<a title="Bestellungen zur Kostenstelle '.$resultKonstenstelle[$pos]->kostenstelle_nr.'" href="bestellung.php?kostenstelle_id='.$resultKonstenstelle[$pos]->kostenstelle_id.'&amp;jahr_monat='.date("Y").'">anzeigen<img src="../../skin/images/application_go.png" alt="Kostenstelle '.$resultKonstenstelle[$pos]->kostenstelle_nr.'" /></a>&nbsp;</td>
			</tr>
			';
	}
	$htmlstring.='</table>';
	return $htmlstring;
}

function output_konstenstelleinformation($resultKonstenstelle=null,$debug=false)
{
	$htmlstring='';
	if (is_null($resultKonstenstelle) || !is_array($resultKonstenstelle) || count($resultKonstenstelle)<1)
		return $htmlstring;

	if (!$oWAWI = new wawi())
	   	die($oWAWI->errormsg . ($debug?' *** File:='.__FILE__.' Line:='.__LINE__:''));

	for ($pos=0;$pos<count($resultKonstenstelle);$pos++)
	{
		if ($pos%2)
			$classe='liste1';
		else
			$classe='liste0';

		$htmlstring.='<fieldset><legend>Kostenstelle '.$resultKonstenstelle[$pos]->kostenstelle_id.'&nbsp;'.$resultKonstenstelle[$pos]->bezeichnung.'</legend>';
		$htmlstring.='<br /><!-- Kostenstelle Kurzdetail -->
			<table class="liste"  style="border:0;width:100%;">

				<tr class="'.$classe.'">
					<th align="right">Kostenstelle ID / Nr &nbsp;</th>
					<td width="70%" align="left" colspan="2">'.$resultKonstenstelle[$pos]->kostenstelle_id.'&nbsp;/&nbsp;'.$resultKonstenstelle[$pos]->kostenstelle_nr.'&nbsp;</td>
				</tr>

				<tr class="'.$classe.'">
					<th align="right">Kurzz.&nbsp;/&nbsp;Bezeichnung&nbsp;</th>
					<td align="left" colspan="2">&nbsp;'.$resultKonstenstelle[$pos]->kurzzeichen.'&nbsp;/&nbsp;'.$resultKonstenstelle[$pos]->bezeichnung.'&nbsp;</td>
				</tr>

				<tr class="'.$classe.'"><th align="right">aktiv&nbsp;</th><td colspan="2">'.(empty($resultKostenstelle[$pos]->ddate)?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />&nbsp;':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />&nbsp;').'</td></tr>

				<tr class="'.$classe.'"><th align="right">Anlage&nbsp;<img src="../../skin/images/edit.png" alt="cuser" /></th><td align="left">'.($resultKonstenstelle[$pos]->c_email?'<a href="mailto:'.$resultKonstenstelle[$pos]->c_email.'?subject=Anlage Kostenstelle '.$resultKonstenstelle[$pos]->kostenstelle_id.' '.$resultKonstenstelle[$pos]->bezeichnung.'">&nbsp;<img src="../../skin/images/email.png" alt="email" /></a>':'').'&nbsp;'.($resultKonstenstelle[$pos]->c_nname?$resultKonstenstelle[$pos]->c_anrede:'').'&nbsp;'.$resultKonstenstelle[$pos]->c_vname.'&nbsp;'.$resultKonstenstelle[$pos]->c_nname.'&nbsp;</td><td align="left">&nbsp;<img src="../../skin/images/date_edit.png" alt="cupdate" />&nbsp;'.substr($resultKonstenstelle[$pos]->cdate,0,19).'&nbsp;</td></tr>

				<tr class="'.$classe.'"><th align="right">&Auml;nderung&nbsp;<img src="../../skin/images/edit.png" alt="luser" /></th><td align="left">'.($resultKonstenstelle[$pos]->l_email?'<a href="mailto:'.$resultKonstenstelle[$pos]->l_email.'?subject=Aenderung Kostenstelle '.$resultKonstenstelle[$pos]->kostenstelle_id.' '.$resultKonstenstelle[$pos]->bezeichnung.'">&nbsp;<img src="../../skin/images/email.png" alt="email" /></a>':'').'&nbsp;'.($resultKonstenstelle[$pos]->l_nname?$resultKonstenstelle[$pos]->l_anrede:'').'&nbsp;'.$resultKonstenstelle[$pos]->l_vname.'&nbsp;'.$resultKonstenstelle[$pos]->l_nname.'&nbsp;</td><td align="left">&nbsp;<img src="../../skin/images/date_edit.png" alt="cupdate" />&nbsp;'.substr($resultKonstenstelle[$pos]->lupdate,0,19).'&nbsp;</td></tr>

				<tr class="'.$classe.'"><th align="right">L&ouml;schung&nbsp;<img src="../../skin/images/edit.png" alt="duser" /></th><td align="left">'.($resultKonstenstelle[$pos]->ddate && $resultKonstenstelle[$pos]->d_email?'<a href="mailto:'.$resultKonstenstelle[$pos]->d_email.'?subject=Geloesccht Kostenstelle '.$resultKonstenstelle[$pos]->kostenstelle_id.' '.$resultKonstenstelle[$pos]->bezeichnung.'">&nbsp;<img src="../../skin/images/email.png" alt="email" /></a>':'').'&nbsp;'.($resultKonstenstelle[$pos]->ddate && $resultKonstenstelle[$pos]->d_nname?$resultKonstenstelle[$pos]->d_anrede:'').'&nbsp;'.($resultKonstenstelle[$pos]->ddate?$resultKonstenstelle[$pos]->d_vname.'&nbsp;'.$resultKonstenstelle[$pos]->d_nname:'').'&nbsp;</td><td align="left">&nbsp;<img src="../../skin/images/date_edit.png" alt="cupdate" />&nbsp;'.substr($resultKonstenstelle[$pos]->ddate,0,19).'&nbsp;</td></tr>

				<tr class="'.$classe.'">
					<td colspan="3"><hr></td>
				</tr>

				<tr class="'.$classe.'">
					<th align="right">Studiengang ID&nbsp;</th>
					<td align="left" colspan="2"><a title="Detail Studiengang '.$resultKonstenstelle[$pos]->studiengang_id.'" href="studiengang_detail.php?studiengang_id='.$resultKonstenstelle[$pos]->studiengang_id.'&amp;stg_kurzzeichen='.$resultKonstenstelle[$pos]->stg_kurzzeichen.'">'.$resultKonstenstelle[$pos]->studiengang_id.'</a></td>
				</tr>

				<tr class="'.$classe.'">
					<th align="right">Studiengang&nbsp;</th>
					<td align="left" colspan="2"><a title="Detail Studiengang '.$resultKonstenstelle[$pos]->studiengang_id.'" href="studiengang_detail.php?studiengang_id='.$resultKonstenstelle[$pos]->studiengang_id.'&amp;stg_kurzzeichen='.$resultKonstenstelle[$pos]->stg_kurzzeichen.'">'.$resultKonstenstelle[$pos]->stg_kurzzeichen.'</a></td>
				</tr>

				<tr class="'.$classe.'">
					<th align="right">Studenten</th>
					<td align="left" colspan="2">'.$resultKonstenstelle[$pos]->studentenanzahl.'&nbsp;</td>
				</tr>
				<tr class="'.$classe.'">
					<th align="right">Stg.Aktiv&nbsp;</th>
					<td align="left" colspan="2">'.$resultKonstenstelle[$pos]->stg_aktiv.'&nbsp;'.($resultKonstenstelle[$pos]->stg_aktiv==true || $resultKonstenstelle[$pos]->stg_aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />').'</td>
				</tr>

			</table>
			';
		$htmlstring.='</fieldset>';

		$oWAWI->errormsg='';
		$oWAWI->result=array();
		if (!$oWAWI->kostenstelle_benutzer($resultKonstenstelle[$pos]->kostenstelle_id))
			$htmlstring.=$oWAWI->errormsg;
		$resultKonstenstellebenutzer=$oWAWI->result;

		if (is_null($resultKonstenstellebenutzer) || !is_array($resultKonstenstellebenutzer) || count($resultKonstenstellebenutzer)<1)
			return $htmlstring;

		$htmlstring.='<fieldset><legend>Benutzer - Rechte</legend>';
			$htmlstring.='<br /><!-- Kostenstelle Kurzdetail -->
					<table class="liste">
					<thead>
						<tr>
							<th>Benutzer&nbsp;</th>
							<th>lesen&nbsp;</th>
							<th>schreiben&nbsp;</th>
							<th>freigeben&nbsp;</th>
							<th>verwalten&nbsp;</th>
						</tr>
					<thead>';
			for ($i=0;$i<count($resultKonstenstellebenutzer);$i++)
			{
				if ($i%2)
					$classe='liste1';
				else
					$classe='liste0';
				$htmlstring.='<!-- Kostenstelle Kurzdetail -->
						<tr class="'.$classe.'">
							<td>'
							.($resultKonstenstellebenutzer[$i]->c_email?'<a href="mailto:'.$resultKonstenstellebenutzer[$i]->c_email.'?subject=Anlage Kostenstelle '.$resultKonstenstellebenutzer[$i]->kostenstelle_id.'">&nbsp;<img src="../../skin/images/email.png" alt="email" /></a>':'').'
							&nbsp;'.$resultKonstenstellebenutzer[$i]->c_titel.'&nbsp;'.$resultKonstenstellebenutzer[$i]->c_vname.'&nbsp;'.$resultKonstenstellebenutzer[$i]->c_nname.'&nbsp;
							</td>
							<td align="center">'.($resultKonstenstellebenutzer[$i]->lesen=='t' || $resultKonstenstellebenutzer[$i]->lesen==true?'<img src="../../skin/images/green_point.gif" alt="ja" />':'<img src="../../skin/images/red_point.gif" alt="nein" />').'</td>
							<td align="center">'.($resultKonstenstellebenutzer[$i]->schreiben=='t' || $resultKonstenstellebenutzer[$i]->schreiben==true?'<img src="../../skin/images/green_point.gif" alt="ja" />':'<img src="../../skin/images/red_point.gif" alt="nein" />').'</td>
							<td align="center">'.($resultKonstenstellebenutzer[$i]->freigeben=='t' || $resultKonstenstellebenutzer[$i]->freigeben==true?'<img src="../../skin/images/green_point.gif" alt="ja" />':'<img src="../../skin/images/red_point.gif" alt="nein" />').'</td>
							<td align="center">'.($resultKonstenstellebenutzer[$i]->verwalten=='t' || $resultKonstenstellebenutzer[$i]->verwalten==true?'<img src="../../skin/images/green_point.gif" alt="ja" />':'<img src="../../skin/images/red_point.gif" alt="nein" />').'</td>
						</tr>
					';

			}
			$htmlstring.='</table>';
		$htmlstring.='</fieldset>';
	}	// Ende Kostenstellen-Array
	$htmlstring.='<div style="width:100%;text-align:right;"><a href="javascript:history.back();"><img border="0" src="../../skin/images/cross.png" alt="schliessen" title="schliessen/close" />&nbsp;zur&uuml;ck</a></div />';
	return $htmlstring;
}
?>
