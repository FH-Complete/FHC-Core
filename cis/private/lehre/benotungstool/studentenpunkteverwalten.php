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
require_once('../../../../include/datum.class.php');

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
			echo "<OPTION value='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->lfbez-$row->lehrform_kurzbz - $gruppen $lektoren</OPTION>\n";
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
echo "\n<!--Menue-->\n";
echo "<br>
<a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Verwaltung</font>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Anwesenheits- und Übersichtstabelle</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Studentenpunkte verwalten</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Statistik</font></a>
<br><br>
<!--Menue Ende-->\n";


echo "<h3>Studentenpunkte verwalten</h3>";
if(isset($_POST['submit']))
{
	$error=false;
	$punkte = (isset($_POST['punkte'])?str_replace(',','.',$_POST['punkte']):'');
	if(isset($punkte) && is_numeric($punkte))
	{
		$ueb_obj = new uebung($conn);
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

		$bsp_obj = new beispiel($conn);

		if($bsp_obj->load_beispiel($uebung_id))
		{
			foreach ($bsp_obj->beispiele as $row)
			{
					$stud_bsp_obj = new beispiel($conn);

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
			echo "<span class='error'>Es konnten nicht alle Daten gespeichert werden</span>";
		else
			echo "Die Daten wurden erfolgreich gespeichert<br>";

	}
	else
	{
		echo "<span class='error'>Punkte sind ungueltig</span>";
	}
}

if(isset($_GET['uid']) && $_GET['uid']!='')
{
	//Punkte eintragen
	$uid = addslashes($_GET['uid']);

	$qry_stud = "SELECT vorname, nachname, uid FROM campus.vw_student WHERE uid='$uid'";

	if(!$result_stud = pg_query($conn, $qry_stud))
		die('Fehler beim laden des Studenten');

	if(!$row_stud = pg_fetch_object($result_stud))
		die('Student wurde nicht gefunden');

	echo "<b>$row_stud->vorname $row_stud->nachname</b><br>\n";

	$uebung_obj = new uebung($conn);
	$uebung_obj->load_uebung($lehreinheit_id);
	if(count($uebung_obj->uebungen)>0)
	{
		echo "<table width='100%'><tr><td valign='top'>";
		echo "<br>Wählen Sie bitte eine Kreuzerlliste aus: <SELECT name='uebung' onChange=\"MM_jumpMenu('self',this,0)\">\n";
		foreach ($uebung_obj->uebungen as $row)
		{
			if($uebung_id=='')
				$uebung_id=$row->uebung_id;

			if($uebung_id == $row->uebung_id)
				$selected = 'selected';
			else
				$selected = '';
			echo "<OPTION value='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id&uid=$uid' $selected>";
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

	$ueb_obj = new uebung($conn);
	if($ueb_obj->load_studentuebung($uid, $uebung_id))
	{
		$anmerkung = $ueb_obj->anmerkung;
		$mitarbeit = $ueb_obj->mitarbeitspunkte;
	}
	else
	{
		$anmerkung = '';
		$mitarbeit = 0;
	}

	echo "
	<form method='POST' action='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$uid'>
	<table width='100%'><tr><td valign='top'>
	Anmerkungen:<br>
	<textarea name='anmerkung' cols=50 rows=5>".htmlentities($anmerkung)."</textarea>
	<br><br>
	<table border='1'>
	<tr>
		<td class='ContentHeader2'>Beispiel</td>
	    <td class='ContentHeader2'>Vorbereitet</td>
	    <td class='ContentHeader2'>Nicht vorbereitet</td>
	    <td class='ContentHeader2'>Probleme</td>
	    <td class='ContentHeader2'>Punkte</td>
	</tr>";

	$bsp_obj = new beispiel($conn);
	$bsp_obj->load_beispiel($uebung_id);

	foreach ($bsp_obj->beispiele as $row)
	{
		$stud_bsp_obj = new beispiel($conn);
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

	echo "
	</td><td valign='top' algin='right'>";

	//Gesamtpunkte diese Kreuzerlliste
	$qry = "SELECT sum(punkte) as punktegesamt FROM campus.tbl_beispiel WHERE uebung_id='$uebung_id'";
	$punkte_gesamt=0;
	if($result=pg_query($conn, $qry))
		if($row = pg_fetch_object($result))
			$punkte_gesamt = $row->punktegesamt;

	//Eingetragen diese Kreuzerlliste
	$qry = "SELECT sum(punkte) as punkteeingetragen FROM campus.tbl_beispiel JOIN campus.tbl_studentbeispiel USING(beispiel_id) WHERE uebung_id='$uebung_id' AND student_uid='$uid' AND vorbereitet=true";
	$punkte_eingetragen=0;
	if($result=pg_query($conn, $qry))
		if($row = pg_fetch_object($result))
			$punkte_eingetragen = ($row->punkteeingetragen!=''?$row->punkteeingetragen:0);

	//Gesamtpunkte alle Kreuzerllisten
	$qry = "SELECT sum(tbl_beispiel.punkte) as punktegesamt_alle FROM campus.tbl_beispiel, campus.tbl_uebung
			WHERE tbl_uebung.uebung_id=tbl_beispiel.uebung_id AND
			tbl_uebung.lehreinheit_id='$lehreinheit_id'";
	$punkte_gesamt_alle=0;
	if($result=pg_query($conn, $qry))
		if($row = pg_fetch_object($result))
			$punkte_gesamt_alle = $row->punktegesamt_alle;

	//Eingetragen alle Kreuzerllisten
	$qry = "SELECT sum(tbl_beispiel.punkte) as punkteeingetragen_alle FROM campus.tbl_beispiel, campus.tbl_studentbeispiel, campus.tbl_uebung
			WHERE tbl_beispiel.beispiel_id = tbl_studentbeispiel.beispiel_id AND
			tbl_uebung.uebung_id=tbl_beispiel.uebung_id AND
			tbl_uebung.lehreinheit_id='$lehreinheit_id' AND
			tbl_studentbeispiel.student_uid='$uid' AND vorbereitet=true";
	$punkte_eingetragen_alle=0;
	if($result=pg_query($conn, $qry))
		if($row = pg_fetch_object($result))
			$punkte_eingetragen_alle = ($row->punkteeingetragen_alle!=''?$row->punkteeingetragen_alle:0);

	//Mitarbeitspunkte
	$qry = "SELECT sum(mitarbeitspunkte) as mitarbeitspunkte FROM campus.tbl_studentuebung JOIN campus.tbl_uebung USING(uebung_id)
			WHERE lehreinheit_id='$lehreinheit_id' AND student_uid='$uid'";
	$mitarbeit_alle=0;
	if($result=pg_query($conn, $qry))
		if($row = pg_fetch_object($result))
			$mitarbeit_alle = ($row->mitarbeitspunkte!=''?$row->mitarbeitspunkte:0);

	//Mitarbeitspunkte
	$qry = "SELECT mitarbeitspunkte FROM campus.tbl_studentuebung
			WHERE uebung_id='$uebung_id' AND student_uid='$uid'";
	$mitarbeit=0;
	if($result=pg_query($conn, $qry))
		if($row = pg_fetch_object($result))
			$mitarbeit = $row->mitarbeitspunkte;
	echo "
	<br>
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
			<td colspan='2' class='ContentHeader2'>Alle Kreuzerllisten bisher:</td>
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
			<td><input type='text' size=2 name='punkte' value='$mitarbeit'></td>
		</tr>
		</table>
		";


	echo "
	</td></tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			<input type='button' value='Zurück' onclick='history.back();'>
			<input type='submit' value='Speichern' name='submit'>
		</td>

	</tr>
	</table>

	</form>
	";

}
else
{
	//Studentenliste
	echo "Bitte w&auml;hlen Sie den Studenten aus.<br><br>";
	echo "
	<table width='80%'>
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
				</tr>
				<tr>
					<td class='ContentHeader2'>UID</td>
					<td class='ContentHeader2'>Nachname</td>
					<td class='ContentHeader2'>Vorname</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>";
			if($row_grp->gruppe_kurzbz!='')
			{
					echo "
					<tr>
						<td colspan='3' align='center'><b>$row_grp->gruppe_kurzbz</b></td>
					</tr>";
					$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student JOIN public.tbl_benutzergruppe USING(uid) WHERE gruppe_kurzbz='".addslashes($row_grp->gruppe_kurzbz)."' ORDER BY nachname, vorname";
			}
			else
			{
				echo "
					<tr>
						<td colspan='3' align='center'><b>Verband $row_grp->verband ".($row_grp->gruppe!=''?"Gruppe $row_grp->gruppe":'')."</b></td>
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
					echo "
					<tr class='liste".($i%2)."'>
						<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->uid</a></td>
						<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->nachname</a></td>
						<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->vorname</a></td>
					</tr>";
					$i++;
				}
			}
		}
	}
	echo "</table>";
}
?>
</td></tr>
</table>
</body>
</html>