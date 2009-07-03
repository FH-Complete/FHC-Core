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

//Hexcode in String umwandeln
function hexstr($hex)
{
    $string="";
    for ($i=0;$i<strlen($hex)-1;$i+=2)
        $string.=chr(hexdec($hex[$i].$hex[$i+1]));
    return $string;
}

//default bild (ein weisser pixel)
$cTmpHEX='ffd8ffe000104a46494600010101004800480000ffe100164578696600004d4d002a00000008000000000000fffe0017437265617465642077697468205468652047494d50ffdb0043000503040404030504040405050506070c08070707070f0b0b090c110f1212110f111113161c1713141a1511111821181a1d1d1f1f1f13172224221e241c1e1f1effdb0043010505050706070e08080e1e1411141e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1e1effc00011080001000103012200021101031101ffc4001500010100000000000000000000000000000008ffc40014100100000000000000000000000000000000ffc40014010100000000000000000000000000000000ffc40014110100000000000000000000000000000000ffda000c03010002110311003f00b2c007ffd9';
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
		
//die bilder werden, sofern es funktioniert, in jpg umgewandelt da es sonst zu fehlern beim erstellen
//von pdfs kommen kann.
$im = @imagecreatefromstring(hexstr($cTmpHEX));
if($im!==false)
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
	echo hexstr($cTmpHEX);
}
?>