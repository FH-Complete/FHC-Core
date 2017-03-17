<?php

class Prestudent_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_prestudent';
		$this->pk = 'prestudent_id';
	}
	
	/**
	 * @return void
	 */
	public function getLastStatuses($person_id, $studiensemester_kurzbz = null, $studiengang_kz = null, $status_kurzbz = null)
	{
		// Checks if the operation is permitted by the API caller
		if (($isEntitled = $this->isEntitled('public.tbl_prestudent', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('public.tbl_prestudentstatus', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('public.tbl_status', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		
		$query = 'SELECT *
					FROM public.tbl_prestudent p
					JOIN (
							SELECT DISTINCT ON(prestudent_id) *
							  FROM public.tbl_prestudentstatus
							 WHERE prestudent_id IN (SELECT prestudent_id FROM public.tbl_prestudent WHERE person_id = ?)
						  ORDER BY prestudent_id, datum desc, insertamum desc
						) ps USING(prestudent_id)
					JOIN public.tbl_status USING(status_kurzbz)
				   WHERE ps.ausbildungssemester = 1';

		$parametersArray = array($person_id);
		
		if ($studiensemester_kurzbz != '')
		{
			array_push($parametersArray, $studiensemester_kurzbz);
			$query .= ' AND ps.studiensemester_kurzbz = ?';
		}
		
		if (isset($studiengang_kz))
		{
			array_push($parametersArray, $studiengang_kz);
			$query .= ' AND p.studiengang_kz = ?';
		}
		
		if ($status_kurzbz != '')
		{
			array_push($parametersArray, $status_kurzbz);
			$query .= ' AND ps.status_kurzbz = ?';
		}
		
		return $this->execQuery($query, $parametersArray);
	}
	
	/**
	 * 
	 */
	public function updateAufnahmegruppe($prestudentIdArray, $aufnahmegruppe)
	{	
		return $this->execQuery(
			'UPDATE public.tbl_prestudent
				SET aufnahmegruppe_kurzbz = ?
			  WHERE prestudent_id IN ?',
			array(
				$aufnahmegruppe,
				$prestudentIdArray
			)
        );
	}
}