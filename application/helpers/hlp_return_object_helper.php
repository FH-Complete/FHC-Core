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
// Collection of functions to handle success and error objects that methods and functions can return
// -------------------------------------------------------------------------------------------------------

/**
 * Used to create a return object, should not be used directly
 * @return stdClass
 */
function _createReturnObject($code, $error, $retval)
{
	$returnObject = new stdClass();
	$returnObject->code = $code;
	$returnObject->error = $error;
	$returnObject->retval = $retval;

	return $returnObject;
}

/**
 * Success
 *
 * @return stdClass
 */
function success($retval = null, $code = null)
{
	return _createReturnObject($code, EXIT_SUCCESS, $retval);
}

/**
 * Error
 *
 * @return stdClass
 */
function error($retval = null, $code = null)
{
	return _createReturnObject($code, EXIT_ERROR, $retval);
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
 * Checks if the result represents an error
 * Wrapper function of isSuccess, more readable code
 * Bob Dylan: ...there's no success like failure. And that failure's no success at all.
 */
function isError($result)
{
	return !isSuccess($result);
}

/**
 * Checks if the result represents a success and also if it contains valid data
 */
function hasData($result)
{
	if (isSuccess($result) && isset($result->retval)
		&& (!isEmptyArray($result->retval)
		|| !isEmptyString($result->retval)
		|| is_numeric($result->retval)
		|| is_object($result->retval)))
	{
		return true;
	}

	return false;
}

/**
 * Returns the property retval if $result contains data, otherwise null
 */
function getData($result)
{
	$data = null;

	if (hasData($result))
	{
		$data = $result->retval;
	}

	return $data;
}

/**
 * Returns the property code if present, otherwise null
 */
function getCode($result)
{
	$code = null;

	if (isset($result->code))
	{
		$code = $result->code;
	}

	return $code;
}

/**
 * Returns the property retval if present, otherwise null
 */
function getError($result)
{
	$error = null;

	if (isset($result->retval))
	{
		$error = $result->retval;
	}

	return $error;
}
