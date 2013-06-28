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
define("DB_USER","user");
define("DB_PASSWORD","password");
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

//LDAP_SERVER: Speichert die Adresse des LDAP Servers
define("LDAP_SERVER","ldap.technikum-wien.at");
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
?>
