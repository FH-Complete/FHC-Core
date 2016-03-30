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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/basis_db.class.php');	
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/uebung.class.php');
require_once('../../../../include/beispiel.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/phrasen.class.php');

if (!$db = new basis_db())
		die('Fehler beim Herstellen der Datenbankverbindung');
			
$sprache = getSprache(); 
$p = new phrasen($sprache);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../../skin/style.css.php" rel="stylesheet" type="text/css">
<title><?php echo $p->t('benotungstool/benotungstool');?></title>
<script language="JavaScript" type="text/javascript">
<!--
	function MM_jumpMenu(targ, selObj, restore)
	{
	  eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");

	  if(restore)
	  {
	  	selObj.selectedIndex = 0;
	  }
	}
  //-->
</script>
</head>

<body>
<?php
$user = get_uid();

if(!check_lektor($user))
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(isset($_GET['lvid']) && is_numeric($_GET['lvid'])) //Lehrveranstaltung_id
	$lvid = $_GET['lvid'];
else
	die($p->t('global/fehlerBeiDerParameteruebergabe'));

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id'])) //Lehreinheit_id
	$lehreinheit_id = $_GET['lehreinheit_id'];
else
	$lehreinheit_id = '';

//Laden der Lehrveranstaltung
$lv_obj = new lehrveranstaltung();
if(!$lv_obj->load($lvid))
	die($lv_obj->errormsg);

//Studiengang laden
$stg_obj = new studiengang($lv_obj->studiengang_kz);

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';

//Vars
$datum_obj = new datum();
$show_excel_link = false;
$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');

//Kopfzeile
echo '<table width="100%">';
echo ' <tr><td>';
echo '<h1>&nbsp;'.$p->t('benotungstool/benotungstool');
echo '</h1></td><td align="right">'."\n";

//Studiensemester laden
$stsem_obj = new studiensemester();
if($stsem=='')
	$stsem = $stsem_obj->getaktorNext();

$stsem_obj->getAll();

//Studiensemester DropDown
$stsem_content = $p->t('global/studiensemester').": <SELECT name='stsem' onChange=\"MM_jumpMenu('self',this,0)\">\n";

foreach($stsem_obj->studiensemester as $studiensemester)
{
	$selected = ($stsem == $studiensemester->studiensemester_kurzbz?'selected':'');
	$stsem_content.= "<OPTION value='anwesenheitstabelle.php?lvid=$lvid&stsem=$studiensemester->studiensemester_kurzbz' $selected>$studiensemester->studiensemester_kurzbz</OPTION>\n";
}
$stsem_content.= "</SELECT>\n";

//Lehreinheiten laden
if($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('admin',$lv_obj->studiengang_kz) || $rechte->isBerechtigt('lehre',$lv_obj->studiengang_kz))
{
	$qry = "SELECT 
				distinct lehrfach.kurzbz as lfbez, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.lehrform_kurzbz as lehrform_kurzbz 
			FROM 
				lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach, lehre.tbl_lehreinheitmitarbeiter
			WHERE 
				tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND
				tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id AND
				tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id AND
				tbl_lehreinheit.studiensemester_kurzbz = ".$db->db_add_param($stsem);
}
else
{
	$qry = "SELECT 
				distinct lehrfach.kurzbz as lfbez, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.lehrform_kurzbz as lehrform_kurzbz 
			FROM 
				lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach, lehre.tbl_lehreinheitmitarbeiter
			WHERE 
				tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND
				tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id AND
				tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id AND
				tbl_lehreinheit.lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id) WHERE mitarbeiter_uid=".$db->db_add_param($user).") AND
				tbl_lehreinheit.studiensemester_kurzbz = ".$db->db_add_param($stsem);
}

if($result = $db->db_query($qry))
{
	if($db->db_num_rows($result)>1)
	{
		//Lehreinheiten DropDown
		echo $p->t('global/lehreinheit').": <SELECT name='lehreinheit_id' onChange=\"MM_jumpMenu('self',this,0)\">\n";
		while($row = $db->db_fetch_object($result))
		{
			if($lehreinheit_id=='')
				$lehreinheit_id=$row->lehreinheit_id;
			$selected = ($row->lehreinheit_id == $lehreinheit_id?'selected':'');
			$qry_lektoren = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE lehreinheit_id=".$db->db_add_param($row->lehreinheit_id, FHC_INTEGER);
			if($result_lektoren = $db->db_query($qry_lektoren))
			{
				$lektoren = '( ';
				$i=0;
				while($row_lektoren = $db->db_fetch_object($result_lektoren))
				{
					$lektoren .= $row_lektoren->kurzbz;
					$i++;
					if($i<$db->db_num_rows($result_lektoren))
						$lektoren.=', ';
					else
						$lektoren.=' ';
				}
				$lektoren .=')';
			}
			$qry_gruppen = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=".$db->db_add_param($row->lehreinheit_id, FHC_INTEGER);
			if($result_gruppen = $db->db_query($qry_gruppen))
			{
				$gruppen = '';
				$i=0;
				while($row_gruppen = $db->db_fetch_object($result_gruppen))
				{
					if($row_gruppen->gruppe_kurzbz=='')
						$gruppen.=$row_gruppen->semester.$row_gruppen->verband.$row_gruppen->gruppe;
					else
						$gruppen.=$row_gruppen->gruppe_kurzbz;
					$i++;
					if($i<$db->db_num_rows($result_gruppen))
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
		if($row = $db->db_fetch_object($result))
			$lehreinheit_id = $row->lehreinheit_id;
	}
}
else
{
	echo $p->t('benotungstool/fehlerBeimAuslesen');
}
echo $stsem_content;
echo '</td><tr></table>';
echo '<table width="100%"><tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
echo "<td>\n";
echo "<b>".$lv_obj->bezeichnung_arr[$sprache]."</b><br>";

if($lehreinheit_id=='')
	die($p->t('benotungstool/keinePassendeLehreinheitGefunden'));

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

echo "<h3>".$p->t('benotungstool/anwesenheitstabelle')."</h3>";

/*
$uebung_obj = new uebung();
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

	$uebung_obj = new uebung();
	$uebung_obj->load_uebung($lehreinheit_id,1);
	if(count($uebung_obj->uebungen)>0)
	{
		echo "<table width='100%'><tr><td valign='top'>";
		echo "<br>".$p->t('benotungstool/waehlenSieEineAufgabeAus').": <SELECT name='uebung' onChange=\"MM_jumpMenu('self',this,0)\">\n";
		echo "<option value='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=' selected></option>";
		foreach ($uebung_obj->uebungen as $row)
		{
			
			if($uebung_id == $row->uebung_id)
				$selected = 'selected';
			else
				$selected = '';		
					
			if($uebung_id=='')
				$uebung_id=$row->uebung_id;
			
			$subuebung_obj = new uebung();
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
				<td><u>".$p->t('benotungstool/freigeschaltet')."</u>.</td>
			</tr>
			<tr>
				<td><b>-</b>...</td>
				<td><u>".$p->t('benotungstool/nichtFreigeschaltet')."</u>.</td>
			</tr>
			</table>
		</td>
	</tr></table>";
	}
	else
		die($p->t('benotungstool/derzeitSindKeineUebungenAngelegt'));

$uebung_obj = new uebung();
$uebung_obj->load($uebung_id);
echo "<h3><u>$uebung_obj->bezeichnung</u></h3>";

echo '<table width="100%"><tr><td>';
echo "<ul><li><a href='anwesenheitsliste.php?output=html&uebung_id=$uebung_id&lehreinheit_id=$lehreinheit_id&stsem=$stsem' target='_blank'>".$p->t('benotungstool/alleStudierenden')."</a>&nbsp;";
if ($show_excel_link)
	echo "<a href='anwesenheitsliste.php?output=xls&uebung_id=$uebung_id&lehreinheit_id=$lehreinheit_id&stsem=$stsem'><img src='../../../../skin/images/excel.gif' width=16 height=16></a>";
echo "</li>";
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
