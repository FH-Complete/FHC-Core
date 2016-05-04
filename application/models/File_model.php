<?php

class File_model extends DB_Model
{
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 
	 */
	public function saveFile($file = NULL)
	{
		$result = FALSE;
		
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if(isAllowed($this->getUID(), 'file'))
		{
			if($this->_validate($file))
			{
				$result = $this->_write($file);
			}
		}
		
		return $result;
	}
	
	private function _validate($file = NULL)
	{
		return TRUE;
	}
	
	private function _write($file = NULL)
	{
		return TRUE;
	}
}