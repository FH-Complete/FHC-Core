<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class FalscheAnzahlAbschlusspruefungen extends PlausiChecker
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
		$prestudentRes = $this->getFalscheAnzahlAbschlusspruefungen(
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
					'fehlertext_params' => array('prestudent_id' => $prestudent->prestudent_id),
					'resolution_params' => array('prestudent_id' => $prestudent->prestudent_id)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Students with finished studies should have exactly one final exam.
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getFalscheAnzahlAbschlusspruefungen(
		$studiensemester_kurzbz = null,
		$studiengang_kz = null,
		$prestudent_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array();

		$qry = "
			SELECT * FROM (
				SELECT
					DISTINCT ON(pre.prestudent_id) pre.person_id, pre.prestudent_id, student_uid, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz,
					(
						SELECT COUNT(*)
						FROM lehre.tbl_abschlusspruefung
						WHERE student_uid = stud.student_uid
						AND abschlussbeurteilung_kurzbz != 'nicht'
						AND abschlussbeurteilung_kurzbz IS NOT NULL
					) AS anzahl_abschlusspruefungen
				FROM
					public.tbl_prestudent pre
					JOIN public.tbl_student stud USING(prestudent_id)
					JOIN public.tbl_prestudentstatus status USING(prestudent_id)
					JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				WHERE
					status_kurzbz = 'Absolvent'
					AND pre.bismelden
					AND stg.melderelevant
					AND NOT EXISTS ( /* exclude gs */
						SELECT 1
						FROM bis.tbl_mobilitaet
						WHERE prestudent_id = pre.prestudent_id
						AND studiensemester_kurzbz = status.studiensemester_kurzbz
					)";

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

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		if (isset($exkludierte_studiengang_kz) && !isEmptyArray($exkludierte_studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz NOT IN ?";
			$params[] = $exkludierte_studiengang_kz;
		}

		$qry .= ") studenten
			WHERE anzahl_abschlusspruefungen != 1";

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
