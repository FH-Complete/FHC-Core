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
 * This controller operates between (interface) the JS (GUI) and the FilterCmptLib (back-end)
 * Provides data to the ajax get calls about the filter component
 * Listens to ajax post calls to change the filter data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Filter extends FHCAPI_Controller
{
	const FILTER_UNIQUE_ID = 'filterUniqueId'; // Name of the filter cmpt unique id (mandatory)
	const FILTER_TYPE = 'filterType'; // The filter type (PHP filter definition) used (mandatory)
	const FILTER_ID = 'filterId'; // The id of the used filter (optional)

	/**
	 * Calls the parent's constructor and loads the FilterCmptLib
	 */
	public function __construct()
	{
		// NOTE: FilterCmpt has its own permissions checks
		parent::__construct([
			'getFilter' => self::PERM_LOGGED,
			'removeFilterField' => self::PERM_LOGGED,
			'addFilterField' => self::PERM_LOGGED,
			'applyFilterFields' => self::PERM_LOGGED,
			'removeCustomFilter' => self::PERM_LOGGED,
			'saveCustomFilter' => self::PERM_LOGGED,
			'reloadDataset' => self::PERM_LOGGED
		]);

		// Loads the FiltersModel
		$this->load->model('system/Filters_model', 'FiltersModel');

		// Loads the FilterCmptLib with HTTP GET/POST parameters
		$this->_startFilterCmptLib();
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Retrieves data about the current filter from the session and will be written on the output in JSON format
	 */
	public function getFilter()
	{
		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$session = $this->filtercmptlib->getSession();
		if (is_object($session)) {
			// If stdClass it is an retval object
			$session = $this->getDataOrTerminateWithError($session);
		}
		$this->terminateWithSuccess($session);
	}

	/**
	 * Remove an applied filter (SQL where condition) from the current filter
	 */
	public function removeFilterField()
	{
		$this->form_validation->set_rules('filterField', 'filterField', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->filtercmptlib->removeFilterField($this->input->post('filterField'));

		if (!$result)
			$this->terminateWithError('Error occurred', self::ERROR_TYPE_GENERAL);

		$this->terminateWithSuccess('Field removed');
	}

	/**
	 * Add a filter (SQL where clause) to be applied to the current filter
	 */
	public function addFilterField()
	{
		$this->form_validation->set_rules('filterField', 'filterField', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->filtercmptlib->addFilterField($this->input->post('filterField'));

		if (!$result)
			$this->terminateWithError('Error occurred', self::ERROR_TYPE_GENERAL);

		$this->terminateWithSuccess('Field added');
	}

	/**
	 * Apply the filter changes
	 */
	public function applyFilterFields()
	{
		$this->form_validation->set_rules('filterFields', 'filterFields', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->filtercmptlib->applyFilterFields($this->input->post('filterFields'));

		if (!$result)
			$this->terminateWithError('Error occurred', self::ERROR_TYPE_GENERAL);

		$this->terminateWithSuccess('Applied');
	}

	/**
	 * Save the current filter as a custom filter for this user with the given description
	 */
	public function saveCustomFilter()
	{
		$this->form_validation->set_rules('customFilterName', 'customFilterName', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->filtercmptlib->saveCustomFilter($this->input->post('customFilterName'));

		if (!$result)
			$this->terminateWithError('Error occurred', self::ERROR_TYPE_GENERAL);

		$this->terminateWithSuccess('Saved');
	}

	/**
	 * Remove a custom filter by its filterId
	 */
	public function removeCustomFilter()
	{
		$this->form_validation->set_rules('filterId', 'filterId', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->filtercmptlib->removeCustomFilter($this->input->post('filterId'));

		if (!$result)
			$this->terminateWithError('Error occurred', self::ERROR_TYPE_GENERAL);

		$this->terminateWithSuccess('Removed');
	}

	/**
	 * Reloads the dataset
	 */
	public function reloadDataset()
	{
		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->filtercmptlib->reloadDataset();

		$this->terminateWithSuccess('Success');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Loads the FilterCmptLib with the FILTER_UNIQUE_ID parameter
	 * If the parameter FILTER_UNIQUE_ID is not given then the execution of the controller is terminated and
	 * an error message is printed
	 */
	private function _startFilterCmptLib()
	{
		$filterUniqueId = null;
		$filterType = null;
		$filterId = null;

		$validations = [
			[
				'field' => self::FILTER_UNIQUE_ID,
				'label' => self::FILTER_UNIQUE_ID,
				'rules' => 'required'
			],
			[
				'field' => self::FILTER_TYPE,
				'label' => self::FILTER_TYPE,
				'rules' => 'required'
			],
		];

		$this->load->library('form_validation');

		if ($this->input->method() == 'get')
			$this->form_validation->set_data($this->input->get());
		$this->form_validation->set_rules($validations);

		if ($this->form_validation->run()) {
			$filterUniqueId = $this->input->post_get(self::FILTER_UNIQUE_ID);
			$filterType = $this->input->post_get(self::FILTER_TYPE);
			$filterId = $this->input->post_get(self::FILTER_ID);

			// Loads the FilterCmptLib that contains all the used logic
			$this->load->library(
				'FilterCmptLib',
				array(
					'filterUniqueId' => $filterUniqueId,
					'filterType' => $filterType,
					'filterId' => $filterId
				)
			);

			// Start the component
			$this->filtercmptlib->start();
		}
	}
}

