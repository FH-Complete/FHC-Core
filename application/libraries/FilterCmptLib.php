<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

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
	const SESSION_COLUMNS_ALIASES = 'columnsAliases';
	const SESSION_ADDITIONAL_COLUMNS = 'additionalColumns';
	const SESSION_CHECKBOXES = 'checkboxes';
	const SESSION_FILTERS = 'filters';
	const SESSION_METADATA = 'datasetMetadata';
	const SESSION_ROW_NUMBER = 'rowNumber';
	const SESSION_TIMEOUT = 'sessionTimeout';

	// Session dataset elements
	const SESSION_DATASET = 'dataset';
	const SESSION_DATASET_RELOAD = 'reloadDataset';
	const SESSION_DATASET_REPRESENTATION = 'datasetRepresentation';
	const SESSION_DATASET_REP_OPTIONS = 'datasetRepresentationOptions';
	const SESSION_DATASET_REP_FIELDS_DEFS = 'datasetRepresentationFieldsDefinitions';

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

	// ...to specify more columns or aliases for them
	const ADDITIONAL_COLUMNS = 'additionalColumns';
	const CHECKBOXES = 'checkboxes';
	const COLUMNS_ALIASES = 'columnsAliases';

	// ...to format/mark records of a dataset
	const FORMAT_ROW = 'formatRow';
	const MARK_ROW = 'markRow';

	// ...to hide the options for a filter
	const HIDE_OPTIONS = 'hideOptions';
	const HIDE_SELECT_FIELDS = 'hideSelectFields';
	const HIDE_SELECT_FILTERS = 'hideSelectFilters';
	const HIDE_SAVE = 'hideSave';

	const CUSTOM_MENU = 'customMenu'; // ...to specify if the menu for this filter is custom (true) or not (false)
	const HIDE_MENU = 'hideMenu'; // ...to specify if the menu should be shown or not

	// ...to specify how to represent the dataset (ex: tablesorter, pivotUI, ...)
	const DATASET_REPRESENTATION = 'datasetRepresentation';
	const DATASET_REP_OPTIONS = 'datasetRepOptions';
	const DATASET_REP_FIELDS_DEFS = 'datasetRepFieldsDefs';

	// Different dataset representations
	const DATASET_REP_TABLESORTER = 'tablesorter';
	const DATASET_REP_PIVOTUI = 'pivotUI';
	const DATASET_REP_TABULATOR = 'tabulator';

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

	// Navigation page parameter name
	const NAVIGATION_PAGE = 'navigation_page';

	private $_ci; // Code igniter instance
	private $_filterUniqueId; // unique id for this filter component
	private $_filterType; // 
	private $_filterId; // 

	/**
	 * Gets the CI instance and loads message helper
	 */
	public function __construct($params = null)
	{
		$this->_ci =& get_instance(); // get code igniter instance
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Checks if at least one of the permissions given as parameter (requiredPermissions) belongs
	 * to the authenticated user, if confirmed then is allowed to use this FilterCmpt.
	 * If the parameter requiredPermissions is NOT given or is not present in the session,
	 * then NO one is allow to use this FilterCmpt
	 * Wrapper method to permissionlib->hasAtLeastOne
	 */
	public function isAllowed()
	{
		$this->_ci->load->library('PermissionLib'); // Load permission library

		// Gets the required permissions from the session if they are not provided as parameter
		$rq = $this->getSessionElement(self::REQUIRED_PERMISSIONS);

		// 
		if ($rq == null)
		{
			//
			$this->_initFilterCmpt();
			// 
			$this->_startFilterCmpt();
			// Gets the required permissions from the session if they are not provided as parameter
			$rq = $this->getSessionElement(self::REQUIRED_PERMISSIONS);
		}

		return $this->_ci->permissionlib->hasAtLeastOne($rq, self::PERMISSION_FILTER_METHOD, self::PERMISSION_TYPE);
	}

	/**
	 * Wrapper method to the session helper funtions to retrieve the whole session for this filter
	 */
	public function getSession()
	{
		return getSessionElement(self::SESSION_NAME, $this->_filterUniqueId);
	}

	/**
	 * Wrapper method to the session helper funtions to retrieve one element from the session of this filter
	 */
	public function getSessionElement($name)
	{
		$session = getSessionElement(self::SESSION_NAME, $this->_filterUniqueId);

		if (isset($session[$name]))
		{
			return $session[$name];
		}

		return null;
	}

	/**
	 * Wrapper method to the session helper funtions to set the whole session for this filter
	 */
	public function setSession($data)
	{
		setSessionElement(self::SESSION_NAME, $this->_filterUniqueId, $data);
	}

	/**
	 * Wrapper method to the session helper funtions to set one element in the session for this filter
	 */
	public function setSessionElement($name, $value)
	{
		$session = getSessionElement(self::SESSION_NAME, $this->_filterUniqueId);

		$session[$name] = $value;

		setSessionElement(self::SESSION_NAME, $this->_filterUniqueId, $session); // stores the single value
	}

	/**
	 *
	 */
	public function dropExpiredFilterCmpts()
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
	public function loadDefinition($filterId, $app, $datasetName, $filterKurzbz)
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
	public function parseFilterJson($definition)
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
	public function generateDatasetQuery($query, $filters)
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

				if ($filtersCounter > 0)
					$where .= ' AND '; // if it's NOT the last one

				if (!isEmptyString($filterDefinition->name)) // if the name of the applied filter is valid
				{
					// ...build the condition
					$where .= '"'.$filterDefinition->name.'"'.$this->_getDatasetQueryCondition($filterDefinition);
				}
			}

			if ($where != '') // if the SQL where clause was built
			{
				$datasetQuery .= ' WHERE '.$where;
			}
		}

		return $datasetQuery;
	}

	/**
	 * Retrieves the dataset from the DB
	 */
	public function getDataset($datasetQuery)
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
	 * NOTE: filterJson should be already checked using the method parseFilterJson
	 */
	public function getFilterName($filterJson)
	{
		$filterName = $filterJson->name; // always present, used as default
		$trimedname = (isset($filterJson->namePhrase)?trim($filterJson->namePhrase):'');
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

	/**
	 * Change the sort of the selected fields of the current filter
	 */
	public function sortSelectedFields($selectedFields)
	{
		$sortSelectedFields = false;

		// Checks the parameter selectedFields
		if (isset($selectedFields) && is_array($selectedFields) && count($selectedFields) > 0)
		{
			// Retrieves all the used fields by the current filter
			$fields = $this->getSessionElement(self::SESSION_FIELDS);

			// Checks that the given selected fields are present in all the used fields by the current filter
			if (!array_diff($selectedFields, $fields))
			{
				$this->setSessionElement(self::SESSION_SELECTED_FIELDS, $selectedFields); // write changes into the session

				$sortSelectedFields = true;
			}
		}

		return $sortSelectedFields;
	}

	/**
	 * Remove a selected field from the current filter
	 */
	public function removeSelectedField($selectedField)
	{
		$removeSelectedField = false;

		// Checks the parameter selectedField
		if (!isEmptyString($selectedField))
		{
			// Retrieves all the used fields by the current filter
			$fields = $this->getSessionElement(self::SESSION_FIELDS);
			// Retrieves the selected fields by the current filter
			$selectedFields = $this->getSessionElement(self::SESSION_SELECTED_FIELDS);

			// Checks that the given selected field is present in the list of all the used fields by the current filter
			if (in_array($selectedField, $fields))
			{
				// If the selected field is present in the list of the selected fields by the current filter
				if (($pos = array_search($selectedField, $selectedFields)) !== false)
				{
					// Then remove it and shift the rest of elements by one if needed
					array_splice($selectedFields, $pos, 1);
				}

				$this->setSessionElement(self::SESSION_SELECTED_FIELDS, $selectedFields); // write changes into the session

				$removeSelectedField = true;
			}
		}

		return $removeSelectedField;
	}

	/**
	 * Add a field to the current filter
	 */
	public function addSelectedField($selectedField)
	{
		$removeSelectedField = false;

		// Checks the parameter selectedField
		if (!isEmptyString($selectedField))
		{
			// Retrieves all the used fields by the current filter
			$fields = $this->getSessionElement(self::SESSION_FIELDS);
			// Retrieves the selected fields by the current filter
			$selectedFields = $this->getSessionElement(self::SESSION_SELECTED_FIELDS);

			// Checks that the given selected field is present in the list of all the used fields by the current filter
			if (in_array($selectedField, $fields))
			{
				array_push($selectedFields, $selectedField); // place the new filed at the end of the selected fields list

				$this->setSessionElement(self::SESSION_SELECTED_FIELDS, $selectedFields); // write changes into the session

				$removeSelectedField = true;
			}
		}

		return $removeSelectedField;
	}

	/**
	 * Remove an applied filter (SQL where condition) from the current filter
	 */
	public function removeAppliedFilter($appliedFilter)
	{
		$removeAppliedFilter = false;

		// Checks the parameter appliedFilter
		if (!isEmptyString($appliedFilter))
		{
			// Retrieves all the used fields by the current filter
			$fields = $this->getSessionElement(self::SESSION_FIELDS);
			// Retrieves the applied filters by the current filter
			$filters = $this->getSessionElement(self::SESSION_FILTERS);

			// Checks that the given applied filter is present in the list of all the used fields by the current filter
			if (in_array($appliedFilter, $fields))
			{
				// Search in what position the given applied filter is
				$pos = $this->_searchFilterByName($filters, $appliedFilter);
				if ($pos !== false) // If found
				{
					array_splice($filters, $pos, 1); // Then remove it and shift the rest of elements by one if needed
				}

				 // Write changes into the session
				$this->setSessionElement(self::SESSION_FILTERS, $filters);
				$this->setSessionElement(self::SESSION_DATASET_RELOAD, true); // the dataset must be reloaded

				$removeAppliedFilter = true;
			}
		}

		return $removeAppliedFilter;
	}

	/**
	 * Apply all the applied filters (SQL where conditions) to the current filter
	 */
	public function applyFilters($appliedFilters, $appliedFiltersOperations, $appliedFiltersConditions, $appliedFiltersOptions)
	{
		$applyFilters = false;

		// Checks the required parameters: appliedFilters and appliedFiltersOperations
		if (isset($appliedFilters) && is_array($appliedFilters)
			&& isset($appliedFiltersOperations) && is_array($appliedFiltersOperations))
		{
			$fields = $this->getSessionElement(self::SESSION_FIELDS); // Retrieves all the used fields by the current filter

			// Checks that the given applied filters are present in all the used fields by the current filter
			if (!array_diff($appliedFilters, $fields))
			{
				$filters = array(); // starts building the new applied filters list
				for ($i = 0; $i < count($appliedFilters); $i++) // loops through the given applied filters
				{
					$filterDefinition = new stdClass(); // new applied filter definition

					// Sets the filter definition required properties
					$filterDefinition->name = $appliedFilters[$i];
					$filterDefinition->operation = $appliedFiltersOperations[$i];

					// Sets the filter definition optional properties
					$filterDefinition->condition = null;
					if (isset($appliedFiltersConditions) && isset($appliedFiltersConditions[$i]))
					{
						$filterDefinition->condition = $appliedFiltersConditions[$i];
					}

					$filterDefinition->option = null;
					if (isset($appliedFiltersOptions) && isset($appliedFiltersOptions[$i]))
					{
						$filterDefinition->option = $appliedFiltersOptions[$i];
					}

					$filters[$i] = $filterDefinition; // adds the new definition to the list
				}

				// Write changes into the session
				$this->setSessionElement(self::SESSION_FILTERS, $filters);
				$this->setSessionElement(self::SESSION_DATASET_RELOAD, true); // the dataset must be reloaded

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
		$this->setSessionElement(self::SESSION_DATASET_RELOAD, true);
	}

	/**
	 * Add a filter (SQL where clause) to be applied to the current filter
	 */
	public function addFilter($filter)
	{
		$addFilter = false;

		// Checks the parameter filter
		if (!isEmptyString($filter))
		{
			// Retrieves all the used fields by the current filter
			$fields = $this->getSessionElement(self::SESSION_FIELDS);
			// Retrieves the applied filters by the current filter
			$filters = $this->getSessionElement(self::SESSION_FILTERS);

			// Checks that the given applied filter is present in the list of all the used fields by the current filter
			if (in_array($filter, $fields))
			{
				// Search in what position the given applied filter is
				$pos = $this->_searchFilterByName($filters, $filter);
				if ($pos === false) // If NOT found then add it
				{
					// New filter definition
					$filterDefinition = new stdClass();
					// Sets filter definition required properties
					$filterDefinition->name = $filter;
					// Sets filter definition optional properties
					$filterDefinition->operation = null;
					$filterDefinition->condition = null;
					$filterDefinition->option = null;
					// Place the new applied filter at the end of the applied filters list
					array_push($filters, $filterDefinition);
				}

				$this->setSessionElement(self::SESSION_FILTERS, $filters); // write changes into the session

				$addFilter = true;
			}
		}

		return $addFilter;
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
			'app' => $this->getSessionElement(self::APP),
			'dataset_name' => $this->getSessionElement(self::DATASET_NAME),
			'description' => $descPGArray,
			'person_id' => $authPersonId
		));

		// New definition to be json encoded
		$jsonDeifinition = new stdClass();
		$jsonDeifinition->name = $customFilterDescription; // name of the filter

		// Generates the "column" property
		$jsonDeifinition->columns = array();
		$selectedFields = $this->getSessionElement(self::SESSION_SELECTED_FIELDS); // retrieved the selected fields
		for ($i = 0; $i < count($selectedFields); $i++)
		{
			// Each element is an object with a property called "name"
			$jsonDeifinition->columns[$i] = new stdClass();
			$jsonDeifinition->columns[$i]->name = $selectedFields[$i];
		}

		// List of applied filters
		$jsonDeifinition->filters = $this->getSessionElement(self::SESSION_FILTERS);

		// If it is already present
		if (hasData($definition))
		{
			// update it
			$this->_ci->FiltersModel->update(
				array(
					'app' => $this->getSessionElement(self::APP),
					'dataset_name' => $this->getSessionElement(self::DATASET_NAME),
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
					'app' => $this->getSessionElement(self::APP),
					'dataset_name' => $this->getSessionElement(self::DATASET_NAME),
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

	/**
	 * Return an unique string that identify this filter component
	 * NOTE: The default value is the URI where the FilterCmpt is called
	 * If the fhc_controller_id is present then is also used
	 */
	public function setFilterUniqueIdByParams($params)
	{
		if ($params != null
			&& is_array($params)
			&& isset($params[self::FILTER_UNIQUE_ID])
			&& !isEmptyString($params[self::FILTER_UNIQUE_ID]))
		{
			$filterUniqueId = $params[self::FILTER_UNIQUE_ID];
		}
		else
		{
			// Gets the current page URI
			$filterUniqueId = $this->_ci->router->directory.$this->_ci->router->class.'/'.$this->_ci->router->method;
		}

		$this->setFilterUniqueId($filterUniqueId);
	}

	/**
	 *
	 */
	public function setFilterUniqueId($filterUniqueId)
	{
		// If the FHC_CONTROLLER_ID parameter is present in the HTTP GET
		if (isset($_GET[self::FHC_CONTROLLER_ID]))
		{
			$filterUniqueId .= '/'.$this->_ci->input->get(self::FHC_CONTROLLER_ID); // then use it
		}
		elseif (isset($_POST[self::FHC_CONTROLLER_ID])) // else if the FHC_CONTROLLER_ID parameter is present in the HTTP POST
		{
			$filterUniqueId .= '/'.$this->_ci->input->post(self::FHC_CONTROLLER_ID); // then use it
		}

		$this->_filterUniqueId = $filterUniqueId;
	}

	/**
	 *
	 */
	public function setFilterType($filterType)
	{
		$this->_filterType = $filterType;
	}

	/**
	 *
	 */
	public function setFilterId($filterId)
	{
		$this->_filterId = $filterId;
	}

	/**
	 * Generates the filters menu structure array and stores it into the session
	 */
	public function generateFilterMenu($navigationPage)
	{
		$filterMenu = new stdClass();
		$filterMenu->filters = array();
		$filterMenu->personalFilters = array();

		$session = $this->getSession(); // The filter currently stored in session (the one that is currently used)
		if ($session != null)
		{
			// Loads the Filters model
			$this->_ci->load->model('system/Filters_model', 'FiltersModel');

			// Loads all the filters related to this page (same dataset_name and same app name)
			$filters = $this->_ci->FiltersModel->getFiltersByAppDatasetNamePersonId(
				$session[self::APP],
				$session[self::DATASET_NAME],
				getAuthPersonId()
			);

			// If filters were loaded
			if (hasData($filters))
			{
				$childrenArray = array(); // contains all the children elements in a menu entry
				$childrenPersonalArray = array(); // contains all the children elements in menu enty for personal filters

				// Loops through loaded filters
				foreach (getData($filters) as $filter)
				{
					$menuEntry = new stdClass();
					$menuEntry->desc = $filter->description[0];
					$menuEntry->link = sprintf(
						'%s?%s=%s',
						site_url($navigationPage),
						self::FILTER_ID,
						$filter->{self::FILTER_ID}
					);

					// If it is NOT a personal filter
					if ($filter->person_id == null)
					{
						$filterMenu->filters[] = $menuEntry;
					}
					else // otherwise
					{
						$menuEntry->subscriptDescription = 'Remove';
						$menuEntry->subscriptLinkClass = 'remove-custom-filter';
						$menuEntry->subscriptLinkValue = $filter->{self::FILTER_ID};

						$filterMenu->personalFilters[] = $menuEntry; // adds to personal filters menu array
					}
				}
			}
		}

		return $filterMenu;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Contains all the logic used to load all the data needed to the FilterCmpt
	 */
	private function _startFilterCmpt()
	{
		// Looks for expired filter components in session and drops them
		$this->dropExpiredFilterCmpts();

		// Read the all session for this filter component
		$session = $this->getSession();

		// If session is NOT empty -> a filter was already loaded
		if ($session != null)
		{
			// Retrieve the filterId stored in the session
			$sessionFilterId = $this->getSessionElement(FilterCmptLib::FILTER_ID);

			// If the filter loaded in session is NOT the same that is being requested then empty the session
			if ($this->_filterId != $sessionFilterId)
			{
				$this->setSession(null);
				$session = null;
			}
			else // else if the filter loaded in session is the same that is being requested
			{
				// Get SESSION_DATASET_RELOAD from the session
				$sessionReloadDataset = $this->getSessionElement(FilterCmptLib::SESSION_DATASET_RELOAD);

				// if Filter changed or reload is forced by parameter then reload the Dataset
				if ($this->_reloadDataset === true || $sessionReloadDataset === true)
				{
					// Set as false to stop changing the dataset
					$this->setSessionElement(FilterCmptLib::SESSION_DATASET_RELOAD, false);

					// Generate dataset query using filters from the session
					$datasetQuery = $this->generateDatasetQuery(
						$this->_query,
						$this->getSessionElement(FilterCmptLib::SESSION_FILTERS)
					);

					// Then retrieve dataset from DB
					$dataset = $this->getDataset($datasetQuery);

					// Save changes into session if data are valid
					if (!isError($dataset))
					{
						$this->_formatDataset($dataset); // marks rows using markRow and format rowns using formatRow

						// Set the new dataset and its attributes in the session
						$this->setSessionElement(FilterCmptLib::SESSION_METADATA, $this->_ci->FiltersModel->getExecutedQueryMetaData());
						$this->setSessionElement(FilterCmptLib::SESSION_ROW_NUMBER, count($dataset->retval));
						$this->setSessionElement(FilterCmptLib::SESSION_DATASET, $dataset->retval);
					}
				}
			}
		}

		// If the session is empty -> first time that this filter is loaded
		if ($session == null)
		{
			// Load filter definition data from DB
			$definition = $this->loadDefinition(
				$this->_filterId,
				$this->_app,
				$this->_datasetName,
				$this->_filterKurzbz
			);

			// Checks and parse json present into the definition
			$parsedFilterJson = $this->parseFilterJson($definition);
			if ($parsedFilterJson != null) // if the json is valid
			{
				// Generate dataset query
				$datasetQuery = $this->generateDatasetQuery($this->_query, $parsedFilterJson->filters);

				// Then retrieve dataset from DB
				$dataset = $this->getDataset($datasetQuery);

				// Try to load the name of the filter using the PhrasesLib
				$filterName = $this->getFilterName($parsedFilterJson);

				// Save changes into session if data are valid
				if (!isError($dataset))
				{
					$this->_formatDataset($dataset); // marks rows using markRow and format rowns using formatRow

					// Stores an array that contains all the data useful for
					$this->setSession(
						array(
							FilterCmptLib::FILTER_ID => $this->_filterId, // the current filter id
							FilterCmptLib::APP => $this->_app, // the current app parameter
							FilterCmptLib::DATASET_NAME => $this->_datasetName, // the carrent dataset name
							FilterCmptLib::SESSION_FILTER_NAME => $filterName, // the current filter name
							FilterCmptLib::SESSION_FIELDS => $this->_ci->FiltersModel->getExecutedQueryListFields(), // all the fields of the dataset
							FilterCmptLib::SESSION_SELECTED_FIELDS => $this->_getColumnsNames($parsedFilterJson->columns), // all the selected fields
							FilterCmptLib::SESSION_COLUMNS_ALIASES => $this->_columnsAliases, // all the fields aliases
							FilterCmptLib::SESSION_ADDITIONAL_COLUMNS => $this->_additionalColumns, // additional columns
							FilterCmptLib::SESSION_CHECKBOXES => $this->_checkboxes, // the name of the field used to build the checkboxes column
							FilterCmptLib::SESSION_FILTERS => $parsedFilterJson->filters, // all the filters used to filter the dataset
							FilterCmptLib::SESSION_METADATA => $this->_ci->FiltersModel->getExecutedQueryMetaData(), // the metadata of the dataset
							FilterCmptLib::SESSION_ROW_NUMBER => count($dataset->retval), // the number of loaded rows by this filter
							FilterCmptLib::SESSION_DATASET => $dataset->retval, // the entire dataset
							FilterCmptLib::SESSION_DATASET_RELOAD => false, // if the dataset must be reloaded, not needed the first time
							FilterCmptLib::SESSION_DATASET_REPRESENTATION => $this->_datasetRepresentation, // the choosen dataset representation
							FilterCmptLib::SESSION_DATASET_REP_OPTIONS => $this->_datasetRepresentationOptions, // the choosen dataset representation options
							FilterCmptLib::SESSION_DATASET_REP_FIELDS_DEFS => $this->_datasetRepFieldsDefs // the choosen dataset representation record fields definition
						)
					);
				}
			}
		}

		// NOTE: latest operations to be performed in the session to be shure that they are always present
		// To be always stored in the session, otherwise is not possible to load data from Filters controller
		$this->setSessionElement(FilterCmptLib::REQUIRED_PERMISSIONS, $this->_requiredPermissions);
		// Renew or set the session expiring time
		$this->setSessionElement(FilterCmptLib::SESSION_TIMEOUT, strtotime('+'.$this->_sessionTimeout.' minutes', time()));
	}

	/**
	 * Checks the required parameters used to call this FilterCmpt
	 */
	private function _checkParameters($filterCmptArray)
	{
		// If no options are given to this component...
		if (!is_array($filterCmptArray) || (is_array($filterCmptArray) && count($filterCmptArray) == 0))
		{
			show_error('Second parameter of the component call must be a NOT empty associative array');
		}
		else // ...otherwise
		{
			// Parameters (app AND dataset name) OR filter id are mandatory
			if ((!isset($filterCmptArray[FilterCmptLib::APP]) && !isset($filterCmptArray[FilterCmptLib::DATASET_NAME]))
				&& !isset($filterCmptArray[FilterCmptLib::FILTER_ID]))
			{
				show_error(
					'The parameters ("'.FilterCmptLib::APP.'" AND "'.FilterCmptLib::DATASET_NAME.') OR "'.
					FilterCmptLib::FILTER_ID.'" must be specified'
				);
			}

			// The query parameter is mandatory
			if (!isset($filterCmptArray[FilterCmptLib::QUERY]))
			{
				show_error('The parameter "'.FilterCmptLib::QUERY.'" must be specified');
			}

			// The dataset representation parameter is mandatory
			if (!isset($filterCmptArray[FilterCmptLib::DATASET_REPRESENTATION]))
			{
				show_error('The parameter "'.FilterCmptLib::DATASET_REPRESENTATION.'" must be specified');
			}

			// Checks if the dataset representation parameter is valid
			if (isset($filterCmptArray[FilterCmptLib::DATASET_REPRESENTATION])
				&& $filterCmptArray[FilterCmptLib::DATASET_REPRESENTATION] != FilterCmptLib::DATASET_REP_TABLESORTER
				&& $filterCmptArray[FilterCmptLib::DATASET_REPRESENTATION] != FilterCmptLib::DATASET_REP_PIVOTUI
				&& $filterCmptArray[FilterCmptLib::DATASET_REPRESENTATION] != FilterCmptLib::DATASET_REP_TABULATOR)
			{
				show_error(
					'The parameter "'.FilterCmptLib::DATASET_REPRESENTATION.
					'" must be IN ("'
						.FilterCmptLib::DATASET_REP_TABLESORTER.'", "'
						.FilterCmptLib::DATASET_REP_PIVOTUI.'", "'
						.FilterCmptLib::DATASET_REP_TABULATOR.'")'
				);
			}

			// If given the session timeout parameter must be a number
			if (isset($filterCmptArray[FilterCmptLib::SESSION_TIMEOUT]) && !is_numeric($filterCmptArray[FilterCmptLib::SESSION_TIMEOUT]))
			{
				show_error('The parameter "'.FilterCmptLib::SESSION_TIMEOUT.'" must be a number');
			}
		}
	}

	/**
	 * Checks parameters and initialize all the properties of this FilterCmpt
	 */
	private function _initFilterCmpt()
	{
		// Gets the filter configuration from the file system
		require_once(APPPATH.'components/filters/'.$this->_filterType.'.php');

		// Gets the filter configuration from the extensions
		// require_once(APPPATH.'components/extensions/'.$this->_filterType.'.php');

		$this->_checkParameters($filterCmptArray);

		// If here then everything is ok

		// Initialize class properties
		$this->_requiredPermissions = null;
		$this->_app = null;
		$this->_datasetName = null;
		$this->_filterKurzbz = null;
		//$this->_filterId = null;
		$this->_reloadDataset = true; // by default the dataset is NOT cached in session
		$this->_query = null;
		$this->_additionalColumns = null;
		$this->_columnsAliases = null;
		$this->_formatRow = null;
		$this->_markRow = null;
		$this->_checkboxes = null;
		$this->_hideOptions = null;
		$this->_hideSelectFields = null;
		$this->_hideSelectFilters = null;
		$this->_hideSave = null;
		$this->_hideMenu = null;
		$this->_customMenu = null;
		$this->_datasetRepresentation = null;
		$this->_datasetRepresentationOptions = null;
		$this->_datasetRepFieldsDefs = null;
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

		if (isset($filterCmptArray[FilterCmptLib::FILTER_ID]))
		{
			$this->_filterId = $filterCmptArray[FilterCmptLib::FILTER_ID];
		}

		// How to retrieve data for the filter: SQL statement or a result from DB
		if (isset($filterCmptArray[FilterCmptLib::QUERY]))
		{
			$this->_query = $filterCmptArray[FilterCmptLib::QUERY];
		}

		if (isset($filterCmptArray[FilterCmptLib::DATASET_RELOAD]))
		{
			$this->_reloadDataset = $filterCmptArray[FilterCmptLib::DATASET_RELOAD];
		}

		// Parameter is used to add extra columns to the dataset
		if (isset($filterCmptArray[FilterCmptLib::ADDITIONAL_COLUMNS])
			&& is_array($filterCmptArray[FilterCmptLib::ADDITIONAL_COLUMNS])
			&& count($filterCmptArray[FilterCmptLib::ADDITIONAL_COLUMNS]) > 0)
		{
			$this->_additionalColumns = $filterCmptArray[FilterCmptLib::ADDITIONAL_COLUMNS];
		}

		// Parameter is used to add use aliases for the columns fo the dataset
		if (isset($filterCmptArray[FilterCmptLib::COLUMNS_ALIASES])
			&& is_array($filterCmptArray[FilterCmptLib::COLUMNS_ALIASES])
			&& count($filterCmptArray[FilterCmptLib::COLUMNS_ALIASES]) > 0)
		{
			$this->_columnsAliases = $filterCmptArray[FilterCmptLib::COLUMNS_ALIASES];
		}

		// Parameter that contains a function to format the rows of the dataset
		if (isset($filterCmptArray[FilterCmptLib::FORMAT_ROW]) && is_callable($filterCmptArray[FilterCmptLib::FORMAT_ROW]))
		{
			$this->_formatRow = $filterCmptArray[FilterCmptLib::FORMAT_ROW];
		}

		// Parameter that contains a function to mark in the GUI the rows of the dataset
		if (isset($filterCmptArray[FilterCmptLib::MARK_ROW]) && is_callable($filterCmptArray[FilterCmptLib::MARK_ROW]))
		{
			$this->_markRow = $filterCmptArray[FilterCmptLib::MARK_ROW];
		}

		// Parameter used to specify the column of the dataset that will be used
		// as id of the checkboxes column in the GUI
		if (isset($filterCmptArray[FilterCmptLib::CHECKBOXES]))
		{
			$this->_checkboxes = $filterCmptArray[FilterCmptLib::CHECKBOXES];
		}

		// To specify if the filter options are shown ot not
		if (isset($filterCmptArray[FilterCmptLib::HIDE_OPTIONS]) && is_bool($filterCmptArray[FilterCmptLib::HIDE_OPTIONS]))
		{
			$this->_hideOptions = $filterCmptArray[FilterCmptLib::HIDE_OPTIONS];
		}

		// To specify if the form to select fields is shown or not
		if (isset($filterCmptArray[FilterCmptLib::HIDE_SELECT_FIELDS]) && is_bool($filterCmptArray[FilterCmptLib::HIDE_SELECT_FIELDS]))
		{
			$this->_hideSelectFields = $filterCmptArray[FilterCmptLib::HIDE_SELECT_FIELDS];
		}

		// To specify if the form to select filters is shown or not
		if (isset($filterCmptArray[FilterCmptLib::HIDE_SELECT_FILTERS]) && is_bool($filterCmptArray[FilterCmptLib::HIDE_SELECT_FILTERS]))
		{
			$this->_hideSelectFilters = $filterCmptArray[FilterCmptLib::HIDE_SELECT_FILTERS];
		}

		// To specify if the form to save a custom FilterCmpt is shown or not
		if (isset($filterCmptArray[FilterCmptLib::HIDE_SAVE]) && is_bool($filterCmptArray[FilterCmptLib::HIDE_SAVE]))
		{
			$this->_hideSave = $filterCmptArray[FilterCmptLib::HIDE_SAVE];
		}

		// If the menu should be shown or not
		if (isset($filterCmptArray[FilterCmptLib::HIDE_MENU]) && is_bool($filterCmptArray[FilterCmptLib::HIDE_MENU]))
		{
			$this->_hideMenu = $filterCmptArray[FilterCmptLib::HIDE_MENU];
		}

		// If a custom menu is set
		if (isset($filterCmptArray[FilterCmptLib::CUSTOM_MENU]) && is_bool($filterCmptArray[FilterCmptLib::CUSTOM_MENU]))
		{
			$this->_customMenu = $filterCmptArray[FilterCmptLib::CUSTOM_MENU];
		}

		// To specify how to represent the dataset (ex: tablesorter, pivotUI, ...)
		if (isset($filterCmptArray[FilterCmptLib::DATASET_REPRESENTATION])
			&& ($filterCmptArray[FilterCmptLib::DATASET_REPRESENTATION] == FilterCmptLib::DATASET_REP_TABLESORTER
			|| $filterCmptArray[FilterCmptLib::DATASET_REPRESENTATION] == FilterCmptLib::DATASET_REP_PIVOTUI
			|| $filterCmptArray[FilterCmptLib::DATASET_REPRESENTATION] == FilterCmptLib::DATASET_REP_TABULATOR))
		{
			$this->_datasetRepresentation = $filterCmptArray[FilterCmptLib::DATASET_REPRESENTATION];
		}

		// To specify options for the dataset representation (ex: tablesorter, pivotUI, ...)
		if (isset($filterCmptArray[FilterCmptLib::DATASET_REP_OPTIONS]) && !isEmptyString($filterCmptArray[FilterCmptLib::DATASET_REP_OPTIONS]))
		{
			$this->_datasetRepresentationOptions = $filterCmptArray[FilterCmptLib::DATASET_REP_OPTIONS];
		}

		// To specify how to represent each record field
		if (isset($filterCmptArray[FilterCmptLib::DATASET_REP_FIELDS_DEFS]) && !isEmptyString($filterCmptArray[FilterCmptLib::DATASET_REP_FIELDS_DEFS]))
		{
			$this->_datasetRepFieldsDefs = $filterCmptArray[FilterCmptLib::DATASET_REP_FIELDS_DEFS];
		}

		// To specify the expiring session time
		if (isset($filterCmptArray[FilterCmptLib::SESSION_TIMEOUT]) && is_numeric($filterCmptArray[FilterCmptLib::SESSION_TIMEOUT]))
		{
			$this->_sessionTimeout = $filterCmptArray[FilterCmptLib::SESSION_TIMEOUT];
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
					if (is_numeric($filterDefinition->condition)) $condition = '= '.$filterDefinition->condition;
					break;
				// not equal (!=)
				case self::OP_NOT_EQUAL:
					if (is_numeric($filterDefinition->condition)) $condition = '!= '.$filterDefinition->condition;
					break;
				// greater than (>)
				case self::OP_GREATER_THAN:
					// It it's a date type
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
					// It it's a date type
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
	 * Calls the method _markRow and _formatRow to marks rows using markRow and format rowns using formatRow
	 * NOTE: this method operates directly on the retrieved dataset: parameter passed by reference
	 */
	private function _formatDataset(&$rawDataset)
	{
		if (hasData($rawDataset) && is_array($rawDataset->retval))
		{
			// For each row of the data set
			for ($rowCounter = 0; $rowCounter < count($rawDataset->retval); $rowCounter++)
			{
				// Calls the methods to mark and to format a row
				// NOTE: keep this order! the markRow function given as parameter is supposing to work
				// on a raw dataset, NOT on a formatted one
				$rawDataset->retval[$rowCounter]->MARK_ROW_CLASS = $this->_markRow($rawDataset->retval[$rowCounter]);
				$this->_formatRow($rawDataset->retval[$rowCounter]);
			}
		}
	}

	/**
	 * Formats the columns of all the rows of the entire dataset
	 * - converts booleans into strings "true" and "false"
	 * - format dates using the format string defined in DEFAULT_DATE_FORMAT
	 * Calls the parameter formatRow if it was given and if it is a valid funtion
	 * NOTE: this method operates directly on the retrieved dataset: parameter passed by reference
	 */
	private function _formatRow(&$rawDatasetRow)
	{
		// For each column of the row
		foreach ($rawDatasetRow as $columnName => $columnValue)
		{
			// Basic conversions
			if (is_bool($columnValue))
			{
				$rawDatasetRow->{$columnName} = ($columnValue === true ? 'true' : 'false');
			}
			elseif (DateTime::createFromFormat('Y-m-d H:i:s', $columnValue) !== false)
			{
				$rawDatasetRow->{$columnName} = date(self::DEFAULT_DATE_FORMAT, strtotime($columnValue));
			}
		}

		// If a valid function call the given formatRow
		if ($this->_formatRow != null && is_callable($this->_formatRow))
		{
			$formatRowFunction = $this->_formatRow;
			$rawDatasetRow = $formatRowFunction($rawDatasetRow);
		}
	}

	/**
	 * Returns a string that contains a class name used to mark rows in the dataset table
	 * Calls the parameter markRow if it was given and if it is a valid funtion
	 */
	private function _markRow($rawDatasetRow)
	{
		// If a valid function call the given markRow
		if ($this->_markRow != null && is_callable($this->_markRow))
		{
			$markRowFunction = $this->_markRow;
			$class = $markRowFunction($rawDatasetRow);
		}

		return !isset($class) ? '' : $class;
	}

	/**
	 * Utility method that retrieves the name of the columns present in a filter JSON definition
	 */
	private function _getColumnsNames($columns)
	{
		$columnsNames = array();

		foreach ($columns as $key => $obj)
		{
			if (isset($obj->name))
			{
				$columnsNames[] = $obj->name;
			}
		}

		return $columnsNames;
	}
}

