<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="abschlusspruefung">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master orientation="l" format="A4" margin="0mm 5mm 0mm 5mm" master-name="PageMaster">
					<fo:region-body margin="0mm 0mm 0mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="pruefung"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="pruefung">
			<fo:page-sequence master-reference="PageMaster">
				<fo:flow flow-name="xsl-region-body" >
	
					<fo:block-container position="absolute" top="10mm" left="15mm" height="20mm">
						<fo:block text-align="left" line-height="20pt" font-family="sans-serif" font-size="16pt">
							<xsl:text>Protokoll Master-Prüfung</xsl:text>
						</fo:block>
					</fo:block-container>

					<fo:block-container position="absolute" top="20mm" left="15mm">
						<fo:block text-align="left" line-height="10pt" font-family="sans-serif" font-size="8pt">
							abgehalten an dem Fachhochschul-Studiengang <xsl:value-of select="stg_bezeichnung" /> gemäß (FhStG), BGBl <xsl:value-of select="bescheidbgbl1" /> idgF BGBl. <xsl:value-of select="bescheidbgbl2" /> und dem mit Bescheid\n 
							des Fachhochschulrates GZ: <xsl:value-of select="bescheidgz" /> vom <xsl:value-of select="bescheidvom" /> genehmigten Antrag.
						</fo:block>
					</fo:block-container>
					
					<!-- LOGO und DATUM -->
					<fo:block-container position="absolute" top="25mm" left="260mm">
						<fo:block text-align="left" line-height="10pt" content-width="50mm" font-family="sans-serif" font-size="8pt">
							Datum: <xsl:value-of select="datum" />
						</fo:block>
					</fo:block-container>
					
					<fo:block>
						<fo:external-graphic src="../skin/images/logo.jpg"  posx="260" posy="10" width="30mm" height="10mm" />
					</fo:block>
				
					<!-- NAME - MATRIKELNUMMER -->
					<fo:block-container position="absolute" top="30mm" left="15mm" height="10mm">
						<fo:block text-align="left" line-height="14pt" font-family="sans-serif" font-size="12pt" font-weight="bold">
							<xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" />
						</fo:block>
					</fo:block-container>
									
					<fo:block-container position="absolute" top="34mm" left="15mm" height="20mm">
						<fo:block text-align="left" line-height="14pt" font-family="sans-serif" font-size="12pt">
							<xsl:text>Personenkennzeichen: </xsl:text><xsl:value-of select="matrikelnr" />
						</fo:block>
					</fo:block-container>
										
					<fo:block-container position="absolute" top="40mm" left="15mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="261.6mm"/>				
								<fo:table-body>
						            <fo:table-row line-height="10pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="200mm" text-align="left" font-weight="bold">
												<xsl:text> Prüfungssenat:</xsl:text>
											</fo:block>
										</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
						<fo:table table-layout="fixed" border-collapse="separate">
						    	<fo:table-column column-width="200.3mm"/>	
							<fo:table-column column-width="24mm"/>
							<fo:table-column column-width="36.8mm"/>
								<fo:table-body>
						            <fo:table-row line-height="10pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="200mm" text-align="left" font-weight="bold">
												<xsl:text> 1. Prüfungsteil</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-top-width="0.2mm" border-top-style="solid" border-top-color="black" border-left-width="0.2mm" border-left-style="solid" border-left-color="black" border-right-width="0.2mm" border-right-style="solid" border-right-color="black" border-bottom-width="0.2mm" border-bottom-style="solid" border-bottom-color="white">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> Note MT</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-right-width="0.2mm" border-right-style="solid" border-right-color="black" border-top-width="0.2mm" border-top-style="solid" border-top-color="black">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="36.4mm" text-align="left">
												<xsl:text> Note Präsentation</xsl:text>
											</fo:block>
										</fo:table-cell>
								</fo:table-row>
								
							</fo:table-body>
						</fo:table>
						<fo:table table-layout="fixed" border-collapse="separate">
						    	<fo:table-column column-width="80mm"/>
							<fo:table-column column-width="120mm"/>
							<fo:table-column column-width="24mm"/>
							<fo:table-column column-width="36.6mm"/>
								<fo:table-body>
						            <fo:table-row line-height="10pt">
										<fo:table-cell border-width="0.2mm" border-style="solid" border-color="black">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Prüfer/in</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Thema der Master Thesis</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid" border-color="white" border-left-style="solid" border-left-color="black" border-right-width="0.2mm" border-right-style="solid" border-right-color="black">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> (Information)</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-right-width="0.2mm" border-right-style="solid" border-right-color="black" border-top-width="0.2mm" border-top-style="solid" border-top-color="white">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="36.4mm" text-align="left">
												<xsl:text> und Diskussion MT</xsl:text>
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
							</fo:table-body>
						</fo:table>
						<fo:table table-layout="fixed" border-collapse="separate">
						    	<fo:table-column column-width="80mm"/>
							<fo:table-column column-width="120mm"/>
							<fo:table-column column-width="24mm"/>
							<fo:table-column column-width="36.4mm"/>
								<fo:table-body>
									<fo:table-row line-height="10pt">
										<fo:table-cell border-width="0.2mm" border-style="solid" border-color="black">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="80mm" text-align="left">
												<xsl:text> </xsl:text><xsl:value-of select="betreuer" />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="120mm" text-align="left">
												<xsl:text> </xsl:text><xsl:value-of select="themenbereich" />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="center">
												<xsl:text> </xsl:text><xsl:value-of select="note" />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="36.4mm" text-align="left">
												<xsl:text> </xsl:text>\n\n
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
							</fo:table-body>
						</fo:table>
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="261.6mm"/>				
								<fo:table-body>
						            <fo:table-row line-height="10pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="261.6mm" text-align="left" font-weight="bold">
												<xsl:text> 2. Prüfungsteil</xsl:text>
											</fo:block>
										</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
						<fo:table table-layout="fixed" border-collapse="separate">
						    	<fo:table-column column-width="80mm"/>
							<fo:table-column column-width="96mm"/>
							<fo:table-column column-width="24mm"/>
							<fo:table-column column-width="24mm"/>
							<fo:table-column column-width="36mm"/>
								<fo:table-body>
						            <fo:table-row line-height="10pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Prüfer/in</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Prüfungsfrage</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> Beginn</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> Ende</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="36mm" text-align="left">
												<xsl:text> Note</xsl:text>
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
									<fo:table-row line-height="10pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="80mm" text-align="left">
												<xsl:text> </xsl:text><xsl:value-of select="pruefer1_nachname" />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n\n\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="36mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
								</fo:table-body>
						</fo:table>
						<fo:table table-layout="fixed" border-collapse="separate">
							<fo:table-column column-width="261.6mm"/>				
								<fo:table-body>
						            	<fo:table-row line-height="10pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="261.6mm" text-align="left" font-weight="bold">
												<xsl:text> 3. Prüfungsteil</xsl:text>
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
								</fo:table-body>
						</fo:table>					
						<fo:table table-layout="fixed" border-collapse="separate">
						    	<fo:table-column column-width="80mm"/>
							<fo:table-column column-width="96mm"/>
							<fo:table-column column-width="24mm"/>
							<fo:table-column column-width="24mm"/>
							<fo:table-column column-width="36mm"/>
							<fo:table-body>
								<fo:table-row line-height="10pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Prüfer/in</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Prüfungsfrage</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> Beginn</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> Ende</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="36mm" text-align="left">
												<xsl:text> Note</xsl:text>
											</fo:block>
										</fo:table-cell>
								</fo:table-row>
								<fo:table-row line-height="10pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="80mm" text-align="left">
												<xsl:text> </xsl:text><xsl:value-of select="pruefer2_nachname" />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												\n\n\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="36mm" text-align="left">
												\n
											</fo:block>
										</fo:table-cell>
								</fo:table-row>

							</fo:table-body>
						</fo:table>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="115mm" left="15mm">
						<fo:block text-align="left" line-height="9pt" font-family="sans-serif" font-size="7pt">
							Die kommissionelle Master-Prüfung vor einem facheinschlägigen Prüfungssenat setzt sich aus der Präsentation der Master Thesis (1. Prüfungsteil), 
							einem Prüfungsgespräch, das auf die Querverbindungen des Themas der Master Thesis zu den relevanten Fächern des Studienplanes eingeht (2. Prüfungsteil) 
							sowie einem Prüfungsgespräch über sonstige curriculumrelevante Inhalte (3. Prüfungsteil) zusammen. [II.E.5.d., S.11]
						</fo:block>
					</fo:block-container>
										
					
					<fo:block-container position="absolute" top="135mm" left="15mm">
						<fo:block text-align="left" line-height="10pt" font-family="sans-serif" font-size="10pt">
							Gesamtbeurteilung: _______________________________________________________
						</fo:block>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="140mm" left="15mm" height="20mm">
						<fo:block text-align="left" line-height="10pt" font-family="sans-serif" font-size="8pt">
							(mit ausgezeichnetem Erfolg bestanden, mit gutem Erfolg bestanden, bestanden, nicht bestanden)
						</fo:block>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="175mm" left="15mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						    	<fo:table-column column-width="65mm"/>
							<fo:table-column column-width="65mm"/>
							<fo:table-column column-width="65mm"/>
							<fo:table-column column-width="65mm"/>
							<fo:table-body>
						      	<fo:table-row line-height="14pt">
									<fo:table-cell>
										<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
											____________________________
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
											____________________________
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
											____________________________
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
											____________________________
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
	
</xsl:stylesheet>


