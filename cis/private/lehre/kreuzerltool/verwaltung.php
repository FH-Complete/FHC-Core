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

if(isset($_GET['lvid'])) //Lehrveranstaltung_id
	$lvid = $_GET['lvid'];
else 
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['lehreinheit_id'])) //Lehreinheit_id
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

$thema = (isset($_POST['thema'])?$_POST['thema']:'');
$anzahlderbeispiele = (isset($_POST['anzahlderbeispiele'])?$_POST['anzahlderbeispiele']:'');
$punkteprobeispiel = (isset($_POST['punkteprobeispiel'])?$_POST['punkteprobeispiel']:'');
$punkteprobeispiel = str_replace(',','.',$punkteprobeispiel);
$freigabebis = (isset($_POST['freigabebis'])?$_POST['freigabebis']:'');
$freigabevon = (isset($_POST['freigabevon'])?$_POST['freigabevon']:'');

$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');

//Kopfzeile
echo '<table border="0" cellspacing="0" cellpadding="0" height="100%" width="100%">';
echo ' <tr>';
echo '<td width="10">&nbsp;</td>';
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
	$qry = "SELECT distinct tbl_lehrfach.kurzbz as lfbez, tbl_lehreinheit.lehreinheit_id FROM lehre.tbl_lehreinheit, lehre.tbl_lehrfach, lehre.tbl_lehreinheitmitarbeiter
			WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND
			tbl_lehreinheit.lehrfach_id = tbl_lehrfach.lehrfach_id AND
			tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_lehreinheit.studiensemester_kurzbz = '$stsem'";
}
else 
{
	$qry = "SELECT distinct tbl_lehrfach.kurzbz as lfbez, tbl_lehreinheit.lehreinheit_id FROM lehre.tbl_lehreinheit, lehre.tbl_lehrfach, lehre.tbl_lehreinheitmitarbeiter
			WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND
			tbl_lehreinheit.lehrfach_id = tbl_lehrfach.lehrfach_id AND
			tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_lehreinheitmitarbeiter.mitarbeiter_uid = '$user' AND
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
			echo "<OPTION value='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->lfbez - $gruppen $lektoren</OPTION>\n";
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
echo '<td width="10">&nbsp;</td>';
echo "<td>\n";
echo "<b>$lv_obj->bezeichnung</b><br>";

if($lehreinheit_id=='')
	die('Es gibt keine Lehreinheiten in diesem Studiensemester f&uuml;r die Sie eine Berechtigung besitzen');

//Menue
echo "\n<!--Menue-->\n";
echo "<br>
<a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Verwaltung</font>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Anwesenheits- und Übersichtstabelle</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Studentenpunkte verwalten</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Statistik</font></a>
<br><br>
<!--Menue Ende-->\n";


//echo "studiensemester: $stsem<br>";
//echo "lehrveranstaltung: $lvid<br>";
//echo "lehreinheit: $lehreinheit_id<br>";

echo "<h3>Kreuzerllisten anlegen und verwalten</h3>";

//Anlegen einer neuen Uebung
if(isset($_POST['uebung_neu']))
{
	if(isset($thema) && isset($anzahlderbeispiele) && isset($punkteprobeispiel))
	{
		//pruefen ob alle Daten eingegeben wurden
		$error=false;
		if($thema=='')
		{
			$error_thema.= "<span class='error'>Thema muss eingegeben werden</span>";
			$error=true;
		}		
		if(!is_numeric($punkteprobeispiel))
		{
			$error_punkteprobeispiel= "<span class='error'>Punkte pro Beispiel muss eine g&uuml;ltige Zahl sein</span>";
			$error=true;
		}
		elseif($punkteprobeispiel<0)
		{
			$error_punkteprobeispiel = "<span class='error'>Punkte pro Beispiel darf nicht negativ sein</span>";
			$error=true;
		}
		if(!is_numeric($anzahlderbeispiele))
		{
			$error_anzahlderbeispiele = "<span class='error'>Anzahl der Beispiele muss eine g&uuml;ltige Zahl sein</span>";
			$error=true;
		}
		elseif($anzahlderbeispiele<0)
		{
			$error_anzahlderbeispiele = "<span class='error'>Anzahl der Beispiele darf nicht negativ sein</span>";
			$error=true;
		}
		
		$freigabevon_sav = $datum_obj->mktime_datumundzeit($freigabevon);
		$freigabebis_sav = $datum_obj->mktime_datumundzeit($freigabebis);
		
		if(!$freigabebis_sav)
		{				
			$error_freigabebis = "<span class='error'>Bis-Datum hat ein ung&uuml;ltiges Format</span>";
			$error=true;
		}
		
		if(!$freigabevon_sav)
		{
			$error_freigabevon = "<span class='error'>Von-Datum hat ein ung&uuml;ltiges Format</span>";
			$error=true;
		}
		
		if($freigabevon_sav && $freigabebis_sav && $freigabevon_sav>$freigabebis_sav)
		{
					$error_freigabevon = "<span class='error'>Von Datum darf nicht gr&ouml;sser als Bis Datum sein</span>";
					$error=true;
		}
		
		if(!$error)
		{
			//Uebung anlegen
			$datum_obj = new datum();
			$uebung_obj = new uebung($conn);
			$uebung_obj->gewicht='';
			$uebung_obj->punkte='';
			$uebung_obj->angabedatei='';
			$uebung_obj->freigabevon = date('Y-m-d H:i',$freigabevon_sav);
			$uebung_obj->freigabebis = date('Y-m-d H:i',$freigabebis_sav);
			$uebung_obj->abgabe=false;
			$uebung_obj->beispiele=true;
			$uebung_obj->bezeichnung=$thema;
			$uebung_obj->positiv=true;
			$uebung_obj->defaultbemerkung='';
			$uebung_obj->lehreinheit_id=$lehreinheit_id;
			$uebung_obj->updateamum = date('Y-m-d H:i:s');
			$uebung_obj->updatevon = $user;
			$uebung_obj->insertamum = date('Y-m-d H:i:s');
			$uebung_obj->insertvon = $user;
			$uebung_obj->statistik = isset($_POST['statistik']);
						
			if($uebung_obj->save(true))
			{
				//Beispiele anlegen
				$uebung_id = $uebung_obj->uebung_id;
				$error_msg='';
				for($i=0;$i<$anzahlderbeispiele;$i++)
				{
					$beispiel_obj = new beispiel($conn);
					$beispiel_obj->uebung_id = $uebung_id;
					$beispiel_obj->bezeichnung = "Beispiel ".($i<9?'0'.($i+1):($i+1));
					$beispiel_obj->punkte = $punkteprobeispiel;
					$beispiel_obj->updateamum = date('Y-m-d H:i:s');
					$beispiel_obj->updatevon = $user;
					$beispiel_obj->insertamum = date('Y-m-d H:i:s');
					$beispiel_obj->insertvon = $user;
					
					if(!$beispiel_obj->save(true))
						$error_msg = $beispiel_obj->errormsg;
				}
				if($error_msg!='')
					echo "<span class='error'>$error_msg</span>";
			}
			else 
				echo "<span class='error'>$uebung_obj->errormsg</span>";
		}

	}
	else 
		echo "<span class='error'>Kreuzerlliste konnte nicht angelegt werden!</span><br>";
}

//Loeschen eines Beispiels
if(isset($_POST['beispiel_delete']))
{
	if(isset($_POST['beispiel']))
	{
		$beispiel_obj = new beispiel($conn);
		$error_msg='';
		//Ausgewaehlte Beispiele holen
		$delete_ids = $_POST['beispiel'];
		foreach($delete_ids as $id)
		{
			//Beispiel loeschen
			if(!$beispiel_obj->delete($id))
				$error_msg=$beispiel_obj->errormsg;
		}
		if($error_msg!='')
			echo "<span class='error'>$error_msg</span>";
	}
}

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
	
	$freigabevon_sav = $datum_obj->mktime_datumundzeit($freigabevon);
	$freigabebis_sav = $datum_obj->mktime_datumundzeit($freigabebis);
	
	if($freigabevon_sav>$freigabebis_sav)
	{
		echo "<span class='error'>Von Datum darf nicht gr&ouml;sser als Bis Datum sein</span>";
		$error=true;
	}
	if(!$freigabebis_sav)
	{				
		echo "<span class='error'>Bis-Datum hat ein ung&uuml;ltiges Format</span>";
		$error=true;
	}
	
	if(!$freigabevon_sav)
	{
		echo "<span class='error'>Von-Datum hat ein ung&uuml;ltiges Format</span>";
		$error=true;
	}
	
	if(!$error)
	{
		$uebung_obj = new uebung($conn);
		$uebung_obj->gewicht='';
		$uebung_obj->punkte='';
		$uebung_obj->angabedatei='';
		$uebung_obj->freigabevon = date('Y-m-d H:i',$freigabevon_sav);
		$uebung_obj->freigabebis = date('Y-m-d H:i',$freigabebis_sav);
		$uebung_obj->abgabe=false;
		$uebung_obj->beispiele=true;
		$uebung_obj->bezeichnung=$thema;
		$uebung_obj->positiv=true;
		$uebung_obj->defaultbemerkung='';
		$uebung_obj->lehreinheit_id=$lehreinheit_id;
		$uebung_obj->updateamum = date('Y-m-d H:i:s');
		$uebung_obj->updatevon = $user;
		$uebung_obj->uebung_id = $uebung_id;
		$uebung_obj->statistik = (isset($_POST['statistik'])?true:false);
		
		if($uebung_obj->save(false))
			echo "Die &Auml;nderung wurde gespeichert!";
		else
			echo "<span class='error'>$uebung_obj->errormsg</span>";
	}
	
}

//Neues Beispiel anlegen
if(isset($_POST['beispiel_neu']) || isset($_POST['beispiel_edit']))
{
	if(isset($_POST['beispiel_edit']) && (!isset($beispiel_id) || !is_numeric($beispiel_id)))
	{
		echo "<span class='error'>Beispiel_id ist ungueltig</span>";
	}
	else 
	{
		if(isset($uebung_id) && $uebung_id!='' && is_numeric($uebung_id))
		{			
			$punkte = (isset($_POST['punkte'])?$_POST['punkte']:'');
			$punkte = str_replace(',','.',$punkte);
			if(is_numeric($punkte) && $punkte!='')
			{
				if($bezeichnung!='')
				{
					$beispiel_obj = new beispiel($conn);
					if(isset($_POST['beispiel_edit']))
					{
						$beispiel_obj->beispiel_id= $beispiel_id;
						$beispiel_obj->new=false;
					}
					else 
					{
						$beispiel_obj->new=true;
						$beispiel_obj->insertamum = date('Y-m-d H:i:s');
						$beispiel_obj->insertvon = $user;				
					}
					
					$beispiel_obj->uebung_id = $uebung_id;
					$beispiel_obj->bezeichnung = $bezeichnung;
					$beispiel_obj->punkte = $punkte;
					$beispiel_obj->updateamum = date('Y-m-d H:i:s');
					$beispiel_obj->updatevon = $user;
					if($beispiel_obj->save())
					{
						$beispiel_id='';
					}
					else 
						echo "<span class='error'>$beispiel_obj->errormsg</span>";
				}
				else 
					echo "<span class='error'>Bezeichnung muss eingegeben werden</span>";
			}
			else 
				echo "<span class='error'>Punkte muss eine g&uuml;ltige Zahl sein</span>";
		}
		else 
			echo "<span class='error'>Zugehoerige Uebung ist fehlerhaft</span>";
	}
}

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
	echo "<table><tr><td colspan='2' width='340' class='ContentHeader3'>Ausgew&auml;hlte Kreuzerlliste bearbeiten</td><td>&nbsp;</td></tr>\n";	
	echo "<tr><td>&nbsp;</td><td></td></tr>";
		
	$uebung_obj = new uebung($conn);
	$uebung_obj->load($uebung_id);
	
	echo "
	<tr><td>Thema</td><td align='right'><input type='text' name='thema' value='$uebung_obj->bezeichnung'></td><td>$error_thema</td></tr>
	<tr><td>Freigabe</td><td align='right'>von <input type='text' size='16' name='freigabevon' value='".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon))."'></td></tr>
	<tr><td>(Format: 31.12.2007 14:30)</td><td align='right'>bis <input type='text' size='16' name='freigabebis' value='".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis))."'></td></tr>
	<tr><td>Statistik f&uuml;r Studenten anzeigen <input type='checkbox' name='statistik' ".($uebung_obj->statistik?'checked':'')."></td><td></td></tr>
	<tr><td colspan=2 align='right'><input type='submit' name='uebung_edit' value='Speichern'></td></tr>
	</table>
	</form>";
	
	$beispiel_obj = new beispiel($conn);
	$beispiel_obj->load_beispiel($uebung_id);
	$anzahl = count($beispiel_obj->beispiele);
	
	echo "</td><td valign='top'>";
	
	//Beispiel neu Anlegen
	echo "<form action='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' method=POST>\n";
	echo "<table width='340'><tr><td colspan='3' class='ContentHeader3'>Neues Beispiel anlegen</td></tr>\n";	
	echo "<tr><td>&nbsp;</td><td></td></tr>\n\n";	
	
	echo "<tr><td>Bezeichnung <input type='text' name='bezeichnung' value='Beispiel ".($anzahl<9?'0'.($anzahl+1):($anzahl+1))."'>";
	echo "&nbsp;Punkte <input type='text' size='2' name='punkte' value='1'></td></tr>";
	echo "<tr><td align='right'><input type='submit' name='beispiel_neu' value='Anlegen'></td></tr>";
		
	echo "</table>
	</form>";
	
	echo "</td></tr><tr><td valign='top'>";
	
	//Uebersicht der Beispiele
	echo "<form action='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' method=POST>\n";
	echo "<table width='340'><tr><td colspan='3' class='ContentHeader3'>Vorhandene Beispiele</td></tr>\n";	
	echo "<tr><td>&nbsp;</td><td></td><td></td></tr>\n\n";	
		
	if($anzahl>0)
	{
		echo "<tr><th>Beispiel</th><th>Punkte</th><th>Auswahl</th></tr>\n";
		foreach ($beispiel_obj->beispiele as $row) 
		{
			echo "<tr><td><a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&beispiel_id=$row->beispiel_id' class='Item'><u>".htmlentities($row->bezeichnung)."</u></a></td>
			<td align='center'>$row->punkte</td>
			<td align='center'><input type='Checkbox' name='beispiel[]' value='$row->beispiel_id'></td>";
		}
		echo "<tr><td></td><td></td><td align='right'><input type='Submit' value='Auswahl löschen' onclick='return confirmdelete()' name='beispiel_delete'></td></tr>";
	}
	else 
		echo "<tr><td colspan='3'>Derzeit sind keine Beispiele angelegt</td><td></td></tr>";
	
	echo "</table>
	</form>";
	
	echo "</td><td valign='top'>";
			
	//Beispiel Aendern
	$error_msg = '';
	if(isset($beispiel_id) && $beispiel_id!='')
	{
		//Bearbeiten eines Beispiels
		if($beispiel_obj->load($beispiel_id))
		{
			echo "<form action='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&beispiel_id=$beispiel_id' method=POST>\n";
			echo "<table width='340'><tr><td colspan='3' class='ContentHeader3'>Beispiel bearbeiten</td></tr>\n";	
			echo "<tr><td>&nbsp;</td><td></td></tr>\n\n";	
	
			echo "<tr><td>Bezeichnung <input type='text' name='bezeichnung' value='$beispiel_obj->bezeichnung'>";
			echo "&nbsp;Punkte <input type='text' size='2' name='punkte' value='$beispiel_obj->punkte'></td></tr>";
			echo "<tr><td align='right'><input type='submit' name='beispiel_edit' value='Ändern'></td></tr>";
		
			echo "</table>
					</form><br><br>";
		}
		else 
			$error_msg = $beispiel_obj->errormsg;			
	}
	echo "</td></tr></table>";
}
else 
{
	//Gesamtuebersicht ueber alle Uebungen
	echo "<table><tr><td valign='top'>";
	echo "<form action='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' method=POST>";	
	echo "<table width='440'><tr><td colspan='3' class='ContentHeader3'>Vorhandene Kreuzerllisten bearbeiten</td></tr>";	
	
	$uebung_obj = new uebung($conn);
	$uebung_obj->load_uebung($lehreinheit_id);
	$anzahl = count($uebung_obj->uebungen);
	$copy_content="<table cellpadding=0><tr><td class='ContentHeader3'>&Uuml;bung in andere LE kopieren</td></tr><tr><td></td><td></td><td>&nbsp;</td></tr><tr><th>&nbsp;</th></tr>";
	if($anzahl>0)
	{
		echo "<tr><td></td><td></td><td>&nbsp;</td></tr><tr><th>Thema</th><th>Freigeschalten</th><th>Auswahl</th><th>&nbsp;</th></tr>";
		foreach ($uebung_obj->uebungen as $row) 
		{
			echo "<tr height=23><td align='left'><a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id' class='Item'><u>".htmlentities($row->bezeichnung)."</u></a></td><td align='center'>";
			
			if((strtotime(strftime($row->freigabevon))<=time()) && (strtotime(strftime($row->freigabebis))>=time()))
				echo 'Ja';
			else 
				echo 'Nein';
			echo "</td><td align='center'><input type='Checkbox' name='uebung[]' value='$row->uebung_id'></td>";
			if(isset($result_alle_lehreinheiten) && pg_num_rows($result_alle_lehreinheiten)>1)
			{
				$copy_content.= '<tr>';
				$copy_content.= '<td>';
				$copy_content.= "<form  style='margin:1px;' action='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&kopieren=true&uebung_copy_id=$row->uebung_id' method='POST'>";
				$copy_content.= "\n<SELECT name='lehreinheit_copy_id'>\n";
				
				for($i=0;$i<pg_num_rows($result_alle_lehreinheiten);$i++)
				{
					$row_alle_lehreinheiten = pg_fetch_object($result_alle_lehreinheiten,$i);
					if($lehreinheit_id!=$row_alle_lehreinheiten->lehreinheit_id)
					{
						$qry_lektoren = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN campus.vw_mitarbeiter ON(mitarbeiter_uid=uid) WHERE lehreinheit_id='$row_alle_lehreinheiten->lehreinheit_id'";
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
						$copy_content.= "<OPTION value='$row_alle_lehreinheiten->lehreinheit_id'>$row_alle_lehreinheiten->lfbez - $gruppen $lektoren</OPTION>\n";
					}
				}
				$copy_content.= '</SELECT> ';
				
				$copy_content.= "&nbsp;&nbsp;&nbsp;<input type='submit' value='COPY'>";
				$copy_content.= "</form>";
				$copy_content.= "</td></tr>";
				
			}
		}
		echo "<tr><td></td><td></td><td><input type='Submit' value='Auswahl löschen' name='delete_uebung' onclick='return confirmdelete();'></td></tr>";
	}
	else 
		echo "<tr><td colspan='3'>Derzeit sind keine Kreuzerllisten angelegt</td><td></td></tr>";
	
	echo "</table>
	</form><br><br>";
	
	//Kopier-Button anzeigen
	$copy_content.='</table>';
	echo "</td><td valign='top'>";
	if(isset($result_alle_lehreinheiten) && pg_num_rows($result_alle_lehreinheiten)>1 && $anzahl>0)
		echo $copy_content;
	echo "</td></tr></table>";
	
	if(!isset($_POST['uebung_neu']))
	{
		$thema = "Uebung ".($anzahl<9?'0'.($anzahl+1):($anzahl+1));
		$anzahlderbeispiele = 10;
		$punkteprobeispiel = 1;
		$freigabevon = date('d.m.Y H:i');
		$freigabebis = date('d.m.Y H:i');
	}
	echo "
	<form action='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' method=POST>
	<table >
	<tr><td width='440' colspan=2 class='ContentHeader3'>Neue Kreuzerlliste anlegen</td><td></td></tr>
	<tr><td>Thema</td><td align='right'><input type='text' name='thema' value='$thema'></td><td><span class='error'>$error_thema</td></tr>
	<tr><td>Anzahl der Beispiele</td><td align='right'><input type='text' name='anzahlderbeispiele' value='$anzahlderbeispiele'></td><td>$error_anzahlderbeispiele</td></tr>
	<tr><td>Anzahl Punkte pro Beispiel</td><td align='right'><input type='text' name='punkteprobeispiel' value='$punkteprobeispiel'></td><td>$error_punkteprobeispiel</td></tr>
	<tr><td>Freigabe</td><td align='right'>von <input type='text' size='16' name='freigabevon' value='$freigabevon'></td><td>$error_freigabevon</td></tr>
	<tr><td>(Format: 31.12.2007 14:30)</td><td align='right'>bis <input type='text' size='16' name='freigabebis' value='$freigabebis'></td><td>$error_freigabebis</td></tr>
	<tr><td>Statistik f&uuml;r Studenten anzeigen <input type='checkbox' name='statistik'></td><td></td></tr>
	<tr><td colspan=2 align='right'><input type='submit' name='uebung_neu' value='Anlegen'></td></tr>
	</table>	
	</form>
	";
	
	echo "</form>";
}
?>
</td></tr>
</table>
</body>
</html>