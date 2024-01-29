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
<xsl:template match="lehrauftraege">

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
		<style:font-face style:name="Arial1" svg:font-family="Arial" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Helvetica" svg:font-family="Helvetica" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="0" svg:font-family="0" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Arial2" svg:font-family="Arial" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Sans1" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Serif1" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Mangal1" svg:font-family="Mangal" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Microsoft YaHei" svg:font-family="&apos;Microsoft YaHei&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="SimSun" svg:font-family="SimSun" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Tahoma1" svg:font-family="Tahoma" style:font-family-generic="system" style:font-pitch="variable"/>
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="Tabelle1" style:family="table">
			<style:table-properties style:width="17cm" table:align="margins"/>
		</style:style>
		<style:style style:name="Tabelle1.A" style:family="table-column">
			<style:table-column-properties style:column-width="1.499cm" style:rel-column-width="850*"/>
		</style:style>
		<style:style style:name="Tabelle1.B" style:family="table-column">
			<style:table-column-properties style:column-width="5.595cm" style:rel-column-width="3172*"/>
		</style:style>
		<style:style style:name="Tabelle1.C" style:family="table-column">
			<style:table-column-properties style:column-width="3.704cm" style:rel-column-width="2100*"/>
		</style:style>
		<style:style style:name="Tabelle1.D" style:family="table-column">
			<style:table-column-properties style:column-width="1.997cm" style:rel-column-width="1132*"/>
		</style:style>
		<style:style style:name="Tabelle1.E" style:family="table-column">
			<style:table-column-properties style:column-width="1.298cm" style:rel-column-width="736*"/>
		</style:style>
		<style:style style:name="Tabelle1.F" style:family="table-column">
			<style:table-column-properties style:column-width="1.011cm" style:rel-column-width="573*"/>
		</style:style>
		<style:style style:name="Tabelle1.G" style:family="table-column">
			<style:table-column-properties style:column-width="1.894cm" style:rel-column-width="1074*"/>
		</style:style>
		<style:style style:name="Tabelle1.A1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
        <style:style style:name="Tabelle1.A1_2" style:family="table-cell">
            <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
        </style:style>
		<style:style style:name="Tabelle1.G1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.A2" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.07cm" fo:padding-right="0.07cm" fo:padding-top="0.05cm" fo:padding-bottom="0.05cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
        <style:style style:name="Tabelle1.A2_2" style:family="table-cell">
            <style:table-cell-properties fo:padding-left="0.07cm" fo:padding-right="0.07cm" fo:padding-top="0.05cm" fo:padding-bottom="0.05cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
        </style:style>
		<style:style style:name="Tabelle1.G2" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.07cm" fo:padding-right="0.07cm" fo:padding-top="0.05cm" fo:padding-bottom="0.05cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2" style:family="table">
			<style:table-properties style:width="17cm" table:align="margins" style:shadow="none" fo:keep-with-next="always"/>
		</style:style>
		<style:style style:name="Tabelle2.A" style:family="table-column">
			<style:table-column-properties style:column-width="6.001cm" style:rel-column-width="3402*"/>
		</style:style>
		<style:style style:name="Tabelle2.B" style:family="table-column">
			<style:table-column-properties style:column-width="3cm" style:rel-column-width="1701*"/>
		</style:style>
		<style:style style:name="Tabelle2.C" style:family="table-column">
			<style:table-column-properties style:column-width="7.999cm" style:rel-column-width="4535*"/>
		</style:style>
		<style:style style:name="Tabelle2.1" style:family="table-row">
			<style:table-row-properties style:min-row-height="1.499cm"/>
		</style:style>
		<style:style style:name="Tabelle2.A1" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="bottom" fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="1pt dotted #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.B1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
		</style:style>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="001fea3c" officeooo:paragraph-rsid="001fea3c" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="0013c612" officeooo:paragraph-rsid="0013c612" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="002ab151" officeooo:paragraph-rsid="002ab151" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="002c2d13" officeooo:paragraph-rsid="002c2d13" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="001fea3c" officeooo:paragraph-rsid="001fea3c" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="0021faff" officeooo:paragraph-rsid="0021faff" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="7.001cm" style:leader-style="solid" style:leader-text="_"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:language="de" fo:country="AT" style:text-underline-style="dotted" style:text-underline-width="bold" style:text-underline-color="font-color" officeooo:rsid="0013c612" officeooo:paragraph-rsid="002ab151" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P15" style:family="paragraph" style:parent-style-name="Header">
			<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" officeooo:rsid="00220045" officeooo:paragraph-rsid="00220045"/>
		</style:style>
		<style:style style:name="P16" style:family="paragraph" style:parent-style-name="Header">
			<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00220045" officeooo:paragraph-rsid="00220045" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="14pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="12.25pt" style:font-size-complex="14pt"/>
		</style:style>
		<style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="12pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="12pt" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P20" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="0021faff" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P21" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P22" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:language="en" fo:country="US" officeooo:rsid="001ece18" officeooo:paragraph-rsid="0021faff" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P23" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:language="en" fo:country="US" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P24" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="7.001cm" style:leader-style="solid" style:leader-text="_"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:language="de" fo:country="AT" officeooo:rsid="001ece18" officeooo:paragraph-rsid="0021faff" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P25" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="001ece18" officeooo:paragraph-rsid="00220045" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P26" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="7.001cm" style:leader-style="solid" style:leader-text="_"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:language="de" fo:country="AT" style:text-underline-style="dotted" style:text-underline-width="bold" style:text-underline-color="font-color" officeooo:rsid="001ece18" officeooo:paragraph-rsid="0021faff" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P27" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-align="end" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="7.001cm" style:leader-style="solid" style:leader-text="_"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:language="de" fo:country="AT" style:text-underline-style="dotted" style:text-underline-width="bold" style:text-underline-color="font-color" officeooo:rsid="001ece18" officeooo:paragraph-rsid="0021faff" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P28" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-align="end" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="7.001cm" style:leader-style="solid" style:leader-text="_"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:language="de" fo:country="AT" style:text-underline-style="dotted" style:text-underline-width="bold" style:text-underline-color="font-color" officeooo:rsid="002ab151" officeooo:paragraph-rsid="002ab151" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P29" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false">
				<style:tab-stops>
					<style:tab-stop style:position="7.001cm" style:leader-style="solid" style:leader-text="_"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:language="de" fo:country="AT" officeooo:rsid="001ece18" officeooo:paragraph-rsid="0021faff" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P30" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-align="end" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="7.001cm" style:leader-style="solid" style:leader-text="_"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="0021faff" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P31" style:family="paragraph" style:parent-style-name="Standard" style:master-page-name="">
			<loext:graphic-properties draw:fill="none"/>
			<style:paragraph-properties fo:margin-left="0.3cm" fo:margin-right="8.999cm" fo:text-indent="0cm" style:auto-text-indent="false" style:page-number="auto"/>
			<style:text-properties style:font-name="Arial" fo:font-size="12pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="12pt" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="Seitenumbruch" style:family="paragraph" style:parent-style-name="Standard" style:master-page-name="">
			<style:paragraph-properties fo:break-before="page"/>
			<loext:graphic-properties draw:fill="none"/>
			<style:paragraph-properties fo:margin-left="0.3cm" fo:margin-right="8.999cm" fo:text-indent="0cm" style:auto-text-indent="false" style:page-number="auto"/>
			<style:text-properties style:font-name="Arial" fo:font-size="12pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="12pt" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="P32" style:family="paragraph" style:parent-style-name="Standard">
			<loext:graphic-properties draw:fill="none"/>
			<style:paragraph-properties fo:margin-left="0.3cm" fo:margin-right="8.999cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="12pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="12pt" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="P33" style:family="paragraph" style:parent-style-name="Standard">
			<loext:graphic-properties draw:fill="none"/>
			<style:paragraph-properties fo:margin-left="0.3cm" fo:margin-right="8.999cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="14pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="12.25pt" style:font-size-complex="14pt"/>
		</style:style>
		<style:style style:name="P34" style:family="paragraph" style:parent-style-name="Standard">
			<loext:graphic-properties draw:fill="none"/>
			<style:paragraph-properties fo:margin-left="0.3cm" fo:margin-right="8.999cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="7pt" officeooo:rsid="001ece18" officeooo:paragraph-rsid="001ece18" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
		</style:style>
		<style:style style:name="P35" style:family="paragraph">
			<loext:graphic-properties draw:fill-color="#999999"/>
			<style:paragraph-properties fo:text-align="center"/>
		</style:style>
		<style:style style:name="T1" style:family="text">
			<style:text-properties officeooo:rsid="001f0457"/>
		</style:style>
		<style:style style:name="T2" style:family="text">
			<style:text-properties officeooo:rsid="001fb2b6"/>
		</style:style>
		<style:style style:name="T3" style:family="text">
			<style:text-properties officeooo:rsid="001fea3c"/>
		</style:style>
		<style:style style:name="T4" style:family="text">
			<style:text-properties fo:language="en" fo:country="US"/>
		</style:style>
		<style:style style:name="T5" style:family="text">
			<style:text-properties officeooo:rsid="00220045"/>
		</style:style>
		<style:style style:name="T6" style:family="text">
			<style:text-properties officeooo:rsid="0023d767"/>
		</style:style>
		<style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
			<style:graphic-properties fo:margin-left="0.318cm" fo:margin-right="0.318cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" draw:fill="none" draw:fill-color="#ffffff" fo:padding="0cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
		</style:style>
		<style:style style:name="fr2" style:family="graphic" style:parent-style-name="Graphics">
			<style:graphic-properties style:run-through="foreground" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="char" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
		</style:style>
		<style:style style:name="gr1" style:family="graphic">
			<style:graphic-properties svg:stroke-color="#b2b2b2" draw:fill-color="#b2b2b2" draw:textarea-horizontal-align="center" draw:textarea-vertical-align="middle" style:protect="position size" style:run-through="foreground" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="page" style:horizontal-pos="from-left" style:horizontal-rel="page"/>
		</style:style>
	</office:automatic-styles>
	<office:body>
		<xsl:apply-templates select="lehrauftrag"/>
		<!-- Add extra page to avoid problems with libreoffice 6 -->
		<office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">
			<text:p text:style-name="Seitenumbruch"/>
		</office:text>
	</office:body>
</office:document-content>
</xsl:template>

<xsl:template match="lehrauftrag">
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
				<xsl:for-each select="../lehrauftrag">
					<xsl:variable select="position()" name="number"/><!-- Variable number definieren, die nach jedem Dokument um eines erhöht wird (position) -->
					<draw:line text:anchor-type="page" text:anchor-page-number="{$number}" draw:z-index="1" draw:style-name="gr{$number}" draw:text-style-name="P35" svg:x1="1.199cm" svg:y1="9.601cm" svg:x2="0.7cm" svg:y2="9.601cm">
						<text:p/>
					</draw:line>
					<draw:line text:anchor-type="page" text:anchor-page-number="{$number}" draw:z-index="2" draw:style-name="gr{$number}" draw:text-style-name="P35" svg:x1="1.199cm" svg:y1="14.101cm" svg:x2="0.7cm" svg:y2="14.101cm">
						<text:p/>
					</draw:line>
				</xsl:for-each>
			</xsl:if>
			<draw:line text:anchor-type="page" text:anchor-page-number="1" draw:z-index="1" draw:style-name="gr1" draw:text-style-name="P35" svg:x1="1.199cm" svg:y1="9.601cm" svg:x2="0.7cm" svg:y2="9.601cm">
				<text:p/>
			</draw:line>
			<draw:line text:anchor-type="page" text:anchor-page-number="1" draw:z-index="2" draw:style-name="gr1" draw:text-style-name="P35" svg:x1="1.199cm" svg:y1="14.101cm" svg:x2="0.7cm" svg:y2="14.101cm">
				<text:p/>
			</draw:line>
			<!--<text:p text:style-name="P31"/>-->
			<text:p text:style-name="Seitenumbruch"/>
			<text:p text:style-name="P32"/>
			<text:p text:style-name="P32"><xsl:value-of select="mitarbeiter/name_gesamt" /></text:p>
			<text:p text:style-name="P32"><xsl:value-of select="mitarbeiter/anschrift" /></text:p>
			<text:p text:style-name="P32"><xsl:value-of select="mitarbeiter/plz" /><xsl:text> </xsl:text><xsl:value-of select="mitarbeiter/ort" /></text:p>
			<xsl:if test="string-length(mitarbeiter/zuhanden)!=0">
				<text:p text:style-name="P32"><xsl:value-of select="mitarbeiter/zuhanden" /></text:p>
			</xsl:if>
			<text:p text:style-name="P33"/>
			<text:p text:style-name="P33"/>
			<text:p text:style-name="P17"/>
			<text:p text:style-name="P17"/>
			<text:p text:style-name="P25">Lehrauftrag
				<xsl:if test="studiengang_typ!=''">
					<xsl:value-of select="studiengang_typ" />-Studiengang <xsl:value-of select="studiengang_bezeichnung" /><xsl:text> </xsl:text>
				</xsl:if>
			<xsl:value-of select="studiensemester_kurzbz" /></text:p>
			<text:p text:style-name="P19"/>
			<text:p text:style-name="P21"><xsl:value-of select="mitarbeiter/name_gesamt" /></text:p>
			<text:p text:style-name="P19">SV.Nr.: <xsl:value-of select="mitarbeiter/svnr" /></text:p>
			<text:p text:style-name="P19">Personalnummer: <xsl:value-of select="mitarbeiter/personalnummer" /></text:p>
			<text:p text:style-name="P19"/>
			<text:p text:style-name="P19"/>
			<text:p text:style-name="P19"/>
			<table:table table:name="Tabelle1" table:style-name="Tabelle1">
				<table:table-column table:style-name="Tabelle1.A"/>
				<table:table-column table:style-name="Tabelle1.B"/>
				<table:table-column table:style-name="Tabelle1.C"/>
				<table:table-column table:style-name="Tabelle1.D"/>
				<table:table-column table:style-name="Tabelle1.E"/>
                <!-- Wenn LektorInnen bei inkludierte_lehre einen Wert stehen haben, werden die Spalten "Satz" und "Brutto" nicht angezeigt -->
                <xsl:choose>
                    <xsl:when test="mitarbeiter/inkludierte_lehre != ''">
                    </xsl:when>
                    <xsl:otherwise>
                        <table:table-column table:style-name="Tabelle1.F"/>
                        <table:table-column table:style-name="Tabelle1.G"/>
                    </xsl:otherwise>
                </xsl:choose>

				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P12">ID</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P10">Lehrveranstaltung</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P10">Department</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
						<text:p text:style-name="P10">Gruppe<text:span text:style-name="T2">(n)</text:span>
						</text:p>
					</table:table-cell>
                    <!-- Wenn LektorInnen bei inkludierte_lehre einen Wert stehen haben, wird die Spalte "Satz" nicht angezeigt
                        Deshalb braucht die Spalte "Std" einen border auf der rechten Seite (deshalb anderer style_name) -->
                    <xsl:choose>
                        <xsl:when test="mitarbeiter/inkludierte_lehre != ''">
                            <table:table-cell table:style-name="Tabelle1.A1_2" office:value-type="string">
                                <text:p text:style-name="P13">Std.</text:p>
                            </table:table-cell>
                        </xsl:when>
                        <xsl:otherwise>
                            <table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
                                <text:p text:style-name="P13">Std.</text:p>
                            </table:table-cell>
                        </xsl:otherwise>
                    </xsl:choose>
					<!-- Wenn LektorInnen bei inkludierte_lehre einen Wert stehen haben, werden die Spalten "Satz" und "Brutto" nicht angezeigt -->
					<xsl:choose>
						<xsl:when test="mitarbeiter/inkludierte_lehre != ''">
							<!--<text:p text:style-name="P10">Satz</text:p>-->
						</xsl:when>
						<xsl:otherwise>
							<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
								<text:p text:style-name="P10">Satz</text:p>
							</table:table-cell>
							<table:table-cell table:style-name="Tabelle1.G1" office:value-type="string">
								<text:p text:style-name="P10">Brutto</text:p>
							</table:table-cell>
						</xsl:otherwise>
					</xsl:choose>
				</table:table-row>

				<xsl:apply-templates select="lehreinheit"/>

				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P1"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P1"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P1"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
						<text:p text:style-name="P11">Summe:</text:p>
					</table:table-cell>
                    <!-- Wenn LektorInnen bei inkludierte_lehre einen Wert stehen haben, wird die Spalte "Satz" nicht angezeigt
                       Deshalb braucht die Spalte "Summe" einen border auf der rechten Seite (deshalb anderer style_name) -->
                    <xsl:choose>
                        <xsl:when test="mitarbeiter/inkludierte_lehre != ''">
                            <table:table-cell table:style-name="Tabelle1.A2_2" office:value-type="string">
                                <text:p text:style-name="P11"><xsl:value-of select="gesamtstunden" /></text:p>
                            </table:table-cell>
                        </xsl:when>
                        <xsl:otherwise>
                            <table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
                                <text:p text:style-name="P11"><xsl:value-of select="gesamtstunden" /></text:p>
                            </table:table-cell>
                        </xsl:otherwise>
                    </xsl:choose>

					<!-- Wenn LektorInnen bei inkludierte_lehre einen Wert stehen haben, wird die Spalte Bruttosumme nicht angezeigt -->
					<xsl:choose>
						<xsl:when test="mitarbeiter/inkludierte_lehre != ''">
							<!--<text:p text:style-name="P2">0.00</text:p>-->
						</xsl:when>
						<xsl:otherwise>
                            <table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
                                <text:p text:style-name="P9"/>
                            </table:table-cell>
                            <table:table-cell table:style-name="Tabelle1.G2" office:value-type="string">
								<text:p text:style-name="P11">€ <xsl:value-of select="gesamtbetrag" /></text:p>
							</table:table-cell>
						</xsl:otherwise>
					</xsl:choose>
				</table:table-row>
			</table:table>
			<text:p text:style-name="P19"/>
			<table:table table:name="Tabelle2" table:style-name="Tabelle2">
				<table:table-column table:style-name="Tabelle2.A"/>
				<table:table-column table:style-name="Tabelle2.B"/>
				<table:table-column table:style-name="Tabelle2.C"/>
				<table:table-row table:style-name="Tabelle2.1">
					<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
						<text:p text:style-name="P7">
							<draw:frame draw:style-name="fr2" draw:name="graphics4" text:anchor-type="char" svg:x="6.301cm" svg:y="-0.5cm" svg:width="2.2cm" svg:height="2.2cm" draw:z-index="3">
								<draw:image xlink:href="Pictures/100002010000015A0000015A8CD841D1.png" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
							</draw:frame>Wien, am <xsl:value-of select="datum" />
						</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle2.B1" office:value-type="string">
						<text:p text:style-name="P7"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
						<text:p text:style-name="P1"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle2.B1" office:value-type="string">
						<text:p text:style-name="P7">Ort, Datum</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle2.B1" office:value-type="string">
						<text:p text:style-name="P7"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle2.B1" office:value-type="string">
						<text:p text:style-name="P8"><xsl:value-of select="studiengangsleiter" /></text:p>
						<text:p text:style-name="P7">Studiengangsleitung</text:p>
					</table:table-cell>
				</table:table-row>
			</table:table>
			<text:p text:style-name="P20"/>
		</office:text>
</xsl:template>
<xsl:template match="lehreinheit">
	<table:table-row>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P2"><xsl:value-of select="lehreinheit_id" /></text:p>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P2"><xsl:value-of select="lehrveranstaltung" /></text:p>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P2">
				<xsl:choose>
					<xsl:when test="string-length(fachbereich)>28">
						<xsl:value-of select="substring(fachbereich,0,25)" /><xsl:text>...</xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="fachbereich" />
					</xsl:otherwise>
				</xsl:choose>
			</text:p>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">

			<xsl:apply-templates select="gruppen_getrennt"/>

		</table:table-cell>
        <!-- Wenn LektorInnen bei inkludierte_lehre einen Wert stehen haben, wird die Spalte "Satz" nicht angezeigt
                Deshalb braucht die Spalte "Std" einen border auf der rechten Seite (deshalb anderer style_name) -->
        <xsl:choose>
            <xsl:when test="../mitarbeiter/inkludierte_lehre != ''">
                <table:table-cell table:style-name="Tabelle1.A2_2" office:value-type="string">
                    <text:p text:style-name="P3"><xsl:value-of select="stunden" /></text:p>
                </table:table-cell>
            </xsl:when>
            <xsl:otherwise>
                <table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
                    <text:p text:style-name="P3"><xsl:value-of select="stunden" /></text:p>
                 </table:table-cell>
            </xsl:otherwise>
        </xsl:choose>

		<!-- Wenn LektorInnen bei inkludierte_lehre einen Wert stehen haben, wird die Spalte "Satz" nicht angezeigt -->
		<xsl:choose>
			<xsl:when test="../mitarbeiter/inkludierte_lehre != ''">
				<!--<text:p text:style-name="P2">0.00</text:p>-->
			</xsl:when>
			<xsl:otherwise>
				<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
					<text:p text:style-name="P2"><xsl:value-of select="satz" /></text:p>
				</table:table-cell>
			</xsl:otherwise>
		</xsl:choose>

		<!-- Wenn LektorInnen bei inkludierte_lehre einen Wert stehen haben, wird die Spalte "Brutto" nicht angezeigt -->
		<xsl:choose>
			<xsl:when test="../mitarbeiter/inkludierte_lehre != ''">
				<!--<text:p text:style-name="P3">€ 0,00</text:p>-->
			</xsl:when>
			<xsl:otherwise>
				<table:table-cell table:style-name="Tabelle1.G2" office:value-type="string">
					<text:p text:style-name="P3">€ <xsl:value-of select="brutto" /></text:p>
				</table:table-cell>
			</xsl:otherwise>
		</xsl:choose>
	</table:table-row>
</xsl:template>

<xsl:template match="gruppen_getrennt">
	<xsl:apply-templates select="einzelgruppe"/>
</xsl:template>

<xsl:template match="einzelgruppe">
	<text:p text:style-name="P5"><xsl:value-of select="." /></text:p>
</xsl:template>

</xsl:stylesheet>
