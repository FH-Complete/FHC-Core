<?php

/**
 * Interface defining method to implement for issue producer (from core and extensions)
 */
interface IIssueExistsChecker
{
	/**
	 * Checks if an issue exists.
	 * @param array $params parameters needed for issue detection
	 * @return object with success(true) if issue exists, success(false) otherwise
	 */
	public function checkIfIssueExists($paramsForChecking);

	/**
	 * Produces an issue.
	 * @param array $params parameters needed for issue detection
	 * @return object with success(true) if issue exists, success(false) otherwise
	 */
	//public function produceIssue($person_id, $oe_kurzbz, $paramsForProducing);
}
