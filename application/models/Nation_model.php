<?php

class Nation_model extends DB_Model
{
	protected $_bundeslandQuery = "SELECT * FROM bis.tbl_bundesland";
	
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
	public function getAll($notLocked = FALSE, $orderEnglish = FALSE)
	{
		$result = NULL;
		
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if(isAllowed($this->getAddonID(), 'nation'))
		{
			$result = $this->db->query($this->_getNationQuery($notLocked, $orderEnglish));
		}
		
		return $result;
	}
	
	/**
	 * 
	 */
	protected function _getNationQuery($notLocked = FALSE, $orderEnglish = FALSE)
	{
		$qry = "SELECT * FROM bis.tbl_nation";
		
		if($notLocked)
		{
			$qry .= " WHERE sperre IS NULL";
		}
		if(!$orderEnglish)
		{
			$qry .= " ORDER BY kurztext";
		}
		else
		{
			$qry .= " ORDER BY engltext";
		}
		
		return $qry;
	}
	
	/**
	 * 
	 */
	public function getBundesland()
	{
		$result = NULL;
		
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if(isAllowed($this->getAddonID(), 'nation'))
		{
			$result = $this->db->query($this->_bundeslandQuery);
		}
		
		return $result;
	}
}