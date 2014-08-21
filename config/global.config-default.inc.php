<?php

//Default-Werte für neue Lehreinheiten
define('DEFAULT_LEHREINHEIT_SPRACHE','German');
define('DEFAULT_LEHREINHEIT_RAUMTYP','Dummy');
define('DEFAULT_LEHREINHEIT_RAUMTYP_ALTERNATIV','Dummy');
define('DEFAULT_LEHREINHEIT_LEHRFORM','UE');

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

//Anmerkung bei Unterrichtseinheiten im LV-Plan anzeigen. Anmerkungen bei LV-Plan Sync mitkopieren.
define('LVPLAN_ANMERKUNG_ANZEIGEN',true);
//Gruppieren zeitgleicher Lehreinheiten im LV-Plan
define('LVPLAN_LEHREINHEITEN_GRUPPIEREN',true);

// Ende-Datum des LVPlan Syncs Format: 2014-02-01
// Wenn leer wird jeweils bis Semesterende gesynct
define('LVPLAN_SYNC_ENDE','');

// Soll nach dem LVPlan Sync automatisch das Horde Sync angestoßen werden
define('LVPLAN_HORDE_SYNC',false);

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

/* Schema zur Erstellung der Kurs Kategorien im Moodle
 * Leer oder nicht gesetzt: STSEM -> STG -> Ausbsemester (WS2014 -> BEL -> 1)
 * DEP-STG-JG-STSEM: Department -> STG -> Jahrgang -> StSem (Informationstechnologie und Informationsmanagement -> BIMK -> Jahrgang 2014 -> WS2014)
 */
define('MOODLE_COURSE_SCHEMA','');

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
?>
