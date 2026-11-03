<?php

class TempusIssueProducer extends PlausiIssueProducer_Controller
{
	private $_currentStudiensemester;

	protected $_apps = 'tempus';

	public function __construct()
	{
		parent::__construct();

		$this->load->model('organisation/studiensemester_model', 'StudiensemesterModel');

		$studiensemesterRes = $this->StudiensemesterModel->getNext();
		if (hasData($studiensemesterRes))
			$this->_currentStudiensemester = getData($studiensemesterRes)[0]->studiensemester_kurzbz;
	}

	public function run($studiensemester_kurzbz = null, $studiengang_kz = null)
	{
		if (isEmptyString($studiensemester_kurzbz))
			$studiensemester_kurzbz = $this->_currentStudiensemester;

		$this->producePlausicheckIssues(array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'studiengang_kz' => $studiengang_kz
		));
	}

	public function runEinzeln($fehler_kurzbz, $studiensemester_kurzbz = null)
	{
		if (isEmptyString($studiensemester_kurzbz))
			$studiensemester_kurzbz = $this->_currentStudiensemester;

		$this->load->library('issues/PlausicheckProducerLib', ['fehlerKurzbz' => [$fehler_kurzbz]]);

		$result = $this->plausicheckproducerlib->producePlausicheckIssues(['studiensemester_kurzbz' => $studiensemester_kurzbz]);

		foreach ($result->errors as $error) $this->logError($error);
		foreach ($result->infos as $info) $this->logInfo($info);
	}
}