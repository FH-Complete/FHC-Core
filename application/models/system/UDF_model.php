<?php

class UDF_model extends DB_Model
{
	// String values of booleans
	const STRING_NULL = 'null';
	const STRING_TRUE = 'true';
	const STRING_FALSE = 'false';

	const UDF_DROPDOWN_TYPE = 'dropdown';
	const UDF_MULTIPLEDROPDOWN_TYPE = 'multipledropdown';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_udf';
		$this->pk = array('schema', 'table');
		$this->hasSequence = false;
	}

	/**
	 * Returns all the UDF for this table
	 */
	public function getUDFsDefinitions($schemaAndTable)
	{
		$st = $this->getSchemaAndTable($schemaAndTable);

		$this->addSelect(UDFLib::COLUMN_JSON_DESCRIPTION);
		$udfResults = $this->loadWhere(
			array(
				'schema' => $st->schema,
				'table' => $st->table
			)
		);

		return $udfResults;
	}

	// ------------------------------------------------------------------------------------
	// These methods work only with the this version of FAS, not with the future versions

	/**
	 * Methods to save data from FAS
	 */
	public function saveUDFs($udfs)
	{
		$result = error('No way man!');
		$resultPerson = success('person');
		$resultPrestudent = success('prestudent');

		$person_id = $udfs['person_id'];
		unset($udfs['person_id']);

		$prestudent_id = $udfs['prestudent_id'];
		unset($udfs['prestudent_id']);

		$jsons = array();

		//
		if (isset($person_id))
		{
			// Load model Person_model
			$this->load->model('person/Person_model', 'PersonModel');

			$result = $this->load(array('public', 'tbl_person'));
			if (isSuccess($result) && count($result->retval) == 1)
			{
				$jsons = json_decode($result->retval[0]->jsons);
			}

			$udfs = $this->_fillMissingTextUDF($udfs, $jsons);
			$udfs = $this->_fillMissingChkboxUDF($udfs, $jsons);
			$udfs = $this->_fillMissingDropdownUDF($udfs, $jsons);

			$resultPerson = $this->PersonModel->update($person_id, $udfs);
		}

		//
		if (isset($prestudent_id))
		{
			// Load model Prestudent_model
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');

			$result = $this->load(array('public', 'tbl_prestudent'));
			if (isSuccess($result) && count($result->retval) == 1)
			{
				$jsons = json_decode($result->retval[0]->jsons);
			}

			$udfs = $this->_fillMissingTextUDF($udfs, $jsons);
			$udfs = $this->_fillMissingChkboxUDF($udfs, $jsons);
			$udfs = $this->_fillMissingDropdownUDF($udfs, $jsons);

			$resultPrestudent = $this->PrestudentModel->update($prestudent_id, $udfs);
		}

		if (isSuccess($resultPerson) && isSuccess($resultPrestudent))
		{
			$result = success(array($resultPerson->retval, $resultPrestudent->retval));
		}
		else if(isError($resultPerson))
		{
			$result = $resultPerson;
		}
		else if(isError($resultPrestudent))
		{
			$result = $resultPrestudent;
		}

		return $result;
	}

	/**
	 *
	 */
	private function _fillMissingChkboxUDF($udfs, $jsons)
	{
		$_fillMissingChkboxUDF = $udfs;

		foreach($jsons as $udfDescription)
		{
			if ($udfDescription->{UDFLib::TYPE} == UDFLib::CHKBOX_TYPE)
			{
				if (!isset($_fillMissingChkboxUDF[$udfDescription->{UDFLib::NAME}]))
				{
					$_fillMissingChkboxUDF[$udfDescription->{UDFLib::NAME}] = false;
				}
				else
				{
					if ($_fillMissingChkboxUDF[$udfDescription->{UDFLib::NAME}] == UDF_model::STRING_FALSE)
					{
						$_fillMissingChkboxUDF[$udfDescription->{UDFLib::NAME}] = false;
					}
					else if ($_fillMissingChkboxUDF[$udfDescription->{UDFLib::NAME}] == UDF_model::STRING_TRUE)
					{
						$_fillMissingChkboxUDF[$udfDescription->{UDFLib::NAME}] = true;
					}
				}
			}
		}

		return $_fillMissingChkboxUDF;
	}

	/**
	 *
	 */
	private function _fillMissingDropdownUDF($udfs, $jsons)
	{
		$_fillMissingDropdownUDF = $udfs;

		foreach($jsons as $udfDescription)
		{
			if ($udfDescription->{UDFLib::TYPE} == UDF_model::UDF_DROPDOWN_TYPE
				|| $udfDescription->{UDFLib::TYPE} == UDF_model::UDF_MULTIPLEDROPDOWN_TYPE)
			{
				if (!isset($_fillMissingDropdownUDF[$udfDescription->{UDFLib::NAME}]))
				{
					$_fillMissingDropdownUDF[$udfDescription->{UDFLib::NAME}] = null;
				}
				else if($_fillMissingDropdownUDF[$udfDescription->{UDFLib::NAME}] == UDF_model::STRING_NULL)
				{
					$_fillMissingDropdownUDF[$udfDescription->{UDFLib::NAME}] = null;
				}
			}
		}

		return $_fillMissingDropdownUDF;
	}

	/**
	 *
	 */
	private function _fillMissingTextUDF($udfs, $jsons)
	{
		$_fillMissingTextUDF = $udfs;

		foreach($jsons as $udfDescription)
		{
			if ($udfDescription->{UDFLib::TYPE} == 'textarea'
				|| $udfDescription->{UDFLib::TYPE} == 'textfield')
			{
				if (!isset($_fillMissingTextUDF[$udfDescription->{UDFLib::NAME}]))
				{
					$_fillMissingTextUDF[$udfDescription->{UDFLib::NAME}] = null;
				}
				else if(trim($_fillMissingTextUDF[$udfDescription->{UDFLib::NAME}]) == '')
				{
					$_fillMissingTextUDF[$udfDescription->{UDFLib::NAME}] = null;
				}
			}
		}

		return $_fillMissingTextUDF;
	}
}
