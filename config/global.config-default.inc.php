<?php

//Default-Werte für neue Lehreinheiten
define('DEFAULT_LEHREINHEIT_SPRACHE','German');
define('DEFAULT_LEHREINHEIT_RAUMTYP','Dummy');
define('DEFAULT_LEHREINHEIT_RAUMTYP_ALTERNATIV','Dummy');
define('DEFAULT_LEHREINHEIT_LEHRFORM','UE');

// Defaul Trennzeichen fuer E-Mail Empfaenger wenn nicht ueber Variablen ueberschrieben
define('DEFAULT_EMAILADRESSENTRENNZEICHEN',',');

//Anzeigeoptionen für Lehrveranstaltungen im CIS
define('CIS_LEHRVERANSTALTUNG_NEWSGROUPS_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_FEEDBACK_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_DOWNLOAD_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_UEBUNGSTOOL_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_PINBOARD_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_MAILSTUDIERENDE_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_STUDENTENUPLOAD_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_LVINFO_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_LVINFO_LEKTOR_EDIT',true); //Legt fest, ob Lehrende die LV-Infos selbst bearbeiten duerfen und ob der Link zum bearbeiten der LV-Infos im CIS angezeigt wird
define('CIS_LEHRVERANSTALTUNG_LEISTUNGSUEBERSICHT_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_SEMESTERINFO_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_LEHRFACH_ANZEIGEN',false);
define('CIS_LEHRVERANSTALTUNG_GESAMTNOTE_ANZEIGEN', true);

// Im CIS Menue Links bei Modulen anzeigen wenn Lehrauftrag
define('CIS_LEHRVERANSTALTUNG_MODULE_LINK',true);

// Legt fest, ob bei den LV-Infos der Block mit den Terminen zu den einzelnen LVs (laut Stundenplan) angezeigt werden soll
define ('CIS_LVINFO_TERMINE_ANZEIGEN', false);

// Legt fest ob bei den LVs im CIS das aktuelle Semester angezeigt wird oder das zum Semester dazupassende (zB Sommersemester im 2. Semester)
define('CIS_LEHRVERANSTALTUNG_AKTUELLES_STUDIENSEMESTER_ANZEIGEN',true);

//Anzeigeoptionen für Profil im CIS
define('CIS_PROFIL_MAILVERTEILER_ANZEIGEN',true);
define('CIS_PROFIL_FHAUSWEIS_ANZEIGEN',true);
define('CIS_PROFIL_FUNKTIONEN_ANZEIGEN',true);
define('CIS_PROFIL_BETRIEBSMITTEL_ANZEIGEN',true);
define('CIS_PROFIL_STUDIENINFORMATION_ANZEIGEN',true);

// Zeitaufzeichnung gesperrt_bis Datum YYYY-MM-DD
define('CIS_ZEITAUFZEICHNUNG_GESPERRT_BIS','');

// Anzeige des Links zur Noteneingabe in der LVA Uebersicht
define('CIS_LVALISTE_NOTENEINGABE_ANZEIGEN',true);

// Anzeige des LV-Plan Links bei globaler Suche
define('CIS_SUCHE_LVPLAN_ANZEIGEN',true);

// Soll geprueft werden ob das Passwort innerhalb des letzten Jahres geaendert wurde true|false
// Wenn dies nicht geaendert wurde wird nach dem Login auf die Passwort aendern Seite umgeleitet
define('CIS_CHECK_PASSWORD_CHANGE',false);

// Link zu den Excel Notenlisten im CIS Anzeigen
define('CIS_ANWESENHEITSLISTE_NOTENLISTE_ANZEIGEN',true);

// Link zu den Anwesenheitslisten (ohne Bilder) im CIS Anzeigen
define('CIS_ANWESENHEITSLISTE_ANWESENHEITSLISTE_ANZEIGEN',true);

// Punkte bei der Noteneingabe anzeigen
define('CIS_GESAMTNOTE_PUNKTE',false);

// Gibt an ob der Lektor erneut eine LVNote eintragen kann wenn bereits eine Zeugnisnote vorhanden ist (true | false) DEFAULT true
define('CIS_GESAMTNOTE_UEBERSCHREIBEN',true);

// Gewichtung der Lehreinheiten bei Noteneintragung true|false
define('CIS_GESAMTNOTE_GEWICHTUNG', true);

// Bei Gesamtnote eine zusaetzliche Spalte fuer den 2. Termin anzeigen
define('CIS_GESAMTNOTE_PRUEFUNG_TERMIN2',true);

// Bei Gesamtnote eine zusaetzliche Spalte fuer den 3. Termin anzeigen
// Erfordert den Eintrag "Termin3" in der Tabelle lehre.tbl_pruefungstyp
define('CIS_GESAMTNOTE_PRUEFUNG_TERMIN3',true);

// Bei Gesamtnote eine zusaetzliche Spalte fuer die kommissionelle Pruefung anlegen
define('CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF', true);

// Bei Gesamtnote eine zusaetzliche Spalte fuer die kommissionelle Pruefung anlegen
define('CIS_GESAMTNOTE_PRUEFUNG_MOODLE_NOTE', true);

// Bei Gesamtnote die Spalte fuer die Quelle der Noten anzeigen (Moodle oder LE)
define('CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE', true);

// Gibt an ob im FAS bei den Lehrveranstaltungsnoten ein zusaetzliches Formular angezeigt wird um
// Vertraege fuer Pruefungshonorare anzulegen
define('FAS_GESAMTNOTE_PRUEFUNGSHONORAR',false);

// Grenzwerte für Anwesenheit
define('FAS_ANWESENHEIT_ROT', 70);
define('FAS_ANWESENHEIT_GELB', 90);

// Legt einen Prüfungstermin an wenn eine neue Note erfasst wird
define('FAS_PRUEFUNG_BEI_NOTENEINGABE_ANLEGEN',false);

// Legt fest ob bei der Uebernahme der Reihungstestpunkte die Punkte
//oder Prozentpunkte uebernommen werden true=Punkte, false=Prozentpunkte
define('FAS_REIHUNGSTEST_PUNKTE', false);

// Legt fest ob Messages im FAS angezeigt werden true|false
define('FAS_MESSAGES',false);

// Enable (true) or disable (false) the UDF tab
define('FAS_UDF', true);

// Legt fest ob Aufnahmegruppen bei Reihungstests verwaltet werden true|false
define('FAS_REIHUNGSTEST_AUFNAHMEGRUPPEN',false);

// Anzeigeoptionen für LV-Plan Menü
define('CIS_LVPLAN_EXPORT_ANZEIGEN',true);
define('CIS_LVPLAN_PERSONENAUSWAHL_ANZEIGEN',true);
define('CIS_LVPLAN_LEHRVERBANDAUSWAHL_ANZEIGEN',true);
define('CIS_LVPLAN_ARCHIVAUSWAHL_ANZEIGEN',true);
define('CIS_LVPLAN_ZUSATZMENUE_ANZEIGEN',true);

//Anmerkung bei Unterrichtseinheiten im LV-Plan anzeigen. Anmerkungen bei LV-Plan Sync mitkopieren.
define('LVPLAN_ANMERKUNG_ANZEIGEN',true);
//Gruppieren zeitgleicher Lehreinheiten im LV-Plan
define('LVPLAN_LEHREINHEITEN_GRUPPIEREN',true);

// Ende-Datum des LVPlan Syncs Format: 2014-02-01
// Wenn leer wird jeweils bis Semesterende gesynct
define('LVPLAN_SYNC_ENDE','');

// Soll nach dem LVPlan Sync automatisch das Horde Sync angestoßen werden
define('LVPLAN_HORDE_SYNC',false);

// Format der Tagesinfo im Tempus %t = Wochentag %b = Begin der Stunde %e = Ende der Stunde %s = Stunde
define('TEMPUS_TAGESINFO_FORMAT','%t %b');

/*
 * VORRUECKUNG_LEHRVERBAND_MAX_SEMESTER
 * leer: Studentlehrverband Semester wird bei der Vorrueckung normal weitergezaehlt bis zum max_semester des Studienganges
 * Zahl: Studentlehrverband Semester wird bei der Vorrueckung maximal bis zur angegebenen Zahl erhoeht und bleibt dann in diesem Semester
 * DEFAULT: ''
 */
define('VORRUECKUNG_LEHRVERBAND_MAX_SEMESTER','');

/*
 * VORRUECKUNG_STATUS_MAX_SEMESTER
 * true: Semester im Status wird bei der Vorrueckung nicht hoeher als max_semester des Studienganges
 * false: Semester zaehlt bei der Vorrueckung immer weiter hoch
 * DEFAULT: true
 */
define('VORRUECKUNG_STATUS_MAX_SEMESTER',true);

// Bei Statuswechsel auf Bewerber -> soll Reihungstest brücksichtigt werden
define('REIHUNGSTEST_CHECK', true);

// Bei Statuswechsel auf Bewerber bzw. Student -> soll ZGV brücksichtigt werden
define('ZGV_CHECK', true);

// Bei Statuswechsel auf Bewerber -> bei true wird email (INFOMAIL_BEWERBER) an den Bewerber geschickt
define('SEND_BEWERBER_INFOMAIL', false);

// Infotext der an Bewerber gesendet wird
define('INFOMAIL_BEWERBER', 'Vielen Dank für Ihr Interesse an einem Studium

Ihre Bewerbung ist vollständig und wurde akzeptiert.
Um die Anmeldung zum Studium abzuschließen, bitten wir Sie, innerhalb der Anmelde- und Zulassungsfrist zu den genannten Öffnungszeiten im Sekretariat persönlich vorbeizukommen.

Anmelde- und Zulassungsfrist:
1.9.2014 - 31.10.2014

Öffnungszeiten:
Mo-Fr 9:00-12:00 Uhr
Mi 13:30-15:30 Uhr

Mit freundlichen Grüßen,
Ihre Fachhochschule');

// Bei neuen Studierenden UID automatisch als Matrikelnummer setzen (true|false)
define('SET_UID_AS_MATRIKELNUMMER',false);

// Bei neuen Studierenden UID automatisch als Personenkennzeichen setzen (true|false)
define('SET_UID_AS_PERSONENKENNZEICHEN',false);

// Legt fest ob fuer Studierende eine Alias EMail Adresse generiert wird (true|false)
define('GENERATE_ALIAS_STUDENT',true);

// Wie viele Tage nach Semesterstart soll bei der Neuanlage von Studierenden noch das aktuelle Semester vorgeschlagen werden.
define('VILESCI_PERSON_NEU_STUDIENSEMESTER_UEBERGANGSFRIST',30);

// Legt fest ob beim Anlegen von neuen Studierenden nur Wintersemester als Default vorgeschlagen werden oder auch Sommersemester (true | false)
define('VILESCI_PERSON_NEU_STUDIENSEMESTER_WINTERONLY',false);

// Anzeigeoptionen für den Studienplan im CIS
define('CIS_STUDIENPLAN_SEMESTER_ANZEIGEN', false);
define('CIS_STUDIENPLAN_LVPLANLINK_ANZEIGEN',true);

//Legt fest ob ein User zu einer LV angemeldet sein muss um Detailinformationen abrufen zu können. (true|false)
define('CIS_LEHRVERANSTALTUNG_WENNANGEMELDET_DETAILS_ANZEIGEN', false);

// Prestudent_ID des Dummy_Studenten (zB fuer Testtool)
define('PRESTUDENT_ID_DUMMY_STUDENT', 13478);

//Legt fest ob die Option für alle Räume im Saalplan Dropdown angezeigt werden soll. (true|false)
define('CIS_SAALPLAN_ALLERAEUME_OPTION', false);

//Legt fest ob Bestätigungsmails über eine Anmelung zu einer Prüfung an eine einzelne Person erfolgt oder an den jeweiligen Lektor. (Leerstring für jeweiligen Lektor | uid);
define('CIS_PRUEFUNG_MAIL_EMPFAENGER_ANMEDLUNG',"");

// Username fuer STIP Schnittstelle
define('STIP_USER_NAME','stipendienstelle');
// Passwort fuer STIP Schnittstelle
define('STIP_USER_PASSWORD','password');

// Array mit Usern die nicht Kollidieren
define('KOLLISIONSFREIE_USER',serialize(array('_DummyLektor')));

// Soll der Lageplan am Infoterminal angezeigt werden (true|false)
//define('CIS_INFOSCREEN_LAGEPLAN_ANZEIGEN', true);

//Wenn auf 'true' gesetzt, dann wird im FAS beim Konto das Feld fuer die CreditPoints angezeigt
define('FAS_KONTO_SHOW_CREDIT_POINTS','false');

// Wenn definiert, wird bei der Vorrückung der Lehreinheiten nicht der Stundensatz des Vorjahres eingetragen.
// Erlaubt sind numerische Werte oder der Wert "default".
// Bei "default" wird der Standard-Stundensatz des Lektors (aus tbl_mitarbeiter) ermittelt, und dieser eingetragen.
// Wenn numerisch, wird dieser Wert bei allen LektorInnen eingetragen.
// Wenn nicht definiert, wird der Stundensatz des Vorjahres übernommen.
define('VILESCI_STUNDENSATZ_VORRUECKUNG', '');

// Wenn true, werden die Content-Aufrufe des CIS in der tbl_webservicelog mitgeloggt. Zuvor manuell einen neuen Webservicetyp "content" anlegen!
define('LOG_CONTENT', false);

// ContentID of default content-template for reports. New contents will be childs of this.
define('REPORT_CONTENT_TEMPLATE', '');

?>
