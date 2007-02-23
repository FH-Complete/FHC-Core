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
include('../vilesci/config.inc.php');
include_once('../include/lehrfach.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

// Einheiten holen
$lehrfachDAO=new lehrfach($conn);
$lehrfachDAO->getAll();



$rdf_url='http://www.technikum-wien.at/tempus/lehrfach';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHRFACH="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/liste">


<?php
foreach ($lehrfachDAO->lehrfach as $lehrfach)
{
	?>
	  <RDF:li>
      	<RDF:Description  id="<?php echo $lehrfach->lehrfach_nr; ?>"  about="<?php echo $rdf_url.'/'.$lehrfach->lehrfach_nr; ?>" >
        	<LEHRFACH:lehrfach_nr><?php echo $lehrfach->lehrfach_nr  ?></LEHRFACH:lehrfach_nr>
			<LEHRFACH:bezeichnung><![CDATA[ <?php echo $lehrfach->bezeichnung  ?> ]]></LEHRFACH:bezeichnung>
    		<LEHRFACH:fachbereich_id><?php echo $lehrfach->fachbereich_id  ?></LEHRFACH:fachbereich_id>
			<LEHRFACH:kurzbz><?php echo $lehrfach->kurzbz  ?></LEHRFACH:kurzbz>
			<LEHRFACH:lehrelink><?php echo $lehrfach->lehrelink  ?></LEHRFACH:lehrelink>
			<LEHRFACH:farbe><?php echo $lehrfach->farbe  ?></LEHRFACH:farbe>
			<LEHRFACH:lehrform_kurzbz><?php echo $lehrfach->lehrform_kurzbz  ?></LEHRFACH:lehrform_kurzbz>
			<LEHRFACH:aktiv><?php echo $lehrfach->aktiv  ?></LEHRFACH:aktiv>
			<LEHRFACH:studiengang_kz><?php echo $lehrfach->studiengang_kz  ?></LEHRFACH:studiengang_kz>
			<LEHRFACH:ects><?php echo $lehrfach->ects  ?></LEHRFACH:ects>
      	</RDF:Description>
      </RDF:li>
<?php
}
?>

  </RDF:Seq>


</RDF:RDF>