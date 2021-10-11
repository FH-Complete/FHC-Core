<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the UDFLib (back-end)
 * Provides data to the ajax get calls about the UDF widget
 * Accepts ajax post calls to save UDFs
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 * NOTE: extends the FHC_Controller instead of the Auth_Controller because the UDFWidget has its
 * 		own permissions check
 */
class UDF extends FHC_Controller
{
	const UDF_UNIQUE_ID = 'udfUniqueId'; // Name of the udf widget unique id

	/**
	 * Calls the parent's constructor and loads the UDFLib
	 */
	public function __construct()
    {
        parent::__construct();

		// Loads authentication library and starts authentication
		$this->load->library('AuthLib');

		// Loads the UDFLib with HTTP GET/POST parameters
		$this->_loadUDFLib();
    }

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Save data about the current UDFs and the result will be written on the output in JSON format
	 */
	public function saveUDFs()
	{
		$udfs = $this->input->post(UDFLib::UDFS_ARG_NAME);

		if (!isEmptyString($udfs))
		{
			$jsonDecodedUDF = json_decode($udfs);
			if ($jsonDecodedUDF != null)
			{
				$this->outputJson($this->udflib->saveUDFs($jsonDecodedUDF));
			}
			else
			{
				$this->outputJsonError('No valid JSON format for UDF values');
			}
		}
		else
		{
			$this->outputJsonError('UDFUniqueId, schema, table name, primary key name and primary key value are mandatory paramenters');
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Loads the UDFLib with the UDF_UNIQUE_ID parameter
	 * If the parameter UDF_UNIQUE_ID is not given then the execution of the controller is terminated and
	 * an error message is printed
	 */
	private function _loadUDFLib()
	{
		// If the parameter UDF_UNIQUE_ID is present in the HTTP GET or POST
		if (isset($_GET[self::UDF_UNIQUE_ID]) || isset($_POST[self::UDF_UNIQUE_ID]))
		{
			// If it is present in the HTTP GET
			if (isset($_GET[self::UDF_UNIQUE_ID]))
			{
				$udfUniqueId = $this->input->get(self::UDF_UNIQUE_ID); // is retrieved from the HTTP GET
			}
			elseif (isset($_POST[self::UDF_UNIQUE_ID])) // Else if it is present in the HTTP POST
			{
				$udfUniqueId = $this->input->post(self::UDF_UNIQUE_ID); // is retrieved from the HTTP POST
			}

			// Loads the UDFLib that contains all the used logic
			$this->load->library('UDFLib');

			$this->udflib->setUDFUniqueId($udfUniqueId);
		}
		else // Otherwise an error will be written in the output
		{
			$this->terminateWithJsonError('Parameter "'.self::UDF_UNIQUE_ID.'" not provided!');
		}
	}
}

