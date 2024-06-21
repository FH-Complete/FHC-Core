<?php

/**
 * Job for producing Plausicheck issues
 */
abstract class PlausiIssueProducer_Controller extends JOB_Controller
{
	protected $_fehlerLibMappings;
	protected $_app;

	public function __construct($app = null)
	{
		parent::__construct();

		// pass extension name if calling from extension
		$extensionName = isset($this->_extensionName) ? $this->_extensionName : null;

		// load libraries
		$this->load->library('issues/PlausicheckProducerLib', array('extensionName' => $extensionName, 'app' => $this->_app));
	}

	protected function producePlausicheckIssues($params)
	{
		$this->logInfo("Plausicheck issue producer job started");

		$result = $this->plausicheckproducerlib->producePlausicheckIssues($this->_fehlerLibMappings, $params);

		// log if error, or log info if inserted new issue
		foreach ($result->errors as $error) $this->logError($error);
		foreach ($result->infos as $info) $this->logInfo($info);

		$this->logInfo("Plausicheck issue producer job stopped");
	}
}
