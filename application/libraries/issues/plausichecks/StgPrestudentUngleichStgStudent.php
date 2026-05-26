<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class StgPrestudentUngleichStgStudent extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;
		$person_id = isset($params['person_id']) ? $params['person_id'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->getStgPrestudentUngleichStgStudent($studiengang_kz, null, $person_id, $exkludierte_studiengang_kz);

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
					'resolution_params' => array('prestudent_id' => $prestudent->prestudent_id)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Studiengang should be the same for prestudent and student.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getStgPrestudentUngleichStgStudent($studiengang_kz = null, $prestudent_id = null, $person_id = null, $exkludierte_studiengang_kz = null)
	{
		$params = array();

		$qry = "
			SELECT
				pre.person_id, pre.prestudent_id, stg.oe_kurzbz prestudent_stg_oe_kurzbz, student_stg.oe_kurzbz student_stg_oe_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_student stud USING(prestudent_id)
				JOIN public.tbl_person pers USING(person_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				JOIN public.tbl_studiengang student_stg ON stud.studiengang_kz = student_stg.studiengang_kz
			WHERE
				stud.studiengang_kz != pre.studiengang_kz";

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

		if (isset($person_id))
		{
			$qry .= " AND pers.person_id = ?";
			$params[] = $person_id;
		}

		if (isset($exkludierte_studiengang_kz) && !isEmptyArray($exkludierte_studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz NOT IN ?";
			$params[] = $exkludierte_studiengang_kz;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
