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

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$s=new studiengang($conn);
$s->getAll('typ, kurzbz', false);
$studiengang=$s->result;

$user = get_uid();

if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
else
	$stg_kz=0;
if (isset($_GET['semester']) || isset($_POST['semester']))
	$semester=(isset($_GET['semester'])?$_GET['semester']:$_POST['semester']);
else
	$semester=0;

if(!is_numeric($stg_kz))
	$stg_kz=0;

if(!is_numeric($semester))
	$semester=0;

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $stg_kz, 'suid'))
	die('Sie haben keine Berechtigung für diesen Studiengang');

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
}
	
//Fachbereichskoordinatoren holen
$qry = "
SELECT 
	distinct
	vorname, 
	nachname, 
	uid 
FROM 
	campus.vw_mitarbeiter JOIN 
	(SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='fbk' AND studiengang_kz='257' 
	 UNION 
	 SELECT koordinator as uid from lehre.tbl_lehrveranstaltung WHERE studiengang_kz='257') as a USING(uid) ORDER BY nachname, vorname";

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
	$aktiv = ' AND aktiv=true';

$sql_query="SELECT * FROM lehre.tbl_lehrveranstaltung
	WHERE studiengang_kz='$stg_kz' AND semester='$semester' $aktiv ORDER BY bezeichnung";

if(!$result_lv = pg_query($conn, $sql_query))
	die("Lehrveranstaltung not found!");

//Studiengang DropDown
$outp='';
$s=array();
$outp.="<SELECT name='stg_kz'>";
foreach ($studiengang as $stg)
{
	if($rechte->isBerechtigt('admin', $stg->studiengang_kz, 'suid') || $rechte->isBerechtigt('assistenz', $stg->studiengang_kz, 'suid'))
	{
		$outp.="<OPTION onclick=\"window.location.href = '".$_SERVER['PHP_SELF']."?stg_kz=$stg->studiengang_kz&semester=$semester'\" ".($stg->studiengang_kz==$stg_kz?'selected':'').">$stg->kuerzel - $stg->bezeichnung</OPTION>";
		$s[$stg->studiengang_kz]->max_sem=$stg->max_semester;
		$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
	}
}

$outp.='</SELECT>';

//Semester
$outp.= '<BR>Semester: -- ';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
	$outp.= '<A href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&semester='.$i.'">'.$i.'</A> -- ';

echo '<html>
	<head>
	<title>Lehrveranstaltung Verwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body class="Background_main">
	';

echo "<H2>Lehrveranstaltung Verwaltung (".$s[$stg_kz]->kurzbz." - ".$semester.")</H2>";

echo '<table width="100%"><tr><td>';
echo $outp;
echo '</td><td>';
//Neu Button
if($rechte->isBerechtigt('admin'))
	echo "<input type='button' onclick='parent.detail.location=\"lehrveranstaltung_details.php?neu=true&stg_kz=$stg_kz&semester=$semester\"' value='Neu'/>";
echo '</td></tr></table>';

echo "<h3>&Uuml;bersicht</h3>
	<table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>
	<thead>
	<tr class='liste'>";

if ($result_lv!=0)
{
	$num_rows=pg_num_rows($result_lv);
	echo "<th class='table-sortable:default'>ID</th>
		  <th class='table-sortable:default'>Kurzbz</th>
		  <th class='table-sortable:default'>Bezeichnung</th>
		  <th class='table-sortable:default'>SS</th>
		  <th class='table-sortable:default'>ECTS</th>
		  <th class='table-sortable:default'>Lehre</th>
		  <th class='table-sortable:default'>LehreVz</th>
		  <th class='table-sortable:default'>Aktiv</th>
		  <th class='table-sortable:numeric'>Sort</th>
		  <th class='table-sortable:default'>Zeugnis</th>\n
		  <th class='table-sortable:default'>FBK</th>\n";
	echo "</tr></thead>";
	echo "<tbody>";
	for($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object($result_lv);
		echo "<tr>";
		//ID
		echo "<td align='right'>$row->lehrveranstaltung_id</td>";
		//Kurzbz
		echo "<td>$row->kurzbz</td>";
		//Bezeichnung
		echo "<td>";
		if($rechte->isBerechtigt('admin'))
			echo "<a href='lehrveranstaltung_details.php?lv_id=$row->lehrveranstaltung_id' target='detail'>$row->bezeichnung</a>";
		else 
			echo $row->bezeichnung;
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
		//FBK
		echo "<td style='white-space:nowrap;'>";
		echo "<form action='".$_SERVER['PHP_SELF']."?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester' method='POST'><SELECT name='fbk'>";
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