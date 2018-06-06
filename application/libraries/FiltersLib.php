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
	const SESSION_DATASET_METADATA = 'datasetMetadata';
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

	const FILTER_PAGE_PARAM = 'filter_page'; // Filter page parameter used to overwrite the page URI

	private $_ci; // Code igniter instance
	private $_filterUniqueId; // unique id for this filter widget

	/**
	 * Gets the CI instance and loads message helper
	 */
	public function __construct($params = null)
	{
		$this->_ci =& get_instance();

		// Loads helper message to manage returning messages
		$this->_ci->load->helper('message');

		$this->_filterUniqueId = $this->_getFilterUniqueId($params); // sets the id for the related filter widget
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Returns the whole session for this filter widget if found, otherwise null
	 */
	public function getSession()
	{
		$session = null;

		// If it is present a session for this filter
		if (isset($_SESSION[self::SESSION_NAME]) && isset($_SESSION[self::SESSION_NAME][$this->_filterUniqueId]))
		{
			$session = $_SESSION[self::SESSION_NAME][$this->_filterUniqueId];
		}

		return $session;
	}

	/**
	 * Returns one element from the session of this filter widget, otherwise null
	 */
	public function getElementSession($name)
	{
		$session = $this->getSession(); // get the whole session for this filter

		if (isset($session[$name]))
		{
			return $session[$name];
		}

		return null;
	}

	/**
	 * Sets the whole session for this filter widget
	 */
	public function setSession($data)
	{
		// If is NOT already present into the session
		if (!isset($_SESSION[self::SESSION_NAME])
			|| (isset($_SESSION[self::SESSION_NAME]) && !is_array($_SESSION[self::SESSION_NAME])))
		{
			$_SESSION[self::SESSION_NAME] = array(); // then create it
		}

		$_SESSION[self::SESSION_NAME][$this->_filterUniqueId] = $data; // stores data
	}

	/**
	 * Sets one element of the session of this filter widget
	 */
	public function setElementSession($name, $value)
	{
		// If is NOT already present into the session
		if (!isset($_SESSION[self::SESSION_NAME])
			|| (isset($_SESSION[self::SESSION_NAME]) && !is_array($_SESSION[self::SESSION_NAME])))
		{
			$_SESSION[self::SESSION_NAME] = array(); // then create it
		}

		// If the session for this filter is NOT already present into the session
		if (!isset($_SESSION[self::SESSION_NAME][$this->_filterUniqueId])
			|| (isset($_SESSION[self::SESSION_NAME][$this->_filterUniqueId])
			&& !is_array($_SESSION[self::SESSION_NAME][$this->_filterUniqueId])))
		{
			$_SESSION[self::SESSION_NAME][$this->_filterUniqueId] = array(); // then create it
		}

		$_SESSION[self::SESSION_NAME][$this->_filterUniqueId][$name] = $value; // stores the single value
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
		$datasetQuery = null;

		// If the given query is valid and the json of the filter is valid
		if (!empty(trim($query)) && $filters != null && is_array($filters))
		{
			$where = '';

			for ($filtersCounter = 0; $filtersCounter < count($filters); $filtersCounter++)
			{
				$filterDefinition = $filters[$filtersCounter];

				if ($filtersCounter > 0) $where .= ' AND ';

				if (!empty(trim($filterDefinition->name)))
				{
					$where .= '"'.$filterDefinition->name.'"'.$this->_getDatasetQueryCondition($filterDefinition);
				}
			}

			if ($where != '')
			{
				$datasetQuery = 'SELECT * FROM ('.$query.') '.self::DATASET_TABLE_ALIAS.' WHERE '.$where;
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

		// Filter name from phrases system
		if (isset($filterJson->namePhrase) && !empty(trim($filterJson->namePhrase)))
		{
			// Loads the library to use the phrases system
			$this->_ci->load->library('PhrasesLib', array(self::FILTER_PHRASES_CATEGORY));

			$tmpFilterNamePhrase = $this->_ci->phraseslib->t(self::FILTER_PHRASES_CATEGORY, $filterJson->namePhrase);
			if (isset($tmpFilterNamePhrase) && !empty(trim($tmpFilterNamePhrase))) // if is not null or an empty string
			{
				$filterName = $tmpFilterNamePhrase;
			}
		}

		return $filterName;
	}

	/**
	 *
	 */
	public function sortSelectedFields($selectedFields)
	{
		$sortSelectedFields = false;

		if (isset($selectedFields) && is_array($selectedFields) && count($selectedFields) > 0)
		{
			$fields = $this->getElementSession(self::SESSION_FIELDS);

			if (!array_diff($selectedFields, $fields))
			{
				$this->setElementSession(self::SESSION_SELECTED_FIELDS, $selectedFields);

				$sortSelectedFields = true;
			}
		}

		return $sortSelectedFields;
	}

	/**
	 *
	 */
	public function removeSelectedField($selectedField)
	{
		$removeSelectedField = false;

		if (isset($selectedField) && !empty(trim($selectedField)))
		{
			$fields = $this->getElementSession(self::SESSION_FIELDS);
			$selectedFields = $this->getElementSession(self::SESSION_SELECTED_FIELDS);

			if (in_array($selectedField, $fields))
			{
				if (($pos = array_search($selectedField, $selectedFields)) !== false)
				{
					array_splice($selectedFields, $pos, 1);
				}

				$this->setElementSession(self::SESSION_SELECTED_FIELDS, $selectedFields);

				$removeSelectedField = true;
			}
		}

		return $removeSelectedField;
	}

	/**
	 *
	 */
	public function addSelectedField($selectedField)
	{
		$removeSelectedField = false;

		if (isset($selectedField) && !empty(trim($selectedField)))
		{
			$fields = $this->getElementSession(self::SESSION_FIELDS);
			$selectedFields = $this->getElementSession(self::SESSION_SELECTED_FIELDS);

			if (in_array($selectedField, $fields))
			{
				array_push($selectedFields, $selectedField);

				$this->setElementSession(self::SESSION_SELECTED_FIELDS, $selectedFields);

				$removeSelectedField = true;
			}
		}

		return $removeSelectedField;
	}

	/**
	 *
	 */
	public function removeAppliedFilter($appliedFilter)
	{
		$removeAppliedFilter = false;

		if (isset($appliedFilter) && !empty(trim($appliedFilter)))
		{
			$fields = $this->getElementSession(self::SESSION_FIELDS);
			$filters = $this->getElementSession(self::SESSION_FILTERS);

			if (in_array($appliedFilter, $fields))
			{
				$pos = false;
				for($i = 0; $i < count($filters); $i++)
				{
					if ($filters[$i]->name == $appliedFilter)
					{
						$pos = $i;
						break;
					}
				}

				if ($pos !== false)
				{
					array_splice($filters, $pos, 1);
				}

				$this->setElementSession(self::SESSION_FILTERS, $filters);
				$this->setElementSession(self::SESSION_RELOAD_DATASET, true);

				$removeAppliedFilter = true;
			}
		}

		return $removeAppliedFilter;
	}

	/**
	 *
	 */
	public function applyFilters($appliedFilters, $appliedFiltersOperations, $appliedFiltersConditions, $appliedFiltersOptions)
	{
		$applyFilters = false;

		if (isset($appliedFilters) && is_array($appliedFilters)
			&& isset($appliedFiltersOperations) && is_array($appliedFiltersOperations))
		{
			$fields = $this->getElementSession(self::SESSION_FIELDS);

			if (!array_diff($appliedFilters, $fields))
			{
				$filters = array();
				for ($i = 0; $i < count($appliedFilters); $i++)
				{
					$filterDefinition = new stdClass();

					$filterDefinition->name = $appliedFilters[$i];
					$filterDefinition->operation = $appliedFiltersOperations[$i];

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

					$filters[$i] = $filterDefinition;
				}

				$this->setElementSession(self::SESSION_FILTERS, $filters);
				$this->setElementSession(self::SESSION_RELOAD_DATASET, true);

				$applyFilters = true;
			}
		}

		return $applyFilters;
	}

	/**
	 *
	 */
	public function addFilter($filter)
	{
		$addFilter = false;

		if (isset($filter) && !empty(trim($filter)))
		{
			$fields = $this->getElementSession(self::SESSION_FIELDS);
			$filters = $this->getElementSession(self::SESSION_FILTERS);

			if (in_array($filter, $fields))
			{
				$pos = false;
				for($i = 0; $i < count($filters); $i++)
				{
					if ($filters[$i]->name == $filter)
					{
						$pos = $i;
						break;
					}
				}

				if ($pos === false)
				{
					$filterDefinition = new stdClass();
					$filterDefinition->name = $filter;
					$filterDefinition->operation = null;
					$filterDefinition->condition = null;
					$filterDefinition->option = null;
					array_push($filters, $filterDefinition);
				}

				$this->setElementSession(self::SESSION_FILTERS, $filters);

				$addFilter = true;
			}
		}

		return $addFilter;
	}

	/**
	 *
	 */
	public function saveCustomFilter($customFilterDescription)
	{
		$saveCustomFilter = false; // by default returns a failure

		// Checks parameter customFilterDescription
		if (!isset($customFilterDescription) || empty(trim($customFilterDescription)))
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
	 *
	 */
	public function removeCustomFilter($filterId)
	{
		$removeCustomFilter = false;

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
		//
		if ($params != null
			&& is_array($params)
			&& isset($params[self::FILTER_PAGE_PARAM])
			&& !empty(trim($params[self::FILTER_PAGE_PARAM])))
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
	 *
	 */
	private function _getDatasetQueryCondition($filterDefinition)
	{
		$condition = '';

		if (!empty(trim($filterDefinition->operation)))
		{
			switch ($filterDefinition->operation)
			{
				case self::OP_EQUAL:
					if (is_numeric($filterDefinition->condition)) $condition = '= '.$filterDefinition->condition;
					break;
				case self::OP_NOT_EQUAL:
					if (is_numeric($filterDefinition->condition)) $condition = '!= '.$filterDefinition->condition;
					break;
				case self::OP_GREATER_THAN:
					if (is_numeric($filterDefinition->condition)
						&& isset($filterDefinition->option)
						&& ($filterDefinition->option == self::OPT_DAYS
						|| $filterDefinition->option == self::OPT_MONTHS))
					{
						$condition = '< (NOW() - \''.$filterDefinition->condition.' '.$filterDefinition->option.'\'::interval)';
					}
					else
					{
						$condition = '> '.$filterDefinition->condition;
					}
					break;
				case self::OP_LESS_THAN:
					if (is_numeric($filterDefinition->condition)
						&& isset($filterDefinition->option)
						&& ($filterDefinition->option == self::OPT_DAYS
						|| $filterDefinition->option == self::OPT_MONTHS))
					{
						$condition = '> (NOW() - \''.$filterDefinition->condition.' '.$filterDefinition->option.'\'::interval)';
					}
					else
					{
						$condition = '< '.$filterDefinition->condition;
					}
					break;
				case self::OP_CONTAINS:
					$condition = 'ILIKE \'%'.$this->_ci->FiltersModel->escapeLike($filterDefinition->condition).'%\'';
					break;
				case self::OP_NOT_CONTAINS:
					$condition = 'NOT ILIKE \'%'.$this->_ci->FiltersModel->escapeLike($filterDefinition->condition).'%\'';
					break;
				case self::OP_IS_TRUE:
					$condition = 'IS TRUE';
					break;
				case self::OP_IS_FALSE:
					$condition = 'IS FALSE';
					break;
				case self::OP_SET:
					$condition = 'IS NOT NULL';
					break;
				case self::OP_NOT_SET:
					$condition = 'IS NULL';
					break;
				default:
					$condition = 'IS NOT NULL';
					break;
			}
		}

		if (!empty(trim($condition))) $condition = ' '.$condition;

		return $condition;
	}
}
