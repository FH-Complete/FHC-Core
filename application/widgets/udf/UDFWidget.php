<?php

/**
 * Represents a generic UDF element
 * It's used to render the HTML of a UDF element using widgets
 */
class UDFWidget extends HTMLWidget
{
	private $_schema; // Schema name
	private $_table; // Table name
	private $_primaryKeyName; // Primary key name
	private $_primaryKeyValue; // Primary key value

	/**
	 * Initialize the UDFWidget and starts the execution of the logic
	 */
	public function __construct($name, $args = array())
	{
		parent::__construct($name, $args); // calls the parent's constructor

		$this->load->library('UDFLib'); // Loads the UDFLib that contains all the used logic

		$this->udflib->setUDFUniqueIdByParams($args); // sets the unique id for this UDF

		$this->_initUDFWidget($args); // checks parameters and initialize properties

		$this->_startUDFWidget($args[UDFLib::UDF_UNIQUE_ID]);
	}

	/**
	 * Called by the WidgetLib, it renders the HTML of the UDF
	 */
	public function display($widgetData)
	{
		$this->_ci->udflib->displayUDFWidget($widgetData);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks parameters and initialize all the properties of this UDFWidget
	 */
	private function _initUDFWidget($args)
	{
		$this->_checkParameters($args);

		// If here then everything is ok

		// Initialize class properties
		$this->_schema = null;
		$this->_table = null;
		$this->_primaryKeyName = null;
		$this->_primaryKeyValue = null;

		// Retrieved the
		if (isset($args[UDFLib::SCHEMA_ARG_NAME]))
		{
			$this->_schema = $args[UDFLib::SCHEMA_ARG_NAME];
		}

		// Retrieved the
		if (isset($args[UDFLib::TABLE_ARG_NAME]))
		{
			$this->_table = $args[UDFLib::TABLE_ARG_NAME];
		}

		// Retrieved the
		if (isset($args[UDFLib::PRIMARY_KEY_NAME]))
		{
			$this->_primaryKeyName = $args[UDFLib::PRIMARY_KEY_NAME];
		}

		// Retrieved the
		if (isset($args[UDFLib::PRIMARY_KEY_VALUE]))
		{
			$this->_primaryKeyValue = $args[UDFLib::PRIMARY_KEY_VALUE];
		}
	}

	/**
	 * Checks the required parameters used to call this UDFWidget
	 */
	private function _checkParameters($args)
	{
		if (!is_array($args) || (is_array($args) && count($args) == 0))
		{
			show_error('Second parameter of the widget call must be a NOT empty associative array');
		}
		else
		{
			if (!isset($args[UDFLib::UDF_UNIQUE_ID]))
			{
				show_error('The parameter "'.UDFLib::UDF_UNIQUE_ID.'" must be specified');
			}

			if (!isset($args[UDFLib::SCHEMA_ARG_NAME]))
			{
				show_error('The parameter "'.UDFLib::SCHEMA_ARG_NAME.'" must be specified');
			}

			if (!isset($args[UDFLib::TABLE_ARG_NAME]))
			{
				show_error('The parameter "'.UDFLib::TABLE_ARG_NAME.'" must be specified');
			}

			if (!isset($args[UDFLib::PRIMARY_KEY_NAME]))
			{
				show_error('The parameter "'.UDFLib::PRIMARY_KEY_NAME.'" must be specified');
			}

			if (!isset($args[UDFLib::PRIMARY_KEY_VALUE]))
			{
				show_error('The parameter "'.UDFLib::PRIMARY_KEY_VALUE.'" must be specified');
			}
		}
	}

	/**
	 * Contains all the logic used to load all the data needed to the UDFWidget
	 */
	private function _startUDFWidget($udfUniqueId)
	{
		// Stores an array that contains all the data useful for
		$this->udflib->setSession(
			array(
				UDFLib::UDF_UNIQUE_ID => $udfUniqueId, // table unique id
				UDFLib::SCHEMA_ARG_NAME => $this->_schema, //
				UDFLib::TABLE_ARG_NAME => $this->_table, //
				UDFLib::PRIMARY_KEY_NAME => $this->_primaryKeyName, //
				UDFLib::PRIMARY_KEY_VALUE => $this->_primaryKeyValue //
			)
		);
	}
}

