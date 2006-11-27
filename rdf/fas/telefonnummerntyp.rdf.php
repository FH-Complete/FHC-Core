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

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING_FAS))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

$rdf_url='http://www.technikum-wien.at/telefonnummerntyp';
?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:TELEFONNUMMERNTYP="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php
$qry = "SET CLIENT_ENCODING TO 'UNICODE';Select name, typ from telefonnummer group by name, typ";
if($result = pg_query($conn,$qry))
{
	$x=0;
	while($row = pg_fetch_object($result)) 
	{
?>
	  <RDF:li>
      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$x; ?>" >
        	<TELEFONNUMMERNTYP:name><?php echo $row->name;  ?></TELEFONNUMMERNTYP:name>
    		<TELEFONNUMMERNTYP:typ><?php echo $row->typ;  ?></TELEFONNUMMERNTYP:typ>
      	</RDF:Description>
      </RDF:li>
<?php
	   $x++;
	}
}
?>
  </RDF:Seq>
</RDF:RDF>