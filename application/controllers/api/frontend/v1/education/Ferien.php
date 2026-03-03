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

if (!defined('BASEPATH')) exit('No direct script access allowed');

//use CI3_Events as Events;

/**
 * This controller operates between (interface) the JS (GUI) and the back-end
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Ferien extends FHCAPI_Controller
{
	const DEFAULT_STUDIENGANG_KZ = 0;

	/**
	 * Calls the parent's constructor and prepares libraries and phrases
	 */
	public function __construct()
	{
		parent::__construct([
			'getFerien' => 'basis/ferien:r',
			'getDefaultVonBis' => 'basis/ferien:r',
			'getOe' => 'basis/ferien:r',
			'getStudienplaene' => 'basis/ferien:r',
			'getFerientypen' => 'basis/ferien:r',
			'getStg' => 'basis/ferien:r',
			'insert' => 'basis/ferien:w',
			'update' => 'basis/ferien:w',
			'delete' => 'basis/ferien:w'
		]);

		// Load models
		$this->load->model('organisation/Ferien_model', 'FerienModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Get Ferien
	 */
	public function getFerien()
	{
		// TODO check input

		$filterVonDatum = $this->input->get('filterVonDatum');
		$filterBisDatum = $this->input->get('filterBisDatum');

		$this->FerienModel->addSelect(
			'tbl_ferien.ferien_id, tbl_ferien.bezeichnung, tbl_ferien.vondatum, tbl_ferien.bisdatum,
			plan.studienplan_id, stg.studiengang_kz, oe.oe_kurzbz, fetyp.ferientyp_kurzbz,
			oe.bezeichnung AS oe_bezeichnung, UPPER(stg.typ::varchar(1) || stg.kurzbz) AS studiengang_kuerzel,
			plan.studienplan_id, plan.bezeichnung AS studienplan_bezeichnung, fetyp.mitarbeiter AS mitarbeiterrelevant, fetyp.studierende AS studierendenrelevant,
			fetyp.lehre'
		);
		$this->FerienModel->addJoin('public.tbl_studiengang stg', 'studiengang_kz', 'LEFT');
		$this->FerienModel->addJoin('lehre.tbl_studienplan plan', 'studienplan_id', 'LEFT');
		$this->FerienModel->addJoin('public.tbl_organisationseinheit oe', 'tbl_ferien.oe_kurzbz = oe.oe_kurzbz', 'LEFT');
		$this->FerienModel->addJoin('lehre.tbl_ferientyp fetyp', 'ferientyp_kurzbz', 'LEFT');

		if (isset($filterVonDatum))
			$this->FerienModel->db->where('tbl_ferien.bisdatum >=', $filterVonDatum);

		if (isset($filterBisDatum))
			$this->FerienModel->db->where('tbl_ferien.vondatum <=', $filterBisDatum);

		$this->FerienModel->addOrder('vondatum', 'DESC');
		$result = $this->FerienModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * Gets default dates (from - to) for filtering Ferien.
	 */
	public function getDefaultVonBis()
	{
		$defaultVonBis = ['defaultVon' => null, 'defaultBis' => null];

		// get current Studienjahr
		$this->load->model('organisation/Studienjahr_model', 'StudienjahrModel');

		$result = $this->StudienjahrModel->getAktOrNextStudienjahr(62);

		if (isError($result)) $this->terminateWithError(getError($result));

		if (hasData($result))
		{
			$studienjahr = getData($result)[0];
			$defaultVonBis['defaultVon'] = $studienjahr->beginn;
			$defaultVonBis['defaultBis'] = $studienjahr->ende;
		}

		$this->terminateWithSuccess($defaultVonBis);
	}

	/**
	 * Get list of Organisationseinheiten
	 */
	public function getOe()
	{
		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');

		//$this->StudiengangModel->addSelect(' tbl_studiengang.*, UPPER(typ::varchar(1) || kurzbz) AS kuerzel');
		$this->OrganisationseinheitModel->addOrder('organisationseinheittyp_kurzbz, oe_kurzbz');
		$result = $this->OrganisationseinheitModel->loadWhere(['aktiv' => true]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * Get list of Studienplaene.
	 * Studienplaene are returned by Organisationseinheit, von and bis datum.
	 */
	public function getStudienplaene()
	{
		// check input
		//~ $this->load->library('form_validation');

		//~ $this->form_validation->set_rules('oe_kurzbz', 'Organisationseinheit', 'max_length[32]');
		//~ $this->form_validation->set_rules('vondatum', 'Von Datum', 'is_valid_date');
		//~ $this->form_validation->set_rules('bisdatum', 'Bis Datum', 'is_valid_date');

		//~ if (!$this->form_validation->run())
			//~ $this->terminateWithValidationErrors($this->form_validation->error_array());

		$oe_kurzbz = $this->input->get('oe_kurzbz');
		$vondatum = $this->input->get('vondatum');
		$bisdatum = $this->input->get('bisdatum');

		// get Studiengang from Oe
		$result = $this->StudiengangModel->loadWhere(['oe_kurzbz' => $oe_kurzbz]);


		if (isError($result)) $this->terminateWithError(getError($result));
		if (!hasData($result)) $this->terminateWithSuccess([]);
		$studiengangKzArr = array_column(getData($result), 'studiengang_kz');

		// load models
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		// get all Studiensemester in requested date range
		$result = $this->StudiensemesterModel->getByDateRange($vondatum, $bisdatum);
		if (isError($result)) $this->terminateWithError(getError($result));
		if (!hasData($result)) $this->terminateWithSuccess([]);
		$studiensemesterArr = array_column(getData($result), 'studiensemester_kurzbz');

		$studienplaene = [];
		foreach ($studiengangKzArr as $studiengang_kz)
		{
			foreach ($studiensemesterArr as $studiensemester_kurzbz)
			{
				// get studienplaene for each Studiengang and Studiensemester
				$this->StudienplanModel->addDistinct("studienplan_id");
				$this->StudienplanModel->addSelect("lehre.tbl_studienplan.*");
				$this->StudienplanModel->addJoin("lehre.tbl_studienordnung", "studienordnung_id");
				$this->StudienplanModel->addJoin("lehre.tbl_studienplan_semester", "studienplan_id");

				$whereArray = array(
					"tbl_studienplan.aktiv" => "TRUE",
					"tbl_studienordnung.studiengang_kz" => $studiengang_kz,
					"tbl_studienplan_semester.studiensemester_kurzbz" => $studiensemester_kurzbz
				);

				$result = $this->StudienplanModel->loadWhere($whereArray);

				//$result = $this->StudienplanModel->getStudienplaeneBySemester($studiengang_kz, $studiensemester_kurzbz);
				if (isError($result)) $this->terminateWithError(getError($result));
				if (!hasData($result)) continue;

				foreach (getData($result) as $studienplan)
				{
					$studienplaene[$studienplan->studienplan_id] = $studienplan;
				}
			}
		}

		$this->terminateWithSuccess($studienplaene);
	}

	/**
	 * Get list of Ferientypen
	 */
	public function getFerientypen()
	{
		$this->load->model('organisation/Ferientyp_model', 'FerientypModel');

		$this->FerientypModel->addOrder('ferientyp_kurzbz');
		$result = $this->FerientypModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * Get list of Studiengaenge
	 */
	public function getStg()
	{
		$this->StudiengangModel->addSelect(' tbl_studiengang.*, UPPER(typ::varchar(1) || kurzbz) AS kuerzel');
		$this->StudiengangModel->addOrder('typ, kurzbz');
		$result = $this->StudiengangModel->loadWhere(['aktiv' => true]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}


	/**
	 * Add Ferien
	 */
	public function insert()
	{
		$this->_validate();

		$data = $this->_getData();

		// get studiengang_kz from oe, otherwise default kz
		$this->StudiengangModel->addSelect('studiengang_kz');
		$this->StudiengangModel->addLimit(1);
		$result = $this->StudiengangModel->loadWhere(['oe_kurzbz' => $data['oe_kurzbz']]);

		$data['studiengang_kz'] = hasData($result) ? getData($result)[0]->studiengang_kz : self::DEFAULT_STUDIENGANG_KZ;

		$data = array_merge($data, ['insertamum' => date('c'), 'insertvon' => getAuthUID()]);

		$id = $this->getDataOrTerminateWithError($this->FerienModel->insert($data));

		$this->terminateWithSuccess(hasData($id) ? getData($id) : null);
	}

	/**
	 * Update Ferien
	 */
	public function update()
	{
		$id = $this->input->post('ferien_id');

		if (!is_numeric($id))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Ferien Id']), self::ERROR_TYPE_GENERAL);

		$this->_validate();

		$data = $this->_getData();

		if (isEmptyArray($data)) $this->terminateWithSuccess(null);

		$data = array_merge($data, ['ferien_id' => $id, 'updateamum' => date('c'), 'updatevon' => getAuthUID()]);

		$result = $this->FerienModel->update($id, $data);

		if (isError($result)) $this->terminateWithError(getError($result));

		$this->terminateWithSuccess($id);
	}

	/**
	 * Delete Ferien
	 */
	public function delete()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('ferien_id', 'Ferien Id', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$ferien_id = $this->input->post('ferien_id');

		$this->FerienModel->addSelect('ferien_id');
		$result = $this->FerienModel->load($ferien_id);

		if (!hasData($result))
			$this->terminateWithError($this->p->t('ferien', 'error_missing', [
				'ferien_id' => $ferien_id
			]));

		//~ $_POST['studiengang_kz'] = current($result)->studiengang_kz;

		//~ $this->form_validation->set_rules('studiengang_kz', 'Studiengang', 'has_permissions_for_stg[admin:rw,assistenz:rw]');

		//~ Events::trigger('konto_delete_validation', $this->form_validation);

		//~ if (!$this->form_validation->run())
			//~ $this->terminateWithValidationErrors($this->form_validation->error_array());


		//Events::trigger('konto_delete', $ferien_id);

		$result = $this->getDataOrTerminateWithError($this->FerienModel->delete($ferien_id));

		$this->terminateWithSuccess();
	}

	/**
	 * Validate ferien post input.
	 */
	private function _validate()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('vondatum', 'Von Datum', 'required|is_valid_date');
		$this->form_validation->set_rules('bisdatum', 'Bis Datum', 'required|is_valid_date');
		$this->form_validation->set_rules('bezeichnung', 'Bezeichnung', 'required|max_length[128]');
		$this->form_validation->set_rules('oe_kurzbz', 'Organisationseinheit', 'required|max_length[32]');
		$this->form_validation->set_rules('studienplan_id', 'Studienplan', 'numeric');
		$this->form_validation->set_rules('ferientyp_kurzbz', 'Ferientyp', 'max_length[64]');

		//Events::trigger('konto_insert_validation', $this->form_validation);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());
	}

	/**
	 * Gets Ferien data from post input.
	 */
	private function _getData()
	{
		$data = [];

		$allowed = [
			'vondatum',
			'bisdatum',
			'bezeichnung',
			'oe_kurzbz',
			'studienplan_id',
			'ferientyp_kurzbz'
		];

		foreach ($allowed as $field)
		{
			$data[$field] = $this->input->post($field);
		}

		return $data;
	}
}
