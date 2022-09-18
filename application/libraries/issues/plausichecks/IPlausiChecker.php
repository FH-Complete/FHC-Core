<?php

/**
 * Interface defining method to implement for issue producer (from core and extensions)
 */
interface IPlausiChecker
{
	/**
	 * Executes a plausi check.
	 * @param array $params parameters needed for executing the check
	 * @return array with objects which failed the plausi check
	 */
	public function executePlausiCheck($paramsForChecking);
}
