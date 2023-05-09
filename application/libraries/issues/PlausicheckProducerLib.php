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
	private $_extensionName;

	public function __construct($params = null)
	{
		// set extension name if called from extension
		if (isset($params['extensionName'])) $this->_extensionName = $params['extensionName'];

		$this->_ci =& get_instance(); // get ci instance
	}

	/**
	 * Executes plausicheck using a given library, returns the result.
	 * @param $libName string name of library producing the issue
	 * @param $params parameters passed to issue production method
	 */
	public function producePlausicheckIssue($libName, $params)
	{
		// if called from extension (extension name set), path includes extension names
		$libRootPath = isset($this->_extensionName) ? self::EXTENSIONS_FOLDER . '/' . $this->_extensionName . '/' : '';

		// path for loading issue library
		$issuesLibPath = $libRootPath . self::PLAUSI_ISSUES_FOLDER . '/';

		// file path of library for check if file exists
		$issuesLibFilePath = DOC_ROOT . self::CI_PATH
			. '/' . $libRootPath . self::CI_LIBRARY_FOLDER . '/' . self::PLAUSI_ISSUES_FOLDER . '/' . $libName . '.php';

		// check if library file exists
		if (!file_exists($issuesLibFilePath)) return error("Issue library file " . $issuesLibFilePath . " does not exist");

		// load library connected to fehlercode
		$this->_ci->load->library($issuesLibPath . $libName);

		$lowercaseLibName = mb_strtolower($libName);

		// check if method is defined in library class
		if (!is_callable(array($this->_ci->{$lowercaseLibName}, self::EXECUTE_PLAUSI_CHECK_METHOD_NAME)))
			return error("Method " . self::EXECUTE_PLAUSI_CHECK_METHOD_NAME . " is not defined in library $lowercaseLibName");

		// call the function for checking for issue production
		return $this->_ci->{$lowercaseLibName}->{self::EXECUTE_PLAUSI_CHECK_METHOD_NAME}($params);
	}
}
