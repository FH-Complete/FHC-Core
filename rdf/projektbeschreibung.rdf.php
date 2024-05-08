<?php

/* Copyright (C) 2012 FH Technikum-Wien
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
require_once('../include/projekt.class.php');
require_once('../include/projektphase.class.php');
require_once('../include/projekttask.class.php'); 
require_once('../include/datum.class.php');
require_once('../include/ressource.class.php');
require_once('../include/organisationseinheit.class.php');

header("Content-type: application/xhtml+xml");

if(isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{
    if(isset($_REQUEST['projekt_kurzbz']))
    {        
        // Projekt laden
        $projekt_kurzbz = $_REQUEST['projekt_kurzbz']; 
        $projekt = new projekt(); 
        $datum = new datum(); 
        $ressource = new ressource(); 
        $phasen = new projektphase(); 
        
        $org = new organisationseinheit(); 
        
        if(!$projekt->load($projekt_kurzbz))
            die('Fehler beim laden des Projektes');
       
        if(!$ressource->getProjectRessourcen($projekt_kurzbz))
            die('Fehler beim laden der Ressourcen');
        
        // lädt alle Phasen der ersten Ebene
        if(!$phasen->getProjektphasen($projekt_kurzbz, true))
            die('Fehler beim laden der Phasen');
        
        if(!$org->load($projekt->oe_kurzbz))
            die('Fehler beim laden der OE');
        
        echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        echo '<projekte>';
        echo '  <projekt>';
        echo '      <projekt_titel><![CDATA['.$projekt->titel.']]></projekt_titel>';
        echo '      <projekt_nummer><![CDATA['.$projekt->nummer.']]></projekt_nummer>';
        echo '      <projekt_beginn><![CDATA['.$datum->formatDatum($projekt->beginn, 'd.m.Y').']]></projekt_beginn>';
        echo '      <projekt_ende><![CDATA['.$datum->formatDatum($projekt->ende, 'd.m.Y').']]></projekt_ende>';
        echo '      <projekt_budget><![CDATA['.$projekt->budget.']]></projekt_budget>';
        echo '      <projekt_beschreibung><![CDATA['.$projekt->beschreibung.']]></projekt_beschreibung>';
        echo '      <projekt_oe><![CDATA['.$org->bezeichnung.']]></projekt_oe>';
        echo '      <projekt_ressourcen>';
        foreach($ressource->result as $res)
            echo '          <pr_ressource><bezeichnung><![CDATA['.$res->bezeichnung.']]></bezeichnung></pr_ressource>';
        echo '      </projekt_ressourcen>';
        echo '      <phasen>';
        
        foreach($phasen->result as $phase)
        {
            $ressource_phasen = new ressource(); 
            $ressource_phasen->getPhaseRessourcen($phase->projektphase_id);
            
            echo '          <phase>';
            echo '              <phase_bezeichnung><![CDATA['.$phase->bezeichnung.']]></phase_bezeichnung>';
            echo '              <phase_beschreibung><![CDATA['.$phase->beschreibung.']]></phase_beschreibung>';
            echo '              <phase_beginn><![CDATA['.$datum->formatDatum($phase->start, 'd.m.Y').']]></phase_beginn>';
            echo '              <phase_end><![CDATA['.$datum->formatDatum($phase->ende,'d.m.Y').']]></phase_end>';
            echo '              <phase_budget><![CDATA['.$phase->budget.']]></phase_budget>';
            echo '              <phase_ressourcen>';
            foreach($ressource_phasen->result as $res_phase)
                echo '                  <ressource><bezeichnung><![CDATA['.$res_phase->bezeichnung.']]></bezeichnung></ressource>';
            
            echo '              </phase_ressourcen>';
            
            $tasks = new projekttask(); 
            $tasks->getProjekttasks($phase->projektphase_id); 
            foreach($tasks->result as $task)
            {
                $ressource_task = new ressource(); 
                $ressource_task->load($task->ressource_id); 
                
                echo '              <task>';
                echo '                  <task_bezeichnung><![CDATA['.$task->bezeichnung.']]></task_bezeichnung>';
                echo '                  <task_beschreibung><![CDATA['.$task->beschreibung.']]></task_beschreibung>'; 
                echo '                  <task_ende><![CDATA['.$datum->formatDatum($task->ende, 'd.m.Y').']]></task_ende>';
                echo '                  <task_ressource><![CDATA['.$ressource_task->bezeichnung.']]></task_ressource>';
                echo '              </task>'; 
            }
            
            $unterphase = new projektphase(); 
            $unterphase->getAllUnterphasen($phase->projektphase_id); 
            foreach($unterphase->result as $uphase)
            {
                $ressource_uphasen = new ressource(); 
                $ressource_uphasen->getPhaseRessourcen($uphase->projektphase_id);
                
                echo '              <unterphase>';
                echo '                  <phase_bezeichnung><![CDATA['.$uphase->bezeichnung.']]></phase_bezeichnung>';
                echo '                  <phase_beschreibung><![CDATA['.$uphase->beschreibung.']]></phase_beschreibung>';
                echo '                  <phase_beginn><![CDATA['.$datum->formatDatum($uphase->start, 'd.m.Y').']]></phase_beginn>';
                echo '                  <phase_end><![CDATA['.$datum->formatDatum($uphase->ende,'d.m.Y').']]></phase_end>';
                echo '                  <phase_budget><![CDATA['.$uphase->budget.']]></phase_budget>';
                echo '                  <phase_ressourcen>';
                foreach($ressource_uphasen->result as $res_phase)
                echo '                  <ressource><bezeichnung><![CDATA['.$res_phase->bezeichnung.']]></bezeichnung></ressource>';
                echo '                  </phase_ressourcen>';
                
            $utasks = new projekttask(); 
            $utasks->getProjekttasks($uphase->projektphase_id); 
            foreach($utasks->result as $task)
            {
                $ressource_task = new ressource(); 
                $ressource_task->load($task->ressource_id); 
                
                echo '              <task>';
                echo '                  <task_bezeichnung><![CDATA['.$task->bezeichnung.']]></task_bezeichnung>';
                echo '                  <task_beschreibung><![CDATA['.$task->beschreibung.']]></task_beschreibung>'; 
                echo '                  <task_ende><![CDATA['.$datum->formatDatum($task->ende, 'd.m.Y').']]></task_ende>';
                echo '                  <task_ressource><![CDATA['.$ressource_task->bezeichnung.']]></task_ressource>';
                echo '              </task>'; 
            }
                
                
                echo '              </unterphase>';
            }
            echo '          </phase>';
        }
        
        echo '      </phasen>';
        echo '  </projekt>';
        echo '</projekte>';
    }
    else
        die('Parameter projekt_kurzbz is missing'); 
    
}
else
    die('Use Parameter xmlformat = xml')

?>
