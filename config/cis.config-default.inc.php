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
setlocale (LC_ALL, 'de_DE.UTF8','de_DE@euro', 'de_DE', 'de','DE', 'ge','German');

// Zeitzone
date_default_timezone_set('Europe/Vienna');

// Connection Strings zur Datenbank
define("DB_SYSTEM","pgsql");
define("DB_HOST","localhost");
define("DB_PORT","5432");
define("DB_NAME","fhcomplete");
define("DB_USER","bla");
define("DB_PASSWORD","bla");
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
define('RAUMINFO_PATH','/var/www/rauminfos/');

// URL zu RDF Verzeichnis
define('XML_ROOT','http://www.technikum-wien.at/rdf/');
// URL zu Application Root
define('APP_ROOT','http://www.technikum-wien.at/');
// Pfad zu Document Root
define('DOC_ROOT','/var/www/');

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
define("AUTH_SYSTEM", "auth_mixed");

// LDAP_SERVER: Adresse des LDAP Servers
define("LDAP_SERVER","www.technikum-wien.at");
define("LDAP_BASE_DN","ou=People, dc=technikum-wien, dc=at");
//User fuer LDAP BIND falls Authentifizierung noetig
define("LDAP_BIND_USER",null);
//Passwort fuer LDAP BIND falls Authentifzierung noetig
define("LDAP_BIND_PASSWORD",null);
//LDAP Attribut in dem der Username steht nach dem gesucht wird
define("LDAP_USER_SEARCH_FILTER","uid");
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

// Default Stylesheet
define('DEFAULT_STYLE','tw');
// Layout Wechsel im CIS moeglich?
define('CHOOSE_LAYOUT',false);

// Default Sprache
define('DEFAULT_LANGUAGE','German');

// E-Mail Einstellungen
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

// ***** MOODLE *****
// Moodle verwenden Ja/Nein
define('MOODLE',true);
// Pfad zum Moodle
define('MOODLE_PATH','http://www.technikum-wien.at/moodle/');
// Connection String fuer Moodle Datenbank (nur Posgresql)
define("CONN_STRING_MOODLE","host=localhost dbname=bla user=bla password=bla");

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

?>
