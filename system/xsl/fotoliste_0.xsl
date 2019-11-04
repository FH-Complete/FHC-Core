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
	<xsl:template match="fotoliste">

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
				<style:table-properties style:width="25.7cm" table:align="margins" style:shadow="none" fo:keep-with-next="auto" style:may-break-between-rows="true"/>
			</style:style>
			<style:style style:name="Tabelle1.1" style:family="table-row">
				<style:table-row-properties fo:keep-together="always"/>
			</style:style>
			<style:style style:name="Tabelle1.A" style:family="table-column">
				<style:table-column-properties style:column-width="4.283cm" style:rel-column-width="10922*"/>
			</style:style>
			<style:style style:name="Tabelle1.A1" style:family="table-cell">
				<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #b2b2b2" fo:border-right="0.05pt solid #b2b2b2" fo:border-top="0.05pt solid #b2b2b2" fo:border-bottom="0.05pt solid #b2b2b2"/>
			</style:style>
			<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
				<style:text-properties style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
			</style:style>
			<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
				<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="00095c8f" officeooo:paragraph-rsid="00095c8f" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
			</style:style>
			<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
				<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="00095c8f" officeooo:paragraph-rsid="000a1f77" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
			</style:style>
			<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
				<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:paragraph-rsid="000a1f77" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
			</style:style>
			<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
				<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="0007e187" officeooo:paragraph-rsid="000a1f77" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
			</style:style>
			<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
				<style:text-properties style:font-name="Arial" fo:font-size="14pt" style:font-size-asian="14pt" style:font-size-complex="14pt"/>
			</style:style>
			<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Table_20_Contents">
				<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
				<style:text-properties style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
			</style:style>
			<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Table_20_Contents">
				<style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-align="center" style:justify-single-word="false" style:writing-mode="page"/>
				<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="0007e187" officeooo:paragraph-rsid="0007e187" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
			</style:style>
			<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Footer">
				<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
				<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="0007e187" officeooo:paragraph-rsid="000a1f77" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
			</style:style>
			<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Footer">
				<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
				<style:text-properties style:font-name="Arial" officeooo:paragraph-rsid="000a1f77"/>
			</style:style>
			<style:style style:name="T1" style:family="text">
				<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
			</style:style>
			<style:style style:name="T2" style:family="text">
				<style:text-properties officeooo:rsid="00095c8f"/>
			</style:style>
			<style:style style:name="T3" style:family="text">
				<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
			</style:style>
			<style:style style:name="T4" style:family="text">
				<style:text-properties fo:font-size="10pt" officeooo:rsid="0007e187" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
			</style:style>
			<style:style style:name="T5" style:family="text">
				<style:text-properties fo:font-size="10pt" officeooo:rsid="00095c8f" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
			</style:style>
			<style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
				<style:graphic-properties style:vertical-pos="from-top" style:vertical-rel="page" style:horizontal-pos="from-left" style:horizontal-rel="page" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
			</style:style>
			<style:style style:name="fr2" style:family="graphic" style:parent-style-name="Graphics">
				<style:graphic-properties style:vertical-pos="top" style:vertical-rel="baseline" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
			</style:style>
		</office:automatic-styles>
		<office:body>
			<office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">
				<office:forms form:automatic-focus="false" form:apply-design-mode="false"/>
				<text:sequence-decls>
					<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
					<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
					<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
					<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
				</text:sequence-decls>

				<text:p text:style-name="P6">Fotoliste <xsl:value-of select="lehrveranstaltung" /></text:p>
				<text:p text:style-name="P1"/>
				<text:p text:style-name="P1">Studiengang: <xsl:value-of select="studiengang" /> - <xsl:value-of select="studiengangs_typ" /></text:p>
				<text:p text:style-name="P1">Studiensemester: <xsl:value-of select="studiensemester" /></text:p>
				<text:p text:style-name="P1">Gruppe: <xsl:value-of select="studiengruppe" /></text:p>
				<text:p text:style-name="P1">Anzahl Studierende: <xsl:value-of select="anzahl_studierende" /></text:p>
				<text:p text:style-name="P1"/>
				<table:table table:name="Tabelle1" table:style-name="Tabelle1">
					<table:table-column table:style-name="Tabelle1.A" table:number-columns-repeated="6"/>

					<xsl:call-template name="row" />

				</table:table>
			</office:text>
		</office:body>
		</office:document-content>
	</xsl:template>

	<xsl:template name="row">
		<xsl:param select="ceiling(count(//studierende) div 6)" name="anzahl_zeilen"/>
		<xsl:param name="index" select="0" />
		<xsl:param name="total" select="$anzahl_zeilen" />
		<xsl:param name="position" select="1" />

			<xsl:if test="not($index = $total)">
				<table:table-row table:style-name="Tabelle1.1">
					<xsl:apply-templates select="studierende[$position]" />
					<xsl:apply-templates select="studierende[$position+1]" />
					<xsl:apply-templates select="studierende[$position+2]" />
					<xsl:apply-templates select="studierende[$position+3]" />
					<xsl:apply-templates select="studierende[$position+4]" />
					<xsl:apply-templates select="studierende[$position+5]" />
				</table:table-row>
				<xsl:call-template name="row">
					<xsl:with-param name="index" select="$index + 1" />
					<xsl:with-param name="position" select="$position + 6" />
				</xsl:call-template>
			</xsl:if>
	</xsl:template>

	<xsl:template match="studierende">
		<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
			<text:p text:style-name="P7">
				<xsl:choose>
					<xsl:when test="foto_gesperrt='f' and foto_url != ''">
						<draw:frame draw:style-name="fr2" draw:name="Bild" text:anchor-type="as-char" svg:width="2.39cm" svg:height="3.20cm" draw:z-index="1">
							<draw:image xlink:href="{foto_url}" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
						</draw:frame>
					</xsl:when>
					<xsl:when test="foto_gesperrt='t'">
						<draw:frame draw:style-name="fr2" draw:name="Bild2" text:anchor-type="as-char" svg:width="2.39cm" svg:height="3.20cm" draw:z-index="0">
							<draw:image xlink:href="Pictures/dummyfoto_bildVonUserGesperrt.jpg" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
						</draw:frame>
					</xsl:when>
					<xsl:when test="foto_url=''">
						<draw:frame draw:style-name="fr2" draw:name="dummy" text:anchor-type="as-char" svg:width="2.39cm" svg:height="3.20cm" draw:z-index="0">
							<draw:image xlink:href="Pictures/dummyfoto_keinBildVorhanden.jpg" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
						</draw:frame>
					</xsl:when>
				</xsl:choose>
			</text:p>
			<text:p text:style-name="P8">
				<text:span text:style-name="T1"><xsl:value-of select="nachname" /></text:span>
				<xsl:text> </xsl:text>
				<xsl:value-of select="vorname" />
				<xsl:text> </xsl:text>
				(<xsl:value-of select="geschlecht" />)
				<text:span text:style-name="T2"><xsl:value-of select="zusatz" /></text:span>
			</text:p>
		</table:table-cell>
	</xsl:template>
</xsl:stylesheet>