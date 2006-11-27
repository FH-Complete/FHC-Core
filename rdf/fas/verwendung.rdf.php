<?php
/**
 * erstellt ein RDF File mit der verwendung
 * Atrribut 'verwendung' in Tabelle 'funktion'
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

$rdf_url='http://www.technikum-wien.at/verwendung';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:VERWENDUNG="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
		  	      
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/1' ?>" >
	        	<VERWENDUNG:verwendungcode>1</VERWENDUNG:verwendungcode>
	    		<VERWENDUNG:bezeichnung>Lehr- und Forschungspersonal (Academic staff)</VERWENDUNG:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>	      
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/2' ?>" >
	        	<VERWENDUNG:verwendungcode>2</VERWENDUNG:verwendungcode>
	    		<VERWENDUNG:bezeichnung>Lehr- und Forschungshilfspersonal (Teaching and Research assistants)</VERWENDUNG:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/3' ?>" >
	        	<VERWENDUNG:verwendungcode>3</VERWENDUNG:verwendungcode>
	    		<VERWENDUNG:bezeichnung>Akademische Dienste für Studierende (Academic Support)</VERWENDUNG:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/4' ?>" >
	        	<VERWENDUNG:verwendungcode>4</VERWENDUNG:verwendungcode>
	    		<VERWENDUNG:bezeichnung>Soziale Dienste und Gesundheitsdienste (Health and Social Support)</VERWENDUNG:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/5' ?>" >
	        	<VERWENDUNG:verwendungcode>5</VERWENDUNG:verwendungcode>
	    		<VERWENDUNG:bezeichnung>Studiengangsleiter/in</VERWENDUNG:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/6' ?>" >
	        	<VERWENDUNG:verwendungcode>6</VERWENDUNG:verwendungcode>
	    		<VERWENDUNG:bezeichnung>Leiter/in FH-Kollegium</VERWENDUNG:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/7' ?>" >
	        	<VERWENDUNG:verwendungcode>7</VERWENDUNG:verwendungcode>
	    		<VERWENDUNG:bezeichnung>Management (School Level Management)</VERWENDUNG:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/8' ?>" >
	        	<VERWENDUNG:verwendungcode>8</VERWENDUNG:verwendungcode>
	    		<VERWENDUNG:bezeichnung>Verwaltung (School Level Administrative Personnel)</VERWENDUNG:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/9' ?>" >
	        	<VERWENDUNG:verwendungcode>9</VERWENDUNG:verwendungcode>
	    		<VERWENDUNG:bezeichnung>Hauspersonal, Gebäude- / Haustechnik (Maintenance and Operations Personnel)</VERWENDUNG:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>


  </RDF:Seq>


</RDF:RDF>