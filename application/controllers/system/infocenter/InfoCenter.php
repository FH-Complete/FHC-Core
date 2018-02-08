<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Also shows infocenter-related data for a person and its prestudents, enables document and zgv checks,
 * displays and saves Notizen for a person, logs infocenter-related actions for a person
 */
class InfoCenter extends VileSci_Controller
{
	// App and Verarbeitungstaetigkeit name for logging
	const APP = 'infocenter';
	const TAETIGKEIT = 'bewerbung';

	// URL prefix for this controller
	const URL_PREFIX = '/system/infocenter/InfoCenter/';

	// Used to log with PersonLogLib
	private $logparams = array(
		'saveformalgep' => array(
			'logtype' => 'Action',
			'name' => 'Document formally checked',
			'message' => 'Document %s formally checked, set to %s'
		),
		'savezgv' => array(
			'logtype' => 'Action',
			'name' => 'ZGV saved',
			'message' => 'ZGV saved for degree program %s, prestudentid %s'
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
			'message' => 'Note with title %s was added'
		)
	);
	private $uid; // contains the UID of the logged user
	private $navigationMenuArray; // contains all the voices for the navigation menu
	private $navigationHeaderArray;

	/**
	 * Constructor
	 */
	public function __construct()
    {
        parent::__construct();

		// Loads models
		$this->load->model('crm/akte_model', 'AkteModel');
		$this->load->model('crm/prestudent_model', 'PrestudentModel');
		$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
		$this->load->model('crm/statusgrund_model', 'StatusgrundModel');
		$this->load->model('person/notiz_model', 'NotizModel');
		$this->load->model('person/person_model', 'PersonModel');
		$this->load->model('system/message_model', 'MessageModel');
		$this->load->model('system/filters_model', 'FiltersModel');

		// Loads libraries
		$this->load->library('DmsLib');
		$this->load->library('PersonLogLib');
		$this->load->library('WidgetLib');

		$this->_setAuthUID(); // sets property uid

		$this->load->library('PermissionLib');
		if(!$this->permissionlib->isBerechtigt('basis/person'))
			show_error('You have no Permission! You need Infocenter Role');

		$this->_setNavigationMenuArray(); // sets property navigationMenuArray

		$this->navigationHeaderArray = array(
			'headertext' => 'Infocenter',
			'headertextlink' => base_url('index.ci.php/system/infocenter/InfoCenter')
		);
    }

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Default
	 */
	public function index()
	{
		$this->load->view(
			'system/infocenter/infocenter.php',
			array(
				'navigationHeaderArray' => $this->navigationHeaderArray,
				'navigationMenuArray' => $this->navigationMenuArray
			)
		);
	}

	/**
	 * Initialization function, gets person and prestudent data and loads the view with the data
	 * @param $person_id
	 */
	public function showDetails($person_id)
	{
		if (!is_numeric($person_id))
			show_error('person id is not numeric!');

		$persondata = $this->_loadPersonData($person_id);
		if (!isset($persondata))
			show_error('person does not exist!');

		$prestudentdata = $this->_loadPrestudentData($person_id);

		$this->load->view(
			'system/infocenter/infocenterDetails.php',
			array_merge(
				$persondata,
				$prestudentdata,
				array(
					'navigationHeaderArray' => $this->navigationHeaderArray,
					'navigationMenuArray' => $this->navigationMenuArray
				)
			)
		);
	}

	/**
	 * Saves if a document has been formal geprueft. saves current timestamp if checked as geprueft, or null if not.
	 */
	public function saveFormalGeprueft()
	{
		$akte_id = $this->input->get('akte_id');
		$formalgeprueft = $this->input->get('formal_geprueft');
		$person_id = $this->input->get('person_id');

		if (!isset($akte_id) || !isset($formalgeprueft) || !isset($person_id))
			show_error('Parameters not set!');

		$akte = $this->AkteModel->load($akte_id);

		if (isError($akte))
		{
			show_error($akte->retval);
		}

		$timestamp = ($formalgeprueft === 'true') ? date('Y-m-d H:i:s') : null;
		$result = $this->AkteModel->update($akte_id, array('formal_geprueft_amum' => $timestamp));

		if (isError($result))
		{
			show_error($result->retval);
		}

		//write person log
		$this->_log(
			$person_id,
			'saveformalgep',
			array(
				empty($akte->retval[0]->titel) ? $akte->retval[0]->bezeichnung : $akte->retval[0]->titel,
				is_null($timestamp) ? 'NULL' : $timestamp
			)
		);

		redirect(self::URL_PREFIX.'showDetails/'.$person_id.'#DokPruef');
	}

	/**
	 * Saves a zgv for a prestudent. includes Ort, Datum, Nation for bachelor and master.
	 * @param $prestudent_id
	 */
	public function saveZgvPruefung($prestudent_id)
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
				'zgvmanation' => $zgvmanation_code
			)
		);

		if (isError($result))
		{
			show_error($result->retval);
		}

		//get extended Prestudent data for logging
		$logdata = $this->_getPersonAndStudiengangFromPrestudent($prestudent_id);

		$this->_log($logdata['person_id'], 'savezgv', array($logdata['studiengang_kurzbz'], $prestudent_id));

		$this->_redirectToStart($prestudent_id, 'ZgvPruef');
	}

	/**
	 * Saves Absage for Prestudent including the reason for the Absage (statusgrund).
	 * inserts Studiensemester and Ausbildungssemester for the new Absage of (chronologically) last status.
	 * @param $prestudent_id
	 */
	public function saveAbsage($prestudent_id)
	{
		//TODO email messaging
		$statusgrund = $this->input->post('statusgrund');

		$lastStatus = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if (isError($lastStatus))
		{
			show_error($lastStatus->retval);
		}

		//check if still Interessent and not freigegeben yet
		if($lastStatus->retval[0]->status_kurzbz === 'Interessent' && !isset($lastStatus->retval[0]->bestaetigtam))
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
					'insertvon' => $this->uid,
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

		if (count($lastStatus->retval) > 0)
		{
			$lastStatus = $lastStatus->retval[0];

			//check if still Interessent and not freigegeben yet
			if($lastStatus->status_kurzbz === 'Interessent' && !isset($lastStatus->bestaetigtam))
			{
				$result = $this->PrestudentstatusModel->update(
					array(
						'prestudent_id' => $prestudent_id,
						'status_kurzbz' => $lastStatus->status_kurzbz,
						'studiensemester_kurzbz' => $lastStatus->studiensemester_kurzbz,
						'ausbildungssemester' => $lastStatus->ausbildungssemester
					),
					array(
						'bestaetigtvon' => $this->uid,
						'bestaetigtam' => date('Y-m-d'),
						'updatevon' => $this->uid,
						'updateamum' => date('Y-m-d H:i:s')
					)
				);

				if (isError($result))
				{
					show_error($result->retval);
				}

				$logdata = $this->_getPersonAndStudiengangFromPrestudent($prestudent_id);

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

		$result = $this->NotizModel->addNotizForPerson($person_id, $titel, $text, $erledigt, $this->uid);

		if (isError($result))
		{
			show_error($result->retval);
		}

		$this->_log($person_id, 'savenotiz', array($titel));

		redirect(self::URL_PREFIX.'showDetails/'.$person_id.'#NotizAkt');
	}

	/**
	 * Outputs content of an Akte, sends appropriate headers (so the document can be downloaded)
	 * @param $akte_id
	 */
	public function outputAkteContent($akte_id)
	{
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

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->uid = getAuthUID();

		if (!$this->uid) show_error('User authentification failed');
	}

	/**
	 *
	 */
	private function _setNavigationMenuArray()
	{
		$listFiltersSent = array();
		$listFiltersNotSent = array();

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

		$customFilters = $this->FiltersModel->getCustomFiltersList('infocenter', 'PersonActions', $this->uid);
		if (hasData($customFilters))
		{
			for ($filtersCounter = 0; $filtersCounter < count($customFilters->retval); $filtersCounter++)
			{
				$filter = $customFilters->retval[$filtersCounter];

				$listCustomFilters[$filter->filter_id] = $filter->description[0];
			}
		}

		$filtersarray = array(
			'abgeschickt' => array(
				'link' => '#',
				'description' => 'Abgeschickt',
				'expand' => true,
				'children' => array()
			),
			'nichtabgeschickt' => array(
				'link' => '#',
				'description' => 'Nicht abgeschickt',
				'expand' => true,
				'children' => array()
			)
		);

		$this->_fillFilters($listFiltersSent, $filtersarray['abgeschickt']);
		$this->_fillFilters($listFiltersNotSent, $filtersarray['nichtabgeschickt']);

		if (isset($listCustomFilters) && is_array($listCustomFilters) && count($listCustomFilters) > 0)
		{
			$filtersarray['personal'] = array(
				'link' => '#',
				'description' => 'Personal filters',
				'expand' => true,
				'children' => array()
			);

			$this->_fillFilters($listCustomFilters, $filtersarray['personal']);
		}

		$this->navigationMenuArray = array(
			'dashboard' => array(
				'link' => '#',
				'description' => 'Dashboard',
				'icon' => 'dashboard'
			),
			'filters' => array(
				'link' => '#',
				'description' => 'Filter',
				'icon' => 'filter',
				'expand' => true,
				'children' => $filtersarray
			)
		);
	}

	private function _fillFilters($filters, &$tofill)
	{
		foreach ($filters as $filterId => $description)
		{
			$toPrint = "%s=%s";
			$tofill['children'][] = array(
				'link' => sprintf($toPrint, base_url('index.ci.php/system/infocenter/InfoCenter?filterId'), $filterId),
				'description' => $description
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

		$user_person = $this->PersonModel->getByUid($this->uid);

		if (isError($user_person))
		{
			show_error($user_person->retval);
		}

		$messagelink = base_url('/index.ci.php/system/Messages/write/'.$user_person->retval[0]->person_id);

		$data = array (
			'stammdaten' => $stammdaten->retval,
			'dokumente' => $dokumente->retval,
			'dokumente_nachgereicht' => $dokumente_nachgereicht->retval,
			'messages' => $messages->retval,
			'logs' => $logs,
			'notizen' => $notizen->retval,
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

			if(isset($zgvpruefung->prestudentstatus))
			{
				$position = strpos($zgvpruefung->prestudentstatus->anmerkung, 'Alt:');

				//parse Anmerkung for Alternative (Prio is given in orgform and sprache anyway)
				$zgvpruefung->prestudentstatus->alternative = is_numeric($position) ? substr($zgvpruefung->prestudentstatus->anmerkung, $position) : null;
			}
			//if prestudent is not interessent or is already bestaetigt, then show only as information, non-editable
			$zgvpruefung->infoonly = !isset($zgvpruefung->prestudentstatus) || isset($zgvpruefung->prestudentstatus->bestaetigtam) || $zgvpruefung->prestudentstatus->status_kurzbz != 'Interessent';

			$zgvpruefungen[] = $zgvpruefung;
		}

		// Interessenten come first
		usort($zgvpruefungen, function ($a, $b)
		{
			if (!isset($a->prestudentstatus->status_kurzbz) || !isset($b->prestudentstatus->status_kurzbz))
				return 0;
			elseif ($a->prestudentstatus->status_kurzbz === 'Interessent' && $b->prestudentstatus->status_kurzbz === 'Interessent')
			{
				//infoonly Interessenten come after new Interessenten
				if ($a->infoonly)
					return 1;
				elseif ($b->infoonly)
					return -1;
			}
			elseif ($a->prestudentstatus->status_kurzbz === 'Interessent')
				return -1;
			elseif ($b->prestudentstatus->status_kurzbz === 'Interessent')
				return 1;
			else
				return 0;
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

		redirect(self::URL_PREFIX.'showDetails/'.$person_id.'#'.$section);
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

		return array('person_id' => $person_id, 'studiengang_kurzbz' => $studiengang_kurzbz);
	}

	/**
	 * Helper function for logging
	 * @param $person_id
	 * @param $logname
	 * @param $messageparams
	 */
	private function _log($person_id, $logname, $messageparams)
	{
		$logdata = $this->logparams[$logname];

		$this->personloglib->log(
			$person_id,
			$logdata['logtype'],
			array(
				'name' => $logdata['name'],
				'message' => vsprintf($logdata['message'],
				$messageparams),
				'success' => 'true'
			),
			self::TAETIGKEIT,
			self::APP,
			null,
			$this->uid
		);
	}
}
