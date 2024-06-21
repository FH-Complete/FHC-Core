<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

global $g_result;

// NOTE(chris): remove p tags from CI_Exceptions::show_error() function
$msg = substr($message, 3);
$msg = substr($msg, 0, -4);
$msg = explode('</p><p>', $msg);

$msgs = [];

$error = [
	'heading' => $heading
];

/** NOTE(chris): extract Error Number and SQL
 * @see: DB_driver.php:692
 */
if (substr(current($msg), 0, 14) == 'Error Number: ') {
	$code = substr(array_shift($msg), 14);
	if ($code)
		$error['code'] = (int)$code;
	$msgs[] = array_shift($msg);
	$error['sql'] = array_shift($msg);
}

/** NOTE(chris): extract Line Number and Filename
 * @see: DB_driver.php:1782
 * @see: DB_driver.php:1783
 */
if (count($msg) >= 2) {
	if (substr(end($msg), 0, 13) == 'Line Number: ' && substr(prev($msg), 0, 10) == 'Filename: ') {
			$error['line'] = (int)substr(array_pop($msg), 13);
			$error['filename'] = substr(array_pop($msg), 10);
	}
}

foreach ($msg as $m)
	$msgs[] = $m;


if (count($msgs) == 1)
	$error['message'] = current($msgs);
else
	$error['messages'] = $msgs;

$g_result->addError($error, FHCAPI_Controller::ERROR_TYPE_DB);
$g_result->setStatus(FHCAPI_Controller::STATUS_ERROR);
