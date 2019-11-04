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
				<style:style style:name="Tabelle3" style:family="table">
					<style:table-properties style:width="16.401cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="margins" style:may-break-between-rows="false"/>
				</style:style>
				<style:style style:name="Tabelle3.A" style:family="table-column">
					<style:table-column-properties style:column-width="5.001cm" style:rel-column-width="19981*"/>
				</style:style>
				<style:style style:name="Tabelle3.B" style:family="table-column">
					<style:table-column-properties style:column-width="3.9cm" style:rel-column-width="15583*"/>
				</style:style>
				<style:style style:name="Tabelle3.C" style:family="table-column">
					<style:table-column-properties style:column-width="7.5cm" style:rel-column-width="29971*"/>
				</style:style>
				<style:style style:name="Tabelle3.A1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="" fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="1pt dotted #000000"/>
				</style:style>
				<style:style style:name="Tabelle3.B1" style:family="table-cell">
					<style:table-cell-properties fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle3.C1" style:family="table-cell">
					<style:table-cell-properties fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="1pt dotted #000000"/>
				</style:style>
				<style:style style:name="Tabelle3.A2" style:family="table-cell">
					<style:table-cell-properties fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle3.B2" style:family="table-cell">
					<style:table-cell-properties fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle3.C2" style:family="table-cell">
					<style:table-cell-properties fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border="none"/>
				</style:style>
				<style:style style:name="Tabelle1" style:family="table">
					<style:table-properties style:width="16.401cm" fo:margin-top="0.199cm" fo:margin-bottom="0cm" table:align="margins"/>
				</style:style>
				<style:style style:name="Tabelle1.A" style:family="table-column">
					<style:table-column-properties style:column-width="6.403cm" style:rel-column-width="3630*"/>
				</style:style>
				<style:style style:name="Tabelle1.B" style:family="table-column">
					<style:table-column-properties style:column-width="9.998cm" style:rel-column-width="5668*"/>
				</style:style>
				<style:style style:name="Tabelle1.1" style:family="table-row"/>
				<style:style style:name="Tabelle1.A1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="middle" fo:background-color="transparent" fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.079cm" fo:padding-bottom="0.079cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
						<style:background-image/>
					</style:table-cell-properties>
				</style:style>
				<style:style style:name="Tabelle1.B1" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="middle" fo:background-color="transparent" fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.079cm" fo:padding-bottom="0.079cm" fo:border="0.05pt solid #000000">
						<style:background-image/>
					</style:table-cell-properties>
				</style:style>
				<style:style style:name="Tabelle1.A2" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.071cm" fo:padding-bottom="0.071cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
				</style:style>
				<style:style style:name="Tabelle1.B2" style:family="table-cell">
					<style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.071cm" fo:padding-bottom="0.071cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
				</style:style>
				<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
					<style:text-properties fo:font-size="16pt" fo:font-weight="bold" officeooo:rsid="000de2a1" officeooo:paragraph-rsid="000de2a1" style:font-size-asian="16pt" style:font-weight-asian="bold" style:font-size-complex="16pt" style:font-weight-complex="bold"/>
				</style:style>
				<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
					<style:text-properties fo:font-size="2pt" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="1.75pt" style:font-size-complex="2pt"/>
				</style:style>
				<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:text-properties fo:font-size="9pt" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
					<style:text-properties fo:font-size="9pt" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
					<style:text-properties fo:font-size="9pt" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
					<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
					<style:text-properties fo:font-size="9pt" fo:font-weight="bold" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="9pt" style:font-weight-asian="bold" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
				</style:style>
				<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<loext:graphic-properties draw:fill="solid" draw:fill-color="#999999" draw:opacity="100%"/>
					<style:paragraph-properties fo:background-color="#999999"/>
					<style:text-properties fo:font-size="9pt" fo:font-weight="bold" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="9pt" style:font-weight-asian="bold" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
				</style:style>
				<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties>
						<style:tab-stops>
							<style:tab-stop style:position="1.499cm"/>
						</style:tab-stops>
					</style:paragraph-properties>
					<style:text-properties fo:font-size="6pt" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="0024d69b" style:font-size-asian="6pt" style:font-size-complex="6pt"/>
				</style:style>
				<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties>
						<style:tab-stops>
							<style:tab-stop style:position="1.499cm"/>
						</style:tab-stops>
					</style:paragraph-properties>
					<style:text-properties fo:font-size="6pt" officeooo:rsid="0030c435" officeooo:paragraph-rsid="0030c435" style:font-size-asian="6pt" style:font-size-complex="6pt"/>
				</style:style>
				<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
					<style:text-properties fo:font-size="9pt" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
					<style:text-properties fo:font-size="9pt" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
					<style:text-properties fo:font-size="9pt" officeooo:rsid="000de2a1" officeooo:paragraph-rsid="000de2a1" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
				</style:style>
				<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
					<style:text-properties fo:font-size="16pt" fo:font-weight="bold" officeooo:rsid="000de2a1" officeooo:paragraph-rsid="0030c435" style:font-size-asian="16pt" style:font-weight-asian="bold" style:font-size-complex="16pt" style:font-weight-complex="bold"/>
				</style:style>
				<style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
					<style:text-properties fo:font-size="16pt" officeooo:rsid="000de2a1" officeooo:paragraph-rsid="000de2a1" style:font-size-asian="16pt" style:font-size-complex="16pt"/>
				</style:style>
				<style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
					<style:text-properties fo:font-size="10pt" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties>
						<style:tab-stops>
							<style:tab-stop style:position="4.498cm"/>
						</style:tab-stops>
					</style:paragraph-properties>
					<style:text-properties fo:font-size="10pt" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
					<style:text-properties fo:font-size="10pt" officeooo:rsid="0013c612" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="8.75pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
					<style:paragraph-properties fo:break-before="page"/>
					<style:text-properties fo:font-size="16pt" fo:font-weight="bold" officeooo:rsid="000de2a1" officeooo:paragraph-rsid="0030c435" style:font-size-asian="16pt" style:font-weight-asian="bold" style:font-size-complex="16pt" style:font-weight-complex="bold"/>
				</style:style>
				<style:style style:name="P20" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
					<style:text-properties fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="0030c435" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
				</style:style>
				<style:style style:name="P21" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
					<style:text-properties fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="0030c435" officeooo:paragraph-rsid="0030c435" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
				</style:style>
				<style:style style:name="P22" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="P23" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
					<style:text-properties fo:font-size="10pt" officeooo:rsid="000f65a0" officeooo:paragraph-rsid="000f65a0" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="P24" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
					<style:text-properties fo:font-size="10pt" officeooo:rsid="0030c435" officeooo:paragraph-rsid="0030c435" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="P25" style:family="paragraph" style:parent-style-name="Table_20_Contents">
					<style:text-properties fo:font-size="10pt" officeooo:rsid="0013c612" officeooo:paragraph-rsid="0013c612" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
				</style:style>
				<style:style style:name="T1" style:family="text">
					<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
				</style:style>
				<style:style style:name="T2" style:family="text">
					<style:text-properties officeooo:rsid="0030c435"/>
				</style:style>
				<style:style style:name="fr1" style:family="graphic" style:parent-style-name="Frame">
					<style:graphic-properties style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="page-content" style:horizontal-pos="center" style:horizontal-rel="page-content" fo:padding="0cm" fo:border="none" style:shadow="none" draw:shadow-opacity="100%"/>
				</style:style>
				<style:style style:name="fr2" style:family="graphic" style:parent-style-name="Graphics">
					<style:graphic-properties style:run-through="foreground" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" style:shadow="none" draw:shadow-opacity="100%" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
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
			<!-- Wichtig für Mehrfachdruck (mehrere Studenten ausgewählt): Wenn ein Element (in diesem Fall Stempel und Unterschriftenblock) relativ zur SEITE ausgerichtet werden soll, 
			muss für jedes Dokument (jeder neue Durchlauf der Schleife) ein draw:frame-Tag definiert werden. Diese müssen ALLE VOR den ersten text:p-Elementen stehen.
			Deshalb wirde erst die Schleife für die draw:frames aufgerufen, dann folg tder Inhalt -->
			<xsl:if test="position()=1">
				<xsl:for-each select="../pruefung">
					<xsl:variable select="position()" name="number"/><!-- Variable number definieren, die nach jedem Dokument um eines erhöht wird (position) -->
					<draw:frame draw:style-name="fr1" draw:name="Rahmen{$number}" text:anchor-type="page" text:anchor-page-number="{$number}" svg:y="20.001cm" draw:z-index="0">
						<draw:text-box fo:min-height="0.499cm" fo:min-width="2cm">
							<table:table table:name="Tabelle3" table:style-name="Tabelle3">
								<table:table-column table:style-name="Tabelle3.A"/>
								<table:table-column table:style-name="Tabelle3.B"/>
								<table:table-column table:style-name="Tabelle3.C"/>
								<table:table-row>
									<table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
										<text:p text:style-name="P25">
											<draw:frame draw:style-name="fr2" draw:name="Bild{$number}" text:anchor-type="char" svg:x="5.214cm" svg:y="-1.069cm" svg:width="3.51cm" svg:height="3.51cm" draw:z-index="1">
												<draw:image xlink:href="Pictures/10000201000002290000022939997AEC.png" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
											</draw:frame>Vienna, <xsl:value-of select="ort_datum" />
										</text:p>
									</table:table-cell>
									<table:table-cell table:style-name="Tabelle3.B1" office:value-type="string">
										<text:p text:style-name="P22"/>
									</table:table-cell>
									<table:table-cell table:style-name="Tabelle3.C1" office:value-type="string">
										<text:p text:style-name="P22"/>
									</table:table-cell>
								</table:table-row>
								<table:table-row>
									<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
										<text:p text:style-name="P25">Place, Date</text:p>
									</table:table-cell>
									<table:table-cell table:style-name="Tabelle3.B2" office:value-type="string">
										<text:p text:style-name="P22"/>
									</table:table-cell>
									<table:table-cell table:style-name="Tabelle3.C2" office:value-type="string">
										<text:p text:style-name="P25">
											<xsl:value-of select="vorsitz_nachname" />
										</text:p>
										<text:p text:style-name="P25">Chair</text:p>
									</table:table-cell>
								</table:table-row>
							</table:table>
						</draw:text-box>
					</draw:frame>
				</xsl:for-each>
			</xsl:if>
			
			<text:p text:style-name="P19">
				<text:span text:style-name="T2">BACHELOR OF SCIENCE</text:span>
			</text:p>
			<text:p text:style-name="P14">CERTIFICATE</text:p>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P15">
				<xsl:choose>
					<xsl:when test="stg_art='b'">Bachelor</xsl:when>					
					<xsl:when test="stg_art='m'">Master</xsl:when>					
					<xsl:when test="stg_art='d'">Diploma</xsl:when>					
					<xsl:when test="stg_art='l'">Course</xsl:when>				
					<xsl:when test="stg_art='k'">Short study</xsl:when>											
				</xsl:choose> Degree Program
			</text:p>
			<text:p text:style-name="P1">
				<xsl:value-of select="stg_bezeichnung_engl"/>
			</text:p>
			<text:p text:style-name="P13"/>
			<text:p text:style-name="P13"/>
			<text:p text:style-name="P13"/>
			<text:p text:style-name="P13"/>
			<text:p text:style-name="P13"/>
			<text:p text:style-name="P11">StudentID: <xsl:value-of select="matrikelnr"/></text:p>
			<text:p text:style-name="P11">Program Code: <xsl:value-of select="studiengang_kz"/></text:p>
			<text:p text:style-name="P11"/>
			<text:p text:style-name="P12"/>
			<text:p text:style-name="P12"/>
			<text:p text:style-name="P12"/>
			<text:p text:style-name="P12"/>
			<text:p text:style-name="P12"/>
			<text:p text:style-name="P12"/>
			<text:p text:style-name="P12"/>
			<text:p text:style-name="P17">First Name/Last Name:<text:tab/>
				<text:span text:style-name="T1">
					<xsl:value-of select="name" />
				</text:span>
			</text:p>
			<text:p text:style-name="P16"/>
			<text:p text:style-name="P17">Date of Birth:<text:tab/>
				<xsl:value-of select="gebdatum" />
			</text:p>
			<text:p text:style-name="P17"/>
			<text:p text:style-name="P17"/>
			<table:table table:name="Tabelle1" table:style-name="Tabelle1">
				<table:table-column table:style-name="Tabelle1.A"/>
				<table:table-column table:style-name="Tabelle1.B"/>
				<table:table-row table:style-name="Tabelle1.1">
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P21">Final assessment</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
						<text:p text:style-name="P20">
							<text:span text:style-name="T2">
								<xsl:value-of select="abschlussbeurteilung_kurzbzEng" />
							</text:span>
						</text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row table:style-name="Tabelle1.1">
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P24">Date of assessment</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B2" office:value-type="string">
						<text:p text:style-name="P23">
							<xsl:value-of select="datum" />
						</text:p>
					</table:table-cell>
				</table:table-row>
			</table:table>
			<text:p text:style-name="P2"/>
			<text:p text:style-name="P9"/>
			<text:p text:style-name="P9"/>
			<text:p text:style-name="P10"/>
			<text:p text:style-name="P18"/>
			<text:p text:style-name="P18"/>
			<text:p text:style-name="P18"/>
		</office:text>
	</xsl:template>

</xsl:stylesheet>