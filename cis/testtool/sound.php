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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
// Holt den Hexcode eines Bildes aus der DB wandelt es in Zeichen
// um und gibt das ein Bild zurueck.
// Aufruf mit <img src='bild.php?src=frage&frage_id=1
require_once('../../config/cis.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

session_start();
if(!isset($_SESSION['pruefling_id']))
{
	$user = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if (!$rechte->isBerechtigt('basis/testtool', null, 's'))
		die($rechte->errormsg);
}

//base64 Dump aus der DB holen
$qry = '';
if(isset($_GET['src']) && $_GET['src']=='frage' && isset($_GET['frage_id']))
{
	$qry = "
	SELECT
		audio
	FROM
		testtool.tbl_frage_sprache
	WHERE
		frage_id=".$db->db_add_param($_GET['frage_id'], FHC_INTEGER)."
		AND sprache=".$db->db_add_param($_GET['sprache']);
}
elseif(isset($_GET['src']) && $_GET['src']=='vorschlag' && isset($_GET['vorschlag_id']))
{
	$qry = "
	SELECT
		audio
	FROM
		testtool.tbl_vorschlag_sprache
	WHERE
		vorschlag_id=".$db->db_add_param($_GET['vorschlag_id'], FHC_INTEGER)."
		AND sprache=".$db->db_add_param($_GET['sprache']);
}
else
	echo 'Unkown type';

if($qry!='')
{
	//Header fuer Sound schicken
	header("Content-type: audio/ogg");
	//Caching unterbinden
	header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
	header("Pragma: no-cache"); // HTTP 1.0.
	header("Expires: 43200"); // Proxies.
	//base64 Werte in Zeichen
	$result = $db->db_query($qry);
	$row = $db->db_fetch_object($result);

	$content = base64_decode($row->audio);

	$len = strlen($content);
	header("Content-Length: $len\n");
	//header("Content-type: audio/wav");

	//ausgeben
	echo $content;
}
?>
