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
 ******************************************************************************
 * Beschreibung:
 * Dieses Skript prueft die gesamte Systemumgebung und sollte nach jedem Update gestartet werden.
 * Geprueft wird: - die Datenbank auf aktualitaet, dabei werden fehlende Attribute angelegt.
 *                - Verzeichnisse (ob vorhanden und beschreibbar falls noetig).
 */

require ('vilesci/config.inc.php');
// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!'.pg_last_error($conn));

echo '<H1>System wird geprueft!</H1>';
echo '<H2>Pruefe Tabellen!</H2>';

$tabellen=array("bis.tbl_ausbildung",
	"bis.tbl_berufstaetigkeit",
	"bis.tbl_beschaeftigungsart1",
	"bis.tbl_beschaeftigungsart2",
	"bis.tbl_beschaeftigungsausmass",
	"bis.tbl_besqual",
	"bis.tbl_bisfunktion",
	"bis.tbl_bisio",
	"bis.tbl_bisverwendung",
	"bis.tbl_entwicklungsteam",
	"bis.tbl_gemeinde",
	"bis.tbl_hauptberuf",
	"bis.tbl_mobilitaetsprogramm",
	"bis.tbl_nation",
	"bis.tbl_orgform",
	"bis.tbl_verwendung",
	"bis.tbl_zgv",
	"bis.tbl_zgvmaster",
	"bis.tbl_zweck",
	"campus.tbl_abgabe",
	"campus.tbl_beispiel",
	"campus.tbl_benutzerlvstudiensemester",
	"campus.tbl_bmreservierung",
	"campus.tbl_erreichbarkeit",
	"campus.tbl_feedback",
	"campus.tbl_legesamtnote",
	"campus.tbl_lvgesamtnote",
	"campus.tbl_lvinfo",
	"campus.tbl_news",
	"campus.tbl_notenschluessel",
	"campus.tbl_notenschluesseluebung",
	"campus.tbl_reservierung",
	"campus.tbl_resturlaub",
	"campus.tbl_studentbeispiel",
	"campus.tbl_studentuebung",
	"campus.tbl_uebung",
	"campus.tbl_zeitaufzeichnung",
	"campus.tbl_zeitsperre",
	"campus.tbl_zeitsperretyp",
	"campus.tbl_zeitwunsch",
	"fue.tbl_aktivitaet",
	"fue.tbl_projekt",
	"fue.tbl_projektbenutzer",
	"kommune.tbl_match",
	"kommune.tbl_team",
	"kommune.tbl_teambenutzer",
	"kommune.tbl_wettbewerb",
	"kommune.tbl_wettbewerbteam",
	"lehre.tbl_abschlussbeurteilung",
	"lehre.tbl_abschlusspruefung",
	"lehre.tbl_akadgrad",
	"lehre.tbl_betreuerart",
	"lehre.tbl_ferien",
	"lehre.tbl_lehreinheit",
	"lehre.tbl_lehreinheitgruppe",
	"lehre.tbl_lehreinheitmitarbeiter",
	"lehre.tbl_lehrfach",
	"lehre.tbl_lehrform",
	"lehre.tbl_lehrfunktion",
	"lehre.tbl_lehrveranstaltung",
	"lehre.tbl_note",
	"lehre.tbl_projektarbeit",
	"lehre.tbl_projektbetreuer",
	"lehre.tbl_projekttyp",
	"lehre.tbl_pruefung",
	"lehre.tbl_pruefungstyp",
	"lehre.tbl_stunde",
	"lehre.tbl_stundenplan",
	"lehre.tbl_stundenplandev",
	"lehre.tbl_zeitfenster",
	"lehre.tbl_zeugnis",
	"lehre.tbl_zeugnisnote",
	"public.tbl_akte",
	"public.tbl_benutzerfunktion",
	"public.tbl_benutzergruppe",
	"public.tbl_betriebsmittelperson",
	"public.tbl_firmentyp",
	"public.tbl_funktion",
	"public.tbl_gruppe",
	"public.tbl_kontakttyp",
	"public.tbl_lehrverband",
	"public.tbl_mitarbeiter",
	"public.tbl_ort",
	"public.tbl_person",
	"public.tbl_prestudent",
	"public.tbl_student",
	"public.tbl_studentlehrverband",
	"public.tbl_studiengang",
	"sync.tbl_zutrittskarte",
	"tbl_adresse",
	"tbl_aufmerksamdurch",
	"tbl_aufnahmeschluessel",
	"tbl_bankverbindung",
	"tbl_benutzer",
	"tbl_benutzerberechtigung",
	"tbl_berechtigung",
	"tbl_betriebsmittel",
	"tbl_betriebsmitteltyp",
	"tbl_buchungstyp",
	"tbl_dokument",
	"tbl_dokumentprestudent",
	"tbl_dokumentstudiengang",
	"tbl_erhalter",
	"tbl_fachbereich",
	"tbl_firma",
	"tbl_kontakt",
	"tbl_konto",
	"tbl_log",
	"tbl_newssprache",
	"bis.tbl_orgform",
	"tbl_ortraumtyp",
	"tbl_personfunktionfirma",
	"tbl_prestudentrolle",
	"tbl_raumtyp",
	"tbl_reihungstest",
	"tbl_rolle",
	"tbl_semesterwochen",
	"tbl_sprache",
	"tbl_standort",
	"tbl_studiensemester",
	"tbl_variable",
	"tbl_vorlage",
	"tbl_vorlagestudiengang",
	"testtool.tbl_ablauf",
	"testtool.tbl_antwort",
	"testtool.tbl_frage",
	"testtool.tbl_gebiet",
	"testtool.tbl_gruppe",
	"testtool.tbl_kategorie",
	"testtool.tbl_kriterien",
	"testtool.tbl_pruefling",
	"testtool.tbl_vorschlag");

foreach ($tabellen AS $tab)
{
	if (!pg_query($conn,'SELECT * FROM '.$tab.' LIMIT 1;'))
		echo $tab.': '.pg_last_error($conn).'<BR>';
	else
		echo $tab.': OK<BR>';
	flush();
}

require ('include/adresse.class.php');
if (!adresse::check_db($conn))
	echo 'Adresse: '.pg_last_error($conn).'<BR>';
else
	echo 'Adresse: OK<BR>';
flush();

?>