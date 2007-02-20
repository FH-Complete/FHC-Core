<?php

// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
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
$student=new student($conn);
if (isset($uid))
	$studenten=$student->load($uid);
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
foreach ($studenten as $student)
{
	?>
	  <RDF:li>
      	<RDF:Description  id="<?php echo $student->uid; ?>"  about="<?php echo $rdf_url.'/'.$student->uid; ?>" >
        	<STUDENT:uid><?php echo $student->uid;  ?></STUDENT:uid>
    		<STUDENT:titelpre><?php echo $student->titelpre; ?></STUDENT:titelpre>
    		<STUDENT:titelpost><?php echo $student->titelpost; ?></STUDENT:titelpost>
    		<STUDENT:vornamen><?php echo $student->vornamen  ?></STUDENT:vornamen>
    		<STUDENT:nachname><?php echo $student->nachname  ?></STUDENT:nachname>
    		<STUDENT:matrikelnummer><?php echo $student->matrikelnr  ?></STUDENT:matrikelnummer>
    		<STUDENT:geburtsdatum><?php echo $student->gebdatum  ?></STUDENT:geburtsdatum>
    		<STUDENT:geburtsdatum_iso><?php echo $student->gebdatum;  ?></STUDENT:geburtsdatum_iso>
    		<STUDENT:alias><?php echo $student->alias  ?></STUDENT:alias>
    		<STUDENT:homepage><?php echo $student->homepage  ?></STUDENT:homepage>
    		<STUDENT:aktiv><?php echo ($student->aktiv?'True':'False')  ?></STUDENT:aktiv>
    		<STUDENT:gebort><?php echo $student->gebort;  ?></STUDENT:gebort>
    		<STUDENT:gebzeit><?php echo $student->gebzeit;  ?></STUDENT:gebzeit>
    		<STUDENT:foto><?php echo $student->foto;  ?></STUDENT:foto>
    		<STUDENT:anmerkungen><?php echo $student->anmerkungen;  ?></STUDENT:anmerkungen>
    		<STUDENT:updateamum><?php echo $student->updateamum;  ?></STUDENT:updateamum>
    		<STUDENT:updatevon><?php echo $student->updatevon;  ?></STUDENT:updatevon>
    		<STUDENT:semester><?php echo $student->semester;  ?></STUDENT:semester>
    		<STUDENT:verband><?php echo $student->verband;  ?></STUDENT:verband>
    		<STUDENT:gruppe><?php echo $student->gruppe;  ?></STUDENT:gruppe>
    		<STUDENT:studiengang_kz><?php echo $student->studiengang_kz; ?></STUDENT:studiengang_kz>
      	</RDF:Description>
      </RDF:li>
<?php
}
?>

  </RDF:Seq>


</RDF:RDF>