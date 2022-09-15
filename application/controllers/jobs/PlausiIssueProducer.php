<?php

/**
 * Job for producing Plausicheck issues
 */
class PlausiIssueProducer extends IssueProducer_Controller
{
	public function __construct()
	{
		parent::__construct();

		// set fehler codes which can be produced by the job
		// structure: fehlercode => class (library) name for resolving
		$this->_fehlerLibMappings = array(
			'zgvDatumInZukunft' => 'ZgvDatumInZukunft',
			'zgvDatumVorGeburtsdatum' => 'ZgvDatumVorGeburtsdatum',
			'zgvMasterDatumInZukunft' => 'ZgvMasterDatumInZukunft',
			'zgvMasterDatumVorZgvdatum' => 'ZgvMasterDatumVorZgvdatum',
			'zgvMasterDatumVorGeburtsdatum' => 'ZgvMasterDatumVorGeburtsdatum',
			'keinAufenthaltszweckPlausi' => 'KeinAufenthaltszweckPlausi',
			'zuVieleZweckeIncomingPlausi' => 'ZuVieleZweckeIncomingPlausi',
			'falscherIncomingZweckPlausi' => 'FalscherIncomingZweckPlausi',
			'outgoingAufenthaltfoerderungfehltPlausi' => 'OutgoingAufenthaltfoerderungfehltPlausi',
			'outgoingAngerechneteEctsFehlenPlausi' => 'OutgoingAngerechneteEctsFehlenPlausi',
			'outgoingErworbeneEctsFehlenPlausi' => 'OutgoingErworbeneEctsFehlenPlausi'
		);

		$this->load->model('studiensemester_model', 'StudiensemesterModel');
	}

	public function run()
	{
		$semRes = $this->StudiensemesterModel->getAkt();

		if (hasData($semRes))
		{
			$studiensemester_kurzbz = getData($semRes)[0]->studiensemester_kurzbz;
		}
	}
}
