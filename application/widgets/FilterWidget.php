<?php

/**
 * To filter data using a SQL statement
 */
class FilterWidget extends Widget
{
	// Paths of the views
	const WIDGET_URL_FILTER = 'widgets/filter/filter';
	const WIDGET_URL_SELECT_FIELDS = 'widgets/filter/selectFields';
	const WIDGET_URL_TABLE_DATASET = 'widgets/filter/tableDataset';
	const WIDGET_URL_SELECT_FILTERS = 'widgets/filter/selectFilters';
	const WIDGET_URL_SAVE_FILTER = 'widgets/filter/saveFilter';

	const DEFAULT_DATE_FORMAT = 'd.m.Y H:i:s';
	const DEFAULT_MARK_ROW_CLASS = 'text-danger';

	// Fitler info from DB
	private $_app;
	private $_datasetName;
	private $_filterKurzbz;
	private $_filterId;

	// SQL statement
	private $_query;

	// Additional columns to add to the dataset or aliases to be used to rename columns of the dataset
	private $_additionalColumns;
	private $_columnsAliases;

	// To format or mark rows of the dataset
	private $_formatRow;
	private $_markRow;

	// To have a column in the GUI with checkboxes to select rows in the table
	private $_checkboxes;

	// To hide the GUI to operate or save the filter widget
	private $_hideHeader;
	private $_hideSave;

	private static $_FilterWidgetInstance; // static property that contains the instance of itself

	/**
	 * Initialize the FilterWidget and starts the execution of the logic
	 */
	public function __construct($name, $args = array())
	{
		parent::__construct($name, $args); // calls the parent's constructor

		self::$_FilterWidgetInstance = $this; // set static property $_FilterWidgetInstance with this instance

		$this->load->library('FiltersLib'); // Loads the FiltersLib that contains all the used logic

		$this->_initFilterWidget($args); // checks parameters and initialize properties

		$this->_startFilterWidget(); // let's start
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Called when echoing the filter widget call
	 */
	public function display($widgetData)
	{
		$this->view(self::WIDGET_URL_FILTER); // GUI starts here
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public static methods used to load views and to access statically to some properies of the FilterWidget

	/**
	 * Loads the view related to the selected fields
	 */
	public static function loadViewSelectFields()
	{
		if (self::$_FilterWidgetInstance->_hideHeader != true)
		{
			self::_loadView(self::WIDGET_URL_SELECT_FIELDS);
		}
	}

	/**
	 * Loads the view related to the selected filters
	 */
	public static function loadViewSelectFilters()
	{
		if (self::$_FilterWidgetInstance->_hideHeader != true)
		{
			self::_loadView(self::WIDGET_URL_SELECT_FILTERS);
		}
	}

	/**
	 * Loads the view related to the form to save a custom filter
	 */
	public static function loadViewSaveFilter()
	{
		if (self::$_FilterWidgetInstance->_hideSave != true)
		{
			self::_loadView(self::WIDGET_URL_SAVE_FILTER);
		}
	}

	/**
	 * Loads the view related to the table dataset
	 */
	public static function loadViewTableDataset()
	{
		self::_loadView(self::WIDGET_URL_TABLE_DATASET);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks parameters and initialize all the properties of this FilterWidget
	 */
	private function _initFilterWidget($args)
	{
		$this->_checkParameters($args);

		// If here then everything is ok

		// Initialize class properties
		$this->_app = null;
		$this->_datasetName = null;
		$this->_filterKurzbz = null;
		$this->_filterId = null;
		$this->_query = null;
		$this->_additionalColumns = null;
		$this->_columnsAliases = null;
		$this->_formatRow = null;
		$this->_markRow = null;
		$this->_checkboxes = null;
		$this->_hideHeader = null;
		$this->_hideSave = null;

		// Parameters needed to retrive univocally a filter from DB
		if (isset($args[FiltersLib::APP_PARAMETER]))
		{
			$this->_app = $args[FiltersLib::APP_PARAMETER];
		}

		if (isset($args[FiltersLib::DATASET_NAME_PARAMETER]))
		{
			$this->_datasetName = $args[FiltersLib::DATASET_NAME_PARAMETER];
		}

		if (isset($args[FiltersLib::FILTER_KURZBZ_PARAMETER]))
		{
			$this->_filterKurzbz = $args[FiltersLib::FILTER_KURZBZ_PARAMETER];
		}

		if (isset($args[FiltersLib::FILTER_ID]))
		{
			$this->_filterId = $args[FiltersLib::FILTER_ID];
		}

		// How to retrive data for the filter: SQL statement or a result from DB
		if (isset($args[FiltersLib::QUERY_PARAMETER]))
		{
			$this->_query = $args[FiltersLib::QUERY_PARAMETER];
		}

		// Parameter is used to add extra columns to the dataset
		if (isset($args[FiltersLib::ADDITIONAL_COLUMNS])
			&& is_array($args[FiltersLib::ADDITIONAL_COLUMNS])
			&& count($args[FiltersLib::ADDITIONAL_COLUMNS]) > 0)
		{
			$this->_additionalColumns = $args[FiltersLib::ADDITIONAL_COLUMNS];
		}

		// Parameter is used to add use aliases for the columns fo the dataset
		if (isset($args[FiltersLib::COLUMNS_ALIASES])
			&& is_array($args[FiltersLib::COLUMNS_ALIASES])
			&& count($args[FiltersLib::COLUMNS_ALIASES]) > 0)
		{
			$this->_columnsAliases = $args[FiltersLib::COLUMNS_ALIASES];
		}

		// Parameter that contains a function to format the rows of the dataset
		if (isset($args[FiltersLib::FORMAT_ROW]) && is_callable($args[FiltersLib::FORMAT_ROW]))
		{
			$this->_formatRow = $args[FiltersLib::FORMAT_ROW];
		}

		// Parameter that contains a function to mark in the GUI the rows of the dataset
		if (isset($args[FiltersLib::MARK_ROW]) && is_callable($args[FiltersLib::MARK_ROW]))
		{
			$this->_markRow = $args[FiltersLib::MARK_ROW];
		}

		// Parameter used to specify the column of the dataset that will be used
		// as id of the checkboxes column in the GUI
		if (isset($args[FiltersLib::CHECKBOXES]))
		{
			$this->_checkboxes = $args[FiltersLib::CHECKBOXES];
		}

		// To specify if the header to operate with the FilterWidget is shown or not
		if (isset($args[FiltersLib::HIDE_HEADER]) && is_bool($args[FiltersLib::HIDE_HEADER]))
		{
			$this->_hideHeader = $args[FiltersLib::HIDE_HEADER];
		}

		// To specify if the form to save a custom FilterWidget is shown or not
		if (isset($args[FiltersLib::HIDE_SAVE]) && is_bool($args[FiltersLib::HIDE_SAVE]))
		{
			$this->_hideSave = $args[FiltersLib::HIDE_SAVE];
		}
	}

	/**
	 * Checks the required parameters used to call this FilterWidget
	 */
	private function _checkParameters($args)
	{
		if (!is_array($args) || (is_array($args) && count($args) == 0))
		{
			show_error('Second parameter of the widget call must be a not empty associative array');
		}
		else
		{
			if ((!isset($args[FiltersLib::APP_PARAMETER]) && !isset($args[FiltersLib::DATASET_NAME_PARAMETER]))
				&& !isset($args[FiltersLib::FILTER_ID]))
			{
				show_error(
					'The parameters ("'.FiltersLib::APP_PARAMETER.'" and "'.FiltersLib::DATASET_NAME_PARAMETER.') OR "'.
					FiltersLib::FILTER_ID.'" must be specified'
				);
			}
			else
			{
				if (!isset($args[FiltersLib::QUERY_PARAMETER]))
				{
					show_error('The parameters "'.FiltersLib::QUERY_PARAMETER.'" must be specified');
				}
			}
		}
	}

	/**
	 * Contains all the logic used to load all the data needed to the FilterWidget
	 */
	private function _startFilterWidget()
	{
		// Read the all session for this filter widget
		$session = $this->filterslib->getSession();

		// If session is NOT empty
		if ($session != null)
		{
			// Retrive the filterId stored in the session
			$sessionFilterId = $this->filterslib->getElementSession(FiltersLib::FILTER_ID);

			// If the filter loaded in session is NOT the same that is being requested then empty the session
			if ($this->_filterId != $sessionFilterId)
			{
				$this->filterslib->setSession(null);
				$session = null;
			}
			else // else if the filter loaded in session is the same that is being requested
			{
				// Get SESSION_RELOAD_DATASET from the session
				$reloadDataset = $this->filterslib->getElementSession(FiltersLib::SESSION_RELOAD_DATASET);
				if ($reloadDataset === true) // if it's value is very true, reload the dataset
				{
					// set as false to stop changing the dataset
					$this->filterslib->setElementSession(FiltersLib::SESSION_RELOAD_DATASET, false);

					// Generate dataset query using filters from the session
					$datasetQuery = $this->filterslib->generateDatasetQuery(
						$this->_query,
						$this->filterslib->getElementSession(FiltersLib::SESSION_FILTERS)
					);

					// Then retrive dataset from DB
					$dataset = $this->filterslib->getDataset($datasetQuery);

					// Save changes into session if data are valid
					if (!isError($dataset))
					{
						$formattedDataset = $this->_formatDataset($dataset); // format the dataset using markRow and formatRow

						// Set the new dataset and it's attributes in the session
						$this->filterslib->setElementSession(
							FiltersLib::SESSION_DATASET_METADATA,
							$this->FiltersModel->getExecutedQueryMetaData()
						);
						$this->filterslib->setElementSession(FiltersLib::SESSION_ROW_NUMBER, count($dataset->retval));
						$this->filterslib->setElementSession(FiltersLib::SESSION_DATASET, $formattedDataset);
					}
				}
			}
		}

		// If the session is empty
		if ($session == null)
		{
			// Load filter definition data from DB
			$definition = $this->filterslib->loadDefinition(
				$this->_filterId,
				$this->_app,
				$this->_datasetName,
				$this->_filterKurzbz
			);

			// Checks and parse json present into the definition
			$parsedFilterJson = $this->filterslib->parseFilterJson($definition);
			if ($parsedFilterJson != null) // if the json is valid
			{
				// Generate dataset query
				$datasetQuery = $this->filterslib->generateDatasetQuery($this->_query, $parsedFilterJson->filters);

				// Then retrive dataset from DB
				$dataset = $this->filterslib->getDataset($datasetQuery);

				// Try to load the name of the filter using the PhrasesLib
				$filterName = $this->filterslib->getFilterName($parsedFilterJson);

				// Save changes into session if data are valid
				if (!isError($dataset))
				{
					$formattedDataset = $this->_formatDataset($dataset); // format the dataset using markRow and formatRow

					// Stores an array that contains all the data useful for
					$this->filterslib->setSession(
						array(
							FiltersLib::FILTER_ID => $this->_filterId, //
							FiltersLib::APP_PARAMETER => $this->_app, //
							FiltersLib::DATASET_NAME_PARAMETER => $this->_datasetName, //

							FiltersLib::SESSION_FILTER_NAME => $filterName, //

							FiltersLib::SESSION_FIELDS => $this->FiltersModel->getExecutedQueryListFields(), //
							FiltersLib::SESSION_SELECTED_FIELDS => $this->_getColumnsNames($parsedFilterJson->columns), //
							FiltersLib::SESSION_COLUMNS_ALIASES => $this->_columnsAliases, //
							FiltersLib::SESSION_ADDITIONAL_COLUMNS => $this->_additionalColumns, //
							FiltersLib::SESSION_CHECKBOXES => $this->_checkboxes, //
							FiltersLib::SESSION_FILTERS => $parsedFilterJson->filters, //
							FiltersLib::SESSION_DATASET_METADATA => $this->FiltersModel->getExecutedQueryMetaData(), //
							FiltersLib::SESSION_ROW_NUMBER => count($dataset->retval), //
							FiltersLib::SESSION_DATASET => $formattedDataset, //
							FiltersLib::SESSION_RELOAD_DATASET => false //
						)
					);
				}
			}
		}

		// var_dump($this->filterslib->getSession());
	}

	/**
	 *
	 */
	private function _formatDataset($rawDataset)
	{
		$formattedDataset = null;

		if (hasData($rawDataset) && is_array($rawDataset->retval))
		{
			$formattedDataset = array();

			for ($rowCounter = 0; $rowCounter < count($rawDataset->retval); $rowCounter++)
			{
				$row = $rawDataset->retval[$rowCounter];

				$formattedRow = $this->_formatRow($row);
				$formattedRow->MARK_ROW_CLASS = $this->_markRow($row);
				$formattedDataset[] = $formattedRow;
			}
		}

		return $formattedDataset;
	}

	/**
	 *
	 */
	private function _formatRow($datasetRaw)
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

		if ($this->_formatRow != null)
		{
			$formatRow = $this->_formatRow;
			$tmpDatasetRaw = $formatRow($tmpDatasetRaw);
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
			if ($this->_markRow != null)
			{
				$markRow = $this->_markRow;
				$class = $markRow($datasetRaw);
			}
		}

		return !isset($class) ? '' : $class;
	}

	/**
	 *
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

	/**
	 * Loads a view using the given viewName and eventually other parameters
	 */
	private static function _loadView($viewName, $parameters = null)
	{
		$ci =& get_instance();
		$ci->load->view($viewName, $parameters);
	}
}
