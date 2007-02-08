<?php
/*
 * Created on 02.12.2004
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
// header f?r no cache
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
include_once('../include/benutzer.class.php');
include_once('../include/mitarbeiter.class.php');

if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';


if (isset($_GET['lektor']))
	$lektor=$_GET['lektor'];
else
	$lektor=true;

if (isset($_GET['fixangestellt']))
	$fixangestellt=$_GET['fixangestellt'];
else
	$fixangestellt=null;

if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else
	$stg_kz=null;

if (isset($_GET['fachbereich_id']))
	$fachbereich_id=$_GET['fachbereich_id'];
else
	$fachbereich_id=null;

// Mitarbeiter holen
$mitarbeiter=new mitarbeiter($conn);
$ma=$mitarbeiter->getMitarbeiter($lektor,$fixangestellt,$stg_kz,$fachbereich_id);

$rdf_url='http://www.technikum-wien.at/mitarbeiter/';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:MITARBEITER="<?php echo $rdf_url; ?>rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>alle">

<?php
foreach ($ma as $mitarbeiter)
{
	?>
	  <RDF:li>
      	<RDF:Description about="<?php echo $rdf_url.$mitarbeiter->uid; ?>" >
        	<MITARBEITER:uid><?php echo $mitarbeiter->uid; ?></MITARBEITER:uid>
    		<MITARBEITER:titelpre><?php echo $mitarbeiter->titelpre; ?></MITARBEITER:titelpre>
    		<MITARBEITER:titelpost><?php echo $mitarbeiter->titelpost; ?></MITARBEITER:titelpost>
    		<MITARBEITER:vornamen><?php echo $mitarbeiter->vornamen; ?></MITARBEITER:vornamen>
    		<MITARBEITER:nachname><?php echo $mitarbeiter->nachname; ?></MITARBEITER:nachname>
    		<MITARBEITER:kurzbz><?php echo $mitarbeiter->kurzbz; ?></MITARBEITER:kurzbz>
      	</RDF:Description>
      </RDF:li>
<?php
}
?>
  </RDF:Seq>
</RDF:RDF>