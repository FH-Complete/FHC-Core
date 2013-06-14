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
							<xsl:text>Proceedings of Master's Examination</xsl:text>
						</fo:block>
					</fo:block-container>

					<fo:block-container position="absolute" top="20mm" left="15mm">
						<fo:block text-align="left" line-height="10pt" font-family="sans-serif" font-size="8pt">
							of <xsl:value-of select="stg_bezeichnung_engl" /> held in accordance with the Austrian University of Applied Sciences Act (FhStg <xsl:value-of select="bescheidbgbl1" />, <xsl:value-of select="bescheidbgbl2" /> ) \n
							and the Accreditation Act of the Austrian Council of Universities of Applied Sciences No. <xsl:value-of select="bescheidgz" /> dated <xsl:value-of select="bescheidvom" />.
						</fo:block>
					</fo:block-container>
					
					<!-- LOGO und DATUM -->
					<fo:block-container position="absolute" top="25mm" left="260mm">
						<fo:block text-align="left" line-height="10pt" content-width="50mm" font-family="sans-serif" font-size="8pt">
							Date: <xsl:value-of select="datum" />
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
							<xsl:text>Student ID: </xsl:text><xsl:value-of select="matrikelnr" />
						</fo:block>
					</fo:block-container>
										
					<fo:block-container position="absolute" top="40mm" left="15mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="261.6mm"/>				
								<fo:table-body>
						            <fo:table-row line-height="10pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="200mm" text-align="left" font-weight="bold">
												<xsl:text> Panel of Assessors:</xsl:text>
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
												<xsl:text> Part 1</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-top-width="0.2mm" border-top-style="solid" border-top-color="black" border-left-width="0.2mm" border-left-style="solid" border-left-color="black" border-right-width="0.2mm" border-right-style="solid" border-right-color="black" border-bottom-width="0.2mm" border-bottom-style="solid" border-bottom-color="white">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> Grade of</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-right-width="0.2mm" border-right-style="solid" border-right-color="black" border-top-width="0.2mm" border-top-style="solid" border-top-color="black">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="38.4mm" text-align="left">
												<xsl:text> Grade of Presentation</xsl:text>
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
												<xsl:text> Assessor</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Title of Master Thesis</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid" border-color="white" border-left-style="solid" border-left-color="black" border-right-width="0.2mm" border-right-style="solid" border-right-color="black">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> Thesis (FYI)</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-right-width="0.2mm" border-right-style="solid" border-right-color="black" border-top-width="0.2mm" border-top-style="solid" border-top-color="white">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="36.4mm" text-align="left">
												<xsl:text> and Defence</xsl:text>
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
												<xsl:text> Part 2</xsl:text>
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
												<xsl:text> Assessor</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Problem</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> Start</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> End</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="36mm" text-align="left">
												<xsl:text> Grade</xsl:text>
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
												<xsl:text> Part 3</xsl:text>
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
												<xsl:text> Assessor</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="left">
												<xsl:text> Problem</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> Start</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="24mm" text-align="left">
												<xsl:text> End</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="10pt" content-width="36mm" text-align="left">
												<xsl:text> Grade</xsl:text>
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
							The Master's Examination is held in front of a panel of expert assessors and consists of the presentation and defence of the Master's Thesis (Part 1), an examination connecting the field of the Master's Thesis with relevant courses of the curriculum (Part 2), and an examination covering other relevant fields of the curriculum (Part 3).
						</fo:block>
					</fo:block-container>
										
					
					<fo:block-container position="absolute" top="135mm" left="15mm">
						<fo:block text-align="left" line-height="10pt" font-family="sans-serif" font-size="10pt">
							Final assessment: _______________________________________________________
						</fo:block>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="140mm" left="15mm" height="20mm">
						<fo:block text-align="left" line-height="10pt" font-family="sans-serif" font-size="8pt">
							(Passed with highest distinction - Passed with distinction - Passed - Standard not attained (Failed))
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
											(Chair)
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
											
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-family="sans-serif" font-size="10pt" content-width="65mm" text-align="center" font-weight="bold">
											Assessors
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