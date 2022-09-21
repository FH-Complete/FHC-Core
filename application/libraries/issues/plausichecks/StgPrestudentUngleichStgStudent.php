<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('IPlausiChecker.php');

/**
 *
 */
class StgPrestudentUngleichStgStudent implements IPlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->library('issues/PlausicheckLib'); // load plausicheck library

		// get all students failing the plausicheck
		$prestudentRes = $this->_ci->plausichecklib->getPrestudentenStgUngleichStgStudent();

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

		return success($results);
	}
}
