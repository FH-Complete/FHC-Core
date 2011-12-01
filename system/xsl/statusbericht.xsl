<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="xml" version="1.0" indent="yes" />


<xsl:template match="statusbericht">
<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
  <office:scripts/>
  <office:font-face-decls>
    <style:font-face style:name="Sans" svg:font-family="Sans"/>
    <style:font-face style:name="Arial2" svg:font-family="Arial" style:font-family-generic="swiss"/>
    <style:font-face style:name="Arial" svg:font-family="Arial" style:font-adornments="Bold" style:font-family-generic="swiss"/>
    <style:font-face style:name="Arial1" svg:font-family="Arial" style:font-adornments="Regular" style:font-family-generic="swiss"/>
    <style:font-face style:name="Calibri" svg:font-family="Calibri" style:font-family-generic="swiss"/>
    <style:font-face style:name="Cambria" svg:font-family="Cambria" style:font-family-generic="swiss"/>
    <style:font-face style:name="Liberation Sans" svg:font-family="'Liberation Sans'" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="DejaVu Sans" svg:font-family="'DejaVu Sans'" style:font-family-generic="system" style:font-pitch="variable"/>
    <style:font-face style:name="DejaVu Sans Light" svg:font-family="'DejaVu Sans Light'" style:font-family-generic="system" style:font-pitch="variable"/>
  </office:font-face-decls>
  <office:automatic-styles>
    <style:style style:name="co1" style:family="table-column">
      <style:table-column-properties fo:break-before="auto" style:column-width="4.768cm"/>
    </style:style>
    <style:style style:name="co2" style:family="table-column">
      <style:table-column-properties fo:break-before="auto" style:column-width="4.449cm"/>
    </style:style>
    <style:style style:name="co3" style:family="table-column">
      <style:table-column-properties fo:break-before="auto" style:column-width="10.305cm"/>
    </style:style>
    <style:style style:name="co4" style:family="table-column">
      <style:table-column-properties fo:break-before="auto" style:column-width="15.201cm"/>
    </style:style>
    <style:style style:name="co5" style:family="table-column">
      <style:table-column-properties fo:break-before="page" style:column-width="3.552cm"/>
    </style:style>
    <style:style style:name="co6" style:family="table-column">
      <style:table-column-properties fo:break-before="auto" style:column-width="10.753cm"/>
    </style:style>
    <style:style style:name="co7" style:family="table-column">
      <style:table-column-properties fo:break-before="auto" style:column-width="2.048cm"/>
    </style:style>
    <style:style style:name="co8" style:family="table-column">
      <style:table-column-properties fo:break-before="auto" style:column-width="3.2cm"/>
    </style:style>
    <style:style style:name="ro1" style:family="table-row">
      <style:table-row-properties style:row-height="0.452cm" fo:break-before="auto" style:use-optimal-row-height="true"/>
    </style:style>
    <style:style style:name="ro2" style:family="table-row">
      <style:table-row-properties style:row-height="1.316cm" fo:break-before="auto" style:use-optimal-row-height="false"/>
    </style:style>
    <style:style style:name="ro3" style:family="table-row">
      <style:table-row-properties style:row-height="0.841cm" fo:break-before="auto" style:use-optimal-row-height="false"/>
    </style:style>
    <style:style style:name="ro4" style:family="table-row">
      <style:table-row-properties style:row-height="0.926cm" fo:break-before="auto" style:use-optimal-row-height="false"/>
    </style:style>
    <style:style style:name="ro5" style:family="table-row">
      <style:table-row-properties style:row-height="1.27cm" fo:break-before="auto" style:use-optimal-row-height="false"/>
    </style:style>
    <style:style style:name="ro6" style:family="table-row">
      <style:table-row-properties style:row-height="0.529cm" fo:break-before="auto" style:use-optimal-row-height="false"/>
    </style:style>
    <style:style style:name="ro7" style:family="table-row">
      <style:table-row-properties style:row-height="0.556cm" fo:break-before="auto" style:use-optimal-row-height="false"/>
    </style:style>
    <style:style style:name="ro8" style:family="table-row">
      <style:table-row-properties style:row-height="0.609cm" fo:break-before="auto" style:use-optimal-row-height="false"/>
    </style:style>
    <style:style style:name="ro9" style:family="table-row">
      <style:table-row-properties style:row-height="0.894cm" fo:break-before="auto" style:use-optimal-row-height="false"/>
    </style:style>
    <style:style style:name="ro10" style:family="table-row">
      <style:table-row-properties style:row-height="0.709cm" fo:break-before="auto" style:use-optimal-row-height="false"/>
    </style:style>
    <style:style style:name="ro11" style:family="table-row">
      <style:table-row-properties style:row-height="0.762cm" fo:break-before="auto" style:use-optimal-row-height="false"/>
    </style:style>
    <style:style style:name="ro12" style:family="table-row">
      <style:table-row-properties style:row-height="0.446cm" fo:break-before="auto" style:use-optimal-row-height="false"/>
    </style:style>
    <style:style style:name="ta1" style:family="table" style:master-page-name="PageStyle_5f_Projekstatus_20_Dokuvorlage">
      <style:table-properties table:display="true" style:writing-mode="lr-tb"/>
    </style:style>
    <number:percentage-style style:name="N11">
      <number:number number:decimal-places="2" number:min-integer-digits="1"/>
      <number:text>%</number:text>
    </number:percentage-style>
    <style:style style:name="ce1" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N0">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:cell-protect="protected" style:print-content="true" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="value-type" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="no-wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce2" style:family="table-cell" style:parent-style-name="Überschrift_20_1" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#ffffff" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" style:direction="ltr" style:rotation-angle="0" style:shrink-to-fit="false" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
    </style:style>
    <style:style style:name="ce3" style:family="table-cell" style:parent-style-name="Überschrift_20_2" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#ffffff" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" style:direction="ltr" style:rotation-angle="0" style:shrink-to-fit="false" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
    </style:style>
    <style:style style:name="ce4" style:family="table-cell" style:parent-style-name="Überschrift_20_4" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:shrink-to-fit="false" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
    </style:style>
    <style:style style:name="ce5" style:family="table-cell" style:parent-style-name="Überschrift_20_4" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:shrink-to-fit="false" style:vertical-align="top"/>
      <style:paragraph-properties fo:text-align="start" fo:margin-left="0cm" style:writing-mode="page"/>
    </style:style>
    <style:style style:name="ce6" style:family="table-cell" style:parent-style-name="Überschrift_20_4" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="none" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border-left="0.99pt solid #000000" style:direction="ltr" fo:border-right="0.99pt solid #000000" style:rotation-angle="0" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="top"/>
      <style:paragraph-properties fo:text-align="start" fo:margin-left="0cm" style:writing-mode="page"/>
    </style:style>
    <style:style style:name="ce7" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="0.99pt solid #000000" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border-left="0.99pt solid #000000" style:direction="ltr" fo:border-right="0.99pt solid #000000" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="none" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce8" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce9" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="0.06pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce10" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#969696" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce11" style:family="table-cell" style:parent-style-name="Überschrift_20_4" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:shrink-to-fit="true"/>
      <style:paragraph-properties fo:text-align="start" fo:margin-left="0cm" style:writing-mode="page"/>
    </style:style>
    <style:style style:name="ce12" style:family="table-cell" style:parent-style-name="Überschrift_20_4">
      <style:table-cell-properties fo:background-color="#c0c0c0"/>
    </style:style>
    <style:style style:name="ce13" style:family="table-cell" style:parent-style-name="Überschrift_20_4">
      <style:table-cell-properties fo:border-bottom="none" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" fo:border-left="0.06pt solid #000000" fo:border-right="0.06pt solid #000000" fo:border-top="0.06pt solid #000000"/>
    </style:style>
    <style:style style:name="ce14" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties fo:border-bottom="0.06pt solid #000000" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" fo:border-left="0.06pt solid #000000" fo:border-right="0.06pt solid #000000" style:rotation-align="none" fo:border-top="none"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce15" style:family="table-cell" style:parent-style-name="Überschrift_20_4">
      <style:table-cell-properties fo:border-bottom="none" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" fo:border-left="none" fo:border-right="0.99pt solid #000000" fo:border-top="0.99pt solid #000000"/>
    </style:style>
    <style:style style:name="ce16" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties fo:background-color="#c0c0c0"/>
    </style:style>
    <style:style style:name="ce17" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="no-wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce18" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="0.99pt solid #000000" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border-left="0.99pt solid #000000" style:direction="ltr" fo:border-right="none" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce19" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce20" style:family="table-cell" style:parent-style-name="Überschrift_20_4" style:data-style-name="N11">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:shrink-to-fit="false"/>
      <style:paragraph-properties fo:text-align="center" fo:margin-left="0cm" style:writing-mode="page"/>
    </style:style>
    <style:style style:name="ce21" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties fo:background-color="#c7c7c7" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" fo:border="0.99pt solid #000000" style:rotation-align="none"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce22" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce23" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="value-type" style:repeat-content="false" fo:wrap-option="wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce24" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0.353cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="8pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="8pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="8pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce25" style:family="table-cell" style:parent-style-name="Default">
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce26" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#ffffff" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="12pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="12pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="12pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce27" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#ffffff" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="12pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="12pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="12pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce28" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#ffffff" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce29" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="none" fo:background-color="#ffffff" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border-left="0.99pt solid #000000" style:direction="ltr" fo:border-right="0.99pt solid #000000" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce30" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="none" fo:background-color="#ffffff" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border-left="none" style:direction="ltr" fo:border-right="0.99pt solid #000000" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce31" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="0.99pt solid #000000" fo:background-color="#ffffff" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border-left="none" style:direction="ltr" fo:border-right="0.99pt solid #000000" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="none" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce32" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce33" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce34" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#969696" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce35" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N11">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="0.99pt solid #000000" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="no-wrap" fo:border-left="0.99pt solid #000000" style:direction="ltr" fo:border-right="none" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce36" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N109">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="0.99pt solid #000000" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="no-wrap" fo:border-left="0.99pt solid #000000" style:direction="ltr" fo:border-right="none" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce37" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:background-color="transparent" fo:border="0.99pt solid #000000" style:rotation-align="none"/>
      <style:paragraph-properties fo:text-align="center" fo:margin-left="0cm"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="9pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="9pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="9pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce38" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="wrap" fo:border="0.06pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce39" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="none" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="no-wrap" fo:border-left="0.99pt solid #000000" style:direction="ltr" fo:border-right="none" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="none" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce40" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="wrap" fo:border="0.06pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce41" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce42" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="no-wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="end" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce43" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:diagonal-bl-tr="none" style:diagonal-tl-br="none" fo:border="none" style:rotation-align="none"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce44" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="0.99pt solid #000000" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border-left="none" style:direction="ltr" fo:border-right="none" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce45" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce46" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce47" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:diagonal-bl-tr="none" style:diagonal-tl-br="none" fo:border="0.99pt solid #000000" style:rotation-align="none"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce48" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce49" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:diagonal-bl-tr="none" style:diagonal-tl-br="none" fo:border="none" style:rotation-align="none"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="8pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="8pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="8pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce50" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="none" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border-left="none" style:direction="ltr" fo:border-right="none" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce51" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="0.99pt solid #000000" fo:background-color="#ffffff" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border-left="none" style:direction="ltr" fo:border-right="none" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="none" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce52" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#969696" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="0.06pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#ffffff" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce53" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:text-align-source="fix" style:repeat-content="false" fo:border="0.06pt solid #000000" style:vertical-align="middle"/>
      <style:paragraph-properties fo:text-align="start" fo:margin-left="0cm"/>
    </style:style>
    <style:style style:name="ce54" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties fo:border="none"/>
    </style:style>
    <style:style style:name="ce55" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce56" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="0.99pt solid #000000" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border-left="none" style:direction="ltr" fo:border-right="none" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce57" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce58" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce59" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce60" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce61" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#ffffff" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce62" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#ffffff" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce63" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#ffffff" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="0.06pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce64" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:background-color="#ffffff" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:shrink-to-fit="false" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:text-outline="false" style:text-line-through-style="none" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce65" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties fo:border-bottom="none" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" fo:wrap-option="wrap" fo:border-left="0.99pt solid #000000" fo:border-right="0.99pt solid #000000" style:rotation-align="none" fo:border-top="0.99pt solid #000000"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce66" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="none" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border-left="none" style:direction="ltr" fo:border-right="0.99pt solid #000000" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="top" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce67" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="none" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border-left="none" style:direction="ltr" fo:border-right="2.49pt solid #000000" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="none" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce68" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border="0.06pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce69" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="none" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border-left="none" style:direction="ltr" fo:border-right="0.99pt solid #000000" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" fo:border-top="0.99pt solid #000000" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce70" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="none" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border-left="none" style:direction="ltr" fo:border-right="0.99pt solid #000000" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" fo:border-top="none" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce71" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="0.99pt solid #000000" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border-left="none" style:direction="ltr" fo:border-right="0.99pt solid #000000" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" fo:border-top="none" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce72" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border="0.99pt solid #000000" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce73" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N166">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="true" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce74" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="0.99pt solid #000000" fo:background-color="#c0c0c0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border-left="none" style:direction="ltr" fo:border-right="0.99pt solid #000000" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="start" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce75" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" fo:border-bottom="0.99pt solid #000000" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="fix" style:repeat-content="false" fo:wrap-option="no-wrap" fo:border-left="none" style:direction="ltr" fo:border-right="0.99pt solid #000000" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" fo:border-top="0.99pt solid #000000" style:vertical-align="automatic" style:vertical-justify="auto"/>
      <style:paragraph-properties fo:text-align="center" css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="10pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="10pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce76" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="value-type" style:repeat-content="false" fo:background-color="transparent" fo:wrap-option="wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="9pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="bold" style:font-size-asian="9pt" style:font-style-asian="normal" style:font-weight-asian="bold" style:font-size-complex="9pt" style:font-style-complex="normal" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="ce77" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="value-type" style:repeat-content="false" fo:wrap-option="wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000000" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="9pt" fo:font-style="normal" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="9pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="9pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="ce78" style:family="table-cell" style:parent-style-name="Default">
      <style:table-cell-properties style:glyph-orientation-vertical="0" style:diagonal-bl-tr="none" style:diagonal-tl-br="none" style:text-align-source="value-type" style:repeat-content="false" fo:wrap-option="wrap" fo:border="none" style:direction="ltr" style:rotation-angle="0" style:rotation-align="none" style:shrink-to-fit="false" style:vertical-align="middle" style:vertical-justify="auto"/>
      <style:paragraph-properties css3t:text-justify="auto" fo:margin-left="0cm" style:writing-mode="page"/>
      <style:text-properties fo:color="#000080" style:text-outline="false" style:text-line-through-style="none" style:font-name="Arial2" fo:font-size="9pt" fo:font-style="italic" fo:text-shadow="none" style:text-underline-style="none" fo:font-weight="normal" style:font-size-asian="9pt" style:font-style-asian="italic" style:font-weight-asian="normal" style:font-size-complex="9pt" style:font-style-complex="italic" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="gr1" style:family="graphic">
      <style:graphic-properties draw:stroke="none" draw:fill="none" draw:fill-color="#ffffff" draw:textarea-horizontal-align="justify" draw:textarea-vertical-align="top" draw:auto-grow-height="false" fo:padding-top="0.13cm" fo:padding-bottom="0.13cm" fo:padding-left="0.25cm" fo:padding-right="0.25cm" fo:wrap-option="wrap" draw:color-mode="standard" draw:luminance="0%" draw:contrast="0%" draw:gamma="100%" draw:red="0%" draw:green="0%" draw:blue="0%" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:image-opacity="100%" style:mirror="none"/>
    </style:style>
    <style:style style:name="P1" style:family="paragraph">
      <style:paragraph-properties fo:text-align="center" style:writing-mode="lr-tb"/>
    </style:style>
  </office:automatic-styles>
  <office:body>
    <office:spreadsheet>
      <table:calculation-settings table:case-sensitive="false" table:automatic-find-labels="false" table:use-regular-expressions="false">
        <table:iteration table:status="enable"/>
      </table:calculation-settings>
      <table:table table:name="Projektstatusbericht" table:style-name="ta1" table:print-ranges="Projektstatusbericht.A2:Projektstatusbericht.D37">
        <office:forms form:automatic-focus="false" form:apply-design-mode="false"/>
        <table:table-column table:style-name="co1" table:default-cell-style-name="ce1"/>
        <table:table-column table:style-name="co2" table:default-cell-style-name="ce25"/>
        <table:table-column table:style-name="co3" table:default-cell-style-name="ce25"/>
        <table:table-column table:style-name="co4" table:default-cell-style-name="ce25"/>
        <table:table-column table:style-name="co5" table:default-cell-style-name="ce25"/>
        <table:table-column table:style-name="co6" table:default-cell-style-name="ce25"/>
        <table:table-column table:style-name="co7" table:default-cell-style-name="ce25"/>
        <table:table-column table:style-name="co8" table:default-cell-style-name="ce25"/>
        <table:table-column table:style-name="co7" table:number-columns-repeated="249" table:default-cell-style-name="ce25"/>
        <table:table-column table:style-name="co7" table:number-columns-repeated="767" table:default-cell-style-name="Default"/>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:number-columns-repeated="3"/>
          <table:table-cell>
            <draw:frame table:end-cell-address="Projektstatusbericht.D3" table:end-x="14.99cm" table:end-y="0.431cm" draw:z-index="0" draw:name="Picture 2" draw:style-name="gr1" draw:text-style-name="P1" svg:width="5.587cm" svg:height="2.198cm" svg:x="9.476cm" svg:y="0cm">
              <draw:image xlink:href="Pictures/1000000000000F43000006D85BD8023A.jpg" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad">
                <text:p/>
              </draw:image>
            </draw:frame>
          </table:table-cell>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro2">
          <table:table-cell table:style-name="ce2" office:value-type="string">
            <text:p>Statusbericht</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce26" table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce61"/>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro3">
          <table:table-cell table:style-name="ce3" office:value-type="string">
            <text:p>Projektstatus vom <xsl:value-of select="datum"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce27" table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce62"/>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro4">
          <table:table-cell table:style-name="ce4" office:value-type="string">
            <text:p>Projekttitel</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce28" office:value-type="string">
            <text:p><xsl:value-of select="projekt_titel"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce5" office:value-type="string">
            <text:p>Projektleiter</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce63" office:value-type="string">
            <text:p></text:p>
          </table:table-cell>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:style-name="ce76" table:number-columns-repeated="5"/>
          <table:table-cell table:number-columns-repeated="1012"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce5" office:value-type="string">
            <text:p>Kürzel</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce28" office:value-type="string">
            <text:p><xsl:value-of select="projekt_kuerzel"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce5" office:value-type="string">
            <text:p>Auftraggeber </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce64" office:value-type="string">
            <text:p></text:p>
          </table:table-cell>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:style-name="ce77"/>
          <table:table-cell table:style-name="ce78" table:number-columns-repeated="4"/>
          <table:table-cell table:number-columns-repeated="1012"/>
        </table:table-row>
        <table:table-row table:style-name="ro5">
          <table:table-cell table:style-name="ce6" office:value-type="string">
            <text:p>Budget</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce29" office:value-type="string">
            <text:p><xsl:value-of select="projekt_budget"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string">
            <text:p>Kernteam</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce65" office:value-type="string">
            <text:p><xsl:apply-templates select="ressourcen"/></text:p>
          </table:table-cell>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:style-name="ce78" table:number-columns-repeated="5"/>
          <table:table-cell table:number-columns-repeated="1012"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce6" office:value-type="string">
            <text:p>Projektziele</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce30" office:value-type="string" table:number-columns-spanned="3" table:number-rows-spanned="1">
            <text:p><xsl:value-of select="projekt_ziele"/></text:p>
          </table:table-cell>
          <table:covered-table-cell table:style-name="ce50"/>
          <table:covered-table-cell table:style-name="ce66"/>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:style-name="ce78" table:number-columns-repeated="5"/>
          <table:table-cell table:number-columns-repeated="1012"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce7"/>
          <table:table-cell table:style-name="ce31" table:number-columns-spanned="3" table:number-rows-spanned="1"/>
          <table:covered-table-cell table:style-name="ce51"/>
          <table:covered-table-cell table:style-name="ce31"/>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:style-name="ce78" table:number-columns-repeated="5"/>
          <table:table-cell table:number-columns-repeated="1012"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce8"/>
          <table:table-cell table:style-name="ce32" table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce31"/>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:style-name="ce78" table:number-columns-repeated="5"/>
          <table:table-cell table:number-columns-repeated="1012"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce9" office:value-type="string" table:number-columns-spanned="4" table:number-rows-spanned="1">
            <text:p>Zusammenfassung</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="2" table:style-name="ce33"/>
          <table:covered-table-cell table:style-name="ce67"/>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:style-name="ce78" table:number-columns-repeated="5"/>
          <table:table-cell table:number-columns-repeated="1012"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce10" office:value-type="string">
            <text:p>Bereich</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce34" office:value-type="string">
            <text:p>Status</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce52" office:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Bemerkung</text:p>
          </table:table-cell>
          <table:covered-table-cell table:style-name="ce34"/>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43" table:number-columns-repeated="6"/>
          <table:table-cell table:number-columns-repeated="1012"/>
        </table:table-row>
        <table:table-row table:style-name="ro6">
          <table:table-cell table:style-name="ce11" office:value-type="string">
            <text:p>Gesamtprojekt</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce35" office:value-type="string" office:value="0">
            <text:p><xsl:value-of select="projekt_fortschritt"/>%</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce53" office:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p></text:p>
          </table:table-cell>
          <table:covered-table-cell table:style-name="ce68"/>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43" table:number-columns-repeated="6"/>
          <table:table-cell table:number-columns-repeated="245"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro7">
          <table:table-cell table:style-name="ce11" office:value-type="string">
            <text:p>Kosten</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce36" office:value-type="string" office:value="0">
            <text:p>€ <xsl:value-of select="projekt_kosten"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce53" office:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p></text:p>
          </table:table-cell>
          <table:covered-table-cell table:style-name="ce68"/>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43" table:number-columns-repeated="6"/>
          <table:table-cell table:number-columns-repeated="245"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro8">
          <table:table-cell table:style-name="ce12" office:value-type="string">
            <text:p>Qualität</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce37" office:value-type="string">
            <text:p>Top/Hoch/Mittel/Niedrig</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce53" office:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p></text:p>
          </table:table-cell>
          <table:covered-table-cell table:style-name="ce68"/>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43" table:number-columns-repeated="6"/>
          <table:table-cell table:number-columns-repeated="245"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce13" office:value-type="string">
            <text:p>Risiko/Probleme</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce38" office:value-type="string" table:number-columns-spanned="3" table:number-rows-spanned="2">
            <text:p></text:p>
          </table:table-cell>
          <table:covered-table-cell table:style-name="Default"/>
          <table:covered-table-cell table:style-name="ce69"/>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43" table:number-columns-repeated="6"/>
          <table:table-cell table:number-columns-repeated="245"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce14"/>
          <table:covered-table-cell table:style-name="ce39"/>
          <table:covered-table-cell table:style-name="Default"/>
          <table:covered-table-cell table:style-name="ce70"/>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce15" office:value-type="string">
            <text:p>Nächste Schritte</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce40" office:value-type="string" table:number-columns-spanned="3" table:number-rows-spanned="2">
            <text:p><xsl:apply-templates select="naechste_schritte"/></text:p>
          </table:table-cell>
          <table:covered-table-cell table:style-name="Default"/>
          <table:covered-table-cell table:style-name="ce71"/>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro9">
          <table:table-cell table:style-name="ce16"/>
          <table:covered-table-cell table:style-name="ce41"/>
          <table:covered-table-cell table:style-name="Default"/>
          <table:covered-table-cell table:style-name="ce72"/>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro6">
          <table:table-cell table:style-name="ce11" office:value-type="string">
            <text:p>Personentage</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string">
            <text:p></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce54"/>
          <table:table-cell table:style-name="ce73"/>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce17"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:style-name="ce55" table:number-columns-spanned="2" table:number-rows-spanned="1"/>
          <table:covered-table-cell table:style-name="ce55"/>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro6">
          <table:table-cell table:style-name="ce18" office:value-type="string">
            <text:p>Status der aktuellen Phase</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce44"/>
          <table:table-cell table:style-name="ce56"/>
          <table:table-cell table:style-name="ce74"/>
          <table:table-cell table:number-columns-repeated="1020"/>
        </table:table-row>
        <table:table-row table:style-name="ro6">
          <table:table-cell table:style-name="ce10" office:value-type="string">
            <text:p>Phase</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce10" office:value-type="string">
            <text:p>Fortschritt</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce34" office:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Bemerkung</text:p>
          </table:table-cell>
          <table:covered-table-cell table:style-name="ce34"/>
          <table:table-cell table:number-columns-repeated="1020"/>
        </table:table-row>
        <table:table-row table:style-name="ro6" table:visibility="collapse">
          <table:table-cell table:style-name="ce19"/>
          <table:table-cell table:style-name="ce45"/>
          <table:table-cell table:style-name="ce57" table:number-columns-spanned="2" table:number-rows-spanned="1"/>
          <table:covered-table-cell table:style-name="ce57"/>
          <table:table-cell table:number-columns-repeated="1020"/>
        </table:table-row>
	<xsl:apply-templates select="projektphasen"/>
        <table:table-row table:style-name="ro10">
          <table:table-cell table:style-name="ce19"/>
          <table:table-cell table:style-name="ce46"/>
          <table:table-cell table:style-name="ce57" table:number-columns-spanned="2" table:number-rows-spanned="1"/>
          <table:covered-table-cell table:style-name="ce57"/>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro10">
          <table:table-cell table:style-name="ce19"/>
          <table:table-cell table:style-name="ce47"/>
          <table:table-cell table:style-name="ce58" table:number-columns-spanned="2" table:number-rows-spanned="1"/>
          <table:covered-table-cell table:style-name="ce75"/>
          <table:table-cell table:number-columns-repeated="1020"/>
        </table:table-row>
        <table:table-row table:style-name="ro10">
          <table:table-cell table:style-name="ce21"/>
          <table:table-cell table:style-name="ce48"/>
          <table:table-cell table:style-name="ce59" table:number-columns-spanned="2" table:number-rows-spanned="1"/>
          <table:covered-table-cell table:style-name="ce59"/>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro11">
          <table:table-cell table:style-name="ce22" table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce60" table:number-columns-spanned="2" table:number-rows-spanned="1"/>
          <table:covered-table-cell table:style-name="ce60"/>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
        <table:table-row table:style-name="ro12">
          <table:table-cell table:style-name="ce23" table:number-columns-repeated="4"/>
          <table:table-cell table:number-columns-repeated="1020"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce24" office:value-type="string">
            <text:p>Welche Aufgaben sind bereits erledigt?</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce49" table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:number-columns-repeated="1020"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce24" office:value-type="string">
            <text:p>Welche Schwierigkeiten, Probleme sind seit der letzten Besprechung aufgetreten?</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce49" table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:number-columns-repeated="1020"/>
        </table:table-row>
        <table:table-row table:style-name="ro6">
          <table:table-cell table:style-name="ce24" office:value-type="string">
            <text:p>Welche Schwierigkeiten, Probleme konnten seit der letzten Besprechung gelöst werden?</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce49" table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:number-columns-repeated="1020"/>
        </table:table-row>
        <table:table-row table:style-name="ro6">
          <table:table-cell table:style-name="ce24" office:value-type="string">
            <text:p>Welcher Aufwand ist bis zum Erreichen noch zu erledigen?</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce49" table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:number-columns-repeated="1020"/>
        </table:table-row>
        <table:table-row table:style-name="ro6">
          <table:table-cell table:style-name="ce24" office:value-type="string">
            <text:p>Gibt es Terminverschiebungen?</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce49" table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:number-columns-repeated="1020"/>
        </table:table-row>
        <table:table-row table:style-name="ro6">
          <table:table-cell table:style-name="ce25"/>
          <table:table-cell table:number-columns-repeated="2"/>
          <table:table-cell table:style-name="ce43"/>
          <table:table-cell table:number-columns-repeated="1020"/>
        </table:table-row>
        <table:table-row table:style-name="ro6" table:number-rows-repeated="4">
          <table:table-cell table:style-name="ce25"/>
          <table:table-cell table:number-columns-repeated="1023"/>
        </table:table-row>
        <table:table-row table:style-name="ro6" table:number-rows-repeated="2">
          <table:table-cell table:number-columns-repeated="1024"/>
        </table:table-row>
        <table:table-row table:style-name="ro1" table:number-rows-repeated="1048531">
          <table:table-cell table:number-columns-repeated="1024"/>
        </table:table-row>
        <table:table-row table:style-name="ro1">
          <table:table-cell table:number-columns-repeated="1024"/>
        </table:table-row>
      </table:table>
      <table:named-expressions>
        <table:named-expression table:name="Excel_BuiltIn_Print_Area_1" table:base-cell-address="$Projektstatusbericht.$A$1" table:expression=""/>
        <table:named-range table:name="Excel_BuiltIn_Print_Area_2" table:base-cell-address="$Projektstatusbericht.$A$1" table:cell-range-address="$Projektstatusbericht.$A$2:.$D$37" table:range-usable-as="print-range"/>
        <table:named-expression table:name="Excel_BuiltIn_Print_Area_3" table:base-cell-address="$Projektstatusbericht.$A$1" table:expression="$#REF!.$A$1:$L$38"/>
        <table:named-expression table:name="Excel_BuiltIn_Sheet_Title_1" table:base-cell-address="$Projektstatusbericht.$A$1" table:expression="&quot;Projekte&quot;"/>
        <table:named-expression table:name="Excel_BuiltIn_Sheet_Title_2" table:base-cell-address="$Projektstatusbericht.$A$1" table:expression="&quot;Blatt2&quot;"/>
        <table:named-expression table:name="Excel_BuiltIn_Sheet_Title_3" table:base-cell-address="$Projektstatusbericht.$A$1" table:expression="&quot;Blatt3&quot;"/>
        <table:named-expression table:name="_Toc163384816_4" table:base-cell-address="$Projektstatusbericht.$A$1" table:expression="$#REF!.$A$1"/>
      </table:named-expressions>
    </office:spreadsheet>
  </office:body>
</office:document-content>
</xsl:template>
	
	<xsl:template match="ressourcen">
		<xsl:apply-templates select="ressource"/>
	</xsl:template>		
	<xsl:template match="ressource">
		<xsl:value-of select="bezeichnung"/><xsl:text>, </xsl:text>
	</xsl:template>
	<xsl:template match="naechste_schritte">
		<xsl:apply-templates select="schritt"/>
	</xsl:template>	
	<xsl:template match="schritt">
		<xsl:text> - </xsl:text><xsl:value-of select="beschreibung"/><xsl:text>
</xsl:text>
	</xsl:template>

	<xsl:template match="projektphasen">
		<xsl:apply-templates select="phase"/>
	</xsl:template>
	<xsl:template match="phase" disable-output-escaping="yes">
		<table:table-row table:style-name="ro10" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" >
          <table:table-cell table:style-name="ce20" office:value-type="string">
            <text:p><xsl:value-of select="bezeichnung"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce35" office:value-type="string">
            <text:p><xsl:value-of select="fortschritt"/>%</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce57" table:number-columns-spanned="2" table:number-rows-spanned="1"/>
          <table:covered-table-cell table:style-name="ce57"/>
          <table:table-cell table:number-columns-repeated="253"/>
          <table:table-cell table:style-name="ce25" table:number-columns-repeated="767"/>
        </table:table-row>
	</xsl:template>

</xsl:stylesheet >