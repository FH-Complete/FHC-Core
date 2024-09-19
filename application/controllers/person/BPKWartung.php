<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Page for checking Persons where no bPK was found automatically
 */
class BPKWartung extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'admin:r',
				'showDetails' => 'admin:r',
				'saveBPK' => 'admin:rw',
			)
		);

		// Loads models
		$this->load->model('crm/akte_model', 'AkteModel');
		$this->load->model('person/person_model', 'PersonModel');
		$this->load->model('person/adresse_model', 'AdressModel');

		$this->load->library('WidgetLib');
		$this->loadPhrases(
			array(
				'global',
				'person',
				'lehre',
				'ui',
				'infocenter',
				'filter'
			)
		);

		$this->setControllerId(); // sets the controller id
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Main page of the bPK Wartung.
	 */
	public function index()
	{
		$this->_setNavigationMenuIndex(); // define the navigation menu for this page

		$this->load->view('person/bpk/bpkwartung.php');
	}

	/**
	 * bPK Details initialization function, gets person data and loads the view with the data.
	 */
	public function showDetails()
	{
		$this->_setNavigationMenuShowDetails();
		$person_id = $this->input->get('person_id');

		if (!is_numeric($person_id))
			show_error('person id is not numeric!');

		$personexists = $this->PersonModel->load($person_id);

		if (isError($personexists))
			show_error(getError($personexists));

		if (!hasData($personexists))
			show_error('Person does not exist!');

		$persondata = $this->_loadPersonData($person_id);


		$data[self::FHC_CONTROLLER_ID] = $this->getControllerId();

		$this->load->view('person/bpk/bpkDetails.php', $persondata);
	}

	/**
	 * Saves a bPK for a person.
	 */
	public function saveBPK()
	{
		$person_id = $this->input->post('person_id');
		$bpk = $this->input->post('bpk');

		if (isEmptyString($person_id))
			$result = error('PersonID missing');
		else
		{
			$result = $this->PersonModel->update(
				$person_id,
				array(
					'bpk' => $bpk,
					'updateamum' => date('Y-m-d H:i:s')
				)
			);
			redirect('person/BPKWartung/index');
		}
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Loads all necessary Person data.
	 * @param $person_id
	 * @return array
	 */
	private function _loadPersonData($person_id)
	{
		$stammdaten = $this->PersonModel->getPersonStammdaten($person_id, true);

		if (isError($stammdaten))
		{
			show_error(getError($stammdaten));
		}

		if (!isset($stammdaten->retval))
			return null;

		$adresse = $this->AdressModel->getZustellAdresse($person_id);

		if (isError($adresse))
		{
			show_error(getError($adresse));
		}

		$data = array(
			'stammdaten' => $stammdaten->retval,
			'adresse' => $adresse->retval[0]
		);

		return $data;
	}

	/**
	 *  Define the navigation menu for the showDetails page
	 */
	private function _setNavigationMenuShowDetails()
	{
		$this->load->library('NavigationLib', array('navigation_page' => 'person/BPKWartung/showDetails'));

		$link = site_url('person/BPKWartung');

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
	private function _setNavigationMenuIndex()
	{
		$this->load->library('NavigationLib', array('navigation_page' => 'person/BPKWartung/index'));

		$link = site_url();

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
}
