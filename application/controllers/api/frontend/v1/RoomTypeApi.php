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
class RoomTypeApi extends FHCAPI_Controller
{
	
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getAllRoomTypes' => array('basis/ort:r'),
			'createRoomType' =>  array('basis/ort:rw'),
		]);

		$this->load->library('form_validation');

		$this->load->model('ressource/Raumtyp_model', 'RoomTypeModel');

		$this->loadPhrases([
			'global',
			'ui',
			'lehre'
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods
    public function getAllRoomTypes() {
		$this->RoomTypeModel->addOrder('raumtyp_kurzbz', 'ASC');
        $result = $this->RoomTypeModel->load();
        
        return $this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
    }

	public function createRoomType() {
		$this->form_validation->set_rules('kurzbezeichnung', 'kurzbezeichnung', 'required|max_length[255]|is_unique[tbl_raumtyp.raumtyp_kurzbz]', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('lehre', 'kurzbz')]),
			'is_unique' => $this->p->t('ui', 'error_fieldUnique', ['field' =>  $this->p->t('lehre', 'kurzbz')]),
		]);
		$this->form_validation->set_rules('beschreibung', 'beschreibung', 'max_length[255]');

		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$data = [
			'raumtyp_kurzbz' => $this->input->post('kurzbezeichnung'),
			'beschreibung' => $this->input->post('beschreibung'),
		];

		$this->RoomTypeModel->db->set($data);
		$result = $this->RoomTypeModel->db->insert($this->RoomTypeModel->getDbTable());

		if ($result === false) {
			return $this->terminateWithError($this->RoomTypeModel->getLastError());
		}

		return $this->terminateWithSuccess();
	}
}

