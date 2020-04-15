<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Enable/Disable DBSkel procedure
|--------------------------------------------------------------------------
|
| DBSkel is disabled by default for security reasons.
| You should enable DBSkel whenever you intend to use DBSkel
|
*/
$config['dbskel_enabled'] = false;

/*
|--------------------------------------------------------------------------
| DBSkel mode
|--------------------------------------------------------------------------
|
| This is used to set the dbskel mode:
|	- dryrun: run without changing the database, useful for testing
|	- new: build a new database or if database is already present creates only new objects
|	- diff: like new, but it also remove object from database that are NOT present in configuration files
|
*/
$config['dbskel_mode'] = 'dryrun';

