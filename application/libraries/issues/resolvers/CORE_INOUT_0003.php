<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Invalid Zweck for incoming
 */
class CORE_INOUT_0003 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['bisio_id']) || !is_numeric($params['bisio_id']))
			return error('Bisio Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->model('codex/Bisiozweck_model', 'BisiozweckModel');

		// get all Zwecke
		$this->_ci->BisiozweckModel->addSelect('zweck_code');
		$bisiozweckRes = $this->_ci->BisiozweckModel->loadWhere(array('bisio_id' => $params['bisio_id']));

		if (isError($bisiozweckRes))
			return $bisiozweckRes;

		if (hasData($bisiozweckRes))
		{
			$bisiozweckData = getData($bisiozweckRes);

			// resolved if Zweck is 1, 2 or 3
			if (count($bisiozweckData) == 1 && !in_array($bisiozweckData[0]->zweck_code, array(1, 2, 3)))
				return success(false);
			else
				return success(true);
		}
		else
			return success(true);
	}
}
