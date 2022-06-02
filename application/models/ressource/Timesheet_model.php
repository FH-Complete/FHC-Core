<?php
class Timesheet_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'addon.tbl_casetime_timesheet';
		$this->pk = 'timesheet_id';
	}

	public function getPendingTimesheets()
	{
		$qry = "SELECT
					DISTINCT uid
				FROM addon.tbl_casetime_timesheet
				WHERE abgeschicktamum IS NOT NULL
				AND genehmigtamum IS NULL
				ORDER BY uid";
		return $this->execQuery($qry);
	}

	public function getUidofMissingTimesheetsLastMonth()
	{
		$qry = "SELECT
					DISTINCT uid
				FROM addon.tbl_casetime_timesheet
				WHERE date_trunc('month',datum) = (date_trunc('month', current_date-interval '1' month))
				AND abgeschicktamum IS NULL
				ORDER BY uid";
		return $this->execQuery($qry);
	}

	public function getAllMissingZeitmodelle()
	{
		{
			$ch = curl_init();

			$url = 'http://10.129.0.19:8080/sync/get_all_missing_zeitmodelle';

			//$fields_string = '';
			curl_setopt($ch, CURLOPT_URL, $url ); //Url together with parameters
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Return data instead printing directly in Browser
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 7); //Timeout after 7 seconds
			curl_setopt($ch, CURLOPT_USERAGENT , "FH-Complete CaseTime Addon");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, true);

			$result = curl_exec($ch);

			if(curl_errno($ch))
			{
				return 'Curl error: ' . curl_error($ch);
				curl_close($ch);
			}
			else
			{
				curl_close($ch);
				$data = json_decode($result);

				if(isset($data->STATUS) && $data->STATUS=='OK')
				{
					return $data->RESULT;
				}
				else
					return $data;
			}
		}
	}

}
