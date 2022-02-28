<?php

/**
 * Interface defining method to implement for issue resolution checker (from core and extensions)
 */
interface IIssueResolvedChecker
{
	/**
	 * Checks if a issue of a certain type is resolved.
	 * Classes for resolving a certain issue type implement this method.
	 * @param array $params parameters needed for issue resolution
	 * @return object with success(true) if issue resolved, success(false) otherwise
	 */
	public function checkIfIssueIsResolved($params);
}