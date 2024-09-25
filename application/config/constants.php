<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Exit status codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
|
*/
define('EXIT_SUCCESS',						0); // no errors
define('EXIT_ERROR',						1); // generic error
define('EXIT_MODEL',						2); // model error
define('EXIT_CONFIG',						3); // configuration error
define('EXIT_UNKNOWN_FILE',					4); // file not found
define('EXIT_UNKNOWN_CLASS',				5); // unknown class
define('EXIT_UNKNOWN_METHOD',				6); // unknown class method
define('EXIT_USER_INPUT',					7); // invalid user input
define('EXIT_DATABASE',						8); // database error
define('EXIT_VALIDATION_UDF',				10); // UDF validation has been failed
define('EXIT_VALIDATION_UDF_MIN_VALUE',		11); // UDF validation has been failed -> MIN VALUE
define('EXIT_VALIDATION_UDF_MAX_VALUE',		12); // UDF validation has been failed -> MAX VALUE
define('EXIT_VALIDATION_UDF_MIN_LENGTH',	13); // UDF validation has been failed -> MIN LENGTH
define('EXIT_VALIDATION_UDF_MAX_LENGTH',	14); // UDF validation has been failed -> MAX LENGTH
define('EXIT_VALIDATION_UDF_REGEX',			15); // UDF validation has been failed -> REGEX
define('EXIT_VALIDATION_UDF_REQUIRED',		16); // UDF validation has been failed -> REQUIRED
define('EXIT_VALIDATION_UDF_NOT_VALID_VAL',	17); // UDF validation has been failed -> Not valid value, object or array

define('EXIT_AUTO_MIN',						1000); // lowest automatically-assigned error code
define('EXIT_AUTO_MAX',						2000); // highest automatically-assigned error code

/*
|--------------------------------------------------------------------------
| General purpose
|--------------------------------------------------------------------------
*/
define('BEGINNING_OF_TIME',					'1970-01-01');

/*
|--------------------------------------------------------------------------
| Authentication constants
|--------------------------------------------------------------------------
*/
// Foreign authentication methods
define('AUTH_HBALDAP',	'httpBasicAuthLDAP');
define('AUTH_BT',		'bewerbung');

// Login methods
define('AUTH_LDAP',	'ldap');
define('AUTH_DB',	'database');
define('AUTH_SSO',	'sso');

// Authentication return codes
define('AUTH_SUCCESS',				0);
define('AUTH_NOT_AUTHENTICATED',	1);
define('AUTH_INVALID_CREDENTIALS',	2);

/*
|--------------------------------------------------------------------------
| Language constants
|--------------------------------------------------------------------------
*/
define('LANG_SESSION_NAME', 'LANGUAGE');
define('LANG_SESSION_INDEXES', 'INDEXES');
define('LANG_SESSION_ACTIVE_LANGUAGES', 'ACTIVE_LANGUAGES');
define('LANG_SESSION_CURRENT_LANGUAGE', 'sprache'); // NOTE: it is not under LANG_SESSION_NAME

/*
|--------------------------------------------------------------------------
| File and directory modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File stream modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
define('FOPEN_READ', 'rb');
define('FOPEN_READ_WRITE', 'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 'ab');
define('FOPEN_READ_WRITE_CREATE', 'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Display debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| Email constants
|--------------------------------------------------------------------------
*/
define('EMAIL_CONFIG_INDEX', 'mail');

/*
|--------------------------------------------------------------------------
| Messaging system constants
|--------------------------------------------------------------------------
*/
// Message statuses
define('MSG_STATUS_UNREAD',		0);
define('MSG_STATUS_READ',		1);
define('MSG_STATUS_ARCHIVED',	2);
define('MSG_STATUS_DELETED',	3);

// Message priorities
define('MSG_PRIORITY_LOW',		1);
define('MSG_PRIORITY_NORMAL',	2);
define('MSG_PRIORITY_HIGH',		3);
define('MSG_PRIORITY_URGENT',	4);

// Message error status
define('MSG_ERR_INVALID_SUBJECT',		40);
define('MSG_ERR_INVALID_BODY',			41);
define('MSG_ERR_INVALID_TEMPLATE',		42);
define('MSG_ERR_INVALID_MSG_ID',		43);
define('MSG_ERR_INVALID_STATUS_ID',		44);
define('MSG_ERR_INVALID_SENDER',		45);
define('MSG_ERR_INVALID_RECIPIENTS',	46);
define('MSG_ERR_INVALID_OU',			47);
define('MSG_ERR_INVALID_TOKEN',			48);
