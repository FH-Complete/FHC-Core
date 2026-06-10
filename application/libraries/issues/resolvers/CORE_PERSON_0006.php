<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Geburtsnation missing
 */
class CORE_PERSON_0006 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['issue_person_id']) || !is_numeric($params['issue_person_id']))
			return error('Person Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->model('codex/Uhstat1daten_model', 'UhstatModel');

		$personRes = $this->_ci->UhstatModel->getUHSTAT1PersonData([$params['issue_person_id']]);

		if (isError($personRes)) return $personRes;

		if (hasData($personRes))
		{
			// get person data
			$personData = getData($personRes)[0];

			// if person identification data present, issue is resolved
			return success(
				!isEmptyString($personData->ersatzkennzeichen)
				|| (!isEmptyString($personData->vbpkAs) && !isEmptyString($personData->vbpkBf))
			);
		}
		else
			return success(false); // if no person found, not resolved
	}
}