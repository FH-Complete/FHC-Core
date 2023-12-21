<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class OrgformStgUngleichOrgformPrestudent extends PlausiChecker
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
		$prestudentRes = $this->getOrgformStgUngleichOrgformPrestudent(
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
						'student_studiengang' => $prestudent->student_studiengang,
						'student_orgform' => $prestudent->student_orgform,
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
	 * Orgform of a Studiengang in Studienplan should be the same as orgform of student.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getOrgformStgUngleichOrgformPrestudent(
		$studiensemester_kurzbz,
		$studiengang_kz = null,
		$prestudent_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				prestudent.person_id, prestudent.prestudent_id, status.studiensemester_kurzbz,
				studiengang.orgform_kurzbz AS stg_orgform, status.orgform_kurzbz AS student_orgform,
				prestudent.studiengang_kz AS student_studiengang, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_studiengang studiengang
				JOIN public.tbl_student student USING(studiengang_kz)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_benutzer benutzer on(benutzer.uid = student.student_uid)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
				LEFT JOIN lehre.tbl_studienplan stpl USING (studienplan_id)
			WHERE
				benutzer.aktiv = true
				AND status.status_kurzbz IN ('Student', 'Unterbrecher', 'Abbrecher', 'Diplomand', 'Absolvent')
				AND studiengang.studiengang_kz < 10000
				AND status.studiensemester_kurzbz = ?
				AND NOT (status.orgform_kurzbz IS NULL AND studiengang.mischform = FALSE)
				AND NOT EXISTS(
					SELECT 1
					FROM
						lehre.tbl_studienplan
					JOIN
						lehre.tbl_studienordnung USING(studienordnung_id)
					WHERE
						tbl_studienplan.studienplan_id = stpl.studienplan_id
						AND tbl_studienordnung.studiengang_kz = prestudent.studiengang_kz
						AND tbl_studienplan.orgform_kurzbz = status.orgform_kurzbz)";

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

		$qry .= "
			ORDER BY student_uid";

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
