<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" indent="yes"/>
	<xsl:template match="bestellungen">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="297mm" page-width="210mm" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="bestellung"/>
		</fo:root>
	</xsl:template>
	<xsl:template match="bestellung">
		<fo:page-sequence master-reference="PageMaster">
			<fo:flow flow-name="xsl-region-body">
				<fo:block-container position="absolute" top="10mm" left="8.5mm" height="15mm">
					<fo:block text-align="left" line-height="18pt" font-family="sans-serif" font-size="16pt">
						<fo:inline font-weight="900">
							<xsl:text>Bestellung Nr.: </xsl:text><xsl:value-of select="bestell_nr" />
						</fo:inline>
					</fo:block>
				</fo:block-container>
				<!-- Logo -->
				<fo:block>
					<fo:external-graphic src="../skin/images/logo_gmbh.jpg"  posx="140" posy="5" height="19mm" width="40mm"/>
				</fo:block>	
				<fo:block-container position="absolute" top="20mm" left="145mm">	
					<fo:block font-size="8pt">
						<xsl:text>Technikum Wien GmbH</xsl:text>
					</fo:block>
				</fo:block-container>
				<!-- Empfaenger -->
				<fo:block-container position="absolute" top="35mm" left="15mm">
					<fo:table table-layout="fixed" border-collapse="collapse">
						<fo:table-column column-width="90.5mm"/>
						<fo:table-body>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="8pt">
										 <xsl:text>Firma:</xsl:text>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt">
										 <xsl:value-of select="empfaenger/name" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt">
										 <xsl:value-of select="empfaenger/strasse" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt">
										 <xsl:value-of select="empfaenger/plz" /><xsl:text> </xsl:text><xsl:value-of select="empfaenger/ort" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt">
										 <xsl:text>Tel.: </xsl:text><xsl:value-of select="empfaenger/telefon" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt">
										 <xsl:text>Fax: </xsl:text><xsl:value-of select="empfaenger/fax" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt">
										<xsl:text>Kundennr.: </xsl:text><xsl:value-of select="kundennummer" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>

						</fo:table-body>
					</fo:table>
				</fo:block-container>

				<!-- Rechnungsanschrift / Lieferanschrift -->
				<fo:block-container position="absolute" top="35mm" left="115.7mm">
					<fo:table table-layout="fixed" border-collapse="collapse">
						<fo:table-column column-width="80mm"/>
						<fo:table-body>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt" font-weight="bold">
										 <xsl:text>Rechnungsadresse:</xsl:text>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt" font-weight="bold">
										 <xsl:value-of select="rechnungsadresse/name" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt" font-weight="bold">
										  <xsl:value-of select="rechnungsadresse/strasse" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt" font-weight="bold">
										 <xsl:value-of select="rechnungsadresse/plz" /><xsl:text> </xsl:text><xsl:value-of select="rechnungsadresse/ort" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt">
										 <!-- empty -->
									</fo:block>
								</fo:table-cell>
							</fo:table-row>

							<fo:table-row line-height="10pt">
								<fo:table-cell border-width="0.2mm">
									<fo:block font-size="10pt" font-weight="bold">
										 <xsl:text>Lieferadresse:</xsl:text>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt" font-weight="bold">
										 <xsl:value-of select="lieferadresse/name" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt" font-weight="bold">
										 <xsl:value-of select="lieferadresse/strasse" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt" font-weight="bold">
										 <xsl:value-of select="lieferadresse/plz" /><xsl:text> </xsl:text><xsl:value-of select="lieferadresse/ort" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt">
										 <!-- empty -->
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="8pt">
								<fo:table-cell>
									<fo:block font-size="6pt">
										<xsl:text>UID-Nummer: ATU 61910007</xsl:text>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="8pt">
								<fo:table-cell>
									<fo:block font-size="6pt">
										<xsl:text>Firmenbuch: Handelsgericht Wien, FN 264937p</xsl:text>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>
					<!-- Tabelle die ueber der Bestelldetail Tabelle liegt, um die vertikalen Linien zu Zeichnen -->

				<fo:block-container position="absolute" top="95mm" left="9.8mm">
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2mm" border-style="solid">
						<fo:table-column column-width="7mm"/>
						<fo:table-column column-width="9.4mm"/>
						<!-- <fo:table-column column-width="6.2mm"/> -->
						<fo:table-column column-width="85.6mm"/><!-- 79,4 -->
						<fo:table-column column-width="34.8mm"/>
						<fo:table-column column-width="14.8mm"/>
						<fo:table-column column-width="19.8mm"/>
						<fo:table-column column-width="11.8mm"/>
						<fo:table-body>
							<fo:table-row line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm">
											<!-- pos -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="10mm">
										<!-- menge -->
									</fo:block>
								</fo:table-cell>
								<!-- 
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm">
									
									</fo:block>
								</fo:table-cell>
								 -->
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="79mm">
										<!-- bezeichnung -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="34mm">
										<!-- aritikelnummer -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="15mm">
										<!-- Preis/VE -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="20mm">
										<!-- Summe [EUR] -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="12mm">
										<!-- UST -->
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="100mm">
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm">
											<!-- pos -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="10mm">
										<!-- menge -->
									</fo:block>
								</fo:table-cell>
								<!-- 
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm">
										
									</fo:block>
								</fo:table-cell>
								 -->
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="79mm">
										<!-- bezeichnung -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="34mm">
										<!-- aritikelnummer -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="15mm">
										<!-- Preis/VE -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="20mm">
										<!-- Summe [EUR] -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="12mm">
										<!-- UST -->
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>

				</fo:block-container>

				<!-- Bestelldetails -->
				<fo:block-container position="absolute" top="95mm" left="10mm">
					<fo:table table-layout="fixed" border-collapse="collapse">
						<fo:table-column column-width="7mm"/>
						<fo:table-column column-width="10mm"/>
						<!-- <fo:table-column column-width="7mm"/> -->
						<fo:table-column column-width="87mm"/><!-- 80 -->
						<fo:table-column column-width="35mm"/>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="12mm"/>
						<fo:table-body>
							<fo:table-row line-height="10pt">
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm" text-align="center">
										 Pos
									</fo:block>
								</fo:table-cell>
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="10mm" text-align="center">
										Menge
									</fo:block>
								</fo:table-cell>
								<!-- 
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm" text-align="center">
										VE
									</fo:block>
								</fo:table-cell>
								 -->
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="80mm" text-align="center">
										Bezeichnung
									</fo:block>
								</fo:table-cell>
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="35mm" text-align="center">
										ArtikelNr
									</fo:block>
								</fo:table-cell>
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="15mm" text-align="center">
										Preis/VE
									</fo:block>
								</fo:table-cell>
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="20mm" text-align="center">
										Summe [EUR]
									</fo:block>
								</fo:table-cell>
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="12mm" text-align="center">
										UST
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<xsl:apply-templates select="details"/>
						</fo:table-body>
					</fo:table>

				</fo:block-container>
				
				<xsl:apply-templates select="details_1"/>
			
				<!-- Fusszeile -->
				<fo:block-container position="absolute" top="240mm" left="10mm">
					<fo:block font-size="10pt">
					Wir bitten um Angabe unserer Bestellnummer auf Rechnung und Lieferschein!\n
					</fo:block>
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.3mm" border-style="solid">
						<fo:table-column column-width="25mm"/>
						<fo:table-column column-width="40mm"/>
						<fo:table-body>
							<fo:table-row line-height="10pt">
								<fo:table-cell  border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" font-weight="bold">
										 Erstellt am:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" font-weight="bold" padding-left="1mm">
										 <xsl:value-of select="erstelldatum" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" font-weight="bold">
										 Wien, am:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" font-weight="bold" padding-left="1mm">
										 <xsl:value-of select="datum" />
									</fo:block>
								</fo:table-cell>
</fo:table-row>
								
<fo:table-row line-height="10pt">
<fo:table-cell border-width="0.2mm" border-style="solid">

									<fo:block font-size="10pt" font-weight="bold">
										 Telefonnr.:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" font-weight="bold" padding-left="1mm">
										 +43 1 333 40 77 - 212
									</fo:block>
								</fo:table-cell>
							
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.3mm" border-left-style="solid" border-right-style="solid" border-bottom-style="solid">
						<fo:table-column column-width="65.8mm"/>
						<fo:table-body>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block font-size="10pt" font-weight="bold">
										 Einkauf:\n\n\n\n
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>
				
				<!-- Summe -->
				<fo:block-container position="absolute" top="245mm" left="150mm">
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.3mm" border-style="solid">
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="25mm"/>
						<fo:table-body>
							<fo:table-row line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" font-weight="bold">
										 Total exkl.:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" font-weight="bold" content-width="25mm" text-align="right">
										 <xsl:value-of select="summe_netto" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" font-weight="bold">
										 USt.:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" font-weight="bold" content-width="25mm" text-align="right">
										 <xsl:value-of select="summe_mwst" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" font-weight="bold">
										 Total inkl.:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" font-weight="bold" content-width="25mm" text-align="right">
										 <xsl:value-of select="summe_brutto" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>

			</fo:flow>
		</fo:page-sequence>
	</xsl:template>
	
	<xsl:template match="details_1">
	<!-- 2. Seite beginnen wenn zu viele Details vorhanden sind -->	
	<fo:block font-size="16pt" 
	            font-family="sans-serif" 
	            space-after.optimum="15pt"
	            text-align="center"
	            break-before="page">
	      </fo:block>
		<!-- Tabelle die ueber der Bestelldetail Tabelle liegt, um die vertikalen Linien zu Zeichnen -->

				<fo:block-container position="absolute" top="25mm" left="9.8mm">
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2mm" border-style="solid">
						<fo:table-column column-width="7mm"/>
						<fo:table-column column-width="9.4mm"/>
						<!-- <fo:table-column column-width="6.2mm"/> -->
						<fo:table-column column-width="85.6mm"/><!-- 79,4 -->
						<fo:table-column column-width="34.8mm"/>
						<fo:table-column column-width="14.8mm"/>
						<fo:table-column column-width="19.8mm"/>
						<fo:table-column column-width="11.8mm"/>
						<fo:table-body>
							<fo:table-row line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm">
											<!-- pos -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="10mm">
										<!-- menge -->
									</fo:block>
								</fo:table-cell>
								<!--
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm">
									
									</fo:block>
								</fo:table-cell>
								 -->
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="79mm">
										<!-- bezeichnung -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="34mm">
										<!-- aritikelnummer -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="15mm">
										<!-- Preis/VE -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="20mm">
										<!-- Summe [EUR] -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-bottom-style="solid" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="12mm">
										<!-- UST -->
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row line-height="130mm">
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm">
											<!-- pos -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="10mm">
										<!-- menge -->
									</fo:block>
								</fo:table-cell>
								<!-- 
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm">
									
									</fo:block>
								</fo:table-cell>
								 -->
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="79mm">
										<!-- bezeichnung -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="34mm">
										<!-- aritikelnummer -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="15mm">
										<!-- Preis/VE -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="20mm">
										<!-- Summe [EUR] -->
									</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-left-style="solid">
									<fo:block font-size="8pt" font-weight="bold" content-width="12mm">
										<!-- UST -->
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>

				</fo:block-container>

				<!-- Bestelldetails -->
				<fo:block-container position="absolute" top="25mm" left="10mm">
					<fo:table table-layout="fixed" border-collapse="collapse">
						<fo:table-column column-width="7mm"/>
						<fo:table-column column-width="10mm"/>
						<!-- <fo:table-column column-width="7mm"/> -->
						<fo:table-column column-width="87mm"/><!-- 80 -->
						<fo:table-column column-width="35mm"/>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="12mm"/>
						<fo:table-body>
							<fo:table-row line-height="10pt">
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm" text-align="center">
										 Pos
									</fo:block>
								</fo:table-cell>
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="10mm" text-align="center">
										Menge
									</fo:block>
								</fo:table-cell>
								<!-- 
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="7mm" text-align="center">
										VE
									</fo:block>
								</fo:table-cell>
								 -->
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="80mm" text-align="center">
										Bezeichnung
									</fo:block>
								</fo:table-cell>
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="35mm" text-align="center">
										ArtikelNr
									</fo:block>
								</fo:table-cell>
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="15mm" text-align="center">
										Preis/VE
									</fo:block>
								</fo:table-cell>
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="20mm" text-align="center">
										Summe [EUR]
									</fo:block>
								</fo:table-cell>
								<fo:table-cell display-align="center">
									<fo:block font-size="8pt" font-weight="bold" content-width="12mm" text-align="center">
										UST
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<xsl:apply-templates select="detail"/>
						</fo:table-body>
					</fo:table>

				</fo:block-container>
	</xsl:template>
	
	<xsl:template match="details">
		<xsl:apply-templates select="detail"/>
	</xsl:template>
	<xsl:template match="detail">
		<fo:table-row line-height="10pt">
			<fo:table-cell display-align="center">
				<fo:block font-size="8pt" content-width="7mm" text-align="right">
					 <xsl:value-of select="position" />
				</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center">
				<fo:block font-size="8pt" content-width="10mm" text-align="center">
					<xsl:value-of select="menge" />
				</fo:block>
			</fo:table-cell>
			<!-- 
			<fo:table-cell display-align="center">
				<fo:block font-size="8pt" content-width="7mm" text-align="center">
					<xsl:value-of select="verpackungseinheit" />
				</fo:block>
			</fo:table-cell>
			 -->
			<fo:table-cell display-align="center">
				<fo:block font-size="8pt" content-width="79mm" padding-left="1mm">
					<xsl:value-of select="beschreibung" />
				</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center">
				<fo:block font-size="8pt" content-width="35mm">
					<xsl:value-of select="artikelnummer" />
				</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center">
				<fo:block font-size="8pt" content-width="14mm" text-align="right">
					<xsl:value-of select="preisprove" />
				</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center">
				<fo:block font-size="8pt" content-width="19mm" text-align="right">
					<xsl:value-of select="summe_netto" />
				</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center">
				<fo:block font-size="8pt" content-width="12mm" text-align="right">
					<xsl:value-of select="mwst" /><xsl:text> %</xsl:text>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>
</xsl:stylesheet>
