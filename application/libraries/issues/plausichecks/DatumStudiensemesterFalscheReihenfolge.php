<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class DatumStudiensemesterFalscheReihenfolge extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->getDatumStudiensemesterFalscheReihenfolge($studiengang_kz, null, $exkludierte_studiengang_kz);

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
					'fehlertext_params' => array('prestudent_id' => $prestudent->prestudent_id),
					'resolution_params' => array(
						'prestudent_id' => $prestudent->prestudent_id
					)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Status Dates and status studysemester dates should be in correct order.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if check is to be executed only for certain Studiengaenge
	 * @return success with prestudents or error
	 */
	public function getDatumStudiensemesterFalscheReihenfolge($studiengang_kz = null, $prestudent_id = null, $exkludierte_studiengang_kz = null)
	{
		$params = array();

		// all active students with Status student in current semester
		$qry = "
			SELECT DISTINCT ON (prestudent_id) *
			FROM (
				SELECT
					prestudent.person_id, prestudent.prestudent_id,
					stg.studiengang_kz, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz,
					ROW_NUMBER () OVER (
						PARTITION BY prestudent.prestudent_id
						ORDER BY sem.start DESC, status.datum DESC, status.insertamum DESC, status.ext_id DESC
					) AS reihenfolge_semester,
					ROW_NUMBER () OVER (
						PARTITION BY prestudent.prestudent_id
						ORDER BY status.datum DESC, status.insertamum DESC, status.ext_id DESC
					) AS reihenfolge_datum
				FROM
					public.tbl_student student
					JOIN public.tbl_benutzer benutzer on(student.student_uid = benutzer.uid)
					JOIN public.tbl_prestudent prestudent USING(prestudent_id)
					JOIN public.tbl_prestudentstatus status USING(prestudent_id)
					JOIN public.tbl_studiensemester sem USING(studiensemester_kurzbz)
					JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
				WHERE
					benutzer.aktiv=true
					AND status.status_kurzbz='Student'
			) reihenfolge
			WHERE reihenfolge_semester <> reihenfolge_datum";

		if (isset($studiengang_kz))
		{
			$qry .= " AND studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		if (isset($exkludierte_studiengang_kz) && !isEmptyArray($exkludierte_studiengang_kz))
		{
			$qry .= " AND studiengang_kz NOT IN ?";
			$params[] = $exkludierte_studiengang_kz;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
