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
class Preset extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'list'					=> 'dashboard/admin:r',
			'getBatch'				=> 'dashboard/admin:r',
			'addWidgets'			=> 'dashboard/admin:rw',
			'removeWidget'			=> 'dashboard/admin:rw'
		]);

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);

		// Libraries
		$this->load->library('dashboard/DashboardLib');

		// Models
		$this->load->model('ressource/Funktion_model', 'FunktionModel');
	}

	public function list($dashboard_kurzbz)
	{
		$sql = "
			WITH
			dashboard_presets AS (
				SELECT
					*
				FROM
					dashboard.tbl_dashboard_preset dp
				JOIN
					dashboard.tbl_dashboard d ON d.dashboard_id = dp.dashboard_id
				WHERE
					d.dashboard_kurzbz = {$this->db->escape($dashboard_kurzbz)}
			),
			general AS 	(
				SELECT
					'general' AS funktion_kurzbz,
					'Allgemein' AS beschreibung
			)

			(
				SELECT
					f.funktion_kurzbz,
					f.beschreibung,
					COUNT(p.preset_id) AS has_preset
				FROM
					general f
				LEFT JOIN
					dashboard_presets p ON p.funktion_kurzbz IS NULL
				GROUP BY
					f.funktion_kurzbz, f.beschreibung
			)
			UNION ALL
			(
				SELECT
					f.funktion_kurzbz,
					f.beschreibung,
					COUNT(p.preset_id) AS has_preset
				FROM
					public.tbl_funktion f
				LEFT JOIN
					dashboard_presets p ON p.funktion_kurzbz = f.funktion_kurzbz
				GROUP BY
					f.funktion_kurzbz, f.beschreibung
				ORDER BY
					f.beschreibung ASC
			)
		";

		$result = $this->FunktionModel->execReadOnlyQuery($sql);

		$funktionen = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($funktionen);
	}

	public function getBatch()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('db', 'Dashboard', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$db = $this->input->post('db');
		$funktionen = $this->input->post('funktionen') ?: [];

		$result = [];

		foreach ($funktionen as $funktion) {
			$conf = $this->dashboardlib->getPreset($db, $funktion);
			if ($conf) {
				$preset = json_decode($conf->preset, true);
				if (!isset($preset[$funktion]) || !isset($preset[$funktion]['widgets']))
					$result[$funktion] = [];
				else
					$result[$funktion] = $preset[$funktion]['widgets'];
			} else {
				$result[$funktion] = [];
			}
		}

		return $this->terminateWithSuccess($result);
	}

	public function addWidgets()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('db', 'Dashboard', 'required');
		$this->form_validation->set_rules('funktion_kurzbz', 'Funktion', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$dashboard_kurzbz = $this->input->post('db');
		$funktion_kurzbz = $this->input->post('funktion_kurzbz');
		$widgets = $this->input->post('widgets') ?: [];

		$preset = $this->dashboardlib->getPresetOrCreateEmptyPreset($dashboard_kurzbz, $funktion_kurzbz);

		$preset_decoded = json_decode($preset->preset, true);
		
		$this->dashboardlib->addWidgetsToWidgets($preset_decoded, $dashboard_kurzbz, $funktion_kurzbz, $widgets);

		$preset->preset = json_encode($preset_decoded);

		$result = $this->dashboardlib->insertOrUpdatePreset($preset);

		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($preset_decoded);
	}

	public function removeWidget()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('db', 'Dashboard', 'required');
		$this->form_validation->set_rules('funktion_kurzbz', 'Funktion', 'required');
		$this->form_validation->set_rules('widgetid', 'Widget', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$dashboard_kurzbz = $this->input->post('db');
		$funktion_kurzbz = $this->input->post('funktion_kurzbz');
		$widgetid = $this->input->post('widgetid');

		$preset = $this->dashboardlib->getPreset($dashboard_kurzbz, $funktion_kurzbz);
		if (!$preset)
			show_404();

		$preset_decoded = json_decode($preset->preset, true);

		if (!$this->dashboardlib->removeWidgetFromWidgets($preset_decoded, $funktion_kurzbz, $widgetid))
			show_404();

		$preset->preset = json_encode($preset_decoded);

		$result = $this->dashboardlib->insertOrUpdatePreset($preset);

		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(array('msg' => $this->p->t('dashboard', 'success_savePreset')));
	}
}
