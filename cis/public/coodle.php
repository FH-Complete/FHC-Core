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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>,
 * 			Andreas Österreicher <oesi@technikum-wien.at>
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/coodle.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/mail.class.php'); 
require_once('../../include/benutzer.class.php'); 
require_once('../../include/reservierung.class.php'); 
require_once('../../include/stunde.class.php');
require_once('../../include/stundenplan.class.php');
require_once('../../include/mitarbeiter.class.php'); 

header("Content-Type: text/html; charset=utf-8");

$sprache = getSprache(); 
$p=new phrasen($sprache); 
$datum_obj = new datum();
$message = '';
$mailMessage='';
$saveOk=null;
$ersteller = false; 
$abgeschlossen = false; 

$coodle_id = (isset($_GET['coodle_id'])?$_GET['coodle_id']:'');

$coodle = new coodle(); 
if(!$coodle->load($coodle_id))
    die($coodle->errormsg); 

// Überprüfen ob Coodle Status laufend oder abgeschlossen hat 
if(!$coodle->checkStatus($coodle_id))
    die($p->t('coodle/umfrageNichtGueltig')); 

// authentifizierung
if(!isset($_GET['zugangscode']))
{
    $uid = get_uid();    
    if(!$coodle->checkBerechtigung($coodle_id, $uid))
        die($p->t('coodle/keineBerechtigung')); 
    
    // überprüfen ob ersteller gleich uid ist
    if($coodle->ersteller_uid == $uid)
        $ersteller = true; 
}
else
{
    if(!$coodle->checkBerechtigung($coodle_id, '', $_GET['zugangscode']))
        die($p->t('coodle/keineBerechtigung')); 
}

// checkboxen speichern
if(isset ($_POST['save']))
{
    $coodle_help = new coodle(); 
    $error = false; 
    
    // Ressource ID von Zugangscode oder UID holen und Beiträge löschen
    if(isset($_GET['zugangscode']))
    {
        // Einträge löschen
        $coodle_help->getRessourceFromUser($coodle_id, '', $_GET['zugangscode']);
        $coodle_ressource_termin= $coodle_help->deleteRessourceTermin($coodle_id, $coodle_help->coodle_ressource_id);  
    }
    else
    {
        if($coodle_help->RessourceExists($coodle_id, $uid))
        {
            $coodle_help->getRessourceFromUser($coodle_id, $uid);
            $coodle_ressource_termin= $coodle_help->deleteRessourceTermin($coodle_id, $coodle_help->coodle_ressource_id);
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
                $error = true; 
        }
    }
    
    if($error)
    {
        $message.= "<span class='error'>".$p->t('global/fehlerBeimSpeichernDerDaten')."</span><br>"; 
    }
    else
    {
        // email an ersteller senden
        sendBenachrichtigung($coodle_id);
            
    	$saveOk=true;
    }
}

// endgültige auswahl des termins speichern
if(isset($_POST['auswahl_termin']))
{
	if($ersteller)
	{
	    $auswahl = $_POST['auswahl_termin']; 
	    if($auswahl!='')
	    {
		    // setzte auswahl von termin_id auf true
		    $coodle_help = new coodle(); 
		    $coodle_help->loadTermin($auswahl);
		    $coodle_help->auswahl = true; 
		    
		    // alle termine der coodle_id auf false setzen
		    if(!$coodle_help->setTerminFalse($coodle_id))
		        exit('Fehler beim Update aufgetreten'); 
		
		    if(!$coodle_help->saveTermin(false))
		        $message.="<span class='error'>".$p->t('global/fehlerBeimSpeichernDerDaten')."</span><br>";
		    else
				$saveOk=true;
		    
		    $coodle_status = new coodle(); 
		    $coodle_status->load($coodle_id); 
		    $coodle_status->coodle_status_kurzbz = 'abgeschlossen'; 
		    $coodle_status->new = false; 
		    $coodle_status->save(); 
		    
		    sendEmail($coodle_id); 
		    
		    
		    if($coodle_help->datum<RES_TAGE_LEKTOR_BIS)
		    {
			    // Raum reservieren
			    $coodle_raum = new coodle(); 
			    $coodle_raum->getRaumeFromId($coodle_id); 
			    
			    //Ende Uhrzeit berechnen
			    $date = new DateTime($coodle_help->datum.' '.$coodle_help->uhrzeit);
		    	$interval =new DateInterval('PT'.$coodle->dauer.'M');
			    $date->add($interval);
		    	$uhrzeit_ende = $date->format('H:i:s');
		
				foreach($coodle_raum->result as $raum)
				{
					$stunde = new stunde();
					$stunden = $stunde->getStunden($coodle_help->uhrzeit, $uhrzeit_ende);
					
					// Pruefen ob der Raum frei ist
					if(!RaumBelegt($raum->ort_kurzbz, $coodle_help->datum, $stunden))
					{
						$reservierung_error=false;
						// Stunden reservieren
						foreach($stunden as $stunde)
						{
					        $raum_reservierung = new reservierung(); 
					        $raum_reservierung->studiengang_kz = '0'; 
					        $raum_reservierung->uid = $uid; 
					        $raum_reservierung->ort_kurzbz = $raum->ort_kurzbz; 
					        $raum_reservierung->datum = $coodle_help->datum; 
					        $raum_reservierung->stunde = $stunde;
					        $raum_reservierung->titel = mb_substr($coodle->titel,0,10);
					        $raum_reservierung->beschreibung = mb_substr($coodle->titel, 0, 32);
					        $raum_reservierung->insertamum = date('Y-m-d H:i:s');
					        $raum_reservierung->insertvon = $uid;
					        
					        //$message.= "Reserviere $raum->ort_kurzbz Stunde $stunde:";
					        if(!$raum_reservierung->save(true))
				        		$reservierung_error=true;			        	
						}
						$message.= $p->t('coodle/raumErfolgreichReserviert', array($raum->ort_kurzbz)).'<br>';
					}
					else
					{
						$message.='<span class="error">'.$p->t('coodle/raumBelegt', array($raum->ort_kurzbz)).'</span><br>';
					}
			    }
		    }
		    else
		    {
		    	$message.='<span class="error">'.$p->t('coodle/raumNichtReserviert', array($datum_obj->formatDatum(RES_TAGE_LEKTOR_BIS, 'd.m.Y'))).'</span><br>';
		    }
	    }
	}
	else
		$message.= '<span class="error">'.$p-t('global/keineBerechtigung').'</span>';
}


$coodle->load($coodle_id); 

if($coodle->coodle_status_kurzbz == 'abgeschlossen')
    $abgeschlossen = true; 

if(isset($_GET['resend']))
{
    if($ersteller && $abgeschlossen)
        sendEmail ($coodle_id);
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet"  href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/style.css.php" type="text/css">
    <title><?php echo $p->t('coodle/terminauswahl');?></title>
    <style type="text/css">

    #header 
    {
        background: #DCDDDF;
        border: 1px solid #c4c6ca;
        /*position: relative;*/
        padding-left: 50px; 
    }
    .error 
    {
        color:red;
        padding-left:20px; 
    }
    
    #coodle_content 
    {
      
    }
    #coodle_content th 
    {
        color:#008462; 
        padding-left: 10px; 
        padding-right: 10px; 
    }
    #coodle_content tr.owner
    {
        background-color: #DCDDDF;
    }
    #coodle_content th.auswahl
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
    #wrapper 
	{
		width: 70%;
		padding: 0px 10px 15px 10px;
		border: 1px solid #ccc;
		background: #eee;
		text-align: left;
	}

	#wrapper h4 
	{
		font-size: 16px;
		margin-top: 0;
		padding-top: 1em;
	}
    </style>

</head>
<body>
<?php
echo '<h1>'.$p->t('coodle/coodle').'</h1>';

if(!isset($_GET['zugangscode']))
	echo "<a href='".APP_ROOT."cis/private/coodle/uebersicht.php'><< ".$p->t('coodle/zurueckZurUebersicht')."</a>"; 
echo '<br><br>';
echo '<div id="wrapper">';
        
$coodle_help = new coodle(); 
$coodle_help->load($coodle_id); 
  
$alt = strtotime($coodle_help->insertamum) ;

$differenz = time() - $alt;
$differenz = $differenz / 86400;
$benutzer = new benutzer();
$benutzer->load($coodle->ersteller_uid);
$ersteller_name = trim($benutzer->titelpre.' '.$benutzer->vorname.' '.$benutzer->nachname.' '.$benutzer->titelpost);
echo '<h4>'.$coodle->titel.'</h4>'; 
$erstellt = array($ersteller_name, round($differenz)); 
echo '<span style="color: #555555">'.$p->t('coodle/erstelltVon', $erstellt).'</span><br><br>';
// echo '<h5>Erstellt von '.$coodle->ersteller_uid.' ( vor '.round($differenz).' Tagen)</h5>';
echo $coodle->beschreibung;

echo '    
</div>
<br><br>
<div>';
        
        $coodle_ressourcen = new coodle(); 
        $coodle_ressourcen->getRessourcen($coodle_id);
        
        // alle termine der coodle umfrage holen
        $coodle_termine = new coodle(); 
        $coodle_termine->getTermine($coodle_id);
        
        $datum = new datum(); 
        
        echo "<div id='coodle_content' >
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
        if($ersteller)
        	echo '<th class="normal">'.$p->t('coodle/keineAuswahl').'</th>';
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
                $name =($benutzer->titelpre!='')?$benutzer->titelpre.' ':''; 
                $name.= $benutzer->vorname.' '; 
                $name.=$benutzer->nachname.' '; 
                $name.=$benutzer->titelpost; 
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
            if($ersteller)
            	echo '<td></td>';
            echo '</tr>';
        }
        
        $disabled = $abgeschlossen?'disabled':'';
        
        if($ersteller)
        {
            // buttons für auswahl des endgültigen termins
            echo '<tr><td>'.$p->t('coodle/auswahlEndtermin').' <a href="#Details" onclick="alert(\''.$p->t('coodle/auswahlHinweis').'\');return false;"><img src="../../skin/images/information.png" style="vertical-align: middle;"></a></td>';
            foreach($coodle_termine->result as $termin)
            {
                $checked=($termin->auswahl)?'checked':''; 
                echo '<td align="center"><input type="radio" name="auswahl_termin" '.$checked.' '.$disabled.' value='.$termin->coodle_termin_id.'></td>';
            }
            echo '<td align="center"><input type="radio" name="auswahl_termin" '.$disabled.' value=""></td>';
            echo "</tr>";
        }

      
        echo "
            <tr><td>&nbsp;</td></tr>
            <tr><td colspan='3'><input type='submit' value='".$p->t('global/speichern')."' name='save' ".$disabled.">";
        
		if($ersteller && $abgeschlossen)
			echo '<input type="button" onclick="window.location.href=\''.$_SERVER['PHP_SELF'].'?coodle_id='.$coodle_id.'&resend\'" value="'.$p->t('coodle/einladungNeuVerschicken').'">';
		if($saveOk===true)
			echo ' <span class="ok">'.$p->t('global/erfolgreichgespeichert').'</span>';
		echo "</td></tr>
            </table>
            </form></div>".$message.'<br>'.$mailMessage;
        if($abgeschlossen)
            echo '<br><br><span class="ok">'.$p->t('coodle/umfrageAbgeschlossen').'</span>'; 
        
        ?>
    </div>
    
</body>
</html>

<?php 

/**
 * Sendet eine Email an den Ersteller der Umfrage
 * @param type $ersteller 
 */
function sendBenachrichtigung($coodle_id)
{
    $coodle_send = new coodle(); 
    if(!$coodle_send->load($coodle_id))
    {
        die("Fehler beim senden aufgetreten");
    }
    
    $email = '';
    $mitarbeiter = new mitarbeiter(); 
    $mitarbeiter->load($coodle_send->ersteller_uid); 
    $person = new person(); 
    $person->load($mitarbeiter->person_id); 
    
    $name = ''; 
    $name.= ($person->titelpre != '')?$person->titelpre.' ':'';
    $name.= $person->vorname.' '.$person->nachname; 
    $name.= ($person->titelpost != '')?' '.$person->titelpost:'';
    
    
    if($person->geschlecht == 'w')
        $email.= 'Sehr geehrte Frau '.$name."!<br><br>";
    else
        $email.="Sehr geehrter Herr ".$name."!<br><br>";
    
    $email.="Ein Termin Ihrer Coodle-Umfrage wurde ausgewählt<br><a href='".APP_ROOT."cis/private/coodle/uebersicht.php'>Link zu Ihrer Coodle Übersicht</a><br><br>Mit freundlichen Grüßen <br><br>
        Fachhochschule Technikum Wien<br>
        Höchstädtplatz 6<br>
        1200 Wien"; 
    
    $mail = new mail($coodle_send->ersteller_uid.'@'.DOMAIN, 'no-reply', 'Coodle Umfrage', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($email); 
	if(!$mail->send())
		die("Fehler beim senden des Mails aufgetreten");	
}

/**
 * Funktion sendet den ausgewählten Termin an alle Ressourcen aus der übergebenen Coodleumfrage
 * @global phrasen $p
 * @param type $coodle_id
 * @param type $auswahl 
 */
function sendEmail($coodle_id)
{
	global $mailMessage;
    global $p; 
    $coodle_help = new coodle(); 
    $termin_id = $coodle_help->getTerminAuswahl($coodle_id); 
    $coodle_help->loadTermin($termin_id);
    
    $coodle_ressource = new coodle();
    $coodle_ressource->getRessourcen($coodle_id);
    $coodle= new coodle(); 
    $coodle->load($coodle_id); 
    $ort='';
    $teilnehmer='';
    foreach($coodle_ressource->result as $row)
    {    	
    	if($row->ort_kurzbz!='')
    	{
    		if($ort!='')
    			$ort.=', ';
    		$ort.="$row->ort_kurzbz";
    	}
    	else
    	{
    		if($row->uid!='')
    		{
    			$benutzer = new benutzer();
    			$benutzer->load($row->uid);
    			$name = trim($benutzer->titelpre.' '.$benutzer->vorname.' '.$benutzer->nachname.' '.$benutzer->titelpost);
    			$mail = $row->uid.'@'.DOMAIN;
    		}
    		else
    		{
    			$mail = $row->email;
    			$name = $row->name;
    		}
    		$coodle_ressource_termin = new coodle();
    		$partstat='';
    		if($coodle_ressource_termin->checkTermin($termin_id, $row->coodle_ressource_id))
    			$partstat='ACCEPTED';
    		else
    			$partstat='TENTATIVE';
    		
    		$teilnehmer.='ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT='.$partstat.';CN='.$name."\n :MAILTO:".$mail."\n";
    	}
    }
    $date = new DateTime($coodle_help->datum.' '.$coodle_help->uhrzeit);
    //Datum des Termins ins richtige Format bringen
    $dtstart = $date->format('Ymd\THis');

    //Ende Datum berechnen
    $interval =new DateInterval('PT'.$coodle->dauer.'M');
    $date->add($interval);
    $dtend = $date->format('Ymd\THis');
    $date = new DateTime();
    $dtstamp = $date->format('Ymd\THis');
    $benutzer = new benutzer();
    $benutzer->load($coodle->ersteller_uid);
    $erstellername = trim($benutzer->titelpre.' '.$benutzer->vorname.' '.$benutzer->nachname.' '.$benutzer->titelpost);
    //Ical File erstellen
    $ical = "BEGIN:VCALENDAR
PRODID:-//Microsoft Corporation//Outlook 11.0 MIMEDIR//EN
VERSION:2.0
METHOD:PUBLISH
BEGIN:VTIMEZONE
TZID:Europe/Vienna
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T020000
TZNAME:GMT+02:00
TZOFFSETTO:+0200
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T030000
TZNAME:GMT+01:00
TZOFFSETTO:+0100
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
ORGANIZER:MAILTO:".$erstellername." <".$coodle->ersteller_uid."@".DOMAIN."
".$teilnehmer."
DTSTART;TZID=Europe/Vienna:".$dtstart."
DTEND;TZID=Europe/Vienna:".$dtend."
LOCATION:".$ort."
TRANSP:OPAQUE
SEQUENCE:0
UID:FHCompleteCoodle".$coodle_id."
DTSTAMP;TZID=Europe/Vienna:".$dtstamp."
DESCRIPTION:".strip_tags(html_entity_decode($coodle->beschreibung, ENT_QUOTES, 'UTF-8'))."
SUMMARY:".strip_tags($coodle->titel)."
PRIORITY:5
CLASS:PUBLIC
END:VEVENT
END:VCALENDAR";

    if(count($coodle_ressource->result)>0)
    {
        foreach($coodle_ressource->result as $row)
        {
            if($row->uid!='')
            {
                $benutzer = new benutzer();
                if(!$benutzer->load($row->uid))
                {
                    $mailMessage.="Fehler beim Laden des Benutzers ".$coodle_ressource->convert_html_chars($row->uid);
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
            $sign = $p->t('mail/signatur');

            $datum = new datum();

            $html=$anrede.'!<br><br>
                Die Terminumfrage zum Thema "'.$coodle_ressource->convert_html_chars($coodle->titel).'" ist beendet.
                <br>
                Der Termin wurde auf den <b>'.$datum->formatDatum($coodle_help->datum, 'd.m.Y').' '.$coodle_help->uhrzeit.'</b> festgelegt.
                <br><br>'.nl2br($sign);

            $text=$anrede."!\n\nDie Terminumfrage zum Thema \"".$coodle_help->convert_html_chars($coodle->titel).'"\" ist beendet.\n
                Der Termin wurde auf den <b>'.$datum->formatDatum($coodle_help->datum, 'd.m.Y').' '.$coodle_help->uhrzeit."</b> festgelegt\n.
                \n\n$sign";

            $mail = new mail($email, 'no-reply@'.DOMAIN,'Terminbestätigung - '.$coodle->titel, $text);
            $mail->setHTMLContent($html);
            //ICal Termineinladung hinzufuegen
            $mail->addAttachmentPlain($ical, 'text/calendar', 'meeting.ics');
            if($mail->send())
            {
                $mailMessage.= $p->t('coodle/mailVersandtAn',array($email))."<br>";
            } 
        }
    }
    else
    {
        die($p->t('coodle/keineRessourcenVorhanden'));
    }
}

/**
 * 
 * Prueft ob ein Raum belegt ist
 * @param $ort_kurzbz
 * @param $datum
 * @param array $stunden
 */
function RaumBelegt($ort_kurzbz, $datum, $stunden)
{
	foreach($stunden as $stunde)
	{
		//Reservierungen pruefen
		$raum_reservierung = new reservierung();
		if($raum_reservierung->isReserviert($ort_kurzbz, $datum, $stunde))
		{
			return true;
		}
		
		//Stundenplan abfragen
		$stundenplan = new stundenplan('stundenplan');
		if($stundenplan->isBelegt($ort_kurzbz, $datum, $stunde))
		{
			return true;
		}
		
		//Stundenplan DEV abfragen
		$stundenplan = new stundenplan('stundenplandev');
		if($stundenplan->isBelegt($ort_kurzbz, $datum, $stunde))
		{
			return true;
		}
	}
	return false;
}
?>