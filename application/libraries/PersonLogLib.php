<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Logging Actions of Persons
 */
class PersonLogLib
{
	const PARKED_LOGNAME = 'Parked';

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

		$result = $this->ci->PersonLogModel->insert($data);
		if (isSuccess($result))
			return true;
		else
			show_error($result->retval);
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
	 * @return bool wether parking was successfull
	 */
	public function park($person_id, $date, $taetigkeit_kurzbz, $app = 'core', $oe_kurzbz = null, $user = null)
	{
		$logdata = array(
			'name' => self::PARKED_LOGNAME
		);

		$data = array(
			'person_id' => $person_id,
			'zeitpunkt' => $date,
			'taetigkeit_kurzbz' => $taetigkeit_kurzbz,
			'app' => $app,
			'oe_kurzbz' => $oe_kurzbz,
			'logtype_kurzbz' => 'Processstate',
			'logdata' => json_encode($logdata),
			'insertvon' => $user
		);

		$result = $this->ci->PersonLogModel->insert($data);
		if (isSuccess($result))
			return true;
		else
			show_error($result->retval);
	}

	/**
	 * Unparks a person, i.e. removes all log entries in the future
	 * @param $person_id
	 */
	public function unPark($person_id)
	{
		$result = $this->ci->PersonLogModel->getLogsInFuture($person_id);

		$deleted = array();

		if (isSuccess($result))
		{
			if (count($result->retval) > 0)
			{
				foreach ($result->retval as $log)
				{
					$logdata = json_decode($log->logdata);
					if (isset($logdata->name) && $logdata->name === self::PARKED_LOGNAME)
					{
						$delresult = $this->ci->PersonLogModel->deleteLog($log->log_id);
						if (isSuccess($delresult))
							$deleted[] = $log->log_id;
					}
				}
			}
		}
		else
			show_error($result->retval);
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

		if (isSuccess($result))
		{
			if (count($result->retval) > 0)
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
		}
		else
			show_error($result->retval);

		return $parkeddate;
	}
}
