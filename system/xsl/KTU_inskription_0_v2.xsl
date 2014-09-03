<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="studenten">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="297mm" page-width="210mm" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="student"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="student">					
			<fo:page-sequence master-reference="PageMaster">
				<fo:flow flow-name="xsl-region-body" >
	
					<fo:block-container position="absolute" top="40pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="14pt">
							Studienbestätigung Katholisch-Theologische Privatuniversität Linz
						</fo:block>
					</fo:block-container> 
									
					<fo:block-container position="absolute" top="60pt" left="30pt" height="20pt">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="140mm"/>
								<fo:table-body>
						            <fo:table-row line-height="30pt">
						                    <fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
											<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="6pt"> 
												<fo:inline vertical-align="super">
													Zur Vorlage an (Stelle an der die Bestätigung vorgelegt wird und deren Bezugszahl, z.B. Sozialversicherungsnr.)
												</fo:inline>
											</fo:block>
									</fo:table-cell>
								</fo:table-row>      
							</fo:table-body>
						</fo:table>
					</fo:block-container>

					<fo:block-container position="absolute" top="76pt" left="445pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt"> 
							<fo:inline vertical-align="super">
								SV-Nummer
							</fo:inline>
						</fo:block>
					</fo:block-container>

					<fo:block-container position="absolute" top="91pt" left="428pt" height="20pt">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="45mm"/>
								<fo:table-body>
						            <fo:table-row line-height="25pt">
										<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
										<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
											<xsl:value-of select="svnr" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>      
							</fo:table-body>
						</fo:table>
					</fo:block-container> 


					<fo:block-container position="absolute" top="96pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="10pt">
							<xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="vornamen" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" />
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="120pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							geboren am<xsl:text> </xsl:text><xsl:value-of select="geburtsdatum" /><xsl:text> </xsl:text>
							ist im<xsl:text> </xsl:text><xsl:value-of select="studiensemester_aktuell" /><xsl:text> </xsl:text>
							als ordentliche(r) Studierende(r) \n
							der Studienrichtung<xsl:text> </xsl:text><xsl:value-of select="studiengang_bezeichnung" /> im
							<xsl:text> </xsl:text><xsl:value-of select="semester" />. Semester gemeldet.
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="150pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							Datum:<xsl:text> </xsl:text><xsl:value-of select="tagesdatum" /><xsl:text> </xsl:text>DVR: 0029874(1739)
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="150pt" left="300pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							Für das Rektorat: <xsl:text> </xsl:text><xsl:value-of select="rektor" />
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="170pt" left="50pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                        </fo:block>
					</fo:block-container> 
<!-- Abschnitt 2  -->

					<fo:block-container position="absolute" top="190pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="14pt">
							Studienbestätigung Katholisch-Theologische Privatuniversität Linz
						</fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="210pt" left="30pt" height="20pt">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="140mm"/>
								<fo:table-body>
						            <fo:table-row line-height="30pt">
						                    <fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
											<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="6pt"> 
												<fo:inline vertical-align="super">
													Zur Vorlage an (Stelle an der die Bestätigung vorgelegt wird und deren Bezugszahl, z.B. Sozialversicherungsnr.)
												</fo:inline>
											</fo:block>
									</fo:table-cell>
								</fo:table-row>      
							</fo:table-body>
						</fo:table>
					</fo:block-container>

					<fo:block-container position="absolute" top="226pt" left="445pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt"> 
							<fo:inline vertical-align="super">
								SV-Nummer
							</fo:inline>
						</fo:block>
					</fo:block-container>

					<fo:block-container position="absolute" top="241pt" left="428pt" height="20pt">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="45mm"/>
								<fo:table-body>
						            <fo:table-row line-height="25pt">
										<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
										<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
											<xsl:value-of select="svnr" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>      
							</fo:table-body>
						</fo:table>
					</fo:block-container> 

					<fo:block-container position="absolute" top="246pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="10pt">
							<xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="vornamen" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" />
						</fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="270pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							geboren am<xsl:text> </xsl:text><xsl:value-of select="geburtsdatum" /><xsl:text> </xsl:text>
							ist im<xsl:text> </xsl:text><xsl:value-of select="studiensemester_aktuell" /><xsl:text> </xsl:text>
							als ordentliche(r) Studierende(r) \n
							der Studienrichtung<xsl:text> </xsl:text><xsl:value-of select="studiengang_bezeichnung" /> im
							<xsl:text> </xsl:text><xsl:value-of select="semester" />. Semester gemeldet.
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="300pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							Datum:<xsl:text> </xsl:text><xsl:value-of select="tagesdatum" /><xsl:text> </xsl:text>DVR: 0029874(1739)
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="300pt" left="300pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							Für das Rektorat: <xsl:text> </xsl:text><xsl:value-of select="rektor" />
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="320pt" left="50pt" height="30pt">
                            <fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
							</fo:block>
					</fo:block-container> 

<!-- Abschnitt 3  -->

					<fo:block-container position="absolute" top="340pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="14pt">
					Studienbestätigung Katholisch-Theologische Privatuniversität Linz
						</fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="360pt" left="30pt" height="20pt">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="140mm"/>
								<fo:table-body>
						            <fo:table-row line-height="30pt">
						                    <fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
											<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="6pt"> 
												<fo:inline vertical-align="super">
													Zur Vorlage an (Stelle an der die Bestätigung vorgelegt wird und deren Bezugszahl, z.B. Sozialversicherungsnr.)
												</fo:inline>
											</fo:block>
									</fo:table-cell>
								</fo:table-row>      
							</fo:table-body>
						</fo:table>
					</fo:block-container>
					

					<fo:block-container position="absolute" top="376pt" left="445pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt"> 
							<fo:inline vertical-align="super">
								SV-Nummer
							</fo:inline>
						</fo:block>
					</fo:block-container>

					<fo:block-container position="absolute" top="391pt" left="428pt" height="20pt">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="45mm"/>
								<fo:table-body>
						            <fo:table-row line-height="25pt">
										<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
										<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
											<xsl:value-of select="svnr" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>      
							</fo:table-body>
						</fo:table>
					</fo:block-container> 

					<fo:block-container position="absolute" top="396pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="10pt">
							<xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="vornamen" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" />
						</fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="420pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							geboren am<xsl:text> </xsl:text><xsl:value-of select="geburtsdatum" /><xsl:text> </xsl:text>
							ist im<xsl:text> </xsl:text><xsl:value-of select="studiensemester_aktuell" /><xsl:text> </xsl:text>
							als ordentliche(r) Studierende(r) \n
							der Studienrichtung <xsl:text> </xsl:text><xsl:value-of select="studiengang_bezeichnung" /> im
							<xsl:text> </xsl:text><xsl:value-of select="semester" />. Semester gemeldet.
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="450pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							Datum:<xsl:text> </xsl:text><xsl:value-of select="tagesdatum" /><xsl:text> </xsl:text>DVR: 0029874(1739)
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="450pt" left="300pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							Für das Rektorat: <xsl:text> </xsl:text><xsl:value-of select="rektor" />
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="470pt" left="50pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                        </fo:block>
					</fo:block-container> 

<!-- Abschnitt 4  -->

					<fo:block-container position="absolute" top="490pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="14pt">
							Studienbestätigung Katholisch-Theologische Privatuniversität Linz
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="510pt" left="30pt" height="20pt">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="140mm"/>
								<fo:table-body>
						            <fo:table-row line-height="30pt">
						                    <fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
											<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="6pt"> 
												<fo:inline vertical-align="super">
													Zur Vorlage an (Stelle an der die Bestätigung vorgelegt wird und deren Bezugszahl, z.B. Sozialversicherungsnr.)
												</fo:inline>
											</fo:block>
									</fo:table-cell>
								</fo:table-row>      
							</fo:table-body>
						</fo:table>
					</fo:block-container>


					<fo:block-container position="absolute" top="526pt" left="445pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt"> 
							<fo:inline vertical-align="super">
								SV-Nummer
							</fo:inline>
						</fo:block>
					</fo:block-container>

					<fo:block-container position="absolute" top="541pt" left="428pt" height="20pt">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="45mm"/>
								<fo:table-body>
						            <fo:table-row line-height="25pt">
										<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
										<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
											<xsl:value-of select="svnr" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>      
							</fo:table-body>
						</fo:table>
					</fo:block-container> 


					<fo:block-container position="absolute" top="546pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="10pt">
							<xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="vornamen" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" />
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="570pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							geboren am<xsl:text> </xsl:text><xsl:value-of select="geburtsdatum" /><xsl:text> </xsl:text>
							ist im<xsl:text> </xsl:text><xsl:value-of select="studiensemester_aktuell" /><xsl:text> </xsl:text>
							als ordentliche(r) Studierende(r) \n
							der Studienrichtung<xsl:text> </xsl:text><xsl:value-of select="studiengang_bezeichnung" /> im
							<xsl:text> </xsl:text><xsl:value-of select="semester" />. Semester gemeldet.
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="600pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							Datum:<xsl:text> </xsl:text><xsl:value-of select="tagesdatum" /><xsl:text> </xsl:text>DVR: 0029874(1739)
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="600pt" left="300pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
							Für das Rektorat: <xsl:text> </xsl:text><xsl:value-of select="rektor" />
						</fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="620pt" left="50pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                        </fo:block>
					</fo:block-container> 

<!-- Abschnitt 5  -->

					<fo:block-container position="absolute" top="640pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="14pt">
							Studienbestätigung Katholisch-Theologische Privatuniversität Linz
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="660pt" left="30pt" height="20pt">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="140mm"/>
								<fo:table-body>
						            <fo:table-row line-height="30pt">
						                    <fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
											<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="6pt"> 
												<fo:inline vertical-align="super">
													Zur Vorlage an (Stelle an der die Bestätigung vorgelegt wird und deren Bezugszahl, z.B. Sozialversicherungsnr.)
												</fo:inline>
											</fo:block>
									</fo:table-cell>
								</fo:table-row>      
							</fo:table-body>
						</fo:table>
					</fo:block-container>


					<fo:block-container position="absolute" top="676pt" left="445pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt"> 
							<fo:inline vertical-align="super">
								SV-Nummer
							</fo:inline>
						</fo:block>
					</fo:block-container>

					<fo:block-container position="absolute" top="691pt" left="428pt" height="20pt">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="45mm"/>
								<fo:table-body>
						            <fo:table-row line-height="25pt">
										<fo:table-cell border-align="right" border-width="0.2mm" border-style="solid" >
										<fo:block line-height="12pt" font-family="sans-serif" font-size="10pt" content-width="45mm" text-align="center">
											<xsl:value-of select="svnr" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>      
							</fo:table-body>
						</fo:table>
					</fo:block-container> 


					<fo:block-container position="absolute" top="696pt" left="30pt" height="30pt">
						<fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="10pt">
							<xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="vornamen" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" />
                        </fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="720pt" left="30pt" height="30pt">
                            <fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
								geboren am<xsl:text> </xsl:text><xsl:value-of select="geburtsdatum" /><xsl:text> </xsl:text>
								ist im<xsl:text> </xsl:text><xsl:value-of select="studiensemester_aktuell" /><xsl:text> </xsl:text>
								als ordentliche(r) Studierende(r) \n
								der Studienrichtung<xsl:text> </xsl:text><xsl:value-of select="studiengang_bezeichnung" /> im
								<xsl:text> </xsl:text><xsl:value-of select="semester" />. Semester gemeldet.
							</fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="750pt" left="30pt" height="30pt">
                            <fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
								Datum:<xsl:text> </xsl:text><xsl:value-of select="tagesdatum" /><xsl:text> </xsl:text>DVR: 0029874(1739)
							</fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="750pt" left="300pt" height="30pt">
                            <fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
								Für das Rektorat: <xsl:text> </xsl:text><xsl:value-of select="rektor" />
							</fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="770pt" left="50pt" height="30pt">
                            <fo:block text-align="left" line-height="12pt" font-family="sans-serif" font-size="8pt">
-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
							</fo:block>
					</fo:block-container> 
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>
</xsl:stylesheet >