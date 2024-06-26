<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class FalscheStatusabfolgeVorStudentstatus extends PlausiChecker
{
	private $_statusAbfolge = array('Interessent', 'Bewerber', 'Aufgenommener', 'Student');

	public function executePlausiCheck($params)
	{
		$studiensemester_kurzbz = isset($params['studiensemester_kurzbz']) ? $params['studiensemester_kurzbz'] : null;

		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiensemester_kurzbz = isset($params['studiensemester_kurzbz']) ? $params['studiensemester_kurzbz'] : null;
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;
		$prestudent_id = isset($params['prestudent_id']) ? $params['prestudent_id'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->getFalscheStatusabfolgeVorStudentstatus(
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
						'prestudent_id' => $prestudent->prestudent_id,
						'studiensemester_kurzbz' => $prestudent->studiensemester_kurzbz,
						'status_abfolge' => implode(', ', $this->_statusAbfolge)
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
	 * There should be certain order of status before the student status.
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
		$params = array($this->_statusAbfolge);

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
					status.status_kurzbz IN ?
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
			(";

		foreach ($this->_statusAbfolge as $idx => $status_kurzbz)
		{
			// previous status should be either same status, or previous status, or null in case of first status
			if ($idx != 0)$qry .= " OR ";
			$qry .= " (status_kurzbz = ? AND prev_status_kurzbz NOT IN ?";
			$prev_status_kurzbz = array($status_kurzbz);

			if ($idx == 0)
				$qry .= " AND prev_status_kurzbz IS NOT NULL";
			else
				$prev_status_kurzbz[] = $this->_statusAbfolge[$idx - 1];

			$qry .= ')';

			$params[] = $status_kurzbz;
			$params[] = $prev_status_kurzbz;
		}

		$qry .= "
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
