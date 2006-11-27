<?php
/*
 * Created on 02.12.2004
 * Erstellt ein RDF mit den Lehrformen
 */
// header f�r no cache
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
include_once('../include/lehrform.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

// Lehrformen holen
$lehrformDAO=new lehrform($conn);
$lehrform=$lehrformDAO->getAll();

$rdf_url='http://www.technikum-wien.at/lehrform';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHRFORM="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
if (is_array($lehrform))
{
	foreach ($lehrform as $lf)
	{
	?>
  <RDF:li>
      	<RDF:Description  id="<?php echo $lf->kurzbz; ?>"  about="<?php echo $rdf_url.'/'.$lf->kurzbz; ?>" >
        	<LEHRFORM:kurzbz><?php echo $lf->kurzbz  ?></LEHRFORM:kurzbz>
    		<LEHRFORM:bezeichnung><?php echo $lf->bezeichnung  ?></LEHRFORM:bezeichnung>
      	</RDF:Description>
  </RDF:li>
	  <?php
	}
}
?>
  </RDF:Seq>
</RDF:RDF>