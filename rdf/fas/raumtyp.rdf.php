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
include('../../include/fas/raumtyp.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/raumtyp';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:RAUMTYP="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php
$raumtypDAO=new raumtyp($conn);
$raumtypDAO->getAll();

	foreach ($raumtypDAO->result as $raumtyp)
	{
		?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$raumtyp->raumtyp_id; ?>" >
	        	<RAUMTYP:raumtyp_id><?php echo $raumtyp->raumtyp_id;  ?></RAUMTYP:raumtyp_id>
	    		<RAUMTYP:bezeichnung><?php echo $raumtyp->bezeichnung;  ?></RAUMTYP:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	<?php
	}
?>
  </RDF:Seq>
</RDF:RDF>