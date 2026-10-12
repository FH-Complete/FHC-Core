<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


$CI =& get_instance();

$config['employee'] = $CI->config->item('employee', 'search');

$config['lehreinheit'] = [
	'primarykey' => 'lehreinheit_id',
	'table' => 'lehre.tbl_lehreinheit',
	'searchfields' => [
		'leid' => [
			'alias' => ['lehreinheit_id', 'id'],
			'comparison' => 'equal-int',
			'field' => 'lehreinheit_id'
		],
		'bezeichnung' => [
			'alias' => ['name', 'title'],
			'comparison' => 'similar',
			'field' => 'tbl_lehrveranstaltung.bezeichnung',
			'join' => [
				'table' => "lehre.tbl_lehrveranstaltung",
				'using' => "lehrveranstaltung_id"
			]
		],
		'semester' => [
			'alias' => ['sem'],
			'comparison' => 'equal-int',
			'field' => 'tbl_lehrveranstaltung.semester',
			'join' => [
				'table' => "lehre.tbl_lehrveranstaltung",
				'using' => "lehrveranstaltung_id"
			]
		],
		'studiensemester_kurzbz' => [
			'alias' => ['studiensemester'],
			'comparison' => 'similar',
			'field' => 'tbl_lehreinheit.studiensemester_kurzbz',
		],
		'stg' => [
			'alias' => ['studiengang'],
			'comparison' => 'similar',
			'field' => 'lvstg.stg_kuerzel',
			'join' => [
				'table' => "(
					SELECT lehrveranstaltung_id, (typ || tbl_studiengang.kurzbz) AS stg_kuerzel
					FROM lehre.tbl_lehrveranstaltung
					JOIN public.tbl_studiengang USING (studiengang_kz)
				 ) lvstg",
				'using' => "lehrveranstaltung_id"
			]
		]
	],
	'resultfields' => [
		"le.lehreinheit_id AS id",
		"le.studiensemester_kurzbz",
		"lv.bezeichnung",
		"'lv_table_icon icon-' AS foto",
		"UPPER(stg.typ || stg.kurzbz) AS studiengang",
		"le.lehrform_kurzbz",
		"lv.semester"
	],
	'renderer' => 'teachingunit',
	'resultjoin' => "
		JOIN lehre.tbl_lehreinheit le USING (lehreinheit_id)
		JOIN lehre.tbl_lehrveranstaltung lv ON (lv.lehrveranstaltung_id = le.lehrveranstaltung_id)
		LEFT JOIN lehre.tbl_lehrveranstaltung lehrfach ON (lehrfach.lehrveranstaltung_id = le.lehrfach_id)
		JOIN public.tbl_studiengang stg ON (stg.studiengang_kz = lv.studiengang_kz)
		WHERE EXISTS (
			SELECT 1
			FROM system.vw_berechtigung
			WHERE vw_berechtigung.uid = (TABLE auth)
				AND vw_berechtigung.berechtigung_kurzbz = 'basis/lvverwaltung'
				AND (
					vw_berechtigung.oe_kurzbz IN (lv.oe_kurzbz, stg.oe_kurzbz, lehrfach.oe_kurzbz)
					OR EXISTS (
						SELECT 1
						FROM lehre.tbl_studienplan_lehrveranstaltung
						JOIN lehre.tbl_studienplan USING (studienplan_id)
						JOIN lehre.tbl_studienordnung USING (studienordnung_id)
						JOIN public.tbl_studiengang spstg USING (studiengang_kz)
						WHERE tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id = lv.lehrveranstaltung_id
							AND spstg.oe_kurzbz = vw_berechtigung.oe_kurzbz
					)
				)
		)
	"
];

$config['lehrveranstaltung'] = [
	'primarykey' => 'lehrveranstaltung_id',
	'table' => 'lehre.tbl_lehrveranstaltung',
	'searchfields' => [
		'lvid' => [
			'alias' => ['lehrveranstaltung_id', 'id'],
			'comparison' => 'equal-int',
			'field' => 'lehrveranstaltung_id'
		],
		'bezeichnung' => [
			'alias' => ['name', 'title'],
			'comparison' => 'similar',
			'field' => 'bezeichnung'
		],
		'semester' => [
			'alias' => ['sem'],
			'comparison' => 'equal-int',
			'field' => 'semester'
		],
		'stg' => [
			'alias' => ['studiengang'],
			'comparison' => 'similar',
			'field' => "(tbl_studiengang.typ || tbl_studiengang.kurzbz)",
			'join' => [
				'table' => "public.tbl_studiengang",
				'using' => "studiengang_kz"
			]
		]
	],
	'resultfields' => [
		"lv.lehrveranstaltung_id AS id",
		"lv.bezeichnung",
		"'lv_table_icon icon-lv' AS foto",
		"UPPER(stg.typ::varchar(1) || stg.kurzbz) AS studiengang",
		"lv.semester",
		"lv.lehrform_kurzbz",
		"oe.bezeichnung AS oe_bezeichnung"
	],
	'renderer' => 'teachingunit',
	'resultjoin' => "
		JOIN lehre.tbl_lehrveranstaltung lv USING (lehrveranstaltung_id)
		JOIN public.tbl_studiengang stg ON (stg.studiengang_kz = lv.studiengang_kz)
		LEFT JOIN public.tbl_organisationseinheit oe ON (oe.oe_kurzbz = lv.oe_kurzbz)
		WHERE EXISTS (
			SELECT 1
			FROM system.vw_berechtigung
			WHERE vw_berechtigung.uid = (TABLE auth)
				AND vw_berechtigung.berechtigung_kurzbz = 'basis/lvverwaltung'
				AND (
					vw_berechtigung.oe_kurzbz IN (lv.oe_kurzbz, stg.oe_kurzbz)
					OR EXISTS (
						SELECT 1
						FROM lehre.tbl_lehreinheit le
						JOIN lehre.tbl_lehrveranstaltung lehrfach ON (lehrfach.lehrveranstaltung_id = le.lehrfach_id)
						WHERE le.lehrveranstaltung_id = lv.lehrveranstaltung_id
						AND lehrfach.oe_kurzbz = vw_berechtigung.oe_kurzbz
					)
					OR EXISTS (
						SELECT 1
						FROM lehre.tbl_studienplan_lehrveranstaltung
						JOIN lehre.tbl_studienplan USING (studienplan_id)
						JOIN lehre.tbl_studienordnung USING (studienordnung_id)
						JOIN public.tbl_studiengang spstg USING (studiengang_kz)
						WHERE tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id = lv.lehrveranstaltung_id
							AND spstg.oe_kurzbz = vw_berechtigung.oe_kurzbz
					)
				)
		)
	"
];
