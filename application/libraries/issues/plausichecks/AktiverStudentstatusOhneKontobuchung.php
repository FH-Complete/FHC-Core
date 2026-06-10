<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 * Student with active status should have been charged, i.e. have a Kontobuchung with negative or zero value.
 */
class AktiverStudentstatusOhneKontobuchung extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiensemester_kurzbz = isset($params['studiensemester_kurzbz']) ? $params['studiensemester_kurzbz'] : null;
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->getAktiverStudentstatusOhneKontobuchung(
			$studiensemester_kurzbz,
			$studiengang_kz,
			null,
			$exkludierte_studiengang_kz
		);

		if (isError($prestudentRes)) return $prestudentRes;

		if (hasData($prestudentRes))
		{
			$prestudents = getData($prestudentRes);

			// populate results with data necessary for writing issues
			foreach ($prestudents as $prestudent)
			{
				$results[] = array(
					'person_id' => $prestudent->person_id,
					'oe_kurzbz' => $prestudent->prestudent_stg_oe_kurzbz,
					'fehlertext_params' => array(
						'prestudent_id' => $prestudent->prestudent_id,
						'studiensemester_kurzbz' => $prestudent->studiensemester_kurzbz
					),
					'resolution_params' => array(
						'prestudent_id' => $prestudent->prestudent_id,
						'studiensemester_kurzbz' => $prestudent->studiensemester_kurzbz
					)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Student with active status should have been charged, i.e. have a Kontobuchung with a negative or zero value.
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getAktiverStudentstatusOhneKontobuchung(
		$studiensemester_kurzbz,
		$studiengang_kz = null,
		$prestudent_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT ON (pre.prestudent_id)
				pre.person_id, pre.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_person pers USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg USING(studiengang_kz)
			WHERE
				status.studiensemester_kurzbz = ?
				AND status.status_kurzbz IN ('Student', 'Incoming')
				AND NOT EXISTS (
					SELECT 1
					FROM
						public.tbl_konto
					WHERE
						person_id = pers.person_id
						AND studiensemester_kurzbz = status.studiensemester_kurzbz
						AND buchungsnr_verweis IS NULL
						AND betrag <= 0
				)
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		if (isset($exkludierte_studiengang_kz) && !isEmptyArray($exkludierte_studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz NOT IN ?";
			$params[] = $exkludierte_studiengang_kz;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
