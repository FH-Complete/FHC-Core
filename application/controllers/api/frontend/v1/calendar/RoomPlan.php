<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

class RoomPlan extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'addRoomReservation' => self::PERM_LOGGED,
			'deleteRoomReservation' => self::PERM_LOGGED,
			'getRoomCreationInfo' => self::PERM_LOGGED,
			'getGruppen' => self::PERM_LOGGED,
			'getLektor' => self::PERM_LOGGED,
			'getReservableMap' => self::PERM_LOGGED,
		]);

		$this->load->library('LogLib');
		$this->loglib->setConfigs(array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'API',
			'dbExecuteUser' => 'RESTful API'
		));

		$this->load->library('form_validation');
		$this->load->library('PermissionLib');
		$this->load->library('StundenplanLib');

		$this->loadPhrases(['ui']);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods



	public function addRoomReservation()
	{
		$this->form_validation->set_rules('selectedStart', "Start", "required");
		$this->form_validation->set_rules('selectedEnd', "End", "required");
		$this->form_validation->set_rules('title', "Title", "required|max_length[10]");
		$this->form_validation->set_rules('beschreibung', "Beschreibung", "required|max_length[32]");
		$this->form_validation->set_rules('ort_kurzbz', "Ort", "required|max_length[16]");
		$this->form_validation->set_rules('studiengang', 'Studiengang', 'numeric');
		$this->form_validation->set_rules('semester', 'Semester', 'integer|greater_than_equal_to[0]');
		$this->form_validation->set_rules('verband', 'Verband', 'trim');
		$this->form_validation->set_rules('gruppe', 'Gruppe', 'trim');
		$this->form_validation->set_rules('spezialgruppe', 'Spezialgruppe', 'max_length[32]');
		$this->form_validation->set_rules('lektoren', 'Lektoren');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$start = $this->input->post('selectedStart');
		$end = $this->input->post('selectedEnd');
		$title = $this->input->post('title');
		$beschreibung = $this->input->post('beschreibung');
		$ort_kurzbz = $this->input->post('ort_kurzbz');

		$studiengang_kz = $this->input->post('studiengang');
		$semester = $this->input->post('semester');
		$verband = $this->input->post('verband');
		$gruppe = $this->input->post('gruppe');
		$spezialgruppe = $this->input->post('spezialgruppe');
		$lektoren = $this->input->post('lektoren');


		$result = $this->stundenplanlib->addReservation($start, $end, $title, $beschreibung, $ort_kurzbz, $lektoren, $studiengang_kz, $semester, $verband, $gruppe, $spezialgruppe);

		if (isError($result))
			$this->terminateWithError($result);

		$this->terminateWithSuccess($result);
	}

	public function deleteRoomReservation()
	{
		$reservierung_id = $this->input->post('reservierung_id');

		$result = $this->stundenplanlib->deleteReservation($reservierung_id);

		if (isError($result))
			$this->terminateWithError($result);

		$this->terminateWithSuccess($result);
	}

	public function getRoomCreationInfo()
	{
		$return_array = array('berechtigt' => false, 'studiengaenge' => []);
		if (!$this->permissionlib->isBerechtigt('lehre/reservierung'))
			$this->terminateWithSuccess($return_array);

		$stg_berechtigungen = $this->permissionlib->getSTG_isEntitledFor('lehre/reservierung');
		if (isEmptyArray($stg_berechtigungen))
			$this->terminateWithSuccess($return_array);

		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->StudiengangModel->addSelect('studiengang_kz, UPPER(CONCAT(typ, kurzbz)) as kuerzel, kurzbzlang');
		$this->StudiengangModel->addOrder('typ, kurzbz');
		$this->StudiengangModel->db->where_in('studiengang_kz', $stg_berechtigungen);
		$studiengaenge = $this->StudiengangModel->loadWhere(array('aktiv' => true));

		if (isError($studiengaenge))
			$this->terminateWithError($studiengaenge);

		$return_array['studiengaenge'] = hasData($studiengaenge) ? getData($studiengaenge) : [];
		$return_array['berechtigt'] = true;

		$this->terminateWithSuccess($return_array);
	}

	public function getGruppen()
	{
		$query = $this->input->get('query');
		if (is_null($query))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$stg_berechtigungen = $this->permissionlib->getSTG_isEntitledFor('lehre/reservierung');

		if (isEmptyArray($stg_berechtigungen))
			$this->terminateWithSuccess([]);

		$this->load->model('organisation/gruppe_model', 'GruppeModel');

		$query_words = explode(' ', urldecode($query));

		$this->GruppeModel->addOrder('gruppe_kurzbz');
		$this->GruppeModel->db->group_start();
		foreach ($query_words as $word)
		{
			$this->GruppeModel->db->group_start();
			$this->GruppeModel->db->where('gruppe_kurzbz ILIKE', "%" . $word . "%");
			$this->GruppeModel->db->or_where('bezeichnung ILIKE', "%" . $word . "%");
			$this->GruppeModel->db->or_where('beschreibung ILIKE', "%" . $word . "%");
			$this->GruppeModel->db->or_where('orgform_kurzbz ILIKE', "%" . $word . "%");

			if (is_numeric($word))
			{
				$this->GruppeModel->db->or_where('studiengang_kz', $word);
			}
			$this->GruppeModel->db->group_end();
		}
		$this->GruppeModel->db->group_end();
		$this->GruppeModel->db->where_in('studiengang_kz', $stg_berechtigungen);
		$gruppen = $this->GruppeModel->loadWhere(array('sichtbar' => true, 'lehre' => true));
		if (isError($gruppen))
			$this->terminateWithError($gruppen);

		$this->terminateWithSuccess(hasData($gruppen) ? getData($gruppen) : []);
	}

	public function getLektor()
	{

		$query = $this->input->get('query');
		if (is_null($query))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$stg_berechtigungen = $this->permissionlib->getSTG_isEntitledFor('lehre/reservierung');

		if (isEmptyArray($stg_berechtigungen))
			$this->terminateWithSuccess([]);

		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		$query_words = explode(' ', urldecode($query));

		$this->MitarbeiterModel->addSelect('uid, person_id, vorname, nachname');
		$this->MitarbeiterModel->addJoin('public.tbl_benutzer', 'uid = mitarbeiter_uid');
		$this->MitarbeiterModel->addJoin('public.tbl_person', 'person_id');
		$this->MitarbeiterModel->db->where('public.tbl_benutzer.aktiv', true);
		$this->MitarbeiterModel->db->group_start();
		foreach ($query_words as $word)
		{
			$this->MitarbeiterModel->db->group_start();
			$this->MitarbeiterModel->db->where('tbl_person.vorname ILIKE', "%" . $word . "%");
			$this->MitarbeiterModel->db->or_where('tbl_person.nachname ILIKE', "%" . $word . "%");
			$this->MitarbeiterModel->db->or_where('uid ILIKE', "%" . $word . "%");
			$this->MitarbeiterModel->db->group_end();
		}
		$this->MitarbeiterModel->db->group_end();

		$this->MitarbeiterModel->addOrder('nachname');
		$this->MitarbeiterModel->addOrder('vorname');
		$mitarbeiter = $this->MitarbeiterModel->load();
		if (isError($mitarbeiter))
			$this->terminateWithError($mitarbeiter);

		$this->terminateWithSuccess(hasData($mitarbeiter) ? getData($mitarbeiter) : []);
	}

	public function getReservableMap($ort_kurzbz = null)
	{
		$this->form_validation->set_rules('start_date', "StartDate", "required");
		$this->form_validation->set_rules('end_date', "EndDate", "required");

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		// storing the post parameter in local variables
		$start_date = $this->input->post('start_date', true);
		$end_date = $this->input->post('end_date', true);

		$result = $this->stundenplanlib->getReservableMap($ort_kurzbz, $start_date, $end_date);

		$this->terminateWithSuccess(array('reservierbarMap' => hasData($result) ? getData($result) : []));
	}

}
