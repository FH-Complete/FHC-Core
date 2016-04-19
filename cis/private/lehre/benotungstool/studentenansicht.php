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
// ********************
// * Studentenansicht fuers Kreuzerltool
// ********************

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
require_once('../../../../include/studentnote.class.php');
require_once('../../../../include/legesamtnote.class.php');
require_once('../../../../include/lvgesamtnote.class.php');
require_once('../../../../include/zeugnisnote.class.php');
require_once('../../../../include/phrasen.class.php');
include('functions.inc.php');

$sprache = getSprache(); 
$p = new phrasen($sprache); 

if (!$db = new basis_db())
		die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
$user = get_uid();
//$user = 'if06b172';
//$user = 'if06b144';
$lektorenansicht = 0;

if(isset($_GET['lvid']) && is_numeric($_GET['lvid'])) //Lehrveranstaltung_id
	$lehrveranstaltung_id = $_GET['lvid'];
else
	die($p->t('global/fehlerBeiDerParameteruebergabe'));

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id'])) //Lehreinheit_id
	$lehreinheit_id = $_GET['lehreinheit_id'];
else
	$lehreinheit_id = '';
$uid = (isset($_GET['uid'])?$_GET['uid']:''); //Uid

	
	
if(check_lektor($user) && (isset($_GET['uid']) && $_GET["uid"] != ""))
{
	$rights = new benutzerberechtigung();
	$rights->getBerechtigungen($user); 
	$lehreinheit=new lehreinheit($_GET["lehreinheit_id"]);
	if(!check_lektor_lehrveranstaltung($user, $lehreinheit->lehrveranstaltung_id, $lehreinheit->studiensemester_kurzbz) && !$rights->isBerechtigt('admin',0))
			die($p->t('global/keineBerechtigungFuerDieseSeite'));
	$lektorenansicht = 1;
	$user = $_GET["uid"];
}

//Laden der Lehrveranstaltung
$lv_obj = new lehrveranstaltung();
if(!$lv_obj->load($lehrveranstaltung_id))
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

//Abgabedatei ausliefern
if (isset($_GET["download_abgabe"]))
{

	$file=$_GET["download_abgabe"];
	$uebung_id = $_GET["uebung_id"];
	$ueb = new uebung();
	$ueb->load_studentuebung($user, $uebung_id);
	$ueb->load_abgabe($ueb->abgabe_id);
	$filename = BENOTUNGSTOOL_PATH."abgabe/".$ueb->abgabedatei;
	header('Content-Type: application/octet-stream');
	header('Content-disposition: attachment; filename="'.$file.'"');
	readfile($filename);
	exit;
}

//Angabedatei ausliefern
if (isset($_GET["download"])){
	$file=$_GET["download"];
	$uebung_id = $_GET["uebung_id"];
	$ueb = new uebung();
	$ueb->load($uebung_id);
	$filename = BENOTUNGSTOOL_PATH."angabe/".$ueb->angabedatei;
	header('Content-Type: application/octet-stream');
	header('Content-disposition: attachment; filename="'.$file.'"');
	readfile($filename);
	exit;
}

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
  //-->
</script>
</head>

<body>


<?php

if (isset($_REQUEST["deleteabgabe"]))
{
	$ueb = new uebung();
	$ueb->load_studentuebung($user, $uebung_id);
	if (!$ueb->delete_abgabe($ueb->abgabe_id))
		echo $ueb->errormsg;

}
//echo $_FILES["abgabedatei"];
//if (isset($_FILES["abgabedatei"]))
if (isset($_POST["abgabe"]))
{
	$abgabedatei_up = $_FILES["abgabedatei"]["tmp_name"];
	$abgabe_anmerkung = (isset($_POST["abgabe_anmerkung"])?$_POST["abgabe_anmerkung"]:'');
	
	if ($abgabedatei_up)
	{
		//echo $abgabedatei_up;
		$datum = date('Y-m-d H:i:s');
		$datumstr = ereg_replace(" ","_",$datum);
		$name_up = pathinfo($_FILES["abgabedatei"]["name"]);
		$name_neu = makeUploadName($db, $which='abgabe', $lehreinheit_id=$lehreinheit_id, $uebung_id=$uebung_id, $ss=$stsem,$uid=$user, $date=$datumstr);
		$abgabedatei = $name_neu.".".$name_up["extension"];
		$abgabepfad = BENOTUNGSTOOL_PATH."abgabe/".$abgabedatei;	
			
		$uebung_obj = new uebung();


		$uebung_obj->load_studentuebung($user, $uebung_id);

		if ($uebung_obj->errormsg != "")
		{
			$uebung_obj->uid = $user;
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
			//echo $uebung_obj->errormsg;
			
		}
		if ($uebung_obj->abgabe_id != null)
		{			
			$uebung_obj->load_abgabe($uebung_obj->abgabe_id);			
			unlink(BENOTUNGSTOOL_PATH."abgabe/".$uebung_obj->abgabedatei);			
			$uebung_obj->abgabedatei = $abgabedatei;
			$uebung_obj->abgabezeit = 	$datum;
			$uebung_obj->abgabe_anmerkung = $abgabe_anmerkung;
			$uebung_obj->abgabe_save(false);
		}
		else
		{
			$uebung_obj->abgabedatei = $abgabedatei;
			$uebung_obj->abgabezeit = 	$datum;
			$uebung_obj->abgabe_anmerkung = $abgabe_anmerkung;
			$uebung_obj->abgabe_save(true);
		}
		$uebung_obj->studentuebung_save(false);
		//Abgabedatei ablegen				
		move_uploaded_file($_FILES['abgabedatei']['tmp_name'], $abgabepfad);
	}
	
	else
	{
		$abgabe_anmerkung = $_POST["abgabe_anmerkung"];
		$uebung_obj2 = new uebung();
		$uebung_obj2->load_studentuebung($user, $uebung_id);
		if ($uebung_obj2->errormsg == "")
		{
			if ($uebung_obj2->abgabe_id != null)
			{	
				$uebung_obj2->load_abgabe($uebung_obj2->abgabe_id);
				$uebung_obj2->abgabe_anmerkung = $abgabe_anmerkung;
				$uebung_obj2->abgabe_save(false);
			}
		}
	}
}
else
	$abgabedatei_up = null;




//Kopfzeile
echo '<table width="100%">';
echo ' <tr><td>';
echo '<h1>'.$p->t('benotungstool/benotungstool');
echo '</h1></td><td align="right">'."\n";

//Studiensemester laden
$stsem_obj = new studiensemester();
if($stsem=='')
	$stsem = $stsem_obj->getaktorNext();

//Lehreinheiten laden zu denen der eingeloggte Student zugeteilt ist
//Bei Lehrverbaenden werden auch die uebergeordneten geladen
$qry = "SELECT distinct lehreinheit_id, lehrfach.kurzbz FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) WHERE lehreinheit_id IN(
		SELECT lehreinheit_id FROM public.tbl_benutzergruppe JOIN lehre.tbl_lehreinheitgruppe USING (gruppe_kurzbz)
		WHERE tbl_benutzergruppe.uid=".$db->db_add_param($user)." AND
		tbl_lehreinheitgruppe.lehreinheit_id IN(
			SELECT lehreinheit_id FROM lehre.tbl_lehreinheit JOIN campus.tbl_uebung USING(lehreinheit_id)
			WHERE tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER)." AND tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem).")
		UNION
		SELECT 
			lehreinheit_id 
		FROM 
			public.tbl_student, lehre.tbl_lehreinheitgruppe, public.tbl_studentlehrverband
		WHERE 
			tbl_student.student_uid=".$db->db_add_param($user)." AND
			tbl_studentlehrverband.prestudent_id=tbl_student.prestudent_id AND
			tbl_studentlehrverband.studiensemester_kurzbz=".$db->db_add_param($stsem)." AND
			tbl_student.studiengang_kz=tbl_lehreinheitgruppe.studiengang_kz AND
			tbl_lehreinheitgruppe.gruppe_kurzbz is null AND
			tbl_studentlehrverband.semester=tbl_lehreinheitgruppe.semester AND
			(
				(
				  (
				  tbl_lehreinheitgruppe.verband<>'' AND
				  tbl_lehreinheitgruppe.gruppe<>'' AND
				  trim(tbl_lehreinheitgruppe.verband) = trim(tbl_studentlehrverband.verband) AND
				  trim(tbl_lehreinheitgruppe.gruppe) = trim(tbl_studentlehrverband.gruppe)
				  )
				  OR
				  (
				    tbl_lehreinheitgruppe.verband<>'' AND
				  	(
				  	trim(tbl_lehreinheitgruppe.gruppe)='' OR
				  	tbl_lehreinheitgruppe.gruppe is null
				  	)
				  	AND
				  	trim(tbl_lehreinheitgruppe.verband) = trim(tbl_studentlehrverband.verband)
				  )
				  OR
				  (
					(trim(tbl_lehreinheitgruppe.verband)='' OR tbl_lehreinheitgruppe.verband is null)
					 AND
					 (trim(tbl_lehreinheitgruppe.gruppe)='' OR tbl_lehreinheitgruppe.gruppe is null)
				  )
				)
			)
			AND
			tbl_lehreinheitgruppe.lehreinheit_id IN(SELECT lehreinheit_id FROM lehre.tbl_lehreinheit JOIN campus.tbl_uebung USING(lehreinheit_id)
				WHERE tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER)." AND tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem)."))";

if($result = $db->db_query($qry))
{
	if($db->db_num_rows($result)>1)
	{
		//Lehreinheiten DropDown
		echo $p->t('global/lehreinheit')." : <SELECT name='lehreinheit_id' onChange=\"MM_jumpMenu('self',this,0)\">\n";
		while($row = $db->db_fetch_object($result))
		{
			if($lehreinheit_id=='')
				$lehreinheit_id=$row->lehreinheit_id;
			$selected = ($row->lehreinheit_id == $lehreinheit_id?'selected':'');
			//Beteiligte Mitarbeiter auslesen
			$qry_lektoren = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN campus.vw_mitarbeiter ON(mitarbeiter_uid=uid) WHERE lehreinheit_id=".$db->db_add_param($row->lehreinheit_id, FHC_INTEGER);
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
			echo "<OPTION value='studentenansicht.php?lvid=$lehrveranstaltung_id&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id".(isset($uid) && $uid!=''?'&uid='.$uid:'')."' $selected>$row->kurzbz - $gruppen $lektoren</OPTION>\n";
		}
		echo '</SELECT> ';
	}
	else
	{
		if($row = $db->db_fetch_object($result))
			$lehreinheit_id = $row->lehreinheit_id;
		else
			$lehreinheit_id ='';
	}
}
else
{
	echo $p->t('benotungstool/fehlerBeimAuslesen');
}

echo '</td><tr></table>';
echo '<table><tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
echo "<td width='100%'>\n";
echo "<table width='100%'><tr><td><b>".$lv_obj->bezeichnung_arr[$sprache]."</b></td><td align='right'><a href='../../../../documents/".strtolower($stg_obj->kuerzel)."/$lv_obj->semester/$lv_obj->lehreverzeichnis/download/' target='_blank' class='Item'>".$p->t('benotungstool/downloadverzeichnisAnzeigen')."</a></td></tr></table><br>";

if($lehreinheit_id=='')		
	die($p->t('benotungstool/keineKreuzerllistenFuerDieseLehrveranstaltung'));

$qry = "SELECT vorname, nachname FROM campus.vw_student WHERE uid=".$db->db_add_param($user);
$name='';
if($result = $db->db_query($qry))
	if($row = $db->db_fetch_object($result))
		$name = $row->vorname.' '.$row->nachname;



if (!isset($_GET["notenuebersicht"]))
{
	$l = 0;	
	$ueb_check = new uebung();
	$ueb_check->load_uebung($lehreinheit_id,1);
	if	(count($ueb_check->uebungen > 0))
	{
		foreach ($ueb_check->uebungen as $row)
		{
			$sub_check = new uebung();
			$sub_check->load_uebung($lehreinheit_id,2,$row->uebung_id);
			if (count($sub_check->uebungen) > 0)
				$l = 1;
		}
	}

	if ($l > 0)
	{
		echo "<br><b>".$p->t('lehre/leistungsuebersicht')." / <a href='studentenansicht.php?lvid=$lehrveranstaltung_id&stsem=$stsem&lehreinheit_id=$lehreinheit_id&notenuebersicht=1&uid=$user'>".$p->t('benotungstool/notenuebersichtFuer')." $name</b><br><br>";
		$uebung_obj = new uebung();
		$uebung_obj->load_uebung($lehreinheit_id,1);
		if(count($uebung_obj->uebungen)>0)
		{
			echo "<table width='100%'><tr><td valign='top'>";
			echo "<br>".$p->t('benotungstool/waehlenSieEineAufgabeAus').": <SELECT name='uebung' onChange=\"MM_jumpMenu('self',this,0)\">\n";
			echo "<option value='studentenansicht.php?lvid=$lehrveranstaltung_id&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uid=$user' selected></option>";
			foreach ($uebung_obj->uebungen as $row)
			{

				if($uebung_id == $row->uebung_id)
					$selected = 'selected';
				else
					$selected = '';

				$subuebung_obj = new uebung();
				$subuebung_obj->load_uebung($lehreinheit_id,2,$row->uebung_id);
				if(count($subuebung_obj->uebungen)>0)
					{
					$disabled = 'disabled';
					$selected = '';
					echo "<OPTION style='background-color:#cccccc;' value='studentenansicht.php?lvid=$lehrveranstaltung_id&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id&uid=$user' $selected $disabled>";
					echo $row->bezeichnung;
					echo '</OPTION>';
					}
				else
					$disabled = '';



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

						echo "<OPTION value='studentenansicht.php?lvid=$lehrveranstaltung_id&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$subrow->uebung_id&uid=$user' $selected>";


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
			die($p->t('benotungstool/derzeitGibtEsKeineUebungen'));
	}
	else
	{
		$callURL="studentenansicht.php?lvid=$lehrveranstaltung_id&stsem=$stsem&lehreinheit_id=$lehreinheit_id&notenuebersicht=1&uid=$user";
		#header("Location:$callURL");	
	echo "<script language=\"JavaScript\">";
	echo "window.location.href  ='$callURL'";
	echo "</script>";
	exit;		
		//echo "Derzeit sind keine Kreuzerllisten oder Abgaben angelegt";	
	}



	//******SPEICHERN DER DATEN*************
	if(isset($_POST['submit']))
	{
		$error=false;

		$ueb_hlp_obj = new uebung();
		$ueb_hlp_obj->load($uebung_id);
		//Wenn Kreuzerlliste Freigegeben ist
		if($datum_obj->mktime_fromtimestamp($ueb_hlp_obj->freigabevon)<time() &&
			 $datum_obj->mktime_fromtimestamp($ueb_hlp_obj->freigabebis)>time())
		{
			$bsp_obj = new beispiel();

			if($bsp_obj->load_beispiel($uebung_id))
			{
				$anzahl_solved = 0;			
				foreach ($bsp_obj->beispiele as $row)
				{
						if (isset($_POST['solved_'.$row->beispiel_id]) && ($_POST['solved_'.$row->beispiel_id]==1))
							$anzahl_solved++;
				}
				if (($anzahl_solved <= $ueb_hlp_obj->maxbsp) || ($ueb_hlp_obj->maxbsp == 0))
				{		
					foreach ($bsp_obj->beispiele as $row)
					{
						$stud_bsp_obj = new beispiel();

						if($stud_bsp_obj->load_studentbeispiel($user, $row->beispiel_id))
						{
							$stud_bsp_obj->new=false;
						}
						else
						{
							$stud_bsp_obj->new=true;
							$stud_bsp_obj->insertamum = date('Y-m-d H:i:s');
							$stud_bsp_obj->insertvon = $user;
							$stud_bsp_obj->vorbereitet = false;
						}
						if (isset($_POST['solved_'.$row->beispiel_id]))				
							$stud_bsp_obj->vorbereitet = ($_POST['solved_'.$row->beispiel_id]==1?true:false);



						$stud_bsp_obj->probleme = (isset($_POST['problem_'.$row->beispiel_id])?true:false);
						$stud_bsp_obj->updateamum = date('Y-m-d H:i:s');
						$stud_bsp_obj->updatevon = $user;
						$stud_bsp_obj->uid = $user;
						$stud_bsp_obj->beispiel_id = $row->beispiel_id;

						if(!$row->check_anzahl_studentbeispiel($row->beispiel_id))
							die('<span class="error">Fehler beim Ermitteln der Beispiele</span>');
						if (($row->anzahl_studentbeispiel >= $ueb_hlp_obj->maxstd) && ($stud_bsp_obj->vorbereitet==true) && ($ueb_hlp_obj->maxstd != null)) //isset($_POST['problem_'.$row->beispiel_id]) &&  $stud_bsp_obj->new || 
						{
							$hlp = new beispiel();
							if($hlp->load_studentbeispiel($user, $row->beispiel_id))
							{
								if($hlp->vorbereitet!=$stud_bsp_obj->vorbereitet)
								{
									echo "<span class='error'>".$p->t('benotungstool/dasBeispielKannNichtMehrAngekreuztWerden',array($row->bezeichnung))."<br></span>";
									$error = true;
								}
							}
						}
						else
						{
							if(!$stud_bsp_obj->studentbeispiel_save())
							{
								echo $stud_bsp_obj->errormsg;
								$error=true;
							}
						}
					}
				}
				else
				{
					$error=true;
					echo $p->t('benotungstool/zuVieleBeispieleAngekreuzt')."!<br>";
				}
			}

			if($error)
				echo "<span class='error'>".$p->t('benotungstool/esKonntenNichtAlleDatenGespeichertWerden')."</span><br>";
			else
				echo $p->t('global/erfolgreichgespeichert')."<br>";
		}
		else
			echo "<span class='error'>".$p->t('benotungstool/nichtGespeichertKreuzerllisteNichtFreigegeben')."!</span>";
	}

	//********ANZEIGE DER EINGETRAGENEN KREUZERL***********
	if ($l > 0)
	{	
		$uebung_obj = new uebung();
		$uebung_obj->load($uebung_id);
		$downloadname = mb_ereg_replace($uebung_id,mb_ereg_replace(' ','_',$uebung_obj->bezeichnung), $uebung_obj->angabedatei);
		echo $p->t('benotungstool/freigegebenVon')." ".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon))." ".$p->t('global/bis')." ".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis));
		echo "<br><br><h3><u>$uebung_obj->bezeichnung</u></h3>";
		if ($uebung_obj->angabedatei)
			echo $p->t('benotungstool/angabe').": <a href='studentenansicht.php?uid=$user&lvid=$lehrveranstaltung_id&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&download=".$downloadname."'>".$downloadname."</a><br><br>";


		$ueb_obj = new uebung();
		if($ueb_obj->load_studentuebung($user, $uebung_id))
		{
			$anmerkung = $ueb_obj->anmerkung;
			$mitarbeit = $ueb_obj->mitarbeitspunkte;
			$note = $ueb_obj->note;
		}
		else
		{
			$anmerkung = '';
			$mitarbeit = 0;
			$note = null;
		}
	//	$anmerkung = ereg_replace("\n","<br>",$anmerkung);
		$anmerkung = mb_str_replace("\n", "<br>", $anmerkung);
		if ($uebung_obj->beispiele)
		{

			$qry_cnt = "SELECT count(*) as anzahl FROM campus.tbl_studentbeispiel WHERE beispiel_id IN (SELECT beispiel_id from campus.tbl_beispiel where uebung_id =".$db->db_add_param($uebung_id, FHC_INTEGER).") AND vorbereitet=true and uid = ".$db->db_add_param($user);
				if($result_cnt = $db->db_query($qry_cnt))
					if($row_cnt = $db->db_fetch_object($result_cnt))
						$anzahl = $row_cnt->anzahl;

			echo "<script type='text/javascript'>";
			if ($uebung_obj->maxbsp)
				echo "maxbsp = ".$uebung_obj->maxbsp.";";
			else
				echo "maxbsp = 9999;";			
			echo "aktbsp = ".$anzahl.";";
			echo "function plus1(id)
				{
					aktbsp++;
					if (aktbsp > maxbsp)
					{			
						document.bspform.reset();
						alert('Sie dürfen maximal '+maxbsp+' Beispiele markieren!');		
						aktbsp = ".$anzahl.";		
					}

				}
				function minus1()
				{
					aktbsp--;		
				}		
				";

			echo "</script>";

			$bsp_obj = new beispiel();
			$bsp_obj->load_beispiel($uebung_id);			
			if ($bsp_obj->beispiele)
			{
				echo " <table>";
				if ($uebung_obj->maxbsp > 0)
					echo "<tr><td>".$p->t('benotungstool/maxBeispieleStudent').":</td><td><b>".$uebung_obj->maxbsp."</b></td></tr>";
				if ($uebung_obj->maxstd > 0)
					echo "<tr><td>".$p->t('benotungstool/maxStudentenBeispiel').":</td><td style='background-color:#dddddd;'><b>".$uebung_obj->maxstd."</b></td></tr>";
				echo "</table>";	
				echo "
				<form accept-charset='UTF-8' method='POST' name='bspform' action='studentenansicht.php?lvid=$lehrveranstaltung_id&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem&uid=$user'>
				<table width='100%'>
					<tr>
						<td valign='top'><div style='width: 70%;'>
						".($anmerkung!=''?'<b>'.$p->t('global/anmerkungen').':</b><br> '.$anmerkung.'<br><br>':'')."
						</div>
							<table border='1'>
							<tr>
								<td class='ContentHeader2'>".$p->t('benotungstool/beispiel')."</td>
								  <td class='ContentHeader2'>".$p->t('benotungstool/vorbereitet')."</td>
								  <td class='ContentHeader2'>".$p->t('benotungstool/nichtVorbereitet')."</td>
								  <td class='ContentHeader2'>".$p->t('benotungstool/probleme')."</td>
								  <td class='ContentHeader2'>".$p->t('benotungstool/punkte')."</td>
							</tr>";



				foreach ($bsp_obj->beispiele as $row)
				{
					$bsp_voll = false;
					$stud_bsp_obj = new beispiel();

					if ($uebung_obj->maxstd > 0)
					{
						$stud_bsp_obj->check_anzahl_studentbeispiel($row->beispiel_id);
						if ($stud_bsp_obj->anzahl_studentbeispiel >= $uebung_obj->maxstd)
							$bsp_voll = true;
					}
					if($stud_bsp_obj->load_studentbeispiel($user, $row->beispiel_id))
					{
						$vorbereitet = $stud_bsp_obj->vorbereitet;
						$probleme = $stud_bsp_obj->probleme;
					}
					else
					{
						$vorbereitet = false;
						$probleme = false;
					}
					if ($bsp_voll)
					{
						$ro = " disabled";
						$markiert = " style='background-color:#dddddd;'";		
					}
					else
					{
						$ro = "";
						$markiert = "";
					}
					echo "<tr$markiert>
							<td>$row->bezeichnung</td>
							<td align='center'><input type='radio' onchange='plus1($row->beispiel_id);' name='solved_$row->beispiel_id' value='1' ".($vorbereitet?'checked':'')."$ro></td>
							<td align='center'><input type='radio' onchange='minus1();' name='solved_$row->beispiel_id' value='0' ".(!$vorbereitet?'checked':'')."></td>
							<td align='center'><input type='checkbox' name='problem_$row->beispiel_id' ".($probleme?'checked':'')."$ro></td>
							<td align='center'>$row->punkte</td>
						</tr>";


				}

				//Speichern button nur Anzeigen wenn die Uebung Freigegeben ist
				if($datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis)>time())
					echo "<tr><td align='right' colspan=5><input type='submit' value='".$p->t('global/speichern')."' name='submit'></td></form></tr>";

				echo "</table>";
			}
			else
				echo "<table><tr><td>".$p->t('benotungstool/keineBeispieleAngelegt')."</td></tr></table><table width='100%'><tr><td width='70%'></div><table><tr><td>&nbsp;</td></tr></table>";

			if ($uebung_obj->abgabe)
			{

				echo "<br><table><tr><td>".$p->t('benotungstool/abgabedatei').":</td></tr>\n";
				$uebung_obj->load_studentuebung($user, $uebung_id);
				if ($uebung_obj->abgabe_id)
				{		
					$uebung_obj->load_abgabe($uebung_obj->abgabe_id);	
					echo " <tr>";
					echo"	<td><a href='studentenansicht.php?uid=$user&lvid=$lehrveranstaltung_id&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem&download_abgabe=".$uebung_obj->abgabedatei."'>".$uebung_obj->abgabedatei."</a>";
					if($datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis)>time())	
						echo " <a href='studentenansicht.php?uid=$user&lvid=$lehrveranstaltung_id&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem&deleteabgabe=1'>[del]</a></td>";
					echo "</tr>";
				}				

				if($datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis)>time())
				{
					echo "	<tr>\n";
					echo "	<form accept-charset='UTF-8' method='POST' action='studentenansicht.php?uid=$user&lvid=$lehrveranstaltung_id&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem' enctype='multipart/form-data' name='kl_angabe'>\n";
					echo "		<td>\n";
					echo "			<input type='file' name='abgabedatei'> <input type='submit' name='abgabe' value='".$p->t('benotungstool/abgeben')."'>";
					echo "		</td>\n";	
					echo "	</form>\n";
					echo "</tr>\n";

				}
				echo "</table>";
			}

			echo "</td><td valign='top' algin='right'>";

			//Gesamtpunkte diese Kreuzerlliste
			$qry = "SELECT sum(punkte) as punktegesamt FROM campus.tbl_beispiel WHERE uebung_id=".$db->db_add_param($uebung_id, FHC_INTEGER);
			$punkte_gesamt=0;
			if($result=$db->db_query($qry))
				if($row = $db->db_fetch_object($result))
					$punkte_gesamt = $row->punktegesamt;

			//Eingetragen diese Kreuzerlliste
			$qry = "SELECT sum(punkte) as punkteeingetragen FROM campus.tbl_beispiel JOIN campus.tbl_studentbeispiel USING(beispiel_id) WHERE uebung_id=".$db->db_add_param($uebung_id, FHC_INTEGER)." AND uid=".$db->db_add_param($user)." AND vorbereitet=true";
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
					tbl_uebung.lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER)." AND
					tbl_uebung.liste_id = ".$db->db_add_param($liste_id, FHC_INTEGER)." AND 
					tbl_studentbeispiel.uid=".$db->db_add_param($user)." AND vorbereitet=true";
			$punkte_eingetragen_alle=0;
			if($result=$db->db_query($qry))
				if($row = $db->db_fetch_object($result))
					$punkte_eingetragen_alle = ($row->punkteeingetragen_alle!=''?$row->punkteeingetragen_alle:0);


			//Mitarbeitspunkte
			$qry = "SELECT sum(mitarbeitspunkte) as mitarbeitspunkte FROM campus.tbl_studentuebung JOIN campus.tbl_uebung USING(uebung_id)
					WHERE lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER)." AND uid=".$db->db_add_param($user)." AND liste_id = ".$db->db_add_param($liste_id, FHC_INTEGER);
			$mitarbeit_alle=0;
			if($result=$db->db_query($qry))
				if($row = $db->db_fetch_object($result))
					$mitarbeit_alle = ($row->mitarbeitspunkte!=''?$row->mitarbeitspunkte:0);

			//Mitarbeitspunkte
			$qry = "SELECT mitarbeitspunkte FROM campus.tbl_studentuebung
					WHERE uebung_id=".$db->db_add_param($uebung_id, FHC_INTEGER)." AND uid=".$db->db_add_param($user);
			$mitarbeit=0;
			if($result=$db->db_query($qry))
				if($row = $db->db_fetch_object($result))
					$mitarbeit = $row->mitarbeitspunkte;
			echo "

				<table border='1' width='210'>
				<tr>
					<td colspan='2' class='ContentHeader2'>".$p->t('benotungstool/dieseKreuzerlliste').":</td>
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
					<td>$mitarbeit</td>
				</tr>
				</table>
				";


			echo "
			</td></tr>

			</table>

			</form>
			";

			//**********STATISTIK***************
			if($uebung_obj->statistik)
			{
				echo "<h3>".$p->t('benotungstool/statistik')."</h3>";
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
						$qry_cnt = "SELECT distinct uid FROM campus.tbl_studentbeispiel JOIN campus.tbl_beispiel USING(beispiel_id) WHERE uebung_id=".$db->db_add_param($uebung_id)." GROUP BY uid";
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
					                				<table width="100%" border="0" cellspacing="0" cellpadding="0">
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
						echo "<br><br>".$p->t('benotungstool/esHabenStudentenEingetragen',array($gesamt));
					}
				}
				else
					echo "<span class='error'>$beispiel_obj->errormsg</span>";
				echo "</td></tr>";
				echo "</table>";
			}
		}
		else if ($uebung_obj->abgabe)
		{

			echo "<table width='100%'>\n";
			echo "<tr><td>".($note!=''?'<b>'.$p->t('benotungstool/note').': </b>'.$note.'<br><br>':'')."</td></tr>\n";
			echo"	
			<tr>
					<td valign='top'>
					".($anmerkung!=''?'<b>'.$p->t('global/anmerkungen').':</b><br> '.$anmerkung.'<br><br>':'')."
					</td>";
			echo "</tr>\n";

			echo "<tr><td><hr></td></tr>\n";
			$uebung_obj->load_studentuebung($user, $uebung_id);
			if ($uebung_obj->abgabe_id)
			{		
				$uebung_obj->load_abgabe($uebung_obj->abgabe_id);	
				echo " <tr>";
				echo"	<td>".$p->t('benotungstool/abgabedatei').": <a href='studentenansicht.php?uid=$user&lvid=$lehrveranstaltung_id&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem&download_abgabe=".$uebung_obj->abgabedatei."'>".$uebung_obj->abgabedatei."</a>";
				if($datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis)>time())	
					echo " <a href='studentenansicht.php?uid=$user&lvid=$lehrveranstaltung_id&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem&deleteabgabe=1'>[del]</a><br></td>";
				echo "</tr>";
			}
			if($datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis)>time())
			{
				echo "	<tr>\n";
				echo "	<form accept-charset='UTF-8' method='POST' action='studentenansicht.php?uid=$user&lvid=$lehrveranstaltung_id&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem' enctype='multipart/form-data'>\n";
				echo "		<td>\n";
				echo "			<br>".$p->t('global/anmerkung').":<br><textarea name='abgabe_anmerkung' rows='3' cols='50'>".$uebung_obj->abgabe_anmerkung."</textarea><br>";				
				echo "			<br>".$p->t('global/datei').":<br><input type='file' name='abgabedatei'> <input type='submit' name='abgabe' value='".$p->t('benotungstool/abgeben')."'>";
				echo "		</td>\n";	
				echo "	</form>\n";
				echo "</tr>\n";
			}
			echo "</table>\n";

		}
	}
}
//notenübersicht
else
{
	if ($lektorenansicht == 1)
	{
		$uid_arr = Array();
		$vorname_arr = Array();
		$nachname_arr = Array();

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

		echo "<br><hr><br>";	
		echo $p->t('benotungstool/studentenAuswaehlen').": ";
		$key = array_search($uid,$uid_arr);
		$prev = $key-1;
		$next = $key+1;
		if ($key > 0)
			echo "<a href='studentenansicht.php?lvid=$lehrveranstaltung_id&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid_arr[$prev]&stsem=$stsem&notenuebersicht=1'> &lt;&lt; </a>";
		echo "<SELECT name='stud_dd' onChange=\"MM_jumpMenu('self',this,0)\">\n";	
		for ($j = 0; $j < count($uid_arr); $j++)
		{						
				if ($uid_arr[$j] == $uid)
					$selected = " selected";
				else
					$selected = "";

				echo "<option value='studentenansicht.php?lvid=$lehrveranstaltung_id&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid_arr[$j]&stsem=$stsem&notenuebersicht=1'$selected>$vorname_arr[$j] $nachname_arr[$j]</option>";
		}
		echo "</select>";
		if ($key < count($uid_arr)-1)
			echo "<a href='studentenansicht.php?lvid=$lehrveranstaltung_id&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid_arr[$next]&stsem=$stsem&notenuebersicht=1'> &gt;&gt; </a>";

		echo "<br><hr><br>";
	}

	echo "<br><b><a href='studentenansicht.php?uid=$user&lvid=$lehrveranstaltung_id&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>".$p->t('benotungstool/leistungsuebersichtNotenuebersichtFuer')." $name</b><br><br>";
	echo "<table><tr><td>";

	$uebung_obj = new uebung();
	$uebung_obj->load_uebung($lehreinheit_id,1);
	if(count($uebung_obj->uebungen)>0)
	{

		echo "<table style='border: 1px #dddddd solid'>";
		echo "	<tr>\n";
		echo "		<th colspan='2'>".$p->t('benotungstool/aufgabe')."</th>\n";
		echo "		<th>".$p->t('benotungstool/gewicht')."</th>\n";
		echo "		<th>".$p->t('benotungstool/punkte')."</th>";
		echo "		<th>".$p->t('benotungstool/teilnote')."</th>\n";
		echo "		<th>".$p->t('benotungstool/note')."</th>";
		echo "	</tr>\n";
		foreach ($uebung_obj->uebungen as $row)
		{	

			$subuebung_obj = new uebung();
			$subuebung_obj->load_uebung($lehreinheit_id,2,$row->uebung_id);
			$l1note = new studentnote();
			if(count($subuebung_obj->uebungen) >= 0)
			{

				$l1note->calc_l1_note($row->uebung_id, $user, $lehreinheit_id);
				if ($l1note->negativ)
					$l1_note = 5;
				else
					$l1_note = $l1note->l1_note;		
				echo "	<tr>\n";			
				echo "		<td colspan='2'>";
				echo $row->bezeichnung;
				if ($row->positiv)
					echo "*";
				echo "		</td>\n";
				echo "		<td align='center'>".$row->gewicht."</td>\n";
				echo "		<td align='center'>";
				if ($l1note->punkte_gesamt_l1 >0)		
					echo $l1note->punkte_gesamt_l1;
				echo "</td>\n";
				echo "<td align='center'></td>";
				echo "<td align='center'>".$l1_note."</td>\n";
				echo "	</tr>\n";

			}

			if(count($subuebung_obj->uebungen) > 0)
			{

				foreach ($subuebung_obj->uebungen as $subrow)
				{

					echo "	<tr>\n";
					echo "		<td>- </td>";		
					echo "		<td>\n";
					echo $subrow->bezeichnung;
					if ($subrow->positiv)
						echo "*";
					echo "		</td>\n";
					echo "		<td align='center'>\n";
					if ($subrow->abgabe)
						echo $subrow->gewicht;
					echo "		</td>\n";
					if ($subrow->beispiele)
					{

						$l1note->calc_punkte($subrow->uebung_id, $user);
						echo "		<td align='center'>".$l1note->punkte_gesamt."</td>";
						echo "		<td align='center'></td>\n";
						echo "		<td align='center'></td>\n";
					}
					else if ($subrow->abgabe)
					{

						$l1note->calc_note($subrow->uebung_id, $user);
						echo "		<td align='center'></td>\n";
						echo "		<td align='center'>".$l1note->note."</td>";
						echo "		<td align='center'></td>\n";
					}
					echo "	</tr>\n";					/*
					if($datum_obj->mktime_fromtimestamp($subrow->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($subrow->freigabebis)>time())
						echo ' + ';
					else
						echo ' - ';
					*/

				}

			}
		}

		$l1note->calc_gesamtnote($lehreinheit_id, $stsem, $user);
		if ($l1note->negativ)
			$gesamtnote = 5;
		else
			$gesamtnote = $l1note->studentgesamtnote;
		echo "<tr style='background-color:#dddddd;'><td colspan='5'>".$p->t('benotungstool/errechneteGesamtnote').": </td><td align='center'>".$gesamtnote."</td></tr>";


		echo "</table>";
		echo "<span style='font-size:8pt;'>".$p->t('benotungstool/mussPositivSein')."</span>";
	}

	echo "</td><td valign='top'>";

	$legesamtnote = new legesamtnote($lehreinheit_id);


	if (!$legesamtnote->load($user, $lehreinheit_id))
	{
		$lenote = null;
	}
	else
	{
		$lenote = $legesamtnote->note;
	}
	if ($lvgesamtnote = new lvgesamtnote($lehrveranstaltung_id,$user,$stsem))
	{
		$lvnote = $lvgesamtnote->note;
	}
	else
		$lvnote = null;
	if ($zeugnisnote = new zeugnisnote($lehrveranstaltung_id,$user,$stsem))
	{
		$znote = $zeugnisnote->note;
	}
	else
		$znote = null;

	echo "<table style='border: 1px #dddddd solid'>\n";
	echo "	<tr><th colspan='2'>".$p->t('benotungstool/eingetrageneNoten')."</th></tr>";
	echo "<tr>\n";
	echo "<td>".$p->t('global/lehreinheit')."</td>";
	echo "<td>".$lenote."</td>";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td>".$p->t('global/lehrveranstaltung')."</td>";
	echo "<td>".$lvnote."</td>";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td>".$p->t('benotungstool/zeunis')."</td>";
	echo "<td>".$znote."</td>";
	echo "</tr>\n";
	echo "</table>";

	echo "</td></tr></table>";
}
?>
</body>
</html>
