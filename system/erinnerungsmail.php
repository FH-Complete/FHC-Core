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
require_once('../config.inc.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur DB');

$qry = "SELECT distinct email FROM public.tbl_studiengang WHERE studiengang_kz!=0 AND email is not null";

$headers = "From: vilesci@technikum-wien.at";
$message = "Dies ist eine automatische eMail!\nBitte überprüfen Sie die Lehrauftragsliste";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$to = "oesi@technikum-wien.at";
 		$subject = "Erinnerung $row->email";
 		
    	if(mail($to, $subject, $message, $headers))
			echo "Email an $to versandt\n";
    	 else
        	echo "Fehler beim Versenden des Erinnerungsmails an $to\n";
	}
}
?>