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
		
		// 
		if (isset($person_id))
		{
			// Load model Person_model
			$this->load->model('person/Person_model', 'PersonModel');
			
			$udfs = $this->_fillMissingChkboxUDF($udfs, 'public', 'tbl_person');
			
			$resultPerson = $this->PersonModel->update($person_id, $udfs);
		}
		
		// 
		if (isset($prestudent_id))
		{
			// Load model Prestudent_model
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			
			$udfs = $this->_fillMissingChkboxUDF($udfs, 'public', 'tbl_prestudent');
			
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
	private function _fillMissingChkboxUDF($udfs, $schema, $table)
	{
		$_fillMissingChkboxUDF = $udfs;
		
		$result = $this->load(array($schema, $table));
		if (isSuccess($result) && count($result->retval) == 1)
		{
			$jsons = json_decode($result->retval[0]->jsons);
			
			foreach($jsons as $udfDescription)
			{
				if ($udfDescription->{DB_Model::UDF_TYPE_NAME} == DB_Model::UDF_CHKBOX_TYPE)
				{
					if (!isset($_fillMissingChkboxUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}]))
					{
						$_fillMissingChkboxUDF[$udfDescription->{DB_Model::UDF_ATTRIBUTE_NAME}] = DB_Model::STRING_FALSE;
					}
				}
			}
		}
		
		return $_fillMissingChkboxUDF;
	}
}