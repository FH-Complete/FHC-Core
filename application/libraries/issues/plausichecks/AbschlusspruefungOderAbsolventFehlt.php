<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class AbschlusspruefungOderAbsolventFehlt extends PlausiChecker
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
		$prestudentRes = $this->getAbschlusspruefungOderAbsolventFehlt($studiensemester_kurzbz, $studiengang_kz, null, $exkludierte_studiengang_kz);

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
	 * If there is a final exam, there should be an absolvent status (and vice versa).
	 * @param studiensemester_kurzbz Status from this and previous semester is checked
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getAbschlusspruefungOderAbsolventFehlt($studiensemester_kurzbz = null, $studiengang_kz = null, $prestudent_id = null, $exkludierte_studiengang_kz = null)
	{
		$params = array();


		$qry = "
				SELECT  person_id, prestudent_id, studiengang_kz, prestudent_stg_oe_kurzbz FROM (
					WITH meldestichtag AS (
						SELECT
							meldestichtag, studiensemester_kurzbz
						FROM
							bis.tbl_bismeldestichtag";

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

			$qry .= " WHERE studiensemester_kurzbz IN ?";

			$params[] = $semesterArr;
		}

			$qry .= "
					)
					SELECT
						DISTINCT ON (prestudent_id) prestudent.person_id, prestudent.prestudent_id,
						stg.studiengang_kz, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz,
						EXISTS (
							SELECT 1
							FROM
								lehre.tbl_abschlusspruefung pr
							WHERE
								student_uid = benutzer.uid
								AND abschlussbeurteilung_kurzbz <> 'nicht'
								AND pr.datum < meldestichtag.meldestichtag
						) AS hat_pruefung,
						(status.status_kurzbz IS NOT NULL) AS hat_absolvent,
						(status.ausbildungssemester = stg.max_semester) AS absolvent_in_letztem_semester
					FROM
						meldestichtag
						CROSS JOIN public.tbl_student student
						JOIN public.tbl_prestudent prestudent USING (prestudent_id)
						JOIN public.tbl_benutzer benutzer ON (benutzer.uid = student.student_uid)
						JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
						LEFT JOIN public.tbl_prestudentstatus status
							ON status.prestudent_id = prestudent.prestudent_id
							AND status.status_kurzbz = 'Absolvent'
							AND status.datum < meldestichtag.meldestichtag
						WHERE EXISTS (
							SELECT 1
							FROM
								public.tbl_prestudentstatus
							WHERE
								prestudent_id = prestudent.prestudent_id
								AND studiensemester_kurzbz = meldestichtag.studiensemester_kurzbz
						)";

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

		$qry .=	"
				) prestudenten
				WHERE
					(hat_absolvent AND absolvent_in_letztem_semester AND hat_pruefung = FALSE)
					OR (hat_pruefung AND hat_absolvent = FALSE)";

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
