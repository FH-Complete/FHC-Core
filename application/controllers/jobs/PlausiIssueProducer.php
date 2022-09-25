<?php

/**
 * Job for producing Plausicheck issues
 */
class PlausiIssueProducer extends JOB_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->library('issues/PlausicheckProducerLib');
		$this->load->library('IssuesLib');
	}

	/**
	 * Runs issue production job.
	 */
	public function run($studiensemester_kurzbz = null, $studiengang_kz = null)
	{
		$fehlerKurzbz = $this->plausicheckproducerlib->getFehlerKurzbz();

		$this->logInfo("Plausicheck issue producer job started");

		// get the data returned by Plausicheck
		foreach ($fehlerKurzbz as $fehler_kurzbz)
		{
			// execute the check
			$this->logInfo("Checking " . $fehler_kurzbz . "...");
			$plausicheckRes = $this->plausicheckproducerlib->producePlausicheck($fehler_kurzbz, $studiensemester_kurzbz, $studiengang_kz);

			if (isError($plausicheckRes)) $this->logError(getError($plausicheckRes));

			if (hasData($plausicheckRes))
			{
				$plausicheckData = getData($plausicheckRes);

				foreach ($plausicheckData as $plausiData)
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
						$this->logInfo("Plausicheck issue " . $fehler_kurzbz . " successfully produced, person_id: " . $person_id);
				}
			}
		}

		$this->logInfo("Plausicheck issue producer job stopped");
	}
}
