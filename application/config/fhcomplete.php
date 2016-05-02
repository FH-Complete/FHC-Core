<?php  
	if (! defined('BASEPATH'))
		exit('No direct script access allowed');

$config['fhc_version'] = '3.2';

$config['fhc_acl'] = array
(
	'public.tbl_person' => 'basis/person',
	'public.tbl_prestudent' => 'basis/person',
	'public.tbl_prestudentstatus' => 'basis/person',
	'public.tbl_organisationseinheit' => 'basis/organisationseinheit',
	'public.tbl_sprache' => 'admin'
);
