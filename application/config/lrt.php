<?php

/**
 * Configs for the Long Run Tasks
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

// Maximum LRTs for a single user in parallel
$config['lrt_max_number_single_user'] = 10;

// Maximum LRTs for the whole system in parallel
$config['lrt_max_number_system'] = 100;

// Maximum running time in hours for a single LRT before killing it
$config['lrt_max_run_timeout'] = 48;

// List of existing LRT types
$config['lrt_types'] = array('LRTDocumentConvertion');

