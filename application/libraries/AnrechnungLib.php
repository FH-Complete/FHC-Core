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

	public function __construct()
	{
		$this->ci =& get_instance();

		$this->ci->load->model('education/Anrechnung_model', 'AnrechnungModel');
		$this->ci->load->model('person/Person_model', 'PersonModel');
		$this->ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->ci->load->model('crm/Student_model', 'StudentModel');
		$this->ci->load->model('content/DmsVersion_model', 'DmsVersionModel');
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

		return success($anrechnung_data);
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
		$this->ci->AnrechnungModel->addJoin('public.tbl_benutzer', 'uid=student_uid');
		$this->ci->AnrechnungModel->addJoin('public.tbl_person', 'tbl_benutzer.person_id=tbl_person.person_id');
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
		$empfehlung_data->empfehlung_angefordert_am = '-';
		$empfehlung_data->notiz = '';   // Begruendung, if not recommended


		if(!$anrechnung = getData($this->ci->AnrechnungModel->load($anrechnung_id))[0])
		{
			show_error('Failed loading Anrechnung');
		}

		// Get date, where recommendation was last requested
		$result = $this->ci->AnrechnungModel->getLastAnrechnungstatus(
			$anrechnung_id,
			self::ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR   //  when STLG asks for recommendation, status is set to in progress lektor
		);
		if ($result = getData($result)[0])
		{
			$empfehlung_data->empfehlung_angefordert_am = (new DateTime($result->insertamum))->format('d.m.Y');
		}

		if (is_null($anrechnung->empfehlung_anrechnung))
		{
			return success($empfehlung_data);
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
			$this->ci->load->model('person/Notiz_model', 'NotizModel');
			$result = $this->ci->NotizModel->getNotizByAnrechnung($anrechnung_id, self::ANRECHNUNG_NOTIZTITEL_NOTIZ_BY_LEKTOR);
			if ($notiz = getData($result)[0])
			{
				$empfehlung_data->notiz = $notiz->text;
			}
		}

		return success($empfehlung_data);

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

		if (!$result = getData($result)[0])
		{
			return success($genehmigung_data);
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

		return success($genehmigung_data);

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
				'genehmigt_von' => $stgl_uid
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
			return success(false);  // dont reject
		}

		// Insert new status rejected
		$result = $this->ci->AnrechnungModel->saveAnrechnungstatus($anrechnung_id, self::ANRECHNUNGSTATUS_REJECTED);

		if (isError($result))
		{
			show_error(getError($result));
		}

		// Add begruendung as notiz
		$this->ci->load->model('person/Notiz_model', 'NotizModel');
		$this->ci->NotizModel->addNotizForAnrechnung(
			$anrechnung_id,
			self::ANRECHNUNG_NOTIZTITEL_NOTIZ_BY_STGL,
			$begruendung,
			getAuthUID()
		);

		return success(true);   // rejected
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
			return success(false);  // dont ask for recommendation
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

		return success(true);   // recommended
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
			return error($result->msg, EXIT_ERROR);
		}

		return success(true);   // recommended
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
			return error($result->msg, EXIT_ERROR);
		}

		return success(true);   // recommended
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

		if (isError($result))
		{
			show_error(getError($result));
		}

		$anrechnung_data->dokumentname  = $result->retval[0]->name;

		return $anrechnung_data;
	}
}
