<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class UhstatPersonkennungFehltCore extends PlausiChecker
{
	const PERSONKENNUNG_KENNZEICHEN_TYPEN = ['vbpkAs', 'vbpkBf'];

	public function executePlausiCheck($params)
	{
		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiensemester_kurzbz = isset($params['studiensemester_kurzbz']) ? $params['studiensemester_kurzbz'] : null;
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;
		$person_id = isset($params['person_id']) ? $params['person_id'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->_getUhstatPersonkennungFehlt(
			$studiensemester_kurzbz,
			$studiengang_kz,
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
					'person_id' => $prestudent->person_id
					//'fehlertext_params' => array('person_id' => $prestudent->person_id),
					//'resolution_params' => array('person_id' => $prestudent->person_id)
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
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	private function _getUhstatPersonkennungFehlt(
		$studiensemester_kurzbz = null,
		$studiengang_kz = null,
		$person_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON (person_id) prestudent_id, person_id
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_person pers USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				stg.melderelevant
				AND pre.bismelden
				AND EXISTS (SELECT 1 FROM bis.tbl_uhstat1daten WHERE person_id = pers.person_id)
				AND (SELECT COUNT (DISTINCT kennzeichentyp_kurzbz) FROM public.tbl_kennzeichen WHERE person_id = pers.person_id AND kennzeichentyp_kurzbz IN ('vbpkAs', 'vbpkBf')) < 2
				AND pers.ersatzkennzeichen IS NULL";

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

		if (isset($person_id))
		{
			$qry .= " AND pers.person_id = ?";
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
