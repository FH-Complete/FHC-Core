<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Birth date of person shouldn't be too long ago.
 */
class CORE_PERSON_0001 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['issue_person_id']) || !is_numeric($params['issue_person_id']))
			return error('Person Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->library('issues/plausichecks/GbDatumWeitZurueck');

		// check if issue persists
		$checkRes = $this->_ci->gbdatumweitzurueck->getGbDatumWeitZurueck(null, null, $params['issue_person_id']);

		if (isError($checkRes)) return $checkRes;

		if (hasData($checkRes))
			return success(false); // not resolved if issue is still present
		else
			return success(true); // resolved otherwise
	}
}
