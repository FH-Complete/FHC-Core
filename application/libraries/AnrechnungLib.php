<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class AnrechnungLib
{
	const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
	const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
	const ANRECHNUNGSTATUS_APPROVED = 'approved';
	const ANRECHNUNGSTATUS_REJECTED = 'rejected';
	
	public function __construct()
	{
		$this->ci =& get_instance();
		
		$this->ci->load->model('education/Anrechnung_model', 'AnrechnungModel');
		$this->ci->load->model('person/Person_model', 'PersonModel');
		$this->ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->ci->load->model('crm/Student_model', 'StudentModel');
		$this->ci->load->model('content/DmsVersion_model', 'DmsVersionModel');
		$this->ci->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');
	}
	
	/**
	 * Get Antrag data
	 * @param $uid
	 * @param $studiensemester_kurzbz
	 * @param $lv_id
	 * @return StdClass
	 */
	public function getAntragData($uid, $studiensemester_kurzbz, $lv_id)
	{
		$antrag_data = new StdClass();

		// Get lehrveranstaltung data. Break, if course is not assigned to student.
		if(!$lv = getData($this->ci->LehrveranstaltungModel->getLvByStudent($uid, $studiensemester_kurzbz, $lv_id))[0])
		{
			show_error('You are not assigned to this course yet.');
		}
		
		// Get the students personal data
		if (!$person = getData($this->ci->PersonModel->getByUid($uid))[0])
		{
			show_error('Failed loading person data.');
		}
		
		// Get the internal personenkennzeichen
		if (!$student = getData($this->ci->StudentModel->load(array('student_uid' => $uid)))[0])
		{
			show_error(getError($student));
		}
		
		// Get studiengang bezeichnung
		if (!$studiengang = getData($this->ci->StudiengangModel->load($lv->studiengang_kz))[0])
		{
			show_error('Failed loading studiengang data.');
		}

		// Get lectors of lehrveranstaltung
		$antrag_data->lektoren = array();
		if (!$lv_lektoren = getData($this->ci->LehrveranstaltungModel->getLecturersByLv($studiensemester_kurzbz, $lv_id)))
		{
			show_error('Failed loading course lectors.');
		}
		
		// Set the given studiensemester
		$antrag_data->lv_id = $lv_id;
		$antrag_data->lv_bezeichnung = $lv->bezeichnung;
		$antrag_data->ects = $lv->ects;
		$antrag_data->studiensemester_kurzbz = $studiensemester_kurzbz;
		$antrag_data->vorname = $person->vorname;
		$antrag_data->nachname = $person->nachname;
		$antrag_data->matrikelnr = $student->matrikelnr;
		$antrag_data->stg_bezeichnung = $studiengang->bezeichnung;
		$antrag_data->lektoren = $lv_lektoren;
		
		return $antrag_data;
	}
	
	/**
	 * Get Anrechnung data, last status and Nachweisdokument dms data.
	 * @param $anrechnung_id
	 * @return array
	 * @throws Exception
	 */
	public function getAnrechnungData($anrechnung_id)
	{
		if (!is_numeric($anrechnung_id))
		{
			show_error('Incorrect parameter');
		}
		
		$anrechnung_data = new StdClass();
		
		$result = $this->ci->AnrechnungModel->load($anrechnung_id);
		
		if (isError($result))
		{
			show_error(getError($result));
		}
		
		if ($anrechnung = getData($result)[0])
		{
			$anrechnung_data = $this->_setAnrechnungDataObject($anrechnung);
		}
		
		return success($anrechnung_data);
		
	}
	
	/**
	 * Get Anrechnung data by Lehrveranstaltung. Also retrieves last status and Nachweisdokument dms data.
	 * @param $lehrveranstaltung_id
	 * @return array
	 * @throws Exception
	 */
	public function getAnrechnungDataByLv($lehrveranstaltung_id)
	{
		$anrechnung_data = new StdClass();
		$anrechnung_data->anrechnung_id = '';
		$anrechnung_data->begruendung_id = '';
		$anrechnung_data->anmerkung = '';
		$anrechnung_data->dms_id = '';
		$anrechnung_data->insertamum = '';
		$anrechnung_data->insertvon = '';
		$anrechnung_data->studiensemester_kurzbz = '';
		$anrechnung_data->empfehlung = '';
		$anrechnung_data->status = '';
		$anrechnung_data->dokumentname = '';
		
		$result = $this->ci->AnrechnungModel->loadWhere(array('lehrveranstaltung_id' => $lehrveranstaltung_id));
		
		if (isError($result))
		{
			show_error(getError($result));
		}
		
		if ($anrechnung = getData($result)[0])
		{
			$anrechnung_data = $this->_setAnrechnungDataObject($anrechnung);
		}
		
		return success($anrechnung_data);
	}
	
	/**
	 * @param $anrechnung_id
	 * @return mixed
	 */
	public function getLastAnrechnungstatus($anrechnung_id)
	{
		$result = $this->ci->AnrechnungModel->getLastAnrechnungstatus($anrechnung_id);
		
		$status_mehrsprachig = getData($result)[0]->bezeichnung_mehrsprachig;
		$status = getUserLanguage() == 'German' ? $status_mehrsprachig[0] : $status_mehrsprachig[1];
		
		return $status;
	}
	
	/**
	 * Approve Anrechnung.
	 * Checks last status of Anrechnung and will only approve if last status is not approved or rejected.
	 * @param $anrechnung_id
	 * @return array
	 * @throws Exception
	 */
	public function approveAnrechnung($anrechnung_id)
	{
		// Check last Anrechnungstatus
		if (!$result = getData($this->ci->AnrechnungModel->getLastAnrechnungstatus($anrechnung_id))[0])
		{
			show_error(getError($result));
		}
		
		$status_kurzbz = $result->status_kurzbz;
	
		// Exit if already approved or rejected
		if ($status_kurzbz == self::ANRECHNUNGSTATUS_APPROVED || $status_kurzbz == self::ANRECHNUNGSTATUS_REJECTED) // TODO: in js: bereits genehmigte nicht clickable!
		{
			return success(false);  // dont approve
		}
		
		// Start DB transaction
		$this->ci->db->trans_start(false);

		$stgl_uid = getAuthUID();
		
		// Insert new status approved
		$this->ci->AnrechnungModel->saveAnrechnungstatus($anrechnung_id, self::ANRECHNUNGSTATUS_APPROVED);

		// Update genehmigt von
		$this->ci->AnrechnungModel->update(
			$anrechnung_id,
			array(
				'genehmigt_von' => $stgl_uid,
				'updateamum'    => (new DateTime())->format('Y-m-d H:m:i'),
				'updatevon'     => $stgl_uid
			)
		);

		// Set zeugnisnote to angerechnet (= note 6)
		$this->ci->AnrechnungModel->addSelect('lehrveranstaltung_id, student_uid, studiensemester_kurzbz');
		$this->ci->AnrechnungModel->addJoin('public.tbl_student', 'prestudent_id');
		$anrechnung = getData($this->ci->AnrechnungModel->load($anrechnung_id))[0];
		$result = $this->ci->ZeugnisnoteModel->insert(array(
				'lehrveranstaltung_id' => $anrechnung->lehrveranstaltung_id,
				'student_uid' => $anrechnung->student_uid,
				'studiensemester_kurzbz' => $anrechnung->studiensemester_kurzbz,
				'note' => 6,
				'insertvon' => $stgl_uid,
				'bemerkung' => 'Digitale Anrechnung'
			)
		);
		
		// Transaction complete
		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
		{
			$this->ci->db->trans_rollback();
			return error($result->msg, EXIT_ERROR);
		}
		
		return success(true);   // approved
	}
	
	public function rejectAnrechnung($anrechnung_id)
	{
		// Check last Anrechnungstatus
		if (!$result = getData($this->ci->AnrechnungModel->getLastAnrechnungstatus($anrechnung_id))[0])
		{
			show_error(getError($result));
		}
		
		$status_kurzbz = $result->status_kurzbz;
		
		// Exit if already approved or rejected
		if ($status_kurzbz == self::ANRECHNUNGSTATUS_APPROVED || $status_kurzbz == self::ANRECHNUNGSTATUS_REJECTED) // TODO: in js: bereits genehmigte nicht clickable!
		{
			return success(false);  // dont reject
		}
		
		// Insert new status rejected
		$result = $this->ci->AnrechnungModel->saveAnrechnungstatus($anrechnung_id, self::ANRECHNUNGSTATUS_REJECTED);
		
		if (isError($result))
		{
			show_error(getError($result));
		}
		
		return success(true);   // rejected
	}
	
	private function _setAnrechnungDataObject($anrechnung)
	{
		$anrechnung_data = new StdClass();
		
		// Get Anrechnung data
		$anrechnung_data->anrechnung_id = $anrechnung->anrechnung_id;
		$anrechnung_data->begruendung_id =  $anrechnung->begruendung_id;
		$anrechnung_data->anmerkung = $anrechnung->anmerkung_student;
		$anrechnung_data->dms_id = $anrechnung->dms_id;
		$anrechnung_data->insertamum = (new DateTime($anrechnung->insertamum))->format('d.m.Y');
		$anrechnung_data->insertvon= $anrechnung->insertvon;
		$anrechnung_data->studiensemester_kurzbz= $anrechnung->studiensemester_kurzbz;
		$anrechnung_data->empfehlung= $anrechnung->empfehlung_anrechnung;
		
		// Get last status bezeichnung in the users language
		$anrechnung_data->status = $this->getLastAnrechnungstatus($anrechnung->anrechnung_id);
		
		// Get document name
		$this->ci->DmsVersionModel->addSelect('name');
		$result = $this->ci->DmsVersionModel->loadWhere(array('dms_id' => $anrechnung->dms_id));
		
		if (isError($result))
		{
			show_error(getError($result));
		}
		
		$anrechnung_data->dokumentname  = $result->retval[0]->name;
		
		return $anrechnung_data;
	}
}