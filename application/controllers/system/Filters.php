<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the FiltersLib (back-end)
 * Provides data to the ajax get calls about the filter
 * Accepts ajax post calls to change the filter data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Filters extends FHC_Controller
{
	const FILTER_PAGE_PARAM = 'filter_page';

	/**
	 * Calls the parent's constructor and loads the FiltersLib
	 */
	public function __construct()
    {
        parent::__construct();

		$this->_loadFiltersLib(); // Loads the FiltersLib with parameters
    }

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Retrives data about the current filter from the session and will be written on the output in JSON format
	 */
	public function getFilter()
	{
		$this->outputJsonSuccess($this->filterslib->getSession());
	}

	/**
	 * Retrives the number of records present in the current dataset and will be written on the output in JSON format
	 */
	public function rowNumber()
	{
		$rowNumber = 0;
		$dataset = $this->filterslib->getElementSession(FiltersLib::SESSION_DATASET);

		if (isset($dataset) && is_array($dataset))
		{
			$rowNumber = count($dataset);
		}

		$this->outputJsonSuccess($rowNumber);
	}

	/**
	 * Change the sort of the selected fields of the current filter and
	 * its data will be written on the output in JSON format
	 */
	public function sortSelectedFields()
	{
		$selectedFields = $this->input->post('selectedFields');

		if ($this->filterslib->sortSelectedFields($selectedFields) == true)
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

		if ($this->filterslib->removeSelectedField($selectedField) == true)
		{
			$this->getFilter();
		}
		else
		{
			$this->outputJsonError('Wrong parameter');
		}
	}

	/**
	 * Add a field to the current filter and its data will be written on the output in JSON format
	 */
	public function addSelectedField()
	{
		$selectedField = $this->input->post('selectedField');

		if ($this->filterslib->addSelectedField($selectedField) == true)
		{
			$this->getFilter();
		}
		else
		{
			$this->outputJsonError('Wrong parameter');
		}
	}

	/**
	 * Remove an applied filter (SQL where condition) from the current filter
	 */
	public function removeAppliedFilter()
	{
		$appliedFilter = $this->input->post('appliedFilter');

		if ($this->filterslib->removeAppliedFilter($appliedFilter) == true)
		{
			$this->outputJsonSuccess('Removed');
		}
		else
		{
			$this->outputJsonError('Wrong parameter');
		}
	}

	/**
	 * Apply all the applied filters (SQL where conditions) to the current filter
	 */
	public function applyFilters()
	{
		$appliedFilters = $this->input->post('appliedFilters');
		$appliedFiltersOperations = $this->input->post('appliedFiltersOperations');
		$appliedFiltersConditions = $this->input->post('appliedFiltersConditions');
		$appliedFiltersOptions = $this->input->post('appliedFiltersOptions');

		if ($this->filterslib->applyFilters(
				$appliedFilters,
				$appliedFiltersOperations,
				$appliedFiltersConditions,
				$appliedFiltersOptions
			) == true)
		{
			$this->outputJsonSuccess('Applied');
		}
		else
		{
			$this->outputJsonError('Wrong parameter');
		}
	}

	/**
	 * Add a filter (SQL where clause) to be applied to the current filter
	 */
	public function addFilter()
	{
		$filter = $this->input->post('filter');

		if ($this->filterslib->addFilter($filter) == true)
		{
			$this->getFilter();
		}
		else
		{
			$this->outputJsonError('Wrong parameter');
		}
	}

	/**
	 * Save the current filter as a custom filter for this user with the given description
	 */
	public function saveCustomFilter()
	{
		$customFilterDescription = $this->input->post('customFilterDescription');

		if ($this->filterslib->saveCustomFilter($customFilterDescription) == true)
		{
			$this->outputJsonSuccess('Saved');
		}
		else
		{
			$this->outputJsonError('Wrong parameter');
		}
	}

	/**
	 * Remove a custom filter by its filter_id
	 */
	public function removeCustomFilter()
	{
		$filter_id = $this->input->post('filter_id');

		if ($this->filterslib->removeCustomFilter($filter_id) == true)
		{
			$this->outputJsonSuccess('Removed');
		}
		else
		{
			$this->outputJsonError('Wrong parameter');
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Loads the FiltersLib with the FILTER_PAGE_PARAM parameter
	 * If the parameter FILTER_PAGE_PARAM is not given then the execution of the controller is terminated and
	 * an error message is printed
	 */
	private function _loadFiltersLib()
	{
		// If the parameter FILTER_PAGE_PARAM is present in the HTTP GET or POST
		if (isset($_GET[self::FILTER_PAGE_PARAM]) || isset($_POST[self::FILTER_PAGE_PARAM]))
		{
			// If it is present in the HTTP GET
			if (isset($_GET[self::FILTER_PAGE_PARAM]))
			{
				$filterPage = $this->input->get(self::FILTER_PAGE_PARAM); // is retrived from the HTTP GET
			}
			elseif (isset($_POST[self::FILTER_PAGE_PARAM])) // Else if it is present in the HTTP POST
			{
				$filterPage = $this->input->post(self::FILTER_PAGE_PARAM); // is retrived from the HTTP POST
			}

			// Loads the FiltersLib that contains all the used logic
			$this->load->library('FiltersLib', array(self::FILTER_PAGE_PARAM => $filterPage));
		}
		else // Otherwise an error will be written in the output
		{
			// NOTE: Used echo to speed up the output before the exit otherwise it's not shown
			echo 'Parameter "'.self::FILTER_PAGE_PARAM.'" not provided!';
			exit;
		}
	}
}
