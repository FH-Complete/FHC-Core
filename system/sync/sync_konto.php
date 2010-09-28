<?php
/* Copyright (C) 2010 FH Technikum Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once('config.inc.php');
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/mail.class.php');

$errormsg = '';
$error_count=0;
$insert_count=0;
$update_count=0;

$db = new basis_db();

//Datenbankverbindung zur WaWi Datenbank herstellen
if ($conn_wawi = pg_pconnect(CONN_STRING_WAWI))
{
	//Encoding auf UTF8 setzen, da die WaWi Datenbank LATIN9 kodiert ist
	if(!pg_query($conn_wawi, 'SET CLIENT_ENCODING TO UNICODE;'))
	{
		$errormsg .= 'Fehler beim Setzen des Encodings';
		$error_count++;
	}
	
	//Alle Kontoeintraege aus der WaWi Datenbank holen
	if($result = pg_query($conn_wawi, 'SELECT * FROM public.tbl_konto;'))
	{
		while($row = pg_fetch_object($result))
		{
			//Dazupassenden Eintrag in der neuen Datenbank suchen
			$qry = "SELECT * FROM wawi.tbl_konto WHERE konto_id='".addslashes($row->konto)."'";
			if($result_neu = $db->db_query($qry))
			{
				if($db->db_num_rows($result_neu)>0)
				{
					//Wenn der Eintrag in der neuen Datenbank bereits vorhanden ist -> Update
					if($row_neu = $db->db_fetch_object($result_neu))
					{
						$update = 'UPDATE wawi.tbl_konto SET ';
						$bedingung = '';
						
						//Spalten ueberpruefen
						if($row_neu->beschreibung != $row->beschreibung)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " beschreibung=".$db->addslashes($row->beschreibung);
						}
					}
				}
				else
				{
					$db->addslashes($var);
					//Wenn der Eintrag noch nicht vorhanden ist, dann wird er neu angelegt
					// INSERT INTO wawi.tbl_konto (konto_id, kontonr,....) VALUES(..)
				}
			}
		}
	}
}
else
{
	$errormsg .= 'Es konnte keine Verbindung zum WAWI Server aufgebaut werden';
	$error_count++;
}

$msg = "
$update_count Datensätze wurden geändert.
$insert_count Datensätze wurden hinzugefügt.
$error_count Fehler sind dabei aufgetreten!";

$msg.=$errormsg;

$mail = new mail(MAIL_ADMIN, 'vilesci.technikum-wien.at', 'WaWi Syncro - Konto', $msg);
if(!$mail->send())
	echo 'Fehler beim Senden des Mails';
else
	echo 'Mail verschickt!';
?>			