<?php
/**
 * Vorlage fuer Vilesci Konfigurationsdatei
 * Diese Datei muss auf vilesci.config.inc.php kopiert werden
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
define("DB_HOST","www.technikum-wien.at");
define("DB_PORT","5432");
define("DB_NAME","devvilesci");
define("DB_USER","bla");
define("DB_PASSWORD","bla");
define("DB_CONNECT_PERSISTENT",TRUE);
define('CONN_CLIENT_ENCODING','UTF-8' );

//Connection String Infoscreen
define("INFOSCREEN_USER","");
define("INFOSCREEN_PASSWORD","");
	
// Name des Servers (benoetigt fuer Cronjobs
define('SERVER_NAME','vilesci.technikum-wien.at');
	
// URL zu FHComplete Root
define('APP_ROOT','http://www.technikum-wien.at/');
// URL zu RDF Verzeichnis
define('XML_ROOT','http://www.technikum-wien.at/rdf/');
// Pfad zu Document Root
define('DOC_ROOT','/var/www/');
// URL zu CIS
define('CIS_ROOT','http://www.technikum-wien.at/');

// Externe Funktionen - Unterordner im Include-Verzeichnis
define('EXT_FKT_PATH','tw');
	
// Fuer Mails etc
define('DOMAIN','technikum-wien.at');

// Ordner für DMS Dokumente 
define('DMS_PATH','/var/fhcomplete/documents/dms/');

// Authentifizierungsmethode 
// Moegliche Werte: 
// auth_mixed    - htaccess mit LDAP (Default)
// auth_demo     - Demo Modus (.htaccess)
// auth_session  - Sessions mit LDAP (Testbetrieb)
define("AUTH_SYSTEM", "auth_mixed");
// Gibt den Namen fuer die htaccess Authentifizierung an (muss mit dem Attribut AuthName im htaccess uebereinstimmen)
define("AUTH_NAME","Technikum-Wien");


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

// Attribut fuer Zutrittskartennummer im LDAP
define("LDAP_CARD_NUMBER","twHitagCardNumber");
// Attribut fuer Zutrittskartennummer2 im LDAP
define("LDAP_CARD_NUMBER2","twCardNumber");

// Ablauffristen fuer die Accounts in Wochen (mind. 2)
define('DEL_MITARBEITER_WEEKS','52');
define('DEL_STUDENT_WEEKS','26');
define('DEL_ABBRECHER_WEEKS','3');

define('DEFAULT_LANGUAGE','German');

// Wie viele Tage sollen im LVPlan angezeigt werden
define('TAGE_PRO_WOCHE','7');

// Obergrenze fuer Semesterstunden die pro Semester pro Lektor unterrichtet werden duerfen
// Externe Lektoren
define('WARN_SEMESTERSTD_FREI','120');
// Fixangestellte Lektoren
define('WARN_SEMESTERSTD_FIX','320');

//Wochen als Grundlage zur Berechnung der Lektorenmeldung 
define('BIS_SWS_WOCHEN', 40); 

// E-Mail Einstellungen
// Mail-Adressen (Angabe von mehreren Addressen mit ',' getrennt moeglich)

// Wenn MAIL_FROM gesetzt ist, werden alle Mails mit diesem Absender versandt
define('MAIL_FROM','');

// Wenn MAIL_DEBUG gesetzt ist, werden alle Mails an diese Adresse gesendet
define('MAIL_DEBUG','invalid@technikum-wien.at');
// Geschaeftsstelle / Personalabteilung
define('MAIL_GST','invalid@technikum-wien.at');
// Administrator
define('MAIL_ADMIN','invalid@technikum-wien.at');
// LVPlan-Stelle
define('MAIL_LVPLAN','invalid@technikum-wien.at');
// ServerAdministratoren
define('MAIL_IT','invalid@technikum-wien.at');
// Support
define('MAIL_SUPPORT','invalid@technikum-wien.at');
// Lehrgaenge
define('MAIL_LG','invalid@technikum-wien.at');

// Default Anmerkung fuer neue Lehreinheiten
// Beispiel: 'Abhaengigkeiten von anderen LV\'s\n\nSpez. Software/Equipment:\n\n'
define ('LEHREINHEIT_ANMERKUNG_DEFAULT', '');

//Gibt an welche Funktion zur generierung des PDF Files herangezogen wird
//moegliche Werte: FOP | XSLFO2PDF
define ('PDF_CREATE_FUNCTION','XSLFO2PDF');
	
//Pfad zu den Projektarbeitsabgaben
define('PAABGABE_PATH','/var/fhcomplete/documents/paabgabe/');
	
// ***** Mantis Bugtracker *****
define('MANTIS_PFAD','http://www.example.com/mantis/api/soap/mantisconnect.php?wsdl');
define('MANTIS_USERNAME',(isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:''));
define('MANTIS_PASSWORT',(isset($_SERVER['PHP_AUTH_PW'])?$_SERVER['PHP_AUTH_PW']:''));

// ***** Moodle *****
define('MOODLE_PATH','http://www.technikum-wien.at/moodle/'); // DEPRECATED
define("CONN_STRING_MOODLE","host=localhost dbname=bla user=bla password=bla"); // nur für Moodle 1.9
define('MOODLE_TOKEN24','');

//Name der aktiven Addons getrennt mit ;
define('ACTIVE_ADDONS','');

//Wenn auf 'true' gesetzt, dann wird im FAS ein 3. Feld für die Eingabe von Reihungstest
//Punkten angezeigt
define('RT_PUNKTE3','false');

// **** nicht aendern ****
define('TABLE_ID','_id');
define('TABLE_BEGIN','tbl_');
define('VIEW_BEGIN','vw_');

//Legt fest ob die Personalnummer beim Anlegen NULL sein soll
define('FAS_PERSONALNUMMER_GENERATE_NULL', false);
	
?>
