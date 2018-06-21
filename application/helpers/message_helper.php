<?php

/**
 * FH-Complete
 *
 * @package		FHC-Helper
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016 fhcomplete.org
 * @license		GPLv3
 * @since		Version 1.0.0
 */

/**
 * Message Helper
 *
 * @subpackage	Helpers
 * @category	Helpers
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

// -------------------------------------------------------------------------------------------------------
// Collection of functions to handle successful and error messages that methods and functions can return
// -------------------------------------------------------------------------------------------------------

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

/**
 * Checks if the result represents a success
 */
function isSuccess($result)
{
	if (is_object($result) && isset($result->error) && $result->error == EXIT_SUCCESS)
	{
		return true;
	}

	return false;
}

/**
 * Checks if the result represents a success and also if it contains data from DB
 */
function hasData($result)
{
	if (isSuccess($result) && isset($result->retval) &&
		is_array($result->retval) && count($result->retval) > 0)
	{
		return true;
	}

	return false;
}

/**
 * Checks if the result represents an error
 * Wrapper function of isSuccess, more readable code
 * Bob Dylan: ...there's no success like failure. And that failure's no success at all.
 */
function isError($result)
{
	return !isSuccess($result);
}
