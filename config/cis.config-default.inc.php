<?php
ini_set('display_errors','1');
error_reporting(E_ALL);

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
setlocale (LC_ALL, 'de_DE.UTF8','de_DE@euro', 'de_DE', 'de','DE', 'ge','German');


// Connection Strings zur Datenbank
define("CONN_STRING","host=localhost dbname=bla user=bla password=bla");
define("CONN_STRING_MOODLE","host=localhost dbname=bla user=bla password=bla");
define('CONN_CLIENT_ENCODING','LATIN9' );


define('TABLE_ID','_id');
define('TABLE_BEGIN','tbl_');
define('VIEW_BEGIN','vw_');

//Pfad zum Logfile
define('LOG_PATH','/var/www/system/');

//Pfad zum Moodle
define('MOODLE_PATH','http://dav.technikum-wien.at/oesi/portal/trunk/moodle/');
//Moodle verwenden Ja/Nein
define('MOODLE',true);

//Pfad zum Upload-Ordner
define('BENOTUNGSTOOL_PATH','/websites/portal/trunk/documents/benotungstool/');

// XML fuer XSL-Vorlagen
define('XML_ROOT','http://dav.technikum-wien.at/oesi/portal/trunk/rdf/');
define('APP_ROOT','http://dav.technikum-wien.at/oesi/portal/trunk/');

// Externe Funktionen - Unterordner im Include-Verzeichnis
define('EXT_FKT_PATH','tw');

//Zusaetzliches Menue bei Lehrveranstaltungen anzeigen (Lektorenbereich, Info u. Kommunikation, etc)
define('CIS_EXT_MENU',true);

//Legt fest ob bei den Zeitsperren der Bereich fuer die Resturlaubstage angezeigt wird
define('URLAUB_TOOLS',true);

// LDAP_SERVER: Adresse des LDAP Servers
define("LDAP_SERVER","www.technikum-wien.at");
define("LDAP_BASE_DN","ou=People, dc=technikum-wien, dc=at");

// Domain fuer Mailadressen etc.
define('DOMAIN','technikum-wien.at');

// Legt fest ob diverse Mailverteiler fuer Studenten gesperrt sind
define('MAILVERTEILER_SPERRE', true);

// Bezeichnung des Campus
define('CAMPUS_NAME','FH Technikum Wien');

// MAXNEWSALTER: beinhaltet die Anzahl der Tag die eine Nachricht am Pinboard angezeigt wird.
define("MAXNEWSALTER",60);
// MAXNEWS: Anzahl der Newseintraege die maximal angezeigt werden
define('MAXNEWS', 99);

//legt fest wie die Attribute in der Tabelle Sprache heissen (benoetigt fuer LVinfo)
define("ATTR_SPRACHE_DE","German");
define("ATTR_SPRACHE_EN","English");

// Version des aktuellen Stundenplans
define('VERSION','7.1 vom 9.9.2006');

//Konstanten fuer die Reservierung
define('RES_TAGE_STUDENT','1');
define('RES_TAGE_LEKTOR_MIN','5'); // Tage ab wann ein Mitarbeiter reservieren kann.
// define('RES_TAGE_LEKTOR_MAX','60');
define('RES_TAGE_LEKTOR_BIS','2008-08-01'); // Datum bis wann im voraus ein Mitarbeiter reservieren kann.

// Stundenplan
define('TAGE_PRO_WOCHE','7');

/* oesi 31.03.2009 - sollte nicht mehr verwendet werden - liegt unter include/globals.inc.php
// Hintergrundfarben fuer Tabellen beim Zeitwunsch
$cfgStdBgcolor[0]="#FF0000";
$cfgStdBgcolor[1]="#D44128";
$cfgStdBgcolor[2]="#CA8780";
$cfgStdBgcolor[3]="#C0C0C0";
$cfgStdBgcolor[4]="#A2C294";
$cfgStdBgcolor[5]="#4EA83C";
$cfgStdBgcolor[6]="#006000";

// Wochentage auf Deutsch (Zeitwunsch)
$tagbez[1]="Montag";
$tagbez[2]="Dienstag";
$tagbez[3]="Mittwoch";
$tagbez[4]="Donnerstag";
$tagbez[5]="Freitag";
$tagbez[6]="Samstag";
$tagbez[7]="Sonntag";
*/

define('LVPLAN_KATEGORIE', 'StundenplanTW');

//Default Stylesheet
define('DEFAULT_STYLE','tw');

// MAIL Adressen
define('MAIL_DEBUG','oesi@technikum-wien.at');
define('MAIL_GST','pam@technikum-wien.at,oesi@technikum-wien.at');
define('MAIL_ADMIN','vilesci@technikum-wien.at');
define('MAIL_LVPLAN','pam@technikum-wien.at,lvplan@technikum-wien.at');
define('MAIL_CIS','cis@technikum-wien.at');

//OPUS
define('OPUS_SERVER','sirene.technikum-wien.at');
define('OPUS_USER','opus');
define('OPUS_PASSWD','opus');
define('OPUS_DB','opus');
// Projektabgabepfad
define('PAABGABE_PATH','/var/www/htdocs/PaUpload/');
//Pfad von PAAbgabe zum OPUS
define('OPUS_PATH_PAA','/var/www/htdocs/opus/htdocs/volltexte/');

?>
