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
				<fo:block-container position="absolute" top="60mm" left="26.5mm" height="20mm">
					<fo:block text-align="left" line-height="18pt" font-family="arial" font-size="16pt">
						<fo:inline font-weight="900">
							<xsl:text>ZERTIFIKAT</xsl:text>
						</fo:inline>
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="67mm" left="26.5mm" height="20mm">
					<fo:block text-align="left" line-height="18pt" font-family="arial" font-size="16pt">
						<fo:inline font-weight="900">
							<xsl:text>Qualifikationsprüfungen</xsl:text>
						</fo:inline>
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="98mm" left="115mm">
					<fo:block line-height="11pt" font-size="8pt" content-width="70mm" text-align="right" font-family="arial">
						<xsl:text>Personenkennzeichen: </xsl:text><xsl:value-of select="matrikelnr" />
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="134mm" left="28mm" height="10mm">
					<fo:block line-height="11pt" font-family="arial" font-size="10pt"><xsl:text>Vorname/Familienname: </xsl:text><xsl:value-of select="name"/><xsl:text>\n</xsl:text></fo:block>
					<fo:block line-height="11pt" font-family="arial" font-size="10pt"><xsl:text>Geburtsdatum: </xsl:text><xsl:value-of select="gebdatum" /></fo:block>
				</fo:block-container>
								
				<fo:block-container position="absolute" top="153mm" left="27mm" height="10mm">
					<fo:block line-height="11pt" font-family="arial" font-size="10pt"><xsl:text> </xsl:text><xsl:value-of select="anrede"/><xsl:text> </xsl:text><xsl:value-of select="name"/>
					<xsl:text> hat im </xsl:text><xsl:value-of select="studiensemester"/><xsl:text> folgende Qualifikationsprüfungen an der FH Technikum Wien abgelegt:</xsl:text>
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="165mm" left="28mm">
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2pt" border-style="solid">
						<fo:table-column column-width="0mm"/>
						<fo:table-column column-width="90mm"/>
						<fo:table-column column-width="65mm"/>
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
										Prüfungsfach
										</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center" background-color="#afb8bc">
									<fo:block font-size="9pt" font-weight="bold" content-width="20mm" text-align="center"  vertical-align="center" font-family="arial">
										Beurteilung
										</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<xsl:apply-templates select="unterrichtsfach"/>
						</fo:table-body>
					</fo:table>
					<fo:block font-size="7pt">\n</fo:block>
					<fo:table>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="155mm"/>
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell>
									<fo:block font-size="6pt" font-family="arial">Beurteilung: bestanden, nicht bestanden, nicht teilgenommen</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell>
									<fo:block font-size="6pt" font-family="arial">Gesetzliche Grundlage: gem. § 4 abs. 3 des Bundesgesetzes über Fachhochschul-Studiengänge (FHStG), BGBl. Nr. 340/1993 idgF</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>
				<fo:block-container position="absolute" top="244mm" left="28mm">
					<fo:table table-layout="fixed" border-collapse="collapse">
						<fo:table-column column-width="73mm" />
						<fo:table-column column-width="10mm" />
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
										<xsl:text>Ort, Ausstellungsdatum</xsl:text>
									</fo:block>
								</fo:table-cell>
								<fo:table-cell>
								</fo:table-cell>
								<fo:table-cell border-top-style="dotted">
									<fo:block line-height="13pt" font-size="9pt" font-family="arial">
										<xsl:text> </xsl:text>
										<xsl:value-of select="studiengangsleiter" />
										<xsl:text>,\n Leiter Aufbaukurse</xsl:text>
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
				<fo:block font-size="9pt" content-width="90mm" vertical-align="center" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:value-of select="bezeichnung"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="9pt" content-width="65mm" font-family="arial">
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="note='nbe'">
							<xsl:text>nicht bestanden</xsl:text>
						</xsl:when>
						<xsl:when test="note='b'">
							<xsl:text>bestanden</xsl:text>
						</xsl:when>
						<xsl:when test="note='nt'">
							<xsl:text>nicht teilgenommen</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="note"/>
						</xsl:otherwise>
					</xsl:choose>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>
</xsl:stylesheet>
