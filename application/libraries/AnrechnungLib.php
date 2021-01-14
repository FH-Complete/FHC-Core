<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class AnrechnungLib
{
	public function __construct()
	{
		$this->ci =& get_instance();
		
		$this->ci->load->model('education/Anrechnung_model', 'AnrechnungModel');
		$this->ci->load->model('person/Person_model', 'PersonModel');
		$this->ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
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
		$antrag_data->bpk = $person->bpk;
		$antrag_data->stg_bezeichnung = $studiengang->bezeichnung;
		$antrag_data->lektoren = $lv_lektoren;
		
		return $antrag_data;
	}
	
	/**
	 * Get Anrechnung data by Lehrveranstaltung. Also retrieves last status and Nachweisdokument dms data.
	 * @param $lehrveranstaltung_id
	 * @return array
	 * @throws Exception
	 */
	public function getAnrechnungData($lehrveranstaltung_id)
	{
		$anrechnung_data = new StdClass();
		$anrechnung_data->anrechnung_id = '';
		$anrechnung_data->begruendung_id = '';
		$anrechnung_data->anmerkung = '';
		$anrechnung_data->dms_id = '';
		$anrechnung_data->insertamum = '';
		$anrechnung_data->insertvon = '';
		$anrechnung_data->studiensemester_kurzbz = '';
		$anrechnung_data->empfehlung = false;
		$anrechnung_data->status = '';
		$anrechnung_data->dokumentname = '';
		
		$result = $this->ci->AnrechnungModel->loadWhere(array('lehrveranstaltung_id' => $lehrveranstaltung_id));
		
		if (isError($result))
		{
			show_error(getError($result));
		}
		
		if ($anrechnung = getData($result)[0])
		{
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
			$anrechnung_data->dokumentname  = $result->retval[0]->name;
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

	
}