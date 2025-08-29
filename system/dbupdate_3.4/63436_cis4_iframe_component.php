<?php

$xsd= <<<EOD
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
	<xs:element name="content">
		<xs:complexType>
			<xs:sequence>
				<xs:element name="url" type="xs:string"/>
			</xs:sequence>
		</xs:complexType>
	</xs:element>
</xs:schema>
EOD;

$xslt_xhtml= <<<EOD
<xsl:stylesheet version="1.0"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" encoding="UTF-8"/>
	<xsl:template match="content">
		<xsl:choose>
			<xsl:when test="string(url)">
				<iframe
					src="{url}"
					frameborder="0"
					style="width:100%; height:90vh; border:0; display:block;"
				>
				</iframe>
			</xsl:when>
			<xsl:otherwise>
				<div class="alert alert-warning">Keine URL im Inhalt gefunden.</div>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
EOD;

$xslt_xhtml_c4= <<<EOD
<xsl:stylesheet version="1.0"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" encoding="UTF-8"/>
	<xsl:template match="content">
		<xsl:choose>
			<xsl:when test="string(url)">
				<iframe
					src="{url}"
					frameborder="0"
					style="width:100%; height:90vh; border:0; display:block;"
				>
				</iframe>
			</xsl:when>
			<xsl:otherwise>
				<div class="alert alert-warning">Keine URL im Inhalt gefunden.</div>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
EOD;


if ($result = @$db->db_query("SELECT * FROM campus.tbl_template WHERE template_kurzbz='iframe2'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$sql=  <<<EOD
	INSERT INTO campus.tbl_template 
	(template_kurzbz, bezeichnung, xsd, xslt_xhtml, xslfo_pdf, xslt_xhtml_c4) 
	VALUES 
	('iframe','iFrame Content ', '{$xsd}', '{$xslt_xhtml}' , NULL, '{$xslt_xhtml_c4}');
EOD;

		if (!$db->db_query($sql))
		{
			echo '<strong>campus.tbl_template: ' . $db->db_last_error() . '</strong><br>';
		}
		else
		{
			echo ' campus.tbl_template: Template "iframe" hinzugefügt.<br>';
		}

	}
}