<?php
/* Copyright (C) 2004 Technikum-Wien
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
/*
 * Generiert eine Anwesenheitsliste mit Fotos
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/datum.class.php');
require_once('../../include/studiengang.class.php');

if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$stg_obj = new studiengang($conn);
$stg_obj->getAll('typ, kurzbzlang', false);
	
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
  
if(isset($_GET['prestudent_id']))
{
	$ids = explode(';',$_GET['prestudent_id']);
	$idstring='';
	
	foreach ($ids as $id) 
	{
		if($idstring!='')
			$idstring.=',';
		$idstring.="'$id'";	
	}
	$qry = "SELECT * FROM (SELECT distinct on(person_id) foto, vorname, nachname, person_id, prestudent_id, tbl_prestudent.studiengang_kz, semester, verband, gruppe FROM public.tbl_person JOIN public.tbl_prestudent USING(person_id) LEFT JOIN public.tbl_student USING(prestudent_id) WHERE prestudent_id in($idstring)) foo ORDER BY nachname, vorname";
}
else
{
	$qry = "SELECT * FROM (SELECT 
			distinct on(person_id) foto, vorname, nachname, person_id, tbl_studentlehrverband.studiengang_kz, tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe
		FROM 
			campus.vw_student_lehrveranstaltung JOIN public.tbl_benutzer USING(uid) 
			JOIN public.tbl_person USING(person_id) JOIN public.tbl_student ON(uid=student_uid) 
			LEFT JOIN public.tbl_studentlehrverband USING(student_uid)
		WHERE 
			lehrveranstaltung_id='".addslashes($lvid)."' AND 
			vw_student_lehrveranstaltung.studiensemester_kurzbz='".addslashes($stsem)."' AND
			tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."'";

	if($lehreinheit_id!='')
		$qry.=" AND lehreinheit_id='".addslashes($lehreinheit_id)."'";
		
	$qry.=' ORDER BY person_id) foo ORDER BY nachname, vorname';
}

echo '<html><head>
<title>Anwesenheitsliste</title>
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head><body>';

if($result = pg_query($conn, $qry))
{
	echo '<table>';
	echo '<tr class="liste"><th>Foto</th><th>Nachname</th><th>Vorname</th><th>Gruppe</th></tr>';
	$i=0;
	while($row = pg_fetch_object($result))
	{
		$i++;
		echo '<tr class="liste'.($i%2).'">';
		if($row->foto!='')
			echo "<td><img src='../bild.php?src=person&person_id=$row->person_id'></td>";
		else 
			echo "<td></td>";
		echo "<td>$row->nachname</td>";
		echo "<td>$row->vorname</td>";	
		echo "<td>".$stg_obj->kuerzel_arr[$row->studiengang_kz]."-$row->semester$row->verband$row->gruppe</td>";	
		
		echo '</tr>';
	}
	echo '</table>';
}

echo '</body></html>';
?>