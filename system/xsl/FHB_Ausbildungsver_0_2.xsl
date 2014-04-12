<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>

<xsl:output method="xml" version="1.0" indent="yes"/>
<xsl:template match="ausbildungsvertraege">

<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
    <office:scripts/>
    <office:font-face-decls>
        <style:font-face style:name="Wingdings" svg:font-family="Wingdings" style:font-pitch="variable" style:font-charset="x-symbol"/>
        <style:font-face style:name="Symbol" svg:font-family="Symbol" style:font-family-generic="roman" style:font-pitch="variable" style:font-charset="x-symbol"/>
        <style:font-face style:name="Lohit Hindi1" svg:font-family="&apos;Lohit Hindi&apos;"/>
        <style:font-face style:name="Tahoma1" svg:font-family="Tahoma"/>
        <style:font-face style:name="Courier New" svg:font-family="&apos;Courier New&apos;" style:font-family-generic="modern"/>
        <style:font-face style:name="Lucida Grande" svg:font-family="&apos;Lucida Grande&apos;, &apos;Times New Roman&apos;" style:font-family-generic="roman"/>
        <style:font-face style:name="Optima" svg:font-family="Optima, &apos;Times New Roman&apos;" style:font-family-generic="roman"/>
        <style:font-face style:name="ヒラギノ角ゴ Pro W3" svg:font-family="&apos;ヒラギノ角ゴ Pro W3&apos;" style:font-family-generic="roman"/>
        <style:font-face style:name="Courier New1" svg:font-family="&apos;Courier New&apos;" style:font-family-generic="modern" style:font-pitch="fixed"/>
        <style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
        <style:font-face style:name="Times New Roman" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
        <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
        <style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="swiss" style:font-pitch="variable"/>
        <style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-family-generic="swiss" style:font-pitch="variable"/>
        <style:font-face style:name="Droid Sans" svg:font-family="&apos;Droid Sans&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
        <style:font-face style:name="Lohit Hindi" svg:font-family="&apos;Lohit Hindi&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
    </office:font-face-decls>
    <office:automatic-styles>
        <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Footer">
            <style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="8pt" officeooo:rsid="001f4de5" officeooo:paragraph-rsid="001f4de5" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
        </style:style>
        <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Footer">
            <style:paragraph-properties fo:text-align="end" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
        </style:style>
        <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Header">
            <style:text-properties fo:language="de" fo:country="AT" style:language-asian="none" style:country-asian="none"/>
        </style:style>
        <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Heading_20_1" style:list-style-name="" style:master-page-name="First_20_Page">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="center" style:justify-single-word="false" style:page-number="auto"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="12pt" style:font-size-asian="12pt" style:font-size-complex="12pt" style:language-complex="zxx" style:country-complex="none"/>
        </style:style>
        <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="8pt" style:font-size-asian="8pt" style:font-name-complex="Arial" style:font-size-complex="8pt"/>
        </style:style>
        <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="center" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="8pt" officeooo:rsid="001cea51" officeooo:paragraph-rsid="001cea51" style:font-size-asian="8pt" style:font-size-complex="8pt"/>
        </style:style>
        <style:style style:name="P7" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="center" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" officeooo:paragraph-rsid="001cea51"/>
        </style:style>
        <style:style style:name="P8" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001cea51" officeooo:paragraph-rsid="001cea51" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P9" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001cea51" officeooo:paragraph-rsid="001cea51" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P10" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001cea51" officeooo:paragraph-rsid="001dc465" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P11" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001de737" officeooo:paragraph-rsid="001de737" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P12" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:paragraph-rsid="001f5be6" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P13" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001f5be6" officeooo:paragraph-rsid="001f5be6" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P14" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001f5be6" officeooo:paragraph-rsid="00229aac" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P15" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001fa3b7" officeooo:paragraph-rsid="001fa3b7" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P16" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="002088ec" officeooo:paragraph-rsid="002088ec" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P17" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" officeooo:rsid="001cea51" officeooo:paragraph-rsid="001cea51" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P18" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" officeooo:rsid="001de737" officeooo:paragraph-rsid="001de737" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P19" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" officeooo:rsid="001f5be6" officeooo:paragraph-rsid="001f5be6" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P20" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" fo:font-style="italic" officeooo:rsid="001cea51" officeooo:paragraph-rsid="001cea51" style:font-size-asian="10pt" style:font-style-asian="italic" style:font-name-complex="Arial" style:font-style-complex="italic"/>
        </style:style>
        <style:style style:name="P21" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="7pt" officeooo:rsid="001de737" officeooo:paragraph-rsid="001de737" style:font-size-asian="7pt" style:font-name-complex="Arial" style:font-size-complex="7pt"/>
        </style:style>
        <style:style style:name="P22" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001de737" officeooo:paragraph-rsid="001de737" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P23" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="002088ec" officeooo:paragraph-rsid="002088ec" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P24" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false" fo:break-before="page"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" officeooo:rsid="001fa3b7" officeooo:paragraph-rsid="001fa3b7" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P25" style:family="paragraph" style:parent-style-name="Standard" style:master-page-name="">
            <style:paragraph-properties fo:margin-left="0.6cm" fo:margin-right="0cm" fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false" fo:orphans="2" fo:widows="2" fo:text-indent="-0.6cm" style:auto-text-indent="false" style:page-number="auto"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001de737" officeooo:paragraph-rsid="001de737" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P26" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:margin-left="0.6cm" fo:margin-right="0cm" fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false" fo:orphans="2" fo:widows="2" fo:text-indent="-0.6cm" style:auto-text-indent="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001de737" officeooo:paragraph-rsid="001de737" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P27" style:family="paragraph" style:parent-style-name="Standard" style:master-page-name="">
            <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false" fo:orphans="2" fo:widows="2" fo:text-indent="0cm" style:auto-text-indent="false" style:page-number="auto"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001de737" officeooo:paragraph-rsid="001de737" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P28" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false" fo:orphans="2" fo:widows="2" fo:text-indent="0cm" style:auto-text-indent="false"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001de737" officeooo:paragraph-rsid="001de737" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P29" style:family="paragraph" style:parent-style-name="Standard" style:master-page-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false" style:page-number="4"/>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="001f5be6" officeooo:paragraph-rsid="00229aac" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="P30" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:line-height="100%" fo:text-align="justify" style:justify-single-word="false">
                <style:tab-stops>
                    <style:tab-stop style:position="7.488cm"/>
                </style:tab-stops>
            </style:paragraph-properties>
            <style:text-properties style:font-name="Tahoma" fo:font-size="10pt" officeooo:rsid="002088ec" officeooo:paragraph-rsid="002088ec" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="T1" style:family="text">
            <style:text-properties style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="T2" style:family="text">
            <style:text-properties fo:font-size="10pt" officeooo:rsid="001cea51" style:font-size-asian="10pt" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="T3" style:family="text">
            <style:text-properties officeooo:rsid="001dc465"/>
        </style:style>
        <style:style style:name="T4" style:family="text">
            <style:text-properties style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color"/>
        </style:style>
        <style:style style:name="T5" style:family="text">
            <style:text-properties fo:font-weight="normal" style:font-name-complex="Arial"/>
        </style:style>
        <style:style style:name="T6" style:family="text">
            <style:text-properties fo:font-size="7pt" style:font-size-asian="7pt" style:font-size-complex="7pt"/>
        </style:style>
    </office:automatic-styles>
    <office:body>
  <xsl:apply-templates select="ausbildungsvertrag"/>
    </office:body>
</office:document-content>
</xsl:template>

<xsl:template match="ausbildungsvertrag">
        <office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">
            <office:forms form:automatic-focus="false" form:apply-design-mode="false"/>
            <text:sequence-decls>
                <text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
                <text:sequence-decl text:display-outline-level="0" text:name="Table"/>
                <text:sequence-decl text:display-outline-level="0" text:name="Text"/>
                <text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
            </text:sequence-decls>
            <text:h text:style-name="P4" text:outline-level="1"><text:span text:style-name="T3">AUSBILDUNGSVERTRAG</text:span></text:h>
            <text:p text:style-name="P5"/>
            <text:p text:style-name="P7">
                <text:span text:style-name="T2">für die Ausbildung im Fachhochschul-<xsl:value-of select="studiengang_typ"/>studiengang</text:span>
            </text:p>
            <text:p text:style-name="P7">
                <text:span text:style-name="T2"/>
            </text:p>
            <text:p text:style-name="P7">
                <text:span text:style-name="T2">„<xsl:value-of select="studiengang"/>“</text:span>
            </text:p>
            <text:p text:style-name="P6">
                <text:span text:style-name="T1">Studiengangskennzahl: <xsl:value-of select="studiengang_kz"/></text:span>
            </text:p>
            <text:p text:style-name="P6">
                <text:span text:style-name="T5">Genehmigt gemäß FHStG und HSQG (BGBl. I 74/2011, idgF)</text:span>
            </text:p>
            <text:p text:style-name="P6">
                <text:span text:style-name="T5">mit Bescheid GZ: FH12020012</text:span>
            </text:p>
            <text:p text:style-name="P8"/>
            <text:p text:style-name="P8"/>
            <text:p text:style-name="P17">Ausbildungsberechtigter:</text:p>
            <text:p text:style-name="P9">FACHHOCHSCHULE BURGENLAND GmbH</text:p>
            <text:p text:style-name="P9">Campus 1, 7000 Eisenstadt.</text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P9">Ansprechstelle für alle wesentlichen Fragen des Lehr- und Studienbetriebs ist der/die jeweilige Studiengangsleiter/in.</text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P17">Ausbildungsvertragsgrundlage:</text:p>
            <text:p text:style-name="P9">Die Ausbildung erfolgt auf Grundlage des Bundesgesetzes über Fachhochschul-Studiengänge (Fachhochschul-Studiengesetz – FHStG in der gültigen Fassung), vor allem auf Basis des Akkreditierungsantrags für den Fachhochschul-<xsl:value-of select="studiengang_typ"/>studiengang <xsl:value-of select="studiengang"/> in der jeweils gültigen Fassung. Die Hauptausbildungsstätte ist in Eisenstadt.</text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P17">Studierende/r:</text:p>
            <text:p text:style-name="P20">(Änderungen sind dem Ausbildungsberechtigten unverzüglich schriftlich bekannt zu geben.)</text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P9">Personenkennzeichen:<text:tab/>
                <text:tab/>
                <text:tab/><xsl:value-of select="matrikelnr"/></text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P9">SV-Nummer (o. Ersatzkennzeichen):<text:tab/><xsl:value-of select="svnr"/></text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P9">Titel:<text:tab/>
                <text:tab/>
                <text:tab/>
                <text:tab/>
                <text:tab/><xsl:value-of select="titelpre"/><xsl:value-of select="titelpost"/></text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P9">Familienname:<text:tab/>
                <text:tab/>
                <text:tab/>
                <text:tab/><xsl:value-of select="nachname"/></text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P9">Vorname:<text:tab/>
                <text:tab/>
                <text:tab/>
                <text:tab/><xsl:value-of select="vorname"/></text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P9">Adresse (Hauptwohnsitz):<text:tab/>
                <text:tab/><xsl:value-of select="nation"/>-<xsl:value-of select="plz"/> <xsl:value-of select="ort"/></text:p>
            <text:p text:style-name="P9">
                <text:tab/>
                <text:tab/>
                <text:tab/>
                <text:tab/>
                <text:tab/><xsl:value-of select="strasse"/></text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P9">geboren<text:tab/>am<text:tab/>
                <text:tab/>
                <text:tab/><xsl:value-of select="gebdatum"/></text:p>
            <text:p text:style-name="P9">
                <text:tab/>
                <text:tab/>in<text:tab/>
                <text:tab/>
                <text:tab/><xsl:value-of select="gebort"/></text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P9">Staatsbürgerschaft:<text:tab/>
                <text:tab/>
                <text:tab/><xsl:value-of select="staatsbuergerschaft"/></text:p>
            <text:p text:style-name="P9"/>
            <text:p text:style-name="P22">
                <text:span text:style-name="T4">Gesetzliche/r VertreterIn:</text:span>
            </text:p>
            <text:p text:style-name="P21">(sofern keine Volljährigkeit – unter 18 Jahren - vorliegt)</text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11">Name:<text:tab/>
                <text:tab/>.........................................................................................................</text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11">Adresse:<text:tab/>.........................................................................................................</text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11">
                <text:tab/>
                <text:tab/>.........................................................................................................</text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P18">Ausbildungsgegenstand:</text:p>
            <text:p text:style-name="P11">Fachhochschul-<xsl:value-of select="studiengang_typ"/>studiengang <xsl:value-of select="studiengang"/>.</text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P18">Ausbildungsziel:</text:p>
            <text:p text:style-name="P11">Abschluss der Ausbildung mit der Verleihung des akademischen Grades <xsl:value-of select="akadgrad"/> gemäß § 6 Abs. 2 FHStG idgF.</text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P18">Studienbeginn und Ausbildungsdauer:</text:p>
            <text:p text:style-name="P11">Studienbeginn:<text:tab/>
                <text:tab/><xsl:value-of select="studiensemester_beginn"/></text:p>
            <text:p text:style-name="P11">Regelstudienzeit: <text:tab/>derzeit <xsl:value-of select="studiengang_maxsemester"/> Semester (<xsl:value-of select="studiengang_anzahljahre"/> Jahre)</text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P18">Berufspraktikum:</text:p>
            <text:p text:style-name="P11">Das im Studiengang vorgesehene Berufspraktikum ist im 5. Semester im fremdsprachigen Ausland, d.h. im Besonderen in dem Land, in dem die gewählte 2. Fremdsprache gesprochen wird, vorgesehen. Über eine Anerkennung des Berufspraktikums entscheidet die Studiengangsleitung des Fachhochschul-<xsl:value-of select="studiengang_typ"/>studienganges <xsl:value-of select="studiengang"/> im Vorhinein. </text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P18">Intensivsprachkurs zur 2. Fremdsprache im Ausland:</text:p>
            <text:p text:style-name="P11">Im Rahmen des Studiums (derzeit nach dem 2. Studienjahr) ist vorgesehen, einen Intensivsprachkurs im Land der gewählten 2. Fremdsprache zu absolvieren. Über die Anerkennung des Intensivsprachkurses entscheidet die Studiengangsleitung des Fachhochschul-<xsl:value-of select="studiengang_typ"/>studienganges <xsl:value-of select="studiengang"/>.</text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P18">Anwesenheitspflicht, Prüfungen, vorzeitige Beendigung des Ausbildungsvertrages:</text:p>
            <text:p text:style-name="P11">Der/die Studierende ist zum Besuch der im Studienplan festgelegten Lehrveranstaltungen des Fachhochschul-<xsl:value-of select="studiengang_typ"/>studienganges <xsl:value-of select="studiengang"/> innerhalb des gesamten Ausbildungszeitraumes verpflichtet. Darüber hinaus ist er/sie verpflichtet, die vorgesehenen Prüfungen gemäß der Prüfungsordnung abzulegen. (Dies gilt auch für Freifächer.)</text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11">Der/die Studierende verpflichtet sich zur aktiven Teilnahme an qualitätssichernden Maßnahmen wie z.B. Lehrveranstaltungsevaluierung, Studentenbefragungen, Informationsveranstaltungen, Akzeptanz-erhebungen etc. der Fachhochschule Burgenland GmbH.</text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P22">Zu den Pflichten der Studierenden zählen insbesondere jene der persönlichen Anwesenheit, der aktiven Beteiligung am Studienbetrieb sowie die Einhaltung von Prüfungs- und Abgabeterminen und die Einhaltung der durch elektronische Veröffentlichung auf der internen Internet-Plattform der Studierenden zur Kenntnis gebrachten Studien- und Prüfungsordnung. </text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11">Krankheiten und Umstände, welche für den Lehr- und Studienbetrieb von wesentlicher Bedeutung sind, sind vom Studierenden unverzüglich der Studiengangsleitung zu melden.</text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11">Der Ausbildungsberechtigte ist zur vorzeitigen Auflösung dieses Vertrages mit sofortiger Wirkung aus folgenden Gründen berechtigt: </text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P25">-<text:tab/>Verletzung von Zahlungsverpflichtungen des/der Studierenden trotz fruchtloser einmonatiger Nachfristsetzung</text:p>
            <text:p text:style-name="P26"/>
            <text:p text:style-name="P26">-<text:tab/>strafrechtswidriges Verhalten des/der Studierenden insbesondere im Zusammenhang mit der Ausbildung</text:p>
            <text:p text:style-name="P26"/>
            <text:p text:style-name="P26">-<text:tab/>Verletzung der studentischen Verpflichtungen durch den/die Studierende(n) insbesondere Verstöße gegen die jeweils geltende Studien- und Prüfungsordnung</text:p>
            <text:p text:style-name="P26"/>
            <text:p text:style-name="P27">Im Falle des letztgenannten Auflösungsgrundes hat die Auflösung auf Antrag des Kollegiums der FH Burgenland zu erfolgen.</text:p>
            <text:p text:style-name="P28"/>
            <text:p text:style-name="P11">Der/die Studierende seiner/ihrerseits ist berechtigt, die Ausbildung unter Angabe von triftigen Gründen zum Ende eines jeden Semesters unter Einhaltung einer einmonatigen Kündigungsfrist zu kündigen. Die begründete schriftliche Mitteilung hat an den Ausbildungsberechtigen und die Studiengangsleitung zu erfolgen. </text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11">Der Ausbildungsvertrag endet automatisch durch Austritt des/der Studierenden auf Grund mangelnden Studienerfolges (negative Beurteilung der letztmöglichen Prüfungswiederholung), durch den Tod des/der Studierenden und durch den erfolgreichen Abschluss des Studienganges. </text:p>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P11"/>
            <text:p text:style-name="P19">Unterbrechung der Ausbildung:</text:p>
            <text:p text:style-name="P13">Dem/der Studierenden kann vom Ausbildungsberechtigten auf Antrag eine Unterbrechung der Ausbildung aus zwingenden persönlichen Gründen bis zu zwei Semester gewährt werden.</text:p>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P13">Er/sie hat in diesem Zusammenhang an den/die Studiengangsleiter/in des Fachhochschul-<xsl:value-of select="studiengang_typ"/>studienganges <xsl:value-of select="studiengang"/> einen schriftlichen begründeten Antrag auf Unterbrechung der Ausbildung mit Angabe des Unterbrechungsgrundes sowie des geplanten Unterbrechungszeitraumes zu richten. Die Gründe der Unterbrechung, die beabsichtigte Fortsetzung und die Aussichten auf den positiven Abschluss des Studiums sind nachzuweisen.</text:p>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P13">Eine Unterbrechung des Studiums bedarf einer positiven Begutachtung durch den/die Studiengangsleiter/in und einer schriftlichen Zustimmung der Fachhochschule Burgenland GmbH im Vorhinein. Während einer etwaigen Unterbrechung ruhen alle Rechte und Pflichten des Ausbildungsberechtigten, sohin der Fachhochschule Burgenland GmbH sowie des/der Studierenden und können keine Prüfungen abgelegt werden.</text:p>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P29">
                <text:span text:style-name="T4">Ausbildungspflicht:</text:span>
            </text:p>
            <text:p text:style-name="P14">Der Ausbildungsberechtigte verpflichtet sich, während des vereinbarten Ausbildungszeitraumes in Übereinstimmung mit dem mit der Republik Österreich abgeschlossenen Fördervertrag die vereinbarte Ausbildung sicherzustellen, um dem/der Studierenden die Erreichung des Ausbildungszieles zu ermöglichen. Der Ausbildungsberechtigte behält sich vor, eventuelle Freifächer und Wahlpflichtmodule bei zu geringer Nachfrage nicht anzubieten oder deren Teilnehmeranzahl zu limitieren.</text:p>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P13">Endet der Studiengang, so wird der Ausbildungsberechtigte dafür sorgen, dass dem/der Studierenden jedenfalls die Gelegenheit gegeben wird, sein/ihr Studium innerhalb der vorgeschriebenen Studiendauer abschließen zu können. Allfällige Schadenersatz oder sonstige Ansprüche aus welchem Rechtstitel immer werden einvernehmlich ausgeschlossen und verzichtet der/die Studierende darauf. Dies soweit ein solcher Ausschluss oder Verzicht rechtlich zulässig ist. </text:p>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P13">Der/die Studierende nimmt zur Kenntnis, dass im Falle zu geringer Anmeldezahlen der Studiengang nicht stattfinden kann. </text:p>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P19">Förderungen:</text:p>
            <text:p text:style-name="P13">Der/die Studierende hat sich während seiner/ihrer Ausbildung um mögliche Förderungen, wie z.B. Studien- und Heimbeihilfen, Fahrtkostenzuschüsse udgl., selbst zu kümmern. Die erforderlichen Bestätigungen können im Office des Fachhochschul-Studienganges beantragt werden.</text:p>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P19">Studienbeitrag:</text:p>
            <text:p text:style-name="P13">Der Ausbildungsberechtigte ist gem. § 2 Abs. 2 FHStG berechtigt, einen Studienbeitrag einzuheben. </text:p>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P19">Studierendenbeitrag (ÖH-Beitrag):</text:p>
            <text:p text:style-name="P13">Gemäß § 29 (4) des Hochschülerschaftsgesetzes 1998 (HSG 1998) setzt die Zulassung zum Studium und die Meldung der Fortsetzung des Studiums die Entrichtung des Studierendenbeitrages einschließlich allfälliger Sonderbeiträge (Abs. 6) für das betreffende Semester voraus. </text:p>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P13">
                <text:span text:style-name="T4">Lehrbehelfe:</text:span> 
            </text:p>
            <text:p text:style-name="P13">Der/die Studierende hat sich Lehrbehelfe und Lehrmaterialien auf eigene Kosten zu beschaffen bzw. die Kosten für Exkursionen, Sprachkurse, Sommerhochschulen etc. zu tragen.</text:p>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P19">eLearning/Blended Learning:</text:p>
            <text:p text:style-name="P13">Der/die Studierende bestätigt, dass er/sie die geltenden Gesetze zum Schutz geistigen Eigentums und der informationellen Selbstbestimmung kennt und befolgt (z.B. Plagiate, Weitergabe von online-Skripten oder Veröffentlichung von persönlichen Daten anderer). Der/die Studierende nimmt zur Kenntnis, dass er/sie persönlich haftet, wenn er/sie gesetzeswidrig Inhalte online verfügbar macht und hat die Fachhochschule Burgenland GmbH diesbezüglich schad- und klaglos zu halten. Es ist dem/der Studierenden nicht erlaubt, die vom Ausbildungsberechtigten eingerichtete eLearning- bzw. Blended Learning-Infrastruktur für kommerzielle Zwecke zu nutzen. Der/die Studierende räumt dem Ausbildungsberechtigten das Recht ein, seine/ihre online gestellten Beiträge einzusehen und für Zwecke der Lehre und der Forschung in anonymisierter Form zu nutzen. Bei Missachtung obiger Regelungen haftet der/die Studierende persönlich für in diesem Zusammenhang verursachte Schäden. Grobe Verstöße können zur Auflösung dieses Ausbildungsvertrages und zum Ausscheiden aus dem Studiengang führen.</text:p>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P24">Sonstiges:</text:p>
            <text:p text:style-name="P15">Sofern der/die Studierende bis zum Ende des ersten Semesters aktiv studiert, wird die studienplatzsichernde Kaution nach dem ersten Semester zurückgezahlt. Die Kaution verfällt jedenfalls bei einem vorzeitigen Austritt oder Ausscheiden (d.h. Austritt oder Ausscheiden vor einschließlich <xsl:value-of select="studiensemester_endedatum"/>).</text:p>
            <text:p text:style-name="P15"/>
            <text:p text:style-name="P15">Studierende an österreichischen Fachhochschul-Studiengängen bzw. Fachhochschulen sind gemäß der Novelle des Fachhochschul-Studiengesetzes vom Juli 2011 Mitglieder der Österreichischen Hochschülerinnen- und Hochschülerschaft (ÖH) und unterliegen damit den Bestimmungen des Hochschülerinnen- und Hochschülerschaftsgesetzes 1998 (HSG 1998) in der gültigen Fassung. </text:p>
            <text:p text:style-name="P15"/>
            <text:p text:style-name="P15">Dieser Vertrag unterliegt österreichischem Recht. Gerichtsstand ist Eisenstadt.</text:p>
            <text:p text:style-name="P15"/>
            <text:p text:style-name="P15">Änderungen oder Ergänzungen dieses Vertrages bedürfen zu ihrer Rechtswirksamkeit der Schriftform. Dies gilt auch für das Abgehen vom Formerfordernis selbst. Mündliche Nebenabreden bestehen nicht. </text:p>
            <text:p text:style-name="P15"/>
            <text:p text:style-name="P15">Sollten einzelne Bestimmungen dieses Vertrages ungültig, unwirksam oder lückenhaft sein, berührt dies nicht die übrigen Vertragsbestimmungen. Die Vertragspartner werden ungültige, unwirksame oder lückenhafte Bestimmungen durch solche ersetzen bzw. ergänzen die dem wirtschaftlichen Zweck der ungültigen, unwirksamen oder lückenhaften Bestimmungen wirtschaftlich entsprechen bzw. möglichst nahe kommen.</text:p>
            <text:p text:style-name="P15">
                <text:s/>
            </text:p>
            <text:p text:style-name="P15">Dieser Ausbildungsvertrag ist gebührenfrei.</text:p>
            <text:p text:style-name="P15"/>
            <text:p text:style-name="P15">Der/die Studierende ist verpflichtet, etwaige Namensänderungen (z.B. durch Heirat) sowie seinen Hauptwohnsitz und eventuellen Zweitwohnsitz und deren Änderungen dem Ausbildungsberechtigten unverzüglich schriftlich bekannt zu geben.</text:p>
            <text:p text:style-name="P15"/>
            <text:p text:style-name="P15">Der/die Studierende ist verpflichtet, jeden Unfall im Sinne § 363 ASVG spätestens binnen drei Tagen an die FH Burgenland zu melden.</text:p>
            <text:p text:style-name="P15"/>
            <text:p text:style-name="P15">Der/die Studierende bestätigt, dass er/sie die Hausordnung in der jeweils gültigen Fassung, welche auf der Homepage der Fachhochschule Burgenland GmbH (www.fh-burgenland.at) veröffentlicht ist, zur Kenntnis genommen hat.</text:p>
            <text:p text:style-name="P15"/>
            <text:p text:style-name="P15">Das Original des Ausbildungsvertrages verbleibt beim Ausbildungsberechtigten. Eine Zweitschrift wird dem/der Studierenden ausgehändigt.</text:p>
            <text:p text:style-name="P15"/>
            <text:p text:style-name="P15">Die personenbezogenen Daten werden vertraulich behandelt und nicht an Dritte weitergegeben. </text:p>
            <text:p text:style-name="P15"/>
            <text:p text:style-name="P15">Der Ausbildungsvertrag kommt nur unter der Voraussetzung der Erfüllung der Zugangsvoraus-setzungen bis 31. Oktober <xsl:value-of select="aktuellesJahr"/> zustande.</text:p>
            <text:p text:style-name="P15"/>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P13"/>
            <text:p text:style-name="P23">Der/die Studierende ist damit einverstanden, dass ihm/ihr die Fachhochschule Burgenland GmbH und mit ihr verbundene Gesellschaften Informationen jeglicher Art z.B. Newsletter und dergleichen in postalischer oder elektronischer Form insbesondere an die bekanntgegebene E-Mailadresse des/der Studierenden übermittelt, sofern der/die Studierende hiezu nicht ausdrücklich und nachweislich widerspricht. </text:p>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P16">Dieser Ausbildungsvertrag wird erst wirksam, wenn er sowohl vom Studierenden und gegebenenfalls dessen gesetzlichem Vertreter als auch dem Ausbildungsberechtigten unterzeichnet wurde. </text:p>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P30">Der/die Studierende:<text:tab/>__________________________________________</text:p>
            <text:p text:style-name="P30">
                <text:tab/>Datum<text:tab/>
                <text:tab/>
                <text:tab/>Unterschrift</text:p>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P30">Gesetzliche/r VertreterIn:<text:tab/>__________________________________________</text:p>
            <text:p text:style-name="P30">
                <text:span text:style-name="T6">(sofern keine Volljährigkeit – unter 18 Jahren – vorliegt)<text:tab/></text:span>Datum <text:tab/>
                <text:tab/>
                <text:tab/>Unterschrift</text:p>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P30">FACHHOCHSCHULE</text:p>
            <text:p text:style-name="P30">BURGENLAND GmbH </text:p>
            <text:p text:style-name="P30">Campus 1, 7000 Eisenstadt<text:tab/>__________________________________________</text:p>
            <text:p text:style-name="P30">
                <text:tab/>Datum<text:tab/>
                <text:tab/>
                <text:tab/>Unterschrift</text:p>
            <text:p text:style-name="P16"/>
            <text:p text:style-name="P12"/>
        </office:text>
</xsl:template>
</xsl:stylesheet>