<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />

	<xsl:template match="abschlusspruefung">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="297mm" page-width="210mm" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm" />
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="pruefung" />
		</fo:root>
	</xsl:template>

	<xsl:template match="pruefung">
		<fo:page-sequence master-reference="PageMaster">

			<fo:flow flow-name="xsl-region-body">

				<fo:block-container position="absolute" top="64mm" left="16mm" height="20mm">
					<fo:block text-align="center" line-height="30pt" font-family="arial" font-size="28pt">
						<xsl:text>Diplom-Urkunde</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="91mm" left="16mm" height="20mm">
					<fo:block text-align="center" line-height="14pt" font-family="arial" font-size="10pt">
						<xsl:text>Gemäß § 6 Abs. 1 des Bundesgesetzes über Fachhochschul-Studiengänge (Fachhochschul-\n
						Studiengesetz - FHStG), BGBl. Nr. </xsl:text>
						<xsl:value-of select="bescheidbgbl1" />
						<xsl:text> idgF,\n
						verleiht das Fachhochschulkollegium
						</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="112mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="16pt" font-family="arial" font-size="16pt">
						<xsl:value-of select="anrede" />
						<xsl:text>   </xsl:text>
						<xsl:value-of select="titelpre" />
						<xsl:text>   </xsl:text>
						<xsl:value-of select="vorname" />
						<xsl:text> </xsl:text>
						<xsl:value-of select="vornamen" />
						<xsl:text> </xsl:text>
						<xsl:value-of select="nachname" />
						<xsl:if test="string-length(titelpost)!=0">
						<xsl:text>, </xsl:text>
						<xsl:value-of select="titelpost" />
						</xsl:if>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="124mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="14pt" font-family="arial" font-size="10pt">
						<xsl:text>geboren am </xsl:text>
						<xsl:value-of select="gebdatum" />
						<xsl:text> in </xsl:text>
						<xsl:if test="string-length(gebort)!=0">
						<xsl:value-of select="gebort" />
						<xsl:text>, </xsl:text>
						</xsl:if>
						<xsl:value-of select="geburtsnation" />
						<xsl:text>, Staatsbürgerschaft </xsl:text>
						<xsl:value-of select="staatsbuergerschaft" />
						<xsl:text>,\n</xsl:text>
						<xsl:choose>
							<xsl:when test="contains(anrede, 'err')">
								<xsl:text>der</xsl:text>
							</xsl:when>
							<xsl:when test="contains(anrede, 'rau')">
								<xsl:text>die</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>die/der</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:text> den Fachhochschul-</xsl:text>
						<xsl:value-of select="stg_art" />
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="138mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="20pt" font-family="arial" font-size="16pt">
						<xsl:value-of select="stg_bezeichnung" />
					</fo:block>
					<fo:block text-align="center" line-height="10pt" font-family="arial" font-size="10pt" padding-top="8pt">
						<xsl:text>(Studiengangskennzahl </xsl:text>
						<xsl:value-of select="studiengang_kz" />
						<xsl:text>)</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="157mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="14pt" font-family="arial" font-size="10pt">
						<xsl:text>an der Fachhochschule Technikum Wien\n
						durch Ablegung der Bachelor-Prüfung am </xsl:text>
						<xsl:value-of select="datum" />
						<xsl:text> ordnungsgemäß abgeschlossen hat,\n
						den mit Bescheid des Board der Agentur für Qualitätssicherung und Akkreditierung Austria vom 9.5.2012,\n
						GZ FH12020016 idgF, gemäß § 6 Abs. 2 FHStG\n
						festgesetzten akademischen Grad</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="188mm" left="16mm" height="30mm">
					<fo:block text-align="center" line-height="16pt" font-family="arial" font-size="16pt">
						<xsl:value-of select="titel" />
					</fo:block>
					<fo:block text-align="center" line-height="10pt" font-family="arial" font-size="10pt" padding-top="8pt">
						<xsl:text>abgekürzt</xsl:text>
					</fo:block>
					<fo:block text-align="center" line-height="16pt" font-family="arial" font-size="16pt" padding-top="13pt">
						<xsl:value-of select="akadgrad_kurzbz" />
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="216mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="10pt" font-family="arial" font-size="10pt">
						<xsl:text>Wien, </xsl:text>
						<xsl:value-of select="sponsion" />
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="226mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="11pt" font-family="arial" font-size="10pt">
						<xsl:text>Für das Fachhochschulkollegium\n  
						Der Rektor</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="254mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="10pt" font-family="arial" font-size="10pt">
						<xsl:value-of select="rektor" />
					</fo:block>
				</fo:block-container>

			</fo:flow>
		</fo:page-sequence>

	</xsl:template>
</xsl:stylesheet>
