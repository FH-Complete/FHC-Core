<?php
class Studentlehrverband_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studentlehrverband';
		$this->pk = array('studiensemester_kurzbz', 'student_uid');
		$this->hasSequence = false;

		$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
	}

	/**
	 * update Lehrverband and Studenlehrverband
	 *
	 * @param char 					$student_id
	 * @param integer 				$studiengang_kz
	 * @param integer 				$ausbildungssemester
	 * @param char 					$verband
	 * @param char 					$gruppe
	 * @param string 				$studiensemester_kurzbz
	 *
	 * @return success if handling lehrverband, studentlehrverband successfull
	 * 			error if not
	 *
	 */
	public function processStudentlehrverband(
		$student_uid,
		$studiengang_kz,
		$ausbildungssemester,
		$verband,
		$gruppe,
		$studiensemester_kurzbz,
		$status_kurzbz = null
	) {
		$uid = getAuthUID();

		$this->load->model('organisation/Lehrverband_model', 'LehrverbandModel');
		$result = $this->LehrverbandModel->checkIfLehrverbandExists($studiengang_kz, $ausbildungssemester, $verband, $gruppe);
		if (isError($result))
		{
			return error("0", $this->p->t('lehre', 'error_updateLehrverband'));
		}

		if ($result->retval == "0")
		{
			// Übergeordneten Lehrverband check and/or insert
			$result = $this->LehrverbandModel->checkIfLehrverbandExists($studiengang_kz, $ausbildungssemester, '', '');
			if (isError($result)) {
				return error("0", $this->p->t('lehre', 'error_updateLehrverband'));
			}

			if ($result->retval == "0")
			{
				$bezeichnung = (
					$status_kurzbz == PrestudentstatusModel::STATUS_ABBRECHER
					|| $status_kurzbz == Prestudentstatus_model::STATUS_UNTERBRECHER)
					? 'Ab-Unterbrecher' : '';
				$result = $this->LehrverbandModel->insert([
					'studiengang_kz' => $studiengang_kz,
					'semester' => $ausbildungssemester,
					'verband' => '',
					'gruppe' => '',
					'aktiv' => true,
					'bezeichnung' => $bezeichnung
				]);

				if (isError($result))
				{
					return error("0", $this->p->t('lehre', 'error_updateLehrverband'));
				}
			}

			// Lehrverband insert
			if ($verband == 'A')
				$bezeichnung = Prestudentstatus_model::STATUS_ABBRECHER;
			elseif ($verband == 'B')
				$bezeichnung = Prestudentstatus_model::STATUS_BEWERBER;
			else
				$bezeichnung = '';

			$result = $this->LehrverbandModel->insert([
				'studiengang_kz' => $studiengang_kz,
				'semester' => $ausbildungssemester,
				'verband' => $verband,
				'gruppe' => $gruppe,
				'bezeichnung' => $bezeichnung,
				'aktiv' => true
			]);

			if (isError($result)) {
				return error("0", $this->p->t('lehre', 'error_updateLehrverband'));
			}
		}

		// Studentlehrverband insert or update
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$result = $this->StudentlehrverbandModel->checkIfStudentLehrverbandExists($student_uid, $studiensemester_kurzbz);
		if (isError($result))
		{
			return error($result);
		}

		if ($result->retval == "0")
		{
			$result = $this->StudentlehrverbandModel->insert([
				'student_uid' => $student_uid,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'semester' => $ausbildungssemester,
				'verband' => $verband,
				'gruppe' => $gruppe,
				'insertamum' => date('c'),
				'insertvon' => $uid,
				'studiengang_kz' => $studiengang_kz
			]);
			if (isError($result)) {
				return error("0", $this->p->t('lehre', 'error_updateStudentlehrverband'));
			}
		}
		else
		{
			$result = $this->StudentlehrverbandModel->update(
				[
					'student_uid' => $student_uid,
					'studiensemester_kurzbz' => $studiensemester_kurzbz
				],
				[
					'semester' => $ausbildungssemester,
					'verband' => $verband,
					'gruppe' => $gruppe,
					'updateamum' => date('c'),
					'updatevon' => $uid,
					'studiengang_kz' => $studiengang_kz
				]
			);
			if (isError($result)) {
				return error("0", $this->p->t('lehre', 'error_updateStudentlehrverband'));
			}
		}

		if (isError($result)) {
			return error("0", $this->p->t('lehre', 'error_updateStudentlehrverband'));
		}
		else
		{
			return success();
		}
	}
}
