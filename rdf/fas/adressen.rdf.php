<?php
/**
 * erstellt ein RDF File mit den Adressen
 * Created on 23.3.2006
 * Aufruf: adressen.rdf.php?pers_id=xyz
 *         adressen.rdf.php?adress_id=xyz
 */
// header f�r no cache
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
include_once('../../include/fas/adresse.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
   	
$rdf_url='http://www.technikum-wien.at/adressen';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ADRESSEN="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php
$adressenDAO=new adresse($conn);
   	
//Parameter holen
if(isset($_GET['pers_id']))
{
	$pers_id = $_GET['pers_id'];
	$adressenDAO->load_pers($pers_id);


	foreach ($adressenDAO->result as $adressen)
	{
		?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$adressen->adresse_id; ?>" >
	        	<ADRESSEN:adresse_id><?php echo $adressen->adresse_id;  ?></ADRESSEN:adresse_id>
	    		<ADRESSEN:bismeldeadresse><?php echo ($adressen->bismeldeadresse?'Ja':'Nein');  ?></ADRESSEN:bismeldeadresse>
	    		<ADRESSEN:gemeinde><?php echo $adressen->gemeinde;  ?></ADRESSEN:gemeinde>
	    		<ADRESSEN:name><?php echo $adressen->name;  ?></ADRESSEN:name>
	    		<ADRESSEN:nation><?php echo $adressen->nation;  ?></ADRESSEN:nation>
	    		<ADRESSEN:ort><?php echo $adressen->ort;  ?></ADRESSEN:ort>
	    		<ADRESSEN:person_id><?php echo $adressen->person_id;  ?></ADRESSEN:person_id>
	    		<ADRESSEN:plz><?php echo $adressen->plz;  ?></ADRESSEN:plz>
	    		<ADRESSEN:strasse><?php echo $adressen->strasse;  ?></ADRESSEN:strasse>
	    		<ADRESSEN:typ><?php echo $adressen->typ;  ?></ADRESSEN:typ>
	    		<ADRESSEN:zustelladresse><?php echo ($adressen->zustelladresse?'Ja':'Nein');  ?></ADRESSEN:zustelladresse>
	      	</RDF:Description>
	      </RDF:li>
	<?php
	
	}
}
elseif(isset($_GET['adress_id']))
{	
	
	$adress_id = $_GET['adress_id'];
	
	$adressenDAO->load($adress_id)

	?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$adressenDAO->adresse_id; ?>" >
	        	<ADRESSEN:adresse_id><?php echo $adressenDAO->adresse_id;  ?></ADRESSEN:adresse_id>
	    		<ADRESSEN:bismeldeadresse><?php echo ($adressenDAO->bismeldeadresse?'Ja':'Nein');  ?></ADRESSEN:bismeldeadresse>
	    		<ADRESSEN:gemeinde><?php echo $adressenDAO->gemeinde;  ?></ADRESSEN:gemeinde>
	    		<ADRESSEN:name><?php echo $adressenDAO->name;  ?></ADRESSEN:name>
	    		<ADRESSEN:nation><?php echo $adressenDAO->nation;  ?></ADRESSEN:nation>
	    		<ADRESSEN:ort><?php echo $adressenDAO->ort;  ?></ADRESSEN:ort>
	    		<ADRESSEN:person_id><?php echo $adressenDAO->person_id;  ?></ADRESSEN:person_id>
	    		<ADRESSEN:plz><?php echo $adressenDAO->plz;  ?></ADRESSEN:plz>
	    		<ADRESSEN:strasse><?php echo $adressenDAO->strasse;  ?></ADRESSEN:strasse>
	    		<ADRESSEN:typ><?php echo $adressenDAO->typ;  ?></ADRESSEN:typ>
	    		<ADRESSEN:zustelladresse><?php echo ($adressenDAO->zustelladresse?'Ja':'Nein');  ?></ADRESSEN:zustelladresse>
	      	</RDF:Description>
	      </RDF:li>
	<?php
}


?>

  </RDF:Seq>


</RDF:RDF>