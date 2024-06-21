<?php

/**
 * Controller for retrieving open issues and, if the issue condition is not met anymore, automatically set it to resolved
 */
abstract class IssueResolver_Controller extends JOB_Controller
{
	protected $_codeLibMappings;

	public function __construct()
	{
		parent::__construct();

		// pass extension name if calling from extension
		$extensionName = isset($this->_extensionName) ? $this->_extensionName : null;

		$this->load->model('system/Issue_model', 'IssueModel');

		$this->load->library('issues/PlausicheckResolverLib', ['extensionName' => $extensionName]);
	}

	/**
	 * Initializes issue resolution.
	 */
	public function run()
	{
		$this->logInfo("Issue resolve job started");

		// load open issues with given errorcodes
		$openIssuesRes = $this->IssueModel->getOpenIssues(array_keys($this->_codeLibMappings));

		$openIssues = hasData($openIssuesRes) ? getData($openIssuesRes) : [];

		$result = $this->plausicheckresolverlib->resolvePlausicheckIssues($this->_codeLibMappings, $openIssues);

		// log if error, or log info if inserted new issue
		foreach ($result->errors as $error) $this->logError($error);
		foreach ($result->infos as $info) $this->logInfo($info);

		$this->logInfo("Issue resolve job ended");
	}
}
