<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="zeugnisse">

<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
  <office:scripts/>
  <office:font-face-decls>
    <style:font-face style:name="DejaVu Sans1" svg:font-family="'DejaVu Sans'" style:font-family-generic="swiss"/>
    <style:font-face style:name="Times New Roman" svg:font-family="'Times New Roman'" style:font-family-generic="roman" style:font-pitch="variable"/>
    <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="DejaVu Sans" svg:font-family="'DejaVu Sans'" style:font-family-generic="system" style:font-pitch="variable"/>
    <style:font-face style:name="Droid Sans" svg:font-family="'Droid Sans'" style:font-family-generic="system" style:font-pitch="variable"/>
  </office:font-face-decls>
  <office:automatic-styles>
    <style:style style:name="Lehrveranstaltungen" style:family="table">
      <style:table-properties style:width="17.006cm" fo:margin-left="0cm" table:align="left"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.A" style:family="table-column">
      <style:table-column-properties style:column-width="2.200cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.B" style:family="table-column">
      <style:table-column-properties style:column-width="0.326cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.C" style:family="table-column">
      <style:table-column-properties style:column-width="7.549cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.D" style:family="table-column">
      <style:table-column-properties style:column-width="1.362cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.E" style:family="table-column">
      <style:table-column-properties style:column-width="1.127cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.F" style:family="table-column">
      <style:table-column-properties style:column-width="0.915cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.G" style:family="table-column">
      <style:table-column-properties style:column-width="1.265cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.H" style:family="table-column">
      <style:table-column-properties style:column-width="1.342cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.I" style:family="table-column">
      <style:table-column-properties style:column-width="1.286cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.1" style:family="table-row">
      <style:table-row-properties style:min-row-height="0.4cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.A1" style:family="table-cell">
      <style:table-cell-properties fo:background-color="#b3b3b3" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.I1" style:family="table-cell">
      <style:table-cell-properties fo:background-color="#b3b3b3" fo:padding="0.097cm" fo:border="0.05pt solid #000000">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.2" style:family="table-row">
      <style:table-row-properties style:min-row-height="0.6cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.A2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#e6e6e6" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.D2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#e6e6e6" fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.I2" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#e6e6e6" fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #000000" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.A3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.147cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.B3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.C3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.D3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.E3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.F3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.G3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.H3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.I3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.A4" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.B4" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.C4" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.D4" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.E4" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.F4" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.G4" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.H4" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.I4" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.5" style:family="table-row">
      <style:table-row-properties style:row-height="0.527cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.A5" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="middle" fo:background-color="#b3b3b3" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.6" style:family="table-row">
      <style:table-row-properties style:row-height="0.524cm"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.A6" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.H6" style:family="table-cell">
      <style:table-cell-properties fo:background-color="transparent" fo:padding="0.097cm" fo:border="none">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Lehrveranstaltungen.I6" style:family="table-cell">
      <style:table-cell-properties fo:background-color="transparent" fo:padding="0.097cm" fo:border="none">
        <style:background-image/>
      </style:table-cell-properties>
    </style:style>
    <style:style style:name="Tabelle2" style:family="table">
      <style:table-properties style:width="17cm" table:align="margins"/>
    </style:style>
    <style:style style:name="Tabelle2.A" style:family="table-column">
      <style:table-column-properties style:column-width="8.5cm" style:rel-column-width="32767*"/>
    </style:style>
    <style:style style:name="Tabelle2.B" style:family="table-column">
      <style:table-column-properties style:column-width="8.5cm" style:rel-column-width="32768*"/>
    </style:style>
    <style:style style:name="Tabelle2.A1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
    </style:style>
    <style:style style:name="Tabelle2.B1" style:family="table-cell">
      <style:table-cell-properties style:vertical-align="bottom" fo:padding="0.097cm" fo:border="none"/>
    </style:style>
    <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="22pt" fo:font-weight="bold" style:font-size-asian="22pt" style:font-weight-asian="bold" style:font-size-complex="22pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="normal" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="normal" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="12pt" fo:font-weight="normal" style:font-size-asian="12pt" style:font-weight-asian="normal" style:font-size-complex="12pt" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="12pt" fo:font-weight="normal" style:font-size-asian="10.5pt" style:font-weight-asian="normal" style:font-size-complex="12pt" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="6pt" fo:font-weight="normal" style:font-size-asian="6pt" style:font-weight-asian="normal" style:font-size-complex="6pt" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="5.5pt" fo:font-weight="bold" style:font-size-asian="5.5pt" style:font-weight-asian="bold" style:font-size-complex="5.5pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P11" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="P12" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P13" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="12pt" style:font-size-asian="10.5pt" style:font-size-complex="12pt"/>
    </style:style>
    <style:style style:name="P14" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P15" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P16" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P17" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P18" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" fo:font-weight="bold" fo:background-color="transparent" style:font-size-asian="7pt" style:font-weight-asian="bold" style:font-size-complex="7pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P19" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" fo:font-weight="bold" fo:background-color="transparent" style:font-size-asian="7pt" style:font-weight-asian="bold" style:font-size-complex="7pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P20" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false" fo:background-color="transparent">
        <style:background-image/>
      </style:paragraph-properties>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P21" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P22" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P23" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P24" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="7pt" fo:font-weight="bold" fo:background-color="transparent" style:font-size-asian="7pt" style:font-weight-asian="bold" style:font-size-complex="7pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P25" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false" fo:background-color="transparent">
        <style:background-image/>
      </style:paragraph-properties>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="PLegend" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false" fo:keep-with-next="always"/>
      <style:text-properties style:font-name="Arial" fo:font-size="5.5pt" fo:font-weight="bold" style:font-size-asian="5.5pt" style:font-weight-asian="bold" style:font-size-complex="5.5pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="PLegendEmpty" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false" fo:keep-with-next="always"/>
      <style:text-properties style:font-name="Arial" fo:font-size="6pt" fo:font-weight="normal" style:font-size-asian="6pt" style:font-weight-asian="normal" style:font-size-complex="6pt" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="PDatumOrt" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false" fo:keep-with-next="always"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="PTabEmpty" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false" fo:keep-with-next="always"/>
      <style:text-properties style:font-name="Arial" fo:font-size="12pt" style:font-size-asian="10.5pt" style:font-size-complex="12pt"/>
    </style:style>
    <style:style style:name="PTabStgl" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false" fo:keep-with-next="always"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="PTabStglUnten" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="center" style:justify-single-word="false" fo:keep-with-next="always"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" fo:font-weight="bold" style:font-size-asian="8pt" style:font-weight-asian="bold" style:font-size-complex="8pt" style:font-weight-complex="bold"/>
    </style:style>

  </office:automatic-styles>
  <office:body>
<xsl:apply-templates select="zeugnis"/>
  </office:body>
</office:document-content>
</xsl:template>

<xsl:template match="zeugnis">
    <office:text>
      <text:sequence-decls>
        <text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Table"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Text"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
      </text:sequence-decls>
      <table:table table:name="Lehrveranstaltungen" table:style-name="Lehrveranstaltungen">
        <table:table-column table:style-name="Lehrveranstaltungen.A"/>
        <table:table-column table:style-name="Lehrveranstaltungen.B"/>
        <table:table-column table:style-name="Lehrveranstaltungen.C"/>
        <table:table-column table:style-name="Lehrveranstaltungen.D"/>
        <table:table-column table:style-name="Lehrveranstaltungen.E"/>
        <table:table-column table:style-name="Lehrveranstaltungen.F"/>
        <table:table-column table:style-name="Lehrveranstaltungen.G"/>
        <table:table-column table:style-name="Lehrveranstaltungen.H"/>
        <table:table-column table:style-name="Lehrveranstaltungen.I"/>
        <table:table-header-rows>
          <table:table-row table:style-name="Lehrveranstaltungen.1">
            <table:table-cell table:style-name="Lehrveranstaltungen.A1" table:number-columns-spanned="3" office:value-type="string">
              <text:p text:style-name="P18">Lehrveranstaltungen</text:p>
            </table:table-cell>
            <table:covered-table-cell/>
            <table:covered-table-cell/>
            <table:table-cell table:style-name="Lehrveranstaltungen.A1" office:value-type="string">
              <text:p text:style-name="P19">LV-Art</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.A1" office:value-type="string">
              <text:p text:style-name="P19">Sem.</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.A1" office:value-type="string">
              <text:p text:style-name="P19">SWS</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.A1" office:value-type="string">
              <text:p text:style-name="P19">ECTS</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.A1" office:value-type="string">
              <text:p text:style-name="P19">Note</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Lehrveranstaltungen.I1" office:value-type="string">
              <text:p text:style-name="P19">Grade</text:p>
            </table:table-cell>
          </table:table-row>
        </table:table-header-rows>

		<xsl:apply-templates select="unterrichtsfach"/>

        <table:table-row table:style-name="Lehrveranstaltungen.5">
          <table:table-cell table:style-name="Lehrveranstaltungen.A5" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P16"/>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Lehrveranstaltungen.A1" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P14">ECTS absolviert</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Lehrveranstaltungen.A1" office:value-type="string">
            <text:p text:style-name="P15"><xsl:value-of select="ects_absolviert"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.I1" table:number-columns-spanned="2" office:value-type="string">
            <text:p text:style-name="P20"/>
          </table:table-cell>
          <table:covered-table-cell/>
        </table:table-row>
        <table:table-row table:style-name="Lehrveranstaltungen.6">
          <table:table-cell table:style-name="Lehrveranstaltungen.A6" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P11"/>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Lehrveranstaltungen.A1" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P14">ECTS verpflichtend</text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Lehrveranstaltungen.I1" office:value-type="string">
            <text:p text:style-name="P15"><xsl:value-of select="ects_gesamt"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.H6" office:value-type="string">
            <text:p text:style-name="P20"/>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.I6" office:value-type="string">
            <text:p text:style-name="P20"/>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="PLegend"><text:soft-page-break/>Legende: WS=Wintersemester, SS=Sommersemester, SWS=Semesterwochenstunden, LV-Art=Lehrveranstaltungsart, VO=Vorlesung, UE=Übung, BP=Praktikum, SE=Seminar, EX=Exkursion, PT=Projekt, AWPF=Wahlpflichtfach, RU=Rechenübung, ILV=integr. LV, LB=Laborübung, PS=Proseminar, WK=Workshop, WA=Wiss. Arbeit, WP=Wirtschaftspraktikum, MT=Managementtechniken, MODUL=gemeinsame Bewertung mehrerer Lehrveranstaltungen mit einer Modulnote</text:p>
      <text:p text:style-name="PLegend"/>
      <text:p text:style-name="PLegend">1 SWS=15 Lehrveranstaltungsstunden, m.E.tg.=mit Erfolg teilgenommen, o.E.tg.=ohne Erfolg teilgenommen, an=anerkannt</text:p>
      <text:p text:style-name="PLegend">Nationale Beurteilung: 1=Sehr Gut, 2=Gut, 3=Befriedigend, 4=Genügend, 5=Nicht Genügend</text:p>
      <text:p text:style-name="PLegend">Internationale Beurteilung (ECTS Notenskala): A/B=Sehr Gut, C=Gut, D=Befriedigend, E=Genügend, F=Nicht Genügend</text:p>
      <text:p text:style-name="PLegendEmpty"/>
      <text:p text:style-name="PLegendEmpty"/>
      <text:p text:style-name="PDatumOrt">Pinkafeld, am <xsl:value-of select="datum_aktuell"/></text:p>
      <table:table table:name="Tabelle2" table:style-name="Tabelle2">
        <table:table-column table:style-name="Tabelle2.A"/>
        <table:table-column table:style-name="Tabelle2.B"/>
		<text:soft-page-break/>
        <table:table-row>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="PTabEmpty"/>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.B1" office:value-type="string">
            <text:p text:style-name="PTabStgl"><xsl:value-of select="studiengangsleiter"/></text:p>
          </table:table-cell>
        </table:table-row>
        <table:table-row>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="PTabEmpty"/>
          </table:table-cell>
          <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
            <text:p text:style-name="PTabStglUnten">Leitung Fachhochschul-Studiengang</text:p>
          </table:table-cell>
        </table:table-row>
      </table:table>
      <text:p text:style-name="PLegendEmpty"/>
    </office:text>
</xsl:template>


<xsl:template match="zeugnis/unterrichtsfach">
<!-- 1. Ebene -->
	<xsl:choose>
		<xsl:when test="unterrichtsfach" > <!-- wenn LVs darunter liegen (Modul)-->
        <table:table-row table:style-name="Lehrveranstaltungen.2">
          <table:table-cell table:style-name="Lehrveranstaltungen.A2" table:number-columns-spanned="3" office:value-type="string">
            <text:p text:style-name="P14"><xsl:value-of select="bezeichnung"/></text:p>
          </table:table-cell>
          <table:covered-table-cell/>
          <table:covered-table-cell/>
          <table:table-cell table:style-name="Lehrveranstaltungen.D2" office:value-type="string">
            <text:p text:style-name="P16"/>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.D2" office:value-type="string">
            <text:p text:style-name="P16"/>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.D2" office:value-type="string">
            <text:p text:style-name="P16"/>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.D2" office:value-type="string">
            <text:p text:style-name="P16"/>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.D2" office:value-type="string">
            <text:p text:style-name="P16"/>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.I2" office:value-type="string">
            <text:p text:style-name="P16"/>
          </table:table-cell>
        </table:table-row>
	</xsl:when>
		<xsl:otherwise>
			<table:table-row>
          <table:table-cell table:style-name="Lehrveranstaltungen.A3" office:value-type="string">
            <text:p text:style-name="P16"><xsl:value-of select="lvnr"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.B3" office:value-type="string">
            <text:p text:style-name="P16"/>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.C3" office:value-type="string">
            <text:p text:style-name="P16"><xsl:value-of select="bezeichnung"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.D3" office:value-type="string">
            <text:p text:style-name="P17"><xsl:value-of select="lehrform"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.E3" office:value-type="string">
            <text:p text:style-name="P17"><xsl:value-of select="stsem_kurz"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.F3" office:value-type="string">
            <text:p text:style-name="P17"><xsl:value-of select="sws"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.G3" office:value-type="string">
            <text:p text:style-name="P17">
				<xsl:if test="positiv='Ja'">
					<xsl:value-of select="ects"/>
				</xsl:if>
			</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.H3" office:value-type="string">
            <text:p text:style-name="P17"><xsl:value-of select="note"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.I3" office:value-type="string">
            <text:p text:style-name="P17">
            	<xsl:choose>
            		<xsl:when test="noteidx=0">
            			<xsl:text>A</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=1">
            			<xsl:text>B</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=2">
            			<xsl:text>C</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=3">
            			<xsl:text>D</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=4">
            			<xsl:text>E</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=5">
            			<xsl:text>F</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=6">
            			<xsl:text>cr</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=7">
            			<xsl:text>p.w.s.</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=8">
            			<xsl:text>p.wo.s.</xsl:text>
            		</xsl:when>
            		<xsl:otherwise>
	         			<xsl:value-of select="noteidx" />
	         		</xsl:otherwise>
            	</xsl:choose>
			</text:p>
          </table:table-cell>
        </table:table-row>
		</xsl:otherwise>
	</xsl:choose>
	<xsl:apply-templates select="unterrichtsfach"/>
</xsl:template>        

<xsl:template match="zeugnis/unterrichtsfach/unterrichtsfach">
<!-- 2. Ebene -->
	
		<table:table-row>
          <table:table-cell table:style-name="Lehrveranstaltungen.A3" office:value-type="string">
            <text:p text:style-name="P16"><xsl:value-of select="lvnr"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.B3" office:value-type="string">
            <text:p text:style-name="P16"/>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.C3" office:value-type="string">
            <text:p text:style-name="P16"><xsl:value-of select="bezeichnung"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.D3" office:value-type="string">
            <text:p text:style-name="P17"><xsl:value-of select="lehrform"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.E3" office:value-type="string">
            <text:p text:style-name="P17"><xsl:value-of select="stsem_kurz"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.F3" office:value-type="string">
            <text:p text:style-name="P17"><xsl:value-of select="sws"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.G3" office:value-type="string">
            <text:p text:style-name="P17">
				<xsl:if test="positiv='Ja'">
					<xsl:value-of select="ects"/>
				</xsl:if>
			</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.H3" office:value-type="string">
            <text:p text:style-name="P17"><xsl:value-of select="note"/></text:p>
          </table:table-cell>
          <table:table-cell table:style-name="Lehrveranstaltungen.I3" office:value-type="string">
            <text:p text:style-name="P17">
            <xsl:choose>
            		<xsl:when test="noteidx=0">
            			<xsl:text>A</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=1">
            			<xsl:text>B</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=2">
            			<xsl:text>C</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=3">
            			<xsl:text>D</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=4">
            			<xsl:text>E</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=5">
            			<xsl:text>F</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=6">
            			<xsl:text>cr</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=7">
            			<xsl:text>p.w.s.</xsl:text>
            		</xsl:when>
            		<xsl:when test="noteidx=8">
            			<xsl:text>p.wo.s.</xsl:text>
            		</xsl:when>
            		<xsl:otherwise>
         				<xsl:value-of select="noteidx" />
         			</xsl:otherwise>
            	</xsl:choose>
			</text:p>
          </table:table-cell>
        </table:table-row>       
	
	<xsl:apply-templates select="unterrichtsfach"/>
</xsl:template>
<xsl:template match="zeugnis/unterrichtsfach/unterrichtsfach/unterrichtsfach">
<!-- 3. Ebene -->
	
		<table:table-row>
      <table:table-cell table:style-name="Lehrveranstaltungen.A4" office:value-type="string">
        <text:p text:style-name="P16"><xsl:value-of select="lvnr"/></text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Lehrveranstaltungen.B4" office:value-type="string">
        <text:p text:style-name="P16"/>
      </table:table-cell>
      <table:table-cell table:style-name="Lehrveranstaltungen.C4" office:value-type="string">
        <text:p text:style-name="P16"><text:s/>- <xsl:value-of select="bezeichnung"/></text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Lehrveranstaltungen.D4" office:value-type="string">
        <text:p text:style-name="P17"><xsl:value-of select="lehrform"/></text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Lehrveranstaltungen.E4" office:value-type="string">
        <text:p text:style-name="P17"><xsl:value-of select="stsem_kurz"/></text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Lehrveranstaltungen.F4" office:value-type="string">
        <text:p text:style-name="P17"><xsl:value-of select="sws"/></text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Lehrveranstaltungen.G4" office:value-type="string">
        <text:p text:style-name="P17">
			<xsl:if test="positiv='Ja'">
				<xsl:value-of select="ects"/>
			</xsl:if>
		</text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Lehrveranstaltungen.H4" office:value-type="string">
        <text:p text:style-name="P17"><xsl:value-of select="note"/></text:p>
      </table:table-cell>
      <table:table-cell table:style-name="Lehrveranstaltungen.I4" office:value-type="string">
        <text:p text:style-name="P17">
      	 <xsl:choose>
         		<xsl:when test="noteidx=0">
         			<xsl:text>A</xsl:text>
         		</xsl:when>
         		<xsl:when test="noteidx=1">
         			<xsl:text>B</xsl:text>
         		</xsl:when>
         		<xsl:when test="noteidx=2">
         			<xsl:text>C</xsl:text>
         		</xsl:when>
         		<xsl:when test="noteidx=3">
         			<xsl:text>D</xsl:text>
         		</xsl:when>
         		<xsl:when test="noteidx=4">
         			<xsl:text>E</xsl:text>
         		</xsl:when>
         		<xsl:when test="noteidx=5">
         			<xsl:text>F</xsl:text>
         		</xsl:when>
         		<xsl:when test="noteidx=6">
           			<xsl:text>cr</xsl:text>
           		</xsl:when>
           		<xsl:when test="noteidx=7">
           			<xsl:text>p.w.s.</xsl:text>
           		</xsl:when>
           		<xsl:when test="noteidx=8">
           			<xsl:text>p.wo.s.</xsl:text>
           		</xsl:when>
         		<xsl:otherwise>
         			<xsl:value-of select="noteidx" />
         		</xsl:otherwise>
         	</xsl:choose>
        </text:p>
      </table:table-cell>
    </table:table-row>
	
</xsl:template>
</xsl:stylesheet>
