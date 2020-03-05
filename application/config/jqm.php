<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// White list of permissions that are able to store a spcific job type in database
$config['job_type_permissions_white_list'] = array(
	'SAPStammdatenUpdate' => array(
		'admin'
	),
	'OEHPayment' => array(
		'admin'
	),
	'SAPPayment' => array(
		'admin'
	)
);
