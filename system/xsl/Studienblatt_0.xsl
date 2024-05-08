<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="studienblaetter">

<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
    <office:scripts/>
    <office:font-face-decls>
        <style:font-face style:name="Mangal1" svg:font-family="Mangal"/>
        <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="roman" style:font-pitch="variable"/>
        <style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="roman" style:font-pitch="variable"/>
        <style:font-face style:name="Times New Roman" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
        <style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
        <style:font-face style:name="Arial1" svg:font-family="Arial" style:font-family-generic="system" style:font-pitch="variable"/>
        <style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="system" style:font-pitch="variable"/>
        <style:font-face style:name="Microsoft YaHei" svg:font-family="&apos;Microsoft YaHei&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
        <style:font-face style:name="Tahoma1" svg:font-family="Tahoma" style:font-family-generic="system" style:font-pitch="variable"/>
        <style:font-face style:name="Times New Roman1" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
    </office:font-face-decls>
    <office:automatic-styles>
        <style:style style:name="Tabelle1" style:family="table">
            <style:table-properties style:width="16.443cm" fo:margin-left="-0.199cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:writing-mode="lr-tb"/>
        </style:style>
        <style:style style:name="Tabelle1.A" style:family="table-column">
            <style:table-column-properties style:column-width="4.69cm"/>
        </style:style>
        <style:style style:name="Tabelle1.B" style:family="table-column">
            <style:table-column-properties style:column-width="7.5cm"/>
        </style:style>
        <style:style style:name="Tabelle1.C" style:family="table-column">
            <style:table-column-properties style:column-width="4.253cm"/>
        </style:style>
        <style:style style:name="Tabelle1.1" style:family="table-row">
            <style:table-row-properties style:min-row-height="1cm" fo:keep-together="auto"/>
        </style:style>
        <style:style style:name="Tabelle1.A1" style:family="table-cell">
            <style:table-cell-properties fo:background-color="#ffffff" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #00000a">
                <style:background-image/>
            </style:table-cell-properties>
        </style:style>
        <style:style style:name="Tabelle1.2" style:family="table-row">
            <style:table-row-properties style:min-row-height="0.501cm" fo:keep-together="auto"/>
        </style:style>
        <style:style style:name="Tabelle2" style:family="table">
            <style:table-properties style:width="16.379cm" fo:margin-left="-0.199cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:writing-mode="lr-tb"/>
        </style:style>
        <style:style style:name="Tabelle2.A" style:family="table-column">
            <style:table-column-properties style:column-width="5.84cm"/>
        </style:style>
        <style:style style:name="Tabelle2.B" style:family="table-column">
            <style:table-column-properties style:column-width="6.003cm"/>
        </style:style>
        <style:style style:name="Tabelle2.C" style:family="table-column">
            <style:table-column-properties style:column-width="4.537cm"/>
        </style:style>
        <style:style style:name="Tabelle2.1" style:family="table-row">
            <style:table-row-properties fo:keep-together="auto"/>
        </style:style>
        <style:style style:name="Tabelle2.A1" style:family="table-cell">
            <style:table-cell-properties fo:background-color="#ffffff" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #00000a">
                <style:background-image/>
            </style:table-cell-properties>
        </style:style>
        <style:style style:name="Tabelle3" style:family="table">
            <style:table-properties style:width="16.443cm" fo:margin-left="-0.199cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:writing-mode="lr-tb"/>
        </style:style>
        <style:style style:name="Tabelle3.A" style:family="table-column">
            <style:table-column-properties style:column-width="7.689cm"/>
        </style:style>
        <style:style style:name="Tabelle3.B" style:family="table-column">
            <style:table-column-properties style:column-width="8.754cm"/>
        </style:style>
        <style:style style:name="Tabelle3.1" style:family="table-row">
            <style:table-row-properties style:min-row-height="0.801cm" fo:keep-together="auto"/>
        </style:style>
        <style:style style:name="Tabelle3.A1" style:family="table-cell">
            <style:table-cell-properties style:vertical-align="middle" fo:background-color="#ffffff" fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #00000a">
                <style:background-image/>
            </style:table-cell-properties>
        </style:style>
        <style:style style:name="Tabelle4" style:family="table">
            <style:table-properties style:width="16.443cm" fo:margin-left="-0.199cm" fo:margin-top="0cm" fo:margin-bottom="0cm" table:align="left" style:writing-mode="lr-tb"/>
        </style:style>
        <style:style style:name="Tabelle4.A" style:family="table-column">
            <style:table-column-properties style:column-width="3.69cm"/>
        </style:style>
        <style:style style:name="Tabelle4.B" style:family="table-column">
            <style:table-column-properties style:column-width="12.753cm"/>
        </style:style>
        <style:style style:name="Tabelle4.1" style:family="table-row">
            <style:table-row-properties fo:keep-together="auto"/>
        </style:style>
        <style:style style:name="Tabelle4.A1" style:family="table-cell">
            <style:table-cell-properties fo:padding-left="0.199cm" fo:padding-right="0.191cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="0.5pt solid #00000a"/>
        </style:style>
        <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Footer">
            <style:paragraph-properties>
                <style:tab-stops>
                    <style:tab-stop style:position="8.001cm" style:type="center"/>
                    <style:tab-stop style:position="15.998cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
        </style:style>
        <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties>
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="11pt" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
        </style:style>
        <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties>
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
        </style:style>
        <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties>
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
        </style:style>
        <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:text-align="end" style:justify-single-word="false">
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
        </style:style>
        <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false">
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
        </style:style>
        <style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false">
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="11pt" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
        </style:style>
        <style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false">
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="11pt" officeooo:rsid="00026b08" officeooo:paragraph-rsid="00026b08" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
        </style:style>
        <style:style style:name="P9" style:family="paragraph" style:parent-style-name="Header">
            <style:paragraph-properties fo:margin-top="0.494cm" fo:margin-bottom="0.423cm" style:contextual-spacing="false">
                <style:tab-stops>
                    <style:tab-stop style:position="7.502cm"/>
                    <style:tab-stop style:position="9.502cm"/>
                    <style:tab-stop style:position="14.753cm"/>
                    <style:tab-stop style:position="15.503cm"/>
                    <style:tab-stop style:position="16.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="11pt" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
        </style:style>
        <style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false">
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
        </style:style>
        <style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false">
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="9pt" officeooo:paragraph-rsid="00026b08" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
        </style:style>
        <style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false">
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="11pt" officeooo:rsid="00026b08" officeooo:paragraph-rsid="00026b08" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
        </style:style>
        <style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties>
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
        </style:style>
        <style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties>
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="11pt" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
        </style:style>
        <style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties>
                <style:tab-stops>
                    <style:tab-stop style:position="6.752cm"/>
                    <style:tab-stop style:position="13.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="11pt" officeooo:rsid="00026b08" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
        </style:style>
        <style:style style:name="P16" style:family="paragraph" style:parent-style-name="Header">
            <style:text-properties fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
        </style:style>
        <style:style style:name="P17" style:family="paragraph" style:parent-style-name="Header" style:master-page-name="Standard">
            <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.423cm" style:contextual-spacing="false" style:page-number="auto">
                <style:tab-stops>
                    <style:tab-stop style:position="7.502cm"/>
                    <style:tab-stop style:position="9.502cm"/>
                    <style:tab-stop style:position="14.753cm"/>
                    <style:tab-stop style:position="15.503cm"/>
                    <style:tab-stop style:position="16.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="14pt" style:font-size-asian="14pt" style:font-size-complex="14pt"/>
        </style:style>
        <style:style style:name="P18" style:family="paragraph" style:parent-style-name="Header">
            <style:paragraph-properties fo:margin-top="0.423cm" fo:margin-bottom="0.423cm" style:contextual-spacing="false">
                <style:tab-stops>
                    <style:tab-stop style:position="7.502cm"/>
                    <style:tab-stop style:position="9.502cm"/>
                    <style:tab-stop style:position="14.753cm"/>
                    <style:tab-stop style:position="15.503cm"/>
                    <style:tab-stop style:position="16.002cm" style:type="right"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties fo:font-size="14pt" style:font-size-asian="14pt" style:font-size-complex="14pt"/>
        </style:style>
        <style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:text-align="end" style:justify-single-word="false" fo:margin-top="0.106cm" fo:margin-bottom="0cm" style:contextual-spacing="false"/>
            <style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
        </style:style>
        <style:style style:name="T1" style:family="text">
            <style:text-properties fo:font-size="9pt" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
        </style:style>
        <style:style style:name="T2" style:family="text">
            <style:text-properties fo:font-size="9pt" officeooo:rsid="00026b08" style:font-size-asian="9pt" style:font-size-complex="9pt"/>
        </style:style>
        <style:style style:name="T3" style:family="text">
            <style:text-properties fo:font-size="11pt" style:font-size-asian="11pt" style:font-size-complex="11pt"/>
        </style:style>
        <style:style style:name="T4" style:family="text">
            <style:text-properties officeooo:rsid="00026b08"/>
        </style:style>
        <style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
            <style:graphic-properties fo:margin-left="0.318cm" fo:margin-right="0.318cm" fo:margin-top="0cm" fo:margin-bottom="0cm" style:run-through="background" style:wrap="run-through" style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="transparent" style:background-transparency="100%" fo:padding="0cm" fo:border="none" style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard">
                <style:background-image/>
            </style:graphic-properties>
        </style:style>
    </office:automatic-styles>
  <office:body>
<xsl:apply-templates select="studienblatt"/>
  </office:body>
</office:document-content>
</xsl:template>

<xsl:template match="studienblatt">
    <office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">
            <text:sequence-decls>
                <text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
                <text:sequence-decl text:display-outline-level="0" text:name="Table"/>
                <text:sequence-decl text:display-outline-level="0" text:name="Text"/>
                <text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
            </text:sequence-decls>
            <text:p text:style-name="P17">Studienblatt</text:p>
            <text:p text:style-name="P18">Bestätigung des Studierendenstatus</text:p>
            <text:p text:style-name="P9"/>
            <table:table table:name="Tabelle1" table:style-name="Tabelle1">
                <table:table-column table:style-name="Tabelle1.A"/>
                <table:table-column table:style-name="Tabelle1.B"/>
                <table:table-column table:style-name="Tabelle1.C"/>
                <table:table-row table:style-name="Tabelle1.1">
                    <table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="2" office:value-type="string">
                        <text:p text:style-name="P10">Familienname, Vorname</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="nachname"/>, <xsl:value-of select="vorname"/></text:p>
                    </table:table-cell>
                    <table:covered-table-cell/>
                    <table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
                        <text:p text:style-name="P10">Personenkennzeichen</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="matrikelnr"/></text:p>
                    </table:table-cell>
                </table:table-row>
                <table:table-row table:style-name="Tabelle1.2">
                    <table:table-cell table:style-name="Tabelle1.A1" table:number-columns-spanned="3" office:value-type="string">
                        <text:p text:style-name="P10">Adresse</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="strasse"/>, <xsl:value-of select="plz"/></text:p>
                    </table:table-cell>
                    <table:covered-table-cell/>
                    <table:covered-table-cell/>
                </table:table-row>
                <table:table-row table:style-name="Tabelle1.2">
                    <table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
                        <text:p text:style-name="P10">Geburtsdatum</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="gebdatum"/></text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
                        <text:p text:style-name="P10">Geburtsort</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="gebort"/></text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
                        <text:p text:style-name="P11">Geschlecht</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="geschlecht"/></text:p>
                    </table:table-cell>
                </table:table-row>
            </table:table>
            <text:p text:style-name="P7"/>
            <text:p text:style-name="P7"/>
            <table:table table:name="Tabelle2" table:style-name="Tabelle2">
                <table:table-column table:style-name="Tabelle2.A"/>
                <table:table-column table:style-name="Tabelle2.B"/>
                <table:table-column table:style-name="Tabelle2.C"/>
                <table:table-row table:style-name="Tabelle2.1">
                    <table:table-cell table:style-name="Tabelle2.A1" table:number-columns-spanned="3" office:value-type="string">
                        <text:p text:style-name="P10">Institution</text:p>
                    </table:table-cell>
                    <table:covered-table-cell/>
                    <table:covered-table-cell/>
                </table:table-row>
                <table:table-row table:style-name="Tabelle2.1">
                    <table:table-cell table:style-name="Tabelle2.A1" table:number-columns-spanned="2" office:value-type="string">
                        <text:p text:style-name="P10">Studiengang</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="studiengang"/></text:p>
                    </table:table-cell>
                    <table:covered-table-cell/>
                    <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
                        <text:p text:style-name="P10">Kennzahl</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="studiengang_kz"/></text:p>
                    </table:table-cell>
                </table:table-row>
                <table:table-row table:style-name="Tabelle2.1">
                    <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
                        <text:p text:style-name="P10">Studiengangsart</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="studiengang_typ"/></text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
                        <text:p text:style-name="P10">Organisationsform</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="orgform_bezeichnung"/></text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
                        <text:p text:style-name="P10">Unterrichtssprache</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="studiengangSprache"/></text:p>
                    </table:table-cell>
                </table:table-row>
                <table:table-row table:style-name="Tabelle2.1">
                    <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
                        <text:p text:style-name="P10">ECTS</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="ects_gesamt"/></text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
                        <text:p text:style-name="P10">ECTS je Semester</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="ects_pro_semester"/></text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
                        <text:p text:style-name="P10">Regelstudiendauer</text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="regelstudiendauer"/></text:p>
                    </table:table-cell>
                </table:table-row>
                <table:table-row table:style-name="Tabelle2.1">
                    <table:table-cell table:style-name="Tabelle2.A1" table:number-columns-spanned="3" office:value-type="string">
                        <text:p text:style-name="P6">
                            <text:span text:style-name="T1">Akademischer</text:span>
                            <text:span text:style-name="T3"> </text:span>
                            <text:span text:style-name="T1">Grad</text:span>
                        </text:p>
                        <text:p text:style-name="P8"><xsl:value-of select="akadgrad"/></text:p>
                    </table:table-cell>
                    <table:covered-table-cell/>
                    <table:covered-table-cell/>
                </table:table-row>
            </table:table>
            <text:p text:style-name="P2"/>
            <text:p text:style-name="P2"/>
            <table:table table:name="Tabelle3" table:style-name="Tabelle3">
                <table:table-column table:style-name="Tabelle3.A"/>
                <table:table-column table:style-name="Tabelle3.B"/>
                <table:table-row table:style-name="Tabelle3.1">
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <text:p text:style-name="P4">Studienantritt</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <text:p text:style-name="P2">
                            <text:span text:style-name="T4"><xsl:value-of select="studiensemester_beginndatum"/></text:span>
                        </text:p>
                    </table:table-cell>
                </table:table-row>
                <table:table-row table:style-name="Tabelle3.1">
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <text:p text:style-name="P4">Erstes Studiensemester</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <text:p text:style-name="P2">
                            <text:span text:style-name="T4"><xsl:value-of select="studiensemester_beginn"/></text:span></text:p>
                    </table:table-cell>
                </table:table-row>
                <table:table-row table:style-name="Tabelle3.1">
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <xsl:choose>
                            <xsl:when test = "abbrecher='true'">
                                <text:p text:style-name="P4">Abgemeldet im Studiensemester</text:p>
                            </xsl:when>

                            <xsl:otherwise>
                                <text:p text:style-name="P4">Aktuelles Studiensemester</text:p>
                            </xsl:otherwise>

                        </xsl:choose>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <text:p text:style-name="P2">
                            <text:span text:style-name="T4"><xsl:value-of select="studiensemester_aktuell"/></text:span></text:p>
                    </table:table-cell>
                </table:table-row>
                <table:table-row table:style-name="Tabelle3.1">
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <xsl:choose>
                            <xsl:when test = "abbrecher='true'">
                                <text:p text:style-name="P4">Abgemeldet im Ausbildungssemester</text:p>
                            </xsl:when>

                            <xsl:otherwise>
                                <text:p text:style-name="P4">Aktuelles Ausbildungssemester</text:p>
                            </xsl:otherwise>

                        </xsl:choose>

                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <text:p text:style-name="P2">
                            <text:span text:style-name="T4"><xsl:value-of select="ausbildungssemester_aktuell"/></text:span></text:p>
                    </table:table-cell>
                </table:table-row>
                <table:table-row table:style-name="Tabelle3.1">
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <text:p text:style-name="P4">Aktueller Studierendenstatus</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <text:p text:style-name="P2">
                            <text:span text:style-name="T4"><xsl:value-of select="studierendenstatus_aktuell"/></text:span></text:p>
                    </table:table-cell>
                </table:table-row>

            <xsl:if test="abbrecher='false'">

                <table:table-row table:style-name="Tabelle3.1">
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <text:p text:style-name="P4">Voraussichtlich letztes Studiensemester</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <text:p text:style-name="P2">
                            <text:span text:style-name="T4"><xsl:value-of select="voraussichtlichLetztesStudiensemester"/></text:span></text:p>
                    </table:table-cell>
                </table:table-row>
            </xsl:if>

                <table:table-row table:style-name="Tabelle3.1">
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <xsl:choose>
                            <xsl:when test="abbrecher='true'">
                                <text:p text:style-name="P4">Abgemeldet am</text:p>
                            </xsl:when>

                            <xsl:otherwise>
                                <text:p text:style-name="P4">Voraussichtliches Abschlussdatum</text:p>
                            </xsl:otherwise>

                        </xsl:choose>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                        <text:p text:style-name="P15"><xsl:value-of select="voraussichtlichLetztesStudiensemester_datum"/></text:p>
                    </table:table-cell>
                </table:table-row>
            </table:table>
            <text:p text:style-name="P2"/>
            <text:p text:style-name="P2"/>
            <text:p text:style-name="P2"/>
            <table:table table:name="Tabelle4" table:style-name="Tabelle4">
                <table:table-column table:style-name="Tabelle4.A"/>
                <table:table-column table:style-name="Tabelle4.B"/>
                <table:table-row table:style-name="Tabelle4.1">
                    <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
                        <text:p text:style-name="P4"/>
                        <text:p text:style-name="P4"/>
                        <text:p text:style-name="P3">
                            <text:span text:style-name="T1">Datum: </text:span>
                            <text:span text:style-name="T2"><xsl:value-of select="datum_aktuell"/></text:span>
                        </text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle4.A1" office:value-type="string">
                        <text:p text:style-name="P4"/>
                        <text:p text:style-name="P5">
                            <text:span text:style-name="T1"><xsl:value-of select="stgl"/></text:span>
                        </text:p>
                        <text:p text:style-name="P5">
                            <text:span text:style-name="T1">Studiengangsleitung <xsl:value-of select="studiengang"/></text:span>
                        </text:p>
                    </table:table-cell>
                </table:table-row>
            </table:table>
            <text:p text:style-name="P19"/>

            
        </office:text>
</xsl:template>
</xsl:stylesheet>