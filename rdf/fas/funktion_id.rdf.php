<?php
/**
 * erstellt ein RDF File mit den Funktions Integer Werten
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
include('../../include/fas/studiensemester.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
   	
$rdf_url='http://www.technikum-wien.at/funktion_id';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:FUNKTION_ID="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">

		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/0'; ?>" >
	    		<FUNKTION_ID:funktion>0</FUNKTION_ID:funktion>
	    		<FUNKTION_ID:bezeichnung>Mitarbeiter</FUNKTION_ID:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/1'; ?>" >
	    		<FUNKTION_ID:funktion>1</FUNKTION_ID:funktion>
	    		<FUNKTION_ID:bezeichnung>Lektor</FUNKTION_ID:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/2'; ?>" >
	    		<FUNKTION_ID:funktion>2</FUNKTION_ID:funktion>
	    		<FUNKTION_ID:bezeichnung>Fachbereichskoordinator</FUNKTION_ID:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/3'; ?>" >
	    		<FUNKTION_ID:funktion>3</FUNKTION_ID:funktion>
	    		<FUNKTION_ID:bezeichnung>Assistenz</FUNKTION_ID:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/4'; ?>" >
	    		<FUNKTION_ID:funktion>4</FUNKTION_ID:funktion>
	    		<FUNKTION_ID:bezeichnung>Rektor</FUNKTION_ID:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/5'; ?>" >
	    		<FUNKTION_ID:funktion>5</FUNKTION_ID:funktion>
	    		<FUNKTION_ID:bezeichnung>Studiengangsleiter</FUNKTION_ID:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/6'; ?>" >
	    		<FUNKTION_ID:funktion>6</FUNKTION_ID:funktion>
	    		<FUNKTION_ID:bezeichnung>Fachbereichsleiter</FUNKTION_ID:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
  </RDF:Seq>


</RDF:RDF>