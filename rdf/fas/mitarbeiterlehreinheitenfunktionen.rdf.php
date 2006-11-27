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

$rdf_url='http://www.technikum-wien.at/mitarbeiterlehreinheitenfunktionen';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:FKT="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/1'?>" >
	        	<FKT:funktion_id>1</FKT:funktion_id>
	        	<FKT:bezeichnung>Betreuer</FKT:bezeichnung>
	      	</RDF:Description>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/2'?>" >
	        	<FKT:funktion_id>2</FKT:funktion_id>
	        	<FKT:bezeichnung>Lehrveranstaltungsleiter</FKT:bezeichnung>
	      	</RDF:Description>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/3'?>" >
	        	<FKT:funktion_id>3</FKT:funktion_id>
	        	<FKT:bezeichnung>Zweitbetreuer</FKT:bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
  </RDF:Seq>
</RDF:RDF>