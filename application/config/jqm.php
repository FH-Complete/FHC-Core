<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// White list of permissions (write mode have to be set) that are able to store a specific job type in database
$config['job_type_permissions_white_list'] = array(
	'SAPStammdatenUpdate' => array(
		'admin:rw',
		'developer:rw'
	),
	'OEHPayment' => 'developer:rw',
	'SAPPayment' => 'developer:rw'
);

// List of schedulers that can be run by the scheduler
$config['job_schedulers_list'] = array(				// Controllers
	'extensions/FHC-Core-SAP/JQMScheduler' => array(	// Methods
		'newUsers',
		'updateUsers'
	)
);

