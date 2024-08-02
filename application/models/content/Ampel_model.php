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
	 * @return stdClass Returns array of objects.
	 */
	public function active($email = false)
	{
		$userLanguage = getUserLanguage();

		$parametersArray = [];
		$query = '
			SELECT *, beschreibung[('.$this->getLanguageIndex($this->escape($userLanguage)).')] as beschreibung_trans, buttontext[('.$this->getLanguageIndex($this->escape($userLanguage)).')] as buttontext_trans
			  FROM public.tbl_ampel
			 WHERE';
		
		if ($email === true)
		{
			$parametersArray['email'] = $email;
			$query .= ' email = ? AND';
		}

		$query .= '(
			(
				(NOW()<(deadline+(COALESCE(verfallszeit,0) || \' days\')::interval)::date)
				OR (verfallszeit IS NULL)
			)
			AND 
			(
				(NOW()>(deadline-(COALESCE(vorlaufzeit,0) || \' days\')::interval)::date)
			    OR (vorlaufzeit IS NULL AND NOW() < deadline)
			)
			)';

		$query .= ' ORDER BY deadline DESC';

		return $this->execQuery($query, $parametersArray);
	}

	/**
	 * Returns all Ampel-receiver of a specific Ampel.
	 * @param string $benutzer_select SQL Statement which defines the Ampel-receiver.
	 * @return stdClass Returns array of objects with property 'uid'.
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


	/**
	 * confirms Ampel by the user.
	 * @param int $ampel_id Ampel-ID
	 * @param string $uid UID
	 * @return stdClass insert into result
	 */
	public function confirmAmpel($ampel_id, $uid)
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data(['ampel_id' => $ampel_id, 'uid' => $uid]);
		$this->form_validation->set_rules('ampel_id', 'Ampel ID', 'required|integer');
		$this->form_validation->set_rules('uid', 'UID', 'required');

		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		return $this->execQuery('
        INSERT INTO public.tbl_ampel_benutzer_bestaetigt (ampel_id, uid)
        VALUES (?,?);', array($ampel_id, $uid));
	}

	/**
	 * checks if a user is assigned to an ampel
	 * @param string $uid userID
	 * @param string $benutzer_select the select query which gets all the user that are assigned to an ampel
	 * @return stdClass 
	 */
	public function isZugeteilt($uid, $benutzer_select){
		$zugeteilt = $this->execReadOnlyQuery("
			SELECT 
				CASE WHEN ? IN (".$benutzer_select.") 
					THEN true 
					ELSE false
				END as zugeteilt	
			", [$uid]);

		if(isError($zugeteilt)){
			return $zugeteilt;
		}

		$zugeteilt = getData($zugeteilt);

		return success(current($zugeteilt)->zugeteilt);
	}

	// THIS FUNCTION IS NOT IN USE
	// fetches all ampeln that were assigned to the user after the working start_date
	function alleAmpeln($uid){
		$userLanguage = getUserLanguage();

		$zugeteile_ampeln = [];
		
		$datum = new datum();
		$now = $datum->mktime_fromdate(date('Y-m-d'));

		// start date of user
		$benutzerStartDate = $this->execReadOnlyQuery("
			SELECT insertamum FROM public.tbl_benutzer WHERE uid = ?", [$uid]);
		$benutzerStartDate = $datum->mktime_fromdate(date(current(getData($benutzerStartDate))->insertamum));

		$allAmpeln = $this->execReadOnlyQuery("
			SELECT *, beschreibung[(".$this->getLanguageIndex($this->escape($userLanguage)).")] as beschreibung_trans, buttontext[(".$this->getLanguageIndex($this->escape($userLanguage)).")] as buttontext_trans FROM 
			public.tbl_ampel");
		
		if(isError($allAmpeln)) return error(getError($allAmpeln));

		$allAmpeln = getData($allAmpeln);
		foreach($allAmpeln as $ampel){

			// check if the ampel is assigned to the user
			$zugeteilt = $this->execReadOnlyQuery("
			SELECT 
				CASE WHEN ? IN (".$ampel->benutzer_select.") 
					THEN true 
					ELSE false
				END as zugeteilt	
			", [$uid]);
			
			if(isError($zugeteilt)) return error(getError($zugeteilt));

			$zugeteilt = current(getData($zugeteilt))->zugeteilt;
			
			
			// abgelaufen check
			// $now > strtotime('+' . $ampel->verfallszeit . ' day', $ampel->deadline)

			if(
				// aktuelles datum liegt vor der Vorlaufzeit der Ampel
				(isset($ampel->vorlaufzeit) && $now < strtotime('-' .  $ampel->vorlaufzeit . ' day', $datum->mktime_fromdate($ampel->deadline)))
				||
				// ampel ist vor Arbeitsstart abgelaufen
				(isset($ampel->verfallszeit) && $benutzerStartDate > strtotime('+' . $ampel->verfallszeit . ' day', $datum->mktime_fromdate($ampel->deadline)))
				||
				// ampel ist vor Arbeitsstart abgelaufen (verfallszeit nicht vorhanden)
				($benutzerStartDate > strtotime('+' . $ampel->verfallszeit . ' day', $datum->mktime_fromdate($ampel->deadline)))
			){
				// continue iteration if ampel is expired before work start or shouldn't be visible yet
				continue;
			}

			$ampel->zugeteilt = $zugeteilt;

			if($zugeteilt) $zugeteile_ampeln[] = $ampel; 
			   
		}

		return success($zugeteile_ampeln);
	}

	private function getLanguageIndex($userLanguage)
	{
		return "
			SELECT index 
			FROM public.tbl_sprache 
			WHERE sprache = " . $userLanguage;
	}

}
