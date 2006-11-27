<?php
/*
 * Created on 02.12.2004
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
// header f�r no cache
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
include_once('../include/studiengang.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

// raumtypen holen
$studiengangDAO=new studiengang($conn);
$studiengaenge=$studiengangDAO->getAll();

$rdf_url='http://www.technikum-wien.at/tempus/studiengang';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:STUDIENGANG="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
if (is_array($studiengaenge)) {

	foreach ($studiengaenge as $sg)
	{
	?>
  <RDF:li>
      	<RDF:Description  id="<?php echo $sg->studiengang_kz; ?>"  about="<?php echo $rdf_url.'/'.$sg->studiengang_kz; ?>" >
        	<STUDIENGANG:studiengang_kz><?php echo $sg->studiengang_kz  ?></STUDIENGANG:studiengang_kz>
    		<STUDIENGANG:kurzbz><?php echo $sg->kurzbz  ?></STUDIENGANG:kurzbz>
    		<STUDIENGANG:kurzbzlang><?php echo $sg->kurzbzlang  ?></STUDIENGANG:kurzbzlang>
			<STUDIENGANG:bezeichnung><?php echo $sg->bezeichnung  ?></STUDIENGANG:bezeichnung>
			<STUDIENGANG:max_semester><?php echo $sg->max_semester  ?></STUDIENGANG:max_semester>
			<STUDIENGANG:typ><?php echo $sg->typ  ?></STUDIENGANG:typ>
			<STUDIENGANG:farbe><?php echo $sg->farbe  ?></STUDIENGANG:farbe>
			<STUDIENGANG:email><?php echo $sg->email  ?></STUDIENGANG:email>
      	</RDF:Description>
  </RDF:li>
	  <?php
	}

}
?>


  </RDF:Seq>
</RDF:RDF>