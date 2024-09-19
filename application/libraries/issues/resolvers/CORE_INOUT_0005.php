<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ECTS angerechnet must exist for outgoing if longer stay
 */
class CORE_INOUT_0005 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['bisio_id']) || !is_numeric($params['bisio_id']))
			return error('Bisio Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->model('codex/Bisio_model', 'BisioModel');

		// get all Zwecke
		$this->_ci->BisioModel->addSelect('ects_angerechnet');
		$bisioRes = $this->_ci->BisioModel->loadWhere(array('bisio_id' => $params['bisio_id']));

		if (isError($bisioRes))
			return $bisioRes;

		if (hasData($bisioRes) && !isEmptyString(getData($bisioRes)[0]->ects_angerechnet))
		{
			// resolved if ects exists
			return success(true);
		}
		else
		{
			// get Bisio Aufenthaltsdauer
			$aufenthaltsdauerRes = $this->_ci->BisioModel->getAufenthaltsdauer($params['bisio_id']);

			if (isError($aufenthaltsdauerRes))
				return $aufenthaltsdauerRes;

			if (hasData($aufenthaltsdauerRes))
			{
				$aufenthaltsdauer = getData($aufenthaltsdauerRes);

				// check if stay >= 29 days. If yes and no ects - not resolved
				if ($aufenthaltsdauer >= 29)
					return success(false);
				else
					return success(true);
			}
			else // no Aufenthaltsdauer - not resolved
				return success(false);
		}
	}
}
