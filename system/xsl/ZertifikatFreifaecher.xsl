<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="zertifikate">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="297mm" page-width="210mm" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="zertifikat"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="zertifikat">					
			<fo:page-sequence master-reference="PageMaster">
				<fo:flow flow-name="xsl-region-body" >
	
					<fo:block-container position="absolute" top="50mm" left="30mm" height="20mm">
						<fo:block text-align="left" line-height="20pt" font-family="sans-serif" font-size="22pt" font-weight="bold">
							<fo:inline font-weight="900">
							<xsl:text>Z e r t i f i k a t </xsl:text>
							</fo:inline>
						</fo:block>
					</fo:block-container> 

					<fo:block-container position="absolute" top="68mm" left="30mm">
						<fo:block line-height="14pt" font-family="sans-serif" font-size="10pt">
							<xsl:text>über die Teilnahme von Herrn/Frau</xsl:text>
						</fo:block>
					</fo:block-container>
					
					<fo:block-container position="absolute" top="80mm" left="30mm">
						<fo:block content-width="150mm" line-height="20pt" font-family="sans-serif" font-size="18pt">
							<xsl:value-of select="name" />
						</fo:block>
					</fo:block-container>		
					
					<fo:block-container position="absolute" top="90mm" left="30mm">
						<fo:block content-width="80mm" line-height="14pt" font-family="sans-serif" font-size="10pt">
							<xsl:text>Personenkennzeichen: </xsl:text><xsl:value-of select="matrikelnr" />
						</fo:block>
					</fo:block-container>					
					
					<fo:block-container position="absolute" top="100mm" left="30mm">
						<fo:block content-width="80mm" line-height="14pt" font-family="sans-serif" font-size="10pt" >
							<xsl:text>an der Lehrveranstaltung</xsl:text>
						</fo:block>
					</fo:block-container>

					
					<fo:block-container position="absolute" top="110mm" left="30mm">
						<fo:block content-width="180mm" line-height="18pt" font-family="sans-serif" font-size="18pt">
							<xsl:value-of select="bezeichnung" />
						</fo:block>
					</fo:block-container>
					

					<fo:block-container position="absolute" top="120mm" left="30mm">
						<fo:block content-width="180mm" line-height="14pt" font-family="sans-serif" font-size="10pt">
							<xsl:text>im Ausmaß von </xsl:text><xsl:value-of select="sws" />
							<xsl:text> Semesterstunden; </xsl:text>
							<xsl:value-of select="ects" /><xsl:text> ECTS Punkte</xsl:text>
							<xsl:text>\n\n im </xsl:text><xsl:value-of select="studiensemester" />
						</fo:block>
					</fo:block-container>	
					
					<xsl:if test="lehrinhalte!=''">
					<fo:block-container position="absolute" top="150mm" left="30mm">
						<fo:block content-width="180mm" line-height="14pt" font-family="sans-serif" font-size="16pt">
							<xsl:text>Lehrinhalte:</xsl:text>
						</fo:block>
					</fo:block-container>	

					<fo:block-container position="absolute" top="158mm" left="35mm">
						<fo:block content-width="150mm" line-height="12pt" font-family="sans-serif" font-size="10pt">
							<xsl:value-of select="lehrinhalte" />
						</fo:block>
					</fo:block-container>
					</xsl:if>
					
					<xsl:if test="note &gt; 0">
					<fo:block-container position="absolute" top="230mm" left="30mm">
						<fo:block content-width="180mm" line-height="14pt" font-family="sans-serif" font-size="12pt">
							<xsl:text>Die Prüfung wurde mit </xsl:text>
							<fo:inline font-weight="900">
							<xsl:value-of select="note_bezeichnung" /><xsl:text> (</xsl:text>
							<xsl:value-of select="note" /><xsl:text>)</xsl:text>
							</fo:inline>
							<xsl:text> abgelegt</xsl:text>
						</fo:block>
					</fo:block-container>
					</xsl:if>					
					
					<fo:block-container position="absolute" top="250mm" left="30mm" height="10mm">
						<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="75mm"/>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="75mm"/>	

							<fo:table-body>
								
								<fo:table-row  line-height="12pt">
									<fo:table-cell>
										<fo:block font-size="8pt" content-width="75mm" text-align="center">
										</fo:block></fo:table-cell>
									<fo:table-cell>
										<fo:block>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-size="10pt" content-width="75mm" text-align="center">
										<xsl:value-of select="ort_datum" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row  line-height="8pt">
									<fo:table-cell>
										<fo:block font-size="8pt" font-weight="bold" content-width="75mm" text-align="center">
											______________________________________
										</fo:block>
									</fo:table-cell>
									<fo:table-cell><fo:block></fo:block></fo:table-cell>
									<fo:table-cell>
										<fo:block font-size="8pt" font-weight="bold" content-width="75mm" text-align="center">
											______________________________________
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
								
								<fo:table-row  line-height="12pt">
									<fo:table-cell>
										<fo:block font-size="10pt" content-width="75mm" text-align="center">
											<xsl:value-of select="lvleiter" />
											<xsl:text>\nLehrveranstaltungsleitung</xsl:text>
										</fo:block></fo:table-cell>
									<fo:table-cell>
										<fo:block>
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-size="10pt" content-width="75mm" text-align="center">
										Ort, Datum
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
								
								<fo:table-row  line-height="10pt">
									<fo:table-cell number-columns-spanned="3">
										<fo:block font-size="10pt" content-width="165mm" text-align="center">
											<xsl:text>Fachhochschule Technikum Wien\nHöchstädtplatz 5\nA-1200 Wien\nZVR-Nr.: 074476426\nDVR-Nr.:0928381</xsl:text>
										</fo:block></fo:table-cell>
								</fo:table-row>
								
								    
							</fo:table-body>
						</fo:table>
						
					</fo:block-container> 
 

 
 
			</fo:flow>
		</fo:page-sequence>
	
	</xsl:template>
	
</xsl:stylesheet >