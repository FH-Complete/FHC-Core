<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" 
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
				version="1.0"
				xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
				xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" 
				xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" 
				xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" 
				xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0">

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="ausbildungsvertraege">

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
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="0cm"/>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="0cm"/>
					<style:tab-stop style:position="0.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="start" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:language="de" fo:country="AT"/>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:language="de" fo:country="AT" style:language-asian="ar" style:country-asian="SA" style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
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
			<style:paragraph-properties fo:line-height="130%" fo:text-align="start" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="8.751cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%">
				<style:tab-stops>
					<style:tab-stop style:position="8.752cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="7pt" fo:language="en" fo:country="US" style:font-size-asian="7pt" style:font-name-complex="Arial" style:font-size-complex="7pt"/>
		</style:style>
		<style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001534a7"/>
		</style:style>
		<style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P20" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P21" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P22" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P23" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00159b76" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P24" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001892c5" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P25" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00161927" officeooo:paragraph-rsid="00161927" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P26" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="normal" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="P27" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P28" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P29" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001892c5"/>
		</style:style>
		<style:style style:name="P30" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P31" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00159b76" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P32" style:family="paragraph" style:parent-style-name="First_5f_20_5f_Page">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P33" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P34" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
					<style:tab-stop style:position="14.503cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P35" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="P36" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
					<style:tab-stop style:position="14.503cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P37" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P38" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P39" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P40" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P41" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P42" style:family="paragraph" style:parent-style-name="Textkörper_20_3">
			<style:paragraph-properties fo:line-height="130%" fo:orphans="0" fo:widows="0"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P43" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="page"/>
		</style:style>
		<style:style style:name="P44" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P45" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P46" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
		</style:style>
		<style:style style:name="P47" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P48" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P49" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00159b76" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P50" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P51" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="00161927" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P52" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P53" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P54" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P55" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:language-asian="zxx" style:country-asian="none" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P56" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:language-asian="zxx" style:country-asian="none" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P57" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P58" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="en" fo:country="US" fo:font-weight="bold" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P59" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P60" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="10pt" officeooo:paragraph-rsid="001b1e78" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="P61" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="P62" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001b1e78"/>
		</style:style>
		<style:style style:name="P63" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		</style:style>
		<style:style style:name="P64" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties officeooo:paragraph-rsid="001b1e78" fo:background-color="transparent"/>
		</style:style>
		<style:style style:name="P65" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="column"/>
		</style:style>
		<style:style style:name="P66" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:break-before="column"/>
			<style:text-properties officeooo:paragraph-rsid="001534a7"/>
		</style:style>
		<style:style style:name="P67" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P68" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="00159b76" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P69" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
		</style:style>
		<style:style style:name="P70" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="column">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P71" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false">
				<style:tab-stops>
					<style:tab-stop style:position="1.251cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" fo:background-color="transparent" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P72" style:family="paragraph" style:parent-style-name="Heading_20_1" style:list-style-name="" style:master-page-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false" style:page-number="1"/>
			<style:text-properties style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="P73" style:family="paragraph" style:parent-style-name="Heading_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
		</style:style>
		<style:style style:name="P74" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P75" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P76" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P77" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P78" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P79" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P80" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="WW8Num4">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P81" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P82" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:rsid="001534a7" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P83" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P84" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001534a7" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P85" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" officeooo:paragraph-rsid="001b1e78" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P86" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P87" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P88" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="column"/>
			<style:text-properties officeooo:paragraph-rsid="001534a7"/>
		</style:style>
		<style:style style:name="P89" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1" style:list-style-name="">
			<style:paragraph-properties fo:margin-left="0.4cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="-0.4cm" style:auto-text-indent="false"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P90" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
			<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="P91" style:family="paragraph" style:parent-style-name="Standard1">
			<style:paragraph-properties fo:line-height="130%"/>
			<style:text-properties fo:color="#ff3333" fo:font-size="16pt" style:font-size-asian="16pt" style:font-name-complex="Arial" style:font-size-complex="16pt"/>
		</style:style>
		<style:style style:name="P92" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:line-height="130%" fo:text-align="left" style:justify-single-word="false"/>
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
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T6" style:family="text">
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T7" style:family="text">
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T8" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:font-weight="bold" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T9" style:family="text">
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T10" style:family="text">
			<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T11" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T12" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T13" style:family="text">
			<style:text-properties fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T14" style:family="text">
			<style:text-properties style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T15" style:family="text">
			<style:text-properties style:font-name-asian="Arial"/>
		</style:style>
		<style:style style:name="T16" style:family="text">
			<style:text-properties officeooo:rsid="001534a7" style:font-name-asian="Arial"/>
		</style:style>
		<style:style style:name="T17" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-name-asian="Arial"/>
		</style:style>
		<style:style style:name="T18" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T19" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" style:font-name-asian="Arial" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="T20" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" fo:font-weight="bold" fo:background-color="transparent" loext:char-shading-value="0" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T21" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-style-complex="italic"/>
		</style:style>
		<style:style style:name="T22" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="T23" style:family="text">
			<style:text-properties fo:language="en" fo:country="US" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T24" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
		</style:style>
		<style:style style:name="T25" style:family="text">
			<style:text-properties fo:font-size="9pt" style:font-name-asian="Arial" style:font-size-asian="9pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T26" style:family="text">
			<style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-name-asian="Arial" style:font-size-asian="9pt" style:font-name-complex="Arial"/>
		</style:style>
		<style:style style:name="T27" style:family="text">
			<style:text-properties style:language-complex="zxx" style:country-complex="none"/>
		</style:style>
		<style:style style:name="T28" style:family="text">
			<style:text-properties fo:background-color="transparent" loext:char-shading-value="0"/>
		</style:style>
		<style:style style:name="T29" style:family="text">
			<style:text-properties officeooo:rsid="001534a7"/>
		</style:style>
		<style:style style:name="T30" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T31" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:background-color="transparent" loext:char-shading-value="0" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T32" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" fo:background-color="transparent" loext:char-shading-value="0" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T33" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="bold" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T34" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="DE" fo:font-weight="normal" style:font-name-asian="Times New Roman" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="T35" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" style:font-name-asian="Times New Roman" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T36" style:family="text">
			<style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
		</style:style>
		<style:style style:name="T37" style:family="text">
			<style:text-properties style:language-asian="ar" style:country-asian="SA"/>
		</style:style>
		<style:style style:name="T38" style:family="text">
			<style:text-properties style:language-asian="zxx" style:country-asian="none"/>
		</style:style>
		<style:style style:name="T39" style:family="text">
			<style:text-properties officeooo:rsid="00161927"/>
		</style:style>
		<style:style style:name="T40" style:family="text">
			<style:text-properties fo:font-weight="normal" style:language-asian="ar" style:country-asian="SA" style:font-weight-asian="normal" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="T41" style:family="text">
			<style:text-properties fo:font-weight="normal" style:font-weight-asian="normal" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="T42" style:family="text">
			<style:text-properties fo:font-size="12pt" fo:font-weight="bold" style:font-size-asian="12pt" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="T43" style:family="text">
			<style:text-properties fo:font-size="12pt" style:font-size-asian="12pt"/>
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
				<text:h text:style-name="P72" text:outline-level="1">Ausbildungsvertrag</text:h>
					<!-- Ueberprueft ob benoetigte Datenfelder leer sind -->
					<xsl:if test="svnr = ''"><text:p text:style-name="P91">Keine Sozialversicherungsnummer oder Ersatzkennzeichen vorhanden</text:p></xsl:if>
					<xsl:if test="gebdatum = ''"><text:p text:style-name="P91">Kein Geburtsdatum vorhanden</text:p></xsl:if>
					<xsl:if test="titel_kurzbz = ''"><text:p text:style-name="P91">Kein akademischer Grad vorhanden</text:p></xsl:if>
					<xsl:if test="student_maxsemester = ''"><text:p text:style-name="P91">Keine Ausbildungsdauer vorhanden</text:p></xsl:if>
				
				<text:p text:style-name="P9"/>
				<text:p text:style-name="P9"/>
				<text:p text:style-name="P9"/>
				<text:p text:style-name="P11">Dieser Vertrag regelt das Rechtsverhältnis zwischen </text:p>
				<text:p text:style-name="P12">
					<text:span text:style-name="T6">dem Verein Fachhochschule Technikum Wien,</text:span>
					<text:span text:style-name="T14">1060 Wien, Mariahilfer Straße 37-39 (kurz „Erhalter“ genannt) einerseits </text:span>
					<text:span text:style-name="T6">und</text:span>
				</text:p>
				<text:p text:style-name="P11"/>
				<text:h text:style-name="P73" text:outline-level="1">
					<text:span text:style-name="T27">Training Contract</text:span>
				</text:h>
				<text:p text:style-name="P9"/>
				<text:p text:style-name="P9"/>
				<text:p text:style-name="P9"/>
				<text:p text:style-name="P20">This contract governs the legal relationship between </text:p>
				<text:p text:style-name="P20">
					<text:span text:style-name="T5">the University of Applied Sciences Technikum Wien Association,</text:span> 1060 Vienna, Mariahilferstraße 37-39 (referred to as &quot;operator&quot;) on the one hand <text:span text:style-name="T5">and</text:span>
				</text:p>
				<text:p text:style-name="P26"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
			</text:section>
			<text:section text:style-name="Sect2" text:name="Bereich2">
				<text:p text:style-name="P7">
					<text:span text:style-name="T12">Familienname (Surname):<text:tab/><xsl:value-of select="nachname"/></text:span>
				</text:p>
				<text:p text:style-name="P16">Vorname (First Name):<text:tab/><xsl:value-of select="vorname"/></text:p>
				<text:p text:style-name="P7">
					<text:span text:style-name="T12">Akademischer Titel (Academic degree):<text:tab/><xsl:value-of select="titelpre"/><xsl:value-of select="titelpost"/></text:span>
				</text:p>
				<text:p text:style-name="P7">
					<text:span text:style-name="T12">Adresse (Address):<text:tab/><xsl:value-of select="strasse"/></text:span>
				</text:p>
				<text:p text:style-name="P7">
					<text:span text:style-name="T10">
						<text:tab/><xsl:value-of select="plz"/></text:span>
				</text:p>
				<text:p text:style-name="P4">
					<text:span text:style-name="T12">Geburtsdatum (Date of birth): <text:tab/><xsl:value-of select="gebdatum"/></text:span>
				</text:p>
				<text:p text:style-name="P4">
					<text:span text:style-name="T10">Sozialversicherungsnr. </text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T10">
							<text:note text:id="ftn1" text:note-class="footnote">
								<text:note-citation>1</text:note-citation>
								<text:note-body>
									<text:p text:style-name="Standard">
										<text:span text:style-name="T15">
											<text:s/>
										</text:span>
										<text:span text:style-name="T2">Gemäß § 3 Absatz 1 des Bildungsdokumentationsgesetzes und der Bildungsdokumentationsverordnung-Fachhochschulen <text:s/>hat der Erhalter die Sozialversicherungsnummer zu erfassen und gemäß § 7 Absatz 2 im Wege der Agentur für Qualitätssicherung und Akkreditierung Austria an das zuständige Bundesministerium und die Bundesanstalt Statistik Österreich zu übermitteln.</text:span>
									</text:p>
									<text:p text:style-name="P8"/>
								</text:note-body>
							</text:note>
						</text:span>
					</text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T10"> </text:span>
					</text:span>
					<text:span text:style-name="T13">:<text:tab/><xsl:value-of select="svnr"/></text:span>
				</text:p>
				<text:p text:style-name="P5">
					<text:span text:style-name="T12">
						<text:tab/>(Social security number)</text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T12">
							<text:note text:id="ftn2" text:note-class="footnote">
								<text:note-citation>2</text:note-citation>
								<text:note-body>
									<text:p text:style-name="Standard">
										<text:span text:style-name="T17">
											<text:s/>
										</text:span>
										<text:span text:style-name="T4">Pursuant to § 3 section 1 of the Education Documentation Act and the Education Documentation Regulation for Universities of Applied Sciences, the operator shall record the social security number pursuant to § 7 paragraph 2 and shall transfer it via the Agency for Quality Assurance and Accreditation Austria to the competent Ministry and Statistics Austria.</text:span>
									</text:p>
								</text:note-body>
							</text:note>
						</text:span>
					</text:span>
					<text:span text:style-name="Footnote_20_Symbol">
						<text:span text:style-name="T12"> </text:span>
					</text:span>
					<text:span text:style-name="T12">: </text:span>
				</text:p>
				<text:p text:style-name="P17"/>
				<text:p text:style-name="P17"/>
				<text:p text:style-name="P3">
					<text:span text:style-name="T12">(kurz „Studentin“ bzw. „Student“ genannt) <text:tab/>
					</text:span>
					<text:span text:style-name="T11">(referred to as &quot;student&quot;)</text:span>
					<text:span text:style-name="T11">on the other,</text:span>
				</text:p>
				<text:p text:style-name="P3">
					<text:span text:style-name="T12">andererseits, <text:tab/>
					</text:span>
				</text:p>
				<text:p text:style-name="P15"/>
				<text:p text:style-name="P14"/>
			</text:section>
			<text:section text:style-name="Sect1" text:name="Bereich3">
				<text:p text:style-name="P2">
					<text:span text:style-name="T12">im Rahmen des <xsl:value-of select="studiengang_typ"/>-Studienganges </text:span>
					<text:span text:style-name="T32">„<xsl:value-of select="studiengang"/>“</text:span>
					<text:span text:style-name="T32">, StgKz <xsl:value-of select="studiengang_kz"/>, </text:span>
					<text:span text:style-name="T31">in der Organisationsform eines </text:span>
					<text:span text:style-name="T32">
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
				<text:p text:style-name="P30">within the <xsl:value-of select="studiengang_typ"/> degree program 
					<text:span text:style-name="T5">„<xsl:value-of select="studiengang_englisch"/>“, program abbrev. <xsl:value-of select="studiengang_kz"/>,</text:span> in the organizational form of a 
					<text:span text:style-name="T5">
					<xsl:choose>
						<xsl:when test="orgform = 'BB'" >
							part-time course.
						</xsl:when>
						<xsl:when test="orgform = 'VZ'" >
							full-time course.
						</xsl:when>
						<xsl:when test="orgform = 'PT'" >
							part time course.
						</xsl:when>
						<xsl:otherwise>
							distance learning course.
						</xsl:otherwise>
					</xsl:choose>
					</text:span>
				</text:p>
				<text:p text:style-name="P43">
					<text:span text:style-name="T18">1. Ausbildungsort</text:span>
				</text:p>
				<text:p text:style-name="P19"/>
				<text:p text:style-name="P20">Studienort sind die Räumlichkeiten der FH Technikum Wien, 1200 Wien, Höchstädtplatz und 1210 Wien, Giefinggasse. Bei Bedarf kann der Erhalter einen anderen Studienort in Wien festlegen, außerhochschulische Aktivitäten (zB Exkursionen) können auch außerhalb von Wien stattfinden. </text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T18">2. Vertragsgrundlage</text:span>
				</text:p>
				<text:p text:style-name="P10"/>
				<text:p text:style-name="P20">Die Ausbildung erfolgt auf der Grundlage des Fachhochschul-Studiengesetzes, BGBl. Nr. 340/1993 idgF, des Hochschul-Qualitätssicherungsgesetzes, BGBl. Nr. 74/2011 idgF, des Akkreditierungsbescheides des Board der AQ Austria vom 9.5.2012, GZ FH12020016 idgF und des Fördervertrags mit dem Bundesministerium für Wissenschaft und Forschung idgF. </text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="Standard">
					<text:bookmark-start text:name="_Ref78860434"/>
					<text:span text:style-name="T18">3. Ausbildungsdauer</text:span>
					<text:bookmark-end text:name="_Ref78860434"/>
				</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">Die Ausbildungsdauer beträgt <xsl:value-of select="student_maxsemester"/> Semester.</text:p>
				<text:p text:style-name="P28"/>
				<text:p text:style-name="P28">Die Studentin bzw. der Student hat das Recht, eine Anerkennung nachgewiesener Kenntnisse beim Studiengang zu beantragen. Eine solche Anerkennung setzt voraus, dass die erworbenen Kenntnisse mit dem Inhalt und dem Umfang der Lehrveranstaltung bzw. eines Berufspraktikums gleichwertig sind und bewirkt die Anrechnung der entsprechenden Lehrveranstaltung oder des Berufspraktikums.</text:p>
				<text:p text:style-name="P28"/>
				<text:p text:style-name="P65">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T18">1. Place of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P27"/>
				<text:p text:style-name="P28">Places of training are the premises of the UAS Technikum Wien, 1200 Vienna, Höchstädt-platz and 1210 Vienna, Giefinggasse. If necessary, the operator may specify a different place of study in Vienna. Non-university activities (e.g. excursions) may take place away from Vienna.</text:p>
				<text:p text:style-name="P28"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T18">2. Contractual Basis</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P28"/>
				<text:p text:style-name="P28">The training is based on the University of Applied Sciences Studies Act, Federal Law Gazette No. 340/1993 as amended, the Higher Education Quality Assurance Act, Federal Law Gazette I No. 74/2011 as amended, the notification of accreditation by the Board of AQ Austria from 9.5.2012, GZ FH12020016 as amended and the subsidy agreement with the Federal Ministry responsible for UASs as amended.</text:p>
				<text:p text:style-name="P28"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T18">3. Duration of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P28"/>
				<text:p text:style-name="P20">
					<text:span text:style-name="T28">The training period lasts <xsl:value-of select="student_maxsemester"/> semesters.</text:span>
				</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">The student has the right to apply to the degree program for Recognition of Prior Learning (RPL). Such recognition requires that the knowledge previously acquired is equivalent in content and scope to that of the course or internship and means that the student will be exempted from the respective course or internship.</text:p>
				<text:p text:style-name="P43">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T18">4. Ausbildungsabschluss</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">Die Ausbildung endet mit der positiven Absolvierung der das jeweilige Studium abschließenden kommissionellen Prüfung. Nach dem Abschluss der vorgeschriebenen Prüfungen wird der akademische Grad <xsl:value-of select="studiengang_typ"/> of Science in Engineering (<xsl:value-of select="titel_kurzbz"/>) durch das FH-Kollegium verliehen. </text:p>
				<text:p text:style-name="P42"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="T18">5. Rechte und Pflichten des Erhalters</text:span>
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
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
					<text:bookmark-end text:name="_Ref78865698"/>
				</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P21">Der Erhalter führt eine periodische Überprüfung des Studiums im Hinblick auf Relevanz und Aktualität durch und ist im Einvernehmen mit dem FH-Kollegium berechtigt, daraus Änderungen des akkreditierten Studienganges abzuleiten.</text:p>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="P21">
					<text:span text:style-name="T29">Der Erhalter ist berechtigt, die Daten der/des Studierenden an den FH Technikum Wien Alumni Club zu übermitteln. Der Alumni Club ist der AbsolventInnenverein der FH Technikum Wien. Er hat zum Ziel, AbsolventInnen, Studierende und Lehrende miteinander zu vernetzen sowie AbsolventInnen laufend über Aktivitäten an der FH Technikum Wien zu informieren. Einer Zusendung von Informationen durch den Alumni Club kann jederzeit widersprochen werden.</text:span>
				</text:p>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">5.2 Pflichten</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="Standard">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T33"/>
					</text:span>
				</text:p>
				<text:list xml:id="list8019569486514330718" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P77">Der Erhalter verpflichtet sich zur ordnungsgemäßen Planung und Durchführung des Studienganges in der Regelstudiendauer. Der Erhalter ist verpflichtet, allfällige Änderungen des akkreditierten Studienganges zeitgerecht bekannt zu geben.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P66">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T21">4. Formal Completion of Training</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="P21">The training ends with the positive completion of the final examination before a committee for the respective course. After completion of the required examinations, the academic degree <xsl:value-of select="studiengang_typ"/> of Science in Engineering (<xsl:value-of select="titel_kurzbz"/>) is awarded by the University of Applied Sciences Council. </text:p>
				<text:p text:style-name="P84"/>
				<text:p text:style-name="P18">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T18">5. Rights and Duties of the Operator</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P18">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T23"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P29">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P29">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">5.1 Rights</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P24">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T34"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P21">The operator performs a periodic review of the course in terms of relevance and topicality, and is authorized, in consultation with the University of Applied Sciences Council, to deduce from this changes in the accredited degree program.</text:p>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="P21">
					<text:span text:style-name="T29">The operator is entitled to communicate a student’s data to the UAS Technikum Wien Alumni Club. The Alumni Club is the graduate association of the UAS Technikum Wien. Its goal is to provide links between graduates, students and lecturers as well as to keep graduates informed of the activities at the UAS Technikum Wien. A mailing of information from the Alumni Club can be vetoed at any time.</text:span>
				</text:p>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="P21"/>
				<text:p text:style-name="P18">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">5.2 Duties </text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P18">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T33"/>
					</text:span>
				</text:p>
				<text:list xml:id="list101939819813669" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P77">The operator undertakes to plan and hold the degree program in a proper manner within the expected time period. The operator is obliged to give adequate notice of any changes to the accredited degree program.</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P88">
							<text:soft-page-break/>
							<text:span text:style-name="T30">Der Erhalter verpflichtet sich, jedenfalls folgende Dokumente zur Verfügung zu stellen: Studierendenausweis, Diploma Supplement, Urkunde über die Verleihung des akademischen Grades, Studienerfolgsbestätigung, Inskriptionsbestätigung.</text:span>
						</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">Der Erhalter verpflichtet sich zur sorgfaltsgemäßen Verwendung der personenbezogenen Daten der Studierenden. Die Daten werden nur im Rahmen der gesetzlichen und vertraglichen Verpflichtungen sowie des Studienbetriebes verwendet und nicht an nicht berechtigte Dritte weitergegeben.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P81"/>
				<text:p text:style-name="P62">
					<text:span text:style-name="T18">6. Rechte und Pflichten der Studierenden</text:span>
				</text:p>
				<text:p text:style-name="P62">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P62">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">6.1 Rechte</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P62">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P60">Die Studentin bzw. der Student hat das Recht auf </text:p>
				<text:list xml:id="list101941487787851" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P78">einen Studienbetrieb gemäß den im akkreditierten Studiengang idgF und in der Satzung der FH Technikum Wien idgF festgelegten Bedingungen;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P78">Unterbrechung der Ausbildung aus nachgewiesenen zwingenden persönlichen, gesundheitlichen oder beruflichen Gründen.</text:p>
					</text:list-item>
				</text:list>
				<text:list xml:id="list101939651802726" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P86">
							<text:s/>The operator undertakes to make the following documents available in any event: student ID card, diploma supplement, certificate attesting to the award of the academic degree, confirmation of success in the degree program, confirmation of registration.</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P80">The operator is committed to use the personal data of the students carefully. The data is only to be used within the operator’s legal and contractual obligations as well as its program of studies and is not to be handed on to unauthorized third parties.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P83"/>
				<text:p text:style-name="P83"/>
				<text:p text:style-name="P83"/>
				<text:p text:style-name="P62">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T18">6. Rights and Duties of the Students</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P62">
					<text:span text:style-name="Strong_20_Emphasis"/>
				</text:p>
				<text:p text:style-name="P62">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T10"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P62">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">6.1 Rights </text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P62">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P48">The student has the right to </text:p>
				<text:list xml:id="list235829763333" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P78">a course of study according to the conditions specified in the accredited degree program as amended and in the Statutes of the UAS Technikum Wien as amended;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P78">interrupt the training due to proof of compelling personal, health or professional reasons.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P46">
					<text:span text:style-name="T8">6.2 Pflichten</text:span>
				</text:p>
				<text:p text:style-name="Standard"/>
				<text:p text:style-name="P20">6.2.1 Einhaltung studienrelevanter Bestimmungen</text:p>
				<text:p text:style-name="P20">Die Studentin bzw. der Student ist verpflichtet, insbesondere folgende Bestimmungen einzuhalten:</text:p>
				<text:list xml:id="list101939549345495" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P76">Studienordnung und Studienrechtliche Bestimmungen / Prüfungsordnung idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">Hausordnung idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">Brandschutzordnung idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">Bibliotheksordnung idgF</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">Die für den jeweiligen Studiengang geltende/n Laborordnung/en idgF</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">Diese Dokumente sind öffentlich zugänglich unter www.technikum-wien.at.</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P48">6.2.2 Studienbeitrag </text:p>
				<text:p text:style-name="P48">Die Studentin bzw. der Student ist verpflichtet, zwei Wochen vor Beginn jedes Semesters (StudienanfängerInnen: bis 20. August vor Studienbeginn) einen Studienbeitrag gemäß Fachhochschul-Studiengesetz in der Höhe von derzeit € 363,36 netto pro Semester zu entrichten. Dies gilt auch in Semestern mit DiplomandInnenenstatus o.ä. Im Falle einer Erhöhung des gesetzlichen Studienbeitrags-satzes erhöht sich der angeführte Betrag entsprechend. Die vollständige Bezahlung des Studienbeitrags ist Voraussetzung für die Aufnahme bzw. die Fortsetzung des Studiums. Bei Nichtantritt des Studiums oder Abbruch zu Beginn oder während des Semesters verfällt der Studienbeitrag. </text:p>
				<text:p text:style-name="P69">
					<text:span text:style-name="T8">6.2 Duties</text:span>
				</text:p>
				<text:p text:style-name="Standard"/>
				<text:p text:style-name="P20">6.2.1 Compliance with regulations relevant to the studies</text:p>
				<text:p text:style-name="P20">In particular the student undertakes to comply with the following regulations:</text:p>
				<text:list xml:id="list235829763334" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P76">Study Regulations and Studies Act Provisions / Examination Regulations as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">General Rules of Conduct as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">Fire Regulations as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">Library Regulations as amended</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">The Laboratory Regulations applicable to the respective degree program as amended</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">These documents are publically available at www.technikum-wien.at.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">6.2.2 Tuition Fees</text:p>
				<text:p text:style-name="P48">Two weeks before the beginning of each semester (new students: up to August 20 before taking up studies) the student undertakes to pay tuition fees according to the University of Applied Sciences Studies Act currently to the sum of € 363.36 net payable per semester. This also applies in semesters with graduand status etc. In the event of an increase in the legal tuition fees rate, the amount quoted will increase accordingly. Full payment of the tuition fees is a prerequisite both for enrolling on the course and continuing with the degree program. For non-commencement or termination of the study at the beginning or during the semester, the tuition fee is forfeited. </text:p>
				<text:p text:style-name="P67">
					<text:soft-page-break/>6.2.3 ÖH-Beitrag</text:p>
				<text:p text:style-name="P20">Gemäß § 4 Abs 10 FHStG sind Studierende an Fachhochschulen Mitglieder der Österreichischen HochschülerInnenschaft (ÖH). Der/Die Studierende hat semesterweise einen ÖH-Beitrag an den Erhalter zu entrichten, der diesen an die ÖH abführt. Die Entrichtung des Betrags ist Voraussetzung für die Zulassung zum Studium bzw. für dessen Fortsetzung.</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">6.2.4 Kaution </text:p>
				<text:p text:style-name="P20">Im Zuge der Einschreibung ist der Nachweis über die einbezahlte Kaution zu erbringen. </text:p>
				<text:p text:style-name="P20">Die Kaution beträgt € 150,–. </text:p>
				<text:p text:style-name="P20">Bei Nichtantritt des Studiums oder Abbruch während des ersten oder zweiten Semesters verfällt die Kaution. </text:p>
				<text:p text:style-name="P48">Bei aufrechtem Inskriptionsverhältnis zu Beginn des zweiten Semesters wird die Kaution auf den Unkostenbeitrag (siehe nächster Punkt) des ersten und zweiten Semesters angerechnet. </text:p>
				<text:p text:style-name="P48">
					<text:span text:style-name="T22"/>
				</text:p>
				<text:p text:style-name="P48">
					<text:span text:style-name="T22">6.2.5 Unkostenbeitrag </text:span>
				</text:p>
				<text:p text:style-name="P48">Pro Semester ist ein Unkostenbeitrag zu entrichten, wobei es sich nicht um einen Pauschalbetrag handelt. Der Unkostenbeitrag stellt eine Abgeltung für über das Normalmaß hinausgehende Serviceleistungen der FH dar, z.B. Freifächer, Beratung/Info Auslands-studium, Sponsionsfeiern, Vorträge / Job-börse, Mensa etc. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">Die Höhe des Unkostenbeitrages beträgt derzeit € 75,– pro Semester. Eine allfällige Anpassung wird durch Aushang bekannt gemacht. </text:p>
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
				<text:p text:style-name="P67">6.2.3 Austrian Student Union fee</text:p>
				<text:p text:style-name="P20">In accordance with § 4 para.10 FHStG (University of Applied Sciences Studies Act) students at Universities of Applied Sciences are members of the Austrian National Union of Students (ÖH). Each semester the student is required to pay an ÖH fee to the operator. This fee is then paid to the ÖH. Payment of this fee is a prerequisite for admission to the course of study or its continuation.</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">6.2.4 Deposit </text:p>
				<text:p text:style-name="P20">During the process of registration, proof of deposit paid must be provided.</text:p>
				<text:p text:style-name="P20">The deposit is € 150,–. </text:p>
				<text:p text:style-name="P20">For non-commencement of studies or termination during the first or second semester, the deposit shall be forfeited. </text:p>
				<text:p text:style-name="P20">If, by the beginning of the second semester, registration has been completed correctly the deposit will be credited to the contribution towards expenses (see next point) for the first and second semester. </text:p>
				<text:p text:style-name="P79"/>
				<text:p text:style-name="P79">6.2.5 Contribution towards Expenses </text:p>
				<text:p text:style-name="P48">A contribution towards expenses, which is not a lump sum, is payable per semester. The contribution towards expenses represents compensation for the services provided by the UAS that go beyond the normal level, such as electives, counseling/ information about studying abroad, graduation ceremonies, lectures/job market, cafeteria, etc. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">The amount of the contribution is currently <text:line-break/>€ 75,– per semester. Any possible adjustment is posted on the noticeboard. </text:p>
				<text:p text:style-name="P48">
				<xsl:choose>
					<xsl:when test="semesterStudent = 3" >
						In 
					</xsl:when>
					<xsl:otherwise>
						From 
					</xsl:otherwise>
				</xsl:choose>
				the 3rd semester the contribution towards expenses must be paid together with the tuition fees before the start of the semester. </text:p>
				<text:p text:style-name="P45">Bei Vertragsauflösung vor Studienabschluss aus Gründen, die die Studentin bzw. der Student zu vertreten hat, oder auf deren bzw. dessen Wunsch, wird der Unkostenbeitrag zur Abdeckung der dem Erhalter erwachsenen administrativen Zusatzkosten einbehalten. </text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">6.2.6 Lehr- und Lernbehelfe </text:p>
				<text:p text:style-name="P20">Die Anschaffung unterrichtsbezogener Literatur und individueller Lernbehelfe ist durch den Unkostenbeitrag nicht abgedeckt. Eventuelle zusätzliche Kosten, die sich beispielsweise durch die studiengangsbezogene, gemeinsame Anschaffung von Lehr- bzw. Lernbehelfen (Skripten, CDs, Bücher, Projektmaterialien, Kopierpapier etc.) oder durch Exkursionen ergeben, werden von jedem Studiengang individuell eingehoben. </text:p>
				<text:p text:style-name="P75"/>
				<text:p text:style-name="P75">6.2.7 Beibringung persönlicher Daten </text:p>
				<text:p text:style-name="P74">Die Studentin bzw. der Student ist verpflichtet, persönliche Daten beizubringen, die auf Grund eines Gesetzes, einer Verordnung oder eines Bescheides vom Erhalter <text:span text:style-name="T15">erfasst werden müssen oder zur Erfüllung des Ausbildungsvertrages bzw. für den Studienbetrieb unerlässlich sind.</text:span>
				</text:p>
				<text:p text:style-name="P74"/>
				<text:p text:style-name="P74">6.2.8 Aktualisierung eigener Daten und Bezug von Informationen</text:p>
				<text:p text:style-name="P53">Die Studentin bzw. der Student hat unaufgefordert dafür zu sorgen, dass die von ihr/ihm beigebrachten Daten aktuell sind. Änderungen sind der Studiengangsassistenz unverzüglich schriftlich mitzuteilen. Darüber hinaus trifft sie/ihn die Pflicht, sich von studienbezogenen Informationen, die ihr/ihm an die vom Erhalter zur Verfügung gestellte Emailadresse zugestellt werden, in geeigneter Weise Kenntnis zu verschaffen.<text:bookmark-start text:name="_Ref78863824"/>
				</text:p>
				<text:p text:style-name="P31">If the contract is cancelled before graduation for reasons that are the fault of the student, or on their wishes, the contribution towards expenses shall be deducted to cover the additional administrative costs borne by the operator. </text:p>
				<text:p text:style-name="P23"/>
				<text:p text:style-name="P23">6.2.6 Teaching Aids and Learning Tools</text:p>
				<text:p text:style-name="P23">The acquisition of teaching-related literature and individual learning tools is not covered by the contribution towards expenses. Any additional costs, which arise for example from the course-related, joint purchase of teaching and / or learning materials (scripts, CDs, books, project materials, copying paper, etc.) or result from field trips, are levied by each individual degree program. </text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">6.2.7 Providing Personal Data</text:p>
				<text:p text:style-name="P51">The student is obliged to produce personal data which must be registered because of a law, regulation or a decision by the operator, or is essential fort he fulfilling of the training contract or fort he program of studies.</text:p>
				<text:p text:style-name="P51"/>
				<text:p text:style-name="P51"/>
				<text:p text:style-name="P51"/>
				<text:p text:style-name="P51">6.2.8 Updating personal data and the retrieval of information</text:p>
				<text:p text:style-name="P48">
					<text:span text:style-name="T39">Without being reminded, the student must ensure that the data provided by them is up-to-date. Changes are to be immediately communicated to the administrative assistant in writing. Furthermore, it is the students’ responsibility to make themselves suitably aware of information relating to their studies which has been sent to them at the email address provided for them by the operator.</text:span>
					<text:bookmark-end text:name="_Ref78863824"/>
				</text:p>
				<text:p text:style-name="P44">
					<text:bookmark-start text:name="_Ref78867653"/>
					<text:span text:style-name="T40">6.2.9 Verwertungsrechte</text:span>
					<text:span text:style-name="T41"> </text:span>
				</text:p>
				<text:p text:style-name="P39">Sofern nicht im Einzelfall andere Regelungen zwischen dem Erhalter und der Studentin oder dem Studenten getroffen wurden, ist die Studentin oder der Student verpflichtet, dem Erhalter die Rechte an Forschungs- und Entwicklungsergebnissen auf dessen schriftliche Anfrage hin anzubieten.</text:p>
				<text:p text:style-name="P39"/>
				<text:p text:style-name="P41">6.2.10 Aufzeichnungen und Mitschnitte</text:p>
				<text:p text:style-name="P39">Es ist der/dem Studierenden ausdrücklich untersagt, Lehrveranstaltungen als Ganzes oder nur Teile davon aufzuzeichnen und/oder mitzuschneiden (z.B. durch Film- und/oder Tonaufnahmen oder sonstige hierfür geeignete audiovisuelle Mittel). Darüber hinaus ist jede Form der öffentlichen Zurverfügungstellung (drahtlos oder drahtgebunden) der vorgenannten Aufnahmen z.B. in sozialen Netzwerken wie Facebook, StudiVZ etc, aber auch auf Youtube usw. oder durch sonstige für diese Zwecke geeignete Kommunikations-mittel untersagt. Diese Regelungen gelten sinngemäß auch für Skripten, sonstige Lernbehelfe und Prüfungsangaben. </text:p>
				<text:p text:style-name="P39">Ausgenommen hiervon ist eine Aufzeichnung zu ausschließlichen Lern-, Studien- und Forschungszwecken und zum privaten Gebrauch, sofern hierfür der Vortragende vorab ausdrücklich seine schriftliche Zustimmung erteilt hat. </text:p>
				<text:p text:style-name="P39"/>
				<text:p text:style-name="P39">
					<text:span text:style-name="T37">6.2.11 Geheimhaltungspflicht</text:span>
					<text:bookmark-end text:name="_Ref78867653"/>
				</text:p>
				<text:p text:style-name="P20">Die Studentin bzw. der Student ist zur Geheimhaltung von Forschungs- und Entwicklungsaktivitäten und -ergebnissen gegenüber Dritten verpflichtet. </text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P67">6.2.9 Exploitation Rights</text:p>
				<text:p text:style-name="P20">Unless other arrangements have been agreed between the operator and the student at an individual level, on written request, the student undertakes to offer the operator the rights to research and development results. </text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">6.2.10 Recordings </text:p>
				<text:p text:style-name="P20">It is expressly forbidden for the student to record lectures in part or in total (e.g. by using film and / or sound recordings or other audio-visual means suitable for this purpose). In addition, any form of making the aforementioned recordings publically available (wired or wireless) for example in social networks such as Facebook, StudiVZ etc, but also on Youtube, etc., or by other means of communication designed for these purposes is strictly prohibited. These regulations shall apply correspondingly to scripts, other learning aids and examination data. </text:p>
				<text:p text:style-name="P20">The only exception is a recording exclusively for the purpose of learning, study and research and for personal use, provided that the lecturer has expressly granted his / her prior written consent.</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">6.2.11 Confidentiality </text:p>
				<text:p text:style-name="P20">The student is required to maintain confidentiality towards third parties of research and development activities and results. </text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P87">
					<text:soft-page-break/>6.2.12 Unfallmeldung </text:p>
				<text:p text:style-name="P39">Im Falle eines Unfalles mit körperlicher Verletzung des/der Studierenden im Zusammenhang mit dem Studium ist die/der Studierende verpflichtet, diesen innerhalb von drei Tagen dem Studiengangssekretariat zu melden. Dies betrifft auch Wegunfälle zur oder von der FH.</text:p>
				<text:p text:style-name="P39"/>
				<text:p text:style-name="P39">6.2.13 Schadensmeldung </text:p>
				<text:p text:style-name="P39">Im Falle des Eintretens eines Schadens am Inventar der Fachhochschule ist der/die Studierende verpflichtet, diesen innerhalb von drei Tagen dem Studiengangssekretariat zu melden. Allfällige Haftungsansprüche bleiben hiervon unberührt.</text:p>
				<text:p text:style-name="P39"/>
				<text:p text:style-name="P92">6.2.14 Rückgabeverpflichtung bei Studienende </text:p>
				<text:p text:style-name="P39">Die Studentin bzw. der Student ist verpflichtet, bei einer Beendigung des Studiums unverzüglich alle zur Verfügung gestellten Gerätschaften, Bücher, Schlüssel und sonstige Materialien zurückzugeben.</text:p>
				<text:p text:style-name="P39"/>
				<text:p text:style-name="P59">
					<text:span text:style-name="T7">7. Beendigung des Vertrages</text:span>
				</text:p>
				<text:p text:style-name="P2">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T30"/>
					</text:span>
				</text:p>
				<text:p text:style-name="P58">7.1 Auflösung im beiderseitigen Einvernehmen</text:p>
				<text:p text:style-name="P39">Im beiderseitigen Einvernehmen ist die Auflösung des Ausbildungsvertrages jederzeit ohne Angabe von Gründen möglich. Die einvernehmliche Auflösung bedarf der Schriftform. </text:p>
				<text:p text:style-name="P39"/>
				<text:p text:style-name="P6">
					<text:span text:style-name="T8">7.2 Kündigung durch die Studentin bzw. den Studenten</text:span>
				</text:p>
				<text:p text:style-name="P22">Die Studentin bzw. der Student kann den Ausbildungsvertrag schriftlich jeweils zum Ende eines Semesters kündigen. </text:p>
				<text:p text:style-name="P22"/>
				<text:p text:style-name="P31">6.2.12 Accident Report</text:p>
				<text:p text:style-name="P20">In the event of an accident with bodily injury to a student in connection with his / her studies, he / she is obliged to report this to the administrative assistant of the degree program within three days. This also applies to accidents on the way to or from the UAS. </text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">6.2.13 Damage Report</text:p>
				<text:p text:style-name="P20">If any damage should be caused to the inventory of the University of Applied Sciences, the student undertakes to report this to the administrative assistant of the degree program within three days. Any liability claims shall remain unaffected.</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P92">6.2.14 Obligation to Return Borrowed Items</text:p>
				<text:p text:style-name="P20">The student undertakes to return promptly all equipment, books, keys and other materials that have been made available, when the course is finished or broken off.</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T42">7. Termination of the contract</text:span>
					</text:span>
				</text:p>
				<text:p text:style-name="P57"/>
				<text:p text:style-name="P61">
					<text:span text:style-name="T5">7.1 Annulment by Mutual Agreement</text:span>
				</text:p>
				<text:p text:style-name="P61"/>
				<text:p text:style-name="P20">By mutual consent, the annulment of the training contract is possible at any time, without notice and for any reason. The amicable annulment must be put down in writing. </text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">7.2 Termination by the Student</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">The student may terminate the training contract in writing at the end of each semester.</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P70">
					<text:soft-page-break/>
					<text:span text:style-name="T8">7.3 Automatische Beendigung des Vertrages</text:span>
				</text:p>
				<text:p text:style-name="P22">Nach erfolgreicher Beendigung des Studiums endet der Vertrag automatisch mit der Verleihung des akademischen Grades.</text:p>
				<text:p text:style-name="P22">Der Vertrag endet automatisch durch die negative Beurteilung der letztmöglichen Prüfungswiederholung.</text:p>
				<text:p text:style-name="P22"/>
				<text:p text:style-name="P6">
					<text:span text:style-name="T8">7.4 Ausschluss durch den Erhalter</text:span>
				</text:p>
				<text:p text:style-name="P20">Der Erhalter kann die Studentin bzw. den Studenten aus wichtigem Grund mit sofortiger Wirkung vom weiteren Studium ausschließen, und zwar beispielsweise wegen </text:p>
				<text:list xml:id="list101941293020952" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P76">nicht genügender Leistung im Sinne der Prüfungsordnung;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">mehrmalige<text:span text:style-name="T24">m</text:span> unentschuldigte<text:span text:style-name="T24">n</text:span> Verletzen der Anwesenheitspflicht; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">wiederholte<text:span text:style-name="T24">m</text:span> Nichteinhalten von Prüfungsterminen und Abgabeterminen für Seminararbeiten, Projektarbeiten etc.; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">schwerwiegender bzw. wiederholter Verstöße gegen die Hausordnung;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">persönliche<text:span text:style-name="T24">m</text:span> Verhalten, das zu einer Beeinträchtigung des Images und/oder Betriebes des Studienganges, der Fach-hochschule bzw. des Erhalters oder von Personen führt, die für die Fachhochschule bzw. den Erhalter tätig sind;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">Verletzung der Verpflichtung, dem Erhalter die Rechte an Forschungs- und Entwicklungsergebnissen anzubieten (siehe Pkt. 6.2.9);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">Verletzung der Geheimhaltungspflicht (siehe Pkt. 6.2.11); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">strafgerichtlicher Verurteilung (wobei die Art des Deliktes und der Grad der Schuld berücksichtigt werden);</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P89"/>
				<text:p text:style-name="P69">
					<text:span text:style-name="Strong_20_Emphasis">
						<text:span text:style-name="T8">7.3 Automatic Ending of the Contract</text:span>
					</text:span>
					<text:span text:style-name="Strong_20_Emphasis"> </text:span>
				</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">When the course of study has been completed successfully the contract ends automatically with the awarding of the academic degree.</text:p>
				<text:p text:style-name="P20">The contract ends automatically if the last possible repeat of an examination ends in failure.</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P13">
					<text:span text:style-name="T8">7.4 Expulsion by the Operator</text:span>
				</text:p>
				<text:p text:style-name="P20">The operator may exclude the student from further study with immediate effect for good cause, for example because of</text:p>
				<text:p text:style-name="P20"/>
				<text:list xml:id="list101941597515944" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P76">insufficient achievement for the purposes of the examination regulations;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">repeated unexcused violation of the compulsory attendance regulation;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">repeated non-compliance with examination dates and deadlines for seminar papers, project work etc.; </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">serious or repeated violation of the house rules;</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">personal behavior, which leads to an adverse effect on the image and / or operation of the course, the university or the operator or on persons who are working for the university or the operator;<text:line-break/>
						</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">breach of the obligation to offer the operator the rights to research and development results (see Section 6.2.9);<text:line-break/>
						</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">breach of confidentiality (see Section 6.2.11);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">a criminal conviction (whereby the nature of the offence and the level of culpability are taken into account);</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P89"/>
				<text:list xml:id="list101939703511298" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P90">Nichterfüllung finanzieller Verpflichtungen trotz Mahnung (z.B. Unkostenbeitrag, Studienbeitrag etc.); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">Weigerung zur Beibringung von Daten (siehe Pkt. 6.2.7);</text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">Plagiieren im Rahmen wissenschaftlicher Arbeiten.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">Der Ausschluss kann mündlich erklärt werden. Mit Ausspruch des Ausschlusses endet der Ausbildungsvertrag, es sei denn, es wird ausdrücklich auf einen anderen Endtermin hingewiesen. Eine schriftliche Bestätigung des Ausschlusses wird innerhalb von zwei Wochen nach dessen Ausspruch per Post an die bekannt gegebene Adresse abgeschickt oder auf andere geeignete Weise übermittelt. </text:p>
				<text:p text:style-name="P20">Gleichzeitig mit dem Ausspruch des Ausschlusses kann auch ein Hausverbot verhängt werden. </text:p>
				<text:p text:style-name="P20"/>
				<xsl:if test="studienplan_sprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
					<text:p text:style-name="P62">
						<text:span text:style-name="T18">8. Ergänzende Vereinbarungen</text:span>
					</text:p>
					<text:p text:style-name="P48"/>
					<text:p text:style-name="P53">
						<text:span text:style-name="T38">Das gesamte Studienprogramm wird in englischer Sprache angeboten. Die Studentin bzw. der Student erklärt, die englische Sprache in Wort und Schrift in dem für eine akademische Ausbildung erforderlichen Ausmaß zu beherrschen.</text:span>
					</text:p>
					<text:p text:style-name="P71"/>
					<text:p text:style-name="P53">Studierende des Studiengangs sind verpflichtet, eine EDV-Ausstattung zu beschaffen und zu unterhalten, die es ermöglicht, an den Fernlehrelementen teilzunehmen. Die gesamten Kosten der Anschaffung und des Betriebs (inkl. Kosten für Internet und e-mail) trägt der Student bzw. die Studentin. </text:p>
				</xsl:if>
				<text:list xml:id="list101940709679101" text:continue-numbering="true" text:style-name="WW8Num4">
					<text:list-item>
						<text:p text:style-name="P86">non-fulfilment of the financial obligations, despite a reminder (e.g. contribution towards expenses, tuition fees , etc.); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">refusal to provide any data (see section 6.2.7); </text:p>
					</text:list-item>
					<text:list-item>
						<text:p text:style-name="P76">plagiarism in academic work.</text:p>
					</text:list-item>
				</text:list>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">The expulsion can be explained verbally. Once notice of the expulsion has been given the training contract ends unless another deadline is explicitly made clear. Within two weeks of notice being given, written confirmation of the expulsion is mailed by post to the address provided or transmitted in any other appropriate manner.</text:p>
				<text:p text:style-name="P20">Simultaneously with notice of expulsion being given an exclusion order from entering the building may also be imposed.</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20"/>
				<xsl:if test="studienplan_sprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
					<text:p text:style-name="P64">
						<text:span text:style-name="T18">8. Supplementary Agreements</text:span>
					</text:p>
					<text:p text:style-name="P54"/>
					<text:p text:style-name="P53">
						<text:span text:style-name="T38">The entire degree program is offered in English. The student declares he / she masters the</text:span>
						<text:span text:style-name="T38">English language in word and in writing to the extent necessary for an academic degree program.</text:span>
					</text:p>
					<text:p text:style-name="P56"/>
					<text:p text:style-name="P56"/>
					<text:p text:style-name="P55">Students in the program are required to obtain and maintain computer equipment which allows them to participate in the distance learning elements. The total cost of acquisition and operation (including costs for Internet and e-mail) shall be borne by the student. </text:p>
				</xsl:if>
				<text:p text:style-name="P65">
					<text:soft-page-break/>
					<text:span text:style-name="T20">
					<xsl:choose>
						<xsl:when test="studienplan_sprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
							9. 
						</xsl:when>
						<xsl:otherwise>
							8. 
						</xsl:otherwise>
					</xsl:choose>
					Unwirksamkeit von Vertrags-bestimmungen</text:span>
					<text:span text:style-name="T28"> </text:span>
				</text:p>
				<text:p text:style-name="P28"/>
				<text:p text:style-name="P28">Sollten einzelne Bestimmungen dieses Vertrages unwirksam oder nichtig sein oder werden, so berührt dies die Gültigkeit der übrigen Bestimmungen dieses Vertrages nicht. </text:p>
				<text:p text:style-name="P28"/>
				<text:p text:style-name="P62">
					<text:span text:style-name="T18">
					<xsl:choose>
						<xsl:when test="studienplan_sprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
							10. 
						</xsl:when>
						<xsl:otherwise>
							9. 
						</xsl:otherwise>
					</xsl:choose>
					Ausfertigungen, Gebühren, Gerichtsstand, geltendes Recht</text:span>
				</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">Die Ausfertigung dieses Vertrages erfolgt in zweifacher Ausführung. Ein Original verbleibt im zuständigen Administrationsbüro des Fachhochschul-Studienganges. Eine Ausfertigung wird der Studentin bzw. dem Studenten übergeben.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">Für Streitigkeiten aus diesem Vertrag gilt österreichisches Recht als vereinbart, allfällige Klagen gegen den Erhalter sind beim sachlich zuständigen Gericht in Wien einzubringen.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">Die englische Übersetzung des deutschsprachigen Vertrages dient nur als Referenz. Rechtsgültigkeit hat ausschließlich der deutsche Vertrag.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P53">Der Ausbildungsvertrag ist gebührenfrei.</text:p>
				<text:p text:style-name="P65">
					<text:span text:style-name="T18">
					<xsl:choose>
						<xsl:when test="studienplan_sprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
							9. 
						</xsl:when>
						<xsl:otherwise>
							8. 
						</xsl:otherwise>
					</xsl:choose>
					Invalidity of Contractual Provisions</text:span>
				</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P20">If any provision of this agreement should be or become invalid or void, this shall not affect the validity of the remaining provisions of this agreement.</text:p>
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P62">
					<text:span text:style-name="T18">
					<xsl:choose>
						<xsl:when test="studienplan_sprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL')">
							10. 
						</xsl:when>
						<xsl:otherwise>
							9. 
						</xsl:otherwise>
					</xsl:choose>
					Copies, Fees, Place of Jurisdiction, Applicable Law</text:span>
				</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">This contract is provided in duplicate. An original remains with the competent administrations office of the University of Applied Sciences’ Degree Program. A copy is given to the student.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">In respect of any disputes arising from this contract, Austrian law shall apply. Any complaints about the operator should be introduced at the competent court in Vienna.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P48">The English translation of the German contract is intended as a reference only. Only the German version of this contract is legally valid in a Court of Law.</text:p>
				<text:p text:style-name="P48"/>
				<text:p text:style-name="P60">
					<text:span text:style-name="T36">The training contract is free of charge. </text:span>
				</text:p>
				<text:p text:style-name="P20"/>
			</text:section>
			<text:section text:style-name="Sect3" text:name="Bereich4">
				<text:p text:style-name="P20"/>
				<text:p text:style-name="P33"/>
				<table:table table:name="Tabelle1" table:style-name="Tabelle1">
					<table:table-column table:style-name="Tabelle1.A"/>
					<table:table-column table:style-name="Tabelle1.B"/>
					<table:table-column table:style-name="Tabelle1.C"/>
					<table:table-row table:style-name="Tabelle1.1">
						<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
							<text:p text:style-name="P34"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P34"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
							<text:p text:style-name="P33">Wien (Vienna), <xsl:value-of select="datum_aktuell"/></text:p>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle1.1">
						<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
							<text:p text:style-name="P38">Ort, Datum (City, Date) </text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P34"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
							<text:p text:style-name="P38">Ort, Datum (City, Date) </text:p>
							<text:p text:style-name="P34"/>
							<text:p text:style-name="P34"/>
							<text:p text:style-name="P34"/>
							<text:p text:style-name="P34"/>
						</table:table-cell>
					</table:table-row>
					<table:table-row table:style-name="Tabelle1.1">
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P36">
								<text:span text:style-name="T24">Die Studentin/der Student /<text:line-break/>ggf. gesetzliche VertreterInnen</text:span>
							</text:p>
							<text:p text:style-name="P36">
								<text:span text:style-name="T26">(The student /<text:line-break/>if necessary legal representatives)</text:span>
							</text:p>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P34"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
							<text:p text:style-name="P35">Für die FH Technikum Wien</text:p>
							<text:p text:style-name="P37">
								<text:span text:style-name="T25">(For the UAS Technikum Wien)</text:span>
							</text:p>
						</table:table-cell>
					</table:table-row>
				</table:table>
				<text:p/>
			</text:section>
		</office:text>
</xsl:template>
</xsl:stylesheet>