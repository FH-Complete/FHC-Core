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
	<title>Check Studenten</title>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css">
	</head>

	<body class="background_main">
		<h2>Email an Neuanfänger schicken</h2>

<?php

$db = new basis_db(); 
$count = 0;
$countError = 0; 

$qry = "SELECT * FROM public.tbl_benutzer 
        JOIN public.tbl_person USING(person_id)
        WHERE uid LIKE '__12%' 
        AND foto is null";

if($result = $db->db_query($qry))
{
    while($row = $db->db_fetch_object($result))
    {
        $person = new person();
        $person->load($row->person_id);
     
        // private email holen
        $kontakt = new kontakt(); 
        $kontakt->load_persKontakttyp($row->person_id, 'email');
        
        $name = $row->anrede.' '.$row->nachname; 
        $zugangscode = $row->zugangscode; 
        
        // Falls mehrere vorhanden sind, an alle schicken
        foreach($kontakt->result as $kon)
        {
            if(sendMail($kon->kontakt, $name, $zugangscode))
                $count+=1; 
            else
                $countError+=1; 
        }
    }
}
else
    echo('Fehler bei der Abfrage aufgetreten');


echo 'Email an '.$count.' Empfänger geschickt. Es sind dabei '.$countError.' aufgetreten';

function sendMail($email, $name, $zugangscode)
{
    // trim zugangscode
    // an private email schicken
    
    $msg = 'TEXT';
    
    $mail = new mail($email, 'no-reply', 'Fotoupload für Ihren FH-Ausweis', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($msg); 
	if(!$mail->send())
		return true; 
	else
		return false; 
}
?>
    </body>
</html>