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
    *
    * Erstellt eine Anwesenheitsliste mit Bildern im HTML Format
    *
    */

   require_once('../../config.inc.php');
   require_once('../../../include/person.class.php');
   require_once('../../../include/studiengang.class.php');
   require_once('../../../include/studiensemester.class.php');
   require_once('../../../include/lehrveranstaltung.class.php');
   error_reporting(E_ALL);
   ini_set('display_errors','1');
   
   if(!$conn=pg_pconnect(CONN_STRING))
      die('Fehler beim Herstellen der Datenbankverbindung');
   
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

   echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
   <html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<body>
';
   
$stgobj=new studiengang($conn);
$stgobj->load($stg);
//Logo
echo "<table width='100%'>
	<tr>
		<td>";
$lvobj = new lehrveranstaltung($conn, $lvid);

echo '<span style="font-size:17px; font-weight:bold;">Anwesenheitsliste '.$lvobj->bezeichnung.'</span>';

$qry = "SELECT distinct on(kuerzel, semester, verband, gruppe, gruppe_kurzbz) UPPER(stg_typ::varchar(1) || stg_kurzbz) as kuerzel, semester, verband, gruppe, gruppe_kurzbz from campus.vw_lehreinheit WHERE lehrveranstaltung_id='".addslashes($lvid)."' AND studiensemester_kurzbz='".addslashes($stsem)."'";
if($lehreinheit_id!='')
	$qry.=" AND lehreinheit_id='".addslashes($lehreinheit_id)."'";
	
$gruppen='';
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($gruppen!='')
			$gruppen.=', ';
		if($row->gruppe_kurzbz=='')
			$gruppen.=trim($row->kuerzel.'-'.$row->semester.$row->verband.$row->gruppe);
		else
			$gruppen.=$row->gruppe_kurzbz;
	}
}

echo "<br>Gruppe: $gruppen";
echo "<br>Studiensemester: $stsem";
		
echo "
		</td>
		<td align='right'><img src='../../../skin/images/logo.jpg' width='130px'></td>
	</tr>
	</table>";


//Studenten holen

echo '<br><br>
<table border=1>
	<thead>
		<tr><th>Hörer/Name</th><th>Kennzeichen</th><th>Gruppe</th><th>Foto</th></tr>
	</thead>
	<tbody>';

$stsem_obj = new studiensemester($conn);
$stsem_obj->load($stsem);
$stsemdatumvon = $stsem_obj->start;
$stsemdatumbis = $stsem_obj->ende;
$qry = "SELECT 
			distinct on(nachname, vorname, person_id) vorname, nachname, matrikelnr, person_id,
			tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
			(SELECT status_kurzbz FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_student.prestudent_id ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as status,
			tbl_bisio.bisio_id, tbl_bisio.bis, tbl_bisio.von,
			tbl_zeugnisnote.note 
		FROM 
			campus.vw_student_lehrveranstaltung JOIN public.tbl_benutzer USING(uid) 
			JOIN public.tbl_person USING(person_id) JOIN public.tbl_student ON(uid=student_uid) 
			LEFT JOIN public.tbl_studentlehrverband USING(student_uid,studiensemester_kurzbz)
			LEFT JOIN lehre.tbl_zeugnisnote on(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id AND tbl_zeugnisnote.student_uid=tbl_student.student_uid AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz)
			LEFT JOIN bis.tbl_bisio ON(uid=tbl_bisio.student_uid)
		WHERE 
			vw_student_lehrveranstaltung.lehrveranstaltung_id='".addslashes($lvid)."' AND 
			vw_student_lehrveranstaltung.studiensemester_kurzbz='".addslashes($stsem)."'";

if($lehreinheit_id!='')
	$qry.=" AND vw_student_lehrveranstaltung.lehreinheit_id='".addslashes($lehreinheit_id)."'";
	
$qry.=' ORDER BY nachname, vorname, person_id, tbl_bisio.bis DESC';

if($result = pg_query($conn, $qry))
{
	$i=0;
	while($elem = pg_fetch_object($result))
	{
		$i++;
		echo '<tr class="liste'.($i%2).'">';
		//Abbrecher und Unterbrecher nicht anzeigen
		if($elem->status!='Abbrecher' && $elem->status!='Unterbrecher')
		{
			if($elem->status=='Incoming')
				$inc=' (i)';
			else 
				$inc='';
				
			if($elem->bisio_id!='' && $elem->status!='Incoming' && ($elem->bis > $stsemdatumvon || $elem->bis=='') && $elem->von < $stsemdatumbis) //Outgoing
				$inc.=' (o)';
				
			if($elem->note==6) //angerechnet
				$inc.=' (ar)';
				
			echo "<td>$elem->nachname $elem->vorname</td>";
			echo "<td>".trim($elem->matrikelnr)."</td>";
			echo '<td>'.$elem->semester.$elem->verband.$elem->gruppe.'</td>';
			echo "<td><img src='".APP_ROOT."cis/public/bild.php?src=person&person_id=$elem->person_id' height='100px'></td>";
		}
		echo '</tr>';
   }
}
echo '</tbody></table><br><br>
(i) ... Incoming<br>
(o)  ... Outgoing<br>
(ar) ... angerechnet<br><br>
Fachhochschulstudiengang ('.strtoupper($stgobj->typ).') '.$stgobj->bezeichnung;

echo '</body>';
echo '</html>';
?>