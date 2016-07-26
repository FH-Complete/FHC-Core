<?php

/** ---------------------------------------------------------------
 * General Error
 *
 * @return  array
 */
function success($retval, $message = null)
{
	$return = new stdClass();
	$return->error = EXIT_SUCCESS;
	$return->fhcCode = $message;
	if (!is_null($message)) $return->msg = lang('fhc_' . $message);
	$return->retval = $retval;
	return $return;
}

/** ---------------------------------------------------------------
 * General Error
 *
 * @return  array
 */
function error($retval = '', $message = null)
{
	$return = new stdClass();
	$return->error = EXIT_ERROR;
	$return->fhcCode = $message;
	if (!is_null($message)) $return->msg = lang('fhc_' . $message);
	$return->retval = $retval;
	return $return;
}