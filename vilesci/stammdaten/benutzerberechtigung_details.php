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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *          Manfred Kindl	<manfred.kindl@technikum-wien.at>
 */
/**
 * Detailseite zum Zuweisen von Berechtigungen zu Benutzern
 */
require_once('../../config/vilesci.config.inc.php');		
require_once('../../include/globals.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/berechtigung.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/funktion.class.php');
require_once('../../include/wawi_kostenstelle.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/berechtigung'))
	die('Sie haben keine Berechtigung fuer diese Seite');

//$reloadstr = '';  // neuladen der liste im oberen frame
$htmlstr = '';
$errorstr = ''; //fehler beim insert
$sel = '';
$chk = '';
$oe_arr = array();
$rolle_arr = array();
$berechtigung_arr = array();
$st_arr = array();
$berechtigung_user_arr = array();

$benutzerberechtigung_id = '';
$art = '';
$oe_kurzbz = '';
$studiengang_kurzbz = '';
$berechtigung_kurzbz = '';
$uid = '';
$studiensemester_kz = '';
$start = '';
$ende = '';
$neu = false;
$negativ = false;
$filter=(isset($_GET['filter'])?$_GET['filter']:'alle');

if(isset($_POST['del']))
{
	if(!$rechte->isBerechtigt('basis/berechtigung', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');
	
	$benutzerberechtigung_id = $_POST['benutzerberechtigung_id'];
	
	$ber = new benutzerberechtigung();
	if(!$ber->delete($benutzerberechtigung_id))
		$errorstr .= 'Datensatz konnte nicht gel&ouml;scht werden!';
	
	//$reloadstr .= "<script type='text/javascript'>\n";
	//$reloadstr .= "	parent.uebersicht.location.href='benutzerberechtigung_uebersicht.php';";
	//$reloadstr .= "</script>\n";

}

if(isset($_POST['schick']))
{
	if($rechte->isBerechtigt('basis/berechtigung', null, 'suid'))
	{
		$benutzerberechtigung_id = $_POST['benutzerberechtigung_id'];
		$art = $_POST['art'];
		$oe_kurzbz = $_POST['oe_kurzbz'];
		$berechtigung_kurzbz = $_POST['berechtigung_kurzbz'];
		$rolle_kurzbz = $_POST['rolle_kurzbz'];
		$uid = $_POST['uid'];
		$funktion_kurzbz = $_POST['funktion_kurzbz'];
		$studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
		$start = $_POST['start'];
		$ende = $_POST['ende'];
		$kostenstelle_id = $_POST['kostenstelle_id'];
		
		$ber = new benutzerberechtigung();
		if (isset($_POST['neu']))
		{
			$ber->insertamum=date('Y-m-d H:i:s');
			$ber->insertvon = $user;
			$ber->new = true;
		}
		else 
		{
			if(!$ber->load($benutzerberechtigung_id))
				die('Fehler beim Laden der Berechtigung');
		}
		if (isset($_POST['negativ']))
			$ber->negativ = true;
		else 
			$ber->negativ = false;
		
		$ber->benutzerberechtigung_id = $benutzerberechtigung_id;
		$ber->art = $art;
		$ber->oe_kurzbz = $oe_kurzbz;
		$ber->berechtigung_kurzbz = $berechtigung_kurzbz;
		$ber->rolle_kurzbz = $rolle_kurzbz;
		$ber->uid = $uid;
		$ber->funktion_kurzbz = $funktion_kurzbz;
		$ber->studiensemester_kurzbz = $studiensemester_kurzbz;
		$ber->start = $start;
		$ber->ende = $ende;
		$ber->updateamum = date('Y-m-d H:i:s');
		$ber->updatevon = $user;
		$ber->kostenstelle_id = $kostenstelle_id;
		
		if(!$ber->save()){
			if (!$ber->new)
				$errorstr .= "Datensatz konnte nicht upgedatet werden!".$ber->errormsg;
			else
				$errorstr .= "Datensatz konnte nicht gespeichert werden!".$ber->errormsg;
		}
		/*if ($ber->new)
		{
			$reloadstr .= "<script type='text/javascript'>\n";
			$reloadstr .= "	parent.uebersicht.location.href='benutzerberechtigung_uebersicht.php';";
			$reloadstr .= "</script>\n";
		}*/
	}
	else 
	{
		$errorstr.='Fehler beim Speichern: Sie haben keine Berechtigung zum Speichern';
	}
}

if (!$b = new berechtigung())
	die($b->errormsg);
		
$b->getRollen();
foreach($b->result as $berechtigung)
{
	$rolle_arr[] = $berechtigung->rolle_kurzbz;
}
sort($rolle_arr);
$b->getBerechtigungen();
foreach($b->result as $berechtigung)
{
	$berechtigung_arr[] = $berechtigung->berechtigung_kurzbz;
	$berechtigung_beschreibung_arr[] = $berechtigung->beschreibung;
}
//var_dump($berechtigung_arr);
$st = new studiensemester();
$st->getAll();
foreach($st->studiensemester as $studiensemester)
{
	$st_arr[] = $studiensemester->studiensemester_kurzbz;
}

$oe = new organisationseinheit();
$oe->getAll();
	
$kostenstelle = new wawi_kostenstelle();
$kostenstelle->getAll();

if (isset($_REQUEST['uid']) || isset($_REQUEST['funktion_kurzbz']))
{
	$uid='';
	$funktion_kurzbz='';
	$rights = new benutzerberechtigung();
	if(isset($_REQUEST['uid']) && $_REQUEST['uid']!='')
	{
		$uid = $_REQUEST['uid'];
		
		$bn = new benutzerberechtigung();
		$bn->getBerechtigungen($uid);
		foreach($bn->berechtigungen as $berechtigung)
		{
			$berechtigung_user_arr[] = $berechtigung->berechtigung_kurzbz;
		}
		//$berechtigung_user_arr[] = array_unique($berechtigung_user_arr[]);
		//var_dump($berechtigung_user_arr);
		$ben = new benutzer();
		if (!$ben->load($uid))
			die('Benutzer existiert nicht');
		
		$rights->loadBenutzerRollen($uid);
		$name = new benutzer();
		$name->load($uid);
		$htmlstr .= "Berechtigungen von <b>".$name->nachname." ".$name->vorname." (".$uid.")</b>\n";
	}
	elseif(isset($_REQUEST['funktion_kurzbz']) && $_REQUEST['funktion_kurzbz']!='')
	{
		$funktion_kurzbz = $_REQUEST['funktion_kurzbz'];
		
		$funktion = new funktion();
		if(!$funktion->load($funktion_kurzbz))
			die('Funktion existiert nicht');

		$rights->loadBenutzerRollen(null, $funktion_kurzbz);
		$htmlstr .= "Berechtigungen der Funktion <b>".$funktion->beschreibung."</b>\n";
	}
	
	//$htmlstr .= "Berechtigungen von <b>".$name->nachname." ".$name->vorname." (".$uid.")".$funktion_kurzbz."</b>\n";
	/*$htmlstr .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Filter:
	  <a href="benutzerberechtigung_details.php?filter=alle&amp;uid='.$uid.'&amp;funktion_kurzbz='.$funktion_kurzbz.'" '.($filter=='alle'?'style="font-weight:bold"':'').'>Alle</a> 
	| <a href="benutzerberechtigung_details.php?filter=wawi&amp;uid='.$uid.'&amp;funktion_kurzbz='.$funktion_kurzbz.'" '.($filter=='wawi'?'style="font-weight:bold"':'').'>nur WaWi</a>
	| <a href="benutzerberechtigung_details.php?filter=ohnewawi&amp;uid='.$uid.'&amp;funktion_kurzbz='.$funktion_kurzbz.'" '.($filter=='ohnewawi'?'style="font-weight:bold"':'').'>ohne WaWi</a>
	
	';*/
	$htmlstr .= "<table id='t1' class='tablesorter'>\n";
	$htmlstr .= "<thead><tr></tr>\n";
	$htmlstr .= "<tr>
					<th>Rolle</th>
					<th>Berechtigung</th>
					<th>Art</th>
					<th>Organisationseinheit</th>
					<th>Kostenstelle</th>
					<!--<th>Semester</th>-->
					<th>Neg</th>
					<th>Gültig ab</th>
					<th>Gültig bis</th>
					<th></th>
					<th></th>
					<!--<th></th>-->
				</tr></thead><tbody>\n";
	foreach($rights->berechtigungen as $b)
	{
		switch($filter)
		{
			case 'alle'; break;
			case 'wawi';
					if(!mb_strstr($b->berechtigung_kurzbz,'wawi'))
						continue 2;
					break;
			case 'ohnewawi';
					if(mb_strstr($b->berechtigung_kurzbz,'wawi'))
						continue 2;
					break;
			default: break;
		}
		$htmlstr .= "	<tr id='".$b->benutzerberechtigung_id."'>\n";
		$htmlstr .= "<form action='benutzerberechtigung_details.php?filter=".$filter."' method='POST' name='berechtigung".$b->benutzerberechtigung_id."'>\n";
		$htmlstr .= "<input type='hidden' name='benutzerberechtigung_id' value='".$b->benutzerberechtigung_id."'>\n";
		$htmlstr .= "<input type='hidden' name='uid' value='".$b->uid."'>\n";
		$htmlstr .= "<input type='hidden' name='funktion_kurzbz' value='".$b->funktion_kurzbz."'>\n";
		
		
		//Rolle
		$htmlstr .= "		<td style='padding: 1px;' name='td_$b->benutzerberechtigung_id'><select name='rolle_kurzbz' id='rolle_kurzbz_$b->benutzerberechtigung_id' onchange='markier(\"td_".$b->benutzerberechtigung_id."\"); setnull(\"berechtigung_kurzbz_$b->benutzerberechtigung_id\");' ".($b->berechtigung_kurzbz!=''?'disabled':'').">\n";
		$htmlstr .= "		<option id='aaa' value='' name='' onclick='enable(\"berechtigung_kurzbz_".$b->benutzerberechtigung_id."\");'>&nbsp;</option>\n";
		for ($i = 0; $i < sizeof($rolle_arr); $i++)
		{
			if ($b->rolle_kurzbz == $rolle_arr[$i])
			{
				$sel = " selected";
			}
			else
				$sel = "";
			$htmlstr .= "<option id='".$rolle_arr[$i]."' value='".$rolle_arr[$i]."' ".$sel." onclick='disable(\"berechtigung_kurzbz_".$b->benutzerberechtigung_id."\");' >".$rolle_arr[$i]."</option>";
		}
		$htmlstr .= "		</select></td>\n";
		
		//Berechtigung
		$htmlstr .= "		<td name='td_$b->benutzerberechtigung_id'><select name='berechtigung_kurzbz' id='berechtigung_kurzbz_$b->benutzerberechtigung_id' ".($b->rolle_kurzbz!=''?'disabled':'')." onchange='markier(\"td_".$b->benutzerberechtigung_id."\"); setnull(\"rolle_kurzbz_$b->benutzerberechtigung_id\");'>\n";
		$htmlstr .= "		<option value='' name='' onclick='enable(\"rolle_kurzbz_".$b->benutzerberechtigung_id."\");'>&nbsp;</option>\n";
		for ($i = 0; $i < sizeof($berechtigung_arr); $i++)
		{
			if ($b->berechtigung_kurzbz == $berechtigung_arr[$i])
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "				<option title='".$berechtigung_beschreibung_arr[$i]."' ".(array_search($berechtigung_arr[$i],$berechtigung_user_arr)!==false?"style='color: #666;'":"")." value='".$berechtigung_arr[$i]."' name='".$berechtigung_arr[$i]."' ".$sel." onclick='disable(\"rolle_kurzbz_".$b->benutzerberechtigung_id."\");'>".$berechtigung_arr[$i]."</option>";
		}
		$htmlstr .= "		</select></td>\n";
		
		//Art
		$htmlstr .= "		<td name='td_$b->benutzerberechtigung_id'><input id='art_$b->benutzerberechtigung_id' type='text' name='art' value='".$b->art."' size='5' maxlength='5' onChange='validateArt(\"art_$b->benutzerberechtigung_id\"); markier(\"td_".$b->benutzerberechtigung_id."\")'></td>\n";
		
		//Organisationseinheit
		$htmlstr .= "		<td name='td_$b->benutzerberechtigung_id'><select id='oe_".$b->benutzerberechtigung_id."' name='oe_kurzbz' ".($b->kostenstelle_id!=''?'disabled':'')." onchange='markier(\"td_".$b->benutzerberechtigung_id."\")' style='width: 300px;'>\n";
		$htmlstr .= "		<option value='' onclick='enable(\"kostenstelle_".$b->benutzerberechtigung_id."\");'>-- Alle --</option>\n";

		foreach ($oe->result as $oekey)
		{
			if ($b->oe_kurzbz == $oekey->oe_kurzbz && $b->oe_kurzbz != null)
				$sel = " selected";
			else
				$sel = "";
			if(!$oekey->aktiv)
				$class='class="inactive"';
			else
				$class='';
			$htmlstr .= "	<option value='".$oekey->oe_kurzbz."' ".$sel." ".$class." onclick='disable(\"kostenstelle_".$b->benutzerberechtigung_id."\");'>".$oekey->organisationseinheittyp_kurzbz.' '.$oekey->bezeichnung.'</option>';
		}
		$htmlstr .= "		</select></td>\n";
		
		//Kostenstelle
		$htmlstr .= "		<td name='td_$b->benutzerberechtigung_id'><select id='kostenstelle_".$b->benutzerberechtigung_id."'name='kostenstelle_id' ".($b->oe_kurzbz!=''?'disabled':'')." onchange='markier(\"td_".$b->benutzerberechtigung_id."\")' style='width: 300px;'>\n";
		$htmlstr .= "		<option value='' onclick='enable(\"oe_".$b->benutzerberechtigung_id."\");'>&nbsp;</option>\n";

		foreach ($kostenstelle->result as $kst)
		{
			if ($b->kostenstelle_id == $kst->kostenstelle_id)
				$sel = " selected";
			else
				$sel = "";
			if(!$kst->aktiv)
				$class='class="inactive"';
			else
				$class='';
			
			$htmlstr .= "	<option value='".$kst->kostenstelle_id."' ".$sel." ".$class." onclick='disable(\"oe_".$b->benutzerberechtigung_id."\");'>".$kst->bezeichnung.'</option>';
		}
		$htmlstr .= "		</select></td>\n";
		
		//Studiensemester	
		/*$htmlstr .= "		<td><select name='studiensemester_kurzbz' onchange='markier(\"".$b->benutzerberechtigung_id."\")'>\n";
		$htmlstr .= "			<option value=''></option>\n";
		for ($i = 0; $i < sizeof($st_arr); $i++)
		{
			if ($b->studiensemester_kurzbz == $st_arr[$i])
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "				<option value='".$st_arr[$i]."' ".$sel.">".$st_arr[$i]."</option>";
		}
		$htmlstr .= "		</select></td>\n";*/
		
		
		$htmlstr .= "		<td align='center' name='td_$b->benutzerberechtigung_id'><input type='checkbox' name='negativ' ".($b->negativ?'checked="checked"':'')." onchange='markier(\"td_".$b->benutzerberechtigung_id."\")'></td>\n";				
		$htmlstr .= "		<td name='td_$b->benutzerberechtigung_id'><input class='datepicker_datum' type='text' name='start' value='".$b->start."' size='10' maxlength='10' onchange='markier(\"td_".$b->benutzerberechtigung_id."\")'></td>\n";
		$htmlstr .= "		<td name='td_$b->benutzerberechtigung_id'><input class='datepicker_datum' type='text' name='ende' value='".$b->ende."' size='10' maxlength='10' onchange='markier(\"td_".$b->benutzerberechtigung_id."\")'></td>\n";
		
		$htmlstr .= "		<td name='td_$b->benutzerberechtigung_id'><input type='submit' name='schick' value='speichern'></td>";
		$htmlstr .= "		<td name='td_$b->benutzerberechtigung_id'><input type='submit' name='del' value='l&ouml;schen'></td>";
		$htmlstr .= "</form>\n";
		$htmlstr .= "	</tr>\n";
		
	}
		
	$htmlstr .= "	</tbody><tfooter><tr id='neu'>\n";
	$htmlstr .= "<form action='benutzerberechtigung_details.php' method='POST' name='berechtigung_neu'>\n";
	$htmlstr .= "<input type='hidden' name='neu' value='1'>\n";
	$htmlstr .= "<input type='hidden' name='benutzerberechtigung_id' value=''>\n";
	$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
	$htmlstr .= "<input type='hidden' name='funktion_kurzbz' value='".$funktion_kurzbz."'>\n";
	
	
	//Rolle
	$htmlstr .= "		<td style='padding-top: 15px;'><select name='rolle_kurzbz' id='rolle_kurzbz_neu' onchange='markier(\"neu\"); setnull(\"berechtigung_kurzbz_neu\"); setnull(\"art_neu\");'>\n";
	$htmlstr .= "			<option value='' onclick='enable(\"berechtigung_kurzbz_neu\");'>&nbsp;</option>\n";
	for ($i = 0; $i < sizeof($rolle_arr); $i++)
	{
		$sel = "";
		$htmlstr .= "				<option value='".$rolle_arr[$i]."' ".$sel." onclick='disable(\"berechtigung_kurzbz_neu\");'>".$rolle_arr[$i]."</option>";
	}
	$htmlstr .= "		</select></td>\n";
	
	//Berechtigung_kurzbz
	$htmlstr .= "		<td style='padding-top: 15px;'><select name='berechtigung_kurzbz' id='berechtigung_kurzbz_neu' onchange='markier(\"neu\"); setnull(\"rolle_kurzbz_neu\");'>\n";
	$htmlstr .= "			<option value='' onclick='enable(\"rolle_kurzbz_neu\");'>&nbsp;</option>\n";
	for ($i = 0; $i < sizeof($berechtigung_arr); $i++)
	{
		$sel = "";
		$htmlstr .= "				<option title='".$berechtigung_beschreibung_arr[$i]."' ".(array_search($berechtigung_arr[$i],$berechtigung_user_arr)!==false?"style='color: #666'":"")." value='".$berechtigung_arr[$i]."' ".$sel." onclick='disable(\"rolle_kurzbz_neu\");'>".$berechtigung_arr[$i]."</option>";
	}
	$htmlstr .= "		</select></td>\n";
	
	//Art
	$htmlstr .= "		<td style='padding-top: 15px;'><input id='art_neu' type='text' name='art' value='' size='5' maxlength='5' onBlur='checkrequired(\"art_neu\")' onChange='validateArt(\"art_neu\")' placeholder='suid'></td>\n";
	
	//Organisationseinheit
	$htmlstr .= "		<td style='padding-top: 15px;'><select id='oe_kurzbz_neu' name='oe_kurzbz' onchange='markier(\"neu\")' style='width: 300px;'>\n";
	$htmlstr .= "			<option value='' onclick='enable(\"kostenstelle_neu\");'>-- Alle --</option>\n";
	
	foreach ($oe->result as $oekey)
	{
		if(!$oekey->aktiv)
				$class='class="inactive"';
			else
				$class='';
		$htmlstr .= "				<option value='".$oekey->oe_kurzbz."' ".$class." onclick='disable(\"kostenstelle_neu\");'>".$oekey->organisationseinheittyp_kurzbz.' '.$oekey->bezeichnung.'</option>';
	}
	$htmlstr .= "		</select></td>\n";
	
	//Kostenstelle
	$htmlstr .= "		<td style='padding-top: 15px;'><select id='kostenstelle_neu' name='kostenstelle_id' onchange='markier(\"".(isset($b->benutzerberechtigung_id)?$b->benutzerberechtigung_id:'')."\")' style='width: 300px;'>\n";
	$htmlstr .= "			<option value='' onclick='enable(\"oe_kurzbz_neu\");'>&nbsp;</option>\n";

	foreach ($kostenstelle->result as $kst)
	{
		if(!$kst->aktiv)
			$class='class="inactive"';
		else
			$class='';
		
		$htmlstr .= "		<option value='".$kst->kostenstelle_id."' ".$class." onclick='disable(\"oe_kurzbz_neu\");'>".$kst->bezeichnung.'</option>';
	}
	$htmlstr .= "		</select></td>\n";
		
	//Studiensemester			
	/*$htmlstr .= "		<td style='padding-top: 10px;'><select name='studiensemester_kurzbz' onchange='markier(\"neu\")'>\n";
	$htmlstr .= "			<option value=''></option>\n";
	for ($i = 0; $i < sizeof($st_arr); $i++)
	{
		$sel = "";
		$htmlstr .= "				<option value='".$st_arr[$i]."' ".$sel.">".$st_arr[$i]."</option>";
	}
	$htmlstr .= "		</select></td>\n";*/
		
	$htmlstr .= "		<td align='center' style='padding-top: 15px;'><input type='checkbox' name='negativ' onchange='markier(\"neu\")'></td>\n";
	$htmlstr .= "		<td style='padding-top: 15px;'><input class='datepicker_datum' type='text' name='start' value='' size='10' maxlength='10' onchange='markier(\"neu\")'></td>\n";
	$htmlstr .= "		<td style='padding-top: 15px;'><input class='datepicker_datum' type='text' name='ende' value='' size='10' maxlength='10' onchange='markier(\"neu\")'></td>\n";
	
	$htmlstr .= "		<td style='padding-top: 15px;'><input type='submit' name='schick' value='neu'></td>";
	$htmlstr .= "</form>\n";
	$htmlstr .= "	</tr>\n";
	
	
	$htmlstr .= "</tfooter></table>\n";

}
$htmlstr .= "<div class='inserterror'>".$errorstr."</div>\n";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Berechtigung - Details</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
	<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
	<script src="../../include/js/mailcheck.js"></script>
	<script src="../../include/js/datecheck.js"></script>
	<script src="../../include/js/jquery1.9.min.js" type="text/javascript"></script>	
	<style type="text/css">
	table.tablesorter tbody td
	{
		padding: 1px;
	}	
	</style>	
	<script type="text/javascript">
		$(document).ready(function() 
			{ 
				$( ".datepicker_datum" ).datepicker({
					 changeMonth: true,
					 changeYear: true, 
					 dateFormat: 'yy-mm-dd',
					 });
				 
				$("#t1").tablesorter(
					{
						//sortList: [[2,0]],
						//widgets: ["zebra"],
						headers: {5:{sorter:false},8:{sorter:false},9:{sorter:false}}
					}); 
			});

		function confdel()
		{
			if(confirm("Diesen Datensatz wirklich löschen?"))
			  return true;
			return false;
		}
		
		function markier(id)
		{
			for (var i = 0; i < document.getElementsByName(id).length; i++) 
			{
				document.getElementsByName(id)[i].style.background = "#FC988D";
			}
		}
		
		function unmarkier(id)
		{
			document.getElementById(id).style.background = "#eeeeee";
		}
		
		function checkdate(feld)
		{
			if ((feld.value != "") && (!dateCheck(feld)))
			{
				//document.studiengangform.schick.disabled = true;
				feld.className = "input_error";
				return false;
			}
			else
			{
				if(feld.value != "")
					feld.value = dateCheck(feld);
		
				feld.className = "input_ok";
				return true;
			}
		}
		
		function checkrequired(id)
		{
			if(document.getElementById(id).value == "")
			{
				document.getElementById(id).style.border = "solid red 2px";
				return false;
			}
			else
			{
				document.getElementById(id).style.border = "";
				return true;
			}
		}
		function setnull(id)
		{
			document.getElementById(id).selectedIndex=0;
		}		
		function disable(id)
		{
			document.getElementById(id).disabled = true;
			//document.getElementById("art_"+id).value="";
		}
		function enable(id)
		{
			document.getElementById(id).disabled = false;
		}
		function validateArt(id)
		{
			var eingabe, c, erlaubt = 'suid', laenge;
			eingabe = document.getElementById(id).value;;
			eingabe = eingabe.toLowerCase();
			laenge = eingabe.length;
			if (eingabe == '')
			{
				alert('Geben Sie bitte einen Wert bei "Art" ein!');
				return false;
			}
			for (c = 0; c < laenge; c++)
			{
				d = eingabe.charAt(c);
				if (erlaubt.indexOf(d) == -1)
				{
					alert ('Erlaubte Werte sind s,u,i,d');
					document.getElementById(id).style.border = "solid red 2px";
					return false;
				}
				else
					document.getElementById(id).style.border = "";
			}
		}
	</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	//echo $reloadstr;
?>

</body>
</html>