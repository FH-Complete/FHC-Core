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
/**
 * Erstellt ein Zip Archiv des Download-Bereichs und leitet dann zum Download weiter
 * @create 20-03-2006
 * Aufruf: zipdownload.php?stg=255&sem=1$short=eng
 */

   require_once('../../config.inc.php');
   require_once('../../../include/studiengang.class.php');
   require_once('../../../include/functions.inc.php');
      
   //Connection zur DB herstellen
   if(!$conn = pg_pconnect(CONN_STRING))
   {
   		writeCISlog('STOP');
   		die("Fehler beim herstellen der DB Verbindung");
   }
   
   //Gueltigkeit der Parameter pruefen		
   if(!isset($_GET['stg']) || !is_numeric($_GET['stg']))
   {
   		writeCISlog('STOP');
   		die("Fehler bei der Parameteruebergabe");
   }
   
   if(!isset($_GET['sem']) || !is_numeric($_GET['sem']))
   {
   		writeCISlog('STOP');
   		die("Fehler bei der Parameteruebergabe");
   }
   		
   if(!isset($_GET['short']) || strstr("..",$_GET['short']))
   {
   		writeCISlog('STOP');
   		die("Fehler bei der Parameteruebergabe");
   }
   
   $stg   = $_GET['stg'];
   $sem   = $_GET['sem'];
   $short = $_GET['short'];
   
   //Studiengangskuerzel holen
   $stg_obj = new studiengang($conn);
   $stg_obj->load($stg);

   $kurzbz = strtolower($stg_obj->kurzbz);

   //Pfade bauen
   $pfad = '../../../documents/'.$kurzbz.'/'.$sem.'/'.$short.'/download/';
   $filename = $kurzbz.'_'.$sem.'_'.$short.'_download.zip';
   $pfad2 = '../../../documents/'.$kurzbz.'/'.$sem.'/'.$short.'/';
   
   //Pfad wechseln
   chdir($pfad);
   
   //File loeschen falls es existiert
   if(file_exists($filename))
   		exec("rm $filename");
   		
   //Zip File erstellen
   exec("zip -r ../".$filename." ./*");

   //Auf Zip File Verweisen
   header("Location: $pfad2$filename");
?>