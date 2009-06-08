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
require_once('../include/aufmerksamdurch.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$ad=new aufmerksamdurch($conn,null,true);

$ad->getAll();

$rdf_url='http://www.technikum-wien.at/aufmerksamdurch';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:AUFMERKSAM="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/alle">

<?php
foreach ($ad->result as $row) 
{
?>
	  <RDF:li>
      	<RDF:Description  id="<?php echo $row->aufmerksamdurch_kurzbz; ?>"  about="<?php echo $rdf_url.'/'.$row->aufmerksamdurch_kurzbz; ?>" >
        	<AUFMERKSAM:aufmerksamdurch_kurzbz><![CDATA[<?php echo $row->aufmerksamdurch_kurzbz;  ?>]]></AUFMERKSAM:aufmerksamdurch_kurzbz>
        	<AUFMERKSAM:beschreibung><![CDATA[<?php echo $row->beschreibung; ?>]]></AUFMERKSAM:beschreibung>
      	</RDF:Description>
      </RDF:li>
<?php
}
?>
  </RDF:Seq>
</RDF:RDF>