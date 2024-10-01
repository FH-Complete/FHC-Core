<?php

/**
 * Job for producing Plausicheck issues
 */
abstract class PlausiIssueProducer_Controller extends JOB_Controller
{
	protected $_fehlerLibMappings = [];
	protected $_app;

	protected function producePlausicheckIssues($params)
	{
		$this->load->library(
			'issues/PlausicheckProducerLib',
			['extensionName' => $this->_extensionName ?? null, 'app' => $this->_app, 'fehlerLibMappings' => $this->_fehlerLibMappings]
		);

		$this->logInfo("Plausicheck issue producer job started");

		$result = $this->plausicheckproducerlib->producePlausicheckIssues($params);

		// log if error, or log info if inserted new issue
		foreach ($result->errors as $error) $this->logError($error);
		foreach ($result->infos as $info) $this->logInfo($info);

		$this->logInfo("Plausicheck issue producer job stopped");
	}
}
