<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the FilterCmptLib (back-end)
 * Provides data to the ajax get calls about the filter component
 * Listens to ajax post calls to change the filter data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 * NOTE: extends the FHC_Controller instead of the Auth_Controller because the FilterCmpt has its
 * 	own permissions check
 */
class Filter extends FHC_Controller
{
	const FILTER_UNIQUE_ID = 'filterUniqueId'; // Name of the filter cmpt unique id (mandatory)
	const FILTER_TYPE = 'filterType'; // The filter type (PHP filter definition) used (mandatory)
	const FILTER_ID = 'filterId'; // The id of the used filter (optional)

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
	 * Remove an applied filter (SQL where condition) from the current filter
	 */
	public function removeFilterField()
	{
		$request = $this->getPostJSON();

		if (property_exists($request, 'filterField')
			&& $this->filtercmptlib->removeFilterField($request->filterField) == true)
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
		$request = $this->getPostJSON();

		if (property_exists($request, 'filterField')
			&& $this->filtercmptlib->addFilterField($request->filterField) == true)
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
		$request = $this->getPostJSON();

		if (property_exists($request, 'filterFields')
			&& $this->filtercmptlib->applyFilterFields($request->filterFields) == true)
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
		$request = $this->getPostJSON();

		if (property_exists($request, 'customFilterName')
			&& $this->filtercmptlib->saveCustomFilter($request->customFilterName) == true)
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
		$request = $this->getPostJSON();

		if (property_exists($request, 'filterId')
			&& $this->filtercmptlib->removeCustomFilter($request->filterId) == true)
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
		$filterId = null;

		// Try to get the POSTed JSON
		$postJSON = $this->getPostJSON();

		// POSTed JSON
		if ($postJSON != null)
		{
			// If the mandatory parameters FILTER_UNIQUE_ID and FILTER_TYPE have been provided
			if (property_exists($postJSON, self::FILTER_UNIQUE_ID) && property_exists($postJSON, self::FILTER_TYPE))
			{
				// Retrives them from the POSTed JSON
				$filterUniqueId = $postJSON->{self::FILTER_UNIQUE_ID};
				$filterType = $postJSON->{self::FILTER_TYPE};
			}

			// If the optional parameter FILTER_ID has been provided
			if (property_exists($postJSON, self::FILTER_ID)) $filterId = $postJSON->{self::FILTER_ID};
		}
		else // otherwise it is an HTTP GET call
		{
			// If the mandatory parameters FILTER_UNIQUE_ID and FILTER_TYPE have been provided
			if (isset($_GET[self::FILTER_UNIQUE_ID]) && isset($_GET[self::FILTER_TYPE]))
			{
				// Retrives them from the HTTP GET
				$filterUniqueId = $this->input->get(self::FILTER_UNIQUE_ID);
				$filterType = $this->input->get(self::FILTER_TYPE);
			}

			// If the optional parameter FILTER_ID has been provided
			if (isset($_GET[self::FILTER_ID])) $filterId = $filterId = $this->input->get(self::FILTER_ID);
		}

		// If the mandatory parameters have _not_ been provided, then terminate the execution and return an error
		if ($filterUniqueId == null) $this->terminateWithJsonError('Parameter "'.self::FILTER_UNIQUE_ID.'" not provided!');
		if ($filterType == null) $this->terminateWithJsonError('Parameter "'.self::FILTER_TYPE.'" not provided!');

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

