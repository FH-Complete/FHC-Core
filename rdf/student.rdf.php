<?php
/* Copyright (C) 2006 fhcomplete.org
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
// header fuer no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
if(isset($_GET['xmlformat']) && $_GET['xmlformat']=='xml')
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
else
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/student.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/datum.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/lehrveranstaltung.class.php');

// *********** Funktionen *************************
function convdate($date)
{
	list($d,$m,$y) = explode('.',$date);
	return $y.'-'.$m.'-'.$d;
}

function checkfilter($row, $filter2)
{
	global $studiensemester_kurzbz, $kontofilterstg;
	$db = new basis_db();

	if($filter2=='dokumente')
	{
		// Alle Personen die noch nicht alle Dokumente gebracht haben
		$qry = "SELECT count(*) as anzahl FROM public.tbl_dokumentstudiengang WHERE
				dokument_kurzbz NOT IN(
					SELECT dokument_kurzbz FROM tbl_dokumentprestudent WHERE prestudent_id='$row->prestudent_id')
				AND studiengang_kz='$row->studiengang_kz'";
		if($db->db_query($qry))
			if($row_filter = $db->db_fetch_object())
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
		if($db->db_query($qry))
			if($row_filter = $db->db_fetch_object())
				if($row_filter->summe=='0.00' || $row_filter->summe=='' || $row_filter->summe=='0')
					return false;
	}
	elseif($filter2=='studiengebuehr')
	{
		// Alle Personen die keine Studiengebuehrbelastung haben 
		// Incoming werden nicht beruecksichtigt
		$prestudent = new prestudent();
		$prestudent->getLastStatus($row->prestudent_id);
		
		$qry = "SELECT count(*) as anzahl FROM public.tbl_konto WHERE 
					studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND 
					person_id='".addslashes($row->person_id)."' AND 
					buchungstyp_kurzbz='Studiengebuehr'";
		if($db->db_query($qry))
			if($row_filter = $db->db_fetch_object())
				if($row_filter->anzahl>0 || $prestudent->status_kurzbz=='Incoming')
					return false;
	}
	elseif(strstr($filter2,'buchungstyp;'))
	{
		// Alle Personen die keine Belastung auf den uebergebenen Buchungstyp haben 
		// Incoming werden nicht beruecksichtigt
		list($filter, $buchungstyp) = explode(';',$filter2);
		$prestudent = new prestudent();
		$prestudent->getLastStatus($row->prestudent_id);
		
		$qry = "SELECT count(*) as anzahl FROM public.tbl_konto WHERE 
					studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND 
					person_id='".addslashes($row->person_id)."' AND 
					buchungstyp_kurzbz='$buchungstyp'";
		if($db->db_query($qry))
			if($row_filter = $db->db_fetch_object())
				if($row_filter->anzahl>0 || $prestudent->status_kurzbz=='Incoming')
					return false;
	}
	elseif($filter2=='zgvohnedatum')
	{
		//Alle Personen die den ZGV Typ eingetragen haben aber noch kein Datum
		$qry = "SELECT zgv_code, zgvdatum, zgvmas_code, zgvmadatum FROM public.tbl_prestudent WHERE prestudent_id='$row->prestudent_id'";
		if($db->db_query($qry))
		{
			if($row_filter = $db->db_fetch_object())
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
	global $rdf_url, $datum_obj, $stg_arr;
	$status='';

	$prestudent = new prestudent();
	$prestudent->getLastStatus($row->prestudent_id);
	$status = $prestudent->status_kurzbz;
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
    		<STUDENT:geschlecht><![CDATA['.$row->geschlecht.']]></STUDENT:geschlecht>
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
    		<STUDENT:studiengang_kz><![CDATA['.abs($row->studiengang_kz).']]></STUDENT:studiengang_kz>
			<STUDENT:studiengang><![CDATA['.$stg_arr[$row->studiengang_kz].']]></STUDENT:studiengang>
			<STUDENT:orgform><![CDATA['.$orgform.']]></STUDENT:orgform>
			<STUDENT:aufmerksamdurch_kurzbz><![CDATA['.$row->aufmerksamdurch_kurzbz.']]></STUDENT:aufmerksamdurch_kurzbz>
			<STUDENT:punkte><![CDATA['.$row->punkte.']]></STUDENT:punkte>
			<STUDENT:punkte1><![CDATA['.$row->rt_punkte1.']]></STUDENT:punkte1>
			<STUDENT:punkte2><![CDATA['.$row->rt_punkte2.']]></STUDENT:punkte2>
			<STUDENT:punkte3><![CDATA['.$row->rt_punkte3.']]></STUDENT:punkte3>
			<STUDENT:dual><![CDATA['.($row->dual=='t'?'true':'false').']]></STUDENT:dual>
			<STUDENT:dual_bezeichnung><![CDATA['.($row->dual=='t'?'Ja':'Nein').']]></STUDENT:dual_bezeichnung>
      	</RDF:Description>
      </RDF:li>';
}

function draw_content($row)
{
	global $rdf_url, $datum_obj;
	$db = new basis_db();
	$status='';

	$mail_privat = '';
	$qry_mail = "SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp='email' AND person_id='$row->person_id' AND zustellung=true ORDER BY kontakt_id DESC LIMIT 1";
	if($db->db_query($qry_mail))
	{
		if($row_mail = $db->db_fetch_object())
		{
			$mail_privat = $row_mail->kontakt;
		}
	}

	if($row->prestudent_id!='')
	{
		$prestudent = new prestudent();
		$prestudent->getLastStatus($row->prestudent_id);
		$status = $prestudent->status_kurzbz;
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
			<STUDENT:matr_nr><![CDATA['.$row->matr_nr.']]></STUDENT:matr_nr>
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
			<STUDENT:studiengang_kz><![CDATA['.abs($row->studiengang_kz).']]></STUDENT:studiengang_kz>
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
			<STUDENT:ausstellungsstaat><![CDATA['.$row->ausstellungsstaat.']]></STUDENT:ausstellungsstaat>
			<STUDENT:aufnahmeschluessel><![CDATA['.$row->aufnahmeschluessel.']]></STUDENT:aufnahmeschluessel>
			<STUDENT:facheinschlberuf><![CDATA['.($row->facheinschlberuf?'true':'false').']]></STUDENT:facheinschlberuf>
			<STUDENT:reihungstest_id><![CDATA['.$row->reihungstest_id.']]></STUDENT:reihungstest_id>
			<STUDENT:anmeldungreihungstest><![CDATA['.$datum_obj->convertISODate($row->anmeldungreihungstest).']]></STUDENT:anmeldungreihungstest>
			<STUDENT:anmeldungreihungstest_iso><![CDATA['.$row->anmeldungreihungstest.']]></STUDENT:anmeldungreihungstest_iso>
			<STUDENT:reihungstestangetreten><![CDATA['.($row->reihungstestangetreten?'true':'false').']]></STUDENT:reihungstestangetreten>
			<STUDENT:punkte><![CDATA['.$row->punkte.']]></STUDENT:punkte>
			<STUDENT:punkte1><![CDATA['.$row->rt_punkte1.']]></STUDENT:punkte1>
			<STUDENT:punkte2><![CDATA['.$row->rt_punkte2.']]></STUDENT:punkte2>
			<STUDENT:punkte3><![CDATA['.$row->rt_punkte3.']]></STUDENT:punkte3>
			<STUDENT:bismelden><![CDATA['.($row->bismelden?'true':'false').']]></STUDENT:bismelden>
			<STUDENT:dual><![CDATA['.($row->dual?'true':'false').']]></STUDENT:dual>
			<STUDENT:dual_bezeichnung><![CDATA['.($row->dual?'Ja':'Nein').']]></STUDENT:dual_bezeichnung>
			<STUDENT:anmerkungpre><![CDATA['.$row->anmerkung.']]></STUDENT:anmerkungpre>
      	</RDF:Description>
      </RDF:li>';
	}
}

// ******* Init **************************


if(isset($_SERVER['REMOTE_USER']))
{
	$user = get_uid();
	loadVariables($user);
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
$orgform = (isset($_GET['orgform'])?$_GET['orgform']:null);

$db = new basis_db();

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
	$stg_obj = new studiengang();
	$stg_obj->getAll(null, false);
	foreach ($stg_obj->result as $row)
		$stg_arr[$row->studiengang_kz]=$row->kuerzel;

	$rdf_url='http://www.technikum-wien.at/student';
	echo '
	<RDF:RDF
		xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
		xmlns:STUDENT="'.$rdf_url.'/rdf#"
		xmlns:NC="http://home.netscape.com/NC-rdf#"
	>


	  <RDF:Seq about="'.$rdf_url.'/alle">
	';

	if(isset($uid))
	{
		$student=new student();
		$student->load($uid, $studiensemester_kurzbz);
		$prestd = new prestudent();

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
				
			//Wenn eine Orgform uebergeben wird nur das Semester ausgewaehlt ist, dann
			//nach der Orgform filtern. Bei Verbaenden, Gruppen und Spezialgruppen wird auf
			//die Orgform keine ruecksicht genommen
			if($verband=='' && $gruppe=='' && $orgform!='')
			{
				$where.=" AND '$orgform' = (SELECT orgform_kurzbz FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_prestudent.prestudent_id";
				if($studiensemester_kurzbz!=null)
					$where.=" AND studiensemester_kurzbz='$studiensemester_kurzbz'";
				$where.=" ORDER BY datum desc, insertamum desc, ext_id desc LIMIT 1)";
			}
		}

		//$where.=" AND tbl_studentlehrverband.studiensemester_kurzbz='$studiensemester_kurzbz'";

		$sql_query="SELECT p.person_id, tbl_student.prestudent_id, tbl_benutzer.uid, titelpre, titelpost,	vorname, vornamen, geschlecht,
						nachname, gebdatum, tbl_prestudent.anmerkung,ersatzkennzeichen,svnr, tbl_student.matrikelnr, p.anmerkung as anmerkungen,
						tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
						tbl_student.studiengang_kz, aufmerksamdurch_kurzbz, 
						(	SELECT kontakt
							FROM public.tbl_kontakt
							WHERE kontakttyp='email' AND person_id=p.person_id AND zustellung
							LIMIT 1
						)
						AS email_privat,
						(SELECT rt_gesamtpunkte as punkte FROM public.tbl_prestudent WHERE prestudent_id=tbl_student.prestudent_id) as punkte,
						(SELECT rt_punkte1 as punkte FROM public.tbl_prestudent WHERE prestudent_id=tbl_student.prestudent_id) as rt_punkte1,
						(SELECT rt_punkte2 as punkte FROM public.tbl_prestudent WHERE prestudent_id=tbl_student.prestudent_id) as rt_punkte2,
						(SELECT rt_punkte3 as punkte FROM public.tbl_prestudent WHERE prestudent_id=tbl_student.prestudent_id) as rt_punkte3,
						 tbl_prestudent.dual as dual
						FROM public.tbl_student 
							JOIN public.tbl_benutzer ON (student_uid=uid) JOIN public.tbl_person p USING (person_id)  JOIN public.tbl_prestudent USING(prestudent_id) ";
		if($gruppe_kurzbz!=null)
			$sql_query.= "JOIN public.tbl_benutzergruppe USING (uid) ";
		$sql_query.="LEFT JOIN public.tbl_studentlehrverband ON (tbl_studentlehrverband.student_uid=tbl_student.student_uid AND tbl_studentlehrverband.studiensemester_kurzbz='$studiensemester_kurzbz')";
		$sql_query.="WHERE ".$where.' ORDER BY nachname, vorname';
		
		
		if($db->db_query($sql_query))
		{
			while($row = $db->db_fetch_object())
			{
				if(checkfilter($row, $filter2))
					draw_content_liste($row);
			}
		}
	}
	elseif($typ=='incoming' || $typ=='outgoing')
	{
		if($studiensemester_kurzbz=='')
			$studiensemester_kurzbz=$semester_aktuell;
		if($typ=='incoming')
		{
			$qry = "SELECT prestudent_id FROM public.tbl_prestudentstatus WHERE status_kurzbz='Incoming' AND studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		}
		else
		{
			$stsem_obj = new studiensemester();
			$stsem_obj->load($studiensemester_kurzbz);
			$qry = "SELECT prestudent_id 
					FROM 
						bis.tbl_bisio JOIN public.tbl_student USING(student_uid)  
					WHERE 
						(
						(tbl_bisio.von>='".$stsem_obj->start."' AND tbl_bisio.von<='".$stsem_obj->ende."')
						OR
						(tbl_bisio.bis>='".$stsem_obj->start."' AND tbl_bisio.bis<='".$stsem_obj->ende."')
						)
						AND NOT EXISTS(SELECT 1 FROM public.tbl_prestudentstatus WHERE status_kurzbz='Incoming' AND prestudent_id=tbl_student.prestudent_id)
					"; 
		}
		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				$student=new student();
				if($uid = $student->getUid($row->prestudent_id))
				{
					//Wenn kein Eintrag fuers aktuelle Studiensemester da ist, dann
					//nochmal laden aber ohne studiensemester
					if(!$student->load($uid, $studiensemester_kurzbz))
						$student->load($uid);
				}
				$prestd = new prestudent();
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
	                      'reihungstestnichtangemeldet','absolvent','diplomand')))
	{
		$prestd = new prestudent();

		if($studiengang_kz!=null)
		{
			if($prestd->loadIntessentenUndBewerber($studiensemester_kurzbz, $studiengang_kz, $semester, $typ, $orgform))
			{
				foreach ($prestd->result as $row)
				{
					if(checkfilter($row, $filter2))
					{
						$student=new student();
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
			//$filter = utf8_decode($filter);
			$qry = "SELECT prestudent_id 
					FROM 
						public.tbl_person JOIN tbl_prestudent USING (person_id) LEFT JOIN tbl_student using(prestudent_id) 
					WHERE 
						lower(COALESCE(nachname,'') ||' '|| COALESCE(vorname,'')) ~* lower(".$db->db_add_param($filter).") OR 
						lower(COALESCE(vorname,'') ||' '|| COALESCE(nachname,'')) ~* lower(".$db->db_add_param($filter).") OR
						student_uid ~* ".$db->db_add_param($filter)." OR
						matrikelnr = ".$db->db_add_param($filter)." OR
						svnr = ".$db->db_add_param($filter).";";
			if($db->db_query($qry))
			{
				while($row = $db->db_fetch_object())
				{
					$student=new student();
					if($uid = $student->getUid($row->prestudent_id))
					{
						//Wenn kein Eintrag fuers aktuelle Studiensemester da ist, dann
						//nochmal laden aber ohne studiensemester
						if(!$student->load($uid, $studiensemester_kurzbz))
							$student->load($uid);
					}
					$prestd = new prestudent();
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
			$student=new student();
			if($uid = $student->getUid($prestudent_id))
			{
				//Wenn kein Eintrag fuers aktuelle Studiensemester da ist, dann
				//nochmal laden aber ohne studiensemester
				if(!$student->load($uid, $studiensemester_kurzbz))
					$student->load($uid);
			}
			$prestd = new prestudent();
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
	$uids = explode(';',$uid);
	echo '<studenten>';
	foreach ($uids as $uid)
	{
		if($uid!='')
		{
			$student = new student();
			$student->load($uid);
            
			$studiengang = new studiengang();
			$studiengang->load($student->studiengang_kz);
            
            $stg_typ = new studiengang(); 
            $stg_typ->getStudiengangTyp($studiengang->typ); 
			$typ=$stg_typ->bezeichnung;
			$typ="FH-$typ-Studiengang";
	/*		switch($studiengang->typ)
			{
				case 'd':	$typ = 'FH-Diplom-Studiengang';
							break;
				case 'm':	$typ = 'FH-Master-Studiengang';
							break;
				case 'b':	$typ = 'FH-Bachelor-Studiengang';
							break;
				default:	$typ = 'FH-Studiengang';
			}
*/
			$qry = "SELECT * FROM campus.vw_benutzer JOIN public.tbl_benutzerfunktion USING(uid) WHERE funktion_kurzbz='rek'";
			$rektor = '';
			if($db->db_query($qry))
			{
				if($row = $db->db_fetch_object())
				{
					$rektor = $row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost;
				}
			}

			$studiengbeginn = '';
			$studiensemester_kurzbz='';
			$qry = "SELECT * FROM public.tbl_prestudentstatus JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE prestudent_id='$student->prestudent_id' AND status_kurzbz in('Student','Incoming') ORDER BY datum LIMIT 1";
			if($db->db_query($qry))
			{
				if($row = $db->db_fetch_object())
				{
					$studienbeginn = $row->start;
					$studiensemester = $row->studiensemester_kurzbz;
				}
			}

			$stsem = new studiensemester();
			//$aktstsem = $stsem->getaktorNext();

			$stsem->load($ss);

			$qry = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id='$student->prestudent_id' AND studiensemester_kurzbz='$ss' ORDER BY datum DESC";
			$semester=0;
			if($db->db_query($qry))
			{
				if($row = $db->db_fetch_object())
				{
					$semester = $row->ausbildungssemester;
				}
			}

			//für ao. Studierende wird die StgKz der Lehrveranstaltungen benötigt, die sie besuchen
			$lv_studiengang_kz='';
			$lv_studiengang_bezeichnung='';
			$lv_studiengang_typ='';

			$lv=new lehrveranstaltung();
			$lv->load_lva_student($student->uid);
			if(count($lv->lehrveranstaltungen)>0)
			{
				$lv_studiengang_kz=$lv->lehrveranstaltungen[0]->studiengang_kz;
				$lv_studiengang=new studiengang();
				$lv_studiengang->load($lv_studiengang_kz);
				$lv_studiengang_bezeichnung=$lv_studiengang->bezeichnung;
				$lv_studiengang_typ=$lv_studiengang->typ;
	            $stg_typ->getStudiengangTyp($lv_studiengang->typ); 
				$lv_studiengang_art=$stg_typ->bezeichnung;
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
				<studiengang_kz><![CDATA['.sprintf("%04d",abs($student->studiengang_kz)).']]></studiengang_kz>
				<studiengang_bezeichnung><![CDATA['.$studiengang->bezeichnung.']]></studiengang_bezeichnung>
				<studiengang_art><![CDATA['.$typ.']]></studiengang_art>
                <studiengang_typ><![CDATA['.$studiengang->typ.']]></studiengang_typ>
				<lv_studiengang_kz><![CDATA['.sprintf("%04d",abs($lv_studiengang_kz)).']]></lv_studiengang_kz>
				<lv_studiengang_bezeichnung><![CDATA['.$lv_studiengang_bezeichnung.']]></lv_studiengang_bezeichnung>
                <lv_studiengang_typ><![CDATA['.$lv_studiengang_typ.']]></lv_studiengang_typ>
				<lv_studiengang_art><![CDATA['.$lv_studiengang_art.']]></lv_studiengang_art>
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
