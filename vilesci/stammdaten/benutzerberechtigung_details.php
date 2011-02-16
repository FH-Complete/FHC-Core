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

$reloadstr = '';  // neuladen der liste im oberen frame
$htmlstr = '';
$errorstr = ''; //fehler beim insert
$sel = '';
$chk = '';
$oe_arr = array();
$rolle_arr = array();
$berechtigung_arr = array();
$st_arr = array();

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
	
	$reloadstr .= "<script type='text/javascript'>\n";
	$reloadstr .= "	parent.uebersicht.location.href='benutzerberechtigung_uebersicht.php';";
	$reloadstr .= "</script>\n";

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
		if ($ber->new)
		{
			$reloadstr .= "<script type='text/javascript'>\n";
			$reloadstr .= "	parent.uebersicht.location.href='benutzerberechtigung_uebersicht.php';";
			$reloadstr .= "</script>\n";
		}
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

$b->getBerechtigungen();
foreach($b->result as $berechtigung)
{
	$berechtigung_arr[] = $berechtigung->berechtigung_kurzbz;
}

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
		
		$ben = new benutzer();
		if (!$ben->load($uid))
			die('Benutzer existiert nicht');
		
		$rights->loadBenutzerRollen($uid);
	}
	elseif(isset($_REQUEST['funktion_kurzbz']) && $_REQUEST['funktion_kurzbz']!='')
	{
		$funktion_kurzbz = $_REQUEST['funktion_kurzbz'];
		
		$funktion = new funktion();
		if(!$funktion->load($funktion_kurzbz))
			die('Funktion existiert nicht');

		$rights->loadBenutzerRollen(null, $funktion_kurzbz);
	}
	
	
	$htmlstr .= "Berechtigungen <b>".$uid.$funktion_kurzbz."</b>\n";
	$htmlstr .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Filter:
	  <a href="benutzerberechtigung_details.php?filter=alle&amp;uid='.$uid.'&amp;funktion_kurzbz='.$funktion_kurzbz.'" '.($filter=='alle'?'style="font-weight:bold"':'').'>Alle</a> 
	| <a href="benutzerberechtigung_details.php?filter=wawi&amp;uid='.$uid.'&amp;funktion_kurzbz='.$funktion_kurzbz.'" '.($filter=='wawi'?'style="font-weight:bold"':'').'>nur WaWi</a>
	| <a href="benutzerberechtigung_details.php?filter=ohnewawi&amp;uid='.$uid.'&amp;funktion_kurzbz='.$funktion_kurzbz.'" '.($filter=='ohnewawi'?'style="font-weight:bold"':'').'>ohne WaWi</a>
	
	';
	$htmlstr .= "<table class='detail' style='padding-top:10px;'>\n";
	$htmlstr .= "<tr></tr>\n";
	$htmlstr .= "<tr>
					<td>Rolle</td>
					<td>Berechtigung</td>
					<td>Art</td>
					<td>Organisationseinheit</td>
					<td>Kostenstelle</td>
					<td>Semester</td>
					<td>Neg</td>
					<td>Start</td>
					<td>Ende</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>\n";
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
		$htmlstr .= "<form action='benutzerberechtigung_details.php?filter=".$filter."' method='POST' name='berechtigung".$b->benutzerberechtigung_id."'>\n";
		$htmlstr .= "<input type='hidden' name='benutzerberechtigung_id' value='".$b->benutzerberechtigung_id."'>\n";
		$htmlstr .= "<input type='hidden' name='uid' value='".$b->uid."'>\n";
		$htmlstr .= "<input type='hidden' name='funktion_kurzbz' value='".$b->funktion_kurzbz."'>\n";
		$htmlstr .= "	<tr id='".$b->benutzerberechtigung_id."'>\n";
		
		//Rolle
		$htmlstr .= "		<td><select name='rolle_kurzbz' id='rolle_kurzbz_$b->benutzerberechtigung_id' onchange='markier(\"".$b->benutzerberechtigung_id."\"); setnull(\"berechtigung_kurzbz_$b->benutzerberechtigung_id\");'>\n";
		$htmlstr .= "			<option value=''></option>\n";
		for ($i = 0; $i < sizeof($rolle_arr); $i++)
		{
			if ($b->rolle_kurzbz == $rolle_arr[$i])
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "				<option value='".$rolle_arr[$i]."' ".$sel.">".$rolle_arr[$i]."</option>";
		}
		$htmlstr .= "		</select></td>\n";
		
		//Berechtigung
		$htmlstr .= "		<td><select name='berechtigung_kurzbz' id='berechtigung_kurzbz_$b->benutzerberechtigung_id' onchange='markier(\"".$b->benutzerberechtigung_id."\"); setnull(\"rolle_kurzbz_$b->benutzerberechtigung_id\");''>\n";
		$htmlstr .= "			<option value=''></option>\n";
		for ($i = 0; $i < sizeof($berechtigung_arr); $i++)
		{
			if ($b->berechtigung_kurzbz == $berechtigung_arr[$i])
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "				<option value='".$berechtigung_arr[$i]."' ".$sel.">".$berechtigung_arr[$i]."</option>";
		}
		$htmlstr .= "		</select></td>\n";
		
		//Art
		$htmlstr .= "		<td><input type='text' name='art' value='".$b->art."' size='5' maxlength='5' onchange='markier(\"".$b->benutzerberechtigung_id."\")'></td>\n";
		
		//Organisationseinheit
		$htmlstr .= "		<td><select name='oe_kurzbz' onchange='markier(\"".$b->benutzerberechtigung_id."\")'>\n";
		$htmlstr .= "			<option value=''>-- Alle --</option>\n";

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
			$htmlstr .= "				<option value='".$oekey->oe_kurzbz."' ".$sel." ".$class.">".$oekey->organisationseinheittyp_kurzbz.' '.$oekey->bezeichnung.'</option>';
		}
		$htmlstr .= "		</select></td>\n";
		
		//Kostenstelle
		$htmlstr .= "		<td><select name='kostenstelle_id' onchange='markier(\"".$b->benutzerberechtigung_id."\")'>\n";
		$htmlstr .= "			<option value=''>-- keine Auswahl --</option>\n";

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
			
			$htmlstr .= "		<option value='".$kst->kostenstelle_id."' ".$sel." ".$class.">".$kst->bezeichnung.'</option>';
		}
		$htmlstr .= "		</select></td>\n";
		
		//Studiensemester	
		$htmlstr .= "		<td><select name='studiensemester_kurzbz' onchange='markier(\"".$b->benutzerberechtigung_id."\")'>\n";
		$htmlstr .= "			<option value=''></option>\n";
		for ($i = 0; $i < sizeof($st_arr); $i++)
		{
			if ($b->studiensemester_kurzbz == $st_arr[$i])
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "				<option value='".$st_arr[$i]."' ".$sel.">".$st_arr[$i]."</option>";
		}
		$htmlstr .= "		</select></td>\n";
		
		
		$htmlstr .= "		<td><input type='checkbox' name='negativ' ".($b->negativ?'checked="checked"':'')." onchange='markier(\"".$b->benutzerberechtigung_id."\")'></td>\n";				
		$htmlstr .= "		<td><input type='text' name='start' value='".$b->start."' size='10' maxlength='10' onchange='markier(\"".$b->benutzerberechtigung_id."\")'></td>\n";
		$htmlstr .= "		<td><input type='text' name='ende' value='".$b->ende."' size='10' maxlength='10' onchange='markier(\"".$b->benutzerberechtigung_id."\")'></td>\n";
		
		$htmlstr .= "		<td><input type='submit' name='schick' value='speichern'></td>";
		$htmlstr .= "		<td><input type='submit' name='del' value='l&ouml;schen'></td>";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "</form>\n";
	}
		
	$htmlstr .= "<form action='benutzerberechtigung_details.php' method='POST' name='berechtigung_neu'>\n";
	$htmlstr .= "<input type='hidden' name='neu' value='1'>\n";
	$htmlstr .= "<input type='hidden' name='benutzerberechtigung_id' value=''>\n";
	$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
	$htmlstr .= "<input type='hidden' name='funktion_kurzbz' value='".$funktion_kurzbz."'>\n";
	$htmlstr .= "	<tr id='neu'>\n";
	
	//Rolle
	$htmlstr .= "		<td><select name='rolle_kurzbz' id='rolle_kurzbz_neu' onchange='markier(\"neu\"); setnull(\"berechtigung_kurzbz_neu\");'>\n";
	$htmlstr .= "			<option value=''></option>\n";
	for ($i = 0; $i < sizeof($rolle_arr); $i++)
	{
		$sel = "";
		$htmlstr .= "				<option value='".$rolle_arr[$i]."' ".$sel.">".$rolle_arr[$i]."</option>";
	}
	$htmlstr .= "		</select></td>\n";
	
	//Berechtigung_kurzbz
	$htmlstr .= "		<td><select name='berechtigung_kurzbz' id='berechtigung_kurzbz_neu' onchange='markier(\"neu\"); setnull(\"rolle_kurzbz_neu\");'>\n";
	$htmlstr .= "			<option value=''></option>\n";
	for ($i = 0; $i < sizeof($berechtigung_arr); $i++)
	{
		$sel = "";
		$htmlstr .= "				<option value='".$berechtigung_arr[$i]."' ".$sel.">".$berechtigung_arr[$i]."</option>";
	}
	$htmlstr .= "		</select></td>\n";
	
	//Art
	$htmlstr .= "		<td><input type='text' name='art' value='' size='5' maxlength='5' onchange='markier(\"neu\")'></td>\n";
	
	//Organisationseinheit
	$htmlstr .= "		<td><select name='oe_kurzbz' onchange='markier(\"neu\")'>\n";
	$htmlstr .= "			<option value=''>-- Alle --</option>\n";
	
	foreach ($oe->result as $oekey)
	{
		if(!$oekey->aktiv)
				$class='class="inactive"';
			else
				$class='';
		$htmlstr .= "				<option value='".$oekey->oe_kurzbz."' ".$class.">".$oekey->organisationseinheittyp_kurzbz.' '.$oekey->bezeichnung.'</option>';
	}
	$htmlstr .= "		</select></td>\n";
	
	//Kostenstelle
	$htmlstr .= "		<td><select name='kostenstelle_id' onchange='markier(\"".(isset($b->benutzerberechtigung_id)?$b->benutzerberechtigung_id:'')."\")'>\n";
	$htmlstr .= "			<option value=''>-- keine Auswahl --</option>\n";

	foreach ($kostenstelle->result as $kst)
	{
		if(!$kst->aktiv)
			$class='class="inactive"';
		else
			$class='';
		
		$htmlstr .= "		<option value='".$kst->kostenstelle_id."' ".$class.">".$kst->bezeichnung.'</option>';
	}
	$htmlstr .= "		</select></td>\n";
		
	//Studiensemester			
	$htmlstr .= "		<td><select name='studiensemester_kurzbz' onchange='markier(\"neu\")'>\n";
	$htmlstr .= "			<option value=''></option>\n";
	for ($i = 0; $i < sizeof($st_arr); $i++)
	{
		$sel = "";
		$htmlstr .= "				<option value='".$st_arr[$i]."' ".$sel.">".$st_arr[$i]."</option>";
	}
	$htmlstr .= "		</select></td>\n";
		
	$htmlstr .= "		<td><input type='checkbox' name='negativ' onchange='markier(\"neu\")'></td>\n";
	$htmlstr .= "		<td><input type='text' name='start' value='' size='10' maxlength='10' onchange='markier(\"neu\")'></td>\n";
	$htmlstr .= "		<td><input type='text' name='ende' value='' size='10' maxlength='10' onchange='markier(\"neu\")'></td>\n";
	
	$htmlstr .= "		<td><input type='submit' name='schick' value='neu'></td>";
	$htmlstr .= "	</tr>\n";
	$htmlstr .= "</form>\n";
	
	$htmlstr .= "</table>\n";

}
$htmlstr .= "<div class='inserterror'>".$errorstr."</div>\n";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Berechtigung - Details</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<script src="../../include/js/mailcheck.js"></script>
	<script src="../../include/js/datecheck.js"></script>
	<script type="text/javascript">
		function confdel()
		{
			if(confirm("Diesen Datensatz wirklick loeschen?"))
			  return true;
			return false;
		}
		
		function markier(id)
		{
			document.getElementById(id).style.background = "#FC988D";
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
		
		function checkrequired(feld)
		{
			if(feld.value == "")
			{
				feld.className = "input_error";
				return false;
			}
			else
			{
				feld.className = "input_ok";
				return true;
			}
		}
		function setnull(id)
		{
			document.getElementById(id).selectedIndex=0;
		}
	</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>