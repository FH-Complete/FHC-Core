<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>
  <xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="projekte">
<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
  <office:scripts/>
  <office:font-face-decls>
    <style:font-face style:name="Wingdings" svg:font-family="Wingdings" style:font-pitch="variable" style:font-charset="x-symbol"/>
    <style:font-face style:name="Symbol" svg:font-family="Symbol" style:font-family-generic="roman" style:font-pitch="variable" style:font-charset="x-symbol"/>
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
    <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Footer">
      <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0.635cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
    </style:style>
    <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
      <style:text-properties style:language-asian="zxx" style:country-asian="none"/>
    </style:style>
    <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:break-before="page"/>
    </style:style>
    <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L1"/>
    <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L2"/>
    <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L3"/>
    <style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L4"/>
    <style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L5"/>
    <style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="Numbering_20_1"/>
    <style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L6"/>
    <style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L7"/>
    <style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L8"/>
    <style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="L9"/>
    <style:style style:name="P14" style:family="paragraph" style:parent-style-name="Heading_20_2" style:list-style-name="Numbering_20_1"/>
    <style:style style:name="P15" style:family="paragraph" style:parent-style-name="Heading_20_2" style:list-style-name="Numbering_20_1">
      <style:paragraph-properties fo:orphans="2" fo:widows="2" style:writing-mode="lr-tb"/>
    </style:style>
    <style:style style:name="P16" style:family="paragraph" style:parent-style-name="Heading_20_3" style:list-style-name="Numbering_20_1"/>
    <style:style style:name="P17" style:family="paragraph" style:parent-style-name="Deckblatt_20_Titel_20_1" style:master-page-name="First_20_Page">
      <style:paragraph-properties style:page-number="auto"/>
    </style:style>
    <style:style style:name="P18" style:family="paragraph" style:parent-style-name="Contents_20_3">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="17cm" style:type="right" style:leader-style="dotted" style:leader-text="."/>
        </style:tab-stops>
      </style:paragraph-properties>
    </style:style>
    <style:style style:name="P19" style:family="paragraph" style:parent-style-name="Contents_20_1">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="17cm" style:type="right" style:leader-style="dotted" style:leader-text="."/>
        </style:tab-stops>
      </style:paragraph-properties>
    </style:style>
    <style:style style:name="P20" style:family="paragraph" style:parent-style-name="Contents_20_Heading" style:master-page-name="Convert_20_1">
      <style:paragraph-properties style:page-number="auto"/>
    </style:style>
    <style:style style:name="P21" style:family="paragraph" style:parent-style-name="Contents_20_2">
      <style:paragraph-properties>
        <style:tab-stops>
          <style:tab-stop style:position="17cm" style:type="right" style:leader-style="dotted" style:leader-text="."/>
        </style:tab-stops>
      </style:paragraph-properties>
    </style:style>
    <style:style style:name="P22" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:break-after="page"/>
    </style:style>

    <style:style style:name="T1" style:family="text">
      <style:text-properties fo:font-weight="normal" style:font-weight-asian="normal" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="T2" style:family="text"/>
    <style:style style:name="fr1" style:family="graphic" style:parent-style-name="Frame">
      <style:graphic-properties style:wrap="dynamic" style:number-wrapped-paragraphs="1" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="right" style:horizontal-rel="page-content" fo:background-color="#ffffff" style:background-transparency="100%" style:writing-mode="lr-tb" draw:wrap-influence-on-position="once-successive">
        <style:background-image/>
      </style:graphic-properties>
    </style:style>
    <style:style style:name="fr2" style:family="graphic" style:parent-style-name="Graphics">
      <style:graphic-properties fo:margin-left="0.319cm" fo:margin-right="0.319cm" style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:padding="0.026cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
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
      <text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-0.635cm" fo:margin-left="1.27cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.905cm" fo:text-indent="-0.635cm" fo:margin-left="1.905cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-0.635cm" fo:margin-left="2.54cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.175cm" fo:text-indent="-0.635cm" fo:margin-left="3.175cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.81cm" fo:text-indent="-0.635cm" fo:margin-left="3.81cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.445cm" fo:text-indent="-0.635cm" fo:margin-left="4.445cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.08cm" fo:text-indent="-0.635cm" fo:margin-left="5.08cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.715cm" fo:text-indent="-0.635cm" fo:margin-left="5.715cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.35cm" fo:text-indent="-0.635cm" fo:margin-left="6.35cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
    </text:list-style>
    <text:list-style style:name="L3">
      <text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-0.635cm" fo:margin-left="1.27cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.905cm" fo:text-indent="-0.635cm" fo:margin-left="1.905cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-0.635cm" fo:margin-left="2.54cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.175cm" fo:text-indent="-0.635cm" fo:margin-left="3.175cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.81cm" fo:text-indent="-0.635cm" fo:margin-left="3.81cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.445cm" fo:text-indent="-0.635cm" fo:margin-left="4.445cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.08cm" fo:text-indent="-0.635cm" fo:margin-left="5.08cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.715cm" fo:text-indent="-0.635cm" fo:margin-left="5.715cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.35cm" fo:text-indent="-0.635cm" fo:margin-left="6.35cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols" text:bullet-char="–">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
    </text:list-style>
    <text:list-style style:name="L4">
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
    <text:list-style style:name="L5">
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
    <text:list-style style:name="L6">
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
    <text:list-style style:name="L7">
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
    <text:list-style style:name="L8">
      <text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.136cm" fo:text-indent="-0.635cm" fo:margin-left="1.136cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
    </text:list-style>
    <text:list-style style:name="L9">
      <text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.136cm" fo:text-indent="-0.635cm" fo:margin-left="1.136cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.771cm" fo:text-indent="-0.635cm" fo:margin-left="1.771cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.406cm" fo:text-indent="-0.635cm" fo:margin-left="2.406cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.041cm" fo:text-indent="-0.635cm" fo:margin-left="3.041cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.676cm" fo:text-indent="-0.635cm" fo:margin-left="3.676cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.311cm" fo:text-indent="-0.635cm" fo:margin-left="4.311cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.946cm" fo:text-indent="-0.635cm" fo:margin-left="4.946cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.581cm" fo:text-indent="-0.635cm" fo:margin-left="5.581cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.216cm" fo:text-indent="-0.635cm" fo:margin-left="6.216cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
      <text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.851cm" fo:text-indent="-0.635cm" fo:margin-left="6.851cm"/>
        </style:list-level-properties>
      </text:list-level-style-bullet>
    </text:list-style>
  </office:automatic-styles>
  <office:body xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0">
    <office:text text:use-soft-page-breaks="true">
      <office:forms form:automatic-focus="false" form:apply-design-mode="false"/>
		<xsl:apply-templates select="projekt"/>


    </office:text>
  </office:body>
</office:document-content>
</xsl:template>

<xsl:template match="projekt">
      <text:sequence-decls xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0">
        <text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Table"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Text"/>
        <text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
      </text:sequence-decls>
      <text:p text:style-name="P17"><xsl:value-of select="projekt_titel"/></text:p>
      <text:p text:style-name="Deckblatt_20_Titel_20_2">Projektbeschreibung</text:p>
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
      <text:p text:style-name="Deckblatt_20_Titel_20_3">FHTW - Abteilung <xsl:value-of select="projekt_oe"/></text:p>
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
        <text:index-body xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
          <text:index-title text:style-name="Sect1" text:name="Inhaltsverzeichnis1_Head">
            <text:p text:style-name="P20">Inhaltsverzeichnis</text:p>
          </text:index-title>
          <text:p text:style-name="P19">
            <text:a  xlink:type="simple" xlink:href="#__RefHeading__359_971256360" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>1 Projektbeschreibung<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P21">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__339_971256360" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>1.1 Eckdaten<text:tab/>3</text:a>
          </text:p>
          <text:p text:style-name="P19">
            <text:a xlink:type="simple" xlink:href="#__RefHeading__361_971256360" text:style-name="Index_20_Link" text:visited-style-name="Index_20_Link"><text:s/>2 Projektphasen<text:tab/>3</text:a>
          </text:p>

        </text:index-body>
      </text:table-of-content>
      <text:list xml:id="list224296688" text:style-name="Numbering_20_1">
        <text:list-header>
          <text:h text:style-name="Heading_20_1" text:outline-level="1"/>
        </text:list-header>
      </text:list>
      <text:p text:style-name="P3"/>
      <text:list xml:id="list1922406822" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:h text:style-name="Heading_20_1" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__359_971256360"/>Projektbeschreibung<text:bookmark-end text:name="__RefHeading__359_971256360"/></text:h>
        </text:list-item>
      </text:list>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"><xsl:value-of select="projekt_beschreibung"/></text:p>
      <text:p text:style-name="Standard"/>
      <text:list xml:id="list849006553" text:continue-numbering="true" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:h text:style-name="Heading_20_2" text:outline-level="2"><text:bookmark-start text:name="__RefHeading__339_971256360"/>Eckdaten<text:bookmark-end text:name="__RefHeading__339_971256360"/></text:h>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:list xml:id="list1105768111" text:style-name="L1">
        <text:list-item>
          <text:p text:style-name="P4">Titel: <xsl:value-of select="projekt_titel"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P4">Nummer: <xsl:value-of select="projekt_nummer"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P4">Beginn: <xsl:value-of select="projekt_beginn"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P4">Ende: <xsl:value-of select="projekt_ende"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P4">Budget: <xsl:value-of select="projekt_budget"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P4">Ressourcen:</text:p>
          <text:list>
        	<xsl:apply-templates select="projekt_ressourcen"/>     
     
          </text:list>
        </text:list-item>
      </text:list>
 <text:p text:style-name="P22"/>
      <text:p text:style-name="Standard"/>
      <text:p text:style-name="Standard"/>
      <text:list xml:id="list1375412226" text:continue-list="list849006553" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:h text:style-name="Heading_20_1" text:outline-level="1"><text:bookmark-start text:name="__RefHeading__361_971256360"/>Projektphasen<text:bookmark-end text:name="__RefHeading__361_971256360"/></text:h>
        </text:list-item>
      </text:list>
      <text:p text:style-name="Standard">Das Projekt wird in folgende Phasen gegliedert:</text:p>
      <text:p text:style-name="Standard">
        <draw:frame xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" draw:style-name="fr3" draw:name="Grafik1" text:anchor-type="paragraph" svg:width="95%" svg:height="30%" draw:z-index="9">
          <draw:image xlink:href="Pictures/20000001000071B00000242C6CF7933F.svg" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
        </draw:frame>
      </text:p>
        <xsl:apply-templates select="phasen"/>
 </xsl:template>


  <xsl:template match="phasen">
    <xsl:apply-templates select="phase"/>
  </xsl:template>


    <xsl:template match="phase">
<text:list xml:id="list54441676" text:continue-list="list1375412226" text:continue-numbering="true" text:style-name="Numbering_20_1" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:h text:style-name="Heading_20_2" text:outline-level="2"><text:bookmark-start text:name="__RefHeading__341_971256360"/><text:soft-page-break/><xsl:value-of select="phase_bezeichnung"/><text:bookmark-end text:name="__RefHeading__341_971256360"/></text:h>
              <text:list>
                <text:list-item>
                  <text:h text:style-name="Heading_20_3" text:outline-level="3"><text:bookmark-start text:name="__RefHeading__343_971256360"/>Beschreibung<text:bookmark-end text:name="__RefHeading__343_971256360"/></text:h>
                </text:list-item>
              </text:list>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:p text:style-name="Standard"><xsl:value-of select="phase_beschreibung"/></text:p>      
      <text:p text:style-name="Standard"/>
      <text:list xml:id="list600377939" text:continue-list="list54441676" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:list>
                <text:list-item>
                  <text:h text:style-name="Heading_20_3" text:outline-level="3"><text:bookmark-start text:name="__RefHeading__345_971256360"/>Eckdaten<text:bookmark-end text:name="__RefHeading__345_971256360"/></text:h>
                </text:list-item>
              </text:list>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:list xml:id="list117873181" text:style-name="L4">
        <text:list-item>
          <text:p text:style-name="P7">Beginn: <xsl:value-of select="phase_beginn"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P7">Ende: <xsl:value-of select="phase_end"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P7">Budget: <xsl:value-of select="phase_budget"/></text:p>
        </text:list-item>
        <text:list-item>
          <text:p text:style-name="P7">Ressourcen:</text:p>
          <text:list>
			<xsl:apply-templates select="phase_ressourcen"/>
          </text:list>
        </text:list-item>
      </text:list>

      <text:p text:style-name="Standard"/>
      <text:list xml:id="list1389790540" text:continue-list="list54441676" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:list>
                <text:list-item>
                	<text:h text:style-name="Heading_20_3" text:outline-level="3"><text:bookmark-start text:name="__RefHeading__343_971256360"/>Tasks<text:bookmark-end text:name="__RefHeading__343_971256360"/></text:h>
                </text:list-item>
              </text:list>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:list xml:id="list1017227384" text:continue-list="list117999972" text:style-name="L4">
			<xsl:apply-templates select="task"/>
      </text:list>
	<xsl:apply-templates select="unterphase"/>
 </xsl:template>






<xsl:template match="unterphase">
<text:list xml:id="list1389790543" text:continue-list="list1389790540" text:style-name="Numbering_20_1" text:continue-numbering="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0">
		<text:list-item>
			<text:list>
				<text:list-item>
					<text:list>
						<text:list-item>
                			<text:h text:style-name="Heading_20_3" text:outline-level="3"><text:bookmark-start text:name="__RefHeading__343_971256360"/>Unterphase: <xsl:value-of select="phase_bezeichnung"/><text:bookmark-end text:name="__RefHeading__343_971256360"/></text:h>
							<text:list xml:id="list600377900">
								<text:list-item>
									<text:h text:style-name="Heading_20_3" text:outline-level="3"><text:bookmark-start text:name="__RefHeading__345_971256360"/>Beschreibung<text:bookmark-end text:name="__RefHeading__345_971256360"/></text:h>
								</text:list-item>
							</text:list>
               			</text:list-item>
					</text:list>
				</text:list-item>
			</text:list>
		</text:list-item>
	</text:list>
	<text:p text:style-name="Standard"><xsl:value-of select="phase_beschreibung"/></text:p>
	<text:p text:style-name="Standard"/>
    <text:list xml:id="list600377935" text:continue-list="list1389790540" text:style-name="Numbering_20_1">
	<text:list-item>
  		<text:list>
    		<text:list-item>
      			<text:list>
        			<text:list-item>
						<text:list>
							<text:list-item>
		              			<text:h text:style-name="Heading_20_3" text:outline-level="3"><text:bookmark-start text:name="__RefHeading__345_971256360"/>Eckdaten<text:bookmark-end text:name="__RefHeading__345_971256360"/></text:h>
			<text:list xml:id="list117873187" text:style-name="L4">
				<text:list-item>
		      		<text:p text:style-name="P7">Beginn: <xsl:value-of select="phase_beginn"/></text:p>
		    	</text:list-item>
		    	<text:list-item>
					<text:p text:style-name="P7">Ende: <xsl:value-of select="phase_end"/></text:p>
		   		</text:list-item>
		    	<text:list-item>
					<text:p text:style-name="P7">Budget: <xsl:value-of select="phase_budget"/></text:p>
		    	</text:list-item>
		   		<text:list-item>
					<text:p text:style-name="P7">Ressourcen:</text:p>
					<text:list>
						<xsl:apply-templates select="phase_ressourcen"/>
	      			</text:list>
				</text:list-item>
		  	</text:list>
		            			</text:list-item>
		          			</text:list>
		        		</text:list-item>
		      		</text:list>
		    	</text:list-item>
			</text:list>
		    	</text:list-item>
			</text:list>
	<text:p text:style-name="Standard"/>
			<text:list xml:id="list600377937" text:continue-list="list1389790540" text:style-name="Numbering_20_1">
        <text:list-item>
          <text:list>
            <text:list-item>
              <text:list>
                <text:list-item>
                  <text:list>
                    <text:list-item>
		              				<text:h text:style-name="Heading_20_3" text:outline-level="3"><text:bookmark-start text:name="__RefHeading__345_971256360"/>Tasks<text:bookmark-end text:name="__RefHeading__345_971256360"/></text:h>
                    </text:list-item>
                  </text:list>
                </text:list-item>
              </text:list>
            </text:list-item>
          </text:list>
        </text:list-item>
      </text:list>
      <text:list xml:id="list1017227382" text:continue-list="list117999972" text:style-name="L4">
			<xsl:apply-templates select="task"/>
      </text:list>
<text:p text:style-name="Standard"/>
 </xsl:template>

<xsl:template match="task">
	<text:list-item>
			<text:p text:style-name="P7" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"><text:s/><xsl:value-of select="task_bezeichnung"/> (Ressource: <text:s/><xsl:value-of select="task_ressource"/>)</text:p>
	<xsl:if test="task_beschreibung != ''">	
	<text:list>
		<text:list-item>
	  		<text:p text:style-name="P7"><xsl:value-of select="task_beschreibung"/></text:p>
		</text:list-item>
	</text:list>
	</xsl:if>
		
	</text:list-item>
</xsl:template>



  <xsl:template match="phase_ressourcen">
    <xsl:apply-templates select="ressource"/>
  </xsl:template>

  <xsl:template match="ressource">
            <text:list-item>
              <text:p text:style-name="P7" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"><xsl:value-of select="bezeichnung"/></text:p>
            </text:list-item>
  </xsl:template>


  <xsl:template match="projekt_ressourcen">
    <xsl:apply-templates select="pr_ressource"/>
  </xsl:template>

  <xsl:template match="pr_ressource">
            <text:list-item>
              <text:p text:style-name="P4" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"><xsl:value-of select="bezeichnung"/></text:p>
            </text:list-item>
</xsl:template>

</xsl:stylesheet>