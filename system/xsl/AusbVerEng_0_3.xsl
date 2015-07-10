<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
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
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Header">
			<style:text-properties fo:language="de" fo:country="AT" style:language-asian="de" style:country-asian="AT"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" >
				<style:tab-stops>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="0cm"/>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="0cm"/>
					<style:tab-stop style:position="0.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="start" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:language="de" fo:country="AT"/>
		</style:style>
		<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:language="de" fo:country="AT" style:language-asian="ar" style:country-asian="SA" style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" fo:font-weight="bold" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" fo:font-weight="bold" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%">
				<style:tab-stops>
					<style:tab-stop style:position="8.752cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%">
				<style:tab-stops>
					<style:tab-stop style:position="8.752cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="7pt" fo:language="en" fo:country="US" style:font-size-asian="7pt" style:font-name-complex="Arial" style:font-size-complex="7pt"/>
		</style:style>
		<style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:background-color="transparent"/>
		</style:style>
		<style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties fo:font-size="10pt" fo:background-color="transparent" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P20" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P21" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P22" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P23" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P24" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P25" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
					<style:tab-stop style:position="14.503cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P26" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P27" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
					<style:tab-stop style:position="14.503cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P28" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
					<style:tab-stop style:position="14.503cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P29" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P30" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0.416cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P31" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0.416cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P32" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P33" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P34" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P35" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P36" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P37" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P38" style:family="paragraph" style:parent-style-name="Textkörper_20_3">
			<style:paragraph-properties fo:line-height="130%" fo:orphans="0" fo:widows="0"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P39" style:family="paragraph" style:parent-style-name="Heading_20_1" style:list-style-name="" style:master-page-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false" style:page-number="auto"/>
			<style:text-properties style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="P40" style:family="paragraph" style:parent-style-name="Heading_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
		</style:style>
		<style:style style:name="P41" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="start" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P42" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P43" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P44" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P45" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001534a7"/>
		</style:style>
		<style:style style:name="P46" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P47" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P48" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P49" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P50" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="0012841f" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P51" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P52" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00159b76" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P53" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001892c5" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P54" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00159b76" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P55" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00161927" officeooo:paragraph-rsid="00161927" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P56" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P57" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P58" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="normal" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="P59" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P60" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P61" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P62" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:language-asian="zxx" style:country-asian="none" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P63" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00159b76" fo:background-color="#ffff00" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P64" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001892c5"/>
		</style:style>
		<style:style style:name="P65" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="column"/>
			<style:text-properties officeooo:paragraph-rsid="001534a7"/>
		</style:style>
		<style:style style:name="P66" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="column"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P67" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P68" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P69" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P70" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P71" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00159b76" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P72" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="page"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P73" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P74" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P75" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P76" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P77" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P78" style:family="paragraph" style:parent-style-name="Standard1">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
					<style:tab-stop style:position="2.501cm"/>
					<style:tab-stop style:position="3.752cm"/>
					<style:tab-stop style:position="5.002cm"/>
					<style:tab-stop style:position="6.253cm"/>
					<style:tab-stop style:position="7.504cm"/>
					<style:tab-stop style:position="8.754cm"/>
					<style:tab-stop style:position="10.005cm"/>
					<style:tab-stop style:position="11.255cm"/>
					<style:tab-stop style:position="12.506cm"/>
					<style:tab-stop style:position="13.757cm"/>
					<style:tab-stop style:position="15.007cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P79" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P80" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P81" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P82" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P83" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="WW8Num4">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P84" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false" fo:break-before="page"/>
			<style:text-properties fo:language="en" fo:country="US" style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="P85" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P86" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P87" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P88" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0.4cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="-0.4cm" style:auto-text-indent="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P89" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0.4cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="-0.4cm" style:auto-text-indent="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P90" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0.4cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="-0.4cm" style:auto-text-indent="false" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
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
			<style:text-properties fo:font-weight="bold"/>
		</style:style>
		<style:style style:name="T6" style:family="text">
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T7" style:family="text">
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T8" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:font-weight="bold" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T9" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="001892c5" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T10" style:family="text">
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T11" style:family="text">
			<style:text-properties fo:font-size="10pt" officeooo:rsid="001534a7" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T12" style:family="text">
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:language-asian="zxx" style:country-asian="none" style:font-name-complex="Arial" style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="T13" style:family="text">
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="T14" style:family="text">
			<style:text-properties fo:font-size="10pt" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T15" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T16" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" officeooo:rsid="0012841f" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T17" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" officeooo:rsid="001534a7" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T18" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T19" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T20" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" fo:background-color="transparent" loext:char-shading-value="0" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T21" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" fo:font-weight="normal" style:font-size-asian="8.75pt" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="italic" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="T22" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T23" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="GB" style:font-size-asian="10pt" style:language-asian="zxx" style:country-asian="none" style:font-name-complex="Arial" style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="T24" style:family="text">
			<style:text-properties style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T25" style:family="text">
			<style:text-properties style:font-name-asian="Arial"/>
		</style:style>
		<style:style style:name="T26" style:family="text">
			<style:text-properties officeooo:rsid="001534a7" style:font-name-asian="Arial"/>
		</style:style>
		<style:style style:name="T27" style:family="text">
			<style:text-properties fo:language="en" fo:country="US"/>
		</style:style>
		<style:style style:name="T28" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-name-asian="Arial"/>
		</style:style>
		<style:style style:name="T29" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T30" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" style:font-name-asian="Arial" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T31" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-style-complex="italic"/>
		</style:style>
		<style:style style:name="T32" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="T33" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T34" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T35" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-name-asian="Arial" style:font-size-asian="9pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T36" style:family="text">
			<style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="9pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T37" style:family="text">
			<style:text-properties fo:language="de" fo:country="AT" fo:font-weight="bold" style:language-asian="ar" style:country-asian="SA" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T38" style:family="text">
			<style:text-properties style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="T39" style:family="text">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T40" style:family="text">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="001534a7" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T41" style:family="text">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T42" style:family="text">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T43" style:family="text">
			<style:text-properties fo:color="#000000" style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T44" style:family="text">
			<style:text-properties style:font-name="Times New Roman" fo:font-size="7pt" fo:language="en" fo:country="US" fo:font-weight="bold" style:font-size-asian="7pt" style:font-weight-asian="bold" style:font-name-complex="Times New Roman"/>
		</style:style>
		<style:style style:name="T45" style:family="text">
			<style:text-properties fo:background-color="transparent" loext:char-shading-value="0"/>
		</style:style>
		<style:style style:name="T46" style:family="text">
			<style:text-properties officeooo:rsid="001534a7"/>
		</style:style>
		<style:style style:name="T47" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T48" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" loext:char-shading-value="0" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T49" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" fo:background-color="transparent" loext:char-shading-value="0" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T50" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T51" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="normal" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="T52" style:family="text">
			<style:text-properties officeooo:rsid="0012841f"/>
		</style:style>
		<style:style style:name="T53" style:family="text">
			<style:text-properties style:language-asian="ar" style:country-asian="SA"/>
		</style:style>
		<style:style style:name="T54" style:family="text">
			<style:text-properties officeooo:rsid="00179141" style:language-asian="ar" style:country-asian="SA"/>
		</style:style>
		<style:style style:name="T55" style:family="text">
			<style:text-properties style:language-asian="zxx" style:country-asian="none"/>
		</style:style>
		<style:style style:name="T56" style:family="text">
			<style:text-properties officeooo:rsid="00179141"/>
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
        <office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
			</text:sequence-decls>
			<text:section text:style-name="Sect1" text:name="Bereich1">
				<text:h text:style-name="P39" text:outline-level="1">Ausbildungsvertrag</text:h>
				<text:p text:style-name="P10"/>
				<text:p text:style-name="P10"/>
				<text:p text:style-name="P10"/>
				<text:p text:style-name="P43">Dieser Vertrag regelt das Rechtsverhältnis zwischen </text:p>
				<text:p text:style-name="P44">
					<text:span text:style-name="T7">dem Verein Fachhochschule Technikum Wien,</text:span>
					<text:span text:style-name="T24">1060 Wien, Mariahilfer Straße 37-39 (kurz „Erhalter“ genannt) einerseits </text:span>
					<text:span text:style-name="T7">und</text:span>
				</text:p>
				<text:p text:style-name="P43"/>
				<text:h text:style-name="P40" text:outline-level="1">
					<text:span text:style-name="T38">Training Contract</text:span>
				</text:h>
				<text:p text:style-name="P10"/>
				<text:p text:style-name="P10"/>
				<text:p text:style-name="P10"/>
				<text:p text:style-name="P47">This contract governs the legal relationship between </text:p>
				<text:p text:style-name="P47">
					<text:span text:style-name="T6">the University of Applied Sciences Technikum Wien Association,</text:span> 1060 Vienna, Mariahilferstraße 37-39 (referred to as &quot;operator&quot;) on the one hand <text:span text:style-name="T6">and</text:span>
				</text:p>
				<text:p text:style-name="P58"/>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47"/>
			</text:section>
			<text:section text:style-name="Sect2" text:name="Bereich2">
				<text:p text:style-name="P8">
					<text:span text:style-name="T19">Familienname (Surname):<text:tab/><xsl:value-of select="nachname"/></text:span>
				</text:p>
				<text:p text:style-name="P41">Vorname (First Name):<text:tab/><xsl:value-of select="vorname"/></text:p>
				<text:p text:style-name="P8">
					<text:span text:style-name="T19">Akademischer Titel (Academic degree):<text:tab/><xsl:value-of select="titelpre"/><xsl:value-of select="titelpost"/></text:span>
				</text:p>
				<text:p text:style-name="P8">
					<text:span text:style-name="T19">Adresse (Address):<text:tab/><xsl:value-of select="strasse"/></text:span>
					<text:span text:style-name="T19">; </text:span>
				</text:p>
				<text:p text:style-name="P8">
					<text:span text:style-name="T10">
						<text:tab/><xsl:value-of select="plz"/></text:span>
				</text:p>
				<text:p text:style-name="P5">
					<text:span text:style-name="T19">Geburtsdatum (Date of birth): <text:tab/><xsl:value-of select="gebdatum"/></text:span>
				</text:p>
				<text:p text:style-name="P5">
					<text:span text:style-name="T10">Sozialversicherungsnr. </text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T10">
							<text:note text:id="ftn1" text:note-class="footnote">
								<text:note-citation>1</text:note-citation>
								<text:note-body>
									<text:p text:style-name="Standard">
										<text:span text:style-name="T25">
											<text:s/>
										</text:span>
										<text:span text:style-name="T2">Gemäß § 3 Absatz 1 des Bildungsdokumentationsgesetzes (BGBl. I Nr. 12/2002 idgF) und der Bildungsdokumentationsverordnung-Fachhochschulen <text:s/>(BGBl. II Nr. 29/2004 idgF) hat der Erhalter die Sozialversicherungsnummer zu erfassen und gemäß § 7 Absatz 2 im Wege der Agentur für Qualitätssicherung und Akkreditierung Austria an das zuständige Bundesministerium und die Bundesanstalt Statistik Österreich zu übermitteln.</text:span>
									</text:p>
									<text:p text:style-name="P9"/>
								</text:note-body>
							</text:note>
						</text:span>
					</text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T10"/>
					</text:span>
					<text:span text:style-name="T22">:<text:tab/><xsl:value-of select="svnr"/></text:span>
				</text:p>
				<text:p text:style-name="P6">
					<text:span text:style-name="T19">
						<text:tab/>(Social security number)</text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T19">
							<text:note text:id="ftn2" text:note-class="footnote">
								<text:note-citation>2</text:note-citation>
								<text:note-body>
									<text:p text:style-name="Standard">
										<text:span text:style-name="T28">
											<text:s/>
										</text:span>
										<text:span text:style-name="T4">Pursuant to § 3 section 1 of the Education Documentation Act (Federal Law Gazette I No. 12/2002 as amended) and the Education Documentation Regulation for Universities of Applied Sciences (Federal Law Gazette II No. 29/2004 as amended), the operator shall record the social security number pursuant to § 7 paragraph 2 and shall transfer it via the Agency for Quality Assurance and Accreditation Austria to the competent Ministry and Statistics Austria.</text:span>
									</text:p>
								</text:note-body>
							</text:note>
						</text:span>
					</text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T19"/>
					</text:span>
					<text:span text:style-name="T19">: </text:span>
				</text:p>
				<text:p text:style-name="P5">
					<text:span text:style-name="T19">Personenkennz. (Personal identifier): <text:tab/><xsl:value-of select="matrikelnr"/></text:span>
				</text:p>
				<text:p text:style-name="P16"/>
				<text:p text:style-name="P16"/>
				<text:p text:style-name="P3">
					<text:span text:style-name="T19">(kurz „Studentin“ bzw. „Student“ genannt) <text:tab/>
					</text:span>
					<text:span text:style-name="T15">(referred to as &quot;student&quot;)</text:span>
					<text:span text:style-name="T15">on the other,</text:span>
				</text:p>
				<text:p text:style-name="P3">
					<text:span text:style-name="T19">andererseits, <text:tab/>
					</text:span>
				</text:p>
				<text:p text:style-name="P15"/>
				<text:p text:style-name="P14"/>
			</text:section>
			<text:section text:style-name="Sect1" text:name="Bereich3">
				<text:p text:style-name="P2">
					<text:span text:style-name="T19">im Rahmen des <xsl:value-of select="studiengang_typ"/>-Studienganges </text:span>
					<text:span text:style-name="T49">„<xsl:value-of select="studiengang"/>“</text:span>
					<text:span text:style-name="T47"/>
					<text:span text:style-name="T49">, StgKz <xsl:value-of select="studiengang_kz"/>, </text:span>
					<text:span text:style-name="T48">in der Organisationsform eines </text:span>
					<text:span text:style-name="T49">
					<xsl:choose>
						<xsl:when test="orgform = 'BB'" >
							berufsbegleitenden Studiums.
						</xsl:when>
						<xsl:when test="orgform = 'VZ'" >
							Vollzeitstudiums.
						</xsl:when>
						<xsl:otherwise>
							Fernstudiums.
						</xsl:otherwise>
					</xsl:choose>
					</text:span>
				</text:p>
				<text:p text:style-name="P68">within the <xsl:value-of select="studiengang_typ"/> degree program 
					<text:span text:style-name="T6">„<xsl:value-of select="studiengang_englisch"/>“, program abbrev. <xsl:value-of select="studiengang_kz"/>,</text:span> in the organizational form of a 
					<text:span text:style-name="T6">
					<xsl:choose>
						<xsl:when test="orgform = 'BB'" >
							part-time course.
						</xsl:when>
						<xsl:when test="orgform = 'VZ'" >
							full-time course.
						</xsl:when>
						<xsl:otherwise>
							distance learning course.
						</xsl:otherwise>
					</xsl:choose>
					</text:span>
				</text:p>
				<text:p text:style-name="P73"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T29">1. Ausbildungsort</text:span>
				</text:p>
				<text:p text:style-name="P46"/>
				<text:p text:style-name="P48">Studienort sind die Räumlichkeiten der FH Technikum Wien, 1200 Wien, Höchstädtplatz und 1210 Wien, Giefinggasse. Bei Bedarf kann der Erhalter einen anderen Studienort festlegen. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T29">2. Vertragsgrundlage</text:span>
				</text:p>
				<text:p text:style-name="P11"/>
				<text:p text:style-name="P48">Die Ausbildung erfolgt auf der Grundlage des Fachhochschul-Studiengesetzes, BGBl. Nr. 340/1993 idgF, des Hochschul-Qualitätssicherungsgesetzes, BGBl. Nr. 74/2011 idgF, des Akkreditierungsbescheides des Board der AQ Austria vom 9.5.2012, GZ FH12020016 idgF und des Fördervertrags mit dem Bundesministerium für Wissenschaft und Forschung idgF. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="Standard">
					<text:bookmark-start text:name="_Ref78860434"/>
					<text:span text:style-name="T29">3. Ausbildungsdauer</text:span>
					<text:bookmark-end text:name="_Ref78860434"/>
				</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">Die Ausbildungsdauer beträgt <xsl:value-of select="student_maxsemester"/> Semester.</text:p>
				<text:p text:style-name="P61"/>
				<text:p text:style-name="P61">Nachgewiesene erworbene Kenntnisse können auf einzelne Lehrveranstaltungen angerechnet werden bzw. zum Erlass einer Lehrveranstaltung oder des Berufspraktikums führen. Hierzu bedarf es eines Antrages der Studentin bzw. des Studenten und der nachfolgenden Feststellung der inhaltlichen und umfänglichen Gleichwertigkeit durch die Studiengangsleitung. </text:p>
				<text:p text:style-name="P61"/>
				<text:p text:style-name="P67"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T29">1. Place of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"/>
				</text:p>
				<text:p text:style-name="P59"/>
				<text:p text:style-name="P61">Places of training are the premises of the UAS Technikum Wien, 1200 Vienna, Höchstädt-platz and 1210 Vienna, Giefinggasse. If necessary, the operator may specify a different place of study. </text:p>
				<text:p text:style-name="P61"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T29">2. Contractual Basis</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"/>
				</text:p>
				<text:p text:style-name="P61"/>
				<text:p text:style-name="P61">The training is based on the University of Applied Sciences Studies Act, Federal Law Gazette No. 340/1993 as amended, the Higher Education Quality Assurance Act, Federal Law Gazette I No. 74/2011 as amended, the notification of accreditation by the Board of AQ Austria from 9.5.2012, GZ FH12020016 as amended and the subsidy agreement with the Federal Ministry responsible for UASs as amended.</text:p>
				<text:p text:style-name="P61"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T29">3. Duration of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"/>
				</text:p>
				<text:p text:style-name="P61"/>
				<text:p text:style-name="P48">
					<text:span text:style-name="T45">The training period lasts <xsl:value-of select="student_maxsemester"/> semesters.</text:span>
				</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">Demonstration of knowledge acquired can be accredited to individual courses or lead to exemption from a course or internship. This requires an application by the student and the subsequent establishment by the Program Director of the content and extent of equivalence. </text:p>
				<text:p text:style-name="P84"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T29">4. Ausbildungsabschluss</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"/>
				</text:p>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P48">Die Ausbildung endet mit der positiven Absolvierung der das jeweilige Studium abschließenden kommissionellen Prüfung. Nach dem Abschluss der vorgeschriebenen Prüfungen wird der akademische Grad Bachelor/Master of Science in Engineering (BSc/MSc) durch das FH-Kollegium verliehen. </text:p>
				<text:p text:style-name="P38"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T29">5. Rechte und Pflichten des Erhalters</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:bookmark-start text:name="_Ref78865698"/>
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8"/>
					</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">5.1 Rechte</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"/>
					<text:bookmark-end text:name="_Ref78865698"/>
				</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P49">Der Erhalter führt eine periodische Überprüfung des Studiums im Hinblick auf Relevanz und Aktualität durch und ist im Einvernehmen mit dem FH-Kollegium berechtigt, daraus Änderungen des akkreditierten Studienganges abzuleiten.</text:p>
				<text:p text:style-name="P49"/>
				<text:p text:style-name="P49">
					<text:span text:style-name="T46">Der Erhalter ist berechtigt, die Daten der/des Studierenden an den FH Technikum Wien Alumni Club zu übermitteln. Der Alumni Club ist der AbsolventInnenverein der FH Technikum Wien. Er hat zum Ziel, AbsolventInnen, Studierende und Lehrende miteinander zu vernetzen sowie AbsolventInnen laufend über Aktivitäten an der FH Technikum Wien zu informieren. Einer Zusendung von Informationen durch den Alumni Club kann jederzeit widersprochen werden.</text:span>
				</text:p>
				<text:p text:style-name="P49"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">5.2 Pflichten</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"/>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T50"/>
					</text:span>
				</text:p>
				<text:list xml:id="list7740817648153666949" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P81">Der Erhalter ist verpflichtet, all jene Voraussetzungen zu bieten, damit das Studium innerhalb der Ausbildungsdauer (Pkt. 3) erfolgreich abgeschlossen werden kann. </text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P65"/>
				<text:p text:style-name="P45">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T31">4. Formal Completion of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"/>
				</text:p>
				<text:p text:style-name="P49"/>
				<text:p text:style-name="P49">The training ends with the positive completion of the final examination before a committee for the respective course. After completion of the required examinations, the academic degree Bachelor / Master of Science in Engineering (BSc / MSc) is awarded by the University of Applied Sciences Council. </text:p>
				<text:p text:style-name="P86"/>
				<text:p text:style-name="P45">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T29">5. Rights and Duties of the Operator</text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P45">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T33"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P64">
					<text:bookmark-start text:name="_Ref788656981"/>
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P64">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">5.1 Rights</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"/>
					<!--<text:bookmark-end text:name="_Ref788656981"/>-->
				</text:p>
				<text:p text:style-name="P53">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T51"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P49">The operator performs a periodic review of the course in terms of relevance and topicality, and is authorized, in consultation with the University of Applied Sciences Council, to deduce from this changes in the accredited degree program.</text:p>
				<text:p text:style-name="P49"/>
				<text:p text:style-name="P49">
					<text:span text:style-name="T46">The operator is entitled to communicate a student’s data to the UAS Technikum Wien Alumni Club. The Alumni Club is the graduate association of the UAS Technikum Wien. Its goal is to provide links between graduates, students and lecturers as well as to keep graduates informed of the activities at the UAS Technikum Wien. A mailing of information from the Alumni Club can be vetoed at any time.</text:span>
				</text:p>
				<text:p text:style-name="P49"/>
				<text:p text:style-name="P49"/>
				<text:p text:style-name="P49"/>
				<text:p text:style-name="P45">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">5.2 Duties </text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P45">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T50"/>
					</text:span>
				</text:p>
				<text:list xml:id="list111613939151866" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P81">The operator undertakes to provide all the necessary conditions for the study to be successfully completed within the duration of training (point 3).</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P65"/>
				<text:list xml:id="list111613941182599" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P81">Die Voraussetzungen zur Erfüllung dieser Verpflichtung sind Gegenstand des akkreditierten Studienganges idgF, der Satzung der FH Technikum Wien idgF und der Hausordnung idgF. </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Der Erhalter ist weiters verpflichtet, das Studium auf der Grundlage höchster Qualitätsansprüche hinsichtlich der Erreichung der Ausbildungsziele zu gestalten und allfällige Änderungen des akkreditierten Studienganges bekannt zu geben.</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P83">Der Erhalter verpflichtet sich zur sorgfaltsgemäßen Verwendung der personenbezogenen Daten der Studierenden. Die Daten werden nur im Rahmen der gesetzlichen und vertraglichen Verpflichtungen sowie des Studienbetriebes verwendet und nicht an nicht berechtigte Dritte weitergegeben.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P87"/>
				<text:list xml:id="list111613942177494" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P80">
							<text:s/>The conditions for the fulfillment of this obligation are the subject of the accredited study program as amended, the Statutes of the UAS Technikum Wien as amended, and the House Rules as amended. </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P79">The operator also undertakes to design the study on the basis of the highest possible quality standards as regards the achievement of the educational goals and to make known any changes to the accredited degree program. </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P83">The operator is committed to use the personal data of the students carefully. The data is only to be used within the operator’s legal and contractual obligations as well as its program of studies and is not to be handed on to unauthorized third parties.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P85"/>
				<text:p text:style-name="P72"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T29">6. Rechte und Pflichten der Studierenden</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8"/>
					</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">6.1 Rechte</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"/>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P11">Die Studentin bzw. der Student hat das Recht auf </text:p>
				<text:list xml:id="list111613944172084" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P80">einen Studienbetrieb gemäß den im akkreditierten Studiengang idgF und in der Satzung der FH Technikum Wien idgF festgelegten Bedingungen;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">ein Zeugnis über die im laufenden Semester abgelegten Prüfungen; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Unterbrechung der Ausbildung aus nachgewiesenen zwingenden persönlichen, gesundheitlichen oder beruflichen Gründen. </text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P85"/>
				<text:p text:style-name="P85"/>
				<text:p text:style-name="P2">
					<text:span text:style-name="T8">6.2 Pflichten</text:span>
				</text:p>
				<text:p text:style-name="P48">6.2.1 Studienbeitrag </text:p>
				<text:p text:style-name="P48">Die Studentin bzw. der Student ist verpflichtet, zwei Wochen vor Beginn jedes Semesters (StudienanfängerInnen: bis 20. August vor Studienbeginn) einen Studienbeitrag gemäß Fachhochschul-Studiengesetz (BGBl. Nr. 340/1993 idgF) in der Höhe von derzeit € 363,36 netto pro Semester zu entrichten. Dies gilt auch in Semestern mit DiplomandInnenenstatus o.ä. Im Falle einer Erhöhung des gesetzlichen Studienbeitrags-satzes erhöht sich der angeführte Betrag entsprechend. Bei Nichtantritt des Studiums oder Abbruch zu Beginn oder während des Semesters verfällt der Studienbeitrag. </text:p>
				<text:p text:style-name="P46">
					<text:tab/>
				</text:p>
				<text:p text:style-name="P66"/>
				<text:p text:style-name="P64">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T29">6. Rights and Duties of the Students</text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P64">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:s/>
					</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T18"/>
					</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">6.1 Rights </text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P48">The student has the right to </text:p>
				<text:list xml:id="list111613948167787" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P80">a course of study according to the conditions specified in the accredited degree program as amended and in the Statutes of the UAS Technikum Wien as amended;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">a certificate showing the examinations successfully passed in the current semester;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">interrupt the training due to proof of compelling personal, health or professional reasons.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P85"/>
				<text:p text:style-name="P85"/>
				<text:p text:style-name="P2">
					<text:span text:style-name="T8">6.2 Duties</text:span>
				</text:p>
				<text:p text:style-name="P48">6.2.1 Tuition Fees </text:p>
				<text:p text:style-name="P48">Two weeks before the beginning of each semester (new students: up to August 20 before taking up studies) the student undertakes to pay tuition fees according to the University of Applied Sciences Studies Act (Federal Law Gazette No 340/1993 as amended) currently to the sum of € 363.36 net payable per semester. This also applies in semesters with graduand status etc. In the event of an increase in the legal tuition fees rate, the amount quoted will increase accordingly. For non-commencement or termination of the study at the beginning or during the semester, the tuition fee is forfeited. </text:p>
				<text:p text:style-name="P73"/>
				<text:p text:style-name="P48">6.2.2 Studierendenbeitrag („ÖH-Beitrag“) </text:p>
				<text:p text:style-name="P48">Gemäß § 4 Abs. 10 des Fachhochschul-Studiengesetzes (BGBl. Nr. 340/1993 idgF und der Bundesministeriengesetz-Novelle 2007, BGBl. I Nr. 6/2007) gehören <text:span text:style-name="T46">ordentliche und außerordentliche </text:span>Studierende an Fachhochschul-Studiengängen der Österreichischen HochschülerInnenschaft (ÖH) gemäß Hochschülerinnen- und Hochschülerschaftsgesetz (HSG 2014) an. Daraus resultiert die Verpflichtung der Studentin oder des Studenten zur Entrichtung des ÖH-Beitrags. Dies gilt auch in Semestern mit DiplomandInnenstatus. Der Studierendenbeitrag kann jährlich durch die ÖH indexiert werden; die genaue Höhe des Studierendenbeitrags wird von der ÖH jährlich für das folgende Studienjahr bekannt gegeben. </text:p>
				<text:p text:style-name="P48">Die Einhebung des Betrags erfolgt durch die Fachhochschule. Der Erhalter überweist in Folge die eingezahlten Beträge der Studierenden ohne Abzüge an die ÖH. Die Entrichtung des Betrags ist Voraussetzung für die Zulassung zum Studium bzw. für dessen Fortsetzung. </text:p>
				<text:p text:style-name="P56"/>
				<text:p text:style-name="P48">6.2.3 Kaution </text:p>
				<text:p text:style-name="P48">Im Zuge der Einschreibung ist der Nachweis über die einbezahlte Kaution zu erbringen. </text:p>
				<text:p text:style-name="P48">Die Kaution beträgt € 150,–. </text:p>
				<text:p text:style-name="P48">Bei Nichtantritt des Studiums oder Abbruch während des ersten oder zweiten Semesters verfällt die Kaution. </text:p>
				<text:p text:style-name="P48">Bei aufrechtem Inskriptionsverhältnis zu Beginn des zweiten Semesters wird die Kaution auf den Unkostenbeitrag (siehe nächster Punkt) des ersten und zweiten Semesters angerechnet. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P69"/>
				<text:p text:style-name="P48">6.2.2 Student fee (&quot;Austrian Student Union fee&quot;)</text:p>
				<text:p text:style-name="P48">Pursuant to § 4 section 10 of the Universities of Applied Sciences Studies Act (Federal Law Gazette No 340/1993 as amended and the Federal Ministries Act - 2007 amendment, Federal Law Gazette I No. 6/2007), <text:span text:style-name="T46">internal and external </text:span>students at universities of applied sciences degree programs are members of the Austrian Students Union in accordance with the Students Act (HSG 2014).This results in the student being obliged to pay the Austrian Student Union fee. This also applies in semesters with graduand status. The student fee can be annually indexed by the Austrian Students <text:span text:style-name="T52">&apos;</text:span> Union; the exact amount of the student fee for the following year is announced annually by the Austrian Students&apos; Union. </text:p>
				<text:p text:style-name="P48">The amount is levied by the University of Applied Sciences. The operator then transfers the amounts paid by the students without deductions to the Students&apos; Union. Payment of the student fee is a pre-requisite for admission to the course or for its continuation. </text:p>
				<text:p text:style-name="P46"/>
				<text:p text:style-name="P46"/>
				<text:p text:style-name="P46"/>
				<text:p text:style-name="P48">6.2.3 Deposit </text:p>
				<text:p text:style-name="P48">During the process of registration, proof of deposit paid must be provided.</text:p>
				<text:p text:style-name="P48">The deposit is € 150,–. </text:p>
				<text:p text:style-name="P48">For non-commencement of studies or termination during the first or second semester, the deposit shall be forfeited. </text:p>
				<text:p text:style-name="P48">If, by the beginning of the second semester, registration has been completed correctly the deposit will be credited to the contribution towards expenses (see next point) for the first and second semester. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P74"/>
				<text:p text:style-name="P57">6.2.4 Unkostenbeitrag </text:p>
				<text:p text:style-name="P78">Pro Semester ist ein Unkostenbeitrag zu entrichten, wobei es sich nicht um einen Pauschalbetrag handelt. Der Unkostenbeitrag stellt eine Abgeltung für über das Normalmaß hinausgehende Serviceleistungen der FH dar, z.B. Freifächer, Beratung/Info Auslands-studium, Sponsionsfeiern, Vorträge / Job-börse, Mensa etc. </text:p>
				<text:p text:style-name="P78"/>
				<text:p text:style-name="P50">Die Höhe des Unkostenbeitrages beträgt derzeit € 75,– pro Semester. Eine allfällige Anpassung wird durch Aushang bekannt gemacht. </text:p>
				<text:p text:style-name="P48">Der Unkostenbeitrag ist 
				<xsl:choose>
						<xsl:when test="semesterStudent = 3" >
							im 
						</xsl:when>
						<xsl:otherwise>
							ab dem 
						</xsl:otherwise>
					</xsl:choose>
					3. Semester gleichzeitig mit der Studiengebühr vor Beginn des Semesters zu entrichten. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">Bei Vertragsauflösung vor Studienabschluss aus Gründen, die die Studentin bzw. der Student zu vertreten hat, oder auf deren bzw. dessen Wunsch, wird der Unkostenbeitrag zur Abdeckung der dem Erhalter erwachsenen administrativen Zusatzkosten einbehalten. </text:p>
				<text:p text:style-name="P56"/>
				<text:p text:style-name="P51">6.2.5 Lehr- und Lernbehelfe </text:p>
				<text:p text:style-name="P51">Die Anschaffung unterrichtsbezogener Literatur und individueller Lernbehelfe ist durch den Unkostenbeitrag nicht abgedeckt. Eventuelle zusätzliche Kosten, die sich beispielsweise durch die studiengangsbezogene, gemeinsame Anschaffung von Lehr- bzw. Lernbehelfen (Skripten, CDs, Bücher, Projektmaterialien, Kopierpapier etc.) oder durch Exkursionen ergeben, werden von jedem Studiengang individuell eingehoben. <text:bookmark-start text:name="_Ref78863824"/>
				</text:p>
				<text:p text:style-name="P36"/>
				<text:p text:style-name="P52">6.2.4 Contribution towards Expenses </text:p>
				<text:p text:style-name="P52">A contribution towards expenses, which is not a lump sum, is payable per semester. The contribution towards expenses represents compensation for the services provided by the UAS that go beyond the normal level, such as electives, counseling/ information about studying abroad, graduation ceremonies, lectures/job market, cafeteria, etc. </text:p>
				<text:p text:style-name="P63"/>
				<text:p text:style-name="P52">The amount of the contribution is currently <text:line-break/>€ 75,– per semester. Any possible adjustment is posted on the noticeboard. </text:p>
				<text:p text:style-name="P52">
				<xsl:choose>
					<xsl:when test="semesterStudent = 3" >
						In 
					</xsl:when>
					<xsl:otherwise>
						From 
					</xsl:otherwise>
				</xsl:choose>
				the 3rd semester the contribution towards expenses must be paid together with the tuition fees before the start of the semester. </text:p>
				<text:p text:style-name="P54"/>
				<text:p text:style-name="P52">If the contract is cancelled before graduation for reasons that are the fault of the student, or on their wishes, the contribution towards expenses shall be deducted to cover the additional administrative costs borne by the operator. </text:p>
				<text:p text:style-name="P52"/>
				<text:p text:style-name="P52">6.2.5 Teaching Aids and Learning Tools</text:p>
				<text:p text:style-name="P52">The acquisition of teaching-related literature and individual learning tools is not covered by the contribution towards expenses. Any additional costs, which arise for example from the course-related, joint purchase of teaching and / or learning materials (scripts, CDs, books, project materials, copying paper, etc.) or result from field trips, are levied by each individual degree program. </text:p>
				<text:p text:style-name="P71">
					<text:soft-page-break/>
				</text:p>
				<text:p text:style-name="P32">6.2.6 Beibringung persönlicher Daten <text:bookmark-end text:name="_Ref78863824"/>
				</text:p>
				<text:p text:style-name="P33">Die Studentin bzw. der Student ist verpflichtet, persönliche Daten beizubringen, die auf Grund eines Gesetzes, einer Verordnung oder eines Bescheides vom Erhalter <text:span text:style-name="T46">erfasst werden müssen oder zur Erfüllung des Ausbildungsvertrages bzw. für den Studienbetrieb unerlässlich sind.</text:span>
				</text:p>
				<text:p text:style-name="P34"/>
				<text:p text:style-name="P34">6.2.7 Aktualisierung eigener Daten und Bezug von Informationen</text:p>
				<text:p text:style-name="P34">Die Studentin bzw. der Student hat unaufgefordert dafür zu sorgen, dass die von ihr/ihm beigebrachten Daten aktuell sind. Änderungen sind der Studiengangsassistenz unverzüglich schriftlich mitzuteilen. Darüber hinaus trifft sie/ihn die Pflicht, sich von studienbezogenen Informationen, die ihr/ihm an die vom Erhalter zur Verfügung gestellte Emailadresse zugestellt werden, in geeigneter Weise Kenntnis zu verschaffen.</text:p>
				<text:p text:style-name="P36"/>
				<text:p text:style-name="P48">6.2.6 Providing Personal Data</text:p>
				<text:p text:style-name="P55">The student is obliged to produce personal data which must be registered because of a law, regulation or a decision by the operator, or is essential fort he fulfilling of the training contract or fort he program of studies.</text:p>
				<text:p text:style-name="P55"/>
				<text:p text:style-name="P55"/>
				<text:p text:style-name="P55"/>
				<text:p text:style-name="P55">6.2.7 Updating personal data and the retrieval of information</text:p>
				<text:p text:style-name="P55">Without being reminded, the student must ensure that the data provided by them is up-to-date. Changes are to be immediately communicated to the administrative assistant in writing. Furthermore, it is the students’ responsibility to make themselves suitably aware of information relating to their studies which has been sent to them at the email address provided for them by the operator.</text:p>
				<text:p text:style-name="P37">
					<text:span text:style-name="T53"/>
				</text:p>
				<text:p text:style-name="P32">
					<text:bookmark-start text:name="_Ref78867653"/>
					<text:span text:style-name="T53">6.2.8 Verwertungsrechte</text:span>
				</text:p>
				<text:p text:style-name="P32">Sofern nicht im Einzelfall andere Regelungen zwischen dem Erhalter und der Studentin oder dem Studenten getroffen wurden, ist die Studentin oder der Student verpflichtet, dem Erhalter die Rechte an Forschungs- und Entwicklungsergebnissen auf dessen schriftliche Anfrage hin anzubieten.</text:p>
				<text:p text:style-name="P32"/>
				<text:p text:style-name="P35">6.2.9 Aufzeichnungen und Mitschnitte</text:p>
				<text:p text:style-name="P32">Es ist der/dem Studierenden ausdrücklich untersagt, Lehrveranstaltungen als Ganzes oder nur Teile davon aufzuzeichnen und/oder mitzuschneiden (z.B. durch Film- und/oder Tonaufnahmen oder sonstige hierfür geeignete audiovisuelle Mittel). Darüber hinaus ist jede Form der öffentlichen Zurverfügungstellung (drahtlos oder drahtgebunden) der vorgenannten Aufnahmen z.B. in sozialen Netzwerken wie Facebook, StudiVZ etc, aber auch auf Youtube usw. oder durch sonstige für diese Zwecke geeignete Kommunikations-mittel untersagt. Diese Regelungen gelten sinngemäß auch für Skripten, sonstige Lernbehelfe und Prüfungsangaben. </text:p>
				<text:p text:style-name="P32">Ausgenommen hiervon ist eine Aufzeichnung zu ausschließlichen Lern-, Studien- und Forschungszwecken und zum privaten Gebrauch, sofern hierfür der Vortragende vorab ausdrücklich seine schriftliche Zustimmung erteilt hat. </text:p>
				<text:p text:style-name="P32"/>
				<text:p text:style-name="P32"/>
				<text:p text:style-name="P32">
					<text:span text:style-name="T53">6.2.10 Geheimhaltungspflicht</text:span>
					<text:bookmark-end text:name="_Ref78867653"/>
				</text:p>
				<text:p text:style-name="P48">Die Studentin bzw. der Student ist zur Geheimhaltung von Forschungs- und Entwicklungsaktivitäten und -ergebnissen gegenüber Dritten verpflichtet. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P70"/>
				<text:p text:style-name="P48">6.2.8 Exploitation Rights</text:p>
				<text:p text:style-name="P48">Unless other arrangements have been agreed between the operator and the student at an individual level, on written request, the student undertakes to offer the operator the rights to research and development results. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">6.2.9 Recordings </text:p>
				<text:p text:style-name="P48">It is expressly forbidden for the student to record lectures in part or in total (e.g. by using film and / or sound recordings or other audio-visual means suitable for this purpose). In addition, any form of making the aforementioned recordings publically available (wired or wireless) for example in social networks such as Facebook, StudiVZ etc, but also on Youtube, etc., or by other means of communication designed for these purposes is strictly prohibited. These regulations shall apply correspondingly to scripts, other learning aids and examination data. </text:p>
				<text:p text:style-name="P48">The only exception is a recording exclusively for the purpose of learning, study and research and for personal use, provided that the lecturer has expressly granted his / her prior written consent.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">6.2.10 Confidentiality </text:p>
				<text:p text:style-name="P48">The student is required to maintain confidentiality towards third parties of research and development activities and results. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P74"/>
				<text:p text:style-name="P48">6.2.11 Unfallmeldung </text:p>
				<text:p text:style-name="P48">Im Falle eines Unfalles mit körperlicher Verletzung des/der Studierenden im Zusammenhang mit dem Studium ist die/der Studierende verpflichtet, innerhalb von drei Tagen eine Meldung am Studiengangssekretariat einzubringen. Dies betrifft auch Wegunfälle zur oder von der FH. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P2">
					<text:span text:style-name="T29">7. Beendigung des Vertrages</text:span>
				</text:p>
				<text:p text:style-name="P2">
					<text:span text:style-name="T8">7.1 Auflösung im beiderseitigen Einvernehmen</text:span>
				</text:p>
				<text:p text:style-name="P7">
					<text:span text:style-name="T10">Im beiderseitigen Einvernehmen ist die Auflösung des Ausbildungsvertrages jederzeit ohne Angabe von Gründen möglich. Die einvernehmliche Auflösung bedarf der Schriftform. </text:span>
				</text:p>
				<text:p text:style-name="P51"/>
				<text:p text:style-name="P7">
					<text:span text:style-name="T8">7.2 Kündigung durch die Studentin bzw. den Studenten</text:span>
				</text:p>
				<text:p text:style-name="P51">Die Studentin bzw. der Student kann den Ausbildungsvertrag schriftlich jeweils zum Ende eines Semesters kündigen. </text:p>
				<text:p text:style-name="P51"/>
				<text:p text:style-name="P7">
					<text:span text:style-name="T8">7.3 Ausschluss durch den Erhalter</text:span>
				</text:p>
				<text:p text:style-name="P48">Der Erhalter kann die Studentin bzw. den Studenten aus wichtigem Grund mit sofortiger Wirkung vom weiteren Studium ausschließen, und zwar beispielsweise wegen </text:p>
				<text:list xml:id="list111613976179076" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P80">nicht genügender Leistung im Sinne der Prüfungsordnung;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">mehrmalige<text:span text:style-name="T56">m</text:span> unentschuldigte<text:span text:style-name="T56">n</text:span> Verletzen der Anwesenheitspflicht ; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">wiederholte<text:span text:style-name="T56">m</text:span> Nichteinhalten von Prüfungsterminen und Abgabeterminen für Seminararbeiten, Projektarbeiten etc.; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">schwerwiegender bzw. wiederholter Verstöße gegen die Hausordnung;</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P88"/>
				<text:p text:style-name="P89"/>
				<text:p text:style-name="P48">6.2.1<text:span text:style-name="T56">1 </text:span>Accident Report </text:p>
				<text:p text:style-name="P48">In the event of an accident with bodily injury to a student in connection with his / her studies, he / she is obliged to bring a report to the administrative assistant of the degree program within three days. This also applies to accidents on the way to or from the UAS. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P2">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T29">7. Termination of the contract </text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P12">
					<text:span text:style-name="T8">7.1 Annulment by Mutual Agreement</text:span>
				</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">By mutual consent, the annulment of the training contract is possible at any time, without notice and for any reason. The amicable annulment must be put down in writing. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P4">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">7.2 Termination by the Student</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"/>
				</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">The student may terminate the training contract in writing at the end of each semester.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P13">
					<text:span text:style-name="T8">7.3 Expulsion by the Operator</text:span>
				</text:p>
				<text:p text:style-name="P48">The operator may exclude the student from further study with immediate effect for good cause, for example because of</text:p>
				<text:p text:style-name="P48"/>
				<text:list xml:id="list111613982159343" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P80">insufficient achievement for the purposes of the examination regulations;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">repeated unexcused violation of the compulsory attendance regulation;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">repeated non-compliance with examination dates and deadlines for seminar papers, project work etc.; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">serious or repeated violation of the house rules;</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P88"/>
				<text:p text:style-name="P90"/>
				<text:list xml:id="list111613984164542" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P80">persönliche<text:span text:style-name="T56">m</text:span> Verhalten, das zu einer Beeinträchtigung des Images und/oder Betriebes des Studienganges, der Fach-hochschule bzw. des Erhalters oder von Personen führt, die für die Fachhochschule bzw. den Erhalter tätig sind;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Verletzung der Verpflichtung, dem Erhalter die Rechte an Forschungs- und Entwicklungsergebnissen anzubieten (siehe Pkt. 6.2.8);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Verletzung der Geheimhaltungspflicht (siehe Pkt. 6.2.10); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">strafgerichtlicher Verurteilung (wobei die Art des Deliktes und der Grad der Schuld berücksichtigt werden);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Nichterfüllung finanzieller Verpflichtungen trotz Mahnung (z.B. Unkostenbeitrag, Studienbeitrag etc.); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Weigerung zur Beibringung von Daten (siehe Pkt. 6.2.6) </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Plagiieren im Rahmen wissenschaftlicher Arbeiten </text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">Der Ausschluss kann mündlich erklärt werden. Mit Ausspruch des Ausschlusses endet der Ausbildungsvertrag, es sei denn, es wird ausdrücklich auf einen anderen Endtermin hingewiesen. Eine schriftliche Bestätigung des Ausschlusses wird innerhalb von zwei Wochen nach dessen Ausspruch per Post an die bekannt gegebene Adresse abgeschickt oder auf andere geeignete Weise übermittelt. </text:p>
				<text:p text:style-name="P48">Gleichzeitig mit dem Ausspruch des Ausschlusses kann auch ein Hausverbot verhängt werden. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P4">
					<text:span text:style-name="T29">7.4 Erlöschen</text:span>
				</text:p>
				<text:p text:style-name="P48">Der Ausbildungsvertrag erlischt mit der Verleihung des akademischen Grades. </text:p>
				<text:p text:style-name="P70"/>
				<text:list xml:id="list111613988165516" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P80">personal behavior, which leads to an adverse effect on the image and / or operation of the course, the university or the operator or on persons who are working for the university or the operator;<text:line-break/>
						</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">breach of the obligation to offer the operator the rights to research and development results (see Section 6.2.8);<text:line-break/>
						</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">breach of confidentiality (see Section 6.2.10);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">a criminal conviction (whereby the nature of the offence and the level of culpability are taken into account);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">non-fulfilment of the financial obligations, despite a reminder (e.g. contribution towards expenses, tuition fees , etc.); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">refusal to provide any data (see section 6.2.6); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">plagiarism in academic work.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">The expulsion can be explained verbally. Once notice of the expulsion has been given the training contract ends unless another deadline is explicitly made clear. Within two weeks of notice being given, written confirmation of the expulsion is mailed by post to the address provided or transmitted in any other appropriate manner.</text:p>
				<text:p text:style-name="P48">Simultaneously with notice of expulsion being given an exclusion order from entering the building may also be imposed.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P4">
					<text:span text:style-name="T29">7.4 Expiry</text:span>
				</text:p>
				<text:p text:style-name="P48">This contract expires with the award of the degree. </text:p>
				<text:p text:style-name="P21"/>
				<xsl:if test="studiengangSprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
					<text:p text:style-name="Standard">
						<text:span text:style-name="T29">8. Ergänzende Vereinbarungen</text:span>
					</text:p>
					<text:p text:style-name="P47"/>
					<text:p text:style-name="P60">
						<text:span text:style-name="T55">Das gesamte Studienprogramm wird in englischer Sprache angeboten. Die Studentin bzw. der Student erklärt, die englische Sprache in Wort und Schrift in dem für eine akademische Ausbildung erforderlichen Ausmaß zu beherrschen.</text:span>
					</text:p>
					<text:p text:style-name="P75"/>
					<text:p text:style-name="P61">Studierende des Studiengangs sind verpflichtet, eine EDV-Ausstattung zu beschaffen und zu unterhalten, die es ermöglicht, an den Fernlehrelementen teilzunehmen. Die gesamten Kosten der Anschaffung und des Betriebs (inkl. Kosten für Internet und e-mail) trägt der Student bzw. die Studentin. </text:p>
					<text:p text:style-name="P61"/>
				</xsl:if>
				<text:p text:style-name="P17">
					<text:span text:style-name="T29">
					<xsl:choose>
						<xsl:when test="studiengangSprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')" >
							9. 
						</xsl:when>
						<xsl:otherwise>
							8. 
						</xsl:otherwise>
					</xsl:choose>
					Unwirksamkeit von Vertrags-bestimmungen, Vertragslücke</text:span>
				</text:p>
				<text:p text:style-name="P61"/>
				<text:p text:style-name="P61">Sollten einzelne Bestimmungen dieses Vertrages unwirksam oder nichtig sein oder werden, so berührt dies die Gültigkeit der übrigen Bestimmungen dieses Vertrages nicht. </text:p>
				<text:p text:style-name="P61">Die Vertragsparteien verpflichten sich, unwirksame oder nichtige Bestimmungen durch neue Bestimmungen zu ersetzen, die dem in den unwirksamen oder nichtigen Bestimmungen enthaltenen Regelungsgehalt in rechtlich zulässiger Weise gerecht werden. Zur Ausfüllung einer allfälligen Lücke verpflichten sich die Vertragsparteien, auf die Etablierung angemessener Regelungen in diesem Vertrag hinzuwirken, die dem am nächsten kommen, was sie nach dem Sinn und Zweck des Vertrages bestimmt hätten, wenn der Punkt von ihnen bedacht worden wäre. </text:p>
				<text:p text:style-name="P61"/>
				<text:p text:style-name="P19"/>
				<xsl:if test="studiengangSprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
					<text:p text:style-name="P17">
						<text:span text:style-name="T29">8. Supplementary Agreements</text:span>
					</text:p>
					<text:p text:style-name="P59"/>
					<text:p text:style-name="P60">
						<text:span text:style-name="T55">The entire degree program is offered in English. The student declares he / she masters the</text:span>
						<text:span text:style-name="T55">English language in word and in writing to the extent necessary for an academic degree program.</text:span>
					</text:p>
					<text:p text:style-name="P62"/>
					<text:p text:style-name="P62"/>
					<text:p text:style-name="P60">
						<text:span text:style-name="T55">Students in the program are required to obtain and maintain computer equipment which allows them to participate in the distance learning elements. The total cost of acquisition and operation (including costs for Internet and e-mail) shall be borne by the student. </text:span>
					</text:p>
					<text:p text:style-name="P61"/>
					<text:p text:style-name="P61"/>
					<text:p text:style-name="P61"/>
				</xsl:if>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T29">
					<xsl:choose>
						<xsl:when test="studiengangSprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')" >
							9. 
						</xsl:when>
						<xsl:otherwise>
							8. 
						</xsl:otherwise>
					</xsl:choose>
					</text:span>
					<text:span text:style-name="T30"> Invalidity of Contractual Provisions, Contractual Gap</text:span>
				</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">If any provision of this agreement should be or become invalid or void, this shall not affect the validity of the remaining provisions of this agreement.</text:p>
				<text:p text:style-name="P48">The parties undertake to replace the invalid or void provisions with new provisions that meet the content of the rules contained in the invalid or void provisions in a legally permissible manner. To fill a possible gap, the parties undertake to work towards the establishment of appropriate regulations in this contract, which come closest to what they would have determined in terms of meaning and purpose of the contract, if the point had been considered by them. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T29">
					<xsl:choose>
						<xsl:when test="studiengangSprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')" >
							10. 
						</xsl:when>
						<xsl:otherwise>
							9. 
						</xsl:otherwise>
					</xsl:choose>
					Ausfertigungen, Gebühren, Gerichtsstand</text:span>
				</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">Die Ausfertigung dieses Vertrages erfolgt in zweifacher Ausführung. Ein Original verbleibt im zuständigen Administrationsbüro des Fachhochschul-Studienganges. Eine Ausfertigung wird der Studentin bzw. dem Studenten übergeben. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">Die englische Übersetzung des deutschsprachigen Vertrages dient nur als Referenz. Rechtsgültigkeit hat ausschließlich der deutsche Vertrag.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">Der Ausbildungsvertrag ist gebührenfrei.</text:p>
				<text:p text:style-name="P48">Gerichtsstand ist Wien, Innere Stadt. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T29">
					<xsl:choose>
						<xsl:when test="studiengangSprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')" >
							10.
						</xsl:when>
						<xsl:otherwise>
							9.
						</xsl:otherwise>
					</xsl:choose>
					</text:span>
					<text:span text:style-name="T30">Copies, Fees, Place of Jurisdiction</text:span>
				</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">This contract is provided in duplicate. An original remains with the competent administrations office of the University of Applied Sciences’ Degree Program. A copy is given to the student. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">The English translation of the German contract is intended as a reference only. Only the German version of this contract is legally valid in a Court of Law. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">The training contract is free of charge. </text:p>
				<text:p text:style-name="P48">Place of Jurisdiction is Vienna, Inner City.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
			</text:section>
			<text:section text:style-name="Sect3" text:name="Bereich4">
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P76">Wien (Vienna), <xsl:value-of select="datum_aktuell"/></text:p>
				<text:p text:style-name="P77">Ort, Datum (City, Date) </text:p>
				<text:p text:style-name="P76"/>
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
							<text:p text:style-name="P31"/>
							<text:p text:style-name="P30"/>
							<text:p text:style-name="P23"/>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle1.1">
						<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
							<text:p text:style-name="P28">
								<text:span text:style-name="T34">Die Studentin/der Student /<text:line-break/>ggf. gesetzliche VertreterInnen</text:span>
							</text:p>
							<text:p text:style-name="P28">
								<text:span text:style-name="T36">(The student /</text:span>
								<text:span text:style-name="T27">
									<text:line-break/>
								</text:span>
								<text:span text:style-name="T36">if necessary legal representatives)</text:span>
							</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P27"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
							<text:p text:style-name="P26">Für die FH Technikum Wien</text:p>
							<text:p text:style-name="P26"/>
							<text:p text:style-name="P29">
								<text:span text:style-name="T35">(For the UAS Technikum Wien)</text:span>
							</text:p>
						</table:table-cell>
					</table:table-row>
				</table:table>
				<text:p text:style-name="P22"/>
			</text:section>
		</office:text>
</xsl:template>
</xsl:stylesheet>