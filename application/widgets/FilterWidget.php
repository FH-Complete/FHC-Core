<?php

/**
 *
 */
class FilterWidget extends Widget
{
	const APP_PARAMETER = 'app';
	const QUERY_PARAMETER = 'query';
	const DATASET_NAME_PARAMETER = 'datasetName';

	const DATASET_PARAMETER = 'dataset';
	const METADATA_PARAMETER = 'metaData';
	const LIST_FIELDS_PARAMETER = 'listFields';

	const WIDGET_URL_FILTER = 'widgets/filter/filter';
	const WIDGET_URL_SELECT_FIELDS = 'widgets/filter/selectFields';
	const WIDGET_URL_TABLE_DATASET = 'widgets/filter/tableDataset';
	const WIDGET_URL_SELECT_FILTERS = 'widgets/filter/selectFilters';

	const SESSION_NAME = 'FILTER';

	const SELECTED_FIELDS = 'selectedFields';
	const SELECTED_FILTERS = 'selectedFilters';
	const ACTIVE_FILTERS = 'activeFilters';
	const ACTIVE_FILTERS_OPERATION = 'activeFiltersOperation';

	const ACTIVE_FILTER_OPERATION_POSTFIX = '-operation';

	const CMD_ADD_FILTER = 'addFilter';
	const CMD_REMOVE_FILTER = 'rmFilter';
	const CMD_ADD_FIELD = 'addField';
	const CMD_REMOVE_FIELD = 'rmField';

	const OP_EQUAL = 'A';
	const OP_NOT_EQUAL = 'B';
	const OP_GREATER_THAN = 'C';
	const OP_LESS_THAN = 'D';
	const OP_IS_TRUE = 'E';
	const OP_IS_FALSE = 'F';
	const OP_CONTAINS = 'G';
	const OP_NOT_CONTAIN = 'H';

	private $app;
	private $query;
	private $datasetName;

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
	}

	/**
	 *
	 */
	public function display($widgetData)
	{
		//
		$this->_setSessionFilterData();

		//
		$dataset = @$this->FiltersModel->execReadOnlyQuery($this->_generateQuery());

		//
		$listFields = $this->FiltersModel->getExecutedQueryListFields();

		//
		$metaData = $this->FiltersModel->getExecutedQueryMetaData();

		//
		$this->loadViewFilters($listFields, $metaData, $dataset);
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
	public static function loadViewTableDataset($dataset)
	{
		self::_loadView(self::WIDGET_URL_TABLE_DATASET, array(self::DATASET_PARAMETER => $dataset));
	}

	/**
	 *
	 */
	public static function loadViewSelectFilters($metaData)
	{
		self::_loadView(self::WIDGET_URL_SELECT_FILTERS, array(self::METADATA_PARAMETER => $metaData));
	}

	/**
	 *
	 */
	public static function loadViewSelectFields($listFields)
	{
		self::_loadView(self::WIDGET_URL_SELECT_FIELDS, array(self::LIST_FIELDS_PARAMETER => $listFields));
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
		$value = self::_getActiveFilterValue($filterMetaData->name);

		if ($filterMetaData->type == 'int4')
		{
			$html = '
				<span>
					<select name="%s" class="select-filter-operation">
						<option value="'.self::OP_EQUAL.'">equal</option>
						<option value="'.self::OP_NOT_EQUAL.'">not equal</option>
						<option value="'.self::OP_GREATER_THAN.'">greater than</option>
						<option value="'.self::OP_LESS_THAN.'">less than</option>
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
						<option value="'.self::OP_CONTAINS.'">contains</option>
						<option value="'.self::OP_NOT_CONTAIN.'">does not contain</option>
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
						<option value="'.self::OP_IS_TRUE.'">is true</option>
						<option value="'.self::OP_IS_FALSE.'">is false</option>
					</select>
				</span>
				<span>
					<input type="hidden" name="%s" value="%s">
				</span>
			';
		}

		return sprintf($html, $filterMetaData->name.'-operation', $filterMetaData->name, $value);
	}

	/**
	 *
	 */
	protected function loadViewFilters($listFields, $metaData, $dataset)
	{
		// Loads views
		$this->view(
			self::WIDGET_URL_FILTER,
			array(
				self::DATASET_PARAMETER => $dataset,
				self::METADATA_PARAMETER => $metaData,
				self::LIST_FIELDS_PARAMETER => $listFields
			)
		);
	}

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
	private static function _loadView($viewName, $parameters)
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

		$this->session->set_userdata(self::SESSION_NAME, $filterSessionArray);
	}

	/**
	 *
	 */
	private function _initFilterWidget($args)
	{
		if (is_array($args) && count($args) > 0)
		{
			if (isset($args[self::APP_PARAMETER]))
			{
				$this->app = $args[self::APP_PARAMETER];
			}
			else
			{
				show_error('The "'.self::APP_PARAMETER.'" parameter must be specified');
			}

			if (isset($args[self::DATASET_NAME_PARAMETER]))
			{
				$this->datasetName = $args[self::DATASET_NAME_PARAMETER];
			}
			else
			{
				show_error('The "'.self::DATASET_NAME_PARAMETER.'" parameter must be specified');
			}

			if (isset($args[self::QUERY_PARAMETER]))
			{
				$this->query = $args[self::QUERY_PARAMETER];
			}
			else
			{
				show_error('The "'.self::QUERY_PARAMETER.'" parameter must be specified');
			}
		}
		else
		{
			show_error('Second parameter must be an associative array');
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
	private function _setActiveFiltersFromPost(&$activeFilters, &$activeFiltersOperation)
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
			self::SELECTED_FILTERS => $this->_getSelectedFiltersFromPost()
		);

		$filterSessionArray[self::ACTIVE_FILTERS] = array();
		$filterSessionArray[self::ACTIVE_FILTERS_OPERATION] = array();

		$this->_setActiveFiltersFromPost(
			$filterSessionArray[self::ACTIVE_FILTERS],
			$filterSessionArray[self::ACTIVE_FILTERS_OPERATION]
		);

		$this->session->set_userdata(self::SESSION_NAME, $filterSessionArray);
	}

	/**
	 *
	 */
	private function _generateQuery()
	{
		$query = $this->query;

		$filterSessionArray = $this->session->userdata(self::SESSION_NAME);

		if (isset($filterSessionArray[self::ACTIVE_FILTERS]))
		{
			$activeFilters = $filterSessionArray[self::ACTIVE_FILTERS];
		}

		if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION]))
		{
			$activeFiltersOperation = $filterSessionArray[self::ACTIVE_FILTERS_OPERATION];
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
							$condition = ' = '.$activeFilterValue;
							break;
						case self::OP_NOT_EQUAL:
							$condition = ' != '.$activeFilterValue;
							break;
						case self::OP_GREATER_THAN:
							$condition = ' > '.$activeFilterValue;
							break;
						case self::OP_LESS_THAN:
							$condition = ' < '.$activeFilterValue;
							break;
						case self::OP_CONTAINS:
							$condition = ' ILIKE \'%'.$activeFilterValue.'%\'';
							break;
						case self::OP_NOT_CONTAIN:
							$condition = ' NOT ILIKE \'%'.$activeFilterValue.'%\'';
							break;
						case self::OP_IS_TRUE:
							$condition = ' IS TRUE';
							break;
						case self::OP_IS_FALSE:
							$condition = ' IS FALSE';
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
