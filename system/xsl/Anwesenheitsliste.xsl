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
<xsl:template match="anwesenheitsliste">

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
			<style:table-properties style:width="18.002cm" table:align="margins" style:shadow="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A" style:family="table-column">
			<style:table-column-properties style:column-width="0.706cm" style:rel-column-width="400*"/>
		</style:style>
		<style:style style:name="Tabelle1.B" style:family="table-column">
			<style:table-column-properties style:column-width="4.882cm" style:rel-column-width="2767*"/>
		</style:style>
		<style:style style:name="Tabelle1.C" style:family="table-column">
			<style:table-column-properties style:column-width="1.981cm" style:rel-column-width="1123*"/>
		</style:style>
		<style:style style:name="Tabelle1.D" style:family="table-column">
			<style:table-column-properties style:column-width="1.3cm" style:rel-column-width="737*"/>
		</style:style>
		<style:style style:name="Tabelle1.E" style:family="table-column">
			<style:table-column-properties style:column-width="1.826cm" style:rel-column-width="1035*"/>
		</style:style>
		<style:style style:name="Tabelle1.1" style:family="table-row">
			<style:table-row-properties style:row-height="0.6cm"/>
		</style:style>
		<style:style style:name="Tabelle1.A1" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.I1" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.A2" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.I2" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.A3" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="middle" fo:background-color="#cccccc" fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.A8" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="middle" fo:background-color="#cccccc" fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Footer">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="000e4736" officeooo:paragraph-rsid="000e4736" style:font-size-asian="8.75pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="14pt" officeooo:rsid="000bf7c5" officeooo:paragraph-rsid="000bf7c5" style:font-size-asian="12.25pt" style:font-size-complex="14pt"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="000bf7c5" officeooo:paragraph-rsid="000bf7c5" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="000bf7c5" officeooo:paragraph-rsid="000bf7c5" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="000bf7c5" officeooo:paragraph-rsid="000bf7c5" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="0014a385" officeooo:paragraph-rsid="0014a385" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="0014a385" officeooo:paragraph-rsid="0014a385" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="0014a385" officeooo:paragraph-rsid="0014a385" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="0014a385" officeooo:paragraph-rsid="0014a385" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="0014a385" officeooo:paragraph-rsid="0014a385" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="0015a8b4" officeooo:paragraph-rsid="0015a8b4" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T1" style:family="text">
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T2" style:family="text">
			<style:text-properties officeooo:rsid="0015a8b4"/>
		</style:style>
		<style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
			<style:graphic-properties fo:margin-left="0.499cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0.499cm" style:wrap="left" style:number-wrapped-paragraphs="no-limit" style:wrap-contour="false" style:vertical-pos="from-top" style:vertical-rel="page" style:horizontal-pos="from-left" style:horizontal-rel="page" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
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
			<draw:frame draw:style-name="fr1" draw:name="Bild1" text:anchor-type="page" text:anchor-page-number="1" svg:x="15.649cm" svg:y="0.9cm" svg:width="4.23cm" svg:height="2.17cm" draw:z-index="0">
				<draw:image xlink:href="Pictures/10000201000000FD0000008209020D9B.png" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
			</draw:frame>
			<text:p text:style-name="P2">Anwesenheitsliste <xsl:value-of select="bezeichnung" /></text:p>
			<text:p text:style-name="P3">Gruppen: <xsl:value-of select="gruppen" /> Studiensemester: <xsl:value-of select="studiensemester" /></text:p>
			<text:p text:style-name="P2"/>
			<text:p text:style-name="P2">Monat ___________</text:p>
			<text:p text:style-name="P2"/>
			<table:table table:name="Tabelle1" table:style-name="Tabelle1">
				<table:table-column table:style-name="Tabelle1.A"/>
				<table:table-column table:style-name="Tabelle1.B"/>
				<table:table-column table:style-name="Tabelle1.C"/>
				<table:table-column table:style-name="Tabelle1.D"/>
				<table:table-column table:style-name="Tabelle1.E" table:number-columns-repeated="5"/>
				<table:table-row table:style-name="Tabelle1.1">
					<table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="4" office:value-type="string">
						<text:p text:style-name="P7">Datum</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.I1" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row table:style-name="Tabelle1.1">
					<table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="4" office:value-type="string">
						<text:p text:style-name="P7">Anzahl der abgehaltenen Einheiten</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.I2" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row table:style-name="Tabelle1.1">
					<table:table-cell table:style-name="Tabelle1.A3" table:number-columns-spanned="9" office:value-type="string">
						<text:p text:style-name="P13">LektorInnen</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
				</table:table-row>

				<xsl:apply-templates select="lehrende"/>

				<table:table-row table:style-name="Tabelle1.1">
					<table:table-cell table:style-name="Tabelle1.A3" table:number-columns-spanned="9" office:value-type="string">
						<text:p text:style-name="P13"><xsl:value-of select="anzahl_studierende" /> Studierende</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row table:style-name="Tabelle1.1">
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P12"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P10">Name</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P11">Kennzeichen</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P11">Gruppe</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.I2" office:value-type="string">
						<text:p text:style-name="P6"/>
					</table:table-cell>
				</table:table-row>

				<xsl:apply-templates select="student"/>

			</table:table>
			<text:p text:style-name="P4"/>
			<text:p text:style-name="P4"/>
			<text:p text:style-name="P4">(m) ... männlich</text:p>
			<text:p text:style-name="P4">(w) ... weiblich</text:p>
			<text:p text:style-name="P4">(x) ... divers</text:p>
            <text:p text:style-name="P4">(u) ... unbekannt</text:p>
			<text:p text:style-name="P4"/>
			<text:p text:style-name="P4"/>
			<text:p text:style-name="P4">(i) ... Incoming</text:p>
			<text:p text:style-name="P4">(o) ... Outgoing</text:p>
			<text:p text:style-name="P4">(ar) ... angerechnet</text:p>
            <text:p text:style-name="P4">(iar) ... intern angerechnet</text:p>
			<text:p text:style-name="P4">(nz) ... nicht zugelassen</text:p>
			<text:p text:style-name="P4">(ma) ... MitarbeiterIn</text:p>
			<text:p text:style-name="P4">(a.o.) ... Außerordentliche/r HörerIn</text:p>
			<text:p text:style-name="P4">(d.d.) ... Double Degree Program</text:p>
			<text:p text:style-name="P4"/>
			<text:p text:style-name="P5">
				<xsl:choose>
					<xsl:when test="studiengang_kz=0">
						Freifach <xsl:value-of select="typ" /><xsl:text> </xsl:text><xsl:value-of select="studiengang" />
					</xsl:when>
					<xsl:otherwise>
						Fachhochschulstudiengang <xsl:value-of select="typ" /><xsl:text> </xsl:text><xsl:value-of select="studiengang" />
					</xsl:otherwise>
				</xsl:choose>
			</text:p>
			<text:p text:style-name="P4">Fehlt ein/e Student/in länger als 2 Wochen, bitte um einen deutlichen Vermerk auf der Anwesenheitsliste. Die Anwesenheitsliste bitte am Ende des Monats im Sekretariat abgeben! Bitte achten Sie darauf, dass Sie nur VOLLSTÄNDIG AUSGEFÜLLTE LISTEN abgeben!</text:p>
		</office:text>
	</office:body>
</office:document-content>
</xsl:template>
<xsl:template match="lehrende">
	<table:table-row table:style-name="Tabelle1.1">
		<table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="4" office:value-type="string">
			<text:p text:style-name="P7"><xsl:value-of select="name" /></text:p>
		</table:table-cell>
		<table:covered-table-cell/>
		<table:covered-table-cell/>
		<table:covered-table-cell/>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P6"/>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P6"/>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P6"/>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P6"/>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.I2" office:value-type="string">
			<text:p text:style-name="P6"/>
		</table:table-cell>
	</table:table-row>
</xsl:template>
<xsl:template match="student">
	<xsl:variable select="position()" name="number"/>
	<xsl:choose>
		<xsl:when test="$number mod 2 != 0">
			<table:table-row table:style-name="Tabelle1.1">
				<table:table-cell table:style-name="Tabelle1.A8" office:value-type="string">
					<text:p text:style-name="P9"><xsl:value-of select="position()" /></text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A8" office:value-type="string">
					<text:p text:style-name="P7">
						<text:span text:style-name="T1">
						<xsl:value-of select="nachname" /></text:span>
						<xsl:text> </xsl:text>
						<xsl:value-of select="vorname" />
						<xsl:text> </xsl:text>

							<xsl:choose>
							<xsl:when test="geschlecht!=''">
								(<xsl:value-of select="geschlecht" />)
							</xsl:when>
							<xsl:otherwise>

							</xsl:otherwise>
							</xsl:choose>

						<xsl:value-of select="zusatz" /></text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A8" office:value-type="string">
					<text:p text:style-name="P8">
						<xsl:choose>
							<xsl:when test="personenkennzeichen!=''">
								<xsl:value-of select="personenkennzeichen" />
							</xsl:when>
							<xsl:otherwise>
								-
							</xsl:otherwise>
						</xsl:choose>
					</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A8" office:value-type="string">
					<text:p text:style-name="P8">
						<xsl:choose>
							<xsl:when test="semester!=''">
								<xsl:value-of select="semester" /><xsl:value-of select="verband" /><xsl:value-of select="gruppe" />
							</xsl:when>
							<xsl:otherwise>
								-
							</xsl:otherwise>
						</xsl:choose>
					</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A8" office:value-type="string">
					<text:p text:style-name="P6"/>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A8" office:value-type="string">
					<text:p text:style-name="P6"/>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A8" office:value-type="string">
					<text:p text:style-name="P6"/>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A8" office:value-type="string">
					<text:p text:style-name="P6"/>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A3" office:value-type="string">
					<text:p text:style-name="P6"/>
				</table:table-cell>
			</table:table-row>
		</xsl:when>
		<xsl:otherwise>
			<table:table-row table:style-name="Tabelle1.1">
				<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
					<text:p text:style-name="P9"><xsl:value-of select="position()" /></text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
					<text:p text:style-name="P7">
						<text:span text:style-name="T1">
							<xsl:value-of select="nachname" /></text:span>
								<xsl:text> </xsl:text>
							<xsl:value-of select="vorname" />
								<xsl:text> </xsl:text>

							<xsl:choose>
							<xsl:when test="geschlecht!=''">
								(<xsl:value-of select="geschlecht" />)
							</xsl:when>
							<xsl:otherwise>

							</xsl:otherwise>
							</xsl:choose>

							<xsl:value-of select="zusatz" /></text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
					<text:p text:style-name="P8"><xsl:value-of select="personenkennzeichen" /></text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
					<text:p text:style-name="P8"><xsl:value-of select="semester" /><xsl:value-of select="verband" /><xsl:value-of select="gruppe" /></text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
					<text:p text:style-name="P6"/>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
					<text:p text:style-name="P6"/>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
					<text:p text:style-name="P6"/>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
					<text:p text:style-name="P6"/>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.I2" office:value-type="string">
					<text:p text:style-name="P6"/>
				</table:table-cell>
			</table:table-row>
		</xsl:otherwise>
	  </xsl:choose>
</xsl:template>
</xsl:stylesheet>
