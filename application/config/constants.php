<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| FH-Complete constants
|--------------------------------------------------------------------------
|
| These constants are used for internal messages. It are also be used
| and translated in the language files.
|
*/
define('FHC_SUCCESS', 0); 		// General Success Message
define('FHC_ERROR', 1); 		// General Error Message
define('FHC_MODEL_ERROR', 2); 	// Model Error
define('FHC_DB_ERROR', 3);		// Database Error
define('FHC_NODBTABLE', 4); 	// No DB-Table is set
define('FHC_NORIGHT', 5); 	    // No rights
define('FHC_INVALIDID', 6); 	// Invalid or no ID (key)
define('FHC_NOPK', 7); 			// No primary key

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
| Exit status codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
*/
define('EXIT_SUCCESS', 0); // no errors
define('EXIT_ERROR', 1); // generic error
define('EXIT_MODEL', 2); // model error
define('EXIT_CONFIG', 3); // configuration error
define('EXIT_UNKNOWN_FILE', 4); // file not found
define('EXIT_UNKNOWN_CLASS', 5); // unknown class
define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
define('EXIT_USER_INPUT', 7); // invalid user input
define('EXIT_DATABASE', 8); // database error
define('EXIT_VALIDATION_UDF',				10); // UDF validation has been failed
define('EXIT_VALIDATION_UDF_MIN_VALUE',		11); // UDF validation has been failed -> MIN VALUE
define('EXIT_VALIDATION_UDF_MAX_VALUE',		12); // UDF validation has been failed -> MAX VALUE
define('EXIT_VALIDATION_UDF_MIN_LENGTH',	13); // UDF validation has been failed -> MIN LENGTH
define('EXIT_VALIDATION_UDF_MAX_LENGTH',	14); // UDF validation has been failed -> MAX LENGTH
define('EXIT_VALIDATION_UDF_REGEX',			15); // UDF validation has been failed -> REGEX
define('EXIT_VALIDATION_UDF_REQUIRED',		16); // UDF validation has been failed -> REQUIRED
define('EXIT_VALIDATION_UDF_NOT_VALID_VAL',	17); // UDF validation has been failed -> Not valid value, object or array

define('EXIT_AUTO_MIN', 1000); // lowest automatically-assigned error code
define('EXIT_AUTO_MAX', 2000); // highest automatically-assigned error code

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
// Email kontakt type
define('EMAIL_KONTAKT_TYPE', 'email');
// tbl_msg_recipient->sentInfo separator
define('SENT_INFO_NEWLINE', '\n');

// Message statuses
define('MSG_STATUS_UNREAD',		0);
define('MSG_STATUS_READ',		1);
define('MSG_STATUS_ARCHIVED',	2);
define('MSG_STATUS_DELETED',	3);

// Priority
define('PRIORITY_LOW',		1);
define('PRIORITY_NORMAL',	2);
define('PRIORITY_HIGH',		3);
define('PRIORITY_URGENT',	4);

// Status return message codes
define('MSG_SUCCESS',	0);
define('MSG_ERROR',		1);

define('MSG_MESSAGE_SENT',		10);
define('MSG_STATUS_UPDATE',		11);

define('MSG_PARTICIPANT_ADDED',			30);
define('MSG_ERR_PARTICIPANT_EXISTS',	31);
define('MSG_ERR_PARTICIPANT_NONSYSTEM',	32);
define('MSG_PARTICIPANT_REMOVED',		33);

define('MSG_ERR_SUBJECT_EMPTY',			40);
define('MSG_ERR_BODY_EMPTY',			41);
define('MSG_ERR_TEMPLATE_NOT_FOUND',	42);
define('MSG_ERR_DELIVERY_MESSAGE',		43);
define('MSG_ERR_CONTACT_NOT_FOUND',		44);
define('MSG_ERR_OU_CONTACTS_NOT_FOUND',	45);

define('MSG_ERR_INVALID_USER_ID',		100);
define('MSG_ERR_INVALID_MSG_ID',		101);
define('MSG_ERR_INVALID_THREAD_ID',		102);
define('MSG_ERR_INVALID_STATUS_ID',		103);
define('MSG_ERR_INVALID_SENDER_ID',		104);
define('MSG_ERR_INVALID_RECIPIENTS',	105);
define('MSG_ERR_INVALID_RECEIVER_ID',	106);
define('MSG_ERR_INVALID_OU',			107);
define('MSG_ERR_INVALID_TEMPLATE',		108);
define('MSG_ERR_INVALID_TOKEN',			109);
