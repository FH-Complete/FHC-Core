<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>
  <xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="supplements">
<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:width:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
  <office:scripts/>
  <office:font-face-decls>
    <style:font-face style:name="Wingdings" svg:font-family="Wingdings" style:font-pitch="variable" style:font-charset="x-symbol"/>
    <style:font-face style:name="Symbol" svg:font-family="Symbol" style:font-family-generic="roman" style:font-pitch="variable" style:font-charset="x-symbol"/>
    <style:font-face style:name="Courier New" svg:font-family="'Courier New'" style:font-family-generic="modern"/>
    <style:font-face style:name="FreeSans1" svg:font-family="FreeSans" style:font-family-generic="swiss"/>
    <style:font-face style:name="Courier New1" svg:font-family="'Courier New'" style:font-family-generic="modern" style:font-pitch="fixed"/>
    <style:font-face style:name="Times New Roman" svg:font-family="'Times New Roman'" style:font-family-generic="roman" style:font-pitch="variable"/>
    <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="Helvetica" svg:font-family="Helvetica" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="Droid Sans Fallback" svg:font-family="'Droid Sans Fallback'" style:font-family-generic="system" style:font-pitch="variable"/>
    <style:font-face style:name="FreeSans" svg:font-family="FreeSans" style:font-family-generic="system" style:font-pitch="variable"/>
  </office:font-face-decls>
  <office:automatic-styles>
    <style:style style:name="Table1" style:family="table" style:master-page-name="Convert_20_1">
      <style:table-properties style:width="17.247cm" fo:margin-left="-0.191cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table1.A" style:family="table-column">
      <style:table-column-properties style:column-width="8.192cm"/>
    </style:style>
    <style:style style:name="Table1.B" style:family="table-column">
      <style:table-column-properties style:column-width="9.056cm"/>
    </style:style>
    <style:style style:name="Table1.1" style:family="table-row">
      <style:table-row-properties style:min-row-height="6.269cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table1.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="none" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table1.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt dotted #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2" style:family="table">
      <style:table-properties style:width="17.02cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.A" style:family="table-column">
      <style:table-column-properties style:column-width="1cm"/>
    </style:style>
    <style:style style:name="Table2.B" style:family="table-column">
      <style:table-column-properties style:column-width="7.001cm"/>
    </style:style>
    <style:style style:name="Table2.C" style:family="table-column">
      <style:table-column-properties style:column-width="9.019cm"/>
    </style:style>
    <style:style style:name="Table2.1" style:family="table-row">
      <style:table-row-properties style:row-height="1cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table2.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table2.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table2.2" style:family="table-row">
      <style:table-row-properties style:row-height="0.982cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table2.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.B2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.C2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.3" style:family="table-row">
      <style:table-row-properties style:row-height="0.997cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table2.A3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.B3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.C3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.4" style:family="table-row">
      <style:table-row-properties style:row-height="1.004cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table2.A4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.B4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.C4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.5" style:family="table-row">
      <style:table-row-properties style:row-height="1.009cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table2.A5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.B5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table2.C5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3" style:family="table">
      <style:table-properties style:width="17.02cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.A" style:family="table-column">
      <style:table-column-properties style:column-width="1cm"/>
    </style:style>
    <style:style style:name="Table3.B" style:family="table-column">
      <style:table-column-properties style:column-width="7.001cm"/>
    </style:style>
    <style:style style:name="Table3.C" style:family="table-column">
      <style:table-column-properties style:column-width="9.019cm"/>
    </style:style>
    <style:style style:name="Table3.1" style:family="table-row">
      <style:table-row-properties style:row-height="1cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table3.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table3.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table3.2" style:family="table-row">
      <style:table-row-properties style:row-height="0.963cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table3.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.B2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.C2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.3" style:family="table-row">
      <style:table-row-properties style:row-height="0.975cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table3.A3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.B3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.C3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.4" style:family="table-row">
      <style:table-row-properties style:row-height="1.789cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table3.A4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.B4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.C4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.5" style:family="table-row">
      <style:table-row-properties style:row-height="1.764cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table3.A5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.B5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.C5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.6" style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table3.A6" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.B6" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table3.C6" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4" style:family="table">
      <style:table-properties style:width="17.02cm" fo:break-before="page" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.A" style:family="table-column">
      <style:table-column-properties style:column-width="1cm"/>
    </style:style>
    <style:style style:name="Table4.B" style:family="table-column">
      <style:table-column-properties style:column-width="7.001cm"/>
    </style:style>
    <style:style style:name="Table4.C" style:family="table-column">
      <style:table-column-properties style:column-width="9.019cm"/>
    </style:style>
    <style:style style:name="Table4.1" style:family="table-row">
      <style:table-row-properties style:row-height="1cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table4.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table4.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table4.2" style:family="table-row">
      <style:table-row-properties style:row-height="0.981cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table4.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.B2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.C2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.3" style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table4.A3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.B3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.C3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.4" style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table4.A4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.B4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table4.C4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5" style:family="table">
      <style:table-properties style:width="17.02cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.A" style:family="table-column">
      <style:table-column-properties style:column-width="1cm"/>
    </style:style>
    <style:style style:name="Table5.B" style:family="table-column">
      <style:table-column-properties style:column-width="7.001cm"/>
    </style:style>
    <style:style style:name="Table5.C" style:family="table-column">
      <style:table-column-properties style:column-width="2.752cm"/>
    </style:style>
    <style:style style:name="Table5.D" style:family="table-column">
      <style:table-column-properties style:column-width="4.249cm"/>
    </style:style>
    <style:style style:name="Table5.F" style:family="table-column">
      <style:table-column-properties style:column-width="1.018cm"/>
    </style:style>
    <style:style style:name="Table5.1" style:family="table-row">
      <style:table-row-properties style:row-height="1cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table5.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table5.2" style:family="table-row">
      <style:table-row-properties style:row-height="0.501cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.3" style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.4" style:family="table-row">
      <style:table-row-properties style:row-height="2.117cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.5" style:family="table-row">
      <style:table-row-properties style:row-height="1.265cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.D5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.E5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.F5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.6" style:family="table-row">
      <style:table-row-properties style:row-height="1.272cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A6" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B6" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C6" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.D6" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.E6" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.F6" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.7" style:family="table-row">
      <style:table-row-properties style:row-height="1.63cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A7" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B7" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C7" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.D7" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.E7" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.F7" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.8" style:family="table-row">
      <style:table-row-properties style:row-height="1.82cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A8" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B8" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C8" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.D8" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.E8" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.F8" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.9" style:family="table-row">
      <style:table-row-properties style:row-height="1.688cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A9" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B9" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C9" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.D9" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.E9" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.F9" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.10" style:family="table-row">
      <style:table-row-properties style:row-height="1.513cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A10" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B10" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C10" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.D10" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.E10" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.F10" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.11" style:family="table-row">
      <style:table-row-properties style:row-height="0.99cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A11" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B11" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C11" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.D11" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.E11" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.F11" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.12" style:family="table-row">
      <style:table-row-properties style:row-height="1.776cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A12" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B12" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C12" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.D12" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.E12" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.F12" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.A13" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B13" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C13" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.D13" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.E13" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.F13" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.14" style:family="table-row">
      <style:table-row-properties style:row-height="1.062cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table5.A14" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.B14" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table5.C14" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table6" style:family="table">
      <style:table-properties style:width="17.02cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table6.A" style:family="table-column">
      <style:table-column-properties style:column-width="1cm"/>
    </style:style>
    <style:style style:name="Table6.B" style:family="table-column">
      <style:table-column-properties style:column-width="7.001cm"/>
    </style:style>
    <style:style style:name="Table6.C" style:family="table-column">
      <style:table-column-properties style:column-width="9.019cm"/>
    </style:style>
    <style:style style:name="Table6.1" style:family="table-row">
      <style:table-row-properties style:row-height="1cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table6.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table6.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table6.2" style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table6.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table6.B2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table6.C2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table6.3" style:family="table-row">
      <style:table-row-properties style:row-height="2.237cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table6.A3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table6.B3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table6.C3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table7" style:family="table">
      <style:table-properties fo:break-before="page" style:width="17.02cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table7.A" style:family="table-column">
      <style:table-column-properties style:column-width="1cm"/>
    </style:style>
    <style:style style:name="Table7.B" style:family="table-column">
      <style:table-column-properties style:column-width="7.001cm"/>
    </style:style>
    <style:style style:name="Table7.C" style:family="table-column">
      <style:table-column-properties style:column-width="9.019cm"/>
    </style:style>
    <style:style style:name="Table7.1" style:family="table-row">
      <style:table-row-properties style:row-height="1cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table7.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table7.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table7.2" style:family="table-row">
<!--      <style:table-row-properties style:row-height="1.314cm" fo:keep-together="auto"/> -->
       <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table7.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table7.B2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table7.C2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table7.3" style:family="table-row">
      <style:table-row-properties style:row-height="0.975cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table7.A3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table7.B3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table7.C3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table8" style:family="table">
      <style:table-properties style:width="17.02cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table8.A" style:family="table-column">
      <style:table-column-properties style:column-width="1cm"/>
    </style:style>
    <style:style style:name="Table8.B" style:family="table-column">
      <style:table-column-properties style:column-width="5.288cm"/>
    </style:style>
    <style:style style:name="Table8.C" style:family="table-column">
      <style:table-column-properties style:column-width="5.357cm"/>
    </style:style>
    <style:style style:name="Table8.D" style:family="table-column">
      <style:table-column-properties style:column-width="5.375cm"/>
    </style:style>
    <style:style style:name="Table8.1" style:family="table-row">
      <style:table-row-properties style:row-height="1cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table8.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table8.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table8.2" style:family="table-row">
      <style:table-row-properties style:min-row-height="2.487cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table8.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table8.B2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table8.C2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table8.D2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table9" style:family="table">
      <style:table-properties style:width="17.02cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table9.A" style:family="table-column">
      <style:table-column-properties style:column-width="1cm"/>
    </style:style>
    <style:style style:name="Table9.B" style:family="table-column">
      <style:table-column-properties style:column-width="16.02cm"/>
    </style:style>
    <style:style style:name="Table9.1" style:family="table-row">
      <style:table-row-properties style:row-height="1cm" fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table9.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table9.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table10" style:family="table">
      <style:table-properties style:width="17cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table10.A" style:family="table-column">
      <style:table-column-properties style:column-width="5cm"/>
    </style:style>
    <style:style style:name="Table10.B" style:family="table-column">
      <style:table-column-properties style:column-width="12cm"/>
    </style:style>
    <style:style style:name="Table10.1" style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table10.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table10.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table10.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table10.B2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table11" style:family="table">
      <style:table-properties style:width="17cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table11.A" style:family="table-column">
      <style:table-column-properties style:column-width="5cm"/>
    </style:style>
    <style:style style:name="Table11.B" style:family="table-column">
      <style:table-column-properties style:column-width="12cm"/>
    </style:style>
    <style:style style:name="Table11.1" style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table11.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
	  </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table11.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
	  </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table11.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table11.B2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12" style:family="table" style:master-page-name="">
      <style:table-properties style:width="17.02cm" style:page-number="auto" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.A" style:family="table-column">
      <style:table-column-properties style:column-width="2cm"/>
    </style:style>
    <style:style style:name="Table12.B" style:family="table-column">
      <style:table-column-properties style:column-width="7.694cm"/>
    </style:style>
    <style:style style:name="Table12.C" style:family="table-column">
      <style:table-column-properties style:column-width="2.327cm"/>
    </style:style>
    <style:style style:name="Table12.D" style:family="table-column">
      <style:table-column-properties style:column-width="1.778cm"/>
    </style:style>
    <style:style style:name="Table12.E" style:family="table-column">
      <style:table-column-properties style:column-width="1.76cm"/>
    </style:style>
    <style:style style:name="Table12.F" style:family="table-column">
      <style:table-column-properties style:column-width="1.461cm"/>
    </style:style>
    <style:style style:name="Table12.1" style:family="table-row">
      <style:table-row-properties fo:keep-together="always"/>
    </style:style>
    <style:style style:name="Table12.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table12.F1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table12.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.A3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.B3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.C3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.D3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.E3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.F3" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.A4" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.A5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.B5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.C5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.D5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.E5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table12.F5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table13" style:family="table">
      <style:table-properties style:width="17cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table13.A" style:family="table-column">
      <style:table-column-properties style:column-width="8.5cm"/>
    </style:style>
    <style:style style:name="Table13.1" style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table13.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="top" fo:padding="0cm" fo:border="none" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table14" style:family="table">
       <style:table-properties style:width="17cm" table:align="margins" style:writing-mode="lr-tb" />
    </style:style>
    <style:style style:name="Table14.A" style:family="table-column">
       <style:table-column-properties style:column-width="5.36cm" style:rel-column-width="3039*" />
    </style:style>
    <style:style style:name="Table14.B" style:family="table-column">
       <style:table-column-properties style:column-width="3.011cm" style:rel-column-width="1707*" />
    </style:style>
    <style:style style:name="Table14.C" style:family="table-column">
       <style:table-column-properties style:column-width="8.629cm" style:rel-column-width="4892*" />
    </style:style>
    <style:style style:name="Table14.A1" style:family="table-cell">
       <style:table-cell-properties fo:padding="0.097cm" fo:border="none" />
    </style:style>
    <style:style style:name="Table15" style:family="table">
       <style:table-properties style:width="17cm" table:align="margins" style:writing-mode="lr-tb" />
    </style:style>
    <style:style style:name="Table15.A" style:family="table-column">
       <style:table-column-properties style:column-width="7.12cm"/>
    </style:style>
    <style:style style:name="Table15.B" style:family="table-column">
       <style:table-column-properties style:column-width="2.97cm"/>
    </style:style>
    <style:style style:name="Table15.C" style:family="table-column">
       <style:table-column-properties style:column-width="6.92cm"/>
    </style:style>
    <style:style style:name="Table15.A1" style:family="table-cell">
       <style:table-cell-properties fo:padding="0.097cm" fo:border="none" />
    </style:style>
    <style:style style:name="Table16" style:family="table">
       <style:table-properties style:width="17cm" table:align="margins" />
    </style:style>
    <style:style style:name="Table16.A" style:family="table-column">
       <style:table-column-properties style:column-width="8.5cm" />
    </style:style>
    <style:style style:name="Table16.A1" style:family="table-cell">
       <style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000" fo:background-color="#afb8bc">
	      <style:background-image/>
	   </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table16.A2" style:family="table-cell">
       <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000" />
    </style:style>
    <style:style style:name="Table16.B2" style:family="table-cell">
       <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000" />
    </style:style>
    <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:language="zxx" fo:country="none" style:language-asian="zxx" style:country-asian="none"/>
    </style:style>
    <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="16pt" fo:language="de" fo:country="AT" fo:font-weight="bold" style:font-size-asian="16pt" style:font-weight-asian="bold" style:font-size-complex="16pt"/>
    </style:style>
    <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="110%" fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties fo:font-size="16pt" fo:language="de" fo:country="AT" fo:font-weight="bold" style:font-size-asian="16pt" style:font-weight-asian="bold" style:font-size-complex="16pt"/>
    </style:style>
    <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="110%" fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties fo:font-size="16pt" fo:language="de" fo:country="AT" style:font-size-asian="16pt" style:font-size-complex="16pt"/>
    </style:style>
    <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="110%"/>
      <style:text-properties fo:font-size="16pt" fo:language="en" fo:country="US" fo:font-weight="bold" style:font-size-asian="16pt" style:font-weight-asian="bold" style:font-size-complex="16pt"/>
    </style:style>
    <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="16pt" fo:language="en" fo:country="US" style:font-size-asian="16pt" style:font-size-complex="16pt"/>
    </style:style>
    <style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="110%"/>
      <style:text-properties fo:font-size="16pt" fo:language="en" fo:country="US" style:font-size-asian="16pt" style:font-size-complex="16pt"/>
    </style:style>
    <style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:language="de" fo:country="AT"/>
    </style:style>
    <style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties fo:language="de" fo:country="AT"/>
    </style:style>
    <style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false">
        <style:tab-stops>
          <style:tab-stop style:position="7.001cm" style:leader-style="solid" style:leader-text="_"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties fo:language="de" fo:country="AT"/>
    </style:style>
    <style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="110%" fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties fo:language="de" fo:country="AT"/>
    </style:style>
    <style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:language="en" fo:country="US"/>
    </style:style>
    <style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:language="en" fo:country="US" style:language-asian="zxx" style:country-asian="none"/>
    </style:style>
    <style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="4pt" fo:language="de" fo:country="AT" style:font-size-asian="3.5pt" style:font-size-complex="4pt"/>
    </style:style>
    <style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="4pt" fo:language="en" fo:country="US" style:font-size-asian="3.5pt" style:font-size-complex="4pt"/>
    </style:style>
    <style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties style:text-autospace="none"/>
      <style:text-properties fo:color="#000000" fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:language-asian="de" style:country-asian="AT" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="8pt" fo:language="en" fo:country="US" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="8pt" fo:language="de" fo:country="AT" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P20" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P21" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="120%"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P22" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="6pt" fo:language="en" fo:country="US" style:font-size-asian="6pt" style:font-size-complex="6pt"/>
    </style:style>
    <style:style style:name="P23" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="120%"/>
      <style:text-properties fo:font-size="6pt" fo:language="en" fo:country="US" style:font-size-asian="6pt" style:font-size-complex="6pt"/>
    </style:style>
    <style:style style:name="P24" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P25" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:language-asian="de" style:country-asian="AT" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P26" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="10pt" fo:language="en" fo:country="US" style:font-size-asian="8.75pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P27" style:family="paragraph" style:parent-style-name="Footer">
      <style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
    </style:style>
    <style:style style:name="P28" style:family="paragraph" style:parent-style-name="Footer">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="0.815cm"/>
          <style:tab-stop style:position="8.001cm" style:type="center"/>
          <style:tab-stop style:position="16.002cm" style:type="right"/>
          <style:tab-stop style:position="17.002cm" style:type="right"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties fo:font-size="7.5pt" fo:language="en" fo:country="US" style:font-size-asian="7.5pt" style:font-size-complex="7.5pt"/>
    </style:style>
    <style:style style:name="P29" style:family="paragraph" style:parent-style-name="Footer">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="0.815cm"/>
          <style:tab-stop style:position="8.001cm" style:type="center"/>
          <style:tab-stop style:position="16.002cm" style:type="right"/>
          <style:tab-stop style:position="17.002cm" style:type="right"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties fo:font-size="7.5pt" style:font-size-asian="7.5pt" style:font-size-complex="7.5pt"/>
    </style:style>
    <style:style style:name="P30" style:family="paragraph" style:parent-style-name="Footer">
      <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0.635cm" fo:text-indent="0cm" style:auto-text-indent="false">
        <style:tab-stops>
          <style:tab-stop style:position="0.815cm"/>
          <style:tab-stop style:position="8.001cm" style:type="center"/>
          <style:tab-stop style:position="16.002cm" style:type="right"/>
          <style:tab-stop style:position="17.002cm" style:type="right"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties fo:font-size="7.5pt" style:font-size-asian="7.5pt" style:font-size-complex="7.5pt"/>
    </style:style>
    <style:style style:name="P31" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.423cm" fo:line-height="100%" fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties fo:font-size="16pt" fo:language="de" fo:country="AT" fo:font-weight="bold" style:font-size-asian="16pt" style:font-weight-asian="bold" style:font-size-complex="16pt"/>
    </style:style>
    <style:style style:name="P32" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0.106cm" fo:line-height="100%"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P33" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0.106cm" fo:line-height="100%"/>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P34" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-left="9.991cm" fo:margin-right="0cm" fo:line-height="110%" fo:text-align="end" style:justify-single-word="false" fo:text-indent="0cm" style:auto-text-indent="false"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P35" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm">
        <style:tab-stops>
          <style:tab-stop style:position="7.001cm" style:leader-style="solid" style:leader-text="_"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-name-asian="Arial" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
		<style:text-properties style:text-underline-style="dotted" style:text-underline-width="bold" style:text-underline-color="font-color"/>
    </style:style>
    <style:style style:name="P36" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm">
        <style:tab-stops>
          <style:tab-stop style:position="7.001cm" style:leader-style="solid" style:leader-text="_"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P37" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm">
        <style:tab-stops>
          <style:tab-stop style:position="7.001cm" style:leader-style="solid" style:leader-text="_"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P38" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="1.249cm" style:auto-text-indent="false"/>
      <style:text-properties fo:language="en" fo:country="US" style:language-asian="zxx" style:country-asian="none"/>
    </style:style>
    <style:style style:name="P39" style:family="paragraph" style:parent-style-name="Heading_20_5">
      <style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P40" style:family="paragraph" style:parent-style-name="Heading_20_5">
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P41" style:family="paragraph" style:parent-style-name="Heading_20_5">
      <style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P42" style:family="paragraph" style:parent-style-name="Tabelleninhalt_20_neu">
      <style:text-properties fo:language="en" fo:country="US"/>
    </style:style>
    <style:style style:name="P43" style:family="paragraph" style:parent-style-name="Tabelleninhalt_20_neu">
      <style:paragraph-properties style:snap-to-layout-grid="false"/>
      <style:text-properties fo:language="en" fo:country="US" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P44" style:family="paragraph" style:parent-style-name="Tabelleninhalt_20_neu">
      <style:paragraph-properties fo:line-height="110%"/>
    </style:style>
    <style:style style:name="P45" style:family="paragraph" style:parent-style-name="Tabelleninhalt_20_neu">
      <style:paragraph-properties fo:orphans="2" fo:widows="2" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="P46" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="7.5pt" fo:font-weight="normal" style:font-size-asian="7.5pt" style:font-weight-asian="normal" style:font-size-complex="7.5pt" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="P47" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:text-properties style:font-name="Arial" fo:font-size="7.5pt" style:font-size-asian="7.5pt" style:font-size-complex="7.5pt"/>
    </style:style>
    <style:style style:name="P48" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:break-before="page"/>
      <style:text-properties fo:language="en" fo:country="US" style:language-asian="zxx" style:country-asian="none"/>
    </style:style>
    <style:style style:name="P49" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_2">
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P50" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_2">
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-size-complex="9.5pt"/>
    </style:style>
    <style:style style:name="P51" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_2">
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-size-complex="9.5pt"/>
    </style:style>
    <style:style style:name="P52" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P53" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P54" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
      <style:paragraph-properties fo:margin-left="0.4cm" fo:margin-right="0cm" fo:text-indent="-0.4cm" style:auto-text-indent="false"/>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P55" style:family="paragraph" style:parent-style-name="Heading_20_4" style:master-page-name="Standard">
      <style:text-properties style:use-window-font-color="true"/>
    </style:style> 
    <style:style style:name="P56" style:family="paragraph" style:parent-style-name="Heading_20_4" style:master-page-name="Convert_20_1">
      <style:paragraph-properties style:page-number="auto"/>
      <style:text-properties style:use-window-font-color="true" fo:language="en" fo:country="US"/>
    </style:style>
    <style:style style:name="P57" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:break-before="page"/>
      <style:text-properties fo:language="en" fo:country="US" style:language-asian="zxx" style:country-asian="none"/>
    </style:style>
    <style:style style:name="P58" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="left" style:justify-single-word="false"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P59" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="110%" fo:text-align="center" style:justify-single-word="false"/>
    </style:style>
    <style:style style:name="P60" style:family="paragraph" style:parent-style-name="Standard" style:master-page-name="Right_20_Page" >
      <style:paragraph-properties style:page-number="1">
        <style:tab-stops>
          <style:tab-stop style:position="10.502cm"/>
        </style:tab-stops>
      </style:paragraph-properties>
      <style:text-properties fo:font-size="16pt" fo:language="de" fo:country="AT" fo:font-weight="bold" style:font-size-asian="16pt" style:font-weight-asian="bold" style:font-size-complex="16pt"/>
    </style:style>
    <style:style style:name="P61" style:family="paragraph" style:parent-style-name="Standard" style:master-page-name="Standard">
      <style:paragraph-properties fo:line-height="110%" style:page-number="1"/>
      <style:text-properties fo:font-size="16pt" fo:language="en" fo:country="US" fo:font-weight="bold" style:font-size-asian="16pt" style:language-asian="zxx" style:country-asian="none" style:font-weight-asian="bold" style:font-size-complex="16pt"/>
    </style:style>
    <style:style style:name="P62" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0.101cm" fo:text-align="center" style:justify-single-word="false" style:shadow="none"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" fo:font-weight="bold" style:font-size-asian="9pt" style:font-weight-asian="bold" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P63" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0.101cm" style:shadow="none"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P64" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0.101cm" fo:text-align="center" style:justify-single-word="false" style:shadow="none"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P65" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0.101cm" fo:text-align="center" style:justify-single-word="false" style:shadow="none"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P66" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0.101cm" fo:text-align="center" style:justify-single-word="false" style:shadow="none"/>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" fo:font-weight="bold" style:font-size-asian="9pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P67" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0.101cm"/>
      <style:text-properties fo:font-size="8pt" fo:language="de" fo:country="AT" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P68" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0.101cm"/>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" fo:font-weight="bold" style:font-size-asian="9pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P69" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0.101cm" fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" fo:font-weight="bold" style:font-size-asian="9pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P70" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0.101cm"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P71" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0.101cm" fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-name-complex="Arial" style:font-size-complex="9pt"/>
    </style:style>
    <style:style style:name="P72" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-top="0.101cm" fo:margin-bottom="0.101cm" fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties fo:font-size="9.5pt" fo:language="en" fo:country="US" style:font-size-asian="9.5pt" style:font-size-complex="9.5pt"/>
    </style:style>
    <style:style style:name="P73" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-size-complex="9pt" fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"/>
    </style:style>
    <style:style style:name="P74" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="9pt" fo:language="de" fo:country="AT" style:font-size-asian="9pt" style:font-size-complex="9pt" fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"/>
	  <style:paragraph-properties fo:text-align="end"/>
    </style:style>
    <style:style style:name="P75" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-size-complex="9pt" fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"/>
    </style:style>
    <style:style style:name="P76" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="9pt" fo:language="en" fo:country="US" style:font-size-asian="9pt" style:font-size-complex="9pt" fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"/>
	  <style:paragraph-properties fo:text-align="end"/>
    </style:style>
    <style:style style:name="T1" style:family="text">
      <style:text-properties fo:language="en" fo:country="US"/>
    </style:style>
    <style:style style:name="T2" style:family="text">
      <style:text-properties fo:color="#000000"/>
    </style:style>
    <style:style style:name="T3" style:family="text">
      <style:text-properties fo:color="#000000" fo:language="en" fo:country="US"/>
    </style:style>
    <style:style style:name="T4" style:family="text">
      <style:text-properties style:font-name-asian="Arial"/>
    </style:style>
    <style:style style:name="T5" style:family="text">
      <style:text-properties style:text-position="super 58%"/>
    </style:style>
    <style:style style:name="T6" style:family="text">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-name-asian="Times New Roman" style:font-size-asian="8pt" style:font-name-complex="Arial" style:language-complex="ar" style:country-complex="SA"/>
    </style:style>
    <style:style style:name="T7" style:family="text">
      <style:text-properties style:font-name="Arial" style:font-name-asian="Times New Roman" style:font-name-complex="Arial" style:language-complex="ar" style:country-complex="SA"/>
    </style:style>
    <style:style style:name="T8" style:family="text">
      <style:text-properties style:font-name="Arial" fo:font-size="16pt" fo:language="de" fo:country="AT" fo:font-weight="bold" style:font-name-asian="Times New Roman" style:font-size-asian="16pt" style:font-weight-asian="bold" style:font-name-complex="Arial" style:font-size-complex="16pt" style:language-complex="ar" style:country-complex="SA"/>
    </style:style>
    <style:style style:name="T9" style:family="text">
      <style:text-properties style:font-name="Arial" fo:font-size="16pt" fo:language="de" fo:country="AT" style:font-name-asian="Times New Roman" style:font-size-asian="16pt" style:font-name-complex="Arial" style:font-size-complex="16pt" style:language-complex="ar" style:country-complex="SA"/>
    </style:style>
    <style:style style:name="T10" style:family="text">
      <style:text-properties style:font-name="Arial" fo:language="de" fo:country="AT" style:font-name-asian="Times New Roman" style:font-name-complex="Arial" style:language-complex="ar" style:country-complex="SA"/>
    </style:style>
    <style:style style:name="T11" style:family="text">
      <style:text-properties fo:font-weight="normal" style:font-weight-asian="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="T12" style:family="text">
      <style:text-properties fo:font-size="16pt" fo:font-weight="bold" style:font-size-asian="16pt" style:font-weight-asian="bold" style:font-size-complex="16pt"/>
    </style:style>
    <style:style style:name="T13" style:family="text">
      <style:text-properties fo:font-size="16pt" fo:language="de" fo:country="AT" style:font-size-asian="16pt" style:font-size-complex="16pt"/>
    </style:style>
	<style:style style:name="T14" style:family="text">
       <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold" />
	</style:style>
	<style:style style:name="T15" style:family="text">
       <style:text-properties fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic" />
	</style:style>

    <style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties fo:margin-left="0.319cm" fo:margin-right="0.319cm" style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:padding="0.026cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
    </style:style>
    <style:style style:name="fr2" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties style:vertical-pos="top" style:vertical-rel="paragraph" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
    </style:style>
  </office:automatic-styles>
	<office:body xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0">
 	<office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0">
		<office:forms form:automatic-focus="false" form:apply-design-mode="false" />
		<xsl:apply-templates select="supplement"/>
		</office:text>
	</office:body>
	</office:document-content>
 </xsl:template>
<xsl:template match="supplement">
		
      <text:sequence-decls xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">
        <text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Table"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Text"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
      </text:sequence-decls>
      <text:p text:style-name="P60"/>
      <text:p text:style-name="P2"/>
      <text:p text:style-name="P2"/>
      <text:p text:style-name="P2"/>
      <text:p text:style-name="P2"/>
      <text:p text:style-name="P31">Transcript of Records</text:p>
      <text:p text:style-name="P9"/>
      <text:p text:style-name="P59">
        <text:span text:style-name="T13">Lehrgang zur Weiterbildung</text:span>
      </text:p>
      <text:p text:style-name="P59">
        <text:span text:style-name="Tabelleninhalt">
          <text:span text:style-name="T8"><xsl:value-of select="studiengang_bezeichnung_deutsch"/></text:span>
        </text:span>
      </text:p>
      <text:p text:style-name="P11"/>
      <text:p text:style-name="P59">
        <text:span text:style-name="T13">Certificate Program for Further Education</text:span>
      </text:p>
      <text:p text:style-name="P3">
        <text:span text:style-name="Tabelleninhalt">
          <text:span text:style-name="T10"><xsl:value-of select="studiengang_bezeichnung_englisch"/></text:span>
        </text:span>
      </text:p>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <table:table table:name="Table1" table:style-name="Table1">
        <table:table-column table:style-name="Table1.A"/>
        <table:table-column table:style-name="Table1.B"/>
        <text:soft-page-break/>
        <table:table-row table:style-name="Table1.1">
          <table:table-cell table:style-name="Table1.A1" office:value-type="string">
            <text:p text:style-name="Standard">Mit dem Dokument wird das Ziel verfolgt, Daten zu erfassen, um die internationale "Transparenz" und die angemessene akademische und berufliche Anerkennung von Qualifikationen (Diplomen, Abschlüssen, Zeugnissen usw.) zu verbessern. Es bietet eine Beschreibung über Art, Niveau, Kontext, Inhalt und Status eines Studiums, den die im Original-Befähigungs-nachweis, dem der Anhang beigefügt ist, genannte Person absolviert und erfolgreich abgeschlossen hat. Der Anhang sollte keinerlei Werturteile, Aussagen über Gleichwertigkeit mit anderen Qualifikationen oder Vorschläge bezüglich der Anerkennung enthalten.</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table1.B1" office:value-type="string">
            <text:p text:style-name="P12">The purpose of the document is to provide data to improve the international transparency and fair academic and professional recognition of qualifications (diplomas, degrees, certificates, etc.). It is designed to provide a description of the nature, level, context, content and status of the studies that were pursued and successfully completed by the individual named on the original qualification to which this supplement is appended. It should be free from any value judgments, equivalence statements or suggestions about recognition.</text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P12"/>
      <table:table table:name="Table2" table:style-name="Table2">
        <table:table-column table:style-name="Table2.A"/>
        <table:table-column table:style-name="Table2.B"/>
        <table:table-column table:style-name="Table2.C"/>
        <table:table-row table:style-name="Table2.1">
          <table:table-cell table:style-name="Table2.A1" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">1.</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.B1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">Angaben zur Person des Qualifikationsinhabers/der Qualifikationsinhaberin</text:p>
            <text:p text:style-name="Tabellenkopf">Information identifying the holder of the qualification</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table2.2">
          <table:table-cell table:style-name="Table2.A2" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">1.1</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.B2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Familienname(n)</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Family Name(s)</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.C2" office:value-type="string">
            <text:p text:style-name="P45">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T6"><xsl:value-of select="nachname"/></text:span>
              </text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.3">
          <table:table-cell table:style-name="Table2.A3" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">1.2</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.B3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Vorname(n)</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Given Name(s)</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.C3" office:value-type="string">
            <text:p text:style-name="P46"><xsl:value-of select="vorname"/></text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.4">
          <table:table-cell table:style-name="Table2.A4" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">1.3</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.B4" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Geburtsdatum (TT.MM.JJJJ)</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Date of birth (DD.MM.YYYY)</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.C4" office:value-type="string">
            <text:p text:style-name="P46">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="geburtsdatum"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table2.5">
          <table:table-cell table:style-name="Table2.A5" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">1.4</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.B5" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Personenkennzeichen</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Student identification number</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table2.C5" office:value-type="string">
            <text:p text:style-name="P47">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T11"><xsl:value-of select="matrikelnummer"/></text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T11"> </text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T11">
                  <text:s/>
                </text:span>
              </text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P12"/>
      <table:table table:name="Table3" table:style-name="Table3">
        <table:table-column table:style-name="Table3.A"/>
        <table:table-column table:style-name="Table3.B"/>
        <table:table-column table:style-name="Table3.C"/>
        <table:table-row table:style-name="Table3.1">
          <table:table-cell table:style-name="Table3.A1" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">2.</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.B1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">Angaben zur Qualifikation</text:p>
            <text:p text:style-name="Tabellenkopf">Information identifying the qualification</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table3.2">
          <table:table-cell table:style-name="Table3.A2" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">2.1</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.B2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Name der Qualifikation</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Name of qualification</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.C2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Akademische/r Social Media Manager/in<!-- <xsl:value-of select="titel_de"/> --></text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Academic Social Media Manager <!-- <xsl:value-of select="titel_en"/> --></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table3.3">
          <table:table-cell table:style-name="Table3.A3" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">2.2</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.B3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Hauptstudierfach oder -fächer für die Qualifikation</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Main field(s) of study for the qualification</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.C3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="studiengang_bezeichnung_deutsch"/></text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="studiengang_bezeichnung_englisch"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table3.4">
          <table:table-cell table:style-name="Table3.A4" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">2.3</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.B4" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Name und Status der Organisation, die die Qualifikation verliehen hat</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Name and status of awarding institution</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.C4" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T2">Fachhochschule Technikum Wien, Verleihung des Status „Fachhochschule" im November 2000</text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt"> </text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T3">University of Applied Sciences Technikum Wien, status University of Applied Science </text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt">conferred November 2000</text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table3.5">
          <table:table-cell table:style-name="Table3.A5" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">2.4</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.B5" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T1">Name und Status der Einrichtung, die das Studium durchführte</text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt"> </text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T1">Name and status of institution administering studies</text:span>
              </text:span>
              <!-- <text:span text:style-name="Tabelleninhalt"> </text:span>
              <text:span text:style-name="Tabelleninhalt">tution administering studies</text:span> -->
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.C5" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Fachhochschule Technikum Wien, Verleihung des Status „Fachhochschule" im November 2000 </text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T3">University of Applied Sciences Technikum Wien, status University of Applied Science conferred November 2000</text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt"> </text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table3.6">
          <table:table-cell table:style-name="Table3.A6" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">2.5</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.B6" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Im Unterricht / in den Prüfungen verwendete Sprachen</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Language(s) of instruction / examination</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.C6" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="sprache_deutsch"/><text:line-break/></text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="sprache_englisch"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        
        <table:table-row table:style-name="Table4.3">
          <table:table-cell table:style-name="Table4.A3" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">2.6</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.B3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Regelstudienzeit und ECTS credits</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Official length of program and ECTS credits</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.C3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">2 Semester | 1 Jahr | 60 ECTS</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">2 semester(s) | 1 year(s) | 60 ECTS</text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table5.2">
          <table:table-cell table:style-name="Table5.A2" office:value-type="string">
            <text:p text:style-name="Tabelleninhalt_20_neu">
              <text:span text:style-name="Tabelleninhalt">2.7</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.B2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Studienart / Mode of Study</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.C2" table:number-columns-spanned="4" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Weiterbildung / Further Education <!--<xsl:value-of select="studienart"/>--></text:span>
            </text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table7.3">
          <table:table-cell table:style-name="Table7.A3" office:value-type="string">
            <text:p text:style-name="Tabelleninhalt_20_neu">
              <text:span text:style-name="Tabelleninhalt">2.8</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table7.B3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Informationsquellen für ergänzende Angaben</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Further information sources</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table7.C3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">www.technikum-wien.at</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">www.lllacademy.at</text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>        
      </table:table>
      
      <text:p text:style-name="P56">Abgeschlossene Lehrveranstaltungen / Completed subjects</text:p>
      <text:p text:style-name="P18"/>
      <!-- <text:p text:style-name="P20"/> -->
      <text:p text:style-name="P21">Im Rahmen des Weiterbildungslehrganges <xsl:value-of select="studiengang_bezeichnung_deutsch"/> der FH Technikum Wien wurden die folgenden Lehrveranstaltungen erfolgreich abgeschlossen:</text:p>
      <text:p text:style-name="P20"/>
      <text:p text:style-name="P21">Within the Further Education program <xsl:value-of select="studiengang_bezeichnung_englisch"/> provided by the University of Applied Science Technikum Wien examinations in the following subjects were passed:</text:p>
      <text:p text:style-name="P20"/>
      <table:table table:name="Table12" table:style-name="Table12">
        <table:table-column table:style-name="Table12.A"/>
        <table:table-column table:style-name="Table12.B"/>
        <table:table-column table:style-name="Table12.C"/>
        <table:table-column table:style-name="Table12.D"/>
        <table:table-column table:style-name="Table12.E"/>
        <table:table-column table:style-name="Table12.F"/>
        <table:table-row table:style-name="Table12.1">
          <table:table-cell table:style-name="Table12.A1" office:value-type="string">
            <text:p text:style-name="P68">Date</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.A1" office:value-type="string">
            <text:p text:style-name="P68">Course</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.A1" office:value-type="string">
            <text:p text:style-name="P69">Type<text:span text:style-name="T5">1</text:span></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.A1" office:value-type="string">
            <text:p text:style-name="P69">SP/W<text:span text:style-name="T5">2</text:span></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.A1" office:value-type="string">
            <text:p text:style-name="P69">ECTS credits</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.F1" office:value-type="string">
            <text:p text:style-name="P69">Grade³</text:p>
          </table:table-cell>
        </table:table-row>
        <xsl:apply-templates select="studiensemester"/>
        <table:table-row table:style-name="Table12.1">
          <table:table-cell table:style-name="Table12.A5" office:value-type="string">
            <text:p text:style-name="P70">Total</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.B5" office:value-type="string">
            <text:p text:style-name="P65">
            -
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.C5" office:value-type="string">
            <text:p text:style-name="P65">
            -
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.D5" office:value-type="string">
            <text:p text:style-name="P65">
            -
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.E5" office:value-type="string">
            <text:p text:style-name="P65"><xsl:value-of select="ects_total"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.F5" office:value-type="string">
            <text:p text:style-name="P65">
            -
            </text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P19"/>
      <text:p text:style-name="P19"/>
      <text:p text:style-name="P23">¹ Type: Laboratory, Labor (LAB); Lecture, Vorlesung (VO); Integrated Course, Integrierte Lehrveranstaltung (ILV); Seminar (SE), Tutorial, Tutorium (TUT); Project, Projekt (PRJ); Exercise, Üebung (UE); Distance Learning, Fernstudium (FL); Other, Andere (SO)</text:p>
      <text:p text:style-name="P23">² 1 Semester period per week = 45 minutes; 1 Semesterwochenstunde = 45 Minuten</text:p>
      <text:p text:style-name="P23">³ Grading Scheme: excellent / Sehr gut (1), good / Gut (2), satisfactory / Befriedigend (3), Sufficient / Genügend (4), Unsatisfactory / Nicht genügend (5), not graded / Nicht beurteilt (nb), Credit based on previous experience/work / Angrechnet (ar), successfully completed / erfolgreich teilgenommen (ea), not successfully completed / nicht erfolgreich teilgenommen (nea), Participated with success / mit Erfolg teilgenommen (met), participated / teilgenommen (tg)</text:p>
      <text:p text:style-name="P18">
        <text:soft-page-break/>
      </text:p>
      <text:p text:style-name="P22"/>
      <!-- Bei den "akademischen" Lehrgängen brauchen wir das nicht. Kann also gestrichen werden. Bei Masterlehrgängen werden wir das später einmal brauchen <table:table table:name="Table16" table:style-name="Table16">
         <table:table-column table:style-name="Table16.A" table:number-columns-repeated="2" />
         <table:table-row>
            <table:table-cell table:style-name="Table16.A1" table:number-columns-spanned="2" office:value-type="string">
               <text:p text:style-name="P68">Abschlussprüfung / Final Examination</text:p>
            </table:table-cell>
            <table:covered-table-cell />
         </table:table-row>
         <table:table-row>
            <table:table-cell table:style-name="Table16.A2" office:value-type="string">
               <text:p text:style-name="P70"><xsl:value-of select="studiengang_typ"/>'s Examination on <xsl:value-of select="abschlusspruefungsdatum" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Table16.B2" office:value-type="string">
               <text:p text:style-name="P70"><xsl:value-of select="abschlussbeurteilung" /></text:p>
            </table:table-cell>
         </table:table-row>
      </table:table>
      <text:p text:style-name="P19"/>
      <text:p text:style-name="P19"/>
      <text:p text:style-name="P23">Leistungsbeurteilung: Mit ausgezeichnetem Erfolg bestanden, Mit gutem Erfolg bestanden, Bestanden</text:p>
      <text:p text:style-name="P23">Grading Scheme: Passed with highest distinction, Passed with distinction, Passed</text:p> -->
      
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <text:p text:style-name="P22"/>
      <table:table table:name="Table13" table:style-name="Table13">
        <table:table-column table:style-name="Table13.A" table:number-columns-repeated="2"/>
        <table:table-row table:style-name="Table13.1">
          <table:table-cell table:style-name="Table13.A1" office:value-type="string">
            <text:p text:style-name="P35">Vienna, <xsl:value-of select="datum"/><text:s text:c="13"/></text:p>
            <text:p text:style-name="P36">Place / Ort, Datum / Date</text:p>
            <text:p text:style-name="P10"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table13.A1" office:value-type="string">
            <text:p text:style-name="P35"><text:s text:c="70"/></text:p>
            <text:p text:style-name="P37">
              <text:span text:style-name="T1"><xsl:value-of select="stgl"/><text:line-break/>LehrgangsleiterIn / Program Director</text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P38"/>

    </xsl:template>

  <xsl:template match="studiensemester">
    <xsl:apply-templates select="semesters"/>
  </xsl:template>
    <xsl:template match="semesters">
    <table:table-row xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" table:style-name="Table12.1">
      <table:table-cell table:style-name="Table12.A4" table:number-columns-spanned="6" office:value-type="string">
        <text:p text:style-name="P66">
          <xsl:value-of select="substring(semesterKurzbz,13)"/>
        </text:p>
      </table:table-cell>
      <table:covered-table-cell/>
      <table:covered-table-cell/>
      <table:covered-table-cell/>
      <table:covered-table-cell/>
      <table:covered-table-cell/>
    </table:table-row>
    <xsl:apply-templates select="lv"/>

    <table:table-row xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" table:style-name="Table12.1">
      <table:table-cell table:style-name="Table12.A5" office:value-type="string">
        <text:p text:style-name="P20">
          Σ
        </text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Table12.B5" office:value-type="string">
        <text:p text:style-name="P65">
		-
        </text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Table12.C5" office:value-type="string">
        <text:p text:style-name="P65">
		-
        </text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Table12.D5" office:value-type="string">
        <text:p text:style-name="P65">
		-
        </text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Table12.E5" office:value-type="string">
        <text:p text:style-name="P65">
			<xsl:value-of select="ects_gesamt"/>
        </text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Table12.F5" office:value-type="string">
        <text:p text:style-name="P65">
		-
        </text:p>
      </table:table-cell>
    </table:table-row>

  </xsl:template>
    <xsl:template match="lv">
    <table:table-row xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" table:style-name="Table12.1">
      <table:table-cell table:style-name="Table12.A5" office:value-type="string">
        <text:p text:style-name="P20">
          <xsl:value-of select="benotungsdatum"/>
        </text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Table12.B5" office:value-type="string">
        <text:p text:style-name="P58">
          <xsl:value-of select="bezeichnung_englisch"/>
        </text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Table12.C5" office:value-type="string">
        <text:p text:style-name="P65">
          <xsl:value-of select="lehrform_kurzbz"/>
        </text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Table12.D5" office:value-type="string">
        <text:p text:style-name="P65">
          <xsl:value-of select="sws"/>
        </text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Table12.E5" office:value-type="string">
        <text:p text:style-name="P65">
          <xsl:value-of select="ects"/>
        </text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Table12.F5" office:value-type="string">
        <text:p text:style-name="P65">
          <xsl:value-of select="note"/>
        </text:p>
      </table:table-cell>
    </table:table-row>
  </xsl:template>

</xsl:stylesheet>
