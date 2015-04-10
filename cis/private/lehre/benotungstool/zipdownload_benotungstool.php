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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Erstellt ein Zip Archiv des Download-Bereichs und leitet dann zum Download weiter
 * @create 20-03-2006
 * Aufruf: zipdownload.php?stg=255&sem=1$short=eng
 */
require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');

$user = get_uid(); 

if(!check_lektor($user))
	die('Sie haben keine Berechtigung fuer diese Seite');

//Gueltigkeit der Parameter pruefen		
if(!isset($_GET['uebung_id']) || !is_numeric($_GET['uebung_id']))
{
	die('Fehler bei der Parameteruebergabe');
}
if(!isset($_GET['lehreinheit_id']) || !is_numeric($_GET['lehreinheit_id']))
{
	die('Fehler bei der Parameteruebergabe');
}
if(!isset($_GET['stsem']))
{
	die('Fehler bei der Parameteruebergabe');
}
if(!isset($_GET['downloadname']))
{
	die('Fehler bei der Parameteruebergabe');
}

$uebung_id   = $_GET['uebung_id'];
$lehreinheit_id   = $_GET['lehreinheit_id'];
$stsem   = $_GET['stsem'];
$downloadname   = $_GET['downloadname'];

if(mb_strstr($downloadname,'..'))
	die('Ungueltiger Parameter gefunden');

//Pfade bauen
$pfad = BENOTUNGSTOOL_PATH.'abgabe/';
$filename = 'download_'.$user.'_'.$downloadname.'.zip';

if(!check_filename($filename))
	die('Ungueltiger Parameter gefunden');

//Pfad wechseln
chdir($pfad);

//File loeschen falls es existiert
//if(file_exists("download_".$user."*"))
exec('rm download_'.$user.'*');
	
//Zip File erstellen
exec("zip -r ".escapeshellarg($filename).' *_[WS]S[0-9][0-9][0-9][0-9]_'.$uebung_id.'_*');

//Auf Zip File Verweisen
//header("Location: $pfad$filename");
header('Content-Type: application/octet-stream');
header('Content-disposition: attachment; filename="'.$filename.'"');
readfile($filename);
unlink($filename);
?>
