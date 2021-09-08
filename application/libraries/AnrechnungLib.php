<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class AnrechnungLib
{
	const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
	const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
	const ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR = 'inProgressLektor';
	const ANRECHNUNGSTATUS_APPROVED = 'approved';
	const ANRECHNUNGSTATUS_REJECTED = 'rejected';

	const ANRECHNUNG_NOTIZTITEL_NOTIZ_BY_LEKTOR = 'AnrechnungNotizLektor';
	const ANRECHNUNG_NOTIZTITEL_NOTIZ_BY_STGL = 'AnrechnungNotizSTGL';
	const ANRECHNUNG_NOTIZTITEL_EMPFEHLUNGSNOTIZ_BY_STGL = 'AnrechnungEmpfehlungsnotizSTGL';

	public function __construct()
	{
		$this->ci =& get_instance();

		$this->ci->load->model('education/Anrechnung_model', 'AnrechnungModel');
		$this->ci->load->model('person/Person_model', 'PersonModel');
		$this->ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->ci->load->model('crm/Student_model', 'StudentModel');
		$this->ci->load->model('content/DmsVersion_model', 'DmsVersionModel');
		$this->ci->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->ci->load->model('person/Notiz_model', 'NotizModel');
		
		$this->ci->load->library('DmsLib');
	}

	/**
	 * Get Antrag data
	 * @param $uid
	 * @param $studiensemester_kurzbz
	 * @param $lv_id
	 * @return StdClass
	 */
	public function getAntragData($prestudent_id, $studiensemester_kurzbz, $lv_id)
	{
		$antrag_data = new StdClass();
		
		// Get students UID.
		$uid = $this->ci->StudentModel->getUID($prestudent_id);
		
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
		$result = $this->ci->LehrveranstaltungModel->getLecturersByLv($studiensemester_kurzbz, $lv_id);
		if (isError($result))
		{
			show_error('Failed loading course lectors.');
		}
		
		$lv_lektoren_arr =  hasData($result) ? getData($result) : array();
		
		// Get latest ZGV
		$result = $this->ci->PrestudentModel->getLatestZGVBezeichnung($prestudent_id);
		$latest_zgv_bezeichnung = hasData($result) ? getData($result)[0]->bezeichnung : '';
		
		// Set the given studiensemester
		$antrag_data->lv_id = $lv_id;
		$antrag_data->lv_bezeichnung = $lv->bezeichnung;
		$antrag_data->ects = $lv->ects;
		$antrag_data->studiensemester_kurzbz = $studiensemester_kurzbz;
		$antrag_data->vorname = $person->vorname;
		$antrag_data->nachname = $person->nachname;
		$antrag_data->matrikelnr = $student->matrikelnr;
		$antrag_data->stg_bezeichnung = $studiengang->bezeichnung;
		$antrag_data->lektoren = $lv_lektoren_arr;
		$antrag_data->zgv = $latest_zgv_bezeichnung;

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
		else
		{
			show_error('No Anrechnung with this anrechnung_id.');
		}

		return $anrechnung_data;

	}

	/**
	 * Get Anrechnung data by Lehrveranstaltung. Also retrieves last status and Nachweisdokument dms data.
	 * @param $lehrveranstaltung_id
	 * @return array
	 * @throws Exception
	 */
	public function getAnrechnungDataByLv($lehrveranstaltung_id, $studiensemester_kurzbz, $prestudent_id)
	{
		$anrechnung_data = new StdClass();
		$anrechnung_data->anrechnung_id = '';
		$anrechnung_data->prestudent_id = '';
		$anrechnung_data->lehrveranstaltung = '';
		$anrechnung_data->begruendung_id = '';
		$anrechnung_data->anmerkung = '';
		$anrechnung_data->dms_id = '';
		$anrechnung_data->insertamum = '';
		$anrechnung_data->insertvon = '';
		$anrechnung_data->studiensemester_kurzbz = '';
		$anrechnung_data->empfehlung = '';
		$anrechnung_data->status_kurzbz = '';
		$anrechnung_data->status = getUserLanguage() == 'German' ? 'neu' : 'new';
		$anrechnung_data->dokumentname = '';

		$result = $this->ci->AnrechnungModel->loadWhere(
			array(
				'lehrveranstaltung_id' => $lehrveranstaltung_id,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'prestudent_id' => $prestudent_id
			)
		);

		if (isError($result))
		{
			show_error(getError($result));
		}

		if ($anrechnung = getData($result)[0])
		{
			$anrechnung_data = $this->_setAnrechnungDataObject($anrechnung);
		}

		return $anrechnung_data;
	}

	/**
	 * Get students data by Anrechnung
	 * @param $anrechnung_id
	 * @return mixed
	 */
	public function getStudentData($anrechnung_id)
	{
		if (!is_numeric($anrechnung_id))
		{
			show_error('Incorrect parameter');
		}

		$this->ci->AnrechnungModel->addSelect('tbl_benutzer.uid, tbl_prestudent.prestudent_id, tbl_person.person_id, tbl_anrechnung.studiensemester_kurzbz, vorname, nachname, geschlecht, tbl_lehrveranstaltung.bezeichnung AS "lv_bezeichnung"');
		$this->ci->AnrechnungModel->addJoin('public.tbl_prestudent', 'prestudent_id');
		$this->ci->AnrechnungModel->addJoin('public.tbl_student', 'prestudent_id');
		$this->ci->AnrechnungModel->addJoin('public.tbl_benutzer', 'uid = student_uid');
		$this->ci->AnrechnungModel->addJoin('public.tbl_person', 'tbl_benutzer.person_id = tbl_person.person_id');
		$this->ci->AnrechnungModel->addJoin('lehre.tbl_lehrveranstaltung', 'lehrveranstaltung_id');

		$result = $this->ci->AnrechnungModel->load($anrechnung_id);

		if (isError($result))
		{
			show_error(getError($result));
		}

		if (!hasData($result))
		{
			show_error('Failed retrieving students data');
		}

		return $result;
	}

	/**
	 * Get Empfehlung data object.
	 * @param $anrechnung_id
	 * @return array
	 * @throws Exception
	 */
	public function getEmpfehlungData($anrechnung_id)
	{
		if (!is_numeric($anrechnung_id))
		{
			show_error('Incorrect parameter');
		}

		$empfehlung_data = new stdClass();
		$empfehlung_data->empfehlung = null;
		$empfehlung_data->empfehlung_von = '-';
		$empfehlung_data->empfehlung_am = '-';
		$empfehlung_data->empfehlungsanfrageAm = '-';
		$empfehlung_data->empfehlungsanfrageAn = '-';
		$empfehlung_data->begruendung = '-';   // Begruendung, if not recommended
		$empfehlung_data->notiz_id = '';  // Empfehlungsnotiz from STGL
		$empfehlung_data->notiz = '';  // Empfehlungsnotiz from STGL
		

		if(!$anrechnung = getData($this->ci->AnrechnungModel->load($anrechnung_id))[0])
		{
			show_error('Failed loading Anrechnung');
		}
		
		// Get Empfehlungsnotiz
		$result = $this->ci->NotizModel->getNotizByAnrechnung(
			$anrechnung_id,
			self::ANRECHNUNG_NOTIZTITEL_EMPFEHLUNGSNOTIZ_BY_STGL
		);
		
		if ($notiz = getData($result)[0])
		{
			$empfehlung_data->notiz_id = $notiz->notiz_id;
			$empfehlung_data->notiz = $notiz->text;
		}

		// Get date, where recommendation was last requested
		$result = $this->ci->AnrechnungModel->getLastAnrechnungstatus(
			$anrechnung_id,
			self::ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR   //  when STLG asks for recommendation, status is set to in progress lektor
		);
		
		// If request for recommendation exists
		if (hasData($result))
		{
			$empfehlung_data->empfehlungsanfrageAm = (new DateTime($result->retval[0]->insertamum))->format('d.m.Y');
			
			// Get lectors who received request for recommendation
			$lector_arr = self::getLectors($anrechnung_id);
			
			if (!isEmptyArray($lector_arr))
			{
				$empfehlung_data->empfehlungsanfrageAn = implode(', ', array_column($lector_arr, 'fullname'));
			}
		}

		if (is_null($anrechnung->empfehlung_anrechnung))
		{
			return $empfehlung_data;
		}

		// If Empfehlung is true or false
		if (!is_null($anrechnung->empfehlung_anrechnung))
		{
			// Get last lector and date, where recommendation was given
			$result = $this->ci->AnrechnungModel->getLastAnrechnungstatus(
				$anrechnung_id,
				self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL   //  when lector sends recommendation, status is set to in progress STGL again
			);
			if ($result = getData($result)[0])
			{
				$empfehlung_datum = (new DateTime($result->insertamum))->format('d.m.Y');
			}

			// Get full name of lector
			$result = $this->ci->PersonModel->getByUID($result->insertvon);
			if ($result = getData($result)[0])
			{
				$empfehlung_von = $result->vorname. ' '. $result->nachname;
			}

			$empfehlung_data->empfehlung    = $anrechnung->empfehlung_anrechnung;
			$empfehlung_data->empfehlung_von     = $empfehlung_von;
			$empfehlung_data->empfehlung_am    = $empfehlung_datum;
		}

		// If Empfehlung is false, retrieve also Notiz with Begruendung
		if (!$anrechnung->empfehlung_anrechnung)
		{
			// Get Ablehnungsbegruendung (only set, if Anrechnung was not recommended yet)
			$result = $this->ci->NotizModel->getNotizByAnrechnung($anrechnung_id, self::ANRECHNUNG_NOTIZTITEL_NOTIZ_BY_LEKTOR);
			if ($notiz = getData($result)[0])
			{
				$empfehlung_data->begruendung = $notiz->text;
			}
		}

		return $empfehlung_data;

	}

	/**
	 * Get Genehmigung data object.
	 * @param $anrechnung_id
	 * @return array
	 * @throws Exception
	 */
	public function getGenehmigungData($anrechnung_id)
	{
		if (!is_numeric($anrechnung_id))
		{
			show_error('Incorrect parameter');
		}

		$genehmigung_data = new stdClass();
		$genehmigung_data->genehmigung = null;
		$genehmigung_data->abgeschlossen_von = '-';
		$genehmigung_data->abgeschlossen_am = '-';
		$genehmigung_data->notiz = '';   // Begruendung, if rejected


		if(!$anrechnung = getData($this->ci->AnrechnungModel->load($anrechnung_id))[0])
		{
			show_error('Failed loading Anrechnung');
		}

		// Get date of approvement or rejection
		$result = $this->ci->AnrechnungModel->getApprovedOrRejected($anrechnung_id);

		// If no approved or rejected Anrechnung exist, return basic genehmigung data object
		if (!$result = getData($result)[0])
		{
			return $genehmigung_data;
		}


		$genehmigung_data->genehmigung = $result->status_kurzbz == self::ANRECHNUNGSTATUS_APPROVED
			? true
			: false;
		$genehmigung_data->abgeschlossen_am = (new DateTime($result->insertamum))->format('d.m.Y');

		// Get full name of lector
		$result = $this->ci->PersonModel->getByUID($result->insertvon);
		if ($result = getData($result)[0])
		{
			$genehmigung_data->abgeschlossen_von = $result->vorname. ' '. $result->nachname;
		}


		// If Anrechnung was rejected, retrieve also Notiz with Begruendung
		if (!$genehmigung_data->genehmigung)
		{
			// Get Ablehnungsbegruendung (only set, if Anrechnung was not recommended yet)
			$this->ci->load->model('person/Notiz_model', 'NotizModel');
			$result = $this->ci->NotizModel->getNotizByAnrechnung($anrechnung_id, self::ANRECHNUNG_NOTIZTITEL_NOTIZ_BY_STGL);
			if ($notiz = getData($result)[0])
			{
				$genehmigung_data->notiz = $notiz->text;
			}
		}

		return $genehmigung_data;

	}
	
	/**
	 * Get Anrechnungstatusbezeichnung of given status_kurzbz in users language.
	 *
	 * @param $status_kurzbz
	 * @return mixed
	 */
	public function getStatusbezeichnung ($status_kurzbz)
	{
		$this->ci->AnrechnungstatusModel->addSelect('bezeichnung_mehrsprachig');
		$result = $this->ci->AnrechnungstatusModel->load($status_kurzbz);
		
		if (!hasData($result))
		{
			show_error('Failed retrieving Anrechnungstatusbezeichung');
		}
		
		return getUserLanguage() == 'German'
			? $result->retval[0]->bezeichnung_mehrsprachig[0]
			: $result->retval[0]->bezeichnung_mehrsprachig[1];
	}

	/**
	 * Get last Anrechnungstatusbezeichnung in users language.
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
		if ($status_kurzbz == self::ANRECHNUNGSTATUS_APPROVED || $status_kurzbz == self::ANRECHNUNGSTATUS_REJECTED)
		{
			return false;  // dont approve
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
				'genehmigt_von' => $stgl_uid
			)
		);

		// Transaction complete
		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
		{
			$this->ci->db->trans_rollback();
			return false;
		}

		return true;   // approved
	}

	/**
	 * Reject Anrechnung.
	 * @param $anrechnung_id
	 * @return array
	 */
	public function rejectAnrechnung($anrechnung_id, $begruendung)
	{
		// Check last Anrechnungstatus
		if (!$result = getData($this->ci->AnrechnungModel->getLastAnrechnungstatus($anrechnung_id))[0])
		{
			show_error(getError($result));
		}

		$status_kurzbz = $result->status_kurzbz;

		// Exit if already approved or rejected
		if ($status_kurzbz == self::ANRECHNUNGSTATUS_APPROVED || $status_kurzbz == self::ANRECHNUNGSTATUS_REJECTED)
		{
			return false;  // dont reject
		}
		
		// Start DB transaction
		$this->ci->db->trans_start(false);

		// Insert new status rejected
		$this->ci->AnrechnungModel->saveAnrechnungstatus($anrechnung_id, self::ANRECHNUNGSTATUS_REJECTED);

		// Add begruendung as notiz
		$this->ci->load->model('person/Notiz_model', 'NotizModel');
		$this->ci->NotizModel->addNotizForAnrechnung(
			$anrechnung_id,
			self::ANRECHNUNG_NOTIZTITEL_NOTIZ_BY_STGL,
			$begruendung,
			getAuthUID()
		);
		
		// Transaction complete
		$this->ci->db->trans_complete();
		
		if ($this->ci->db->trans_status() === false)
		{
			$this->ci->db->trans_rollback();
			return false;
		}

		return true;   // rejected
	}

	/**
	 * Request recommendation.
	 * @param $anrechnung_id
	 * @return array
	 */
	public function requestRecommendation($anrechnung_id)
	{
		// Check last Anrechnungstatus
		if (!$result = getData($this->ci->AnrechnungModel->getLastAnrechnungstatus($anrechnung_id))[0])
		{
			show_error(getError($result));
		}

		$status_kurzbz = $result->status_kurzbz;

		// Exit if already approved or rejected or processed by lector
		if ($status_kurzbz == self::ANRECHNUNGSTATUS_APPROVED
			|| $status_kurzbz == self::ANRECHNUNGSTATUS_REJECTED
			|| $status_kurzbz == self::ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR)
		{
			return false;  // dont ask for recommendation
		}

		// Start DB transaction
		$this->ci->db->trans_start(false);

		// Insert new status inProgressLektor
		$result = $this->ci->AnrechnungModel->saveAnrechnungstatus($anrechnung_id, self::ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR);

		/**
		 * Anyway update empfehlung_anrechnung to be null
		 * Regardless of what empfehlung_anrechnung was already set (true/false/null), it should be (reset to ) null by
		 * requesting a (new) recommendation.
		 * **/
		$this->ci->AnrechnungModel->update(
			$anrechnung_id,
			array(
				'empfehlung_anrechnung' => null
			)
		);

		// Transaction complete
		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
		{
			$this->ci->db->trans_rollback();
			return error($result->msg, EXIT_ERROR);
		}

		return true;   // recommended
	}

	/**
	 * Recommend Anrechnung.
	 * @param $anrechnung_id
	 * @return array
	 * @throws Exception
	 */
	public function recommendAnrechnung($anrechnung_id)
	{
		// Check last Anrechnungstatus
		if (!$result = getData($this->ci->AnrechnungModel->getLastAnrechnungstatus($anrechnung_id))[0])
		{
			show_error(getError($result));
		}

		$status_kurzbz = $result->status_kurzbz;

		// Exit if already approved or rejected
		if ($status_kurzbz == self::ANRECHNUNGSTATUS_APPROVED || $status_kurzbz == self::ANRECHNUNGSTATUS_REJECTED)
		{
			return success(false);  // dont approve
		}

		// Start DB transaction
		$this->ci->db->trans_start(false);

		// Insert new status progessed by stgl
		$this->ci->AnrechnungModel->saveAnrechnungstatus($anrechnung_id, self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL);

		// Update empfehlung_anrechnung
		$this->ci->AnrechnungModel->update(
			$anrechnung_id,
			array(
				'empfehlung_anrechnung' => true
			)
		);

		// Transaction complete
		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
		{
			$this->ci->db->trans_rollback();
			return false;
		}

		return true;   // recommended
	}

	/**
	 * Do not recommend Anrechnung.
	 * @param $anrechnung_id
	 * @return array
	 * @throws Exception
	 */
	public function dontRecommendAnrechnung($anrechnung_id, $begruendung)
	{
		// Check last Anrechnungstatus
		if (!$result = getData($this->ci->AnrechnungModel->getLastAnrechnungstatus($anrechnung_id))[0])
		{
			show_error(getError($result));
		}

		$status_kurzbz = $result->status_kurzbz;

		// Exit if already approved or rejected
		if ($status_kurzbz == self::ANRECHNUNGSTATUS_APPROVED || $status_kurzbz == self::ANRECHNUNGSTATUS_REJECTED)
		{
			return false;  // dont approve
		}

		// Start DB transaction
		$this->ci->db->trans_start(false);

		// Insert new status progessed by stgl
		$this->ci->AnrechnungModel->saveAnrechnungstatus($anrechnung_id, self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL);

		// Update empfehlung_anrechnung
		$this->ci->AnrechnungModel->update(
			$anrechnung_id,
			array(
				'empfehlung_anrechnung' => false
			)
		);

		$lektor_uid = getAuthUID();

		// Add begruendung as notiz
		$this->ci->load->model('person/Notiz_model', 'NotizModel');
		$this->ci->NotizModel->addNotizForAnrechnung(
			$anrechnung_id,
			self::ANRECHNUNG_NOTIZTITEL_NOTIZ_BY_LEKTOR,
			$begruendung,
			$lektor_uid
		);

		// Transaction complete
		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
		{
			$this->ci->db->trans_rollback();
			return false;
		}

		return true;   // recommended
	}
	
	/**
	 * Set Filename that should be used on download
	 * @param $dms_id
	 * @return string|null
	 */
	public function setFilenameOnDownload($dms_id)
	{
		// Load Anrechnung
		$result = $this->ci->AnrechnungModel->loadWhere(array('dms_id' => $dms_id));
		
		// Return null if no data found
		if (!hasData($result))
		{
			return null;
		}
		
		$prestudent_id = $result->retval[0]->prestudent_id;
		$lehrveranstaltung_id = $result->retval[0]->lehrveranstaltung_id;
		
		// Get LV OrgForm
		$this->ci->LehrveranstaltungModel->addSelect('stg.orgform_kurzbz');
		$this->ci->LehrveranstaltungModel->addJoin('public.tbl_studiengang AS stg', 'studiengang_kz');
		$result = $this->ci->LehrveranstaltungModel->load($lehrveranstaltung_id);
		$orgform_kurzbz = hasData($result) ? '_'. $result->retval[0]->orgform_kurzbz : '';
		
		// Get full name of student
		$this->ci->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->ci->PrestudentModel->addSelect('vorname, nachname');
		$this->ci->PrestudentModel->addJoin('public.tbl_person', 'person_id');
		$result = $this->ci->PrestudentModel->load($prestudent_id);
		$fullname = hasData($result) ? $result->retval[0]->vorname. $result->retval[0]->nachname : '';
		
		// Return filename
		return 'Anrechnungsantrag'. $orgform_kurzbz .'_LV-'. $lehrveranstaltung_id. '_'. $fullname;
	}
	
	public function LVhasLector($anrechnung_id)
	{
		$result = $this->ci->AnrechnungModel->load($anrechnung_id);
		if (!hasData($result))
		{
			showError('Anrechnung existiert nicht');
		}
		
		// Get lectors of lehrveranstaltung
		$result = $this->ci->LehrveranstaltungModel->getLecturersByLv(
			$result->retval[0]->studiensemester_kurzbz,
			$result->retval[0]->lehrveranstaltung_id
		);
		
		// Continue, if LV has no lector (there is no one to ask for recommendation)
		return hasData($result) ? true : false;
	}
	
	/**
	 * Get LV Leitung. If not present, get all LV lectors.
	 *
	 * @param $anrechnung_id
	 * @return array|bool
	 */
	public function getLectors($anrechnung_id)
	{
		$this->ci->AnrechnungModel->addSelect('lehrveranstaltung_id, studiensemester_kurzbz');
		$result = $this->ci->AnrechnungModel->load($anrechnung_id);
		
		if (!hasData($result))
		{
			return false;
		}
		
		$lehrveranstaltung_id = getData($result)[0]->lehrveranstaltung_id;
		$studiensemester_kurzbz = getData($result)[0]->studiensemester_kurzbz;
		
		// Get lectors
		$lector_arr = array();
		
		$this->ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$result = $this->ci->LehrveranstaltungModel->getLecturersByLv($studiensemester_kurzbz, $lehrveranstaltung_id);
		
		if (!$result = getData($result))
		{
			return false;
		}
		
		// Check if lv has LV-Leitung
		$key = array_search(true, array_column($result, 'lvleiter'));
		
		// If lv has LV-Leitung, keep only the one
		if ($key !== false)
		{
			$lector_arr[]= $result[$key];
		}
		// ...otherwise keep all lectors
		else
		{
			$lector_arr = array_merge($lector_arr, $result);
		}
		
		/**
		 * NOTE: This step is only done to make the array unique by uid, vorname and nachname in the following step
		 * (e.g. if same lector is ones LV-Leitung and another time not, then array_unique would leave both.
		 * But we wish to send only one email by to that one person)
		 * **/
		foreach ($lector_arr as $lector)
		{
			unset($lector->lvleiter);
			$lector->fullname = $lector->vorname. ' '. $lector->nachname;
		}
		
		// Now make the lector array aka mail receivers unique
		$lector_arr = array_unique($lector_arr, SORT_REGULAR);
		
		return $lector_arr;
	}

	// Return an object with Anrechnungdata
	private function _setAnrechnungDataObject($anrechnung)
	{
		$anrechnung_data = new StdClass();

		// Get Anrechnung data
		$anrechnung_data->anrechnung_id = $anrechnung->anrechnung_id;
		$anrechnung_data->prestudent_id = $anrechnung->prestudent_id;
		$anrechnung_data->lehrveranstaltung_id = $anrechnung->lehrveranstaltung_id;
		$anrechnung_data->begruendung_id =  $anrechnung->begruendung_id;
		$anrechnung_data->anmerkung = $anrechnung->anmerkung_student;
		$anrechnung_data->dms_id = $anrechnung->dms_id;
		$anrechnung_data->insertamum = (new DateTime($anrechnung->insertamum))->format('d.m.Y');
		$anrechnung_data->insertvon= $anrechnung->insertvon;
		$anrechnung_data->studiensemester_kurzbz= $anrechnung->studiensemester_kurzbz;
		$anrechnung_data->empfehlung= $anrechnung->empfehlung_anrechnung;

		// Get last status_kurzbz
		$result = $this->ci->AnrechnungModel->getLastAnrechnungstatus($anrechnung->anrechnung_id);
		$anrechnung_data->status_kurzbz = $result->retval[0]->status_kurzbz;

		// Get last status bezeichnung in the users language
		$anrechnung_data->status = $this->getLastAnrechnungstatus($anrechnung->anrechnung_id);


		// Get document name
		$this->ci->DmsVersionModel->addSelect('name');
		$result = $this->ci->DmsVersionModel->loadWhere(array('dms_id' => $anrechnung->dms_id));

		$anrechnung_data->dokumentname  = hasData($result) ? getData($result)[0]->name : '';

		return $anrechnung_data;
	}
}
