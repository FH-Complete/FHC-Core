<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PlausicheckProducerLib
{
	const PLAUSI_ISSUES_FOLDER = 'issues/plausichecks';
	const EXECUTE_PLAUSI_CHECK_METHOD_NAME = 'executePlausiCheck';

	private $_ci; // ci instance
	private $_currentStudiensemester; // current Studiensemester

	// set fehler which can be produced by the job
	// structure: fehler_kurzbz => class (library) name for resolving
	private $_fehlerLibMappings = array(
		'StgPrestudentUngleichStgStudent' => 'StgPrestudentUngleichStgStudent'
		//'zgvDatumInZukunft' => 'ZgvDatumInZukunft',
		//'zgvDatumVorGeburtsdatum' => 'ZgvDatumVorGeburtsdatum',
		//'zgvMasterDatumInZukunft' => 'ZgvMasterDatumInZukunft',
		//'zgvMasterDatumVorZgvdatum' => 'ZgvMasterDatumVorZgvdatum',
		//'zgvMasterDatumVorGeburtsdatum' => 'ZgvMasterDatumVorGeburtsdatum',
		//'keinAufenthaltszweckPlausi' => 'KeinAufenthaltszweckPlausi',
		//'zuVieleZweckeIncomingPlausi' => 'ZuVieleZweckeIncomingPlausi',
		//'falscherIncomingZweckPlausi' => 'FalscherIncomingZweckPlausi',
		//'outgoingAufenthaltfoerderungfehltPlausi' => 'OutgoingAufenthaltfoerderungfehltPlausi',
		//'outgoingAngerechneteEctsFehlenPlausi' => 'OutgoingAngerechneteEctsFehlenPlausi',
		//'outgoingErworbeneEctsFehlenPlausi' => 'OutgoingErworbeneEctsFehlenPlausi'
	);

	public function __construct()
	{
		$this->_ci =& get_instance(); // get ci instance

		// load models
		$this->_ci->load->model('organisation/studiensemester_model', 'StudiensemesterModel');

		// load libraries
		$this->_ci->load->library('issues/PlausicheckLib'); // load plausicheck library

		// get current Studiensemester
		$studiensemesterRes = $this->_ci->StudiensemesterModel->getAkt();
		if (hasData($studiensemesterRes)) $this->_currentStudiensemester = getData($studiensemesterRes)[0]->studiensemester_kurzbz;

	}

	public function producePlausicheck($fehler_kurzbz, $studiensemester_kurzbz = null, $studiengang_kz = null)
	{
		$libName = $this->_fehlerLibMappings[$fehler_kurzbz];

		// get Studiensemester
		if (isEmptyString($studiensemester_kurzbz)) $studiensemester_kurzbz = $this->_currentStudiensemester;

		// get path of library for issue to be produced
		$issuesLibPath = DOC_ROOT . 'application/libraries/' . self::PLAUSI_ISSUES_FOLDER . '/';
		//$issuesLibPath = base_url('application/libraries/' . self::PLAUSI_ISSUES_FOLDER . '/');
		$issuesLibFilePath = $issuesLibPath . $libName . '.php';

		// check if library file exists
		if (!file_exists($issuesLibFilePath)) return error("Issue library file " . $issuesLibFilePath . " does not exist");

		// load library connected to fehlercode
		$this->_ci->load->library(self::PLAUSI_ISSUES_FOLDER . '/'.$libName);

		$lowercaseLibName = mb_strtolower($libName);

		// check if method is defined in library class
		if (!is_callable(array($this->_ci->{$lowercaseLibName}, self::EXECUTE_PLAUSI_CHECK_METHOD_NAME)))
			return error("Method " . self::EXECUTE_PLAUSI_CHECK_METHOD_NAME . " is not defined in library $lowercaseLibName");

		// pass the data needed for issue check
		$paramsForCheck = array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'studiengang_kz' => $studiengang_kz
		);

		// call the function for checking for issue production
		return $this->_ci->{$lowercaseLibName}->{self::EXECUTE_PLAUSI_CHECK_METHOD_NAME}($paramsForCheck);
	}

	public function getFehlerKurzbz()
	{
		return array_keys($this->_fehlerLibMappings);
	}
}
