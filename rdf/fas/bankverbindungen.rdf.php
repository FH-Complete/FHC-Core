<?php
/**
 * erstellt ein RDF File mit den Bankverbindungen
 * Created on 23.3.2006
 * Aufruf: bankverbindungen.rdf.php?pers_id=xyz
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
include_once('../../include/fas/bankverbindung.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/bankverbindungen';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:BANKVERBINDUNGEN="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php
//Parameter holen
if(isset($_GET['pers_id']))
{
	$pers_id = $_GET['pers_id'];

	// Bankverbindungen holen
	$bankverbindungenDAO=new bankverbindung($conn);
	$bankverbindungenDAO->load_pers($pers_id);
		
			
	foreach ($bankverbindungenDAO->result as $bankverbindungen)
	{
		?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$bankverbindungen->bankverbindung_id; ?>" >
	        	<BANKVERBINDUNGEN:bankverbindung_id><?php echo $bankverbindungen->bankverbindung_id;  ?></BANKVERBINDUNGEN:bankverbindung_id>
	        	<BANKVERBINDUNGEN:person_id><?php echo $bankverbindungen->person_id;  ?></BANKVERBINDUNGEN:person_id>
	    		<BANKVERBINDUNGEN:name><![CDATA[<?php echo $bankverbindungen->name;  ?>]]></BANKVERBINDUNGEN:name>
	    		<BANKVERBINDUNGEN:anschrift><![CDATA[<?php echo $bankverbindungen->anschrift;  ?>]]></BANKVERBINDUNGEN:anschrift>
	    		<BANKVERBINDUNGEN:blz><?php echo $bankverbindungen->blz; ?></BANKVERBINDUNGEN:blz>
	    		<BANKVERBINDUNGEN:bic><?php echo $bankverbindungen->bic; ?></BANKVERBINDUNGEN:bic>
	    		<BANKVERBINDUNGEN:kontonummer><?php echo $bankverbindungen->kontonr; ?></BANKVERBINDUNGEN:kontonummer>
	    		<BANKVERBINDUNGEN:iban><?php echo $bankverbindungen->iban; ?></BANKVERBINDUNGEN:iban>
	    		<BANKVERBINDUNGEN:typ><?php echo fmod($bankverbindungen->typ,10); ?></BANKVERBINDUNGEN:typ>
	    		<BANKVERBINDUNGEN:typ_name><?php echo $bankverbindungen->getTypBezeichnung(fmod($bankverbindungen->typ,10)); ?></BANKVERBINDUNGEN:typ_name>
	    		<BANKVERBINDUNGEN:verrechnungskonto><?php echo ($bankverbindungen->typ>10?'Ja':'Nein'); ?></BANKVERBINDUNGEN:verrechnungskonto>
	      	</RDF:Description>
	      </RDF:li>
	<?php
	
	}
}
elseif(isset($_GET['bankverbindung_id']))
{
	$bankverbindung_id = $_GET['bankverbindung_id'];

	// Bankverbindungen holen
	$bankverbindungenDAO=new bankverbindung($conn);
	$bankverbindungenDAO->load($bankverbindung_id);
	?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$bankverbindungenDAO->bankverbindung_id; ?>" >
	        	<BANKVERBINDUNGEN:bankverbindung_id><?php echo $bankverbindungenDAO->bankverbindung_id;  ?></BANKVERBINDUNGEN:bankverbindung_id>
	        	<BANKVERBINDUNGEN:person_id><?php echo $bankverbindungenDAO->person_id;  ?></BANKVERBINDUNGEN:person_id>
	    		<BANKVERBINDUNGEN:name><![CDATA[<?php echo $bankverbindungenDAO->name;  ?>]]></BANKVERBINDUNGEN:name>
	    		<BANKVERBINDUNGEN:anschrift><![CDATA[<?php echo $bankverbindungenDAO->anschrift;  ?>]]></BANKVERBINDUNGEN:anschrift>
	    		<BANKVERBINDUNGEN:blz><?php echo $bankverbindungenDAO->blz; ?></BANKVERBINDUNGEN:blz>
	    		<BANKVERBINDUNGEN:bic><?php echo $bankverbindungenDAO->bic; ?></BANKVERBINDUNGEN:bic>
	    		<BANKVERBINDUNGEN:kontonummer><?php echo $bankverbindungenDAO->kontonr; ?></BANKVERBINDUNGEN:kontonummer>
	    		<BANKVERBINDUNGEN:iban><?php echo $bankverbindungenDAO->iban; ?></BANKVERBINDUNGEN:iban>
	    		<BANKVERBINDUNGEN:typ><?php echo fmod($bankverbindungenDAO->typ,10); ?></BANKVERBINDUNGEN:typ>
	    		<BANKVERBINDUNGEN:verrechnungskonto><?php echo ($bankverbindungenDAO->typ>10?'Ja':'Nein'); ?></BANKVERBINDUNGEN:verrechnungskonto>
	    		
	      	</RDF:Description>
	      </RDF:li>
	<?php
}
?>

  </RDF:Seq>


</RDF:RDF>