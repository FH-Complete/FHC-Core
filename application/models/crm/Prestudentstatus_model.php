<?php

class Prestudentstatus_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_prestudentstatus';
		$this->pk = array('ausbildungssemester', 'studiensemester_kurzbz', 'status_kurzbz', 'prestudent_id');
		$this->hasSequence = false;
	}
	
	/**
	 * @return void
	 */
	public function getLastStatus($prestudent_id, $studiensemester_kurzbz = '', $status_kurzbz = '')
	{
		// Checks if the operation is permitted by the API caller
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_prestudentstatus'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_prestudentstatus'), FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('lehre.tbl_studienplan'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('lehre.tbl_studienplan'), FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_status'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_status'), FHC_MODEL_ERROR);
		
		$query = "SELECT tbl_prestudentstatus.*,
						 bezeichnung AS studienplan_bezeichnung,
						 tbl_status.bezeichnung_mehrsprachig
					FROM public.tbl_prestudentstatus LEFT JOIN lehre.tbl_studienplan USING (studienplan_id)
						 JOIN public.tbl_status USING (status_kurzbz)
				   WHERE tbl_status.status_kurzbz = tbl_prestudentstatus.status_kurzbz
					 AND prestudent_id = ?";

		$parametersArray = array($prestudent_id);
		
		if ($studiensemester_kurzbz != '')
		{
			array_push($parametersArray, $studiensemester_kurzbz);
			$query .= ' AND studiensemester_kurzbz = ?';
		}
		if ($status_kurzbz != '')
		{
			array_push($parametersArray, $status_kurzbz);
			$query .= ' AND status_kurzbz = ?';
		}
		
		$query .= ' ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1';
		
		$result = $this->db->query($query, $parametersArray);
		
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}