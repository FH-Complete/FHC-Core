<?php

/**
 * Job for resolving core issues
 */
class IssueResolver extends IssueResolver_Controller
{
	public function __construct()
	{
		parent::__construct();

		// set fehler codes which can be resolved by the job, with own resolver defined
		// structure: fehlercode => class (library) name for resolving
		$this->_codeLibMappings = array(
			'CORE_ZGV_0001' => 'CORE_ZGV_0001',
			'CORE_ZGV_0002' => 'CORE_ZGV_0002',
			'CORE_ZGV_0003' => 'CORE_ZGV_0003',
			'CORE_ZGV_0004' => 'CORE_ZGV_0004',
			'CORE_ZGV_0005' => 'CORE_ZGV_0005',
			'CORE_INOUT_0001' => 'CORE_INOUT_0001',
			'CORE_INOUT_0002' => 'CORE_INOUT_0002',
			'CORE_INOUT_0003' => 'CORE_INOUT_0003',
			'CORE_INOUT_0004' => 'CORE_INOUT_0004',
			'CORE_INOUT_0005' => 'CORE_INOUT_0005',
			'CORE_INOUT_0006' => 'CORE_INOUT_0006',
			'CORE_INOUT_0007' => 'CORE_INOUT_0007',
			'CORE_INOUT_0008' => 'CORE_INOUT_0008',
			'CORE_INOUT_0009' => 'CORE_INOUT_0009',
			'CORE_STG_0001' => 'CORE_STG_0001',
			'CORE_STG_0002' => 'CORE_STG_0002',
			'CORE_STG_0003' => 'CORE_STG_0003',
			'CORE_STG_0004' => 'CORE_STG_0004',
			'CORE_STUDENTSTATUS_0002' => 'CORE_STUDENTSTATUS_0002',
			'CORE_STUDENTSTATUS_0003' => 'CORE_STUDENTSTATUS_0003',
			'CORE_STUDENTSTATUS_0004' => 'CORE_STUDENTSTATUS_0004',
			'CORE_STUDENTSTATUS_0005' => 'CORE_STUDENTSTATUS_0005',
			'CORE_STUDENTSTATUS_0006' => 'CORE_STUDENTSTATUS_0006',
			'CORE_STUDENTSTATUS_0007' => 'CORE_STUDENTSTATUS_0007',
			'CORE_STUDENTSTATUS_0008' => 'CORE_STUDENTSTATUS_0008',
			'CORE_STUDENTSTATUS_0009' => 'CORE_STUDENTSTATUS_0009',
			'CORE_STUDENTSTATUS_0010' => 'CORE_STUDENTSTATUS_0010',
			'CORE_STUDENTSTATUS_0011' => 'CORE_STUDENTSTATUS_0011',
			'CORE_STUDENTSTATUS_0012' => 'CORE_STUDENTSTATUS_0012',
			'CORE_STUDENTSTATUS_0013' => 'CORE_STUDENTSTATUS_0013',
			'CORE_STUDENTSTATUS_0014' => 'CORE_STUDENTSTATUS_0014',
			'CORE_STUDENTSTATUS_0015' => 'CORE_STUDENTSTATUS_0015',
			'CORE_STUDENTSTATUS_0016' => 'CORE_STUDENTSTATUS_0016',
			'CORE_PERSON_0001' => 'CORE_PERSON_0001',
			'CORE_PERSON_0002' => 'CORE_PERSON_0002',
			'CORE_PERSON_0003' => 'CORE_PERSON_0003',
			'CORE_PERSON_0004' => 'CORE_PERSON_0004'
		);

		// fehler which are resolved the same way as they are produced
		// structure: fehlercode => class (library) name for resolving
		$this->_codeProducerLibMappings = array(
			'CORE_STUDENTSTATUS_0001' => 'AbbrecherAktiv',
			'CORE_STUDENTSTATUS_0017' => 'BeginndatumVorBismeldung',
			'CORE_STUDENTSTATUS_0018' => 'StudentstatusNachDiplomand',
			'CORE_STUDENTSTATUS_0019' => 'OrgformBewerberUngleichOrgformStudent',
			'CORE_STUDENTSTATUS_0020' => 'StartsemesterUngleichPersonenkennzeichen',
			'CORE_STUDENTSTATUS_0021' => 'AbschlusspruefungOderAbsolventFehlt',
			'CORE_STUDENTSTATUS_0022' => 'FalscheStatusabfolgeVorStudentstatus'
		);
	}
}
