<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"  
xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" 
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" 
xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" 
xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" 
xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"  
xmlns:xlink="http://www.w3.org/1999/xlink" 
xmlns:dc="http://purl.org/dc/elements/1.1/" 
xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" 
xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" 
xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" 
xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" 
xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" 
xmlns:math="http://www.w3.org/1998/Math/MathML" 
xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" 
xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" 
xmlns:ooo="http://openoffice.org/2004/office" 
xmlns:ooow="http://openoffice.org/2004/writer" 
xmlns:oooc="http://openoffice.org/2004/calc" 
xmlns:dom="http://www.w3.org/2001/xml-events" 
xmlns:xforms="http://www.w3.org/2002/xforms" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xmlns:rpt="http://openoffice.org/2005/report" 
xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" 
xmlns:xhtml="http://www.w3.org/1999/xhtml" 
xmlns:grddl="http://www.w3.org/2003/g/data-view#" 
xmlns:tableooo="http://openoffice.org/2009/table" 
xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" 
xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" 
xmlns:css3t="http://www.w3.org/TR/css3-text/" 
office:version="1.2" 
grddl:transformation="http://docs.oasis-open.org/office/1.2/xslt/odf2rdf.xsl" 
xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0">

<xsl:output method="xml" encoding="UTF-8" version="1.0" indent="yes" />

<xsl:template match="zutrittskarte">
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
xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" 
xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" 
xmlns:ooo="http://openoffice.org/2004/office" 
xmlns:ooow="http://openoffice.org/2004/writer" 
xmlns:oooc="http://openoffice.org/2004/calc" 
xmlns:dom="http://www.w3.org/2001/xml-events" 
xmlns:xforms="http://www.w3.org/2002/xforms" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xmlns:rpt="http://openoffice.org/2005/report" 
xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" 
xmlns:xhtml="http://www.w3.org/1999/xhtml" 
xmlns:grddl="http://www.w3.org/2003/g/data-view#" 
xmlns:officeooo="http://openoffice.org/2009/office" 
xmlns:tableooo="http://openoffice.org/2009/table" 
xmlns:drawooo="http://openoffice.org/2010/draw" 
xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" 
xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" 
xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" 
xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" 
xmlns:css3t="http://www.w3.org/TR/css3-text/" 
office:version="1.2">
	<office:scripts/>
	<office:font-face-decls>
		<style:font-face style:name="Lohit Hindi1" svg:font-family="&apos;Lohit Hindi&apos;"/>
		<style:font-face style:name="Times New Roman" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
		<style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="TheSans" svg:font-family="TheSans" style:font-family-generic="swiss" style:font-pitch="variable"/>
		<style:font-face style:name="Droid Sans Fallback" svg:font-family="&apos;Droid Sans Fallback&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
		<style:font-face style:name="Lohit Hindi" svg:font-family="&apos;Lohit Hindi&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
	</office:font-face-decls>
	<office:automatic-styles>
		<style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
			<style:text-properties style:font-name="Liberation Sans" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
			<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
			<style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false" fo:break-before="page"/>
			<style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P4" style:family="paragraph">
			<loext:graphic-properties draw:fill="none" draw:fill-color="#ffffff"/>
			<style:text-properties style:font-name="Liberation Sans" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="P5" style:family="paragraph">
			<style:paragraph-properties fo:line-height="100%" style:writing-mode="lr-tb"/>
		</style:style>
		<style:style style:name="P6" style:family="paragraph">
			<loext:graphic-properties draw:fill="none" draw:fill-color="#ffffff"/>
			<style:paragraph-properties fo:line-height="100%" style:writing-mode="lr-tb"/>
			<style:text-properties fo:color="#095e96" style:font-name="Liberation Sans" fo:font-size="8pt" fo:font-style="normal" fo:font-weight="normal" style:font-size-asian="8pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="8pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="T1" style:family="text">
			<style:text-properties style:font-name="Liberation Sans" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
		</style:style>
		<style:style style:name="T2" style:family="text">
			<style:text-properties fo:color="#00649c" style:font-name="Liberation Sans" fo:font-size="12pt" fo:font-style="normal" fo:font-weight="normal" style:font-size-asian="12pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="12pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="T3" style:family="text">
			<style:text-properties fo:color="#71787d" style:font-name="Liberation Sans" fo:font-size="12pt" fo:font-style="normal" fo:font-weight="normal" style:font-size-asian="12pt" style:font-style-asian="normal" style:font-weight-asian="normal" style:font-size-complex="12pt" style:font-style-complex="normal" style:font-weight-complex="normal"/>
		</style:style>
		<style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
			<style:graphic-properties style:run-through="foreground" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
		</style:style>
		<style:style style:name="fr2" style:family="graphic" style:parent-style-name="Graphics">
			<style:graphic-properties style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
		</style:style>
		<style:style style:name="fr3" style:family="graphic" style:parent-style-name="Graphics">
			<style:graphic-properties style:vertical-pos="from-top" style:vertical-rel="page" style:horizontal-pos="from-left" style:horizontal-rel="page" fo:background-color="transparent" draw:fill="none" draw:fill-color="#ffffff" style:shadow="none" draw:shadow-opacity="100%" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
		</style:style>
		<style:style style:name="gr1" style:family="graphic">
			<style:graphic-properties draw:stroke="none" svg:stroke-color="#000000" draw:fill="none" draw:fill-color="#ffffff" fo:min-height="0.453cm" style:run-through="foreground" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="page" style:horizontal-pos="from-left" style:horizontal-rel="page" draw:wrap-influence-on-position="once-concurrent" style:flow-with-text="false"/>
		</style:style>
		<style:style style:name="gr2" style:family="graphic">
			<style:graphic-properties draw:stroke="none" svg:stroke-color="#000000" draw:fill="none" draw:fill-color="#ffffff" fo:min-height="1.462cm" style:run-through="foreground" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" draw:wrap-influence-on-position="once-concurrent" style:flow-with-text="false"/>
		</style:style>
	</office:automatic-styles>
	<office:body>
		<office:text>
			<office:forms form:automatic-focus="false" form:apply-design-mode="false"/>
			<text:sequence-decls>
				<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
				<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
			</text:sequence-decls>
	
			<xsl:apply-templates select="student"/>

		</office:text>
	</office:body>
</office:document-content>
</xsl:template>


<xsl:template match="student">
	<xsl:variable name="uid" select="uid" />
	<xsl:variable name="idx"><xsl:value-of select="position()-1"/></xsl:variable>
			<text:p text:style-name="P3">
				<draw:frame draw:style-name="fr2" draw:name="Bild2" text:anchor-type="paragraph" svg:x="5.302cm" svg:y="0.302cm" svg:width="2.519cm" svg:height="1.295cm" draw:z-index="7">
					<draw:image xlink:href="Pictures/fhtw_logo_schwarz.png" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad" loext:mime-type="image/png"/>
				</draw:frame>
				<draw:frame text:anchor-type="paragraph" draw:z-index="5" draw:style-name="gr2" draw:text-style-name="P6" svg:width="7.81cm" svg:height="1.463cm" svg:x="0.353cm" svg:y="0.21cm">
					<draw:text-box>
						<text:p text:style-name="P5">
							<text:span text:style-name="T2">Zutrittskarte</text:span>
						</text:p>
						<text:p text:style-name="P5">
							<text:span text:style-name="T2">Hertha Firnberg Schulen</text:span>
						</text:p>
					</draw:text-box>
				</draw:frame>
			</text:p>
			<text:p text:style-name="P2"/>
			<text:p text:style-name="P2"/>
			<text:p text:style-name="P2"/>
			<text:p text:style-name="P2"/>
			<text:p text:style-name="P1">
				<draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}0" draw:style-name="gr1" draw:text-style-name="P4" svg:width="5.733cm" svg:height="0.479cm" svg:x="2.505cm" svg:y="2.97cm">
					<draw:text-box>
						<text:p text:style-name="P1"/>
						<xsl:if test="string-length(gebdatum)=0">
							<text:p text:style-name="P1"/>
						</xsl:if>
						<text:p text:style-name="P1"><xsl:value-of select="vorname"/></text:p>
						<text:p text:style-name="P1"><xsl:value-of select="nachname"/></text:p>
						<xsl:if test="string-length(gebdatum)!=0">
							<text:p text:style-name="P1"><xsl:value-of select="gebdatum"/></text:p>
						</xsl:if>
					</draw:text-box>
				</draw:frame>
				
				<draw:frame draw:style-name="fr3" draw:name="Grafik_{$uid}" text:anchor-type="char" svg:x="0.55cm" svg:y="1.829cm" svg:width="1.75cm" svg:height="2.379cm" draw:z-index="{$idx}5">
					<draw:image xlink:href="Pictures/{$uid}.jpg" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad" loext:mime-type="image/jpeg"/>
				</draw:frame>
			</text:p>
</xsl:template>

</xsl:stylesheet >
