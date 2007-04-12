<?php

// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/student.class.php');

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

$gruppe=(isset($_GET['gruppe'])?$_GET['gruppe']:null);
$grp=(isset($_GET['grp'])?$_GET['grp']:null);
$ver=(isset($_GET['ver'])?$_GET['ver']:null);
$sem=(isset($_GET['sem'])?$_GET['sem']:null);
$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:null);
if(isset($_GET['uid']))
	$uid=$_GET['uid'];

// Studenten holen
$student=new student($conn,null,true);
if (isset($uid))
	$student->load($uid);
else
	$studenten=$student->getStudents($stg_kz,$sem,$ver,$grp,$gruppe);

$rdf_url='http://www.technikum-wien.at/student';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:STUDENT="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/alle">

<?php
if(isset($uid))
	drawStudent($student);
else 
	foreach ($studenten as $student)
		drawStudent($student);

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
      	</RDF:Description>
      </RDF:li>
<?php
}
?>

  </RDF:Seq>


</RDF:RDF>