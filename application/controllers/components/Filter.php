<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the FilterCmptLib (back-end)
 * Provides data to the ajax get calls about the filter cmpt
 * Accepts ajax post calls to change the filter data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 * NOTE: extends the FHC_Controller instead of the Auth_Controller because the FilterCmpt has its
 * 		own permissions check
 */
class Filter extends FHC_Controller
{
	const FILTER_UNIQUE_ID = 'filterUniqueId'; // Name of the filter cmpt unique id
	const FILTER_TYPE = 'filterType'; // 

	/**
	 * Calls the parent's constructor and loads the FilterCmptLib
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads authentication library and starts authentication
		$this->load->library('AuthLib');

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
		$this->outputJsonSuccess($this->filtercmptlib->getSession());
	}

	/**
	 * Change the sort of the selected fields of the current filter and
	 * its data will be written on the output in JSON format
	 */
	public function sortSelectedFields()
	{
		$selectedFields = $this->input->post('selectedFields');

		if ($this->filtercmptlib->sortSelectedFields($selectedFields) == true)
		{
			$this->getFilter();
		}
		else
		{
			$this->outputJsonError('Wrong parameter');
		}
	}

	/**
	 * Remove a selected field from the current filter and
	 * its data will be written on the output in JSON format
	 */
	public function removeSelectedField()
	{
		$selectedField = $this->input->post('selectedField');

		if ($this->filtercmptlib->removeSelectedField($selectedField) == true)
		{
			$this->outputJsonSuccess('Field removed');
		}
		else
		{
			$this->outputJsonError('Error occurred');
		}
	}

	/**
	 * Add a field to the current filter and its data will be written on the output in JSON format
	 */
	public function addSelectedField()
	{
		$selectedField = $this->input->post('selectedField');

		if ($this->filtercmptlib->addSelectedField($selectedField) == true)
		{
			$this->outputJsonSuccess('Field added');
		}
		else
		{
			$this->outputJsonError('Error occurred');
		}
	}

	/**
	 * Remove an applied filter (SQL where condition) from the current filter
	 */
	public function removeFilterField()
	{
		$appliedFilter = $this->input->post('filterField');

		if ($this->filtercmptlib->removeFilterField($appliedFilter) == true)
		{
			$this->outputJsonSuccess('Field removed');
		}
		else
		{
			$this->outputJsonError('Error occurred');
		}
	}

	/**
	 * Add a filter (SQL where clause) to be applied to the current filter
	 */
	public function addFilterField()
	{
		$filterField = $this->input->post('filterField');

		if ($this->filtercmptlib->addFilterField($filterField) == true)
		{
			$this->outputJsonSuccess('Field added');
		}
		else
		{
			$this->outputJsonError('Error occurred');
		}
	}

	/**
	 * Apply the filter changes
	 */
	public function applyFilterFields()
	{
		$filterFields = $this->input->post('filterFields');

		if ($this->filtercmptlib->applyFilterFields($filterFields) == true)
		{
			$this->outputJsonSuccess('Applied');
		}
		else
		{
			$this->outputJsonError('Error occurred');
		}
	}

	/**
	 * Save the current filter as a custom filter for this user with the given description
	 */
	public function saveCustomFilter()
	{
		$customFilterName = $this->input->post('customFilterName');

		if ($this->filtercmptlib->saveCustomFilter($customFilterName) == true)
		{
			$this->outputJsonSuccess('Saved');
		}
		else
		{
			$this->outputJsonError('An error occurred while saving a custom filter');
		}
	}

	/**
	 * Remove a custom filter by its filterId
	 */
	public function removeCustomFilter()
	{
		$filterId = $this->input->post('filterId');

		if ($this->filtercmptlib->removeCustomFilter($filterId) == true)
		{
			$this->outputJsonSuccess('Removed');
		}
		else
		{
			$this->outputJsonError('Wrong parameter');
		}
	}

	/**
	 * Reloads the dataset
	 */
	public function reloadDataset()
	{
		$this->filtercmptlib->reloadDataset();

		$this->outputJsonSuccess('Success');
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

		// If the parameter FILTER_UNIQUE_ID is present in the HTTP GET or POST
		if (isset($_GET[self::FILTER_UNIQUE_ID]) || isset($_POST[self::FILTER_UNIQUE_ID]))
		{
			// If it is present in the HTTP GET
			if (isset($_GET[self::FILTER_UNIQUE_ID]))
			{
				$filterUniqueId = $this->input->get(self::FILTER_UNIQUE_ID); // is retrieved from the HTTP GET
			}
			elseif (isset($_POST[self::FILTER_UNIQUE_ID])) // Else if it is present in the HTTP POST
			{
				$filterUniqueId = $this->input->post(self::FILTER_UNIQUE_ID); // is retrieved from the HTTP POST
			}
		}
		else // Otherwise an error will be written in the output
		{
			$this->terminateWithJsonError('Parameter "'.self::FILTER_UNIQUE_ID.'" not provided!');
		}

		// If the parameter FILTER_TYPE is present in the HTTP GET or POST
		if (isset($_GET[self::FILTER_TYPE]) || isset($_POST[self::FILTER_TYPE]))
		{
			// If it is present in the HTTP GET
			if (isset($_GET[self::FILTER_TYPE]))
			{
				$filterType = $this->input->get(self::FILTER_TYPE); // is retrieved from the HTTP GET
			}
			elseif (isset($_POST[self::FILTER_TYPE])) // Else if it is present in the HTTP POST
			{
				$filterType = $this->input->post(self::FILTER_TYPE); // is retrieved from the HTTP POST
			}
		}
		else // Otherwise an error will be written in the output
		{
			$this->terminateWithJsonError('Parameter "'.self::FILTER_TYPE.'" not provided!');
		}

		// Loads the FilterCmptLib that contains all the used logic
		$this->load->library(
			'FilterCmptLib',
			array(
				'filterUniqueId' => $filterUniqueId,
				'filterType' => $filterType,
				'filterId' => $this->input->get('filterId')
			)
		);

		// 
		$this->filtercmptlib->start();
	}
}

