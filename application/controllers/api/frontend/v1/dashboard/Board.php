<?php
/**
 * Copyright (C) 2026 fhcomplete.org
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
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about addresses
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Board extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'list'					=> 'dashboard/admin:r',
			'create'				=> 'dashboard/admin:rw',
			'update'				=> 'dashboard/admin:rw',
			'delete'				=> 'dashboard/admin:rw'
		]);

		// Models
		$this->load->model('dashboard/Dashboard_model', 'DashboardModel');
	}

	public function list()
	{
		$this->DashboardModel->addSelect('dashboard_id');
		$this->DashboardModel->addSelect('dashboard_kurzbz');
		$this->DashboardModel->addSelect('tbl_dashboard.beschreibung');
		$this->DashboardModel->addSelect("(
			SELECT json_agg(w.*) 
			FROM dashboard.tbl_widget w 
			JOIN dashboard.tbl_dashboard_widget dw 
			USING(widget_id) 
			WHERE dw.dashboard_id=tbl_dashboard.dashboard_id
		) AS \"widgetSetup\"");

		$result = $this->DashboardModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function create()
	{
		$dashboard_kurzbz = $this->input->post('dashboard_kurzbz');
		
		$result = $this->DashboardModel->insert([
			'dashboard_kurzbz' => $dashboard_kurzbz
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function update()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('dashboard_id', 'Dashboard ID', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$dashboard_id = $this->input->post('dashboard_id');
		$dashboard_kurzbz = $this->input->post('dashboard_kurzbz');
		$beschreibung = $this->input->post('beschreibung');

		$result = $this->DashboardModel->update([
			'dashboard_id' => $dashboard_id
		], [
			'dashboard_kurzbz' => $dashboard_kurzbz,
			'beschreibung' => $beschreibung
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function delete()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('dashboard_id', 'Dashboard ID', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$dashboard_id = $this->input->post('dashboard_id');

		//delete all presets
		$this->load->model('dashboard/Dashboard_Preset_model', 'DashboardPresetModel');

		$result = $this->DashboardPresetModel->delete([
			'dashboard_id' => $dashboard_id
		]);
		$this->getDataOrTerminateWithError($result);

		//delete all widgets
		$this->load->model('dashboard/Dashboard_Widget_model', 'DashboardWidgetModel');

		$result = $this->DashboardWidgetModel->delete([
			'dashboard_id' => $dashboard_id
		]);
		$this->getDataOrTerminateWithError($result);

		$result = $this->DashboardModel->delete($dashboard_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}
}
