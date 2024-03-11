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
				<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
				<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
				<style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
				<style:font-face style:name="Lohit Devanagari" svg:font-family="&apos;Lohit Devanagari&apos;"/>
				<style:font-face style:name="Lohit Devanagari1" svg:font-family="&apos;Lohit Devanagari&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
				<style:font-face style:name="Noto Sans CJK SC" svg:font-family="&apos;Noto Sans CJK SC&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
				<style:font-face style:name="Noto Serif CJK SC" svg:font-family="&apos;Noto Serif CJK SC&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
				<style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="swiss" style:font-pitch="variable"/>
				<style:font-face style:name="Times New Roman" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
			</office:font-face-decls>
			<office:automatic-styles>
				<style:style style:name="Tabelle1" style:family="table">
					<style:table-properties style:width="16.245cm" fo:margin-left="-0.191cm" table:align="left" style:writing-mode="lr-tb"/>
				</style:style>
				<style:style style:name="Tabelle1.A" style:family="table-column">
					<style:table-column-properties style:column-width="16.245cm"/>
				</style:style>
				<style:style style:name="Tabelle1.1" style:family="table-row">
					<style:table-row-properties fo:keep-together="auto"/>
				</style:style>
				<style:style style:name="Tabelle1.A1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="none" style:writing-mode="lr-tb"/>
				</style:style>
				<style:style style:name="Tabelle2" style:family="table">
					<style:table-properties style:width="16.245cm" fo:margin-left="-0.191cm" table:align="left" style:writing-mode="lr-tb"/>
				</style:style>
				<style:style style:name="Tabelle2.A" style:family="table-column">
					<style:table-column-properties style:column-width="4.41cm"/>
				</style:style>
				<style:style style:name="Tabelle2.B" style:family="table-column">
					<style:table-column-properties style:column-width="11.836cm"/>
				</style:style>
				<style:style style:name="Tabelle2.1" style:family="table-row">
					<style:table-row-properties fo:keep-together="auto"/>
				</style:style>
				<style:style style:name="Tabelle2.A1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
				</style:style>
				<style:style style:name="Tabelle2.5" style:family="table-row">
					<style:table-row-properties style:min-row-height="8.301cm" fo:keep-together="auto"/>
				</style:style>
				<style:style style:name="Tabelle3" style:family="table">
					<style:table-properties style:width="16.245cm" fo:margin-left="-0.191cm" table:align="left" style:writing-mode="lr-tb"/>
				</style:style>
				<style:style style:name="Tabelle3.A" style:family="table-column">
					<style:table-column-properties style:column-width="7.528cm"/>
				</style:style>
				<style:style style:name="Tabelle3.B" style:family="table-column">
					<style:table-column-properties style:column-width="0.416cm"/>
				</style:style>
				<style:style style:name="Tabelle3.C" style:family="table-column">
					<style:table-column-properties style:column-width="8.301cm"/>
				</style:style>
				<style:style style:name="Tabelle3.1" style:family="table-row">
					<style:table-row-properties fo:keep-together="auto"/>
				</style:style>
				<style:style style:name="Tabelle3.A1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
				</style:style>
				<style:style style:name="Tabelle3.B1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="none" style:writing-mode="lr-tb"/>
				</style:style>
				<style:style style:name="Tabelle3.A2" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="none" style:writing-mode="lr-tb"/>
				</style:style>
				<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Header">
					<style:paragraph-properties fo:line-height="150%">
						<style:tab-stops>
							<style:tab-stop style:position="3.501cm"/>
							<style:tab-stop style:position="9.502cm"/>
							<style:tab-stop style:position="14.753cm"/>
							<style:tab-stop style:position="15.503cm"/>
							<style:tab-stop style:position="16.002cm" style:type="right"/>
						</style:tab-stops>
					</style:paragraph-properties>
				</style:style>
				<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Header">
					<style:paragraph-properties fo:line-height="150%">
						<style:tab-stops>
							<style:tab-stop style:position="3.501cm"/>
						</style:tab-stops>
					</style:paragraph-properties>
				</style:style>
				<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Header">
					<style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:contextual-spacing="false">
						<style:tab-stops>
							<style:tab-stop style:position="7.502cm"/>
							<style:tab-stop style:position="9.502cm"/>
							<style:tab-stop style:position="14.753cm"/>
							<style:tab-stop style:position="15.503cm"/>
							<style:tab-stop style:position="16.002cm" style:type="right"/>
						</style:tab-stops>
					</style:paragraph-properties>
				</style:style>
				<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Header">
					<style:paragraph-properties fo:margin-top="0.494cm" fo:margin-bottom="0.882cm" style:contextual-spacing="false" fo:line-height="0.776cm">
						<style:tab-stops>
							<style:tab-stop style:position="7.502cm"/>
							<style:tab-stop style:position="9.502cm"/>
							<style:tab-stop style:position="14.753cm"/>
							<style:tab-stop style:position="15.503cm"/>
							<style:tab-stop style:position="16.002cm" style:type="right"/>
						</style:tab-stops>
					</style:paragraph-properties>
					<style:text-properties fo:font-size="14pt" style:font-size-asian="14pt" style:font-size-complex="14pt"/>
				</style:style>
				<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Header">
					<style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:contextual-spacing="false">
						<style:tab-stops>
							<style:tab-stop style:position="7.502cm"/>
							<style:tab-stop style:position="9.502cm"/>
							<style:tab-stop style:position="14.753cm"/>
							<style:tab-stop style:position="15.503cm"/>
							<style:tab-stop style:position="16.002cm" style:type="right"/>
						</style:tab-stops>
					</style:paragraph-properties>
					<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Header">
					<style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:contextual-spacing="false">
						<style:tab-stops>
							<style:tab-stop style:position="7.502cm"/>
							<style:tab-stop style:position="9.502cm"/>
							<style:tab-stop style:position="14.753cm"/>
							<style:tab-stop style:position="15.503cm"/>
							<style:tab-stop style:position="16.002cm" style:type="right"/>
						</style:tab-stops>
					</style:paragraph-properties>
					<style:text-properties fo:font-size="9pt" fo:language="none" fo:country="none" style:font-size-asian="9pt" style:language-asian="none" style:country-asian="none" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Header">
					<style:text-properties fo:language="none" fo:country="none" style:language-asian="none" style:country-asian="none"/>
				</style:style>
				<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:contextual-spacing="false" fo:line-height="0.776cm">
						<style:tab-stops>
							<style:tab-stop style:position="9.502cm"/>
							<style:tab-stop style:position="16.002cm" style:type="right"/>
						</style:tab-stops>
					</style:paragraph-properties>
					<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:contextual-spacing="false"/>
					<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:contextual-spacing="false">
						<style:tab-stops>
							<style:tab-stop style:position="9.502cm"/>
							<style:tab-stop style:position="16.002cm" style:type="right"/>
						</style:tab-stops>
					</style:paragraph-properties>
					<style:text-properties fo:font-size="9pt" fo:language="none" fo:country="none" style:font-size-asian="9pt" style:language-asian="none" style:country-asian="none" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:contextual-spacing="false"/>
				</style:style>
				<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:contextual-spacing="false" style:snap-to-layout-grid="false"/>
				</style:style>
				<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
					<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties fo:break-after="page"/>
					<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="T1" style:family="text">
					<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt"/>
				</style:style>
				<style:style style:name="T2" style:family="text">
					<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="T3" style:family="text">
					<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="T4" style:family="text">
					<style:text-properties fo:font-size="9pt" fo:language="none" fo:country="none" style:font-size-asian="9pt" style:language-asian="none" style:country-asian="none" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="T5" style:family="text">
					<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
				</style:style>
				<style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
					<style:graphic-properties fo:margin-left="0.319cm" fo:margin-right="0.319cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:run-through="background" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit" style:wrap-contour="true" style:wrap-contour-mode="outside" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="transparent" draw:fill="none" draw:fill-color="#ffffff" fo:padding="0.002cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
				</style:style>
				<style:style style:name="Sect1" style:family="section">
					<style:section-properties text:dont-balance-text-columns="true" style:writing-mode="lr-tb" style:editable="false">
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
			<office:forms form:automatic-focus="false" form:apply-design-mode="false"/>
			<text:tracked-changes text:track-changes="true"/>
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Figure"/>
			</text:sequence-decls>
			<text:section text:style-name="Sect1" text:name="Bereich1" text:protected="true">
				<table:table table:name="Tabelle1" table:style-name="Tabelle1">
					<table:table-column table:style-name="Tabelle1.A"/>
					<table:table-row table:style-name="Tabelle1.1">
						<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
							<text:p text:style-name="P1">

								<text:span text:style-name="T2">Studiengang: 
									<text:s text:c="13"/>
								</text:span>
								<text:span text:style-name="T4"><xsl:value-of select="studiengang"/></text:span>
							</text:p>
							<text:p text:style-name="P2">
								<text:span text:style-name="T2">Organisationsform:
									<text:tab/>
								</text:span>
								<text:span text:style-name="T4"><xsl:value-of select="organisationsform"/></text:span>
							</text:p>
						</table:table-cell>
					</table:table-row>
				</table:table>
				<text:p text:style-name="P4">Abmeldung vom Studium durch Studierende</text:p>
				<table:table table:name="Tabelle2" table:style-name="Tabelle2">
					<table:table-column table:style-name="Tabelle2.A"/>
					<table:table-column table:style-name="Tabelle2.B"/>
					<table:table-row table:style-name="Tabelle2.1">
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P3">
								<text:span text:style-name="T3">Name der*des Studierenden</text:span>
							</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P6">
								<text:span text:style-name="T4"><xsl:value-of select="name"/></text:span>
							</text:p>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle2.1">
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P5">Personenkennzeichen</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P5">
								<text:span text:style-name="T4"><xsl:value-of select="personenkz"/></text:span>
							</text:p>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle2.1">
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P5">Studienjahr</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P5">
								<text:span text:style-name="T4"><xsl:value-of select="studienjahr"/></text:span>
							</text:p>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle2.1">
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P5">Studiensemester</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P5">
								<text:span text:style-name="T4"><xsl:value-of select="studiensemester"/></text:span>
							</text:p>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle2.1">
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P5">Semester</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
							<text:p text:style-name="P5">
								<text:span text:style-name="T4"><xsl:value-of select="semester"/></text:span>
							</text:p>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle2.5">
						<table:table-cell table:style-name="Tabelle2.A1" table:number-columns-spanned="2" office:value-type="string">
							<text:p text:style-name="P8">Grund der Abmeldung:</text:p>
							<text:p text:style-name="P10">
								<text:span text:style-name="T4"><xsl:value-of select="grund"/></text:span>
							</text:p>
						</table:table-cell>
						<table:covered-table-cell/>
					</table:table-row>
				</table:table>
				<text:p text:style-name="Standard"/>

				<text:p text:style-name="Standard"/>
				<text:p text:style-name="Standard"/>
				<text:p text:style-name="Standard"/>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T2">Wir weisen Sie darauf hin, dass Ihr FHTW Account noch 21 Tage aktiv ist. Wir bitten Sie, alle benötigte Dateien (Zeugnisse, Studienerfolgsbestätigungen, Studienbestätigungen, etc.) innerhalb dieses Zeitraums herunterzuladen. Für die Ausstellung von Duplikaten fallen nach Inaktivsetzung des CIS-Accounts Kosten an.
						<text:line-break/>
					</text:span>
					<text:span text:style-name="T1">Sie sind gem. Ausbildungsvertrag verpflichtet, unverzüglich alle zur Verfügung gestellten Gerätschaften, Bücher, Schlüssel und sonstige Materialien zurückzugeben.</text:span>
					<text:span text:style-name="T2">
						<text:line-break/>Bei Abmeldung vor dem 01.09. bzw. 15.02. und bereits eingezahltem Studienbeitrag für das kommende Semester: Wir informieren Sie darüber, dass der Studienbeitrag für das kommende Semester von Ihnen zurückgefordert werden kann. Bitte geben Sie uns dafür innerhalb von 14 Tagen Ihre Bankdaten an folgende E-Mail-Adresse bekannt: billing@technikum-wien.at.
					</text:span>
				</text:p>
				<text:p text:style-name="P14"/>
			</text:section>
		</office:text>
	</xsl:template>

</xsl:stylesheet>