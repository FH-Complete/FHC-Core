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
 * This controller operates between (interface) the JS (GUI) and the PhrasesLib (back-end)
 * Provides data to the ajax get calls about the Phrasen plugin
 * This controller works with JSON calls on the HTTP GET and the output is always JSON
 */
class TabulatorPresets extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getTabulatorPresets' => self::PERM_LOGGED,
			'createTabulatorPreset' => self::PERM_LOGGED,
			'deleteTabulatorPreset' => self::PERM_LOGGED,
		]);

		$this->load->model('tabulator/Tabulator_preset_model', 'TabulatorPresetModel');
	}
	
	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getTabulatorPresets()
	{
		$tableName = $this->input->get("tableName");
		if (!$tableName) {
			$this->terminateWithError(error("Invalid parameters!"));
		}

		$uid = getAuthUID();
		$presets = $this->TabulatorPresetModel->getTabulatorPresetsByUserAndTable($uid, $tableName);
		$presetsData = $this->getDataOrTerminateWithError($presets) ?? [];

		$this->terminateWithSuccess($presetsData);
	}

	public function createTabulatorPreset()
	{
		$tableName = $this->input->post("tableName");
		$presetName = $this->input->post("presetName");
		$preset = $this->input->post("preset");

		if (!$tableName || !$presetName || !$preset) {
			$this->terminateWithError("Invalid parameters!", "general", 400);
		}

		$uid = getAuthUID();

		$existingPresetsResult = $this->TabulatorPresetModel->getTabulatorPresetsByUserAndTable($uid, $tableName);
		$existingPresets = $this->getDataOrTerminateWithError($existingPresetsResult) ?? [];

		if (count($existingPresets) > 19) {
			$this->terminateWithError("Maximum number of presets reached!", "general", 409);
		}

		$existingPresetNames = array_map(function($presetInfo) {
			return $presetInfo->preset_name;
		}, $existingPresets);

		if (in_array($presetName, $existingPresetNames)) {
			$this->terminateWithError("Preset name duplicate not allowed!", "general", 409);
		}

		$presetCreationResult = $this->TabulatorPresetModel->createTabulatorPreset($uid, $tableName, $presetName, $preset);

		if (isError($presetCreationResult)) {
			$this->terminateWithError($presetCreationResult->retval);
		}

		$newPresetData = $this->TabulatorPresetModel->getTabulatorPreset($presetCreationResult->retval);
		$newPresetArray = $this->getDataOrTerminateWithError($newPresetData) ?? [null];
		$newPreset = $newPresetArray[0];

		$this->terminateWithSuccess($newPreset);
	}

	public function deleteTabulatorPreset()
	{
		$presetId = $this->input->post("presetId");
		if (!$presetId) {
			$this->terminateWithError("Invalid parameters!", "general", 400);
		}

		$presetData = $this->TabulatorPresetModel->getTabulatorPreset($presetId);
		$presetArray = $this->getDataOrTerminateWithError($presetData) ?? [null];
		$preset = $presetArray[0];
		if (!$preset) {
			$this->terminateWithError("Preset not found!", "general", 404);
		}

		$uid = getAuthUID();
		$this->addMeta("preset", $preset);
		if ($preset->benutzer_uid !== $uid) {
			$this->terminateWithError("You are not allowed to delete this preset!", "general", 403);
		}

		$presetDeletionResult = $this->TabulatorPresetModel->deleteTabulatorPreset($presetId);
		if (isError($presetDeletionResult)) {
			$this->terminateWithError($presetDeletionResult->retval);
		}

		$this->terminateWithSuccess();
	}

}