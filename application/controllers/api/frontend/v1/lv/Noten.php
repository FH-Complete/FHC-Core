<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Noten extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getCertificate' => 'student/noten:r',
			'getTeacherProposal' => 'student/noten:r',
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load Phrases
		$this->loadPhrases([
			'stv',
			'person',
			'lehre'
		]);
	}

	public function getCertificate($lv_id, $studiensemester_kurzbz = null)
	{
		if (is_null($lv_id) || !ctype_digit((string)$lv_id))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$this->load->model('education/lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$result = $this->LehrveranstaltungModel->loadWhere([
			'lehrveranstaltung_id' => $lv_id
		]);

		$lehrveranstaltung = $this->getDataOrTerminateWithError($result);

		if (!$lehrveranstaltung)
			$this->terminateWithSuccess([]);

		if ($studiensemester_kurzbz !== null && !$this->StudiensemesterModel->isValidStudiensemester($studiensemester_kurzbz))
		{
			$this->terminateWithError($studiensemester_kurzbz . ' - ' . $this->p->t('lehre', 'error_noStudiensemester'));
		}

		$result = $this->ZeugnisnoteModel->getZeugnisnoten(null, $studiensemester_kurzbz, $lehrveranstaltung[0]->lehrveranstaltung_id);

		$grades = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($grades);
	}

	public function getTeacherProposal($lv_id, $studiensemester_kurzbz = null)
	{
		if (is_null($lv_id) || !ctype_digit((string)$lv_id))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$this->load->model('education/lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('education/Lvgesamtnote_model', 'LvgesamtnoteModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$result = $this->LehrveranstaltungModel->loadWhere([
			'lehrveranstaltung_id' => $lv_id
		]);

		$lehrveranstaltung = $this->getDataOrTerminateWithError($result);

		if (!$lehrveranstaltung)
			$this->terminateWithSuccess([]);

		if ($studiensemester_kurzbz !== null && !$this->StudiensemesterModel->isValidStudiensemester($studiensemester_kurzbz))
		{
			$this->terminateWithError($studiensemester_kurzbz . ' - ' . $this->p->t('lehre', 'error_noStudiensemester'));
		}

		$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lv_id, null, $studiensemester_kurzbz);

		$grades = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($grades);
	}

}
