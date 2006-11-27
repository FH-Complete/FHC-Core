<?php
/**
 * erstellt ein RDF File mit den Ausbildungssemestern
 * Created on 23.3.2006
 * Aufruf: ausbildungssemester.rdf.php?stg=xx
 */
// header fuer no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
include('../../vilesci/config.inc.php');
include_once('../../include/fas/ausbildungssemester.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/ausbildungssemester';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:AUSBILDUNGSSEMESTER="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/liste">
<?php
$ausbsem_obj = new ausbildungssemester($conn);

if(isset($_GET['stg']) && is_numeric($_GET['stg']))
{
	$ausbsem_obj->load_stg($_GET['stg']);

	foreach ($ausbsem_obj->result as $ausbsem)
	{
		?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$ausbsem->ausbildungssemester_id; ?>" >
	        	<AUSBILDUNGSSEMESTER:ausbildungssemester_id><?php echo $ausbsem->ausbildungssemester_id;  ?></AUSBILDUNGSSEMESTER:ausbildungssemester_id>
	    		<AUSBILDUNGSSEMESTER:studiengang_id><?php echo $ausbsem->studiengang_id; ?></AUSBILDUNGSSEMESTER:studiengang_id>
	    		<AUSBILDUNGSSEMESTER:semester><?php echo $ausbsem->semester; ?></AUSBILDUNGSSEMESTER:semester>
	    		<AUSBILDUNGSSEMESTER:name><?php echo $ausbsem->name; ?></AUSBILDUNGSSEMESTER:name>
	      	</RDF:Description>
	      </RDF:li>
	<?php

	}
}
?>

  </RDF:Seq>

</RDF:RDF>