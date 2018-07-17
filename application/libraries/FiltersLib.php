<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FilterWidget logic
 */
class FiltersLib
{
	// Session parameters names
	const SESSION_NAME = 'FHC_FILTER_WIDGET'; // Filter session name
	const SESSION_FILTER_NAME = 'filterName';
	const SESSION_FIELDS = 'fields';
	const SESSION_SELECTED_FIELDS = 'selectedFields';
	const SESSION_COLUMNS_ALIASES = 'columnsAliases';
	const SESSION_ADDITIONAL_COLUMNS = 'additionalColumns';
	const SESSION_CHECKBOXES = 'checkboxes';
	const SESSION_FILTERS = 'filters';
	const SESSION_METADATA = 'datasetMetadata';
	const SESSION_DATASET = 'dataset';
	const SESSION_ROW_NUMBER = 'rowNumber';
	const SESSION_RELOAD_DATASET = 'reloadDataset';

	// Alias for the dynamic table used to retrive the dataset
	const DATASET_TABLE_ALIAS = 'datasetFilterTable';

	// Parameters names...
	// ...to identify a single filter widget in the session
	const FHC_CONTROLLER_ID = 'fhc_controller_id';

	 // ...to identify a single filter widget in the DB
	const FILTER_ID = 'filter_id';
	const APP_PARAMETER = 'app';
	const DATASET_NAME_PARAMETER = 'datasetName';
	const FILTER_KURZBZ_PARAMETER = 'filterKurzbz';

	// ...to specify permissions that are needed to use this FilterWidget
	const REQUIRED_PERMISSIONS_PARAMETER = 'requiredPermissions';

	// ...stament to retrive the dataset
	const QUERY_PARAMETER = 'query';

	// ...to specify more columns or aliases for them
	const ADDITIONAL_COLUMNS = 'additionalColumns';
	const CHECKBOXES = 'checkboxes';
	const COLUMNS_ALIASES = 'columnsAliases';

	// ...to format/mark records of a dataset
	const FORMAT_ROW = 'formatRow';
	const MARK_ROW = 'markRow';

	// ...to hide the options for a filter
	const HIDE_HEADER = 'hideHeader';
	const HIDE_SAVE = 'hideSave';

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
	const OPT_DAYS = 'days';
	const OPT_MONTHS = 'months';

	const FILTER_PHRASES_CATEGORY = 'FilterWidget'; // The category used to store phrases for the FilterWidget

	const FILTER_PAGE_PARAM = 'filter_page'; // Filter page parameter name

	const PERMISSION_FILTER_METHOD = 'FilterWidget'; // Name for fake method to be checked by the PermissionLib
	const PERMISSION_TYPE = 'rw';

	private $_ci; // Code igniter instance
	private $_filterUniqueId; // unique id for this filter widget

	/**
	 * Gets the CI instance and loads message helper
	 */
	public function __construct($params = null)
	{
		$this->_ci =& get_instance(); // get code igniter instance

		// Loads authentication helper
		$this->_ci->load->helper('fhc_authentication'); // NOTE: needed to load custom filters do not remove!

		$this->_filterUniqueId = $this->_getFilterUniqueId($params); // sets the id for the related filter widget
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Checks if at least one of the permissions given as parameter (requiredPermissions) belongs
	 * to the authenticated user, if confirmed then is allowed to use this FilterWidget.
	 * If the parameter requiredPermissions is NOT given or is not present in the session,
	 * then NO one is allow to use this FilterWidget
	 * Wrapper method to permissionlib->hasAtLeastOne
	 */
	public function isAllowed($requiredPermissions = null)
	{
		$this->_ci->load->library('PermissionLib'); // Load permission library

		// Gets the required permissions from the session if they are not provided as parameter
		$rq = $requiredPermissions;
		if ($rq == null) $rq = $this->getElementSession(self::REQUIRED_PERMISSIONS_PARAMETER);

		return $this->_ci->permissionlib->hasAtLeastOne($rq, self::PERMISSION_FILTER_METHOD, self::PERMISSION_TYPE);
	}

	/**
	 * Wrapper method to the session helper funtions to retrive the whole session for this filter
	 */
	public function getSession()
	{
		return getElementSession(self::SESSION_NAME, $this->_filterUniqueId);
	}

	/**
	 * Wrapper method to the session helper funtions to retrive one element from the session of this filter
	 */
	public function getElementSession($name)
	{
		$session = getElementSession(self::SESSION_NAME, $this->_filterUniqueId);

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
		setElementSession(self::SESSION_NAME, $this->_filterUniqueId, $data);
	}

	/**
	 * Wrapper method to the session helper funtions to set one element in the session for this filter
	 */
	public function setElementSession($name, $value)
	{
		$session = getElementSession(self::SESSION_NAME, $this->_filterUniqueId);

		$session[$name] = $value;

		setElementSession(self::SESSION_NAME, $this->_filterUniqueId, $session); // stores the single value
	}

	/**
	 * Loads the definition data from DB for a filter widget
	 */
	public function loadDefinition($filterId, $app, $datasetName, $filterKurzbz)
	{
		// Loads the needed models
		$this->_ci->load->model('system/Filters_model', 'FiltersModel');
		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel'); // to get the default custom filter

		$this->_ci->FiltersModel->resetQuery(); // reset any previous built query

		$this->_ci->FiltersModel->addJoin('public.tbl_benutzer', 'person_id', 'LEFT'); // left join with benutzer table
		$this->_ci->FiltersModel->addSelect('system.tbl_filters.*'); // select only from table filters
		$this->_ci->FiltersModel->addOrder('sort', 'ASC'); // sort on column sort
		$this->_ci->FiltersModel->addLimit(1); // if more than one filter is set as default only one will be retrived

		$definition = null;
		$whereParameters = null; // where clause parameters

		// If we have a good filterId then use it!
		if ($filterId != null && is_numeric($filterId) && $filterId > 0)
		{
			$whereParameters = array(
				'filter_id' => $filterId
			);
		}
		else
		{
			// If we can univocally retrive a filter
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
					'uid' => getAuthUID(),
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
		if (hasData($definition) && isset($definition->retval[0]->filter) && trim($definition->retval[0]->filter) != '')
		{
			// Get the json definition of the filter
			$tmpJsonEncodedFilter = json_decode($definition->retval[0]->filter);

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
	 * Generate the query to retrive the dataset for a filter
	 */
	public function generateDatasetQuery($query, $filters)
	{
		$datasetQuery = 'SELECT * FROM ('.$query.') '.self::DATASET_TABLE_ALIAS;
		$trimedval = trim($query);

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
	 * Retrives the dataset from the DB
	 */
	public function getDataset($datasetQuery)
	{
		$dataset = null;

		if ($datasetQuery != null)
		{
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
			// Retrives all the used fields by the current filter
			$fields = $this->getElementSession(self::SESSION_FIELDS);

			// Checks that the given selected fields are present in all the used fields by the current filter
			if (!array_diff($selectedFields, $fields))
			{
				$this->setElementSession(self::SESSION_SELECTED_FIELDS, $selectedFields); // write changes into the session

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
		$trimedval = (isset($selectedField)?trim($selectedField):'');
		// Checks the parameter selectedField
		if (!isEmptyString($selectedField))
		{
			// Retrives all the used fields by the current filter
			$fields = $this->getElementSession(self::SESSION_FIELDS);
			// Retrives the selected fields by the current filter
			$selectedFields = $this->getElementSession(self::SESSION_SELECTED_FIELDS);

			// Checks that the given selected field is present in the list of all the used fields by the current filter
			if (in_array($selectedField, $fields))
			{
				// If the selected field is present in the list of the selected fields by the current filter
				if (($pos = array_search($selectedField, $selectedFields)) !== false)
				{
					// Then remove it and shift the rest of elements by one if needed
					array_splice($selectedFields, $pos, 1);
				}

				$this->setElementSession(self::SESSION_SELECTED_FIELDS, $selectedFields); // write changes into the session

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
		$trimedval = (isset($selectedField)?trim($selectedField):'');
		// Checks the parameter selectedField
		if (!isEmptyString($selectedField))
		{
			// Retrives all the used fields by the current filter
			$fields = $this->getElementSession(self::SESSION_FIELDS);
			// Retrives the selected fields by the current filter
			$selectedFields = $this->getElementSession(self::SESSION_SELECTED_FIELDS);

			// Checks that the given selected field is present in the list of all the used fields by the current filter
			if (in_array($selectedField, $fields))
			{
				array_push($selectedFields, $selectedField); // place the new filed at the end of the selected fields list

				$this->setElementSession(self::SESSION_SELECTED_FIELDS, $selectedFields); // write changes into the session

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
		$trimedval = (isset($appliedFilter)?trim($appliedFilter):'');
		// Checks the parameter appliedFilter
		if (!isEmptyString($appliedFilter))
		{
			// Retrives all the used fields by the current filter
			$fields = $this->getElementSession(self::SESSION_FIELDS);
			// Retrives the applied filters by the current filter
			$filters = $this->getElementSession(self::SESSION_FILTERS);

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
				$this->setElementSession(self::SESSION_FILTERS, $filters);
				$this->setElementSession(self::SESSION_RELOAD_DATASET, true); // the dataset must be reloaded

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
			$fields = $this->getElementSession(self::SESSION_FIELDS); // Retrives all the used fields by the current filter

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
				$this->setElementSession(self::SESSION_FILTERS, $filters);
				$this->setElementSession(self::SESSION_RELOAD_DATASET, true); // the dataset must be reloaded

				$applyFilters = true;
			}
		}

		return $applyFilters;
	}

	/**
	 * Add a filter (SQL where clause) to be applied to the current filter
	 */
	public function addFilter($filter)
	{
		$addFilter = false;
		$trimedval = (isset($filter)?trim($filter):'');
		// Checks the parameter filter
		if (!isEmptyString($filter))
		{
			// Retrives all the used fields by the current filter
			$fields = $this->getElementSession(self::SESSION_FIELDS);
			// Retrives the applied filters by the current filter
			$filters = $this->getElementSession(self::SESSION_FILTERS);

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

				$this->setElementSession(self::SESSION_FILTERS, $filters); // write changes into the session

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
		$trimedval = (isset($customFilterDescription)?trim($customFilterDescription):'');
		// Checks parameter customFilterDescription if not valid stop the execution
		if (isEmptyString($customFilterDescription))
		{
			return $saveCustomFilter;
		}

		$this->_ci->load->model('system/Filters_model', 'FiltersModel'); // to load the filter definitions
		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel'); // to get the person_id of the authenticated user

		$this->_ci->FiltersModel->resetQuery(); // reset any previous built query
		$this->_ci->BenutzerModel->resetQuery(); // reset any previous built query

		// Loads data for the authenticated user
		$authBenutzer = $this->_ci->BenutzerModel->loadWhere(array('uid' => getAuthUID()));
		if (hasData($authBenutzer)) // if data are found
		{
			// person_id of the authenticated user
			$authPersonId = $authBenutzer->retval[0]->person_id;
			// Postgres array for the description
			$descPGArray = str_replace('%desc%', $customFilterDescription, '{"%desc%", "%desc%", "%desc%", "%desc%"}');

			// Loads the definition to check if is already present in the DB
			$definition = $this->_ci->FiltersModel->loadWhere(array(
				'app' => $this->getElementSession(self::APP_PARAMETER),
				'dataset_name' => $this->getElementSession(self::DATASET_NAME_PARAMETER),
				'description' => $descPGArray,
				'person_id' => $authPersonId
			));

			// New definition to be json encoded
			$jsonDeifinition = new stdClass();
			$jsonDeifinition->name = $customFilterDescription; // name of the filter

			// Generates the "column" property
			$jsonDeifinition->columns = array();
			$selectedFields = $this->getElementSession(self::SESSION_SELECTED_FIELDS); // retrived the selected fields
			for ($i = 0; $i < count($selectedFields); $i++)
			{
				// Each element is an object with a property called "name"
				$jsonDeifinition->columns[$i] = new stdClass();
				$jsonDeifinition->columns[$i]->name = $selectedFields[$i];
			}

			// List of applied filters
			$jsonDeifinition->filters = $this->getElementSession(self::SESSION_FILTERS);

			// If it is already present
			if (hasData($definition))
			{
				// update it
				$this->_ci->FiltersModel->update(
					array(
						'app' => $this->getElementSession(self::APP_PARAMETER),
						'dataset_name' => $this->getElementSession(self::DATASET_NAME_PARAMETER),
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
						'app' => $this->getElementSession(self::APP_PARAMETER),
						'dataset_name' => $this->getElementSession(self::DATASET_NAME_PARAMETER),
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

			// delete it!
			$this->_ci->FiltersModel->delete(array('filter_id' => $filterId));

			$removeCustomFilter = true;
		}

		return $removeCustomFilter;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Return an unique string that identify this filter widget
	 * NOTE: The default value is the URI where the FilterWidget is called
	 * If the fhc_controller_id is present then is also used
	 */
	private function _getFilterUniqueId($params)
	{
		if ($params != null
			&& is_array($params)
			&& isset($params[self::FILTER_PAGE_PARAM])
			&& !isEmptyString($params[self::FILTER_PAGE_PARAM]))
		{
			$filterUniqueId = $params[self::FILTER_PAGE_PARAM];
		}
		else
		{
			// Gets the current page URI
			$filterUniqueId = $this->_ci->router->directory.$this->_ci->router->class.'/'.$this->_ci->router->method;
		}

		// If the FHC_CONTROLLER_ID parameter is present in the HTTP GET
		if (isset($_GET[self::FHC_CONTROLLER_ID]))
		{
			$filterUniqueId .= '/'.$this->_ci->input->get(self::FHC_CONTROLLER_ID); // then use it
		}
		elseif (isset($_POST[self::FHC_CONTROLLER_ID])) // else if the FHC_CONTROLLER_ID parameter is present in the HTTP POST
		{
			$filterUniqueId .= '/'.$this->_ci->input->post(self::FHC_CONTROLLER_ID); // then use it
		}

		return $filterUniqueId;
	}

	/**
	 * Generates a condition for a SQL where clause using the given applied filter definition.
	 * By default an empty string is returned.
	 */
	private function _getDatasetQueryCondition($filterDefinition)
	{
		$condition = ''; // starts building the condition

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
						&& ($filterDefinition->option == self::OPT_DAYS
						|| $filterDefinition->option == self::OPT_MONTHS))
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
						&& ($filterDefinition->option == self::OPT_DAYS
						|| $filterDefinition->option == self::OPT_MONTHS))
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
}
