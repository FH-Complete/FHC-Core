<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:xlink="http://www.w3.org/1999/xlink" 
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="zeugnisse">

<office:document-styles xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
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
	<office:styles>
		<style:default-style style:family="graphic">
			<style:graphic-properties svg:stroke-color="#3465a4" draw:fill-color="#729fcf" fo:wrap-option="no-wrap" draw:shadow-offset-x="0.3cm" draw:shadow-offset-y="0.3cm" draw:start-line-spacing-horizontal="0.283cm" draw:start-line-spacing-vertical="0.283cm" draw:end-line-spacing-horizontal="0.283cm" draw:end-line-spacing-vertical="0.283cm" style:flow-with-text="false"/>
			<style:paragraph-properties style:text-autospace="ideograph-alpha" style:line-break="strict" style:writing-mode="lr-tb" style:font-independent-line-spacing="false">
				<style:tab-stops/>
			</style:paragraph-properties>
			<style:text-properties style:use-window-font-color="true" style:font-name="Liberation Serif" fo:font-size="12pt" fo:language="de" fo:country="AT" style:letter-kerning="true" style:font-name-asian="SimSun" style:font-size-asian="10.5pt" style:language-asian="zh" style:country-asian="CN" style:font-name-complex="Mangal1" style:font-size-complex="12pt" style:language-complex="hi" style:country-complex="IN"/>
		</style:default-style>
		<style:default-style style:family="paragraph">
			<style:paragraph-properties fo:hyphenation-ladder-count="no-limit" style:text-autospace="ideograph-alpha" style:punctuation-wrap="hanging" style:line-break="strict" style:tab-stop-distance="1.251cm" style:writing-mode="page"/>
			<style:text-properties style:use-window-font-color="true" style:font-name="Liberation Serif" fo:font-size="12pt" fo:language="de" fo:country="AT" style:letter-kerning="true" style:font-name-asian="SimSun" style:font-size-asian="10.5pt" style:language-asian="zh" style:country-asian="CN" style:font-name-complex="Mangal1" style:font-size-complex="12pt" style:language-complex="hi" style:country-complex="IN" fo:hyphenate="false" fo:hyphenation-remain-char-count="2" fo:hyphenation-push-char-count="2"/>
		</style:default-style>
		<style:default-style style:family="table">
			<style:table-properties table:border-model="collapsing"/>
		</style:default-style>
		<style:default-style style:family="table-row">
			<style:table-row-properties fo:keep-together="auto"/>
		</style:default-style>
		<style:style style:name="Standard" style:family="paragraph" style:class="text">
			<style:text-properties style:font-name="Arial" fo:font-family="Arial" style:font-style-name="Standard" style:font-family-generic="swiss" style:font-pitch="variable" style:font-size-asian="10.5pt"/>
		</style:style>
		<style:style style:name="Heading" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Text_20_body" style:class="text">
			<style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.212cm" style:contextual-spacing="false" fo:keep-with-next="always"/>
			<style:text-properties style:font-name="Liberation Sans" fo:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable" fo:font-size="14pt" style:font-name-asian="Microsoft YaHei" style:font-family-asian="&apos;Microsoft YaHei&apos;" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="14pt" style:font-name-complex="Mangal1" style:font-family-complex="Mangal" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="14pt"/>
		</style:style>
		<style:style style:name="Text_20_body" style:display-name="Text body" style:family="paragraph" style:parent-style-name="Standard" style:class="text">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.247cm" style:contextual-spacing="false" fo:line-height="120%"/>
		</style:style>
		<style:style style:name="List" style:family="paragraph" style:parent-style-name="Text_20_body" style:class="list">
			<style:text-properties style:font-size-asian="12pt" style:font-name-complex="Mangal2" style:font-family-complex="Mangal"/>
		</style:style>
		<style:style style:name="Caption" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
			<style:paragraph-properties fo:margin-top="0.212cm" fo:margin-bottom="0.212cm" style:contextual-spacing="false" text:number-lines="false" text:line-number="0"/>
			<style:text-properties fo:font-size="12pt" fo:font-style="italic" style:font-size-asian="12pt" style:font-style-asian="italic" style:font-name-complex="Mangal2" style:font-family-complex="Mangal" style:font-size-complex="12pt" style:font-style-complex="italic"/>
		</style:style>
		<style:style style:name="Index" style:family="paragraph" style:parent-style-name="Standard" style:class="index">
			<style:paragraph-properties text:number-lines="false" text:line-number="0"/>
			<style:text-properties style:font-size-asian="12pt" style:font-name-complex="Mangal2" style:font-family-complex="Mangal"/>
		</style:style>
		<style:style style:name="Quotations" style:family="paragraph" style:parent-style-name="Standard" style:class="html">
			<style:paragraph-properties fo:margin-left="1cm" fo:margin-right="1cm" fo:margin-top="0cm" fo:margin-bottom="0.499cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
		</style:style>
		<style:style style:name="Title" style:family="paragraph" style:parent-style-name="Heading" style:next-style-name="Text_20_body" style:class="chapter">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="28pt" fo:font-weight="bold" style:font-size-asian="28pt" style:font-weight-asian="bold" style:font-size-complex="28pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="Subtitle" style:family="paragraph" style:parent-style-name="Heading" style:next-style-name="Text_20_body" style:class="chapter">
			<style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0.212cm" style:contextual-spacing="false" fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="18pt" style:font-size-asian="18pt" style:font-size-complex="18pt"/>
		</style:style>
		<style:style style:name="Heading_20_1" style:display-name="Heading 1" style:family="paragraph" style:parent-style-name="Heading" style:next-style-name="Text_20_body" style:default-outline-level="1" style:class="text">
			<style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.212cm" style:contextual-spacing="false"/>
			<style:text-properties fo:font-size="130%" fo:font-weight="bold" style:font-size-asian="130%" style:font-weight-asian="bold" style:font-size-complex="130%" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="Heading_20_2" style:display-name="Heading 2" style:family="paragraph" style:parent-style-name="Heading" style:next-style-name="Text_20_body" style:default-outline-level="2" style:class="text">
			<style:paragraph-properties fo:margin-top="0.353cm" fo:margin-bottom="0.212cm" style:contextual-spacing="false"/>
			<style:text-properties fo:font-size="115%" fo:font-weight="bold" style:font-size-asian="115%" style:font-weight-asian="bold" style:font-size-complex="115%" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="Heading_20_3" style:display-name="Heading 3" style:family="paragraph" style:parent-style-name="Heading" style:next-style-name="Text_20_body" style:default-outline-level="3" style:class="text">
			<style:paragraph-properties fo:margin-top="0.247cm" fo:margin-bottom="0.212cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#808080" fo:font-size="14pt" fo:font-weight="bold" style:font-size-asian="14pt" style:font-weight-asian="bold" style:font-size-complex="14pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="Table_20_Contents" style:display-name="Table Contents" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
			<style:paragraph-properties text:number-lines="false" text:line-number="0"/>
		</style:style>
		<style:style style:name="Objekt_20_mit_20_Pfeilspitze" style:display-name="Objekt mit Pfeilspitze" style:family="paragraph" style:parent-style-name="Standard" style:default-outline-level=""/>
		<style:style style:name="Objekt_20_mit_20_Schatten" style:display-name="Objekt mit Schatten" style:family="paragraph" style:parent-style-name="Standard" style:default-outline-level=""/>
		<style:style style:name="Objekt_20_ohne_20_Füllung" style:display-name="Objekt ohne Füllung" style:family="paragraph" style:parent-style-name="Standard" style:default-outline-level=""/>
		<style:style style:name="Objekt_20_ohne_20_Füllung_20_und_20_Linie" style:display-name="Objekt ohne Füllung und Linie" style:family="paragraph" style:parent-style-name="Standard" style:default-outline-level=""/>
		<style:style style:name="Textkörper_20_Blocksatz" style:display-name="Textkörper Blocksatz" style:family="paragraph" style:parent-style-name="Standard" style:default-outline-level="">
			<style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
		</style:style>
		<style:style style:name="Titel1" style:family="paragraph" style:parent-style-name="Standard" style:default-outline-level="">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties fo:font-size="24pt" style:font-size-asian="24pt"/>
		</style:style>
		<style:style style:name="Titel2" style:family="paragraph" style:parent-style-name="Standard" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0.199cm" fo:margin-top="0.101cm" fo:margin-bottom="0.101cm" style:contextual-spacing="false" fo:text-align="center" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:font-size="36pt" style:font-size-asian="36pt"/>
		</style:style>
		<style:style style:name="Überschrift1" style:family="paragraph" style:parent-style-name="Standard" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0.42cm" fo:margin-bottom="0.21cm" style:contextual-spacing="false"/>
			<style:text-properties fo:font-size="18pt" fo:font-weight="bold" style:font-size-asian="18pt" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="Überschrift2" style:family="paragraph" style:parent-style-name="Standard" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0.42cm" fo:margin-bottom="0.21cm" style:contextual-spacing="false"/>
			<style:text-properties fo:font-size="14pt" fo:font-style="italic" fo:font-weight="bold" style:font-size-asian="14pt" style:font-style-asian="italic" style:font-weight-asian="bold"/>
		</style:style>
		<style:style style:name="Maßlinie" style:family="paragraph" style:parent-style-name="Standard" style:default-outline-level="">
			<style:text-properties fo:font-size="12pt" style:font-size-asian="12pt"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Gliederung_20_1" style:display-name="master-page3~LT~Gliederung 1" style:family="paragraph" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.499cm" style:contextual-spacing="false" fo:text-align="start" style:justify-single-word="false" fo:orphans="2" fo:widows="2" style:writing-mode="lr-tb"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="31.5pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-name-asian="Tahoma" style:font-family-asian="Tahoma" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="31.5pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-name-complex="Liberation Sans1" style:font-family-complex="&apos;Liberation Sans&apos;" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="12pt" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Gliederung_20_2" style:display-name="master-page3~LT~Gliederung 2" style:family="paragraph" style:parent-style-name="master-page3_7e_LT_7e_Gliederung_20_1" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.4cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="28pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="28pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Gliederung_20_3" style:display-name="master-page3~LT~Gliederung 3" style:family="paragraph" style:parent-style-name="master-page3_7e_LT_7e_Gliederung_20_2" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.3cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="24pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="24pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Gliederung_20_4" style:display-name="master-page3~LT~Gliederung 4" style:family="paragraph" style:parent-style-name="master-page3_7e_LT_7e_Gliederung_20_3" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.199cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Gliederung_20_5" style:display-name="master-page3~LT~Gliederung 5" style:family="paragraph" style:parent-style-name="master-page3_7e_LT_7e_Gliederung_20_4" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.101cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Gliederung_20_6" style:display-name="master-page3~LT~Gliederung 6" style:family="paragraph" style:parent-style-name="master-page3_7e_LT_7e_Gliederung_20_5" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.101cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Gliederung_20_7" style:display-name="master-page3~LT~Gliederung 7" style:family="paragraph" style:parent-style-name="master-page3_7e_LT_7e_Gliederung_20_6" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.101cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Gliederung_20_8" style:display-name="master-page3~LT~Gliederung 8" style:family="paragraph" style:parent-style-name="master-page3_7e_LT_7e_Gliederung_20_7" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.101cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Gliederung_20_9" style:display-name="master-page3~LT~Gliederung 9" style:family="paragraph" style:parent-style-name="master-page3_7e_LT_7e_Gliederung_20_8" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.101cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Titel" style:display-name="master-page3~LT~Titel" style:family="paragraph" style:default-outline-level="">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false" fo:orphans="2" fo:widows="2" style:writing-mode="lr-tb"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="44pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-name-asian="Tahoma" style:font-family-asian="Tahoma" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="44pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-name-complex="Liberation Sans1" style:font-family-complex="&apos;Liberation Sans&apos;" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="12pt" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Untertitel" style:display-name="master-page3~LT~Untertitel" style:family="paragraph" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="center" style:justify-single-word="false" fo:orphans="2" fo:widows="2" fo:text-indent="0cm" style:auto-text-indent="false" style:writing-mode="lr-tb"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="32pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-name-asian="Tahoma" style:font-family-asian="Tahoma" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="32pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-name-complex="Liberation Sans1" style:font-family-complex="&apos;Liberation Sans&apos;" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="12pt" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Notizen" style:display-name="master-page3~LT~Notizen" style:family="paragraph" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0.6cm" fo:margin-right="0cm" fo:text-align="start" style:justify-single-word="false" fo:orphans="2" fo:widows="2" fo:text-indent="-0.6cm" style:auto-text-indent="false" style:writing-mode="lr-tb"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-name-asian="Tahoma" style:font-family-asian="Tahoma" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-name-complex="Liberation Sans1" style:font-family-complex="&apos;Liberation Sans&apos;" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="12pt" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Hintergrundobjekte" style:display-name="master-page3~LT~Hintergrundobjekte" style:family="paragraph" style:default-outline-level="">
			<style:paragraph-properties fo:text-align="start" style:justify-single-word="false" fo:orphans="2" fo:widows="2" style:writing-mode="lr-tb"/>
			<style:text-properties style:font-name="Liberation Serif" fo:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable" fo:font-size="12pt" style:letter-kerning="true" style:font-name-asian="Tahoma" style:font-family-asian="Tahoma" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="12pt" style:font-name-complex="Liberation Sans1" style:font-family-complex="&apos;Liberation Sans&apos;" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="master-page3_7e_LT_7e_Hintergrund" style:display-name="master-page3~LT~Hintergrund" style:family="paragraph" style:default-outline-level="">
			<style:paragraph-properties fo:text-align="start" style:justify-single-word="false" fo:orphans="2" fo:widows="2" style:writing-mode="lr-tb"/>
			<style:text-properties style:font-name="Liberation Serif" fo:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable" fo:font-size="12pt" style:letter-kerning="true" style:font-name-asian="Tahoma" style:font-family-asian="Tahoma" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="12pt" style:font-name-complex="Liberation Sans1" style:font-family-complex="&apos;Liberation Sans&apos;" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="default" style:family="paragraph" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-align="start" style:justify-single-word="false" fo:orphans="2" fo:widows="2" fo:text-indent="0cm" style:auto-text-indent="false" style:writing-mode="lr-tb"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-name-asian="Tahoma" style:font-family-asian="Tahoma" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="18pt" style:font-name-complex="Liberation Sans1" style:font-family-complex="&apos;Liberation Sans&apos;" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="gray1" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="gray2" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="gray3" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="bw1" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="bw2" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="bw3" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="orange1" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="orange2" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="orange3" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="turquoise1" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="turquoise2" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="turquoise3" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="blue1" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="blue2" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="blue3" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="sun1" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="sun2" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="sun3" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="earth1" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="earth2" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="earth3" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="green1" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="green2" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="green3" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="seetang1" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="seetang2" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="seetang3" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="lightblue1" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="lightblue2" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="lightblue3" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="yellow1" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="yellow2" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="yellow3" style:family="paragraph" style:parent-style-name="default" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:contextual-spacing="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:color="#ffffff" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="18pt" style:letter-kerning="true" style:font-size-asian="18pt"/>
		</style:style>
		<style:style style:name="Hintergrundobjekte" style:family="paragraph" style:default-outline-level="">
			<style:paragraph-properties fo:text-align="start" style:justify-single-word="false" fo:orphans="2" fo:widows="2" style:writing-mode="lr-tb"/>
			<style:text-properties style:font-name="Liberation Serif" fo:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable" fo:font-size="12pt" style:letter-kerning="true" style:font-name-asian="Tahoma" style:font-family-asian="Tahoma" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="12pt" style:font-name-complex="Liberation Sans1" style:font-family-complex="&apos;Liberation Sans&apos;" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="Hintergrund" style:family="paragraph" style:default-outline-level="">
			<style:paragraph-properties fo:text-align="start" style:justify-single-word="false" fo:orphans="2" fo:widows="2" style:writing-mode="lr-tb"/>
			<style:text-properties style:font-name="Liberation Serif" fo:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable" fo:font-size="12pt" style:letter-kerning="true" style:font-name-asian="Tahoma" style:font-family-asian="Tahoma" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="12pt" style:font-name-complex="Liberation Sans1" style:font-family-complex="&apos;Liberation Sans&apos;" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="12pt"/>
		</style:style>
		<style:style style:name="Notizen" style:family="paragraph" style:default-outline-level="">
			<style:paragraph-properties fo:margin-left="0.6cm" fo:margin-right="0cm" fo:text-align="start" style:justify-single-word="false" fo:orphans="2" fo:widows="2" fo:text-indent="-0.6cm" style:auto-text-indent="false" style:writing-mode="lr-tb"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-name-asian="Tahoma" style:font-family-asian="Tahoma" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-name-complex="Liberation Sans1" style:font-family-complex="&apos;Liberation Sans&apos;" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="12pt" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="Gliederung_20_1" style:display-name="Gliederung 1" style:family="paragraph" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.499cm" style:contextual-spacing="false" fo:text-align="start" style:justify-single-word="false" fo:orphans="2" fo:widows="2" style:writing-mode="lr-tb"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="31.5pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-name-asian="Tahoma" style:font-family-asian="Tahoma" style:font-family-generic-asian="system" style:font-pitch-asian="variable" style:font-size-asian="31.5pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-name-complex="Liberation Sans1" style:font-family-complex="&apos;Liberation Sans&apos;" style:font-family-generic-complex="system" style:font-pitch-complex="variable" style:font-size-complex="12pt" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="Gliederung_20_2" style:display-name="Gliederung 2" style:family="paragraph" style:parent-style-name="Gliederung_20_1" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.4cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="28pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="28pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="Gliederung_20_3" style:display-name="Gliederung 3" style:family="paragraph" style:parent-style-name="Gliederung_20_2" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.3cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="24pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="24pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="Gliederung_20_4" style:display-name="Gliederung 4" style:family="paragraph" style:parent-style-name="Gliederung_20_3" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.199cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="Gliederung_20_5" style:display-name="Gliederung 5" style:family="paragraph" style:parent-style-name="Gliederung_20_4" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.101cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="Gliederung_20_6" style:display-name="Gliederung 6" style:family="paragraph" style:parent-style-name="Gliederung_20_5" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.101cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="Gliederung_20_7" style:display-name="Gliederung 7" style:family="paragraph" style:parent-style-name="Gliederung_20_6" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.101cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="Gliederung_20_8" style:display-name="Gliederung 8" style:family="paragraph" style:parent-style-name="Gliederung_20_7" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.101cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="Gliederung_20_9" style:display-name="Gliederung 9" style:family="paragraph" style:parent-style-name="Gliederung_20_8" style:default-outline-level="">
			<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.101cm" style:contextual-spacing="false"/>
			<style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:text-line-through-type="none" style:font-name="Mangal" fo:font-family="Mangal" style:font-family-generic="roman" fo:font-size="20pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:letter-kerning="true" style:font-size-asian="20pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:text-emphasize="none"/>
		</style:style>
		<style:style style:name="Table_20_Heading" style:display-name="Table Heading" style:family="paragraph" style:parent-style-name="Table_20_Contents" style:class="extra">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false" text:number-lines="false" text:line-number="0"/>
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="Frame_20_contents" style:display-name="Frame contents" style:family="paragraph" style:parent-style-name="Standard" style:class="extra"/>
		<style:style style:name="Footer" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
			<style:paragraph-properties text:number-lines="false" text:line-number="0">
				<style:tab-stops>
					<style:tab-stop style:position="8.2cm" style:type="center"/>
					<style:tab-stop style:position="16.401cm" style:type="right"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="Graphics" style:family="graphic">
			<style:graphic-properties text:anchor-type="paragraph" svg:x="0cm" svg:y="0cm" style:wrap="dynamic" style:number-wrapped-paragraphs="no-limit" style:wrap-contour="false" style:vertical-pos="top" style:vertical-rel="paragraph" style:horizontal-pos="center" style:horizontal-rel="paragraph"/>
		</style:style>
		<style:style style:name="Frame" style:family="graphic">
			<style:graphic-properties text:anchor-type="paragraph" svg:x="0cm" svg:y="0cm" fo:margin-left="0.201cm" fo:margin-right="0.201cm" fo:margin-top="0.201cm" fo:margin-bottom="0.201cm" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit" style:wrap-contour="false" style:vertical-pos="top" style:vertical-rel="paragraph-content" style:horizontal-pos="center" style:horizontal-rel="paragraph-content" fo:padding="0.15cm" fo:border="0.06pt solid #000000"/>
		</style:style>
		<text:outline-style style:name="Outline">
			<text:outline-level-style text:level="1" style:num-format="">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.762cm" fo:text-indent="-0.762cm" fo:margin-left="0.762cm"/>
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="2" style:num-format="">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.016cm" fo:text-indent="-1.016cm" fo:margin-left="1.016cm"/>
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="3" style:num-format="">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-1.27cm" fo:margin-left="1.27cm"/>
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="4" style:num-format="">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.524cm" fo:text-indent="-1.524cm" fo:margin-left="1.524cm"/>
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="5" style:num-format="">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.778cm" fo:text-indent="-1.778cm" fo:margin-left="1.778cm"/>
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="6" style:num-format="">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.032cm" fo:text-indent="-2.032cm" fo:margin-left="2.032cm"/>
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="7" style:num-format="">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.286cm" fo:text-indent="-2.286cm" fo:margin-left="2.286cm"/>
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="8" style:num-format="">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-2.54cm" fo:margin-left="2.54cm"/>
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="9" style:num-format="">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.794cm" fo:text-indent="-2.794cm" fo:margin-left="2.794cm"/>
				</style:list-level-properties>
			</text:outline-level-style>
			<text:outline-level-style text:level="10" style:num-format="">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.048cm" fo:text-indent="-3.048cm" fo:margin-left="3.048cm"/>
				</style:list-level-properties>
			</text:outline-level-style>
		</text:outline-style>
		<text:notes-configuration text:note-class="footnote" style:num-format="1" text:start-value="0" text:footnotes-position="page" text:start-numbering-at="document"/>
		<text:notes-configuration text:note-class="endnote" style:num-format="i" text:start-value="0"/>
		<text:linenumbering-configuration text:number-lines="false" text:offset="0.499cm" style:num-format="1" text:number-position="left" text:increment="5"/>
		<style:default-page-layout>
			<style:page-layout-properties style:writing-mode="lr-tb" style:layout-grid-standard-mode="true"/>
		</style:default-page-layout>
	</office:styles>
	<office:automatic-styles>
		<style:style style:name="MP1" style:family="paragraph" style:parent-style-name="Footer">
			<style:text-properties fo:color="#71787d" fo:font-size="8pt" officeooo:rsid="002ff23b" officeooo:paragraph-rsid="002ff23b" style:font-size-asian="10.5pt"/>
		</style:style>
		<style:page-layout style:name="Mpm1">
			<style:page-layout-properties fo:page-width="21.001cm" fo:page-height="29.7cm" style:num-format="1" style:print-orientation="portrait" fo:margin-top="4.6cm" fo:margin-bottom="1.199cm" fo:margin-left="2.3cm" fo:margin-right="2.3cm" fo:background-color="transparent" style:writing-mode="lr-tb" style:layout-grid-color="#c0c0c0" style:layout-grid-lines="44" style:layout-grid-base-height="0.55cm" style:layout-grid-ruby-height="0cm" style:layout-grid-mode="none" style:layout-grid-ruby-below="false" style:layout-grid-print="true" style:layout-grid-display="true" style:layout-grid-base-width="0.37cm" style:layout-grid-snap-to="true" style:layout-grid-snap-to-characters="true" style:footnote-max-height="0cm">
				<style:background-image/>
				<style:footnote-sep style:width="0.018cm" style:distance-before-sep="0.101cm" style:distance-after-sep="0.101cm" style:line-style="solid" style:adjustment="left" style:rel-width="25%" style:color="#000000"/>
			</style:page-layout-properties>
			<style:header-style/>
			<style:footer-style>
				<style:header-footer-properties fo:min-height="0cm" fo:margin-top="0.499cm"/>
			</style:footer-style>
		</style:page-layout>
	</office:automatic-styles>
	<office:master-styles>
		<style:master-page style:name="Standard" style:page-layout-name="Mpm1">
			<style:footer>
				<xsl:choose>
					<xsl:when test="signed">
						<text:p text:style-name="MP1">Fachhochschule Technikum Wien, Höchstädtplatz 6, 1200 Wien<text:tab/>
							<text:tab/>DVR 0928381, ZVR 074476426</text:p>
					</xsl:when>
					<xsl:otherwise>
						<text:p text:style-name="MP1"> </text:p>
					</xsl:otherwise>
				</xsl:choose>
			</style:footer>
		</style:master-page>
	</office:master-styles>
</office:document-styles>

</xsl:template>
</xsl:stylesheet>