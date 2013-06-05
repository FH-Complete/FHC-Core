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
      <style:table-properties style:width="17.02cm" table:align="left" style:writing-mode="lr-tb"/>
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
      <style:table-row-properties style:row-height="1.314cm" fo:keep-together="auto"/>
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
      <style:table-row-properties style:row-height="1.632cm" fo:keep-together="auto"/>
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
      <style:table-properties style:width="17.02cm" fo:break-before="page" table:align="left" style:writing-mode="lr-tb"/>
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
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table9.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#afb8bc" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Table10" style:family="table">
      <style:table-properties style:width="17.074cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table10.A" style:family="table-column">
      <style:table-column-properties style:column-width="1.752cm"/>
    </style:style>
    <style:style style:name="Table10.B" style:family="table-column">
      <style:table-column-properties style:column-width="15.323cm"/>
    </style:style>
    <style:style style:name="Table10.1" style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table10.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table10.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table11" style:family="table">
      <style:table-properties style:width="17.074cm" table:align="left" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table11.A" style:family="table-column">
      <style:table-column-properties style:column-width="1.752cm"/>
    </style:style>
    <style:style style:name="Table11.B" style:family="table-column">
      <style:table-column-properties style:column-width="15.323cm"/>
    </style:style>
    <style:style style:name="Table11.1" style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:style>
    <style:style style:name="Table11.A1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #000000" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="Table11.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.191cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.5pt solid #000000" fo:border-right="none" fo:border-top="0.5pt solid #000000" fo:border-bottom="0.5pt solid #000000" style:writing-mode="lr-tb"/>
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
      <style:table-row-properties fo:keep-together="auto"/>
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
      <style:text-properties fo:color="#000000" fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:language-asian="de" style:country-asian="AT" style:font-name-complex="Arial" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="9.5pt" fo:language="en" fo:country="US" style:font-size-asian="9.5pt" style:font-size-complex="9.5pt"/>
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
      <style:text-properties fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P25" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:language-asian="de" style:country-asian="AT" style:font-size-complex="10pt"/>
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
      <style:text-properties fo:font-size="9.5pt" style:font-size-asian="9.5pt" style:font-size-complex="9.5pt"/>
    </style:style>
    <style:style style:name="P40" style:family="paragraph" style:parent-style-name="Heading_20_5">
      <style:text-properties fo:font-size="9.5pt" fo:language="en" fo:country="US" style:font-size-asian="9.5pt" style:font-size-complex="9.5pt"/>
    </style:style>
    <style:style style:name="P41" style:family="paragraph" style:parent-style-name="Heading_20_5">
      <style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
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
      <style:text-properties fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P50" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_2">
      <style:text-properties fo:font-size="9.5pt" fo:language="en" fo:country="US" style:font-size-asian="9.5pt" style:font-size-complex="9.5pt"/>
    </style:style>
    <style:style style:name="P51" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_2">
      <style:text-properties fo:font-size="9.5pt" fo:language="de" fo:country="AT" style:font-size-asian="9.5pt" style:font-size-complex="9.5pt"/>
    </style:style>
    <style:style style:name="P52" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
      <style:text-properties fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P53" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
      <style:text-properties fo:font-size="9.5pt" fo:language="en" fo:country="US" style:font-size-asian="9.5pt" style:font-size-complex="9.5pt"/>
    </style:style>
    <style:style style:name="P54" style:family="paragraph" style:parent-style-name="Formatvorlage_20_Aufzählung_20_1">
      <style:paragraph-properties fo:margin-left="0.4cm" fo:margin-right="0cm" fo:text-indent="-0.4cm" style:auto-text-indent="false"/>
      <style:text-properties fo:font-size="10pt" fo:language="de" fo:country="AT" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
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
      <text:p text:style-name="P31">ANHANG ZUM DIPLOM</text:p>
      <text:p text:style-name="P31">DIPLOMA SUPPLEMENT</text:p>
      <text:p text:style-name="P9"/>
      <text:p text:style-name="P59">
        <text:span text:style-name="Tabelleninhalt">
          <text:span text:style-name="T9"><xsl:value-of select="studiengang_typ"/></text:span>
        </text:span>
        <text:span text:style-name="T13">-Studiengang</text:span>
      </text:p>
      <text:p text:style-name="P59">
        <text:span text:style-name="Tabelleninhalt">
          <text:span text:style-name="T8"><xsl:value-of select="studiengang_bezeichnung_deutsch"/></text:span>
        </text:span>
      </text:p>
      <text:p text:style-name="P11"/>
      <text:p text:style-name="P4"><text:span text:style-name="Tabelleninhalt"><text:span text:style-name="T10"><xsl:value-of select="studiengang_typ"/></text:span></text:span>’s degree program</text:p>
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
            <text:p text:style-name="Standard">Dieser Anhang zum Diplom wurde nach dem von der Europäischen Kommission, dem Europarat und UNESCO/CEPES entwickelten Modell erstellt. Mit dem Anhang wird das Ziel verfolgt, ausreichend unabhängige Daten zu erfassen, um die internationale "Transparenz" und die angemessene akademische und berufliche Anerkennung von Qualifikationen (Diplomen, Abschlüssen, Zeugnissen usw.) zu verbessern. Der Anhang soll eine Beschreibung über Art, Niveau, Kontext, Inhalt und Status eines Studiums bieten, den die im Original-Befähigungsnachweis, dem der Anhang beigefügt ist, genannte Person absolviert und erfolgreich abgeschlossen hat. Der Anhang sollte keinerlei Werturteile, Aussagen über Gleichwertigkeit mit anderen Qualifikationen oder Vorschläge bezüglich der Anerkennung enthalten. Zu allen acht Punkten sollten Angaben gemacht werden. Werden zu einem Punkt keine Angaben gemacht, sollte der Grund dafür angegeben werden.</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table1.B1" office:value-type="string">
            <text:p text:style-name="P12">This diploma supplement follows the model developed by the European Commission, Council of Europe and UNESCO/CEPES. The purpose of the supplement is to provide sufficient independent data to improve the international transparency and fair academic and professional recognition of qualifications (diplomas, degrees, certificates, etc.). It is designed to provide a description of the nature, level, context, content and status of the studies that were pursued and successfully completed by the individual named on the original qualification to which this supplement is appended. It should be free from any value judgments, equivalence statements or suggestions about recognition. Information in all eight sections should be provided. Where information is not provided, an explanation should give the reason why.</text:p>
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
              <text:span text:style-name="Tabelleninhalt">Name der Qualifikation und verliehener Titel</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Name of qualification, title conferred</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table3.C2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="titel_de"/></text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="titel_en"/></text:span>
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
                <text:span text:style-name="T1">Name and status of insti</text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt"> </text:span>
              <text:span text:style-name="Tabelleninhalt">tution administering studies</text:span>
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
      </table:table>
      <table:table table:name="Table4" table:style-name="Table4">
        <table:table-column table:style-name="Table4.A"/>
        <table:table-column table:style-name="Table4.B"/>
        <table:table-column table:style-name="Table4.C"/>
        <table:table-row table:style-name="Table4.1">
          <table:table-cell table:style-name="Table4.A1" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">3.</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.B1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">Angaben zum Niveau der Qualifikation</text:p>
            <text:p text:style-name="Tabellenkopf">Informationen on the level of the qualification</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table4.2">
          <table:table-cell table:style-name="Table4.A2" office:value-type="string">
            <text:p text:style-name="Tabelleninhalt_20_neu">
              <text:span text:style-name="Tabelleninhalt">3.1</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.B2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Niveau der Qualifikation</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Level of qualification</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.C2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="niveau_deutsch"/></text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="niveau_englisch"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table4.3">
          <table:table-cell table:style-name="Table4.A3" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">3.2</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.B3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Regelstudienzeit (gesetzliche Studiendauer)</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Official length of program</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.C3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="semester"/> Semester | <xsl:value-of select="jahre"/> Jahre | <xsl:value-of select="ects"/> ECTS</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="semester"/> semester(s) | <xsl:value-of select="jahre"/> year(s) | <xsl:value-of select="ects"/> ECTS</text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table4.4">
          <table:table-cell table:style-name="Table4.A4" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">3.3</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.B4" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Zulassungsvoraussetzungen</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Access requirement(s)</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table4.C4" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="zulassungsvoraussetzungen_deutsch"/></text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="zulassungsvoraussetzungen_englisch"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="Standard"/>
      <table:table table:name="Table5" table:style-name="Table5">
        <table:table-column table:style-name="Table5.A"/>
        <table:table-column table:style-name="Table5.B"/>
        <table:table-column table:style-name="Table5.C"/>
        <table:table-column table:style-name="Table5.D"/>
        <table:table-column table:style-name="Table5.A"/>
        <table:table-column table:style-name="Table5.F"/>
        <table:table-row table:style-name="Table5.1">
          <table:table-cell table:style-name="Table5.A1" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">4.</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.B1" table:number-columns-spanned="5" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">Angaben über den Inhalt und die erzielten Ergebnisse</text:p>
            <text:p text:style-name="Tabellenkopf">Information on the contents and results gained</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table5.2">
          <table:table-cell table:style-name="Table5.A2" office:value-type="string">
            <text:p text:style-name="Tabelleninhalt_20_neu">
              <text:span text:style-name="Tabelleninhalt">4.1</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.B2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Studienart / Mode of Study</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.C2" table:number-columns-spanned="4" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="studienart"/></text:span>
            </text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table5.3">
          <table:table-cell table:style-name="Table5.A3" office:value-type="string">
            <text:p text:style-name="Tabelleninhalt_20_neu">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T1">4.2</text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt"> </text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.B3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Anforderungen des Studiums</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Program requirements</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.C3" table:number-columns-spanned="4" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="anforderungen_deutsch"/></text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="anforderungen_englisch"/></text:span>
            </text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table5.4">
          <table:table-cell table:style-name="Table5.A4" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">4.3</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.B4" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Angaben zum Studium (z.B absolvierte Module und Einheiten) und erzielte Noten/Bewertungen/ECTS Anrechnungspunkte</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Program details (courses, modules or units of studied, individual grades obtained)</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.C4" table:number-columns-spanned="4" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="ects"/> ECTS</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Siehe "Studiendaten"</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">See "Transcript of Records"</text:span>
            </text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table5.5">
          <table:table-cell table:style-name="Table5.A13" table:number-rows-spanned="9" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">4.4</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.B13" table:number-rows-spanned="9" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Notenskala</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Grading scheme, grade translation and grade distribution guidance</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"/>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"/>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.C5" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Nationale Notenskala</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Local Grades</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.D5" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Definition</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.E5" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T2">%-</text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt"> </text:span>
              <text:span text:style-name="Tabelleninhalt">ave 1</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.F5" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T2">%-</text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt"> </text:span>
              <text:span text:style-name="Tabelleninhalt">ave 2</text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table5.6">
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table5.C6" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">1</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.D6" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Sehr gut – Hervorragende Leistung</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Excellent work</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.E6" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradeLastYear1"/></text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.F6" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradePrevLastYear1"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table5.7">
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table5.C7" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">2</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.D7" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Gut – Generell gut, einige Fehler </text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Generally good, some mistakes</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.E7" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradeLastYear2"/></text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.F7" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradePrevLastYear2"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table5.8">
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table5.C8" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">3</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.D8" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Befriedigend – Ausgewogen, einige entscheidende Fehler</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Some major mistakes, Satisfactory</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.E8" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradeLastYear3"/></text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.F8" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradePrevLastYear3"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table5.9">
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table5.C9" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">4</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.D9" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Genügend – Leistung entsprechend den Minimalkriterien</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Work meeting minimal criteria</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.E9" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradeLastYear4"/></text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.F9" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradePrevLastYear4"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table5.10">
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table5.C10" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">5</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.D10" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Nicht Genügend – Erfordert weitere Arbeit</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">More work is required</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.E10" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradeLastYear5"/></text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.F10" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradePrevLastYear5"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table5.11">
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table5.C11" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">TG/Ea</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.D11" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Mit Erfolg Teilgenommen </text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Successful Participation</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.E11" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradeLastYearEa"/></text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.F11" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradePrevLastYearEa"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table5.12">
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table5.C12" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">AR</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.D12" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Angerechnet auf Basis von Vorleistungen</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Credited based on previous experience/work</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.E12" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradeLastYearAr"/></text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.F12" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradePrevLastYearAr"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table5.11">
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Table5.C13" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Nb</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.D13" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T2">Nicht Beurteilt</text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt"> </text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T2">Not Graded</text:span>
              </text:span>
              <text:span text:style-name="Tabelleninhalt"> </text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.E13" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradeLastYearNb"/></text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.F13" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="gradePrevLastYearNb"/></text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table5.14">
          <table:table-cell table:style-name="Table5.A14" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">4.5</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.B14" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Gesamtbeurteilung der Qualifikation</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Overall classification of the qualification</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table5.C14" table:number-columns-spanned="4" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="beurteilung"/></text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="beurteilung_english"/></text:span>
            </text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P12"/>
      <table:table table:name="Table6" table:style-name="Table6">
        <table:table-column table:style-name="Table6.A"/>
        <table:table-column table:style-name="Table6.B"/>
        <table:table-column table:style-name="Table6.C"/>
        <table:table-row table:style-name="Table6.1">
          <table:table-cell table:style-name="Table6.A1" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">5.</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table6.B1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">Angaben zur Funktion der Qualifikation</text:p>
            <text:p text:style-name="Tabellenkopf">Information on the function of the qualification</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table6.2">
          <table:table-cell table:style-name="Table6.A2" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">5.1</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table6.B2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Zugangsberechtigung zu weiterführenden Studien</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Access to further study</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table6.C2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="zugangsberechtigung_deutsch"/></text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="zugangsberechtigung_englisch"/> </text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table6.3">
          <table:table-cell table:style-name="Table6.A3" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">5.2</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table6.B3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Beruflicher Status</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Professional status conferred</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table6.C3" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Zugang zu akademischen Berufen nach Maßgabe der berufsrechtlichen Vorschriften; Diplom im Sinne der Richtlinie 89/48/EWG.</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Access to academic professions according to the professional regulation; Diploma in the sense of directive RL 89/48/EEC.</text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P12"/>
      <table:table table:name="Table7" table:style-name="Table7">
        <table:table-column table:style-name="Table7.A"/>
        <table:table-column table:style-name="Table7.B"/>
        <table:table-column table:style-name="Table7.C"/>
        <table:table-row table:style-name="Table7.1">
          <table:table-cell table:style-name="Table7.A1" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">6.</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table7.B1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">Sonstige Angaben</text:p>
            <text:p text:style-name="Tabellenkopf">Additional information</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table7.2">
          <table:table-cell table:style-name="Table7.A2" office:value-type="string">
            <text:p text:style-name="P42">
              <text:span text:style-name="Tabelleninhalt">6.1</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table7.B2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Weitere Angaben</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Additional information</text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table7.C2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T2"><xsl:value-of select="praktikum"/> </text:span>
              </text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">
                <text:span text:style-name="T2"><xsl:value-of select="auslandssemester"/></text:span>
              </text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table7.3">
          <table:table-cell table:style-name="Table7.A3" office:value-type="string">
            <text:p text:style-name="Tabelleninhalt_20_neu">
              <text:span text:style-name="Tabelleninhalt">6.2</text:span>
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
              <text:span text:style-name="Tabelleninhalt">www.aq.ac.at</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">www.bmwf.gv.at/home/academic_mobility/enic_naric_austria/</text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <table:table table:name="Table8" table:style-name="Table8">
        <table:table-column table:style-name="Table8.A"/>
        <table:table-column table:style-name="Table8.B"/>
        <table:table-column table:style-name="Table8.C"/>
        <table:table-column table:style-name="Table8.D"/>
        <table:table-row table:style-name="Table8.1">
          <table:table-cell table:style-name="Table8.A1" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">7.</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table8.B1" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">Beurkundung des Anhangs</text:p>
            <text:p text:style-name="Tabellenkopf">Certification of the supplement</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table8.2">
          <table:table-cell table:style-name="Table8.A2" office:value-type="string">
            <text:p text:style-name="P43">
              <text:span text:style-name="Tabelleninhalt"/>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table8.B2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Datum</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Date</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><text:s/><xsl:value-of select="sponsion_datum"/></text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table8.C2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Studiengangsleitung</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Program Director</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt"><xsl:value-of select="stgl"/></text:span>
            </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table8.D2" office:value-type="string">
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Rundsiegel</text:span>
            </text:p>
            <text:p text:style-name="P44">
              <text:span text:style-name="Tabelleninhalt">Official stamp</text:span>
            </text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P12"/>
      <table:table table:name="Table9" table:style-name="Table9">
        <table:table-column table:style-name="Table9.A"/>
        <table:table-column table:style-name="Table9.B"/>
        <table:table-row table:style-name="Table9.1">
          <table:table-cell table:style-name="Table9.A1" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">8.</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table9.B1" office:value-type="string">
            <text:p text:style-name="Tabellenkopf">Angaben zum nationalen Hochschulsystem (siehe Anhang)</text:p>
            <text:p text:style-name="Tabellenkopf">Information on the Austrian higher education system (see appendix)</text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
	<text:p text:style-name="Standard"></text:p>
	<text:p text:style-name="Standard">Siehe folgende Seiten</text:p>
      	<text:p text:style-name="Standard">See following pages</text:p>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P56">ANHANG: Angaben zum nationalen Hochschulsystem</text:p>
      <text:p text:style-name="P24"/>
      <text:p text:style-name="P41">Der postsekundäre Sektor in Österreich</text:p>
      <text:list xml:id="list2030519318" text:style-name="WW8Num16">
        <text:list-item>
          <text:p text:style-name="P52">In Österreich umfasst der postsekundäre Sektor auf Universitätsniveau („Hochschulsektor")</text:p>
        </text:list-item>
      </text:list>
      <text:list xml:id="list2065892677" text:style-name="WW8Num13">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:p text:style-name="P49">die Öffentlichen Universitäten, erhalten vom Staat;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">die Privatuniversitäten, erhalten von privaten Trägern mit staatlicher Akkreditierung;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">die Erhalter von Fachhochschul-Studiengängen, erhalten von privatrechtlich organisierten und</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">staatlich subventionierten oder von öffentlichen Trägern, mit staatlicher Akkreditierung (manche Trägern wurde die Berechtigung zur Führung der Bezeichnung „Fachhochschule" verliehen);</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">die Pädagogischen Hochschulen, erhalten vom Staat oder von privaten Trägern mit staatlicher</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">Akkreditierung;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">die Philosophisch-Theologischen Hochschulen, erhalten von der Katholischen Kirche</text:p>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P14"/>
      <text:list xml:id="list1154796277" text:continue-list="list2030519318" text:style-name="WW8Num16">
        <text:list-item>
          <text:p text:style-name="P52">Der außeruniversitäre postsekundäre Sektor umfasst</text:p>
        </text:list-item>
      </text:list>
      <text:list xml:id="list282475735" text:continue-list="list2065892677" text:style-name="WW8Num13">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:p text:style-name="P49">die Hebammenakademien;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">die Medizinisch-Technischen Akademien;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">die Militärischen Akademien;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">die Diplomatische Akademie;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">bestimmte Psychotherapeutischen Ausbildungseinrichtungen;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">die Konservatorien;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P49">bestimmte Wirtschaftsschulen.</text:p>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P14"/>
      <text:p text:style-name="P24">Im Folgenden wird ausschließlich auf den „Hochschulsektor" eingegangen.</text:p>
      <text:p text:style-name="P24"/>
      <text:p text:style-name="P41">Allgemeine Struktur des Hochschulwesens</text:p>
      <text:p text:style-name="P24">Es gibt ein altes und ein neues System der österreichischen ordentlichen Studien: das alte ohne Bezug zum Bologna-Prozess und das neue mit Bezug dazu.</text:p>
      <text:p text:style-name="P14"/>
      <text:list xml:id="list1370974139" text:continue-list="list1154796277" text:style-name="WW8Num16">
        <text:list-item>
          <text:p text:style-name="P52">Das alte System ist das der Diplomstudien, die grundsätzlich auf der Basis einer Reifeprüfung begonnen werden und deren Abschluss zur Aufnahme eines Doktoratsstudiums berechtigt. Ein Diplomgrad wird von den Universitäten nach einem Diplomstudium mit 240 bis 360 ECTS-Credits verliehen. Der volle Wortlaut ist „Magister/Magistra ..." samt einer fachspezifischen Beifügung, z.B. „Magister philosophiae". In den ingenieurwissenschaftlichen Studien ist der Wortlaut „Diplom-Ingenieur/in". Das Studium der Humanmedizin und der Zahnmedizin sind Ausnahmen: Hier wird als erster akademischer Grad Doctor medicinae universae" bzw. „Doctor medicinae dentalis" nach einem Diplomstudium mit 360 ECTS-Credits verliehen.</text:p>
          <text:p text:style-name="P54">In Fachhochschul-Studiengängen wird, analog zu den Universitätsstudien, ein Fachhochschul-Diplomgrad („Diplom-Ingenieur/in (FH)" im ingenieurwissenschaftlichen Bereich bzw. „Magister/Magistra (FH)" in den anderen Bereichen; 240 bis 300 ECTS-Credits) verliehen. </text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P52"><text:soft-page-break/>Das neue System folgt der Trennung zwischen einem Undergraduate-Studium und einem Graduate-Studium. Nach Beendigung des Undergraduate-Studiums (Bachelorstudium an Universitäten; Fachhochschul-Bachelorstudiengang; Studiengang an Pädagogischen Hochschulen; 180 ECTS-Credits) wird ein Bachelorgrad (mit dem Wortlaut „Bachelor of/in ...") verliehen. Nach Beendigung des Graduate-Studiums (Masterstudium an Universitäten mit 120 ECTS-Credits bzw. Fachhochschul-Masterstudiengang mit 60 bis 120 ECTS-Credits) wird ein Mastergrad (mit dem Wortlaut „Master of/in ...") verliehen. In ingenieurwissenschaftlichen Graduate-Studien kann der Mastergrad auch „Diplom-Ingenieur/in" lauten.<text:line-break/></text:p>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P24">Die Inhaber/innen dieser Diplomgrade oder Mastergrade (einschließlich Fachhochschul-Diplomgraden oder Fachhochschul-Mastergraden) sind zur Zulassung zum Doktoratsstudium an einer Universität berechtigt. Der Doktorgrad (mit dem Wortlaut „Doktor/in ...") wird nach einem Studium mit 120 ECTS-Credits, der akademische Grad „Doctor of Philosophy" („PhD") nach einem forschungsorientierten Studium mit 180 bis 240 ECTS-Credits verliehen.</text:p>
      <text:p text:style-name="P16"/>
      <text:p text:style-name="P25">Neben den ordentlichen Studien, die oben beschrieben wurden, gibt es auch außerordentliche Studien, die an Universitäten entweder ein Universitätslehrgang oder der Besuch einzelner Lehrveranstaltungen, im Fachhochschulbereich ein Lehrgang zur Weiterbildung und an Pädagogischen Hochschulen ein Hochschullehrgang sein können.</text:p>
      <text:p text:style-name="P24"/>
      <text:p text:style-name="P41">Diplomstudium</text:p>
      <text:p text:style-name="P24">Die Zulassung zu einem Diplomstudium erfolgt auf der Grundlage eines österreichischen oder gleichwertigen ausländischen Reifezeugnisses, eines Zeugnisses über die Studienberechtigungsprüfung oder eines Zeugnisses über die Berufsreifeprüfung, in künstlerischen Studien auf der Grundlage einer Zulassungsprüfung. Die Zulassung zu einem Fachhochschul-Diplomstudiengang kann auch auf der Grundlage einer einschlägigen beruflichen Qualifikation erfolgen. In einigen Studien (vor allem Humanmedizin und Zahnmedizin sowie in Fachhochschul-Diplomstudiengängen) findet ein Auswahlverfahren statt. Das Studium kann in Studienabschnitte unterteilt sein. Die Dauer jedes Studienabschnitts, die Fächer und ihre Inhalte sind im Curriculum festgelegt. Sie gliedern sich in Pflichtfächer und Wahlfächer. Jeder Studienabschnitt wird mit einer Diplomprüfung abgeschlossen. Fachhochschul-Diplomstudiengänge und einige Diplomstudien an Universitäten umfassen ein angeleitetes Praktikum. Die Zulassung zur letzten Diplomprüfung setzt die Approbation der Diplomarbeit voraus.</text:p>
      <text:p text:style-name="P24"/>
      <text:p text:style-name="P41">Bachelorstudium</text:p>
      <text:p text:style-name="P24">Die Zulassung zu einem Bachelorstudium erfolgt auf der Grundlage eines österreichischen oder gleichwertigen ausländischen Reifezeugnisses, eines Zeugnisses über die Studienberechtigungsprüfung oder eines Zeugnisses über die Berufsreifeprüfung, in künstlerischen Studien auf der Grundlage einer Zulassungsprüfung. Die Zulassung zu einem Fachhochschul-Bachelorstudiengang kann auch auf der Grundlage einer einschlägigen beruflichen Qualifikation erfolgen. In einigen Studien (vor allem in Fachhochschul-Bachelorstudiengänge und in Studiengängen an Pädagogischen Hochschulen) findet ein Auswahlverfahren statt. Die Fächer/Module und ihre Inhalte sind im Curriculum festgelegt. In der Regel sind zwei Bachelorarbeiten im Rahmen von Lehrveranstaltungen abzufassen. Fachhochschul-Bachelorstudiengänge und einige Bachelorstudien an Universitäten umfassen ein angeleitetes Praktikum. Das Studium kann mit einer Bachelorprüfung abgeschlossen werden.</text:p>
      <text:p text:style-name="P24"/>
      <text:p text:style-name="P41"><text:soft-page-break/>Masterstudium</text:p>
      <text:p text:style-name="P24">Die Zulassung zu einem Masterstudium erfolgt auf der Grundlage eines abgeschlossenen österreichischen Bachelorstudiums oder eines gleichwertigen postsekundären Abschlusses. Die Fächer/Module und ihre Inhalte sind im Curriculum festgelegt. Ein Schwerpunkt des Studiums liegt auf der Erstellung der Masterarbeit. Das Studium wird mit einer Masterprüfung abgeschlossen. Die Zulassung zur Masterprüfung setzt die Approbation der Masterarbeit voraus. An Pädagogischen Hochschulen gibt es kein Masterstudium.</text:p>
      <text:p text:style-name="P24"/>
      <text:p text:style-name="P41">Doktoratsstudium</text:p>
      <text:p text:style-name="P24">Die Zulassung zu einem Doktoratsstudium an einer Universität erfolgt auf der Grundlage eines</text:p>
      <text:p text:style-name="P24">abgeschlossenen österreichischen Diplom- oder Masterstudiums oder eines gleichwertigen postsekundären Abschlusses. Die Inhalte und Anforderungen sind im Curriculum festgelegt. Das Hauptgewicht liegt auf der Anfertigung einer Dissertation als Ergebnis einer selbstständigen wissenschaftlichen Forschungsleistung. Das Studium wird mit der Approbation der Dissertation und einem Rigorosum/einer Defensio abgeschlossen. Im Fachhochschulbereich und an Pädagogischen Hochschulen gibt es kein Doktoratsstudium.</text:p>
      <text:p text:style-name="P24"/>
      <text:p text:style-name="P41">Leistungsbewertung und Notensystem (Österreichische Notenskala)</text:p>
      <text:p text:style-name="P24">Entsprechend den in den Curricula geregelten Prüfungsmodalitäten kann die Bewertung der Leistungen in der Form mündlicher oder schriftlicher Prüfungen oder von Projektarbeiten erfolgen. Mündliche Prüfungen sind grundsätzlich öffentlich.</text:p>
      <text:p text:style-name="P14"/>
      <text:p text:style-name="P24">Österreichische Beurteilung:</text:p>
      <table:table table:name="Table10" table:style-name="Table10">
        <table:table-column table:style-name="Table10.A"/>
        <table:table-column table:style-name="Table10.B"/>
        <table:table-row table:style-name="Table10.1">
          <table:table-cell table:style-name="Table10.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P32">Positiv</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table10.1">
          <table:table-cell table:style-name="Table10.A2" office:value-type="string">
            <text:p text:style-name="P32">1</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table10.A1" office:value-type="string">
            <text:p text:style-name="P32">Sehr gut – Hervorragende Leistung</text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table10.1">
          <table:table-cell table:style-name="Table10.A2" office:value-type="string">
            <text:p text:style-name="P32">2</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table10.A1" office:value-type="string">
            <text:p text:style-name="P33">Gut – Generell gut, einige Fehler</text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table10.1">
          <table:table-cell table:style-name="Table10.A2" office:value-type="string">
            <text:p text:style-name="P32">3</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table10.A1" office:value-type="string">
            <text:p text:style-name="P33">Befriedigend – Ausgewogen, einige entscheidende Fehler</text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table10.1">
          <table:table-cell table:style-name="Table10.A2" office:value-type="string">
            <text:p text:style-name="P32">4</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table10.A1" office:value-type="string">
            <text:p text:style-name="P33">Genügend/Leistung entsprechen den Minimalkriterien</text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table10.1">
          <table:table-cell table:style-name="Table10.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P32">Negativ</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table10.1">
          <table:table-cell table:style-name="Table10.A2" office:value-type="string">
            <text:p text:style-name="P32">5</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table10.A1" office:value-type="string">
            <text:p text:style-name="P33">Nicht Genügend/Erfordert weitere Arbeit</text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P8"/>
      <text:p text:style-name="P56">APPENDIX: Information on the Austrian higher education system</text:p>
      <text:p text:style-name="P26"/>
      <text:p text:style-name="P39">Post-secondary Education in Austria</text:p>
      <text:list xml:id="list1050643529" text:continue-numbering="true" text:style-name="WW8Num16">
        <text:list-item>
          <text:p text:style-name="P53">The Austrian post-secondary university level sector (Hochschulsektor) consists of</text:p>
        </text:list-item>
      </text:list>
      <text:list xml:id="list695069009" text:continue-list="list282475735" text:style-name="WW8Num13">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:p text:style-name="P50">Public universities (Universitäten), maintained by the state;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P50">private universities (Privatuniversitäten), operated by private organizations with state accreditation;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P50">maintainers of university of applied sciences degree programs (Fachhochschul-Studiengänge) incorporated upon the basis of private or public law and subsidized by the state, with state accreditation (some of which are entitled to use the designation Fachhochschule);</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P50">university colleges of education (Pädagogische Hochschulen) maintained by the state or operated by private organizations with state accreditation;</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P50">universities of philosophy and theology (Philosophisch-Theologische Hochschulen), operated by the Roman Catholic Church.</text:p>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P15"/>
      <text:list xml:id="list471845522" text:continue-list="list1050643529" text:style-name="WW8Num16">
        <text:list-item>
          <text:p text:style-name="P53">The non-university post-secondary sector (außeruniversitärer postsekundärer Sektor) consists of</text:p>
        </text:list-item>
      </text:list>
      <text:list xml:id="list119325670" text:continue-list="list695069009" text:style-name="WW8Num13">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:p text:style-name="P50">academies for midwifery (Hebammenakademien);</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P50">clinical technical academies (Medizinisch-Technische Akademien);</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P50">military academies (Militärische Akademien);</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P50">the school of international studies (Diplomatische Akademie);</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P51">certain training institutions for psychotherapists (Psychotherapeutische Ausbildungseinrichtungen);</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P50">conservatories (Konservatorien);</text:p>
            </text:list-item>
            <text:list-item>
              <text:p text:style-name="P50">certain business schools (Wirtschaftsschulen).</text:p>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P15"/>
      <text:p text:style-name="P17">The following text addresses exclusively the university level sector.</text:p>
      <text:p text:style-name="P17"/>
      <text:p text:style-name="P39">Overall Structure of University Level Higher Education</text:p>
      <text:p text:style-name="P17">There are currently two different systems of degree programs in Austria: an older system without reference to the Bologna process and a newer one with reference to it.</text:p>
      <text:p text:style-name="P15"/>
      <text:list xml:id="list1119633922" text:continue-list="list471845522" text:style-name="WW8Num16">
        <text:list-item>
          <text:p text:style-name="P53">Under the auspices of the older system of diploma degree programs (Diplomstudien), the first degree awarded is the diploma degree (Diplomgrad). An Austrian higher secondary school leaving certificate or its equivalent is the general qualification necessary for enrolling in a diploma degree programs; conclusion of a diploma degree program entitles degree holders to enroll in doctoral degree programs. A diploma degree (Diplomgrad) is awarded by Austrian universities after a course of study consisting of 240 to 360 ECTS credits. Full degree titles are gender specific designations: Magister for men; Magistra for women. Degree titles also include a general description of the field of study in which they were obtained, e.g. Magister philosophiae. In the fields of engineering, the degree titles are Diplom-Ingenieur/in. Degrees awarded in medicine and dentistry are exceptions to the above. The first degrees awarded after the completion of these degree programs consisting of 360 ECTS credits are Doctor medicinae universae and Doctor medicinae dentalis, respectively. Graduates of university of applied sciences programs that consist of 240 to 300 ECTS credits are awarded, analogous to university studies, a university of applied science diploma degree (Fachhochschul-Diplomgrad) contingent upon <text:soft-page-break/>discipline: either a Diplom-Ingenieur/in (FH) for fields of engineering or Magister/Magistra (FH) in other fields of study.</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P53">The new system is based on the distinction between undergraduate and graduate studies. Upon completion of an undergraduate program (Bachelorstudium at universities; Fachhochschul-Bachelorstudiengang; Studiengang at university colleges of education; 180 ECTS credits), a bachelor’s degree (designation: Bachelor of/in ..." ) is awarded. Upon completion of a graduate program (Masterstudium at universities comprising 120 ECTS credits or, respectively, Fachhochschul-Masterstudiengang comprising 60 to 120 ECTS credits), a master’s degree (designation: „Master of/in ..." ) is awarded. In the fields of engineering, the designation of the master’s degree can also be „Diplom-Ingenieur/in". </text:p>
        </text:list-item>
      </text:list>
      <text:p text:style-name="P17"/>
      <text:p text:style-name="P17">Recipients of these diploma degrees from the old system or master’s degrees from the new system (including the ones awarded in both cases by the universities of applied sciences) are entitled to enroll in doctoral degree programs (Doktoratsstudium) at universities. A doctoral degree (Doktorgrad with the designation Doktor/in") is awarded after a course of study consisting of 120 ECTS credits; the academic title of "Doctor of Philosophy", abbreviated as "PhD," is awarded after a research intensive course of study consisting of 180 to 240 ECTS credits.</text:p>
      <text:p text:style-name="P17"/>
      <text:p text:style-name="P17">In addition to the degree programs (ordentliche Studien) described above, there are non-degree</text:p>
      <text:p text:style-name="P17">programs (außerordentliche Studien) consisting of certificate university programs for further education (Universitätslehrgänge) and individual courses in scientific subjects, both at universities, certificate university of applied sciences programs for further education (Lehrgänge zur Weiterbildung) at universities of applied sciences, and certificate university college programs for further education (Hochschullehrgänge) at university colleges of education.</text:p>
      <text:p text:style-name="P17"/>
      <text:p text:style-name="P40">Diploma Degree Program (Diplomstudium)</text:p>
      <text:p text:style-name="P17">Admission to a diploma degree program is granted upon the basis of the Austrian higher secondary school leaving certificate (Reifezeugnis), its foreign equivalent, or the successful completion of a special university entrance qualification examination (Studienberechtigungsprüfung). Students of compulsory lower schools who have completed additional schooling in the form of apprenticeships as skilled workers also may take a vocationally based examination acknowledged as equivalent to the higher secondary school leaving certificate (Berufsreifeprüfung). Admission to diploma degree programs in the arts is based on aptitude ascertained by admission examinations. Admission to university of applied sciences diploma degree programs may also take place upon the basis of previous vocational or technical experience and qualifications of applicants. In some fields of study (in particular human medicine and dentistry, and university of applied sciences diploma degree programs) admission is based on a selective admission process. A degree program may be divided into stages (Studienabschnitte). The length of each stage of the degree program as well as the areas of study (Fächer) and content required are articulated in curricula that distinguish between required subjects (Pflichtfächer) and electives (Wahlfächer). Each stage concludes with a diploma examination (Diplomprüfung). University of applied sciences diploma degree programs and some diploma degree programs at universities include an internship or practical training. The approval of a diploma thesis (Diplomarbeit) is a prerequisite for admission to the concluding diploma examination.</text:p>
      <text:p text:style-name="P17"/>
      <text:p text:style-name="P39">Bachelor Degree Program (Bachelorstudium)</text:p>
      <text:p text:style-name="P17">Admission to a bachelor degree program is granted upon the basis on the Austrian higher secondary school leaving certificate (Reifezeugnis), its foreign equivalent, or the successful completion of a special university entrance qualification examination (Studienberechtigungsprüfung). Students of compulsory lower schools who have completed additional schooling in the form of apprenticeships as skilled workers may take a vocationally <text:soft-page-break/>based examination acknowledged as equivalent to the higher secondary school leaving certificate (Berufsreifeprüfung). Admission to bachelor degree programs in the arts is based on aptitude ascertained by admission examinations. Admission to university of applied sciences bachelor degree programs may also take place upon the basis of previous vocational or technical experience and qualifications of applicants. In some fields of study (in particular university of applied sciences bachelor degree programs and study programs at university colleges of education) admission is based on a selective admission process. Areas/Modules of study (Fächer/Module) are laid down in curricula. As a rule, two substantial bachelor’s papers or projects (Bachelorarbeiten) must be completed in the process of completing degree program requirements. University of applied sciences bachelor degree programs and some bachelor degree programs at universities include an internship or practical training. The program can concludes with a bachelor’s examination (Bachelorprüfung). </text:p>
      <text:p text:style-name="P17"/>
      <text:p text:style-name="P40">Master Degree Program (Masterstudium)</text:p>
      <text:p text:style-name="P17">Admission to a master degree program is granted on the basis of the successful completion of an Austrian bachelor degree program (Bachelorstudium), or a comparable post-secondary degree acknowledged being its equivalent. Areas/Modules of study (Fächer/Module) are laid down in curricula. A main emphasis is the composition of a master’s thesis (Masterarbeit). This degree program concludes with a master’s examination (Masterprüfung). The approval of the master’s thesis (Masterarbeit) is a prerequisite for admission to this examination. At university colleges of education no master degree programs are offered.</text:p>
      <text:p text:style-name="P17"/>
      <text:p text:style-name="P40">Doctoral Degree Program (Doktoratsstudium)</text:p>
      <text:p text:style-name="P17">Admission to a doctoral degree program at a university is granted on the basis of the successful completion of an Austrian diploma or master degree program, or a comparable post-secondary degree acknowledged being their equivalents. Contents and requirements of study are laid down in curricula. The focus lies with the drafting of a dissertation as the result of self-guided research performance. This degree program concludes with the approval of the dissertation and with a comprehensive doctoral examination (Rigorosum) or a defensio. At universities of applied sciences an at university colleges of education no doctoral degree programs are offered.</text:p>
      <text:p text:style-name="P17"/>
      <text:p text:style-name="P39">Evaluation of performance and grading system (Austrian grading scale) </text:p>
      <text:p text:style-name="P17">According to the modalities for examinations outlined in the curricula, achievement may be evaluated upon the basis of oral and written exams or project related work. In principle oral examinations are open to the public.</text:p>
      <text:p text:style-name="P15"/>
      <text:p text:style-name="P17">Austrian Grading Scheme</text:p>
      <table:table table:name="Table11" table:style-name="Table11">
        <table:table-column table:style-name="Table11.A"/>
        <table:table-column table:style-name="Table11.B"/>
        <table:table-row table:style-name="Table11.1">
          <table:table-cell table:style-name="Table11.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P32">Positive</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table11.1">
          <table:table-cell table:style-name="Table11.A2" office:value-type="string">
            <text:p text:style-name="P32">1</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table11.A1" office:value-type="string">
            <text:p text:style-name="P32"><text:span text:style-name="T4">"</text:span>Sehr gut " – Excellent work</text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table11.1">
          <table:table-cell table:style-name="Table11.A2" office:value-type="string">
            <text:p text:style-name="P32">2</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table11.A1" office:value-type="string">
            <text:p text:style-name="P32"><text:span text:style-name="T4">"</text:span>Gut" – Generally good, some mistakes </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table11.1">
          <table:table-cell table:style-name="Table11.A2" office:value-type="string">
            <text:p text:style-name="P32">3</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table11.A1" office:value-type="string">
            <text:p text:style-name="P32"><text:span text:style-name="T4">"</text:span>Befriedigend" – Some major mistakes, satisfactory </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table11.1">
          <table:table-cell table:style-name="Table11.A2" office:value-type="string">
            <text:p text:style-name="P32">4</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table11.A1" office:value-type="string">
            <text:p text:style-name="P32"><text:span text:style-name="T4">"</text:span>Genügend" – Work meeting minimal criteria </text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row table:style-name="Table11.1">
          <table:table-cell table:style-name="Table11.A1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P32">Negative</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Table11.1">
          <table:table-cell table:style-name="Table11.A2" office:value-type="string">
            <text:p text:style-name="P32">5</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table11.A1" office:value-type="string">
            <text:p text:style-name="P32"><text:span text:style-name="T4">"</text:span>Nicht Genügend" – more work is required</text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P13"/>       
      
      
		<text:p text:style-name="P48">
        <draw:frame xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" draw:style-name="fr2" draw:name="graphics3" text:anchor-type="paragraph" svg:width="17cm" svg:height="24.042cm" draw:z-index="14" >
          <draw:image xlink:href="Pictures/100000000000092300000CECE56EC0B3.tif" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
        </draw:frame>
      </text:p>
      

        <text:span text:style-name="T12">TRANSCRIPT OF RECORDS</text:span>

      <text:p text:style-name="P5">Semester <xsl:value-of select="start_semester_number"/> - <xsl:value-of select="end_semester_number"/></text:p>
      <text:p text:style-name="P6"/>
      <text:p text:style-name="P7"><xsl:value-of select="studiengang_typ"/>’s degree program</text:p>
      <text:p text:style-name="P5"><xsl:value-of select="studiengang_bezeichnung_englisch"/><text:line-break/></text:p>
      <text:p text:style-name="P6"/>
      <text:p text:style-name="P34"><text:span text:style-name="T4"><text:s text:c="13"/></text:span>Student ID: <xsl:value-of select="matrikelnummer"/> </text:p>
      <text:p text:style-name="P34"><text:span text:style-name="T4"><text:s text:c="14"/></text:span>Program Code: <xsl:value-of select="studiengang_kz"/></text:p>
      <text:p text:style-name="P18"/>
      <text:p text:style-name="P18"/>
      <text:p text:style-name="P18"/>
      <text:p text:style-name="P18"/>
      <text:p text:style-name="P20"/>
      <text:p text:style-name="P21">First Name/Last Name: <xsl:value-of select="name"/></text:p>
      <text:p text:style-name="P20"/>
      <text:p text:style-name="P20">Date of Birth: <xsl:value-of select="geburtsdatum"/></text:p>
      <text:p text:style-name="P20"/>
      <text:p text:style-name="P21">Within the period of studies at the University of Applied Science Technikum Wien from <xsl:value-of select="start_semester"/> to <xsl:value-of select="end_semester"/> in the <xsl:value-of select="studiengang_typ"/>’s degree program <xsl:value-of select="studiengang_bezeichnung_englisch"/> examinations in the following subjects were passed:</text:p>
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
            <text:p text:style-name="P70"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.C5" office:value-type="string">
            <text:p text:style-name="P71"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.D5" office:value-type="string">
            <text:p text:style-name="P71"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.E5" office:value-type="string">
            <text:p text:style-name="P65"><xsl:value-of select="ects_total"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Table12.F5" office:value-type="string">
            <text:p text:style-name="P71"/>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P19"/>
      <text:p text:style-name="P19"/>
      <text:p text:style-name="P19"/>
      <text:p text:style-name="P23">¹ Type: Laboratory (LAB), Lecture (VO), Integrated Course (ILV), Seminar (SE), Tutorial (TUT), Project (PRJ), Exercise (UE), Distance Learning (FL), Other (SO)</text:p>
      <text:p text:style-name="P23">² 1 Semester period per week = 45 minutes</text:p>
      <text:p text:style-name="P23">³ Grading Scheme: excellent (1), very good (2), good (3), satisfactory (4), fail (5), not graded (nb), Credit based on previous experience/work (ar), successfully completed (ea), not successfully completed (nea), Participated with success (met), participated (tg)</text:p>
      <text:p text:style-name="P18">
        <text:soft-page-break/>
      </text:p>
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
            <text:p text:style-name="P36">Place, Date</text:p>
            <text:p text:style-name="P10"/>
          </table:table-cell>
          <table:table-cell table:style-name="Table13.A1" office:value-type="string">
            <text:p text:style-name="P35"><text:s text:c="70"/></text:p>
            <text:p text:style-name="P37">
              <text:span text:style-name="T1"><xsl:value-of select="stgl"/><text:line-break/>Program Director</text:span>
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
          <xsl:value-of select="semesterKurzbz"/>
        </text:p>
      </table:table-cell>
      <table:covered-table-cell/>
      <table:covered-table-cell/>
      <table:covered-table-cell/>
      <table:covered-table-cell/>
      <table:covered-table-cell/>
    </table:table-row>
    <xsl:apply-templates select="lv"/>
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