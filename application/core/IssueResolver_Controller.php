<?php

/**
 * Controller for retrieving open issues and, if the issue condition is not met anymore, automatically set it to resolved
 */
abstract class IssueResolver_Controller extends JOB_Controller
{
	const CI_PATH = 'application';
	const ISSUE_RESOLVERS_FOLDER = 'issues/resolvers';
	const CHECK_ISSUE_RESOLVED_METHOD_NAME = 'checkIfIssueIsResolved';

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
		}
		else
		{
			// log info if no data found
			if (!hasData($openIssuesRes))
			{
				$this->logInfo("No open issues found");
			}
			else
			{
				$openIssues = getData($openIssuesRes);

				foreach ($openIssues as $issue)
				{
					// ignore if Fehlercode is not in libmappings (shouldn't be checked)
					if (!isset($this->_codeLibMappings[$issue->fehlercode])) continue;

					$libName = $this->_codeLibMappings[$issue->fehlercode];

					// add person id and oe kurzbz automatically as params, merge it with additional params
					// decode bewerbung_parameter into assoc array
					$params = array_merge(
						array('issue_id' => $issue->issue_id, 'issue_person_id' => $issue->person_id, 'issue_oe_kurzbz' => $issue->oe_kurzbz),
						isset($issue->behebung_parameter) ? json_decode($issue->behebung_parameter, true) : array()
					);

					// if called from extension (extension name set), path includes extension names, otherwise it is the core library folder
					$libRootPath = isset($this->_extensionName) ? 'extensions/' . $this->_extensionName . '/' : '';
					$issuesLibPath = $libRootPath . self::ISSUE_RESOLVERS_FOLDER . '/';
					$issuesLibFilePath = DOC_ROOT . self::CI_PATH . '/' . $libRootPath . 'libraries/' . self::ISSUE_RESOLVERS_FOLDER . '/' . $libName . '.php';

					// check if library file exists
					if (!file_exists($issuesLibFilePath))
					{
						// log error and continue with next issue if not
						$this->logError("Issue library file " . $issuesLibFilePath . " does not exist");
						continue;
					}

					// load library connected to fehlercode
					$this->load->library($issuesLibPath . $libName);

					$lowercaseLibName = mb_strtolower($libName);

					// check if method is defined in library class
					if (!is_callable(array($this->{$lowercaseLibName}, self::CHECK_ISSUE_RESOLVED_METHOD_NAME)))
					{
						// log error and continue with next issue if not
						$this->logError("Method " . self::CHECK_ISSUE_RESOLVED_METHOD_NAME . " is not defined in library $lowercaseLibName");
						continue;
					}

					// call the function for checking for issue resolution
					$issueResolvedRes = $this->{$lowercaseLibName}->{self::CHECK_ISSUE_RESOLVED_METHOD_NAME}($params);

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
		}

		$this->logInfo("Issue resolve job ended");
	}
}
