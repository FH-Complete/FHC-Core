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
require_once('../config/vilesci.config.inc.php');
require_once('../include/akte.class.php');
require_once('../include/dms.class.php'); 

//base64 Dump aus der DB holen
if(isset($_GET['id']) && is_numeric($_GET['id']))
{
	$akte = new akte($_GET['id']);

	//Header fuer Bild schicken
	header("Content-type: $akte->mimetype");
	header('Content-Disposition: attachment; filename="'.$akte->titel.'"');
	echo base64_decode($akte->inhalt);
}

if(isset($_GET['akte_id']) && is_numeric($_GET['akte_id']))
{
	$akte = new akte(); 
	if(!$akte->load($_GET['akte_id']))
		die('Fehler beim Laden der Akte'); 
	
	$dms = new dms(); 
	if(!$dms->load($akte->dms_id))
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

if(isset($_GET['person_id']) && isset($_GET['dokument_kurzbz']))
{
    $akte = new akte(); 
    if(!$akte->getAkten($_GET['person_id'], $_GET['dokument_kurzbz']))
        die('Konnte Dokument nicht laden'); 
    
    
    
    // erste Akte
    //$akten = $akte->result[0]; 
    if(isset($akte->result[0]))
    {
       $akten = $akte->result[0]; 
       $titel = ($akten->titel == '')?$akten->dokument_kurzbz:$akten->titel; 
    }
    else
        die('Kein Dokument vorhanden'); 

    $dms= new dms(); 
    if(!$dms->load($akten->dms_id))
        die('Kein Dokument vorhanden'); 
    // dms anzeigen

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
    
	//echo base64_decode($akten->inhalt);
}
?>