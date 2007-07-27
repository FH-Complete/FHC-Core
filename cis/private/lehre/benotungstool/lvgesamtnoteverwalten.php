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
require_once('../../../../include/studentnote.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/legesamtnote.class.php');
require_once('../../../../include/lvgesamtnote.class.php');

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

$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');
$uid = (isset($_GET['uid'])?$_GET['uid']:'');

//Kopfzeile
echo '<table class="tabcontent" height="100%">';
echo ' <tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
echo '<td class="ContentHeader"><font class="ContentHeader">&nbsp;"Kreuzerl"-Tool';
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
	$stsem_content.= "<OPTION value='studentenpunkteverwalten.php?lvid=$lvid&stsem=$studiensemester->studiensemester_kurzbz' $selected>$studiensemester->studiensemester_kurzbz</OPTION>\n";
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
			echo "<OPTION value='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->lfbez-$row->lehrform_kurzbz - $gruppen $lektoren</OPTION>\n";
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
echo '<table><tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
echo "<td>\n";
echo "<b>$lv_obj->bezeichnung</b><br>";

if($lehreinheit_id=='')
	die('Es wurde keine passende Lehreinheit in diesem Studiensemester gefunden');

//Menue
include("menue.inc.php");


// lvgesamtnote für studenten speichern
if (isset($_REQUEST["submit"]) && ($_POST["student_uid"] != '')){
	
	$jetzt = date("Y-m-d H:i:s");	
	$student_uid = $_POST["student_uid"];
	$lvid = $_REQUEST["lvid"];
	$lvgesamtnote = new lvgesamtnote($conn);
    if (!$lvgesamtnote->load($lvid, $student_uid, $stsem))
    {
		$lvgesamtnote->student_uid = $student_uid;
		$lvgesamtnote->lehrveranstaltung_id = $lvid;
		$lvgesamtnote->studiensemester_kurzbz = $stsem;
		$lvgesamtnote->note = $_POST["note"];
		$lvgesamtnote->mitarbeiter_uid = $user;
		$lvgesamtnote->benotungsdatum = $jetzt;
		$lvgesamtnote->freigabedatum = null;
		$lvgesamtnote->bemerkung = null;
		$lvgesamtnote->updateamum = null;
		$lvgesamtnote->updatevon = null;
		$lvgesamtnote->insertamum = $jetzt;
		$lvgesamtnote->insertvon = $user;
		$new = true;
    }
    else
    {
		$lvgesamtnote->note = $_POST["note"];
		$lvgesamtnote->benotungsdatum = $jetzt;
		$lvgesamtnote->updateamum = $jetzt;
		$lvgesamtnote->updatevon = $user;
		$new = false;
	}
	if (!$lvgesamtnote->save($new))
		echo "<span class='error'>".$lvgesamtnote->errormsg."</span>";
}

echo "<h3>LV Gesamtnote verwalten</h3>";


//Studentenliste
echo "
<table>
";

$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$lehreinheit_id' ORDER BY semester, verband, gruppe, gruppe_kurzbz";

if($result_grp = pg_query($conn, $qry))
{
	while($row_grp = pg_fetch_object($result_grp))
	{
		echo "<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class='ContentHeader2'>UID</td>
				<td class='ContentHeader2'>Nachname</td>
				<td class='ContentHeader2'>Vorname</td>
				<td class='ContentHeader2'>LE-Noten (LE-ID)</td>
				<td class='ContentHeader2'></td>
				<td class='ContentHeader2'>LV-Gesamtnote</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>";
		if($row_grp->gruppe_kurzbz!='')
		{
				echo "
				<tr>
					<td colspan='4' align='center'><b>$row_grp->gruppe_kurzbz</b></td>
				</tr>";
				$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student JOIN public.tbl_benutzergruppe USING(uid) WHERE gruppe_kurzbz='".addslashes($row_grp->gruppe_kurzbz)."' ORDER BY nachname, vorname";
		}
		else
		{
			echo "
				<tr>
					<td colspan='5' align='center'><b>Verband $row_grp->verband ".($row_grp->gruppe!=''?"Gruppe $row_grp->gruppe":'')."</b></td>
				</tr>";
				$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student
				             WHERE studiengang_kz='$row_grp->studiengang_kz' AND
				             semester='$row_grp->semester' ".
							 ($row_grp->verband!=''?" AND trim(verband)=trim('$row_grp->verband')":'').
							 ($row_grp->gruppe!=''?" AND trim(gruppe)=trim('$row_grp->gruppe')":'').
				            " ORDER BY nachname, vorname";
		}
		
        if($result_stud = pg_query($conn, $qry_stud))
		{
			$i=1;
			while($row_stud = pg_fetch_object($result_stud))
			{
    				
				//$studentnote = new studentnote($conn,$lehreinheit_id,$stsem,$row_stud->uid);
				

				
				/*
    			$legesamtnote = new legesamtnote($conn, $lehreinheit_id);
    			
    			if (!$legesamtnote->load($row_stud->uid,$lehreinheit_id))
				{    				
    				$note_le = null;
    			}
    			else
    				$note_le = $legesamtnote->note;
    			if ($lvgesamtnote = new lvgesamtnote($conn, $lvid,$row_stud->uid,$stsem))
    			{
    				$note_lv = $lvgesamtnote->note;
    			}
    			else
    				$note_lv = null;
				
				if ($note_lv)
					$note_vorschlag = $note_lv;
				else
					$note_vorschlag = $note_le;
				*/
				
				echo "
				<tr class='liste".($i%2)."'>
					<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->uid</a></td>
					<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->nachname</a></td>
					<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->vorname</a></td>";
					
				
				$note_les_str = '';
				$le_anz = 0;
				$note_le = 0;
				$le = new lehreinheit($conn);
				$le->load_lehreinheiten($lvid, $stsem);
				foreach($le->lehreinheiten as $l)				
				{				
					$legesamtnote = new legesamtnote($conn, $l->lehreinheit_id);
	    			
	    			if (!$legesamtnote->load($row_stud->uid,$l->lehreinheit_id))
					{    				
	    				//$note_les_str .= "- (".$l->lehreinheit_id.")";
	    			}
	    			else
	    			{
	    				$note_le += $legesamtnote->note;
	    				$le_anz += 1;
	    				$note_les_str .= $legesamtnote->note." (".$l->lehreinheit_id.") ";
	    			}
	    		}
	    			
    			if ($lvgesamtnote = new lvgesamtnote($conn, $lvid,$row_stud->uid,$stsem))
    			{
    				$note_lv = $lvgesamtnote->note;
    			}
    			else
    				$note_lv = null;
				
				if ($note_lv)
					$note_vorschlag = $note_lv;
				else if ($le_anz > 0)
					$note_vorschlag = round($note_le/$le_anz);
				else
					$note_vorschlag = null;
							
								
				
				echo "<td>$note_les_str</td>";
				echo "<form name='$row_stud->uid' method='POST' action='lvgesamtnoteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&stsem=$stsem'><td><input type='hidden' name='student_uid' value='$row_stud->uid'><input type='text' size='1' value='$note_vorschlag' name='note'><input type='submit' name='submit' value='->'></td></form>";
					
				echo "<td>$note_lv</td>";				
				echo "</tr>";
				$i++;
			}
		}
	}
}
echo "</table>";

?>
</td></tr>
</table>
</body>
</html>
