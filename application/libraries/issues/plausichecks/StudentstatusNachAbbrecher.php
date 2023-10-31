<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class StudentstatusNachAbbrecher extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->getStudentstatusNachAbbrecher($studiengang_kz, null, $exkludierte_studiengang_kz);

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
	 * There shouldn't be any status after Abbrecher status.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getStudentstatusNachAbbrecher($studiengang_kz = null, $prestudent_id = null, $exkludierte_studiengang_kz = null)
	{
		$params = array();

		$qry = "
			SELECT
				prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_student student
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus prestatus USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				prestatus.status_kurzbz = 'Abbrecher'
				AND get_rolle_prestudent(prestudent.prestudent_id, prestatus.studiensemester_kurzbz) <> 'Abbrecher'";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
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
