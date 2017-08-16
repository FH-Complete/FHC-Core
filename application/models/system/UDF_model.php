<?php

class UDF_model extends DB_Model
{
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
	 * 
	 */
	public function execQuery($query, $parametersArray = null)
	{
		// 
		if (
			(
				substr($query, 0, 6) == 'SELECT'
				|| substr($query, 0, 4) == 'WITH'
			)
			&&
			(
				!stripos($query, 'INSERT')
				&& !stripos($query, 'UPDATE')
				&& !stripos($query, 'DELETE')
			)
		)
		{
			return parent::execQuery($query, $parametersArray);
		}
		else
		{
			return error('You are allowed to run only query for reading data');
		}
	}
	
	/**
	 * 
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
			
			$udfs = $this->_fillMissingChkboxUDF($udfs, $jsons);
			$udfs = $this->_fillMissingDropdownUDF($udfs, $jsons);
			$udfs = $this->_fillMissingTextUDF($udfs, $jsons);
			
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
			
			$udfs = $this->_fillMissingChkboxUDF($udfs, $jsons);
			$udfs = $this->_fillMissingDropdownUDF($udfs, $jsons);
			$udfs = $this->_fillMissingTextUDF($udfs, $jsons);
			
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
			if ($udfDescription->{DB_Model::UDF_TYPE_NAME} == DB_Model::UDF_CHKBOX_TYPE)
			{
				if (!isset($_fillMissingChkboxUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}]))
				{
					$_fillMissingChkboxUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}] = false;
				}
				else
				{
					if ($_fillMissingChkboxUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}] == DB_Model::STRING_FALSE)
					{
						$_fillMissingChkboxUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}] = false;
					}
					else if ($_fillMissingChkboxUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}] == DB_Model::STRING_TRUE)
					{
						$_fillMissingChkboxUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}] = true;
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
			if ($udfDescription->{DB_Model::UDF_TYPE_NAME} == DB_Model::UDF_DROPDOWN_TYPE
				|| $udfDescription->{DB_Model::UDF_TYPE_NAME} == DB_Model::UDF_MULTIPLEDROPDOWN_TYPE)
			{
				if (!isset($_fillMissingDropdownUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}]))
				{
					$_fillMissingDropdownUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}] = null;
				}
				else if($_fillMissingDropdownUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}] == DB_Model::STRING_NULL)
				{
					$_fillMissingDropdownUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}] = null;
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
			if ($udfDescription->{DB_Model::UDF_TYPE_NAME} == 'textarea'
				|| $udfDescription->{DB_Model::UDF_TYPE_NAME} == 'textfield')
			{
				if (!isset($_fillMissingTextUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}]))
				{
					$_fillMissingTextUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}] = null;
				}
				else if(trim($_fillMissingTextUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}]) == '')
				{
					$_fillMissingTextUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}] = null;
				}
			}
		}
		
		return $_fillMissingTextUDF;
	}
}