<?php
	ini_set('display_errors','1');
	error_reporting(E_ALL);

	mb_internal_encoding('UTF-8');
	mb_regex_encoding('UTF-8');
	setlocale (LC_ALL, 'de_DE.UTF8','de_DE@euro', 'de_DE', 'de','DE', 'ge','German');
	date_default_timezone_set('Europe/Vienna');

	// Connection Strings zur Datenbank
	define('DB_SYSTEM','pgsql');
	define('DB_HOST','localhost');
	define('DB_PORT','5432');
	define('DB_NAME','fhcomplete');
	define('DB_USER','fhcomplete');
	define('DB_PASSWORD','fhcomplete');
	define('DB_CONNECT_PERSISTENT',FALSE);
	define('CONN_CLIENT_ENCODING','UTF-8' );

	// Dokumentenmanagement
	define('DMS_PATH','/var/fhcomplete/documents/dms/');

	// Pfad zu Document Root
	define('DOC_ROOT','/var/www/html/build/');

	// Fuer Mails etc
	define('DOMAIN','technikum-wien.at');

	// Default Sprache
	define('DEFAULT_LANGUAGE','German' );

	// Authentifizierungsmethode
	// Moegliche Werte:
	// auth_mixed    - htaccess mit LDAP (Default)
	// auth_demo     - Demo Modus (.htaccess)
	// auth_session  - Sessions mit LDAP (Testbetrieb)
	define('AUTH_SYSTEM', 'auth_demo');
	// Gibt den Namen fuer die htaccess Authentifizierung an (muss mit dem Attribut AuthName im htaccess uebereinstimmen)
	define('AUTH_NAME','FH-Complete');

	// DatenbankRollen fuer Grants
	define('DB_CIS_USER_GROUP','web');
	define('DB_FAS_USER_GROUP','admin');

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

?>
