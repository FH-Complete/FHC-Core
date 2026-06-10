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
	xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0">

	<xsl:output method="xml" version="1.0" indent="yes"/>

	<xsl:template match="antraege">
<office:document-content
	xmlns:officeooo="http://openoffice.org/2009/office"
	xmlns:css3t="http://www.w3.org/TR/css3-text/"
	xmlns:grddl="http://www.w3.org/2003/g/data-view#"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:rpt="http://openoffice.org/2005/report"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
	xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
	xmlns:oooc="http://openoffice.org/2004/calc"
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
	xmlns:ooow="http://openoffice.org/2004/writer"
	xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
	xmlns:xlink="http://www.w3.org/1999/xlink"
	xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
	xmlns:ooo="http://openoffice.org/2004/office"
	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
	xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0"
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
	xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
	xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2"
	xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0"
	xmlns:tableooo="http://openoffice.org/2009/table"
	xmlns:drawooo="http://openoffice.org/2010/draw"
	xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0"
	xmlns:dom="http://www.w3.org/2001/xml-events"
	xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:math="http://www.w3.org/1998/Math/MathML"
	xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
	xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"
	xmlns:xforms="http://www.w3.org/2002/xforms" office:version="1.3">
	<office:scripts/>
	<office:font-face-decls>
		<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Arial1" svg:font-family="Arial" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Lohit Devanagari" svg:font-family="&apos;Lohit Devanagari&apos;"/>
		<style:font-face style:name="Lohit Devanagari1" svg:font-family="&apos;Lohit Devanagari&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Noto Sans CJK SC" svg:font-family="&apos;Noto Sans CJK SC&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Tahoma1" svg:font-family="Tahoma" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Times New Roman" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Times New Roman1" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="Tabelle1" style:family="table">
			<style:table-properties style:width="16.245cm" fo:margin-left="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="Tabelle1.A" style:family="table-column">
			<style:table-column-properties style:column-width="16.245cm"/>
		</style:style>
		<style:style style:name="Tabelle1.1" style:family="table-row">
			<style:table-row-properties fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle1.A1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="transparent" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="none">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle2" style:family="table">
			<style:table-properties style:width="17.126cm" fo:margin-left="0.009cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="Tabelle2.A" style:family="table-column">
			<style:table-column-properties style:column-width="6.909cm"/>
		</style:style>
		<style:style style:name="Tabelle2.B" style:family="table-column">
			<style:table-column-properties style:column-width="10.215cm"/>
		</style:style>
		<style:style style:name="Tabelle2.1" style:family="table-row">
			<style:table-row-properties style:min-row-height="0.679cm" fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle2.A1" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.123cm" fo:padding-right="0.123cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.B1" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="bottom" fo:padding-left="0.123cm" fo:padding-right="0.123cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.2" style:family="table-row">
			<style:table-row-properties style:row-height="0.658cm" fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle2.3" style:family="table-row">
			<style:table-row-properties style:row-height="0.626cm" fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle2.4" style:family="table-row">
			<style:table-row-properties style:row-height="0.642cm" fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle2.5" style:family="table-row">
			<style:table-row-properties style:min-row-height="7.502cm" fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle2.A5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.123cm" fo:padding-right="0.123cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.75pt solid #000000" fo:border-right="0.75pt solid #000000" fo:border-top="0.75pt solid #000000" fo:border-bottom="0.5pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.6" style:family="table-row">
			<style:table-row-properties style:min-row-height="0.801cm" fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle3" style:family="table">
			<style:table-properties style:width="15.998cm" fo:margin-left="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="Tabelle3.A" style:family="table-column">
			<style:table-column-properties style:column-width="6.863cm"/>
		</style:style>
		<style:style style:name="Tabelle3.B" style:family="table-column">
			<style:table-column-properties style:column-width="0.591cm"/>
		</style:style>
		<style:style style:name="Tabelle3.C" style:family="table-column">
			<style:table-column-properties style:column-width="8.544cm"/>
		</style:style>
		<style:style style:name="Tabelle3.1" style:family="table-row">
			<style:table-row-properties fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle3.A1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="transparent" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="none">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle3.2" style:family="table-row">
			<style:table-row-properties style:min-row-height="2.611cm" fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle3.A2" style:family="table-cell">
			<style:table-cell-properties fo:background-color="transparent" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.5pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle3.A3" style:family="table-cell">
			<style:table-cell-properties fo:background-color="transparent" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="none">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Header">
			<style:paragraph-properties fo:line-height="130%" fo:orphans="0" fo:widows="0">
				<style:tab-stops>
					<style:tab-stop style:position="7.502cm"/>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="14.753cm"/>
					<style:tab-stop style:position="15.503cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="2pt" fo:font-style="italic" style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" fo:font-weight="bold" style:font-size-asian="2pt" style:font-style-asian="italic" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:orphans="0" fo:widows="0"/>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:contextual-spacing="false" fo:line-height="130%" fo:orphans="0" fo:widows="0">
				<style:tab-stops>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.494cm" fo:margin-bottom="0.882cm" style:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="7.502cm"/>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="14.753cm"/>
					<style:tab-stop style:position="15.503cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:contextual-spacing="false" fo:orphans="0" fo:widows="0">
				<style:tab-stops>
					<style:tab-stop style:position="7.502cm"/>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="14.753cm"/>
					<style:tab-stop style:position="15.503cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.212cm" fo:margin-bottom="0.212cm" style:contextual-spacing="false" fo:orphans="0" fo:widows="0">
				<style:tab-stops>
					<style:tab-stop style:position="7.502cm"/>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="14.753cm"/>
					<style:tab-stop style:position="15.503cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="">
			<style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:contextual-spacing="false" fo:orphans="0" fo:widows="0" fo:keep-with-next="always">
				<style:tab-stops>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="">
			<style:paragraph-properties fo:margin-top="0.212cm" fo:margin-bottom="0.212cm" style:contextual-spacing="false" fo:orphans="0" fo:widows="0" fo:keep-with-next="always">
				<style:tab-stops>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="0.706cm" fo:orphans="0" fo:widows="0"/>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.494cm" fo:margin-bottom="0.706cm" style:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="0.706cm" fo:orphans="0" fo:widows="0"/>
		</style:style>
		<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:font-size="11pt" fo:language="de" fo:country="DE" style:font-size-asian="11pt" style:font-name-complex="Times New Roman1"/>
		</style:style>
		<style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.494cm" fo:margin-bottom="0.706cm" style:contextual-spacing="false" fo:break-after="page">
				<style:tab-stops>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="T1" style:family="text">
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="T2" style:family="text">
			<style:text-properties fo:font-size="14pt" style:font-size-asian="14pt" style:font-size-complex="14pt"/>
		</style:style>
		<style:style style:name="T3" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T4" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T5" style:family="text">
			<style:text-properties fo:font-size="9pt" fo:language="de" fo:country="DE" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T6" style:family="text">
			<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
			<style:graphic-properties fo:margin-left="0.318cm" fo:margin-right="0.318cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:run-through="foreground" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit" style:wrap-contour="true" style:wrap-contour-mode="outside" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="transparent" draw:fill="none" draw:fill-color="#ffffff" fo:padding="0cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
		</style:style>
		<style:style style:name="Sect1" style:family="section">
			<style:section-properties style:editable="false">
				<style:columns fo:column-count="1" fo:column-gap="0cm"/>
			</style:section-properties>
		</style:style>
	</office:automatic-styles>
	<office:body>
		<xsl:apply-templates match="antrag"/>	
	</office:body>
</office:document-content>
</xsl:template> 

<xsl:template match="antrag">
	<office:text>
			<text:tracked-changes text:track-changes="true"/>
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Figure"/>
			</text:sequence-decls>
			<text:section text:style-name="Sect1" text:name="TextSection" text:protected="true">
				<text:p text:style-name="P2"/>
				<table:table table:name="Tabelle1" table:style-name="Tabelle1">
					<table:table-column table:style-name="Tabelle1.A"/>
					<table:table-row table:style-name="Tabelle1.1">
						<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
							<text:p text:style-name="P1">
								<text:span text:style-name="T1">Studiengang: 
									<text:s text:c="16"/>
								</text:span>
					
								<text:span text:style-name="T1"><xsl:value-of select="studiengang"/></text:span>
		
							</text:p>
							<text:p text:style-name="P3">
								<text:span text:style-name="T1">Organisationsform:
									<text:tab/>
								</text:span>
								<text:span text:style-name="T1"><xsl:value-of select="organisationsform"/></text:span>
							</text:p>
						</table:table-cell>
					</table:table-row>
				</table:table>
				<text:p text:style-name="P5">
					<text:span text:style-name="T2">Antrag auf Unterbrechung des Studiums</text:span>
				</text:p>
				<table:table table:name="Tabelle2" table:style-name="Tabelle2">
					<table:table-column table:style-name="Tabelle2.A"/>
					<table:table-column table:style-name="Tabelle2.B"/>
					<table:table-row table:style-name="Tabelle2.1">
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P6">
								<text:span text:style-name="T3">Name der*des Studierenden</text:span>
							</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle2.B1" office:value-type="string">
							<text:h text:style-name="P8" text:outline-level="1">

								<text:span text:style-name="T5"><xsl:value-of select="name"/></text:span>

							</text:h>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle2.2">
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P6">
								<text:span text:style-name="T3">Personenkennzeichen</text:span>
							</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle2.B1" office:value-type="string">
							<text:h text:style-name="P8" text:outline-level="1">

								<text:span text:style-name="T5"><xsl:value-of select="personenkz"/></text:span>
			
							</text:h>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle2.3">
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P6">
								<text:span text:style-name="T3">Studienjahr</text:span>
							</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle2.B1" office:value-type="string">
							<text:h text:style-name="P8" text:outline-level="1">

							<text:span text:style-name="T5"><xsl:value-of select="studienjahr"/></text:span>

							</text:h>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle2.4">
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P6">
								<text:span text:style-name="T3">Aktuelles Semester</text:span>
							</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle2.B1" office:value-type="string">
							<text:h text:style-name="P8" text:outline-level="1">
								<text:span text:style-name="T5"><xsl:value-of select="semester"/></text:span>
							</text:h>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle2.5">
						<table:table-cell table:style-name="Tabelle2.A5" table:number-columns-spanned="2" office:value-type="string">
							<text:p text:style-name="P4">
								<text:span text:style-name="T3">Grund der Unterbrechung:</text:span>
							</text:p>
							<text:p text:style-name="P4">

								<text:span text:style-name="T3"><xsl:value-of select="grund"/></text:span>

							</text:p>
						</table:table-cell>
						<table:covered-table-cell/>
					</table:table-row>
					<table:table-row table:style-name="Tabelle2.6">
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P7">
								<text:span text:style-name="T3">Wiedereinstieg am</text:span>
							</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle2.B1" office:value-type="string">
							<text:h text:style-name="P9" text:outline-level="1">
	
								<text:span text:style-name="T5"><xsl:value-of select="returndate"/></text:span>

							</text:h>
						</table:table-cell>
					</table:table-row>
				</table:table>
				<text:p text:style-name="P10"/>
				<table:table table:name="Tabelle3" table:style-name="Tabelle3">
					<table:table-column table:style-name="Tabelle3.A"/>
					<table:table-column table:style-name="Tabelle3.B"/>
					<table:table-column table:style-name="Tabelle3.C"/>
					<table:table-row table:style-name="Tabelle3.1">
						<table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
							<text:p text:style-name="P13">
								<text:span text:style-name="T3">Datum: </text:span>
		
								<text:span text:style-name="T5"><xsl:value-of select="createdate"/></text:span>
	
							</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
							<text:p text:style-name="P11"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
							<text:p text:style-name="P11"/>
						</table:table-cell>
					</table:table-row>
				</table:table>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P12">
					<text:span text:style-name="T3">Infolge der Weiterentwicklung der Qualität des Studienganges kann es zu Änderungen der Studienbedingungen beim Wiedereinstieg kommen (z. B. Studienplan, Prüfungsordnung etc.)</text:span>
				</text:p>
				<text:p text:style-name="P12">
					<text:span text:style-name="T3">Falls Sie das Studium im Wintersemester vor dem 15.10. bzw. im Sommersemester vor dem 15.3. beenden, wird Ihnen der Studienbeitrag für das aktuelle Semester rückerstattet. Bitte geben Sie uns innerhalb von 14 Tagen Ihre Bankdaten an folgende E-Mail-Adresse bekannt: </text:span>
					<text:a xlink:type="simple" xlink:href="mailto:billing@technikum-wien.at" text:style-name="Internet_20_link" text:visited-style-name="Visited_20_Internet_20_Link">
						<text:span text:style-name="Internet_20_link">
							<text:span text:style-name="T3">billing@technikum-wien.at</text:span>
						</text:span>
					</text:a>
					<text:span text:style-name="T3">. </text:span>
				</text:p>
				<text:p text:style-name="P15"/>
			</text:section>
		</office:text>
</xsl:template>
</xsl:stylesheet>