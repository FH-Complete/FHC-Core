<?php

$config['number_displayed_past_studiensemester_default'] = 5;

# Additional columns:
/*
$config["list_columns"] = [
	'fieldname' => [
		'js' => 'path/to/snippet.js', // tabulator config snippet (eg: return { name: 'Name', field: 'fieldname' }; )
		'default' => "SQL SELECT statement for value",
		'joins' => [
			// additional joins needed for the SELECT statement above
			[
				'tablename',
				'join condition', // single fieldname for USING or full condition for ON
				'LEFT|RIGHT', // optional - join type
				'after_xxx|before_xxx|end' // optional - position in query
			]
			// ...
		]
	]
];
*/

$config['student_tab_order'] = [
	'details',
	'notes',
	'messages',
	'contact',
	'prestudent',
	'status',
	'documents',
	'archive',
	'banking',
	'grades',
	'exam',
	'exemptions',
	'projektarbeit',
	'finalexam',
	'mobility',
	'jointstudies',
	'admissionDates',
	'groups',
	'functions',
	'coursedates',
	'resources',
];
$config['students_tab_order'] = [
	'banking',
	'status',
	'messages',
	'groups',
	'finalexam',
	'combinePeople',
	'archive',
];

$config['stv_prestudent_tags'] = [
	'prioone' => ['readonly' => false],
	'priotwo' => ['readonly' => false],
	'hinweis' => ['readonly' => false],
	'hinweis_assistenz' => ['readonly' => false],
	'hinweis_kf' => ['readonly' => false],
	'hinweis_lehrende' => ['readonly' => false],
	'hinweis_stg_kf' => ['readonly' => false],
	'finished_stg' => ['readonly' => false],
	'finished_kf' => ['readonly' => false],
	'inwork_kf' => ['readonly' => false],
	'dd_auto' => ['readonly' => true],
	'wh_auto' => ['readonly' => true],
	'prewh_auto' => ['readonly' => true],
	'out_auto' => ['readonly' => true],
	'zgv_auto' => ['readonly' => true],
	'unterbrecher_auto' => ['readonly' => true],
	'stbtr_erh_auto' => ['readonly' => true],
	'jgv_auto' => ['readonly' => true],
	'in_auto' => ['readonly' => true],
	'preabbr_auto' => ['readonly' => true],
];

$config['userAutomatedTags'] = 'sftest';
