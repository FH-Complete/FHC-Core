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
 * To display a table that shows data retriev by a SQL statement
 */
class TableWidget extends Widget
{
	// Paths of the views
	const WIDGET_URL_TABLE = 'widgets/table/table';
	const WIDGET_URL_DATASET_TABLESORTER = 'widgets/table/tableDataset';
	const WIDGET_URL_DATASET_PIVOTUI = 'widgets/table/pivotUIDataset';
	const WIDGET_URL_DATASET_TABULATOR = 'widgets/table/tabulatorDataset';

	// Default formats
	const DEFAULT_DATE_FORMAT = 'd.m.Y H:i:s';
	const DEFAULT_MARK_ROW_CLASS = 'text-danger';

	// Required permissions to use this TableWidget
	private $_requiredPermissions;

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

	private $_datasetRepresentation; // dataset representation (ex: tablesorter, pivotUI, ...)
	private $_datasetRepresentationOptions; // dataset representation options for tablesorter, pivotUI, ...
	private $_datasetRepFieldsDefs; // dataset representation attributes for each record field

	private $_reloadDataset; // Force Reload of Dataset

	private $_sessionTimeout; // session expiring time

	private $_encryptedColumns; // contains info about encrypted columns

	private static $_TableWidgetInstance; // static property that contains the instance of itself

	/**
	 * Initialize the TableWidget and starts the execution of the logic
	 */
	public function __construct($name, $args = array())
	{
		parent::__construct($name, $args); // calls the parent's constructor

		self::$_TableWidgetInstance = $this; // set static property $_TableWidgetInstance with this instance

		$this->load->library('TableWidgetLib'); // Loads the TableWidgetLib that contains all the used logic

		$this->_initTableWidget($args); // checks parameters and initialize properties

		$this->tablewidgetlib->setTableUniqueIdByParams($args);

		// Let's start if it's allowed
		// NOTE: If it is NOT allowed then no data are loaded
		if ($this->tablewidgetlib->isAllowed($this->_requiredPermissions))
		{
			$this->_startTableWidget($args[TableWidgetLib::TABLE_UNIQUE_ID]);
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Called when echoing the table widget call
	 */
	public function display($widgetData)
	{
		$this->view(self::WIDGET_URL_TABLE, array(
			'tableUniqueId' => $widgetData[TableWidgetLib::TABLE_UNIQUE_ID]
		)); // GUI starts here
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public static methods used to load views and to access statically to some properies of the TableWidget

	/**
	 * Loads the view related to the dataset, here is decided how to represent the dataset (ex: tablesorter, pivotUI, ...)
	 */
	public static function loadViewDataset()
	{
		if (self::$_TableWidgetInstance->_datasetRepresentation == TableWidgetLib::DATASET_REP_TABLESORTER)
		{
			self::_loadView(self::WIDGET_URL_DATASET_TABLESORTER);
		}

		if (self::$_TableWidgetInstance->_datasetRepresentation == TableWidgetLib::DATASET_REP_PIVOTUI)
		{
			self::_loadView(self::WIDGET_URL_DATASET_PIVOTUI);
		}

		if (self::$_TableWidgetInstance->_datasetRepresentation == TableWidgetLib::DATASET_REP_TABULATOR)
		{
			self::_loadView(self::WIDGET_URL_DATASET_TABULATOR);
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks parameters and initialize all the properties of this TableWidget
	 */
	private function _initTableWidget($args)
	{
		$this->_checkParameters($args);

		// If here then everything is ok

		// Initialize class properties
		$this->_requiredPermissions = null;
		$this->_reloadDataset = true; // by default the dataset is NOT cached in session
		$this->_query = null;
		$this->_additionalColumns = null;
		$this->_columnsAliases = null;
		$this->_formatRow = null;
		$this->_markRow = null;
		$this->_checkboxes = null;
		$this->_datasetRepresentation = null;
		$this->_datasetRepresentationOptions = null;
		$this->_datasetRepFieldsDefs = null;
		$this->_sessionTimeout = TableWidgetLib::SESSION_DEFAULT_TIMEOUT;
		$this->_encryptedColumns = null;

		// Retrieved the required permissions parameter if present
		if (isset($args[TableWidgetLib::REQUIRED_PERMISSIONS]))
		{
			$this->_requiredPermissions = $args[TableWidgetLib::REQUIRED_PERMISSIONS];
		}

		// How to retrieve data for the table: SQL statement or a result from DB
		if (isset($args[TableWidgetLib::QUERY]))
		{
			$this->_query = $args[TableWidgetLib::QUERY];
		}

		if (isset($args[TableWidgetLib::DATASET_RELOAD]))
		{
			$this->_reloadDataset = $args[TableWidgetLib::DATASET_RELOAD];
		}

		// Parameter is used to add extra columns to the dataset
		if (isset($args[TableWidgetLib::ADDITIONAL_COLUMNS])
			&& is_array($args[TableWidgetLib::ADDITIONAL_COLUMNS])
			&& count($args[TableWidgetLib::ADDITIONAL_COLUMNS]) > 0)
		{
			$this->_additionalColumns = $args[TableWidgetLib::ADDITIONAL_COLUMNS];
		}

		// Parameter is used to add use aliases for the columns fo the dataset
		if (isset($args[TableWidgetLib::COLUMNS_ALIASES])
			&& is_array($args[TableWidgetLib::COLUMNS_ALIASES])
			&& count($args[TableWidgetLib::COLUMNS_ALIASES]) > 0)
		{
			$this->_columnsAliases = $args[TableWidgetLib::COLUMNS_ALIASES];
		}

		// Parameter that contains a function to format the rows of the dataset
		if (isset($args[TableWidgetLib::FORMAT_ROW]) && is_callable($args[TableWidgetLib::FORMAT_ROW]))
		{
			$this->_formatRow = $args[TableWidgetLib::FORMAT_ROW];
		}

		// Parameter that contains a function to mark in the GUI the rows of the dataset
		if (isset($args[TableWidgetLib::MARK_ROW]) && is_callable($args[TableWidgetLib::MARK_ROW]))
		{
			$this->_markRow = $args[TableWidgetLib::MARK_ROW];
		}

		// Parameter used to specify the column of the dataset that will be used
		// as id of the checkboxes column in the GUI
		if (isset($args[TableWidgetLib::CHECKBOXES]))
		{
			$this->_checkboxes = $args[TableWidgetLib::CHECKBOXES];
		}

		// To specify how to represent the dataset (ex: tablesorter, pivotUI, ...)
		if (isset($args[TableWidgetLib::DATASET_REPRESENTATION])
			&& ($args[TableWidgetLib::DATASET_REPRESENTATION] == TableWidgetLib::DATASET_REP_TABLESORTER
			|| $args[TableWidgetLib::DATASET_REPRESENTATION] == TableWidgetLib::DATASET_REP_PIVOTUI
			|| $args[TableWidgetLib::DATASET_REPRESENTATION] == TableWidgetLib::DATASET_REP_TABULATOR))
		{
			$this->_datasetRepresentation = $args[TableWidgetLib::DATASET_REPRESENTATION];
		}

		// To specify options for the dataset representation (ex: tablesorter, pivotUI, ...)
		if (isset($args[TableWidgetLib::DATASET_REP_OPTIONS]) && !isEmptyString($args[TableWidgetLib::DATASET_REP_OPTIONS]))
		{
			$this->_datasetRepresentationOptions = $args[TableWidgetLib::DATASET_REP_OPTIONS];
		}

		// To specify how to represent each record field
		if (isset($args[TableWidgetLib::DATASET_REP_FIELDS_DEFS]) && !isEmptyString($args[TableWidgetLib::DATASET_REP_FIELDS_DEFS]))
		{
			$this->_datasetRepFieldsDefs = $args[TableWidgetLib::DATASET_REP_FIELDS_DEFS];
		}

		// To specify the expiring session time
		if (isset($args[TableWidgetLib::SESSION_TIMEOUT]) && is_numeric($args[TableWidgetLib::SESSION_TIMEOUT]))
		{
			$this->_sessionTimeout = $args[TableWidgetLib::SESSION_TIMEOUT];
		}

		// Parameter is used to define the ecrypted columns
		if (isset($args[TableWidgetLib::ENCRYPTED_COLUMNS])
			&& is_array($args[TableWidgetLib::ENCRYPTED_COLUMNS])
			&& count($args[TableWidgetLib::ENCRYPTED_COLUMNS]) > 0)
		{
			$this->_encryptedColumns = $args[TableWidgetLib::ENCRYPTED_COLUMNS];
		}
	}

	/**
	 * Checks the required parameters used to call this TableWidget
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
			// The unique id parameter is mandatory
			if (!isset($args[TableWidgetLib::TABLE_UNIQUE_ID]))
			{
				show_error('The parameter "'.TableWidgetLib::TABLE_UNIQUE_ID.'" must be specified');
			}

			// The query parameter is mandatory
			if (!isset($args[TableWidgetLib::QUERY]))
			{
				show_error('The parameter "'.TableWidgetLib::QUERY.'" must be specified');
			}

			// The dataset representation parameter is mandatory
			if (!isset($args[TableWidgetLib::DATASET_REPRESENTATION]))
			{
				show_error('The parameter "'.TableWidgetLib::DATASET_REPRESENTATION.'" must be specified');
			}

			// Checks if the dataset representation parameter is valid
			if (isset($args[TableWidgetLib::DATASET_REPRESENTATION])
				&& $args[TableWidgetLib::DATASET_REPRESENTATION] != TableWidgetLib::DATASET_REP_TABLESORTER
				&& $args[TableWidgetLib::DATASET_REPRESENTATION] != TableWidgetLib::DATASET_REP_PIVOTUI
				&& $args[TableWidgetLib::DATASET_REPRESENTATION] != TableWidgetLib::DATASET_REP_TABULATOR)
			{
				show_error(
					'The parameter "'.TableWidgetLib::DATASET_REPRESENTATION.
					'" must be IN ("'
						.TableWidgetLib::DATASET_REP_TABLESORTER.'", "'
						.TableWidgetLib::DATASET_REP_PIVOTUI.'", "'
						.TableWidgetLib::DATASET_REP_TABULATOR.'")'
				);
			}

			// If given the session timeout parameter must be a number
			if (isset($args[TableWidgetLib::SESSION_TIMEOUT]) && !is_numeric($args[TableWidgetLib::SESSION_TIMEOUT]))
			{
				show_error('The parameter "'.TableWidgetLib::SESSION_TIMEOUT.'" must be a number');
			}
		}
	}

	/**
	 * Contains all the logic used to load all the data needed to the TableWidget
	 */
	private function _startTableWidget($tableUniqueId)
	{
		// Looks for expired table widgets in session and drops them
		$this->tablewidgetlib->dropExpiredTableWidgets();

		// Read the all session for this table widget
		$session = $this->tablewidgetlib->getSession();

		// If session is NOT empty -> a table was already loaded
		if ($session != null)
		{
			// Get SESSION_DATASET_RELOAD from the session
			$sessionReloadDataset = $this->tablewidgetlib->getSessionElement(TableWidgetLib::SESSION_DATASET_RELOAD);

			// if Filter changed or reload is forced by parameter then reload the Dataset
			if ($this->_reloadDataset === true || $sessionReloadDataset === true)
			{
				// Set as false to stop changing the dataset
				$this->tablewidgetlib->setSessionElement(TableWidgetLib::SESSION_DATASET_RELOAD, false);

				// Generate dataset query using tables from the session
				$datasetQuery = $this->tablewidgetlib->generateDatasetQuery($this->_query);

				// Then retrieve dataset from DB
				$dataset = $this->tablewidgetlib->getDataset($datasetQuery, $this->_encryptedColumns);

				// Save changes into session if data are valid
				if (!isError($dataset))
				{
					$this->_formatDataset($dataset); // marks rows using markRow and format rowns using formatRow

					// Set the new dataset and its attributes in the session
					$this->tablewidgetlib->setSessionElement(TableWidgetLib::SESSION_METADATA, $this->tablewidgetlib->getExecutedQueryMetaData());
					$this->tablewidgetlib->setSessionElement(TableWidgetLib::SESSION_ROW_NUMBER, count($dataset->retval));
					$this->tablewidgetlib->setSessionElement(TableWidgetLib::SESSION_DATASET, $dataset->retval);
				}
			}
		}

		// If the session is empty -> first time that this table is loaded
		if ($session == null)
		{
			// Generate dataset query
			$datasetQuery = $this->tablewidgetlib->generateDatasetQuery($this->_query);

			// Then retrieve dataset from DB
			$dataset = $this->tablewidgetlib->getDataset($datasetQuery, $this->_encryptedColumns);

			// Save changes into session if data are valid
			if (!isError($dataset))
			{
				$this->_formatDataset($dataset); // marks rows using markRow and format rowns using formatRow

				// Stores an array that contains all the data useful for
				$this->tablewidgetlib->setSession(
					array(
						TableWidgetLib::TABLE_UNIQUE_ID => $tableUniqueId, // table unique id
						TableWidgetLib::SESSION_FIELDS => $this->tablewidgetlib->getExecutedQueryListFields(), // all the fields of the dataset
						TableWidgetLib::SESSION_COLUMNS_ALIASES => $this->_columnsAliases, // all the fields aliases
						TableWidgetLib::SESSION_ADDITIONAL_COLUMNS => $this->_additionalColumns, // additional columns
						TableWidgetLib::SESSION_ENCRYPTED_COLUMNS => $this->_encryptedColumns, // encrypted columns
						TableWidgetLib::SESSION_CHECKBOXES => $this->_checkboxes, // the name of the field used to build the checkboxes column
						TableWidgetLib::SESSION_METADATA => $this->tablewidgetlib->getExecutedQueryMetaData(), // the metadata of the dataset
						TableWidgetLib::SESSION_ROW_NUMBER => count($dataset->retval), // the number of loaded rows by this table
						TableWidgetLib::SESSION_DATASET => $dataset->retval, // the entire dataset
						TableWidgetLib::SESSION_DATASET_RELOAD => false, // if the dataset must be reloaded, not needed the first time
						TableWidgetLib::SESSION_DATASET_REPRESENTATION => $this->_datasetRepresentation, // the choosen dataset representation
						TableWidgetLib::SESSION_DATASET_REP_OPTIONS => $this->_datasetRepresentationOptions, // the choosen dataset representation options
						TableWidgetLib::SESSION_DATASET_REP_FIELDS_DEFS => $this->_datasetRepFieldsDefs // the choosen dataset representation record fields definition
					)
				);
			}
		}

		// NOTE: must the latest operation to be performed in the session to be shure that is always present
		// To be always stored in the session, otherwise is not possible to load data from Filters controller
		$this->tablewidgetlib->setSessionElement(TableWidgetLib::REQUIRED_PERMISSIONS, $this->_requiredPermissions);
		// Renew or set the session expiring time
		$this->tablewidgetlib->setSessionElement(TableWidgetLib::SESSION_TIMEOUT, strtotime('+'.$this->_sessionTimeout.' minutes', time()));
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
	 * Loads a view using the given viewName and eventually other parameters
	 */
	private static function _loadView($viewName, $parameters = null)
	{
		$ci =& get_instance();
		$ci->load->view($viewName, $parameters);
	}
}

