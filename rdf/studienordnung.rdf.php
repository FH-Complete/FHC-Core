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


header("Content-type: application/xhtml+xml");

if(isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{
    if(isset($_REQUEST['stg_kz']))
    {
		$orgform_array=array('VBB','BB','DL','VZ');
		
        // Studiengangsdaten laden
        $stg_kz = $_REQUEST['stg_kz']; 
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
		
		switch($objStg->orgform_kurzbz)
		{
			case 'VBB':
				$orgform_kurzbz_lang='Vollzeit und Berufsbegleitend';
				break;
			case 'VZ':
				$orgform_kurzbz_lang='Vollzeit';
				break;
			case 'BB':
				$orgform_kurzbz_lang='Berufsbegleitend';
				break;
			case 'DL':
				$orgform_kurzbz_lang='Distance Learning';
				break;
			default:
				$orgform_kurzbz_lang=$objStg->orgform_kurzbz;
		}
		
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        echo '<studiengang>';
        echo '  <studiengang_kz><![CDATA['.$objStg->studiengang_kz.']]></studiengang_kz>';
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
/*        echo '  <studienplan>';
        echo '      <regelstudiendauer><![CDATA['.$objStg->max_semester.']]></regelstudiendauer>';
        echo '      <bezeichnung><![CDATA['.$objStg->bezeichnung.']]></bezeichnung>';
        echo '      <bezeichnung_englisch><![CDATA['.$objStg->english.']]></bezeichnung_englisch>';
        echo '      <kurzbzlang><![CDATA['.$objStg->kurzbzlang.']]></kurzbzlang>'; */
		
		$objLV = new lehrveranstaltung();
		if(!$objLV->load_lva($objStg->studiengang_kz, null,null,true,true,'orgform_kurzbz,semester,sort,bezeichnung'))
			die('Fehler beim laden der Lehrveranstaltungen');
		
//		$count_arr=$objLV->count_lva($objStg->studiengang_kz,'orgform_kurzbz');
		foreach ($orgform_array as $orgform_kurzbz)
		{
			$count=$objLV->count_lva_orgform($objStg->studiengang_kz,$orgform_kurzbz);

			if($orgform_kurzbz==$objStg->orgform_kurzbz)
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
			
			switch($orgform_kurzbz)
			{
				case 'VBB':
					$orgform_kurzbz_lang='Vollzeit und Berufsbegleitend';
					break;
				case 'VZ':
					$orgform_kurzbz_lang='Vollzeit';
					break;
				case 'BB':
					$orgform_kurzbz_lang='Berufsbegleitend';
					break;
				case 'DL':
					$orgform_kurzbz_lang='Distance Learning';
					break;
				default:
					$orgform_kurzbz_lang=$orgform_kurzbz;
			}

			echo '  	<orgform>';
			echo '      	<orgform_kurzbz><![CDATA['.$orgform_kurzbz.']]></orgform_kurzbz>';
			echo '      	<orgform_kurzbz_lang><![CDATA['.$orgform_kurzbz_lang.']]></orgform_kurzbz_lang>';
			echo '			<regelstudiendauer><![CDATA['.$objStg->max_semester.']]></regelstudiendauer>';
			echo '          <studienplaetze><![CDATA['.$objStg->studienplaetze.']]></studienplaetze>';

			// ************ Lehrveranstaltungen ***************
			for($i=1;$i<=$objStg->max_semester;$i++)
			{
				echo '		<semester>';
				echo '			<semester_nr><![CDATA['.$i.']]></semester_nr>';
				foreach($objLV->lehrveranstaltungen as $lv)
				{
					if(!($lv->semester==$i && ($lv->orgform_kurzbz==$orgform_kurzbz || ($orgform_match && is_null($lv->orgform_kurzbz)))))
						continue;

					$lv_alvs = new lehrveranstaltung(); 
					if(!$alvs = $lv_alvs->getALVS($lv->lehrveranstaltung_id, $i))
						$alvs = ''; 

					echo '  		<lehrveranstaltung>';
					echo '              <lv_semester><![CDATA['.$lv->semester.']]></lv_semester>';
					echo '              <lv_bezeichnung><![CDATA['.$lv->bezeichnung.']]></lv_bezeichnung>';
					echo '              <lv_kurzbz><![CDATA['.$lv->kurzbz.']]></lv_kurzbz>';
					echo '              <lv_lehrform_kurzbz><![CDATA['.$lv->lehrform_kurzbz.']]></lv_lehrform_kurzbz>';
					echo '              <lv_gruppen><![CDATA[]]></lv_gruppen>';
					echo '              <lv_ects><![CDATA['.$lv->ects.']]></lv_ects>';
					echo '              <lv_semesterstunden><![CDATA['.$lv->semesterstunden.']]></lv_semesterstunden>';
					echo '              <lv_alvs><![CDATA['.$alvs.']]></lv_alvs>';
					echo '              <lv_anmerkung><![CDATA['.clearHtmlTags($lv->anmerkung).']]></lv_anmerkung>';

					// ***************** LV-Info ***************
					if ($objLVInfo->exists($lv->lehrveranstaltung_id,'German'))
					{
						if(!$objLVInfo->load($lv->lehrveranstaltung_id,'German'))
							die('Fehler beim laden der Lehrveranstaltungen');
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
					}
					echo '      </lehrveranstaltung>';
				}
				echo '</semester>';
			}
			echo '    </orgform>';
		}
//        echo '  </studienplan>';
        echo '</studiengang>';
    }
    else
        die('Parameter stg_kz is missing'); 
    
}
else
    die('Use Parameter xmlformat = xml')

?>
