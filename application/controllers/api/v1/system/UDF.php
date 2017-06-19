<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class UDF extends APIv1_Controller
{
	/**
	 * UDF API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// Load model UDF_model
		$this->load->model('system/UDF_model', 'UDFModel');
	}

	/**
	 * @return void
	 */
	public function getUDF()
	{
		$decode = $this->get('decode');
		$schema = $this->get('schema');
		$table = $this->get('table');
		
		$result = error();
		
		if (isset($schema) || isset($table))
		{
			$result = $this->UDFModel->loadWhere(
				array(
					'schema' => $schema,
					'table' => $table
				)
			);
		}
		else
		{
			$result = $this->UDFModel->load();
		}
		
		if ($decode)
		{
			$this->_jsonDecodeResult($result);
		}
		
		$this->response($result, REST_Controller::HTTP_OK);
	}
	
	/**
	 * Decode to json the column jsons for every result set
	 */
	private function _jsonDecodeResult(&$result)
	{
		if (hasData($result))
		{
			for($i = 0; $i < count($result->retval); $i++)
			{
				$obj = $result->retval[$i];
				$obj->jsons = json_decode($obj->jsons);
			}
		}
	}
}