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
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/dokument.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/dokumentprestudent';
	
if(isset($_GET['prestudent_id']))
	if(is_numeric($_GET['prestudent_id']))
		$prestudent_id=$_GET['prestudent_id'];
	else 
		die('Prestudent_id ist ungueltig');
else 
	die('Fehlerhafte Parameteruebergabe');
	
$dok = new dokument($conn, null, null, true);
if(!$dok->getPrestudentDokumente($prestudent_id))
	die($dok->errormsg);
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:DOKUMENT="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php

foreach ($dok->result as $row)
{
	?>
  <RDF:li>
      	<RDF:Description  id="<?php echo $row->dokument_kurzbz.'/'.$row->prestudent_id; ?>"  about="<?php echo $rdf_url.'/'.$row->dokument_kurzbz.'/'.$row->prestudent_id; ?>" >
        	<DOKUMENT:dokument_kurzbz><![CDATA[<?php echo $row->dokument_kurzbz  ?>]]></DOKUMENT:dokument_kurzbz>
    		<DOKUMENT:prestudent_id><![CDATA[<?php echo $row->prestudent_id  ?>]]></DOKUMENT:prestudent_id>
    		<DOKUMENT:mitarbeiter_uid><![CDATA[<?php echo $row->mitarbeiter_uid  ?>]]></DOKUMENT:mitarbeiter_uid>
    		<DOKUMENT:datum><![CDATA[<?php echo $row->datum  ?>]]></DOKUMENT:datum>
    		<DOKUMENT:bezeichnung><![CDATA[<?php echo $row->bezeichnung  ?>]]></DOKUMENT:bezeichnung>
      	</RDF:Description>
  </RDF:li>
<?php
}

?>

  </RDF:Seq>
</RDF:RDF>