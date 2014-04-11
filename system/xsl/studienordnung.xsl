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
		<style:table-cell-properties fo:background-color="#99ffcc" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
			<style:background-image/>
		</style:table-cell-properties>
	</style:style>
	<style:style style:name="Lehrveranstaltung.B1" style:family="table-cell">
		<style:table-cell-properties fo:background-color="#99ffcc" fo:padding="0.097cm" fo:border="0.05pt solid #000000">
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
<!--    <style:style style:name="Matrix.A" style:family="table-column">
       <style:table-column-properties style:column-width="1.3cm" />
    </style:style> -->
    <style:style style:name="Matrix.B" style:family="table-column">
       <style:table-column-properties style:column-width="8.572cm" />
    </style:style>
    <style:style style:name="Matrix.C" style:family="table-column">
       <style:table-column-properties style:column-width="1.838cm" />
    </style:style>
    <style:style style:name="Matrix.D" style:family="table-column">
       <style:table-column-properties style:column-width="1.231cm" />
    </style:style>
    <style:style style:name="Matrix.E" style:family="table-column">
       <style:table-column-properties style:column-width="1.094cm" />
    </style:style>
    <style:style style:name="Matrix.F" style:family="table-column">
       <style:table-column-properties style:column-width="1.799cm" />
    </style:style>
    <style:style style:name="Matrix.G" style:family="table-column">
       <style:table-column-properties style:column-width="1.328cm" />
    </style:style>
    <style:style style:name="Matrix.H" style:family="table-column">
       <style:table-column-properties style:column-width="1.367cm" />
    </style:style>
	<style:style style:name="Matrix.1" style:family="table-row">
       <style:table-row-properties fo:background-color="#8fd2ff" fo:keep-together="auto" />
    </style:style>
    <style:style style:name="Matrix.2" style:family="table-row">
       <style:table-row-properties fo:background-color="#99ffcc" fo:keep-together="auto" />
    </style:style>
    <style:style style:name="Matrix.3" style:family="table-row">
       <style:table-row-properties fo:keep-together="auto" />
    </style:style>
    <style:style style:name="Matrix.A1" style:family="table-cell">
       <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="0.5pt solid #00000a" />
    </style:style>
    <style:style style:name="Matrix.B1" style:family="table-cell">
       <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="0.5pt solid #00000a" />
    </style:style>
    <style:style style:name="Matrix.B2" style:family="table-cell">
       <style:table-cell-properties fo:background-color="#cccccc" style:vertical-align="middle" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="0.5pt solid #00000a" />
    </style:style>
      <style:style style:name="Tabelle1" style:family="table">
         <style:table-properties style:width="15.155cm" table:align="right" style:shadow="none" />
      </style:style>
      <style:style style:name="Tabelle1.A" style:family="table-column">
         <style:table-column-properties style:column-width="7.343cm" />
      </style:style>
      <style:style style:name="Tabelle1.B" style:family="table-column">
         <style:table-column-properties style:column-width="1.272cm" />
      </style:style>
      <style:style style:name="Tabelle1.C" style:family="table-column">
         <style:table-column-properties style:column-width="0.979cm" />
      </style:style>
      <style:style style:name="Tabelle1.D" style:family="table-column">
         <style:table-column-properties style:column-width="0.953cm" />
      </style:style>
      <style:style style:name="Tabelle1.E" style:family="table-column">
         <style:table-column-properties style:column-width="1.695cm" />
      </style:style>
      <style:style style:name="Tabelle1.F" style:family="table-column">
         <style:table-column-properties style:column-width="1.431cm" />
      </style:style>
      <style:style style:name="Tabelle1.G" style:family="table-column">
         <style:table-column-properties style:column-width="1.483cm" />
      </style:style>
      <style:style style:name="Tabelle1.1" style:family="table-row">
         <style:table-row-properties fo:background-color="#83caff">
            <style:background-image />
         </style:table-row-properties>
      </style:style>
      <style:style style:name="Tabelle1.A1" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#83caff" fo:padding="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.A2" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B2" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C2" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D2" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E2" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F2" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G2" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.3" style:family="table-row">
         <style:table-row-properties fo:background-color="#99ffcc">
            <style:background-image />
         </style:table-row-properties>
      </style:style>
      <style:style style:name="Tabelle1.A3" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B3" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C3" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D3" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E3" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F3" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G3" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A4" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B4" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C4" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D4" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E4" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F4" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G4" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A5" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B5" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C5" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D5" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E5" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F5" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G5" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A6" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B6" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C6" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D6" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E6" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F6" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G6" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A7" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B7" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C7" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D7" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E7" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F7" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G7" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A8" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B8" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C8" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D8" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E8" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F8" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G8" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A9" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B9" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C9" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D9" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E9" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F9" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G9" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A10" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B10" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C10" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D10" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E10" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F10" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G10" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A11" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B11" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C11" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D11" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E11" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F11" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G11" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A12" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B12" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C12" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D12" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E12" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F12" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G12" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A13" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B13" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C13" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D13" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E13" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F13" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G13" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A14" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B14" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C14" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D14" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E14" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F14" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G14" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A15" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B15" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C15" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D15" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E15" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F15" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G15" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A16" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B16" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#dddddd" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.C16" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D16" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F16" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G16" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A17" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#ffffff" fo:padding="0cm" fo:border="none">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.A18" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A19" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B19" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C19" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D19" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E19" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F19" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G19" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A20" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B20" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C20" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D20" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E20" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F20" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G20" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A21" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B21" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C21" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D21" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E21" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F21" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G21" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A22" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B22" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C22" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D22" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E22" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F22" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G22" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A23" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B23" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C23" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D23" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E23" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F23" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G23" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A24" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B24" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C24" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D24" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E24" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F24" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G24" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A25" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C25" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D25" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F25" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G25" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A27" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding="0.049cm" fo:border="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B27" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C27" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D27" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E27" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F27" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G27" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A28" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B28" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C28" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D28" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E28" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F28" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G28" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A29" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#99ffcc" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.B29" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#99ffcc" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.A30" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B30" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C30" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D30" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E30" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F30" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G30" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A31" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C31" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D31" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F31" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G31" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A33" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#83caff" fo:padding="0.049cm" fo:border="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.B33" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#83caff" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.A34" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#83caff" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.B34" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#83caff" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.A36" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B36" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C36" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D36" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E36" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F36" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G36" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A37" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C37" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D37" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F37" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G37" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A42" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B42" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C42" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D42" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E42" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F42" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G42" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A43" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C43" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D43" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F43" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G43" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A48" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B48" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C48" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D48" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E48" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F48" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G48" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A50" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B50" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C50" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D50" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E50" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F50" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G50" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.51" style:family="table-row">
         <style:table-row-properties fo:background-color="#ffff99">
            <style:background-image />
         </style:table-row-properties>
      </style:style>
      <style:style style:name="Tabelle1.A51" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B51" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C51" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D51" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E51" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F51" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G51" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A52" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C52" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D52" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F52" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G52" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A53" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A54" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#ff8080" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.B54" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#dddddd" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.C54" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D54" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F54" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G54" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A58" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#ffff99" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.B58" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#ffff99" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.A60" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="bottom" fo:background-color="#ffffff" fo:padding="0.049cm" fo:border-left="1.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.B60" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C60" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D60" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E60" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F60" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G60" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A61" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding="0.049cm" fo:border="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B61" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C61" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D61" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E61" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F61" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G61" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A62" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C62" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D62" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F62" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G62" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A64" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#ffff99" fo:padding="0.049cm" fo:border="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.B64" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:background-color="#ffff99" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="0.5pt solid #00000a" fo:border-bottom="0.5pt solid #00000a">
            <style:background-image />
         </style:table-cell-properties>
      </style:style>
      <style:style style:name="Tabelle1.B66" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C66" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D66" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E66" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F66" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G66" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A67" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding="0.049cm" fo:border="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B67" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C67" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D67" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E67" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F67" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G67" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A68" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C68" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D68" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F68" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G68" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B72" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C72" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D72" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E72" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F72" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G72" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A73" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding="0.049cm" fo:border="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.B73" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C73" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D73" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.E73" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F73" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G73" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.A74" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0.049cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="0.5pt solid #00000a" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.C74" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.D74" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.F74" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
      </style:style>
      <style:style style:name="Tabelle1.G74" style:family="table-cell">
         <style:table-cell-properties style:vertical-align="middle" fo:padding-left="0cm" fo:padding-right="0.049cm" fo:padding-top="0cm" fo:padding-bottom="0.049cm" fo:border-left="none" fo:border-right="0.5pt solid #00000a" fo:border-top="none" fo:border-bottom="0.5pt solid #00000a" />
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
      <style:text-properties style:font-name="Arial" fo:font-size="22pt" officeooo:rsid="001dc677" officeooo:paragraph-rsid="001dc677"/>
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
	  <style:text-properties fo:font-size="10pt" fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
	  <style:paragraph-properties fo:margin-bottom="0.25cm"/>
	</style:style>
	<style:style style:name="P20" style:family="paragraph" style:parent-style-name="Heading_20_1" style:list-style-name="">
       <style:paragraph-properties fo:line-height="110%" fo:orphans="2" fo:widows="2">
          <style:tab-stops />
       </style:paragraph-properties>
	</style:style>
    <style:style style:name="P21" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L1">
      <style:text-properties fo:font-size="10pt" officeooo:rsid="001dc677" officeooo:paragraph-rsid="001dc677"/>
	  <style:paragraph-properties fo:margin-bottom="0.25cm" fo:text-align="center"/>
    </style:style>
    <style:style style:name="P22" style:family="paragraph" style:parent-style-name="Heading_20_2">
      <style:text-properties style:font-name="Arial" fo:font-size="18pt"/>
    </style:style>
    <style:style style:name="P23" style:family="paragraph" style:parent-style-name="Heading_20_3">
      <style:text-properties style:font-name="Arial" fo:font-size="14pt" fo:font-weight="normal" style:font-weight-asian="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="P24" style:family="paragraph" style:parent-style-name="Heading_20_3">
      <style:text-properties style:font-name="Arial" fo:font-size="12pt" fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="P25" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L1">
      <style:text-properties fo:font-size="10pt" officeooo:rsid="001dc677" officeooo:paragraph-rsid="001dc677"/>
	  <style:paragraph-properties fo:margin-bottom="0.25cm"/>
	</style:style>
    <style:style style:name="P26" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L1">
      <style:text-properties fo:font-size="10pt" fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold" officeooo:rsid="001dc677" officeooo:paragraph-rsid="001dc677"/>
	  <style:paragraph-properties fo:margin-bottom="0.25cm" fo:text-align="center"/>
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
      <text:p text:style-name="P5"><xsl:value-of select="studiengang_bezeichnung"/> (StgKz: <xsl:value-of select="studiengang_kz"/>)</text:p>
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
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15785_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>2 Zugangsvoraussetzungen<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P15">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15787_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>3 Aufnahmeverfahren<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P15">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15777_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>4 Berufliche Tätigkeitsfelder<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P15">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15779_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>5 Qualifikationsziele (Lernergebnisse des Studienganges)<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P15">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15781_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>6 Studienplan<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P16">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__339_971256360" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>6.1 Organisationsform<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P17">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__361_971256360" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>6.1.1 Studienplanmatrix<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P17">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15783_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>6.1.2 Modulgrafik<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P17">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__15783_1435587267" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>6.1.3 Modulbeschreibungen<text:tab/>3</text:a>
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
	  <text:list xml:id="list6862182980785781816" text:style-name="L2">
		<text:list-item><text:p text:style-name="P3">Studiengangskennzahl: <xsl:value-of select="studiengang_kz"/></text:p></text:list-item>
		<text:list-item><text:p text:style-name="P7">Studiengangsbezeichnung: <xsl:value-of select="studiengang_bezeichnung"/></text:p></text:list-item>
		<text:list-item><text:p text:style-name="P7">Studiengangsbezeichnung (engl.): <xsl:value-of select="bezeichnung_englisch"/></text:p></text:list-item>
		<text:list-item><text:p text:style-name="P7">Studiengangskurzbezeichnung: <xsl:value-of select="studiengang_kurzbzlang"/></text:p></text:list-item>
		<text:list-item><text:p text:style-name="P3">Studiengangsart: <xsl:value-of select="studiengang_art"/></text:p></text:list-item>
		<text:list-item><text:p text:style-name="P3">Akademischer Grad: <xsl:value-of select="studiengang_art"/> of Science in Engineering (<xsl:value-of select="titel_kurzbz"/>)</text:p></text:list-item>
		<text:list-item><text:p text:style-name="P3">Organisationsform: <xsl:value-of select="orgform_kurzbz_lang"/></text:p></text:list-item>
		<text:list-item><text:p text:style-name="P3">Standort: Wien</text:p></text:list-item>
		<text:list-item><text:p text:style-name="P7">Studiengangsleitung: <xsl:value-of select="studiengangsleitung"/></text:p></text:list-item>
	  </text:list>
      <text:p text:style-name="P3"/>
      <text:list xml:id="list233823232037712" text:continue-numbering="true" text:continue-list="list233821455398143" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:h text:style-name="P11" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__15785_1435587267"/>Zugangsvoraussetzungen<text:bookmark-end text:name="__RefHeading__15785_1435587267"/></text:h>
        </text:list-item>
	  </text:list>
      <text:p text:style-name="Standard"/>
      <text:list xml:id="list233823232037713" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:h text:style-name="P11" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__15787_1435587267"/>Aufnahmeverfahren<text:bookmark-end text:name="__RefHeading__15787_1435587267"/></text:h>
        </text:list-item>
      </text:list>
      <text:p text:style-name="Standard"/>
      <text:list xml:id="list233821599912460" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:h text:style-name="P11" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__15777_1435587267"/>Berufliche Tätigkeitsfelder<text:bookmark-end text:name="__RefHeading__15777_1435587267"/></text:h>
		</text:list-item>
	  </text:list>
	  <text:p text:style-name="P18">An dieser Stelle sind die beruflichen Tätigkeitsfelder des Studiengangs aufzuzeigen.</text:p>
      <text:p text:style-name="Standard"/>
	  <text:list xml:id="list140455441160016" text:continue-numbering="true" text:style-name="Numbering_20_1">
		<text:list-item>
          <text:h text:style-name="P11" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__15779_1435587267"/>Qualifikationsziele (Lernergebnisse des Studienganges)<text:bookmark-end text:name="__RefHeading__15779_1435587267"/></text:h>
        </text:list-item>
	  </text:list>
      <text:p text:style-name="Standard"/>
	  <text:list xml:id="list105303509167461" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:h text:style-name="P11" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__15781_1435587267"/>Studienplan<text:bookmark-end text:name="__RefHeading__15781_1435587267"/></text:h>
        </text:list-item>
      </text:list>
	  
	  <xsl:apply-templates select="orgform"/>

    </office:text>
  </office:body>
</office:document-content>
</xsl:template>

<xsl:template match="orgform">
      <text:p text:style-name="Standard"/>
	  <text:list xml:id="list2341352636336469980" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
		  <text:list>
		    <text:list-item>
              <text:h text:style-name="P22" text:outline-level="2"><text:bookmark-start text:name="__RefHeading__339_971256360"/>Organisationsform <xsl:value-of select="orgform_kurzbz_lang"/><text:bookmark-end text:name="__RefHeading__339_971256360"/></text:h>
			</text:list-item>
		  </text:list>
        </text:list-item>
      </text:list>
      <text:h text:style-name="P24" text:outline-level="3"><text:tab/>Studienorganisatorische Punkte</text:h>
      <text:list xml:id="list8309596791993483161" text:style-name="L1">
        <text:list-item>
          <text:p text:style-name="P7">Regelstudiendauer: <xsl:value-of select="regelstudiendauer"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P7">Sprache: </text:p>
          <text:p text:style-name="P7">[Angabe der Unterrichtssprache (Deutsch oder Englisch). Gegebenenfalls Angabe, ob einzelne Lehrveranstaltungen oder Prüfungen in einer anderen Sprache durchgeführt werden.]</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P7">Auslandssemester: </text:p>
          <text:p text:style-name="P7">[Angabe, ob ein verpflichtendes oder optionales Auslandssemester vorgesehen ist und in welchem Semester bzw. welchen Semestern es absolvierbar ist. Erforderlichenfalls weitere zentrale Informationen zum Auslandssemester anführen.]</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P7">Berufspraktikum:</text:p>
          <text:p text:style-name="P7">[Im Normalfall nur bei Bachelorstudiengängen relevant bzw. falls nicht relevant, löschen. Angabe des Semesters, der Dauer in Wochen und des Umfangs (ECTS) des Berufspraktikums.]</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P7">Einteilung des Studienjahres: </text:p>
          <text:p text:style-name="P7">[Angaben zu Beginn und Ende sowie zur Zahl der Lehrveranstaltungswochen von Winter- und Sommersemester; Angaben zu Beginn und Ende sowie zur Zahl der lehrveranstaltungsfreien Wochen während des Winter- und Sommersemesters sowie zwischen Winter- und Sommersemester.]</text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P7">Gemeinsames Studienprogramm: </text:p>
          <text:p text:style-name="P7">[Angaben dazu nur falls zutreffend bzw. falls nicht zutreffend, löschen. Angaben zu folgenden Themen: „Art des gemeinsamen Studienprogramms“ (joint-degree / multiple-degree / double-degree program); „Angaben zum akademischen Grad“;  „Angaben zur Partnerinstitution/en“ inklusive Hinweis auf die bestehende Vereinbarung mit der/den Partnerinstitution/en, in der die Details geregelt sind.]</text:p>
        </text:list-item>
      </text:list>
      <text:p text:style-name="Standard"/>
      <text:list xml:id="list233822041148163" text:continue-list="list233821599912460" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:list>
                <text:list-item>
                  <text:h text:style-name="P23" text:outline-level="3"><text:bookmark-start text:name="__RefHeading__361_971256360"/>Studienplanmatrix<text:bookmark-end text:name="__RefHeading__361_971256360"/></text:h>
                </text:list-item>
              </text:list>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
	  <text:p text:style-name="P7">[Die Studienplanmatrix ist eine Liste der Lehrveranstaltungen eines Studiengangs gruppiert nach Semestern und nach Modulen. Pro Lehrveranstaltung sind die Lehrform, die SWS, die LVS, die Anzahl der Gruppen, die ALVS und die ECTS anzugeben. Die Summenbildung erfolgt je Modul, je Semester und über alle Semester. Wahlpflichtmodule und -lehrveranstaltungen werden separat unterhalb des entsprechenden Semesters angegeben. Nachfolgend findet sich zunächst ein schematisches Beispiel für eine Studienplanmatrix.</text:p>
	  <text:p text:style-name="P7"/>
	  <text:p text:style-name="P7">Unterhalb des Schemas finden Sie die aus dem FAS generierte Lehrveranstaltungsliste im Hinblick auf das Studienjahr 2014/15. Die Spalten „LVS“, „Gruppen“, „ALVS“ und „ECTS“ sind aus dem FAS generiert. Bitte kontrollieren Sie die Werte und aktualisieren Sie diese gegebenenfalls. Die SWS sind bitte händisch einzutragen. Bringen Sie die aktualisierte und ergänzte Liste anschließend in das für die Studienplanmatrix vorgesehene Schema.]</text:p>
      <text:p text:style-name="P7"/>
         <table:table table:name="Tabelle1" table:style-name="Tabelle1">
            <table:table-column table:style-name="Tabelle1.A" />
            <table:table-column table:style-name="Tabelle1.B" />
            <table:table-column table:style-name="Tabelle1.C" />
            <table:table-column table:style-name="Tabelle1.D" />
            <table:table-column table:style-name="Tabelle1.E" />
            <table:table-column table:style-name="Tabelle1.F" />
            <table:table-column table:style-name="Tabelle1.G" />
            <table:table-row table:style-name="Tabelle1.1">
               <table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="7" office:value-type="string">
                  <text:p text:style-name="P19">1. SEMESTER</text:p>
               </table:table-cell>
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.1">
               <table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
                  <text:p text:style-name="P25">Bezeichnung Modul bzw. LV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B2" office:value-type="string">
                  <text:p text:style-name="P21">LV-Typ</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C2" office:value-type="string">
                  <text:p text:style-name="P21">SWS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D2" office:value-type="string">
                  <text:p text:style-name="P21">LVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E2" office:value-type="string">
                  <text:p text:style-name="P21">Gruppen</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F2" office:value-type="string">
                  <text:p text:style-name="P21">ALVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G2" office:value-type="string">
                  <text:p text:style-name="P21">ECTS</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.3">
               <table:table-cell table:style-name="Tabelle1.A3" office:value-type="string">
                  <text:p text:style-name="P19">Modul A</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B3" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C3" office:value-type="string">
                  <text:p text:style-name="P26">5</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D3" office:value-type="string">
                  <text:p text:style-name="P26">75</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E3" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F3" office:value-type="string">
                  <text:p text:style-name="P26">90</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G3" office:value-type="string">
                  <text:p text:style-name="P26">7,5</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A4" office:value-type="string">
                  <text:p text:style-name="P25">LV 1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B4" office:value-type="string">
                  <text:p text:style-name="P21">VO</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C4" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D4" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E4" office:value-type="string">
                  <text:p text:style-name="P21">1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F4" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G4" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A5" office:value-type="string">
                  <text:p text:style-name="P25">LV 2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B5" office:value-type="string">
                  <text:p text:style-name="P21">UE</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C5" office:value-type="string">
                  <text:p text:style-name="P21">1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D5" office:value-type="string">
                  <text:p text:style-name="P21">15</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E5" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F5" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G5" office:value-type="string">
                  <text:p text:style-name="P21">1,5</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A6" office:value-type="string">
                  <text:p text:style-name="P25">LV 3</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B6" office:value-type="string">
                  <text:p text:style-name="P21">VO</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C6" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D6" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E6" office:value-type="string">
                  <text:p text:style-name="P21">1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F6" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G6" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.3">
               <table:table-cell table:style-name="Tabelle1.A7" office:value-type="string">
                  <text:p text:style-name="P19">Modul B</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B7" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C7" office:value-type="string">
                  <text:p text:style-name="P26">4</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D7" office:value-type="string">
                  <text:p text:style-name="P26">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E7" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F7" office:value-type="string">
                  <text:p text:style-name="P26">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G7" office:value-type="string">
                  <text:p text:style-name="P26">6</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A8" office:value-type="string">
                  <text:p text:style-name="P25">LV 5</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B8" office:value-type="string">
                  <text:p text:style-name="P21">VO</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C8" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D8" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E8" office:value-type="string">
                  <text:p text:style-name="P21">1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F8" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G8" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A9" office:value-type="string">
                  <text:p text:style-name="P25">LV 6</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B9" office:value-type="string">
                  <text:p text:style-name="P21">VO</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C9" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D9" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E9" office:value-type="string">
                  <text:p text:style-name="P21">1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F9" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G9" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.3">
               <table:table-cell table:style-name="Tabelle1.A10" office:value-type="string">
                  <text:p text:style-name="P19">Modul C</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B10" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C10" office:value-type="string">
                  <text:p text:style-name="P26">6</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D10" office:value-type="string">
                  <text:p text:style-name="P26">90</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E10" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F10" office:value-type="string">
                  <text:p text:style-name="P26">180</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G10" office:value-type="string">
                  <text:p text:style-name="P26">7</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A11" office:value-type="string">
                  <text:p text:style-name="P25">LV 7</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B11" office:value-type="string">
                  <text:p text:style-name="P21">ILV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C11" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D11" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E11" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F11" office:value-type="string">
                  <text:p text:style-name="P21">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G11" office:value-type="string">
                  <text:p text:style-name="P21">2,5</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A12" office:value-type="string">
                  <text:p text:style-name="P25">LV 8</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B12" office:value-type="string">
                  <text:p text:style-name="P21">ILV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C12" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D12" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E12" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F12" office:value-type="string">
                  <text:p text:style-name="P21">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G12" office:value-type="string">
                  <text:p text:style-name="P21">1,5</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A13" office:value-type="string">
                  <text:p text:style-name="P25">LV 9</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B13" office:value-type="string">
                  <text:p text:style-name="P21">ILV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C13" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D13" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E13" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F13" office:value-type="string">
                  <text:p text:style-name="P21">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G13" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.3">
               <table:table-cell table:style-name="Tabelle1.A14" office:value-type="string">
                  <text:p text:style-name="P19">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B14" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C14" office:value-type="string">
                  <text:p text:style-name="P26">…</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D14" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E14" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F14" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G14" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A15" office:value-type="string">
                  <text:p text:style-name="P25">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B15" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C15" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D15" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E15" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F15" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G15" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A16" office:value-type="string">
                  <text:p text:style-name="P19">Summenzeile:</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C16" office:value-type="string">
                  <text:p text:style-name="P26">22</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D16" office:value-type="string">
                  <text:p text:style-name="P26">330</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F16" office:value-type="string">
                  <text:p text:style-name="P26">550</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G16" office:value-type="string">
                  <text:p text:style-name="P26">30</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.1">
               <table:table-cell table:style-name="Tabelle1.A18" table:number-columns-spanned="7" office:value-type="string">
                  <text:p text:style-name="P19">2. SEMESTER</text:p>
               </table:table-cell>
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.1">
               <table:table-cell table:style-name="Tabelle1.A19" office:value-type="string">
                  <text:p text:style-name="P25">Bezeichnung Modul bzw. LV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B19" office:value-type="string">
                  <text:p text:style-name="P21">LV-Typ</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C19" office:value-type="string">
                  <text:p text:style-name="P21">SWS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D19" office:value-type="string">
                  <text:p text:style-name="P21">LVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E19" office:value-type="string">
                  <text:p text:style-name="P21">Gruppen</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F19" office:value-type="string">
                  <text:p text:style-name="P21">ALVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G19" office:value-type="string">
                  <text:p text:style-name="P21">ECTS</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.3">
               <table:table-cell table:style-name="Tabelle1.A20" office:value-type="string">
                  <text:p text:style-name="P19">Modul G</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B20" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C20" office:value-type="string">
                  <text:p text:style-name="P26">4</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D20" office:value-type="string">
                  <text:p text:style-name="P26">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E20" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F20" office:value-type="string">
                  <text:p text:style-name="P26">120</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G20" office:value-type="string">
                  <text:p text:style-name="P26">6</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A21" office:value-type="string">
                  <text:p text:style-name="P25">LV 18</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B21" office:value-type="string">
                  <text:p text:style-name="P21">VO</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C21" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D21" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E21" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F21" office:value-type="string">
                  <text:p text:style-name="P21">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G21" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A22" office:value-type="string">
                  <text:p text:style-name="P25">LV 19</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B22" office:value-type="string">
                  <text:p text:style-name="P21">VO</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C22" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D22" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E22" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F22" office:value-type="string">
                  <text:p text:style-name="P21">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G22" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.3">
               <table:table-cell table:style-name="Tabelle1.A23" office:value-type="string">
                  <text:p text:style-name="P19">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B23" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C23" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D23" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E23" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F23" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G23" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A24" office:value-type="string">
                  <text:p text:style-name="P25">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B24" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C24" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D24" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E24" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F24" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G24" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A25" office:value-type="string">
                  <text:p text:style-name="P19">Summenzeile:</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C25" office:value-type="string">
                  <text:p text:style-name="P26">20</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D25" office:value-type="string">
                  <text:p text:style-name="P26">300</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F25" office:value-type="string">
                  <text:p text:style-name="P26">870</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G25" office:value-type="string">
                  <text:p text:style-name="P26">30</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.1">
               <table:table-cell table:style-name="Tabelle1.A27" office:value-type="string">
                  <text:p text:style-name="P19">3. SEMESTER</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B27" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C27" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D27" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E27" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F27" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G27" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.1">
               <table:table-cell table:style-name="Tabelle1.A28" office:value-type="string">
                  <text:p text:style-name="P25">Bezeichnung Modul bzw. LV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B28" office:value-type="string">
                  <text:p text:style-name="P21">LV-Typ</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C28" office:value-type="string">
                  <text:p text:style-name="P21">SWS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D28" office:value-type="string">
                  <text:p text:style-name="P21">LVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E28" office:value-type="string">
                  <text:p text:style-name="P21">Gruppen</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F28" office:value-type="string">
                  <text:p text:style-name="P21">ALVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G28" office:value-type="string">
                  <text:p text:style-name="P21">ECTS</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A29" office:value-type="string">
                  <text:p text:style-name="P19">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A30" office:value-type="string">
                  <text:p text:style-name="P25">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B30" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C30" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D30" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E30" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F30" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G30" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A31" office:value-type="string">
                  <text:p text:style-name="P19">Summenzeile:</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C31" office:value-type="string">
                  <text:p text:style-name="P26">20</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D31" office:value-type="string">
                  <text:p text:style-name="P26">300</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F31" office:value-type="string">
                  <text:p text:style-name="P26">730</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G31" office:value-type="string">
                  <text:p text:style-name="P26">30</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
            </table:table-row>
            <text:soft-page-break />
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A33" office:value-type="string">
                  <text:p text:style-name="P19">4. SEMESTER</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A34" office:value-type="string">
                  <text:p text:style-name="P25">Bezeichnung Modul bzw. LV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LV-Typ</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">SWS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">Gruppen</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ALVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ECTS</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A29" office:value-type="string">
                  <text:p text:style-name="P19">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A36" office:value-type="string">
                  <text:p text:style-name="P25">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B36" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C36" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D36" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E36" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F36" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G36" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A37" office:value-type="string">
                  <text:p text:style-name="P19">Summenzeile:</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C37" office:value-type="string">
                  <text:p text:style-name="P26">18</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D37" office:value-type="string">
                  <text:p text:style-name="P26">270</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F37" office:value-type="string">
                  <text:p text:style-name="P26">1005</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G37" office:value-type="string">
                  <text:p text:style-name="P26">30</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A33" office:value-type="string">
                  <text:p text:style-name="P19">5. SEMESTER</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A34" office:value-type="string">
                  <text:p text:style-name="P25">Bezeichnung Modul bzw. LV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LV-Typ</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">SWS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">Gruppen</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ALVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ECTS</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A29" office:value-type="string">
                  <text:p text:style-name="P19">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A42" office:value-type="string">
                  <text:p text:style-name="P25">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B42" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C42" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D42" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E42" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F42" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G42" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A43" office:value-type="string">
                  <text:p text:style-name="P19">Summenzeile:</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C43" office:value-type="string">
                  <text:p text:style-name="P26">20</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D43" office:value-type="string">
                  <text:p text:style-name="P26">300</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F43" office:value-type="string">
                  <text:p text:style-name="P26">500</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G43" office:value-type="string">
                  <text:p text:style-name="P26">30</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A33" office:value-type="string">
                  <text:p text:style-name="P19">6. SEMESTER</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A34" office:value-type="string">
                  <text:p text:style-name="P25">Bezeichnung Modul bzw. LV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LV-Typ</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">SWS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">Gruppen</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ALVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ECTS</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A29" office:value-type="string">
                  <text:p text:style-name="P19">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A48" office:value-type="string">
                  <text:p text:style-name="P25">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B48" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C48" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D48" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E48" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F48" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G48" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A29" office:value-type="string">
                  <text:p text:style-name="P19">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B29" office:value-type="string">
                  <text:p text:style-name="P26">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A50" office:value-type="string">
                  <text:p text:style-name="P25">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B50" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C50" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D50" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E50" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F50" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G50" office:value-type="string">
                  <text:p text:style-name="P21">...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Tabelle1.51">
               <table:table-cell table:style-name="Tabelle1.A51" office:value-type="string">
                  <text:p text:style-name="P19">Wahlpflichtmodule I - IV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B51" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C51" office:value-type="string">
                  <text:p text:style-name="P26">4</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D51" office:value-type="string">
                  <text:p text:style-name="P26">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E51" office:value-type="string">
                  <text:p text:style-name="P26">3</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F51" office:value-type="string">
                  <text:p text:style-name="P26">180</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G51" office:value-type="string">
                  <text:p text:style-name="P26">6</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A52" office:value-type="string">
                  <text:p text:style-name="P19">Summenzeile:</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C52" office:value-type="string">
                  <text:p text:style-name="P26">20</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D52" office:value-type="string">
                  <text:p text:style-name="P26">300</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F52" office:value-type="string">
                  <text:p text:style-name="P26">1190</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G52" office:value-type="string">
                  <text:p text:style-name="P26">30</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A53" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P25" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A54" office:value-type="string">
                  <text:p text:style-name="P19">Summe über alle Semester</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B54" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C54" office:value-type="string">
                  <text:p text:style-name="P26">120</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D54" office:value-type="string">
                  <text:p text:style-name="P26">1800</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B54" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F54" office:value-type="string">
                  <text:p text:style-name="P26">5.100</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G54" office:value-type="string">
                  <text:p text:style-name="P26">180</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A33" office:value-type="string">
                  <text:p text:style-name="P19">6. SEMESTER - Wahlpflichtmodule</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B33" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A58" office:value-type="string">
                  <text:p text:style-name="P19">Wahlpflichtmodul I</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B58" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B58" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B58" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B58" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B58" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B58" office:value-type="string">
                  <text:p text:style-name="P19" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A34" office:value-type="string">
                  <text:p text:style-name="P25">LV-Bezeichnung</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LV-Typ</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">SWS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">Gruppen</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ALVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ECTS</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A60" office:value-type="string">
                  <text:p text:style-name="P25">LV 57</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B60" office:value-type="string">
                  <text:p text:style-name="P21">ILV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C60" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D60" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E60" office:value-type="string">
                  <text:p text:style-name="P21">1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F60" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G60" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A61" office:value-type="string">
                  <text:p text:style-name="P25">LV 58</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B61" office:value-type="string">
                  <text:p text:style-name="P21">VO</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C61" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D61" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E61" office:value-type="string">
                  <text:p text:style-name="P21">1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F61" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G61" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A62" office:value-type="string">
                  <text:p text:style-name="P25">Summenzeile:</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C62" office:value-type="string">
                  <text:p text:style-name="P21">4</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D62" office:value-type="string">
                  <text:p text:style-name="P21">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F62" office:value-type="string">
                  <text:p text:style-name="P21">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G62" office:value-type="string">
                  <text:p text:style-name="P21">6</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
            </table:table-row>
            <text:soft-page-break />
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A64" office:value-type="string">
                  <text:p text:style-name="P19">Wahlpflichtmodul II</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A34" office:value-type="string">
                  <text:p text:style-name="P25">LV-Bezeichnung</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LV-Typ</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">SWS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">Gruppen</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ALVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ECTS</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A60" office:value-type="string">
                  <text:p text:style-name="P25">LV 59</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B66" office:value-type="string">
                  <text:p text:style-name="P21">ILV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C66" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D66" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E66" office:value-type="string">
                  <text:p text:style-name="P21">1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F66" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G66" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A67" office:value-type="string">
                  <text:p text:style-name="P25">LV 60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B67" office:value-type="string">
                  <text:p text:style-name="P21">VO</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C67" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D67" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E67" office:value-type="string">
                  <text:p text:style-name="P21">1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F67" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G67" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A68" office:value-type="string">
                  <text:p text:style-name="P25">Summenzeile:</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C68" office:value-type="string">
                  <text:p text:style-name="P21">4</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D68" office:value-type="string">
                  <text:p text:style-name="P21">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F68" office:value-type="string">
                  <text:p text:style-name="P21">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G68" office:value-type="string">
                  <text:p text:style-name="P21">6</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.A17" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A64" office:value-type="string">
                  <text:p text:style-name="P19">Wahlpflichtmodul III</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
                  <text:p text:style-name="P26" />
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A34" office:value-type="string">
                  <text:p text:style-name="P25">LV-Bezeichnung</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LV-Typ</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">SWS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">LVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">Gruppen</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ALVS</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
                  <text:p text:style-name="P21">ECTS</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A60" office:value-type="string">
                  <text:p text:style-name="P25">LV 61</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B72" office:value-type="string">
                  <text:p text:style-name="P21">ILV</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C72" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D72" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E72" office:value-type="string">
                  <text:p text:style-name="P21">1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F72" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G72" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A73" office:value-type="string">
                  <text:p text:style-name="P25">LV 62</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B73" office:value-type="string">
                  <text:p text:style-name="P21">VO</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C73" office:value-type="string">
                  <text:p text:style-name="P21">2</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D73" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.E73" office:value-type="string">
                  <text:p text:style-name="P21">1</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F73" office:value-type="string">
                  <text:p text:style-name="P21">30</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G73" office:value-type="string">
                  <text:p text:style-name="P21">3</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A74" office:value-type="string">
                  <text:p text:style-name="P25">Summenzeile:</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.C74" office:value-type="string">
                  <text:p text:style-name="P21">4</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.D74" office:value-type="string">
                  <text:p text:style-name="P21">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B16" office:value-type="string">
                  <text:p text:style-name="P21" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.F74" office:value-type="string">
                  <text:p text:style-name="P21">60</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.G74" office:value-type="string">
                  <text:p text:style-name="P21">6</text:p>
               </table:table-cell>
            </table:table-row>
         </table:table>
    <text:p text:style-name="Standard"/>
    <text:p text:style-name="Standard"/>

	<xsl:apply-templates select="semester" mode="matrix" />
    <text:p text:style-name="Standard"/>
		<text:list xml:id="list233823045617055" text:continue-list="list233822041148163" text:style-name="Numbering_20_1">
			<text:list-item>
			  <text:list>
				<text:list-item>
				  <text:list>
				    <text:list-item>
				      <text:h text:style-name="P23" text:outline-level="3"><text:bookmark-start text:name="__RefHeading__15783_1435587267"/>Modulgrafik<text:bookmark-end text:name="__RefHeading__15783_1435587267"/></text:h>
					</text:list-item>
				  </text:list>
				</text:list-item>
			  </text:list>
			</text:list-item>
		  </text:list>
        <text:p text:style-name="P7">[Bitte die Modulgrafik ergänzen; vgl. dazu die Modulgrafiken in den Beispiel-Studienordnungen.]</text:p>
        <text:p text:style-name="Standard"/>
        <text:list xml:id="list105303525162521" text:continue-list="list233823045617055" text:continue-numbering="true" text:style-name="Numbering_20_1">
            <text:list-item>
               <text:list>
                  <text:list-item>
                     <text:list>
                        <text:list-item>
                           <text:h text:style-name="P23" text:outline-level="3">
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
                  <text:p text:style-name="P25">Modul-Bezeichnung</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A1" office:value-type="string">
                  <text:p text:style-name="P25">Modul xy</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Modul.1">
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P25">Umfang (ECTS)</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P25">5</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Modul.1">
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P25">Ausbildungssemester</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P25">1</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Modul.1">
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P25">Kompetenzbereich</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P25">Fachrichtungen, ...</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Modul.1">
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P25">Pflicht-/Wahl-Modul</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P25">Pflicht-Modul? Wahl-Modul?</text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row table:style-name="Modul.1">
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P25">Prüfungsmodalitäten (falls Modulprüfung)</text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Modul.A2" office:value-type="string">
                  <text:p text:style-name="P25">...</text:p>
               </table:table-cell>
            </table:table-row>
          </table:table>
		<xsl:apply-templates select="semester" mode="beschreibung"/>
</xsl:template>

<xsl:template match="semester" mode="matrix">
		<table:table table:name="Matrix" table:style-name="Matrix">
<!--		<table:table-column table:style-name="Matrix.A"/> -->
		<table:table-column table:style-name="Matrix.B"/>
		<table:table-column table:style-name="Matrix.C"/>
		<table:table-column table:style-name="Matrix.D"/>
		<table:table-column table:style-name="Matrix.E"/>
		<table:table-column table:style-name="Matrix.F"/>
		<table:table-column table:style-name="Matrix.G"/>
		<table:table-column table:style-name="Matrix.H"/>
		<table:table-row table:style-name="Matrix.1">
			<table:table-cell table:style-name="Matrix.A1" table:number-columns-spanned="7">
				<text:p text:style-name="P19"><xsl:value-of select="semester_nr"/>. SEMESTER</text:p>
			</table:table-cell>
			<table:covered-table-cell/>
			<table:covered-table-cell/>
			<table:covered-table-cell/>
			<table:covered-table-cell/>
		</table:table-row>
		<table:table-row table:style-name="Matrix.1">
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P25">Bezeichnung Modul bzw. LV</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21">Lehrform</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21">SWS</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21">LVS</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21">Gruppen</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21">ALVS</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21">ECTS</text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row table:style-name="Matrix.2">
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P19">Modul xy</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
		</table:table-row>
		
		<xsl:apply-templates select="lehrveranstaltung" mode="matrix"/>
		
		<table:table-row>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P19">Summenzeile:</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.B2">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.B2">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
		</table:table-row>
	</table:table>
	<text:p text:style-name="Standard"/>
</xsl:template>

<xsl:template match="lehrveranstaltung" mode="matrix">
		<table:table-row table:style-name="Matrix.3">
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P25"><xsl:value-of select="lv_bezeichnung"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"><xsl:value-of select="lv_lehrform_kurzbz"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"><xsl:value-of select="lv_semesterstunden"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"><xsl:value-of select="lv_gruppen"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"><xsl:value-of select="lv_alvs"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Matrix.A1">
				<text:p text:style-name="P21"><xsl:value-of select="lv_ects"/></text:p>
			</table:table-cell>
		</table:table-row>
</xsl:template>

<xsl:template match="semester" mode="beschreibung">
	<xsl:if test="semester_nr!=0">
	  <xsl:apply-templates select="lehrveranstaltung" mode="beschreibung" />
	</xsl:if>
</xsl:template>

<xsl:template match="lehrveranstaltung" mode="beschreibung">
	
    <text:p text:style-name="Standard"/>
    <table:table table:name="Lehrveranstaltung" table:style-name="Lehrveranstaltung">
		<table:table-column table:style-name="Lehrveranstaltung.A"/>
		<table:table-column table:style-name="Lehrveranstaltung.B"/>
		<text:soft-page-break/>
		<table:table-row>
			<table:table-cell table:style-name="Lehrveranstaltung.A1" office:value-type="string">
				<text:p text:style-name="P25">LV-Bezeichnung</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Lehrveranstaltung.B1" office:value-type="string">
				<text:p text:style-name="P25"><xsl:value-of select="lv_bezeichnung"/></text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
				<text:p text:style-name="P25">ECTS</text:p>
			</table:table-cell>
				<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
					<text:p text:style-name="P25"><xsl:value-of select="lv_ects"/></text:p>
				</table:table-cell>
			</table:table-row>
			<table:table-row>
				<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
					<text:p text:style-name="P25">Ausbildungssemester</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
					<text:p text:style-name="P25"><xsl:value-of select="lv_semester"/></text:p>
				</table:table-cell>
			</table:table-row>
			<table:table-row>
				<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
					<text:p text:style-name="P25">Lehrform</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
					<text:p text:style-name="P25"><xsl:value-of select="lv_lehrform_kurzbz"/></text:p>
				</table:table-cell>
			</table:table-row>
			<table:table-row>
				<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
					<text:p text:style-name="P25">LV-Kürzel</text:p>
				</table:table-cell>
				<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
					<text:p text:style-name="P25"><xsl:value-of select="lv_kurzbz"/></text:p>
				</table:table-cell>
			</table:table-row>
			<!-- **************** LV-Info ************** -->
			<xsl:if test="lvinfo_sprache">
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P25">Methodik / Didaktik</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P25"><xsl:value-of select="lvinfo_methodik"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P25">Lehrnergebnisse</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P25"><xsl:value-of select="lvinfo_lehrziele"/></text:p>
						<!-- <text:p text:style-name="P_LV"><xsl:value-of select="lvinfo_kurzbeschreibung"/></text:p> -->
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P25">Lehrinhalte</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P25"><xsl:value-of select="lvinfo_lehrinhalte"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P25">Prüfungsmodalitäten</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P25"><xsl:value-of select="lvinfo_pruefungsordnung"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P25">Literatur</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P25"><xsl:value-of select="lvinfo_unterlagen"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Lehrveranstaltung.A2" office:value-type="string">
						<text:p text:style-name="P25">Sprache</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Lehrveranstaltung.B2" office:value-type="string">
						<text:p text:style-name="P25"></text:p>
					</table:table-cell>
				</table:table-row>
			</xsl:if>
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