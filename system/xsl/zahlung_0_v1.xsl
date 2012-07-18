<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="konto">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="297mm" page-width="210mm" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<fo:page-sequence master-reference="PageMaster">
				<fo:flow flow-name="xsl-region-body" >
					<!-- Logo -->
					<fo:block>
						<!--<fo:external-graphic src="../skin/images/logo.jpg"  posx="140" posy="15" width="60mm" height="20mm"/>-->
						<fo:external-graphic  posx="140" posy="15" width="60mm" height="20mm" >
							 <xsl:attribute name="src">
							  	<xsl:value-of select="person/logopath" />logo.jpg
							 </xsl:attribute>
						</fo:external-graphic>
					</fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<!-- Datum  -->
					<fo:block text-align="right" font-size="10pt">
					    Wien, am<xsl:text> </xsl:text><xsl:value-of select="person/tagesdatum" />
					</fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<!-- Studiengang -->
					<fo:block text-align="right" font-size="10pt">
					    Studiengang
					</fo:block>
					<fo:block text-align="right" font-size="12pt">
					    <xsl:value-of select="person/studiengang" />
 					</fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block text-align="right" font-size="10pt">
					    Sozialversicherungsnummer/Ersatzkennzeichen
					</fo:block>
					<fo:block text-align="right" font-size="12pt">
					    <xsl:value-of select="person/sozialversicherungsnummer" />
					</fo:block>
					<fo:block text-align="right" font-size="12pt">
					    <xsl:value-of select="person/ersatzkennzeichen" />
					</fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<!-- Zahlungsbestaetigung -->
					<fo:block text-align="center" font-size="20pt">
					<xsl:choose>
					  <xsl:when test="buchung/rueckerstattung">
					    AUSZAHLUNGSBESTÄTIGUNG
					  </xsl:when>
					  <xsl:otherwise>
					   ZAHLUNGSBESTÄTIGUNG
					  </xsl:otherwise>
					</xsl:choose>
					</fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block text-align="left" font-size="10pt">
					<xsl:choose>
					  <xsl:when test="buchung/rueckerstattung">
					    \n <xsl:value-of select="person/name_gesamt" />, geboren am <xsl:value-of select="person/geburtsdatum" />, Personenkennzahl <xsl:value-of select="person/matrikelnr" />, bestätigt hiermit, dass die Studiengangsleitung folgende Auszahlungen getätigt hat:
					  </xsl:when>
					  <xsl:otherwise>
					   \n Die Studiengangsleitung bestätigt hiermit, dass <xsl:value-of select="person/name_gesamt" />, geboren am <xsl:value-of select="person/geburtsdatum" />, Personenkennzahl <xsl:value-of select="person/matrikelnr" />, folgende Einzahlungen getätigt hat:
					  </xsl:otherwise>
					</xsl:choose>
					</fo:block>						
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>

					<!-- Tabelle -->
					
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="50mm"/>
						<fo:table-column column-width="70mm"/>
						<fo:table-column column-width="20mm"/>

						<fo:table-body>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Datum</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Nummer</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Buchungstyp</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Buchungstext</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Betrag in EUR</fo:block></fo:table-cell>
							</fo:table-row>
							<xsl:apply-templates select="buchung"/>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="buchungsdatum" /></fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="buchungsnr" /></fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="buchungstyp" /></fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="buchungstext" /></fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="betrag" /></fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<!-- Tabelle ENDE -->
					<fo:block><xsl:text>
					</xsl:text></fo:block>

					

					<!-- Unterschrift -->
					
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="50mm"/>
						<fo:table-column column-width="50mm"/>

						<fo:table-body>
							<fo:table-row line-height="10pt">
								<fo:table-cell>
									<fo:block text-align="left" font-size="10pt">
									Fachhochschule Technikum Wien\n Höchstädtplatz 5\n A-1200 Wien\n ZVR-Nr.: 074476526\n DVR-Nr.: 0928381
									</fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block text-align="right" font-size="10pt">
									<xsl:if test="buchung/rueckerstattung">
										<xsl:value-of select="person/name_gesamt" />
									</xsl:if>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					
					
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
		
	<xsl:template match="buchung">
		<fo:table-row  line-height="10pt">
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:text> </xsl:text><xsl:value-of select="buchungsdatum" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:text> </xsl:text><xsl:value-of select="buchungsnr" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:text> </xsl:text><xsl:value-of select="buchungstyp_beschreibung" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:text> </xsl:text><xsl:value-of select="buchungstext" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="20mm" text-align="right"><xsl:text> </xsl:text><xsl:value-of select="betrag" /></fo:block></fo:table-cell>
		</fo:table-row>
	</xsl:template>

</xsl:stylesheet >