<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>
  <xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="studienordnung">

<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
	<office:scripts/>
	<office:font-face-decls>
		<style:font-face style:name="OpenSymbol" svg:font-family="OpenSymbol" style:font-charset="x-symbol"/>
		<style:font-face style:name="Mangal1" svg:font-family="Mangal"/>
		<style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Arial1" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Arial" svg:font-family="Arial" style:font-adornments="Standard" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Microsoft YaHei" svg:font-family="&apos;Microsoft YaHei&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="SimSun" svg:font-family="SimSun" style:font-family-generic="system" style:font-pitch="variable"/>
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="Tabelle1" style:family="table">
			<style:table-properties style:width="17cm" table:align="margins"/>
		</style:style>
		<style:style style:name="Tabelle1.A" style:family="table-column">
			<style:table-column-properties style:column-width="12.651cm" style:rel-column-width="48767*"/>
		</style:style>
		<style:style style:name="Tabelle1.B" style:family="table-column">
			<style:table-column-properties style:column-width="1.45cm" style:rel-column-width="5589*"/>
		</style:style>
		<style:style style:name="Tabelle1.D" style:family="table-column">
			<style:table-column-properties style:column-width="1.45cm" style:rel-column-width="5590*"/>
		</style:style>
		<style:style style:name="Tabelle1.A1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.D1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.A2" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.D2" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.A3" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#97ffba" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.D3" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#97ffba" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.A4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.B4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.C4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.D4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.A6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.B6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.C6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.D6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.A7" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.B7" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.C7" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.D7" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.A8" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.B8" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#cccccc" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle1.C8" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle1.D8" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2" style:family="table">
			<style:table-properties style:width="17cm" table:align="margins"/>
		</style:style>
		<style:style style:name="Tabelle2.A" style:family="table-column">
			<style:table-column-properties style:column-width="12.651cm" style:rel-column-width="48767*"/>
		</style:style>
		<style:style style:name="Tabelle2.B" style:family="table-column">
			<style:table-column-properties style:column-width="1.45cm" style:rel-column-width="5589*"/>
		</style:style>
		<style:style style:name="Tabelle2.D" style:family="table-column">
			<style:table-column-properties style:column-width="1.45cm" style:rel-column-width="5590*"/>
		</style:style>
		<style:style style:name="Tabelle2.A1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle2.D1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle2.A2" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle2.D2" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle2.A3" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#97ffba" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle2.D3" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#97ffba" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle2.A4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.B4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.C4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.D4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.A6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.B6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.C6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.D6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.A7" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.B7" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.C7" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.D7" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.A8" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.B8" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#cccccc" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle2.C8" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle2.D8" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle3" style:family="table">
			<style:table-properties style:width="17cm" table:align="margins"/>
		</style:style>
		<style:style style:name="Tabelle3.A" style:family="table-column">
			<style:table-column-properties style:column-width="12.651cm" style:rel-column-width="48767*"/>
		</style:style>
		<style:style style:name="Tabelle3.B" style:family="table-column">
			<style:table-column-properties style:column-width="1.45cm" style:rel-column-width="5589*"/>
		</style:style>
		<style:style style:name="Tabelle3.D" style:family="table-column">
			<style:table-column-properties style:column-width="1.45cm" style:rel-column-width="5590*"/>
		</style:style>
		<style:style style:name="Tabelle3.A1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#ff8080" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle3.B1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#cccccc" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle3.C1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle3.D1" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle4" style:family="table">
			<style:table-properties style:width="17cm" table:align="margins"/>
		</style:style>
		<style:style style:name="Tabelle4.A" style:family="table-column">
			<style:table-column-properties style:column-width="12.651cm" style:rel-column-width="48767*"/>
		</style:style>
		<style:style style:name="Tabelle4.B" style:family="table-column">
			<style:table-column-properties style:column-width="1.45cm" style:rel-column-width="5589*"/>
		</style:style>
		<style:style style:name="Tabelle4.D" style:family="table-column">
			<style:table-column-properties style:column-width="1.45cm" style:rel-column-width="5590*"/>
		</style:style>
		<style:style style:name="Tabelle4.A1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle4.D1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle4.A2" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#ffff99" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle4.D2" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#ffff99" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle4.A3" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle4.D3" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle4.A4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle4.B4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle4.C4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle4.D4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle4.A5" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle4.B5" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle4.C5" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle4.D5" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle4.A6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle4.B6" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#cccccc" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle4.C6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle4.D6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle5" style:family="table">
			<style:table-properties style:width="17cm" table:align="margins"/>
		</style:style>
		<style:style style:name="Tabelle5.A" style:family="table-column">
			<style:table-column-properties style:column-width="12.651cm" style:rel-column-width="48767*"/>
		</style:style>
		<style:style style:name="Tabelle5.B" style:family="table-column">
			<style:table-column-properties style:column-width="1.45cm" style:rel-column-width="5589*"/>
		</style:style>
		<style:style style:name="Tabelle5.D" style:family="table-column">
			<style:table-column-properties style:column-width="1.45cm" style:rel-column-width="5590*"/>
		</style:style>
		<style:style style:name="Tabelle5.A1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#ffff99" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle5.D1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#ffff99" fo:padding="0.097cm" fo:border="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle5.A2" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle5.D2" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle5.A3" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle5.B3" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle5.C3" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle5.D3" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle5.A4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle5.B4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle5.C4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle5.D4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle5.A5" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle5.B5" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#cccccc" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle5.C5" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle5.D5" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6" style:family="table">
			<style:table-properties style:width="17cm" table:align="margins"/>
		</style:style>
		<style:style style:name="Tabelle6.A" style:family="table-column">
			<style:table-column-properties style:column-width="4.5cm" style:rel-column-width="17345*"/>
		</style:style>
		<style:style style:name="Tabelle6.B" style:family="table-column">
			<style:table-column-properties style:column-width="12.501cm" style:rel-column-width="48190*"/>
		</style:style>
		<style:style style:name="Tabelle6.A1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle6.B1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.097cm" fo:border="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle6.A2" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B2" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A3" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B3" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B4" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A5" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B5" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B6" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A7" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#97ffba" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle6.B7" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#97ffba" fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle6.A8" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B8" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A9" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B9" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A10" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B10" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A11" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B11" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A12" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B12" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A13" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B13" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A14" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B14" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.A15" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle6.B15" style:family="table-cell">
			<style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7" style:family="table">
			<style:table-properties style:width="25.7cm" table:align="margins"/>
		</style:style>
		<style:style style:name="Tabelle7.A" style:family="table-column">
			<style:table-column-properties style:column-width="4.897cm" style:rel-column-width="12486*"/>
		</style:style>
		<style:style style:name="Tabelle7.B" style:family="table-column">
			<style:table-column-properties style:column-width="0.199cm" style:rel-column-width="508*"/>
		</style:style>
		<style:style style:name="Tabelle7.C" style:family="table-column">
			<style:table-column-properties style:column-width="0.7cm" style:rel-column-width="1785*"/>
		</style:style>
		<style:style style:name="Tabelle7.i" style:family="table-column">
			<style:table-column-properties style:column-width="0.709cm" style:rel-column-width="1806*"/>
		</style:style>
		<style:style style:name="Tabelle7.1" style:family="table-row">
			<style:table-row-properties style:min-row-height="0.561cm"/>
		</style:style>
		<style:style style:name="Tabelle7.A1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding="0.101cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle7.B1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="transparent" fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle7.C1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle7.g1" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle7.C2" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle7.i2" style:family="table-cell">
			<style:table-cell-properties fo:background-color="#8fd2ff" fo:padding-left="0cm" fo:padding-right="0cm" fo:padding-top="0.101cm" fo:padding-bottom="0.101cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000">
				<style:background-image/>
			</style:table-cell-properties>
		</style:style>
		<style:style style:name="Tabelle7.A3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.B3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.D3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.E3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.F3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.G3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.H3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.J3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.K3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.L3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.M3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.N3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.P3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.Q3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.R3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.S3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.T3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.V3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.W3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.X3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.Y3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.Z3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.b3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.c3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.d3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.e3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.f3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.h3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.i3" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.A4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.B4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.D4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.E4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.F4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.G4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.H4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.J4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.K4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.L4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.M4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.N4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.P4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Q4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.R4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.S4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.T4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.V4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.W4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.X4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Y4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Z4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.b4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.c4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.d4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.e4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.f4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.h4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.i4" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.A5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.B5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.D5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.E5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.F5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.G5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.H5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.J5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.K5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.L5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.M5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.N5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.P5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Q5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.R5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.S5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.T5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.V5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.W5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.X5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Y5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Z5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.b5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.c5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.d5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.e5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.f5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.h5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.i5" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.A6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.B6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.D6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.E6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.F6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.G6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.H6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.J6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.K6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.L6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.M6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.N6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.P6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Q6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.R6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.S6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.T6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.V6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.W6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.X6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Y6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Z6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.b6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.c6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.d6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.e6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.f6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.h6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.i6" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.A7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.B7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.D7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.E7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.F7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.G7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.H7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.J7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.K7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.L7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.M7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.N7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.P7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Q7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.R7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.S7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.T7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.V7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.W7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.X7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Y7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Z7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.b7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.c7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.d7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.e7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.f7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.h7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.i7" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.A8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.B8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.D8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.E8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.F8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.G8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.H8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.J8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.K8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.L8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.M8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.N8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.P8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Q8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.R8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.S8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.T8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.V8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.W8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.X8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Y8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Z8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.b8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.c8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.d8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.e8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.f8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.h8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.i8" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.A9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.B9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.D9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.E9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.F9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.G9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.H9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.J9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.K9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.L9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.M9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.N9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.P9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.Q9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.R9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.S9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.T9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.V9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.W9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.X9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.Y9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.Z9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.b9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.c9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.d9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.e9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.f9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.h9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.i9" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="none"/>
		</style:style>
		<style:style style:name="Tabelle7.A10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.B10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.D10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.E10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.F10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.G10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.H10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.J10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.K10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.L10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.M10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.N10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.P10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Q10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.R10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.S10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.T10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.V10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.W10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.X10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Y10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Z10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.b10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.c10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.d10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.e10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.f10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.h10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.i10" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.A11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.B11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.D11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.E11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.F11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.G11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.H11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.J11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.K11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.L11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.M11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.N11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.P11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Q11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.R11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.S11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.T11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.V11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.W11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.X11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Y11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Z11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.b11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.c11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.d11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.e11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.f11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.h11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.i11" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.A12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.B12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.D12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.E12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.F12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.G12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.H12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.J12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.K12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.L12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.M12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.N12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.P12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Q12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.R12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.S12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.T12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.V12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.W12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.X12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Y12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Z12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.b12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.c12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.d12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.e12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.f12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.h12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.i12" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.A13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.B13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.D13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.E13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.F13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.G13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.H13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.J13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.K13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.L13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.M13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.N13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.P13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Q13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.R13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.S13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.T13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.V13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.W13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.X13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Y13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Z13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.b13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.c13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.d13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.e13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.f13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.h13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.i13" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.A14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.B14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.C14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.D14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.E14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.F14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.G14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.H14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.I14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.J14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.K14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.L14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.M14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.N14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.O14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.P14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Q14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.R14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.S14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.T14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.U14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.V14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.W14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.X14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Y14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.Z14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.a14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.b14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.c14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.d14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.e14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.f14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
		</style:style>
		<style:style style:name="Tabelle7.g14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.h14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="Tabelle7.i14" style:family="table-cell">
			<style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.049cm" fo:padding-bottom="0.049cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000"/>
		</style:style>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Footer" style:master-page-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false" style:page-number="auto" text:number-lines="false" text:line-number="0" style:vertical-align="top"/>
			<style:text-properties officeooo:rsid="00323ef6" officeooo:paragraph-rsid="00323ef6"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Footer" style:master-page-name="">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false" style:page-number="auto" text:number-lines="false" text:line-number="0" style:vertical-align="top">
				<style:tab-stops>
					<style:tab-stop style:position="12.7cm"/>
					<style:tab-stop style:position="25.4cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
			<style:text-properties officeooo:rsid="00323ef6" officeooo:paragraph-rsid="00323ef6"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="0018e662" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="001f96bb" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="00212809" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="00226e84" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="00226e84" officeooo:paragraph-rsid="00226e84" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="000d492e" fo:background-color="#ffff00" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="00212809" fo:background-color="#ffff00" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:color="#0086cb" style:font-name="Arial1" fo:font-size="26pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="000d492e" style:font-size-asian="26pt" style:font-size-complex="26pt"/>
		</style:style>
		<style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Arial1" fo:font-size="18pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="000d492e" style:font-size-asian="18pt" style:font-size-complex="18pt"/>
		</style:style>
		<style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:color="#ffffff" style:font-name="Arial1" fo:font-size="36pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="000d492e" style:font-size-asian="36pt" style:font-size-complex="36pt"/>
		</style:style>
		<style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:color="#0086cb" style:font-name="Arial1" fo:font-size="18pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="000d492e" style:font-size-asian="18pt" style:font-size-complex="18pt"/>
		</style:style>
		<style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties fo:color="#0086cb" style:font-name="Arial1" fo:font-size="10pt" fo:font-weight="bold" officeooo:rsid="000d492e" officeooo:paragraph-rsid="000d492e" fo:background-color="#ffff00" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P15" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P16" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties officeooo:rsid="0014f677" officeooo:paragraph-rsid="0014f677" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P17" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties officeooo:rsid="0014f677" officeooo:paragraph-rsid="0014f677" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P19" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties officeooo:rsid="0014f677" officeooo:paragraph-rsid="0018e662" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P21" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties officeooo:rsid="0014f677" officeooo:paragraph-rsid="0018e662" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P22" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P24" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties officeooo:paragraph-rsid="0018e662" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P26" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties officeooo:paragraph-rsid="0018e662" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P27" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties officeooo:rsid="0018e662" officeooo:paragraph-rsid="0018e662" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P28" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties officeooo:rsid="001f96bb" officeooo:paragraph-rsid="001f96bb" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P29" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties officeooo:rsid="00212809" officeooo:paragraph-rsid="00212809" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P30" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties officeooo:rsid="00226e84" officeooo:paragraph-rsid="00226e84" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P31" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties officeooo:rsid="00241fb6" officeooo:paragraph-rsid="00241fb6" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P32" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties fo:font-weight="bold" officeooo:rsid="0014f677" officeooo:paragraph-rsid="0014f677" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P33" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties fo:font-weight="bold" officeooo:rsid="0014f677" officeooo:paragraph-rsid="0014f677" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P35" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties fo:font-weight="bold" officeooo:rsid="0014f677" officeooo:paragraph-rsid="0018e662" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P37" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties fo:font-weight="bold" officeooo:rsid="0014f677" officeooo:paragraph-rsid="0018e662" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P38" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties fo:font-weight="bold" officeooo:rsid="0018e662" officeooo:paragraph-rsid="0018e662" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P39" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
			<style:text-properties fo:font-weight="bold" officeooo:rsid="0018e662" officeooo:paragraph-rsid="0018e662" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P40" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:text-properties fo:font-weight="bold" officeooo:rsid="001f96bb" officeooo:paragraph-rsid="001f96bb" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
		</style:style>
		<style:style style:name="P50" style:family="paragraph" style:parent-style-name="Contents_20_2">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="1cm"/>
					<style:tab-stop style:position="17cm" style:type="right" style:leader-style="dotted" style:leader-text="."/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P51" style:family="paragraph" style:parent-style-name="Contents_20_1">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="0.499cm"/>
					<style:tab-stop style:position="17cm" style:type="right" style:leader-style="dotted" style:leader-text="."/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>
		<style:style style:name="P52" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L2">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="000d492e" fo:background-color="#ffff00" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P53" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L4">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="000d492e" fo:background-color="#ffff00" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P54" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L4">
			<style:text-properties officeooo:paragraph-rsid="000d492e" fo:background-color="#ffff00"/>
		</style:style>
		<style:style style:name="P55" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L1" style:master-page-name="">
			<style:paragraph-properties style:page-number="auto" fo:background-color="transparent">
				<style:background-image/>
			</style:paragraph-properties>
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="000d492e" fo:background-color="#ffff00" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="P57" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L1">
			<style:paragraph-properties fo:background-color="transparent">
				<style:background-image/>
			</style:paragraph-properties>
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="000d492e" fo:background-color="#ffff00" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
    <style:style style:name="P59" style:family="paragraph" style:parent-style-name="Standard" style:master-page-name="">
      <style:paragraph-properties fo:margin-top="2.3cm" fo:margin-bottom="0cm" loext:contextual-spacing="false" style:page-number="auto"/>
      <style:text-properties fo:color="#0086cb" style:font-name="Arial1" fo:font-size="36pt" officeooo:rsid="000d492e" officeooo:paragraph-rsid="000d492e" style:font-size-asian="36pt" style:font-size-complex="36pt"/>
    </style:style>
		<style:style style:name="P61" style:family="paragraph" style:parent-style-name="Numbering_20_1" style:master-page-name="Standard">
			<style:paragraph-properties fo:break-before="page"/>
		</style:style>
		<style:style style:name="P62" style:family="paragraph" style:parent-style-name="Numbering_20_1" style:master-page-name="Standard">
		</style:style>
		<style:style style:name="P63" style:family="paragraph" style:parent-style-name="Numbering_20_2" style:master-page-name="Firstpage">
		</style:style>
		<style:style style:name="P64" style:family="paragraph" style:parent-style-name="Numbering_20_2" style:master-page-name="Landscape">
			<style:paragraph-properties style:page-number="auto"/>
		</style:style>
		<style:style style:name="P65" style:family="paragraph" style:parent-style-name="Numbering_20_2" style:master-page-name="Standard">
			<style:paragraph-properties style:page-number="auto"/>
		</style:style>
		<style:style style:name="P66" style:family="paragraph" style:parent-style-name="Numbering_20_2">
		</style:style>
		<style:style style:name="P67" style:family="paragraph" style:parent-style-name="Table_20_Contents">
			<style:paragraph-properties>
				<style:tab-stops>
					<style:tab-stop style:position="0.4cm"/>
				</style:tab-stops>
			</style:paragraph-properties>
		</style:style>

		<style:style style:name="T1" style:family="text">
			<style:text-properties fo:background-color="#ffff00" loext:char-shading-value="0"/>
		</style:style>
		<style:style style:name="T2" style:family="text">
			<style:text-properties fo:color="#0086cb" fo:font-size="26pt" style:font-size-asian="26pt" style:font-size-complex="26pt"/>
		</style:style>
		<style:style style:name="T3" style:family="text">
			<style:text-properties officeooo:rsid="00175338"/>
		</style:style>
		<style:style style:name="T4" style:family="text">
			<style:text-properties officeooo:rsid="0018e662"/>
		</style:style>
		<style:style style:name="T5" style:family="text">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" fo:background-color="#ffff00" loext:char-shading-value="0" style:font-size-asian="10pt"/>
		</style:style>
		<style:style style:name="T6" style:family="text">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" fo:background-color="#ffff00" loext:char-shading-value="0" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="T7" style:family="text">
			<style:text-properties style:font-name="Arial1" fo:font-size="10pt" officeooo:rsid="000d492e" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
		</style:style>
		<style:style style:name="T8" style:family="text">
			<style:text-properties officeooo:rsid="00280f99"/>
		</style:style>
        <style:style style:name="Numbering_20_3" style:display-name="Numbering 3" style:family="paragraph" style:parent-style-name="List" style:default-outline-level="3" style:list-style-name="Numbering_20_2" style:class="list">
        <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
        <style:text-properties fo:color="#0086cb" fo:font-size="16pt"/>
    </style:style>

    <style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
    </style:style>
    <style:style style:name="fr2" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
    </style:style>
    <style:style style:name="fr3" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
    </style:style>
		<style:style style:name="Sect1" style:family="section">
			<style:section-properties style:editable="false">
				<style:columns fo:column-count="1" fo:column-gap="0cm"/>
			</style:section-properties>
		</style:style>
		<text:list-style style:name="L1">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="matrix1">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="matrix2">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="matrix3">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="matrix4">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="matrix5">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="modul1">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="modul2">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="modul3">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="modul4">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="modul5">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="lernergebnis1">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="lernergebnis2">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="lernergebnis3">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="lernergebnis4">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="lernergebnis5">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
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
		<text:list-style style:name="L3">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.486cm" fo:text-indent="-0.635cm" fo:margin-left="7.486cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
		<text:list-style style:name="L4">
			<text:list-level-style-number text:level="1" style:num-suffix=")" style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-0.635cm" fo:margin-left="1.27cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.905cm" fo:text-indent="-0.635cm" fo:margin-left="1.905cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-0.635cm" fo:margin-left="2.54cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.175cm" fo:text-indent="-0.635cm" fo:margin-left="3.175cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.81cm" fo:text-indent="-0.635cm" fo:margin-left="3.81cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.445cm" fo:text-indent="-0.635cm" fo:margin-left="4.445cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.08cm" fo:text-indent="-0.635cm" fo:margin-left="5.08cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.715cm" fo:text-indent="-0.635cm" fo:margin-left="5.715cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.35cm" fo:text-indent="-0.635cm" fo:margin-left="6.35cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
			<text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
				<style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
					<style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm"/>
				</style:list-level-properties>
			</text:list-level-style-number>
		</text:list-style>
	</office:automatic-styles>

	<office:body>
	<office:text xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" text:use-soft-page-breaks="true">
		<office:forms form:automatic-focus="false" form:apply-design-mode="false"/>
		<text:sequence-decls>
			<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
			<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
			<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
			<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
		</text:sequence-decls>
		<text:p text:style-name="P63"/>
			<text:p text:style-name="P59">Studienordnung - Studienplan</text:p>
			<text:p text:style-name="P10"><xsl:value-of select="studienordnung_gueltigvon"/></text:p>

		<text:p text:style-name="P10"><xsl:value-of select="studiengang_bezeichnung"/> (<xsl:value-of select="studiengang_kz"/>)</text:p>

		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P11"/>
		<text:p text:style-name="P13"><xsl:value-of select="studiengang_kz"/>-<xsl:value-of select="studiengang_kurzbzlang"/>-<xsl:value-of select="studienordnung_gueltigvon"/></text:p>
		<text:h text:style-name="P62" text:outline-level="1">
			<text:bookmark-start text:name="__RefHeading__3656_462112980"/>Inhaltsverzeichnis<text:bookmark-end text:name="__RefHeading__3656_462112980"/>
		</text:h>
		<text:p text:style-name="P1"/>
		<text:table-of-content text:style-name="Sect1" text:protected="false" text:name="Inhaltsverzeichnis1">
			<text:table-of-content-source text:outline-level="3">
				<text:index-title-template text:style-name="Contents_20_Heading"/>
				<text:table-of-content-entry-template text:outline-level="1" text:style-name="Contents_20_1">
					<text:index-entry-link-start text:style-name="Index_20_Link"/>
					<text:index-entry-chapter/>
					<text:index-entry-tab-stop style:type="left" style:position="0.499cm" style:leader-char=" "/>
					<text:index-entry-text/>
					<text:index-entry-tab-stop style:type="right" style:position="17cm" style:leader-char="."/>
					<text:index-entry-page-number/>
					<text:index-entry-link-end/>
				</text:table-of-content-entry-template>
				<text:table-of-content-entry-template text:outline-level="2" text:style-name="Contents_20_2">
					<text:index-entry-link-start text:style-name="Index_20_Link"/>
					<text:index-entry-chapter/>
					<text:index-entry-tab-stop style:type="left" style:position="1cm" style:leader-char=" "/>
					<text:index-entry-text/>
					<text:index-entry-tab-stop style:type="right" style:position="17cm" style:leader-char="."/>
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
				<text:p text:style-name="P51">
					<text:a xlink:type="simple" xlink:href="#__RefHeading__3658_462112980" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link">1.<text:tab/>Studienplan Organisationsform Berufsbegleitend<text:tab/>3</text:a>
				</text:p>
				<text:p text:style-name="P50">
					<text:a xlink:type="simple" xlink:href="#__RefHeading__3660_462112980" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link">1.1.<text:tab/>Studienplanmatrix<text:tab/>3</text:a>
				</text:p>
				<text:p text:style-name="P50">
					<text:a xlink:type="simple" xlink:href="#__RefHeading__3662_462112980" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link">1.2.<text:tab/>Modulgrafik<text:tab/>5</text:a>
				</text:p>
				<text:p text:style-name="P50">
					<text:a xlink:type="simple" xlink:href="#__RefHeading__3664_462112980" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link">1.3.<text:tab/>Modulbeschreibungen<text:tab/>6</text:a>
				</text:p>
				<text:p text:style-name="P51">
					<text:a xlink:type="simple" xlink:href="#__RefHeading__3668_462112980" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link">2.<text:tab/>Studienplan Organisationsform Vollzeit<text:tab/>8</text:a>
				</text:p>
				<text:p text:style-name="P50">
					<text:a xlink:type="simple" xlink:href="#__RefHeading__3670_462112980" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link">2.1.<text:tab/>Studienplanmatrix<text:tab/>8</text:a>
				</text:p>
			</text:index-body>
		</text:table-of-content>

		<xsl:apply-templates select="studienplan"/>

	</office:text>
	</office:body>
</office:document-content>
</xsl:template>

<xsl:template match="studienplan">
			<text:p text:style-name="P1"/>
			<text:list xml:id="list6602357775918451119" text:style-name="Numbering_20_1">
				<text:list-item>
					<text:h text:style-name="P61" text:outline-level="1">
						<text:bookmark-start text:name="__RefHeading__3658_462112980"/>Studienplan Organisationsform <xsl:value-of select="orgform_kurzbz_lang"/><text:bookmark-end text:name="__RefHeading__3658_462112980"/>
					</text:h>
					<text:list>
						<text:list-item>
							<text:h text:style-name="P66" text:outline-level="2">
								<text:bookmark-start text:name="__RefHeading__3660_462112980"/>Studienplanmatrix<text:bookmark-end text:name="__RefHeading__3660_462112980"/>
							</text:h>
						</text:list-item>
					</text:list>
				</text:list-item>
			</text:list>
			<text:p text:style-name="P1"/>

			<xsl:apply-templates select="semester" mode="matrix" />

			<text:p text:style-name="P2"/>
			<text:p text:style-name="P3"/>
			<table:table table:name="Tabelle3" table:style-name="Tabelle3">
				<table:table-column table:style-name="Tabelle3.A"/>
				<table:table-column table:style-name="Tabelle3.B" table:number-columns-repeated="2"/>
				<table:table-column table:style-name="Tabelle3.D"/>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
						<text:p text:style-name="P37">Summe über alle Semester:</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.B1" office:value-type="string">
						<text:p text:style-name="P24"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.C1" office:value-type="string">
						<text:p text:style-name="P35"><xsl:value-of select="lv_summe_sws_orgform"/></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle3.D1" office:value-type="string">
						<text:p text:style-name="P35"><xsl:value-of select="lv_summe_ects_orgform"/></text:p>
					</table:table-cell>
				</table:table-row>
			</table:table>
			<text:p text:style-name="P3"/>
			<text:p text:style-name="P3"/>
			<table:table table:name="Tabelle4" table:style-name="Tabelle4">
				<table:table-column table:style-name="Tabelle4.A"/>
				<table:table-column table:style-name="Tabelle4.B" table:number-columns-repeated="2"/>
				<table:table-column table:style-name="Tabelle4.D"/>
				<text:soft-page-break/>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
						<text:p text:style-name="P37">
							<text:span text:style-name="T4">6</text:span>. <text:span text:style-name="T4">Semester - Wahlpflichtmodule</text:span>
						</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
						<text:p text:style-name="P26"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
						<text:p text:style-name="P26"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.D1" office:value-type="string">
						<text:p text:style-name="P26"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle4.A2" office:value-type="string">
						<text:p text:style-name="P38">Wahlpflichtmodul I</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.A2" office:value-type="string">
						<text:p text:style-name="P26"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.A2" office:value-type="string">
						<text:p text:style-name="P26"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.D2" office:value-type="string">
						<text:p text:style-name="P26"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle4.A3" office:value-type="string">
						<text:p text:style-name="P37">
							<text:span text:style-name="T4">LV-</text:span>Bezeichnung</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.A3" office:value-type="string">
						<text:p text:style-name="P35">LV-Typ</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.A3" office:value-type="string">
						<text:p text:style-name="P35">SWS</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.D3" office:value-type="string">
						<text:p text:style-name="P35">ECTS</text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle4.A4" office:value-type="string">
						<text:p text:style-name="P21">Bezeichnung</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.B4" office:value-type="string">
						<text:p text:style-name="P19">Typ</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.C4" office:value-type="string">
						<text:p text:style-name="P19"></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.D4" office:value-type="string">
						<text:p text:style-name="P19"></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle4.A5" office:value-type="string">
						<text:p text:style-name="P21">Bezeichnung</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.B5" office:value-type="string">
						<text:p text:style-name="P27">Typ</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.C5" office:value-type="string">
						<text:p text:style-name="P19"></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.D5" office:value-type="string">
						<text:p text:style-name="P19"></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle4.A6" office:value-type="string">
						<text:p text:style-name="P37">Summenzeile:</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.B6" office:value-type="string">
						<text:p text:style-name="P24"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.C6" office:value-type="string">
						<text:p text:style-name="P39"></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle4.D6" office:value-type="string">
						<text:p text:style-name="P39"></text:p>
					</table:table-cell>
				</table:table-row>
			</table:table>
			<text:p text:style-name="P3"/>
			<text:p text:style-name="P3"/>
			<table:table table:name="Tabelle5" table:style-name="Tabelle5">
				<table:table-column table:style-name="Tabelle5.A"/>
				<table:table-column table:style-name="Tabelle5.B" table:number-columns-repeated="2"/>
				<table:table-column table:style-name="Tabelle5.D"/>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle5.A1" office:value-type="string">
						<text:p text:style-name="P38">Wahlpflichtmodul II</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.A1" office:value-type="string">
						<text:p text:style-name="P26"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.A1" office:value-type="string">
						<text:p text:style-name="P26"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.D1" office:value-type="string">
						<text:p text:style-name="P26"/>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle5.A2" office:value-type="string">
						<text:p text:style-name="P37">
							<text:span text:style-name="T4">LV-</text:span>Bezeichnung</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.A2" office:value-type="string">
						<text:p text:style-name="P35">LV-Typ</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.A2" office:value-type="string">
						<text:p text:style-name="P35">SWS</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.D2" office:value-type="string">
						<text:p text:style-name="P35">ECTS</text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle5.A3" office:value-type="string">
						<text:p text:style-name="P21">Bezeichnung</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.B3" office:value-type="string">
						<text:p text:style-name="P19">Typ</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.C3" office:value-type="string">
						<text:p text:style-name="P19"></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.D3" office:value-type="string">
						<text:p text:style-name="P19"></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle5.A4" office:value-type="string">
						<text:p text:style-name="P21">Bezeichnung</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.B4" office:value-type="string">
						<text:p text:style-name="P27">Typ</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.C4" office:value-type="string">
						<text:p text:style-name="P19"></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.D4" office:value-type="string">
						<text:p text:style-name="P19"></text:p>
					</table:table-cell>
				</table:table-row>
				<table:table-row>
					<table:table-cell table:style-name="Tabelle5.A5" office:value-type="string">
						<text:p text:style-name="P37">Summenzeile:</text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.B5" office:value-type="string">
						<text:p text:style-name="P24"/>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.C5" office:value-type="string">
						<text:p text:style-name="P39"></text:p>
					</table:table-cell>
					<table:table-cell table:style-name="Tabelle5.D5" office:value-type="string">
						<text:p text:style-name="P39"></text:p>
					</table:table-cell>
				</table:table-row>
			</table:table>
			<text:p text:style-name="P3"/>
			<text:p text:style-name="P1"/>
			<text:list xml:id="list134241179162787" text:continue-list="list6602357775918451119" text:style-name="Numbering_20_1">
				<text:list-item>
					<text:list>
						<text:list-item>
							<text:h text:style-name="P64" text:outline-level="2">
								<text:bookmark-start text:name="__RefHeading__3662_462112980"/>Modulgrafik<text:bookmark-end text:name="__RefHeading__3662_462112980"/>
							</text:h>
						</text:list-item>
					</text:list>
				</text:list-item>
			</text:list>
			<text:p text:style-name="P8">[Die Modulgrafik ist derzeit noch nicht automatisch generierbar. Sie wird zu einem späteren Zeitpunkt in den Studienplan integriert werden.]</text:p>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1">
			</text:p>
			<text:p text:style-name="P1"/>
			<!-- <text:list xml:id="list134241269166448" text:continue-numbering="true" text:style-name="Numbering_20_1">-->
			<text:list xml:id="list134241269166448" text:continue-list="list134241179162787" text:style-name="Numbering_20_1">
				<text:list-item>
					<text:list>
						<text:list-item>
							<text:h text:style-name="P65" text:outline-level="2">
								<text:bookmark-start text:name="__RefHeading__3664_462112980"/>Modulbeschreibungen<text:bookmark-end text:name="__RefHeading__3664_462112980"/>
							</text:h>
						</text:list-item>
					</text:list>
				</text:list-item>
			</text:list>

			<text:p text:style-name="P8"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1"/>

			<xsl:apply-templates select="semester" mode="modulbeschreibung" />

			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1"/>
			<text:p text:style-name="P1"/>

			<text:p text:style-name="P1"/>
</xsl:template>

<xsl:template match="semester" mode="matrix">
	<table:table table:name="Tabelle1" table:style-name="Tabelle1">
		<table:table-column table:style-name="Tabelle1.A"/>
		<table:table-column table:style-name="Tabelle1.B" table:number-columns-repeated="2"/>
		<table:table-column table:style-name="Tabelle1.D"/>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
				<text:p text:style-name="P32"><xsl:value-of select="semester_nr"/>. Semester</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
				<text:p text:style-name="P15"/>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
				<text:p text:style-name="P15"/>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.D1" office:value-type="string">
				<text:p text:style-name="P15"/>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
				<text:p text:style-name="P32">Bezeichnung Modul bzw. LV</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
				<text:p text:style-name="P33">LV-Typ</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
				<text:p text:style-name="P33">SWS</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.D2" office:value-type="string">
				<text:p text:style-name="P33">ECTS</text:p>
			</table:table-cell>
		</table:table-row>

		<xsl:apply-templates match="lehrveranstaltung" mode="matrix" />

		<table:table-row>
			<table:table-cell table:style-name="Tabelle1.A8" office:value-type="string">
				<text:p text:style-name="P32">Summenzeile:</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.B8" office:value-type="string">
				<text:p text:style-name="P22"/>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.C8" office:value-type="string">
				<text:p text:style-name="P33"><xsl:value-of select="lv_summe_sws_semester"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.D8" office:value-type="string">
				<text:p text:style-name="P33"><xsl:value-of select="lv_summe_ects_semester"/></text:p>
			</table:table-cell>
		</table:table-row>
	</table:table>
</xsl:template>

<xsl:template match="lehrveranstaltung" mode="matrix">
<xsl:choose>
	<xsl:when test="lv_lehrtyp_kurzbz='modul'">
		<table:table-row>
			<table:table-cell table:style-name="Tabelle1.A3" office:value-type="string">
				<text:p text:style-name="P16"><xsl:value-of select="lv_bezeichnung"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.A3" office:value-type="string">
				<text:p text:style-name="P22"><xsl:value-of select="lv_lehrform_kurzbz"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.A3" office:value-type="string">
				<text:p text:style-name="P33">
                    <xsl:if test="lv_sws!=0">
                        <xsl:value-of select="lv_sws"/>
                    </xsl:if>
                </text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.D3" office:value-type="string">
				<text:p text:style-name="P33"><xsl:value-of select="lv_ects"/></text:p>
			</table:table-cell>
		</table:table-row>
	</xsl:when>
	<xsl:otherwise>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle1.A7" office:value-type="string">
				<text:p text:style-name="P16"><xsl:value-of select="lv_bezeichnung"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.B4" office:value-type="string">
				<text:p text:style-name="P17"><xsl:value-of select="lv_lehrform_kurzbz"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.C7" office:value-type="string">
				<text:p text:style-name="P17"><xsl:value-of select="lv_sws"/></text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle1.D4" office:value-type="string">
				<text:p text:style-name="P17"><xsl:value-of select="lv_ects"/></text:p>
			</table:table-cell>
		</table:table-row>
	</xsl:otherwise>
</xsl:choose>
	<xsl:apply-templates match="lehrveranstaltungen" mode="matrix" />
</xsl:template>

<xsl:template match="lehrveranstaltungen" mode="matrix">
	<xsl:apply-templates match="lehrveranstaltung" mode="matrix" />
</xsl:template>

<xsl:template match="semester" mode="modulbeschreibung">
    <text:h text:style-name="Numbering_20_3" text:outline-level="2">
	       <text:bookmark-start text:name="__RefHeading__3660_462112980"/><xsl:value-of select="semester_nr"/>. Semester<text:bookmark-end text:name="__RefHeading__3660_462112980"/>
    </text:h>

	<table:table table:name="Tabelle6" table:style-name="Tabelle6">
		<table:table-column table:style-name="Tabelle6.A"/>
		<table:table-column table:style-name="Tabelle6.B"/>
			<xsl:apply-templates match="lehrveranstaltung" mode="modulbeschreibung" />
	</table:table>
</xsl:template>

<xsl:template match="lehrveranstaltung" mode="modulbeschreibung">
<xsl:choose>
	<xsl:when test="lv_lehrtyp_kurzbz='modul'">
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A1" office:value-type="string">
				<text:p text:style-name="P40">Modul-Bezeichnung</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B1" office:value-type="string">
				<text:p text:style-name="P40"><xsl:value-of select="lv_bezeichnung"/></text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P28">Modul-Bezeichnung engl.</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P28"><xsl:value-of select="lv_bezeichnung_en"/></text:p>
			</table:table-cell>
		</table:table-row>
        <!--
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P28">Modulnummer</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P28" />
			</table:table-cell>
		</table:table-row>
    -->
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P28">Umfang (ECTS)</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P28"><xsl:value-of select="lv_ects"/></text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P28">Ausbildungssemester</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P28"><xsl:value-of select="lv_semester"/></text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P28">Sprache</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P28"><xsl:value-of select="lv_sprache"/></text:p>
			</table:table-cell>
		</table:table-row>
        <!--
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P28">Kompetenzbereich</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P28" />
			</table:table-cell>
		</table:table-row>
    -->
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P28">Pflicht-/Wahl-Modul</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P31">Pflichtmodul</text:p>
			</table:table-cell>
		</table:table-row>
	</xsl:when>
	<xsl:otherwise>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A7" office:value-type="string">
				<text:p text:style-name="P40">LV-Bezeichnung</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B7" office:value-type="string">
				<text:p text:style-name="P40"><xsl:value-of select="lv_bezeichnung"/></text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P28">LV-Bezeichnung engl.</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P28"><xsl:value-of select="lv_bezeichnung_en"/></text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P28">Umfang (ECTS)</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P28"><xsl:value-of select="lv_ects"/></text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P28">LV-Typ</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P28"><xsl:value-of select="lv_lehrform_langbz"/></text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P29">Ausbildungssemester</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P29"><xsl:value-of select="lv_semester"/></text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P5">Kurzbeschreibung</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P29">
                    <xsl:call-template name="replace">
                        <xsl:with-param name="string" select="lvinfo_kurzbeschreibung"/>
                    </xsl:call-template>
                </text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P5">Course Description</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P29">
                    <xsl:call-template name="replace">
                        <xsl:with-param name="string" select="lvinfo_kurzbeschreibung_en"/>
                    </xsl:call-template>
                </text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P5">Lernergebnisse</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P67">
                    <xsl:call-template name="replace">
                        <xsl:with-param name="string" select="lvinfo_lehrziele"/>
                    </xsl:call-template>
				</text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P5">Learning Outcomes</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P67">
                    <xsl:call-template name="replace">
                        <xsl:with-param name="string" select="lvinfo_lehrziele_en"/>
                    </xsl:call-template>
				</text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P29">Lehrinhalte</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P29">
                    <xsl:call-template name="replace">
                        <xsl:with-param name="string" select="lvinfo_lehrinhalte"/>
                    </xsl:call-template>
                </text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P29">Course Contents</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P29">
                    <xsl:call-template name="replace">
                        <xsl:with-param name="string" select="lvinfo_lehrinhalte_en"/>
                    </xsl:call-template>
                </text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P29">Vorkenntnisse</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P29">
                    <xsl:call-template name="replace">
                        <xsl:with-param name="string" select="lvinfo_voraussetzungen"/>
                    </xsl:call-template>
                </text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P29">Prerequisites</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P29">
                    <xsl:call-template name="replace">
                        <xsl:with-param name="string" select="lvinfo_voraussetzungen_en"/>
                    </xsl:call-template>
                </text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P30">Literatur/Literature</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P30">
                    <xsl:call-template name="replace">
    					<xsl:with-param name="string" select="lvinfo_unterlagen"/>
    				</xsl:call-template>
                </text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P29">Leistungsbeurteilung</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P30">
                    <xsl:call-template name="replace">
    					<xsl:with-param name="string" select="lvinfo_pruefungsordnung"/>
    				</xsl:call-template>
                </text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P29">Assessment Methods</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P30">
                    <xsl:call-template name="replace">
    					<xsl:with-param name="string" select="lvinfo_pruefungsordnung_en"/>
    				</xsl:call-template>
                </text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P29">Anwesenheit</text:p>
			</table:table-cell>
			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P29">
                    <xsl:call-template name="replace">
    					<xsl:with-param name="string" select="lvinfo_anwesenheit"/>
    				</xsl:call-template>
                </text:p>
			</table:table-cell>
		</table:table-row>
		<table:table-row>
			<table:table-cell table:style-name="Tabelle6.A15" office:value-type="string">
				<text:p text:style-name="P29">Attendance</text:p>
			</table:table-cell>			<table:table-cell table:style-name="Tabelle6.B15" office:value-type="string">
				<text:p text:style-name="P29">
                    <xsl:call-template name="replace">
    					<xsl:with-param name="string" select="lvinfo_anwesenheit_en"/>
    				</xsl:call-template>
                </text:p>
			</table:table-cell>
		</table:table-row>
	</xsl:otherwise>
</xsl:choose>
	<xsl:apply-templates match="lehrveranstaltungen" mode="modulbeschreibung" />
</xsl:template>

<xsl:template match="lehrveranstaltungen" mode="modulbeschreibung">
	<xsl:apply-templates match="lehrveranstaltung" mode="modulbeschreibung" />
</xsl:template>

<xsl:template name="replace">
    <xsl:param name="string"/>
    <xsl:choose>
        <xsl:when test="contains($string,'\n')">
            <xsl:call-template name="replaceaufzaehlungszeichen">
                <xsl:with-param name="string" select="substring-before($string,'\n')"/>
            </xsl:call-template>
            <text:line-break/>
            <xsl:call-template name="replace">
                <xsl:with-param name="string" select="substring-after($string,'\n')"/>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="replaceaufzaehlungszeichen">
                <xsl:with-param name="string" select="$string"/>
            </xsl:call-template>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template name="replaceaufzaehlungszeichen">
    <xsl:param name="string"/>
    <xsl:choose>
        <xsl:when test="contains($string,'- ') and substring-before($string,'- ')=''">
            <xsl:value-of select="substring-before($string,'- ')"/>
            <xsl:text>• </xsl:text>
            <xsl:call-template name="replace">
                <xsl:with-param name="string" select="substring-after($string,'- ')"/>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$string"/>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>
