<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Studiengang should be the same for prestudent and studienplan.
 */
class CORE_STG_0004 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['prestudent_id']) || !is_numeric($params['prestudent_id']))
			return error('Prestudent Id missing, issue_id: '.$params['issue_id']);

		if (!isset($params['studienordnung_id']) || !is_numeric($params['studienordnung_id']))
			return error('Studienordnung Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->library('issues/plausichecks/StgPrestudentUngleichStgStudienplan');

		// check if issue persists
		$checkRes = $this->_ci->stgprestudentungleichstgstudienplan->getStgPrestudentUngleichStgStudienplan(null, $params['prestudent_id'], $params['studienordnung_id']);

		if (isError($checkRes)) return $checkRes;

		if (hasData($checkRes))
			return success(false); // not resolved if issue is still present
		else
			return success(true); // resolved otherwise
	}
}
