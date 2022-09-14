<?php

/**
 * Controller for writing (producing) issues if the issue condition is met
 */
abstract class IssueProducer_Controller extends JOB_Controller
{
	const ISSUES_FOLDER = 'issues';
	const CHECK_ISSUE_EXISTS_METHOD_NAME = 'checkIfIssueExists';

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
		$this->logInfo("Issue producer job started");

		foreach ($this->_codeLibMappings as $fehlercode => $library)
		{
			// add person id and oe kurzbz automatically as params, merge it with additional params
			// decode bewerbung_parameter into assoc array
			$params = array_merge(
				array('issue_id' => $issue->issue_id, 'issue_person_id' => $issue->person_id, 'issue_oe_kurzbz' => $issue->oe_kurzbz),
				isset($issue->behebung_parameter) ? json_decode($issue->behebung_parameter, true) : array()
			);

			// if called from extension (extension name set), path includes extension names, otherwise it is the core library folder
			$libRootPath = isset($this->_extensionName) ? 'extensions/' . $this->_extensionName . '/' : '';
			$issuesLibPath = $libRootPath . self::ISSUES_FOLDER . '/';
			$issuesLibFilePath = DOC_ROOT . 'application/' . $libRootPath . 'libraries/' . self::ISSUES_FOLDER . '/' . $libName . '.php';

			// check if library file exists
			if (!file_exists($issuesLibFilePath))
			{
				// log error and continue with next issue if not
				$this->logError("Issue library file " . $issuesLibFilePath . " does not exist");
				continue;
			}

			// load library connected to fehlercode
			$this->load->library(
				$issuesLibPath . $libName
			);

			$lowercaseLibName = mb_strtolower($libName);

			// check if method is defined in libary class
			if (!is_callable(array($this->{$lowercaseLibName}, self::CHECK_ISSUE_EXISTS_METHOD_NAME)))
			{
				// log error and continue with next issue if not
				$this->logError("Method " . self::CHECK_ISSUE_EXISTS_METHOD_NAME . " is not defined in library $lowercaseLibName");
				continue;
			}

			// call the function for checking for issue resolution
			$issueResolvedRes = $this->{$lowercaseLibName}->{self::CHECK_ISSUE_EXISTS_METHOD_NAME}($params);

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

		$this->logInfo("Issue resolve job ended");
	}
}
