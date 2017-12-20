<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class InfocenterDetails extends VileSci_Controller
{

	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		// Load models
		$this->load->model('person/person_model', 'PersonModel');
		$this->load->model('person/kontakt_model', 'KontaktModel');
		$this->load->model('person/adresse_model', 'AdresseModel');
		$this->load->model('person/notiz_model', 'NotizModel');
		$this->load->model('person/notizzuordnung_model', 'NotizzuordnungModel');
		$this->load->model('crm/prestudent_model', 'PrestudentModel');
		$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
		$this->load->model('crm/akte_model', 'AkteModel');
		$this->load->model('crm/statusgrund_model', 'StatusgrundModel');
		$this->load->model('codex/nation_model', 'NationModel');
		$this->load->model('codex/zgv_model', 'ZgvModel');
		$this->load->model('codex/zgvmaster_model', 'ZgvmasterModel');
		$this->load->model('organisation/studiengang_model', 'StudiengangModel');

		$this->load->library('DmsLib');
		$this->load->library('WidgetLib');
		$this->load->library('PersonLogLib');

		$this->load->helper('fhcauth');
		$this->load->helper('url');

		$this->app = 'aufnahme';
		$this->uid = getAuthUID();
		if(!$this->uid)
			show_error('user authentification failed');
	}

	public function index()
	{
		//TODO error page
	}

	private function __loadPersonData($person_id)
	{
		$person = $this->PersonModel->load($person_id);

		if ($person->error)
		{
			show_error($person->retval);
		}

		$staatsbuergerschaft = $this->NationModel->load($person->retval[0]->staatsbuergerschaft);
		if ($staatsbuergerschaft->error)
		{
			show_error($staatsbuergerschaft->retval);
		}

		$geburtsnation = $this->NationModel->load($person->retval[0]->geburtsnation);
		if ($geburtsnation->error)
		{
			show_error($geburtsnation->retval);
		}

		$this->KontaktModel->addDistinct();
		$this->KontaktModel->addSelect('kontakttyp, kontakt');
		$kontakte = $this->KontaktModel->loadWhere(array('person_id' => $person_id));

		if ($kontakte->error)
		{
			show_error($kontakte->retval);
		}

		$adresse = $this->AdresseModel->loadWhere(array('person_id' => $person_id));

		if ($adresse->error)
		{
			show_error($adresse->retval);
		}

		$dokumente = $this->AkteModel->loadWhere(array('person_id' => $person_id));

		if ($dokumente->error)
		{
			show_error($dokumente->retval);
		}

		$logs = $this->personloglib->getLogs($person_id, $this->app);

		foreach($logs as $log)
			$log->logdata = json_decode($log->logdata);

		$this->NotizzuordnungModel->addSelect('notiz_id');
		$notizzuordnung = $this->NotizzuordnungModel->loadWhere(array('person_id' => $person_id));

		if ($notizzuordnung->error)
		{
			show_error($notizzuordnung->retval);
		}

		$notizen = array();

		foreach ($notizzuordnung->retval as $notiz_id)
		{
			$notiz = $this->NotizModel->load($notiz_id->notiz_id);
			$notizen[] = $notiz->retval[0];
		}

		$data = array (
			'person' => $person->retval[0],
			'staatsbuergerschaft' => $staatsbuergerschaft->retval[0],
			'geburtsnation' => $geburtsnation->retval[0],
			'kontakte' => $kontakte->retval,
			'adresse' => isset($adresse->retval[0]) ? $adresse->retval[0] : null,
			'dokumente' => $dokumente->retval,
			'logs' => $logs,
			'notizen' => $notizen
		);

		return $data;
	}

	private function __loadPrestudentData($person_id)
	{
		$zgvpruefungen = [];

		$prestudenten = $this->PrestudentModel->loadWhere(array('person_id' => $person_id));

		if ($prestudenten->error)
		{
			show_error($prestudenten->retval);
		}

		foreach ($prestudenten->retval as $prestudent)
		{
			$zgvpruefung = new stdClass();
			$zgvpruefung->prestudent_id = $prestudent->prestudent_id;

			//Prestudentstatus
			$lastStatus = $this->PrestudentstatusModel->getLastStatus($prestudent->prestudent_id);

			if ($lastStatus->error)
			{
				show_error($lastStatus->retval);
			}

			$zgvpruefung->prestudentstatus = $lastStatus->retval[0];

			// Studiengang
			$this->StudiengangModel->addSelect('kurzbzlang, bezeichnung, typ');//TODO need bezeichnung?
			$studiengang = $this->StudiengangModel->load($prestudent->studiengang_kz);

			if ($studiengang->error)
			{
				show_error($studiengang->retval);
			}

			$zgvpruefung->studiengang = $studiengang->retval[0]->kurzbzlang;
			$zgvpruefung->studiengangtyp = $studiengang->retval[0]->typ;

			// Zgv
			if (isset($prestudent->zgv_code))
			{
				$this->ZgvModel->addSelect('zgv_code, zgv_bez');
				$zgv = $this->ZgvModel->load($prestudent->zgv_code);

				if ($zgv->error)
				{
					show_error($zgv->retval);
				}

				$zgvpruefung->zgv_code = $zgv->retval[0]->zgv_code;
				$zgvpruefung->zgv_bez = $zgv->retval[0]->zgv_bez;
			}
			else
			{
				$zgvpruefung->zgv_code = null;
				$zgvpruefung->zgv_bez = null;
			}
			$zgvpruefung->zgvort = $prestudent->zgvort;
			$zgvpruefung->zgvdatum = $prestudent->zgvdatum;

			// Zgv Nation
			if (isset($prestudent->zgvnation))
			{
				$this->NationModel->addSelect('nation_code, kurztext');
				$zgvnation = $this->NationModel->load($prestudent->zgvnation);

				if ($zgvnation->error)
				{
					show_error($zgvnation->retval);
				}

				$zgvpruefung->zgvnation_code = $zgvnation->retval[0]->nation_code;
				$zgvpruefung->zgvnation_bez = $zgvnation->retval[0]->kurztext;
			}
			else
			{
				$zgvnation = null;
				$zgvpruefung->zgvnation_code = null;
				$zgvpruefung->zgvnation_bez = null;
			}

			// Zgv Master
			if (isset($prestudent->zgvmas_code))
			{
				$this->ZgvmasterModel->addSelect('zgvmas_code, zgvmas_bez');
				$zgvmas = $this->ZgvmasterModel->load($prestudent->zgvmas_code);

				if ($zgvmas->error)
				{
					show_error($zgvmas->retval);
				}
				$zgvpruefung->zgvmas_code = $zgvmas->retval[0]->zgvmas_code;
				$zgvpruefung->zgvmas_bez = $zgvmas->retval[0]->zgvmas_bez;
			}
			else
			{
				$zgvpruefung->zgvmas_code = null;
				$zgvpruefung->zgvmas_bez = null;
			}
			$zgvpruefung->zgvmaort = $prestudent->zgvmaort;
			$zgvpruefung->zgvmadatum = $prestudent->zgvmadatum;

			// Zgv Master Nation
			if (isset($prestudent->zgvmanation))
			{
				$this->NationModel->addSelect('nation_code, kurztext');
				$zgvmanation = $this->NationModel->load($prestudent->zgvmanation);

				if ($zgvmanation->error)
				{
					show_error($zgvmanation->retval);
				}

				$zgvpruefung->zgvmanation_code = $zgvmanation->retval[0]->nation_code;
				$zgvpruefung->zgvmanation_bez = $zgvmanation->retval[0]->kurztext;
			}
			else
			{
				$zgvmanation = null;
				$zgvpruefung->zgvmanation_code = null;
				$zgvpruefung->zgvmanation_bez = null;
			}

			$zgvpruefungen[] = $zgvpruefung;
		}

		//TODO replace with widget
		$statusgruende = $this->StatusgrundModel->load()->retval;

		$data = array (
			'zgvpruefungen' => $zgvpruefungen,
			'statusgruende' => $statusgruende
		);

		return $data;
	}

	public function showDetails($person_id)
	{
		$persondata = $this->__loadPersonData($person_id);
		$prestudentdata = $this->__loadPrestudentData($person_id);
		$this->load->view('system/infocenter/infocenterDetails.php', array_merge($persondata, $prestudentdata));
	}

	public function saveFormalGeprueft()
	{
		$akte_id = $this->input->get('akte_id');
		$formalgeprueft = $this->input->get('formal_geprueft');
		$person_id = $this->input->get('person_id');

		$akte = $this->AkteModel->load($akte_id);

		if ($akte->error)
		{
			show_error($akte->retval);
		}

		$timestamp = (isset($formalgeprueft) && $formalgeprueft === 'true')? date('Y-m-d H:i:s') : null;
		$this->AkteModel->update($akte_id, array('formal_geprueft_amum' => $timestamp));

		//write person log
		$this->personloglib->log($person_id, 'Action', array('name' => 'Dokument formal geprüft', 'message' => 'Dokument'.$akte->titel.' formal geprüft, gesetzt auf '.(is_null($timestamp) ? 'NULL' : $timestamp), 'success' => 'true'), $this->app, null, $this->uid);

		redirect('/system/infocenter/InfocenterDetails/showDetails/'.$person_id);
	}

	public function saveZgvPruefung($prestudent_id)
	{
		// prestudentdata
		$studiensemester = $this->input->post('studiensemester') === 'null' ? null : $this->input->post('studiensemester');
		$ausbildungssemester = $this->input->post('ausbildungssemester');

		// zgvdata
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

		$lastStatus = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if ($lastStatus->error)
		{
			show_error($lastStatus->retval);
		}

		$result = $this->PrestudentstatusModel->update(array('prestudent_id' => $prestudent_id, 'studiensemester_kurzbz' => $lastStatus->retval[0]->studiensemester_kurzbz, 'ausbildungssemester' => $lastStatus->retval[0]->ausbildungssemester), array('studiensemester_kurzbz' => $studiensemester, 'ausbildungssemester' => $ausbildungssemester));

		if ($result->error)
		{
			show_error($result->retval);
		}

		$result = $this->PrestudentModel->update($prestudent_id, array('zgv_code' => $zgv_code, 'zgvort' => $zgvort, 'zgvdatum' => $zgvdatum, 'zgvnation' => $zgvnation_code,
			'zgvmas_code' => $zgvmas_code, 'zgvmaort' => $zgvmaort, 'zgvmadatum' => $zgvmadatum, 'zgvmanation' => $zgvmanation_code));

		if ($result->error)
		{
			show_error($result->retval);
		}

		$logdata = $this->__getPersonAndStudiengangFromPrestudent($prestudent_id);

		$this->personloglib->log($logdata['person_id'], 'Action', array('name' => 'Zgv gespeichert', 'message' => 'Zgv für Studiengang '.$logdata['studiengang_kurzbz'].' wurde gespeichert ', 'success' => 'true'), $this->app, null, $this->uid);

		$this->__redirectToStart($prestudent_id);
	}

	public function saveAbsage($prestudent_id)
	{
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

		$this->personloglib->log($logdata['person_id'], 'Processstate', array('name' => 'Interessent abgewiesen', 'message' => 'Interessent wurde für Studiengang '.$logdata['studiengang_kurzbz'].' abgewiesen', 'success' => 'true'), $this->app, null, $this->uid);

		$this->__redirectToStart($prestudent_id);
	}

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

		$this->personloglib->log($logdata['person_id'], 'Processstate', array('name' => 'Interessent freigegeben', 'message' => 'Interessent wurde für Studiengang '.$logdata['studiengang_kurzbz'].' freigegeben', 'success' => 'true'), $this->app, null, $this->uid);

		$this->__redirectToStart($prestudent_id);
	}

	public function saveNotiz($person_id)
	{
		$titel = $this->input->post('notiztitel');
		$text = $this->input->post('notiz');
		$erledigt = false;

		$this->NotizModel->addNotizForPerson($person_id, $titel, $text, $erledigt, $this->uid);

		$this->personloglib->log($person_id, 'Action', array('name' => 'Notiz hinzugefügt', 'message' => 'Notiz mit Titel '.$titel.' wurde hinzugefügt', 'success' => 'true'), $this->app, null, $this->uid);

		redirect('/system/infocenter/InfocenterDetails/showDetails/'.$person_id);
	}

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

		header("Content-type: ".$akte->retval[0]->mimetype);
		header('Content-Disposition: attachment; filename="'.$akte->retval[0]->titel.'"');
		echo $aktecontent->retval;
	}

	private function __redirectToStart($prestudent_id)
	{
		$this->PrestudentModel->addSelect('person_id');
		$person_id = $this->PrestudentModel->load($prestudent_id)->retval[0]->person_id;
		redirect('/system/infocenter/InfocenterDetails/showDetails/'.$person_id);
	}

	private function __getPersonAndStudiengangFromPrestudent($prestudent_id)
	{
		$this->PrestudentModel->addSelect('person_id, studiengang_kz');
		$prestudent = $this->PrestudentModel->load($prestudent_id);

		if ($prestudent->error)
		{
			show_error($prestudent->retval);
		}

		$person_id = $prestudent->retval[0]->person_id;

		$this->StudiengangModel->addSelect('kurzbzlang');//TODO need bezeichnung?
		$studiengang = $this->StudiengangModel->load($prestudent->retval[0]->studiengang_kz);

		if ($studiengang->error)
		{
			show_error($studiengang->retval);
		}

		$studiengang_kurzbz = $studiengang->retval[0]->kurzbzlang;

		return array('person_id' => $person_id, 'studiengang_kurzbz' => $studiengang_kurzbz);
	}

}