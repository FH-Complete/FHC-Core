<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Logging Actions of Persons
 */
class PersonLogLib
{
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
	 * @param string $app Application that log belongs to.
	 * @param string $oe_kurzbz Organisation Unit the Log belongs to.
	 * @param string $user User who created the log.
	 * @return boolean true if success
	 */
	public function log($person_id, $logtype_kurzbz, $logdata, $app = 'core', $oe_kurzbz = null, $user = null)
	{
		$data = array(
			'person_id' => $person_id,
			'zeitpunkt' => date('Y-m-d H:i:s'),
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
	 * @param string $app Name of the App.
	 * @param string $oe_kurzbz Organisations Unit.
	 * @return object $result
	 */
	public function getLogs($person_id, $app = null, $oe_kurzbz = null)
	{
		$result = $this->ci->PersonLogModel->filterLog($person_id, $app, $oe_kurzbz);

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
}
