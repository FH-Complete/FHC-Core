<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class IssuesKonfiguration extends Auth_Controller
{
	private $_uid;

	const STRING_DATA_TYPE = 'string';
	const INTEGER_DATA_TYPE = 'integer';
	const FLOAT_DATA_TYPE = 'float';

	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'admin:r',
				'getApps' => 'admin:r',
				'getFehlerKonfigurationByApp' => 'admin:r',
				'saveFehlerKonfiguration' => 'admin:rw',
				'deleteKonfiguration' => 'admin:rw',
				'deleteKonfigurationsWerte' => 'admin:rw'
			)
		);

		// Load libraries
		$this->load->library('IssuesLib');
		$this->load->library('WidgetLib');

		// Load models
		$this->load->model('system/Fehlerkonfigurationstyp_model', 'FehlerkonfigurationstypModel');
		$this->load->model('system/Fehlerkonfiguration_model', 'FehlerkonfigurationModel');

		$this->loadPhrases(
			array(
				'global',
				'ui',
				'filter',
				'fehlermonitoring'
			)
		);

		$this->_setAuthUID(); // sets property uid
		$this->setControllerId(); // sets the controller id
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Load initial view.
	 */
	public function index()
	{
		$this->load->view("system/issues/issuesKonfiguration.php");
	}

	/**
	 * Loads all Apps to which Fehler exist.
	 */
	public function getApps()
	{
		$this->FehlerModel->addDistinct();
		$this->FehlerModel->addSelect('app');
		$this->FehlerModel->addJoin('system.tbl_fehler_konfigurationstyp', 'app');
		$this->FehlerModel->addOrder('app');

		$appRes = $this->FehlerModel->load();

		$this->outputJson($appRes);
	}

	/**
	 * Gets all fehlercodes, optionally by app.
	 */
	public function getFehlerKonfigurationByApp()
	{
		$app = $this->input->get('app');

		// get all Konfiguration types, optionally filtered by app
		$this->FehlerkonfigurationstypModel->addSelect('konfigurationstyp_kurzbz, konfigurationsdatentyp, beschreibung');
		$this->FehlerkonfigurationstypModel->addOrder('konfigurationstyp_kurzbz');
		$konfRes = isEmptyString($app)
			? $this->FehlerkonfigurationstypModel->load()
			: $this->FehlerkonfigurationstypModel->loadWhere(array('app' => $app));

		if (isError($konfRes)) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'fehlerFehlerKonfigurationLaden'));

		// get all Fehler, optionally filtered by app
		$params = array('fehlercode_extern' => null);
		$this->FehlerModel->addSelect('fehlercode, fehler_kurzbz, fehlertyp_kurzbz, fehlertext');
		$this->FehlerModel->addOrder('fehlercode');
		if (!isEmptyString($app)) $params['app'] = $app;
		$fehlerRes = $this->FehlerModel->loadWhere($params);

		if (isError($fehlerRes)) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'fehlerFehlerLaden'));

		// return object with retrieved data
		$konfObj = new StdClass();
		$konfObj->konfigurationstypen = array();
		$konfObj->fehler = array();

		if (hasData($konfRes)) $konfObj->konfigurationstypen = getData($konfRes);
		if (hasData($fehlerRes)) $konfObj->fehler = getData($fehlerRes);

		$this->outputJsonSuccess($konfObj);
	}

	/**
	 * Saves a Fehler configuration, inserts new configuration or updates existing.
	 * Checks if datatype of passed configuration is correct.
	 */
	public function saveFehlerKonfiguration()
	{
		$result = null;
		$konfigurationstyp_kurzbz = $this->input->post('konfigurationstyp_kurzbz');
		$fehlercode = $this->input->post('fehlercode');
		$konfigurationsWert = $this->input->post('konfigurationsWert');

		// check if all params passed
		if (isEmptyString($konfigurationstyp_kurzbz)) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'ungueltigerKonfigurationstyp'));

		if (isEmptyString($fehlercode)) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'fehlercodeFehlt'));

		// separate by semicolon if multiple values passed
		$konfigurationsWert = explode(';', $konfigurationsWert);

		// check konfigurationswert

		// get the expected data type
		$dataType = self::STRING_DATA_TYPE;
		$this->FehlerkonfigurationstypModel->addSelect('konfigurationsdatentyp');
		$konfigtypRes = $this->FehlerkonfigurationstypModel->loadWhere(array('konfigurationstyp_kurzbz' => $konfigurationstyp_kurzbz));

		if (hasData($konfigtypRes))
		{
			$konfigurationsdatentyp = getData($konfigtypRes)[0]->konfigurationsdatentyp;
			foreach ($konfigurationsWert as $idx => $konfWert)
			{
				// check if data type correct
				$valid = false;
				switch ($konfigurationsdatentyp)
				{
					case self::INTEGER_DATA_TYPE:
						$valid = (string)(int)$konfWert == $konfWert;
						$konfigurationsWert[$idx] = (int) $konfWert;
						break;
					case self::FLOAT_DATA_TYPE:
						$valid = (string)(float)$konfWert == $konfWert;
						$konfigurationsWert[$idx] = (float) $konfWert;
						break;
					default:
						$valid = is_string($konfWert) && preg_match('/^[A-Za-z0-9_]+$/', $konfWert);
				}
				if (!$valid) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'ungueltigerKonfigurationswert', array($konfigurationsdatentyp)));
			}
		}

		// check if konfiguration already set for the fehlercode
		$this->FehlerkonfigurationModel->addSelect('konfiguration');
		$fehlerkonfigurationRes = $this->FehlerkonfigurationModel->loadWhere(
			array(
				'konfigurationstyp_kurzbz' => $konfigurationstyp_kurzbz,
				'fehlercode' => $fehlercode
			)
		);

		if (isError($fehlerkonfigurationRes)) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'fehlerFehlerKonfigurationLaden'));

		// if konfiguration exists, update by add konfiguration values to existing
		if (hasData($fehlerkonfigurationRes))
		{
			$fehlerkonfiguration = getData($fehlerkonfigurationRes);

			$existingKonf = json_decode($fehlerkonfiguration[0]->konfiguration);

			if (!$existingKonf) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'fehlerJsonDekodierung'));

			if (!is_array($existingKonf)) $existingKonf = array($existingKonf);

			$newKonf = json_encode(array_values(array_unique(array_merge($existingKonf, $konfigurationsWert))));
			if (!$newKonf) $this->terminateWithJsonError("error when encoding JSON");

			$result = $this->FehlerkonfigurationModel->update(
				array('konfigurationstyp_kurzbz' => $konfigurationstyp_kurzbz, 'fehlercode' => $fehlercode),
				array('konfiguration' => $newKonf, 'updateamum' => 'NOW()', 'updatevon' => $this->_uid)
			);
		}
		else // if no konfiguration exists, add new konfiguration entry
		{
			$newKonf = json_encode($konfigurationsWert);
			if (!$newKonf) $this->terminateWithJsonError("error when encoding JSON");

			$result = $this->FehlerkonfigurationModel->insert(
				array(
					'konfigurationstyp_kurzbz' => $konfigurationstyp_kurzbz,
					'fehlercode' => $fehlercode,
					'konfiguration' => $newKonf,
					'insertvon' => $this->_uid
				)
			);
		}

		// output result (insert or update)
		$this->outputJson($result);
	}

	/**
	 * Deletes values of a Konfiguration.
	 */
	public function deleteKonfigurationsWerte()
	{
		$konfigurationstyp_kurzbz = $this->input->post('konfigurationstyp_kurzbz');
		$fehlercode = $this->input->post('fehlercode');
		$konfigurationsWert = $this->input->post('konfigurationsWert');

		// check if Konfigurationstyp correctly passed
		if (isEmptyString($konfigurationstyp_kurzbz)) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'ungueltigerKonfigurationstyp'));

		// check if fehlercode correctly passed
		if (isEmptyString($fehlercode)) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'fehlercodeFehlt'));

		// separate by semicolon if multiple values passed
		$konfigurationsWert = explode(';', $konfigurationsWert);

		// check if konfiguration already set for the fehlercode
		$this->FehlerkonfigurationModel->addSelect('konfiguration');
		$fehlerkonfigurationRes = $this->FehlerkonfigurationModel->loadWhere(
			array(
				'konfigurationstyp_kurzbz' => $konfigurationstyp_kurzbz,
				'fehlercode' => $fehlercode
			)
		);

		if (!hasData($fehlerkonfigurationRes)) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'fehlerFehlerKonfigurationLaden'));

		// if konfiguration exists, update values
		if (hasData($fehlerkonfigurationRes))
		{
			$fehlerkonfiguration = getData($fehlerkonfigurationRes);

			$existingKonf = json_decode($fehlerkonfiguration[0]->konfiguration);

			if (!$existingKonf) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'fehlerJsonDekodierung'));

			if (!is_array($existingKonf)) $existingKonf = array($existingKonf);

			$newKonfArr = array_values(array_diff($existingKonf, $konfigurationsWert));

			// if no konfiguration values left, delete whole entry
			if (isEmptyArray($newKonfArr))
			{
				$this->outputJson(
					$this->FehlerkonfigurationModel->delete(
						array('konfigurationstyp_kurzbz' => $konfigurationstyp_kurzbz, 'fehlercode' => $fehlercode)
					)
				);
			}
			else
			{
				$newKonf = json_encode($newKonfArr);
				if (!$newKonf) $this->terminateWithJsonError("error when encoding JSON");

				// if there are still values, delete only part of the konfiguration
				$this->outputJson(
					$this->FehlerkonfigurationModel->update(
						array('konfigurationstyp_kurzbz' => $konfigurationstyp_kurzbz, 'fehlercode' => $fehlercode),
						array('konfiguration' => $newKonf, 'updateamum' => 'NOW()', 'updatevon' => $this->_uid)
					)
				);
			}
		}
	}

	/**
	 * Deletes a Konfiguration.
	 */
	public function deleteKonfiguration()
	{
		$konfigurationstyp_kurzbz = $this->input->post('konfigurationstyp_kurzbz');
		$fehlercode = $this->input->post('fehlercode');

		// check if Konfigurationstyp correctly passed
		if (isEmptyString($konfigurationstyp_kurzbz)) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'ungueltigerKonfigurationstyp'));

		// check if fehlercode correctly passed
		if (isEmptyString($fehlercode)) $this->terminateWithJsonError($this->p->t('fehlermonitoring', 'fehlercodeFehlt'));

		$this->outputJson(
			$this->FehlerkonfigurationModel->delete(
				array('konfigurationstyp_kurzbz' => $konfigurationstyp_kurzbz, 'fehlercode' => $fehlercode)
			)
		);
	}

	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid) show_error('User authentification failed');
	}
}
