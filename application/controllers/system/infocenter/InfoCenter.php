<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Shows infocenter-related data for a person and its prestudents, enables document and zgv checks,
 * displays and saves Notizen for a person, logs infocenter-related actions for a person
 */
class InfoCenter extends Auth_Controller
{
	// App and Verarbeitungstaetigkeit name for logging
	const APP = 'infocenter';
	const TAETIGKEIT = 'bewerbung';

	const INFOCENTER_URI = 'system/infocenter/InfoCenter'; // URL prefix for this controller
	const INDEX_PAGE = 'index';
	const FREIGEGEBEN_PAGE = 'freigegeben';
	const SHOW_DETAILS_PAGE = 'showDetails';

	const NAVIGATION_PAGE = 'navigation_page';
	const ORIGIN_PAGE = 'origin_page';

	private $_uid; // contains the UID of the logged user

	// Used to log with PersonLogLib
	private $_logparams = array(
		'saveformalgep' => array(
			'logtype' => 'Action',
			'name' => 'Document formally checked',
			'message' => 'Document %s formally checked, set to %s',
			'success' => null
		),
		'savezgv' => array(
			'logtype' => 'Action',
			'name' => 'ZGV saved',
			'message' => 'ZGV saved for degree program %s, prestudentid %s',
			'success' => null
		),
		'abgewiesen' => array(
			'logtype' => 'Processstate',
			'name' => 'Interessent rejected',
			'message' => 'Interessent with prestudentid %s was rejected for degree program %s, reason: %s'
		),
		'freigegeben' => array(
			'logtype' => 'Processstate',
			'name' => 'Interessent confirmed',
			'message' => 'Status Interessent for prestudentid %s was confirmed for degree program %s'
		),
		'savenotiz' => array(
			'logtype' => 'Action',
			'name' => 'Note added',
			'message' => 'Note with title %s was added',
			'success' => null
		),
		'updatenotiz' => array(
			'logtype' => 'Action',
			'name' => 'Note updated',
			'message' => 'Note with title %s was updated',
			'success' => null
		)
	);

	/**
	 * Constructor
	 */
	public function __construct()
    {
        parent::__construct(
			array(
				'index' => 'infocenter:r',
				'freigegeben' => 'infocenter:r',
				'showDetails' => 'infocenter:r',
				'unlockPerson' => 'infocenter:rw',
				'saveFormalGeprueft' => 'infocenter:rw',
				'getLastPrestudentWithZgvJson' => 'infocenter:r',
				'getZgvInfoForPrestudent' => 'infocenter:r',
				'saveZgvPruefung' => 'infocenter:rw',
				'saveAbsage' => 'infocenter:rw',
				'saveFreigabe' => 'infocenter:rw',
				'saveNotiz' => 'infocenter:rw',
				'updateNotiz' => 'infocenter:rw',
				'reloadNotizen' => 'infocenter:r',
				'reloadLogs' => 'infocenter:r',
				'outputAkteContent' => 'infocenter:r',
				'getParkedDate' => 'infocenter:r',
				'park' => 'infocenter:rw',
				'unpark' => 'infocenter:rw',
				'getStudienjahrEnd' => 'infocenter:r',
				'setNavigationMenuArrayJson' => 'infocenter:r'
			)
		);

		// Loads models
		$this->load->model('crm/akte_model', 'AkteModel');
		$this->load->model('crm/prestudent_model', 'PrestudentModel');
		$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
		$this->load->model('crm/statusgrund_model', 'StatusgrundModel');
		$this->load->model('person/notiz_model', 'NotizModel');
		$this->load->model('person/person_model', 'PersonModel');
		$this->load->model('system/message_model', 'MessageModel');
		$this->load->model('system/filters_model', 'FiltersModel');
		$this->load->model('system/personLock_model', 'PersonLockModel');

		// Loads libraries
		$this->load->library('PersonLogLib');
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

		$this->_setAuthUID(); // sets property uid

		$this->setControllerId(); // sets the controller id
    }

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Main page of the InfoCenter tool
	 */
	public function index()
	{
		$this->_setNavigationMenuIndex(); // define the navigation menu for this page

		$this->load->view('system/infocenter/infocenter.php');
	}

	/**
	 * Freigegeben page of the InfoCenter tool
	 */
	public function freigegeben()
	{
		$this->_setNavigationMenuFreigegeben(); // define the navigation menu for this page

		$this->load->view('system/infocenter/infocenterFreigegeben.php');
	}

	/**
	 * Personal details page of the InfoCenter tool
	 * Initialization function, gets person and prestudent data and loads the view with the data
	 * @param $person_id
	 */
	public function showDetails()
	{
		$this->_setNavigationMenuShowDetails();

		$person_id = $this->input->get('person_id');

		if (!is_numeric($person_id))
			show_error('person id is not numeric!');

		$personexists = $this->PersonModel->load($person_id);

		if (isError($personexists))
			show_error($personexists->retval);

		if (empty($personexists->retval))
			show_error('person does not exist!');

		$origin_page = $this->input->get(self::ORIGIN_PAGE);
		if ($origin_page == self::INDEX_PAGE)
		{
			// mark person as locked for editing
			$result = $this->PersonLockModel->lockPerson($person_id, $this->_uid, self::APP);

			if (isError($result))
				show_error($result->retval);
		}

		$persondata = $this->_loadPersonData($person_id);
		$prestudentdata = $this->_loadPrestudentData($person_id);

		$data = array_merge(
			$persondata,
			$prestudentdata
		);

		$data[self::FHC_CONTROLLER_ID] = $this->getControllerId();
		$data[self::ORIGIN_PAGE] = $origin_page;

		$this->load->view('system/infocenter/infocenterDetails.php', $data);
	}

	/**
	 * Unlocks page from edit by a person, redirects to overview filter page
	 * @param $person_id
	 */
	public function unlockPerson($person_id)
	{
		$result = $this->PersonLockModel->unlockPerson($person_id, self::APP);

		if (isError($result))
			show_error($result->retval);

		redirect('/'.self::INFOCENTER_URI.'?'.self::FHC_CONTROLLER_ID.'='.$this->getControllerId());
	}

	/**
	 * Saves if a document has been formal geprueft. Saves current timestamp if checked as geprueft, or null if not.
	 * @param $person_id
	 */
	public function saveFormalGeprueft($person_id)
	{
		$akte_id = $this->input->post('akte_id');
		$formalgeprueft = $this->input->post('formal_geprueft');

		$json = false;

		if (isset($akte_id) && isset($formalgeprueft) && isset($person_id))
		{
			$akte = $this->AkteModel->load($akte_id);

			if (hasData($akte))
			{
				$timestamp = ($formalgeprueft === 'true') ? date('Y-m-d H:i:s') : null;
				$result = $this->AkteModel->update($akte_id, array('formal_geprueft_amum' => $timestamp));

				if (isSuccess($result))
				{
					$json = $timestamp;

					$this->_log(
						$person_id,
						'saveformalgep',
						array(
							empty($akte->retval[0]->titel) ? $akte->retval[0]->bezeichnung : $akte->retval[0]->titel,
							is_null($timestamp) ? 'NULL' : $timestamp
						)
					);
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 * Gets prestudent that was last modified in json format, for ZGV übernehmen
	 * @param $person_id
	 */
	public function getLastPrestudentWithZgvJson($person_id)
	{
		$prestudent = $this->PrestudentModel->getLastPrestudent($person_id, true);

		$this->output->set_content_type('application/json')->set_output(json_encode($prestudent));
	}

	/**
	 * Gets Zugangsvoraussetzungen for a prestudent as a description text and shows them in a view
	 * @param $prestudent_id
	 */
	public function getZgvInfoForPrestudent($prestudent_id)
	{
		$studienordnung = $this->PrestudentstatusModel->getStudienordnungWithZgvText($prestudent_id);

		$prestudentdata = $this->_getPersonAndStudiengangFromPrestudent($prestudent_id);
		$studiengangkurzbz = $prestudentdata['studiengang_kurzbz'];
		$studiengangbezeichnung = $prestudentdata['studiengang_bezeichnung'];

		$data = array(
					'studiengang_bezeichnung' => $studiengangbezeichnung,
					'studiengang_kurzbz' => $studiengangkurzbz,
					'data' => null
				);

		if (hasData($studienordnung))
		{
			$data['data'] = $studienordnung->retval[0]->data;
		}

		$this->load->view('system/infocenter/studiengangZgvInfo.php', $data);
	}

	/**
	 * Saves a ZGV for a prestudent, includes Ort, Datum, Nation for bachelor and master
	 * @param $prestudent_id
	 */
	public function saveZgvPruefung()
	{
		$prestudent_id = $this->input->post('prestudentid');

		if (empty($prestudent_id))
			$result = error('Prestudentid missing');
		else
		{
			// zgvdata
			// Check for string null, in case dropdown changed to default value
			$zgv_code = $this->input->post('zgv') === 'null' ? null : $this->input->post('zgv');
			$zgvort = $this->input->post('zgvort');
			$zgvdatum = $this->input->post('zgvdatum');
			$zgvdatum = empty($zgvdatum) ? null : date_format(date_create($zgvdatum), 'Y-m-d');
			$zgvnation_code = $this->input->post('zgvnation') === 'null' ? null : $this->input->post('zgvnation');

			//zgvmasterdata
			$zgvmas_code = $this->input->post('zgvmas') === 'null' ? null : $this->input->post('zgvmas');
			$zgvmaort = $this->input->post('zgvmaort');
			$zgvmadatum = $this->input->post('zgvmadatum');
			$zgvmadatum = empty($zgvmadatum) ? null : date_format(date_create($zgvmadatum), 'Y-m-d');
			$zgvmanation_code = $this->input->post('zgvmanation') === 'null' ? null : $this->input->post('zgvmanation');

			$result = $this->PrestudentModel->update(
				$prestudent_id,
				array(
					'zgv_code' => $zgv_code,
					'zgvort' => $zgvort,
					'zgvdatum' => $zgvdatum,
					'zgvnation' => $zgvnation_code,
					'zgvmas_code' => $zgvmas_code,
					'zgvmaort' => $zgvmaort,
					'zgvmadatum' => $zgvmadatum,
					'zgvmanation' => $zgvmanation_code,
					'updateamum' => date('Y-m-d H:i:s')
				)
			);

			if (isSuccess($result))
			{
				//get extended Prestudent data for logging
				$logdata = $this->_getPersonAndStudiengangFromPrestudent($prestudent_id);

				$this->_log($logdata['person_id'], 'savezgv', array($logdata['studiengang_kurzbz'], $prestudent_id));
			}
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	/**
	 * Saves Absage for Prestudent including the reason for the Absage (statusgrund).
	 * inserts Studiensemester and Ausbildungssemester for the new Absage of (chronologically) last status.
	 * @param $prestudent_id
	 */
	public function saveAbsage($prestudent_id)
	{
		$statusgrund = $this->input->post('statusgrund');

		$lastStatus = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if (isError($lastStatus))
		{
			show_error($lastStatus->retval);
		}

		//check if still Interessent and not freigegeben yet
		if (count($lastStatus->retval) > 0 && $lastStatus->retval[0]->status_kurzbz === 'Interessent' && !isset($lastStatus->retval[0]->bestaetigtam))
		{
			$result = $this->PrestudentstatusModel->insert(
				array(
					'prestudent_id' => $prestudent_id,
					'studiensemester_kurzbz' => $lastStatus->retval[0]->studiensemester_kurzbz,
					'ausbildungssemester' => $lastStatus->retval[0]->ausbildungssemester,
					'datum' => date('Y-m-d'),
					'orgform_kurzbz' => $lastStatus->retval[0]->orgform_kurzbz,
					'studienplan_id' => $lastStatus->retval[0]->studienplan_id,
					'status_kurzbz' => 'Abgewiesener',
					'statusgrund_id' => $statusgrund,
					'insertvon' => $this->_uid,
					'insertamum' => date('Y-m-d H:i:s')
				)
			);

			if (isError($result))
			{
				show_error($result->retval);
			}

			$logdata = $this->_getPersonAndStudiengangFromPrestudent($prestudent_id);

			//statusgrund bezeichnung for logging
			$this->StatusgrundModel->addSelect('bezeichnung_mehrsprachig');
			$result = $this->StatusgrundModel->load($statusgrund);
			if (isError($result))
			{
				show_error($result->retval);
			}

			$statusgrund_bez = $result->retval[0]->bezeichnung_mehrsprachig[1];

			$this->_log($logdata['person_id'], 'abgewiesen', array($prestudent_id, $logdata['studiengang_kurzbz'], $statusgrund_bez));
		}
		$this->_redirectToStart($prestudent_id, 'ZgvPruef');
	}

	/**
	 * Saves Freigabe of a Prestudent to the Studiengang.
	 * updates bestaetigtam and bestaetigtvon fields of the last status
	 * @param $prestudent_id
	 */
	public function saveFreigabe($prestudent_id)
	{
		$lastStatus = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if (isError($lastStatus))
		{
			show_error($lastStatus->retval);
		}

		if (count($lastStatus->retval) > 0)
		{
			$lastStatus = $lastStatus->retval[0];

			//check if still Interessent and not freigegeben yet
			if ($lastStatus->status_kurzbz === 'Interessent' && !isset($lastStatus->bestaetigtam))
			{
				$result = $this->PrestudentstatusModel->update(
					array(
						'prestudent_id' => $prestudent_id,
						'status_kurzbz' => $lastStatus->status_kurzbz,
						'studiensemester_kurzbz' => $lastStatus->studiensemester_kurzbz,
						'ausbildungssemester' => $lastStatus->ausbildungssemester
					),
					array(
						'bestaetigtvon' => $this->_uid,
						'bestaetigtam' => date('Y-m-d'),
						'updatevon' => $this->_uid,
						'updateamum' => date('Y-m-d H:i:s')
					)
				);

				if (isError($result))
				{
					show_error($result->retval);
				}

				$this->load->model('crm/dokumentprestudent_model', 'DokumentprestudentModel');

				$logdata = $this->_getPersonAndStudiengangFromPrestudent($prestudent_id);

				//set documents which have been formal geprüft to accepted
				$result = $this->AkteModel->loadWhere(array('person_id' => $logdata['person_id'], 'formal_geprueft_amum !=' => NULL));

				if (isError($result))
				{
					show_error($result->retval);
				}

				$dokument_kurzbzs = array();

				foreach ($result->retval as $akte)
				{
					$dokument_kurzbzs[] = $akte->dokument_kurzbz;
				}

				$result = $this->DokumentprestudentModel->setAcceptedDocuments($prestudent_id, $dokument_kurzbzs);

				//returns null if no documents to accept
				if ($result !== null && isError($result))
				{
					show_error($result->retval);
				}

				$this->_sendFreigabeMail($prestudent_id);

				$this->_log($logdata['person_id'], 'freigegeben', array($prestudent_id, $logdata['studiengang_kurzbz']));
			}
		}

		$this->_redirectToStart($prestudent_id, 'ZgvPruef');
	}

	/**
	 * Saves a new Notiz for a person
	 * @param $person_id
	 */
	public function saveNotiz($person_id)
	{
		$titel = $this->input->post('notiztitel');
		$text = $this->input->post('notiz');
		$erledigt = false;

		$result = $this->NotizModel->addNotizForPerson($person_id, $titel, $text, $erledigt, $this->_uid);

		if (isSuccess($result))
		{
			$this->_log($person_id, 'savenotiz', array($titel));
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	/**
	 * Updates a new Notiz for a person
	 * @param int $notiz_id
	 * @param int $person_id
	 * @return bool true if success
	 */
	public function updateNotiz($notiz_id, $person_id)
	{
		$titel = $this->input->post('notiztitel');
		$text = $this->input->post('notiz');

		$result = $this->NotizModel->update(
			$notiz_id,
			array(
				'titel' => $titel,
				'text' => $text,
				'verfasser_uid' => $this->_uid,
				"updateamum" => 'NOW()',
				"updatevon" => $this->_uid
			)
		);

		if (isSuccess($result))
		{
			//set log "Notiz updated"
			$this->_log($person_id, 'updatenotiz', array($titel));
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	/**
	 * Loads Notizen view for a person, helper for reloading after ajax request
	 * @param $person_id
	 */
	public function reloadNotizen($person_id)
	{
		$notizen = $this->NotizModel->getNotiz($person_id);

		if (isError($notizen))
		{
			show_error($notizen->retval);
		}

		$this->load->view('system/infocenter/notizen.php', array('notizen' => $notizen->retval));
	}

	/**
	 * Loads Logs view for a person, helper for reloading after ajax request
	 * @param $person_id
	 */
	public function reloadLogs($person_id)
	{
		$logs = $this->personloglib->getLogs($person_id);
		$this->load->view('system/infocenter/logs.php', array('logs' => $logs));
	}

	/**
	 * Outputs content of an Akte, sends appropriate headers (so the document can be downloaded)
	 * @param $akte_id
	 */
	public function outputAkteContent($akte_id)
	{
		$this->load->library('DmsLib');

		$akte = $this->AkteModel->load($akte_id);

		if (isError($akte))
		{
			show_error($akte->retval);
		}

		$aktecontent = $this->dmslib->getAkteContent($akte_id);

		if (isError($aktecontent))
		{
			show_error($aktecontent->retval);
		}

		$this->output
			->set_status_header(200)
			->set_content_type($akte->retval[0]->mimetype, 'utf-8')
			->set_header('Content-Disposition: attachment; filename="'.$akte->retval[0]->titel.'"')
			->set_output($aktecontent->retval)
			->_display();
	}

	/**
	 * Gets the date until which a person is parked
	 * @param $person_id
	 */
	public function getParkedDate($person_id)
	{
		$result = $this->personloglib->getParkedDate($person_id);

		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	/**
	 * Initializes parking of a person, i.e. a person is not expected to do any actions while parked
	 */
	public function park()
	{
		$person_id = $this->input->post('person_id');
		$date = $this->input->post('parkdate');

		$result = $this->personloglib->park($person_id, date_format(date_create($date), 'Y-m-d'), self::TAETIGKEIT, self::APP, null, $this->_uid);

		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	/**
	 * Removes parking of a person
	 */
	public function unPark()
	{
		$person_id = $this->input->post('person_id');

		$result = $this->personloglib->unPark($person_id);

		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	/**
	 * Gets the End date of the current Studienjahr
	 */
	public function getStudienjahrEnd()
	{
		$this->load->model('organisation/studienjahr_model', 'StudienjahrModel');

		$result = $this->StudienjahrModel->getCurrStudienjahr();

		$json = false;

		if (hasData($result))
		{
			$json = $result->retval[0]->ende;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 * Wrapper for setNavigationMenu, returns JSON message
	 */
	public function setNavigationMenuArrayJson()
	{
		$navigation_page = $this->input->get(self::NAVIGATION_PAGE);

		if (strpos($navigation_page, self::INDEX_PAGE) !== false)
		{
			$this->_setNavigationMenuIndex();
		}
		else
		{
			$this->_setNavigationMenuFreigegeben();
		}

		$this->outputJsonSuccess('success');
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

	/**
	 *  Define the navigation menu for the index page
	 */
	private function _setNavigationMenuIndex()
	{
		$this->load->library('NavigationLib', array(self::NAVIGATION_PAGE => self::INFOCENTER_URI.'/'.self::INDEX_PAGE));

		$listFiltersSent = array();
		$listFiltersNotSent = array();
		$listCustomFilters = array();

		$filtersSent = $this->FiltersModel->getFilterList('infocenter', 'PersonActions', '%InfoCenterSentApplication%');
		if (hasData($filtersSent))
		{
			for ($filtersCounter = 0; $filtersCounter < count($filtersSent->retval); $filtersCounter++)
			{
				$filter = $filtersSent->retval[$filtersCounter];

				$listFiltersSent[$filter->filter_id] = $filter->description[0];
			}
		}

		$filtersNotSent = $this->FiltersModel->getFilterList('infocenter', 'PersonActions', '%InfoCenterNotSentApplication%');
		if (hasData($filtersNotSent))
		{
			for ($filtersCounter = 0; $filtersCounter < count($filtersNotSent->retval); $filtersCounter++)
			{
				$filter = $filtersNotSent->retval[$filtersCounter];

				$listFiltersNotSent[$filter->filter_id] = $filter->description[0];
			}
		}

		$customFilters = $this->FiltersModel->getCustomFiltersList('infocenter', 'PersonActions', $this->_uid);
		if (hasData($customFilters))
		{
			for ($filtersCounter = 0; $filtersCounter < count($customFilters->retval); $filtersCounter++)
			{
				$filter = $customFilters->retval[$filtersCounter];

				$listCustomFilters[$filter->filter_id] = $filter->description[0];
			}
		}

		$filtersArray = array();

		$filtersArray['abgeschickt'] = $this->navigationlib->oneLevel(
			ucfirst($this->p->t('global', 'abgeschickt')), 	// description
			'#',											// link
			array(),										// children
			'',												// icon
			true											// expand
		);

		$filtersArray['nichtabgeschickt'] = $this->navigationlib->oneLevel(
			ucfirst($this->p->t('global', 'nichtAbgeschickt')),	// description
			'#',												// link
			array(),											// children
			'',													// icon
			true												// expand
		);

		$this->_fillFilters($listFiltersSent, $filtersArray['abgeschickt']);
		$this->_fillFilters($listFiltersNotSent, $filtersArray['nichtabgeschickt']);

		if (count($listCustomFilters) > 0)
		{
			$filtersArray['personal'] = $this->navigationlib->oneLevel(
				'Personal filters',	// description
				'#',				// link
				array(),			// children
				'',					// icon
				true				// expand
			);

			$this->_fillCustomFilters($listCustomFilters, $filtersArray['personal']);
		}

		$this->navigationlib->setSessionMenu(
			array(
				'filters' => $this->navigationlib->oneLevel(
					'Filter',		// description
					'#',			// link
					$filtersArray,	// children
					'',				// icon
					true			// expand
				)
			)
		);
	}

	/**
	 *  Define the navigation menu for the showDetails page
	 */
	private function _setNavigationMenuShowDetails()
	{
		$this->load->library('NavigationLib', array(self::NAVIGATION_PAGE => self::INFOCENTER_URI.'/'.self::SHOW_DETAILS_PAGE));

		$origin_page = $this->input->get(self::ORIGIN_PAGE);

		$link = site_url(self::INFOCENTER_URI.'/'.self::INDEX_PAGE);
		if ($origin_page == self::FREIGEGEBEN_PAGE)
		{
			$link = site_url(self::INFOCENTER_URI.'/'.self::FREIGEGEBEN_PAGE);
		}

		$this->navigationlib->setSessionMenu(
			array(
				'back' => $this->navigationlib->oneLevel(
					'Zurück',	// description
					$link,			// link
					array(),		// children
					'angle-left',	// icon
					true			// expand
				)
			)
		);
	}

	/**
	 *  Define the navigation menu for the freigegeben page
	 */
	private function _setNavigationMenuFreigegeben()
	{
		$this->load->library('NavigationLib', array(self::NAVIGATION_PAGE => self::INFOCENTER_URI.'/'.self::FREIGEGEBEN_PAGE));

		$listFilters = array();
		$listCustomFilters = array();

		$filters = $this->FiltersModel->getFilterList('infocenter', 'PersonActions', '%InfoCenterFreigegeben%');
		if (hasData($filters))
		{
			for ($filtersCounter = 0; $filtersCounter < count($filters->retval); $filtersCounter++)
			{
				$filter = $filters->retval[$filtersCounter];

				$listFilters[$filter->filter_id] = $filter->description[0];
			}
		}

		$customFilters = $this->FiltersModel->getCustomFiltersList('infocenter', 'PersonActions', $this->_uid);
		if (hasData($customFilters))
		{
			for ($filtersCounter = 0; $filtersCounter < count($customFilters->retval); $filtersCounter++)
			{
				$filter = $customFilters->retval[$filtersCounter];

				$listCustomFilters[$filter->filter_id] = $filter->description[0];
			}
		}

		$filtersArray = array();
		$filtersArray['children'] = array();

		$this->_fillFiltersFreigegeben($listFilters, $filtersArray);

		if (count($listCustomFilters) > 0)
		{
			$filtersArray['children']['personal'] = $this->navigationlib->oneLevel(
				'Personal filters',	// description
				'#',				// link
				array(),			// children
				'',					// icon
				true				// expand
			);

			$this->_fillCustomFilters($listCustomFilters, $filtersArray['children']['personal']);
		}

		$this->navigationlib->setSessionMenu(
			array(
				'filters' => $this->navigationlib->oneLevel(
					'Filter',					// description
					'#',						// link
					(isset($filtersArray['children'])?$filtersArray['children']:''),	// children
					'',							// icon
					true						// expand
				)
			)
		);
	}

	/**
	 * Utility method used to fill elements of the InfoCenter left menu of the main InfoCenter page
	 */
	private function _fillFilters($filters, &$tofill)
	{
		$toPrint = "%s?%s=%s&%s=%s";

		foreach ($filters as $filterId => $description)
		{
			$tofill['children'][] = array(
				'link' => sprintf(
					$toPrint,
					site_url(self::INFOCENTER_URI), 'filter_id', $filterId,
					FHC_Controller::FHC_CONTROLLER_ID,
					$this->getControllerId()
				),
				'description' => $description
			);
		}
	}

	/**
	 * Utility method used to fill elements of the InfoCenter left menu of the freigegeben InfoCenter page
	 */
	private function _fillFiltersFreigegeben($filters, &$tofill)
	{
		$toPrint = "%s?%s=%s&%s=%s";

		foreach ($filters as $filterId => $description)
		{
			$tofill['children'][] = array(
				'link' => sprintf(
					$toPrint,
					site_url(self::INFOCENTER_URI.'/'.self::FREIGEGEBEN_PAGE), 'filter_id', $filterId,
					FHC_Controller::FHC_CONTROLLER_ID,
					$this->getControllerId()
				),
				'description' => $description
			);
		}
	}

	/**
	 * Utility method used to fill elements of the InfoCenter left menu
	 * with the list of the custom filter of the authenticated user
	 */
	private function _fillCustomFilters($filters, &$tofill)
	{
		$toPrint = "%s?%s=%s&%s=%s";

		foreach ($filters as $filterId => $description)
		{
			$tofill['children'][] = array(
				'link' => sprintf(
					$toPrint,
					site_url(self::INFOCENTER_URI), 'filter_id', $filterId,
					FHC_Controller::FHC_CONTROLLER_ID,
					$this->getControllerId()
				),
				'description' => $description,
				'subscriptDescription' => 'Remove',
				'subscriptLinkClass' => 'remove-custom-filter',
				'subscriptLinkValue' => $filterId
			);
		}
	}

	/**
	 * Loads all necessary Person data: Stammdaten (name, svnr, contact, ...), Dokumente, Logs and Notizen
	 * @param $person_id
	 * @return array
	 */
	private function _loadPersonData($person_id)
	{
		$locked = $this->PersonLockModel->checkIfLocked($person_id, self::APP);

		if (isError($locked))
		{
			show_error($locked->retval);
		}

		$lockedby = null;

		//mark red if locked by other user
		$lockedbyother = false;

		if (isset($locked->retval[0]->uid))
		{
			$lockedby = $locked->retval[0]->uid;
			if ($lockedby !== $this->_uid)
				$lockedbyother = true;
		}

		$stammdaten = $this->PersonModel->getPersonStammdaten($person_id, true);

		if (isError($stammdaten))
		{
			show_error($stammdaten->retval);
		}

		if (!isset($stammdaten->retval))
			return null;

		$dokumente = $this->AkteModel->getAktenWithDokInfo($person_id, null, false);

		if (isError($dokumente))
		{
			show_error($dokumente->retval);
		}

		$dokumente_nachgereicht = $this->AkteModel->getAktenWithDokInfo($person_id, null, true);

		if (isError($dokumente_nachgereicht))
		{
			show_error($dokumente_nachgereicht->retval);
		}

		$messages = $this->MessageModel->getMessagesOfPerson($person_id, 1);

		if (isError($messages))
		{
			show_error($messages->retval);
		}

		$logs = $this->personloglib->getLogs($person_id);

		$notizen = $this->NotizModel->getNotiz($person_id);

		if (isError($notizen))
		{
			show_error($notizen->retval);
		}

		$notizen_bewerbung = $this->NotizModel->getNotizByTitel($person_id, 'Anmerkung zur Bewerbung');

		if (isError($notizen_bewerbung))
		{
			show_error($notizen_bewerbung->retval);
		}

		$user_person = $this->PersonModel->getByUid($this->_uid);

		if (isError($user_person))
		{
			show_error($user_person->retval);
		}

		$messagelink = site_url('/system/Messages/write/'.$user_person->retval[0]->person_id);

		$data = array (
			'lockedby' => $lockedby,
			'lockedbyother' => $lockedbyother,
			'stammdaten' => $stammdaten->retval,
			'dokumente' => $dokumente->retval,
			'dokumente_nachgereicht' => $dokumente_nachgereicht->retval,
			'messages' => $messages->retval,
			'logs' => $logs,
			'notizen' => $notizen->retval,
			'notizenbewerbung' => $notizen_bewerbung->retval,
			'messagelink' => $messagelink
		);

		return $data;
	}

	/**
	 * Loads all necessary Prestudent data: Zgv data, Statusgruende
	 * @param $person_id
	 * @return array
	 */
	private function _loadPrestudentData($person_id)
	{
		$zgvpruefungen = array();

		$prestudenten = $this->PrestudentModel->loadWhere(array('person_id' => $person_id));

		if (isError($prestudenten))
		{
			show_error($prestudenten->retval);
		}

		foreach ($prestudenten->retval as $prestudent)
		{
			$prestudent = $this->PrestudentModel->getPrestudentWithZgv($prestudent->prestudent_id);

			if (isError($prestudent))
			{
				show_error($prestudent->retval);
			}

			$zgvpruefung = $prestudent->retval[0];

			if (isset($zgvpruefung->prestudentstatus))
			{
				$position = strpos($zgvpruefung->prestudentstatus->anmerkung, 'Alt:');

				//parse Anmerkung for Alternative (Prio is given in orgform and sprache anyway)
				$zgvpruefung->prestudentstatus->alternative = is_numeric($position) ? substr($zgvpruefung->prestudentstatus->anmerkung, $position) : null;
			}
			//if prestudent is not interessent or is already bestaetigt, then show only as information, non-editable
			$zgvpruefung->infoonly = !isset($zgvpruefung->prestudentstatus) || isset($zgvpruefung->prestudentstatus->bestaetigtam) || $zgvpruefung->prestudentstatus->status_kurzbz != 'Interessent';

			$zgvpruefungen[] = $zgvpruefung;
		}

		// Interessenten come first, otherwise by bewerbungsdatum desc, then by prestudent_id desc
		usort($zgvpruefungen, function ($a, $b) {
			$bewdatesort = isset($a->prestudentstatus) && isset($b->prestudentstatus) ? strcmp($b->prestudentstatus->bewerbung_abgeschicktamum, $a->prestudentstatus->bewerbung_abgeschicktamum) : 0;
			$defaultsort = $bewdatesort === 0 ? (int)$b->prestudent_id - (int)$a->prestudent_id : $bewdatesort;
			if (!isset($a->prestudentstatus->status_kurzbz) || !isset($b->prestudentstatus->status_kurzbz))
				return $defaultsort;
			elseif ($a->prestudentstatus->status_kurzbz === 'Interessent' && $b->prestudentstatus->status_kurzbz === 'Interessent')
			{
				//infoonly Interessenten come after new Interessenten
				if ($a->infoonly === $b->infoonly)
					return $defaultsort;
				elseif ($a->infoonly)
					return 1;
				elseif ($b->infoonly)
					return -1;
			}
			elseif ($a->prestudentstatus->status_kurzbz === 'Interessent')
				return -1;
			elseif ($b->prestudentstatus->status_kurzbz === 'Interessent')
				return 1;
			else
				return $defaultsort;
		});

		$statusgruende = $this->StatusgrundModel->loadWhere(array('status_kurzbz' => 'Abgewiesener'))->retval;

		$data = array (
			'zgvpruefungen' => $zgvpruefungen,
			'statusgruende' => $statusgruende
		);

		return $data;
	}

	/**
	 * Helper function for redirecting to initial page for person from a prestudent-specific page
	 * @param $prestudent_id
	 * @param $section optional section of the page to go to
	 */
	private function _redirectToStart($prestudent_id, $section = '')
	{
		$this->PrestudentModel->addSelect('person_id');
		$person_id = $this->PrestudentModel->load($prestudent_id)->retval[0]->person_id;

		redirect(
			sprintf(
				'/%s/%s?%s=%s&%s=%s#%s',
				self::INFOCENTER_URI,
				self::SHOW_DETAILS_PAGE,
				'person_id',
				$person_id,
				self::FHC_CONTROLLER_ID,
				$this->getControllerId(),
				$section
			)
		);
	}

	/**
	 * Helper function retrieves personid and studiengang kurzbz from a prestudent id
	 * @param $prestudent_id
	 * @return array
	 */
	private function _getPersonAndStudiengangFromPrestudent($prestudent_id)
	{
		$prestudent = $this->PrestudentModel->getPrestudentWithZgv($prestudent_id);

		if (isError($prestudent))
		{
			show_error($prestudent->retval);
		}

		$person_id = $prestudent->retval[0]->person_id;
		$studiengang_kurzbz = $prestudent->retval[0]->studiengang;
		$studiengang_bezeichnung = $prestudent->retval[0]->studiengangbezeichnung;

		return array('person_id' => $person_id, 'studiengang_kurzbz' => $studiengang_kurzbz, 'studiengang_bezeichnung' => $studiengang_bezeichnung);
	}

	/**
	 * Helper function for logging
	 * @param $person_id
	 * @param $logname
	 * @param $messageparams
	 */
	private function _log($person_id, $logname, $messageparams)
	{
		$logdata = $this->_logparams[$logname];

		$datatolog = array(
			'name' => $logdata['name']
		);

		if (isset($logdata['message']))
			$datatolog['message'] = vsprintf($logdata['message'], $messageparams);

		if (array_key_exists('success', $logdata))
			$datatolog['success'] = true;

		$this->personloglib->log(
			$person_id,
			$logdata['logtype'],
			$datatolog,
			self::TAETIGKEIT,
			self::APP,
			null,
			$this->_uid
		);
	}

	/**
	 * Sends infomail with prestudent and person data when Prestudent is freigegeben
	 * @param $prestudent_id
	 */
	private function _sendFreigabeMail($prestudent_id)
	{
		//get data
		$prestudent = $this->PrestudentModel->getPrestudentWithZgv($prestudent_id)->retval[0];
		$prestudentstatus = $prestudent->prestudentstatus;
		$person_id = $prestudent->person_id;
		$person = $this->PersonModel->getPersonStammdaten($person_id, true)->retval;
		$dokumente = $this->AkteModel->getAktenWithDokInfo($person_id, null, false)->retval;
		$dokumenteNachzureichen = $this->AkteModel->getAktenWithDokInfo($person_id, null, true)->retval;

		//fill mail variables
		$interessentbez = $person->geschlecht == 'm' ? 'Ein Interessent' : 'Eine Interessentin';
		$sprache = $prestudentstatus->sprachedetails->bezeichnung[0];
		$orgform = $prestudentstatus->orgform != '' ? ' ('.$prestudentstatus->orgform.')' : '';
		$geschlecht = $person->geschlecht == 'm' ? 'm&auml;nnlich' : 'weiblich';
		$geburtsdatum = date('d.m.Y', strtotime($person->gebdatum));
		$zgvort = !empty($prestudent->zgvort) ? ' in '.$prestudent->zgvort : '';
		$zgvnation = !empty($prestudent->zgvnation_bez) ? ', '.$prestudent->zgvnation_bez : '';
		$zgvdatum = !empty($prestudent->zgvdatum) ? ', am '.date_format(date_create($prestudent->zgvdatum), 'd.m.Y') : '';

		$dokumenteNachzureichenMail = $dokumenteMail = array();
		//convert documents to array so they can be parsed, and keeping only needed fields
		$lastel = end($dokumente);
		foreach ($dokumente as $dokument)
		{
			$postfix = $lastel === $dokument ? '' : ' |';
			$dokumenteMail[] = array('dokument_bezeichnung' => $dokument->dokument_bezeichnung.$postfix);
		}

		foreach ($dokumenteNachzureichen as $dokument)
		{
			$anmerkung = !empty($dokument->anmerkung) ? ' | Anmerkung: '.$dokument->anmerkung : '';
			$nachgereichtam = !empty($dokument->nachgereicht_am) ? ' | wird nachgereicht bis '.date_format(date_create($dokument->nachgereicht_am), 'd.m.Y') : '';
			$dokumenteNachzureichenMail[] = array('dokument_bezeichnung' => $dokument->dokument_bezeichnung, 'anmerkung' => $anmerkung, 'nachgereicht_am' => $nachgereichtam);
		}

		$notizenBewerbung = $this->NotizModel->getNotizByTitel($person_id, 'Anmerkung zur Bewerbung')->retval;

		$notizentext = '<ul style="padding-left: 20px; margin-left: 0;">';
		foreach ($notizenBewerbung as $notiz)
		{
			// For applicant-notices the user is not shown
			if ($notiz->insertvon != 'online_notiz')
				$notizentext .= '<li>'.$notiz->text.' ('.$notiz->insertvon.')</li>';
			else
				$notizentext .= '<li>'.$notiz->text.'</li>';
		}
		$notizentext .= '</ul>';

		$mailadresse = '';
		foreach ($person->kontakte as $kontakt)
		{
			if ($kontakt->kontakttyp === 'email')
			{
				$mailadresse = $kontakt->kontakt;
				break;
			}
		}

		$data = array
		(
			'interessentbez' => $interessentbez,
			'studiengangbez' => $prestudent->studiengangbezeichnung,
			'studiengangtypbez' => $prestudent->studiengangtyp_bez,
			'orgform' => $orgform,
			'studiensemester' => $prestudentstatus->studiensemester_kurzbz,
			'sprache' => $sprache,
			'geschlecht' => $geschlecht,
			'vorname' => $person->vorname,
			'nachname' => $person->nachname,
			'gebdatum' => $geburtsdatum,
			'mailadresse' => $mailadresse,
			'prestudentid' => $prestudent_id,
			'zgvbez' => $prestudent->zgv_bez,
			'zgvort' => $zgvort,
			'zgvdatum' => $zgvdatum,
			'zgvnation' => $zgvnation,
			'notizentext' => $notizentext,
			'dokumente' => $dokumenteMail,
			'dokumente_nachgereicht' => $dokumenteNachzureichenMail
		);

		$this->load->library('parser');
		$this->load->library('MailLib');
		$this->load->library('LogLib');

		//parse freigabe html email template, wordwrap wraps text so no display errors
		$email = wordwrap($this->parser->parse('templates/mailtemplates/interessentFreigabe', $data, true), 70);

		$subject = ($person->geschlecht == 'm' ? 'Interessent ' : 'Interessentin ').$person->vorname.' '.$person->nachname.' für '.$prestudent->studiengangbezeichnung.$orgform.' freigegeben';

		$receiver = $prestudent->studiengangmail;

		if (!empty($receiver))
		{
			//Freigabeinformationmail sent from default system mail to studiengang mail(s)
			$sent = $this->maillib->send('', $receiver, $subject, $email, '', null, null, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');

			if (!$sent)
				$this->loglib->logError('Error when sending Freigabe mail');
		}
		else
		{
			$this->loglib->logError('Studiengang has no mail for sending Freigabe mail');
		}
	}
}
