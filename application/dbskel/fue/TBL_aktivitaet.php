<?php

$tableArray = array(
	'tbl_aktivitaet' => array(
		'aktivitaet_kurzbz' => array(
			'comment' => 'I guess this is the PK',
			'type' => 'character varying(16)',
			'null' => false
		),
		'beschreibung' => array(
			'comment' => 'none',
			'type' => 'character varying(256)',
			'null' => false,
			'default' => "'Test string'"
		),
		'sort' => array(
			'comment' => 'nope',
			'type' => 'integer',
			'default' => 1
		),
		'oe_kurzbz' => array(
			'comment' => 'uhm',
			'type' => 'character varying(32)'
		)
	),
	'comment' => 'Timesheet SLA Activity'
);
