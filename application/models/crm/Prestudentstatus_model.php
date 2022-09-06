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
		$query = 'SELECT tbl_prestudentstatus.*,
						 tbl_studienplan.bezeichnung AS studienplan_bezeichnung,
						 tbl_studienplan.orgform_kurzbz AS orgform,
						 sprache,
						 tbl_orgform.bezeichnung_mehrsprachig AS bezeichnung_orgform,
						 tbl_status.bezeichnung_mehrsprachig,
						 tbl_status_grund.bezeichnung_mehrsprachig AS bezeichnung_statusgrund
					FROM public.tbl_prestudentstatus
						 LEFT JOIN lehre.tbl_studienplan USING (studienplan_id)
						 JOIN public.tbl_status USING (status_kurzbz)
						 LEFT JOIN public.tbl_status_grund USING (statusgrund_id)
						 LEFT JOIN bis.tbl_orgform ON tbl_studienplan.orgform_kurzbz = bis.tbl_orgform.orgform_kurzbz
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
			$query .= ' AND tbl_prestudentstatus.status_kurzbz = ?';
		}

		$query .= ' ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1';

		return $this->execQuery($query, $parametersArray);
	}

    /**
     * Liefert den Ersten Status eines Prestudenten mit der übergebenen Statuskurzbezeichnung.
     *
     * @param $prestudent_id
     * @param $status_kurzbz
     * @return array
     */
    public function getFirstStatus($prestudent_id, $status_kurzbz)
    {
        $this->addOrder('datum, insertamum, ext_id');
        $this->addLimit(1);

        return $this->loadWhere(array(
            'prestudent_id' => $prestudent_id,
            'status_kurzbz' => $status_kurzbz
        ));
    }

	/**
	 * updateStufe
	 */
	public function updateStufe($prestudentIdArray, $stufe)
	{
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

	/**
	 * Gets Studienordnung for last status of Prestudent
	 * @param $prestudent_id
	 * @return array
	 */
	public function getStudienordnungFromPrestudent($prestudent_id)
	{
		$lastStatus = $this->getLastStatus($prestudent_id);

		if ($lastStatus->error)
		{
			return $lastStatus;
		}

		if (count($lastStatus->retval) > 0)
		{
			$lastStatus = $lastStatus->retval[0];

			$this->addJoin('lehre.tbl_studienplan', 'studienplan_id');
			$this->addJoin('lehre.tbl_studienordnung', 'studienordnung_id');
			return $this->loadWhere(
				array(
					'public.tbl_prestudentstatus.prestudent_id' => $lastStatus->prestudent_id,
					'public.tbl_prestudentstatus.status_kurzbz' => $lastStatus->status_kurzbz,
					'public.tbl_prestudentstatus.studiensemester_kurzbz' => $lastStatus->studiensemester_kurzbz,
					'public.tbl_prestudentstatus.ausbildungssemester' => $lastStatus->ausbildungssemester
				)
			);
		}
		else
		{
			return success(array());
		}
	}

	/**
	 * Gets Studienordnung for last status of Prestudent, including ZGV information text
	 * @param $prestudent_id
	 * @return array
	 */
	public function getStudienordnungWithZgvText($prestudent_id)
	{
		$lastStatus = $this->getLastStatus($prestudent_id);

		if ($lastStatus->error)
		{
			return $lastStatus;
		}

		if (count($lastStatus->retval) > 0)
		{
			$lastStatus = $lastStatus->retval[0];

			$this->addJoin('lehre.tbl_studienplan', 'studienplan_id');
			$this->addJoin('lehre.tbl_studienordnung', 'studienordnung_id');
			$this->addJoin('addon.tbl_stgv_zugangsvoraussetzung', 'studienordnung_id');
			return $this->loadWhere(
				array(
					'public.tbl_prestudentstatus.prestudent_id' => $lastStatus->prestudent_id,
					'public.tbl_prestudentstatus.status_kurzbz' => $lastStatus->status_kurzbz,
					'public.tbl_prestudentstatus.studiensemester_kurzbz' => $lastStatus->studiensemester_kurzbz,
					'public.tbl_prestudentstatus.ausbildungssemester' => $lastStatus->ausbildungssemester
				)
			);
		}
		else
		{
			return success(array());
		}
	}

	/**
	 * getLastStatuses
	 */
	public function getLastStatusPerson($person_id, $studiensemester_kurzbz = null)
	{
		$query = 'SELECT *
					FROM public.tbl_prestudent p
					JOIN (
							SELECT DISTINCT ON(prestudent_id) *
							  FROM public.tbl_prestudentstatus
							 WHERE prestudent_id IN (SELECT prestudent_id FROM public.tbl_prestudent WHERE person_id = ?)
						  ORDER BY prestudent_id, datum desc, insertamum desc
						) ps USING(prestudent_id)
					JOIN public.tbl_status USING(status_kurzbz)';

		$parametersArray = array($person_id);

		if ($studiensemester_kurzbz != '')
		{
			array_push($parametersArray, $studiensemester_kurzbz);
			$query .= ' AND ps.studiensemester_kurzbz = ?';
		}

		return $this->execQuery($query, $parametersArray);
	}
}
