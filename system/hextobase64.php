<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: Cbristian Paminger <cbristian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 *
 */
/**
 * Dieses Script veraendert die Datenbank, damit die Dokumente (PDFs, Bilder, etc) die in 
 * der Datenbank gespeichert sind, nicht mebr HEX codiert, sondern base64 Codiert sind
 * 
 * !!! Dieses Script muss von der CommandLine gestartet werden da es sonst zu einem Timeout kommt !!!
 */
header('Expires:  -1');
header('Cache-Control: no-store, no-cache, must-revalidate' );
header('Pragma: no-cache' );	
header('Content-Type: text/html;charset=UTF-8');

require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');

$next_row_counter=10;

$db = new basis_db();
if (!$db->db_query('BEGIN'))
	die('Error bei begin');

echo "tbl_akte...".date('Y-m-d H:i:s')."<br>\n";
flush();

//Akte
$qry = "UPDATE public.tbl_akte SET inhalt = encode(decode(inhalt, 'hex'),'base64')";
if(!$db->db_query($qry))
{
	$db->db_query('ROLLBACK');
	die('Fehler:'.$db->db_last_error());
}

echo "<br>\ntbl_akte...".date('Y-m-d H:i:s')." ENDE<br>\n";
flush();
echo "<hr>\n";

//Person
echo "tbl_person...".date('Y-m-d H:i:s')."<br>\n";
flush();

$qry = "UPDATE public.tbl_person SET foto = encode(decode(foto, 'hex'),'base64') WHERE foto is not null";
if(!$db->db_query($qry))
{
	$db->db_query('ROLLBACK');
	die('Fehler:'.$db->db_last_error());
}
echo "<br>\ntbl_person...".date('Y-m-d H:i:s')." ENDE ".$db->db_last_error()."<br>\n";
flush();

echo "<hr>\n";

//Testtool - tbl_frage_sprache.bild
echo "tbl_frage_sprache bild...".date('Y-m-d H:i:s')."<br>\n";
flush();

$qry = "UPDATE testtool.tbl_frage_sprache SET bild = encode(decode(bild, 'hex'),'base64') WHERE bild is not null and bild<>''";
if(!$db->db_query($qry))
{
	$db->db_query('ROLLBACK');
	die('Fehler:'.$db->db_last_error());
}
echo "<br>\ntbl_frage_sprache bild...".date('Y-m-d H:i:s')." ENDE<br>\n";
flush();

//Testtool - tbl_frage_sprache.bild
echo "tbl_frage_sprache audio...".date('Y-m-d H:i:s')."<br>\n";
flush();

$qry = "UPDATE testtool.tbl_frage_sprache SET audio = encode(decode(audio, 'hex'),'base64') WHERE audio is not null AND audio<>''";
if(!$db->db_query($qry))
{
	$db->db_query('ROLLBACK');
	die('Fehler:'.$db->db_last_error());
}
echo "<br>\ntbl_frage_sprache audio...".date('Y-m-d H:i:s')." ENDE<br>\n";
flush();
echo "<hr>\n";

//Testtool - tbl_vorschlag_sprache.bild
echo "testtool.tbl_vorschlag_sprache bild...".date('Y-m-d H:i:s')."<br>\n";
flush();

$qry = "UPDATE testtool.tbl_vorschlag_sprache SET bild = encode(decode(bild, 'hex'),'base64') WHERE bild is not null AND bild<>''";
if(!$db->db_query($qry))
{
	$db->db_query('ROLLBACK');
	die('Fehler:'.$db->db_last_error());
}
echo "<br>\ntesttool.tbl_vorschlag_sprache bild...".date('Y-m-d H:i:s')." ENDE <br>\n";
flush();

echo "<hr>\n";
//Testtool - tbl_vorschlag_sprache.audio
echo "testtool.tbl_vorschlag_sprache audio...".date('Y-m-d H:i:s')."<br>\n";
flush();

$qry = "UPDATE testtool.tbl_vorschlag_sprache SET audio = encode(decode(audio, 'hex'),'base64') WHERE audio is not null AND audio<>''";
if(!$db->db_query($qry))
{
	$db->db_query('ROLLBACK');
	die('Fehler:'.$db->db_last_error());
}
echo "<br>\ntesttool.tbl_vorschlag_sprache audio...".date('Y-m-d H:i:s')." ENDE <br>\n";
flush();

echo "<hr>\n";

//Kommune tbl_wettbewerb
echo "kommune.tbl_wettbewerb icon...".date('Y-m-d H:i:s')."<br>\n";
flush();
$qry = "UPDATE kommune.tbl_wettbewerb SET icon = encode(decode(icon, 'hex'),'base64') WHERE icon is not null AND icon<>''";
if(!$db->db_query($qry))
{
	$db->db_query('ROLLBACK');
	die('Fehler:'.$db->db_last_error());
}
echo "<br>\nkommune.tbl_wettbewerb icon...".date('Y-m-d H:i:s')." ENDE<br>\n";
flush();

echo "<hr>\n";

//Kommune tbl_team
echo "kommune.tbl_team.logo...".date('Y-m-d H:i:s')."<br>\n";
flush();

$qry = "UPDATE kommune.tbl_team SET logo = encode(decode(logo, 'hex'),'base64') WHERE logo is not null AND logo<>''";
if(!$db->db_query($qry))
{
	$db->db_query('ROLLBACK');
	die('Fehler:'.$db->db_last_error());
}
echo "<br>\nkommune.tbl_team logo...".date('Y-m-d H:i:s')." ENDE<br>\n";
flush();

echo "<hr>\n";

//tbl_sprache
echo "public.tbl_sprache flagge ...".date('Y-m-d H:i:s')."<br>\n";
flush();

$qry = "UPDATE public.tbl_sprache SET flagge = encode(decode(flagge, 'hex'),'base64') WHERE flagge is not null AND flagge<>''";
if(!$db->db_query($qry))
{
	$db->db_query('ROLLBACK');
	die('Fehler:'.$db->db_last_error());
}
echo "<br>\npublic.tbl_sprache flagge...".date('Y-m-d H:i:s')." ENDE<br>\n";
flush();

echo "<hr>\n";

//tbl_erhalter
echo "public.tbl_erhalter logo ...".date('Y-m-d H:i:s')."<br>\n";
flush();

$qry = "UPDATE public.tbl_erhalter SET logo = encode(decode(logo, 'hex'),'base64') WHERE logo is not null AND logo<>''";
if(!$db->db_query($qry))
{
	$db->db_query('ROLLBACK');
	die('Fehler:'.$db->db_last_error());
}

echo "<br>\npublic.tbl_erhalter logo...".date('Y-m-d H:i:s')."<br>\n";
flush();

echo "<hr>\n";

$qry = "UPDATE campus.tbl_veranstaltungskategorie SET bild = encode(decode(bild, 'hex'),'base64') WHERE bild is not null AND bild<>''";
if(!$db->db_query($qry))
{
	$db->db_query('ROLLBACK');
	die('Fehler:'.$db->db_last_error());
}
echo "<br>\ncampus.tbl_veranstaltungskategorie bild...".date('Y-m-d H:i:s')." ENDE<br>\n";
flush();

echo "<hr>\n";

if(!$db->db_query('COMMIT'))
	echo "<br>\n".date('Y-m-d H:i:s')." ERROR :".$db->db_last_error()."<br>\n".' line '. __LINE__ .' ; file ' . __FILE__;
else
	echo "<br>\n<b>Aktualisierung abgeschlossen ".date('Y-m-d H:i:s')."</b>"; 
?>