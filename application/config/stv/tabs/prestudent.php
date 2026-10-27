<?php

//all fields can be configured to be hidden, see class attribute stv-details-prestudent-name for name
$config['hiddenFields'] = [
	//propably used by FH-Communities
	'aufnahmeschluessel', 'standort_code', 'facheinschlaegigBerufstaetig'
];

$config['hideUDFs'] = false;

$config['showZgvErfuellt'] = (defined('ZGV_ERFUELLT_ANZEIGEN') && ZGV_ERFUELLT_ANZEIGEN);

$config['showZgvDoktor'] = (defined('ZGV_DOKTOR_ANZEIGEN') && ZGV_DOKTOR_ANZEIGEN);
