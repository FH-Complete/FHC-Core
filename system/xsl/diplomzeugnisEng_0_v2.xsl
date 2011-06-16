<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="abschlusspruefung">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="297mm" page-width="210mm" margin="10mm 10mm 10mm 10mm" master-name="PageMaster">
					<fo:region-body margin="0mm 0mm 0mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="pruefung"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="pruefung">					
		<fo:page-sequence master-reference="PageMaster">
			<fo:flow flow-name="xsl-region-body" >

				<fo:block-container position="absolute" top="43mm" left="21.5mm">
				<fo:block line-height="19pt" font-family="arial" font-size="16pt" font-weight="bold" content-width="90mm"><xsl:text> MASTER OF SCIENCE\n CERTIFICATE</xsl:text></fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="65mm" left="21.5mm">
				<fo:block line-height="16pt" font-family="arial" font-size="16pt" content-width="90mm"><xsl:text> Degree Program</xsl:text></fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="72mm" left="21.5mm">
				<fo:block line-height="18pt" font-family="arial" font-size="16pt" font-weight="bold" content-width="90mm"><xsl:text> </xsl:text><xsl:value-of select="stg_bezeichnung_engl"/></fo:block>
				</fo:block-container>
 
				<fo:block-container position="absolute" top="94.5mm" left="117.5mm">
				<fo:block line-height="11pt" font-family="arial" font-size="9pt" content-width="70mm" text-align="right"><xsl:text>StudentID: </xsl:text><xsl:value-of select="matrikelnr" /><xsl:text>\nProgram Code: </xsl:text><xsl:value-of select="studiengang_kz" /><xsl:text> </xsl:text></fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="132mm" left="23mm">
				<fo:block line-height="11pt" font-family="arial" font-size="10pt"><xsl:text>First Name/Last Name:\n</xsl:text></fo:block>
				<fo:block line-height="11pt" font-family="arial" font-size="10pt"><xsl:text>Date of Birth:</xsl:text></fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="132mm" left="68mm">
				<fo:block line-height="11pt" font-family="arial" font-size="10pt" font-weight="bold">
				<xsl:if test="string-length(titelpre)!=0"><xsl:value-of select="titelpre" /><xsl:text> </xsl:text></xsl:if>
				<xsl:value-of select="vorname" /><xsl:text> </xsl:text>
				<xsl:value-of select="vornamen" /><xsl:text> </xsl:text>
				<xsl:value-of select="nachname" />
				<xsl:if test="string-length(titelpost)!=0"><xsl:text>, </xsl:text><xsl:value-of select="titelpost" /></xsl:if>
				<xsl:text>\n</xsl:text>
				</fo:block>
				<fo:block line-height="11pt" font-family="arial" font-size="10pt">
				<xsl:value-of select="gebdatum" />
				</fo:block>
				</fo:block-container>
 
				<fo:block-container position="absolute" top="153mm" left="23mm">
				<fo:table table-layout="fixed" border-collapse="collapse">
						<fo:table-column column-width="63mm" />
						<fo:table-column column-width="100mm" />
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell border-width="0.2mm" border-style="solid"><fo:block vertical-align="top" line-height="14pt" font-family="arial" font-size="10pt" font-weight="bold" content-width="60mm"><xsl:text> Final assessment</xsl:text></fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"><fo:block vertical-align="top" line-height="14pt" font-family="arial" font-size="10pt" font-weight="bold" content-width="95mm"><xsl:text> </xsl:text><xsl:value-of select="abschlussbeurteilung_kurzbzEng" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell border-width="0.2mm" border-style="solid"><fo:block vertical-align="top" line-height="14pt" font-family="arial" font-size="10pt" content-width="60mm"><xsl:text> Part 1:</xsl:text></fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"><fo:block vertical-align="top" line-height="14pt" font-family="arial" font-size="10pt" content-width="95mm"><xsl:text> Master Thesis</xsl:text></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell border-width="0.2mm" border-style="solid"><fo:block vertical-align="top" line-height="14pt" font-family="arial" font-size="10pt" content-width="60mm"><xsl:text> Title of Master Thesis:</xsl:text></fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"><fo:block vertical-align="top" line-height="14pt" font-family="arial" font-size="10pt" content-width="95mm"><xsl:text> </xsl:text><xsl:value-of select="themenbereich" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell border-width="0.2mm" border-style="solid"><fo:block vertical-align="top" line-height="14pt" font-family="arial" font-size="10pt" content-width="60mm"><xsl:text> Part 2:</xsl:text></fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"><fo:block vertical-align="top" line-height="14pt" font-family="arial" font-size="10pt" content-width="95mm"><xsl:text> Final examination</xsl:text></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell border-width="0.2mm" border-style="solid"><fo:block vertical-align="top" line-height="14pt" font-family="arial" font-size="10pt" content-width="60mm"><xsl:text> Date:</xsl:text></fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"><fo:block vertical-align="top" line-height="14pt" font-family="arial" font-size="10pt" content-width="95mm"><xsl:text> </xsl:text><xsl:value-of select="datum" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell number-columns-spanned="2"><fo:block-container height="7mm"></fo:block-container></fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell number-columns-spanned="2">
								<fo:block line-height="8pt" font-family="arial" font-size="6pt" content-width="150mm"><xsl:text>Final assessment: passed with distinction, passed with merit, Passed \nPursuant to section 5 subsection 1 of the University of Applied Sciences Studies Act (FHStG), BGBI. Nr. </xsl:text><xsl:value-of select="bescheidbgbl1" /><xsl:text> idgF</xsl:text></fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>

				<fo:block-container position="absolute" top="244mm" left="23mm">
					<fo:table table-layout="fixed" border-collapse="collapse">
						<fo:table-column column-width="73mm" />
						<fo:table-column column-width="17mm" />
						<fo:table-column column-width="73mm" />
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell>
									<fo:block line-height="12pt" font-size="9pt">
										Vienna, <xsl:value-of select="ort_datum" />
									</fo:block>
									<fo:block line-height="3pt" font-size="3pt" />
								</fo:table-cell>
								<fo:table-cell></fo:table-cell>
								<fo:table-cell></fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell border-top-style="dotted">
								<fo:block line-height="13pt" font-family="arial" font-size="9pt"><xsl:text>Place, Date</xsl:text></fo:block>
								</fo:table-cell>
								<fo:table-cell></fo:table-cell>
								<fo:table-cell border-top-style="dotted">
								<fo:block line-height="13pt" font-family="arial" font-size="9pt"><xsl:value-of select="vorsitz_nachname" /><xsl:text>\nChair</xsl:text></fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>
			</fo:flow>
		</fo:page-sequence>
	
	</xsl:template>
</xsl:stylesheet >
