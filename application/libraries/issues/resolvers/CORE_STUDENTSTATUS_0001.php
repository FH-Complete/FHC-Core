<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Abbrecher cannot be active.
 */
class CORE_STUDENTSTATUS_0001 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['prestudent_id']) || !is_numeric($params['prestudent_id']))
			return error('Prestudent Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->library('issues/plausichecks/AbbrecherAktiv');

		// check if issue persists
		$checkRes = $this->_ci->abbrecheraktiv->getAbbrecherAktiv(null, $params['prestudent_id']);

		if (isError($checkRes)) return $checkRes;

		if (hasData($checkRes))
			return success(false); // not resolved if issue is still present
		else
			return success(true); // resolved otherwise
	}
}
