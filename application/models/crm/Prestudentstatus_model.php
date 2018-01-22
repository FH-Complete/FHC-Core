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
	 * getLastStatus
	 */
	public function getLastStatus($prestudent_id, $studiensemester_kurzbz = '', $status_kurzbz = '')
	{
		// Checks if the operation is permitted by the API caller
		if (isError($ent = $this->isEntitled('public.tbl_status', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;
		if (isError($ent = $this->isEntitled('lehre.tbl_studienplan', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;
		if (isError($ent = $this->isEntitled('public.tbl_prestudentstatus', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;

		$query = 'SELECT tbl_prestudentstatus.*,
						 bezeichnung AS studienplan_bezeichnung,
						 tbl_studienplan.orgform_kurzbz as orgform,
						 sprache,
						 tbl_status.bezeichnung_mehrsprachig,
						 tbl_status_grund.bezeichnung_mehrsprachig as bezeichnung_statusgrund
					FROM public.tbl_prestudentstatus LEFT JOIN lehre.tbl_studienplan USING (studienplan_id)
						 JOIN public.tbl_status USING (status_kurzbz)
						 LEFT JOIN public.tbl_status_grund USING (statusgrund_id)
				   WHERE tbl_status.status_kurzbz = tbl_prestudentstatus.status_kurzbz
					 AND prestudent_id = ?';

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

		return $this->execQuery($query, $parametersArray);
	}

	/**
	 * updateStufe
	 */
	public function updateStufe($prestudentIdArray, $stufe)
	{
		if (isError($ent = $this->isEntitled($this->dbTable, PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;
		
		return $this->execQuery(
			'UPDATE public.tbl_prestudentstatus
				SET rt_stufe = ?
			  WHERE status_kurzbz = \'Interessent\'
			    AND prestudent_id IN ?',
			array(
				$stufe,
				$prestudentIdArray
			)
        );
	}

	/**
	 * Get all Prestudent status entries according to the given filter
	 *
	 * @param prestudent_id ID of the Prestudent.
	 * @param $status_kurzbz kurzbz of the status.
	 * @param $ausbildungssemester ausbildungssemester of the status.
	 * @param $studiensemester_kurzbz studiensemster of the status.
	 *
	 * @return result object with all the status entries
	 */
	public function getStatusByFilter($prestudent_id, $status_kurzbz = '', $ausbildungssemester = '', $studiensemester_kurzbz = '')
	{
		// Checks if the operation is permitted by the API caller
		if (isError($ent = $this->isEntitled('public.tbl_prestudentstatus', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;

		$query = '
			SELECT
				tbl_prestudentstatus.*
			FROM
				public.tbl_prestudentstatus
			WHERE
				prestudent_id = ?';

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
		if ($ausbildungssemester != '')
		{
			array_push($parametersArray, $ausbildungssemester);
			$query .= ' AND ausbildungssemester = ?';
		}

		$query .= ' ORDER BY datum DESC, insertamum DESC, ext_id DESC';

		return $this->execQuery($query, $parametersArray);
	}
}
