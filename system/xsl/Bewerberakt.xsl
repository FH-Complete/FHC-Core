<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
	xmlns:fo="http://www.w3.org/1999/XSL/Format" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" 
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" 
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" 
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" 
	version="1.0">
<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="dokumentenakt">
<office:document-content 
	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" 
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" 
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" 
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" 
	xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" 
	xmlns:xlink="http://www.w3.org/1999/xlink" 
	xmlns:dc="http://purl.org/dc/elements/1.1/" 
	xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" 
	xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" 
	xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" 
	xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" 
	xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" 
	xmlns:math="http://www.w3.org/1998/Math/MathML" 
	xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
	<office:scripts/>
	<office:font-face-decls>
		<style:font-face style:name="Mangal1" svg:font-family="Mangal"/>
		<style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Microsoft YaHei" svg:font-family="&apos;Microsoft YaHei&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="SimSun" svg:font-family="SimSun" style:font-family-generic="system" style:font-pitch="variable"/>
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="Tabelle1" style:family="table">
			<style:table-properties style:width="14.002cm" table:align="margins" style:shadow="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A" style:family="table-column">
			<style:table-column-properties style:column-width="7.001cm" style:rel-column-width="32767*"/>
		</style:style>
		<style:style style:name="Tabelle1.A1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#dddddd" fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.A2" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.12" style:family="table-row">
			<style:table-row-properties style:row-height="0.101cm"/>
		</style:style>
		<style:style style:name="Tabelle1.A12" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#dddddd" fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.B12" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#dddddd" fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.A13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A15" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B15" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A16" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B16" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A17" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B17" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A18" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B18" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A19" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A20" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B20" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A21" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A23" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A24" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A25" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B25" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A26" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B26" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A27" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B27" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A28" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B28" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A29" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A31" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A32" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A33" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B33" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A34" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B34" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A36" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A37" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A38" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A39" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B39" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A40" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B40" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A41" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A42" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B42" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A43" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A44" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B44" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A45" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A46" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B46" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A47" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B47" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A48" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A49" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B49" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A51" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A52" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A53" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A54" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A55" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A56" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B56" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A57" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A58" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B58" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A59" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A60" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A61" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B61" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A62" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B62" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A63" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B63" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A64" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.B64" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle1.A65" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="6pt solid #dddddd" fo:border-right="none" fo:border-top="none" fo:border-bottom="6pt solid #dddddd"/>
		</style:style>
		<style:style style:name="Tabelle1.B65" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.199cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="none" fo:border-right="6pt solid #dddddd" fo:border-top="none" fo:border-bottom="6pt solid #dddddd"/>
		</style:style>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="001d0b51" officeooo:paragraph-rsid="001d0b51" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="001e3b98" officeooo:paragraph-rsid="001e3b98" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="0020372f" officeooo:paragraph-rsid="0020372f" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="001d0b51" officeooo:paragraph-rsid="001d0b51" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="001d0b51" officeooo:paragraph-rsid="001d0b51" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="001e3b98" officeooo:paragraph-rsid="001e3b98" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="001e3b98" officeooo:paragraph-rsid="001e3b98" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="0020372f" officeooo:paragraph-rsid="0020372f" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="0020372f" officeooo:paragraph-rsid="0020372f" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" officeooo:rsid="0020372f" officeooo:paragraph-rsid="0020372f" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties fo:color="#3399ff" style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="001d0b51" officeooo:paragraph-rsid="001d0b51" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
			<loext:graphic-properties draw:fill="solid" draw:fill-color="#dddddd" draw:opacity="100%"/>
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false" fo:background-color="#dddddd"/>
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="001d0b51" officeooo:paragraph-rsid="001d0b51" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" officeooo:rsid="001d0b51" officeooo:paragraph-rsid="001d0b51" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="001d0b51" officeooo:paragraph-rsid="001d0b51" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
			<loext:graphic-properties draw:fill="solid" draw:fill-color="#dddddd" draw:opacity="100%"/>
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false" fo:background-color="#dddddd"/>
			<style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="001d0b51" officeooo:paragraph-rsid="001d0b51" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P17" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="0020372f" officeooo:paragraph-rsid="0020372f" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P18" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties fo:color="#ff0000" style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="0020372f" officeooo:paragraph-rsid="0020372f" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="T1" style:family="text">
			<style:text-properties style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color"/>
		</style:style>
		<style:style style:name="T2" style:family="text">
			<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
		</style:style>
	</office:automatic-styles>
	<office:body>
		<office:text text:use-soft-page-breaks="true">
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
			</text:sequence-decls>
			<table:table table:name="Tabelle1" table:style-name="Tabelle1">
				<table:table-column table:style-name="Tabelle1.A" table:number-columns-repeated="2"/>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P16">Neue Bewerbung für Studiengang <xsl:value-of select="studiengang_kuerzel"/> <xsl:value-of select="orgform_kurzbz"/></text:p>
						<text:p text:style-name="P13">(Abgeschickt am <xsl:value-of select="bewerbung_abgeschicktamum"/>)</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P15">Angaben zur Person</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A3" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P14">Angaben zur Person</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A11" office:value-type="string">
						<text:p text:style-name="P14">Nachname</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B11" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="nachname"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A11" office:value-type="string">
						<text:p text:style-name="P14">Vorname</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B11" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="vorname"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A11" office:value-type="string">
						<text:p text:style-name="P14">Geburtsdatum</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B11" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="geb_datum"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A11" office:value-type="string">
						<text:p text:style-name="P14">Geburtsort</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B11" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="gebort"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A11" office:value-type="string">
						<text:p text:style-name="P14">Geburtsnation</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B11" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="geburtsnation"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A11" office:value-type="string">
						<text:p text:style-name="P14">Soz.-Vers. Nr. </text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B11" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="svnr"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A11" office:value-type="string">
						<text:p text:style-name="P14">Staatsbürgerschaft</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B11" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="staatsbuergerschaft"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A11" office:value-type="string">
						<text:p text:style-name="P14">Geschlecht</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B11" office:value-type="string">
						<text:p text:style-name="P2">
						<xsl:choose>
							<xsl:when test="geschlecht='m'">
								Männlich
							</xsl:when>
							<xsl:when test="geschlecht='w'">
								Weiblich
							</xsl:when>
							<xsl:otherwise>-</xsl:otherwise>
						</xsl:choose>
						</text:p>
					</table:table-cell>
				</table:table-row>
				
				<xsl:if test="aufnahme_notizen != ''">
					<table:table-row table:style-name="Tabelle1.12">
						<table:table-cell table:style-name="Tabelle1.A12" office:value-type="string">
							<text:p text:style-name="P1"/>
						</table:table-cell>
						<table:table-cell table:style-name="Tabelle1.B12" office:value-type="string">
							<text:p text:style-name="P1"/>
						</table:table-cell>
					</table:table-row>
					<table:table-row>
						<table:table-cell table:style-name="Tabelle1.A13" table:number-columns-spanned="2" office:value-type="string">
							<text:p text:style-name="P15">Priorität Masterklassen:</text:p>
						</table:table-cell>
						<table:covered-table-cell/>
					</table:table-row>
					
					<xsl:apply-templates select="aufnahme_notizen" />
					
				</xsl:if>

				<table:table-row table:style-name="Tabelle1.12">
					<table:table-cell table:style-name="Tabelle1.A12" office:value-type="string">
						<text:p text:style-name="P1"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B12" office:value-type="string">
						<text:p text:style-name="P1"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A13" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P15">Heimatadresse</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A14" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P14">Heimatadresse für BewerberInnen aus dem Inland</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A25" office:value-type="string">
						<text:p text:style-name="P2">Straße, Nr.</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B25" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="heimat_strasse"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A25" office:value-type="string">
						<text:p text:style-name="P2">PLZ/Ort</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B25" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="heimat_plz"/><xsl:text> </xsl:text><xsl:value-of select="heimat_ort"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A25" office:value-type="string">
						<text:p text:style-name="P2">Bundesland</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B25" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="heimat_bundesland"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A25" office:value-type="string">
						<text:p text:style-name="P1"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B25" office:value-type="string">
						<text:p text:style-name="P1"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A19" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P5">Heimatadresse für BewerberInnen aus dem Ausland</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A25" office:value-type="string">
						<text:p text:style-name="P1"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B25" office:value-type="string">
						<text:p text:style-name="P2">Heimatadresse=Zustelladresse</text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A21" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P12">Hinweis: Wenn „Heimatadresse=Zustelladresse“ zutrifft, gilt dies für BewerberInnen aus dem In- und Ausland</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row table:style-name="Tabelle1.12">
					<table:table-cell table:style-name="Tabelle1.A12" office:value-type="string">
						<text:p text:style-name="P1"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B12" office:value-type="string">
						<text:p text:style-name="P1"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A23" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P6">Zustelladresse</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A24" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P2">Zustelladresse für BewerberInnen aus dem Inland</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A25" office:value-type="string">
						<text:p text:style-name="P2">Straße, Nr.</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B25" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="zustell_strasse"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A34" office:value-type="string">
						<text:p text:style-name="P2">PLZ/Ort</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="zustell_plz"/><xsl:text> </xsl:text><xsl:value-of select="zustell_ort"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A34" office:value-type="string">
						<text:p text:style-name="P2">Bundesland</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
						<text:p text:style-name="P2"><xsl:value-of select="zustell_bundesland"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A34" office:value-type="string">
						<text:p text:style-name="P2"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
						<text:p text:style-name="P2"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A29" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P7">Zustelladresse für BewerberInnen aus dem Ausland</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row table:style-name="Tabelle1.12">
					<table:table-cell table:style-name="Tabelle1.A12" office:value-type="string">
						<text:p text:style-name="P2"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B12" office:value-type="string">
						<text:p text:style-name="P2"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A31" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P8">Kontakt</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A32" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P3">Kontakt</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A34" office:value-type="string">
						<text:p text:style-name="P3">Mobiltelefon</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
						<text:p text:style-name="P3"><xsl:value-of select="telefonnummer"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A34" office:value-type="string">
						<text:p text:style-name="P3">E-Mail</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B34" office:value-type="string">
						<text:p text:style-name="P4"><xsl:value-of select="email"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row table:style-name="Tabelle1.12">
					<table:table-cell table:style-name="Tabelle1.A12" office:value-type="string">
						<text:p text:style-name="P2"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B12" office:value-type="string">
						<text:p text:style-name="P2"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A36" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P9">Ausbildung</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A37" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P4">Ausbildung</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A38" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P11">Reifeprüfung (AHS, BHS, Berufsreifeprüfung)</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A40" office:value-type="string">
						<text:p text:style-name="P4">Name der Schule und Ort</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B40" office:value-type="string">
						<text:p text:style-name="P4">ZGV-Ort ? <xsl:value-of select="zgvort"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A40" office:value-type="string">
						<text:p text:style-name="P4">Abgelegt am</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B40" office:value-type="string">
						<text:p text:style-name="P4">ZGV-Datum ? <xsl:value-of select="zgvdatum"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A41" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P11">Studienberechtigungsprüfung</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A56" office:value-type="string">
						<text:p text:style-name="P4">Abgelegt am</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B56" office:value-type="string">
						<text:p text:style-name="P4">Daten woher ?</text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A43" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P11">Facheinschlägige berufliche Qualifikation</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A56" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B56" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
				</table:table-row>
				<text:soft-page-break/>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A45" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P11">Vorbereitungskurs</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A56" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B56" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A56" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B56" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A48" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P4">In welchem Staat wurden die Zugangsvoraussetzungen abgelegt</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A56" office:value-type="string">
						<text:p text:style-name="P4">Staat</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B56" office:value-type="string">
						<text:p text:style-name="P4"><xsl:value-of select="zgvnation"/></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row table:style-name="Tabelle1.12">
					<table:table-cell table:style-name="Tabelle1.A12" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B12" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A51" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P9">Bewerbungsunterlagen</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A52" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P4">Bewerbungsunterlagen / Anlagen</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A53" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P4">Bitte achten Sie darauf, dass <text:span text:style-name="T1">jedes</text:span> Dokument:</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A54" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P4">- kleiner als 4MB ist</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A55" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P4">- der Dateiname keine Umlaute, Sonderzeichen, Leerzeichen enthält</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A56" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B56" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A57" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P4">oder wenn Abschluss-/Reifeprüfungszeugnis noch nicht vorhanden:</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A64" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A59" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P4">Motivationsschreiben in deutscher Sprache!</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A60" table:number-columns-spanned="2" office:value-type="string">
						<text:p text:style-name="P4">Achtung: ist Teil des Aufnahmeverfahrens und wird bewertet!</text:p>
					</table:table-cell>
					<table:covered-table-cell/>
				</table:table-row>
				
				<xsl:apply-templates select="dokumente" />
				
				<table:table-row>
					<table:table-cell table:style-name="Tabelle1.A65" office:value-type="string">
						<text:p text:style-name="P4"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle1.B65" office:value-type="string">
						<text:p text:style-name="P10"/>
					</table:table-cell>
				</table:table-row>
			</table:table>
			<text:p text:style-name="P14"/>
			<text:p text:style-name="P14"/>

		</office:text>
	</office:body>
</office:document-content>
</xsl:template>

<xsl:template match="dokumente">
	<xsl:apply-templates select="dokument" />
</xsl:template>

<xsl:template match="dokument">
	<table:table-row>
		<table:table-cell table:style-name="Tabelle1.A64" office:value-type="string">
			<text:p text:style-name="P4"><xsl:value-of select="name"/></text:p>
		</table:table-cell>
		<table:table-cell table:style-name="Tabelle1.B64" office:value-type="string">
			<xsl:choose>
				<xsl:when test="nachgereicht='true' and filename=''">
					<text:p text:style-name="P10">Das Dokument wird nachgereicht</text:p>
					<text:p text:style-name="P17"><xsl:value-of select="anmerkung"/></text:p>
					<xsl:if test="errormsg != ''">
						<text:p text:style-name="P18"><xsl:value-of select="errormsg"/></text:p>
					</xsl:if>
				</xsl:when>
				<xsl:otherwise>
					<text:p text:style-name="P4"><xsl:value-of select="filename"/></text:p>
					<xsl:if test="errormsg != ''">
						<text:p text:style-name="P18"><xsl:value-of select="errormsg"/></text:p>
					</xsl:if>
				</xsl:otherwise>
			</xsl:choose>				
		</table:table-cell>
	</table:table-row>
</xsl:template>

<xsl:template match="aufnahme_notizen">
	<xsl:apply-templates select="aufnahme_notiz" />
</xsl:template>

<xsl:template match="aufnahme_notiz">
	<table:table-row>
		<table:table-cell table:style-name="Tabelle1.A14" table:number-columns-spanned="2" office:value-type="string">
			<text:p text:style-name="P14"><xsl:value-of select="position()" />) <xsl:value-of select="."/></text:p>
		</table:table-cell>
		<table:covered-table-cell/>
	</table:table-row>
</xsl:template>
  
</xsl:stylesheet>