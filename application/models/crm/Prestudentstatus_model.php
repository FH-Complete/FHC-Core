<?php

class Prestudentstatus_model extends DB_Model
{

	const STATUS_ABBRECHER = 'Abbrecher';
	const STATUS_UNTERBRECHER = 'Unterbrecher';
	const STATUS_STUDENT = 'Student';
	const STATUS_DIPLOMAND = 'Diplomand';
	const STATUS_ABSOLVENT = 'Absolvent';
	const STATUS_BEWERBER = 'Bewerber';
	const STATUS_AUFGENOMMENER = 'Aufgenommener';
	const STATUS_WARTENDER = 'Wartender';
	const STATUS_ABGEWIESENER = 'Abgewiesener';
	const STATUS_INTERESSENT = 'Interessent';
	const STATUS_INCOMING = 'Incoming';

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

	/**
	 * get Email of relevant Studiengang of prestudent
	 */
	public function getLastStatusWithStgEmail($prestudent_id, $studiensemester_kurzbz = '', $status_kurzbz = '')
	{
		$this->addSelect('tbl_prestudentstatus.*,
						tbl_studienplan.bezeichnung AS studienplan_bezeichnung,
						tbl_orgform.orgform_kurzbz AS orgform,
						tbl_studienplan.sprache,
						tbl_orgform.bezeichnung_mehrsprachig AS bezeichnung_orgform,
						tbl_status.bezeichnung_mehrsprachig,
						tbl_status_grund.bezeichnung_mehrsprachig AS bezeichnung_statusgrund,
						tbl_studiengang.bezeichnung AS stg_bezeichnung,
						tbl_studiengang.email');
		$this->addJoin('lehre.tbl_studienplan', 'studienplan_id', 'LEFT');
		$this->addJoin('lehre.tbl_studienordnung', 'studienordnung_id', 'LEFT');
		$this->addJoin('public.tbl_studiengang', 'studiengang_kz', 'LEFT');
		$this->addJoin('public.tbl_status', 'tbl_status.status_kurzbz = tbl_prestudentstatus.status_kurzbz');
		$this->addJoin('public.tbl_status_grund', 'statusgrund_id', 'LEFT');
		$this->addJoin('bis.tbl_orgform', 'COALESCE(tbl_studienplan.orgform_kurzbz, ' . $this->dbTable . '.orgform_kurzbz, tbl_studiengang.orgform_kurzbz) = tbl_orgform.orgform_kurzbz', 'LEFT');
		$this->db->where('tbl_status.status_kurzbz = tbl_prestudentstatus.status_kurzbz');

		$where = array('prestudent_id' => $prestudent_id);
		if ($studiensemester_kurzbz)
			$where['studiensemester_kurzbz'] = $studiensemester_kurzbz;
		if ($status_kurzbz)
			$where['tbl_prestudentstatus.status_kurzbz'] = $status_kurzbz;

		$this->addOrder('datum', 'DESC');
		$this->addOrder('insertamum', 'DESC');
		$this->addOrder('ext_id', 'DESC');
		$this->addLimit(1);

		return $this->loadWhere($where);
	}

	public function loadLastWithStgDetails($prestudent_id, $studiensemester_kurzbz = null, $max_date = null)
	{
		$this->load->config('studierendenantrag');

		$lang = getUserLanguage();

		$this->addSelect($this->dbTable . '.prestudent_id');
		$this->addSelect($this->dbTable . '.ausbildungssemester AS semester');
		$this->addSelect($this->dbTable . '.studiensemester_kurzbz');
		$this->addSelect('s.matrikelnr');
		$this->addSelect('ss.studienjahr_kurzbz');
		$this->addSelect('pers.vorname');
		$this->addSelect('pers.nachname');
		$this->addSelect('TRIM(CONCAT(pers.vorname, \' \', pers.nachname)) AS name');
		$this->addSelect('pers.person_id');
		$this->addSelect('g.studiengang_kz');
		$this->addSelect('g.bezeichnung');
		$this->addSelect('o.orgform_kurzbz');
		$this->addSelect(
			'o.bezeichnung_mehrsprachig[(SELECT index FROM public.tbl_sprache WHERE sprache=\'' . $lang . '\')] AS orgform_bezeichnung',
			false
		);

		$this->addJoin('public.tbl_student s', 'prestudent_id');
		$this->addJoin('public.tbl_prestudent p', 'prestudent_id');
		$this->addJoin('public.tbl_studiensemester ss', 'studiensemester_kurzbz');
		$this->addJoin('public.tbl_person pers', 'person_id');
		$this->addJoin('public.tbl_studiengang g', 'p.studiengang_kz=g.studiengang_kz');
		$this->addJoin('lehre.tbl_studienplan plan', 'studienplan_id', 'LEFT');
		$this->addJoin('bis.tbl_orgform o', 'COALESCE(plan.orgform_kurzbz, ' . $this->dbTable . '.orgform_kurzbz, g.orgform_kurzbz)=o.orgform_kurzbz');

		$this->addOrder($this->dbTable . '.datum', 'DESC');
		$this->addOrder($this->dbTable . '.insertamum', 'DESC');
		$this->addOrder($this->dbTable . '.ext_id', 'DESC');

		$this->addLimit(1);

		if ($max_date)
			$this->db->where($this->dbTable . '.insertamum <', $max_date);

		$whereArr = [
			$this->dbTable . '.prestudent_id' => $prestudent_id,
			'g.aktiv' => true
		];

		if ($studiensemester_kurzbz !== null)
		{
			$whereArr[$this->dbTable. '.studiensemester_kurzbz'] = $studiensemester_kurzbz;
		}

		return $this->loadWhere($whereArr);
	}

	/**
	 * call like this:
	 * $this->PrestudentstatusModel->withGrund('grund_kurzbz')->update($id, $otherData);
	 * or:
	 * $this->PrestudentstatusModel->withGrund('grund_kurzbz')->insert($otherData);
	 * @param string $statusgrund_kurzbz
	 * @return object $this
	 */
	public function withGrund($statusgrund_kurzbz)
	{
		if ($statusgrund_kurzbz)
			$this->db->set(
				'statusgrund_id',
				'(SELECT statusgrund_id FROM public.tbl_status_grund WHERE statusgrund_kurzbz=' . $this->db->escape($statusgrund_kurzbz) . ')',
				false
			);

		return $this;
	}

	/**
	 * Check if there is only one prestudentstatus left
	 *
	 * @param integer					$prestudent_id
	 * @param string					$studiensemester_kurzbz
	 *
	 * @return stdClass
	 */
	public function checkIfLastStatusEntry($prestudent_id, $studiensemester_kurzbz = null)
	{
		$this->addSelect('COUNT(*) AS anzahl', false);

		if ($studiensemester_kurzbz)
			$this->db->where('studiensemester_kurzbz', $studiensemester_kurzbz);

		$result = $this->loadWhere([
			'prestudent_id' => $prestudent_id
		]);

		if (isError($result))
			return $result;

		$resultObject = current($result->retval);

		$anzahl = (int)$resultObject->anzahl;

		if ($anzahl <= 1)
			return success(true, $this->p->t('lehre', 'error_lastRole'));

		return success(false, $this->p->t('lehre', 'anzahl_existingRoles', ['anzahl' => $anzahl]));
	}

	public function getAllPrestudentstatiWithStudiensemester($prestudent_id)
	{
		$qry = "
				SELECT
					tbl_prestudentstatus.status_kurzbz,
					tbl_prestudentstatus.studiensemester_kurzbz,
					tbl_prestudentstatus.ausbildungssemester,
					tbl_prestudentstatus.datum,
					s.start AS studiensemester_start,
					pl.orgform_kurzbz AS studienplan_orgform_kurzbz,
					stud.matrikelnr,
					pers.vorname,
					pers.nachname
				FROM
					public.tbl_prestudentstatus
					JOIN public.tbl_studiensemester s USING (studiensemester_kurzbz)
					JOIN public.tbl_prestudent USING (prestudent_id)
					JOIN public.tbl_person pers USING (person_id)
					LEFT JOIN public.tbl_student stud USING (prestudent_id)
					LEFT JOIN lehre.tbl_studienplan pl USING (studienplan_id)
				WHERE
					prestudent_id = ?
				ORDER BY
					public.tbl_prestudentstatus.datum DESC,
					public.tbl_prestudentstatus.insertamum DESC,
					public.tbl_prestudentstatus.ext_id DESC
		";

		return $this->execQuery($qry, array($prestudent_id));
	}

	/**
	 * Gets status history of a prestudent
	 * This function uses the language of the logged in user to
	 * translate the given statusgrund
	 *
	 * @param integer				$prestudent_id
	 *
	 * @return stdClass
	 */
	public function getHistoryPrestudent($prestudent_id)
	{
		$lang= getUserLanguage();
		$this->addSelect('tbl_prestudentstatus.prestudent_id');
		$this->addSelect('tbl_prestudentstatus.status_kurzbz');
		$this->addSelect('tbl_prestudentstatus.studiensemester_kurzbz');
		$this->addSelect('tbl_prestudentstatus.ausbildungssemester');
		$this->addSelect('tbl_prestudentstatus.datum');
		$this->addSelect("TO_CHAR(tbl_prestudentstatus.datum::timestamp, 'DD.MM.YYYY') AS format_datum");
		$this->addSelect('tbl_prestudentstatus.insertamum');
		$this->addSelect('tbl_prestudentstatus.insertvon');
		$this->addSelect('tbl_prestudentstatus.updateamum');
		$this->addSelect('tbl_prestudentstatus.updatevon');
		$this->addSelect('tbl_prestudentstatus.orgform_kurzbz');
		$this->addSelect('tbl_prestudentstatus.bestaetigtam');
		$this->addSelect("TO_CHAR(tbl_prestudentstatus.bestaetigtam::timestamp, 'DD.MM.YYYY') AS format_bestaetigtam");
		$this->addSelect('tbl_prestudentstatus.bestaetigtvon');
		$this->addSelect('tbl_prestudentstatus.bewerbung_abgeschicktamum');
		$this->addSelect("TO_CHAR(tbl_prestudentstatus.bewerbung_abgeschicktamum::timestamp, 'DD.MM.YYYY') AS format_bewerbung_abgeschicktamum");
		$this->addSelect('tbl_prestudentstatus.anmerkung');
		$this->addSelect('plan.studienplan_id');
		$this->addSelect('plan.bezeichnung');

		$this->addSelect('grund.beschreibung[(
			SELECT index 
			FROM public.tbl_sprache 
			WHERE sprache=' . $this->escape($lang) . '
		)] AS statusgrund_bezeichnung', false);
		$this->addSelect("CASE 
			WHEN s.student_uid IS NOT NULL 
			AND tbl_prestudentstatus.status_kurzbz IN (" . implode(",", $this->escape([
				'Student',
				'Diplomand',
				'Abbrecher',
				'Absolvent',
				'Ausserodentlicher',
				'Incoming',
				'Outgoing',
				'Unterbrecher'
			])) . ") 
			THEN lv.semester || lv.verband || lv.gruppe 
			ELSE '-'
			END AS lehrverband", false);


		$this->addJoin('lehre.tbl_studienplan plan', 'studienplan_id', 'LEFT');
		$this->addJoin('public.tbl_status_grund grund', 'statusgrund_id', 'LEFT');
		$this->addJoin('public.tbl_student s', 'prestudent_id', 'LEFT');
		$this->addJoin(
			'public.tbl_studentlehrverband lv',
			's.student_uid IS NOT NULL AND s.student_uid=lv.student_uid AND tbl_prestudentstatus.studiensemester_kurzbz=lv.studiensemester_kurzbz',
			'LEFT'
		);

		$this->addOrder('tbl_prestudentstatus.datum', 'DESC');
		$this->addOrder('tbl_prestudentstatus.insertamum', 'DESC');
		$this->addOrder('tbl_prestudentstatus.ext_id', 'DESC');

		return $this->loadWhere([
			'tbl_prestudentstatus.prestudent_id' => $prestudent_id
		]);
	}

	/**
	 * Gets status history of a prestudent for checking purposes.
	 * This function adds the new state or replaces the edited.
	 *
	 * @param integer				$prestudent_id
	 * @param string				$status_kurzbz
	 * @param DateTime				$new_date
	 * @param string				$new_studiensemester_kurzbz
	 * @param integer				$new_ausbildungssemester
	 * @param string				$old_studiensemester_kurzbz
	 * @param integer				$old_ausbildungssemester
	 *
	 * @return stdClass
	 */
	public function getHistoryWithNewOrEditedState(
		$prestudent_id,
		$status_kurzbz,
		$new_date,
		$new_studiensemester_kurzbz,
		$new_ausbildungssemester,
		$old_studiensemester_kurzbz,
		$old_ausbildungssemester
	) {
		$new_date = $new_date->format('Y-m-d');

		$this->addSelect('status_kurzbz');
		$this->addSelect('studiensemester_kurzbz');
		$this->addSelect('ausbildungssemester');
		$this->addSelect('datum');
		$this->addSelect('insertamum');
		$this->addSelect('ext_id');

		if ($old_studiensemester_kurzbz || $old_ausbildungssemester) {
			$this->db->not_group_start();
			$this->db->where('status_kurzbz', $status_kurzbz);
			$this->db->where('studiensemester_kurzbz', $old_studiensemester_kurzbz);
			$this->db->where('ausbildungssemester', $old_ausbildungssemester);
			$this->db->group_end();
		}

		$this->db->where('prestudent_id', $prestudent_id);

		$tmpTable = $this->db->get_compiled_select($this->dbTable);

		$tmpTable .= "UNION
			SELECT " .
				$this->escape($status_kurzbz) . " AS status_kurzbz, " .
				$this->escape($new_studiensemester_kurzbz) . " AS studiensemester_kurzbz, " .
				$this->escape($new_ausbildungssemester) . " AS ausbildungssemester, " .
				$this->escape($new_date) . "::date AS datum," .
				$this->escape(date('c')) . "::date AS insertamum," .
				"NULL AS ext_id";

		$this->addJoin('public.tbl_studiensemester sem', 'studiensemester_kurzbz');

		$this->addOrder('s.datum', 'DESC');
		$this->addOrder('s.insertamum', 'DESC');
		$this->addOrder('s.ext_id', 'DESC');

		$dbTable = $this->dbTable;
		$this->dbTable = "(" . $tmpTable . ") s";

		$result = $this->load();

		$this->dbTable = $dbTable;

		return $result;
	}

	/**
	 * For checks if Orgform of Student status and Bewerber status match.
	 * Returns any Bewerber status that does not match the first Student
	 * status' Orgform.
	 *
	 * @param integer				$prestudent_id
	 *
	 * @return stdClass
	 */
	public function getBewerberWhereOrgformNotStudent($prestudent_id)
	{
		$this->addSelect('plan.orgform_kurzbz');

		$this->addJoin('lehre.tbl_studienplan plan', 'studienplan_id', 'LEFT');

		$this->addOrder('tbl_prestudentstatus.datum', 'DESC');
		$this->addOrder('tbl_prestudentstatus.insertamum', 'DESC');
		$this->addOrder('tbl_prestudentstatus.ext_id', 'DESC');
		
		$this->addLimit(1);

		$this->db->where('prestudent_id', $prestudent_id);
		$this->db->where('status_kurzbz', self::STATUS_STUDENT);
		
		$sql = $this->db->get_compiled_select($this->dbTable);

		$this->addJoin('lehre.tbl_studienplan plan', 'studienplan_id', 'LEFT');

		$this->db->where('plan.orgform_kurzbz !=', '(' . $sql . ')', false);
		return $this->loadWhere([
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => self::STATUS_BEWERBER
		]);
	}
}
