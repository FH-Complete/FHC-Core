<?php

/** ---------------------------------------------------------------
 * General Error
 *
 * @return  array
 */
function success($retval, $message = EXIT_SUCCESS)
{
	$return = new stdClass();
	$return->error = EXIT_SUCCESS;
	$return->fhcCode = $message;
	$return->msg = lang('fhc_' . $message);
	$return->retval = $retval;
	return $return;
}

/** ---------------------------------------------------------------
 * General Error
 *
 * @return  array
 */
function error($retval = '', $message = EXIT_ERROR)
{
	$return = new stdClass();
	$return->error = EXIT_ERROR;
	$return->fhcCode = $message;
	$return->msg = lang('fhc_' . $message);
	$return->retval = $retval;
	return $return;
}