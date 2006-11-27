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
include_once('../include/fachbereich.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';


// test

$einheit_kurzbz='';
$grp='1';
//$ver='A';
$sem=5;
$stg_kz=145;


/*
$einheit_kurzbz='';
$grp=$_GET['grp'];
$ver=$_GET['ver'];
$sem=$_GET['sem'];
$stg_kz=$_GET['stg_kz']; */

// fachbereiche holen
$fachbereichDAO=new fachbereich($conn);
$fachbereiche=$fachbereichDAO->getAll();



$rdf_url='http://www.technikum-wien.at/tempus/fachbereich';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:FACHBEREICH="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
if (is_array($fachbereiche)) {

	foreach ($fachbereiche as $fb)
	{
	?>
  <RDF:li>
      	<RDF:Description  id="<?php echo $fb->id; ?>"  about="<?php echo $rdf_url.'/'.$fb->id; ?>" >
        	<FACHBEREICH:id><?php echo $fb->id  ?></FACHBEREICH:id>
    		<FACHBEREICH:kurzbz><?php echo $fb->kurzbz  ?></FACHBEREICH:kurzbz>
    		<FACHBEREICH:bezeichnung><?php echo $fb->bezeichnung  ?></FACHBEREICH:bezeichnung>
    		<FACHBEREICH:farbe><?php echo $fb->farbe  ?></FACHBEREICH:farbe>
    		<FACHBEREICH:studiengang_kz><?php echo $fb->studiengang_kz  ?></FACHBEREICH:studiengang_kz>
    		<FACHBEREICH:studiengang_kurzbz><?php echo $fb->studiengang_kurzbz  ?></FACHBEREICH:studiengang_kurzbz>
      	</RDF:Description>
  </RDF:li>
	  <?php
	}

}
?>


  </RDF:Seq>
</RDF:RDF>