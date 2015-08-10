<?php
/* Copyright (C) 2006 Technikum-Wien
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
// Holt den Hexcode eines Bildes aus der DB wandelt es in Zeichen
// um und gibt das ein Bild zurueck.
// Aufruf mit <img src='bild.php?src=frage&frage_id=1
require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/dms.class.php');

$db = new basis_db();
//base64 Dump aus der DB holen
$cTmpHEX='/9j/4AAQSkZJRgABAQEASABIAAD/4QAWRXhpZgAATU0AKgAAAAgAAAAAAAD//gAXQ3JlYXRlZCB3aXRoIFRoZSBHSU1Q/9sAQwAFAwQEBAMFBAQEBQUFBgcMCAcHBwcPCwsJDBEPEhIRDxERExYcFxMUGhURERghGBodHR8fHxMXIiQiHiQcHh8e/9sAQwEFBQUHBgcOCAgOHhQRFB4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4e/8AAEQgAAQABAwEiAAIRAQMRAf/EABUAAQEAAAAAAAAAAAAAAAAAAAAI/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCywAf/2Q==';
if(isset($_GET['src']) && $_GET['src']=='person' && isset($_GET['person_id']))
{
	$qry = "SELECT foto FROM public.tbl_person WHERE person_id=".$db->db_add_param($_GET['person_id'], FHC_INTEGER);
	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
			$cTmpHEX=base64_decode($row->foto);
	}
}
elseif(isset($_GET['src']) && $_GET['src']=='akte' && isset($_GET['person_id']))
{
	$qry = "SELECT inhalt as foto, dms_id FROM public.tbl_akte WHERE person_id=".$db->db_add_param($_GET['person_id'], FHC_INTEGER)." AND dokument_kurzbz='Lichtbil'";
	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			if($row->foto!='')
				$cTmpHEX=base64_decode($row->foto);
			elseif($row->dms_id!='')
			{
				// Wenn das Foto nicht im Inhalt steht wird aus aus dem DMS geladen
				$dms = new dms();
				if(!$dms->load($row->dms_id))
					die('Kein Dokument vorhanden');

				$filename=DMS_PATH.$dms->filename;

				$dms->touch($dms->dms_id, $dms->version);

				if(file_exists($filename))
				{
					if($handle = fopen($filename,"r"))
					{
						$cTmpHEX='';
						while (!feof($handle))
						{
							$cTmpHEX.= fread($handle, 8192);
						}
						fclose($handle);
					}
					else
						echo 'Fehler: Datei konnte nicht geoeffnet werden';
				}
				else
					echo 'Die Datei existiert nicht';
			}
		}
	}
}

//Header fuer Bild schicken
header("Content-type: image/gif");
//base64 Werte in Zeichen umwandeln und ausgeben
exit($cTmpHEX);
?>
