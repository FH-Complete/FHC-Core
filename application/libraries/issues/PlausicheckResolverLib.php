<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PlausicheckResolverLib
{
	const CI_PATH = 'application';
	const CI_LIBRARY_FOLDER = 'libraries';
	const EXTENSIONS_FOLDER = 'extensions';
	const ISSUE_RESOLVERS_FOLDER = 'issues/resolvers';
	const CHECK_ISSUE_RESOLVED_METHOD_NAME = 'checkIfIssueIsResolved';

	private $_ci; // ci instance
	private $_extensionName; // name of extension
	private $_codeLibMappings = []; // mappings for issues which explicitly defined resolver
	private $_codeProducerLibMappings = []; // mappings for issues which are resolved as produced

	public function __construct($params = null)
	{
		// set extension name if called from extension
		if (isset($params['extensionName'])) $this->_extensionName = $params['extensionName'];
		if (isset($params['codeLibMappings'])) $this->_codeLibMappings = $params['codeLibMappings'];
		if (isset($params['codeProducerLibMappings'])) $this->_codeProducerLibMappings = $params['codeProducerLibMappings'];

		$this->_ci =& get_instance(); // get ci instance

		$this->_ci->load->library('IssuesLib');
		$this->_ci->load->library('issues/PlausicheckProducerLib', ['extensionName' => $this->_extensionName, 'isForResolutionCheck' => true]);
	}

	/**
	 * Reseolves multiple plausicheck issues at once.
	 * @param array $codeLibMappings contains fehler type to check and library responsible for check (fehlercode => libName)
	 * @param array $openIssues passed issues to resolve
	 * @return result object with occured error and info
	 */
	public function resolvePlausicheckIssues($openIssues)
	{
		$result = new StdClass();
		$result->errors = [];
		$result->infos = [];

		foreach ($openIssues as $issue)
		{
			// add person id and oe kurzbz automatically as params, merge it with additional params
			// decode bewerbung_parameter into assoc array
			$params = array_merge(
				array('issue_id' => $issue->issue_id, 'issue_person_id' => $issue->person_id, 'issue_oe_kurzbz' => $issue->oe_kurzbz),
				isset($issue->behebung_parameter) ? json_decode($issue->behebung_parameter, true) : array()
			);

			$issueResolved = false;

			// ignore if Fehlercode is not in libmappings (shouldn't be checked)
			if (isset($this->_codeLibMappings[$issue->fehlercode]))
			{
				$libName = $this->_codeLibMappings[$issue->fehlercode];

				// if called from extension (extension name set), path includes extension names
				$libRootPath = isset($this->_extensionName) ? self::EXTENSIONS_FOLDER . '/' . $this->_extensionName . '/' : '';

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
			elseif (isset($this->_codeProducerLibMappings[$issue->fehlercode]))
			{
				$libName = $this->_codeProducerLibMappings[$issue->fehlercode];

				$issueResolvedRes = $this->_ci->plausicheckproducerlib->producePlausicheckIssue(
					$libName,
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
