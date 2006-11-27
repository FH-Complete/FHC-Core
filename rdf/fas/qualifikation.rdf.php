<?php
/**
 * erstellt ein RDF File mit der qualifikation
 * Atrribut 'qualifikation' in Tabelle 'funktion'
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

$rdf_url='http://www.technikum-wien.at/qualifikation';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:QUALIFIKATION="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
		  	      
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/0' ?>" >
	        	<QUALIFIKATION:qualifikation_id>0</QUALIFIKATION:qualifikation_id>
	    		<QUALIFIKATION:bezeichnung>keine</QUALIFIKATION:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>	      
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/1' ?>" >
	        	<QUALIFIKATION:qualifikation_id>1</QUALIFIKATION:qualifikation_id>
	    		<QUALIFIKATION:bezeichnung>Habilitation</QUALIFIKATION:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/2' ?>" >
	        	<QUALIFIKATION:qualifikation_id>2</QUALIFIKATION:qualifikation_id>
	    		<QUALIFIKATION:bezeichnung>der Habilitation gleichwertige Qualifikation</QUALIFIKATION:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/3' ?>" >
	        	<QUALIFIKATION:qualifikation_id>3</QUALIFIKATION:qualifikation_id>
	    		<QUALIFIKATION:bezeichnung>berufliche Tätigkeit</QUALIFIKATION:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
  </RDF:Seq>


</RDF:RDF>