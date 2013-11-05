<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" indent="yes"/>
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
			<fo:flow flow-name="xsl-region-body">
				<fo:block-container position="absolute" top="45mm" left="21.5mm" height="20mm">
					<fo:block text-align="left" line-height="18pt" font-family="arial" font-size="16pt">
						<fo:inline font-weight="900">
							<xsl:text>Transcript of Records \n </xsl:text>
							<xsl:choose>
								<xsl:when test="string-length(semester_bezeichnung)=0">
									<xsl:value-of select="stsem"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="semester"/>
									<xsl:choose>
										<xsl:when test="semester=1">
											<xsl:text>st</xsl:text>
										</xsl:when>
										<xsl:when test="semester=2">
											<xsl:text>nd</xsl:text>
										</xsl:when>
										<xsl:when test="semester=3">
											<xsl:text>rd</xsl:text>
										</xsl:when>
										<xsl:otherwise>
											<xsl:text>th</xsl:text>
										</xsl:otherwise>
									</xsl:choose>
									<xsl:text> Semester (</xsl:text>
									<xsl:value-of select="stsem"/>
									<xsl:text>)</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
						</fo:inline>
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="65mm" left="23mm" height="20mm">
					<fo:block text-align="left" line-height="18pt" font-family="arial" font-size="16pt">
						<xsl:choose>
							<xsl:when test="studiengang_art='Bachelor'">
								<xsl:text>Bachelor's</xsl:text>
							</xsl:when>
							<xsl:when test="studiengang_art='Master'">
								<xsl:text>Master's</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="studiengang_art"/>
							</xsl:otherwise>
						</xsl:choose> Degree Program
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="72mm" left="21.5mm" height="20mm">
					<fo:block text-align="left" line-height="18pt" content-width="80mm" font-family="arial" font-size="16pt">
						<fo:inline font-weight="900">
							<xsl:value-of select="studiengang_englisch"/>
						</fo:inline>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="94.5mm" left="117.5mm">
					<fo:block line-height="11pt" font-size="9pt" content-width="70mm" text-align="right" font-family="arial">
						<xsl:text>Student ID: </xsl:text><xsl:value-of select="matrikelnr" />
						<xsl:text>\nProgram Code: </xsl:text><xsl:value-of select="studiengang_kz" /><xsl:text> </xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="117mm" left="23mm" height="10mm">
					<fo:block line-height="11pt" font-family="arial" font-size="10pt"><xsl:text>First Name/Last Name:\n</xsl:text></fo:block>
					<fo:block line-height="11pt" font-family="arial" font-size="10pt"><xsl:text>Date of Birth:</xsl:text></fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="117mm" left="68mm">
				<fo:block line-height="11pt" font-family="arial" font-size="10pt" font-weight="bold">
				<xsl:value-of select="name"/>
				<xsl:text>\n</xsl:text>
				</fo:block>
				<fo:block line-height="11pt" font-family="arial" font-size="10pt">
				<xsl:value-of select="gebdatum" />
				</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="137mm" left="23mm">
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2pt" border-style="solid">
						<fo:table-column column-width="0mm"/>
						<fo:table-column column-width="102mm"/>
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="20mm"/>
						<fo:table-body>
							<fo:table-row line-height="9pt">
								<fo:table-cell border-width="0mm" >
								<fo:block font-size="9pt" font-weight="bold" >
										<!-- wenn die erste Spalte eine Hintergrundfarbe hat, dann wird der Text von der Hintergrundfarbe ueberschrieben.
										     Deshalb gibt es hier eine Dummy-Spalte. Ab der zweiten Spalte funktioniert es dann problemlos
										     grauslich, funktioniert aber...
										-->
								</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center"  background-color="#afb8bc" >
									<fo:block font-size="9pt" font-weight="bold" content-width="102mm" vertical-align="center" font-family="arial">
										<xsl:text> Course</xsl:text>
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center" background-color="#afb8bc">
									<fo:block font-size="9pt" font-weight="bold" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
										<xsl:text>Grade</xsl:text>
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center" background-color="#afb8bc">
									<fo:block font-size="9pt" font-weight="bold" content-width="20mm" text-align="center"  vertical-align="center" font-family="arial">
										<xsl:text>SP/W</xsl:text>
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center" background-color="#afb8bc">
									<fo:block font-size="9pt" font-weight="bold" content-width="20mm" text-align="center"  vertical-align="center" font-family="arial">
										<xsl:text>ECTS\n credits </xsl:text>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<xsl:apply-templates select="unterrichtsfach"/>


							<!--  ECTS-Gesamt -->
							<fo:table-row line-height="12pt">
								<fo:table-cell border-width="0mm">
									<!-- Dummy Zelle -->
								</fo:table-cell>
								<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
								<fo:block font-size="9pt" content-width="104mm" vertical-align="center" font-family="arial">
									<xsl:text> Total </xsl:text>
									
								</fo:block>
							</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
									<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
										<xsl:text> - </xsl:text>
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
									<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
										<xsl:text> - </xsl:text>
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
									<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
										<xsl:text> </xsl:text>
										<xsl:value-of select="ects_gesamt"/>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<!--<fo:table-row line-height="0pt">
									<fo:table-cell border-width="0.2mm" border-style="solid" ></fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid" ></fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid" ></fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid" ></fo:table-cell>
								</fo:table-row>-->
							<xsl:apply-templates select="fussnote"/>
						</fo:table-body>
					</fo:table>
					<fo:block font-size="7pt" font-family="arial">\n</fo:block>
					<fo:table>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="155mm"/>
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell>
									<fo:block font-size="6pt" font-family="arial">Grades: </fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-size="6pt" font-family="arial">excellent (1), very good (2), good (3), satisfactory (4), fail (5), not graded (nb), Credit based on previous experience/work (ar),</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell>
									<fo:block font-size="6pt"/>
								</fo:table-cell>
								<fo:table-cell>
								<!--non-credit participation (na) - Note im FAS nicht vorhanden-->
									<fo:block font-size="6pt" font-family="arial">Participated with success (met), passed (b), successfully completed (ea), not successfully completed (nea), did not participate (nt), participated(tg)</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<fo:block font-size="7pt">\n</fo:block>
					
					<xsl:if test="abschlusspruefung_typ">
						<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2pt" border-style="solid">
							<fo:table-column column-width="0mm"/>
							<fo:table-column column-width="164.2mm"/>
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
										<fo:block font-size="9pt" font-weight="bold" content-width="162mm" vertical-align="center" font-family="arial">
											Final Examination
											</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
						<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2pt" border-style="solid">
							<fo:table-column column-width="0mm"/>
							<fo:table-column column-width="103mm"/>
							<fo:table-column column-width="60.8mm"/>
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
									<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center" >
										<fo:block font-size="9pt" content-width="102mm" vertical-align="center" font-family="arial">
											<xsl:if test="abschlusspruefung_typ='Bachelor'" >
												<xsl:text> Bachelor's Examination on </xsl:text>
											</xsl:if>
											<xsl:if test="abschlusspruefung_typ='Diplom'" >
												<xsl:text> Master's Examination on </xsl:text>
											</xsl:if>
											<xsl:value-of select="abschlusspruefung_datum" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center" >
										<fo:block font-size="9pt" content-width="60mm" vertical-align="center" font-family="arial">
											<xsl:text> </xsl:text><xsl:value-of select="abschlusspruefung_note_english" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
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
										<fo:block font-size="6pt" font-family="arial">Passed with highest distinction, Passed with distinction, Passed</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</xsl:if>
					
				</fo:block-container>

				<fo:block-container position="absolute" top="252mm" left="23mm">
					<fo:table table-layout="fixed" border-collapse="collapse">
						<fo:table-column column-width="73mm" />
						<fo:table-column column-width="17mm" />
						<fo:table-column column-width="73mm" />
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell>
									<fo:block line-height="12pt" font-size="9pt" font-family="arial">
										Vienna, <xsl:value-of select="ort_datum" />
									</fo:block>
									<fo:block line-height="3pt" font-size="3pt" />
								</fo:table-cell>
								<fo:table-cell></fo:table-cell>
								<fo:table-cell></fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell border-top-style="dotted">
									<fo:block line-height="13pt" font-size="9pt" font-family="arial">
										<xsl:text>Place, Date</xsl:text>
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
<!--				<fo:block-container position="absolute" top="290mm" left="30mm" height="10mm">
					<fo:table table-layout="fixed" border-collapse="collapse">
						<fo:table-column column-width="185mm"/>
							<fo:table-body>
								<fo:table-row  line-height="10pt" >
									<fo:table-cell>
										<fo:block font-size="8pt" content-width="185mm" text-align="left">
											<xsl:text>Fachhochschule Technikum Wien, Hoechstaedtplatz 5, 1200 Vienna, AUSTRIA DVR 0928381,ZVR 074476426</xsl:text>
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
				</fo:block-container>-->
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>
	<xsl:template match="unterrichtsfach">
		<fo:table-row line-height="12pt">
			<fo:table-cell border-width="0mm">
				<!-- Dummy Zelle -->
			</fo:table-cell>
			<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
				<fo:block font-size="9pt" content-width="102mm" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="bisio_von">
						    <xsl:text>International Semester Abroad: </xsl:text><xsl:value-of select="bisio_von"/><xsl:text>-</xsl:text><xsl:value-of select="bisio_bis"/><xsl:text>, at </xsl:text><xsl:value-of select="bisio_ort"/><xsl:text>, </xsl:text><xsl:value-of select="bisio_universitaet"/>
							<xsl:text>\n All credits earned during the International Semester Abroad (ISA) are fully credited for the </xsl:text> 
							<xsl:value-of select="../semester"/>
							<xsl:choose>
								<xsl:when test="../semester=1">
									<xsl:text>st</xsl:text>
								</xsl:when>
								<xsl:when test="../semester=2">
									<xsl:text>nd</xsl:text>
								</xsl:when>
								<xsl:when test="../semester=3">
									<xsl:text>rd</xsl:text>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>th</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:text> semester at the UAS Fachhochschule Technikum Wien. (see Transcript of Records)</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="string-length(bezeichnung_englisch)!=0">
									<xsl:value-of select="bezeichnung_englisch"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>[ACHTUNG: Keine englische Bezeichung für "</xsl:text><xsl:value-of select="bezeichnung"/><xsl:text>" in der Datenbank!]</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="note!=''">
							<xsl:value-of select="note"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>-</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="sws!=''">
							<xsl:value-of select="sws"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>-</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="ects!=''">
							<xsl:value-of select="ects"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>-</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
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
				<fo:block font-size="9pt" content-width="96mm" font-family="arial">
					<xsl:value-of select="fussnotenzeichen"/>
					<xsl:text> </xsl:text>
					<fo:inline font-weight="bold">
						<xsl:choose>
							<xsl:when test="themenbereich_bezeichnung='Themenbereich: '">
								<xsl:text>Subject Area:</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text></xsl:text>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:text> </xsl:text>
					</fo:inline>
					<xsl:value-of select="themenbereich"/>
					<xsl:text> </xsl:text>
					<fo:inline font-weight="bold">
						<xsl:choose>
							<xsl:when test="titel_bezeichnung='Bachelorarbeit:'">
								<xsl:text>Bachelor's Thesis:</xsl:text>
							</xsl:when>
							<xsl:when test="titel_bezeichnung='Diplomarbeit:'">
								<xsl:text>Master's Thesis:</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text></xsl:text>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:text> </xsl:text>
					</fo:inline>
					<xsl:choose>
						<xsl:when test="string-length(titel_en)!=0">
							<xsl:value-of select="titel_en"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="titel"/>
						</xsl:otherwise>
					</xsl:choose>
					
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:choose>
					<xsl:when test="../projektarbeit_note_anzeige='true'">
						<xsl:choose>
							<xsl:when test="note!=''">
								<xsl:value-of select="note"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>-</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>-</xsl:text>
					</xsl:otherwise>
					</xsl:choose>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="sws!=''">
							<xsl:value-of select="sws"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>-</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="ects!=''">
							<xsl:value-of select="ects"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>-</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>
</xsl:stylesheet>
