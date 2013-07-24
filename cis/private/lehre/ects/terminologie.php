<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/phrasen.class.php');

$sprache = getSprache(); 
$p= new phrasen($sprache);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../../skin/style.css.php" rel="stylesheet" type="text/css">
<title><?php echo $p->t('courseInformation/terminologie');?></title>

</head>
<body style="padding: 10px">
<h1><?php echo $p->t('courseInformatoin/lvInfoTerminologie')?></h1>

			      <table class="tabcontent">
			         <tr>
			               <td width="85%">
				              &nbsp;
						    </td>
							<td>
								<ul>
								<li>&nbsp;<a class="Item" href='index.php'><font size='3'><?php echo $p->t('global/bearbeiten');?></font></a></li>
								<li>&nbsp;<a class="Item" href='freigabe.php'><font size='3'><?php echo $p->t('courseInformation/freigabe');?></font></a></li>
								<li>&nbsp;<a class="Item" href='beispiele.php'><font size='3'><?php echo $p->t('global/beispiele');?></font></a></li>
								<li>&nbsp;<a class="Item" href='terminologie.php'><font size='3'><?php echo $p->t('courseInformation/terminologie')?></font></a></li>
				 				</ul>
							</td>
			          </tr>
			      </table>

     <table id="tabterm" border="1">

	<tr class="liste1">
		<td colspan="2" align="center">
			<b><?php echo $p->t('courseInformation/terminologieDeutschEnglisch');?></b>
		</td>
	</tr>
	<tr class="liste1">
		<td>
		</td>
		<td></td>
	</tr>
	<tr class="liste0">
		<td>
			<b>Deutsch</b>
		</td>
		<td>
		      <b>Englisch</b>
		</td>
	</tr>
	<tr class="liste1">
 		<td>
	      Abschluss (einer Lehrveranstaltung)
		</td>
		<td>
		completion
		</td>
	</tr>
	<tr class="liste0">
		<td>
			Anf&auml;ngerIn
		</td>
		<td>
			beginner
		</td>
	</tr>
	<tr class="liste1">
		<td>
			angerechnet
		</td>
		<td>
			recognized
		</td>
	</tr>
	<tr class="liste0">
		<td>
			Anrechnung
		</td>
		<td>
			recognition
		</td>
	</tr>
	<tr class="liste1">
		<td>
			Aufgaben
		</td>
		<td>
			tasks / responsibilities / assignment
		</td>
	</tr>
	<tr class="liste0">
		<td>
			Bachelor-Studiengang
		</td>
		<td>
		 	bachelor's degree program
		</td>
	</tr>
	<tr class="liste1">
		<td>
			Bachelor
		</td>
		<td>
			Bachelor
		</td>
	</tr>
	<tr class="liste0">
		<td>
		Bachelor-Arbeiten
		</td>
		<td>
			bachelor's paper
		</td>
	</tr>
	<tr class="liste1">
		<td>
		berufsbegleitend
		</td>
		<td>
		   part-time study
		</td>
	</tr>
	<tr class="liste0">
	<td>
		Berufspraktikum
		</td>
		<td>
			 intership
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 bestanden
		</td>
		<td>
			 pass
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 bestanden, mit gutem Erfolg
		</td>
		<td>
			 pass with merit
		</td>
	</tr>
	<tr class="liste1">
		<td>
			bestanden, mit ausgezeichnetem
			Erfolg
		</td>
		<td>
			 pass with distinction
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 BetreuerIn
		</td>
		<td>
			 supervisor
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Diplomarbeit
		</td>
		<td>
			 master's thesis
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 DI (FH)
		</td>
		<td>
			 DI (FH)
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Diplom-Studiengang
		</td>
		<td>
	 		 diploma degree program
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Erhalter
		</td>
		<td>
			 operator
		</td>
	</tr>
	<tr class="liste1">
		<td>
			Experte/in
		</td>
		<td>
			 expert
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Fachbereich
		</td>
		<td>
			 special field
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 FachbereichskoordinatorIn
		</td>
		<td>
			 special field coordinator
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 FachbereichsleiterIn
		</td>
		<td>
			 head of special field
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Fachhochschul-Beirat
		</td>
		<td>
			 University of Applied Sciences Advisory Board (UAS Advisory Board)
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Fachhochschul-Kollegium
		</td>
		<td>
			 University of Applied Sciences Council (UAS Council)
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Fachhochschul-KollegiumsleiterIn
		</td>
		<td>
			 Head of University of Applied Sciences Council
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Fachhochschulrat
		</td>
		<td>
			 FH Council
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Fachhochschulkonferenz
		</td>
		<td>
			 Association of Universities of
			Applied Sciences Austria
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Fernlehre
		</td>
		<td>
			 distance learning
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Fernlehrelemente
		</td>
		<td>
			 distance learning elements
		</td>
	</tr>
	<tr class="liste0">
		<td>
			Fortgeschrittene/r
		</td>
		<td>
			 advanced
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Gesamtnote, Gesamtbeurteilung
		</td>
		<td>
			 final grade
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Grundlagen
		</td>
		<td>
			 fundamentals
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 hauptberuflich Lehrende/r
		</td>
		<td>
			 full-time instructor
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Immanente Leistungsbeurteilung
		</td>
		<td>
			continuous assessment
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 integrierte Lehrveranstaltung
		</td>
		<td>
			 integrated course
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 JahrgangssprecherIn
		</td>
		<td>
			 class representative
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 kommissionelle Pr&uuml;fung
		</td>
		<td>
			 panel exam
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Labor
		</td>
		<td>
			 laboratory
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Lehrender
		</td>
		<td>
			 instructor
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Lehrgang universit&auml;ren
			Charakters
		</td>
		<td>
			 university
			level course
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Lehrinhalte
		</td>
		<td>
			 course contents
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Lehrk&ouml;rper
		</td>
		<td>
			 teaching staff
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Lehrmethode
		</td>
		<td>
			 teaching method
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Lehrveranstaltung
		</td>
		<td>
			 course
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Lehrziele
		</td>
		<td>
			 course objectives
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Lernmethode
		</td>
		<td>
			 study technique
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Magisterarbeit
		</td>
		<td>
			 master's thesis
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Mag. (FH)
		</td>
		<td>
			 Mag. (FH)
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Master-Studiengang
		</td>
		<td>
			 master's degree program
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Matrikelnummer
		</td>
		<td>
			 registration number
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 M&uuml;ndliche Pr&uuml;fung
		</td>
		<td>
			 oral examination
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 nebenberuflich Lehrende/r
		</td>
		<td>
			 part-time instructor
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Niveaustufe
		</td>
		<td>
			 level
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Niveaustufen:
		</td>
		<td>
			 levels
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Note
		</td>
		<td>
			 grade
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Organisation der LV
		</td>
		<td>
			 course organization
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Personenkennzeichen
		</td>
		<td>
			 personal identification number
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Pflichtveranstaltung
		</td>
		<td>
			 required course
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Pr&auml;sentation
		</td>
		<td>
			 presentation
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Programmverantwortlicher
		</td>
		<td>
			 Program Director
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Projektarbeit
		</td>
		<td>
			 project work
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Pr&uuml;fung
		</td>
		<td>
			 examination
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Pr&uuml;fungsmodalit&auml;ten
		</td>
		<td>
			 exam procedure
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Pr&uuml;fungsordnung
		</td>
		<td>
			 examination regulation
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Rektor
		</td>
		<td>
			 rector
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Schriftliche Pr&uuml;fung
		</td>
		<td>
			 written examination
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Semester
		</td>
		<td>
			 semester
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Seminar
		</td>
		<td>
			 seminar
		</td>
	</tr>
	
	
	<!-- beg. neu 26.12.2009 seq  -->
	<tr class="liste1">
		<td>
			 Stellv. Studiengangsleiter
		</td>
		<td>
			 Deputy Program Director
		</td>
	</tr>

	<tr class="liste0">
		<td>
			 AssistentIn
		</td>
		<td>
			 Administrative Assistant
		</td>
	</tr>

	<tr class="liste1">
		<td>
			 ECTS-Leistungspunkte
		</td>
		<td>
			 ECTS credits
		</td>
	</tr>
	
	<tr class="liste0">
		<td>
			 Semesterwochenstunden
		</td>
		<td>
			 Semester periods per week (SP/W)
		</td>
	</tr>	
	<!-- end neu 26.12.2009 seq  -->
	
	
	<tr class="liste1">
		<td>
			 Sommersemester
		</td>
		<td>
			 summer semester / spring semester
		</td>
	</tr>
	<tr class="liste0">
		<td>
			Spezialisten
		</td>
		<td>
			 specialists
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Studiengang
		</td>
		<td>
			 degree program
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 StudiengangssprecherIn
		</td>
		<td>
			 program representative
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 StudiengangsleiterIn
		</td>
		<td>
			 program director
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Studienjahr
		</td>
		<td>
			 academic year
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Studienplan
		</td>
		<td>
			 curriculum
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Studienplatz
		</td>
		<td>
			 study place
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Studierendenvertretung
		</td>
		<td>
			 student council
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Teilgebiet
		</td>
		<td>
			 segment
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Test
		</td>
		<td>
			 test
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Titel der Lehrveranstaltung
		</td>
		<td>
			 course title
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 &Uuml;bung (einfache, mit Anleitung)
		</td>
		<td>
			 exercise</font>
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 &Uuml;bung (im Sinne einer LV)
		</td>
		<td>
			 practice
			session		</td>
	</tr>
	<tr class="liste1">
		<td>
			 &Uuml;bung (im Sinne &uuml;ben)
		</td>
		<td>
			 practice
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Unterricht
		</td>
		<td>
			 instruction
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Vollzeit
		</td>
		<td>
			 full-time
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Voraussetzungen (f&uuml;r LVs)
		</td>
		<td>
			 requirements
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Vorlesung
		</td>
		<td>
			 lecture
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Vortragender
		</td>
		<td>
			 lecturer
		</td>
	</tr>
	<tr class="liste1">
		<td>
			 Wintersemester
		</td>
		<td>
			 autumn semester / winter semester
		</td>
	</tr>
	<tr class="liste0">
		<td>
			 Zeugnis
		</td>
		<td>
			 certificate
		</td>
	</tr>
	

	<!-- beg. neu 26.12.2009 seq -->
	<tr class="liste1">
		<td>
			 Unterrichtssprache
		</td>
		<td>
			 Language of instruction
		</td>
	</tr>

	<tr class="liste0">
		<td>
			 Bewerbung
		</td>
		<td>
			 application
		</td>
	</tr>

	<tr class="liste1">
		<td>
			 Zugangsvorraussetzungen
		</td>
		<td>
			 admission requirements
		</td>
	</tr>
	
	<tr class="liste0">
		<td>
			 Matura
		</td>
		<td>
			 Secondary School diploma
		</td>
	</tr>
	
	<tr class="liste1">
		<td>
			 Auslandssemester
		</td>
		<td>
			 exchange semester
		</td>
	</tr>	
	
	<tr class="liste0">
		<td>
			 Fachhochschule Technikum Wien                      
		</td>
		<td>
			 University of Applied Sciences Technikum Wien (UAS Technikum Wien)
		</td>
	</tr>	
	
	<tr class="liste1">
		<td>
			 Institut  für                      
		</td>
		<td>
			 department of
		</td>
	</tr>	

	<tr class="liste0">
		<td>
			 Senat                      
		</td>
		<td>
			 Senate
		</td>
	</tr>		
	
	<!-- end neu 26.12.2009 seq -->
</table>
</td></tr></table>
</body></html>