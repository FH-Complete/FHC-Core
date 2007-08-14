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
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
$time = microtime_float();
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
	
	//Aus- und Einblenden der Listen
	__js_page_array = new Array();

    function js_toggle_container(conid)
    {
		if (document.getElementById)
		{
        	var block = "table-row";
			if (navigator.appName.indexOf('Microsoft') > -1)
				block = 'block';
            var status = __js_page_array[conid];
            if (status == null)
            	status=document.getElementById(conid).style.display; //status = "none";
            if (status == "none")
            {
            	document.getElementById(conid).style.display = block;
            	__js_page_array[conid] = "visible";
            }
            else
            {
            	document.getElementById(conid).style.display = 'none';
            	__js_page_array[conid] = "none";
            }
            return false;
     	}
     	else
     		return true;
  	}
  //-->
</script>
</head>

<body>
<?php
if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

$user = get_uid();
//$user = "sauermann";
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
$global_msg ='';
$error_thema='';
$error_anzahlderbeispiele='';
$error_punkteprobeispiel='';
$error_freigabebis='';
$error_freigabevon='';
$error_gewicht='';

$thema = (isset($_POST['thema'])?$_POST['thema']:'');
$anzahlderbeispiele = (isset($_POST['anzahlderbeispiele'])?$_POST['anzahlderbeispiele']:'');
$punkteprobeispiel = (isset($_POST['punkteprobeispiel'])?$_POST['punkteprobeispiel']:'');
$punkteprobeispiel = str_replace(',','.',$punkteprobeispiel);
$freigabebis = (isset($_POST['freigabebis'])?$_POST['freigabebis']:'');
$freigabevon = (isset($_POST['freigabevon'])?$_POST['freigabevon']:'');
$gewicht = (isset($_POST['gewicht'])?$_POST['gewicht']:'');
$positiv = (isset($_POST['positiv'])?$_POST['positiv']:'');

$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');
$copy_content = '';

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
	$stsem_content.= "<OPTION value='verwaltung.php?lvid=$lvid&stsem=$studiensemester->studiensemester_kurzbz' $selected>$studiensemester->studiensemester_kurzbz</OPTION>\n";
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
	$result_alle_lehreinheiten = $result;
	if(pg_num_rows($result)>1)
	{
		//Lehreinheiten DropDown
		echo " Lehreinheit: <SELECT name='lehreinheit_id' onChange=\"MM_jumpMenu('self',this,0)\">\n";
		while($row = pg_fetch_object($result))
		{
			if($lehreinheit_id=='')
				$lehreinheit_id=$row->lehreinheit_id;
			$selected = ($row->lehreinheit_id == $lehreinheit_id?'selected':'');
			//Zugeteilte Lektoren
			$qry_lektoren = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter using(mitarbeiter_uid) WHERE lehreinheit_id='$row->lehreinheit_id'";
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


			//Zugeteilte Gruppen
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
			echo "<OPTION value='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->lfbez-$row->lehrform_kurzbz - $gruppen $lektoren</OPTION>\n";
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
	die('Es gibt keine Lehreinheiten in diesem Studiensemester f&uuml;r die Sie eine Berechtigung besitzen');

//Menue
include("menue.inc.php");
/*
echo "\n<!--Menue-->\n";
echo "<br>
<a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Verwaltung</font>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Anwesenheits- und Übersichtstabelle</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Studentenpunkte verwalten</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Statistik</font></a>
<br><br>
<!--Menue Ende-->\n";
*/

//echo "studiensemester: $stsem<br>";
//echo "lehrveranstaltung: $lvid<br>";
//echo "lehreinheit: $lehreinheit_id<br>";

echo "<h3>Übungen anlegen und verwalten</h3>";
echo "</tr></table>";

//Anlegen einer neuen Uebung
if(isset($_POST['uebung_neu']))
{
	if(isset($thema))
	{
		//pruefen ob alle Daten eingegeben wurden
		$error=false;
		$error_msg = '';
		if($thema=='')
		{
			//$error_thema .= "<span class='error'>Thema muss eingegeben werden</span>";
			echo "<span class='error'>Thema muss eingegeben werden</span>";
			$error=true;
		}
		if(!is_numeric($gewicht))
		{
			echo "<span class='error'>Gewicht muss eine Zahl sein</span>";
			$error = true;
		}

		if(!$error)
		{
			//Uebung anlegen
			$datum_obj = new datum();
			$uebung_obj = new uebung($conn);
			$uebung_obj->gewicht=$gewicht;
			$uebung_obj->punkte='';
			$uebung_obj->angabedatei='';
			$uebung_obj->freigabevon = null;
			$uebung_obj->freigabebis = null;
			$uebung_obj->abgabe=false;
			$uebung_obj->beispiele=false;
			$uebung_obj->bezeichnung=$thema;
			$uebung_obj->positiv=isset($_POST['positiv']);
			$uebung_obj->defaultbemerkung='';
			$uebung_obj->lehreinheit_id=$lehreinheit_id;
			$uebung_obj->updateamum = date('Y-m-d H:i:s');
			$uebung_obj->updatevon = $user;
			$uebung_obj->insertamum = date('Y-m-d H:i:s');
			$uebung_obj->insertvon = $user;
			$uebung_obj->statistik = false;
			$uebung_obj->liste_id = null;
			
			if($uebung_obj->save(true))
			{
				if($error_msg!='')
					echo "<span class='error'>$error_msg</span>";
				//else
				//	header("Location: verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$uebung_obj->uebung_id");
			}
			else
				echo "<span class='error'>$uebung_obj->errormsg</span>";
		}

	}
	else
		echo "<span class='error'>Übung konnte nicht angelegt werden!</span><br>";
}


//Loeschen einer Uebung
if(isset($_POST['delete_uebung']))
{
	if(isset($_POST['uebung']))
	{
		$ueb_obj = new uebung($conn);
		$error_msg='';
		//Ausgewaehlte Beispiele holen
		$delete_ids = $_POST['uebung'];
		foreach($delete_ids as $id)
		{
			//Beispiel loeschen
			if(!$ueb_obj->delete($id))
				$error_msg=$ueb_obj->errormsg;
		}
		if($error_msg!='')
			echo "<span class='error'>$error_msg</span>";
	}
}

//Editieren einer Uebung
if(isset($_POST['uebung_edit']))
{
	$error = false;
	if($thema=='')
	{
		echo "<span class='error'>Thema muss eingegeben werden'</span>";
		$error = true;
	}


	if(!$error)
	{
		$uebung_obj = new uebung($conn);
		$uebung_obj->gewicht='';
		$uebung_obj->punkte='';
		$uebung_obj->angabedatei='';
		$uebung_obj->freigabevon = null;
		$uebung_obj->freigabebis = null;
		$uebung_obj->abgabe=false;
		$uebung_obj->beispiele=false;
		$uebung_obj->bezeichnung=$thema;
		$uebung_obj->positiv=true;
		$uebung_obj->defaultbemerkung='';
		$uebung_obj->lehreinheit_id=$lehreinheit_id;
		$uebung_obj->updateamum = date('Y-m-d H:i:s');
		$uebung_obj->updatevon = $user;
		$uebung_obj->uebung_id = $uebung_id;
		$uebung_obj->statistik = false;

		if($uebung_obj->save(false))
			header("Location: verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id");
			//echo "Die &Auml;nderung wurde gespeichert!";
		else
			echo "<span class='error'>$uebung_obj->errormsg</span>";
	}

}


//Eine Uebung in eine andere Lehreinheit kopieren
if(isset($_GET['kopieren']) && $_GET['kopieren']=='true')
{
	//echo "Kopiere Uebung ".$_GET['uebung_copy_id']." to ".$_POST['lehreinheit_copy_id'];
	//Laden der zu kopierenden Uebung
	if(is_numeric($_GET['uebung_copy_id']) && is_numeric($_POST['lehreinheit_copy_id']))
	{
		//Source Uebung Laden
		$qry = "SELECT * FROM campus.tbl_uebung WHERE uebung_id='".$_GET['uebung_copy_id']."'";
		if($result_source = pg_query($conn, $qry))
		{
			if($row_source = pg_fetch_object($result_source))
			{
				//Berechtigung Checken
				$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='".$_POST['lehreinheit_copy_id']."' AND mitarbeiter_uid='$user'";
				if($row_berechtigt = pg_query($conn, $qry))
				{
					if(pg_num_rows($row_berechtigt)>0 ||
					   $rechte->isBerechtigt('admin',0) ||
					   $rechte->isBerechtigt('admin',$lv_obj->studiengang_kz))
					{
						//Schauen ob bereits eine uebung mit diesem Namen vorhanden ist
						$qry = "SELECT * FROM campus.tbl_uebung WHERE lehreinheit_id='".$_POST['lehreinheit_copy_id']."' AND bezeichnung='".addslashes($row_source->bezeichnung)."'";
						$result_bezeichnung_exists = pg_query($conn, $qry);
						if(pg_num_rows($result_bezeichnung_exists)==0)
						{
							//Uebung einfuegen
							$uebung_dest = new uebung($conn);
							$uebung_dest->gewicht = $row_source->punkte;
							$uebung_dest->punkte = $row_source->punkte;
							$uebung_dest->angabedatei = $row_source->angabedatei;
							$uebung_dest->freigabevon = $row_source->freigabevon;
							$uebung_dest->freigabebis = $row_source->freigabebis;
							$uebung_dest->abgabe = ($row_source->abgabe=='t'?true:false);
							$uebung_dest->beispiele = ($row_source->beispiele=='t'?true:false);
							$uebung_dest->bezeichnung = $row_source->bezeichnung;
							$uebung_dest->positiv = ($row_source->positiv=='t'?true:false);
							$uebung_dest->statistik = ($row_source->statistik=='t'?true:false);
							$uebung_dest->defaultbemerkung = $row_source->defaultbemerkung;
							$uebung_dest->lehreinheit_id = $_POST['lehreinheit_copy_id'];
							$ubeung_dest->updateamum = date('Y-m-d H:i:s');
							$uebung_dest->updatevon = $user;
							$uebung_dest->insertamum = date('Y-m-d H:i:s');
							$uebung_dest->insertvon = $user;

							if($uebung_dest->save(true))
							{
								//Beispiel laden
								$qry = "SELECT * FROM campus.tbl_beispiel WHERE uebung_id='".$_GET['uebung_copy_id']."'";
								if($result_bsp_source = pg_query($conn, $qry))
								{
									$error_bsp_save=false;
									while($row_bsp_source = pg_fetch_object($result_bsp_source))
									{
										//Beispiel speichern
										$beispiel_dest = new beispiel($conn);
										$beispiel_dest->uebung_id = $uebung_dest->uebung_id;
										$beispiel_dest->bezeichnung = $row_bsp_source->bezeichnung;
										$beispiel_dest->punkte = $row_bsp_source->punkte;
										$beispiel_dest->updateamum = date('Y-m-d H:i:s');
										$beispiel_dest->updatevon = $user;
										$beispiel_dest->insertamum = date('Y-m-d H:i:s');
										$beispiel_dest->insertvon = $user;

										if(!$beispiel_dest->save(true))
											$error_bsp_save=true;
									}

									if($error_bsp_save)
										echo "<span class='error'>Fehler: Es konnten nicht alle Beispiel kopiert werden</span>";
									else
										echo "Daten wurden erfolgreich kopiert";
								}
							}
							else
							{

								echo "<span class='error'>Fehler beim kopieren der Daten: $uebung_dest->errormsg</span>";
							}
						}
						else
							echo "<span class='error'>Fehler beim Kopieren: In der Ziel-Lehreinheit existiert bereits eine Kreuzerlliste mit diesem Namen!</span>";
					}
					else
						echo "<span class='error'>Sie haben keine Berechtigung f&uuml;r diese Aktion</span>";
				}
			}
			else
				echo "<span class='error'>Uebung ".$_GET['uebung_copy_id']." wurde nicht gefunden</span>";
		}
		else
			echo "<span class='error'>Uebung ".$_GET['uebung_copy_id']." wurde nicht gefunden</span>";
	}
	else
		echo "<span class='error'>Fehler bei der Parameteruebergabe</span>";
}

//Uebersichtstabelle
if(isset($uebung_id) && $uebung_id!='')
{
	echo "<table><tr><td valign='top'>";
	//Bearbeiten der ausgewaehlten Uebung
	echo "<form action='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' method=POST>\n";
	echo "<table><tr><td colspan='2' width='340' class='ContentHeader3'>Ausgew&auml;hlte Übung bearbeiten</td><td>&nbsp;</td></tr>\n";
	echo "<tr><td>&nbsp;</td><td></td></tr>";

	$uebung_obj = new uebung($conn);
	$uebung_obj->load($uebung_id);

	echo "
	<tr><td>Thema</td><td align='right'><input type='text' name='thema'  maxlength='32' value='$uebung_obj->bezeichnung'></td><td>$error_thema</td></tr>
	<!--
	<tr><td>Freigabe</td><td align='right'>von <input type='text' size='16' name='freigabevon' value='".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon))."'></td></tr>
	<tr><td>(Format: 31.12.2007 14:30)</td><td align='right'>bis <input type='text' size='16' name='freigabebis' value='".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis))."'></td></tr>
	<tr><td>Statistik f&uuml;r Studenten anzeigen <input type='checkbox' name='statistik' ".($uebung_obj->statistik?'checked':'')."></td><td></td></tr>
	-->
	<tr><td colspan=2 align='right'><input type='submit' name='uebung_edit' value='Speichern'></td></tr>
	</table>
	</form>";

	$beispiel_obj = new beispiel($conn);
	$beispiel_obj->load_beispiel($uebung_id);
	$anzahl = count($beispiel_obj->beispiele);
	echo "</td><td valign='top'>";

	echo "</td></tr><tr><td valign='top'>";

	echo "</td><td valign='top'>";
}
else
{
	//Gesamtuebersicht ueber alle Uebungen
	
	echo "<table><tr><td valign='top'>";
	echo "<form action='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' method=POST>";
	echo "<table width='440'><tr><td colspan='3' class='ContentHeader3'>Vorhandene Übungen bearbeiten</td></tr>";

	$uebung_obj = new uebung($conn);
	$uebung_obj->load_uebung($lehreinheit_id,$level=1,$uebung_id=null);
	$anzahl = count($uebung_obj->uebungen);
	//$copy_content="<table cellpadding=0><tr><td class='ContentHeader3'>&Uuml;bung in andere LE kopieren</td></tr><tr><td></td><td></td><td>&nbsp;</td></tr><tr><th>&nbsp;</th></tr>";
	$has_copy_content=false;
	if($anzahl>0)
	{
		echo "<tr><td></td><td></td><td>&nbsp;</td></tr><tr><th>Thema</th><th>Freigeschalten</th><th>Auswahl</th><th>&nbsp;</th></tr>";

		//Alle Lehreinheiten holen die zu dieser lehrveranstaltung gehoeren
		//und der angemeldete User berechtigt ist
		$copy_option_content = array();
		for($i=0;$i<pg_num_rows($result_alle_lehreinheiten);$i++)
		{
			$row_alle_lehreinheiten = pg_fetch_object($result_alle_lehreinheiten,$i);
			if($lehreinheit_id!=$row_alle_lehreinheiten->lehreinheit_id)
			{
				//zugeteilte Lektoren holen
				$qry_lektoren = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter using(mitarbeiter_uid) WHERE lehreinheit_id='$row_alle_lehreinheiten->lehreinheit_id'";
				if($result_lektoren = pg_query($conn, $qry_lektoren))
				{
					$lektoren = '( ';
					$j=0;
					while($row_lektoren = pg_fetch_object($result_lektoren))
					{
						$lektoren .= $row_lektoren->kurzbz;
						$j++;
						if($j<pg_num_rows($result_lektoren))
							$lektoren.=', ';
						else
							$lektoren.=' ';
					}
					$lektoren .=')';
				}
				//zugeteilte Gruppen holen
				$qry_gruppen = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$row_alle_lehreinheiten->lehreinheit_id'";
				if($result_gruppen = pg_query($conn, $qry_gruppen))
				{
					$gruppen = '';
					$j=0;
					while($row_gruppen = pg_fetch_object($result_gruppen))
					{
						if($row_gruppen->gruppe_kurzbz=='')
							$gruppen.=$row_gruppen->semester.$row_gruppen->verband.$row_gruppen->gruppe;
						else
							$gruppen.=$row_gruppen->gruppe_kurzbz;
						$j++;
						if($j<pg_num_rows($result_gruppen))
							$gruppen.=', ';
						else
							$gruppen.=' ';
					}
				}
				//$copy_option_content.= "<OPTION value='$row_alle_lehreinheiten->lehreinheit_id'>$row_alle_lehreinheiten->lfbez - $gruppen $lektoren</OPTION>\n";
				$copy_le_content[$row_alle_lehreinheiten->lehreinheit_id] = "$row_alle_lehreinheiten->lfbez-$row_alle_lehreinheiten->lehrform_kurzbz - $gruppen $lektoren";
			}
			
		}

		//Uebungen durchlaufen
		foreach ($uebung_obj->uebungen as $row)
		{
			$has_option_content=false;
			echo "<tr height=23><td align='left'>";
			echo "<a onClick='return(js_toggle_container(\"submenu_$row->uebung_id\"));' class='MenuItem'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'></a>&nbsp;<a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$row->uebung_id' class='Item'><u>".htmlentities($row->bezeichnung)."</u></a>";
			echo "</td><td align='center'>";

			//if((strtotime(strftime($row->freigabevon))<=time()) && (strtotime(strftime($row->freigabebis))>=time()))
			//	echo 'Ja';
			//else
			//	echo 'Nein';
			echo "</td><td align='center'><input type='Checkbox' name='uebung[]' value='$row->uebung_id'></td>";
			//echo "</td><td></td>";
			//Wenn andere Lehreinheiten vorhanden sind dann wird die moeglichkeit zum kopieren von
			//Uebungen in diese Lehreinheiten angeboten.
			if(isset($result_alle_lehreinheiten) && pg_num_rows($result_alle_lehreinheiten)>1)
			{
				$copy_content.= '<tr height=23>';
				$copy_content.= '<td nowrap align="right">';
				$copy_option_content = '';
				//Lehreinheiten fuer Combo durchgehen und schauen ob
				//fuer diese Lehreinheit bereits eine Uebung mit gleichem Namen existiert
				//Falls ja wird diese nicht in der Combo angezeigt
				foreach ($copy_le_content as $id=>$bezeichnung)
				{
					$qry = "SELECT uebung_id FROM campus.tbl_uebung WHERE lehreinheit_id='$id' AND bezeichnung='$row->bezeichnung'";
					//echo $qry;
					if($result_vorhanden = pg_query($conn, $qry))
					{
						if(pg_num_rows($result_vorhanden)==0)
						{
							$copy_option_content.= "<OPTION value='$id'>$bezeichnung</OPTION>\n";
							$has_option_content=true;
							$has_copy_content=true;
						}
					}
				}
				//Wenn eintraege fuer Combo vorhanden sind dann wirds angezeigt
				if($has_option_content)
				{
					$copy_content.= "\n<form  style='margin:1px;' action='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&kopieren=true&uebung_copy_id=$row->uebung_id' method='POST'>";
					$copy_content.= "\n<SELECT name='lehreinheit_copy_id'>\n";
					$copy_content.= $copy_option_content;
					$copy_content.= '</SELECT> ';
					$copy_content.= "&nbsp;&nbsp;&nbsp;<input type='submit' value='COPY'>";
					$copy_content.= "</form>\n";
				}
				else
				{
					$copy_content.="&nbsp;";
				}
				$copy_content.= "</td></tr>";
			}
			$subuebung_obj = new uebung($conn);
			$subuebung_obj->load_uebung($lehreinheit_id,$level=2,$uebung_id=$row->uebung_id);
			$subanzahl = count($subuebung_obj->uebungen);
			echo "<tr><td colspan='3'>";
			echo "<table width='440' id='submenu_".$row->uebung_id."' style='display:none;'>";
			echo "<ul style='margin-top: 0px; margin-bottom: 0px;'>";
			foreach ($subuebung_obj->uebungen as $subrow)
			{
				echo "<tr><td width='120'><li style='margin-left:20px;'><a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$subrow->uebung_id&liste_id=$row->uebung_id'>".$subrow->bezeichnung."</a></li></td><td width='170'>";
				if((strtotime(strftime($subrow->freigabevon))<=time()) && (strtotime(strftime($subrow->freigabebis))>=time()))
					echo 'Ja';
				else
					echo 'Nein';
				echo "</td><td align='center'><input type='Checkbox' name='uebung[]' value='$subrow->uebung_id'></td></tr>";
			}
			echo "</ul>";
			echo "</table>";
			echo "</td></tr>";
		}
		echo "<tr><td></td><td></td><td><input type='Submit' value='Auswahl löschen' name='delete_uebung' onclick='return confirmdelete();'></td></tr>";
	}
	else
		echo "<tr><td colspan='3'>Derzeit sind keine Übungen angelegt</td><td></td></tr>";

	echo "</table>
	</form><br><br>";

	//Kopier-Buttons anzeigen
	//$copy_content.='</table>';
	//echo "</td><td valign='top'>";
	//if($has_copy_content)
	//	echo $copy_content;
	//echo "</td></tr></table>";

	//Uebung neu anlegen
	if(!isset($_POST['uebung_neu']))
	{
		$thema = "Uebung ".($anzahl<9?'0'.($anzahl+1):($anzahl+1));
		$anzahlderbeispiele = 10;
		$punkteprobeispiel = 1;
		$freigabevon = date('d.m.Y H:i');
		$freigabebis = date('d.m.Y H:i');
	}
	
	echo "</td><td valign='top'>";
	echo "
	<form action='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' method=POST>
	<table >
	<tr><td width='440' colspan=2 class='ContentHeader3'>Neue Übung anlegen</td><td></td></tr>
	<tr><td>Thema</td><td align='right'><input type='text' name='thema' maxlength='32' value='$thema'></td><td><span class='error'>$error_thema</td></tr>
	<tr><td>Gewicht</td><td align='right'><input type='text' size='16' name='gewicht' value='1'></td><td>$error_gewicht</td></tr>
		<tr><td>Positiv </td><td><input type='checkbox' name='positiv'></td></tr>
	<!--
	<tr><td>Anzahl der Beispiele</td><td align='right'><input type='text' name='anzahlderbeispiele' maxlength='2' size='2' value='$anzahlderbeispiele'></td><td>$error_anzahlderbeispiele</td></tr>
	<tr><td>Anzahl Punkte pro Beispiel</td><td align='right'><input type='text' name='punkteprobeispiel' value='$punkteprobeispiel'></td><td>$error_punkteprobeispiel</td></tr>
	<tr><td>Freigabe</td><td align='right'>von <input type='text' size='16' name='freigabevon' value='$freigabevon'></td><td>$error_freigabevon</td></tr>
	<tr><td>(Format: 31.12.2007 14:30)</td><td align='right'>bis <input type='text' size='16' name='freigabebis' value='$freigabebis'></td><td>$error_freigabebis</td></tr>
	<tr><td>Statistik f&uuml;r Studenten anzeigen <input type='checkbox' name='statistik'></td><td></td></tr>
	-->
	<tr><td colspan=2 align='right'><input type='submit' name='uebung_neu' value='Anlegen'></td></tr>
	</table>
	</form>
	";
}
?>
</td></tr>
</table>
</body>
</html>