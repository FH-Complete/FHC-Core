<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ZGV Datum must be after Geburtsdatum
 */
class CORE_ZGV_0002 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['prestudent_id']) || !is_numeric($params['prestudent_id']))
			return error('Prestudent Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		// get zgvdatum of prestudent
		$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->_ci->PrestudentModel->addSelect('zgvdatum, gebdatum');
		$this->_ci->PrestudentModel->addJoin('public.tbl_person', 'person_id');
		$prestudentRes = $this->_ci->PrestudentModel->load($params['prestudent_id']);

		if (isError($prestudentRes))
			return $prestudentRes;

		if (hasData($prestudentRes))
		{
			$prestudentData = getData($prestudentRes)[0];

			$zgvdatum = $prestudentData->zgvdatum;

			if (isEmptyString($zgvdatum))
				return success(false);

			$gebdatum = $prestudentData->gebdatum;

			if (isEmptyString($gebdatum))
				return success(false);

			// check if zgvdatum comes before geburtsdatum
			if ($zgvdatum < $gebdatum)
				return success(false);
			else
				return success(true);
		}
		else
			return success(false);
	}
}
