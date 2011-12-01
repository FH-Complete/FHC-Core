<?php
/* Copyright (C) 2011 Technikum-Wien
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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>
 */

require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/wawi_bestellung.class.php');
require_once('../include/projekt.class.php');
require_once('../include/projektphase.class.php');
require_once('../include/projekttask.class.php');
require_once('../include/ressource.class.php'); 

if (isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{
	if(isset($_GET['projekt_kurzbz']))
	{
		$projekt_kurzbz = $_GET['projekt_kurzbz'];
		
		$timestamp = time(); 
		$datum = date("d.m.Y", $timestamp); 
		
		$projekt = new projekt(); 
		if(!$projekt->load($projekt_kurzbz))
			die("Fehler beim laden des Projektes"); 
			
		$projektphase = new projektphase(); 
		if(!$projektphase->getProjektphasen($projekt_kurzbz))
			die("Fehler beim laden der Phasen");
		
		// Offene Projekttasks - > current_date - ORDER BY Ende - LIMIT 3
		$projekttasksOffen = new projekttask(); 
		if(!$projekttasksOffen->getProjekttasksForStatusbericht($projekt_kurzbz))
			die("Fehler beim laden der Tasks"); 

		// Projektphasen nur 1. Ebene - keine Unterphasen
		$projektphasenStatusbericht = new projektphase(); 
		if(!$projektphasenStatusbericht->getProjektphasen($projekt_kurzbz, true))
			die("Fehler beim laden der Phasen"); 
			
		$ressource = new ressource(); 
		if(!$ressource->getProjectRessourcen($projekt_kurzbz))
			die("Fehler beim laden der Ressourcen"); 
			
		$oBestellung = new wawi_bestellung();
		$oBestellung->getBestellungProjekt($projekt_kurzbz);
	
		// Kosten aller dem Projekt zugeordneten Bestellungen
		$projekt_kosten = 0; 
		foreach ($oBestellung->result as $bestellung)
		{	
			$brutto = $bestellung->getBrutto($bestellung->bestellung_id);
			if($brutto == '')
				$brutto = '0';

			$projekt_kosten += $brutto;  
		}
		
		// berechne den Projektfortschritt [GesamtFortschrittPhasen / AnzahlPhasen]
		$projekt_fortschritt = 0; 
		foreach($projektphasenStatusbericht->result as $phase)
		{
			// nur phasen zählen die schon begonnen haben
			if($phase->start < time() && $phase->start != '')
				$projekt_fortschritt += $phase->getFortschritt($phase->projektphase_id); 
		}
			
		$anzahlPhasen = count($projektphasenStatusbericht->result);
		$anzahlPhasen = ($anzahlPhasen == 0)? 1 : $anzahlPhasen;
			
		$projekt_fortschritt = $projekt_fortschritt /  $anzahlPhasen; 
		$projekt_fortschritt = sprintf("%01.2f",$projekt_fortschritt); 

		header("Content-type: application/xhtml+xml");
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		
		echo "\n<statusbericht>\n";
		echo "	<datum><![CDATA[$datum]]></datum>\n";
		echo "	<projekt_titel><![CDATA[$projekt->titel]]></projekt_titel>\n";
		echo "	<projekt_kuerzel><![CDATA[$projekt->projekt_kurzbz]]></projekt_kuerzel>\n";
		echo "	<projekt_budget><![CDATA[$projekt->budget]]></projekt_budget>\n";
		echo "	<projekt_ziele><![CDATA[$projekt->beschreibung]]></projekt_ziele>\n";
		echo "  <projekt_kosten><![CDATA[$projekt_kosten]]></projekt_kosten>\n";
		echo "  <projekt_fortschritt><![CDATA[$projekt_fortschritt]]></projekt_fortschritt>\n"; 
		echo "	<ressourcen>\n";
		foreach($ressource->result as $res)
			echo "    <ressource><bezeichnung><![CDATA[$res->bezeichnung]]></bezeichnung></ressource>\n";	
		echo "  </ressourcen>\n";
		echo "  <naechste_schritte>\n";
		foreach($projekttasksOffen->result as $taskOffen)
			echo "    <schritt><beschreibung><![CDATA[$taskOffen->bezeichnung]]></beschreibung></schritt>\n";
		echo "  </naechste_schritte>\n";
		echo "  <projektphasen>\n";
		foreach($projektphasenStatusbericht->result as $phasen)
		{	
			$fortschritt = '0';
			if($phasen->start < time() && $phasen->start != '')
				$fortschritt = $phasen->getFortschritt($phasen->projektphase_id); 
				
			echo "    <phase>\n";
			echo "      <bezeichnung><![CDATA[$phasen->bezeichnung]]></bezeichnung>\n";
			echo "      <fortschritt><![CDATA[$fortschritt]]></fortschritt>\n"; 
			echo "    </phase>\n"; 
		}
		echo "  </projektphasen>\n";  
		echo "</statusbericht>\n";
	}
	else
	{
		echo "Parameter: projekt_kurzbz"; 
	}
}
else
	die('Use Parameter xmlformat=xml');
?>
