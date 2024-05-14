<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="abschlusspruefung">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master orientation="l" format="A4" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="pruefung"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="pruefung">
			<fo:page-sequence master-reference="PageMaster">
				<fo:flow flow-name="xsl-region-body" >
	
					<fo:block-container position="absolute" top="25mm" left="15mm">
						<fo:block text-align="left" line-height="20pt" font-family="sans-serif" font-size="16pt">
							Protokoll Bachelor-Prüfung
						</fo:block>
					</fo:block-container> 
					
					<!--FINANZAMT-->
					<fo:block-container position="absolute" top="35mm" left="15mm">
					</fo:block-container>
					
					<!-- LOGO und DATUM -->
					<fo:block-container position="absolute" top="40mm" left="260mm">
						<fo:block text-align="left" line-height="10pt" content-width="50mm" font-family="sans-serif" font-size="8pt">
							Datum: <xsl:value-of select="datum" />
						</fo:block>
					</fo:block-container>
					
					<fo:block>
						<fo:external-graphic src="../skin/images/logo.jpg"  posx="260" posy="30" width="30mm" height="10mm" />
					</fo:block>
				
					<!-- NAME - MATRIKELNUMMER -->
					<fo:block-container position="absolute" top="45mm" left="15mm" height="10mm">
						<fo:block text-align="left" line-height="14pt" font-family="sans-serif" font-size="12pt" font-weight="bold">
							<xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" />
						</fo:block>
					</fo:block-container>
									
					<fo:block-container position="absolute" top="50mm" left="15mm" height="20mm">
						<fo:block text-align="left" line-height="14pt" font-family="sans-serif" font-size="12pt">
							Personenkennzeichen: <xsl:value-of select="matrikelnr" />
						</fo:block>
					</fo:block-container>
										
					<fo:block-container position="absolute" top="60mm" left="15mm" height="10mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="261.6mm"/>				
								<fo:table-body>
						            <fo:table-row line-height="14pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left" font-weight="bold">
												<xsl:text> Prüfungssenat:</xsl:text>
											</fo:block>
										</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="80mm"/>
							<fo:table-column column-width="181.2mm"/>
								<fo:table-body>
						            <fo:table-row line-height="12pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> BA-Betreuer</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Bachelorarbeiten</xsl:text>
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
									<fo:table-row line-height="12pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="80mm" text-align="left">
												<xsl:text> </xsl:text><xsl:value-of select="betreuer" />\n <xsl:value-of select="betreuer_2" />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="181.2mm" text-align="left">
												<xsl:text> </xsl:text><xsl:value-of select="themenbereich" />\n <xsl:value-of select="themenbereich_2" />
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
							</fo:table-body>
						</fo:table>
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="80mm"/>
							<fo:table-column column-width="100mm"/>
							<fo:table-column column-width="26mm"/>
							<fo:table-column column-width="26mm"/>
							<fo:table-column column-width="28mm"/>
								<fo:table-body>
						            <fo:table-row line-height="12pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Prüfer</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Prüfungsfrage</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Beginn</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Ende</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Note</xsl:text>
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
									<fo:table-row line-height="12pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="80mm" text-align="left">
												<xsl:text> </xsl:text><xsl:value-of select="pruefer1_nachname" />\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
									<fo:table-row line-height="12pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="80mm" text-align="left">
												<xsl:text> </xsl:text><xsl:value-of select="pruefer2_nachname" />\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
									<fo:table-row line-height="12pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="80mm" text-align="left">
												<xsl:text> </xsl:text><xsl:value-of select="pruefer3_nachname" />\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
									<fo:table-row line-height="12pt">
									<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
					<fo:block-container position="absolute" top="140mm" left="15mm">
						<fo:block text-align="left" line-height="10pt" font-family="sans-serif" font-size="10pt">
							Gesamtbeurteilung: _______________________________________________________
						</fo:block>
					</fo:block-container>
					<fo:block-container position="absolute" top="145mm" left="15mm" height="20mm">
						<fo:block text-align="left" line-height="10pt" font-family="sans-serif" font-size="8pt">
							(mit ausgezeichnetem Erfolg bestanden, mit gutem Erfolg bestanden, bestanden, nicht bestanden)
						</fo:block>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="160mm" left="15mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="65mm"/>
							<fo:table-column column-width="65mm"/>
							<fo:table-column column-width="65mm"/>
							<fo:table-column column-width="65mm"/>
								<fo:table-body>
						            <fo:table-row line-height="14pt">
										<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
												______________________________
											</fo:block>
										</fo:table-cell>
										<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
												______________________________
											</fo:block>
										</fo:table-cell>
										<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
												______________________________
											</fo:block>
										</fo:table-cell>
										<fo:table-cell>
											<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
												______________________________
											</fo:block>
										</fo:table-cell>
								</fo:table-row>
					            <fo:table-row line-height="14pt">
									<fo:table-cell>
										<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
											<xsl:value-of select="vorsitz_nachname" />\n
											(Vorsitz)
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
											
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
											Prüfungssenat
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
											
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
			</fo:flow>
		</fo:page-sequence>
	
	</xsl:template>
	
</xsl:stylesheet >