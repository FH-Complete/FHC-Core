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

//Hexcode in String umwandeln
function hexstr($hex)
{
    $string="";
    for ($i=0;$i<strlen($hex)-1;$i+=2)
        $string.=chr(hexdec($hex[$i].$hex[$i+1]));
    return $string;
}

//Hex Dump aus der DB holen
$qry = '';
if(isset($_GET['src']) && $_GET['src']=='person' && isset($_GET['person_id']))
{
	$qry = "SELECT foto FROM public.tbl_person WHERE person_id='".addslashes($_GET['person_id'])."'";
}
else 
	echo 'Unkown type';

if($qry!='')
{
	$db = new basis_db();
	//Header fuer Bild schicken
	header("Content-type: image/gif");
	$db->db_query($qry);
	//HEX Werte in Zeichen umwandeln und ausgeben
	if($row = $db->db_fetch_object())	
		echo hexstr($row->foto);
}
?>