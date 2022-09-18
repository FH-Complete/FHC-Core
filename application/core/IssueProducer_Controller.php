<?php

/**
 * Controller for writing (producing) issues if the issue condition is met
 */
abstract class IssueProducer_Controller extends JOB_Controller
{
	const ISSUES_FOLDER = 'issues';
	const CHECK_ISSUE_EXISTS_METHOD_NAME = 'checkIfIssueExists';
	const PRODUCE_ISSUE_METHOD_NAME = 'produceIssue';

	protected $_fehlerLibMappings;

	public function __construct()
	{
		parent::__construct();

		$this->load->model('system/Issue_model', 'IssueModel');

		$this->load->library('IssuesLib');
	}

	/**
	 * Initializes issue production.
	 */
	//public function produceIssue($fehler_kurzbz, $person_id, $oe_kurzbz, $paramsForChecking, $paramsForProduction)
	//{
		//// get libname from fehler_kurzbz
		//$libName = $this->_fehlerLibMappings[$fehler_kurzbz];
		
		//// if called from extension (extension name set), path includes extension names, otherwise it is the core library folder
		//$libRootPath = isset($this->_extensionName) ? 'extensions/' . $this->_extensionName . '/' : '';
		//$issuesLibPath = $libRootPath . self::ISSUES_FOLDER . '/';
		//$issuesLibFilePath = DOC_ROOT . 'application/' . $libRootPath . 'libraries/' . self::ISSUES_FOLDER . '/' . $libName . '.php';

		//// check if library file exists
		//if (!file_exists($issuesLibFilePath)) return error("Issue library file " . $issuesLibFilePath . " does not exist");

		//// load library connected to fehler_kurzbz
		//$this->load->library($issuesLibPath . $libName);

		//$lowercaseLibName = mb_strtolower($libName);

		//// check if method is defined in library class
		//if (!is_callable(array($this->{$lowercaseLibName}, self::CHECK_ISSUE_EXISTS_METHOD_NAME)))
			//return error("Method " . self::CHECK_ISSUE_EXISTS_METHOD_NAME . " is not defined in library $lowercaseLibName");

		//// call the function for checking for issue resolution
		//$issueExistsRes = $this->{$lowercaseLibName}->{self::CHECK_ISSUE_EXISTS_METHOD_NAME}($paramsForChecking);

		//if (isError($issueExistsRes)) return $issueExistsRes;

		//$issueExistsData = getData($issueExistsRes);

		//if ($issueExistsData === true)
		//{
			//// write issue if it was detected
			////$produceRes = $this->{$lowercaseLibName}->{self::PRODUCE_ISSUE_METHOD_NAME}(
				////$fehler_kurzbz,
				////isset($params['person_id']) ? $params['person_id'] : null,
				////isset($params['oe_kurzbz']) ? $params['oe_kurzbz'] : null,
				////$paramsForProduction
			////);

			////if (isError($produceRes))
				////return $produceRes;

			//$addIssueres = $this->IssuesLib->addFhcIssue($fehler_kurzbz, $person_id, $oe_kurzbz, $fehlertext_params, $resolution_params);
			
			//if (isError())

			//return success("Issue " . $issue->issue_id . " successfully written");
		//}
	//}
}
