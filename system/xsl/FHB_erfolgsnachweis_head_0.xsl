<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="zeugnisse">
	<xsl:apply-templates select="zeugnis"/>
</xsl:template>
<xsl:template match="zeugnis">
<office:document-styles xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
  <office:font-face-decls>
    <style:font-face style:name="DejaVu Sans1" svg:font-family="'DejaVu Sans'" style:font-family-generic="swiss"/>
    <style:font-face style:name="Times New Roman" svg:font-family="'Times New Roman'" style:font-family-generic="roman" style:font-pitch="variable"/>
    <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
    <style:font-face style:name="DejaVu Sans" svg:font-family="'DejaVu Sans'" style:font-family-generic="system" style:font-pitch="variable"/>
    <style:font-face style:name="Droid Sans" svg:font-family="'Droid Sans'" style:font-family-generic="system" style:font-pitch="variable"/>
  </office:font-face-decls>
  <office:styles>
    <style:default-style style:family="graphic">
      <style:graphic-properties svg:stroke-color="#808080" draw:fill-color="#cfe7f5" fo:wrap-option="no-wrap" draw:shadow-offset-x="0.3cm" draw:shadow-offset-y="0.3cm" draw:start-line-spacing-horizontal="0.283cm" draw:start-line-spacing-vertical="0.283cm" draw:end-line-spacing-horizontal="0.283cm" draw:end-line-spacing-vertical="0.283cm" style:flow-with-text="false"/>
      <style:paragraph-properties style:text-autospace="ideograph-alpha" style:line-break="strict" style:writing-mode="lr-tb" style:font-independent-line-spacing="false">
        <style:tab-stops/>
      </style:paragraph-properties>
      <style:text-properties style:use-window-font-color="true" fo:font-size="12pt" fo:language="de" fo:country="AT" style:letter-kerning="true" style:font-size-asian="10.5pt" style:language-asian="zh" style:country-asian="CN" style:font-size-complex="12pt" style:language-complex="hi" style:country-complex="IN"/>
    </style:default-style>
    <style:default-style style:family="paragraph">
      <style:paragraph-properties fo:hyphenation-ladder-count="no-limit" style:text-autospace="ideograph-alpha" style:punctuation-wrap="hanging" style:line-break="strict" style:tab-stop-distance="1.251cm" style:writing-mode="page"/>
      <style:text-properties style:use-window-font-color="true" style:font-name="Times New Roman" fo:font-size="12pt" fo:language="de" fo:country="AT" style:letter-kerning="true" style:font-name-asian="Droid Sans" style:font-size-asian="10.5pt" style:language-asian="zh" style:country-asian="CN" style:font-name-complex="DejaVu Sans" style:font-size-complex="12pt" style:language-complex="hi" style:country-complex="IN" fo:hyphenate="false" fo:hyphenation-remain-char-count="2" fo:hyphenation-push-char-count="2"/>
    </style:default-style>
    <style:default-style style:family="table">
      <style:table-properties table:border-model="collapsing"/>
    </style:default-style>
    <style:default-style style:family="table-row">
      <style:table-row-properties fo:keep-together="auto"/>
    </style:default-style>
    <style:style style:name="Standard" style:family="paragraph" style:class="text"/>
    <style:style style:name="Heading" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Text_20_body" style:class="text">
      <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.212cm" fo:keep-with-next="always"/>
      <style:text-properties style:font-name="Arial" fo:font-size="14pt" style:font-name-asian="Droid Sans" style:font-size-asian="14pt" style:font-name-complex="DejaVu Sans" style:font-size-complex="14pt"/>
    </style:style>
    <style:style style:name="Text_20_body" style:display-name="Text body" style:family="paragraph" style:parent-style-name="Standard" style:class="text">
      <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.212cm"/>
    </style:style>
    <style:style style:name="List" style:family="paragraph" style:parent-style-name="Text_20_body" style:class="list">
      <style:text-properties style:font-size-asian="12pt" style:font-name-complex="DejaVu Sans1"/>
    </style:style>
    <style:style style:name="Caption" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
      <style:paragraph-properties fo:margin-top="0.212cm" fo:margin-bottom="0.212cm" text:number-lines="false" text:line-number="0"/>
      <style:text-properties fo:font-size="12pt" fo:font-style="italic" style:font-size-asian="12pt" style:font-style-asian="italic" style:font-name-complex="DejaVu Sans1" style:font-size-complex="12pt" style:font-style-complex="italic"/>
    </style:style>
    <style:style style:name="Index" style:family="paragraph" style:parent-style-name="Standard" style:class="index">
      <style:paragraph-properties text:number-lines="false" text:line-number="0"/>
      <style:text-properties style:font-size-asian="12pt" style:font-name-complex="DejaVu Sans1"/>
    </style:style>
    <style:style style:name="Table_20_Contents" style:display-name="Table Contents" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
      <style:paragraph-properties text:number-lines="false" text:line-number="0"/>
    </style:style>
    <style:style style:name="Header" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
      <style:paragraph-properties text:number-lines="false" text:line-number="0">
        <style:tab-stops>
          <style:tab-stop style:position="8.5cm" style:type="center"/>
          <style:tab-stop style:position="17cm" style:type="right"/>
        </style:tab-stops>
      </style:paragraph-properties>
    </style:style>
     <style:style style:name="Footer" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
      <style:paragraph-properties text:number-lines="false" text:line-number="0">
        <style:tab-stops>
          <style:tab-stop style:position="8.5cm" style:type="center"/>
          <style:tab-stop style:position="17cm" style:type="right"/>
        </style:tab-stops>
      </style:paragraph-properties>
    </style:style>
    <text:outline-style style:name="Outline">
      <text:outline-level-style text:level="1" style:num-format="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.762cm" fo:text-indent="-0.762cm" fo:margin-left="0.762cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="2" style:num-format="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.016cm" fo:text-indent="-1.016cm" fo:margin-left="1.016cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="3" style:num-format="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-1.27cm" fo:margin-left="1.27cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="4" style:num-format="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.524cm" fo:text-indent="-1.524cm" fo:margin-left="1.524cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="5" style:num-format="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.778cm" fo:text-indent="-1.778cm" fo:margin-left="1.778cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="6" style:num-format="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.032cm" fo:text-indent="-2.032cm" fo:margin-left="2.032cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="7" style:num-format="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.286cm" fo:text-indent="-2.286cm" fo:margin-left="2.286cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="8" style:num-format="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-2.54cm" fo:margin-left="2.54cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="9" style:num-format="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.794cm" fo:text-indent="-2.794cm" fo:margin-left="2.794cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
      <text:outline-level-style text:level="10" style:num-format="">
        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
          <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.048cm" fo:text-indent="-3.048cm" fo:margin-left="3.048cm"/>
        </style:list-level-properties>
      </text:outline-level-style>
    </text:outline-style>
    <text:notes-configuration text:note-class="footnote" style:num-format="1" text:start-value="0" text:footnotes-position="page" text:start-numbering-at="document"/>
    <text:notes-configuration text:note-class="endnote" style:num-format="i" text:start-value="0"/>
    <text:linenumbering-configuration text:number-lines="false" text:offset="0.499cm" style:num-format="1" text:number-position="left" text:increment="5"/>
  </office:styles>
  <office:automatic-styles>
    <style:style style:name="Tabelle1" style:family="table">
      <style:table-properties style:width="17.006cm" fo:margin-left="0cm" table:align="left"/>
    </style:style>
    <style:style style:name="Tabelle1.A" style:family="table-column">
      <style:table-column-properties style:column-width="2.646cm"/>
    </style:style>
    <style:style style:name="Tabelle1.B" style:family="table-column">
      <style:table-column-properties style:column-width="6.145cm"/>
    </style:style>
    <style:style style:name="Tabelle1.C" style:family="table-column">
      <style:table-column-properties style:column-width="3.958cm"/>
    </style:style>
    <style:style style:name="Tabelle1.D" style:family="table-column">
      <style:table-column-properties style:column-width="4.256cm"/>
    </style:style>
    <style:style style:name="Tabelle1.A1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.30pt solid #000000" fo:border-right="none" fo:border-top="0.30pt solid #000000" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Tabelle1.B1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="0.30pt solid #000000" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Tabelle1.D1" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.30pt solid #000000" fo:border-top="0.30pt solid #000000" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Tabelle1.A2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.30pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Tabelle1.B2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border="none"/>
    </style:style>
    <style:style style:name="Tabelle1.D2" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.30pt solid #000000" fo:border-top="none" fo:border-bottom="none"/>
    </style:style>
    <style:style style:name="Tabelle1.A3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.30pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.30pt solid #000000"/>
    </style:style>
    <style:style style:name="Tabelle1.B3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.30pt solid #000000"/>
    </style:style>
    <style:style style:name="Tabelle1.D3" style:family="table-cell">
      <style:table-cell-properties fo:padding="0.097cm" fo:border-left="none" fo:border-right="0.30pt solid #000000" fo:border-top="none" fo:border-bottom="0.30pt solid #000000"/>
    </style:style>
    <style:style style:name="MP1" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="22pt" fo:font-weight="bold" style:font-size-asian="22pt" style:font-weight-asian="bold" style:font-size-complex="22pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:style style:name="MP2" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="normal" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="MP3" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="12pt" fo:font-weight="normal" style:font-size-asian="12pt" style:font-weight-asian="normal" style:font-size-complex="12pt" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="MP4" style:family="paragraph" style:parent-style-name="Standard">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="normal" style:font-size-asian="10pt" style:font-weight-asian="normal" style:font-size-complex="10pt" style:font-weight-complex="normal"/>
    </style:style>
    <style:style style:name="MP5" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
    </style:style>
    <style:style style:name="MP6" style:family="paragraph" style:parent-style-name="Table_20_Contents">
      <style:paragraph-properties fo:text-align="start" style:justify-single-word="false"/>
      <style:text-properties style:font-name="Arial" fo:font-size="10pt" fo:font-weight="bold" style:font-size-asian="10pt" style:font-weight-asian="bold" style:font-size-complex="10pt" style:font-weight-complex="bold"/>
    </style:style>
    <style:page-layout style:name="Mpm1">
      <style:page-layout-properties fo:page-width="21.001cm" fo:page-height="29.7cm" style:num-format="1" style:print-orientation="portrait" fo:margin="2cm" fo:margin-top="2cm" fo:margin-bottom="2cm" fo:margin-left="2cm" fo:margin-right="2cm" style:writing-mode="lr-tb" style:footnote-max-height="0cm">
        <style:footnote-sep style:width="0.018cm" style:distance-before-sep="0.101cm" style:distance-after-sep="0.101cm" style:line-style="solid" style:adjustment="left" style:rel-width="25%" style:color="#000000"/>
      </style:page-layout-properties>
      <style:header-style>
        <style:header-footer-properties fo:min-height="0.101cm" fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-bottom="0cm" style:dynamic-spacing="false"/>
      </style:header-style>
      <style:footer-style/>
    </style:page-layout>
  </office:automatic-styles>
  <office:master-styles>
    <style:master-page style:name="Standard" style:page-layout-name="Mpm1">
      <style:header>
        <text:p text:style-name="MP1"/>
        <text:p text:style-name="MP1"/>
        <text:p text:style-name="MP1"/>
        <text:p text:style-name="MP1"/>
        <text:p text:style-name="MP1">Erfolgsnachweis</text:p>
        <text:p text:style-name="MP2">
			<xsl:choose>
				<xsl:when test="studiengang_typ='b'" >
					Fachhochschul-Bachelorstudiengang
				</xsl:when>
				<xsl:when test="studiengang_typ='m'" >
					Fachhochschul-Masterstudiengang
				</xsl:when>
				<xsl:otherwise>
					Fachhochschul-Studiengang
				</xsl:otherwise>
			</xsl:choose>

			<xsl:value-of select="studiengang" />

			</text:p>
        <text:p text:style-name="MP2"/>
        <text:p text:style-name="MP3">Studienjahr <xsl:value-of select="studienjahr" /></text:p>
        <text:p text:style-name="MP4"/>
        <table:table table:name="Tabelle1" table:style-name="Tabelle1">
          <table:table-column table:style-name="Tabelle1.A"/>
          <table:table-column table:style-name="Tabelle1.B"/>
          <table:table-column table:style-name="Tabelle1.C"/>
          <table:table-column table:style-name="Tabelle1.D"/>
          <table:table-row>
            <table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
              <text:p text:style-name="MP5">Zuname:</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
              <text:p text:style-name="MP6"><xsl:value-of select="nachname" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
              <text:p text:style-name="MP5">Personenkennzeichen:</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Tabelle1.D1" office:value-type="string">
              <text:p text:style-name="MP6"><xsl:value-of select="personenkennzeichen" /></text:p>
            </table:table-cell>
          </table:table-row>
          <table:table-row>
            <table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
              <text:p text:style-name="MP5">Vorname:</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Tabelle1.B2" office:value-type="string">
              <text:p text:style-name="MP6"><xsl:value-of select="vorname" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Tabelle1.B2" office:value-type="string">
              <text:p text:style-name="MP5">Studienjahr:</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Tabelle1.D2" office:value-type="string">
              <text:p text:style-name="MP6"><xsl:value-of select="student_studienjahr" /></text:p>
            </table:table-cell>
          </table:table-row>
          <table:table-row>
            <table:table-cell table:style-name="Tabelle1.A3" office:value-type="string">
              <text:p text:style-name="MP5">Geburtsdatum:</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Tabelle1.B3" office:value-type="string">
              <text:p text:style-name="MP6"><xsl:value-of select="gebdatum" /></text:p>
            </table:table-cell>
            <table:table-cell table:style-name="Tabelle1.B3" office:value-type="string">
              <text:p text:style-name="MP5"/>
            </table:table-cell>
            <table:table-cell table:style-name="Tabelle1.D3" office:value-type="string">
              <text:p text:style-name="MP5"/>
            </table:table-cell>
          </table:table-row>
        </table:table>
        <text:p text:style-name="MP4"/>
      </style:header>
      <style:footer>
        <text:p text:style-name="Footer"></text:p>
        <text:p text:style-name="Footer"></text:p>
      </style:footer>
    </style:master-page>
  </office:master-styles>
</office:document-styles>
</xsl:template>
</xsl:stylesheet>
