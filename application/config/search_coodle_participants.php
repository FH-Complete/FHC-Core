<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


$CI =& get_instance();


$config['employee'] = $CI->config->item('employee', 'search');
unset($config['student']['searchfields']['email']);
unset($config['student']['searchfields']['tel']);
$config['employee']['resultfields'] = [
	"m.mitarbeiter_uid AS uid",
	"(p.vorname || ' ' || p.nachname) AS name",
];
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
	"(p.vorname || ' ' || p.nachname) AS name",
];
$config['student']['resultjoin'] = "
		JOIN public.tbl_student s USING (student_uid)
		JOIN public.tbl_benutzer b ON(b.uid = s.student_uid AND b.aktiv = true)
		JOIN public.tbl_person p USING(person_id)";

$config['group'] = $CI->config->item('group', 'search');
$config['group']['resultfields'] = [
		"g.gruppe_kurzbz as name",
		"ARRAY( 
			SELECT bg.uid as uid
			FROM public.tbl_benutzergruppe bg
			WHERE bg.gruppe_kurzbz = g.gruppe_kurzbz
			ORDER BY bg.uid
			) AS user_uids",
		"ARRAY( 
			SELECT p.vorname || ' ' || p.nachname AS name
			FROM public.tbl_benutzergruppe bg
			JOIN public.tbl_benutzer b ON(b.uid = bg.uid)
			JOIN public.tbl_person p ON(p.person_id = b.person_id)
			WHERE bg.gruppe_kurzbz = g.gruppe_kurzbz
			ORDER BY bg.uid
			) AS user_names",
];