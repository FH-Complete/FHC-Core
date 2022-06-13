<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Page for managing groups of which user is the manager
 */
class Gruppenadministration extends Auth_Controller
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
		$this->load->model('person/benutzergruppe_model', 'BenutzergruppeModel');

		$this->load->library('WidgetLib');
		$this->loadPhrases(
			array(
				'global',
				'person',
				'lehre',
				'ui',
				'filter'
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
		//$this->_setNavigationMenuIndex(); // define the navigation menu for this page

		$this->load->view(
			'person/gruppenadministration/gruppenadministration.php',
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

		// $this->BenutzergruppeModel->addSelect('uid, vorname, nachname');
		// $this->BenutzergruppeModel->addJoin('public.tbl_benutzer', 'uid');
		// $this->BenutzergruppeModel->addJoin('public.tbl_person', 'person_id');
		// $benutzerRes = $this->BenutzergruppeModel->loadWhere(array('gruppe_kurzbz' => $gruppe_kurzbz));
		//
		// if (isError($benutzerRes))
		// 	show_error(getError($benutzerRes));
		//
		// $benutzer = hasData($benutzerRes) ? getData($benutzerRes) : array();

		$this->load->view(
			'person/gruppenadministration/benutzergruppe.php',
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
				$this->outputJsonError($this->p->t('gruppenadministration', 'benutzerSchonZugewiesen'));
				return;
			}

			$result = $this->BenutzergruppeModel->insert(
				array(
					'uid' => $uid,
					'gruppe_kurzbz' => $gruppe_kurzbz,
					'insertamum' => date('Y-m-d H:i:s')
				)
			);
		}

		$this->outputJson($result);
	}

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

		$this->outputJson($result);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 *  Define the navigation menu for the showDetails page
	 */
	private function _setNavigationMenuShowDetails()
	{
		$this->load->library('NavigationLib', array('navigation_page' => 'person/Gruppenadministration/showBenutzergruppe'));

		$link = site_url('person/Gruppenadministration');

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
	 *  Define the navigation menu for the showDetails page
	 */
	// private function _setNavigationMenuIndex()
	// {
	// 	$this->load->library('NavigationLib', array('navigation_page' => 'person/gruppenadministration/index'));
	//
	// 	$link = site_url();
	//
	// 	$this->navigationlib->setSessionMenu(
	// 		array(
	// 			'back' => $this->navigationlib->oneLevel(
	// 				'Zurück',	// description
	// 				$link,			// link
	// 				array(),		// children
	// 				'angle-left',	// icon
	// 				true,			// expand
	// 				null, 			// subscriptDescription
	// 				null, 			// subscriptLinkClass
	// 				null, 			// subscriptLinkValue
	// 				'', 			// target
	// 				1 				// sort
	// 			)
	// 		)
	// 	);
	// }

	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid) show_error('User authentification failed');
	}
}
