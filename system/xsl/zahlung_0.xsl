<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
xmlns:xlink="http://www.w3.org/1999/xlink"
xmlns:dc="http://purl.org/dc/elements/1.1/"
xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
>

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="konto">

<office:document-content
	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
	xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
	xmlns:xlink="http://www.w3.org/1999/xlink"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
	xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
	xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
	xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
	xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0"
	xmlns:math="http://www.w3.org/1998/Math/MathML"
	xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
	xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"
	xmlns:ooo="http://openoffice.org/2004/office"
	xmlns:ooow="http://openoffice.org/2004/writer"
	xmlns:oooc="http://openoffice.org/2004/calc"
	xmlns:dom="http://www.w3.org/2001/xml-events"
	xmlns:xforms="http://www.w3.org/2002/xforms"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:rpt="http://openoffice.org/2005/report"
	xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns:grddl="http://www.w3.org/2003/g/data-view#"
	xmlns:officeooo="http://openoffice.org/2009/office"
	xmlns:tableooo="http://openoffice.org/2009/table"
	xmlns:drawooo="http://openoffice.org/2010/draw"
	xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0"
	xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0"
	xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0"
	xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0"
	xmlns:css3t="http://www.w3.org/TR/css3-text/"
	office:version="1.2">
	<office:scripts/>
	<office:font-face-decls>
		<style:font-face style:name="Mangal1" svg:font-family="Mangal"/>
		<style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Microsoft YaHei" svg:font-family="&apos;Microsoft YaHei&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="SimSun" svg:font-family="SimSun" style:font-family-generic="system" style:font-pitch="variable"/>
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="Tabelle1" style:family="table">
			<style:table-properties style:width="16.002cm" table:align="margins"/>
		</style:style>
		<style:style style:name="Tabelle1.A" style:family="table-column">
			<style:table-column-properties style:column-width="1.896cm" style:rel-column-width="1075*"/>
		</style:style>
		<style:style style:name="Tabelle1.B" style:family="table-column">
			<style:table-column-properties style:column-width="1.658cm" style:rel-column-width="940*"/>
		</style:style>
		<style:style style:name="Tabelle1.C" style:family="table-column">
			<style:table-column-properties style:column-width="3.556cm" style:rel-column-width="2016*"/>
		</style:style>
		<style:style style:name="Tabelle1.D" style:family="table-column">
			<style:table-column-properties style:column-width="6.495cm" style:rel-column-width="3682*"/>
		</style:style>
		<style:style style:name="Tabelle1.E" style:family="table-column">
			<style:table-column-properties style:column-width="2.397cm" style:rel-column-width="1359*"/>
		</style:style>
		<style:style style:name="Tabelle1.A1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.E1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.A2" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.E2" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="9pt" fo:font-weight="bold" officeooo:rsid="00086ab6" officeooo:paragraph-rsid="00086ab6" style:font-size-asian="9pt" style:font-weight-asian="bold" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="9pt" officeooo:rsid="00086ab6" officeooo:paragraph-rsid="00086ab6" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="9pt" officeooo:rsid="00086ab6" officeooo:paragraph-rsid="00086ab6" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="9pt" officeooo:rsid="0009b8b2" officeooo:paragraph-rsid="0009b8b2" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="11pt" officeooo:rsid="000b7da5" officeooo:paragraph-rsid="000b7da5" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="11pt" officeooo:rsid="000b7da5" officeooo:paragraph-rsid="00086ab6" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="100%" fo:text-align="end" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="11pt" officeooo:rsid="000b7da5" officeooo:paragraph-rsid="000c04a4" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="11pt" officeooo:rsid="00086ab6" officeooo:paragraph-rsid="00086ab6" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="12pt" officeooo:rsid="00086ab6" officeooo:paragraph-rsid="00086ab6" style:font-size-asian="12pt" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="9pt" officeooo:rsid="00086ab6" officeooo:paragraph-rsid="00086ab6" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard" style:master-page-name="">
			<loext:graphic-properties draw:fill="none"/>
			<style:paragraph-properties fo:margin-left="11cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false" style:page-number="auto"/>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
			<loext:graphic-properties draw:fill="none"/>
			<style:paragraph-properties fo:margin-left="11.6cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:font-name="Arial" fo:color="#999999" fo:font-size="8pt" officeooo:rsid="000b7da5" officeooo:paragraph-rsid="000b7da5" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Header">
			<style:text-properties officeooo:rsid="000b7da5" officeooo:paragraph-rsid="000b7da5"/>
		</style:style>
		<style:style style:name="T1" style:family="text">
			<style:text-properties officeooo:rsid="0009b8b2"/>
		</style:style>
		<style:style style:name="fr1" style:family="graphic" style:parent-style-name="Frame">
			<style:graphic-properties style:vertical-pos="bottom" style:vertical-rel="page-content" style:horizontal-pos="right" style:horizontal-rel="page-content" fo:padding="0cm" fo:border="none" style:shadow="none" draw:shadow-opacity="100%"/>
		</style:style>
		<style:style style:name="fr2" style:family="graphic" style:parent-style-name="Graphics">
			<style:graphic-properties fo:margin-left="0.318cm" fo:margin-right="0.318cm" fo:margin-top="0cm" fo:margin-bottom="113.189cm" style:run-through="foreground" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit" style:wrap-contour="true" style:wrap-contour-mode="outside" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" draw:fill="none" draw:fill-color="#ffffff" fo:padding="0cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard" style:flow-with-text="true"/>
		</style:style>
		<style:style style:name="fr3" style:family="graphic" style:parent-style-name="Frame">
			<style:graphic-properties style:vertical-pos="bottom" style:vertical-rel="page-content" style:horizontal-pos="right" style:horizontal-rel="page-content" fo:padding="0.101cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.06pt solid #000000" fo:border-bottom="none" style:shadow="none" draw:shadow-opacity="100%"/>
		</style:style>
	</office:automatic-styles>
	<office:body>
		<office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
			</text:sequence-decls>
			<text:p text:style-name="P11"/>
			<text:p text:style-name="P12"/>
			<text:p text:style-name="P12">Datum: <xsl:value-of select="person/tagesdatum" /></text:p>
			<text:p text:style-name="P12"/>
			<text:p text:style-name="P12"/>
			<text:p text:style-name="P12"/>
			<text:p text:style-name="P9">
				<xsl:choose>
					<xsl:when test="buchung/rueckerstattung">
						Auszahlungsbestätigung
					</xsl:when>
					<xsl:otherwise>
						Zahlungsbestätigung
					</xsl:otherwise>
				</xsl:choose>
			</text:p>
			<text:p text:style-name="P8"/>
			<text:p text:style-name="P8"/>

			<xsl:if test="not(buchung/rueckerstattung)">
				<text:p text:style-name="P5"/>
			</xsl:if>

			<text:p text:style-name="P5">
			<xsl:choose>
				<xsl:when test="person/geschlecht='m'">
					<xsl:text>Herr </xsl:text>
				</xsl:when>
				<xsl:when test="person/geschlecht='w'">
					<xsl:text>Frau </xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>Herr/Frau </xsl:text>
				</xsl:otherwise>
			</xsl:choose>
				<xsl:value-of select="person/name_titel" />
			</text:p>
			<text:p text:style-name="P5">geboren am <xsl:value-of select="person/geburtsdatum" /></text:p>
			<text:p text:style-name="P5">Personenkennzeichen <xsl:value-of select="person/matrikelnr" /></text:p>
			<text:p text:style-name="P5">
			<xsl:choose>
				<xsl:when test="person/sozialversicherungsnummer!=''">
					Sozialversicherungsnummer <xsl:value-of select="person/sozialversicherungsnummer" />
				</xsl:when>
				<xsl:when test="person/ersatzkennzeichen!=''">
					Ersatzkennzeichen <xsl:value-of select="person/ersatzkennzeichen" />
				</xsl:when>
				<xsl:otherwise>
					Keine Sozialversicherungsnummer oder Ersatzkennzeichen vorhanden
				</xsl:otherwise>
			</xsl:choose>
			</text:p>
			<text:p text:style-name="P5"/>
			<text:p text:style-name="P5">
			<xsl:choose>
				<xsl:when test="not(buchung/rueckerstattung)">
					folgende Einzahlungen getätigt hat:
				</xsl:when>
				<xsl:otherwise>
					bestätigt hiermit, dass die Studiengangsleitung folgende Auszahlungen getätigt hat:
				</xsl:otherwise>
			</xsl:choose>
			</text:p>
			<text:p text:style-name="P8"/>
			<table:table table:name="Tabelle1" table:style-name="Tabelle1">
				<table:table-column table:style-name="Tabelle1.A"/>
				<table:table-column table:style-name="Tabelle1.B"/>
				<table:table-column table:style-name="Tabelle1.C"/>
				<table:table-column table:style-name="Tabelle1.D"/>
				<table:table-column table:style-name="Tabelle1.E"/>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P1">Datum</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P1">Nummer</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P1">Buchungstyp</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P1">Buchungstext</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.E1" office:value-type="string">
						<text:p text:style-name="P1">Betrag in EUR</text:p>
					</table:table-cell>
				</table:table-row>

				<xsl:apply-templates select="buchung"/>

			</table:table>
			<text:p text:style-name="P8"/>
			<text:p text:style-name="P6">
			<xsl:choose>
				<xsl:when test="not(buchung/rueckerstattung)">
					<draw:frame draw:style-name="fr1" draw:name="Rahmen1" text:anchor-type="char" draw:z-index="1">
						<draw:text-box fo:min-height="1cm" fo:min-width="2cm">
						</draw:text-box>
					</draw:frame>
				</xsl:when>
				<xsl:otherwise>
					<draw:frame draw:style-name="fr3" draw:name="Rahmen1" text:anchor-type="char" draw:z-index="1">
						<draw:text-box fo:min-height="1cm" fo:min-width="2cm">
							<text:p text:style-name="P7"><text:s text:c="11"/>Unterschrift <xsl:value-of select="person/name_titel" /></text:p>
						</draw:text-box>
					</draw:frame>
				</xsl:otherwise>
			</xsl:choose>

			</text:p>
		</office:text>
	</office:body>
</office:document-content>
</xsl:template>
<xsl:template match="buchung">
	<table:table-row>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P2"><xsl:value-of select="buchungsdatum" /></text:p>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P2"><xsl:value-of select="buchungsnr" /></text:p>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P4"><xsl:value-of select="buchungstyp_beschreibung" /></text:p>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P10"><xsl:value-of select="buchungstext" /></text:p>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.E2" office:value-type="string">
			<text:p text:style-name="P3"><xsl:value-of select="betrag" /></text:p>
		</table:table-cell>
	</table:table-row>
</xsl:template>
</xsl:stylesheet>