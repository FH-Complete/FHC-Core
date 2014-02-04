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
require_once('../../../../include/studentnote.class.php');
require_once('../../../../include/datum.class.php');
require_once('functions.inc.php');
require_once('../../../../include/phrasen.class.php');
		
$sprache = getSprache(); 
$p = new phrasen($sprache); 
if (!$db = new basis_db())
		die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
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
$uid = (isset($_GET['uid'])?$_GET['uid']:'');

//Abgabedatei ausliefern
if (isset($_GET["download_abgabe"])){
	$file=$_GET["download_abgabe"];
	$uebung_id = $_GET["uebung_id"];
	$ueb = new uebung();
	$ueb->load_studentuebung($uid, $uebung_id);
	$ueb->load_abgabe($ueb->abgabe_id);
	$filename = BENOTUNGSTOOL_PATH."abgabe/".$ueb->abgabedatei;
	header('Content-Type: application/octet-stream');
	header('Content-disposition: attachment; filename="'.$file.'"');
	readfile($filename);
	exit;
}

//Handbuch ausliefern
if (isset($_GET["handbuch"])){
	$filename = BENOTUNGSTOOL_PATH."handbuch_benotungstool.pdf";
	header('Content-Type: application/octet-stream');
	header('Content-disposition: attachment; filename="handbuch_benotungstool.pdf"');
	readfile($filename);
	exit;
}


if (isset($_FILES["abgabedatei"]))
{
	//echo $_FILES["abgabedatei"];	
	$abgabedatei_up = $_FILES["abgabedatei"]["tmp_name"];
					
	if ($abgabedatei_up)
	{
		$student_uid = $uid;
		$datum = date('Y-m-d H:i:s');
		$datumstr = ereg_replace(" ","_",$datum);
		$name_up = pathinfo($_FILES["abgabedatei"]["name"]);
		$name_neu = makeUploadName($db, $which='abgabe', $lehreinheit_id=$lehreinheit_id, $uebung_id=$uebung_id, $ss=$stsem,$uid=$student_uid, $date=$datumstr);
		$abgabedatei = $name_neu.".".$name_up["extension"];
		$abgabepfad = BENOTUNGSTOOL_PATH."abgabe/".$abgabedatei;	
			
		$uebung_obj = new uebung();
		$uebung_obj->load_studentuebung($student_uid, $uebung_id);
	
			
		if ($uebung_obj->errormsg != "")
		{
			$uebung_obj->student_uid = $student_uid;
			$uebung_obj->mitarbeiter_uid = null;
			$uebung_obj->abgabe_id = null;
			$uebung_obj->uebung_id = $uebung_id;
			$uebung_obj->note = null;
			$uebung_obj->mitarbeitspunkte = null;
			$uebung_obj->punkte = null;
			$uebung_obj->anmerkung = null;
			$uebung_obj->benotungsdatum = null;
			$uebung_obj->updateamum = null;
			$uebung_obj->updatevon = null;
			$uebung_obj->insertamum = $datum;
			$uebung_obj->insertvon = $user;
			$uebung_obj->new = true;
			$uebung_obj->studentuebung_save($new=true);
			echo $uebung_obj->errormsg;
			
		}
		if ($uebung_obj->abgabe_id != null)
		{			
			$uebung_obj->load_abgabe($uebung_obj->abgabe_id);			
			unlink(BENOTUNGSTOOL_PATH."abgabe/".$uebung_obj->abgabedatei);			
			$uebung_obj->abgabedatei = $abgabedatei;
			$uebung_obj->abgabezeit = 	$datum;
			$uebung_obj->abgabe_anmerkung = "";
			$uebung_obj->abgabe_save(false);
		}
		else
		{
			$uebung_obj->abgabedatei = $abgabedatei;
			$uebung_obj->abgabezeit = 	$datum;
			$uebung_obj->abgabe_anmerkung = "";
			$uebung_obj->abgabe_save(true);
		}
		$uebung_obj->studentuebung_save(false);
		//Abgabedatei ablegen				
		move_uploaded_file($_FILES['abgabedatei']['tmp_name'], $abgabepfad);
	}
}
else
	$abgabedatei_up = null;

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
	function confirmdelete()
	{
		return confirm('<?php echo $p->t('gesamtnote/wollenSieWirklichLoeschen');?>');
	}
  //-->
</script>
</head>

<body>
<?php


//Kopfzeile
echo '<table width="100%"><tr><td><h1>'.$p->t('benotungstool/benotungstool');
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
	$stsem_content.= "<OPTION value='studentenpunkteverwalten.php?lvid=$lvid&stsem=$studiensemester->studiensemester_kurzbz' $selected>$studiensemester->studiensemester_kurzbz</OPTION>\n";
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
	if($db->db_num_rows($result)>0)
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
			echo "<OPTION value='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->lfbez-$row->lehrform_kurzbz - $gruppen $lektoren</OPTION>\n";
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
echo "<br><a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Verwaltung</font>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Anwesenheits- und Übersichtstabelle</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Studentenpunkte verwalten</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Statistik</font></a>
<br><br>
<!--Menue Ende-->\n";
*/

echo "<h3>".$p->t('benotungstool/studentenaufgabenVerwalten')."</h3>";
if(isset($_POST['submit']))
{
	$error=false;
	$punkte = (isset($_POST['punkte'])?mb_ereg_replace(',','.',$_POST['punkte']):'');
	if(isset($punkte) && is_numeric($punkte) && !isset($_POST['abgabe']))
	{
		$ueb_obj = new uebung();
		if($ueb_obj->load_studentuebung($uid, $uebung_id))
			$ueb_obj->new = false;
		else
		{
			$ueb_obj->new = true;
			$ueb_obj->insertamum = date('Y-m-d H:i:s');
			$ueb_obj->insertvon = $user;
		}

		$ueb_obj->mitarbeitspunkte = $punkte;
		$ueb_obj->anmerkung = $_POST['anmerkung'];
		$ueb_obj->updateamum = date('Y-m-d H:i:s');
		$ueb_obj->updatevon = $user;
		$ueb_obj->mitarbeiter_uid = $user;
		$ueb_obj->uebung_id = $uebung_id;
		$ueb_obj->student_uid = $uid;

		if(!$ueb_obj->studentuebung_save())
			$error = true;

		$bsp_obj = new beispiel();

		if($bsp_obj->load_beispiel($uebung_id))
		{
			foreach ($bsp_obj->beispiele as $row)
			{
					$stud_bsp_obj = new beispiel();

					if($stud_bsp_obj->load_studentbeispiel($uid, $row->beispiel_id))
					{
						$stud_bsp_obj->new=false;
					}
					else
					{
						$stud_bsp_obj->new=true;
						$stud_bsp_obj->insertamum = date('Y-m-d H:i:s');
						$stud_bsp_obj->insertvon = $user;
					}
					$stud_bsp_obj->vorbereitet = ($_POST['solved_'.$row->beispiel_id]==1?true:false);
					$stud_bsp_obj->probleme = (isset($_POST['problem_'.$row->beispiel_id])?true:false);
					$stud_bsp_obj->updateamum = date('Y-m-d H:i:s');
					$stud_bsp_obj->updatevon = $user;
					$stud_bsp_obj->student_uid = $uid;
					$stud_bsp_obj->beispiel_id = $row->beispiel_id;

					if(!$stud_bsp_obj->studentbeispiel_save())
					{
						echo $stud_bsp_obj->errormsg;
						$error=true;
					}
			}
		}

		if($error)
			echo "<span class='error'>".$p->t('benotungstool/esKonntenNichtAlleDatenGespeichertWerden')."</span>";
		else
			echo $p->t('global/erfolgreichgespeichert')."<br>";

	}
	else if (!isset($_POST['abgabe']))
	{
		echo "<span class='error'>".$p->t('benotungstool/punkteSindUngueltig')."</span>";
	}
	if(isset($_POST['abgabe']) && is_numeric($_POST['note']))
	{
		$note = $_POST['note'];
		$ueb_obj = new uebung();
		if($ueb_obj->load_studentuebung($uid, $uebung_id))
			$ueb_obj->new = false;
		else
		{
			$ueb_obj->new = true;
			$ueb_obj->insertamum = date('Y-m-d H:i:s');
			$ueb_obj->insertvon = $user;
		}

		$ueb_obj->note = $note;
		$ueb_obj->anmerkung = $_POST['anmerkung'];
		$ueb_obj->updateamum = date('Y-m-d H:i:s');
		$ueb_obj->updatevon = $user;
		$ueb_obj->mitarbeiter_uid = $user;
		$ueb_obj->uebung_id = $uebung_id;
		$ueb_obj->student_uid = $uid;

		if(!$ueb_obj->studentuebung_save())
			$error = true;
		if($error)
			echo "<span class='error'>".$p->t('benotungstool/esKonntenNichtAlleDatenGespeichertWerden')."</span>";
		else
			echo $p->t('global/erfolgreichgespeichert')."<br>";
	}
	else if (isset($_POST['abgabe']))
		echo "<span class='error'>".$p->t('benotungstool/noteIstUngueltig')."<br></span>";
}

if(isset($_GET['uid']) && $_GET['uid']!='')
{
	//Punkte eintragen
	$uid = addslashes($_GET['uid']);

	$qry_stud = "SELECT vorname, nachname, uid FROM campus.vw_student WHERE uid='$uid'";

	if(!$result_stud = $db->db_query($qry_stud))
		die($p->t('benotungstool/fehlerBeimLadenDesStudenten'));

	if(!$row_stud = $db->db_fetch_object($result_stud))
		die($p->t('benotungstool/studentWurdeNichtGefunden'));

	//echo "<b>$row_stud->vorname $row_stud->nachname</b><br>\n";
	

	
	$uid_arr = Array();
	$vorname_arr = Array();
	$nachname_arr = Array();

			// studentenquery					
			$qry_stud_dd = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student_lehrveranstaltung JOIN campus.vw_student using(uid) WHERE  studiensemester_kurzbz = ".$db->db_add_param($stsem)." and lehreinheit_id = ".$db->db_add_param($lehreinheit_id, FHC_INTEGER)."  ORDER BY nachname, vorname";
            if($result_stud_dd = $db->db_query($qry_stud_dd))
			{
				$i=1;
				while($row_stud_dd = $db->db_fetch_object($result_stud_dd))
				{
					$uid_arr[] = $row_stud_dd->uid;
					$vorname_arr[] = $row_stud_dd->vorname;
					$nachname_arr[] = $row_stud_dd->nachname;				

				}
			}
//		}
//	}
	echo $p->t('benotungstool/studentenAuswaehlen').": ";
	$key = array_search($uid,$uid_arr);
	$prev = $key-1;
	$next = $key+1;
	if ($key > 0)
		echo "<a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid_arr[$prev]&stsem=$stsem'> &lt;&lt; </a>";	
	echo "<SELECT name='stud_dd' onChange=\"MM_jumpMenu('self',this,0)\">\n";	
	for ($j = 0; $j < count($uid_arr); $j++)
	{						
			if ($uid_arr[$j] == $uid)
				$selected = " selected";
			else
				$selected = "";
		
			echo "<option value='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid_arr[$j]&stsem=$stsem'$selected>$vorname_arr[$j] $nachname_arr[$j]</option>";
	}
	echo "</select>";
	if ($key < count($uid_arr)-1)
		echo "<a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid_arr[$next]&stsem=$stsem'> &gt;&gt; </a>";	
	
	
	$uebung_obj = new uebung();
	$uebung_obj->load_uebung($lehreinheit_id,1);
	if(count($uebung_obj->uebungen)>0)
	{
		echo "<table width='100%'><tr><td valign='top'>";
		echo "<br>".$p->t('benotungstool/waehlenSieEineAufgabeAus').": <SELECT name='uebung' onChange=\"MM_jumpMenu('self',this,0)\">\n";
		echo "<option value='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=&uid=$uid' selected></option>";
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
			
			echo "<OPTION style='background-color:#cccccc;' value='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id&uid=$uid' $selected $disabled>";
			
			
			echo $row->bezeichnung;
			echo '</OPTION>';
			
			if(count($subuebung_obj->uebungen)>0)
			{
				foreach ($subuebung_obj->uebungen as $subrow)
				{
					if($uebung_id=='')
						$uebung_id=$subrow->uebung_id;
		
					if($uebung_id == $subrow->uebung_id)
						$selected = 'selected';
					else
						$selected = '';
					
					echo "<OPTION value='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$subrow->uebung_id&uid=$uid' $selected>";

					
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

	$ueb_obj = new uebung();
	$ueb_obj->load($uebung_id);
	if($ueb_obj->load_studentuebung($uid, $uebung_id))
	{
		$anmerkung = $ueb_obj->anmerkung;
		$mitarbeit = $ueb_obj->mitarbeitspunkte;
		$note = $ueb_obj->note;
	}
	else
	{
		$anmerkung = '';
		$mitarbeit = 0;
		$note = '';
	}
	
	if ($ueb_obj->beispiele && is_numeric($_GET['uebung_id']))
	{
		echo "
		<form accept-charset='UTF-8' method='POST' action='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid'>
		<table width='100%'><tr><td valign='top'>
		".$p->t('global/anmerkung').":<br>
		<textarea name='anmerkung' cols=50 rows=5>".$anmerkung."</textarea>
		<br><br>
		<table border='1'>
		<tr>
			<td class='ContentHeader2'>".$p->t('benotungstool/beispiel')."</td>
		    <td class='ContentHeader2'>".$p->t('benotungstool/vorbereitet')."</td>
		    <td class='ContentHeader2'>".$p->t('benotungstool/nichtVorbereitet')."</td>
		    <td class='ContentHeader2'>".$p->t('benotungstool/probleme')."</td>
		    <td class='ContentHeader2'>".$p->t('benotungstool/punkte')."</td>
		</tr>";
	
		$bsp_obj = new beispiel();
		$bsp_obj->load_beispiel($uebung_id);
	
		foreach ($bsp_obj->beispiele as $row)
		{
			$stud_bsp_obj = new beispiel();
			if($stud_bsp_obj->load_studentbeispiel($uid, $row->beispiel_id))
			{
				$vorbereitet = $stud_bsp_obj->vorbereitet;
				$probleme = $stud_bsp_obj->probleme;
			}
			else
			{
				$vorbereitet = false;
				$probleme = false;
			}
			echo "<tr>
				<td>$row->bezeichnung</td>
					<td align='center'><input type='radio' name='solved_$row->beispiel_id' value='1' ".($vorbereitet?'checked':'')."></td>
					<td align='center'><input type='radio' name='solved_$row->beispiel_id' value='0' ".(!$vorbereitet?'checked':'')."></td>
					<td align='center'><input type='checkbox' name='problem_$row->beispiel_id' ".($probleme?'checked':'')."></td>
					<td align='center'>$row->punkte</td>
				</tr>";
		}
	
		echo "</table>";
		
		
		
		$ueb_obj->load_studentuebung($uid, $uebung_id);
		if ($ueb_obj->abgabe_id)	
		{	
			$ueb_obj->load_abgabe($ueb_obj->abgabe_id);
			$filename = $ueb_obj->abgabedatei;
		}
		else
			$filename='';

		if ($filename != '')
			echo "<br>".$p->t('benotungstool/abgabedatei').": <a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid&download_abgabe=$filename'>".$filename."</a><br><br>";
		

		
		echo "
		</td><td valign='top' algin='right'>";
	
		//Gesamtpunkte diese Kreuzerlliste
		$qry = "SELECT sum(punkte) as punktegesamt FROM campus.tbl_beispiel WHERE uebung_id=".$db->db_add_param($uebung_id, FHC_INTEGER);
		$punkte_gesamt=0;
		if($result=$db->db_query($qry))
			if($row = $db->db_fetch_object($result))
				$punkte_gesamt = $row->punktegesamt;
	
		//Eingetragen diese Kreuzerlliste
		$qry = "SELECT sum(punkte) as punkteeingetragen FROM campus.tbl_beispiel JOIN campus.tbl_studentbeispiel USING(beispiel_id) WHERE uebung_id=".$db->db_add_param($uebung_id, FHC_INTEGER)." AND student_uid=".$db->db_add_param($uid)." AND vorbereitet=true";
		$punkte_eingetragen=0;
		if($result=$db->db_query($qry))
			if($row = $db->db_fetch_object($result))
				$punkte_eingetragen = ($row->punkteeingetragen!=''?$row->punkteeingetragen:0);
	
		
		//Gesamtpunkte alle Kreuzerllisten in dieser Übung
		$ueb_help = new uebung($uebung_id);
		$liste_id = $ueb_help->liste_id;
		$qry = "SELECT sum(tbl_beispiel.punkte) as punktegesamt_alle FROM campus.tbl_beispiel, campus.tbl_uebung
				WHERE tbl_uebung.uebung_id=tbl_beispiel.uebung_id AND
				tbl_uebung.lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER)." and tbl_uebung.liste_id = ".$db->db_add_param($liste_id, FHC_INTEGER);
		$punkte_gesamt_alle=0;
		if($result=$db->db_query($qry))
			if($row = $db->db_fetch_object($result))
				$punkte_gesamt_alle = $row->punktegesamt_alle;
	
		//Eingetragen alle Kreuzerllisten
		$qry = "SELECT sum(tbl_beispiel.punkte) as punkteeingetragen_alle FROM campus.tbl_beispiel, campus.tbl_studentbeispiel, campus.tbl_uebung
				WHERE tbl_beispiel.beispiel_id = tbl_studentbeispiel.beispiel_id AND
				tbl_uebung.uebung_id=tbl_beispiel.uebung_id AND
				tbl_uebung.lehreinheit_id=".$db->db_add_param($lehreinheit_id)." AND
				tbl_uebung.liste_id = ".$db->db_add_param($liste_id)." AND 
				tbl_studentbeispiel.student_uid=".$db->db_add_param($uid)." AND vorbereitet=true";
		$punkte_eingetragen_alle=0;
		if($result=$db->db_query($qry))
			if($row = $db->db_fetch_object($result))
				$punkte_eingetragen_alle = ($row->punkteeingetragen_alle!=''?$row->punkteeingetragen_alle:0);
	
		//Mitarbeitspunkte
		$qry = "SELECT sum(mitarbeitspunkte) as mitarbeitspunkte FROM campus.tbl_studentuebung JOIN campus.tbl_uebung USING(uebung_id)
				WHERE lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER)." AND student_uid=".$db->db_add_param($uid)." and liste_id=".$db->db_add_param($liste_id);
		$mitarbeit_alle=0;
		if($result=$db->db_query($qry))
			if($row = $db->db_fetch_object($result))
				$mitarbeit_alle = ($row->mitarbeitspunkte!=''?$row->mitarbeitspunkte:0);
	
		//Mitarbeitspunkte
		$qry = "SELECT mitarbeitspunkte FROM campus.tbl_studentuebung
				WHERE uebung_id=".$db->db_add_param($uebung_id, FHC_INTEGER)." AND student_uid=".$db->db_add_param($uid);
		$mitarbeit=0;
		if($result=$db->db_query($qry))
			if($row = $db->db_fetch_object($result))
				$mitarbeit = ($row->mitarbeitspunkte!=''?$row->mitarbeitspunkte:0);
		echo "
		<br>
			<table border='1' width='210'>
			<tr>
				<td colspan='2' class='ContentHeader2'>Diese Kreuzerlliste:</td>
			</tr>
			<tr>
				<td width='180'>".$p->t('benotungstool/punkteInsgesamtMoeglich').":</td>
				<td width='30'>$punkte_gesamt</td>
			</tr>
			<tr>
				<td>".$p->t('benotungstool/punkteEingetragen').":</td>
				<td>$punkte_eingetragen</td>
			</tr>
			</table>
			<br><br>
			<table border='1' width='210'>
			<tr>
				<td colspan='2' class='ContentHeader2'>".$p->t('benotungstool/alleKreuzerllistenDieserUebung').":</td>
			</tr>
			<tr>
				<td width='180'>".$p->t('benotungstool/punkteInsgesamtMoeglich').":</td>
				<td width='30'>$punkte_gesamt_alle</td>
			</tr>
			<tr>
				<td>".$p->t('benotungstool/punkteEingetragen').":</td>
				<td>$punkte_eingetragen_alle</td>
			</tr>
			</table>
			<br><br>
			<table border='1' width='210'>
			<tr>
				<td colspan='2' class='ContentHeader2'>".$p->t('benotungstool/mitarbeitspunkte').":</td>
			</tr>
			<tr>
				<td width='180'>".$p->t('benotungstool/bisherInsgesamt').":</td>
				<td width='30'>$mitarbeit_alle</td>
			</tr>
			<tr>
				<td>".$p->t('benotungstool/dieseKreuzerlliste').":</td>
				<td><input type='text' size=2 name='punkte' value='$mitarbeit'></td>
			</tr>
			</table>
			";
	
	
		echo "
		</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<input type='button' value='".$p->t('global/zurueck')."' onclick='history.back();'>
				<input type='submit' value='".$p->t('global/speichern')."' name='submit'>
			</td>
	
		</tr>
		</table>
	
		</form>
		";
	}
	else if (is_numeric($_GET['uebung_id']))
	{
		$ueb_obj->load_studentuebung($uid, $uebung_id);
		if ($ueb_obj->abgabe_id)	
		{	
			$ueb_obj->load_abgabe($ueb_obj->abgabe_id);
			$filename = $ueb_obj->abgabedatei;
		}
		else
			$filename='';
		//Abgaben benoten
		$studentnote = new studentnote($lehreinheit_id,$stsem,$uid,$uebung_id);
		$studentnote->calc_note($uebung_id, $uid);
		echo "<span class='studentnote'>".$p->t('benotungstool/note').": ".$studentnote->note." (Gewicht: ".$ueb_obj->gewicht.")</span><br><br>";
		if ($filename != '')
			echo $p->t('benotungstool/abgabedatei').": <a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid&download_abgabe=$filename'>".$filename."</a><br><br>";
		echo "
		<form accept-charset='UTF-8' method='POST' action='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid'>
		<table width='100%'><tr><td valign='top'>
		".$p->t('global/anmerkung').":<br>
		<textarea name='anmerkung' cols=50 rows=5>".$anmerkung."</textarea>
		</td><td>
		<table border='1'>
		<tr>
			<td class='ContentHeader2'>Note</td>
			<td><input type='text' name='note' value='$note'><input type='hidden' name='abgabe' value='1'></td>
		</tr>";
		echo "
		<tr>
			<td colspan='2'>
				<input type='button' value='".$p->t('global/zurueck')."' onclick='history.back();'>
				<input type='submit' value='".$p->t('global/speichern')."' name='submit'>	
			</td>
	
		</tr>
		</table>
		</form>";

	}
	echo "</td></tr></table>";	
	echo "<table>\n";		
	echo "	<tr>\n";
	echo "	<form accept-charset='UTF-8' method='POST' action='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem&uid=$uid' enctype='multipart/form-data'>\n";
	echo "		<td>\n";
	echo "			<b>".$p->t('benotungstool/studentenabgabedatei').":</b><br><input type='file' name='abgabedatei'> <input type='submit' name='abgabe' value='".$p->t('benotungstool/abgeben')."'>";
	echo "		</td>\n";	
	echo "	</form>\n";
	echo "</tr>\n";
	echo "</table>";
}
else
{
	
	//Übungen benoten
	$uebung_obj = new uebung();
	$uebung_obj->load_uebung($lehreinheit_id,1);
	if(count($uebung_obj->uebungen)>0)
	{
		echo "<table width='100%'><tr><td valign='top'>";
		echo "<br>".$p->t('benotungstool/aufgabeKreuzerllistenAbgaben').": <SELECT name='uebung' onChange=\"MM_jumpMenu('self',this,0)\">\n";
		echo "<option value='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=&uid=$uid' selected></option>";
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
			
			echo "<OPTION style='background-color:#cccccc;' value='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id&uid=$uid' $selected $disabled>";
			
			
			echo $row->bezeichnung;
			echo '</OPTION>';
			
			if(count($subuebung_obj->uebungen)>0)
			{
				foreach ($subuebung_obj->uebungen as $subrow)
				{
					if($uebung_id=='')
						$uebung_id=$subrow->uebung_id;
		
					if($uebung_id == $subrow->uebung_id)
						$selected = 'selected';
					else
						$selected = '';
					
					echo "<OPTION value='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$subrow->uebung_id&uid=$uid' $selected>";

					
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
		echo "<a href='anwesenheitsliste.php?output=html&uebung_id=$uebung_id&lehreinheit_id=$lehreinheit_id&stsem=$stsem' target='_blank'> [".$p->t('benotungstool/benoten')."]</a>";
		$abgabe_obj = new uebung($uebung_id);
		if ($abgabe_obj->abgabe && glob(BENOTUNGSTOOL_PATH."abgabe/*_[WS]S[0-9][0-9][0-9][0-9]_".$uebung_id."_*"))
		{
		   $date = date('Y-m-d_H:i:s');
			$downloadname = makeUploadName($db, $which="zip", $lehreinheit_id, $uebung_id, $stsem, $uid=null, $date);
			$downloadname = mb_ereg_replace($uebung_id, ereg_replace(" ","_",$abgabe_obj->bezeichnung), $downloadname);
			echo "<a href='zipdownload_benotungstool.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&downloadname=$downloadname'> [".$p->t('benotungstool/abgabenDownloaden')."]</a>";
		}
		else
			echo "[".$p->t('benotungstool/keineAbgabenVerfuegbar')."]";
		
		echo '</td></tr></table>';
	}
	
	
	
	
	echo "<br><hr><br>";
	//Studentenliste
	echo $p->t('benotungstool/bitteWaehlenSieDenStudentenAus')."<br>";
	echo "
	<table width='80%'>
	";

			echo "<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					
				</tr>
				<tr>
					<td class='ContentHeader2'>".$p->t('global/uid')."</td>
					<td class='ContentHeader2'>".$p->t('global/nachname')."</td>
					<td class='ContentHeader2'>".$p->t('global/vorname')."</td>
					<td class='ContentHeader2'>".$p->t('benotungstool/studentenansicht')."</td>
					
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>";
			/*			
			if($row_grp->gruppe_kurzbz!='')
			{
					echo "
					<tr>
						<td colspan='4' align='center'><b>$row_grp->gruppe_kurzbz</b></td>
					</tr>";
					$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student JOIN public.tbl_benutzergruppe USING(uid) WHERE gruppe_kurzbz='".addslashes($row_grp->gruppe_kurzbz)."' AND studiensemester_kurzbz = '".$stsem."' ORDER BY nachname, vorname";
			}
			else
			{
				echo "
					<tr>
						<td colspan='4' align='center'><b>Verband $row_grp->verband ".($row_grp->gruppe!=''?"Gruppe $row_grp->gruppe":'')."</b></td>
					</tr>";
					$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student
					             WHERE studiengang_kz='$row_grp->studiengang_kz' AND
					             semester='$row_grp->semester' ".
								 ($row_grp->verband!=''?" AND trim(verband)=trim('$row_grp->verband')":'').
								 ($row_grp->gruppe!=''?" AND trim(gruppe)=trim('$row_grp->gruppe')":'').
					            " ORDER BY nachname, vorname";
			}
			*/
			// studentenquery		
			$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student_lehrveranstaltung JOIN campus.vw_student using(uid) WHERE  studiensemester_kurzbz = '".$stsem."' and lehreinheit_id = '".$lehreinheit_id."' ORDER BY nachname, vorname";
            if($result_stud = $db->db_query($qry_stud))
			{
				$i=1;
				while($row_stud = $db->db_fetch_object($result_stud))
				{
        			

					
					echo "
					<tr class='liste".($i%2)."'>
						<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->uid</a></td>
						<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->nachname</a></td>
						<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->vorname</a></td>
						<td><a href='studentenansicht.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item' target='_blank'>".$p->t('benotungstool/studentenansicht')."</a></td>
					</tr>";
					$i++;
				}
			}
//		}
//	}
	echo "</table>";
}
?>
</td></tr>
</table>
</body>
</html>
