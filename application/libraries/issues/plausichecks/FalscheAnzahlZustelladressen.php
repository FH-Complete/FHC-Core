<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class FalscheAnzahlZustelladressen extends PlausiChecker
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
		$personRes = $this->getFalscheAnzahlZustelladressen(
			$studiensemester_kurzbz,
			$studiengang_kz,
			null,
			$exkludierte_studiengang_kz
		);

		if (isError($personRes)) return $personRes;

		if (hasData($personRes))
		{
			$persons = getData($personRes);

			// populate results with data necessary for writing issues
			foreach ($persons as $person)
			{
				$results[] = array(
					'person_id' => $person->person_id,
					'resolution_params' => array('person_id' => $person->person_id)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Students should have exactly one delivery address.
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param person_id int if check is to be executed only for one person
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getFalscheAnzahlZustelladressen(
		$studiensemester_kurzbz = null,
		$studiengang_kz = null,
		$person_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array();

		$qry = "
			SELECT
				DISTINCT person_id
			FROM
				(
					SELECT person_id, COUNT(adresse_id) AS anzahl_adressen
					FROM public.tbl_adresse addr
					WHERE zustelladresse IS TRUE
					GROUP BY person_id
				) adressen
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudent pre USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_student USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				anzahl_adressen != 1
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

		if (isset($person_id))
		{
			$qry .= " AND person_id = ?";
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
