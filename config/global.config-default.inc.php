<?php

//Default-Werte für neue Lehreinheiten
define('DEFAULT_LEHREINHEIT_SPRACHE','German');
define('DEFAULT_LEHREINHEIT_RAUMTYP','Dummy');
define('DEFAULT_LEHREINHEIT_RAUMTYP_ALTERNATIV','Dummy');
define('DEFAULT_LEHREINHEIT_LEHRFORM','UE');

// Defaul Trennzeichen fuer E-Mail Empfaenger wenn nicht ueber Variablen ueberschrieben
define('DEFAULT_EMAILADRESSENTRENNZEICHEN',',');

// Gibt an ob neue Mitarbeiter per default fixangestellt sind oder nicht
define('DEFAULT_MITARBEITER_FIXANGESTELLT', true);

//Anzeigeoptionen für Lehrveranstaltungen im CIS
define('CIS_LEHRVERANSTALTUNG_NEWSGROUPS_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_FEEDBACK_ANZEIGEN',true);
define('CIS_LEHRVERANSTALTUNG_DOWNLOAD_ANZEIGEN',true);
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
define('CIS_LEHRVERANSTALTUNG_ANRECHNUNG_ANZEIGEN', true);

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

// Anzeige des Links zum Profil von Personen bei globaler Suche
define('CIS_SUCHE_PROFIL_ANZEIGEN',true);

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

// Gibt an ob die Note im Notenfreigabemail enthalten ist oder nicht
// Aus Datenschutzgründen ist dies per default deaktiviert
define('CIS_GESAMTNOTE_FREIGABEMAIL_NOTE', false);

// Gibt an ob in der Notenliste der Studierenden nur offizielle Noten oder alle angezeigt werden
define('CIS_NOTENLISTE_OFFIZIELL_ANZEIGEN', false);

// Gibt an ob in der Notenliste der Durchschnitt und der gewichtete Durchschnitt angezeigt werden
define('CIS_NOTENLISTE_DURCHSCHNITT_ANZEIGEN', true);

// Grenzwerte für Anwesenheit
define('FAS_ANWESENHEIT_ROT', 70);
define('FAS_ANWESENHEIT_GELB', 90);

// Legt einen Prüfungstermin an wenn eine neue Note erfasst wird
define('FAS_PRUEFUNG_BEI_NOTENEINGABE_ANLEGEN',false);

// Legt fest ob die Uebernahme der Reihungstestpunkte im FAS moeglich ist
// true | false
define('FAS_REIHUNGSTEST_PUNKTEUEBERNAHME', true);

// Legt fest ob bei der Uebernahme der Reihungstestpunkte die Punkte
//oder Prozentpunkte uebernommen werden true=Punkte, false=Prozentpunkte
define('FAS_REIHUNGSTEST_PUNKTE', false);

// Legt fest, welche Reihungstestgebiete bei der Berechnung der Gesamtpunkte NICHT einbezogen werden.
// array(gebiet_id1, gebiet_id2,...)
define('FAS_REIHUNGSTEST_EXCLUDE_GEBIETE', serialize(array()));

// Legt fest ob Messages im FAS angezeigt werden true|false
define('FAS_MESSAGES',false);

// Enable (true) or disable (false) the UDF tab
define('FAS_UDF', true);

// Legt fest ob Aufnahmegruppen bei Reihungstests verwaltet werden true|false
define('FAS_REIHUNGSTEST_AUFNAHMEGRUPPEN',false);

// Legt fest welche OEs nicht zur Stundenobergrenze für Lektoren hinzugerechnet werden
define('FAS_LV_LEKTORINNENZUTEILUNG_STUNDEN_IGNORE_OE', array('eci'));

// Legt fest, ob Vertragsdetails zum Lehrauftrag im Reiter LektorInnenzuteilung angezeigt werden
define('FAS_LV_LEKTORINNENZUTEILUNG_VERTRAGSDETAILS_ANZEIGEN', false);

// Legt fest ob bei Fixangestellten Lektoren der Stundensatz vorgeschlagen wird
define('FAS_LV_LEKTORINNENZUTEILUNG_FIXANGESTELLT_STUNDENSATZ', true);

// Gibt an, ob/ab welchen Studiensemester eine zusätzliche Vertragspruefung der Lektoren erfolgt.
// Ab diesem Semester wird die Lektorenzuordnung nur angezeigt wenn ein erteilter Vertrag vorhanden ist
define('CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON', '');

// Legt fest, ob Vertragsdetails zum Projektauftrag im Reiter Projektarbeit angezeigt werden
define('FAS_STUDIERENDE_PROJEKTARBEIT_VERTRAGSDETAILS_ANZEIGEN', false);

// Anzeigeoptionen für LV-Plan Menü
define('CIS_LVPLAN_EXPORT_ANZEIGEN',true);
define('CIS_LVPLAN_PERSONENAUSWAHL_ANZEIGEN',true);
define('CIS_LVPLAN_LEHRVERBANDAUSWAHL_ANZEIGEN',true);
define('CIS_LVPLAN_ARCHIVAUSWAHL_ANZEIGEN',true);
define('CIS_LVPLAN_ZUSATZMENUE_ANZEIGEN',true);
define('CIS_LVPLAN_SAALPLAN_ANZEIGEN',true);

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

// gibt an ob beim Anlegen von Mitarbeitern ein Alias generiert wird.
define('GENERATE_ALIAS_MITARBEITERIN',true);

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

//Wenn auf true gesetzt, dann wird im FAS beim Konto das Feld fuer die Mahnspanne angezeigt
define('FAS_KONTO_SHOW_MAHNSPANNE', true);

// Wenn definiert, wird bei der Vorrückung der Lehreinheiten nicht der Stundensatz des Vorjahres eingetragen.
// Erlaubt sind numerische Werte oder der Wert "default".
// Bei "default" wird der Standard-Stundensatz des Lektors (aus tbl_mitarbeiter) ermittelt, und dieser eingetragen.
// Wenn numerisch, wird dieser Wert bei allen LektorInnen eingetragen.
// Wenn nicht definiert, wird der Stundensatz des Vorjahres übernommen.
// Bei "nachbeschaeftigungsart" wird
//      bei echten Dienstvertraegen mit voller inkludierter Lehre (-1) der Stundensatz auf null gesetzt
//      bei echten Dienstvertraegen mit teilweise oder nicht inkludierter Lehre der Default Stundensatz gesetzt
//      bei sonstigen Dienstvertraegen der Default Stundensatz gesetzt
define('VILESCI_STUNDENSATZ_VORRUECKUNG', '');

// Wenn true, werden die Content-Aufrufe des CIS in der tbl_webservicelog mitgeloggt. Zuvor manuell einen neuen Webservicetyp "content" anlegen!
define('LOG_CONTENT', false);

// ContentID of default content-template for reports. New contents will be childs of this.
define('REPORT_CONTENT_TEMPLATE', '');

// Schwund in %, der bei Arbeitsplätzen herausgerechnet werden soll.
// zB 5. Dann werden bei 20 Plätzen 5% Schwund herausgerechnet und nur 19 Plätze zurückgegeben
define('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND', 0);

// TeilnehmerInnen von Qualifikationskursen werden mit einem Statusgrund versehen.
// Die ID dieses Statusgrundes kann hier eingegeben werden. Es wird zB vom Infocenter-Tool gesetzt und im Bewerbungstool abgefragt
define('STATUSGRUND_ID_QUALIFIKATIONKURSTEILNEHMER', null);

// EinsteigerInnen ins Sommersemester werden mit einem Statusgrund versehen.
// Die ID dieses Statusgrundes kann hier eingegeben werden. Es wird zB vom Infocenter-Tool gesetzt und im Bewerbungstool abgefragt
define('STATUSGRUND_ID_EINSTIEG_SOMMERSEMESTER', null);

// Studiengangs_kz des Studiengangs "Qualifikationskurse". Der Studiengang hat eine Sonderstellung zB für das Bewerbungstool.
define('STUDIENGANG_KZ_QUALIFIKATIONKURSE', null);

// Gibt an ob der Login ins Testtool ueber das Bewerbungstool stattfindet oder nicht
define('TESTTOOL_LOGIN_BEWERBUNGSTOOL', false);

// Prueft ob Buchungen bereits ins SAP uebertragen wurden und sperrt ggf die Bearbeitung
define('BUCHUNGEN_CHECK_SAP', true);

// Gibt an, ob im FAS die Zahlungsbestaetigungen zum Download / im CIS generell die Zahlungen angezeigt werden
define ('ZAHLUNGSBESTAETIGUNG_ANZEIGEN', true);

// Gibt an, ob im CIS die Zahlungsbestaetigungen fuer Lehrgaenge zum Download angezeigt werden
define ('ZAHLUNGSBESTAETIGUNG_ANZEIGEN_FUER_LEHRGAENGE', true);

// Gibt an, ob im CIS die Zahlungsreferenz angezeigt wird
define ('ZAHLUNGSBESTAETIGUNG_ZAHLUNGSREFERENZ_ANZEIGEN', false);
?>
