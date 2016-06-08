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
require_once('../../../../include/basis_db.class.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/uebung.class.php');
require_once('../../../../include/beispiel.class.php');
require_once('../../../../include/datum.class.php');
require_once('functions.inc.php');
require_once('../../../../include/phrasen.class.php');

$sprache = getSprache();
$p = new phrasen($sprache); 

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
		
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
$time = microtime_float();
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
	
//Angabedatei ausliefern
if (isset($_GET["download"])){
	$file=$_GET["download"];
	$uebung_id = $_GET["uebung_id"];
	$ueb = new uebung();
	$ueb->load($uebung_id);
	$filename = BENOTUNGSTOOL_PATH."angabe/".$ueb->angabedatei;
	header('Content-Type: application/octet-stream');
	header('Content-disposition: attachment; filename="'.$file.'"');
	readfile($filename);
	exit;
}

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
	
	function set_notenschluessel_prozent()
	{
		document.ns.schluessel_punkte_1.value=89;
		document.ns.schluessel_punkte_2.value=76;
		document.ns.schluessel_punkte_3.value=63;
		document.ns.schluessel_punkte_4.value=50;
		document.ns.schluessel_punkte_5.value=0;
	}
  //-->
</script>
</head>

<body>
<?php


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

//Vars
$datum_obj = new datum();
$global_msg ='';
$error_thema='';
$error_anzahlderbeispiele='';
$error_punkteprobeispiel='';
$error_freigabebis='';
$error_freigabevon='';
$error_maxstd = '';
$error_maxbsp = '';
$error_gewicht = '';

$thema = (isset($_POST['thema'])?$_POST['thema']:'');
$liste_id = (isset($_REQUEST['liste_id'])?$_REQUEST['liste_id']:'');
$anzahlderbeispiele = (isset($_POST['anzahlderbeispiele'])?$_POST['anzahlderbeispiele']:'');
$punkteprobeispiel = (isset($_POST['punkteprobeispiel'])?$_POST['punkteprobeispiel']:'');
$punkteprobeispiel = mb_ereg_replace(',','.',$punkteprobeispiel);
$freigabebis = (isset($_POST['freigabebis'])?$_POST['freigabebis']:'');
$freigabevon = (isset($_POST['freigabevon'])?$_POST['freigabevon']:'');
$maxstd = (isset($_POST['maxstd'])?$_POST['maxstd']:'');
$maxbsp = (isset($_POST['maxbsp'])?$_POST['maxbsp']:'');
$gewicht = (isset($_POST['gewicht'])?$_POST['gewicht']:'');
if (isset($_FILES["angabedatei"]))
	$angabedatei_up = $_FILES["angabedatei"]["tmp_name"];
else
	$angabedatei_up = null;
	
$beispiel_id = (isset($_GET['beispiel_id'])?$_GET['beispiel_id']:'');
$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');

//Angabedatei löschen
if (isset($_GET["deletefile"])){
	$file=$_GET["deletefile"];
	$ueb = new uebung();
	$ueb->load($uebung_id);
	$filename = BENOTUNGSTOOL_PATH."angabe/".$ueb->angabedatei;
	$ueb->angabedatei = '';
	$ueb->save(false);
	unlink($filename);
}

//notenschlüssel anlegen
if (isset($_POST["schluessel"]) && $_POST["schluessel"]=='Speichern')
{
	$punkte_arr = array();
	$punkte_arr[1] = $_POST["schluessel_punkte_1"];
	$punkte_arr[2] = $_POST["schluessel_punkte_2"];
	$punkte_arr[3] = $_POST["schluessel_punkte_3"];
	$punkte_arr[4] = $_POST["schluessel_punkte_4"];
	$punkte_arr[5] = $_POST["schluessel_punkte_5"];
	for ($i=1;$i<=5;$i++)
	{
		if (is_numeric($punkte_arr[$i]))
		{
			$qry = "select * from campus.tbl_notenschluesseluebung where uebung_id = ".$db->db_add_param($liste_id, FHC_INTEGER)." and note = ".$db->db_add_param($i);
			$result = $db->db_query($qry);
			if($db->db_num_rows($result)>0)
				$str = "update campus.tbl_notenschluesseluebung set punkte = ".$db->db_add_param($punkte_arr[$i])." where uebung_id = ".$db->db_add_param($liste_id, FHC_INTEGER)." and note = ".$db->db_add_param($i);
			else
				$str = "insert into campus.tbl_notenschluesseluebung (uebung_id, note, punkte) values (".$db->db_add_param($liste_id).",".$db->db_add_param($i).",".$db->db_add_param($punkte_arr[$i]).")";
			if (!$db->db_query($str))
				echo "<span class='error'>Daten konnten nicht gespeichert werden</span>";
		}
	}
}
//Kopfzeile
echo '<table class="tabcontent">';
echo ' <tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
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
	$stsem_content.= "<OPTION value='verwaltung.php?lvid=$lvid&stsem=$studiensemester->studiensemester_kurzbz' $selected>$studiensemester->studiensemester_kurzbz</OPTION>\n";
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
	$result_alle_lehreinheiten = $result;
	if($db->db_num_rows($result)>1)
	{
		//Lehreinheiten DropDown
		echo " ".$p->t('global/lehreinheit').": <SELECT name='lehreinheit_id' onChange=\"MM_jumpMenu('self',this,0)\">\n";
		while($row = $db->db_fetch_object($result))
		{
			if($lehreinheit_id=='')
				$lehreinheit_id=$row->lehreinheit_id;
			$selected = ($row->lehreinheit_id == $lehreinheit_id?'selected':'');
			//Zugeteilte Lektoren
			$qry_lektoren = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter using(mitarbeiter_uid) WHERE lehreinheit_id=".$db->db_add_param($row->lehreinheit_id, FHC_INTEGER);
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


			//Zugeteilte Gruppen
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
			echo "<OPTION value='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->lfbez-$row->lehrform_kurzbz - $gruppen $lektoren</OPTION>\n";
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
echo '<table width="100%"><tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
echo "<td>\n";
echo "<h2>".$lv_obj->bezeichnung_arr[$sprache]."</h2>";

if($lehreinheit_id=='')
	die($p->t('benotungstool/esGibtKeineLehreinheiten'));

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

echo "<h3>".$p->t('benotungstool/uebungVerwalten')."</h3>";

//Anlegen einer neuen Uebung
if(isset($_POST['uebung_neu']) || isset($_POST['abgabe_neu']))
{
	if(isset($thema))
	{
		//pruefen ob alle Daten eingegeben wurden
		$error=false;
		if($thema=='')
		{
			$error_thema.= "<span class='error'>".$p->t('benotungstool/themaMussEingegebenWerden')."</span>";
			$error=true;
		}
		if(!is_numeric($gewicht))
		{
			echo "<span class='error'>".$p->t('benotungstool/gewichtMussEineZahlSein')."</span>";
			$error = true;
		}
		if (isset($_POST['uebung_neu']))
		{
			if(!is_numeric($punkteprobeispiel))
			{
				$error_punkteprobeispiel= "<span class='error'>".$p->t('benotungstool/punkteProBeispielGueltigeZahl')."</span>";
				$error=true;
			}
			elseif($punkteprobeispiel<0)
			{
				$error_punkteprobeispiel = "<span class='error'>".$p->t('benotungstool/punkteProBeispielNichtNegativ')."</span>";
				$error=true;
			}
			if(!is_numeric($anzahlderbeispiele))
			{
				$error_anzahlderbeispiele = "<span class='error'>".$p->t('benotungstool/anzahlDerBeispieleGueltigeZahl')."</span>";
				$error=true;
			}
			elseif($anzahlderbeispiele<0)
			{
				$error_anzahlderbeispiele = "<span class='error'>".$p->t('benotungstool/anzahlDerBeispieleNichtNegativ')."</span>";
				$error=true;
			}
			elseif($anzahlderbeispiele>99)
			{
				$error_anzahlderbeispiele = "<span class='error'>".$p->t('benotungstool/anzahlDerBeispieleKleiner100')."</span>";
				$error=true;
			}
	
			if ($maxstd != '')
			{
				if(!is_numeric($maxstd))
				{
					$error_maxstd = "<span class='error'>".$p->t('benotungstool/anzahlStudentenGueltigeZahl')."</span>";
					$error=true;
				}
				elseif($maxstd<0)
				{
					$error_maxstd = "<span class='error'>".$p->t('benotungstool/anzahlStudentenNichtNegativ')."</span>";
					$error=true;
				}
				elseif($maxstd>99)
				{
					$error_maxd = "<span class='error'>".$p->t('benotungstool/anzahlStudentenKleiner100')."</span>";
					$error=true;
				}
			}
			else
				$maxstd = null;
			
			if ($maxbsp != '')
			{
				if(!is_numeric($maxbsp))
				{
					$error_maxbsp = "<span class='error'>".$p->t('benotungstool/anzahlStudentenGueltigeZahl')."</span>";
					$error=true;
				}
				elseif($maxbsp<0)
				{
					$error_maxbsp = "<span class='error'>".$p->t('benotungstool/anzahlStudentenNichtNegativ')."</span>";
					$error=true;
				}
				elseif($maxbsp>99)
				{
					$error_maxbsp = "<span class='error'>".$p->t('benotungstool/anzahlStudentenKleiner100')."</span>";
					$error=true;
				}
			}
			else
				$maxbsp = null;
		}
		
		$freigabevon_sav = $datum_obj->mktime_datumundzeit($freigabevon);
		$freigabebis_sav = $datum_obj->mktime_datumundzeit($freigabebis);

		if(!$freigabebis_sav)
		{
			$error_freigabebis = "<span class='error'>".$p->t('benotungstool/bisDatumUngueltigesFormat')."</span>";
			$error=true;
		}

		if(!$freigabevon_sav)
		{
			$error_freigabevon = "<span class='error'>".$p->t('benotungstool/vonDatumUngueltigesFormat')."</span>";
			$error=true;
		}

		if($freigabevon_sav && $freigabebis_sav && $freigabevon_sav>$freigabebis_sav)
		{
					$error_freigabevon = "<span class='error'>".$p->t('benotungstool/vonDatumNichtGroesserAlsBisDatum')."</span>";
					$error=true;
		}

		if(!$error)
		{

			//Uebung anlegen (KL oder Abgabe)
			$datum_obj = new datum();
			$uebung_obj = new uebung();
			//$uebung_obj->gewicht='';
			$uebung_obj->punkte='';
			$uebung_obj->angabedatei='';
			$uebung_obj->freigabevon = date('Y-m-d H:i',$freigabevon_sav);
			$uebung_obj->freigabebis = date('Y-m-d H:i',$freigabebis_sav);
			if (isset($_POST["uebung_neu"]))
			{
				if (isset($_POST["kl_abgabe"]))				
					$uebung_obj->abgabe=true;
				else
					$uebung_obj->abgabe=false;
				$uebung_obj->beispiele=true;
			}
			else
			{
				$uebung_obj->abgabe=true;
				$uebung_obj->beispiele=false;
			}
			$uebung_obj->bezeichnung=$thema;
			$uebung_obj->positiv=isset($_POST['positiv']);
			$uebung_obj->defaultbemerkung='';
			$uebung_obj->lehreinheit_id=$lehreinheit_id;
			$uebung_obj->updateamum = date('Y-m-d H:i:s');
			$uebung_obj->updatevon = $user;
			$uebung_obj->insertamum = date('Y-m-d H:i:s');
			$uebung_obj->insertvon = $user;
			$uebung_obj->statistik = isset($_POST['statistik']);
			$uebung_obj->liste_id = $liste_id;
			$uebung_obj->maxstd = $maxstd;
			$uebung_obj->maxbsp = $maxbsp;
			$uebung_obj->gewicht = $gewicht;
			$uebung_obj->get_next_nummer();
			$uebung_obj->nummer = $uebung_obj->next_nummer;	

			if($uebung_obj->save(true))
			{
				$uebung_id = $uebung_obj->uebung_id;
				
				//Angabedatei ablegen
				if ($angabedatei_up)
				{
					$name_up = pathinfo($_FILES["angabedatei"]["name"]);
					//Handle double file extensions (e.g.: .tar.gz)
					//Array of possible double extensions
					$ext_array = array('.tar.gz','.tar.bz2','.tar.xz','.tar.lzma','.tar.Z');
					//Find occurence of extensions ending with ".tar."
					if (in_array(substr($_FILES["angabedatei"]["name"], strripos($_FILES["angabedatei"]["name"], '.tar.')), $ext_array))
						$extension = substr($_FILES["angabedatei"]["name"], strripos($_FILES["angabedatei"]["name"]+1, '.tar.'));
					else 
						$extension = $name_up["extension"];

					$name_neu = makeUploadName($db, $which='angabe', $lehreinheit_id=$lehreinheit_id, $uebung_id=$uebung_id, $ss=$stsem);
					$angabedatei = $name_neu.".".$extension;
					
					$angabepfad = BENOTUNGSTOOL_PATH."angabe/".$angabedatei;
					//$angabepfad = BENOTUNGSTOOL_PATH.$angabedatei;
					//unlink($angabepfad);
					//echo $angabepfad;
					move_uploaded_file($_FILES['angabedatei']['tmp_name'], $angabepfad);
					$uebung_obj->angabedatei = $angabedatei;
					$uebung_obj->save(false);
				}
				//Beispiele anlegen
				
				$error_msg='';
				for($i=0;$i<$anzahlderbeispiele;$i++)
				{
					$beispiel_obj = new beispiel();
					$beispiel_obj->uebung_id = $uebung_id;
					$beispiel_obj->bezeichnung = "Beispiel ".($i<9?'0'.($i+1):($i+1));
					$beispiel_obj->punkte = $punkteprobeispiel;
					$beispiel_obj->updateamum = date('Y-m-d H:i:s');
					$beispiel_obj->updatevon = $user;
					$beispiel_obj->insertamum = date('Y-m-d H:i:s');
					$beispiel_obj->insertvon = $user;
					$beispiel_obj->get_next_nummer();
					$beispiel_obj->nummer = $beispiel_obj->next_nummer;

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
		echo "<span class='error'>".$p->t('benotungstool/kreuzerllisteNichtAngelegt')."!</span><br>";
}

//Loeschen eines Beispiels
if(isset($_POST['beispiel_delete']))
{
	if(isset($_POST['beispiel']))
	{
		$beispiel_obj = new beispiel();
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

//Loeschen einer Uebung
if(isset($_POST['delete_uebung']))
{
	if(isset($_POST['uebung']))
	{
		$ueb_obj = new uebung();
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
		echo "<span class='error'>".$p->t('benotungstool/themaMussEingegebenWerden')."</span>";
		$error = true;
	}
	if(!is_numeric($gewicht))
	{
		echo "<span class='error'>".$p->t('benotungstool/gewichtMussEineZahlSein')."</span>";
		$error = true;
	}
	$freigabevon_sav = $datum_obj->mktime_datumundzeit($freigabevon);
	$freigabebis_sav = $datum_obj->mktime_datumundzeit($freigabebis);

	if ($maxstd != '')
		{
			if(!is_numeric($maxstd))
			{
				echo "<span class='error'>".$p->t('benotungstool/anzahlStudentenGueltigeZahl')."</span>";
				$error=true;
			}
			elseif($maxstd<0)
			{
				echo "<span class='error'>".$p->t('benotungstool/anzahlStudentenNichtNegativ')."</span>";
				$error=true;
			}
			elseif($maxstd>99)
			{
				echo "<span class='error'>".$p->t('benotungstool/anzahlStudentenKleiner100')."</span>";
				$error=true;
			}
		}
		else
			$maxstd = null;
		
		if ($maxbsp != '')
		{
			if(!is_numeric($maxbsp))
			{
				echo "<span class='error'>".$p->t('benotungstool/anzahlStudentenGueltigeZahl')."</span>";
				$error=true;
			}
			elseif($maxbsp<0)
			{
				echo "<span class='error'>".$p->t('benotungstool/anzahlStudentenNichtNegativ')."</span>";
				$error=true;
			}
			elseif($maxbsp>99)
			{
				echo "<span class='error'>".$p->t('benotungstool/anzahlStudentenKleiner100')."</span>";
				$error=true;
			}
		}
		else
			$maxbsp = null;
	
	if($freigabevon_sav>$freigabebis_sav)
	{
		echo "<span class='error'>".$p->t('benotungstool/vonDatumNichtGroesserAlsBisDatum')."</span>";
		$error=true;
	}
	if(!$freigabebis_sav)
	{
		echo "<span class='error'>".$p->t('benotungstool/bisDatumUngueltigesFormat')."</span>";
		$error=true;
	}

	if(!$freigabevon_sav)
	{
		echo "<span class='error'>".$p->t('benotungstool/vonDatumUngueltigesFormat')."</span>";
		$error=true;
	}

	if(!$error)
	{
		//Angabedatei ablegen
		if ($angabedatei_up)
		{
			$name_up = pathinfo($_FILES["angabedatei"]["name"]);
			//Handle double file extensions (e.g.: .tar.gz)
			//Array of possible double extensions
			$ext_array = array('.tar.gz','.tar.bz2','.tar.xz','.tar.lzma','.tar.Z');
			//Find occurence of extensions ending with ".tar."
			if (in_array(substr($_FILES["angabedatei"]["name"], strripos($_FILES["angabedatei"]["name"], '.tar.')), $ext_array))
				$extension = substr($_FILES["angabedatei"]["name"], strripos($_FILES["angabedatei"]["name"]+1, '.tar.'));
			else
				$extension = $name_up["extension"];
			$name_neu = makeUploadName($db, $which='angabe', $lehreinheit_id=$lehreinheit_id, $uebung_id=$uebung_id, $ss=$stsem);
			$angabedatei_neu = $name_neu.".".$extension;
			
			$angabepfad = BENOTUNGSTOOL_PATH."angabe/".$angabedatei_neu;
			//$angabepfad = BENOTUNGSTOOL_PATH.$angabedatei;
			//unlink($angabepfad);
			//echo $angabepfad;
			foreach (glob(BENOTUNGSTOOL_PATH."angabe/*".$uebung_id.".*") as $old)
				unlink($old);
			move_uploaded_file($_FILES['angabedatei']['tmp_name'], $angabepfad);
		}
		else
		{	
			$uebung_akt = new uebung();
			$uebung_akt->load($uebung_id);
			$angabedatei_neu = $uebung_akt->angabedatei;
		}
		$uebung_obj = new uebung();
		$uebung_obj->load($uebung_id);
		$uebung_obj->gewicht=$gewicht;
		$uebung_obj->punkte='';
		$uebung_obj->angabedatei=$angabedatei_neu;
		$uebung_obj->freigabevon = date('Y-m-d H:i',$freigabevon_sav);
		$uebung_obj->freigabebis = date('Y-m-d H:i',$freigabebis_sav);
		if ($uebung_obj->beispiele)		
			$uebung_obj->abgabe = (isset($_POST['kl_abgabe'])?true:false);
		//$uebung_obj->beispiele=true;
		$uebung_obj->bezeichnung=$thema;
		$uebung_obj->positiv=(isset($_POST['positiv'])?true:false);
		$uebung_obj->defaultbemerkung='';
		$uebung_obj->lehreinheit_id=$lehreinheit_id;
		$uebung_obj->updateamum = date('Y-m-d H:i:s');
		$uebung_obj->updatevon = $user;
		$uebung_obj->uebung_id = $uebung_id;
		$uebung_obj->statistik = (isset($_POST['statistik'])?true:false);
		$uebung_obj->liste_id = $_POST["liste_id"];
		$uebung_obj->maxstd = $maxstd;
		$uebung_obj->maxbsp = $maxbsp;

		if($uebung_obj->save(false))
			echo "Die &Auml;nderung wurde gespeichert!";
		else
			echo "<span class='error'>$uebung_obj->errormsg</span>";
	}

}

// Notenschluessel toggle

if (isset($_GET['liste_id']) && isset($_GET['notenschluessel']))
{
	$ueb_ns = new uebung();
	$ueb_ns->toggle_prozent_punkte($_GET['liste_id']);
	echo $ueb_ns->errormsg;
}


//Editieren einer Liste
if(isset($_POST['liste_edit']))
{
	$error = false;
	if($thema=='')
	{
		echo "<span class='error'>".$p->t('benotungstool/themaMussEingegebenWerden')."</span>";
		$error = true;
	}
	if(!is_numeric($gewicht))
	{
		echo "<span class='error'>".$p->t('benotungstool/gewichtMussEineZahlSein')."</span>";
		$error = true;
	}
	

	if(!$error)
	{
		
		$uebung_obj = new uebung();
		$uebung_obj->load($_GET['liste_id']);
		$uebung_obj->gewicht=$gewicht;
		$uebung_obj->punkte='';
		$uebung_obj->angabedatei='';
		$uebung_obj->freigabevon = null;
		$uebung_obj->freigabebis = null;
		//$uebung_obj->abgabe=false;
		//$uebung_obj->beispiele=true;
		$uebung_obj->bezeichnung=$thema;
		$uebung_obj->positiv=(isset($_POST['positiv'])?true:false);
		$uebung_obj->defaultbemerkung='';
		$uebung_obj->lehreinheit_id=$lehreinheit_id;
		$uebung_obj->updateamum = date('Y-m-d H:i:s');
		$uebung_obj->updatevon = $user;
		$uebung_obj->uebung_id = $_REQUEST["liste_id"];
		//$uebung_obj->statistik = (isset($_POST['statistik'])?true:false);
		$uebung_obj->liste_id = '';
		//$uebung_obj->maxstd = $maxstd;
		//$uebung_obj->maxbsp = $maxbsp;

		if($uebung_obj->save(false))
			echo $p->t('global/erfolgreichgespeichert')."!";
		else
			echo "<span class='error'>$uebung_obj->errormsg</span>";
	}

}

//Neues Beispiel anlegen
if(isset($_POST['beispiel_neu']) || isset($_POST['beispiel_edit']))
{
	if(isset($_POST['beispiel_edit']) && (!isset($beispiel_id) || !is_numeric($beispiel_id)))
	{
		echo "<span class='error'>".$p->t('benotungstool/beispielIdUngueltig')."</span>";
	}
	else
	{
		if(isset($uebung_id) && $uebung_id!='' && is_numeric($uebung_id))
		{
			$punkte = (isset($_POST['punkte'])?$_POST['punkte']:'');
			$punkte = mb_ereg_replace(',','.',$punkte);
			$bezeichnung = $_REQUEST["bezeichnung"];
			if(is_numeric($punkte) && $punkte!='')
			{
				if($bezeichnung!='')
				{
					$beispiel_obj = new beispiel();
					if(isset($_POST['beispiel_edit']))
					{
						$beispiel_obj->load($beispiel_id);					
						$beispiel_obj->beispiel_id= $beispiel_id;
						$beispiel_obj->new=false;
					}
					else
					{
						$beispiel_obj->new=true;
						$beispiel_obj->insertamum = date('Y-m-d H:i:s');
						$beispiel_obj->insertvon = $user;
						$beispiel_obj->get_next_nummer();
						$beispiel_obj->nummer = $beispiel_obj->next_nummer;
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
					echo "<span class='error'>".$p->t('benotungstool/bezeichnungMussEingegebenWerden')."</span>";
			}
			else
				echo "<span class='error'>".$p->t('benotungstool/punkteMuessenEineGueltigeZahlSein')."</span>";
		}
		else
			echo "<span class='error'>".$p->t('benotungstool/zugehoerigeUebungFehlerhaft')."</span>";
	}
}



//Uebersichtstabelle
if(isset($_GET["uebung_id"]) && $_GET["uebung_id"]!='')
{
	
	echo "<table><tr><td valign='top'>";
	//Bearbeiten der ausgewaehlten Uebung
	echo "<form accept-charset='UTF-8' action='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&liste_id=$liste_id' method=POST enctype='multipart/form-data'>\n";
	echo "<table><tr><td colspan='2' width='340' class='ContentHeader3'>".$p->t('benotungstool/ausgewaehlteAufgabeBearbeiten')."</td><td>&nbsp;</td></tr>\n";
	echo "<tr><td>&nbsp;</td><td></td></tr>";

	$uebung_obj = new uebung();
	$uebung_obj->load($uebung_id);
	//$downloadname = mb_ereg_replace($uebung_id,ereg_replace(' ','_',$uebung_obj->bezeichnung), $uebung_obj->angabedatei);
	$downloadname = mb_str_replace(' ', '_', $uebung_obj->bezeichnung);
	$downloadname = mb_str_replace($uebung_id, $downloadname, $uebung_obj->angabedatei);
	echo "
	<tr><td>".$p->t('benotungstool/thema')."</td><td align='right'><input type='text' name='thema'  maxlength='32' value='".htmlentities($uebung_obj->bezeichnung,ENT_QUOTES,'UTF-8')."'></td><td>$error_thema</td></tr>
	<tr><td>".$p->t('benotungstool/freigabe')."</td><td align='right'>von <input type='text' size='16' name='freigabevon' value='".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabevon))."'></td></tr>
	<tr><td>".$p->t('benotungstool/format')."</td><td align='right'>bis <input type='text' size='16' name='freigabebis' value='".date('d.m.Y H:i',$datum_obj->mktime_fromtimestamp($uebung_obj->freigabebis))."'></td></tr>";
	
	if ($uebung_obj->beispiele){
		echo "<tr><td>".$p->t('benotungstool/maxStudentenBeispiel')."</td><td align='right'><input type='text' name='maxstd' value='$uebung_obj->maxstd'></td><td>$error_maxstd</td></tr>
	<tr><td>".$p->t('benotungstool/maxBeispieleStudent')."</td><td align='right'><input type='text' name='maxbsp' value='$uebung_obj->maxbsp'></td><td>$error_maxbsp</td></tr>";
		echo"<tr><td>".$p->t('benotungstool/abgabe')." </td><td><input type='checkbox' name='kl_abgabe' ".($uebung_obj->abgabe?'checked':'')."></td></tr>";
		echo "<input type='hidden' size='16' name='gewicht' value='0'>";
	}	
	else if ($uebung_obj->abgabe)
	{
		echo "<tr><td>".$p->t('benotungstool/gewicht')."</td><td align='right'><input type='text' size='16' name='gewicht' value='$uebung_obj->gewicht'></td><td>$error_gewicht</td></tr>";
		echo "<tr><td>".$p->t('benotungstool/positiv')." </td><td><input type='checkbox' name='positiv' ".($uebung_obj->positiv?'checked':'')."></td></tr>";
	}
	if ($uebung_obj->beispiele)	
		echo"<tr><td>".$p->t('benotungstool/statistikFuerStudentenAnzeigen')." </td><td><input type='checkbox' name='statistik' ".($uebung_obj->statistik?'checked':'')."></td></tr>";
	echo "<tr>";
	echo "<td>".$p->t('benotungstool/angabeidatei')."</td>";
	if ($uebung_obj->angabedatei != '')
		echo "<td><a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&liste_id=$liste_id&download=".$downloadname."'>".$downloadname."</a> <a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&liste_id=$liste_id&deletefile=1'>[del]</a></td>";
	else
		echo "<td></td>";
	echo "</tr>";
	echo "
	<tr><td></td><td><input type='file' name='angabedatei'></td></tr>
	<tr><td colspan=2 align='right'><input type='submit' name='uebung_edit' value='".$p->t('global/speichern')."'></td></tr>
	</table>
	<input type='hidden' name='liste_id' value='".$liste_id."'>
	</form>";

	$beispiel_obj = new beispiel();
	$beispiel_obj->load_beispiel($uebung_id);
	$anzahl = count($beispiel_obj->beispiele);
	echo "</td><td valign='top'>";

	//Beispiel neu Anlegen
	if ($uebung_obj->beispiele)
	{
		echo "<form accept-charset='UTF-8' action='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&liste_id=$liste_id' method=POST>\n";
		echo "<table width='340'><tr><td colspan='3' class='ContentHeader3'>".$p->t('benotungstool/neuesBeispielAnlegen')."</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td></td></tr>\n\n";
	
		echo "<tr><td>".$p->t('global/bezeichnung')." <input type='text' name='bezeichnung' maxlength='32' value='".$p->t('benotungstool/beispiel')." ".($anzahl<9?'0'.($anzahl+1):($anzahl+1))."'>";
		echo "&nbsp;".$p->t('benotungstool/punkte')." <input type='text' size='2' name='punkte' value='1'></td></tr>";
		echo "<tr><td align='right'><input type='submit' name='beispiel_neu' value='".$p->t('benotungstool/anlegen')."'></td></tr>";
	
		echo "</table>
		</form>";
	}
	
	echo "</td></tr><tr><td valign='top'>";
	
	
	//Uebersicht der Beispiele
	if ($uebung_obj->beispiele)
	{
		echo "<form accept-charset='UTF-8' action='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&liste_id=$liste_id' method=POST>\n";
		echo "<table width='340'><tr><td colspan='3' class='ContentHeader3'>".$p->t('benotungstool/vorhandeneBeispiele')."</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td></td><td></td></tr>\n\n";
	
		if($anzahl>0)
		{
			echo "<tr><th>".$p->t('benotungstool/beispiel')."</th><th>".$p->t('benotungstool/punkte')."</th><th>".$p->t('benotungstool/auswahl')."</th></tr>\n";
			foreach ($beispiel_obj->beispiele as $row)
			{
				echo "<tr><td><a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&beispiel_id=$row->beispiel_id&liste_id=$liste_id' class='Item'><u>".$row->bezeichnung."</u></a></td>
				<td align='center'>$row->punkte</td>
				<td align='center'><input type='Checkbox' name='beispiel[]' value='$row->beispiel_id'></td>";
			}
			echo "<tr><td></td><td></td><td align='right'><input type='Submit' value='".$p->t('benotungstool/auswahlLoeschen')."' onclick='return confirmdelete()' name='beispiel_delete'></td></tr>";
		}
		else
			echo "<tr><td colspan='3'>".$p->t('benotungstool/keineBeispieleAngelegt')."</td><td></td></tr>";
	
		echo "</table></form>";
	}
	echo "</td><td valign='top'>";

	//Beispiel Aendern
	$error_msg = '';
	if(isset($beispiel_id) && $beispiel_id!='')
	{
		//Bearbeiten eines Beispiels
		if($beispiel_obj->load($beispiel_id))
		{
			echo "<form accept-charset='UTF-8' action='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&beispiel_id=$beispiel_id&liste_id=$liste_id' method=POST>\n";
			echo "<table width='340'><tr><td colspan='3' class='ContentHeader3'>".$p->t('benotungstool/beispielBearbeiten')."</td></tr>\n";
			echo "<tr><td>&nbsp;</td><td></td></tr>\n\n";

			echo "<tr><td>".$p->t('global/bezeichnung')." <input type='text' name='bezeichnung' maxlength='32' value='".htmlentities($beispiel_obj->bezeichnung,ENT_QUOTES,'UTF-8')."'>";
			echo "&nbsp;".$p->t('benotungstool/punkte')." <input type='text' size='2' name='punkte' value='$beispiel_obj->punkte'></td></tr>";
			echo "<tr><td align='right'><input type='submit' name='beispiel_edit' value='".$p->t('global/aendern')."'></td></tr>";

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
	if(isset($liste_id) && $liste_id!='')
	{
		echo "<table><tr><td valign='top'>";
		//Bearbeiten der ausgewaehlten Liste
		echo "<form accept-charset='UTF-8' action='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id' method=POST>\n";
		echo "<table><tr><td colspan='2' width='340' class='ContentHeader3'>".$p->t('benotungstool/uebungBearbeiten')."</td><td>&nbsp;</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td></td></tr>";
	
		$liste_obj = new uebung();
		$liste_obj->load($liste_id);
	
		echo "
		<tr><td>".$p->t('benotungstool/thema')."</td><td align='right'><input type='text' name='thema'  maxlength='32' value='".htmlentities($liste_obj->bezeichnung,ENT_QUOTES,'UTF-8')."'></td><td>$error_thema</td></tr><tr><td>".$p->t('benotungstool/gewicht')."</td><td align='right'><input type='text' size='16' name='gewicht' value='$liste_obj->gewicht'></td><td>$error_gewicht</td></tr>
		<tr><td>".$p->t('benotungstool/positiv')." </td><td><input type='checkbox' name='positiv' ".($liste_obj->positiv?'checked':'')."></td></tr>
		<tr><td colspan=2 align='right'><input type='submit' name='liste_edit' value='".$p->t('global/speichern')."'></td></tr>
		</table>
		</form>";
	}
	
	//Gesamtuebersicht ueber alle Listen innerhalb der Uebung
	echo "<table><tr><td valign='top'>";
	echo "<form accept-charset='UTF-8' action='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id' method=POST>";
	echo "<table width='440'>";
	$studentuebung = new uebung();
	if (!$studentuebung->check_studentuebung($liste_id))	
		echo "<tr><td colspan='3' class='ContentHeader3'>".$p->t('benotungstool/vorhandeneAufgabeBearbeiten')."</td></tr>";

	$uebung_obj = new uebung();
	$uebung_obj->load_uebung($lehreinheit_id,$level=2,$uebung_id=$liste_id);
	$anzahl = count($uebung_obj->uebungen);
	$copy_content="<table cellpadding=0><tr><td class='ContentHeader3'>".$p->t('benotungstool/uebungInAndereLeKopieren')."</td></tr><tr><td></td><td></td><td>&nbsp;</td></tr><tr><th>&nbsp;</th></tr>";
	$has_copy_content=false;
	if($anzahl>0)
	{
		echo "<tr><td></td><td></td><td>&nbsp;</td></tr><tr><th>".$p->t('benotungstool/thema')."</th><th>".$p->t('benotungstool/freigeschalten')."</th><th>".$p->t('benotungstool/auswahl')."</th><th>&nbsp;</th></tr>";

		//Alle Lehreinheiten holen die zu dieser lehrveranstaltung gehoeren
		//und der angemeldete User berechtigt ist
		$copy_option_content = array();
		for($i=0;$i<$db->db_num_rows($result_alle_lehreinheiten);$i++)
		{
			$row_alle_lehreinheiten = $db->db_fetch_object($result_alle_lehreinheiten,$i);
			if($lehreinheit_id!=$row_alle_lehreinheiten->lehreinheit_id)
			{
				//zugeteilte Lektoren holen
				$qry_lektoren = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter using(mitarbeiter_uid) WHERE lehreinheit_id=".$db->db_add_param($row_alle_lehreinheiten->lehreinheit_id, FHC_INTEGER);
				if($result_lektoren = $db->db_query($qry_lektoren))
				{
					$lektoren = '( ';
					$j=0;
					while($row_lektoren = $db->db_fetch_object($result_lektoren))
					{
						$lektoren .= $row_lektoren->kurzbz;
						$j++;
						if($j<$db->db_num_rows($result_lektoren))
							$lektoren.=', ';
						else
							$lektoren.=' ';
					}
					$lektoren .=')';
				}
				//zugeteilte Gruppen holen
				$qry_gruppen = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=".$db->db_add_param($row_alle_lehreinheiten->lehreinheit_id,FHC_INTEGER);
				if($result_gruppen = $db->db_query($qry_gruppen))
				{
					$gruppen = '';
					$j=0;
					while($row_gruppen = $db->db_fetch_object($result_gruppen))
					{
						if($row_gruppen->gruppe_kurzbz=='')
							$gruppen.=$row_gruppen->semester.$row_gruppen->verband.$row_gruppen->gruppe;
						else
							$gruppen.=$row_gruppen->gruppe_kurzbz;
						$j++;
						if($j<$db->db_num_rows($result_gruppen))
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
			echo "<tr height=23><td align='left'><a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id&liste_id=$liste_id' class='Item'><u>".$row->bezeichnung."</u></a></td><td align='center'>";

			if((strtotime(strftime($row->freigabevon))<=time()) && (strtotime(strftime($row->freigabebis))>=time()))
				echo $p->t('global/ja');
			else
				echo $p->t('global/nein');
			echo "</td><td align='center'><input type='Checkbox' name='uebung[]' value='$row->uebung_id'></td>";
			//Wenn andere Lehreinheiten vorhanden sind dann wird die moeglichkeit zum kopieren von
			//Uebungen in diese Lehreinheiten angeboten.
			if(isset($result_alle_lehreinheiten) && $db->db_num_rows($result_alle_lehreinheiten)>1)
			{
				$copy_content.= '<tr height=23>';
				$copy_content.= '<td nowrap align="right">';
				$copy_option_content = '';
				//Lehreinheiten fuer Combo durchgehen und schauen ob
				//fuer diese Lehreinheit bereits eine Uebung mit gleichem Namen existiert
				//Falls ja wird diese nicht in der Combo angezeigt
				foreach ($copy_le_content as $id=>$bezeichnung)
				{
					$qry = "SELECT uebung_id FROM campus.tbl_uebung WHERE lehreinheit_id=".$db->db_add_param($id, FHC_INTEGER)." AND bezeichnung=".$db->db_add_param($row->bezeichnung);
					//echo $qry;
					if($result_vorhanden = $db->db_query($qry))
					{
						if($db->db_num_rows($result_vorhanden)==0)
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
					$copy_content.= "\n<form accept-charset='UTF-8'  style='margin:1px;' action='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&kopieren=true&uebung_copy_id=$row->uebung_id' method='POST'>";
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
		}
		echo "<tr><td></td><td></td><td><input type='Submit' value='".$p->t('benotungstool/auswahlLoeschen')."' name='delete_uebung' onclick='return confirmdelete();'></td></tr>";
		if ($row->beispiele)
			$anzeigen = 'beispiele';
		else
			$anzeigen = 'abgabe';
	}
	else
	{
		$studentuebung = new uebung();
		if (!$studentuebung->check_studentuebung($liste_id))
		{
			echo "<tr><td colspan='3'>".$p->t('benotungstool/derzeitSindKeineAufgabenAngelegt')."</td><td></td></tr>";
			$anzeigen = 'beide';
		}
		else
			$anzeigen = "nada";
	}

	echo "</table>
	</form><br><br>";

	//Kopier-Buttons anzeigen
	$copy_content.='</table>';
	echo "</td><td valign='top'>";
	//if($has_copy_content)
	//	echo $copy_content;
	echo "</td></tr></table>";

	//Uebung neu anlegen
	if(!isset($_POST['uebung_neu']))
	{
		$thema = $p->t('benotungstool/liste')." ".($anzahl<9?'0'.($anzahl+1):($anzahl+1));
		$anzahlderbeispiele = 10;
		$punkteprobeispiel = 1;
		$freigabevon = date('d.m.Y H:i');
		$freigabebis = date('d.m.Y H:i');
		$maxstudentenprobeispiel = '';
		$maxbeispieleprostudent = '';
		$gewicht = 1;
		
	}
	echo "</td><td valign='top'>";
	
	if ($anzeigen != 'abgabe' && $anzeigen != 'nada')
	{
		echo "
	<form accept-charset='UTF-8' action='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id' method=POST enctype='multipart/form-data'>
	<table >
	<tr><td width='440' colspan=2 class='ContentHeader3'>".$p->t('benotungstool/neueKreuzerllisteAnlegen')."</td><td></td></tr>
	<tr><td>".$p->t('benotungstool/thema')."</td><td align='right'><input type='text' name='thema' maxlength='32' value='$thema'></td><td><span class='error'>$error_thema</td></tr>
	<tr><td>".$p->t('benotungstool/anzahlDerBeispiele')."</td><td align='right'><input type='text' name='anzahlderbeispiele' maxlength='2' size='2' value='$anzahlderbeispiele'></td><td>$error_anzahlderbeispiele</td></tr>
	<tr><td>".$p->t('benotungstool/anzahlPunkteProBeispiel')."</td><td align='right'><input type='text' name='punkteprobeispiel' value='$punkteprobeispiel'></td><td>$error_punkteprobeispiel</td></tr>
	<tr><td>".$p->t('benotungstool/maxStudentenBeispiel')."</td><td align='right'><input type='text' name='maxstd' value='$maxstd'></td><td>$error_maxstd</td></tr>
	<tr><td>".$p->t('benotungstool/maxBeispieleStudent')."</td><td align='right'><input type='text' name='maxbsp' value='$maxbsp'></td><td>$error_maxbsp</td></tr>
	<tr><td>".$p->t('benotungstool/freigabe')."</td><td align='right'>von <input type='text' size='16' name='freigabevon' value='$freigabevon'></td><td>$error_freigabevon</td></tr>
	<tr><td>".$p->t('benotungstool/format')."</td><td align='right'>bis <input type='text' size='16' name='freigabebis' value='$freigabebis'></td><td>$error_freigabebis</td></tr>
	<input type='hidden' size='16' name='gewicht' value='0'>
	<tr><td>".$p->t('benotungstool/abgabe')." </td><td><input type='checkbox' name='kl_abgabe'></td></tr>
	<tr><td>".$p->t('benotungstool/statistikFuerStudentenAnzeigen')." </td><td><input type='checkbox' name='statistik'></td></tr>
	<tr><td>".$p->t('benotungstool/angabeidatei')."</td><td><input type='file' name='angabedatei'></td></tr>
	<tr><td colspan=2 align='right'><input type='submit' name='uebung_neu' value='".$p->t('benotungstool/anlegen')."'></td></tr>
	</table>
	</form>
	";
		// notenschlüssel
		$qry = "select * from campus.tbl_notenschluesseluebung where uebung_id = ".$db->db_add_param($liste_id, FHC_INTEGER)." order by note";
		if($result = $db->db_query($qry))
		{
			$notenschluessel = array();
			$notenschluessel[1] = '';
			$notenschluessel[2] = '';
			$notenschluessel[3] = '';
			$notenschluessel[4] = '';
			$notenschluessel[5] = '';
			if($db->db_num_rows($result)>=1)
			{
				while($schluesselrow = $db->db_fetch_object($result))
				{
					$notenschluessel[$schluesselrow->note] = $schluesselrow->punkte;
				}
			}
		}
		
		if ($anzeigen != "beide")
		{	
			if ($liste_obj->prozent == 't')
			{
				$einheit = " %";
				$einheit_link = $p->t('benotungstool/notenschluesselInProzent')." / <a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id&notenschluessel=punkte'>".$p->t('benotungstool/punkten')."</a>";
			}			
			else
			{
				$einheit=" ".$p->t('benotungstool/punkte');
				$einheit_link = $p->t('benotungstool/notenschluesselIn')." <a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id&notenschluessel=prozent'>".$p->t('benotungstool/prozentPunkten');
			}
			
			echo "<form accept-charset='UTF-8' action='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id' method='POST' name='ns'>\n";
			echo "<table><tr><td colspan='3' class='ContentHeader3'>".$p->t('benotungstool/notenschluesselDefinieren')."</td></tr>\n";
			echo "<tr>";
			echo "<td colspan='3'>";
			echo $einheit_link;
			echo "</td>";			
			echo "</tr>";			
			echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
			echo "<tr><td>".$p->t('benotungstool/note')."</td><td>".$p->t('benotungstool/minimum')."</td></tr>";
			echo "<tr><td><input type='text' name='schluessel_note_1' maxlength='2' size='2' value='1' readonly></td><td><input type='text' size='4' name='schluessel_punkte_1' value='$notenschluessel[1]'>$einheit</td></tr>";
			echo "<tr><td><input type='text' name='schluessel_note_2' maxlength='2' size='2' value='2' readonly></td><td><input type='text' size='4' name='schluessel_punkte_2' value='$notenschluessel[2]'>$einheit</td></tr>";
			echo "<tr><td><input type='text' name='schluessel_note_3' maxlength='2' size='2' value='3' readonly></td><td><input type='text' size='4' name='schluessel_punkte_3' value='$notenschluessel[3]'>$einheit</td></tr>";
			echo "<tr><td><input type='text' name='schluessel_note_4' maxlength='2' size='2' value='4' readonly></td><td><input type='text' size='4' name='schluessel_punkte_4' value='$notenschluessel[4]'>$einheit</td></tr>";
			echo "<tr><td><input type='text' name='schluessel_note_5' maxlength='2' size='2' value='5' readonly></td><td><input type='text' size='4' name='schluessel_punkte_5' value='$notenschluessel[5]'>$einheit</td></tr>";
			echo "<tr>";
			echo "<td align='right' colspan='3'>";
			if ($liste_obj->prozent == 't')
				echo "<input type='button' onclick='set_notenschluessel_prozent();' value='".$p->t('benotungstool/standardwerteSetzen')."'><br>";
			echo "<input type='submit' name='schluessel' value='".$p->t('global/speichern')."'></td>";
			echo "</tr>";
		
			echo "</table>
			</form>";
		}
	}
	if(!isset($_POST['uebung_neu']))
		$thema = "Abgabe ".($anzahl<9?'0'.($anzahl+1):($anzahl+1));
	
	if ($anzeigen != 'beispiele' && $anzeigen != 'nada')
	{
		echo "
	<form accept-charset='UTF-8' action='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id' method=POST enctype='multipart/form-data'>
	<table >
	<tr><td width='440' colspan=2 class='ContentHeader3'>".$p->t('benotungstool/neueAbgabeAnlegen')."</td><td></td></tr>
	<tr><td>".$p->t('benotungstool/thema')."</td><td align='right'><input type='text' name='thema' maxlength='32' value='".htmlentities($thema,ENT_QUOTES,'UTF-8')."'></td><td><span class='error'>$error_thema</td></tr>
	<tr><td>".$p->t('benotungstool/freigabe')."</td><td align='right'>von <input type='text' size='16' name='freigabevon' value='$freigabevon'></td><td>$error_freigabevon</td></tr>
	<tr><td>".$p->t('benotungstool/format')."</td><td align='right'>bis <input type='text' size='16' name='freigabebis' value='$freigabebis'></td><td>$error_freigabebis</td></tr>
	<tr><td>".$p->t('benotungstool/gewicht')."</td><td align='right'><input type='text' size='16' name='gewicht' value='$gewicht'></td><td>$error_gewicht</td></tr>
	<tr><td>".$p->t('benotungstool/positiv')." </td><td><input type='checkbox' name='positiv'></td></tr>
	<!--<tr><td>".$p->t('benotungstool/statistikFuerStudentenAnzeigen')." </td><td><input type='checkbox' name='statistik'></td></tr>-->
	<tr><td>".$p->t('benotungstool/angabeidatei')."</td><td><input type='file' name='angabedatei'></td></tr>
	<tr><td colspan=2 align='right'><input type='submit' name='abgabe_neu' value='".$p->t('benotungstool/anlegen')."'></td></tr>
	</table>
	</form>
	";
	}
}
?>
</td></tr>
</table>
</body>
</html>
