<?php

/**
 * Copyright (C) 2023 fhcomplete.org
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

/**
 * To filter data using a SQL statement
 */
class FilterWidget extends Widget
{
	// Paths of the views
	const WIDGET_URL_FILTER = 'widgets/filter/filter';
	const WIDGET_URL_FILTER_OPTIONS = 'widgets/filter/filterOptions';
	const WIDGET_URL_SELECT_FIELDS = 'widgets/filter/selectFields';
	const WIDGET_URL_DATASET_TABLESORTER = 'widgets/filter/tableDataset';
	const WIDGET_URL_DATASET_PIVOTUI = 'widgets/filter/pivotUIDataset';
	const WIDGET_URL_DATASET_TABULATOR = 'widgets/filter/tabulatorDataset';
	const WIDGET_URL_SELECT_FILTERS = 'widgets/filter/selectFilters';
	const WIDGET_URL_SAVE_FILTER = 'widgets/filter/saveFilter';

	// Default formats
	const DEFAULT_DATE_FORMAT = 'd.m.Y H:i:s';
	const DEFAULT_MARK_ROW_CLASS = 'text-danger';

	// Required permissions to use this FilterWidget
	private $_requiredPermissions;

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

	// To hide the GUI to operate with the filter widget or to save a custom filter
	private $_hideOptions; // if true hides all the options
	private $_hideSelectFields; // if true hides the fields selection
	private $_hideSelectFilters; // if true hides the filters selections
	private $_hideSave; // if true hides the GUI to save a custom filter

	private $_hideMenu; // if true then the menu is not shown
	private $_customMenu; // if true then method _setFilterMenu is NOT called

	private $_datasetRepresentation; // dataset representation (ex: tablesorter, pivotUI, ...)
	private $_datasetRepresentationOptions; // dataset representation options for tablesorter, pivotUI, ...
	private $_datasetRepFieldsDefs; // dataset representation attributes for each record field

	private $_reloadDataset; // Force Reload of Dataset

	private $_sessionTimeout; // session expiring time

	private $_encryptedColumns; // contains info about encrypted columns

	private static $_FilterWidgetInstance; // static property that contains the instance of itself

	/**
	 * Initialize the FilterWidget and starts the execution of the logic
	 */
	public function __construct($name, $args = array())
	{
		parent::__construct($name, $args); // calls the parent's constructor

		self::$_FilterWidgetInstance = $this; // set static property $_FilterWidgetInstance with this instance

		$this->load->library('FilterWidgetLib'); // Loads the FilterWidgetLib that contains all the used logic

		$this->_initFilterWidget($args); // checks parameters and initialize properties

		$this->filterwidgetlib->setFilterUniqueIdByParams($args);

		// Let's start if it's allowed
		// NOTE: If it is NOT allowed then no data are loaded
		if ($this->filterwidgetlib->isAllowed($this->_requiredPermissions))
		{
			$this->_startFilterWidget();

			// If a custom menu is not used, then default menu is used
			if ($this->_hideMenu != true && $this->_customMenu != true) $this->_setFilterMenu();
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Called when echoing the filter widget call
	 */
	public function display($widgetData)
	{
		$this->view(self::WIDGET_URL_FILTER, array(
			'app' => $this->_app,
			'dataset' => $this->_datasetName,
			'filterid' => $this->_filterId
		)); // GUI starts here
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public static methods used to load views and to access statically to some properies of the FilterWidget

	/**
	 * Loads the view related to the filter options
	 */
	public static function loadViewFilterOptions()
	{
		if (self::$_FilterWidgetInstance->_hideOptions != true)
		{
			self::_loadView(self::WIDGET_URL_FILTER_OPTIONS);
		}
	}

	/**
	 * Loads the view related to the selected fields
	 */
	public static function loadViewSelectFields()
	{
		if (self::$_FilterWidgetInstance->_hideSelectFields != true)
		{
			self::_loadView(self::WIDGET_URL_SELECT_FIELDS);
		}
	}

	/**
	 * Loads the view related to the selected filters
	 */
	public static function loadViewSelectFilters()
	{
		if (self::$_FilterWidgetInstance->_hideSelectFilters != true)
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
	 * Loads the view related to the dataset, here is decided how to represent the dataset (ex: tablesorter, pivotUI, ...)
	 */
	public static function loadViewDataset()
	{
		if (self::$_FilterWidgetInstance->_datasetRepresentation == FilterWidgetLib::DATASET_REP_TABLESORTER)
		{
			self::_loadView(self::WIDGET_URL_DATASET_TABLESORTER);
		}

		if (self::$_FilterWidgetInstance->_datasetRepresentation == FilterWidgetLib::DATASET_REP_PIVOTUI)
		{
			self::_loadView(self::WIDGET_URL_DATASET_PIVOTUI);
		}

		if (self::$_FilterWidgetInstance->_datasetRepresentation == FilterWidgetLib::DATASET_REP_TABULATOR)
		{
			self::_loadView(self::WIDGET_URL_DATASET_TABULATOR);
		}
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
		$this->_requiredPermissions = null;
		$this->_app = null;
		$this->_datasetName = null;
		$this->_filterKurzbz = null;
		$this->_filterId = null;
		$this->_reloadDataset = true; // by default the dataset is NOT cached in session
		$this->_query = null;
		$this->_additionalColumns = null;
		$this->_columnsAliases = null;
		$this->_formatRow = null;
		$this->_markRow = null;
		$this->_checkboxes = null;
		$this->_encryptedColumns = null;
		$this->_hideOptions = null;
		$this->_hideSelectFields = null;
		$this->_hideSelectFilters = null;
		$this->_hideSave = null;
		$this->_hideMenu = null;
		$this->_customMenu = null;
		$this->_datasetRepresentation = null;
		$this->_datasetRepresentationOptions = null;
		$this->_datasetRepFieldsDefs = null;
		$this->_sessionTimeout = FilterWidgetLib::SESSION_DEFAULT_TIMEOUT;

		// Retrieved the required permissions parameter if present
		if (isset($args[FilterWidgetLib::REQUIRED_PERMISSIONS]))
		{
			$this->_requiredPermissions = $args[FilterWidgetLib::REQUIRED_PERMISSIONS];
		}

		// Parameters needed to retrieve univocally a filter from DB
		if (isset($args[FilterWidgetLib::APP]))
		{
			$this->_app = $args[FilterWidgetLib::APP];
		}

		if (isset($args[FilterWidgetLib::DATASET_NAME]))
		{
			$this->_datasetName = $args[FilterWidgetLib::DATASET_NAME];
		}

		if (isset($args[FilterWidgetLib::FILTER_KURZBZ]))
		{
			$this->_filterKurzbz = $args[FilterWidgetLib::FILTER_KURZBZ];
		}

		if (isset($args[FilterWidgetLib::FILTER_ID]))
		{
			$this->_filterId = $args[FilterWidgetLib::FILTER_ID];
		}

		// How to retrieve data for the filter: SQL statement or a result from DB
		if (isset($args[FilterWidgetLib::QUERY]))
		{
			$this->_query = $args[FilterWidgetLib::QUERY];
		}

		if (isset($args[FilterWidgetLib::DATASET_RELOAD]))
		{
			$this->_reloadDataset = $args[FilterWidgetLib::DATASET_RELOAD];
		}

		// Parameter is used to add extra columns to the dataset
		if (isset($args[FilterWidgetLib::ADDITIONAL_COLUMNS])
			&& is_array($args[FilterWidgetLib::ADDITIONAL_COLUMNS])
			&& count($args[FilterWidgetLib::ADDITIONAL_COLUMNS]) > 0)
		{
			$this->_additionalColumns = $args[FilterWidgetLib::ADDITIONAL_COLUMNS];
		}

		// Parameter is used to define the ecrypted columns
		if (isset($args[FilterWidgetLib::ENCRYPTED_COLUMNS])
			&& is_array($args[FilterWidgetLib::ENCRYPTED_COLUMNS])
			&& count($args[FilterWidgetLib::ENCRYPTED_COLUMNS]) > 0)
		{
			$this->_encryptedColumns = $args[FilterWidgetLib::ENCRYPTED_COLUMNS];
		}

		// Parameter is used to add use aliases for the columns fo the dataset
		if (isset($args[FilterWidgetLib::COLUMNS_ALIASES])
			&& is_array($args[FilterWidgetLib::COLUMNS_ALIASES])
			&& count($args[FilterWidgetLib::COLUMNS_ALIASES]) > 0)
		{
			$this->_columnsAliases = $args[FilterWidgetLib::COLUMNS_ALIASES];
		}

		// Parameter that contains a function to format the rows of the dataset
		if (isset($args[FilterWidgetLib::FORMAT_ROW]) && is_callable($args[FilterWidgetLib::FORMAT_ROW]))
		{
			$this->_formatRow = $args[FilterWidgetLib::FORMAT_ROW];
		}

		// Parameter that contains a function to mark in the GUI the rows of the dataset
		if (isset($args[FilterWidgetLib::MARK_ROW]) && is_callable($args[FilterWidgetLib::MARK_ROW]))
		{
			$this->_markRow = $args[FilterWidgetLib::MARK_ROW];
		}

		// Parameter used to specify the column of the dataset that will be used
		// as id of the checkboxes column in the GUI
		if (isset($args[FilterWidgetLib::CHECKBOXES]))
		{
			$this->_checkboxes = $args[FilterWidgetLib::CHECKBOXES];
		}

		// To specify if the filter options are shown ot not
		if (isset($args[FilterWidgetLib::HIDE_OPTIONS]) && is_bool($args[FilterWidgetLib::HIDE_OPTIONS]))
		{
			$this->_hideOptions = $args[FilterWidgetLib::HIDE_OPTIONS];
		}

		// To specify if the form to select fields is shown or not
		if (isset($args[FilterWidgetLib::HIDE_SELECT_FIELDS]) && is_bool($args[FilterWidgetLib::HIDE_SELECT_FIELDS]))
		{
			$this->_hideSelectFields = $args[FilterWidgetLib::HIDE_SELECT_FIELDS];
		}

		// To specify if the form to select filters is shown or not
		if (isset($args[FilterWidgetLib::HIDE_SELECT_FILTERS]) && is_bool($args[FilterWidgetLib::HIDE_SELECT_FILTERS]))
		{
			$this->_hideSelectFilters = $args[FilterWidgetLib::HIDE_SELECT_FILTERS];
		}

		// To specify if the form to save a custom FilterWidget is shown or not
		if (isset($args[FilterWidgetLib::HIDE_SAVE]) && is_bool($args[FilterWidgetLib::HIDE_SAVE]))
		{
			$this->_hideSave = $args[FilterWidgetLib::HIDE_SAVE];
		}

		// If the menu should be shown or not
		if (isset($args[FilterWidgetLib::HIDE_MENU]) && is_bool($args[FilterWidgetLib::HIDE_MENU]))
		{
			$this->_hideMenu = $args[FilterWidgetLib::HIDE_MENU];
		}

		// If a custom menu is set
		if (isset($args[FilterWidgetLib::CUSTOM_MENU]) && is_bool($args[FilterWidgetLib::CUSTOM_MENU]))
		{
			$this->_customMenu = $args[FilterWidgetLib::CUSTOM_MENU];
		}

		// To specify how to represent the dataset (ex: tablesorter, pivotUI, ...)
		if (isset($args[FilterWidgetLib::DATASET_REPRESENTATION])
			&& ($args[FilterWidgetLib::DATASET_REPRESENTATION] == FilterWidgetLib::DATASET_REP_TABLESORTER
			|| $args[FilterWidgetLib::DATASET_REPRESENTATION] == FilterWidgetLib::DATASET_REP_PIVOTUI
			|| $args[FilterWidgetLib::DATASET_REPRESENTATION] == FilterWidgetLib::DATASET_REP_TABULATOR))
		{
			$this->_datasetRepresentation = $args[FilterWidgetLib::DATASET_REPRESENTATION];
		}

		// To specify options for the dataset representation (ex: tablesorter, pivotUI, ...)
		if (isset($args[FilterWidgetLib::DATASET_REP_OPTIONS]) && !isEmptyString($args[FilterWidgetLib::DATASET_REP_OPTIONS]))
		{
			$this->_datasetRepresentationOptions = $args[FilterWidgetLib::DATASET_REP_OPTIONS];
		}

		// To specify how to represent each record field
		if (isset($args[FilterWidgetLib::DATASET_REP_FIELDS_DEFS]) && !isEmptyString($args[FilterWidgetLib::DATASET_REP_FIELDS_DEFS]))
		{
			$this->_datasetRepFieldsDefs = $args[FilterWidgetLib::DATASET_REP_FIELDS_DEFS];
		}

		// To specify the expiring session time
		if (isset($args[FilterWidgetLib::SESSION_TIMEOUT]) && is_numeric($args[FilterWidgetLib::SESSION_TIMEOUT]))
		{
			$this->_sessionTimeout = $args[FilterWidgetLib::SESSION_TIMEOUT];
		}
	}

	/**
	 * Checks the required parameters used to call this FilterWidget
	 */
	private function _checkParameters($args)
	{
		// If no options are given to this widget...
		if (!is_array($args) || (is_array($args) && count($args) == 0))
		{
			show_error('Second parameter of the widget call must be a NOT empty associative array');
		}
		else // ...otherwise
		{
			// Parameters (app AND dataset name) OR filter id are mandatory
			if ((!isset($args[FilterWidgetLib::APP]) && !isset($args[FilterWidgetLib::DATASET_NAME]))
				&& !isset($args[FilterWidgetLib::FILTER_ID]))
			{
				show_error(
					'The parameters ("'.FilterWidgetLib::APP.'" AND "'.FilterWidgetLib::DATASET_NAME.') OR "'.
					FilterWidgetLib::FILTER_ID.'" must be specified'
				);
			}

			// The query parameter is mandatory
			if (!isset($args[FilterWidgetLib::QUERY]))
			{
				show_error('The parameter "'.FilterWidgetLib::QUERY.'" must be specified');
			}

			// The dataset representation parameter is mandatory
			if (!isset($args[FilterWidgetLib::DATASET_REPRESENTATION]))
			{
				show_error('The parameter "'.FilterWidgetLib::DATASET_REPRESENTATION.'" must be specified');
			}

			// Checks if the dataset representation parameter is valid
			if (isset($args[FilterWidgetLib::DATASET_REPRESENTATION])
				&& $args[FilterWidgetLib::DATASET_REPRESENTATION] != FilterWidgetLib::DATASET_REP_TABLESORTER
				&& $args[FilterWidgetLib::DATASET_REPRESENTATION] != FilterWidgetLib::DATASET_REP_PIVOTUI
				&& $args[FilterWidgetLib::DATASET_REPRESENTATION] != FilterWidgetLib::DATASET_REP_TABULATOR)
			{
				show_error(
					'The parameter "'.FilterWidgetLib::DATASET_REPRESENTATION.
					'" must be IN ("'
						.FilterWidgetLib::DATASET_REP_TABLESORTER.'", "'
						.FilterWidgetLib::DATASET_REP_PIVOTUI.'", "'
						.FilterWidgetLib::DATASET_REP_TABULATOR.'")'
				);
			}

			// If given the session timeout parameter must be a number
			if (isset($args[FilterWidgetLib::SESSION_TIMEOUT]) && !is_numeric($args[FilterWidgetLib::SESSION_TIMEOUT]))
			{
				show_error('The parameter "'.FilterWidgetLib::SESSION_TIMEOUT.'" must be a number');
			}
		}
	}

	/**
	 * Contains all the logic used to load all the data needed to the FilterWidget
	 */
	private function _startFilterWidget()
	{
		// Looks for expired filter widgets in session and drops them
		$this->filterwidgetlib->dropExpiredFilterWidgets();

		// Read the all session for this filter widget
		$session = $this->filterwidgetlib->getSession();

		// If session is NOT empty -> a filter was already loaded
		if ($session != null)
		{
			// Retrieve the filterId stored in the session
			$sessionFilterId = $this->filterwidgetlib->getSessionElement(FilterWidgetLib::FILTER_ID);

			// If the filter loaded in session is NOT the same that is being requested then empty the session
			if ($this->_filterId != $sessionFilterId)
			{
				$this->filterwidgetlib->setSession(null);
				$session = null;
			}
			else // else if the filter loaded in session is the same that is being requested
			{
				// Get SESSION_DATASET_RELOAD from the session
				$sessionReloadDataset = $this->filterwidgetlib->getSessionElement(FilterWidgetLib::SESSION_DATASET_RELOAD);

				// if Filter changed or reload is forced by parameter then reload the Dataset
				if ($this->_reloadDataset === true || $sessionReloadDataset === true)
				{
					// Set as false to stop changing the dataset
					$this->filterwidgetlib->setSessionElement(FilterWidgetLib::SESSION_DATASET_RELOAD, false);

					// Generate dataset query using filters from the session
					$datasetQuery = $this->filterwidgetlib->generateDatasetQuery(
						$this->_query,
						$this->filterwidgetlib->getSessionElement(FilterWidgetLib::SESSION_FILTERS)
					);

					// Then retrieve dataset from DB
					$dataset = $this->filterwidgetlib->getDataset($datasetQuery, $this->_encryptedColumns);

					// Save changes into session if data are valid
					if (!isError($dataset))
					{
						$this->_formatDataset($dataset); // marks rows using markRow and format rowns using formatRow

						// Set the new dataset and its attributes in the session
						$this->filterwidgetlib->setSessionElement(FilterWidgetLib::SESSION_METADATA, $this->FiltersModel->getExecutedQueryMetaData());
						$this->filterwidgetlib->setSessionElement(FilterWidgetLib::SESSION_ROW_NUMBER, count($dataset->retval));
						$this->filterwidgetlib->setSessionElement(FilterWidgetLib::SESSION_DATASET, $dataset->retval);
					}
				}
			}
		}

		// If the session is empty -> first time that this filter is loaded
		if ($session == null)
		{
			// Load filter definition data from DB
			$definition = $this->filterwidgetlib->loadDefinition(
				$this->_filterId,
				$this->_app,
				$this->_datasetName,
				$this->_filterKurzbz
			);

			// Checks and parse json present into the definition
			$parsedFilterJson = $this->filterwidgetlib->parseFilterJson($definition);
			if ($parsedFilterJson != null) // if the json is valid
			{
				// Generate dataset query
				$datasetQuery = $this->filterwidgetlib->generateDatasetQuery($this->_query, $parsedFilterJson->filters);

				// Then retrieve dataset from DB
				$dataset = $this->filterwidgetlib->getDataset($datasetQuery, $this->_encryptedColumns);

				// Try to load the name of the filter using the PhrasesLib
				$filterName = $this->filterwidgetlib->getFilterName($parsedFilterJson);

				// Save changes into session if data are valid
				if (!isError($dataset))
				{
					$this->_formatDataset($dataset); // marks rows using markRow and format rowns using formatRow

					// Stores an array that contains all the data useful for
					$this->filterwidgetlib->setSession(
						array(
							FilterWidgetLib::FILTER_ID => $this->_filterId, // the current filter id
							FilterWidgetLib::APP => $this->_app, // the current app parameter
							FilterWidgetLib::DATASET_NAME => $this->_datasetName, // the carrent dataset name
							FilterWidgetLib::SESSION_FILTER_NAME => $filterName, // the current filter name
							FilterWidgetLib::SESSION_FIELDS => $this->FiltersModel->getExecutedQueryListFields(), // all the fields of the dataset
							FilterWidgetLib::SESSION_SELECTED_FIELDS => $this->_getColumnsNames($parsedFilterJson->columns), // all the selected fields
							FilterWidgetLib::SESSION_COLUMNS_ALIASES => $this->_columnsAliases, // all the fields aliases
							FilterWidgetLib::SESSION_ADDITIONAL_COLUMNS => $this->_additionalColumns, // additional columns
							FilterWidgetLib::SESSION_ENCRYPTED_COLUMNS => $this->_encryptedColumns, // encrypted columns
							FilterWidgetLib::SESSION_CHECKBOXES => $this->_checkboxes, // the name of the field used to build the checkboxes column
							FilterWidgetLib::SESSION_FILTERS => $parsedFilterJson->filters, // all the filters used to filter the dataset
							FilterWidgetLib::SESSION_METADATA => $this->FiltersModel->getExecutedQueryMetaData(), // the metadata of the dataset
							FilterWidgetLib::SESSION_ROW_NUMBER => count($dataset->retval), // the number of loaded rows by this filter
							FilterWidgetLib::SESSION_DATASET => $dataset->retval, // the entire dataset
							FilterWidgetLib::SESSION_DATASET_RELOAD => false, // if the dataset must be reloaded, not needed the first time
							FilterWidgetLib::SESSION_DATASET_REPRESENTATION => $this->_datasetRepresentation, // the choosen dataset representation
							FilterWidgetLib::SESSION_DATASET_REP_OPTIONS => $this->_datasetRepresentationOptions, // the choosen dataset representation options
							FilterWidgetLib::SESSION_DATASET_REP_FIELDS_DEFS => $this->_datasetRepFieldsDefs // the choosen dataset representation record fields definition
						)
					);
				}
			}
		}

		// NOTE: latest operations to be performed in the session to be shure that they are always present
		// To be always stored in the session, otherwise is not possible to load data from Filters controller
		$this->filterwidgetlib->setSessionElement(FilterWidgetLib::REQUIRED_PERMISSIONS, $this->_requiredPermissions);
		// Renew or set the session expiring time
		$this->filterwidgetlib->setSessionElement(FilterWidgetLib::SESSION_TIMEOUT, strtotime('+'.$this->_sessionTimeout.' minutes', time()));
	}

	/**
	 *
	 */
	private function _setFilterMenu()
	{
		// Generates the filters structure array
		$this->filterwidgetlib->generateFilterMenu(
			$this->router->directory.$this->router->class.'/'.$this->router->method
		);
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

		foreach ($columns as $obj)
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
