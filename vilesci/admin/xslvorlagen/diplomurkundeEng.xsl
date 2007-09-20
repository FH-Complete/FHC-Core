<?xml version="1.0" encoding="ISO-8859-15"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="abschlusspruefung">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="297mm" page-width="210mm" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="pruefung"/>
		</fo:root>
	</xsl:template>
	
        <xsl:template match="pruefung">					
		<fo:page-sequence master-reference="PageMaster">
					
                        <fo:flow flow-name="xsl-region-body" >


<fo:block-container position="absolute" top="42mm" left="25mm" height="20mm">
	<fo:block text-align="center" line-height="20pt" font-family="sans-serif" font-size="18pt">
		<xsl:text>DIPLOMA</xsl:text>
	</fo:block>
</fo:block-container> 

<fo:block-container position="absolute" top="56mm" left="25mm" height="20mm">
	<fo:block text-align="center" line-height="20pt" font-family="sans-serif" font-size="12pt">
	<xsl:text>The Fachhochschulkollegium awards</xsl:text>
	</fo:block>
</fo:block-container> 

<fo:block-container position="absolute" top="70mm" left="25mm" height="10mm">
	<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="14pt">
	<xsl:value-of select="anrede_engl" /><xsl:text>   </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="vornamen" /><xsl:text> </xsl:text><xsl:value-of select="nachname" />
	</fo:block>
</fo:block-container> 


<fo:block-container position="absolute" top="80mm" left="25mm" height="10mm">
<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">
born <xsl:value-of select="gebdatum" /> in <xsl:value-of select="gebort"  />,<xsl:text> </xsl:text><xsl:value-of select="geburtsnation_engl"  />,
\ncitizen of <xsl:value-of select="staatsbuergerschaft_engl" />,
\nstudent of the FH diploma degree programme
\nprogramme classification number <xsl:value-of select="studiengang_kz" />, 
							   
                </fo:block>
</fo:block-container> 


<fo:block-container position="absolute" top="108mm" left="25mm" height="10mm">
<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="14pt">
	"<xsl:value-of select="stg_bezeichnung" />"
	</fo:block>
</fo:block-container> 

<fo:block-container position="absolute" top="120mm" left="25mm" height="10mm">
	<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">	
							   who successfully passed the diploma examination on <xsl:value-of select="datum" />
							   \nat the "Fachhochschule Technikum Wien"
              </fo:block>
</fo:block-container> 



<fo:block-container position="absolute" top="145mm" left="25mm" height="10mm">
		<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">	
							   pursuant to the paragraph 5 subsection 1 of the Fachhochschule Studies Act
							   \n(Austrian legal reference: Fachhochschul-Studiengesetz  FHStG,
							   \nBGBl. Nr. <xsl:value-of select="bescheidbgbl1" />, idgF)
                         </fo:block>
</fo:block-container>


<fo:block-container position="absolute" top="165mm" left="25mm" height="10mm">
<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">	
							   and in accordance with the notice of 
							   \nthe Fachhochschulrat on <xsl:value-of select="titelbescheidvom" />, 
							   \nthe academic degree
         </fo:block>
</fo:block-container>



<fo:block-container position="absolute" top="185mm" left="25mm" height="10mm">
						<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="14pt">
	<xsl:value-of select="titel" />
						
		</fo:block>
</fo:block-container>




<fo:block-container position="absolute" top="195mm" left="25mm" height="10mm">
<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">	
							   abbreviation
							
       </fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="205mm" left="25mm" height="10mm">
						<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="14pt">
	<xsl:value-of select="akadgrad_kurzbz" />
						
	</fo:block>
</fo:block-container> 

<fo:block-container position="absolute" top="215mm" left="25mm" height="10mm">
	<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">
						     Vienna,<xsl:text> </xsl:text><xsl:value-of select="sponsion" />				
		</fo:block>
</fo:block-container> 


	<fo:block-container position="absolute" top="225mm" left="25mm" height="10mm">
						<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">	
							   On behalf of the Fachhochschulkollegium:
							   \nThe Rector
							
          </fo:block>
</fo:block-container>


<fo:block-container position="absolute" top="250mm" left="25mm" height="10mm">
	<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">	
							  <xsl:value-of select="rektor" />
							
       </fo:block>
</fo:block-container>


					
			</fo:flow>
	    </fo:page-sequence>
	
	</xsl:template>
	
</xsl:stylesheet >