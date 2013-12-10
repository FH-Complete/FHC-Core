<?php
/* Copyright (C) 2006 fhcomplete.org
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
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/lvinfo.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/organisationsform.class.php');
//require_once('../../include/organisationseinheit.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$s=new studiengang();
$s->getAll('typ, kurzbz', false);
$studiengang=$s->result;

$user = get_uid();
$oe_studiengang='';
if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
{
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
	if($stg_kz!='')
	{
		$stg_obj = new studiengang();
		if(!$stg_obj->load($stg_kz))
			die('Studiengang kann nicht geladen werden');
		$oe_studiengang = $stg_obj->oe_kurzbz;
	}
}
else
	$stg_kz='';
	
if (isset($_GET['semester']) || isset($_POST['semester']))
	$semester=(isset($_GET['semester'])?$_GET['semester']:$_POST['semester']);
else
	$semester=0;

if(!is_numeric($stg_kz) && $stg_kz!='')
	$stg_kz='';

if(!is_numeric($semester))
	$semester=0;

$oe_fachbereich='';
if(isset($_REQUEST['fachbereich_kurzbz']))
{
	$fachbereich_kurzbz = $_REQUEST['fachbereich_kurzbz'];
	if($fachbereich_kurzbz!='')
	{
		$fb_obj = new fachbereich();
		if(!$fb_obj->load($fachbereich_kurzbz))
			die('Institut konnte nicht geladen werden');
		$oe_fachbereich = $fb_obj->oe_kurzbz;
	}
}
else 
	$fachbereich_kurzbz = '';

if (isset($_REQUEST['oe_kurzbz']))
{
	$oe_kurzbz = $_REQUEST['oe_kurzbz'];
}
else
	$oe_kurzbz='';

//Wenn kein Fachbereich und kein Studiengang gewaehlt wurde
//dann wird der Studiengang auf 0 gesetzt da sonst die zu ladende liste zu lang wird

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$write_admin=false;
$write_low=false;
$messages='';

if(isset($_POST['lvid']))
{
	//Wenn eine LVID uebergeben wird, dann wird die berechtigung des studienganges 
	//dieser LV geprueft
	$lv_obj = new lehrveranstaltung();
	$lv_obj->load($_POST['lvid']);
	$stg_obj = new studiengang();
	$stg_obj->load($lv_obj->studiengang_kz);
	$oe_studiengang = $stg_obj->oe_kurzbz;
}
if($rechte->isBerechtigt('lehre/lehrveranstaltung', $oe_studiengang, 'suid')
|| $rechte->isBerechtigt('lehre/lehrveranstaltung', $oe_fachbereich, 'suid'))
	$write_admin=true;

if($rechte->isBerechtigt('lehre/lehrveranstaltung:begrenzt', $oe_studiengang, 'suid') 
|| $rechte->isBerechtigt('lehre/lehrveranstaltung:begrenzt', $oe_fachbereich, 'suid'))
	$write_low=true;

if(!$rechte->isBerechtigt('lehre/lehrveranstaltung:begrenzt'))
	die('Sie haben keine Berechtigung fuer diese Seite');

if (isset($_GET['isaktiv']) || isset($_POST['isaktiv']))
	$isaktiv=(isset($_GET['isaktiv'])?$_GET['isaktiv']:$_POST['isaktiv']);
else
	if($write_admin)
		$isaktiv='';
	else
		$isaktiv='true';
		
// Speichern der Daten
if(isset($_POST['lvid']) && is_numeric($_POST['lvid']))
{
	// Die Aenderungen werden per Ajax Request durchgefuehrt,
	// daher wird nach dem Speichern mittels exit beendet
	if($write_admin)
	{
		//Lehrevz Speichern
		if(isset($_POST['lehrevz']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->lehreverzeichnis=$_POST['lehrevz'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit( 'true');
				else 
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else 
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}

		//Aktiv Feld setzen
		if(isset($_POST['aktiv']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->aktiv=($_POST['aktiv']=='true'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else 
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else 
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}
	}
	
	if($write_low || $write_admin)
	{
		//LVInfo kopieren
		if(isset($_POST['source_id']))
		{
			$lvinfo = new lvinfo();
			if(!$lvinfo->copy($_POST['source_id'], $_POST['lvid']))
				exit('Fehler beim Kopieren');
			else 
				exit('true');
		}
		
		//Lehre Feld setzen
		if(isset($_POST['lehre']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->lehre=($_POST['lehre']=='true'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else 
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else 
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}
	
		//Zeugnis Feld setzen
		if(isset($_POST['zeugnis']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->zeugnis=($_POST['zeugnis']=='true'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else 
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else 
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}
	
		//Sort Speichern
		if(isset($_POST['sort']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->sort=$_POST['sort'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else 
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else 
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}
		
		//Incoming Speichern
		if(isset($_POST['incoming']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->incoming=$_POST['incoming'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else 
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else 
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}
	
		//FBK Speichern
		if(isset($_POST['fbk']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->koordinator=$_POST['fbk'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else 
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else 
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}
		
		//Lehrform Speichern
		if(isset($_POST['lf']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->lehrform_kurzbz=$_POST['lf'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else 
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else 
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}
	
		//Lehrtyp Speichern
		if(isset($_POST['lt']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->lehrtyp_kurzbz=$_POST['lt'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else 
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else 
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}
	
		//Projektarbeit Feld setzen
		if(isset($_POST['projektarbeit']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->projektarbeit=($_POST['projektarbeit']=='true'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else 
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else 
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}
	}
	else 
	{
		exit('Sie haben keine Schreibrechte fuer diese Seite');
	}
}

//Lehrformen holen
$qry = "
SELECT
	lehrform_kurzbz,
	bezeichnung
FROM
	lehre.tbl_lehrform ORDER BY lehrform_kurzbz";

$lf = array();
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$lf[$row->lehrform_kurzbz]['lehrform_kurzbz']=$row->lehrform_kurzbz;
		$lf[$row->lehrform_kurzbz]['bezeichnung']=$row->bezeichnung;
	}
}

//Lehrtypen holen
$qry = "
SELECT
	lehrtyp_kurzbz,
	bezeichnung
FROM
	lehre.tbl_lehrtyp ORDER BY lehrtyp_kurzbz";

$lt = array();
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$lt[$row->lehrtyp_kurzbz]['lehrtyp_kurzbz']=$row->lehrtyp_kurzbz;
		$lt[$row->lehrtyp_kurzbz]['bezeichnung']=$row->bezeichnung;
	}
}

//Fachbereichskoordinatoren holen
$fb_kurzbz='';
if($stg_kz!='')
{
	$where = "oe_kurzbz=(SELECT oe_kurzbz FROM public.tbl_studiengang 
						WHERE studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER)." LIMIT 1)";
	$where2="studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER);
	$tables='lehre.tbl_lehrveranstaltung';
}
else
{
	if($fachbereich_kurzbz != '')
		$fb_kurzbz=$fachbereich_kurzbz;
	else
	{
		$fachb=new fachbereich();
		$fachb->loadOE($oe_kurzbz);
		$fb_kurzbz=$fachb->fachbereich_kurzbz;
	}
	$where = "fachbereich_kurzbz=".$db->db_add_param($fb_kurzbz);
	$where2 = $where." AND 
	          tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND 
	          tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id";
	$tables='lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrfach';
}
	
$qry = "
SELECT
	distinct
	vorname,
	nachname,
	uid
FROM
	campus.vw_mitarbeiter JOIN
	(SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='fbk' AND $where
	 UNION
	 SELECT koordinator as uid FROM $tables WHERE $where2) as a USING(uid) ORDER BY nachname, vorname";

$fbk = array();
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$fbk[$row->uid]['vorname']=$row->vorname;
		$fbk[$row->uid]['nachname']=$row->nachname;
	}
}

//Lehrveranstaltungen holen

//Wenn nicht admin, werden erst nur die aktiven angezeigt, es koennen aber auch die inaktiven eingeblendet werden

$aktiv='';
$isaktiv=trim($isaktiv);

if($isaktiv=='true')
{
	$aktiv = ' AND tbl_lehrveranstaltung.aktiv=true';	
}
elseif($isaktiv=='false')
{
	$aktiv = ' AND tbl_lehrveranstaltung.aktiv=false';
}
else
{
	$aktiv='';
}

if($fb_kurzbz !='')
	$sql_query="SELECT distinct tbl_lehrveranstaltung.* 
	FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrfach WHERE
	tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
	tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND
	tbl_lehrfach.fachbereich_kurzbz=".$db->db_add_param($fb_kurzbz);
else
	$sql_query="SELECT * FROM lehre.tbl_lehrveranstaltung WHERE true";

if($stg_kz!='')
	$sql_query.= " AND tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER);
//if($oe_kurzbz!='')
//	$sql_query.= " AND tbl_lehrveranstaltung.oe_kurzbz=".$db->db_add_param($oe_kurzbz);
if($semester != -1)
	$sql_query.=" AND tbl_lehrveranstaltung.semester=".$db->db_add_param($semester, FHC_INTEGER)." $aktiv ORDER BY tbl_lehrveranstaltung.bezeichnung";

if(!$result_lv = $db->db_query($sql_query))
	die("Lehrveranstaltung not found!");

//Studiengang DropDown
$outp='';
$s=array();
$outp.="<form action='".$_SERVER['PHP_SELF']."' method='GET' onsubmit='return checksubmit();'>";
$outp.=" Studiengang <SELECT name='stg_kz' id='select_stg_kz'>";
$outp.="<OPTION value='' ".($stg_kz==''?'selected':'').">-- Alle --</OPTION>";
$stg_berechtigt = $rechte->getStgKz('lehre/lehrveranstaltung:begrenzt');

foreach ($studiengang as $stg)
{
	if(in_array($stg->studiengang_kz, $stg_berechtigt))
	{
		$outp.="<OPTION value='$stg->studiengang_kz' ".($stg->studiengang_kz==$stg_kz?'selected':'').">".$db->convert_html_chars($stg->kuerzel.' - '.$stg->kurzbzlang)."</OPTION>";
	}
	if(!isset($s[$stg->studiengang_kz]))
		$s[$stg->studiengang_kz]=new stdClass();
	$s[$stg->studiengang_kz]->max_sem=9; // $stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
}
if(!isset($s['']))
	$s['']=new stdClass();
$s['']->max_sem=9;

$outp.='</SELECT>';

//Semester DropDown
$outp.= ' Semester <SELECT name="semester"><option value="-1">--Alle--</option>';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
	$outp.="<OPTION value='$i' ".($i==$semester?'selected':'').">$i</OPTION>";
$outp.='</SELECT>';

//Institut DropDown
$outp.= ' Institut <SELECT name="fachbereich_kurzbz" id="select_fachbereich_kurzbz">';
$fachb = new fachbereich();
$fachb->getAll();
$outp.= "<OPTION value='' ".($fachbereich_kurzbz==''?'selected':'').">-- Alle --</OPTION>";
$fachbereich_berechtigt = $rechte->getFbKz('lehre/lehrveranstaltung:begrenzt');
foreach ($fachb->result as $fb)
{
	if($fachbereich_kurzbz==$fb->fachbereich_kurzbz)
		$selected = 'selected';
	else
		$selected = '';

	if(in_array($fb->fachbereich_kurzbz, $fachbereich_berechtigt))
		$outp.= '<OPTION value="'.$db->convert_html_chars($fb->fachbereich_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($fb->fachbereich_kurzbz).'</OPTION>';
}

$outp.= '</SELECT>';

//if($write_admin) Von kindlm am 12.04.2013 auskommentiert, da Assistentinnen auch bei inaktiven LV's die Lehrform aendern koennen sollen
//{
	//Aktiv DropDown
	$outp.= ' Aktiv <SELECT name="isaktiv" id="isaktiv">';
	$outp.= "<OPTION value=''".($isaktiv==''?' selected':'').">-- Alle --</OPTION>";
	$outp.= "<OPTION value='true '".($isaktiv=='true'?'selected':'').">-- Aktiv --</OPTION>";
	$outp.= "<OPTION value='false '".($isaktiv=='false'?'selected':'').">-- Nicht aktiv --</OPTION>";
	$outp.= '</SELECT>';
//}
/*else 
{
	$isaktiv='aktiv';
}*/

	$outp.= ' <input type="submit" value="Anzeigen">';

//Organisationseinheit Dropdown
	$outp.= '<br>Organisationseinheit <select name="oe_kurzbz" id="select_oe_kurzbz"><option value="">-- Alle --</option>';
	$oe=new organisationseinheit();
	$oe->getAll();
	foreach($oe->result as $row)
	{
		if($oe_kurzbz==$row->oe_kurzbz)
			$selected='selected';
		else
			$selected='';
		$outp.= '<option value="'.$db->convert_html_chars($row->oe_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung).'</option>';
	}
	$outp.= '</select>';

	$outp.= '</form>';

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Lehrveranstaltung Verwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
	<script type="text/javascript" src="../../include/js/jquery.js"></script>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>

	<script type="text/javascript">
		$(document).ready(function() 
			{ 
				$("#t1").tablesorter(
				{
					sortList: [[2,0]],
					widgets: ["zebra"]
				}); 
			}); 

			var isaktiv="'.$isaktiv.'";
			function checksubmit()
			{		
				if(document.getElementById("select_stg_kz").value==\'\' 
					&& document.getElementById("select_fachbereich_kurzbz").value==\'\'
					&& document.getElementById("select_oe_kurzbz").value==\'\')
				{
					alert("Die Felder Studiengang, Institut und Organisationseinheit dürfen nicht gleichzeitig auf \'Alle\' gesetzt sein");
					return false;
				}
				else
					return true;
		
			}
			function changelehrevz(lvid, lehrevz)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php", 
					data:{ "lvid": lvid, "lehrevz": lehrevz },
					success: function(data) 
					{ 
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#lehrevzok"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#lehrevzok"+lvid).css("background-color", "");}, 500);
						}

					},
					error: function() { alert("error"); }
				});
			}
			function changesort(lvid, sort)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php", 
					data:{ "lvid": lvid, "sort": sort },
					success: function(data) 
					{ 
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#sortok"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#sortok"+lvid).css("background-color", "");}, 500);
						}
					},
					error: function() { alert("error"); }
				});
			}
			function changeincoming(lvid, incoming)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php", 
					data:{ "lvid": lvid, "incoming": incoming },
					success: function(data) 
					{ 
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#incomingok"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#incomingok"+lvid).css("background-color", "");}, 500);
						}

					},
					error: function() { alert("error"); }
				});
			}

			function changefbk(lvid, fbk)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php", 
					data:{ "lvid": lvid, "fbk": fbk },
					success: function(data) 
					{ 
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#fbkok"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#fbkok"+lvid).css("background-color", "");}, 500);
						}

					},
					error: function() { alert("error"); }
				});
			}
			
			function changelehrform(lvid, lf)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php", 
					data:{ "lvid": lvid, "lf": lf },
					success: function(data) 
					{ 
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#lf"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#lf"+lvid).css("background-color", "");}, 500);
						}

					},
					error: function() { alert("error"); }
				});
			}

			function changelehrtyp(lvid, lt)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php", 
					data:{ "lvid": lvid, "lt": lt },
					success: function(data) 
					{ 
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#lt"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#lt"+lvid).css("background-color", "");}, 500);
						}

					},
					error: function() { alert("error"); }
				});
			}
			
			function copylvinfo(lvid, source_id)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php", 
					data:{ "lvid": lvid, "source_id": source_id },
					success: function(data) 
					{ 
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#lvinfo"+lvid).html("vorhanden");
						}
					},
					error: function() { alert("error"); }
				});
			}

			function changeboolean(lvid, name)
			{
				value=document.getElementById(name+lvid).value;
				
				var dataObj = {};
				dataObj["lvid"]=lvid;
				dataObj[name]=value;

				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php", 
					data:dataObj,
					success: function(data) 
					{
						if(data=="true")
						{
							//Image und Value aendern
							if(value=="true")
								value="false";
							else
								value="true";
							document.getElementById(name+lvid).value=value;
							document.getElementById(name+"img"+lvid).src="../../skin/images/"+value+".png";
						}
						else 
							alert("ERROR:"+data)
					},
					error: function() { alert("error"); }
				});
			}
			
		</script>
	</head>
	<body class="Background_main">
	';

if(isset($s[$stg_kz]->kurzbz))
	$header=$s[$stg_kz]->kurzbz;
else if($fachbereich_kurzbz!='')
	$header=$fachbereich_kurzbz;
else
{
	$oe=new organisationseinheit();
	$oe->load($oe_kurzbz);
	$header=$oe->organisationseinheittyp_kurzbz.' '.$oe->bezeichnung;
}
$header .= ' - ';
if($semester!='-1')
	$header .= $semester;

echo "<H2>Lehrveranstaltung Verwaltung (".$db->convert_html_chars($header).")</H2>";
echo $messages;
echo '<table width="100%"><tr><td>';
echo $outp;

echo '</td><td valign="top">';
//Neu Button
if($write_admin)
	echo '<input type="button" onclick="parent.lv_detail.location=\'lehrveranstaltung_details.php?neu=true&stg_kz='.$db->convert_html_chars($stg_kz).'&semester='.$db->convert_html_chars($semester).'\'" value="Neu"/>';
echo '</td></tr></table>';

if ($result_lv!=0)
{
	//Organisationsformen laden
	$orgform_obj = new organisationsform();
	if(!$orgform_obj->getOrgformLV())
		die('Organisationsformen konnten nicht geladen werden');

	$num_rows=$db->db_num_rows($result_lv);
	echo '<h3>&Uuml;bersicht - '.$num_rows.' LVAs</h3>
	<table class="tablesorter" id="t1">
	<thead>
	<tr>';
	echo "<th>ID</th>
		  <th>Kurzbz</th>
		  <th>Bezeichnung</th>
		  <th>Lehrform</th>
		  <th>Lehrtyp</th>
		  <th>Stg</th>\n
		  <th>Orgform</th>
		  <th title='Semesterstunden'>SS</th>
		  <th>ECTS</th>
		  <th>Lehre</th>
		  <th title='Verzeichnisname im Filesystem'>LehreVz</th>
		  <th>Aktiv</th>
		  <th title='Sortierreihenfolge der LV am Zeugnis'>Sort</th>
		  <th title='Anzahl der Incoming die an dieser LV teilnehmen duerfen'>Incoming</th>
		  <th>Zeugnis</th>
		  <th title='Soll diese Lehrveranstaltung bei Diplom-/Bachelorarbeit ausgewaehlt werden koennen?'>BA/DA</th>
		  <th>Koordinator</th>
		  <th>LV-Info</th>
		  <th>Lehrfach</th>
		  <th>LV-Angebot</th>
		  <th>kompatible LV</th>";

	echo "</tr></thead>";
	echo "<tbody>";
	for($i=0;$i<$num_rows;$i++)
	{
		$row=$db->db_fetch_object($result_lv);
		echo "<tr>";
		//ID
		echo "<td align='right'>";
		if($write_admin)
			echo '<a href="lehrveranstaltung_details.php?lv_id='.$db->convert_html_chars($row->lehrveranstaltung_id).'" target="lv_detail">'.$db->convert_html_chars($row->lehrveranstaltung_id).'</a>';
		else		
			echo $db->convert_html_chars($row->lehrveranstaltung_id);
		echo '</td>';
		//Kurzbz
		echo '<td>',$db->convert_html_chars($row->kurzbz).'</td>';
		//Bezeichnung
		echo '<td>';
		if($write_admin)
			echo '<a href="lehrveranstaltung_details.php?lv_id='.$db->convert_html_chars($row->lehrveranstaltung_id).'" target="lv_detail">'.$db->convert_html_chars($row->bezeichnung).'</a>';
		else
			echo $db->convert_html_chars($row->bezeichnung);
		echo '</td>';

		//Lehrform
		echo '<td style="white-space:nowrap;">';
		echo '<SELECT style="width:80px;" id="lf'.$row->lehrveranstaltung_id.'">';
		echo '<option value="">--</option>';
		foreach ($lf as $lehrform=>$lf_kz)
		{
			if($lehrform==$row->lehrform_kurzbz)
				$selected='selected';
			else
				$selected='';
			echo '<option value="'.$db->convert_html_chars($lehrform).'" '.$selected.'>'.$db->convert_html_chars($lf_kz['lehrform_kurzbz']).' '.$db->convert_html_chars($lf_kz['bezeichnung']).'</option>';
		}
		echo '</SELECT><input type="button" value="ok" id="lf'.$row->lehrveranstaltung_id.'" onclick="changelehrform(\''.$row->lehrveranstaltung_id.'\',$(\'#lf'.$row->lehrveranstaltung_id.'\').val())">';
		echo '</td>';
		
		//Lehrtyp
		echo '<td style="white-space:nowrap;">';
		echo '<SELECT id="lt'.$row->lehrveranstaltung_id.'">';
		echo '<option value="">--</option>';
		foreach ($lt as $lehrtyp=>$lt_kz)
		{
			if($lehrtyp==$row->lehrtyp_kurzbz)
				$selected='selected';
			else
				$selected='';
			echo '<option value="'.$db->convert_html_chars($lehrtyp).'" '.$selected.'>'.$db->convert_html_chars($lt_kz['bezeichnung']).'</option>';
		}
		echo '</SELECT><input type="button" value="ok" id="lf'.$row->lehrveranstaltung_id.'" onclick="changelehrtyp(\''.$row->lehrveranstaltung_id.'\',$(\'#lt'.$row->lehrveranstaltung_id.'\').val())">';
		echo '</td>';

		//Studiengang
		echo '<td>'.$db->convert_html_chars($s[$row->studiengang_kz]->kurzbz).'</td>';
		//Organisationsform
		echo '<td style="white-space:nowrap;">';
		echo $db->convert_html_chars($row->orgform_kurzbz);
		echo '</td>';
		//Semesterstunden
		echo '<td>'.$db->convert_html_chars($row->semesterstunden).'</td>';
		//ECTS
		echo '<td>'.$db->convert_html_chars($row->ects).'</td>';
		//Lehre
		echo '<td align="center">
		<div style="display: none">'.$db->convert_html_chars($row->lehre).'</div>
		<a href="Lehre" onclick="changeboolean(\''.$row->lehrveranstaltung_id.'\',\'lehre\'); return false">
		<input type="hidden" id="lehre'.$row->lehrveranstaltung_id.'" value="'.($row->lehre=='t'?'true':'false').'">
		<img id="lehreimg'.$row->lehrveranstaltung_id.'" title="Lehre" src="../../skin/images/'.($row->lehre=='t'?'true.png':'false.png').'" height="20">
		</a></td>';
		//LehreVz
		echo '<td  style="white-space:nowrap;">';
		if($write_admin)
		{
			echo '<input type="text" id="lehrevz'.$row->lehrveranstaltung_id.'" value="'.$db->convert_html_chars($row->lehreverzeichnis).'" size="4" name="lehrevz">
			<input type="button" id="lehrevzok'.$row->lehrveranstaltung_id.'" value="ok" onclick="changelehrevz(\''.$row->lehrveranstaltung_id.'\',document.getElementById(\'lehrevz'.$row->lehrveranstaltung_id.'\').value);">';
		}
		else
			echo $db->convert_html_chars($row->lehreverzeichnis);

		echo '</td>';
		//Aktiv
		echo '<td align="center" style="white-space:nowrap;">';
		if($write_admin)
		{
			echo '<div style="display: none">'.$db->convert_html_chars($row->aktiv).'</div>';
			echo '<a href="Aktiv" onclick="changeboolean(\''.$row->lehrveranstaltung_id.'\',\'aktiv\'); return false">
				<input type="hidden" id="aktiv'.$row->lehrveranstaltung_id.'" value="'.($row->aktiv=='t'?'true':'false').'">
				<img id="aktivimg'.$row->lehrveranstaltung_id.'" title="Aktiv" src="../../skin/images/'.($row->aktiv=='t'?'true.png':'false.png').'" height="20">
				</a>
			';
		}
		else
			echo ($row->aktiv=='t'?'Ja':'Nein');
		echo '</td>';
		//Sort
		echo '<td style="white-space:nowrap;">';
		echo '<div style="display: none">'.$db->convert_html_chars($row->sort).'</div>';
		echo '<input type="text" id="sort'.$row->lehrveranstaltung_id.'" value="'.$db->convert_html_chars($row->sort).'" size="4">
			<input type="button" id="sortok'.$row->lehrveranstaltung_id.'"value="ok" onclick="changesort(\''.$row->lehrveranstaltung_id.'\',document.getElementById(\'sort'.$row->lehrveranstaltung_id.'\').value);">';
		echo "</td>";
		//Incoming
		echo '<td style="white-space:nowrap;">';
		echo '<div style="display: none">'.$db->convert_html_chars($row->incoming).'</div>';
		echo '<input type="text" id="incoming'.$row->lehrveranstaltung_id.'" value="'.$db->convert_html_chars($row->incoming).'" size="4">
			<input type="button" value="ok" id="incomingok'.$row->lehrveranstaltung_id.'" onclick="changeincoming(\''.$row->lehrveranstaltung_id.'\',document.getElementById(\'incoming'.$row->lehrveranstaltung_id.'\').value);">';
		echo '</td>';
		//Zeugnis
		echo '<td align="center">
				<div style="display: none">'.$db->convert_html_chars($row->zeugnis).'</div>
				<a href="Zeugnis" onclick="changeboolean(\''.$row->lehrveranstaltung_id.'\',\'zeugnis\'); return false">
				<input type="hidden" id="zeugnis'.$row->lehrveranstaltung_id.'" value="'.($row->zeugnis=='t'?'true':'false').'">
				<img id="zeugnisimg'.$row->lehrveranstaltung_id.'" title="Zeugnis" src="../../skin/images/'.($row->zeugnis=='t'?'true.png':'false.png').'" height="20">
				</a>
			</td>';
		//Projektarbeit
		echo '<td align="center">
				<div style="display: none">'.$db->convert_html_chars($row->projektarbeit).'</div>
				<a href="Projektarbeit" onclick="changeboolean(\''.$row->lehrveranstaltung_id.'\',\'projektarbeit\'); return false">
				<input type="hidden" id="projektarbeit'.$row->lehrveranstaltung_id.'" value="'.($row->projektarbeit=='t'?'true':'false').'">
				<img id="projektarbeitimg'.$row->lehrveranstaltung_id.'" title="Projektarbeit" src="../../skin/images/'.($row->projektarbeit=='t'?'true.png':'false.png').'" height="20">
				</a>
			</td>';
		//FBK
		echo '<td style="white-space:nowrap;">';
		echo '<SELECT id="fbk'.$row->lehrveranstaltung_id.'">';
		echo '<option value="">-- Keine Auswahl --</option>';
		foreach ($fbk as $fb_uid=>$fb_k)
		{
			if($fb_uid==$row->koordinator)
				$selected='selected';
			else
				$selected='';
			echo '<option value="'.$db->convert_html_chars($fb_uid).'" '.$selected.'>'.$db->convert_html_chars($fb_k['nachname']." ".$fb_k['vorname']).'</option>';
		}
		echo '</SELECT><input type="button" value="ok" id="fbkok'.$row->lehrveranstaltung_id.'" onclick="changefbk(\''.$row->lehrveranstaltung_id.'\',$(\'#fbk'.$row->lehrveranstaltung_id.'\').val())">';
		echo '</td>';
		echo '<td nowrap>';
		//LVInfo
		$lvinfo = new lvinfo();
		if(!$lvinfo->exists($row->lehrveranstaltung_id))
		{
			echo '<span id="lvinfo'.$row->lehrveranstaltung_id.'">
					kopieren von id: <input type="text" size="3" id="source_id'.$row->lehrveranstaltung_id.'" value="" />
					<input type="button" value="ok" onclick="copylvinfo(\''.$row->lehrveranstaltung_id.'\',$(\'#source_id'.$row->lehrveranstaltung_id.'\').val())">
				</span>';
		}
		else 
			echo 'vorhanden';
		echo '</td>';
		//Lehrfach anlegen
		echo '<td nowrap>';
		if($write_admin)
			echo '<a href="lehrfach.php?neu
			&filter_stg_kz='.$db->convert_html_chars($row->studiengang_kz).'
			&filter_semester='.$db->convert_html_chars($row->semester).'
			&filter_fachbereich_kurzbz=&filter_aktiv=
			&stg_kz='.$row->studiengang_kz.'
			&kurzbz='.$db->convert_html_chars($row->kurzbz).'
			&bezeichnung='.$db->convert_html_chars($row->bezeichnung).'
			&semester='.$db->convert_html_chars($row->semester).'
			&farbe=&fachbereich_kurzbz=Dummy
			&sprache='.$db->convert_html_chars($row->sprache).'" target="_parent" method="post">LF Neu</a>';
		else		
			echo $db->convert_html_chars($row->lehrveranstaltung_id);
		
		echo '</td>
			<td nowrap>
				<a href="lehrveranstaltung_lvangebot.php?lehrveranstaltung_id='.$db->convert_html_chars($row->lehrveranstaltung_id).'" target="lv_detail">LV-Angebot</a>
			</td>';
		
		echo '<td><a href="lehrveranstaltung_kompatibel.php?lehrveranstaltung_id='.$row->lehrveranstaltung_id.'&type=edit" target="lv_detail">anzeigen</a></td>';
		echo "</tr>\n";
	}
}
else
	echo 'Kein Eintrag gefunden!';

echo '</tbody>
	</table>';

?>
	</body>
</html>
