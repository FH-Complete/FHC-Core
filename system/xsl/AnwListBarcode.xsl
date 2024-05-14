<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
	xmlns:fo="http://www.w3.org/1999/XSL/Format" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" 
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" 
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" 
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>
<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="anwesenheitslisten">

<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
	<office:scripts/>
	<office:font-face-decls>
		<style:font-face style:name="UPCEANm" svg:font-family="UPCEANm" style:font-pitch="variable" style:font-charset="x-symbol"/>
		<style:font-face style:name="Mangal1" svg:font-family="Mangal"/>
		<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="roman"/>
		<style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Microsoft YaHei" svg:font-family="&apos;Microsoft YaHei&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="SimSun" svg:font-family="SimSun" style:font-family-generic="system" style:font-pitch="variable"/>
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="Barcode" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="UPCEANm" officeooo:rsid="000a39ed" officeooo:paragraph-rsid="000a39ed"/>
		</style:style>
		<style:style style:name="Tabelle1" style:family="table">
			<style:table-properties style:width="17cm" table:align="margins" style:shadow="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A" style:family="table-column">
			<style:table-column-properties style:column-width="4cm" />
		</style:style>
		<style:style style:name="Tabelle1.B" style:family="table-column">
			<style:table-column-properties style:column-width="13cm" />
		</style:style>
		<style:style style:name="Tabelle1.A1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="1pt dashed #000000"/>
		</style:style>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:font-size="18pt" officeooo:rsid="001ef933" officeooo:paragraph-rsid="001ef933" style:font-size-asian="18pt" style:font-size-complex="18pt"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:font-size="18pt" officeooo:rsid="001ef933" officeooo:paragraph-rsid="001ef933" style:font-size-asian="15.75pt" style:font-size-complex="18pt"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:font-size="18pt" officeooo:rsid="001ef933" officeooo:paragraph-rsid="002bdaad" style:font-size-asian="15.75pt" style:font-size-complex="18pt"/>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:font-size="14pt" officeooo:rsid="00202b32" officeooo:paragraph-rsid="00202b32" style:font-size-asian="12.25pt" style:font-size-complex="14pt"/>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:font-size="12pt" officeooo:rsid="001ef933" officeooo:paragraph-rsid="001ef933" style:font-size-asian="12pt" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:padding="0.074cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.06pt solid #000000" style:join-border="false"/>
			<style:text-properties fo:font-size="14pt" officeooo:rsid="00202b32" officeooo:paragraph-rsid="00202b32" style:font-size-asian="12.25pt" style:font-size-complex="14pt"/>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false" fo:padding="0.074cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.06pt solid #000000" style:join-border="false"/>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties fo:font-size="14pt" officeooo:rsid="00262b16" officeooo:paragraph-rsid="00262b16" style:font-size-asian="12.25pt" style:font-size-complex="14pt"/>
		</style:style>
		<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties fo:font-size="10pt" officeooo:rsid="00262b16" officeooo:paragraph-rsid="00296a84" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties fo:font-size="11pt" officeooo:rsid="00262b16" officeooo:paragraph-rsid="00262b16" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties fo:font-size="11pt" officeooo:rsid="00262b16" officeooo:paragraph-rsid="00296a84" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
			<style:text-properties officeooo:rsid="0009ddca" officeooo:paragraph-rsid="0009ddca"/>
		</style:style>
		<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="start" style:justify-single-word="false" fo:break-after="page"/>
			<style:text-properties officeooo:rsid="000a65d0" officeooo:paragraph-rsid="000a65d0"/>
		</style:style>
		<style:style style:name="T1" style:family="text">
			<style:text-properties fo:font-size="12pt" style:font-size-asian="12pt" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="T2" style:family="text">
			<style:text-properties fo:font-size="12pt" officeooo:rsid="00247dbd" style:font-size-asian="12pt" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="T3" style:family="text">
			<style:text-properties fo:font-size="12pt" officeooo:rsid="0021913e" style:font-size-asian="12pt" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="T4" style:family="text">
			<style:text-properties officeooo:rsid="0021913e"/>
		</style:style>
		<style:style style:name="T5" style:family="text">
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="T6" style:family="text">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt"/>
		</style:style>
		<style:style style:name="T7" style:family="text">
			<style:text-properties officeooo:rsid="0027e935"/>
		</style:style>
	</office:automatic-styles>
	<office:body>
		<office:text>
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
			</text:sequence-decls>
			<xsl:apply-templates select="anwesenheitsliste"/>
		</office:text>
	</office:body>
</office:document-content>
</xsl:template>

<xsl:template match="anwesenheitsliste">			
			<text:p text:style-name="P1">Anwesenheitsliste</text:p>
			<text:p text:style-name="P5">
				<text:span text:style-name="T4"><xsl:value-of select="lehreinheit/datum" /></text:span><text:s text:c="2"/>
				<text:span text:style-name="T4"><xsl:value-of select="lehreinheit/beginn" /> - <xsl:value-of select="lehreinheit/ende" /></text:span><text:s text:c="2"/>
				<text:span text:style-name="T4"><xsl:value-of select="lehreinheit/ort" /></text:span>
			</text:p>
			<text:p text:style-name="P5">
				<text:span text:style-name="T7"><xsl:value-of select="lehreinheit/studiengang" /></text:span>
			</text:p>
			<text:p text:style-name="P5">
				<text:span text:style-name="T4"><xsl:value-of select="lehreinheit/kuerzel" /></text:span><text:tab/>
				<text:span text:style-name="T4"><xsl:value-of select="lehreinheit/einheiten" /> LE</text:span>
			</text:p>
			<text:p text:style-name="P3" />
			<text:p text:style-name="P3">
				<text:span text:style-name="T7"><xsl:value-of select="lehreinheit/bezeichnung" /></text:span>
			</text:p>
			<text:p text:style-name="Barcode"><xsl:value-of select="lehreinheit/barcode" /></text:p>
			<text:p text:style-name="P13">
				<text:span text:style-name="T2">
					<text:s text:c="5"/>_____________________________</text:span>
			</text:p>
			<text:p text:style-name="P13">
				<text:span text:style-name="T2">
					<xsl:value-of select="vortragende/vortragender/titelpre" />
					<xsl:text> </xsl:text>
					<xsl:value-of select="vortragende/vortragender/vorname" />
					<xsl:text> </xsl:text>
					<xsl:value-of select="vortragende/vortragender/nachname" />
					<xsl:if test="vortragende/vortragender/titelpost != ''">,<xsl:text> </xsl:text><xsl:value-of select="vortragende/vortragender/titelpost" /></xsl:if>
				</text:span>
			</text:p>
			<text:p text:style-name="P2"/>
			<text:p text:style-name="P4">Lehrinhalte</text:p>
			<text:p text:style-name="P7"/>
			<text:p text:style-name="P7"/>
			<text:p text:style-name="P4"/>
			<text:p text:style-name="P4"/>
			<table:table table:name="Tabelle1" table:style-name="Tabelle1">
				<table:table-column table:style-name="Tabelle1.A" />
				<table:table-column table:style-name="Tabelle1.B" />
				<xsl:apply-templates select="studenten"/>				
			</table:table>
			<text:p text:style-name="P14"></text:p>
</xsl:template>

<xsl:template match="studenten">	
	<xsl:apply-templates select="student"/>
</xsl:template>

<xsl:template match="student">	
	<table:table-row>
		<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
			<text:p text:style-name="Barcode"><xsl:value-of select="barcode" /></text:p>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
			<text:p text:style-name="P10">
				<xsl:value-of select="titelpre" />
				<xsl:text> </xsl:text>
				<xsl:value-of select="vorname" />
				<xsl:text> </xsl:text>
				<xsl:value-of select="nachname" />
				<xsl:if test="titelpost != ''">,<xsl:text> </xsl:text><xsl:value-of select="titelpost" /></xsl:if>
			</text:p>
			<text:p text:style-name="P10">
				<text:span text:style-name="T5">Status: <xsl:value-of select="status" /></text:span>
			</text:p>
			<text:p text:style-name="P10" />
		</table:table-cell>
	</table:table-row>
</xsl:template>

</xsl:stylesheet>