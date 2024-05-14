<?php
/* Copyright (C) 2015 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */
/**
 * Die Dokumente werden entweder base64 kodiert in der Datenbank in der Spalte inhalt gespeichert
 * oder im Filesystem, in diesem Fall ist die Akte mit einer DMS ID verknuepft in welcher der Dateiname steht.
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/dms.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/functions.inc.php');
require_once('../include/notiz.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('mitarbeiter'))
	die('Keine Berechtigung');

if(isset($_GET['id']) && is_numeric($_GET['id']))
{
	$notiz = new notiz();
	if(!$notiz->isNotizDokument($_GET['id']))
		die('Dokument wurde nicht gefunden oder haengt nicht an einer Notiz');

	$dms = new dms();
    if(!$dms->load($_GET['id']))
        die('Kein Dokument vorhanden');

    $filename=DMS_PATH.$dms->filename;

    if(!isset($_GET['notimeupdate']))
        $dms->touch($dms->dms_id, $dms->version);

    if(file_exists($filename))
    {
        if($handle = fopen($filename,"r"))
        {
            if($dms->mimetype=='')
                $dms->mimetype='application/octetstream';

            header('Content-type: '.$dms->mimetype);
            header('Content-Disposition: inline; filename="'.$dms->name.'"');
            header('Content-Length: ' .filesize($filename));

            while (!feof($handle))
            {
                echo fread($handle, 8192);
            }
            fclose($handle);
        }
        else
            echo 'Fehler: Datei konnte nicht geoeffnet werden';
    }
    else
        echo 'Die Datei existiert nicht';
}
else
{
    echo "Ungueltige DMS-ID";
}

?>
