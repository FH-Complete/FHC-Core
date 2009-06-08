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
include('../vilesci/config.inc.php');
include_once('../include/einheit.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';


// Einheiten holen
$einheitenDAO=new einheit($conn);
$einheiten=$einheitenDAO->getAll();



$rdf_url='http://www.technikum-wien.at/tempus/einheiten';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:EINHEIT="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
foreach ($einheiten as $einheit)
{
	?>
	  <RDF:li>
      	<RDF:Description  id="<?php echo $einheit->kurzbz; ?>"  about="<?php echo $rdf_url.'/'.$einheit->kurzbz; ?>" >
        	<EINHEIT:kurzbz><?php echo $einheit->kurzbz  ?></EINHEIT:kurzbz>
    		<EINHEIT:stg_kz><?php echo $einheit->stg_kz  ?></EINHEIT:stg_kz>
			<EINHEIT:stg_kurzbz><?php echo $einheit->stg_kurzbz  ?></EINHEIT:stg_kurzbz>
			<EINHEIT:bezeichnung><?php echo $einheit->bezeichnung  ?></EINHEIT:bezeichnung>
			<EINHEIT:semester><?php echo $einheit->semester  ?></EINHEIT:semester>
			<EINHEIT:typ><?php echo $einheit->typ  ?></EINHEIT:typ>
      	</RDF:Description>
      </RDF:li>
<?php
}
?>

  </RDF:Seq>


</RDF:RDF>