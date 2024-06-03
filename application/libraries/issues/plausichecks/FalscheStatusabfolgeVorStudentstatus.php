<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class FalscheStatusabfolgeVorStudentstatus extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$studiensemester_kurzbz = isset($params['studiensemester_kurzbz']) ? $params['studiensemester_kurzbz'] : null;

		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiensemester_kurzbz = isset($params['studiensemester_kurzbz']) ? $params['studiensemester_kurzbz'] : null;
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->getFalscheStatusabfolgeVorStudentstatus(
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
	 * Bewerber should have participated in Reihungstest.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getFalscheStatusabfolgeVorStudentstatus(
		$studiensemester_kurzbz = null,
		$studiengang_kz = null,
		$prestudent_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array();

		$qry = "
			SELECT
				DISTINCT person_id, prestudent_id, prestudent_stg_oe_kurzbz, studiensemester_kurzbz
			FROM
			(
				SELECT
					prestudent.person_id, prestudent.prestudent_id,
					stg.studiengang_kz, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz,
					status.status_kurzbz, status.datum, status.insertamum, status.ext_id,
					LAG(status.status_kurzbz, 1)
						OVER (
							PARTITION BY prestudent.prestudent_id
							ORDER BY status.datum, status.insertamum, status.ext_id
						) AS prev_status_kurzbz
				FROM
					public.tbl_prestudent prestudent
					JOIN public.tbl_prestudentstatus status ON (prestudent.prestudent_id=status.prestudent_id)
					JOIN public.tbl_person USING(person_id)
					LEFT JOIN bis.tbl_orgform USING(orgform_kurzbz)
					JOIN public.tbl_studiengang stg USING(studiengang_kz)
				WHERE
					status.status_kurzbz IN ('Interessent', 'Bewerber', 'Aufgenommener', 'Student')
					AND stg.melderelevant
					AND prestudent.bismelden
					-- there should be a student already
					AND EXISTS (
						SELECT 1
						FROM
							public.tbl_prestudentstatus
						WHERE
							prestudent_id = prestudent.prestudent_id
							AND status_kurzbz = 'Student'
							AND datum >= status.datum
					)
			) prestudents
			WHERE
			(
				-- incorrect order
				(status_kurzbz = 'Interessent' AND prev_status_kurzbz NOT IN ('Interessent') AND prev_status_kurzbz IS NOT NULL)
				OR (
					(status_kurzbz <> 'Interessent' AND prev_status_kurzbz IS NULL)
					OR (status_kurzbz = 'Bewerber' AND prev_status_kurzbz NOT IN ('Bewerber', 'Interessent'))
					OR (status_kurzbz = 'Aufgenommener' AND prev_status_kurzbz NOT IN ('Aufgenommener', 'Bewerber'))
					OR (status_kurzbz = 'Student' AND prev_status_kurzbz NOT IN ('Student', 'Aufgenommener'))
				)
			)";

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
						AND ps.prestudent_id = prestudents.prestudent_id
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

		$qry .= " ORDER BY person_id DESC, prestudent_id DESC, prestudent_stg_oe_kurzbz, studiensemester_kurzbz";

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
