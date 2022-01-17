<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ZGV master Datum in future
 */
class CORE_ZGV_0003 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['prestudent_id']) || !is_numeric($params['prestudent_id']))
			return error('Prestudent Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		// get zgvdatum of prestudent
		$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->_ci->PrestudentModel->addSelect('zgvmadatum');
		$prestudentRes = $this->_ci->PrestudentModel->load($params['prestudent_id']);

		if (isError($prestudentRes))
			return $prestudentRes;

		if (hasData($prestudentRes))
		{
			$zgvdatum = getData($prestudentRes)[0]->zgvmadatum;

			if (isEmptyString($zgvdatum))
				return success(false);

			// check if zgvdatum comes after today
			if ($zgvdatum > date('Y-m-d'))
				return success(false);
			else
				return success(true);
		}
		else
			return success(false);
	}
}
