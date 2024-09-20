<?php

/**
 * Job for producing core Plausicheck issues
 */
class PlausiIssueProducer extends PlausiIssueProducer_Controller
{
	private $_currentStudiensemester;
	protected $_app = 'core';

	public function __construct()
	{
		parent::__construct();

		$this->load->library('issues/PlausicheckDefinitionLib');

		// load models
		$this->load->model('organisation/studiensemester_model', 'StudiensemesterModel');

		// get current Studiensemester
		$studiensemesterRes = $this->StudiensemesterModel->getAkt();
		if (hasData($studiensemesterRes)) $this->_currentStudiensemester = getData($studiensemesterRes)[0]->studiensemester_kurzbz;

		// set fehler which can be produced by the job
		// structure: fehler_kurzbz => class (library) name for resolving
		$this->_fehlerLibMappings = $this->plausicheckdefinitionlib->getFehlerLibMappings();
	}

	/**
	 * Runs issue production job.
	 * @param studiensemester_kurzbz string job is run for students of a certain semester.
	 * @param studiengang_kz int job is run for students of certain Studiengang.
	 */
	public function run($studiensemester_kurzbz = null, $studiengang_kz = null)
	{
		// get Studiensemester
		if (isEmptyString($studiensemester_kurzbz)) $studiensemester_kurzbz = $this->_currentStudiensemester;

		// producing issues for semester and optionally Studiengang
		$this->producePlausicheckIssues(array('studiensemester_kurzbz' => $studiensemester_kurzbz, 'studiengang_kz' => $studiengang_kz));
	}
}
