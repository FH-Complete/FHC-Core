<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
>
<xsl:output method="xml" version="1.0" indent="yes"/>

<xsl:template match="zertifikate">
<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:ooo="http://openoffice.org/2004/office" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:rpt="http://openoffice.org/2005/report" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" office:version="1.2">
   <office:scripts />
   <office:font-face-decls>
      <style:font-face style:name="OpenSymbol" svg:font-family="OpenSymbol" style:font-charset="x-symbol" />
      <style:font-face style:name="Mangal1" svg:font-family="Mangal" />
      <style:font-face style:name="Times New Roman" svg:font-family="'Times New Roman'" style:font-family-generic="roman" style:font-pitch="variable" />
      <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable" />
      <style:font-face style:name="Mangal" svg:font-family="Mangal" style:font-family-generic="system" style:font-pitch="variable" />
      <style:font-face style:name="Microsoft YaHei" svg:font-family="'Microsoft YaHei'" style:font-family-generic="system" style:font-pitch="variable" />
      <style:font-face style:name="SimSun" svg:font-family="SimSun" style:font-family-generic="system" style:font-pitch="variable" />
   </office:font-face-decls>
   <office:automatic-styles>
      <style:style style:name="Tabelle1" style:family="table">
         <style:table-properties style:width="17cm" table:align="margins" />
      </style:style>
      <style:style style:name="Tabelle1.A" style:family="table-column">
         <style:table-column-properties style:column-width="12.832cm" style:rel-column-width="49467*" />
      </style:style>
      <style:style style:name="Tabelle1.B" style:family="table-column">
         <style:table-column-properties style:column-width="4.168cm" style:rel-column-width="16068*" />
      </style:style>
      <style:style style:name="Tabelle1.A1" style:family="table-cell">
         <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000" />
      </style:style>
      <style:style style:name="Tabelle1.B1" style:family="table-cell">
         <style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000" />
      </style:style>
      <style:style style:name="Tabelle1.A2" style:family="table-cell">
         <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000" />
      </style:style>
      <style:style style:name="Tabelle2" style:family="table">
         <style:table-properties style:width="17cm" table:align="margins" />
      </style:style>
      <style:style style:name="Tabelle2.A" style:family="table-column">
         <style:table-column-properties style:column-width="1.43cm" />
      </style:style>
      <style:style style:name="Tabelle2.B" style:family="table-column">
         <style:table-column-properties style:column-width="0.88cm" />
      </style:style>
      <style:style style:name="Tabelle2.C" style:family="table-column">
         <style:table-column-properties style:column-width="5.837cm" />
      </style:style>
      <style:style style:name="Tabelle2.D" style:family="table-column">
         <style:table-column-properties style:column-width="4.688cm" />
      </style:style>
      <style:style style:name="Tabelle2.E" style:family="table-column">
         <style:table-column-properties style:column-width="4.168cm" />
      </style:style>
      <style:style style:name="Tabelle2.A1" style:family="table-cell">
         <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000" />
      </style:style>
      <style:style style:name="Tabelle2.E1" style:family="table-cell">
         <style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000" />
      </style:style>
      <style:style style:name="Tabelle2.A2" style:family="table-cell">
         <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.05pt solid #000000" />
      </style:style>
      <style:style style:name="Tabelle2.D2" style:family="table-cell">
         <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000" />
      </style:style>
      <style:style style:name="Tabelle3" style:family="table">
         <style:table-properties style:width="17cm" table:align="margins" />
      </style:style>
      <style:style style:name="Tabelle3.A" style:family="table-column">
         <style:table-column-properties style:column-width="5.667cm" style:rel-column-width="21845*" />
      </style:style>
      <style:style style:name="Tabelle3.A1" style:family="table-cell">
         <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000" />
      </style:style>
      <style:style style:name="Tabelle3.C1" style:family="table-cell">
         <style:table-cell-properties fo:padding="0.097cm" fo:border="0.05pt solid #000000" />
      </style:style>
      <style:style style:name="Tabelle3.A2" style:family="table-cell">
         <style:table-cell-properties fo:padding="0.097cm" fo:border="none" />
      </style:style>
      <style:style style:name="Tabelle3.C2" style:family="table-cell">
         <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="0.05pt solid #000000" />
      </style:style>
      <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Text_20_body">
         <style:paragraph-properties fo:line-height="130%"/>
         <style:text-properties style:font-name="Arial" fo:font-size="14pt" fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold" officeooo:rsid="00156f39" officeooo:paragraph-rsid="00156f39" />
      </style:style>
      <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Table_20_Contents">
         <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt" />
      </style:style>
      <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Table_20_Contents">
         <style:paragraph-properties fo:line-height="130%"/>
         <style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00156f39" officeooo:paragraph-rsid="00156f39" style:font-size-asian="8pt" style:font-size-complex="8pt" />
      </style:style>
      <style:style style:name="P4" style:family="paragraph" style:parent-style-name="Table_20_Contents">
         <style:paragraph-properties fo:line-height="130%"/>
         <style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="0016dfcf" officeooo:paragraph-rsid="0016dfcf" style:font-size-asian="8pt" style:font-size-complex="8pt" />
      </style:style>
      <style:style style:name="P5" style:family="paragraph" style:parent-style-name="Table_20_Contents">
         <style:paragraph-properties fo:line-height="130%"/>
         <style:text-properties style:font-name="Arial" fo:font-size="12pt" officeooo:rsid="00156f39" officeooo:paragraph-rsid="00156f39" style:font-size-asian="10.5pt" style:font-size-complex="12pt" />
      </style:style>
      <style:style style:name="P6" style:family="paragraph" style:parent-style-name="Table_20_Contents">
         <style:paragraph-properties fo:line-height="130%"/>
         <style:text-properties style:font-name="Arial" fo:font-size="12pt" officeooo:rsid="00156f39" officeooo:paragraph-rsid="00156f39" style:font-size-asian="12pt" style:font-size-complex="12pt" />
      </style:style>
      <style:style style:name="P7" style:family="paragraph" style:parent-style-name="Table_20_Contents">
         <style:paragraph-properties fo:line-height="130%"/>
         <style:text-properties style:font-name="Arial" fo:font-size="7pt" officeooo:rsid="00156f39" officeooo:paragraph-rsid="00156f39" style:font-size-asian="7pt" style:font-size-complex="7pt" />
      </style:style>
      <style:style style:name="P8" style:family="paragraph" style:parent-style-name="Table_20_Contents" style:list-style-name="L1">
         <style:paragraph-properties fo:line-height="130%"/>
         <style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00156f39" officeooo:paragraph-rsid="00156f39" style:font-size-asian="8pt" style:font-size-complex="8pt" />
      </style:style>
      <style:style style:name="P9" style:family="paragraph" style:parent-style-name="Table_20_Contents" style:list-style-name="L2">
         <style:paragraph-properties fo:line-height="130%"/>
         <style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00156f39" officeooo:paragraph-rsid="00156f39" style:font-size-asian="8pt" style:font-size-complex="8pt" />
      </style:style>
      <style:style style:name="P10" style:family="paragraph" style:parent-style-name="Table_20_Contents" style:list-style-name="L3">
         <style:paragraph-properties fo:line-height="130%"/>
         <style:text-properties style:font-name="Arial" fo:font-size="8pt" officeooo:rsid="00156f39" officeooo:paragraph-rsid="00156f39" style:font-size-asian="8pt" style:font-size-complex="8pt" />
      </style:style>
      <style:style style:name="P11" style:family="paragraph" style:parent-style-name="Table_20_Contents">
         <style:paragraph-properties fo:line-height="130%"/>
         <style:text-properties style:font-name="Arial" fo:font-size="12pt" officeooo:rsid="0017d72e" officeooo:paragraph-rsid="0017d72e" style:font-size-asian="12pt" style:font-size-complex="12pt" />
      </style:style>
      <style:style style:name="P12" style:family="paragraph" style:parent-style-name="Table_20_Contents">
         <style:paragraph-properties fo:line-height="130%"/>
         <style:text-properties style:font-name="Arial" fo:font-size="12pt" officeooo:rsid="00156f39" officeooo:paragraph-rsid="0017d72e" style:font-size-asian="12pt" style:font-size-complex="12pt" />
      </style:style>
	  <style:style style:name="P13" style:family="paragraph" style:parent-style-name="Footnote">
         <style:paragraph-properties fo:line-height="130%"/>
		 <style:text-properties style:font-name="Arial" fo:font-size="8pt" style:font-size-asian="8pt" style:font-size-complex="8pt" />
	  </style:style>
      <style:style style:name="P14" style:family="paragraph" style:parent-style-name="Table_20_Contents">
         <style:paragraph-properties fo:text-align="center"/>
         <style:text-properties style:font-name="Arial" fo:font-size="14pt" officeooo:rsid="00156f39" officeooo:paragraph-rsid="00156f39" />
      </style:style>
      <style:style style:name="T1" style:family="text">
         <style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt" />
      </style:style>
      <style:style style:name="T2" style:family="text">
         <style:text-properties fo:font-size="10pt" officeooo:rsid="0017d72e" style:font-size-asian="10pt" style:font-size-complex="10pt" />
      </style:style>
      <style:style style:name="T3" style:family="text">
         <style:text-properties officeooo:rsid="0017d72e" />
      </style:style>
      <text:list-style style:name="L1">
         <text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-0.635cm" fo:margin-left="1.27cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.905cm" fo:text-indent="-0.635cm" fo:margin-left="1.905cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-0.635cm" fo:margin-left="2.54cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.175cm" fo:text-indent="-0.635cm" fo:margin-left="3.175cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.81cm" fo:text-indent="-0.635cm" fo:margin-left="3.81cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.445cm" fo:text-indent="-0.635cm" fo:margin-left="4.445cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.08cm" fo:text-indent="-0.635cm" fo:margin-left="5.08cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.715cm" fo:text-indent="-0.635cm" fo:margin-left="5.715cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.35cm" fo:text-indent="-0.635cm" fo:margin-left="6.35cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
      </text:list-style>
      <text:list-style style:name="L2">
         <text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-0.635cm" fo:margin-left="1.27cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.905cm" fo:text-indent="-0.635cm" fo:margin-left="1.905cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-0.635cm" fo:margin-left="2.54cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.175cm" fo:text-indent="-0.635cm" fo:margin-left="3.175cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.81cm" fo:text-indent="-0.635cm" fo:margin-left="3.81cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.445cm" fo:text-indent="-0.635cm" fo:margin-left="4.445cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.08cm" fo:text-indent="-0.635cm" fo:margin-left="5.08cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.715cm" fo:text-indent="-0.635cm" fo:margin-left="5.715cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.35cm" fo:text-indent="-0.635cm" fo:margin-left="6.35cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
      </text:list-style>
      <text:list-style style:name="L3">
         <text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.27cm" fo:text-indent="-0.635cm" fo:margin-left="1.27cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.905cm" fo:text-indent="-0.635cm" fo:margin-left="1.905cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.54cm" fo:text-indent="-0.635cm" fo:margin-left="2.54cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.175cm" fo:text-indent="-0.635cm" fo:margin-left="3.175cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.81cm" fo:text-indent="-0.635cm" fo:margin-left="3.81cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.445cm" fo:text-indent="-0.635cm" fo:margin-left="4.445cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.08cm" fo:text-indent="-0.635cm" fo:margin-left="5.08cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols" text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.715cm" fo:text-indent="-0.635cm" fo:margin-left="5.715cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols" text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.35cm" fo:text-indent="-0.635cm" fo:margin-left="6.35cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
         <text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols" text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
               <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.985cm" fo:text-indent="-0.635cm" fo:margin-left="6.985cm" />
            </style:list-level-properties>
         </text:list-level-style-bullet>
      </text:list-style>
   </office:automatic-styles>
   <office:body>
	   <xsl:apply-templates select="zertifikat" />
    </office:body>
</office:document-content>
</xsl:template>

<xsl:template match="zertifikat">
    <office:text text:use-soft-page-breaks="true" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0">
         <text:sequence-decls>
            <text:sequence-decl text:display-outline-level="0" text:name="Illustration" />
            <text:sequence-decl text:display-outline-level="0" text:name="Table" />
            <text:sequence-decl text:display-outline-level="0" text:name="Text" />
            <text:sequence-decl text:display-outline-level="0" text:name="Drawing" />
         </text:sequence-decls>
		 <text:p text:style-name="P14">KATHOLOGISCH-THEOLOGISCHE <text:span text:style-name="T1">PRIVAT</text:span>UNIVERSITÄT LINZ</text:p>
		 <text:p text:style-name="P14">Theologische Fakultät</text:p>
         <text:p text:style-name="P1"></text:p>
         <text:p text:style-name="P1">Lehrveranstaltungszeugnis</text:p>
         <table:table table:name="Tabelle1" table:style-name="Tabelle1">
            <table:table-column table:style-name="Tabelle1.A" />
            <table:table-column table:style-name="Tabelle1.B" />
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A1" office:value-type="string">
                  <text:p text:style-name="P3">Familienname, Vorname(n)</text:p>
                  <text:p text:style-name="P11"><xsl:value-of select="nachname"/>, <xsl:value-of select="vorname"/></text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle1.B1" office:value-type="string">
                  <text:p text:style-name="P3">Geburtsdatum</text:p>
                  <text:p text:style-name="P11"><xsl:value-of select="gebdatum"/></text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle1.A2" table:number-columns-spanned="2" office:value-type="string">
                  <text:p text:style-name="P7">Studium</text:p>
                  <text:p text:style-name="P12">
                     <text:span text:style-name="T3"><xsl:value-of select="lv_studiengang_bezeichnung"/></text:span>
                     <text:span text:style-name="T1">(Studienplan: <xsl:value-of select="studienplan"/>)</text:span>
                  </text:p>
               </table:table-cell>
               <table:covered-table-cell />
            </table:table-row>
         </table:table>
         <text:p text:style-name="Standard" />
         <table:table table:name="Tabelle2" table:style-name="Tabelle2">
            <table:table-column table:style-name="Tabelle2.A" />
            <table:table-column table:style-name="Tabelle2.B" />
            <table:table-column table:style-name="Tabelle2.C" />
            <table:table-column table:style-name="Tabelle2.D" />
            <table:table-column table:style-name="Tabelle2.E" />
            <table:table-row>
               <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
                  <text:p text:style-name="P3">Art
					<text:note text:id="ftn0" text:note-class="footnote">
						<text:note-citation>1</text:note-citation>
						<text:note-body>
							<text:p text:style-name="P13">VL (Vorlesung), SV (Spezialvorlesung), VL+KO bzw. SV+KO (Spezial/Vorlesung mit Konversatorium), PS (Proseminar), SE (Seminar), AG (Arbeitsgemeinschaft), UE (Übung), PK (Praktikum), EX (Exkursion), PA (Projektarbeit), KO (Konversatorium)</text:p>
							<text:p text:style-name="P2"/>
						</text:note-body>
					</text:note>
				  </text:p>
                  <text:p text:style-name="P5"><xsl:value-of select="lehrform_kurzbz"/></text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle2.A1" office:value-type="string">
                  <text:p text:style-name="P3">CP
					<text:note text:id="ftn1" text:note-class="footnote">
						<text:note-citation>2</text:note-citation>
						<text:note-body>
							<text:p text:style-name="P13">Studienleistungen werden in Creditpoints (CP) nach ECTS bemessen. 1 CP steht für einen Arbeitsaufwand von 25 bis 30 Stunden zur Erreichung des Bildungsziels der Lehrveranstaltung.</text:p>
							<text:p text:style-name="P2"/>
						</text:note-body>
					</text:note>
				  </text:p>
                  <text:p text:style-name="P5"><xsl:value-of select="ects"/></text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle2.A1" table:number-columns-spanned="2" office:value-type="string">
                  <text:p text:style-name="P3">Titel der Lehrveranstaltung</text:p>
                  <text:p text:style-name="P5"><xsl:value-of select="lehrfach_bezeichnung"/></text:p>
               </table:table-cell>
               <table:covered-table-cell />
               <table:table-cell table:style-name="Tabelle2.E1" office:value-type="string">
                  <text:p text:style-name="P3">absolviert im Semester</text:p>
                  <text:p text:style-name="P5"><xsl:value-of select="studiensemester"/></text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle2.A2" table:number-columns-spanned="3" office:value-type="string">
                  <text:p text:style-name="P3">anzurechnen in einem Modul des ersten Studienabschnitts</text:p>
                  <text:list xml:id="list5825913448202049734" text:style-name="L1">
                     <text:list-item>
                        <text:p text:style-name="P8">Einführungsmodul</text:p>
                     </text:list-item>
                     <text:list-item>
                        <text:p text:style-name="P8">Fächermodul Grundkurse</text:p>
                     </text:list-item>
                     <text:list-item>
                        <text:p text:style-name="P8">Fächermodul Vertiefung I</text:p>
                     </text:list-item>
                     <text:list-item>
                        <text:p text:style-name="P8">Thematisches Modul I ("WiEGe")</text:p>
                     </text:list-item>
                     <text:list-item>
                        <text:p text:style-name="P8">Thematisches Modul II ("Kunst etc.")</text:p>
                     </text:list-item>
                     <text:list-item>
                        <text:p text:style-name="P8">Wahlmodul I</text:p>
                     </text:list-item>
                  </text:list>
               </table:table-cell>
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:table-cell table:style-name="Tabelle2.D2" table:number-columns-spanned="2" office:value-type="string">
                  <text:p text:style-name="P3">anzurechnen in einem Modul des zweiten Studienabschnitts</text:p>
                  <text:list xml:id="list3413782848763846391" text:style-name="L2">
                     <text:list-item>
                        <text:p text:style-name="P9">Fächermodul Vertiefung II</text:p>
                     </text:list-item>
                     <text:list-item>
                        <text:p text:style-name="P9">Thematisches Modul III ("Säkularisierung etc.")</text:p>
                     </text:list-item>
                     <text:list-item>
                        <text:p text:style-name="P9">Wahlmodul II</text:p>
                     </text:list-item>
                     <text:list-item>
                        <text:p text:style-name="P9">Diplommodul</text:p>
                     </text:list-item>
                  </text:list>
               </table:table-cell>
               <table:covered-table-cell />
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle2.D2" table:number-columns-spanned="5" office:value-type="string">
                  <text:p text:style-name="P3">anzurechnen in</text:p>
                  <text:list xml:id="list3426876807229036270" text:style-name="L3">
                     <text:list-item>
                        <text:p text:style-name="P10">Modul "Qualifikation für den Religionsunterricht an Pflichtschulen"</text:p>
                     </text:list-item>
                     <text:list-item>
                        <text:p text:style-name="P10">Erweiterungsstudium Katholische Religionspädagogik</text:p>
                     </text:list-item>
                  </text:list>
               </table:table-cell>
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle2.D2" table:number-columns-spanned="5" office:value-type="string">
                  <text:p text:style-name="P3">Wortlaut der Studienverpflichtung gemäß Studienplan (nur eintragen, wenn der Lehrveranstaltungstitel damit nicht identisch ist)</text:p>
                  <text:p text:style-name="P6"><xsl:value-of select="bezeichnung"/></text:p>
               </table:table-cell>
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
               <table:covered-table-cell />
            </table:table-row>
         </table:table>
         <text:p text:style-name="Standard" />
         <table:table table:name="Tabelle3" table:style-name="Tabelle3">
            <table:table-column table:style-name="Tabelle3.A" table:number-columns-repeated="3" />
            <table:table-row>
               <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                  <text:p text:style-name="P4">Benotung
					<text:note text:id="ftn2" text:note-class="footnote">
						<text:note-citation>3</text:note-citation>
						<text:note-body>
							<text:p text:style-name="P13">Notenskala: Sehr gut (1), gut (2), befriedigend (3), genügend (4), nicht genügend (5). - Bei Lehrveranstaltungen, wo eine Benotung gemäß dieser Notenskala unzweckmäßig ist, lautet die positive Benotung "mit Erfolg teilgenommen", die negative Benotung "ohne Erfolg teilgenommen".</text:p>
							<text:p text:style-name="P2"/>
						</text:note-body>
					</text:note>
				  </text:p>
				  <text:p text:style-name="P6"><xsl:value-of select="note"/></text:p>
                  <text:p text:style-name="P2" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle3.A1" office:value-type="string">
                  <text:p text:style-name="P4">Prüfer/in</text:p>
				  <text:p text:style_name="P6"><xsl:value-of select="pruefer"/></text:p>
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle3.C1" office:value-type="string">
                  <text:p text:style-name="P4">Datum Signierung Prüfer/in</text:p>
				  <text:p text:style-name="P6"><xsl:value-of select="benotungsdatum"/></text:p>
               </table:table-cell>
            </table:table-row>
            <table:table-row>
               <table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
                  <text:p text:style-name="P4" />
                  <text:p text:style-name="P4">Stampiglie KTU Linz / Rektorat</text:p>
                  <text:p text:style-name="P6" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle3.A2" office:value-type="string">
                  <text:p text:style-name="P2" />
               </table:table-cell>
               <table:table-cell table:style-name="Tabelle3.C2" office:value-type="string">
                  <text:p text:style-name="P4">Datum Eintrag Prüfungsevidenz</text:p>
				  <text:p text:style-name="P6"><xsl:value-of select="uebernahmedatum"/></text:p>
               </table:table-cell>
            </table:table-row>
         </table:table>
         <text:p text:style-name="Standard" />
      </office:text>
	  </xsl:template>
</xsl:stylesheet>