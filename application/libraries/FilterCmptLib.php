<?php
/**
 * Copyright (C) 2022 fhcomplete.org
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

use \stdClass as stdClass;

/**
 * Filter component logic
 */
class FilterCmptLib
{
	// FilterCmpt session name
	const SESSION_NAME = 'FHC_FILTER_COMPONENT';

	// Session elements
	const SESSION_FILTER_NAME = 'filterName';
	const SESSION_FIELDS = 'fields';
	const SESSION_SELECTED_FIELDS = 'selectedFields';
	const SESSION_FILTERS = 'filters';
	const SESSION_METADATA = 'datasetMetadata';
	const SESSION_ROW_NUMBER = 'rowNumber';
	const SESSION_TIMEOUT = 'sessionTimeout';

	// Session dataset elements
	const SESSION_DATASET = 'dataset';
	const SESSION_DATASET_RELOAD = 'reloadDataset';

	const SESSION_SIDE_MENU = 'sideMenu';

	// Default session timeout
	const SESSION_DEFAULT_TIMEOUT = 30;

	// Alias for the dynamic table used to retrieve the dataset
	const DATASET_TABLE_ALIAS = 'datasetFilterTable';

	// Parameters names...
	// ...to identify a single filter component in the session
	const FHC_CONTROLLER_ID = 'fhc_controller_id';

	 // ...to identify a single filter component in the DB
	const FILTER_ID = 'filter_id';
	const APP = 'app';
	const DATASET_NAME = 'datasetName';
	const FILTER_KURZBZ = 'filterKurzbz';
	const DATASET_RELOAD = 'reloadDataset';

	// ...to specify permissions that are needed to use this FilterCmpt
	const REQUIRED_PERMISSIONS = 'requiredPermissions';

	// ...stament to retrieve the dataset
	const QUERY = 'query';

	// Filter operations values
	const OP_EQUAL = 'equal';
	const OP_NOT_EQUAL = 'nequal';
	const OP_GREATER_THAN = 'gt';
	const OP_LESS_THAN = 'lt';
	const OP_IS_TRUE = 'true';
	const OP_IS_FALSE = 'false';
	const OP_CONTAINS = 'contains';
	const OP_NOT_CONTAINS = 'ncontains';
	const OP_SET = 'set';
	const OP_NOT_SET = 'nset';

	// Filter options values
	const OPT_MINUTES = 'minutes';
	const OPT_HOURS = 'hours';
	const OPT_DAYS = 'days';
	const OPT_MONTHS = 'months';

	const FILTER_PHRASES_CATEGORY = 'FilterWidget'; // The category used to store phrases for the FilterCmpt

	const FILTER_UNIQUE_ID = 'filterUniqueId'; // Filter page parameter name

	const PERMISSION_FILTER_METHOD = 'FilterCmpt'; // Name for fake method to be checked by the PermissionLib
	const PERMISSION_TYPE = 'r';

	private $_ci; // Code igniter instance

	private $_filterUniqueId; // Unique id for this filter component
	private $_filterType; //
	private $_filterId; //

	private $_app;
	private $_datasetName;
	private $_filterKurzbz;
	private $_query;
	private $_requiredPermissions;
	private $_reloadDataset;
	private $_sessionTimeout;

	/**
	 * Gets the CI instance and loads message helper
	 */
	public function __construct($params)
	{
		$this->_ci =& get_instance(); // get code igniter instance

		// Set parameters
		$this->_setParameters($params);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Wrapper method to the session helper funtions to retrieve the whole session for this filter
	 */
	public function getSession()
	{
		return getSessionElement(self::SESSION_NAME, $this->_filterUniqueId);
	}

	/**
	 * Contains all the logic used to load all the data needed to the FilterCmpt
	 */
	public function start()
	{
		//
		if (!$this->_checkJSParameters()) return;

		$filterCmptArray = array(); // default value

		//
		$filePath = findResource(APPPATH.'components/filters/', $this->_filterType, true);
		if (!isEmptyString($filePath))
		{
			// Gets the filter configuration from the file system
			require_once($filePath);
		}
		else
		{
			$filePath = findResource(APPPATH.'components/extensions/', $this->_filterType, true, 'filters');
			if (!isEmptyString($filePath)) require_once($filePath);
		}

		//
		if (!isset($filterCmptArray) && isEmptyArray($filterCmptArray))
		{
			$this->_setSession(error('Component definition file '.$this->_filterType.' not found'));
			return;
		}

		//
		if (!$this->_checkPHPParameters($filterCmptArray)) return;

		//
		$this->_initFilterCmpt($filterCmptArray);

		//
		if (!$this->_isAllowed()) return;

		// Looks for expired filter components in session and drops them
		$this->_dropExpiredFilterCmpts();

		// Read the all session for this filter component
		$session = $this->getSession();

		// If session is NOT empty -> a filter was already loaded
		if ($session != null)
		{
			// Retrieve the filterId stored in the session
			$sessionFilterId = $this->_getSessionElement(FilterCmptLib::FILTER_ID);

			// If the filter loaded in session is NOT the same that is being requested then empty the session
			if ($this->_filterId != $sessionFilterId)
			{
				$this->_setSession(null);
				$session = null;
			}
			else // else if the filter loaded in session is the same that is being requested
			{
				// Get SESSION_DATASET_RELOAD from the session
				$sessionReloadDataset = $this->_getSessionElement(FilterCmptLib::SESSION_DATASET_RELOAD);

				// if Filter changed or reload is forced by parameter then reload the Dataset
				if ($this->_reloadDataset === true || $sessionReloadDataset === true)
				{
					// Set as false to stop changing the dataset
					$this->_setSessionElement(FilterCmptLib::SESSION_DATASET_RELOAD, false);

					// Generate dataset query using filters from the session
					$datasetQuery = $this->_generateDatasetQuery(
						$this->_query,
						$this->_getSessionElement(FilterCmptLib::SESSION_FILTERS)
					);

					// Then retrieve dataset from DB
					$dataset = $this->_getDataset($datasetQuery);

					// Save changes into session if data are valid
					if (!isError($dataset))
					{
						// Set the new dataset and its attributes in the session
						$this->_setSessionElement(FilterCmptLib::SESSION_METADATA, $this->_ci->FiltersModel->getExecutedQueryMetaData());
						$this->_setSessionElement(FilterCmptLib::SESSION_ROW_NUMBER, count($dataset->retval));
						$this->_setSessionElement(FilterCmptLib::SESSION_DATASET, $dataset->retval);
					}
				}
			}
		}

		// If the session is empty -> first time that this filter is loaded
		if ($session == null)
		{
			// Load filter definition data from DB
			$definition = $this->_loadDefinition(
				$this->_filterId,
				$this->_app,
				$this->_datasetName,
				$this->_filterKurzbz
			);

			// Checks and parse json present into the definition
			$parsedFilterJson = $this->_parseFilterJson($definition);
			if ($parsedFilterJson != null) // if the json is valid
			{
				// Generate dataset query
				$datasetQuery = $this->_generateDatasetQuery($this->_query, $parsedFilterJson->filters);

				// Then retrieve dataset from DB
				$dataset = $this->_getDataset($datasetQuery);

				// Try to load the name of the filter using the PhrasesLib
				$filterName = $this->_getFilterName($parsedFilterJson);

				// Save changes into session if data are valid
				if (!isError($dataset))
				{
					// Stores an array that contains all the data useful for
					$this->_setSession(
						array(
							FilterCmptLib::FILTER_ID => $this->_filterId, // the current filter id
							FilterCmptLib::APP => $this->_app, // the current app parameter
							FilterCmptLib::DATASET_NAME => $this->_datasetName, // the carrent dataset name
							FilterCmptLib::SESSION_FILTER_NAME => $filterName, // the current filter name
							FilterCmptLib::SESSION_FIELDS => $this->_ci->FiltersModel->getExecutedQueryListFields(), // all the fields of the dataset
							FilterCmptLib::SESSION_SELECTED_FIELDS => $this->_getColumnsNames($parsedFilterJson->columns), // all the selected fields
							FilterCmptLib::SESSION_FILTERS => $parsedFilterJson->filters, // all the filters used to filter the dataset
							FilterCmptLib::SESSION_METADATA => $this->_ci->FiltersModel->getExecutedQueryMetaData(), // the metadata of the dataset
							FilterCmptLib::SESSION_ROW_NUMBER => count($dataset->retval), // the number of loaded rows by this filter
							FilterCmptLib::SESSION_DATASET => $dataset->retval, // the entire dataset
							FilterCmptLib::SESSION_DATASET_RELOAD => false, // if the dataset must be reloaded, not needed the first time
							FilterCmptLib::SESSION_SIDE_MENU => $this->_generateFilterMenu($this->_app, $this->_datasetName)
						)
					);
				}
			}
		}

		// NOTE: latest operations to be performed in the session to be shure that they are always present
		// To be always stored in the session, otherwise is not possible to load data from Filters controller
		$this->_setSessionElement(FilterCmptLib::REQUIRED_PERMISSIONS, $this->_requiredPermissions);
		// Renew or set the session expiring time
		$this->_setSessionElement(FilterCmptLib::SESSION_TIMEOUT, strtotime('+'.$this->_sessionTimeout.' minutes', time()));
	}

	/**
	 * Add a filter (SQL where clause) to be applied to the current filter
	 */
	public function addFilterField($filterField)
	{
		$addFilterField = false;

		// Checks the parameter filter
		if (!isEmptyString($filterField))
		{
			// Retrieves all the used fields by the current filter
			$fields = $this->_getSessionElement(self::SESSION_FIELDS);
			// Retrieves the applied filters by the current filter
			$filters = $this->_getSessionElement(self::SESSION_FILTERS);

			// Checks that the given applied filter is present in the list of all the used fields by the current filter
			if (in_array($filterField, $fields))
			{
				// Search in what position the given applied filter is
				$pos = $this->_searchFilterByName($filters, $filterField);
				if ($pos === false) // If NOT found then add it
				{
					// New filter definition
					$filterDefinition = new stdClass();
					// Sets filter definition required properties
					$filterDefinition->name = $filterField;
					// Sets filter definition optional properties
					$filterDefinition->operation = null;
					$filterDefinition->condition = null;
					$filterDefinition->option = null;
					// Place the new applied filter at the end of the applied filters list
					array_push($filters, $filterDefinition);
				}

				$this->_setSessionElement(self::SESSION_FILTERS, $filters); // write changes into the session

				$addFilterField = true;
			}
		}

		return $addFilterField;
	}

	/**
	 * Remove an applied filter (SQL where condition) from the current filter
	 */
	public function removeFilterField($filterField)
	{
		$removeFilterField = false;

		// Checks the parameter filterField
		if (!isEmptyString($filterField))
		{
			// Retrieves all the used fields by the current filter
			$fields = $this->_getSessionElement(self::SESSION_FIELDS);
			// Retrieves the applied filters by the current filter
			$filters = $this->_getSessionElement(self::SESSION_FILTERS);

			// Checks that the given applied filter is present in the list of all the used fields by the current filter
			if (in_array($filterField, $fields))
			{
				// Search in what position the given applied filter is
				$pos = $this->_searchFilterByName($filters, $filterField);
				if ($pos !== false) // If found
				{
					array_splice($filters, $pos, 1); // Then remove it and shift the rest of elements by one if needed
				}

				 // Write changes into the session
				$this->_setSessionElement(self::SESSION_FILTERS, $filters);
				$this->_setSessionElement(self::SESSION_DATASET_RELOAD, true); // the dataset must be reloaded

				$removeFilterField = true;
			}
		}

		return $removeFilterField;
	}

	/**
	 * Apply all the applied filters (SQL where conditions) to the current filter
	 */
	public function applyFilterFields($filterFields)
	{
		$applyFilters = false;

		// Check if the parameter is an array and it is not empty
		if (!isEmptyArray($filterFields))
		{
			$filters = array();

			// Check if the parameter is fine
			$fine = true;
			foreach ($filterFields as $filterField)
			{
				// If not an empty array
				if ($filterField != null)
				{
					//
					if (isset($filterField->name) && isset($filterField->operation) && isset($filterField->condition)
						&& !isEmptyString($filterField->name) && !isEmptyString($filterField->operation)
						&& !isEmptyString($filterField->condition))
					{
						// Fine
						$filter = new stdClass();
						$filter->name = $filterField->name;
						$filter->operation = $filterField->operation;
						$filter->condition = $filterField->condition;
						if (isset($filterField->option) && !isEmptyString($filterField->option))
						{
							$filter->option = $filterField->option;
						}
						else
						{
							$filter->option = null;
						}
						$filters[] = $filter;
					}
					else // otherwise is not fine and stop checking
					{
						$fine = false;
						break;
					}
				}
				else //
				{
					$fine = false;
					break;
				}
			}

			//
			if ($fine)
			{
				// Write changes into the session
				$this->_setSessionElement(self::SESSION_FILTERS, $filters);
				$this->_setSessionElement(self::SESSION_DATASET_RELOAD, true); // the dataset must be reloaded

				$applyFilters = true;
			}
		}

		return $applyFilters;
	}

	/**
	 * Reloads dataset by setting session variable to true
	 */
	public function reloadDataset()
	{
		$this->_setSessionElement(self::SESSION_DATASET_RELOAD, true);
	}

	/**
	 * Save the current filter as a custom filter for this user with the given description
	 */
	public function saveCustomFilter($customFilterDescription)
	{
		$saveCustomFilter = false; // by default returns a failure

		// Checks parameter customFilterDescription if not valid stop the execution
		if (isEmptyString($customFilterDescription)) return $saveCustomFilter;

		$this->_ci->load->model('system/Filters_model', 'FiltersModel'); // to load the filter definitions
		$this->_ci->FiltersModel->resetQuery(); // reset any previous built query

		// person_id of the authenticated user
		$authPersonId = getAuthPersonId();
		// Postgres array for the description
		$descPGArray = str_replace('%desc%', $customFilterDescription, '{"%desc%", "%desc%", "%desc%", "%desc%"}');

		// Loads the definition to check if is already present in the DB
		$definition = $this->_ci->FiltersModel->loadWhere(array(
			'app' => $this->_getSessionElement(self::APP),
			'dataset_name' => $this->_getSessionElement(self::DATASET_NAME),
			'description' => $descPGArray,
			'person_id' => $authPersonId
		));

		// New definition to be json encoded
		$jsonDeifinition = new stdClass();
		$jsonDeifinition->name = $customFilterDescription; // name of the filter

		// Generates the "column" property
		$jsonDeifinition->columns = array();
		$selectedFields = $this->_getSessionElement(self::SESSION_SELECTED_FIELDS); // retrieved the selected fields
		for ($i = 0; $i < count($selectedFields); $i++)
		{
			// Each element is an object with a property called "name"
			$jsonDeifinition->columns[$i] = new stdClass();
			$jsonDeifinition->columns[$i]->name = $selectedFields[$i];
		}

		// List of applied filters
		$jsonDeifinition->filters = $this->_getSessionElement(self::SESSION_FILTERS);

		// If it is already present
		if (hasData($definition))
		{
			// update it
			$this->_ci->FiltersModel->update(
				array(
					'app' => $this->_getSessionElement(self::APP),
					'dataset_name' => $this->_getSessionElement(self::DATASET_NAME),
					'description' => $descPGArray,
					'person_id' => $authPersonId
				),
				array(
					'filter' => json_encode($jsonDeifinition)
				)
			);

			$saveCustomFilter = true;
		}
		else // otherwise insert a new one
		{
			$this->_ci->FiltersModel->insert(
				array(
					'app' => $this->_getSessionElement(self::APP),
					'dataset_name' => $this->_getSessionElement(self::DATASET_NAME),
					'filter_kurzbz' => uniqid($authPersonId, true),
					'description' => $descPGArray,
					'person_id' => $authPersonId,
					'sort' => null,
					'default_filter' => false,
					'filter' => json_encode($jsonDeifinition),
					'oe_kurzbz' => null
				)
			);

			$saveCustomFilter = true;
		}

		if ($saveCustomFilter === true) 
		{
			$this->_setSessionElement(FilterCmptLib::SESSION_SIDE_MENU, 
				$this->_generateFilterMenu($this->_app, $this->_datasetName));	
		}
		
		return $saveCustomFilter;
	}

	/**
	 * Remove a custom filter by its filter_id
	 */
	public function removeCustomFilter($filterId)
	{
		$removeCustomFilter = false;

		// Checks the parameter filterId
		if (isset($filterId) && is_numeric($filterId) && $filterId > 0)
		{
			$this->_ci->load->model('system/Filters_model', 'FiltersModel'); // to remove the filter definitions from DB

			// Delete it from database
			$this->_ci->FiltersModel->delete(array(self::FILTER_ID => $filterId));

			// Delete it from session
			$this->_dropFromSessionFilterCmptById($filterId);

			$removeCustomFilter = true;
		}

		return $removeCustomFilter;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Generates the filters menu structure array and stores it into the session
	 */
	private function _generateFilterMenu($app, $datasetName)
	{
		$filterMenu = new stdClass();
		$filterMenu->filters = array();
		$filterMenu->personalFilters = array();

		// Loads the Filters model
		$this->_ci->load->model('system/Filters_model', 'FiltersModel');

		// Loads all the filters related to this page (same dataset_name and same app name)
		$filters = $this->_ci->FiltersModel->getFiltersByAppDatasetNamePersonId(
			$app,
			$datasetName,
			getAuthPersonId()
		);

		// If filters were loaded
		if (hasData($filters))
		{
			// Loops through loaded filters
			foreach (getData($filters) as $filter)
			{
				$menuEntry = new stdClass();
				$menuEntry->desc = $filter->description[0];
				$menuEntry->filter_id = $filter->filter_id;

				// If it is NOT a personal filter
				if ($filter->person_id == null)
				{
					$filterMenu->filters[] = $menuEntry;
				}
				else // otherwise
				{
					$menuEntry->subscriptDescription = '(Remove)';
					$menuEntry->subscriptLinkClass = 'remove-custom-filter';
					$menuEntry->subscriptLinkValue = $filter->{self::FILTER_ID};

					$filterMenu->personalFilters[] = $menuEntry; // adds to personal filters menu array
				}
			}
		}

		return $filterMenu;
	}

	/**
	 * Wrapper method to the session helper funtions to retrieve one element from the session of this filter
	 */
	private function _getSessionElement($name)
	{
		$session = getSessionElement(self::SESSION_NAME, $this->_filterUniqueId);

		if (isset($session[$name]))
		{
			return $session[$name];
		}

		return null;
	}

	/**
	 * Checks if at least one of the permissions given as parameter (requiredPermissions) belongs
	 * to the authenticated user, if confirmed then is allowed to use this FilterCmpt.
	 * If the parameter requiredPermissions is NOT given or is not present in the session,
	 * then NO one is allow to use this FilterCmpt
	 * Wrapper method to permissionlib->hasAtLeastOne
	 */
	private function _isAllowed()
	{
		$this->_ci->load->library('PermissionLib'); // Load permission library

		if (!$this->_ci->permissionlib->hasAtLeastOne($this->_requiredPermissions, self::PERMISSION_FILTER_METHOD, self::PERMISSION_TYPE))
		{
			$this->_setSession(error('The required permission is not help by the logged user'));
			return false;
		}

		return true;
	}

	/**
	 *
	 */
	private function _setParameters($params)
	{
		if (isset($params['filterUniqueId'])) $this->_filterUniqueId = $params['filterUniqueId'];
		if (isset($params['filterType'])) $this->_filterType = $params['filterType'];
		if (isset($params['filterId'])) $this->_filterId = $params['filterId'];
	}

	/**
	 * Checks the required parameters used to call this FilterCmpt
	 */
	private function _checkJSParameters()
	{
		//
		if (isEmptyString($this->_filterUniqueId))
		{
			$this->_setSession(error('Parameter "filterUniqueId" not provided'));
			return false;
		}

		//
		if (isEmptyString($this->_filterType))
		{
			$this->_setSession(error('Parameter "filterType" not provided'));
			return false;
		}

		return true;
	}

	/**
	 * Checks the required parameters used to call this FilterCmpt
	 */
	private function _checkPHPParameters($filterCmptArray)
	{
		// If no options are given to this component...
		if (!is_array($filterCmptArray) || (is_array($filterCmptArray) && count($filterCmptArray) == 0))
		{
			$this->_setSession(error('No parameters provided'));
			return false;
		}
		else // ...otherwise
		{
			// Parameters app AND dataset name
			if (!isset($filterCmptArray[FilterCmptLib::APP]) && !isset($filterCmptArray[FilterCmptLib::DATASET_NAME]))
			{
				$this->_setSession(
					error(
						'The parameters "'.FilterCmptLib::APP.'" AND "'.FilterCmptLib::DATASET_NAME.' must be specified'
					)
				);
				return false;
			}

			// The query parameter is mandatory
			if (!isset($filterCmptArray[FilterCmptLib::QUERY]))
			{
				$this->_setSession(error('The parameter "'.FilterCmptLib::QUERY.'" must be specified'));
				return false;
			}

			//
			if (!isset($filterCmptArray[FilterCmptLib::DATASET_NAME]))
			{
				$this->_setSession(error('The parameter "'.FilterCmptLib::DATESET_NAME.'" must be specified'));
				return false;
			}

			//
			if (!isset($filterCmptArray[FilterCmptLib::REQUIRED_PERMISSIONS]))
			{
				$this->_setSession(error('The parameter "'.FilterCmptLib::REQUIRED_PERMISSIONS.'" must be specified'));
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks parameters and initialize all the properties of this FilterCmpt
	 */
	private function _initFilterCmpt($filterCmptArray)
	{
		// If here then everything is ok

		// Initialize class properties
		$this->_app = null;
		$this->_datasetName = null;
		$this->_filterKurzbz = null;
		$this->_query = null;
		$this->_requiredPermissions = null;

		$this->_reloadDataset = true; // by default the dataset is NOT cached in session
		$this->_sessionTimeout = FilterCmptLib::SESSION_DEFAULT_TIMEOUT;

		// Retrieved the required permissions parameter if present
		if (isset($filterCmptArray[FilterCmptLib::REQUIRED_PERMISSIONS]))
		{
			$this->_requiredPermissions = $filterCmptArray[FilterCmptLib::REQUIRED_PERMISSIONS];
		}

		// Parameters needed to retrieve univocally a filter from DB
		if (isset($filterCmptArray[FilterCmptLib::APP]))
		{
			$this->_app = $filterCmptArray[FilterCmptLib::APP];
		}

		if (isset($filterCmptArray[FilterCmptLib::DATASET_NAME]))
		{
			$this->_datasetName = $filterCmptArray[FilterCmptLib::DATASET_NAME];
		}

		if (isset($filterCmptArray[FilterCmptLib::FILTER_KURZBZ]))
		{
			$this->_filterKurzbz = $filterCmptArray[FilterCmptLib::FILTER_KURZBZ];
		}

		// How to retrieve data for the filter: SQL statement or a result from DB
		if (isset($filterCmptArray[FilterCmptLib::QUERY]))
		{
			$this->_query = $filterCmptArray[FilterCmptLib::QUERY];
		}
	}

	/**
	 * Generates a condition for a SQL where clause using the given applied filter definition.
	 * By default an empty string is returned.
	 */
	private function _getDatasetQueryCondition($filterDefinition)
	{
		$condition = ''; // starts building the condition

		$this->_ci->load->model('system/Filters_model', 'FiltersModel');

		// "operation" is a required property for the applied filter definition
		if (!isEmptyString($filterDefinition->operation))
		{
			// Checks what operation is required
			switch ($filterDefinition->operation)
			{
				// comparison (==)
				case self::OP_EQUAL:
					// Numeric
					if (is_numeric($filterDefinition->condition))
					{
						$condition = '= '.$filterDefinition->condition;
					}
					else // string type
					{
						$condition = '= \''.$this->_ci->FiltersModel->escapeLike($filterDefinition->condition).'\'';
					}
					break;
				// not equal (!=)
				case self::OP_NOT_EQUAL:
					// Numeric
					if (is_numeric($filterDefinition->condition))
					{
						$condition = '!= '.$filterDefinition->condition;
					}
					else // string type
					{
						$condition = '!= \''.$this->_ci->FiltersModel->escapeLike($filterDefinition->condition).'\'';
					}
					break;
				// greater than (>)
				case self::OP_GREATER_THAN:
					// If it's a date type
					if (is_numeric($filterDefinition->condition)
						&& isset($filterDefinition->option)
						&& ($filterDefinition->option == self::OPT_HOURS
						|| $filterDefinition->option == self::OPT_DAYS
						|| $filterDefinition->option == self::OPT_MONTHS
						|| $filterDefinition->option == self::OPT_MINUTES))
					{
						$condition = '< (NOW() - \''.$filterDefinition->condition.' '.$filterDefinition->option.'\'::interval)';
					}
					else // otherwise is a number
					{
						$condition = '> '.$filterDefinition->condition;
					}
					break;
				// less than (<)
				case self::OP_LESS_THAN:
					// If it's a date type
					if (is_numeric($filterDefinition->condition)
						&& isset($filterDefinition->option)
						&& ($filterDefinition->option == self::OPT_HOURS
						|| $filterDefinition->option == self::OPT_DAYS
						|| $filterDefinition->option == self::OPT_MONTHS
						|| $filterDefinition->option == self::OPT_MINUTES))
					{
						$condition = '> (NOW() - \''.$filterDefinition->condition.' '.$filterDefinition->option.'\'::interval)';
					}
					else // otherwise is a number
					{
						$condition = '< '.$filterDefinition->condition;
					}
					break;
				// contains (ILIKE)
				case self::OP_CONTAINS:
					$condition = 'ILIKE \'%'.$this->_ci->FiltersModel->escapeLike($filterDefinition->condition).'%\'';
					break;
				// not contains (NOT ILIKE)
				case self::OP_NOT_CONTAINS:
					$condition = 'NOT ILIKE \'%'.$this->_ci->FiltersModel->escapeLike($filterDefinition->condition).'%\'';
					break;
				// is true (=== true)
				case self::OP_IS_TRUE:
					$condition = 'IS TRUE';
					break;
				// is false (=== false)
				case self::OP_IS_FALSE:
					$condition = 'IS FALSE';
					break;
				// is set
				case self::OP_SET:
					$condition = 'IS NOT NULL';
					break;
				// is NOT set
				case self::OP_NOT_SET:
					$condition = 'IS NULL';
					break;
				// by default must not be null (!= null)
				default:
					$condition = 'IS NOT NULL';
					break;
			}
		}

		// if the condition is valid
		if (!isEmptyString($condition)) $condition = ' '.$condition; // add a white space before

		return $condition;
	}

	/**
	 * Search for a filter inside a list of filters by the given filter name
	 * Returns false if NOT found, otherwise the position inside the list
	 */
	private function _searchFilterByName($filters, $filterName)
	{
		$pos = false;

		for($i = 0; $i < count($filters); $i++)
		{
			if ($filters[$i]->name == $filterName)
			{
				$pos = $i;
				break;
			}
		}

		return $pos;
	}

	/**
	 * Remove from the session the given filter component
	 */
	private function _dropFromSessionFilterCmptById($filterId)
	{
		// Loads the session for all the filter components
		$filterCmptsSession = getSession(self::SESSION_NAME);

		// If something is present in session
		if ($filterCmptsSession != null)
		{
			// Loops in the session for all the filter components
			foreach ($filterCmptsSession as $filterCmpt => $filterCmptData)
			{
				// If this filter component is not the one that we are looking for
				if ($filterCmptData[self::FILTER_ID] == $filterId)
				{
					cleanSessionElement(self::SESSION_NAME, $filterCmpt); // ...remove it
					break; // stop to search
				}
			}
		}
	}

	/**
	 * Utility method that retrieves the name of the columns present in a filter JSON definition
	 */
	private function _getColumnsNames($columns)
	{
		$columnsNames = array();

		// For each column
		foreach ($columns as $obj)
		{
			// If it is set the property name of the column
			if (isset($obj->name)) $columnsNames[] = $obj->name;
		}

		return $columnsNames;
	}

	/**
	 * Wrapper method to the session helper funtions to set the whole session for this filter
	 */
	private function _setSession($data)
	{
		setSessionElement(self::SESSION_NAME, $this->_filterUniqueId, $data);
	}

	/**
	 * Wrapper method to the session helper funtions to set one element in the session for this filter
	 */
	private function _setSessionElement($name, $value)
	{
		$session = getSessionElement(self::SESSION_NAME, $this->_filterUniqueId);

		$session[$name] = $value;

		setSessionElement(self::SESSION_NAME, $this->_filterUniqueId, $session); // stores the single value
	}

	/**
	 *
	 */
	private function _dropExpiredFilterCmpts()
	{
		// Loads the session for all the filter components
		$filterCmptsSession = getSession(self::SESSION_NAME);

		// If something is present in session
		if ($filterCmptsSession != null)
		{
			// Loops in the session for all the filter components
			foreach ($filterCmptsSession as $filterCmpt => $filterCmptData)
			{
				// If this filter component is not the current used filter component and the it is expired...
				if ($this->_filterUniqueId != $filterCmpt && $filterCmptData[self::SESSION_TIMEOUT] <= time())
				{
					cleanSessionElement(self::SESSION_NAME, $filterCmpt); // ...remove it
				}
			}
		}
	}

	/**
	 * Loads the definition data from DB for a filter component
	 */
	private function _loadDefinition($filterId, $app, $datasetName, $filterKurzbz)
	{
		// Loads the needed models
		$this->_ci->load->model('system/Filters_model', 'FiltersModel');
		$this->_ci->FiltersModel->resetQuery(); // reset any previous built query

		$this->_ci->FiltersModel->addSelect('system.tbl_filters.*'); // select only from table filters
		$this->_ci->FiltersModel->addOrder('sort', 'ASC'); // sort on column sort
		$this->_ci->FiltersModel->addLimit(1); // if more than one filter is set as default only one will be retrieved

		$definition = null;
		$whereParameters = null; // where clause parameters

		// If we have a good filterId then use it!
		if ($filterId != null && is_numeric($filterId) && $filterId > 0)
		{
			$whereParameters = array(
				self::FILTER_ID => $filterId
			);
		}
		else
		{
			// If we can univocally retrieve a filter
			if ($app != null && $datasetName != null && $filterKurzbz != null)
			{
				$whereParameters = array(
					'app' => $app,
					'dataset_name' => $datasetName,
					'filter_kurzbz' => $filterKurzbz
				);
			}
			// Else if we have only app and datasetName
			elseif ($app != null && $datasetName != null && $filterKurzbz == null)
			{
				// Try to load the custom filter (person_id = logged user person_id) with the given "app" and "dataset_name"
				// that is set as default filter (default_filter = true)
				$whereParameters = array(
					'app' => $app,
					'dataset_name' => $datasetName,
					'person_id' => getAuthPersonId(),
					'default_filter' => true
				);

				$definition = $this->_ci->FiltersModel->loadWhere($whereParameters);
				if (!hasData($definition)) // If a custom filter is NOT found
				{
					// Try to load the global filter (person_id = null) with the given "app" and "dataset_name" that is set as
					// default filter (default_filter = true)
					$whereParameters = array(
						'app' => $app,
						'dataset_name' => $datasetName,
						'person_id' => null,
						'default_filter' => true
					);

					$definition = $this->_ci->FiltersModel->loadWhere($whereParameters);
				}
			}
		}

		// If no definition where loaded and where parameters were set
		if ($definition == null && $whereParameters != null)
		{
			$definition = $this->_ci->FiltersModel->loadWhere($whereParameters);

			// Last chance!!!
			if (!hasData($definition)) // If no data have been found until now the tries the most desperate query
			{
				$this->_ci->FiltersModel->addOrder('filter_id', 'ASC'); // sort on column filter_id to get the oldest
				$whereParameters = array(
					'app' => $app,
					'dataset_name' => $datasetName
				);

				$definition = $this->_ci->FiltersModel->loadWhere($whereParameters);
			}
		}

		return $definition;
	}

	/**
	 * Checks if the json definition of this filter is valid
	 */
	private function _parseFilterJson($definition)
	{
		$jsonEncodedFilter = null;

		// If the definition contains data and they are valid
		if (hasData($definition) && isset(getData($definition)[0]->filter) && trim(getData($definition)[0]->filter) != '')
		{
			// Get the json definition of the filter
			$tmpJsonEncodedFilter = json_decode(getData($definition)[0]->filter);

			// Checks required filter's properies
			if (isset($tmpJsonEncodedFilter->name)
				&& isset($tmpJsonEncodedFilter->columns)
				&& is_array($tmpJsonEncodedFilter->columns)
				&& isset($tmpJsonEncodedFilter->filters)
				&& is_array($tmpJsonEncodedFilter->filters))
			{
				$jsonEncodedFilter = $tmpJsonEncodedFilter;
			}
		}

		return $jsonEncodedFilter;
	}

	/**
	 * Generate the query to retrieve the dataset for a filter
	 */
	private function _generateDatasetQuery($query, $filters)
	{
		$datasetQuery = 'SELECT * FROM ('.$query.') '.self::DATASET_TABLE_ALIAS;

		// If the given query is valid and the parameter filters is an array
		if (!isEmptyString($query) && $filters != null && is_array($filters))
		{
			$where = ''; // starts building the SQL where clause

			// Loops through the given applied filters
			for ($filtersCounter = 0; $filtersCounter < count($filters); $filtersCounter++)
			{
				$filterDefinition = $filters[$filtersCounter]; // definition of one filter

				// If the name of the applied filter is valid
				if  (!isEmptyString($filterDefinition->name))
				{
					// Build the query conditions
					$datasetQueryCondition = $this->_getDatasetQueryCondition($filterDefinition);

					// If the built condition is valid then add it to the query clause
					if (!isEmptyString($datasetQueryCondition))
					{
						// If this is NOT the first one
						if ($filtersCounter > 0) $where .= ' AND ';

						$where .= '"'.$filterDefinition->name.'"'.$datasetQueryCondition;
					}
				}
			}

			// If the SQL where clause was built
			if ($where != '') $datasetQuery .= ' WHERE '.$where;
		}

		return $datasetQuery;
	}

	/**
	 * Retrieves the dataset from the DB
	 */
	private function _getDataset($datasetQuery)
	{
		$dataset = null;

		if ($datasetQuery != null)
		{
			$this->_ci->load->model('system/Filters_model', 'FiltersModel');

			// Execute the given SQL statement suppressing error messages
			$dataset = @$this->_ci->FiltersModel->execReadOnlyQuery($datasetQuery);
		}

		return $dataset;
	}

	/**
	 * Get the filter name, the default is the "name" property of the JSON definition
	 * If the property namePhrase is present into the JSON definition, then try to load that from the phrases system
	 * NOTE: filterJson should be already checked using the method _parseFilterJson
	 */
	private function _getFilterName($filterJson)
	{
		$filterName = $filterJson->name; // always present, used as default

		// Filter name from phrases system
		if (isset($filterJson->namePhrase) && !isEmptyString($filterJson->namePhrase))
		{
			// Loads the library to use the phrases system
			$this->_ci->load->library('PhrasesLib', array(self::FILTER_PHRASES_CATEGORY));

			$tmpFilterNamePhrase = $this->_ci->phraseslib->t(self::FILTER_PHRASES_CATEGORY, $filterJson->namePhrase);
			if (!isEmptyString($tmpFilterNamePhrase)) // if is not null or an empty string
			{
				$filterName = $tmpFilterNamePhrase;
			}
		}

		return $filterName;
	}
}

