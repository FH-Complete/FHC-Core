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

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/mail.class.php');
require_once('../../include/datum.class.php');

$errormsg = '';
$ausgabe = '';
$updated_lines = '';
$error_count=0;
$insert_count=0;
$update_count=0;
$interval ='';

$date = new datum(); 
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
	$qry = 'SELECT 
				*,
				cbenutzer.username as cusername,
				lbenutzer.username as lusername,
				konto.lupdate as lkontoupdate
			FROM
				public.konto 
				LEFT JOIN public.benutzer cbenutzer ON (cuser=user_id)
				LEFT JOIN public.benutzer lbenutzer ON (konto.luser=lbenutzer.user_id)
			;';
	if($result = pg_query($conn_wawi, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			//Dazupassenden Eintrag in der neuen Datenbank suchen
			$qry = "SELECT *, beschreibung[1] AS first_beschreibung FROM wawi.tbl_konto WHERE konto_id='".addslashes($row->konto)."'";
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
						if($row_neu->kontonr != $row->kontonr)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " kontonr=".$db->addslashes($row->kontonr);
							$updated_lines .= "kontonr von: \"".$row_neu->kontonr."\" auf: \"".$row->kontonr."\"\n";
							
							$update_count++;
						}
						//Wenn sich Beschreibung ändert, ändert sich kurzbeschreibung auch mit						
						if($row_neu->first_beschreibung != $row->beschreibung)
						{
							$kurzbz = substr($row->beschreibung, 0, 32);  
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " beschreibung[1]=".$db->addslashes($row->beschreibung);
							$bedingung .= ", kurzbz =".$db->addslashes($kurzbz); 
							$updated_lines .= "beschreibung von: \"".$row_neu->first_beschreibung."\" auf: \"".$row->beschreibung."\" \n";
							$updated_lines .= "kurzbz von: \"".$row_neu->kurzbz."\" auf: \"".$kurzbz."\" \n";
							
							$update_count+= 2; 
						}
						
						if($date->formatDatum($row_neu->insertamum, 'Y-m-d H:i:s') != $date->formatDatum($row->cdate, 'Y-m-d H:i:s'))
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " insertamum=".$db->addslashes($row->cdate);
							$updated_lines .= "insertamum von: \"".$row_neu->insertamum."\" auf: \"".$row->cdate."\" \n";
							
							$update_count++;
						}
						
						if ($row_neu->insertvon != $row->cusername)
						{
							if($bedingung!='')
								$bedingung.=',';
							$bedingung .= " insertvon=".$db->addslashes($row->cusername);
							$updated_lines .= "insertvon von: \"".$row_neu->insertvon."\" auf: \"".$row->cusername."\" \n";
							
							$update_count++;
						}
	
						if($date->formatDatum($row_neu->updateamum, 'Y-m-d H:i:s') != $date->formatDatum($row->lkontoupdate, 'Y-m-d H:i:s'))
						{							
							if($bedingung!='')
								$bedingung.=',';	
							$bedingung .= " updateamum=".$db->addslashes($row->lkontoupdate);
							$updated_lines .= "updateamum von: \"".$row_neu->updateamum."\" auf: \"".$row->lkontoupdate."\" \n";
							
							$update_count++;
						}
												
						if($row_neu->updatevon != $row->lusername)
						{
							if($bedingung!='')
							$bedingung.=',';
							$bedingung .= " updatevon=".$db->addslashes($row->lusername);
							$updated_lines .= "updatevon von: \"".$row_neu->updatevon."\" auf: \"".$row->lusername."\" \n";
							
							$update_count++;
						}
						
						if($updated_lines != '')
							$ausgabe .= "ID ".$row_neu->konto_id.": ".$updated_lines."\n \n";
							$updated_lines ='';
					}
					
					if ($bedingung !='')
					{
						$update .= $bedingung." WHERE konto_id =".$row_neu->konto_id.";";
						//echo ($update);
						$db->db_query($update);
					}
				}
				else
				{
					//Wenn der Eintrag noch nicht vorhanden ist, dann wird er neu angelegt
					$kurzbz = substr($row->beschreibung, 0, 32);
					$insert_qry = 	"INSERT INTO 
									wawi.tbl_konto 
									(konto_id, kontonr, beschreibung, kurzbz, aktiv, insertamum, insertvon, updateamum, updatevon) 
									VALUES (
									".$row->konto.",".$row->kontonr.","."ARRAY[".$db->addslashes($row->beschreibung).", 'EE_".$row->beschreibung."']".",
									".$db->addslashes($kurzbz).", true, ".$db->addslashes($row->cdate).",".$db->addslashes($row->cusername).",
									".$db->addslashes($row->lkontoupdate).",".$db->addslashes($row->lusername).");";
					$insert_count++;
					//echo ($insert_qry);
					if($db->db_query($insert_qry) != true)
						$error_count++;
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

//Sequenz neu setzen
if ($insert_count >0)
{
	$max_qry= "SELECT MAX(konto_id) as max from wawi.tbl_konto";
	if($result_max = $db->db_query($max_qry))
	{
		if($row_max = $db->db_fetch_object($result_max))
		{
			$set_qry ="SELECT setval('wawi.seq_konto_konto_id', $row_max->max)";
			$db->db_query($set_qry);
		}
		else 
		$error_count++;
	}
	else 
	$error_count++;
}

$msg = "
$update_count Datensätze wurden geändert.
$insert_count Datensätze wurden hinzugefügt.
$error_count Fehler sind dabei aufgetreten!

$ausgabe 
";



$msg.=$errormsg;

$mail = new mail(MAIL_ADMIN, 'vilesci.technikum-wien.at', 'WaWi Syncro - Konto', $msg);
if(!$mail->send())
	echo 'Fehler beim Senden des Mails';
else
	echo '<br> Mail verschickt!';
?>			