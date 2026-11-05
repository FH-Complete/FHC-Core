<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The permissions that open the Benotungstool: the keys of CIS_GESAMTNOTE_ROLLENMATRIX. The page and
 * the Noten API read this one list. A constructor needs it before CodeIgniter can load a configuration,
 * so this reads the file itself.
 *
 * @return array one 'permission:rw' entry per key
 */
function benotungstoolPermissions()
{
	$config = array();
	include APPPATH . 'config/noten.php';

	$permissions = array();
	foreach (array_keys($config['CIS_GESAMTNOTE_ROLLENMATRIX']) as $permission) $permissions[] = $permission . ':rw';

	return $permissions;
}

/**
 * A define() flag of the old tool from global.config.inc.php. A config file without the flag reads it
 * as off, so an older config file does not stop the request.
 *
 * @return bool
 */
function benotungstoolFlag($name)
{
	return defined($name) && constant($name);
}
