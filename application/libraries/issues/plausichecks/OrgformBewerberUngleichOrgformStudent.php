<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class OrgformBewerberUngleichOrgformStudent extends PlausiChecker
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
		$prestudentRes = $this->getOrgformBewerberUngleichOrgformStudent(
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
						'studiensemester_kurzbz' => $prestudent->studiensemester_kurzbz,
						'bewerber_studiensemester_kurzbz' => $prestudent->bewerber_studiensemester_kurzbz
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
	 * Orgform of Bewerber should be same as of student (Orgform of Status AND Studienplan)
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getOrgformBewerberUngleichOrgformStudent(
		$studiensemester_kurzbz,
		$studiengang_kz = null,
		$prestudent_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON (prestudent.prestudent_id) prestudent.person_id, prestudent.prestudent_id, students.studiensemester_kurzbz,
				bewerber_status.studiensemester_kurzbz AS bewerber_studiensemester_kurzbz, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudent prestudent
				JOIN public.tbl_prestudentstatus bewerber_status
					ON prestudent.prestudent_id = bewerber_status.prestudent_id AND bewerber_status.status_kurzbz = 'Bewerber'
				JOIN lehre.tbl_studienplan bewerber_studienplan ON bewerber_status.studienplan_id = bewerber_studienplan.studienplan_id
				JOIN (
					SELECT
						DISTINCT ON (prestudent_id) prestudent_id, st.studiensemester_kurzbz,
						pl.orgform_kurzbz AS studienplan_orgform_kurzbz
					FROM
						public.tbl_prestudentstatus st
						JOIN lehre.tbl_studienplan pl USING (studienplan_id)
					WHERE
						status_kurzbz = 'Student'
					ORDER BY
						st.prestudent_id, st.datum, st.insertamum, st.ext_id
				) students ON prestudent.prestudent_id = students.prestudent_id
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				prestudent.bismelden
				AND stg.melderelevant
				AND bewerber_studienplan.orgform_kurzbz <> students.studienplan_orgform_kurzbz";

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
			$qry .= " AND studiengang.studiengang_kz = ?";
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
