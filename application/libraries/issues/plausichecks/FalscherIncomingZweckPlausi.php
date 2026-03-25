<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class FalscherIncomingZweckPlausi extends PlausiChecker
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

		// get all students failing the plausicheck
		$prestudentRes = $this->_getFalscherIncomingZweck(
			$studiensemester_kurzbz,
			$studiengang_kz,
			$bisio_id,
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
	private function _getFalscherIncomingZweck(
		$studiensemester_kurzbz = null,
		$studiengang_kz = null,
		$bisio_id = null,
		$exkludierte_studiengang_kz = null
	) {

		$params = array();
		$studiensemester_clause = '';

		if (isset($studiensemester_kurzbz))
		{
			$studiensemester_clause = "AND status.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		$qry = "
		SELECT * FROM (
			SELECT
				DISTINCT ON (bisio_id) prestudent_id, person_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, zw.zweck_code, stg.studiengang_kz,
				bisio.bisio_id, COUNT(zw.zweck_code) OVER (PARTITION BY bisio_id) AS anzahl
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_student stud USING (prestudent_id)
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiensemester sem ON status.studiensemester_kurzbz = sem.studiensemester_kurzbz
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				JOIN bis.tbl_bisio bisio ON stud.student_uid = bisio.student_uid
				JOIN bis.tbl_bisio_zweck zw USING (bisio_id)
			WHERE
				stg.melderelevant
				AND pre.bismelden
				AND status.status_kurzbz = 'Incoming'
				AND bisio.von::date < sem.ende AND bisio.bis::date > sem.start
				{$studiensemester_clause}
		) zwecke
		WHERE anzahl = 1 AND zweck_code NOT IN ('1', '2', '3')";

		if (isset($studiengang_kz))
		{
			$qry .= " AND zwecke.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($bisio_id))
		{
			$qry .= " AND zwecke.bisio_id = ?";
			$params[] = $bisio_id;
		}

		if (isset($exkludierte_studiengang_kz) && !isEmptyArray($exkludierte_studiengang_kz))
		{
			$qry .= " AND zwecke.studiengang_kz NOT IN ?";
			$params[] = $exkludierte_studiengang_kz;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
