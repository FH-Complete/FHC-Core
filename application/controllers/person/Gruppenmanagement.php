<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Page for managing groups of which user is the manager
 */
class Gruppenmanagement extends Auth_Controller
{
	private $_uid; // contains the UID of the logged user

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'lehre/gruppenmanager:r',
				'showBenutzergruppe' => 'lehre/gruppenmanager:r',
				'getBenutzer' => 'lehre/gruppenmanager:r',
				'getAllBenutzer' => 'lehre/gruppenmanager:r',
				'addBenutzer' => 'lehre/gruppenmanager:rw',
				'removeBenutzer' => 'lehre/gruppenmanager:rw'
			)
		);

		// Loads models
		$this->load->model('person/benutzer_model', 'BenutzerModel');
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('person/benutzergruppe_model', 'BenutzergruppeModel');
		$this->load->model('system/Log_model', 'LogModel');

		$this->load->library('WidgetLib');
		$this->loadPhrases(
			array(
				'global',
				'person',
				'lehre',
				'ui',
				'filter',
				'gruppenmanagement'
			)
		);

		$this->setControllerId(); // sets the controller id
		$this->_setAuthUID(); // sets property uid
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Main page
	 */
	public function index()
	{
		$this->load->view(
			'person/gruppenmanagement/gruppenmanagement.php',
			array('uid' => $this->_uid)
		);
	}

	/**
	 * Shows Benutzergruppe overview page.
	 */
	public function showBenutzergruppe()
	{
		$this->_setNavigationMenuShowDetails();
		$gruppe_kurzbz = $this->input->get('gruppe_kurzbz');

		$data[self::FHC_CONTROLLER_ID] = $this->getControllerId();

		$this->load->view(
			'person/gruppenmanagement/benutzergruppe.php',
			array('gruppe_kurzbz' => $gruppe_kurzbz)
		);
	}

	/**
	* Gets Benutzer assigned to a Gruppe
	*/
	public function getBenutzer()
	{
		$gruppe_kurzbz = $this->input->get('gruppe_kurzbz');

		$this->BenutzergruppeModel->addSelect('uid, vorname, nachname, ben.aktiv');
		$this->BenutzergruppeModel->addJoin('public.tbl_benutzer ben', 'uid');
		$this->BenutzergruppeModel->addJoin('public.tbl_person', 'person_id');
		$benutzerRes = $this->BenutzergruppeModel->loadWhere(array('gruppe_kurzbz' => $gruppe_kurzbz));

		$this->outputJson($benutzerRes);
	}

	/**
	* Gets all Benutzer for assignment to Gruppe
	*/
	public function getAllBenutzer()
	{
		$this->BenutzerModel->addSelect('uid, vorname, nachname');
		$this->BenutzerModel->addJoin('public.tbl_person', 'person_id');
		$benutzerRes = $this->BenutzerModel->loadWhere(
			array('tbl_benutzer.aktiv' => true)
		);
		$this->outputJson($benutzerRes);
	}

	/**
	 * Adds a Benutzer to Gruppe
	 */
	public function addBenutzer()
	{
		$uid = $this->input->post('uid');
		$gruppe_kurzbz = $this->input->post('gruppe_kurzbz');

		if (isEmptyString($uid))
			$result = error('Uid missing');
		else
		{
			$benutzerExistsRes = $this->BenutzergruppeModel->loadWhere(
				array(
					'uid' => $uid,
					'gruppe_kurzbz' => $gruppe_kurzbz
				)
			);

			if (isError($benutzerExistsRes))
			{
				$this->outputJsonError(getError($benutzerExistsRes));
				return;
			}

			if (hasData($benutzerExistsRes))
			{
				$this->outputJsonError($this->p->t('gruppenmanagement', 'benutzerSchonZugewiesen'));
				return;
			}

			$result = $this->BenutzergruppeModel->insert(
				array(
					'uid' => $uid,
					'gruppe_kurzbz' => $gruppe_kurzbz,
					'insertamum' => date('Y-m-d H:i:s'),
					'insertvon' => $this->_uid
				)
			);

			// log the group add
			$lastQry = $this->db->last_query();

			if (isSuccess($result))
			{
				$beschreibung = 'Gruppenmanagement: Nutzer zu Gruppe hinzugefügt';
				$this->_writeLog($this->_uid, $beschreibung, $lastQry);
			}
		}

		$this->outputJson($result);
	}

	/**
	 * Removes Benutzer from Gruppe
	 */
	public function removeBenutzer()
	{
		$uid = $this->input->post('uid');
		$gruppe_kurzbz = $this->input->post('gruppe_kurzbz');

		if (isEmptyString($uid))
			$result = error('Uid missing');
		else
		{
			$result = $this->BenutzergruppeModel->delete(
				array(
					'uid' => $uid,
					'gruppe_kurzbz' => $gruppe_kurzbz
				)
			);
		}

		// log the group remove
		$lastQry = $this->db->last_query();

		if (isSuccess($result))
		{
			$beschreibung = 'Gruppenmanagement: Nutzer aus Gruppe entfernt';
			$this->_writeLog($this->_uid, $beschreibung, $lastQry);
		}

		$this->outputJson($result);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 *  Define the navigation menu for the showDetails page
	 */
	private function _setNavigationMenuShowDetails()
	{
		$this->load->library('NavigationLib', array('navigation_page' => 'person/Gruppenmanagement/showBenutzergruppe'));

		$link = site_url('person/Gruppenmanagement');

		$this->navigationlib->setSessionMenu(
			array(
				'back' => $this->navigationlib->oneLevel(
					'Zurück',	// description
					$link,			// link
					array(),		// children
					'angle-left',	// icon
					true,			// expand
					null, 			// subscriptDescription
					null, 			// subscriptLinkClass
					null, 			// subscriptLinkValue
					'', 			// target
					1 				// sort
				)
			)
		);
	}

	/**
	 * Set uid of authentificated user
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid) show_error('User authentification failed');
	}

	/**
	 * Writes an entry in the log table
	 */
	private function _writeLog($uid, $beschreibung, $lastQry)
	{
		$mitarbeiterResult = $this->MitarbeiterModel->load(array('mitarbeiter_uid'=>$this->_uid));

		if(!isSuccess($mitarbeiterResult) || !hasData($mitarbeiterResult))
		{
			$uid = DUMMY_LEKTOR_UID;
			$beschreibung .= ': '.$this->_uid;
			$beschreibung = mb_substr($beschreibung, 0, 64);
		}

		$this->LogModel->insert(array(
			'mitarbeiter_uid' => $uid,
			'beschreibung' => $beschreibung,
			'sql' => $lastQry
		));
	}

}
