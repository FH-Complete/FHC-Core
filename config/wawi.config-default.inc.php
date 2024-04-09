<?php
/**
 * Vorlage fuer WaWi Konfigurationsdatei
 * Diese Datei muss auf wawi.config.inc.php kopiert werden
 */

// Error Reporting
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
define("DB_USER","wawi");
define("DB_PASSWORD","wawi");
define("DB_CONNECT_PERSISTENT",TRUE);
define('CONN_CLIENT_ENCODING','UTF-8' );
	
define('SERVER_ROOT','http://www.technikum-wien.at/');
define('APP_ROOT','http://www.technikum-wien.at/wawi/');
	
// Externe Funktionen - Unterordner im Include-Verzeichnis
define('EXT_FKT_PATH','tw');
	
// Fuer Mails etc
define('DOMAIN','technikum-wien.at');

// Authentifizierungsmethode 
// Moegliche Werte: 
// auth_mixed    - htaccess mit LDAP (Default)
// auth_session  - Sessions mit LDAP (Testbetrieb)
define("AUTH_SYSTEM", "auth_mixed");
// Gibt den Namen fuer die htaccess Authentifizierung an (muss mit dem Attribut AuthName im htaccess uebereinstimmen)
define("AUTH_NAME","FHComplete");
	
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
 * LDAP_SERVICEPING: LDAP Service Ping verwenden (true | false) - wirksam für alle LDAP Server
 */
define('LDAP_SERVER','ldap://ldap.example.com');
define('LDAP_PORT',389);
define('LDAP_STARTTLS',true);
define('LDAP_BASE_DN','ou=People,dc=example,dc=com');
define('LDAP_BIND_USER',null);
define('LDAP_BIND_PASSWORD',null);
define('LDAP_USER_SEARCH_FILTER','uid');
define('LDAP_SERVICEPING',true);

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

// Attribut fuer Zutrittskartennummer im LDAP
define("LDAP_CARD_NUMBER","twHitagCardNumber");
// Attribut fuer Zutrittskartennummer2 im LDAP
define("LDAP_CARD_NUMBER2","twCardNumber");

// Mail-Adressen (Angabe von mehreren Addressen mit ',' getrennt moeglich)
// Wenn MAIL_DEBUG gesetzt ist, werden alle Mails an diese Adresse gesendet
define('MAIL_DEBUG','invalid@technikum-wien.at');
// Geschaeftsstelle / Personalabteilung
define('MAIL_GST','invalid@technikum-wien.at');
// Administrator
define('MAIL_ADMIN','invalid@technikum-wien.at');
// LVPlan
define('MAIL_LVPLAN','invalid@technikum-wien.at');
// Serveradministration
define('MAIL_IT','invalid@technikum-wien.at');
// Support
define('MAIL_SUPPORT','invalid@technikum-wien.at');
// Zentraleinkauf
define('MAIL_ZENTRALEINKAUF','info@technikum-wien.at');

//Gibt an welche Funktion zur generierung des PDF Files herangezogen wird
//moegliche Werte: FOP | XSLFO2PDF
define ('PDF_CREATE_FUNCTION','XSLFO2PDF');

// Ordner für DMS Dokumente 
define('DMS_PATH','/var/www/fhcomplete/dms/');
?>
