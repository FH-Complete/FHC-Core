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
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/fachbereich.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$s=new studiengang($conn);
$s->getAll('typ, kurzbz', false);
$studiengang=$s->result;

$user = get_uid();

if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
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

$fachbereich_kurzbz = (isset($_REQUEST['fachbereich_kurzbz'])?$_REQUEST['fachbereich_kurzbz']:'');

//Wenn kein Fachbereich und kein Studiengang gewaehlt wurde
//dann wird der Studiengang auf 0 gesetzt da sonst die zu ladende liste zu lang wird
if($fachbereich_kurzbz=='' && $stg_kz=='')
	$stg_kz='0';

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', null, 'suid', $fachbereich_kurzbz))
	die('Sie haben keine Berechtigung f�r diesen Studiengang');

if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
{
	if($rechte->isBerechtigt('admin', $stg_kz, 'suid'))
	{
		//Lehrevz Speichern
		if(isset($_POST['lehrevz']))
		{
			$qry = "UPDATE lehre.tbl_lehrveranstaltung SET lehreverzeichnis='".addslashes($_POST['lehrevz'])."' WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
			if(!pg_query($conn, $qry))
				echo "Fehler beim Speichern!";
			else
				echo "Erfolgreich gespeichert";
		}

		//Aktiv Feld setzen
		if(isset($_GET['aktiv']))
		{
			$qry = "UPDATE lehre.tbl_lehrveranstaltung SET aktiv=".($_GET['aktiv']=='t'?'false':'true')." WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
			if(!pg_query($conn, $qry))
				echo "Fehler beim Speichen!";
			else
				echo "Erfolgreich gespeichert";
		}
		//Organisationsform Speichern
		if(isset($_POST['orgform']))
		{
			$qry = "UPDATE lehre.tbl_lehrveranstaltung SET orgform_kurzbz=".($_POST['orgform']==''?'null':"'".addslashes($_POST['orgform'])."'")." WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
			if(!pg_query($conn, $qry))
				echo "Fehler beim Speichern!";
			else
				echo "Erfolgreich gespeichert";
		}
	}
	
	
	//Lehre Feld setzen
	if(isset($_GET['lehre']))
	{
		$qry = "UPDATE lehre.tbl_lehrveranstaltung SET lehre=".($_GET['lehre']=='t'?'false':'true')." WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
		if(!pg_query($conn, $qry))
			echo "Fehler beim Speichen!";
		else
			echo "Erfolgreich gespeichert";
	}

	//Zeugnis Feld setzen
	if(isset($_GET['zeugnis']))
	{
		$qry = "UPDATE lehre.tbl_lehrveranstaltung SET zeugnis=".($_GET['zeugnis']=='t'?'false':'true')." WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
		if(!pg_query($conn, $qry))
			echo "Fehler beim Speichen!";
		else
			echo "Erfolgreich gespeichert";
	}

	//Sort Speichern
	if(isset($_POST['sort']))
	{
		$qry = "UPDATE lehre.tbl_lehrveranstaltung SET sort='".addslashes($_POST['sort'])."' WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
		if(!pg_query($conn, $qry))
			echo "Fehler beim Speichern!";
		else
			echo "Erfolgreich gespeichert";
	}

	//FBK Speichern
	if(isset($_POST['fbk']))
	{
		$qry = "UPDATE lehre.tbl_lehrveranstaltung SET koordinator=".($_POST['fbk']==''?'null':"'".addslashes($_POST['fbk'])."'")." WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
		if(!pg_query($conn, $qry))
			echo "Fehler beim Speichern!";
		else
			echo "Erfolgreich gespeichert";
	}

	//Projektarbeit Feld setzen
	if(isset($_GET['projektarbeit']))
	{
		$qry = "UPDATE lehre.tbl_lehrveranstaltung SET projektarbeit=".($_GET['projektarbeit']=='t'?'false':'true')." WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
		//echo $qry;
		if(!pg_query($conn, $qry))
			echo "Fehler beim Speichen!";
		else
			echo "Erfolgreich gespeichert";
	}
}

//Fachbereichskoordinatoren holen
if($stg_kz!='')
{
	$where = "studiengang_kz='$stg_kz'";
	$where2=$where;
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
	 SELECT koordinator as uid from $tables WHERE $where2) as a USING(uid) ORDER BY nachname, vorname";

$fbk = array();
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$fbk[$row->uid]['vorname']=$row->vorname;
		$fbk[$row->uid]['nachname']=$row->nachname;
	}
}

//Lehrveranstaltungen holen

//Wenn nicht admin, dann nur die aktiven anzeigen
$aktiv='';
if(!$rechte->isBerechtigt('admin'))
	$aktiv = ' AND tbl_lehrveranstaltung.aktiv=true';

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

if(!$result_lv = pg_query($conn, $sql_query))
	die("Lehrveranstaltung not found!");

//Studiengang DropDown
$outp='';
$s=array();
$outp.="<form action='".$_SERVER['PHP_SELF']."' method='GET' onsubmit='return checksubmit();'>";
$outp.="Studiengang <SELECT name='stg_kz' id='select_stg_kz'>";
$outp.="<OPTION value='' ".($stg_kz==''?'selected':'').">-- Alle --</OPTION>";
foreach ($studiengang as $stg)
{
	if($rechte->isBerechtigt('admin', $stg->studiengang_kz, 'suid') || $rechte->isBerechtigt('assistenz', $stg->studiengang_kz, 'suid'))
	{
		$outp.="<OPTION value='$stg->studiengang_kz' ".($stg->studiengang_kz==$stg_kz?'selected':'').">$stg->kuerzel - $stg->kurzbzlang</OPTION>";
	}
	$s[$stg->studiengang_kz]->max_sem=8; // $stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
}
$s['']->max_sem=8;

$outp.='</SELECT>';

//Semester DropDown
$outp.= 'Semester <SELECT name="semester">';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
	$outp.="<OPTION value='$i' ".($i==$semester?'selected':'').">$i</OPTION>";
$outp.='</SELECT>';

//Fachbereich DropDown
$outp.= 'Fachbereich <SELECT name="fachbereich_kurzbz" id="select_fachbereich_kurzbz">';
$fachb = new fachbereich($conn);
$fachb->getAll();
$outp.= "<OPTION value='' ".($fachbereich_kurzbz==''?'selected':'').">-- Alle --</OPTION>";
foreach ($fachb->result as $fb)
{
	if($fachbereich_kurzbz==$fb->fachbereich_kurzbz)
		$selected = 'selected';
	else
		$selected = '';

	if($rechte->isBerechtigt('admin', 0, 'suid') ||
	   $rechte->isBerechtigt('assistenz', null, 'suid', $fb->fachbereich_kurzbz) ||
	   $rechte->isBerechtigt('admin', null, 'suid', $fb->fachbereich_kurzbz))
	$outp.= "<OPTION value='$fb->fachbereich_kurzbz' $selected>$fb->fachbereich_kurzbz</OPTION>";
}
$outp.= '</SELECT>';
$outp.= '<input type="submit" value="Anzeigen">';
$outp .="</form>";

echo "<H2>Lehrveranstaltung Verwaltung (".(isset($s[$stg_kz]->kurzbz)?$s[$stg_kz]->kurzbz:$fachbereich_kurzbz)." - ".$semester.")</H2>";


echo '<html>
	<head>
	<title>Lehrveranstaltung Verwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	<script language="Javascript">
	function checksubmit()
	{
		//alert(document.getElementById("select_stg_kz").value+" : "+document.getElementById("select_fachbereich_kurzbz").value);
		//return false;

		if(document.getElementById("select_stg_kz").value==\'\' && document.getElementById("select_fachbereich_kurzbz").value==\'\')
		{
			alert("Studiengang und Fachbereich d�rfen nicht gleichzeitig auf \'Alle\' gesetzt sein");
			return false;
		}
		else
			return true;

	}
	</script>
	</head>
	<body class="Background_main">
	';

echo '<table width="100%"><tr><td>';
echo $outp;
echo '</td><td>';
//Neu Button
if($rechte->isBerechtigt('admin'))
	echo "<input type='button' onclick='parent.lv_detail.location=\"lehrveranstaltung_details.php?neu=true&stg_kz=$stg_kz&semester=$semester\"' value='Neu'/>";
echo '</td></tr></table>';

if ($result_lv!=0)
{
	$num_rows=pg_num_rows($result_lv);
	echo "<h3>&Uuml;bersicht - $num_rows LVAs</h3>
	<table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>
	<thead>
	<tr class='liste'>";
	echo "<th class='table-sortable:default'>ID</th>
		  <th class='table-sortable:default'>Kurzbz</th>
		  <th class='table-sortable:default'>Bezeichnung</th>
		  <th class='table-sortable:default'>Lehrform</th>
		  <th class='table-sortable:default'>Stg</th>
		  <th class='table-sortable:default'>Organisationsform</th>
		  <th class='table-sortable:default'>SS</th>
		  <th class='table-sortable:default'>ECTS</th>
		  <th class='table-sortable:default'>Lehre</th>
		  <th class='table-sortable:default'>LehreVz</th>
		  <th class='table-sortable:default'>Aktiv</th>
		  <th class='table-sortable:numeric'>Sort</th>
		  <th class='table-sortable:default'>Zeugnis</th>
		  <th class='table-sortable:default'>BA/DA</th>
		  <th class='table-sortable:default'>FBK</th>\n";
	echo "</tr></thead>";
	echo "<tbody>";
	for($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object($result_lv);
		echo "<tr>";
		//ID
		echo "<td align='right'>";
		
		if($rechte->isBerechtigt('admin'))		
			echo "<a href='lehrveranstaltung_details.php?lv_id=$row->lehrveranstaltung_id' target='lv_detail'>$row->lehrveranstaltung_id</a>";
		else		
			echo "$row->lehrveranstaltung_id";
		echo "</td>";
		//Kurzbz
		echo "<td>$row->kurzbz</td>";
		//Bezeichnung
		echo "<td>";
		if($rechte->isBerechtigt('admin'))
			echo "<a href='lehrveranstaltung_details.php?lv_id=$row->lehrveranstaltung_id' target='lv_detail'>$row->bezeichnung</a>";
		else
			echo $row->bezeichnung;
		echo "</td>";
		echo "<td>".$row->lehrform_kurzbz."</td>";
		echo "<td>".$s[$row->studiengang_kz]->kurzbz."</td>";
		//Organisationsform
		echo "<td style='white-space:nowrap;'>";
		if($rechte->isBerechtigt('admin'))
		{
			echo "<form action='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&fachbereich_kurzbz=$fachbereich_kurzbz' method='POST'>";
			echo "<SELECT name='orgform'>";
			echo "<option value=''>-- Keine Auswahl --</option>";
			if($row->orgform_kurzbz=='BB')
			{
				echo "<option value='BB' selected='selected'>Berufsbegleitend</option>";
			}
			else 
			{
				echo "<option value='BB'>Berufsbegleitend</option>";
			}
			if($row->orgform_kurzbz=='VZ')
			{
				echo "<option value='VZ' selected='selected'>Vollzeit</option>";
			}
			else 
			{
				echo "<option value='VZ'>Vollzeit</option>";
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
		echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&lehre=$row->lehre'><img src='../../skin/images/".($row->lehre=='t'?'true.gif':'false.gif')."'></a></td>";
		//LehreVz
		echo "<td  style='white-space:nowrap;'>";
		if($rechte->isBerechtigt('admin'))
			echo "<form action='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester' method='POST'><input type='text' value='$row->lehreverzeichnis' size='4' name='lehrevz'><input type='submit' value='ok'></form>";
		else
			echo $row->lehreverzeichnis;
		echo "</td>";
		//Aktiv
		echo "<td align='center'  style='white-space:nowrap;'>";
		if($rechte->isBerechtigt('admin'))
			echo "<a href='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&aktiv=$row->aktiv'><img src='../../skin/images/".($row->aktiv=='t'?'true.gif':'false.gif')."'></a>";
		else
			echo ($row->aktiv?'Ja':'Nein');
		echo "</td>";
		//Sort
		echo "<td style='white-space:nowrap;'>";
		echo "<div style='display: none'>$row->sort</div>";
		echo "<form action='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester' method='POST'><input type='text' value='$row->sort' size='4' name='sort'><input type='submit' value='ok'></form>";
		echo "</td>";
		//Zeugnis
		echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&zeugnis=$row->zeugnis'><img src='../../skin/images/".($row->zeugnis=='t'?'true.gif':'false.gif')."'></a></td>";
		//Projektarbeit
		echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&projektarbeit=$row->projektarbeit'><img src='../../skin/images/".($row->projektarbeit=='t'?'true.gif':'false.gif')."'></a></td>";
		//FBK
		echo "<td style='white-space:nowrap;'>";
		echo "<form action='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&fachbereich_kurzbz=$fachbereich_kurzbz' method='POST'><SELECT name='fbk'>";
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
		echo "</td>";
		echo "</tr>\n";
	}

}
else
	echo "Kein Eintrag gefunden!";
?>
</tbody>
</table>

<br>
</body>
</html>