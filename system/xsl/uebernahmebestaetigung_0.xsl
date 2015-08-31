<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="betriebsmittelperson">
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
						<fo:external-graphic src="../skin/images/logo.jpg"  posx="140" posy="15" width="60mm" height="20mm"/>
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
					<fo:block font-size="15pt">
					Übernahmebestätigung
					</fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block><xsl:text>
					</xsl:text></fo:block>
					<fo:block text-align="left" font-size="10pt">
					Durch Unterzeichnung dieses Formulars wird die Übernahme von inventarisierter Ware laut unten stehender Angaben bestätigt.
					</fo:block>						
					<fo:block><xsl:text>
					</xsl:text></fo:block>


					<!-- Tabelle -->
					
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="50mm"/>
						<fo:table-column column-width="130mm"/>

						<fo:table-body>
							<fo:table-row  line-height="40pt">
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center" font-weight="bold"> Vor- und Zuname</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center"><xsl:text> </xsl:text><xsl:value-of select="titelpre" /><xsl:text> </xsl:text><xsl:value-of select="vorname" /><xsl:text> </xsl:text><xsl:value-of select="nachname" /><xsl:text> </xsl:text><xsl:value-of select="titelpost" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="40pt">
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center" font-weight="bold"> Ware/Bezeichnung</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center"><xsl:text> </xsl:text><xsl:value-of select="typ" /><xsl:text> </xsl:text><xsl:value-of select="substring(beschreibung,0,50)" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="40pt">
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center" font-weight="bold"> Inventarnummer</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center"><xsl:text> </xsl:text><xsl:value-of select="inventarnummer" /><xsl:text> </xsl:text><xsl:value-of select="nummer" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="40pt">
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center" font-weight="bold"> Organisationseinheit</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center"><xsl:text> </xsl:text><xsl:value-of select="organisationseinheit" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="40pt">
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center" font-weight="bold"> Kaution</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center"><xsl:text> </xsl:text><xsl:value-of select="kaution" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="40pt">
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center" font-weight="bold"> Übernommen am</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" display-align="center" border-style="solid" ><fo:block font-size="11pt" vertical-align="center"><xsl:text> </xsl:text><xsl:value-of select="ausgegebenam" /></fo:block></fo:table-cell>
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

					<!-- Unterschrift -->
					<fo:block text-align="right" font-size="11pt">
					___________________________________
					</fo:block>
					<fo:block text-align="right" font-size="11pt">
					(Unterschrift)
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
					<fo:block text-align="left" font-size="8pt">
						Fachhochschule Technikum Wien, Höchstädtplatz 5, A-1200 Wien
					</fo:block>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
</xsl:stylesheet >
