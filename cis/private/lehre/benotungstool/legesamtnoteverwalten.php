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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../../../config/cis.config.inc.php');
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

if($stsem!='' && !check_stsem($stsem))
	die($p->t('global/studiensemesterKonnteNichtGefundenWerden'));

//Vars
$datum_obj = new datum();

$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');
$uid = (isset($_GET['uid'])?$_GET['uid']:'');
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
	function confirmdelete()
	{
		return confirm('<?php echo $p->t('gesamtnote/wollenSieWirklichLoeschen');?>');
	}
  
  
    var anfrage = null;

    function erzeugeAnfrage()
    {
        try 
        {
        	anfrage = new XMLHttpRequest();
        } 
        catch (versuchmicrosoft) 
        {
            try 
            {
                anfrage = new ActiveXObject("Msxml12.XMLHTTP");
            } 
            catch (anderesmicrosoft)
            {
                try 
                {
                    anfrage = new ActiveXObject("Microsoft.XMLHTTP");
                }
                catch (fehlschlag)
                {
                    anfrage = null;
                }

            }
        }
        if (anfrage == null) 
        	alert("Fehler beim Erstellen des Anfrageobjekts!");
    }
   
   function saveLENote(uid)
   {
		note = document.getElementById(uid).note.value;	
		if ((note < 0) || (note > 5 && note != 8 && note != 7 && note!=16))
		{
			alert("<?php echo $p->t('benotungstool/noteEingeben');?>");
			document.getElementById(uid).note.value="";
		}
		else
		{	
			erzeugeAnfrage(); 
		    //note = document.getElementById(uid).note.value;
		    stud_uid = uid;
		    var jetzt = new Date();
			var ts = jetzt.getTime();
		    var url= '<?php echo "legesamtnoteeintragen.php?lvid=".addslashes($lvid)."&lehreinheit_id=".addslashes($lehreinheit_id)."&stsem=".addslashes($stsem); ?>';
		    url += '&submit=1&student_uid='+uid+"&note="+note+"&"+ts;
		    anfrage.open("GET", url, true);
		    anfrage.onreadystatechange = updateSeite;
		    anfrage.send(null);
		}
   }
   
   function updateSeite()
   {
	    if (anfrage.readyState == 4)
	    {
	        if (anfrage.status == 200) 
	        {
	        	uid = stud_uid;
				var note = document.getElementById(uid).note.value;
	            var resp = anfrage.responseText;
	            if (resp == "neu" || resp == "update")
	            {
					            	
	            	notentd = document.getElementById("note_"+uid);
	            	while (notentd.childNodes.length>0)
	            	{
						notentd.removeChild(notentd.lastChild);
	            	}
	            	notenode = document.createTextNode(note);
                    notentd.appendChild(notenode);
                 }
                 else
                 	{
	                 	alert(resp);
	                 	document.getElementById(uid).note.value="";
                 	}
	        } 
	        else 
	        	alert("Request status:" + anfrage.status);
	    }
	}  
//-->
</script>
</head>

<body>
<?php
//Kopfzeile
echo '<table width="100%">';
echo ' <tr>';
echo '<td><h1>'.$p->t('benotungstool/benotungstool');
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
	$stsem_content.= "<OPTION value='legesamtnoteeintragen.php?lvid=$lvid&stsem=$studiensemester->studiensemester_kurzbz' $selected>$studiensemester->studiensemester_kurzbz</OPTION>\n";
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
		echo $p->t('global/lehreinheit')." : <SELECT name='lehreinheit_id' onChange=\"MM_jumpMenu('self',this,0)\">\n";
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
			echo "<OPTION value='legesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->lfbez-$row->lehrform_kurzbz - $gruppen $lektoren</OPTION>\n";
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

echo '<table  class="tabcontent"><tr>';
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

// legesamtnote für studenten speichern
if (isset($_REQUEST["submit"]) && ($_POST["student_uid"] != '')){
	
	$jetzt = date("Y-m-d H:i:s");	
	$student_uid = $_POST["student_uid"];	
	$legesamtnote = new legesamtnote($lehreinheit_id);

	if(!$student = new student($student_uid))
		die($p->t('benotungstool/studentWurdeNichtGefunden'));

	if (!$legesamtnote->load($student->prestudent_id,$lehreinheit_id))
	{
		$legesamtnote->prestudent_id = $prestudent_id;
		$legesamtnote->lehreinheit_id = $lehreinheit_id;
		$legesamtnote->note = $_POST["note"];
		$legesamtnote->benotungsdatum = $jetzt;
		$legesamtnote->updateamum = null;
		$legesamtnote->updatevon = null;
		$legesamtnote->insertamum = $jetzt;
		$legesamtnote->insertvon = $user;
		$legesamtnote->new = true;
	}
	else
	{
		$legesamtnote->note = $_POST["note"];
		$legesamtnote->benotungsdatum = $jetzt;
		$legesamtnote->updateamum = $jetzt;
		$legesamtnote->updatevon = $user;
	}
	if (!$legesamtnote->save())
		echo "<span class='error'>".$legesamtnote->errormsg."</span>";
}

echo "<h3>".$p->t('benotungstool/leGesamtnotenVerwalten')."</h3>";
echo $p->t('benotungstool/noten');


//Studentenliste
echo "
<table>
";

		echo "<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class='ContentHeader2'>".$p->t('global/uid')."</td>
				<td class='ContentHeader2'>".$p->t('global/nachname')."</td>
				<td class='ContentHeader2'>".$p->t('global/vorname')."</td>
				<td class='ContentHeader2'>".$p->t('benotungstool/gesamtnote')."</td>
				<td class='ContentHeader2'>&nbsp;</td>
				<td class='ContentHeader2'></td>
				<td class='ContentHeader2'></td>
				<td class='ContentHeader2'>".$p->t('benotungstool/leGesamtnote')."</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
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
					<td colspan='8' align='center'><b>$row_grp->gruppe_kurzbz</b></td>
				</tr>";
				$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student_lehrveranstaltung JOIN public.tbl_benutzergruppe USING(uid) WHERE gruppe_kurzbz='".addslashes($row_grp->gruppe_kurzbz)."' AND studiensemester_kurzbz = '".$stsem."' ORDER BY nachname, vorname";
		}
		else
		{
			echo "
				<tr>
					<td colspan='8' align='center'><b>Verband $row_grp->verband ".($row_grp->gruppe!=''?"Gruppe $row_grp->gruppe":'')."</b></td>
				</tr>";
				$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student_lehrveranstaltung
				             WHERE studiengang_kz='$row_grp->studiengang_kz' AND
				             semester='$row_grp->semester' ".
							 ($row_grp->verband!=''?" AND trim(verband)=trim('$row_grp->verband')":'').
							 ($row_grp->gruppe!=''?" AND trim(gruppe)=trim('$row_grp->gruppe')":'').
				            " ORDER BY nachname, vorname";
		}
*/

// studentenquery		
$qry_stud = "SELECT uid, prestudent_id, vorname, nachname, matrikelnr FROM campus.vw_student_lehrveranstaltung JOIN campus.vw_student using(uid) WHERE  studiensemester_kurzbz = ".$db->db_add_param($stsem)." and lehreinheit_id = ".$db->db_add_param($lehreinheit_id, FHC_INTEGER)." ORDER BY nachname, vorname";

if($result_stud = $db->db_query($qry_stud))
{
	$i=1;
	while($row_stud = $db->db_fetch_object($result_stud))
	{
		$studentnote = new studentnote();
		$studentnote->calc_gesamtnote($lehreinheit_id,$stsem,$row_stud->prestudent_id);
		//echo $studentnote->debug;
		$legesamtnote = new legesamtnote($lehreinheit_id);
		if (!$legesamtnote->load($row_stud->prestudent_id,$lehreinheit_id))
		{
			$note = null;
		}
		else
		{
			$note = $legesamtnote->note;
		} 
		
		if ($studentnote->studentgesamtnote!=0)
			$note_calc = round($studentnote->studentgesamtnote,2);
		else
			$note_calc = null;
		echo "
		<tr class='liste".($i%2)."'>
			<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->uid</a></td>
			<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->nachname</a></td>
			<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->vorname</a></td>";					
		echo "<td>$note_calc</td>";
		echo "<td align='center'>";
		if ($studentnote->negativ)
			echo "<span class='negativ'>neg</span>";
		echo "</td>";
		echo "<td align='center'>";
		if ($studentnote->fehlt)
			echo "<span class='negativ'>X</span>";
		else
			echo "ok";
		echo "</td>";
		if ($note)
			$note_final = $note;
		else
		{
			if ($studentnote->negativ)
				$note_final = 5;
			else
			{		
				$note_final = round($studentnote->studentgesamtnote);
				if ($note_final == 0)
					$note_final = null;
			}
		} 
		echo "<form  accept-charset='UTF-8' name='$row_stud->uid' id='$row_stud->uid' method='POST' action='legesamtnoteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&stsem=$stsem'><td><input type='hidden' name='student_uid' value='$row_stud->uid'><input type='text' size='1' value='$note_final' name='note'><input type='button' value='->' onclick='saveLENote(\"$row_stud->uid\")'></td></form>";
		if ($note == 5)
			$negmarkier = " style='color:red; font-weight:bold;'";
		else
			$negmarkier = "";
		echo "<td align='center' id='note_$row_stud->uid'><span".$negmarkier.">$note</span></td>";
		echo "</tr>";
		$i++;
	}
}
echo "</table>";

?>
</td></tr>
</table>
</body>
</html>
