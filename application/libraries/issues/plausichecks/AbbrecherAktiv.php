<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class AbbrecherAktiv extends PlausiChecker
{
	protected $_base_sql = "
		SELECT
			pre.person_id, pre.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
		FROM
			public.tbl_prestudentstatus pre_status
			JOIN public.tbl_prestudent pre USING(prestudent_id)
			JOIN public.tbl_student student USING(prestudent_id)
			JOIN public.tbl_benutzer benutzer on(benutzer.uid=student.student_uid)
			JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
		WHERE
			pre_status.status_kurzbz ='Abbrecher'
			AND benutzer.aktiv=true";

	protected $_config_params = ['exkludierteStudiengaenge' => " AND stg.studiengang_kz NOT IN ?"];
	protected $_params_for_checking = ['studiengang_kz' => " AND stg.studiengang_kz = ?", 'prestudent_id' => " AND pre.prestudent_id = ?"];
	protected $_fehlertext_params = ['prestudent_id'];
	protected $_resolution_params = ['prestudent_id'];
}
