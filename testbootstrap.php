<?php
// tests/Bootstrap.php
if (! defined('CI_BOOTSTRAPPED'))
{
	define('CI_BOOTSTRAPPED', true);

	// 1) point these at your project
	define('ENVIRONMENT', 'testing');
	$_SERVER['CI_ENV'] = 'testing';
	
	$system_path = __DIR__ . '/vendor/codeigniter/framework/system';
	$application_folder = __DIR__ . '/application';
	$view_folder = '';

	if (($_temp = realpath($system_path)) !== FALSE) {
		$system_path = $_temp . '/';
	} else {
		$system_path = rtrim($system_path, '/') . '/';
	}
	
	define('BASEPATH', str_replace("\\", "/", $system_path));
	define('APPPATH', str_replace("\\", "/", realpath($application_folder)) . '/');
	if (!empty($view_folder) && is_dir($view_folder)) {
		$view_folder = realpath($view_folder) . '/';
	} elseif (is_dir(APPPATH . 'views/')) {
		$view_folder = APPPATH . 'views/';
	} else {
		exit('Your view folder path is invalid');
	}
	define('VIEWPATH', $view_folder);
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
	define('FCPATH', __DIR__ . '/');

	// 2) show all errors while testing
	error_reporting(-1);
	ini_set('display_errors', 1);

	// 3) bring in the core (but we won’t hit any controller until we call it)
	require_once __DIR__ . '/vendor/autoload.php';
	require_once BASEPATH . 'core/CodeIgniter.php';
}
