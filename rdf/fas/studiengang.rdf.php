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
include('../../include/fas/studiengang.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
   	
$rdf_url='http://www.technikum-wien.at/studiengang';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:STUDIENGANG="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php
$studiengangDAO=new studiengang($conn);
$studiengangDAO->getAll();   	

	foreach ($studiengangDAO->result as $studiengang)
	{
		?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$studiengang->studiengang_id; ?>" >
	        	<STUDIENGANG:studiengang_id><?php echo $studiengang->studiengang_id;  ?></STUDIENGANG:studiengang_id>
	    		<STUDIENGANG:name><?php 
	    		$art='';
	    		if($studiengang->studiengangsart==1)
	    			$art='(B) ';
	    		if($studiengang->studiengangsart==2)
	    			$art='(M) ';
	    		if($studiengang->studiengangsart==3)
	    			$art='(D) ';
	    		echo $art.$studiengang->name;  ?></STUDIENGANG:name>
	      	</RDF:Description>
	      </RDF:li>
	<?php
	}
?>

  </RDF:Seq>


</RDF:RDF>