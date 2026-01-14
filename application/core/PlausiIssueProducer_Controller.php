<?php

/**
 * Job for producing Plausicheck issues
 */
abstract class PlausiIssueProducer_Controller extends JOB_Controller
{
	protected $_fehlerKurzbz = [];
	protected $_apps;

	protected function producePlausicheckIssues($params)
	{
		$this->load->library(
			'issues/PlausicheckProducerLib',
			['apps' => $this->_apps, 'fehlerKurzbz' => $this->_fehlerKurzbz]
		);

		$this->logInfo("Plausicheck issue producer job started");

		$result = $this->plausicheckproducerlib->producePlausicheckIssues($params);

		// log if error, or log info if inserted new issue
		foreach ($result->errors as $error) $this->logError($error);
		foreach ($result->infos as $info) $this->logInfo($info);

		$this->logInfo("Plausicheck issue producer job stopped");
	}
}
