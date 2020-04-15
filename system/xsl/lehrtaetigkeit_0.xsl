<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
        xmlns:fo="http://www.w3.org/1999/XSL/Format"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        version="1.0"
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
        xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
>

    <xsl:output method="xml" version="1.0" indent="yes"/>
    <xsl:template match="lehrtaetigkeit">

<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
                         xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
                         xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
                         xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
                         xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
                         xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
                         xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/"
                         xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
                         xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
                         xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
                         xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
                         xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0"
                         xmlns:math="http://www.w3.org/1998/Math/MathML"
                         xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
                         xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"
                         xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer"
                         xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events"
                         xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                         xmlns:rpt="http://openoffice.org/2005/report"
                         xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2"
                         xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#"
                         xmlns:officeooo="http://openoffice.org/2009/office"
                         xmlns:tableooo="http://openoffice.org/2009/table"
                         xmlns:drawooo="http://openoffice.org/2010/draw"
                         xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0"
                         xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0"
                         xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0"
                         xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0"
                         xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
    <office:scripts/>
    <office:font-face-decls>
        <style:font-face style:name="Mangal1" svg:font-family="Mangal"/>
        <style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;"
                         style:font-family-generic="roman" style:font-pitch="variable"/>
        <style:font-face style:name="Arial" svg:font-family="Arial" style:font-adornments="Standard"
                         style:font-family-generic="swiss" style:font-pitch="variable"/>
        <style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;"
                         style:font-family-generic="swiss" style:font-pitch="variable"/>
        <style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="system"
                         style:font-pitch="variable"/>
        <style:font-face style:name="Microsoft YaHei" svg:font-family="&apos;Microsoft YaHei&apos;"
                         style:font-family-generic="system" style:font-pitch="variable"/>
        <style:font-face style:name="SimSun" svg:font-family="SimSun" style:font-family-generic="system"
                         style:font-pitch="variable"/>
    </office:font-face-decls>
    <office:automatic-styles>
        <style:style style:name="Tabelle1" style:family="table">
            <style:table-properties style:width="17cm" table:align="margins"/>
        </style:style>
        <style:style style:name="Tabelle1.A" style:family="table-column">
            <style:table-column-properties style:column-width="8.5cm" style:rel-column-width="32767*"/>
        </style:style>
        <style:style style:name="Tabelle1.B" style:family="table-column">
            <style:table-column-properties style:column-width="8.5cm" style:rel-column-width="32768*"/>
        </style:style>
        <style:style style:name="Tabelle1.A1" style:family="table-cell">
            <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #cccccc"
                                         fo:border-right="none" fo:border-top="0.05pt solid #cccccc"
                                         fo:border-bottom="0.05pt solid #cccccc"/>
        </style:style>
        <style:style style:name="Tabelle1.B1" style:family="table-cell">
            <style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #cccccc"/>
        </style:style>
        <style:style style:name="Tabelle1.A2" style:family="table-cell">
            <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #cccccc"
                                         fo:border-right="none" fo:border-top="none"
                                         fo:border-bottom="0.05pt solid #cccccc"/>
        </style:style>
        <style:style style:name="Tabelle1.B2" style:family="table-cell">
            <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #cccccc"
                                         fo:border-right="0.05pt solid #cccccc" fo:border-top="none"
                                         fo:border-bottom="0.05pt solid #cccccc"/>
        </style:style>
        <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="115%"/>
            <style:text-properties style:font-name="Arial" fo:font-size="11pt" officeooo:rsid="0019e1ef"
                                   officeooo:paragraph-rsid="0019e1ef" style:font-size-asian="10.5pt"
                                   loext:shadow="none"/>
        </style:style>
        <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="115%" fo:text-align="end" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Arial" fo:font-size="11pt" officeooo:rsid="0019e1ef"
                                   officeooo:paragraph-rsid="0019e1ef" style:font-size-asian="10.5pt"
                                   loext:shadow="none"/>
        </style:style>
        <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="115%" fo:text-align="justify"
                                        style:justify-single-word="false"/>
            <style:text-properties style:font-name="Arial" fo:font-size="11pt" officeooo:rsid="0019e1ef"
                                   officeooo:paragraph-rsid="0019e1ef" style:font-size-asian="10.5pt"
                                   loext:shadow="none"/>
        </style:style>
        <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="115%"/>
            <style:text-properties style:font-name="Arial" fo:font-size="13pt" officeooo:rsid="0019e1ef"
                                   officeooo:paragraph-rsid="0019e1ef" style:font-size-asian="13pt"
                                   style:font-size-complex="13pt" loext:shadow="none"/>
        </style:style>
        <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Table_20_Contents">
            <style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Arial" fo:font-size="11pt" officeooo:rsid="0019e1ef"
                                   officeooo:paragraph-rsid="0019e1ef" style:font-size-asian="10.5pt"/>
        </style:style>
        <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Table_20_Contents">
            <style:paragraph-properties fo:text-align="center" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Arial" fo:font-size="11pt" fo:font-weight="bold"
                                   officeooo:rsid="0019e1ef" officeooo:paragraph-rsid="0019e1ef"
                                   style:font-size-asian="10.5pt" style:font-weight-asian="bold"
                                   style:font-weight-complex="bold"/>
        </style:style>
        <style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="115%"/>
            <style:text-properties style:font-name="Arial" fo:font-size="11pt" officeooo:rsid="0019e1ef"
                                   officeooo:paragraph-rsid="0019e1ef" style:font-size-asian="10.5pt"
                                   loext:shadow="none"/>
        </style:style>
        <style:style style:name="fr1" style:family="graphic" style:parent-style-name="Graphics">
            <style:graphic-properties style:vertical-pos="from-top" style:vertical-rel="paragraph"
                                      style:horizontal-pos="from-left" style:horizontal-rel="paragraph"
                                      style:mirror="none" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%"
                                      draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%"
                                      draw:color-inversion="false" draw:image-opacity="100%"
                                      draw:color-mode="standard"/>
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
            <text:p text:style-name="P4">Bestätigung über Lehrtätigkeit</text:p>
            <text:p text:style-name="P1"/>
            <text:p text:style-name="P1"/>
            <text:p text:style-name="P3">Hiermit wird bestätigt, dass <xsl:value-of select="anrede" /> <xsl:text> </xsl:text><xsl:value-of select="full_name" />,
                geboren am <xsl:value-of select="birthday" />,
                <xsl:choose>
                    <xsl:when test="end_date != ''">
                        in der Zeit von <xsl:value-of select="begin_date" /> bis <xsl:value-of select="end_date" /> als Hochschullehrer tätig war.
                    </xsl:when>
                    <xsl:otherwise>
                        seit <xsl:value-of select="begin_date" />
                        für die FH Technikum Wien als HochschullektorIn tätig ist.
                        Die Tätigkeit umfasst die Konzeption, Organisation und Abhaltung von Lehrveranstaltungen.
                    </xsl:otherwise>
                </xsl:choose>
            </text:p>
            <text:p text:style-name="P3"/>
            <text:p text:style-name="P1">Nachstehend eine detaillierte Aufstellung der Lehraufträge:</text:p>
            <text:p text:style-name="P1"/>
            <table:table table:name="Tabelle1" table:style-name="Tabelle1">
                <table:table-column table:style-name="Tabelle1.A" table:number-columns-repeated="2"/>
                <table:table-row>
                    <table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
                        <text:p text:style-name="P6">Semester</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
                        <text:p text:style-name="P6">Semesterstunden pro Semester</text:p>
                    </table:table-cell>
                </table:table-row>
                <!--apply dynamic table rows-->
                <xsl:for-each select="total_ss_per_semester">
                    <xsl:if test="total_semesterstunden > 0">
                        <table:table-row>
                            <table:table-cell table:style-name="Tabelle1.A2" office:value-type="string">
                                <text:p text:style-name="P5"><xsl:value-of select="studiensemester_kurzbz" /></text:p>
                            </table:table-cell>
                            <table:table-cell table:style-name="Tabelle1.B2" office:value-type="string">
                                <text:p text:style-name="P5"><xsl:value-of select="total_semesterstunden" /></text:p>
                            </table:table-cell>
                        </table:table-row>
                    </xsl:if>
                </xsl:for-each>
            </table:table>
            <text:p text:style-name="P1"/>
            <xsl:if test="total_ss_actual_semester != ''">
                <text:p text:style-name="P1"/>
                <text:p text:style-name="P1">Für das aktuelle Semester <xsl:value-of select="total_ss_actual_semester/studiensemester_kurzbz" /> sind derzeit <xsl:value-of select="total_ss_actual_semester/total_semesterstunden" /> Semesterstunden beauftragt.
                </text:p>
            </xsl:if>
            <text:p text:style-name="P1"/>
            <text:p text:style-name="P1"/>
            <text:p text:style-name="P1"/>
            <text:p text:style-name="P1"/>
            <text:p text:style-name="P1"/>
            <text:p text:style-name="P1">
                <draw:frame draw:style-name="fr1" draw:name="Bild1" text:anchor-type="paragraph" svg:x="0.183cm"
                            svg:y="0.025cm" svg:width="5.226cm" svg:height="2.182cm" draw:z-index="0">
                    <draw:image xlink:href="Pictures/10000000000001AF000000B49D9770EDA3C5B4C0.jpg" xlink:type="simple"
                                xlink:show="embed" xlink:actuate="onLoad"/>
                </draw:frame>
            </text:p>
            <text:p text:style-name="P1"/>
            <text:p text:style-name="P1"/>
            <text:p text:style-name="P2">Wien, <xsl:value-of select="actual_date" /></text:p>
        </office:text>
    </office:body>
</office:document-content>

    </xsl:template>

</xsl:stylesheet>