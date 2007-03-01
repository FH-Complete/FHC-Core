<?php
/*
 * Created on 02.12.2004
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/lehrfunktion.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/lehrfunktion';

$lfkt = new lehrfunktion($conn);
$lfkt->getAll();

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHRFUNKTION="<?php echo $rdf_url; ?>/rdf#"
>
   <RDF:Seq about="<?php echo $rdf_url ?>/liste">
      <RDF:li>
<?php
foreach ($lfkt->lehrfunktionen as $row)
{
?>
         <RDF:Description  id="<?php echo $row->lehrfunktion_kurzbz; ?>"  about="<?php echo $rdf_url.'/'.$row->lehrfunktion_kurzbz; ?>" >
            <LEHRFUNKTION:lehrfunktion_kurzbz><![CDATA[<?php echo $row->lehrfunktion_kurzbz ?>]]></LEHRFUNKTION:lehrfunktion_kurzbz>
            <LEHRFUNKTION:beschreibung><![CDATA[<?php echo $row->beschreibung ?>]]></LEHRFUNKTION:beschreibung>
            <LEHRFUNKTION:standardfaktor><![CDATA[<?php echo $row->standardfaktor ?>]]></LEHRFUNKTION:standardfaktor>
         </RDF:Description>
<?php
}
?>
      </RDF:li>
   </RDF:Seq>
</RDF:RDF>