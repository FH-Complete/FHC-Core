<?php
/* Copyright (C) 2008 Technikum-Wien
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
/****************************************************************************
 * Script: 			mlists_generate.php
 * Descr:  			Das Skript generiert Mailinglisten in der Datenbanken
 *					fuer Einheiten, Lektoren und  fix Angestellte.
 * Author: 			Christian Paminger
 * Erstellt: 		12.9.2005
 * Update: 			14.9.2005 von Christian Paminger
 *****************************************************************************/

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/mail.class.php');

$error_msg='';
?>

<HTML>
<HEAD>
	<TITLE>Mailinglisten</TITLE>
	<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<LINK rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</HEAD>
<BODY>
	<H3>MailingListen abgleich</H3>
	<?php
	if (!$db = new basis_db())
 		die('Fehler beim Oeffnen der Datenbankverbindung');

	// aktuelles Studiensemester ermitteln
	$sql_query="SELECT studiensemester_kurzbz FROM public.vw_studiensemester ORDER BY delta LIMIT 1";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	if($row = $db->db_fetch_object($result))
		$studiensemester=$row->studiensemester_kurzbz;
	else
		$error_msg.= $db->db_last_error().$sql_query;

	$stsem_obj = new studiensemester();

	/*
	if(mb_substr($studiensemester,0,1)=='W')
		$stsem2 = $stsem_obj->getPreviousFrom($studiensemester);
	else
		$stsem2 = $stsem_obj->getNextFrom($studiensemester);
	*/
	$stsem2 = $stsem_obj->getNearestFrom($studiensemester);

	function setGeneriert($gruppe)
	{
		$db = new basis_db();
		$qry = "UPDATE public.tbl_gruppe SET generiert=true WHERE UPPER(gruppe_kurzbz)=UPPER('".addslashes($gruppe)."')";
		$db->db_query($qry);
	}
	
	/**
	 * Einfache Verteiler, deren Erstellung ohne Schleifen-Logik moeglich ist, werden ueber dieses Array erstellt
	 * Benoetigt werden die 3 Attribute:
	 * $verteilerArray['name_des_verteilers']['bezeichnung'] = 'Bezeichnung des Verteilers (32 Zeichen)';
	 * $verteilerArray['name_des_verteilers']['beschreibung'] = 'Beschreibung des Verteilers (Anzeige im CIS)(128 Zeichen)';
	 * $verteilerArray['name_des_verteilers']['sql'] = 'UIDs, die im Verteiler enthalten sein sollen (kein Semikolon am Ende)';
	 * 
	 * Die Verteiler werden dann alle gleich erstellt:
	 * - Pruefen, ob Gruppe existiert, wenn nicht, anlegen mit Default-Werten
	 * - Gruppe auf generiert setzen
	 * - UIDs loeschen, die nicht mehr in den Verteiler gehoeren
	 * - UIDs hinzufuegen, die im Verteiler fehlen
	 */
	
	$verteilerArray = array();
	
	// Sql-Schema: SELECT foo AS uid FROM bar WHERE foobar
	
	//Aktive MitarbeiterInnen mit Personalnummer > 0
	$verteilerArray['tw_ma']['bezeichnung'] = 'Alle aktiven MitarbeiterInnen';
	$verteilerArray['tw_ma']['beschreibung'] = 'Alle aktiven MitarbeiterInnen';
	$verteilerArray['tw_ma']['sql'] = "	SELECT DISTINCT mitarbeiter_uid AS uid 
										FROM public.tbl_mitarbeiter 
										JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid) 
										WHERE aktiv 
										AND personalnummer >= 0";
	//Aktive weibliche MitarbeiterInnen mit Personalnummer > 0
	$verteilerArray['tw_ma_w']['bezeichnung'] = 'Weibliche Mitarbeiterinnen';
	$verteilerArray['tw_ma_w']['beschreibung'] = 'Weibliche Mitarbeiterinnen';
	$verteilerArray['tw_ma_w']['sql'] = "	SELECT DISTINCT mitarbeiter_uid AS uid 
											FROM public.tbl_mitarbeiter 
											JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid) 
											JOIN public.tbl_person USING(person_id) 
											WHERE tbl_benutzer.aktiv 
											AND geschlecht='w' 
											AND personalnummer >=0";
	//Alle aktiven MitarbeiterInnen mit Attribut lektor=true
	$verteilerArray['tw_lkt']['bezeichnung'] = 'Alle LektorInnen';
	$verteilerArray['tw_lkt']['beschreibung'] = 'Alle LektorInnen an der FH Technikum Wien';
	$verteilerArray['tw_lkt']['sql'] = "	SELECT mitarbeiter_uid AS uid 
											FROM public.tbl_mitarbeiter 
											JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid) 
											WHERE lektor 
											AND aktiv";
	//MitarbeiterInnen mit gueltiger Funktion "ass" (assistenz)
	$verteilerArray['tw_sek']['bezeichnung'] = 'Alle Sekretariate';
	$verteilerArray['tw_sek']['beschreibung'] = 'Alle Sekretariate an der FH Technikum Wien';
	$verteilerArray['tw_sek']['sql'] = "	SELECT mitarbeiter_uid AS uid
											FROM
											public.tbl_mitarbeiter
											JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid)
											JOIN public.tbl_benutzerfunktion USING(uid)
											WHERE aktiv AND funktion_kurzbz='ass' AND
											(tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now()) AND
											(tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now()) AND
											mitarbeiter_uid NOT LIKE '\\\\_%' ";
	//Aktive MitarbeiterInnen mit gueltiger Funktion "Leitung", "gLtg" oder "stvLtg" in aktiven Studiengaengen
	$verteilerArray['tw_stgl']['bezeichnung'] = 'Alle StudiengangsleiterInnen';
	$verteilerArray['tw_stgl']['beschreibung'] = 'Alle StudiengangsleiterInnen und deren StellvertreterInnen';
	$verteilerArray['tw_stgl']['sql'] = "	SELECT DISTINCT mitarbeiter_uid AS uid
											FROM
												public.tbl_mitarbeiter
												JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid)
												JOIN public.tbl_benutzerfunktion USING(uid)
												JOIN public.tbl_studiengang USING(oe_kurzbz)
											WHERE
												tbl_benutzer.aktiv
												AND (tbl_benutzerfunktion.funktion_kurzbz='Leitung' OR funktion_kurzbz='gLtg' OR funktion_kurzbz='stvLtg')
												AND (tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now())
												AND (tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now())
												AND mitarbeiter_uid NOT LIKE '\\\\_%' 
												AND tbl_studiengang.aktiv=true";
	//Aktive MitarbeiterInnen mit gueltiger Funktion "Leitung", "gLtg" oder "stvLtg" in aktiven Bachelor- oder Master-Studiengaengen mit Kennzahl>0 und Kennzahl<10000
	$verteilerArray['tw_stgl_bama']['bezeichnung'] = 'Studiengangsleitung BAMA';
	$verteilerArray['tw_stgl_bama']['beschreibung'] = 'Studiengangsleitung und Stellvertretung von Bachelor und Master Studiengängen';
	$verteilerArray['tw_stgl_bama']['sql'] = "	SELECT DISTINCT mitarbeiter_uid AS uid
												FROM
													public.tbl_mitarbeiter
													JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid)
													JOIN public.tbl_benutzerfunktion USING(uid)
													JOIN public.tbl_studiengang USING(oe_kurzbz)
												WHERE
													tbl_benutzer.aktiv
													AND (tbl_benutzerfunktion.funktion_kurzbz='Leitung' OR funktion_kurzbz='gLtg' OR funktion_kurzbz='stvLtg')
													AND (tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now())
													AND (tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now())
													AND mitarbeiter_uid NOT LIKE '\\\\_%'
													AND tbl_studiengang.aktiv=true
													AND tbl_studiengang.typ in('b','m')
													AND tbl_studiengang.studiengang_kz>0
													AND tbl_studiengang.studiengang_kz<10000";
	//Alle aktiven MitarbeiterInnen mit Attribut fixangestellt=true
	$verteilerArray['tw_fix']['bezeichnung'] = 'Alle Fix-Angestellten';
	$verteilerArray['tw_fix']['beschreibung'] = 'Alle Fix-Angestellten an der FH Technikum Wien';
	$verteilerArray['tw_fix']['sql'] = "	SELECT mitarbeiter_uid AS uid 
											FROM public.tbl_mitarbeiter 
											JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid) 
											WHERE fixangestellt 
											AND aktiv 
											AND mitarbeiter_uid NOT LIKE '\\\\_%'";
	//Alle aktiven MitarbeiterInnen mit Attribut fixangestellt=true und lektor=true
	$verteilerArray['tw_fix_lkt']['bezeichnung'] = 'Alle fixangestellten LektorInnen';
	$verteilerArray['tw_fix_lkt']['beschreibung'] = 'Alle fixangestellten LektorInnen an der FH Technikum Wien';
	$verteilerArray['tw_fix_lkt']['sql'] = "	SELECT mitarbeiter_uid AS uid 
												FROM public.tbl_mitarbeiter 
												JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid) 
												WHERE fixangestellt 
												AND lektor 
												AND aktiv 
												AND mitarbeiter_uid NOT LIKE '\\\\_%'";
	//Alle aktiven MitarbeiterInnen mit Attribut fixangestellt=false und lektor=true
	$verteilerArray['tw_ext_lkt']['bezeichnung'] = 'Externe LektorInnen';
	$verteilerArray['tw_ext_lkt']['beschreibung'] = 'Alle externen LektorInnen an der FH Technikum Wien';
	$verteilerArray['tw_ext_lkt']['sql'] = "	SELECT mitarbeiter_uid AS uid 
												FROM public.tbl_mitarbeiter 
												JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid) 
												WHERE NOT fixangestellt 
												AND lektor 
												AND aktiv 
												AND mitarbeiter_uid NOT LIKE '\\\\_%'";
	//Hochschulvertretung. Studierende mit gueltiger Funktion 'hsv'.
	$verteilerArray['tw_hsv']['bezeichnung'] = 'Hochschulvertretung FHTW';
	$verteilerArray['tw_hsv']['beschreibung'] = 'Hochschulvertretung FHTW';
	$verteilerArray['tw_hsv']['sql'] = "	SELECT uid
											FROM
												public.tbl_benutzerfunktion
											JOIN public.tbl_benutzer USING(uid)
											WHERE
												funktion_kurzbz='hsv'
												AND tbl_benutzer.aktiv AND
												(tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now()) AND
												(tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now())";
	//Studienvertretung. Studierende mit gueltiger Funktion 'stdv'.
	$verteilerArray['tw_stdv']['bezeichnung'] = 'Alle StudierendenvertreterInnen';
	$verteilerArray['tw_stdv']['beschreibung'] = 'Alle StudierendenvertreterInnen';
	$verteilerArray['tw_stdv']['sql'] = "	SELECT uid
											FROM
												public.tbl_benutzerfunktion
											JOIN public.tbl_benutzer USING(uid)
											WHERE
												funktion_kurzbz='stdv'
												AND tbl_benutzer.aktiv AND
												(tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now()) AND
												(tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now())";
	//Jahrgangsvertretung. Studierende mit gueltiger Funktion 'jgv'.
	$verteilerArray['tw_jgv']['bezeichnung'] = 'Alle JahrgangsvertreterInnen';
	$verteilerArray['tw_jgv']['beschreibung'] = 'Alle JahrgangsvertreterInnen';
	$verteilerArray['tw_jgv']['sql'] = "	SELECT uid
											FROM
												public.tbl_benutzerfunktion
											JOIN public.tbl_benutzer USING(uid)
											WHERE
												funktion_kurzbz='jgv'
												AND tbl_benutzer.aktiv AND
												(tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now()) AND
												(tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now())";
	//Alle aktiven Studierenden 
	//Abbrecher bleiben noch 3 Wochen im Verteiler andere inaktive noch fuer 20 Wochen
	//damit im CIS die Menuepunkte fuer Studierende richtig angezeigt werden
	$verteilerArray['tw_std']['bezeichnung'] = 'Alle Studierenden';
	$verteilerArray['tw_std']['beschreibung'] = 'Alle ordentlichen, außerordentlichen und fiktiven Studierenden';
	$verteilerArray['tw_std']['sql'] = "	SELECT uid
											FROM campus.vw_student
											WHERE (
												aktiv
												OR
												(aktiv=false AND get_rolle_prestudent(vw_student.prestudent_id, null)='Abbrecher' AND updateaktivam>now()-'3 weeks'::interval)
												OR
												(aktiv=false AND get_rolle_prestudent(vw_student.prestudent_id, null)!='Abbrecher' AND updateaktivam>now()-'20 weeks'::interval)
											)";
	//Alle aktiven männlichen Studierenden
	//Abbrecher bleiben noch 3 Wochen im Verteiler andere inaktive noch fuer 20 Wochen
	$verteilerArray['tw_std_m']['bezeichnung'] = 'Alle männlichen Studenten';
	$verteilerArray['tw_std_m']['beschreibung'] = 'Alle männlichen Studenten an der FHTW';
	$verteilerArray['tw_std_m']['sql'] = "	SELECT uid
											FROM campus.vw_student
											WHERE (
												aktiv
												AND geschlecht='m'
												OR
												(aktiv=false AND geschlecht='m' AND get_rolle_prestudent(vw_student.prestudent_id, null)='Abbrecher' AND updateaktivam>now()-'3 weeks'::interval)
												OR
												(aktiv=false AND geschlecht='m' AND get_rolle_prestudent(vw_student.prestudent_id, null)!='Abbrecher' AND updateaktivam>now()-'20 weeks'::interval)
											)";
	//Alle aktiven weiblichen Studierenden
	//Abbrecher bleiben noch 3 Wochen im Verteiler andere inaktive noch fuer 20 Wochen
	$verteilerArray['tw_std_w']['bezeichnung'] = 'Alle weiblichen Studentinnen';
	$verteilerArray['tw_std_w']['beschreibung'] = 'Alle weiblichen Studentinnen an der FHTW';
	$verteilerArray['tw_std_w']['sql'] = "	SELECT uid
											FROM campus.vw_student
											WHERE (
												aktiv
												AND geschlecht='w'
												OR
												(aktiv=false AND geschlecht='w' AND get_rolle_prestudent(vw_student.prestudent_id, null)='Abbrecher' AND updateaktivam>now()-'3 weeks'::interval)
												OR
												(aktiv=false AND geschlecht='w' AND get_rolle_prestudent(vw_student.prestudent_id, null)!='Abbrecher' AND updateaktivam>now()-'20 weeks'::interval)
											)";
	//Alle ordentlichen, aktiven Bachelor- und Master-Studierenden
	//Absolventen bleiben noch  20 Wochen im Verteiler
	$verteilerArray['tw_bama']['bezeichnung'] = 'Alle BaMa-Studierenden';
	$verteilerArray['tw_bama']['beschreibung'] = 'Alle ordentlichen Bachelor- und Master-Studierenden';
	$verteilerArray['tw_bama']['sql'] = "	SELECT uid
											FROM campus.vw_student
											WHERE (
												aktiv
												OR
												(aktiv=false AND get_rolle_prestudent(vw_student.prestudent_id, null)='Absolvent' AND updateaktivam>now()-'20 weeks'::interval)
												AND studiengang_kz IN (SELECT studiengang_kz FROM public.tbl_studiengang WHERE typ IN ('b','m'))
											)";
	//Moodle-LektorenVerteiler 
	$verteilerArray['moodle_lkt']['bezeichnung'] = 'Moodle Lektoren';
	$verteilerArray['moodle_lkt']['beschreibung'] = 'Moodle Lektoren';
	$verteilerArray['moodle_lkt']['sql'] = "	SELECT distinct mitarbeiter_uid AS uid
												FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, addon.tbl_moodle ,campus.vw_lehreinheit
												WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id
												AND vw_lehreinheit.studiengang_kz=tbl_lehrveranstaltung.studiengang_kz
												AND vw_lehreinheit.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id
												AND vw_lehreinheit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id
												AND vw_lehreinheit.studiensemester_kurzbz=tbl_lehreinheit.studiensemester_kurzbz
												AND vw_lehreinheit.studiensemester_kurzbz=tbl_lehreinheit.studiensemester_kurzbz
												AND ((tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_moodle.lehrveranstaltung_id
												AND tbl_moodle.studiensemester_kurzbz=tbl_lehreinheit.studiensemester_kurzbz)
												OR 	(tbl_lehreinheit.lehreinheit_id=tbl_moodle.lehreinheit_id))";
	//Serviceabteilungen. Aktive MitarbeiterInnen mit gueltiger Leitungsfunktion in einer Abteilung
	$verteilerArray['serviceabteilungen']['bezeichnung'] = 'LeiterInnen sonst. OEen';
	$verteilerArray['serviceabteilungen']['beschreibung'] = 'LeiterInnen der Abteilungen und Gruppen';
	$verteilerArray['serviceabteilungen']['sql'] = "	SELECT distinct mitarbeiter_uid AS uid
														FROM
															public.tbl_mitarbeiter
														JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid)
														JOIN public.tbl_benutzerfunktion USING(uid)
														JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
														WHERE tbl_benutzer.aktiv AND (funktion_kurzbz='Leitung') 
														AND (tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now())
														AND (tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now())
														AND tbl_organisationseinheit.organisationseinheittyp_kurzbz='Abteilung'";
	//Aktive MitarbeiterInnen der OE "Sprachen"
	$verteilerArray['sprachen']['bezeichnung'] = 'Sprachen';
	$verteilerArray['sprachen']['beschreibung'] = 'MitarbeiterInnen des Instituts Sprachen und Kulturwissenschaften ';
	$verteilerArray['sprachen']['sql'] = "	SELECT distinct uid
											FROM
												public.tbl_benutzer
												JOIN public.tbl_benutzerfunktion USING(uid)
												JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
											WHERE oe_kurzbz in('Sprachen')
											AND tbl_benutzer.aktiv
											AND (tbl_benutzerfunktion.datum_von<=now() OR tbl_benutzerfunktion.datum_von is null)
											AND (tbl_benutzerfunktion.datum_bis>=now() OR tbl_benutzerfunktion.datum_bis is null)";
	// Aktive MitarbeiterInnen der OE "Sprachen"
	$verteilerArray['humanities']['bezeichnung'] = 'MA Institut Sprachen';
	$verteilerArray['humanities']['beschreibung'] = 'MitarbeiterInnen des Instituts Sprachen und Kulturwissenschaften ';
	$verteilerArray['humanities']['sql'] = "SELECT distinct uid
											FROM
												public.tbl_benutzer
												JOIN public.tbl_benutzerfunktion USING(uid)
												JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
											WHERE oe_kurzbz in('Sprachen')
											AND tbl_benutzer.aktiv
											AND (tbl_benutzerfunktion.datum_von<=now() OR tbl_benutzerfunktion.datum_von is null)
											AND (tbl_benutzerfunktion.datum_bis>=now() OR tbl_benutzerfunktion.datum_bis is null)";
	//Aktive BenutzerInnen mit der Funktion "kollegium"
	$verteilerArray['kollegium']['bezeichnung'] = 'Kollegium der FH Technikum Wien';
	$verteilerArray['kollegium']['beschreibung'] = 'Kollegium der FH Technikum Wien';
	$verteilerArray['kollegium']['sql'] = "	SELECT distinct uid
											FROM
												public.tbl_benutzer
												JOIN public.tbl_benutzerfunktion USING(uid)
											WHERE funktion_kurzbz='kollegium'
											AND tbl_benutzer.aktiv
											AND (tbl_benutzerfunktion.datum_von<=now() OR tbl_benutzerfunktion.datum_von is null)
											AND (tbl_benutzerfunktion.datum_bis>=now() OR tbl_benutzerfunktion.datum_bis is null)";
	//Aktive BenutzerInnen mit der Funktion "managementteam"
	$verteilerArray['tw_managementteam']['bezeichnung'] = 'Akademisches Managementteam';
	$verteilerArray['tw_managementteam']['beschreibung'] = 'Akademisches Managementteam';
	$verteilerArray['tw_managementteam']['sql'] = "	SELECT distinct uid
													FROM
														public.tbl_benutzer
														JOIN public.tbl_benutzerfunktion USING(uid)
													WHERE funktion_kurzbz='managementteam'
													AND tbl_benutzer.aktiv
													AND (tbl_benutzerfunktion.datum_von<=now() OR tbl_benutzerfunktion.datum_von is null)
													AND (tbl_benutzerfunktion.datum_bis>=now() OR tbl_benutzerfunktion.datum_bis is null)";
	//Aktive BenutzerInnen mit der Funktion "fue"
	$verteilerArray['tw_fue']['bezeichnung'] = 'Forschung und Entwicklung';
	$verteilerArray['tw_fue']['beschreibung'] = 'Forschung und Entwicklung';
	$verteilerArray['tw_fue']['sql'] = "	SELECT distinct uid
											FROM
												public.tbl_benutzer
												JOIN public.tbl_benutzerfunktion USING(uid)
											WHERE funktion_kurzbz='fue'
											AND tbl_benutzer.aktiv
											AND (tbl_benutzerfunktion.datum_von<=now() OR tbl_benutzerfunktion.datum_von is null)
											AND (tbl_benutzerfunktion.datum_bis>=now() OR tbl_benutzerfunktion.datum_bis is null)";
	//Aktive weiblichen Benutzerinnen mit der Funktion "fue"
	$verteilerArray['tw_fue_frauen']['bezeichnung'] = 'Weibliche Mitarbeiterinnen FuE';
	$verteilerArray['tw_fue_frauen']['beschreibung'] = 'Weibliche Mitarbeiterinnen in Forschung und Entwicklung';
	$verteilerArray['tw_fue_frauen']['sql'] = "	SELECT distinct uid
												FROM
													public.tbl_benutzer
													JOIN public.tbl_benutzerfunktion USING(uid)
													JOIN public.tbl_person USING (person_id)
												WHERE funktion_kurzbz='fue'
												AND geschlecht='w'
												AND tbl_benutzer.aktiv
												AND (tbl_benutzerfunktion.datum_von<=now() OR tbl_benutzerfunktion.datum_von is null)
												AND (tbl_benutzerfunktion.datum_bis>=now() OR tbl_benutzerfunktion.datum_bis is null)";
	//Alle aktiven MitarbeiterInnen mit Funktion Leitung oder stvLeitung oder gfLtg
	$verteilerArray['tw_leitung']['bezeichnung'] = 'Alle MA mit Leitungsfunktion';
	$verteilerArray['tw_leitung']['beschreibung'] = 'Alle MA mit Funktion Leitung, stellvertretende Leitung oder geschäftsführende Leitung';
	$verteilerArray['tw_leitung']['sql'] = "	SELECT DISTINCT uid 
												FROM 
													public.tbl_person 
												JOIN public.tbl_benutzer USING (person_id) 
												JOIN tbl_benutzerfunktion USING (uid)
												WHERE funktion_kurzbz IN('Leitung','stvLtg','gLtg')
												AND tbl_benutzer.aktiv
												AND (tbl_benutzerfunktion.datum_von<=now() OR tbl_benutzerfunktion.datum_von is null)
												AND (tbl_benutzerfunktion.datum_bis>=now() OR tbl_benutzerfunktion.datum_bis is null)";
	//Alle aktiven Studierenden in Academy-Lehrgaengen
	//Abbrecher bleiben noch 3 Wochen im Verteiler andere inaktive noch fuer 20 Wochen
	$verteilerArray['tw_academy_std']['bezeichnung'] = 'Alle Studierenden der Academy';
	$verteilerArray['tw_academy_std']['beschreibung'] = 'Alle Studierenden der TW-Academy (LehrgangsteilnehmerInnen)';
	$verteilerArray['tw_academy_std']['sql'] = "	SELECT DISTINCT uid AS uid
													FROM campus.vw_student
													JOIN public.tbl_studiengang USING (studiengang_kz)
													JOIN public.tbl_organisationseinheit USING (oe_kurzbz)
													WHERE (
														vw_student.aktiv
														OR
														(vw_student.aktiv=false AND get_rolle_prestudent(vw_student.prestudent_id, null)='Abbrecher' AND vw_student.updateaktivam>now()-'3 weeks'::interval)
														OR
														(vw_student.aktiv=false AND get_rolle_prestudent(vw_student.prestudent_id, null)!='Abbrecher' AND vw_student.updateaktivam>now()-'20 weeks'::interval))
													AND tbl_organisationseinheit.oe_parent_kurzbz='lehrgang'
													AND organisationseinheittyp_kurzbz='Lehrgang'";

	$bezeichnung = '';
	$beschreibung = '';
	foreach ($verteilerArray AS $listname => $data)
	{
		$grp = new gruppe();
		// Pruefen, ob die Gruppe existert, wenn nicht, anlegen
		if(!$grp->exists($listname))
		{
			if (strlen($data['bezeichnung']) > 32)
				$bezeichnung = substr($data['bezeichnung'], 0, 32);
			else 
				$bezeichnung = $data['bezeichnung'];
			
			if (strlen($data['beschreibung']) > 128)
				$beschreibung = substr($data['beschreibung'], 0, 128);
			else
				$beschreibung = $data['beschreibung'];
			
			$grp->gruppe_kurzbz = $listname;
			$grp->studiengang_kz = '0';
			$grp->semester = '0';
			$grp->bezeichnung = $bezeichnung;
			$grp->beschreibung = $beschreibung;
			$grp->mailgrp = true;
			$grp->sichtbar = true;
			$grp->generiert = true;
			$grp->aktiv = true;
			$grp->lehre = false;
			$grp->content_visible = false;
			$grp->gesperrt = false;
			$grp->zutrittssystem = false;
			$grp->aufnahmegruppe = false;
			$grp->insertamum = date('Y-m-d H:i:s');
			$grp->insertvon = 'mlists_generate';
			
			if(!$grp->save(true, true))
			{
				$error_msg .= 'Fehler beim Anlegen der Gruppe '.$listname.': '.$grp->errormsg;
				continue;
			}
		}
		else
		{
			setGeneriert($listname);
		}
		echo strtoupper($listname).' wird abgeglichen...<BR>';
		flush();
		//Eventuelles Semikolon am Ende des SQLs entfernen
		if (substr($data['sql'], -1) == ';')
			$data['sql'] = substr($data['sql'], 0, strlen($data['sql'])-1);

		$qry_delete = "	DELETE FROM 
							public.tbl_benutzergruppe 
						WHERE 
							UPPER(gruppe_kurzbz)=UPPER(".$db->db_add_param($listname).") 
						AND 
							uid NOT IN (".$data['sql'].");";

		if(!($result = $db->db_query($qry_delete)))
			$error_msg .= $db->db_last_error().$qry_delete.'<br><br>';
		
		echo strtoupper($listname).' '.$db->db_affected_rows($result).' Einträge gelöscht<BR>';
		
		flush();

		$qry_insert = "	WITH 
							uids AS (".$data['sql'].")
						INSERT INTO 
							public.tbl_benutzergruppe(uid, gruppe_kurzbz, insertamum, insertvon) 
						SELECT 
							*, UPPER(".$db->db_add_param($listname)."), now(), 'mlists_generate' 
						FROM 
							uids 
						WHERE 
							uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER(".$db->db_add_param($listname)."));";

		if(!($result = $db->db_query($qry_insert)))
			$error_msg .= $db->db_last_error().$qry_insert.'<br><br>';
		
		echo strtoupper($listname).' '.$db->db_affected_rows($result).' Einträge hinzugefügt<BR><BR>';
		
		flush();
	}

// **************************************************************
// Erstellen der Mailinglisten mit Schleifen-Logik
// **************************************************************

	
	// **************************************************************
	// Lektoren-Verteiler innerhalb der Studiengaenge abgleichen
	// Lektoren holen die nicht mehr in den Verteiler gehoeren
	echo '<BR>Lektoren-Verteiler der Studiengaenge werden abgeglichen!<BR>';
	flush();
	$sql_query="SELECT uid, gruppe_kurzbz FROM public.tbl_benutzergruppe
		WHERE gruppe_kurzbz LIKE '%\\_LKT' AND UPPER(gruppe_kurzbz)!=UPPER('tw_lkt') AND UPPER(gruppe_kurzbz)!=UPPER('tw_fix_lkt') AND UPPER(gruppe_kurzbz)!=UPPER('tw_ext_lkt') AND UPPER(gruppe_kurzbz)!=UPPER('moodle_lkt')
		AND (uid,UPPER(gruppe_kurzbz)) NOT IN
		(SELECT mitarbeiter_uid,UPPER(typ::varchar(1) || tbl_studiengang.kurzbz || '_lkt')
			FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_studiengang
			WHERE
			tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
			tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_studiengang.studiengang_kz=tbl_lehrveranstaltung.studiengang_kz AND
			(studiensemester_kurzbz='$studiensemester' OR
			 studiensemester_kurzbz='$stsem2') AND mitarbeiter_uid NOT LIKE '\\\\_%')";

	if(!($result=$db->db_query($sql_query)))
		$error_msg.=$db->db_last_error().$sql_query;
	while($row=$db->db_fetch_object($result))
	{
		$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$row->gruppe_kurzbz') AND uid='$row->uid'";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die noch nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT distinct mitarbeiter_uid, UPPER(typ::varchar(1) || tbl_studiengang.kurzbz || '_lkt') AS mlist_name, tbl_studiengang.studiengang_kz
		FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_studiengang
		WHERE
		tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
		tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
		tbl_studiengang.studiengang_kz=tbl_lehrveranstaltung.studiengang_kz AND
		(studiensemester_kurzbz='$studiensemester' OR
		 studiensemester_kurzbz='$stsem2') AND
		mitarbeiter_uid NOT LIKE '\\\\_%' AND tbl_studiengang.studiengang_kz!=0 AND
		(mitarbeiter_uid,UPPER(typ::varchar(1) || tbl_studiengang.kurzbz || '_lkt')) NOT IN
		(SELECT uid, UPPER(gruppe_kurzbz) FROM public.tbl_benutzergruppe
			WHERE gruppe_kurzbz LIKE '%\\_LKT' AND UPPER(gruppe_kurzbz)!=UPPER('tw_lkt') AND UPPER(gruppe_kurzbz)!=UPPER('tw_fix_lkt') AND UPPER(gruppe_kurzbz)!=UPPER('tw_ext_lkt') AND UPPER(gruppe_kurzbz)!=UPPER('moodle_lkt'))";
	//echo $sql_query;
	if(!($result=$db->db_query($sql_query)))
		$error_msg.=$db->db_last_error().$sql_query;
	while($row=$db->db_fetch_object($result))
	{
		$sql_query="SELECT * FROM public.tbl_gruppe WHERE gruppe_kurzbz='".strtoupper($row->mlist_name)."'";
		if($res = $db->db_query($sql_query))
		{
			if($db->db_num_rows($res)<=0)
			{
				setGeneriert($row->mlist_name);
				$sql_query="INSERT INTO public.tbl_gruppe(gruppe_kurzbz, studiengang_kz, semester, bezeichnung,
							beschreibung, mailgrp, sichtbar, generiert, aktiv, updateamum, updatevon,
							insertamum, insertvon)
							VALUES('".strtoupper($row->mlist_name)."',$row->studiengang_kz, 0,'$row->mlist_name',".
							"'$row->mlist_name', true, true, true, true, now(),'mlists_generate',now(), 'mlists_generate');";
				if(!$db->db_query($sql_query))
					echo "<br>Fehler beim Anlegen der Gruppe: $sql_query<br>";
			}
		}
		else
			echo "<br>Fehler:$sql_query";

		setGeneriert($row->mlist_name);
		$sql_query="INSERT INTO public.tbl_benutzergruppe(uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row->mitarbeiter_uid','".strtoupper($row->mlist_name)."', now(), 'mlists_generate')";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;

		flush();
	}


	// **************************************************************
	// Studienvertretungen der Studiengänge abgleichen

	echo 'Studienvertretungsverteiler werden abgeglichen!<BR>';
	flush();

	//Verteiler anlegen
	$sql_query="SELECT DISTINCT
					UPPER(typ||kurzbz)||'_'||UPPER(funktion_kurzbz) AS gruppe ,
					UPPER(typ||kurzbz) AS studiengang,
					tbl_studiengang.studiengang_kz
				FROM public.tbl_benutzerfunktion
				JOIN public.tbl_studiengang USING (oe_kurzbz)
				WHERE funktion_kurzbz='stdv'
				AND UPPER(typ||kurzbz)||'_'||UPPER(funktion_kurzbz) NOT IN (SELECT gruppe_kurzbz FROM public.tbl_gruppe)
				AND tbl_studiengang.aktiv
				AND tbl_studiengang.studiengang_kz!=0";
	if($res = $db->db_query($sql_query))
	{
		while($row = $db->db_fetch_object($res))
		{
			if($db->db_num_rows($res)>0)
			{
				$sql_query="INSERT INTO public.tbl_gruppe(gruppe_kurzbz, studiengang_kz, semester, bezeichnung,
								beschreibung, mailgrp, sichtbar, generiert, aktiv, updateamum, updatevon,
								insertamum, insertvon)
								VALUES(".$db->db_add_param($row->gruppe).",".$db->db_add_param($row->studiengang_kz).", NULL,".$db->db_add_param('Studienvertretung '.$row->studiengang).",".$db->db_add_param('Studienvertretung '.$row->studiengang).", true, true, true, true, now(),'mlists_generate',now(), 'mlists_generate');";
				if(!$db->db_query($sql_query))
					echo "<br>Fehler beim Anlegen der Gruppe: $sql_query<br>";
			}
		}
	}

	// Studierende holen, die nicht mehr in den Verteiler gehoeren
	$sql_query="SELECT gruppe_kurzbz, uid
				FROM public.tbl_benutzergruppe JOIN public.tbl_gruppe USING(gruppe_kurzbz)
				WHERE gruppe_kurzbz LIKE '%_STDV'
				AND uid not in (SELECT uid FROM public.tbl_benutzerfunktion JOIN public.tbl_benutzer USING(uid)
								WHERE funktion_kurzbz='stdv' AND tbl_benutzer.aktiv AND
								(tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now()) AND
								(tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now())
								AND (SELECT studiengang_kz FROM public.tbl_studiengang
									WHERE oe_kurzbz=tbl_benutzerfunktion.oe_kurzbz LIMIT 1)=tbl_gruppe.studiengang_kz)
								AND tbl_gruppe.studiengang_kz!='0'";
	if(!($result=$db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row = $db->db_fetch_object($result))
	{
		$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$row->gruppe_kurzbz') AND uid='$row->uid'";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;

		flush();
	}
	ob_flush();
	// Studierende holen, die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT uid, tbl_gruppe.gruppe_kurzbz
				FROM
					public.tbl_benutzerfunktion
					JOIN public.tbl_benutzer USING(uid)
					JOIN public.tbl_studiengang USING(oe_kurzbz)
					JOIN public.tbl_gruppe ON(tbl_gruppe.studiengang_kz=tbl_studiengang.studiengang_kz AND gruppe_kurzbz like '%_STDV')
				WHERE
					funktion_kurzbz='stdv'
					AND tbl_benutzer.aktiv AND
					(tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now()) AND
					(tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now())
					AND uid NOT in(Select uid from public.tbl_benutzergruppe JOIN public.tbl_gruppe USING(gruppe_kurzbz)
				WHERE studiengang_kz=(SELECT studiengang_kz FROM public.tbl_studiengang
				WHERE oe_kurzbz=tbl_benutzerfunktion.oe_kurzbz LIMIT 1)
					AND gruppe_kurzbz Like '%_STDV')";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row = $db->db_fetch_object($result))
	{
		if($row->gruppe_kurzbz!='')
		{
			setGeneriert($row->gruppe_kurzbz);
			$sql_query="INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row->uid','".mb_strtoupper($row->gruppe_kurzbz)."', now(), 'mlists_generate')";
			if(!$db->db_query($sql_query))
				$error_msg.=$db->db_last_error().$sql_query;

			flush();
		}
	}


	// **************************************************************
	// Verteiler Jahrgangsvertretung abgleichen

	echo 'Jahrgangsvertretungsverteiler werden abgeglichen!<BR>';
	flush();

	//Verteiler der einzelnen Studiengaenge anlegen
	$sql_query="SELECT DISTINCT
					UPPER(typ||kurzbz)||'_'||UPPER(funktion_kurzbz) AS gruppe ,
					UPPER(typ||kurzbz) AS studiengang,
					tbl_studiengang.studiengang_kz
				FROM public.tbl_benutzerfunktion
				JOIN public.tbl_studiengang USING (oe_kurzbz)
				WHERE funktion_kurzbz='jgv'
				AND UPPER(typ||kurzbz)||'_'||UPPER(funktion_kurzbz) NOT IN (SELECT gruppe_kurzbz FROM public.tbl_gruppe)
				AND tbl_studiengang.aktiv
				AND tbl_studiengang.studiengang_kz!=0";
	if($res = $db->db_query($sql_query))
	{
		while($row = $db->db_fetch_object($res))
		{
			if($db->db_num_rows($res)>0)
			{
				$sql_query="INSERT INTO public.tbl_gruppe(gruppe_kurzbz, studiengang_kz, semester, bezeichnung,
								beschreibung, mailgrp, sichtbar, generiert, aktiv, updateamum, updatevon,
								insertamum, insertvon)
								VALUES(".$db->db_add_param($row->gruppe).",".$db->db_add_param($row->studiengang_kz).", NULL,".$db->db_add_param('Jahrgangsvertretung '.$row->studiengang).",".$db->db_add_param('Jahrgangsvertretung '.$row->studiengang).", true, true, true, true, now(),'mlists_generate',now(), 'mlists_generate');";
				if(!$db->db_query($sql_query))
					echo "<br>Fehler beim Anlegen der Gruppe: $sql_query<br>";
			}
		}
	}

	// Studierende holen, die nicht mehr in den Verteiler gehoeren
	$sql_query="SELECT gruppe_kurzbz, uid
				FROM public.tbl_benutzergruppe JOIN public.tbl_gruppe USING(gruppe_kurzbz)
				WHERE gruppe_kurzbz LIKE '%_JGV'
				AND uid not in (SELECT uid FROM public.tbl_benutzerfunktion JOIN public.tbl_benutzer USING(uid)
								WHERE funktion_kurzbz='jgv' AND tbl_benutzer.aktiv AND
								(tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now()) AND
								(tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now())
								AND (SELECT studiengang_kz FROM public.tbl_studiengang
									WHERE oe_kurzbz=tbl_benutzerfunktion.oe_kurzbz LIMIT 1)=tbl_gruppe.studiengang_kz)
								AND tbl_gruppe.studiengang_kz!='0'";
	if(!($result=$db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row = $db->db_fetch_object($result))
	{
		$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$row->gruppe_kurzbz') AND uid='$row->uid'";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;

		flush();
	}

	// Studierende holen, die noch nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT uid, tbl_gruppe.gruppe_kurzbz
				FROM
					public.tbl_benutzerfunktion
					JOIN public.tbl_benutzer USING(uid)
					JOIN public.tbl_studiengang USING(oe_kurzbz)
					JOIN public.tbl_gruppe ON(tbl_gruppe.studiengang_kz=tbl_studiengang.studiengang_kz AND gruppe_kurzbz like '%_JGV')
				WHERE
					funktion_kurzbz='jgv'
					AND tbl_benutzer.aktiv AND
					(tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now()) AND
					(tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now())
					AND uid NOT in(Select uid from public.tbl_benutzergruppe JOIN public.tbl_gruppe USING(gruppe_kurzbz)
				WHERE studiengang_kz=(SELECT studiengang_kz FROM public.tbl_studiengang
				WHERE oe_kurzbz=tbl_benutzerfunktion.oe_kurzbz LIMIT 1)
					AND gruppe_kurzbz Like '%_JGV')";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row = $db->db_fetch_object($result))
	{
		if($row->gruppe_kurzbz!='')
		{
			setGeneriert($row->gruppe_kurzbz);
			$sql_query="INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row->uid','".mb_strtoupper($row->gruppe_kurzbz)."', now(), 'mlists_generate')";
			if(!$db->db_query($sql_query))
				$error_msg.=$db->db_last_error().$sql_query;

			flush();
		}
	}

	// **************************************************************
	// Organisationseinheiten-Verteiler

	/*
	$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE aktiv AND mailverteiler";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$mlist_name=strtoupper($row->oe_kurzbz);

			$grp = new gruppe();
			if(!$grp->exists($mlist_name))
			{
				$grp->gruppe_kurzbz = $mlist_name;
				$grp->studiengang_kz = '0';
				$grp->bezeichnung = $row->oe_kurzbz;
				$grp->beschreibung = 'Personen der Organisationseinheit '.$row->bezeichnung;
				$grp->semester = '0';
				$grp->mailgrp = true;
				$grp->sichtbar = true;
				$grp->generiert = true;
				$grp->aktiv = true;
				$grp->lehre = true;
				$grp->insertamum = date('Y-m-d H:i:s');
				$grp->insertvon = 'mlists_generate';

				if(!$grp->save(true, false))
					die('Fehler: '.$grp->errormsg);
			}
			else
			{
				setGeneriert($mlist_name);
			}

			$oe = new organisationseinheit();
			$childs = $oe->getChilds($row->oe_kurzbz);

			// Lektoren holen die nicht mehr in den Verteiler gehoeren
			echo '<br>'.$mlist_name.' wird abgeglichen!<BR>';
			flush();

			$oes='';
			foreach ($childs as $oe_kurzbz)
			{
				if($oes!='')
					$oes.=',';

				$oes .= "'".addslashes($oe_kurzbz)."'";
			}

			$sql_query = "SELECT distinct uid FROM public.tbl_benutzer JOIN public.tbl_benutzerfunktion USING(uid)
						WHERE oe_kurzbz in($oes)
						AND tbl_benutzer.aktiv
						AND (tbl_benutzerfunktion.datum_von<=now() OR tbl_benutzerfunktion.datum_von is null)
						AND (tbl_benutzerfunktion.datum_bis>=now() OR tbl_benutzerfunktion.datum_bis is null)";

			$sql_querys="DELETE FROM public.tbl_benutzergruppe WHERE gruppe_kurzbz='$mlist_name' AND uid NOT IN ($sql_query)";
			if(!$db->db_query($sql_querys))
			{
				$error_msg.=$db->db_last_error().' '.$sql_querys;
				echo '-';
				flush();
			}

			$sql_query.=" AND uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE gruppe_kurzbz='$mlist_name')";
			if(!($result_oe = $db->db_query($sql_query)))
				$error_msg.=$db->db_last_error().' '.$sql_query;
			// Lektoren holen die nicht im Verteiler sind
			echo '<BR>';
			while($row_oe = $db->db_fetch_object($result_oe))
			{
				$sql_query="INSERT INTO public.tbl_benutzergruppe(uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row_oe->uid','".$mlist_name."', now(), 'mlists_generate')";
				if(!$db->db_query($sql_query))
				{
					$error_msg.=$db->db_last_error().$sql_query;
					exit($error_msg);
				}
				echo '-';
				flush();
			}
		}
	}
	*/
	// **************************************************************
	// Instituts-Verteiler
	echo '<br>Abgleich der Institutsverteiler<br>';
	//Externe Mitarbeiter
	$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE aktiv AND mailverteiler AND organisationseinheittyp_kurzbz='Institut'";

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$mlist_name=strtoupper($row->oe_kurzbz).'_EXT';

			$grp = new gruppe();
			if(!$grp->exists($mlist_name))
			{
				$grp->gruppe_kurzbz = $mlist_name;
				$grp->studiengang_kz = '0';
				$grp->bezeichnung = $row->oe_kurzbz;
				$grp->beschreibung = 'Externe Mitarbeiter des Instituts '.$row->bezeichnung;
				$grp->semester = '0';
				$grp->mailgrp = true;
				$grp->sichtbar = true;
				$grp->generiert = true;
				$grp->aktiv = true;
				$grp->lehre = true;
				$grp->insertamum = date('Y-m-d H:i:s');
				$grp->insertvon = 'mlists_generate';

				if(!$grp->save(true, false))
					die('Fehler: '.$grp->errormsg);
			}
			else
			{
				setGeneriert($mlist_name);
			}

			$oe = new organisationseinheit();
			$childs = $oe->getChilds($row->oe_kurzbz);

			// Lektoren holen die nicht mehr in den Verteiler gehoeren
			echo '<br>'.$mlist_name.' wird abgeglichen!';
			flush();

			$oes='';
			foreach ($childs as $oe_kurzbz)
			{
				if($oes!='')
					$oes.=',';

				$oes .= "'".addslashes($oe_kurzbz)."'";
			}

			$sql_query = "SELECT distinct uid
						FROM
							public.tbl_benutzer
							JOIN public.tbl_benutzerfunktion USING(uid)
							JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
						WHERE oe_kurzbz in($oes)
						AND tbl_benutzer.aktiv AND NOT fixangestellt
						AND (tbl_benutzerfunktion.datum_von<=now() OR tbl_benutzerfunktion.datum_von is null)
						AND (tbl_benutzerfunktion.datum_bis>=now() OR tbl_benutzerfunktion.datum_bis is null)";

			$sql_querys="DELETE FROM public.tbl_benutzergruppe WHERE gruppe_kurzbz='$mlist_name' AND uid NOT IN ($sql_query)";
			if(!$db->db_query($sql_querys))
			{
				$error_msg.=$db->db_last_error().' '.$sql_querys;
			}

			$sql_query.=" AND uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE gruppe_kurzbz='$mlist_name')";
			if(!($result_oe = $db->db_query($sql_query)))
				$error_msg.=$db->db_last_error().' '.$sql_query;
			// Lektoren holen die nicht im Verteiler sind

			while($row_oe = $db->db_fetch_object($result_oe))
			{
				$sql_query="INSERT INTO public.tbl_benutzergruppe(uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row_oe->uid','".$mlist_name."', now(), 'mlists_generate')";
				if(!$db->db_query($sql_query))
				{
					$error_msg.=$db->db_last_error().$sql_query;
				}
			}
		}
	}

	//Fixe Mitarbeiter
	$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE aktiv AND mailverteiler AND organisationseinheittyp_kurzbz='Institut'";

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$mlist_name=strtoupper($row->oe_kurzbz).'_FIX';

			$grp = new gruppe();
			if(!$grp->exists($mlist_name))
			{
				$grp->gruppe_kurzbz = $mlist_name;
				$grp->studiengang_kz = '0';
				$grp->bezeichnung = $row->oe_kurzbz;
				$grp->beschreibung = 'Fixangestellte Mitarbeiter des Instituts '.$row->bezeichnung;
				$grp->semester = '0';
				$grp->mailgrp = true;
				$grp->sichtbar = true;
				$grp->generiert = true;
				$grp->aktiv = true;
				$grp->lehre = true;
				$grp->insertamum = date('Y-m-d H:i:s');
				$grp->insertvon = 'mlists_generate';

				if(!$grp->save(true, false))
					die('Fehler: '.$grp->errormsg);
			}
			else
			{
				setGeneriert($mlist_name);
			}

			$oe = new organisationseinheit();
			$childs = $oe->getChilds($row->oe_kurzbz);

			// Lektoren holen die nicht mehr in den Verteiler gehoeren
			echo '<br>'.$mlist_name.' wird abgeglichen!';
			flush();

			$oes='';
			foreach ($childs as $oe_kurzbz)
			{
				if($oes!='')
					$oes.=',';

				$oes .= "'".addslashes($oe_kurzbz)."'";
			}

			$sql_query = "SELECT distinct uid
						FROM
							public.tbl_benutzer
							JOIN public.tbl_benutzerfunktion USING(uid)
							JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
						WHERE oe_kurzbz in($oes)
						AND tbl_benutzer.aktiv AND fixangestellt
						AND (tbl_benutzerfunktion.datum_von<=now() OR tbl_benutzerfunktion.datum_von is null)
						AND (tbl_benutzerfunktion.datum_bis>=now() OR tbl_benutzerfunktion.datum_bis is null)";

			$sql_querys="DELETE FROM public.tbl_benutzergruppe WHERE gruppe_kurzbz='$mlist_name' AND uid NOT IN ($sql_query)";
			if(!$db->db_query($sql_querys))
			{
				$error_msg.=$db->db_last_error().' '.$sql_querys;
			}

			$sql_query.=" AND uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE gruppe_kurzbz='$mlist_name')";
			if(!($result_oe = $db->db_query($sql_query)))
				$error_msg.=$db->db_last_error().' '.$sql_query;
			// Lektoren holen die nicht im Verteiler sind
			while($row_oe = $db->db_fetch_object($result_oe))
			{
				$sql_query="INSERT INTO public.tbl_benutzergruppe(uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row_oe->uid','".$mlist_name."', now(), 'mlists_generate')";
				if(!$db->db_query($sql_query))
				{
					$error_msg.=$db->db_last_error().$sql_query;
				}
			}
		}
	}
	echo '<br>';
	
	// **************************************************************
	// Studierendenverteiler fuer die einzelnen Organisationseinheiten bei Mischformen
	echo '<br>Abgleich der Mischformverteiler';
	$stsem = $stsem_obj->getNearest();

	$sql_query = "
		SELECT
			tbl_prestudentstatus.orgform_kurzbz,
			tbl_studiengang.studiengang_kz,
			tbl_studiengang.typ,
			tbl_studiengang.kurzbz
		FROM
			public.tbl_student
			JOIN public.tbl_benutzer ON(student_uid=uid)
			JOIN public.tbl_prestudentstatus USING(prestudent_id)
			JOIN public.tbl_studiengang USING(studiengang_kz)
		WHERE
			tbl_studiengang.mischform
			AND tbl_benutzer.aktiv
			AND tbl_prestudentstatus.orgform_kurzbz is not null
		GROUP BY
			tbl_studiengang.studiengang_kz, tbl_prestudentstatus.orgform_kurzbz, tbl_studiengang.typ, tbl_studiengang.kurzbz
		";

	if($result = $db->db_query($sql_query))
	{
		echo '<BR>';

		while($row = $db->db_fetch_object($result))
		{
			$mlist_name=strtoupper($row->typ.$row->kurzbz.'_'.$row->orgform_kurzbz);
			echo $mlist_name.'<br>';

			//Gruppe anlegen falls noch nicht vorhanden
			$grp = new gruppe();
			if(!$grp->exists($mlist_name))
			{
				$grp->gruppe_kurzbz = $mlist_name;
				$grp->studiengang_kz = $row->studiengang_kz;
				$grp->bezeichnung = 'Alle '.$row->orgform_kurzbz.' Studenten von '.strtoupper($row->typ.$row->kurzbz);
				$grp->beschreibung = 'Alle '.$row->orgform_kurzbz.' Studenten von '.strtoupper($row->typ.$row->kurzbz);
				$grp->semester = '0';
				$grp->mailgrp = true;
				$grp->sichtbar = true;
				$grp->generiert = true;
				$grp->aktiv = true;
				$grp->lehre = false;
				$grp->insertamum = date('Y-m-d H:i:s');
				$grp->insertvon = 'mlists_generate';

				if(!$grp->save(true, false))
					die('Fehler: '.$grp->errormsg);
			}
			else
			{
				setGeneriert($mlist_name);
			}

			$sql_query="
				SELECT
					distinct student_uid
				FROM
					public.tbl_student
					JOIN public.tbl_benutzer ON(uid=student_uid)
				WHERE
					tbl_benutzer.aktiv AND
					'".addslashes($row->orgform_kurzbz)."'=
						(SELECT orgform_kurzbz
						 FROM public.tbl_prestudentstatus
						 WHERE
						 	prestudent_id=tbl_student.prestudent_id
						 	AND tbl_prestudentstatus.studiensemester_kurzbz='".addslashes($stsem)."'
						 ORDER BY datum desc, insertamum desc, ext_id desc LIMIT 1)
					AND tbl_student.studiengang_kz='".addslashes($row->studiengang_kz)."'";

			//Personen entfernen die nicht mehr in den Verteiler gehoeren
			$qry = "DELETE FROM public.tbl_benutzergruppe WHERE gruppe_kurzbz='".$mlist_name."' AND uid NOT IN(".$sql_query.");";
			if(!$db->db_query($qry))
			{
				$error_msg.="Fehler bei Qry:".$qry;
			}

			//Fehlende Personen hinzufuegen
			$sql_query.=" AND student_uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE gruppe_kurzbz='$mlist_name')";
			if(!($result_oe = $db->db_query($sql_query)))
				$error_msg.=$db->db_last_error().' '.$sql_query;


			while($row_oe = $db->db_fetch_object($result_oe))
			{
				$sql_query="INSERT INTO public.tbl_benutzergruppe(uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row_oe->student_uid','".$mlist_name."', now(), 'mlists_generate')";
				if(!$db->db_query($sql_query))
				{
					$error_msg.=$db->db_last_error().$sql_query;
					exit($error_msg);
				}
				flush();
			}
		}
	}
	else
		$error_msg.=$db->db_last_error().' '.$sql_query;

	echo $error_msg;
	
	// Send Mail to admin if error occurs
	if ($error_msg != '')
	{
		$mailtext = '
			<style type="text/css">
			.table1
			{
				font-size: small;
				cellpadding: 3px;
			}
			.table1 th
			{
				background: #DCE4EF;
				border: 1px solid #FFF;
				padding: 4px;
				text-align: left;
			}
			.table1 td
			{
				background-color: #EEEEEE;
				padding: 4px;
			}
			</style>
			Im Script mlists_generate.php sind folgende Fehler aufgetreten:<br><br>';
		$mailtext .= $error_msg;
		$mailtext = wordwrap($mailtext,70);
		$mail = new mail(MAIL_ADMIN, 'no-reply', 'ERROR mlists_generate.php', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
		//$mail->setBCCRecievers('kindlm@technikum-wien.at');
		$mail->setHTMLContent($mailtext);
		$mail->send();
	}
	?>
	<BR>
	<P>
		Die Mailinglisten wurden abgeglichen. <BR>
	</P>
</BODY>
</HTML>
