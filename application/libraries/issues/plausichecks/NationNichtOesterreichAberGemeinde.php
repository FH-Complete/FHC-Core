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

		// pass parameters needed for plausicheck
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;

		// get all students failing the plausicheck
		$personRes = $this->_ci->plausichecklib->getNationNichtOesterreichAberGemeinde($studiengang_kz);

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
}
