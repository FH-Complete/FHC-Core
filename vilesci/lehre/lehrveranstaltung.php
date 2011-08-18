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
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/lvinfo.class.php');
require_once('../../include/lehrveranstaltung.class.php');

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
if (isset($_GET['isaktiv']) || isset($_POST['isaktiv']))
	$isaktiv=(isset($_GET['isaktiv'])?$_GET['isaktiv']:$_POST['isaktiv']);
else
	$isaktiv='';

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

//Wenn kein Fachbereich und kein Studiengang gewaehlt wurde
//dann wird der Studiengang auf 0 gesetzt da sonst die zu ladende liste zu lang wird

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$write_admin=false;
$write_low=false;

if($rechte->isBerechtigt('lehre/lehrveranstaltung', $oe_studiengang, 'suid')
|| $rechte->isBerechtigt('lehre/lehrveranstaltung', $oe_fachbereich, 'suid'))
	$write_admin=true;

if($rechte->isBerechtigt('lehre/lehrveranstaltung:begrenzt', $oe_studiengang, 'suid') 
|| $rechte->isBerechtigt('lehre/lehrveranstaltung:begrenzt', $oe_fachbereich, 'suid'))
	$write_low=true;

if(!$rechte->isBerechtigt('lehre/lehrveranstaltung:begrenzt'))
	die('Sie haben keine Berechtigung fuer diese Seite');

if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
{
	if($write_admin)
	{
		//Lehrevz Speichern
		if(isset($_POST['lehrevz']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_GET['lvid']))
			{
				$lv_obj->lehreverzeichnis=$_POST['lehrevz'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					echo 'Erfolgreich gespeichert';
				else 
					echo 'Fehler beim Speichern:'.$lv_obj->errormsg;
			}
			else 
				echo 'Fehler beim Laden der LV:'.$lv_obj->errormsg;
		}

		//Aktiv Feld setzen
		if(isset($_GET['aktiv']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_GET['lvid']))
			{
				$lv_obj->aktiv=($_GET['aktiv']=='t'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					echo 'Erfolgreich gespeichert';
				else 
					echo 'Fehler beim Speichern:'.$lv_obj->errormsg;
			}
			else 
				echo 'Fehler beim Laden der LV:'.$lv_obj->errormsg;
		}
		//Organisationsform Speichern
		if(isset($_POST['orgform']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_GET['lvid']))
			{
				$lv_obj->orgform_kurzbz=$_POST['orgform'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					echo 'Erfolgreich gespeichert';
				else 
					echo 'Fehler beim Speichern:'.$lv_obj->errormsg;
			}
			else 
				echo 'Fehler beim Laden der LV:'.$lv_obj->errormsg;
		}
	}
	
	if($write_low || $write_admin)
	{
		//LVInfo kopieren
		if(isset($_POST['source_id']))
		{
			$lvinfo = new lvinfo();
			if(!$lvinfo->copy($_POST['source_id'], $_GET['lvid']))
				echo 'Fehler beim Kopieren';
			else 
				echo 'Erfolgreich gespeichert';
		}
		
		//Lehre Feld setzen
		if(isset($_GET['lehre']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_GET['lvid']))
			{
				$lv_obj->lehre=($_GET['lehre']=='t'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					echo 'Erfolgreich gespeichert';
				else 
					echo 'Fehler beim Speichern:'.$lv_obj->errormsg;
			}
			else 
				echo 'Fehler beim Laden der LV:'.$lv_obj->errormsg;
		}
	
		//Zeugnis Feld setzen
		if(isset($_GET['zeugnis']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_GET['lvid']))
			{
				$lv_obj->zeugnis=($_GET['zeugnis']=='t'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					echo 'Erfolgreich gespeichert';
				else 
					echo 'Fehler beim Speichern:'.$lv_obj->errormsg;
			}
			else 
				echo 'Fehler beim Laden der LV:'.$lv_obj->errormsg;
		}
	
		//Sort Speichern
		if(isset($_POST['sort']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_GET['lvid']))
			{
				$lv_obj->sort=$_POST['sort'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					echo 'Erfolgreich gespeichert';
				else 
					echo 'Fehler beim Speichern:'.$lv_obj->errormsg;
			}
			else 
				echo 'Fehler beim Laden der LV:'.$lv_obj->errormsg;
		}
		
		//Incoming Speichern
		if(isset($_POST['incoming']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_GET['lvid']))
			{
				$lv_obj->incoming=$_POST['incoming'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					echo 'Erfolgreich gespeichert';
				else 
					echo 'Fehler beim Speichern:'.$lv_obj->errormsg;
			}
			else 
				echo 'Fehler beim Laden der LV:'.$lv_obj->errormsg;
		}
	
		//FBK Speichern
		if(isset($_POST['fbk']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_GET['lvid']))
			{
				$lv_obj->koordinator=$_POST['fbk'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					echo 'Erfolgreich gespeichert';
				else 
					echo 'Fehler beim Speichern:'.$lv_obj->errormsg;
			}
			else 
				echo 'Fehler beim Laden der LV:'.$lv_obj->errormsg;
		}
	
		//Projektarbeit Feld setzen
		if(isset($_GET['projektarbeit']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_GET['lvid']))
			{
				$lv_obj->projektarbeit=($_GET['projektarbeit']=='t'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					echo 'Erfolgreich gespeichert';
				else 
					echo 'Fehler beim Speichern:'.$lv_obj->errormsg;
			}
			else 
				echo 'Fehler beim Laden der LV:'.$lv_obj->errormsg;
		}
	}
	else 
	{
		echo 'Sie haben keine Schreibrechte fuer diese Seite';
	}
}

//Fachbereichskoordinatoren holen
if($stg_kz!='')
{
	$where = "oe_kurzbz=(SELECT oe_kurzbz FROM public.tbl_studiengang WHERE studiengang_kz='$stg_kz' LIMIT 1)";
	$where2="studiengang_kz='$stg_kz'";
	$tables='lehre.tbl_lehrveranstaltung';
}
else 
{
	$where = "fachbereich_kurzbz='$fachbereich_kurzbz'";
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

//Wenn nicht admin, dann nur die aktiven anzeigen
$aktiv='';
$isaktiv=trim($isaktiv);
if(!$write_admin)
	$aktiv = ' AND tbl_lehrveranstaltung.aktiv=true';
else 
{
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
}

if($fachbereich_kurzbz !='')
	$sql_query="SELECT distinct tbl_lehrveranstaltung.* FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrfach WHERE
	tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
	tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND
	tbl_lehrfach.fachbereich_kurzbz='".addslashes($fachbereich_kurzbz)."'";
else
	$sql_query="SELECT * FROM lehre.tbl_lehrveranstaltung WHERE true";

if($stg_kz!='')
	$sql_query.= " AND tbl_lehrveranstaltung.studiengang_kz='$stg_kz'";

$sql_query.=" AND tbl_lehrveranstaltung.semester='$semester' $aktiv ORDER BY tbl_lehrveranstaltung.bezeichnung";

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
		$outp.="<OPTION value='$stg->studiengang_kz' ".($stg->studiengang_kz==$stg_kz?'selected':'').">$stg->kuerzel - $stg->kurzbzlang</OPTION>";
	}
	$s[$stg->studiengang_kz]->max_sem=9; // $stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
}
$s['']->max_sem=9;

$outp.='</SELECT>';

//Semester DropDown
$outp.= ' Semester <SELECT name="semester">';
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
		$outp.= "<OPTION value='$fb->fachbereich_kurzbz' $selected>$fb->fachbereich_kurzbz</OPTION>";
}

$outp.= '</SELECT>';

if($write_admin)
{
	//Aktiv DropDown
	$outp.= ' Aktiv <SELECT name="isaktiv" id="isaktiv">';
	$outp.= "<OPTION value=''".($isaktiv==''?' selected':'').">-- Alle --</OPTION>";
	$outp.= "<OPTION value='true '".($isaktiv=='true'?'selected':'').">-- Aktiv --</OPTION>";
	$outp.= "<OPTION value='false '".($isaktiv=='false'?'selected':'').">-- Nicht aktiv --</OPTION>";
	$outp.= '</SELECT>';
}
else 
{
	$isaktiv='aktiv';
}
$outp.= '<input type="submit" value="Anzeigen">';
$outp .="</form>";



echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
		<title>Lehrveranstaltung Verwaltung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script type="text/javascript">
			var isaktiv="'.$isaktiv.'";
			function checksubmit()
			{		
				if(document.getElementById("select_stg_kz").value==\'\' && document.getElementById("select_fachbereich_kurzbz").value==\'\')
				{
					alert("Studiengang und Fachbereich dürfen nicht gleichzeitig auf \'Alle\' gesetzt sein");
					return false;
				}
				else
					return true;
		
			}
		</script>
	</head>
	<body class="Background_main">
	';
echo "<H2>Lehrveranstaltung Verwaltung (".(isset($s[$stg_kz]->kurzbz)?$s[$stg_kz]->kurzbz:$fachbereich_kurzbz)." - ".$semester.")</H2>";

echo '<table width="100%"><tr><td>';
echo $outp;

echo '</td><td>';
//Neu Button
if($write_admin)
	echo "<input type='button' onclick='parent.lv_detail.location=\"lehrveranstaltung_details.php?neu=true&stg_kz=$stg_kz&semester=$semester\"' value='Neu'/>";
echo '</td></tr></table>';

if ($result_lv!=0)
{
	$num_rows=$db->db_num_rows($result_lv);
	echo "<h3>&Uuml;bersicht - $num_rows LVAs</h3>
	<table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>
	<thead>
	<tr class='liste'>";
	echo "<th class='table-sortable:default'>ID</th>
		  <th class='table-sortable:default'>Kurzbz</th>
		  <th class='table-sortable:default'>Bezeichnung</th>
		  <th class='table-sortable:default'>LF</th>
		  <th class='table-sortable:default'>Stg</th>
		  <th class='table-sortable:default'>Orgform</th>
		  <th class='table-sortable:default' title='Semesterstunden'>SS</th>
		  <th class='table-sortable:default'>ECTS</th>
		  <th class='table-sortable:default'>Lehre</th>
		  <th class='table-sortable:default' title='Verzeichnisname im Filesystem'>LehreVz</th>
		  <th class='table-sortable:default'>Aktiv</th>
		  <th class='table-sortable:numeric' title='Sortierreihenfolge der LV am Zeugnis'>Sort</th>
		  <th class='table-sortable:numeric' title='Anzahl der Incoming die an dieser LV teilnehmen duerfen'>Incoming</th>
		  <th class='table-sortable:default'>Zeugnis</th>
		  <th class='table-sortable:default' title='Soll diese Lehrveranstaltung bei Diplom-/Bachelorarbeit ausgewaehlt werden koennen?'>BA/DA</th>
		  <th class='table-sortable:default'>Koordinator</th>
		  <th class='table-sortable:default'>LV-Info</th>\n";
	echo "</tr></thead>";
	echo "<tbody>";
	for($i=0;$i<$num_rows;$i++)
	{
		$row=$db->db_fetch_object($result_lv);
		echo "<tr>";
		//ID
		echo "<td align='right'>";
		
		if($write_admin)		
			echo "<a href='lehrveranstaltung_details.php?lv_id=$row->lehrveranstaltung_id' target='lv_detail'>$row->lehrveranstaltung_id</a>";
		else		
			echo "$row->lehrveranstaltung_id";
		echo "</td>";
		//Kurzbz
		echo "<td>$row->kurzbz</td>";
		//Bezeichnung
		echo "<td>";
		if($write_admin)
			echo "<a href='lehrveranstaltung_details.php?lv_id=$row->lehrveranstaltung_id' target='lv_detail'>$row->bezeichnung</a>";
		else
			echo $row->bezeichnung;
		echo "</td>";
		echo "<td>".$row->lehrform_kurzbz."</td>";
		echo "<td>".$s[$row->studiengang_kz]->kurzbz."</td>";
		//Organisationsform
		echo "<td style='white-space:nowrap;'>";
		if($write_admin)
		{
			echo "<form action='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&fachbereich_kurzbz=$fachbereich_kurzbz&isaktiv=$isaktiv' method='POST'>";
			echo "<SELECT name='orgform'>";
			echo "<option value=''>-- Keine Auswahl --</option>";
			
			$qry_orgform = "SELECT * FROM bis.tbl_orgform WHERE orgform_kurzbz NOT IN ('VBB', 'ZGS') ORDER BY orgform_kurzbz";
			if($result_orgform = $db->db_query($qry_orgform))
			{
				while($row_orgform = $db->db_fetch_object($result_orgform))
				{
					if($row_orgform->orgform_kurzbz==$row->orgform_kurzbz)
						$selected='selected';
					else 
						$selected='';
					echo "<option value='$row_orgform->orgform_kurzbz' $selected>$row_orgform->bezeichnung</option>";
				}
			}
						
			echo "</SELECT><input type='submit' value='ok' name='submitorg'></form>";

		}
		else 
		{
			echo $row->orgform_kurzbz;
		}
		echo "</td>";
		//Semesterstunden
		echo "<td>$row->semesterstunden</td>";
		//ECTS
		echo "<td>$row->ects</td>";
		//Lehre
		echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&lehre=$row->lehre&isaktiv=$isaktiv&fachbereich_kurzbz=$fachbereich_kurzbz'><img src='../../skin/images/".($row->lehre=='t'?'true.png':'false.png')."' height='20'></a></td>";
		//LehreVz
		echo "<td  style='white-space:nowrap;'>";
		if($write_admin)
			echo "<form action='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&isaktiv=$isaktiv&fachbereich_kurzbz=$fachbereich_kurzbz' method='POST'><input type='text' value='$row->lehreverzeichnis' size='4' name='lehrevz'><input type='submit' value='ok'></form>";
		else
			echo $row->lehreverzeichnis;
		echo "</td>";
		//Aktiv
		echo "<td align='center'  style='white-space:nowrap;'>";
		if($write_admin)
			echo "<a href='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&aktiv=$row->aktiv&isaktiv=$isaktiv&fachbereich_kurzbz=$fachbereich_kurzbz'><img src='../../skin/images/".($row->aktiv=='t'?'true.png':'false.png')."' height='20'></a>";
		else
			echo ($row->aktiv?'Ja':'Nein');
		echo "</td>";
		//Sort
		echo "<td style='white-space:nowrap;'>";
		echo "<div style='display: none'>$row->sort</div>";
		echo "<form action='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&isaktiv=$isaktiv&fachbereich_kurzbz=$fachbereich_kurzbz' method='POST'><input type='text' value='$row->sort' size='4' name='sort'><input type='submit' value='ok'></form>";
		echo "</td>";
		//Incoming
		echo "<td style='white-space:nowrap;'>";
		echo "<div style='display: none'>$row->incoming</div>";
		echo "<form action='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&isaktiv=$isaktiv&fachbereich_kurzbz=$fachbereich_kurzbz' method='POST'><input type='text' value='$row->incoming' size='4' name='incoming'><input type='submit' value='ok'></form>";
		echo "</td>";
		//Zeugnis
		echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&zeugnis=$row->zeugnis&isaktiv=$isaktiv&fachbereich_kurzbz=$fachbereich_kurzbz'><img src='../../skin/images/".($row->zeugnis=='t'?'true.png':'false.png')."' height='20'></a></td>";
		//Projektarbeit
		echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&projektarbeit=$row->projektarbeit&isaktiv=$isaktiv&fachbereich_kurzbz=$fachbereich_kurzbz'><img src='../../skin/images/".($row->projektarbeit=='t'?'true.png':'false.png')."' height='20'></a></td>";
		//FBK
		echo "<td style='white-space:nowrap;'>";
		echo "<form action='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&fachbereich_kurzbz=$fachbereich_kurzbz&isaktiv=$isaktiv' method='POST'><SELECT name='fbk'>";
		echo "<option value=''>-- Keine Auswahl --</option>";
		foreach ($fbk as $fb_uid=>$fb_k)
		{
			if($fb_uid==$row->koordinator)
				$selected='selected';
			else
				$selected='';
			echo "<option value='$fb_uid' $selected>".$fb_k['nachname']." ".$fb_k['vorname']."</option>";
		}
		echo "</SELECT><input type='submit' value='ok' name='submitfbk'></form>";
		echo '</td>';
		echo '<td nowrap>';
		//LVInfo
		$lvinfo = new lvinfo();
		if(!$lvinfo->exists($row->lehrveranstaltung_id))
		{
			echo '
				<form action="'.$_SERVER['PHP_SELF'].'?lvid='.$row->lehrveranstaltung_id.'&stg_kz='.$stg_kz.'&semester='.$semester.'&fachbereich_kurzbz='.$fachbereich_kurzbz.'&isaktiv='.$isaktiv.'" method="POST">
					kopieren von id: <input type="text" size="3" name="source_id" value="" />
					<input type="submit" name="submitlvinfo" value="ok">
				</form>';
		}
		else 
			echo 'vorhanden';
		echo '</td>';
		echo "</tr>\n";
	}

}
else
	echo 'Kein Eintrag gefunden!';
?>
		</tbody>
		</table>
	</body>
</html>