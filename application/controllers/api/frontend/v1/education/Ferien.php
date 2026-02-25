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
	/**
	 * Calls the parent's constructor and prepares libraries and phrases
	 */
	public function __construct()
	{
		parent::__construct([
			'getFerien' => 'basis/ferien:r',
			'getStg' => 'basis/ferien:r',
			'insert' => 'basis/ferien:w',
			'update' => 'basis/ferien:w',
			'delete' => 'basis/ferien:w'
		]);

		// Load models
		$this->load->model('organisation/Ferien_model', 'FerienModel');

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Get Ferien
	 *
	 * @return void
	 */
	public function getFerien()
	{
		$studiengang_kz = $this->input->get('studiengang_kz');

		$this->addMeta('stgkz', $studiengang_kz);

		if (!isset($studiengang_kz)) $this->terminateWithSuccess([]);

		//~ if (isset($studiengang_kz) && !is_numeric($studiengang_kz))
			//~ $this->terminateWithError($this->p->t('ui', 'errorMissingOrInvalidParameters', ['parameter'=> 'Studiengang']), self::ERROR_TYPE_GENERAL);

		$this->FerienModel->addSelect('tbl_ferien.*, , UPPER(typ::varchar(1) || kurzbz) AS studiengang_kuerzel');
		$this->FerienModel->addJoin('public.tbl_studiengang', 'studiengang_kz');

		if (isset($studiengang_kz) && is_numeric($studiengang_kz))
			$this->FerienModel->db->where('studiengang_kz', $studiengang_kz);

		$this->FerienModel->addOrder('vondatum', 'DESC');
		$result = $this->FerienModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * Get list of Studiengaenge
	 *
	 * @return void
	 */
	public function getStg()
	{
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->StudiengangModel->addSelect(' tbl_studiengang.*, UPPER(typ::varchar(1) || kurzbz) AS kuerzel');
		$this->StudiengangModel->addOrder('typ, kurzbz');
		$result = $this->StudiengangModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * Save Ferien
	 *
	 * @return void
	 */
	public function insert()
	{
		$this->_validate();

		$data = $this->_getData();

		// TODO add insertaum and updateamum?
		//~ $data = [
			//~ 'insertamum' => date('c'),
			//~ 'insertvon' => getAuthUID()
		//~ ];
		$id = $this->getDataOrTerminateWithError($this->FerienModel->insert($data));

		$this->terminateWithSuccess(hasData($id) ? getData($id) : null);
	}

	/**
	 * Update Ferien
	 *
	 * @return void
	 */
	public function update()
	{
		$id = $this->input->post('ferien_id');

		if (!is_numeric($id))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Ferien Id']), self::ERROR_TYPE_GENERAL);

		$this->_validate();

		$data = $this->_getData();

		if (isEmptyArray($data)) $this->terminateWithSuccess(null);
		// TODO add insertaum and updateamum?
		//~ $data = [
			//~ 'updateamum' => date('c'),
			//~ 'updatevon' => getAuthUID()
		//~ ];

		$data['ferien_id'] = $id;

		$result = $this->FerienModel->update($id, $data);

		if (isError($result)) $this->terminateWithError(getError($result));

		$this->terminateWithSuccess($id);
	}

	/**
	 * Delete Ferien
	 *
	 * @return void
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
		$this->addMeta('res', $result);

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
	 * @param
	 * @return object success or error
	 */
	private function _validate()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('vondatum', 'Von Datum', 'required|is_valid_date');
		$this->form_validation->set_rules('bisdatum', 'Bis Datum', 'required|is_valid_date');
		$this->form_validation->set_rules('bezeichnung', 'Bezeichnung', 'required|max_length[128]');
		$this->form_validation->set_rules('studiengang_kz', 'Studiengang', 'required|numeric');

		//Events::trigger('konto_insert_validation', $this->form_validation);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());
	}

	/**
	 * Gets Ferien data from post input.
	 * @return array
	 */
	private function _getData()
	{
		$data = [];

		$allowed = [
			'vondatum',
			'bisdatum',
			'bezeichnung',
			'studiengang_kz'
		];


		foreach ($allowed as $field)
		{
			if ($this->input->post($field) !== null) $data[$field] = $this->input->post($field);
		}

		return $data;
	}
}
