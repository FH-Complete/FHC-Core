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
	}

	/**
	 * Check if Studentlehrverband already exists
	 * @param string $student_id
	 * @param string $studiensemester_kurzbz
	 * @return 1: if Rolle exists, 0: if it doesn't
	 */
	public function checkIfStudentlehrverbandExists($student_uid, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					*
				FROM 
				    public.tbl_studentlehrverband
				WHERE
					student_uid = ? 
				AND
				    studiensemester_kurzbz = ?";

		$result = $this->execQuery($qry, array($student_uid, $studiensemester_kurzbz));

		if (isError($result))
		{
			return error($result);
		}
		elseif (!hasData($result))
		{
			return success("0", $this->p->t('lehre','error_noStudentlehrverband'));
		}
		else
		{
			return success("1", $this->p->t('lehre','error_updateStudentlehrverband'));
		}
	}

	public function processStudentlehrverband($student_uid, $studiengang_kz, $ausbildungssemester, $verband, $gruppe, $studiensemester_kurzbz)
	{
		$uid = getAuthUID();
		$this->db->trans_begin(); // Start Transaktion

		$this->load->model('organisation/Lehrverband_model', 'LehrverbandModel');
		$result = $this->LehrverbandModel->checkIfLehrverbandExists($studiengang_kz, $ausbildungssemester, $verband, $gruppe);
		if (isError($result))
		{
			$this->db->trans_rollback();
			return error("0", $this->p->t('lehre','error_updateLehrverband'));
		}

		if ($result->retval == "0") {

			// Übergeordneten Lehrverband check and/or insert
			$result = $this->LehrverbandModel->checkIfLehrverbandExists($studiengang_kz, $ausbildungssemester, '', '');
			if (isError($result)) {
				$this->db->trans_rollback();
				return error("0", $this->p->t('lehre','error_updateLehrverband'));
			}

			if ($result->retval == "0")
			{
				$result = $this->LehrverbandModel->insert([
					'studiengang_kz' => $studiengang_kz,
					'semester' => $ausbildungssemester,
					'verband' => '',
					'gruppe' => '',
					'aktiv' => true,
					'bezeichnung' => 'Ab-Unterbrecher'
				]);

				if ($this->db->trans_status() === false || isError($result))
				{
					$this->db->trans_rollback();
					return error("0", $this->p->t('lehre','error_updateLehrverband'));
				}
			}



			// Lehrverband insert
			$bezeichnung = $verband == 'A' ? ' Abbrecher' : 'Unterbrecher';
			$result = $this->LehrverbandModel->insert([
				'studiengang_kz' => $studiengang_kz,
				'semester' => $ausbildungssemester,
				'verband' => $verband,
				'gruppe' => $gruppe,
				'bezeichnung' => $bezeichnung,
				'aktiv' => true
			]);

			if ($this->db->trans_status() === false || isError($result)) {
				$this->db->trans_rollback();
				return error("0", $this->p->t('lehre','error_updateLehrverband'));
			}
		}


		// Studentlehrverband insert or update
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$result = $this->StudentlehrverbandModel->checkIfStudentLehrverbandExists($student_uid, $studiensemester_kurzbz);
		if (isError($result))
		{
			$this->db->trans_rollback();
			return error(getError($result));
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
			if ($this->db->trans_status() === false || isError($result)) {
				$this->db->trans_rollback();
				return error("0", $this->p->t('lehre','error_updateStudentlehrverband'));
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
			if ($this->db->trans_status() === false || isError($result)) {
				$this->db->trans_rollback();
				return error("0", $this->p->t('lehre','error_updateStudentlehrverband'));
			}
		}

		// finish transaktion
		if ($this->db->trans_status() === false || isError($result)) {
			$this->db->trans_rollback();
			return error("0", $this->p->t('lehre','error_updateStudentlehrverband'));
		} else {
			$this->db->trans_commit();
			return success();
		}
	}
}
