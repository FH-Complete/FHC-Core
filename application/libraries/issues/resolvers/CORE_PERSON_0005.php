<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Geburtsnation missing
 */
class CORE_PERSON_0005 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['issue_person_id']) || !is_numeric($params['issue_person_id']))
			return error('Person Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->model('person/Person_model', 'PersonModel');

		// load geburtsnation for the given person
		$this->_ci->PersonModel->addSelect('geburtsnation');
		$personRes = $this->_ci->PersonModel->load($params['issue_person_id']);

		if (isError($personRes)) return $personRes;

		if (hasData($personRes))
		{
			// get person data
			$personData = getData($personRes)[0];

			// if geburtsnation present, issue is resolved
			return success(!isEmptyString($personData->geburtsnation));
		}
		else
			return success(false); // if no person found, not resolved
	}
}