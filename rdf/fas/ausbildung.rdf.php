<?php
/**
 * Erstellt ein RDF mit den Ausbildungen aus der BIS Meldung
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

$rdf_url='http://www.technikum-wien.at/ausbildung';
?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:AUSBILDUNG="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
	  <RDF:li>
      	<RDF:Description RDF:about="1" >
        	<AUSBILDUNG:ausbildung_id>1</AUSBILDUNG:ausbildung_id>
    		<AUSBILDUNG:bezeichnung>Universitätsabschluss mit Doktorat als Zweit- oder Dritt- oder PhD-Abschluss</AUSBILDUNG:bezeichnung>
      	</RDF:Description>
      </RDF:li>
   	  <RDF:li>
      	<RDF:Description RDF:about="2" >
        	<AUSBILDUNG:ausbildung_id>2</AUSBILDUNG:ausbildung_id>
    		<AUSBILDUNG:bezeichnung>Universitäts- oder Hochschulabschluss auf Diplom oder Magisterebene, Doktor als Erstabschluss</AUSBILDUNG:bezeichnung>
      	</RDF:Description>
      </RDF:li>
      <RDF:li>
      	<RDF:Description RDF:about="3" >
        	<AUSBILDUNG:ausbildung_id>3</AUSBILDUNG:ausbildung_id>
    		<AUSBILDUNG:bezeichnung>Fachhochschulabschluss auf Diplom- oder Magisterebene</AUSBILDUNG:bezeichnung>
      	</RDF:Description>
      </RDF:li>
	  <RDF:li>
      	<RDF:Description RDF:about="4" >
        	<AUSBILDUNG:ausbildung_id>4</AUSBILDUNG:ausbildung_id>
    		<AUSBILDUNG:bezeichnung>Universitätsabschluss auf Bakkalaureatsebene</AUSBILDUNG:bezeichnung>
      	</RDF:Description>
      </RDF:li>
      <RDF:li>
      	<RDF:Description RDF:about="5" >
        	<AUSBILDUNG:ausbildung_id>5</AUSBILDUNG:ausbildung_id>
    		<AUSBILDUNG:bezeichnung>Fachhochschulabschluss auf Bakkalaureatsebene</AUSBILDUNG:bezeichnung>
      	</RDF:Description>
      </RDF:li>
      <RDF:li>
      	<RDF:Description RDF:about="6" >
        	<AUSBILDUNG:ausbildung_id>6</AUSBILDUNG:ausbildung_id>
    		<AUSBILDUNG:bezeichnung>Diplom einer Akademie</AUSBILDUNG:bezeichnung>
      	</RDF:Description>
      </RDF:li>
      <RDF:li>
      	<RDF:Description RDF:about="7" >
        	<AUSBILDUNG:ausbildung_id>7</AUSBILDUNG:ausbildung_id>
    		<AUSBILDUNG:bezeichnung>Anderer tertiärer Bildungsabschluss</AUSBILDUNG:bezeichnung>
      	</RDF:Description>
      </RDF:li>
      <RDF:li>
      	<RDF:Description RDF:about="8" >
        	<AUSBILDUNG:ausbildung_id>8</AUSBILDUNG:ausbildung_id>
    		<AUSBILDUNG:bezeichnung>Reifeprüfung einer allgemeinbildenden höheren Schule</AUSBILDUNG:bezeichnung>
      	</RDF:Description>
      </RDF:li>
       <RDF:li>
      	<RDF:Description RDF:about="9" >
        	<AUSBILDUNG:ausbildung_id>9</AUSBILDUNG:ausbildung_id>
    		<AUSBILDUNG:bezeichnung>Reifeprüfung einer berufsbildenden höheren Schule</AUSBILDUNG:bezeichnung>
      	</RDF:Description>
      </RDF:li>
       <RDF:li>
      	<RDF:Description RDF:about="10" >
        	<AUSBILDUNG:ausbildung_id>10</AUSBILDUNG:ausbildung_id>
    		<AUSBILDUNG:bezeichnung>Lehrabschlussprüfung</AUSBILDUNG:bezeichnung>
      	</RDF:Description>
      </RDF:li>
      <RDF:li>
      	<RDF:Description RDF:about="11" >
        	<AUSBILDUNG:ausbildung_id>11</AUSBILDUNG:ausbildung_id>
    		<AUSBILDUNG:bezeichnung>Pflichtschule</AUSBILDUNG:bezeichnung>
      	</RDF:Description>
      </RDF:li>
  </RDF:Seq>
</RDF:RDF>