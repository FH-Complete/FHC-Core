<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="learningagreement">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set><fo:simple-page-master format="A4" orientation="P" master-name="PageMaster">				
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="student"/>
		</fo:root>
	</xsl:template>
	
		<xsl:template match="student">
		<fo:page-sequence master-reference="PageMaster">
			<fo:flow flow-name="xsl-region-body" >
				<fo:block>
					<fo:external-graphic src="../../../skin/images/logo.jpg"  posx="120" posy="15" height="20.44mm" width="69.99mm"/>
				</fo:block>
				<fo:block-container position="absolute" top="35mm" left="15mm">
				<fo:block line-height="11pt" font-family="arial" font-size="12pt" font-weight="bold">
						<xsl:text>Learning Agreement  \n\n</xsl:text>
				</fo:block>
					<fo:block line-height="11pt" font-family="arial" font-size="10pt" font-weight="bold">
						<xsl:text>First Name/Last Name:  </xsl:text>
						<xsl:value-of select="vorname"/>
						<xsl:text> </xsl:text>
						<xsl:value-of select="nachname"/>
					</fo:block>
					<fo:block line-height="11pt" font-family="arial" font-size="10pt">
						<xsl:text>\n</xsl:text>
						<xsl:text>Date of Birth: </xsl:text>
						<xsl:value-of select="gebdatum" />
						<xsl:text>\n\n</xsl:text>
						<xsl:text>University: </xsl:text>
						<xsl:value-of select="universitaet" />
						<xsl:text>\n\n</xsl:text>						
						<xsl:text>Studies abroad from </xsl:text>
						<xsl:value-of select="von" />
						<xsl:text> to </xsl:text>
						<xsl:value-of select="bis" />
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="85mm" left="15mm">
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2pt" border-style="solid">
						<fo:table-column column-width="85mm"/>
						<fo:table-column column-width="60mm"/>
						<fo:table-column column-width="20mm"/>
						<fo:table-column column-width="15mm"/>
						<fo:table-body>
							<fo:table-row line-height="14pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
									<fo:block font-size="10pt" font-weight="bold">
										 Course Title at UAS Technikum Wien
										</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  display-align="center">
									<fo:block font-size="10pt" font-weight="bold" content-width="50mm" text-align="center">
										Degree Program
										</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
									<fo:block font-size="10pt" font-weight="bold" content-width="20mm" text-align="center">
										Semester
										</fo:block>
								</fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
									<fo:block font-size="10pt" font-weight="bold" content-width="15mm" text-align="center">
										ECTS
										</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<xsl:apply-templates select="lehrveranstaltung"/>

						</fo:table-body>
					</fo:table>
					
					<fo:table table-layout="fixed" border-collapse="collapse" border-width="0.2pt" border-style="solid">
						<fo:table-column column-width="181.2mm"/>
						<fo:table-body>
							<xsl:apply-templates select="deutschkurs1"/>
							<xsl:apply-templates select="deutschkurs2"/>
							<xsl:apply-templates select="deutschkurs3"/>
							<xsl:apply-templates select="bachelorthesis"/>
							<xsl:apply-templates select="masterthesis"/>
						</fo:table-body>
					</fo:table>
					
					<fo:block font-size="10pt">\n \n PLEASE NOTE: For courses offered in German and/or English sufficient language competencies are required</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="230mm" left="35mm">
					<fo:block line-height="11pt" font-family="arial" font-size="10pt" font-weight="bold">
						<xsl:text>_______________________________</xsl:text>
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="235mm" left="49mm">
					<fo:block line-height="11pt" font-family="arial" font-size="9pt">
						<xsl:text>(Signature Student)</xsl:text>
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="250mm" left="35mm">
					<fo:block line-height="11pt" font-family="arial" font-size="10pt" font-weight="bold">
						<xsl:text>_______________________________</xsl:text>
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="255mm" left="39mm">
					<fo:block line-height="10pt" font-family="arial" font-size="9pt">
						<xsl:text>(Signature Department Coordinator)</xsl:text>
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="265mm" left="35mm">
					<fo:block line-height="11pt" font-family="arial" font-size="10pt" font-weight="bold">
						<xsl:text>_______________________________</xsl:text>
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="270mm" left="39mm">
					<fo:block line-height="10pt" font-family="arial" font-size="9pt">
						<xsl:text>(Signature International Coordinator)</xsl:text>
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="250mm" left="159mm">
					<fo:block line-height="10pt" font-family="arial" font-size="9pt">
						<xsl:text>Institutional Stamp</xsl:text>
					</fo:block>
				</fo:block-container>
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>	
	
	<xsl:template match="lehrveranstaltung">
		<fo:table-row line-height="12pt">
			<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
				<fo:block font-size="10pt" content-width="95mm">
					<xsl:text> </xsl:text>
					<xsl:value-of select="bezeichnung"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="10pt" content-width="55mm" text-align="center">
					<xsl:text> </xsl:text>
					<xsl:value-of select="studiengang"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="10pt" content-width="20mm" text-align="center">
					<xsl:text> </xsl:text>
					<xsl:value-of select="semester"/>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" display-align="center">
				<fo:block font-size="10pt" content-width="15mm" text-align="center">
					<xsl:text> </xsl:text>
					<xsl:value-of select="ects"/>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>
	<xsl:template match="bachelorthesis">
		<fo:table-row line-height="12pt">
			<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
				<fo:block font-size="10pt" content-width="180mm">
					<xsl:text> Bachelor Thesis: </xsl:text>
					<xsl:value-of select="."/>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>
	<xsl:template match="masterthesis">
		<fo:table-row line-height="12pt">
			<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
				<fo:block font-size="10pt" content-width="95mm">
					<xsl:text> Master Thesis: </xsl:text>
					<xsl:value-of select="."/>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>
	<xsl:template match="deutschkurs1">
		<fo:table-row line-height="12pt">
			<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
				<fo:block font-size="10pt" content-width="95mm">
					<xsl:text></xsl:text>
					<xsl:value-of select="."/>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>
	<xsl:template match="deutschkurs2">
		<fo:table-row line-height="12pt">
			<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
				<fo:block font-size="10pt" content-width="95mm">
					<xsl:text></xsl:text>
					<xsl:value-of select="."/>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>	
	<xsl:template match="deutschkurs3">
		<fo:table-row line-height="12pt">
			<fo:table-cell display-align="center" border-width="0.2mm" border-style="solid">
				<fo:block font-size="10pt" content-width="95mm">
					<xsl:text></xsl:text>
					<xsl:value-of select="."/>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>	
</xsl:stylesheet >
