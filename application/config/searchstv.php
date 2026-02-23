<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


$CI =& get_instance();


$config['student'] = $CI->config->item('student', 'search');
$config['student']['searchfields']['pkz'] = [
	'alias' => ['personenkennzeichen', 'personalid'],
	'comparison' => 'equals',
	'field' => 'matrikelnr'
];
$config['student']['searchfields']['matrnr'] = [
	'alias' => ['matrikelnr', 'matrikelnummer', 'matrno', 'matriculationno', 'matriculationnumber', 'studno', 'studentno', 'studentnumber'],
	'comparison' => 'equals',
	'field' => 'matr_nr',
	'join' => [
		[
			'table' => "public.tbl_prestudent",
			'using' => "prestudent_id"
		],
		[
			'table' => "public.tbl_person",
			'using' => "person_id"
		]
	]
];

$config['prestudent'] = $CI->config->item('prestudent', 'search');
$config['prestudent']['searchfields']['pkz'] = [
	'alias' => ['personenkennzeichen', 'personalid'],
	'comparison' => 'equals',
	'field' => 'matrikelnr',
	'join' => [
		'table' => "public.tbl_student",
		'using' => "prestudent_id"
	]
];
$config['prestudent']['searchfields']['matrnr'] = [
	'alias' => ['matrikelnr', 'matrikelnummer', 'matrno', 'matriculationno', 'matriculationnumber', 'studno', 'studentno', 'studentnumber'],
	'comparison' => 'equals',
	'field' => 'matr_nr',
	'join' => [
		'table' => "public.tbl_person",
		'using' => "person_id"
	]
];
