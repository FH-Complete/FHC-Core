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
							<xsl:text>ZEUGNIS \n </xsl:text>
							<xsl:choose>
								<xsl:when test="string-length(semester_bezeichnung)=0">
									<xsl:value-of select="stsem"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="semester_bezeichnung"/>
									<xsl:text> (</xsl:text>
									<xsl:value-of select="stsem"/>
									<xsl:text>)</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
						</fo:inline>
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="65mm" left="23mm" height="20mm">
					<fo:block text-align="left" line-height="18pt" font-family="arial" font-size="16pt">
						<xsl:value-of select="studiengang_art"/>-Studiengang
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="72mm" left="21.5mm" height="20mm">
					<fo:block text-align="left" line-height="18pt" content-width="80mm" font-family="arial" font-size="16pt">
						<fo:inline font-weight="900">
							<xsl:value-of select="studiengang"/>
						</fo:inline>
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="94.5mm" left="117.5mm">
					<fo:block line-height="11pt" font-size="9pt" content-width="70mm" text-align="right" font-family="arial">
						<xsl:text>Personenkennzeichen: </xsl:text><xsl:value-of select="matrikelnr" />
						<xsl:text>\nKennzahl des Studienganges: </xsl:text><xsl:value-of select="studiengang_kz" /><xsl:text> </xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="117mm" left="23mm" height="10mm">
					<fo:block line-height="11pt" font-family="arial" font-size="10pt"><xsl:text>Vorname/Familienname:\n</xsl:text></fo:block>
					<fo:block line-height="11pt" font-family="arial" font-size="10pt"><xsl:text>Geburtsdatum:</xsl:text></fo:block>
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
						<fo:table-column column-width="103mm"/>
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
									<fo:block font-size="9pt" font-weight="bold" content-width="102mm" vertical-align="center" font-family="arial">
										Lehrveranstaltung
										</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center" background-color="#afb8bc">
									<fo:block font-size="9pt" font-weight="bold" content-width="20mm" text-align="center"  vertical-align="center" font-family="arial">
										Note
										</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center" background-color="#afb8bc">
									<fo:block font-size="9pt" font-weight="bold" content-width="20mm" text-align="center"  vertical-align="center" font-family="arial">
										SWS
										</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center" background-color="#afb8bc">
									<fo:block font-size="9pt" font-weight="bold" content-width="20mm" text-align="center"  vertical-align="center" font-family="arial">
										ECTS-LP
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
									<xsl:text> Gesamt </xsl:text>
									
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
					<fo:block font-size="7pt">\n</fo:block>
					<fo:table>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="155mm"/>
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell>
									<fo:block font-size="6pt" font-family="arial">Notenstufen: </fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-size="6pt" font-family="arial">Sehr gut (1), Gut (2), Befriedigend (3), Genügend (4), Nicht genügend (5), mit Erfolg teilgenommen (met), nicht teilgenommen (nt), teilgenommen(tg),</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell>
									<fo:block font-size="6pt"/>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-size="6pt" font-family="arial">angerechnet (ar), nicht beurteilt (nb), bestanden (b), erfolgreich absolviert (ea), nicht erfolgreich absolviert (nea)</fo:block>
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
											Kommissionelle Abschlussprüfung
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
												<xsl:text> Bachelorprüfung</xsl:text>
											</xsl:if>
											<xsl:if test="abschlusspruefung_typ='Diplom'" >
												<xsl:text> Masterprüfung</xsl:text>
											</xsl:if>
											<xsl:text> vom </xsl:text>
											<xsl:value-of select="abschlusspruefung_datum" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center" >
										<fo:block font-size="9pt" content-width="60mm" vertical-align="center" font-family="arial">
											<xsl:text> </xsl:text><xsl:value-of select="abschlusspruefung_note" />
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
										<fo:block font-size="6pt" font-family="arial">Notenstufen: </fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-size="6pt" font-family="arial">mit ausgezeichnetem Erfolg bestanden, mit gutem Erfolg bestanden, bestanden</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</xsl:if>
				</fo:block-container>
				<fo:block-container position="absolute" top="252mm" left="21.5mm">
					<fo:table table-layout="fixed" border-collapse="collapse">
						<fo:table-column column-width="73mm" />
						<fo:table-column column-width="17mm" />
						<fo:table-column column-width="73mm" />
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell>
									<fo:block line-height="12pt" font-size="9pt" font-family="arial">
										Wien, am <xsl:value-of select="ort_datum" />
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
										<xsl:text>\nStudiengangsleitung</xsl:text>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>
	<xsl:template match="unterrichtsfach">
		<fo:table-row line-height="12pt">
			<fo:table-cell border-width="0mm">
				<!-- Dummy Zelle -->
			</fo:table-cell>
			<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
				<fo:block font-size="9pt" content-width="104mm" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="bisio_von">
							<xsl:text>Auslandsaufenthalt: </xsl:text><xsl:value-of select="bisio_von"/><xsl:text>-</xsl:text><xsl:value-of select="bisio_bis"/><xsl:text>, </xsl:text><xsl:value-of select="bisio_ort"/><xsl:text>, </xsl:text><xsl:value-of select="bisio_universitaet"/>
							<xsl:text>\n Die im Ausland absolvierten Lehrveranstaltungen werden für das </xsl:text><xsl:value-of select="../semester"/><xsl:text>. Semester des Studiums an der Fachhochschule Technikum Wien angerechnet  (Details siehe Transcript of Records der Gasthochschule).</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="bezeichnung"/>
						</xsl:otherwise>
					</xsl:choose>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="20mm" text-align="center" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="note"/>
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
				<fo:block font-size="9pt" content-width="96mm" font-family="arial">
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
</xsl:stylesheet>
