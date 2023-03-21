<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library for writing and reading issues (problems in fhcomplete system which need resolving)
 */
class IssuesLib
{
	private $_ci; // Code igniter instance

	const APP_INDEX = 'app';
	const INSERTVON_INDEX = 'insertvon';
	const FALLBACK_FEHLERCODE_INDEX = 'fallbackFehlercode';

	const STATUS_NEU = 'new';
	const STATUS_IN_BEARBEITUNG = 'inProgress';
	const STATUS_BEHOBEN = 'resolved';

	const ERRORTYPE_CODE = 'error';
	const WARNINGTYPE_CODE = 'warning';

	public function __construct($params = null)
	{
		$this->_ci =& get_instance();

		// Properties default values
		$this->_app = 'core';
		$this->_insertvon = 'system';
		$this->_fallbackFehlercode = 'UNKNOWN_ERROR';

		// If parameters are given then overwrite the default values
		if (!isEmptyArray($params)) $this->setConfigs($params);

		// load models
		$this->_ci->load->model('system/Issue_model', 'IssueModel');
		$this->_ci->load->model('system/Fehler_model', 'FehlerModel');
	}

	// --------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Store configuration parameters for this lib
	 */
	public function setConfigs($params)
	{
		// If parameters are given then overwrite the default values
		if (!isEmptyArray($params))
		{
			if (isset($params[self::APP_INDEX])) $this->_app = $params[self::APP_INDEX];
			if (isset($params[self::INSERTVON_INDEX])) $this->_insertvon = $params[self::INSERTVON_INDEX];
			if (isset($params[self::FALLBACK_FEHLERCODE_INDEX])) $this->_fallbackFehlercode = $params[self::FALLBACK_FEHLERCODE_INDEX];
		}
	}

	/**
	 * Adds an Fhc issue, i.e. an internal, self-defined issue.
	 * @param string $fehler_kurzbz short unique text name of the issue
	 * @param int $person_id
	 * @param string $oe_kurzbz
	 * @param array $fehlertext_params params for sprint replace of error text in system.tbl_fehler
	 * @return object success or error
	 */
	public function addFhcIssue($fehler_kurzbz, $person_id = null, $oe_kurzbz = null, $fehlertext_params = null, $resolution_params = null)
	{
		$fehlerRes = $this->_ci->FehlerModel->loadWhere(array('fehler_kurzbz' => $fehler_kurzbz));

		if (hasData($fehlerRes))
		{
			$fehlercode = getData($fehlerRes)[0]->fehlercode;
			return $this->_addIssue($fehlercode, $person_id, $oe_kurzbz, $fehlertext_params, $resolution_params);
		}
		else
			return error("Error $fehler_kurzbz not found");
	}

	/**
	 * Adds an external issue, already defined externally by another system.
	 * @param string $fehlercode_extern the error code in the external system
	 * @param string $inhalt_extern error text in external system
	 * @param int $person_id
	 * @param int $oe_kurzbz
	 * @param array $fehlertext_params params for replacement of parts of error text
	 * @param bool $force_predefined if true, only predefined (with entry in fehler table) external issues are added
	 * @return object success or error
	 */
	public function addExternalIssue($fehlercode_extern, $inhalt_extern, $person_id = null, $oe_kurzbz = null, $fehlertext_params = null)
	{
		if (isEmptyString($fehlercode_extern))
			return error("fehlercode_extern missing");

		// get external fehlercode (unique for each app)
		$this->_ci->FehlerModel->addSelect('fehlercode');
		$fehlerRes = $this->_ci->FehlerModel->loadWhere(
			array(
				'fehlercode_extern' => $fehlercode_extern,
				'app' => $this->_app
			)
		);

		if (isError($fehlerRes))
			return $fehlerRes;

		// check if there is a predefined custom error for the external issue
		if (hasData($fehlerRes))
		{
			$fehlerData = getData($fehlerRes)[0];
			// if found, use the code
			$fehlercode = $fehlerData->fehlercode;
		}
		else
		{
			// if predefined error is not found, insert with fallback code
			$fehlercode = $this->_fallbackFehlercode;
		}

		// add external issue
		return $this->_addIssue($fehlercode, $person_id, $oe_kurzbz, $fehlertext_params, null, $fehlercode_extern, $inhalt_extern);
	}

	/**
	 * Set issue to resolved.
	 * @param int $issue_id
	 * @param string $user uid of issue resolver
	 * @return object success or error
	 */
	public function setBehoben($issue_id, $user)
	{
		$data = array(
			'status_kurzbz' => self::STATUS_BEHOBEN,
			'verarbeitetvon' => $user,
			'verarbeitetamum' => date('Y-m-d H:i:s')
		);

		return $this->_changeIssueStatus($issue_id, $data, $user);
	}

	/**
	 * Set issue to in progress.
	 * @param int $issue_id
	 * @param string $user uid of issue resovler
	 * @return object success or error
	 */
	public function setInBearbeitung($issue_id, $user)
	{
		$data = array(
			'status_kurzbz' => self::STATUS_IN_BEARBEITUNG,
			'verarbeitetvon' => $user
		);

		return $this->_changeIssueStatus($issue_id, $data, $user);
	}

	/**
	 * Set issue to new.
	 * @param int $issue_id
	 * @param string $user uid of issue resolver
	 * @return object success or error
	 */
	public function setNeu($issue_id, $user)
	{
		$data = array(
			'status_kurzbz' => self::STATUS_NEU,
			'verarbeitetvon' => null,
			'verarbeitetamum' => null
		);

		return $this->_changeIssueStatus($issue_id, $data, $user);
	}

	// --------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Changes status of an issue.
	 * @param int $issue_id
	 * @param array $sdata the data to save, including status
	 * @param string $user uid of person changing the status (needed for in Bearbeitung and behoben)
	 * @return success or error
	 */
	private function _changeIssueStatus($issue_id, $data, $user)
	{
		if (!isset($issue_id) || !is_numeric($issue_id))
			return error("Issue Id must be set correctly.");

		// check if given status is same as existing
		$this->_ci->IssueModel->addSelect('status_kurzbz');
		$currStatus = $this->_ci->IssueModel->load($issue_id);

		if (hasData($currStatus))
		{
			if (getData($currStatus)[0]->status_kurzbz == $data['status_kurzbz'])
				return success("Same status already set");
		}
		else
			return error("Error when getting status");

		$data['updatevon'] = $user;
		$data['updateamum'] = date('Y-m-d H:i:s');

		return $this->_ci->IssueModel->update(
			array(
				'issue_id' => $issue_id
			),
			$data
		);
	}

	/**
	 * Adds an issue.
	 * @param $fehlercode
	 * @param int $person_id
	 * @param string $oe_kurzbz
	 * @param array $fehlertext_params
	 * @param string $resolution_params
	 * @param string $fehlercode_extern
	 * @param string $inhalt_extern
	 * @return object success or error
	 */
	private function _addIssue(
		$fehlercode,
		$person_id = null,
		$oe_kurzbz = null,
		$fehlertext_params = null,
		$resolution_params = null,
		$fehlercode_extern = null,
		$inhalt_extern = null
	) {
		if (isEmptyString($person_id) && isEmptyString($oe_kurzbz))
			return error("Person_id or oe_kurzbz must be set.");

		// get fehlertextVorlage and replace it with params
		$fehlerRes = $this->_ci->FehlerModel->load($fehlercode);

		if (hasData($fehlerRes))
		{
			$fehlertextVorlage = getData($fehlerRes)[0]->fehlertext;

			$fehlertext = $fehlertextVorlage;
			if (!isEmptyArray($fehlertext_params))
			{
				if (count($fehlertext_params) != substr_count($fehlertextVorlage, '%s'))
					return error('Wrong number of parameters for Fehlertext, fehler_kurzbz ' . $fehlercode);

				$fehlertext = vsprintf($fehlertextVorlage, $fehlertext_params);
			}

			$openIssuesCountRes = $this->_ci->IssueModel->getOpenIssueCount($fehlercode, $person_id, $oe_kurzbz, $fehlercode_extern);

			if (hasData($openIssuesCountRes))
			{
				// don't insert if issue is already open
				// already open - status new with same fehlercode or same fehlercode-extern (if set)
				$openIssueCount = getData($openIssuesCountRes)[0]->anzahl_open_issues;

				if ($openIssueCount == 0)
				{
					if (isset($resolution_params))
					{
						if (is_array($resolution_params))
						{
							foreach ($resolution_params as $resolution_key => $resolution_param)
							{
								if (!is_string($resolution_key))
									return error("Invalid parameter for resolution, must be an associative array");
							}
						}
						else
							return error("Invalid parameters for resolution");
					}

					// insert new issue
					return $this->_ci->IssueModel->insert(
						array(
							'fehlercode' => $fehlercode,
							'fehlercode_extern' => $fehlercode_extern,
							'inhalt' => $fehlertext,
							'inhalt_extern' => $inhalt_extern,
							'person_id' => $person_id,
							'oe_kurzbz' => $oe_kurzbz,
							'datum' => date('Y-m-d H:i:s'),
							'status_kurzbz' => self::STATUS_NEU,
							'behebung_parameter' => isset($resolution_params) ? json_encode($resolution_params) : null,
							'insertvon' => $this->_insertvon
						)
					);
				}
				else // return success if issue already exists
					return success("Issue already exists");
			}
			else
				return error("Number of open issues could not be determined");
		}
		else
			return error("Error $fehlercode could not be found");
	}
}
