<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Aufenthaltsförderung must exist if certain length of outgoing stay is exceeded
 */
class CORE_INOUT_0004 implements IIssueResolvedChecker
{
	public function checkIfIssueIsResolved($params)
	{
		if (!isset($params['bisio_id']) || !is_numeric($params['bisio_id']))
			return error('Bisio Id missing, issue_id: '.$params['issue_id']);

		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->model('codex/Aufenthaltfoerderung_model', 'AufenthaltfoerderungModel');

		// get all Zwecke
		$this->_ci->AufenthaltfoerderungModel->addSelect('tbl_aufenthaltfoerderung.aufenthaltfoerderung_code');
		$this->_ci->AufenthaltfoerderungModel->addJoin('bis.tbl_bisio_aufenthaltfoerderung', 'aufenthaltfoerderung_code');
		$this->_ci->AufenthaltfoerderungModel->addOrder('tbl_aufenthaltfoerderung.aufenthaltfoerderung_code');
		$bisioFoerderungRes = $this->_ci->AufenthaltfoerderungModel->loadWhere(array('bisio_id' => $params['bisio_id']));

		if (isError($bisioFoerderungRes))
			return $bisioFoerderungRes;

		if (hasData($bisioFoerderungRes))
		{
			// resolved if Aufenthaltsfoerderung exists
			return success(true);
		}
		else
		{
			$this->_ci->load->model('codex/Bisio_model', 'BisioModel');

			// get Bisio Aufenthaltsdauer
			$aufenthaltsdauerRes = $this->_ci->BisioModel->getAufenthaltsdauer($params['bisio_id']);

			if (isError($aufenthaltsdauerRes))
				return $aufenthaltsdauerRes;

			if (hasData($aufenthaltsdauerRes))
			{
				$aufenthaltsdauer = getData($aufenthaltsdauerRes);

				// check if stay >= 29 days. If yes and no Aufenthaltsfoerderung - not resolved
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
