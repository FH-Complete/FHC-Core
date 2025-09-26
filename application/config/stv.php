<?php

$config['number_displayed_past_studiensemester_default'] = 5;
$config['tabs'] =
	[
		'details' => [
			//all fields can be configured to be hidden, see class attribute stv-details-details-name for name
			'hiddenFields' => [],
			'hideUDFs' => false
		],

		'prestudent' => [

			//all fields can be configured to be hidden, see class attribute stv-details-prestudent-name for name
			'hiddenFields' => [

				//propably used by FH-Communities
				'aufnahmeschluessel', 'standort_code', 'facheinschlaegigBerufstaetig'

			],
			'hideUDFs' => false
		],
		'finalexam' => [
			'documents' => [
				'pruefungsprotokoll' => [
					'de' => [
						'Bakk' => 'PrProtBA',
						'Master' => 'PrProtMA',
					],
					'en' => [
						'Bakk' => 'PrProtBAEng',
						'Master' => 'PrProtMAEng',
					],
				],
				'pruefungszeugnis' => [
					'de' => [
						'Bakk' => 'Bakkzeugnis',
						'Master' => 'Diplomzeugnis',
					],
					'en' => [
						'Bakk' => 'BakkzeugnisEng',
						'Master' => 'DiplomzeugnisEng',
					],
				],
				'urkunde' => [
					'de' => [
						'Bakk' => 'Bakkurkunde',
						'Master' => 'Diplomurkunde',
					],
					'en' => [
						'Bakk' => 'BakkurkundeEng',
						'Master' => 'DiplomurkundeEng',
					],
				],
			],
		],
		'exemptions' => [
			//if true, Anrechnungen can be added and edited in tab Anrechnungen
			'editableAnrechnungen' => false,
		],
		'notes' => [
			//if true, the count of Messages will be shown in the header of the Tab Messages
			'showCountNotes' => true
		]
	];
	
// List of fields to show when ZGV_DOKTOR_ANZEIGEN is defined
$fieldsZgvDoktor = ['zgvdoktorort', 'zgvdoktordatum', 'zgvdoktornation', 'zgvdoktor_erfuellt', 'zgvdoktor_code'];

// List of fields to show when ZGV_ERFUELLT_ANZEIGEN is defined
$fieldsZgvErfuellt = ['zgv_erfuellt', 'zgvmas_erfuellt','zgvdoktor_erfuellt'];

//order important: to show zgf_erfuellt_doktor just in case visibility of doktor is true
if (!defined('ZGV_ERFUELLT_ANZEIGEN') || !ZGV_ERFUELLT_ANZEIGEN) {
	$config['tabs']['prestudent']['hiddenFields'] = array_merge(
		$config['tabs']['prestudent']['hiddenFields'], $fieldsZgvErfuellt
	);
}

if (!defined('ZGV_DOKTOR_ANZEIGEN') || !ZGV_DOKTOR_ANZEIGEN) {
	$config['tabs']['prestudent']['hiddenFields'] = array_merge(
		$config['tabs']['prestudent']['hiddenFields'],
		$fieldsZgvDoktor
	);
}

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
	'groups',
	'finalexam',
	'archive',
];
