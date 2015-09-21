<?php

/* Copyright (C) 2012 fhcomplete.org
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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>
 */

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/studiengang.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/lvinfo.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/lehreinheitgruppe.class.php');
require_once('../include/studienordnung.class.php');
require_once('../include/studienplan.class.php');
require_once('../include/organisationsform.class.php');
require_once('../include/lehrform.class.php');
require_once('../include/sprache.class.php');

header("Content-type: application/xhtml+xml");

if(isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{
	if(isset($_GET['studienordnung_id']))
	{
		$studienordnung_id = $_REQUEST['studienordnung_id'];

		$studienordnung_obj = new studienordnung();
		$studienordnung_obj->loadStudienordnung($studienordnung_id);

		$stg_kz = $studienordnung_obj->studiengang_kz;
		$gueltigvon_stsem = $studienordnung_obj->gueltigvon;

        $orgform_obj = new organisationsform();
        $orgform_obj->getAll();

        foreach($orgform_obj->result as $row_orgform)
        {
        	$orgform[$row_orgform->orgform_kurzbz]=$row_orgform->bezeichnung;
        }
    	//$datum = new datum();

        $objStg = new studiengang();

        if(!$objStg->load($stg_kz))
            die('Fehler beim laden des Studiengangs');
        $objLVInfo = new lvinfo();

		switch($objStg->typ)
		{
			case 'b':
				$stg_art = 'Bachelor';
				$titel_kurzbz = 'BSc';
				break;
			case 'm':
				$stg_art = 'Master';
				$titel_kurzbz ='MSc';
				break;
			case 'd':
				$stg_art = 'Diplom';
				break;
			default:
				$stg_art ='';
				$titel_kurzbz = '';
		}

		$stgleiter = $objStg->getLeitung($objStg->studiengang_kz);
		$stgl='';
		foreach ($stgleiter as $stgleiter_uid)
		{
			$stgl_ma = new mitarbeiter($stgleiter_uid);
			$stgl .= trim($stgl_ma->titelpre.' '.$stgl_ma->vorname.' '.$stgl_ma->nachname.' '.$stgl_ma->titelpost);
		}

		$orgform_kurzbz_lang = $orgform[$objStg->orgform_kurzbz];


		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        echo '<studienordnung>';
        echo '  <studiengang_kz><![CDATA['. sprintf("%'.04d",$objStg->studiengang_kz).']]></studiengang_kz>';
        echo '  <studiengang_kurzbz><![CDATA['.$objStg->kurzbz.']]></studiengang_kurzbz>';
        echo '  <studiengang_typ><![CDATA['.$objStg->typ.']]></studiengang_typ>';
        echo '  <studiengang_art><![CDATA['.$stg_art.']]></studiengang_art>';
		echo '  <studiengang_kurzbzlang><![CDATA['.$objStg->kurzbzlang.']]></studiengang_kurzbzlang>';
		echo '  <studiengang_bezeichnung><![CDATA['.$objStg->bezeichnung.']]></studiengang_bezeichnung>';
        echo '  <bezeichnung_englisch><![CDATA['.$objStg->english.']]></bezeichnung_englisch>';
        echo '  <titel_kurzbz><![CDATA['.$titel_kurzbz.']]></titel_kurzbz>';
        echo '  <studiengangsleitung><![CDATA['.$stgl.']]></studiengangsleitung>';
        echo '	<orgform_kurzbz><![CDATA['.$objStg->orgform_kurzbz.']]></orgform_kurzbz>';
        echo '	<orgform_kurzbz_lang><![CDATA['.$orgform_kurzbz_lang.']]></orgform_kurzbz_lang>';
        echo '	<studienordnung_gueltigvon><![CDATA['.$gueltigvon_stsem.']]></studienordnung_gueltigvon>';

        $studienplan = new studienplan();
    	$studienplan->loadStudienplanSTO($studienordnung_id);

		foreach($studienplan->result as $row_studienplan)
		{
	        $summe_ects_orgform = 0;
	        $summe_sws_orgform = 0;
			echo '  <studienplan>';
	        echo '      <regelstudiendauer><![CDATA['.$row_studienplan->regelstudiendauer.']]></regelstudiendauer>';
	        echo '      <bezeichnung><![CDATA['.$row_studienplan->bezeichnung.']]></bezeichnung>';

//			$count=$objLV->count_lva_orgform($objStg->studiengang_kz,$orgform_kurzbz);

/*			if($orgform_kurzbz==$objStg->orgform_kurzbz)
			{
				$orgform_match=true;
				$count+=$objLV->count_lva_orgform($objStg->studiengang_kz,null);
//				echo '<orgform>'.$orgform_kurzbz.' ('.$count.')</orgform>';
			}
			else
			{
				$orgform_match=false;
			}
			if($count<1)
			{
				continue;
			}
			*/
			$orgform_kurzbz_lang = $orgform[$row_studienplan->orgform_kurzbz];


			$ects_gesamt = ($row_studienplan->regelstudiendauer)*30;

//			echo '  	<orgform>';
			echo '      	<orgform_kurzbz><![CDATA['.$row_studienplan->orgform_kurzbz.']]></orgform_kurzbz>';
			echo '      	<orgform_kurzbz_lang><![CDATA['.$orgform_kurzbz_lang.']]></orgform_kurzbz_lang>';
			echo '			<regelstudiendauer><![CDATA['.$row_studienplan->regelstudiendauer.']]></regelstudiendauer>';
			echo '			<ects_gesamt><![CDATA['.$ects_gesamt.']]></ects_gesamt>';
			//echo '          <studienplaetze><![CDATA['.$objStg->studienplaetze.']]></studienplaetze>';

			// ************ Lehrveranstaltungen ***************

			//Basis von Ösi
			/*
			$lv = new lehrveranstaltung();
			$lv->loadLehrveranstaltungStudienplan($studienplan_id);
			getLVFromStudienplanByLehrtyp($studienplan_id, null, $i)
			$tree = $lv->getLehrveranstaltungTree();


			foreach($tree as $module)
			{
				echo $module->bezeichnung;

				if(isset($module->childs))
				{
					foreach($module->childs as $lv_1)
					{
						echo $lv_1->bezeichnung;

						if(isset($lv_1->childs))
						{
							foreach($lv_1->childs as $lv_2)
							{
								echo $lv_2->bezeichnung;
					}
				}
			}

			printlv($tree);

			function printlv($tree)
			{
				foreach($tree as $lv)
				{
					echo $lv->bezeichnung;

					if(isset($lv->childs))
					{
						printlv($lv->childs);
					}
				}
			}*/



			for($i=1;$i<=$objStg->max_semester;$i++)
			{
				$summe_ects_semester = 0;
				$summe_sws_semester = 0;
				echo '		<semester>';
				echo '			<semester_nr><![CDATA['.$i.']]></semester_nr>';

				$lv = new lehrveranstaltung();
				$lv->loadLehrveranstaltungStudienplan($row_studienplan->studienplan_id, $i);
				$tree = $lv->getLehrveranstaltungTree();

				//var_dump($tree);
				printLehrveranstaltungTree($tree);

				//if ($lv->lehrtyp_kurzbz!='modul')
				//	$summe += $lv->ects;

				echo '	<lv_summe_ects_semester><![CDATA['.$summe_ects_semester.']]></lv_summe_ects_semester>';
				echo '	<lv_summe_sws_semester><![CDATA['.round($summe_sws_semester,2).']]></lv_summe_sws_semester>';

				$summe_ects_orgform += $summe_ects_semester;
				$summe_sws_orgform += $summe_sws_semester;
				echo '</semester>';
			}
			echo '			<lv_summe_ects_orgform><![CDATA['.$summe_ects_orgform.']]></lv_summe_ects_orgform>';
			echo '			<lv_summe_sws_orgform><![CDATA['.round($summe_sws_orgform,2).']]></lv_summe_sws_orgform>';
			echo '    </studienplan>';
		}
        //echo '  </studienplan>';
        echo '</studienordnung>';
    }
    else
        die('Parameter studienordnung_id is missing');

}
else
    die('Use Parameter xmlformat = xml');

function cmp($a, $b)
{
    return strcmp($a->bezeichnung, $b->bezeichnung);
}

function printLehrveranstaltungTree($tree)
{
	global $summe_ects_semester, $summe_sws_semester;
	usort($tree, "cmp");
	foreach($tree as $lv)
	{
		$db = new basis_db();
		$lv_alvs = new lehrveranstaltung();
		if(!$alvs = $lv_alvs->getALVS($lv->lehrveranstaltung_id, $lv->semester))
			$alvs = '';
		//Semesterwochen zum berechnen der SWS ermitteln
		$qry = '	SELECT
						wochen
					FROM
						public.tbl_semesterwochen
					WHERE
						studiengang_kz='.$lv->studiengang_kz.'
					AND
						semester='.$lv->semester;
		if($wochen_stg = $db->db_query($qry))
		{
			if($db->db_num_rows($wochen_stg)==1)
			{
				$row_wochen = $db->db_fetch_object($wochen_stg);
				$wochen = $row_wochen->wochen;
			}
			else
				$wochen = '15';
		}
		if ($lv->semesterstunden!='')
			$sws = ($lv->semesterstunden / $wochen);
		else
			$sws = 0;

		//Bezeichnung der Lehrform
		$lehrform_kurzbz = new lehrform();
		$lehrform_kurzbz->load($lv->lehrform_kurzbz);

		//Klasse "sprache" instanzieren, um anschließend die Sprache(e.g. "German") in der richtigen Sprache zu bekommen("Deutsch")
		$sp = new sprache();

		echo '  		<lehrveranstaltung>';
		echo '              <lv_semester><![CDATA['.$lv->semester.']]></lv_semester>';
		echo '              <lv_lehrtyp_kurzbz><![CDATA['.$lv->lehrtyp_kurzbz.']]></lv_lehrtyp_kurzbz>';
		echo '              <lv_bezeichnung><![CDATA['.$lv->bezeichnung.']]></lv_bezeichnung>';
		echo '              <lv_bezeichnung_en><![CDATA['.$lv->bezeichnung_english.']]></lv_bezeichnung_en>';
		echo '              <lv_kurzbz><![CDATA['.$lv->kurzbz.']]></lv_kurzbz>';
		echo '              <lv_lehrform_kurzbz><![CDATA['.$lv->lehrform_kurzbz.']]></lv_lehrform_kurzbz>';
		echo '              <lv_lehrform_langbz><![CDATA['.$lehrform_kurzbz->bezeichnung.']]></lv_lehrform_langbz>';
		echo '              <lv_gruppen><![CDATA[]]></lv_gruppen>';
		echo '              <lv_ects><![CDATA['.$lv->ects.']]></lv_ects>';
		echo '              <lv_semesterstunden><![CDATA['.$lv->semesterstunden.']]></lv_semesterstunden>';
		echo '              <lv_sws><![CDATA['.round($sws,2).']]></lv_sws>';
		echo '              <lv_alvs><![CDATA['.$alvs.']]></lv_alvs>';
		echo '              <lv_anmerkung><![CDATA['.clearHtmlTags($lv->anmerkung).']]></lv_anmerkung>';
		echo '							<lv_sprache><![CDATA['.$sp->getBezeichnung($lv->sprache, constant("DEFAULT_LANGUAGE")).']]></lv_sprache>';


		$objLVInfo = new lvinfo();
		// ***************** LV-Info ***************
		if ($objLVInfo->exists($lv->lehrveranstaltung_id,'German'))
		{
			if(!$objLVInfo->load($lv->lehrveranstaltung_id,'German'))
				die('Fehler beim laden der deutschen LV-Informationen');
			//var_dump($objLVInfo);
			echo '              <lvinfo_sprache><![CDATA['.clearHtmlTags($objLVInfo->sprache).']]></lvinfo_sprache>';
			echo '              <lvinfo_titel><![CDATA['.clearHtmlTags($objLVInfo->titel).']]></lvinfo_titel>';
			echo '              <lvinfo_lehrziele><![CDATA['.clearHtmlTags($objLVInfo->lehrziele).']]></lvinfo_lehrziele>';
			echo '              <lvinfo_methodik><![CDATA['.clearHtmlTags($objLVInfo->methodik).']]></lvinfo_methodik>';
			echo '              <lvinfo_lehrinhalte><![CDATA['.clearHtmlTags($objLVInfo->lehrinhalte).']]></lvinfo_lehrinhalte>';
			echo '              <lvinfo_voraussetzungen><![CDATA['.clearHtmlTags($objLVInfo->voraussetzungen).']]></lvinfo_voraussetzungen>';
			echo '              <lvinfo_unterlagen><![CDATA['.clearHtmlTags($objLVInfo->unterlagen).']]></lvinfo_unterlagen>';
			echo '              <lvinfo_pruefungsordnung><![CDATA['.clearHtmlTags($objLVInfo->pruefungsordnung).']]></lvinfo_pruefungsordnung>';
			echo '              <lvinfo_kurzbeschreibung><![CDATA['.clearHtmlTags($objLVInfo->kurzbeschreibung).']]></lvinfo_kurzbeschreibung>';
			echo '              <lvinfo_anmerkungen><![CDATA['.clearHtmlTags($objLVInfo->anmerkungen).']]></lvinfo_anmerkungen>';
			echo '              <lvinfo_anwesenheit><![CDATA['.clearHtmlTags($objLVInfo->anwesenheit).']]></lvinfo_anwesenheit>';
		}
		if ($objLVInfo->exists($lv->lehrveranstaltung_id,'English'))
		{
			if(!$objLVInfo->load($lv->lehrveranstaltung_id,'English'))
				die('Fehler beim laden der englischen LV-Informationen');
			//var_dump($objLVInfo);
			echo '              <lvinfo_sprache><![CDATA['.clearHtmlTags($objLVInfo->sprache).']]></lvinfo_sprache>';
			echo '              <lvinfo_titel_en><![CDATA['.clearHtmlTags($objLVInfo->titel).']]></lvinfo_titel_en>';
			echo '              <lvinfo_lehrziele_en><![CDATA['.clearHtmlTags($objLVInfo->lehrziele).']]></lvinfo_lehrziele_en>';
			echo '              <lvinfo_methodik_en><![CDATA['.clearHtmlTags($objLVInfo->methodik).']]></lvinfo_methodik_en>';
			echo '              <lvinfo_lehrinhalte_en><![CDATA['.clearHtmlTags($objLVInfo->lehrinhalte).']]></lvinfo_lehrinhalte_en>';
			echo '              <lvinfo_voraussetzungen_en><![CDATA['.clearHtmlTags($objLVInfo->voraussetzungen).']]></lvinfo_voraussetzungen_en>';
			echo '              <lvinfo_unterlagen_en><![CDATA['.clearHtmlTags($objLVInfo->unterlagen).']]></lvinfo_unterlagen_en>';
			echo '              <lvinfo_pruefungsordnung_en><![CDATA['.clearHtmlTags($objLVInfo->pruefungsordnung).']]></lvinfo_pruefungsordnung_en>';
			echo '              <lvinfo_kurzbeschreibung_en><![CDATA['.clearHtmlTags($objLVInfo->kurzbeschreibung).']]></lvinfo_kurzbeschreibung_en>';
			echo '              <lvinfo_anmerkungen_en><![CDATA['.clearHtmlTags($objLVInfo->anmerkungen).']]></lvinfo_anmerkungen_en>';
			echo '              <lvinfo_anwesenheit_en><![CDATA['.clearHtmlTags($objLVInfo->anwesenheit).']]></lvinfo_anwesenheit_en>';
		}
		if ($lv->lehrtyp_kurzbz!='modul')
		{
			$summe_ects_semester += $lv->ects;
			$summe_sws_semester += $sws;
		}

		// Darunterliegende LVs/Module
		if(isset($lv->childs) && count($lv->childs)>0)
		{
			echo '<lehrveranstaltungen>';
			printLehrveranstaltungTree($lv->childs);
			echo '</lehrveranstaltungen>';
		}
		echo '      </lehrveranstaltung>';
	}
}
?>
