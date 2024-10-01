<?php

/**
 * Controller for retrieving open issues and, if the issue condition is not met anymore, automatically set it to resolved
 */
abstract class IssueResolver_Controller extends JOB_Controller
{
	// mappings in form fehlercode -> resolverlibrary name, fehler which have explicit resolver class defined
	protected $_codeLibMappings = [];

	// mappings in form fehlercode -> producer library name, fehler which are resolved the same way they are produced
	protected $_codeProducerLibMappings = [];

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
		$this->load->library(
			'issues/PlausicheckResolverLib',
			[
				'extensionName' => $this->_extensionName ?? null,
				'codeLibMappings' => $this->_codeLibMappings,
				'codeProducerLibMappings' => $this->_codeProducerLibMappings
			]
		);

		$this->logInfo("Issue resolve job started");

		// load open issues with given errorcodes
		$openIssuesRes = $this->IssueModel->getOpenIssues(
			array_merge(array_keys($this->_codeLibMappings), array_keys($this->_codeProducerLibMappings))
		);

		$openIssues = hasData($openIssuesRes) ? getData($openIssuesRes) : [];

		$result = $this->plausicheckresolverlib->resolvePlausicheckIssues($openIssues);

		// log if error, or log info if inserted new issue
		foreach ($result->errors as $error) $this->logError($error);
		foreach ($result->infos as $info) $this->logInfo($info);

		$this->logInfo("Issue resolve job ended");
	}
}
