<?php
class Vertragvertragsstatus_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_vertrag_vertragsstatus';
		$this->pk = array('vertragsstatus_kurzbz', 'vertrag_id');
        $this->hasSequence = false;
	}

    /**
     * Check if Vertrag has the given Vertragsstatus.
     * @param integer $vertrag_id
     * @param string $mitarbeiter_uid
     * @param string $vertragsstatus_kurzbz
     * @return array
     */
    public function hasStatus($vertrag_id, $mitarbeiter_uid, $vertragsstatus_kurzbz)
    {
        $this->addSelect('1');
        $this->addLimit(1);

        return $this->loadWhere(array(
            'vertrag_id' => $vertrag_id,
            'uid' => $mitarbeiter_uid,
            'vertragsstatus_kurzbz' => $vertragsstatus_kurzbz
        ));
    }

    /**
     * Get the latest Vertragsstatus for the given Vertrag and Mitarbeiter
     * @param integer $vertrag_id
     * @param string $mitarbeiter_uid
     * @return array
     */
    public function getLastStatus($vertrag_id, $mitarbeiter_uid)
    {
        $this->addSelect('vertragsstatus_kurzbz');
        $this->addOrder('datum', 'DESC');
        $this->addLimit(1);
        return $this->loadWhere(
            array(
                'vertrag_id' => $vertrag_id,
                'uid' => $mitarbeiter_uid
            )
        );
    }

    /**
     * Set Vertragsstatus for the given Vertrag and Mitarbeiter.
     * @param integer $vertrag_id
	 * @param string $mitarbeiter_uid
     * @param string $vertragsstatus_kurzbz
     * @return object	On success, return success object.
	 * 					If status already exists or earlier status is missing, return error object.
     */
    public function setStatus($vertrag_id, $mitarbeiter_uid, $vertragsstatus_kurzbz){

        // Check if vertrag has already this status
        $result = $this->hasStatus($vertrag_id, $mitarbeiter_uid, $vertragsstatus_kurzbz);
        
		// If status is already set, return error message
        if (hasData($result))
        {
            return error('Fehler: Status bereits vorhanden.');
        }

        // If new status should be 'akzeptiert', the latest status has to be 'erteilt'
        if ($vertragsstatus_kurzbz == 'akzeptiert')
        {
            $result = $this->getLastStatus($vertrag_id, $mitarbeiter_uid);
            $last_status = getData($result)[0]->vertragsstatus_kurzbz;
	
			// If latest status is not 'erteilt', return error message
            if ($last_status != 'erteilt')
            {
                return error('Fehler: Vor Status \'angenommen\' muss erst Status \'erteilt\' gesetzt sein.');
            }
        }

        // Set new status if passed all checks
        return $this->insert(
            array(
                'vertrag_id' => $vertrag_id,
                'vertragsstatus_kurzbz' => $vertragsstatus_kurzbz,
                'uid' => $mitarbeiter_uid,
                'datum' =>  $this->escape('NOW()'),
                'insertvon' =>  getAuthUID(),
                'insertamum' =>  $this->escape('NOW()')
            )
        );
    }

    /**
     * Updates the date of the given vertragsstatus.
     * @param $vertrag_id
     * @param $vertragsstatus_kurzbz
     * @return array
     */
    public function updateStatus($vertrag_id, $vertragsstatus_kurzbz)
    {
        $user = getAuthUID();
        return $this->update(
            array(
                'vertrag_id' => $vertrag_id,
                'vertragsstatus_kurzbz' => $vertragsstatus_kurzbz
            ),
            array(
                'datum' => $this->escape('NOW()'),
                'updateamum' => $this->escape('NOW()'),
                'updatevon' => $user,
            )
        );
    }

    /**
     * Deletes the given vertragsstatus of the contract.
     * @param $vertrag_id
     * @param $vertragsstatus_kurbz
     * @return array
     */
    public function deleteStatus($vertrag_id, $vertragsstatus_kurzbz)
    {
        return $this->delete(
            array(
                'vertrag_id' => $vertrag_id,
                'vertragsstatus_kurzbz' => $vertragsstatus_kurzbz
            )
        );
    }

	/**
	 * Get all contracts, where the status had been set to 'bestellt' on given date
	 * @param string $string_date e.g. 'YYYY-MM-DD' or special Date/Time inputs like 'YESTERDAY', 'TODAY', 'NOW'
	 * @param bool $further_processed If true, ALL ordered contracts of that day are retrieved, even if they were
	 * 								  were ALSO approved/accepted/cancelled (further processed) on that same day.
	 * @return array
	 */
    public function getOrdered_fromDate($string_date = 'TODAY', $further_processed = false)
	{
    	$condition = '
    		vertragsstatus_kurzbz = \'bestellt\' AND
    		(datum)::date = date \''. $string_date .'\'
		';
    	
    	if (!$further_processed)
		{
			$condition .= '
			 AND
    		vertrag_id NOT IN (
    			SELECT vertrag_id
    			FROM lehre.tbl_vertrag_vertragsstatus
    			WHERE vertragsstatus_kurzbz IN (\'erteilt\', \'akzeptiert\', \'storno\')
    			)
			';
		}
  
		return $this->loadWhere($condition);
	}
	
	/**
	 * Get all contracts, where the status had been set to 'erteilt' on given date
	 * @param string $string_date e.g. '01.11.2019' or special Date/Time inputs like 'YESTERDAY', 'TODAY', 'NOW'
	 * @param bool $further_processed If true, ALL contracts approved on that day are retrieved, even if they were
	 * 								  were ALSO accepted/cancelled (further processed) on that same day.
	 * @return array
	 */
	public function getApproved_fromDate($string_date = 'TODAY', $further_processed = false)
	{
		$condition = '
				vertragsstatus_kurzbz = \'erteilt\' AND
				(datum)::date = date \''. $string_date .'\'
			';
		
		if (!$further_processed)
		{
			$condition .= '
				 AND
				vertrag_id NOT IN (
					SELECT vertrag_id
					FROM lehre.tbl_vertrag_vertragsstatus
					WHERE vertragsstatus_kurzbz IN (\'akzeptiert\', \'storno\')
					)
				';
		}
		
		return $this->loadWhere($condition);
	}
}
