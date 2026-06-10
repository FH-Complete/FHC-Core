<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class IncomingHeimatNationOesterreich extends PlausiChecker
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
		$personRes = $this->getIncomingHeimatNationOesterreich(
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
					'resolution_params' => array('person_id' => $person->person_id, 'studiensemester_kurzbz' => $studiensemester_kurzbz)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Incoming shouldn't have austrian home address.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param person_id int if check is to be executed only for one person
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getIncomingHeimatNationOesterreich(
		$studiensemester_kurzbz,
		$studiengang_kz = null,
		$person_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT pers.person_id, status.studiensemester_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_person pers USING(person_id)
				JOIN public.tbl_adresse addr USING(person_id)
				JOIN public.tbl_studiengang stg USING(studiengang_kz)
			WHERE
				status.status_kurzbz = 'Incoming'
				AND addr.nation = 'A'
				AND addr.heimatadresse
				AND status.studiensemester_kurzbz = ?
				AND stg.melderelevant
				AND pre.bismelden";

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
