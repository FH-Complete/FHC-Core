<?php
/**
 * erstellt ein RDF File mit den Studiensemestern
 * Created on 23.3.2006 
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
include('../../include/fas/studiensemester.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
   	
$rdf_url='http://www.technikum-wien.at/studiensemester';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:STUDIENSEMESTER="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php
$studiensemesterDAO=new studiensemester($conn);
$studiensemesterDAO->getAll();   	

	foreach ($studiensemesterDAO->result as $studiensemester)
	{
		?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$studiensemester->studiensemester_id; ?>" >
	        	<STUDIENSEMESTER:studiensemester_id><?php echo $studiensemester->studiensemester_id;  ?></STUDIENSEMESTER:studiensemester_id>
	    		<STUDIENSEMESTER:aktuell><?php echo ($studiensemester->aktuell?'Ja':'Nein');  ?></STUDIENSEMESTER:aktuell>
	    		<STUDIENSEMESTER:art><?php echo $studiensemester->art;  ?></STUDIENSEMESTER:art>
	    		<STUDIENSEMESTER:jahr><?php echo $studiensemester->jahr;  ?></STUDIENSEMESTER:jahr>
	    		<STUDIENSEMESTER:kurzbz><?php echo ($studiensemester->art==1?'WS':'SS').$studiensemester->jahr;  ?></STUDIENSEMESTER:kurzbz>
	      	</RDF:Description>
	      </RDF:li>
	<?php
	}
?>

  </RDF:Seq>


</RDF:RDF>