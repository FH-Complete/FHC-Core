<?php

/**
 * Interface defining method to implement for issue producer (from core and extensions)
 */
interface IIssueExistsChecker
{
	/**
	 * Checks if an issue exists.
	 * Classes for checking if a certain issue exists implement this method.
	 * @param array $params parameters needed for issue detection
	 * @return object with success(true) if issue exists, success(false) otherwise
	 */
	public function checkIfIssueExists($params);
}
