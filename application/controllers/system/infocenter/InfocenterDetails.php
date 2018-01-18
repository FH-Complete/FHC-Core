<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Class InfocenterDetails
 * shows aufnahme-related data for a person and its prestudents, enables document and zgv checks,
 * displays and saves Notizen for a person, logs aufnahme-related actions for a person
 */
class InfocenterDetails extends VileSci_Controller
{
	//app name for logging
	const APP = 'aufnahme';

	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->model('person/person_model', 'PersonModel');
		$this->load->model('person/notiz_model', 'NotizModel');
		$this->load->model('crm/prestudent_model', 'PrestudentModel');
		$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
		$this->load->model('crm/statusgrund_model', 'StatusgrundModel');
		$this->load->model('crm/akte_model', 'AkteModel');

		$this->load->library('DmsLib');
		$this->load->library('WidgetLib');
		$this->load->library('PersonLogLib');

		$this->load->helper('fhcauth');
		$this->load->helper('url');

		$this->uid = getAuthUID();
		if(!$this->uid)
			show_error('user authentification failed');
	}

	/**
	 * loads all necessary Person data: Stammdaten (name, svnr, contact, ...), Dokumente, Logs and Notizen
	 * @param $person_id
	 * @return array
	 */
	private function __loadPersonData($person_id)
	{
		$stammdaten = $this->PersonModel->getPersonStammdaten($person_id);

		if ($stammdaten->error)
		{
			show_error($stammdaten->retval);
		}

		if(!isset($stammdaten->retval))
			return null;

		$dokumente = $this->AkteModel->getAktenWithDokInfo($person_id, null, false);

		if ($dokumente->error)
		{
			show_error($dokumente->retval);
		}

		$dokumente_nachgereicht = $this->AkteModel->getAktenWithDokInfo($person_id, null, true);

		if ($dokumente_nachgereicht->error)
		{
			show_error($dokumente->retval);
		}

		$logs = $this->personloglib->getLogs($person_id, $this::APP);

		$notizen = $this->NotizModel->getNotiz($person_id);

		if ($notizen->error)
		{
			show_error($notizen->retval);
		}

		$data = array (
			'stammdaten' => $stammdaten->retval,
			'dokumente' => $dokumente->retval,
			'dokumente_nachgereicht' => $dokumente_nachgereicht->retval,
			'logs' => $logs,
			'notizen' => $notizen->retval
		);

		return $data;
	}

	/**
	 * loads all necessary Prestudent data: Zgv data, Statusgruende
	 * @param $person_id
	 * @return array
	 */
	private function __loadPrestudentData($person_id)
	{
		$zgvpruefungen = array();

		$prestudenten = $this->PrestudentModel->loadWhere(array('person_id' => $person_id));

		if ($prestudenten->error)
		{
			show_error($prestudenten->retval);
		}

		foreach ($prestudenten->retval as $prestudent)
		{
			$prestudent = $this->PrestudentModel->getPrestudentWithZgv($prestudent->prestudent_id);

			if ($prestudent->error)
			{
				show_error($prestudent->retval);
			}

			$zgvpruefung = $prestudent->retval[0];

			//if prestudent is not interessent or is already bestaetigt, then show only as information, non-editable
			$zgvpruefung->infoonly = !isset($zgvpruefung->prestudentstatus) || isset($zgvpruefung->prestudentstatus->bestaetigtam) || $zgvpruefung->prestudentstatus->status_kurzbz != 'Interessent';

			$zgvpruefungen[] = $zgvpruefung;
		}

		//Interessenten come first
		usort($zgvpruefungen, function ($a, $b)
		{
			if(!isset($a->prestudentstatus->status_kurzbz) || !isset($b->prestudentstatus->status_kurzbz))
				return 0;
			elseif($a->prestudentstatus->status_kurzbz === 'Interessent' && $b->prestudentstatus->status_kurzbz === 'Interessent')
			{
				//infoonly Interessenten are behind new Interessenten
				if($a->infoonly)
					return 1;
				elseif($b->infoonly)
					return -1;
			}
			elseif($a->prestudentstatus->status_kurzbz === 'Interessent')
				return -1;
			elseif($b->prestudentstatus->status_kurzbz === 'Interessent')
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
	 * initialization function, gets person and prestudent data and loads the view with the data
	 * @param $person_id
	 */
	public function showDetails($person_id)
	{
		if(!is_numeric($person_id))
			show_error('person id is not numeric!');
		$persondata = $this->__loadPersonData($person_id);
		if(!isset($persondata))
			show_error('person does not exist!');
		$prestudentdata = $this->__loadPrestudentData($person_id);
		$this->load->view('system/infocenter/infocenterDetails.php', array_merge($persondata, $prestudentdata));
	}

	/**
	 * saves if a document has been formal geprueft. saves current timestamp if checked as geprueft, or null if not.
	 */
	public function saveFormalGeprueft()
	{
		$akte_id = $this->input->get('akte_id');
		$formalgeprueft = $this->input->get('formal_geprueft');
		$person_id = $this->input->get('person_id');

		if(!isset($akte_id) || !isset($formalgeprueft) || !isset($person_id))
			show_error('Parameters not set!');

		$akte = $this->AkteModel->load($akte_id);

		if ($akte->error)
		{
			show_error($akte->retval);
		}

		$timestamp = ($formalgeprueft === 'true') ? date('Y-m-d H:i:s') : null;
		$result = $this->AkteModel->update($akte_id, array('formal_geprueft_amum' => $timestamp));

		if ($result->error)
		{
			show_error($result->retval);
		}

		//write person log
		$this->personloglib->log($person_id, 'Action', array('name' => 'Dokument formal geprüft', 'message' => 'Dokument '.$akte->titel.' formal geprüft, gesetzt auf '.(is_null($timestamp) ? 'NULL' : $timestamp), 'success' => 'true'), $this::APP, null, $this->uid);
		//redirect to start page
		redirect('/system/infocenter/InfocenterDetails/showDetails/'.$person_id.'#DokPruef');
	}

	/**
	 * saves a zgv for a prestudent. includes Ort, Datum, Nation for bachelor and master.
	 * @param $prestudent_id
	 */
	public function saveZgvPruefung($prestudent_id)
	{
		// zgvdata
		$zgv_code = $this->input->post('zgv') === 'null' ? null : $this->input->post('zgv');//check for string null, in case dropdown changed to default value
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

		$result = $this->PrestudentModel->update($prestudent_id, array('zgv_code' => $zgv_code, 'zgvort' => $zgvort, 'zgvdatum' => $zgvdatum, 'zgvnation' => $zgvnation_code,
			'zgvmas_code' => $zgvmas_code, 'zgvmaort' => $zgvmaort, 'zgvmadatum' => $zgvmadatum, 'zgvmanation' => $zgvmanation_code));

		if ($result->error)
		{
			show_error($result->retval);
		}

		//get extended Prestudent data for logging
		$logdata = $this->__getPersonAndStudiengangFromPrestudent($prestudent_id);

		$this->personloglib->log($logdata['person_id'], 'Action', array('name' => 'Zgv gespeichert', 'message' => 'Zgv für Studiengang '.$logdata['studiengang_kurzbz'].' wurde gespeichert', 'success' => 'true'), $this::APP, null, $this->uid);

		$this->__redirectToStart($prestudent_id, 'ZgvPruef');
	}

	/**
	 * saves Absage for Prestudent including the reason for the Absage (statusgrund).
	 * inserts Studiensemester and Ausbildungssemester for the new Absage of (chronologically) last status.
	 * @param $prestudent_id
	 */
	public function saveAbsage($prestudent_id)
	{
		//TODO email messaging
		$statusgrund = $this->input->post('statusgrund');

		$lastStatus = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if ($lastStatus->error)
		{
			show_error($lastStatus->retval);
		}

		$result = $this->PrestudentstatusModel->insert(array('prestudent_id' => $prestudent_id, 'studiensemester_kurzbz' => $lastStatus->retval[0]->studiensemester_kurzbz, 'ausbildungssemester' => $lastStatus->retval[0]->ausbildungssemester, 'datum' => date('Y-m-d'), 'orgform_kurzbz' => $lastStatus->retval[0]->orgform_kurzbz, 'studienplan_id' => $lastStatus->retval[0]->studienplan_id, 'status_kurzbz' => 'Abgewiesener', 'statusgrund_id' => $statusgrund, 'insertvon' => $this->uid, 'insertamum' => date('Y-m-d H:i:s')));

		if ($result->error)
		{
			show_error($result->retval);
		}

		$logdata = $this->__getPersonAndStudiengangFromPrestudent($prestudent_id);

		$this->personloglib->log($logdata['person_id'], 'Processstate', array('name' => 'Interessent abgewiesen', 'message' => 'Interessent wurde für Studiengang '.$logdata['studiengang_kurzbz'].' abgewiesen', 'success' => 'true'), $this::APP, null, $this->uid);

		$this->__redirectToStart($prestudent_id, 'ZgvPruef');
	}

	/**
	 * saves Freigabe of a Prestudent to the Studiengang.
	 * updates bestaetigtam and bestaetigtvon fields of the last status
	 * @param $prestudent_id
	 */
	public function saveFreigabe($prestudent_id)
	{
		$lastStatus = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if (count($lastStatus->retval) > 0)
		{
			$lastStatus = $lastStatus->retval[0];

			$result = $this->PrestudentstatusModel->update(array('prestudent_id' => $prestudent_id, 'status_kurzbz' => $lastStatus->status_kurzbz, 'studiensemester_kurzbz' => $lastStatus->studiensemester_kurzbz, 'ausbildungssemester' => $lastStatus->ausbildungssemester),
				array('bestaetigtvon' => $this->uid, 'bestaetigtam' => date('Y-m-d'), 'updatevon' => $this->uid, 'updateamum' => date('Y-m-d H:i:s')));

			if ($result->error)
			{
				show_error($result->retval);
			}
		}

		$logdata = $this->__getPersonAndStudiengangFromPrestudent($prestudent_id);

		$this->personloglib->log($logdata['person_id'], 'Processstate', array('name' => 'Interessent freigegeben', 'message' => 'Interessent wurde für Studiengang '.$logdata['studiengang_kurzbz'].' freigegeben', 'success' => 'true'), $this::APP, null, $this->uid);

		$this->__redirectToStart($prestudent_id, 'ZgvPruef');
	}

	/**
	 * saves a new Notiz for a person
	 * @param $person_id
	 */
	public function saveNotiz($person_id)
	{
		$titel = $this->input->post('notiztitel');
		$text = $this->input->post('notiz');
		$erledigt = false;

		$result = $this->NotizModel->addNotizForPerson($person_id, $titel, $text, $erledigt, $this->uid);

		if ($result->error)
		{
			show_error($result->retval);
		}

		$this->personloglib->log($person_id, 'Action', array('name' => 'Notiz hinzugefügt', 'message' => 'Notiz mit Titel '.$titel.' wurde hinzugefügt', 'success' => 'true'), $this::APP, null, $this->uid);

		redirect('/system/infocenter/InfocenterDetails/showDetails/'.$person_id.'#NotizAkt');
	}

	/**
	 * Outputs content of an Akte, sends appropriate headers (so the document can be downloaded)
	 * @param $akte_id
	 */
	public function outputAkteContent($akte_id)
	{
		$akte = $this->AkteModel->load($akte_id);

		if ($akte->error)
		{
			show_error($akte->retval);
		}

		$aktecontent = $this->dmslib->getAkteContent($akte_id);

		if($aktecontent->error)
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
	 * helper function for redirecting to initial page for person from a prestudent-specific page
	 * @param $prestudent_id
	 * @param $section optional section of the page to go to
	 */
	private function __redirectToStart($prestudent_id, $section = '')
	{
		$this->PrestudentModel->addSelect('person_id');
		$person_id = $this->PrestudentModel->load($prestudent_id)->retval[0]->person_id;
		redirect('/system/infocenter/InfocenterDetails/showDetails/'.$person_id.'#'.$section);
	}

	/**
	 * helper function retrieves personid and studiengang kurzbz from a prestudent id
	 * @param $prestudent_id
	 * @return array
	 */
	private function __getPersonAndStudiengangFromPrestudent($prestudent_id)
	{
		$prestudent = $this->PrestudentModel->getPrestudentWithZgv($prestudent_id);

		if ($prestudent->error)
		{
			show_error($prestudent->retval);
		}

		$person_id = $prestudent->retval[0]->person_id;
		$studiengang_kurzbz = $prestudent->retval[0]->studiengang;

		return array('person_id' => $person_id, 'studiengang_kurzbz' => $studiengang_kurzbz);
	}

}