<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * TableWidget logic
 */
class TableWidgetLib
{
	const TABLE_UNIQUE_ID = 'tableUniqueId'; // TableWidget unique id

	// Session parameters names
	const SESSION_NAME = 'FHC_TABLE_WIDGET'; // Table session name
	const SESSION_FIELDS = 'fields';
	const SESSION_COLUMNS_ALIASES = 'columnsAliases';
	const SESSION_ADDITIONAL_COLUMNS = 'additionalColumns';
	const SESSION_CHECKBOXES = 'checkboxes';
	const SESSION_METADATA = 'datasetMetadata';
	const SESSION_DATASET = 'dataset';
	const SESSION_ROW_NUMBER = 'rowNumber';
	const SESSION_RELOAD_DATASET = 'reloadDataset';
	const SESSION_DATASET_REPRESENTATION = 'datasetRepresentation';
	const SESSION_DATASET_REP_OPTIONS = 'datasetRepresentationOptions';
	const SESSION_DATASET_REP_FIELDS_DEFS = 'datasetRepresentationFieldsDefinitions';

	// Alias for the dynamic table used to retrieve the dataset
	const DATASET_TABLE_ALIAS = 'datasetTableWidget';

	// Parameters names...

	// ...to reload the dataset
	const DATASET_RELOAD_PARAMETER = 'reloadDataset';

	// ...to specify permissions that are needed to use this TableWidget
	const REQUIRED_PERMISSIONS_PARAMETER = 'requiredPermissions';

	// ...stament to retrieve the dataset
	const QUERY_PARAMETER = 'query';

	// ...to specify more columns or aliases for them
	const ADDITIONAL_COLUMNS = 'additionalColumns';
	const CHECKBOXES = 'checkboxes';
	const COLUMNS_ALIASES = 'columnsAliases';

	// ...to format/mark records of a dataset
	const FORMAT_ROW = 'formatRow';
	const MARK_ROW = 'markRow';

	// ...to specify how to represent the dataset (ex: tablesorter, pivotUI, ...)
	const DATASET_REPRESENTATION = 'datasetRepresentation';
	const DATASET_REP_OPTIONS = 'datasetRepOptions';
	const DATASET_REP_FIELDS_DEFS = 'datasetRepFieldsDefs';

	// Different dataset representations
	const DATASET_REP_TABLESORTER = 'tablesorter';
	const DATASET_REP_PIVOTUI = 'pivotUI';
	const DATASET_REP_TABULATOR = 'tabulator';

	const PERMISSION_TABLE_METHOD = 'TableWidget'; // Name for fake method to be checked by the PermissionLib
	const PERMISSION_TYPE = 'rw';

	private $_ci; // Code igniter instance
	private $_tableUniqueId; // unique id for this table widget

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
		if ($rq == null) $rq = $this->getSessionElement(self::REQUIRED_PERMISSIONS_PARAMETER);

		return $this->_ci->permissionlib->hasAtLeastOne($rq, self::PERMISSION_TABLE_METHOD, self::PERMISSION_TYPE);
	}

	/**
	 * Wrapper method to the session helper funtions to retrieve the whole session for this filter
	 */
	public function getSession()
	{
		return getSessionElement(self::SESSION_NAME, $this->_tableUniqueId);
	}

	/**
	 * Wrapper method to the session helper funtions to retrieve one element from the session of this filter
	 */
	public function getSessionElement($name)
	{
		$session = getSessionElement(self::SESSION_NAME, $this->_tableUniqueId);

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
		setSessionElement(self::SESSION_NAME, $this->_tableUniqueId, $data);
	}

	/**
	 * Wrapper method to the session helper funtions to set one element in the session for this filter
	 */
	public function setSessionElement($name, $value)
	{
		$session = getSessionElement(self::SESSION_NAME, $this->_tableUniqueId);

		$session[$name] = $value;

		setSessionElement(self::SESSION_NAME, $this->_tableUniqueId, $session); // stores the single value
	}

	/**
	 * Generate the query to retrieve the dataset for a filter
	 */
	public function generateDatasetQuery($query)
	{
		return 'SELECT * FROM ('.$query.') '.self::DATASET_TABLE_ALIAS;
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
	 * Retrieves metadata from the last executed query
	 */
	public function getExecutedQueryMetaData()
	{
		return $this->_ci->FiltersModel->getExecutedQueryMetaData();
	}

	/**
	 * Retrieves the list of fields from the last executed query
	 */
	public function getExecutedQueryListFields()
	{
		return $this->_ci->FiltersModel->getExecutedQueryListFields();
	}

	/**
	 * Return an unique string that identify this filter widget
	 * NOTE: The default value is the URI where the FilterWidget is called
	 * If the fhc_controller_id is present then is also used
	 */
	public function setTableUniqueIdByParams($params)
	{
		if ($params != null
			&& is_array($params)
			&& isset($params[self::TABLE_UNIQUE_ID])
			&& !isEmptyString($params[self::TABLE_UNIQUE_ID]))
		{
			$tableUniqueId = $this->_ci->router->directory.$this->_ci->router->class.'/'.
				$this->_ci->router->method.'/'.
				$params[self::TABLE_UNIQUE_ID];

			$this->setTableUniqueId($tableUniqueId);
		}
	}

	/**
	 * Set the _tableUniqueId property
	 */
	public function setTableUniqueId($tableUniqueId)
	{
		$this->_tableUniqueId = $tableUniqueId;
	}
}
