<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

$config['frist_rueckzahlung_studiengebuer_WS'] = '15.10.';
$config['frist_rueckzahlung_studiengebuer_SS'] = '15.03.';

// TODO(chris): review this!
#$config['unterbrechung_dms'] = ['oe_kurzbz' => null, 'dokument_kurzbz' => null, 'kategorie_kurzbz' => null];
$config['unterbrechung_dms'] = ['oe_kurzbz' => null, 'dokument_kurzbz' => null, 'kategorie_kurzbz' => 'Akte'];

/**
 * UPLOAD
 */

/**
 * Allowed filetypes for attachment upload in unterbrechung antrag
 *
 * @var array           An array of fileextensions
 */
$config['unterbrechung_dms_filetypes'] = ['jpg', 'pdf'];


/**
 * GRADES
 */

/**
 * On wiederholung the student must repeat certain lvs.
 * This lvs will be graded with this id
 *
 * @var integer			tbl_note.note
 */
$config['wiederholung_note_angerechnet'] = 19;

/**
 * On wiederholung the student can not attend certain lvs.
 * Those lvs will be graded with this id
 *
 * @var integer			tbl_note.note
 */
$config['wiederholung_note_nicht_zugelassen'] = 20;


/**
 * JOBS
 */

/**
 * The Job will remind for every Unterbrecher who has a
 * wiedereinstieg_datum between the date the Job is run
 * and the modified date
 * e.g.: If the Job is running on 2023-04-20 and the modifier
 * is '+3 days' it will remind of everyone that
 * has a wiedereinstiegs_datum between 2023-04-20 and 2023-04-23
 *
 * @var string	        A string formated as PHP DateTime modifier
 * @see https://www.php.net/manual/de/datetime.modify.php
 */
$config['unterbrechung_job_remind_wiedereinstieg_date_modifier'] = '+3 days';

/**
 * The Job will sent a request to everyone who faild the 3rd committee exam
 * and respecting the given conditions (not repeated yet, stg not in blacklist)
 * to decide if he/she will repeat or not
 *
 * First request
 *
 * @var string           A string formated as PHP DateTime modifier
 * @see https://www.php.net/manual/de/datetime.modify.php
 */
$config['wiederholung_job_request_1_date_modifier'] = '+0 days';

/**
 * Second request
 *
 * @var string           A string formated as PHP DateTime modifier
 * @see https://www.php.net/manual/de/datetime.modify.php
 */
$config['wiederholung_job_request_2_date_modifier'] = '+3 weeks';

/**
 * Final deadline - after this the student will be abgemeldet if he hasn't chosen yet
 *
 * @var string           A string formated as PHP DateTime modifier
 * @see https://www.php.net/manual/de/datetime.modify.php
 */
$config['wiederholung_job_deadline_date_modifier'] = '+1 month';

/**
 * before this exam dates for Wiederholer will be ignored
 *
 * @var string           A string formated as Date
 *
 */
$config['digitalization_start'] = '2022-07-01';




/**
 * Objection period - the student will be abgemeldet if he hasn't objected in this period
 *
 * @var string           A string formated as PHP DateTime modifier
 * @see https://www.php.net/manual/de/datetime.modify.php
 */
$config['abmeldung_job_deadline_date_modifier'] = '+2 weeks';



/**
 * System User - uid of a user that is allowed to set prestudentstatus
 *
 * @var string
 */
$config['antrag_job_systemuser'] = '';


/**
 * WHITELISTS
 */

/**
 * List of stati who entitle a prestudent to create an Antrag
 *
 * @var array			Array of tbl_status.status_kurzbz's
 */
$config['antrag_prestudentstatus_whitelist'] = ['Student', 'Diplomand'];
$config['antrag_prestudentstatus_whitelist_abmeldung'] = ['Student', 'Diplomand', 'Unterbrecher'];


/**
 * BLACKLISTS
 */

/**
 * List of Statusgründe that prevent a prestudent from create an Wiederholungsantrag
 *
 * @var array			An array of tbl_status_grund.statusgrund_id's
 */
$config['status_gruende_wiederholer'] = [16, 15];

/**
 * Blacklisted for abmeldung anträge
 *
 * @var array           An array of tbl_studiengang.studiengang_kz's
 */
$config['stgkz_blacklist_abmeldung'] = [];

/**
 * Blacklisted for unterbrechung anträge
 *
 * @var array           An array of tbl_studiengang.studiengang_kz's
 */
$config['stgkz_blacklist_unterbrechung'] = [];

/**
 * Blacklisted for wiederholung anträge
 *
 * @var array           An array of tbl_studiengang.studiengang_kz's
 */
$config['stgkz_blacklist_wiederholung'] = [];

/**
 * Blacklisted noten for negative committee exams
 * noten with this ids won't be seen as negative
 *
 * @var array           An array of noten ids
 */
$config['note_blacklist_wiederholung'] = [];
