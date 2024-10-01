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
	private $_extensionName; // name of extension
	private $_konfiguration = []; // configuration parameters
	private $_fehlerLibMappings = []; // mappings of fehler and libraries for producing them
	private $_isForResolutionCheck = false; // mappings of fehler and libraries for producing them

	public function __construct($params = null)
	{
		// set extension name if called from extension
		if (isset($params['extensionName'])) $this->_extensionName = $params['extensionName'];
		if (isset($params['fehlerLibMappings'])) $this->_fehlerLibMappings = $params['fehlerLibMappings'];
		if (isset($params['isForResolutionCheck'])) $this->_isForResolutionCheck = $params['isForResolutionCheck'];

		// set application
		$app = isset($params['app']) ? $params['app'] : null;

		$this->_ci =& get_instance(); // get ci instance

		// load libraries
		$this->_ci->load->library('IssuesLib');

		// load models
		$this->_ci->load->model('system/Fehlerkonfiguration_model', 'FehlerkonfigurationModel');

		// get all configuration parameters for the application
		$fehlerkonfigurationRes = $this->_ci->FehlerkonfigurationModel->getKonfiguration($app);

		if (hasData($fehlerkonfigurationRes))
		{
			$fehlerkonfiguration = getData($fehlerkonfigurationRes);

			foreach ($fehlerkonfiguration as $fk)
			{
				$this->_konfiguration[$fk->fehler_kurzbz][$fk->konfigurationstyp_kurzbz] = $fk->konfiguration;
			}
		}
	}

	/**
	 * Produces multiple plausicheck issues at once and saved them to db.
	 * @param array $params passed to each plausicheck
	 * @return result object with occured error and info
	 */
	public function producePlausicheckIssues($params)
	{
		$result = new StdClass();
		$result->errors = [];
		$result->infos = [];

		foreach ($this->_fehlerLibMappings as $fehler_kurzbz => $libName)
		{
			$plausicheckRes = $this->producePlausicheckIssue(
				$libName,
				$fehler_kurzbz,
				$params
			);

			if (hasData($plausicheckRes))
			{
				$plausicheckData = getData($plausicheckRes);

				foreach ($plausicheckData as $plausiData)
				{
					// get the data needed for issue production
					$person_id = isset($plausiData['person_id']) ? $plausiData['person_id'] : null;
					$oe_kurzbz = isset($plausiData['oe_kurzbz']) ? $plausiData['oe_kurzbz'] : null;
					$fehlertext_params = isset($plausiData['fehlertext_params']) ? $plausiData['fehlertext_params'] : null;
					$resolution_params = isset($plausiData['resolution_params']) ? $plausiData['resolution_params'] : null;

					// write the issue
					$addIssueRes = $this->_ci->issueslib->addFhcIssue($fehler_kurzbz, $person_id, $oe_kurzbz, $fehlertext_params, $resolution_params);

					// log if error, or log info if inserted new issue
					if (isError($addIssueRes))
						$result->errors[] = getError($addIssueRes);
					elseif (hasData($addIssueRes) && is_integer(getData($addIssueRes)))
						$result->infos[] = "Plausicheck issue " . $fehler_kurzbz . " successfully produced, person_id: " . $person_id;
				}
			}
		}

		return $result;
	}

	/**
	 * Executes plausicheck using a given library, returns the result.
	 * @param $libName string name of library producing the issue
	 * @param $fehler_kurzbz string unique short name of fehler, for which issue is produced
	 * @param $params parameters passed to issue production method
	 */
	public function producePlausicheckIssue($libName, $fehler_kurzbz, $params)
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

		// load konfiguration parameters of the fehler_kurzbz
		$config = isset($this->_konfiguration[$fehler_kurzbz]) ? $this->_konfiguration[$fehler_kurzbz] : null;

		// load library connected to fehlercode
		$this->_ci->load->library(
			$issuesLibPath . $libName,
			['configurationParams' => $config, 'isForResolutionCheck' => $this->_isForResolutionCheck]
		);

		$lowercaseLibName = mb_strtolower($libName);

		// check if method is defined in library class
		if (!is_callable(array($this->_ci->{$lowercaseLibName}, self::EXECUTE_PLAUSI_CHECK_METHOD_NAME)))
			return error("Method " . self::EXECUTE_PLAUSI_CHECK_METHOD_NAME . " is not defined in library $lowercaseLibName");

		// call the function for checking for issue production
		return $this->_ci->{$lowercaseLibName}->{self::EXECUTE_PLAUSI_CHECK_METHOD_NAME}($params);
	}
}
