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
require_once('../include/basis_db.class.php');
require_once('../include/mail.class.php');
require_once('../include/person.class.php');
require_once('../include/kontakt.class.php');
?>

<html>
	<head>
	<title>Zugangscode Senden</title>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css">
	</head>

	<body class="background_main">
		<h2>Email an Neuanfänger schicken</h2>

<?php

$db = new basis_db(); 
$count = 0;
$count_studenten = 0; 
$countError = 0; 

$qry = "SELECT * FROM public.tbl_benutzer 
        JOIN public.tbl_person USING(person_id)
        WHERE uid LIKE '__12%' 
        AND foto is null";

  
if($result = $db->db_query($qry))
{
    while($row = $db->db_fetch_object($result))
    {
        $kon_alt='';
        $person = new person();
        $person->load($row->person_id);
     
        // private email holen
        $kontakt = new kontakt(); 
        $kontakt->load_persKontakttyp($row->person_id, 'email');
        
        $zugangscode = $row->zugangscode; 
        $count_studenten+=1; 
        
        // Falls mehrere vorhanden sind, an alle schicken
        foreach($kontakt->result as $kon)
        {
            if($kon_alt != $kon->kontakt)
            {
                
                if(sendMail($kon->kontakt, $row->nachname, $zugangscode))
                {
                    echo $kon->kontakt.'</br>';
                    $count+=1; 
                }
                else
                    $countError+=1; 
            }
            
            $kon_alt = $kon->kontakt; 
        }
    }
}
else
    echo('Fehler bei der Abfrage aufgetreten');


echo $count.' Emails an '.$count_studenten.' Studenten geschickt. Es sind dabei '.$countError.' Fehler aufgetreten';

function sendMail($email, $name, $zugangscode)
{
    // trim zugangscode
    // an private email schicken
    $msg = '<b>Sehr geehrter Herr/Frau '.$name.'</b><br><br>';
    $msg.= 'Willkommen an der Fachhochschule Technikum Wien. <br><br>';
    $msg.= 'Für Ihren FH-Ausweis, der gleichzeitig als Zutrittskarte dient, benötigen wir ein Foto von Ihnen.<br>';
    $msg.= 'Bitte nutzen Sie den folgenden Link und den angegebenen Zugangscode um Ihr Foto hochzuladen.<br><br>';
    $msg.= '<a href="https://cis.technikum-wien.at/cis/public/prestudententool/index.php">https://cis.technikum-wien.at/cis/public/prestudententool/index.php</a><br>';
    $msg.= 'Zugangscode: '.trim($zugangscode).'<br><br>';
    $msg.= 'Die Kriterien, die Ihr Foto erfüllen muss, finden Sie beim Upload oder unter <a href="https://cis.technikum-wien.at/cms/content.php?content_id=6174">https://cis.technikum-wien.at/cms/content.php?content_id=6174</a> <br><br>';
    $msg.= 'Technische Unterstützung erhalten Sie unter <a href="mailto:support@technikum-wien.at">support@technikum-wien.at</a> <br><br>';
    $msg.= 'Wir wünschen Ihnen einen erfolgreichen Start ins Studium!<br><br>';
    $msg.= 'Fachhochschule Technikum Wien <br><br>';
    
    $msg.= '---------------- <br><br>';
    $msg.= '<b>Dear Mr/Ms '.$name.'</b><br><br>';
    $msg.= 'Welcome to the University of Applied Sciences Technikum Wien.<br><br>';
    $msg.= 'For your UAS identity card, which also serves as a key card, we need a photo of you.<br>';
    $msg.= 'Please use the following link and the access code indicated in order to upload your photo.<br><br>';
    $msg.= '<a href="https://cis.technikum-wien.at/cis/public/prestudententool/index.php?lang=English">https://cis.technikum-wien.at/cis/public/prestudententool/index.php</a><br>';
    $msg.= 'Access code: '.trim($zugangscode).'<br><br>';
    $msg.= 'You can find the criteria that your photo has to fulfil either when uploading or under <a href ="https://cis.technikum-wien.at/cms/content.php?content_id=6174&sprache=English">https://cis.technikum-wien.at/cms/content.php?content_id=6174</a><br><br>';
    $msg.= 'Technical support is available under <a href="mailto:support@technikum-wien.at">support@technikum-wien.at</a> <br><br>';
    $msg.= 'We wish you a successful start to your studies!<br><br>';
    $msg.= 'University of Applied Sciences Technikum Wien';
    
    $mail = new mail($email, 'no-reply', 'Fotoupload für Ihren FH-Ausweis', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($msg); 
	if($mail->send())
		return true; 
	else
		return false; 
}
?>
    </body>
</html>