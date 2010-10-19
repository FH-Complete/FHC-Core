<?php
	ini_set('display_errors','1');
	error_reporting(E_ALL);
	
	mb_internal_encoding("UTF-8");
	mb_regex_encoding("UTF-8");
	setlocale (LC_ALL, 'de_DE.UTF8','de_DE@euro', 'de_DE', 'de','DE', 'ge','German');
	date_default_timezone_set('Europe/Vienna');
	
	// Connection Strings zur Datenbank
	define("DB_SYSTEM","pgsql");
	define("DB_HOST","localhost");
	define("DB_PORT","5433");
	define("DB_NAME","fhcomplete");
	define("DB_USER","user");
	define("DB_PASSWORD","password");
	define("DB_CONNECT_PERSISTENT",TRUE);
	define('CONN_CLIENT_ENCODING','UTF-8' );
	
	define("CONN_STRING_WAWI","host=localhost port=5432 dbname=wawi user=user password=password");

	// Fuer Mails etc
	define('DOMAIN','technikum-wien.at');

	//LDAP_SERVER: Speichert die Adresse des LDAP Servers
	define("LDAP_SERVER","ldap.technikum-wien.at");
	define("LDAP_BASE_DN","ou=People, dc=technikum-wien, dc=at");

	// Mail-Adressen
	define('MAIL_DEBUG','');
	define('MAIL_GST','info@technikum-wien.at');
	define('MAIL_ADMIN','info@technikum-wien.at');
	define('MAIL_LVPLAN','info@technikum-wien.at');
	define('MAIL_IT','info@technikum-wien.at');
	define('MAIL_SUPPORT','info@technikum-wien.at');

	//Gibt an welche Funktion zur generierung des PDF Files herangezogen wird
	//moegliche Werte: FOP | XSLFO2PDF
	define ('PDF_CREATE_FUNCTION','XSLFO2PDF');
?>