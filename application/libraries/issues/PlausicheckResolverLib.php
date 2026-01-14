<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PlausicheckResolverLib
{
	const CI_PATH = 'application';
	const CI_LIBRARY_FOLDER = 'libraries';
	const EXTENSIONS_FOLDER = 'extensions';
	const ISSUE_RESOLVERS_FOLDER = 'issues/resolvers';
	const CHECK_ISSUE_RESOLVED_METHOD_NAME = 'checkIfIssueIsResolved';
	const CONFIG_FEHLER_FILENAME = 'fehler.php';
	const CONFIG_FEHLER_NAME = 'fehler';
	const FEHLERCODE_NAME = 'fehlercode';
	const FEHLER_KURZBZ_NAME = 'fehler_kurzbz';
	const RESOLVER_LIB_NAME = 'resolverLibName';
	const PRODUCER_IS_RESOLVER_NAME = 'producerIsResolver';
	const EXTENSION_NAME = 'extensionName';


	private $_ci; // ci instance
	private $_extensionName; // name of extension
	private $_fehlercodes = []; // name of extension
	private $_codeLibMappings = []; // mappings for issues which explicitly defined resolver
	private $_defaultIssueParams = ['issue_id' => 'issue_id', 'issue_person_id' => 'person_id', 'issue_oe_kurzbz' => 'oe_kurzbz'];

	public function __construct($params = null)
	{
		// get all fehler to be produced (by kurzbz array or app)
		if (isset($params['fehlercodes']) && !isEmptyArray($params['fehlercodes']))
		{
			$this->_fehlercodes = $params['fehlercodes'];
		}

		$this->_ci =& get_instance(); // get ci instance

		$this->_ci->load->library('IssuesLib');
		$this->_ci->load->library('ExtensionsLib');

		$this->_ci->load->config('fehler');

		// Load Fehler Entries of Core
		$configArray = $this->_ci->config->item(self::CONFIG_FEHLER_NAME);

		$fehlerKurzbzArr = [];

		foreach ($configArray as $coreEntry)
		{
			if (!isset($coreEntry[self::FEHLERCODE_NAME])
				|| !in_array($coreEntry[self::FEHLERCODE_NAME], $this->_fehlercodes)
			) {
				continue;
			}

			if (isset($coreEntry[self::FEHLER_KURZBZ_NAME])) $fehlerKurzbzArr[] = $coreEntry[self::FEHLER_KURZBZ_NAME];

			$this->_codeLibMappings[$coreEntry[self::FEHLERCODE_NAME]][self::RESOLVER_LIB_NAME] = $coreEntry[self::RESOLVER_LIB_NAME] ?? null;
			$this->_codeLibMappings[$coreEntry[self::FEHLERCODE_NAME]][self::PRODUCER_IS_RESOLVER_NAME]
				= $coreEntry[self::PRODUCER_IS_RESOLVER_NAME] ?? false;
		}

		// load fehler entries of extensions
		$extensions = $this->_ci->extensionslib->getInstalledExtensions();

		if (hasData($extensions))
		{
			$extensionArray = array();

			$extensionsData = getData($extensions);

			foreach ($extensionsData as $ext)
			{
				$configFilename = APPPATH.'config/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$ext->name.'/'.self::CONFIG_FEHLER_FILENAME;

				if (file_exists($configFilename))
				{
					$config = array(); // default value

					include($configFilename);

					if (isset($config[self::CONFIG_FEHLER_NAME]) && is_array($config[self::CONFIG_FEHLER_NAME]))
					{
						foreach ($config[self::CONFIG_FEHLER_NAME] as $extensionEntry)
						{
							if (
								!isset($extensionEntry[self::FEHLERCODE_NAME])
								|| !in_array($extensionEntry[self::FEHLERCODE_NAME], $this->_fehlercodes)
							) {
								continue;
							}

							$fehlercode = $extensionEntry[self::FEHLERCODE_NAME];
							if (isset($extensionEntry[self::FEHLER_KURZBZ_NAME])) $fehlerKurzbzArr[] = $extensionEntry[self::FEHLER_KURZBZ_NAME];

							$this->_codeLibMappings[$fehlercode][self::RESOLVER_LIB_NAME]
								= $extensionEntry[self::RESOLVER_LIB_NAME] ?? null;
							$this->_codeLibMappings[$fehlercode][self::EXTENSION_NAME] = $ext->name;
							$this->_codeLibMappings[$fehlercode][self::PRODUCER_IS_RESOLVER_NAME]
								= $extensionEntry[self::PRODUCER_IS_RESOLVER_NAME] ?? false;

						}
					}
				}
			}
		}

		$this->_ci->load->library('issues/PlausicheckProducerLib', ['fehlerKurzbz' => $fehlerKurzbzArr]);
	}

	/**
	 * Reseolves multiple plausicheck issues at once.
	 * @param array $openIssues passed issues to resolve
	 * @return result object with occured error and info
	 */
	public function resolvePlausicheckIssues($openIssues)
	{
		$result = new StdClass();
		$result->errors = [];
		$result->infos = [];

		// check if all issues to resolve could be found in database
		$mappingFehlerCodes = array_keys($this->_codeLibMappings);
		$notFoundFehlerCodes = array_diff($this->_fehlercodes, $mappingFehlerCodes);

		if (!isEmptyArray($notFoundFehlerCodes))
			$result->errors[] = error('Fehler to resolve not defined in config: '.implode(', ', $notFoundFehlerCodes));

		foreach ($openIssues as $issue)
		{
			$params = [];
			foreach ($this->_defaultIssueParams as $index => $propertyName)
			{
				$params[$index] = $issue->{$propertyName};
			}

			// add person id and oe kurzbz automatically as params, merge it with additional params
			// decode bewerbung_parameter into assoc array
			$params = array_merge(
				$params,
				isset($issue->behebung_parameter) ? json_decode($issue->behebung_parameter, true) : array()
			);

			$issueResolved = false;

			// ignore if Fehlercode is not in libmappings (shouldn't be checked)
			if (isset($this->_codeLibMappings[$issue->fehlercode]))
			{
				$codeLibMapping = $this->_codeLibMappings[$issue->fehlercode];
				$libName = $codeLibMapping[self::RESOLVER_LIB_NAME];
				$extensionName = $codeLibMapping[self::EXTENSION_NAME] ?? null;
				$producerIsResolver = $codeLibMapping[self::PRODUCER_IS_RESOLVER_NAME] ?? false;

				if ($producerIsResolver)
				{
					// execute same check as used for issue production
					$issueResolvedRes = $this->_ci->plausicheckproducerlib->producePlausicheckIssue(
						$issue->fehler_kurzbz,
						$params
					);

					if (isError($issueResolvedRes))
					{
						$result->errors[] = getError($issueResolvedRes);
					}
					else
					{
						$issueResolved = !hasData($issueResolvedRes);
					}
				}
				else
				{
					// if called from extension (extension name set), path includes extension names
					$libRootPath = isset($extensionName) ? self::EXTENSIONS_FOLDER . '/' . $extensionName . '/' : '';

					// path for loading issue library
					$issuesLibPath = $libRootPath . self::ISSUE_RESOLVERS_FOLDER . '/';

					// file path of library for check if file exists
					$issuesLibFilePath = DOC_ROOT . self::CI_PATH
						. '/' . $libRootPath . self::CI_LIBRARY_FOLDER . '/' . self::ISSUE_RESOLVERS_FOLDER . '/' . $libName . '.php';

					// check if library file exists
					if (!file_exists($issuesLibFilePath))
					{
						// log error and continue with next issue if not
						$result->errors[] = "Issue library file " . $issuesLibFilePath . " does not exist";
						continue;
					}

					// load library connected to fehlercode
					$this->_ci->load->library($issuesLibPath . $libName);

					$lowercaseLibName = mb_strtolower($libName);

					// check if method is defined in library class
					if (!is_callable(array($this->_ci->{$lowercaseLibName}, self::CHECK_ISSUE_RESOLVED_METHOD_NAME)))
					{
						// log error and continue with next issue if not
						$result->errors[] = "Method " . self::CHECK_ISSUE_RESOLVED_METHOD_NAME . " is not defined in library $lowercaseLibName";
						continue;
					}

					// call the function for checking for issue resolution
					$issueResolvedRes = $this->_ci->{$lowercaseLibName}->{self::CHECK_ISSUE_RESOLVED_METHOD_NAME}($params);

					if (isError($issueResolvedRes))
					{
						$result->errors[] = getError($issueResolvedRes);
					}
					else
					{
						$issueResolved = getData($issueResolvedRes) === true;
					}
				}
			}

			// set issue to resolved if needed
			if ($issueResolved)
			{
				$behobenRes = $this->_ci->issueslib->setBehoben($issue->issue_id, null);

				if (isError($behobenRes))
					$result->errors[] = getError($behobenRes);
				else
					$result->infos[] = "Issue " . $issue->issue_id . " successfully resolved";
			}
		}

		return $result;
	}
}
