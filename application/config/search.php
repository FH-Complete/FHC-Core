<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


$config['person'] = [
	'primarykey' => 'person_id',
	'table' => 'public.tbl_person',
	'searchfields' => [
		'uid' => [
			'comparison' => 'equals',
			'field' => 'uid',
			'join' => [
				'table' => "public.tbl_benutzer",
				'using' => "person_id"
			],
			'1-n' => true
		],
		'vorname' => [
			'alias' => ['firstname'],
			'comparison' => 'similar',
			'field' => 'vorname'
		],
		'nachname' => [
			'alias' => ['lastname', 'surename'],
			'comparison' => 'similar',
			'field' => 'nachname'
		],
		'name' => [
			'comparison' => 'similar',
			'field' => "(vorname || ' ' || nachname)"
		],
		'email' => [
			'comparison' => 'similar',
			'field' => 'kontakt',
			'join' => [
				'table' => "public.tbl_kontakt",
				'on' => "kontakttyp = 'email' AND tbl_kontakt.person_id = tbl_person.person_id"
			],
			"1-n" => true
		],
		'tel' => [
			'alias' => ['phone', 'telefon'],
			'comparison' => 'similar',
			'field' => 'kontakt',
			'join' => [
				'table' => "public.tbl_kontakt",
				'on' => "kontakttyp IN ('telefon', 'so.tel', 'mobil') AND tbl_kontakt.person_id = tbl_person.person_id"
			],
			"1-n" => true
		],
		'preid' => [
			'alias' => ['prestudent_id'],
			'comparison' => 'equal-int',
			'field' => 'prestudent_id',
			'join' => [
				'table' => "public.tbl_prestudent",
				'using' => "person_id"
			],
			'1-n' => true
		],
		'pid' => [
			'alias' => ['person_id'],
			'comparison' => 'equal-int',
			'field' => 'person_id'
		]
	],
	'resultfields' => [
		"ARRAY( SELECT uid FROM public.tbl_benutzer WHERE person_id = p.person_id ) AS uids",
		"p.person_id",
		"(p.vorname || ' ' || p.nachname) AS name",
		"ARRAY( SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp = 'email' AND person_id=p.person_id ) AS email",
		"CASE
			WHEN p.foto IS NOT NULL THEN 'data:image/jpeg' || CONVERT_FROM(DECODE('3b','hex'), 'UTF8') || 'base64,' || p.foto
			ELSE NULL END
			AS photo_url"
	],
	'resultjoin' => "
		JOIN public.tbl_person p USING (person_id)"
];

$config['student'] = [
	'primarykey' => 'student_uid',
	'table' => 'public.tbl_student',
	'searchfields' => [
		'uid' => [
			'comparison' => 'equals',
			'field' => 'student_uid'
		],
		'vorname' => [
			'alias' => ['firstname'],
			'comparison' => 'similar',
			'field' => 'vorname',
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
		],
		'nachname' => [
			'alias' => ['lastname', 'surename'],
			'comparison' => 'similar',
			'field' => 'nachname',
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
		],
		'name' => [
			'comparison' => 'similar',
			'field' => "(vorname || ' ' || nachname)",
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
		],
		'email' => [
			'comparison' => 'similar',
			'field' => 'kontakt',
			'join' => [
				[
					'table' => "public.tbl_prestudent",
					'using' => "prestudent_id"
				],
				[
					'table' => "public.tbl_kontakt",
					'on' => "kontakttyp = 'email' AND tbl_kontakt.person_id = tbl_prestudent.person_id"
				]
			],
			"1-n" => true
		],
		'tel' => [
			'alias' => ['phone', 'telefon'],
			'comparison' => 'similar',
			'field' => 'kontakt',
			'join' => [
				[
					'table' => "public.tbl_prestudent",
					'using' => "prestudent_id"
				],
				[
					'table' => "public.tbl_kontakt",
					'on' => "kontakttyp IN ('telefon', 'so.tel', 'mobil') AND tbl_kontakt.person_id = tbl_prestudent.person_id"
				]
			],
			"1-n" => true
		],
		'stg' => [
			'alias' => ['studiengang'],
			'comparison' => 'equals',
			'field' => "typ || kurzbz",
			'join' => [
				[
					'table' => "public.tbl_prestudent",
					'using' => "prestudent_id"
				],
				[
					'table' => "public.tbl_studiengang",
					'on' => "tbl_studiengang.studiengang_kz = tbl_prestudent.studiengang_kz"
				]
			]
		],
		'preid' => [
			'alias' => ['prestudent_id'],
			'comparison' => 'equal-int',
			'field' => 'prestudent_id'
		],
		'pid' => [
			'alias' => ['person_id'],
			'comparison' => 'equal-int',
			'field' => 'person_id',
			'join' => [
				'table' => "public.tbl_prestudent",
				'using' => "prestudent_id"
			]
		]
	],
	'resultfields' => [
		"s.student_uid AS uid",
		"s.matrikelnr",
		"p.person_id",
		"(p.vorname || ' ' || p.nachname) AS name",
		"ARRAY( SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp = 'email' AND person_id=p.person_id ) AS email",
		"CASE
			WHEN p.foto IS NOT NULL THEN 'data:image/jpeg' || CONVERT_FROM(DECODE('3b','hex'), 'UTF8') || 'base64,' || p.foto
			ELSE NULL END
			AS photo_url"
	],
	'resultjoin' => "
		JOIN public.tbl_student s USING (student_uid)
		JOIN public.tbl_benutzer b ON(b.uid = s.student_uid)
		JOIN public.tbl_person p USING(person_id)"
];

$config['prestudent'] = [
	'primarykey' => 'prestudent_id',
	'table' => 'public.tbl_prestudent',
	'searchfields' => [
		'uid' => [
			'comparison' => 'equals',
			'field' => 'student_uid',
			'join' => [
				'table' => "public.tbl_student",
				'using' => "prestudent_id"
			]
		],
		'vorname' => [
			'alias' => ['firstname'],
			'comparison' => 'similar',
			'field' => 'vorname',
			'join' => [
				'table' => "public.tbl_person",
				'using' => "person_id"
			]
		],
		'nachname' => [
			'alias' => ['lastname', 'surename'],
			'comparison' => 'similar',
			'field' => 'nachname',
			'join' => [
				'table' => "public.tbl_person",
				'using' => "person_id"
			]
		],
		'name' => [
			'comparison' => 'similar',
			'field' => "(vorname || ' ' || nachname)",
			'join' => [
				'table' => "public.tbl_person",
				'using' => "person_id"
			]
		],
		'email' => [
			'comparison' => 'similar',
			'field' => 'kontakt',
			'join' => [
				'table' => "public.tbl_kontakt",
				'on' => "kontakttyp = 'email' AND tbl_kontakt.person_id = tbl_prestudent.person_id"
			],
			"1-n" => true
		],
		'tel' => [
			'alias' => ['phone', 'telefon'],
			'comparison' => 'similar',
			'field' => 'kontakt',
			'join' => [
				'table' => "public.tbl_kontakt",
				'on' => "kontakttyp IN ('telefon', 'so.tel', 'mobil') AND tbl_kontakt.person_id = tbl_prestudent.person_id"
			],
			"1-n" => true
		],
		'stg' => [
			'alias' => ['studiengang'],
			'comparison' => 'equals',
			'field' => "typ || kurzbz",
			'join' => [
				'table' => "public.tbl_studiengang",
				'using' => "studiengang_kz"
			]
		],
		'preid' => [
			'alias' => ['prestudent_id'],
			'comparison' => 'equal-int',
			'field' => 'prestudent_id'
		],
		'pid' => [
			'alias' => ['person_id'],
			'comparison' => 'equal-int',
			'field' => 'person_id',
			'join' => [
				'table' => "public.tbl_person",
				'using' => "person_id"
			]
		]
	],
	'resultfields' => [
		"ps.prestudent_id",
		"ps.studiengang_kz",
		"s.matrikelnr",
		"p.person_id",
		"b.uid",
		"(p.vorname || ' ' || p.nachname) AS name",
		"ARRAY( SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp = 'email' AND person_id=p.person_id ) AS email",
		"CASE
			WHEN p.foto IS NOT NULL THEN 'data:image/jpeg' || CONVERT_FROM(DECODE('3b','hex'), 'UTF8') || 'base64,' || p.foto
			ELSE NULL END
			AS photo_url",
		"UPPER(sg.typ || sg.kurzbz) AS stg_kuerzel",
		"sg.bezeichnung",
		"(
			SELECT bezeichnung_mehrsprachig[(TABLE lang)]
			FROM public.tbl_status
			WHERE status_kurzbz = public.get_rolle_prestudent(ps.prestudent_id, NULL)
			LIMIT 1
		) AS status",
		"COALESCE(
			(
				SELECT COALESCE(plan.orgform_kurzbz, pss.orgform_kurzbz)
				FROM public.tbl_prestudentstatus pss
				LEFT JOIN lehre.tbl_studienplan plan USING (studienplan_id)
				WHERE pss.prestudent_id=ps.prestudent_id
				ORDER BY pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
				LIMIT 1
			),
			sg.orgform_kurzbz
		) AS orgform"
	],
	'resultjoin' => "
		LEFT JOIN public.tbl_prestudent ps USING (prestudent_id)
		LEFT JOIN public.tbl_student s ON (ps.prestudent_id = s.prestudent_id)
		LEFT JOIN public.tbl_benutzer b ON (b.uid = s.student_uid)
		JOIN public.tbl_person p ON (p.person_id = ps.person_id)
		LEFT JOIN public.tbl_studiengang sg ON (sg.studiengang_kz = ps.studiengang_kz)"
];

$config['employee'] = [
	'alias' => ['ma', 'mitarbeiter'],
	'primarykey' => 'mitarbeiter_uid',
	'table' => 'public.tbl_mitarbeiter',
	'searchfields' => [
		'uid' => [
			'alias' => ['mitarbeiter_uid'],
			'comparison' => 'equals',
			'field' => "mitarbeiter_uid"
		],
		'vorname' => [
			'alias' => ['firstname'],
			'comparison' => 'similar',
			'field' => "vorname",
			'join' => [
				[
					'table' => "public.tbl_benutzer",
					'on' => "uid = mitarbeiter_uid"
				],
				[
					'table' => "public.tbl_person",
					'using' => "person_id"
				]
			]
		],
		'nachname' => [
			'alias' => ['lastname', 'surename'],
			'comparison' => 'similar',
			'field' => "nachname",
			'join' => [
				[
					'table' => "public.tbl_benutzer",
					'on' => "uid = mitarbeiter_uid"
				],
				[
					'table' => "public.tbl_person",
					'using' => "person_id"
				]
			]
		],
		'name' => [
			'comparison' => 'similar',
			'field' => "(vorname || ' ' || nachname)",
			'join' => [
				[
					'table' => "public.tbl_benutzer",
					'on' => "uid = mitarbeiter_uid"
				],
				[
					'table' => "public.tbl_person",
					'using' => "person_id"
				]
			]
		],
		'email' => [
			'comparison' => 'similar',
			'field' => "COALESCE(alias, uid) || '" . '@' . DOMAIN . "'",
			'join' => [
				'table' => "public.tbl_benutzer",
				'on' => "uid = mitarbeiter_uid"
			]
		],
		'tel' => [
			'alias' => ['phone', 'telefon'],
			'comparison' => 'similar',
			'field' => "TRIM(COALESCE(kontakt, '') || ' ' || COALESCE(telefonklappe, ''))",
			'join' => [
				'table' => "public.tbl_kontakt",
				'on' => "kontakttyp = 'telefon' AND tbl_kontakt.standort_id = tbl_mitarbeiter.standort_id"
			],
			"1-n" => true
		],
		'pid' => [
			'alias' => ['person_id'],
			'comparison' => 'equal-int',
			'field' => "person_id",
			'join' => [
				'table' => "public.tbl_benutzer",
				'on' => "uid = mitarbeiter_uid"
			]
		],
		'oe' => [
			'alias' => ['ou', 'organisationseinheit', 'organisationunit'],
			'comparison' => 'vector',
			'field' => "fts_bezeichnung",
			'join' => [
				[
					'table' => "public.tbl_benutzerfunktion",
					'on' => "mitarbeiter_uid = uid
						AND funktion_kurzbz = 'oezuordnung'
						AND (datum_von IS NULL OR datum_von <= NOW())
						AND (datum_bis IS NULL OR datum_bis >= NOW())"
				],
				[
					'table' => "public.tbl_organisationseinheit",
					'using' => "oe_kurzbz"
				]
			],
			'1-n' => true
		],
		'kst' => [
			'comparison' => 'vector',
			'field' => "fts_bezeichnung",
			'join' => [
				[
					'table' => "public.tbl_benutzerfunktion",
					'on' => "mitarbeiter_uid = uid
						AND funktion_kurzbz = 'kstzuordnung'
						AND (datum_von IS NULL OR datum_von <= NOW())
						AND (datum_bis IS NULL OR datum_bis >= NOW())"
				],
				[
					'table' => "public.tbl_organisationseinheit",
					'using' => "oe_kurzbz"
				]
			],
			'1-n' => true
		]
	],
	'resultfields' => [
		"b.uid",
		"p.person_id",
		"(p.vorname || ' ' || p.nachname) AS name",
		"ARRAY(
			SELECT
				'[' || ot.bezeichnung || '] ' || o.bezeichnung AS bezeichnung
			FROM public.tbl_benutzerfunktion bf
			JOIN public.tbl_organisationseinheit o USING(oe_kurzbz)
			JOIN public.tbl_organisationseinheittyp ot USING(organisationseinheittyp_kurzbz)
			WHERE bf.funktion_kurzbz = 'oezuordnung'
				AND (bf.datum_von IS NULL OR bf.datum_von <= NOW())
				AND (bf.datum_bis IS NULL OR bf.datum_bis >= NOW())
				AND bf.uid = b.uid
			GROUP BY o.bezeichnung, ot.bezeichnung
		) AS organisationunit_name",
		"COALESCE(b.alias, b.uid) || '" . '@' . DOMAIN . "' AS email",
		"TRIM(COALESCE(k.kontakt, '') || ' ' || COALESCE(m.telefonklappe, '')) AS phone",
		"'" . base_url("/cis/public/bild.php?src=person&person_id=") . "' || p.person_id AS photo_url",
		"ARRAY(
			SELECT
				'[' || ot.bezeichnung || '] ' || o.bezeichnung AS bezeichnung
			FROM public.tbl_benutzerfunktion bf
			JOIN public.tbl_organisationseinheit o USING(oe_kurzbz)
			JOIN public.tbl_organisationseinheittyp ot USING(organisationseinheittyp_kurzbz)
			WHERE bf.funktion_kurzbz = 'kstzuordnung'
				AND (bf.datum_von IS NULL OR bf.datum_von <= NOW())
				AND (bf.datum_bis IS NULL OR bf.datum_bis >= NOW())
				AND bf.uid = b.uid
			GROUP BY o.bezeichnung, ot.bezeichnung
		) AS standardkostenstelle"
	],
	'resultjoin' => "
		JOIN public.tbl_mitarbeiter m USING (mitarbeiter_uid)
		JOIN public.tbl_benutzer b ON (b.uid = m.mitarbeiter_uid)
		JOIN public.tbl_person p USING(person_id)
		LEFT JOIN (
			SELECT kontakt, standort_id
			FROM public.tbl_kontakt
			WHERE kontakttyp = 'telefon'
		) k ON (k.standort_id = m.standort_id)"
];

$config['unassigned_employee'] = $config['employee'];
$config['unassigned_employee']['alias'] = ['mitarbeiter_ohne_zuordnung'];
$config['unassigned_employee']['prepare'] = "unassigned_employee AS (
	SELECT tbl_mitarbeiter.*
	FROM public.tbl_mitarbeiter
	LEFT JOIN public.tbl_benutzerfunktion ON (
		uid = mitarbeiter_uid
		AND funktion_kurzbz = 'kstzuordnung'
		AND (datum_von IS NULL OR datum_von <= NOW())
		AND (datum_bis IS NULL OR datum_bis >= NOW())
	)
	WHERE tbl_benutzerfunktion.bezeichnung IS NULL
	UNION
	SELECT tbl_mitarbeiter.*
	FROM public.tbl_mitarbeiter
	LEFT JOIN public.tbl_benutzerfunktion ON (
		uid = mitarbeiter_uid
		AND funktion_kurzbz = 'oezuordnung'
		AND (datum_von IS NULL OR datum_von <= NOW())
		AND (datum_bis IS NULL OR datum_bis >= NOW())
	)
	WHERE tbl_benutzerfunktion.bezeichnung IS NULL
)";
$config['unassigned_employee']['table'] = "unassigned_employee";
$config['unassigned_employee']['searchfields']['tel']['join']['on'] = "kontakttyp = 'telefon' AND tbl_kontakt.standort_id = unassigned_employee.standort_id";

$config['organisationunit'] = [
	'alias' => ['ou', 'organisationseinheit', 'oe'],
	'primarykey' => 'oe_kurzbz',
	'table' => 'public.tbl_organisationseinheit',
	'searchfields' => [
		'uid' => [
			'comparison' => 'equals',
			'field' => 'uid',
			'prepare' => "organisationunit_leader(oe_kurzbz, uid, vorname, nachname) AS (
				SELECT oe_kurzbz, vorname, nachname, uid
				FROM public.tbl_benutzerfunktion
				JOIN public.tbl_benutzer USING (uid)
				JOIN public.tbl_person USING (person_id)
				WHERE funktion_kurzbz = 'Leitung'
				   AND (datum_von IS NULL OR datum_von <= NOW())
				   AND (datum_bis IS NULL OR datum_bis >= NOW())
				   AND tbl_benutzer.aktiv = TRUE
			)",
			'join' => [
				'table' => "organisationunit_leader",
				'using' => "oe_kurzbz"
			],
			'1-n' => true
		],
		'vorname' => [
			'alias' => ['firstname'],
			'comparison' => 'similar',
			'field' => 'vorname',
			'prepare' => "organisationunit_leader(oe_kurzbz, uid, vorname, nachname) AS (
				SELECT oe_kurzbz, vorname, nachname, uid
				FROM public.tbl_benutzerfunktion
				JOIN public.tbl_benutzer USING (uid)
				JOIN public.tbl_person USING (person_id)
				WHERE funktion_kurzbz = 'Leitung'
				   AND (datum_von IS NULL OR datum_von <= NOW())
				   AND (datum_bis IS NULL OR datum_bis >= NOW())
				   AND tbl_benutzer.aktiv = TRUE
			)",
			'join' => [
				'table' => "organisationunit_leader",
				'using' => "oe_kurzbz"
			],
			'1-n' => true
		],
		'nachname' => [
			'alias' => ['lastname', 'surename'],
			'comparison' => 'similar',
			'field' => 'nachname',
			'prepare' => "organisationunit_leader(oe_kurzbz, uid, vorname, nachname) AS (
				SELECT oe_kurzbz, vorname, nachname, uid
				FROM public.tbl_benutzerfunktion
				JOIN public.tbl_benutzer USING (uid)
				JOIN public.tbl_person USING (person_id)
				WHERE funktion_kurzbz = 'Leitung'
				   AND (datum_von IS NULL OR datum_von <= NOW())
				   AND (datum_bis IS NULL OR datum_bis >= NOW())
				   AND tbl_benutzer.aktiv = TRUE
			)",
			'join' => [
				'table' => "organisationunit_leader",
				'using' => "oe_kurzbz"
			],
			'1-n' => true
		],
		'name' => [
			'comparison' => 'similar',
			'field' => "(vorname || ' ' || nachname)",
			'prepare' => "organisationunit_leader(oe_kurzbz, uid, vorname, nachname) AS (
				SELECT oe_kurzbz, vorname, nachname, uid
				FROM public.tbl_benutzerfunktion
				JOIN public.tbl_benutzer USING (uid)
				JOIN public.tbl_person USING (person_id)
				WHERE funktion_kurzbz = 'Leitung'
				   AND (datum_von IS NULL OR datum_von <= NOW())
				   AND (datum_bis IS NULL OR datum_bis >= NOW())
				   AND tbl_benutzer.aktiv = TRUE
			)",
			'join' => [
				'table' => "organisationunit_leader",
				'using' => "oe_kurzbz"
			],
			'1-n' => true
		],
		'oe' => [
			'alias' => ['ou', 'organisationseinheit', 'organisationunit'],
			'comparison' => 'vector',
			'field' => "fts_bezeichnung"
		],
		'kurzbz' => [
			'alias' => ['oe_kurzbz'],
			'comparison' => 'equals',
			'field' => "oe_kurzbz"
		]
	],
	'resultfields' => [
		"oe.oe_kurzbz",
		"('[' || type.bezeichnung || '] ' || oe.bezeichnung) AS name",
		"oe_parent.oe_kurzbz AS parentoe_kurzbz",
		"(CASE WHEN oe_parent.bezeichnung IS NOT NULL THEN '[' || type_parent.bezeichnung || '] ' || oe_parent.bezeichnung END) AS parentoe_name",
		"ARRAY(
			SELECT JSON_BUILD_OBJECT('uid', b.uid, 'vorname', p.vorname, 'nachname', p.nachname, 'name', (p.vorname || ' ' || p.nachname))
			FROM public.tbl_benutzerfunktion bf
			JOIN public.tbl_benutzer b USING (uid)
			JOIN public.tbl_person p USING (person_id)
			WHERE funktion_kurzbz = 'Leitung'
			AND (datum_von IS NULL OR datum_von <= NOW())
			AND (datum_bis IS NULL OR datum_bis >= NOW())
			AND b.aktiv = TRUE
			AND oe_kurzbz = oe.oe_kurzbz
		) AS leaders",
		"(
			SELECT COUNT(*)
			FROM public.tbl_benutzerfunktion
			WHERE funktion_kurzbz = 'oezuordnung'
			AND (datum_von IS NULL OR datum_von <= NOW())
			AND (datum_bis IS NULL OR datum_bis >= NOW())
			AND oe_kurzbz = oe.oe_kurzbz
		) AS number_of_people",
		"(CASE WHEN oe.mailverteiler THEN oe.oe_kurzbz || '" . '@' . DOMAIN . "' END) AS mailgroup"
	],
	'resultjoin' => "
		JOIN public.tbl_organisationseinheit oe
			USING (oe_kurzbz)
		JOIN public.tbl_organisationseinheittyp type
			USING (organisationseinheittyp_kurzbz)
		LEFT JOIN public.tbl_organisationseinheit oe_parent
			ON (oe_parent.oe_kurzbz = oe.oe_parent_kurzbz)
		LEFT JOIN public.tbl_organisationseinheittyp type_parent
			ON (oe_parent.organisationseinheittyp_kurzbz = type_parent.organisationseinheittyp_kurzbz)"
];

$config['room'] = [
	'alias' => ['raum'],
	'primarykey' => 'ort_kurzbz',
	'table' => 'public.tbl_ort',
	'searchfields' => [
		'name' => [
			'comparison' => 'similar',
			'field' => 'ort_kurzbz'
		]
	],
	'resultfields' => [
		"ort.ort_kurzbz",
		"ort.gebteil AS building",
		"ort.ausstattung AS equipment",
		"ort.stockwerk AS floor",
		"ort.dislozierung AS room_number",
		"ort.content_id",
		"address.ort AS city",
		"address.plz AS zip",
		"address.strasse AS street",
		"ort.max_person",
		"ort.arbeitsplaetze AS workplaces"
	],
	'resultjoin' => "
		JOIN public.tbl_ort ort
			USING (ort_kurzbz)
		LEFT JOIN public.tbl_standort
			USING (standort_id)
		LEFT JOIN public.tbl_adresse address
			USING (adresse_id)"
];

$config['cms'] = [
	'primarykey' => 'contentsprache_id',
	'table' => 'campus.tbl_contentsprache',
	'prepare' => "
		cms_auth (content_id) AS (
			SELECT content_id
			FROM campus.tbl_content c
			WHERE NOT EXISTS (SELECT 1 FROM campus.tbl_contentgruppe g WHERE g.content_id=c.content_id)
			UNION
			SELECT content_id
			FROM public.vw_gruppen g
			JOIN campus.tbl_contentgruppe c USING (gruppe_kurzbz)
			WHERE uid = (TABLE auth)
		),
		cms_active (content_id, template_kurzbz) AS (
			SELECT content_id, template_kurzbz
			FROM cms_auth
			JOIN campus.tbl_content USING (content_id)
			WHERE aktiv = TRUE
		),
		cms_active_redirect (content_id) AS (
			SELECT content_id
			FROM cms_active
			WHERE template_kurzbz = 'redirect'
		),
		cms_active_redirect_linked (content_id) AS (
			SELECT content_id
			FROM cms_active_redirect
			JOIN campus.tbl_contentsprache USING (content_id)
			WHERE LEFT((xpath('string(/content/url)', content))[1]::text, 1) <> '#'
		),
		cms_active_others (content_id) AS (
			SELECT content_id
			FROM cms_active
			WHERE template_kurzbz IN ('contentmittitel', 'contentohnetitel', 'contentmittitel_filterwidget')
		)
	",
	'searchfields' => [
		'content' => [
			'alias' => ['inhalt'],
			'comparison' => "vector",
			'field' => "(setweight(to_tsvector('simple', COALESCE(titel, '')), 'A') || setweight(to_tsvector('simple', COALESCE(content, '')::text), 'B'))"
		],
		'content_id' => [
			'alias' => ['id'],
			'comparison' => "equal-int",
			'field' => "content_id"
		],
		'lang' => [
			'alias' => ['language', 'sprache'],
			'comparison' => "equals",
			'field' => "sprache"
		]
	],
	'resultfields' => [
		"contentsprache.content_id",
		"content.template_kurzbz",
		"contentsprache.version",
		"contentsprache.sprache AS language",
		"contentsprache.titel AS title",
		"contentsprache.content",
		"(xpath('string(/content/url)', contentsprache.content))[1] AS content_url"
	],
	'resultjoin' => "
		JOIN campus.tbl_contentsprache contentsprache
			USING (contentsprache_id)
		JOIN campus.tbl_content content
			USING (content_id)
		WHERE content_id IN (
			SELECT content_id 
			FROM cms_active_redirect_linked 
			UNION 
			SELECT content_id 
			FROM cms_active_others
		)
		AND version = campus.get_highest_content_version(content_id)"
];

$config['dms'] = [
	'primarykey' => 'dms_id, version',
	'table' => 'campus.tbl_dms_version',
	'searchfields' => [
		'keywords' => [
			'alias' => ['keyword', 'keywords', 'schlagwort', 'schlagworte'],
			'comparison' => "vector",
			'field' => "(to_tsvector('simple', COALESCE(schlagworte, '')))"
		]
	],
	'resultfields' => [
		"v.dms_id",
		"v.version",
		"v.filename",
		"v.mimetype",
		"v.name",
		"v.beschreibung AS description",
		"v.schlagworte AS keywords"
	],
	'resultjoin' => "
		JOIN campus.tbl_dms_version v
			USING (dms_id, version)
		WHERE cis_suche = TRUE
			AND version=(SELECT MAX(version) FROM campus.tbl_dms_version WHERE dms_id=v.dms_id)
			AND NOT EXISTS (
				SELECT
					1
				FROM
					fue.tbl_projekt_dokument p
				WHERE p.dms_id = v.dms_id
			) AND (
				NOT EXISTS (
					WITH RECURSIVE categories (kategorie_kurzbz) AS (
						SELECT
							kategorie_kurzbz
						FROM
							campus.tbl_dms c
						WHERE c.dms_id = v.dms_id
						UNION ALL
						SELECT
							cat.parent_kategorie_kurzbz AS kategorie_kurzbz
						FROM
							categories
							JOIN campus.tbl_dms_kategorie cat USING (kategorie_kurzbz)
					)
					SELECT
						1
					FROM
						categories
					JOIN campus.tbl_dms_kategorie_gruppe USING (kategorie_kurzbz)
					UNION
					SELECT
						1
					FROM
						categories
						JOIN campus.tbl_dms_kategorie USING (kategorie_kurzbz)
					WHERE
						berechtigung_kurzbz IS NOT NULL
				) OR EXISTS (
					WITH RECURSIVE categories (kategorie_kurzbz) AS (
						SELECT
							kategorie_kurzbz
						FROM
							campus.tbl_dms c
						WHERE c.dms_id = v.dms_id
						UNION ALL
						SELECT
							cat.parent_kategorie_kurzbz AS kategorie_kurzbz
						FROM
							categories
							JOIN campus.tbl_dms_kategorie cat USING (kategorie_kurzbz)
					)
					SELECT
						1
					FROM
						categories
						JOIN campus.tbl_dms_kategorie_gruppe USING (kategorie_kurzbz)
						JOIN public.tbl_benutzergruppe USING(gruppe_kurzbz)
					WHERE
						uid = (TABLE auth)
				)
			)"
];
