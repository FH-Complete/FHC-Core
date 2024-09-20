<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

global $g_result;

$error = [
	'message' => $message,
	'class' => get_class($exception),
	'filename' => $exception->getFile(),
	'line' => $exception->getLine()
];

if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true) {
	$error['backtrace'] = [];
	foreach (debug_backtrace() as $err) {
		if (isset($err['file']) && strpos($err['file'], realpath(BASEPATH)) !== 0) {
			$error['backtrace'][] = [
				'file' => $err['file'],
				'line' => $err['line'],
				'function' => $err['function']
			];
		}
	}
}

$g_result->addError($error, FHCAPI_Controller::ERROR_TYPE_EXCEPTION);
$g_result->setStatus(FHCAPI_Controller::STATUS_ERROR);
