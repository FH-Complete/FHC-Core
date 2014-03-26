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
						<xsl:text>Diplom</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="91mm" left="16mm" height="20mm">
					<fo:block text-align="center" line-height="14pt" font-family="arial" font-size="10pt">
						<xsl:text>Gemäß § 10 Abs. 3 Z 9 des Bundesgesetzes über Fachhochschul-Studiengänge\n
						(Fachhochschul-Studiengesetz - FHStG), BGBl. Nr. </xsl:text>
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
						<xsl:text> den Lehrgang zur Weiterbildung</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="138mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="20pt" font-family="arial" font-size="16pt">
						<xsl:value-of select="stg_bezeichnung" />
					</fo:block>
					<fo:block text-align="center" line-height="10pt" font-family="arial" font-size="10pt" padding-top="5pt">
						<xsl:text>(Lehrgangsnummer 0050</xsl:text>
						<xsl:value-of select="translate(studiengang_kz, '-','')" />
						<xsl:text>)</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="155mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="14pt" font-family="arial" font-size="10pt">
						<xsl:text>an der Fachhochschule Technikum Wien abgeschlossen hat,\n
						den Abschluss-Grad</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="170mm" left="16mm" height="30mm">
					<fo:block text-align="center" line-height="16pt" font-family="arial" font-size="16pt">
						<xsl:value-of select="titel" />
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="195mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="10pt" font-family="arial" font-size="10pt">
						<xsl:text>Wien, </xsl:text>
						<xsl:value-of select="sponsion" />
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="205mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="11pt" font-family="arial" font-size="10pt">
						<xsl:text>Für das Fachhochschulkollegium\n  
						Der Rektor</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="240mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="10pt" font-family="arial" font-size="10pt">
						<xsl:value-of select="rektor" />
					</fo:block>
				</fo:block-container>

			</fo:flow>
		</fo:page-sequence>

	</xsl:template>
</xsl:stylesheet>
