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
				'index' => 'admin:r',
				'showBenutzergruppe' => 'admin:r',
				'saveBPK' => 'admin:rw',
			)
		);

		// Loads models
		$this->load->model('organisation/benutzergruppe_model', 'BenutzergruppeModel');

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
			'organisation/gruppenadministration/gruppenadministration.php',
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

		$this->BenutzergruppeModel->addSelect('uid, vorname, nachname');
		$this->BenutzergruppeModel->addJoin('public.tbl_benutzer', 'uid');
		$this->BenutzergruppeModel->addJoin('public.tbl_person', 'person_id');
		$benutzer = $this->BenutzergruppeModel->loadWhere(array('gruppe_kurzbz' => $gruppe_kurzbz));

		$this->load->view(
			'organisation/gruppenadministration/benutzergruppe.php',
			array('gruppe_kurzbz' => $gruppe_kurzbz, 'benutzer' => $benutzer)
		);
	}

	/**
	 * Saves a ZGV for a prestudent, includes Ort, Datum, Nation for bachelor and master
	 */
	// public function saveBPK()
	// {
	// 	$person_id = $this->input->post('person_id');
	// 	$bpk = $this->input->post('bpk');
	//
	// 	if (isEmptyString($person_id))
	// 		$result = error('PersonID missing');
	// 	else
	// 	{
	// 		$result = $this->PersonModel->update(
	// 			$person_id,
	// 			array(
	// 				'bpk' => $bpk,
	// 				'updateamum' => date('Y-m-d H:i:s')
	// 			)
	// 		);
	// 		redirect('person/BPKWartung/index');
	// 	}
	// }

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 *  Define the navigation menu for the showDetails page
	 */
	private function _setNavigationMenuShowDetails()
	{
		$this->load->library('NavigationLib', array('navigation_page' => 'organisation/Gruppenadministration/showBenutzergruppe'));

		$link = site_url('organisation/Gruppenadministration');

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
	// 	$this->load->library('NavigationLib', array('navigation_page' => 'organisation/Gruppenadministration/index'));
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
