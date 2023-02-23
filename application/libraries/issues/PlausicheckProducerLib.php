<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PlausicheckProducerLib
{
	const CI_PATH = 'application';
	const CI_LIBRARY_FOLDER = 'libraries';
	const EXTENSIONS_FOLDER = 'extensions';
	const PLAUSI_ISSUES_FOLDER = 'issues/plausichecks';
	const EXECUTE_PLAUSI_CHECK_METHOD_NAME = 'executePlausiCheck';

	private $_ci; // ci instance
	private $_currentStudiensemester; // current Studiensemester

	public function __construct()
	{
		$this->_ci =& get_instance(); // get ci instance

		// load models
		$this->_ci->load->model('organisation/studiensemester_model', 'StudiensemesterModel');

		// get current Studiensemester
		$studiensemesterRes = $this->_ci->StudiensemesterModel->getAkt();
		if (hasData($studiensemesterRes)) $this->_currentStudiensemester = getData($studiensemesterRes)[0]->studiensemester_kurzbz;
	}

	/**
	 * Executes plausicheck using a given library, returns the result.
	 * @param $libName string
	 * @param $studiensemester_kurzbz string optionally needed for issue production
	 * @param $studiengang_kz int optionally needed for issue production
	 */
	public function producePlausicheckIssue($libName, $studiensemester_kurzbz = null, $studiengang_kz = null)
	{
		// get Studiensemester
		if (isEmptyString($studiensemester_kurzbz)) $studiensemester_kurzbz = $this->_currentStudiensemester;

		// if called from extension (extension name set), path includes extension names
		$libRootPath = isset($this->_extensionName) ? self::EXTENSIONS_FOLDER . '/' . $this->_extensionName . '/' : '';

		// path for loading issue library
		$issuesLibPath = $libRootPath . self::PLAUSI_ISSUES_FOLDER . '/';

		// file path of library for check if file exists
		$issuesLibFilePath = DOC_ROOT . self::CI_PATH
			. '/' . $libRootPath . self::CI_LIBRARY_FOLDER . '/' . self::PLAUSI_ISSUES_FOLDER . '/' . $libName . '.php';

		// get path of library for issue to be produced

		//~ $issuesLibPath = DOC_ROOT . self::CI_LIBRARY_PATH . '/' . self::PLAUSI_ISSUES_FOLDER . '/';
		//~ $issuesLibFilePath = $issuesLibPath . $libName . '.php';

		// check if library file exists
		if (!file_exists($issuesLibFilePath)) return error("Issue library file " . $issuesLibFilePath . " does not exist");

		// load library connected to fehlercode
		//$this->_ci->load->library(self::PLAUSI_ISSUES_FOLDER . '/'.$libName);
		$this->_ci->load->library($issuesLibPath . $libName);

		$lowercaseLibName = mb_strtolower($libName);

		// check if method is defined in library class
		if (!is_callable(array($this->_ci->{$lowercaseLibName}, self::EXECUTE_PLAUSI_CHECK_METHOD_NAME)))
			return error("Method " . self::EXECUTE_PLAUSI_CHECK_METHOD_NAME . " is not defined in library $lowercaseLibName");

		// pass the data needed for issue check
		$paramsForCheck = array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'studiengang_kz' => $studiengang_kz
		);

		// call the function for checking for issue production
		return $this->_ci->{$lowercaseLibName}->{self::EXECUTE_PLAUSI_CHECK_METHOD_NAME}($paramsForCheck);
	}
}
