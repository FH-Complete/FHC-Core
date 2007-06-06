<?php

// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/student.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/datum.class.php');
// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

// test
/*
$gruppe='';
$grp='1';
$ver='A';
$sem=3;
$stg_kz=145;
*/

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

$datum_obj = new datum();
$gruppe=(isset($_GET['gruppe'])?$_GET['gruppe']:null);
$grp=(isset($_GET['grp'])?$_GET['grp']:null);
$ver=(isset($_GET['ver'])?$_GET['ver']:null);
$sem=(isset($_GET['sem'])?$_GET['sem']:null);
$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:null);
if(isset($_GET['uid']))
	$uid=$_GET['uid'];

if(isset($_GET['stsem']) && $_GET['stsem']=='true')
	$stsem = $semester_aktuell;
else 
	$stsem=null;	

if(isset($_GET['xmlformat']) && $_GET['xmlformat']=='xml')
	$xmlformat='xml';
else 
	$xmlformat='rdf';

if($xmlformat=='rdf')
{
	// Studenten holen
	$student=new student($conn,null,true);
	if (isset($uid))
		$student->load($uid, $stsem);
	else
		$studenten=$student->getStudents($stg_kz,$sem,$ver,$grp,$gruppe, $stsem);
	
	$rdf_url='http://www.technikum-wien.at/student';
	
	?>
	
	<RDF:RDF
		xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
		xmlns:STUDENT="<?php echo $rdf_url; ?>/rdf#"
	>
	
	
	  <RDF:Seq about="<?php echo $rdf_url ?>/alle">
	
	<?php
	function drawStudent($student)
	{
		global $rdf_url;
		?>
		  <RDF:li>
	      	<RDF:Description  id="<?php echo $student->uid; ?>"  about="<?php echo $rdf_url.'/'.$student->uid; ?>" >
	        	<STUDENT:uid><![CDATA[<?php echo $student->uid;  ?>]]></STUDENT:uid>
	        	<STUDENT:person_id><![CDATA[<?php echo $student->person_id; ?>]]></STUDENT:person_id>
	    		<STUDENT:titelpre><![CDATA[<?php echo $student->titelpre; ?>]]></STUDENT:titelpre>
	    		<STUDENT:titelpost><![CDATA[<?php echo $student->titelpost; ?>]]></STUDENT:titelpost>
	    		<STUDENT:vornamen><![CDATA[<?php echo $student->vornamen  ?>]]></STUDENT:vornamen>
	    		<STUDENT:vorname><![CDATA[<?php echo $student->vorname  ?>]]></STUDENT:vorname>
	    		<STUDENT:nachname><![CDATA[<?php echo $student->nachname  ?>]]></STUDENT:nachname>
	    		<STUDENT:matrikelnummer><![CDATA[<?php echo $student->matrikelnr  ?>]]></STUDENT:matrikelnummer>
	    		<STUDENT:geburtsdatum><![CDATA[<?php echo $student->gebdatum  ?>]]></STUDENT:geburtsdatum>
	    		<STUDENT:geburtsdatum_iso><![CDATA[<?php echo $student->gebdatum;  ?>]]></STUDENT:geburtsdatum_iso>
	    		<STUDENT:alias><![CDATA[<?php echo $student->alias  ?>]]></STUDENT:alias>
	    		<STUDENT:homepage><![CDATA[<?php echo $student->homepage  ?>]]></STUDENT:homepage>
	    		<STUDENT:aktiv><![CDATA[<?php echo ($student->aktiv?'true':'false')  ?>]]></STUDENT:aktiv>
	    		<STUDENT:gebort><![CDATA[<?php echo $student->gebort;  ?>]]></STUDENT:gebort>
	    		<STUDENT:gebzeit><![CDATA[<?php echo $student->gebzeit;  ?>]]></STUDENT:gebzeit>
	    		<STUDENT:foto><![CDATA[<?php echo $student->foto;  ?>]]></STUDENT:foto>
	    		<STUDENT:anmerkungen><![CDATA[<?php echo $student->anmerkungen;  ?>]]></STUDENT:anmerkungen>
	    		<STUDENT:updateamum><![CDATA[<?php echo $student->updateamum;  ?>]]></STUDENT:updateamum>
	    		<STUDENT:updatevon><![CDATA[<?php echo $student->updatevon;  ?>]]></STUDENT:updatevon>
	    		<STUDENT:semester><![CDATA[<?php echo $student->semester;  ?>]]></STUDENT:semester>
	    		<STUDENT:verband><![CDATA[<?php echo $student->verband;  ?>]]></STUDENT:verband>
	    		<STUDENT:gruppe><![CDATA[<?php echo $student->gruppe;  ?>]]></STUDENT:gruppe>
	    		<STUDENT:studiengang_kz><![CDATA[<?php echo $student->studiengang_kz; ?>]]></STUDENT:studiengang_kz>
	    		
	    		<STUDENT:anrede><![CDATA[<?php echo $student->anrede; ?>]]></STUDENT:anrede>
	    		<STUDENT:svnr><![CDATA[<?php echo $student->svnr; ?>]]></STUDENT:svnr>
	    		<STUDENT:ersatzkennzeichen><![CDATA[<?php echo $student->ersatzkennzeichen; ?>]]></STUDENT:ersatzkennzeichen>
	    		<STUDENT:familienstand><![CDATA[<?php echo $student->familienstand; ?>]]></STUDENT:familienstand>
	    		<STUDENT:geschlecht><![CDATA[<?php echo $student->geschlecht; ?>]]></STUDENT:geschlecht>
	    		<STUDENT:anzahlkinder><![CDATA[<?php echo $student->anzahlkinder; ?>]]></STUDENT:anzahlkinder>
	    		<STUDENT:staatsbuergerschaft><![CDATA[<?php echo $student->staatsbuergerschaft; ?>]]></STUDENT:staatsbuergerschaft>
	    		<STUDENT:geburtsnation><![CDATA[<?php echo $student->geburtsnation; ?>]]></STUDENT:geburtsnation>
	    		<STUDENT:sprache><![CDATA[<?php echo $student->sprache; ?>]]></STUDENT:sprache>
	    		<STUDENT:prestudent_id><![CDATA[<?php echo $student->prestudent_id; ?>]]></STUDENT:prestudent_id>
	      	</RDF:Description>
	      </RDF:li>
	<?php
	}
	if(isset($uid))
		drawStudent($student);
	else 
		foreach ($studenten as $student)
			drawStudent($student);
	
	
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
				<geburtsdatum><![CDATA['.$student->gebdatum.']]></geburtsdatum>
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
				<studienbeginn><![CDATA['.$datum_obj->convertISODate($studienbeginn).']]></studienbeginn>
				<studiensemester><![CDATA['.$studiensemester.']]></studiensemester>
				<tagesdatum><![CDATA['.date('d.m.Y').']]></tagesdatum>
	    	</student>';			
		}
	}
	echo '</studenten>';
}
?>