<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The controller LehrauftragAkzeptieren displays all Lehrauftraege of the logged in lector.
 * Lehrauftraege can be accepted by selecting them, entering the password and submitting them.
 */
class LehrauftragAkzeptieren extends Auth_Controller
{
	const APP = 'lehrauftrag';
	const LEHRAUFTRAG_URI = 'lehre/lehrauftrag/LehrauftragAkzeptieren';    // URL prefix for this controller
	const BERECHTIGUNG_LEHRAUFTRAG_AKZEPTIEREN = 'lehre/lehrauftrag_akzeptieren';

	private $_uid;  // uid of the logged user

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index' => 'lehre/lehrauftrag_akzeptieren:r',
				'acceptLehrauftrag' => 'lehre/lehrauftrag_akzeptieren:rw',
				'checkInkludierteLehre' => 'lehre/lehrauftrag_akzeptieren:rw'
			)
		);

		// Load models
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('accounting/Vertrag_model', 'VertragModel');
		$this->load->model('accounting/Vertragvertragsstatus_model', 'VertragvertragsstatusModel');
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('codex/Bisverwendung_model', 'BisverwendungModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('vertragsbestandteil/Dienstverhaeltnis_model', 'DienstverhaeltnisModel');

		// Load libraries
		$this->load->library('WidgetLib');
		$this->load->library('PermissionLib');
		$this->load->library('AuthLib');

		// Load helpers
		$this->load->helper('array');
		$this->load->helper('url');

		// Load language phrases
		$this->loadPhrases(
			array(
				'global',
				'ui',
				'lehre',
				'password',
				'dms',
				'table'
			)
		);

		$this->_setAuthUID(); // sets property uid

		$this->setControllerId(); // sets the controller id
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Main page of Lehrauftrag
	 */
	public function index()
	{
		// Set studiensemester selected for studiengang dropdown
		$studiensemester_kurzbz = $this->input->get('studiensemester'); // if provided by selected studiensemester
		if (is_null($studiensemester_kurzbz)) // else set next studiensemester as default value
		{
			$studiensemester = $this->StudiensemesterModel->getAktOrNextSemester();
			if (hasData($studiensemester))
			{
				$studiensemester_kurzbz = $studiensemester->retval[0]->studiensemester_kurzbz;
			}
			elseif (isError($studiensemester))
			{
				show_error(getError($studiensemester));
			}
		}

		// Check if user is external lector
		$this->MitarbeiterModel->addJoin('public.tbl_benutzer', 'uid = mitarbeiter_uid');
		$result = $this->MitarbeiterModel->loadWhere(array(
			'uid' => $this->_uid,
			'fixangestellt' => false,
			'personalnummer > ' => 0,
			'lektor' => true,
			'aktiv' => true
		));

		$is_external_lector = hasData($result) ? true : false;

		$view_data = array(
			'studiensemester_selected' => $studiensemester_kurzbz,
			'is_external_lector' => $is_external_lector
		);

		$this->load->view('lehre/lehrauftrag/acceptLehrauftrag.php', $view_data);
	}

	/**
	 * Set the contract status of Lehrauftrag to 'akzeptiert'.
	 * Performed on ajax call.
	 */
	public function acceptLehrauftrag()
	{
		// Verify password
		$password = $this->input->post('password');
		if (!isEmptyString($password))
		{
			$result = $this->authlib->checkUserAuthByUsernamePassword($this->_uid, $password);
			if (isError($result))
			{
				return $this->outputJsonError('Passwort ist inkorrekt');    // exit if password is incorrect
			}
		}
		else
		{
			return $this->outputJsonError('Passwort fehlt');
		}

		// Loop through lehraufträge
		$lehrauftrag_arr = $this->input->post('selected_data');

		if(is_array($lehrauftrag_arr))
		{
			foreach($lehrauftrag_arr as $lehrauftrag)
			{
				$vertrag_id = (!is_null($lehrauftrag['vertrag_id'])) ? $lehrauftrag['vertrag_id'] : null;

				// Check if user is entitled to accept this Lehrauftrag
				// * first retrieve person_id of the contract
				$this->VertragModel->addSelect('person_id');

				if ($result = getData($this->VertragModel->load($vertrag_id)))
				{
					// * then find the uid of that contracts person_id
					$this->BenutzerModel->addSelect('uid');

					if ($result = getData($this->BenutzerModel->getFromPersonId($result[0]->person_id)))
					{
						// * finally check uid of contract against the logged in user
						$account_found = false;
						foreach ($result as $row_accounts)
						{
							if ($row_accounts->uid == $this->_uid)
							{
								$account_found = true;
							}
						}

						if (!$account_found)
						{
							return $this->outputJsonError('Sie haben keine Berechtigung für einen Vertrag');
						}
					}
					else
					{
						return $this->outputJsonError('Fehler beim Laden der Benutzerdaten');
					}
				}
				else
				{
					return $this->outputJsonError('Fehler beim Laden des Vertrags');
				}

				// Set status to accepted
				$result = $this->VertragvertragsstatusModel->setStatus($vertrag_id, $this->_uid, 'akzeptiert');

				if ($result->retval)
				{
					$json []= array(
						'row_index' => $lehrauftrag['row_index'],
						'akzeptiert' => date('Y-m-d')
					);
				}
				else
				{
					return $this->outputJsonError($result->retval);
				}
			}

			// Output json to ajax
			if (isset($json) && !isEmptyArray($json))
			{
				$this->outputJsonSuccess($json);
			}
		}
		else
		{
			return $this->outputJsonError('Fehler beim Übertragen der Daten.');
		}
	}

	/**
	 * Check if lectors latest Verwendung has inkludierte Lehre
	 * - inkludierte_lehre is null OR 0: freelancer lector -> has NO inkludierte Lehre
	 * - inkludierte_lehre -1: fix employed lector -> has inkludierte Lehre (all inclusive)
	 * - inkludierte_lehre > 0: fix employed lector -> has inkludierte Lehre (value is amount of hours included)
	 */
	public function checkInkludierteLehre()
	{
		if(defined('DIENSTVERHAELTNIS_SUPPORT') && DIENSTVERHAELTNIS_SUPPORT)
		{
			// Bei neuer Vertragsstruktur wird nur anhand des echten DVs entschieden ob eine Anzeige
			// des Stundensatzes erfolgt oder nicht.
			$result = $this->DienstverhaeltnisModel->getDVByPersonUID($this->_uid, null, date('Y-m-d'));

			if (hasData($result))
			{
				$data = getData($result);
				foreach($data as $row)
				{
					if($row->vertragsart_kurzbz == 'echterdv')
						$this->outputJsonSuccess(true);
					else
						$this->outputJsonSuccess(false);
				}
			}
			else
			{
				$this->outputJsonError(getError($result));
			}
		}
		else
		{
			// DEPRECATED
			$result = $this->BisverwendungModel->getLast($this->_uid, false);

			if (hasData($result))
			{
				$this->outputJsonSuccess(!is_null($result->retval[0]->inkludierte_lehre) && $result->retval[0]->inkludierte_lehre != 0);
			}
			else
			{
				$this->outputJsonError(getError($result));
			}
		}
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid) show_error('User authentification failed');
	}

}
