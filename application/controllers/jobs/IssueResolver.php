<?php

/**
 * Job for resolving core issues
 */
class IssueResolver extends IssueResolver_Controller
{
	public function __construct()
	{
		parent::__construct();

		// set fehler codes which can be resolved by the job
		// structure: fehlercode => class (library) name for resolving
		$this->_codeLibMappings = array(
			'CORE_ZGV_0001' => 'CORE_ZGV_0001',
			'CORE_ZGV_0002' => 'CORE_ZGV_0002',
			'CORE_ZGV_0003' => 'CORE_ZGV_0003',
			'CORE_ZGV_0004' => 'CORE_ZGV_0004',
			'CORE_ZGV_0005' => 'CORE_ZGV_0005',
			'CORE_INOUT_0001' => 'CORE_INOUT_0001',
			'CORE_INOUT_0002' => 'CORE_INOUT_0002',
			'CORE_INOUT_0003' => 'CORE_INOUT_0003',
			'CORE_INOUT_0004' => 'CORE_INOUT_0004',
			'CORE_INOUT_0005' => 'CORE_INOUT_0005',
			'CORE_INOUT_0006' => 'CORE_INOUT_0006'
		);
	}
}
