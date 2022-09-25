<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('IPlausiChecker.php');

/**
 *
 */
class AktSemesterNull implements IPlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		$this->_ci =& get_instance(); // get code igniter instance

		// pass parameters needed for plausicheck
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->_ci->plausichecklib->getAktSemesterNull($studiengang_kz);

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
}
