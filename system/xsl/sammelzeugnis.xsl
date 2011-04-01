<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="sammelzeugnisse">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set><fo:simple-page-master format="A3" orientation="L" master-name="PageMaster">				
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="sammelzeugnis"/>
		</fo:root>
	</xsl:template>


	<xsl:template match="sammelzeugnis">
		<fo:page-sequence master-reference="PageMaster">
			<fo:flow flow-name="xsl-region-body" >
				<fo:block>
					<fo:external-graphic src="../skin/images/logo.jpg"  posx="300" posy="15" height="33.44mm" width="99.99mm"/>
				</fo:block>
				<fo:block-container position="absolute" top="100mm" left="270mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate" >
						<fo:table-column column-width="100"  />								
						<fo:table-body>
							<fo:table-row  line-height="12pt">								
								<fo:table-cell ><fo:block content-width="100mm" text-align="center" line-height="25pt" font-family="sans-serif" font-size="25pt" font-weight="bold">Transcript of Records</fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="12pt">								
								<fo:table-cell ><fo:block content-width="100mm" text-align="center" line-height="25pt" font-family="sans-serif" font-size="15pt" font-weight="bold"><xsl:value-of select="studiengang_art" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="12pt">								
								<fo:table-cell ><fo:block content-width="100mm" text-align="center" line-height="25pt" font-family="sans-serif" font-size="15pt" font-weight="bold"><xsl:value-of select="studiengang_englisch" /></fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="240mm" left="330mm" height="0mm">
					<fo:block line-height="11pt" font-family="arial" font-size="10pt" content-width="50mm" text-align="right"> 
						<xsl:text>University of Applied Sciences </xsl:text>
						<xsl:text>\nTechnikum Wien </xsl:text>
						<xsl:text>\nHoechstaedtplatz 5</xsl:text>
						<xsl:text>\nA-1200 Vienna, Austria, Europe</xsl:text>
						<xsl:text>\nT: +43-1-3334077</xsl:text>
					</fo:block>
				</fo:block-container>
			</fo:flow>
		</fo:page-sequence>
	
		<fo:page-sequence master-reference="PageMaster">
			<fo:flow flow-name="xsl-region-body" >
				<fo:block-container position="absolute" top="25mm" left="28mm" height="20mm">
					<fo:block text-align="left" line-height="18pt" font-family="arial" font-size="16pt">
						<xsl:value-of select="studiengang_art"/>-Degree Program
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="32mm" left="26mm" height="20mm">
					<fo:block text-align="left" line-height="18pt" content-width="80mm" font-family="arial" font-size="16pt">
						<fo:inline font-weight="900">
							<xsl:value-of select="studiengang_englisch"/>
						</fo:inline>
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="42mm" left="26mm" height="20mm">
					<fo:block text-align="left" line-height="18pt" content-width="80mm" font-family="arial" font-size="16pt">
						<fo:inline font-weight="900">
							<xsl:text>Transcript of Records</xsl:text>
						</fo:inline>
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="48mm" left="27mm" height="20mm">
					<fo:block text-align="left" line-height="18pt" content-width="80mm" font-family="arial" font-size="12pt">
						<fo:inline font-weight="900">
							<xsl:text>Semester </xsl:text>
							<xsl:value-of select="start_semester_number"/>
							<xsl:text>-</xsl:text>
							<xsl:value-of select="end_semester_number"/>
						</fo:inline>
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="40.5mm" left="110.5mm">
					<fo:block line-height="11pt" font-size="9pt" content-width="70mm" text-align="right" font-family="arial">
						<xsl:text>Student ID: </xsl:text><xsl:value-of select="matrikelnr" />
						<xsl:text>\nProgram Code: </xsl:text><xsl:value-of select="studiengang_kz" /><xsl:text> </xsl:text>
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="63mm" left="28mm" height="10mm">
					<fo:block line-height="11pt" font-family="arial" font-size="10pt"><xsl:text>First Name/Last Name:\n</xsl:text></fo:block>
					<fo:block line-height="11pt" font-family="arial" font-size="10pt"><xsl:text>Date of Birth:</xsl:text></fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="63mm" left="68mm">
					<fo:block line-height="11pt" font-family="arial" font-size="10pt" font-weight="bold">
						<xsl:value-of select="name"/>
						<xsl:text>\n</xsl:text>
					</fo:block>
					<fo:block line-height="11pt" font-family="arial" font-size="10pt">
						<xsl:value-of select="gebdatum" />
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="82mm" left="28mm">
					<fo:block line-height="11pt" font-family="arial" font-size="10pt" content-width="170mm"> 
						<xsl:text>Within the period of studies at the University of Applied Science Technikum Wien from </xsl:text>
						<xsl:value-of select="start_semester"/>
						<xsl:text> to </xsl:text>
						<xsl:value-of select="end_semester"/>
						<xsl:text> in the </xsl:text>
						<xsl:value-of select="studiengang_art"/>
						<xsl:text>´s </xsl:text>
						<xsl:value-of select="studiengang_englisch"/>
						<xsl:text> Program examinations in the following subjects were passed: </xsl:text>
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="97mm" left="28mm">
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2pt" border-style="solid">
						<fo:table-column column-width="0mm"/>
						<fo:table-column column-width="25mm"/>
						<fo:table-column column-width="78mm"/>
						<fo:table-column column-width="20mm"/> 
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="20mm"/>
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
									<fo:block font-size="9pt" font-weight="bold" content-width="25mm" text-align="center" vertical-align="center" font-family="arial">
										Semester
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center"  background-color="#afb8bc" >
									<fo:block font-size="9pt" font-weight="bold" content-width="78mm" text-align="center" vertical-align="center" font-family="arial">
										Course
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center" background-color="#afb8bc">
									<fo:block font-size="9pt" font-weight="bold" content-width="20mm" text-align="center"  vertical-align="center" font-family="arial">
										Grade
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center" background-color="#afb8bc">
									<fo:block font-size="9pt" font-weight="bold" content-width="20mm" text-align="center"  vertical-align="center" font-family="arial">
										SP/W
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center" background-color="#afb8bc">
									<fo:block font-size="9pt" font-weight="bold" content-width="20mm" text-align="center"  vertical-align="center" font-family="arial">
										ECTS
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<xsl:apply-templates select="unterrichtsfach_1"/>
						</fo:table-body>
					</fo:table>
				</fo:block-container>
					
				<fo:block-container position="absolute" top="30mm" left="228mm">
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2pt" border-style="solid">
						<fo:table-column column-width="0mm"/>
						<fo:table-column column-width="25mm"/>
						<fo:table-column column-width="78mm"/>
						<fo:table-column column-width="20mm"/> 
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="20mm"/>
						<fo:table-body>
							<xsl:apply-templates select="unterrichtsfach_2"/>
						</fo:table-body>
					</fo:table>
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2pt" border-style="solid">
						<fo:table-column column-width="0mm"/>
						<fo:table-column column-width="103.4mm"/>
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="20mm"/> 
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="20mm"/>
						<fo:table-body>
							<xsl:apply-templates select="fussnote"/> 
						</fo:table-body>
					</fo:table>
					<fo:block font-size="7pt">\n</fo:block>
					<fo:table>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="155mm"/>
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell>
									<fo:block font-size="6pt" font-family="arial">Grades: </fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-size="6pt" font-family="arial">excellent (1), very good (2), good (3), satisfactory (4), fail (5),</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell>
									<fo:block font-size="6pt"/>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-size="6pt" font-family="arial">Credit based on previous experience/work (ar), not graded (nb), participated (tg), passed (b), successfully completed (ea), not successfully completed (nea)</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>

				<fo:block-container position="absolute" top="252mm" left="228mm">
					<fo:table table-layout="fixed" border-collapse="collapse">
						<fo:table-column column-width="73mm" />
						<fo:table-column column-width="17mm" />
						<fo:table-column column-width="73mm" />
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell>
									<fo:block line-height="12pt" font-size="9pt" font-family="arial">
										Vienna, 
									</fo:block>
									<fo:block line-height="3pt" font-size="3pt" />
								</fo:table-cell>
								<fo:table-cell></fo:table-cell>
								<fo:table-cell></fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell border-top-style="dotted">
									<fo:block line-height="13pt" font-size="9pt" font-family="arial">
										<xsl:text>Ort, Datum</xsl:text>
									</fo:block>
								</fo:table-cell>
								<fo:table-cell>
								</fo:table-cell>
								<fo:table-cell border-top-style="dotted">
									<fo:block line-height="13pt" font-size="9pt" font-family="arial">
										<xsl:value-of select="studiengangsleiter" />
										<xsl:text>\nProgram Director</xsl:text>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>
	
	<xsl:template match="unterrichtsfach_1">
		<fo:table-row line-height="12pt">
			<fo:table-cell border-width="0mm">
				<!-- Dummy Zelle -->
			</fo:table-cell>
			<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
				<fo:block font-size="9pt" content-width="25mm" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="semester"/>
					<xsl:text> - </xsl:text>
					<xsl:value-of select="stsem"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="78mm" text-align="left" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
							<xsl:choose>
								<xsl:when test="string-length(bezeichnung_englisch)!=0">
									<xsl:value-of select="bezeichnung_englisch"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>[ACHTUNG: Keine englische Bezeichung für "</xsl:text><xsl:value-of select="bezeichnung"/><xsl:text>" in der Datenbank!]</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="note_anmerkung"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="sws"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="ects"/>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>	

	<xsl:template match="unterrichtsfach_2">
		<fo:table-row line-height="12pt">
			<fo:table-cell border-width="0mm">
				<!-- Dummy Zelle -->
			</fo:table-cell>
			<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
				<fo:block font-size="9pt" content-width="25mm" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="semester"/>
					<xsl:text> - </xsl:text>
					<xsl:value-of select="stsem"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="78mm" text-align="left" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="string-length(bezeichnung_englisch)!=0">
							<xsl:value-of select="bezeichnung_englisch"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>[ACHTUNG: Keine englische Bezeichung für "</xsl:text><xsl:value-of select="bezeichnung"/><xsl:text>" in der Datenbank!]</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="note_anmerkung"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="sws"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="ects"/>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>	
	
	<xsl:template match="fussnote">
		<fo:table-row line-height="11pt">
			<fo:table-cell border-width="0mm">
				<!-- Dummy Zelle -->
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid">
				<fo:block font-size="9pt" content-width="103.4mm" font-family="arial">
					<xsl:value-of select="fussnotenzeichen"/>
					<xsl:text> </xsl:text>
					<fo:inline font-weight="bold">
						<xsl:value-of select="themenbereich_bezeichnung"/>
						<xsl:text> </xsl:text>
					</fo:inline>
					<xsl:value-of select="themenbereich"/>
					<xsl:text> </xsl:text>
					<fo:inline font-weight="bold">
						<xsl:value-of select="titel_bezeichnung"/>
						<xsl:text> </xsl:text>
					</fo:inline>
					<xsl:value-of select="titel"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:if test="../projektarbeit_note_anzeige='true'">
						<xsl:value-of select="note"/>
					</xsl:if>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="sws"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="ects"/>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>	
</xsl:stylesheet >
