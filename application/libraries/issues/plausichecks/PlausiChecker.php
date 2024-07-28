<?php

/**
 * class defining ressources and method to use for plausicheck issue producer
 */
abstract class PlausiChecker
{
	protected $_ci; // code igniter instance
	protected $_config; // all applicable configuration parameters for this plausicheck
	protected $_db; // database for queries

	protected $_isForResolutionCheck; // if true, additional parameters only needed for resolution are checked

	protected $_config_params = []; // name of all config params which should be applied for this plausicheck, with sql [name] => [sql]
	protected $_params_for_checking = []; // name of all passed params for checking, with sql [name] => [sql]

	protected $_fehlertext_params = []; // parameter names for fehlertext params used for this plausicheck
	protected $_resolution_params = []; // parameter names for resolution params used for this plausicheck

	public function __construct($params = null)
	{
		$this->_ci =& get_instance(); // get code igniter instance

		// set configuration
		$this->_config = $params['configurationParams'] ?? [];

		$this->_isForResolutionCheck = $params['isForResolutionCheck'] ?? false;

		// get database for queries
		$this->_db = new DB_Model();
	}

	/**
	 * Executes a plausi check.
	 * @param $paramsForChecking array parameters needed for executing the check
	 * @return array with objects which failed the plausi check
	 */
	public function executePlausiCheck($paramsForChecking)
	{
		$results = [];
		$params = [];
		$qry = $this->_base_sql;

		if ($this->_isForResolutionCheck == true)
		{
			foreach ($this->_resolution_params as $resParam)
			{
				if (!isset($paramsForChecking[$resParam]))
					return error("$resParam missing".(isset($paramsForChecking['issue_id']) ? ", issue ID: ".$paramsForChecking['issue_id'] : ""));
			}
		}

		// add config params to query
		if (isset($this->_config_params) && !isEmptyArray($this->_config_params))
		{
			foreach ($this->_config_params as $param_name => $param_sql)
			{
				if (isset($this->_config[$param_name]))
				{
					$qry .= $param_sql;
					$params[] = $this->_config[$param_name];
				}
			}
		}

		// add check params to query
		if (isset($this->_params_for_checking) && !isEmptyArray($this->_params_for_checking))
		{
			foreach ($this->_params_for_checking as $param_name => $param_sql)
			{
				if (isset($paramsForChecking[$param_name]))
				{
					$qry .= $param_sql;
					$params[] = $paramsForChecking[$param_name];
				}
			}
		}

		$result = $this->_db->execReadOnlyQuery($qry, $params);

		if (isError($result)) return $result;

		if (hasData($result))
		{
			$data = getData($result);

			// populate results with data necessary for writing issues
			foreach ($data as $d)
			{
				$fehlertext_params = [];
				$resolution_params = [];

				// add params for error texts
				foreach ($this->_fehlertext_params as $param)
				{
					if (isset($d->{$param})) $fehlertext_params[$param] = $d->{$param};
				}

				// add params for resolution of issue
				foreach ($this->_resolution_params as $param)
				{
					if (isset($d->{$param})) $resolution_params[$param] = $d->{$param};
				}

				$results[] = array(
					'person_id' => $d->person_id,
					'oe_kurzbz' => $d->prestudent_stg_oe_kurzbz,
					'fehlertext_params' => $fehlertext_params,
					'resolution_params' => $resolution_params
				);
			}
		}

		// return the results
		return success($results);
	}
}
