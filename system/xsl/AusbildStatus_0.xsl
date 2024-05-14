<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="ausbildungsvertraege">

<office:document-styles xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
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
  <office:styles>
    <style:default-style style:family="graphic">
      <style:graphic-properties svg:stroke-color="#808080" draw:fill-color="#cfe7f5" fo:wrap-option="no-wrap" draw:shadow-offset-x="0.3cm" draw:shadow-offset-y="0.3cm" draw:start-line-spacing-horizontal="0.283cm" draw:start-line-spacing-vertical="0.283cm" draw:end-line-spacing-horizontal="0.283cm" draw:end-line-spacing-vertical="0.283cm" style:flow-with-text="false"/>
      <style:paragraph-properties style:text-autospace="ideograph-alpha" style:line-break="strict" style:writing-mode="lr-tb" style:font-independent-line-spacing="false">
        <style:tab-stops/>
      </style:paragraph-properties>
      <style:text-properties style:use-window-font-color="true" fo:font-size="12pt" fo:language="de" fo:country="DE" style:font-size-asian="10.5pt" style:language-asian="zh" style:country-asian="CN" style:font-size-complex="12pt" style:language-complex="hi" style:country-complex="IN"/>
    </style:default-style>
    <style:default-style style:family="paragraph">
      <style:paragraph-properties fo:hyphenation-ladder-count="no-limit" style:text-autospace="ideograph-alpha" style:punctuation-wrap="hanging" style:line-break="strict" style:tab-stop-distance="1.251cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:font-name="Liberation Serif" fo:font-size="12pt" fo:language="de" fo:country="DE" style:font-name-asian="Droid Sans" style:font-size-asian="10.5pt" style:language-asian="zh" style:country-asian="CN" style:font-name-complex="Lohit Hindi" style:font-size-complex="12pt" style:language-complex="hi" style:country-complex="IN" fo:hyphenate="false" fo:hyphenation-remain-char-count="2" fo:hyphenation-push-char-count="2"/>
    </style:default-style>
    <style:default-style style:family="table">
      <style:table-properties table:border-model="collapsing"/>
    </style:default-style>
    <style:default-style style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:default-style>
    <style:style style:name="Standard" style:family="paragraph" style:class="text">
      <style:paragraph-properties fo:orphans="2" fo:widows="2" style:writing-mode="lr-tb"/>
      <style:text-properties style:use-window-font-color="true" style:font-name="Arial" fo:font-size="12pt" fo:language="de" fo:country="DE" style:font-name-asian="Times New Roman" style:font-size-asian="12pt" style:font-name-complex="Arial" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
    </style:style>
    <style:style style:name="Heading" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Text_20_body" style:class="text">
      <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.212cm" fo:keep-with-next="always"/>
      <style:text-properties style:font-name="Liberation Sans" fo:font-size="14pt" style:font-name-asian="Droid Sans" style:font-size-asian="14pt" style:font-name-complex="Lohit Hindi" style:font-size-complex="14pt"/>
    </style:style>
    <style:style style:name="Text_20_body" style:display-name="Text body" style:family="paragraph" style:parent-style-name="Standard" style:class="text">
      <style:text-properties style:font-name="Optima" fo:font-size="11pt" style:font-size-asian="11pt" style:font-name-complex="Optima"/>
    </style:style>
    <style:style style:name="List" style:family="paragraph" style:parent-style-name="Text_20_body" style:class="list">
      <style:text-properties style:font-size-asian="12pt" style:font-name-complex="Lohit Hindi1"/>
    </style:style>
    <style:style style:name="Caption" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
      <style:paragraph-properties fo:margin-top="0.212cm" fo:margin-bottom="0.212cm" text:number-lines="false" text:line-number="0"/>
      <style:text-properties fo:font-size="12pt" fo:font-style="italic" style:font-size-asian="12pt" style:font-style-asian="italic" style:font-name-complex="Lohit Hindi1" style:font-size-complex="12pt" style:font-style-complex="italic"/>
    </style:style>
    <style:style style:name="Index" style:family="paragraph" style:parent-style-name="Standard" style:class="index">
      <style:paragraph-properties text:number-lines="false" text:line-number="0"/>
      <style:text-properties style:font-size-asian="12pt" style:font-name-complex="Lohit Hindi1"/>
    </style:style>
    <style:style style:name="Heading_20_1" style:display-name="Heading 1" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Standard" style:default-outline-level="1" style:class="text">
      <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.106cm" fo:keep-with-next="always"/>
      <style:text-properties fo:font-size="16pt" fo:language="de" fo:country="AT" fo:font-weight="bold" style:letter-kerning="true" style:font-size-asian="16pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="16pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="Heading_20_2" style:display-name="Heading 2" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Standard" style:list-style-name="WW8Num7" style:class="text">
      <style:paragraph-properties fo:margin="100%" fo:margin-left="0.63cm" fo:margin-right="0cm" fo:margin-top="0.423cm" fo:margin-bottom="0.106cm" fo:text-indent="0cm" style:auto-text-indent="false" fo:keep-with-next="always"/>
      <style:text-properties fo:font-size="11pt" fo:font-style="italic" fo:font-weight="bold" style:font-size-asian="11pt" style:font-style-asian="italic" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="14pt" style:font-style-complex="italic" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="Heading_20_3" style:display-name="Heading 3" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Standard" style:class="text">
      <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.106cm" fo:keep-with-next="always"/>
      <style:text-properties fo:font-size="13pt" fo:font-weight="bold" style:font-size-asian="13pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="13pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="Heading_20_4" style:display-name="Heading 4" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Standard" style:class="text">
      <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.106cm" fo:keep-with-next="always"/>
      <style:text-properties style:font-name="Times New Roman" fo:font-size="14pt" fo:font-weight="bold" style:font-size-asian="14pt" style:font-weight-asian="bold" style:font-name-complex="Times New Roman" style:font-size-complex="14pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="Heading_20_5" style:display-name="Heading 5" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Standard" style:default-outline-level="5" style:class="text">
      <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.106cm"/>
      <style:text-properties fo:font-size="13pt" fo:font-style="italic" fo:font-weight="bold" style:font-size-asian="13pt" style:font-style-asian="italic" style:font-weight-asian="bold" style:font-size-complex="13pt" style:font-style-complex="italic" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="Heading_20_6" style:display-name="Heading 6" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Standard" style:default-outline-level="6" style:class="text">
      <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.106cm"/>
      <style:text-properties style:font-name="Times New Roman" fo:font-size="11pt" fo:font-weight="bold" style:font-size-asian="11pt" style:font-weight-asian="bold" style:font-name-complex="Times New Roman" style:font-size-complex="11pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="Heading_20_7" style:display-name="Heading 7" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Standard" style:default-outline-level="7" style:class="text">
      <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.106cm"/>
      <style:text-properties style:font-name="Times New Roman" style:font-name-complex="Times New Roman" style:font-size-complex="12pt"/>
    </style:style>
    <style:style style:name="Heading_20_8" style:display-name="Heading 8" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Standard" style:default-outline-level="8" style:class="text">
      <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.106cm"/>
      <style:text-properties style:font-name="Times New Roman" fo:font-style="italic" style:font-style-asian="italic" style:font-name-complex="Times New Roman" style:font-size-complex="12pt" style:font-style-complex="italic"/>
    </style:style>
    <style:style style:name="Heading_20_9" style:display-name="Heading 9" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Standard" style:default-outline-level="9" style:class="text">
      <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.106cm"/>
      <style:text-properties fo:font-size="11pt" style:font-size-asian="11pt" style:font-name-complex="Arial" style:font-size-complex="11pt"/>
    </style:style>
    <style:style style:name="Header" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="8.001cm" style:type="center"/>
          <style:tab-stop style:position="16.002cm" style:type="right"/>
        </style:tab-stops>
      </style:paragraph-properties>
    </style:style>
    <style:style style:name="Footer" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="8.001cm" style:type="center"/>
          <style:tab-stop style:position="16.002cm" style:type="right"/>
        </style:tab-stops>
      </style:paragraph-properties>
    </style:style>
    <style:style style:name="Textkörper_20_2" style:display-name="Textkörper 2" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="justify" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Optima" fo:font-size="11pt" style:font-size-asian="11pt" style:font-name-complex="Optima"/>
    </style:style>
    <style:style style:name="Kommentartext" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="10pt" style:font-size-asian="10pt"/>
    </style:style>
    <style:style style:name="Textkörper_20_3" style:display-name="Textkörper 3" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="justify" style:justify-single-word="false">
        <style:tab-stops>
          <style:tab-stop style:position="1.251cm"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties fo:font-size="10.5pt" style:font-size-asian="10.5pt"/>
    </style:style>
    <style:style style:name="Kommentarthema" style:family="paragraph" style:parent-style-name="Kommentartext" style:next-style-name="Kommentartext">
      <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="Sprechblasentext" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:font-name="Tahoma" fo:font-size="8pt" style:font-size-asian="8pt" style:font-name-complex="Tahoma" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="Standard_20__28_Web_29_" style:display-name="Standard (Web)" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.176cm" fo:margin-bottom="0.176cm"/>
      <style:text-properties fo:color="#000000" style:font-name="Times New Roman" style:font-name-complex="Times New Roman" style:font-size-complex="12pt"/>
    </style:style>
    <style:style style:name="Tabelleninhalt" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.141cm" fo:margin-bottom="0.141cm"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="GB" style:font-size-asian="9pt"/>
    </style:style>
    <style:style style:name="Aufzählungen" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="WW8Num5"/>
    <style:style style:name="Formatvorlage_20_Aufzählung_20_1" style:display-name="Formatvorlage Aufzählung 1" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="WW8Num4">
      <style:paragraph-properties fo:line-height="130%"/>
      <style:text-properties fo:font-size="10pt" style:font-size-asian="10pt"/>
    </style:style>
    <style:style style:name="Standard1" style:family="paragraph">
      <style:paragraph-properties fo:orphans="2" fo:widows="2"/>
      <style:text-properties fo:color="#000000" style:font-name="Lucida Grande" fo:font-size="12pt" fo:language="de" fo:country="DE" style:font-name-asian="ヒラギノ角ゴ Pro W3" style:font-size-asian="12pt" style:font-name-complex="Lucida Grande" style:font-size-complex="10pt" style:language-complex="ar" style:country-complex="SA"/>
    </style:style>
    <style:style style:name="Footnote" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
      <style:paragraph-properties fo:margin="100%" fo:margin-left="0.598cm" fo:margin-right="0cm" fo:text-indent="-0.598cm" style:auto-text-indent="false" text:number-lines="false" text:line-number="0"/>
      <style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="Table_20_Contents" style:display-name="Table Contents" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
      <style:paragraph-properties text:number-lines="false" text:line-number="0"/>
    </style:style>
    <style:style style:name="Table_20_Heading" style:display-name="Table Heading" style:family="paragraph" style:parent-style-name="Table_20_Contents" style:class="extra">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false" text:number-lines="false" text:line-number="0"/>
      <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="WW8Num2z0" style:family="text">
      <style:text-properties style:text-position="-50% 100%" style:font-name="Wingdings" fo:font-size="16pt" style:font-size-asian="16pt" style:font-name-complex="Wingdings" style:font-size-complex="16pt"/>
    </style:style>
    <style:style style:name="WW8Num3z0" style:family="text">
      <style:text-properties style:font-name="Arial" style:font-name-complex="Arial"/>
    </style:style>
    <style:style style:name="WW8Num3z1" style:family="text">
      <style:text-properties style:font-name="Courier New" style:font-name-complex="Courier New"/>
    </style:style>
    <style:style style:name="WW8Num3z2" style:family="text">
      <style:text-properties style:font-name="Wingdings" style:font-name-complex="Wingdings"/>
    </style:style>
    <style:style style:name="WW8Num3z3" style:family="text">
      <style:text-properties style:font-name="Symbol" style:font-name-complex="Symbol"/>
    </style:style>
    <style:style style:name="WW8Num4z0" style:family="text">
      <style:text-properties fo:font-variant="normal" fo:text-transform="none" fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:text-position="0% 100%" style:font-name="Wingdings" fo:font-size="12pt" fo:font-style="normal" fo:text-shadow="none" fo:font-weight="normal" style:font-size-asian="12pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-name-complex="Wingdings" text:display="true"/>
    </style:style>
    <style:style style:name="WW8Num4z1" style:family="text">
      <style:text-properties style:font-name="Courier New" style:font-name-complex="Courier New"/>
    </style:style>
    <style:style style:name="WW8Num4z2" style:family="text">
      <style:text-properties style:font-name="Wingdings" style:font-name-complex="Wingdings"/>
    </style:style>
    <style:style style:name="WW8Num4z3" style:family="text">
      <style:text-properties style:font-name="Symbol" style:font-name-complex="Symbol"/>
    </style:style>
    <style:style style:name="WW8Num5z0" style:family="text">
      <style:text-properties fo:color="#008462" style:text-position="super 58%" style:font-name="Wingdings" fo:font-size="20pt" style:font-size-asian="20pt" style:font-name-complex="Wingdings" style:font-size-complex="20pt"/>
    </style:style>
    <style:style style:name="WW8Num5z1" style:family="text">
      <style:text-properties style:font-name="Courier New" style:font-name-complex="Courier New"/>
    </style:style>
    <style:style style:name="WW8Num5z2" style:family="text">
      <style:text-properties style:font-name="Wingdings" style:font-name-complex="Wingdings"/>
    </style:style>
    <style:style style:name="WW8Num5z3" style:family="text">
      <style:text-properties style:font-name="Symbol" style:font-name-complex="Symbol"/>
    </style:style>
    <style:style style:name="WW8Num6z0" style:family="text">
      <style:text-properties style:font-name="Symbol" style:font-name-complex="Symbol"/>
    </style:style>
    <style:style style:name="WW8Num6z1" style:family="text">
      <style:text-properties style:font-name="Courier New" style:font-name-complex="Courier New"/>
    </style:style>
    <style:style style:name="WW8Num6z2" style:family="text">
      <style:text-properties style:font-name="Wingdings" style:font-name-complex="Wingdings"/>
    </style:style>
    <style:style style:name="WW8Num7z0" style:family="text">
      <style:text-properties fo:font-style="normal" style:font-style-asian="normal"/>
    </style:style>
    <style:style style:name="Absatz-Standardschriftart" style:family="text"/>
    <style:style style:name="Page_20_Number" style:display-name="Page Number" style:family="text" style:parent-style-name="Absatz-Standardschriftart"/>
    <style:style style:name="Kommentarzeichen" style:family="text">
      <style:text-properties fo:font-size="8pt" style:font-size-asian="8pt"/>
    </style:style>
    <style:style style:name="Footnote_20_Symbol" style:display-name="Footnote Symbol" style:family="text">
      <style:text-properties style:text-position="super 58%"/>
    </style:style>
    <style:style style:name="Footnote_20_anchor" style:display-name="Footnote anchor" style:family="text">
      <style:text-properties style:text-position="super 58%"/>
    </style:style>
    <style:style style:name="Frame" style:family="graphic">
      <style:graphic-properties text:anchor-type="paragraph" svg:x="0cm" svg:y="0cm" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit" style:wrap-contour="false" style:vertical-pos="top" style:vertical-rel="paragraph-content" style:horizontal-pos="center" style:horizontal-rel="paragraph-content"/>
    </style:style>
    <style:style style:name="Graphics" style:family="graphic">
      <style:graphic-properties text:anchor-type="paragraph" svg:x="0cm" svg:y="0cm" style:wrap="dynamic" style:number-wrapped-paragraphs="no-limit" style:wrap-contour="false" style:vertical-pos="top" style:vertical-rel="paragraph" style:horizontal-pos="center" style:horizontal-rel="paragraph"/>
    </style:style>
    <style:style style:name="OLE" style:family="graphic">
      <style:graphic-properties text:anchor-type="paragraph" svg:x="0cm" svg:y="0cm" style:wrap="dynamic" style:number-wrapped-paragraphs="no-limit" style:wrap-contour="false" style:vertical-pos="top" style:vertical-rel="paragraph" style:horizontal-pos="center" style:horizontal-rel="paragraph"/>
    </style:style>
    <text:outline-style style:name="Outline">
      <text:outline-level-style text:level="1" style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0cm" fo:text-indent="0cm" fo:margin-left="0cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="2" style:num-format="1" text:display-levels="2">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.016cm" fo:text-indent="-1.016cm" fo:margin-left="1.016cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="3" style:num-format="1" text:display-levels="3">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-1.27cm" fo:margin-left="1.27cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="4" style:num-format="1" text:display-levels="4">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.524cm" fo:text-indent="-1.524cm" fo:margin-left="1.524cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="5" style:num-format="1" text:display-levels="5">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.778cm" fo:text-indent="-1.778cm" fo:margin-left="1.778cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="6" style:num-format="1" text:display-levels="6">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.032cm" fo:text-indent="-2.032cm" fo:margin-left="2.032cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="7" style:num-format="1" text:display-levels="7">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.286cm" fo:text-indent="-2.286cm" fo:margin-left="2.286cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="8" style:num-format="1" text:display-levels="8">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-2.54cm" fo:margin-left="2.54cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="9" style:num-format="1" text:display-levels="9">
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
    <text:list-style style:name="WW8Num1">
      <text:list-level-style-number text:level="1" style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.762cm" fo:text-indent="-0.762cm" fo:margin-left="0.762cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="2" style:num-format="1" text:display-levels="2">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.016cm" fo:text-indent="-1.016cm" fo:margin-left="1.016cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="3" style:num-format="1" text:display-levels="3">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-1.27cm" fo:margin-left="1.27cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="4" style:num-format="1" text:display-levels="4">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.524cm" fo:text-indent="-1.524cm" fo:margin-left="1.524cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="5" style:num-format="1" text:display-levels="5">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.778cm" fo:text-indent="-1.778cm" fo:margin-left="1.778cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="6" style:num-format="1" text:display-levels="6">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.032cm" fo:text-indent="-2.032cm" fo:margin-left="2.032cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="7" style:num-format="1" text:display-levels="7">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.286cm" fo:text-indent="-2.286cm" fo:margin-left="2.286cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="8" style:num-format="1" text:display-levels="8">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-2.54cm" fo:margin-left="2.54cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="9" style:num-format="1" text:display-levels="9">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.794cm" fo:text-indent="-2.794cm" fo:margin-left="2.794cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="10" style:num-suffix="." style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
    </text:list-style>
    <text:list-style style:name="WW8Num2">
      <text:list-level-style-bullet text:level="1" text:style-name="WW8Num2z0" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.365cm" fo:margin-left="0.365cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-number text:level="2" style:num-format="1" text:display-levels="2">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.016cm" fo:text-indent="-1.016cm" fo:margin-left="1.016cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="3" style:num-format="1" text:display-levels="3">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-1.27cm" fo:margin-left="1.27cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="4" style:num-format="1" text:display-levels="4">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.524cm" fo:text-indent="-1.524cm" fo:margin-left="1.524cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="5" style:num-format="1" text:display-levels="5">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.778cm" fo:text-indent="-1.778cm" fo:margin-left="1.778cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="6" style:num-format="1" text:display-levels="6">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.032cm" fo:text-indent="-2.032cm" fo:margin-left="2.032cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="7" style:num-format="1" text:display-levels="7">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.286cm" fo:text-indent="-2.286cm" fo:margin-left="2.286cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="8" style:num-format="1" text:display-levels="8">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-2.54cm" fo:margin-left="2.54cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="9" style:num-format="1" text:display-levels="9">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.794cm" fo:text-indent="-2.794cm" fo:margin-left="2.794cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="10" style:num-suffix="." style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
    </text:list-style>
    <text:list-style style:name="WW8Num3" text:consecutive-numbering="true">
      <text:list-level-style-bullet text:level="1" text:style-name="WW8Num3z0" style:num-suffix="." text:bullet-char="-">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-0.635cm" fo:margin-left="1.27cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Arial"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="2" text:style-name="WW8Num3z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-0.635cm" fo:margin-left="2.54cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="3" text:style-name="WW8Num3z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.81cm" fo:text-indent="-0.635cm" fo:margin-left="3.81cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="4" text:style-name="WW8Num3z3" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.08cm" fo:text-indent="-0.635cm" fo:margin-left="5.08cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Symbol"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="5" text:style-name="WW8Num3z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.35cm" fo:text-indent="-0.635cm" fo:margin-left="6.35cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="6" text:style-name="WW8Num3z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.62cm" fo:text-indent="-0.635cm" fo:margin-left="7.62cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="7" text:style-name="WW8Num3z3" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="8.89cm" fo:text-indent="-0.635cm" fo:margin-left="8.89cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Symbol"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="8" text:style-name="WW8Num3z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="10.16cm" fo:text-indent="-0.635cm" fo:margin-left="10.16cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="9" text:style-name="WW8Num3z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="11.43cm" fo:text-indent="-0.635cm" fo:margin-left="11.43cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-number text:level="10" style:num-suffix="." style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
    </text:list-style>
    <text:list-style style:name="WW8Num4" text:consecutive-numbering="true">
      <text:list-level-style-bullet text:level="1" text:style-name="WW8Num4z0" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.4cm" fo:text-indent="-0.4cm" fo:margin-left="0.4cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="2" text:style-name="WW8Num4z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-0.635cm" fo:margin-left="2.54cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="3" text:style-name="WW8Num4z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.81cm" fo:text-indent="-0.635cm" fo:margin-left="3.81cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="4" text:style-name="WW8Num4z3" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.08cm" fo:text-indent="-0.635cm" fo:margin-left="5.08cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Symbol"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="5" text:style-name="WW8Num4z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.35cm" fo:text-indent="-0.635cm" fo:margin-left="6.35cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="6" text:style-name="WW8Num4z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.62cm" fo:text-indent="-0.635cm" fo:margin-left="7.62cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="7" text:style-name="WW8Num4z3" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="8.89cm" fo:text-indent="-0.635cm" fo:margin-left="8.89cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Symbol"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="8" text:style-name="WW8Num4z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="10.16cm" fo:text-indent="-0.635cm" fo:margin-left="10.16cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="9" text:style-name="WW8Num4z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="11.43cm" fo:text-indent="-0.635cm" fo:margin-left="11.43cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-number text:level="10" style:num-suffix="." style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
    </text:list-style>
    <text:list-style style:name="WW8Num5" text:consecutive-numbering="true">
      <text:list-level-style-bullet text:level="1" text:style-name="WW8Num5z0" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.63cm" fo:text-indent="-0.365cm" fo:margin-left="1cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="2" text:style-name="WW8Num5z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-0.635cm" fo:margin-left="2.54cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="3" text:style-name="WW8Num5z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.81cm" fo:text-indent="-0.635cm" fo:margin-left="3.81cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="4" text:style-name="WW8Num5z3" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.08cm" fo:text-indent="-0.635cm" fo:margin-left="5.08cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Symbol"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="5" text:style-name="WW8Num5z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.35cm" fo:text-indent="-0.635cm" fo:margin-left="6.35cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="6" text:style-name="WW8Num5z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.62cm" fo:text-indent="-0.635cm" fo:margin-left="7.62cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="7" text:style-name="WW8Num5z3" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="8.89cm" fo:text-indent="-0.635cm" fo:margin-left="8.89cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Symbol"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="8" text:style-name="WW8Num5z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="10.16cm" fo:text-indent="-0.635cm" fo:margin-left="10.16cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="9" text:style-name="WW8Num5z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="11.43cm" fo:text-indent="-0.635cm" fo:margin-left="11.43cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-number text:level="10" style:num-suffix="." style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
    </text:list-style>
    <text:list-style style:name="WW8Num6" text:consecutive-numbering="true">
      <text:list-level-style-bullet text:level="1" text:style-name="WW8Num6z0" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="1.27cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Symbol"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="2" text:style-name="WW8Num6z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="2.54cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="3" text:style-name="WW8Num6z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="3.81cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="4" text:style-name="WW8Num6z0" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="5.08cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Symbol"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="5" text:style-name="WW8Num6z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="6.35cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="6" text:style-name="WW8Num6z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="7.62cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="7" text:style-name="WW8Num6z0" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="8.89cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Symbol"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="8" text:style-name="WW8Num6z1" style:num-suffix="." text:bullet-char="o">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="10.16cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Courier New1"/>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="9" text:style-name="WW8Num6z2" style:num-suffix="." text:bullet-char="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="11.43cm"/>
        </style:list-level-properties>
        <style:text-properties style:font-name="Wingdings"/>
      </text:list-level-style-bullet>
      <text:list-level-style-number text:level="10" style:num-suffix="." style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
    </text:list-style>
    <text:list-style style:name="WW8Num7" text:consecutive-numbering="true">
      <text:list-level-style-number text:level="1" text:style-name="WW8Num7z0" style:num-suffix="." style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="1.27cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="2" style:num-suffix="." style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="2.54cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="3" style:num-suffix="." style:num-format="i">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" fo:text-align="end">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.318cm" fo:margin-left="3.81cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="4" style:num-suffix="." style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="5.08cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="5" style:num-suffix="." style:num-format="a" style:num-letter-sync="true">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="6.35cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="6" style:num-suffix="." style:num-format="i">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" fo:text-align="end">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.318cm" fo:margin-left="7.62cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="7" style:num-suffix="." style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="8.89cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="8" style:num-suffix="." style:num-format="a" style:num-letter-sync="true">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.635cm" fo:margin-left="10.16cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="9" style:num-suffix="." style:num-format="i">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" fo:text-align="end">
          <style:list-level-label-alignment text:label-followed-by="listtab" fo:text-indent="-0.318cm" fo:margin-left="11.43cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
      <text:list-level-style-number text:level="10" style:num-suffix="." style:num-format="1">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm"/>
        </style:list-level-properties>
      </text:list-level-style-number>
    </text:list-style>
    <text:notes-configuration text:note-class="footnote" text:citation-style-name="Footnote_20_Symbol" text:citation-body-style-name="Footnote_20_anchor" style:num-format="1" text:start-value="0" text:footnotes-position="page" text:start-numbering-at="document" text:increment="0"/>
    <text:notes-configuration text:note-class="endnote" style:num-format="i" text:start-value="0"/>
    <text:linenumbering-configuration text:number-lines="false" text:offset="0.499cm" style:num-format="1" text:number-position="left" text:increment="5"/>
    <style:default-page-layout>
      <style:page-layout-properties style:layout-grid-standard-mode="true"/>
    </style:default-page-layout>
  </office:styles>
  <office:automatic-styles>
    <style:style style:name="MP1" style:family="paragraph" style:parent-style-name="Header">
      <style:text-properties fo:language="de" fo:country="AT" style:language-asian="none" style:country-asian="none"/>
    </style:style>
    <style:style style:name="MP2" style:family="paragraph" style:parent-style-name="Footer">
      <style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="MT1" style:family="text"/>
    <style:style style:name="MT2" style:family="text">
      <style:text-properties fo:font-size="8pt" fo:background-color="#ffff00" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="MT3" style:family="text">
      <style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="MT4" style:family="text">
      <style:text-properties fo:font-size="8pt" fo:font-weight="bold" fo:background-color="#ffff00" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="Mfr1" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties fo:margin-left="0.319cm" fo:margin-right="0.319cm" style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:padding="0.026cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
    </style:style>
    <style:page-layout style:name="Mpm1">
      <style:page-layout-properties fo:page-width="21.001cm" fo:page-height="29.7cm" style:num-format="1" style:print-orientation="portrait" fo:margin-top="1.27cm" fo:margin-bottom="1.229cm" fo:margin-left="2.501cm" fo:margin-right="2.501cm" style:writing-mode="lr-tb" style:layout-grid-color="#c0c0c0" style:layout-grid-lines="42" style:layout-grid-base-height="0.635cm" style:layout-grid-ruby-height="0cm" style:layout-grid-mode="none" style:layout-grid-ruby-below="false" style:layout-grid-print="false" style:layout-grid-display="false" style:layout-grid-base-width="0.423cm" style:layout-grid-snap-to="true" style:layout-grid-snap-to-characters="true" style:footnote-max-height="0cm">
        <style:footnote-sep style:width="0.018cm" style:distance-before-sep="0.101cm" style:distance-after-sep="0.101cm" style:line-style="solid" style:adjustment="left" style:rel-width="25%" style:color="#000000"/>
      </style:page-layout-properties>
      <style:header-style>
        <style:header-footer-properties fo:min-height="3.48cm" fo:margin-bottom="3.381cm" style:dynamic-spacing="true"/>
      </style:header-style>
      <style:footer-style>
        <style:header-footer-properties fo:min-height="0.771cm" fo:margin-top="0.672cm" style:dynamic-spacing="true"/>
      </style:footer-style>
    </style:page-layout>
  </office:automatic-styles>
  <office:master-styles>
    <style:master-page style:name="Standard" style:page-layout-name="Mpm1">
      <style:header>
        <text:p text:style-name="MP1">
          <draw:frame draw:style-name="Mfr1" draw:name="Grafik1" text:anchor-type="char" svg:x="13.26cm" svg:y="-0.24cm" svg:width="4.192cm" svg:height="2.17cm" draw:z-index="5">
            <draw:image xlink:href="Pictures/tw_logo.png" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
          </draw:frame>
        </text:p>
      </style:header>
      <style:footer>
        <text:p text:style-name="MP2">Ausbildungsvertrag 2019/20<text:tab/><text:tab/><text:span text:style-name="Page_20_Number"><text:page-number text:select-page="current">6</text:page-number></text:span></text:p>
        <text:p text:style-name="Footer">
          <text:span text:style-name="Page_20_Number">
            <text:span text:style-name="MT3"><xsl:value-of select="studiengang_typ"/>-Studiengang</text:span>
          </text:span>

          <text:span text:style-name="Page_20_Number">
            <text:span text:style-name="MT3"><xsl:value-of select="studiengang"/></text:span>
          </text:span>
        </text:p>
      </style:footer>
    </style:master-page>
    <style:master-page style:name="First_20_Page" style:display-name="First Page" style:page-layout-name="Mpm1" style:next-style-name="Standard">
      <style:header>
        <text:p text:style-name="MP1">
          <draw:frame draw:style-name="Mfr1" draw:name="Grafik2" text:anchor-type="char" svg:x="13.26cm" svg:y="-0.24cm" svg:width="4.192cm" svg:height="2.17cm" draw:z-index="5">
            <draw:image xlink:href="Pictures/tw_logo.png" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
          </draw:frame>
        </text:p>
      </style:header>
      <style:footer>
        <text:p text:style-name="MP2">Ausbildungsvertrag 2019/20<text:tab/><text:tab/><text:span text:style-name="Page_20_Number"><text:page-number text:select-page="current">1</text:page-number></text:span></text:p>
        <text:p text:style-name="Footer">
          <text:span text:style-name="Page_20_Number">
            <text:span text:style-name="MT3"><xsl:value-of select="studiengang_typ"/>-Studiengang</text:span>
          </text:span>

          <text:span text:style-name="Page_20_Number">
            <text:span text:style-name="MT3"><xsl:value-of select="studiengang"/></text:span>
          </text:span>
        </text:p>
      </style:footer>
    </style:master-page>
  </office:master-styles>
</office:document-styles>
</xsl:template>

</xsl:stylesheet>