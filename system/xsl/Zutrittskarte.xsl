<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"  xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2" grddl:transformation="http://docs.oasis-open.org/office/1.2/xslt/odf2rdf.xsl">

<xsl:output method="xml" encoding="UTF-8" version="1.0" indent="yes" />


<xsl:template match="zutrittskarte">
<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2" grddl:transformation="http://docs.oasis-open.org/office/1.2/xslt/odf2rdf.xsl">
  <office:scripts/>
  <office:font-face-decls>
    <style:font-face style:name="Lohit Hindi1" svg:font-family="'Lohit Hindi'"/>
    <style:font-face style:name="Times New Roman" svg:font-family="'Times New Roman'" style:font-family-generic="roman" style:font-pitch="variable"/>
    <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="Droid Sans Fallback" svg:font-family="'Droid Sans Fallback'" style:font-family-generic="system" style:font-pitch="variable"/>
    <style:font-face style:name="Lohit Hindi" svg:font-family="'Lohit Hindi'" style:font-family-generic="system" style:font-pitch="variable"/>
  </office:font-face-decls>
  <office:automatic-styles>
    <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
      <style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false" fo:break-before="page"/>
      <style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:line-height="150%"/>
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
      <style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="P7" style:family="paragraph">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    <style:style style:name="T1" style:family="text">
      <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
    </style:style>
    
    <style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="transparent" style:background-transparency="100%" style:shadow="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard">
        <style:background-image/>
      </style:graphic-properties>
    </style:style>
    <style:style style:name="gr1" style:family="graphic">
      <style:graphic-properties draw:stroke="none" svg:stroke-color="#000000" draw:fill="none" draw:fill-color="#ffffff" fo:min-height="0.51cm" style:run-through="foreground" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph"/>
    </style:style>
  </office:automatic-styles>
  <office:body>
    <office:text>
      <text:sequence-decls>
        <text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Table"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Text"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
      </text:sequence-decls>
 	
	<xsl:apply-templates select="mitarbeiter"/>
	<xsl:apply-templates select="student"/>
	<xsl:apply-templates select="datum"/>
    
    </office:text>
  </office:body>
</office:document-content>
</xsl:template>
<xsl:template match="mitarbeiter">
	<xsl:variable name="uid" select="uid" />
	<xsl:variable name="idx"><xsl:value-of select="position()-1"/></xsl:variable>
     <text:p text:style-name="P4"/>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>


 <text:p text:style-name="P1">
        <draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}3" draw:style-name="gr1" draw:text-style-name="P7" svg:width="5.765cm" svg:height="0.511cm" svg:x="2.335cm" svg:y="1.773cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1">Pers.-Nr. <xsl:value-of select="personalnummer"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>
        <draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}4" draw:style-name="gr1" draw:text-style-name="P7" svg:width="5.79cm" svg:height="0.511cm" svg:x="2.334cm" svg:y="2.282cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1">Ausgestellt am <xsl:value-of select="ausstellungsdatum"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>
        <draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}2" draw:style-name="gr1" draw:text-style-name="P7" svg:width="5.806cm" svg:height="0.511cm" svg:x="2.335cm" svg:y="1.263cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1"><xsl:value-of select="nachname"/><xsl:text> </xsl:text><xsl:value-of select="titelpost"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>
        <draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}1" draw:style-name="gr1" draw:text-style-name="P7" svg:width="5.827cm" svg:height="0.511cm" svg:x="2.335cm" svg:y="0.753cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1"><xsl:value-of select="vorname"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>
        <draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}0" draw:style-name="gr1" draw:text-style-name="P7" svg:width="5.733cm" svg:height="0.511cm" svg:x="2.335cm" svg:y="0.243cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1"><xsl:value-of select="titelpre"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>
        <draw:frame draw:style-name="fr1" draw:name="Grafik_{$uid}" text:anchor-type="char" svg:x="0.215cm" svg:y="0.046cm" svg:width="2cm" svg:height="2.701cm" draw:z-index="{$idx}5">
          <draw:image xlink:href="Pictures/{$uid}.jpg" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
        </draw:frame>
      </text:p>
	
</xsl:template>
<xsl:template match="student">
	<xsl:variable name="uid" select="uid" />
	<xsl:variable name="idx"><xsl:value-of select="position()-1"/></xsl:variable>
     <text:p text:style-name="P4"/>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>


 <text:p text:style-name="P1">
		<draw:frame draw:style-name="fr1" draw:name="Grafik_{$uid}" text:anchor-type="char" svg:x="0.215cm" svg:y="0.046cm" svg:width="2cm" svg:height="2.701cm" draw:z-index="{$idx}5">
          <draw:image xlink:href="Pictures/{$uid}.jpg" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
        </draw:frame>

		<draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}0" draw:style-name="gr1" draw:text-style-name="P7" svg:width="5.733cm" svg:height="0.511cm" svg:x="2.335cm" svg:y="0.035cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1"><xsl:value-of select="titelpre"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>

		<draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}1" draw:style-name="gr1" draw:text-style-name="P7" svg:width="5.827cm" svg:height="0.511cm" svg:x="2.335cm" svg:y="0.46cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1"><xsl:value-of select="vorname"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>

        <draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}2" draw:style-name="gr1" draw:text-style-name="P7" svg:width="5.806cm" svg:height="0.511cm" svg:x="2.335cm" svg:y="0.896cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1"><xsl:value-of select="nachname"/><xsl:text> </xsl:text><xsl:value-of select="titelpost"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>

        <draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}3" draw:style-name="gr1" draw:text-style-name="P7" svg:width="5.765cm" svg:height="0.511cm" svg:x="2.335cm" svg:y="1.349cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1"><xsl:value-of select="gebdatum"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>

		<draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}4" draw:style-name="gr1" draw:text-style-name="P7" svg:width="5.79cm" svg:height="0.511cm" svg:x="2.335cm" svg:y="1.843cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1"><xsl:value-of select="matrikelnummer"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>

		<draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}4" draw:style-name="gr1" draw:text-style-name="P7" svg:width="2.652cm" svg:height="0.511cm" svg:x="2.335cm" svg:y="2.282cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1">Gültig bis/valid thru</text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>

		<draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}5" draw:style-name="gr1" draw:text-style-name="P7" svg:width="1.816cm" svg:height="0.511cm" svg:x="5.704cm" svg:y="2.282cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1"><xsl:value-of select="gueltigbis"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>

      </text:p>
	
</xsl:template>
<xsl:template match="datum">
	<xsl:variable name="uid" select="uid" />
	<xsl:variable name="idx"><xsl:value-of select="position()-1"/></xsl:variable>
     <text:p text:style-name="P4"/>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>
      <text:p text:style-name="P3"/>


 <text:p text:style-name="P1">
		
		<draw:frame text:anchor-type="paragraph" draw:z-index="{$idx}5" draw:style-name="gr1" draw:text-style-name="P7" svg:width="1.816cm" svg:height="0.511cm" svg:x="5.704cm" svg:y="2.282cm">
          <draw:text-box>
            <text:p text:style-name="P7">
              <text:span text:style-name="T1"><xsl:value-of select="gueltigbis"/></text:span>
            </text:p>
          </draw:text-box>
        </draw:frame>

      </text:p>
	
</xsl:template>
</xsl:stylesheet >
