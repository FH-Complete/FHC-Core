<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the tablewidgetlib (back-end)
 * Provides data to the ajax get calls about the table widget
 * Accepts ajax post calls to change the filter data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 * NOTE: extends the FHC_Controller instead of the Auth_Controller because the TableWidget has its
 * 		own permissions check
 */
class Tables extends FHC_Controller
{
	const TABLE_UNIQUE_ID = 'tableUniqueId'; // Name of the table widget unique id

	/**
	 * Calls the parent's constructor and loads the tablewidgetlib
	 */
	public function __construct()
    {
        parent::__construct();

		// Loads authentication library and starts authentication
		$this->load->library('AuthLib');

		// Loads the tablewidgetlib with HTTP GET/POST parameters
		$this->_loadTableWidgetLib();

		// Checks if the caller is allow to read this data
		$this->_isAllowed();
    }

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Retrieves data about the current filter from the session and will be written on the output in JSON format
	 */
	public function getTable()
	{
		$this->outputJsonSuccess($this->tablewidgetlib->getSession());
	}

	/**
	 * Retrieves the number of records present in the current dataset and will be written on the output in JSON format
	 */
	public function rowNumber()
	{
		$rowNumber = 0;
		$dataset = $this->tablewidgetlib->getSessionElement(TableWidgetLib::SESSION_DATASET);

		if (isset($dataset) && is_array($dataset))
		{
			$rowNumber = count($dataset);
		}

		$this->outputJsonSuccess($rowNumber);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks if the user is allowed to use this filter
	 */
	private function _isAllowed()
	{
		if (!$this->tablewidgetlib->isAllowed())
		{
			$this->terminateWithJsonError('You are not allowed to access to this content');
		}
	}

	/**
	 * Loads the tablewidgetlib with the TABLE_UNIQUE_ID parameter
	 * If the parameter TABLE_UNIQUE_ID is not given then the execution of the controller is terminated and
	 * an error message is printed
	 */
	private function _loadTableWidgetLib()
	{
		// If the parameter TABLE_UNIQUE_ID is present in the HTTP GET or POST
		if (isset($_GET[self::TABLE_UNIQUE_ID]) || isset($_POST[self::TABLE_UNIQUE_ID]))
		{
			// If it is present in the HTTP GET
			if (isset($_GET[self::TABLE_UNIQUE_ID]))
			{
				$tableUniqueId = $this->input->get(self::TABLE_UNIQUE_ID); // is retrieved from the HTTP GET
			}
			elseif (isset($_POST[self::TABLE_UNIQUE_ID])) // Else if it is present in the HTTP POST
			{
				$tableUniqueId = $this->input->post(self::TABLE_UNIQUE_ID); // is retrieved from the HTTP POST
			}

			// Loads the tablewidgetlib that contains all the used logic
			$this->load->library('TableWidgetLib');

			$this->tablewidgetlib->setTableUniqueId($tableUniqueId);
		}
		else // Otherwise an error will be written in the output
		{
			$this->terminateWithJsonError('Parameter "'.self::TABLE_UNIQUE_ID.'" not provided!');
		}
	}
}
