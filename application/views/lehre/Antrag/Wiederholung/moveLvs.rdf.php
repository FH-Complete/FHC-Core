<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:DBDML="http://www.technikum-wien.at/dbdml/rdf#"
>
  <RDF:Seq RDF:about="http://www.technikum-wien.at/dbdml/msg">
	<RDF:li>
		<RDF:Description RDF:about="http://www.technikum-wien.at/dbdml/0" >
			<DBDML:return><?= $return ? 'true' : 'false'; ?></DBDML:return>
			<DBDML:errormsg><![CDATA[<?= implode("/n", $errormsg);?>]]></DBDML:errormsg>
			<DBDML:warning><![CDATA[]]></DBDML:warning>
			<DBDML:data><![CDATA[]]></DBDML:data>
		</RDF:Description>
	</RDF:li>
  </RDF:Seq>
</RDF:RDF>
