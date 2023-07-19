<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class NationNichtOesterreichAberGemeinde extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;

		// get all students failing the plausicheck
		$personRes = $this->getNationNichtOesterreichAberGemeinde(
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
					'fehlertext_params' => array('gemeinde' => $person->gemeinde, 'adresse_id' => $person->adresse_id),
					'resolution_params' => array('adresse_id' => $person->adresse_id)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Nation is not Austria, but address has austrian Gemeinde.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param person_id int if check is to be executed only for one person
	 * @return success with prestudents or error
	 */
	public function getNationNichtOesterreichAberGemeinde($studiengang_kz = null, $person_id = null, $exkludierte_studiengang_kz = null)
	{
		$params = array();

		$qry = "SELECT DISTINCT tbl_person.person_id, adr.gemeinde, adr.adresse_id
				FROM
					public.tbl_adresse adr
					JOIN public.tbl_prestudent USING(person_id)
					JOIN public.tbl_person USING(person_id)
					JOIN public.tbl_student USING(prestudent_id)
					JOIN public.tbl_benutzer ON(uid=student_uid)
					JOIN public.tbl_studiengang stg ON tbl_prestudent.studiengang_kz = stg.studiengang_kz
				WHERE
					adr.nation!='A'
					AND tbl_benutzer.aktiv
					AND gemeinde NOT IN ('Münster')
					AND EXISTS(SELECT 1 FROM bis.tbl_gemeinde WHERE name = adr.gemeinde)";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($person_id))
		{
			$qry .= " AND tbl_person.person_id = ?";
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
