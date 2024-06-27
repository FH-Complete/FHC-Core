<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PrestudentstatusCheckLib
{
	const INTERESSENT_STATUS = 'Interessent';
	const BEWERBER_STATUS = 'Bewerber';
	const AUFGENOMMENER_STATUS = 'Aufgenommener';
	const UNTERBRECHER_STATUS = 'Unterbrecher';
	const STUDENT_STATUS = 'Student';
	const DIPLOMAND_STATUS = 'Diplomand';
	const ABSOLVENT_STATUS = 'Absolvent';
	const ABBRECHER_STATUS = 'Abbrecher';

	private $_ci;
	private $_statusAbfolgeVorStudent = [self::INTERESSENT_STATUS, self::BEWERBER_STATUS, self::AUFGENOMMENER_STATUS];
	private $_endStatusArr = [self::ABSOLVENT_STATUS, self::ABBRECHER_STATUS];

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->_ci->load->model('person/Person_model', 'PersonModel');
		$this->_ci->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->_ci->load->model('organisation/Studienplan_model', 'StudienplanModel');
	}

	/**
	 * Checks if a status add is valid.
	 * @return object error if invalid
	 */
	public function checkStatusAdd(
		$prestudent_id,
		$status_kurzbz,
		$new_status_studiensemester_kurzbz,
		$new_status_datum,
		$new_status_ausbildungssemester,
		$new_studienplan_id
	) {
		$studentName = '';

		$nameRes = $this->_ci->PersonModel->loadPrestudent($prestudent_id);

		if (hasData($nameRes))
		{
			$nameData = getData($nameRes)[0];
			$studentName = $nameData->vorname.' '.$nameData->nachname;
		}

		// Datum des neuen Status darf nicht in Vergangenheit liegen, sonst Probleme wenn neues Datum < Bismeldedatum
		if (new DateTime($new_status_datum) < new DateTime('today'))
			return error($studentName . $this->_ci->p->t('lehre', 'error_entryInPast'));

		return $this->_checkIfValidStatusHistory(
			$prestudent_id,
			$status_kurzbz,
			$new_status_studiensemester_kurzbz,
			$new_status_datum,
			$new_status_ausbildungssemester,
			$new_studienplan_id
		);
	}

	/**
	 * Checks if a status update is valid.
	 * @return error if invalid
	 */
	public function checkStatusUpdate(
		$prestudent_id,
		$status_kurzbz,
		$new_status_studiensemester_kurzbz,
		$new_status_datum,
		$new_status_ausbildungssemester,
		$new_studienplan_id,
		$old_status_studiensemester,
		$old_status_ausbildungssemester
	) {

		return $this->_checkIfValidStatusHistory(
			$prestudent_id,
			$status_kurzbz,
			$new_status_studiensemester_kurzbz,
			$new_status_datum,
			$new_status_ausbildungssemester,
			$new_studienplan_id,
			$old_status_studiensemester,
			$old_status_ausbildungssemester
		);
	}

	/**
	 * Check if History of StatusData is valid
	 * @param integer $prestudent_id
	 * @return error if not valid, array StatusArr if valid
	 */
	private function _checkIfValidStatusHistory(
		$prestudent_id,
		$status_kurzbz,
		$new_status_studiensemester_kurzbz,
		$new_status_datum,
		$new_status_ausbildungssemester,
		$new_studienplan_id,
		$old_status_studiensemester = null,
		$old_status_ausbildungssemester = null
	) {
		//get start studiensemester
		$semResult = $this->_ci->StudiensemesterModel->load([
			'studiensemester_kurzbz' => $new_status_studiensemester_kurzbz
		]);

		if (isError($semResult))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($semResult));
		}

		if (!hasData($semResult)) {
			return error($this->_ci->p->t('lehre', 'error_noStudiensemester') . $new_status_studiensemester_kurzbz);
		}

		$studiensemester = getData($semResult)[0];
		$new_status_semesterstart = $studiensemester->start;

		// get studienplan orgform
		$new_studienplan_orgform_kurzbz = '';
		$this->_ci->StudienplanModel->addSelect('orgform_kurzbz');
		$stplResult = $this->_ci->StudienplanModel->load([
			'studienplan_id' => $new_studienplan_id
		]);

		if (isError($stplResult))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($stplResult));
		}

		if (hasData($stplResult)) $new_studienplan_orgform_kurzbz = getData($stplResult)[0]->orgform_kurzbz;


		//get all prestudentstati
		$resultPs = $this->_ci->PrestudentstatusModel->getAllPrestudentstatiWithStudiensemester($prestudent_id);

		if (isError($resultPs)) return $resultPs;

		$resultArr = hasData($resultPs) ? getData($resultPs) : [];
		$statusArr = [];

		$newStatusInserted = false;
		$new_status_datum_form = new DateTime($new_status_datum);
		$new_status_semesterstart_form = new DateTime($new_status_semesterstart);

		$neuer_status = new stdClass();
		$neuer_status->status_kurzbz = $status_kurzbz;
		$neuer_status->studiensemester_kurzbz = $new_status_studiensemester_kurzbz;
		$neuer_status->datum = $new_status_datum;
		$neuer_status->ausbildungssemester = $new_status_ausbildungssemester;
		$neuer_status->studienplan_orgform_kurzbz = $new_studienplan_orgform_kurzbz;

		foreach ($resultArr as $row)
		{
			// Status, der gerade in Bearbeitung ist, überspringen
			if (isset($old_status_studiensemester)
				&& isset($old_status_ausbildungssemester)
				&& $row->status_kurzbz == $status_kurzbz
				&& $row->studiensemester_kurzbz == $old_status_studiensemester
				&& $row->ausbildungssemester == $old_status_ausbildungssemester)
				continue;

			$studiensemester_start = new DateTime($row->studiensemester_start);
			$status_datum = new DateTime($row->datum);

			if ($new_status_datum_form >= $status_datum && $new_status_semesterstart_form >= $studiensemester_start)
			{
				if (!$newStatusInserted)
				{
					// neuer Status erstmals größer als Datum eines bestehenden Status -> neuen Status EINMALIG einfügen für spätere Statusprüfung
					$neuer_status = new stdClass();
					$neuer_status->status_kurzbz = $status_kurzbz;
					$neuer_status->studiensemester_kurzbz = $new_status_studiensemester_kurzbz;
					$neuer_status->datum = $new_status_datum;
					$neuer_status->ausbildungssemester = $new_status_ausbildungssemester;
					$neuer_status->studienplan_orgform_kurzbz = $new_studienplan_orgform_kurzbz;
					$neuer_status->matrikelnr = $row->matrikelnr;
					$neuer_status->vorname = $row->vorname;
					$neuer_status->nachname = $row->nachname;
					$statusArr[] = $neuer_status;
					$newStatusInserted = true;
				}
				$statusArr[] = $row;
			}
			elseif ($new_status_datum_form <= $status_datum && $new_status_semesterstart_form <= $studiensemester_start)
			{
				$statusArr[] = $row;
			}
			else
			{
				// Zeitabfolge ungültig, Fehler
				return error($this->_ci->p->t('lehre', 'error_statuseintrag_zeitabfolge'));
			}
		}

		// erster Studentstatus
		$ersterStudent = null;

		// Über alle gespeicherten Status gehen und Statusabfolge prüfen
		for ($i = 0; $i < count($statusArr); $i++)
		{
			$curr_status = $statusArr[$i];
			$curr_status_kurzbz = $curr_status->status_kurzbz;
			$curr_status_ausbildungssemester = $curr_status->ausbildungssemester;
			$next_idx = $i - 1; //absteigend sortiert, nächster Status ist vorheriger Eintrag
			$next_status = isset($statusArr[$next_idx]) ? $statusArr[$next_idx] : null;

			$studentName = $curr_status->vorname . ' ' . $curr_status->nachname;

			if ($curr_status_kurzbz == self::STUDENT_STATUS) $ersterStudent = $curr_status;

			// Abbrecher- oder Absolventenstatus muss Endstatus sein
			if (isset($next_status) && in_array($curr_status_kurzbz, $this->_endStatusArr))
			{
				return error($studentName . ' ' . $this->_ci->p->t('lehre', 'error_endstatus'));
			}

			// wenn Unterbrecher auf Unterbrecher folgt, muss Ausbildungssemester gleich sein
			if
				($curr_status_kurzbz == self::UNTERBRECHER_STATUS && isset($next_status) && $next_status->status_kurzbz == self::UNTERBRECHER_STATUS
				&& $curr_status_ausbildungssemester != $next_status->ausbildungssemester)
			{
				return error($studentName . ' ' . $this->_ci->p->t('lehre', 'error_consecutiveUnterbrecher'));
			}

			// wenn Abbrecher auf Unterbrecher folgt, muss Ausbildungssemester gleich sein
			if (isset($next_status)
				&& $curr_status_kurzbz == self::UNTERBRECHER_STATUS
				&& $next_status->status_kurzbz == self::ABBRECHER_STATUS && $curr_status_ausbildungssemester != $next_status->ausbildungssemester)
			{
				return error($studentName . ' ' . $this->_ci->p->t('lehre', 'error_consecutiveUnterbrecherAbbrecher'));
			}

			if (isset($next_status) && $next_status->status_kurzbz == self::STUDENT_STATUS)
			{
				$restliche_status_obj = array_slice($statusArr, $i);
				$restliche_status = array_unique(array_column($restliche_status_obj, 'status_kurzbz'));
				$status_intersected = array_intersect($restliche_status, $this->_statusAbfolgeVorStudent);

				// Vor Studentstatus darf kein Diplomand Status vorhanden sein
				if (in_array(self::DIPLOMAND_STATUS, $restliche_status))
				{
					return error($studentName . ' ' . $this->_ci->p->t('lehre', 'error_consecutiveDiplomandStudent'));
				}

				// Vor Studentstatus müssen bestimmte Status vorhanden sein
				if (array_values($status_intersected) != array_values(array_reverse($this->_statusAbfolgeVorStudent)))
				{
					return error(
						$studentName . ' '
						. $this->_ci->p->t('lehre', 'error_wrongStatusOrderBeforeStudent', array(implode(', ', $this->_statusAbfolgeVorStudent)))
					);
				}
			}
		}

		if (isset($ersterStudent))
		{
			$studentName = $ersterStudent->vorname . ' ' . $ersterStudent->nachname;

			// wenn erster Studentstatus, checken ob Personenkennzeichen passt
			$studienjahrNumber = $this->_ci->StudiensemesterModel->getStudienjahrNumberFromStudiensemester($ersterStudent->studiensemester_kurzbz);

			if ($studienjahrNumber != mb_substr($ersterStudent->matrikelnr, 0, 2))
			{
				return error($studentName . ' ' . $this->_ci->p->t('lehre', 'error_personenkennzeichenPasstNichtZuStudiensemester'));
			}

			// wenn erster Studentstatus, checken ob Orgform des Bewerbers mit Studenten übereinstimmt
			if (!isEmptyArray(
					array_filter(
						$restliche_status_obj,
						function ($s) use ($ersterStudent) {
							return
								$s->status_kurzbz == self::BEWERBER_STATUS
								&& (
									$s->studienplan_orgform_kurzbz != $ersterStudent->studienplan_orgform_kurzbz
								);
						}
					)
				)
			)
			{
				return error($studentName . ' ' . $this->_ci->p->t('lehre', 'error_bewerberOrgformUngleichStudentOrgform'));
			}
		}

		return $resultPs;
	}
}
