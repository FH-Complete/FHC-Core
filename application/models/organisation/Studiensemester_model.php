<?php

class Studiensemester_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studiensemester';
		$this->pk = 'studiensemester_kurzbz';
	}
	
	public function getLastOrAktSemester($days = 60)
	{
		// Checks if the operation is permitted by the API caller
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['public.tbl_studiensemester'], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['public.tbl_studiensemester'], FHC_MODEL_ERROR);
		
		if (!is_numeric($days))
		{
			$days = 60;
		}
		
		$query = "SELECT studiensemester_kurzbz
					FROM public.tbl_studiensemester
				   WHERE start < NOW() - '" . $days . " DAYS'::INTERVAL
				ORDER BY start DESC
				   LIMIT 1";
		
		$result = $this->db->query($query);
		
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
	
	public function getNextFrom($studiensemester_kurzbz)
	{
		// Checks if the operation is permitted by the API caller
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['public.tbl_studiensemester'], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['public.tbl_studiensemester'], FHC_MODEL_ERROR);
		
		$query = "SELECT studiensemester_kurzbz,
						 start,
						 ende
					FROM public.tbl_studiensemester
				   WHERE start > (
									SELECT ende
									  FROM public.tbl_studiensemester
									 WHERE studiensemester_kurzbz = ?
								)
				ORDER BY start
				   LIMIT 1";
		
		$result = $this->db->query($query, array($studiensemester_kurzbz));
		
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
	
	/**
	 * @return void
	 */
	public function getNearest($semester = '')
	{
		// Checks if the operation is permitted by the API caller
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['public.vw_studiensemester'], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['public.vw_studiensemester'], FHC_MODEL_ERROR);
		
		$query = "SELECT studiensemester_kurzbz,
						 start,
						 ende
					FROM public.vw_studiensemester";
		
		if (is_numeric($semester))
		{
			if ($semester % 2 == 0)
			{
				$ss = 'SS';
			}
			else
			{
				$ss = 'WS';
			}

			$query .= " WHERE SUBSTRING(studiensemester_kurzbz FROM 1 FOR 2) = '" . $ss . "'";
		}
		
		$query .= " ORDER BY delta LIMIT 1";
		
		$result = $this->db->query($query);
		
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}