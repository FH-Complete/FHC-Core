<?xml version="1.0" encoding="ISO-8859-15"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="supplements">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set><fo:simple-page-master format="A3" orientation="L" master-name="PageMaster">				
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="supplement"/>
		</fo:root>
	</xsl:template>
	
        <xsl:template match="supplement">
		<fo:page-sequence master-reference="PageMaster">
			<fo:flow flow-name="xsl-region-body" >
				<fo:block>
					<fo:external-graphic src="../skin/images/logo.jpg"  posx="300" posy="15" height="33.44mm" width="99.99mm"/>
				</fo:block>
				<fo:block-container position="absolute" top="100mm" left="270mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate" >
						<fo:table-column column-width="100"  />
															
						<fo:table-body>
							<fo:table-row  line-height="12pt">								
								<fo:table-cell ><fo:block content-width="100mm" text-align="center" line-height="25pt" font-family="sans-serif" font-size="25pt" font-weight="bold">ANHANG ZUM DIPLOM</fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="12pt">								
								<fo:table-cell ><fo:block content-width="100mm" text-align="center" line-height="25pt" font-family="sans-serif" font-size="25pt" font-weight="bold">DIPLOMA SUPPLEMENT</fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="12pt">								
								<fo:table-cell ><fo:block content-width="100mm" text-align="center" line-height="25pt" font-family="sans-serif" font-size="15pt" font-weight="bold"><xsl:value-of select="studiengang_bezeichnung_deutsch" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="12pt">								
								<fo:table-cell ><fo:block content-width="100mm" text-align="center" line-height="25pt" font-family="sans-serif" font-size="15pt" font-weight="bold"><xsl:value-of select="studiengang_bezeichnung_englisch" /></fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 
			</fo:flow>
		</fo:page-sequence>
		
		<fo:page-sequence master-reference="PageMaster">
			<fo:flow flow-name="xsl-region-body" >
				
				<fo:block>
					<fo:external-graphic src="../skin/images/logo.jpg"  posx="160" posy="15" height="11.68mm" width="34.97mm"/>
				</fo:block>
				
				<fo:block-container position="absolute" top="25mm" left="20mm" right="200mm" height="0mm">
					<fo:block text-align="block" line-height="10pt" font-family="sans-serif" font-size="6pt">
				Dieser Anhang zum Diplom wurde nach dem von der Europäischen Kommission, dem Europarat und UNESCO/CEPES entwickelten Modell erstellt. Mit dem
				\n Anhang wird das Ziel verfolgt, ausreichend unabhängige Daten zu erfassen, um die internationale "Transparenz" und die angemessene akademische und berufliche \n Anerkennung von Qualifikationen (Diplomen, Abschlüssen, Zeugnissen usw.) zu verbessern. Der Anhang soll eine Beschreibung über Art, Niveau, Kontext, Inhalt \n und Status eines Studiums bieten, den die im Original-Befähigungsnachweis, dem der Anhang beigefügt ist, genannte Person absolviert und erfolgreich abgeschlossen\n hat.\n This diploma supplement follows the model developed by the European Commission, Council of Europe and UNESCO/CEPES. The purpose of the supplement \n is to provide sufficient independent data to improve the international transparency and fair academic and professional recognition of qualifications (diplomas, degrees, certificates, etc.). It \n is designed to provide a description of the nature, level, context, content and status of the studies that were pursued and successfully completed by the individual \n named on the original qualification to which this supplement is appended.
					</fo:block>
				</fo:block-container> 
				
				
				<fo:block-container position="absolute" top="60.5mm" left="20mm" right="200mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate" >
						<fo:table-column column-width="10mm"  />
						<fo:table-column column-width="170.4mm"/>
									
						<fo:table-body>
							<fo:table-row  line-height="12pt">								
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> 1.\n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" background-color="rgb(200,200,200)"><fo:block font-size="8pt" font-weight="bold"> ANGABEN ZUR PERSON DES QUALIFIKATIONSINHABERS \n INFORMATION IDENTIFYING THE HOLDER OF THE QUALIFICATION</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 

				<fo:block-container position="absolute" top="70mm" left="20mm" right="200mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="80mm"/>
						<fo:table-column column-width="90mm"/>
						
						<fo:table-body>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 1.1</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Familienname(n)/Family name(s)</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> <xsl:value-of select="nachname" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 1.2</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Vorname(n)/Given name(s)</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> <xsl:value-of select="vorname" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 1.3 \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Geburtsdatum (TT.MM.JJJJ) \n Date of birth (DDMMYYYY)</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> <xsl:value-of select="geburtsdatum" /> \n</fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 1.4 \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Personenkennzeichen \n Student identification number</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> <xsl:value-of select="matrikelnummer" /> \n</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 


				<fo:block-container position="absolute" top="98.5mm" left="20mm" right="200mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="170.4mm"/>
						
						<fo:table-body>
							<fo:table-row  line-height="12pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 2. \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  background-color="rgb(200,200,200)"><fo:block font-size="8pt" font-weight="bold"> ANGABEN ZUR QUALIFIKATION \n INFORMATION IDENTIFYING THE QUALIFICATION </fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 

				<fo:block-container position="absolute" top="108mm" left="20mm" right="200mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="80mm"/>
						<fo:table-column column-width="90mm"/>
						
						<fo:table-body>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 2.1 \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Name der Qualifikation und verliehener Titel \n Name of qualification, title conferred</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="titel" />\n<xsl:value-of select="titel_kurzbz" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 2.2 \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Hauptstudienfach oder -fächer für die Qualifikation \n Main field(s) of study for the qualification</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="studiengang_bezeichnung_deutsch" />\n<xsl:value-of select="studiengang_bezeichnung_englisch" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 2.3 \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Name und Status der Organisation, die die Qualifikation\n verliehen hat \n Name and status of awarding institution \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Fachhochschule Technikum Wien, Verleihung des Status \n "Fachhochschule" im November 2000 \n University of Applied Sciences Fachhochschule Technikum Wien, \n status University of Applied Science since November 2000</fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 2.4 \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Name und Status der Einrichtung, die das Studium durchführte \n Name and status of institution administering studies</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Fachhochschule Technikum Wien \n University of Applied Sciences Fachhochschule Technikum Wien</fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 2.5 \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Im Unterricht/in den Prüfungen verwendete Sprachen \n Language(s) of instruction/examination</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Deutsch, Englisch\n German, English</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 

				<fo:block-container position="absolute" top="158.5mm" left="20mm" right="200mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="170.4mm"/>
							
						<fo:table-body>
							<fo:table-row  line-height="12pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 3. \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  background-color="rgb(200,200,200)"><fo:block font-size="8pt" font-weight="bold">ANGABEN ZUM NIVEAU DER QUALIFIKATION \n INFORMATION ON THE LEVEL OF THE QUALIFICATION</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 

				<fo:block-container position="absolute" top="168mm" left="20mm" right="200mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="80mm"/>
						<fo:table-column column-width="90mm"/>
						
						<fo:table-body>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 3.1 \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Niveau der Qualifikation \n Level of qualification</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="niveau_deutsch" />\n<xsl:value-of select="niveau_englisch" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 3.2 \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Regelstudienzeit (gesetzliche Studiendauer) \n Official length of program</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="semester" /> Semester/<xsl:value-of select="jahre" /> Jahr(e)\n<xsl:value-of select="semester" /> semester/<xsl:value-of select="jahre" /> year(s)</fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 3.3 \n \n \n \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Zulassungsvoraussetzungen \n Access requirement(s)\n \n \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="zulassungsvoraussetzungen_deutsch" />\n<xsl:value-of select="zulassungsvoraussetzungen_englisch" /></fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 

				<fo:block-container position="absolute" top="234.5mm" left="20mm" right="200mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="170.4mm"/>
						
						<fo:table-body>
							<fo:table-row  line-height="12pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 4. \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  background-color="rgb(200,200,200)"><fo:block font-size="8pt" font-weight="bold">ANGABEN ÜBER DEN INHALT UND DIE ERZIELTEN ERGEBNISSE \n INFORMATION ON THE CONTENTS AND RESULTS GAINED</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 

				<fo:block-container position="absolute" top="244mm" left="20mm" right="200mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="80mm"/>
						<fo:table-column column-width="90mm"/>
						
						<fo:table-body>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 4.1</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Studienart/Mode of study</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="studienart" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 4.2 \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Anforderungen des Studiums \n Program requirements \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="anforderungen_deutsch" /></fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 

				<fo:block-container position="absolute" top="20mm" left="222mm" right="500mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="80mm"/>
						<fo:table-column column-width="90mm"/>

						<fo:table-body>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">\n \n \n \n \n \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">\n \n \n \n \n \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="anforderungen_englisch" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 4.3 \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Angaben zum Studium (z.B absolvierte Module und Einheiten) \n und erzielte Noten/Bewertungen/ECTS Anrechnungspunkte \n Program details (courses, modules or units studied, individual \n grades obtained)</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="ects" /> ECTS \n Siehe "Semesterzeugnisse" \n See "semester transcripts" \n</fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 4.4 \n \n \n \n \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Notenskala \n Grading scheme, grade translation \n and grade distribution guidance \n \n \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" >
									<fo:block font-size="8pt">
										<fo:inline text-decoration="underline">Einzelbeurteilung/Grades</fo:inline>
										\n 1=Sehr gut/Excellent  \n 2=Gut/Good  \n 3=Befriedigend/Satisfactory  \n 4=Genügend/Sufficient \n 5=Nicht Genügend/Unsatisfactory \n \n TG=Teilgenommen/Participated \n AR=Angerechnet/Credited \n \n Gesamtbeurteilung/Overall assessment: \n -Bestanden (pass) \n -Mit gutem Erfolg bestanden (pass with merit) \n -Mit ausgezeichnetem Erfolg bestanden (pass with distinction)
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 4.5 \n </fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Gesamtbeurteilung der Qualifikation \n Overall classification of the qualification</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="beurteilung" /> \n</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 


				<fo:block-container position="absolute" top="68mm" left="360mm" right="500mm" height="0mm">
					<fo:block text-align="block" line-height="10pt" font-family="sans-serif" font-size="8pt">
						<fo:inline text-decoration="underline"> ECTS-Note/ECTS-grade </fo:inline> \n
						A \n
						B \n
						C \n
						D/E \n
						F/FX
							</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="132.5mm" left="222mm" right="500mm" height="0mm">

					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="170.4mm"/>
									
						<fo:table-body>
							<fo:table-row  line-height="12pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 5. \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  background-color="rgb(200,200,200)"><fo:block font-size="8pt" font-weight="bold"> ANGABEN ZUR FUNKTION DER QUALIFIKATION \n INFORMATION ON THE FUNCTION OF THE QUALIFICATION</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>

				<fo:block-container position="absolute" top="142mm" left="222mm" right="500mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="80mm"/>
						<fo:table-column column-width="90mm"/>

						<fo:table-body>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 5.1 \n \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Zugangsberechtigung zu weiterführenden Studien \n Access to further study \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="zugangsberechtigung_deutsch" />\n<xsl:value-of select="zugangsberechtigung_englisch" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="10mm"> 5.2 \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="80mm"> Beruflicher Status \n Professional status conferred \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" content-width="91mm"> Zugang zu akademischen Berufen nach Maßgabe der berufsrechtlichen Vorschriften; Diplom im Sinne der Richtlinie 89/48/EWG.\n Access to academic professions according to the professional regulation; Diploma in the sense of directive RL 89/48/EEC.</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 	

				<fo:block-container position="absolute" top="200mm" left="222mm" right="500mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="170.4mm"/>
						
						<fo:table-body>
							<fo:table-row  line-height="12pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 6.</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  background-color="rgb(200,200,200)"><fo:block font-size="8pt" font-weight="bold"> SONSTIGE ANGABEN/ADDITIONAL INFORMATION</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 	

				<fo:block-container position="absolute" top="205mm" left="222mm" right="500mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="80mm"/>
						<fo:table-column column-width="90mm"/>

						<fo:table-body>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 6.1 \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Weitere Angaben \n Additional information</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="praktikum" />\n<xsl:value-of select="auslandssemester" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 6.2 \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Informationsquellen für ergänzende Angaben \n Further information sources</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> http://www.technikum-wien.at, http://www.fhr.ac.at\n</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container> 

				<fo:block-container position="absolute" top="225mm" left="222mm" right="500mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="170.6mm"/>
						
						<fo:table-body>
							<fo:table-row  line-height="12pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 7.</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  background-color="rgb(200,200,200)"><fo:block font-size="8pt" font-weight="bold"> BEURKUNDUNG DES ANHANGS/CERTIFICATION OF THE SUPPLEMENT</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>

				<fo:block-container position="absolute" top="230mm" left="222mm" right="500mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="25mm"/>
						<fo:table-column column-width="54.8mm"/>

						<fo:table-body>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 7.1</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Datum/Date</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="tagesdatum" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 7.2</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Name</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><xsl:value-of select="stgl" /></fo:block></fo:table-cell>
							</fo:table-row>
							<fo:table-row  line-height="10pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> 7.3 \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Amtl. Funktion \n Capacity</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Studiengangsleitung \n Director of academic degree program</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>

				<fo:block-container position="absolute" top="246.8mm" left="222mm" right="500mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="80.2mm"/>
						
						<fo:table-body>
							<fo:table-row  line-height="12pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" > 7.4 \n \n \n \n \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" > Unterschrift/Signature \n \n \n \n \n</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>

				<fo:block-container position="absolute" top="230mm" left="313mm" right="500mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="90mm"/>
						
						<fo:table-body>
							<fo:table-row  line-height="12pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" > Rundsiegel/Official stamp \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>

				<fo:block-container position="absolute" top="245mm" left="313mm" right="500mm" height="0mm">
					<fo:block text-align="block" line-height="10pt" font-family="sans-serif" font-size="8pt">
				<fo:inline text-decoration="underline">
				\n</fo:inline> \n
				Fachhochschule Technikum Wien \n
				\n
				Hoechstaedtplatz 5 \n
				A-1200 Wien, Österreich\n
				T: +43-1-3334077-<xsl:value-of select="telefonklappe" />
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="245mm" left="358mm" right="500mm" height="0mm">
					<fo:block text-align="block" line-height="10pt" font-family="sans-serif" font-size="8pt">
				<fo:inline text-decoration="underline">
				\n</fo:inline> \n
				University of Applied Sciences \n
				Technikum Wien \n
				Hoechstaedtplatz 5 \n
				A-1200 Vienna, Austria, Europe \n
				T: +43-1-3334077-<xsl:value-of select="telefonklappe" />
					</fo:block>
				</fo:block-container>

				<fo:block-container position="absolute" top="275mm" left="222mm" right="500mm" height="0mm">
					<fo:table table-layout="fixed" border-collapse="separate">
						<fo:table-column column-width="10mm"/>
						<fo:table-column column-width="170.7mm"/>
						
						<fo:table-body>
							<fo:table-row  line-height="12pt">
								<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 8. \n</fo:block></fo:table-cell>
								<fo:table-cell border-width="0.2mm" border-style="solid"  background-color="rgb(200,200,200)"><fo:block font-size="8pt" font-weight="bold"> ANGABEN ZUM NATIONALEN HOCHSCHULSYSTEM (siehe Anhang) \n INFORMATION ON THE AUSTRIAN HIGHER EDUCATION SYSTEM (see appendix)</fo:block></fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block-container>
				
			</fo:flow>
	    </fo:page-sequence>
	</xsl:template>
</xsl:stylesheet >