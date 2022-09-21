<?php

/**
 * Job for producing Plausicheck issues
 */
class PlausiIssueProducer extends JOB_Controller
{
	const PLAUSI_ISSUES_FOLDER = 'issues/plausichecks';
	const EXECUTE_PLAUSI_CHECK_METHOD_NAME = 'executePlausiCheck';

	private $_fehlerLibMappings;

	public function __construct()
	{
		parent::__construct();

		// set fehler which can be produced by the job
		// structure: fehler_kurzbz => class (library) name for resolving
		$this->_fehlerLibMappings = array(
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
		
		$this->load->model('organisation/studiensemester_model', 'StudiensemesterModel');

		$this->load->library('IssuesLib');
	}

	/**
	 * Initializes issue production.
	 */
	public function run($studiensemester_kurzbz = null)
	{
		if (isEmptyString($studiensemester_kurzbz))
		{
			$studiensemesterRes = $this->StudiensemesterModel->getAkt();

			if (isError($studiensemesterRes)) $this->logError(getError($studiensemesterRes));
			
			if (hasData($studiensemesterRes)) $studiensemester_kurzbz = getData($studiensemesterRes);
		}

		$this->logInfo("Plausicheck issue producer job started");

		foreach ($this->_fehlerLibMappings as $fehler_kurzbz => $libName)
		{
			// get path of library for issue to be produced
			$issuesLibPath = DOC_ROOT . 'application/libraries/' . self::PLAUSI_ISSUES_FOLDER . '/';
			//$issuesLibPath = base_url('application/libraries/' . self::PLAUSI_ISSUES_FOLDER . '/');
			$issuesLibFilePath = $issuesLibPath . $libName . '.php';

			// check if library file exists
			if (!file_exists($issuesLibFilePath))
			{
				// log error and continue with next issue if not
				$this->logError("Issue library file " . $issuesLibFilePath . " does not exist");
				continue;
			}

			// load library connected to fehlercode
			$this->load->library(self::PLAUSI_ISSUES_FOLDER . '/'.$libName);

			$lowercaseLibName = mb_strtolower($libName);

			// check if method is defined in library class
			if (!is_callable(array($this->{$lowercaseLibName}, self::EXECUTE_PLAUSI_CHECK_METHOD_NAME)))
			{
				// log error and continue with next issue if not
				$this->logError("Method " . self::EXECUTE_PLAUSI_CHECK_METHOD_NAME . " is not defined in library $lowercaseLibName");
				continue;
			}

			// get the data needed for issue check
			$paramsForCheck = array(
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			);
			
			// call the function for checking for issue production
			$executePlausiRes = $this->{$lowercaseLibName}->{self::EXECUTE_PLAUSI_CHECK_METHOD_NAME}($paramsForCheck);

			if (isError($executePlausiRes))
			{
				$this->logError(getError($executePlausiRes));
			}
			else
			{
				// get the data returned by Plausicheck
				$executePlausiData = getData($executePlausiRes);

				if (is_array($executePlausiData))
				{
					foreach ($executePlausiData as $plausiData)
					{
						// get the data needed for issue production
						$person_id = isset($plausiData['person_id']) ? $plausiData['person_id'] : null;
						$oe_kurzbz = isset($plausiData['oe_kurzbz']) ? $plausiData['oe_kurzbz'] : null;
						$fehlertext_params = isset($plausiData['fehlertext_params']) ? $plausiData['fehlertext_params'] : null;
						$resolution_params = isset($plausiData['resolution_params']) ? $plausiData['resolution_params'] : null;
						
						// write the issue
						$addIssueRes = $this->issueslib->addFhcIssue($fehler_kurzbz, $person_id, $oe_kurzbz, $fehlertext_params, $resolution_params);

						if (isError($addIssueRes))
							$this->logError(getError($addIssueRes));
						else
							$this->logInfo("Plausicheck issue " . $fehler_kurzbz . " successfully produced");
					}
				}
			}
		}

		$this->logInfo("Plausicheck issue producer job stopped");
	}
}
