<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Logging Actions of Persons
 */
class PersonLogLib
{
	const PARKED_LOGNAME = 'Parked';
	const ONHOLD_LOGNAME = 'Onhold';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->model('system/PersonLog_model', 'PersonLogModel');
	}

	/**
	 * Writes a Log for a Person
	 * @param int $person_id ID of the Person.
	 * @param string $logtype_kurzbz Type of Log.
	 * @param array $logdata Array of the JSON Data to save.
	 * @param string $taetigkeit_kurzbz
	 * @param string $app Application that log belongs to.
	 * @param string $oe_kurzbz Organisation Unit the Log belongs to.
	 * @param string $user User who created the log.
	 * @return bool true if success
	 */
	public function log($person_id, $logtype_kurzbz, $logdata, $taetigkeit_kurzbz, $app = 'core',  $oe_kurzbz = null, $user = null)
	{
		$data = array(
			'person_id' => $person_id,
			'zeitpunkt' => date('Y-m-d H:i:s'),
			'taetigkeit_kurzbz' => $taetigkeit_kurzbz,
			'app' => $app,
			'oe_kurzbz' => $oe_kurzbz,
			'logtype_kurzbz' => $logtype_kurzbz,
			'logdata' => json_encode($logdata),
			'insertvon' => $user
		);

		return $this->ci->PersonLogModel->insert($data);
	}

	/**
	 * Gets Logs for a Person, filtered by parameters.
	 * Requirements for retrieving log: name is set
	 * @param int $person_id ID of the Person.
	 * @param string $taetigkeit_kurzbz Verarbeitungstätigkeit
	 * @param string $app Name of the App.
	 * @param string $oe_kurzbz Organisation Unit.
	 * @return array
	 */
	public function getLogs($person_id, $taetigkeit_kurzbz = null, $app = null, $oe_kurzbz = null)
	{
		$result = $this->ci->PersonLogModel->filterLog($person_id, $taetigkeit_kurzbz, $app, $oe_kurzbz);

		if (isSuccess($result))
		{
			$decoded_logs = array();
			//decode logs
			foreach ($result->retval as $log)
			{
				$log->logdata = json_decode($log->logdata);
				//requirement - logname not null
				if (isset($log->logdata->name))
				{
					$decoded_logs[] = $log;
				}
			}

			return $decoded_logs;
		}
		else
			show_error($result->retval);
	}

	/**
	 * Parks a person, i.e. marks a person so no actions are expected for the person (e.g. as a prestudent)
	 * Done by adding a logentry in the future
	 * @param $person_id
	 * @param $date
	 * @param $taetigkeit_kurzbz
	 * @param string $app
	 * @param null $oe_kurzbz
	 * @param null $user
	 * @return insert object
	 */
	public function park($person_id, $date, $taetigkeit_kurzbz, $app = 'core', $oe_kurzbz = null, $user = null)
	{
		$onhold = $this->getOnHoldDate($person_id);

		if (hasData($onhold))
			return error("Person already on hold");

		$logjson = array(
			'name' => self::PARKED_LOGNAME
		);

		return $this->_saveLog($person_id, $date, $taetigkeit_kurzbz, $logjson, $app, $oe_kurzbz, $user);
	}

	/**
	 * Unparks a person, i.e. removes all log entries in the future with logname for parking
	 * @param $person_id
	 * @return array with deleted logids
	 */
	public function unPark($person_id)
	{
		$deleted = array();

		$result = $this->ci->PersonLogModel->getLogsInFuture($person_id);
		if (hasData($result))
		{
			foreach ($result->retval as $log)
			{
				$logdata = json_decode($log->logdata);
				if (isset($logdata->name) && $logdata->name === self::PARKED_LOGNAME)
				{
					$delresult = $this->ci->PersonLogModel->deleteLog($log->log_id);
					if (isSuccess($delresult))
					{
						$deleted[] = $log->log_id;
					}
				}
			}
		}

		return success($deleted);
	}

	/**
	 * Gets date until which a person is parked
	 * @param $person_id
	 * @return the date if person is parked, null otherwise
	 */
	public function getParkedDate($person_id)
	{
		$result = $this->ci->PersonLogModel->getLogsInFuture($person_id);

		$parkeddate = null;

		if (hasData($result))
		{
			foreach ($result->retval as $log)
			{
				$logdata = json_decode($log->logdata);
				if (isset($logdata->name) && $logdata->name === self::PARKED_LOGNAME)
				{
					$parkeddate = $log->zeitpunkt;
					break;
				}
			}
		}

		return $parkeddate;
	}

	/**
	 * Sets person on hold, i.e. marks a person so no actions are expected for the person (e.g. as a prestudent).
	 * Done by adding a logentry with a special name. can be undone only manually by clicking button.
	 * @param $person_id
	 * @param $date
	 * @param $taetigkeit_kurzbz
	 * @param string $app
	 * @param null $oe_kurzbz
	 * @param null $user
	 * @return array
	 */
	public function setOnHold($person_id, $date, $taetigkeit_kurzbz, $app = 'core', $oe_kurzbz = null, $user = null)
	{
		$parked = $this->getParkedDate($person_id);

		if (hasData($parked))
			return error("Person already parked");

		$logjson = array(
			'name' => self::ONHOLD_LOGNAME
		);

		return $this->_saveLog($person_id, $date, $taetigkeit_kurzbz, $logjson, $app, $oe_kurzbz, $user);
	}

	/**
	 * Removes on hold status, i.e. removes all log entries with logname for on hold
	 * @param $person_id
	 * @return array
	 */
	public function removeOnHold($person_id)
	{
		$deleted = array();

		$result = $this->ci->PersonLogModel->filterLog($person_id);
		if (hasData($result))
		{
			foreach ($result->retval as $log)
			{
				$logdata = json_decode($log->logdata);
				if (isset($logdata->name) && $logdata->name === self::ONHOLD_LOGNAME)
				{
					$delresult = $this->ci->PersonLogModel->deleteLog($log->log_id);
					if (isSuccess($delresult))
					{
						$deleted[] = $log->log_id;
					}
				}
			}
		}
		return success($deleted);
	}

	/**
	 * Gets date until which a person is on hold
	 * @param $person_id
	 * @return the date if person is on hold, null otherwise
	 */
	public function getOnHoldDate($person_id)
	{
		$result = $this->ci->PersonLogModel->filterLog($person_id);

		$onholddate = null;

		if (hasData($result))
		{
			foreach ($result->retval as $log)
			{
				$logdata = json_decode($log->logdata);
				if (isset($logdata->name) && $logdata->name === self::ONHOLD_LOGNAME)
				{
					$onholddate = $log->zeitpunkt;
					break;
				}
			}
		}

		return $onholddate;
	}

	/**
	 * Saves a log with specified parameters, including a specified log date.
	 * @param $person_id
	 * @param $date
	 * @param $taetigkeit_kurzbz
	 * @param $logjson
	 * @param string $app
	 * @param null $oe_kurzbz
	 * @param null $user
	 * @return mixed
	 */
	private function _saveLog($person_id, $date, $taetigkeit_kurzbz, $logjson, $app = 'core', $oe_kurzbz = null, $user = null)
	{
		$data = array(
			'person_id' => $person_id,
			'zeitpunkt' => $date,
			'taetigkeit_kurzbz' => $taetigkeit_kurzbz,
			'app' => $app,
			'oe_kurzbz' => $oe_kurzbz,
			'logtype_kurzbz' => 'Processstate',
			'logdata' => json_encode($logjson),
			'insertvon' => $user
		);

		return $this->ci->PersonLogModel->insert($data);
	}
}
