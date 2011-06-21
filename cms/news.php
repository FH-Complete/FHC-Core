<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
/**
 * Laedt die News und zeigt diese an
 * 
 * Wenn kein Parameter uebergeben wird, werden nur die allgemeinen News angezeigt
 * Wenn ein Studiengang uebergeben wird, werden rechts neben den News Studiengangsdetails angezeigt
 * 
 * Parameter:
 * stg_kz   Studiengangskennzahl 
 * semester Semester
 * edit     Edit Buttons anzeigen
 * 
 */
require_once('../config/cis.config.inc.php');
require_once('../include/content.class.php');
require_once('../include/template.class.php');
require_once('../include/functions.inc.php');
require_once('../include/news.class.php');
require_once('../include/kontakt.class.php');
require_once('../include/benutzerfunktion.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/datum.class.php');
require_once('../include/phrasen.class.php');

$sprache = getSprache();

$datum_obj = new datum();
//XML Content laden
$content = new content();
$db = new basis_db();

$studiengang_kz = (isset($_GET['studiengang_kz'])?$_GET['studiengang_kz']:0);
$semester = (isset($_GET['semester'])?$_GET['semester']:null);

$editable = isset($_GET['edit']);
$news = new news();

$news->getnews(MAXNEWSALTER, $studiengang_kz, $semester, false, null, MAXNEWS);

$xml = '<?xml version="1.0" encoding="UTF-8"?><content>';

foreach($news->result as $row)
{
	$content = new content();
	$content->getContent($row->content_id, $sprache,null, true, true);
	
	//das Datum des News Eintrages ist nicht im XML enthalten, es muss extra hinzugefuegt werden
	$datum = '<datum><![CDATA['.$datum_obj->formatDatum($row->datum,'d.m.Y').']]></datum>';
	
	//Wenn der Parameter edit uebergeben wird, dann wird neben dem Datum ein Link zum Editieren des Eintrags angezeigt
	if($editable) 
		$id = '<news_id><![CDATA['.$row->news_id.']]></news_id>';
	else
		$id='';
	$xml .= mb_substr($content->content,0,mb_strlen($content->content)-7).$datum.$id.mb_substr($content->content,-7);
	//$xml .= $content->content;
}

if($studiengang_kz!=0)
	$xml.=getStgContent($studiengang_kz, $semester, $sprache);

$xml .= '</content>';

$doc = new DOMDocument();
$doc->loadXML($xml);

//XSLT Vorlage laden
$template = new template();
if(!$template->load('news'))
	die($template->errormsg);

$xsltemplate = new DOMDocument();
$xsltemplate->loadXML($template->xslt_xhtml);

//Transformation
$processor = new XSLTProcessor();
$processor->importStylesheet($xsltemplate);

echo $processor->transformToXML($doc);

/**
 * Liefert ein XML mit den Details eines Studiengangs 
 * welche dann neben den News angezeigt werden
 * 
 * @param $studiengang_kz
 * @param $semester
 * @param $sprache
 */
function getStgContent($studiengang_kz, $semester, $sprache)
{
	$p = new phrasen($sprache);
	
	$xml = '<stg_extras>';
	
	$studiengang = new studiengang();
	$studiengang->load($studiengang_kz);
	
	
	//Studiengangsleitung
	$stg_oe_obj = new studiengang();                
	$stgl = $stg_oe_obj->getLeitung($studiengang_kz);
    $xml.='<stg_header><![CDATA['.$p->t('global/studiengangsmanagement').']]></stg_header>';
	$xml.='<stg_ltg_name><![CDATA['.$p->t('global/studiengangsleitung').']]></stg_ltg_name>';
	if(count($stgl)>0)
    {
		foreach ($stgl as $uid) 
	    {
			$row_course_leader = new mitarbeiter($uid);
			$xml.='<stg_ltg>';
		    $xml.='<name><![CDATA['.$row_course_leader->titelpre.' '.$row_course_leader->vorname.' '.$row_course_leader->nachname.' '.$row_course_leader->titelpost.']]></name>';
		    
			if(isset($row_course_leader) && $row_course_leader->uid != "")
			{
				$xml.='<email><![CDATA['.$row_course_leader->uid.'@'.DOMAIN.']]></email>';
			}		
		
		  	if(isset($row_course_leader) && $row_course_leader->telefonklappe != "")
			{
				$hauptnummer='';
								
				if($row_course_leader->standort_id!='')
				{
					$kontakt = new kontakt();
					$kontakt->loadFirmaKontakttyp($row_course_leader->standort_id, 'telefon');
					$hauptnummer = $kontakt->kontakt;
				}
												
				$xml.= '<telefon><![CDATA['.$hauptnummer.' - '.$row_course_leader->telefonklappe.']]></telefon>';
			}
			$xml.='</stg_ltg>';
	    }
    }
    
	//geschaeftsf. Leitung auselesen
	$xml.='<gf_ltg_name><![CDATA['.$p->t('global/geschaeftsfuehrendeltg').']]></gf_ltg_name>';
	$benutzerfkt = new benutzerfunktion();
	$benutzerfkt->getBenutzerFunktionen('gLtg', $studiengang->oe_kurzbz);
	foreach($benutzerfkt->result as $row)
	{
		$ma = new mitarbeiter();
		$ma->load($row->uid);
		
		if($ma->uid!='')
		{
			$xml.='<gf_ltg>';
			
			$xml.='<name><![CDATA['.$ma->titelpre.' '.$ma->vorname.' '.$ma->nachname.' '.$ma->titelpost.']]></name>';
			$xml.='<email><![CDATA['.$ma->uid.'@'.DOMAIN.']]></email>';
			
			if($ma->telefonklappe != '')
			{
				if($ma->standort_id!='')
				{
					$kontakt = new kontakt();
					$kontakt->loadFirmaKontakttyp($row_course_leader->standort_id, 'telefon');
					$hauptnummer = $kontakt->kontakt;
				}
				$xml.= '<telefon><![CDATA['.$hauptnummer.' - '.$ma->telefonklappe.']]></telefon>';
			}
			
			$xml.='</gf_ltg>';
		}
	}
		
	//Studiengangsleiter Stellvertreter auselesen
	$benutzerfkt = new benutzerfunktion();
	$benutzerfkt->getBenutzerFunktionen('stvLtg', $studiengang->oe_kurzbz);
	$xml.='<stv_ltg_name><![CDATA['.$p->t('global/stellvertreter').']]></stv_ltg_name>';
	foreach($benutzerfkt->result as $row)
	{
		$ma = new mitarbeiter();
		$ma->load($row->uid);
		
		if($ma->uid!='')
		{
			$xml.='<stv_ltg>';
			
			$xml.='<name><![CDATA['.$ma->titelpre.' '.$ma->vorname.' '.$ma->nachname.' '.$ma->titelpost.']]></name>';
			$xml.='<email><![CDATA['.$ma->uid.'@'.DOMAIN.']]></email>';
			
			if($ma->telefonklappe != '')
			{
				if($ma->standort_id!='')
				{
					$kontakt = new kontakt();
					$kontakt->loadFirmaKontakttyp($row_course_leader->standort_id, 'telefon');
					$hauptnummer = $kontakt->kontakt;
				}
				$xml.= '<telefon><![CDATA['.$hauptnummer.' - '.$ma->telefonklappe.']]></telefon>';
			}
			
			$xml.='</stv_ltg>';
		}
	}	
	
	//Assistenz
	$benutzerfkt = new benutzerfunktion();
	$benutzerfkt->getBenutzerFunktionen('ass', $studiengang->oe_kurzbz);
	$xml.='<ass_name><![CDATA['.$p->t('global/sekretariat').']]></ass_name>';
	foreach($benutzerfkt->result as $row)
	{
		$ma = new mitarbeiter();
		$ma->load($row->uid);
		
		if($ma->uid!='')
		{
			$xml.='<ass>';
			
			$xml.='<name><![CDATA['.$ma->titelpre.' '.$ma->vorname.' '.$ma->nachname.' '.$ma->titelpost.']]></name>';
			$xml.='<email><![CDATA['.$ma->uid.'@'.DOMAIN.']]></email>';
			
			if($ma->telefonklappe != '')
			{
				if($ma->standort_id!='')
				{
					$kontakt = new kontakt();
					$kontakt->loadFirmaKontakttyp($row_course_leader->standort_id, 'telefon');
					$hauptnummer = $kontakt->kontakt;
				}
				$xml.= '<telefon><![CDATA['.$hauptnummer.' - '.$ma->telefonklappe.']]></telefon>';
			}
			
			$xml.='</ass>';
		}
	}	

	//Zusatzinfo (Oeffnungszeiten etc)
	$xml.='<zusatzinfo><![CDATA['.$studiengang->zusatzinfo_html.']]></zusatzinfo>';
				
	
	//Studentenvertreter
	$benutzerfkt = new benutzerfunktion();
	$benutzerfkt->getBenutzerFunktionen('stdv', $studiengang->oe_kurzbz);
	$xml.='<stdv_name><![CDATA['.$p->t('global/studentenvertreter').']]></stdv_name>';
	foreach($benutzerfkt->result as $row)
	{
		$bn = new benutzer();
		$bn->load($row->uid);
		
		if($bn->uid!='')
		{
			$xml.='<stdv>';			
			$xml.='<name><![CDATA['.$bn->titelpre.' '.$bn->vorname.' '.$bn->nachname.' '.$bn->titelpost.']]></name>';
			$xml.='<email><![CDATA['.$bn->uid.'@'.DOMAIN.']]></email>';
			$xml.='</stdv>';
		}
	}
	
	if(CIS_EXT_MENU)
	{
		$xml.='<cis_ext_menu>
			<lehrziele_name><![CDATA['.$p->t('global/lehrziele').']]></lehrziele_name>
			<download_name><![CDATA['.$p->t('global/allgemeinerdownload').']]></download_name>
			<newsgroup_name><![CDATA['.$p->t('global/newsgroups').']]></newsgroup_name>
			<kurzbz><![CDATA['.strtolower($studiengang->kuerzel).']]></kurzbz>
			<kurzbzlang><![CDATA['.strtolower($studiengang->kurzbzlang).']]></kurzbzlang>
		</cis_ext_menu>';
	}

	$xml.='</stg_extras>';
	return $xml;
}
?>