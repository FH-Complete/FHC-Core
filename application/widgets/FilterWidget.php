<?php

/**
 *
 */
class FilterWidget extends Widget
{
	const APP_PARAMETER = 'app';
	const DATASET_NAME_PARAMETER = 'datasetName';
	const FILTER_KURZBZ = 'filterKurzbz';
	const FILTER_ID = 'filter_id';
	const QUERY_PARAMETER = 'query';
	const DB_RESULT = 'dbResult';
	const ADDITIONAL_COLUMNS = 'additionalColumns';
	const FORMAT_RAW = 'formatRaw';
	const MARK_ROW = 'markRow';
	const CHECKBOXES = 'checkboxes';
	const HIDE_HEADER = 'hideHeader';
	const HIDE_SAVE = 'hideSave';
	const COLUMNS_ALIASES = 'columnsAliases';

	const DATASET_PARAMETER = 'dataset';
	const METADATA_PARAMETER = 'metaData';
	const LIST_FIELDS_PARAMETER = 'listFields';

	const WIDGET_URL_FILTER = 'widgets/filter/filter';
	const WIDGET_URL_SELECT_FIELDS = 'widgets/filter/selectFields';
	const WIDGET_URL_TABLE_DATASET = 'widgets/filter/tableDataset';
	const WIDGET_URL_SELECT_FILTERS = 'widgets/filter/selectFilters';
	const WIDGET_URL_SAVE_FILTER = 'widgets/filter/saveFilter';

	const SESSION_NAME = 'FHC_FILTER_WIDGET';

	const ALL_SELECTED_FIELDS = 'allSelectedFields';
	const ALL_COLUMNS_ALIASES = 'allColumnsAliases';

	const SELECTED_FIELDS = 'selectedFields';
	const SELECTED_FILTERS = 'selectedFilters';
	const ACTIVE_FILTERS = 'activeFilters';
	const ACTIVE_FILTERS_OPTION = 'activeFiltersOption';
	const ACTIVE_FILTERS_OPERATION = 'activeFiltersOperation';
	const FILTER_NAME = 'filterName';

	const ACTIVE_FILTER_OPTION_POSTFIX = '-option';
	const ACTIVE_FILTER_OPERATION_POSTFIX = '-operation';

	const CMD_ADD_FILTER = 'addFilter';
	const CMD_REMOVE_FILTER = 'rmFilter';
	const CMD_ADD_FIELD = 'addField';
	const CMD_REMOVE_FIELD = 'rmField';

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

	const OPT_DAYS = 'days';
	const OPT_MONTHS = 'months';

	const DEFAULT_DATE_FORMAT = 'd.m.Y H:i:s';

	const DEFAULT_MARK_ROW_CLASS = 'text-danger';

	private $app;
	private $query;
	private $datasetName;
	private $filterKurzbz;
	private $filterId;
	private $additionalColumns;
	private $formatRaw;
	private $markRow;
	private $checkboxes;
	private $columnsAliases;
	private $filterName;

	private $dataset;
	private $metaData;
	private $listFields;

	private static $FilterWidgetInstance;

	/**
	 *
	 */
	public function __construct($name, $args = array())
	{
		parent::__construct($name, $args); //

		$this->_initFilterWidget($args); //

		$this->_initSession(); //

		//
		$this->load->model('system/Filters_model', 'FiltersModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');

		self::$FilterWidgetInstance = $this;
	}

	/**
	 *
	 */
	public function display($widgetData)
	{
		//
		$filterSessionArray = $this->_readSession();

		//
		if ($this->filterId == null && isset($filterSessionArray[self::FILTER_ID]))
		{
			$this->filterId = $filterSessionArray[self::FILTER_ID];
		}

		//
		if ($this->filterName == null && isset($filterSessionArray[self::FILTER_NAME]))
		{
			$this->filterName = $filterSessionArray[self::FILTER_NAME];
		}

		//
		if ($filterSessionArray[self::FILTER_ID] != $this->filterId)
		{
			//
			$this->_loadFilter();
		}

		//
		$this->_setSessionFilterData();

		//
		$this->FiltersModel->resetQuery();

		//
		$this->dataset = @$this->FiltersModel->execReadOnlyQuery($this->_generateQuery());

		//
		$this->listFields = $this->FiltersModel->getExecutedQueryListFields();

		//
		$selectedFields = array();
		$filterSessionArray = $this->_readSession();
		if (isset($filterSessionArray[self::SELECTED_FIELDS]))
		{
			$selectedFields = $filterSessionArray[self::SELECTED_FIELDS];
		}

		//
		if (count($selectedFields) == 0)
		{
			$filterSessionArray[self::SELECTED_FIELDS] = $this->listFields;
		}

		//
		if ($this->columnsAliases != null && count($this->listFields) != count($this->columnsAliases))
		{
			show_error('Parameter columnsAliases does not have a number of items equal to those returned by the query');
		}

		$filterSessionArray[self::COLUMNS_ALIASES] = $this->_getColumnAliasesFromPost();
		$filterSessionArray[self::CHECKBOXES] = $this->checkboxes;

		if ($this->app != null)
		{
			$filterSessionArray[self::APP_PARAMETER] = $this->app;
		}

		if ($this->datasetName != null)
		{
			$filterSessionArray[self::DATASET_NAME_PARAMETER] = $this->datasetName;
		}

		$filterSessionArray[self::ALL_SELECTED_FIELDS] = $this->listFields;
		$filterSessionArray[self::ALL_COLUMNS_ALIASES] = $this->columnsAliases;

		/* ------------------------------------------------------------ */

		$tmpDataset = null;
		if (hasData($this->dataset))
		{
			$tmpDataset = array();

			for ($resultsCounter = 0; $resultsCounter < count($this->dataset->retval); $resultsCounter++)
			{
				$result = $this->dataset->retval[$resultsCounter];

				$class = $this->_markRow($result);
				$formattedResult = $this->_formatRaw($result);
				$formattedResult->FILTER_CLASS_MARK_ROW = $class;
				$tmpDataset[] = $formattedResult;
			}
		}

		$filterSessionArray[self::DATASET_PARAMETER] = $tmpDataset;

		/* ------------------------------------------------------------ */

		//
		$this->metaData = $this->FiltersModel->getExecutedQueryMetaData();

		$filterSessionArray[self::METADATA_PARAMETER] = $this->metaData;

		$this->_writeSession($filterSessionArray);

		//
		$this->loadViewFilters();
	}

	/**
	 *
	 */
	public static function getSelectedFilters()
	{
		return self::_getFromSession(self::SELECTED_FILTERS);
	}

	/**
	 *
	 */
	public static function getColumnsAliases()
	{
		return self::_getFromSession(self::COLUMNS_ALIASES);
	}

	/**
	 *
	 */
	public static function loadViewSelectFields()
	{
		if (self::$FilterWidgetInstance->hideHeader != true)
		{
			self::_loadView(self::WIDGET_URL_SELECT_FIELDS);
		}
	}

	/**
	 *
	 */
	public static function loadViewSelectFilters()
	{
		if (self::$FilterWidgetInstance->hideHeader != true)
		{
			self::_loadView(self::WIDGET_URL_SELECT_FILTERS);
		}
	}

	/**
	 *
	 */
	public static function loadViewSaveFilter()
	{
		if (self::$FilterWidgetInstance->hideSave != true)
		{
			self::_loadView(self::WIDGET_URL_SAVE_FILTER);
		}
	}

	/**
	 *
	 */
	public static function loadViewTableDataset()
	{
		self::_loadView(self::WIDGET_URL_TABLE_DATASET);
	}

	/**
	 *
	 */
	private function _formatRaw($datasetRaw)
	{
		$tmpDatasetRaw = clone $datasetRaw;

		foreach ($tmpDatasetRaw as $columnName => $columnValue)
		{
			if (is_bool($columnValue))
			{
				$tmpDatasetRaw->{$columnValue} = $columnValue === true ? 'true' : 'false';
			}
			elseif (DateTime::createFromFormat('Y-m-d G:i:s', $columnValue) !== false)
			{
				$tmpDatasetRaw->{$columnValue} = date(self::DEFAULT_DATE_FORMAT, strtotime($columnValue));
			}
		}

		if ($this->formatRaw != null)
		{
			$formatRaw = $this->formatRaw;
			$tmpDatasetRaw = $formatRaw($tmpDatasetRaw);
		}

		return $tmpDatasetRaw;
	}

	/**
	 *
	 */
	private function _markRow($datasetRaw)
	{
		if (is_object($datasetRaw))
		{
			if ($this->markRow != null)
			{
				$markRow = $this->markRow;
				$class = $markRow($datasetRaw);
			}
		}

		return $class == null ? '' : $class;
	}

	/**
	 *
	 */
	public static function displayFilterName()
	{
		if (self::$FilterWidgetInstance->filterName != null && self::$FilterWidgetInstance->filterName != '')
		{
			echo '<div class="filter-name-title">'.self::$FilterWidgetInstance->filterName.'</div><br>';
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Protected

	/**
	 *
	 */
	protected function loadViewFilters()
	{
		// Loads views
		$this->view(self::WIDGET_URL_FILTER);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private

	/**
	 *
	 */
	private static function _getFromSession($key)
	{
		$_getFromSession = null;
		$ci =& get_instance();
		$filterSessionArray = $ci->session->userdata(self::SESSION_NAME);

		if (isset($filterSessionArray[$key]))
		{
			$_getFromSession = $filterSessionArray[$key];
		}

		return $_getFromSession;
	}

	/**
	 *
	 */
	private static function _getActiveFilterValue($filterName)
	{
		$getActiveFilterValue = '';

		$activeFields = self::_getFromSession(self::ACTIVE_FILTERS);

		if (isset($activeFields[$filterName]))
		{
			$getActiveFilterValue = $activeFields[$filterName];
		}

		return $getActiveFilterValue;
	}

	/**
	 *
	 */
	private static function _getActiveFilterOperationValue($filterName)
	{
		$getActiveFilterOperationValue = '';

		$activeFieldsOperation = self::_getFromSession(self::ACTIVE_FILTERS_OPERATION);

		if (isset($activeFieldsOperation[$filterName]))
		{
			$getActiveFilterOperationValue = $activeFieldsOperation[$filterName];
		}

		return $getActiveFilterOperationValue;
	}

	/**
	 *
	 */
	private static function _getActiveFilterOptionValue($filterName)
	{
		$getActiveFilterOptionValue = '';

		$activeFieldsOption = self::_getFromSession(self::ACTIVE_FILTERS_OPTION);

		if (isset($activeFieldsOption[$filterName]))
		{
			$getActiveFilterOptionValue = $activeFieldsOption[$filterName];
		}

		return $getActiveFilterOptionValue;
	}

	/**
	 *
	 */
	private static function _loadView($viewName, $parameters = null)
	{
		$ci =& get_instance();
		$ci->load->view($viewName, $parameters);
	}

	/**
	 *
	 */
	private function _initSession()
	{
		$filterSessionArray = array();

		$session = $this->_readSession();

		if (count($session) > 0)
		{
			$filterSessionArray = $session;
		}

		if (!isset($filterSessionArray[self::SELECTED_FIELDS]))
		{
			$filterSessionArray[self::SELECTED_FIELDS] = array();
		}

		if (!isset($filterSessionArray[self::SELECTED_FILTERS]))
		{
			$filterSessionArray[self::SELECTED_FILTERS] = array();
		}

		if (!isset($filterSessionArray[self::ACTIVE_FILTERS]))
		{
			$filterSessionArray[self::ACTIVE_FILTERS] = array();
		}

		if (!isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION]))
		{
			$filterSessionArray[self::ACTIVE_FILTERS_OPERATION] = array();
		}

		if (!isset($filterSessionArray[self::ACTIVE_FILTERS_OPTION]))
		{
			$filterSessionArray[self::ACTIVE_FILTERS_OPTION] = array();
		}

		if (!isset($filterSessionArray[self::ADDITIONAL_COLUMNS]))
		{
			$filterSessionArray[self::ADDITIONAL_COLUMNS] = array();
		}

		if (!isset($filterSessionArray[self::FILTER_ID]))
		{
			$filterSessionArray[self::FILTER_ID] = -1;
		}

		if (!isset($filterSessionArray[self::COLUMNS_ALIASES]))
		{
			$filterSessionArray[self::COLUMNS_ALIASES] = array();
		}

		if (!isset($filterSessionArray[self::FILTER_NAME]))
		{
			$filterSessionArray[self::FILTER_NAME] = null;
		}

		if (!isset($filterSessionArray[self::APP_PARAMETER]))
		{
			$filterSessionArray[self::APP_PARAMETER] = null;
		}

		if (!isset($filterSessionArray[self::DATASET_NAME_PARAMETER]))
		{
			$filterSessionArray[self::DATASET_NAME_PARAMETER] = null;
		}

		$this->_writeSession($filterSessionArray);
	}

	/**
	 *
	 */
	private function _initFilterWidget($args)
	{
		$this->app = null;
		$this->query = null;
		$this->datasetName = null;
		$this->filterKurzbz = null;
		$this->filterId = null;
		$this->additionalColumns = null;
		$this->formatRaw = null;
		$this->markRow = null;
		$this->checkboxes = null;
		$this->hideHeader = false;
		$this->hideSave = false;
		$this->columnsAliases = null;
		$this->filterName = null;

		$this->filterUniqueId = $this->_getFilterUniqueId();

		if (!is_array($args) || (is_array($args) && count($args) == 0))
		{
			show_error('Second parameter must be a not empty associative array');
		}
		else
		{
			if ((
					!isset($args[self::APP_PARAMETER])
					&& !isset($args[self::DATASET_NAME_PARAMETER])
					&& !isset($args[self::FILTER_KURZBZ])
				)
				&& !isset($args[self::FILTER_ID]))
			{
				show_error('At least one parameters must be specified 1');
			}
			else
			{
				if (!isset($args[self::QUERY_PARAMETER]) && !isset($args[self::DB_RESULT]))
				{
					show_error('At least one parameters must be specified 2');
				}
				else
				{
					if (isset($args[self::APP_PARAMETER])
						&& isset($args[self::DATASET_NAME_PARAMETER])
						&& isset($args[self::FILTER_KURZBZ]))
					{
						$this->app = $args[self::APP_PARAMETER];
						$this->datasetName = $args[self::DATASET_NAME_PARAMETER];
						$this->filterKurzbz = $args[self::FILTER_KURZBZ];
					}
					else
					{
						$this->filterId = $args[self::FILTER_ID];
					}

					if (isset($args[self::QUERY_PARAMETER]))
					{
						$this->query = $args[self::QUERY_PARAMETER];
					}
					elseif (isset($args[self::DB_RESULT]))
					{
						$this->query = $args[self::DB_RESULT];
					}
				}
			}

			if (isset($args[self::ADDITIONAL_COLUMNS])
				&& is_array($args[self::ADDITIONAL_COLUMNS])
				&& count($args[self::ADDITIONAL_COLUMNS]) > 0)
			{
				$this->additionalColumns = $args[self::ADDITIONAL_COLUMNS];
			}

			if (isset($args[self::FORMAT_RAW]) && is_callable($args[self::FORMAT_RAW]))
			{
				$this->formatRaw = $args[self::FORMAT_RAW];
			}

			if (isset($args[self::MARK_ROW]) && is_callable($args[self::MARK_ROW]))
			{
				$this->markRow = $args[self::MARK_ROW];
			}

			if (isset($args[self::CHECKBOXES]))
			{
				$this->checkboxes = $args[self::CHECKBOXES];
			}

			if (isset($args[self::HIDE_HEADER]) && is_bool($args[self::HIDE_HEADER]))
			{
				$this->hideHeader = $args[self::HIDE_HEADER];
			}

			if (isset($args[self::HIDE_SAVE]) && is_bool($args[self::HIDE_SAVE]))
			{
				$this->hideSave = $args[self::HIDE_SAVE];
			}

			if (isset($args[self::COLUMNS_ALIASES])
				&& is_array($args[self::COLUMNS_ALIASES])
				&& count($args[self::COLUMNS_ALIASES]) > 0)
			{
				$this->columnsAliases = $args[self::COLUMNS_ALIASES];
			}
		}
	}

	/**
	 *
	 */
	private function _loadFilter()
	{
		//
		$this->FiltersModel->resetQuery();

		//
		$this->FiltersModel->addJoin('public.tbl_benutzer', 'person_id', 'LEFT');

		//
		$this->FiltersModel->addSelect('system.tbl_filters.*');

		//
		$this->FiltersModel->addOrder('sort', 'ASC');

		//
		$this->FiltersModel->addLimit(1);

		$whereParameters = null;

		if ($this->filterId == null)
		{
			$whereParameters = array(
				'app' => $this->app,
				'dataset_name' => $this->datasetName,
				'filter_kurzbz' => $this->filterKurzbz,
				'uid' => getAuthUID(),
				'default_filter' => true
			);
		}
		else
		{
			$whereParameters = array(
				'filter_id' => $this->filterId
			);
		}

		//
		$filter = $this->FiltersModel->loadWhere($whereParameters);

		$jsonEncodedFilter = null;

		if (hasData($filter))
		{
			if (isset($filter->retval[0]->filter) && trim($filter->retval[0]->filter) != '')
			{
				$jsonEncodedFilter = json_decode($filter->retval[0]->filter);
			}
		}

		if ($jsonEncodedFilter != null)
		{
			$selectedFields = array();
			$selectedFilters = array();
			$activeFilters = array();
			$activeFiltersOperation = array();
			$activeFiltersOption = array();
			$filterName = null;

			if (isset($jsonEncodedFilter->columns))
			{
				$columns = $jsonEncodedFilter->columns;

				for($columnsCounter = 0; $columnsCounter < count($columns); $columnsCounter++)
				{
					if (isset($columns[$columnsCounter]->name))
					{
						$selectedFields[] = $columns[$columnsCounter]->name;
					}
				}
			}

			if (isset($jsonEncodedFilter->filters))
			{
				$filters = $jsonEncodedFilter->filters;

				for($filtersCounter = 0; $filtersCounter < count($filters); $filtersCounter++)
				{
					if (isset($filters[$filtersCounter]->name))
					{
						$selectedFilters[] = $filters[$filtersCounter]->name;
						$activeFilters[$filters[$filtersCounter]->name] = $filters[$filtersCounter]->condition;
						$activeFiltersOperation[$filters[$filtersCounter]->name] = $filters[$filtersCounter]->operation;
						if (isset($filters[$filtersCounter]->option))
						{
							$activeFiltersOption[$filters[$filtersCounter]->name] = $filters[$filtersCounter]->option;
						}
					}
				}
			}

			if (isset($jsonEncodedFilter->name))
			{
				$filterName = $jsonEncodedFilter->name;
			}

			$this->filterName = $filterName;
			$this->app = $filter->retval[0]->app;

			$filterSessionArray = array(
				self::SELECTED_FIELDS => $selectedFields,
				self::SELECTED_FILTERS => $selectedFilters,
				self::ACTIVE_FILTERS => $activeFilters,
				self::ACTIVE_FILTERS_OPERATION => $activeFiltersOperation,
				self::ACTIVE_FILTERS_OPTION => $activeFiltersOption,
				self::FILTER_NAME => $filterName,
				self::APP_PARAMETER => $filter->retval[0]->app,
				self::DATASET_NAME_PARAMETER => $filter->retval[0]->dataset_name
			);

			$this->_writeSession($filterSessionArray);
		}
		else
		{
			$filterSessionArray = array(
				self::SELECTED_FIELDS => array(),
				self::SELECTED_FILTERS => array(),
				self::ACTIVE_FILTERS => array(),
				self::ACTIVE_FILTERS_OPERATION => array(),
				self::ACTIVE_FILTERS_OPTION => array(),
				self::FILTER_NAME => null,
				self::APP_PARAMETER => null,
				self::DATASET_NAME_PARAMETER => null
			);

			$this->_writeSession($filterSessionArray);
		}
	}

	/**
	 *
	 */
	private function _saveFilter()
	{
		if (isset($_POST['saveCustomFilter']) && $_POST['saveCustomFilter'] == 'true')
		{
			$objToBeSaved = new stdClass();

			$filterSessionArray = $this->_readSession();

			if (isset($filterSessionArray[self::SELECTED_FIELDS]))
			{
				$selectedFields = $filterSessionArray[self::SELECTED_FIELDS];
				$objToBeSaved->columns = array();

				for ($selectedFieldsCounter = 0; $selectedFieldsCounter < count($selectedFields); $selectedFieldsCounter++)
				{
					$objToBeSaved->columns[$selectedFieldsCounter] = new stdClass();
					$objToBeSaved->columns[$selectedFieldsCounter]->name = $selectedFields[$selectedFieldsCounter];
				}
			}

			if (isset($filterSessionArray[self::SELECTED_FILTERS]))
			{
				$selectedFilters = $filterSessionArray[self::SELECTED_FILTERS];
				$objToBeSaved->filters = array();

				for ($selectedFiltersCounter = 0; $selectedFiltersCounter < count($selectedFilters); $selectedFiltersCounter++)
				{
					$objToBeSaved->filters[$selectedFiltersCounter] = new stdClass();
					$objToBeSaved->filters[$selectedFiltersCounter]->name = $selectedFilters[$selectedFiltersCounter];

					if (isset($filterSessionArray[self::ACTIVE_FILTERS])
						&& isset($filterSessionArray[self::ACTIVE_FILTERS][$selectedFilters[$selectedFiltersCounter]]))
					{
						$objToBeSaved->filters[$selectedFiltersCounter]->condition = $filterSessionArray[self::ACTIVE_FILTERS][$selectedFilters[$selectedFiltersCounter]];
					}

					if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION])
						&& isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION][$selectedFilters[$selectedFiltersCounter]]))
					{
						$objToBeSaved->filters[$selectedFiltersCounter]->operation = $filterSessionArray[self::ACTIVE_FILTERS_OPERATION][$selectedFilters[$selectedFiltersCounter]];
					}

					if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPTION])
						&& isset($filterSessionArray[self::ACTIVE_FILTERS_OPTION][$selectedFilters[$selectedFiltersCounter]]))
					{
						$objToBeSaved->filters[$selectedFiltersCounter]->option = $filterSessionArray[self::ACTIVE_FILTERS_OPTION][$selectedFilters[$selectedFiltersCounter]];
					}
				}
			}

			$desc = $_POST['customFilterDescription'];
			$descPGArray = '{"'.$desc.'", "'.$desc.'", "'.$desc.'", "'.$desc.'"}';

			$resultBenutzer = $this->BenutzerModel->load(getAuthUID());
			$personId = $resultBenutzer->retval[0]->person_id;

			$result = $this->FiltersModel->loadWhere(array(
				'app' => $this->app,
				'dataset_name' => $this->datasetName,
				'description' => $descPGArray,
				'person_id' => $personId
			));

			if (hasData($result))
			{
				$this->FiltersModel->update(
					array(
						'app' => $this->app,
						'dataset_name' => $this->datasetName,
						'description' => $descPGArray,
						'person_id' => $personId
					),
					array(
						'filter' => json_encode($objToBeSaved)
					)
				);
			}
			else
			{
				$this->FiltersModel->insert(array(
					'app' => $this->app,
					'dataset_name' => $this->datasetName,
					'filter_kurzbz' => uniqid($personId, true),
					'person_id' => $personId,
					'description' => $descPGArray,
					'sort' => null,
					'default_filter' => false,
					'filter' => json_encode($objToBeSaved),
					'oe_kurzbz' => null
				));
			}
		}
	}

	/**
	 *
	 */
	private function _getSelectedFieldsFromPost()
	{
		// Selected fields
		$selectedFields = array();

		$filterSessionArray = $this->_readSession();
		if (isset($filterSessionArray[self::SELECTED_FIELDS]))
		{
			$selectedFields = $filterSessionArray[self::SELECTED_FIELDS];
		}

		return $selectedFields;
	}

	/**
	 *
	 */
	private function _getAppFromPost()
	{
		$app = $this->app;

		$filterSessionArray = $this->_readSession();
		if (isset($filterSessionArray[self::APP_PARAMETER]))
		{
			$app = $filterSessionArray[self::APP_PARAMETER];
		}

		return $app;
	}

	/**
	 *
	 */
	private function _getDatasetFromPost()
	{
		$datasetName = $this->datasetName;

		$filterSessionArray = $this->_readSession();
		if (isset($filterSessionArray[self::DATASET_NAME_PARAMETER]))
		{
			$datasetName = $filterSessionArray[self::DATASET_NAME_PARAMETER];
		}

		return $datasetName;
	}

	/**
	 *
	 */
	private function _getColumnAliasesFromPost()
	{
		$columnsAliases = $this->columnsAliases;
		$selectedFields = array();

		$filterSessionArray = $this->_readSession();

		if (isset($filterSessionArray[self::SELECTED_FIELDS]))
		{
			$selectedFields = $filterSessionArray[self::SELECTED_FIELDS];
		}

		if (isset($this->listFields) && count($selectedFields) > 0 && is_array($this->columnsAliases) && count($this->columnsAliases) > 0)
		{
			$columnsAliases = array();

			for ($i = 0; $i < count($selectedFields); $i++)
			{
				if (($pos = array_search($selectedFields[$i], $this->listFields)) !== false)
				{
					$columnsAliases[] = $this->columnsAliases[$pos];
				}
			}
		}

		return $columnsAliases;
	}

	/**
	 *
	 */
	private function _getSelectedFiltersFromPost()
	{
		// Selected filters
		$selectedFilters = array();

		$filterSessionArray = $this->_readSession();
		if (isset($filterSessionArray[self::SELECTED_FILTERS]))
		{
			$selectedFilters = $filterSessionArray[self::SELECTED_FILTERS];
		}

		return $selectedFilters;
	}

	/**
	 *
	 */
	private function _setActiveFiltersFromPost(&$activeFilters, &$activeFiltersOperation, &$activeFiltersOption)
	{
		$selectedFilters = array();
		$filterSessionArray = $this->_readSession();

		if (isset($filterSessionArray[self::ACTIVE_FILTERS]))
		{
			$activeFilters = $filterSessionArray[self::ACTIVE_FILTERS];
		}

		if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION]))
		{
			$activeFiltersOperation = $filterSessionArray[self::ACTIVE_FILTERS_OPERATION];
		}

		if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPTION]))
		{
			$activeFiltersOption = $filterSessionArray[self::ACTIVE_FILTERS_OPTION];
		}

		if (isset($filterSessionArray[self::SELECTED_FILTERS]))
		{
			$selectedFilters = $filterSessionArray[self::SELECTED_FILTERS];
		}

		if (is_array($_POST))
		{
			if (array_key_exists(self::CMD_REMOVE_FILTER, $_POST) && trim($_POST[self::CMD_REMOVE_FILTER]) != '')
			{
				if (isset($activeFilters[$_POST[self::CMD_REMOVE_FILTER]]))
				{
					unset($activeFilters[$_POST[self::CMD_REMOVE_FILTER]]);
				}

				if (isset($activeFiltersOperation[$_POST[self::CMD_REMOVE_FILTER]]))
				{
					unset($activeFiltersOperation[$_POST[self::CMD_REMOVE_FILTER]]);
				}

				if (isset($activeFiltersOption[$_POST[self::CMD_REMOVE_FILTER]]))
				{
					unset($activeFiltersOption[$_POST[self::CMD_REMOVE_FILTER]]);
				}
			}
			else
			{
				for ($selectedFiltersCounter = 0; $selectedFiltersCounter < count($selectedFilters); $selectedFiltersCounter++)
				{
					$selectedFilter = $selectedFilters[$selectedFiltersCounter];

					if (isset($_POST[$selectedFilter]))
					{
						$activeFilters[$selectedFilter] = $_POST[$selectedFilter];
					}

					if (isset($_POST[$selectedFilter.self::ACTIVE_FILTER_OPERATION_POSTFIX]))
					{
						$activeFiltersOperation[$selectedFilter] = $_POST[$selectedFilter.self::ACTIVE_FILTER_OPERATION_POSTFIX];
					}

					if (isset($_POST[$selectedFilter.self::ACTIVE_FILTER_OPTION_POSTFIX]))
					{
						$activeFiltersOption[$selectedFilter] = $_POST[$selectedFilter.self::ACTIVE_FILTER_OPTION_POSTFIX];
					}
				}
			}
		}
	}

	/**
	 *
	 */
	private function _setSessionFilterData()
	{
		$session = $this->_readSession();

		$filterSessionArray = array(
			self::SELECTED_FIELDS => $this->_getSelectedFieldsFromPost(),
			self::SELECTED_FILTERS => $this->_getSelectedFiltersFromPost(),
			self::ADDITIONAL_COLUMNS => $this->additionalColumns,
			self::COLUMNS_ALIASES => $this->_getColumnAliasesFromPost(),
			self::APP_PARAMETER => $this->_getAppFromPost(),
			self::DATASET_NAME_PARAMETER => $this->_getDatasetFromPost()
		);

		$filterSessionArray[self::ACTIVE_FILTERS] = array();
		$filterSessionArray[self::ACTIVE_FILTERS_OPERATION] = array();
		$filterSessionArray[self::ACTIVE_FILTERS_OPTION] = array();

		$this->_setActiveFiltersFromPost(
			$filterSessionArray[self::ACTIVE_FILTERS],
			$filterSessionArray[self::ACTIVE_FILTERS_OPERATION],
			$filterSessionArray[self::ACTIVE_FILTERS_OPTION]
		);

		$filterSessionArray[self::FILTER_ID] = $this->filterId;
		$filterSessionArray[self::FILTER_NAME] = $this->filterName;

		$this->_writeSession(array_merge($session, $filterSessionArray));
	}

	/**
	 *
	 */
	private function _generateQuery()
	{
		$query = $this->query;

		$activeFilters = array();
		$activeFiltersOperation = array();
		$activeFiltersOption = array();

		$filterSessionArray = $this->_readSession();

		if (isset($filterSessionArray[self::ACTIVE_FILTERS]))
		{
			$activeFilters = $filterSessionArray[self::ACTIVE_FILTERS];
		}

		if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION]))
		{
			$activeFiltersOperation = $filterSessionArray[self::ACTIVE_FILTERS_OPERATION];
		}

		if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPTION]))
		{
			$activeFiltersOption = $filterSessionArray[self::ACTIVE_FILTERS_OPTION];
		}

		//
		if (count($activeFilters) > 0)
		{
			$where = '';
			$first = true;

			foreach ($activeFilters as $field => $activeFilterValue)
			{
				if ($first)
				{
					$first = false;
				}
				else
				{
					$where .= ' AND ';
				}

				if (isset($activeFiltersOperation[$field]))
				{
					$where .= '"'.$field.'"';
					$condition = '';

					switch ($activeFiltersOperation[$field])
					{
						case self::OP_EQUAL:
							if (!is_numeric($activeFilterValue)) $activeFilterValue = 0;
							$condition = ' = '.$activeFilterValue;
							break;
						case self::OP_NOT_EQUAL:
							if (!is_numeric($activeFilterValue)) $activeFilterValue = 0;
							$condition = ' != '.$activeFilterValue;
							break;
						case self::OP_GREATER_THAN:
							if (!is_numeric($activeFilterValue)) $activeFilterValue = 0;
							if (isset($activeFiltersOption[$field])
								&& ($activeFiltersOption[$field] == self::OPT_DAYS
								|| $activeFiltersOption[$field] == self::OPT_MONTHS))
							{
								$condition = ' < (NOW() - \''.$activeFilterValue.' '.$activeFiltersOption[$field].'\'::interval)';
							}
							else
							{
								$condition = ' > '.$activeFilterValue;
							}

							break;
						case self::OP_LESS_THAN:
							if (!is_numeric($activeFilterValue)) $activeFilterValue = 0;
							if (isset($activeFiltersOption[$field])
								&& ($activeFiltersOption[$field] == self::OPT_DAYS
								|| $activeFiltersOption[$field] == self::OPT_MONTHS))
							{
								$condition = ' > (NOW() - \''.$activeFilterValue.' '.$activeFiltersOption[$field].'\'::interval)';
							}
							else
							{
								$condition = ' < '.$activeFilterValue;
							}
							break;
						case self::OP_CONTAINS:
							$activeFilterValue = $this->FiltersModel->escapeLike($activeFilterValue); // escapes
							$condition = ' ILIKE \'%'.$activeFilterValue.'%\'';
							break;
						case self::OP_NOT_CONTAINS:
							$activeFilterValue = $this->FiltersModel->escapeLike($activeFilterValue); // escapes
							$condition = ' NOT ILIKE \'%'.$activeFilterValue.'%\'';
							break;
						case self::OP_IS_TRUE:
							$condition = ' IS TRUE';
							break;
						case self::OP_IS_FALSE:
							$condition = ' IS FALSE';
							break;
						case self::OP_SET:
							$condition = ' IS NOT NULL';
							break;
						case self::OP_NOT_SET:
							$condition = ' IS NULL';
							break;
						default:
							$condition = ' IS NOT NULL';
							break;
					}

					$where .= $condition;
				}
			}

			if ($where != '')
			{
				$query = 'SELECT * FROM ('.$this->query.') tableFilters WHERE '.$where;
			}
		}

		return $query;
	}

	/**
	 *
	 */
	private function _readSession()
	{
		if (isset($_SESSION[self::SESSION_NAME]) && isset($_SESSION[self::SESSION_NAME][$this->filterUniqueId]))
			return $_SESSION[self::SESSION_NAME][$this->filterUniqueId];

		return array();
	}

	/**
	 *
	 */
	private function _writeSession($data)
	{
		if (!isset($_SESSION[self::SESSION_NAME])
			|| (isset($_SESSION[self::SESSION_NAME]) && !is_array($_SESSION[self::SESSION_NAME])))
		{
			$_SESSION[self::SESSION_NAME] = array();
		}

		$_SESSION[self::SESSION_NAME][$this->filterUniqueId] = $data;
	}

	/**
	 *
	 */
	private function _getFilterUniqueId()
	{
		return $this->router->directory.$this->router->class.'/'.$this->router->method.'/'.$this->input->get('fhc_controller_id');
	}
}
