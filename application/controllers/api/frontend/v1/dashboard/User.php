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
 * Provides data to the ajax get calls about the users dashboard
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class User extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'get'							=> 'dashboard/benutzer:r',
			'addWidget'						=> 'dashboard/benutzer:rw',
			'removeWidget'					=> 'dashboard/benutzer:rw'
		]);

		// Libraries
		$this->load->library('dashboard/DashboardLib');

		// Models
		$this->load->model('ressource/Funktion_model', 'FunktionModel');
	}

	public function get($dashboard_kurzbz)
	{
		$dashboard = $this->dashboardlib->getDashboardByKurzbz($dashboard_kurzbz);
		if (!$dashboard)
			show_404();
		
		$uid = $this->authlib->getAuthObj()->username;

		/*$mergedconfig = $this->dashboardlib->getMergedConfig($dashboard->dashboard_id, $uid);

		$this->terminateWithSuccess([
			'general' => call_user_func_array(
				'array_merge_recursive',
				$mergedconfig
			)
		]);*/
		$defaultconfig = $this->dashboardlib->getDefaultConfig($dashboard->dashboard_id, $uid);
		$userconfig = $this->dashboardlib->getUserConfig($dashboard->dashboard_id, $uid);

		$defaultconfig_squashed = $defaultconfig ? call_user_func_array('array_merge_recursive', $defaultconfig) : [];
		$userconfig_squashed = $userconfig ? call_user_func_array('array_merge_recursive', $userconfig) : [];

		$mergedconfig = array_replace_recursive($defaultconfig_squashed, $userconfig_squashed);

		$this->terminateWithSuccess([
			DashboardLib::SECTION_IF_FUNKTION_KURZBZ_IS_NULL => $mergedconfig
		]);
	}

	public function addWidget()
	{
		$this->terminateWithValidationErrors(['test' => 'Test']);
		$this->load->library('form_validation');

		$this->form_validation->set_rules('dashboard', 'Dashboard', 'required');
		$this->form_validation->set_rules('widget[widget]', 'Widget', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$widget = $this->input->post('widget');
		$dashboard_kurzbz = $this->input->post('dashboard');
		$uid = $this->authlib->getAuthObj()->username;
		
		if (!isset($widget['widgetid']))
			$widget['widgetid'] = $this->dashboardlib->generateWidgetId($dashboard_kurzbz);

		$override = $this->dashboardlib->getOverrideOrCreateEmptyOverride($dashboard_kurzbz, $uid);
		
		$override_decoded = json_decode($override->override, true);

		if (!isset($override_decoded['general']) || !is_array($override_decoded['general']))
			$override_decoded['general'] = [];

		 if (!isset($override_decoded['general']['widgets']))
				$override_decoded['general']['widgets'] = [];

		$override_decoded['general']['widgets'][$widget['widgetid']] = $widget;
		
		$override->override = json_encode($override_decoded);
				
		$result = $this->dashboardlib->insertOrUpdateOverride($override);

		$this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($widget['widgetid']);
	}

	public function removeWidget()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('dashboard', 'Dashboard', 'required');
		$this->form_validation->set_rules('widget', 'Widget', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$widget_id = $this->input->post('widget');
		$dashboard_kurzbz = $this->input->post('dashboard');
		$uid = $this->authlib->getAuthObj()->username;
		
		$override = $this->dashboardlib->getOverride($dashboard_kurzbz, $uid);
		if (!$override)
			show_404();
		
		$override_decoded = json_decode($override->override, true);

		foreach (array_keys($override_decoded) as $k) {
			if (!isset($override_decoded[$k]["widgets"])) {
				unset($override_decoded[$k]);
				continue;
			}
			if (isset($override_decoded[$k]["widgets"][$widget_id])) {
				unset($override_decoded[$k]["widgets"][$widget_id]);
			}
			if (!$override_decoded[$k]["widgets"]) {
				unset($override_decoded[$k]);
			}
		}

		$override->override = json_encode($override_decoded);
				
		$result = $this->dashboardlib->insertOrUpdateOverride($override);

		$this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess();
	}
}
