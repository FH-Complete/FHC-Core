<?php
/*
 * Created on 02.12.2004
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
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
include('../vilesci/config.inc.php');
include_once('../include/person.class.php');
include_once('../include/student.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

// test
/*
$einheit_kurzbz='';
$grp='1';
$ver='A';
$sem=3;
$stg_kz=145;
*/

$einheit_kurzbz=$_GET['einheit'];
$grp=$_GET['grp'];
$ver=$_GET['ver'];
$sem=$_GET['sem'];
$stg_kz=$_GET['stg_kz'];

// Studenten holen
$studentenDAO=new student($conn);
$studenten=$studentenDAO->getStudents($einheit_kurzbz, $grp, $ver, $sem,$stg_kz);

$rdf_url='http://www.technikum-wien.at/tempus/studenten';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:STUDENT="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
foreach ($studenten as $student)
{
	?>
	  <RDF:li>
      	<RDF:Description  id="<?php echo $student->uid; ?>"  about="<?php echo $rdf_url.'/'.$student->uid; ?>" >
        	<STUDENT:uid><?php echo $student->uid  ?></STUDENT:uid>
    		<STUDENT:titel><?php echo $student->titel  ?></STUDENT:titel>
    		<STUDENT:vornamen><?php echo $student->vornamen  ?></STUDENT:vornamen>
    		<STUDENT:nachname><?php echo $student->nachname  ?></STUDENT:nachname>
    		<STUDENT:matrikelnummer><?php echo $student->matrikelnr  ?></STUDENT:matrikelnummer>
    		<STUDENT:geburtsdatum><?php echo $student->gebdatum  ?></STUDENT:geburtsdatum>
    		<STUDENT:email><?php echo $student->email  ?></STUDENT:email>
    		<STUDENT:homepage><?php echo $student->homepage  ?></STUDENT:homepage>
    		<STUDENT:aktiv><?php echo ($student->aktiv?'True':'False')  ?></STUDENT:aktiv>
    		<STUDENT:gebort><?php echo $student->gebort  ?></STUDENT:gebort>
    		<STUDENT:gebzeit><?php echo $student->gebzeit  ?></STUDENT:gebzeit>
    		<STUDENT:foto><?php echo $student->foto  ?></STUDENT:foto>
    		<STUDENT:anmerkungen><?php echo $student->anmerkungen  ?></STUDENT:anmerkungen>
    		<STUDENT:updateamum><?php echo $student->updateamum  ?></STUDENT:updateamum>
    		<STUDENT:updatevon><?php echo $student->updatevon  ?></STUDENT:updatevon>
    		<STUDENT:gruppe><?php echo $student->gruppe  ?></STUDENT:gruppe>
    		<STUDENT:verband><?php echo $student->verband  ?></STUDENT:verband>
    		<STUDENT:semester><?php echo $student->semester  ?></STUDENT:semester>
    		<STUDENT:studiengang_kz><?php echo $student->studiengang_kz ?></STUDENT:studiengang_kz>
    		<STUDENT:stg_bezeichnung><?php echo $student->stg_bezeichnung ?></STUDENT:stg_bezeichnung>
      	</RDF:Description>
      </RDF:li>
<?php
}
?>

  </RDF:Seq>


</RDF:RDF>