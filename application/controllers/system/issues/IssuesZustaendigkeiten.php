<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class IssuesZustaendigkeiten extends Auth_Controller
{
	private $_uid;

	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'admin:r',
				'getApps' => 'admin:r',
				'getFehlercodes' => 'admin:r',
				'getNonAssignedZustaendigkeiten' => 'admin:r',
				'addZustaendigkeit' => 'admin:rw',
				'deleteZustaendigkeit' => 'admin:rw'
			)
		);

		// Load libraries
		$this->load->library('IssuesLib');
		$this->load->library('WidgetLib');

		// Load models
		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		$this->load->model('system/Fehler_model', 'FehlerModel');
		$this->load->model('system/Fehlerzustaendigkeiten_model', 'FehlerzustaendigkeitenModel');

		$this->loadPhrases(
			array(
				'global',
				'ui',
				'filter',
				'lehre',
				'person',
				'fehlermonitoring'
			)
		);

		$this->_setAuthUID(); // sets property uid
		$this->setControllerId(); // sets the controller id
	}

	public function index()
	{
		$this->load->view("system/issues/issuesZustaendigkeiten.php");
	}

	/**
	 * Loads all Apps to which Fehler exist.
	 */
	public function getApps()
	{
		$this->FehlerModel->addDistinct();
		$this->FehlerModel->addSelect('app');
		$this->FehlerModel->addOrder('app');

		$appRes = $this->FehlerModel->load();

		$this->outputJson($appRes);
	}

	/**
	 * Gets all fehlercodes, optionally by app.
	 */
	public function getFehlercodes()
	{
		$app = $this->input->get('app');

		$this->FehlerModel->addSelect('fehlercode, fehler_kurzbz, fehlertext, fehlertyp_kurzbz, app');
		$this->FehlerModel->addOrder('fehlercode');

		$fehlerRes = isset($app) ? $this->FehlerModel->loadWhere(array('app' => $app)) : $this->FehlerModel->load();

		$this->outputJson($fehlerRes);
	}

	/**
	 * Gets all Mitarbeiter, Organisationseinheiten, Funktionen not assigned to a Fehler yet.
	 */
	public function getNonAssignedZustaendigkeiten()
	{
		$fehlercode = $this->input->get('fehlercode');

		$mitarbeiterRes = $this->FehlerzustaendigkeitenModel->getNonAssignedMitarbeiter($fehlercode);

		if (isError($mitarbeiterRes))
		{
			$this->outputJsonError(getError($mitarbeiterRes));
			return;
		}

		$this->OrganisationseinheitModel->addSelect('oe_kurzbz, bezeichnung, organisationseinheittyp_kurzbz');
		$this->OrganisationseinheitModel->addOrder('organisationseinheittyp_kurzbz, bezeichnung');
		$oeRes = $this->OrganisationseinheitModel->loadWhere(array('aktiv' => true));

		if (isError($oeRes))
		{
			$this->outputJsonError(getError($oeRes));
			return;
		}

		$oe_funktionen = array();

		if (hasData($oeRes))
		{
			$oes = getData($oeRes);

			foreach ($oes as $oe)
			{
				$oe->funktionen = array();
				$funktionRes = $this->FehlerzustaendigkeitenModel->getNonAssignedFunktionen($fehlercode, $oe->oe_kurzbz);

				if (isError($funktionRes))
				{
					$this->outputJsonError(getError($oeRes));
					return;
				}

				$funktionData = getData($funktionRes);
				$oe->funktionen = $funktionData;
				$oe_funktionen[] = $oe;
			}
		}

		if (isError($funktionRes))
		{
			$this->outputJsonError(getError($funktionRes));
			return;
		}

		$result = array(
			'mitarbeiter' => getData($mitarbeiterRes),
			'oe_funktionen' => $oe_funktionen
		);

		$this->outputJsonSuccess($result);
	}

	/**
	 * Adds a Zuständigkeit after performing error checks.
	 */
	public function addZustaendigkeit()
	{
		$fehlercode = $this->input->post('fehlercode');
		$mitarbeiter_person_id = $this->input->post('mitarbeiter_person_id');
		$oe_kurzbz = $this->input->post('oe_kurzbz');
		$funktion_kurzbz = $this->input->post('funktion_kurzbz');

		if (isEmptyString($fehlercode))
			$this->outputJsonError($this->p->t('fehlermonitoring', 'fehlercodeFehlt'));
		elseif (isEmptyString($mitarbeiter_person_id) && isEmptyString($oe_kurzbz))
			$this->outputJsonError($this->p->t('fehlermonitoring', 'mitarbeiterUndOeFehlt'));
		elseif (!isEmptyString($mitarbeiter_person_id) && !isEmptyString($oe_kurzbz))
			$this->outputJsonError($this->p->t('fehlermonitoring', 'nurOeOderMitarbeiterSetzen'));
		elseif (isset($mitarbeiter_person_id) && !is_numeric($mitarbeiter_person_id))
			$this->outputJsonError($this->p->t('fehlermonitoring', 'ungueltigeMitarbeiterId'));
		else
		{
			$data = array(
				'fehlercode' => $fehlercode
			);

			if (!isEmptyString($mitarbeiter_person_id))
				$data['person_id'] = $mitarbeiter_person_id;

			if (!isEmptyString($oe_kurzbz))
				$data['oe_kurzbz'] = $oe_kurzbz;

			if (!isEmptyString($funktion_kurzbz))
				$data['funktion_kurzbz'] = $funktion_kurzbz;

			$zustaendigkeitExistsRes = $this->FehlerzustaendigkeitenModel->loadWhere($data);

			if (isError($zustaendigkeitExistsRes))
				$this->outputJsonError(getError($zustaendigkeitExistsRes));
			elseif (hasData($zustaendigkeitExistsRes))
				$this->outputJsonError($this->p->t('fehlermonitoring', 'zustaendigkeitExistiert'));
			else
			{
				$data['insertvon'] = $this->_uid;

				$this->outputJson($this->FehlerzustaendigkeitenModel->insert($data));
			}
		}
	}

	/**
	 * Deletes a Zuständigkeit.
	 */
	public function deleteZustaendigkeit()
	{
		$fehlerzustaendigkeiten_id = $this->input->post('fehlerzustaendigkeiten_id');

		// check if Id correctly passed
		if (!isset($fehlerzustaendigkeiten_id) || !is_numeric($fehlerzustaendigkeiten_id))
		{
			$this->outputJsonError($this->p->t('fehlermonitoring', 'ungueltigeZustaendigkeitenId'));
			return;
		}

		$this->outputJson($this->FehlerzustaendigkeitenModel->delete($fehlerzustaendigkeiten_id));
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
