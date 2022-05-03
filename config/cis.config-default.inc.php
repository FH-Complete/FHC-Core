<?php
/**
 * Vorlage fuer CIS Konfigurationsdatei
 * Diese Datei muss auf cis.config.inc.php kopiert werden
 */
// Error reporting
ini_set('display_errors','1');
error_reporting(E_ALL);

// Encoding
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
setlocale (LC_ALL, 'de_AT.utf8');
setlocale (LC_NUMERIC, 'C');

// Zeitzone
date_default_timezone_set('Europe/Vienna');

// Connection Strings zur Datenbank
define("DB_SYSTEM","pgsql");
define("DB_HOST","localhost");
define("DB_PORT","5432");
define("DB_NAME","fhcomplete");
define("DB_USER","web");
define("DB_PASSWORD","web");
define("DB_CONNECT_PERSISTENT",TRUE);
define('CONN_CLIENT_ENCODING','UTF-8' );

// Pfad fuer Logfiles
define('LOG_PATH','/var/fhcomplete/log/');

// Name des Servers (benoetigt fuer Cronjobs)
define('SERVER_NAME','www.technikum-wien.at');

// Ablagepfad der hochgeladenen Benotungstool Dokumente
define('BENOTUNGSTOOL_PATH','/var/fhcomplete/documents/benotungstool/');
// Ablagepfad der Dokumente des DMS (sollte ausserhalb von htdocs liegen)
define('DMS_PATH','/var/fhcomplete/documents/dms/');
// Import Verzeichnis fuer DMS
define('IMPORT_PATH','/var/fhcomplete/documents/import/');
// Ablagepfad der hochgeladenen Projektarbeiten (sollte ausserhalb von htdocs liegen)
define('PAABGABE_PATH','/var/fhcomplete/documents/paabgabe/');

// Pfad zu den Rauminfos
define('RAUMINFO_PATH','/var/www/html/build/rauminfos/');

// URL zu RDF Verzeichnis
define('XML_ROOT','http://www.fhcomplete.org/build/rdf/');
// URL zu Application Root
define('APP_ROOT','http://www.fhcomplete.org/build/');
// Pfad zu Document Root
define('DOC_ROOT','/var/www/html/build/');
// URL zu Vilesci Root
define('VILESCI_ROOT','http://www.fhcomplete.org/build/');

// Externe Funktionen - Unterordner im Include-Verzeichnis
define('EXT_FKT_PATH','tw');

// ID des CMS-Contents bei dem das CIS Menue beginnt
define('CIS_MENU_ENTRY_CONTENT',1);

// Zusaetzliche Links bei News anzeigen (Lehrziele, Allg. Download, Newsgroups)
define('CIS_EXT_MENU',true);

// Legt fest ob bei den Zeitsperren der Bereich fuer die Resturlaubstage angezeigt wird
define('URLAUB_TOOLS',true);

// Authentifizierungsmethode
// Moegliche Werte:
// auth_mixed    - htaccess mit LDAP (Default)
// auth_session  - Sessions mit LDAP (Testbetrieb)
define("AUTH_SYSTEM", "auth_demo");
// Gibt den Namen fuer die htaccess Authentifizierung an (muss mit dem Attribut AuthName im htaccess uebereinstimmen)
define("AUTH_NAME","FH-Complete");

/*
 * LDAP Einstellungen
 *
 * LDAP_SERVER: LDAP Server URL inkl. ldap:// bzw ldaps://
 * LDAP_PORT: LDAP Port (389 | 636)
 * LDAP_STARTTLS: Starttls für Verschlüsselung starten (true | false)
 * LDAP_BASE_DN: Basis DN der User (ou=People,dc=example,dc=com)
 * LDAP_BIND_USER: DN des Users falls eine Authentifizierung am LDAP noetig ist oder null
 * LDAP_BIND_PASSWORD: Passwort des Users falls eine Authentifizierung am LDAP noetig ist oder null
 * LDAP_USER_SEARCH_FILTER: LDAP Attribut in dem der Username steht nach dem gesucht wird (uid | sAMAccountName)
 */
define('LDAP_SERVER','ldap://ldap.example.com');
define('LDAP_PORT',389);
define('LDAP_STARTTLS',true);
define('LDAP_BASE_DN','ou=People,dc=example,dc=com');
define('LDAP_BIND_USER',null);
define('LDAP_BIND_PASSWORD',null);
define('LDAP_USER_SEARCH_FILTER','uid');

// 2. LDAP Server (zB wenn Mitarbeiter und Studierende auf 2 getrennten Servern liegen)
/*
define('LDAP2_SERVER','ldaps://dc1.example.com');
define('LDAP2_PORT',636);
define('LDAP2_STARTTLS',false);
define('LDAP2_BASE_DN','ou=Mitarbeiter,dc=example,dc=com');
define('LDAP2_BIND_USER','cn=fhcomplete,dc=example,dc=com');
define('LDAP2_BIND_PASSWORD','Pa55w0rd');
define('LDAP2_USER_SEARCH_FILTER','sAMAccountName');
*/

// LDAP MASTER SERVER fuer Passwort Aenderungen
define('LDAP_SERVER_MASTER',LDAP_SERVER);

// Default Password fuer neue Accounts
// Hier sollte ein langes geheimes Passwort gesetzt werden!
define('ACCOUNT_ACTIVATION_PASSWORD','');

// Attribut fuer Zutrittskartennummer im LDAP
define("LDAP_CARD_NUMBER","twHitagCardNumber");
// Attribut fuer Zutrittskartennummer2 im LDAP
define("LDAP_CARD_NUMBER2","twCardNumber");

// Domain fuer Mailadressen etc.
define('DOMAIN','technikum-wien.at');

// Legt fest ob diverse Mailverteiler fuer Studenten gesperrt sind
define('MAILVERTEILER_SPERRE', true);

// Bezeichnung des Campus
define('CAMPUS_NAME','FH Technikum Wien');

// Anzahl der Tag die eine Nachricht am Pinboard angezeigt wird.
define("MAXNEWSALTER",60);
// Anzahl der Newseintraege die maximal angezeigt werden
define('MAXNEWS', 99);

// legt fest wie die Attribute in der Tabelle Sprache heissen (benoetigt fuer LVinfo)
define("ATTR_SPRACHE_DE","German");
define("ATTR_SPRACHE_EN","English");

// Konstanten fuer die Reservierung
// Tage ab wann ein Mitarbeiter reservieren kann.
define('RES_TAGE_LEKTOR_MIN','5');
// Datum bis wann im voraus ein Mitarbeiter reservieren kann.
define('RES_TAGE_LEKTOR_BIS','2020-01-01');

// Wie viele Tage pro Woche werden im LVPlan angezeigt
define('TAGE_PRO_WOCHE','7');

// Kalenderkategorie beim Export des LVPlans
define('LVPLAN_KATEGORIE', 'StundenplanTW');

// Key zum Verschluesseln des LV-Plan Google Links
define('LVPLAN_CYPHER_KEY',pack('H*', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'));

// Gibt an ob Termine aus dem Vorsemester nach der Semesterhaelfte des Folgesemesters
// noch im pers. LVPan aufscheinen.
// true | false
define('LVPLAN_LOAD_UEBER_SEMESTERHAELFTE', false);

// Default Stylesheet
define('DEFAULT_STYLE','tw');
// Layout Wechsel im CIS moeglich?
define('CHOOSE_LAYOUT',false);

// Default Sprache
define('DEFAULT_LANGUAGE','German');

// E-Mail Einstellungen
// Wenn MAIL_FROM gesetzt ist, werden alle Mails mit diesem Absender versandt
define('MAIL_FROM','');

// Wenn MAIL_DEBUG gesetzt ist, werden alle Mails an diese Adresse gesendet
define('MAIL_DEBUG','invalid@technikum-wien.at');
// Geschaeftsstelle / Personalabteilung
define('MAIL_GST','invalid@technikum-wien.at');
//Administrator
define('MAIL_ADMIN','invalid@technikum-wien.at');
//LVPlan-Stelle
define('MAIL_LVPLAN','invalid@technikum-wien.at');
//CIS Admin
define('MAIL_CIS','invalid@technikum-wien.at');
//Ansprechpartner fuer Incoming
define('MAIL_INTERNATIONAL','invalid@technikum-wien.at');
//Ansprechpartner fuer Outgoing
define('MAIL_INTERNATIONAL_OUTGOING', 'invalid@technikum-wien.at');

define('ANZAHL_PREINTERESSENT','5');

//Name der aktiven Addons getrennt mit ;
define('ACTIVE_ADDONS','');

// ***** OPUS *****
// Angaben fuer OPUS Schnittstelle
define('OPUS_SERVER','www.technikum-wien.at');
define('OPUS_USER','bla');
define('OPUS_PASSWD','bla');
define('OPUS_DB','bla');
// Pfad zu den Opus Volltexten
define('OPUS_PATH_PAA','/var/www/opus/volltexte/');

// ***** SOGO *****
define('SOGO_SERVER','https://sogo.technikum-wien.at/SOGo/');
define('SOGO_USER','user');
define('SOGO_PASSWD','passwort');

// ***** Nicht aendern *****
define('TABLE_ID','_id');
define('TABLE_BEGIN','tbl_');
define('VIEW_BEGIN','vw_');

//Gibt an, ob das Studienbuchblatt im CIS gedruckt werden kann
define('CIS_DOKUMENTE_STUDIENBUCHLBATT_DRUCKEN',true);

//Gibt an, ob die Studienerfolgsbestätigung im CIS gedruckt werden kann
define('CIS_DOKUMENTE_STUDIENERFOLGSBESTAETIGUNG_DRUCKEN',true);

//Gibt an, ob die archivierten Self-Service Dokumente im CIS heruntergeladen werden koennen
define('CIS_DOKUMENTE_SELFSERVICE', true);

//**** INFOSCREEN ****
//Gibt an, ob der Lageplan im Infoterminal angezeigt werden soll.
define('CIS_INFOSCREEN_LAGEPLAN_ANZEIGEN',true);
//Gibt an, ob News im Infoterminal angezeigt werden soll.
define('CIS_INFOSCREEN_NEWS_ANZEIGEN',false);

//User, welcher für das Anlegen von Anrechnungen bei der Prüfungsanmeldung verwendet wird
define('CIS_PRUEFUNGSANMELDUNG_USER','p.pruefungsanmeldung');

// Anmeldefristen für Prüfungen in Tagen;
// Wenn nicht definiert: 3
//define('CIS_PRUEFUNGSANMELDUNG_FRIST',3);

// Mindestvorlaufzeit beim Anlegen von Prüfungen in Tagen
// Wenn nicht definiert: 14
//define('CIS_PRUEFUNGSTERMIN_FRIST',14);

// Soll für die Prüfungsanmeldungen eine Anrechnung erstellt werden
define('CIS_PRUEFUNGSANMELDUNG_ANRECHNUNG', true);

//Gibt an, ob der Bereich zur Anmeldung zu Pruefungen des gesamten Studiengangs angezeigt werden soll
define('CIS_PRUEFUNGSANMELDUNG_LEHRVERANSTALTUNGEN_AUS_STUDIENGANG', true);

//Gibt an, ob mehrere Pruefungen zur selben Zeit im selben Raum stattfinden duerfen
define('CIS_PRUEFUNGSANMELDUNG_ERLAUBE_TERMINKOLLISION', true);

//Gibt an, wie viele Semester aus der Vergangenheit unter Meine LV angezeigt werden
define('CIS_MEINELV_ANZAHL_SEMESTER_PAST', 3);

//Gibt an, welche Buchungstypen bei der Überprüfung auf Einzahlung berücksichtigt werden
define('CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN', serialize(array("Studiengebuehr")));

//Gibt an bei welcher Länge die LV-Bezeichnungen im Menü abgeschnitten werden. Default: 21
define('CIS_LVMENUE_CUTLENGTH', 21);

// Gibt an, auf welche Seite TicketIds ala #1234 im Jahresplan verlinkt werden zB zur Verlinkung in Bugtracker
define('JAHRESPLAN_TICKET_LINK','https://bug.technikum-wien.at/otrs/index.pl?Action=AgentTicketZoom;TicketNumber=');

//Gibt an ob der Block zu Verplanung in geteilter Arbeitszeit bei den Zeitwünschen angezeigt wird. Default: false
define('CIS_ZEITWUNSCH_GD', false);

// Covid-Status anzeigen
define('CIS_SHOW_COVID_STATUS', false);

// Docsbox configs
define('DOCSBOX_SERVER', 'http://docconverter.technikum-wien.at/');
define('DOCSBOX_PATH_API', 'api/v1/');
define('DOCSBOX_CONVERSION_TIMEOUT', 30); // seconds
define('DOCSBOX_WAITING_SLEEP_TIME', 1);

?>

