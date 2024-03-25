<?php

class Prestudentstatus_model extends DB_Model
{

	const STATUS_ABBRECHER = 'Abbrecher';
	const STATUS_UNTERBRECHER = 'Unterbrecher';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_prestudentstatus';
		$this->pk = array('ausbildungssemester', 'studiensemester_kurzbz', 'status_kurzbz', 'prestudent_id');
		$this->hasSequence = false;

/*		$CI =& get_instance();

		$CI->load->library('PhrasesLib');

		// Load language phrases
		$CI->loadPhrases([
			'ui', 'lehre'
		]);*/
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

	public function loadLastWithStgDetails($prestudent_id, $studiensemester_kurzbz = null)
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

		$this->db->where_in($this->dbTable . '.status_kurzbz', $this->config->item('antrag_prestudentstatus_whitelist'));

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
		if($statusgrund_kurzbz)
			$this->db->set(
				'statusgrund_id',
				'(SELECT statusgrund_id FROM public.tbl_status_grund WHERE statusgrund_kurzbz =' . $this->db->escape($statusgrund_kurzbz) .')',
				false
			);

		return $this;
	}

	/**
	 * Check if Rolle already exists
	 * @param integer $prestudent_id
	 * @param string $status_kurzbz
	 * @param string $studiensemester_kurzbz
	 * @param integer $ausbildungssemester
	 * @return 1: if Rolle exists, 0: if it doesn't
	 * Copy from studentDBDML.php
	 */
	public function checkIfExistingPrestudentRolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester)
	{
		$qry = "SELECT
					*
				FROM 
				    public.tbl_prestudentstatus
				WHERE
					prestudent_id = ? 
				AND 
				    status_kurzbz = ?
				AND
				    studiensemester_kurzbz = ?
				AND
				    ausbildungssemester = ?";

		$result = $this->execQuery($qry, array($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester));

		if (isError($result))
		{
			return error($result);
		}
		elseif (!hasData($result))
		{
			return success(0);
		}
		else
		{
			return success("1", $this->p->t('studierendenantrag','error_rolleBereitsVorhanden'));
		}
	}

	/**
	 * Check if Rolle there is an existing Bewerberstatus
	 * @param integer $prestudent_id
	 * @return error if no bewerberstatus, success if existing
	 */
	public function checkIfExistingBewerberstatus($prestudent_id, $name = null)
	{
		$qry = "SELECT
					*
				FROM 
				    public.tbl_prestudentstatus
				WHERE
					prestudent_id = ? 
				AND 
				    status_kurzbz = 'Bewerber'";

		$result = $this->execQuery($qry, array($prestudent_id));

		if (isError($result))
		{
			return error($result);
		}
		elseif (!hasData($result))
		{
			$person = $name ? $name : "Person";
			return success("0",  $person . " muss zuerst zum Bewerber gemacht werden!");
		}
		else
		{
			return success($result);
		}
	}

	/**
	 * Check if there is only one prestudentstatus left
	 * @param integer $prestudent_id
	 * @return success("1") if last prestudentstatusentry, else success("0")
	 */
	public function checkIfLastStatusEntry($prestudent_id)
	{
		$qry = "SELECT
					COUNT(*) as anzahl
				FROM 
				    public.tbl_prestudentstatus
				WHERE
					prestudent_id = ? ";

		$result = $this->execQuery($qry, array($prestudent_id));

		if (isError($result))
		{
			return error($result);
		}
		else
		{
			$resultObject = current(getData($result));

			if (property_exists($resultObject, 'anzahl'))
			{
				$anzahl = (int) $resultObject->anzahl;
				if ($anzahl <= 1 )
				{
					return success ("1", "Die letzte Rolle kann nur durch den Administrator geloescht werden");
					//return error("Die letzte Rolle kann nur durch den Administrator geloescht werden");
				}
				else
					return success("0", $anzahl . " Rollen vorhanden");
					//return success($anzahl);

			}
			else
			{
				return error("PrestudentstatusModel: Error During Check if Last Status Entry.");
			}
		}
	}

	/**
	 * Check if Datum New Status is in the Past
	 * @param integer $prestudent_id
	 * @return error if in past
	 */
	public function checkDatumNewStatus($new_status_datum)
	{
		$today = new DateTime('today');
		$new_status_datum = new DateTime($new_status_datum);

		if($new_status_datum < $today)
		{
			return error("Datum eines neuen Statuseintrags darf nicht in der Vergangenheit liegen ");
		}

		else
		{
			return success();
		}
	}

	/**
	 * Check if History of StatusData is valid
	 * @param integer $prestudent_id
	 * @return error if not valid, array StatusArr if valid
	 */
	public function checkIfValidStatusHistory($prestudent_id, $status_kurzbz, $new_status_studiensemester_kurzbz, $new_status_datum, $new_status_ausbildungssemester, $old_status_studiensemester_kurzbz = '', $old_status_ausbildungssemester = '')
	{
		//$isNewStatus = $this->checkIfNewStatus($prestudent_id,  $status_kurzbz);
		$isNewStatus =  $old_status_studiensemester_kurzbz == '' && $old_status_ausbildungssemester == '';

		//get start studiensemester
		$result = $this->StudiensemesterModel->load([
			'studiensemester_kurzbz' => $new_status_studiensemester_kurzbz
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		elseif(!hasData($result)) {
			return error("Kein Eintrag für Studiensemester vorhanden! :-/" . $new_status_studiensemester_kurzbz);
		}

		$studiensemester = current($result->retval);
		$new_status_semesterstart = $studiensemester->start;

		//get all prestudentstati
		//TODO(manu) errorlogic
		$resultPs = $this->getAllPrestudentstatiWithStudiensemester($prestudent_id);
		if (isError($resultPs))
		{
			$resultArr = [];
		}
		if(!hasData($resultPs))
		{
			$resultArr = [];
		}
		else
			$resultArr = $resultPs->retval;

		$statusArr = array();

		$newStatusInserted = false;
		$new_status_datum_form = new DateTime($new_status_datum);
		$new_status_semesterstart_form = new DateTime($new_status_semesterstart);

		foreach ($resultArr as $row)
		{
			$studiensemester_start = new DateTime($row->studiensemester_start);
			$status_datum = new DateTime ($row->datum);


			if ($new_status_datum_form >= $status_datum && $new_status_semesterstart_form >= $studiensemester_start) {

				if (!$newStatusInserted)
				{
					// neuer Status erstmals größer als Datum eines bestehenden Status -> neuen Status EINMALIG einfügen für spätere Statusprüfung
					$neuer_status = new stdClass();
					$neuer_status->status_kurzbz = $status_kurzbz;
					$neuer_status->studiensemester_kurzbz = $new_status_studiensemester_kurzbz;
					$neuer_status->datum = $new_status_datum;
					$neuer_status->ausbildungssemester = $new_status_ausbildungssemester;
					$statusArr[] = $neuer_status;
					$newStatusInserted = true;
				}
				$statusArr[] = $row;
			}
			elseif($new_status_datum_form <= $status_datum && $new_status_semesterstart_form <= $studiensemester_start){
				$statusArr[] = $row;

			}
			else
			{
				return error("Datum des Statuseintrags muss nach dem Statusdatum, das Semesterstartdatum nach Semesterstartdatum des vorherigen Statuseintrags sein");
			}

		}

		$endstatusArr = array('Absolvent', 'Abbrecher');
		// Über alle gespeicherten Status gehen und Statusabfolge prüfen
		for ($i = 0; $i < count($statusArr); $i++) {
			$curr_status = $statusArr[$i];
			$curr_status_kurzbz = $curr_status->status_kurzbz;
			$curr_status_ausbildungssemester = $curr_status->ausbildungssemester;
			$next_idx = $i - 1; //absteigend sortiert, nächster Status ist vorheriger Eintrag
			$next_status = isset($statusArr[$next_idx]) ? $statusArr[$next_idx] : null;

			// Abbrecher- oder Absolventenstatus muss Endstatus sein
			if (isset($next_status) && in_array($curr_status_kurzbz, $endstatusArr)) {

				return error("Nach Abbrecher- und Absolventenstatus darf kein anderer Status mehr eingetragen werden");
			}

			// wenn Unterbrecher auf Unterbrecher folgt, muss Ausbildungssemester gleich sein
			if (
				$curr_status_kurzbz == 'Unterbrecher' && isset($next_status) && $next_status->status_kurzbz == 'Unterbrecher'
				&& $curr_status_ausbildungssemester != $next_status->ausbildungssemester
			)
			{
				return error("Aufeinanderfolgende Unterbrecher müssen gleiches Ausbildungssemester haben");
			}

			// wenn Abbrecher auf Unterbrecher folgt, muss Ausbildungssemester gleich sein
			if (
				isset($next_status) && $curr_status_kurzbz == 'Unterbrecher'
				&& $next_status->status_kurzbz == 'Abbrecher' && $curr_status_ausbildungssemester != $next_status->ausbildungssemester
			)
			{
				return error("Unterbrecher und folgender Abbrecher müssen gleiches Ausbildungssemester haben");
			}

			// keine Studenten nach Diplomand Status
			if (
				isset($next_status) && $curr_status_kurzbz == 'Diplomand' && $next_status->status_kurzbz == 'Student'
			)
			{
				return error("Nach Diplomandenstatus darf kein Studentenstatus mehr eingetragen werden");
			}

		}

		//return $statusArr; //rot
		//return $resultPs->retval; //rot
		//TODO(Manu) check, warum Fehlermeldung bei anderem returnwert!
		return $resultPs;
	}

	public function getAllPrestudentstatiWithStudiensemester($prestudent_id, $old_status_studiensemester_kurzbz = '', $old_status_ausbildungssemester = '')
	{

		//Todo(manu) check isNewStatus
		$isNewStatus =  $old_status_studiensemester_kurzbz == '' && $old_status_ausbildungssemester == '';

		$qry = "
				SELECT public.tbl_prestudentstatus.status_kurzbz, 
				public.tbl_prestudentstatus.studiensemester_kurzbz, 
				public.tbl_prestudentstatus.ausbildungssemester, 
				public.tbl_prestudentstatus.datum, 
				s.start AS studiensemester_start 
				FROM public.tbl_prestudentstatus 
				JOIN public.tbl_studiensemester s USING (studiensemester_kurzbz) 
				WHERE prestudent_id = ? 
				ORDER BY public.tbl_prestudentstatus.datum DESC, 
				public.tbl_prestudentstatus.insertamum DESC, 
				public.tbl_prestudentstatus.ext_id DESC
		";

		$result = $this->execQuery($qry, array($prestudent_id));

		if (isError($result))
		{
			return error($result);
		}
		if (!hasData($result)) {
			return success("0",'No Statusdata vorhanden');
		}

		return $result;

	}


}
