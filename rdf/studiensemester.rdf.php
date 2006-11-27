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
include_once('../include/studiensemester.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

// studiensemester holen
$studiensemesterDAO=new studiensemester($conn);
$studiensemesterAll=$studiensemesterDAO->getAll();

$rdf_url='http://www.technikum-wien.at/tempus/studiensemester';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:STUDIENSEMESTER="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/liste">


<?php
foreach ($studiensemesterAll as $ss)
{
	?>
	  <RDF:li>
      	<RDF:Description  id="<?php echo $ss->kurzbz; ?>"  about="<?php echo $rdf_url.'/'.$ss->kurzbz; ?>" >
        	<STUDIENSEMESTER:kurzbz><?php echo $ss->kurzbz  ?></STUDIENSEMESTER:kurzbz>
		<STUDIENSEMESTER:start><![CDATA[ <?php echo $ss->start  ?> ]]></STUDIENSEMESTER:start>
    		<STUDIENSEMESTER:ende><?php echo $ss->ende  ?></STUDIENSEMESTER:ende>
      	</RDF:Description>
      </RDF:li>
<?php
}
?>

  </RDF:Seq>


</RDF:RDF>
