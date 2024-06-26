<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class StartsemesterUngleichPersonenkennzeichen extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiensemester_kurzbz = isset($params['studiensemester_kurzbz']) ? $params['studiensemester_kurzbz'] : null;
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;
		$prestudent_id = isset($params['prestudent_id']) ? $params['prestudent_id'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->getStartsemesterUngleichPersonenkennzeichen(
			$studiensemester_kurzbz,
			$studiengang_kz,
			$prestudent_id,
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
						'prestudent_id' => $prestudent->prestudent_id
					),
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
	 * Bewerber should have participated in Reihungstest.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getStartsemesterUngleichPersonenkennzeichen(
		$studiensemester_kurzbz = null,
		$studiengang_kz = null,
		$prestudent_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array();

		$qry = "
				SELECT
					prestudent.person_id, prestudent.prestudent_id,
					stg.studiengang_kz, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
				FROM
					public.tbl_prestudent prestudent
					JOIN public.tbl_studiengang stg USING (studiengang_kz)
					JOIN public.tbl_student stud USING (prestudent_id)
				WHERE
					stg.melderelevant
					AND prestudent.bismelden
					AND
					(
						SELECT
							substring(studiensemester_kurzbz, 5, 2)::integer -
							(CASE
								WHEN
									substring(studiensemester_kurzbz, 1, 2) = 'SS'
								THEN
									1
								ELSE
									0
							END)::integer
						FROM
							public.tbl_prestudentstatus
						WHERE
							prestudent_id = prestudent.prestudent_id
							AND status_kurzbz = 'Student'
						ORDER BY
							datum, insertamum, ext_id
						LIMIT 1
					) <> substring(stud.matrikelnr, 1, 2)::integer";

		if (isset($studiensemester_kurzbz))
		{
			$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
			$prevStudiensemesterRes = $this->_ci->StudiensemesterModel->getPreviousFrom($studiensemester_kurzbz);

			if (isError($prevStudiensemesterRes)) return $prevStudiensemesterRes;

			$semesterArr = array($studiensemester_kurzbz);

			if (hasData($prevStudiensemesterRes))
			{
				// if Studiensemester given, check only if has status in current or previous semester
				$semesterArr[] = getData($prevStudiensemesterRes)[0]->studiensemester_kurzbz;
			}

			$qry .= " AND EXISTS (
						SELECT 1
						FROM public.tbl_prestudentstatus ps
						WHERE studiensemester_kurzbz IN ?
						AND ps.prestudent_id = prestudent.prestudent_id
					)";

			$params[] = $semesterArr;
		}

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
			$qry .= " AND stg.studiengang_kz NOT IN ?";
			$params[] = $exkludierte_studiengang_kz;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
