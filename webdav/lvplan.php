<?php

// Files we need
require_once '../include/sabredav/lib/Sabre/autoload.php';
require_once 'auth.class.php';
require_once 'Caldav_Backend.php';
require_once('Principal.php');
/*
//PHP Error To Exception
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");
*/
// Backends
$authBackend = new myAuth();
$principalBackend = new  MySabre_DAVACL_PrincipalBackend($authBackend);
$calendarBackend = new MySabre_CalDAV_Backend($authBackend);

$tree = array(
	new \Sabre\CalDAV\Principal\Collection($principalBackend),
	new \Sabre\CalDAV\CalendarRootNode($principalBackend, $calendarBackend)
);

// The object tree needs in turn to be passed to the server class
$server = new \Sabre\DAV\Server($tree);

// You are highly encouraged to set your WebDAV server base url. Without it,
// SabreDAV will guess, but the guess is not always correct. Putting the
// server on the root of the domain will improve compatibility. 
$path = str_replace($_SERVER['DOCUMENT_ROOT'],'',__FILE__).'/';
$server->setBaseUri($path);

// Authentication plugin
$authPlugin = new \Sabre\DAV\Auth\Plugin($authBackend,'SabreDAV');
$server->addPlugin($authPlugin);

// CalDAV plugin
$caldavPlugin = new \Sabre\CalDAV\Plugin();
$server->addPlugin($caldavPlugin);

// ACL plugin
$aclPlugin = new \Sabre\DAVACL\Plugin();
$server->addPlugin($aclPlugin);

// Support for html frontend
$browser = new \Sabre\DAV\Browser\Plugin();
$server->addPlugin($browser);

// And off we go!
$server->exec();

