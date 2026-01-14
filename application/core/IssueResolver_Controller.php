<?php

/**
 * Controller for retrieving open issues and, if the issue condition is not met anymore, automatically set it to resolved
 */
abstract class IssueResolver_Controller extends JOB_Controller
{
	// codes of fehler to be resolved
	protected $fehlercodes = [];

	public function __construct()
	{
		parent::__construct();

		// pass extension name if calling from extension
		$this->load->model('system/Issue_model', 'IssueModel');
	}

	/**
	 * Initializes issue resolution.
	 */
	public function run()
	{
		// initialize librariy with provided fehlercodes
		$this->load->library(
			'issues/PlausicheckResolverLib',
			[
				'fehlercodes' => $this->_fehlercodes
			]
		);

		$this->logInfo("Issue resolve job started");

		// resolve the issues
		$result = $this->plausicheckresolverlib->resolvePlausicheckIssues();

		// log if error, or log info if inserted new issue
		foreach ($result->errors as $error) $this->logError($error);
		foreach ($result->infos as $info) $this->logInfo($info);

		$this->logInfo("Issue resolve job ended");
	}
}
