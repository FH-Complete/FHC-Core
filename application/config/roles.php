<?php
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Standard ROLES in FH-Complete and their persmissions
| -------------------------------------------------------------------------
|
*/
$config['roles'] = array
(
	array
	(
		'rolle_kurzbz' => 'admin',
		'berechtigung' => array
		(
			'admin', 'assistenz', 'basis/addon', 'basis/ampel', 'basis/ampeluebersicht',
			'basis/benutzer', 'basis/berechtigung', 'basis/betriebsmittel', 'basis/cms',
			'basis/cms_review', 'basis/cms_sperrfreigabe', 'basis/cronjob', 'basis/dms',
			'basis/fas', 'basis/ferien', 'basis/fhausweis','basis/firma',
			'basis/infoscreen',	'basis/moodle', 'basis/moodle','basis/news', 'basis/notiz',
			'basis/organisationseinheit', 'basis/ort', 'basis/person', 'basis/planner',
			'basis/service', 'basis/statistik', 'basis/studiengang', 'basis/studiensemester', 'basis/tempus',
			'basis/testtool', 'basis/variable', 'basis/vilesci', 'buchung/typen',
			'buchung/mitarbeiter', 'inout/incoming', 'inout/outgoing', 'inout/uebersicht',
			'lehre', 'lehre/abgabetool', 'lehre/freifach', 'lehre/lehrfach',
			'lehre/lehrveranstaltung', 'lehre/lvplan', 'lehre/lvinfo',
			'lehre/pruefungsanmeldungAdmin', 'lehre/pruefungsbeurteilung',
			'lehre/pruefungsbeurteilungAdmin', 'lehre/pruefungsterminAdmin',
			'lehre/pruefungsfenster', 'lehre/reihungstest', 'lehre/reservierung',
			'lehre/studienordnung', 'lehre/studienordnungInaktiv', 'lehre/studienplan',
			'lehre/vorrueckung', 'lv-plan', 'lv-plan/gruppenentfernen',
			'lv-plan/lektorentfernen', 'mitarbeiter', 'mitarbeiter/bankdaten',
			'mitarbeiter/personalnummer', 'mitarbeiter/stammdaten', 'mitarbeiter/urlaube',
			'mitarbeiter/zeitsperre', 'news', 'planner', 'preinteressent', 'raumres',
			'reihungstest', 'sdTools', 'soap/lv', 'soap/lvplan', 'soap/mitarbeiter',
			'soap/ort', 'soap/pruefungsfenster', 'soap/student', 'soap/studienordnung',
			'soap/benutzer', 'soap/buchungen', 'student/bankdaten', 'student/anrechnung',
			'student/anwesenheit', 'student/dokumente',	'student/noten', 'system/phrase',
			'system/vorlage', 'system/vorlagestudiengang', 'student/stammdaten',
			'student/vorrueckung', 'system/developer', 'system/loginasuser',
			'user', 'veranstaltung', 'vertrag/mitarbeiter', 'vertrag/typen',
			'wawi/berichte', 'wawi/bestellung', 'wawi/bestellung_advanced', 'wawi/budget',
			'wawi/delete_advanced',	'wawi/firma', 'wawi/freigabe',
			'wawi/freigabe_advanced', 'wawi/inventar', 'wawi/konto', 'wawi/kostenstelle',
			'wawi/rechnung', 'wawi/rechnung_freigeben',	'wawi/rechnung_transfer',
			'wawi/storno'
		)
	),
	array
	(
		'rolle_kurzbz' => 'infocenter',
		'berechtigung' => array
		(
			'basis/adresse','basis/akte','basis/kontakt','basis/log','basis/nation','basis/notiz','basis/notizzuordnung',
			'basis/person','basis/prestudent','basis/prestudentstatus','basis/status','basis/zgv','basis/zgvmaster',
			'lehre/studienplan','system/filters','fs/dms','basis/message','basis/benutzerrolle', 'basis/sprache'
		)
	)
);
