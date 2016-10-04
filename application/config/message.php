<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

$config['msg_delivery'] = true;
$config['send_immediately'] = false; // If the message should be sent immediately

define('EMAIL_KONTAKT_TYPE', 'email'); // Email kontakt type
define('SENT_INFO_NEWLINE', '\n'); // tbl_msg_recipient->sentInfo separator

/*
|--------------------------------------------------------------------------
| Constants for Messaging System
|--------------------------------------------------------------------------
|
| Statuses
| Priority
| Return Codes
|
*/
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

define('MSG_ERR_INVALID_USER_ID',		100);
define('MSG_ERR_INVALID_MSG_ID',		101);
define('MSG_ERR_INVALID_THREAD_ID',		102);
define('MSG_ERR_INVALID_STATUS_ID',		103);
define('MSG_ERR_INVALID_SENDER_ID',		104);
define('MSG_ERR_INVALID_RECIPIENTS',	105);
define('MSG_ERR_INVALID_RECEIVER_ID',	106);
define('MSG_ERR_INVALID_OU',			107);
define('MSG_ERR_INVALID_TEMPLATE',		108);