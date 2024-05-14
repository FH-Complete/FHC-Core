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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Script zur Erinnerung der Assistenz
 * Dieses Script wird 1x pro Monat per Cronjob gestartet
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/mail.class.php');

$db = new basis_db();

$qry = "SELECT distinct email FROM public.tbl_studiengang WHERE studiengang_kz!=0 AND email is not null AND aktiv";

$message = "Dies ist eine automatische eMail!\n\nAm 20. jedes Monats wird die Lehrauftragsliste automatisch an die Geschäftsführung geschickt. Bitte führen Sie bis dahin noch alle anstehenden Korrekturen durch!\n\nBesten Dank,\nGeschäftsführung";
$subject = "Erinnerung Lehrauftragsliste";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
 		$to = $row->email;

 		$mail = new mail($to, 'vilesci@'.DOMAIN, $subject, $message);
    	if($mail->send())
			echo "Email an $to versandt\n";
    	 else
        	echo "Fehler beim Versenden des Erinnerungsmails an $to\n";
	}
}
?>
