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
/****************************************************************************
 * Script: 			stpl_kalender.php
 * Descr:  			Das Script dient zum Darstellung des Stundenplans
 *					als Kalender ueber das gesamte Semester
 * Verzweigungen: 	von stpl_week.php
 * Author: 			Christian Paminger
 * Erstellt: 		21.9.2001 von Christian Paminger
 * Update: 			10.9.2005 von Christian Paminger
 *****************************************************************************/

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/wochenplan.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/studiensemester.class.php');

if(!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

//Startwerte setzen
if(!isset($_GET['db_stpl_table']))
	$db_stpl_table='stundenplan';
else
	$db_stpl_table=$_GET['db_stpl_table'];
	
if(!in_array($db_stpl_table,array('stundenplan','stundenplandev')))
	die('db_stpl_table invalid');
if(isset($_GET['type']))
	$type=$_GET['type'];
if(isset($_GET['pers_uid']))
	$pers_uid=$_GET['pers_uid'];
$ort_kurzbz=(isset($_GET['ort_kurzbz'])?$_GET['ort_kurzbz']:'');
$ort_kurzbz=(isset($_GET['ort'])?$_GET['ort']:$ort_kurzbz);
$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:'');
$sem=(isset($_GET['sem'])?$_GET['sem']:'');
$ver=(isset($_GET['ver'])?$_GET['ver']:'');
$grp=(isset($_GET['grp'])?$_GET['grp']:'');
$gruppe_kurzbz=(isset($_GET['einheit'])?$_GET['einheit']:'');
$gruppe_kurzbz=(isset($_GET['gruppe'])?$_GET['gruppe']:$gruppe_kurzbz);
if (isset($_GET['begin']))
	$begin=$_GET['begin'];
if (isset($_GET['ende']))
	$ende=$_GET['ende'];
if (isset($_GET['format']))
	$format=$_GET['format'];
else
	$format='HTML';
$version=(isset($_GET['version'])?$_GET['version']:2);
$target=(isset($_GET['target'])?$_GET['target']:null);

// UID bestimmen
$uid = get_uid();

// Beginn Ende setzen
if(!isset($begin))
{
	$objSS=new studiensemester();
	$ss = $objSS->getaktorNext();
	$objSS->load($ss);
	$begin = datum::mktime_fromdate($objSS->start);
	$ende = datum::mktime_fromdate($objSS->ende);
}



// for spezial friends
if($uid=='maderdon')
{
	if(!isset($_GET['format']))
	{
		$format='ical';
		$version=2;
		$target='ical';
		$begin=1188597600;
		$ende=1202166000;
	}
}

$jahr=date("Y",$begin);
$mon=date("m",$begin);
$name='FH-Kalender_'.$mon.'_'.$jahr;
if(isset($target))
	$name.='_'.$target;

// doing some DOS-CRLF magic...
$crlf=crlf();

// Check Type
// Print in csv-file
if($format=='csv')
{
	$name.='.csv';
	header("Content-disposition: filename=$name");
	header("Content-type: application/ms-excel");
	header("Pragma: public");
	header("Expires: 0");
	if ($target=='outlook')
		echo '"Betreff","Beginnt am","Beginnt um","Endet am","Endet um","Ganztaegiges Ereignis","Erinnerung Ein/Aus","Erinnerung am","Erinnerung um","Besprechungsplanung","Erforderliche Teilnehmer","Optionale Teilnehmer","Besprechungsressourcen","Abrechnungsinformationen","Beschreibung","Kategorien","Ort","Priorit?t","Privat","Reisekilometer","Vertraulichkeit","Zeitspanne zeigen als"';
	else
		echo '"title","category","location","description","keywords","start_date","start_time","end_date","end_time","alarm","recur_type","recur_end_date","recur_interval","recur_data"';
}
// Print in ical-file - MR
elseif($format=='ical')
{
	$name.='.ics';
	header("Content-disposition: filename=$name");
	header("Content-type: text/calendar");
	header("Pragma: public");
	header("Expires: 0");
	echo 'BEGIN:VCALENDAR'.$crlf.'VERSION:'.$version.'.0'.$crlf.'PRODID:'.CAMPUS_NAME;
}
// Print in HTML-File
else
{
	echo '<html>';
	echo '<head>';
	echo '<title>Kalender</title>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
	echo '<link rel="stylesheet" href="../../../skin/cis.css" type="text/css">';
	echo '<link rel="stylesheet" type="text/css" media="print" href="../../../skin/print.css" />';
	echo '</head>';
	echo '<body id="inhalt">';
}

if(!isset($begin) || !isset($ende))
{
	// datum holen falls nicht gesetzt
	if (!isset($_GET['semesterplan']))
	{
		die("Datum ist nicht gesetzt!");
	}
	else
	{
   		$query="SELECT start,ende FROM campus.tbl_studiensemester WHERE studiensemester_kurzbz=(SELECT wert FROM public.tbl_variable WHERE name='semester_aktuell' AND uid='$uid');";
		if(!$result_semester=$db->db_query($query))
		    die($db->db_last_error());

    	if($db->db_num_rows($result_semester)>0)
		{
			$begin=strtotime($db->db_result($result_semester,0,'start'));
			$ende=strtotime($db->db_result($result_semester,0,'ende'));
		}
		else
		{
			die('Studiensemester konnte nicht gefunden werden!');
		}
		$result_semester=$db->db_query("SELECT wert FROM public.tbl_variable WHERE uid='$uid' AND name='db_stpl_table';");
		if($db->db_num_rows($result_semester)>0)
			$db_stpl_table=$db->db_result($result_semester,0,'wert');
		else
		{
			die('User nicht vorhanden!');
		}
	}
}

if($ende-$begin>31536000)
{
	die("Datumsbereich ist zu grosz!");
}

if(!isset($type))
{
	if($pers_uid=check_student($uid))
		$type='student';
	elseif($pers_uid=check_lektor($uid))
		$type='lektor';
    else
        die("Cannot set type!");
}

if(!isset($pers_uid))
{
	if($type=='student')
		$pers_uid=check_student($uid);
	elseif($type=='lektor')
		$pers_uid=check_lektor($uid);
	else
		$pers_uid='';
}

// Stundenplanobjekt erzeugen
$stdplan = new wochenplan($type);
$stdplan->crlf=$crlf;

// Zusaetzliche Daten laden
if(!$stdplan->load_data($type,$pers_uid,$ort_kurzbz,$stg_kz,$sem,$ver,$grp,$gruppe_kurzbz) )
{
	die($stdplan->errormsg);
}

//Ueberschriften in HTML
if($format=='HTML')
{
	if($type=='verband' || $type=='einheit')
	{
		if (strlen($gruppe_kurzbz)>0)
			echo '<H1>Lehrverband: '.$gruppe_kurzbz.'</H1>';
		else
			echo '<H1>Lehrverband: '.$stdplan->stg_kurzbzlang.'-'.$sem.$ver.$grp.'</H1>';
	}
	if($type=='ort')
		echo '<H1>Ort: '.$ort_kurzbz.' - '.$stdplan->ort_bezeichnung.'</H1>';
	if($type=='lektor')
		echo '<H1>Lektor: '.$stdplan->pers_titelpre.' '.$stdplan->pers_vornamen.' '.$stdplan->pers_nachname.' '.$stdplan->pers_titelpost.'</H1>';
}


$i=0;
// Kalender erstellen
while($begin<$ende)
{
	$i++;
	if(!date("w",$begin))
		$begin=jump_day($begin,1);
	
	$stdplan->init_stdplan();
	$datum=$begin;
	$begin+=604800;	// eine Woche

	// Stundenplan einer Woche laden
	if(!$stdplan->load_week($datum,$db_stpl_table))
	{
		die($stdplan->errormsg);
	}

	// Stundenplan der Woche drucken
	if($format=='csv' || $format=='ical')
	{		
		$stdplan->draw_week_csv($target, LVPLAN_KATEGORIE);
	}
	else
	{
		$style='style="padding-top: 10px;" class="page-break-after"';

		echo '<div '.$style.'>';
		$stdplan->draw_week(false);
		echo '</div>';
	}
}

// Print in csv-file
if($format=='csv')
{
	echo $crlf;
}
// Print in ical-file
elseif($format=='ical')
{
	echo $crlf.'END:VCALENDAR';
	ob_end_flush();
}
// Print in HTML-File
else
{
	echo '<P class="dont-print">Fehler und Feedback bitte an <A class="Item" href="mailto:'.MAIL_LVPLAN.'">LV-Plan</A></P>';
	echo '</body></html>';
}
?>