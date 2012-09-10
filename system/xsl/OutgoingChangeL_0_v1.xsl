<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0">
<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="outgoing">
<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
  <office:scripts/>
  <office:font-face-decls>
    <style:font-face style:name="Lohit Hindi1" svg:font-family="'Lohit Hindi'"/>
    <style:font-face style:name="Liberation Serif" svg:font-family="'Liberation Serif'" style:font-family-generic="roman" style:font-pitch="variable"/>
    <style:font-face style:name="Times New Roman" svg:font-family="'Times New Roman'" style:font-family-generic="roman" style:font-pitch="variable"/>
    <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="Liberation Sans" svg:font-family="'Liberation Sans'" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="Droid Sans Fallback" svg:font-family="'Droid Sans Fallback'" style:font-family-generic="system" style:font-pitch="variable"/>
    <style:font-face style:name="Lohit Hindi" svg:font-family="'Lohit Hindi'" style:font-family-generic="system" style:font-pitch="variable"/>
  </office:font-face-decls>
  <office:automatic-styles>
    <style:style style:name="Table1" style:family="table">
      <style:table-properties style:width="16.725cm" fo:margin-left="0.037cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table1.A" style:family="table-column">
      <style:table-column-properties style:column-width="8.354cm"/>
    </style:style>
    <style:style style:name="Table1.B" style:family="table-column">
      <style:table-column-properties style:column-width="8.371cm"/>
    </style:style>
    <style:style style:name="Table1.1" style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table1.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table1.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2" style:family="table">
      <style:table-properties style:width="16.743cm" fo:margin-left="0.037cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.A" style:family="table-column">
      <style:table-column-properties style:column-width="2.715cm"/>
    </style:style>
    <style:style style:name="Table2.B" style:family="table-column">
      <style:table-column-properties style:column-width="5.198cm"/>
    </style:style>
    <style:style style:name="Table2.C" style:family="table-column">
      <style:table-column-properties style:column-width="2.203cm"/>
    </style:style>
    <style:style style:name="Table2.F" style:family="table-column">
      <style:table-column-properties style:column-width="2.221cm"/>
    </style:style>
    <style:style style:name="Table2.1" style:family="table-row">
      <style:table-row-properties style:min-row-height="1.214cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table2.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.F1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.2" style:family="table-row">
      <style:table-row-properties style:min-row-height="0.635cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table3" style:family="table">
      <style:table-properties style:width="16.753cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.A" style:family="table-column">
      <style:table-column-properties style:column-width="7.752cm"/>
    </style:style>
    <style:style style:name="Table3.B" style:family="table-column">
      <style:table-column-properties style:column-width="0.499cm"/>
    </style:style>
    <style:style style:name="Table3.C" style:family="table-column">
      <style:table-column-properties style:column-width="8.502cm"/>
    </style:style>
    <style:style style:name="Table3.1" style:family="table-row">
      <style:table-row-properties style:min-row-height="0.616cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table3.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.5pt dotted #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border="none" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.5pt dotted #000000" fo:border-bottom="none" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4" style:family="table">
      <style:table-properties style:width="16.753cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.A" style:family="table-column">
      <style:table-column-properties style:column-width="7.752cm"/>
    </style:style>
    <style:style style:name="Table4.B" style:family="table-column">
      <style:table-column-properties style:column-width="0.499cm"/>
    </style:style>
    <style:style style:name="Table4.C" style:family="table-column">
      <style:table-column-properties style:column-width="8.502cm"/>
    </style:style>
    <style:style style:name="Table4.1" style:family="table-row">
      <style:table-row-properties style:min-row-height="0.616cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table4.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.5pt dotted #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border="none" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.5pt dotted #000000" fo:border-bottom="none" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:language="en" fo:country="US"/>
    </style:style>
    <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:language="en" fo:country="US" fo:font-style="italic" style:font-style-asian="italic"/>
    </style:style>
    <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:language="en" fo:country="GB"/>
    </style:style>
    <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:language="en" fo:country="GB" style:font-size-complex="11pt"/>
    </style:style>
    <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:language="en" fo:country="GB" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:language="none" fo:country="none" style:language-asian="none" style:country-asian="none"/>
    </style:style>
    <style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm"/>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" fo:line-height="130%"/>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" fo:line-height="130%" style:snap-to-layout-grid="false"/>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="GB" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" style:snap-to-layout-grid="false"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="GB" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm" fo:line-height="130%" style:snap-to-layout-grid="false"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.212cm"/>
      <style:text-properties fo:language="en" fo:country="US"/>
    </style:style>
    <style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0cm" fo:line-height="130%" style:snap-to-layout-grid="false"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0cm" fo:line-height="130%"/>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0cm" fo:line-height="130%" style:snap-to-layout-grid="false"/>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="5.251cm" fo:margin-top="0.141cm" fo:margin-bottom="0cm" fo:line-height="130%" fo:text-indent="0cm" style:auto-text-indent="false" style:snap-to-layout-grid="false"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P19" style:family="paragraph" style:parent-style-name="Heading_20_2">
      <style:text-properties fo:language="en" fo:country="US"/>
    </style:style>
    <style:style style:name="P20" style:family="paragraph" style:parent-style-name="Heading_20_2">
      <style:text-properties fo:language="en" fo:country="GB"/>
    </style:style>
    <style:style style:name="P21" style:family="paragraph" style:parent-style-name="Heading_20_1" style:master-page-name="Standard">
      <style:paragraph-properties style:page-number="auto"/>
      <style:text-properties fo:language="en" fo:country="US"/>
    </style:style>
    <style:style style:name="P22" style:family="paragraph" style:parent-style-name="Footer">
      <style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
      <style:text-properties style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P23" style:family="paragraph" style:parent-style-name="Footer">
      <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0.635cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
    </style:style>
    <style:style style:name="T1" style:family="text">
      <style:text-properties style:text-position="super 58%"/>
    </style:style>
    <style:style style:name="T2" style:family="text">
      <style:text-properties style:font-name-asian="Arial"/>
    </style:style>
    <style:style style:name="T3" style:family="text">
      <style:text-properties fo:language="en" fo:country="US"/>
    </style:style>
    <style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties fo:margin-left="0.319cm" fo:margin-right="0.319cm" style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:padding="0.026cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
    </style:style>
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
      <text:h text:style-name="P21" text:outline-level="1">Changes to Learning Agreement</text:h>
      <text:p text:style-name="P1"/>
      <text:p text:style-name="P1"/>
      <table:table table:name="Table1" table:style-name="Table1">
        <table:table-column table:style-name="Table1.A"/>
        <table:table-column table:style-name="Table1.B"/>
        <table:table-row table:style-name="Table1.1">
          <table:table-cell table:style-name="Table1.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P7">Field of Study: <text:span text:style-name="T3"><xsl:value-of select="studiengang"/></text:span></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table1.1">
          <table:table-cell table:style-name="Table1.A2" office:value-type="string">
            <text:p text:style-name="P7">First Name: <text:span text:style-name="T3"><xsl:value-of select="vorname"/></text:span></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table1.A1" office:value-type="string">
            <text:p text:style-name="P7">Last Name: <text:span text:style-name="T3"><xsl:value-of select="nachname"/></text:span></text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table1.1">
          <table:table-cell table:style-name="Table1.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P7">Student’s E-mail Adress: <text:span text:style-name="T3"><xsl:value-of select="email"/></text:span></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table1.1">
          <table:table-cell table:style-name="Table1.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P7">Sending Institution: <text:s/><text:span text:style-name="T3"><xsl:value-of select="sending_institution"/></text:span></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table1.1">
          <table:table-cell table:style-name="Table1.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P7">Country: <text:span text:style-name="T3"><xsl:value-of select="sending_institution_nation"/></text:span></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>
      <text:h text:style-name="P19" text:outline-level="2">Changes to original learning agreement</text:h>
      <text:p text:style-name="P14">(to be filled in ONLY if appropriate)</text:p>
      <text:p text:style-name="P1"/>
      <table:table table:name="Table2" table:style-name="Table2">
        <table:table-column table:style-name="Table2.A"/>
        <table:table-column table:style-name="Table2.B"/>
        <table:table-column table:style-name="Table2.C" table:number-columns-repeated="3"/>
        <table:table-column table:style-name="Table2.F"/>
        <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P10">Course unit code (if any) and page no. of the information package</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P10">Course unit title (as indicated in the course catalogue)</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P10">Deleted course unit</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P10">Added course unit</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P10">N<text:span text:style-name="T1">o</text:span> of </text:p>
            <text:p text:style-name="P10">week hours</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.F1" office:value-type="string">
            <text:p text:style-name="P12">N<text:span text:style-name="T1">o</text:span> of ECTS credits or national credits</text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.2">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.F1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.2">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.F1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.2">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.F1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.2">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.F1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.2">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.F1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.2">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.F1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.2">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.F1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.2">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.F1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.2">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.F1" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P2">If necessary, continue this list on a separate sheet.</text:p>
      <text:p text:style-name="P1"/>
      <text:p text:style-name="P1"/>
      <text:h text:style-name="P20" text:outline-level="2">Sending institution</text:h>
      <text:p text:style-name="P5">We confirm that the learning agreement is accepted.</text:p>
      <text:p text:style-name="P4"/>
      <table:table table:name="Table3" table:style-name="Table3">
        <table:table-column table:style-name="Table3.A"/>
        <table:table-column table:style-name="Table3.B"/>
        <table:table-column table:style-name="Table3.C"/>
        <table:table-row table:style-name="Table3.1">
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P15"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.B1" office:value-type="string">
            <text:p text:style-name="P13"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P18"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table3.1">
          <table:table-cell table:style-name="Table3.A2" office:value-type="string">
            <text:p text:style-name="P16">Date </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.B1" office:value-type="string">
            <text:p text:style-name="P9"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.A2" office:value-type="string">
            <text:p text:style-name="P16">Signature Departmental Coordinator</text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table3.1">
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P17"/>
            <text:p text:style-name="P16"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.B1" office:value-type="string">
            <text:p text:style-name="P9"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="P17"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table3.1">
          <table:table-cell table:style-name="Table3.A2" office:value-type="string">
            <text:p text:style-name="P8">Date</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.B1" office:value-type="string">
            <text:p text:style-name="P9"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.A2" office:value-type="string">
            <text:p text:style-name="P8">Signature International Coordinator</text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>
      <text:h text:style-name="P20" text:outline-level="2"><text:soft-page-break/>Receiving institution</text:h>
      <text:p text:style-name="P5">We confirm that the learning agreement is accepted.</text:p>
      <text:p text:style-name="P3"/>
      <table:table table:name="Table4" table:style-name="Table4">
        <table:table-column table:style-name="Table4.A"/>
        <table:table-column table:style-name="Table4.B"/>
        <table:table-column table:style-name="Table4.C"/>
        <table:table-row table:style-name="Table4.1">
          <table:table-cell table:style-name="Table4.A1" office:value-type="string">
            <text:p text:style-name="P15"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.B1" office:value-type="string">
            <text:p text:style-name="P13"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.A1" office:value-type="string">
            <text:p text:style-name="P18"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table4.1">
          <table:table-cell table:style-name="Table4.A2" office:value-type="string">
            <text:p text:style-name="P16">Date </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.B1" office:value-type="string">
            <text:p text:style-name="P9"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.A2" office:value-type="string">
            <text:p text:style-name="P16"><text:span text:style-name="T2"><text:s/></text:span>Signature Departmental Coordinator</text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table4.1">
          <table:table-cell table:style-name="Table4.A1" office:value-type="string">
            <text:p text:style-name="P17"/>
            <text:p text:style-name="P16"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.B1" office:value-type="string">
            <text:p text:style-name="P9"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.A1" office:value-type="string">
            <text:p text:style-name="P17"/>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table4.1">
          <table:table-cell table:style-name="Table4.A2" office:value-type="string">
            <text:p text:style-name="P8">Date</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.B1" office:value-type="string">
            <text:p text:style-name="P9"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.A2" office:value-type="string">
            <text:p text:style-name="P8">Signature International Coordinator</text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P1"/>
      <text:p text:style-name="P1"/>
    </office:text>
  </office:body>
</office:document-content>
</xsl:template>
</xsl:stylesheet>