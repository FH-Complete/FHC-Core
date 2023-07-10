<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Date of sponsion shouldn't be missing for Absolvent.
 */
class CORE_STUDENTSTATUS_0011 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['abschlusspruefung_id']) || !is_numeric($params['abschlusspruefung_id']))
			return error('Prestudent Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->library('issues/plausichecks/DatumSponsionFehlt');

		// check if issue persists
		$checkRes = $this->_ci->datumsponsionfehlt->getDatumSponsionFehlt(null, null, $params['abschlusspruefung_id']);

		if (isError($checkRes)) return $checkRes;

		if (hasData($checkRes))
			return success(false); // not resolved if issue is still present
		else
			return success(true); // resolved otherwise
	}
}
