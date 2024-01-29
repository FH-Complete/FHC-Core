<?php
class Ampel_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_ampel';
		$this->pk = 'ampel_id';
	}

	/**
	 * Returns all active Ampeln, that are actually:
	 * 1. not after the deadline date
	 * 2. not before the vorlaufszeit
	 * @param bool $email If true, then only ampeln are retrieved that are marked to be sent by mail.
	 * @return array Returns array of objects.
	 */
	public function active($email = false)
	{
		$parametersArray = null;
		$query = '
			SELECT *
			  FROM public.tbl_ampel
			 WHERE';

		if ($email === true)
		{
			$parametersArray['email'] = $email;
			$query .= ' email = ? AND';
		}

		$query .= '(
			(NOW()<(deadline+(COALESCE(verfallszeit,0) || \' days\')::interval)::date)
			    OR (verfallszeit IS NULL)
			   AND (NOW()>(deadline-(COALESCE(vorlaufzeit,0) || \' days\')::interval)::date)
			    OR (vorlaufzeit IS NULL AND NOW() < deadline))';

		$query .= ' ORDER BY deadline DESC';

		return $this->execQuery($query, $parametersArray);
	}

	/**
	 * Returns all Ampel-receiver of a specific Ampel.
	 * @param string $benutzer_select SQL Statement which defines the Ampel-receiver.
	 * @return array Returns array of objects with property 'uid'.
	 */
	public function execBenutzerSelect($benutzer_select)
	{
		if (!isEmptyString($benutzer_select))
		{
			return $this->execQuery($benutzer_select);
		}
	}

	/**
	 * Checks if Ampel was confirmed by the user.
	 * @param int $ampel_id Ampel-ID
	 * @param string $uid UID
	 * @return bool
	 */
	public function isConfirmed($ampel_id, $uid)
	{
		$result = null;
		$query = '
			SELECT 1
			  FROM public.tbl_ampel_benutzer_bestaetigt
			 WHERE ampel_id = ?
			   AND uid = ?';

		if (isset($ampel_id, $uid))
		{
			$result = $this->execQuery($query, array($ampel_id, $uid));
		}

		if ($result)
		{
			if (count($result->retval) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
			return $result; //will contain the error-msg from execQuery
	}
}
