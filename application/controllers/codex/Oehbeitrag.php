<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class Oehbeitrag extends Auth_Controller
{
	const STUDIENSEMESTER_START = 'WS2020';

	public function __construct()
    {
        parent::__construct(
			array(
				'index' => 'admin:r',// TODO which Berechtigung?
				'getValidStudiensemester' => 'admin:r',
				'addOehbeitrag' => 'admin:rw',
				'deleteOehbeitrag' => 'admin:rw'
			)
		);

        $this->load->model('codex/Oehbeitrag_model', 'OehbeitragModel');
        $this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
    }

	public function index()
	{
		$oehbeitraege = array();

		$oehbeitragRes = $this->_loadOehbeitraege();

		if (isError($oehbeitragRes))
			show_error(getError($oehbeitragRes));

		if (hasData($oehbeitragRes))
			$oehbeitraege = getData($oehbeitragRes);

		$this->load->view("codex/oehbeitrag.php", array('oehbeitraege' => $oehbeitraege));
	}

	public function getValidStudiensemester()
	{
		$studiensemester = array();

		$studiensemesterres = $this->OehbeitragModel->getUnassignedStudiensemester(self::STUDIENSEMESTER_START);

		if (isError($studiensemesterres))
		{
			$this->outputJsonError(getError($studiensemesterres));
			die();
		}

		if (hasData($studiensemesterres))
			$studiensemester = getData($studiensemesterres);

		$this->outputJsonSuccess($studiensemester);
	}

	public function addOehbeitrag()
	{
		$studierendenbeitrag = $this->input->post('studierendenbeitrag');
		$versicherung = $this->input->post('versicherung');
		$von_studiensemester_kurzbz = $this->input->post('von_studiensemester_kurzbz');
		$bis_studiensemester_kurzbz = $this->input->post('bis_studiensemester_kurzbz');
		if ($bis_studiensemester_kurzbz == 'null')
			$bis_studiensemester_kurzbz = null;

		if (!is_numeric($studierendenbeitrag))
			$this->outputJsonError('Ungültiger Studierendenbeitrag');
		elseif (!is_numeric($versicherung))
			$this->outputJsonError('Ungültige Versicherung');
		else
		{
			$vonBisCheck = $this->_checkVonBisStudiensemester($von_studiensemester_kurzbz, $bis_studiensemester_kurzbz);

			if (isError($vonBisCheck))
				$this->outputJsonError(getError($vonBisCheck));
			else
			{
				$data = array(
					'studierendenbeitrag' => $studierendenbeitrag,
					'versicherung' => $versicherung,
					'von_studiensemester_kurzbz' => $von_studiensemester_kurzbz,
					'bis_studiensemester_kurzbz' => $bis_studiensemester_kurzbz
				);

				$insertRes = $this->OehbeitragModel->insert($data);

				$this->outputJson($insertRes);
			}
		}
	}

	public function deleteOehbeitrag()
	{
		$oehbeitrag_id = $this->input->post("oehbeitrag_id");

		$this->outputJson($this->OehbeitragModel->delete($oehbeitrag_id));
	}

	private function _loadOehbeitraege()
	{
		$this->OehbeitragModel->addJoin('public.tbl_studiensemester', 'tbl_oehbeitrag.von_studiensemester_kurzbz = tbl_studiensemester.studiensemester_kurzbz');
		$this->OehbeitragModel->addOrder('public.tbl_studiensemester.start', 'DESC');
		return $this->OehbeitragModel->load();
	}

	private function _checkVonBisStudiensemester($von_studiensemester_kurzbz, $bis_studiensemester_kurzbz)
	{
		$regex = "/^(WS|SS)\d{4}$/";
		if (!preg_match($regex, $von_studiensemester_kurzbz))
			return error("Ungültiges Von-Studiensemester");

		if (!preg_match($regex, $bis_studiensemester_kurzbz) && $bis_studiensemester_kurzbz != null)
			return error("Ungültiges Bis-Studiensemester");

		$this->StudiensemesterModel->addSelect("start");
		$vonStudiensemesterRes = $this->StudiensemesterModel->load($von_studiensemester_kurzbz);

		if (!hasData($vonStudiensemesterRes))
			return error("Fehler beim Holen von Von-Studiensemester");

		$this->StudiensemesterModel->addSelect("start");
		$bisStudiensemesterRes = $this->StudiensemesterModel->load($bis_studiensemester_kurzbz);

		if (!hasData($bisStudiensemesterRes))
			return error("Fehler beim Holen von Bis-Studiensemester");

		$vonStudiensemester = getData($vonStudiensemesterRes)[0]->start;
		$bisStudiensemester = getData($bisStudiensemesterRes)[0]->start;

		if ($bis_studiensemester_kurzbz != null && new DateTime($vonStudiensemester) > new DateTime($bisStudiensemester))
			return error("Von-Studiensemester größer als Bis-Studiensemester");

		$assignableRes = $this->OehbeitragModel->checkIfStudiensemesterAssignable($von_studiensemester_kurzbz, $bis_studiensemester_kurzbz);

		if (isError($assignableRes))
			return $assignableRes;

		if (hasData($assignableRes))
		{
			$assignable = getData($assignableRes)[0];

			if (!$assignable)
				return error("Keine Zuweisung möglich, Semesterüberschneidung");
		}

		return success("Studiensemester gültig");
	}
}
