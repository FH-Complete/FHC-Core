<?php

/**
 * Controller for retrieving open issues and, if the issue condition is not met anymore, automatically set it to resolved
 */
abstract class IssueResolver_Controller extends JOB_Controller
{
	const ISSUES_FOLDER = 'issues';

	protected $_codeLibMappings;

	public function __construct()
	{
		parent::__construct();

		$this->load->model('system/Issue_model', 'IssueModel');

		$this->load->library('IssuesLib');
	}

	/**
	 * Initializes issue resolution.
	 */
	public function run()
	{
		$this->logInfo("Issue resolve job started");

		// load open issues with given errorcodes
		$openIssuesRes = $this->IssueModel->getOpenIssues(array_keys($this->_codeLibMappings));

		// log error if occured
		if (isError($openIssuesRes))
		{
			$this->logError(getError($openIssuesRes));
			return;
		}

		// log info if no data found
		if (!hasData($openIssuesRes))
		{
			$this->logInfo("No open issues found");
			return;
		}

		$openIssues = getData($openIssuesRes);

		foreach ($openIssues as $issue)
		{
			if (isset($this->_codeLibMappings[$issue->fehlercode]))
			{
				$libName = $this->_codeLibMappings[$issue->fehlercode];

				// add person id and oe kurzbz automatically as params, merge it with additional params
				// decode bewerbung_parameter into assoc array
				$params = array_merge(
					array('issue_id' => $issue->issue_id, 'issue_person_id' => $issue->person_id, 'issue_oe_kurzbz' => $issue->oe_kurzbz),
					isset($issue->behebung_parameter) ? json_decode($issue->behebung_parameter, true) : array()
				);

				// if called from extension (extension name set), path includes extension names, otherwiese it is the core library folder
				$libPath = isset($this->_extensionName) ? 'extensions/'.$this->_extensionName.'/'.self::ISSUES_FOLDER.'/' : self::ISSUES_FOLDER.'/';
				// load library connected to fehlercode
				$this->load->library(
					$libPath.$libName
				);

				$lowercaseLibName = mb_strtolower($libName);

				// call the function for checking if issue is resolved
				$issueResolvedRes = $this->{$lowercaseLibName}->checkIfIssueIsResolved($params);

				if (isError($issueResolvedRes))
				{
					$this->logError(getError($issueResolvedRes));
				}
				else
				{
					$issueResolvedData = getData($issueResolvedRes);

					if ($issueResolvedData === true)
					{
						// set issue to resolved if needed
						$behobenRes = $this->issueslib->setBehoben($issue->issue_id, null);

						if (isError($behobenRes))
							$this->logError(getError($behobenRes));
						else
							$this->logInfo("Issue " . $issue->issue_id . " successfully resolved");
					}
				}
			}
		}

		$this->logInfo("Issue resolve job ended");
	}
}
