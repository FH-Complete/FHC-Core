<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="ausbildungsvertraege">

<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
	<office:scripts/>
	<office:font-face-decls>
	<style:font-face style:name="Wingdings" svg:font-family="Wingdings" style:font-pitch="variable" style:font-charset="x-symbol"/>
	<style:font-face style:name="Symbol" svg:font-family="Symbol" style:font-family-generic="roman" style:font-pitch="variable" style:font-charset="x-symbol"/>
	<style:font-face style:name="Lohit Hindi1" svg:font-family="'Lohit Hindi'"/>
	<style:font-face style:name="Courier New" svg:font-family="'Courier New'" style:font-family-generic="modern"/>
	<style:font-face style:name="Lucida Grande" svg:font-family="'Lucida Grande', 'Times New Roman'" style:font-family-generic="roman"/>
	<style:font-face style:name="Optima" svg:font-family="Optima, 'Times New Roman'" style:font-family-generic="roman"/>
	<style:font-face style:name="ヒラギノ角ゴ Pro W3" svg:font-family="'ヒラギノ角ゴ Pro W3'" style:font-family-generic="roman"/>
	<style:font-face style:name="Courier New1" svg:font-family="'Courier New'" style:font-family-generic="modern" style:font-pitch="fixed"/>
	<style:font-face style:name="Liberation Serif" svg:font-family="'Liberation Serif'" style:font-family-generic="roman" style:font-pitch="variable"/>
	<style:font-face style:name="Times New Roman" svg:font-family="'Times New Roman'" style:font-family-generic="roman" style:font-pitch="variable"/>
	<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
	<style:font-face style:name="Liberation Sans" svg:font-family="'Liberation Sans'" style:font-family-generic="swiss" style:font-pitch="variable"/>
	<style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="swiss" style:font-pitch="variable"/>
	<style:font-face style:name="Droid Sans" svg:font-family="'Droid Sans'" style:font-family-generic="system" style:font-pitch="variable"/>
	<style:font-face style:name="Lohit Hindi" svg:font-family="'Lohit Hindi'" style:font-family-generic="system" style:font-pitch="variable"/>
	</office:font-face-decls>
	<office:automatic-styles>
	<style:style style:name="Tabelle1" style:family="table">
		<style:table-properties style:width="15.252cm" table:align="left" style:writing-mode="lr-tb"/>
	</style:style>
	<style:style style:name="Tabelle1.A" style:family="table-column">
		<style:table-column-properties style:column-width="7.001cm"/>
	</style:style>
	<style:style style:name="Tabelle1.B" style:family="table-column">
		<style:table-column-properties style:column-width="1.251cm"/>
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
		<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
		<style:tab-stops>
			<style:tab-stop style:position="0cm"/>
			<style:tab-stop style:position="6.251cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
	</style:style>
	<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%"/>
		<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-name-complex="Arial" style:font-size-complex="8pt"/>
	</style:style>
	<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
		<style:tab-stops>
			<style:tab-stop style:position="1.251cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-name-complex="Arial" style:font-size-complex="8pt"/>
	</style:style>
	<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%"/>
		<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
		<style:tab-stops>
			<style:tab-stop style:position="6.251cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
		<style:tab-stops>
			<style:tab-stop style:position="0cm"/>
			<style:tab-stop style:position="6.251cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false">
		<style:tab-stops>
			<style:tab-stop style:position="1.251cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties fo:font-size="10pt" fo:background-color="#ffff00" style:font-size-asian="10pt" style:language-asian="zxx" style:country-asian="none" style:font-name-complex="Arial" style:language-complex="zxx" style:country-complex="none"/>
	</style:style>
	<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
		<style:text-properties fo:language="de" fo:country="AT"/>
	</style:style>
	<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%"/>
		<style:text-properties fo:font-size="7pt" style:font-size-asian="7pt" style:font-name-complex="Arial" style:font-size-complex="7pt"/>
	</style:style>
	<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties fo:font-size="7pt" style:font-size-asian="7pt" style:font-name-complex="Arial" style:font-size-complex="7pt"/>
	</style:style>
	<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%"/>
		<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
	</style:style>
	<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
	</style:style>
	<style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
		<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-name-complex="Arial" style:font-size-complex="8pt"/>
	</style:style>
	<style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page">
		<style:tab-stops>
			<style:tab-stop style:position="1.251cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-name-complex="Arial" style:font-size-complex="8pt"/>
	</style:style>
	<style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false">
		<style:tab-stops>
			<style:tab-stop style:position="1.251cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:font-size="10pt" fo:background-color="#ffff00" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.252cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false" fo:keep-with-next="always">
		<style:tab-stops>
			<style:tab-stop style:position="1.251cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
		<style:tab-stops>
			<style:tab-stop style:position="1.251cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
	</style:style>
	<style:style style:name="P20" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false">
		<style:tab-stops>
			<style:tab-stop style:position="1.251cm"/>
			<style:tab-stop style:position="14.503cm" style:type="right"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
	</style:style>
	<style:style style:name="P21" style:family="paragraph" style:parent-style-name="Standard">
		<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="-0.25cm" fo:margin-top="0.106cm" fo:margin-bottom="0cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false">
		<style:tab-stops>
			<style:tab-stop style:position="1.251cm"/>
			<style:tab-stop style:position="14.503cm" style:type="right"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
	</style:style>
	<style:style style:name="P22" style:family="paragraph" style:parent-style-name="Heading_20_1" style:master-page-name="First_20_Page">
		<style:paragraph-properties style:page-number="1"/>
		<style:text-properties style:language-complex="zxx" style:country-complex="none"/>
	</style:style>
	<style:style style:name="P23" style:family="paragraph" style:parent-style-name="Heading_20_2">
		<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="-0.635cm" style:auto-text-indent="false">
		<style:tab-stops>
			<style:tab-stop style:position="0.751cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:font-style="normal" style:font-style-asian="normal"/>
	</style:style>
	<style:style style:name="P24" style:family="paragraph" style:parent-style-name="Heading_20_2">
		<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="-0.635cm" style:auto-text-indent="false">
		<style:tab-stops>
			<style:tab-stop style:position="0.751cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:language="de" fo:country="AT" fo:font-style="normal" style:language-asian="ar" style:country-asian="SA" style:font-style-asian="normal"/>
	</style:style>
	<style:style style:name="P25" style:family="paragraph" style:parent-style-name="Heading_20_2">
		<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="-0.635cm" style:auto-text-indent="false">
		<style:tab-stops>
			<style:tab-stop style:position="0.751cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:background-color="#ffff00"/>
	</style:style>
	<style:style style:name="P26" style:family="paragraph" style:parent-style-name="Heading_20_2">
		<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0.071cm" fo:margin-bottom="0.212cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="-0.635cm" style:auto-text-indent="false">
		<style:tab-stops>
			<style:tab-stop style:position="0.751cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:language="de" fo:country="AT" fo:font-style="normal" style:language-asian="ar" style:country-asian="SA" style:font-style-asian="normal"/>
	</style:style>
	<style:style style:name="P27" style:family="paragraph" style:parent-style-name="Heading_20_3">
		<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.106cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA"/>
	</style:style>
	<style:style style:name="P28" style:family="paragraph" style:parent-style-name="Heading_20_3">
		<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA"/>
	</style:style>
	<style:style style:name="P29" style:family="paragraph" style:parent-style-name="Heading_20_4">
		<style:paragraph-properties fo:margin-top="0.212cm" fo:margin-bottom="0.071cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" fo:font-weight="normal" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA" style:font-weight-asian="normal" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P30" style:family="paragraph" style:parent-style-name="Heading_20_4">
		<style:paragraph-properties fo:margin-top="0.353cm" fo:margin-bottom="0.071cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="normal" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA" style:font-weight-asian="normal" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P31" style:family="paragraph" style:parent-style-name="Heading_20_4">
		<style:paragraph-properties fo:margin-top="0.353cm" fo:margin-bottom="0.071cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" fo:font-weight="normal" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA" style:font-weight-asian="normal" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P32" style:family="paragraph" style:parent-style-name="Heading_20_4">
		<style:paragraph-properties fo:margin-top="0.353cm" fo:margin-bottom="0.071cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="en" fo:country="US" fo:font-weight="normal" style:font-size-asian="10pt" style:language-asian="ar" style:country-asian="SA" style:font-weight-asian="normal" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P33" style:family="paragraph" style:parent-style-name="Footer">
		<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
	</style:style>
	<style:style style:name="P34" style:family="paragraph" style:parent-style-name="Header">
		<style:text-properties fo:language="de" fo:country="AT" style:language-asian="none" style:country-asian="none"/>
	</style:style>
	<style:style style:name="P35" style:family="paragraph" style:parent-style-name="Textkörper_20_2">
		<style:paragraph-properties fo:line-height="130%"/>
		<style:text-properties style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P36" style:family="paragraph" style:parent-style-name="Textkörper_20_3">
		<style:paragraph-properties fo:line-height="130%" fo:orphans="0" fo:widows="0"/>
		<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P37" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
		<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
	</style:style>
	<style:style style:name="P38" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
		<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-name-complex="Arial" style:font-size-complex="8pt"/>
	</style:style>
	<style:style style:name="P39" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
		<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties fo:font-size="8pt" fo:language="de" fo:country="AT" style:font-size-asian="8pt" style:font-name-complex="Arial" style:font-size-complex="8pt"/>
	</style:style>
	<style:style style:name="P40" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
		<style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
		<style:text-properties style:language-complex="zxx" style:country-complex="none"/>
	</style:style>
	<style:style style:name="P41" style:family="paragraph" style:parent-style-name="Standard1">
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
		<style:text-properties fo:color="#000000" style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P42" style:family="paragraph" style:parent-style-name="Standard1">
		<style:paragraph-properties fo:line-height="130%"/>
		<style:text-properties fo:color="#ff3333" fo:font-size="16pt" style:font-size-asian="16pt" style:font-name-complex="Arial" style:font-size-complex="16pt"/>
	</style:style>
	<style:style style:name="P43" style:family="paragraph" style:parent-style-name="Heading_20_2">
		<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0.071cm" fo:margin-bottom="0.212cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="-0.635cm" style:auto-text-indent="false" fo:keep-with-next="always">
		<style:tab-stops>
			<style:tab-stop style:position="0.751cm"/>
		</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:language="de" fo:country="AT" fo:font-style="normal" style:language-asian="ar" style:country-asian="SA" style:font-style-asian="normal"/>
	</style:style>
	<style:style style:name="P44" style:family="paragraph" style:parent-style-name="Textkörper_20_3">
		<style:paragraph-properties fo:line-height="130%" fo:orphans="0" fo:widows="0" fo:keep-together="always" />
		<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="P45" style:family="paragraph" style:parent-style-name="Heading_20_2">
		<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0.071cm" fo:margin-bottom="0.212cm" fo:line-height="130%" fo:text-align="justify" style:justify-single-word="false" fo:text-indent="-0.635cm" style:auto-text-indent="false" fo:break-before="page">
			<style:tab-stops>
				<style:tab-stop style:position="0.751cm"/>
			</style:tab-stops>
		</style:paragraph-properties>
		<style:text-properties fo:language="de" fo:country="AT" fo:font-style="normal" style:language-asian="ar" style:country-asian="SA" style:font-style-asian="normal"/>
	</style:style>
	<style:style style:name="T1" style:family="text">
		<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold"/>
	</style:style>
	<style:style style:name="T2" style:family="text">
		<style:text-properties fo:font-weight="bold" fo:background-color="#ffff00" style:font-weight-asian="bold"/>
	</style:style>
	<style:style style:name="T3" style:family="text">
		<style:text-properties fo:font-size="9pt" style:font-name-asian="Arial" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
	</style:style>
	<style:style style:name="T4" style:family="text">
		<style:text-properties style:font-name-asian="Arial"/>
	</style:style>
	<style:style style:name="T5" style:family="text">
		<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="T6" style:family="text">
		<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
	</style:style>
	<style:style style:name="T7" style:family="text">
		<style:text-properties fo:font-size="8pt" fo:background-color="#ffff00" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
	</style:style>
	<style:style style:name="T8" style:family="text">
		<style:text-properties fo:font-size="8pt" fo:font-weight="bold" fo:background-color="#ffff00" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt"/>
	</style:style>
	<style:style style:name="T9" style:family="text">
		<style:text-properties style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="T10" style:family="text">
		<style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
	</style:style>
	<style:style style:name="T11" style:family="text">
		<style:text-properties fo:background-color="#ffff00"/>
	</style:style>
	<style:style style:name="T12" style:family="text">
		<style:text-properties style:language-complex="zxx" style:country-complex="none"/>
	</style:style>
	<style:style style:name="T13" style:family="text">
		<style:text-properties fo:font-style="normal" style:font-style-asian="normal"/>
	</style:style>
	<style:style style:name="T14" style:family="text"/>
	<style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
		<style:graphic-properties fo:margin-left="0.319cm" fo:margin-right="0.319cm" style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:padding="0.026cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
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
		<text:h text:style-name="P22" text:outline-level="1" text:is-list-header="true">Ausbildungsvertrag</text:h>
				<!-- Ueberprueft ob benoetigte Datenfelder leer sind -->
				<xsl:if test="gebdatum = ''"><text:p text:style-name="P42">Kein Geburtsdatum vorhanden</text:p></xsl:if>
				<xsl:if test="titel_kurzbz = ''"><text:p text:style-name="P42">Kein akademischer Grad vorhanden</text:p></xsl:if>
				<xsl:if test="student_maxsemester = ''"><text:p text:style-name="P42">Keine Ausbildungsdauer vorhanden</text:p></xsl:if>

		<text:p text:style-name="P2"/>
		<text:p text:style-name="P4">Lorem ipsum dolor sit amet, consetetur sadipscing</text:p>
		<text:p text:style-name="P4">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam</text:p>
		<text:p text:style-name="P2"/>
		<text:p text:style-name="P6">Familienname: <text:tab/><xsl:value-of select="nachname"/></text:p>
		<text:p text:style-name="P6">Vorname: <text:tab/><xsl:value-of select="vorname"/></text:p>
		<text:p text:style-name="P6">Akademische/r Titel: <text:tab/>
		<xsl:choose>
			<xsl:when test="titelpre!='' or titelpost!=''">
			<xsl:value-of select="titelpre"/><xsl:value-of select="titelpost"/>
			</xsl:when>
			<xsl:otherwise>-</xsl:otherwise>
		</xsl:choose>
		</text:p>
		<text:p text:style-name="P6">Adresse: <text:tab/><xsl:value-of select="strasse"/>; <xsl:value-of select="plz"/></text:p>
		<text:p text:style-name="P7">Geburtsdatum: <text:tab/><text:database-display text:table-name="" text:table-type="table" text:column-name="Geb.datum"><xsl:value-of select="gebdatum"/></text:database-display></text:p>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P4">(kurz „Studentin“ bzw. „Student“ genannt) andererseits im Rahmen des <xsl:value-of select="studiengang_typ"/> Studienganges „<xsl:value-of select="studiengang"/>“, StgKz <xsl:value-of select="studiengang_kz"/>, in der Organisationsform eines
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
</text:p>
		<text:p text:style-name="P14"/>
		<text:list xml:id="list305698312" text:continue-numbering="false" text:style-name="WW8Num7">
		<text:list-item>
			<text:list>
			<text:list-item>
				<text:p text:style-name="P26">Ausbildungsort</text:p>
			</text:list-item>
			</text:list>
		</text:list-item>
		</text:list>
		<text:p text:style-name="P5">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et.</text:p>
		<text:p text:style-name="P36"/>
		<text:list xml:id="list932404618" text:continue-numbering="true" text:style-name="WW8Num7">
		<text:list-item>
			<text:list>
			<text:list-item>
				<text:p text:style-name="P26">Vertragsgrundlage</text:p>
			</text:list-item>
			</text:list>
		</text:list-item>
		</text:list>
		<text:p text:style-name="P5">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</text:p>
		<text:p text:style-name="P36"/>
		<text:list xml:id="list636990326" text:continue-numbering="true" text:style-name="WW8Num7">
		<text:list-item>
			<text:list>
			<text:list-item>
				<text:p text:style-name="P26">Ausbildungsdauer</text:p>
			</text:list-item>
			</text:list>
		</text:list-item>
		</text:list>
		<text:p text:style-name="P5">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
			<text:p text:style-name="P2"/>
			Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</text:p>
		<text:p text:style-name="P36"/>
		<text:list xml:id="list107841840" text:continue-numbering="true" text:style-name="WW8Num7">
		<text:list-item>
			<text:list>
			<text:list-item>
				<text:p text:style-name="P45">Ausbildungsabschluss</text:p>
			</text:list-item>
			</text:list>
		</text:list-item>
		</text:list>
		<text:p text:style-name="P44">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</text:p>
		<text:p text:style-name="P36"/>
		<text:list xml:id="list890989597" text:continue-numbering="true" text:style-name="WW8Num7">
		<text:list-item>
			<text:list>
			<text:list-item>
				<text:p text:style-name="P26">Rechte und Pflichten des Erhalters</text:p>
			</text:list-item>
			</text:list>
		</text:list-item>
		</text:list>
		<text:p text:style-name="P27"><text:bookmark-start text:name="_Ref78865698"/>5.1 Rechte<text:bookmark-end text:name="_Ref78865698"/></text:p>
		<text:p text:style-name="P5">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</text:p>
		<text:list xml:id="list1539722475" text:style-name="WW8Num4">
		<text:list-header>
			<text:p text:style-name="P39"/>
		</text:list-header>
		</text:list>
		<text:p text:style-name="P27">5.2 Pflichten</text:p>
		<text:list xml:id="list1245891399" text:continue-numbering="true" text:style-name="WW8Num4">
		<text:list-item>
			<text:p text:style-name="P40">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</text:p>
		</text:list-item>
		<text:list-item>
			<text:p text:style-name="P40">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</text:p>
		</text:list-item>
		<text:list-item>
			<text:p text:style-name="P40">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</text:p>
		</text:list-item>
		</text:list>

		<text:p text:style-name="P5"/>
		<text:p text:style-name="P5"/>
		<text:p text:style-name="P5"/>
		<text:p text:style-name="P18"><text:tab/><text:tab/><text:tab/><text:tab/><text:tab/><text:tab/><text:s text:c="8"/>Wien, <xsl:value-of select="datum_aktuell"/></text:p>
		<table:table table:name="Tabelle1" table:style-name="Tabelle1">
		<table:table-column table:style-name="Tabelle1.A"/>
		<table:table-column table:style-name="Tabelle1.B"/>
		<table:table-column table:style-name="Tabelle1.A"/>
		<table:table-row table:style-name="Tabelle1.1">
			<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
			<text:p text:style-name="P19">Ort, Datum</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
			<text:p text:style-name="P21"/>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
			<text:p text:style-name="P19">Ort, Datum</text:p>
			<text:p text:style-name="P19"/>
			<text:p text:style-name="P19"/>
			<text:p text:style-name="P19"/>
			</table:table-cell>
		</table:table-row>
		<table:table-row table:style-name="Tabelle1.1">
			<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P20">Die Studentin/der Student<text:line-break/>ggf. gesetzliche VertreterInnen</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
			<text:p text:style-name="P21"/>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
			<text:p text:style-name="P19">Lorem ipsum dolor sit amet</text:p>
			</table:table-cell>
		</table:table-row>
		</table:table>
	</office:text>
</xsl:template>
</xsl:stylesheet>
