<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="studiengang">


<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
  <office:scripts/>
  <office:font-face-decls>
    <style:font-face style:name="OpenSymbol1" svg:font-family="OpenSymbol" style:font-charset="x-symbol"/>
    <style:font-face style:name="Wingdings" svg:font-family="Wingdings" style:font-pitch="variable" style:font-charset="x-symbol"/>
    <style:font-face style:name="Symbol" svg:font-family="Symbol" style:font-family-generic="roman" style:font-pitch="variable" style:font-charset="x-symbol"/>
    <style:font-face style:name="Arial1" svg:font-family="Arial, sans-serif"/>
    <style:font-face style:name="Lohit Hindi1" svg:font-family="'Lohit Hindi'"/>
    <style:font-face style:name="OpenSymbol" svg:font-family="OpenSymbol"/>
    <style:font-face style:name="Courier New" svg:font-family="'Courier New'" style:font-family-generic="modern"/>
    <style:font-face style:name="Courier New1" svg:font-family="'Courier New'" style:font-family-generic="modern" style:font-pitch="fixed"/>
    <style:font-face style:name="Liberation Serif" svg:font-family="'Liberation Serif'" style:font-family-generic="roman" style:font-pitch="variable"/>
    <style:font-face style:name="Times New Roman" svg:font-family="'Times New Roman'" style:font-family-generic="roman" style:font-pitch="variable"/>
    <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="Helvetica" svg:font-family="Helvetica" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="Liberation Sans" svg:font-family="'Liberation Sans'" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="Droid Sans" svg:font-family="'Droid Sans'" style:font-family-generic="system" style:font-pitch="variable"/>
    <style:font-face style:name="Lohit Hindi" svg:font-family="'Lohit Hindi'" style:font-family-generic="system" style:font-pitch="variable"/>
  </office:font-face-decls>
  <office:automatic-styles>
	  <style:style style:name="Lehrveranstaltung" style:family="table">
		<style:table-properties style:width="17cm" table:align="margins" style:may-break-between-rows="false" style:writing-mode="lr-tb"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A" style:family="table-column">
		<style:table-column-properties style:column-width="5.45cm"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B" style:family="table-column">
		<style:table-column-properties style:column-width="11.562cm"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A1" style:family="table-cell">
		<style:table-cell-properties fo:background-color="#00ff00" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
			<style:background-image/>
		</style:table-cell-properties>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B1" style:family="table-cell">
		<style:table-cell-properties fo:background-color="#00ff00" fo:padding="0.097cm" fo:border="0.05pt solid #000000">
			<style:background-image/>
		</style:table-cell-properties>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A2" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B2" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A3" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B3" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A4" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B4" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A5" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B5" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A6" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B6" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A7" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B7" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A8" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B8" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A9" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B9" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A10" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B10" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A11" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B11" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A12" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B12" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.A13" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B13" style:family="table-cell">
		<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
	</style:style>
    <style:style style:name="Modul" style:family="table">
       <style:table-properties style:width="17.247cm" fo:margin-left="-0.199cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:may-break-between-rows="false" style:writing-mode="lr-tb" />
    </style:style>
    <style:style style:name="Modul.A" style:family="table-column">
       <style:table-column-properties style:column-width="4.438cm" />
    </style:style>
    <style:style style:name="Modul.B" style:family="table-column">
       <style:table-column-properties style:column-width="12.809cm" />
    </style:style>
    <style:style style:name="Modul.1" style:family="table-row">
       <style:table-row-properties fo:keep-together="auto" />
    </style:style>
    <style:style style:name="Modul.A1" style:family="table-cell">
       <style:table-cell-properties style:vertical-align="middle" fo:background-color="#8fd2ff" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="0.5pt solid #00000a">
          <style:background-image />
       </style:table-cell-properties>
    </style:style>
    <style:style style:name="Modul.A2" style:family="table-cell">
       <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="0.5pt solid #00000a" />
    </style:style>
    <style:style style:name="Matrix" style:family="table">
       <style:table-properties style:width="17.247cm" fo:margin-left="-0.199cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:may-break-between-rows="false" style:writing-mode="lr-tb" />
    </style:style>
    <style:style style:name="Matrix.A" style:family="table-column">
       <style:table-column-properties style:column-width="1.3cm" />
    </style:style>
    <style:style style:name="Matrix.B" style:family="table-column">
       <style:table-column-properties style:column-width="9.5cm" />
    </style:style>
    <style:style style:name="Matrix.C" style:family="table-column">
       <style:table-column-properties style:column-width="1.75cm" />
    </style:style>
    <style:style style:name="Matrix.D" style:family="table-column">
       <style:table-column-properties style:column-width="1.3cm" />
    </style:style>
    <style:style style:name="Matrix.E" style:family="table-column">
       <style:table-column-properties style:column-width="1.9cm" />
    </style:style>
    <style:style style:name="Matrix.F" style:family="table-column">
       <style:table-column-properties style:column-width="1.55cm" />
    </style:style>
    <style:style style:name="Matrix.1" style:family="table-row">
       <style:table-row-properties fo:background-color="#8fd2ff" fo:keep-together="auto" />
    </style:style>
    <style:style style:name="Matrix.2" style:family="table-row">
       <style:table-row-properties fo:background-color="#00ff00" fo:keep-together="auto" />
    </style:style>
    <style:style style:name="Matrix.3" style:family="table-row">
       <style:table-row-properties fo:keep-together="auto" />
    </style:style>
<!--    <style:style style:name="Matrix.A1" style:family="table-cell">
       <style:table-cell-properties style:vertical-align="middle" fo:background-color="#8fd2ff" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="0.5pt solid #00000a">
          <style:background-image />
       </style:table-cell-properties>
    </style:style> -->
    <style:style style:name="Matrix.A1" style:family="table-cell">
       <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="0.5pt solid #00000a" />
    </style:style>
    <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Footer">
      <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0.635cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
    </style:style>
    <style:style style:name="P_LV" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:language-asian="zxx" style:country-asian="none"/>
    </style:style>
    <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties officeooo:rsid="001dc677" officeooo:paragraph-rsid="001dc677"/>
    </style:style>
    <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:break-before="page"/>
    </style:style>
    <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Deckblatt_20_Titel_20_2">
      <style:text-properties officeooo:rsid="001dc677" officeooo:paragraph-rsid="001dc677"/>
    </style:style>
    <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L1"/>
    <style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L1">
      <style:text-properties officeooo:rsid="001dc677" officeooo:paragraph-rsid="001dc677"/>
    </style:style>
    <style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L2">
      <style:text-properties officeooo:rsid="001dc677" officeooo:paragraph-rsid="001dc677"/>
    </style:style>
    <style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties officeooo:rsid="001dc677" officeooo:paragraph-rsid="001dc677"/>
    </style:style>
    <style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L2"/>
    <style:style style:name="P11" style:family="paragraph" style:parent-style-name="Heading_20_1">
      <style:text-properties officeooo:rsid="001dc677" officeooo:paragraph-rsid="001dc677"/>
    </style:style>
    <style:style style:name="P12" style:family="paragraph" style:parent-style-name="Deckblatt_20_Titel_20_1" style:master-page-name="First_20_Page">
      <style:paragraph-properties style:page-number="auto"/>
      <style:text-properties officeooo:rsid="001dc677"/>
    </style:style>
    <style:style style:name="P13" style:family="paragraph" style:parent-style-name="Deckblatt_20_Titel_20_2">
      <style:text-properties officeooo:rsid="001dc677"/>
    </style:style>
    <style:style style:name="P14" style:family="paragraph" style:parent-style-name="Contents_20_Heading" style:master-page-name="Convert_20_1">
      <style:paragraph-properties style:page-number="auto"/>
    </style:style>
    <style:style style:name="P15" style:family="paragraph" style:parent-style-name="Contents_20_1">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="17cm" style:type="right" style:leader-style="dotted" style:leader-text="."/>
        </style:tab-stops>
      </style:paragraph-properties>
    </style:style>
    <style:style style:name="P16" style:family="paragraph" style:parent-style-name="Contents_20_2">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="17cm" style:type="right" style:leader-style="dotted" style:leader-text="."/>
        </style:tab-stops>
      </style:paragraph-properties>
    </style:style>
    <style:style style:name="P17" style:family="paragraph" style:parent-style-name="Contents_20_3">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="17cm" style:type="right" style:leader-style="dotted" style:leader-text="."/>
        </style:tab-stops>
      </style:paragraph-properties>
    </style:style>
	<style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
	  <style:text-properties fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"/>
	</style:style>
	<style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
	  <style:text-properties fo:font-style="bold" style:font-style-asian="bold" style:font-style-complex="bold"/>
	</style:style>
    <style:style style:name="P20" style:family="paragraph" style:parent-style-name="Heading_20_1" style:list-style-name="">
       <style:paragraph-properties fo:line-height="110%" fo:orphans="2" fo:widows="2">
          <style:tab-stops />
       </style:paragraph-properties>
    </style:style>
    <style:style style:name="T1" style:family="text">
      <style:text-properties officeooo:rsid="001dc677"/>
    </style:style>
    <style:style style:name="T2" style:family="text">
      <style:text-properties officeooo:rsid="001f0000"/>
    </style:style>
    <style:style style:name="T3" style:family="text"/>
    <style:style style:name="fr1" style:family="graphic" style:parent-style-name="Frame">
      <style:graphic-properties style:wrap="dynamic" style:number-wrapped-paragraphs="1" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="right" style:horizontal-rel="page-content" fo:background-color="#ffffff" style:background-transparency="100%" style:writing-mode="lr-tb" draw:wrap-influence-on-position="once-successive">
        <style:background-image/>
      </style:graphic-properties>
    </style:style>
    <style:style style:name="fr2" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties fo:margin-left="0.319cm" fo:margin-right="0.319cm" style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:padding="0.026cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
    </style:style>
    <style:style style:name="Sect1" style:family="section">
      <style:section-properties style:editable="false">
        <style:columns fo:column-count="1" fo:column-gap="0cm"/>
      </style:section-properties>
    </style:style>
    <text:list-style style:name="L1">
      <text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-0.635cm" fo:margin-left="1.27cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
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
    <text:list-style style:name="L2">
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
    <office:text text:use-soft-page-breaks="true">
      <office:forms form:automatic-focus="false" form:apply-design-mode="false"/>
      <text:sequence-decls>
        <text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Table"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Text"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
      </text:sequence-decls>
      <text:p text:style-name="P12">Studienordnung</text:p>
      <text:p text:style-name="P13">WS2014</text:p>
      <text:p text:style-name="P5"><xsl:value-of select="studiengang_bezeichnung"/> (KZ: <xsl:value-of select="studiengang_kz"/>)</text:p>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Deckblatt_20_Titel_20_3">FHTW - <text:span text:style-name="T1"><xsl:value-of select="studiengang_kurzbzlang"/></text:span></text:p>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Anmerkungen_20_-_20_bitte_20_löschen_21_"/>
      <text:p text:style-name="Standard"/>
      <text:table-of-content text:style-name="Sect1" text:protected="true" text:name="Inhaltsverzeichnis1">
        <text:table-of-content-source text:outline-level="10">
          <text:index-title-template text:style-name="Contents_20_Heading">Inhaltsverzeichnis</text:index-title-template>
          <text:table-of-content-entry-template text:outline-level="1" text:style-name="Contents_20_1">
            <text:index-entry-link-start text:style-name="Index_20_Link"/>
            <text:index-entry-chapter/>
            <text:index-entry-text/>
            <text:index-entry-tab-stop style:type="right" style:leader-char="."/>
            <text:index-entry-page-number/>
            <text:index-entry-link-end/>
          </text:table-of-content-entry-template>
          <text:table-of-content-entry-template text:outline-level="2" text:style-name="Contents_20_2">
            <text:index-entry-link-start text:style-name="Index_20_Link"/>
            <text:index-entry-chapter/>
            <text:index-entry-text/>
            <text:index-entry-tab-stop style:type="right" style:leader-char="."/>
            <text:index-entry-page-number/>
            <text:index-entry-link-end/>
          </text:table-of-content-entry-template>
          <text:table-of-content-entry-template text:outline-level="3" text:style-name="Contents_20_3">
            <text:index-entry-link-start text:style-name="Index_20_Link"/>
            <text:index-entry-chapter/>
            <text:index-entry-text/>
            <text:index-entry-tab-stop style:type="right" style:leader-char="."/>
            <text:index-entry-page-number/>
            <text:index-entry-link-end/>
          </text:table-of-content-entry-template>
          <text:table-of-content-entry-template text:outline-level="4" text:style-name="Contents_20_4">
            <text:index-entry-link-start text:style-name="Index_20_Link"/>
            <text:index-entry-chapter/>
            <text:index-entry-text/>
            <text:index-entry-tab-stop style:type="right" style:leader-char="."/>
            <text:index-entry-page-number/>
            <text:index-entry-link-end/>
          </text:table-of-content-entry-template>
          <text:table-of-content-entry-template text:outline-level="5" text:style-name="Contents_20_5">
            <text:index-entry-link-start text:style-name="Index_20_Link"/>
            <text:index-entry-chapter/>
            <text:index-entry-text/>
            <text:index-entry-tab-stop style:type="right" style:leader-char="."/>
            <text:index-entry-page-number/>
            <text:index-entry-link-end/>
          </text:table-of-content-entry-template>
          <text:table-of-content-entry-template text:outline-level="6" text:style-name="Contents_20_6">
            <text:index-entry-link-start text:style-name="Index_20_Link"/>
            <text:index-entry-chapter/>
            <text:index-entry-text/>
            <text:index-entry-tab-stop style:type="right" style:leader-char="."/>
            <text:index-entry-page-number/>
            <text:index-entry-link-end/>
          </text:table-of-content-entry-template>
          <text:table-of-content-entry-template text:outline-level="7" text:style-name="Contents_20_7">
            <text:index-entry-link-start text:style-name="Index_20_Link"/>
            <text:index-entry-chapter/>
            <text:index-entry-text/>
            <text:index-entry-tab-stop style:type="right" style:leader-char="."/>
            <text:index-entry-page-number/>
            <text:index-entry-link-end/>
          </text:table-of-content-entry-template>
          <text:table-of-content-entry-template text:outline-level="8" text:style-name="Contents_20_8">
            <text:index-entry-link-start text:style-name="Index_20_Link"/>
            <text:index-entry-chapter/>
            <text:index-entry-text/>
            <text:index-entry-tab-stop style:type="right" style:leader-char="."/>
            <text:index-entry-page-number/>
            <text:index-entry-link-end/>
          </text:table-of-content-entry-template>
          <text:table-of-content-entry-template text:outline-level="9" text:style-name="Contents_20_9">
            <text:index-entry-link-start text:style-name="Index_20_Link"/>
            <text:index-entry-chapter/>
            <text:index-entry-text/>
            <text:index-entry-tab-stop style:type="right" style:leader-char="."/>
            <text:index-entry-page-number/>
            <text:index-entry-link-end/>
          </text:table-of-content-entry-template>
          <text:table-of-content-entry-template text:outline-level="10" text:style-name="Contents_20_10">
            <text:index-entry-link-start text:style-name="Index_20_Link"/>
            <text:index-entry-chapter/>
            <text:index-entry-text/>
            <text:index-entry-tab-stop style:type="right" style:leader-char="."/>
            <text:index-entry-page-number/>
            <text:index-entry-link-end/>
          </text:table-of-content-entry-template>
        </text:table-of-content-source>
        <text:index-body>
          <text:index-title text:style-name="Sect1" text:name="Inhaltsverzeichnis1_Head">
            <text:p text:style-name="P14">Inhaltsverzeichnis</text:p>
          </text:index-title>
          <text:p text:style-name="P15">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__359_971256360" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>1 Eckdaten<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P15">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15777_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>2 Berufliche Tätigkeitsfelder<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P15">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15779_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>3 Qualifikationsziele (Lernergebnisse des Studienganges)<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P15">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15781_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>4 Studienplan<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P16">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__339_971256360" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>4.1 Organisationsform<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P17">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__361_971256360" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>4.1.1 Studienplanmatrix<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P17">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15783_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>4.1.2 Modulgrafik<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P17">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15783_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>4.1.3 Modulbeschreibungen<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P15">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15785_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>5 Zugangsvoraussetzungen<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P15">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15787_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>6 Aufnahmeverfahren<text:tab/>3</text:a>
          </text:p>
        </text:index-body>
      </text:table-of-content>
      <text:list xml:id="list1907704423577569659" text:style-name="Numbering_20_1">
        <text:list-header>
          <text:h text:style-name="Heading_20_1" text:outline-level="1"/>
        </text:list-header>
      </text:list>
      <text:p text:style-name="P4"/>
      <text:list xml:id="list233821455398143" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:h text:style-name="P11" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__359_971256360"/>Eckdaten<text:bookmark-end text:name="__RefHeading__359_971256360"/></text:h>
        </text:list-item>
      </text:list>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="P3">Studiengangskennzahl: <xsl:value-of select="studiengang_kz"/></text:p>
      <text:p text:style-name="P3">Studiengangsart: <xsl:value-of select="studiengang_art"/></text:p>
      <text:p text:style-name="P3">Studiengangs Kürzel: <xsl:value-of select="studiengang_kurzbz"/></text:p>
      <text:p text:style-name="P3">Akademischer Grad: <xsl:value-of select="studiengang_art"/> of Science in Engineering (<xsl:value-of select="titel_kurzbz"/>)</text:p>
      <text:p text:style-name="P3">Standort: Wien</text:p>
      <text:p text:style-name="P7">Studiengangsleitung: <xsl:value-of select="studiengangsleitung"/></text:p>
      <text:p text:style-name="P7">Studiengangsbezeichnung: <xsl:value-of select="studiengang_bezeichnung"/></text:p>
      <text:p text:style-name="P7">Studiengangsbezeichnung (engl.): <xsl:value-of select="bezeichnung_englisch"/></text:p>
      <text:p text:style-name="P7">Kurzbezeichnung: <xsl:value-of select="studiengang_kurzbzlang"/></text:p>
      <text:p text:style-name="P3"/>
      <text:list xml:id="list233821599912460" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:h text:style-name="Heading_20_1" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__15777_1435587267"/>Berufliche Tätigkeitsfelder<text:bookmark-end text:name="__RefHeading__15777_1435587267"/></text:h>
		</text:list-item>
	  </text:list>
	  <text:p text:style-name="P18">An dieser Stelle sind die beruflichen Tätigkeitsfelder des Studiengangs aufzuzeigen.</text:p>
      <text:p text:style-name="Standard"/>
	  <text:list xml:id="list140455441160016" text:continue-numbering="true" text:style-name="Numbering_20_1">
		<text:list-item>
          <text:h text:style-name="Heading_20_1" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__15779_1435587267"/>Qualifikationsziele (Lernergebnisse des Studienganges)<text:bookmark-end text:name="__RefHeading__15779_1435587267"/></text:h>
        </text:list-item>
	  </text:list>
      <text:p text:style-name="Standard"/>
	  <text:list xml:id="list105303509167461" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:h text:style-name="Heading_20_1" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__15781_1435587267"/>Studienplan<text:bookmark-end text:name="__RefHeading__15781_1435587267"/></text:h>
        </text:list-item>
      </text:list>
	  
	  <xsl:apply-templates select="orgform"/>

	

      <text:list xml:id="list233823232037712" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:h text:style-name="Heading_20_1" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__15785_1435587267"/>Zugangsvoraussetzungen<text:bookmark-end text:name="__RefHeading__15785_1435587267"/></text:h>
        </text:list-item>
        <text:list-item>
          <text:h text:style-name="Heading_20_1" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__15787_1435587267"/>Aufnahmeverfahren<text:bookmark-end text:name="__RefHeading__15787_1435587267"/></text:h>
        </text:list-item>
      </text:list>
    </office:text>
  </office:body>
</office:document-content>
</xsl:template>


<!-- <xsl:template match="studienplan">
	  <text:list xml:id="list2341352636336469980" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
		  <text:list>
		    <text:list-item>
              <text:h text:style-name="Heading_20_2" text:outline-level="2"><text:bookmark-start text:name="__RefHeading__339_971256360"/>Organisationsform<text:bookmark-end text:name="__RefHeading__339_971256360"/></text:h>
			</text:list-item>
		  </text:list>
        </text:list-item>
      </text:list>
      <text:list xml:id="list8309596791993483161" text:style-name="L1">
        <text:list-item>
          <text:p text:style-name="P7">Regelstudiendauer: <xsl:value-of select="regelstudiendauer"/></text:p>
        </text:list-item>
      </text:list>

<xsl:apply-templates select="orgform"/>
</xsl:template> -->


<xsl:template match="orgform">
      <text:p text:style-name="Standard"/>
	  <text:list xml:id="list2341352636336469980" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
		  <text:list>
		    <text:list-item>
              <text:h text:style-name="Heading_20_2" text:outline-level="2"><text:bookmark-start text:name="__RefHeading__339_971256360"/>Organisationsform <xsl:value-of select="orgform_kurzbz_lang"/><text:bookmark-end text:name="__RefHeading__339_971256360"/></text:h>
			</text:list-item>
		  </text:list>
        </text:list-item>
      </text:list>
      <text:list xml:id="list8309596791993483161" text:style-name="L1">
        <text:list-item>
          <text:p text:style-name="P7">Regelstudiendauer: <xsl:value-of select="regelstudiendauer"/></text:p>
        </text:list-item>
      </text:list>
      <text:p text:style-name="Standard"/>
      <text:list xml:id="list233822041148163" text:continue-list="list233821599912460" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:list>
                <text:list-item>
                  <text:h text:style-name="Heading_20_3" text:outline-level="3"><text:bookmark-start text:name="__RefHeading__361_971256360"/>Studienplanmatrix<text:bookmark-end text:name="__RefHeading__361_971256360"/></text:h>
                </text:list-item>
              </text:list>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
    <text:p text:style-name="Standard"/>
	<table:table table:name="Matrix" table:style-name="Matrix">
		<table:table-column table:style-name="Matrix.A"/>
		<table:table-column table:style-name="Matrix.B"/>
		<table:table-column table:style-name="Matrix.C"/>
		<table:table-column table:style-name="Matrix.D"/>
		<table:table-column table:style-name="Matrix.E"/>
		<table:table-column table:style-name="Matrix.F"/>
		<table:table-row table:style-name="Matrix.1">
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P20">Sem</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P20">Bezeichnung Modul bzw. LV</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P20">LV-Typ</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P20">SWS</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P20">Gruppen</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P20">ECTS</text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row table:style-name="Matrix.2">
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P7"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P7">Modul xy</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P7"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P7"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P7"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style_name="P7"></text:p>
			</table:table-cell>
		</table:table-row>
		<xsl:apply-templates match="lehrveranstaltung" mode="matrix"/>
	</table:table>
    <text:p text:style-name="Standard"/>
<!--      <text:list xml:id="list5318946080435694118" text:style-name="L2">
        <text:list-item>
          <text:list>
			<text:list-item>
			  <text:p text:style-name="P10"><text:span text:style-name="T1">Organisationsform</text:span>: <xsl:value-of select="orgform_kurzbz"/></text:p>
			</text:list-item>
			<text:list-item>
			    <text:p text:style-name="P8">Studienplätze: <xsl:value-of select="studienplaetze"/></text:p>
			</text:list-item>
          </text:list>
        </text:list-item>
      </text:list> -->
		<text:list xml:id="list233823045617055" text:continue-list="list233822041148163" text:style-name="Numbering_20_1">
			<text:list-item>
			  <text:list>
				<text:list-item>
				  <text:list>
				    <text:list-item>
				      <text:h text:style-name="Heading_20_3" text:outline-level="3"><text:bookmark-start text:name="__RefHeading__15783_1435587267"/>Modulgrafik<text:bookmark-end text:name="__RefHeading__15783_1435587267"/></text:h>
					</text:list-item>
				  </text:list>
				</text:list-item>
			  </text:list>
			</text:list-item>
		  </text:list>
        <text:p text:style-name="P18">Bitte ergänzen</text:p>
        <text:p text:style-name="Standard"/>
        <text:list xml:id="list105303525162521" text:continue-numbering="true" text:style-name="Numbering_20_1">
            <text:list-item>
               <text:list>
                  <text:list-item>
                     <text:list>
                        <text:list-item>
                           <text:h text:style-name="Heading_20_3" text:outline-level="3">
                              <text:bookmark-start text:name="__RefHeading__15783_1435587267" />
                              Modulbeschreibungen
                              <text:bookmark-end text:name="__RefHeading__15783_1435587267" />
                           </text:h>
                        </text:list-item>
                     </text:list>
                  </text:list-item>
               </text:list>
            </text:list-item>
         </text:list>
         <table:table table:name="Modul" table:style-name="Modul">
            <table:table-column table:style-name="Modul.A" />
            <table:table-column table:style-name="Modul.B" />
            <table:table-row table:style-name="Modul.1">
               <table:table-cell table:style-name="Modul.A1" office:value-type="string">
                  <text:p text:style-name="P7">Modul-Bezeichnung</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A1" office:value-type="string">
                  <text:p text:style-name="P7">Modul xy</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Modul.1">
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P7">Umfang (ECTS)</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P7">5</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Modul.1">
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P7">Ausbildungssemester</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P7">1</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Modul.1">
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P7">Kompetenzbereich</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P7">Fachrichtungen, ...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Modul.1">
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P7">Pflicht-/Wahl-Modul</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P7">
                     <text:span text:style-name="T2">Pflicht-Modul?</text:span>
                     <text:span text:style-name="T3">Wahl-Modul?</text:span>
                  </text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Modul.1">
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P7">Prüfungsmodalitäten (falls Modulprüfung)</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P7">...</text:p>
               </table:table-cell>
            </table:table-row>
         </table:table>
	<xsl:apply-templates select="lehrveranstaltung" mode="beschreibung"/>
</xsl:template>

<xsl:template match="lehrveranstaltung" mode="matrix">
		<table:table-row table:style-name="Matrix.3">
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P7"><xsl:value-of select="lv_semester"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P7"><xsl:value-of select="lv_bezeichnung"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P7"><xsl:value-of select="lv_lehrform_kurzbz"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P7"><xsl:value-of select="lv_semesterstunden"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P7"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P7"><xsl:value-of select="lv_ects"/></text:p>
			</table:table-cell>
		</table:table-row>
</xsl:template>

<xsl:template match="lehrveranstaltung" mode="beschreibung">
	
    <text:p text:style-name="Standard"/>
    <table:table table:name="Lehrveranstaltung" table:style-name="Lehrveranstaltung">
		<table:table-column table:style-name="Lehrveranstaltung.A"/>
		<table:table-column table:style-name="Lehrveranstaltung.B"/>
		<text:soft-page-break/>
		<table:table-row>
			<table:table-cell table:style-name="Lehrveranstaltung.A1" office:value-type="string">
				<text:p text:style-name="P_LV">LV-Bezeichnung</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Lehrveranstaltung.B1" office:value-type="string">
				<text:p text:style-name="P_LV"><xsl:value-of select="lv_bezeichnung"/></text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
				<text:p text:style-name="P-LV">ECTS</text:p>
			</table:table-cell>
				<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
					<text:p text:style-name="P_LV"><xsl:value-of select="lv_ects"/></text:p>
				</table:table-cell>
			</table:table-row>
			<table:table-row>
				<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
					<text:p text:style-name="P_LV">Ausbildungssemester</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
					<text:p text:style-name="P_LV"><xsl:value-of select="lv_semester"/></text:p>
				</table:table-cell>
			</table:table-row>
			<table:table-row>
				<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
					<text:p text:style-name="P_LV">Lehrform</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
					<text:p text:style-name="P_LV"><xsl:value-of select="lv_lehrform_kurzbz"/></text:p>
				</table:table-cell>
			</table:table-row>
			<table:table-row>
				<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
					<text:p text:style-name="P_LV">Semesterstunden</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
					<text:p text:style-name="P_LV"><xsl:value-of select="lv_semesterstunden"/></text:p>
				</table:table-cell>
			</table:table-row>
			<table:table-row>
				<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
					<text:p text:style-name="P_LV">LV-Kürzel</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
					<text:p text:style-name="P_LV"><xsl:value-of select="lv_kurzbz"/></text:p>
				</table:table-cell>
			</table:table-row>
			<!-- **************** LV-Info ************** -->
			<xsl:if test="lvinfo_sprache">
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P_LV">Methodik / Didaktik</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P_LV"><xsl:value-of select="lvinfo_methodik"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P_LV">Lehrinhalte</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P_LV"><xsl:value-of select="lvinfo_lehrinhalte"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P_LV">Prüfungsmodalitäten</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P_LV"><xsl:value-of select="lvinfo_pruefungsordnung"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P_LV">Literatur</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P_LV"><xsl:value-of select="lvinfo_unterlagen"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P_LV">Lehrziele</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P21"><xsl:value-of select="lvinfo_lehrziele"/></text:p>
						<text:p text:style-name="P_LV"><xsl:value-of select="lvinfo_kurzbeschreibung"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P18">LV-Voraussetzungen</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P18"><xsl:value-of select="lvinfo_voraussetzungen"/></text:p>
					</table:table-cell>
				</table:table-row>
			</xsl:if>
			
			<table:table-row>
				<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
					<text:p text:style-name="P_LV">Anmerkung</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
					<text:p text:style-name="P_LV"><xsl:value-of select="lv_anmerkung"/></text:p>
					<xsl:if test="lvinfo_sprache"><text:p text:style-name="P_LV"><xsl:value-of select="lvinfo_anmerkungen"/></text:p></xsl:if>
				</table:table-cell>
			</table:table-row>
		</table:table>

<!--<xsl:apply-templates select="lvinfo_sprache"/>-->
</xsl:template>


<xsl:template match="lvinfo_sprache">

<text:list xml:id="list5318946080435694129" text:style-name="L2">
        <text:list-item>
          <text:p text:style-name="P10"><text:span text:style-name="T1">LV-Lehrziele</text:span>: <xsl:value-of select="lvinfo_lehrziele"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P8">LV-Methodik: <xsl:value-of select="lvinfo_methodik"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P8">LV-Lehrinhalte: <xsl:value-of select="lvinfo_lehrinhalte"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P8">LV-Voraussetzungen: <xsl:value-of select="lvinfo_voraussetzungen"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P8">LV-Unterlagen: <xsl:value-of select="lvinfo_unterlagen"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P8">LV-Prüfungsordnung: <xsl:value-of select="lvinfo_pruefungsordnung"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P8">LV-Kurzbeschreibung: <xsl:value-of select="lvinfo_kurzbeschreibung"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P8">LV-Anmerkungen: <xsl:value-of select="lvinfo_anmerkungen"/></text:p>
        </text:list-item>

      </text:list>
</xsl:template>


</xsl:stylesheet>