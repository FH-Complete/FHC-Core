<?php

/**
 * Job for producing Plausicheck issues
 */
abstract class PlausiIssueProducer_Controller extends JOB_Controller
{
	protected $_fehlerLibMappings;
	protected $_app;

	public function __construct($app = null)
	{
		parent::__construct();

		// pass extension name if calling from extension
		$extensionName = isset($this->_extensionName) ? $this->_extensionName : null;

		// load libraries
		$this->load->library('issues/PlausicheckProducerLib', array('extensionName' => $extensionName, 'app' => $this->_app));
		$this->load->library('IssuesLib');
	}

	protected function producePlausicheckIssues($params)
	{
		$this->logInfo("Plausicheck issue producer job started");

		// get the data returned by Plausicheck
		foreach ($this->_fehlerLibMappings as $fehler_kurzbz => $libName)
		{
			// execute the check
			$this->logInfo("Checking " . $fehler_kurzbz . "...");
			$plausicheckRes = $this->plausicheckproducerlib->producePlausicheckIssue(
				$libName,
				$fehler_kurzbz,
				$params
			);

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

					// log if error, or log info if inserted new issue
					if (isError($addIssueRes))
						$this->logError(getError($addIssueRes));
					elseif (hasData($addIssueRes) && is_integer(getData($addIssueRes)))
						$this->logInfo("Plausicheck issue " . $fehler_kurzbz . " successfully produced, person_id: " . $person_id);
				}
			}
		}

		$this->logInfo("Plausicheck issue producer job stopped");
	}
}
