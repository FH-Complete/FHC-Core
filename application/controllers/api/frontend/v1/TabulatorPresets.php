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
			'updateTabulatorPreset' => self::PERM_LOGGED,
			'deleteTabulatorPreset' => self::PERM_LOGGED,
		]);

		$this->load->model('tabulator/Tabulator_preset_model', 'TabulatorPresetModel');

		$this->loadPhrases([
			'global',
			'tabulator_presets'
		]);
	}
	
	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getTabulatorPresets()
	{
		$tableName = $this->input->get("tableName");
		if (!$tableName) {
			$this->terminateWithError(error($this->p->t("global", "invalid_params_err_msg")));
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
			$this->terminateWithError($this->p->t("global", "invalid_params_err_msg"), "general", 400);
		}

		$uid = getAuthUID();

		$existingPresetsResult = $this->TabulatorPresetModel->getTabulatorPresetsByUserAndTable($uid, $tableName);
		$existingPresets = $this->getDataOrTerminateWithError($existingPresetsResult) ?? [];

		if (count($existingPresets) > 19) {
			$this->terminateWithError($this->p->t("tabulator_presets", "max_presets_count_err_msg"), "general", 409);
		}

		$existingPresetNames = array_map(function($presetInfo) {
			return $presetInfo->preset_name;
		}, $existingPresets);

		if (in_array($presetName, $existingPresetNames)) {
			$this->terminateWithError($this->p->t("tabulator_presets", "preset_name_duplicate_err_msg"), "general", 409);
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

	public function updateTabulatorPreset()
	{
		$presetId = $this->input->post("presetId");
		$preset = $this->input->post("preset");

		if (!$presetId || !$preset) {
			$this->terminateWithError($this->p->t("global", "invalid_params_err_msg"), "general", 400);
		} 

		$existingPresetData = $this->TabulatorPresetModel->getTabulatorPreset($presetId);
		$existingPresetArray = $this->getDataOrTerminateWithError($existingPresetData) ?? [null];
		$existingPreset = $existingPresetArray[0];
		if (!$existingPreset) {
			$this->terminateWithError($this->p->t("tabulator_presets", "preset_not_found_err_msg"), "general", 404);
		}

		$uid = getAuthUID();
		if ($existingPreset->benutzer_uid !== $uid) {
			$this->terminateWithError($this->p->t("tabulator_presets", "preset_not_own_update_err_msg"), "general", 403);
		}

		$presetUpdateResult = $this->TabulatorPresetModel->updateTabulatorPreset($presetId, $preset);
		if (isError($presetUpdateResult)) {
			$this->terminateWithError($presetUpdateResult->retval);
		}

		$updatedPresetData = $this->TabulatorPresetModel->getTabulatorPreset($presetId);
		$updatedPresetArray = $this->getDataOrTerminateWithError($updatedPresetData) ?? [null];
		$updatedPreset = $updatedPresetArray[0];

		$this->terminateWithSuccess($updatedPreset);
	}

	public function deleteTabulatorPreset()
	{
		$presetId = $this->input->post("presetId");
		if (!$presetId) {
			$this->terminateWithError($this->p->t("global", "invalid_params_err_msg"), "general", 400);
		}

		$presetData = $this->TabulatorPresetModel->getTabulatorPreset($presetId);
		$presetArray = $this->getDataOrTerminateWithError($presetData) ?? [null];
		$preset = $presetArray[0];
		if (!$preset) {
			$this->terminateWithError($this->p->t("tabulator_presets", "preset_not_found_err_msg"), "general", 404);
		}

		$uid = getAuthUID();
		if ($preset->benutzer_uid !== $uid) {
			$this->terminateWithError($this->p->t("tabulator_presets", "preset_not_own_delete_err_msg"), "general", 403);
		}

		$presetDeletionResult = $this->TabulatorPresetModel->deleteTabulatorPreset($presetId);
		if (isError($presetDeletionResult)) {
			$this->terminateWithError($presetDeletionResult->retval);
		}

		$this->terminateWithSuccess();
	}

}