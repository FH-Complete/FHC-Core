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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
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
<title><?php echo $p->t('benotungstool/kreuzerltool');?></title>
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
	function confirmdelete()
	{
		return confirm('<?php echo $p->t('gesamtnote/wollenSieWirklichLoeschen');?>');
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

$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');

//Kopfzeile
echo '<table width="100%">';
echo ' <tr><td>';
echo '<h1>'.$p->t('benotungstool/benotungstool');
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
	$stsem_content.= "<OPTION value='statistik.php?lvid=$lvid&stsem=$studiensemester->studiensemester_kurzbz' $selected>$studiensemester->studiensemester_kurzbz</OPTION>\n";
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
				tbl_lehreinheit.lehrfach_id = lehrfach.lehrfach_id AND
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
			echo "<OPTION value='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->lfbez-$row->lehrform_kurzbz - $gruppen $lektoren</OPTION>\n";
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

echo "<h3>".$p->t('benotungstool/statistikFuerKreuzerllisten')."</h3>";
$uebung_obj = new uebung();
$uebung_obj->load_uebung($lehreinheit_id,1);
if(count($uebung_obj->uebungen)>0)
{
	echo "<table width='100%'><tr><td valign='top'>";
	echo "<br>".$p->t('benotungstool/waehlenSieEineKreuzerlliste').": <SELECT name='uebung' onChange=\"MM_jumpMenu('self',this,0)\">\n";
	echo "<option value='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=' selected></option>";
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
		
		echo "<OPTION style='background-color:#cccccc;' value='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id' $selected $disabled>";
		
		
		echo $row->bezeichnung;
		echo '</OPTION>';
		
		if(count($subuebung_obj->uebungen)>0)
		{
			foreach ($subuebung_obj->uebungen as $subrow)
			{
				//if($uebung_id=='')
				//	$uebung_id=$subrow->uebung_id;
	
				if($uebung_id == $subrow->uebung_id)
					$selected = 'selected';
				else
					$selected = '';
				if ($subrow->beispiele)
					echo "<OPTION value='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$subrow->uebung_id' $selected>";

				
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


echo "<br><br><br>";
if(isset($uebung_id) && $uebung_id!='')
{
	$beispiel_obj = new beispiel();
	if($beispiel_obj->load_beispiel($uebung_id))
	{
		if(count($beispiel_obj->beispiele)>0)
		{
			echo '<table border="0" cellpadding="0" cellspacing="0" width="600">
         		 <tr>
	           		 <td>&nbsp;</td>
	           		 <td height="19" width="339" valign="bottom">
		           		 <table border="0" cellpadding="0" cellspacing="0" width="339" background="../../../../skin/images/bg.gif">
		                	<tr>
		                  		<td>&nbsp;</td>
		                	</tr>
		              	</table>
		             </td>
          		</tr>';
			$i=0;
			$qry_cnt = "SELECT distinct student_uid FROM campus.tbl_studentbeispiel JOIN campus.tbl_beispiel USING(beispiel_id) WHERE uebung_id=".$db->db_add_param($uebung_id, FHC_INTEGER)." GROUP BY student_uid";
				if($result_cnt = $db->db_query($qry_cnt))
						$gesamt=$db->db_num_rows($result_cnt);

			foreach ($beispiel_obj->beispiele as $row)
			{
				$i++;
				$solved = 0;
				$psolved = 0;
				$qry_cnt = "SELECT count(*) as anzahl FROM campus.tbl_studentbeispiel WHERE beispiel_id=".$db->db_add_param($row->beispiel_id, FHC_INTEGER)." AND vorbereitet=true";
				if($result_cnt = $db->db_query($qry_cnt))
					if($row_cnt = $db->db_fetch_object($result_cnt))
						$solved = $row_cnt->anzahl;

				if($solved>0)
					$psolved = $solved/$gesamt*100;

				echo '<tr>
	            		<td '.($i%2?'class="MarkLine"':'').' valign="top" height="10" width="200"><font size="2" face="Arial, Helvetica, sans-serif">
	              			'.$row->bezeichnung.'
	              		</font></td>
						<td '.($i%2?'class="MarkLine"':'').'>
	            			<table width="339" border="0" cellpadding="0" cellspacing="0" background="../../../../skin/images/bg_.gif">
	                		<tr>
	                  			<td valign="top">
	                  				<table class="tabcontent">
	                      			<tr>
	                        			<td nowrap><font size="2" face="Arial, Helvetica, sans-serif">
	                        			<img src="../../../../skin/images/entry.gif" width="'.($psolved*3).'" height="5" alt="" border="1" />
	                        			<span class="smallb"><b>&nbsp;'.$solved.'</b> ['.number_format($psolved,1,'.','').'%]</span></font>
	                        			</td>
									</tr>
									</table>
								</td>
	                		</tr>
	              			</table>
						</td>
	          		</tr>';
			}
			echo "</table>";
			echo "<br><br>Es haben insgesamt <u>$gesamt Studenten</u> eingetragen.";
		}
	}
	else
		echo "<span class='error'>$beispiel_obj->errormsg</span>";
}

?>
</td></tr>
</table>
</body>
</html>
