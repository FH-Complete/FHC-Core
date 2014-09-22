<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="zertifikate">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="297mm" page-width="210mm" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="zertifikat"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="zertifikat">					
			<fo:page-sequence master-reference="PageMaster">
				<fo:flow flow-name="xsl-region-body" >
	
					<fo:block-container position="absolute" top="55mm" left="23mm" height="20mm">
						<fo:block text-align="left" line-height="16pt" font-family="sans-serif" font-size="16pt" font-weight="bold">
							<fo:inline font-weight="900">
							<xsl:text>ZERTIFIKAT</xsl:text>
							</fo:inline>
						</fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="62mm" left="23mm">
						<fo:block line-height="16pt" font-family="sans-serif" font-size="16pt">
							<fo:inline font-weight="900">
							<xsl:text>Freifächer</xsl:text>
							</fo:inline>
						</fo:block>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="97mm" left="140mm">
						<fo:block content-width="70mm" line-height="10pt" font-family="sans-serif" font-size="8pt">
							<xsl:text>Personenkennzeichen: </xsl:text><xsl:value-of select="matrikelnr" />
						</fo:block>
					</fo:block-container>	
					
					<fo:block-container position="absolute" top="125mm" left="24mm">
						<fo:block content-width="150mm" line-height="20pt" font-family="sans-serif" font-size="10pt">
							<xsl:text>Vorname/Familienname: </xsl:text><xsl:value-of select="name" />
						</fo:block>
					</fo:block-container>		
					
					<fo:block-container position="absolute" top="135mm" left="24mm">
						<fo:block content-width="150mm" line-height="20pt" font-family="sans-serif" font-size="10pt">
							<xsl:text>Geburtsdatum: </xsl:text><xsl:value-of select="gebdatum" />
						</fo:block>
					</fo:block-container>		
										
					<fo:block-container position="absolute" top="147mm" left="24mm">
						<fo:block content-width="160mm" line-height="12pt" font-family="sans-serif" font-size="10pt">
							<xsl:text> Herr/Frau </xsl:text>
							<xsl:value-of select="name" />
							<xsl:text> hat im </xsl:text>
							<xsl:value-of select="studiensemester" />
							<xsl:text> das folgende Freifach an der FH Technikum Wien belegt:</xsl:text>
						</fo:block>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="162mm" left="24mm">
							<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2pt" border-style="solid">
								<fo:table-column column-width="0mm"/>
								<fo:table-column column-width="80mm"/>
								<fo:table-column column-width="38mm"/>
								<fo:table-column column-width="16mm"/>
								<fo:table-column column-width="24mm"/>
								<fo:table-body>
									<fo:table-row line-height="19pt">
										<fo:table-cell border-width="0mm" >
										<fo:block font-size="9pt" font-weight="bold" >
												<!-- wenn die erste Spalte eine Hintergrundfarbe hat, dann wird der Text von der Hintergrundfarbe ueberschrieben.
												     Deshalb gibt es hier eine Dummy-Spalte. Ab der zweiten Spalte funktioniert es dann problemlos
												     grauslich, funktioniert aber...
												-->
												</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center"  background-color="#afb8bc" >
											<fo:block font-size="9pt" font-weight="bold" content-width="80mm" vertical-align="center" font-family="arial">
												Lehrveranstaltung
												</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center" background-color="#afb8bc">
											<fo:block font-size="9pt" font-weight="bold" content-width="38mm" text-align="center"  vertical-align="center" font-family="arial">
												Note
												</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center" background-color="#afb8bc">
											<fo:block font-size="9pt" font-weight="bold" content-width="16mm" text-align="center"  vertical-align="center" font-family="arial">
												SWS
												</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center" background-color="#afb8bc">
											<fo:block font-size="9pt" font-weight="bold" content-width="24mm" text-align="center"  vertical-align="center" font-family="arial">
												<xsl:text>ECTS credits</xsl:text>
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
									<fo:table-row line-height="12pt">
									<fo:table-cell border-width="0mm">
										<!-- Dummy Zelle -->
									</fo:table-cell>
									<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
										<fo:block font-size="9pt" content-width="81mm" vertical-align="center" font-family="arial">
											<xsl:text> </xsl:text>
											<xsl:value-of select="bezeichnung"/>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
										<fo:block font-size="9pt" content-width="38mm" text-align="center" vertical-align="center" font-family="arial">
											<xsl:text> </xsl:text>
											<xsl:choose>
												<xsl:when test="note='tg'">
													<xsl:text>teilgenommen</xsl:text>
												</xsl:when>
												<xsl:when test="note='met'">
													<xsl:text>mit Erfolg teilgenommen</xsl:text>
												</xsl:when>
												<xsl:when test="note='b'">
													<xsl:text>bestanden</xsl:text>
												</xsl:when>
												<xsl:otherwise>
													<xsl:value-of select="note"/>
												</xsl:otherwise>
											</xsl:choose>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
										<fo:block font-size="9pt" content-width="16mm" text-align="center" vertical-align="center" font-family="arial">
											<xsl:text> </xsl:text>
											<xsl:value-of select="sws"/>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
										<fo:block font-size="9pt" content-width="24mm" text-align="center" vertical-align="center" font-family="arial">
											<xsl:text> </xsl:text>
											<xsl:value-of select="ects"/>
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
									</fo:table-body>
							</fo:table>
					</fo:block-container>
					
					<xsl:if test="lehrinhalte!=''">
					<fo:block-container position="absolute" top="180mm" left="24mm">
						<fo:block content-width="180mm" line-height="14pt" font-family="sans-serif" font-size="10pt">
							<xsl:text>Inhalte der Lehrveranstaltung:</xsl:text>
						</fo:block>
					</fo:block-container>	

					<fo:block-container position="absolute" top="192mm" left="24mm">
						<fo:block content-width="160mm" line-height="12pt" font-family="sans-serif" font-size="10pt">
							<xsl:value-of select="lehrinhalte" />
						</fo:block>
					</fo:block-container>
					</xsl:if>
					
					
					<fo:block-container position="absolute" top="250mm" left="24mm" height="10mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="75mm"/>
						<fo:table-column column-width="14mm"/>
						<fo:table-column column-width="75mm"/>	

							<fo:table-body>
								
								<fo:table-row  line-height="12pt">
									<fo:table-cell>
										<fo:block font-size="10pt" content-width="75mm">
										<xsl:value-of select="ort_datum" />
										</fo:block></fo:table-cell>
									<fo:table-cell>
										<fo:block>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-size="10pt" content-width="75mm">
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row  line-height="8pt">
									<fo:table-cell>
										<fo:block font-size="8pt" font-weight="bold" content-width="75mm">
											<xsl:text>_____________________________________________</xsl:text>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell><fo:block></fo:block></fo:table-cell>
									<fo:table-cell>
										<fo:block font-size="8pt" font-weight="bold" content-width="75mm">
											<xsl:text>_____________________________________________</xsl:text>
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
								
								<fo:table-row  line-height="12pt">
									<fo:table-cell>
										<fo:block font-size="10pt" content-width="75mm">
										<xsl:text>Ort, Datum</xsl:text>	
										</fo:block></fo:table-cell>
									<fo:table-cell>
										<fo:block>										
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-size="10pt" content-width="75mm">
										<xsl:value-of select="lvleiter" />
											<xsl:text>, LeiterIn Freifach</xsl:text>
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