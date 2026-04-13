<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class OutgoingErworbeneEctsFehlenPlausi extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiensemester_kurzbz = isset($params['studiensemester_kurzbz']) ? $params['studiensemester_kurzbz'] : null;
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;
		$bisio_id = isset($params['bisio_id']) ? $params['bisio_id'] : null;
		$person_id = isset($params['person_id']) ? $params['person_id'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->_getAngerechneteEctsFehlen(
			$studiensemester_kurzbz,
			$studiengang_kz,
			$bisio_id,
			$person_id,
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
					//'fehlertext_params' => array('bisio_id' => $prestudent->bisio_id),
					'resolution_params' => array('bisio_id' => $prestudent->bisio_id)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Prestudent should have a final status.
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param bisio_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	private function _getAngerechneteEctsFehlen(
		$studiensemester_kurzbz = null,
		$studiengang_kz = null,
		$bisio_id = null,
		$person_id = null,
		$exkludierte_studiengang_kz = null
	) {

		$params = array();

		$qry = "
			SELECT
				DISTINCT ON (bisio_id) prestudent_id, person_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz,
				stg.studiengang_kz, bisio.bisio_id
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_student stud USING (prestudent_id)
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiensemester sem ON status.studiensemester_kurzbz = sem.studiensemester_kurzbz
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				JOIN bis.tbl_bisio bisio ON stud.student_uid = bisio.student_uid
			WHERE
				bisio.bis IS NOT NULL
				AND bisio.bis::date <= NOW()
				AND bisio.bis::date - bisio.von::date >= 29
				AND bisio.ects_erworben IS NULL
				AND NOT EXISTS (SELECT 1 FROM public.tbl_prestudentstatus WHERE status_kurzbz = 'Incoming' AND prestudent_id = pre.prestudent_id)
				AND bisio.von::date < sem.ende AND bisio.bis::date > sem.start
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND status.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($bisio_id))
		{
			$qry .= " AND bisio.bisio_id = ?";
			$params[] = $bisio_id;
		}

		if (isset($person_id))
		{
			$qry .= " AND pre.person_id = ?";
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
