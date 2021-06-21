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
				'updateOehbeitrag' => 'admin:rw',
				'deleteOehbeitrag' => 'admin:rw'
			)
		);

        $this->load->model('codex/Oehbeitrag_model', 'OehbeitragModel');
        $this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->load->library('WidgetLib');
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
		$oehbeitrag_id = $this->input->get('oehbeitrag_id');
		$oehbeitrag_id_arr = isset($oehbeitrag_id) ? array($oehbeitrag_id) : null;

		$studiensemester = array();

		$studiensemesterres = $this->OehbeitragModel->getUnassignedStudiensemester(self::STUDIENSEMESTER_START, $oehbeitrag_id_arr);
		if (isError($studiensemesterres))
		{
			$this->outputJsonError(getError($studiensemesterres));
			return;
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

		if (!$this->_checkAmount($studierendenbeitrag))
			$this->outputJsonError('Ungültiger Studierendenbeitrag');
		elseif (!$this->_checkAmount($versicherung))
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

				$this->outputJson($this->OehbeitragModel->insert($data));
			}
		}
	}

	public function updateOehbeitrag()
	{
		$oehbeitrag_id = $this->input->post("oehbeitrag_id");
		$data = $this->input->post("data");

		if (!is_numeric($oehbeitrag_id) || isEmptyArray($data))
		{
			$this->outputJsonError("Ungültige Parameter");
			return;
		}

		foreach ($data as $idx => $value)
		{
			if ($idx == 'studierendenbeitrag' || $idx == 'versicherung')
			{
				if (!$this->_checkAmount($value))
				{
					$this->outputJsonError("Ungültige(r) $idx");
					return;
				}
			}
			elseif ($idx == 'von_studiensemester_kurzbz' || $idx == 'bis_studiensemester_kurzbz')
			{
				$this->OehbeitragModel->addSelect('von_studiensemester_kurzbz, bis_studiensemester_kurzbz');
				$vonBisStudiensemesterRes = $this->OehbeitragModel->load($oehbeitrag_id);

				if (!hasData($vonBisStudiensemesterRes))
				{
					$this->outputJsonError("Fehler beim Holen des Öhbeitrags");
					return;
				}

				$vonBisStudiensemester = getData($vonBisStudiensemesterRes);

				$von_studiensemester_kurzbz = $idx == 'von_studiensemester_kurzbz' ? $value : $vonBisStudiensemester[0]->von_studiensemester_kurzbz;

				if ($idx == 'bis_studiensemester_kurzbz')
					$bis_studiensemester_kurzbz = $data[$idx] = $value == 'null' ? null : $value;
				else
					$bis_studiensemester_kurzbz = $vonBisStudiensemester[0]->bis_studiensemester_kurzbz;

				$checkStudiensemester = $this->_checkVonBisStudiensemester($von_studiensemester_kurzbz, $bis_studiensemester_kurzbz, $oehbeitrag_id);

				if (isError($checkStudiensemester))
				{
					$this->outputJsonError(getError($checkStudiensemester));
					return;
				}
			}
		}

		$this->outputJson($this->OehbeitragModel->update($oehbeitrag_id, $data));
	}

	public function deleteOehbeitrag()
	{
		$oehbeitrag_id = $this->input->post("oehbeitrag_id");

		$this->outputJson($this->OehbeitragModel->delete($oehbeitrag_id));
	}

	private function _loadOehbeitraege()
	{
		$this->OehbeitragModel->addSelect('oehbeitrag_id, von_studiensemester_kurzbz, bis_studiensemester_kurzbz, studierendenbeitrag, versicherung, sem_von.start as von_datum, sem_bis.ende as bis_datum');
		$this->OehbeitragModel->addJoin('public.tbl_studiensemester sem_von', 'tbl_oehbeitrag.von_studiensemester_kurzbz = sem_von.studiensemester_kurzbz');
		$this->OehbeitragModel->addJoin('public.tbl_studiensemester sem_bis', 'tbl_oehbeitrag.bis_studiensemester_kurzbz = sem_bis.studiensemester_kurzbz', 'LEFT');
		$this->OehbeitragModel->addOrder('sem_von.start', 'DESC');
		return $this->OehbeitragModel->load();
	}

	private function _checkAmount($amount)
	{
		return is_numeric($amount) && (float) $amount <= 99999.99;
	}

	private function _checkVonBisStudiensemester($von_studiensemester_kurzbz, $bis_studiensemester_kurzbz, $oehbeitrag_id = null)
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

		$oehbeitrag_id_arr = isset($oehbeitrag_id) ? array($oehbeitrag_id) : null;

		$assignableRes = $this->OehbeitragModel->checkIfStudiensemesterAssignable(
			$von_studiensemester_kurzbz,
			$bis_studiensemester_kurzbz,
			$oehbeitrag_id_arr
		);

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
