<?php

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
		]
	];