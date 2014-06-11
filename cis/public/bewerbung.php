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
 * 			Manfred Kindl 	<kindlm@technikum-wien.at>
 */

require_once('../../config/cis.config.inc.php'); 

session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
session_start();
if (!isset($_SESSION['bewerbung/user']) || $_SESSION['bewerbung/user']=='') 
{
    $_SESSION['request_uri']=$_SERVER['REQUEST_URI'];

    header('Location: registration.php?method=allgemein');
    exit;
}

//require_once('../../include/functions.inc.php');
require_once('../../include/konto.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/benutzerberechtigung.class.php'); 
require_once('../../include/nation.class.php'); 
require_once('../../include/person.class.php'); 
require_once('../../include/datum.class.php'); 
require_once('../../include/kontakt.class.php'); 
require_once('../../include/adresse.class.php'); 
require_once('../../include/prestudent.class.php');
require_once('../../include/studiengang.class.php'); 
require_once('../../include/zgv.class.php'); 
require_once('../../include/dms.class.php'); 
require_once('../../include/dokument.class.php'); 
require_once('../../include/akte.class.php');
require_once('../../include/mail.class.php'); 
require_once('../../include/studiensemester.class.php'); 
require_once('../../include/studienplan.class.php');
require_once('../../include/basis_db.class.php'); 

$person_id = $_SESSION['bewerbung/personId'];
$akte_id = isset($_GET['akte_id'])?$_GET['akte_id']:'';
$method=isset($_GET['method'])?$_GET['method']:'';
$datum = new datum(); 
$person = new person(); 
if(!$person->load($person_id))
    die('Konnte Person nicht laden'); 

$message = '&nbsp;'; 

if($method=='delete')
{
    $akte= new akte(); 
    if(!$akte->load($akte_id))
    {
        $message = "Ungueltige akte_id übergeben";
    }
    else
    {
    	if($akte->person_id!=$person_id)
    		die('Ungueltiger Zugriff');
    	
        $dms_id = $akte->dms_id;
        $dms = new dms(); 
        
        if($akte->delete($akte_id))
        {
            if(!$dms->deleteDms($dms_id))
                $message = "Konnte DMS Eintrag nicht löschen"; 
            else
                $message = "Erfolgreich gelöscht"; 
        }
        else
        {
            $message="Konnte Akte nicht Löschen"; 
        }
    }
    
}

if(isset($_POST['btn_bewerbung_abschicken']))
{
   // Mail an zuständige Assistenz schicken
    $pr_id = isset($_POST['prestudent_id'])?$_POST['prestudent_id']:''; 
    
    $studiensemester = new studiensemester(); 
    $std_semester = $studiensemester->getakt();    
    
    if($pr_id != '')
    {
        // Status Bewerber anlegen
        $prestudent_status = new prestudent(); 
        $prestudent_status->load($pr_id); 
        
        $alterstatus = new prestudent();
        $alterstatus->getLastStatus($pr_id);
        
        // check ob es status schon gibt
        if(!$prestudent_status->load_rolle($pr_id, 'Bewerber', $std_semester, '1'))
        {
            $prestudent_status->status_kurzbz = 'Bewerber'; 
            $prestudent_status->studiensemester_kurzbz = $std_semester; 
            $prestudent_status->ausbildungssemester = '1'; 
            $prestudent_status->datum = date("Y-m-d H:i:s"); 
            $prestudent_status->insertamum = date("Y-m-d H:i:s"); 
            $prestudent_status->insertvon = ''; 
            $prestudent_status->updateamum = date("Y-m-d H:i:s"); 
            $prestudent_status->updatevon = ''; 
            $prestudent_status->studienplan_id = $alterstatus->studienplan_id;
            $prestudent_status->new = true; 
            if(!$prestudent_status->save_rolle())
                die('Fehler beim anlegen der Rolle'); 
        }
        
        if(sendBewerbung($pr_id))
			echo "<script type='text/javascript'>alert('Sie haben sich erfolgreich beworben. Die zuständige Assistenz wird sich in den nächsten Tagen bei Ihnen melden.');</script>";
        else
			echo "<script type='text/javascript'>alert('Es ist ein Fehler beim versenden der Bewerbung aufgetreten. Bitte versuchen Sie es nocheinmal');</script>";

    }
    
    
}

if(isset($_POST['submit_nachgereicht']))
{   
    $akte = new akte; 
    
    // gibt es schon einen eintrag?
    if(isset($_POST['akte_id']))
    {
        // Update
    }
    else
    {
        // Insert
        $akte->dokument_kurzbz = $_POST['dok_kurzbz'];
        $akte->person_id = $person_id;
        $akte->erstelltam = date('Y-m-d H:i:s');
        $akte->gedruckt = false;
        $akte->titel = ''; 
        $akte->anmerkung = $_POST['txt_anmerkung'];
        $akte->updateamum = date('Y-m-d H:i:s');
        $akte->insertamum = date('Y-m-d H:i:s');
        $akte->uid = '';
        $akte->new = true; 
        $akte->nachgereicht = (isset($_POST['check_nachgereicht']))?true:false; 
        if(!$akte->save())
            echo"Fehler beim Speichern aufgetreten ".$akte->errormsg; 
    }

}

// gibt an welcher Tab gerade aktiv ist
$active = isset($_GET['active'])?$_GET['active']:0; 

// Persönliche Daten speichern
if(isset($_POST['btn_person']))
{
    $person->titelpre = $_POST['titel_pre'];
    $person->vorname = $_POST ['vorname'];
    $person->nachname = $_POST['nachname'];
    $person->titelpost = $_POST['titel_post'];
    $person->gebdatum = $datum->formatDatum($_POST['geburtsdatum'], 'Y-m-d');
    $person->staatsbuergerschaft = $_POST['staatsbuergerschaft']; 
    $person->geschlecht = $_POST['geschlecht']; 
    $person->svnr = $_POST['svnr']; 
	$person->gebort = $_POST['gebort']; 
	$person->geburtsnation = $_POST['geburtsnation']; 
 	
    $person->new = false; 
    if(!$person->save())
        $message=('Fehler beim Speichern der Person aufgetreten'); 
	
	if($person->checkSvnr($person->svnr))
		$message = "SVNR bereits vorhanden"; 
}

// Kontaktdaten speichern
if(isset($_POST['btn_kontakt']))
{
    $kontakt = new kontakt(); 
    $kontakt->load_persKontakttyp($person->person_id, 'email'); 
    // gibt es schon kontakte von user
    if(count($kontakt->result)>0)
    {
        // Es gibt bereits einen Emailkontakt
        $kontakt_id = $kontakt->result[0]->kontakt_id;
        
        if($_POST['email'] == '')
        {
            // löschen
            $kontakt->delete($kontakt_id); 
        }
        else 
        {
	        $kontakt->person_id = $person->person_id; 
	        $kontakt->kontakt_id = $kontakt_id; 
	        $kontakt->zustellung = true; 
	        $kontakt->kontakttyp = 'email';
	        $kontakt->kontakt = $_POST['email'];
	        $kontakt->new = false; 
	        
	        $kontakt->save(); 
        }
    }
    else
    {
        // neuen Kontakt anlegen
        $kontakt->person_id = $person->person_id; 
        $kontakt->zustellung = true; 
        $kontakt->kontakttyp = 'email';
        $kontakt->kontakt = $_POST['email']; 
        $kontakt->new = true; 
        
        $kontakt->save(); 
    }
	
	$kontakt_t = new kontakt(); 
    $kontakt_t->load_persKontakttyp($person->person_id, 'telefon'); 
    // gibt es schon kontakte von user
    if(count($kontakt_t->result)>0)
    {
        // Es gibt bereits einen Emailkontakt
        $kontakt_id = $kontakt_t->result[0]->kontakt_id;
        
        if($_POST['telefonnummer'] == '')
        {
            // löschen
            $kontakt_t->delete($kontakt_id); 
        }
        else 
        {
	        $kontakt_t->person_id = $person->person_id; 
	        $kontakt_t->kontakt_id = $kontakt_id; 
	        $kontakt_t->zustellung = true; 
	        $kontakt_t->kontakttyp = 'telefon';
	        $kontakt_t->kontakt = $_POST['telefonnummer'];
	        $kontakt_t->new = false; 
	        
	        $kontakt_t->save(); 
        }
    }
    else
    {
        // neuen Kontakt anlegen
        $kontakt_t->person_id = $person->person_id; 
        $kontakt_t->zustellung = true; 
        $kontakt_t->kontakttyp = 'telefon';
        $kontakt_t->kontakt = $_POST['telefonnummer']; 
        $kontakt_t->new = true; 
        
        $kontakt_t->save(); 
    }

    // Adresse Speichern
    if($_POST['strasse']!='' && $_POST['plz']!='' && $_POST['ort']!='')
    {
        $adresse = new adresse(); 
        $adresse->load_pers($person->person_id);
        if(count($adresse->result)>0)
        {
            // gibt es schon eine adresse, wird die erste adresse genommen und upgedatet
            $adresse_help = new adresse(); 
            $adresse_help->load($adresse->result[0]->adresse_id); 

            // gibt schon eine Adresse
            $adresse_help->strasse = $_POST['strasse']; 
            $adresse_help->plz = $_POST['plz'];
            $adresse_help->ort = $_POST['ort']; 
            $adresse_help->nation = $_POST['nation']; 
            $adresse_help->updateamum = date('Y-m-d H:i:s'); 
            $adresse_help->new = false; 
            if(!$adresse_help->save())
                die($adresse_help->errormsg); 

        }
        else
        {
            // adresse neu anlegen
            $adresse->strasse = $_POST['strasse']; 
            $adresse->plz = $_POST['plz'];
            $adresse->ort = $_POST['ort']; 
            $adresse->nation = $_POST['nation']; 
            $adresse->insertamum = date('Y-m-d H:i:s'); 
            $adresse->updateamum = date('Y-m-d H:i:s'); 
            $adresse->person_id = $person->person_id; 
            $adresse->zustelladresse = true; 
            $adresse->heimatadresse = true; 
            $adresse->new = true; 
            if(!$adresse->save())
                die('Fehler beim Anlegen der Adresse aufgetreten'); 

        }
    }
}

if(isset($_POST['btn_zgv']))
{
    // Zugangsvoraussetzungen speichern
    $prestudent = new prestudent(); 
    if(!$prestudent->load($_POST['prestudent']))
        die('Prestudent konnte nicht geladen werden'); 
    
    $prestudent->new = false; 
    $prestudent->zgv_code = $_POST['zgv']; 
    $prestudent->zgvort = $_POST['zgv_ort']; 
    $prestudent->zgvdatum = $datum->formatDatum($_POST['zgv_datum'], 'Y-m-d');
    $prestudent->zgvmas_code = $_POST['zgv_master']; 
    $prestudent->zgvmaort = $_POST['zgv_master_ort']; 
    $prestudent->zgvmadatum = $datum->formatDatum($_POST['zgv_master_datum'], 'Y-m-d');
    $prestudent->updateamum = date('Y-m-d H:i:s'); 
    
    if(!$prestudent->save())
        die('Fehler beim Speichern des Prestudenten aufgetaucht.'); 
    
    // Studienplan Speichern
    $prestudent_status = new prestudent();
    
    if($prestudent_status->getLastStatus($_POST['prestudent']))
    {
    	$prestudent_status->new = false;
    	$prestudent_status->studienplan_id=$_POST['studienplan_id'];
    	$prestudent_status->save_rolle();
    }
}


// Abfrage ob ein Punkt schon vollständig ist
 if($person->vorname != '' && $person->nachname != '' && $person->gebdatum != '' && $person->staatsbuergerschaft != '' && $person->geschlecht != '')
 {
     $status_person = true; 
     $status_person_text = '<span id="success">vollständig</span>';
 }
 else
 {
     $status_person = false; 
     $status_person_text = '<span id="error">unvollständig</span>';
 }
 
$kontakt = new kontakt(); 
$kontakt->load_persKontakttyp($person->person_id, 'email'); 
$adresse = new adresse(); 
$adresse->load_pers($person->person_id);
if(count($kontakt->result)>0 && count($adresse->result)>0)
{
    $status_kontakt = true; 
    $status_kontakt_text = '<span id="success">vollständig</span>';
}
else
{
    $status_kontakt = false; 
    $status_kontakt_text = '<span id="error">unvollständig</span>';
}

$prestudent = new prestudent(); 
if(!$prestudent->getPrestudenten($person->person_id))
    die('Fehler beim laden des Prestudenten');

$zgv_auswahl = false; 

// Überprüfe ZGV pro Prestudent
foreach($prestudent->result as $pre)
{
    if($pre->zgv_code != '' || $pre->zgvmas_code != '' || $pre->zgvdoktor_code != '')
        $zgv_auswahl = true; 
}


if(!$zgv_auswahl)
{
    $status_zgv = false; 
    $status_zgv_text = '<span id="error">unvollständig</span>';
}
else
{
    $status_zgv = true; 
    $status_zgv_text = '<span id="success">vollständig</span>'; 
}

$dokument_help = new dokument(); 
$dokument_help->getAllDokumenteForPerson($person_id);
$akte_person= new akte(); 
$akte_person->getAkten($person_id);

$missing = false; 
$help_array = array(); 

foreach($akte_person->result as $akte)
{
    $help_array[] = $akte->dokument_kurzbz;
}

foreach($dokument_help->result as $dok)
{
    if(!in_array($dok->dokument_kurzbz, $help_array))
    {
        $missing = true; 
    }
}

if($missing)
{
    $status_dokumente = false; 
    $status_dokumente_text = '<span id="error">unvollständig</span>';
}
else
{
    $status_dokumente = true; 
    $status_dokumente_text = '<span id="success">vollständig</span>';
}

$konto = new konto();
if($konto->checkKontostand($person_id))
{
	$status_zahlungen = true;
	$status_zahlungen_text = '<span id="success">vollständig</span>';
}
else
{
	if($konto->errormsg=='')
	{
		$status_zahlungen = false;
	    $status_zahlungen_text = '<span id="error">unvollständig</span>';
	}
	else
	{
		$status_zahlungen = false;
		$status_zahlungen_text = '<span id="error">Fehler: '.$konto->errormsg.'</span>';
	}
}

?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Bewerbung für einen Studiengang</title>
<link rel="stylesheet" href="../../skin/styles/jquery-ui-1.10.3.custom.css" />
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
<script src="../../include/js/jquery1.9.min.js"></script>

<script type="text/javascript" src="../../include/js/jquery.idTabs.min.js"></script>
<script>
$(function() {

$( "#tabs" ).tabs({ collapsible: true });
activeTab(<?php echo $active;?>);
$( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
$( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
});

function activeTab(tab_nr)
{
    $( "#tabs" ).tabs({ active: tab_nr });
}

function checkKontakt()
{
	if($("#telefonnummer").val() == '')
    {
        alert("Telefonnummer darf nicht leer sein!"); 
        return false; 
    }
	
	if($("#email").val() == '')
    {
        alert("Email-Adresse darf nicht leer sein!"); 
        return false; 
    }
	
	if($("#strasse").val() == '')
    {
        alert("Strasse darf nicht leer sein!"); 
        return false; 
    }
	
	if($("#plz").val() == '')
    {
        alert("Postleitzahl darf nicht leer sein!"); 
        return false; 
    }
	
	if($("#ort").val() == '')
    {
        alert("Ort darf nicht leer sein!"); 
        return false; 
    }
	
	return true; 
}

function checkPerson()
{
    if($("#nachname").val() == '')
    {
        alert("Ungültiger Nachname!"); 
        return false; 
    }
    if($("#vorname").val() == '')
    {
        alert("Ungültiger Vorname!"); 
        return false; 
    }
    
    if($("#staatsbuergerschaft").val() == '')
    {
        alert("Bitte Staatsbürgerschaft auswählen");
        return false; 
    }

    if($("#gebdatum").val() != '')
    {
        var patt1=new RegExp("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})");
        if(!patt1.test($("#gebdatum").val()))
        {
            alert("Ungültiges Geburtsdatum!");
            return false; 
        }
    }
	
	// Berechnung der Sozialversicherungsnummer wenn AT
	if($("#staatsbuergerschaft").val() == 'A')
    {
		if($("#svnr").val().length != '10')
        {
            alert("Ungültige Sozialversicherungsnummer!"); 
            return false; 
        }
		
		var checksum = 0; 
		var soz_nr = $("#svnr").val(); 
		
		checksum = (3*soz_nr[0])+(7*soz_nr[1])+(9*soz_nr[2])+(5*soz_nr[4])+(8*soz_nr[5])+(4*soz_nr[6])+(2*soz_nr[7])+(1*soz_nr[8])+(6*soz_nr[9])
		checksum = checksum%11; 
		
		if(checksum != soz_nr[3])
		{
			alert("Ungültige Sozialversicherungsnummer!"); 
			return false; 
		}
    }

    return true; 
    
}
</script>

<style>
    
.idTabs {
	position:absolute;
	float:left;
	width:100%;
	padding:0 0 1.75em 1em;
	margin:0;
	list-style:none;
	line-height:1em;
}

.idTabs LI {
	float:left;
	margin:0;
	padding:0;
}

.idTabs A {
	display:block;
	color:#444;
	text-decoration:none;
	font-weight:bold;
	background:#FFF;
	margin:0;
	padding:0.25em 1em;
	border-left:1px solid #fff;
	border-top:1px solid #fff;
	border-right:1px solid #fff;
}
.idTabs A:hover{
	display:block;
	color:#FFF;
	text-decoration:none;
	font-weight:bold;
	background:grey;
	margin:0;
	padding:0.25em 1em;
	border-left:1px solid #fff;
	border-top:1px solid #fff;
	border-right:1px solid #aaa;
}
    
.idTabs .selected
{
	display:block;
	color:#FFF;
	text-decoration:none;
	font-weight:bold;
	background:grey;
	margin:0;
	padding:0.25em 1em;
	border-left:1px solid #fff;
	border-top:1px solid #fff;
	border-right:1px solid #aaa;
}

.ui-tabs-vertical { width: 60em; }
.ui-tabs-vertical .ui-tabs-nav { padding: .2em .1em .2em .2em; float: left; width: 20em; }
.ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%; border-bottom-width: 0px !important; border-right-width: 0 !important; margin: 0 -1px .2em 0; }
.ui-tabs-vertical .ui-tabs-nav li a { display:block; }
.ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; border-right-width: 1px; }
.ui-tabs-vertical .ui-tabs-panel { padding: 1em; float: left; width: 40em;}
#tabs {border: none}
#error
{
    color:red;
    font-size:14px;
    padding-left:38px; 
}

#success
{
    color:green;
    font-size:14px; 
    padding-left:38px; 
    
}


#zgv_menu ul li {
display:inline;
padding: 5px;
    font-family: sans-serif, Helvetica, Arial;
    font-size: 15px;

}
</style>

</head>
<body>
<div id="tabs" style="width:auto">
	<div id="tabs-0" style="width:100%">    
    <ul>
        <li><a href="#tabs-1">&gt;|1| Allgemein <br> </a></li>
        <li><a href="#tabs-2">&gt;|2| Persönliche Daten <br> <?php echo $status_person_text;?></a></li>
        <!--<li><a href="#tabs-3">&gt;|3| Zugangsvoraussetzungen<br> <?php echo $status_zgv_text; ?></a></li>-->
        <li><a href="#tabs-4">&gt;|3| Kontaktinformationen <br> <?php echo $status_kontakt_text;?></a></li>
        <li><a href="#tabs-5">&gt;|4| Dokumente <br> <?php echo $status_dokumente_text;?></a></li>
        <li><a href="#tabs-6">&gt;|5| Zahlungen <br> <?php echo $status_zahlungen_text;?></a></li>		
        <li><a href="#tabs-7">&gt;|6| Bewerbung abschicken <br> </a></li>
    </ul>
	</div>
<div id="tabs-1">
<h2>Allgemein</h2>
<p>Wir freuen uns dass Sie sich für einen oder mehrere unserer Studiengänge bewerben. <br><br>
    Bitte füllen Sie das Formular vollständig aus und schicken Sie es danach ab.<br><br>
    <b>Bewerbungsmodus:</b><br>
    <p style="text-align:justify;">Füllen Sie alle Punkte aus. Sind alle Werte vollständig eingetragen, können Sie unter "Bewerbung abschicken" Ihre Bewerbung and die zuständige Assistenz schicken.<br>
    Diese wird sich in den nächsten Tagen bei Ihnen melden.</p>
    <br><br>
    <p><b>Aktuelle Bewerbungen: </b></p>
    <?php
    
     // Zeige Stati der aktuellen Bewerbungen an
        $prestudent = new prestudent(); 
        if(!$prestudent->getPrestudenten($person_id))
            die('Konnte Prestudenten nicht laden'); 
        
        
        echo "<table border = '1' width = '100%'>
                <tr>
                    <th>Studiengang</th>
                    <th>Status</th>
                    <th>Datum</th>
                    <th>Aktion</th>
					<th>Bewerbungsstatus</th>
                </tr>"; 
        foreach($prestudent->result as $row)
        {
            $stg = new studiengang(); 
            if(!$stg->load($row->studiengang_kz))
                die('Konnte Studiengang nicht laden'); 
            
            $prestudent_status = new prestudent(); 
            $prestatus_help= ($prestudent_status->getLastStatus($row->prestudent_id))?$prestudent_status->status_kurzbz:'Noch kein Status vorhanden'; 
            $bewerberstatus =($prestudent_status->bestaetigtam != '' || $prestudent_status->bestaetigtvon != '')?'bestätigt':'noch nicht bestätigt'; 
            echo "<tr>
                    <td>".$stg->bezeichnung."</td>
                    <td>".$prestatus_help."</td>
                    <td>".$datum->formatDatum($prestudent_status->datum, 'd.m.Y')."</td>
                    <td></td>
					<td>$bewerberstatus</td>
                </tr>";
        }
        
        echo "</table>"; 
    
    ?>
    
    <br>
    <button class='btn_weiter' type='button' onclick='activeTab(1);'>Weiter</button>
</div>
<div id="tabs-2">
<h2>Persönliche Daten</h2>
<?php

    $nation = new nation(); 
    $nation->getAll($ohnesperre = true); 
    $titelpre = ($person->titelpre != '')?$person->titelpre:'';
    $vorname = ($person->vorname != '')?$person->vorname:'';
    $nachname = ($person->nachname != '')?$person->nachname:'';
    $titelpost = ($person->titelpost != '')?$person->titelpost:'';
    $geburtstag = ($person->gebdatum != '')?$datum->formatDatum($person->gebdatum, 'd.m.Y'):'';
	$gebort =  ($person->gebort != '')?$person->gebort:'';
	
    $svnr = ($person->svnr != '')?$person->svnr:'';
    
    echo "
    <form method='POST' action='".$_SERVER['PHP_SELF']."?active=1'>
        <table border='0' >
            <tr>
                <td>Titel vorgestellt: </td><td><input type='text' name='titel_pre' id='titel_pre' value='".$titelpre."'></td>
            </tr>
            <tr>
                <td>Vorname*: </td><td><input type='text' name='vorname' id='vorname' value='".$vorname."'></td>
            </tr>
            <tr>
                <td>Nachname*: </td><td><input type='text' name='nachname' id='nachname' value='".$nachname."'></td>
            </tr>
            <tr>
                <td>Titel nachgestellt: </td><td><input type='text' name='titel_post' id='titel_post' value='".$titelpost."'></td>
            </tr>
            <tr>
                <td>Geburtsdatum* (dd.mm.yyyy): </td><td><input type='text' id='gebdatum' name='geburtsdatum' value='".$geburtstag."'></td>
            </tr>
            <tr>
                <td>Geburtsort: </td><td><input type='text' id='gebort' name='gebort' value='".$gebort."'></td>
            </tr>			
            <tr>
                <td>Geburtsnation: </td>
                <td><Select name='geburtsnation' id='geburtsnation'>
                    <option value=''>-- Bitte auswählen -- </option>";
            $selected = '';
        foreach($nation->nation as $nat)
        {
            $selected = ($person->geburtsnation == $nat->code)?'selected':'';
            echo "<option value='".$nat->code."' ".$selected.">".$nat->kurztext."</option>"; 
        }

        echo "</select></td>
            </tr>			
            <tr>
                <td>Sozialversicherungsnr.: </td><td><input type='text' name='svnr' id='svnr' value='".$svnr."'></td>
            </tr>
            <tr>
                <td>Staatsbürgerschaft*: </td>
                <td><Select name='staatsbuergerschaft' id='staatsbuergerschaft'>
                    <option value=''>-- Bitte auswählen -- </option>";
            $selected = '';
        foreach($nation->nation as $nat)
        {
            $selected = ($person->staatsbuergerschaft == $nat->code)?'selected':'';
            echo "<option value='".$nat->code."' ".$selected.">".$nat->kurztext."</option>"; 
        }

        echo "</select></td>
            </tr>
            <tr>";
        $geschl_m = ($person->geschlecht == 'm')?'checked':'';
        $geschl_w = ($person->geschlecht == 'w')?'checked':'';
        echo"<td>Geschlecht*: </td><td>m: <input type='radio' name='geschlecht' value='m' ".$geschl_m."> w: <input type='radio' name='geschlecht' value='w' ".$geschl_w."></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>            
            <tr>
                <td><input type='submit' value='Speichern' name='btn_person' onclick='return checkPerson();'> &nbsp;<button class='btn_weiter' type='button' onclick='activeTab(2);'>Weiter</button></td>
            </tr>
        </table>
    </form>";
		echo $message; 
    ?>
</div>
	
<!-- <div id="tabs-3">
<h2>Zugangsvoraussetzungen</h2>
<?php
/**
$studiengang = new studiengang(); 
    $prestudent = new prestudent(); 
    if(!$prestudent->getPrestudenten($person->person_id))
        die('Fehler beim laden des Prestudenten');*/
    ?>

<div id="zgv_menu">
<ul class="idTabs"> 
    <?php
	/*
    $studiengang = new studiengang(); 
    $prestudent = new prestudent(); 
    if(!$prestudent->getPrestudenten($person->person_id))
        die('Fehler beim laden des Prestudenten');
    
    // Zeige Studiengänge pro Prestudent an
    foreach($prestudent->result as $pre)
    {
        if(!$studiengang->load($pre->studiengang_kz))
            die('Konnte Studiengang nicht laden'); 
        
        echo "<li><a href='#".$pre->prestudent_id."'>".$studiengang->bezeichnung."</a></li>";
    }*/
   ?> 
</ul> 
</div>
<?php
  /*  foreach($prestudent->result as $pre)
    {
        if(!$studiengang->load($pre->studiengang_kz))
            die('Konnte Studiengang nicht laden'); 
        
        echo "<div id='".$pre->prestudent_id."'>";
        
        $prestudent = new prestudent(); 
        if(!$prestudent->load($pre->prestudent_id))
            die('Konnte prestudenten nicht laden'); 
        
        $zgv = $prestudent->zgv_code; 
        $zgv_ort = $prestudent->zgvort;        
        $zgv_datum = $datum->formatDatum($prestudent->zgvdatum, 'd.m.Y'); 
        $zgvmaster = $prestudent->zgvmas_code;
        $zgvmaster_ort = $prestudent->zgvmaort;
        $zgvmaster_datum = $datum->formatDatum($prestudent->zgvmadatum, 'd.m.Y'); 
        $zgvdoktor = $prestudent->zgvdoktor_code; 
        $zgvdoktor_ort = $prestudent->zgvdoktorort; 
        $zgvdoktor_datum = $datum->formatDatum($prestudent->zgvdoktordatum, 'd.m.Y'); 
        
        echo "<br><br><br><br>
        <form method='POST' action='".$_SERVER['PHP_SELF']."?active=2'>
            <table border='0'>
            	<tr>
            		<td>Studienplan:</td>
            		<td><SELECT name='studienplan_id'>
        <OPTION value=''>-- bitte Auswählen --</option>";
        $studienplan = new studienplan();
        $studienplan->getStudienplaene($prestudent->studiengang_kz);
        
        $prestudentstatus = new prestudent();
        $prestudentstatus->getLastStatus($prestudent->prestudent_id);
        foreach($studienplan->result as $row_studienplan)
        {
        	if($prestudentstatus->studienplan_id==$row_studienplan->studienplan_id)
        		$selected='selected';
        	else
        		$selected='';
        	if($row_studienplan->aktiv)
        	{
        		echo '<OPTION value="'.$row_studienplan->studienplan_id.'" '.$selected.'>'.$row_studienplan->bezeichnung.'</OPTION>';
        	}
        }
		echo " 		</select></td>
            	</tr>
            	<tr>
            		<td>&nbsp;</td>
            		<td></td>
            	</tr>
                <tr>
                    <td>Zugangsvoraussetzung: </td>
                    <td><select name='zgv'><option value=''>-- Bitte auswählen --</option>";
                    
        $zgv_help = new zgv(); 
        if(!$zgv_help->getAll())
            die('Konnte die ZGV nicht laden'); 
        
        foreach($zgv_help->result as $row)
        {
            $selected = ($zgv == $row->zgv_code)?'selected':'';
            echo '<option value="'.$row->zgv_code.'" '.$selected.'>'.$row->zgv_bez.'</option>';
        }
    
        echo"   </select></td>
                </tr>
                <tr>
                    <td>Abgelegt in (Ort): </td><td><input type='text' name='zgv_ort' value='".$zgv_ort."'></td>
                </tr>
                <tr>
                    <td>Abgelegt am (Datum, dd.mm.yyyy): </td><td><input type='text' name='zgv_datum' value='".$zgv_datum."'></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>Zugangsvoraussetzung Master (wenn verfügbar): </td><td><select name='zgv_master'><option value=''>-- Bitte auswählen --</option>";
        
        $zgv_help_master = new zgv(); 
        if(!$zgv_help_master->getAllMaster())
            die('Konnte die ZGV nicht laden'); 
        
        foreach($zgv_help_master->result as $row)
        {
            $selected = ($zgvmaster == $row->zgvmas_code)?'selected':'';
            echo '<option value="'.$row->zgvmas_code.'" '.$selected.'>'.$row->zgvmas_bez.'</option>';
        }
        
        echo "      </select></td>
                </tr>
                <tr>
                    <td>Abgelegt in (Ort): </td><td><input type='text' name='zgv_master_ort' value='".$zgvmaster_ort."'></td>
                </tr>
                <tr>
                    <td>Abgelegt am (Datum, dd.mm.yyyy): </td><td><input type='text' name='zgv_master_datum' value='".$zgvmaster_datum."'></td>
                </tr>
                <tr>
                    <td><input type='hidden' name='prestudent' value='$prestudent->prestudent_id'>&nbsp;</td>
                </tr>
                <tr>
                    <td>Zugangsvoraussetzung Doktorat (wenn verfügbar)</td><td><select name='zgv_doktor'><option value=''>-- Bitte auswählen --</option>";
            
        $zgv_help_doktor = new zgv(); 
   //     if(!$zgv_help_doktor->getAllDoktor())
   //         die($zgv_help_doktor->errormsg); 
        
        foreach($zgv_help_doktor->result as $row_doktor)
        {
            $selected = ($zgvdoktor == $row_doktor->zgvdoktor_code)?'selected':'';
            echo '<option> value="'.$row_doktor->zgvdoktor_code.'" '.$selected.'>'.$row_doktor->zgvdoktor_bez.'</option>';
        }
        
        echo"</select></td><td></td>
            </tr>
            <tr>
                <td>Abgelegt in (Ort): </td><td><input type='text' name='zgv_doktor_ort' value='".$zgvdoktor_ort."'></td>
            </tr>
            <tr>
                <td>Abgelegt am (Datum, dd.mm.yyyy): </td><td><input type='text' name='zgv_doktor_datum' value='".$zgvdoktor_datum."'></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan='2'>&nbsp;<input type='submit' value='Speichern' name='btn_zgv'> &nbsp;<button class='btn_weiter' type='button' onclick='activeTab(3);'>Weiter</button></td>
            </tr>   
                
    
        </table></form> 
                </div>";
    }*/
?>
</div>-->
	
	
	
    <div id="tabs-4">
<h2>Kontaktinformationen</h2>
<?php
    $nation = new nation(); 
    $nation->getAll($ohnesperre=true); 
   
    $kontakt = new kontakt(); 
    $kontakt->load_persKontakttyp($person->person_id, 'email'); 
    $email = isset($kontakt->result[0]->kontakt)?$kontakt->result[0]->kontakt:'';
	
	$kontakt_t = new kontakt();
	$kontakt_t->load_persKontakttyp($person->person_id, 'telefon'); 
    $telefon = isset($kontakt_t->result[0]->kontakt)?$kontakt_t->result[0]->kontakt:'';
	
    $adresse = new adresse(); 
    $adresse->load_pers($person->person_id); 
    $strasse = isset($adresse->result[0]->strasse)?$adresse->result[0]->strasse:'';
    $plz = isset($adresse->result[0]->plz)?$adresse->result[0]->plz:'';
    $ort = isset($adresse->result[0]->ort)?$adresse->result[0]->ort:'';
    $adr_nation = isset($adresse->result[0]->nation)?$adresse->result[0]->nation:'';
	
	
   
     echo "
    <form method='POST' action='".$_SERVER['PHP_SELF']."?active=2'>
        <table border='0'>
		<tr>
                <td>Email*: </td><td><input type='text' name='email' id='email' value='".$email."' size='32'></td>
            </tr>
			<tr>
				<td>Telefonnummer*: </td><td><input type='text' name='telefonnummer' id='telefonnummer' value='".$telefon."' size='32'></td>
			</tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Straße*: </td><td><input type='text' name='strasse' id='strasse' value='".$strasse."'></td>
            </tr>
            <tr>
                <td>Postleitzahl*: </td><td><input type='text' name='plz' id='plz' value='".$plz."'></td>
            </tr>
            <tr>
                <td>Ort*: </td><td><input type='text' name='ort' id='ort' value='".$ort."'></td>
            </tr>
             <tr>
                <td>Nation*: </td>
                <td><Select name='nation'><option>--Bitte auswählen --</option>";
        $selected = '';
        foreach($nation->nation as $nat)
        {
            $selected = ($adr_nation == $nat->code)?'selected':''; 
            echo "<option value='".$nat->code."' ".$selected.">".$nat->kurztext."</option>"; 
        }

        echo "</select></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>    
            <tr>
                <td>&nbsp;</td>
            </tr>                
            <tr>
                <td colspan='2'><input type='submit' value='Speichern' name='btn_kontakt' onclick='return checkKontakt();'> &nbsp;<button class='btn_weiter' type='button' onclick='activeTab(4);'>Weiter</button></td>
            </t>
        </table>
    </form>";
    ?>
</div>
    <div id="tabs-5">
    <h2>Dokumente</h2>
    <p>Bitte laden Sie alle vorhandenen Dokumente, die für Ihre Bewerbung relevant sind, über folgenden Link hoch:</p>
    <?php
    echo '<a href="'.APP_ROOT.'cis/public/dms_akteupload.php?person_id='.$person_id.'" onclick="FensterOeffnen(this.href); return false;">Dokumente Upload</a>';
    ?>
    <p>Dokumente zum Uploaden:</p>
    <?php
    $dokumente_person = new dokument(); 
    $dokumente_person->getAllDokumenteForPerson($person_id, true); 
    
    echo '<table border="1" width="130%">
        
            <tr><th width="50%">Name</th><th width="10%">Status</th><th width="10%">Aktion</th><th width ="80%"></th><th>Info</th>&nbsp;</tr>';
    
    foreach($dokumente_person->result as $dok)
    {
        $akte = new akte; 
        $akte->getAkten($person_id, $dok->dokument_kurzbz); 
        
        if(count($akte->result)>0)
        {
             $akte_id = isset($akte->result[0]->akte_id)?$akte->result[0]->akte_id:''; 
            
            // check ob status "wird nachgereicht"
            if($akte->result[0]->nachgereicht == true)
            {
                // wird nachgereicht
                 $status = '<img title="wird nachgereicht" src="'.APP_ROOT.'skin/images/hourglass.png" width="20px">'; 
                 $nachgereicht_help = 'checked';
                 $div = "<form method='POST' action='".$_SERVER['PHP_SELF']."?active=3'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:true;'>".$akte->result[0]->anmerkung."</span>";
				 $aktion = '<a href="'.$_SERVER['PHP_SELF'].'?method=delete&akte_id='.$akte_id.'&active=3"><img title="löschen" src="'.APP_ROOT.'skin/images/delete.png" width="20px"></a>'; 
            }
            else 
            {
				$dokument = new dokument(); 
				if($dokument->load($akte->result[0]->dokument_kurzbz,$prestudent->prestudent_id))
				{
					// Dokument wurde bereits überprüft
					$status = '<img title="abgegeben" src="'.APP_ROOT.'skin/images/true_green.png" width="20px">'; 
					$nachgereicht_help = '';
					$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."&active=3'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>wird nachgereicht:<input type='checkbox' name='check_nachgereicht' ".$nachgereicht_help."><input type='text' size='15' name='txt_anmerkung'><input type='submit' value='OK' name='submit_nachgereicht'></span><input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'><input type='hidden' name='akte_id' value='".$akte_id."'></form>";
					$aktion = ''; 
				}
				else
				{
					// Dokument hochgeladen ohne überprüfung der Assistenz
					$status = '<img title="abgegeben" src="'.APP_ROOT.'skin/images/check_black.png" width="20px">'; 
					$nachgereicht_help = '';
					$div = "<form method='POST' action='".$_SERVER['PHP_SELF']."&active=3'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>wird nachgereicht:<input type='checkbox' name='check_nachgereicht' ".$nachgereicht_help."><input type='text' size='15' name='txt_anmerkung'><input type='submit' value='OK' name='submit_nachgereicht'></span><input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'><input type='hidden' name='akte_id' value='".$akte_id."'></form>";
					$aktion = '<a href="'.$_SERVER['PHP_SELF'].'?method=delete&akte_id='.$akte_id.'&active=3"><img title="löschen" src="'.APP_ROOT.'skin/images/delete.png" width="20px"></a>'; 
	
				}                
            }
        }
        else
        {
            // Dokument fehlt noch
            $status = '<img title="offen" src="'.APP_ROOT.'skin/images/upload.png" width="20px">';
            $aktion = '<img src="'.APP_ROOT.'skin/images/delete.png" width="20px" title="löschen"> <a href="'.APP_ROOT.'cis/public/dms_akteupload.php?person_id='.$person_id.'&dokumenttyp='.$dok->dokument_kurzbz.'" onclick="FensterOeffnen(this.href); return false;"><img src="'.APP_ROOT.'skin/images/upload.png" width="20px" title="upload"></a><a href="#" onclick="toggleDiv(\'nachgereicht_'.$dok->dokument_kurzbz.'\');"><img src="'.APP_ROOT.'skin/images/hourglass.png" width="20px" title="wird nachgereicht"></a>';
            $div = "<form method='POST' action='".$_SERVER['PHP_SELF']."?active=3'><span id='nachgereicht_".$dok->dokument_kurzbz."' style='display:none;'>wird nachgereicht:<input type='checkbox' name='check_nachgereicht'><input type='text' size='15' name='txt_anmerkung'><input type='submit' value='OK' name='submit_nachgereicht'></span><input type='hidden' name='dok_kurzbz' value='".$dok->dokument_kurzbz."'></form>";
            
        }

		$ben_stg = new basis_db();
		$qry = "SELECT studiengang_kz FROM public.tbl_dokumentstudiengang 
            JOIN public.tbl_prestudent using (studiengang_kz) 
            JOIN public.tbl_dokument using (dokument_kurzbz)
            WHERE dokument_kurzbz = ".$ben_stg->db_add_param($dok->dokument_kurzbz)." and person_id =".$ben_stg->db_add_param($person_id, FHC_INTEGER);
		
		$ben = "Benötigt für: \n";
		if($result = $ben_stg->db_query($qry))
		{
			while($row = $ben_stg->db_fetch_object($result))
			{
				$stg = new studiengang(); 
				$stg->load($row->studiengang_kz); 
				
				$ben .= $stg->bezeichnung."\n"; 
			}
		}
		
		
        echo "<tr><td valign='top'>".$dok->bezeichnung."</td><td valign='top' align='center'>".$status."</td><td valign='top'>".$aktion."</td><td valign='top'>".$div."</td><td><img src='".APP_ROOT."skin/images/info.png' width='20px' title='".$ben."'></td></tr>";
    }
    echo '</table>
            <br>
        <table>
            <tr>
                <td>Status:</td><td></td>
            </tr>
            <tr>
                <td><img title="offen" src="'.APP_ROOT.'skin/images/upload.png" width="20px"></td><td>Dokument noch nicht abgegeben (offen)</td>
            </tr>
            <tr>
                <td><img title="offen" src="'.APP_ROOT.'skin/images/check_black.png" width="20px"></td><td>Dokument wurde abgegeben aber noch nicht überprüft</td>
            </tr>
            <tr>
                <td><img title="offen" src="'.APP_ROOT.'skin/images/hourglass.png" width="20px"></td><td>Dokument wird nachgereicht </td>
            </tr>
            <tr>
                <td><img title="offen" src="'.APP_ROOT.'skin/images/true_green.png" width="20px"></td><td>Dokument wurde bereits überprüft</td>
            </tr>
        </table>

';
    echo '<br>'.$message; 
    ?>
    </div>
    
	<div id="tabs-6">
	<?php
//	$sprache = getSprache();
	$sprache=DEFAULT_LANGUAGE;
	$p = new phrasen($sprache);
//	$uid=get_uid();
	$datum_obj = new datum();
	$studiengang = new studiengang();
	$studiengang->getAll();
	
	$stg_arr = array();
	foreach ($studiengang->result as $row)
		$stg_arr[$row->studiengang_kz]=$row->kuerzel;
	
	//$benutzer = new benutzer();
	//if(!$benutzer->load($uid))
	//	die('Benutzer wurde nicht gefunden');
	
	echo '<h2>'.$p->t('tools/zahlungen').' - '.$person->vorname.' '.$person->nachname.'</h2>';
		
	$konto = new konto();
	$konto->getBuchungstyp();
	$buchungstyp = array();
	
	foreach ($konto->result as $row)
		$buchungstyp[$row->buchungstyp_kurzbz]=$row->beschreibung;
	
	$konto = new konto();
	$konto->getBuchungen($person_id);
	if(count($konto->result)>0)
	{
		echo '<br><br><table>';
		echo '<tr class="liste">';
		echo '
			<td>'.$p->t('global/datum').'</td>
			<td>'.$p->t('tools/zahlungstyp').'</td>
			<td>'.$p->t('lvplan/stg').'</td>
			<td>'.$p->t('global/studiensemester').'</td>
			<td>'.$p->t('tools/buchungstext').'</td>
			<td>'.$p->t('tools/betrag').'</td>
			<td>'.'Zahlungsinformation'.'</td>';
			//<td>'.'Überweisung'.'</td>'; //TODO Phrase einfügen
		echo '</tr>';
//			<td>'.$p->t('tools/zahlungsbestaetigung').'</td>
		$i=0;
		foreach ($konto->result as $row)
		{
			$i++;
			$betrag = $row['parent']->betrag;
			
			if(isset($row['childs']))
			{
				foreach ($row['childs'] as $row_child)
				{
					$betrag += $row_child->betrag;
				}
			}
			
			if($betrag<0)
				$style='style="background-color: #FF8888;"';
			elseif($betrag>0)
				$style='style="background-color: #88DD88;"';
			else 
			{
				$style='class="liste'.($i%2).'"';
			}
			
			echo "<tr $style>";
			echo '<td>'.date('d.m.Y',$datum_obj->mktime_fromdate($row['parent']->buchungsdatum)).'</td>';
			echo '<td>'.$buchungstyp[$row['parent']->buchungstyp_kurzbz].'</td>';
			echo '<td>'.$stg_arr[$row['parent']->studiengang_kz].'</td>';
			echo '<td>'.$row['parent']->studiensemester_kurzbz.'</td>';
			
			echo '<td nowrap>'.$row['parent']->buchungstext.'</td>';
			echo '<td align="right" nowrap>'.($betrag<0?'-':($betrag>0?'+':'')).sprintf('%.2f',abs($row['parent']->betrag)).' €</td>';
			echo '<td align="center">';
			if($betrag==0 && $row['parent']->betrag<0)
				echo 'bezahlt';
				//echo '<a href="pdfExport.php?xml=konto.rdf.php&xsl=Zahlung&uid='.$uid.'&buchungsnummern='.$row['parent']->buchungsnr.'" title="'.$p->t('tools/bestaetigungDrucken').'"><img src="../../skin/images/pdfpic.gif" alt="'.$p->t('tools/bestaetigungDrucken').'"></a>';
			elseif($row['parent']->betrag>0)
			{
				//Auszahlung
			}
			else 
			{
			{
				echo '<a onclick="window.open(';
				echo "'zahlungen_details.php?buchungsnr=".$row['parent']->buchungsnr."','Zahlungsdetails','height=320,width=550,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=no,toolbar=no,location=no,menubar=no,dependent=yes');return false;";
				echo '" href="#">'.$p->t('tools/offen').'</a>';
			}
				echo '</td>';
				/*
				echo '<td align="center">';
				echo '<a href="https://routing.eps.or.at/appl/epsSO/transinit/bankauswahl_prepare.html?lang=de
							&amp;caiSO=%2BaDRiYhLjZXKuB19*CkCTIMQBN6sYSHmjNPQkIglglcYeFS98ZCVrvzVdGw5tF1Fzi
							0JrGhL*WWFcSHu6PWY2FCY2BTH0umA-" target="_blank">
							<img src="../../skin/images/eps-logo_full.gif" width="30" height="30" alt="EPS Überweisung"></a>';

				echo '</td>';
				*/

			}
			echo '</tr>';
		}
		echo '</table>';
	}
	else 
	{
		echo $p->t('tools/keineZahlungenVorhanden');
	}	
	//echo '</td></tr></table';
	?>
	</div>
	
    <div id="tabs-7">
    <h2>Bewerbung abschicken</h2>
    <p>Haben Sie alle Daten korrekt ausgefüllt bzw. alle Dokumente auf das System hochgeladen, können Sie Ihre Bewerbung abschicken.<br>
        Die jeweilige Studiengangsassistenz wird sich in den folgenden Tagen, bezüglich der Bewerbung, bei Ihnen Melden.
        <br><br>Bitte überprüfen Sie nochmals Ihre Daten.<br>
        Um Ihre Bewerbung jetzt abzuschließen klicken auf folgenden Link:</p><br><br>
    <?php
                
    $disabled = 'disabled'; 
    if($status_person == true && $status_kontakt == true && $status_dokumente == true && $status_zahlungen == true)
        $disabled = ''; 
    
    $prestudent_help= new prestudent(); 
    $prestudent_help->getPrestudenten($person->person_id); 
    $stg = new studiengang(); 
    
    
    foreach($prestudent_help->result as $prest)
    {
        $stg->load($prest->studiengang_kz); 
        echo "<br>Bewerbung abschicken für ".$stg->bezeichnung.'<br>'; 
        
        echo '
            <form method="POST" action="'.$_SERVER['PHP_SELF'].'">
                <input type="submit" value="Bewerbung abschicken ('.$stg->kurzbzlang.')" name="btn_bewerbung_abschicken" '.$disabled.'>
                <input type="hidden" name="prestudent_id" value="'.$prest->prestudent_id.'">
            </form>'; 
    }           
            
    ?>
</div>
</div>    
    
    
    <script type="text/javascript">
	
	function FensterOeffnen(adresse) 
	{
		MeinFenster = window.open(adresse, "Info", "width=700,height=200");
  		MeinFenster.focus();
	}
    
    function toggleDiv(div)
    {
        $('#'+div).toggle();
    }
    </script>
</body>
</html>

<?php 

// sendet eine Email an die Assistenz dass die Bewerbung abgeschlossen ist
function sendBewerbung($prestudent_id)
{
    global $person_id; 
    
    $person = new person(); 
    $person->load($person_id); 
    
    $prestudent = new prestudent(); 
    if(!$prestudent->load($prestudent_id))
        die('Konnte Prestudent nicht laden'); 
    
    $studiengang = new studiengang(); 
    if(!$studiengang->load($prestudent->studiengang_kz))
        die('Konnte Studiengang nicht laden'); 
    
    $email = 'Es hat sich ein Student für Ihren Studiengang beworben. <br>';
    $email.= 'Name: '.$person->vorname.' '.$person->nachname.'<br>';
    $email.= 'Studiengang: '.$studiengang->bezeichnung.'<br><br>';
    $email.= 'Für mehr Details, verwenden Sie die Personenansicht im FAS.'; 
    
    $mail = new mail($studiengang->email, 'no-reply', 'Bewerbung '.$person->vorname.' '.$person->nachname, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($email); 
	if(!$mail->send())
		return false; 
	else
		return true; 
    
}

?>
