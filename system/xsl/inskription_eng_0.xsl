<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
				xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>

	<xsl:output method="xml" version="1.0" indent="yes"/>
	<xsl:template match="studenten">

		<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
			<office:scripts/>
			<office:font-face-decls>
				<style:font-face style:name="Mangal2" svg:font-family="Mangal"/>
				<style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="roman"/>
				<style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
				<style:font-face style:name="Arial" svg:font-family="Arial" style:font-adornments="Standard" style:font-family-generic="swiss" style:font-pitch="variable"/>
				<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
				<style:font-face style:name="Liberation Sans1" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
				<style:font-face style:name="Mangal1" svg:font-family="Mangal" style:font-family-generic="system" style:font-pitch="variable"/>
				<style:font-face style:name="Microsoft YaHei" svg:font-family="&apos;Microsoft YaHei&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
				<style:font-face style:name="SimSun" svg:font-family="SimSun" style:font-family-generic="system" style:font-pitch="variable"/>
				<style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="system" style:font-pitch="variable"/>
			</office:font-face-decls>
			<office:automatic-styles>
				<style:style style:name="Tabelle1" style:family="table">
					<style:table-properties style:width="17.701cm" table:align="left"/>
				</style:style>
				<style:style style:name="Tabelle1.A" style:family="table-column">
					<style:table-column-properties style:column-width="12.991cm"/>
				</style:style>
				<style:style style:name="Tabelle1.B" style:family="table-column">
					<style:table-column-properties style:column-width="4.71cm"/>
				</style:style>
				<style:style style:name="Tabelle1.1" style:family="table-row">
					<style:table-row-properties style:min-row-height="1.100cm"/>
				</style:style>
				<style:style style:name="Tabelle1.2" style:family="table-row">
					<style:table-row-properties style:min-row-height="0.500cm"/>
				</style:style>
				<style:style style:name="Tabelle1.A1" style:family="table-cell">
					<style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000"/>
				</style:style>
				<style:style style:name="Tabelle1.B1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="bottom" fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle1.A2" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="middle" style:min-row-height="0.600cm" fo:padding="0.0cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle1.B2" style:family="table-cell">
					<style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000"/>
				</style:style>
				<style:style style:name="Tabelle2" style:family="table">
					<style:table-properties style:width="17.7cm" fo:margin-left="0cm" table:align="left"/>
				</style:style>
				<style:style style:name="Tabelle2.A" style:family="table-column">
					<style:table-column-properties style:column-width="8,850cm"/>
				</style:style>
				<style:style style:name="Tabelle2.B" style:family="table-column">
					<style:table-column-properties style:column-width="8,851cm"/>
				</style:style>
				<style:style style:name="Tabelle2.A1" style:family="table-cell">
					<style:table-cell-properties fo:padding="0.0cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle3" style:family="table">
					<style:table-properties style:width="17.701cm" table:align="left"/>
				</style:style>
				<style:style style:name="Tabelle3.A" style:family="table-column">
					<style:table-column-properties style:column-width="12.991cm"/>
				</style:style>
				<style:style style:name="Tabelle3.B" style:family="table-column">
					<style:table-column-properties style:column-width="4.71cm"/>
				</style:style>
				<style:style style:name="Tabelle3.1" style:family="table-row">
					<style:table-row-properties style:min-row-height="1.005cm"/>
				</style:style>
				<style:style style:name="Tabelle3.A1" style:family="table-cell">
					<style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000"/>
				</style:style>
				<style:style style:name="Tabelle3.B1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="bottom" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
				</style:style>
				<style:style style:name="Tabelle3.A2" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="middle" fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle4" style:family="table">
					<style:table-properties style:width="17.013cm" fo:margin-left="0cm" table:align="left"/>
				</style:style>
				<style:style style:name="Tabelle4.A" style:family="table-column">
					<style:table-column-properties style:column-width="7.911cm"/>
				</style:style>
				<style:style style:name="Tabelle4.B" style:family="table-column">
					<style:table-column-properties style:column-width="9.102cm"/>
				</style:style>
				<style:style style:name="Tabelle4.A1" style:family="table-cell">
					<style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle5" style:family="table">
					<style:table-properties style:width="17.701cm" table:align="left"/>
				</style:style>
				<style:style style:name="Tabelle5.A" style:family="table-column">
					<style:table-column-properties style:column-width="12.991cm"/>
				</style:style>
				<style:style style:name="Tabelle5.B" style:family="table-column">
					<style:table-column-properties style:column-width="4.71cm"/>
				</style:style>
				<style:style style:name="Tabelle5.1" style:family="table-row">
					<style:table-row-properties style:min-row-height="1.005cm"/>
				</style:style>
				<style:style style:name="Tabelle5.A1" style:family="table-cell">
					<style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000"/>
				</style:style>
				<style:style style:name="Tabelle5.B1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="bottom" fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle5.A2" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="middle" fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle6" style:family="table">
					<style:table-properties style:width="17.013cm" fo:margin-left="0cm" table:align="left"/>
				</style:style>
				<style:style style:name="Tabelle6.A" style:family="table-column">
					<style:table-column-properties style:column-width="7.911cm"/>
				</style:style>
				<style:style style:name="Tabelle6.B" style:family="table-column">
					<style:table-column-properties style:column-width="9.102cm"/>
				</style:style>
				<style:style style:name="Tabelle6.A1" style:family="table-cell">
					<style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle7" style:family="table">
					<style:table-properties style:width="17.701cm" table:align="left"/>
				</style:style>
				<style:style style:name="Tabelle7.A" style:family="table-column">
					<style:table-column-properties style:column-width="12.991cm"/>
				</style:style>
				<style:style style:name="Tabelle7.B" style:family="table-column">
					<style:table-column-properties style:column-width="4.71cm"/>
				</style:style>
				<style:style style:name="Tabelle7.1" style:family="table-row">
					<style:table-row-properties style:min-row-height="1.005cm"/>
				</style:style>
				<style:style style:name="Tabelle7.A1" style:family="table-cell">
					<style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000"/>
				</style:style>
				<style:style style:name="Tabelle7.B1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="bottom" fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle7.A2" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="middle" fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle8" style:family="table">
					<style:table-properties style:width="17.013cm" fo:margin-left="0cm" table:align="left"/>
				</style:style>
				<style:style style:name="Tabelle8.A" style:family="table-column">
					<style:table-column-properties style:column-width="7.911cm"/>
				</style:style>
				<style:style style:name="Tabelle8.B" style:family="table-column">
					<style:table-column-properties style:column-width="9.102cm"/>
				</style:style>
				<style:style style:name="Tabelle8.A1" style:family="table-cell">
					<style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle9" style:family="table">
					<style:table-properties style:width="17.701cm" table:align="left"/>
				</style:style>
				<style:style style:name="Tabelle9.A" style:family="table-column">
					<style:table-column-properties style:column-width="12.991cm"/>
				</style:style>
				<style:style style:name="Tabelle9.B" style:family="table-column">
					<style:table-column-properties style:column-width="4.71cm"/>
				</style:style>
				<style:style style:name="Tabelle9.1" style:family="table-row">
					<style:table-row-properties style:min-row-height="1.005cm"/>
				</style:style>
				<style:style style:name="Tabelle9.A1" style:family="table-cell">
					<style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000"/>
				</style:style>
				<style:style style:name="Tabelle9.B1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="bottom" fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle9.A2" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="middle" fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle10" style:family="table">
					<style:table-properties style:width="17.013cm" fo:margin-left="0cm" table:align="left"/>
				</style:style>
				<style:style style:name="Tabelle10.A" style:family="table-column">
					<style:table-column-properties style:column-width="7.911cm"/>
				</style:style>
				<style:style style:name="Tabelle10.B" style:family="table-column">
					<style:table-column-properties style:column-width="9.102cm"/>
				</style:style>
				<style:style style:name="Tabelle10.A1" style:family="table-cell">
					<style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
				</style:style>
				<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
					<style:text-properties officeooo:rsid="00094cd9" officeooo:paragraph-rsid="000a79ac" style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
				</style:style>
				<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties>
						<style:tab-stops/>
					</style:paragraph-properties>
					<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00094cd9" officeooo:paragraph-rsid="000a79ac" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
				</style:style>
				<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties fo:text-align="end" style:justify-single-word="false">
						<style:tab-stops/>
					</style:paragraph-properties>
					<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00094cd9" officeooo:paragraph-rsid="000a79ac" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
				</style:style>
				<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties fo:text-align="center" style:justify-single-word="false">
						<style:tab-stops/>
					</style:paragraph-properties>
					<style:text-properties style:font-name="Arial" fo:font-size="7pt" officeooo:rsid="00094cd9" officeooo:paragraph-rsid="000a79ac" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
				</style:style>
				<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:text-properties style:font-name="Arial" fo:font-size="6pt" officeooo:rsid="00094cd9" officeooo:paragraph-rsid="000a79ac" style:font-size-asian="6pt" style:font-size-complex="6pt"/>
				</style:style>
				<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
					<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00094cd9" officeooo:paragraph-rsid="000a79ac" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
				</style:style>
				<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
					<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="00094cd9" officeooo:paragraph-rsid="000a79ac" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="00094cd9" officeooo:paragraph-rsid="000a79ac" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.101cm" loext:contextual-spacing="false"/>
					<style:text-properties style:font-name="Arial" fo:font-size="14pt" officeooo:rsid="00094cd9" officeooo:paragraph-rsid="000a79ac" style:font-size-asian="14pt" style:font-size-complex="14pt"/>
				</style:style>
				<style:style style:name="T1" style:family="text">
					<style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
				</style:style>
			</office:automatic-styles>
			<office:body>
				<xsl:apply-templates select="student"/>
			</office:body>
		</office:document-content>
	</xsl:template>

	<xsl:template match="student">
		<office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
			</text:sequence-decls>

			<xsl:call-template name="abschnitt"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P4">---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</text:p>
			<text:p text:style-name="P1"/>
			<xsl:call-template name="abschnitt"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P4">---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</text:p>
			<text:p text:style-name="P1"/>
			<xsl:call-template name="abschnitt"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P4">---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</text:p>
			<text:p text:style-name="P1"/>
			<xsl:call-template name="abschnitt"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P4">---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</text:p>
			<text:p text:style-name="P1"/>
			<xsl:call-template name="abschnitt"/>

		</office:text>
	</xsl:template>
	<xsl:template name="abschnitt">
		<text:p text:style-name="P9">Certificate of Enrolment University of Applied Sciences Demo</text:p>
		<table:table table:name="Tabelle1" table:style-name="Tabelle1">
			<table:table-column table:style-name="Tabelle1.A"/>
			<table:table-column table:style-name="Tabelle1.B"/>
			<table:table-row table:style-name="Tabelle1.1">
				<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
					<text:p text:style-name="P5">To be submitted to (place where the certificate is to be submitted and its reference number, e.g. social security number).</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
					<text:p text:style-name="P6">Student number</text:p>
				</table:table-cell>
			</table:table-row>
			<table:table-row table:style-name="Tabelle1.2">
				<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
					<text:p text:style-name="P8">
						<xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="vornamen" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" />
					</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle1.B2" office:value-type="string">
					<text:p text:style-name="P7">
						<xsl:value-of select="matrikelnummer" />
					</text:p>
				</table:table-cell>
			</table:table-row>
		</table:table>
		<text:p text:style-name="P1" />
		<text:p text:style-name="P1">
			born on<xsl:text> </xsl:text><xsl:value-of select="geburtsdatum" />, is enrolled as a  regular student in<xsl:text> </xsl:text>
			<xsl:value-of select="studiensemester_aktuell" /><xsl:text> </xsl:text>(beginning <xsl:text> </xsl:text><xsl:value-of select="studienbeginn_aktuell" />)
			in the
			<xsl:choose>
				<xsl:when test="studiengang_typ='b'">
					Bachelor Degree program
				</xsl:when>
				<xsl:when test="studiengang_typ='m'">
					Master Degree program
				</xsl:when>
				<xsl:otherwise>
					Degree Program
				</xsl:otherwise>
			</xsl:choose>
			<text:line-break/>
			<xsl:choose>
			<xsl:when test="studiengang_bezeichnung_sto_englisch=''">
				<xsl:value-of select="studiengang_bezeichnung_eng" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="studiengang_bezeichnung_sto_englisch" />
			</xsl:otherwise>
		</xsl:choose>
			(<xsl:value-of select="studiengang_kz" />), <xsl:text> </xsl:text><xsl:value-of select="semester" />
			<xsl:choose>
				<xsl:when test="semester='1'">st</xsl:when>
				<xsl:when test="semester='2'">nd</xsl:when>
				<xsl:when test="semester='3'">rd</xsl:when>
				<xsl:otherwise>th</xsl:otherwise>
			</xsl:choose>
			semester (beginning<xsl:text> </xsl:text><xsl:value-of select="studiensemester_beginn" />, <xsl:text> </xsl:text><xsl:value-of select="studienbeginn_beginn" />).
		</text:p>
		<text:p text:style-name="P1"/>
		<table:table table:name="Tabelle2" table:style-name="Tabelle2">
			<table:table-column table:style-name="Tabelle2.A"/>
			<table:table-column table:style-name="Tabelle2.B"/>
			<table:table-row>
				<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
					<text:p text:style-name="P2">Date:<xsl:text> </xsl:text><xsl:value-of select="tagesdatum" /><xsl:text> </xsl:text></text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
					<text:p text:style-name="P3">Rector:<xsl:text> </xsl:text><xsl:value-of select="rektor" /></text:p>
				</table:table-cell>
			</table:table-row>
		</table:table>
	</xsl:template>
</xsl:stylesheet>