<?php
// Set environment
define('ENVIRONMENT', 'testing');
$_SERVER['CI_ENV'] = 'testing';

// Setup CI core constants
$system_path = __DIR__ . '/vendor/codeigniter/framework/system';
$application_folder = __DIR__ . '/application';
$view_folder = $application_folder . '/views';

if (!defined('ICONV_ENABLED')) define('ICONV_ENABLED', function_exists('iconv'));
if (!defined('MB_ENABLED')) define('MB_ENABLED', extension_loaded('mbstring'));

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('BASEPATH', rtrim(realpath($system_path), '/') . '/');
define('APPPATH', rtrim(realpath($application_folder), '/') . '/');
define('VIEWPATH', rtrim(realpath($view_folder), '/') . '/');

// Autoload Composer and project config
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/global.config.inc.php';
require_once __DIR__ . '/config/vilesci.config.inc.php';
require_once __DIR__ . '/application/config/core_includes.php';

// Load base CI functionality manually, without routing
require_once BASEPATH . 'core/Common.php';

$CFG =& load_class('Config', 'core');
$UNI =& load_class('Utf8', 'core');
$URI =& load_class('URI', 'core');
$RTR =& load_class('Router', 'core'); // Optional: Don't route
$OUT =& load_class('Output', 'core');

require_once BASEPATH . 'core/Controller.php';

//require_once BASEPATH . 'core/CodeIgniter.php';
function &get_instance() { return CI_Controller::get_instance(); }

// Manually load your custom controller later in tests
