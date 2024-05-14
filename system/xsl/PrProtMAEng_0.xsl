<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
>
  <xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="abschlusspruefung">
<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
  <office:scripts/>
  <office:font-face-decls>
    <style:font-face style:name="Lohit Hindi1" svg:font-family="'Lohit Hindi'"/>
    <style:font-face style:name="Times New Roman" svg:font-family="'Times New Roman'" style:font-family-generic="roman" style:font-pitch="variable"/>
    <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="Droid Sans" svg:font-family="'Droid Sans'" style:font-family-generic="system" style:font-pitch="variable"/>
    <style:font-face style:name="Lohit Hindi" svg:font-family="'Lohit Hindi'" style:font-family-generic="system" style:font-pitch="variable"/>
  </office:font-face-decls>
  <office:automatic-styles>
    <style:style style:name="Tabelle1" style:family="table">
      <style:table-properties style:width="18.232cm" fo:margin-left="-0.199cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Tabelle1.A" style:family="table-column">
      <style:table-column-properties style:column-width="2.896cm"/>
    </style:style>
    <style:style style:name="Tabelle1.B" style:family="table-column">
      <style:table-column-properties style:column-width="2.492cm"/>
    </style:style>
    <style:style style:name="Tabelle1.C" style:family="table-column">
      <style:table-column-properties style:column-width="0.72cm"/>
    </style:style>
    <style:style style:name="Tabelle1.D" style:family="table-column">
      <style:table-column-properties style:column-width="1.182cm"/>
    </style:style>
    <style:style style:name="Tabelle1.E" style:family="table-column">
      <style:table-column-properties style:column-width="1.817cm"/>
    </style:style>
    <style:style style:name="Tabelle1.H" style:family="table-column">
      <style:table-column-properties style:column-width="0.794cm"/>
    </style:style>
    <style:style style:name="Tabelle1.I" style:family="table-column">
      <style:table-column-properties style:column-width="2.538cm"/>
    </style:style>
    <style:style style:name="Tabelle1.1" style:family="table-row">
      <style:table-row-properties style:min-row-height="0.5cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Tabelle1.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:background-color="#d9d9d9" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Tabelle1.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:background-color="#d9d9d9" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Tabelle1.D2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:background-color="#ffffff" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Tabelle1.B5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:background-color="#ffffff" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Tabelle1.14" style:family="table-row">
      <style:table-row-properties style:min-row-height="1.475cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Tabelle1.A14" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Tabelle1.15" style:family="table-row">
      <style:table-row-properties style:min-row-height="2.141cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Tabelle1.16" style:family="table-row">
      <style:table-row-properties style:min-row-height="0.457cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Tabelle1.19" style:family="table-row">
      <style:table-row-properties style:min-row-height="3.119cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Footer">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:language="de" fo:country="AT"/>
    </style:style>
    <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%"/>
      <style:text-properties style:font-name="Arial" fo:language="de" fo:country="AT"/>
    </style:style>
    <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%">
        <style:tab-stops>
          <style:tab-stop style:position="0.501cm"/>
          <style:tab-stop style:position="0.549cm"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%" style:snap-to-layout-grid="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%">
        <style:tab-stops>
          <style:tab-stop style:position="7.502cm"/>
          <style:tab-stop style:position="10.001cm"/>
          <style:tab-stop style:position="12.502cm"/>
          <style:tab-stop style:position="15.002cm"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%" style:snap-to-layout-grid="false">
        <style:tab-stops>
          <style:tab-stop style:position="7.502cm"/>
          <style:tab-stop style:position="10.001cm"/>
          <style:tab-stop style:position="12.502cm"/>
          <style:tab-stop style:position="15.002cm"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="6.502cm"/>
          <style:tab-stop style:position="12.002cm"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="7.001cm"/>
          <style:tab-stop style:position="12.502cm"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%">
        <style:tab-stops>
          <style:tab-stop style:position="6.502cm"/>
          <style:tab-stop style:position="12.002cm"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" fo:font-style="italic" style:font-size-asian="10pt" style:font-style-asian="italic" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%" style:snap-to-layout-grid="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:language="de" fo:country="AT" fo:font-style="italic" style:font-size-asian="10pt" style:font-style-asian="italic" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%" style:snap-to-layout-grid="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-style="italic" style:font-size-asian="10pt" style:font-style-asian="italic" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-style="italic" style:font-size-asian="10pt" style:font-style-asian="italic" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="11pt" fo:language="de" fo:country="AT" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
    </style:style>
    <style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Arial" fo:font-size="11pt" fo:language="de" fo:country="AT" fo:font-weight="bold" style:font-size-asian="11pt" style:font-weight-asian="bold" style:font-size-complex="11pt"/>
    </style:style>
    <style:style style:name="P20" style:family="paragraph" style:parent-style-name="Heading_20_2" style:master-page-name="Standard">
      <style:paragraph-properties style:page-number="1"/>
      <style:text-properties style:font-name="Arial" fo:language="de" fo:country="AT"/>
    </style:style>
    <style:style style:name="P21" style:family="paragraph">
      <style:paragraph-properties style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="P22" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%"/>
      <style:text-properties style:font-name="Arial" fo:font-size="3pt" fo:language="de" fo:country="AT" style:font-size-asian="3pt" style:font-name-complex="Arial" style:font-size-complex="3pt"/>
    </style:style>
    <style:style style:name="P23" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:break-before="page" />
    </style:style>
    <style:style style:name="P24" style:family="paragraph" style:parent-style-name="Heading_20_2" style:master-page-name="Standard">
		<style:paragraph-properties style:page-number="1"/>
		<style:text-properties style:font-name="Arial" fo:language="de" fo:country="AT"/>
	</style:style>
	<style:style style:name="P25" style:family="paragraph" style:parent-style-name="Heading_20_2">
		<style:text-properties style:font-name="Arial" fo:language="de" fo:country="AT"/>
	</style:style>
    <style:style style:name="T1" style:family="text">
      <style:text-properties fo:language="de" fo:country="AT" style:font-name-complex="Arial"/>
    </style:style>
    <style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties fo:margin-left="0.319cm" fo:margin-right="0.319cm" style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="page" style:horizontal-pos="from-left" style:horizontal-rel="page" fo:padding="0.002cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
    </style:style>
    <style:style style:name="gr1" style:family="graphic">
      <style:graphic-properties draw:stroke="solid" svg:stroke-width="0.026cm" svg:stroke-color="#000000" draw:stroke-linejoin="miter" draw:fill="solid" draw:fill-color="#ffffff" draw:textarea-horizontal-align="left" draw:textarea-vertical-align="top" draw:auto-grow-height="false" fo:padding-top="0.229cm" fo:padding-bottom="0.229cm" fo:padding-left="0.441cm" fo:padding-right="0.441cm" fo:wrap-option="wrap" fo:margin-left="0.319cm" fo:margin-right="0.319cm" style:run-through="foreground" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph"/>
    </style:style>
  </office:automatic-styles>
  <office:body>
    <office:text text:use-soft-page-breaks="true">
       <xsl:apply-templates select="pruefung" />
	</office:text>
  </office:body>
</office:document-content>
</xsl:template>

<xsl:template match="pruefung">
      <text:sequence-decls>
        <text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Table"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Text"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
      </text:sequence-decls>
      <text:p text:style-name="P24"/>
		<text:p text:style-name="P25">Record of Master Examination before a Committee</text:p>
      <text:p text:style-name="P18">held in the UAS Master’s Degree Program <xsl:value-of select="stg_bezeichnung_engl" />, Classification Number <xsl:value-of select="studiengang_kz" /></text:p>
      <text:p text:style-name="P2"/>
      <text:p text:style-name="P19"><xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" /></text:p>
      <text:p text:style-name="P18">Personal Identity Number: <xsl:value-of select="matrikelnr" /></text:p>
      <text:p text:style-name="P2"/>
      
      <table:table table:name="Tabelle1" table:style-name="Tabelle1">
        <table:table-column table:style-name="Tabelle1.A"/>
        <table:table-column table:style-name="Tabelle1.B"/>
        <table:table-column table:style-name="Tabelle1.C"/>
        <table:table-column table:style-name="Tabelle1.D"/>
        <table:table-column table:style-name="Tabelle1.E"/>
        <table:table-column table:style-name="Tabelle1.A" table:number-columns-repeated="2"/>
        <table:table-column table:style-name="Tabelle1.H"/>
        <table:table-column table:style-name="Tabelle1.I"/>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="9" office:value-type="string">
            <text:p text:style-name="P4">Examining Committee</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P4">Chair</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle1.D2" table:number-columns-spanned="6" office:value-type="string">
            <text:p text:style-name="P4"><xsl:value-of select="vorsitz_nachname" /></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P4">1st Examiner</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle1.D2" table:number-columns-spanned="6" office:value-type="string">
            <text:p text:style-name="P4"><xsl:value-of select="pruefer1_nachname" /></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P5">2nd Examiner</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle1.D2" table:number-columns-spanned="6" office:value-type="string">
            <text:p text:style-name="P4"><xsl:value-of select="pruefer2_nachname" /></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
            <text:p text:style-name="P4">Exam Date</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle1.B5" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P4"><xsl:value-of select="datum" /></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P4">Time of Start</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle1.B5" office:value-type="string">
            <text:p text:style-name="P6"/>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
            <text:p text:style-name="P4">Time of Finish</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle1.D2" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P6">
              <text:bookmark text:name="_GoBack"/>
            </text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P4">Examination Attempt</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle1.D2" table:number-columns-spanned="6" office:value-type="string">
            <text:p text:style-name="P12">            
            1st Attempt 
            <draw:custom-shape text:anchor-type="char" draw:z-index="0" draw:name="Rechteck 1" draw:style-name="gr1" draw:text-style-name="P21" svg:width="0.336cm" svg:height="0.359cm" svg:x="1.9cm" svg:y="0.05cm"><text:p/>
            	<draw:enhanced-geometry svg:viewBox="0 0 21600 21600" draw:type="rectangle" draw:enhanced-path="M 0 0 L 21600 0 21600 21600 0 21600 0 0 Z N"/>
            </draw:custom-shape>
            <text:s text:c="6"/>/ 1st Retake 
            <draw:custom-shape text:anchor-type="char" draw:z-index="2" draw:name="Rechteck 1" draw:style-name="gr1" draw:text-style-name="P21" svg:width="0.336cm" svg:height="0.359cm" svg:x="4.46cm" svg:y="0.05cm"><text:p/>
	          <draw:enhanced-geometry svg:viewBox="0 0 21600 21600" draw:type="rectangle" draw:enhanced-path="M 0 0 L 21600 0 21600 21600 0 21600 0 0 Z N"/>
	        </draw:custom-shape>
       		<text:s text:c="7"/>/ <text:s/>2nd Retake
	        <draw:custom-shape text:anchor-type="char" draw:z-index="3" draw:name="Rechteck 1" draw:style-name="gr1" draw:text-style-name="P21" svg:width="0.336cm" svg:height="0.359cm" svg:x="7.35cm" svg:y="0.05cm"><text:p/>
	          <draw:enhanced-geometry svg:viewBox="0 0 21600 21600" draw:type="rectangle" draw:enhanced-path="M 0 0 L 21600 0 21600 21600 0 21600 0 0 Z N"/>
	        </draw:custom-shape>
			</text:p>        
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P4">Topic and Assessment of Master's Thesis</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle1.B5" table:number-columns-spanned="6" office:value-type="string">
            <text:p text:style-name="P4"><xsl:value-of select="themenbereich" /></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle1.D2" office:value-type="string">
            <text:p text:style-name="P4">Grade (Information): </text:p>
            <text:p text:style-name="P4"><xsl:value-of select="note" /></text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P4">Subject of the Examination</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="7" office:value-type="string">
            <text:p text:style-name="P4">Examination interview on the Master’s Thesis and its links to subjects of the curriculum as well as examination interview on a curricular theme</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P4">Parts of the examination held in English (Optional - in line with the degree program’s guidelines):</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle1.D2" table:number-columns-spanned="7" office:value-type="string">
            <text:p text:style-name="P14"><draw:custom-shape text:anchor-type="char" draw:z-index="6" draw:name="Rechteck 1" draw:style-name="gr1" draw:text-style-name="P21" svg:width="0.336cm" svg:height="0.359cm" svg:x="10.964cm" svg:y="0.153cm"><text:p/><draw:enhanced-geometry svg:viewBox="0 0 21600 21600" draw:type="rectangle" draw:enhanced-path="M 0 0 L 21600 0 21600 21600 0 21600 0 0 Z N"/></draw:custom-shape>Presentation of the Master's Thesis</text:p>
            <text:p text:style-name="P14">Examination interview on the Master's Thesis <text:span text:style-name="T1">and its links</text:span></text:p>
            <text:p text:style-name="P4"><draw:custom-shape text:anchor-type="char" draw:z-index="4" draw:name="Rechteck 1" draw:style-name="gr1" draw:text-style-name="P21" svg:width="0.336cm" svg:height="0.359cm" svg:x="10.982cm" svg:y="-0.046cm"><text:p/><draw:enhanced-geometry svg:viewBox="0 0 21600 21600" draw:type="rectangle" draw:enhanced-path="M 0 0 L 21600 0 21600 21600 0 21600 0 0 Z N"/></draw:custom-shape>to subjects of the curriculum</text:p>
            <text:p text:style-name="P14"><draw:custom-shape text:anchor-type="char" draw:z-index="5" draw:name="Rechteck 1" draw:style-name="gr1" draw:text-style-name="P21" svg:width="0.336cm" svg:height="0.359cm" svg:x="10.986cm" svg:y="-0.019cm"><text:p/><draw:enhanced-geometry svg:viewBox="0 0 21600 21600" draw:type="rectangle" draw:enhanced-path="M 0 0 L 21600 0 21600 21600 0 21600 0 0 Z N"/></draw:custom-shape>Examination interview on other subjects relevant to the curriculum</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="9" office:value-type="string">
            <text:p text:style-name="P4">Notes on the presentation of the Master's Thesis</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.D2" table:number-columns-spanned="9" office:value-type="string">
            <!-- Wenn die Titel der Projektarbeiten zu lang werden, werden hier Zeilenabstände im Bereich "Notizen" reduziert -->
			<xsl:variable select="themenbereich" name="themenbereich"/>
			<xsl:variable name="themenbereichlaenge" select="(string-length($themenbereich))"/>
				<xsl:if test="$themenbereichlaenge &lt; 400">
					<text:p text:style-name="P4"/>
				</xsl:if>
				<xsl:if test="$themenbereichlaenge &lt; 350">
					<text:p text:style-name="P4"/>
				</xsl:if>
				<xsl:if test="$themenbereichlaenge &lt; 300">
					<text:p text:style-name="P4"/>
				</xsl:if>
				<xsl:if test="$themenbereichlaenge &lt; 250">
					<text:p text:style-name="P4"/>
				</xsl:if>
				<xsl:if test="$themenbereichlaenge &lt; 200">
					<text:p text:style-name="P4"/>
				</xsl:if>
				<xsl:if test="$themenbereichlaenge &lt; 150">
					<text:p text:style-name="P4"/>
				</xsl:if>
			<xsl:variable select="themenbereich_2" name="themenbereich_2"/>
			<xsl:variable name="themenbereichlaenge_2" select="(string-length($themenbereich_2))"/>
				<xsl:if test="$themenbereichlaenge_2 &lt; 400">
					<text:p text:style-name="P4"/>
				</xsl:if>
				<xsl:if test="$themenbereichlaenge_2 &lt; 350">
					<text:p text:style-name="P4"/>
				</xsl:if>
				<xsl:if test="$themenbereichlaenge_2 &lt; 300">
					<text:p text:style-name="P4"/>
				</xsl:if>
				<xsl:if test="$themenbereichlaenge_2 &lt; 250">
					<text:p text:style-name="P4"/>
				</xsl:if>
				<xsl:if test="$themenbereichlaenge_2 &lt; 200">
					<text:p text:style-name="P4"/>
				</xsl:if>
				<xsl:if test="$themenbereichlaenge_2 &lt; 150">
					<text:p text:style-name="P4"/>
				</xsl:if>
			<text:p text:style-name="P4"/>
			<text:p text:style-name="P4"/>
			<text:p text:style-name="P4"/>
			<text:p text:style-name="P4"/>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
         </table:table>
      <text:p text:style-name="P23"> </text:p>
      <text:p text:style-name="P19"><xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" /></text:p>
      <text:p text:style-name="P18">Personal identity number: <xsl:value-of select="matrikelnr" /></text:p>
      <text:p text:style-name="P19"/>
      
      <table:table table:name="Tabelle1" table:style-name="Tabelle1">
        <table:table-column table:style-name="Tabelle1.A"/>
        <table:table-column table:style-name="Tabelle1.B"/>
        <table:table-column table:style-name="Tabelle1.C"/>
        <table:table-column table:style-name="Tabelle1.D"/>
        <table:table-column table:style-name="Tabelle1.E"/>
        <table:table-column table:style-name="Tabelle1.A" table:number-columns-repeated="2"/>
        <table:table-column table:style-name="Tabelle1.H"/>
        <table:table-column table:style-name="Tabelle1.I"/>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="9" office:value-type="string">
            <text:p text:style-name="P4">Questions to open the examination interview</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.D2" table:number-columns-spanned="9" office:value-type="string">
            <text:p text:style-name="P12"/>
            <text:p text:style-name="P12"/>
            <text:p text:style-name="P12"/>
            <text:p text:style-name="P12"/>
            <text:p text:style-name="P12"/>
            <text:p text:style-name="P12"/>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.15">
          <table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="4" office:value-type="string">
            <text:p text:style-name="P7">
            <draw:custom-shape text:anchor-type="char" draw:z-index="7" draw:name="Rechteck 1" draw:style-name="gr1" draw:text-style-name="P21" svg:width="0.336cm" svg:height="0.359cm" svg:x="16cm" svg:y="0.1cm"><text:p/><draw:enhanced-geometry svg:viewBox="0 0 21600 21600" draw:mirror-horizontal="false" draw:mirror-vertical="false" draw:type="rectangle" draw:enhanced-path="M 0 0 L 21600 0 21600 21600 0 21600 0 0 Z N"/></draw:custom-shape>
            <draw:custom-shape text:anchor-type="char" draw:z-index="10" draw:name="Rechteck 1" draw:style-name="gr1" draw:text-style-name="P21" svg:width="0.336cm" svg:height="0.359cm" svg:x="16cm" svg:y="0.7cm"><text:p/><draw:enhanced-geometry svg:viewBox="0 0 21600 21600" draw:mirror-horizontal="false" draw:mirror-vertical="false" draw:type="rectangle" draw:enhanced-path="M 0 0 L 21600 0 21600 21600 0 21600 0 0 Z N"/></draw:custom-shape>
            <draw:custom-shape text:anchor-type="char" draw:z-index="8" draw:name="Rechteck 1" draw:style-name="gr1" draw:text-style-name="P21" svg:width="0.336cm" svg:height="0.359cm" svg:x="16cm" svg:y="1.3cm"><text:p/><draw:enhanced-geometry svg:viewBox="0 0 21600 21600" draw:mirror-horizontal="false" draw:mirror-vertical="false" draw:type="rectangle" draw:enhanced-path="M 0 0 L 21600 0 21600 21600 0 21600 0 0 Z N"/></draw:custom-shape>
            <draw:custom-shape text:anchor-type="char" draw:z-index="9" draw:name="Rechteck 1" draw:style-name="gr1" draw:text-style-name="P21" svg:width="0.336cm" svg:height="0.359cm" svg:x="16cm" svg:y="1.9cm"><text:p/><draw:enhanced-geometry svg:viewBox="0 0 21600 21600" draw:mirror-horizontal="false" draw:mirror-vertical="false" draw:type="rectangle" draw:enhanced-path="M 0 0 L 21600 0 21600 21600 0 21600 0 0 Z N"/></draw:custom-shape>Assessment of the examination interview</text:p>
            <text:p text:style-name="P7">based on technical accuracy, completeness, structure and linguistic quality.</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Tabelle1.D2" table:number-columns-spanned="5" office:value-type="string">         
            <text:p text:style-name="P4">                        	
            	 (+++) Passed with distinction
           	</text:p>
            <text:p text:style-name="P4">
            	(++) Passed with merit
            </text:p>
            <text:p text:style-name="P4">
            	(+) Passed
            </text:p>
            <text:p text:style-name="P4">
            	(-) Failed
            </text:p>
            <text:p text:style-name="P22" />
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.16">
          <table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="9" office:value-type="string">
            <text:p text:style-name="P4">Reasons for failing OR any possible explanatory notes on a passing grade </text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.1">
          <table:table-cell table:style-name="Tabelle1.D2" table:number-columns-spanned="9" office:value-type="string">
           <text:p text:style-name="P12"/>
           <text:p text:style-name="P12"/>
           <text:p text:style-name="P12"/>
           <text:p text:style-name="P12"/>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.16">
          <table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="9" office:value-type="string">
            <text:p text:style-name="P4">Any unusual occurrences</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Tabelle1.19">
          <table:table-cell table:style-name="Tabelle1.D2" table:number-columns-spanned="9" office:value-type="string">
            <text:p text:style-name="P8"/>
            <text:p text:style-name="P7"/>
            <text:p text:style-name="P7"/>
            <text:p text:style-name="P7"/>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P11"/>
      <text:p text:style-name="P9"/>
      <text:p text:style-name="P9"/>
      <text:p text:style-name="P9">_________________________<text:tab/>_______________________<text:tab/>_____________________</text:p>
      <text:p text:style-name="P10">Signature of the chair<text:tab/>1st examiner<text:tab/>2nd examiner</text:p>
</xsl:template>
</xsl:stylesheet>