<?php
/**
 * erstellt ein RDF File mit den beschaeftigungsarten
 * Atrribut 'beschart1' in Tabelle 'funktion'
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

$rdf_url='http://www.technikum-wien.at/beschaeftigungsart1';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:BESCHAEFTIGUNGSART1="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/1' ?>" >
	        	<BESCHAEFTIGUNGSART1:ba1code>1</BESCHAEFTIGUNGSART1:ba1code>
	    		<BESCHAEFTIGUNGSART1:bezeichnung>Dienstverhältnis zum Bund</BESCHAEFTIGUNGSART1:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/2' ?>" >
	        	<BESCHAEFTIGUNGSART1:ba1code>2</BESCHAEFTIGUNGSART1:ba1code>
	    		<BESCHAEFTIGUNGSART1:bezeichnung>Dienstverhältnis zu einer anderen Gebietskörperschaft</BESCHAEFTIGUNGSART1:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/3' ?>" >
	        	<BESCHAEFTIGUNGSART1:ba1code>3</BESCHAEFTIGUNGSART1:ba1code>
	    		<BESCHAEFTIGUNGSART1:bezeichnung>Dienstverhältnis zur Bildungseinrichtung oder deren Träger ("Echter" Dienstvertrag)</BESCHAEFTIGUNGSART1:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/4' ?>" >
	        	<BESCHAEFTIGUNGSART1:ba1code>4</BESCHAEFTIGUNGSART1:ba1code>
	    		<BESCHAEFTIGUNGSART1:bezeichnung>Dienstverhältnis zur Bildungseinrichtung oder deren Träger (Freier Dienstvertrag)</BESCHAEFTIGUNGSART1:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/5' ?>" >
	        	<BESCHAEFTIGUNGSART1:ba1code>5</BESCHAEFTIGUNGSART1:ba1code>
	    		<BESCHAEFTIGUNGSART1:bezeichnung>Lehr- oder Ausbildungsverhältnis</BESCHAEFTIGUNGSART1:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/6' ?>" >
	        	<BESCHAEFTIGUNGSART1:ba1code>6</BESCHAEFTIGUNGSART1:ba1code>
    		<BESCHAEFTIGUNGSART1:bezeichnung>Sonstiges Beschäftigungsverhältnis</BESCHAEFTIGUNGSART1:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>

  </RDF:Seq>


</RDF:RDF>