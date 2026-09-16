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

class Reservierung extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getMyReservation' => 'basis/cis:r',
			'deleteReservation' => 'lehre/reservierung:rw'
		]);

		$this->load->model('ressource/Reservierung_model', 'ReservierungModel');

	}

	/**
	 * Retrieves all reservations from lehre.vw_reservation of the logged in user
	 */
	public function getMyReservation()
	{
		$result = $this->ReservierungModel->getMyReservation();
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
	}

	/**
	 * deletes a reservation by ID
	 */
	public function deleteReservation()
	{
		$reservierung_id = $this->input->post('reservierung_id');


		if ($reservierung_id === NULL || trim((string)$reservierung_id) === '') {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}
		
		$result = $this->ReservierungModel->deleteReservation($reservierung_id);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}
}

