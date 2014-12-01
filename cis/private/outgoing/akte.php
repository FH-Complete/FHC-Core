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
// Holt den Hexcode eines Aktes aus der DB wandelt es in Zeichen
// um und gibt das Dokument zurueck.

require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/akte.class.php');
require_once('../../../include/benutzer.class.php');


$uid = get_uid();

$benutzer = new benutzer();
if(!$benutzer->load($uid))
	die('Benutzer nicht gefunden');

//base64 Dump aus der DB holen
if(isset($_GET['id']) && is_numeric($_GET['id']))
{
	$akte = new akte($_GET['id']);
	if($akte->person_id!=$benutzer->person_id)
		die('Sie haben keine Berechtigung fuer diese Datei');
	
	//Header fuer Bild schicken
	header("Content-type: $akte->mimetype");
	header('Content-Disposition: attachment; filename="'.$akte->titel.'"');
	echo base64_decode($akte->inhalt);
}
else 
	echo 'Unkown type';

?>
