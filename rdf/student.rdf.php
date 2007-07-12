<?php

// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
if(isset($_GET['xmlformat']) && $_GET['xmlformat']=='xml')
	echo '<?xml version="1.0" encoding="ISO-8859-9" standalone="yes"?>';
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

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

function convdate($date)
{
	list($d,$m,$y) = explode('.',$date);
	return $y.'-'.$m.'-'.$d;
}

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

if($studiensemester_kurzbz=='aktuelles')
	$studiensemester_kurzbz = $semester_aktuell;
	
if(isset($_GET['xmlformat']) && $_GET['xmlformat']=='xml')
	$xmlformat='xml';
else 
	$xmlformat='rdf';

$datum_obj = new datum();

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
	
	function draw_content($row)
	{
		global $rdf_url, $datum_obj, $conn;
		$status='';
		if($row->prestudent_id!='')
		{
			$prestudent = new prestudent($conn, null, null);
			$prestudent->getLastStatus($row->prestudent_id);
			$status = $prestudent->rolle_kurzbz;
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
	    		<STUDENT:aktiv><![CDATA['.($row->aktiv?'true':'false').']]></STUDENT:aktiv>
	    		<STUDENT:gebort><![CDATA['.$row->gebort.']]></STUDENT:gebort>
	    		<STUDENT:gebzeit><![CDATA['.$row->gebzeit.']]></STUDENT:gebzeit>
	    		<STUDENT:anmerkungen><![CDATA['.$row->anmerkungen.']]></STUDENT:anmerkungen>
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
	    			    		
	    		<STUDENT:uid><![CDATA['.(isset($row->uid)?$row->uid:'').']]></STUDENT:uid>
	    		<STUDENT:matrikelnummer><![CDATA['.(isset($row->matrikelnr)?$row->matrikelnr:'').']]></STUDENT:matrikelnummer>	    		
				<STUDENT:alias><![CDATA['.(isset($row->alias)?$row->alias:'').']]></STUDENT:alias>
	    		<STUDENT:semester><![CDATA['.(isset($row->semester)?$row->semester:'').']]></STUDENT:semester>
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
				<STUDENT:anmerkung><![CDATA['.$row->anmerkung.']]></STUDENT:anmerkung>
	      	</RDF:Description>
	      </RDF:li>';
		}
	}
	
	if($typ=='student')
	{
		// Studenten holen
		$student=new student($conn,null,true);
		if (isset($uid))
			$student->load($uid, $studiensemester_kurzbz);
		else
			$studenten=$student->getStudents($studiengang_kz,$semester,$verband,$gruppe,$gruppe_kurzbz, $studiensemester_kurzbz);
		$prestd = new prestudent($conn, null, true);
		if(isset($uid))
		{
			draw_content($student);
			$prestd->load($student->prestudent_id);
			draw_prestudent($prestd);
		}
		else 
			foreach ($studenten as $student)
			{
				draw_content($student);
				$prestd->load($student->prestudent_id);
				draw_prestudent($prestd);
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
			$qry = "SELECT prestudent_id FROM public.tbl_person JOIN tbl_prestudent USING (person_id) WHERE nachname ~* '".addslashes($filter)."';";
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
					WHERE prestudent_id='$student->prestudent_id' ORDER BY datum LIMIT 1";
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
				<semester><![CDATA['.$student->semester.']]></semester>
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