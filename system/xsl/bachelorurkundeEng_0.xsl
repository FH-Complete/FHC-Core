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
						<xsl:text>Diploma</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="91mm" left="16mm" height="20mm">
					<fo:block text-align="center" line-height="14pt" font-family="arial" font-size="10pt">
						<xsl:text>Pursuant to paragraph 6 subsection 1 of the Universities of Applied Sciences Studies Act\n
						(Austrian legal reference: Fachhochschul-Studiengesetz - FHStG, BGBl. Nr. </xsl:text>
						<xsl:value-of select="bescheidbgbl1" />
						<xsl:text> idgF)\n
						the University of Applied Sciences Council (Fachhochschulkollegium) awards
						</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="112mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="16pt" font-family="arial" font-size="16pt">
						<xsl:value-of select="anrede_engl" />
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
						<xsl:text>born </xsl:text>
						<xsl:value-of select="gebdatum" />
						<xsl:text> in </xsl:text>
						<xsl:if test="string-length(gebort)!=0">
						<xsl:value-of select="gebort" />
						<xsl:text>, </xsl:text>
						</xsl:if>
						<xsl:value-of select="geburtsnation_engl" />
						<xsl:text>, citizen of </xsl:text>
						<xsl:value-of select="staatsbuergerschaft_engl" />
						<xsl:text>,\n
						student of the university of applied sciences </xsl:text>
						<xsl:value-of select="stg_art_engl" />
						<xsl:text>'s degree program</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="139mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="20pt" font-family="arial" font-size="16pt">
						<xsl:value-of select="stg_bezeichnung_engl" />
					</fo:block>
					<fo:block text-align="center" line-height="10pt" font-family="arial" font-size="10pt" padding-top="8pt">
						<xsl:text>(program classification number </xsl:text>
						<xsl:value-of select="studiengang_kz" />
						<xsl:text>)</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="158mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="14pt" font-family="arial" font-size="10pt">
						<xsl:text>after successfully passing the diploma examination on </xsl:text>
						<xsl:value-of select="datum" />
						<xsl:text>\n
						at the University of Applied Sciences Technikum Wien (Fachhochschule Technikum Wien)\n
						in accordance with the directive of the Agency for Quality Assurance and Accreditation Austria dated 9.5.2012\n
						the academic degree</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="188mm" left="16mm" height="30mm">
					<fo:block text-align="center" line-height="16pt" font-family="arial" font-size="16pt">
						<xsl:value-of select="titel" />
					</fo:block>
					<fo:block text-align="center" line-height="10pt" font-family="arial" font-size="10pt" padding-top="8pt">
						<xsl:text>abbreviated</xsl:text>
					</fo:block>
					<fo:block text-align="center" line-height="16pt" font-family="arial" font-size="16pt" padding-top="13pt">
						<xsl:value-of select="akadgrad_kurzbz" />
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="217mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="10pt" font-family="arial" font-size="10pt">
						<xsl:text>Vienna, </xsl:text>
						<xsl:value-of select="sponsion" />
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="227mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="11pt" font-family="arial" font-size="10pt">
						<xsl:text>On behalf of the University of Applied Sciences Council:\n  
						The Rector</xsl:text>
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="255mm" left="16mm" height="10mm">
					<fo:block text-align="center" line-height="10pt" font-family="arial" font-size="10pt">
						<xsl:value-of select="rektor" />
					</fo:block>
				</fo:block-container>

			</fo:flow>
		</fo:page-sequence>

	</xsl:template>
</xsl:stylesheet>
