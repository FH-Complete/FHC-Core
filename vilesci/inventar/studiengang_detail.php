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
	$studiengang_id=trim(isset($_REQUEST['studiengang_id'])?$_REQUEST['studiengang_id']:'');
	$kurzzeichen=trim((isset($_REQUEST['kurzzeichen']) ? $_REQUEST['kurzzeichen']:''));
	$studiengang_search=trim((isset($_REQUEST['studiengang_search']) ? $_REQUEST['studiengang_search']:''));
 	$debug=trim((isset($_REQUEST['debug']) ? $_REQUEST['debug']:false));

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
	if($oBenutzerberechtigung->isBerechtigt($berechtigung_kurzbz,null,'s'))
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
		<title>WAWI Studiengang - Suche</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="<?php echo $path;?>skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $path;?>include/js/jquery.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="<?php echo $path;?>include/js/tablesort/table.css" type="text/css">
		<script src="<?php echo $path;?>include/js/tablesort/table.js" type="text/javascript"></script>

		<link rel="stylesheet" type="text/css" href="<?php echo $path;?>skin/jquery-ui-1.9.2.custom.min.css">
		<script type="text/javascript" src="<?php echo $path;?>vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="<?php echo $path;?>vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="<?php echo $path;?>vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="<?php echo $path;?>include/js/jquery.ui.datepicker.translation.js"></script>
		<script type="text/javascript" src="<?php echo $path;?>vendor/jquery/sizzle/sizzle.js"></script>
	</head>
	<body>
		<h1 title="Anwender:<?php echo $uid ?>">&nbsp;WAWI Studiengang - Suche&nbsp;</h1>
	    <form name="sendform" action="<?php echo $_SERVER["PHP_SELF"];  ?>" method="post" enctype="application/x-www-form-urlencoded">
		<table class="navbar" style="border:0;width:100%;">
			<tr>
				<!-- studiengang -->
				<td><label for="studiengang_id">Studiengang</label>&nbsp;<input onchange="document.sendform.studiengang_search.value='';document.sendform.kurzzeichen.value='';" id="studiengang_id" name="studiengang_id" size="5" maxlength="10" value="<?php echo $studiengang_id; ?>" >&nbsp;
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
								  $('#studiengang_id').autocomplete('inventar_autocomplete.php', {
									minChars:1,
									matchSubset:1,matchContains:1,
									width:500,
									cacheLength:50,
									onItemSelect:selectItem,
									formatItem:formatItem,
									extraParams:{'work':'wawi_studiengang_id'
										}
								  });
						  });
						</script>
				</td>

			<!-- kurzzeichen-->
				<td><label for="kurzzeichen">Kurzzeichen</label>&nbsp;<input onchange="document.sendform.studiengang_id.value='';" id="kurzzeichen" name="kurzzeichen" size="10" maxlength="40" value="<?php echo $kurzzeichen; ?>" >&nbsp;
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
								  $('#kurzzeichen').autocomplete('inventar_autocomplete.php', {
									minChars:1,
									matchSubset:1,matchContains:1,
									width:500,
									cacheLength:50,
									onItemSelect:selectItem,
									formatItem:formatItem,
									extraParams:{'work':'wawi_studiengang_search'
										}
								  });
						  });
						</script>
				</td>
			<!-- studiengang suche-->
				<td><label for="studiengang_search">Bezeichnung</label>&nbsp;<input onchange="document.sendform.studiengang_id.value='';document.sendform.kurzzeichen.value='';" id="studiengang_search" name="studiengang_search" size="20" maxlength="40" value="<?php echo $studiengang_search; ?>" >&nbsp;</td>
				<td class="ac_submit">&nbsp;<a href="javascript:document.sendform.submit();"><img src="../../skin/images/application_go.png" alt="suchen" />&nbsp;suchen</a>&nbsp;<input style="display:none;" name="debug" value="<?php echo $debug;?>"></td>
			</tr>
		</table>
		</form>
	<hr>
<?php
// ------------------------------------------------------------------------------------------
//	Datenlesen
// ------------------------------------------------------------------------------------------
	// Init vor Studiengangn lesen
	$oWAWI->errormsg='';
	$oWAWI->result=array();
	$check=$studiengang_id.$kurzzeichen.$studiengang_search;
	if ( $check!='' && !$oWAWI->studiengang($studiengang_id,$kurzzeichen,$studiengang_search))
  		$errormsg[]=$oWAWI->errormsg;

	if (is_array($oWAWI->result) && count($oWAWI->result)==1)
	{
		$studiengang_id=$oWAWI->result[0]->studiengang_id;
		if ( $check!='' && !$oWAWI->studiengang_kostenstelle($studiengang_id,$kurzzeichen,$studiengang_search))
  			$errormsg[]=$oWAWI->errormsg;
		echo output_Studienganginformation($oWAWI->result,$debug);
	}
	else if (is_array($oWAWI->result) && count($oWAWI->result) >1)
	{
		echo output_Studiengang($oWAWI->result,$debug);
	}
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
// ------------------------------------------------------------------------------------------
function output_Studiengang($resultStudiengang=null,$debug=false)
{
	$htmlstring='';
	if (is_null($resultStudiengang) || !is_array($resultStudiengang) || count($resultStudiengang)<1)
		return $htmlstring;

	$htmlstring.='<table  id="t1" class="liste table-autosort:2 table-stripeclass:alternate table-autostripe">
			<thead>';

	if (is_array($resultStudiengang) && count($resultStudiengang)>1)
		$htmlstring.='<tr><th colspan="10">Bitte eine Studiengang aus den '.count($resultStudiengang).' gefundenen ausw&auml;hlen</th></tr>';

	$htmlstring.='<tr class="liste">
				<th class="table-sortable:default">Studiengang ID</th>
				<th class="table-sortable:default">Kurzz.</th>
				<th class="table-sortable:default">Bezeichnung</th>
				<th class="table-sortable:default">Studenten</th>
				<th class="table-sortable:default">Aktiv</th>
				<th class="table-sortable:default" style="font-size:x-small;">Bestellung</th>
			</tr>
			</thead>
			';
	for ($pos=0;$pos<count($resultStudiengang);$pos++)
	{
		if ($pos%2)
			$classe='liste1';
		else
			$classe='liste0';
		$htmlstring.='<!-- studiengang Auflisten -->
	    	<tr class="'.$classe.'" style="font-size:smaller;">
				<td><a title="Detail Studiengang '.$resultStudiengang[$pos]->studiengang_id.'" href="'.$_SERVER["PHP_SELF"].'?studiengang_id='.$resultStudiengang[$pos]->studiengang_id.'">'.$resultStudiengang[$pos]->studiengang_id.'</a></td>
				<td>'.$resultStudiengang[$pos]->kurzzeichen.'</td>
				<td>'.$resultStudiengang[$pos]->bezeichnung.'</td>
				<td>'.$resultStudiengang[$pos]->studentenanzahl.'</td>
				<td>'.($resultStudiengang[$pos]->aktiv==true || $resultStudiengang[$pos]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />').'</td>
				<td align="right">&nbsp;<a title="Bestellungen zum Studiengang '.$resultStudiengang[$pos]->studiengang_id.'" href="bestellung.php?studiengang_id='.$resultStudiengang[$pos]->studiengang_id.'&amp;jahr_monat='.date("Y").'">anzeigen<img src="../../skin/images/application_go.png" alt="Bestellungen anzeigen" /></a>&nbsp;</td>
			</tr>
			';
	}
	$htmlstring.='</table>';
	return $htmlstring;
}
// ------------------------------------------------------------------------------------------
function output_Studienganginformation($resultStudiengang=null,$debug=false)
{
	$htmlstring='';
	if (is_null($resultStudiengang) || !is_array($resultStudiengang) || count($resultStudiengang)<1)
		return $htmlstring;
	if (!$oWAWI = new wawi())
	   	die($oWAWI->errormsg . ($debug?' *** File:='.__FILE__.' Line:='.__LINE__:''));
	$pos=0;

	if ($pos%2)
		$classe='liste1';
	else
		$classe='liste0';

	$htmlstring.='<fieldset><legend>Studiengang&nbsp;'.$resultStudiengang[$pos]->studiengang_id.'&nbsp;'.$resultStudiengang[$pos]->bezeichnung.'</legend>';
	$htmlstring.='<br /><!-- Studiengang Detail -->
			<table  class="liste" style="border:0;width:100%;">
				<tr class="'.$classe.'">
					<th align="right">Studiengang</th>
					<td width="80%">'.$resultStudiengang[$pos]->studiengang_id.'&nbsp;'.$resultStudiengang[$pos]->kurzzeichen.'&nbsp;'.$resultStudiengang[$pos]->bezeichnung.'</td>
				</tr>
				<tr class="'.$classe.'">
					<th align="right">Studenten</th>
					<td>'.$resultStudiengang[$pos]->studentenanzahl.'&nbsp;</td>
				</tr>
				<tr class="'.$classe.'">
					<th align="right">Aktiv&nbsp;</th>
					<td>'.$resultStudiengang[$pos]->aktiv_kz.'&nbsp;'.($resultStudiengang[$pos]->aktiv==true || $resultStudiengang[$pos]->aktiv=='t'?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />').'</td>
				</tr>
				<tr class="'.$classe.'">
					<th align="right">Bestellung</th>
					<td>&nbsp;<a title="Bestellungen zum Studiengang '.$resultStudiengang[$pos]->studiengang_id.'" href="bestellung.php?studiengang_id='.$resultStudiengang[$pos]->studiengang_id.'&amp;jahr_monat='.date("Y").'">anzeigen<img src="../../skin/images/application_go.png" alt="Bestellungen anzeigen" /></a>&nbsp;</td>
				</tr>

		</table>';

	$oWAWI->errormsg='';
	$oWAWI->result=array();
	if (!$oWAWI->studiengang_benutzer($resultStudiengang[$pos]->studiengang_id))
		$htmlstring.=$oWAWI->errormsg;
	$resultStudiengangbenutzer=$oWAWI->result;

	if (is_array($resultStudiengangbenutzer) && count($resultStudiengangbenutzer)>0)
	{
		$htmlstring.='<br /><!-- studiengang Kurzdetail -->
				<table class="liste">
				<thead>
					<tr>
						<th>Benutzer&nbsp;</th>
						<th style="display:none;">Tel&nbsp;</th>

						<th style="display:none;">lesen&nbsp;</th>
						<th style="display:none;">schreiben&nbsp;</th>
						<th>freigeben&nbsp;</th>
						<th>verwalten&nbsp;</th>

						<th colspan="2">letzte &Auml;nderung</th>
					</tr>
				<thead>';

		for ($i=0;$i<count($resultStudiengangbenutzer);$i++)
		{
			if ($i%2)
				$classe='liste1';
			else
				$classe='liste0';
			$htmlstring.='<!-- Studiengang Kurzdetail -->
					<tr class="'.$classe.'">

						<td>&nbsp;'
						.($resultStudiengangbenutzer[$i]->email?'<a href="mailto:'.$resultStudiengangbenutzer[$i]->email.'?subject=Anlage studiengang '.$resultStudiengangbenutzer[$i]->studiengang_id.'">&nbsp;<img src="../../skin/images/email.png" alt="email" /></a>':'')
						.($resultStudiengangbenutzer[$i]->titel?'&nbsp;'.$resultStudiengangbenutzer[$i]->titel.'&nbsp;':'&nbsp;').$resultStudiengangbenutzer[$i]->vname.'&nbsp;'.$resultStudiengangbenutzer[$i]->nname.'&nbsp;
						</td>

						<td style="display:none;">&nbsp;'
						.$resultStudiengangbenutzer[$i]->tel
						.'</td>

						<td style="display:none;" align="center">'.($resultStudiengangbenutzer[$i]->lesen=='t' || $resultStudiengangbenutzer[$i]->lesen==true?'<img src="../../skin/images/green_point.gif" alt="ja" />':'<img src="../../skin/images/red_point.gif" alt="nein" />').'</td>
						<td style="display:none;" align="center">'.($resultStudiengangbenutzer[$i]->schreiben=='t' || $resultStudiengangbenutzer[$i]->schreiben==true?'<img src="../../skin/images/green_point.gif" alt="ja" />':'<img src="../../skin/images/red_point.gif" alt="nein" />').'</td>
						<td align="center">'.($resultStudiengangbenutzer[$i]->freigeben=='t' || $resultStudiengangbenutzer[$i]->freigeben==true?'<img src="../../skin/images/green_point.gif" alt="ja" />':'<img src="../../skin/images/red_point.gif" alt="nein" />').'</td>
						<td align="center">'.($resultStudiengangbenutzer[$i]->verwalten=='t' || $resultStudiengangbenutzer[$i]->verwalten==true?'<img src="../../skin/images/green_point.gif" alt="ja" />':'<img src="../../skin/images/red_point.gif" alt="nein" />').'</td>


						<td>&nbsp;'
						.($resultStudiengangbenutzer[$i]->l_email?'<a href="mailto:'.$resultStudiengangbenutzer[$i]->l_email.'?subject=Anlage studiengang '.$resultStudiengangbenutzer[$i]->studiengang_id.'">&nbsp;<img src="../../skin/images/email.png" alt="email" /></a>':'')
						.($resultStudiengangbenutzer[$i]->l_titel?'&nbsp;'.$resultStudiengangbenutzer[$i]->l_titel.'&nbsp;':'&nbsp;').$resultStudiengangbenutzer[$i]->l_vname.'&nbsp;'.$resultStudiengangbenutzer[$i]->l_nname
						.'</td>
						<td>&nbsp;<img src="../../skin/images/date_edit.png" alt="cupdate" />&nbsp;'
						.substr($resultStudiengangbenutzer[$i]->lupdate,0,19)
						.'</td>
					</tr>
				';

		}
		$htmlstring.='</table>';
	}
	$htmlstring.='</fieldset>';

	$htmlstring.='<fieldset><legend>Kostenstelle(n)</legend>';
	$htmlstring.='<br /><!-- Kostenstellen -->
		<table class="liste"  style="border:0;">';
	$htmlstring.='
					<thead>
						<tr>
							<th>ID&nbsp;</th>
							<th>Nr.&nbsp;</th>
							<th>Kurzz.</th>
							<th>Bezeichnung</th>
							<th style="display:none;">Anlage</th>
							<th>letzte &Auml;nderung</th>
							<th>aktiv</th>
							<th>Bestellung</th>
						</tr>
					<thead>';
	for ($pos=0;$pos<count($resultStudiengang);$pos++)
	{
		if ($pos%2)
			$classe='liste1';
		else
			$classe='liste0';

		$resultKostenstelle=$oWAWI->kostenstelle($resultStudiengang[$pos]->kostenstelle_kostenstelle_id,null,null,$resultStudiengang[0]->studiengang_id );

		for ($ii=0;$ii<count($resultKostenstelle);$ii++)
		{
			$htmlstring.='
				<tr class="'.$classe.'">
					<td><a title="Detail Kostenstelle '.$resultKostenstelle[$ii]->kostenstelle_id.'" href="kostenstelle_detail.php?kostenstelle_id='.$resultKostenstelle[$ii]->kostenstelle_id.'">'.$resultKostenstelle[$ii]->kostenstelle_id.'</a></td>
					<td><a title="Detail Kostenstelle '.$resultKostenstelle[$ii]->kostenstelle_nr.'" href="kostenstelle_detail.php?kostenstelle_id='.$resultKostenstelle[$ii]->kostenstelle_id.'">'.$resultKostenstelle[$ii]->kostenstelle_nr.'</a></td>
					<td>'.$resultKostenstelle[$ii]->kurzzeichen.'</td>
					<td>'.$resultKostenstelle[$ii]->bezeichnung.'</td>

					<td style="display:none;">'
					.($resultKostenstelle[$ii]->c_email?'<a href="mailto:'.$resultKostenstelle[$ii]->c_email.'?subject=Anlage studiengang '.$resultKostenstelle[$ii]->studiengang_id.'">&nbsp;<img src="../../skin/images/email.png" alt="email" /></a>':'')
					.($resultKostenstelle[$ii]->c_titel?'&nbsp;'.$resultKostenstelle[$ii]->c_titel.'&nbsp;':'&nbsp;').$resultKostenstelle[$ii]->c_vname.'&nbsp;'.$resultKostenstelle[$ii]->c_nname.'&nbsp;
					</td>

					<td>'
					.($resultKostenstelle[$ii]->l_email?'<a href="mailto:'.$resultKostenstelle[$ii]->l_email.'?subject=Anlage studiengang '.$resultKostenstelle[$ii]->studiengang_id.'">&nbsp;<img src="../../skin/images/email.png" alt="email" /></a>':'')
					.($resultKostenstelle[$ii]->l_titel?'&nbsp;'.$resultKostenstelle[$ii]->l_titel.'&nbsp;':'&nbsp;').$resultKostenstelle[$ii]->l_vname.'&nbsp;'.$resultKostenstelle[$ii]->l_nname.'&nbsp;
					</td>

					<td align="left">&nbsp;'.(empty($resultKostenstelle[$ii]->ddate)?'&nbsp;<img src="../../skin/images/tick.png" alt="aktiv" />&nbsp;':'&nbsp;<img src="../../skin/images/cross.png" alt="nicht aktiv" />&nbsp;'.$resultKostenstelle[$ii]->d_nname.'&nbsp;'.substr($resultKostenstelle[$ii]->ddate,0,19)).'</td>

					<td style="display:none;">'
					.($resultKostenstelle[$ii]->d_email?'<a href="mailto:'.$resultKostenstelle[$ii]->d_email.'?subject=Anlage studiengang '.$resultKostenstelle[$ii]->studiengang_id.'">&nbsp;<img src="../../skin/images/email.png" alt="email" /></a>':'')
					.($resultKostenstelle[$ii]->d_titel?'&nbsp;'.$resultKostenstelle[$ii]->d_titel.'&nbsp;':'&nbsp;').$resultKostenstelle[$ii]->d_vname.'&nbsp;'.$resultKostenstelle[$ii]->d_nname.'&nbsp;

					</td>

					<td>&nbsp;<a title="Bestellungen zur Kostenstelle '.$resultKostenstelle[$ii]->kostenstelle_id.'" href="bestellung.php?kostenstelle_id='.$resultKostenstelle[$ii]->kostenstelle_id.'&amp;jahr_monat='.date("Y").'">anzeigen<img src="../../skin/images/application_go.png" alt="Bestellungen anzeigen" /></a>&nbsp;</td>

				</tr>

			';
		}
	}
	$htmlstring.='</table>';
	$htmlstring.='</fieldset>';
	$htmlstring.='<div style="width:100%;text-align:right;"><a href="javascript:history.back();"><img src="../../skin/images/cross.png" alt="schliessen" title="schliessen/close" />&nbsp;zur&uuml;ck</a></div />';
	return $htmlstring;

}
?>
