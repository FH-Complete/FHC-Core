<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once('../config/cis.config.inc.php');
require_once('../include/dms.class.php');

if(!isset($_GET['id']))
	die('ID muss uebergeben werden');

//if(!isset($_GET['version']))
//	die('Version muss uebergeben werden'); 
	
$id = $_GET['id'];
$version = isset($_GET['version'])?$_GET['version']:null; 

if(!is_numeric($id))
	die('ID ist ungueltig');
	
if($version!='' && !is_numeric($version))
	die('Version ist ungueltig'); 
	
$doc = new dms();
if(!$doc->load($id,$version))
	die('Dieses Dokument existiert nicht mehr');

if(!isset($_GET['notimeupdate']))
	$doc->touch($doc->dms_id, $doc->version);
	
$filename = DMS_PATH.$doc->filename;
if(file_exists($filename))
{
	if($handle = fopen($filename,"r"))
	{
		header('Content-type: '.$doc->mimetype);
		header('Content-Disposition: inline; filename="'.$doc->name.'"');
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
?>