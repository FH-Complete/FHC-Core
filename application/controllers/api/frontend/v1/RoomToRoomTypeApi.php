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

/**
 * This controller operates between (interface) the JS (GUI) and the SearchBarLib (back-end)
 * Provides data to the ajax get calls about the searchbar component
 * This controller works with JSON calls on the HTTP GET and the output is always JSON
 */
class RoomToRoomTypeApi extends FHCAPI_Controller
{
	
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		// NOTE(chris): additional permission checks will be done in SearchBarLib
		parent::__construct([
			'getRoomToRoomTypeRelationsByRoomShortCode' => self::PERM_LOGGED,
			'createRoomToRoomTypeRelation' => self::PERM_LOGGED,
			'deleteRoomToRoomTypeRelation' => self::PERM_LOGGED,
		]);

		$this->load->library('form_validation');

		$this->load->model('ressource/Ortraumtyp_model', 'OrtRoomTypeModel');

		$this->loadPhrases([
			'global',
			'ui',
			'lehre'
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods
    public function getRoomToRoomTypeRelationsByRoomShortCode($roomShortCode) {
		$this->OrtRoomTypeModel->db->select('public.tbl_ortraumtyp.*, public.tbl_raumtyp.beschreibung as raumtyp_beschreibung');
		$this->OrtRoomTypeModel->db->join('public.tbl_raumtyp', 'public.tbl_raumtyp.raumtyp_kurzbz = public.tbl_ortraumtyp.raumtyp_kurzbz', 'left');
		$this->OrtRoomTypeModel->db->order_by('hierarchie', 'ASC');
        $result = $this->OrtRoomTypeModel->loadWhere(['ort_kurzbz' => $roomShortCode]);
        
        return $this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
    }

	public function createRoomToRoomTypeRelation() {
		$this->form_validation->set_rules('roomShortCode', 'roomShortCode', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('lehre', 'kurzbz')])
		]);
		$this->form_validation->set_rules('roomTypeShortCode', 'roomTypeShortCode', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('lehre', 'kurzbz')])
		]);
		$this->form_validation->set_rules('hierarchy', 'hierarchy', 'required|integer', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('ui', 'hierarchy')]),
			'integer' => $this->p->t('ui', 'error_fieldInteger', ['field' =>  $this->p->t('ui', 'hierarchy')])
		]);

		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$existingRelationResponse = $this->OrtRoomTypeModel->loadWhere([
			'ort_kurzbz' => $this->input->post('roomShortCode'),
			'hierarchie' => $this->input->post('hierarchy'),
		]);
		if (hasData($existingRelationResponse)) { 
			$this->terminateWithError($this->p->t('ui', 'error_roomToRoomTypeRelationAlreadyExists'), self::ERROR_TYPE_GENERAL);
		}
		
		$data = [
			'ort_kurzbz' => $this->input->post('roomShortCode'),
			'raumtyp_kurzbz' => $this->input->post('roomTypeShortCode'),
			'hierarchie' => $this->input->post('hierarchy'),
		];

		$this->OrtRoomTypeModel->db->set($data);
		$result = $this->OrtRoomTypeModel->db->insert($this->OrtRoomTypeModel->getDbTable());

		if ($result === false) {
			return $this->terminateWithError($this->OrtRoomTypeModel->getLastError());
		}

		return $this->terminateWithSuccess(['message' => 'Room to Room Type relation created successfully.']);
	}

	public function deleteRoomToRoomTypeRelation() {
		$this->form_validation->set_rules('roomShortCode', 'roomShortCode', 'required');
		$this->form_validation->set_rules('roomTypeShortCode', 'roomTypeShortCode', 'required');

		if ($this->form_validation->run() === false) {
			return $this->terminateWithError(validation_errors());
		}

		$result = $this->OrtRoomTypeModel->db->delete($this->OrtRoomTypeModel->getDbTable(), [
			'ort_kurzbz' => $this->input->post('roomShortCode'),
			'raumtyp_kurzbz' => $this->input->post('roomTypeShortCode'),
		]);

		if ($result === false) {
			return $this->terminateWithError($this->OrtRoomTypeModel->getLastError());
		}

		return $this->terminateWithSuccess(['message' => 'Room to Room Type relation deleted successfully.']);
	}
}

