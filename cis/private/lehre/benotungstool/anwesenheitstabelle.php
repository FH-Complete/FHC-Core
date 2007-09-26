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

require_once('../../../config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/uebung.class.php');
require_once('../../../../include/beispiel.class.php');
require_once('../../../../include/datum.class.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../../skin/cis.css" rel="stylesheet" type="text/css">
<title>Kreuzerltool</title>
<script language="JavaScript">
<!--
	function MM_jumpMenu(targ, selObj, restore)
	{
	  eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");

	  if(restore)
	  {
	  	selObj.selectedIndex = 0;
	  }
	}
	function confirmdelete()
	{
		return confirm('Wollen Sie die markierten Einträge wirklich löschen? Alle bereits eingetragenen Kreuzerl gehen dabei verloren!!');
	}
  //-->
</script>
</head>

<body>
<?php
if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

$user = get_uid();

if(!check_lektor($user, $conn))
	die('Sie haben keine Berechtigung fuer diesen Bereich');

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if(isset($_GET['lvid']) && is_numeric($_GET['lvid'])) //Lehrveranstaltung_id
	$lvid = $_GET['lvid'];
else
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id'])) //Lehreinheit_id
	$lehreinheit_id = $_GET['lehreinheit_id'];
else
	$lehreinheit_id = '';

//Laden der Lehrveranstaltung
$lv_obj = new lehrveranstaltung($conn);
if(!$lv_obj->load($lvid))
	die($lv_obj->errormsg);

//Studiengang laden
$stg_obj = new studiengang($conn,$lv_obj->studiengang_kz);

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';

//Vars
$datum_obj = new datum();
$show_excel_link = false;
$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');

//Kopfzeile
echo '<table class="tabcontent">';
echo ' <tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
echo '<td class="ContentHeader"><font class="ContentHeader">&nbsp;Benotungsool';
echo '</font></td><td  class="ContentHeader" align="right">'."\n";

//Studiensemester laden
$stsem_obj = new studiensemester($conn);
if($stsem=='')
	$stsem = $stsem_obj->getaktorNext();

$stsem_obj->getAll();

//Studiensemester DropDown
$stsem_content = "Studiensemester: <SELECT name='stsem' onChange=\"MM_jumpMenu('self',this,0)\">\n";

foreach($stsem_obj->studiensemester as $studiensemester)
{
	$selected = ($stsem == $studiensemester->studiensemester_kurzbz?'selected':'');
	$stsem_content.= "<OPTION value='anwesenheitstabelle.php?lvid=$lvid&stsem=$studiensemester->studiensemester_kurzbz' $selected>$studiensemester->studiensemester_kurzbz</OPTION>\n";
}
$stsem_content.= "</SELECT>\n";

//Lehreinheiten laden
if($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('admin',$lv_obj->studiengang_kz))
{
	$qry = "SELECT distinct tbl_lehrfach.kurzbz as lfbez, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.lehrform_kurzbz as lehrform_kurzbz FROM lehre.tbl_lehreinheit, lehre.tbl_lehrfach, lehre.tbl_lehreinheitmitarbeiter
			WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND
			tbl_lehreinheit.lehrfach_id = tbl_lehrfach.lehrfach_id AND
			tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_lehreinheit.studiensemester_kurzbz = '$stsem'";
}
else
{
	$qry = "SELECT distinct tbl_lehrfach.kurzbz as lfbez, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.lehrform_kurzbz as lehrform_kurzbz FROM lehre.tbl_lehreinheit, lehre.tbl_lehrfach, lehre.tbl_lehreinheitmitarbeiter
			WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND
			tbl_lehreinheit.lehrfach_id = tbl_lehrfach.lehrfach_id AND
			tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_lehreinheit.lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id) WHERE mitarbeiter_uid='$user') AND
			tbl_lehreinheit.studiensemester_kurzbz = '$stsem'";

}

if($result = pg_query($conn, $qry))
{
	if(pg_num_rows($result)>1)
	{
		//Lehreinheiten DropDown
		echo " Lehreinheit: <SELECT name='lehreinheit_id' onChange=\"MM_jumpMenu('self',this,0)\">\n";
		while($row = pg_fetch_object($result))
		{
			if($lehreinheit_id=='')
				$lehreinheit_id=$row->lehreinheit_id;
			$selected = ($row->lehreinheit_id == $lehreinheit_id?'selected':'');
			$qry_lektoren = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE lehreinheit_id='$row->lehreinheit_id'";
			if($result_lektoren = pg_query($conn, $qry_lektoren))
			{
				$lektoren = '( ';
				$i=0;
				while($row_lektoren = pg_fetch_object($result_lektoren))
				{
					$lektoren .= $row_lektoren->kurzbz;
					$i++;
					if($i<pg_num_rows($result_lektoren))
						$lektoren.=', ';
					else
						$lektoren.=' ';
				}
				$lektoren .=')';
			}
			$qry_gruppen = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$row->lehreinheit_id'";
			if($result_gruppen = pg_query($conn, $qry_gruppen))
			{
				$gruppen = '';
				$i=0;
				while($row_gruppen = pg_fetch_object($result_gruppen))
				{
					if($row_gruppen->gruppe_kurzbz=='')
						$gruppen.=$row_gruppen->semester.$row_gruppen->verband.$row_gruppen->gruppe;
					else
						$gruppen.=$row_gruppen->gruppe_kurzbz;
					$i++;
					if($i<pg_num_rows($result_gruppen))
						$gruppen.=', ';
					else
						$gruppen.=' ';
				}
			}
			echo "<OPTION value='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->lfbez-$row->lehrform_kurzbz - $gruppen $lektoren</OPTION>\n";
		}
		echo '</SELECT> ';
	}
	else
	{
		if($row = pg_fetch_object($result))
			$lehreinheit_id = $row->lehreinheit_id;
	}
}
else
{
	echo 'Fehler beim Auslesen der Lehreinheiten';
}
echo $stsem_content;
echo '</td><tr></table>';
echo '<table width="100%"><tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
echo "<td>\n";
echo "<b>$lv_obj->bezeichnung</b><br>";

if($lehreinheit_id=='')
	die('Es wurde keine passende Lehreinheit in diesem Studiensemester gefunden');

//Menue
include("menue.inc.php");
/*
echo "\n<!--Menue-->\n";
echo "<br>
<a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Verwaltung</font>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Anwesenheits- und Übersichtstabelle</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Studentenpunkte verwalten</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Statistik</font></a>
<br><br>
<!--Menue Ende-->\n";
*/

echo "<h3>Anwesenheits- und &Uuml;bersichtstabelle</h3>";

/*
$uebung_obj = new uebung($conn);
$uebung_obj->load_uebung($lehreinheit_id);
if(count($uebung_obj->uebungen)>0)
{
	echo "<table width='100%'><tr><td valign='top'>";
	echo "Wählen Sie bitte eine Kreuzerlliste aus: <SELECT name='uebung' onChange=\"MM_jumpMenu('self',this,0)\">\n";
	foreach ($uebung_obj->uebungen as $row)
	{
		if($uebung_id=='')
			$uebung_id=$row->uebung_id;

		if($uebung_id == $row->uebung_id)
			$selected = 'selected';
		else
			$selected = '';
		echo "<OPTION value='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id' $selected>";
		//Freigegeben = +
		//Nicht Freigegeben = -
		if($datum_obj->mktime_fromtimestamp($row->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($row->freigabebis)>time())
			echo '+ ';
		else
			echo '- ';
		echo $row->bezeichnung;
		echo '</OPTION>';
	}
	echo '</SELECT>';
	echo "</td>
		<td>
			<table>
			<tr>
				<td><b>+</b>...</td>
				<td>Kreuzerlliste ist <u>freigeschalten</u>.</td>
			</tr>
			<tr>
				<td><b>-</b>...</td>
				<td>Kreuzerlliste ist <u>nicht freigeschalten</u>.</td>
			</tr>
			</table>
		</td>
	</tr></table>";
}
else
	die("Derzeit gibt es keine Uebungen");
*/

	$uebung_obj = new uebung($conn);
	$uebung_obj->load_uebung($lehreinheit_id,1);
	if(count($uebung_obj->uebungen)>0)
	{
		echo "<table width='100%'><tr><td valign='top'>";
		echo "<br>Wählen Sie bitte eine Aufgabe aus (Kreuzerllisten, Abgaben): <SELECT name='uebung' onChange=\"MM_jumpMenu('self',this,0)\">\n";
		echo "<option value='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=' selected></option>";
		foreach ($uebung_obj->uebungen as $row)
		{
			
			if($uebung_id == $row->uebung_id)
				$selected = 'selected';
			else
				$selected = '';		
					
			if($uebung_id=='')
				$uebung_id=$row->uebung_id;
			
			$subuebung_obj = new uebung($conn);
			$subuebung_obj->load_uebung($lehreinheit_id,2,$row->uebung_id);
			if(count($subuebung_obj->uebungen)>0)
				{
				$disabled = 'disabled';
				$selected = '';
				}
			else
				$disabled = '';
			
			echo "<OPTION style='background-color:#cccccc;' value='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id' $selected $disabled>";
			
			
			echo $row->bezeichnung;
			echo '</OPTION>';
			
			if(count($subuebung_obj->uebungen)>0)
			{
				foreach ($subuebung_obj->uebungen as $subrow)
				{
					if($uebung_id=='')
						$uebung_id=$subrow->uebung_id;
		
					if($uebung_id == $subrow->uebung_id)
					{				
						$selected = 'selected';
						if ($subrow->beispiele)	
							$show_excel_link = true;
					}
					else
					{						
						$selected = '';
					}
					
					echo "<OPTION value='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$subrow->uebung_id' $selected>";

					
					//Freigegeben = +
					//Nicht Freigegeben = -
					if($datum_obj->mktime_fromtimestamp($subrow->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($subrow->freigabebis)>time())
						echo ' + ';
					else
						echo ' - ';
					
					echo $subrow->bezeichnung;
					echo '</OPTION>';
					
				}
			}
		}
		
		echo '</SELECT>';
		
		echo '</td>';
		
		echo "<td>
			<table>
			<tr>
				<td><b>+</b>...</td>
				<td><u>freigeschaltet</u>.</td>
			</tr>
			<tr>
				<td><b>-</b>...</td>
				<td><u>nicht freigeschaltet</u>.</td>
			</tr>
			</table>
		</td>
	</tr></table>";
	}
	else
		die("Derzeit gibt es keine Uebungen");

$uebung_obj = new uebung($conn);
$uebung_obj->load($uebung_id);
echo "<h3><u>$uebung_obj->bezeichnung</u></h3>";

echo '<table width="100%"><tr><td>';
echo "<ul><li><a href='anwesenheitsliste.php?output=html&uebung_id=$uebung_id&lehreinheit_id=$lehreinheit_id&stsem=$stsem' target='_blank'>Alle Studierende</a>&nbsp;";
if ($show_excel_link)
	echo "<a href='anwesenheitsliste.php?output=xls&uebung_id=$uebung_id&lehreinheit_id=$lehreinheit_id'><img src='../../../../skin/images/excel.gif' width=16 height=16></a></li>";

$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$lehreinheit_id' ORDER BY semester, verband, gruppe, gruppe_kurzbz";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		echo "<li><a href='anwesenheitsliste.php?output=html&uebung_id=$uebung_id&gruppe=$row->lehreinheitgruppe_id&stsem=$stsem' target='_blank'>";

		if($row->gruppe_kurzbz=='')
			echo "Gruppe $row->verband$row->gruppe";
		else
			echo "$row->gruppe_kurzbz";

		if ($show_excel_link)		
			echo "</a>&nbsp;<a href='anwesenheitsliste.php?output=xls&uebung_id=$uebung_id&gruppe=$row->lehreinheitgruppe_id'><img src='../../../../skin/images/excel.gif' width=16 height=16></a></li>";
	}
}
echo '</ul>';

echo "</td><!--<td valign='top'>
<ul><li>
<a href='anwesenheitsliste.php?output=xls&lehreinheit_id=$lehreinheit_id&all'>Gesamt&uuml;bersicht&nbsp;<img src='../../../../skin/images/excel.gif' width=16 height=16></a>
</li></ul>
</td>--></tr></table>";

?>
</td></tr>
</table>
</body>
</html>