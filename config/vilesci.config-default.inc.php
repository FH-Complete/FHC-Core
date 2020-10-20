<?php
/**
 * Vorlage fuer Vilesci Konfigurationsdatei
 * Diese Datei muss auf vilesci.config.inc.php kopiert werden
 */

// Error Reporting
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Encoding
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
setlocale (LC_ALL, 'de_DE.UTF8', 'de_DE@euro', 'de_DE', 'de', 'DE', 'ge', 'German');

// Zeitzone
date_default_timezone_set('Europe/Vienna');

// Connection Strings zur Datenbank
define('DB_SYSTEM', 'pgsql');
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'fhcomplete');
define('DB_USER', 'vilesci');
define('DB_PASSWORD', 'vilesci');
define('DB_CONNECT_PERSISTENT', TRUE);
define('CONN_CLIENT_ENCODING', 'UTF-8' );

//Connection String Infoscreen
define('INFOSCREEN_USER', '');
define('INFOSCREEN_PASSWORD', '');

// Name des Servers (benoetigt fuer Cronjobs
define('SERVER_NAME', 'localhost');

// URL zu FHComplete Root
define('APP_ROOT', 'http://www.fhcomlete.org/build/');
// URL zu RDF Verzeichnis
define('XML_ROOT', 'http://www.fhcomlete.org/build/rdf/');
// Pfad zu Document Root
define('DOC_ROOT', '/var/www/html/build/');
// URL zu CIS
define('CIS_ROOT', 'http://www.fhcomlete.org/build/');

// Externe Funktionen - Unterordner im Include-Verzeichnis
define('EXT_FKT_PATH', 'tw');

// Bezeichnung des Campus
define('CAMPUS_NAME','');

// Fuer Mails etc
define('DOMAIN', 'example.com');

// Ordner für DMS Dokumente
define('DMS_PATH', '/var/fhcomplete/documents/dms/');

// Authentifizierungsmethode
// Moegliche Werte:
// auth_mixed    - htaccess mit LDAP (Default)
// auth_demo     - Demo Modus (.htaccess)
// auth_session  - Sessions mit LDAP (Testbetrieb)
define('AUTH_SYSTEM', 'auth_demo');
// Gibt den Namen fuer die htaccess Authentifizierung an (muss mit dem Attribut AuthName im htaccess uebereinstimmen)
define('AUTH_NAME', 'FH Complete');

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
define('LDAP_SERVER', 'ldap://ldap.example.com');
define('LDAP_PORT', 389);
define('LDAP_STARTTLS', true);
define('LDAP_BASE_DN', 'ou=People,dc=example,dc=com');
define('LDAP_BIND_USER', null);
define('LDAP_BIND_PASSWORD', null);
define('LDAP_USER_SEARCH_FILTER', 'uid');

// 2. LDAP Server (zB wenn Mitarbeiter und Studierende auf 2 getrennten Servern liegen)
/*
define('LDAP2_SERVER', 'ldaps://dc1.example.com');
define('LDAP2_PORT', 636);
define('LDAP2_STARTTLS', false);
define('LDAP2_BASE_DN', 'ou=Mitarbeiter,dc=example,dc=com');
define('LDAP2_BIND_USER', 'cn=fhcomplete,dc=example,dc=com');
define('LDAP2_BIND_PASSWORD', 'Pa55w0rd');
define('LDAP2_USER_SEARCH_FILTER', 'sAMAccountName');
*/

// LDAP MASTER SERVER fuer Passwort Aenderungen
define('LDAP_SERVER_MASTER', LDAP_SERVER);

// Default Password fuer neue Accounts
// Hier sollte ein langes geheimes Passwort gesetzt werden!
define('ACCOUNT_ACTIVATION_PASSWORD', '');

// Attribut fuer Zutrittskartennummer im LDAP
define('LDAP_CARD_NUMBER', 'twHitagCardNumber');
// Attribut fuer Zutrittskartennummer2 im LDAP
define('LDAP_CARD_NUMBER2', 'twCardNumber');

// Ablauffristen fuer die Accounts in Wochen (mind. 2)
define('DEL_MITARBEITER_WEEKS', '52');
define('DEL_STUDENT_WEEKS', '26');
define('DEL_ABBRECHER_WEEKS', '3');

define('DEFAULT_LANGUAGE', 'German');

// Wie viele Tage sollen im LVPlan angezeigt werden
define('TAGE_PRO_WOCHE', '7');

// Obergrenze fuer Semesterstunden die pro Semester pro Lektor unterrichtet werden duerfen
// Externe Lektoren
define('WARN_SEMESTERSTD_FREI', '120');
// Fixangestellte Lektoren
define('WARN_SEMESTERSTD_FIX', '320');

//Wochen als Grundlage zur Berechnung der Lektorenmeldung
define('BIS_SWS_WOCHEN', 40);

// E-Mail Einstellungen
// Mail-Adressen (Angabe von mehreren Addressen mit ',' getrennt moeglich)

// Wenn MAIL_FROM gesetzt ist, werden alle Mails mit diesem Absender versandt
define('MAIL_FROM', '');

// Wenn MAIL_DEBUG gesetzt ist, werden alle Mails an diese Adresse gesendet
define('MAIL_DEBUG', 'invalid@example.com');
// Geschaeftsstelle / Personalabteilung
define('MAIL_GST', 'invalid@example.com');
// Administrator
define('MAIL_ADMIN', 'invalid@example.com');
// LVPlan-Stelle
define('MAIL_LVPLAN', 'invalid@example.com');
// ServerAdministratoren
define('MAIL_IT', 'invalid@example.com');
// Support
define('MAIL_SUPPORT', 'invalid@example.com');
// Lehrgaenge
define('MAIL_LG', 'invalid@example.com');
// Infocenter
define('MAIL_INFOCENTER','invalid@example.com');

// Default Anmerkung fuer neue Lehreinheiten
// Beispiel: 'Abhaengigkeiten von anderen LV\'s\n\nSpez. Software/Equipment:\n\n'
define ('LEHREINHEIT_ANMERKUNG_DEFAULT', '');

//Pfad zu den Projektarbeitsabgaben
define('PAABGABE_PATH', '/var/fhcomplete/documents/paabgabe/');

// ***** Mantis Bugtracker *****
define('MANTIS_PFAD', 'http://www.example.com/mantis/api/soap/mantisconnect.php?wsdl');
define('MANTIS_USERNAME', (isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:''));
define('MANTIS_PASSWORT', (isset($_SERVER['PHP_AUTH_PW'])?$_SERVER['PHP_AUTH_PW']:''));

//Name der aktiven Addons getrennt mit ;
define('ACTIVE_ADDONS', '');

//Wenn auf 'true' gesetzt, dann wird im FAS ein 3. Feld für die Eingabe von Reihungstest
//Punkten angezeigt
define('RT_PUNKTE3', 'false');

// **** nicht aendern ****
define('TABLE_ID', '_id');
define('TABLE_BEGIN', 'tbl_');
define('VIEW_BEGIN', 'vw_');

//Legt fest ob die Personalnummer beim Anlegen NULL sein soll
define('FAS_PERSONALNUMMER_GENERATE_NULL', false);

// Legt fest ob Felder mit Reihgungstest-Basispunkten im Reiter Aufnahme-Termine angezeigt werden
define('FAS_REIHUNGSTEST_PUNKTE_BASISGEBIET_ANZEIGEN', false);

// API Informationen
define('FHC_REST_API_KEY', 'testapikey@fhcomplete.org');
define('FHC_REST_USER', 'username');
define('FHC_REST_PASSWORD', 'password');

/**
 * Signatur
 * DEFAULT: https://signatur.example.com/api/sign
 */
define('SIGNATUR_URL', 'https://signatur.example.com/api/sign');
// User für Zugriff auf Signaturserver
define('SIGNATUR_USER', 'username');
// Passwort für Zugriff auf Signaturserver
define('SIGNATUR_PASSWORD', 'password');
// Signaturprofil das verwendet werden soll
define('SIGNATUR_DEFAULT_PROFILE', 'FHC_AMT_GROSS_DE');

/**
 * Datenverbund Anbindung
 */
// Code der Bildungseinrichtung
define('DVB_BILDUNGSEINRICHTUNG_CODE','XX');
// Datenverbund-Portal
define('DVB_PORTAL', 'https://stubei-p.portal.at');
// Username
define('DVB_USERNAME','username');
// Passwort
define('DVB_PASSWORD','passwort');

define('CI_ENVIRONMENT', 'development'); // Code igniter environment variable

// BIS Personalmeldung

// Studiengaenge, die nicht gemeldet werden
define('BIS_EXCLUDE_STG', array());

// Basis Vollzeit Arbeitsstunden für Berechnung von Jahresvollzeitaequivalenz JVZAE (echte Dienstverträge)
define('BIS_VOLLZEIT_ARBEITSSTUNDEN', '40');

// Basis Vollzeit Semesterwochenstunden für Berechnung von Jahresvollzeitaequivalenz JVZAE auf Stundenbasis (freie Dienstverträge)
define('BIS_VOLLZEIT_SWS_EINZELSTUNDENBASIS', '15');

// Basis Vollzeit Semesterwochenstunden für Berechnung von Jahresvollzeitaequivalenz JVZAE für inkludierte Lehre bei echten Dienstverträgen
define('BIS_VOLLZEIT_SWS_INKLUDIERTE_LEHRE', '25');

// Semester Gewichtung für Berechnung von Jahresvollzeitaequivalenz JVZAE
define('BIS_HALBJAHRES_GEWICHTUNG_SWS', 0.5);

// Jahrespauschale fuer studentische Hilfskraefte (in Stunden)
define('BIS_PAUSCHALE_STUDENTISCHE_HILFSKRAFT', 0);

// Jahrespauschale fuer sonstige Dienstverhaeltnisse, zb Werkvertrag (in Stunden)
define('BIS_PAUSCHALE_SONSTIGES_DIENSTVERHAELTNIS', 0);

define('BIS_FUNKTIONSCODE_1234_ARR', array(
	'vertrBefugter' => 1,		// Vertretungsbefugte/r des Erhalters (GF, Prokura)
	'kollegium_Ltg' => 2,		// Leiter/in des Kollegiums
	'kollegium_Ltg' => 2,		// Leiter/in des Kollegiums
	'kollegium_stvLtg' => 3,	// stellv. Leiter/In des Kollegiums
	'kollegium' => 4			// Mitglied des Kollegiums
));

// Liste der Leitungsfunktionen
define('BIS_FUNKTIONSCODE_5_ARR', array(
	'Leitung'
));

// Organisationseinheitstypen bei denen KEINE Leiter gemeldet werden
define('BIS_FUNKTIONSCODE_6_ARR', array(
	'Team'
));

// Standortcode fuer Lehrgaenge
define('BIS_STANDORTCODE_LEHRGAENGE', '0');

// bPk Abfrage
define('BPK_FUER_ALLE_BENUTZER_ABFRAGEN', false);
?>
