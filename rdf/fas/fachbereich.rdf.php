<?php
/**
 * erstellt ein RDF File mit den Studiensemestern
 * Created on 23.3.2006 
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
include('../../include/fas/fachbereich.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
   	
$rdf_url='http://www.technikum-wien.at/fachbereich';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:FACHBEREICH="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php
$fachbereichDAO=new fachbereich($conn);
$fachbereichDAO->getAll();   	

	foreach ($fachbereichDAO->result as $fachbereich)
	{
		?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$fachbereich->fachbereich_id; ?>" >
	        	<FACHBEREICH:fachbereich_id><?php echo $fachbereich->fachbereich_id;  ?></FACHBEREICH:fachbereich_id>
	    		<FACHBEREICH:name><?php echo $fachbereich->name;  ?></FACHBEREICH:name>
	      	</RDF:Description>
	      </RDF:li>
	<?php
	}
?>

  </RDF:Seq>


</RDF:RDF>