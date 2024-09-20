<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

global $g_result;

// NOTE(chris): remove p tags from CI_Exceptions::show_error() function
$msg = substr($message, 3);
$msg = substr($msg, 0, -4);
$msg = explode('</p><p>', $msg);

$error = [
	'heading' => $heading
];
if (count($msg) == 1)
	$error['message'] = current($msg);
else
	$error['messages'] = $msg;

$g_result->addError($error, FHCAPI_Controller::ERROR_TYPE_GENERAL);
$g_result->setStatus(FHCAPI_Controller::STATUS_ERROR);
