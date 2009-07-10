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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 *
 */
/**
 * Dieses Script veraendert die Datenbank, damit die Dokumente (PDFs, Bilder, etc) die in 
 * der Datenbank gespeichert sind, nicht mehr HEX codiert, sondern base64 Codiert sind
 * 
 * !!! Dieses Script muss von der CommandLine gestartet werden da es sonst zu einem Timeout kommt !!!
 */

require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');

$db = new basis_db();

//Hexcode in String umwandeln
function hexstr($hex)
{
    $string="";
    for ($i=0;$i<mb_strlen($hex)-1;$i+=2)
        $string.=chr(hexdec(mb_substr($hex,$i,1).mb_substr($hex,$i+1,1)));
    
    return $string;
}

function convert($string)
{
	return base64_encode(hexstr($string));
}

$db->db_query('BEGIN');

echo 'tbl_akte...<br>';
flush();
//Akte
$qry = "SELECT akte_id, inhalt FROM public.tbl_akte";
$i=0;
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$i++;
		$qry = "UPDATE public.tbl_akte SET inhalt='".convert($row->inhalt)."' WHERE akte_id='".$row->akte_id."'";
		
		if($i==10)
		{
			echo'<br>';
			$i=0;
		}
		echo $row->akte_id,', ';
		flush();
		
		$db->db_query($qry);		
	}
}

echo 'tbl_person...<br>';
flush();
//Person
$qry = "SELECT foto, person_id FROM public.tbl_person WHERE foto is not null";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$qry = "UPDATE public.tbl_person SET foto='".convert($row->foto)."' WHERE person_id='".$row->person_id."'";
		
		if(!$db->db_query($qry))
		{
			die('Error:'.$db->db_last_error().$qry);
			$db->db_query('ROLLBACK');
		}
	}
}

echo 'tbl_frage_sprache.bild...<br>';
flush();
//Testtool - tbl_frage_sprache.bild
$qry = "SELECT bild, frage_id, sprache FROM testtool.tbl_frage_sprache WHERE bild is not null";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$qry = "UPDATE testtool.tbl_frage_sprache SET bild='".convert($row->bild)."' WHERE frage_id='".$row->frage_id."' AND sprache='".$row->sprache."'";
		
		if(!$db->db_query($qry))
		{
			die('Error:'.$db->db_last_error().$qry);
			$db->db_query('ROLLBACK');
		}
	}
}

echo 'tbl_frage_sprache.audio...<br>';
flush();
//Testtool - tbl_frage_sprache.audio
$qry = "SELECT audio, frage_id, sprache FROM testtool.tbl_frage_sprache WHERE audio is not null";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$qry = "UPDATE testtool.tbl_frage_sprache SET audio='".convert($row->audio)."' WHERE frage_id='".$row->frage_id."' AND sprache='".$row->sprache."'";
		
		if(!$db->db_query($qry))
		{
			die('Error:'.$db->db_last_error().$qry);
			$db->db_query('ROLLBACK');
		}
	}
}

echo 'tbl_vorschlag_sprache.bild...<br>';
flush();
//Testtool - tbl_vorschlag_sprache.bild
$qry = "SELECT bild, frage_id, sprache FROM testtool.tbl_vorschlag_sprache WHERE bild is not null";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$qry = "UPDATE testtool.tbl_vorschlag_sprache SET bild='".convert($row->bild)."' WHERE frage_id='".$row->frage_id."' AND sprache='".$row->sprache."'";
		
		if(!$db->db_query($qry))
		{
			die('Error:'.$db->db_last_error().$qry);
			$db->db_query('ROLLBACK');
		}
	}
}

echo 'tbl_vorschlag_sprache.audio...<br>';
flush();
//Testtool - tbl_vorschlag_sprache.audio
$qry = "SELECT audio, frage_id, sprache FROM testtool.tbl_vorschlag_sprache WHERE audio is not null";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$qry = "UPDATE testtool.tbl_vorschlag_sprache SET audio='".convert($row->audio)."' WHERE frage_id='".$row->frage_id."' AND sprache='".$row->sprache."'";
		
		if(!$db->db_query($qry))
		{
			die('Error:'.$db->db_last_error().$qry);
			$db->db_query('ROLLBACK');
		}
	}
}

echo 'tbl_wettbewerb...<br>';
flush();
//Kommune tbl_wettbewerb
$qry = "SELECT icon, wettbewerb_kurzbz FROM kommune.tbl_wettbewerb WHERE icon is not null";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$qry = "UPDATE kommune.tbl_wettbewerb SET icon='".convert($row->icon)."' WHERE wettbewerb_kurzbz='".$row->wettbewerb_kurzbz."'";
		if(!$db->db_query($qry))
		{
			die('Error:'.$db->db_last_error().$qry);
			$db->db_query('ROLLBACK');
		}
	}
}

echo 'tbl_team...<br>';
flush();
//Kommune tbl_team
$qry = "SELECT logo, team_kurzbz FROM kommune.tbl_team WHERE logo is not null";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$qry = "UPDATE kommune.tbl_team SET logo='".convert($row->logo)."' WHERE team_kurzbz='".$row->team_kurzbz."'";
		if(!$db->db_query($qry))
		{
			die('Error:'.$db->db_last_error().$qry);
			$db->db_query('ROLLBACK');
		}
	}
}

echo 'tbl_sprache...<br>';
flush();
//tbl_sprache
$qry = "SELECT flagge, sprache FROM public.tbl_sprache WHERE flagge is not null";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$qry = "UPDATE public.tbl_sprache SET flagge='".convert($row->flagge)."' WHERE sprache='".$row->sprache."'";
		if(!$db->db_query($qry))
		{
			die('Error:'.$db->db_last_error().$qry);
			$db->db_query('ROLLBACK');
		}
	}
}

echo 'tbl_erhalter...<br>';
flush();
//tbl_erhalter
$qry = "SELECT logo, erhalter_kz FROM public.tbl_erhalter WHERE logo is not null";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$qry = "UPDATE public.tbl_erhalter SET logo='".convert($row->logo)."' WHERE erhalter_kz='".$row->erhalter_kz."'";
		if(!$db->db_query($qry))
		{
			die('Error:'.$db->db_last_error().$qry);
			$db->db_query('ROLLBACK');
		}
	}
}

if(!$db->db_query('COMMIT'))
	echo 'ERROR:'.$db->db_last_error();
else
	echo '<b>Aktualisierung abgeschlossen</b>';
?>