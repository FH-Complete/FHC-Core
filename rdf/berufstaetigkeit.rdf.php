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

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/berufstaetigkeit';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:BT="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/alle">

<?php
$qry = "SET CLIENT_ENCODING to 'UNICODE'; SELECT * FROM bis.tbl_berufstaetigkeit ORDER BY berufstaetigkeit_bez";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
?>
	  <RDF:li>
      	<RDF:Description  id="<?php echo $row->berufstaetigkeit_code; ?>"  about="<?php echo $rdf_url.'/'.$row->berufstaetigkeit_code; ?>" >
        	<BT:code><![CDATA[<?php echo $row->berufstaetigkeit_code;  ?>]]></BT:code>
        	<BT:bezeichnung><![CDATA[<?php echo $row->berufstaetigkeit_bez; ?>]]></BT:bezeichnung>
        	<BT:kurzbz><![CDATA[<?php echo $row->berufstaetigkeit_kurzbz; ?>]]></BT:kurzbz>
      	</RDF:Description>
      </RDF:li>
<?php
	}
}
?>
  </RDF:Seq>
</RDF:RDF>