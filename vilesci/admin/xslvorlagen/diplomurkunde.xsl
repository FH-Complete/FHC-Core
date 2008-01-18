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
		<xsl:text>DIPLOMURKUNDE</xsl:text>
	</fo:block>
</fo:block-container> 

<fo:block-container position="absolute" top="56mm" left="25mm" height="20mm">
	<fo:block text-align="center" line-height="20pt" font-family="sans-serif" font-size="12pt">
	<xsl:text>Das Fachhochschulkollegium verleiht</xsl:text>
	</fo:block>
</fo:block-container> 

<fo:block-container position="absolute" top="70mm" left="25mm" height="10mm">
	<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="14pt">
	<xsl:value-of select="anrede" /><xsl:text>   </xsl:text><xsl:value-of select="titelpre" /><xsl:text>   </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="vornamen" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text>   </xsl:text><xsl:value-of select="titelpost" />
	</fo:block>
</fo:block-container> 


<fo:block-container position="absolute" top="80mm" left="25mm" height="10mm">
<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">
geboren am <xsl:value-of select="gebdatum" /> in <xsl:value-of select="gebort"  />,<xsl:text> </xsl:text><xsl:value-of select="geburtsnation"  />,
\nStaatsbürgerschaft <xsl:value-of select="staatsbuergerschaft" />,
\ndie/der den Fachhochschul-<xsl:value-of select="stg_art" />,
\nStudiengangskennzahl <xsl:value-of select="studiengang_kz" />, 
							   
                </fo:block>
</fo:block-container> 


<fo:block-container position="absolute" top="108mm" left="25mm" height="10mm">
<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="14pt">
	"<xsl:value-of select="stg_bezeichnung" />"
	</fo:block>
</fo:block-container> 

<fo:block-container position="absolute" top="120mm" left="25mm" height="10mm">
	<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">	
							   an der "Fachhochschule Technikum Wien"
							   \ndurch Ablegung der Diplomprüfung am <xsl:value-of select="datum" />
							   \nordnungsgemäß abgeschlossen hat,
              </fo:block>
</fo:block-container> 



<fo:block-container position="absolute" top="145mm" left="25mm" height="10mm">
		<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">	
							   gemäß § 5 Abs. 1 des Bundesgesetzes
							   \nüber Fachhochschul-Studiengänge (Fachhochschul-Studiengesetz - FHStG),
							   \nBGBl.Nr. <xsl:value-of select="bescheidbgbl1" />, idgF,
 							   \nden mit Bescheid des Fachhochschulrates vom <xsl:value-of select="titelbescheidvom" />,
							   \ngemäß § 5 Abs. 2 FHStG festgesetzten

                         </fo:block>
</fo:block-container>


<fo:block-container position="absolute" top="175mm" left="25mm" height="10mm">
<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">	
							   akademischen Grad
							
         </fo:block>
</fo:block-container>



<fo:block-container position="absolute" top="185mm" left="25mm" height="10mm">
						<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="14pt">
	<xsl:value-of select="titel" />
						
		</fo:block>
</fo:block-container>




<fo:block-container position="absolute" top="195mm" left="25mm" height="10mm">
<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">	
							   abgekürzt
							
       </fo:block>
</fo:block-container>

<fo:block-container position="absolute" top="205mm" left="25mm" height="10mm">
						<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="14pt">
	<xsl:value-of select="akadgrad_kurzbz" />
						
	</fo:block>
</fo:block-container> 

<fo:block-container position="absolute" top="215mm" left="25mm" height="10mm">
	<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">
						     Wien,<xsl:text> </xsl:text><xsl:value-of select="sponsion" />				
		</fo:block>
</fo:block-container> 


	<fo:block-container position="absolute" top="225mm" left="25mm" height="10mm">
						<fo:block text-align="center" line-height="14pt" font-family="sans-serif" font-size="12pt">	
							   Für das Fachhochschulkollegium:
							   \nDer Rektor
							
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