<?php
/**
 * erstellt ein RDF File mit den Adressen
 * Created on 23.3.2006
 * Aufruf: adressen.rdf.php?pers_id=xyz
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
include_once('../../include/fas/nation.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING_FAS))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

if(isset($_GET['ohnesperre']) && $_GET['ohnesperre']=='true')
	$ohnesperre=true;
else 
	$ohnesperre=false;
	
$nationDAO=new nation($conn);
if(!$nationDAO->getAll($ohnesperre))
	die("$nationDAO->errormsg");

$rdf_url='http://www.technikum-wien.at/nation';
?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NATION="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php

foreach ($nationDAO->result as $nation)
{
?>
	  <RDF:li>
      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$nation->code; ?>" >
        	<NATION:code><?php echo $nation->code;  ?></NATION:code>
    		<NATION:kurztext><?php echo $nation->kurztext;  ?></NATION:kurztext>
      	</RDF:Description>
      </RDF:li>
<?php
}
?>
  </RDF:Seq>
</RDF:RDF>