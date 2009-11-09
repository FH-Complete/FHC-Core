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
// Aufruf mit <img src='bild.php?src=person&person_id=1>
  require_once('../../config/cis.config.inc.php');
  require_once('../../include/basis_db.class.php');
  if (!$db = new basis_db())
  		die('Fehler beim Oeffnen der Datenbankverbindung');

//default bild (ein weisser pixel)
$cTmpHEX='/9j/4AAQSkZJRgABAQEASABIAAD/4QAWRXhpZgAATU0AKgAAAAgAAAAAAAD//gAXQ3JlYXRlZCB3aXRoIFRoZSBHSU1Q/9sAQwAFAwQEBAMFBAQEBQUFBgcMCAcHBwcPCwsJDBEPEhIRDxERExYcFxMUGhURERghGBodHR8fHxMXIiQiHiQcHh8e/9sAQwEFBQUHBgcOCAgOHhQRFB4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4e/8AAEQgAAQABAwEiAAIRAQMRAf/EABUAAQEAAAAAAAAAAAAAAAAAAAAI/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCywAf/2Q==';
//Hex Dump aus der DB holen
if(isset($_GET['src']) && $_GET['src']=='person' && isset($_GET['person_id']))
{
	//$qry = "SELECT foto FROM public.tbl_person WHERE person_id='".addslashes($_GET['person_id'])."'";
	$qry = "SELECT inhalt as foto FROM public.tbl_akte WHERE person_id='".addslashes($_GET['person_id'])."' AND dokument_kurzbz='Lichtbil'";
	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			if($row->foto!='')
		  		$cTmpHEX=$row->foto;
		}
	}
}
#exit($cTmpHEX);
		
//die bilder werden, sofern es funktioniert, in jpg umgewandelt da es sonst zu fehlern beim erstellen
//von pdfs kommen kann.
$im = @imagecreatefromstring(base64_decode($cTmpHEX));
if($im!=false)
{
	
  exit('dfsd');
  
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