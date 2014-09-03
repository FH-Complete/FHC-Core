<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="studienerfolge">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="297mm" page-width="210mm" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="studienerfolg"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="studienerfolg">
			<fo:page-sequence master-reference="PageMaster">
				<fo:flow flow-name="xsl-region-body" >
				
					<!-- Logo -->
					<fo:block>
						<!--<fo:external-graphic src="../skin/images/logo.jpg"  posx="140" posy="15" width="60mm" height="20mm" />-->
						<fo:external-graphic  posx="175" posy="20" width="25mm" height="25mm" >
							 <xsl:attribute name="src">
							  	<xsl:value-of select="logopath" />logo.jpg
							 </xsl:attribute>
						</fo:external-graphic>
					</fo:block>
					
					<!-- Titel -->
					<fo:block-container position="absolute" top="20mm" left="15mm">
						<fo:block font-size="16pt">Katholisch-Theologische Privatuniversität Linz</fo:block>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="26mm" left="15mm">
						<fo:block font-size="12pt">Bethlehemstraße 20\nA-4020 Linz</fo:block>
						<fo:block font-size="6pt">\nDVR-Nr.: 0029874(1739)</fo:block>
					</fo:block-container>

					
					<fo:block-container position="absolute" top="45mm" left="15mm">
						<fo:block text-align="left" line-height="20pt" font-family="sans-serif" font-size="16pt">
							<xsl:text>Bestätigung des Studienerfolges</xsl:text>
						</fo:block>
					</fo:block-container> 
					
					<!--FINANZAMT-->
					<fo:block-container position="absolute" top="55mm" left="15mm">
						<fo:block text-align="left" line-height="10pt" font-family="sans-serif" font-size="8pt">
							<xsl:value-of select="finanzamt" />
						</fo:block>
					</fo:block-container>
					
					<!-- NAME - GEBURTSDATUM - MATRIKELNUMMER -->
					<fo:block-container position="absolute" top="60mm" left="15mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="120mm"/>
							<fo:table-column column-width="30mm"/>
							<fo:table-column column-width="30mm"/>
								<fo:table-body>
						            <fo:table-row line-height="14pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="8pt" content-width="45mm" text-align="left">
												<xsl:text> Familienname, Vorname\n</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="8pt" content-width="45mm" text-align="left">
												<xsl:text> Geburtsdatum\n</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="8pt" content-width="45mm" text-align="left">
												<xsl:text> Personenkennzeichen\n</xsl:text>
											</fo:block>
										</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="65mm" left="16mm">
						<fo:block text-align="left" line-height="14pt" font-family="sans-serif" font-size="10pt" font-weight="bold">
							<xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" />
						</fo:block>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="65mm" left="137mm">
						<fo:block text-align="left" line-height="14pt" font-family="sans-serif" font-size="10pt" font-weight="bold">
							<xsl:value-of select="gebdatum" />
						</fo:block>
					</fo:block-container>
					<fo:block-container position="absolute" top="65mm" left="167mm">
						<fo:block text-align="left" line-height="14pt" font-family="sans-serif" font-size="10pt" font-weight="bold">
							<xsl:value-of select="matrikelnr" />
						</fo:block>
					</fo:block-container>
					
					<!--STUDIENGANG UND KENNZAHL -->
					<fo:block-container position="absolute" top="70.8mm" left="15mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="150.4mm"/>
							<fo:table-column column-width="30mm"/>
								<fo:table-body>
						            <fo:table-row line-height="14pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="8pt" content-width="45mm" text-align="left">
												<xsl:text> Studiengang\n</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="8pt" content-width="45mm" text-align="left">
												<xsl:text> Kennzahl\n</xsl:text>
											</fo:block>
										</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="76mm" left="16mm">
						<fo:block text-align="left" line-height="14pt" font-family="sans-serif" font-size="10pt" font-weight="bold">
							<xsl:value-of select="studiengang" />
						</fo:block>
					</fo:block-container>
					<fo:block-container position="absolute" top="76mm" left="167mm">
						<fo:block text-align="left" line-height="14pt" font-family="sans-serif" font-size="10pt" font-weight="bold">
							<xsl:value-of select="studiengang_kz" />
						</fo:block>
					</fo:block-container>
					
					<!-- Studiensemester - Ausbildungssemester -->
					<fo:block-container position="absolute" top="81.6mm" left="15mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="120mm"/>
							<fo:table-column column-width="60.4mm"/>
								<fo:table-body>
						            <fo:table-row line-height="14pt">
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="8pt" content-width="45mm" text-align="left">
												<xsl:text> Aktuelles Studiensemester\n</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-width="0.2mm" border-style="solid">
											<fo:block font-family="sans-serif" font-size="8pt" content-width="45mm" text-align="left">
												<xsl:text> Aktuelles Ausbildungssemester\n</xsl:text>
											</fo:block>
										</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="87mm" left="16mm">
						<fo:block text-align="left" line-height="10pt" font-family="sans-serif" font-size="10pt" font-weight="bold">
							<xsl:value-of select="studiensemester_aktuell" />
						</fo:block>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="87mm" left="137mm">
						<fo:block text-align="left" line-height="10pt" font-family="sans-serif" font-size="10pt" font-weight="bold">
							<xsl:value-of select="semester_aktuell" />
						</fo:block>
					</fo:block-container>
					
					<!-- TABELLE -->
					<fo:block-container position="absolute" top="110mm" left="15mm">
						<fo:block text-align="left" line-height="14pt" font-family="sans-serif" font-size="10pt" font-weight="bold">
							<xsl:text>Folgende Prüfungen wurden erfolgreich abgelegt:</xsl:text>
						</fo:block>
					</fo:block-container>
					
 					<fo:block-container position="absolute" top="120mm" left="15mm">
						<fo:table table-layout="fixed" border-collapse="separate" border-width="0.2mm" border-style="solid">
						<fo:table-column column-width="70mm"/>
						<fo:table-column column-width="24mm"/>
						<fo:table-column column-width="32mm"/>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="15mm"/>					

							<fo:table-body>
								<fo:table-row  line-height="10pt">
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold">
											<xsl:text> Lehrveranstaltung</xsl:text>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold" content-width="24mm" text-align="center">
											 <xsl:text>Studiensemester</xsl:text>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold" content-width="32mm" text-align="center">
											 <xsl:text>Ausbildungssemester</xsl:text>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold" content-width="15mm" text-align="center">
											<xsl:text>ECTS</xsl:text>
										</fo:block>
									</fo:table-cell>	
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold" content-width="20mm" text-align="center">
											 <xsl:text>Datum</xsl:text>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold" content-width="15mm" text-align="center">
											 <xsl:text>Benotung</xsl:text>
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
								
								<xsl:apply-templates select="unterrichtsfach"/>
								    <fo:table-row  line-height="10pt">
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold">
											<xsl:text> Semestersumme:</xsl:text>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold" content-width="24mm" text-align="center">
											 <xsl:value-of select="studiensemester_kurzbz" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold" content-width="32mm" text-align="center">
											 <xsl:text></xsl:text>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold" content-width="15mm" text-align="center">
											<xsl:value-of select="gesamtects" />
										</fo:block>
									</fo:table-cell>	
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold" content-width="20mm" text-align="center">
											 <xsl:text>Schnitt:</xsl:text>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell border-width="0.2mm" border-style="solid">
										<fo:block font-size="8pt" font-weight="bold" content-width="15mm" text-align="center">
											 <xsl:value-of select="schnitt" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
						<fo:block>
						\n\n
						</fo:block>
						<fo:table>
						<fo:table-column column-width="35mm"/>
						<fo:table-column column-width="145mm"/>
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell  border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt">Datum: <xsl:value-of select="datum" /></fo:block>
									</fo:table-cell>
									<fo:table-cell  border-width="0.2mm" border-style="solid">
									<fo:block font-size="10pt" text-align="right" content-width="145mm">Gilt auch ohne Unterschrift und Stempel</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
						<fo:table>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="155mm"/>
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell>
									<fo:block font-size="7pt">Benotung: </fo:block>
									</fo:table-cell>
									<fo:table-cell>
									<fo:block font-size="7pt">Sehr gut (1), Gut (2), Befriedigend (3), Genügend (4), Nicht genügend (5), angerechnet (ar), mit Erfolg teilgenommen (met),</fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell>
									<fo:block font-size="7pt"></fo:block>
									</fo:table-cell>
									<fo:table-cell>
									<fo:block font-size="7pt"><!--bestanden (b), approbiert (ap), erfolgreich absolviert (ea), nicht erfolgreich absolviert (nea), nicht teilgenommen (nt)--></fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
					 
			</fo:flow>
		</fo:page-sequence>
	
	</xsl:template>
	
	<xsl:template match="unterrichtsfach">
		<fo:table-row  line-height="10pt">
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="70mm"><xsl:text> </xsl:text><xsl:value-of select="bezeichnung" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="24mm" text-align="center"><xsl:text> </xsl:text><xsl:value-of select="../studiensemester_kurzbz" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="32mm" text-align="center"><xsl:text> </xsl:text><xsl:value-of select="../semester" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="15mm" text-align="center"><xsl:text> </xsl:text><xsl:value-of select="ects" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="20mm" text-align="center"><xsl:text> </xsl:text><xsl:value-of select="benotungsdatum" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="15mm" text-align="center"><xsl:text> </xsl:text><xsl:value-of select="note" /></fo:block></fo:table-cell>
		</fo:table-row>
	</xsl:template>
</xsl:stylesheet >