<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PlausicheckProducerLib
{
	const CI_PATH = 'application';
	const CI_LIBRARY_FOLDER = 'libraries';
	const PLAUSI_ISSUES_FOLDER = 'issues/plausichecks';
	const EXECUTE_PLAUSI_CHECK_METHOD_NAME = 'executePlausiCheck';
	const CONFIG_FEHLER_NAME = 'fehler';
	const CONFIG_FEHLER_INDEX = 'fehler';
	const FEHLER_KURZBZ_NAME = 'fehler_kurzbz';
	const PRODUCER_LIB_NAME = 'producerLibName';
	const EXTENSION_NAME = 'extensionName';

	private $_ci; // ci instance
	private $_konfiguration = []; // configuration parameters
	private $_fehlerKurzbz = []; // fehler to produce
	private $_fehlerLibMappings = []; // mappings of fehler and libraries for producing them
	private $_apps = []; // apps of fehler to produce

	public function __construct($params = null)
	{
		// set application(s))
		if (isset($params['apps']))
		{
			if (is_string($params['apps'])) $params['apps'] = [$params['apps']];
			if (is_array($params['apps'])) $this->_apps = $params['apps'];
		}

		$this->_ci =& get_instance(); // get ci instance

		// load libraries
		$this->_ci->load->library('IssuesLib');
		$this->_ci->load->library('ExtensionsLib');

		// load models
		$this->_ci->load->model('system/Fehler_model', 'FehlerModel');
		$this->_ci->load->model('system/Fehlerkonfiguration_model', 'FehlerkonfigurationModel');

		$this->_ci->load->config(self::CONFIG_FEHLER_NAME);

		// get all configuration parameters for the application(s))
		$fehlerkonfigurationRes = $this->_ci->FehlerkonfigurationModel->getKonfiguration($this->_apps);

		if (hasData($fehlerkonfigurationRes))
		{
			$fehlerkonfiguration = getData($fehlerkonfigurationRes);

			foreach ($fehlerkonfiguration as $fk)
			{
				$this->_konfiguration[$fk->fehler_kurzbz][$fk->konfigurationstyp_kurzbz] = $fk->konfiguration;
			}
		}

		// get all fehler to be produced (by kurzbz array or app)
		if (isset($params['fehlerKurzbz']) && !isEmptyArray($params['fehlerKurzbz']))
		{
			$this->_fehlerKurzbz = $params['fehlerKurzbz'];
		}
		else
		{
			$this->_ci->FehlerModel->addSelect('fehler_kurzbz');
			if (!isEmptyArray($this->_apps)) $this->_ci->FehlerModel->db->where_in('app', $this->_apps);
			$fehlerRes = $this->_ci->FehlerModel->load();

			if (hasData($fehlerRes))
			{
				$this->_fehlerKurzbz = array_column(getData($fehlerRes), 'fehler_kurzbz');
			}
		}

		// get producer file paths for the fehler

		// Load Fehler Entries of Core
		$configArray = $this->_ci->config->item(self::CONFIG_FEHLER_INDEX);

		if (isset($configArray) && is_array($configArray))
		{
			foreach ($configArray as $coreEntry)
			{
				if (!isset($coreEntry[self::FEHLER_KURZBZ_NAME])
					|| !isset($coreEntry[self::PRODUCER_LIB_NAME])
					|| !in_array($coreEntry[self::FEHLER_KURZBZ_NAME], $this->_fehlerKurzbz)
				) {
					continue;
				}

				$this->_fehlerLibMappings[$coreEntry[self::FEHLER_KURZBZ_NAME]][self::PRODUCER_LIB_NAME] = $coreEntry[self::PRODUCER_LIB_NAME];
			}
		}

		// load fehler entries of extensions
		$extensions = $this->_ci->extensionslib->getInstalledExtensions();

		if (hasData($extensions))
		{
			$extensionArray = array();

			$extensionsData = getData($extensions);

			foreach ($extensionsData as $ext)
			{
				$configFilePath = ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$ext->name.'/'.self::CONFIG_FEHLER_NAME.'.php';
				$configFilename = APPPATH.'config/'.$configFilePath;

				// if fehler config file exists in extension
				if (file_exists($configFilename))
				{
					$config = array(); // default value

					// include the config file
					include($configFilename);

					if (isset($config[self::CONFIG_FEHLER_NAME]) && is_array($config[self::CONFIG_FEHLER_NAME]))
					{
						foreach ($config[self::CONFIG_FEHLER_NAME] as $extensionEntry)
						{
							if (
								!isset($extensionEntry[self::FEHLER_KURZBZ_NAME])
								|| !isset($extensionEntry[self::PRODUCER_LIB_NAME])
								|| !in_array($extensionEntry[self::FEHLER_KURZBZ_NAME], $this->_fehlerKurzbz)
							) {
								continue;
							}

							// add extension config data to fehler lib mappings
							$fehler_kurzbz = $extensionEntry[self::FEHLER_KURZBZ_NAME];

							$this->_fehlerLibMappings[$fehler_kurzbz][self::PRODUCER_LIB_NAME] = $extensionEntry[self::PRODUCER_LIB_NAME];
							$this->_fehlerLibMappings[$fehler_kurzbz][self::EXTENSION_NAME] = $ext->name;
						}
					}
				}
			}
		}
	}

	/**
	 * Produces multiple plausicheck issues at once, and saves them in the database.
	 * @param array $params passed to each plausicheck
	 * @return result object with occured error and info
	 */
	public function producePlausicheckIssues($params)
	{
		$result = new StdClass();
		$result->errors = [];
		$result->infos = [];

		$mappingFehlerKurbz = array_keys($this->_fehlerLibMappings);

		// check if all issues to produce could be found in database
		$notFoundFehlerKurzbz = array_diff($this->_fehlerKurzbz, $mappingFehlerKurbz);

		if (!isEmptyArray($notFoundFehlerKurzbz))
			$result->errors[] = error('Fehler to produce not defined in config: '.implode(', ', $notFoundFehlerKurzbz));

		foreach ($mappingFehlerKurbz as $fehler_kurzbz)
		{
			$plausicheckRes = $this->producePlausicheckIssue(
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
	 * @param $fehler_kurzbz string unique short name of fehler, for which issue is produced
	 * @param $params parameters passed to issue production method
	 */
	public function producePlausicheckIssue($fehler_kurzbz, $params)
	{
		if (!isset($this->_fehlerLibMappings[$fehler_kurzbz])) return error("Mapping for Fehler " . $fehler_kurzbz . " was not found");

		$mapping = $this->_fehlerLibMappings[$fehler_kurzbz];

		if (!isset($mapping[self::PRODUCER_LIB_NAME]) || isEmptyString($mapping[self::PRODUCER_LIB_NAME]))
			return error("No producer lib name set for Fehler " . $fehler_kurzbz);

		$libName = $mapping[self::PRODUCER_LIB_NAME];

		// if called from extension (extension name set), path includes extension names
		$libRootPath = isset($mapping[self::EXTENSION_NAME]) ? ExtensionsLib::EXTENSIONS_DIR_NAME . '/' . $mapping[self::EXTENSION_NAME] . '/' : '';

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
			['configurationParams' => $config]
		);

		$lowercaseLibName = mb_strtolower($libName);

		// check if method is defined in library class
		if (!is_callable(array($this->_ci->{$lowercaseLibName}, self::EXECUTE_PLAUSI_CHECK_METHOD_NAME)))
			return error("Method " . self::EXECUTE_PLAUSI_CHECK_METHOD_NAME . " is not defined in library $lowercaseLibName");

		// call the function for checking for issue production
		return $this->_ci->{$lowercaseLibName}->{self::EXECUTE_PLAUSI_CHECK_METHOD_NAME}($params);
	}
}
