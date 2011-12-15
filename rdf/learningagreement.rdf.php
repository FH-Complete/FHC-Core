<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
require_once('../config/vilesci.config.inc.php');
require_once '../include/person.class.php';
require_once '../include/preincoming.class.php'; 
require_once '../include/firma.class.php'; 
require_once '../include/lehrveranstaltung.class.php'; 
require_once '../include/studiengang.class.php';
require_once '../include/datum.class.php';


if (isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{
	if(isset($_GET['id']))
	{
		$preincoming = new preincoming(); 
		if(!$preincoming->load($_GET['id']))
			die('Preincoming wurde nicht gefunden'); 

		$person = new person(); 
		$person->load($preincoming->person_id); 
		
		if($preincoming->firma_id == "")
		{
			$universitaet = $preincoming->universitaet; 
		}
		else
		{
			$universitaetId = $preincoming->firma_id; 
			$firma = new firma(); 
			if(!$firma->load($preincoming->firma_id))
				die('Universitaet nicht gefunden'); 
			$universitaet = $firma->name; 
		}
		
		$lvs = $preincoming->getLehrveranstaltungen($preincoming->preincoming_id); 
		$date = new datum(); 
		$datum = $date->formatDatum($person->gebdatum,'d.m.Y'); 	
		$von = $date->formatDatum($preincoming->von,'d.m.Y'); 	
		$bis = $date->formatDatum($preincoming->bis,'d.m.Y');
		
		header("Content-type: application/xhtml+xml");
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		echo "<learningagreement>\n"; 
		echo "\n<student>\n";
		echo "  <titel_post><![CDATA[$person->titelpost]]></titel_post>\n";
		echo "  <titel_pre><![CDATA[$person->titelpre]]></titel_pre>\n";  
		echo "	<vorname><![CDATA[$person->vorname]]></vorname>\n";
		echo "	<nachname><![CDATA[$person->nachname]]></nachname>\n";
		echo "	<gebdatum><![CDATA[$datum]]></gebdatum>\n";
		echo "	<universitaet><![CDATA[$universitaet]]></universitaet>\n";
		echo "	<von><![CDATA[$von]]></von>\n";
		echo "	<bis><![CDATA[$bis]]></bis>\n";

		foreach($lvs as $lv)
		{
			$lehrveranstaltung = new lehrveranstaltung(); 
			$lehrveranstaltung->load($lv); 
			$studiengang = new studiengang(); 
			$studiengang->load($lehrveranstaltung->studiengang_kz); 
			
			echo "  <lehrveranstaltung>\n"; 
			echo "   <bezeichnung><![CDATA[$lehrveranstaltung->bezeichnung]]></bezeichnung>\n"; 
			echo "   <semester><![CDATA[$lehrveranstaltung->semester]]></semester>\n";
			echo "   <ects><![CDATA[$lehrveranstaltung->ects]]></ects>\n";
			echo "   <studiengang><![CDATA[$studiengang->english]]></studiengang>\n";
			echo "  </lehrveranstaltung>\n"; 
		}
		
		if($preincoming->bachelorthesis)
			echo '<bachelorthesis><![CDATA['.$preincoming->research_area.']]></bachelorthesis>';
		if($preincoming->masterthesis)
			echo '<masterthesis><![CDATA['.$preincoming->research_area.']]></masterthesis>';
		if($preincoming->deutschkurs1)
			echo '<deutschkurs1><![CDATA[German for Beginners]]></deutschkurs1>';
		if($preincoming->deutschkurs2)
			echo '<deutschkurs2><![CDATA[German Advanced]]></deutschkurs2>'; 
		if($preincoming->deutschkurs3)
			echo '<deutschkurs3><![CDATA[German Intensive Language Course]]></deutschkurs3>';
		echo "</student>\n"; 
		echo "</learningagreement>\n"; 
	}
	else
		die('Parameter id is missing'); 
}