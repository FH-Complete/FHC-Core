<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


$CI =& get_instance();

$config['person'] = $CI->config->item('person', 'search');

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

$config['unassigned_employee'] = $CI->config->item('unassigned_employee', 'search');
$config['unassigned_employee']['resultjoin'] = "
    JOIN public.tbl_mitarbeiter m USING (mitarbeiter_uid)
    JOIN public.tbl_benutzer b ON (b.uid = m.mitarbeiter_uid AND b.aktiv = true)
    JOIN public.tbl_person p USING(person_id)
    LEFT JOIN (
        SELECT kontakt, standort_id
        FROM public.tbl_kontakt
        WHERE kontakttyp = 'telefon'
    ) k ON (k.standort_id = m.standort_id)";

$config['room'] = $CI->config->item('room', 'search');

$config['organisationunit'] = $CI->config->item('organisationunit', 'search');