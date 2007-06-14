<?xml version="1.0" encoding="ISO-8859-15"?>
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
						<fo:external-graphic src="../skin/images/TWLogo_klein.jpg"  posx="140" posy="15" />
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
					<!-- Zahlungsbestätigung -->
					<fo:block text-align="center" font-size="20pt">
							ZAHLUNGSBESTÄTIGUNG
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
						Die Studiengangsleitung bestätigt hiermit, dass <xsl:value-of select="person/anrede" /><xsl:text> </xsl:text>
						<xsl:value-of select="person/titelpre" /><xsl:text> </xsl:text><xsl:value-of select="person/vorname" /><xsl:text> </xsl:text><xsl:value-of select="person/nachname" />
						<xsl:text> </xsl:text><xsl:value-of select="person/titelpost" />, geboren am <xsl:value-of select="person/geburtsdatum" />, folgende Einzahlungen getätigt hat:
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
					<fo:block><xsl:text>
					</xsl:text></fo:block>

					<!-- Unterschrift -->
					
					<fo:block text-align="left" font-size="10pt">
					Fachhochschule Technikum\nWien\nHöchstädtplatz 5\nA-1200 Wien\nZVR-Nr.: 074476526\nDVR-Nr.: 0928381
					</fo:block>
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