<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Autoload custom controllers, models, etc that are present in the application/core directory
 */
spl_autoload_register(function ($class) {
	if (substr($class, 0, 3) !== 'CI_' && substr($class, 0, 4) !== 'FHC_')
		if (file_exists($file = APPPATH.'core/'.$class.'.php'))
			require_once $file;
});
