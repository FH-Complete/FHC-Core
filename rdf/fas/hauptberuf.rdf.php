<?php
/**
 * erstellt ein RDF File mit den hauptberufen
 * Atrribut 'hauptberuf' in Tabelle 'funktion'
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

$rdf_url='http://www.technikum-wien.at/hauptberuf';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:HAUPTBERUF="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
		  	      
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/0' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>0</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Universität</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>	      
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/1' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>1</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Fachhochschule</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/2' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>2</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Andere postsekundäre Bildungseinrichtung</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/3' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>3</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Allgemeinbildende höhere Schule</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	       <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/4' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>4</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Berufsbildende höhere Schule</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/5' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>5</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Andere Schule</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/6' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>6</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Öffentlicher Sektor</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/7' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>7</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Unternehmenssektor</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/8' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>8</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Freiberuflich tätig</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/9' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>9</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Privater gemeinnütziger Sektor</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/10' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>10</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Ausserhochschulische Forschungseinrichtung</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/11' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>11</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Internationale Organisation</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/12' ?>" >
	        	<HAUPTBERUF:hauptberuf_id>12</HAUPTBERUF:hauptberuf_id>
	    		<HAUPTBERUF:bezeichnung>Sonstiges</HAUPTBERUF:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	    

  </RDF:Seq>


</RDF:RDF>