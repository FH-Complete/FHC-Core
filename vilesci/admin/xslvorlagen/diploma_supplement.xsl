<?xml version="1.0" encoding="ISO-8859-15"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="supplements">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set><fo:simple-page-master format="A3" orientation="L"
master-name="PageMaster">
				
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="supplement"/>
		</fo:root>
	</xsl:template>
	
        <xsl:template match="supplement">					
		<fo:page-sequence master-reference="PageMaster">
					
                        <fo:flow flow-name="xsl-region-body" >


<fo:block-container position="absolute" top="25mm" left="20mm" right="200mm" height="0mm">
	<fo:block text-align="block" line-height="10pt" font-family="sans-serif" font-size="6pt">
Dieser Anhang zum Diplom wurde nach dem von der Europäischen Kommission, dem Europarat und UNESCO/CEPES entwickelten Modell erstellt. Mit dem
\nAnhang wird das Ziel verfolgt, ausreichend unabhängige Daten zu erfassen, um die internationale "Transparenz" und die angemessene akademische und berufliche \nAnerkennung von Qualifikationen (Diplomen, Abschlüssen, Zeugnissen usw.) zu verbessern. Der Anhang soll eine Beschreibung über Art, Niveau, Kontext, Inhalt \nund Status eines Studiums bieten, den die im Original-Befähigungsnachweis, dem der Anhang beigefügt ist, genannte Person absolviert und erfolgreich abgeschlossen\nhat\nThis diploma supplement follows the model developed by the European Commission, Council of Europe and UNESCO/CEPES. The purpose of the supplement \nis to provide sufficient data to improve international transparency and fair academic and professional recognition of qualifications (diplomas, degrees, certificates, etc.). It \nis designed to provide a description of the nature, level, context, content and status of the studies that were pursued and successfully completed by the individual \nnamed on the original qualification to which this supplement is appended.
	</fo:block>
</fo:block-container> 


<fo:block-container position="absolute" top="60mm" left="20mm" right="200mm" height="0mm">

	<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="10mm"/>
<fo:table-column column-width="170mm"/>
					
		<fo:table-body>
<fo:table-row  line-height="12pt">
								

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 1. \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> ANGABEN ZUR PERSON DES QUALIFIKATIONSINHABERS \nINFORMATION IDENTIFYING THE HOLDER OF THE QUALIFICATION</fo:block></fo:table-cell>
								
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

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">1.1</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Familienname(n)/Family name(s)</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> aaaaaaa</fo:block></fo:table-cell>
	
</fo:table-row>

<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">1.2</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Vorname(n)/Given name(s)</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">bbbbbbbbbbbbbbb</fo:block></fo:table-cell>
										</fo:table-row>

<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">1.3 \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Geburtsdatum (TT.MM.JJJJ) \n Date of birth (DDMMYYYY)</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">ccccccccc \n</fo:block></fo:table-cell>
										</fo:table-row>

<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">1.4 \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Personenkennzeichen \n Student identification number</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">ddddddddd \n</fo:block></fo:table-cell>
										</fo:table-row>



</fo:table-body>
</fo:table>

</fo:block-container> 


<fo:block-container position="absolute" top="98mm" left="20mm" right="200mm" height="0mm">

	<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="10mm"/>
<fo:table-column column-width="170mm"/>
					
		<fo:table-body>
<fo:table-row  line-height="12pt">
								

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 2. \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold"> ANGABEN ZUR QUALIFIKATION \nINFORMATION IDENTIFYING THE QUALIFICATION </fo:block></fo:table-cell>
								
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

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">2.1 \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Name der Qualifikation und verliehener Titel \nName of qualification, title conferred</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"> Bachelor of Science in Engineering, Bsc \n</fo:block></fo:table-cell>
	
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">2.2 \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Hauptstudienfach oder -fächer für die Qualifikation \nMain field(s) of study for the qualification</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">hhhhhhhhhhhhhhh\ngggggggggggg</fo:block></fo:table-cell>
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">2.3 \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Name und Status der Organisation, die die Qualifikation\nverliehen hat \nName and status of awarding institution \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Fachhochschule Technikum Wien, Verleihung des Status \n"Fachhochschule" im November 2000 \nUniversity of Applied Sciences Fachhochschule Technikum Wien, \nStatus University of Applied Science since November 2000</fo:block></fo:table-cell>
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">2.4 \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Name und Status der Einrichtung, die das Studium durchführte \n Name and status of institution administrating studies</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Fachhochschule Technikum Wien \nUniversity of Applied Sciences Fachhochschule Technikum Wien</fo:block></fo:table-cell>
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">2.5 \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Im Unterricht/in den Prüfungen verwendete Sprachen \nLanguage(s) of instructions/examination</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Deutsch, Englisch\nGerman, English</fo:block></fo:table-cell>
</fo:table-row>



</fo:table-body>
</fo:table>

</fo:block-container> 

<fo:block-container position="absolute" top="158mm" left="20mm" right="200mm" height="0mm">

	<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="10mm"/>
<fo:table-column column-width="170mm"/>
					
		<fo:table-body>
<fo:table-row  line-height="12pt">
								

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 3. \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold">ANGABEN ZUM NIVEAU DER QUALIFIKATION \nINFORMATION ON THE LEVEL OF THE QUALIFICATION</fo:block></fo:table-cell>
								
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

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">3.1 \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Niveau der Qualifikation \nLevel of qualification</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Bachelorstudium (UNESCO ISCED 5A) \nBachelor degree program (UNESCO ISCED 5A)</fo:block></fo:table-cell>
	
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">3.2 \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Regelstudiendauer (gesetzliche Studiendauer) \nOfficial lenght of program</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">6 Semester/3 Jahre\n6 semesters/3 years</fo:block></fo:table-cell>
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">3.3 \n \n \n \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Zulassungsvoraussetzungen \nAccess requirement(s)\n \n \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Allgemeine Universitätsreise (vgl. §4 Abs.3 FHStG idgF), \nBerufsreifeprüfung bzw. Studienberechtigungsprüfung oder \n einschlägige berufliche Qualifikation (Lehrabschluss bzw. Abschluss \n einer berufsbildenden mittleren Schule mit Zulassungsprüfung). Die \nAufnahme erfolgt auf Basis eines Auswahlverfahrens (Werdegang, \nEignungstest, Bewerbungsgespräch). \nAustrian or equivalent foreign school leaving certificate \n(Reifeprüfung), university entrance examination certificate \n(Studienberechtigungsprüfung), certificate or equivalent relevant \nprofessional qualification (Berufsreifeprüfung) plus entrance \nexamination equal to the university entrance examination. There is a \nselection procedure prior to admission (including entrance exam and \ninterview, professional background is considered).</fo:block></fo:table-cell>
</fo:table-row>




</fo:table-body>
</fo:table>

</fo:block-container> 

<fo:block-container position="absolute" top="234mm" left="20mm" right="200mm" height="0mm">

	<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="10mm"/>
<fo:table-column column-width="170mm"/>
					
		<fo:table-body>
<fo:table-row  line-height="12pt">
								

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 4. \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold">ANGABEN ÜBER DEN INHALT UND DIE ERZIELTEN ERGEBNISSE \nINFORMATION ON THE CONTENTS AND RESULTS GAINED</fo:block></fo:table-cell>
								
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

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">4.1</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Studienart/Mode of study</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Vollzeitstudium/Full-time degree program</fo:block></fo:table-cell>
	
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">4.2 \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Anforderungen des Studiums \nProgram requirements \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Das Studium erfordert die positive Absolvierung von \nLehrveranstaltungen (Vorlesungen, Übungen, Seminare, Projekte, \nintegrierte Lehrveranstaltungen) im Ausmaß von jeweils 30 ECTS pro \nSemester gemäß dem vorgeschriebenen Studienplan. Die Ausbildung \nintegriert technische, wirtschaftliche, organisatorische und \npersönlichkeitsbildende Elemente. Das Studium beinhaltet ein \nfacheinschlägiges Berufspraktikum. Im Rahmen des Studiums sind \nzwei Bachelorarbeiten zu verfassen und eine abschließende Prüfung \n(Bachelorprüfung) zu absolvieren. Curriculum des Studienganges \ngemäß dem vom FHR mit der Kennzahl 0254 genehmigten Antrag.</fo:block></fo:table-cell>
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

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">The program requires the positive completion of all courses (lectures, \nlabs, seminars, project work, and integrated courses) to the extent of 30 \nECTS per semester according to the curriculum. The program integrates \ntehnical, economical, management and persoal study elements. \nIncluded in the program is a relevant work placement. The degree is \nawarded upon the succeful completion of 2 bachelor theses and the \nfinal examination. Curriculum of the program according to the \napplication as approved by the Fachhoschul Council (Classification \nnumber: 0254)</fo:block></fo:table-cell>
	
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">4.3 \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Angaben zum Studium (z.B absolvierte Module und Einheiten) \nund erzielte Noten/Bewertungen/ECTS Anrechnungspunkte \nProgram details (courses, modules or units studied, individual \ngrades obtained)</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">180 ECTS \nSieche "Semesterzeugnisse" \nSee "semester transcripts" \n</fo:block></fo:table-cell>
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">4.4 \n \n \n \n \n \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Noteskala \nGrading scheme, grading translation \nand grade distribution guidance \n \n \n \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt"><fo:inline text-decoration="underline">Einzelbeurteilung/Grades
</fo:inline>\n1=Sehr gut/Excellent  \n2=Gut/Good  \n3=Befriedigend/Satisfactory  \n4=Genügend/Sufficient \n5=Nicht Genügend/Unsatisfactory \n \nTG=Teilgenommen/Participated, \nAR=Angerechnet/Credited \n \n \nGesamtbeurteilung/Overall assessment: \n-Bestanden/pass \n-Mit gutem Erfolg bestanden (pass with merit) \n-Mit ausgezeichnetem Erfolg bestanden (pass with distinction)</fo:block></fo:table-cell>
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">4.5 \n </fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Gesamtbeurteilung der Qualification \nOverall classification of the qualification</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">bestanden \n</fo:block></fo:table-cell>
</fo:table-row>


</fo:table-body>
</fo:table>

</fo:block-container> 


<fo:block-container position="absolute" top="68mm" left="360mm" right="500mm" height="0mm">
	<fo:block text-align="block" line-height="10pt" font-family="sans-serif" font-size="8pt">
<fo:inline text-decoration="underline">ECTS-Note/ECTS-grade </fo:inline> \n
A \n
B \n
C \n
D/E \n
F/FX
	</fo:block>
</fo:block-container>



<fo:block-container position="absolute" top="132mm" left="222mm" right="500mm" height="0mm">

	<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="10mm"/>
<fo:table-column column-width="170mm"/>
					
		<fo:table-body>
<fo:table-row  line-height="12pt">
								

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 5. \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold">ANGABEN ZUR FUNKTION DER QUALIFIKATION \nINFORMATION ON THE FUNKTION OF THE QUALIFICATION</fo:block></fo:table-cell>
								
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

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">5.1 \n \n \n \n \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Zugangsberechtigung zu weiterführenden Studien \nAccess to further study \n \n \n \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Der Abschluss des Bachelorstudiengangs berechtigt zu einem \nfacheinschlägigen Magister-bzw. Master-Studium an einer \nfachhochsculischen Einrichtung oder Universität (mit eventuellen \nZusatzprüfungen). \nThe successful completion of the Bachelor Degree Program qualifies the graduate to apply for admission to a relevant Master Degree Program at \na University of Applied Sciences or a University (additional qualifying \nexams may be required).</fo:block></fo:table-cell>
	
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">5.2 \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Beruficler Status \nProfessional status conferred \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Zugang zu akademischen Berufen nach Maßgabe der berufsrechtlichen \nVorschriften; Diplom im Sinne der Richtlinie 89/48/EWG \nAccess to academic professions according to the professional \nregulation; Diploma in the sense of directive RL 89/48/EEC</fo:block></fo:table-cell>
</fo:table-row>



</fo:table-body>
</fo:table>

</fo:block-container> 	


<fo:block-container position="absolute" top="189mm" left="222mm" right="500mm" height="0mm">

	<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="10mm"/>
<fo:table-column column-width="170mm"/>
					
		<fo:table-body>
<fo:table-row  line-height="12pt">
								

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 6.</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold">SONSTIGE ANGABEN/ADDITIONAL INFORMATION</fo:block></fo:table-cell>
								
</fo:table-row>
	</fo:table-body>
</fo:table>

</fo:block-container> 	


<fo:block-container position="absolute" top="195mm" left="222mm" right="500mm" height="0mm">



<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="10mm"/>
<fo:table-column column-width="80mm"/>
<fo:table-column column-width="90mm"/>

<fo:table-body>
		<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">6.1 \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Weitere Angaben \nAdditional information</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Berufspraktikum/Intership:absolviert/completed \n</fo:block></fo:table-cell>
	
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">6.2 \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Informationsquellen für ergänzende Angaben \nFurther information sources</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">http://www.technikum-wien.at, http://www.fhr.ac.at, \nhttp://www.bmbwk.qv.at</fo:block></fo:table-cell>
</fo:table-row>



</fo:table-body>
</fo:table>

</fo:block-container> 

<fo:block-container position="absolute" top="214mm" left="222mm" right="500mm" height="0mm">

	<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="10mm"/>
<fo:table-column column-width="170mm"/>
					
		<fo:table-body>
<fo:table-row  line-height="12pt">
								

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 7.</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold">BEURKUNDUNG DES ANHANGS/CERTIFICATION OF THE SUPPLEMENT</fo:block></fo:table-cell>
								
</fo:table-row>
	</fo:table-body>
</fo:table>


</fo:block-container>


<fo:block-container position="absolute" top="220mm" left="222mm" right="500mm" height="0mm">



<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="10mm"/>
<fo:table-column column-width="25mm"/>
<fo:table-column column-width="55mm"/>

<fo:table-body>
		<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">7.1</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Datum/Date</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">13.06.2007</fo:block></fo:table-cell>
	
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">7.2</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Name</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">FH-Prof. Dipl.-Ing. Dr. Martin Horauer</fo:block></fo:table-cell>
</fo:table-row>


<fo:table-row  line-height="10pt">

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">7.3 \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Amtl. Function \nCapacity</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt">Studiengangsleittung \nDirector of academic degree program</fo:block></fo:table-cell>
</fo:table-row>

</fo:table-body>
</fo:table>

</fo:block-container>


<fo:block-container position="absolute" top="237.25mm" left="222mm" right="500mm" height="0mm">

	<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="10mm"/>
<fo:table-column column-width="80mm"/>
					
		<fo:table-body>
<fo:table-row  line-height="12pt">
								

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" > 7.4 \n \n \n \n \n \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" >Unterschrift/Signature \n \n \n \n \n \n</fo:block></fo:table-cell>
								
</fo:table-row>
	</fo:table-body>
</fo:table>


</fo:block-container>


<fo:block-container position="absolute" top="220mm" left="313mm" right="500mm" height="0mm">

	<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="90mm"/>
	
		<fo:table-body>
<fo:table-row  line-height="12pt">
								

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" >Rundsiegel/Official stamp \n \n \n \n \n \n \n \n \n \n</fo:block></fo:table-cell>


								
</fo:table-row>
	</fo:table-body>
</fo:table>


</fo:block-container>

<fo:block-container position="absolute" top="238mm" left="313mm" right="500mm" height="0mm">
	<fo:block text-align="block" line-height="10pt" font-family="sans-serif" font-size="10pt" font-weight="bold">
<fo:inline text-decoration="underline">
\n</fo:inline> \n
Fachhochschule Tehnikum Wien \n
University of Applied Sciences \n
Hoechstaedtplaz 5 \n
A-1200 Vienna, Austria, Europa \n \n
+43-1-3334077-262 
	</fo:block>
</fo:block-container>



<fo:block-container position="absolute" top="270mm" left="222mm" right="500mm" height="0mm">

	<fo:table table-layout="fixed" border-collapse="separate">
<fo:table-column column-width="10mm"/>
<fo:table-column column-width="170mm"/>
					
		<fo:table-body>
<fo:table-row  line-height="12pt">
								

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="10pt" font-weight="bold"> 8. \n</fo:block></fo:table-cell>

<fo:table-cell border-width="0.2mm" border-style="solid" ><fo:block font-size="8pt" font-weight="bold">ANGABEN ZUM NATIONALEN HOCHSCHULSYSTEM (siehe Anhang) \nINFORMATION ON THE AUSTRIAN HIGHER EDUCATION SYSTEM (see appendix)</fo:block></fo:table-cell>
								
</fo:table-row>
	</fo:table-body>
</fo:table>

</fo:block-container>


			</fo:flow>
	    </fo:page-sequence>
	
	</xsl:template>
	
</xsl:stylesheet >