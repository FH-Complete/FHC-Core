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
/*
 * Created on 02.12.2004
 *
 */
// header fuer no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/datum.class.php');
require_once('../include/lehrstunde.class.php');
require_once('../include/lehrverband.class.php');
require_once('../include/gruppe.class.php');

$datumObj=new datum();

function checkID($needle)
{
	global $id_list;

	reset($id_list);
	foreach($id_list as $v)
		if ($v==$needle)
			return true;
	return false;
}

if (isset($stundenplan_id0))
{
	$idList=array();
	while(list($k,$v)=each($_GET))
		if (strpos($k,'stundenplan_id')!==false)
			$idList[]=$v;
}

$uid=get_uid();

if (isset($_GET['datum']))
	$datum=$_GET['datum'];
else
	$datum=date('Y-m-d',(time()));

if (isset($_GET['datum_bis']))
	$datum_bis=$_GET['datum_bis'];
else
	$datum_bis=date('Y-m-d',( jump_day($datumObj->mktime_fromdate($datum),1) ));

if (isset($_GET['stunde']))
	$stunde=$_GET['stunde'];
else
	$stunde=null;
if (isset($_GET['type']))
	$type=$_GET['type'];
else
	$type='lektor';
if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else
	$stg_kz=null;
if (isset($_GET['sem']))
	$sem=$_GET['sem'];
else
	$sem=null;
if (isset($_GET['ver']))
	$ver=$_GET['ver'];
else
	$ver=null;
if (isset($_GET['grp']))
	$grp=$_GET['grp'];
else
	$grp=null;
if (isset($_GET['gruppe']))
	$einheit=$_GET['gruppe'];
else
	$einheit=null;
if (isset($_GET['pers_uid']))
	$pers_uid=$_GET['pers_uid'];
else
	$pers_uid=$uid;
if (isset($_GET['ort_kurzbz']))
	$ort_kurzbz=$_GET['ort_kurzbz'];
else
	$ort_kurzbz=null;

if (isset($idList))
	$type='idList';
else
	$idList=null;

$error_msg='';
$error_msg.=loadVariables($uid);
$alle_unr_mitladen=true;
$lehrstunden=new lehrstunde();
$anz=$lehrstunden->load_lehrstunden($type,$datum,$datum_bis,$pers_uid,$ort_kurzbz,$stg_kz,$sem,$ver,$grp,$einheit,$db_stpl_table,$idList,null, null, $alle_unr_mitladen);
if ($anz<0)
{
	$errormsg=$lehrstunden->errormsg;
	echo "Fehler: ".$errormsg;
	exit();
}

$rdf_url='http://www.technikum-wien.at/lehrstunde';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHRSTUNDE="<?php echo $rdf_url; ?>/rdf#"
	>
	<RDF:Seq about="<?php echo $rdf_url ?>/alle">

<?php
$db = new basis_db();
function getAnzahl($studiengang_kz, $semester, $verband, $gruppe, $gruppe_kurzbz, $studiensemester_kurzbz, $lehreinheit_id, $mitschwund)
{
	global $db;

	if($semester=='')
		return 0;
	if($gruppe_kurzbz=='')
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_studentlehrverband
				WHERE studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
				AND studiengang_kz=".$db->db_add_param($studiengang_kz)." AND
				semester=".$db->db_add_param($semester);
		if(trim($verband)!='')
			$qry.=" AND trim(verband)=trim(".$db->db_add_param($verband).")";
		if(trim($gruppe)!='')
			$qry.=" AND trim(gruppe)=trim(".$db->db_add_param($gruppe).")";

		if($mitschwund)
		{
			// Studierende mit Anrechnungen herausnehmen
			$qry.=" AND NOT EXISTS(
						SELECT 1 FROM lehre.tbl_zeugnisnote
						WHERE student_uid=tbl_studentlehrverband.student_uid
						AND lehrveranstaltung_id=(SELECT lehrveranstaltung_id FROM lehre.tbl_lehreinheit
										WHERE lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER).")
						AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
						AND note=6)";
			// Abbrecher und Unterbrecher herausnehmen
			$qry.=" AND get_rolle_prestudent((SELECT prestudent_id FROM public.tbl_student
						WHERE student_uid=tbl_studentlehrverband.student_uid),null) NOT IN('Abbrecher','Unterbrecher') ";
		}
	}
	else
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_benutzergruppe
				WHERE studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
				AND gruppe_kurzbz=".$db->db_add_param($gruppe_kurzbz);

		if($mitschwund)
		{
			// Studierende mit Anrechnungen herausnehmen
			$qry.=" AND NOT EXISTS(
						SELECT 1 FROM lehre.tbl_zeugnisnote
						WHERE student_uid=tbl_benutzergruppe.uid
						AND lehrveranstaltung_id=(SELECT lehrveranstaltung_id FROM lehre.tbl_lehreinheit
										WHERE lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER).")
						AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
						AND note=6)";
			// Abbrecher und Unterbrecher herausnehmen
			$qry.=" AND get_rolle_prestudent((SELECT prestudent_id FROM public.tbl_student
						WHERE student_uid=tbl_benutzergruppe.uid),null) NOT IN('Abbrecher','Unterbrecher') ";
		}
	}

	if($res_anz = $db->db_query($qry))
	{
		if($row_anz = $db->db_fetch_object($res_anz))
		{
			return $row_anz->anzahl;
		}
	}
}

if (is_array($lehrstunden->lehrstunden))
{
	foreach ($lehrstunden->lehrstunden as $ls)
	{
		if(is_null($stunde) || $ls->stunde==$stunde)
		{
			//Anzahl der Studenten in der Gruppe ermitteln
			$stsem = getStudiensemesterFromDatum($ls->datum);
			$anzahl = getAnzahl($ls->studiengang_kz, $ls->sem, $ls->ver, $ls->grp, $ls->gruppe_kurzbz, $stsem, $ls->lehreinheit_id, false);
			$anzahl_mitschwund = getAnzahl($ls->studiengang_kz, $ls->sem, $ls->ver, $ls->grp, $ls->gruppe_kurzbz, $stsem, $ls->lehreinheit_id, true);
			$gruppenbezeichnung = '';
			$gruppenbeschreibung = '';

			if($ls->gruppe_kurzbz!='')
			{
				$obj = new gruppe();
				if(!$obj->load($ls->gruppe_kurzbz))
					die($obj->errormsg);
				$gruppenbezeichnung = $obj->bezeichnung;
				$gruppenbeschreibung = $obj->beschreibung;
			}
			else
			{
				$obj = new lehrverband();
				if($obj->load($ls->studiengang_kz, $ls->sem, $ls->ver, $ls->grp))
				{
					$gruppenbezeichnung = $obj->bezeichnung;
					$gruppenbeschreibung = '';
				}
			}
			?>
  			<RDF:li>
  	    	<RDF:Description  id="<?php echo $ls->stundenplan_id; ?>"  about="<?php echo $rdf_url.'/'. $ls->stundenplan_id; ?>" >
  		      	<LEHRSTUNDE:id><![CDATA[<?php echo $ls->stundenplan_id  ?>]]></LEHRSTUNDE:id>
  		      	<LEHRSTUNDE:reservierung><![CDATA[<?php echo ($ls->reservierung?'true':'false'); ?>]]></LEHRSTUNDE:reservierung>
				<LEHRSTUNDE:lehreinheit_id><![CDATA[<?php echo $ls->lehreinheit_id  ?>]]></LEHRSTUNDE:lehreinheit_id>
				<LEHRSTUNDE:datum><![CDATA[<?php echo $ls->datum  ?>]]></LEHRSTUNDE:datum>
				<LEHRSTUNDE:stunde><![CDATA[<?php echo $ls->stunde  ?>]]></LEHRSTUNDE:stunde>
  		  		<LEHRSTUNDE:unr><![CDATA[<?php echo $ls->unr  ?>]]></LEHRSTUNDE:unr>
				<LEHRSTUNDE:ort_kurzbz><![CDATA[<?php echo $ls->ort_kurzbz  ?>]]></LEHRSTUNDE:ort_kurzbz>
				<LEHRSTUNDE:lehrfach><![CDATA[<?php echo $ls->lehrfach  ?>]]></LEHRSTUNDE:lehrfach>
				<LEHRSTUNDE:lehrfach_bez><![CDATA[<?php echo $ls->lehrfach_bez  ?>]]></LEHRSTUNDE:lehrfach_bez>
				<LEHRSTUNDE:lehrform><![CDATA[<?php echo $ls->lehrform  ?>]]></LEHRSTUNDE:lehrform>
				<LEHRSTUNDE:lektor><![CDATA[<?php echo $ls->lektor_kurzbz  ?>]]></LEHRSTUNDE:lektor>
				<LEHRSTUNDE:sem><![CDATA[<?php echo $ls->sem  ?>]]></LEHRSTUNDE:sem>
				<LEHRSTUNDE:ver><![CDATA[<?php echo $ls->ver  ?>]]></LEHRSTUNDE:ver>
				<LEHRSTUNDE:grp><![CDATA[<?php echo $ls->grp  ?>]]></LEHRSTUNDE:grp>
				<LEHRSTUNDE:gruppe><![CDATA[<?php echo $ls->gruppe_kurzbz  ?>]]></LEHRSTUNDE:gruppe>
				<LEHRSTUNDE:lehrform><![CDATA[<?php echo $ls->lehrform  ?>]]></LEHRSTUNDE:lehrform>
				<LEHRSTUNDE:studiengang><![CDATA[<?php echo $ls->studiengang  ?>]]></LEHRSTUNDE:studiengang>
				<LEHRSTUNDE:farbe><![CDATA[<?php echo $ls->farbe  ?>]]></LEHRSTUNDE:farbe>
				<LEHRSTUNDE:anmerkung><![CDATA[<?php echo $ls->anmerkung;  ?>]]></LEHRSTUNDE:anmerkung>
				<LEHRSTUNDE:anmerkung_lehreinheit><![CDATA[<?php echo $ls->anmerkung_lehreinheit;  ?>]]></LEHRSTUNDE:anmerkung_lehreinheit>
				<LEHRSTUNDE:titel><![CDATA[<?php echo $ls->titel;  ?>]]></LEHRSTUNDE:titel>
				<LEHRSTUNDE:anzahlstudenten><![CDATA[<?php echo $anzahl_mitschwund.' ('.$anzahl.')';  ?>]]></LEHRSTUNDE:anzahlstudenten>
				<LEHRSTUNDE:gruppe_bezeichnung><![CDATA[<?php echo $gruppenbezeichnung;  ?>]]></LEHRSTUNDE:gruppe_bezeichnung>
				<LEHRSTUNDE:gruppe_beschreibung><![CDATA[<?php echo $gruppenbeschreibung;  ?>]]></LEHRSTUNDE:gruppe_beschreibung>
  	    	</RDF:Description>
  			</RDF:li>
			<?php
		}
	}
}
?>

  </RDF:Seq>
</RDF:RDF>
