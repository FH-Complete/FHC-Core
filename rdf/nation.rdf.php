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
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/nation.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

// studiensemester holen
$nation = new nation($conn, null, true);
$nation->getAll();

$rdf_url='http://www.technikum-wien.at/nation';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NATION="<?php echo $rdf_url; ?>/rdf#"
>
   <RDF:Seq about="<?php echo $rdf_url ?>/liste">
<?php
foreach ($nation->nation as $row)
{
	?>
      <RDF:li>
         <RDF:Description  id="<?php echo $row->code; ?>"  about="<?php echo $rdf_url.'/'.$row->code; ?>" >
            <NATION:nation_code><![CDATA[<?php echo $row->code ?>]]></NATION:nation_code>
            <NATION:entwicklungsstand><![CDATA[<?php echo $row->entwicklungsstand ?>]]></NATION:entwicklungsstand>
            <NATION:eu><![CDATA[<?php echo ($row->eu?'true':'false') ?>]]></NATION:eu>
            <NATION:ewr><![CDATA[<?php echo ($row->ewr?'true':'false') ?>]]></NATION:ewr>
            <NATION:kontinent><![CDATA[<?php echo $row->kontinent ?>]]></NATION:kontinent>
            <NATION:kurztext><![CDATA[<?php echo $row->kurztext ?>]]></NATION:kurztext>
            <NATION:langtext><![CDATA[<?php echo $row->langtext ?>]]></NATION:langtext>
            <NATION:engltext><![CDATA[<?php echo $row->engltext ?>]]></NATION:engltext>
            <NATION:sperre><![CDATA[<?php echo ($row->sperre?'true':'false') ?>]]></NATION:sperre>
         </RDF:Description>
      </RDF:li>
<?php
}
?>
   </RDF:Seq>
</RDF:RDF>
