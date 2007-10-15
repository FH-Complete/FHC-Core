<?xml version="1.0" encoding="ISO-8859-15"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="zeugnisse">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="297mm" page-width="210mm" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="zeugnis"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="zeugnis">					
			<fo:page-sequence master-reference="PageMaster">
				<fo:flow flow-name="xsl-region-body" >
	
					<fo:block-container position="absolute" top="25mm" left="30mm" height="20mm">
						<fo:block text-align="left" line-height="20pt" font-family="sans-serif" font-size="18pt">
							<fo:inline font-weight="900">
							<xsl:text>Zeugnis </xsl:text><xsl:value-of select="studiensemester" />
							<xsl:text>\n </xsl:text><xsl:value-of select="semester_bezeichnung" />
							</fo:inline>
						</fo:block>
					</fo:block-container> 
						
					<fo:block-container position="absolute" top="46mm" left="30mm" height="20mm">
						<fo:block text-align="left" line-height="20pt" font-family="sans-serif" font-size="18pt">
							<fo:inline font-weight="900">
							<xsl:value-of select="studiengang_art" />\n
							<xsl:value-of select="studiengang" />
							</fo:inline>
						</fo:block>
					</fo:block-container> 			
					


					<fo:block-container position="absolute" top="68mm" left="140mm">
						<fo:block line-height="14pt" font-family="sans-serif" font-size="10pt">
							<xsl:text>Personenkennzeichen: </xsl:text>
						</fo:block>
					</fo:block-container>
					<fo:block-container position="absolute" top="68mm" left="177mm">
						<fo:block content-width="80mm" line-height="14pt" font-family="sans-serif" font-size="10pt" font-weight="bold">
							<xsl:value-of select="matrikelnr" />
						</fo:block>
					</fo:block-container>
					<fo:block-container position="absolute" top="73mm" left="143mm">
						<fo:block line-height="14pt" font-family="sans-serif" font-size="10pt">
							<xsl:text>Kennzahl des Studiengangs: </xsl:text>
						</fo:block>
					</fo:block-container>
					<fo:block-container position="absolute" top="73mm" left="189mm">
						<fo:block content-width="80mm" line-height="14pt" font-family="sans-serif" font-size="10pt" font-weight="bold">
							<xsl:value-of select="studiengang_kz" />
						</fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="85mm" left="30mm" height="10mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="45mm"/>
						    <fo:table-column column-width="100mm"/>
								<fo:table-body>
						            <fo:table-row line-height="14pt">
										<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text>Vorname/Familienname: </xsl:text>
											</fo:block>
									</fo:table-cell>
									<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="12pt" content-width="100mm" text-align="left">
												<fo:inline font-weight="900"><xsl:value-of select="name" /></fo:inline>
											</fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row line-height="14pt">
										<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text>Geburtsdatum: </xsl:text>
											</fo:block>
									</fo:table-cell>
									<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> </xsl:text><xsl:value-of select="gebdatum" />
											</fo:block>
									</fo:table-cell>
								</fo:table-row>      
							</fo:table-body>
						</fo:table>
					</fo:block-container>
 
 					<fo:block-container position="absolute" top="101mm" left="30mm">
						<fo:table table-layout="fixed" border-collapse="separate" border-width="2pt" border-style="solid">
						<fo:table-column column-width="85mm"/>
						<fo:table-column column-width="30mm"/>
						<fo:table-column column-width="25mm"/>
						<fo:table-column column-width="25mm"/>
						

							<fo:table-body>
								<fo:table-row  line-height="14pt">
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="14pt" font-weight="bold">
										 Lehrveranstaltung
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="14pt" font-weight="bold" content-width="30mm" text-align="center">
											 Note
										</fo:block>
									</fo:table-cell>
									
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="14pt" font-weight="bold" content-width="25mm" text-align="center">Anzahl\n SWS</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="14pt" font-weight="bold" content-width="25mm" text-align="center">
										ECTS\n Punkte
										</fo:block>
									</fo:table-cell>	
								</fo:table-row>
								
								<xsl:apply-templates select="unterrichtsfach"/>
								<fo:table-row line-height="0pt">
									<fo:table-cell border-width="0.2mm" border-style="solid" ></fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid" ></fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid" ></fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid" ></fo:table-cell>
								</fo:table-row>
								<xsl:apply-templates select="fussnote"/>
								    
							</fo:table-body>
						</fo:table>
						<fo:table>
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="155mm"/>
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell>
									<fo:block font-size="7pt">Notenstufen: </fo:block>
									</fo:table-cell>
									<fo:table-cell>
									<fo:block font-size="7pt">Sehr gut (1), Gut (2), Befriedigend (3), Gen�gend (4), Nicht gen�gend (5), angerechnet (ar), nicht beurteilt (nb),</fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell>
									<fo:block font-size="7pt"></fo:block>
									</fo:table-cell>
									<fo:table-cell>
									<fo:block font-size="7pt">teilgenommen (tg), bestanden (b), approbiert (ap), erfolgreich absolviert (ea), nicht erfolgreich absolviert (nea)</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="250mm" left="30mm" height="10mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="75mm"/>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="75mm"/>	

							<fo:table-body>
								
								<fo:table-row  line-height="12pt">
									<fo:table-cell>
										<fo:block font-size="8pt" content-width="75mm" text-align="center">
										</fo:block></fo:table-cell>
									<fo:table-cell>
										<fo:block>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-size="10pt" content-width="75mm" text-align="center">
										<xsl:value-of select="ort_datum" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row  line-height="8pt">
									<fo:table-cell>
										<fo:block font-size="8pt" font-weight="bold" content-width="75mm" text-align="center">
											______________________________________
										</fo:block>
									</fo:table-cell>
									<fo:table-cell><fo:block></fo:block></fo:table-cell>
									<fo:table-cell>
										<fo:block font-size="8pt" font-weight="bold" content-width="75mm" text-align="center">
											______________________________________
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
								
								<fo:table-row  line-height="12pt">
									<fo:table-cell>
										<fo:block font-size="10pt" content-width="75mm" text-align="center">
											<xsl:value-of select="studiengangsleiter" />
											<xsl:text>\nStudiengangsleitung</xsl:text>
										</fo:block></fo:table-cell>
									<fo:table-cell>
										<fo:block>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-size="10pt" content-width="75mm" text-align="center">
										Ort, Datum
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
								
								<fo:table-row  line-height="10pt">
									<fo:table-cell number-columns-spanned="3">
										<fo:block font-size="10pt" content-width="165mm" text-align="center">
											<xsl:text>Fachhochschule Technikum Wien\nH�chst�dtplatz 5\nA-1200 Wien\nZVR-Nr.: 074476426\nDVR-Nr.:0928381</xsl:text>
										</fo:block></fo:table-cell>
								</fo:table-row>
								
								    
							</fo:table-body>
						</fo:table>
						
					</fo:block-container> 
 

 
 
			</fo:flow>
		</fo:page-sequence>
	
	</xsl:template>
	
	<xsl:template match="unterrichtsfach">
		<fo:table-row  line-height="16pt">
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="11pt" content-width="85mm"><xsl:text> </xsl:text><xsl:value-of select="bezeichnung" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="11pt" content-width="30mm" text-align="center"><xsl:text> </xsl:text><xsl:value-of select="note" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="11pt" content-width="25mm" text-align="center"><xsl:text> </xsl:text><xsl:value-of select="sws" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="11pt" content-width="25mm" text-align="center"><xsl:text> </xsl:text><xsl:value-of select="ects" /></fo:block></fo:table-cell>
		</fo:table-row>
	</xsl:template>
	
	<xsl:template match="fussnote">
		<fo:table-row line-height="10pt">
			<fo:table-cell border-width="0.2mm" border-style="solid" >
				<fo:block font-size="8pt" content-width="58mm">
					<xsl:value-of select="fussnotenzeichen" /><xsl:text> </xsl:text>
					<fo:inline font-weight="bold"><xsl:value-of select="themenbereich_bezeichnung" /><xsl:text> </xsl:text></fo:inline>
					<xsl:value-of select="themenbereich" /><xsl:text> </xsl:text>
					<fo:inline font-weight="bold"><xsl:value-of select="titel_bezeichnung" /><xsl:text> </xsl:text></fo:inline>
					<xsl:value-of select="titel" />
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="11pt" content-width="30mm" text-align="center"><xsl:text> </xsl:text><xsl:value-of select="note" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="11pt" content-width="25mm" text-align="center"><xsl:text> </xsl:text><xsl:value-of select="sws" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="11pt" content-width="25mm" text-align="center"><xsl:text> </xsl:text><xsl:value-of select="ects" /></fo:block></fo:table-cell>
		</fo:table-row>
	</xsl:template>
</xsl:stylesheet >