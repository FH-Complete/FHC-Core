<?php

$config['showZahlungsbestaetigung'] = (defined('ZAHLUNGSBESTAETIGUNG_ANZEIGEN') && ZAHLUNGSBESTAETIGUNG_ANZEIGEN);

$config['showBuchungsnrPermissions'] = ['admin'];

$config['showMahnspanne'] = (!defined('FAS_KONTO_SHOW_MAHNSPANNE') || FAS_KONTO_SHOW_MAHNSPANNE === true);

$config['showCreditpoints'] = (defined('FAS_KONTO_SHOW_CREDIT_POINTS') && FAS_KONTO_SHOW_CREDIT_POINTS == 'true');

$config['columns'] = [
	'buchungsdatum' => [
		'field' => "buchungsdatum",
		'title' => 'buchungsdatum',
		'titlePhrase' => 'konto/buchungsdatum'
	],
	'buchungstext' => [
		'field' => "buchungstext",
		'title' => 'buchungstext',
		'titlePhrase' => 'konto/buchungstext'
	],
	'betrag' => [
		'field' => "betrag",
		'title' => 'betrag',
		'titlePhrase' => 'konto/betrag'
	],
	'studiensemester_kurzbz' => [
		'field' => "studiensemester_kurzbz",
		'title' => 'studiensemester',
		'titlePhrase' => 'lehre/studiensemester'
	],
	'buchungstyp_kurzbz' => [
		'field' => "buchungstyp_kurzbz",
		'title' => 'buchungstyp',
		'titlePhrase' => 'konto/buchungstyp',
		'visible' => false
	],
	'buchungsnr' => [
		'field' => "buchungsnr",
		'title' => 'buchungsnr',
		'titlePhrase' => 'konto/buchungsnr',
		'visible' => false
	],
	'insertvon' => [
		'field' => "insertvon",
		'title' => 'insertvon',
		'titlePhrase' => 'global/insertvon',
		'visible' => false
	],
	'insertamum' => [
		'field' => "insertamum",
		'title' => 'insertamum',
		'titlePhrase' => 'global/insertamum',
		'visible' => false
	],
	'kuerzel' => [
		'field' => "kuerzel",
		'title' => 'studiengang',
		'titlePhrase' => 'lehre/studiengang',
		'visible' => false
	],
	'anmerkung' => [
		'field' => "anmerkung",
		'title' => 'anmerkung',
		'titlePhrase' => 'global/anmerkung'
	],
	'actions' => [
		'title' => 'actions',
		'titlePhrase' => 'global/actions',
		'frozen' => true
	]
];

$config['columnsMultiPerson'] = [
	'person_id' => [
		'field' => "person_id",
		'title' => 'person_id',
		'titlePhrase' => 'person/person_id'
	],
	'anrede' => [
		'field' => "anrede",
		'title' => 'anrede',
		'titlePhrase' => 'person/anrede',
		'visible' => false
	],
	'titelpost' => [
		'field' => "titelpost",
		'title' => 'titelpost',
		'titlePhrase' => 'person/titelpost',
		'visible' => false
	],
	'titelpre' => [
		'field' => "titelpre",
		'title' => 'titelpre',
		'titlePhrase' => 'person/titelpre',
		'visible' => false
	],
	'vorname' => [
		'field' => "vorname",
		'title' => 'vorname',
		'titlePhrase' => 'person/vorname'
	],
	'vornamen' => [
		'field' => "vornamen",
		'title' => 'vornamen',
		'titlePhrase' => 'person/vornamen',
		'visible' => false
	],
	'nachname' => [
		'field' => "nachname",
		'title' => 'nachname',
		'titlePhrase' => 'person/nachname'
	]
] + $config['columns'];

$config['additionalCols'] = [];
