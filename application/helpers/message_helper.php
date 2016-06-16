<?php

/** ---------------------------------------------------------------
 * General Error
 *
 * @return  array
 */
function success($retval, $message = FHC_SUCCESS)
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
function error($retval = '', $message = FHC_MODEL_ERROR)
{
	$return = new stdClass();
	$return->error = EXIT_MODEL;
	$return->fhcCode = $message;
	$return->msg = lang('fhc_' . $message);
	$return->retval = $retval;
	return $return;
}