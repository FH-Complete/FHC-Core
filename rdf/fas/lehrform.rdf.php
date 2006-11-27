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
include('../../include/fas/lehrform.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/lehrform';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHRFORM="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php
$lehrformDAO=new lehrform($conn);
$lehrformDAO->getAll();

	foreach ($lehrformDAO->result as $lehrform)
	{
		?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$lehrform->lehrform_id; ?>" >
	        	<LEHRFORM:lehrform_id><?php echo $lehrform->lehrform_id;  ?></LEHRFORM:lehrform_id>
	    		<LEHRFORM:bezeichnung><?php echo $lehrform->bezeichnung;  ?></LEHRFORM:bezeichnung>
	    		<LEHRFORM:kurzbezeichnung><?php echo $lehrform->kurzbezeichnung;  ?></LEHRFORM:kurzbezeichnung>
	    		<LEHRFORM:standardfaktor><?php echo $lehrform->standardfaktor;  ?></LEHRFORM:standardfaktor>
	      	</RDF:Description>
	      </RDF:li>
	<?php
	}
?>
  </RDF:Seq>
</RDF:RDF>