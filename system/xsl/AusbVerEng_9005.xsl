<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="ausbildungsvertraege">

<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
	<office:scripts/>
	<office:font-face-decls>
		<style:font-face style:name="Wingdings" svg:font-family="Wingdings" style:font-pitch="variable" style:font-charset="x-symbol"/>
		<style:font-face style:name="Symbol" svg:font-family="Symbol" style:font-family-generic="roman" style:font-pitch="variable" style:font-charset="x-symbol"/>
		<style:font-face style:name="Mangal1" svg:font-family="Mangal"/>
		<style:font-face style:name="Courier New1" svg:font-family="&apos;Courier New&apos;" style:font-family-generic="modern"/>
		<style:font-face style:name="Lucida Grande" svg:font-family="&apos;Lucida Grande&apos;, &apos;Times New Roman&apos;" style:font-family-generic="roman"/>
		<style:font-face style:name="ヒラギノ角ゴ Pro W3" svg:font-family="&apos;ヒラギノ角ゴ Pro W3&apos;" style:font-family-generic="roman"/>
		<style:font-face style:name="Courier New" svg:font-family="&apos;Courier New&apos;" style:font-family-generic="modern" style:font-pitch="fixed"/>
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
			<style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.5pt dotted #000000" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="Tabelle1.B1" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border="none" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="Tabelle1.A2" style:family="table-cell">
			<style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.5pt dotted #000000" fo:border-bottom="0.5pt dotted #000000" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Header">
			<style:text-properties fo:language="de" fo:country="AT" style:language-asian="de" style:country-asian="AT"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="0cm"/>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="0cm"/>
					<style:tab-stop style:position="0.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="start" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:language="de" fo:country="AT" style:language-asian="ar" style:country-asian="SA" style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
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
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001534a7"/>
		</style:style>
		<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001892c5" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="normal" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="P20" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P21" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P22" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P23" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P24" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001892c5"/>
		</style:style>
		<style:style style:name="P25" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001b1e78"/>
		</style:style>
		<style:style style:name="P26" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="column"/>
		</style:style>
		<style:style style:name="P27" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P28" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
					<style:tab-stop style:position="14.503cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P29" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P30" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
					<style:tab-stop style:position="14.503cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P31" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P32" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P33" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="page"/>
		</style:style>
		<style:style style:name="P34" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%">
				<style:tab-stops>
					<style:tab-stop style:position="8.752cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="en" fo:country="US" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P35" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="start" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="en" fo:country="US" fo:font-weight="bold" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P36" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P37" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P38" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00218383" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P39" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P40" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00232d24" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P41" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00233549" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P42" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P43" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="0025de2b" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P44" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="start" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00218383" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P45" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00218383" officeooo:paragraph-rsid="00218383" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P46" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00218383" officeooo:paragraph-rsid="00218383" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P47" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00232d24" officeooo:paragraph-rsid="00232d24" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P48" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00233549" officeooo:paragraph-rsid="00233549" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P49" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00218383" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P50" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00233549" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P51" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00218383" officeooo:paragraph-rsid="00218383" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P52" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00233549" officeooo:paragraph-rsid="00233549" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P53" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="8pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="8pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P54" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:language="de" fo:country="AT" fo:font-weight="bold" officeooo:rsid="001f79b5" officeooo:paragraph-rsid="001f79b5" style:language-asian="ar" style:country-asian="SA" style:font-weight-asian="bold" style:language-complex="zxx" style:country-complex="none" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P55" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties officeooo:paragraph-rsid="001f79b5"/>
		</style:style>
		<style:style style:name="P56" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties officeooo:paragraph-rsid="00218383"/>
		</style:style>
		<style:style style:name="P57" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties officeooo:paragraph-rsid="00233549"/>
		</style:style>
		<style:style style:name="P58" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P59" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" officeooo:rsid="00218383" officeooo:paragraph-rsid="00218383" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P60" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P61" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" fo:font-weight="bold" officeooo:paragraph-rsid="00233549" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="P62" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="P63" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties officeooo:rsid="001f79b5" officeooo:paragraph-rsid="001f79b5"/>
		</style:style>
		<style:style style:name="P64" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001f79b5"/>
		</style:style>
		<style:style style:name="P65" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="00218383"/>
		</style:style>
		<style:style style:name="P66" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001f79b5" officeooo:paragraph-rsid="001f79b5" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P67" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P68" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P69" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00161927" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P70" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="12pt" fo:language="de" fo:country="DE" fo:font-weight="bold" style:font-name-asian="Times New Roman" style:font-size-asian="12pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P71" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="column"/>
			<style:text-properties officeooo:paragraph-rsid="001534a7"/>
		</style:style>
		<style:style style:name="P72" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="column"/>
			<style:text-properties officeooo:paragraph-rsid="001b1e78"/>
		</style:style>
		<style:style style:name="P73" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="page"/>
		</style:style>
		<style:style style:name="P74" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P75" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="page"/>
			<style:text-properties officeooo:paragraph-rsid="002431e5"/>
		</style:style>
		<style:style style:name="P76" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="9pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P77" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P78" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="002431e5" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P79" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="0025de2b" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P80" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00218383" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P81" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00233549" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P82" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="WW8Num4">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="002431e5" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P83" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="002431e5" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P84" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00218383" officeooo:paragraph-rsid="00218383" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P85" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00233549" officeooo:paragraph-rsid="00233549" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P86" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00233549" officeooo:paragraph-rsid="00233549" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P87" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="002431e5" officeooo:paragraph-rsid="002431e5" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P88" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="002431e5" officeooo:paragraph-rsid="002431e5" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P89" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="002431e5" officeooo:paragraph-rsid="002431e5" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P90" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="0025de2b" officeooo:paragraph-rsid="0025de2b" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P91" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" officeooo:rsid="0025de2b" officeooo:paragraph-rsid="0025de2b" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P92" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="P93" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P94" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" officeooo:rsid="0025de2b" officeooo:paragraph-rsid="0025de2b" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P95" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" officeooo:rsid="0025de2b" officeooo:paragraph-rsid="0025de2b" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P96" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P97" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P98" style:family="paragraph" style:parent-style-name="Heading_20_1" style:list-style-name="" style:master-page-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false" style:page-number="1"/>
			<style:text-properties style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="P99" style:family="paragraph" style:parent-style-name="Heading_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
		</style:style>
		<style:style style:name="P100" style:family="paragraph" style:parent-style-name="Standard1">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:color="#ff3333" fo:font-size="16pt" style:font-size-asian="16pt" style:font-name-complex="Arial" style:font-size-complex="16pt"/>
		</style:style>
		<style:style style:name="T1" style:family="text">
			<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="T2" style:family="text">
			<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T3" style:family="text">
			<style:text-properties fo:font-size="8pt" officeooo:rsid="001f79b5" style:font-size-asian="8pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T4" style:family="text">
			<style:text-properties fo:font-size="8pt" fo:background-color="transparent" loext:char-shading-value="0" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="T5" style:family="text">
			<style:text-properties fo:font-size="8pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="8pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T6" style:family="text">
			<style:text-properties fo:font-size="8pt" fo:language="en" fo:country="US" officeooo:rsid="0020dd06" style:font-name-asian="Arial" style:font-size-asian="8pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T7" style:family="text">
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T8" style:family="text">
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T9" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:font-weight="bold" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T10" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="00233549" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T11" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="0025de2b" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T12" style:family="text">
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T13" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T14" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" officeooo:rsid="001f79b5" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T15" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T16" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" officeooo:rsid="001f79b5" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T17" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T18" style:family="text">
			<style:text-properties style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T19" style:family="text">
			<style:text-properties style:font-name-asian="Arial"/>
		</style:style>
		<style:style style:name="T20" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-name-asian="Arial"/>
		</style:style>
		<style:style style:name="T21" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T22" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" officeooo:rsid="0025de2b" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T23" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" style:font-name-asian="Arial" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T24" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" officeooo:rsid="00233549" style:font-name-asian="Arial" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T25" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" fo:background-color="transparent" loext:char-shading-value="0" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T26" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" officeooo:rsid="0025de2b" fo:background-color="transparent" loext:char-shading-value="0" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T27" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-style-complex="italic"/>
		</style:style>
		<style:style style:name="T28" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T29" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T30" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T31" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-name-asian="Arial" style:font-size-asian="9pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T32" style:family="text">
			<style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="9pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T33" style:family="text">
			<style:text-properties style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="T34" style:family="text">
			<style:text-properties officeooo:rsid="001534a7"/>
		</style:style>
		<style:style style:name="T35" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00218383" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T36" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" loext:char-shading-value="0" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T37" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00218383" fo:background-color="transparent" loext:char-shading-value="0" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T38" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T39" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="normal" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="T40" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="en" fo:country="US" fo:font-weight="bold" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T41" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="8pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="8pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T42" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="8pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="8pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T43" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T44" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="12pt" fo:language="de" fo:country="DE" fo:font-weight="bold" style:font-name-asian="Times New Roman" style:font-size-asian="12pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T45" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="12pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="12pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T46" style:family="text">
			<style:text-properties style:language-asian="ar" style:country-asian="SA"/>
		</style:style>
		<style:style style:name="T47" style:family="text">
			<style:text-properties officeooo:rsid="00233549" style:language-asian="ar" style:country-asian="SA"/>
		</style:style>
		<style:style style:name="T48" style:family="text">
			<style:text-properties officeooo:rsid="00161927"/>
		</style:style>
		<style:style style:name="T49" style:family="text">
			<style:text-properties fo:font-size="12pt" style:font-size-asian="12pt"/>
		</style:style>
		<style:style style:name="T50" style:family="text">
			<style:text-properties officeooo:rsid="00218383"/>
		</style:style>
		<style:style style:name="T51" style:family="text">
			<style:text-properties officeooo:rsid="00232d24"/>
		</style:style>
		<style:style style:name="T52" style:family="text">
			<style:text-properties officeooo:rsid="00233549"/>
		</style:style>
		<style:style style:name="T53" style:family="text">
			<style:text-properties officeooo:rsid="002431e5"/>
		</style:style>
		<style:style style:name="T54" style:family="text">
			<style:text-properties officeooo:rsid="0025de2b"/>
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
	</office:automatic-styles>
	<office:body>
<xsl:apply-templates select="ausbildungsvertrag"/>
	</office:body>
</office:document-content>
</xsl:template>

<xsl:template match="ausbildungsvertrag">
	<office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">

			<text:tracked-changes text:track-changes="true"/>
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
			</text:sequence-decls>
			<text:section text:style-name="Sect1" text:name="Bereich1">
				<text:h text:style-name="P98" text:outline-level="1">Ausbildungsvertrag</text:h>
				<text:p text:style-name="P54">außerordentliches Studium (Besuch einzelner Lehrveranstaltungen eines Studiengangs)</text:p>
					<!-- Ueberprueft ob benoetigte Datenfelder leer sind -->
					<xsl:if test="svnr = ''"><text:p text:style-name="P100">Keine Sozialversicherungsnummer oder Ersatzkennzeichen vorhanden</text:p></xsl:if>
					<xsl:if test="gebdatum = ''"><text:p text:style-name="P100">Kein Geburtsdatum vorhanden</text:p></xsl:if>
					<xsl:if test="student_maxsemester = ''"><text:p text:style-name="P100">Keine Ausbildungsdauer vorhanden</text:p></xsl:if>
				<text:p text:style-name="P7"/>
				<text:p text:style-name="P7"/>
				<text:p text:style-name="P58">
					<text:span text:style-name="T18">Dieser Vertrag regelt das Rechtsverhältnis zwischen </text:span>
					<text:span text:style-name="T8">dem Verein Fachhochschule Technikum Wien,</text:span>
					<text:span text:style-name="T18">1060 Wien, Mariahilfer Straße 37-39 (kurz „Erhalter“ genannt) einerseits </text:span>
					<text:span text:style-name="T8">und</text:span>
				</text:p>
				<text:p text:style-name="P9"/>
				<text:h text:style-name="P99" text:outline-level="1">
					<text:span text:style-name="T33">Training Contract</text:span>
				</text:h>
				<text:p text:style-name="P54">for external students (attending individual courses of a degree program)</text:p>
				<text:p text:style-name="P7"/>
				<text:p text:style-name="P7"/>
				<text:p text:style-name="P14">This contract governs the legal relationship between <text:span text:style-name="T7">the University of Applied Sciences Technikum Wien Association,</text:span> 1060 Vienna, Mariahilferstraße 37-39 (referred to as &quot;operator&quot;) on the one hand <text:span text:style-name="T7">and</text:span>
				</text:p>
				<text:p text:style-name="P19"/>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P14"/>
			</text:section>
			<text:section text:style-name="Sect2" text:name="Bereich2">
				<text:p text:style-name="P6">
					<text:span text:style-name="T15">Familienname (Surname):<text:tab/><xsl:value-of select="nachname"/></text:span>
				</text:p>
				<text:p text:style-name="P10">Vorname (First Name):<text:tab/><xsl:value-of select="vorname"/></text:p>
				<text:p text:style-name="P6">
					<text:span text:style-name="T15">Akademischer Titel (Academic degree):<text:tab/><xsl:value-of select="titelpre"/><xsl:value-of select="titelpost"/></text:span>
				</text:p>
				<text:p text:style-name="P6">
					<text:span text:style-name="T15">Adresse (Address):<text:tab/><xsl:value-of select="strasse"/></text:span>
				</text:p>
				<text:p text:style-name="P6">
					<text:span text:style-name="T12">
						<text:tab/><xsl:value-of select="plz"/></text:span>
				</text:p>
				<text:p text:style-name="P3">
					<text:span text:style-name="T15">Geburtsdatum (Date of birth): <text:tab/><xsl:value-of select="gebdatum"/></text:span>
				</text:p>
				<text:p text:style-name="P3">
					<text:span text:style-name="T12">Sozialversicherungsnr. </text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T12">
							<text:note text:id="ftn1" text:note-class="footnote">
								<text:note-citation>1</text:note-citation>
								<text:note-body>
									<text:p text:style-name="P64">
										<text:span text:style-name="T19">
											<text:s/>
										</text:span>
										<text:span text:style-name="T2">Gemäß § 3 Absatz 1 des Bildungsdokumentationsgesetzes idgF und der Bildungsdokumentationsverordnung-Fachhochschulen idgF hat der Erhalter die Sozialversicherungsnummer zu erfassen und gemäß § 7 Absatz 2 im Wege der Agentur für Qualitätssicherung und Akkreditierung Austria an das zuständige Bundesministerium und die Bundesanstalt Statistik Österreich zu übermitteln. </text:span>
									</text:p>
									<text:p text:style-name="P53"/>
								</text:note-body>
							</text:note>
						</text:span>
					</text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T12"> </text:span>
					</text:span>
					<text:span text:style-name="T17">:<text:tab/><xsl:value-of select="svnr"/></text:span>
				</text:p>
				<text:p text:style-name="P4">
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
										<text:span text:style-name="T5">Pursuant to § 3 section 1 of the Education Documentation Act as amended and the Education Documentation Regulation for Universities of Applied Sciences as amended, the operator shall record the social security number pursuant to § 7 paragraph 2 and shall transfer it via the Agency for Quality Assurance and Accreditation Austria to the competent Ministry and Statistics Austria. </text:span>
									</text:p>
								</text:note-body>
							</text:note>
						</text:span>
					</text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T15"> </text:span>
					</text:span>
					<text:span text:style-name="T15">: </text:span>
				</text:p>
				<text:p text:style-name="P34"/>
				<text:p text:style-name="P55">
					<text:span text:style-name="T15">(kurz „ao. Studentin“ bzw. „ao. Student“ genannt)<text:tab/>(referred to as &quot;external student&quot;)</text:span>
					<text:span text:style-name="T13">on the andererseits,<text:tab/>
						<text:tab/>other,</text:span>
				</text:p>
				<text:p text:style-name="P60"/>
			</text:section>
			<text:section text:style-name="Sect1" text:name="Bereich3">
				<text:p text:style-name="P63">
					<text:span text:style-name="T36">im Rahmen des außerordentlichen Studiums bzw. des Besuchs einzelner Lehrveranstaltungen <text:s/>an der FH Technikum Wien. Die konkreten Lehrveranstaltungen des außerordentlichen Studiums sind in der Information über die Zulassung zum außerordentlichen Studium angeführt.</text:span>
				</text:p>
				<text:p text:style-name="P66">as part of an external course of study or attending individual courses at the UAS Technikum Wien. The specific courses of an external study program are included in the Information about Admission to an External Study Program.</text:p>
				<text:p text:style-name="P33">
					<text:span text:style-name="T21">1. Ausbildungsort</text:span>
				</text:p>
				<text:p text:style-name="P13"/>
				<text:p text:style-name="P14">Studienort sind die Räumlichkeiten der FH Technikum Wien, 1200 Wien, Höchstädtplatz und 1210 Wien, Giefinggasse. Bei Bedarf kann der Erhalter einen anderen Studienort in Wien festlegen, außerhochschulische Aktivitäten (z.B. Exkursionen) können auch außerhalb von Wien stattfinden. </text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T21">2. Vertragsgrundlage</text:span>
				</text:p>
				<text:p text:style-name="P8"/>
				<text:p text:style-name="P14">Die Ausbildung erfolgt auf der Grundlage von  § 4 Abs. 2 und 3 des Fachhochschul-Studiengesetzes, BGBl. Nr. 340/1993 idgF, des Hochschul-Qualitätssicherungsgesetzes, BGBl. I Nr. 74/2011 idgF, und des Akkreditierungs-bescheides des Board der AQ Austria vom 9.5.2012, GZ FH12020016 idgF und der Satzung der Fachhochschule Technikum Wien idgF.</text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="Standard">
					<text:bookmark-start text:name="_Ref78860434"/>
					<text:span text:style-name="T21">3. Ausbildungsdauer</text:span>
					<text:bookmark-end text:name="_Ref78860434"/>
				</text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P51">Die Ausbildungsdauer des außerordentlichen Studiums ist durch die Dauer der Lehrveranstaltung/en, zu der bzw. denen die ao. Studentin bzw. der ao. Student zugelassen ist, definiert.</text:p>
				<text:p text:style-name="P51"/>
				<text:p text:style-name="P65">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">4. Ausbildungsabschluss</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P38"/>
				<text:p text:style-name="P49">Die Ausbildung endet mit der positiven Absolvierung der das jeweilige Studium abschließenden kommissionellen Prüfung. Nach dem Abschluss der vorgeschriebenen Prüfungen wird der akademische Grad Bachelor of Science in Engineering (BSc) durch das FH-Kollegium verliehen. </text:p>
				<text:p text:style-name="P26">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">1. Place of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P21">Places of training are the premises of the UAS Technikum Wien, 1200 Vienna, Höchstädt-platz and 1210 Vienna, Giefinggasse. If necessary, the operator may specify a different place of study in Vienna. Non-university activities (e.g. excursions) may take place away from Vienna.</text:p>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">2. Contractual Basis</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="P21">The training is based on § 4 paras. 2 and 3 of the University of Applied Sciences Studies Act, Federal Law Gazette No. 340/1993 as amended, the Higher Education Quality Assurance Act, Federal Law Gazette I No. 74/2011 as amended and the notification of accreditation by the Board of AQ Austria from 9.5.2012, GZ FH12020016 as amended and the statutes of the University of Applied Sciences Technikum Wien.</text:p>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">3. Duration of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="P56">
					<text:span text:style-name="T35">The duration of the external course of study is defined by the duration of the courses that the </text:span>
					<text:span text:style-name="T37">external student is allowed to attend.</text:span>
				</text:p>
				<text:p text:style-name="P51"/>
				<text:p text:style-name="P51"/>
				<text:p text:style-name="P51"/>
				<text:p text:style-name="P56">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T27">4. Formal Completion of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P38"/>
				<text:p text:style-name="P45">The training ends with the positive completion of the final examination before a committee for the respective course. After completion of the required examinations, the academic degree Bachelor of Science in Engineering (BSc) is awarded by the University of Applied Sciences Council. </text:p>
				<text:p text:style-name="P33">
					<text:span text:style-name="T21">5. Rechte und Pflichten des Erhalters</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:bookmark-start text:name="_Ref78865698"/>
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T9"/>
					</text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T9">5.1 Rechte</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
					<text:bookmark-end text:name="_Ref78865698"/>
				</text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P15">Der Erhalter führt eine periodische Überprüfung des Studiums im Hinblick auf Relevanz und Aktualität durch und ist im Einvernehmen mit dem FH-Kollegium berechtigt, daraus Änderungen des akkreditierten Studienganges abzuleiten.</text:p>
				<text:p text:style-name="P15"/>
				<text:p text:style-name="P38">
					<text:span text:style-name="T34">Der Erhalter ist berechtigt, die Daten der/des ao. Studierenden an den FH Technikum Wien Alumni Club zu übermitteln. Der Alumni Club ist der AbsolventInnenverein der FH Technikum Wien. Er hat zum Ziel, AbsolventInnen, Studierende und Lehrende miteinander zu vernetzen sowie AbsolventInnen laufend über Aktivitäten an der FH Technikum Wien zu informieren. Einer Zusendung von Informationen durch den Alumni Club kann jederzeit widersprochen werden.</text:span>
				</text:p>
				<text:p text:style-name="P15"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T9">5.2 Pflichten</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T38"/>
					</text:span>
				</text:p>
				<text:list xml:id="list930332961812280424" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P84">Der Erhalter verpflichtet sich zur ordnungsgemäßen Planung und Durchführung der Lehrveranstaltung/en in der vorgesehenen Zeit. Der Erhalter ist verpflichtet, allfällige Änderungen des akkreditierten Studienganges zeitgerecht bekannt zu geben.</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P84">
							<text:span text:style-name="T34">Der Erhalter verpflichtet sich zur sorgfaltsgemäßen Verwendung der personenbezogenen Daten der </text:span>ao. <text:span text:style-name="T34">Studierenden. Die Daten werden nur im Rahmen der gesetzlichen und vertraglichen Verpflichtungen sowie des Studienbetriebes verwendet und nicht an nicht berechtigte Dritte weitergegeben.</text:span>
						</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P71">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">5. Rights and Duties of the Operator</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P12">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T28"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P24">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T9"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P24">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T9">5.1 Rights</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P17">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T39"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P15">The operator performs a periodic review of the course in terms of relevance and topicality, and is authorized, in consultation with the University of Applied Sciences Council, to deduce from this changes in the accredited degree program.</text:p>
				<text:p text:style-name="P15"/>
				<text:p text:style-name="P38">
					<text:span text:style-name="T34">The operator is entitled to communicate an external student’s data to the UAS Technikum Wien Alumni Club. The Alumni Club is the graduate association of the UAS Technikum Wien. Its goal is to provide links between graduates, students and lecturers as well as to keep graduates informed of the activities at the UAS Technikum Wien. A mailing of information from the Alumni Club can be vetoed at any time.</text:span>
				</text:p>
				<text:p text:style-name="P15"/>
				<text:p text:style-name="P15"/>
				<text:p text:style-name="P15"/>
				<text:p text:style-name="P12">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T9">5.2 Duties </text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P12">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T38"/>
					</text:span>
				</text:p>
				<text:list xml:id="list183618967479793" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P84">The operator undertakes to plan and hold the degree program in a proper manner within the expected time period. The operator is obliged to give adequate notice of any changes to the accredited degree program.<text:line-break/>
						</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P84">The operator is committed to use the personal data of the external students carefully. The data is only to be used within the operator’s legal and contractual obligations as well as its program of studies and is not to be handed on to unauthorized third parties.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P72">
					<text:soft-page-break/>
					<text:span text:style-name="T21">6. Rechte und Pflichten der ao. Studierenden</text:span>
				</text:p>
				<text:p text:style-name="P25">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T9"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P25">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T9">6.1 Rechte</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P25">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T9"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P59">Die ao. Studentin bzw. der ao. Student hat das Recht auf </text:p>
				<text:list xml:id="list183619564520558" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P84">einen Lehrveranstaltungsbetrieb gemäß den im akkreditierten Studiengang idgF und in der Satzung der FH Technikum Wien idgF festgelegten Bedingungen;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P84">ein Zeugnis über die im laufenden Semester abgelegten Prüfungen.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P56">
					<text:span text:style-name="T9"/>
				</text:p>
				<text:p text:style-name="P56">
					<text:span text:style-name="T9">6.2 Pflichten</text:span>
				</text:p>
				<text:p text:style-name="P65"/>
				<text:p text:style-name="P44">6.2.1 Einhaltung studienrelevanter Bestimmungen</text:p>
				<text:p text:style-name="P45">Die ao. Studentin bzw. der ao. Student ist verpflichtet, insbesondere folgende Bestimmungen einzuhalten:</text:p>
				<text:list xml:id="list235829763333" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P80">Studienordnung und Studienrechtliche Bestimmungen / Prüfungsordnung idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Hausordnung idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Brandschutzordnung idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Bibliotheksordnung idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Die für den jeweiligen Studiengang geltende/n Laborordnung/en idgF</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P38"/>
				<text:p text:style-name="P46">Diese Dokumente sind öffentlich zugänglich unter www.technikum-wien.at.</text:p>
				<text:p text:style-name="P72">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">6. Rights and Duties of the external Students</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P25">
					<text:span text:style-name="Strong_20_Emphasis"/>
				</text:p>
				<text:p text:style-name="P25">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T12"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P25">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T9">6.1 Rights </text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P25">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T9"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P45">The external student has the right to </text:p>
				<text:list xml:id="list183617998795032" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P84">attend a course being run in accordance with the conditions laid down in the accredited degree program as amended and in the statute of the UAS Technikum Wien as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P84">a certificate showing the examinations successfully passed in the current semester.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P56">
					<text:span text:style-name="T9"/>
				</text:p>
				<text:p text:style-name="P56">
					<text:span text:style-name="T9">6.2 Duties</text:span>
				</text:p>
				<text:p text:style-name="P65"/>
				<text:p text:style-name="P38">6.2.1 Compliance with regulations relevant to the studies</text:p>
				<text:p text:style-name="P38">In particular the external student undertakes to comply with the following regulations:</text:p>
				<text:list xml:id="list235829763334" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P80">Study Regulations and Studies Act Provisions / Examination Regulations as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">General Rules of Conduct as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Fire Regulations as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Library Regulations as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">The Laboratory Regulations applicable to the respective degree program as amended</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P38"/>
				<text:p text:style-name="P46">These documents are publically available at www.technikum-wien.at.</text:p>
				<text:p text:style-name="P74">6.2.2 Studienbeitrag </text:p>
				<text:p text:style-name="P40">
					<text:span text:style-name="T51">Die ao. Studentin bzw. der ao. Student</text:span> ist verpflichtet, zwei Wochen vor Beginn jedes Semesters (StudienanfängerInnen: bis 20. August vor Studienbeginn) einen Studienbeitrag gemäß Fachhochschul-Studiengesetz in der Höhe von derzeit € 363,36 netto pro Semester zu entrichten. Im Falle einer Erhöhung des gesetzlichen Studienbeitragssatzes erhöht sich der angeführte Betrag entsprechend. </text:p>
				<text:p text:style-name="P40">Die vollständige Bezahlung des Studienbeitrags ist Voraussetzung für die Aufnahme bzw. die Fortsetzung des ao. Studiums. Bei Nichtantritt des ao. Studiums oder Abbruch zu Beginn oder während des Semesters verfällt der Studienbeitrag. </text:p>
				<text:p text:style-name="P40"/>
				<text:p text:style-name="P47">6.2.3 ÖH-Beitrag</text:p>
				<text:p text:style-name="P47">Gemäß § 4 Abs 10 FHStG sind Studierende an Fachhochschulen Mitglieder der Österreichischen HochschülerInnenschaft (ÖH). Der/Die ao. Studierende hat semesterweise einen ÖH-Beitrag an den Erhalter zu entrichten, der diesen an die ÖH abführt. Die Entrichtung des Betrags ist Voraussetzung für die Zulassung zum ao. Studium bzw. für dessen Fortsetzung.</text:p>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47">6.2.4 Unkostenbeitrag </text:p>
				<text:p text:style-name="P47">Pro Semester ist ein Unkostenbeitrag zu entrichten, wobei es sich nicht um einen Pauschalbetrag handelt. Der Unkostenbeitrag stellt eine Abgeltung für über das Normalmaß hinausgehende Serviceleistungen der FH dar, z.B. Freifächer, Beratung/Info Auslands-studium, Sponsionsfeiern, Vorträge / Job-börse, Mensa etc. </text:p>
				<text:p text:style-name="P68">6.2.2 Tuition Fees</text:p>
				<text:p text:style-name="P40">Two weeks before the beginning of each semester (new students: up to August 20 before taking up studies) the external student undertakes to pay tuition fees according to the University of Applied Sciences Studies Act currently to the sum of € 363.36 net payable per semester. In the event of an increase in the legal tuition fees rate, the amount quoted will increase accordingly. </text:p>
				<text:p text:style-name="P40"/>
				<text:p text:style-name="P40">Full payment of the tuition fees is a prerequisite both for enrolling on the course and continuing with the degree program. For non-commencement or termination of the <text:span text:style-name="T51">external <text:s/>
					</text:span>study at the beginning or during the semester, the tuition fee is forfeited. </text:p>
				<text:p text:style-name="P40"/>
				<text:p text:style-name="P47">6.2.3 Austrian Student Union fee</text:p>
				<text:p text:style-name="P47">In accordance with § 4 para.10 FHStG (University of Applied Sciences Studies Act) students at Universities of Applied Sciences are members of the Austrian National Union of Students (ÖH). Each semester the external student is required to pay an ÖH fee to the operator. This fee is then paid to the ÖH. Payment of this fee is a prerequisite for admission to the course of external study or its continuation.</text:p>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47">6.2.4 Contribution towards Expenses </text:p>
				<text:p text:style-name="P47">A contribution towards expenses, which is not a lump sum, is payable per semester. The contribution towards expenses represents compensation for the services provided by the UAS that go beyond the normal level, such as electives, counseling/ information about studying abroad, graduation ceremonies, lectures/job market, cafeteria, etc. </text:p>
				<text:p text:style-name="P68">
					<text:soft-page-break/>Die Höhe des Unkostenbeitrages beträgt derzeit € 75,– pro Semester. Eine allfällige Anpassung wird durch Aushang bekannt gemacht. </text:p>
				<text:p text:style-name="P47">Der Unkostenbeitrag ist gleichzeitig mit der Studiengebühr vor Beginn des Semesters zu entrichten.</text:p>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47">Bei Vertragsauflösung vor dem Ende der besuchten Lehrveranstaltungen aus Gründen, die die ao. Studentin bzw. der ao. Student zu vertreten hat, oder auf deren bzw. dessen Wunsch, wird der Unkostenbeitrag zur Abdeckung der dem Erhalter erwachsenen administrativen Zusatzkosten einbehalten.</text:p>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47">6.2.5 Lehr- und Lernbehelfe </text:p>
				<text:p text:style-name="P47">Die Anschaffung unterrichtsbezogener Literatur und individueller Lernbehelfe ist durch den Unkostenbeitrag nicht abgedeckt. Eventuelle zusätzliche Kosten, die sich beispielsweise durch die studiengangsbezogene, gemeinsame Anschaffung von Lehr- bzw. Lernbehelfen (Skripten, CDs, Bücher, Projektmaterialien, Kopierpapier etc.) oder durch Exkursionen ergeben, werden von jedem Studiengang individuell eingehoben. </text:p>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47">6.2.6 Beibringung persönlicher Daten </text:p>
				<text:p text:style-name="P47">Die ao. Studentin bzw. der ao. Student ist verpflichtet, persönliche Daten beizubringen, die auf Grund eines Gesetzes, einer Verordnung oder eines Bescheides vom Erhalter erfasst werden müssen oder zur Erfüllung des Ausbildungsvertrages bzw. für den Studienbetrieb unerlässlich sind.</text:p>
				<text:p text:style-name="P68">The amount of the contribution is currently <text:line-break/>€ 75,– per semester. Any possible adjustment is posted on the noticeboard. </text:p>
				<text:p text:style-name="P47">The contribution towards expenses must be paid together with the tuition fees before the start of the semester.</text:p>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47">If the contract is cancelled before graduation for reasons that are the fault of the external student, or on their wishes, the contribution towards expenses shall be deducted to cover the additional administrative costs borne by the operator.</text:p>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47">6.2.5 Teaching Aids and Learning Tools</text:p>
				<text:p text:style-name="P47">The acquisition of teaching-related literature and individual learning tools is not covered by the contribution towards expenses. Any additional costs, which arise for example from the course-related, joint purchase of teaching and / or learning materials (scripts, CDs, books, project materials, copying paper, etc.) or result from field trips, are levied by each individual degree program. </text:p>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47"/>
				<text:p text:style-name="P47">6.2.6 Providing Personal Data</text:p>
				<text:p text:style-name="P47">The external student is obliged to produce personal data which must be registered because of a law, regulation or a decision by the operator, or is essential fort he fulfilling of the training contract or fort he program of studies.</text:p>
				<text:p text:style-name="P96">6.2.<text:span text:style-name="T52">7</text:span> Aktualisierung eigener Daten und Bezug von Informationen</text:p>
				<text:p text:style-name="P50">
					<text:span text:style-name="T52">Die ao. Studentin bzw. der ao. Student</text:span> hat unaufgefordert dafür zu sorgen, dass die von ihr/ihm beigebrachten Daten aktuell sind. Änderungen sind der Studiengangsassistenz unverzüglich schriftlich mitzuteilen. Darüber hinaus trifft sie/ihn die Pflicht, sich von studienbezogenen Informationen, die ihr/ihm an die vom Erhalter zur Verfügung gestellte Emailadresse zugestellt werden, in geeigneter Weise Kenntnis zu verschaffen.</text:p>
				<text:p text:style-name="P50"/>
				<text:p text:style-name="P52">6.2.8 Verwertungsrechte </text:p>
				<text:p text:style-name="P52">Sofern nicht im Einzelfall andere Regelungen zwischen dem Erhalter und der ao. Studentin oder dem ao. Studenten getroffen wurden, ist die ao. Studentin oder der ao. Student verpflichtet, dem Erhalter die Rechte an Forschungs- und Entwicklungsergebnissen auf dessen schriftliche Anfrage hin anzubieten.</text:p>
				<text:p text:style-name="P52"/>
				<text:p text:style-name="P52">6.2.9 Aufzeichnungen und Mitschnitte</text:p>
				<text:p text:style-name="P52">Es ist der/dem ao. Studierenden ausdrücklich untersagt, Lehrveranstaltungen als Ganzes oder nur Teile davon aufzuzeichnen und/oder mitzuschneiden (z.B. durch Film- und/oder Tonaufnahmen oder sonstige hierfür geeignete audiovisuelle Mittel). Darüber hinaus ist jede Form der öffentlichen Zurverfügungstellung (drahtlos oder drahtgebunden) der vorgenannten Aufnahmen z.B. in sozialen Netzwerken wie Facebook, StudiVZ etc, aber auch auf Youtube usw. oder durch sonstige für diese Zwecke geeignete Kommunikations-mittel untersagt. Diese Regelungen gelten sinngemäß auch für Skripten, sonstige Lernbehelfe und Prüfungsangaben. </text:p>
				
				<text:p text:style-name="P69">6.2.7 Updating personal data and the retrieval of information</text:p>
				<text:p text:style-name="P18">
					<text:span text:style-name="T48">Without being reminded, the external student must ensure that the data provided by them is up-to-date. Changes are to be immediately communicated to the administrative assistant in writing. Furthermore, it is the students’ responsibility to make themselves suitably aware of information relating to their studies which has been sent to them at the email address provided for them by the operator.</text:span>
					<text:bookmark-end text:name="_Ref78863824"/>
				</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P48">6.2.8 Exploitation Rights</text:p>
				<text:p text:style-name="P48">Unless other arrangements have been agreed between the operator and the external student at an individual level, on written request, the external student undertakes to offer the operator the rights to research and development results. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">6.2.9 Recordings </text:p>
				<text:p text:style-name="P48">It is expressly forbidden for the external student to record lectures in part or in total (e.g. by using film and / or sound recordings or other audio-visual means suitable for this purpose). In addition, any form of making the aforementioned recordings publically available (wired or wireless) for example in social networks such as Facebook, StudiVZ etc, but also on Youtube, etc., or by other means of communication designed for these purposes is strictly prohibited. These regulations shall apply correspondingly to scripts, other learning aids and examination data. </text:p>
				<text:p text:style-name="P97">Ausgenommen hiervon ist eine Aufzeichnung zu ausschließlichen Lern-, Studien- und Forschungszwecken und zum privaten Gebrauch, sofern hierfür der Vortragende vorab ausdrücklich seine schriftliche Zustimmung erteilt hat.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">6.2.10 Geheimhaltungspflicht</text:p>				
				<text:p text:style-name="P48">Die ao. Studentin bzw. der ao. Student ist zur Geheimhaltung von Forschungs- und Entwicklungsaktivitäten und -ergebnissen gegenüber Dritten verpflichtet. </text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P48">6.2.11 Unfallmeldung </text:p>
				<text:p text:style-name="P48">Im Falle eines Unfalles mit körperlicher Verletzung des/der ao. Studierenden im Zusammenhang mit dem ao. Studium ist die/der ao. Studierende verpflichtet, diesen innerhalb von drei Tagen dem Studiengangssekretariat zu melden. Dies betrifft auch Wegunfälle zur oder von der FH.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">6.2.12 Schadensmeldung </text:p>
				<text:p text:style-name="P48">Im Falle des Eintretens eines Schadens am Inventar der Fachhochschule ist der/die ao. Studierende verpflichtet, diesen innerhalb von drei Tagen dem Studiengangssekretariat zu melden. Allfällige Haftungsansprüche bleiben hiervon unberührt.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">6.2.13 Rückgabeverpflichtung bei Studienende </text:p>
				<text:p text:style-name="P48">Die ao. Studentin bzw. der ao. Student ist verpflichtet, bei einer Beendigung des ao. Studiums unverzüglich alle zur Verfügung gestellten Gerätschaften, Bücher, Schlüssel und sonstige Materialien zurückzugeben.</text:p>
				<text:p text:style-name="P67">The only exception is a recording exclusively for the purpose of learning, study and research and for personal use, provided that the lecturer has expressly granted his / her prior written consent.</text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P48">6.2.10 Confidentiality </text:p>
				<text:p text:style-name="P41">The external student is required to maintain confidentiality towards third parties of research and development activities and results. </text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P48">6.2.11 Accident Report</text:p>
				<text:p text:style-name="P48">In the event of an accident with bodily injury to an external student in connection with his / her external studies, he / she is obliged to report this to the administrative assistant of the degree program within three days. This also applies to accidents on the way to or from the UAS. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">6.2.12 Damage Report</text:p>
				<text:p text:style-name="P48">If any damage should be caused to the inventory of the University of Applied Sciences, the external student undertakes to report this to the administrative assistant of the degree program within three days. Any liability claims shall remain unaffected.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">6.2.13 Obligation to Return Borrowed Items</text:p>
				<text:p text:style-name="P48">The external student undertakes to return promptly all equipment, books, keys and other materials that have been made available, when the course is finished or broken off.</text:p>
				<text:p text:style-name="P70">
					<text:soft-page-break/>7. Beendigung des Vertrages </text:p>
				<text:p text:style-name="P2">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T44"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P35">7.1 Auflösung im beiderseitigen Einvernehmen</text:p>
				<text:p text:style-name="P32">Im beiderseitigen Einvernehmen ist die Auflösung des Ausbildungsvertrages jederzeit ohne Angabe von Gründen möglich. Die einvernehmliche Auflösung bedarf der Schriftform. </text:p>
				<text:p text:style-name="P32"/>
				<text:p text:style-name="P5">
					<text:span text:style-name="T9">7.2 Kündigung durch die ao. Studentin bzw. den ao. Studenten</text:span>
				</text:p>
				<text:p text:style-name="P16">Die ao. Studentin bzw. der ao. Student kann den Ausbildungsvertrag schriftlich jeweils zum Ende eines Semesters kündigen. </text:p>
				<text:p text:style-name="P16"/>
				<text:p text:style-name="P57">
					<text:span text:style-name="T9">7.3</text:span>
					<text:span text:style-name="T9"> Ausschluss durch den Erhalter</text:span>
				</text:p>
				<text:p text:style-name="P41">Der Erhalter kann die ao. Studentin bzw. den ao. Studenten aus wichtigem Grund mit sofortiger Wirkung vom weiteren Studium ausschließen, und zwar beispielsweise wegen </text:p>
				<text:list xml:id="list183618167631736" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P81">nicht genügender Leistung im Sinne der Prüfungsordnung;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P81">mehrmaligem unentschuldigten Verletzen der Anwesenheitspflicht; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P81">wiederholtem Nichteinhalten von Prüfungsterminen und Abgabeterminen für Seminararbeiten, Projektarbeiten etc.; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P81">schwerwiegender bzw. wiederholter Verstöße gegen die Hausordnung;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P85">persönlichem Verhalten, das zu einer Beeinträchtigung des Images und/oder Betriebes des Studienganges, der Fach-hochschule bzw. des Erhalters oder von Personen führt, die für die Fachhochschule bzw. den Erhalter tätig sind;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P87">Verletzung der Verpflichtung, dem Erhalter die Rechte an Forschungs- und Entwicklungsergebnissen anzubieten (siehe Pkt. 6.2.8);</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P67">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T49">7. Termination of the contract </text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P23">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T45"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P11">
					<text:span text:style-name="T7">7.1 Annulment by Mutual Agreement</text:span>
				</text:p>
				<text:p text:style-name="P11"/>
				<text:p text:style-name="P14">By mutual consent, the annulment of the training contract is possible at any time, without notice and for any reason. The amicable annulment must be put down in writing. </text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P14">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T23">7.2 Termination by the external Student</text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P14">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T40"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P14">The external student may terminate the training contract in writing at the end of each semester.</text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P61">7.3 Expulsion by the Operator </text:p>
				<text:p text:style-name="P41">The operator may exclude the student from further study with immediate effect for good cause, for example because of</text:p>
				<text:p text:style-name="P41"/>
				<text:list xml:id="list183619547819695" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P81">insufficient achievement for the purposes of the examination regulations;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P81">repeated unexcused violation of the compulsory attendance regulation;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P81">repeated non-compliance with examination dates and deadlines for seminar papers, project work etc.; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P81">serious or repeated violation of the house rules;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P86">personal behavior, which leads to an adverse effect on the image and / or operation of the course, the university or the operator or on persons who are working for the university or the operator;<text:line-break/>
						</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P88">breach of the obligation to offer the operator the rights to research and development results (see Section 6.2.8);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P93">
							<text:soft-page-break/>Verletzung der Geheimhaltungspflicht (siehe Pkt. 6.2.1<text:span text:style-name="T53">0</text:span>); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P83">strafgerichtlicher Verurteilung (wobei die Art des Deliktes und der Grad der Schuld berücksichtigt werden);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P83">Nichterfüllung finanzieller Verpflichtungen trotz Mahnung (z.B. Unkostenbeitrag, Studienbeitrag etc.); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P82">Weigerung zur Beibringung von Daten (siehe Pkt. 6.2.<text:span text:style-name="T53">6</text:span>);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P82">Plagiieren im Rahmen wissenschaftlicher Arbeiten.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P78"/>
				<text:p text:style-name="P89">Besucht die ao. Studentin bzw. der ao. Student nur eine Lehrveranstaltung, so ist damit zugleich der Ausschluss vom ao. Studium verbunden. </text:p>
				<text:p text:style-name="P89"/>
				<text:p text:style-name="P89">Der Ausschluss kann mündlich erklärt werden. Mit Ausspruch des Ausschlusses endet der Ausbildungsvertrag, es sei denn, es wird ausdrücklich auf einen anderen Endtermin hingewiesen. Eine schriftliche Bestätigung des Ausschlusses wird innerhalb von zwei Wochen nach dessen Ausspruch per Post an die bekannt gegebene Adresse abgeschickt oder auf andere geeignete Weise übermittelt. </text:p>
				<text:p text:style-name="P89">Gleichzeitig mit dem Ausspruch des Ausschlusses kann auch ein Hausverbot verhängt werden. </text:p>
				<text:p text:style-name="P89"/>
				<text:p text:style-name="P91">7.4 Erlöschen</text:p>
				<text:p text:style-name="P79">
					<text:span text:style-name="T54">Der Ausbildungsvertrag erlischt mit der Beendigung der besuchten Lehrveranstaltungen durch die Ausstellung eines Zeugnisses oder einer Teilnahmebestätigung. Im Fall des Besuchs mehrerer Lehrveranstaltungen während eines Semesters gibt die Lehrveranstaltung mit der spätesten Ausstellung des Zeugnisses oder der Teilnahmebestätigung den Ausschlag.</text:span>
				</text:p>
				<text:list xml:id="list183619874298973" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P93">breach of confidentiality (see Section 6.2.1<text:span text:style-name="T53">0</text:span>);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P83">a criminal conviction (whereby the nature of the offence and the level of culpability are taken into account);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P83">non-fulfilment of the financial obligations, despite a reminder (e.g. contribution towards expenses, tuition fees , etc.); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P82">refusal to provide any data (see section 6.2.<text:span text:style-name="T53">6</text:span>); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P82">plagiarism in academic work.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P78"/>
				<text:p text:style-name="P78"/>
				<text:p text:style-name="P89">If external students attend only one course, they are simultaneously excluded from an external course of study.</text:p>
				<text:p text:style-name="P89"/>
				<text:p text:style-name="P89"/>
				<text:p text:style-name="P89">The expulsion can be explained verbally. Once notice of the expulsion has been given the training contract ends unless another deadline is explicitly made clear. Within two weeks of notice being given, written confirmation of the expulsion is mailed by post to the address provided or transmitted in any other appropriate manner.</text:p>
				<text:p text:style-name="P78">
					<text:span text:style-name="T53">Simultaneously with notice of expulsion being given an exclusion order from entering the building may also be imposed.</text:span>
				</text:p>
				<text:p text:style-name="P78"/>
				<text:p text:style-name="P78"/>
				<text:p text:style-name="P91">7.4 Expiry</text:p>
				<text:p text:style-name="P90">The training contract comes to an end when the courses visited are shown to be completed by means of a certificate of achievement or an attendance certificate. If several courses have been attended during one semester, the course which is the last to issue its certificate of achievement or attendance certificate is the deciding factor.</text:p>
				<text:p text:style-name="P90"/>
				<text:p text:style-name="P90"/>
				<xsl:if test="studienplan_sprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
					<text:p text:style-name="P95">8. Ergänzende Vereinbarungen</text:p>
					<text:p text:style-name="P90"/>
					<text:p text:style-name="P90">Das gesamte Studienprogramm wird in englischer Sprache angeboten. Die ao. Studentin bzw. der ao. Student erklärt, die englische Sprache in Wort und Schrift in dem für eine akademische Ausbildung erforderlichen Ausmaß zu beherrschen.</text:p>
					<text:p text:style-name="P90"/>
					<text:p text:style-name="P90">Ao. Studierende des Studiengangs sind verpflichtet, eine EDV-Ausstattung zu beschaffen und zu unterhalten, die es ermöglicht, an den Fernlehrelementen teilzunehmen. Die gesamten Kosten der Anschaffung und des Betriebs (inkl. Kosten für Internet und e-mail) trägt der ao. Student bzw. die ao. Studentin.</text:p>
					<text:p text:style-name="P90"/>
					<text:p text:style-name="P94">8. Supplementary Agreements </text:p>
					<text:p text:style-name="P90"/>
					<text:p text:style-name="P90">The entire degree program is offered in English. The external student declares he / she masters the English language in word and in writing to the extent necessary for an academic degree program.</text:p>
					<text:p text:style-name="P90"/>
					<text:p text:style-name="P90"/>
					<text:p text:style-name="P90">External students in the program are required to obtain and maintain computer equipment which allows them to participate in the distance learning elements. The total cost of acquisition and operation (including costs for Internet and e-mail) shall be borne by the external student. </text:p>
				</xsl:if>
				<text:p text:style-name="P75">
					<text:span text:style-name="T26">
					<xsl:choose>
						<xsl:when test="studienplan_sprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
							9. 
						</xsl:when>
						<xsl:otherwise>
							8. 
						</xsl:otherwise>
					</xsl:choose>
					</text:span>
					<text:span text:style-name="T25"> Unwirksamkeit von Vertrags-bestimmungen</text:span>
				</text:p>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="P21">Sollten einzelne Bestimmungen dieses Vertrages unwirksam oder nichtig sein oder werden, so berührt dies die Gültigkeit der übrigen Bestimmungen dieses Vertrages nicht. </text:p>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="P25">
					<text:span text:style-name="T22">
					<xsl:choose>
						<xsl:when test="studienplan_sprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
							10. 
						</xsl:when>
						<xsl:otherwise>
							9. 
						</xsl:otherwise>
					</xsl:choose>
					</text:span>
					<text:span text:style-name="T21"> Ausfertigungen, Gebühren, Gerichtsstand, geltendes Recht</text:span>
				</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P43">Die Ausfertigung dieses Vertrages erfolgt in zweifacher Ausführung. Ein Original verbleibt im zuständigen Administrationsbüro des Fachhochschul-Studienganges. Eine Ausfertigung wird der ao. Studentin bzw. dem ao. Studenten übergeben.</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P18">Für Streitigkeiten aus diesem Vertrag gilt österreichisches Recht als vereinbart, allfällige Klagen gegen den Erhalter sind beim sachlich zuständigen Gericht in Wien einzubringen.</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P18">Die englische Übersetzung des deutschsprachigen Vertrages dient nur als Referenz. Rechtsgültigkeit hat ausschließlich der deutsche Vertrag.</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P22">Der Ausbildungsvertrag ist gebührenfrei.</text:p>
				<text:p text:style-name="P26">
					<text:span text:style-name="T22">
					<xsl:choose>
						<xsl:when test="studienplan_sprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
							9. 
						</xsl:when>
						<xsl:otherwise>
							8. 
						</xsl:otherwise>
					</xsl:choose>
					</text:span>
					<text:span text:style-name="T21"> Invalidity of Contractual Provisions</text:span>
				</text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P14">If any provision of this agreement should be or become invalid or void, this shall not affect the validity of the remaining provisions of this agreement.</text:p>
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P25">
					<text:span text:style-name="T22">
					<xsl:choose>
						<xsl:when test="studienplan_sprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
							10. 
						</xsl:when>
						<xsl:otherwise>
							9. 
						</xsl:otherwise>
					</xsl:choose>
					</text:span>
					<text:span text:style-name="T21"> Copies, Fees, Place of Jurisdiction, Applicable Law</text:span>
				</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P43">This contract is provided in duplicate. An original remains with the competent administrations office of the University of Applied Sciences’ Degree Program. A copy is given to the external student.</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P18">In respect of any disputes arising from this contract, Austrian law shall apply. Any complaints about the operator should be introduced at the competent court in Vienna.</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P18">The English translation of the German contract is intended as a reference only. Only the German version of this contract is legally valid in a Court of Law.</text:p>
				<text:p text:style-name="P18"/>
				<text:p text:style-name="P18">The training contract is free of charge. </text:p>
				<text:p text:style-name="P14"/>
			</text:section>
			<text:section text:style-name="Sect3" text:name="Bereich4">
				<text:p text:style-name="P14"/>
				<text:p text:style-name="P27"/>
				<table:table table:name="Tabelle1" table:style-name="Tabelle1">
					<table:table-column table:style-name="Tabelle1.A"/>
					<table:table-column table:style-name="Tabelle1.B"/>
					<table:table-column table:style-name="Tabelle1.C"/>
					<table:table-row table:style-name="Tabelle1.1">
						<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
							<text:p text:style-name="P28"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P28"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
							<text:p text:style-name="P27">Wien (Vienna), <xsl:value-of select="datum_aktuell"/></text:p>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle1.1">
						<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
							<text:p text:style-name="P76">Ort, Datum (City, Date) </text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P28"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
							<text:p text:style-name="P76">Ort, Datum (City, Date) </text:p>
							<text:p text:style-name="P28"/>
							<text:p text:style-name="P28"/>
							<text:p text:style-name="P28"/>
							<text:p text:style-name="P28"/>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle1.1">
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P30">
								<text:span text:style-name="T29">Die ao. Studentin/der ao. Student /<text:line-break/>ggf. gesetzliche VertreterInnen</text:span>
							</text:p>
							<text:p text:style-name="P30">
								<text:span text:style-name="T32">(The external student /<text:line-break/>if necessary legal representatives)</text:span>
							</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P28"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P29">Für die FH Technikum Wien</text:p>
							<text:p text:style-name="P31">
								<text:span text:style-name="T31">(For the UAS Technikum Wien)</text:span>
							</text:p>
						</table:table-cell>
					</table:table-row>
				</table:table>
				<text:p text:style-name="Standard"/>
			</text:section>
		</office:text>
</xsl:template>
</xsl:stylesheet>