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
// ********************
// * Studentenansicht fuers Kreuzerltool
// ********************

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
require_once('../../../../include/studentnote.class.php');
require_once('../../../../include/legesamtnote.class.php');
require_once('../../../../include/lvgesamtnote.class.php');
require_once('../../../../include/zeugnisnote.class.php');
include('functions.inc.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

$user = get_uid();
//$user = 'if06b172';
//$user = 'if06b144';
$lektorenansicht = 0;

#$rechte = new benutzerberechtigung($conn);
#$rechte->getBerechtigungen($user);

if(isset($_GET['lvid']) && is_numeric($_GET['lvid'])) //Lehrveranstaltung_id
	$lvid = $_GET['lvid'];
else
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id'])) //Lehreinheit_id
	$lehreinheit_id = $_GET['lehreinheit_id'];
else
	$lehreinheit_id = '';

if(check_lektor($user, $conn) && (isset($_GET['uid']) && $_GET["uid"] != ""))
{
	$rights = new benutzerberechtigung($conn);
	$rights->getBerechtigungen($user); 
	//if(!check_lektor_lehreinheit($conn, $user, $_GET["lehreinheit_id"]) && !$rights->isBerechtigt('admin',0))
	$lehreinheit=new lehreinheit($conn, $_GET["lehreinheit_id"]);
	if(!check_lektor_lehrveranstaltung($conn, $user, $lehreinheit->lehrveranstaltung_id, $lehreinheit->studiensemester_kurzbz) && !$rights->isBerechtigt('admin',0))
		die("Sie haben keine Berechtigung für diese Lehreinheit");
	$lektorenansicht = 1;
	$user = $_GET["uid"];
}

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

$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');

//Abgabedatei ausliefern
if (isset($_GET["download_abgabe"])){
	$file=$_GET["download_abgabe"];
	$uebung_id = $_GET["uebung_id"];
	$ueb = new uebung($conn);
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
	$ueb = new uebung($conn);
	$ueb->load($uebung_id);
	$filename = "/documents/benotungstool/angabe/".$ueb->angabedatei;
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
<title>Kreuzerltool</title>
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
	$ueb = new uebung($conn);
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
		$name_neu = makeUploadName($conn, $which='abgabe', $lehreinheit_id=$lehreinheit_id, $uebung_id=$uebung_id, $ss=$stsem,$uid=$user, $date=$datumstr);
		$abgabedatei = $name_neu.".".$name_up["extension"];
		$abgabepfad = BENOTUNGSTOOL_PATH."abgabe/".$abgabedatei;	
			
		$uebung_obj = new uebung($conn);
		$uebung_obj->load_studentuebung($user, $uebung_id);
			
		if ($uebung_obj->errormsg != "")
		{
			$uebung_obj->student_uid = $user;
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
		$uebung_obj2 = new uebung($conn);
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
echo '<table class="tabcontent" height="100%">';
echo ' <tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
echo '<td class="ContentHeader"><font class="ContentHeader">&nbsp;Benotungstool';
echo '</font></td><td  class="ContentHeader" align="right">'."\n";

//Studiensemester laden
$stsem_obj = new studiensemester($conn);
if($stsem=='')
	$stsem = $stsem_obj->getaktorNext();

//Lehreinheiten laden zu denen der eingeloggte Student zugeteilt ist
//Bei Lehrverbaenden werden auch die uebergeordneten geladen
$qry = "SELECT distinct lehreinheit_id, kurzbz FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrfach USING(lehrfach_id) WHERE lehreinheit_id IN(
		SELECT lehreinheit_id FROM public.tbl_benutzergruppe JOIN lehre.tbl_lehreinheitgruppe USING (gruppe_kurzbz)
		WHERE tbl_benutzergruppe.uid='$user' AND
		tbl_lehreinheitgruppe.lehreinheit_id IN(
			SELECT lehreinheit_id FROM lehre.tbl_lehreinheit JOIN campus.tbl_uebung USING(lehreinheit_id)
			WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND tbl_lehreinheit.studiensemester_kurzbz='$stsem')
		UNION
		SELECT 
			lehreinheit_id 
		FROM 
			public.tbl_student, lehre.tbl_lehreinheitgruppe, public.tbl_studentlehrverband
		WHERE 
			tbl_student.student_uid='$user' AND
			tbl_studentlehrverband.student_uid=tbl_student.student_uid AND
			tbl_studentlehrverband.studiensemester_kurzbz='$stsem' AND
			tbl_student.studiengang_kz=tbl_lehreinheitgruppe.studiengang_kz AND
			tbl_lehreinheitgruppe.gruppe_kurzbz is null AND
			trim(tbl_studentlehrverband.semester)=trim(tbl_lehreinheitgruppe.semester) AND
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
				WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND tbl_lehreinheit.studiensemester_kurzbz='$stsem'))";
//echo $qry;
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
			//Beteiligte Mitarbeiter auslesen
			$qry_lektoren = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN campus.vw_mitarbeiter ON(mitarbeiter_uid=uid) WHERE lehreinheit_id='$row->lehreinheit_id'";
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
			echo "<OPTION value='studentenansicht.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->kurzbz - $gruppen $lektoren</OPTION>\n";
		}
		echo '</SELECT> ';
	}
	else
	{
		if($row = pg_fetch_object($result))
			$lehreinheit_id = $row->lehreinheit_id;
		else
			$lehreinheit_id ='';
	}
}
else
{
	echo 'Fehler beim Auslesen der Lehreinheiten';
}

echo '</td><tr></table>';
echo '<table><tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
echo "<td width='100%'>\n";
echo "<table width='100%'><tr><td><b>$lv_obj->bezeichnung</b></td><td align='right'><a href='../../../../documents/".strtolower($stg_obj->kuerzel)."/$lv_obj->semester/$lv_obj->lehreverzeichnis/download/' target='_blank' class='Item'>Downloadverzeichnis anzeigen</a></td></tr></table><br>";

if($lehreinheit_id=='')		
	die('Derzeit gibt es keine Kreuzerllisten f&uuml;r diese Lehrveranstaltung');

$qry = "SELECT vorname, nachname FROM campus.vw_student WHERE uid='$user'";
$name='';
if($result = pg_query($conn, $qry))
	if($row = pg_fetch_object($result))
		$name = $row->vorname.' '.$row->nachname;



if (!isset($_GET["notenuebersicht"]))
{
	$l = 0;	
	$ueb_check = new uebung($conn);
	$ueb_check->load_uebung($lehreinheit_id,1);
	if	(count($ueb_check->uebungen > 0))
	{
		foreach ($ueb_check->uebungen as $row)
		{
			$sub_check = new uebung($conn);
			$sub_check->load_uebung($lehreinheit_id,2,$row->uebung_id);
			if (count($sub_check->uebungen) > 0)
				$l = 1;
		}
	}
	
	if ($l > 0)
	{
		echo "<br><b>Leistungsuebersicht / <a href='studentenansicht.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&notenuebersicht=1&uid=$user'>Notenübersicht</a> f&uuml;r $name</b><br><br>";
		$uebung_obj = new uebung($conn);
		$uebung_obj->load_uebung($lehreinheit_id,1);
		if(count($uebung_obj->uebungen)>0)
		{
			echo "<table width='100%'><tr><td valign='top'>";
			echo "<br>Wählen Sie bitte eine Aufgabe aus (Kreuzerllisten, Abgaben): <SELECT name='uebung' onChange=\"MM_jumpMenu('self',this,0)\">\n";
			echo "<option value='studentenansicht.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uid=$user' selected></option>";
			foreach ($uebung_obj->uebungen as $row)
			{
				
				if($uebung_id == $row->uebung_id)
					$selected = 'selected';
				else
					$selected = '';		
						
				//if($uebung_id=='')
				//	$uebung_id=$row->uebung_id;
				
				$subuebung_obj = new uebung($conn);
				$subuebung_obj->load_uebung($lehreinheit_id,2,$row->uebung_id);
				if(count($subuebung_obj->uebungen)>0)
					{
					$disabled = 'disabled';
					$selected = '';
					echo "<OPTION style='background-color:#cccccc;' value='studentenansicht.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id&uid=$user' $selected $disabled>";
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
						
						echo "<OPTION value='studentenansicht.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$subrow->uebung_id&uid=$user' $selected>";
		
						
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
	}
	else
	{
		header("Location:studentenansicht.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&notenuebersicht=1&uid=$user");	
		//echo "Derzeit sind keine Kreuzerllisten oder Abgaben angelegt";	
	}
	
	
	
	
	//******SPEICHERN DER DATEN*************
	if(isset($_POST['submit']))
	{
		$error=false;
	
		$ueb_hlp_obj = new uebung($conn);
		$ueb_hlp_obj->load($uebung_id);
		//Wenn Kreuzerlliste Freigegeben ist
		if($datum_obj->mktime_fromtimestamp($ueb_hlp_obj->freigabevon)<time() &&
		   $datum_obj->mktime_fromtimestamp($ueb_hlp_obj->freigabebis)>time())
		{
			$bsp_obj = new beispiel($conn);
	
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
						$stud_bsp_obj = new beispiel($conn);
		
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
						$stud_bsp_obj->student_uid = $user;
						$stud_bsp_obj->beispiel_id = $row->beispiel_id;
						
						if(!$row->check_anzahl_studentbeispiel($row->beispiel_id))
							die('<span class="error">Fehler beim Ermitteln der Beispiele</span>');
						if (($row->anzahl_studentbeispiel >= $ueb_hlp_obj->maxstd) && ($stud_bsp_obj->vorbereitet==true) && ($ueb_hlp_obj->maxstd != null)) //isset($_POST['problem_'.$row->beispiel_id]) &&  $stud_bsp_obj->new || 
						{
							$hlp = new beispiel($conn);
							if($hlp->load_studentbeispiel($user, $row->beispiel_id))
							{
								if($hlp->vorbereitet!=$stud_bsp_obj->vorbereitet)
								{
									echo "<span class='error'>Das Beispiel $row->bezeichnung kann nicht mehr angekreuzt werden<br></span>";
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
					echo "Zu viele Beispiele angekreuzt!<br>";
				}
			}
	
			if($error)
				echo "<span class='error'>Es konnten nicht alle Daten gespeichert werden</span><br>";
			else
				echo "Die Daten wurden erfolgreich gespeichert<br>";
		}
		else
			echo "<span class='error'>Die &Auml;nderungen k&ouml;nnen nicht gespeichert werden, da diese Kreuzerlliste nicht freigegeben ist!</span>";
	}
	
	//********ANZEIGE DER EINGETRAGENEN KREUZERL***********
	if ($l > 0)
	{	
		$uebung_obj = new uebung($conn);
		$uebung_obj->load($uebung_id);
		$downloadname = str_replace($uebung_id,ereg_replace(' ','_',$uebung_obj->bezeichnung), $uebung_obj->angabedatei);
		echo "Freigegeben von ".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon))." bis ".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis));
		echo "<br><br><h3><u>$uebung_obj->bezeichnung</u></h3>";
		if ($uebung_obj->angabedatei)
			echo "Angabe: <a href='studentenansicht.php?uid=$user&lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&download=".$downloadname."'>".$downloadname."</a><br><br>";
		
		
		$ueb_obj = new uebung($conn);
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
		$anmerkung = ereg_replace("\n","<br>",htmlentities($anmerkung));
		
		if ($uebung_obj->beispiele)
		{
			
			$qry_cnt = "SELECT count(*) as anzahl FROM campus.tbl_studentbeispiel WHERE beispiel_id IN (SELECT beispiel_id from campus.tbl_beispiel where uebung_id = $uebung_id) AND vorbereitet=true and student_uid = '$user'";
				if($result_cnt = pg_query($conn,$qry_cnt))
					if($row_cnt = pg_fetch_object($result_cnt))
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
			
			$bsp_obj = new beispiel($conn);
			$bsp_obj->load_beispiel($uebung_id);			
			if ($bsp_obj->beispiele)
			{
				echo " <table>";
				if ($uebung_obj->maxbsp > 0)
					echo "<tr><td>Maximale Anzahl der Beispiele/Student:</td><td><b>".$uebung_obj->maxbsp."</b></td></tr>";
				if ($uebung_obj->maxstd > 0)
					echo "<tr><td>Maximale Anzahl Studenten/Beispiel:</td><td style='background-color:#dddddd;'><b>".$uebung_obj->maxstd."</b></td></tr>";
				echo "</table>";	
				echo "
				<form accept-charset='UTF-8' method='POST' name='bspform' action='studentenansicht.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem&uid=$user'>
				<table width='100%'>
					<tr>
						<td valign='top'><div style='width: 70%;'>
						".($anmerkung!=''?'<b>Anmerkungen:</b><br> '.$anmerkung.'<br><br>':'')."
						</div>
							<table border='1'>
							<tr>
								<td class='ContentHeader2'>Beispiel</td>
							    <td class='ContentHeader2'>Vorbereitet</td>
							    <td class='ContentHeader2'>Nicht vorbereitet</td>
							    <td class='ContentHeader2'>Probleme</td>
							    <td class='ContentHeader2'>Punkte</td>
							</tr>";
				
				
				
				foreach ($bsp_obj->beispiele as $row)
				{
					$bsp_voll = false;		
					$stud_bsp_obj = new beispiel($conn);
						
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
					echo "<tr><td align='right' colspan=5><input type='submit' value='Speichern' name='submit'></td></form></tr>";
				
				echo "</table>";
			}
			else
				echo "<table><tr><td>Keine Beispiele angelegt</td></tr></table><table width='100%'><tr><td width='70%'></div><table><tr><td>&nbsp;</td></tr></table>";
			
			if ($uebung_obj->abgabe)
			{
				
				echo "<br><table><tr><td>Abgabedatei:</td></tr>\n";
				$uebung_obj->load_studentuebung($user, $uebung_id);
				if ($uebung_obj->abgabe_id)
				{		
					$uebung_obj->load_abgabe($uebung_obj->abgabe_id);	
					echo " <tr>";
					echo"	<td><a href='studentenansicht.php?uid=$user&lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem&download_abgabe=".$uebung_obj->abgabedatei."'>".$uebung_obj->abgabedatei."</a>";
					if($datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis)>time())	
						echo " <a href='studentenansicht.php?uid=$user&lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem&deleteabgabe=1'>[del]</a></td>";
					echo "</tr>";
				}				
		
				if($datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis)>time())
				{
					echo "	<tr>\n";
					echo "	<form accept-charset='UTF-8' method='POST' action='studentenansicht.php?uid=$user&lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem' enctype='multipart/form-data' name='kl_angabe'>\n";
					echo "		<td>\n";
					echo "			<input type='file' name='abgabedatei'> <input type='submit' name='abgabe' value='Abgeben'>";
					echo "		</td>\n";	
					echo "	</form>\n";
					echo "</tr>\n";
					
				}
				echo "</table>";
			}
			
			echo "</td><td valign='top' algin='right'>";
			
			//Gesamtpunkte diese Kreuzerlliste
			$qry = "SELECT sum(punkte) as punktegesamt FROM campus.tbl_beispiel WHERE uebung_id='$uebung_id'";
			$punkte_gesamt=0;
			if($result=pg_query($conn, $qry))
				if($row = pg_fetch_object($result))
					$punkte_gesamt = $row->punktegesamt;
			
			//Eingetragen diese Kreuzerlliste
			$qry = "SELECT sum(punkte) as punkteeingetragen FROM campus.tbl_beispiel JOIN campus.tbl_studentbeispiel USING(beispiel_id) WHERE uebung_id='$uebung_id' AND student_uid='$user' AND vorbereitet=true";
			$punkte_eingetragen=0;
			if($result=pg_query($conn, $qry))
				if($row = pg_fetch_object($result))
					$punkte_eingetragen = ($row->punkteeingetragen!=''?$row->punkteeingetragen:0);
			
			//Gesamtpunkte alle Kreuzerllisten in dieser Übung
			$ueb_help = new uebung($conn, $uebung_id);
			$liste_id = $ueb_help->liste_id;
			$qry = "SELECT sum(tbl_beispiel.punkte) as punktegesamt_alle FROM campus.tbl_beispiel, campus.tbl_uebung
					WHERE tbl_uebung.uebung_id=tbl_beispiel.uebung_id AND
					tbl_uebung.lehreinheit_id='$lehreinheit_id' and tbl_uebung.liste_id = '$liste_id'";
			$punkte_gesamt_alle=0;
			if($result=pg_query($conn, $qry))
				if($row = pg_fetch_object($result))
					$punkte_gesamt_alle = $row->punktegesamt_alle;
			
			//Eingetragen alle Kreuzerllisten
			$qry = "SELECT sum(tbl_beispiel.punkte) as punkteeingetragen_alle FROM campus.tbl_beispiel, campus.tbl_studentbeispiel, campus.tbl_uebung
					WHERE tbl_beispiel.beispiel_id = tbl_studentbeispiel.beispiel_id AND
					tbl_uebung.uebung_id=tbl_beispiel.uebung_id AND
					tbl_uebung.lehreinheit_id='$lehreinheit_id' AND
					tbl_uebung.liste_id = '$liste_id' AND 
					tbl_studentbeispiel.student_uid='$user' AND vorbereitet=true";
			$punkte_eingetragen_alle=0;
			if($result=pg_query($conn, $qry))
				if($row = pg_fetch_object($result))
					$punkte_eingetragen_alle = ($row->punkteeingetragen_alle!=''?$row->punkteeingetragen_alle:0);
			
			//Mitarbeitspunkte
			$qry = "SELECT sum(mitarbeitspunkte) as mitarbeitspunkte FROM campus.tbl_studentuebung JOIN campus.tbl_uebung USING(uebung_id)
					WHERE lehreinheit_id='$lehreinheit_id' AND student_uid='$user' AND liste_id = '$liste_id'";
			$mitarbeit_alle=0;
			if($result=pg_query($conn, $qry))
				if($row = pg_fetch_object($result))
					$mitarbeit_alle = ($row->mitarbeitspunkte!=''?$row->mitarbeitspunkte:0);
			
			//Mitarbeitspunkte
			$qry = "SELECT mitarbeitspunkte FROM campus.tbl_studentuebung
					WHERE uebung_id='$uebung_id' AND student_uid='$user'";
			$mitarbeit=0;
			if($result=pg_query($conn, $qry))
				if($row = pg_fetch_object($result))
					$mitarbeit = $row->mitarbeitspunkte;
			echo "
			
				<table border='1' width='210'>
				<tr>
					<td colspan='2' class='ContentHeader2'>Diese Kreuzerlliste:</td>
				</tr>
				<tr>
					<td width='180'>Punkte insgesamt m&ouml;glich:</td>
					<td width='30'>$punkte_gesamt</td>
				</tr>
				<tr>
					<td>Punkte eingetragen:</td>
					<td>$punkte_eingetragen</td>
				</tr>
				</table>
				<br><br>
				<table border='1' width='210'>
				<tr>
					<td colspan='2' class='ContentHeader2'>Alle Kreuzerllisten dieser Übung:</td>
				</tr>
				<tr>
					<td width='180'>Punkte insgesamt m&ouml;glich:</td>
					<td width='30'>$punkte_gesamt_alle</td>
				</tr>
				<tr>
					<td>Punkte eingetragen:</td>
					<td>$punkte_eingetragen_alle</td>
				</tr>
				</table>
				<br><br>
				<table border='1' width='210'>
				<tr>
					<td colspan='2' class='ContentHeader2'>Mitarbeitspunkte:</td>
				</tr>
				<tr>
					<td width='180'>Bisher insgesamt:</td>
					<td width='30'>$mitarbeit_alle</td>
				</tr>
				<tr>
					<td>Diese Kreuzerlliste:</td>
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
				echo "<h3>Statistik</h3>";
				$beispiel_obj = new beispiel($conn);
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
						$qry_cnt = "SELECT distinct student_uid FROM campus.tbl_studentbeispiel JOIN campus.tbl_beispiel USING(beispiel_id) WHERE uebung_id='$uebung_id' GROUP BY student_uid";
							if($result_cnt = pg_query($conn,$qry_cnt))
									$gesamt=pg_num_rows($result_cnt);
			
						foreach ($beispiel_obj->beispiele as $row)
						{
							$i++;
							$solved = 0;
							$psolved = 0;
							$qry_cnt = "SELECT count(*) as anzahl FROM campus.tbl_studentbeispiel WHERE beispiel_id=$row->beispiel_id AND vorbereitet=true";
							if($result_cnt = pg_query($conn,$qry_cnt))
								if($row_cnt = pg_fetch_object($result_cnt))
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
						echo "<br><br>Es haben insgesamt <u>$gesamt Studenten</u> eingetragen.";
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
			echo "<tr><td>".($note!=''?'<b>Note: </b>'.$note.'<br><br>':'')."</td></tr>\n";
			echo"	
			<tr>
					<td valign='top'>
					".($anmerkung!=''?'<b>Anmerkungen:</b><br> '.$anmerkung.'<br><br>':'')."
					</td>";
			echo "</tr>\n";
		
			echo "<tr><td><hr></td></tr>\n";
			$uebung_obj->load_studentuebung($user, $uebung_id);
			if ($uebung_obj->abgabe_id)
			{		
				$uebung_obj->load_abgabe($uebung_obj->abgabe_id);	
				echo " <tr>";
				echo"	<td>Abgabedatei: <a href='studentenansicht.php?uid=$user&lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem&download_abgabe=".$uebung_obj->abgabedatei."'>".$uebung_obj->abgabedatei."</a>";
				if($datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis)>time())	
					echo " <a href='studentenansicht.php?uid=$user&lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem&deleteabgabe=1'>[del]</a><br></td>";
				echo "</tr>";
			}
			if($datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis)>time())
			{
				echo "	<tr>\n";
				echo "	<form accept-charset='UTF-8' method='POST' action='studentenansicht.php?uid=$user&lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&stsem=$stsem' enctype='multipart/form-data'>\n";
				echo "		<td>\n";
				echo "			<br>Anmerkung:<br><textarea name='abgabe_anmerkung' rows='3' cols='50'>".$uebung_obj->abgabe_anmerkung."</textarea><br>";				
				echo "			<br>Datei:<br><input type='file' name='abgabedatei'> <input type='submit' name='abgabe' value='Abgeben'>";
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
				
			$qry_stud_dd = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student_lehrveranstaltung JOIN campus.vw_student using(uid) WHERE  studiensemester_kurzbz = '".$stsem."' and lehreinheit_id = '".$lehreinheit_id."'  ORDER BY nachname, vorname";			
            if($result_stud_dd = pg_query($conn, $qry_stud_dd))
			{
				$i=1;
				while($row_stud_dd = pg_fetch_object($result_stud_dd))
				{
					$uid_arr[] = $row_stud_dd->uid;
					$vorname_arr[] = $row_stud_dd->vorname;
					$nachname_arr[] = $row_stud_dd->nachname;				

				}
			}

		echo "<br><hr><br>";	
		echo "Bitte Wählen Sie eine/n Studierende/n aus: ";
		$key = array_search($uid,$uid_arr);
		$prev = $key-1;
		$next = $key+1;
		if ($key > 0)
			echo "<a href='studentenansicht.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid_arr[$prev]&stsem=$stsem&notenuebersicht=1'> &lt;&lt; </a>";	
		echo "<SELECT name='stud_dd' onChange=\"MM_jumpMenu('self',this,0)\">\n";	
		for ($j = 0; $j < count($uid_arr); $j++)
		{						
				if ($uid_arr[$j] == $uid)
					$selected = " selected";
				else
					$selected = "";
			
				echo "<option value='studentenansicht.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid_arr[$j]&stsem=$stsem&notenuebersicht=1'$selected>$vorname_arr[$j] $nachname_arr[$j]</option>";
		}
		echo "</select>";
		if ($key < count($uid_arr)-1)
			echo "<a href='studentenansicht.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid_arr[$next]&stsem=$stsem&notenuebersicht=1'> &gt;&gt; </a>";		
	
		echo "<br><hr><br>";
	}
	
	echo "<br><b><a href='studentenansicht.php?uid=$user&lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>Leistungsuebersicht</a> / Notenübersicht f&uuml;r $name</b><br><br>";	
	echo "<table><tr><td>";
	
	$uebung_obj = new uebung($conn);
	$uebung_obj->load_uebung($lehreinheit_id,1);
	if(count($uebung_obj->uebungen)>0)
	{
		
		echo "<table style='border: 1px #dddddd solid'>";
		echo "	<tr>\n";
		echo "		<th colspan='2'>Aufgabe</th>\n";
		echo "		<th>Gewicht</th>\n";
		echo "		<th>Punkte</th>";
		echo "		<th>Teilnote</th>\n";
		echo "		<th>Note</th>";
		echo "	</tr>\n";
		foreach ($uebung_obj->uebungen as $row)
		{	
			
			$subuebung_obj = new uebung($conn);
			$subuebung_obj->load_uebung($lehreinheit_id,2,$row->uebung_id);
			$l1note = new studentnote($conn);
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
		echo "<tr style='background-color:#dddddd;'><td colspan='5'>Errechnete Gesamtnote: </td><td align='center'>".$gesamtnote."</td></tr>";
	
		
		echo "</table>";
		echo "<span style='font-size:8pt;'>* muss positiv sein</span>";
	}
	
	echo "</td><td valign='top'>";
	
	$legesamtnote = new legesamtnote($conn, $lehreinheit_id);
	    			
	if (!$legesamtnote->load($user, $lehreinheit_id))
	{    				
		$lenote = null;
	}
	else
	{
		$lenote = $legesamtnote->note;
	} 
	if ($lvgesamtnote = new lvgesamtnote($conn, $lvid,$user,$stsem))
	{
		$lvnote = $lvgesamtnote->note;
	}
	else
		$lvnote = null;
	if ($zeugnisnote = new zeugnisnote($conn, $lvid,$user,$stsem))
	{
		$znote = $zeugnisnote->note;
	}
	else
		$znote = null;
	
	echo "<table style='border: 1px #dddddd solid'>\n";
	echo "	<tr><th colspan='2'>Eingetragene Noten</th></tr>";
	echo "<tr>\n";
	echo "<td>Lehreinheit</td>";
	echo "<td>".$lenote."</td>";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td>Lehrveranstaltung</td>";
	echo "<td>".$lvnote."</td>";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td>Zeugnis</td>";
	echo "<td>".$znote."</td>";
	echo "</tr>\n";
	echo "</table>";
	
	echo "</td></tr></table>";
}
?>
</body>
</html>
