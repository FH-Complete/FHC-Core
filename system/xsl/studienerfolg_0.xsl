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
<xsl:template match="studienerfolge">

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
		<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Times New Roman" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Arial1" svg:font-family="Arial" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Microsoft YaHei" svg:font-family="&apos;Microsoft YaHei&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Tahoma1" svg:font-family="Tahoma" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Times New Roman1" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="Tabelle1" style:family="table">
			<style:table-properties style:width="16.452cm" fo:margin-left="-0.199cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="Tabelle1.A" style:family="table-column">
			<style:table-column-properties style:column-width="9.5cm"/>
		</style:style>
		<style:style style:name="Tabelle1.B" style:family="table-column">
			<style:table-column-properties style:column-width="3.2cm"/>
		</style:style>
		<style:style style:name="Tabelle1.C" style:family="table-column">
			<style:table-column-properties style:column-width="3.752cm"/>
		</style:style>
		<style:style style:name="Tabelle1.1" style:family="table-row">
			<style:table-row-properties style:min-row-height="1cm" fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle1.A1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#ffffff" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #00000a" fo:border-right="none" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.B1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#ffffff" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #00000a">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.2" style:family="table-row">
			<style:table-row-properties style:min-row-height="0.501cm" fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle3" style:family="table">
			<style:table-properties style:width="16.452cm" fo:margin-left="-0.199cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:shadow="none" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="Tabelle3.A" style:family="table-column">
			<style:table-column-properties style:column-width="6.9cm"/>
		</style:style>
		<style:style style:name="Tabelle3.B" style:family="table-column">
			<style:table-column-properties style:column-width="2.096cm"/>
		</style:style>
		<style:style style:name="Tabelle3.D" style:family="table-column">
			<style:table-column-properties style:column-width="1cm"/>
		</style:style>
		<style:style style:name="Tabelle3.F" style:family="table-column">
			<style:table-column-properties style:column-width="1.799cm"/>
		</style:style>
		<style:style style:name="Tabelle3.G" style:family="table-column">
			<style:table-column-properties style:column-width="1.561cm"/>
		</style:style>
		<style:style style:name="Tabelle3.1" style:family="table-row">
			<style:table-row-properties fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle3.A1" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="middle" fo:background-color="#ffffff" fo:padding="0.101cm" fo:border-left="0.5pt solid #00000a" fo:border-right="none" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle3.G1" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="middle" fo:background-color="#ffffff" fo:padding="0.101cm" fo:border="0.5pt solid #00000a">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle3.A2" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="middle" fo:background-color="#ffffff" fo:padding="0.101cm" fo:border-left="0.5pt solid #00000a" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle3.G2" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="middle" fo:background-color="#ffffff" fo:padding="0.101cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle4" style:family="table">
			<style:table-properties style:width="16.443cm" fo:margin-left="-0.199cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:shadow="none" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="Tabelle4.A" style:family="table-column">
			<style:table-column-properties style:column-width="3.69cm"/>
		</style:style>
		<style:style style:name="Tabelle4.B" style:family="table-column">
			<style:table-column-properties style:column-width="12.753cm"/>
		</style:style>
		<style:style style:name="Tabelle4.1" style:family="table-row">
			<style:table-row-properties fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle4.A1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.199cm" fo:border-left="0.5pt solid #00000a" fo:border-right="none" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a"/>
		</style:style>
		<style:style style:name="Tabelle4.B1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.199cm" fo:border="0.5pt solid #00000a"/>
		</style:style>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Header">
			<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Footer">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="8.001cm" style:type="center"/>
					<style:tab-stop style:position="15.998cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="11pt" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="end" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="11pt" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="11pt" officeooo:rsid="00026b08" officeooo:paragraph-rsid="00026b08" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Header" style:master-page-name="Standard">
			<style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.423cm" loext:contextual-spacing="false" style:page-number="auto">
				<style:tab-stops>
					<style:tab-stop style:position="7.502cm"/>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="14.753cm"/>
					<style:tab-stop style:position="15.503cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="14pt" officeooo:paragraph-rsid="0004ac29" style:font-size-asian="14pt" style:font-size-complex="14pt"/>
		</style:style>
		<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Header">
			<style:paragraph-properties fo:margin-top="0.494cm" fo:margin-bottom="0.423cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="7.502cm"/>
					<style:tab-stop style:position="9.502cm"/>
					<style:tab-stop style:position="14.753cm"/>
					<style:tab-stop style:position="15.503cm"/>
					<style:tab-stop style:position="16.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="8pt" officeooo:rsid="0004ac29" officeooo:paragraph-rsid="0004ac29" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="11pt" officeooo:rsid="00026b08" officeooo:paragraph-rsid="00026b08" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="11pt" officeooo:rsid="00026b08" officeooo:paragraph-rsid="0004ac29" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="11pt" officeooo:paragraph-rsid="00026b08" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="11pt" officeooo:rsid="00077a90" officeooo:paragraph-rsid="00077a90" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" officeooo:paragraph-rsid="0004ac29" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" officeooo:rsid="0004ac29" officeooo:paragraph-rsid="0004ac29" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="11pt" officeooo:rsid="0004ac29" officeooo:paragraph-rsid="0004ac29" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
		</style:style>
		<style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="1.707cm"/>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="7pt" officeooo:rsid="0008e4d3" officeooo:paragraph-rsid="0008e4d3" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
		</style:style>
		<style:style style:name="P20" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="1.499cm"/>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="7pt" officeooo:rsid="0008e4d3" officeooo:paragraph-rsid="000a4a46" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
		</style:style>
		<style:style style:name="P21" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="00065723" officeooo:paragraph-rsid="00065723" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P22" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="0005e38f" officeooo:paragraph-rsid="0005e38f" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P23" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="0005e38f" officeooo:paragraph-rsid="0005e38f" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P24" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="0005e38f" officeooo:paragraph-rsid="00065d8b" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P25" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="8pt" fo:font-weight="bold" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P26" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="8pt" officeooo:rsid="0005e38f" officeooo:paragraph-rsid="0005e38f" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P27" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="8pt" officeooo:rsid="0005e38f" officeooo:paragraph-rsid="0005e38f" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P28" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="8pt" officeooo:rsid="00065d8b" officeooo:paragraph-rsid="00065d8b" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P29" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="6.752cm"/>
					<style:tab-stop style:position="13.002cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="8pt" officeooo:rsid="0008e4d3" officeooo:paragraph-rsid="0008e4d3" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="T1" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T2" style:family="text">
			<style:text-properties fo:font-size="9pt" officeooo:rsid="00026b08" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T3" style:family="text">
			<style:text-properties fo:font-size="9pt" officeooo:rsid="0005e38f" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T4" style:family="text">
			<style:text-properties officeooo:rsid="00026b08"/>
		</style:style>
		<style:style style:name="T5" style:family="text">
			<style:text-properties officeooo:rsid="0004ac29"/>
		</style:style>
		<style:style style:name="T6" style:family="text">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T7" style:family="text">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="0008e4d3" style:font-size-asian="10pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T8" style:family="text">
			<style:text-properties officeooo:rsid="00065723"/>
		</style:style>
		<style:style style:name="T9" style:family="text">
			<style:text-properties officeooo:rsid="000a4a46"/>
		</style:style>
		<style:style style:name="T10" style:family="text">
			<style:text-properties officeooo:rsid="000a6e9b"/>
		</style:style>
		<style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
			<style:graphic-properties fo:margin-left="0.318cm" fo:margin-right="0.318cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" draw:fill="none" draw:fill-color="#ffffff" fo:padding="0cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
		</style:style>
	</office:automatic-styles>
	<office:body>
		<xsl:apply-templates select="studienerfolg"/>
	</office:body>
</office:document-content>
</xsl:template>

<xsl:template match="studienerfolg">
		<office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
			</text:sequence-decls>
			<text:p text:style-name="P9">
				<text:span text:style-name="T5">Bestätigung des Studienerfolges</text:span>
			</text:p>
			<table:table table:name="Tabelle1" table:style-name="Tabelle1">
				<table:table-column table:style-name="Tabelle1.A"/>
				<table:table-column table:style-name="Tabelle1.B"/>
				<table:table-column table:style-name="Tabelle1.C"/>
				<table:table-row table:style-name="Tabelle1.1">
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P8">Familienname, Vorname</text:p>
						<text:p text:style-name="P14"><xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" /></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
						<text:p text:style-name="P16">Geburtsdatum</text:p>
						<text:p text:style-name="P12"><xsl:value-of select="gebdatum" /></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
						<text:p text:style-name="P8">Personenkennzeichen</text:p>
						<text:p text:style-name="P7"><xsl:value-of select="matrikelnr" /></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row table:style-name="Tabelle1.2">
					<table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P8">Studiengang</text:p>
						<text:p text:style-name="P7"><xsl:value-of select="studiengang_typ" /><xsl:text> </xsl:text>
						<xsl:choose>
							<xsl:when test="studiengang_bezeichnung_sto=''">
								<xsl:value-of select="studiengang" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="studiengang_bezeichnung_sto" />
							</xsl:otherwise>
						</xsl:choose>
						</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
					<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
						<text:p text:style-name="P16">Kennzahl</text:p>
						<text:p text:style-name="P12"><xsl:value-of select="studiengang_kz" /></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row table:style-name="Tabelle1.2">
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P17">Aktuelles Studiensemester</text:p>
						<text:p text:style-name="P13">
							<text:span text:style-name="T4"><xsl:value-of select="studiensemester_aktuell" /></text:span>
						</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B1" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P17">Aktuelles Ausbildungssemester</text:p>
						<text:p text:style-name="P7"><xsl:value-of select="semester_aktuell" /></text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
			</table:table>
			<text:p text:style-name="P6"/>
			<text:p text:style-name="P6"/>
			<text:p text:style-name="P18">Im Studiensemester <xsl:value-of select="studiensemester_kurzbz" /> wurden folgende Lehrveranstaltungen erfolgreich absolviert:</text:p>
			<text:p text:style-name="P3"/>
			<table:table table:name="Tabelle3" table:style-name="Tabelle3">
				<table:table-column table:style-name="Tabelle3.A"/>
				<table:table-column table:style-name="Tabelle3.B" table:number-columns-repeated="2"/>
				<table:table-column table:style-name="Tabelle3.D" table:number-columns-repeated="2"/>
				<table:table-column table:style-name="Tabelle3.F"/>
				<table:table-column table:style-name="Tabelle3.G"/>
				<table:table-row table:style-name="Tabelle3.1">
					<table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
						<text:p text:style-name="P21">Lehrveranstaltung</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
						<text:p text:style-name="P24">Studien-</text:p>
						<text:p text:style-name="P24">semester</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
						<text:p text:style-name="P24">Ausbildungs-semester</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
						<text:p text:style-name="P23">SWS</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
						<text:p text:style-name="P23">ECTS</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
						<text:p text:style-name="P23">D<text:span text:style-name="T8">a</text:span>tum</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.G1" office:value-type="string">
						<text:p text:style-name="P23">Benotung</text:p>
					</table:table-cell>
				</table:table-row>

				<xsl:apply-templates select="unterrichtsfach"/>

				<table:table-row table:style-name="Tabelle3.1">
					<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
						<text:p text:style-name="P22">Summe:</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
						<text:p text:style-name="P23"></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
						<text:p text:style-name="P25"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
						<text:p text:style-name="P23"><xsl:value-of select="gesamtstunden_lv_positiv" /></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
						<text:p text:style-name="P23"><xsl:value-of select="gesamtects_positiv" /></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
						<text:p text:style-name="P23">Schnitt:</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.G2" office:value-type="string">
						<text:p text:style-name="P23"><xsl:value-of select="schnitt_positiv" /></text:p>
					</table:table-cell>
				</table:table-row>
			</table:table>
			<text:p text:style-name="P3"/>
			<text:p text:style-name="P3"/>
			<text:p text:style-name="P3"/>
			<table:table table:name="Tabelle4" table:style-name="Tabelle4">
				<table:table-column table:style-name="Tabelle4.A"/>
				<table:table-column table:style-name="Tabelle4.B"/>
				<table:table-row table:style-name="Tabelle4.1">
					<table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
						<text:p text:style-name="P4">
							<text:span text:style-name="T1">Datum: </text:span>
							<text:span text:style-name="T3"><xsl:value-of select="datum" /></text:span>
						</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.B1" office:value-type="string">
						<text:p text:style-name="P5">
						</text:p>
					</table:table-cell>
				</table:table-row>
			</table:table>
			<text:p text:style-name="P19"/>
		</office:text>
</xsl:template>
<xsl:template match="unterrichtsfach">
	<xsl:if test="note_positiv='1'">
		<table:table-row table:style-name="Tabelle3.1">
			<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
				<text:p text:style-name="P26"><xsl:value-of select="bezeichnung" /></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
				<text:p text:style-name="P27"><xsl:value-of select="../studiensemester_kurzbz" /></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
				<text:p text:style-name="P27"><xsl:value-of select="../semester" /></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
				<text:p text:style-name="P27"><xsl:value-of select="sws_lv" /></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
				<text:p text:style-name="P27"><xsl:value-of select="ects" /></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
				<text:p text:style-name="P27"><xsl:value-of select="benotungsdatum" /></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle3.G2" office:value-type="string">
				<text:p text:style-name="P27"><xsl:value-of select="note" /></text:p>
			</table:table-cell>
		</table:table-row>
	</xsl:if>
</xsl:template>
</xsl:stylesheet>
