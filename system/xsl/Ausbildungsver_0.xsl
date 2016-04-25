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
				<xsl:if test="svnr = ''"><text:p text:style-name="P42">Keine Sozialversicherungsnummer oder Ersatzkennzeichen vorhanden</text:p></xsl:if>
				<xsl:if test="gebdatum = ''"><text:p text:style-name="P42">Kein Geburtsdatum vorhanden</text:p></xsl:if>
				<xsl:if test="titel_kurzbz = ''"><text:p text:style-name="P42">Kein akademischer Grad vorhanden</text:p></xsl:if>
				
      <text:p text:style-name="P2"/>
      <text:p text:style-name="P4">Dieser Vertrag regelt das Rechtsverhältnis zwischen dem </text:p>
      <text:p text:style-name="P4"><text:span text:style-name="T1">Verein Fachhochschule Technikum Wien,</text:span> 1060 Wien, Mariahilfer Straße 37-39 (kurz „Erhalter“ genannt) einerseits <text:span text:style-name="T1">und</text:span></text:p>
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
      <text:p text:style-name="P1">
        <text:span text:style-name="T10"><text:span text:style-name="T10">Sozialversicherungsnummer:</text:span>
        <text:span text:style-name="Footnote_20_Symbol">
          <text:span text:style-name="T10">
            <text:note text:id="ftn1" text:note-class="footnote">
             <text:note-citation text:label="1">1</text:note-citation>
              <text:note-body>
                <text:p text:style-name="Standard">
                  <text:span text:style-name="T4">
                    <text:s/>
                  </text:span>
                  <text:span text:style-name="T5">Gemäß § 3 Absatz 1 des Bildungsdokumentationsgesetzes und der Bildungsdokumentationsverordnung-Fachhochschulen <text:s/> hat der Erhalter die Sozialversicherungsnummer zu erfassen und gemäß § 7 Absatz 2 im Wege der Agentur für Qualitätssicherung und Akkreditierung Austria an das zuständige Bundesministerium und die Bundesanstalt Statistik Österreich zu übermitteln.</text:span>
                </text:p>
                <text:p text:style-name="P10"/>
              </text:note-body>
            </text:note>
          </text:span>
        </text:span><text:tab/><xsl:value-of select="svnr"/></text:span>
      </text:p>
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
      <text:p text:style-name="P5">Studienort sind die Räumlichkeiten der FH Technikum Wien, 1200 Wien, Höchstädtplatz und 1210 Wien, Giefinggasse. Bei Bedarf kann der Erhalter einen anderen Studienort in Wien festlegen, außerhochschulische Aktivitäten (zB Exkursionen) können auch außerhalb von Wien stattfinden.</text:p>
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
      <text:p text:style-name="P5">Die Ausbildung erfolgt auf der Grundlage des Fachhochschul-Studiengesetzes, BGBl. Nr. 340/1993 idgF, des Hochschul-Qualitätssicherungsgesetzes, BGBl. I Nr. 74/2011 idgF, des Akkreditierungsbescheides des Board der AQ Austria vom 9.5.2012, GZ FH12020016 idgF und des Fördervertrags mit dem für Fachhochschulen zuständigen Bundesministerium idgF.</text:p>
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
      <text:p text:style-name="P5">Die Ausbildungsdauer beträgt <xsl:value-of select="studiengang_maxsemester"/> Semester.</text:p>
      <text:p text:style-name="P5"/>
      <text:p text:style-name="P5">Die Studentin bzw. der Student hat das Recht, eine Anerkennung nachgewiesener Kenntnisse beim Studiengang zu beantragen. Eine solche Anerkennung setzt voraus, dass die erworbenen Kenntnisse mit dem Inhalt und dem Umfang der Lehrveranstaltung bzw. eines Berufspraktikums gleichwertig sind und bewirkt die Anrechnung der entsprechenden Lehrveranstaltung oder des Berufspraktikums.</text:p>
      <text:p text:style-name="P36"/>
      <text:list xml:id="list107841840" text:continue-numbering="true" text:style-name="WW8Num7">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:p text:style-name="P43">Ausbildungsabschluss</text:p>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P44">Die Ausbildung endet mit der positiven Absolvierung der das jeweilige Studium abschließenden kommissionellen Prüfung. Nach dem Abschluss der vorgeschriebenen Prüfungen wird der akademische Grad <xsl:value-of select="studiengang_typ"/> of Science in Engineering (<xsl:value-of select="titel_kurzbz"/>) durch das FH-Kollegium verliehen.</text:p>
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
      <text:p text:style-name="P5">Der Erhalter führt eine periodische Überprüfung des Studiums im Hinblick auf Relevanz und Aktualität durch und ist im Einvernehmen mit dem FH-Kollegium berechtigt, daraus Änderungen des akkreditierten Studienganges abzuleiten.</text:p>
      <text:p text:style-name="P5"/>
      <text:p text:style-name="P5">Der Erhalter ist berechtigt, die Daten der/des Studierenden an den FH Technikum Wien Alumni Club zu übermitteln. Der Alumni Club ist der AbsolventInnenverein der FH Technikum Wien. Er hat zum Ziel, AbsolventInnen, Studierende und Lehrende miteinander zu vernetzen sowie AbsolventInnen laufend über Aktivitäten an der FH Technikum Wien zu informieren. Einer Zusendung von Informationen durch den Alumni Club kann jederzeit widersprochen werden.</text:p>
      <text:list xml:id="list1539722475" text:style-name="WW8Num4">
        <text:list-header>
          <text:p text:style-name="P39"/>
        </text:list-header>
      </text:list>
      <text:p text:style-name="P27">5.2 Pflichten</text:p>
      <text:list xml:id="list1245891399" text:continue-numbering="true" text:style-name="WW8Num4">
        <text:list-item>
          <text:p text:style-name="P40">Der Erhalter verpflichtet sich zur ordnungsgemäßen Planung und Durchführung des Studienganges in der Regelstudiendauer. Der Erhalter ist verpflichtet, allfällige Änderungen des akkreditierten Studienganges zeitgerecht bekannt zu geben.</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">Der Erhalter verpflichtet sich, jedenfalls folgende Dokumente zur Verfügung zu stellen: Studierendenausweis, Diploma Supplement, Urkunde über die Verleihung des akademischen Grades, Studienerfolgsbestätigung, Inskriptionsbestätigung.</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">Der Erhalter verpflichtet sich zur sorgfaltsgemäßen Verwendung der personenbezogenen Daten der Studierenden. Die Daten werden nur im Rahmen der gesetzlichen und vertraglichen Verpflichtungen sowie des Studienbetriebes verwendet und nicht an nicht berechtigte Dritte weitergegeben.</text:p>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P38"/>
      <text:list xml:id="list1403787711" text:continue-list="list890989597" text:style-name="WW8Num7">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:p text:style-name="P26">Rechte und Pflichten der Studierenden</text:p>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P27">6.1 Rechte</text:p>
      <text:p text:style-name="P5">Die Studentin bzw. der Student hat das Recht auf </text:p>
      <text:list xml:id="list1358297633" text:continue-list="list1245891399" text:style-name="WW8Num4">
        <text:list-item>
          <text:p text:style-name="P40">einen Studienbetrieb gemäß den im akkreditierten Studiengang idgF und in der Satzung der FH Technikum Wien idgF festgelegten Bedingungen;</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P37">
            <text:span text:style-name="T12">Unterbrechung der Ausbildung aus nachgewiesenen zwingenden persönlichen, gesundheitlichen oder beruflichen Gründen.</text:span>
          </text:p>
          <text:p text:style-name="P38"/>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P28">6.2 Pflichten</text:p>
      <text:p text:style-name="P29">6.2.1 Einhaltung studienrelevanter Bestimmungen</text:p>
      <text:p text:style-name="P5">Die Studentin bzw der Student ist verpflichtet, insbesondere folgende Bestimmungen einzuhalten:</text:p>
      <text:list xml:id="list2358297633" text:continue-list="list1245891399" text:style-name="WW8Num4">
        <text:list-item>
          <text:p text:style-name="P40">Studienordnung und Studienrechtliche Bestimmungen / Prüfungsordnung idgF</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P37">Hausordnung idgF</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P37">Brandschutzordnung idgF</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P37">Bibliotheksordnung idgF</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P37">Die für den jeweiligen Studiengang geltende/n Laborordnung/en idgF</text:p>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P5"/>
      <text:p text:style-name="P5">Diese Dokumente sind öffentlich zugänglich unter www.technikum-wien.at.</text:p>
      
      <text:p text:style-name="P29">6.2.2 Studienbeitrag</text:p>
      <text:p text:style-name="P5">Die Studentin bzw. der Student ist verpflichtet, zwei Wochen vor Beginn jedes Semesters (StudienanfängerInnen: bis 20. August vor Studienbeginn) einen Studienbeitrag gemäß Fachhochschul-Studiengesetz in der Höhe von derzeit € 363,36 netto pro Semester zu entrichten. Dies gilt auch in Semestern mit DiplomandInnenstatus o.ä. Im Falle einer Erhöhung des gesetzlichen Studienbeitragssatzes erhöht sich der angeführte Betrag entsprechend. <text:soft-page-break/>Die vollständige Bezahlung des Studienbeitrags ist Voraussetzung für die Aufnahme bzw. die Fortsetzung des Studiums. Bei Nichtantritt des Studiums oder Abbruch zu Beginn oder während des Semesters verfällt der Studienbeitrag.</text:p>
      
      <text:p text:style-name="P31">6.2.3 ÖH-Beitrag</text:p>
      <text:p text:style-name="P5">Gemäß § 4 Abs 10 FHStG sind Studierende an Fachhochschulen Mitglieder der Österreichischen HochschülerInnenschaft (ÖH). Der/Die Studierende hat semesterweise einen ÖH-Beitrag an den Erhalter zu entrichten, der diesen an die ÖH abführt. Die Entrichtung des Betrags ist Voraussetzung für die Zulassung zum Studium bzw. für dessen Fortsetzung.</text:p>
      
      <text:p text:style-name="P32">6.2.4 Kaution</text:p>
      <text:p text:style-name="P5">Im Zuge der Einschreibung ist der Nachweis über die einbezahlte Kaution zu erbringen.</text:p>
      <text:p text:style-name="P5">Die Kaution beträgt € 150,–.</text:p>
      <text:p text:style-name="P5">Bei Nichtantritt des Studiums oder Abbruch während des ersten oder zweiten Semesters verfällt die Kaution.</text:p>
      <text:p text:style-name="P5">Bei aufrechtem Inskriptionsverhältnis zu Beginn des zweiten Semesters wird die Kaution auf den Unkostenbeitrag (siehe nächster Punkt) des ersten und zweiten Semesters angerechnet. </text:p>
      
      <text:p text:style-name="P30">6.2.5 Unkostenbeitrag </text:p>
      <text:p text:style-name="P41">Pro Semester ist ein Unkostenbeitrag zu entrichten, wobei es sich nicht um einen Pauschalbetrag handelt. Der Unkostenbeitrag stellt eine Abgeltung für über das Normalmaß hinausgehende Serviceleistungen der FH dar, z.B. Freifächer, Beratung/Info Auslandsstudium, Sponsionsfeiern, Vorträge / Jobbörse, Mensa etc.</text:p>
      <text:p text:style-name="P5">Die Höhe des Unkostenbeitrages beträgt derzeit € 75,– pro Semester. Eine allfällige Anpassung wird durch Aushang bekannt gemacht.</text:p>
      <text:p text:style-name="P5">Der Unkostenbeitrag ist 
	<xsl:choose>
		<xsl:when test="semesterStudent = 3" >
			im
		</xsl:when>
		<xsl:otherwise>
			ab dem 
		</xsl:otherwise>
	</xsl:choose>
3. Semester gleichzeitig mit der Studiengebühr vor Beginn des Semesters zu entrichten.</text:p>
      <text:p text:style-name="P5">Bei Vertragsauflösung vor Studienabschluss aus Gründen, die die Studentin bzw. der Student zu vertreten hat, oder auf deren bzw. dessen Wunsch, wird der Unkostenbeitrag zur Abdeckung der dem Erhalter erwachsenen administrativen Zusatzkosten einbehalten.</text:p>
      
      <text:p text:style-name="P32">6.2.6 Lehr- und Lernbehelfe</text:p>
      <text:p text:style-name="P8">Die Anschaffung unterrichtsbezogener Literatur und individueller Lernbehelfe ist durch den Unkostenbeitrag nicht abgedeckt. Eventuelle zusätzliche Kosten, die sich beispielsweise durch die studiengangsbezogene, gemeinsame Anschaffung von Lehr- bzw. Lernbehelfen (Skripten, CDs, Bücher, Projektmaterialien, Kopierpapier etc.) oder durch Exkursionen ergeben, werden von jedem Studiengang individuell eingehoben.</text:p>
      
      <text:p text:style-name="P32">6.2.7 Beibringung persönlicher Daten</text:p>
      <text:p text:style-name="P35">Die Studentin bzw. der Student ist verpflichtet, persönliche Daten beizubringen, die auf Grund eines Gesetzes, einer Verordnung oder eines Bescheides vom Erhalter erfasst werden müssen oder zur Erfüllung des Ausbildungsvertrages bzw für den Studienbetrieb unerlässlich sind.</text:p>
      
      <text:p text:style-name="P32">6.2.8 Aktualisierung eigener Daten und Bezug von Informationen</text:p>
      <text:p text:style-name="P35">Die Studentin bzw. der Student hat unaufgefordert dafür zu sorgen, dass die von ihr/ihm beigebrachten Daten aktuell sind. Änderungen sind der Studiengangsassistenz unverzüglich schriftlich mitzuteilen. Darüber hinaus trifft sie/ihn die Pflicht, sich von studienbezogenen Informationen, die ihr/ihm an die vom Erhalter zur Verfügung gestellte Emailadresse zugestellt werden, in geeigneter Weise Kenntnis zu verschaffen.</text:p>
      
      <text:p text:style-name="P32">6.2.9 Verwertungsrechte</text:p>
      <text:p text:style-name="P35">Sofern nicht im Einzelfall andere Regelungen zwischen dem Erhalter und der Studentin oder dem Studenten getroffen wurden, ist die Studentin oder der Student verpflichtet, dem Erhalter die Rechte an Forschungs- und Entwicklungsergebnissen auf dessen schriftliche Anfrage hin anzubieten.</text:p>
      
      <text:p text:style-name="P32">6.2.10 Aufzeichnungen und Mitschnitte</text:p>
      <text:p text:style-name="P35">Es ist der/dem Studierenden ausdrücklich untersagt, Lehrveranstaltungen als Ganzes oder nur Teile davon aufzuzeichnen und/oder mitzuschneiden (z.B. durch Film- und/oder Tonaufnahmen oder sonstige hierfür geeignete audiovisuelle Mittel). Darüber hinaus ist jede Form der öffentlichen Zurverfügungstellung (drahtlos oder drahtgebunden) der vorgenannten Aufnahmen z.B. in sozialen Netzwerken wie Facebook, StudiVZ etc, aber auch auf Youtube usw. oder durch sonstige für diese Zwecke geeignete Kommunikationsmittel untersagt. Diese Regelungen gelten sinngemäß auch für Skripten, sonstige Lernbehelfe und Prüfungsangaben.</text:p>
      <text:p text:style-name="P35">Ausgenommen hiervon ist eine Aufzeichnung zu ausschließlichen Lern-, Studien- und Forschungszwecken und zum privaten Gebrauch, sofern hierfür der Vortragende vorab ausdrücklich seine schriftliche Zustimmung erteilt hat.</text:p>
      
      <text:p text:style-name="P31">6.2.11 Geheimhaltungspflicht</text:p>
      <text:p text:style-name="P5">Die Studentin bzw. der Student ist zur Geheimhaltung von Forschungs- und Entwicklungsaktivitäten und -ergebnissen gegenüber Dritten verpflichtet. </text:p>
      <text:p text:style-name="P5"/>
      
      <text:p text:style-name="P5">6.2.12 Unfallmeldung</text:p>
      <text:p text:style-name="P5">Im Falle eines Unfalles mit körperlicher Verletzung des/der Studierenden im Zusammenhang mit dem Studium ist die/der Studierende verpflichtet, diesen innerhalb von drei Tagen dem Studiengangssekretariat zu melden. Dies betrifft auch Wegunfälle zur oder von der FH.</text:p>
      <text:p text:style-name="P5"/>
      
      <text:p text:style-name="P5">6.2.13 Schadensmeldung</text:p>
      <text:p text:style-name="P5">Im Falle des Eintretens eines Schadens am Inventar der Fachhochschule ist der/die Studierende verpflichtet, diesen innerhalb von drei Tagen dem Studiengangssekretariat zu melden. Allfällige Haftungsansprüche bleiben hiervon unberührt.</text:p>
      <text:p text:style-name="P5"/>
      
      <text:p text:style-name="P5">6.2.14 Rückgabeverpflichtung bei Studienende</text:p>
      <text:p text:style-name="P5">Die Studentin bzw der Student ist verpflichtet, bei einer Beendigung des Studiums unverzüglich alle zur Verfügung gestellten Gerätschaften, Bücher, Schlüssel und sonstige Materialien zurückzugeben.</text:p>
      <text:p text:style-name="P5"/>
      <text:list xml:id="list866389060" text:continue-list="list1403787711" text:style-name="WW8Num7">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:p text:style-name="P26">Beendigung des Vertrages</text:p>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P27">7.1 Auflösung im beiderseitigen Einvernehmen</text:p>
      <text:p text:style-name="P8">Im beiderseitigen Einvernehmen ist die Auflösung des Ausbildungsvertrages jederzeit ohne Angabe von Gründen möglich. Die einvernehmliche Auflösung bedarf der Schriftform.</text:p>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P27">7.2 Kündigung durch die Studentin bzw. den Studenten</text:p>
      <text:p text:style-name="P8">Die Studentin bzw. der Student kann den Ausbildungsvertrag schriftlich jeweils zum Ende eines Semesters kündigen.</text:p>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P27">7.3 Automatische Beendigung des Vertrages</text:p>
      <text:p text:style-name="P8">Nach erfolgreicher Beendigung des Studiums endet der Vertrag automatisch mit der Verleihung des akademischen Grades.</text:p>
      <text:p text:style-name="P8">Der Vertrag endet automatisch durch die negative Beurteilung der letztmöglichen Prüfungswiederholung.</text:p>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P27">7.4 Ausschluss durch den Erhalter</text:p>
      <text:p text:style-name="P5">Der Erhalter kann die Studentin bzw. den Studenten aus wichtigem Grund mit sofortiger Wirkung vom weiteren Studium ausschließen, und zwar beispielsweise wegen</text:p>
      <text:list xml:id="list1474649563" text:continue-list="list1358297633" text:style-name="WW8Num4">
        <text:list-item>
          <text:p text:style-name="P40">nicht genügender Leistung im Sinne der Prüfungsordnung;</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">mehrmaligem unentschuldigten Verletzen der Anwesenheitspflicht ;</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">wiederholtem Nichteinhalten von Prüfungsterminen und Abgabeterminen für Seminararbeiten, Projektarbeiten etc.;</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">schwerwiegender bzw. wiederholter Verstöße gegen die Hausordnung;</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">persönlichem Verhalten, das zu einer Beeinträchtigung des Images und/oder Betriebes des Studienganges, der Fachhochschule bzw. des Erhalters oder von Personen führt, die für die Fachhochschule bzw. den Erhalter tätig sind;</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">Verletzung der Verpflichtung, dem Erhalter die Rechte an Forschungs- und Entwicklungsergebnissen anzubieten (siehe Pkt. 6.2.9);</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">Verletzung der Geheimhaltungspflicht (siehe Pkt. 6.2.11); </text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">strafgerichtlicher Verurteilung (wobei die Art des Deliktes und der Grad der Schuld berücksichtigt werden);</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">Nichterfüllung finanzieller Verpflichtungen trotz Mahnung (z.B. Unkostenbeitrag, Studienbeitrag etc.);</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">Weigerung zur Beibringung von Daten (siehe Pkt. 6.2.7)</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P40">Plagiieren im Rahmen wissenschaftlicher Arbeiten</text:p>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P12"/>
      <text:p text:style-name="P5">Der Ausschluss kann mündlich erklärt werden. Mit Ausspruch des Ausschlusses endet der Ausbildungsvertrag, es sei denn, es wird ausdrücklich auf einen anderen Endtermin hingewiesen. Eine schriftliche Bestätigung des Ausschlusses wird innerhalb von zwei Wochen nach dessen Ausspruch per Post an die bekannt gegebene Adresse abgeschickt oder auf andere geeignete Weise übermittelt.</text:p>
      <text:p text:style-name="P5">Gleichzeitig mit dem Ausspruch des Ausschlusses kann auch ein Hausverbot verhängt werden.</text:p>
      <text:p text:style-name="P5"/>
	<xsl:if test="studiengangSprache = 'English' or ((studiengang_kurzbz ='BEW' or studiengang_kurzbz='BWI') and orgform ='DL') or (studiengang_kurzbz ='MWI' and (orgform ='DL' or orgform ='PT'))">
      <text:list xml:id="list422793909" text:continue-list="list866389060" text:style-name="WW8Num7">
		<text:list-item>
          <text:list>
            <text:list-item>
              <text:p text:style-name="P26">
                Ergänzende Vereinbarungen
              </text:p>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P5">Das gesamte Studienprogramm wird in englischer Sprache angeboten. Die Studentin bzw. der Student erklärt, die englische Sprache in Wort und Schrift in dem für eine akademische Ausbildung erforderlichen Ausmaß zu beherrschen.</text:p>
      <text:p text:style-name="P17"/>
      <text:p text:style-name="P5">
        Studierende des Studiengangs sind verpflichtet, eine EDV-Ausstattung zu beschaffen und zu unterhalten, die es ermöglicht, an den Fernlehrelementen teilzunehmen. Die gesamten Kosten der Anschaffung und des Betriebs (inkl. Kosten für Internet und e-mail) trägt der Student bzw. die Studentin.
      </text:p>
      <text:p text:style-name="P5"/>
	</xsl:if>

      <text:list xml:id="list398292235" text:continue-list="list866389060" text:style-name="WW8Num7">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:p text:style-name="P26"><text:soft-page-break/>Unwirksamkeit von Vertragsbestimmungen</text:p>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P5">Sollten einzelne Bestimmungen dieses Vertrages unwirksam oder nichtig sein oder werden, so berührt dies die Gültigkeit der übrigen Bestimmungen dieses Vertrages nicht.</text:p>
      <text:p text:style-name="P5"/>
      <text:list xml:id="list118967672" text:continue-list="list866389060" text:style-name="WW8Num7">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:p text:style-name="P26">Ausfertigungen, Gebühren, Gerichtsstand, geltendes Recht</text:p>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P5">Die Ausfertigung dieses Vertrages erfolgt in zweifacher Ausführung. Ein Original verbleibt im zuständigen Administrationsbüro des Fachhochschul-Studienganges. Eine Ausfertigung wird der Studentin bzw. dem Studenten übergeben.</text:p>
      <text:p text:style-name="P5">Für Streitigkeiten aus diesem Vertrag gilt österreichisches Recht als vereinbart, allfällige Klagen gegen den Erhalter sind beim sachlich zuständigen Gericht in Wien einzubringen.</text:p>
      <text:p text:style-name="P5"/>
      <text:p text:style-name="P5">Der Ausbildungsvertrag ist gebührenfrei.</text:p>
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
            <text:p text:style-name="P19">Für die FH Technikum Wien</text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
    </office:text>
</xsl:template>
</xsl:stylesheet>