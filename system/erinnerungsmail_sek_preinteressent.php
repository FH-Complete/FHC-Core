<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 */
/*
 * Versendet Erinnerungsmails an die Assistenz zur Uebernahme der Freigegebenen Preinteressenten
 */
require_once('../vilesci/config.inc.php');
require_once('../include/studiengang.class.php');
require_once('../include/preinteressent.class.php');
require_once('../include/person.class.php');
require_once('../include/datum.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

   
$studiengang = new studiengang($conn);
$studiengang->getAll();
$datum_obj = new datum();
$message_sync='';
//alle Studiengaenge durchlaufen
foreach ($studiengang->result as $stg)
{
	//Freigegebene aber noch nicht uebernommene Preinteressenten des Studienganges laden
	$preinteressent = new preinteressent($conn);
	$preinteressent->loadFreigegebene($stg->studiengang_kz);
	
	if(count($preinteressent->result)>0)
	{
		$message="Dies ist eine automatische Mail!\n\n";
		$message.="Die folgenden Preinteressenten wurden zur �bernahme, f�r den Studiengang $stg->kuerzel, freigegeben aber noch nicht �bernommen:\n\n";
		
		foreach ($preinteressent->result as $row)
		{
			$person = new person($conn);
			$person->load($row->person_id);
			$message.="- $person->nachname $person->vorname ".($person->gebdatum!=''?"(Geburtsdatum: ".$datum_obj->formatDatum($person->gebdatum,'d.m.Y').')':'')."\n";
		}
		
		$message.="\nSie k�nnen die Personen im FAS unter 'Extras->Preinteressenten �bernehmen' oder unter folgendem Link:\n";
		$message.=APP_ROOT."vilesci/personen/preinteressent_uebernahme.php?studiengang_kz=$stg->studiengang_kz";
		$message.="\nins FAS �bertragen";
		$to = $stg->email;
		//Mail versenden
		if(mail($to, 'Preinteressent �bernahme - Erinnerungsmail', $message, 'FROM: vilesci@'.DOMAIN))
			$message_sync.="Studiengang: $stg->kuerzel EMail-Versand an $stg->email ... ok\n";
		else 
			$message_sync.="Studiengang: $stg->kuerzel EMail-Versand an $stg->email ... FEHLER BEIM SENDEN !!!\n";
	}
}
if($message_sync!='')
{
	//Mail an Administration
	$message_sync = "Dies ist eine automatische Mail!\n\nEs wurden folgende Benachrichtungen zur Preinteressenten�bernahme verschickt:\n\n".$message_sync;
	$to = MAIL_ADMIN;
	if(mail($to, 'Preinteressent �bernahme - Erinnerungsmail', $message_sync, 'FROM: vilesci@'.DOMAIN))
		echo "<br><b>Erinnerungsmails wurden versendet</b>";
	else 
		echo "<br><b>Fehler beim Versenden der Mails</b>";
}

?>