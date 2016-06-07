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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at> and
 *          Andreas Moik  <moik@technikum-wien.at>.
 */

require_once('../config/vilesci.config.inc.php');
require_once('../include/preoutgoing.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/firma.class.php');
require_once('../include/standort.class.php');
require_once('../include/adresse.class.php');
require_once('../include/nation.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/lehrverband.class.php');

header("Content-type: application/xhtml+xml");

if (isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{
    if(!isset($_REQUEST['preoutgoing_id']))
        die('Parameter preoutgoing_id is missing!');

    $preoutgoing_id = $_REQUEST['preoutgoing_id'];

    $preoutgoing = new preoutgoing();
    $studiengang = new studiengang();
    $prestudent = new prestudent();
    $lehrverband = new lehrverband();

    if(!$preoutgoing->load($preoutgoing_id))
        die('Konnte Outgoing nicht finden!');
    
    
    if(!$prestudent->load($preoutgoing->prestudent_id))
        die('Konnte Prestudent nicht laden!');

    $projektarbeittitel = $preoutgoing->projektarbeittitel;
    $studiengang->load($prestudent->studiengang_kz);
    $preoutgoingFirma = new preoutgoing();
    $preoutgoingFirma->loadAuswahl($preoutgoing_id);
    $preoutgoing_firma = $preoutgoingFirma->firma_id;
    $prestudent->getLastStatus($preoutgoing->prestudent_id);
    $prestudent->load_studentlehrverband($prestudent->studiensemester_kurzbz);

    $firma = new firma();
    $nation = new nation();
    if($preoutgoing_firma != '')
    {
        $standort = new standort(); 
        $adresse = new adresse(); 

        $firma->load($preoutgoing_firma);
        $standort->load_firma($firma->firma_id);
        $adresse->load($standort->adresse_id);
        $nation->load($adresse->nation);
    }
    
    $preoutgoingLv = new preoutgoing(); 
    $preoutgoingLv->loadLvs($preoutgoing_id);
    
    echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?> ';
    echo '<learningagreement_outgoing>';
    echo '  <outgoing>';
    echo '      <vorname><![CDATA['.$prestudent->vorname.']]></vorname>';
    echo '      <nachname><![CDATA['.$prestudent->nachname.']]></nachname>';
    echo '      <titel_pre><![CDATA['.$prestudent->titelpre.']]></titel_pre>';
    echo '      <titel_post><![CDATA['.$prestudent->titelpost.']]></titel_post>';
    echo '      <email><![CDATA['.$prestudent->uid.'@'.DOMAIN.']]></email>';
    echo '      <sending_institution>FH Technikum Wien</sending_institution>';
    echo '      <sending_institution_nation>Austria</sending_institution_nation>';
    echo '      <studiengang><![CDATA['.$studiengang->english.']]></studiengang>';
    echo '      <receiving_institution><![CDATA['.$firma->name.']]></receiving_institution>';
    echo '      <receiving_institution_nation><![CDATA['.$nation->engltext.']]></receiving_institution_nation>';
    echo '      <semester><![CDATA['.$prestudent->semester.']]></semester>';
    echo '      <studiensemester><![CDATA['.$prestudent->studiensemester_kurzbz.']]></studiensemester>';
    echo '		<datum>'.date('d.m.Y').'</datum>';
    echo '      <lehrveranstaltungen>';
    foreach($preoutgoingLv->lehrveranstaltungen as $lv)
        echo'       <lehrveranstaltung><lv>'.$lv->bezeichnung.'</lv><ects>'.$lv->ects.'</ects><wochenstunden>'.$lv->wochenstunden.'</wochenstunden><unitcode>'.$lv->unitcode.'</unitcode></lehrveranstaltung>';
    echo '      </lehrveranstaltungen>';
    if($preoutgoing->bachelorarbeit) // Topic fehlt noch
        echo '       <bachelorarbeit><projektarbeittitel><![CDATA['.$projektarbeittitel.']]></projektarbeittitel></bachelorarbeit>';
    if($preoutgoing->masterarbeit) // Topic fehlt noch
        echo '       <masterarbeit><projektarbeittitel><![CDATA['.$projektarbeittitel.']]></projektarbeittitel></masterarbeit>';
    echo '  </outgoing>';
    echo '</learningagreement_outgoing>';
}else
    die('Parameter xmlformat not set!');


?>
