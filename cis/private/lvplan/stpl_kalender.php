<?php
/****************************************************************************
 * Script: 			stpl_kalender.php
 * Descr:  			Das Script dient zum Darstellung des Stundenplans
 *					als Kalender ueber das gesamte Semester
 * Verzweigungen: 	von stpl_week.php
 * Author: 			Christian Paminger
 * Erstellt: 		21.9.2001 von Christian Paminger
 * Update: 			10.9.2005 von Christian Paminger
 *****************************************************************************/

//Startwerte setzen
$db_stpl_table='stundenplan';
$type=$_GET['type'];
$pers_uid=$_GET['pers_uid'];
$ort_kurzbz=(isset($_GET['ort_kurzbz'])?$_GET['ort_kurzbz']:'');
$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:'');
$sem=(isset($_GET['sem'])?$_GET['sem']:'');
$ver=(isset($_GET['ver'])?$_GET['ver']:'');
$grp=(isset($_GET['grp'])?$_GET['grp']:'');
$gruppe_kurzbz=(isset($_GET['einheit'])?$_GET['einheit']:'');
$begin=$_GET['begin'];
$ende=$_GET['ende'];
$format=$_GET['format'];
$version=(isset($_GET['version'])?$_GET['version']:2);

// UID bestimmen
if (!isset($REMOTE_USER))
	$REMOTE_USER='pam';
$uid=$REMOTE_USER;


$jahr=date("Y",$begin);
$mon=date("m",$begin);
$name='TW-Kalender_'.$mon.'_'.$jahr;
if (isset($target))
	$name.='_'.$target;

// doing some DOS-CRLF magic...
$crlf="\n";
$client=getenv("HTTP_USER_AGENT");
if (ereg('[^(]*\((.*)\)[^)]*',$client,$regs))
{
	$os = $regs[1];
	// this looks better under WinX
	if (eregi("Win",$os)) $crlf="\r\n";
}

// Check Type
// Print in csv-file
if ($format=='csv')
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
else if ($format=='ical')
{
	$name.='.ics';
	header("Content-disposition: filename=$name");
	header("Content-type: text/calendar");
	header("Pragma: public");
	header("Expires: 0");
	echo 'BEGIN:VCALENDAR'.$crlf.'VERSION:'.$version.'.0';
}
// Print in HTML-File
else
{
	echo '<html>';
	echo '<head>';
	echo '<title>Kalender</title>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
	echo '<link rel="stylesheet" href="../../skin/cis.css" type="text/css">';
	echo '</head>';
	echo '<body>';
}


// Jetzt gehts los
include('../config.inc.php');
include('../../include/functions.inc.php');
require('../../include/stundenplan.class.php');

writeCISlog('START');

if (!$conn = @pg_pconnect(CONN_STRING))
{
	writeCISlog('STOP');
   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
}
if (!isset($begin) || !isset($ende))
	// datum holen falls nicht gesetzt
	if (!isset($_GET['semesterplan']))
	{
		writeCISlog('STOP');
		die("Datum ist nicht gesetzt!");
	}
	else
	{
		$result_semester=@pg_query($conn,"SELECT start,ende FROM tbl_studiensemester WHERE studiensemester_kurzbz=(SELECT wert FROM tbl_variable WHERE name='semester_aktuell' AND uid='$uid');");
		if (pg_numrows($result_semester)>0)
		{
			$begin=strtotime(pg_result($result_semester,0,'start'));
			$ende=strtotime(pg_result($result_semester,0,'ende'));
		}
		else
		{
			writeCISlog('STOP');
			die('Studiensemester konnte nicht gefunden werden!');
		}
		$result_semester=@pg_query($conn,"SELECT wert FROM tbl_variable WHERE uid='$uid' AND name='db_stpl_table';");
		if (pg_numrows($result_semester)>0)
			$db_stpl_table=pg_result($result_semester,0,'wert');
		else
		{
			writeCISlog('STOP');
			die('User nicht vorhanden!');
		}
	}
if ($ende-$begin>31536000)
{
	writeCISlog('STOP');
	die("Datumsbereich ist zu grosz!");
}

if (!isset($type))
	if ($pers_uid=check_student($uid, $conn))
		$type='student';
	elseif ($pers_uid=check_lektor($uid, $conn))
		$type='lektor';
    else
    {
    	writeCISlog('STOP');
        die("Cannot set type!");
    }
if (!isset($pers_uid))
	if ($type=='student')
		$pers_uid=check_student($uid, $conn);
	elseif ($type=='lektor')
		$pers_uid=check_lektor($uid, $conn);

// Stundenplanobjekt erzeugen
$stdplan=new stundenplan($type,$conn);
$stdplan->crlf=$crlf;

// Zusaetzliche Daten laden
if (! $stdplan->load_data($type,$pers_uid,$ort_kurzbz,$stg_kz,$sem,$ver,$grp,$gruppe_kurzbz) )
{
		writeCISlog('STOP');
		die($stdplan->errormsg);
}

//Ueberschriften in HTML
if ($format=='HTML')
{
	if ($type=='verband' || $type=='einheit')
		if (count($gruppe_kurzbz)>0)
			echo '<H1>Lehrverband: '.$gruppe_kurzbz.'</H1>';
		else
			echo '<H1>Lehrverband: '.$stdplan->stg_kurzbzlang.'-'.$sem.$ver.$grp.'</H1>';
	if ($type=='ort')
		echo '<H1>Ort: '.$ort_kurzbz.' - '.$stdplan->ort_bezeichnung.'</H1>';
	if ($type=='lektor')
		echo '<H1>Lektor: '.$stdplan->$pers_titel.' '.$stdplan->pers_vornamen.' '.$stdplan->pers_nachname.'</H1>';
}



// Kalender erstellen
while ($begin<$ende)
{
	if (!date("w",$begin))
		$begin=jump_day($begin,1);
	$stdplan->init_stdplan();
	$datum=$begin;
	$begin+=604800;	// eine Woche

	// Stundenplan einer Woche laden
	if (! $stdplan->load_week($datum,$db_stpl_table))
	{
		writeCISlog('STOP');
		die($stdplan->errormsg);
	}

	// Stundenplan der Woche drucken
	if ($format=='csv' || $format=='ical')
		$stdplan->draw_week_csv($target);
	//else if ($format=='ical')
	//	$stdplan->draw_week_ical($target);
	else
		$stdplan->draw_week();
}

// Print in csv-file
if ($format=='csv')
{
	echo $crlf;
}
// Print in ical-file
else if ($format=='ical')
{
	echo $crlf.'END:VCALENDAR';
}
// Print in HTML-File
else
{
	echo '<P>Fehler und Feedback bitte an <A href="mailto:stpl@technikum-wien.at">Stundenplan</A></P>';
	echo '</body></html>';
}
writeCISlog('STOP');
?>