<?php
/**
 * erstellt ein RDF File mit den Telefonnummern
 * Created on 23.3.2006
 * Aufruf: telefonnummern.rdf.php?pers_id=xyz
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
include('../../vilesci/config.inc.php');
include_once('../../include/fas/telefonnummer.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/telefonnummern';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:TELEFONNUMMERN="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php
if(isset($_GET['pers_id']))
{
	$pers_id = $_GET['pers_id'];

	// Telefonnummern holen
	$telefonnummernDAO=new telefonnummer($conn);
	$telefonnummernDAO->load_pers($pers_id);
			
	foreach ($telefonnummernDAO->result as $telefonnummern)
	{
		?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$telefonnummern->telefonnummer_id; ?>" >
	        	<TELEFONNUMMERN:telefonnummer_id><?php echo $telefonnummern->telefonnummer_id;  ?></TELEFONNUMMERN:telefonnummer_id>
	        	<TELEFONNUMMERN:name><?php echo $telefonnummern->name;  ?></TELEFONNUMMERN:name>
	    		<TELEFONNUMMERN:nummer><?php echo $telefonnummern->nummer;  ?></TELEFONNUMMERN:nummer>
	    		<TELEFONNUMMERN:person_id><?php echo $telefonnummern->person_id;  ?></TELEFONNUMMERN:person_id>
	    		<TELEFONNUMMERN:typ><?php echo $telefonnummern->typ;  ?></TELEFONNUMMERN:typ>
	      	</RDF:Description>
	      </RDF:li>
	<?php
	
	}
}
elseif(isset($_GET['telefonnummer_id']))
{
	$telefonnummer_id = $_GET['telefonnummer_id'];
	// Telefonnummern holen
	$telefonnummernDAO=new telefonnummer($conn);
	$telefonnummernDAO->load($telefonnummer_id);
		?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$telefonnummernDAO->telefonnummer_id; ?>" >
	        	<TELEFONNUMMERN:telefonnummer_id><?php echo $telefonnummernDAO->telefonnummer_id;  ?></TELEFONNUMMERN:telefonnummer_id>
	        	<TELEFONNUMMERN:name><?php echo $telefonnummernDAO->name;  ?></TELEFONNUMMERN:name>
	    		<TELEFONNUMMERN:nummer><?php echo $telefonnummernDAO->nummer;  ?></TELEFONNUMMERN:nummer>
	    		<TELEFONNUMMERN:person_id><?php echo $telefonnummernDAO->person_id;  ?></TELEFONNUMMERN:person_id>
	    		<TELEFONNUMMERN:typ><?php echo $telefonnummernDAO->typ;  ?></TELEFONNUMMERN:typ>
	      	</RDF:Description>
	      </RDF:li>
	<?php
}
?>

  </RDF:Seq>


</RDF:RDF>