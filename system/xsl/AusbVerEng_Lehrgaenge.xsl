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
<xsl:template match="ausbildungsvertraege">

<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
	<office:scripts/>
	<office:font-face-decls>
		<style:font-face style:name="OpenSymbol" svg:font-family="OpenSymbol" style:font-charset="x-symbol"/>
		<style:font-face style:name="Wingdings" svg:font-family="Wingdings" style:font-pitch="variable" style:font-charset="x-symbol"/>
		<style:font-face style:name="Symbol" svg:font-family="Symbol" style:font-family-generic="roman" style:font-pitch="variable" style:font-charset="x-symbol"/>
		<style:font-face style:name="Mangal1" svg:font-family="Mangal"/>
		<style:font-face style:name="Courier New" svg:font-family="&apos;Courier New&apos;" style:font-family-generic="modern"/>
		<style:font-face style:name="Lucida Grande" svg:font-family="&apos;Lucida Grande&apos;, &apos;Times New Roman&apos;" style:font-family-generic="roman"/>
		<style:font-face style:name="ヒラギノ角ゴ Pro W3" svg:font-family="&apos;ヒラギノ角ゴ Pro W3&apos;" style:font-family-generic="roman"/>
		<style:font-face style:name="Courier New1" svg:font-family="&apos;Courier New&apos;" style:font-family-generic="modern" style:font-pitch="fixed"/>
		<style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Symbol1" svg:font-family="Symbol" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Times New Roman" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Optima" svg:font-family="Optima" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Microsoft YaHei" svg:font-family="&apos;Microsoft YaHei&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="SimSun" svg:font-family="SimSun" style:font-family-generic="system" style:font-pitch="variable"/>
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="Tabelle1" style:family="table">
			<style:table-properties style:width="15.252cm" table:align="left" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="Tabelle1.A" style:family="table-column">
			<style:table-column-properties style:column-width="7.001cm"/>
		</style:style>
		<style:style style:name="Tabelle1.B" style:family="table-column">
			<style:table-column-properties style:column-width="1.713cm"/>
		</style:style>
		<style:style style:name="Tabelle1.C" style:family="table-column">
			<style:table-column-properties style:column-width="6.539cm"/>
		</style:style>
		<style:style style:name="Tabelle1.1" style:family="table-row">
			<style:table-row-properties fo:keep-together="auto"/>
		</style:style>
		<style:style style:name="Tabelle1.A1" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.5pt dotted #000000" fo:border-bottom="0.5pt dotted #000000" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="Tabelle1.B1" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border="none" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="Tabelle1.A2" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.5pt dotted #000000" fo:border-bottom="none" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="0cm"/>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="0cm"/>
					<style:tab-stop style:position="0.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="start" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:language="de" fo:country="AT"/>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="start" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001534a7"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001892c5" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:language-asian="zxx" style:country-asian="none" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P20" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001892c5"/>
		</style:style>
		<style:style style:name="P21" style:family="paragraph" style:parent-style-name="First_5f_20_5f_Page">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P22" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P23" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P24" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P25" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
					<style:tab-stop style:position="14.503cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P26" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P27" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
					<style:tab-stop style:position="14.503cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P28" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
					<style:tab-stop style:position="14.503cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P29" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P30" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P31" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0.416cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P32" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0.416cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P33" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P34" style:family="paragraph" style:parent-style-name="Textkörper_20_3">
			<style:paragraph-properties fo:line-height="130%" fo:orphans="0" fo:widows="0"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P35" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P36" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P37" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P38" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e88af" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P39" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="10pt" officeooo:paragraph-rsid="00207396" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P40" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" officeooo:paragraph-rsid="001e88af" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P41" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%">
				<style:tab-stops>
					<style:tab-stop style:position="8.752cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P42" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" fo:font-weight="bold" officeooo:paragraph-rsid="00207396" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="P43" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="P44" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:language-asian="zxx" style:country-asian="none" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P45" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00207396" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:language-asian="zxx" style:country-asian="none" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P46" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00207396" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:language-asian="zxx" style:country-asian="none" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P47" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001c5f45" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P48" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001e4e15" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P49" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00207396" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P50" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00213085" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P51" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e4e15" officeooo:paragraph-rsid="001e4e15" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P52" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" officeooo:paragraph-rsid="001c5f45" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P53" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P54" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001e4e15" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P55" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P56" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P57" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00161927" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P58" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00207396" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P59" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00207396" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P60" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00213085" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P61" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="0022c7a9" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P62" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e4e15" officeooo:paragraph-rsid="001e4e15" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P63" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="WW8Num4">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e4e15" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P64" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e4e15" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P65" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="start" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e4e15" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P66" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P67" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P68" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e88af" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P69" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e88af" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P70" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e88af" officeooo:paragraph-rsid="00207396" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P71" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00207396" officeooo:paragraph-rsid="00207396" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P72" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00213085" officeooo:paragraph-rsid="00213085" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P73" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="normal" officeooo:rsid="00207396" officeooo:paragraph-rsid="00207396" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="P74" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="normal" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="P75" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		</style:style>
		<style:style style:name="P76" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties officeooo:paragraph-rsid="001c5f45"/>
		</style:style>
		<style:style style:name="P77" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties officeooo:paragraph-rsid="001e88af"/>
		</style:style>
		<style:style style:name="P78" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties officeooo:paragraph-rsid="001e88af"/>
		</style:style>
		<style:style style:name="P79" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties officeooo:paragraph-rsid="00207396"/>
		</style:style>
		<style:style style:name="P80" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties officeooo:paragraph-rsid="00207396"/>
		</style:style>
		<style:style style:name="P81" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:language="de" fo:country="AT" style:language-asian="ar" style:country-asian="SA" style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="P82" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001e88af"/>
		</style:style>
		<style:style style:name="P83" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="00207396"/>
		</style:style>
		<style:style style:name="P84" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="00207396" fo:background-color="transparent"/>
		</style:style>
		<style:style style:name="P85" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="00213085" fo:background-color="transparent"/>
		</style:style>
		<style:style style:name="P86" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="00213085"/>
		</style:style>
		<style:style style:name="P87" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="page"/>
		</style:style>
		<style:style style:name="P88" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001c5f45" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P89" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00207396" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P90" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e4e15" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P91" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001c5f45" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P92" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00161927" officeooo:paragraph-rsid="00161927" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P93" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P94" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" officeooo:paragraph-rsid="00207396" style:font-name-asian="Arial" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="P95" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="column"/>
			<style:text-properties officeooo:paragraph-rsid="00213085"/>
		</style:style>
		<style:style style:name="P96" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="column"/>
			<style:text-properties officeooo:paragraph-rsid="001534a7"/>
		</style:style>
		<style:style style:name="P97" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00207396" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P98" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P99" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="WW8Num4">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00207396" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P100" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="WW8Num4">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P101" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P102" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e4e15" officeooo:paragraph-rsid="001e4e15" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P105" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e85cb" officeooo:paragraph-rsid="001e85cb" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P106" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P107" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P108" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001e88af" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P111" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P112" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001e4e15" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P113" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e85cb" officeooo:paragraph-rsid="001e85cb" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P114" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties officeooo:paragraph-rsid="001e4e15"/>
		</style:style>
		<style:style style:name="P115" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0.4cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="-0.4cm" style:auto-text-indent="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P116" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P117" style:family="paragraph" style:parent-style-name="Header">
			<style:text-properties fo:language="de" fo:country="AT" style:language-asian="de" style:country-asian="AT"/>
		</style:style>
		<style:style style:name="P118" style:family="paragraph" style:parent-style-name="Heading_20_1" style:list-style-name="" style:master-page-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false" style:page-number="1"/>
			<style:text-properties style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="P119" style:family="paragraph" style:parent-style-name="Heading_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
		</style:style>
		<style:style style:name="T1" style:family="text">
			<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="T2" style:family="text">
			<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T3" style:family="text">
			<style:text-properties fo:font-size="8pt" fo:background-color="transparent" loext:char-shading-value="0" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="T4" style:family="text">
			<style:text-properties fo:font-size="8pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="8pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T5" style:family="text">
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T6" style:family="text">
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T7" style:family="text">
			<style:text-properties fo:font-weight="bold" officeooo:rsid="00213085" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T8" style:family="text">
			<style:text-properties fo:font-weight="bold" officeooo:rsid="00207396" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T9" style:family="text">
			<style:text-properties fo:font-weight="bold" officeooo:rsid="00207396"/>
		</style:style>
		<style:style style:name="T10" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:font-weight="bold" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T11" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:font-weight="bold" style:font-size-asian="10pt" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T12" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="00213085" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T13" style:family="text">
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T14" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T15" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T16" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T17" style:family="text">
			<style:text-properties style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T18" style:family="text">
			<style:text-properties style:font-name-asian="Arial"/>
		</style:style>
		<style:style style:name="T19" style:family="text">
			<style:text-properties fo:language="en" fo:country="US"/>
		</style:style>
		<style:style style:name="T20" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-name-asian="Arial"/>
		</style:style>
		<style:style style:name="T21" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T22" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" officeooo:rsid="00213085" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T23" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-style-complex="italic"/>
		</style:style>
		<style:style style:name="T24" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T25" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T26" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-name-asian="Arial" style:font-size-asian="9pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T27" style:family="text">
			<style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="9pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T28" style:family="text">
			<style:text-properties style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="T29" style:family="text">
			<style:text-properties fo:color="#000000" fo:language="en" fo:country="GB"/>
		</style:style>
		<style:style style:name="T30" style:family="text">
			<style:text-properties fo:color="#000000" fo:language="en" fo:country="GB" style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="T31" style:family="text">
			<style:text-properties fo:color="#000000" fo:language="en" fo:country="US" style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="T32" style:family="text">
			<style:text-properties fo:background-color="transparent" loext:char-shading-value="0"/>
		</style:style>
		<style:style style:name="T33" style:family="text">
			<style:text-properties officeooo:rsid="001534a7"/>
		</style:style>
		<style:style style:name="T34" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T35" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001e4e15" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T36" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" loext:char-shading-value="0" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T37" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" fo:background-color="transparent" loext:char-shading-value="0" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T38" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T39" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="normal" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="T40" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="normal" fo:background-color="transparent" loext:char-shading-value="0" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="T41" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T42" style:family="text">
			<style:text-properties style:language-asian="ar" style:country-asian="SA"/>
		</style:style>
		<style:style style:name="T43" style:family="text">
			<style:text-properties officeooo:rsid="001e88af" style:language-asian="ar" style:country-asian="SA"/>
		</style:style>
		<style:style style:name="T44" style:family="text">
			<style:text-properties fo:font-weight="normal" style:font-weight-asian="normal" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="T45" style:family="text">
			<style:text-properties officeooo:rsid="001c5f45"/>
		</style:style>
		<style:style style:name="T46" style:family="text">
			<style:text-properties officeooo:rsid="001e4e15"/>
		</style:style>
		<style:style style:name="T47" style:family="text">
			<style:text-properties officeooo:rsid="001e88af"/>
		</style:style>
		<style:style style:name="T48" style:family="text">
			<style:text-properties officeooo:rsid="00161927"/>
		</style:style>
		<style:style style:name="T49" style:family="text">
			<style:text-properties officeooo:rsid="00207396"/>
		</style:style>
		<style:style style:name="T50" style:family="text">
			<style:text-properties officeooo:rsid="00213085"/>
		</style:style>
		<style:style style:name="T51" style:family="text">
			<style:text-properties officeooo:rsid="0022c7a9"/>
		</style:style>
		<style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
			<style:graphic-properties fo:margin-left="0.319cm" fo:margin-right="0.319cm" style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:padding="0.002cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
		</style:style>
		<style:style style:name="Sect1" style:family="section">
			<style:section-properties style:writing-mode="lr-tb" style:editable="false">
				<style:columns fo:column-count="2" fo:column-gap="1.27cm">
					<style:column style:rel-width="32767*" fo:start-indent="0cm" fo:end-indent="0.635cm"/>
					<style:column style:rel-width="32768*" fo:start-indent="0.635cm" fo:end-indent="0cm"/>
				</style:columns>
			</style:section-properties>
		</style:style>
		<style:style style:name="Sect2" style:family="section">
			<style:section-properties style:writing-mode="lr-tb" style:editable="false">
				<style:columns fo:column-count="1" fo:column-gap="0cm"/>
			</style:section-properties>
		</style:style>
		<style:style style:name="Sect3" style:family="section">
			<style:section-properties text:dont-balance-text-columns="true" style:writing-mode="lr-tb" style:editable="false">
				<style:columns fo:column-count="1" fo:column-gap="0cm"/>
			</style:section-properties>
		</style:style>
		<text:list-style style:name="L1">
			<text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-0.635cm" fo:margin-left="1.27cm"/>
				</style:list-level-properties>
			</text:list-level-style-bullet>
			<text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.905cm" fo:text-indent="-0.635cm" fo:margin-left="1.905cm"/>
				</style:list-level-properties>
			</text:list-level-style-bullet>
			<text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-0.635cm" fo:margin-left="2.54cm"/>
				</style:list-level-properties>
			</text:list-level-style-bullet>
			<text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.175cm" fo:text-indent="-0.635cm" fo:margin-left="3.175cm"/>
				</style:list-level-properties>
			</text:list-level-style-bullet>
			<text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.81cm" fo:text-indent="-0.635cm" fo:margin-left="3.81cm"/>
				</style:list-level-properties>
			</text:list-level-style-bullet>
			<text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.445cm" fo:text-indent="-0.635cm" fo:margin-left="4.445cm"/>
				</style:list-level-properties>
			</text:list-level-style-bullet>
			<text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.08cm" fo:text-indent="-0.635cm" fo:margin-left="5.08cm"/>
				</style:list-level-properties>
			</text:list-level-style-bullet>
			<text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.715cm" fo:text-indent="-0.635cm" fo:margin-left="5.715cm"/>
				</style:list-level-properties>
			</text:list-level-style-bullet>
			<text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.35cm" fo:text-indent="-0.635cm" fo:margin-left="6.35cm"/>
				</style:list-level-properties>
			</text:list-level-style-bullet>
			<text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm"/>
				</style:list-level-properties>
			</text:list-level-style-bullet>
		</text:list-style>
	</office:automatic-styles>
	<office:body>
<xsl:apply-templates select="ausbildungsvertrag"/>
	</office:body>
</office:document-content>
</xsl:template>

<xsl:template match="ausbildungsvertrag">
		<office:text 	text:use-soft-page-breaks="true" 
						xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
						xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" 
						xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" 
						xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" 
						xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" 
						xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
			</text:sequence-decls>
			<text:section text:style-name="Sect1" text:name="Bereich1">
				<text:h text:style-name="P118" text:outline-level="1">Ausbildungsvertrag</text:h>
				<text:p text:style-name="P81"/>
				<text:p text:style-name="P8">Dieser Vertrag regelt das Rechtsverhältnis zwischen </text:p>
				<text:p text:style-name="P9">
					<text:span text:style-name="T6">dem Verein Fachhochschule Technikum Wien,</text:span>
					<text:span text:style-name="T17">1060 Wien, Mariahilfer Straße 37-39 (kurz „Erhalter“ genannt) einerseits </text:span>
					<text:span text:style-name="T6">und</text:span>
				</text:p>
				<text:p text:style-name="P8"/>
				<text:h text:style-name="P119" text:outline-level="1">
					<text:span text:style-name="T28">Training Contract</text:span>
				</text:h>
				<text:p text:style-name="P81"/>
				<text:p text:style-name="P13">This contract governs the legal relationship between </text:p>
				<text:p text:style-name="P13">
					<text:span text:style-name="T5">the University of Applied Sciences Technikum Wien Association,</text:span> 1060 Vienna, Mariahilfer Straße 37-39 (referred to as &quot;operator&quot;) on the one hand <text:span text:style-name="T5">and</text:span>
				</text:p>
				<text:p text:style-name="P13"/>
			</text:section>
			<text:section text:style-name="Sect2" text:name="Bereich2">
				<text:p text:style-name="P5">
					<text:span text:style-name="T15">Familienname (Surname):<text:tab/><xsl:value-of select="nachname"/></text:span>
				</text:p>
				<text:p text:style-name="P10">Vorname (First Name):<text:tab/><xsl:value-of select="vorname"/></text:p>
				<text:p text:style-name="P5">
					<text:span text:style-name="T15">Akademischer Titel (Academic degree):<text:tab/><xsl:value-of select="titelpre"/><xsl:value-of select="titelpost"/></text:span>
				</text:p>
				<text:p text:style-name="P5">
					<text:span text:style-name="T15">Adresse (Address):<text:tab/><xsl:value-of select="strasse"/></text:span>
					<text:span text:style-name="T15">; </text:span>
				</text:p>
				<text:p text:style-name="P5">
					<text:span text:style-name="T13">
						<text:tab/><xsl:value-of select="plz"/></text:span>
				</text:p>
				<text:p text:style-name="P2">
					<text:span text:style-name="T15">Geburtsdatum (Date of birth): <text:tab/><xsl:value-of select="gebdatum"/></text:span>
				</text:p>
				<text:p text:style-name="P2">
					<text:span text:style-name="T13">Sozialversicherungsnr. </text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T13">
							<text:note text:id="ftn1" text:note-class="footnote">
								<text:note-citation>1</text:note-citation>
								<text:note-body>
									<text:p text:style-name="Standard">
										<text:span text:style-name="T18">
											<text:s/>
										</text:span>
										<text:span text:style-name="T2">Gemäß § 3 Absatz 1 des Bildungsdokumentationsgesetzes idgF und der Bildungsdokumentationsverordnung-Fachhochschulen <text:s/>idgF hat der Erhalter die Sozialversicherungsnummer zu erfassen und gemäß § 7 Absatz 2 im Wege der Agentur für Qualitätssicherung und Akkreditierung Austria an das zuständige Bundesministerium und die Bundesanstalt Statistik Österreich zu übermitteln.</text:span>
									</text:p>
									<text:p text:style-name="P6"/>
								</text:note-body>
							</text:note>
						</text:span>
					</text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T13"> </text:span>
					</text:span>
					<text:span text:style-name="T16">:<text:tab/><xsl:value-of select="svnr"/></text:span>
				</text:p>
				<text:p text:style-name="P3">
					<text:span text:style-name="T15">
						<text:tab/>(Social security number)</text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T15">
							<text:note text:id="ftn2" text:note-class="footnote">
								<text:note-citation>2</text:note-citation>
								<text:note-body>
									<text:p text:style-name="Standard">
										<text:span text:style-name="T20">
											<text:s/>
										</text:span>
										<text:span text:style-name="T4">Pursuant to § 3 section 1 of the Education Documentation Act as amended and the Education Documentation Regulation for Universities of Applied Sciences as amended, the operator shall record the social security number pursuant to § 7 paragraph 2 and shall transfer it via the Agency for Quality Assurance and Accreditation Austria to the competent Ministry and Statistics Austria.</text:span>
									</text:p>
								</text:note-body>
							</text:note>
						</text:span>
					</text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T15"> </text:span>
					</text:span>
					<text:span text:style-name="T15">: <text:tab/>
					</text:span>
				</text:p>
				<text:p text:style-name="P41"/>
			</text:section>
			<text:section text:style-name="Sect1" text:name="Bereich3">
				<text:p text:style-name="P76">
					<text:span text:style-name="T36">(kurz „ao. Studentin“ bzw. „ao. Student“ genannt) andererseits im Rahmen des Lehrgangs zur Weiterbildung nach §9 FHStG idgF</text:span>
				</text:p>
				<text:p text:style-name="P76">
					<text:span text:style-name="T36"/>
				</text:p>
				<text:p text:style-name="P76">
					<text:span text:style-name="T37"><xsl:value-of select="studiengang"/>, Lehrgangsnummer <xsl:value-of select="studiengang_kz"/>, </text:span>
				</text:p>
				<text:p text:style-name="P76">
					<text:span text:style-name="T37"/>
				</text:p>
				<text:p text:style-name="P76">
					<text:span text:style-name="T36">in der Organisationsform eines </text:span>
					
					<xsl:choose>
						<xsl:when test="orgform = 'BB'" >
							<text:span text:style-name="T37">berufsbegleitenden</text:span>
							<text:span text:style-name="T40"> Lehrgangs zur Weiterbildung. </text:span>
						</xsl:when>
						<xsl:when test="orgform = 'VZ'" >
							<text:span text:style-name="T37">Vollzeit</text:span>
							<text:span text:style-name="T40">-Lehrgangs zur Weiterbildung.</text:span>
						</xsl:when>
						<xsl:otherwise>
							<text:span text:style-name="T40">Lehrgangs zur Weiterbildung.</text:span>
						</xsl:otherwise>
					</xsl:choose>
				</text:p>
				<text:p text:style-name="P1"/>
				<text:p text:style-name="P1">
					<text:span text:style-name="T36">Dieser Lehrgang wird von der Technikum Wien GmbH organisiert und gemeinsam mit der Fachhochschule Technikum Wien durchgeführt. Es gelten die AGB der Technikum Wien GmbH, diese sind unter </text:span>
				</text:p>
				<text:p text:style-name="P91">(referred to as &quot;external student&quot;) on the other within the Certificate Program for Further Education subjected to § 9 FHStG as amended</text:p>
				<text:p text:style-name="P52"/>
				<text:p text:style-name="P52"><xsl:value-of select="studiengang_englisch"/>, program code <xsl:value-of select="studiengang_kz"/>, </text:p>
				<text:p text:style-name="P52"/>
				<text:p text:style-name="P52"/>
				<text:p text:style-name="P47">in the organizational form of a 
					<xsl:choose>
						<xsl:when test="orgform = 'BB'" >
							<text:span text:style-name="T5"> part-time</text:span> <text:span text:style-name="T44"> Certificate Program for Further Education.</text:span>
						</xsl:when>
						<xsl:when test="orgform = 'VZ'" >
							<text:span text:style-name="T5"> full-time</text:span> <text:span text:style-name="T44"> Certificate Program for Further Education.</text:span>
						</xsl:when>
						<xsl:otherwise>
							<text:span text:style-name="T44"> Certificate Program for Further Education.</text:span>
						</xsl:otherwise>
					</xsl:choose>
				</text:p>
				<text:p text:style-name="P1">
					<text:span text:style-name="T36"/>
				</text:p>
				<text:p text:style-name="P1">
					<text:span text:style-name="T36">This course is organized by the Technikum Wien GmbH and conducted together with the University of Applied Sciences Technikum Wien. The General Terms and Conditions of the Technikum Wien GmbH apply. These can be accessed at</text:span>
				</text:p>
				<text:p text:style-name="P88">
					<text:span text:style-name="T32">https://academy.technikum-wien.at/agb jederzeit abrufbar.</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T21"/>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T21">1. Ausbildungsort</text:span>
				</text:p>
				<text:p text:style-name="P12"/>
				<text:p text:style-name="P54">Studienort sind die Räumlichkeiten der FH Technikum Wien, 1200 Wien, Höchstädtplatz. Bei Bedarf kann der Erhalter einen anderen Studienort <text:span text:style-name="T46">in Wien </text:span>festlegen<text:span text:style-name="T46">, außerhochschulische Aktivitäten (z.B. Exkursionen) können auch außerhalb von Wien stattfinden.</text:span>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T21">2. Vertragsgrundlage</text:span>
				</text:p>
				<text:p text:style-name="P7"/>
				<text:p text:style-name="P54">Die Ausbildung erfolgt auf der Grundlage des Fachhochschul-Studiengesetzes, BGBl. Nr. 340/1993 idgF und des Hochschul-Qualitätssicherungsgesetzes, BGBl. Nr. 74/2011 idgF <text:span text:style-name="T46">und der Satzung der Fachhochschule Technikum Wien idgF.</text:span>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="Standard">
					<text:bookmark-start text:name="_Ref78860434"/>
					<text:span text:style-name="T21">3. Ausbildungsdauer</text:span>
					<text:bookmark-end text:name="_Ref78860434"/>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P13">Die Ausbildungsdauer beträgt <xsl:value-of select="student_maxsemester"/> Semester.</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P51">Die ao. Studentin bzw. der ao. Student hat das Recht, eine Anerkennung nachgewiesener Kenntnisse beim Lehrgang zu beantragen. Eine solche Anerkennung setzt voraus, dass die erworbenen Kenntnisse mit dem Inhalt und dem Umfang der Lehrveranstaltung sind und bewirkt die Anrechnung der entsprechenden Lehrveranstaltung.</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P91">https://academy.technikum-wien.at/agb at any time.</text:p>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">1. Place of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P17"/>
				<text:p text:style-name="P48">Places of training are the premises of the UAS Technikum Wien, 1200 Vienna, Höchstädtplatz. If necessary, the operator may specify a different place of study <text:span text:style-name="T46">in Vienna</text:span>. <text:span text:style-name="T46">Non-university activities (e.g. excursions) may take place away from Vienna.</text:span>
				</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">2. Contractual Basis</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P48">The training is based on the University of Applied Sciences Studies Act, Federal Law Gazette No. 340/1993 as amended and the Higher Education Quality Assurance Act, Federal Law Gazette I No. 74/2011 as amended <text:span text:style-name="T46">and the statutes of the University of Applied Sciences Technikum Wien as amended.</text:span>
				</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">3. Duration of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P13">
					<text:span text:style-name="T32">The training period lasts <xsl:value-of select="student_maxsemester"/> semesters.</text:span>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P62">The external student has the right to apply to the degree program for Recognition of Prior Learning (RPL). Such recognition requires that the knowledge previously acquired is equivalent in content and scope to that of the course and means that the external student will be exempted from the respective course.</text:p>
				<text:p text:style-name="P87">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">4. Ausbildungsabschluss</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P13"/>
				<xsl:choose>
					<xsl:when test="lgartcode='1'">
						<text:p text:style-name="P13">Nach Abschluss aller vorgeschriebenen Prüfungen wird der akademische Grad &quot;<xsl:value-of select="akadgrad"/>&quot; verliehen.</text:p>
					</xsl:when>
					<xsl:when test="lgartcode='2'">
						<text:p text:style-name="P13">Nach Abschluss aller vorgeschriebenen Prüfungen wird der Titel &quot;<xsl:value-of select="akadgrad"/>&quot; verliehen.</text:p>
					</xsl:when>
					<xsl:otherwise>
						<text:p text:style-name="P13">Nach Abschluss aller vorgeschriebenen Prüfungen wird ein Zertifizierungsdiplom der Technikum Wien Academy verliehen.</text:p>
					</xsl:otherwise>
				</xsl:choose>
				<text:p text:style-name="P34"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T21">5. Rechte und Pflichten des Erhalters</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:bookmark-start text:name="_Ref78865698"/>
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10"/>
					</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10">5.1 Rechte</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
					<text:bookmark-end text:name="_Ref78865698"/>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P14">Der Erhalter führt eine periodische Überprüfung des Studiums im Hinblick auf Relevanz und Aktualität durch und ist im Einvernehmen mit dem FH-Kollegium berechtigt, daraus Änderungen des Lehrgangs zur Weiterbildung abzuleiten.</text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P14">
					<text:span text:style-name="T33">Der Erhalter ist berechtigt, die Daten der/des ao. Studierenden an den FH Technikum Wien Alumni Club zu übermitteln. Der Alumni Club ist der AbsolventInnenverein der FH Technikum Wien. Er hat zum Ziel, AbsolventInnen, Studierende und Lehrende miteinander zu vernetzen sowie AbsolventInnen laufend über Aktivitäten an der FH Technikum Wien zu informieren. Einer Zusendung von Informationen durch den Alumni Club kann jederzeit widersprochen werden.</text:span>
				</text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10">5.2 Pflichten</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T38"/>
					</text:span>
				</text:p>
				<text:list xml:id="list8312921868541980065" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P102">Der Erhalter verpflichtet sich zur ordnungsgemäßen Planung und Durchführung des Lehrgangs. Der Erhalter ist verpflichtet, allfällige Änderungen des Lehrgangs zeitgerecht bekannt zu geben.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P96">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T23">4. Formal Completion of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P14"/>
				<xsl:choose>
					<xsl:when test="lgartcode='1'">
						<text:p text:style-name="P13">When all the required examinations have been completed the academic degree &quot;<xsl:value-of select="akadgrad"/>&quot; will be awarded.</text:p>
					</xsl:when>
					<xsl:when test="lgartcode='2'">
						<text:p text:style-name="P13">When all the required examinations have been completed the title &quot;<xsl:value-of select="akadgrad"/>&quot; will be awarded.</text:p>
					</xsl:when>
					<xsl:otherwise>
						<text:p text:style-name="P13">>When all the required examinations have been completed a Technikum Wien Academy diploma certificate will be awarded.</text:p>
					</xsl:otherwise>
				</xsl:choose>
				<text:p text:style-name="P107"/>
				<text:p text:style-name="P11">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">5. Rights and Duties of the Operator</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P11">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T24"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P20">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P20">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10">5.1 Rights</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P16">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T39"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P14">The operator performs a periodic review of the course in terms of relevance and topicality, and is authorized, in consultation with the University of Applied Sciences Council, to deduce from this changes in the Certificate Program for Further Education.</text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P14">
					<text:span text:style-name="T33">The operator is entitled to communicate an external student’s data to the UAS Technikum Wien Alumni Club. The Alumni Club is the graduate association of the UAS Technikum Wien. Its goal is to provide links between graduates, students and lecturers as well as to keep graduates informed of the activities at the UAS Technikum Wien. A mailing of information from the Alumni Club can be vetoed at any time.</text:span>
				</text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P11">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10">5.2 Duties </text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P11">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T38"/>
					</text:span>
				</text:p>
				<text:list xml:id="list155838500619743" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P102">The operator undertakes to plan and hold the Certificate Program for Further Education in a proper manner within the expected time period. The operator is obliged to give adequate notice of any changes to the Certificate Program for Further Education.</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P114">
							<text:soft-page-break/>
							<text:span text:style-name="T35">Der Erhalter verpflichtet sich, jedenfalls folgende Dokumente zur Verfügung zu stellen: Studierendenausweis, Diploma Supplement, Urkunde über die Verleihung des akademischen Grades, Studienerfolgsbestätigung, Inskriptions-bestätigung.</text:span>
						</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P100">Der Erhalter verpflichtet sich zur sorgfaltsgemäßen Verwendung der personenbezogenen Daten der ao. Studierenden. Die Daten werden nur im Rahmen der gesetzlichen und vertraglichen Verpflichtungen sowie des Studienbetriebes verwendet und nicht an nicht berechtigte Dritte weitergegeben.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P101"/>
				<text:p text:style-name="P82">
					<text:span text:style-name="T21">6. Rechte und Pflichten der ao. Studierenden</text:span>
				</text:p>
				<text:p text:style-name="P82">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P82">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10">6.1 Rechte</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P82">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P40">Die ao. Studentin bzw. der ao. Student hat das Recht auf <text:span text:style-name="T41">einen Studienbetrieb gemäß den im Lehrgang zur Weiterbildung idgF und in der Satzung der FH Technikum Wien idgF festgelegten Bedingungen.</text:span>
				</text:p>
				<text:p text:style-name="P108"/>
				<text:p text:style-name="P77">
					<text:span text:style-name="T10">6.2 Pflichten</text:span>
				</text:p>
				<text:p text:style-name="P65">6.2.1 Einhaltung studienrelevanter Bestimmungen</text:p>
				<text:p text:style-name="P64"/>
				<text:p text:style-name="P64">Die ao. Studentin bzw. der ao. Student ist verpflichtet, insbesondere folgende Bestimmungen einzuhalten:</text:p>
				<text:list xml:id="list155839567341657" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P63">Studienordnung und Prüfungsordnung für Lehrgänge idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P63">Hausordnung idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P63">Brandschutzordnung idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P63">Bibliotheksordnung idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P63">Die für den jeweiligen Lehrgang geltende/n Laborordnung/en idgF</text:p>
					</text:list-item>
				</text:list>
				<text:list xml:id="list155839134361124" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P112">
							<text:span text:style-name="T46">The operator undertakes to make the following documents available in any event: student ID card, diploma supplement, certificate attesting to the award of the academic degree, confirmation of success in the degree program, confirmation of registration.</text:span>
						</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P100">The operator is committed to use the personal data of the external students carefully. The data is only to be used within the operator’s legal and contractual obligations as well as its program of studies and is not to be handed on to unauthorized third parties.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P106"/>
				<text:p text:style-name="P106"/>
				<text:p text:style-name="P82">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">6. Rights and Duties of the external Students</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P82">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T14"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P82">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10">6.1 Rights </text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P82">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P55">The external student has the right to a course of study according to the conditions specified in the Certificate Program for Further Education as amended and in the Statutes of the UAS Technikum Wien as amended.</text:p>
				<text:p text:style-name="P108"/>
				<text:p text:style-name="P77">
					<text:span text:style-name="T10">6.2 Duties</text:span>
				</text:p>
				<text:p text:style-name="P64">6.2.1 Compliance with regulations relevant to the studies</text:p>
				<text:p text:style-name="P64"/>
				<text:p text:style-name="P64">In particular the external student undertakes to comply with the following regulations:</text:p>
				<text:p text:style-name="P64"/>
				<text:list xml:id="list1993716771416284164" text:continue-numbering="true"  text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P63">Study Regulations and Examination Regulations for Certificate Programs for Further Education as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P63">General Rules of Conduct as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P63">Fire Regulations as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P63">Library Regulations as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P63">The Laboratory Regulations applicable to the respective Certificate Program for Further Education as amended</text:p>
					</text:list-item>
				</text:list>				
				<text:p text:style-name="P90">Diese Dokumente sind öffentlich zugänglich unter www.technikum-wien.at.</text:p>
				<text:p text:style-name="P78">
					<text:span text:style-name="T35">Darüber hinaus sind die AGB der Technikum Wien GmbH Bestandteil dieses Vertrages (https://academy.technikum-wien.at/agb).</text:span>
				</text:p>
				<text:p text:style-name="P66"/>
				<text:p text:style-name="P66"/>
				<text:p text:style-name="P69">6.2.2 Lehrgangskosten inkl. Studierenden-beitrag (&quot;ÖH-Beitrag&quot;)</text:p>
				<text:p text:style-name="P69">Voraussetzung für die Geltung dieses Ausbildungsvertrages und für die Teilnahme am Lehrgang ist die erfolgte vollständige Bezahlung der Lehrgangskosten zu den jeweiligen Zahlungsterminen. Bezüglich der Möglichkeiten (teilweiser) Rückerstattungen gelten die AGB der Technikum Wien GmbH für Lehrgänge zur Weiterbildung. Gemäß § 4 Abs 10 FHStG sind ao. Studierende an Fachhochschulen Mitglieder der Österreichischen HochschülerInnenschaft (ÖH). Der/Die ao. Studierende hat semesterweise einen ÖH-Beitrag an den Erhalter zu entrichten, der diesen an die ÖH abführt. Die Entrichtung des Betrags ist Voraussetzung für die Zulassung zum Studium bzw. für dessen Fortsetzung.</text:p>
				<text:p text:style-name="P69"/>
				<text:p text:style-name="P69">6.2.3 Beibringung persönlicher Daten </text:p>
				<text:p text:style-name="P69">Die ao. Studentin bzw. der ao. Student ist verpflichtet, persönliche Daten beizubringen, die auf Grund eines Gesetzes, einer Verordnung oder eines Bescheides vom Erhalter erfasst werden müssen oder zur Erfüllung des Ausbildungsvertrages bzw. für den Studienbetrieb unerlässlich sind.</text:p>
				<text:p text:style-name="P56"/>
				<text:p text:style-name="P56"/>
				<text:p text:style-name="P113">
					<text:span text:style-name="T29">These documents are publically available at www.technikum-wien.at.</text:span>
				</text:p>
				<text:p text:style-name="P105">
					<text:span text:style-name="T29">Furthermore, the General Terms and Conditions of the Technikum Wien GmbH are an integral part of this contract (https://academy.technikum-wien.at/agb).</text:span>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P68">6.2.2 Course costs inc. student fee (Austrian Student Union fee) 4</text:p>
				<text:p text:style-name="P68">One condition for the validity of the training contract and for participation in the course is full payment of the course costs by the respective payment deadlines. As regards the possibilities of (partial) reimbursement, the terms and conditions of the Technikum Wien GmbH for courses for further education apply. In accordance with § 4 para.10 FHStG (University of Applied Sciences Studies Act) external students at Universities of Applied Sciences are members of the Austrian National Union of Students (ÖH). Each semester the external student is required to pay an ÖH fee to the operator. This fee is then paid to the ÖH. Payment of this fee is a prerequisite for admission to the course of study or its continuation.</text:p>
				<text:p text:style-name="P68"/>
				<text:p text:style-name="P68">6.2.3 Providing Personal Data</text:p>
				<text:p text:style-name="P55">
					<text:span text:style-name="T47">The external student is obliged to produce personal data which must be registered because of a law, regulation or a decision by the operator, or is essential for the fulfilling of the training contract or for the program of studies.</text:span>
				</text:p>
				<text:p text:style-name="P35">6.2.4 Aktualisierung eigener Daten und Bezug von Informationen</text:p>
				<text:p text:style-name="P36">Die ao. Studentin bzw. der ao. Student hat unaufgefordert dafür zu sorgen, dass die von ihr/ihm beigebrachten Daten aktuell sind. Änderungen sind der Studiengangsassistenz unverzüglich schriftlich mitzuteilen. Darüber hinaus trifft sie/ihn die Pflicht, sich von studienbezogenen Informationen, die ihr/ihm an die vom Erhalter zur Verfügung gestellte Emailadresse zugestellt werden, in geeigneter Weise Kenntnis zu verschaffen.</text:p>
				<text:p text:style-name="P38"/>
				<text:p text:style-name="P38">6.2.5 Verwertungsrechte </text:p>
				<text:p text:style-name="P38">Sofern nicht im Einzelfall andere Regelungen zwischen dem Erhalter und der ao. Studentin oder dem ao. Studenten getroffen wurden, ist die ao. Studentin oder der ao. Student verpflichtet, dem Erhalter die Rechte an Forschungs- und Entwicklungsergebnissen auf dessen schriftliche Anfrage hin anzubieten.</text:p>
				<text:p text:style-name="P38"/>
				<text:p text:style-name="P38">6.2.6 Aufzeichnungen und Mitschnitte</text:p>
				<text:p text:style-name="P38">Es ist der/dem ao. Studierenden ausdrücklich untersagt, Lehrveranstaltungen als Ganzes oder nur Teile davon aufzuzeichnen und/oder mitzuschneiden (z.B. durch Film- und/oder Tonaufnahmen oder sonstige hierfür geeignete audiovisuelle Mittel). Darüber hinaus ist jede Form der öffentlichen Zurverfügungstellung (drahtlos oder drahtgebunden) der vorgenannten Aufnahmen z.B. in sozialen Netzwerken wie Facebook, WhatsAPP, LinkedIn, XING etc, aber auch auf Youtube, Instagram usw. oder durch sonstige für diese Zwecke geeignete Kommunikations-mittel untersagt. Diese Regelungen gelten sinngemäß auch für Skripten, sonstige Lernbehelfe und Prüfungsangaben. </text:p>
				<text:p text:style-name="P38">Ausgenommen hiervon ist eine Aufzeichnung zu ausschließlichen Lern-, Studien- und Forschungszwecken und zum privaten Gebrauch, sofern hierfür der / die Vortragende vorab ausdrücklich seine / ihre schriftliche Zustimmung erteilt hat. </text:p>
				<text:p text:style-name="P92">6.2.4 Updating personal data and the retrieval of information</text:p>
				<text:p text:style-name="P57">
					<text:span text:style-name="T48">Without being reminded, the external student must ensure that the data provided by them is up-to-date. Changes are to be immediately communicated to the administrative assistant in writing. Furthermore, it is the students’ responsibility to make themselves suitably aware of information relating to their studies which has been sent to them at the email address provided for them by the operator.</text:span>
				</text:p>
				<text:p text:style-name="P57"/>
				<text:p text:style-name="P57"/>
				<text:p text:style-name="P68">6.2.5 Exploitation Rights</text:p>
				<text:p text:style-name="P68">Unless other arrangements have been agreed between the operator and the external student at an individual level, on written request, the external student undertakes to offer the operator the rights to research and development results. </text:p>
				<text:p text:style-name="P68"/>
				<text:p text:style-name="P68"/>
				<text:p text:style-name="P68">6.2.6 Recordings </text:p>
				<text:p text:style-name="P68">It is expressly forbidden for the external student to record lectures in part or in total (e.g. by using film and / or sound recordings or other audio-visual means suitable for this purpose). In addition, any form of making the aforementioned recordings publically available (wired or wireless) for example in social networks such as Facebook, WhatsAPP, LinkedIn, XING etc, but also on Youtube, Instagram etc., or by other means of communication designed for these purposes is strictly prohibited. These regulations shall apply correspondingly to scripts, other learning aids and examination data. </text:p>
				<text:p text:style-name="P68">The only exception is a recording exclusively for the purpose of learning, study and research and for personal use, provided that the lecturer has expressly granted his / her prior written consent.<text:bookmark-start text:name="_Ref78867653"/>
				</text:p>
				<text:p text:style-name="P33"/>
				<text:p text:style-name="P33"/>
				<text:p text:style-name="P33"/>
				<text:p text:style-name="P33">
					<text:soft-page-break/>
					<text:span text:style-name="T42">6.2.7</text:span>
					<text:span text:style-name="T42"> Geheimhaltungspflicht</text:span>
					<text:bookmark-end text:name="_Ref78867653"/>
				</text:p>
				<text:p text:style-name="P13">Die ao. Studentin bzw. der ao. Student ist zur Geheimhaltung von Forschungs- und Entwicklungsaktivitäten und -ergebnissen gegenüber Dritten verpflichtet. </text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P68">6.2.8 Schadensmeldung</text:p>
				<text:p text:style-name="P68">Im Falle des Eintretens eines Schadens am Inventar der Fachhochschule ist der/die ao. Studierende verpflichtet, diesen innerhalb von drei Tagen dem Lehrgangssekretariat zu melden. Allfällige Haftungsansprüche bleiben hiervon unberührt.</text:p>
				<text:p text:style-name="P68"/>
				<text:p text:style-name="P68"/>
				<text:p text:style-name="P68">6.2.9 Rückgabeverpflichtung bei Studienende</text:p>
				<text:p text:style-name="P68">Die ao. Studentin bzw. der ao. Student ist verpflichtet, bei einer Beendigung des Lehrgangs unverzüglich alle zur Verfügung gestellten Gerätschaften, Bücher, Schlüssel und sonstige Materialien zurückzugeben.</text:p>
				<text:p text:style-name="P68"/>
				<text:p text:style-name="P80">
					<text:span text:style-name="T21">7. Beendigung des Vertrages</text:span>
				</text:p>
				<text:p text:style-name="P80">
					<text:span text:style-name="T10">7.1 Auflösung im beiderseitigen Einvernehmen</text:span>
				</text:p>
				<text:p text:style-name="P39">Im beiderseitigen Einvernehmen ist die Auflösung des Ausbildungsvertrages jederzeit ohne Angabe von Gründen möglich. Die einvernehmliche Auflösung bedarf der Schriftform. </text:p>
				<text:p text:style-name="P58"/>
				<text:p text:style-name="P79">
					<text:span text:style-name="T10">7.2 Kündigung durch die ao. Studentin bzw. den ao. Studenten</text:span>
				</text:p>
				<text:p text:style-name="P70">Die ao. Studentin bzw. der ao. Student kann den Ausbildungsvertrag schriftlich jeweils zum Ende eines Semesters kündigen. <text:span text:style-name="T49">Die Verpflichtung zur vollständigen Leistung der Lehrgangskosten wird von einer Kündigung durch die ao. Studentin bzw. den ao. Studenten nicht berührt.</text:span>
				</text:p>
				<text:p text:style-name="P93">6.2.7 Confidentiality </text:p>
				<text:p text:style-name="P13">The external student is required to maintain confidentiality towards third parties of research and development activities and results. </text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P68">6.2.8 Damage Report</text:p>
				<text:p text:style-name="P68">If any damage should be caused to the inventory of the University of Applied Sciences, the external student undertakes to report this to the administrative assistant of the Certificate Program for Further Education within three days. Any liability claims shall remain unaffected.</text:p>
				<text:p text:style-name="P68"/>
				<text:p text:style-name="P68">6.2.9 Obligation to Return Borrowed Items</text:p>
				<text:p text:style-name="P68">The external student undertakes to return promptly all equipment, books, keys and other materials that have been made available, when the Certificate Program for Further Education is finished or broken off.</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P80">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">7. Termination of the contract </text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P42">7.1 Annulment by Mutual Agreement </text:p>
				<text:p text:style-name="P59"/>
				<text:p text:style-name="P59">By mutual consent, the annulment of the training contract is possible at any time, without notice and for any reason. The amicable annulment must be put down in writing. </text:p>
				<text:p text:style-name="P59"/>
				<text:p text:style-name="P80">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10">7.2 Termination by the external Student</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P59"/>
				<text:p text:style-name="P59">The external student may terminate the training contract in writing at the end of each semester. <text:span text:style-name="T49">The requirement to pay the course costs in full is not affected by an external student terminating the course.</text:span>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P89">
					<text:span text:style-name="T9">7.3 Automatische Beendigung des Vertrages</text:span>
				</text:p>
				<text:p text:style-name="P71">Der Ausbildungsvertrag erlischt mit dem Abschluss des Lehrgangs.</text:p>
				<text:p text:style-name="P71">Der Vertrag endet automatisch durch die negative Beurteilung der letztmöglichen Prüfungswiederholung, in diesem Fall bleibt die Verpflichtung zur vollständigen Leistung der Lehrgangskosten unberührt.</text:p>
				<text:p text:style-name="P15"/>
				<text:p text:style-name="P4">
					<text:span text:style-name="T10">7.4 Ausschluss durch den Erhalter</text:span>
				</text:p>
				<text:p text:style-name="P13">Der Erhalter kann die ao. Studentin bzw. den ao. Studenten aus wichtigem Grund mit sofortiger Wirkung vom weiteren Studium ausschließen, und zwar beispielsweise wegen </text:p>
				<text:list xml:id="list155839382667014" text:continue-list="list155839134361124" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P98">nicht genügender Leistung im Sinne der Prüfungsordnung;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P98">mehrmaligem unentschuldigten Verletzen der Anwesenheitspflicht ; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P98">wiederholtem Nichteinhalten von Prüfungsterminen und Abgabeterminen für Seminararbeiten, Projektarbeiten etc.; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P98">schwerwiegender bzw. wiederholter Verstöße gegen die Hausordnung;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P99">persönlichem Verhalten, das zu einer Beeinträchtigung des Images und/oder Betriebes des Lehrganges, der Fach-hochschule bzw. des Erhalters oder von Personen führt, die für die Fachhochschule bzw. den Erhalter tätig sind;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P99">Verletzung der Verpflichtung, dem Erhalter die Rechte an Forschungs- und Entwicklungsergebnissen anzubieten (siehe Pkt. 6.2.<text:span text:style-name="T51">5</text:span>);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P99">Verletzung der Geheimhaltungspflicht (siehe Pkt. 6.2.<text:span text:style-name="T51">7</text:span>); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P99">strafgerichtlicher Verurteilung (wobei die Art des Deliktes und der Grad der Schuld berücksichtigt werden);</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P115"/>
				<text:p text:style-name="P94">
					<text:span text:style-name="T8">7.3 Automatic Ending of the Contract</text:span>
				</text:p>
				<text:p text:style-name="P73"/>
				<text:p text:style-name="P73">The training contract expires on completion of</text:p>
				<text:p text:style-name="P73">the Certificate Program for Further Education.</text:p>
				<text:p text:style-name="P73">The contract terminates automatically when the last possible opportunity to retake an examination ends in failure. In this case the obligation to fully pay all the costs of the course still remains.</text:p>
				<text:p text:style-name="P74"/>
				<text:p text:style-name="P43">
					<text:span text:style-name="T11">7.4 Expulsion by the Operator</text:span>
				</text:p>
				<text:p text:style-name="P13">The operator may exclude the external student from further study with immediate effect for good cause, for example because of</text:p>
				<text:p text:style-name="P13"/>
				<text:list xml:id="list155838895556138" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P98">insufficient achievement for the purposes of the examination regulations;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P98">repeated unexcused violation of the compulsory attendance regulation;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P98">repeated non-compliance with examination dates and deadlines for seminar papers, project work etc.; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P98">serious or repeated violation of the house rules;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P99">personal behavior, which leads to an adverse effect on the image and / or operation of the Certificate Program, the university or the operator or on persons who are working for the university or the operator;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P99">breach of the obligation to offer the operator the rights to research and development results (see Section 6.2.<text:span text:style-name="T51">5</text:span>);<text:line-break/>
						</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P99">breach of confidentiality (see Section 6.2.<text:span text:style-name="T51">7</text:span>);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P99">a criminal conviction (whereby the nature of the offence and the level of culpability are taken into account);</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P115"/>
				<text:list xml:id="list155838677773193" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P116">Nichterfüllung finanzieller Verpflichtungen trotz Mahnung (z.B. Unkostenbeitrag, Studienbeitrag etc.); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P98">Weigerung zur Beibringung von Daten (siehe Pkt. 6.2.<text:span text:style-name="T51">3</text:span>) </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P98">Plagiieren im Rahmen wissenschaftlicher Arbeiten </text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P13">Der Ausschluss kann mündlich erklärt werden. Mit Ausspruch des Ausschlusses endet der Ausbildungsvertrag, es sei denn, es wird ausdrücklich auf einen anderen Endtermin hingewiesen. Eine schriftliche Bestätigung des Ausschlusses wird innerhalb von zwei Wochen nach dessen Ausspruch per Post an die bekannt gegebene Adresse abgeschickt oder auf andere geeignete Weise übermittelt. </text:p>
				<text:p text:style-name="P61">Gleichzeitig mit dem Ausspruch des Ausschlusses kann auch ein Hausverbot verhängt werden. <text:span text:style-name="T51">Die Verpflichtung zur vollständigen Leistung der Lehrgangskosten wird von einem Ausschluss nicht berührt.</text:span>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P83">
					<text:span text:style-name="T21">8. Ergänzende Vereinbarungen</text:span>
				</text:p>
				<text:p text:style-name="P97"/>
				<text:p text:style-name="P49">Ao. Studierende des Programms sind verpflichtet, eine EDV-Ausstattung zu beschaffen und zu unterhalten, die es ermöglicht, an den Fernlehrelementen teilzunehmen. Die gesamten Kosten der Anschaffung und des Betriebs (inkl. Kosten für Internet und e-mail) trägt der ao. Student bzw. die ao. Studentin. </text:p>
				<text:p text:style-name="P49"/>
				<text:p text:style-name="P85">
					<text:span text:style-name="T21">9. Unwirksamkeit von Vertrags-bestimmungen</text:span>
				</text:p>
				<text:p text:style-name="P50"/>
				<text:p text:style-name="P60">
					<text:span text:style-name="T32">Sollten einzelne Bestimmungen dieses Vertrages unwirksam oder nichtig sein oder werden, so berührt dies die Gültigkeit der übrigen Bestimmungen dieses Vertrages nicht.</text:span>
				</text:p>
				<text:p text:style-name="P60">
					<text:span text:style-name="T32"/>
				</text:p>
				<text:list xml:id="list155838732359392" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P111">non-fulfilment of the financial obligations, despite a reminder (e.g. contribution towards expenses, tuition fees , etc.); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P98">refusal to provide any data (see section 6.2.<text:span text:style-name="T51">3</text:span>); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P98">plagiarism in academic work</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P13">The expulsion can be explained verbally. Once notice of the expulsion has been given the training contract ends unless another deadline is explicitly made clear. Within two weeks of notice being given, written confirmation of the expulsion is mailed by post to the address provided or transmitted in any other appropriate manner.</text:p>
				<text:p text:style-name="P61">Simultaneously with notice of expulsion being given an exclusion order from entering the building may also be imposed. <text:span text:style-name="T51">The requirement to pay the course costs in full is not affected by an expulsion.</text:span>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P84">
					<text:span text:style-name="T21">8. Supplementary Agreements</text:span>
				</text:p>
				<text:p text:style-name="P46"/>
				<text:p text:style-name="P45">External students in the program are required to obtain and maintain computer equipment which allows them to participate in the distance learning elements. The total cost of acquisition and operation (including costs for Internet and e-mail) shall be borne by the external student. </text:p>
				<text:p text:style-name="P45"/>
				<text:p text:style-name="P45"/>
				<text:p text:style-name="P86">
					<text:span text:style-name="T21">9. Invalidity of Contractual Provisions</text:span>
				</text:p>
				<text:p text:style-name="P60"/>
				<text:p text:style-name="P50">If any provision of this agreement should be or become invalid or void, this shall not affect the validity of the remaining provisions of this agreement.</text:p>
				<text:p text:style-name="P95">
					<text:soft-page-break/>
					<text:span text:style-name="T21">10. Ausfertigungen, Gebühren, Gerichtsstand, geltendes Recht</text:span>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P72">Die Ausfertigung dieses Vertrages erfolgt in zweifacher Ausführung. Ein Original verbleibt im zuständigen Administrationsbüro des Lehrgangs. Eine Ausfertigung wird der ao. Studentin bzw. dem ao. Studenten übergeben. </text:p>
				<text:p text:style-name="P72">Für Streitigkeiten aus diesem Vertrag gilt österreichisches Recht als vereinbart, allfällige Klagen gegen den Erhalter sind beim sachlich zuständigen Gericht in Wien einzubringen.</text:p>
				<text:p text:style-name="P72"/>
				<text:p text:style-name="P72">Die englische Übersetzung des deutschsprachigen Vertrages dient nur als Referenz. Rechtsgültigkeit hat ausschließlich der deutsche Vertrag.</text:p>
				<text:p text:style-name="P72"/>
				<text:p text:style-name="P72">Der Ausbildungsvertrag ist gebührenfrei.</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P95">
					<text:span text:style-name="T21">10. Copies, Fees, Place of Jurisdiction, Applicable Law</text:span>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P72">This contract is provided in duplicate. An original remains with the competent administrations office of the Certificate Program for Further Education. A copy is given to the external student. </text:p>
				<text:p text:style-name="P72">In respect of any disputes arising from this contract, Austrian law shall apply. Any complaints about the operator should be introduced at the competent court in Vienna.</text:p>
				<text:p text:style-name="P72"/>
				<text:p text:style-name="P72">The English translation of the German contract is intended as a reference only. Only the German version of this contract is legally valid in a Court of Law.</text:p>
				<text:p text:style-name="P72"/>
				<text:p text:style-name="P72">The training contract is free of charge. </text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P13"/>
			</text:section>
			<text:section text:style-name="Sect3" text:name="Bereich4">
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P22">Wien (Vienna), <xsl:value-of select="datum_aktuell"/></text:p>
				<text:p text:style-name="P30">Ort, Datum (City, Date) </text:p>
				<text:p text:style-name="P22"/>
				<table:table table:name="Tabelle1" table:style-name="Tabelle1">
					<table:table-column table:style-name="Tabelle1.A"/>
					<table:table-column table:style-name="Tabelle1.B"/>
					<table:table-column table:style-name="Tabelle1.C"/>
					<table:table-row table:style-name="Tabelle1.1">
						<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
							<text:p text:style-name="P24"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P25"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
							<text:p text:style-name="P32"/>
							<text:p text:style-name="P31"/>
							<text:p text:style-name="P23"/>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle1.1">
						<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
							<text:p text:style-name="P28">
								<text:span text:style-name="T25">Die ao. Studentin/der ao. Student /<text:line-break/>ggf. gesetzliche VertreterInnen</text:span>
							</text:p>
							<text:p text:style-name="P28">
								<text:span text:style-name="T27">(The external student /</text:span>
								<text:span text:style-name="T19">
									<text:line-break/>
								</text:span>
								<text:span text:style-name="T27">if necessary legal representatives)</text:span>
							</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P27"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
							<text:p text:style-name="P26">Für die FH Technikum Wien</text:p>
							<text:p text:style-name="P26"/>
							<text:p text:style-name="P29">
								<text:span text:style-name="T26">(For the UAS Technikum Wien)</text:span>
							</text:p>
						</table:table-cell>
					</table:table-row>
				</table:table>
				<text:p/>
			</text:section>
		</office:text>
</xsl:template>
</xsl:stylesheet>
