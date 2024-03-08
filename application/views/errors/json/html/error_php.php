<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

global $g_result;

$error = [
	'message' => $message,
	'severity' => $severity,
	'filename' => $filepath,
	'line' => $line
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

// TODO(chris): change type with severity
$g_result->addError($error, 'php');

if (((E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity) {
	$g_result->setStatus('error');
}
