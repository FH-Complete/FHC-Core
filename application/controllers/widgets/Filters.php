<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the FilterWidgetLib (back-end)
 * Provides data to the ajax get calls about the filter widget
 * Accepts ajax post calls to change the filter data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 * NOTE: extends the FHC_Controller instead of the Auth_Controller because the FilterWidget has its
 * 		own permissions check
 */
class Filters extends FHC_Controller
{
	const FILTER_UNIQUE_ID = 'filterUniqueId'; // Name of the filter widget unique id

	/**
	 * Calls the parent's constructor and loads the FilterWidgetLib
	 */
	public function __construct()
    {
        parent::__construct();

		// Loads authentication library and starts authentication
		$this->load->library('AuthLib');

		// Loads the FilterWidgetLib with HTTP GET/POST parameters
		$this->_loadFilterWidgetLib();

		// Checks if the caller is allow to read this data
		$this->_isAllowed();
    }

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Retrieves data about the current filter from the session and will be written on the output in JSON format
	 */
	public function getFilter()
	{
		$this->outputJsonSuccess($this->filterwidgetlib->getSession());
	}

	/**
	 * Retrieves the number of records present in the current dataset and will be written on the output in JSON format
	 */
	public function rowNumber()
	{
		$rowNumber = 0;
		$dataset = $this->filterwidgetlib->getSessionElement(FilterWidgetLib::SESSION_DATASET);

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

		if ($this->filterwidgetlib->sortSelectedFields($selectedFields) == true)
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

		if ($this->filterwidgetlib->removeSelectedField($selectedField) == true)
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

		if ($this->filterwidgetlib->addSelectedField($selectedField) == true)
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

		if ($this->filterwidgetlib->removeAppliedFilter($appliedFilter) == true)
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

		if ($this->filterwidgetlib->applyFilters(
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

		if ($this->filterwidgetlib->addFilter($filter) == true)
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

		if ($this->filterwidgetlib->saveCustomFilter($customFilterDescription) == true)
		{
			$this->outputJsonSuccess('Saved');
		}
		else
		{
			$this->outputJsonError('An error occurred while saving a custom filter');
		}
	}

	/**
	 * Remove a custom filter by its filter_id
	 */
	public function removeCustomFilter()
	{
		$filter_id = $this->input->post('filter_id');

		if ($this->filterwidgetlib->removeCustomFilter($filter_id) == true)
		{
			$this->outputJsonSuccess('Removed');
		}
		else
		{
			$this->outputJsonError('Wrong parameter');
		}
	}


	/**
	 * Define the navigation menu for the current filter widget
	 */
	public function setNavigationMenu()
	{
		// Generates the filters structure array
		$this->filterwidgetlib->generateFilterMenu($this->input->get(FilterWidgetLib::NAVIGATION_PAGE));

		$this->outputJsonSuccess('Success');
	}

	/**
	 * Reloads the dataset
	 */
	public function reloadDataset()
	{
		$this->filterwidgetlib->reloadDataset();

		$this->outputJsonSuccess('Success');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks if the user is allowed to use this filter
	 */
	private function _isAllowed()
	{
		if (!$this->filterwidgetlib->isAllowed())
		{
			$this->terminateWithJsonError('You are not allowed to access to this content');
		}
	}

	/**
	 * Loads the FilterWidgetLib with the FILTER_UNIQUE_ID parameter
	 * If the parameter FILTER_UNIQUE_ID is not given then the execution of the controller is terminated and
	 * an error message is printed
	 */
	private function _loadFilterWidgetLib()
	{
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

			// Loads the FilterWidgetLib that contains all the used logic
			$this->load->library('FilterWidgetLib');

			$this->filterwidgetlib->setFilterUniqueId($filterUniqueId);
		}
		else // Otherwise an error will be written in the output
		{
			$this->terminateWithJsonError('Parameter "'.self::FILTER_UNIQUE_ID.'" not provided!');
		}
	}
}

