<?php

/**
 * More than one Zweck for incoming
 */
class CORE_INOUT_0002 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['bisio_id']) || !is_numeric($params['bisio_id']))
			return error('Bisio Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->model('codex/Bisiozweck_model', 'BisiozweckModel');

		// get all bisio Zwecke
		$this->_ci->BisiozweckModel->addSelect('1');
		$bisiozweckRes = $this->_ci->BisiozweckModel->loadWhere(array('bisio_id' => $params['bisio_id']));

		if (isError($bisiozweckRes))
			return $bisiozweckRes;

		if (hasData($bisiozweckRes))
		{
			if (count(getData($bisiozweckRes)) <= 1) // resolved if one bisio Zweck
				return success(true);
			else
				return success(false); // otherwise not resolved
		}
		else
			return success(true); // resolved if no bisio zweck
	}
}
