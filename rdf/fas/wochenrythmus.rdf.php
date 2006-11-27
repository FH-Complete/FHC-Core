<?php
/**
 * erstellt ein RDF File mit dem WOCHENRYTHMUS
 * Atrribut 'ivar' in Tabelle 'lehreinheit'
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

$rdf_url='http://www.technikum-wien.at/wochenrythmus';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:WOCHENRYTHMUS="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">

	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/0' ?>" >
	        	<WOCHENRYTHMUS:wochenrythmus_id>0</WOCHENRYTHMUS:wochenrythmus_id>
	    		<WOCHENRYTHMUS:bezeichnung>Geblockt</WOCHENRYTHMUS:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/1' ?>" >
	        	<WOCHENRYTHMUS:wochenrythmus_id>1</WOCHENRYTHMUS:wochenrythmus_id>
	    		<WOCHENRYTHMUS:bezeichnung>1 Wöchentlich</WOCHENRYTHMUS:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/2' ?>" >
	        	<WOCHENRYTHMUS:wochenrythmus_id>2</WOCHENRYTHMUS:wochenrythmus_id>
	    		<WOCHENRYTHMUS:bezeichnung>2 Wöchentlich</WOCHENRYTHMUS:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/3' ?>" >
	        	<WOCHENRYTHMUS:wochenrythmus_id>3</WOCHENRYTHMUS:wochenrythmus_id>
	    		<WOCHENRYTHMUS:bezeichnung>3 Wöchentlich</WOCHENRYTHMUS:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/4' ?>" >
	        	<WOCHENRYTHMUS:wochenrythmus_id>4</WOCHENRYTHMUS:wochenrythmus_id>
	    		<WOCHENRYTHMUS:bezeichnung>4 Wöchentlich</WOCHENRYTHMUS:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>

  </RDF:Seq>
</RDF:RDF>