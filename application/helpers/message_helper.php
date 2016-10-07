<?php

/**
 * Success
 *
 * @return  array
 */
function success($retval, $code = null, $msg_indx_prefix = 'fhc_')
{
	$success = new stdClass();
	$success->error = EXIT_SUCCESS;
	$success->fhcCode = $code;
	if (!is_null($code)) $success->msg = lang($msg_indx_prefix . $code);
	$success->retval = $retval;
	
	return $success;
}

/**
 * Error
 *
 * @return  array
 */
function error($retval = '', $code = null, $msg_indx_prefix = 'fhc_')
{
	$error = new stdClass();
	$error->error = EXIT_ERROR;
	$error->fhcCode = $code;
	if (!is_null($code)) $error->msg = lang($msg_indx_prefix . $code);
	$error->retval = $retval;
	
	return $error;
}