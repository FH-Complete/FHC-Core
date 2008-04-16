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
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
if(isset($_GET['xmlformat']) && $_GET['xmlformat']=='xml')
	echo '<?xml version="1.0" encoding="ISO-8859-15" standalone="yes"?>';
else
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/student.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/datum.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/studiengang.class.php');

// *********** Funktionen *************************++
function convdate($date)
{
	list($d,$m,$y) = explode('.',$date);
	return $y.'-'.$m.'-'.$d;
}

function checkfilter($row, $filter2)
{
	global $conn, $studiensemester_kurzbz, $kontofilterstg;

	if($filter2=='dokumente')
	{
		// Alle Personen die noch nicht alle Dokumente gebracht haben
		$qry = "SELECT count(*) as anzahl FROM public.tbl_dokumentstudiengang WHERE
				dokument_kurzbz NOT IN(
					SELECT dokument_kurzbz FROM tbl_dokumentprestudent WHERE prestudent_id='$row->prestudent_id')
				AND studiengang_kz='$row->studiengang_kz'";
		if($result_filter = pg_query($conn, $qry))
			if($row_filter = pg_fetch_object($result_filter))
				if($row_filter->anzahl==0)
					return false;
	}
	elseif($filter2=='konto')
	{
		// Alle Personen die offene Buchungen haben
		$qry = "SELECT sum(betrag) as summe FROM tbl_konto WHERE person_id='$row->person_id'";
		if($kontofilterstg=='true')
			$qry.=" AND studiengang_kz='$row->studiengang_kz'";
		//echo $qry;
		if($result_filter = pg_query($conn, $qry))
			if($row_filter = pg_fetch_object($result_filter))
				if($row_filter->summe=='0.00' || $row_filter->summe=='' || $row_filter->summe=='0')
					return false;
	}
	elseif($filter2=='studiengebuehr')
	{
		// Alle Personen die keine Studiengebuehrbelastung haben 
		// Incoming werden nicht beruecksichtigt
		$prestudent = new prestudent($conn, null, null);
		$prestudent->getLastStatus($row->prestudent_id);
		
		$qry = "SELECT count(*) as anzahl FROM public.tbl_konto WHERE 
					studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND 
					person_id='".addslashes($row->person_id)."' AND 
					buchungstyp_kurzbz='Studiengebuehr'";
		if($result_filter = pg_query($conn, $qry))
			if($row_filter = pg_fetch_object($result_filter))
				if($row_filter->anzahl>0 || $prestudent->rolle_kurzbz=='Incoming')
					return false;
	}
	elseif(strstr($filter2,'buchungstyp;'))
	{
		// Alle Personen die keine Belastung auf den uebergebenen Buchungstyp haben 
		// Incoming werden nicht beruecksichtigt
		list($filter, $buchungstyp) = split(';',$filter2);
		$prestudent = new prestudent($conn, null, null);
		$prestudent->getLastStatus($row->prestudent_id);
		
		$qry = "SELECT count(*) as anzahl FROM public.tbl_konto WHERE 
					studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND 
					person_id='".addslashes($row->person_id)."' AND 
					buchungstyp_kurzbz='$buchungstyp'";
		if($result_filter = pg_query($conn, $qry))
			if($row_filter = pg_fetch_object($result_filter))
				if($row_filter->anzahl>0 || $prestudent->rolle_kurzbz=='Incoming')
					return false;
	}
	elseif($filter2=='zgvohnedatum')
	{
		//Alle Personen die den ZGV Typ eingetragen haben aber noch kein Datum
		$qry = "SELECT zgv_code, zgvdatum, zgvmas_code, zgvmadatum FROM public.tbl_prestudent WHERE prestudent_id='$row->prestudent_id'";
		if($result_filter = pg_query($conn, $qry))
		{
			if($row_filter = pg_fetch_object($result_filter))
			{
				if(($row_filter->zgv_code!='' && $row_filter->zgvdatum=='') ||
				   ($row_filter->zgvmas_code!='' && $row_filter->zgvmadatum==''))
				   	return true;
				else 
					return false;
			}
		}			
	}
	return true;
}

function draw_content_liste($row)
{
	global $rdf_url, $datum_obj, $conn, $stg_arr;
	$status='';

	/*$mail_privat = '';
	$qry_mail = "SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp='email' AND person_id='$row->person_id' AND zustellung=true ORDER BY kontakt_id DESC LIMIT 1";
	if($result_mail = pg_query($conn, $qry_mail))
	{
		if($row_mail = pg_fetch_object($result_mail))
		{
			$mail_privat = $row_mail->kontakt;
		}
	}*/

	$prestudent = new prestudent($conn, null, null);
	$prestudent->getLastStatus($row->prestudent_id);
	$status = $prestudent->rolle_kurzbz;
	$orgform = $prestudent->orgform_kurzbz;

	echo '
	  <RDF:li>
      	<RDF:Description  id="'.$row->prestudent_id.'"  about="'.$rdf_url.'/'.$row->prestudent_id.'" >
        	<STUDENT:person_id><![CDATA['.$row->person_id.']]></STUDENT:person_id>
        	<STUDENT:prestudent_id><![CDATA['.$row->prestudent_id.']]></STUDENT:prestudent_id>
        	<STUDENT:uid><![CDATA['.(isset($row->uid)?$row->uid:'').']]></STUDENT:uid>
    		<STUDENT:titelpre><![CDATA['.$row->titelpre.']]></STUDENT:titelpre>
    		<STUDENT:titelpost><![CDATA['.$row->titelpost.']]></STUDENT:titelpost>
    		<STUDENT:vornamen><![CDATA['.$row->vornamen.']]></STUDENT:vornamen>
    		<STUDENT:vorname><![CDATA['.$row->vorname.']]></STUDENT:vorname>
    		<STUDENT:nachname><![CDATA['.$row->nachname.']]></STUDENT:nachname>
    		<STUDENT:svnr>'.($row->svnr==''?'&#xA0;':'<![CDATA['.$row->svnr.']]>').'</STUDENT:svnr>
    		<STUDENT:ersatzkennzeichen>'.($row->ersatzkennzeichen==''?'&#xA0;':'<![CDATA['.$row->ersatzkennzeichen.']]>').'</STUDENT:ersatzkennzeichen>
    		<STUDENT:geburtsdatum><![CDATA['.$datum_obj->convertISODate($row->gebdatum).']]></STUDENT:geburtsdatum>
    		<STUDENT:geburtsdatum_iso><![CDATA['.$row->gebdatum.']]></STUDENT:geburtsdatum_iso>
			<STUDENT:semester><![CDATA['.(isset($row->semester)?$row->semester:'').']]></STUDENT:semester>
    		<STUDENT:verband><![CDATA['.(isset($row->verband)?$row->verband:'').']]></STUDENT:verband>
    		<STUDENT:gruppe><![CDATA['.(isset($row->gruppe)?$row->gruppe:'').']]></STUDENT:gruppe>
			<STUDENT:matrikelnummer><![CDATA['.(isset($row->matrikelnr)?$row->matrikelnr:'').']]></STUDENT:matrikelnummer>
    		<STUDENT:mail_privat><![CDATA['.$row->email_privat.']]></STUDENT:mail_privat>
    		<STUDENT:mail_intern><![CDATA['.(isset($row->uid)?$row->uid.'@'.DOMAIN:'').']]></STUDENT:mail_intern>
			<STUDENT:status><![CDATA['.$status.']]></STUDENT:status>
    		<STUDENT:anmerkungen>'.($row->anmerkungen==''?'&#xA0;':'<![CDATA['.$row->anmerkungen.']]>').'</STUDENT:anmerkungen>
    		<STUDENT:anmerkungpre>'.($row->anmerkung==''?'&#xA0;':'<![CDATA['.$row->anmerkung.']]>').'</STUDENT:anmerkungpre>
    		<STUDENT:studiengang_kz><![CDATA['.$row->studiengang_kz.']]></STUDENT:studiengang_kz>
			<STUDENT:studiengang><![CDATA['.$stg_arr[$row->studiengang_kz].']]></STUDENT:studiengang>
			<STUDENT:orgform><![CDATA['.$orgform.']]></STUDENT:orgform>
			<STUDENT:aufmerksamdurch_kurzbz><![CDATA['.$row->aufmerksamdurch_kurzbz.']]></STUDENT:aufmerksamdurch_kurzbz>
      	</RDF:Description>
      </RDF:li>';
}

function draw_content($row)
{
	global $rdf_url, $datum_obj, $conn;
	$status='';

	$mail_privat = '';
	$qry_mail = "SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp='email' AND person_id='$row->person_id' AND zustellung=true ORDER BY kontakt_id DESC LIMIT 1";
	if($result_mail = pg_query($conn, $qry_mail))
	{
		if($row_mail = pg_fetch_object($result_mail))
		{
			$mail_privat = $row_mail->kontakt;
		}
	}

	if($row->prestudent_id!='')
	{
		$prestudent = new prestudent($conn, null, null);
		$prestudent->getLastStatus($row->prestudent_id);
		$status = $prestudent->rolle_kurzbz;
		$orgform = $prestudent->orgform_kurzbz;
		
		if($status=='Aufgenommener' || $status=='Bewerber' || $status=='Wartender' || $status=='Interessent')
			$semester_prestudent = $prestudent->ausbildungssemester;
		else 
			$semester_prestudent = '';
	echo '
	  <RDF:li>
      	<RDF:Description  id="'.$row->prestudent_id.'"  about="'.$rdf_url.'/'.$row->prestudent_id.'" >
        	<STUDENT:person_id><![CDATA['.$row->person_id.']]></STUDENT:person_id>
    		<STUDENT:titelpre><![CDATA['.$row->titelpre.']]></STUDENT:titelpre>
    		<STUDENT:titelpost><![CDATA['.$row->titelpost.']]></STUDENT:titelpost>
    		<STUDENT:vornamen><![CDATA['.$row->vornamen.']]></STUDENT:vornamen>
    		<STUDENT:vorname><![CDATA['.$row->vorname.']]></STUDENT:vorname>
    		<STUDENT:nachname><![CDATA['.$row->nachname.']]></STUDENT:nachname>
    		<STUDENT:geburtsdatum><![CDATA['.$datum_obj->convertISODate($row->gebdatum).']]></STUDENT:geburtsdatum>
    		<STUDENT:geburtsdatum_iso><![CDATA['.$row->gebdatum.']]></STUDENT:geburtsdatum_iso>
    		<STUDENT:homepage><![CDATA['.$row->homepage.']]></STUDENT:homepage>
    		<STUDENT:gebort><![CDATA['.$row->gebort.']]></STUDENT:gebort>
    		<STUDENT:gebzeit><![CDATA['.$row->gebzeit.']]></STUDENT:gebzeit>
    		<STUDENT:anmerkungen>'.($row->anmerkungen==''?'&#xA0;':'<![CDATA['.$row->anmerkungen.']]>').'</STUDENT:anmerkungen>
    		<STUDENT:anrede><![CDATA['.$row->anrede.']]></STUDENT:anrede>
    		<STUDENT:svnr><![CDATA['.$row->svnr.']]></STUDENT:svnr>
    		<STUDENT:ersatzkennzeichen><![CDATA['.$row->ersatzkennzeichen.']]></STUDENT:ersatzkennzeichen>
    		<STUDENT:familienstand><![CDATA['.$row->familienstand.']]></STUDENT:familienstand>
    		<STUDENT:geschlecht><![CDATA['.$row->geschlecht.']]></STUDENT:geschlecht>
    		<STUDENT:anzahlkinder><![CDATA['.$row->anzahlkinder.']]></STUDENT:anzahlkinder>
    		<STUDENT:staatsbuergerschaft><![CDATA['.$row->staatsbuergerschaft.']]></STUDENT:staatsbuergerschaft>
    		<STUDENT:geburtsnation><![CDATA['.$row->geburtsnation.']]></STUDENT:geburtsnation>
    		<STUDENT:sprache><![CDATA['.$row->sprache.']]></STUDENT:sprache>
    		<STUDENT:status><![CDATA['.$status.']]></STUDENT:status>
    		<STUDENT:orgform><![CDATA['.$orgform.']]></STUDENT:orgform>
    		<STUDENT:mail_privat><![CDATA['.$mail_privat.']]></STUDENT:mail_privat>
    		<STUDENT:mail_intern><![CDATA['.(isset($row->uid)?$row->uid.'@'.DOMAIN:'').']]></STUDENT:mail_intern>

    		<STUDENT:aktiv><![CDATA['.((isset($row->bnaktiv) && $row->bnaktiv)?'true':'false').']]></STUDENT:aktiv>
    		<STUDENT:uid><![CDATA['.(isset($row->uid)?$row->uid:'').']]></STUDENT:uid>
    		<STUDENT:matrikelnummer><![CDATA['.(isset($row->matrikelnr)?$row->matrikelnr:'').']]></STUDENT:matrikelnummer>
			<STUDENT:alias><![CDATA['.(isset($row->alias)?$row->alias:'').']]></STUDENT:alias>
    		<STUDENT:semester><![CDATA['.(isset($row->semester)?$row->semester:$semester_prestudent).']]></STUDENT:semester>
    		<STUDENT:verband><![CDATA['.(isset($row->verband)?$row->verband:'').']]></STUDENT:verband>
    		<STUDENT:gruppe><![CDATA['.(isset($row->gruppe)?$row->gruppe:'').']]></STUDENT:gruppe>
			<STUDENT:studiengang_kz_student><![CDATA['.(is_a($row,'student')?$row->studiengang_kz:'').']]></STUDENT:studiengang_kz_student>';
	}
}

function draw_prestudent($row)
{
	global $rdf_url, $datum_obj, $stg_arr;
	if($row->prestudent_id!='')
	{
	echo '
			<STUDENT:prestudent_id><![CDATA['.$row->prestudent_id.']]></STUDENT:prestudent_id>
    		<STUDENT:studiengang_kz_prestudent><![CDATA['.$row->studiengang_kz.']]></STUDENT:studiengang_kz_prestudent>
			<STUDENT:studiengang_kz><![CDATA['.$row->studiengang_kz.']]></STUDENT:studiengang_kz>
			<STUDENT:aufmerksamdurch_kurzbz><![CDATA['.$row->aufmerksamdurch_kurzbz.']]></STUDENT:aufmerksamdurch_kurzbz>
			<STUDENT:studiengang><![CDATA['.$stg_arr[$row->studiengang_kz].']]></STUDENT:studiengang>
			<STUDENT:berufstaetigkeit_code><![CDATA['.$row->berufstaetigkeit_code.']]></STUDENT:berufstaetigkeit_code>
			<STUDENT:ausbildungcode><![CDATA['.$row->ausbildungcode.']]></STUDENT:ausbildungcode>
			<STUDENT:zgv_code><![CDATA['.$row->zgv_code.']]></STUDENT:zgv_code>
			<STUDENT:zgvort><![CDATA['.$row->zgvort.']]></STUDENT:zgvort>
			<STUDENT:zgvdatum><![CDATA['.$datum_obj->convertISODate($row->zgvdatum).']]></STUDENT:zgvdatum>
			<STUDENT:zgvdatum_iso><![CDATA['.$row->zgvdatum.']]></STUDENT:zgvdatum_iso>
			<STUDENT:zgvmas_code><![CDATA['.$row->zgvmas_code.']]></STUDENT:zgvmas_code>
			<STUDENT:zgvmaort><![CDATA['.$row->zgvmaort.']]></STUDENT:zgvmaort>
			<STUDENT:zgvmadatum><![CDATA['.$datum_obj->convertISODate($row->zgvmadatum).']]></STUDENT:zgvmadatum>
			<STUDENT:zgvmadatum_iso><![CDATA['.$row->zgvmadatum.']]></STUDENT:zgvmadatum_iso>
			<STUDENT:aufnahmeschluessel><![CDATA['.$row->aufnahmeschluessel.']]></STUDENT:aufnahmeschluessel>
			<STUDENT:facheinschlberuf><![CDATA['.($row->facheinschlberuf?'true':'false').']]></STUDENT:facheinschlberuf>
			<STUDENT:reihungstest_id><![CDATA['.$row->reihungstest_id.']]></STUDENT:reihungstest_id>
			<STUDENT:anmeldungreihungstest><![CDATA['.$datum_obj->convertISODate($row->anmeldungreihungstest).']]></STUDENT:anmeldungreihungstest>
			<STUDENT:anmeldungreihungstest_iso><![CDATA['.$row->anmeldungreihungstest.']]></STUDENT:anmeldungreihungstest_iso>
			<STUDENT:reihungstestangetreten><![CDATA['.($row->reihungstestangetreten?'true':'false').']]></STUDENT:reihungstestangetreten>
			<STUDENT:punkte><![CDATA['.$row->punkte.']]></STUDENT:punkte>
			<STUDENT:bismelden><![CDATA['.($row->bismelden?'true':'false').']]></STUDENT:bismelden>
			<STUDENT:anmerkungpre><![CDATA['.$row->anmerkung.']]></STUDENT:anmerkungpre>
      	</RDF:Description>
      </RDF:li>';
	}
}

// ******* Init **************************
// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';


if(isset($_SERVER['REMOTE_USER']))
{
	$user = get_uid();
	loadVariables($conn, $user);
}

$gruppe_kurzbz=(isset($_GET['gruppe_kurzbz'])?$_GET['gruppe_kurzbz']:null);
$gruppe=(isset($_GET['gruppe'])?$_GET['gruppe']:null);
$verband=(isset($_GET['verband'])?$_GET['verband']:null);
$semester=(isset($_GET['semester'])?$_GET['semester']:null);
$studiengang_kz=(isset($_GET['studiengang_kz'])?$_GET['studiengang_kz']:null);
$studiensemester_kurzbz = (isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:null);
$uid = (isset($_GET['uid'])?$_GET['uid']:null);
$typ = (isset($_GET['typ'])?$_GET['typ']:null);
$prestudent_id = (isset($_GET['prestudent_id'])?$_GET['prestudent_id']:null);
$filter = (isset($_GET['filter'])?$_GET['filter']:null);
$ss = (isset($_GET['ss'])?$_GET['ss']:null);
$filter2 = (isset($_GET['filter2'])?$_GET['filter2']:null);

if($studiensemester_kurzbz=='aktuelles')
	$studiensemester_kurzbz = $semester_aktuell;

if(isset($_GET['xmlformat']) && $_GET['xmlformat']=='xml')
	$xmlformat='xml';
else
	$xmlformat='rdf';

$datum_obj = new datum();

// ************ Beginn **************************

if($xmlformat=='rdf')
{
	$stg_arr = array();
	$stg_obj = new studiengang($conn, null, null);
	$stg_obj->getAll(null, false);
	foreach ($stg_obj->result as $row)
		$stg_arr[$row->studiengang_kz]=$row->kuerzel;

	$rdf_url='http://www.technikum-wien.at/student';
	echo '
	<RDF:RDF
		xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
		xmlns:STUDENT="'.$rdf_url.'/rdf#"
	>


	  <RDF:Seq about="'.$rdf_url.'/alle">
	';

	if(isset($uid))
	{
		$student=new student($conn,null,true);
		$student->load($uid, $studiensemester_kurzbz);
		$prestd = new prestudent($conn, null, true);

		draw_content($student);
		$prestd->load($student->prestudent_id);
		draw_prestudent($prestd);
	}
	if($typ=='student')
	{
		// Studenten holen
		$where = '';
		if ($gruppe_kurzbz!=null)
		{
			$where=" gruppe_kurzbz='".$gruppe_kurzbz."' ";
			if($studiensemester_kurzbz!=null)
				$where.=" AND tbl_benutzergruppe.studiensemester_kurzbz='$studiensemester_kurzbz'";
		}
		else
		{
			$where.=" tbl_studentlehrverband.studiengang_kz=$studiengang_kz";
			if ($semester!=null)
				$where.=" AND tbl_studentlehrverband.semester=$semester";
			if ($verband!=null)
				$where.=" AND tbl_studentlehrverband.verband='".$verband."'";
			if ($gruppe!=null)
				$where.=" AND tbl_studentlehrverband.gruppe='".$gruppe."'";
		}

		$where.=" AND tbl_studentlehrverband.studiensemester_kurzbz='$studiensemester_kurzbz'";

		$sql_query="SET CLIENT_ENCODING TO 'UNICODE';
					SELECT p.person_id, tbl_student.prestudent_id, tbl_benutzer.uid, titelpre, titelpost,	vorname, vornamen,
						nachname, gebdatum, tbl_prestudent.anmerkung,ersatzkennzeichen,svnr, tbl_student.matrikelnr, p.anmerkung as anmerkungen,
						tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
						tbl_studentlehrverband.studiengang_kz, aufmerksamdurch_kurzbz, 
						(	SELECT kontakt
							FROM public.tbl_kontakt
							WHERE kontakttyp='email' AND person_id=p.person_id AND zustellung
							LIMIT 1
						)
						AS email_privat
						FROM public.tbl_studentlehrverband JOIN public.tbl_student USING (student_uid)
							JOIN public.tbl_benutzer ON (student_uid=uid) JOIN public.tbl_person p USING (person_id)  JOIN public.tbl_prestudent USING(prestudent_id) ";
		if($gruppe_kurzbz!=null)
			$sql_query.= "JOIN public.tbl_benutzergruppe USING (uid) ";
		$sql_query.="WHERE ".$where.' ORDER BY nachname, vorname';
		//echo $sql_query;
		if($result = pg_query($conn, $sql_query))
			while($row = pg_fetch_object($result))
			{
				if(checkfilter($row, $filter2))
					draw_content_liste($row);
			}
	}
	elseif($typ=='incoming')
	{
		$qry = "SELECT prestudent_id FROM public.tbl_prestudentrolle WHERE rolle_kurzbz='Incoming' AND studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		if($result = pg_query($conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$student=new student($conn,null,true);
				if($uid = $student->getUid($row->prestudent_id))
				{
					//Wenn kein Eintrag fuers aktuelle Studiensemester da ist, dann
					//nochmal laden aber ohne studiensemester
					if(!$student->load($uid, $studiensemester_kurzbz))
						$student->load($uid);
				}
				$prestd = new prestudent($conn, null, true);
				$prestd->load($row->prestudent_id);
				if($uid!='')
				{
					draw_content($student);
					draw_prestudent($prestd);
				}
				else
				{
					draw_content($prestd);
					draw_prestudent($prestd);
				}
			}
		}
	}
	elseif(in_array($typ, array('prestudent', 'interessenten','bewerber','aufgenommen',
	                      'warteliste','absage','zgv','reihungstestangemeldet',
	                      'reihungstestnichtangemeldet')))
	{
		$prestd = new prestudent($conn, null, true);

		if($studiengang_kz!=null)
		{
			if($prestd->loadIntessentenUndBewerber($studiensemester_kurzbz, $studiengang_kz, $semester, $typ))
			{
				foreach ($prestd->result as $row)
				{
					if(checkfilter($row, $filter2))
					{
						$student=new student($conn,null,true);
						if($uid = $student->getUid($row->prestudent_id))
						{
							if(!$student->load($uid, $studiensemester_kurzbz))
								$student->load($uid);
							draw_content($student);
						}
						else
							draw_content($row);
						draw_prestudent($row);
					}
				}
			}
		}
		elseif($prestudent_id!=null)
		{
			if($prestd->load($prestudent_id))
			{
				draw_content($prestd);
				draw_prestudent($prestd);
			}
			else
				echo $prestd->errormsg;
		}
		else
		{
			echo 'Falsche Parameteruebergabe';
		}
	}
	else
	{
		if($filter!='')
		{
			$filter = utf8_decode($filter);
			$qry = "SELECT prestudent_id 
					FROM 
						public.tbl_person JOIN tbl_prestudent USING (person_id) LEFT JOIN tbl_student using(prestudent_id) 
					WHERE 
						nachname ~* '".addslashes($filter)."' OR 
						vorname ~* '".addslashes($filter)."' OR
						student_uid ~* '".addslashes($filter)."' OR
						matrikelnr = '".addslashes($filter)."';";
			if($result = pg_query($conn, $qry))
			{
				while($row = pg_fetch_object($result))
				{
					$student=new student($conn,null,true);
					if($uid = $student->getUid($row->prestudent_id))
					{
						//Wenn kein Eintrag fuers aktuelle Studiensemester da ist, dann
						//nochmal laden aber ohne studiensemester
						if(!$student->load($uid, $studiensemester_kurzbz))
							$student->load($uid);
					}
					$prestd = new prestudent($conn, null, true);
					$prestd->load($row->prestudent_id);
					if($uid!='')
					{
						draw_content($student);
						draw_prestudent($prestd);
					}
					else
					{
						draw_content($prestd);
						draw_prestudent($prestd);
					}
				}
			}
		}
		elseif(isset($prestudent_id))
		{
			$student=new student($conn,null,true);
			if($uid = $student->getUid($prestudent_id))
			{
				//Wenn kein Eintrag fuers aktuelle Studiensemester da ist, dann
				//nochmal laden aber ohne studiensemester
				if(!$student->load($uid, $studiensemester_kurzbz))
					$student->load($uid);
			}
			$prestd = new prestudent($conn, null, true);
			$prestd->load($prestudent_id);
			if($uid!='')
			{
				draw_content($student);
				draw_prestudent($prestd);
			}
			else
			{
				draw_content($prestd);
				draw_prestudent($prestd);
			}
		}


	}


	echo "</RDF:Seq>\n</RDF:RDF>";
}
else
{
	//XML
	$uids = split(';',$uid);
	echo '<studenten>';
	foreach ($uids as $uid)
	{
		if($uid!='')
		{
			$student = new student($conn);
			$student->load($uid);

			$studiengang = new studiengang($conn);
			$studiengang->load($student->studiengang_kz);

			$typ='';
			switch($studiengang->typ)
			{
				case 'd':	$typ = 'FH-Diplom-Studiengang';
							break;
				case 'm':	$typ = 'FH-Master-Studiengang';
							break;
				case 'b':	$typ = 'FH-Bachelor-Studiengang';
							break;
				default:	$typ = 'FH-Studiengang';
			}

			$qry = "SELECT * FROM campus.vw_benutzer JOIN public.tbl_benutzerfunktion USING(uid) WHERE funktion_kurzbz='rek'";
			$rektor = '';
			if($result = pg_query($conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$rektor = $row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost;
				}
			}

			$studiengbeginn = '';
			$studiensemester_kurzbz='';
			$qry = "SELECT * FROM public.tbl_prestudentrolle JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE prestudent_id='$student->prestudent_id' AND rolle_kurzbz in('Student','Incoming') ORDER BY datum LIMIT 1";
			if($result = pg_query($conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$studienbeginn = $row->start;
					$studiensemester = $row->studiensemester_kurzbz;
				}
			}

			$stsem = new studiensemester($conn);
			//$aktstsem = $stsem->getaktorNext();

			$stsem->load($ss);

			$qry = "SELECT * FROM public.tbl_prestudentrolle WHERE prestudent_id='$student->prestudent_id' AND studiensemester_kurzbz='$ss' ORDER BY datum DESC";
			$semester=0;
			if($result = pg_query($conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$semester = $row->ausbildungssemester;
				}
			}

			echo '
			<student>
				<uid><![CDATA['.$student->uid.']]></uid>
				<person_id><![CDATA['.$student->person_id.']]></person_id>
				<titelpre><![CDATA['.$student->titelpre.']]></titelpre>
				<titelpost><![CDATA['.$student->titelpost.']]></titelpost>
				<vornamen><![CDATA['.$student->vornamen.']]></vornamen>
				<vorname><![CDATA['.$student->vorname.']]></vorname>
				<nachname><![CDATA['.$student->nachname.']]></nachname>
				<matrikelnummer><![CDATA['.$student->matrikelnr.']]></matrikelnummer>
				<geburtsdatum><![CDATA['.$datum_obj->convertISODate($student->gebdatum).']]></geburtsdatum>
				<geburtsdatum_iso><![CDATA['.$student->gebdatum.']]></geburtsdatum_iso>
				<semester><![CDATA['.$semester.']]></semester>
				<verband><![CDATA['.$student->verband.']]></verband>
				<gruppe><![CDATA['.$student->gruppe.']]></gruppe>
				<studiengang_kz><![CDATA['.sprintf("%04d",$student->studiengang_kz).']]></studiengang_kz>
				<studiengang_bezeichnung><![CDATA['.$studiengang->bezeichnung.']]></studiengang_bezeichnung>
				<studiengang_art><![CDATA['.$typ.']]></studiengang_art>
				<anrede><![CDATA['.$student->anrede.']]></anrede>
				<svnr><![CDATA['.$student->svnr.']]></svnr>
				<ersatzkennzeichen><![CDATA['.$student->ersatzkennzeichen.']]></ersatzkennzeichen>
				<familienstand><![CDATA['.$student->familienstand.']]></familienstand>
				<rektor><![CDATA['.$rektor.']]></rektor>
				<studienbeginn_beginn><![CDATA['.$datum_obj->convertISODate($studienbeginn).']]></studienbeginn_beginn>
				<studiensemester_beginn><![CDATA['.$studiensemester.']]></studiensemester_beginn>
				<studiensemester_aktuell><![CDATA['.$stsem->studiensemester_kurzbz.']]></studiensemester_aktuell>
				<studienbeginn_aktuell><![CDATA['.$datum_obj->convertISODate($stsem->start).']]></studienbeginn_aktuell>
				<tagesdatum><![CDATA['.date('d.m.Y').']]></tagesdatum>
	    	</student>';
		}
	}
	echo '</studenten>';
}
?>