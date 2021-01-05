<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class AnrechnungLib
{
	public function __construct()
	{
		$this->ci =& get_instance();
		
		$this->ci->load->model('person/Person_model', 'PersonModel');
		$this->ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
	}
	
	/**
	 * Get Anrechnung data
	 * @param $uid
	 * @param $studiensemester_kurzbz
	 * @param $lv_id
	 * @return StdClass
	 */
	public function getAnrechnungData($uid, $studiensemester_kurzbz, $lv_id)
	{
		$anrechnung_data = new StdClass();

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
		$anrechnung_data->lektoren = array();
		if (!$lv_lektoren = getData($this->ci->LehrveranstaltungModel->getLecturersByLv($studiensemester_kurzbz, $lv_id)))
		{
			show_error('Failed loading course lectors.');
		}
		
		// Set the given studiensemester
		$anrechnung_data->lv_bezeichnung = $lv->bezeichnung;
		$anrechnung_data->ects = $lv->ects;
		$anrechnung_data->studiensemester_kurzbz = $studiensemester_kurzbz;
		$anrechnung_data->vorname = $person->vorname;
		$anrechnung_data->nachname = $person->nachname;
		$anrechnung_data->bpk = $person->bpk;
		$anrechnung_data->stg_bezeichnung = $studiengang->bezeichnung;
		$anrechnung_data->lektoren = $lv_lektoren;
		
		return $anrechnung_data;
	}

}