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

class UDF extends API_Controller
{
	/**
	 * UDF API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('UDF' => 'system/udf:rw'));

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
	 *
	 */
	public function postUDF()
	{
		$udfs = $this->post();
		$validation = $this->_validate($udfs);

		if (isSuccess($validation))
		{
			$caller = null;
			if (isset($udfs['caller']))
			{
				$caller = $udfs['caller'];
				unset($udfs['caller']);
			}

			$result = $this->UDFModel->saveUDFs($udfs);

			if ($caller != null)
			{
				$res = 'ERR';
				if (isSuccess($result))
				{
					$res = 'OK';
				}

				redirect($caller.'&res='.$res);
			}
			else
			{
				$this->response($result, REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response($validation, REST_Controller::HTTP_OK);
		}
	}

	/**
	 *
	 */
	private function _validate($udfs)
	{
		$validation = error('person_id or prestudent_id is missing');

		if((isset($udfs['person_id']) && !(is_null($udfs['person_id'])) && ($udfs['person_id'] != ''))
			|| (isset($udfs['prestudent_id']) && !(is_null($udfs['prestudent_id'])) && ($udfs['prestudent_id'] != '')))
		{
			$validation = success(true);
		}

		return $validation;
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
