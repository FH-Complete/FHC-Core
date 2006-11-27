<?php
/**
 * erstellt ein RDF File mit dem Beschaeftigungsausmass
 * Atrribut 'ausmass' in Tabelle 'funktion'
 * Created on 2.5.2006 
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

$rdf_url='http://www.technikum-wien.at/ausmass';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:AUSMASS="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/1' ?>" >
	        	<AUSMASS:id>1</AUSMASS:id>
	    		<AUSMASS:bezeichnung>Vollzeit</AUSMASS:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/2' ?>" >
	        	<AUSMASS:id>2</AUSMASS:id>
	    		<AUSMASS:bezeichnung><![CDATA[<= 15 Wochenstunden]]></AUSMASS:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/3' ?>" >
	        	<AUSMASS:id>3</AUSMASS:id>
	    		<AUSMASS:bezeichnung>16 - 25 Wochenstunden</AUSMASS:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/4' ?>" >
	        	<AUSMASS:id>4</AUSMASS:id>
	    		<AUSMASS:bezeichnung>26 - 35 Wochenstunden</AUSMASS:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/5' ?>" >
	        	<AUSMASS:id>5</AUSMASS:id>
	    		<AUSMASS:bezeichnung>Karenz</AUSMASS:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
  </RDF:Seq>
</RDF:RDF>