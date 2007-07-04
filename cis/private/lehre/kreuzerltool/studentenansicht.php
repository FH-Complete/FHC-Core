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
  //-->
</script>
</head>

<body>
<?php
if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

$user = get_uid();

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

//Lehreinheiten laden zu denen der eingeloggte Student zugeteilt ist
//Bei Lehrverbaenden werden auch die uebergeordneten geladen
$qry = "SELECT distinct lehreinheit_id, kurzbz FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrfach USING(lehrfach_id) WHERE lehreinheit_id IN(
		SELECT lehreinheit_id FROM public.tbl_benutzergruppe JOIN lehre.tbl_lehreinheitgruppe USING (gruppe_kurzbz)
		WHERE tbl_benutzergruppe.uid='$user' AND
		tbl_lehreinheitgruppe.lehreinheit_id IN(
			SELECT lehreinheit_id FROM lehre.tbl_lehreinheit JOIN campus.tbl_uebung USING(lehreinheit_id)
			WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND tbl_lehreinheit.studiensemester_kurzbz='$stsem')
		UNION
		SELECT lehreinheit_id FROM public.tbl_student, lehre.tbl_lehreinheitgruppe
		WHERE tbl_student.student_uid='$user' AND
		tbl_student.studiengang_kz=tbl_lehreinheitgruppe.studiengang_kz AND
		trim(tbl_student.semester)=trim(tbl_lehreinheitgruppe.semester) AND
		(
			(
			  (
			  tbl_lehreinheitgruppe.verband<>'' AND
			  tbl_lehreinheitgruppe.gruppe<>'' AND
			  trim(tbl_lehreinheitgruppe.verband) = trim(tbl_student.verband) AND
			  trim(tbl_lehreinheitgruppe.gruppe) = trim(tbl_student.gruppe)
			  )
			  OR
			  (
			    tbl_lehreinheitgruppe.verband<>'' AND
			  	(
			  	trim(tbl_lehreinheitgruppe.gruppe)='' OR
			  	tbl_lehreinheitgruppe.gruppe is null
			  	)
			  	AND
			  	trim(tbl_lehreinheitgruppe.verband) = trim(tbl_student.verband)
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

echo "<br><b>Leistungsuebersicht f&uuml;r $name</b><br><br>";

$uebung_obj = new uebung($conn);
$uebung_obj->load_uebung($lehreinheit_id);
if(count($uebung_obj->uebungen)>0)
{
	echo "<table width='100%'><tr><td valign='top'>";
	echo "Wählen Sie bitte eine Kreuzerlliste aus: <SELECT name='uebung' onChange=\"MM_jumpMenu('self',this,0)\">\n";
	foreach ($uebung_obj->uebungen as $row)
	{
		if($uebung_id=='')
			$uebung_id=$row->uebung_id;

		if($uebung_id == $row->uebung_id)
			$selected = 'selected';
		else
			$selected = '';
		echo "<OPTION value='studentenansicht.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id' $selected>";
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
				}
				$stud_bsp_obj->vorbereitet = ($_POST['solved_'.$row->beispiel_id]==1?true:false);
				$stud_bsp_obj->probleme = (isset($_POST['problem_'.$row->beispiel_id])?true:false);
				$stud_bsp_obj->updateamum = date('Y-m-d H:i:s');
				$stud_bsp_obj->updatevon = $user;
				$stud_bsp_obj->student_uid = $user;
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
		echo "<span class='error'>Die &Auml;nderungen k&ouml;nnen nicht gespeichert werden, da diese Kreuzerlliste nicht freigegeben ist!</span>";
}

//********ANZEIGE DER EINGETRAGENEN KREUZERL***********
$uebung_obj = new uebung($conn);
$uebung_obj->load($uebung_id);
echo "Freigegeben von ".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon))." bis ".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis));
echo "<br><br><h3><u>$uebung_obj->bezeichnung</u></h3>";

$ueb_obj = new uebung($conn);
if($ueb_obj->load_studentuebung($user, $uebung_id))
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
<form method='POST' action='studentenansicht.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id'>
<table width='100%'>
	<tr>
		<td valign='top'><div style='width: 70%;'>
		".($anmerkung!=''?'<b>Anmerkungen:</b> '.htmlentities($anmerkung).'<br><br>':'')."
		</div>
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
	echo "<tr>
			<td>$row->bezeichnung</td>
			<td align='center'><input type='radio' name='solved_$row->beispiel_id' value='1' ".($vorbereitet?'checked':'')."></td>
			<td align='center'><input type='radio' name='solved_$row->beispiel_id' value='0' ".(!$vorbereitet?'checked':'')."></td>
			<td align='center'><input type='checkbox' name='problem_$row->beispiel_id' ".($probleme?'checked':'')."></td>
			<td align='center'>$row->punkte</td>
		</tr>";
}

//Speichern button nur Anzeigen wenn die Uebung Freigegeben ist
if($datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis)>time())
	echo "<tr><td align='right' colspan=5><input type='submit' value='Speichern' name='submit'></td></tr>";

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
$qry = "SELECT sum(punkte) as punkteeingetragen FROM campus.tbl_beispiel JOIN campus.tbl_studentbeispiel USING(beispiel_id) WHERE uebung_id='$uebung_id' AND student_uid='$user' AND vorbereitet=true";
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
		tbl_studentbeispiel.student_uid='$user' AND vorbereitet=true";
$punkte_eingetragen_alle=0;
if($result=pg_query($conn, $qry))
	if($row = pg_fetch_object($result))
		$punkte_eingetragen_alle = ($row->punkteeingetragen_alle!=''?$row->punkteeingetragen_alle:0);

//Mitarbeitspunkte
$qry = "SELECT sum(mitarbeitspunkte) as mitarbeitspunkte FROM campus.tbl_studentuebung JOIN campus.tbl_uebung USING(uebung_id)
		WHERE lehreinheit_id='$lehreinheit_id' AND student_uid='$user'";
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
}
?>
</td></tr>
</table>
</body>
</html>