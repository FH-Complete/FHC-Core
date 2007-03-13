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
require_once('../include/lehreinheit.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$lehreinheit_id = (isset($_GET['lehreinheit_id'])?$_GET['lehreinheit_id']:'');
//$_GET['studiengang_kz'];
//$_GET['semester'];
//$_GET['lehrveranstaltung_id'];

if($lehreinheit_id!='')
{
	$lehreinheit=new lehreinheit($conn, null, true);
	$lehreinheit->load($lehreinheit_id);
}
else 
{
	die('Fehlerhafte Parameteruebergabe');
}


$rdf_url='http://www.technikum-wien.at/lehreinheit';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHREINHEIT="<?php echo $rdf_url; ?>/rdf#"
>
   <RDF:Seq about="<?php echo $rdf_url ?>/liste">
      <RDF:li>
         <RDF:Description  id="<?php echo $lehreinheit->lehreinheit_id; ?>"  about="<?php echo $rdf_url.'/'.$lehreinheit->lehreinheit_id; ?>" >
            <LEHREINHEIT:lehreinheit_id><?php echo $lehreinheit->lehreinheit_id  ?></LEHREINHEIT:lehreinheit_id>
            <LEHREINHEIT:lehrveranstaltung_id><?php echo $lehreinheit->lehrveranstaltung_id ?></LEHREINHEIT:lehrveranstaltung_id>
            <LEHREINHEIT:studiensemester_kurzbz><![CDATA[<?php echo $lehreinheit->studiensemester_kurzbz ?>]]></LEHREINHEIT:studiensemester_kurzbz>
            <LEHREINHEIT:lehrfach_id><?php echo $lehreinheit->lehrfach_id  ?></LEHREINHEIT:lehrfach_id>
            <LEHREINHEIT:lehrform_kurzbz><![CDATA[<?php echo $lehreinheit->lehrform_kurzbz ?>]]></LEHREINHEIT:lehrform_kurzbz>
            <LEHREINHEIT:stundenblockung><?php echo $lehreinheit->stundenblockung ?></LEHREINHEIT:stundenblockung>
            <LEHREINHEIT:wochenrythmus><?php echo $lehreinheit->wochenrythmus ?></LEHREINHEIT:wochenrythmus>
            <LEHREINHEIT:start_kw><?php echo $lehreinheit->start_kw  ?></LEHREINHEIT:start_kw>
            <LEHREINHEIT:raumtyp><![CDATA[<?php echo $lehreinheit->raumtyp ?>]]></LEHREINHEIT:raumtyp>
            <LEHREINHEIT:raumtypalternativ><![CDATA[<?php echo $lehreinheit->raumtypalternativ ?>]]></LEHREINHEIT:raumtypalternativ>
            <LEHREINHEIT:sprache><![CDATA[<?php echo $lehreinheit->sprache ?>]]></LEHREINHEIT:sprache>
            <LEHREINHEIT:lehre><?php echo ($lehreinheit->lehre?'Ja':'Nein') ?></LEHREINHEIT:lehre>
            <LEHREINHEIT:anmerkung><![CDATA[<?php echo $lehreinheit->anmerkung ?>]]></LEHREINHEIT:anmerkung>
            <LEHREINHEIT:unr><?php echo $lehreinheit->unr ?></LEHREINHEIT:unr>
            <LEHREINHEIT:lvnr><?php echo $lehreinheit->lvnr ?></LEHREINHEIT:lvnr>
         </RDF:Description>
      </RDF:li>
   </RDF:Seq>
</RDF:RDF>