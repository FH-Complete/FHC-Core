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

	// Alias for the dynamic table used to retrive the dataset
	const DATASET_TABLE_ALIAS = 'datasetFilterTable';

	// Parameters names...
	// ...to identify a single filter widget in the session
	const FHC_CONTROLLER_ID = 'fhc_controller_id';

	 // ...to identify a single filter widget in the DB
	const APP_PARAMETER = 'app';
	const DATASET_NAME_PARAMETER = 'datasetName';
	const FILTER_KURZBZ_PARAMETER = 'filterKurzbz';
	const FILTER_ID = 'filter_id';

	// ...stament to retrive the dataset
	const QUERY_PARAMETER = 'query';

	// ...to specify more columns or aliases for them
	const ADDITIONAL_COLUMNS = 'additionalColumns';
	const CHECKBOXES = 'checkboxes';
	const COLUMNS_ALIASES = 'columnsAliases';

	// ...to format/mark records of a dataset
	const FORMAT_RAW = 'formatRaw';
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
	 * NOTE: filterJson should be already checked using the method parseFilterJson
	 */
	public function generateDatasetQuery($query, $filterJson)
	{
		$datasetQuery = null;

		// If the given query is valid and the json of the filter is valid
		if (!empty(trim($query)) && $filterJson != null)
		{
			$definedFilters = $filterJson->filters;
			$where = '';

			for ($filtersCounter = 0; $filtersCounter < count($definedFilters); $filtersCounter++)
			{
				$filterDefinition = $definedFilters[$filtersCounter];

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

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

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
