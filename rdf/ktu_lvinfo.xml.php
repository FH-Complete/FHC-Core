<?php
/* Copyright (C) 2014 FH fhcomplete.org
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
 * Authors: Stefan Puraner <stefan.puraner@technikum-wien.at>
 */
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/studiengang.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/lvinfo.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/lehrfach.class.php');

$lvid = (isset($_REQUEST['lvid'])?$_REQUEST['lvid']:NULL);
$studiensemester = new studiensemester();
$std_sem = (isset($_REQUEST['semester'])?$_REQUEST['semester']:$studiensemester->getaktorNext());
$sprache = (isset($_REQUEST['sprache'])?$_REQUEST['sprache']:"German");
$studiengang_kz = (isset($_REQUEST['stg_kz'])?$_REQUEST['stg_kz']:NULL);

if(is_null($studiengang_kz))
{
    echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\n"; 
    die("<error>Studiengangskennzahl fehlt</error>\n");
}
else
{
    $studiengang = new studiengang($studiengang_kz);
}

switch ($sprache)
{
    case "German":
	break;
    case "English":
	break;
    default:
	$sprache = "German";
}

$lehrveranstaltung = new lehrveranstaltung();
$result = null;

function sortArray($a, $b)
{
    if($a->lv_bezeichnung == $b->lv_bezeichnung)
    {
	return 0;
    }
    return ($a->lv_bezeichnung < $b->lv_bezeichnung) ? -1 : 1;
}
$lv_arr = array();
if($lvid == null)
{
    // TODO Abfrage zum einschränker der Daten
    if($studiengang->studiengang_kz != 0 && $studiengang->studiengang_kz <999 && $studiengang->studiengang_kz > 0)
    {
	$lehrveranstaltung->load_lva($studiengang->studiengang_kz);
	$i=0;
	foreach($lehrveranstaltung->lehrveranstaltungen as $lv_key => $lv)
	{
	    $lehreinheit = new lehreinheit();
	    $lehreinheit->load_lehreinheiten($lv->lehrveranstaltung_id, $std_sem);
	    if(!empty($lehreinheit->lehreinheiten))
	    {
		$lv_titel = new lehrveranstaltung($lehreinheit->lehreinheiten[0]->lehrfach_id);
		$lehrveranstaltung->lehrveranstaltungen[$lv_key]->lehrfach_bez = $lv_titel->bezeichnung;
	    }
	    $lvinfo = new lvinfo();
	    $i++;
	    $lvinfo->load($lv->lehrveranstaltung_id, $sprache);
	    $lehrveranstaltung->lehrveranstaltungen[$lv_key]->lvinfo = $lvinfo;
//		if($i==2)
//		    break;
	}
	$studiengang->lehrveranstaltungen = $lehrveranstaltung->lehrveranstaltungen;
    }
    else
    {
	unset($studiengang->lehrveranstaltungen[$key]);
    }
}
else 
{
    //Ausgabe einer bestimmten Lehrveranstaltung
    $lvid_arr = explode(";",$lvid_arr);
}

echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\n"; 
echo "<studiengang>";
$i = 0;
echo "<studiengang_kz>".$studiengang->studiengang_kz."</studiengang_kz>";
echo "<studiengang_bezeichnung>".$studiengang->bezeichnung."</studiengang_bezeichnung>";
echo "<lehrveranstaltungen>";
foreach($studiengang->lehrveranstaltungen as $lv)
{
    echo "<lv>";
    echo "<lvid>".$lv->lehrveranstaltung_id."</lvid>";
    echo "<lv_bezeichnung>".str_replace("&","&amp;",$lv->bezeichnung)."</lv_bezeichnung>";
    echo "<lv_lehrfach_bez>".str_replace("&","&amp;",$lv->lehrfach_bez)."</lv_lehrfach_bez>";
    echo "<lvInfo>";
    if($lv->lvinfo->errormsg === NULL)
    {
	echo "<lvInfo_titel><![CDATA[".$lv->lvinfo->titel."]]></lvInfo_titel>";
//	echo "<lvInfo_kurzbz><![CDATA[".$lv->lvinfo->kurzbeschreibung."]]></lvInfo_kurzbz>";
	echo "<lvInfo_lehrziele><![CDATA[".$lv->lvinfo->lehrziele."]]></lvInfo_lehrziele>";
	echo "<lvInfo_lehrinhalte><![CDATA[".$lv->lvinfo->lehrinhalte."]]></lvInfo_lehrinhalte>";
	echo "<lvInfo_methodik><![CDATA[".$lv->lvinfo->methodik."]]></lvInfo_methodik>";
	echo "<lvInfo_sprache><![CDATA[".$lv->lvinfo->sprache."]]></lvInfo_sprache>";
	echo "<lvInfo_voraussetzungen><![CDATA[".$lv->lvinfo->voraussetzungen."]]></lvInfo_voraussetzungen>";
	echo "<lvInfo_unterlagen><![CDATA[".$lv->lvinfo->voraussetzungen."]]></lvInfo_unterlagen>";
	echo "<lvInfo_pruefung><![CDATA[".$lv->lvinfo->pruefungsordnung."]]></lvInfo_pruefung>";
    }
    else
    {
	echo "<lvInfoErrormsg>".$lv->lvinfo->errormsg."</lvInfoErrormsg>";
    }
    echo "</lvInfo>";
    echo "</lv>";
}
echo "</lehrveranstaltungen>";

//foreach($result as $stg)
//{
//    echo "<STG>";
//    echo "<STG_bezeichnung>".$stg->bezeichnung."</STG_bezeichnung>";
//    echo "<lehrveranstaltungen>";
//    foreach($stg->lehrveranstaltungen as $lv)
//    {
//	if($lv->lvinfo->errormsg == "")
//	{
//	    echo "<lv>";
//	    echo "<LV_id>".$lv->lehrveranstaltung_id."</LV_id>";
//	    echo "<LV_bez>".str_replace("&","&amp;",$lv->lvinfo->lv_bezeichnung)."</LV_bez>";
//	    echo "<LVINFO_titel>".str_replace("&","&amp;", htmlentities($lv->lvinfo->titel))."</LVINFO_titel>";
//	    echo "<LVINFO_kurzbz>".str_replace("&","&amp;", htmlentities($lv->lvinfo->kurzbeschreibung))."</LVINFO_kurzbz>";
//	    echo "<LVINFO_lehrziele>".str_replace("&","&amp;", htmlentities($lv->lvinfo->lehrziele))."</LVINFO_lehrziele>";
//	    echo "<LVINFO_lehrinhalte>".str_replace("&","&amp;", htmlentities($lv->lvinfo->lehrinhalte))."</LVINFO_lehrinhalte>";
//	    echo "</lv>";
//	}
//	
//	$i++;
////	if($i==2)
////	    break;
//    }
//    echo "</lehrveranstaltungen>";
//    echo "</STG>";
//    
//}
echo "</studiengang>";


