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

require_once(dirname(__FILE__).'/../../../config/cis.config.inc.php');
require_once(dirname(__FILE__).'/../../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../../include/wochenplan.class.php');
require_once(dirname(__FILE__).'/../../../include/datum.class.php');
require_once(dirname(__FILE__).'/../../../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../../../include/lehrveranstaltung.class.php');
require_once(dirname(__FILE__).'/../../../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../../../include/Excel/excel.php');

if(!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

$sprache = getSprache();
$p=new phrasen($sprache);

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

$stsem=(isset($_GET['stsem'])?$_GET['stsem']:'');
$lva=(isset($_GET['lva'])?$_GET['lva']:'');


if(isset($_GET["cal"]))
{
	// Nicht authentifizierter Zugriff per Codierter UID
	// fuer Abonnierung im Google ueber /webdav/google.php
	$cal = $_GET["cal"];
	$uid=decryptData($cal,LVPLAN_CYPHER_KEY);
	//Wenn der Key manuell geaendert wird koennen Fehlerhaft kodierte Zeichen
	//entstehen und fuehren zu DB fehlern deshalb werden falsch kodierte uids hier aussortiert
	if(!check_utf8($uid))
		die('Fehlerhafter Parameter');

	//Pruefen ob dieser Benutzer auch wirklich existiert
	$benutzer = new benutzer();
	if(!$benutzer->load($uid))
		die('Ungueltiger Benutzername');

	//Output-Format wird auf ical geaendert
	$target='ical';
	$format='ical';
}
else
{
	// UID bestimmen
	$uid = get_uid();
}

// Beginn Ende setzen
if(!isset($begin))
{
	$objSS=new studiensemester();
	if($stsem=='')
		$ss = $objSS->getaktorNext();
	else
		$ss = $stsem;
	$objSS->load($ss);
	$datum_obj = new datum();
	$begin = $datum_obj->mktime_fromdate($objSS->start);

	// Ein Monat vor ende des Studiensemester soll zusaetzlich das kommende angezeigt werden
	$datum_obj = new datum();
	$diff = $datum_obj->DateDiff($objSS->ende, date('Y-m-d H:i:s'));

	if($diff>=-30)
	{
		$objSS->getNextFrom($ss);
		$ende = $datum_obj->mktime_fromdate($objSS->ende);
	}
	else
		$ende = $datum_obj->mktime_fromdate($objSS->ende);
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
	echo '
BEGIN:VTIMEZONE
TZID:Europe/Vienna
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T020000
TZNAME:GMT+02:00
TZOFFSETTO:+0200
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T030000
TZNAME:GMT+01:00
TZOFFSETTO:+0100
END:STANDARD
END:VTIMEZONE';
}
elseif($format=='excel')
{
	$exceldata=array();
}
// Print in HTML-File
else
{
	echo '<html>';
	echo '<head>';
	echo '<title>'.$p->t('global/kalender').'</title>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
	echo '<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">';
	echo '<link rel="stylesheet" media="print" href="../../../skin/cis.css" type="text/css">';
	echo '<link rel="stylesheet" type="text/css" media="print" href="../../../skin/print.css" />';
	echo '</head>';
	echo '<body id="inhalt">';
}

if(!isset($begin) || !isset($ende))
{
	// datum holen falls nicht gesetzt
	if (!isset($_GET['semesterplan']))
	{
		die($p->t('global/datumNichtGesetzt')."!");
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
			die($p->t('global/studiensemesterKonnteNichtGefundenWerden').'!');
		}
		$result_semester=$db->db_query("SELECT wert FROM public.tbl_variable WHERE uid='$uid' AND name='db_stpl_table';");
		if($db->db_num_rows($result_semester)>0)
			$db_stpl_table=$db->db_result($result_semester,0,'wert');
		else
		{
			die($p->t('global/userNichtGefunden').'!');
		}
	}
}


if($ende-$begin>34560000) // = 400 Tage
{
	die($p->t('lvplan/datumsbereichZuGross')."!");
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
if(!$stdplan->load_data($type,$pers_uid,$ort_kurzbz,$stg_kz,$sem,$ver,$grp,$gruppe_kurzbz,null,$lva) )
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
		echo '<H1>Lektor: '.$stdplan->pers_titelpre.' '.$stdplan->pers_vorname.' '.$stdplan->pers_nachname.' '.$stdplan->pers_titelpost.'</H1>';
}


$i=0;
// Kalender erstellen
while($begin<$ende)
{
	$i++;
	$begin = montag($begin);

	$stdplan->init_stdplan();
	$datum=$begin;

	// eine Woche weiterspringen
	$datum_tmp = new datum();
	$begin = $datum_tmp->jump_week($begin,1);

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
	elseif($format=='excel')
	{
		$data = $stdplan->draw_week_csv('return', LVPLAN_KATEGORIE);
		$exceldata = array_merge($exceldata, $data);
	}
	else
	{
		$style='style="padding-top: 10px;" class="page-break-after"';

		echo '<div '.$style.'>';
		$stdplan->draw_week(false,'',false);
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
}
elseif($format=='excel')
{
	OutputKalenderAsExcel($exceldata);
}
// Print in HTML-File
else
{
	echo '<P class="dont-print">'.$p->t('lvplan/fehlerUndFeedback').' <A class="Item" href="mailto:'.MAIL_LVPLAN.'">'.$p->t('lvplan/lvKoordinationsstelle').'</A></P>';
	echo '</body></html>';
}


/**
 *
 */
function OutputKalenderAsExcel($exceldata)
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Termine". "_" . date("d_m_Y") . ".xls");
	$workbook->setVersion(8);
	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Termine");
	$worksheet->setInputEncoding('utf-8');

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();

	$format_title =& $workbook->addFormat();
	$format_title->setBold();
	// let's merge
	$format_title->setAlign('merge');

	//Zeilenueberschriften ausgeben
	$headline=array('Datum','Von','Bis','Ort','Lektoren','Gruppen','Lehrfach','Anmerkung','StundeVon','StundeBis');

	$i=0;
	foreach ($headline as $title)
	{
		$worksheet->write(0,$i,$title, $format_bold);
			$maxlength[$i]=mb_strlen($title);
		$i++;
	}

	$zeile=1;
	if(is_array($exceldata))
	{
		foreach($exceldata as $row)
		{
			$i=0;

			writecol($worksheet, $maxlength,$zeile, $i++, $row['start_date']);
			writecol($worksheet, $maxlength,$zeile, $i++, $row['start_time']);
			writecol($worksheet, $maxlength,$zeile, $i++, $row['end_time']);
			writecol($worksheet, $maxlength,$zeile, $i++, $row['ort']);
			$lkt='';
			foreach($row['lektor_uid'] as $row_lkt)
			{
				$bn = new benutzer();
				$bn->load($row_lkt);

				$lkt.=$bn->vorname.' '.$bn->nachname.', ';
			}
			$lkt = mb_substr($lkt, 0, -2);
			writecol($worksheet, $maxlength,$zeile, $i++, $lkt);
			writecol($worksheet, $maxlength,$zeile, $i++, implode(',',$row['gruppen']));

			if($row['lehrfach_id']!='')
			{
				$lv = new lehrveranstaltung();
				$lv->load($row['lehrfach_id']);
				$bezeichnung = $lv->bezeichnung;
			}
			else
				$bezeichnung = $row['Summary'];

			writecol($worksheet, $maxlength,$zeile, $i++, $bezeichnung);
			writecol($worksheet, $maxlength,$zeile, $i++, $row['titel']);

			writecol($worksheet, $maxlength,$zeile, $i++, min($row['stunden']));
			writecol($worksheet, $maxlength,$zeile, $i++, max($row['stunden']));


			$zeile++;
		}
	}

	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);

	$workbook->close();
}
function writecol($worksheet, &$maxlength, $zeile, $i, $content)
{
	$worksheet->write($zeile, $i, $content);

	if(isset($maxlength[$i]))
		if(mb_strlen($content)>$maxlength[$i])
			$maxlength[$i]=mb_strlen($content);
}

?>
