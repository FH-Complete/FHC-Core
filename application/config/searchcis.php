<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


$CI =& get_instance();


$config['employee'] = $CI->config->item('employee', 'search');
$config['employee']['resultjoin'] = "
		JOIN public.tbl_mitarbeiter m USING (mitarbeiter_uid)
		JOIN public.tbl_benutzer b ON (b.uid = m.mitarbeiter_uid AND b.aktiv = true)
		JOIN public.tbl_person p USING(person_id)
		LEFT JOIN (
			SELECT kontakt, standort_id
			FROM public.tbl_kontakt
			WHERE kontakttyp = 'telefon'
		) k ON (k.standort_id = m.standort_id)";

$config['student'] = $CI->config->item('student', 'search');
unset($config['student']['searchfields']['email']);
unset($config['student']['searchfields']['tel']);
$config['student']['resultfields'] = [
	"s.student_uid AS uid",
	"s.matrikelnr",
	"p.person_id",
	"(p.vorname || ' ' || p.nachname) AS name",
	"ARRAY[s.student_uid || '@' || '" . DOMAIN . "'] AS email",
	"CASE
		WHEN p.foto IS NOT NULL THEN 'data:image/jpeg' || CONVERT_FROM(DECODE('3b','hex'), 'UTF8') || 'base64,' || p.foto
		ELSE NULL END
		AS photo_url",
	"b.aktiv"
];
$config['student']['resultjoin'] = "
		JOIN public.tbl_student s USING (student_uid)
		JOIN public.tbl_benutzer b ON(b.uid = s.student_uid AND b.aktiv = true)
		JOIN public.tbl_person p USING(person_id)";

$config['organisationunit'] = $CI->config->item('organisationunit', 'search');
$config['organisationunit']['prepare'] = 'active_organisationseinheit AS (SELECT * FROM public.tbl_organisationseinheit WHERE aktiv = true AND organisationseinheittyp_kurzbz <> \'Container\')';
$config['organisationunit']['table'] = 'active_organisationseinheit';

$config['room'] = $CI->config->item('room', 'search');

$config['cms'] = $CI->config->item('cms', 'search');

$config['dms'] = $CI->config->item('dms', 'search');
