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
<xsl:template match="abschlusspruefung">
<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
	<office:scripts/>
	<office:font-face-decls>
		<style:font-face style:name="Helvetica" svg:font-family="Helvetica"/>
		<style:font-face style:name="Mangal1" svg:font-family="Mangal"/>
		<style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Microsoft YaHei" svg:font-family="&apos;Microsoft YaHei&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="SimSun" svg:font-family="SimSun" style:font-family-generic="system" style:font-pitch="variable"/>
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="122%" fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="0006c6a3" officeooo:paragraph-rsid="0006c6a3" style:font-size-asian="8.75pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="122%" fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="28pt" officeooo:rsid="0006c6a3" officeooo:paragraph-rsid="0006c6a3" style:font-size-asian="28pt" style:font-size-complex="28pt"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="122%" fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="16pt" officeooo:rsid="0006c6a3" officeooo:paragraph-rsid="0006c6a3" style:font-size-asian="16pt" style:font-size-complex="16pt"/>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="122%" fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:color="#ff3333" fo:font-weight="bold" fo:font-size="16pt" officeooo:rsid="0006c6a3" officeooo:paragraph-rsid="0006c6a3" style:font-size-asian="16pt" style:font-size-complex="16pt"/>
		</style:style>
		<style:style style:name="Seitenumbruch" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="page" fo:line-height="122%" fo:text-align="center" style:justify-single-word="false"/>
			<style:paragraph-properties fo:line-height="122%" fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="28pt" officeooo:rsid="0006c6a3" officeooo:paragraph-rsid="0006c6a3" style:font-size-asian="28pt" style:font-size-complex="28pt"/>
		</style:style>
	</office:automatic-styles>

	<office:body>
		<xsl:apply-templates select="pruefung"/>
	</office:body>
	</office:document-content>
</xsl:template>

<xsl:template match="pruefung">
		<office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
			</text:sequence-decls>
			<text:p text:style-name="Seitenumbruch">Diploma</text:p>
				<!-- Ueberprueft ob benoetigte Datenfelder leer sind -->
				<xsl:if test="staatsbuergerschaft = ''"><text:p text:style-name="P4">Staatsbürgerschaft nicht angegeben</text:p></xsl:if>
				<xsl:if test="pruefungstyp_kurzbz != 'Diplom'"><text:p text:style-name="P4">Prüfungstyp passt nicht zu diesem Dokumenttyp</text:p></xsl:if>
				<xsl:if test="datum = ''"><text:p text:style-name="P4">Datum der Abschlussprüfung nicht gesetzt</text:p></xsl:if>
				<xsl:if test="titel = ''"><text:p text:style-name="P4">Kein akademischer Grad ausgewählt</text:p></xsl:if>
				<xsl:if test="sponsion = ''"><text:p text:style-name="P4">Sponsionsdatum nicht gesetzt</text:p></xsl:if>
				<xsl:if test="bescheidbgbl1 = ''"><text:p text:style-name="P4">Bundesgesetzblattnummer (BGBl) beim Studiengang ist nicht gesetzt</text:p></xsl:if>

			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut</text:p>
			<text:p text:style-name="P1">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna</text:p>
			<text:p text:style-name="P1">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam</text:p>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P3"><xsl:value-of select="anrede_engl" /><xsl:text> </xsl:text><xsl:value-of select="name" /></text:p>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1">born <xsl:value-of select="gebdatum" /> in
			<xsl:if test="string-length(gebort)!=0">
				<xsl:value-of select="gebort" />
				<xsl:text>, </xsl:text>
			</xsl:if>
			<xsl:value-of select="geburtsnation_engl" />, citizen of <xsl:value-of select="staatsbuergerschaft_engl" />,</text:p>
			<text:p text:style-name="P1">student of the University of Applied Sciences
				<xsl:choose>
					<xsl:when test="stg_art='b'">Bachelor</xsl:when>
					<xsl:when test="stg_art='m'">Master</xsl:when>
					<xsl:when test="stg_art='d'">Diploma</xsl:when>
					<xsl:when test="stg_art='l'">Course</xsl:when>
					<xsl:when test="stg_art='k'">Short study</xsl:when>
				</xsl:choose>
				<xsl:if test="stg_art != 'l' or 'k'" >'s</xsl:if> degree program</text:p>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P3"><xsl:value-of select="stg_bezeichnung_engl" /></text:p>
			<text:p text:style-name="P1">(program classification number <xsl:value-of select="studiengang_kz" />)</text:p>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1">after successfully passing the diploma examination on <xsl:value-of select="datum" /></text:p>
			<text:p text:style-name="P1">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut</text:p>
			<text:p text:style-name="P1">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et</text:p>
			<text:p text:style-name="P1">Lorem ipsum dolor sit amet, consetetur</text:p>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P3"><xsl:value-of select="titel" /></text:p>
			<text:p text:style-name="P1">abbreviated</text:p>
			<text:p text:style-name="P3"><xsl:value-of select="akadgrad_kurzbz" /></text:p>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1">Vienna, <xsl:value-of select="sponsion" /></text:p>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1">Lorem ipsum dolor sit amet, consetetur sadipscing elitr</text:p>
			<text:p text:style-name="P1">Lorem ipsum</text:p>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1"><xsl:value-of select="rektor" /></text:p>
		</office:text>
</xsl:template>
</xsl:stylesheet>
