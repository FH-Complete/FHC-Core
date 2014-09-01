<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="lehrauftraege">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
 			<fo:simple-page-master page-height="297mm" page-width="210mm" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="lehrauftrag"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="lehrauftrag">
		<fo:page-sequence master-reference="PageMaster">
			<fo:flow  ><!--flow-name="xsl-region-body"-->
				<!-- Titel -->
				\n<fo:block-container position="absolute" top="15mm" left="10mm">
				<fo:block font-size="15pt">Fachhochschule Technikum Wien Lehrauftrag</fo:block>
				</fo:block-container>
				<!-- Logo -->
				<fo:block>
					<fo:external-graphic src="../skin/images/logo.jpg"  posx="140" posy="5" width="60mm" height="20mm" />
				</fo:block>
				<!-- Studiengang -->
				\n<fo:block-container position="absolute" top="20mm" left="10mm">
				<fo:block font-size="12pt">
					\n<xsl:value-of select="studiengang" />
				</fo:block>
				</fo:block-container>
				<!--Name und Adresse-->
				<fo:block font-size="10pt">
					\n<fo:block-container position="absolute" top="40mm" left="21mm">
					<fo:block>
						<fo:inline font-weight="bold" font-size="12pt">
							\n<xsl:value-of select="mitarbeiter/name_gesamt" />
							\n<xsl:value-of select="mitarbeiter/anschrift" />		
							\n<xsl:value-of select="mitarbeiter/plz" /><xsl:text> </xsl:text>
							<xsl:value-of select="mitarbeiter/ort" />
							\n<xsl:value-of select="mitarbeiter/zuhanden" />
						</fo:inline>
					</fo:block>
					</fo:block-container>
								
					\n<fo:block-container font-size="7pt" position="absolute" top="62mm" left="21mm">
					<fo:block font-size="7pt">\n
						Abs.: Fachhochschule Technikum Wien, Höchstädtplatz 6, A-1200 Wien
					</fo:block>
					</fo:block-container>
					
					\n<fo:block-container font-size="7pt" position="absolute" top="92mm" left="5mm">
					<fo:block font-size="7pt" color="#B3B3B3">\n
						&#x97;
					</fo:block>
					</fo:block-container>
					
					<fo:block font-size="10pt" font-weight="bold">
							<xsl:value-of select="studiensemester_kurzbz" />
					</fo:block>
					\n<fo:block font-size="8pt">
						<fo:inline font-weight="bold">
							<!--<xsl:value-of select="mitarbeiter/name_gesamt" />-->
							<xsl:choose>
								<xsl:when test="string-length(mitarbeiter/titelpre)!=0">
									<xsl:value-of select="mitarbeiter/titelpre" /><xsl:text> </xsl:text>
								</xsl:when>
								
							</xsl:choose>
							
							<xsl:value-of select="mitarbeiter/vorname" /><xsl:text> </xsl:text>
							<xsl:value-of select="mitarbeiter/familienname" /><xsl:text> </xsl:text>
							<xsl:value-of select="mitarbeiter/titelpost" /><xsl:text> </xsl:text>
						</fo:inline>
						\n<xsl:text>SV.Nr.: </xsl:text><xsl:value-of select="mitarbeiter/svnr" />		
						\n<xsl:text>Personalnummer: </xsl:text><xsl:value-of select="mitarbeiter/personalnummer" />
					</fo:block>
				</fo:block>
				
				<fo:block font-size="8pt">
					\n\n\n\n\n\n\n Wir beauftragen Sie, im <xsl:value-of select="studiensemester" /> folgende Lehrveranstaltungen abzuhalten:\n
				</fo:block>
				<!-- Tabelle -->

				<fo:table table-layout="fixed" border-collapse="separate">
					<fo:table-column column-width="12mm"/>
					<fo:table-column column-width="67mm"/>
					<fo:table-column column-width="38mm"/>
					<fo:table-column column-width="30mm"/>
					<fo:table-column column-width="10mm"/>
					<fo:table-column column-width="8mm"/>
					<!-- <fo:table-column column-width="7mm"/>-->
					<fo:table-column column-width="17mm"/>
					<fo:table-body>
						<fo:table-row  line-height="10pt">
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Nummer</fo:block></fo:table-cell>
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Lehrveranstaltung</fo:block></fo:table-cell>
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Institut</fo:block></fo:table-cell>
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Gruppe</fo:block></fo:table-cell>
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Std.</fo:block></fo:table-cell>
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Satz</fo:block></fo:table-cell>
							<!-- <fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Fakt.</fo:block></fo:table-cell>-->
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Brutto</fo:block></fo:table-cell>
						</fo:table-row>
						<xsl:apply-templates select="lehreinheit"/>
					</fo:table-body>
				</fo:table>
				
				<xsl:apply-templates select="newsite"/>
				
				<fo:table table-layout="fixed" border-collapse="separate">
					<fo:table-column column-width="12mm"/>
					<fo:table-column column-width="67mm"/>
					<fo:table-column column-width="38mm"/>
					<fo:table-column column-width="30mm"/>
					<fo:table-column column-width="10mm"/>
					<fo:table-column column-width="8mm"/>
					<!-- <fo:table-column column-width="7mm"/>-->
					<fo:table-column column-width="17mm"/>
					<fo:table-body>
						<fo:table-row  line-height="10pt">
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"></fo:block></fo:table-cell>
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"></fo:block></fo:table-cell>
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"></fo:block></fo:table-cell>
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> Summe:</fo:block></fo:table-cell>
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold" text-align="right" content-width="10mm"><xsl:value-of select="gesamtstunden" /></fo:block></fo:table-cell>
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"></fo:block></fo:table-cell>
							<!-- <fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"></fo:block></fo:table-cell>-->
							<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"  font-weight="bold" content-width="17mm" text-align="right">EURO <xsl:value-of select="gesamtbetrag" /></fo:block></fo:table-cell>
						</fo:table-row>
					</fo:table-body>
				</fo:table>
				
				
				
				<fo:block><xsl:text>
				</xsl:text></fo:block>
				<!-- Tabelle ENDE -->
				<fo:block font-size="8pt">Die angeführten Stundensätze sind Bruttobeträge, von denen gegebenenfalls die Dienstnehmeranteile für Steuern und Sozialversicherung abgezogen werden. 
					Die angeführte Stundenzahl ist die maximal vorgesehene; abgerechnet werden jedoch nur die tatsächlich gehaltenen Stunden laut Anwesenheitslisten. 
					Außerdem besteht die Verpflichtung zur Teilnahme an Lektorenkonferenzen.
				</fo:block>
				<fo:block><xsl:text>
				</xsl:text></fo:block>
				<fo:block><xsl:text>
				</xsl:text></fo:block>
				<fo:block><xsl:text>
				</xsl:text></fo:block>
				<fo:block><xsl:text>
				</xsl:text></fo:block>
				<!-- Unterschrift -->
				<fo:table table-layout="fixed" border-collapse="separate">
					<fo:table-column column-width="70mm"/>
					<fo:table-column column-width="40mm"/>
					<fo:table-column column-width="70mm"/>
					<fo:table-body>
						<fo:table-row  line-height="12pt">
							<fo:table-cell border-width="0"><fo:block font-size="8pt" ></fo:block></fo:table-cell>
							<fo:table-cell border-width="0"><fo:block font-size="8pt" ></fo:block></fo:table-cell>
							<fo:table-cell border-width="0"><fo:block font-size="8pt" content-width="70" text-align="center" >Wien, am <xsl:value-of select="datum" /></fo:block></fo:table-cell>
						</fo:table-row>
						<fo:table-row  line-height="3pt">
							<fo:table-cell border-width="0"><fo:block font-size="10pt" content-width="70" text-align="center">________________________</fo:block></fo:table-cell>
							<fo:table-cell border-width="0"><fo:block font-size="10pt" ></fo:block></fo:table-cell>
							<fo:table-cell border-width="0"><fo:block font-size="10pt" content-width="70" text-align="center">________________________</fo:block></fo:table-cell>
						</fo:table-row>
						<fo:table-row  line-height="12pt">
							<fo:table-cell border-width="0"><fo:block font-size="8pt" content-width="70" text-align="center" ><xsl:value-of select="studiengangsleiter" /></fo:block></fo:table-cell>
							<fo:table-cell border-width="0"><fo:block font-size="8pt" ></fo:block></fo:table-cell>
							<fo:table-cell border-width="0"><fo:block font-size="8pt" content-width="70" text-align="center" >Ort, Datum</fo:block></fo:table-cell>
						</fo:table-row>
						<fo:table-row  line-height="12pt">
							<fo:table-cell border-width="0"><fo:block font-size="8pt" content-width="70" text-align="center" >Studiengangsleitung</fo:block></fo:table-cell>
							<fo:table-cell border-width="0"><fo:block font-size="8pt" ></fo:block></fo:table-cell>
							<fo:table-cell border-width="0"><fo:block font-size="8pt" content-width="70" text-align="center" ></fo:block></fo:table-cell>
						</fo:table-row>
					</fo:table-body>
				</fo:table>
				<fo:block text-align="center" font-size="7pt">
				Fachhochschule Technikum\nWien\nHöchstädtplatz 6\nA-1200 Wien\nZVR-Nr.: 074476526\nDVR-Nr.: 0928381
				</fo:block>
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>
		
	<xsl:template match="lehreinheit">
		<fo:table-row  line-height="10pt">
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="12mm"><xsl:text> </xsl:text><xsl:value-of select="lehreinheit_id" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="67mm"><xsl:text> </xsl:text><xsl:value-of select="lehrveranstaltung" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="38mm"><xsl:text> </xsl:text>
			<xsl:choose>
				<xsl:when test="string-length(fachbereich)>28">
					<xsl:value-of select="substring(fachbereich,0,25)" /><xsl:text>...</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="fachbereich" />
				</xsl:otherwise>
			</xsl:choose>
			</fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="30mm" text-align="center"><xsl:value-of select="gruppe" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="10mm" text-align="right"><xsl:value-of select="stunden" /></fo:block></fo:table-cell>
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="8mm" text-align="right"><xsl:value-of select="satz" /></fo:block></fo:table-cell>
			<!-- <fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="7mm" text-align="right"><xsl:value-of select="faktor" /></fo:block></fo:table-cell>-->
			<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="17mm" text-align="right">EURO <xsl:value-of select="brutto" /></fo:block></fo:table-cell>
		</fo:table-row>
	</xsl:template>
	<xsl:template match="newsite">
		  <fo:block font-size="16pt" 
	            font-family="sans-serif" 
	            space-after.optimum="15pt"
	            text-align="center"
	            break-before="page">
	      </fo:block>
		<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="12mm"/>
						<fo:table-column column-width="67mm"/>
						<fo:table-column column-width="38mm"/>
						<fo:table-column column-width="30mm"/>
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="8mm"/>
						<!-- <fo:table-column column-width="7mm"/>-->
						<fo:table-column column-width="17mm"/>
						<fo:table-body>
				<xsl:apply-templates select="lehreinheit"/>
			</fo:table-body>
		</fo:table>
	</xsl:template>

</xsl:stylesheet >