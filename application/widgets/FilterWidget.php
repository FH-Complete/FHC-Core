<?php

/**
 *
 */
class FilterWidget extends Widget
{
	const APP_PARAMETER = 'app';
	const DATASET_NAME_PARAMETER = 'datasetName';
	const FILTER_KURZBZ = 'filterKurzbz';
	const FILTER_ID = 'filterId';
	const QUERY_PARAMETER = 'query';
	const DB_RESULT = 'dbResult';
	const ADDITIONAL_COLUMNS = 'additionalColumns';
	const FORMAT_RAW = 'formatRaw';
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

	const SESSION_NAME = 'FILTER';

	const SELECTED_FIELDS = 'selectedFields';
	const SELECTED_FILTERS = 'selectedFilters';
	const ACTIVE_FILTERS = 'activeFilters';
	const ACTIVE_FILTERS_OPTION = 'activeFiltersOption';
	const ACTIVE_FILTERS_OPERATION = 'activeFiltersOperation';

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

	private $app;
	private $query;
	private $datasetName;
	private $filterKurzbz;
	private $filterId;
	private $additionalColumns;
	private $formatRaw;
	private $checkboxes;
	private $columnsAliases;

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
		$filterSessionArray = $this->session->userdata(self::SESSION_NAME);

		//
		if ($this->filterId == null && isset($filterSessionArray[self::FILTER_ID]))
		{
			$this->filterId = $filterSessionArray[self::FILTER_ID];
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
		$this->_saveFilter();

		//
		$this->FiltersModel->resetQuery();

		//
		$this->dataset = @$this->FiltersModel->execReadOnlyQuery($this->_generateQuery());

		//
		$this->listFields = $this->FiltersModel->getExecutedQueryListFields();

		$filterSessionArray = $this->session->userdata(self::SESSION_NAME);
		if (isset($filterSessionArray[self::SELECTED_FIELDS]))
		{
			$selectedFields = $filterSessionArray[self::SELECTED_FIELDS];
		}

		if (count($selectedFields) == 0)
		{
			$filterSessionArray[self::SELECTED_FIELDS] = $this->listFields;
			$this->session->set_userdata(self::SESSION_NAME, $filterSessionArray);
		}

		//
		$this->metaData = $this->FiltersModel->getExecutedQueryMetaData();

		//
		$this->loadViewFilters();
	}

	/**
	 *
	 */
	public static function getSelectedFields()
	{
		return self::_getFromSession(self::SELECTED_FIELDS);
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
	public static function getAdditionalColumns()
	{
		return self::_getFromSession(self::ADDITIONAL_COLUMNS);
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
			self::_loadView(
				self::WIDGET_URL_SELECT_FIELDS,
				array(
					self::LIST_FIELDS_PARAMETER => self::$FilterWidgetInstance->listFields
				)
			);
		}
	}

	/**
	 *
	 */
	public static function loadViewSelectFilters()
	{
		if (self::$FilterWidgetInstance->hideHeader != true)
		{
			self::_loadView(
				self::WIDGET_URL_SELECT_FILTERS,
				array(
					self::LIST_FIELDS_PARAMETER => self::$FilterWidgetInstance->listFields,
					self::METADATA_PARAMETER => self::$FilterWidgetInstance->metaData
				)
			);
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
		self::_loadView(
			self::WIDGET_URL_TABLE_DATASET,
			array(
				self::LIST_FIELDS_PARAMETER => self::$FilterWidgetInstance->listFields,
				self::DATASET_PARAMETER => self::$FilterWidgetInstance->dataset
			)
		);
	}

	/**
	 *
	 */
	public static function getFilterMetaData($filter, $metaData)
	{
		$md = null;

		for ($metaDataCounter = 0; $metaDataCounter < count($metaData); $metaDataCounter++)
		{
			if ($metaData[$metaDataCounter]->name == $filter)
			{
				$md = $metaData[$metaDataCounter];
				break;
			}
		}

		return $md;
	}

	/**
	 *
	 */
	public static function renderFilterType($filterMetaData)
	{
		$html = '';
		$activeFilterValue = self::_getActiveFilterValue($filterMetaData->name);
		$activeFilterOperationValue = self::_getActiveFilterOperationValue($filterMetaData->name);
		$activeFilterOptionValue = self::_getActiveFilterOptionValue($filterMetaData->name);

		if ($filterMetaData->type == 'int4')
		{
			$html = '
				<span>
					<select name="%s" class="select-filter-operation">
						<option value="'.self::OP_EQUAL.'" '.($activeFilterOperationValue == self::OP_EQUAL ? 'selected' : '').'>equal</option>
						<option value="'.self::OP_NOT_EQUAL.'" '.($activeFilterOperationValue == self::OP_NOT_EQUAL ? 'selected' : '').'>not equal</option>
						<option value="'.self::OP_GREATER_THAN.'" '.($activeFilterOperationValue == self::OP_GREATER_THAN ? 'selected' : '').'>greater than</option>
						<option value="'.self::OP_LESS_THAN.'" '.($activeFilterOperationValue == self::OP_LESS_THAN ? 'selected' : '').'>less than</option>
					</select>
				</span>
				<span>
					<input type="number" name="%s" value="%s" class="select-filter-operation-value">
				</span>
			';
		}
		elseif ($filterMetaData->type == 'varchar')
		{
			$html = '
				<span>
					<select name="%s" class="select-filter-operation">
						<option value="'.self::OP_CONTAINS.'" '.($activeFilterOperationValue == self::OP_CONTAINS ? 'selected' : '').'>contains</option>
						<option value="'.self::OP_NOT_CONTAINS.'" '.($activeFilterOperationValue == self::OP_NOT_CONTAINS ? 'selected' : '').'>does not contain</option>
					</select>
				</span>
				<span>
					<input type="text" name="%s" value="%s" class="select-filter-operation-value">
				</span>
			';
		}
		elseif ($filterMetaData->type == 'bool')
		{
			$html = '
				<span>
					<select name="%s" class="select-filter-operation">
						<option value="'.self::OP_IS_TRUE.'" '.($activeFilterOperationValue == self::OP_IS_TRUE ? 'selected' : '').'>is true</option>
						<option value="'.self::OP_IS_FALSE.'" '.($activeFilterOperationValue == self::OP_IS_FALSE ? 'selected' : '').'>is false</option>
					</select>
				</span>
				<span>
					<input type="hidden" name="%s" value="%s">
				</span>
			';
		}
		elseif ($filterMetaData->type == 'timestamp')
		{
			$html = '
				<span>
					<select name="%s" class="select-filter-operation">
						<option value="'.self::OP_LESS_THAN.'" '.($activeFilterOperationValue == self::OP_LESS_THAN ? 'selected' : '').'>less than</option>
						<option value="'.self::OP_GREATER_THAN.'" '.($activeFilterOperationValue == self::OP_GREATER_THAN ? 'selected' : '').'>greater than</option>
						<option value="'.self::OP_SET.'" '.($activeFilterOperationValue == self::OP_SET ? 'selected' : '').'>is set</option>
						<option value="'.self::OP_NOT_SET.'" '.($activeFilterOperationValue == self::OP_NOT_SET ? 'selected' : '').'>is not set</option>
					</select>
				</span>
				<span>
					<input type="text" name="%s" value="%s" class="select-filter-operation-value">
				</span>
				<select name="%s" class="select-filter-option">
					<option value="'.self::OPT_DAYS.'" '.($activeFilterOptionValue == self::OPT_DAYS ? 'selected' : '').'>Days</option>
					<option value="'.self::OPT_MONTHS.'" '.($activeFilterOptionValue == self::OPT_MONTHS ? 'selected' : '').'>Months</option>
				</select>
			';
		}

		return sprintf($html, $filterMetaData->name.'-operation', $filterMetaData->name, $activeFilterValue, $filterMetaData->name.'-option');
	}

	/**
	 *
	 */
	public static function formatRaw($fieldName, $fieldValue, $datasetRaw)
	{
		$tmpDatasetRaw = null;

		if (is_object($datasetRaw))
		{
			$tmpDatasetRaw = clone $datasetRaw;
			$tmpMetaData = self::getFilterMetaData($fieldName, self::$FilterWidgetInstance->metaData);

			if (is_bool($fieldValue))
			{
				$tmpDatasetRaw->{$fieldName} = $fieldValue === true ? 'true' : 'false';
			}
			elseif ($tmpMetaData != null && $tmpMetaData->type == 'timestamp')
			{
				$tmpDatasetRaw->{$fieldName} = date(self::DEFAULT_DATE_FORMAT, strtotime($fieldValue));
			}

			$formatRaw = self::$FilterWidgetInstance->getFormatRaw();

			if ($formatRaw != null)
			{
				$tmpDatasetRaw = $formatRaw($fieldName, $fieldValue, $tmpDatasetRaw);
			}
		}

		return $tmpDatasetRaw;
	}

	/**
	 *
	 */
	public static function getCheckboxes()
	{
		return self::$FilterWidgetInstance->_getCheckboxes();
	}

	//------------------------------------------------------------------------------------------------------------------
	// Protected

	/**
	 *
	 */
	protected function loadViewFilters()
	{
		// Loads views
		$this->view(self::WIDGET_URL_FILTER,
			array(
				self::DATASET_PARAMETER => $this->dataset,
				self::METADATA_PARAMETER => $this->metaData,
				self::LIST_FIELDS_PARAMETER => $this->listFields
			)
		);
	}

	/**
	 *
	 */
	protected function getFormatRaw()
	{
		return $this->formatRaw;
	}

	/**
	 *
	 */
	protected function _getCheckboxes()
	{
		return $this->checkboxes;
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

		if (isset($_SESSION[self::SESSION_NAME]))
		{
			$filterSessionArray = $_SESSION[self::SESSION_NAME];
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

		$this->session->set_userdata(self::SESSION_NAME, $filterSessionArray);
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
		$this->checkboxes = null;
		$this->hideHeader = false;
		$this->hideSave = false;
		$this->columnsAliases = null;

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

			if (isset($args[self::CHECKBOXES])
				&& is_array($args[self::CHECKBOXES])
				&& count($args[self::CHECKBOXES]) > 0)
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

			$filterSessionArray = array(
				self::SELECTED_FIELDS => $selectedFields,
				self::SELECTED_FILTERS => $selectedFilters,
				self::ACTIVE_FILTERS => $activeFilters,
				self::ACTIVE_FILTERS_OPERATION => $activeFiltersOperation,
				self::ACTIVE_FILTERS_OPTION => $activeFiltersOption
			);

			$this->session->set_userdata(self::SESSION_NAME, $filterSessionArray);
		}
		else
		{
			$filterSessionArray = array(
				self::SELECTED_FIELDS => array(),
				self::SELECTED_FILTERS => array(),
				self::ACTIVE_FILTERS => array(),
				self::ACTIVE_FILTERS_OPERATION => array(),
				self::ACTIVE_FILTERS_OPTION => array()
			);

			$this->session->set_userdata(self::SESSION_NAME, $filterSessionArray);
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

			$filterSessionArray = $this->session->userdata(self::SESSION_NAME);

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

		$filterSessionArray = $this->session->userdata(self::SESSION_NAME);
		if (isset($filterSessionArray[self::SELECTED_FIELDS]))
		{
			$selectedFields = $filterSessionArray[self::SELECTED_FIELDS];
		}

		if (is_array($_POST))
		{
			if (array_key_exists(self::CMD_ADD_FIELD, $_POST) && trim($_POST[self::CMD_ADD_FIELD]) != '')
			{
				if (!in_array($_POST[self::CMD_ADD_FIELD], $selectedFields))
				{
					$selectedFields[] = $_POST[self::CMD_ADD_FIELD];
				}
			}

			if (array_key_exists(self::CMD_REMOVE_FIELD, $_POST) && trim($_POST[self::CMD_REMOVE_FIELD]) != '')
			{
				$selectedFields = $this->_removeElementFromArray($selectedFields, $_POST[self::CMD_REMOVE_FIELD]);
			}
		}

		return $selectedFields;
	}

	/**
	 *
	 */
	private function _getSelectedFiltersFromPost()
	{
		// Selected filters
		$selectedFilters = array();

		$filterSessionArray = $this->session->userdata(self::SESSION_NAME);
		if (isset($filterSessionArray[self::SELECTED_FILTERS]))
		{
			$selectedFilters = $filterSessionArray[self::SELECTED_FILTERS];
		}

		if (is_array($_POST))
		{
			if (array_key_exists(self::CMD_ADD_FILTER, $_POST) && trim($_POST[self::CMD_ADD_FILTER]) != '')
			{
				if (!in_array($_POST[self::CMD_ADD_FILTER], $selectedFilters))
				{
					$selectedFilters[] = $_POST[self::CMD_ADD_FILTER];
				}
			}

			if (array_key_exists(self::CMD_REMOVE_FILTER, $_POST) && trim($_POST[self::CMD_REMOVE_FILTER]) != '')
			{
				$selectedFilters = $this->_removeElementFromArray($selectedFilters, $_POST[self::CMD_REMOVE_FILTER]);
			}
		}

		return $selectedFilters;
	}

	/**
	 *
	 */
	private function _setActiveFiltersFromPost(&$activeFilters, &$activeFiltersOperation, &$activeFiltersOption)
	{
		$selectedFilters = array();
		$filterSessionArray = $this->session->userdata(self::SESSION_NAME);

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
		$filterSessionArray = array(
			self::SELECTED_FIELDS => $this->_getSelectedFieldsFromPost(),
			self::SELECTED_FILTERS => $this->_getSelectedFiltersFromPost(),
			self::ADDITIONAL_COLUMNS => $this->additionalColumns,
			self::COLUMNS_ALIASES => $this->columnsAliases
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

		$this->session->set_userdata(self::SESSION_NAME, $filterSessionArray);
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

		$filterSessionArray = $this->session->userdata(self::SESSION_NAME);

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
							$condition = ' ILIKE \'%'.$activeFilterValue.'%\'';
							break;
						case self::OP_NOT_CONTAINS:
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
	private function _removeElementFromArray($array, $element)
	{
		$_removeElementFromArray = array();

		for ($arrayCounter = 0; $arrayCounter < count($array); $arrayCounter++)
		{
			$arrayElement = $array[$arrayCounter];
			if ($arrayElement != $element)
			{
				$_removeElementFromArray[] = $arrayElement;
			}
		}

		return $_removeElementFromArray;
	}
}
