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

require_once('../../config/cis.config.inc.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/coodle.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/mail.class.php'); 
require_once('../../include/benutzer.class.php'); 
require_once('../../include/reservierung.class.php'); 

if(isset($_GET['lang']))
	setSprache($_GET['lang']);

header("Content-Type: text/html; charset=utf-8");

$sprache = getSprache(); 
$p=new phrasen($sprache); 

$message = '';
$ersteller = false; 
$abgeschlossen = false; 

$coodle_id = (isset($_GET['coodle_id'])?$_GET['coodle_id']:'');

$coodle = new coodle(); 
if(!$coodle->load($coodle_id))
    die($coodle->errormsg); 

// Überprüfen ob Coodle Status laufend oder abgeschlossen hat 
if(!$coodle->checkStatus($coodle_id))
    die('Umfrage ist nicht mehr gültig'); 

// authentifizierung
if(!isset($_GET['zugangscode']))
{
    $uid = get_uid();    
    if(!$coodle->checkBerechtigung($coodle_id, $uid))
        die('Keine Berechtiung für diese Umfrage'); 
    
    // überprüfen ob ersteller gleich uid ist
    if($coodle->ersteller_uid == $uid)
        $ersteller = true; 
}
else
{
    if(!$coodle->checkBerechtigung($coodle_id, '', $_GET['zugangscode']))
        die('Keine Berechtigung für diese Umfrage'); 
}

// checkboxen speichern
if(isset ($_POST['save']))
{
    $coodle_help = new coodle(); 
    
    // Ressource ID von Zugangscode oder UID holen und Beiträge löschen
    if(isset($_GET['zugangscode']))
    {
        
        $coodle_help->getRessourceFromUser($coodle_id, '', $_GET['zugangscode']);
        $coodle_ressource_termin= $coodle_help->deleteRessourceTermin($coodle_id, $coodle_help->coodle_ressource_id);  
        $message = "<span class='ok'>Erfolgreich gespeichert</span>";   // weil wenn alle checkboxen gelöscht werden kommt man nicht mehr in die speichern schleife
    }
    else
    {
        if($coodle_help->RessourceExists($coodle_id, $uid))
        {
            $coodle_help->getRessourceFromUser($coodle_id, $uid);
            $coodle_ressource_termin= $coodle_help->deleteRessourceTermin($coodle_id, $coodle_help->coodle_ressource_id);
            $message = "<span class='ok'>Erfolgreich gespeichert</span>"; 
        }
    }
    
    //  Einträge speichern
    foreach($_POST as $key=>$value)
    {
        if(mb_substr($key, 0, 5) =='check')
        {
            $termin = explode('_', $key);
            $ressource_id = $termin[1];
            $termin_id = $termin[2];
            
            $coodle_ressource_termin = new coodle(); 
            $coodle_ressource_termin->coodle_ressource_id = $ressource_id;
            $coodle_ressource_termin->coodle_termin_id = $termin_id; 
            $coodle_ressource_termin->new = true; 

            if(!$coodle_ressource_termin->saveRessourceTermin())
                $message= "<span class='error'>Fehler beim Speichern aufgetreten</span>"; 
            else
                $message = "<span class='ok'>Erfolgreich gespeichert</span>"; 
        }
    }
}

// endgültige auswahl des termins speichern
if(isset($_POST['auswahl_termin']))
{
    $auswahl = $_POST['auswahl_termin']; 
    
    // setzte auswahl von termin_id auf true
    $coodle_help = new coodle(); 
    $coodle_help->loadTermin($auswahl);
    $coodle_help->auswahl = true; 
    
    // alle termine der coodle_id auf false setzen
    if(!$coodle_help->setTerminFalse($coodle_id))
        exit('Fehler beim Update aufgetreten'); 

    if(!$coodle_help->saveTermin(false))
        $message="<span class='error'>Fehler beim Speichern aufgetreten</span>";
    else
        $message="<span class='ok'>Erfolgreich gespeichert</span>"; 
    
    $coodle_status = new coodle(); 
    $coodle_status->load($coodle_id); 
    $coodle_status->coodle_status_kurzbz = 'abgeschlossen'; 
    $coodle_status->new = false; 
    $coodle_status->save(); 
    
    sendEmail($coodle_id); 
    
    // raum reservieren
    $coodle_raum = new coodle(); 
    $coodle_raum->getRaumeFromId($coodle_id); 
    
    // wenn 1 raum eingetragen ist speichern
    if(count($coodle_raum->result) == 1)
    {
        $raum_reservierung = new reservierung(); 
        $raum_reservierung->ort_kurzb = '';
        $raum_reservierung->studiengang_kz = '0'; 
        $raum_reservierung->uid = $uid; 
        $raum_reservierung->ort_kurzbz = $coodle_raum->result[0]->ort_kurzbz; 
        $raum_reservierung->datum = $coodle_help->datum; 
        
        // uhrzeit in welcher stunde
        
        $raum_reservierung->stunde = '1';
        if($raum_reservierung->save(true))
            echo "Raum wurde gespeichert"; 
    }
    else
        echo "0 oder mehrere räume eingetragen"; 
    
}

$coodle->load($coodle_id); 

if($coodle->coodle_status_kurzbz == 'abgeschlossen')
    $abgeschlossen = true; 

if(isset($_GET['resend']))
{
    if($ersteller && $abgeschlossen)
        sendEmail ($coodle_id);
}

?>

<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>Coodle Übersicht</title>
    <style type="text/css">
    body 
    {
        background: #f9f9f9;
        color: #000;
        font: 14px Arial;
        margin: 0 auto;
        padding: 0;
        position: relative;
    }
    h1,h2,h3,h4,h5,h6{ color:#008462;}

    h5 {margin-top:0px; }
    .container {width: 100%; }
    #header 
    {
        background: #DCDDDF;
        border: 1px solid #c4c6ca;
        position: relative;
        padding-left: 50px; 
    }
    .error 
    {
        color:red;
        padding-left:20px; 
    }
    .ok 
    {
        color:green; 
        padding-left:20px;
    }
    #content 
    {
        padding: 20px 20px;
    }
    #content th 
    {
        color:#008462; 
        padding-left: 10px; 
        padding-right: 10px; 
    }
    #content tr.owner
    {
        background-color: #DCDDDF;
    }
    #content th.auswahl
    {
        color:red;
    }
    a
    {
        color: #008381; text-decoration: none;
        cursor: pointer;
    }
    a:hover
    {
        color: Black; text-decoration: none;
    }
    </style>

</head>
<body>
<div class="container">
    <section id="header">
        <?php 
        $coodle_help = new coodle(); 
        $coodle_help->load($coodle_id); 
  
        $alt = strtotime($coodle_help->insertamum) ;

        $differenz = time() - $alt;
        $differenz = $differenz / 86400;

        
        echo '<h1>'.$coodle->titel.'</h1>'; 
        echo '<h5>Erstellt von '.$coodle->ersteller_uid.' ( vor '.round($differenz).' Tagen)</h5>';
        echo '<h4>'.$coodle->beschreibung.'</h4>';
        ?>
    </section>
</div>
    <div class="main">
        <?php
        $coodle_ressourcen = new coodle(); 
        $coodle_ressourcen->getRessourcen($coodle_id);
        
        // alle termine der coodle umfrage holen
        $coodle_termine = new coodle(); 
        $coodle_termine->getTermine($coodle_id);
        
        $datum = new datum(); 
        
        echo "<br>&nbsp;";
        if(!isset($_GET['zugangscode']))
            echo "<a href='".APP_ROOT."cis/private/coodle/uebersicht.php'><< zurück zur Übersicht</a>"; 
        
         if($ersteller && $abgeschlossen)
            echo '<a href="'.$_SERVER['PHP_SELF'].'?coodle_id='.$coodle_id.'&resend" style="padding-left:25px;">Einladungen neu verschicken</a>'; 

        echo "<section id='content'>
                    <form action='' method='POST'>

                <table>
                <tr><td></td>";
        foreach($coodle_termine->result as $termin)
        {
            $class_auswahl='normal';
            $time = strtotime($termin->uhrzeit); 
            $coodle_auswahl = new coodle(); 
            
            // Falls es schon eine Auswahl gibt - hervorheben
            if($coodle_auswahl->checkTerminAuswahl($coodle_id, $termin->coodle_termin_id))
                $class_auswahl = 'auswahl';
            
            echo "<th class='".$class_auswahl."'>".$datum->formatDatum($termin->datum, 'd.m.Y').'<br>'.date('H:i',$time)."</th>";
        }
        
        echo "</tr>";
        
        // ressourcen durchlaufen
        foreach($coodle_ressourcen->result as $ressource)
        {
            $name = '';
            $class ='normal'; 
            $benutzer = new benutzer(); 
            
            // wenn uid gesetzt ist nimm uid
            if($ressource->uid != '')
            {
                $benutzer->load($ressource->uid); 
                $name =($benutzer->titelpost!='')?$benutzer->titelpost.' ':''; 
                $name.= $benutzer->vorname.' '; 
                $name.=$benutzer->nachname.' '; 
                $name.=$benutzer->titelpre; 
            }

            // wenn uid nicht gesetzt ist nimm zugangscode
            if($ressource->zugangscode !='' && $ressource->uid =='')
                $name = $ressource->name; 
            
            if($ressource->ort_kurzbz != '')
                continue;  
            
            // eigene Reihe farbig hervorheben
            if(isset($_GET['zugangscode']) && $_GET['zugangscode'] == $ressource->zugangscode)
                $class ='owner';
            if(!isset($_GET['zugangscode']) && $ressource->uid == $uid)
                $class = 'owner';
                
            echo "<tr class='".$class."'><td>".$name."</td>";
            // termine zu ressourcen anzeigen
            foreach($coodle_termine->result as $termin)
            {
                $checked ='';
                $disabled='';
                if($coodle_termine->checkTermin($termin->coodle_termin_id, $ressource->coodle_ressource_id))
                        $checked = 'checked';
                
                $coodle_help = new coodle();
                if(isset($_GET['zugangscode']))
                {
                    $coodle_help->getRessourceFromUser($coodle_id, '', $_GET['zugangscode']);
                    if($ressource->coodle_ressource_id != $coodle_help->coodle_ressource_id)
                        $disabled = 'disabled'; 
                }else
                {
                    $coodle_help->getRessourceFromUser($coodle_id, $uid);
                    if($ressource->coodle_ressource_id != $coodle_help->coodle_ressource_id)
                        $disabled = 'disabled'; 
                }
                
                if($abgeschlossen)
                    $disabled='disabled'; 
                
                echo "<td align='center'><input type='checkbox' ".$checked." ".$disabled." name='check_".$ressource->coodle_ressource_id."_".$termin->coodle_termin_id."'></td>";
            }
            
            echo '</tr>';
        }
        
         $disabled = $abgeschlossen?'disabled':'';
        
        if($ersteller)
        {
            // buttons für auswahl des endgültigen termins
            echo '<tr><td>Auswahl:</td>';
            foreach($coodle_termine->result as $termin)
            {
                $checked=($termin->auswahl)?'checked':''; 
                echo '<td align="center"><input type="radio" name="auswahl_termin" '.$checked.' '.$disabled.' value='.$termin->coodle_termin_id.'></td>';
            }
            
            echo "</tr>";
        }
        
       
        if($abgeschlossen)
            $message='<span class="ok">Die Umfrage ist abgeschlossen</span>'; 
        
       

        
        echo "
            <tr><td>&nbsp;</td></tr>
            <tr><td><input type='submit' value='save' name='save' ".$disabled."></td></tr>
            </table>
            </form></section></div>".$message;
        ?>
    </div>
    
    
</body>
</html>

<?php 

/**
 * Funktion sendet den ausgewählten Termin an alle Ressourcen aus der übergebenen Coodleumfrage
 * @global phrasen $p
 * @param type $coodle_id
 * @param type $auswahl 
 */
function sendEmail($coodle_id)
{
     // email senden
    global $p; 
    
    $coodle_help = new coodle(); 
    $termin_id = $coodle_help->getTerminAuswahl($coodle_id); 
    $coodle_help->loadTermin($termin_id);
    
    $coodle_ressource = new coodle();
    $coodle_ressource->getRessourcen($coodle_id);
    
    $coodle= new coodle(); 
    $coodle->load($coodle_id); 
    
    if(count($coodle_ressource->result)>0)
    {
        foreach($coodle_ressource->result as $row)
        {
            if($row->uid!='')
            {
                $benutzer = new benutzer();
                if(!$benutzer->load($row->uid))
                {
                    echo "Fehler beim Laden des Benutzers ".$coodle_ressource->convert_html_chars($row->uid);
                    continue;
                }

                if($benutzer->geschlecht=='w')
                    $anrede = "Sehr geehrte Frau ";
                else
                    $anrede = "Sehr geehrter Herr ";

                $anrede.= $benutzer->titelpre.' '.$benutzer->vorname.' '.$benutzer->nachname.' '.$benutzer->titelpost;

                // Interner Teilnehmer
                $email = $row->uid.'@'.DOMAIN;
            }
            elseif($row->email!='')
            {
                // Externe Teilnehmer
                $email = $row->email;
                $anrede='Sehr geehrte(r) Herr/Frau '.$row->name; 
            }
            else
            {
                // Raueme bekommen kein Mail
                continue;
            }
            $anrede = trim($anrede);
            $sign = "Mit freundlichen Grüßen\n\n";
            $sign .= "Fachhochschule Technikum Wien\n";
            $sign .= "Höchstädtplatz 5\n";
            $sign .= "1200 Wien\n";

            $datum = new datum(); 

            $html=$anrede.'!<br><br>
                Die Terminumfrage zum Thema "'.$coodle_ressource->convert_html_chars($coodle->titel).'" ist beendet.
                <br>
                Der Termin wurde auf den <b>'.$datum->formatDatum($coodle_help->datum, 'd.m.Y').' '.$coodle_help->uhrzeit.'</b> festgelegt.
                <br><br>'.nl2br($sign);

            $text=$anrede."!\n\nDie Terminumfrage zum Thema \"".$coodle_help->convert_html_chars($coodle->titel).'"\" ist beendet.\n
                Der Termin wurde auf den <b>'.$datum->formatDatum($coodle_help->datum, 'd.m.Y').' '.$coodle_help->uhrzeit.'</b> festgelegt\n.
                \n\n$sign';

            $mail = new mail($email, 'no-reply@'.DOMAIN,'Terminbestätigung - '.$coodle->titel, $text);
            $mail->setHTMLContent($html);
            if($mail->send())
            {
                echo $p->t('coodle/mailVersandtAn',array($email))."<br>";
            } 
        }
    }
    else
    {
        die($p->t('coodle/keineRessourcenVorhanden'));
    }
}

?>