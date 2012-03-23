<?php
/* Copyright (C) 2012 FH Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Webdav Server fuer DMS-Zugriff
 */
require_once('../config/cis.config.inc.php');
require_once('../include/sabredav/lib/Sabre/autoload.php');
//require_once('../../../sabredav161/SabreDAV/lib/Sabre/autoload.php');
require_once('DMSdirectory.class.php');
require_once('DMSfile.class.php');
require_once('auth.class.php');

$authBackend = new MyAuth();
// Creating the plugin. We're assuming that the realm
// name is called 'SabreDAV'. 
$authPlugin = new Sabre_DAV_Auth_Plugin($authBackend,'FHTW');

// Change public to something else, if you are using a different directory for your files
$rootDirectory = new DMSDirectory('',$authPlugin);

// The server object is responsible for making sense out of the WebDAV protocol
$server = new Sabre_DAV_Server($rootDirectory);

// Adding the plugin to the server
$server->addPlugin($authPlugin);

// If your server is not on your webroot, make sure the following line has the correct information

// $server->setBaseUri('/~evert/mydavfolder'); // if its in some kind of home directory
// $server->setBaseUri('/dav/index.php/'); // if you can't use mod_rewrite, use index.php as a base uri
// $server->setBaseUri('/'); // ideally, SabreDAV lives on a root directory with mod_rewrite sending every request to index.php
$path = str_replace($_SERVER['DOCUMENT_ROOT'],'',__FILE__).'/';
$server->setBaseUri($path);

// The lock manager is reponsible for making sure users don't overwrite each others changes. Change 'data' to a different 
// directory, if you're storing your data somewhere else.
$lockBackend = new Sabre_DAV_Locks_Backend_File('data/locks');
$lockPlugin = new Sabre_DAV_Locks_Plugin($lockBackend);
$server->addPlugin($lockPlugin);

//GUI fuer Browser
$browser = new Sabre_DAV_Browser_Plugin();
$server->addPlugin($browser);

// All we need to do now, is to fire up the server
$server->exec();
?>
