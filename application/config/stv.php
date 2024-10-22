<?php

$config['tabs'] =
	[
	'prestudent' => [

		//all fields can be configured to be hidden, see class attribute stv-prestudent-prestudent-name for name
		'hiddenFields' => [

			//corresponding to config-entry 'ZGV_DOKTOR_ANZEIGEN' in global.config
			'zgvdoktor_code', 'zgvdoktorort', 'zgvdoktordatum', 'zgvdoktornation', 'zgvdoktor_erfuellt',

			//corresponding to config-entry 'ZGV_ERFUELLT_ANZEIGEN' in global.config
			'zgv_erfuellt', 'zgvmas_erfuellt','zgvdoktor_erfuellt',

			//propably used by FH-Communities
			'aufnahmeschluessel', 'standort_code', 'facheinschlaegigBerufstaetig'

		],
		'hideUDFs' => false
	]
];