<?xml version="1.0" encoding="ISO-8859-15"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="abschlusspruefung">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="350mm" page-width="210mm" margin="5mm 10mm 5mm 10mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="pruefung"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="pruefung">					
			<fo:page-sequence master-reference="PageMaster">
				<fo:flow flow-name="xsl-region-body" >
	

<fo:block-container position="absolute" top="76pt" left="443pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt"> 
<fo:inline vertical-align="super">
								Personenkennzeichen
</fo:inline>
</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="95pt" left="450pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="12pt"> 

		0410227046
</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="120pt" left="443pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt"> 
<fo:inline vertical-align="super">
								Studiengangskennzahl
</fo:inline>
</fo:block>
</fo:block-container>


<fo:block-container position="absolute" top="91pt" left="445pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="45mm"/>
<fo:table-body>
<fo:table-row line-height="25pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container> 


<fo:block-container position="absolute" top="135pt" left="445pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="45mm"/>
<fo:table-body>
<fo:table-row line-height="25pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container> 


<fo:block-container position="absolute" top="139pt" left="450pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="12pt"> 

		0011
</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="150pt" left="30pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="16pt">
							Diplomprüfungszeugnis
</fo:block>
</fo:block-container>


<fo:block-container position="absolute" top="180pt" left="30pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="130mm"/>
<fo:table-body>
<fo:table-row line-height="30pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="181pt" left="32pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		Vorname(n), Familienname
        <fo:inline font-size="12pt">
		\n aaaaaaaa				
        </fo:inline>



</fo:block>
</fo:block-container>


<fo:block-container position="absolute" top="180pt" left="400pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="61mm"/>
<fo:table-body>
<fo:table-row line-height="30pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="181pt" left="402pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		Geburtsdatum

   <fo:inline font-size="12pt">
		\n bbbbbbbbb				
        </fo:inline>

</fo:block>
</fo:block-container>




<fo:block-container position="absolute" top="221pt" left="30pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="191mm"/>
<fo:table-body>
<fo:table-row line-height="100pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="223pt" left="32pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		Diplom-Studiengang

                 <fo:inline font-size="12pt" font-weight="900" text-align="center" >                    \n
                    \n
		     \n cccccccccc				
                  </fo:inline>

</fo:block>
</fo:block-container>


<fo:block-container position="absolute" top="322pt" left="30pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="191mm"/>
<fo:table-body>
<fo:table-row line-height="30pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="323pt" left="32pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
Gesetzliche Grundlage: gem. § 5 Abs. 1 des Bundesgesetzes über Fachhochschul-Studiengänge (FHStG), BGBl.Nr. 340/1993
</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="353pt" left="30pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="191mm"/>
<fo:table-body>
<fo:table-row line-height="80pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="354pt" left="32pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		Gesamtnote der Diplomprüfung

                 <fo:inline font-size="12pt" font-weight="900" text-align="center" >                    \n
                 
		     \n dddddddddd				
                  </fo:inline>

</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="460pt" left="30pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="130mm"/>
<fo:table-body>
<fo:table-row line-height="10pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="460pt" left="32pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		Diplomprüfung

</fo:block>
</fo:block-container>


<fo:block-container position="absolute" top="460pt" left="400pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="61mm"/>
<fo:table-body>
<fo:table-row line-height="10pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="460pt" left="402pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		Datum

</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="475pt" left="30pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="130mm"/>
<fo:table-body>
<fo:table-row line-height="10pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="475pt" left="32pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		1. Teil der Diplomprüfung: Diplomarbeit

</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="475pt" left="400pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="61mm"/>
<fo:table-body>
<fo:table-row line-height="10pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="475pt" left="402pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		eeeeeeeeeeee

</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="490pt" left="30pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="191mm"/>
<fo:table-body>
<fo:table-row line-height="80pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="491pt" left="32pt" height="1pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		Thema der Diplomarbeit

                 <fo:inline font-size="12pt" font-weight="900" text-align="center" >                    \n
		     \n fffffffffffffffff				
                  </fo:inline>

</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="571pt" left="30pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="130mm"/>
<fo:table-body>
<fo:table-row line-height="10pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="571pt" left="32pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		2. Teil der Diplomprüfung: Kommissionelle Prüfung

</fo:block>
</fo:block-container>


<fo:block-container position="absolute" top="571pt" left="400pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="61mm"/>
<fo:table-body>
<fo:table-row line-height="10pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="571pt" left="402pt" height="0pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		ggggggggggggg

</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="586pt" left="204pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="130mm"/>
<fo:table-body>
<fo:table-row line-height="60pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="586pt" left="206pt" height="1pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		Vorsitzender des Prüfungssenats
                 \n
                 \n
                 \n
                iiiiiiiiiiiiiiiii
 
</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="586pt" left="30pt" height="0pt">
<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="61mm"/>
<fo:table-body>
<fo:table-row line-height="60pt">
<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
									
		</fo:block>
	</fo:table-cell>
	</fo:table-row>      
	</fo:table-body>
</fo:table>
</fo:block-container>

<fo:block-container position="absolute" top="586pt" left="32pt" height="1pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
		Ort, Ausstellungsdatum
                 \n
                 \n
                 \n
                Wien, hhhhhhhhhhhhhh
 
</fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="650pt" left="32pt" height="1pt">
<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="6pt">
	
Gesamtnote: mit ausgezeichnetem Erfolg bestanden, mit gutem Erfolg bestanden \n ZVR-Nr.: 074476426, DVR-Nr.: 0928381
 
 
</fo:block>
</fo:block-container>





 
			</fo:flow>
		</fo:page-sequence>
	
	</xsl:template>
	
</xsl:stylesheet >