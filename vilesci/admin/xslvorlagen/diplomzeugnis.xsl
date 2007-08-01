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
	
				<fo:block-container position="absolute" top="100pt" left="443pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt"> 
				<fo:inline vertical-align="super">
												Personenkennzeichen
				</fo:inline>
				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="125pt" left="450pt" height="0pt">
				<fo:block line-height="12pt" font-family="sans-serif" font-size="12pt" content-width="40mm" text-align="center"> 

						<xsl:value-of select="matrikelnr" />
				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="150pt" left="443pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt"> 
				<fo:inline vertical-align="super">
												Studiengangskennzahl
				</fo:inline>
				</fo:block>
				</fo:block-container>


				<fo:block-container position="absolute" top="120pt" left="445pt" height="0pt">
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


				<fo:block-container position="absolute" top="165pt" left="445pt" height="0pt">
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


				<fo:block-container position="absolute" top="170pt" left="450pt" height="0pt">
				<fo:block line-height="12pt" font-family="sans-serif" font-size="12pt"  content-width="45mm" text-align="center"> 

						<xsl:value-of select="studiengang_kz" />
				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="170pt" left="60pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="16pt">
						Diplomprüfungszeugnis
				</fo:block>
				</fo:block-container>


				<fo:block-container position="absolute" top="210pt" left="60pt" height="0pt">
				<fo:table table-layout="fixed" border-collapse="separate">
				<fo:table-column column-width="120.3mm"/>
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

				<fo:block-container position="absolute" top="210pt" left="62pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						Vorname(n), Familienname
				        <fo:inline font-size="12pt">
						\n <xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="nachname" />	
				        </fo:inline>



				</fo:block>
				</fo:block-container>


				<fo:block-container position="absolute" top="210pt" left="402pt" height="0pt">
				<fo:table table-layout="fixed" border-collapse="separate">
				<fo:table-column column-width="60.3mm"/>
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

				<fo:block-container position="absolute" top="210pt" left="402pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						Geburtsdatum

				   <fo:inline font-size="12pt">
						\n <xsl:value-of select="gebdatum" />			
				        </fo:inline>

				</fo:block>
				</fo:block-container>




				<fo:block-container position="absolute" top="241pt" left="60pt" height="0pt">
				<fo:table table-layout="fixed" border-collapse="separate">
				<fo:table-column column-width="181mm"/>
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

				<fo:block-container position="absolute" top="243pt" left="62pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						Diplom-Studiengang

				                 <fo:inline font-size="12pt" font-weight="900" text-align="center" >                    \n
				                    \n
						     \n <xsl:value-of select="stg_bezeichnung" />				
				                  </fo:inline>

				</fo:block>
				</fo:block-container>


				<fo:block-container position="absolute" top="322pt" left="60pt" height="0pt">
				<fo:table table-layout="fixed" border-collapse="separate">
				<fo:table-column column-width="181mm"/>
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

				<fo:block-container position="absolute" top="323pt" left="62pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
				Gesetzliche Grundlage: gem. § 5 Abs. 1 des Bundesgesetzes über Fachhochschul-Studiengänge (FHStG), BGBl.Nr. <xsl:value-of select="bescheidbgbl1" />
				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="353pt" left="60pt" height="0pt">
				<fo:table table-layout="fixed" border-collapse="separate">
				<fo:table-column column-width="181mm"/>
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

				<fo:block-container position="absolute" top="354pt" left="62pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						Gesamtnote der Diplomprüfung

				                 <fo:inline font-size="12pt" font-weight="900" text-align="center" >                    \n
				                 
						     \n <xsl:value-of select="abschlussbeurteilung_kurzbz" />			
				                  </fo:inline>

				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="460pt" left="60pt" height="0pt">
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

				<fo:block-container position="absolute" top="460pt" left="62pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						Diplomprüfung

				</fo:block>
				</fo:block-container>


				<fo:block-container position="absolute" top="460pt" left="430pt" height="0pt">
				<fo:table table-layout="fixed" border-collapse="separate">
				<fo:table-column column-width="51mm"/>
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

				<fo:block-container position="absolute" top="460pt" left="432pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						Datum

				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="475pt" left="60pt" height="0pt">
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

				<fo:block-container position="absolute" top="475pt" left="62pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						1. Teil der Diplomprüfung: Diplomarbeit

				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="475pt" left="430pt" height="0pt">
				<fo:table table-layout="fixed" border-collapse="separate">
				<fo:table-column column-width="51mm"/>
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

				<fo:block-container position="absolute" top="475pt" left="432pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						<xsl:value-of select="datum_projekt" />

				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="490pt" left="60pt" height="0pt">
				<fo:table table-layout="fixed" border-collapse="separate">
				<fo:table-column column-width="181.5mm"/>
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

				<fo:block-container position="absolute" top="491pt" left="62pt" height="1pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						Thema der Diplomarbeit

				                 <fo:inline font-size="12pt" font-weight="900" text-align="center" >                    \n
						     \n <xsl:value-of select="themenbereich" />
				                  </fo:inline>

				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="571pt" left="60pt" height="0pt">
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

				<fo:block-container position="absolute" top="571pt" left="62pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						2. Teil der Diplomprüfung: Kommissionelle Prüfung

				</fo:block>
				</fo:block-container>


				<fo:block-container position="absolute" top="571pt" left="430pt" height="0pt">
				<fo:table table-layout="fixed" border-collapse="separate">
				<fo:table-column column-width="51mm"/>
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

				<fo:block-container position="absolute" top="571pt" left="432pt" height="0pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						<xsl:value-of select="datum" />

				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="586pt" left="234pt" height="0pt">
				<fo:table table-layout="fixed" border-collapse="separate">
				<fo:table-column column-width="120.1mm"/>
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

				<fo:block-container position="absolute" top="586pt" left="236pt" height="1pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						Vorsitzender des Prüfungssenats
				                 \n
				                 \n
				                 \n
				                <xsl:value-of select="vorsitz_nachname" />
				 
				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="586pt" left="60pt" height="0pt">
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

				<fo:block-container position="absolute" top="586pt" left="62pt" height="1pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
						Ort, Ausstellungsdatum
				                 \n
				                 \n
				                 \n
				                Wien, <xsl:value-of select="datum_aktuell" />
				 
				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="650pt" left="62pt" height="1pt">
				<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="6pt">
					
				Gesamtnote: mit ausgezeichnetem Erfolg bestanden, mit gutem Erfolg bestanden, bestanden \n ZVR-Nr.: 074476426, DVR-Nr.: 0928381
				 
				 
				</fo:block>
				</fo:block-container>





 
			</fo:flow>
		</fo:page-sequence>
	
	</xsl:template>
	
</xsl:stylesheet >