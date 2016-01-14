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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
   /**
    * anwesenheitsliste.pdf.php
    *
    * Erstellt eine Anwesenheitsliste im PDF-Format
    *
    */

 	require_once('../../../config/cis.config.inc.php');
// ------------------------------------------------------------------------------------------
//	Datenbankanbindung
// ------------------------------------------------------------------------------------------
	require_once('../../../include/basis_db.class.php');
	if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');

   // Pfad zu fpdf
   define('FPDF_FONTPATH','../../../include/pdf/font/');
   // library einbinden
   require_once('../../../include/pdf/fpdf.php');

   require_once('../../../include/person.class.php');
   require_once('../../../include/studiengang.class.php');
   require_once('../../../include/studiensemester.class.php');
   require_once('../../../include/lehrveranstaltung.class.php');
   require_once('../../../include/pdf.inc.php');

   //Uebergabeparameter abpruefen
   if(isset($_GET['stg'])) //Studiengang
   {
   	  if(is_numeric($_GET['stg']))
      	$stg=$_GET['stg'];
      else
      	die('Fehler bei der Parameteruebergabe');
   }
   else
   		$stg='';
   if(isset($_GET['sem'])) //Semester
   {
   	  if(is_numeric($_GET['sem']))
   	  	$sem=$_GET['sem'];
   	  else
   	  	die('Fehler bei der Parameteruebergabe');
   }
   else
   		$sem='';

   if(isset($_GET['verband'])) //Verband
      $verband=$_GET['verband'];
   else
      $verband='';
   if(isset($_GET['gruppe'])) //Gruppe
      $gruppe=$_GET['gruppe'];
   else
	  $gruppe='';
   if(isset($_GET['gruppe_kurzbz'])) //Einheit
      $gruppe_kurzbz = $_GET['gruppe_kurzbz'];
   else
      $gruppe_kurzbz='';

   if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
   		$lvid = $_GET['lvid'];
   	else
   		die('Fehler bei der Parameteruebergabe');

   	if(isset($_GET['stsem']))
   		$stsem = $_GET['stsem'];
   	else
   		die('Studiensemester wurde nicht uebergeben');

   $lehreinheit_id = (isset($_GET['lehreinheit_id'])?$_GET['lehreinheit_id']:'');
/**
 * liefert den groesseren der beiden werte
 *
 */
function getmax($val1,$val2)
{
	return ($val1>$val2)?$val1:$val2;

}
require_once('../../../include/'.EXT_FKT_PATH.'/anwesenheitsliste_bilder.inc.php');
?>
