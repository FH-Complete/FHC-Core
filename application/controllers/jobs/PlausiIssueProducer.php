<?php

/**
 * Job for producing core Plausicheck issues
 */
class PlausiIssueProducer extends PlausiIssueProducer_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->library('issues/PlausicheckDefinitionLib');

		// set fehler which can be produced by the job
		// structure: fehler_kurzbz => class (library) name for resolving
		$this->_fehlerLibMappings = $this->plausicheckdefinitionlib->getFehlerLibMappings();
	}
}
