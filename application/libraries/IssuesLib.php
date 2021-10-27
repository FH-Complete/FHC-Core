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
	public function addFhcIssue($fehler_kurzbz, $person_id = null, $oe_kurzbz = null, $fehlertext_params = null)
	{
		$fehlerRes = $this->_ci->FehlerModel->loadWhere(array('fehler_kurzbz' => $fehler_kurzbz));

		if (hasData($fehlerRes))
		{
			$fehlercode = getData($fehlerRes)[0]->fehlercode;
			return $this->_addIssue($fehlercode, $person_id, $oe_kurzbz, $fehlertext_params);
		}
		else
			return error("Fehler $fehler_kurzbz nicht gefunden");
	}

	/**
	 * Adds an external issue, already defined externally by another system.
	 * @param string $fehlercode_extern the error code in the external system
	 * @param string $inhalt_extern error text in external system
	 * @param int $person_id
	 * @param int $oe_kurzbz
	 * @param array $fehlertext_params params for replacement of parts of error text
	 * @param bool $force_predefined if true, only predefined external issues are added
	 * @return object success or error
	 */
	public function addExternalIssue($fehlercode_extern, $inhalt_extern, $person_id = null, $oe_kurzbz = null, $fehlertext_params = null, $force_predefined = false)
	{
		if (isEmptyString($fehlercode_extern))
			return error("fehlercode_extern fehlt");

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

		$fehlerData = getData($fehlerRes)[0];

		// check if there is a predefined custom error for the external issue
		if (hasData($fehlerRes))
		{
			// if found, use the code
			$fehlercode = $fehlerData->fehlercode;
		}
		elseif ($force_predefined === true)
		{
			// only added if predefined
			return success("No definition found - not added");
		}
		else
		{
			// if predefined error is not found, insert with fallback code
			$fehlercode = $this->_fallbackFehlercode;
		}

		// add external issue
		return $this->_addIssue($fehlercode, $person_id, $oe_kurzbz, $fehlertext_params, $fehlercode_extern, $inhalt_extern);
	}

	/**
	 * Changes status of an issue.
	 * @param int $issue_id
	 * @param string $status_kurzbz the new status
	 * @param string $verarbeitetvon uid of person changing the status (needed for in Bearbeitung and behoben)
	 * @return success or error
	 */
	public function changeIssueStatus($issue_id, $status_kurzbz, $verarbeitetvon = null)
	{
		if (!isset($issue_id) || !is_numeric($issue_id))
			return error("Issue Id muss korrekt gesetzt sein.");

		// check if given status is same as existing
		$this->_ci->IssueModel->addSelect('status_kurzbz');
		$currStatus = $this->_ci->IssueModel->load($issue_id);

		if (hasData($currStatus))
		{
			if (getData($currStatus)[0]->status_kurzbz == $status_kurzbz)
				return success("Gleicher Status bereits gesetzt");
		}
		else
			return error("Fehler beim Holen des Status");

		$data = array(
			'status_kurzbz' => $status_kurzbz,
			'updatevon' => $verarbeitetvon,
			'updateamum' => date('Y-m-d H:i:s')
		);

		if ($status_kurzbz == self::STATUS_NEU)
		{

			$data['verarbeitetvon'] = null;
		}

		if ($status_kurzbz == self::STATUS_NEU || $status_kurzbz == self::STATUS_IN_BEARBEITUNG)
		{
			$data['verarbeitetamum'] = null;
		}

		if ($status_kurzbz == self::STATUS_IN_BEARBEITUNG || $status_kurzbz == self::STATUS_BEHOBEN)
		{
			if (isset($verarbeitetvon))
				$data['verarbeitetvon'] = $verarbeitetvon;
			else
				return error("Verarbeitetvon nicht gesetzt");
		}

		if ($status_kurzbz == self::STATUS_BEHOBEN)
			$data['verarbeitetamum'] = date('Y-m-d H:i:s');

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
	 * @param string $fehlercode_extern
	 * @param string $inhalt_extern
	 * @return object success or error
	 */
	private function _addIssue($fehlercode, $person_id = null, $oe_kurzbz = null, $fehlertext_params = null, $fehlercode_extern = null, $inhalt_extern = null)
	{
		if (isEmptyString($person_id) && isEmptyString($oe_kurzbz))
			return error("Person_id oder oe_kurzbz muss gesetzt sein.");

		// get fehlertextVorlage and replace it with params
		$fehlerRes = $this->_ci->FehlerModel->load($fehlercode);

		if (hasData($fehlerRes))
		{
			$fehlertextVorlage = getData($fehlerRes)[0]->fehlertext;
			$fehlertext = isEmptyArray($fehlertext_params) ? $fehlertextVorlage : vsprintf($fehlertextVorlage, $fehlertext_params);

			$openIssuesCountRes = $this->_ci->IssueModel->getOpenIssueCount($fehlercode, $person_id, $oe_kurzbz, $fehlercode_extern);

			if (hasData($openIssuesCountRes))
			{
				// don't insert if issue is already open
				// already open - status new with same fehlercode or same fehlercode-extern (if set)
				$openIssueCount = getData($openIssuesCountRes)[0]->anzahl_open_issues;

				if ($openIssueCount == 0)
				{
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
							'insertvon' => $this->_insertvon
						)
					);
				}
				else
					return success($openIssueCount);
			}
			else
				return error("Anzahl offener Issues konnte nicht ermittelt werden.");
		}
		else
			return error("Fehler $fehlercode nicht gefunden");
	}
}
