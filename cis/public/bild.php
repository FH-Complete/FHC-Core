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
// Holt ein Bildes aus der DB wandelt es
// um und gibt das ein Bild zurueck.
// Aufruf mit <img src='bild.php?src=person&person_id=1>
require_once('../../config/cis.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/benutzer.class.php');

session_start();

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

//Wenn das Bild direkt aufgerufen wird, ist eine Authentifizierung erforderlich
//Wenn es vom Server selbst aufgerufen wird, ist keine Auth. notwendig 
//(z.B. fuer die Erstellung von PDFs)
if($_SERVER['REMOTE_ADDR']!=$_SERVER['SERVER_ADDR'])
{
    // wenn session gesetzt ist von Prestudententool -> keine Abfrage da diese Studenten noch keine uid haben
    if(!isset($_SESSION['prestudent/user']))
       $uid = get_uid();
}
  
//default bild (ein weisser pixel)
$cTmpHEX='/9j/4AAQSkZJRgABAQEASABIAAD/4QAWRXhpZgAATU0AKgAAAAgAAAAAAAD//gAXQ3JlYXRlZCB3aXRoIFRoZSBHSU1Q/9sAQwAFAwQEBAMFBAQEBQUFBgcMCAcHBwcPCwsJDBEPEhIRDxERExYcFxMUGhURERghGBodHR8fHxMXIiQiHiQcHh8e/9sAQwEFBQUHBgcOCAgOHhQRFB4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4e/8AAEQgAAQABAwEiAAIRAQMRAf/EABUAAQEAAAAAAAAAAAAAAAAAAAAI/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCywAf/2Q==';
//Hex Dump aus der DB holen
if(isset($_GET['src']) && $_GET['src']=='person' && isset($_GET['person_id'])  && is_numeric($_GET['person_id']))
{
	$qry = "SELECT tbl_akte.inhalt as foto, tbl_person.foto_sperre FROM public.tbl_akte JOIN public.tbl_person USING(person_id) WHERE tbl_akte.person_id='".addslashes($_GET['person_id'])."' AND dokument_kurzbz='Lichtbil'";
	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			$gesperrt=false;
			
			//Schauen ob eine Foto Sperre existiert
			if($db->db_parse_bool($row->foto_sperre))
			{
				$gesperrt=true;
				
				//Wenn der User selbst darauf zugreift darf er das Bild sehen
				$benutzer = new benutzer();
				$benutzer->load($uid);
				if($benutzer->person_id==$_GET['person_id'])
					$gesperrt=false;
			}
				
			if($row->foto!='' && !$gesperrt)
		  		$cTmpHEX=$row->foto;
		}
	}
}
		
//die bilder werden, sofern es funktioniert, in jpg umgewandelt da es sonst zu fehlern beim erstellen
//von pdfs kommen kann.
$im = @imagecreatefromstring(base64_decode($cTmpHEX));
if($im!=false)
{  
  @ob_clean();
	header("Content-type: image/jpeg");
	exit(imagejpeg($im));
}
else
{
	//bei manchen Bildern funktioniert die konvertierung nicht
	//diese werden dann einfach so angezeigt.
	@ob_clean();
	header("Content-type: image/gif");
	exit(base64_decode($cTmpHEX));
}
?>