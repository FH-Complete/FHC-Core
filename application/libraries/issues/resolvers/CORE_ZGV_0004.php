<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ZGV Datum should not be after ZGV master Datum
 */
class CORE_ZGV_0004 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['prestudent_id']) || !is_numeric($params['prestudent_id']))
			return error('Prestudent Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		// get zgvdatum of prestudent
		$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->_ci->PrestudentModel->addSelect('zgvdatum, zgvmadatum');
		$prestudentRes = $this->_ci->PrestudentModel->load($params['prestudent_id']);

		if (isError($prestudentRes))
			return $prestudentRes;

		if (hasData($prestudentRes))
		{
			$prestudentData = getData($prestudentRes)[0];

			// get and compare zgvdatum and zgvmadatum
			$zgvdatum = $prestudentData->zgvdatum;

			if (isEmptyString($zgvdatum))
				return success(false);

			$zgvmadatum = $prestudentData->zgvmadatum;

			if (isEmptyString($zgvmadatum))
				return success(false);

			// check if zgvmadatum comes after zgvdatum
			if ($zgvmadatum < $zgvdatum)
				return success(false);
			else
				return success(true);
		}
		else
			return success(false);
	}
}
