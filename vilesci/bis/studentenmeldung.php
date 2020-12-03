<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Studentenmeldung
 *
 * Erstellt ein XML File fuer die Studentenmeldung an den FHR
 * Das XML-File wird im Filesystem abgelegt.
 * Zusaetzlich wird eine Uebersichtsliste ueber die im File enthaltenen Daten erstellt und
 * nicht plausible Daten
 *
 * Parameter: stg_kz ... Kennzahl des Studienganges
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/bisio.class.php');
require_once('../../include/prestudent.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('student/stammdaten',null,'suid') && !$rechte->isBerechtigt('assistenz',null,'suid') && !$rechte->isBerechtigt('admin',null,'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

$error_log='';
$error_log1='';
$error_log_all="";
$error_log_io = ''; // error log fuer plausichecks von incomings/outgoings
$stgart='';
$fehler='';
$maxsemester=0;
$v='';
$studiensemester=new studiensemester();
// Wenn Studiensemester als GET übergeben wird, dieses laden, sonst getaktorNext()
if (isset($_GET['studiensemester']))
{
	$ssem = $_GET['studiensemester'];
	$psem = $studiensemester->getPreviousFrom($ssem);
}
else
{
	$ssem = $studiensemester->getaktorNext();
	$psem = $studiensemester->getPrevious();
}

$anzahl_fehler=0;
$erhalter='';
$stgart='';
$orgform_code='';
$status='';
$datei='';
$aktstatus='';
$aktstatus_datum='';
$mob='';
$gast='';
$avon='';
$abis='';
$zweck='';
$bewerberM=array();
$bewerberW=array();
$bsem=array();
$stsem=array();
$usem=array();
$asem=array();
$absem=array();
$iosem=array();
$gssem=array();
$bewerbercount=array();
$orgform_kurzbz='';
$tabelle='';
$stlist='';
$bwlist='';
$storgfor='';
$verwendete_orgformen=array();
$student_data=array();

$datum_obj = new datum();

//Beginn- und Endedatum des aktuellen Semesters
$qry="SELECT * FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=".$db->db_add_param($ssem).";";
if($result = $db->db_query($qry))
{
	if($row = $db->db_fetch_object($result))
	{
		$beginn=$row->start;
		$ende=$row->ende;
	}
}
//Ermittlung aktuelles und letztes BIS-Meldedatum
if(mb_strstr($ssem,"WS"))
{
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 11, 15, date("Y")));
	$bisprevious=date("Y-m-d",  mktime(0, 0, 0, 04, 15, date("Y")));
}
elseif(mb_strstr($ssem,"SS"))
{
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 04, 15, date("Y")));
	$bisprevious=date("Y-m-d",  mktime(0, 0, 0, 11, 15, date("Y")-1));
}
else
{
	die('Ung&uuml;ltiges Studiensemester!');
}
//ausgewaehlter Studiengang
if(isset($_GET['stg_kz']))
{
	$stg_kz=$_GET['stg_kz'];
}
else
{
	die('<H2>Es wurde kein Studiengang ausgew&auml;hlt!</H2>');
}

/*
 standortcode 22=Wien
derzeit fuer alle Studierende der gleiche Standort
ToDo: Standort sollte pro Student konfigurierbar sein.
*/
$standortcode='22';
if(in_array($stg_kz,array('265','268','761','760','266','267','764','269','400','794','795','786','859')))
	$standortcode='14'; // Pinkafeld
elseif(in_array($stg_kz,array('639','640','263','743','364','635','402','401','725','264','271','781')))
	$standortcode='3'; // Eisenstadt

$datumobj=new datum();

$qry='SELECT * FROM bis.tbl_orgform';

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$orgform_code_array[$row->orgform_kurzbz]=$row->code;
	}
}
$qry = 'SELECT * FROM bis.tbl_gsstudientyp';

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$kodex_studientyp_array[$row->gsstudientyp_kurzbz]=$row->studientyp_code;
	}
}

// StudStatusCode
$kodex_studstatuscode_array['Student']     =1;
$kodex_studstatuscode_array['Unterbrecher']=2;
$kodex_studstatuscode_array['Absolvent']   =3;
$kodex_studstatuscode_array['Abbrecher']   =4;

//Studiengangsdaten auslesen
$stg_obj = new studiengang();
if($stg_obj->load($stg_kz))
{
	$maxsemester=$stg_obj->max_semester;
	if($maxsemester==0)
	{
		echo "Die maximale Semesteranzahl des Studienganges ist nicht angegeben!";
		exit;
	}

	$erhalter = sprintf('%03s',$stg_obj->erhalter_kz);

	switch($stg_obj->typ)
	{
		case 'b': $stgart=1; break;
		case 'm': $stgart=2; break;
		case 'd': $stgart=3; break;
		case 'e': $stgart=4; break;
		default: die('<h2>Dieser Studiengangstyp kann nicht gemeldet werden. Typ muss (b, m, d oder e) sein</h2>'); break;
	}

	// DoubleDegree Studierende werden per Default aus BB gemeldet.
	// Wenn es ein reiner VZ Studiengang ist, dann sollen diese aber als VZ gemeldet werden.
	if($stg_obj->orgform_kurzbz=='VZ')
		$orgform_code_array['DDP']=$orgform_code_array['VZ'];

	$orgform_code = $orgform_code_array[$stg_obj->orgform_kurzbz];
	$orgform_kurzbz=$stg_obj->orgform_kurzbz;
}
else
	die('Fehler:'.$stg_obj->errormsg);


//Ausgabe aktiver Studenten, die nicht gemeldet werden
$qry_akt="
	SELECT
		DISTINCT ON(student_uid, nachname, vorname) *, public.tbl_person.person_id AS pers_id
	FROM
		public.tbl_student
		JOIN public.tbl_benutzer ON(student_uid=uid)
		JOIN public.tbl_person USING (person_id)
		JOIN public.tbl_prestudent USING (prestudent_id)
		JOIN public.tbl_prestudentstatus ON(tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id)
	WHERE
		bismelden=FALSE
		AND tbl_student.studiengang_kz=".$db->db_add_param($stg_kz)."
		AND (tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($ssem)." AND status_kurzbz IN ('Student','Diplomand','Unterbrecher','Praktikant','Outgoing'))
		AND tbl_prestudent.prestudent_id NOT IN
			(
			SELECT prestudent_id
			FROM public.tbl_prestudentstatus
			WHERE
			 	tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($ssem)."
			 	AND (status_kurzbz='Abbrecher' OR status_kurzbz='Absolvent')
			 )
	ORDER BY student_uid, nachname, vorname
	";
if($result_akt = $db->db_query($qry_akt))
{
	while($row_akt = $db->db_fetch_object($result_akt))
	{
		$v.="<u><b>Person (UID, Vorname, Nachname) '".$row_akt->student_uid."', '".$row_akt->nachname."', '".$row_akt->vorname."'</u></b> hat Status $row_akt->status_kurzbz, wird aber nicht BIS gemeldet!!! <br>\n";
		$anzahl_fehler++;
	}
}

//Incoming ohne I/O Datensatz anzeigen
$qry_in="
	SELECT
		DISTINCT ON(student_uid, nachname, vorname) *, public.tbl_person.person_id AS pers_id
	FROM
		public.tbl_student
		JOIN public.tbl_benutzer ON(student_uid=uid)
		JOIN public.tbl_person USING (person_id)
		JOIN public.tbl_prestudent USING (prestudent_id)
		JOIN public.tbl_prestudentstatus ON(tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id)
	WHERE
		bismelden=TRUE
		AND tbl_student.studiengang_kz=".$db->db_add_param($stg_kz)."
		AND (status_kurzbz='Incoming' AND NOT EXISTS (SELECT 1 FROM bis.tbl_bisio WHERE student_uid=tbl_student.student_uid))
	ORDER BY student_uid, nachname, vorname
	";
if($result_in = $db->db_query($qry_in))
{
	while($row_in = $db->db_fetch_object($result_in))
	{
		$v.="<u>Bei Student (UID, Vorname, Nachname) '".$row_in->student_uid."', '".$row_in->nachname."', '".$row_in->vorname."' ($row_in->status_kurzbz): </u>\n";
		$v.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Es fehlt der I/O-Datensatz\n\n";
		$anzahl_fehler++;
	}
}

//Hauptselect
// An der FHTW können nur die Incomings ausgelesen werden, wenn die stg_kz 10006 übergeben wird
if (CAMPUS_NAME == 'FH Technikum Wien' && $stg_kz==10006)
{
	$qry="
	SELECT
		DISTINCT ON(student_uid, nachname, vorname) *, public.tbl_person.person_id AS pers_id, to_char(gebdatum, 'ddmmyy') AS vdat
	FROM
		public.tbl_student
		JOIN public.tbl_benutzer ON(student_uid=uid)
		JOIN public.tbl_person USING (person_id)
		JOIN public.tbl_prestudent USING (prestudent_id)
		JOIN public.tbl_prestudentstatus ON(tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id)
	WHERE
		bismelden=TRUE
		AND (status_kurzbz='Incoming' AND student_uid IN (SELECT student_uid FROM bis.tbl_bisio WHERE (tbl_bisio.bis>=".$db->db_add_param($bisprevious).")
				OR (tbl_bisio.von<=".$db->db_add_param($bisdatum)." AND (tbl_bisio.bis>=".$db->db_add_param($bisdatum)."  OR tbl_bisio.bis IS NULL))
		))
	ORDER BY student_uid, nachname, vorname
	";
}
else
{
	$qry="
	SELECT
		DISTINCT ON(student_uid, nachname, vorname) *, public.tbl_person.person_id AS pers_id, to_char(gebdatum, 'ddmmyy') AS vdat
	FROM
		public.tbl_student
		JOIN public.tbl_benutzer ON(student_uid=uid)
		JOIN public.tbl_person USING (person_id)
		JOIN public.tbl_prestudent USING (prestudent_id)
		JOIN public.tbl_prestudentstatus ON(tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id)
	WHERE
		bismelden=TRUE
		AND tbl_student.studiengang_kz=".$db->db_add_param($stg_kz)."
		AND (((tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($ssem).") AND (tbl_prestudentstatus.datum<=".$db->db_add_param($bisdatum).")
			AND (status_kurzbz='Student' OR status_kurzbz='Outgoing'
			OR status_kurzbz='Praktikant' OR status_kurzbz='Diplomand' OR status_kurzbz='Absolvent'
			OR status_kurzbz='Abbrecher' OR status_kurzbz='Unterbrecher'))
			OR ((tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($psem).") AND (status_kurzbz='Absolvent'
			OR status_kurzbz='Abbrecher') AND tbl_prestudentstatus.datum>".$db->db_add_param($bisprevious).")
			OR (status_kurzbz='Incoming' AND student_uid IN (SELECT student_uid FROM bis.tbl_bisio WHERE (tbl_bisio.bis>=".$db->db_add_param($bisprevious).")
				OR (tbl_bisio.von<=".$db->db_add_param($bisdatum)." AND (tbl_bisio.bis>=".$db->db_add_param($bisdatum)."  OR tbl_bisio.bis IS NULL))
		)))
	ORDER BY student_uid, nachname, vorname
	";
}

if($result = $db->db_query($qry))
{

	$datei.="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<Erhalter>
  <ErhKz>".$erhalter."</ErhKz>
  <MeldeDatum>".date("dmY", $datumobj->mktime_fromdate($bisdatum))."</MeldeDatum>
  <StudierendenBewerberMeldung>
    <Studiengang>
      <StgKz>".$stg_kz."</StgKz>";

	while($row = $db->db_fetch_object($result))
	{
		$datei.= GenerateXMLStudentBlock($row);
	}

	//Bewerberblock bei Ausserordentlichen nicht anzeigen
	if($stg_kz!=('9'.$erhalter))
	{
		$stg_obj = new studiengang();

		if($orgform_code==3 || $stg_obj->isMischform($stg_kz,$ssem) || $stg_obj->isMischform($stg_kz,$psem))
		{
			$orgcodes = array_unique($orgform_code_array);
			//Mischform
			foreach($orgcodes as $code)
				$datei.= GenerateXMLBewerberBlock($code);
		}
		else
			$datei.= GenerateXMLBewerberBlock();
	}
}

$datei.="
    </Studiengang>
  </StudierendenBewerberMeldung>
</Erhalter>";
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>BIS - Meldung Student - ('.$stg_kz.')</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">';

		include('../../include/meta/jquery.php');
		include('../../include/meta/jquery-tablesorter.php');

echo '	</head>
	<style>
	#t1, #t2
	{
		width: auto;
	}
	</style>
	<script language="JavaScript" type="text/javascript">
	$(document).ready(function()
	{
		$("#t1").tablesorter(
		{
			sortList: [[6,1],[5,1],[4,1],[2,0],[3,0]], 
			widgets: ["zebra", "filter", "stickyHeaders"],
			widgetOptions : {	filter_functions:  
								{ 
									// Add select menu to this column 
									4 : {
									"Abbrecher" : function(e, n, f, i, $r, c, data) { return /Abbrecher/.test(e); }, 
									"Absolvent" : function(e, n, f, i, $r, c, data) { return /Absolvent/.test(e); },
									"Diplomand" : function(e, n, f, i, $r, c, data) { return /Diplomand/.test(e); },
									"Incoming" : function(e, n, f, i, $r, c, data) { return /Incoming/.test(e); },
									"Student" : function(e, n, f, i, $r, c, data) { return /Student/.test(e); },
									"Unterbrecher" : function(e, n, f, i, $r, c, data) { return /Unterbrecher/.test(e); }, 
									}
								} 
							} 
		});
		$("#t2").tablesorter(
		{
			sortList: [[0,0],[1,0]], 
			widgets: ["zebra", "filter", "stickyHeaders"] 
		});
	});
	</script>
	<body>';
if ($rechte->isBerechtigt('admin'))
{
	echo '<form name="frm_studiengang" action='.$_SERVER['PHP_SELF'].' method="GET">';
	echo 'Studiengang: <SELECT name="stg_kz"  onchange="document.frm_studiengang.submit()">';
	$studiengang = new studiengang();
	$studiengang->getAll('typ, kurzbz', true);
	$types = new studiengang();
	$types->getAllTypes();
	$typ = '';
	foreach ($studiengang->result AS $row)
	{
		if ($row->studiengang_kz == $stg_kz)
		{
			$selected = 'selected';
		}
		else
		{
			$selected = '';
		}

		if ($typ != $row->typ || $typ == '')
		{
			if ($typ != '')
			{
				echo '</optgroup>';
			}
			echo '<optgroup label="'.($types->studiengang_typ_arr[$row->typ] != ''?$types->studiengang_typ_arr[$row->typ]:$row->typ).'">';
		}

		echo '<OPTION value="'.$row->studiengang_kz.'"'.$selected.'>'.$row->kuerzel.' - '.$row->bezeichnung.'</OPTION>';

		$typ = $row->typ;
	}
	echo '</select>';
	echo '</form>';
}
$studiengang = new studiengang($stg_kz);
$typ = new studiengang($stg_kz);
$typ->getStudiengangTyp($studiengang->typ);
echo "<H1>BIS - Studentendaten werden &uuml;berpr&uuml;ft! Studiengang: ".$db->convert_html_chars($stg_kz)." - ".$typ->bezeichnung." ".$studiengang->bezeichnung."</H1>\n";
echo "<H2>Nicht plausible BIS-Daten (f&uuml;r Meldung ".$db->convert_html_chars($ssem)."): </H2><br>";
echo nl2br($v."\n\n");

//Tabelle mit Ergebnissen ausgeben
$tabelle="<H2>BIS-Meldungs&uuml;bersicht: </H2><br>
<table border=1>
	<colgroup>
		<col width='180'>
		<col width='80'>
		<col width='80'>
		<col width='80'>
		<col width='80'>
		<col width='80'>
		<col width='80'>
		<col width='80'>
		<col width='80'>
		<col width='80'>
		<col width='80'>
	</colgroup>
<tr align=center>
	<th bgcolor='#AFFA49'>Semester</th>
	<th bgcolor='#AFFA49'>1</th>
	<th bgcolor='#AFFA49'>2</th>
	<th bgcolor='#AFFA49'>3</th>
	<th bgcolor='#AFFA49'>4</th>
	<th bgcolor='#AFFA49'>5</th>
	<th bgcolor='#AFFA49'>6</th>
	<th bgcolor='#AFFA49'>7</th>
	<th bgcolor='#AFFA49'>8</th>
	<th bgcolor='#AFFA49'>50</th>
	<th bgcolor='#AFFA49'>60</th>
</tr>";

$semester_arr = array(1,2,3,4,5,6,7,8,50,60);

sort($verwendete_orgformen);

$orgformen = implode('/',$verwendete_orgformen);

$aktiv="
	<tr align=center>
		<td bgcolor='#AFFA49'>aktive Studenten ($orgformen)</td>";
$unterbrecher="
	<tr align=center>
		<td bgcolor='#AFFA49'>Unterbrecher ($orgformen)</td>";
$abbrecher="
	<tr align=center>
		<td bgcolor='#AFFA49'>Abbrecher ($orgformen)</td>";
$absolventen="
	<tr align=center>
		<td bgcolor='#AFFA49'>Absolventen ($orgformen)</td>";
$outgoing="
	<tr align=center>
		<td bgcolor='#AFFA49'>Outgoing ($orgformen)</td>";
$gemeinsamestudien="
	<tr align=center>
		<td bgcolor='#AFFA49'>GemeinsameStudien</td>";
foreach ($semester_arr as $semester)
{
	$aktiv.='<td>&nbsp;';
	$unterbrecher.='<td>&nbsp;';
	$abbrecher.='<td>&nbsp;';
	$absolventen.='<td>&nbsp;';
	$outgoing.='<td>&nbsp;';
	$gemeinsamestudien.='<td>&nbsp;';

	$i=0;
	foreach($verwendete_orgformen as $orgform)
	{
		if($i!=0)
		{
			$aktiv.=' / ';
			$unterbrecher.=' / ';
			$abbrecher.=' / ';
			$absolventen.=' / ';
			$outgoing.=' / ';
			$gemeinsamestudien .=' / ';
		}

		$aktiv .= (isset($stsem[$orgform][$semester])?$stsem[$orgform][$semester]:'');
		$unterbrecher .= (isset($usem[$orgform][$semester])?$usem[$orgform][$semester]:'');
		$abbrecher .= (isset($asem[$orgform][$semester])?$asem[$orgform][$semester]:'');
		$absolventen .= (isset($absem[$orgform][$semester])?$absem[$orgform][$semester]:'');
		$outgoing .= (isset($iosem[$orgform][$semester])?$iosem[$orgform][$semester]:'');
		$gemeinsamestudien .= (isset($gssem[$orgform][$semester])?$gssem[$orgform][$semester]:'');
		$i++;
	}
	$aktiv.='</td>';
	$unterbrecher.='</td>';
	$abbrecher.='</td>';
	$absolventen.='</td>';
	$outgoing.='</td>';
	$gemeinsamestudien.='</td>';
}
$aktiv.='</tr>';
$unterbrecher.='</tr>';
$abbrecher.='</tr>';
$absolventen.='</tr>';
$outgoing.='</tr>';
$gemeinsamestudien.='</tr>';


$tabelle.=$aktiv.$unterbrecher.$abbrecher.$absolventen.$outgoing.$gemeinsamestudien.
"
<tr align=center style='border-top:1px solid black'>
	<td bgcolor='#AFFA49'>Incoming</td>
	<td>".(isset($iosem[0])?$iosem[0]:'')."</td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
<tr align=center>";

$tabelle.= "
<td bgcolor='#AFFA49'>Bewerber(ges.)($orgformen)</td>
<td bgcolor='#DED8FE'>".(isset($bewerbercount[0])?$bewerbercount[0]:0)."</td>
<td bgcolor='#DED8FE'>";
for($i=0;$i<sizeof($verwendete_orgformen);$i++)
{
	if($i!=0)
		$tabelle.=' / ';

	$tabelle.= isset($bewerbercount[$verwendete_orgformen[$i]])?$bewerbercount[$verwendete_orgformen[$i]]:'';
}
$tabelle.='</td>';

$tabelle.= "
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td bgcolor='#FF0000'>".$anzahl_fehler."</td>
</tr>
</table>
<br>";
echo $tabelle;

$ddd='bisdaten/bismeldung_'.$ssem.'_Stg'.$stg_kz.'.xml';

$dateiausgabe=fopen($ddd,'w');
fwrite($dateiausgabe,$datei);
fclose($dateiausgabe);

$eee='bisdaten/tabelle_'.$ssem.'_Stg'.$stg_kz.'.html';

$dateiausgabe=fopen($eee,'w');
fwrite($dateiausgabe,$tabelle);
fclose($dateiausgabe);

if(file_exists($ddd))
{
	echo '<a href="archiv.php?meldung='.$ddd.'&html='.$eee.'&stg='.$stg_kz.'&sem='.$ssem.'&typ=studenten&action=archivieren">BIS-Meldung Stg '.$stg_kz.' archivieren</a><br>';
	echo '<a href="'.$ddd.'" target="_blank" download>XML-Datei f&uuml;r BIS-Meldung Stg '.$stg_kz.'</a><br>';
}
if(file_exists($eee))
{
	echo '<a href="'.$eee.'">BIS-Melde&uuml;bersicht der BIS-Meldung Stg '.$stg_kz.'</a><br><br>';
}

echo '<table id="t1" class="tablesorter">
	<thead>
	<tr align=center>
		<th>UID</th>
		<th>PersKZ</th>
		<th>Nachname</th>
		<th>Vorname</th>
		<th>Status</th>
		<th>Semester</th>
		<th>Orgform</th>
	</tr>
	</thead>
	<tbody>
	',$stlist,'
	</tbody>
	</table>';

echo '<br>Bewerber&uuml;bersicht';
echo '<table id="t2" class="tablesorter">
	<thead>
	<tr align=center>
		<th>Nachname</th>
		<th>Vorname</th>
		<th>Orgform</th>
		<th>Geschlecht</th>
	</tr>
	</thead>
	<tbody>
	',$bwlist,'
	</tbody>
	</table>';

echo '</body></html>';

/**************************************************************************
 *  FUNKTIONEN
 **************************************************************************/

/**
 * Generiert den Studenten Block
 */
function GenerateXMLStudentBlock($row)
{
	global $bisdatum, $db;
	global $ssem, $psem;
	global $v;
	global $stgart, $maxsemester, $orgform_kurzbz, $bisprevious,$anzahl_fehler;
	global $iosem, $stsem, $usem, $asem, $absem, $stlist, $gssem;
	global $verwendete_orgformen, $datum_obj,$orgform_code_array,$standortcode;
	global $kodex_studientyp_array, $kodex_studstatuscode_array;
	global $stg_kz;
	$error_log='';
	$error_log1='';
	$error_log_io = '';
	$datei = '';
	$datumobj = new datum();

	$laststatus = new prestudent();
	$laststatus->getLastStatus($row->prestudent_id);

	//Pruefen ob Ausserordnetlicher Studierender (4.Stelle in Personenkennzeichen = 9)
	if(mb_substr($row->matrikelnr,3,1)=='9')
		$ausserordentlich=true;
	else
		$ausserordentlich=false;

	// Pruefen, ob Incoming (3.Stelle in Personenkennzeichen = 0)
	$incoming = mb_substr($row->matrikelnr,2,1) == '0' ? true : false;

	$qryadr="SELECT * FROM public.tbl_adresse WHERE heimatadresse IS TRUE AND person_id=".$db->db_add_param($row->pers_id).";";
	$results=$db->db_query($qryadr);

	if($db->db_num_rows($results)!=1)
	{
		$error_log1="Es sind ".$db->db_num_rows($results)." Heimatadressen eingetragen\n";
	}
	if($rowadr=$db->db_fetch_object($results))
	{
		$plz=$rowadr->plz;
		$gemeinde=$rowadr->gemeinde;
		$strasse=$rowadr->strasse;
		$nation=$rowadr->nation;
		$co_name = $rowadr->co_name;
	}
	else
	{
		$plz='';
		$gemeinde='';
		$strasse='';
		$nation='';
		$co_name = '';
	}

	// Zustelladresse & c/o Name(=abweichender Empfaenger)
	$qryzustelladr = "
		SELECT *
		FROM public.tbl_adresse
		WHERE zustelladresse IS TRUE
		AND person_id=". $db->db_add_param($row->pers_id). ";
	";
	$results = $db->db_query($qryzustelladr);

	if ($db->db_num_rows($results) != 1)
	{
		$error_log1.= "Es sind ".$db->db_num_rows($results)." Zustelladressen eingetragen\n";
	}

	$zustell_plz = '';
	$zustell_gemeinde = '';
	$zustell_strasse = '';
	$zustell_nation = '';

	if ($rowzustelladr = $db->db_fetch_object($results))
	{
		$zustell_plz = $rowzustelladr->plz;
		$zustell_gemeinde = $rowzustelladr->gemeinde;
		$zustell_strasse = $rowzustelladr->strasse;
		$zustell_nation = $rowzustelladr->nation;
	}

	// FH eMail-Adresse FH aus UID@Domain
	$email = '';
	if ($row->student_uid != '')
	{
		$email = $row->student_uid. '@'. DOMAIN;
	}

	if($row->gebdatum<'1920-01-01' OR $row->gebdatum==null OR $row->gebdatum=='')
	{
		$error_log.=(!empty($error_log)?', ':'')."Geburtsdatum ('".$row->gebdatum."')";
	}
	if($row->geschlecht!='m' && $row->geschlecht!='w' && $row->geschlecht!='x')
	{
		$error_log.=(!empty($error_log)?', ':'')."Geschlecht ('".$row->geschlecht."')";
	}
	if($row->vorname=='' || $row->vorname==null)
	{
		$error_log.=(!empty($error_log)?', ':'')."Vorname ('".$row->vorname."')";
	}
	if($row->nachname=='' || $row->nachname==null)
	{
		$error_log.=(!empty($error_log)?', ':'')."Nachname ('".$row->nachname."')";
	}
	if($row->matr_nr=='')
	{
		$error_log.=(!empty($error_log)?', ':'')."Matrikelnummer fehlt";
	}
	if($row->matr_nr!='' && $row->matr_nr!=null && mb_strlen(trim($row->matr_nr))!=8)
	{
		$error_log.=(!empty($error_log)?', ':'')."Matrikelnummer ('".trim($row->matr_nr)."') ist nicht 8 Zeichen lang";
	}
	if($row->svnr!='' && $row->svnr!=null && mb_strlen(trim($row->svnr))!=10)
	{
		$error_log.=(!empty($error_log)?', ':'')."SVNR ('".trim($row->svnr)."') ist nicht 10 Zeichen lang";
	}
	if($row->ersatzkennzeichen!='' && $row->ersatzkennzeichen!=null && mb_strlen(trim($row->ersatzkennzeichen))!=10)
	{
		$error_log.=(!empty($error_log)?', ':'')."Ersatzkennzeichen ('".trim($row->ersatzkennzeichen)."') ist nicht 10 Zeichen lang";
	}
	if($row->svnr!='' && $row->svnr!=null && substr($row->svnr,4,6)!=$row->vdat && substr($row->vdat,0,4)!='0101' && substr($row->vdat,0,4)!='0107')
	{
		$error_log.=(!empty($error_log)?', ':'')."SVNR ('".$row->svnr."') enth&auml;lt Geburtsdatum (".$datum_obj->formatDatum($row->gebdatum,'d.m.Y').") nicht";
	}
	if($row->ersatzkennzeichen!='' && $row->ersatzkennzeichen!=null && substr($row->ersatzkennzeichen,4,6)!=$row->vdat)
	{
		$error_log.=(!empty($error_log)?', ':'')."Ersatzkennzeichen ('".$row->ersatzkennzeichen."') enth&auml;lt Geburtsdatum (".$datum_obj->formatDatum($row->gebdatum,'d.m.Y').") nicht";
	}
	if(($row->svnr=='' || $row->svnr==null)&&($row->ersatzkennzeichen=='' || $row->ersatzkennzeichen==null))
	{
		$error_log.=(!empty($error_log)?', ':'')."SVNR ('".$row->svnr."') bzw. ErsKz ('".$row->ersatzkennzeichen."') fehlt";
	}
	if($row->staatsbuergerschaft=='' || $row->staatsbuergerschaft==null)
	{
		$error_log.=(!empty($error_log)?', ':'')."Staatsb&uuml;rgerschaft ('".$row->staatsbuergerschaft."')";
	}
	if($plz=='' || $plz==null)
	{
		$error_log.=(!empty($error_log)?', ':'')."Heimat-PLZ ('".$plz."')";
	}
	if($gemeinde=='' || $gemeinde==null)
	{
		$error_log.=(!empty($error_log)?', ':'')."Heimat-Gemeinde ('".$gemeinde."')";
	}
	if($strasse=='' || $strasse==null)
	{
		$error_log.=(!empty($error_log)?', ':'')."Heimat-Strasse ('".$strasse."')";
	}
	if($nation=='' || $nation==null)
	{
		$error_log.=(!empty($error_log)?', ':'')."Heimat-Nation ('".$nation."')";
	}
	/*if($row->bpk == '' || $row->bpk == null)
	{
		$error_log .= (!empty($error_log) ? ', ' : '') . "bPK fehlt";
	}
	if($row->bpk != '' && $row->bpk != null)
	{
		if (!preg_match('/[a-zA-Z0-9\+\/]{27}=/', $row->bpk))
		{
			$error_log.=(!empty($error_log) ? ', ' : ''). "bPK-Zeichenfolge ist ung&uuml;ltig";
		}

		if (strlen($row->bpk) != 28)
		{
			$error_log.=(!empty($error_log) ? ', ' : ''). "bPK ist nicht 28 Zeichen lang";
		}
	}*/
	if (!$ausserordentlich && !$incoming)
	{
		if ($zustell_plz == '' || $zustell_plz == null)
		{
			$error_log.=(!empty($error_log)?', ':'')."Zustell-PLZ fehlt";
		}

		if ($zustell_gemeinde == '' || $zustell_gemeinde == null)
		{
			$error_log.=(!empty($error_log)?', ':'')."Zustell-Gemeinde fehlt";
		}

		if ($zustell_strasse == '' || $zustell_strasse == null)
		{
			$error_log.=(!empty($error_log)?', ':'')."Zustell-Strasse fehlt";
		}

		if ($zustell_nation == '' || $zustell_nation == null)
		{
			$error_log.=(!empty($error_log)?', ':'')."Zustell-Nation fehlt";
		}

		if ($email == '' || $email == null)
		{
			$error_log.=(!empty($error_log)?', ':'')."Studenten-eMail Adresse fehlt (keine Student-UID eingetragen).";
		}

	}

	if(!$ausserordentlich)
	{
		if($row->zgv_code=='' || $row->zgv_code==null)
		{
			$error_log.=(!empty($error_log)?', ':'')."ZugangCode ('".$row->zgv_code."')";
		}
		if($row->zgvdatum=='' || $row->zgvdatum==null)
		{
			$error_log.=(!empty($error_log)?', ':'')."ZugangDatum ('".$row->zgvdatum."')";
		}
		else
		{
			if($row->zgvdatum>date("Y-m-d"))
			{
					$error_log.=(!empty($error_log)?', ':'')."ZugangDatum liegt in der Zukunft ('".$row->zgvdatum."')";
			}
		}
		if($stgart==2) // Master-Studiengang
		{
			if($row->zgvmas_code=='' || $row->zgvmas_code==null)
			{
					$error_log.=(!empty($error_log)?', ':'')."ZugangMaStgCode ('".$row->zgvmas_code."')";
			}
			if($row->zgvmadatum=='' || $row->zgvmadatum==null)
			{
					$error_log.=(!empty($error_log)?', ':'')."ZugangMaStgDatum ('".$row->zgvmadatum."')";
			}
			else
			{
				if($row->zgvmadatum>date("Y-m-d"))
				{
						$error_log.=(!empty($error_log)?', ':'')."ZugangMaStgDatum liegt in der Zukunft ('".$row->zgvmadatum."')";
				}
				if($row->zgvmadatum<$row->zgvdatum)
				{
						$error_log.=(!empty($error_log)?', ':'')."ZugangMaStgDatum ('".$row->zgvmadatum."') kleiner als Zugangdatum ('".$row->zgvdatum."')";
				}
				if($row->zgvmadatum<$row->gebdatum)
				{
						$error_log.=(!empty($error_log)?', ':'')."ZugangMaStgDatum ('".$row->zgvmadatum."') kleiner als Geburtsdatum ('".$row->gebdatum."')";
				}
			}
		}
	}

	$aktstatus_stsem = $ssem;
	//StudStatusCode und Semester ermitteln
	$qrystatus="SELECT * FROM public.tbl_prestudentstatus
		WHERE prestudent_id=".$db->db_add_param($row->prestudent_id)." AND studiensemester_kurzbz=".$db->db_add_param($ssem)." AND (tbl_prestudentstatus.datum<=".$db->db_add_param($bisdatum).")
		ORDER BY datum desc, insertamum desc, ext_id desc;";

	if($resultstatus = $db->db_query($qrystatus))
	{
		if($db->db_num_rows($resultstatus)>0)
		{
			if($rowstatus = $db->db_fetch_object($resultstatus))
			{
				$qry1="SELECT count(*) AS dipl FROM public.tbl_prestudentstatus WHERE prestudent_id=".$db->db_add_param($row->prestudent_id)." AND (tbl_prestudentstatus.datum<=".$db->db_add_param($bisdatum).") AND status_kurzbz='Diplomand'";
				if($result1 = $db->db_query($qry1))
				{
					if($row1 = $db->db_fetch_object($result1))
					{
						$sem=$rowstatus->ausbildungssemester;
						if($sem>$maxsemester)
						{
							$sem=$maxsemester;
						}
						if($row1->dipl>1)
						{
							$sem=50;
						}
						if($row1->dipl>3)
						{
							$sem=60;
						}
					}
				}
				if($rowstatus->status_kurzbz=="Student" || $rowstatus->status_kurzbz=="Outgoing"
					|| $rowstatus->status_kurzbz=="Incoming" || $rowstatus->status_kurzbz=='Praktikant'
					|| $rowstatus->status_kurzbz=="Diplomand")
				{
					$status=1;
				}
				else if($rowstatus->status_kurzbz=="Unterbrecher" )
				{
					$status=2;
				}
				else if($rowstatus->status_kurzbz=="Absolvent" )
				{
					$status=3;
				}
				else if($rowstatus->status_kurzbz=="Abbrecher" )
				{
					$status=4;
				}
				else
				{
					$error_log.= (!empty($error_log)?', ':''). "$row->vorname $row->nachname wird nicht gemeldet da kein gueltiger Status vorhanden ist!";
					return '';
				}
				$aktstatus=$rowstatus->status_kurzbz;
				$aktstatus_datum=$rowstatus->datum;
				$storgform=$rowstatus->orgform_kurzbz;
				$aktstatus_stsem = $rowstatus->studiensemester_kurzbz;
			}
		}
		else
		{
			$qrystatus="SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id=".$db->db_add_param($row->prestudent_id)." AND studiensemester_kurzbz=".$db->db_add_param($psem)." AND (tbl_prestudentstatus.datum<=".$db->db_add_param($bisdatum).") ORDER BY datum desc, insertamum desc, ext_id desc;";
			if($resultstatus = $db->db_query($qrystatus))
			{
				if($rowstatus = $db->db_fetch_object($resultstatus))
				{
					$qry1="SELECT count(*) AS dipl FROM public.tbl_prestudentstatus WHERE prestudent_id=".$db->db_add_param($row->prestudent_id)." AND status_kurzbz='Diplomand' AND (tbl_prestudentstatus.datum<=".$db->db_add_param($bisdatum).")";
					if($result1 = $db->db_query($qry1))
					{
						if($row1 = $db->db_fetch_object($result1))
						{
							$sem=$rowstatus->ausbildungssemester;
							if($sem>$maxsemester)
							{
								$sem=$maxsemester;
							}
							if($row1->dipl>1)
							{
								$sem=50;
							}
							if($row1->dipl>3)
							{
								$sem=60;
							}
						}
					}

					if($ausserordentlich)
					{
						$status=1;
					}
					else if($rowstatus->status_kurzbz=="Incoming")
					{
						$status=1;
					}
					else if($rowstatus->status_kurzbz=="Absolvent" )
					{
						$status=3;
					}
					else if($rowstatus->status_kurzbz=="Abbrecher" )
					{
						$status=4;
					}
					else
					{
						$error_log.= (!empty($error_log)?', ':''). "$row->vorname $row->nachname wird nicht gemeldet da kein gueltiger Status vorhanden ist!";
						return '';
					}
					$aktstatus=$rowstatus->status_kurzbz;
					$aktstatus_datum=$rowstatus->datum;
					$storgform=$rowstatus->orgform_kurzbz;
					$aktstatus_stsem = $rowstatus->studiensemester_kurzbz;
				}
				else
				{
					$aktstatus='';
					$storgform='';
					$aktstatus_datum='';
					$aktstatus_stsem='';
					$sem='';
					$error_log.= (!empty($error_log)?', ':''). "kein gueltiger Status vorhanden";

				}
			}
			else
			{
				$aktstatus='';
				$storgform='';
				$aktstatus_datum='';
				$aktstatus_stsem='';
				$sem='';
				$error_log.= (!empty($error_log)?', ':'').  "kein gueltiger Status vorhanden";

			}
		}
	}
	//Wenn im Status keine Organisationsform eingetragen ist, wird die des Studienganges uebernommen
	if($storgform=='')
	{
		// Wenn FHTW und studiengang_kz 10006 (Campus International) wird die OrgForm des Studiengangs vom Incoming ermittelt
		if (CAMPUS_NAME == 'FH Technikum Wien' && $stg_kz == 10006)
		{
			$studiengang = new studiengang($row->studiengang_kz);
			$storgform = $studiengang->orgform_kurzbz;
		}
		else
		{
			$storgform=$orgform_kurzbz;
		}
	}

	// **** GS Container ****/
	$gsstatus='';
	$gsblock='';
	$gemeinsamestudien=false;
	$qrygs="SELECT
				tbl_mobilitaet.*,
				tbl_gsprogramm.programm_code,
				tbl_firma.partner_code
			FROM
				bis.tbl_mobilitaet
				LEFT JOIN bis.tbl_gsprogramm USING(gsprogramm_id)
				LEFT JOIN public.tbl_firma USING(firma_id)
			WHERE
				prestudent_id=".$db->db_add_param($row->prestudent_id)."
				AND (studiensemester_kurzbz=".$db->db_add_param($aktstatus_stsem)." OR (studiensemester_kurzbz=".$db->db_add_param($psem)." AND status_kurzbz = 'Absolvent'))
			ORDER BY tbl_mobilitaet.insertamum DESC limit 1;";

	$studtyp = '';
	if($resultgs = $db->db_query($qrygs))
	{
		while($rowgs = $db->db_fetch_object($resultgs))
		{
			$gsstatus = 'GS '.$rowgs->status_kurzbz.' '.$row->gsstudientyp_kurzbz;
			$gemeinsamestudien=true;
			$studtyp = $kodex_studientyp_array[$row->gsstudientyp_kurzbz];
			$studstatuscode = (isset($kodex_studstatuscode_array[$rowgs->status_kurzbz])?$kodex_studstatuscode_array[$rowgs->status_kurzbz]:'');

			$gserror='';
			if($studstatuscode=='')
				$gserror.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Gemeinsame Studien - Status ist nicht gesetzt\n";
			if($studtyp=='')
				$gserror.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Gemeinsame Studien - Studientyp ist nicht gesetzt\n";
			if($rowgs->partner_code=='')
				$gserror.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Gemeinsame Studien - Partner Code ist leer\n";
			if($rowgs->programm_code=='')
				$gserror.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Gemeinsame Studien - Programm ist leer\n";

			if($gserror!='')
			{
				$v.="<u>Bei Student (UID, Vorname, Nachname) '".$row->student_uid."', '".$row->nachname."', '".$row->vorname."' ($laststatus->status_kurzbz): </u>\n";
				$v.=$gserror."\n";
				return '';
			}
			$gsblock.="
		<GS>
			<MobilitaetsProgrammCode>".$rowgs->mobilitaetsprogramm_code."</MobilitaetsProgrammCode>
			<ProgrammNr>".$rowgs->programm_code."</ProgrammNr>
			<StudTyp>".$studtyp."</StudTyp>
			<PartnerCode>".$rowgs->partner_code."</PartnerCode>
			<Ausbildungssemester>".$rowgs->ausbildungssemester."</Ausbildungssemester>
			<StudStatusCode>".$studstatuscode."</StudStatusCode>
		</GS>";
			if(!isset($gssem[$storgform][$rowgs->ausbildungssemester]))
			{
				$gssem[$storgform][$rowgs->ausbildungssemester]=0;
			}
			$gssem[$storgform][$rowgs->ausbildungssemester]++;
		}
	}

	//bei Absolventen das Beendigungsdatum (Sponsion oder Abschlussprüfung) überprüfen
	if($aktstatus=='Absolvent' && !$gemeinsamestudien)
	{
		$qry_ap="SELECT * FROM lehre.tbl_abschlusspruefung WHERE student_uid=".$db->db_add_param($row->student_uid)." AND abschlussbeurteilung_kurzbz!='nicht' AND abschlussbeurteilung_kurzbz IS NOT NULL";
		if($result_ap = $db->db_query($qry_ap))
		{
			$ap=0;
			while($row_ap = $db->db_fetch_object($result_ap))
			{
				if($row_ap->datum=='' || $row_ap->datum==null)
				{
					$error_log.=(!empty($error_log)?', ':'')."Datum der Abschlusspr&uuml;fung ('".$row_ap->datum."')";
				}
				if($row_ap->sponsion=='' || $row_ap->sponsion==null)
				{
					$error_log.=(!empty($error_log)?', ':'')."Datum der Sponsion ('".$row_ap->sponsion."')";
				}
				$ap++;
			}
			if($ap!=1)
			{
				$error_log.=(!empty($error_log)?', ':'').$ap." bestandene Abschlußprüfungen";
			}
		}
		else
		{
			die("\nQry Failed:".$qry_ap);
		}
	}
	if($orgform_code_array[$storgform]!=1) // Wenn nicht Vollzeit
	{
		if($row->berufstaetigkeit_code=='' || $row->berufstaetigkeit_code==null)
		{
			$error_log.=(!empty($error_log)?', ':'')."Berufst&auml;tigkeitscode ('".$row->berufstaetigkeit_code."')";
		}
	}
	if($aktstatus!='Incoming')
	{
		if(!$row->reihungstestangetreten)
		{
			$error_log.=(!empty($error_log)?', ':'')."Zum Reihungstest angetreten";
		}
		if($sem==0)
		{
			$error_log.=(!empty($error_log)?', ':'')."Aktuelles Semester (Rolle) ('".$sem."')";
		}
	}
	else
	{
		if($nation=='A' || $nation=='a')
		{
			$error_log.=(!empty($error_log)?', ':'')."Heimat-Nation bei Incoming('".$nation."')";
		}
	}

	$qryad="SELECT * FROM public.tbl_prestudentstatus
				WHERE prestudent_id=".$db->db_add_param($row->prestudent_id)."
				AND (status_kurzbz='Student' OR status_kurzbz='Unterbrecher')
				AND (tbl_prestudentstatus.datum<=".$db->db_add_param($bisdatum).") ORDER BY datum asc;";
	if($resultad = $db->db_query($qryad))
	{
		if($rowad = $db->db_fetch_object($resultad))
		{
			$beginndatum = $rowad->datum;
		}
		else
			$beginndatum='';
	}
	$ausstellungsstaat='';
	if($row->zgvmanation!='' && $stgart==2) // Master
		$ausstellungsstaat = $row->zgvmanation;
	elseif($row->zgvnation!='')
		$ausstellungsstaat = $row->zgvnation;
	else
		$ausstellungsstaat = $row->ausstellungsstaat;

	if($ausstellungsstaat=='' && ($datumobj->mktime_fromdate($beginndatum) > $datumobj->mktime_fromdate('2011-04-15')) && !$ausserordentlich)
	{
		$error_log.=(!empty($error_log)?', ':'')."Ausstellungsstaat ist nicht eingetragen";
	}

	if($error_log!='' OR $error_log1!='')
	{
		$v.="<u>Bei Student (UID, Vorname, Nachname) '".$row->student_uid."', '".$row->nachname."', '".$row->vorname."' ($laststatus->status_kurzbz): </u>\n";
		if($error_log!='')
		{
			$v.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fehler: ".$error_log."\n";
		}
		if($error_log1!='')
		{
			$v.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$error_log1;
		}
		$anzahl_fehler++;
		$v.="\n";
		$error_log='';
		$error_log1='';
		return '';
	}
	else
		{
		$datei .= "
		<StudentIn>
			<PersKz>" . trim($row->matrikelnr) . "</PersKz>";

		$datei .= "
			<Matrikelnummer>" . $row->matr_nr . "</Matrikelnummer>";

		if (!$ausserordentlich)
		{
			$datei .= "
			<OrgFormCode>" . $orgform_code_array[$storgform] . "</OrgFormCode>";
		}

		$datei .= "
			<GeburtsDatum>" . date("dmY", $datumobj->mktime_fromdate($row->gebdatum)) . "</GeburtsDatum>
			<Geschlecht>" . strtoupper($row->geschlecht) . "</Geschlecht>";

		if ($row->titelpre != '')
		{
			$datei .= "
			<AkadGradeVorName>" . $row->titelpre . "</AkadGradeVorName>";
		}

		if ($row->titelpost != '')
		{
			$datei .= "
			<AkadGradeNachName>" . $row->titelpost . "</AkadGradeNachName>";
		}

		$datei .= "
			<Vorname>" . $row->vorname . "</Vorname>
			<Familienname>" . $row->nachname . "</Familienname>";

		if ($row->svnr != '')
		{
			$datei .= "
			<SVNR>" . $row->svnr . "</SVNR>";
		}
		if ($row->ersatzkennzeichen != '')
		{
			$datei .= "
			<ErsKz>" . $row->ersatzkennzeichen . "</ErsKz>";
		}

		/*$datei .= "
			<bPK>" . $row->bpk . "</bPK>
		";*/

		$datei .= "
			<StaatsangehoerigkeitCode>" . $row->staatsbuergerschaft . "</StaatsangehoerigkeitCode>
			<HeimatPLZ>" . $plz . "</HeimatPLZ>
			<HeimatGemeinde>" . $gemeinde . "</HeimatGemeinde>
			<HeimatStrasse><![CDATA[" . $strasse . "]]></HeimatStrasse>
			<HeimatNation>" . $nation . "</HeimatNation>";

		if (!$ausserordentlich && !$incoming)
		{
			$datei .= "
			<ZustellPLZ>" . $zustell_plz . "</ZustellPLZ>
			<ZustellGemeinde>" . $zustell_gemeinde . "</ZustellGemeinde>
			<ZustellStrasse>" . $zustell_strasse . "</ZustellStrasse>
			<ZustellNation>" . $zustell_nation . "</ZustellNation>";
		}

		if ($co_name != '')
		{
			$datei .= "
			<coName>" . $co_name . "</coName>
			";
		}

		if ($email != '')
		{
			$datei .= "
			<eMailAdresse>" . $email . "</eMailAdresse>
			";
		}

		if(!$ausserordentlich)
		{
			$datei.="
			<ZugangCode>".$row->zgv_code."</ZugangCode>";
			$datei.="
			<ZugangDatum>".date("dmY", $datumobj->mktime_fromdate($row->zgvdatum))."</ZugangDatum>";
		}

		if($stgart==2) // Master-Studiengang
		{
			$datei.="
			<ZugangMaStgCode>".$row->zgvmas_code."</ZugangMaStgCode>";
			$datei.="
			<ZugangMaStgDatum>".date("dmY", $datumobj->mktime_fromdate($row->zgvmadatum))."</ZugangMaStgDatum>";
		}

		if($aktstatus!='Incoming' && !$ausserordentlich)
		{
			if($row->zgvmanation!='' && $stgart=='2')
				$ausstellungsstaat = $row->zgvmanation;
			elseif($row->zgvnation!='')
				$ausstellungsstaat = $row->zgvnation;
			else
				$ausstellungsstaat = $row->ausstellungsstaat;

			if($ausstellungsstaat!='' && ($datumobj->mktime_fromdate($beginndatum) > $datumobj->mktime_fromdate('2011-04-15')))
			{
				$datei.='
			<Ausstellungsstaat>'.$ausstellungsstaat.'</Ausstellungsstaat>';
			}
		}

		if($beginndatum!='' && !$ausserordentlich && $studtyp!='E')
		{
			$datei.="
			<BeginnDatum>".date("dmY", $datumobj->mktime_fromdate($beginndatum))."</BeginnDatum>";
		}

		if(($aktstatus=='Absolvent' || $aktstatus=='Abbrecher') && $studtyp!='E')
		{
			$datei.="
			<BeendigungsDatum>".date("dmY", $datumobj->mktime_fromdate($aktstatus_datum))."</BeendigungsDatum>";
		}

		/* Ausbildungssemester nicht anzeigen wenn
			Incoming
			Ausserordentlich Studierender
		*/
		if($aktstatus!='Incoming' && !$ausserordentlich  && $studtyp!='E')
		{
			$datei.="
			<Ausbildungssemester>".$sem."</Ausbildungssemester>";
		}

		if($studtyp!='E')
		{
			$datei.="
				<StudStatusCode>".$status."</StudStatusCode>";
		}

		if($orgform_code_array[$storgform]!=1 && !$ausserordentlich) // Wenn nicht Vollzeit und nicht Ausserordentlich
		{
			$datei.="
			<BerufstaetigkeitCode>".$row->berufstaetigkeit_code."</BerufstaetigkeitCode>";
		}

		if(!$ausserordentlich)
		{
			$datei.="
			<StandortCode>".$standortcode."</StandortCode>";
		}
		/*
		 * BMWFFoerderrung derzeit fuer alle Studierende auf Ja gesetzt
		 * Ausnahme:
		 *		ausserordnetliche Studierende
		 *		Incoming
		 *		Externe Teilnehmer an Gemeinsamen Studien
		 *
		 * ToDo: sollte pro Studierenden konfigurierbar sein
		 */
		if($aktstatus=='Incoming' || $ausserordentlich
			|| ($gemeinsamestudien && $kodex_studientyp_array[$row->gsstudientyp_kurzbz]=='E'))
			$bmwf='N';
		else
			$bmwf='J';

		$datei.="
			<BMWFWfoerderrelevant>".$bmwf."</BMWFWfoerderrelevant>";

		// **** IO Container ****/
		$qryio="SELECT * FROM bis.tbl_bisio WHERE student_uid=".$db->db_add_param($row->student_uid)."
					AND (von>".$db->db_add_param($bisprevious)." OR bis IS NULL OR bis>".$db->db_add_param($bisprevious).")
					AND von<=".$db->db_add_param($bisdatum).";";
		$outgoing_count=0;
		if($resultio = $db->db_query($qryio))
		{
			while($rowio = $db->db_fetch_object($resultio))
			{
				$mob=$rowio->mobilitaetsprogramm_code;
				$gast=$rowio->nation_code;
				$avon=date("dmY", $datumobj->mktime_fromdate($rowio->von));
				$abis=date("dmY", $datumobj->mktime_fromdate($rowio->bis));
				$adauer = (is_null($rowio->von) || is_null($rowio->bis))
					? null
					: $datumobj->DateDiff($rowio->von, $rowio->bis);

				// Aufenthaltszweckcode --------------------------------------------------------------------------------
				$bisio_zweck = new bisio();
				$bisio_zweck->getZweck($rowio->bisio_id);
				$zweck_code_arr = array();

				// Bei Incomings...
				if ($aktstatus == 'Incoming')
				{
					// ...max 1 Aufenthaltszweck
					if (count($bisio_zweck->result) > 1)
					{
						$error_log_io .= (!empty($error_log_io) ? ', ' : ''). "Es sind". count($bisio_zweck->result).
							" Aufenthaltszwecke eingetragen (max. 1 Zweck für Incomings)";
					}

					//...nur Zweck 1, 2 oder 3 erlaubt
					if (count($bisio_zweck->result) == 1 &&
						empty(array_intersect(array(1, 2, 3), array_column($bisio_zweck->result, 'zweck_code'))))
					{
						$error_log_io .= (!empty($error_log_io) ? ', ' : ''). "Aufenthaltszweckcode ist ".
							$bisio_zweck->result[0]->zweck_code. " (f&uuml;r Incomings ist nur Zweck 1, 2, 3 erlaubt)";
					}
				}

				foreach ($bisio_zweck->result as $row_zweck)
				{
					// Nur eindeutige Werte (bei Mehrfachangaben; trifft auf Outgoings zu)
					if (!in_array($row_zweck->zweck_code, $zweck_code_arr))
					{
						// Aufenthaltszweck 1, 2, 3 nicht gemeinsam melden
						if (in_array(1,$zweck_code_arr) && in_array(2,$zweck_code_arr) && in_array(3,$zweck_code_arr))
						{
							$error_log_io .= (!empty($error_log_io) ? ', ' : '').
								"Aufenthaltzweckcode 1, 2, 3 d&uuml;rfen nicht gemeinsam gemeldet werden";
						}

						$zweck_code_arr []= $row_zweck->zweck_code;
					}
				}

				// Aufenthaltfoerderungscode ---------------------------------------------------------------------------
				$aufenthaltfoerderung_code_arr = array();

				// Nur bei Outgoings Aufenthaltsfoerderungscode melden
				if ($aktstatus != 'Incoming') {
					$bisio_foerderung = new bisio();
					$bisio_foerderung->getFoerderungen($rowio->bisio_id);

					// ... mindestens 1 Aufenthaltfoerderung melden, wenn Auslandsaufenthalt >= 29 Tage
					if ((!$bisio_foerderung->result || count($bisio_foerderung->result) == 0) && $adauer >= 29)
					{
						$error_log_io .= (!empty($error_log_io) ? ', ' : '') .
							"Keine Aufenthaltsfoerderung angegeben (bei Outgoings >= 29 Tage Monat im Ausland muss mind. 1 gemeldet werden)";
					}

					foreach ($bisio_foerderung->result as $row_foerderung)
					{
						// ...wenn code = 5, nur ein Wert erlaubt (keine Mehrfachangaben)
						if ($row_foerderung->aufenthaltfoerderung_code == 5) {
							unset($aufenthaltfoerderung_code_arr);
							$aufenthaltfoerderung_code_arr [] = $row_foerderung->aufenthaltfoerderung_code;
							break;
						}

						// nur eindeutige Werte
						if (!in_array($row_foerderung->aufenthaltfoerderung_code, $aufenthaltfoerderung_code_arr)) {
							$aufenthaltfoerderung_code_arr [] = $row_foerderung->aufenthaltfoerderung_code;
						}
					}

					if($datumobj->mktime_fromdate($rowio->bis) < $datumobj->mktime_fromdate($bisdatum))
						$aufenthalt_finished = true;
					else
						$aufenthalt_finished = false;

					if ($rowio->ects_erworben == '' && $adauer >= 29 && $aufenthalt_finished)
					{
						$error_log_io .= (!empty($error_log_io) ? ', ' : '') .
							"Erworbene ECTS fehlen (Meldepflicht bei Outgoings >= 29 Tage Monat im Ausland)";
					}

					if ($rowio->ects_angerechnet == '' && $adauer >= 29 && $aufenthalt_finished)
					{
						$error_log_io .= (!empty($error_log_io) ? ', ' : '') .
							"Angerechnete ECTS fehlen (Meldepflicht bei Outgoings >= 29 Tage Monat im Ausland)";
					}
				}

				// Bei validen Daten errorlog ausgeben
				if($error_log_io != '')
				{
					$v.="<u>Bei Student (UID, Vorname, Nachname) '".$row->student_uid."', '".$row->nachname."', '".$row->vorname."' ($laststatus->status_kurzbz): </u>\n";
					if($error_log_io != '')
					{
						$v.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Fehler: ".$error_log_io. "\n";
					}
					$v.="\n";
					$error_log_io = '';
					return '';
				}
				// Bei validen Daten XML-Datensatz bauen
				else
				{
					$datei.="
					<IO>
						<MobilitaetsProgrammCode>".$mob."</MobilitaetsProgrammCode>
						<GastlandCode>".$gast."</GastlandCode>
						<AufenthaltVon>".$avon."</AufenthaltVon>";
						if($datumobj->mktime_fromdate($rowio->bis)<$datumobj->mktime_fromdate($bisdatum) && $datumobj->mktime_fromdate($rowio->bis)>$datumobj->mktime_fromdate($bisprevious))
						{
							$datei.="
							<AufenthaltBis>".$abis."</AufenthaltBis>";
						}

						foreach ($zweck_code_arr as $zweck)
						{
							$datei.="
							<AufenthaltZweckCode>". $zweck. "</AufenthaltZweckCode>";
						}
						if ($aktstatus != 'Incoming' && $rowio->ects_erworben != '')
						{
							$datei.="
							<ECTSerworben>".$rowio->ects_erworben."</ECTSerworben>";
						}
						if ($aktstatus != 'Incoming' && $rowio->ects_angerechnet != '')
						{
							$datei.="
							<ECTSangerechnet>".$rowio->ects_angerechnet."</ECTSangerechnet>";
						}
						foreach ($aufenthaltfoerderung_code_arr as $aufenthaltfoerderung_code)
						{
							$datei.="
							<AufenthaltFoerderungCode>". $aufenthaltfoerderung_code. "</AufenthaltFoerderungCode>";
						}

					$datei.="
					</IO>";
				}



				if($aktstatus!='Incoming')
				{
					if(!isset($iosem[$storgform][$sem]))
					{
						$iosem[$storgform][$sem]=0;
					}
					$iosem[$storgform][$sem]++;
					$outgoing_count++;
				}
				else
				{
					if(!isset($iosem[0]))
					{
						$iosem[0]=0;
					}
					$iosem[0]++;
				}
			}
		}
		$datei.= $gsblock;
		$datei.="
		</StudentIn>";

		// Aktive Studierende - keine Incoming, keine Externen GS
		if(($aktstatus=='Student' || $aktstatus=='Diplomand' || $aktstatus=='Praktikant' || $aktstatus=='Outgoing')
			&& !($gemeinsamestudien && $kodex_studientyp_array[$row->gsstudientyp_kurzbz]=='E'))
		{
			if(!isset($stsem[$storgform][$sem]))
			{
				$stsem[$storgform][$sem]=0;
			}
			$stsem[$storgform][$sem]++;
		}
		if($aktstatus=='Unterbrecher')
		{
			if(!isset($usem[$storgform][$sem]))
			{
				$usem[$storgform][$sem]=0;
			}
			$usem[$storgform][$sem]++;
		}
		if($aktstatus=='Abbrecher')
		{
			if(!isset($asem[$storgform][$sem]))
			{
				$asem[$storgform][$sem]=0;
			}
			$asem[$storgform][$sem]++;
		}
		if($aktstatus=='Absolvent')
		{
			if(!isset($absem[$storgform][$sem]))
			{
				$absem[$storgform][$sem]=0;
			}
			$absem[$storgform][$sem]++;
		}
	}
	if(!in_array($storgform, $verwendete_orgformen))
		$verwendete_orgformen[]=$storgform;

	$status = '';
	if($gsstatus!='')
		$status = $gsstatus;
	else
		$status = $aktstatus;
	if($outgoing_count>0)
	{
		$status .= ' ( Outgoing ';
		if($outgoing_count>1)
			$status.= $outgoing_count.'x';
		$status .= ')';
	}

	//Studentenliste
	$stlist.="
	<tr>
		<td align=center>".trim($row->student_uid)."</td>
		<td align=center>".trim($row->matrikelnr)."</td>
		<td>".trim($row->nachname)."</td>
		<td>".trim($row->vorname)."</td>
		<td>".$status."</td>
		<td align=center>".trim($sem)."</td>
		<td align=center>".trim($storgform)."</td>
	</tr>";
	return $datei;
}

/**
 * Erstellt die Bewerbermeldung
 *
 * Wenn der Parameter orgformcode uebergeben wird, werden nur die Bewerberzahlen dieser Orgform geliefert
 * sonst alle
 */
function GenerateXMLBewerberBlock($orgformcode=null)
{
	global $db;
	global $ssem, $stgart, $psem;
	global $stg_kz, $bisdatum;
	global $bwlist, $orgform_kurzbz;
	global $bewerbercount,$orgform_code_array;
	$datei = '';
	$bewerberM=array();
	$bewerberW=array();

	if(mb_strstr($ssem,"WS"))
	{
		//Bewerber
		$qrybw="
		SELECT
			*, transform_geschlecht(tbl_person.geschlecht, tbl_person.gebdatum) as geschlecht_imputiert
		FROM
			public.tbl_prestudent
			JOIN public.tbl_prestudentstatus ON(tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id)
			JOIN public.tbl_person USING(person_id)
			LEFT JOIN bis.tbl_orgform USING(orgform_kurzbz)
		WHERE (studiensemester_kurzbz=".$db->db_add_param($ssem)." OR studiensemester_kurzbz=".$db->db_add_param($psem).")
			AND tbl_prestudent.studiengang_kz=".$db->db_add_param($stg_kz)."
			AND (tbl_prestudentstatus.datum<=".$db->db_add_param($bisdatum).")
			AND status_kurzbz='Bewerber'
			AND reihungstestangetreten
			";
		if(!is_null($orgformcode))
			$qrybw.=" AND tbl_orgform.code=".$db->db_add_param($orgformcode);

		if($resultbw = $db->db_query($qrybw))
		{
			while($rowbw = $db->db_fetch_object($resultbw))
			{
				// Bachelor / Diplom
				if(($stgart==1 || $stgart==3) && $rowbw->zgv_code!=NULL)
				{
					if(strtoupper($rowbw->geschlecht_imputiert)=='M')
					{
						if(!isset($bewerberM[$rowbw->zgv_code]))
						{
							$bewerberM[$rowbw->zgv_code]=0;
						}
						$bewerberM[$rowbw->zgv_code]++;
					}
					else
					{
						if(!isset($bewerberW[$rowbw->zgv_code]))
						{
							$bewerberW[$rowbw->zgv_code]=0;
						}
						$bewerberW[$rowbw->zgv_code]++;
					}
				}
				// Master
				if($stgart==2 && $rowbw->zgvmas_code!=NULL)
				{
					if(strtoupper($rowbw->geschlecht_imputiert)=='M')
					{
						if(!isset($bewerberM[$rowbw->zgvmas_code]))
						{
							$bewerberM[$rowbw->zgvmas_code]=0;
						}
						$bewerberM[$rowbw->zgvmas_code]++;
					}
					else
					{
						if(!isset($bewerberW[$rowbw->zgvmas_code]))
						{
							$bewerberW[$rowbw->zgvmas_code]=0;
						}
						$bewerberW[$rowbw->zgvmas_code]++;
					}
				}
				$bworgform = ($rowbw->orgform_kurzbz!=''?$rowbw->orgform_kurzbz:$orgform_kurzbz);

				if(isset($bewerbercount[0]))
					$bewerbercount[0]++;
				else
					$bewerbercount[0]=1;
				if(isset($bewerbercount[$bworgform]))
					$bewerbercount[$bworgform]++;
				else
					$bewerbercount[$bworgform]=1;

				if($rowbw->geschlecht_imputiert!=$rowbw->geschlecht)
					$geschlecht_imputiert = ' -> '.$rowbw->geschlecht_imputiert;
				else
					$geschlecht_imputiert = '';

				$bwlist.='
				<tr>
					<td>'.trim($rowbw->nachname).'</td>
					<td>'.trim($rowbw->vorname).'</td>
					<td>'.$bworgform.'</td>
					<td>'.$rowbw->geschlecht.$geschlecht_imputiert.'</td>
				</tr>';
			}
		}

		foreach(array_keys($bewerberM) as $key)
			if(!isset($bewerberW[$key]))
				$bewerberW[$key]=0;

		foreach(array_keys($bewerberW) as $key)
		{
			if(!isset($bewerberM[$key]))
				$bewerberM[$key]=0;
			$datei.="
		<BewerberInnen>
			<OrgFormCode>".$orgform_code_array[$bworgform]."</OrgFormCode>";
			if($stgart==2)
				$datei.='
			<ZugangMaStgCode>'.$key.'</ZugangMaStgCode>';
			else
				$datei.='
			<ZugangCode>'.$key.'</ZugangCode>';

			$datei.='
			<AnzBewerberM>'.$bewerberM[$key].'</AnzBewerberM>
			<AnzBewerberW>'.$bewerberW[$key].'</AnzBewerberW>
		</BewerberInnen>';
		}
	}
	return $datei;
}
?>
