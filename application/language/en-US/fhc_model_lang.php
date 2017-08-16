<?php
if ( ! defined('BASEPATH'))
	exit('No direct script access allowed');

// DB-Errormessages
$lang['fhc_'.FHC_MODEL_ERROR]	= 'Error in Model';
$lang['fhc_'.FHC_NODBTABLE]	= 'dbTable is not set!';
$lang['fhc_'.FHC_NORIGHT]	= 'rights are missing!';
$lang['fhc_'.EXIT_VALIDATION_UDF]				= 'UDF validation has been failed';
$lang['fhc_'.EXIT_VALIDATION_UDF_MIN_VALUE]		= 'UDF validation has been failed - MIN VALUE';
$lang['fhc_'.EXIT_VALIDATION_UDF_MAX_VALUE]		= 'UDF validation has been failed - MAX VALUE';
$lang['fhc_'.EXIT_VALIDATION_UDF_MIN_LENGTH]	= 'UDF validation has been failed - MIN LENGTH';
$lang['fhc_'.EXIT_VALIDATION_UDF_MAX_LENGTH]	= 'UDF validation has been failed - MAX LENGTH';
$lang['fhc_'.EXIT_VALIDATION_UDF_REGEX]			= 'UDF validation has been failed - REGEX';
$lang['fhc_'.EXIT_VALIDATION_UDF_REQUIRED]		= 'UDF validation has been failed - REQUIRED';
$lang['fhc_'.EXIT_VALIDATION_UDF_NOT_VALID_VAL]	= 'UDF validation has been failed - Not valid value, object or array given';